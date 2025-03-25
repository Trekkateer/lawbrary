<html><body>
    <?php
        //Settings
        $test = true; $country = 'KOSOVO';
        $start = [1999, 6];//Which year and month to start from
        $limit = [NULL, NULL];//Which year and month to end at

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

        //Makes sure every number has a certain number of digits
        function zero_buffer ($inputNum, $outputLen=2) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum) < $outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

        //Gets the limit
        $limit[0] = $limit[0] ?? Date('Y');
        $limit[1] = $limit[1] ?? Date('m');
        //Loops through the years
        for ($year = $start[0]; $year <= $limit[0]; $year++) {
            //Loops through the months
            for ($month = $start[1]; $month <= $limit[1]; $month++) {
                //Processes the data
                $html_dom = file_get_html('http://old.kuvendikosoves.org/?cid=1,193&date='.$year.'-'.zero_buffer($month));
                $laws = $html_dom->find('div#details')[0]->find('div.detail-list');
                foreach($laws as $law) {
                    //Gets values
                    $enactDate = Date('Y-m-d', strtotime(explode('<br>', $law->find('div.bottom')[0]->find('span.right')[0]->innertext)[1]));
                        $enforceDate = $enactDate; $lastactDate = $enactDate;
                    $ID = $country.'-'.strtr($law->find('div.center')[0]->find('span.orange')[0]->plaintext, array('/'=>'', '-'=>''));
                    $regime = 'The Republic of Kosovo';
                    $name = $law->find('div.center')[0]->find('h4')[0]->plaintext;
                    $type = 'Act'; if (str_contains($name, 'ndryshimin')) {$type = 'Amendment to '.$type;}
                    $status = 'Valid';
                    $summary = trim($law->find('div.center')[0]->find('p')[0]->plaintext);
                        if (str_contains($summary, 'Vërejtje: ') || str_contains($summary, 'Ligji është miratuar')) {$summary = NULL;}
                    $source = 'http://old.kuvendikosoves.org/'.$law->find('div.center')[0]->find('a.pdf-small')[0]->href;

                    //Makes sure there are no quotes in the title, summary, or the href
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                    if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}
                    if (str_contains($source, "'")) {$source = str_replace("'", "%27", $source);}

                    //JSONifies the values
                    $name = '{"sq":"'.$name.'"}';
                    $summary = $summary === NULL ? 'NULL':'\'{"sq":"'.$summary.'"}\'';
                    $source = '{"sq":"'.$source.'"}'; $pdf = $source;
                    
                    //Inserts the new laws
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `summary`, `source`, `pdf`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', ".$summary.", '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
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
    ?>
</body></html>