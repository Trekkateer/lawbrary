<html><body>
    <?php //!!The website firewall is blocking cURL
        //Settings
        $test = true; $country = 'SG';
        $start = 0;//Which year to start from
        $step = 500;//How many laws to request at a time
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

        //Sets up cURL querying function
        $HTTP_Call = function($href) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $href,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: ASP.NET_SessionId=ckmeswybanc3snexgc2ypjiu'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return $response;
        };
 
        //Gets the limit
        $html_dom->load($HTTP_Call('https://sso.agc.gov.sg/Browse/Act/Current/All?PageSize='.$step.'&SortBy=Title&SortOrder=ASC'));
        $limit = $limit ?? explode('?', explode('/', $html_dom->find('a[aria-label="Last Page"]')[0]->href)[5])[0];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom->load($HTTP_Call('https://sso.agc.gov.sg/Browse/Act/Current/All/'.$page.'?PageSize='.$step.'&SortBy=Title&SortOrder=ASC'));
            $laws = $html_dom->find('table.table.browse-list')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = end(explode(' ', trim($law->find('td')[0]->find('a.non-ajax')[0]->plaintext))).'/01/01'; $enforceDate = $enactDate;
                $ID = $country.'-'.explode('/Act/', $law->find('td')[0]->find('a.non-ajax')[0]->href)[1];
                $name = trim($law->find('td')[0]->find('a.non-ajax')->plaintext);
                $type = 'Act'; $status = 'Valid';
                $source = 'https://sso.agc.gov.sg'.$law->find('td')[0]->find('a.non-ajax')[0]->href;
                $pdf = 'https://sso.agc.gov.sg'.$law->find('td')[1]->find('a.non-ajax')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $pdf = '{"en":"'.$pdf.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`, `pdf`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
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