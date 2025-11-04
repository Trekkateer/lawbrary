<?php //Nauru
    //Settings
    $test = false; $scraper = 'NR';
    $start = 0;//Which page to start from
    $step = 20;//How many laws are on each page
    $limit = null;//How many pages there are

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["NR"]';
    $publisher = '{"en":"RONLAW - Nauru’s Online Legal Database", "na":"RONLAW"}';

    //Gets the limit
    $dom = file_get_html('http://ronlaw.gov.nr/nauru_lpms/index.php/act/browse/0/letter/any/year/any/filter/any'); echo $dom;
    $limit = $limit ?? explode(' of ', $dom->find('div.rounded-corners', 0)->find('div.content-area', 0)->find('em', 0)->plaintext)[1];
    //Loops through the pages
    for ($offset = $start; $offset <= $limit; $offset+=$step) {
        //Gets the HTML
        $dom = file_get_html('http://ronlaw.gov.nr/nauru_lpms/index.php/act/browse/'.$offset.'/letter/any/year/any/filter/any');// echo $dom;

        //Processes the data in the table
        $laws = $dom->find('ul.results', 0)->find('li');
        foreach ($laws as $law) {
            //Gets values
            $name = fixQuotes(strtr(trim($law->find('a', 0)->plaintext), array(' - '=>'-')), 'en');
            $enactYear = end(explode(' ', str_replace('-', ' ', $name)));
                $enactDate = $enforceDate = $lastActDate = $enactYear.'-01-01';
            $ID = $scraper.':'.explode('/', $law->find('a', 0)->href)[7];
            //Gets the regime
            if (strtotime($enactDate) <= strtotime('9 September 1914')) {
                $regime = '{"en":"The German Empire", "na":"Impero a Germany"}';
            } elseif (strtotime($enactDate) <= strtotime('29 September 1923')) {
                $regime = '{"en":"The British Empire", "na":"Impero a Britannia"}';
            } elseif (strtotime($enactDate) <= strtotime('26 August 1942')) {
                $regime = '{"en":"The Commonwealth of Australia", "na":"Otereiriya"}';
            } elseif (strtotime($enactDate) <= strtotime('13 September 1945')) {
                $regime = '{"en":"The Empire of Japan", "na":"Impero a Giappone"}';
            } elseif (strtotime($enactDate) <= strtotime('31 January 1968')) {
                $regime = '{"en":"The Commonwealth of Australia", "na":"Otereiriya"}';
            } else {
                $regime = '{"en":"The Republic of Nauru", "na":"Repubrikin Naoero"}';
            }
            //Gets the rest of the values
            $type = end(explode(' ', explode(' (No. ', explode(' '.$enactYear, trim(preg_replace('/[0-9-]/', '', $name)))[0])[0]));
            $isAmend = str_contains($name, 'Amendment') ? 1:0;
            $status = str_contains($law->plaintext, 'repealed') ? 'Repealed':'Valid';
            $source = $law->find('a', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastActDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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

    //Closes the connections
    $conn->close(); $conn2->close();
?>