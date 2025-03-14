<html><body>
    <?php
        //Settings
        $test = true; $scraper = 'BB';
        $start = 2009;//Which year to start from
        $limit = 2020;//Which year to end at
        //For some reason, only laws from 2009 to 2020 are recorded. Independance was November 30th, 1966.

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Makes sure every number has a certain number of digits
        function zero_buffer($inputNum, $outputLen=3) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

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

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops through the types
        foreach (array('Act', 'Statutory Instrument') as $type) {
            //Loops through the pages
            for ($year = $start; $year <= $limit; $year++) {
                //Processes the data
                $html_dom = file_get_html('https://oag.gov.bb/Laws/Annuals/'.$year.'/'.str_replace(' ', '-', $type).'s-Annual/');
                $laws = $html_dom->find('ul.iconlist.iconlist-color.mb-5')[0]->find('li.attachment.pdf');
                foreach($laws as $law) {
                    //Gets values
                    $enactDate = $year.'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                    $ID = $scraper.'-'.$typeID[$type].$year.zero_buffer(explode(' (Corrected Copy)', end(explode($IDDeliminators[$type], strtr($law->find('a.attachment-link')[0]->find('h3.attachment-title')[0]->plaintext, array(' No. '=>' No ')))))[0]);
                    $country = '["BB"]'; $regime = '{"en":"Barbados"}';
                    $name = $law->find('a.attachment-link')[0]->find('h3.attachment-title')[0]->plaintext;
                    $status = 'Valid';
                    $source = $PDF = 'https://oag.gov.bb'.str_replace(' ', '%20', $law->find('a.attachment-link')[0]->href);

                    //JSONifies the values
                    $name = '{"en":"'.$name.'"}';
                    $source = '{"en":"'.$source.'"}';
                    $PDF = '{"en":"'.$PDF.'"}';
                    
                    //Inserts the new laws
                    $SQL2 = "INSERT INTO `laws".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `country`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$country."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }
        
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>