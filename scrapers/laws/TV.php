<?php //Tuvalu
    //Settings
    $test = false; $scraper = 'TV';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Makes sure certain things get capitalized and fixes some spellings TODO: Add more
    $sanitizeSummary = array(
        ' british' => ' British',
        ' the crown' => ' the Crown',
        ' the ekalesia a kelisiano' => ' the Ekalesia a Kelisiano',
        ' england' => ' England',
        ' ireland' => ' Ireland',
        ' falekaupule' => ' Falekaupule',
        ' the multilateral investment guarantee agency' => ' the Multilateral Investment Guarantee Agency',
        ' the international centre for the settlement of investment disputes' => ' the International Centre for the Settlement of Investment Disputes',
        ' the international development association' => ' the International Development Association',
        ' international bank for reconstruction and development' => ' International Bank for Reconstruction and Development',
        ' the international finance corporation' => ' the International Finance Corporation',
        ' international monetary fund' => ' International Monetary Fund',
        ' kaupule' => ' Kaupule',
        ' red cross' => ' Red Cross',
        ' revided' => ' revised',
        ' the tuvalu philatelic bureau' => ' the Tuvalu Philatelic Bureau',
        ' the tuvalu telecommunications corporation' => ' the Tuvalu Telecommunications Corporation',
        ' tuvalu' => ' Tuvalu'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["TV"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The Office of the Attorney General"}';

    //Loops through all the letters
    foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
        //Creates curl handler for search
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tuvalu-legislation.tv/cms/legislation/acts_only/by-alphabetical-order.html',
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
                'Cookie: 802cd4939d71262452ffebd08a194ae8=66646a6bd2734861c61da37716832cfd'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);

        //Creates HTML DOM
        $dom->load($response);

        //Processes the data in the table
        $laws = $dom->find('tr.row0');
        foreach ($laws as $law) {
            //Gets raw values
            $enactDate = $law->find('td', 5)->find('div', 0)->{'data-bs-content'};
            $ID = $law->find('td', 1)->find('div', 0)->{'data-bs-content'};
            $name = $law->find('td', 3)->find('a', 0)->plaintext;
            $summary = $law->find('td', 1)->find('div', 0)->{'data-bs-content'};
            $topic = $law->find('td', 4)->find('div', 0)->{'data-bs-content'};
            $source = $law->find('td', 3)->find('a', 0)->href;
            
            //Sanitizes the values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($enactDate));
            $ID = $scraper.':'.explode('<hr', explode(': ', $ID)[1])[0];
            $name = fixQuotes(explode('&nbsp;', $name)[0], 'en');
            $regime = strtotime($enactDate) > strtotime('1 October 1978') ? '{"en":"Tuvalu"}':'{"en":"The British Empire"}';
            //$isAmend = str_contains($name, 'Amendment') ? 1:0;
            $summary = strtolower(explode("<hr class='notes'>", $summary)[1] ?? 'NULL');
                $summary = fixQuotes(($summary !== 'null' && str_starts_with($summary, 'an act')) ? ucfirst(str_replace(array_keys($sanitizeSummary), array_values($sanitizeSummary), $summary)):'NULL', 'en');
            $topic = fixQuotes(trim(explode('<br></span>', explode("'>", $topic)[1])[0]), 'en');
            $source = 'https://laws.bahamas.gov.bs'.$source;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $summary = $summary === 'NULL' ? $summary:"'{\"en\":\"".$summary."\"}'";
            $topic = '{"en":"'.$topic.'"}';
            $source = '{"en":"'.$source.'"}'; $PDF = $source;
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `summary`, `type`, `topic`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', ".$summary.", '".$type."', '".$topic."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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