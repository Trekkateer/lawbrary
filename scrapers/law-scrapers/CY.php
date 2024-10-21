<html><body>
    <?php
        //Settings
        $test = true; $country = 'CY';
        $start = 1878;//Which year to start from
        $limit = null;//Which year to end at

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
        function zero_buffer ($inputNum, $outputLen=3) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        }

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Excludes certain years
            if ($page === 1902 || $page === 1903 || $page === 1916) {continue;}

            //Processes the data
            $html_dom = file_get_html('http://www.cylaw.org/nomoi/'.$page.'_arith_index.html');
            $laws = $html_dom->find('li');
            foreach($laws as $law) {
                //Gets the source
                $source = isset($law->find('a')[0]->href) ? 'https://www.cylaw.org'.$law->find('a')[0]->href:NULL; $PDF = $source;
                if ($source) {
                    //Gets the language
                    if (preg_replace('/[\s\p{P}\p{N}\p{Latin}]+/', '', explode(' - ', $law->plaintext)[1]) === '') {$lang = 'en';} else {$lang = 'el';}

                    //Gets values
                    $enactDate = $page.'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                    $ID = $country.'-'.zero_buffer(explode('/', explode('Ν. ', explode(' - ', $law->plaintext)[0])[1])[0]).explode('/', explode('Ν. ', explode(' - ', $law->plaintext)[0])[1])[1];
                    //Gets the regime
                    switch(true) {
                        case (strtotime('12 July 1878') < strtotime($enactDate) || $enactDate === '1878-01-01') && strtotime($enactDate) < strtotime('5 November 1914'):
                            $regime = 'The British Protectorate of Cyprus';
                            break;
                        case strtotime('5 November 1914') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 May 1925'):
                            $regime = 'British Occupied Cyprus';
                            break;
                        case strtotime('1 May 1925') < strtotime($enactDate) && strtotime($enactDate) < strtotime('16 August 1960'):
                            $regime = 'The Crown Colony of Cyprus';
                            break;
                        case strtotime('16 August 1960') <strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('Y')):
                            $regime = 'The Republic of Cyprus';
                            break;
                    }
                    //Gets the rest of the values
                    $name = trim(strtr($law->plaintext, array('[pdf]'=>'', 'w,1'=>'w, 1')));
                    $type = 'Law'; $status = 'Valid';

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                    //JSONifies the values
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = '{"'.$lang.'":"'.$PDF.'"}';
                    
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