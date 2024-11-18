<html><body>
    <?php //!!Some laws are being repeated for some reason
        //Settings
        $test = true; $country = 'AU';
        $start = 0;//Which law to start from
        $step = 510;//How many laws per page
        $limit = null;//Total number of laws desired. 

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the types
        $types = array(
            'Act'=>'Act',
            'AdministrativeArrangementsOrder'=>'Administrative Arrangements Order',
            'ContinuedLaw'=>'Continued Law',
            'Constitution'=>'Constitution',
            'Gazette'=>'Gazette Item',
            'LegislativeInstrument'=>'Legislative Instrument',
            'NotifiableInstrument'=>'Notifiable Instrument',
            'PrerogativeInstrument'=>'Prerogative Instrument'
        );

        //Gets the statuses
        $statuses = array(
            'InForce'=>'In Force'
        );
        
        //Gets the laws
        $limit = $limit ?? json_decode(file_get_contents('https://api.prod.legislation.gov.au/v1/titles/search(criteria=%27and(status(InForce),pointintime(Latest))%27)?select=collection,id,isInForce,makingDate,name,searchContexts,statusHistory&expand=searchContexts(expand=fullTextVersion)&orderby=name%20asc&%24skip=0'), true)['@odata.count'];
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the data from legislation.gov API
            $laws = json_decode(file_get_contents('https://api.prod.legislation.gov.au/v1/titles/search(criteria=%27and(status(InForce),pointintime(Latest))%27)?select=collection,id,isInForce,makingDate,name,searchContexts,statusHistory&expand=searchContexts(expand=fullTextVersion)&orderby=name%20asc&%24skip='.$offset), true)['value'];
            foreach ($laws as $law) {
                //Interprets the data
                $enforceDate = date('Y-m-d', strtotime($law['statusHistory'][0]['start'])); $enactDate = $enforceDate;
                $ID = $country.'-'.$law['id'];
                $name = $law['name'];
                $type = $types[$law['collection']];
                //$status = $statuses[$law['statusHistory'][0]['status']];
                $status = $statuses[$law['status']];
                $source = 'https://www.legislation.gov.au/'.$ID.'/latest/text';

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the name
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';

                //Makes sure there are no duplicates and adds law to the table
                $SQL2 = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL2);
                if ($result->num_rows === 0) {
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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