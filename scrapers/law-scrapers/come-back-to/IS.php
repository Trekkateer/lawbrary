<html><body>
    <?php //Link is broken
        //Settings
        $test = true; $country = 'IS';
        $start = 0;//Which page to start from
        $limit = null;//How many pages there are

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
        $API_Call = function ($url='https://island.is/_next/data/GKJASp-jRRb59Zo4zX6AA/reglugerdir.json') {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl); curl_close($curl);
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

        //Gets the limit
        $limit = $limit ?? $API_Call()['pageProps']['pageProps']['pageProps']['componentProps']['regulations']['totalPages']; echo $limit;
        //Loops through the pages
        for ($page = $start; $page < $limit; $page++) {
            //Processes the data
            $regulations = $API_Call()['pageProps']['pageProps']['pageProps']['componentProps']['regulations']['data'];
            foreach ($regulations as $regulation) {
                //Gets values
                $enactDate = date('Y/m/d', strtotime($regulation['effectiveDate'])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.str_replace('/', '', $regulation['name']);
                $name = $regulation['title'];
                //Gets regime
                switch(true) {
                    case strtotime('930-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1262-01-01'):
                        $regime = 'The Icelandic Free State';
                        break;
                    case strtotime('1262-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1397-01-01'):
                        $regime = 'The Old Kingdom of Norway';
                        break;
                    case strtotime('1397-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1537-01-01'):
                        $regime = 'The Kalmar Union';
                        break;
                    case strtotime('1537-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 January 1814'):
                        $regime = 'Denmark-Norway';
                        break;
                    case strtotime('14 January 1814') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 December 1918'):
                        $regime = 'Danish Iceland';
                        break;
                    case strtotime('1 December 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('17 June 1944'):
                        $regime = 'The Kingdom of Iceland';
                        break;
                    case strtotime('17 June 1944') < strtotime($enactDate) && strtotime($enactDate) < strtotime(date('d M Y')):
                        $regime = 'The Republic of Iceland';
                        break;
                }
                //Gets the rest of the values
                $type = "Law"; if ($regulation['type'] === 'amending') $type = 'Amendment';
                $origin = strtr($regulation['ministry']['slug'], $ministries);
                $status = 'Valid'; if ($regulation['repealed']) $status = 'Repealed';
                $source = 'https://island.is/reglugerdir/nr/'.str_replace('/', '-', $regulation['name']);

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"is":"'.$name.'"}';
                $source = '{"is":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `origin`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$origin."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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