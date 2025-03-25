<html><body>
    <?php
        //Settings
        $test = true; $country = 'KR';
        $start = 0;//Where to start from
        $limit = array('ko'=>6183, 'en'=>2691);//Total number of laws desired. Current max is 6180 for Korean and 2691 for English

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; //'../' refers to the parent directory
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

        //Loops through the languages
        $realPaths = array('ko'=>'lsSc.do'/*, 'en'=>'LSW/eng/engLsSc.do'*/);
        foreach(array('ko'=>'lsScListR.do'/*, 'en'=>'LSW/eng/engLsScListWideR.do'*/) as $lang=>$path) {
            //Creates curl handler for search
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.law.go.kr/'.$path,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('q' => '*','outmax' => $limit[$lang],'pg' => 1,'fsort'=>'20,11,31','section' => 'lawNm','lsiSeq' => 0),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: elevisor_for_j2ee_uid=2utyv1bjtwwm1; JSESSIONID=W2tMUjkIKJHhOLQby76G4jmv.LSW2'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);

            //Parses the data
            $html_dom->load($response);

            //Processes the data in the table
            $laws = $html_dom->find('#viewHeightDiv')[0]->find('table')[0]->find('tbody')[0]->find('tr');
            foreach($laws as $row_num => $law) {
                $vals = array(//Array stores variables from a table
                    'Source' => '',
                    'Title' => '',
                    'enactDate' => '',
                    'Type' => '',
                    'ID' => '',
                    'enforceDate' => '',
                    'Revision' => '',
                    'Origin' => '',
                );
    
                //Gets the datapoints from cells
                $cells = $law->find('td');
                for($cell = 1; $cell <= 7; $cell++) {
                    $vals[array_keys($vals)[$cell]] = trim($cells[$cell]->plaintext);
                }

                //Gets the source and status
                $vals['Source'] = 'https://www.law.go.kr/'.$realPaths[$lang].'?query='.str_replace(' ', '+', $vals['Title']).'#liBgcolor0';
                $status = 'Valid';
    
                //Finalizes date and ID
                $vals['enactDate'] = strtr($vals['enactDate'], array('. '=>'-', '.'=>''));
                $vals['enforceDate'] = strtr($vals['enforceDate'], array('. '=>'-', '.'=>'')) ?? $vals['enactDate'];
                $lastactDate = $vals['enactDate'];
                $vals['ID'] = $country.'-'.str_replace('/', '', $vals['enforceDate']).strtr($vals['ID'], array('No.'=>'', '제'=>'', '호'=>''));

                //Gets the regime
                switch(true) {
                    case strtotime('12 October 1897') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('22 August 1910'):
                        $regime = 'The Empire of Korea';
                        break;
                    case strtotime('22 August 1910') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('15 August 1945'):
                        $regime = 'The Empire of Japan';
                        break;
                    case strtotime('15 August 1945') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('15 August 1948'):
                        $regime = 'The United States Military Government in Korea';
                        break;
                    case strtotime('15 August 1948') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime(date('d M Y')):
                        $regime = 'The Republic of Korea';
                        break;
                }

                //Makes sure there are no appostophes in the title or origin
                $vals['Title'] = str_replace("'", "’", $vals['Title']);
                $vals['Source'] = str_replace("'", "’", $vals['Source']);
                $vals['Origin'] = str_replace("'", "’", $vals['Origin']);

                //Makes sure that the law is not an ammendment
                if ($vals['Revision'] === '제정' || $vals['Revision'] === 'New Enactment') {
                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$vals['ID']."'";
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

                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."', `type`='".$type."', `origin`='".$origin."' WHERE `ID`='".$vals['ID']."'";
                        }
                    } else {
                        //JSONifies the values
                        $name = '{"'.$lang.'":"'.$vals['Title'].'"}';
                        $source = '{"'.$lang.'":"'.$vals['Source'].'"}';
                        $type = '{"'.$lang.'":"'.$vals['Type'].'"}';
                        $origin = '{"'.$lang.'":"'.$vals['Origin'].'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `origin`, `status`, `source`)
                                VALUES ('".$vals['enactDate']."', '".$vals['enforceDate']."', '".$lastactDate."', '".$vals['ID']."', '".$regime."', '".$name."', '".$type."', '".$origin."', '".$status."', '".$source."')";
                    }
                    
                    //Executes the SQL
                    echo $row_num.'. '.$SQL2.'<br/>';
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