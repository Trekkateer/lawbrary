<html><body>
    <?php //!!Not enough values
        //Settings
        $test = true; $country = 'SS';

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

        //Gets the HTML
        $html_dom = file_get_html('https://mojca.gov.ss/laws/');

        //Processes the data in the table
        $body_rows = $html_dom->find('li.elementor-icon-list-item');
        foreach ($body_rows as $body_row) {
            //Gets values
            $enactDate = date('Y/m/d', strtotime(str_replace(',', ' ', $body_row->find('td')[1]->find('span')[0]->innertext))); $enforceDate = $enactDate;
            
            $ID = $country.'-'.zero_buffer($page*10+$rowNum);
            
            $name = trim(str_replace(' of '.$year, '',  $body_row->find('td')[0]->find('a')[0]->innertext));
                if (str_ends_with($name, $year)) $name = substr($name, 0, strlen($name)-5);
           
            $source = $body_row->find('td')[3]->find('a')[0]->href; $PDF = $source;
            
            if (str_contains('Amendment', $name)) {$type = 'Amendment';} else {$type = 'Law';}

            //Makes sure there are no quotes in the title
            if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
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