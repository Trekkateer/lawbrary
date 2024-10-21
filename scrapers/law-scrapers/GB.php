<html><body>
    <?php
        //Settings
        $test = false; $country = 'GB';
        $start = 1;//Which page to start from
        $step = 20;//How many laws there are on each page
        $limit1 = null;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

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
            $response = curl_exec($curl); curl_close($curl);
            return $response;
        };

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the limit ('primary+secondary for all legislation')
        $html_dom->load($HTTP_Call('https://www.legislation.gov.uk/primary'));
        $limit = $limit1 ?? explode('page=', end($html_dom->find('div.prevPagesNextNav')[0]->find('li[class="pageLink"]'))->find('a')[0]->href)[1];

        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the new limit
            $html_dom->load($HTTP_Call('https://www.legislation.gov.uk/primary?page='.$page));
            $limit = $limit1 ?? explode('page=', end($html_dom->find('div.prevPagesNextNav')[0]->find('li[class="pageLink"]'))->find('a')[0]->href)[1];

            //Processes the data
            $laws = $html_dom->find('div#content')[0]->find('table')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $law) {
                //Gets the language
                $lang = $law->find('td')[0]->find('a')[0]->{'xml:lang'} ?? 'en';

                //Gets the rest of the values
                $enactDate = explode(' ', $law->find('td')[1]->plaintext)[0].'-00-00'; $enforceDate = $enactDate;
                $ID = $country.'-'.str_replace('/', '', explode('contents', $law->find('td')[0]->find('a')[0]->href)[0]);
                $name = trim($law->find('td')[0]->find('a')[0]->plaintext);
                $type = trim($law->find('td')[2]->plaintext);
                    if (str_contains('(Amendment)', $name)) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                    if (str_contains('(repealed)', $name)) {$status = 'Repealed';}
                $source = 'https://www.legislation.gov.uk'.$law->find('td')[0]->find('a')[0]->href;
                $PDF = 'https://www.legislation.gov.uk'.str_replace('contents', 'data.pdf', $law->find('td')[0]->find('a')[0]->href);

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //Creates SQL
                $SQL = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $name;
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                        //JSONifies the href
                        $compoundedSource = json_decode($row['source'], true);
                        $compoundedSource[$lang] = $source;
                        $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                        //JSONifies the pdf
                        $compoundedPDF = json_decode($row['PDF'], true);
                        $compoundedPDF[$lang] = $PDF;
                        $PDF = json_encode($compoundedPDF, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`, `PDF`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '"."Valid"."', '".$source."', '".$PDF."')";
                }

                //Executes the SQL
                echo '<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>