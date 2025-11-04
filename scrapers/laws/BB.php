<?php //Barbados
    //Settings
    $test = false; $scraper = 'BB';
    $start = 2009;//Which year to start from
    $limit = 2020;//Which year to end at
    //For some reason, only laws from 2009 to 2020 are recorded. Independance was November 30th, 1966.

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

    //Makes sure you can get the ID
    $IDDeliminators = array(
        'Act'=>$year.'-',
        'Statutory Instrument'=>' No '
    );
    //Creates IDs
    $typeID = array(
        'Act'=>'A',
        'Statutory Instrument'=>'SI'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["BB"]'; $regime = '{"en":"Barbados"}'; $status = 'Valid';
    $publisher = '{"en":"The Office of the Attorney General and Legal Affairs"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops through the types
    foreach (array('Act', 'Statutory Instrument') as $type) {
        //Loops through the pages
        for ($year = $start; $year <= $limit; $year++) {
            //Processes the data
            $dom = file_get_html('https://oag.gov.bb/Laws/Annuals/'.$year.'/'.str_replace(' ', '-', $type).'s-Annual/');
            $laws = $dom->find('ul.iconlist.iconlist-color.mb-5', 0)->find('li.attachment.pdf');
            foreach($laws as $law) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = $year.'-01-01'; 
                $ID = $scraper.':'.$typeID[$type].$year.str_pad(explode(' (Corrected Copy)', end(explode($IDDeliminators[$type], strtr($law->find('a.attachment-link', 0)->find('h3.attachment-title', 0)->plaintext, array(' No. '=>' No ')))))[0], 3, '0', STR_PAD_LEFT);
                $name = $law->find('a.attachment-link', 0)->find('h3.attachment-title', 0)->plaintext;
                $source = $PDF = 'https://oag.gov.bb'.str_replace(' ', '%20', $law->find('a.attachment-link', 0)->href);

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$PDF.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
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