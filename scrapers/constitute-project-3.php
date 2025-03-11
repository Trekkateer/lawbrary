<?php
    //TODO: Find a way to include `ammends` and `ammendedBy`

    //Settings
    $testSQL = true; $testDownload = true;

    //Translates words into English, Spanish, and Arabic
    $translations = array(
        'const' => array('en'=>'Constitution', 'es'=>'Constitución', 'ar'=>'دستور'),
        'draft' => array('en'=>'Draft', 'es'=>'Projecto', 'ar'=>'صياغة'),
        'of' => array('en'=>'of', 'es'=>'de', 'ar'=>''),
        'reinstated' => array('en'=>'reinst.', 'es'=>'restaurda', 'ar'=>'أعيد تفعيله'),
        'revised' => array('en'=>'rev.', 'es'=>'modif.', 'ar'=>'المعدل'),
        ', ' => array('en'=>', ', 'es'=>', ', 'ar'=>'، '),
        'cutoff' => array('en'=>2, 'es'=>2, 'ar'=>3)
    );

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Connects to the content database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username, $password, $database);
    $conn2->select_db($database) or die("Unable to select database");

    //Sets the static publisher variable, and a cache of countries that have been updated
    $publisher = '{\"ar\":\"مشروع الدستور\", \"en\":\"The Constitute Project\", \"es\":\"El Proyecto Constitute\"}';
    $visited = array();//For the purpose of clearing the database

    //Loops through the languages
    foreach(array('ar', 'en', 'es') as $lang) {
        //Crawls the Constitute Project
        $dom = file_get_contents('https://constituteproject.org/service/constitutions?ownership=all&lang='.$lang);
        $docs = json_decode($dom, true);

        //Adds constitution to the library
        foreach ($docs as $doc) {
            //Gets Name and other variables
            $CP_ID = $doc['id'];
            $year = preg_replace('/[^0-9]/', '', $CP_ID);
            $countryName = strtr(explode('_'.$year, $CP_ID)[0], ['_'=>' ']);
            $CP_country = $doc['country'];
            $CP_country_id = $doc['country_id'];
            $CP_title_short = $doc['title_short'];

            //Matches the CP country to a country in our database in cases where the names are different
            switch ($CP_country) {
                case "Denmark": $CP_country = "The Danish Realm"; break;
                case "Palestine": $CP_country = "The West Bank"; break;
                case "UK": $CP_country = "The United Kingdom"; break;
            }

            //Gets draft and status
            $CP_is_draft = $doc['is_draft'];
            $CP_in_force = $doc['in_force'];

            //Gets the relevant dates
            $CP_date_drafted = $doc['date_drafted'];
            $CP_year_enacted = $doc['year_enacted'];
            $CP_year_reinstated = $doc['year_reinstated'];
            $CP_year_revised = $doc['year_revised'];
            $CP_year_to = $doc['year_to'];

            //Gets the country data from lawbrary
            $SQL1 = 'SELECT * FROM `countries` WHERE `name` LIKE "%'.$countryName.'%" OR `name` LIKE "%'.$CP_country.'%" OR `name` LIKE "%'.$CP_country_id.'%" OR `name` LIKE "%'.$CP_title_short.'%" LIMIT 1';
            $result1 = $conn2->query($SQL1);
            if ($result1->num_rows == 1) {
                //Gets the id and adjective
                while ($row = $result1->fetch_assoc()) {
                    //Gets data
                    $adjective = json_decode($row['adjective'], true)[$lang] ?? '';
                    $countryID = $row['ID'];

                    
                    //Clears the database if it has not been visited yet
                    if (!in_array($countryID, $visited)) $conn->query('DELETE FROM `'.$countryID.'` WHERE `publisher`="'.$publisher.'";');

                    //Sets the ID for the constitution
                    $ID = $countryID.':CP-CONST-'.end(explode('_', $CP_ID));

                    //Sets the rest of the variables
                    if ($CP_is_draft) {
                        $draftDate = "'".$CP_date_drafted."'";
                        $enactDate = 'NULL';
                        $lastActDate = 'NULL';
                        $endDate = 'NULL';
                    } else {
                        $draftDate = $CP_date_drafted ? "'".$CP_date_drafted."'":'NULL';
                        $enactDate = "'".$CP_year_enacted."-01-01'";
                        $lastActDate = "'".($CP_year_revised ?? $CP_year_enacted)."-01-01'";
                        $endDate = ($CP_year_to == date('Y') ? 'NULL':"'".$CP_year_to."-01-01'");
                    }

                    //Sets the rest of the variables
                    $country = '[\"'.$countryID.'\"]';
                    $type = $CP_is_draft ? 'Draft Constitution':'Constitution';
                    $status = $CP_in_force ? 'In Force':'Not In Force';
                    $source = 'https://constituteproject.org/constitution/'.$CP_ID.'?lang='.$lang;
                    $CP_PDF = 'https://constituteproject.org/constitution/'.$CP_ID.'.pdf?lang='.$lang;

                    //Sets the title
                    $name = ($CP_is_draft ? $translations['draft'][$lang].' ':'').$translations['const'][$lang];
                    if (str_contains($adjective, '<')) {//Handles the adjective
                        $name .= ' '.explode('<', $adjective)[1];
                    } else {$name = $adjective.' '.$name;}
                    $name .= ' '.$translations['of'][$lang].' '.($CP_is_draft ? explode('-', $CP_date_drafted)[0]:$CP_year_enacted);
                    if ($CP_year_reinstated || $CP_year_revised) {
                        $name .= ' (';
                        if ($CP_year_reinstated) {
                            $name .= $translations['reinstated'][$lang].' '.$CP_year_reinstated.$translations[', '][$lang];
                        }
                        if ($CP_year_revised) {
                            $name .= $translations['revised'][$lang].' '.$CP_year_revised.$translations[', '][$lang];
                        }
                        $name = substr($name, 0, strlen($name)-$translations['cutoff'][$lang]).')';
                    }
                    $name = ($lang == 'en' ? 'The ':'').trim($name);

                    /*Downloads the pdf of the constitution
                    Excludes the US and Ukraine English translations, which I got from original sources*/
                    //Gets to downloading the PDF
                    $destination = '../documents/'.$countryID.'/'.$lang.'/'.explode(':', $ID)[1].'.pdf'; echo realpath($destination).'<br>';
                    $PDF = '/documents/'.$countryID.'/'.$lang.'/'.explode(':', $ID)[1].'.pdf'; echo realpath($PDF).'<br>';
                    if (!$testDownload && $countryID."/".$lang !== 'UA/en' && $countryID."/".$lang !== 'US/en') {
                        //Adds a folder for the language is one does not exist
                        if (!file_exists('../documents/'.$countryID.'/'.$lang)) mkdir('../documents/'.$countryID.'/'.$lang, 0777, true);

                        //File Handling
                        $new_file = fopen($destination, "w") or die("cannot open" . $PDF);

                        //Setting the curl download operations
                        $cd = curl_init();
                        curl_setopt($cd, CURLOPT_URL, $CP_PDF);
                        curl_setopt($cd, CURLOPT_FILE, $new_file);
                        curl_setopt($cd, CURLOPT_TIMEOUT, 100); //Timeout is 30 seconds, to download the large files you may need to increase the timeout limit.

                        //Running curl to download file
                        curl_exec($cd);
                        if (curl_errno($cd)) {
                            echo "the cURL error is : ".curl_error($cd);
                            $PDF = $CP_PDF;
                        } else {
                            if (curl_getinfo($cd)["http_code"] == 200) {
                                //The http status 200 means everything is going well. the error codes can be 401, 403 or 404.
                                echo "The File is Downloaded";
                            } else {
                                echo "The error code is : ".curl_getinfo($cd)["http_code"];
                                $PDF = $CP_PDF;
                            }
                        }

                        //Closing the curl and file
                        curl_close($cd);
                        fclose($new_file);
                    }

                    //Detects if a constitution is already registered
                    $SQL2 = 'SELECT * FROM `'.$countryID.'` WHERE `ID`="'.$ID.'"';
                    $result2 = $conn->query($SQL2);
                    //Sets up SQL for adding constitutions
                    if ($result2->num_rows > 0) {//If there is a constitution, update it
                        while ($row = $result2->fetch_assoc()) {
                            //Sets name, source, and PDF
                            $nameJSON = json_decode($row['name'], true);
                            $nameJSON[$lang] = $name;
                            $name = json_encode($nameJSON, JSON_UNESCAPED_UNICODE);

                            $sourceJSON = json_decode($row['source'], true);
                            $sourceJSON[$lang] = $source;
                            $source = json_encode($sourceJSON, JSON_UNESCAPED_UNICODE);

                            $PDFJSON = json_decode($row['PDF'], true);
                            $PDFJSON[$lang] = $PDF;
                            $PDF = json_encode($PDFJSON, JSON_UNESCAPED_UNICODE);

                            $SQL3 = "UPDATE `".$countryID."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `id`='".$ID."';";
                        }
                    } else {//If no constitution is there, add a new one
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';
                        $PDF = '{"'.$lang.'":"'.$PDF.'"}';
                        $SQL3 = "INSERT INTO `".$countryID."`(`draftDate`, `enactDate`, `lastactDate`, `endDate`, `saveDate`, `ID`, `name`, `country`, `publisher`, `type`, `status`, `source`, `PDF`) VALUES (".$draftDate.", ".$enactDate.", ".$lastActDate.", ".$endDate.", '".date('Y-m-d')."', '".$ID."', '".$name."', '".$country."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."');";
                    }

                    //Executes the SQL
                    echo '<br>'.$SQL3.'<br>';
                    if (!$testSQL) {if (isset($SQL3)) $conn->query($SQL3);}

                    //Resets SQL
                    $SQL3 = null; $SQL3 = null; $SQL5 = null;

                    //Adds the country to the visited list
                    if (!in_array($countryID, $visited)) $visited[] = $countryID;
                }
            }
        }
    }

    //Closes MySQL connection
    $conn->close(); $conn2->close();
?>