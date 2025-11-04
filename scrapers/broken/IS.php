<?php //Iceland
    //Link is broken

    //Settings
    $test = true; $scraper = 'IS';
    $start = 0;//Which page to start from
    $limit = null;//How many pages there are

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
    $API_Call = function ($href='https://island.is/_next/data/GKJASp-jRRb59Zo4zX6AA/reglugerdir.json') {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $href,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = new simple_html_dom(curl_exec($curl));
        $response = $response->find('#__NEXT_DATA__', 0)->innertext;
        curl_close($curl);
        return (json_decode($response, true));
    };

    //Defines ministry codes
    $ministries = array('avnsr'=>'{"is":"Atvinnuvega- og nýsköpunarráðuneyti (fyrrum ráðun.)", "en":"Ministry of Industries and Innovation (former appointment)"}', 
                        'fjehr'=>'{"is":"Fjármála- og efnahagsráðuneyti", "en":"Ministry of Finance and Economic Affairs"}', 
                        'hvin'=>'{"is":"Háskóla-, iðnaðar- og nýsköpunarráðuneyti", "en":"Ministry of Higher Education, Industry and Innovation"}', 
                        'dmr'=>'{"is":"Dómsmálaráðuneyti", "en":"Ministry of Justice"}', 
                        'frn'=>'{"is":"Félags- og vinnumarkaðsráðuneyti", "en":"Ministry of Social Affairs and Labour Market"}', 
                        'fsr'=>'{"is":"Forsætisráðuneyti", "en":"Office of the Prime Minister"}', 
                        'inr'=>'{"is":"Innviðaráðuneyti", "en":"Ministry of Infrastructure"}', 
                        'mar'=>'{"is":"Matvælaráðuneyti", "en":"Ministry of Food and Agriculture"}', 
                        'mrn'=>'{"is":"Mennta- og barnamálaráðuneyti", "en":"Ministry of Education and Children"}', 
                        'mvr'=>'{"is":"", "en":"Ministry of Culture and Trade"}', 
                        'urn'=>'{"is":"Umhverfis-, orku- og loftslagsráðuneyti", "en":"Ministry of the Environment, Energy and Climate"}', 
                        'urr'=>'{"is":"Utanríkisráðuneyti", "en":"Ministry for Foreign Affairs"}', 
                        'hr'=>'{"is":"", "en":"Ministry of Health"}');

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["IS"]';
    $type = "Law";
    $publisher = '{"is":"Stjórnarráð Íslands", "en":"The Government of Iceland"}';

    //Gets the limit
    echo json_encode($API_Call()['props']['pageProps']['pageProps']['layoutProps'], JSON_UNESCAPED_UNICODE);
    $limit = $limit ?? $API_Call()['props']['pageProps']['pageProps']['layoutProps']['regulations']['totalPages']; echo $limit;
    //Loops through the pages
    for ($page = $start; $page < $limit; $page++) {
        //Processes the data
        $regulations = $API_Call()['props']['pageProps']['pageProps']['componentProps']['regulations']['data'];
        foreach ($regulations as $regulation) {
            //Gets values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($regulation['effectiveDate']));
            $ID = $scraper.':'.str_replace('/', '', $regulation['name']);
            $name = fixQuotes($regulation['title'], 'is');
            //Gets regime
            switch(true) {
                case strtotime($enactDate) <= strtotime('today'):
                    $regime = '{"is":"Lýðveldið Ísland", "en":"The Republic of Iceland"}';
                    break;
                case strtotime($enactDate) < strtotime('17 June 1944'):
                    $regime = '{"is":"Konungsríkið Ísland", "en":"The Kingdom of Iceland"}';
                    break;
                case strtotime($enactDate) < strtotime('1 December 1918'):
                    $regime = '{"is":"Íslenska Danmörk", "en":"Danish Iceland"}';
                    break;
                case strtotime($enactDate) < strtotime('14 January 1814'):
                    $regime = '{"is":"Danmörk-Noregur", "en":"Denmark-Norway"}';
                    break;
                case strtotime($enactDate) < strtotime('1537-01-01'):
                    $regime = '{"is":"Kalmarsambandið", "en":"The Kalmar Union"}';
                    break;
                case strtotime($enactDate) < strtotime('1397-01-01'):
                    $regime = '{"is":"Gamla konungsríkið Noreg", "en":"The Old Kingdom of Norway"}';
                    break;
                case strtotime($enactDate) < strtotime('930-01-01'):
                    $regime = '{"is":"Íslenska fríríkið", "en":"The Icelandic Free State"}';
                    break;
            }
            //Gets the rest of the values
            $origin = strtr($regulation['ministry']['slug'], $ministries);
            $status = $regulation['repealed'] ? 'Repealed':'Valid';
            $source = 'https://island.is/reglugerdir/nr/'.str_replace('/', '-', $regulation['name']);

            //JSONifies the values
            $name = '{"is":"'.$name.'"}';
            $source = '{"is":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `origin`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$origin."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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

    //Closes the connection
    $conn->close(); $conn2->close();
?>