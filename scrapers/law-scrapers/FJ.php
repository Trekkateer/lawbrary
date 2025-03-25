<html><body>
    <?php
        //Settings
        $test = true; $country = 'FJ';

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

        //Preloop functions
        function capitalize_title($string='', $delimiters=array()) {
            $string = strtolower($string);
            foreach ($delimiters as $delimiter) {
                $temp = explode($delimiter, $string);
                    array_walk($temp, function (&$value) {
                        if ($value !== 'of' && $value !== 'and' && $value !== 'the') {
                            $value = ucfirst($value);
                        }
                    });
                $string = implode($delimiter, $temp);
            }
            return $string;
        }

        //Loops through all the letters
        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W') as $letter) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.laws.gov.fj/acts/actlist/'.$letter);

            //Processes the data in the table
            $body_rows = $html_dom->find('table', 0)->find('a');
            foreach ($body_rows as $body_row) {
                //Gets raw values
                $enactDate = explode(' ', $body_row->innertext)[sizeof(explode(' ', $body_row->innertext))-1]; $enforceDate = $enactDate;
                $ID = $country.'-'.explode('/', $body_row->href)[3];
                $name = capitalize_title(trim(explode($enactDate, $body_row->innertext)[0]), array(' ', '('));
                    if ($ID === 'FJ-3062') {$name = 'Appropriation Act';}
                $source = 'https://www.laws.gov.fj'.$body_row->href;
                $enactDate = date("Y/m/d", strtotime($enactDate.'/01/01'));

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}'; $PDF = $source;
                //$topic = '{"en":"'.$topic.'"}';

                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '"."Law"."', '"."Valid"."', '".$source."')"; echo $SQL2.'<br/>';
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