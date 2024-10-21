<html><body>
    <?php
        //Settings
        $test = true; $country = 'AE';
        $start = 1;//Which law to start from
        $step = 50;//How many laws per page
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
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $API_Call = function ($page, $lang) use ($step) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://uaelegislation.gov.ae/'.$lang.'/legislations/list?_token=LnDMPlUA9OCaqrNAoQASxCBvL71LbxJxHcIL8gUf&paginateBy='.$step.'&subject=&page='.$page,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{"_token":"LnDMPlUA9OCaqrNAoQASxCBvL71LbxJxHcIL8gUf","paginateBy":'.$step.',"lawTypes":[],"subject":""}',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: visitor_count=eyJpdiI6IjhYQ2FBYWpuUGdBcXpTRzJESFZOT0E9PSIsInZhbHVlIjoiN0RRWWZOdzYxSWo3VHV5QUNHZmgvaXBySFdtdFZRb0tYTC92R1J6ZThHV0xOVFZTRUgxRHViSDRWallQNDVOQSIsIm1hYyI6IjU4NDFiZjdmMzE0MGIyMTc5YWJmYzBmNTdkNzJkYTQ1NWMzNzljMmUyOWY3ZDJiYWY2YTEyOWUxNmY0Y2Q3ZDkiLCJ0YWciOiIifQ%3D%3D; legalapp_session=I4ddbDzkRigXi8qEyMxYTwQP1cFyzSDGez9j2zDe; Persist_legal_c=ffffffff0979120745525d5f4f58455e445a4a42378b',
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return json_decode($response, true);
        };

        //Loops through languages
        foreach (array('ar', 'en') as $lang) {
            //Gets the limit
            $limit = $limit ?? $API_Call(0, $lang)['pages']; echo $limit;
            
            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Gets html
                $html_dom->load($API_Call($page, $lang)['html']);

                //Gets values
                foreach ($html_dom->find('div.body_tr') as $law) {
                    if (!isset($law->find('div.body_td')[0])) {break;}
                    $enactDate = date('Y/m/d', strtotime(trim($law->find('div.body_td')[0]->find('span.text_center')[1]->plaintext))); $enforceDate = $enactDate;
                    $ID = $country.'-'.explode('legislations/', $law->find('div.body_td')[0]->find('a')[0]->href)[1];
                    $name = trim($law->find('div.body_td')[0]->find('a')[0]->plaintext);
                    $type = 'Law'; $status = 'In Force';
                    $source = $law->find('div.body_td')[0]->find('a')[0]->href;
                    $PDF = $law->find('div.body_td')[0]->find('a')[0]->href.'/download';

                    //Makes sure there are no quotes in the name
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

                            //JSONifies the PDF
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
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                    }

                    //Makes the query
                    echo $SQL2.'<br/>';
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