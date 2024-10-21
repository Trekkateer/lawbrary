<html><body>
    <?php
        //Settings
        $test = true; $country = 'TT';
        $start = 1832;//Which year to start from
        $limit = null;//Total number of years desired.

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $HTTP_Call = function ($year) {return file_get_html('http://laws.gov.tt/ttdll-web/revision/byyear?year='.$year.'&apply=Apply');};

        //Gets the limit
        $limit = $limit ?? Date('Y');
        //Loops though the years
        for ($year = $start; $year < $limit; $year++) {
            //Gets values
            $html_dom->load($HTTP_Call($year));
            foreach ($html_dom->find('table.table.table-striped.m-b-none')[0]->find('tbody')[0]->find('tr') as $law) {
                //Gets the values
                $enactDate = $year.'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.trim(strtr($law->find('td')[1]->plaintext, array('No. '=>'', ' of '=>'')));
                $name = $law->find('td')[2]->plaintext;
                //Gets the regime
                switch(true) {
                    case strtotime('27 March 1802') < strtotime($enactDate) && strtotime($enactDate) < strtotime('3 January 1958'):
                        $regime = 'The Crown Colony of Trinidad and Tobago';
                        break;
                    case strtotime('3 January 1958') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 January 1962'):
                        $regime = 'The Federation of the West Indies';
                        break;
                    case strtotime('14 January 1962') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 August 1976'):
                        $regime = 'Trinidad and Tobago';
                        break;
                    case strtotime('1 August 1976') < strtotime($enactDate) && strtotime($enactDate) < strtotime(date('d M Y')):
                        $regime = 'The Republic of Trinidad and Tobago';
                        break;
                }
                //Gets the rest of the values
                $type = 'Law';
                    if (str_contains($name, 'Ammendment')) {$type = 'Ammendment to '.$type;}
                $status = 'Valid';
                $source = 'https://laws.gov.tt/'.$law->find('td')[0]->find('a')[0]->href; $PDF = $source;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', "\"", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $PDF = '{"en":"'.$PDF.'"}';

                //Inserts the law to the table
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';

                //Makes the query
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