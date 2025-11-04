<?php
    //Settings
    $test = false; $scraper = 'NO';
    $start = 0;//Which page to start from
    $step = 20;//How much to increase the offset by every iteration
    $limit = null;//How many pages there are

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings
    error_reporting(E_ALL & ~E_WARNING);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
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
    $limit = $limit ?? explode(' ', $API_Call('https://lovdata.no/register/lover')->find('p[class="header-meta header red"]', 0)->plaintext)[14];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["NO"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"no":"Stiftelsen Lovdata", "en":"The Lovdata Foundation"}';

    //Loops through the pages
    for ($offset = $start; $offset < $limit; $offset+=$step) {
        //Processes the data in the table
        $laws = $API_Call('https://lovdata.no/register/lover?offset='.$offset)->find('div.list-items', 0)->find('div.documentList', 0)->find('article');
        foreach ($laws as $law) {
            //Gets values
            $enactDate = $enforceDate = $lastActDate = explode('-', $law->find('span.red', 0)->plaintext)[1].'-'.explode('-', $law->find('span.red', 0)->plaintext)[2].'-'.explode('-', $law->find('span.red', 0)->plaintext)[3];
            $ID = $scraper.':'.str_replace('-', '', $law->find('span.red', 0)->plaintext);
            $name = fixQuotes(trim($law->find('h3', 0)->plaintext), 'en');
            //Gets the regime
            if (strtotime($enactDate) < strtotime('17 June 1397')) {
                $regime = '{"no":"Det gamle kongeriket Norge", "en":"The Old Kingdom of Norway"}';
            } elseif (strtotime($enactDate) < strtotime('7 August 1524')) {
                $regime = '{"no":"Kalmarunionen", "en":"The Kalmar Union"}';
            } else if (strtotime($enactDate) < strtotime('4 November 1814')) {
                $regime = '{"no":"Danmark-Norge", "en":"Denmark-Norway"}';
            } else if (strtotime($enactDate) < strtotime('7 June 1905')) {
                $regime = '{"no":"De forente kongeriker Sverige og Norge", "en":"The United Kingdoms of Sweden and Norway"}';
            } else if (strtotime($enactDate) < strtotime('today')) {
                $regime = '{"no":"Kongeriket Norge", "en":"The Kingdom of Norway"}';
            }
            //Gets the rest of the values
            $origin = trim($law->find('span.blueLight', 0)->plaintext);
            $source = 'https://lovdata.no'.$law->find('h3', 0)->find('a', 0)->href;

            //JSONifies the values
            $name = '{"no":"'.$name.'"}';
            $origin = '{"no":"'.$origin.'", "en":"'.strtr($origin, $ministries).'"}';
            $source = '{"no":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastActDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `origin`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$origin."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
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