<html><body>
    <?php //!!Data doesn't load
        //Settings
        $test = true; $country = 'MN';
        $limit = null;//Total number of laws desired

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

        echo file_get_html('https://legalinfo.mn/mn/law?page=law&cate=27&active=1&page=1&sort=title&page=1');

        //Loops through the types
        foreach (array(27=>'Law') as $typeNum=>$type) {
            //Gets the limit
            $html_dom = file_get_html('https://legalinfo.mn/mn/law?page=law&cate='.$typeNum.'&active=1&page=1&sort=title&page=1');
            $limit = $limit ?? explode('/', $html_dom->find('li[class="number uk-disabled"]')[0]->plaintext)[1]; echo $limit;

            //Processes the data in the table
            $body_rows = $html_dom->find('table', 0)->find('a');
            foreach ($body_rows as $body_row) {
                //Gets raw values
                $enactDate = explode(' ', $body_row->innertext)[sizeof(explode(' ', $body_row->innertext))-1]; $enforceDate = $enactDate;
                $ID = $country.'-'.explode('/', $body_row->href)[3];
                $name = capitalize_after_delimiters(trim(explode($enactDate, $body_row->innertext)[0]), array(' '), array('('));
                    if ($ID === 'FJ-3062') {$name = 'Appropriation Act';}
                $source = 'https://www.laws.gov.fj'.$body_row->href; $PDF = $source;
                $enactDate = date("Y/m/d", strtotime($enactDate.'/01/01'));

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$source.'"}';
                //$topic = '{"en":"'.$topic.'"}';

                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '"."Law"."', '"."Valid"."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }

            //Resets the limit
            $limit = null;
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