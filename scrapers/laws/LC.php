<?php //Saint Lucia
    //Settings
    $test = false; $scraper = 'LC';
    $start = 2003;//Which year to start from
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

    //Sets up the querying function
    function HTTP_Call ($href) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $href,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl); curl_close($curl);
        return new simple_html_dom($response);
    }

    //Sanitizes the data
    $sanitize = array('  '=>' ');

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["LC"]'; 
    $origin = '{"en":"Parliament of Saint Lucia"}';
    $publisher = '{"en":"The National Printing Corporation"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops through the years
    for ($year = $start; $year <= $limit; $year++) {
        //Loops through the types of laws
        foreach (array('acts/'=>'Act', 'si/'=>'Statutory Instrument') as $page => $type) {
            //Gets the data
            $laws = HTTP_Call('https://npc.govt.lc/laws/'.$page.$year)->find('div.col-lg-10', 0)->find('table', 0)->find('a');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = $year.'-01-01';
                $ID = $scraper.':A'.str_pad(explode(' ', explode(' - ', $law->plaintext)[0])[2], 3, '0', STR_PAD_LEFT).$year;
                $regime = strtotime($enactDate) < strtotime('22 February 1979') ? '{"en":"The British Empire", "fr":"L’Empire britannique"}':'{"en":"Saint Lucia", "fr":"Sainte-Lucie"}';
                $name = fixQuotes(explode('- Price', strtr($law->plaintext, $sanitize))[0], 'en');
                $status = 'Valid';
                //$isAmend = str_contains($name, 'Amendment') ? 1:0;
                $source = $law->href;

                //Makes sure there are no quotes in the URL
                strtr($source, ["'"=>"%27"]);

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }
    
    //Connects to the content database
    $username1="ug0iy8zo9nryq"; $password1="T_1&x+$|*N6F"; $database1="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username1, $password1, $database1);
    $conn2->select_db($database1) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>