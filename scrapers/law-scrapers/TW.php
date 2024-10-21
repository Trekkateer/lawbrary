<html><body>
    <?php
        //Settings
        $test = true; $country = 'TW';
        $start = 1;//Which page to start from
        $step = 60;//How many laws there are on each page
        $limit = null;//How many pages there are

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

        //Fixes the date
        $caltrans = function ($date, $cal) {
            if ($cal === 'zh') {
                return ((int)explode('-', $date)[0]+1911).'/'.explode('-', $date)[1].'/'.explode('-', $date)[2];
            } else {return str_replace('-', '/', $date);}
        };

        //Translates types into English
        $types = array('法律'=>'Act',
                        '法規命令'=>'Regulation',
                        '行政規則'=>'Direction',
                        '地方法規'=>'Local Regulation',
                        '法規草案'=>'Draft',
                        'All'=>'Act',
                        'Act'=>'Act',
                        'Regulations'=>'Regulation',
                        'Directions'=>'Direction',
                        'Drafts'=>'Draft');

        //Loops through languages
        foreach (array(''=>'zh'/*, 'ENG/'=>'en'*/) as $locale => $lang) {
            //Gets the limit
            $html_dom = file_get_html('https://law.moj.gov.tw/'.$locale.'News/NewsList.aspx?psize='.$step);
            $limit = $limit ?? explode('&', explode('page=', $html_dom->find('#hlPage')[3]->href)[1])[0];

            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Processes the data
                $html_dom = file_get_html('https://law.moj.gov.tw/'.$locale.'News/NewsList.aspx?page='.$page.'&psize='.$step);
                $laws = $html_dom->find('table.table.table-hover.tab-list.tab-news')[0]->find('tbody')[0]->find('tr');
                foreach ($laws as $law) {
                    //Gets the type
                    $type = $types[trim($law->find('td')[2]->plaintext)];
                    if ($type !== 'Draft' && $type !== 'Local Regulation') {
                        //Gets the rest of the values
                        $enactDate = $caltrans($law->find('td')[1]->plaintext, $lang); $enforceDate = $enactDate;
                        $ID = $country.'-'.explode('&', (explode('id=', strtolower($law->find('td')[3]->find('a')[0]->href))[1] ?? explode('/Law/LawSearch/LawInformation/', $law->find('td')[3]->find('a')[0]->href)[1] ?? explode('/Law/LawSearch/LawInformation?sysNumber=', $law->find('td')[3]->find('a')[0]->href)[1] ?? explode('https://www.stat.gov.tw/News.aspx?n', $law->find('td')[3]->find('a')[0]->href)[1]))[0];
                        $name = trim($law->find('td')[3]->find('a')[0]->plaintext);
                        $source = $law->find('td')[3]->find('a')[0]->href;
                            if (!str_contains($source, 'https://')) {$source = 'https://law.moj.gov.tw/'.$source;}
                        //$PDF = <!--come back to this-->

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
                                $compoundedSource = json_decode($row['source'], true);
                                $compoundedSource[$lang] = $source;
                                $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                                $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                            }
                        } else {
                            //JSONifies the name and href
                            $name = '{"'.$lang.'":"'.$name.'"}';
                            $source = '{"'.$lang.'":"'.$source.'"}';

                            //Creates SQL
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`)
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '"."Valid"."', '".$source."')";
                        }

                        //Executes the SQL
                        echo $SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
                }
            }
        }

        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>