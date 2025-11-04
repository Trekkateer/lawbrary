<?php //Cyprus
    //Settings
    $test = false; $scraper = 'CY';
    $start = 1878;//Which year to start from
    $limit = null;//Which year to end at

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static var
    $saveDate = date('Y-m-d'); $country = '["CY"]'; $type = 'Act'; $status = 'Valid';
    $publisher = '{"el":"Παγκύπριος Δικηγορικός Σύλλογος", "en":"The Cyprus Bar Association", "tk":"Kıbrıs Barosu"}';

    //Gets the limit
    $limit = $limit ?? Date('Y');
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Excludes certain years
        if ($page === 1902 || $page === 1903 || $page === 1916) {continue;}

        //Processes the data
        $dom = file_get_html('http://www.cylaw.org/nomoi/'.$page.'_arith_index.html');
        $laws = $dom->find('li > p > a');//Makes sure that the list is in fact a law. Excludes laws that have no sources
        foreach($laws as $law) {
                //Gets the language
                if (preg_replace('/[\s\p{P}\p{N}\p{Latin}]+/', '', explode(' - ', $law->plaintext)[1]) === '') {$lang = 'en';} else {$lang = 'el';}

                //Gets values
                $enactDate = $enforceDate = $lastactDate = $page.'-01-01';
                $ID = $scraper.':'.str_pad(explode('/', explode('Ν. ', explode(' - ', $law->plaintext)[0])[1])[0], 3, '0', STR_PAD_LEFT).explode('/', explode('Ν. ', explode(' - ', $law->plaintext)[0])[1])[1];
                $name = fixQuotes(trim(str_replace(['[pdf]', 'w,1'], ['', 'w, 1'], $law->plaintext)), $lang);
                switch(true) {
                    case (strtotime('12 July 1878') < strtotime($enactDate) || $enactDate === '1878-01-01') && strtotime($enactDate) < strtotime('5 November 1914'):
                        $regime = '{"en":"The British Protectorate of Cyprus", "el":"Το Βρετανικό Προτεκτοράτο της Κύπρου", "tk":"Kıbrıs ꞌın İngiliz Himayesi"}';
                        break;
                    case strtotime('5 November 1914') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 May 1925'):
                        $regime = '{"en":"British Occupied Cyprus", "el":"Βρετανική κατεχόμενη Κύπρος", "tk":"İngiliz İşgali Altındaki Kıbrıs"}';
                        break;
                    case strtotime('1 May 1925') < strtotime($enactDate) && strtotime($enactDate) < strtotime('16 August 1960'):
                        $regime = '{"en":"The Crown Colony of Cyprus", "el":"Η Αποικία του Στέμματος της Κύπρου", "tk":"Kıbrıs Taç Kolonisi"}';
                        break;
                    case strtotime('16 August 1960') <strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('Y')):
                        $regime = '{"en":"The Republic of Cyprus", "el":"Κυπριακή Δημοκρατία", "tk":"Kıbrıs Cumhuriyeti"}';
                        break;
                }
                $source = 'https://www.cylaw.org'.$law->href;

                //JSONifies the values
                $name = '{"'.$lang.'":"'.$name.'"}';
                $source = '{"'.$lang.'":"'.$source.'"}';

                //Gives an error if the name or source is not valid
                if (!json_decode($name)) echo "<br/>Warning: \$name is not valid JSON<br/>";
                if (!json_decode($source)) echo "<br/>Warning: \$source is not valid JSON<br/>";
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$source."')";
                echo '<a href="http://www.cylaw.org/nomoi/'.$page.'_arith_index.html" target="_blank">p. '.$page.':</a> '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
        }
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