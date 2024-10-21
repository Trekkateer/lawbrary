<html><body>
    <?php //!!They changed the website so this scraper is no longer functional!!
        //Settings
        $test = true; $country = 'SK';
        $start = 1;//Which year to start from
        $step = 100;//How many laws to include on each page
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

        //Translates the types
        $types = array(
            'Opatrenie'=>'Measure',
            'Nelegislatívny všeobecný materiál'=>'Non-legislative general material',
            'Zákon'=>'Act',
            'Akt EÚ'=>'EU Legislation',
            'Akt medzinárodného práva'=>'International Law',
            'Vyhláška'=>'Ordinance',
            'Nariadenie vlády Slovenskej republiky'=>'Regulation',
            'Poslanecký návrh - zákon'=>'Bill',
            'Poslanecký návrh - ústavný zákon'=>'Constitutional Law Proposal',
            'Informatívny materiál na rokovanie vlády SR'=>'Informative Material',
            'Legislatívny zámer'=>'Legislative Intent',
            'Ústavný zákon'=>'Constitutional Law',
            'Oznámenie'=>'Announcement',
            'Predbežná informácia'=>'Preliminary Information'
        );

        //Translates the statuses
        $statuses = array(
            ' Medzirezortné pripomienkové konanie '=>'Inter-ministerial Comment Procedure',
            ' Vyhodnotenie medzirezortného pripomienkového konania '=>'Evaluation of the Inter-ministerial Comment Procedure',
            ' Pred rokovaním '=>'Before the Meeting',
            ' Rokovanie poradných orgánov vlády SR '=>'Meeting of the Advisory Bodies of the Government of the Slovak Republic',
            ' Po rokovaní poradných orgánov vlády SR '=>'After the meeting of the advisory bodies of the Government of the Slovak Republic',
            ' Rokovanie vlády SR '=>'Meeting of the Government of the Slovak Republic',
            ' Schválené na vláde SR '=>'Approved by the Government of the Slovak Republic',
            ' Schvaľovanie ministrom '=>'Approval by the Minister',
            ' Schvaľovanie prezidentom SR '=>'Approval by the President of the Slovak Republic',
            ' Rokovanie Národnej rady SR '=>'Meeting of the National Council of the Slovak Republic',
            ' Redakčná úprava '=>'Editorial Edit',
            ' Pred publikovaním materiálu '=>'Before you publish a material',
            ' Publikácia opatrenia do RO '=>'Publication of measures to RO',
            ' Pripomienkovanie predbežnej informácie '=>'Commenting on the preliminary information',
            ' Predprípravná fáza ukončená '=>'Pre-preparatory phase completed',
            ' Zápis stanoviska k návrhu aktu EÚ '=>'Entry of an opinion on a proposal for an EU act',
            ' Vytvorenie finálneho predbežného stanoviska '=>'Creation of a final preliminary opinion',
            ' Vyhodnotenie medzirezortného pripomienkového konania Akt EÚ '=>'Evaluation of the inter-ministerial comment procedure on the EU Act',
            ' Po rokovaní Úradu vlády SR '=>'After the meeting of the Office of the Government of the Slovak Republic',
            ' Po rokovaní '=>'After the Meeting',
            ' Vyhodnotenie medzirezortného pripomienkového konania Akt EÚ '=>'Evaluation of the inter-ministerial comment procedure on the EU Act',
            ' Publikované v Zbierke zákonov SR '=>'Published in Collection of Laws of the Slovak Republic',
            ' Po rokovaní NR SR '=>'After the meeting of the National Council of the Slovak Republic',
            ' Publikovanie materiálu '=>'Publish material'
        );

        //Gets the limit
        $html_dom = file_get_html('https://www.slov-lex.sk/vyhladavanie-legislativneho-procesu?filter=1&orderByType=asc&delta='.$step);
        $limit = $limit ?? explode(' z ', $html_dom->find('span.lfr-icon-menu-text')[0]->plaintext)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://www.slov-lex.sk/vyhladavanie-legislativneho-procesu?filter=1&orderByType=asc&delta='.$step.'&cur='.$page);
            $laws = $html_dom->find('table.table.table-bordered.table-hover.table-striped')[0]->find('tbody.table-data')[0]->find('tr[class!="lfr-template"]');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = date('Y-m-d', strtotime(trim(str_replace('.', '-', $law->find('td')[4]->plaintext)))); $enforceDate = $enactDate; $lastactDate = $enforceDate;
                $ID = str_replace('/', '', trim($law->find('td')[1]->plaintext));
                $regime = 'The Slovak Republic';
                $name = trim($law->find('td')[2]->plaintext);
                $type = $types[$law->find('td')[0]->find('img')[0]->title];
                $status = $statuses[$law->find('td')[3]->plaintext];
                $source = $law->find('td')[2]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"sk":"'.$name.'"}';
                $source = '{"sk":"'.$source.'"}';

                //Makes sure the date is attainable
                if ($enactDate !== '1970-01-01') {
                    //Inserts the new laws
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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