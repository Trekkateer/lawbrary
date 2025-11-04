<?php //Japan
    //!!Enactment dates are not immediately available!! 
    //!!The website is not working!!

    //Settings
    $test = true; $scraper = 'JP';
    $start = 0;//Where to start from

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $html_dom = new simple_html_dom();

    //Opens my scraper
    include '../skrapateer';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`";
    if (!$test) {$conn->query($SQL1);}

    //Defines the months
    $months = array('1'=>'jan','2'=>'feb','3'=>'mar','4'=>'apr','5'=>'may','6'=>'jun','7'=>'jul','8'=>'aug','9'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec');

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["JP"]';
    $publisher = '{"en":"Japanese Law Translation"}';

    //Loops through the languages
    foreach(array('jp'=>['あ','い','う','え','お','か','き','く','け','こ','さ','し','す','せ','そ','た','ち','つ','て','と','な','に','ね','の','は','ひ','ふ','へ','ほ','ま','み','む','め','も','や','ゆ','よ','り','れ','ろ']/*, 'en'=>array['A','B','C','D','E','F','G','H','I','J','L','M','N','O','P','Q','R','S','T','U','V','W']*/) as $lang=>$alphabet) {
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
                CURLOPT_POSTFIELDS => array(
                    '_csrfToken' => 'PJymUtzBtEKyPassvyvznkZK4hZQNaho2zG2YTz42kUuCruVu/IKoW8xc/yvAJgXlaSb/EC4GE6gV7NbcKeLlp6jvppXqAIVcCnWJjNUzF64UtNSD57iJExsjPTZwD3CDFcSz8tj+iT3kK2ecY0qKA==',
                    'ia'=>'03', 'ja'=>'04', 'al[0]', $letter),
                CURLOPT_HTTPHEADER => array()
            ));
            $response = curl_exec($curl); curl_close($curl);

            //Parses the data
            $html_dom->load($response);

            //Processes the data in the table
            $laws = $html_dom->find('ul.search-result', 0)->find('li');
            foreach($laws as $law) {
                //Sets the values
                $status = 'Valid';

                //Makes sure there are no appostophes in the title or origin
                $name   = fixQuotes($name, $lang);
                $origin = fixQuotes($origin, $lang);
                $source = fixQuotes($source, $lang);

                if ($vals['Revision'] === '제정' || $vals['Revision'] === 'New Enactment') {
                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$vals['ID']."'";
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

                            $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `type`='".$type."', `origin`='".$origin."' WHERE `ID`='".$vals['ID']."'";
                        }
                    } else {
                        //JSONifies the values
                        $name = '{"'.$lang.'":"'.$vals['Title'].'"}';
                        $source = '{"'.$lang.'":"'.$vals['Source'].'"}';
                        $origin = '{"'.$lang.'":"'.$vals['Origin'].'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `origin`, `status`, `source`)
                                VALUES ('".$vals['Enactment Date']."', '".$vals['Enforcement Date']."', '".$lastactDate."', '".$saveDate."', '".$vals['ID']."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$origin."', '".$status."', '".$source."')";
                    }
                    
                    //Executes the SQL
                    echo $row_num.'. '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
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