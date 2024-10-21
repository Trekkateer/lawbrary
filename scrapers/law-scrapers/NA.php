<html><body>
    <?php        
        //Settings
        $test = true; $country = 'NA';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; //'../' refers to the parent directory

        //Sanitizes the statuses
        $statuses = array(
            ''=>'Valid',
            'Not yet in force'=>'Not in Force',
            'Not yet in Force.'=>'Not in Force',
            'Not yet in force.'=>'Not in Force',
            'Never brought into force in RSA or SWA.'=>'Not in Force',
            'Status uncertain.'=>'Uncertain',
        );

        //Gets data
        $html_dom = file_get_html('https://www.lac.org.na/index.php/laws/statutes/');
        $laws = $html_dom->find('table')[0]->find('tbody')[0]->find('tr');
        for ($i = 1; $i < count($laws); $i++) {$law = $laws[$i];
            //Gets the values
            $name_dom = new simple_html_dom(explode('<a href="', $law->find('td')[0]->innertext)[0]); $name = explode('Entitles holder of bank note', explode('Repealed', $name_dom->plaintext)[0])[0];
                $name = trim(strtr($name, array('0f'=>'of', ', AG'=>'', ', 18'=>' 1 of 18', '(Britain)'=>'', '(RSA)'=>'', '(SA)'=>'', '- UPDATED'=>'')));
            $enactDate = trim(end(explode(' of ', $name))).'-01-01'; $enforceDate = $enactDate; $lastActDate = $enactDate;
            $ID = $country.'-'.trim(end(explode(' of ', $name))).end(explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0]));
            //Gets the regime
            switch (true) {
                case strtotime('1884') < strtotime($enactDate) && strtotime($enactDate) < strtotime('17 December 1920'):
                    $regime = 'The German Empire';
                    break;
                case strtotime('17 December 1920') < strtotime($enactDate) && strtotime($enactDate) < strtotime('31 May 1961'):
                    $regime = 'The Union of South Africa';
                    break;
                case strtotime('31 May 1961') < strtotime($enactDate) && strtotime($enactDate) < strtotime('21 March 1990'):
                    $regime = 'The Republic of South Africa';
                    break;
                case strtotime('21 March 1990') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                    $regime = 'The Republic of Namibia';
                    break;
            }
            //Gets the rest of the values
            $type = explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0])[count(explode(' ', explode(' of '.trim(end(explode(' of ', $name))), $name)[0]))-2];
            $topic = ucfirst(strtolower($law->find('td')[2]->plaintext));
            $status = $statuses[trim($law->find('td')[4]->plaintext)] ?? 'Valid';//TODO: Sanitize more statuses
            $source = $law->find('td', 1)->find('a', 1)->href;

            //Makes sure there are no quotes in the title or source
            $name = str_replace("'", "’", $name);
            $source = str_replace("'", "%27", $source);

            //JSONifies the title and source
            $name = '{"en":"'.$name.'"}';
            $topic = '{"en":"'.$topic.'"}';
            $source = '{"en":"'.$source.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `name`, `regime`, `type`, `topic`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$topic."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
            if (!$test && $ID !== 'NA-20161' && $ID !== 'NA-LawLaw') {$conn->query($SQL2);}
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