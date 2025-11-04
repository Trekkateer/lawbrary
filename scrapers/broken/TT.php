<?php //Trinidad and Tobago
    //!!The laws are not available online, only as downlaods!!

    //Settings
    $test = true; $scraper = 'TT';
    $start = 1832;//Which year to start from
    $limit = null;//Total number of years desired.

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $html_dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["TT"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The Government of the Republic of Trinidad and Tobago"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops though the years
    for ($year = $start; $year <= $limit; $year++) {
        //Gets values
        $html_dom->load(file_get_html('http://laws.gov.tt/ttdll-web/revision/byyear?year='.$year));
        foreach ($html_dom->find('table.table.table-striped.m-b-none', 0)->find('tbody', 0)->find('tr') as $law) {
            //Gets the values
            $enactDate = $enforceDate = $lastactDate = $year.'-01-01';
            $ID = $scraper.':'.trim(str_replace(['No. ', ' of '], ['', ''], $law->find('td', 1)->plaintext));
            $name = fixQuotes($law->find('td', 2)->plaintext, "en");
            //Gets the country and regime
            $scraper = '["TT"]';
            switch(true) {
                case strtotime('27 March 1802') < strtotime($enactDate) && strtotime($enactDate) < strtotime('3 January 1958'):
                    $regime = '{"en":"The Crown Colony of Trinidad and Tobago"}';
                    break;
                case strtotime('3 January 1958') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 January 1962'):
                    $regime = '{"en":"The Federation of the West Indies"}';
                    break;
                case strtotime('14 January 1962') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 August 1976'):
                    $regime = '{"en":"Trinidad and Tobago"}';
                    break;
                case strtotime('1 August 1976') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                    $regime = '{"en":"The Republic of Trinidad and Tobago"}';
                    break;
            }
            //Gets the rest of the values
            $source = 'https://laws.gov.tt/'.$law->find('td', 0)->find('a', 0)->href; $PDF = $source;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = '{"en":"'.$PDF.'"}';

            //Inserts the law to the table
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';

            //Makes the query
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