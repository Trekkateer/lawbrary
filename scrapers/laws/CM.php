<?php //Cameroon
    //Settings
    $test = false; $scraper = 'CM';
    $start = 0;//Which page to start from
    $step = 8;//How many laws are on each page
    $limit = null;//Which law to stop at

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

    //Creates a function that parses French
    function strtotimeFR($date_string) {
        return strtotime(strtr(strtolower($date_string), array('janvier'=>'january','fevrier'=>'february','mars'=>'march','avril'=>'april','mai'=>'may','juin'=>'june','juillet'=>'july','jully'=>'july','aout'=>'august','septembre'=>'september','octobre'=>'october','novembre'=>'november','decembre'=>'december')));
    }

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["CM"]'; $type = 'Law'; $status = 'Valid';
    $publisher = '{"fr":"Présidence de la République du Cameroun","en":"The Presidency of the Republic of Cameroon"}';

    //Loops through languages
    foreach (array('fr'=>'fr/actualites/actes/lois', 'en'=>'en/news/the-acts/laws') as $lang=>$locale) {
        //Checks each page
        $limit = $limit ?? explode('start=', end(file_get_html('https://prc.cm/'.$locale)->find('ul.pagination-list', 0)->find('li'))->find('a.pagenav', 0)->href)[1];
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the HTML
            $dom = file_get_html('https://prc.cm/'.$locale.'?start='.$offset);

            //Processes the data in the table
            $docs = $dom->find('h4 > a');
            foreach ($docs as $rowNum => $doc) {
                //Gets Values
                $path = strtr(explode('/', $doc->href)[5], array('-title-loi-'=>'-loi-', 'du20'=>'du-20', 'loi-20'=>'loi-n-20', '-019-of-dec'=>'-019-of-19-dec', '-2017-020-20-'=>'-2017-020-of-20-', '-to-amend-and-supplement-some-provisions-of-law-no-2013-4-of-'=>'-of-4-',
                                            '-https-www-prc-cm-en-multimedia-documents-6813-'=>'-', '-to-authorize-the-president-of-the-republic-to-ratify-the-united-nations-convention-on-transparency-in-treaty-based-investor-state-arbitration-adopted-on-'=>'-',));
                $enactDate = date('Y-m-d', strtotimeFR(explode('-', $path)[6].'-'.explode('-', $path)[7].'-'.explode('-', $path)[8])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $scraper.':'.explode('-', $path)[4].explode('-', $path)[3];
                    $ID = strtr($ID, array('00222023'=>'0022023', '132021'=>'0132021'));
                //Gets Regime
                switch(true) {
                    case strtotime($enactDate) < strtotime('1 January 1960'):
                        $regime = '{"fr":"République française", "en":"The Republic of France"}';
                        break;
                    case strtotime('1 January 1960') < strtotime($enactDate):
                        $regime = '{"fr":"République du Cameroun", "en":"The republic of Cameroon"}';
                        break;
                }
                //Gets the rest of the values
                $name = fixQuotes(trim($doc->innertext), $lang);
                $source = 'https://prc.cm'.$doc->href;

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

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
                        $compoundedHREF = json_decode($row['source'], true);
                        $compoundedHREF[$lang] = $source;
                        $source = json_encode($compoundedHREF, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')";
                }

                //Executes the SQL
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
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