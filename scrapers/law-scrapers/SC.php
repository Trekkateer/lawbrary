<html><body>
    <?php
        //Settings
        $test = true; $country = 'SC';
        $start = 0;//Which year to start from
        $limit = null;//Which year to end at

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the limit
        $html_dom = file_get_html('https://www.nationalassembly.sc/legislation');
        $limit = $limit ?? explode('page=', $html_dom->find('a[title="Go to last page"]')[0]->href)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://www.nationalassembly.sc/legislation?type=All&search=&page='.$page);
            $laws = $html_dom->find('table.cols-4')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $law) {
                //Gets the type
                $type = strtr(trim($law->find('td')[1]->plaintext), array(' (S.I)'=>'')); $status = 'Valid';

                //Gets values
                $enactDate = date('Y/m/d', strtotime(trim(str_replace('/', '-', $law->find('td')[3]->plaintext)))); $enforceDate = $enactDate;
                if ($type === 'Statutory Instrument') {
                    $ID = $country.'-'.trim(strtr(explode(' - ', $law->find('td')[0]->plaintext)[0], array(' of '=>'', 'S.I. '=>'SI')));
                    $name = trim(explode(' - ', $law->find('td')[0]->plaintext)[1]);
                    $pdf = 'https://www.nationalassembly.sc/sites/default/files/'.date('Y-m', strtotime($enactDate)).'/'.strtr(trim(explode(', 20', $law->find('td')[0]->plaintext)[0]).', '.date('Y', strtotime($enactDate)), array('S.I.'=>'SI', ' of 20'=>' 20', 'No.'=>'No ', "'"=>'', 'Notice, 20'=>'Notice 20', 'Order, 20'=>'Order 20', 'Regulations, 20'=>'Regulations 20', '('=>'%28', ')'=>'%29')).'.pdf';
                    if (str_contains($name, 'Amendment')) {$type = $type.' Amendment';}
                } else {
                    $ID = $country.'-B'.trim(strtr(explode(' (Bill No.', $law->find('td')[0]->plaintext)[1], array(' of '=>'', ')'=>'')));
                    $name = trim(explode(' (Bill No.', $law->find('td')[0]->plaintext)[0]);
                    $pdf = 'https://www.nationalassembly.sc/sites/default/files/'.date('Y-m', strtotime($enactDate)).'/Bill '.strtr(trim(explode(' (Bill No.', $law->find('td')[0]->plaintext)[1]), array(' of '=>' ', ')'=>'')).' - '.strtr(trim(explode(' (Bill No.', $law->find('td')[0]->plaintext)[0]), array(', 20'=>' 20', '('=>'%28', ')'=>'%29')).'.pdf';
                    if (str_contains($name, 'Amendment')) {$type = $type.' Amendment';}
                }
                $source = 'https://www.nationalassembly.sc'.$law->find('td')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $pdf = '{"en":"'.$pdf.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`, `pdf`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
        
        //Connects to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>