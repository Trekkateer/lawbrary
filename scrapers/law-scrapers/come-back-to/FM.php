<html><body>
    <?php //!!Not all laws have valid dates or links!!
        //Settings
        $test = true; $scraper = 'FM';
        $start = 10;//Which congress to start from. There are no records before the 10th congress
        $limit = 23;//Which congress to end at

        //Opens the parser (SIMPLE_HTML_DOM)
        include '../../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Connects to the content database
        $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/>';
        if (!$test) {$conn->query($SQL1);}
        $SQL10 = "SELECT `ID` FROM `dbupm726ysc0bg`.`divisions` WHERE `parent` = '".$scraper."'";
        $result10 = $conn2->query($SQL10);
        while ($row10 = $result10->fetch_assoc()) {
            $SQL11 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($row10['ID'])."`"; echo $SQL11.'<br/>';
            if (!$test) {$conn->query($SQL11);}
        }
        echo '<br/>';

        //Figures out ordinals
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        $ordinalize = function ($congress) use ($ends) {
            if ($congress%100 == 10 || $congress%100 == 11 || $congress%100 == 13) {return $congress.'th';}
            else if ($congress%100 == 12) {return $congress.'th-cfsm';}
            else {return $congress.$ends[$congress%10].'-cfsm';}
        };

        //Formats the months
        $months = array('JANUARY'=>'01','FEBRUARY'=>'02','MARCH'=>'03','APRIL'=>'04','MAY'=>'05','JUNE'=>'06','JULY'=>'07','AUGUST'=>'08','SEPTEMBER'=>'09','OCTOBER'=>'10','NOVEMBER'=>'11','DECEMBER'=>'12');
        
        //Loops through each type of law
        foreach (array('Public Law'=>10,'Resolution'=>20,'Bill'=>15) as $type => $start) {
            //Loops through the congresses
            for ($congress = $start; $congress <= $limit; $congress++) {
                //Processes the data
                $html_dom = file_get_html('https://www.cfsm.gov.fm/'.$ordinalize($congress).'-'.strtr(strtolower($type), [' '=>'-']).'s/');
                $laws = $html_dom->find('table[id^="tablepress"]')[0]->find('tbody.row-hover')[0]->find('tr');
                foreach($laws as $lawNum => $law) {
                    //Gets values
                    echo '<br/><span>'.$law->find('td.column-2')[0]->plaintext.'</span><br/>';
                    //Gets the dates
                    $enactDate = trim(strtoupper(strtr($law->find('td.column-2')[0]->plaintext, [' '=>'', '..'=>'.'])));
                    if (in_array(explode(' ', $enactDate)[0], array_keys($months))) {
                        if (str_contains($enactDate, ' TO ')) {
                            $enactDate = preg_replace(['/((?<!\d)\d),/', '/(\d{2}) (\d{1,2}), (\d{4})/'], ['0$1,', '$3-$1-$2'], explode(' TO ', str_replace(array_keys($months), $months, $enactDate))[1]);
                        } else $enactDate = preg_replace('/(\d{2}) (\d{1,2}) \- (\d{1,2}), (\d{4})/', '$4-$1-$3', str_replace(array_keys($months), $months, $enactDate));
                    } else if (str_contains($enactDate, '-')) {
                        $enactDate = preg_replace(['/((?<!\d)\d)\//','/(\d{2})\/(\d{2})\/(\d{4})/','/(\d{2})\/(\d{2})\/(\d{2})/'], ['0$1/', '$3-$1-$2', (((int)'$3')>date('y') ? '19':'20').'$3-$2-$1'], trim(explode('-', $enactDate)[1]));
                    } else $enactDate = preg_replace(['/((?<!\d)\d)\./', '/(\d{2})\.(\d{2})\.(\d{4})/', '/(\d{2})\.(\d{2})\.(\d{2})/'], ['0$1.', '$3-$1-$2', (((int)'$3')>date('y') ? '19':'20').'$3-$1-$2'], $enactDate);
                    $enforceDate = $lastactDate = $enactDate;
                    //Gets the other values
                    $ID = $scraper.':'.strtr($law->find('td.column-1')[0]->plaintext, [' '=>'', '.'=>'']);
                    $country = '["FM"]';
                    $regime = '{"en":"The Federated States of Micronesia"}';
                    $name = trim($law->find('td.column-3')[0]->plaintext);
                    //TODO: Figure out how to tell if the law is an amendment and what it amends
                    $status = 'Valid';
                    $source = $law->find('td.column-1')[0]->find('a')[0]->href; $PDF = $source;

                    //Makes sure there are no quotes in the title
                    $name = strtr($name, array("'"=>'’'));

                    //JSONifies the values
                    $name = '{"en":"'.$name.'"}';
                    $source = '{"en":"'.$source.'"}';
                    $PDF = '{"en":"'.$PDF.'"}';
                    
                    //Inserts the new laws
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enforceDate)) {echo 'Date is not valid!!<br/>';}
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `country`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".date('Y-m-d')."', '".$ID."', '".$country."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                    echo 'c'.$congress.'p'.ceil($lawNum/10).': '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }
        
        //Connects to the content database
        $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>