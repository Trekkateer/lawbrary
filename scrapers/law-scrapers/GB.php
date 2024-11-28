<html><body>
    <?php
        //Settings
        $test = true;
        $start = 1;//Which page to start from
        $limit = null;//How many pages there are

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

        //Gets the types
        $typesAndOrigins = [
            'ukpga' => ['Act', '\'{"en":"The British Parliament"}\'', 'GB'],
            'ukppa' => ['Act', '\'{"en":"The British Parliament"}\'', 'GB'],
            'apgb'  => ['Act', '\'{"en":"The British Parliament"}\'', 'GB'],
            'aep'   => ['Act', '\'{"en":"The English Parliament"}\'', 'GB'],
            'aip'   => ['Act', '\'{"en":"The Old Irish Parliament"}\'', 'GB-NIR'],
            'nia'   => ['Act', '\'{"en":"The Assembly of Northern Ireland"}\'', 'GB-NIR'],
            'apni'  => ['Act', '\'{"en":"The Parliament of Northern Ireland"}\'', 'GB-NIR'],
            'asp'   => ['Act', '\'{"en":"The Scottish Parliament"}\'', 'GB-SCT'],
            'aosp'  => ['Act', '\'{"en":"The Old Scottish Parliament"}\'', 'GB-SCT'],
            'asc'   => ['Act', '\'{"en":"The Senedd Cymru"}\'', 'GB-WLS'],
            'anaw'  => ['Act', '\'{"en":"The National Assembly for Wales"}\'', 'GB-WLS'],

            'ukla'  => ['Local Act', 'NULL', 'GB'],
            'gbla'  => ['Local Act', '\'{"en":"The British Parliament"}\'', 'GB'],

            'ukcm'  => ['Measure', '\'{"en":"The Church of England"}\'', 'GB'],
            'mnia'  => ['Measure', '\'{"en":"The Assembly of Northern Ireland"}\'', 'GB-NIR'],
            'mwa'   => ['Measure', '\'{"en":"The National Assembly for Wales"}\'', 'GB-WLS'],
        ];

        //Gets the limit ('primary+secondary for all legislation')
        $html_dom->load($HTTP_Call('https://www.legislation.gov.uk/primary?page='.$start));
        $nextLimit = $limit ?? explode('page=', end($html_dom->find('div.prevPagesNextNav')[0]->find('li.pageLink'))->find('a')[0]->href)[1];

        //Loops through the pages
        $jurisdictions = array();
        for ($page = $start; $page <= $nextLimit; $page++) {echo "<br/>p.".$page." ";
            //Gets the new limit
            $html_dom->load($HTTP_Call('https://www.legislation.gov.uk/primary?page='.$page));// echo $html_dom;
            $nextLimit = $limit ?? explode('page=', end($html_dom->find('div.prevPagesNextNav')[0]->find('li.pageLink'))->find('a')[0]->href)[1];

            //Processes the data
            $laws = $html_dom->find('#content')[0]->find('table')[0]->find('tbody')[0]->find('tr');
            foreach ($laws as $law) {
                //Gets the correct Lawbrary page and flushes it if needed
                $LBpage = $typesAndOrigins[explode('/', $law->find('a')[0]->href)[1]][2];
                if (!in_array($LBpage, $jurisdictions)) {
                    $jurisdictions[] = $LBpage;//Adds the jurisdiction to the array

                    //Clears the table
                    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
                    if (!$test) {$conn->query($SQL1);}
                }

                //Gets the language
                $lang = $law->find('a')[0]->{'xml:lang'} ?? 'en';

                //Gets the rest of the values
                $enactDate = explode(' ', $law->find('a')[1]->plaintext ?? end(explode(' ', $law->find('a')[0]->plaintext)))[0].'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                //The ID comes from the href, which has the monarch in it. I tried to get rid of it, but that leads to duplicates
                $ID = $LBpage.':'./*preg_replace('/Edw1|Edw2|Edw3|Ric2|Hen4|Hen7|Hen8|Edw6|Eliz1|Ja1|Cha1|Chas1|Cha2|WillandMar|Will|Ann|Geo1|Geo2|Geo3|Geo4|Will4|Will4and1Vict|Vict|Edw7|Edw7and1Geo5|Geo5|Edw8and1Geo6|Geo5and1Edw8|Geo6|Geo6andEliz2|Eliz2/', '', */strtr(explode('contents', $law->find('a')[0]->href)[0], array('/'=>'', '-'=>''));
                $name = trim($law->find('a')[0]->plaintext);
                $country = '["GB"]';
                //Gets the regime
                switch (true) {
                    case strtotime($enactDate) <= strtotime('today'): $regime = 'The United Kingdom of Great Britain and Northern Ireland'; break;
                    case strtotime($enactDate) < strtotime('1922-12-06'): $regime = 'The United Kingdom of Great Britain and Ireland'; break;
                    case strtotime($enactDate) < strtotime('1801-01-01'): $regime = 'The Kingdom of Great Britain'; break;
                    default: $regime = 'The Kingdom of Great Britain'; break;
                }
                //Gets the rest of the values
                $origin = $typesAndOrigins[explode('/', $law->find('a')[0]->href)[1]][1];
                $type = $typesAndOrigins[explode('/', $law->find('a')[0]->href)[1]][0];
                if (str_contains('(Amendment)', $name)) {$isAmend = 1;} else {$isAmend = 0;}
                $status = 'Valid';
                    if (str_contains('(repealed)', $name)) {$status = 'Repealed';}
                $source = 'https://www.legislation.gov.uk'.str_replace('/contents', '', $law->find('a')[0]->href);
                $PDF = 'https://www.legislation.gov.uk'.str_replace('/contents', '/data.pdf', $law->find('a')[0]->href);

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //Creates SQL
                $SQL = "SELECT * FROM `laws".strtolower($LBpage)."` WHERE `ID`='".$ID."'";
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

                        $SQL2 = "UPDATE `laws".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `isAmend`, `status`, `source`, `PDF`)
                             VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$type."', ".$isAmend.", '".$status."', '".$source."', '".$PDF."')";
                }

                //Executes the SQL
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        foreach ($jurisdictions as $jurisdiction) {
            $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$jurisdiction."'"; echo '<br/><br/>'.$SQL3;
            if (!$test) {$conn2->query($SQL3);}
        }
    ?>
</body></html>