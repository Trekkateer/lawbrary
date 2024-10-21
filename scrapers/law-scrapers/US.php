<html><body>
    <?php
        //Settings
        $test = true; $country = 'US';
        $startCongress = 82;//For some reason no laws before this are recorded
        $startLaw = 0;//Which law to start from
        $step = 250;//How much to increase the limit by every iteration
        $limit = null;//Total number of laws desired for each congress. Set to null to get number automatically 

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Figures out ordinals
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        $ordinalize = function ($congress) use ($ends) {
            if ($congress%100 >= 11 && $congress%100 <= 13) {return $congress.'th';}
            else {return $congress.$ends[$congress%10];}
        };

        //Gets data from Congress API
        $mostRecentCongress = round((getdate()['year'] - 1788)/2);
        for ($congress = $startCongress; $congress <= $mostRecentCongress; $congress++) {
            //Figures out how many laws there are for a given congress
            $data1 = file_get_contents('https://api.congress.gov/v3/law/'.$congress.'?api_key=b5wpSYu45GbmEBoXKJwB3AbIbsiNWFZ6MhbjHdEk'); echo $data1.'<br/>';
            $limit = $limit ?? json_decode($data1, true)['pagination']['count'];

            //Gets the laws
            for ($offset = $startLaw; $offset < $limit; $offset += $step) {
                //Gets the data from congress.gov API
                $data = file_get_contents('https://api.congress.gov/v3/law/'.$congress.'?offset='.$offset.'&limit='.$step.'&api_key=b5wpSYu45GbmEBoXKJwB3AbIbsiNWFZ6MhbjHdEk');
                $bills = json_decode($data, true)['bills'];
                foreach ($bills as $bill) {
                    //Interprets the data
                    $enactDate = date('Y/m/d', strtotime($bill['latestAction']['actionDate'])); $enforceDate = $enactDate;
                    $ID = $country.'-'.$bill['laws'][sizeof($bill['laws'])-1]['number'];
                    $name = $bill['title'];
                    $type = $bill['laws'][sizeof($bill['laws'])-1]['type'];
                    $source = 'https://congress.gov/bill/'.$ordinalize($congress).'-congress/'.strtolower($bill['originChamber']).'-bill/'.$bill['number'].'/text';

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                    if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                    if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                    //JSONifies the title and source
                    $name = '{"en":"'.$name.'"}';
                    $source = '{"en":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '"."Valid"."', '".$source."')"; echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }

        //Connects to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>