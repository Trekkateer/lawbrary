<html><body>
    <?php
        //Settings
        $test = true; $country = 'CA';

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Loops through English and French
        $alphabet = array('en'=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'Y'     ),
                          'fr'=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',      'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',      'V', 'W', 'Y', 'Z'));
        foreach (array('en'=>'eng/acts/', 'fr'=>'fra/lois/') as $lang=>$locale) {
            //Loops through all the letters
            foreach ($alphabet[$lang] as $letter) {
                //Gets the HTML 
                $html_dom = file_get_html('https://www.laws-lois.justice.gc.ca/'.$locale.$letter.'.html'); //echo $html_dom;

                //Processes the data in the table
                $body_rows = $html_dom->find('div.contentBlock > ul', 0)->find('li');
                foreach ($body_rows as $body_row) {
                    //Gets the ID
                    $ID = $country.'-'.str_replace('-', '', explode('/', $body_row->find('a[class="TocTitle"]', 0)->href)[0]);
                    if (!str_contains($ID, 'CA-Z')) {
                        //Gets values
                        $enactDate = date('Y-m-d', strtotime(strtr(explode('-', str_replace(', ', '', explode(', c', explode('</abbr>', $body_row->find('div.linksBox', 0)->find('span.htmlLink', 0)->innertext)[1])[0]))[0], array('('=>'', ')'=>'')))); $enforceDate = $enactDate; $lastactDate = $enactDate;
                        if (str_contains($body_row->find('a.TocTitle', 0)->innertext, '<span>')) {
                            $name = trim($body_row->find('a.TocTitle', 0)->find('span')->innertext);
                        } else {$name = trim($body_row->find('a.TocTitle', 0)->innertext);}
                        //Gets the regime
                        switch(true) {
                            case strtotime($enactDate) < strtotime('1 July 1867'):
                                $regime = 'The United Kingdom';
                                break;
                            case strtotime('1 July 1867') < strtotime($enactDate) && strtotime($enactDate) < strtotime('17 April 1982'):
                                $regime = 'The Canadian Confederation';
                                break;
                            case strtotime('17 April 1982') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                                $regime = 'Canada';
                                break;
                        }
                        //Gets the rest of the values
                        $type = 'Act';
                        if (str_contains($name, 'Repealed') || str_contains($name, 'Abrogée')) {
                            $status = 'Repealed';
                        } else {$status = 'Valid';}
                        $source = 'https://www.laws-lois.justice.gc.ca/'.$locale.$body_row->find('a.TocTitle', 0)->href;
                        $PDF = 'https://www.laws-lois.justice.gc.ca'.$body_row->find('div.linksBox', 0)->find('span.pdfLink', 0)->find('a', 0)->href;

                        //Makes sure there are no quotes in the title
                        if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                        //if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                        //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                        //Creates SQL
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

                                //JSONifies the PDF
                                $compoundedPDF = json_decode($row['PDF'], true);
                                $compoundedPDF[$lang] = $PDF;
                                $PDF = json_encode($compoundedPDF, JSON_UNESCAPED_UNICODE);

                                $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                            }
                        } else {
                            //JSONifies the name and href
                            $name = '{"'.$lang.'":"'.$name.'"}';
                            $source = '{"'.$lang.'":"'.$source.'"}';
                            $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                            //Creates SQL
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`)
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                        }

                        //Executes the SQL
                        echo $SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
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
        }
    ?>
</body></html>