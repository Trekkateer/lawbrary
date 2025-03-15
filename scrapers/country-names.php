<?php
    //SETTINGS
    $test = true;

    //Turns off the notices when we use end()
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    //Opens the parser
    require('simple_html_dom.php');

    //Connects to the Lawbrary database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Gets the language names from our DB
    $countryFixer = array('The Czech Republic' => 'Czechia', 'Holy See'=>'The Vatican City', 'Timor-Leste'=>'East Timor');
    $langIDs = array('Abaza' => 'abq', 'Altai' => 'alt', 'Anglo-Saxon' => 'ang', 'Arbëresh'=>'aae', 'Aymara'=>'ay', 'Banyumasan'=>'jv', 'Austro-Bavarian'=>'bar', 'Chichewa'=>'ny', 'Hokkien Min Nan'=>'nan', 'Min Nan'=>'nan', 'Min Dong'=>'cdo', 'Caló'=>'rmq', 'Comorian'=>'zdj', 'Chuvash'=>'cv', 'Esperanto'=>'eo', 'West-Vlams'=>'vls', 'Frisian'=>'fy', 'Gagauz' => 'gag', 'Demotic Greek'=>'el', 'Katharevousa Greek'=>'el', 'Griko'=>'el', 'Guaraní'=>'gn', 'Ido'=>'io', 'Interlingua'=>'ia', 'Jèrriais'=>'nrf', 'Guernsiais'=>'nrf', 'Kalinago'=>'crb', 'Kalmyk'=>'xal', 'North Korean'=>'ko', 'South Korean' => 'ko', 'Leonese'=>'ast', 'Lojban'=>'jbo', 'Māori'=>'mi', 'Yucatec Maya'=>'yua', 'Meitei'=>'mni', 'Moldovan'=>'ro', 'Monégasque'=>'lij', 'Nahuatl'=>'nhe', 'Norfolkese' => 'pih', 'Ossetic'=>'os', 'Otomí'=>'oto', 'Pitcairnese' => 'pih', 'Kapampangan'=>'pam', 'Brazilian Portuguese'=>'pt', 'European Portuguese'=>'pt', 'Provençal'=>'oc', 'Sami'=>'sme', 'Sanskrit'=>'sa', 'Scots Gaelic'=>'gd', 'Slovak'=>'sk', 'Slovene'=>'sl', 'Sorbian'=>'dsb', 'Swati'=>'ss', 'Tarantino'=>'it', 'Tok Pisin'=>'tpi', 'Tonga'=>'tog', 'Udmurt'=>'udm', 'Valencian'=>'ca', 'Venetian'=>'vec', 'Volapük'=>'vo', 'Xhosa'=>'xh', 'Yoruba'=>'yo', 'isiZulu'=>'zu');
    $result = $conn->query('SELECT * FROM `languages`');
    while($row = $result->fetch_assoc()) {
        $langIDs[json_decode($row['name'], true)['en']] = $row['ID'];
    }
    $langIDs['Mandarin Chinese'] = 'zh';

    //Loops through the four Wikipedia pages
    echo "------------------------------<br/>Scraping from Wikipedia<br/>------------------------------<br/>";
    $wikis = array('A–C', 'D–I', 'J–P', 'Q–Z');
    foreach($wikis as $wiki) {echo "<br/>---------------------<br/>now scraping ".$wiki."<br/>---------------------<br/>";
        //Gets data from the Wikipedia page
        $wikiRows = file_get_html('https://en.wikipedia.org/wiki/List_of_country_names_in_various_languages_('.$wiki.')')->find('table.wikitable', 0)->find('tbody', 0)->find('tr');
        foreach($wikiRows as $wikiRow) {
            //Skips the header row(s)
            if ($wikiRow->find('td[colspan="2"]', 0) || $wikiRow->find('th', 0)) continue;
            $wikiRow = str_get_html(explode('<p>', $wikiRow->innertext)[0]);

            //Matches the row to a country in our DB, getting the ID and name
            $countryName = str_replace(array_keys($countryFixer), array_values($countryFixer), trim($wikiRow->find('td', 0)->find('a', 0)->plaintext));
            $result = $conn->query("SELECT * FROM `countries` WHERE `name`->'$.en'='".$countryName."'");
            if ($result->num_rows >= 1) {
                echo "<br/>found ".$countryName."<br/>";
                while($row = $result->fetch_assoc()) {
                    //Matches the row with a country
                    $table = 'countries';
                    $ID = $row['ID'];
                    $names = json_decode($row['name'], true);
                    $titles = json_decode($row['title'], true);
                    $altonyms = json_decode($row['altonym'], true);
                }
            } else {
                $resultDiv = $conn->query("SELECT * FROM `divisions` WHERE `name`->'$.en'='".$countryName."'");
                if ($resultDiv->num_rows >= 1) {
                    echo "<br/>found ".$countryName."<br/>";
                    while($row = $resultDiv->fetch_assoc()) {
                        //Matches the row with a division
                        $table = 'divisions';
                        $ID = $row['ID'];
                        $names = json_decode($row['name'], true);
                        $titles = json_decode($row['title'], true);
                        $altonyms = json_decode($row['altonym'], true);
                    }
                } else {
                    $resultDiv2 = $conn->query("SELECT * FROM `divisions2` WHERE `name`->'$.en' LIKE '%".$countryName."%'");
                    if ($resultDiv2->num_rows >= 1) {
                        echo "<br/>found ".$countryName."<br/>";
                        while($row = $resultDiv2->fetch_assoc()) {
                            //Matches the row with a division
                            $table = 'divisions2';
                            $ID = $row['ID'];
                            $names = json_decode($row['name'], true);
                            $titles = json_decode($row['title'], true);
                            $altonyms = json_decode($row['altonym'], true);
                        }
                    } else {echo "<br/>error: ".$countryName." not found<br/>"; continue;}
                }
            }

            //Loops through the names
            $items = explode('),', $wikiRow->find('td', 1)->plaintext);
            foreach($items as $item) {
                //Gets the name, language and adds it to the array. the use of explode('  ') may be unstable
                $name = trim(strtr(explode('  ', (explode('/', end(explode(' - ', explode(' (', $item)[0])))[0]))[0], ["'"=>"ꞌ"]));
                $langs = explode(', ', explode(' (', explode(')', $item)[0])[1]);
                foreach($langs as $langNum => $lang) {
                    //Sanitizes the language
                    $lang = trim(explode(' - ', $lang)[0]);

                    //Accounts for variants
                    $varMarkers = array('variant', 'variant*', 'alternate', 'alternative', 'informal', 'informally', 'formally', 'formal', 'archaic', 'historical', 'former', 'older', 'old', 'rare', 'uncommon', 'common');
                    if (!empty(array_intersect($varMarkers, explode(' ', $lang)))) {
                        $lang = trim(str_replace($varMarkers, '', $lang), ' *');
                        $array = 'altonyms';
                    } else if (str_contains($lang, 'full name in')) {
                        $lang = trim(str_replace('full name in', '', $lang), ' *');
                        $array = 'titles';
                    } else $array = 'names';

                    //Sets the language ID. Skips the language if it is not listed
                    $langID = $langIDs[$lang];
                    if (!isset($langID)) continue;

                    //Adds the name to the array
                    if (array_search($name, $names) !== $langID) {
                        if ((isset($names[$langID]) && $array === 'names') || (array_search($name, $altonyms ?? []) !== $langID && $array === 'altonyms')) {
                            if (!isset($altonyms[$langID])) $altonyms[$langID] = array();
                            $altonyms[$langID][] = $name;
                        } else if ($array === 'titles') {
                            $titles[$langID] = $titles[$langID] ?? $name;
                        } else {
                            $names[$langID] = $names[$langID] ?? $name;
                        }
                    }
                }
            }

            //JSONifies the arrays
            $name = json_encode($names, JSON_UNESCAPED_UNICODE);
            $titles = json_encode($titles, JSON_UNESCAPED_UNICODE);
            $altonym = isset($altonyms) ? "'".json_encode($altonyms, JSON_UNESCAPED_UNICODE)."'":'NULL';

            //Updates the country or division in the database
            $SQL = "UPDATE `".$table."` SET `name`='".$name."', `title`='".$titles."', `altonym`=".$altonym." WHERE `ID`='".$ID."'";
            echo $SQL."<br/>";
            if (!$test) {$conn->query($SQL);}
        }
    }

    //Closes connection
    $conn->close();
?>