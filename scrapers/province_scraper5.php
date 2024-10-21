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
        //The SETTINGS
        $test = false; $editParent = true;
        $countryCode = 'BH'; $subCode = '';//In case we're doing second level divisions
        $wikitable = 0; $sqlTable = 'divisions';
        $editLang = true; $isLangOffic = array('ar'=>true, 'en'=>false);
        $otherType = false;//Whether there is more than one type listed on wikitable
        $firstDivisionType = 'Governorate';//First division type on Wikitable
        $pluralDivisionType = 'Governorates';//Plural form of first division type
        $langStart = 1;//First Column to use that has link in header
        $ignore1 = false; $ignore2 = false; //Which columns to ignore
        $nameOffset = 0;//Zero unless there is a double column
        $useAsEnglish = false; $useESFR = false;//Deals with languages
        $capital = 'BH-13';//ISO Code of the country's capital if it has one


        //Opens the library
        include 'simple_html_dom.php';

        //Connects to the database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";

        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $sql1 = 'DELETE FROM `'.$sqlTable.'` WHERE `ID` LIKE "%'.$countryCode.'-%"';
        echo $sql1.'<br><br>';
        if (!$test) {$conn->query($sql1);}

        //Extracts the HTML
        $html_dom = file_get_html('https://en.wikipedia.org/wiki/ISO_3166-2:'.$countryCode);
        $body_rows = $html_dom->find('table.wikitable')[$wikitable]->find('tbody')[0]->find('tr');//Getting all of the table body rows
        $division_type = $firstDivisionType; //First type on Wikipedia
        $divisions = array($countryCode.$subCode=>array($pluralDivisionType=>array()));
        $languages = array();//All the languages mentioned in the header
        foreach($body_rows as $body_row) {
            //Division Code
            $division_code = trim($body_row->find('td', 0)->plaintext);

            //Division Names
            //Gets the languages
            $whichCell = $langStart;
            $headCells = $body_row->find('th');
            while ($whichCell < count($headCells)-1) {//Gets the languages
                if ($whichCell !== $ignore1 && $whichCell !== $ignore2) {//Wether or not to ignore a column
                    $langCode = $headCells[$whichCell+1]->find('a', 0)->innertext;
                    if (strlen($langCode) <= 3 && strlen($langCode) !== 0 && $langCode !== '—') {
                        if ($whichCell === $useAsEnglish) {
                            $languages[$whichCell] = 'en';
                        } else {$languages[$whichCell] = $langCode;}
                    }
                }
                $whichCell++;
            }
            //Gets the names
            $whichCell = $langStart;
            $division_names = $body_row->find('td');
            $first_name = '';//In case there are some blank values
            $division_name = '{';
            $numOfIgnoredColumns = ($ignore1 ? 1:0) + ($ignore2 ? 1:0);
            while ($whichCell < count($languages)+$langStart+$numOfIgnoredColumns) {
                if ($whichCell !== $ignore1 && $whichCell !== $ignore2) {//Wether or not to ignore a column
                    $this_cell = $division_names[$whichCell+$nameOffset+1];
                    
                    //In case the wikitable has a link in it
                    if (str_contains($this_cell->innertext, '</a>')) {
                        $this_name = trim(explode('(', $this_cell->find('a', 0)->plaintext)[0]);
                    } else {$this_name = trim(explode('(', $this_cell->plaintext)[0]);}

                    //Makes sure there are no quotes in the title
                    if (str_contains($this_name, "'")) {$this_name = str_replace("'", "’", $this_name);}

                    if ($first_name === '') {$first_name=$this_name;}//Sets the first name
                    if ($this_name === '') {$this_name=$first_name;}//In case the value is blank
                    $division_name .= '"'.$languages[$whichCell].'":"'.$this_name.'", ';
                }
                $whichCell += 1;
            }
            //In case the wikitable does not have English, Spanish or French
            if (!str_contains($division_name, '"en"')) {$division_name = $division_name.'"en":"'.$first_name.'", ';}
            if (!str_contains($division_name, '"es"') && $useESFR) {$division_name = $division_name.'"es":"'.$first_name.'", ';}
            if (!str_contains($division_name, '"fr"') && $useESFR) {$division_name = $division_name.'"fr":"'.$first_name.'", ';}                
            //Gets rid of the ', ' and the end
            $division_name = substr($division_name, 0, strlen($division_name)-2).'}';

            //Division Type
            if ($otherType) {
                if (!str_contains($body_row->find('td', count($body_row->find('td'))-$otherType)->innertext, "</a>")) {
                    if ($division_type !== trim(ucwords($body_row->find('td', count($body_row->find('td'))-$otherType)->innertext))) {
                        $division_type = trim(ucwords($body_row->find('td', count($body_row->find('td'))-$otherType)->innertext));
                    }
                } else {//In case there's an anchor tag
                    if ($division_type !== trim(ucwords($body_row->find('td', count($body_row->find('td'))-$otherType)->find('a', 0)->innertext))) {
                        $division_type = trim(ucwords($body_row->find('td', count($body_row->find('td'))-$otherType)->find('a', 0)->innertext));
                    }
                }
            } if (!$otherType && $capital) {$division_type=$firstDivisionType;}//In case the first type is the capital

            //Division Parent
            if ($sqlTable === "divisions2") {
                $subCode = "-".trim($body_row->find('td span.monospaced', 1)->plaintext);
            }

            if ($division_code && $division_name && $division_type) {// Checking if the row has truthy content
                //Adds a new subdivision if one does not exist
                if (is_null($divisions[$countryCode.$subCode])) {
                    $divisions[$countryCode.$subCode] = array($pluralDivisionType=>array());
                }

                //Sets the plural version of the type
                if (substr($division_type, -2) === 'sh') {
                    $pluralDivisionType = substr($division_type, 0, strlen($division_type)-2).'shes';
                } else if (substr($division_type, -1) === 'y') {
                    $pluralDivisionType = substr($division_type, 0, strlen($division_type)-1).'ies';
                } else if (substr($division_type, -1) === 's') {
                    $pluralDivisionType = substr($division_type, 0, strlen($division_type)-1).'ses';
                } else {$pluralDivisionType = $division_type.'s';}

                //Adds a new type of division if it does not already exist
                if (is_null($divisions[$countryCode.$subCode][$pluralDivisionType])) {
                    $divisions[$countryCode.$subCode][$pluralDivisionType] = array();
                }

                //Adds the subdivision
                array_push($divisions[$countryCode.$subCode][$pluralDivisionType], $division_code);

                //Changes the type to capital if capital exists
                if ($capital === $division_code) {$division_type = 'Capital';}

                //Updates the province database
                if ($sqlTable === 'divisions') {
                    $sql2 = "INSERT INTO `divisions` (`ID`, `emoji`, `name`, `type`, `source`, `hasMap`, `hasLaws`, `hasFlag`, `treaties`, `parent`, `children`) VALUES ('".$division_code."',NULL,'".$division_name."','".$division_type."','',0,0,0,NULL,'".$countryCode.$subCode."',NULL);";
                } else {$sql2 = "INSERT INTO `divisions2` (`ID`, `emoji`, `name`, `type`, `source`, `hasMap`, `hasLaws`, `hasFlag`, `treaties`, `parent`) VALUES ('".$division_code."',NULL,'".$division_name."','".$division_type."','',0,0,0,NULL,'".$countryCode.$subCode."');";}
                echo $sql2.'<br>';
                if (!$test) {$conn->query($sql2);}
            }
        } echo '<br>';

        //Creates country `children` updater from array
        $sqlHigherTable = $sqlTable === 'divisions' ? 'countries':'divisions';
        //Updates country table
        if ($editParent) {
            foreach ($divisions as $key => $val) {
                $sql3 = "UPDATE `".$sqlHigherTable."` SET `children`='".json_encode($val)."' WHERE `ID`='".$key."';";
                echo $sql3.'<br>';
                if (!$test) {$conn->query($sql3);}
            }
        }

        //Creates `languages` updater
        if ($editLang) {
            foreach($languages as $language) {
                $sql4 = "SELECT * FROM `languages` WHERE `ID` LIKE '%\"".$language."\"%'";
                $result = $conn->query($sql4);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //Sets arrays
                        $dispIn = array(
                            'countries' => json_decode($row['dispIn'], true)['countries'] ?? array(),
                            'divisions' => json_decode($row['dispIn'], true)['divisions'] ?? array(),
                            'divisions2' => json_decode($row['dispIn'], true)['divisions2'] ?? array()
                        );
                        $officIn = array(
                            'countries' => json_decode($row['officIn'], true)['countries'] ?? array(),
                            'divisions' => json_decode($row['officIn'], true)['divisions'] ?? array(),
                            'divisions2' => json_decode($row['officIn'], true)['divisions2'] ?? array()
                        );

                        //Adds codes to the arrays
                        foreach($divisions as $key => $vals) {
                            if (!in_array($key, $dispIn[$sqlHigherTable]) && !in_array('GLOBAL', $dispIn[$sqlHigherTable])) {array_push($dispIn[$sqlHigherTable], $key);}
                            if (!in_array($key, $officIn[$sqlHigherTable]) && !in_array('GLOBAL', $officIn[$sqlHigherTable])) {array_push($officIn[$sqlHigherTable], $key);}

                            foreach($vals as $val) {
                                foreach ($val as $valCode) {
                                    if (!in_array($valCode, $dispIn[$sqlTable]) && !in_array('GLOBAL', $dispIn[$sqlTable])) {array_push($dispIn[$sqlTable], $valCode);}
                                    if (!in_array($valCode, $officIn[$sqlTable]) && !in_array('GLOBAL', $officIn[$sqlTable])) {array_push($officIn[$sqlTable], $valCode);}
                                }
                            }
                        }

                        //Removes empty arrays from the objects
                        foreach ($dispIn as $inKey => $inVal) {
                            if ($inVal === array()) {unset($dispIn[$inKey]);}
                        }
                        foreach ($officIn as $inKey => $inVal) {
                            if ($inVal === array()) {unset($officIn[$inKey]);}
                        }

                        $sql4 = "UPDATE `languages` SET ";
                        if ($editLang) {$sql4 .= "`dispIn`='".json_encode($dispIn)."', ";}
                        if ($isLangOffic[$language]) {$sql4 .= "`officIn`='".json_encode($officIn
                            )."'";} else {$sql4 = substr($sql4, 0, strlen($sql4)-2);}
                        $sql4 .= " WHERE `ID`='".$row['ID']."'";
                    }
                }

                echo '<br><br>'.$sql4;
                if (!$test) {$conn->query($sql4);}
            }
        }

        $conn->close();
    ?>
</body>
</html>