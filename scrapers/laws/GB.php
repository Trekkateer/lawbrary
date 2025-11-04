<?php //The United Kingdom

    ### TODO: ###
    //Find a way to detect treaties

    //Settings
    $test = false; $scraper = 'GB';
    $start = 1;//Which page to start from
    $liminit = NULL;//How many pages we want. Natural limit is 1770 currently.

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the main page
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

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

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["GB"]';
    $publisher = '{"en":"The National Archives of the United Kingdom"}';

    //Gets the limit ('primary+secondary for all legislation')
    $dom = str_get_html($HTTP_Call('https://www.legislation.gov.uk/primary?page='.$start));
    $limit = $liminit ?? explode('page=', $dom->find('div.prevPagesNextNav', 0)->find('li.pageLink.next', 0)->find('a', 0)->href)[1];
    //Loops through the pages
    $pagesUpdated = array();
    for ($page = $start; $page <= $limit; $page++) {echo "<br/>P".$page." ";
        //Gets the new limit
        $dom = str_get_html($HTTP_Call('https://www.legislation.gov.uk/primary?page='.$page));
        $limit = $liminit ?? explode('page=', $dom->find('div.prevPagesNextNav', 0)->find('li.pageLink.next', 0)->find('a', 0)->href ?? 'page='.$limit)[1];

        //Processes the data
        $laws = $dom->find('#content', 0)->find('table', 0)->find('tbody', 0)->find('tr');
        foreach ($laws as $law) {
            //Gets the correct Lawbrary page and flushes it if needed
            $LBpage = $typesAndOrigins[explode('/', $law->find('a', 0)->href)[1]][2];
            if (!in_array($LBpage, $pagesUpdated) && $LBpage !== $scraper) {
                $pagesUpdated[] = $LBpage;//Adds the jurisdiction to the array
                //Clears the table if it has not been cleared yet
                $SQL10 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($LBpage)."`"; echo '<br/>'.$SQL10.'<br/>';
                if (!$test) {$conn->query($SQL10);}
            }

            //Gets the language
            $lang = $law->find('a', 0)->{'xml:lang'} ?? 'en';

            //Gets the rest of the values
            $enactDate = $enforceDate = $lastactDate = explode(' ', $law->find('a', 1)->plaintext ?? end(explode(' ', $law->find('a', 0)->plaintext)))[0].'-01-01';
            //The ID comes from the href, which has the monarch in it. I tried to get rid of it, but that leads to duplicates
            $ID = $LBpage.':'./*preg_replace('/Edw1|Edw2|Edw3|Ric2|Hen4|Hen7|Hen8|Edw6|Eliz1|Ja1|Cha1|Chas1|Cha2|WillandMar|Will|Ann|Geo1|Geo2|Geo3|Geo4|Will4|Will4and1Vict|Vict|Edw7|Edw7and1Geo5|Geo5|Edw8and1Geo6|Geo5and1Edw8|Geo6|Geo6andEliz2|Eliz2/', '', */strtr(explode('contents', $law->find('a', 0)->href)[0], array('/'=>'', '-'=>''));
            $name = fixQuotes(trim($law->find('a', 0)->plaintext), $lang);
            //Gets the regime
            switch (true) {
                case strtotime($enactDate) <= strtotime('today'): $regime = '{"en":"The United Kingdom of Great Britain and Northern Ireland"}'; break;
                case strtotime($enactDate) < strtotime('1922-12-06'): $regime = '{"en":"The United Kingdom of Great Britain and Ireland"}'; break;
                case strtotime($enactDate) < strtotime('1801-01-01'): $regime = '{"en":"The Kingdom of Great Britain"}'; break;
                default: $regime = '{"en":"The Kingdom of Great Britain"}'; break;
            }
            //Gets the rest of the values
            $origin = $typesAndOrigins[explode('/', $law->find('a', 0)->href)[1]][1];
            $type = $typesAndOrigins[explode('/', $law->find('a', 0)->href)[1]][0];
            $isAmend = str_contains($name, 'Amendment') ? 1:0;
            $status = str_contains($name, '(repealed)') ? 'Repealed':'Valid';
            $source = 'https://www.legislation.gov.uk'.str_replace('/contents', '', $law->find('a', 0)->href); $PDF = $source.'/data.pdf';

            //Creates SQL
            $SQL = "SELECT * FROM `".strtolower($LBpage)."` WHERE `ID`='".$ID."'";
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

                    $SQL2 = "UPDATE `".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                }
            } else {
                //JSONifies the name and href
                $name = '{"'.$lang.'":"'.$name.'"}';
                $source = '{"'.$lang.'":"'.$source.'"}';
                $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `isAmend`, `status`, `source`, `PDF`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', ".$isAmend.", '".$status."', '".$source."', '".$PDF."')";
            }

            //Executes the SQL
            echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }
    }
    
    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".strtolower($scraper)."'"; echo '<br/><br/>'.$SQL3;
    foreach ($pagesUpdated as $page) {
        $SQL3 = "UPDATE `divisions` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".strtolower($page)."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    }

    //Closes the connections
    $conn->close(); $conn2->close();
?>