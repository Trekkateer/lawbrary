<html><body>
    <?php //!!Many laws are missing a source page
        //TODO: Find a way to get source pages
        //TODO: Implement ammendment support
        //Settings
        $test = false; $country = 'LK';
        $start = 0;//Which law to start from
        $step = 20;//How many laws are on each page
        $limit = null;//Total number of laws desired

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';
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
        $HTTP_Call = function ($lang, $href) {
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
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: c2c41549d73f81fa91097e85b68c21df=67p0c10si77n5pmiedl0ugq0f7; jfcookie[lang]='.$lang
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return $response;
        };

        //Loops through the languages
        foreach (array('si', 'ta', 'en') as $lang) {
            //Gets the limit
            $html_dom->load($HTTP_Call($lang, 'https://parliament.lk/business-of-parliament/index2.php?option=com_actsandbills&task=acts&tmpl=component&start=0&legis=Please%20select%20a%20Legislature&year=&keyword=&id=undefined&type=0&actno='));
            $limit = $limit ?? explode("'", end($html_dom->find('a.pagenav'))->onclick)[1]; echo $limit.'<br/>';

            //Loops through the pages
            for ($offset = $start; $offset <= $limit; $offset += $step) {
                //Gets values
                $html_dom->load($HTTP_Call($lang, 'https://parliament.lk/business-of-parliament/index2.php?option=com_actsandbills&task=acts&tmpl=component&start='.$offset.'&legis=Please%20select%20a%20Legislature&year=&keyword=&id=undefined&type=0&actno='));
                $laws = $html_dom->find('div.acts_box');
                foreach ($laws as $lawNum => $law) {//echo $law;
                    $enactDate = explode(': ', $law->find('div.nTabber_content')[0]->find('div.con_box')[1]->plaintext)[1]; $enforceDate = $enactDate; $lastActDate = $enactDate;
                    $ID = $country.'-ACT'.strtr(trim(explode(':', $law->find('div.acts_box_top')[0]->find('a.nTabber_plus')[0]->plaintext)[0]), array('/'=>'', 'Chap '=>'CHAP'));
                    //Gets the regime
                    switch (true) {
                        case (strtotime($enactDate) < strtotime('1978-02-04') ):
                            $regime = 'The Democratic Socialist Republic of Sri Lanka';
                        case (strtotime($enactDate) < strtotime('1948-02-04')):
                            $regime = 'British Ceylon';
                        case (strtotime($enactDate) < strtotime('1815-02-04')):
                            $regime = 'The Kingdom of Kandy';
                        default: $regime = 'Sri Lanka';
                            break;
                    }
                    //Gets the rest of the values
                    $name = trim(explode(':', $law->find('div.acts_box_top')[0]->find('a.nTabber_plus')[0]->plaintext)[1]);
                    $type = 'Act';
                        if (str_contains($name, 'සංශෝධන')) {$type = 'Amendment to '.$type;}
                        $conAmmendNum = 0;
                        if (isset($law->find('img[src="https://parliament.lk/images/c-ammend.png"]')[0])) {
                            $conAmmendNum++;
                            $type = 'Constitutional Amendment';
                            $ID = 'LK-CON'.$conAmmendNum.explode('-', $enactDate)[0];
                        }
                    $status = 'Valid';
                    //Gets the source
                    if (isset($law->find('div.nTabber_content')[0]->find('div.con_box')[2]->find('a.act_down')[0])) {
                        $isSource = true;
                        $source = $PDF = '"'.$law->find('div.nTabber_content')[0]->find('div.con_box')[2]->find('a.act_down')[0]->href.'"';
                    } else {
                        $isSource = false;
                        $source = $PDF = null;
                    }

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                    //Creates SQL to check if the law is already stored
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
                            $compoundedSource[$lang] = trim($source, '"');
                            $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                            //JSONifies the PDF
                            $compoundedPDF = json_decode($row['PDF'], true);
                            $compoundedPDF[$lang] = trim($PDF, '"');
                            $PDF = json_encode($compoundedPDF, JSON_UNESCAPED_UNICODE);

                            //Creates SQL
                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":'.$source.'}';
                        $PDF = '{"'.$lang.'":'.$PDF.'}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                    }

                    //Makes the query
                    if ($isSource) {//Only executes if there is a source
                        echo $SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
                }
            }

            //Resets the limit
            $limit = null;
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