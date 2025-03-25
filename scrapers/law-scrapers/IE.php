<html><body>
    <?php
        //Settings
        $test = true; $country = 'IE';
        $start = 1922;//Which year to start from
        $limit = null;//Which year to stop at

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

        //Makes sure every number has a certain number of digits
        function zero_buffer ($inputNum, $outputLen=4) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://www.irishstatutebook.ie/eli/'.$page.'/act#');

            //Gets the regime
            switch (true) {
                /*case strtotime("18 June 1542") < strtotime($page) && strtotime($page) <= strtotime("1 January 1801"):
                    $regime = "The Kingdom of Ireland";
                    break;
                case strtotime("1 January 1801") < strtotime($page) && strtotime($page) <= strtotime("6 December 1922"):
                    $regime = "The United Kingdom of Great Britain and Ireland";
                    break;
                case strtotime("6 December 1922") < strtotime($page) && strtotime($page) <= strtotime("29 December 1937"):*/
                case strtotime($page) < strtotime("1937"):
                    //From 6 December 1922 to 29 December 1937
                    $regime = "The Irish Free State";
                    break;
                case strtotime("1937") < strtotime($page) && strtotime($page) < strtotime("1949"):
                    //From 29 December 1937 to 18 April 1949
                    $regime = "Ireland";
                    break;
                case strtotime("1949") < strtotime($page) && strtotime($page) <= strtotime($limit):
                    //From 18 April 1949 to now
                    $regime = "The Republic of Ireland";
                    break;
            }

            //Public Laws
            $publicLaws = $html_dom->find('#public-acts-dtb')[0]->find('tbody')[0]->find('tr');
            foreach ($publicLaws as $publicLaw) {
                //Gets values
                $enactDate = $page."-01-01"; $enforceDate = $page."-01-01"; $lastactDate = $page."-01-01";
                $ID = $country.'-'.$page.zero_buffer($publicLaw->find('td')[0]->innertext);
                $name = trim(str_replace('Act, ', 'Act', explode($page, str_replace($page.'.', $page, $publicLaw->find('td')[1]->find('a')[0]->innertext))[0]));
                $type = 'Public Act'; $status = 'Valid';
                    if (str_contains($name, 'Amendment')) {$type = $type.' Amendment';}
                $source = 'https://www.irishstatutebook.ie/eli/'.$page.'/'.$publicLaw->find('td')[1]->find('a')[0]->href;
                $PDF = str_replace('.html', '.pdf', $source);

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
    
                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$PDF.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                
                if (!$test) {$conn->query($SQL2);}
            }

            //Private Laws
            if (isset($html_dom->find('#private-acts-dtb')[0])) {
                $privateLaws = $html_dom->find('#private-acts-dtb')[0]->find('tbody')[0]->find('tr');
                foreach ($privateLaws as $privateLaw) {
                    //Gets values
                    $enactDate = $page."-01-01"; $enforceDate = $page."-01-01"; $lastactDate = $page."-01-01";
                    $ID = $country.'-'.$page.zero_buffer(explode('No. ', explode('/'.$page.' — ', $privateLaw->find('td')[0]->find('a')[0]->innertext)[0])[1]);
                    $name = trim(str_replace('Act, ', 'Act', explode($page, str_replace($page.'.', $page, explode('/'.$page.' — ', $privateLaw->find('td')[0]->find('a')[0]->innertext)[1]))[0]));
                    $type = 'Private Act';
                        if (str_contains($name, 'Amendment')) {$type = $type.' Amendment';}
                    $status = 'Valid';
                    $source = 'https://www.irishstatutebook.ie/eli/'.$page.'/'.$privateLaw->find('td')[0]->find('a')[0]->href;
                    $PDF = str_replace('.html', '.pdf', $source);

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
        
                    //JSONifies the values
                    $name = '{"en":"'.$name.'"}';
                    $source = '{"en":"'.$source.'"}';
                    $PDF = '{"en":"'.$PDF.'"}';
                    
                    //Inserts the new laws
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                    
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