<?php //Sri Lanka

    //!!Many laws are missing a source page!!
    //TODO: Find a way to get source pages
    //TODO: Implement ammendment support

    //Settings
    $test = false; $scraper = 'LK';
    $start = 0;//Which law to start from
    $step = 20;//How many laws are on each page
    $limit = null;//Total number of laws desired

    // Suppress warnings only
    error_reporting(E_ALL & ~E_WARNING);

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $HTTP_Call = function ($lang, $offset=0) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://parliament.lk/business-of-parliament/index2.php?option=com_actsandbills&task=acts&tmpl=component&start='.$offset.'&legis=Please%20select%20a%20Legislature&year=&keyword=&id=undefined&type=0&actno=',
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
        return new simple_html_dom($response);
    };

    //Sets the Static Variables
    $saveDate = date('Y-m-d'); $country = '["LK"]';
    $publisher = '{"si":"ශ්‍රී ලංකා පාර්ලිමේන්තුව","ta":"இலங்கை பாராளுமன்றம்","en":"The Parliament of Sri Lanka"}';
    $conAmmendNum = 0;

    //Loops through the languages
    foreach (array('si', 'ta', 'en') as $lang) {
        //Gets the limit
        $limit = $limit ?? explode("'", end($HTTP_Call($lang)->find('a.pagenav'))->onclick)[1]; echo $limit.'<br/>';

        //Loops through the pages
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets values
            $laws = $HTTP_Call($lang, $offset)->find('div.acts_box');
            foreach ($laws as $lawNum => $law) {
                $enactDate = $enforceDate = $lastActDate = explode(': ', $law->find('div.nTabber_content', 0)->find('div.con_box', 1)->plaintext)[1];
                $ID = $scraper.':ACT'.strtr(trim(explode(':', $law->find('div.acts_box_top', 0)->find('a.nTabber_plus', 0)->plaintext)[0]), array('/'=>'', 'Chap '=>'CHAP'));
                //Gets the regime
                switch (true) {
                    case (strtotime($enactDate) < strtotime('1978-02-04') ):
                        $regime = '{"en":"The Democratic Socialist Republic of Sri Lanka"}';
                    case (strtotime($enactDate) < strtotime('1948-02-04')):
                        $regime = '{"en":"British Ceylon"}';
                    case (strtotime($enactDate) < strtotime('1815-02-04')):
                        $regime = '{"en":"The Kingdom of Kandy"}';
                    default: $regime = '{"en":"Sri Lanka"}';
                        break;
                }
                //Gets the rest of the values
                $name = trim(explode(':', $law->find('div.acts_box_top', 0)->find('a.nTabber_plus', 0)->plaintext)[1]);
                $type = 'Act';
                    //$isAmend = str_contains($name, 'සංශෝධන') || str_contains($name, 'திருத்தம்') || str_contains($name, 'Amendment') ? 1:0;
                    if ($law->find('img[src="https://parliament.lk/images/c-ammend.png"]', 0) !== null) {
                        $conAmmendNum++;
                        $type = 'Constitutional Amendment';
                        $ID = 'LK:CON'.$conAmmendNum.explode('-', $enactDate)[0];
                    }
                $status = 'Valid';
                //Gets the source
                if ($law->find('div.nTabber_content', 0)->find('div.con_box', 2)->find('a.act_down', 0) !== null) {
                    $source = $PDF = '"'.$law->find('div.nTabber_content', 0)->find('div.con_box', 2)->find('a.act_down', 0)->href.'"';
                } else {
                    $source = $PDF = null;
                }

                //Makes sure there are no quotes in the title
                $name = fixQuotes($name, $lang);

                //Creates SQL to check if the law is already stored
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

                        //Creates SQL
                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."', `PDF`='".$PDF."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":'.$source.'}';
                    $PDF = '{"'.$lang.'":'.$PDF.'}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastActDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                }

                //Makes the query
                if (isset($source)) {//Only executes if there is a source
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }

        //Resets the limit
        $limit = null;
    }

    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>