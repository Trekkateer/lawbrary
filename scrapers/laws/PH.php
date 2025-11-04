<?php
    //Settings
    $test = false; $scraper = 'PH';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings
    error_reporting(E_ALL & ~E_WARNING);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the regimes
    $regime = array(
        0 => '{"en":"The United States of America"}',
        1 => '{"en":"The Commonwealth of the Philippines"}',
        2 => '{"en":"The Republic of the Philippines"}'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["PH"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The LawPhil Project", "es":"El Proyecto LawPhil", "tl":"Ang LawPhil Project"}';

    //Loops through the pages
    foreach (array('acts'=>0, 'comacts'=>1, 'bataspam'=>2, 'repacts'=>2) as $page => $regime) {
        //Gets the page limit
        $dom = file_get_html('https://lawphil.net/statutes/'.$page.'/'.$page.'.html');
        $laws = $dom->find('table#s-menu', 0)->find('tr.xy');
        foreach ($laws as $law) {
            //Gets values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(explode($law->find('td', 0)->find('a', 0)->plaintext, $law->find('td', 0)->plaintext)[1]));
            $ID = $scraper.':'.explode('-', $enactDate)[0].str_pad(end(explode(' ', $law->find('a', 0)->plaintext)), 5, '0', STR_PAD_LEFT);
            $name = fixQuotes(trim($law->find('td', 1)->plaintext), 'en');
            $regime = $regimes[$regime];
            $isAmend = str_contains(strtolower($name), 'amend') ? 1:0;
            $source = 'https://lawphil.net/'.$law->find('td', 0)->find('a', 0)->href;
            $PDF = $law->find('td', 2)->find('a', 0)->href ?? NULL;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = isset($PDF) ? '\'{"en":"https://lawphil.net/'.$PDF.'"}\'':'NULL';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', ".$PDF.")"; echo $SQL2.'<br/>';
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