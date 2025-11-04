<?php //Antigua and Barbuda
    //!!Website not responsive to cURL or file_get_html!!

    //Settings
    $test = true; $scraper = 'AG';
    $start = 2021;//Which year to start from
    $limit = null;//Which year to end at

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $HTTP_Call = function ($page) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://laws.gov.ag/annual/?variable='.$page.'#laws-list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl); curl_close($curl);
        echo $response;
    };

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["AG"]';
    $type = 'Act'; $status = 'Valid';
    $regime = '{"en":"Antigua and Barbuda"}';
    $publisher = '{"en":"The Ministry of Legal Affairs"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Processes the data
        $html_dom = new simple_html_dom($HTTP_Call($page)); echo $html_dom.'test2';
        $laws = $html_dom->find('#main-content')->find('div.container', 0)->find('table', 0)->find('tbody', 0)->find('tr');
        foreach ($laws as $lawNum => $law) {
            //Gets values
            $enactDate = $enforceDate = $lastactDate = trim($law->find('td', 1)->plaintext).'-01-01';
            $ID = $scraper.':'.trim($law->plaintext).$lawNum;
            $name = fixQuotes(trim($law->find('td', 0)->plaintext), 'en');
            $isAmend = (str_contains($name, 'Amendment')) ? 1:0;
            $source = $law->find('td', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = $PDF = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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
?>