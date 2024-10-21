<html><body>
    <?php
        //Settings
        $test = true; $country = 'GY';
        $start = 0;//Which law to start from
        $step = 10;//The number of laws per page
        $limit = null;//How many pages there are

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

        //Makes sure there are four digits in every outputed number
        $zero_buffer = function ($inputNum, $outputLen=4) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Gets the limit
        $html_dom = file_get_html('https://www.parliament.gov.gy/publications/acts-of-parliament');
        $limit = $limit ?? explode('P', explode('/', $html_dom->find('a.page-last', 0)->href)[5])[1];
        //Loops through the pages
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.parliament.gov.gy/publications/acts-of-parliament/P'.$offset);

            //Processes the data in the table
            $laws = $html_dom->find('table')[0]->find('tr.odd, tr.even');
            foreach ($laws as $rowNum => $law) {
                //Gets values
                $enactDate = strtr($law->find('td')[1]->find('span.common-td')[0]->plaintext, array(','=>' ')) !== 'n/a' ? strtr($law->find('td')[1]->find('span.common-td')[0]->plaintext, array(','=>' ')):end(explode(' ', trim($law->find('td')[0]->find('a')[0]->plaintext))).'-01-01';
                    if (preg_replace('/[\p{Latin}\p{P}01]+/', '', $enactDate) === '') {$enactDate = end(explode('_', explode('.pdf', $law->find('td')[3]->find('a')[0]->href)[0])).'-01-01';}
                    $enactDate = date('Y-m-d', strtotime($enactDate)); $enforceDate = $enactDate; $lastactDate = $enforceDate;
                $ID = $country.'-'.$zero_buffer($offset+$rowNum);
                //Gets regime
                switch(true) {
                    case strtotime('1667-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1831-01-01'):
                        $regime = 'Dutch Essequibo';
                        break;
                    case strtotime('1831-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('26 May 1966'):
                        $regime = 'British Guiana';
                        break;
                    case strtotime('26 May 1966') < strtotime($enactDate) && strtotime($enactDate) < strtotime('23 February 1970'):
                        $regime = 'The Republic of Guyana';
                        break;
                    case strtotime('23 February 1970') < strtotime($enactDate) && strtotime($enactDate) < strtotime('6 October 1980'):
                        $regime = 'The Republic of Guyana';
                        break;
                    case strtotime('6 October 1980') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                        $regime = 'The Co-operative Republic of Guyana';
                        break;
                }
                //Gets the rest of the values
                $name = ucfirst(strtr(trim($law->find('td')[0]->find('a')[0]->plaintext), array('( '=>'(', ' )'=>')', 'amendment'=>'Amendment', 'ACT'=>'Act')));
                $type = 'Act';
                    if (str_contains($name, 'Amendment')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $source = $law->find('td')[0]->find('a')[0]->href;
                $PDF = $law->find('td')[3]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '\'{"en":"'.$PDF.'"}\'';
                    if (str_contains($PDF, 'javascript:void(0);') || str_contains($PDF, '.doc')) {$PDF = 'NULL';}
                
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