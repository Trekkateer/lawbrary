<?php
    //Settings
    $test = false; $scraper = 'MC';
    $start = 0;//Which law to start from
    $limit = null;//Total number of laws desired.

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings and notices
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $API_Call = function ($type, $limit) use ($start) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://legimonaco.mc/~~search/depot/_search',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"size":'.$limit.',"track_total_hits":true,"_source":["path","title","type","date","abrogated","focus","abstract"],"query":{"bool":{"must":[{"terms":{"type":["'.$type.'"]}}],"must_not":[{"terms":{"processing":["home"]}},{"terms":{"trashed":["true"]}},{"terms":{"excludeFromSearch":["true"]}},{"range":{"startDate":{"gt":"now"}}},{"range":{"endDate":{"lte":"now"}}}]}},"sort":[{"date":{"order":"desc"}},{"typeOrder":{"order":"asc"}},{"number":{"order":"asc"}}],"from":'.$start.'}',
            CURLOPT_HTTPHEADER => array(
                'Cookie: BIGipServerPOOL_LEGIMCO_FRTO_PROD=204685322.20480.0000',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return json_decode($response, true);
    };

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["MC"]';
    $regime = '{"fr":"La Principauté de Monaco", "mc":"La Principauté de Monaco", "en":"The Principality of Monaco"}';
    $publisher = '{"fr":"Le Gouvernement Princier de Monaco", "mc":"Le Gouvernement Princier de Monaco", "en":"The Government of the Principality of Monaco"}';

    //Loops through the languages
    foreach (array('legislation', 'regulation') as $type) {
        //Gets the limit
        $limit = $limit ?? $API_Call($type, 1)['hits']['total']['value'];

        //Gets values
        foreach ($API_Call($type, $limit)['hits']['hits'] as $law) {
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law['_source']['date']));
            $ID = $scraper.':'.$law['_id'];
            $name = fixQuotes($law['_source']['title'], $lang);
            $type = ucfirst($type); $status = 'Valid';
            $source = 'https://legimonaco.mc'.$law['_source']['path'];
            $PDF = $source.'?V=pdf&.pdf';

            //JSONifies the values
            $name = '{"fr":"'.$name.'"}';
            $source = '{"fr":"'.$source.'"}';
            $PDF = '{"fr":"'.$PDF.'"}';

            //Inserts the law to the table
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';

            //Makes the query
            if (!$test) {$conn->query($SQL2);}
        }

        //Resets the limit
        $limit = null;
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>