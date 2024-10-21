<html><body>
    <?php
        //Settings
        $test = true; $country = 'MH';

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; //'../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Helps resolve the types of specific laws
        $lawTypes = array(
            'Act'=>'Act',
            'EUADA'=>'Act',
            'Facilitation'=>'Act',
            'Fund'=>'Act',
            'Ministries'=>'Act',
            'Nitijela'=>'Act',
            'Office'=>'Act',
            'RADA'=>'Act',
            'Code'=>'Code',
            'Islands'=>'Constitution'
        );

        //Loops through the letters
        foreach(array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
            //Gets data
            $html_dom = file_get_html('https://www.rmiparliament.org/cms/legislation.html?view=acts_alpha&submit4='.$letter);

            //Processes the data in the table
            $laws = $html_dom->find('table[class="table table-bordered table-hover table-condensed"]')[0]->find('tbody')[0]->find('tr.row0');
            foreach($laws as $law) {
                $vals = array(//Sets up the values for each law
                    'Source'=>'',
                    'PDF' => '',
                    'ID' => '',
                    'Title' => '',
                    'Topic' => '',
                    'Enactment Date' => '',
                    'Enforcement Date' => ''
                );

                //Gets the note
                $summary = $law->find('td')[2]->rel ?? NULL;
                    $summary = isset($summary) ? '\''.trim($summary).'\'':'NULL';
    
                //Gets the datapoints from cells
                $cells = $law->find('td');
                for($cell = 2; $cell <= 5; $cell++) {
                    $vals[array_keys($vals)[$cell]] = trim($cells[$cell]->innertext);
                }
    
                //Finalizes the values
                $title_dom = new simple_html_dom(); $title_dom->load($vals['Title']);
                $vals['Enactment Date'] = date('Y-m-d', strtotime($vals['Enactment Date'])); $vals['Enforcement Date'] = $vals['Enactment Date'];
                $vals['ID'] = $country.'-'.strtr($vals['ID'], array('-'=>'', ' '=>''));
                $vals['Title'] = explode(' [', $title_dom->find('a')[0]->plaintext)[0];
                $type = $lawTypes[end(explode(' ',  trim(strtr(preg_replace('/[^A-Za-z ]/', '', $vals['Title']), array(' of'=>' ')))))];
                $vals['Topic'] = explode(' - ', $vals['Topic'])[1];
                $vals['Source'] = 'https://www.rmiparliament.org'.$title_dom->find('a')[0]->href; $vals['PDF'] = $vals['Source'];

                //Sets the regime
                switch(true) {
                    case strtotime('29 August 1885') < strtotime($vals['Enactment Date']) && strtotime($vals['Enactment Date']) < strtotime('28 June 1919'):
                        $regime = 'The German Empire';
                        break;
                    case strtotime('28 June 1919') < strtotime($vals['Enactment Date']) && strtotime($vals['Enactment Date']) < strtotime('18 June 1947'):
                        $regime = 'The Empire of Japan';
                        break;
                    case strtotime('18 June 1947') < strtotime($vals['Enactment Date']) && strtotime($vals['Enactment Date']) < strtotime('22 December 1990'):
                        $regime = 'The United States of America';
                        break;
                    case strtotime('22 December 1990') < strtotime($vals['Enactment Date']) && strtotime($vals['Enactment Date']) < strtotime(date('d M Y')):
                        $regime = 'The Republic of the Marshall Islands';
                        break;
                }

                //Makes sure there are no appostophes in the title or origin
                $vals['Title'] = str_replace("'", "’", $vals['Title']);

                //JSONifies the values
                $vals['Title'] = '{"en":"'.$vals['Title'].'"}';
                $vals['Topic'] = '{"en":"'.$vals['Topic'].'"}';
                $vals['Source'] = '{"en":"'.$vals['Source'].'"}';
                $vals['PDF'] = '{"en":"'.$vals['PDF'].'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `regime`, `name`, `type`, `topic`, `status`, `summary`, `source`, `PDF`)
                        VALUES ('".$vals['Enactment Date']."', '".$vals['Enforcement Date']."', '".$vals['ID']."', '".$regime."', '".$vals['Title']."', '".$type."', '".$vals['Topic']."', '"."Valid"."', ".$summary.", '".$vals['Source']."', '".$vals['PDF']."')";

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
    ?>
</body></html>