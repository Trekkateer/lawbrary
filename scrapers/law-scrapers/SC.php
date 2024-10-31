<html><body>
    <?php
        //Settings
        $test = true; $country = 'SC';
        $start = 0;//Which page to start from
        $limit = null;//Which page to end at

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

        //Gets the limit
        $html_dom = file_get_html('https://www.nationalassembly.sc/legislation');
        $limit = $limit ?? explode('page=', $html_dom->find('a[title="Go to last page"]')[0]->href)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://www.nationalassembly.sc/legislation?type=All&search=&page='.$page);
            $laws = $html_dom->find('table.cols-4')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $law) {
                //Gets the name
                $name = trim(strtr($law->find('td')[0]->plaintext, $sanitizeName));
                //Gets the type and the rest of the values
                $type = explode(' (', trim($law->find('td')[1]->plaintext))[0]; $status = 'Valid';
                if ($type === 'Statutory Instrument') {
                    $enactYear = end(explode(' ', trim(explode('-', $name)[0], ' )')));
                    $ID = $country.'-SI'.$zero_buffer(explode(' of ', explode('SI ', $name)[1])[0]).$enactYear;
                    //$PDF = 'https://www.nationalassembly.sc/sites/default/files/'.trim(explode('/', ($law->find('td')[3]->plaintext))[2]).'-'.explode('/', $law->find('td')[3]->plaintext)[1].'/'.strtr(trim(explode(', '.$enactYear, $name)[0]).', '.$enactYear, $sanitizeURL).'.pdf';
                } else if ($type === 'Bill') {
                    $enactYear = end(explode(' ', trim($name, ' )')));
                    $ID = $country.'-B'.$zero_buffer(explode(' of ', trim(explode('(Bill No.', $name)[1]))[0]).$enactYear;
                    //$PDF = 'https://www.nationalassembly.sc/sites/default/files/'.trim(explode('/', $law->find('td')[3]->plaintext)[2]).'-'.explode('/', $law->find('td')[3]->plaintext)[1].'/Bill '.strtr(trim(explode('(Bill No.', $name)[1]), array(' of '=>' ', ')'=>'')).' - '.strtr($name, $sanitizeURL).'.pdf';
                }
                if (str_contains($name, 'Amendment')) {$type = 'Amendment to '.$type;}
                $source = 'https://www.nationalassembly.sc'.$law->find('td')[0]->find('a')[0]->href;
                //Gets the regime
                if (strtotime($enactYear.'-01-01') < strtotime('29 June 1976')) {
                    $regime = 'The British Empire';
                } else {$regime = 'The Republic of Seychelles';}

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                //$PDF = '{"en":"'.$PDF.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactYear."-01-01', '".$enactYear."-01-01', '".$enactYear."-01-01', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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