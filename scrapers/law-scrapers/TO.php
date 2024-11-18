<html><body>
    <?php
        //Settings
        $test = true; $country = 'TO';

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Makes sure certain things get capitalized
        $capitalizees    = ['the asian development bank', 'the international monetary fund', 'the kingdom', "the bailiffs' office", 'the office of the anti-corruption commissioner', "pa'anga", 'supreme court', 'tonga', 'kingdom of tonga', 'united nations'];
        $capitalizations = ['the Asian Development Bank', 'the International Monetary Fund', 'the Kingdom', "the Bailiffs' Office", 'the Office of the Anti-corruption Commissioner', "Pa'anga", 'Supreme Court', 'Tonga', 'Kingdom of Tonga', 'United Nations'];

        //Loops through all the letters
        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
            //Creates curl handler for search
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://ago.gov.to/cms/legislation/current-revised-edition/by-title.html?view=acts_alpha',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'submit4' => $letter,
                    'pointintime_post' => date('Y-m-d').' 00:00:00',
                ),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: f8751c1ee897553d05999c058046501d=1db6bea759b4b5dea33bf3642f7f8ba4'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);

            //Creates HTML DOM
            $html_dom->load($response);

            //Processes the data in the table
            $body_rows = $html_dom->find('tr.row0');
            foreach ($body_rows as $body_row) {
                //Gets raw values
                $enactDate = $body_row->find('td')[6]->find('div')[0]->{'data-content'};
                $ID = $body_row->find('td')[1]->find('div')[0]->{'data-content'};
                $nameEN = $body_row->find('td')[3]->find('a')[0]->plaintext; $nameTO = $body_row->find('td')[4]->find('a')[0]->plaintext;
                $summary = $body_row->find('td')[1]->find('div')[0]->{'data-content'};
                $topic = $body_row->find('td')[5]->find('div')[0]->{'data-content'};
                $sourceEN = $body_row->find('td')[3]->find('a')[0]->href; $sourceTO = $body_row->find('td')[4]->find('a')[0]->href;
                
                //Sanitizes some values
                $enactDate = date('Y-m-d', strtotime(explode('<br />', $enactDate)[1])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.explode('</strong>', explode('<br />', $ID)[0])[1];
                if (strtotime($enactDate) < strtotime('4 June 1970')) {$regime = 'The British Empire';}
                    else {$regime = 'The Kingdom of Tonga';}
                $nameEN = trim(explode('&nbsp;', $nameEN)[0]); $nameTO = trim(explode('&nbsp;', $nameTO)[0]);
                $summary = strtolower(explode('<br />', $summary)[2] ?? 'NULL');
                    $summary = ($summary !== 'null' && str_starts_with($summary, 'an act')) ? ucfirst(str_replace($capitalizees, $capitalizations, $summary)):'NULL';
                $type = 'Act';
                if (str_contains(strtolower($nameEN), 'amendment')) {$isAmend = 1;} else {$isAmend = 0;}
                $status = 'Valid';
                $topic = explode(" - ", $topic)[1];
                $sourceEN = 'https://ago.gov.to'.$sourceEN; $sourceTO = 'https://ago.gov.to'.$sourceTO;

                //Makes sure there are no quotes in the title
                if (str_contains($nameEN, "'")) {$nameEN = str_replace("'", "’", $nameEN);} if (str_contains($nameTO, "'")) {$nameTO = str_replace("'", "’", $nameTO);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}

                //JSONifies the values
                $name = '{"to":"'.$nameTO.'", "en":"'.$nameEN.'"}';
                $summary = $summary === 'NULL' ? $summary:"'{\"en\":\"".$summary."\"}'";
                $topic = '{"en":"'.$topic.'"}';
                $source = '{"to":"'.$sourceTO.'", "en":"'.$sourceEN.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `summary`, `type`, `isAmend`, `topic`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', ".$summary.", '".$type."', '".$isAmend."', '".$topic."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
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