<html><body>
    <?php
        //Settings
        $test = true; $country = 'CM';
        $start = 0;//Which page to start from
        $step = 8;//How many laws are on each page
        $limit = null;//Which law to stop at

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Creates a function that parses French
        function strtotimeFR($date_string) {
            return strtotime(strtr(strtolower($date_string), array('janvier'=>'january','fevrier'=>'february','mars'=>'march','avril'=>'april','mai'=>'may','juin'=>'june','juillet'=>'july','jully'=>'july','aout'=>'august','septembre'=>'september','octobre'=>'october','novembre'=>'november','decembre'=>'december')));
        }

        //Loops through languages
        foreach (array('fr'=>'fr/actualites/actes/lois', 'en'=>'en/news/the-acts/laws') as $lang=>$locale) {
            //Checks each page
            $limit = $limit ?? explode('start=', end(file_get_html('https://prc.cm/'.$locale)->find('ul.pagination-list')[0]->find('li'))->find('a.pagenav')[0]->href)[1]; echo $limit;
            for ($offset = $start; $offset <= $limit; $offset += $step) {
                //Gets the HTML
                $html_dom = file_get_html('https://prc.cm/'.$locale.'?start='.$offset);

                //Processes the data in the table
                $body_rows = $html_dom->find('h4 > a');
                foreach ($body_rows as $rowNum => $body_row) {
                    //Gets Values
                    $path = explode('/', $body_row->href)[5];
                        $path = strtr($path, array('-title-loi-'=>'-loi-', 'du20'=>'du-20', 'loi-20'=>'loi-n-20', '-019-of-dec'=>'-019-of-19-dec', '-2017-020-20-'=>'-2017-020-of-20-', '-to-amend-and-supplement-some-provisions-of-law-no-2013-4-of-'=>'-of-4-',
                                                   '-https-www-prc-cm-en-multimedia-documents-6813-'=>'-', '-to-authorize-the-president-of-the-republic-to-ratify-the-united-nations-convention-on-transparency-in-treaty-based-investor-state-arbitration-adopted-on-'=>'-',));
                    $enactDate = date('Y-m-d', strtotimeFR(explode('-', $path)[6].'-'.explode('-', $path)[7].'-'.explode('-', $path)[8])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                    $ID = $country.'-'.explode('-', $path)[4].explode('-', $path)[3];
                        $ID = strtr($ID, array('00222023'=>'0022023', '132021'=>'0132021'));

                    //Gets Regime
                    switch(true) {
                        case strtotime($enactDate) < strtotime('1 January 1960'):
                            $regime = 'The Republic of France';
                            break;
                        case strtotime('1 January 1960') < strtotime($enactDate):
                            $regime = 'The republic of Cameroon';
                            break;
                    }

                    //Gets the rest of the values
                    $name = trim($body_row->innertext);
                    $type = 'Law'; $status = 'Valid';
                    $source = 'https://prc.cm'.$body_row->href;

                    //Makes sure there are no appostophes in the title
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
                            $compoundedHREF = json_decode($row['source'], true);
                            $compoundedHREF[$lang] = $source;
                            $source = json_encode($compoundedHREF, JSON_UNESCAPED_UNICODE);

                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."')";
                    }

                    //Executes the SQL
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