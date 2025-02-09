<?php
    //This script scrapes the ISO 639-2 language codes from Wikipedia and the names from the Library of Congress
    //I wrote it in about 5 hours. One of my quickest times yet. Very proud of it.

    //The SETTINGS
    $test = false;

    //Connects to the Lawbrary database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Loads the parser
    require('simple_html_dom.php');

    //Turns off the notices when we use end()
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    //The scraper
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
            
            //Checks if the language is already in the database. If not, adds it
            $sql =  "SELECT * FROM `languages` WHERE `ID`='".$ID."'";
            $result = $conn->query($sql);
            if ($result->num_rows === 0) {
                //Gets the endonym and the English name
                $nameEN = trim(explode('(', explode(';', strtr($lang->find('td', 4)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                $nameID = trim(explode('(', explode(';', strtr($lang->find('td', 7)->find('span', 0)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);

                //Queries LOC for the French and German names
                $lang = file_get_html('https://www.loc.gov/standards/iso639-2/php/code_list.php')->find('table', 1)->find('tr', $langNum);
                $nameFR = trim(explode('(', explode(';', strtr($lang->find('td', 3)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                $nameDE = trim(explode('(', explode(';', strtr($lang->find('td', 4)->plaintext, ["'"=>"ꞌ", '&nbsp;'=>' ']))[0])[0]);
                $name = '{"'.$ID.'":"'.$nameID.'", "en":"'.$nameEN.'", "fr":"'.$nameFR.'", "de":"'.$nameDE.'"}';

                //Adds the language to the database
                $SQL = "INSERT INTO `languages` (`ID`, `name`, `hasFlag`, `type`, `status`, `translations`) VALUES ('".$ID."', '".$name."', 0, '".$scope."', '".$status."', '{\"SEARCH\":\"Search the Lawbrary!\", \"OVIEW\":\"Overview\", \"CONST\":\"Constitution\", \"LAWS\":\"All Documents\", \"MAPOF\":\"Map of \$name\", \"NOLAWS\":\"Sorry, there are currently no laws documented for \$name.\", \"LANGERR\":\"Sorry, there is currently no such content<br/>availiable in this language. Try \$enLink.\"}')";
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

                    //Updates the language in the database
                    $SQL = "UPDATE `languages` SET `name`='".$name."', `type`='".$scope."', `status`='".$status."' WHERE `ID`='".$ID."'";
                }
            }

            //Sends the query
            echo $SQL."<br/>";
            if (!$test) {$conn->query($SQL);}
        }
    }

    //Organizes the table alphabetically by the English name
    //If the name has a space, it is sorted by the last word followed by the first. Otherwise, the regular name is used
    $SQL1 = "CREATE TABLE `languages2` LIKE `languages`";
    $SQL2 = "INSERT INTO languages2 SELECT * FROM languages ORDER BY TRIM('\"' FROM IF(`name` LIKE '% %', CONCAT(SUBSTRING_INDEX(name->'$.en', ' ', -1), ' ', SUBSTRING_INDEX(name->'$.en', ' ', 1)), name->'$.en'))";
    $SQL3 = "DROP TABLE `languages`;";
    $SQL4 = "ALTER TABLE `languages2` RENAME TO `languages`;";
    echo "<br/>".$SQL1."<br/>".$SQL2."<br/>".$SQL3."<br/>".$SQL4."<br/>";
    if (!$test) {
        $conn->query($SQL1);
        $conn->query($SQL2);
        $conn->query($SQL3);
        $conn->query($SQL4);
    }

    //Closes the connection
    $conn->close();
?>