<html><body>
    <?php //!! The government website is currently not working
        //Settings
        $test = true; $scraper = 'MM';
        $start = 0;//Where to start from
        $limit = null;//Total number of laws desired

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; //'../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Decodes the months
        $months = array('1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec');

        //Loops through the languages
        foreach(array('my'=>array('01', '2_1_1'), 'en'=>array('05', '2_3_1')) as $lang=>$fields) {
            //Creates curl handler for search
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.mlis.gov.mm/lsScListJsp.do',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'pageSize'=>'50',
                    'pageIndex'=>1,
                    'upperLawordKndCode'=>'0100',
                    'queryType'=>'01',
                    'ordrType'=>'01',
                    'query'=>'',
                    'selFont'=>'Z1',
                    'menuInfo'=>'2_3_1',
                    'ordrTypeValue'=>'ASC'
                ),
                CURLOPT_POSTFIELDS => '{
                    "pageSize": "50",
                    "pageIndex": 1,
                    "upperLawordKndCode": "0100",
                    "queryType": "'.$fields[0].'",
                    "ordrType": "01",
                    "query": "",
                    "selFont": "Z1",
                    "menuInfo": "'.$fields[1].'",
                    "ordrTypeValue": "ASC"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: JSESSIONID=4A52F1E1599426EC24910043088F549B',
                    //'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl); echo 'test'.$response;

            //Parses the data
            $html_dom->load($response);

            //Processes the data in the table
            $body_rows = $html_dom->find('#viewHeightDiv')[0]->find('table')[0]->find('tbody')[0]->find('tr');
            foreach($body_rows as $row_num => $body_row) {
                $vals = array(//Sets up the values for each law
                    'Source' => '',
                    'Title' => '',
                    'Enactment Date' => '',
                    'Type' => '',
                    'ID' => '',
                    'Enforcement Date' => '',
                    'Revision' => '',
                    'Origin' => '',
                );
    
                //Gets the datapoints from cells
                $cells = $body_row->find('td');
                for($cell = 1; $cell <= 7; $cell++) {
                    $vals[array_keys($vals)[$cell]] = trim($cells[$cell]->plaintext);
                }

                //Gets the source
                $vals['Source'] = 'https://www.law.go.kr/'.$realPaths[$lang].'?query='.str_replace(' ', '+', $vals['Title']).'#liBgcolor0';
    
                //Finalizes date and ID
                $vals['Enactment Date'] = date('Y/m/d', strtotime($months[explode(' ', str_replace('.', '', $vals['Enactment Date']))[1]].' '.explode(' ', str_replace('.', '', $vals['Enactment Date']))[2].' '.explode(' ', str_replace('.', '', $vals['Enactment Date']))[0]));
                $vals['Enforcement Date'] = date('Y/m/d', strtotime($months[explode(' ', str_replace('.', '', $vals['Enforcement Date']))[1]].' '.explode(' ', str_replace('.', '', $vals['Enforcement Date']))[2].' '.explode(' ', str_replace('.', '', $vals['Enforcement Date']))[0])) ?? $vals['Enactment Date'];
                $vals['ID'] = $scraper.':'.str_replace('/', '', $vals['Enforcement Date']).strtr($vals['ID'], array('No.'=>'', '제'=>'', '호'=>''));

                //Makes sure there are no appostophes in the title or origin
                $vals['Title'] = str_replace("'", "’", $vals['Title']);
                $vals['Origin'] = str_replace("'", "’", $vals['Origin']);
                $vals['Source'] = str_replace("'", "’", $vals['Source']);

                if ($vals['Revision'] === '제정' || $vals['Revision'] === 'New Enactment') {
                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `laws".strtolower($scraper)."` WHERE `ID`='".$vals['ID']."'";
                    $result = $conn->query($SQL);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            //JSONifies the name
                            $compoundedName = json_decode($row['name'], true);
                            $compoundedName[$lang] = $vals['Title'];
                            $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                            //JSONifies the href
                            $compoundedSource = json_decode($row['source'], true);
                            $compoundedSource[$lang] = $vals['Source'];
                            $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                            //JSONifies the type
                            $compoundedType = json_decode($row['type'], true);
                            $compoundedType[$lang] = $vals['Type'];
                            $type = json_encode($compoundedType, JSON_UNESCAPED_UNICODE);

                            //JSONifies the origin
                            $compoundedOrigin = json_decode($row['origin'], true);
                            $compoundedOrigin[$lang] = $vals['Origin'];
                            $origin = json_encode($compoundedOrigin, JSON_UNESCAPED_UNICODE);

                            $SQL2 = "UPDATE `laws".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `type`='".$type."', `origin`='".$origin."' WHERE `ID`='".$vals['ID']."'";
                        }
                    } else {
                        //JSONifies the values
                        $name = '{"'.$lang.'":"'.$vals['Title'].'"}';
                        $source = '{"'.$lang.'":"'.$vals['Source'].'"}';
                        $type = '{"'.$lang.'":"'.$vals['Type'].'"}';
                        $origin = '{"'.$lang.'":"'.$vals['Origin'].'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `savedDate`, `ID`, `name`, `type`, `origin`, `status`, `source`)
                                VALUES ('".$vals['Enactment Date']."', '".$vals['Enforcement Date']."', '".$vals['Enactment Date']."', '".date('Y-m-d')."', '".$vals['ID']."', '".$name."', '".$type."', '".$origin."', '"."Valid"."', '".$source."')";
                    }
                    
                    //Executes the SQL
                    //echo $row_num.'. '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }

        //Connects to the content database
        $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>