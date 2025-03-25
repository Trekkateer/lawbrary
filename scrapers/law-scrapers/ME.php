<html><body>
    <?php
        //Settings
        $test = true; $country = 'ME';
        $start = 0;//Which law to start from
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

        //Gets the actual locale
        $realLocale = array('me'=>'dokumenta/', 'en'=>'en/documents/');

        //Loops through the languages
        foreach (array('cnr'=>'me'/*, 'en'=>'en'*/) as $lang=>$locale) {
            foreach (array(1=>'Legislation') as $typeID=>$type) {
                //Gets the limit
                $limit = $limit ?? $API_Call($locale, $typeID, 1)['hits']['total']['value'];

                //Gets values
                foreach ($API_Call($locale, $typeID, $limit)['hits']['hits'] as $law) {
                    $enactDate = date('Y-m-d', strtotime($law['_source']['published_at'])); $enforceDate = $enactDate; $lastActDate = $enactDate;
                    $ID = $country.'-'.$law['_source']['id'];
                    $name = trim(strtr(explode(' - ', $law['_source']['title'])[0], array('<b>'=>'', '<b/>'=>'')));
                    //Gets the regime
                    switch(true) {
                        case strtotime('1516') < strtotime($enactDate) && strtotime($enactDate) < strtotime('13 March 1852'):
                            $regime = 'The Prince-Bishopric of Montenegro';
                            break;
                        case strtotime('13 March 1852') < strtotime($enactDate) && strtotime($enactDate) < strtotime('28 August 1910'):
                            $regime = 'The Principality of Montenegro';
                            break;
                        case strtotime('28 August 1910') < strtotime($enactDate) && strtotime($enactDate) < strtotime('26 November 1918'):
                            $regime = 'The Kingdom of Montenegro';
                            break;
                        case strtotime('26 November 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 November 1945'):
                            $regime = 'The Kingdom of Yugoslavia';
                            break;
                        case strtotime('29 November 1945') < strtotime($enactDate) && strtotime($enactDate) < strtotime('27 April 1992'):
                            $regime = 'The Socialist Federal Republic of Yugoslavia';
                            break;
                        case strtotime('27 April 1992') < strtotime($enactDate) && strtotime($enactDate) < strtotime('21 May 2006'):
                            $regime = 'The State Union of Serbia and Montenegro';
                            break;
                        case strtotime('21 May 2006') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                            $regime = 'Montenegro';
                            break;
                    }
                    //Gets the rest of the values
                    $origin = '{"cnr":"'.$law['_source']['organizationalUnit']['title'].'", "en":"'.$law['_source']['organizationalUnit']['title_en'].'"}';
                    $source = 'https://www.gov.me/'.$realLocale[$locale].$law['_source']['node_ref'];

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                    //if (str_contains($name, '"')) {$name = str_replace('"', "\"", $name);}
                    //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
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
                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `name`, `regime`, `type`, `origin`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$origin."', '"."Valid"."', '".$source."')";
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