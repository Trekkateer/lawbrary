<html><body>
    <?php //Website not responsive to cURL or file_get_html
        //Settings
        $test = true; $country = 'AG';
        $start = 1981;//Which year to start from
        $limit = null;//Which year to end at

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php'; // '../' refers to the parent directory
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

        //Makes sure every number has a certain number of digits
        function zero_buffer ($inputNum, $outputLen=2) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

        //Sets up querying function
        $HTTP_Call = function ($href) {
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
            $response = curl_exec($curl); curl_close($curl); echo $response.'test<br/>';
            return $response;
        };

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom->load($HTTP_Call('https://laws.gov.ag/annual/?variable='.$page)); echo $html_dom.'test2';
            $laws = $html_dom->find('#main-content')->find('div.container')[0]->find('table')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $lawNum => $law) {
                //Gets values
                $enactDate = trim($law->find('td')[1]->plaintext).'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.trim($law->plaintext).$lawNum;
                $regime = 'Antigua and Barbuda';
                $name = trim($law->find('td')[0]->plaintext);
                $type = 'Act'; $status = 'Valid';
                    if (str_contains($name, 'Amendment')) {$type = $type.' Amendment';}
                $source = $law->find('td')[0]->href; $PDF = $source;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $pdf = '{"en":"'.$pdf.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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