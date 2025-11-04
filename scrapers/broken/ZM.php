<?php
    //Settings
    $test = true; $scraper = 'ZM';
    $start = 0;//Which page to start from
    $limit = null;//How many pages there are

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

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["ZM"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The National Assembly of Zambia"}';

    //Gets the limit
    $dom = file_get_html('https://www.parliament.gov.zm/acts-of-parliament');
    $limit = $limit ?? explode('page=', $dom->find('a[title="Go to last page"]', 0)->href)[1];
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Gets the HTML
        $dom = file_get_html('https://www.parliament.gov.zm/acts-of-parliament?page='.$page);

        //Processes data year by year
        $years = $dom->find('div.view-content', 0)->find('div.item-list-acts-year');
        foreach($years as $year) {
            //Processes the data in the table
            $laws = $year->find('li.views-row');
            $year = $year->find('h3', 0)->find('span.date-display-single', 0)->plaintext;
            foreach ($laws as $law) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = $year.'-01-01';
                $ID = $scraper.':'.strtr($law->find('div.views-field-title', 0)->find('span.field-content', 0)->find('a', 0)->find('div.act-number-appended', 0)->plaintext, array('( Act No. '=>'', ' of '=>'', ' '=>'', ')'=>''));
                $name = fixQuotes(trim(explode('<div', $law->find('div.views-field-title', 0)->find('span.field-content', 0)->find('a', 0)->innertext)[0]), 'en');
                $regime = strtotime($enactDate) > strtotime('18 April 1980') ? '{"en":"The Republic of Zambia"}':'{"en":"The British Empire"}';
                //$isAmend = str_contains($name, 'Amendment') ? 1:0;
                $source = 'https://www.parliament.gov.zm'.$law->find('div.views-field-title', 0)->find('span.field-content', 0)->find('a', 0)->href;
                $PDF = 'https://www.parliament.gov.zm/sites/default/files/documents/acts/'.$name.'.pdf';

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$PDF.'"}';
                
                //Inserts the new laws
                $SQL2 = "p. ".$page.": INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                if (!$test && $enactDate !== '-01-01') {$conn->query($SQL2);}
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