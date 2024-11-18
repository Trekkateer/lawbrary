<html><body>
    <?php //!!The request keeps getting blocked!!
        //Settings
        $test = true; $country = 'NZ';
        $start = 0;//Which page to start from
        $step = 200;//How much to increase the limit by every iteration
        $limit = null;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php'; // '../' refers to the parent directory

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
        $html_dom = file_get_html('https://www.legislation.govt.nz/act/results.aspx?search=ad_act______1_ac%40bn%40rn%40dn%40apub%40aloc%40apri%40apro%40aimp%40bgov%40bloc%40bpri%40bmem%40rpub%40rimp_ac%40ainf%40anif%40aaif%40aase%40arep%40bcur%40rinf%40rnif_a_aw_se_&p=1');
        $limit = $limit ?? explode(' ', $html_dom->find('#ctl00_Cnt_resultsReturnedLabel')[0]->plaintext)[4];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page+=$step) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.legislation.govt.nz/act/results.aspx?search=ad_act______'.$step.'_ac%40bn%40rn%40dn%40apub%40aloc%40apri%40apro%40aimp%40bgov%40bloc%40bpri%40bmem%40rpub%40rimp_ac%40ainf%40anif%40aaif%40aase%40arep%40bcur%40rinf%40rnif_a_aw_se_&p='.$page);// echo $html_dom;

            //Processes the data in the table
            $body_rows = $html_dom->find('table')[0]->find('tbody')[0]->find('tr[class^="results"]');
            foreach ($body_rows as $body_row) {
                //Gets values
                $name = trim($body_row->find('td.resultsTitle')->find('a')->plaintext);
                $enactDate = explode(' No', $body_row->find('td.resultsYear')->plaintext)[0].'/01/01'; $enforceDate = $enactDate;
                $ID = $country.'-'.strtr($body_row->find('td.resultsYear')->plaintext, array(' No '=>''));
                if (str_contains($body_row->plaintext, '[repealed]')) {$status = 'Repealed';} else {$status = 'Valid';}
                $source = $body_row->find('td.resultsTitle')->find('a')->href;

                //Makes sure there are no quotes in the title
                $name = str_replace("'", "’", $name);
                //if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                //$topic = '{"en":"'.$topic.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test && !str_contains($body_row->plaintext, '[not the latest version]')) {$conn->query($SQL2);}
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