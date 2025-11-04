<?php //Estonia //TODO: Try to fix the English incorporation
    //Settings
    $test = false; $scraper = 'EE';
    $start = 0; //Which page to start from
    $limit = NULL; //Which page to stop at

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["EE"]';
    $publisher = '{"et":"Eesti Riigi Teataja","en":"The State Gazette of Estonia"}';

    //Sets the oringin translations
    $origins = array(
        'Ettevõtlusminister'                     => ['[{"et":"Ettevõtlusminister", "en":"The Minister of Entrepreneurship"}]', '\'[{"et":"Ettevõtlus", "en":"Entrepreneurship"}]\''],
        'Ettevõtlus- ja infotehnoloogiaminister' => ['[{"et":"Ettevõtlus- ja infotehnoloogiaminister", "en":"The Minister of Entrepreneurship and Information Technology"}]', '\'[{"et":"Ettevõtlus", "en":"Entrepreneurship"}, {"et":"Infotehnoloogia", "en":"Information Technology"}]\''],
        'Haridus- ja teadusminister'             => ['[{"et":"Haridus- ja teadusminister", "en":"The Minister of Education and Research"}]', '\'[{"et":"Haridus", "en":"Education"}, {"et":"Teadus", "en":"Research"}]\''],
        'Haridusminister'                        => ['[{"et":"Haridusminister", "en":"The Minister of Education"}]', '\'[{"et":"Haridus", "en":"Education"}]\''],
        'Justiitsminister'                       => ['[{"et":"Justiitsminister", "en":"The Minister of Justice"}]', '\'[{"et":"Õigus", "en":"Law"}]\''],
        'Justiits- ja digiminister'              => ['[{"et":"Justiits- ja digiminister", "en":"The Minister of Justice and Digitalisation"}]', '\'[{"et":"Õigus", "en":"Law"}, {"et":"Digitehnoloogia", "en":"Digital Technology"}]\''],
        'Kaitseminister'                         => ['[{"et":"Kaitseminister", "en":"The Minister of Defence"}]', '\'[{"et":"Kaitse", "en":"Defence"}]\''],
        'Keskkonnaminister'                      => ['[{"et":"Keskkonnaminister", "en":"The Minister of Environment"}]', '\'[{"et":"Keskkond", "en":"Environment"}]\''],
        'Kliimaminister'                         => ['[{"et":"Kliimaminister", "en":"The Minister of Climate"}]', '\'[{"et":"Kliima", "en":"Climate"}]\''],
        'Kultuuriminister'                       => ['[{"et":"Kultuuriminister", "en":"The Minister of Culture"}]', '\'[{"et":"Kultuur", "en":"Culture"}]\''],
        'Kultuuri- ja haridusminister'           => ['[{"et":"Kultuuri- ja haridusminister", "en":"The Minister of Culture and Education"}]', '\'[{"et":"Kultuur", "en":"Culture"}, {"et":"Haridus", "en":"Education"}]\''],
        'Maaeluminister'                         => ['[{"et":"Maaeluminister", "en":"The Minister of Rural Affairs"}]', '\'[{"et":"Maaelu", "en":"Rural Affairs"}]\''],
        'Majandusminister'                       => ['[{"et":"Majandusminister", "en":"The Minister of Economic Affairs"}]', '\'[{"et":"Majandus", "en":"Economic Affairs"}]\''],
        'Majandus- ja kommunikatsiooniminister'  => ['[{"et":"Majandus- ja kommunikatsiooniminister", "en":"The Minister of Economic Affairs and Communications"}]', '\'[{"et":"Majandus", "en":"Economic Affairs"}, {"et":"Kommunikatsioon", "en":"Communications"}]\''],
        'Majandus- ja infotehnoloogiaminister'   => ['[{"et":"Majandus- ja infotehnoloogiaminister", "en":"The Minister of Economic Affairs and Information Technology"}]', '\'[{"et":"Majandus", "en":"Economic Affairs"}, {"et":"Infotehnoloogia", "en":"Information Technology"}]\''],
        'Majandus- ja taristuminister'           => ['[{"et":"Majandus- ja taristuminister", "en":"The Minister of Economic Affairs and Infrastructure"}]', '\'[{"et":"Majandus", "en":"Economic Affairs"}, {"et":"Taristu", "en":"Infrastructure"}]\''],
        'Majandus- ja tööstusminister'           => ['[{"et":"Majandus- ja tööstusminister", "en":"The Minister of Economic Affairs and Industry"}]', '\'[{"et":"Majandus", "en":"Economic Affairs"}, {"et":"Tööstus", "en":"Industry"}]\''],
        'Põllumajandusminister'                  => ['[{"et":"Põllumajandusminister", "en":"The Minister of Agriculture"}]', '\'[{"et":"Põllumajandus", "en":"Agriculture"}]\''],
        'Rahandusminister'                       => ['[{"et":"Rahandusminister", "en":"The Minister of Finance"}]', '\'[{"et":"Rahandus", "en":"Finance"}]\''],
        'Rahvastikuminister'                     => ['[{"et":"Rahvastikuminister", "en":"The Minister of Population"}]', '\'[{"et":"Rahvastik", "en":"Population"}]\''],
        'Regionaalminister'                      => ['[{"et":"Regionaalminister", "en":"The Minister of Regional Affairs"}]', '\'[{"et":"Regionaalne areng", "en":"Regional Development"}]\''],
        'Regionaal- ja põllumajandusminister'    => ['[{"et":"Regionaal- ja põllumajandusminister", "en":"The Minister of Regional Affairs and Agriculture"}]', '\'[{"et":"Regionaalne areng", "en":"Regional Development"}, {"et":"Põllumajandus", "en":"Agriculture"}]\''],
        'Riigihalduse minister'                  => ['[{"et":"Riigihalduse minister", "en":"The Minister of State Administration"}]', '\'[{"et":"Riigihaldus", "en":"State Administration"}]\''],
        'Siseminister'                           => ['[{"et":"Siseminister", "en":"The Minister of Internal Affairs"}]', '\'[{"et":"Siseministeerium", "en":"Ministry of the Interior"}]\''],
        'Sotsiaalminister'                       => ['[{"et":"Sotsiaalminister", "en":"The Minister of Social Affairs"}]', '\'[{"et":"Sotsiaalne kaitse", "en":"Social Protection"}]\''],
        'Sotsiaalkaitseminister'                 => ['[{"et":"Sotsiaalkaitseminister", "en":"The Minister of Social Protection"}]', '\'[{"et":"Sotsiaalne kaitse", "en":"Social Protection"}]\''],
        'Sotsiaalkaitseminister ning tervise- ja tööminister' => ['[{"et":"Sotsiaalkaitseminister", "en":"The Minister of Social Protection"}, {"et":"Tervise- ja tööminister", "en":"The Minister of Health and Labor"}]', '\'[{"et":"Sotsiaalne kaitse", "en":"Social Protection"}, {"et":"Tervise", "en":"Health"}, {"et":"Töö", "en":"Labor"}]\''],
        'Sotsiaalkaitseminister ja terviseminister' => ['[{"et":"Sotsiaalkaitseminister", "en":"The Minister of Social Protection"}, {"et":"Terviseminister", "en":"The Minister of Health"}]', '\'[{"et":"Sotsiaalne kaitse", "en":"Social Protection"}, {"et":"Tervishoid", "en":"Health"}]\''],
        'Taristuminister'                        => ['[{"et":"Taristuminister", "en":"The Minister of Infrastructure"}]', '\'[{"et":"Taristu", "en":"Infrastructure"}]\''],
        'Teede- ja Sideminister'                 => ['[{"et":"Teede- ja Sideminister", "en":"The Minister of Roads and Communications"}]', '\'[{"et":"Teed", "en":"Roads"}, {"et":"Side", "en":"Communications"}]\''],
        'Terviseminister'                        => ['[{"et":"Terviseminister", "en":"The Minister of Health"}]', '\'[{"et":"Tervishoid", "en":"Health Care"}]\''],
        'Tervise- ja tööminister'                => ['[{"et":"Tervise- ja tööminister", "en":"The Minister of Health and Labor"}]', '\'[{"et":"Tervise", "en":"Health"}, {"et":"Töö", "en":"Labor"}]\''],
        'Välisminister'                          => ['[{"et":"Välisminister", "en":"The Minister of Foreign Affairs"}]', '\'[{"et":"Välisasjad", "en":"Foreign Affairs"}]\''],
        'Väliskaubandus- ja ettevõtlusminister'  => ['[{"et":"Väliskaubandus- ja ettevõtlusminister", "en":"The Minister of Foreign Trade and Entrepreneurship"}]', '\'[{"et":"Väliskaubandus", "en":"Foreign Trade"}, {"et":"Ettevõtlus", "en":"Entrepreneurship"}]\''],
        'Väliskaubandus- ja infotehnoloogiaminister' => ['[{"et":"Väliskaubandus- ja infotehnoloogiaminister", "en":"The Minister of Foreign Trade and Information Technology"}]', '\'[{"et":"Väliskaubandus", "en":"Foreign Trade"}, {"et":"Infotehnoloogia", "en":"Information Technology"}]\''],

        'Peaminister' => ['[{"et":"Peaminister", "en":"The Prime Minister"}]', 'NULL'],

        'Eesti Panga President' => ['[{"et":"Eesti Panga President", "en":"The President of the Bank of Estonia"}]', 'NULL'],

        'Rahvahääletusel vastu võetud' => ['[{"et":"Rahvahääletusel vastu võetud", "en":"Adopted by referendum"}]', 'NULL'],

        'Riigikogu' => ['[{"et":"Riigikogu", "en":"The Riigikogu"}]', 'NULL'],

        'Ülemnõukogu' => ['[{"et":"Ülemnõukogu", "en":"The Supreme Council"}]', 'NULL'],

        'Vabariigi Valimiskomisjon' => ['[{"et":"Vabariigi Valimiskomisjon", "en":"The National Electoral Committee"}]', 'NULL'],
        'Vabariigi Valitsus' => ['[{"et":"Vabariigi Valitsus", "en":"The Government of the Republic"}]', 'NULL'],
    );
    $types = array(
        'määrus' => 'Regulation',
        'seadus' => 'Law',
        'Seadus' => 'Law',
        'korraldus' => 'Order'
    );

    //Loops through Estonian and English. English is currently not working on the site
    foreach (array('tervikteksti_tulemused.html' => 'et'/*, 'en/' => 'en'*/) as $locale  => $lang) {
        //Gets the limit
        $limit = $limit ?? file_get_html('https://www.riigiteataja.ee/'.$locale.'?sakk=koik_otsitavad&leht=0&kuvaKoik=true&sorteeri=kehtivuseAlgus&kasvav=true')->find('li.last', 0)->find('a', 0)->id;
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the HTML
            $dom = file_get_html('https://www.riigiteataja.ee/'.$locale.'?sakk=koik_otsitavad&leht='.$page.'&kuvaKoik=true&sorteeri=kehtivuseAlgus&kasvav=true');

            //Processes the data in the table
            $laws = $dom->find('table > tbody', 0)->find('tr');
            foreach ($laws as $law) {
                //Gets the source
                $source = $law->find('td', 0)->find('a', 0)->href;
                //Gets rest of the values
                $enactDate = $enforceDate = date('Y-m-d', strtotime(trim(explode('-', $law->find('td', 4)->plaintext)[0])));
                    $endDate = strtotime(trim(explode('-', $law->find('td', 4)->plaintext)[1])) ? "'".date('Y-m-d', strtotime(trim(explode('-', $law->find('td', 4)->innertext)[1])))."'":"NULL";
                $ID = $scraper.':'.explode('/', $source)[4];
                $name = fixQuotes($law->find('td', 0)->find('a', 0)->plaintext, $lang);
                //Gets the regime
                if (strtotime($enactDate) < strtotime('1918-02-24')) $regime = '{"et":"Venemaa Keisririik", "en":"The Russian Empire"}';
                else if (strtotime($enactDate) < strtotime('1940-06-16')) $regime = '{"et":"Eesti Vabariik", "en":"The Republic of Estonia"}';
                else if (strtotime($enactDate) < strtotime('1944-09-22')) $regime = '{"et":"Saksa sõjaväeline administratsioon", "en":"German Military Administration"}';
                else if (strtotime($enactDate) < strtotime('1990-08-20')) $regime = '{"et":"Eesti NSV", "en":"Estonian SSR"}';
                else $regime = '{"et":"Eesti Vabariik", "en":"The Republic of Estonia"}';
                $origin = $origins[$law->find('td', 1)->plaintext][0];
                //Gets the rest of the values
                $type = $types[$law->find('td', 2)->plaintext];
                if (str_contains(' - kehtetu', $law->find('td', 4)->plaintext)) $status = 'Repealed';
                    else $status = 'In Force';
                $topic = $origins[$law->find('td', 1)->plaintext][1];

                //Creates SQL
                /*$SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $name;
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);
                        
                        //JSONifies the origin
                        $compoundedOrigin = json_decode($row['origin'], true);
                        $compoundedOrigin[$lang] = $origin;
                        $origin = json_encode($compoundedOrigin, JSON_UNESCAPED_UNICODE);

                        //JSONifies the href
                        $compoundedSource = json_decode($row['source'], true);
                        $compoundedSource[$lang] = $source;
                        $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                        //$SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `origin`='".$origin."' WHERE `ID`='".$ID."'";
                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                    }
                } else {*/
                    //JSONifies the name, source and origin
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `endDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `topic`, `source`)
                            VALUES ('".$enactDate."', '".$enforceDate."', ".$endDate.", '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$origin."', '".$publisher."', '".$type."', '".$status."', ".$topic.", '".$source."')";
                //}

                //Executes the SQL
                echo '<a href="https://www.riigiteataja.ee/'.$locale.'?sakk=koik_otsitavad&leht='.$page.'&kuvaKoik=true&sorteeri=kehtivuseAlgus&kasvav=true" target="_blank">p'.$page.':</a> '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
        
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    }

    //Closes the connections
    $conn->close(); $conn2->close();
?>