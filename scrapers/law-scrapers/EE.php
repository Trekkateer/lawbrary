<html><body>
    <?php
        //Settings
        $test = true; $country = 'EE';
        $startPage = 0; $pageLimit = 220;

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Loops through English and French
        foreach (array('tervikteksti_tulemused.html'=>'et'/*, 'en/'=>'en'*/) as $locale => $lang) {
            for ($page = $startPage; $page <= $pageLimit; $page++) {
                //Gets the HTML
                $html_dom = file_get_html('https://www.riigiteataja.ee/'.$locale.'?leht='.$page.'&kuvaKoik=false&sorteeri=kehtivuseAlgus&kasvav=true'); //echo $html_dom;

                //Processes the data in the table
                $body_rows = $html_dom->find('table > tbody', 0)->find('tr');
                foreach ($body_rows as $body_row) {
                    //Gets values
                    $name = $body_row->find('td', 0)->find('a', 0)->innertext;
                    $source = $body_row->find('td', 0)->find('a', 0)->href;
                    $ID = $country.'-'.explode('/', $source)[4];
                    $type = strtr($body_row->find('td', 2)->innertext, array('määrus'=>'Regulation', 'Seadus'=>'Law', 'korraldus'=>'Order'));
                    $origin = $body_row->find('td', 1)->innertext;
                    $enactDate = date('Y/m/d', strtotime(trim(explode('-', $body_row->find('td', 4)->innertext)[0]))); $enforceDate = $enactDate;
                    $endDate = strtotime(trim(explode('-', $body_row->find('td', 4)->innertext)[1])) ? "'".date('Y/m/d', strtotime(trim(explode('-', $body_row->find('td', 4)->innertext)[1])))."'":"NULL";
                    $status = 'Valid';

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                    if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                    //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

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
                            
                            //JSONifies the origin
                            $compoundedOrigin = json_decode($row['origin'], true);
                            $compoundedOrigin[$lang] = $origin;
                            $origin = json_encode($compoundedOrigin, JSON_UNESCAPED_UNICODE);

                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."', `origin`='".$origin."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name, source and origin
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';
                        $origin = '{"'.$lang.'":"'.$origin.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `endDate`, `ID`, `name`, `type`, `origin`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', ".$endDate.", '".$ID."', '".$name."', '".$type."', '".$origin."', '".$status."', '".$source."')";
                    }

                    //Executes the SQL
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
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
        }
    ?>
</body></html>