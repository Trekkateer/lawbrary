<html><body>
    <?php
        //Settings
        $test = true; $LBpage = 'AE';
        $start = 1;//Which law to start from
        $step = 25;//How many laws per page
        $limit = null;//Total number of laws desired.

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
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $API_Call = function ($page, $lang) use ($step) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://uaelegislation.gov.ae/'.$lang.'/legislations/list?_token=QBX9Z4vLfTrfG0ePlyiuVI3ih28tIdQrR1e6KMlV&paginateBy='.$step.'&subject=null&page='.$page,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{"_token":"QBX9Z4vLfTrfG0ePlyiuVI3ih28tIdQrR1e6KMlV","paginateBy":'.$step.',"lawTypes":[],"subject":""}',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: XSRF-TOKEN=eyJpdiI6IlpXdjZ3cnJxYlhIVDcxamZPMDRLVlE9PSIsInZhbHVlIjoiVTJ1ZE43NjkzSzVuSHBmempuVXF0d2RId3pkaEFXK1p2UFZEZTJibnY0Q3lhdjh6aDhiVjVneGNSMURIS21JOTl1QWh1VlVoSDlsdll6TGkzUmxRMUJzMU5KR0NOVk1NQk9DRlpiVnNwbWpqem9TZ2hDaFZqYmdRVjA0ak84d20iLCJtYWMiOiI4ZTIwMTgxNjNhMWIwMGM1YTU3ZTIxZTIyYTZkOWI1ZjhkODQ3Y2E0MjVmNzdjYWQ0NjUxZGQ2NmE2NmZkZTQyIiwidGFnIjoiIn0%3D; legalapp_session=XYdzVlmvPqi37q7GVG3EEAGO9DdCfiIHRsqf1hmv; Persist_legal_c=ffffffff0979121c45525d5f4f58455e445a4a42378b;',
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
            'قرار وزير الصحة' => ['Decision', '{"ar":"وزير الصحة", "en":"The Minister of Health"}'],
            'قرار رئيس' => ['Decision', '{"ar":"الرئيس", "en":"The President"}'],
            'قــرار مجلس الوزراء' => ['Decision', '{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}'],
            'قـرار مجلس الوزراء' => ['Decision', '{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}'],
            'قرار مجلس الوزراء' => ['Decision', '{"ar":"مجلس الوزراء", "en":"The Council of Ministers"}'],

            'مرسوم بقانون اتحادي' => ['Decree-Law'],
            'المرسوم بقانون اتحادي' => ['Decree-Law'],
                    'مرسوم بقانون' => ['Decree-Law'],
            'قانون اتحادي' => ['Decree'],

            'قانون الاتحادي' => ['Law', '{"ar":"المجلس الوطني الاتحادي", "en":"The Federal National Council"}'],

            'لائحة الجزاءات الإدارية' => ['List'],

            'نظام' => ['System']
        );

        //Loops through languages
        foreach (array('ar', 'en') as $lang) {
            //Gets the limit
            $limit = $limit ?? $API_Call(0, $lang)['pages'];
            
            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Gets html
                $html_dom->load($API_Call($page, $lang)['html']);

                //Gets values
                foreach ($html_dom->find('div.body_tr') as $law) {
                    if (!isset($law->find('div.body_td')[0])) {break;}
                    $enactDate = trim($law->find('div.body_td')[0]->find('span.text_center')[1]->plaintext).'-01-01'; $enforceDate = $enactDate; $lastactDate = $enforceDate;
                    $ID = $LBpage.'-'.explode('legislations/', $law->find('div.body_td')[0]->find('a')[0]->href)[1];
                    $name = trim($law->find('div.body_td')[0]->find('a')[0]->plaintext);
                    $country = '["AE"]';
                    $regime = strtotime('today') > strtotime('10 February 1972') ? '{"ar":"الإمارات العربية المتحدة", "en":"The United Arab Emirates"}':'{"en":"The British Empire"}';
                    //Gets the type and origin
                    $type = 'NULL'; $origin = 'NULL';
                    foreach ($types as $typeAR=>$typeEN) {
                        if (str_starts_with($name, $typeAR)) {
                            $type = $typeEN[0];
                            $origin = $typeEN[1] ?? 'NULL';
                        }
                    }
                    $origin = $origin !== 'NULL' ? "'".$origin."'":$origin;
                    //Gets the rest of the values
                    $status = 'In Force';
                    $source = $law->find('div.body_td')[0]->find('a')[0]->href;
                    $PDF = $source.'/download';

                    //Makes sure there are no quotes in the name
                    //TODO: Figure out how to do this with Arabic
                    $name = strtr($name, array("'" => "’", ' "' => " “", '"' => "”"));

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

                            //JSONifies the PDF
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
                        $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `status`, `source`, `PDF`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$type."', ".$status."', '".$source."', '".$PDF."')";
                    }

                    //Makes the query
                    echo 'p. '.$page.' '.$SQL2.'<br/>';
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
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$LBpage."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>