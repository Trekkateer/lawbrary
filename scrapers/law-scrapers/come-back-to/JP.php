<html><body>
    <?php //Enactment dates are not immediately available
        //Settings
        $test = true; $country = 'JP';
        $start = 0;//Where to start from

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

        //Preloop arrays
        $months = array('1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec');

        //Loops through the languages
        $realPaths = array('ko'=>'lsSc.do', 'en'=>'LSW/eng/engLsSc.do');
        foreach(array('jp'=>array('あ','い','う','え','お','か','き','く','け','こ','さ','し','す','せ','そ','た','ち','つ','て','と','な','に','ね','の','は','ひ','ふ','へ','ほ','ま','み','む','め','も','や','ゆ','よ','り','れ','ろ')/*, 'en'=>array('A','B','C','D','E','F','G','H','I','J','L','M','N','O','P','Q','R','S','T','U','V','W')*/) as $lang=>$alphabet) {
            //Loops through the alphabet
            foreach ($alphabet as $letter) {
                //Creates curl handler for search
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://www.japaneselawtranslation.go.jp/'.$lang.'/laws/result/',
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('_csrfToken' => 'PJymUtzBtEKyPassvyvznkZK4hZQNaho2zG2YTz42kUuCruVu/IKoW8xc/yvAJgXlaSb/EC4GE6gV7NbcKeLlp6jvppXqAIVcCnWJjNUzF64UtNSD57iJExsjPTZwD3CDFcSz8tj+iT3kK2ecY0qKA==', 'ia'=>'03', 'ja'=>'04', 'al[0]', $letter),
                    CURLOPT_HTTPHEADER => array()
                ));
                $response = curl_exec($curl); curl_close($curl);

                //Parses the data
                $html_dom->load($response);

                //Processes the data in the table
                $laws = $html_dom->find('ul.search-result')[0]->find('li');
                foreach($laws as $law) {
                    //Gets the values

                    //Makes sure there are no appostophes in the title or origin
                    $name = str_replace("'", "’", $name);
                    $origin = str_replace("'", "’", $origin);
                    $source = str_replace("'", "’", $source);

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
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `origin`, `status`, `source`)
                                    VALUES ('".$vals['Enactment Date']."', '".$vals['Enforcement Date']."', '".$vals['ID']."', '".$name."', '".$type."', '".$origin."', '"."Valid"."', '".$source."')";
                        }
                        
                        //Executes the SQL
                        //echo $row_num.'. '.$SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
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