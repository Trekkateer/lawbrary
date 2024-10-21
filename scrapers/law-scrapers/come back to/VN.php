<html><body>
    <?php //!! Exceeds the Xampp time limit for executions
        //Settings
        $test = true; $country = 'VN';
        $start = 1;//Which page to start from
        $step = 50;//How many laws there are on each page
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

        //Does the Vietnamese
        $lang = 'vi';
        //Gets the limit
        $html_dom = file_get_html('https://luatvietnam.vn/van-ban-luat-viet-nam.html?pSize='.$step);
        $limit = $limit ?? $html_dom->find('div.pag-right')[0]->find('span[class="page-numbers"] + a.page-numbers')[0]->plaintext;

        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://luatvietnam.vn/van-ban-luat-viet-nam.html?pSize='.$step.'&page='.$page);
            $laws = $html_dom->find('div.block-content')[0]->find('article.doc-article');
            foreach ($laws as $law) {
                //Gets the values
                $enactDate = date('Y-m-d', strtotime(str_replace('/', '-', $law->find('div.doc-clumn2')[0]->find('div.post-meta-doc')[0]->find('div.doc-dmy')[0]->find('span.w-doc-dmy2')[0]->plaintext))); $enforceDate = $enactDate;
                $ID = $country.'-'.$law->find('div.doc-clumn2')[0]->find('div.post-type-doc')[0]->find('div.doc-meta')[0]->find('a[class="doc-tag save-doc"]')[0]->{'data-id'};
                $name = trim($law->find('div.doc-clumn2')[0]->find('div.post-doc')[0]->find('div.post-type-doc')[0]->find('a')[0]->plaintext);
                $type = 'Law'; $status = 'Valid';
                $source = 'https://luatvietnam.vn'.$law->find('div.doc-clumn2')[0]->find('div.post-type-doc')[0]->find('a')[0]->href;
                //$PDF = <!--come back to this-->

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the name and href
                $name = '{"'.$lang.'":"'.$name.'"}';
                $source = '{"'.$lang.'":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`)
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')";

                //Executes the SQL
                echo $law->find('div.doc-clumn1')[0]->find('span.doc-number')[0]->plaintext.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Does the English
        $lang = 'en';
        //Gets the limit
        $html_dom = file_get_html('https://english.luatvietnam.vn/official-gazette.html?pSize='.$step);
        $limit = $limit ?? explode('of ', $html_dom->find('div.pag-right')[0]->find('span.pag-text')[0]->plaintext)[1];

        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://english.luatvietnam.vn/official-gazette.html?pSize='.$step.'&page='.$page);
            $laws = $html_dom->find('#legal-normative-documents')[0]->find('article.article-document');
            foreach($laws as $law) {
                //Gets the values
                $enactDate = date('Y-m-d', strtotime(str_replace('/', '-', $law->find('div.col2-document')[0]->find('div.date-row')[0]->find('div.date-col2')[0]->plaintext))); $enforceDate = $enactDate;
                $ID = $country.'-'.substr($law->find('div.col1-document')[0]->find('div.post-document')[0]->find('div.tag-document')[0]->find('a[class="tag-item save-doc"]')[0]->{'data-id'}, 4);
                $name = trim($law->find('div.col1-document')[0]->find('div.post-document')[0]->find('a')[0]->plaintext);
                $type = 'Law'; $status = 'Valid';
                $source = 'https://luatvietnam.vn'.$law->find('div.col1-document')[0]->find('div.post-document')[0]->find('a')[0]->href;
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
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')";
                }

                //Executes the SQL
                echo $law->find('div.doc-clumn1')[0]->find('span.doc-number')[0]->plaintext.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
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