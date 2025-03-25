<html><body>
    <?php
        //Settings
        $test = true; $country = 'PH';

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

        //Makes sure there are five digits in every outputed number
        function zero_buffer ($inputNum, $outputLen=5) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Loops through the pages
        foreach (array('acts'=>'The United States of America', 'comacts'=>'The Commonwealth of the Philippines', 'bataspam'=>'The Republic of the Philippines', 'repacts'=>'The Republic of the Philippines') as $page => $regime) {
            //Gets the page limit
            $html_dom = file_get_html('https://lawphil.net/statutes/'.$page.'/'.$page.'.html');
            $laws = $html_dom->find('table#s-menu')[0]->find('tr.xy');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = date('Y-m-d', strtotime(explode($law->find('td')[0]->find('a')[0]->plaintext, $law->find('td')[0]->plaintext)[1])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.explode('-', $enactDate)[0].zero_buffer(end(explode(' ', $law->find('a')[0]->plaintext)));
                $name = trim($law->find('td')[1]->plaintext);
                $type = 'Act';
                    if (str_contains(strtolower($name), 'amend')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $source = 'https://lawphil.net/'.$law->find('td')[0]->find('a')[0]->href;
                $PDF = $law->find('td')[2]->find('a')[0]->href ?? NULL;

                //Makes sure there are no quotes in the title
                $name = strtr($name, array("'"=>"’", ' "'=>' “', '"'=>'”'));

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = isset($PDF) ? '\'{"en":"https://lawphil.net/'.$PDF.'"}\'':'NULL';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', ".$PDF.")"; echo $SQL2.'<br/>';
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