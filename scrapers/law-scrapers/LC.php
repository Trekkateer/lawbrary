<html><body>
    <?php
        //Settings
        $test = true; $country = 'LC';
        $start = 2003;//Which year to start from
        $limit = null;//Which year to end at

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
            return $response;
        }

        //Sanitizes the data
        $sanitize = array('  '=>' ');

        //Makes sure the ID is 3 digits long
        function zero_buffer ($inputNum, $outputLen=3) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops through the years
        for ($year = $start; $year <= $limit; $year++) {
            //Loops through the types of laws
            foreach (array('acts/'=>'Act', 'si/'=>'Statutory Instrument') as $page => $type) {
                //Gets the data
                $html_dom->load(HTTP_Call('https://npc.govt.lc/laws/'.$page.$year));
                $laws = $html_dom->find('div.col-lg-10')[0]->find('table')[0]->find('a');
                foreach ($laws as $law) {
                    //Gets values
                    $enactDate = $year.'-01-01'; $enforceDate = $enactDate; $lastactDate = $enforceDate;
                    $ID = $country.'-A'.zero_buffer(explode(' ', explode(' - ', $law->plaintext)[0])[2]).$year;
                    //Gets the regime TODO:
                    if (strtotime($enactDate) < strtotime('22 February 1979')) {
                        $regime = 'The British Empire';
                    } else {$regime = 'Saint Lucia';}
                    $name = trim(explode('- Price', strtr($law->plaintext, $sanitize))[0]); 
                    $lawType = $type; $status = 'Valid';
                        if (str_contains($name, 'Amendment')) {$lawType = $lawType.' Amendment';}
                    $source = $law->href;

                    //Makes sure there are no quotes in the title or URL
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}                    
                    if (str_contains($source, "'")) {$source = str_replace("'", "%27", $source);}

                    //JSONifies the values
                    $name = '{"en":"'.$name.'"}';
                    $source = '{"en":"'.$source.'"}';
                    
                    //Inserts the new laws
                    //echo $name.'<br/>';
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
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