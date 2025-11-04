<?php //Kosovo
    //!!THEY PUT UP A CLOUDFLARE WALL, SO I CAN"T SCRAPE IT!!
    
    //Settings
    $test = true; $scraper = 'KOSOVO';
    $start = [1999, 6];//Which year and month to start from
    $limit = [NULL, NULL];//Which year and month to end at

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
    $saveDate = date('Y-m-d'); $country = '["KOSOVO"]';
    $publisher = '{"sq":"Kuvendi i Kosovës","en":"The Assembly of Kosovo"}';

    //Gets the limit
    $limit[0] = $limit[0] ?? Date('Y');
    $limit[1] = $limit[1] ?? Date('m');
    //Loops through the years
    for ($year = $start[0]; $year <= $limit[0]; $year++) {
        //Loops through the months
        for ($month = $start[1]; $month <= $limit[1]; $month++) {
            //Processes the data
            $dom = file_get_html('http://old.kuvendikosoves.org/?cid=1,193&date='.$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT));
            $laws = $dom->find('div#details', 0)->find('div.detail-list');
            foreach($laws as $law) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = Date('Y-m-d', strtotime(explode('<br>', $law->find('div.bottom', 0)->find('span.right', 0)->innertext)[1]));
                $ID = $scraper.':'.strtr($law->find('div.center', 0)->find('span.orange', 0)->plaintext, array('/'=>'', '-'=>''));
                $regime = '{"sq":"Republika e Kosovës","sr":"Republika Kosova","en":"The Republic of Kosovo"}';
                $name = fix_quotes($law->find('div.center', 0)->find('h4', 0)->plaintext, 'sq');
                $type = 'Act'; if (str_contains($name, 'ndryshimin')) {$isAmmend = 0;}
                $status = 'Valid';
                $summary = fix_quotes(trim($law->find('div.center', 0)->find('p', 0)->plaintext), 'sq');
                    if (str_contains($summary, 'Vërejtje: ') || str_contains($summary, 'Ligji është miratuar')) {$summary = NULL;}
                $source = strtr('http://old.kuvendikosoves.org/'.$law->find('div.center', 0)->find('a.pdf-small', 0)->href, array("'"=>"%27"));

                //JSONifies the values
                $name = '{"sq":"'.$name.'"}';
                $summary = $summary === NULL ? 'NULL':'\'{"sq":"'.$summary.'"}\'';
                $source = '{"sq":"'.$source.'"}'; $PDF = $source;
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `country`, `regime`, `publisher`, `name`, `type`, `isAmmend`, `status`, `summary`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$country."', '".$regime."', '".$publisher."', '".$name."', '".$type."', ".$isAmmend.", '".$status."', ".$summary.", '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }
    
    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `Updated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>