<?php //Namibia
    //Settings
    $test = false; $scraper = 'NA';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings and notices
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sanitizes the statuses
    $statuses = array(
        ''=>'Valid',
        'Not yet in force'=>'Not in Force',
        'Not yet in Force.'=>'Not in Force',
        'Not yet in force.'=>'Not in Force',
        'Never brought into force in RSA or SWA.'=>'Not in Force',
        'Status uncertain.'=>'Uncertain',
    );
    
    //Sanitizes the names
    $sanitizeName = array('0f'=>'of', ', AG'=>'', ', 18'=>' 1 of 18', '(Britain)'=>'', '(RSA)'=>'', '(SA)'=>'', '- UPDATED'=>'');

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["NA"]';
    $publisher = '{"en":"The Legal Assistance Centre"}';

    //Gets data
    $dom = file_get_html('https://www.lac.org.na/index.php/laws/statutes/');
    $laws = $dom->find('table', 0)->find('tbody', 0)->find('tr');
    for ($i = 1; $i < count($laws); $i++) {$law = $laws[$i];
        //Gets the values
        $name_dom = new simple_html_dom(explode('<a href="', $law->find('td', 0)->innertext)[0]); $name = fixQuotes(strtr(explode('Entitles holder of bank note', explode('Repealed', $name_dom->plaintext)[0])[0], $sanitizeName), 'en');
        $enactDate = $enforceDate = $lastactDate = trim(end(explode(' of ', $name))).'-01-01';
        $ID = $scraper.':'.trim(end(explode(' of ', $name))).end(explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0]));
        //Gets the regime
        if (strtotime($enactDate) < strtotime('1915')) {
            $regime = '{"en":"The German Empire"}';
        } elseif (strtotime($enactDate) < strtotime('17 December 1920')) {
            $regime = '{"en":"The German Empire"}';
        } elseif (strtotime($enactDate) < strtotime('31 May 1961')) {
            $regime = '{"en":"The Union of South Africa"}';
        } elseif (strtotime($enactDate) < strtotime('21 March 1990')) {
            $regime = '{"en":"The Republic of South Africa"}';
        } elseif (strtotime($enactDate) < strtotime('today')) {
            $regime = '{"en":"The Republic of Namibia"}';
        }
        //Gets the rest of the values
        $type = explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0])[count(explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0]))-2];
        $topic = ucfirst(strtolower($law->find('td', 2)->plaintext));
        $status = $statuses[trim($law->find('td', 4)->plaintext)] ?? 'Valid';//TODO: Sanitize more statuses
        $source = strtr($law->find('td', 1)->find('a', 1)->href, array(" "=>"%20", "'"=>"%27"));

        //JSONifies the title and source
        $name = '{"en":"'.$name.'"}';
        $topic = '{"en":"'.$topic.'"}';
        $source = $PDF = '{"en":"'.$source.'"}';

        //Creates SQL
        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastActDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `topic`, `status`, `source`, `PDF`) 
                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$topic."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
        if (!$test && $ID !== 'NA-20161' && $ID !== 'NA-LawLaw') {$conn->query($SQL2);}
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