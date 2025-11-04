<?php
    //Settings
    $test = false; $scraper = 'IE';
    $start = 1922;//Which year to start from
    $limit = null;//Which year to stop at

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

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["IE"]';
    $origin = '{"en":"the Oireachtas","ga":"Oireachtas na hÉireann"}';
    $publisher = '{"en":"The Irish Statute Book","ga":"An Leabhar Reachtanna Éireann"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Processes the data
        $dom = file_get_html('https://www.irishstatutebook.ie/eli/'.$page.'/act#');

        //Gets the regime
        //Default regime (from 18 April 1949 to present)
        $regime = '{"en":"The Republic of Ireland", "ga":"Poblacht na hÉireann"}';
        if (strtotime($page."-01-01") < -653443200) {
            //From 29 December 1937 to 18 April 1949
            $regime = '{"en":"Ireland", "ga":"Éire"}';
        } if (strtotime($page."-01-01") < -1010102400) {
            //From 6 December 1922 to 29 December 1937
            $regime = '{"en":"The Irish Free State", "ga":"Saorstát Éireann"}';
        } /*if (strtotime($page) < -1485475200) {
            //From 1 January 1801 to 6 December 1922
            $regime = '{"en":"The United Kingdom of Great Britain and Ireland", "ga":"An Ríocht Aontaithe na Breataine Móire agus na hÉireann"}';
        } if (strtotime($page) < -5333126400) {
            //From 18 June 1542 to 1 January 1801
            $regime = '{"en":"The Kingdom of Ireland", "ga":"Ríocht na hÉireann"}';
        }*/

        //Public Laws
        $publicLaws = $dom->find('#public-acts-dtb', 0)->find('tbody', 0)->find('tr');
        foreach ($publicLaws as $publicLaw) {
            //Gets values
            $enactDate = $enforceDate = $lastactDate = $page."-01-01";
            $ID = $scraper.':'.$page.str_pad($publicLaw->find('td', 0)->innertext, 4, '0', STR_PAD_LEFT);
            $name = fixQuotes(str_replace('Act, ', 'Act', explode($page, str_replace($page.'.', $page, $publicLaw->find('td', 1)->find('a', 0)->innertext))[0]), 'en');
            $type = 'Public Act'; $status = 'Valid';
            $isAmend = str_contains($name, 'Amendment') ? 1:0;
            $source = strtr('https://www.irishstatutebook.ie/eli/'.$page.'/'.$publicLaw->find('td', 1)->find('a', 0)->href, array('"'=>'%22', "'"=>'%27'));
            $PDF = str_replace('.html', '.pdf', $source);

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = '{"en":"'.$PDF.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `country`, `regime`, `publisher`, `name`, `type`, `isAmend`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$country."', '".$regime."', '".$publisher."', '".$name."', '".$type."', ".$isAmend.", '".$status."', '".$source."', '".$PDF."')";
            echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }

        //Private Laws
        if (isset($dom->find('#private-acts-dtb')[0])) {
            $privateLaws = $dom->find('#private-acts-dtb', 0)->find('tbody', 0)->find('tr');
            foreach ($privateLaws as $privateLaw) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = $page."-01-01";
                $ID = $country.':'.$page.str_pad(explode('No. ', explode('/'.$page.' — ', $privateLaw->find('td', 0)->find('a', 0)->innertext)[0])[1], 4, '0', STR_PAD_LEFT);
                $name = fixQuotes(str_replace('Act, ', 'Act', explode($page, str_replace($page.'.', $page, explode('/'.$page.' — ', $privateLaw->find('td', 0)->find('a', 0)->innertext)[1]))[0]), 'en');
                $type = 'Private Act'; $status = 'Valid';
                $isAmend = str_contains($name, 'Amendment') ? 1:0;
                $source = strtr('https://www.irishstatutebook.ie/eli/'.$page.'/'.$privateLaw->find('td', 0)->find('a', 0)->href, array('"'=>'%22', "'"=>'%27'));
                $PDF = str_replace('.html', '.pdf', $source);
    
                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$PDF.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `country`, `regime`, `publisher`, `name`, `type`, `isAmend`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$country."', '".$regime."', '".$publisher."', '".$name."', '".$type."', ".$isAmend.", '".$status."', '".$source."', '".$PDF."')";
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }
    
    //Connects to the content database
    $username="ug0iy8zo9nryq";  $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>