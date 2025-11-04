<?php //Montenegro
    //TODO: Make sure that all the titles are in Montenegrin

    //Settings
    $test = false; $scraper = 'ME';
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

    //Sets the types of laws
    $types = array(
        1  => 'Act',         //Zakon
        2  => 'Bill',        //Predlog zakona
        3  => 'Draft Law',   //Nacrt zakona
        4  => 'Regulation',  //Uredba
        5  => 'Regulations', //Pravilnik
        6  => 'Strategy',    //Strategija
        7  => 'Action Plan', //Akcioni plan / plan rada
        8  => 'Public Call', //Javni poziv
        9  => 'Public Registers and Records', //Javni registri i javne evidencije
        10 => 'Report',      //Izvještaj
        11 => 'Competition', //Konkurs
        12 => 'Advertisement', //Oglas
        13 => 'Decision',    //Odluka
        14 => 'Decision',    //Rješenje
        15 => 'Proposal',    //Tender
        16 => 'Contract',    //Ugovor
        17 => 'Form',        //Obrazac
        18 => 'Other',       //Ostalo
        19 => 'Information', //Informacija
        20 => 'Conclusion'   //Zaključak
    );

    //Sets up querying function
    $API_Call = function ($locale, $type, $limit=1) use ($start) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://wapi.gov.me/search/gov_article_production',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"size":'.$limit.',"from":'.$start.',"sort":{"published_at":"desc"},"track_total_hits":true,"query":{"bool":{"must":[{"term":{"locale":"'.$locale.'"}},{"terms":{"documentType.id":["'.$type.'"]}},{"term":{"systemObjectType":"document"}}]}},"aggs":{"documentTypes":{"terms":{"size":40,"field":"documentType.id"}},"organizationalUnits":{"terms":{"size":40,"field":"organizationalUnit.id"}}}}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return json_decode($response, true);
    };

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["ME"]'; $status = 'Valid';
    $publisher = '{"en":"The Parliament of Montenegro"}';
    $realLocale = array('me'=>'dokumenta/', 'en'=>'en/documents/');

    //Loops through the languages
    foreach (array('cnr'=>'me', 'en'=>'en') as $lang=>$locale) {
        foreach ($types as $typeID=>$type) {
            //Gets the limit
            $limit = $limit ?? $API_Call($locale, $typeID, 1)['hits']['total']['value'];

            //Gets values
            foreach ($API_Call($locale, $typeID, $limit)['hits']['hits'] as $law) {
                $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law['_source']['published_at']));
                $ID = $scraper.':'.$law['_source']['id'];
                $name = fixQuotes(trim(strtr(explode(' - ', $law['_source']['title'])[0], ['<b>'=>'', '<b/>'=>'', '-'=>' '])), $lang);
                    $name = preg_match('/[^a-zčćđžšśž]+/', $name) ? ucfirst(mb_strtolower($name)):$name;
                //Gets the regime
                if (strtotime($enactDate) < strtotime('13 March 1852')) {
                    $regime = '{"cnr":"Митрополство Црногорско", "en":"The Prince-Bishopric of Montenegro"}';
                } elseif (strtotime($enactDate) < strtotime('28 August 1910')) {
                    $regime = '{"cnr":"Принципалност Црна Гора", "en":"The Principality of Montenegro"}';
                } else if (strtotime($enactDate) < strtotime('26 November 1918')) {
                    $regime = '{"cnr":"Краљевина Црна Гора", "en":"The Kingdom of Montenegro"}';
                } else if (strtotime($enactDate) < strtotime('29 November 1945')) {
                    $regime = '{"cnr":"Краљевина Југославија", "en":"The Kingdom of Yugoslavia"}';
                } else if (strtotime($enactDate) < strtotime('27 April 1992')) {
                    $regime = '{"cnr":"Социјалистичка Федеративна Република Југославија", "en":"The Socialist Federal Republic of Yugoslavia"}';
                } else if (strtotime($enactDate) < strtotime('21 May 2006')) {
                    $regime = '{"cnr":"Државна заједница Србија и Црна Гора", "en":"The State Union of Serbia and Montenegro"}';
                } else {
                    $regime = '{"cnr":"Црна Гора", "en":"Montenegro"}';
                }
                //Gets the rest of the values
                $origin = '{"cnr":"'.$law['_source']['organizationalUnit']['title'].'", "en":"'.$law['_source']['organizationalUnit']['title_en'].'"}';
                $source = 'https://www.gov.me/'.$realLocale[$locale].$law['_source']['node_ref'];

                //Creates SQL to check if the law is already stored
                $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $name;
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                        //JSONifies the href
                        $compoundedSource = json_decode($row['source'], true);
                        $compoundedSource[$lang] = $source;
                        $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                        //Creates SQL
                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `origin`, `status`, `source`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$origin."', '".$status."', '".$source."')";
                }

                //Makes the query
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }

            //Resets the limit
            $limit = null;
        }
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