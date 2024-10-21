<html>
<head>
    <meta charset="UTF-8">
    <?php
    function console_log($output, $with_script_tags = true) {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }
    ?>
</head>
<body>
    <?php
    //Inputs
    $testSQL = true; $testDownload = true;
    $lang = 'ar';

    $translations = array(
        'Constitution' => array('en'=>'Constitution', 'es'=>'Constitución', 'ar'=>'دستور'),
        'of' => array('en'=>'of', 'es'=>'de', 'ar'=>''),
        'reinstated' => array('en'=>'reinst.', 'es'=>'restaurda', 'ar'=>'أعيد تفعيله'),
        'revised' => array('en'=>'rev.', 'es'=>'modif.', 'ar'=>'المعدل'),
        ', ' => array('en'=>', ', 'es'=>', ', 'ar'=>'، '),
        'cutoff' => array('en'=>2, 'es'=>2, 'ar'=>'3')
    );

    //Creates curl handler for search
    $ch_search = curl_init();
    curl_setopt_array($ch_search, [
        CURLOPT_URL => 'https://constituteproject.org/service/constitutions?in_force=true&is_draft=true&ownership=all&lang='.$lang,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CUSTOMREQUEST => 'GET'
    ]);
    $response = curl_exec($ch_search);
    
    //Closes the handler
    curl_close($ch_search);

    //Creates json from the response
    $json=json_decode($response, true);

    //Connects to the Lawbrary database
    $username="ug0iy8zo9nryq";
    $password="T_1&x+$|*N6F";
    $database="dbupm726ysc0bg";

    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Deletes Old Entries
    $sql0 = "DELETE FROM `constitutions` WHERE `publisher`='{\"".$lang."\":\"The Constitute Project\"}'";
    echo $sql0."<br><br>";
    if (!$testSQL) {$conn->query($sql0);}

    //Adds constitution to the library
    for ($i=0; $i<count($json); $i++) {
        if (!$json[$i]['is_draft']) {
            //Gets Name
            $CP_id = $json[$i]['id'];
            $explodeID = explode('_', $CP_id);
            $year = $explodeID[count($explodeID) - 1];
            $countryName = trim(str_replace('_', ' ', explode('_'.$year, $CP_id)[0]));
            $CP_country = $json[$i]['country'];
            $CP_country_id = $json[$i]['country_id'];
            $CP_title_short = $json[$i]['title_short'];

            //Way to get our chosen country names to be recognized if different from CP
            if ($CP_country_id === "Denmark") {$CP_country_id = "The Danish Realm";}
            elseif ($CP_country_id === "Palestine") {$CP_country_id = "West Bank";}
            elseif ($CP_country_id === "Turkey") {$CP_country_id = "Turkea";}
            elseif ($CP_title_short === "UK") {$CP_title_short = "The United Kingdom";}

            //Gets the relevant dates
            $CP_year_enacted = $json[$i]['year_enacted'];
            $CP_year_reinstated = $json[$i]['year_reinstated'];
            $CP_year_revised = $json[$i]['year_revised'];

            //Gets the country data from lawbrary
            $sql1 = 'SELECT * FROM `countries` WHERE `name` LIKE "%'.$countryName.'%" OR `name` LIKE "%'.$CP_country.'%" OR `name` LIKE "%'.$CP_country_id.'%" OR `name` LIKE "%'.$CP_title_short.'%" LIMIT 1';
            $result1 = $conn->query($sql1);

            if ($result1->num_rows > 0) {
                //Gets the id and adjective
                while ($row = $result1->fetch_assoc()) {
                    //Gets data
                    $adjective = isset(json_decode($row['adjective'], true)[$lang]) ? json_decode($row['adjective'], true)[$lang] : '';
                    $ID = $row['ID'];

                    //Sets the rest of the variables
                    $date = $CP_year_enacted.'-01-01';
                    $country = $ID;
                    $ID = $ID.'-Constitution';
                    $sourceCP = 'https://constituteproject.org/constitution/'.$CP_id.'.pdf?lang='.$lang;
                    $CP_region = $json[$i]['region'];

                    if (str_contains($adjective, '<')) {
                        $newTitle = $translations['Constitution'][$lang].' '.explode('<', $adjective)[1];
                    } else {$newTitle = $adjective.' '.$translations['Constitution'][$lang];}
                    $newTitle .= ' '.$translations['of'][$lang].' '.$CP_year_enacted;
                    if ($CP_year_reinstated || $CP_year_revised) {
                        $newTitle .= ' (';
                        if ($CP_year_reinstated) {
                            $newTitle .= $translations['reinstated'][$lang].' '.$CP_year_reinstated.$translations[', '][$lang];
                        }
                        if ($CP_year_revised) {
                            $newTitle .= $translations['revised'][$lang].' '.$CP_year_revised.$translations[', '][$lang];
                        }
                        $newTitle = substr($newTitle, 0, strlen($newTitle)-$translations['cutoff'][$lang]).')';
                    }
                    $newTitle = trim($newTitle);

                    //Downloads the pdf
                    //echo realpath('../documents/constitutions/'.$country."-".$lang.".pdf");
                    if (!$testDownload && $country.'-'.$lang !== 'DANISH-REALM-dk' && $country.'-'.$lang !== 'DANISH-REALM-en' && $country."-".$lang !== 'UA-uk' && $country."-".$lang !== 'UA-en' && $country."-".$lang !== 'US-en') {
                        //File download information
                        $source = $sourceCP; //The target url from which the file will be downloaded
                        $destination = '../documents/constitutions/'.$country."-".$lang.".pdf"; //Downloaded file url

                        //File Handling
                        $new_file = fopen($destination, "w") or die("cannot open" . $destination);

                        //Setting the curl operations
                        $cd = curl_init();
                        curl_setopt($cd, CURLOPT_URL, $source);
                        curl_setopt($cd, CURLOPT_FILE, $new_file);
                        curl_setopt($cd, CURLOPT_TIMEOUT, 100); //Timeout is 30 seconds, to download the large files you may need to increase the timeout limit.

                        //Running curl to download file
                        curl_exec($cd);
                        if (curl_errno($cd)) {
                            echo "the cURL error is : ".curl_error($cd);
                            $sourcePDF = $sourceCP;
                        } else {
                            $status = curl_getinfo($cd);
                            if ($status["http_code"] == 200) {
                                //The http status 200 means everything is going well. the error codes can be 401, 403 or 404.
                                echo "The File is Downloaded";
                                $sourcePDF = 'local';
                            } else {
                                echo "The error code is : ".$status["http_code"];
                                $sourcePDF = $sourceCP;
                            }
                        }

                        curl_close($cd);
                        fclose($new_file);
                    } else {$sourcePDF = 'local';}

                    //Sets up sql for adding region
                    $sql3 = "UPDATE `countries` SET `region`='".$CP_region."' WHERE `id`='".$country."'";

                    //Detects if a constitution is already registered
                    $sql2 = 'SELECT * FROM `constitutions` WHERE `country`="'.$country.'"';
                    $result2 = $conn->query($sql2);
                    //Sets up SQL for adding constitutions
                    if ($result2->num_rows > 0) {
                        while ($row = $result2->fetch_assoc()) {
                            //Sets Sourse and publisher if unset
                            $titleJSON = json_decode($row['title'], true);
                            $sourceJSON = json_decode($row['source'], true);
                            $publisherJSON = json_decode($row['publisher'], true);
                            if ((!isset($publisherJSON[$lang]) || $publisherJSON[$lang] === "The Constitute Project") && ($titleJSON[$lang] !== $newTitle || $sourceJSON[$lang] !== $sourcePDF || $publisherJSON[$lang] !== "The Constitute Project")) {
                                $sql4 = 'UPDATE `constitutions` SET ';//Starts SQL String
                                if ($titleJSON[$lang] !== $newTitle) {//In case the title isn't set
                                    $title = '{';
                                    foreach ($titleJSON as $key => $value) {
                                        if ($key !== $lang) {$title .= '"'.$key.'":"'.$value.'", ';}
                                    } $title .= '"'.$lang.'":"'.$newTitle.'"}';

                                    $sql4 .= "`title`='".$title."',";
                                }
                                if ($sourceJSON[$lang] !== $sourcePDF) {//In case the language isn't set
                                    $source = '{';
                                    foreach ($sourceJSON as $key => $value) {
                                        if ($key !== $lang) {$source .= '"'.$key.'":"'.$value.'", ';}
                                    } $source .= '"'.$lang.'":"'.$sourcePDF.'"}';

                                    $sql4 .= "`source`='".$source."',";
                                }
                                if ($publisherJSON[$lang] !== "The Constitute Project") {//In case the publisher isn't set
                                    $publisher = '{';
                                    foreach ($publisherJSON as $key => $value) {
                                        if ($key !== $lang) {$publisher .= '"'.$key.'":"'.$value.'", ';}
                                    } $publisher .= '"'.$lang.'":"The Constitute Project"}';
        
                                    $sql4 .= "`publisher`='".$publisher."',";
                                }
                                $sql4 = substr($sql4, 0, strlen($sql4)-1)." WHERE `country`='".$country."';";
                            } else {//What happens if the constitution doesn't need updating
                                $sql4 = "INSERT INTO `constitutions`(`date`, `title`, `ID`, `country`, `source`, `publisher`) VALUES ('".$row['date']."', '".$row['title']."', '".$row['ID']."', '".$row['country']."', '".$row['source']."', '".$row['publisher']."')";
                                $sql5 = "DELETE FROM `constitutions` WHERE `ID`='".$row['ID']."' LIMIT 1";
                            }
                        }
                    } else {//If no constitution is there, add a new one
                        $title = '{"'.$lang.'":"'.$newTitle.'"}';
                        $source = '{"'.$lang.'":"'.$sourcePDF.'"}';
                        $sql4 = "INSERT INTO `constitutions`(`date`, `title`, `ID`, `country`, `source`, `publisher`) VALUES ('".$date."', '".$title."', '".$ID."', '".$country."', '".$source."', '{\"".$lang."\":\"The Constitute Project\"}')";
                    }

                    //Executes the SQL
                    echo '<br>'.$sql3.'<br>'.$sql4.'<br>'.(isset($sql5) ? $sql5.'<br>' : '').'<br>';
                    if (!$testSQL) {$conn->query($sql3); $conn->query($sql4); if (isset($sql5)) {$conn->query($sql5);}}

                    //Resets SQL
                    $sql3 = null; $sql4 = null; $sql5 = null;
                }
            }
        }
    }

    //Echos content
    echo "<br>"."<br>".json_encode($json);

    //Closes MySQL connection
    $conn->close();
    ?>
</body>
</html>