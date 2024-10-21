<html><body>
    <?php
        //Settings
        $test = true; $country = 'ZW';
        $start = 1;//Which page to start from
        $step = 20;//How many laws are on each page
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

        //Makes sure there are four digits in each outputed number
        $zero_buffer = function ($input, $numLen=4) {
            $output = ''.$input;
            while (strlen($output)<$numLen) {$output = '0'.$output;}
            return $output;
        };

        //Gets the limit
        $html_dom = file_get_html('https://www.law.co.zw/acts-of-parliament/');
        $limit = $limit ?? end($html_dom->find('a[class="page-numbers"]'))->plaintext; echo $limit;
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.law.co.zw/acts-of-parliament/?page='.$page);// echo $html_dom;

            //Processes the data in the table
            $laws = $html_dom->find('#content_wpdm_package_1')[0]->find('div.row')[0]->find('div.list-group');
            foreach ($laws as $lawNum => $law) {
                //Gets values
                $name = $law->find('div')[0]->find('a')[0]->plaintext;
                $enactDate = date('Y-m-d', strtotime($law->find('div')[3]->find('span.badge')[0]->plaintext)); $enforceDate = $enactDate;
                $ID = $country.'-'.$zero_buffer($page.$lawNum);
                $type = 'Act';
                    if (str_contains($name, 'Amendment')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $source = $law->find('div')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                $name = str_replace("'", "’", $name);

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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