<?php //The Bahamas
    //Settings
    $test = false; $scraper = 'BS';

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

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["BS"]'; $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The Office of the Attorney General and Ministry of Legal Affairs"}';

    //Loops through all the letters
    foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
        //Creates curl handler for search
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://laws.bahamas.gov.bs/cms/legislation/acts_only/by-alphabetical-order.html',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'pointintime_post' => $saveDate.' 00:00:00',
                'submit4' => $letter,
                'submit4' => $letter,
                'pointintime_post_alpha' => $saveDate.' 00:00:00'
            ),
            CURLOPT_HTTPHEADER => array(
                'Cookie: f8751c1ee897553d05999c058046501d=1db6bea759b4b5dea33bf3642f7f8ba4'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);

        //Creates HTML DOM
        $dom = new simple_html_dom($response);

        //Processes the data in the table
        $docs = $dom->find('tr.row0');
        foreach ($docs as $doc) {
            //Gets the values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($doc->find('td', 4)->find('div', 0)->{'data-bs-content'}));
            $ID = $scraper.':'.explode('<hr', explode(': ', $doc->find('td', 0)->find('div', 0)->{'data-bs-content'})[1])[0];
            if (strtotime($doc->find('td', 4)->find('div', 0)->{'data-bs-content'}) <= 111106800) {$regime = '{"en":"The Crown Colony of the Bahamas"}';}
                else {$regime = '{"en":"The Commonwealth of the Bahamas"}';};
            $name = fixQuotes(explode('&nbsp', $doc->find('td', 2)->find('a', 0)->innertext)[0], 'en');
            $summary = fixQuotes(isset(explode("<hr class='notes'>", $doc->find('td', 0)->find('div', 0)->{'data-bs-content'})[1]) ? ucfirst(strtolower(explode("<hr class='notes'>", $doc->find('td', 0)->find('div', 0)->{'data-bs-content'})[1])):NULL, 'en');
            $topic = fixQuotes(trim(explode('<br></span>', explode("'>", $doc->find('td', 3)->find('div', 0)->{'data-bs-content'})[1])[0]), 'en');
            $source = $PDF = 'https://laws.bahamas.gov.bs'.$doc->find('td', 2)->find('a', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $summary = $summary === NULL ? 'NULL':"'{\"en\":\"".$summary."\"}'";
            $topic = '{"en":"'.$topic.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = '{"en":"'.$PDF.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `summary`, `type`, `topic`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', ".$summary.", '".$type."', '".$topic."', '".$status."', '".$source."', '".$PDF."')";
            echo $letter.': '.$SQL2.'<br/>';
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