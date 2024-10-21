<html><body>
    <?php
        //Settings
        $test = true; $country = 'ZM';
        $start = 0;//Which page to start from
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
        $html_dom = file_get_html('https://www.parliament.gov.zm/acts-of-parliament');
        $limit = $limit ?? explode('page=', $html_dom->find('a[title="Go to last page"]')[0]->href)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.parliament.gov.zm/acts-of-parliament?page='.$page);

            //Processes the data in the table
            $laws = $html_dom->find('div.view-content')[0]->find('li.views-row');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = explode(')', end(explode(' ', $law->find('div.views-field-title')[0]->find('span.field-content')[0]->find('a')[0]->find('div.act-number-appended')[0]->plaintext)))[0].'-01-01'; $enforceDate = $enactDate;
                $ID = $country.'-'.strtr($law->find('div.views-field-title')[0]->find('span.field-content')[0]->find('a')[0]->find('div.act-number-appended')[0]->plaintext, array('( Act No. '=>'', ' of '=>'', ' '=>'', ')'=>''));
                $name = trim(explode('<div', $law->find('div.views-field-title')[0]->find('span.field-content')[0]->find('a')[0]->innertext)[0]);
                $type = 'Act';
                    if (str_contains('Amendment', $name)) {$type = 'Amendment to '.$type;}
                $source = 'https://www.parliament.gov.zm'.$law->find('div.views-field-title')[0]->find('span.field-content')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '"."Valid"."', '".$source."')"; echo $SQL2.'<br/>';
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