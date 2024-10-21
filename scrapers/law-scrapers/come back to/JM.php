<html><body>
    <?php //!!No date data is availiable on the front page
        //!!Request is not working
        //Settings
        $test = true; $country = 'JM';
        $start = 0;//Which law to start from
        $step = 500;//How many laws on a page
        $limit = null;//Total number of laws desired

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


        //Sets up querying function
        $API_Call = function ($start, $length) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://laws.moj.gov.jm/library/statutes/revised?draw=2&columns[0][data]=shortTitle&columns[0][name]=&columns[0][searchable]=true&columns[0][orderable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=legalAreas&columns[1][name]=&columns[1][searchable]=false&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=actions&columns[2][name]=&columns[2][searchable]=false&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&start=0&length=10&search[value]=&search[regex]=false&_dt=dt',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: PHPSESSID=61v95lgto38rjtknqsp8gv4mlh'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl); echo ($response);
            return json_decode($response, true);
        };

        //Gets the total number of laws
        $limit = $limit ?? $API_Call($start, $step)['recordsFiltered']; echo $limit;
        for ($page = $start; $page <= $limit; $page += $step) {
            //Interprets the data
            $laws = $API_Call($page, $step)['data'];
            foreach ($laws as $law) {
                //Interprets the data
                $ID = $country.'-'.$law['DT_RowId'];
                $name = $html_dom->load($law['shortTitle'])->find('a')[0]->plaintext;
                $type = 'Revised Statute'; $status = 'In Force';
                $source = 'https://laws.moj.gov.jm'.$html_dom->load($law['shortTitle'])->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the title and source
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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