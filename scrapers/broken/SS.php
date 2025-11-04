<?php //South Sudan
    //TODO: Find a better database

    //Settings
    $test = true; $scraper = 'SS';

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

    //Gets the HTML
    $dom = file_get_html('https://mojca.gov.ss/laws/');

    //Capitalizes titles with exceptions
    $exceptions = [
        'a', 'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'up'
    ];
    $ucwordsexcept = function($str, $delims=' ') use ($exceptions) {
        $out = array(strtolower(trim($str)));
        foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
            if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
            $out[$key+1] = '';
            foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                $out[$key+1] .= !in_array($word, $exceptions) ? mb_strtoupper($word[0], 'UTF-8').substr($word, 1).$delim:$word.$delim;
            }
            $out[$key+1] = rtrim($out[$key+1], $delim);
        }
        return ucfirst(end($out));
    };

    //Makes sure the ID is 2 digits long
    $randNum = 0;
    $zero_buffer = function ($inputNum='', $outputLen=2) use (&$randNum) {
        $outputNum = trim($inputNum);
        if ($outputNum === '') {$outputNum = $randNum; $randNum++;}
        while (strlen($outputNum) < $outputLen) {$outputNum = '0'.$outputNum;}
        return $outputNum;
    };

    //Gets the types
    $types = [
        ' Act '=>'Law',
        ' Circular '=>'Circular',
        ' Code '=>'Code',
        ' Constitution '=>'Constitution',
    ];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["SS"]';
    $publisher = '{"ar":"وزارة العدل والشؤون الدستورية", "en":"The Ministry of Justice and Constitutional Affairs"}';

    //Processes the data in the table
    $laws = $dom->find('div[data-id="72d4f93"]', 0)->find('ul.elementor-icon-list-items', 0)->find('li.elementor-icon-list-item > a');
    foreach ($laws as $law) {
        //Gets values
        $enactDate = $enforceDate = $lastActDate = end(explode(' ', explode('(Table of Sections)', trim($name, ')'))[0])).'-01-01';
        $ID = $scraper.':'.$zero_buffer().explode('-', $enactDate)[0];
        $name = $ucwordsexcept(strtr($law->find('span.elementor-icon-list-text', 0)->plaintext, array('-'=>' ')), ' (');
        //Gets the regime
        switch (true) {
            case (strtotime($enactDate) < strtotime('today')):
                $regime = '{"ar":"جمهورية جنوب السودان", "en":"The Republic of South Sudan"}';
            case (strtotime($enactDate) < strtotime('2011-07-09')):
                $regime = '{"ar":"منطقة جنوب السودان ذاتية الحكم", "en":"The Southern Sudan Autonomous Region"}';
            case (strtotime($enactDate) < strtotime('2005-07-09')):
                $regime = '{"ar":"جمهورية السودان", "en":"The Republic of the Sudan"}';
            case (strtotime($enactDate) < strtotime('1985-04-06')):
                $regime = '{"ar":"الجمهورية الديمقراطية السودانية", "en":"The Democratic Republic of the Sudan"}';
            case (strtotime($enactDate) < strtotime('1969-05-25')):
                $regime = '{"ar":"جمهورية السودان", "en":"The Republic of the Sudan"}';
            case (strtotime($enactDate) < strtotime('1956-01-01')):
                $regime = '{"ar":"الحماية البريطانية", "en":"The Anglo-Egyptian Condominium"}';
                break;
        }
        //Gets the type
        for ($i = 0; $i < count($types); $i++) {
            if (str_contains($name, array_keys($types)[$i])) {
                $type = array_values($types)[$i];
                break;
            }
        }
        //$isAmend = str_contains($name, 'Amendment') ? 1:0;
        //Gets the rest of the values
        $status = 'Valid';
        $source = $law->href;

        //Makes sure there are no quotes in the title
        $name = fixQuotes($name, 'en');

        //JSONifies the values
        $name = '{"en":"'.$name.'"}';
        $source = '{"en":"'.$source.'"}';
        
        //Inserts the new laws
        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastActDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
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