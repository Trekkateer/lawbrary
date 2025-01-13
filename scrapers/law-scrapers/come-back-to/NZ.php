<html><body>
    <?php //!!The request keeps getting blocked!!
        //Settings
        $test = true; $scraper = 'NZ';
        $start = 0;//Which page to start from
        $step = 200;//How much to increase the limit by every iteration
        $limit = null;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the limit
        $html_dom = file_get_html('https://www.legislation.govt.nz/act/results.aspx?search=ad_act______1_ac%40bn%40rn%40dn%40apub%40aloc%40apri%40apro%40aimp%40bgov%40bloc%40bpri%40bmem%40rpub%40rimp_ac%40ainf%40anif%40aaif%40aase%40arep%40bcur%40rinf%40rnif_a_aw_se_&p=1');
        $limit = $limit ?? explode(' ', $html_dom->find('#ctl00_Cnt_resultsReturnedLabel')[0]->plaintext)[4];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page+=$step) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.legislation.govt.nz/act/results.aspx?search=ad_act______'.$step.'_ac%40bn%40rn%40dn%40apub%40aloc%40apri%40apro%40aimp%40bgov%40bloc%40bpri%40bmem%40rpub%40rimp_ac%40ainf%40anif%40aaif%40aase%40arep%40bcur%40rinf%40rnif_a_aw_se_&p='.$page);// echo $html_dom;

            //Processes the data in the table
            $laws = $html_dom->find('table')[0]->find('tbody')[0]->find('tr[class^="results"]');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = $enforceDate = $lastactDate = explode(' No', $law->find('td.resultsYear')->plaintext)[0].'-01-01';
                $ID = $scraper.'-'.strtr($law->find('td.resultsYear')->plaintext, array(' No '=>''));
                $name = trim($law->find('td.resultsTitle')->find('a')->plaintext);
                $country = '["NZ"]';
                $regime = strtotime($enactDate) < strtotime('25 November 1947') ? '{"en":"The British Empire"}':'{"en":"New Zealand", "mi":"Aotearoa"}';
                $type = 'Act';
                $status = str_contains($law->plaintext, '[repealed]') ? 'Repealed':'Valid';
                $source = $law->find('td.resultsTitle')->find('a')->href;

                //Makes sure there are no apostrophes in the title
                $name = strtr($name, array(" '"=>" ‘", "'"=>"’"));

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test && !str_contains($law->plaintext, '[not the latest version]')) {$conn->query($SQL2);}
            }
        }
        
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>