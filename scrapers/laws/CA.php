<?php //Canada
    //Settings
    $test = false; $scraper = 'CA';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["CA"]'; $type = 'Act';
    $publisher = '{"en":"The Justice Laws Website","fr":"Le site Web des lois du Canada"}';

    //Loops through English and French
    $alphabet = array('en'=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'Y'     ),
                        'fr'=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',      'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',      'V', 'W', 'Y', 'Z'));
    foreach (array('en'=>'eng/acts/', 'fr'=>'fra/lois/') as $lang=>$locale) {
        //Loops through all the letters
        foreach ($alphabet[$lang] as $letter) {
            //Gets the HTML 
            $dom = file_get_html('https://www.laws-lois.justice.gc.ca/'.$locale.$letter.'.html'); //echo $dom;

            //Processes the data in the table
            $laws = $dom->find('div.contentBlock > ul', 0)->find('li');
            foreach ($laws as $law) {
                //Gets the ID
                $ID = $scraper.':'.str_replace('-', '', explode('/', $law->find('a[class="TocTitle"]', 0)->href)[0]);
                if (!str_contains($ID, 'CA:Z')) {//Skips some weird list things
                    //Gets values
                    $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(strtr(explode('-', str_replace(', ', '', explode(', c', explode('</abbr>', $law->find('div.linksBox', 0)->find('span.htmlLink', 0)->innertext)[1])[0]))[0], array('('=>'', ')'=>''))));
                    if (str_contains($law->find('a.TocTitle', 0)->plaintext, '<span>')) {
                        $name = fixQuotes(str_replace('</sup>', '', trim($law->find('a.TocTitle', 0)->find('span', 0)->plaintext)), $lang);
                    } else {$name = fixQuotes(str_replace('</sup>', '', trim($law->find('a.TocTitle', 0)->plaintext)), $lang);}
                    //Gets the regime
                    switch(true) {
                        case strtotime($enactDate) < strtotime('1 July 1867'):
                            $regime = '{"en":"The United Kingdom", "fr":"Le Royaume-Uni"}';
                            break;
                        case strtotime('1 July 1867') < strtotime($enactDate) && strtotime($enactDate) < strtotime('17 April 1982'):
                            $regime = '{"en":"The Canadian Confederation", "fr":"La Confédération canadienne"}';
                            break;
                        case strtotime('17 April 1982') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                            $regime = '{"en":"Canada", "fr":"Le Canada"}';
                            break;
                    }
                    //Gets the rest of the values
                    if (str_contains($name, 'Repealed') || str_contains($name, 'Abrogée')) {
                        $status = 'Repealed';
                    } else {$status = 'Valid';}
                    $source = 'https://www.laws-lois.justice.gc.ca/'.$locale.$law->find('a.TocTitle', 0)->href;
                    $PDF = 'https://www.laws-lois.justice.gc.ca'.$law->find('div.linksBox', 0)->find('span.pdfLink', 0)->find('a', 0)->href;

                    //Creates SQL
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

                            //JSONifies the PDF
                            $compoundedPDF = json_decode($row['PDF'], true);
                            $compoundedPDF[$lang] = $PDF;
                            $PDF = json_encode($compoundedPDF, JSON_UNESCAPED_UNICODE);

                            $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';
                        $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                    }

                    //Executes the SQL
                    echo $letter.': '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
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
    }
?>