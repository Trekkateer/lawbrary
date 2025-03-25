<html><body>
    <?php
        //Settings
        $test = true; $country = 'SK';
        $start = 1;//What page to start from
        $step = 100;//How many laws to get at a time
        $limit = null;//Total number of pages desired

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

        //Sets up the querying function
        $API_Call = function ($url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => //Set "stadium" to null to get laws in all stages
                '{
                    "stadium":"PROCES_UKONCENY",
                    "hladanyVyraz":"",
                    "cisloLegislativnehoMaterialu":"",
                    "rezortneCislo":"",
                    "oblastKodList":[],
                    "nazov":"",
                    "rocnik":"",
                    "datumZmenyOd":null,
                    "datumZmenyDo":null
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = json_decode(curl_exec($curl), true); curl_close($curl);
            return $response;
        };

        //Translates the types
        $types = [
            'Akt EÚ'=>'EU Legislation',
            'Akt medzinárodného práva'=>'International Act',
            'Poslanecký návrh zákona'=>'Bill',
            'Poslanecký návrh - ústavný zákon'=>'Constitutional Bill',
            'Ústavný zákon'=>'Constitutional Law',
            'Rozhodnutie'=>'Decision',
            'Vyhláška'=>'Decree',
            'Informatívny materiál na rokovanie vlády SR'=>'Information',
            'Legislatívny zámer'=>'Legislative intent',
            'Nelegislatívny všeobecný materiál'=>'Non-legislative general material',
            'Opatrenie'=>'Measure',
            'Oznámenie'=>'Notice',
            'Nariadenie vlády Slovenskej republiky'=>'Regulation',
            'Zákon'=>'Law',
        ];
        //Translates the statuses
        $statuses = [
            'PROCES_UKONCENY'=>'Valid',
            /*' Medzirezortné pripomienkové konanie '=>'Inter-ministerial Comment Procedure',
            ' Vyhodnotenie medzirezortného pripomienkového konania '=>'Evaluation of the Inter-ministerial Comment Procedure',
            ' Pred rokovaním '=>'Before the Meeting',
            ' Rokovanie poradných orgánov vlády SR '=>'Meeting of the Advisory Bodies of the Government of the Slovak Republic',
            ' Po rokovaní poradných orgánov vlády SR '=>'After the meeting of the advisory bodies of the Government of the Slovak Republic',
            ' Rokovanie vlády SR '=>'Meeting of the Government of the Slovak Republic',
            ' Schválené na vláde SR '=>'Approved by the Government of the Slovak Republic',
            ' Schvaľovanie ministrom '=>'Approval by the Minister',
            ' Schvaľovanie prezidentom SR '=>'Approval by the President of the Slovak Republic',
            ' Rokovanie Národnej rady SR '=>'Meeting of the National Council of the Slovak Republic',
            ' Redakčná úprava '=>'Editorial Edit',
            ' Pred publikovaním materiálu '=>'Before you publish a material',
            ' Publikácia opatrenia do RO '=>'Publication of measures to RO',
            ' Pripomienkovanie predbežnej informácie '=>'Commenting on the preliminary information',
            ' Predprípravná fáza ukončená '=>'Pre-preparatory phase completed',
            ' Zápis stanoviska k návrhu aktu EÚ '=>'Entry of an opinion on a proposal for an EU act',
            ' Vytvorenie finálneho predbežného stanoviska '=>'Creation of a final preliminary opinion',
            ' Vyhodnotenie medzirezortného pripomienkového konania Akt EÚ '=>'Evaluation of the inter-ministerial comment procedure on the EU Act',
            ' Po rokovaní Úradu vlády SR '=>'After the meeting of the Office of the Government of the Slovak Republic',
            ' Po rokovaní '=>'After the Meeting',
            ' Vyhodnotenie medzirezortného pripomienkového konania Akt EÚ '=>'Evaluation of the inter-ministerial comment procedure on the EU Act',
            ' Publikované v Zbierke zákonov SR '=>'Published in Collection of Laws of the Slovak Republic',
            ' Po rokovaní NR SR '=>'After the meeting of the National Council of the Slovak Republic',
            ' Publikovanie materiálu '=>'Publish material'*/
        ];
        
        //Finds the limit
        $limit = $limit ?? $API_Call('https://api-gateway.slov-lex.sk/internal/elegislativa/legislativne-materialy/filter?stadium=PROCES_UKONCENY&page='.$start.'&size='.$step.'&sortBy=MPK&sortDirection=DESC')['totalElements'];
        //Gets the laws
        $laws = $API_Call('https://api-gateway.slov-lex.sk/internal/elegislativa/legislativne-materialy/filter?stadium=PROCES_UKONCENY&page='.$start.'&size='.($limit-$start+1).'&sortBy=MPK&sortDirection=DESC')['legMaterialList'];
        foreach ($laws as $law) {
            //Gets the dates and ID
            $enactDate = $law["zaciatokStadia"]; $enforceDate = $enactDate; $lastactDate = $enforceDate;
            $endDate = ($law["datumUkonceniaPkMpk"] ?? NULL) ? "'".$law["datumUkonceniaPkMpk"]."'":'NULL';
            $ID = $country.'-'.str_replace('/', '', $law['cisloLegislativnehoMaterialu']);
            //Gets the regime
            switch(true) {
                case strtotime($enactDate) < strtotime('28 October 1918'):
                    $regime = 'The Austro-Hungarian Empire';
                    break;
                case strtotime('28 October 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('30 September 1938'):
                    $regime = 'The First Czechoslovak Republic';
                    break;
                case strtotime('30 September 1938') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 March 1939'):
                    $regime = 'The Second Czechoslovak Republic';
                    break;
                case strtotime('14 March 1939') < strtotime($enactDate) && strtotime($enactDate) < strtotime('24 October 1945'):
                    $regime = 'The First Slovak Republic';
                    break;
                case strtotime('24 October 1945') < strtotime($enactDate) && strtotime($enactDate) < strtotime('25 February 1948'):
                    $regime = 'The Third Czechoslovak Republic';
                    break;
                case strtotime('25 February 1948') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 January 1993'):
                    $regime = 'The Fourth Czechoslovak Republic';
                    break;
                case strtotime('1 January 1993') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                    $regime = 'The Second Slovak Republic';
                    break;
            }
            //Gets the rest of the values
            $name = $law['nazov'];
            $type = $types[$law['typNazov']];
            $status = $statuses[$law['stadium']['hodnota']];
            $source = 'https://www.slov-lex.sk/elegislativa/legislativne-procesy/SK/'.$law['cisloLegislativnehoMaterialu'];

            //Makes sure there are no quotes in the title or summary
            if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

            //JSONifies the title and source
            $name = '{"sk":"'.$name.'"}';
            $source = '{"sk":"'.$source.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `endDate`, `ID`, `regime`, `name`, `type`, `status`, `source`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', ".$endDate.", '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
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