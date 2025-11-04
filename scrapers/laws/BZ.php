<?php //Belize
    //Settings
    $test = false; $scraper = 'BZ';
    $start = 2021;//Which year to start from
    $limit = null;//Which year to stop at

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM) and HTML
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets static variables. TODO: Find a way to make status dynamic
    $saveDate = date('Y-m-d'); $country = '["BZ"]'; $status = 'Valid';
    $publisher = '{"en":"The Attorney General’s Ministry of Belize"}';
    
    //Loops through the types
    foreach (array('act'=>'Act', 'SI'=>'Statutory Instrument') as $typeCode => $type) {
        //Gets the laws
        $limit = $limit ?? date('Y');
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the data from agm.gov.bz API
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.agm.gov.bz/api-laws/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('action' => 1001,'volume' => $page.'_'.$typeCode),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: PHPSESSID=4e069f714e5e7a9bde5fbdcfe47b5872'
                ),
            )); 
            $response = curl_exec($curl); curl_close($curl);

            //Runs through each bill
            $bills = json_decode($response, true)['data'];
            foreach ($bills as $bill) {
                $dom = new simple_html_dom($bill[0]);

                //Makes sure you can get the code properly
                if (!preg_match('/[A-Za-z]/', explode(' ', $dom->find('a', 0)->plaintext)[2])) {
                    //Interprets the data
                    $enactDate = $enforceDate = $lastactDate = $page.'-01-01';
                    $ID = $scraper.':'.strtoupper($typeCode).explode(' ', $dom->find('a', 0)->plaintext)[2].$page;
                    $name = fixQuotes($dom->find('a', 0)->plaintext, 'en');
                    //Gets the regime
                    switch(true) {
                        case strtotime('1862') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1981'):
                            $regime = '{"en":"The Crown Colony of British Honduras"}';
                            break;
                        case strtotime('21 September 1981') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                            $regime = '{"en":"Belize"}';
                            break;
                    }
                    //Gets the rest of the values
                    $isAmend = str_contains($name, 'Amendment') ? 1:0;
                    $source = $dom->find('a', 0)->href;

                    //JSONifies the title and source
                    $name = '{"en":"'.$name.'"}';
                    $source = $PDF = '{"en":"https://www.agm.gov.bz'.$source.'"}';

                    //Makes sure there are no duplicates and adds law to the table
                    $SQL2 = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
                    $result = $conn->query($SQL2);
                    if ($result->num_rows === 0) {
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `isAmend`, `status`, `source`, `PDF`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', ".$isAmend.", '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
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

    //Closes the connections
    $conn->close(); $conn2->close();
?>