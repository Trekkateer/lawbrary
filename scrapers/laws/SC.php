<?php //The Seychelles
    //Settings
    $test = false; $scraper = 'SC';
    $start = 0;//Which page to start from
    $limit = null;//Which page to end at

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

    //Sanitizes the data
    $sanitizeName = [
        '0f'=>'of', 'OF'=>'of', 'of 222'=>'of 2022', 'of 223'=>'of 2023',
        'S.I.'=>'SI ', 'S.I'=>'SI ', 'S. I.'=>'SI ', 'S.1.'=>'SI ',
        '(Bill. No'=>'(Bill No.', '(bill No.'=>'(Bill No.', '(Bill no.'=>'(Bill No.', '(bill no.'=>'(Bill No.',
        '( '=>'(', ' )'=>')',
        '  '=>' ',
    ];
    $sanitizeURL = [
        'S.I.'=>'SI', ' of 20'=>'%2020', 'No.'=>'No%20', ', 20'=>'%2020',
        ' '=>'%20', "'"=>'', '’'=>'%27',
    ];

    //Makes sure the ID is 3 digits long
    $randNum = 0;
    $zero_buffer = function ($inputNum, $outputLen=3) use (&$randNum) {
        $outputNum = trim($inputNum);
        if ($outputNum === '') {$outputNum = $randNum; $randNum++;}
        while (strlen($outputNum) < $outputLen) {$outputNum = '0'.$outputNum;}
        return $outputNum;
    };

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["SC"]';
    $status = 'Valid';
    $publisher = '{"en":"The National Assembly of Seychelles", "fr":"L’Assemblée nationale des Seychelles"}';

    //Gets the limit
    $dom = file_get_html('https://www.nationalassembly.sc/legislation');
    $limit = $limit ?? explode('page=', $dom->find('a[title="Go to last page"]', 0)->href)[1];
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Processes the data
        $dom = file_get_html('https://www.nationalassembly.sc/legislation?type=All&search=&page='.$page);
        $laws = $dom->find('table.cols-4', 0)->find('tbody', 0)->find('tr');
        foreach ($laws as $law) {
            //Gets the name
            $name = fixQuotes(trim(strtr($law->find('td', 0)->plaintext, $sanitizeName)), 'en');
            //Gets the type and the rest of the values
            $type = explode(' (', trim($law->find('td', 1)->plaintext))[0];
            if ($type === 'Statutory Instrument') {
                $enactYear = end(explode(' ', trim(explode('-', $name)[0], ' )')));
                $ID = $scraper.':SI'.$zero_buffer(explode(' of ', explode('SI ', $name)[1])[0]).$enactYear;
                //$PDF = 'https://www.nationalassembly.sc/sites/default/files/'.trim(explode('/', ($law->find('td', 3)->plaintext))[2]).'-'.explode('/', $law->find('td', 3)->plaintext)[1].'/'.strtr(trim(explode(', '.$enactYear, $name)[0]).', '.$enactYear, $sanitizeURL).'.pdf';
            } else if ($type === 'Bill') {
                $enactYear = end(explode(' ', trim($name, ' )')));
                $ID = $scraper.':B'.$zero_buffer(explode(' of ', trim(explode('(Bill No.', $name)[1]))[0]).$enactYear;
                //$PDF = 'https://www.nationalassembly.sc/sites/default/files/'.trim(explode('/', $law->find('td', 3)->plaintext)[2]).'-'.explode('/', $law->find('td', 3)->plaintext)[1].'/Bill '.strtr(trim(explode('(Bill No.', $name)[1]), array(' of '=>' ', ')'=>'')).' - '.strtr($name, $sanitizeURL).'.pdf';
            }
            //$isAmend = str_contains($name, 'Amendment') ? 1:0;
            $source = 'https://www.nationalassembly.sc'.$law->find('td', 0)->find('a', 0)->href;
            //Gets the regime
            if (strtotime($enactYear.'-01-01') < strtotime('29 June 1976')) {
                $regime = '{"en":"The British Empire", "fr":"L’Empire Britannique"}';
            } else {$regime = '{"en":"The Republic of Seychelles", "fr":"La République des Seychelles"}';}

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            //$PDF = '{"en":"'.$PDF.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactYear."-01-01', '".$enactYear."-01-01', '".$enactYear."-01-01', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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