<?php //Tanzania
    //Settings
    $test = false; $scraper = 'TZ';

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
    $saveDate = date('Y-m-d'); $country = '["TZ"]';
    $regime = '{"en":"The United Republic of Tanzania", "sw":"Jamhuri ya Muungano wa Tanzania"}';
    $publisher = '{"en":"The Parliament of Tanzania", "sw":"Bunge la Tanzania"}';

    //Gets the data from API
    $laws = json_decode(file_get_contents('https://www.parliament.go.tz/polis/api/acts/async?_=1722797794113'), true)['data'];
    foreach ($laws as $law) {
        //Interprets the data
        $enactDate = $enforceDate = $lastactDate = $law['posted'].'-01-01';
        $ID = $scraper.':'.$law['id'];
        //Gets the name
        $name_sw = fixQuotes(strtr(trim($law['title_sw'], ' .'), array('  '=>' ')), 'sw');
        $name_en = fixQuotes(strtr(trim($law['title_en'], ' .'), array('  '=>' ')), 'en');
        if ($name_sw !== $name_en && !str_contains($name_sw, ' Act')) {
            $name = '{"sw":"'.$name_sw.'", "en":"'.$name_en.'"}';
        } else {
            if (str_contains($name_en, ' Act')) {
                $name = '{"en":"'.$name_en.'"}';
            } else {$name = '{"sw":"'.$name_sw.'"}';}
        }
        //Gets the rest of the values
        $type = 'Act'; if (str_contains($law['title_en'], 'Code')) {$type = 'Code';}
        $isAmend = (str_contains($law['title_sw'], 'Sheria ya Marekebisho') || str_contains($law['title_en'], 'Amendment')) ? 1:0;
        $status = 'Valid';
        $source = strtr('https://www.parliament.go.tz/polis'.strtr($law['file_url'], array(' '=>'%20')), array("'"=>"%27"));

        //JSONifies the title and source
        $source = '{"sw":"'.$source.'"}';

        //Creates SQL
        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `country`, `regime`, `publisher`, `name`, `type`, `status`, `source`, `PDF`) 
                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$country."', '".$regime."', '".$publisher."', '".$name."', '".$type."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
        if (!$test) {$conn->query($SQL2);}
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