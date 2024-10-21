<html><body>
    <?php //!!Not a general law database
        //Settings
        $test = true; $country = 'SY';

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';

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
        $html_dom = file_get_html('https://oag.gov.bt/language/en/resources/acts-2/'); //echo $html_dom;

        //Processes the data in the table
        $body_rows = $html_dom->find('a[href^="https://oag.gov.bt/wp-content/uploads/"]');
        foreach ($body_rows as $body_row) {
            if ($body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/Co-operatives-Amendment-Act-of-Bhutan-2009-English.pdf' &&
              $body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/22Child Care &amp; Protection Act.pdf' &&
              $body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/Co-operatives-Amendment-Act-of-Bhutan-2009-English.pdf' &&
              $body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/Co-operatives-Amendment-Act-of-Bhutan-2009-Dzongkha.pdf' &&
              $body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/Kadyonka-kha-ga-nga-cha-chha1969-1972.pdf' &&
              $body_row->href !== 'https://oag.gov.bt/wp-content/uploads/2010/05/Seeds-Act-of-Bhutan-2000Dzongkha.pdf') {
                //Sets the language
                if ($body_row->innertext === 'English') {$lang = 'en';}
                else if ($body_row->innertext === 'Dzongkha') {$lang = 'dz';}

                //Gets the name, year and href
                $path = explode('/', $body_row->href)[sizeof(explode('/', $body_row->href))-1];
                $name = explode('.pdf', $path)[0];
                    $name = str_replace('-', ' ', $name);//Creates spaces
                    $name = str_replace('_', ' ', $name);
                    $name = str_replace('%20', ' ', $name);
                    $name = str_replace(' version0', '', $name);//Deletes version
                    $name = str_replace(' version', '', $name);
                    $name = str_replace(' English and Dzongkha', '', $name);//Changes language
                    $name = str_replace('Both ', '', $name);
                    $name = str_replace('both ', '', $name);
                    $name = str_replace('English1', '', $name);
                    $name = str_replace('Dzongkha 1', '', $name);
                    $name = str_replace('English', '', $name);
                    $name = str_replace('english', '', $name);
                    $name = str_replace('Dzongkhag', '', $name);
                    $name = str_replace('Dzongkha', '', $name);
                    $name = str_replace('dzongkha', '', $name);
                    $name = str_replace('Eng & Dzo', '', $name);
                    $name = str_replace('Dzo & Eng', '', $name);
                    $name = str_replace(' Dzo & Eng', '', $name);
                    $name = str_replace('()', '', $name);//Gets rid of parentheses
                    
                    echo $name.'<br/>';


                //Creates SQL
                /*$SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `country`, `ID`, `name`, `type`, `status`, `source`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$country."', '".$ID."', '".$name."', '".$type."', '"."Valid"."', '".$href."')";

                //Executes the SQL
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}*/
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
        }
    ?>
</body></html>