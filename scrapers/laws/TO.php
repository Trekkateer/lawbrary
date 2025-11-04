<?php //Tonga
    //Settings
    $test = false; $scraper = 'TO';

    //Opens my library
    include '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $dom = new simple_html_dom();

    //Suppress warnings only
    error_reporting(E_ALL & ~E_WARNING);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Makes sure certain things get capitalized TODO: Add more
    $capitalizees    = ['the asian development bank', ' the multilateral investment guarantee agency', ' the international centre for the settlement of investment disputes', ' the international development association', ' international bank for reconstruction and development', ' the international finance corporation', ' international monetary fund', 'the kingdom', "the bailiffs' office", 'the office of the anti-corruption commissioner', "pa'anga", ' red cross', ' supreme court', ' kingdom of tonga', ' tonga', ' united nations'];
    $capitalizations = ['the Asian Development Bank', ' the Multilateral Investment Guarantee Agency', ' the International Centre for the Settlement of Investment Disputes', ' the International Development Association', ' International Bank for Reconstruction and Development', ' the International Finance Corporation', ' International Monetary Fund', 'the Kingdom', "the Bailiffs' Office", 'the Office of the Anti-corruption Commissioner', "Pa'anga", ' Red Cross', ' Supreme Court', ' Kingdom of Tonga', ' Tonga', ' United Nations'];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["TO"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"to":"Ko e ‘Ofisi ‘o e Loea Lahi", "en":"The Office of the Attorney General"}';

    //Loops through all the letters
    foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
        //Creates curl handler for search
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ago.gov.to/cms/legislation/current-revised-edition/by-title.html?view=acts_alpha',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'submit4' => $letter,
                'pointintime_post' => $saveDate.' 00:00:00',
            ),
            CURLOPT_HTTPHEADER => array(
                'Cookie: f8751c1ee897553d05999c058046501d=1db6bea759b4b5dea33bf3642f7f8ba4'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);

        //Creates HTML DOM
        $dom->load($response);

        //Processes the data in the table
        $laws = $dom->find('tr.row0');
        foreach ($laws as $law) {
            //Gets raw values
            $enactDate = $law->find('td', 6)->find('div', 0)->{'data-content'};
            $ID = $law->find('td', 1)->find('div', 0)->{'data-content'};
            $nameEN = $law->find('td', 3)->find('a', 0)->plaintext; $nameTO = $law->find('td', 4)->find('a', 0)->plaintext;
            $summary = $law->find('td', 1)->find('div', 0)->{'data-content'};
            $topic = $law->find('td', 5)->find('div', 0)->{'data-content'};
            $sourceEN = $law->find('td', 3)->find('a', 0)->href; $sourceTO = $law->find('td', 4)->find('a', 0)->href;
            
            //Sanitizes some values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(explode('<br />', $enactDate)[1]));
            $ID = $scraper.':'.explode('</strong>', explode('<br />', $ID)[0])[1];
            $regime = strtotime($enactDate) < strtotime('4 June 1970') ? '{"to":"Ko e ‘Emipaea Pilitāniá", "en":"The British Empire"}':'{"to":"Ko e Pule’anga ‘o Tonga", "en":"The Kingdom of Tonga"}';
            $nameEN = fixQuotes(trim(explode('&nbsp;', $nameEN)[0]), 'en'); $nameTO = fixQuotes(trim(explode('&nbsp;', $nameTO)[0]), 'to');
            $summary = fixQuotes(strtolower(explode('<br />', $summary)[2] ?? 'NULL'), 'en');
                $summary = $summary !== 'null' && str_starts_with($summary, 'an act') ? ucfirst(str_replace($capitalizees, $capitalizations, $summary)):'NULL';
            $isAmend = str_contains(strtolower($nameEN), 'amendment') ? 1:0;
            $topic = explode(" - ", $topic)[1];
            $sourceEN = 'https://ago.gov.to'.$sourceEN; $sourceTO = 'https://ago.gov.to'.$sourceTO;

            //JSONifies the values
            $name = '{"to":"'.$nameTO.'", "en":"'.$nameEN.'"}';
            $summary = $summary === 'NULL' ? $summary:"'{\"en\":\"".$summary."\"}'";
            $topic = '{"en":"'.$topic.'"}';
            $source = '{"to":"'.$sourceTO.'", "en":"'.$sourceEN.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `summary`, `type`, `topic`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', ".$summary.", '".$type."', '".$topic."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
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