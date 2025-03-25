<html><body>
    <?php
        //Settings
        $test = true; $country = 'NO';
        $startPage = 0;//Which page to start from
        $step = 20;//How much to increase the offset by every iteration
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

        //Sets up querying function
        $API_Call = function ($url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl); curl_close($curl);
            return new simple_html_dom($response);
        };

        //Defines ministry translations
        $ministries = array('Arbeids- og inkluderingsdepartementet'=>'Ministry of Labour and Social Inclusion',
                            'Barne- og familiedepartementet'=>'Ministry of Children and Families',
                            'Digitaliserings- og forvaltningsdepartementet'=>'Ministry of Digitalisation and Public Administration',
                            'Energidepartementet'=>'Ministry of Energy',
                            'Finansdepartementet'=>'Ministry of Finance',
                            'Forsvarsdepartementet'=>'Ministry of Defence',
                            'Helse- og omsorgsdepartementet'=>'Ministry of Health and Care Services',
                            'Klima- og miljødepartementet'=>'Ministry of Climate and Environment',
                            'Kommunal- og distriktsdepartementet'=>'Ministry of Local Government and Regional Development',
                            'Kultur- og likestillingsdepartementet'=>'Ministry of Culture and Equality',
                            'Kunnskapsdepartementet'=>'Ministry of Education and Research',
                            'Justis- og beredskapsdepartementet'=>'Ministry of Justice and Public Security',
                            'Landbruks- og matdepartementet'=>'Ministry of Agriculture and Food',
                            'Nærings- og fiskeridepartementet'=>'Ministry of Trade, Industry and Fisheries',
                            'Samferdselsdepartementet'=>'Ministry of Transport and Communications',
                            'Statsministerens kontor'=>'Office of the Prime Minister',
                            'Utenriksdepartementet'=>'Ministry of Foreign Affairs');

        //Gets the limit
        $limit = $limit ?? explode(' ', $API_Call('https://lovdata.no/register/lover')->find('p[class="header-meta header red"]')[0]->plaintext)[14];

        //Loops through the pages
        for ($offset = $startPage; $offset < $limit; $offset+=$step) {
            //Processes the data in the table
            $body_rows = $API_Call('https://lovdata.no/register/lover?offset='.$offset)->find('div.list-items')[0]->find('div.documentList')[0]->find('article');
            foreach ($body_rows as $body_row) {
                //Gets values
                $enactDate = explode('-', $body_row->find('span.red')[0]->plaintext)[1].'-'.explode('-', $body_row->find('span.red')[0]->plaintext)[2].'-'.explode('-', $body_row->find('span.red')[0]->plaintext)[3]; $enforceDate = $enactDate; $lastActDate = $enactDate;
                $ID = $country.'-'.str_replace('-', '', $body_row->find('span.red')[0]->plaintext);
                $name = trim($body_row->find('h3')[0]->plaintext);
                //Gets the regime
                switch(true) {
                    case strtotime($enactDate) < strtotime('17 June 1397'):
                        $regime = 'The Old Kingdom of Norway';
                        break;
                    case strtotime('17 June 1397') < strtotime('7 August 1524'):
                        $regime = 'The Kalmar Union';
                        break;
                    case strtotime('7 August 1524') < strtotime('4 November 1814'):
                        $regime = 'Denmark-Norway';
                        break;
                    case strtotime('4 November 1814') < strtotime($enactDate) && strtotime($enactDate) < strtotime('7 June 1905'):
                        $regime = 'The United Kingdoms of Sweden and Norway';
                        break;
                    case strtotime('7 June 1905') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                        $regime = 'The Kingdom of Norway';
                        break;
                }
                //Gets the rest of the values
                $type = 'Act'; $status = 'Valid';
                $origin = trim($body_row->find('span.blueLight')[0]->plaintext);
                $source = 'https://lovdata.no'.$body_row->find('h3')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                $name = str_replace("'", "’", $name);

                //JSONifies the values
                $name = '{"no":"'.$name.'"}';
                $origin = '{"no":"'.$origin.'", "en":"'.strtr($origin, $ministries).'"}';
                $source = '{"no":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `name`, `regime`, `type`, `origin`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$origin."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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