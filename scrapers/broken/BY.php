<?php //Belarus
    //!!The President's website is down!!
    
    //Settings
    $test = true; $scraper = 'BY';
    $start = 0;//Which law to start from
    $step = 24;//How much to increase the limit by every iteration
    $limit = null;//Total number of laws desired.

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e";  $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Creates array for fixing the types and IDs
    $types = array(
        'Дэкрэты'=>'Decree', 'Дырэктывы'=>'Directive', 'Распараджэннi'=>'Executive Order', 'Указы'=>'Ordinance', 
        'Декреты'=>'Decree', 'Директивы'=>'Directive', 'Распоряжения'=>'Executive Order', 'Указы'=>'Ordinance', 
        'Decrees'=>'Decree', 'Directives'=>'Directive', 'Executive Orders'=>'Executive Order', 'Ordinances'=>'Ordinance'
    );
    $IDs = array(
        'dekret'=>'DEKRET', 'direktiva'=>'DIREKTIVA', 'dyrektyva'=>'DIREKTIVA', 'rasparadzenne'=>'RASPARADZHENNE', 'rasparadzhenne'=>'RASPARADZHENNE', 'ukaz'=>'UKAZ',
        /*'dekret'=>'DEKRET', 'direktiva'=>'DIREKTIVA',*/ 'rasporyazhenii'=>'RASPARADZHENNE', 'rasporyazhenie'=>'RASPARADZHENNE', 'rasporjazhenie'=>'RASPARADZHENNE',// 'ukaz'=>'UKAZ',
        'decree'=>'DEKRET', 'directive'=>'DIREKTIVA', 'executiveorder'=>'RASPARADZHENNE', 'ordinance'=>'UKAZ',
    );

    //Sets static variables TODO: find a way to make status dynamic
    $saveDate = date('Y-m-d'); $country = '["BY"]'; $status = 'Valid';
    $publisher = '{"en":"The Official Internet Portal of the President of the Republic of Belarus","be":"Афіцыйны інтэрнэт-партал Прэзідэнта Рэспублікі Беларусь","ru":"Официальный интернет-портал Президента Республики Беларусь"}';

    //Sets up querying function
    $API_Call = function ($href) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $href,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Cookie: TS0159ea58=012b21144dcc899e5848199ed7815889bc3d7c9011a35046b08fde9dfc4853225c68d37fb140b8343d55722987e4c7bae236cd195c; TS0101fca2=012b21144d4acb967db5d7f54b5715e569845337a21be862ea9dfcc39d894428af1fcf20b2bf27b0bc538433d48b5b0519269f2a04; TS0121b763=012b21144dcc3cfd196a5f9aee2c8fea035a334b797b387a178265c0d6ac2e28a6e1dd17790ca7b427fecba14a0f96494b6dfb6a6c; TS01752208=012b21144d08593b4b20e5e555196bff37949c14557304280ce1d4d760b4d83f4b49708b0e8d2611edd1c66cb4777c7836b5736466'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return (json_decode($response, true));
    };

    //Loops through the languages
    foreach (array('be', 'ru', 'en') as $lang) {
        //Gets the limit of how many laws there are
        $limit = $limit ?? $API_Call('https://search.president.gov.by/'.$lang.'/search/show-more-category/document/'.$start.'/'.$step)['total_results'];

        //Gets the laws
        for ($page = $start; $page <= $limit; $page += $step) {
            //Interprets the data
            $laws = $API_Call('https://search.president.gov.by/'.$lang.'/search/show-more-category/document/'.$page.'/'.$step)['index'];
            foreach ($laws as $law) {
                //Makes sure the law is not just comments
                if (isset($IDs[explode('-', explode('/documents/', $law['url'])[1])[0]]) || str_contains('normal-0-false-false-false-microsoftinternetexplorer4-', $law['url'])) {
                    //Gets the ID
                    $explodeURL = explode('-', strtr(explode('/documents/', $law['url'])[1], array('-no-'=>'-', '-np-'=>'np-', 'executive-order'=>'executiveorder', 'normal-0-false-false-false-microsoftinternetexplorer4-'=>'', '-st1behaviorurlieooui-'=>'-', '-st1behaviorurlieooui-'=>'-')));
                        if (str_contains($law['url'], '-g')) {
                            if (str_contains($law['url'], '-g-')) {
                                $ID = $explodeURL[sizeof($explodeURL)-3];
                            } else {$ID = $explodeURL[sizeof($explodeURL)-2];}
                        } else {//Figures out where the year is based on month
                            if (preg_match('/[A-Za-z]/', $explodeURL[sizeof($explodeURL)-2])) {
                                $ID = $explodeURL[sizeof($explodeURL)-1];
                            } else {$ID = $explodeURL[sizeof($explodeURL)-2];}
                        }
                        $ID = $scraper.':'.$IDs[$explodeURL[0]].preg_replace('/[A-Za-z]/', '', $explodeURL[1].$ID); echo $ID.'</br>';
                    //Gets other values
                    $enactDate = $enforceDate = $lastactDate = date('Y-m-d', $law['date']);
                    $name = fixQuotes(trim($law['description']), $lang);
                    //Gets the regime
                    switch(true) {
                        case strtotime('25 March 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 January 1919'):
                            $regime = '{"be":"Дэмакратычная Рэспубліка Беларусь", "ru":"Демократическая Республика Беларусь", "en":"The Democratic Republic of Belarus"}';
                            break;
                        case strtotime('1 January 1919') < strtotime($enactDate) && strtotime($enactDate) < strtotime('25 August 1991'):
                            $regime = '{"be":"Беларуская ССР", "ru":"Белорусская ССР", "en":"The Belarusian S.S.R."}';
                            break;
                        case strtotime('25 August 1991') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                            $regime = '{"be":"Рэспубліка Беларусь", "ru":"Республика Беларусь", "en":"The Republic of Belarus"}';
                            break;
                    }
                    //Gets the rest of the values
                    $summary = fixQuotes(trim($law['comment']), $lang);
                    $type = $types[$law['category'][0]['name']];
                    $source = 'https://president.gov.by'.$law['url'];

                    //JSONifies the values, making sure to incluse any already stored translations
                    $SQLFind = "SELECT * FROM `".strtolower($scraper)."` WHERE `id`='".$ID."'";
                    $result = $conn->query($SQLFind);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            //JSONifies the name
                            $nameJSON = json_decode($row['name'], true);
                            $nameJSON[$lang] = $name;
                            $name = json_encode($nameJSON, JSON_UNESCAPED_UNICODE);

                            //JSONifies the summary
                            $summaryJSON = json_decode($row['summary'], true);
                            $summaryJSON[$lang] = $summary;
                            $summary = json_encode($summaryJSON, JSON_UNESCAPED_UNICODE);

                            //JSONifies the source
                            $sourceJSON = json_decode($row['source'], true);
                            $sourceJSON[$lang] = $source;
                            $source = json_encode($sourceJSON, JSON_UNESCAPED_UNICODE);

                            //Updates the table
                            $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `summary`='".$summary."', `source`='".$source."' WHERE `ID`='".$ID."'";

                            //Makes sure the ID is not being duplicated
                            /*if (isset(json_decode($row['source'], true)[$lang])) {
                                if ($source !== json_decode($row['source'], true)[$lang]) {
                                    //Sets new ID
                                    $ID = $ID.$explodeURL[sizeof($explodeURL)-1];
                                    
                                    //Creates correct SQL
                                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `summary`, `type`, `status`, `source`) 
                                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$summary."', '".$type."', '".$status."', '".$source."')";
                                } else {$SQL2 = $SQLFind;}
                            }*/
                        }
                    } else {//If there is no existing entry
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $summary = '{"'.$lang.'":"'.$summary.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Inserts the law to the table
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `summary`, `type`, `status`, `source`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$summary."', '".$type."', '".$status."', '".$source."')";
                    }

                    //Makes the query
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}
?>