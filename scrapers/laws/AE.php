<?php //The United Arab Emirates
    //Settings
    $test = false; $scraper = 'AE';
    $start = 1;//Which law to start from
    $step = 25;//How many laws per page
    $limit = null;//Total number of laws desired.

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $API_Call = function ($page, $lang) use ($step) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://uaelegislation.gov.ae/'.$lang.'/legislations/list?_token=hx6iIjR9RCaQtKg0VZ3EhktxirGCGFjIq0WJmP7n&paginateBy='.$step.'&subject=null&page='.$page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"_token":"hx6iIjR9RCaQtKg0VZ3EhktxirGCGFjIq0WJmP7n","paginateBy":'.$step.',"lawTypes":[],"subject":""}',
            CURLOPT_HTTPHEADER => array(
                'Cookie: legalapp_session=bA31YLpL5VPdOxM6l8UyLw92nzuJ8EYx99G9gg4s; Persist_legal_c=ffffffff0979123245525d5f4f58455e445a4a42378b; XSRF-TOKEN=eyJpdiI6ImFUQW4yV3h2WVNnRzBYY1g1VG5ZZVE9PSIsInZhbHVlIjoiT0x6RWdXdFU5S2pZKzYwdUZVYXhIZWE1VWl6YlFGaWE5Wm5Rc24zTUFJdE8zSzg5SDFuNVQ3K3IrNlZ2OGFmU0xZTVF2anRJcjJQUWZhR0tqRTVCV2k0MVNUZU1DdzVhTFBMK2gxUy9ORHZwL3E5TThDWnhobDBXRmlZeE02SS8iLCJtYWMiOiJmM2Y0ZjI4ZmNkYmNkMDY1MWFlY2Q5ZTZhZjI5MGViZjRiYzI5OTdmY2M4ZWEzYTlkNzRkYzc5ZTk5ZTg3MWU0IiwidGFnIjoiIn0%3D; __cf_bm=ZbAs75bU85xa9mvVaRtd0Re_m38e7XgFcNGVtjG2GKI-1744525036-1.0.1.1-LZeD3w7kj1YlGnIe_c0jwaeZJTDooscLy.wQni61ZCAHhV1_cJvcwoBz2_WX_vaeEM9Erx90V0HwBPlF4CVlq1NhPf.wA6kDCjQuSxv0JQQ; visitor_count=eyJpdiI6InM1a2ZsdWQ5Mys4dGJjSmY5ZnFQbVE9PSIsInZhbHVlIjoiRDlyZDBOcTNlZldVMFM4UG4vMjEwOXZNUmllSlYzTngxS1dYYkx5M2VJVWxqTUk0V1ZQZ3pwUXMrY1Via2VEVSIsIm1hYyI6Ijc2M2I0NTg0ZmE1ZjVmOGU0NGQzYjdhMjVjNmIxNGM2M2UzNzUwYzU5YmRlZWViMzBiZmZhMjVjZWE5NTIyODIiLCJ0YWciOiIifQ%3D%3D',
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return json_decode($response, true);
    };

    //Translates the type
    $types = array(
        'دستور دولة' => ['Constitution'],

        'قرار وزاري' => ['Decision'],
        'قرار وزير الصحة' => ['Decision', '\'{"ar":"وزير الصحة", "en":"The Minister of Health"}\''],
        'قرار رئيس' => ['Decision', '\'{"ar":"الرئيس", "en":"The President"}\''],
        'قــرار مجلس الوزراء' => ['Decision', '\'{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}\''],
        'قـرار مجلس الوزراء' => ['Decision', '\'{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}\''],
        'قرار مجلس الوزراء' => ['Decision', '\'{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}\''],

        'مرسوم بقانون اتحادي' => ['Decree-Law'],
        'المرسوم بقانون اتحادي' => ['Decree-Law'],
                'مرسوم بقانون' => ['Decree-Law'],
        'قانون اتحادي' => ['Decree'],

        'قانون الاتحادي' => ['Law', '\'{"ar":"المجلس الوطني الاتحادي", "en":"The Federal National Council"}\''],

        'لائحة الجزاءات الإدارية' => ['List'],

        'نظام' => ['System']
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["AE"]'; $status = 'In Force';
    $publisher = '{"ar":"حكومة دولة الإمارات العربية المتحدة", "en":"The Government of the United Arab Emirates"}';

    //Loops through languages
    foreach (array('ar', 'en') as $lang) {
        //Gets the limit
        $limit = $limit ?? $API_Call(0, $lang)['pages'];
        
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets html
            $dom = str_get_html($API_Call($page, $lang)['html']);

            //Gets values
            foreach ($dom->find('div.body_tr') as $law) {
                if ($law->find('div.body_td', 0) == null) {break;}
                $enactDate = $enforceDate = $lastactDate = trim($law->find('div.body_td', 0)->find('span.text_center', 1)->plaintext).'-01-01';
                $ID = $scraper.':'.explode('legislations/', $law->find('div.body_td', 0)->find('a', 0)->href)[1];
                $name = fixQuotes(trim($law->find('div.body_td', 0)->find('a', 0)->plaintext), $lang);
                $regime = strtotime('today') > strtotime('10 February 1972') ? '{"ar":"الإمارات العربية المتحدة", "en":"The United Arab Emirates"}':'{"en":"The British Empire"}';
                //Gets the type and origin
                $type = 'NULL'; $origin = 'NULL';
                foreach ($types as $typeAR=>$typeEN) {
                    if (str_starts_with($name, $typeAR)) {
                        $type = $typeEN[0];
                        $origin = $typeEN[1] ?? 'NULL';
                    }
                }
                //Gets the rest of the values
                $source = $law->find('div.body_td', 0)->find('a', 0)->href;
                $PDF = $source.'/download';

                //Creates SQL
                $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
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

                        //JSONifies the PDF
                        $compoundedPDF = json_decode($row['PDF'], true);
                        $compoundedPDF[$lang] = $PDF;
                        $PDF = json_encode($compoundedPDF, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = '{"'.$lang.'":"'.$PDF.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`, `PDF`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                }

                //Makes the query
                echo 'p. '.$page.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>