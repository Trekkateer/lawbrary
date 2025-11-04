<?php
    //This script scrapes the ISO 639-2 language codes from Wikipedia and the names from the Library of Congress
    //I wrote it in about 5 hours. One of my quickest times yet. Very proud of it.

    //The SETTINGS
    $test = true;

    //Connects to the Lawbrary database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Loads the parser
    require('simple_html_dom.php');

    //Turns off the notices when we use end()
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    //The scraper
    echo "------------------------------<br/>Scraping from Wikipedia and the LOC<br/>------------------------------<br/>";
    $langs = file_get_html('https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes')->find('table', 0)->find('tbody', 0)->find('tr');
    foreach($langs as $langNum => $lang) {
        //Skips the header row
        if ($langNum === 0) {continue;}

        //The <code> tag breaks the parser, so we use explode to work around it
        //Gets the language ID ISO 639-1 is preferred
        $ID = end(explode('">', explode('</a>', (empty($lang->find('td', 3)->plaintext) ? $lang->find('td', 0)->plaintext:$lang->find('td', 3)->plaintext))[0]));

        //Makes sure the language has the correct scope
        if (($lang->find('td', 5)->plaintext === 'Individual' || $lang->find('td', 5)->plaintext === 'Macrolanguage') && $lang->find('td', 6)->plaintext === 'Living') {
            //Gets the variables
            $scope = $lang->find('td', 5)->plaintext;
            $status = $lang->find('td', 6)->plaintext;

            //Gets the endonym, English, and altonym name from Wikipedia
            $nameID = trim(explode('(', explode(';', strtr($lang->find('td', 7)->find('span', 0)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
            $nameEN = trim(explode('(', explode(';', strtr($lang->find('td', 4)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
            $altonym = array('en'=>array_map(function($i){return trim($i);}, explode(';', strtr($lang->find('td', 8)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))));

            //Checks if the language is already in the database. If not, adds it
            $SQL =  "SELECT * FROM `languages` WHERE `ID`='".$ID."'";
            $result = $conn->query($SQL);
            if ($result->num_rows === 0) {echo "<h1>Adding ".$ID."</h1>";
                //Queries LOC for the French and German names
                $lang = file_get_html('https://www.loc.gov/standards/iso639-2/php/code_list.php')->find('table', 1)->find('tr', $langNum);
                $nameFR = trim(explode('(', explode(';', strtr($lang->find('td', 3)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                $nameDE = trim(explode('(', explode(';', strtr($lang->find('td', 4)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                $name = '{"'.$ID.'":"'.$nameID.'", "en":"'.$nameEN.'", "fr":"'.$nameFR.'", "de":"'.$nameDE.'"}';

                //Sets the altonym
                $altonym = json_encode($altonym, JSON_UNESCAPED_UNICODE);
                if ($newAltonym['en'] === ['']) unset($newAltonym['en']);
                $newAltonym = str_replace("'[]'", 'NULL', "'".json_encode($newAltonym, JSON_UNESCAPED_UNICODE)."'");

                //Adds the language to the database. Altonym has not yet been tested
                $SQL0 = "INSERT INTO `languages` (`ID`, `name`, `altonym`, `hasFlag`, `type`, `status`, `translations`) VALUES ('".$ID."', '".$name."', ".$altonym.", 0, '".$scope."', '".$status."', '{\"SEARCH\":\"Search the Lawbrary!\", \"OVIEW\":\"Overview\", \"CONST\":\"Constitution\", \"LAWS\":\"All Documents\", \"MAPOF\":\"Map of \$name\", \"NOLAWS\":\"Sorry, there are currently no laws documented for \$name.\", \"LANGERR\":\"Sorry, there is currently no such content<br/>availiable in this language. Try \$enLink.\"}')";
            } else {
                while ($row = $result->fetch_assoc()) {
                    //Gets the name from the LB database
                    $name = json_decode($row['name'], true);

                    //Queries LOC for the French and German names
                    $lang = file_get_html('https://www.loc.gov/standards/iso639-2/php/code_list.php')->find('table', 1)->find('tr', $langNum);
                    $name[$ID] = $name[$ID] ?? $nameID;
                    $name['en'] = $name['en'] ?? $nameEN;
                    $name['fr'] = $name['fr'] ?? trim(explode('(', explode(';', strtr($lang->find('td', 3)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                    $name['de'] = $name['de'] ?? trim(explode('(', explode(';', strtr($lang->find('td', 4)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                    $name = json_encode($name, JSON_UNESCAPED_UNICODE);

                    //Sets the altonym
                    $newAltonym = json_decode($row['altonym'], true);
                    $newAltonym['en'] = array_filter(array_unique(array_merge($newAltonym['en'] ?? [], $altonym['en'] ?? [])));
                    if ($newAltonym['en'] === []) unset($newAltonym['en']);
                    $newAltonym = str_replace("'[]'", 'NULL', "'".json_encode($newAltonym, JSON_UNESCAPED_UNICODE)."'");

                    //Updates the language in the database
                    $SQL0 = "UPDATE `languages` SET `name`='".$name."', `altonym`=".$newAltonym.", `type`='".$scope."', `status`='".$status."' WHERE `ID`='".$ID."'";
                }
            }

            //Sends the query
            echo $SQL0."<br/>";
            if (!$test) {$conn->query($SQL0);}
        }
    }

    //Organizes the table alphabetically by the English name
    //If the name has a space, it is sorted by the last word followed by the first. Otherwise, the regular name is used
    $SQL1 = "CREATE TABLE `languagesTemp` LIKE `languages`";
    $SQL2 = "INSERT INTO `languagesTemp` SELECT * FROM `languages` ORDER BY TRIM('\"' FROM IF(`name`->'$.en' LIKE '% %', CONCAT(SUBSTRING_INDEX(`name`->'$.en', ' ', -1), ' ', SUBSTRING_INDEX(`name`->'$.en', ' ', 1)), `name`->'$.en'))";
    $SQL3 = "DROP TABLE `languages`;";
    $SQL4 = "ALTER TABLE `languagesTemp` RENAME TO `languages`;";
    echo "<br/>------------------------------<br/>Alphabetizing the table<br/>------------------------------<br/>".$SQL1."<br/>".$SQL2."<br/>".$SQL3."<br/>".$SQL4."<br/>";
    if (!$test) $conn->query($SQL1); $conn->query($SQL2); $conn->query($SQL3); $conn->query($SQL4);

    //Closes the connection
    $conn->close();
?>