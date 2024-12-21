<html><body>
    <?php
        //Settings
        $test = true; $LBpage = 'ZW';
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
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Makes sure there are four digits in each outputed number
        $zero_buffer = function ($input, $numLen=4) {
            $output = ''.$input;
            while (strlen($output)<$numLen) {$output = '0'.$output;}
            return $output;
        };

        //Gets the limit
        $html_dom = file_get_html('https://www.law.co.zw/acts-of-parliament/');
        $limit = $limit ?? end($html_dom->find('a[class="page-numbers"]'))->plaintext;
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.law.co.zw/acts-of-parliament/?page='.$page);

            //Processes the data in the table
            $laws = $html_dom->find('#content_wpdm_package_1')[0]->find('div.row')[0]->find('div.list-group');
            foreach ($laws as $lawNum => $law) {
                //Gets values
                $enactDate = date('Y-m-d', strtotime($law->find('div')[3]->find('span.badge')[0]->plaintext));
                $ID = $LBpage.'-'.$zero_buffer($page.$lawNum);
                $name = $law->find('div')[0]->find('a')[0]->plaintext;
                $country = '["ZW"]';
                //Gets the regime
                switch (true) {
                    case strtotime($enactDate) <= strtotime("today"):
                        $regime = '{"en":"The Republic of Zambia"}';
                        break;
                    case strtotime($enactDate) <= strtotime("18 April 1980"):
                        $regime = '{"en":"The Republic of Rhodesia"}';
                        break;
                    case strtotime($enactDate) <= strtotime("11 November 1965"):
                        $regime = '{"en":"The British Empire"}';
                        break;
                }
                $regime = strtotime($enactDate) > strtotime('24 October 1964') ? '{"en":"The Republic of Zambia}':'{"en":"The British Empire"}';
                //Gets the rest of the values
                $type = 'Act';
                $isAmend = str_contains($name, 'Amendment') ? 1:0;
                $status = 'Valid';
                $source = $law->find('div')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                $name = str_replace("'", "’", $name);

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `type`, `isAmend`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enactDate."', '".$enactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$type."', ".$isAmend.", '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$LBpage."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>