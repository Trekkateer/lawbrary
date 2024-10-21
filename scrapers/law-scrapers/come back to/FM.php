<html><body>
    <?php //!!Not all laws have valid dates or links!!
        //Settings
        $test = true; $country = 'FM';
        $start = 10;//Which congress to start from
        $limit = 23;//Which congress to end at

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

        //Figures out ordinals
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        $ordinalize = function ($congress) use ($ends) {
            if ($congress%100 >= 11 && $congress%100 <= 13) {return $congress.'th';}
            else {return $congress.$ends[$congress%10];}
        };

        //Formats the months
        $months = array('JANUARY'=>'-01-','FEBRUARY'=>'-02-','MARCH'=>'-03-','APRIL'=>'-04-','MAY'=>'-05-','JUNE'=>'-06-','JULY'=>'-07-','AUGUST'=>'-08-','SEPTEMBER'=>'-09-','OCTOBER'=>'-10-','NOVEMBER'=>'-11-','DECEMBER'=>'-12-');
        
        //Gets the type
        $types = array('PL'=>'Public Law');
        
        //Loops through the congresses
        for ($congress = $start; $congress <= $limit; $congress++) {echo '<br/>'.$congress.'. <br/>';
            //Processes the data
            $html_dom = file_get_html('https://www.cfsm.gov.fm/'.$ordinalize($congress).'-public-laws/');
            $laws = $html_dom->find('table[id^="tablepress"]')[0]->find('tbody.row-hover')[0]->find('tr');
            foreach($laws as $law) {
                //Gets values
                $enactDate = trim($law->find('td.column-2')[0]->plaintext);
                    $enactDate = preg_replace('/[A-Za-z]/', '', $enactDate) === $enactDate ? date('Y-m-d', strtotime(explode(' - ', str_replace(' ', '', $enactDate))[1])):explode(', ', $enactDate)[1].$months[explode(' ', $enactDate)[0]].explode(', ', explode(' - ', $enactDate)[1])[0];
                    $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.strtr($law->find('td.column-1')[0]->plaintext, array(' '=>'', '-'=>''));
                $regime = 'The Federated States of Micronesia';
                $name = trim($law->find('td.column-3')[0]->plaintext);
                $type = $types[explode(' ', $law->find('td.column-1')[0]->plaintext)[0]];
                    if (str_contains(strtoupper($name), 'AMENDMENT') || str_contains(strtoupper($name), 'AMEND')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $source = $law->find('td.column-1')[0]->find('a')[0]->href; $pdf = $source;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $pdf = '{"en":"'.$pdf.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `pdf`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
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