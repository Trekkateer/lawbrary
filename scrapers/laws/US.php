<?php //The United States
    //Settings
    $test = false; $scraper = 'US';
    $start = 82;//the API does not have data for congresses before 82
    $step = 250;//Number of laws on each page
    $limit = null;//Total number of laws desired for each congress

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Figures out ordinals
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    $ordinalize = function ($congress) use ($ends) {
        if ($congress%100 >= 11 && $congress%100 <= 13) {return $congress.'th';}
        else {return $congress.$ends[$congress%10];}
    };

    //Gets the static variables
    $saveDate = date('Y-m-d'); $country = '["US"]';
    $regime = '{"en":"The United States of America"}';
    $publisher = '{"en":"The Library of Congress"}';

    //Gets data from Congress API
    $mostRecentCongress = round((date('Y') - 1788)/2);
    //$missingCongresses = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81);
    for ($congress = $start; $congress <= $mostRecentCongress; $congress++) {
        //Skips some of the congresses
        //if (in_array($congress, $missingCongresses)) {echo '<br/>continue<br/>'; continue;}
        echo $congress.'<br/>';

        //Figures out how many laws there are for a given congress
        $data1 = file_get_contents('https://api.congress.gov/v3/law/'.$congress.'?api_key=b5wpSYu45GbmEBoXKJwB3AbIbsiNWFZ6MhbjHdEk');
        $limit = $limit ?? json_decode($data1, true)['pagination']['count'];

        //Gets the laws
        for ($offset = 0; $offset < $limit; $offset += $step) {
            //Gets the data from congress.gov API
            $data = file_get_contents('https://api.congress.gov/v3/law/'.$congress.'?offset='.$offset.'&limit='.$step.'&api_key=b5wpSYu45GbmEBoXKJwB3AbIbsiNWFZ6MhbjHdEk');
            $bills = json_decode($data, true)['bills'];
            foreach ($bills as $bill) {
                //Interprets the data
                $enactDate = $enforceDate = $bill['latestAction']['actionDate']; $lastactDate = $bill['updateDate'] ?? $enactDate;
                $ID = $scraper.':'.$bill['laws'][sizeof($bill['laws'])-1]['number'];
                $origin = str_replace(['House', 'Senate'], ['The House of Representatives', 'The Senate'], $bill['originChamber']);
                $name = fixQuotes(trim($bill['title'], ' .'), 'en');
                $type = $bill['laws'][sizeof($bill['laws'])-1]['type']; $status = 'Valid';
                //$isAmend = (str_contains($name, 'Amend') || str_contains($name, 'amend')) ? 1:0;
                $source = 'https://congress.gov/bill/'.$ordinalize($congress).'-congress/'.strtolower($bill['originChamber']).'-bill/'.$bill['number'].'/text';

                //JSONifies the title and source
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `origin`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$origin."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }

    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>