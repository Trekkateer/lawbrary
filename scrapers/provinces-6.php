<html>
<body>
    <?php
        //The SETTINGS
        $test = true; $editParent = true;
        $countryCode = 'AF'; $subCode = '';//In case we're doing second level divisions
        $wikitable = 0; $SQLTable = 'divisions';
        $isLangOffic = array('fa'=>true, 'ps'=>true, 'en'=>false);
        $otherType = false;//Whether there is more than one type listed on wikitable
        $firstDivisionType = 'Governorate';//First division type on Wikitable
        $pluralDivisionType = 'Governorates';//Plural form of first division type
        $langStart = 1;//First Column to use that has link in header
        $ignore1 = false; $ignore2 = false; //Which columns to ignore
        $nameOffset = 0;//Zero unless there is a double column
        $useAsEnglish = false; $useESFR = false;//Deals with languages
        $capital = 'AF-KAB';//ISO Code of the country's capital if it has one

        //Opens the parser library
        include 'simple_html_dom.php';

        //Connects to the database
        $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = 'DELETE FROM `'.$SQLTable.'` WHERE `ID` LIKE "%'.$countryCode.'-%"';
        echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Extracts the HTML
        $dom = file_get_html('https://en.wikipedia.org/wiki/ISO_3166-2:'.$countryCode);
        $rows = $dom->find('table.wikitable')[$wikitable]->find('tbody')[0]->find('tr');
        $divisionType = $firstDivisionType; //First type on Wikipedia
        $divisions = array($countryCode.$subCode=>array($pluralDivisionType=>array()));
        $langs = array();//All the languages mentioned in the header
        foreach($rows as $row) {echo $row.'<br/>';
            //Division Code
            $divisionCode = trim($row->find('td', 0)->plaintext);

            //Division Names
            //Gets the languages
            $whichCell = $langStart;
            $headCells = $row->find('th');
            while ($whichCell < count($headCells)-1) {//Gets the languages
                if ($whichCell !== $ignore1 && $whichCell !== $ignore2) {//Wether or not to ignore a column
                    $langCode = $headCells[$whichCell+1]->find('a', 0)->innertext;
                    if (strlen($langCode) <= 3 && strlen($langCode) !== 0 && $langCode !== '—') {
                        if ($whichCell === $useAsEnglish) {
                            $langs[$whichCell] = 'en';
                        } else {$langs[$whichCell] = $langCode;}
                    }
                }
                $whichCell++;
            }
            //Gets the names
            $whichCell = $langStart;
            $divisionNames = $row->find('td');
            $firstName = '';//In case there are some blank values
            $divisionName = '{';
            $numOfIgnoredColumns = ($ignore1 ? 1:0) + ($ignore2 ? 1:0);
            while ($whichCell < count($langs)+$langStart+$numOfIgnoredColumns) {
                if ($whichCell !== $ignore1 && $whichCell !== $ignore2) {//Wether or not to ignore a column
                    $thisCell = $divisionNames[$whichCell+$nameOffset+1];
                    
                    //In case the wikitable has a link in it
                    if (str_contains($thisCell->innertext, '</a>')) {
                        $thisName = trim(explode('(', $thisCell->find('a', 0)->plaintext)[0]);
                    } else {$thisName = trim(explode('(', $thisCell->plaintext)[0]);}

                    //Makes sure there are no quotes in the title
                    if (str_contains($thisName, "'")) {$thisName = str_replace("'", "’", $thisName);}

                    if ($firstName === '') {$firstName=$thisName;}//Sets the first name
                    if ($thisName === '') {$thisName=$firstName;}//In case the value is blank
                    $divisionName .= '"'.$langs[$whichCell].'":"'.$thisName.'", ';
                }
                $whichCell += 1;
            }
            //In case the wikitable does not have English, Spanish or French
            if (!str_contains($divisionName, '"en"')) $divisionName = $divisionName.'"en":"'.$firstName.'", ';
            if (!str_contains($divisionName, '"es"') && $useESFR) $divisionName = $divisionName.'"es":"'.$firstName.'", ';
            if (!str_contains($divisionName, '"fr"') && $useESFR) $divisionName = $divisionName.'"fr":"'.$firstName.'", ';
            //Gets rid of the ', ' and the end
            $divisionName = substr($divisionName, 0, strlen($divisionName)-2).'}';

            //Division Type
            if ($otherType) {
                if (!str_contains($row->find('td', count($row->find('td'))-$otherType)->innertext, "</a>")) {
                    if ($divisionType !== trim(ucwords($row->find('td', count($row->find('td'))-$otherType)->innertext))) {
                        $divisionType = trim(ucwords($row->find('td', count($row->find('td'))-$otherType)->innertext));
                    }
                } else {//In case there's an anchor tag
                    if ($divisionType !== trim(ucwords($row->find('td', count($row->find('td'))-$otherType)->find('a', 0)->innertext))) {
                        $divisionType = trim(ucwords($row->find('td', count($row->find('td'))-$otherType)->find('a', 0)->innertext));
                    }
                }
            } if (!$otherType && $capital) $divisionType=$firstDivisionType;//In case the first type is the capital

            //Division Parent
            if ($SQLTable === "divisions2") $subCode = "-".trim($row->find('td span.monospaced', 1)->plaintext);

            if ($divisionCode && $divisionName && $divisionType) {// Checking if the row has truthy content
                //Adds a new subdivision if one does not exist
                if (is_null($divisions[$countryCode.$subCode])) $divisions[$countryCode.$subCode] = array($pluralDivisionType=>array());

                //Sets the plural version of the type
                if (substr($divisionType, -2) === 'sh') {
                    $pluralDivisionType = substr($divisionType, 0, strlen($divisionType)-2).'shes';
                } else if (substr($divisionType, -1) === 'y') {
                    $pluralDivisionType = substr($divisionType, 0, strlen($divisionType)-1).'ies';
                } else if (substr($divisionType, -1) === 's') {
                    $pluralDivisionType = substr($divisionType, 0, strlen($divisionType)-1).'ses';
                } else {$pluralDivisionType = $divisionType.'s';}

                //Adds a new type of division if it does not already exist
                if (is_null($divisions[$countryCode.$subCode][$pluralDivisionType])) $divisions[$countryCode.$subCode][$pluralDivisionType] = array();

                //Adds the subdivision
                $divisions[$countryCode.$subCode][$pluralDivisionType][] = $divisionCode;

                //Changes the type to capital if capital exists
                if ($capital === $divisionCode) {$divisionType = 'Capital';}

                //Sets the language
                $divisionLangs = array("Official"=>[], "Display"=>[]);
                foreach ($langs as $lang) {
                    if ($isLangOffic[$lang]) $divisionLangs["Official"][] = $lang;
                    $divisionLangs["Display"][] = $lang;
                }
                $divisionLangs = json_encode($divisionLangs, JSON_UNESCAPED_UNICODE);

                //Updates the province database
                if ($SQLTable === 'divisions') {
                    $SQL2 = "INSERT INTO `divisions` (`ID`, `emoji`, `langs`, `name`, `type`, `source`, `hasMap`, `hasLaws`, `hasFlag`, `treaties`, `parent`, `children`) VALUES ('".$divisionCode."',NULL,'".$divisionLangs."', '".$divisionName."','".$divisionType."','',0,0,0,NULL,'".$countryCode.$subCode."',NULL);";
                } else {$SQL2 = "INSERT INTO `divisions2` (`ID`, `emoji`, `langs`, `name`, `type`, `source`, `hasMap`, `hasLaws`, `hasFlag`, `treaties`, `parent`) VALUES ('".$divisionCode."',NULL,'".$divisionLangs."', '".$divisionName."','".$divisionType."','',0,0,0,NULL,'".$countryCode.$subCode."');";}
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        } echo '<br/>';

        //Creates country `children` updater from array
        $SQLHigherTable = $SQLTable === 'divisions' ? 'countries':'divisions';
        //Updates country table
        if ($editParent) {
            foreach ($divisions as $key => $val) {
                $SQL3 = "UPDATE `".$SQLHigherTable."` SET `children`='".json_encode($val)."' WHERE `ID`='".$key."';";
                echo $SQL3.'<br/>';
                if (!$test) {$conn->query($SQL3);}
            }
        }

        //Closes the connection
        $conn->close();
    ?>
</body>
</html>