<html><body>
    <?php
        //Settings
        $test = true; $country = 'NR';
        $start = 0;//Which page to start from
        $step = 20;//How many laws are on each page
        $limit = null;//How many pages there are

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

        //Gets the limit
        $html_dom = file_get_html('http://ronlaw.gov.nr/nauru_lpms/index.php/act/browse/0/letter/any/year/any/filter/any');
        $limit = $limit ?? explode(' of ', $html_dom->find('div.rounded-corners')[0]->find('div.content-area')[0]->find('em')[0]->plaintext)[1];
        //Loops through the pages
        for ($offset = $start; $offset <= $limit; $offset+=$step) {
            //Gets the HTML
            $html_dom = file_get_html('http://ronlaw.gov.nr/nauru_lpms/index.php/act/browse/'.$offset.'/letter/any/year/any/filter/any');// echo $html_dom;

            //Processes the data in the table
            $laws = $html_dom->find('ul.results')[0]->find('li');
            foreach ($laws as $law) {
                //Gets values
                $name = strtr(trim($law->find('a')[0]->plaintext), array(' - '=>'-'));
                $enactYear = end(explode(' ', str_replace('-', ' ', $name)));
                    $enactDate = $enactYear.'-01-01'; $enforceDate = $enactDate; $lastActDate = $enactDate;
                $ID = $country.'-'.explode('/', $law->find('a')[0]->href)[7];
                //Gets the regime
                switch(true) {
                    case strtotime('2 October 1888') < strtotime($enactDate) && strtotime($enactDate) < strtotime('9 September 1914'):
                        $regime = 'The German Empire';
                        break;
                    case strtotime('9 September 1914') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 September 1923'):
                        $regime = 'The British Empire';
                        break;
                    case strtotime('29 September 1923') < strtotime($enactDate) && strtotime($enactDate) < strtotime('26 August 1942'):
                        $regime = 'The Commonwealth of Australia';
                        break;
                    case strtotime('26 August 1942') < strtotime($enactDate) && strtotime($enactDate) < strtotime('13 September 1945'):
                        $regime = 'The Empire of Japan';
                        break;
                    case strtotime('13 September 1945') < strtotime($enactDate) && strtotime($enactDate) < strtotime('31 January 1968'):
                        $regime = 'The Commonwealth of Australia';
                        break;
                    case strtotime('31 January 1968') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                        $regime = 'The Republic of Nauru';
                        break;
                }
                //Gets the rest of the values
                $type = end(explode(' ', explode(' (No. ', explode(' '.$enactYear, trim(preg_replace('/[0-9-]/', '', $name)))[0])[0]));
                    if (str_contains($name, 'Amendment')) {$type = 'Amendment to '.$type;}
                if (str_contains($law->plaintext, 'repealed')) {$status = 'Repealed';} else {$status = 'Valid';}
                $source = $law->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                $name = str_replace("'", "’", $name);

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `name`, `regime`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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
    ?>
</body></html>