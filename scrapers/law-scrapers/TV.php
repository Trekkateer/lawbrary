<html><body>
    <?php
        //Settings
        $test = false; $country = 'TV';

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

        //Makes sure certain things get capitalized TODO: Add more
        $capitalizees    = [' british', ' the crown', ' the ekalesia a kelisiano', ' england', ' ireland', ' falekaupule', ' the multilateral investment guarantee agency', ' the international centre for the settlement of investment disputes', ' the international development association', ' international bank for reconstruction and development', ' the international finance corporation', ' international monetary fund', ' kaupule', ' red cross', ' the tuvalu philatelic bureau', ' the tuvalu telecommunications corporation', ' tuvalu'];
        $capitalizations = [' British', ' the Crown', ' the Ekalesia a Kelisiano', ' England', ' Ireland', ' Falekaupule', ' the Multilateral Investment Guarantee Agency', ' the International Centre for the Settlement of Investment Disputes', ' the International Development Association', ' International Bank for Reconstruction and Development', ' the International Finance Corporation', ' International Monetary Fund', ' Kaupule', ' Red Cross', ' the Tuvalu Philatelic Bureau', ' the Tuvalu Telecommunications Corporation', ' Tuvalu'];

        //Loops through all the letters
        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
            //Creates curl handler for search
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://tuvalu-legislation.tv/cms/legislation/acts_only/by-alphabetical-order.html',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'pointintime_post' => date('Y-m-d').' 00:00:00',
                    'submit4' => $letter,
                    'submit4' => $letter,
                    'pointintime_post_alpha' => date('Y-m-d').' 00:00:00'
                ),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: 802cd4939d71262452ffebd08a194ae8=66646a6bd2734861c61da37716832cfd'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);

            //Creates HTML DOM
            $html_dom->load($response);

            //Processes the data in the table
            $body_rows = $html_dom->find('tr.row0');
            foreach ($body_rows as $body_row) {
                //Gets raw values
                $enactDate = $body_row->find('td')[5]->find('div')[0]->{'data-bs-content'};
                $ID = $body_row->find('td')[1]->find('div')[0]->{'data-bs-content'};
                $name = $body_row->find('td')[3]->find('a')[0]->plaintext;
                $summary = $body_row->find('td')[1]->find('div')[0]->{'data-bs-content'};
                $topic = $body_row->find('td')[4]->find('div')[0]->{'data-bs-content'};
                $source = $body_row->find('td')[3]->find('a')[0]->href;
                
                //Sanitizes some values
                $enactDate = date('Y-m-d', strtotime($enactDate)); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.explode('<hr', explode(': ', $ID)[1])[0];
                if (strtotime($enactDate) < strtotime('1 October 1978')) {$regime = '{"en":"The British Empire"}';} else {$regime = '{"en":"Tuvalu"}';}
                $name = explode('&nbsp;', $name)[0];
                $summary = strtolower(explode("<hr class='notes'>", $summary)[1] ?? 'NULL');
                    $summary = ($summary !== 'null' && str_starts_with($summary, 'an act')) ? ucfirst(str_replace($capitalizees, $capitalizations, $summary)):'NULL';
                $type = 'Act';
                if (str_contains($name, 'Amendment')) {$isAmend = 1;} else {$isAmend = 0;}
                $status = 'Valid';
                $topic = trim(explode('<br></span>', explode("'>", $topic)[1])[0]);
                $source = 'https://laws.bahamas.gov.bs'.$source;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}
                if (str_contains($topic, "'")) {$topic = str_replace("'", "’", $topic);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $summary = $summary === 'NULL' ? $summary:"'{\"en\":\"".$summary."\"}'";
                $topic = '{"en":"'.$topic.'"}';
                $source = '{"en":"'.$source.'"}'; $PDF = $source;
                
                //Inserts the new laws
                echo $summary.'<br/>';
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `summary`, `type`, `isAmend`, `topic`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', ".$summary.", '".$type."', '".$isAmend."', '".$topic."', '".$status."', '".$source."', '".$PDF."')"; //echo $SQL2.'<br/>';
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