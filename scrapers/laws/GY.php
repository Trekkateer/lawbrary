<?php //Guyana
    //Settings
    $test = false; $scraper = 'GY';
    $start = 0;//Which law to start from
    $step = 10;//The number of laws per page
    $limit = null;//How many pages there are

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
    $saveDate = date('Y-m-d'); $country = '["GY"]'; $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The Parliament of the Co-operative Republic of Guyana"}';

    //Gets the limit
    $dom = file_get_html('https://www.parliament.gov.gy/publications/acts-of-parliament');
    $limit = $limit ?? explode('P', explode('/', $dom->find('a.page-last', 0)->href)[5])[1];
    //Loops through the pages
    for ($offset = $start; $offset <= $limit; $offset += $step) {
        //Gets the HTML
        $dom = file_get_html('https://www.parliament.gov.gy/publications/acts-of-parliament/P'.$offset);

        //Processes the data in the table
        $laws = $dom->find('table', 0)->find('tr.odd, tr.even');
        foreach ($laws as $lawNum => $law) {
            //Skips a line if the date is null
            if ($law->find('td', 1)->find('span.common-td', 0)->plaintext === 'n/a') {echo 'skipped a line (enactment date not available)<br/>'; continue;}

            //Gets values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(strtr($law->find('td', 1)->find('span.common-td', 0)->plaintext, array(','=>' '))));
            $ID = $scraper.':'.str_pad($offset+$lawNum, 4, "0", STR_PAD_LEFT); //The ID in the href is not consistent if the PDF is 'javascript:void(0);' so we make our own ID
            //Gets regime
            switch(true) {
                case strtotime('1667-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1831-01-01'):
                    $regime = '{"en":"Dutch Essequibo"}';
                    break;
                case strtotime('1831-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('26 May 1966'):
                    $regime = '{"en":"British Guiana"}';
                    break;
                case strtotime('26 May 1966') < strtotime($enactDate) && strtotime($enactDate) < strtotime('23 February 1970'):
                    $regime = '{"en":"The Republic of Guyana"}';
                    break;
                case strtotime('23 February 1970') < strtotime($enactDate) && strtotime($enactDate) < strtotime('6 October 1980'):
                    $regime = '{"en":"The Republic of Guyana"}';
                    break;
                case strtotime('6 October 1980') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                    $regime = '{"en":"The Co-operative Republic of Guyana"}';
                    break;
            }
            //Gets the rest of the values
            $name = fixQuotes(ucfirst(str_replace(['( ', ' )', 'amendment', 'ACT'], ['(', ')', 'Amendment', 'Act'], trim($law->find('td', 0)->find('a', 0)->plaintext))), 'en');
            $isAmend = str_contains($name, 'Amendment') ? 1:0;
            $source = $law->find('td', 0)->find('a', 0)->href;
            $PDF = $law->find('td', 3)->find('a', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = (str_contains($PDF, 'javascript:void(0);') || str_contains($PDF, '.doc')) ? 'NULL':'\'{"en":"'.$PDF.'"}\'';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `isAmend`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', ".$isAmend.", '".$status."', '".$source."', ".$PDF.")";
            echo 'P'.$offset.': '.$SQL2.'<br/>';
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