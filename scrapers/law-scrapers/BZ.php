<html><body>
    <?php
        //Settings
        $test = true; $country = 'BZ';
        $start = 2021;//Which year to start from
        $limit = null;//Which year to stop at

        //Opens the parser (HTML_DOM) and HTML
        include '../simple_html_dom.php'; //'../' refers to the parent directory
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
        
        //Loops through the types
        foreach (array('act'=>'Act', 'SI'=>'Statutory Instrument') as $typeCode => $typeName) {
            //Gets the laws
            $limit = date('Y');
            for ($page = $start; $page <= $limit; $page++) {
                //Gets the data from agm.gov.bz API
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://www.agm.gov.bz/api-laws/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('action' => 1001,'volume' => $page.'_'.$typeCode),
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: PHPSESSID=4e069f714e5e7a9bde5fbdcfe47b5872'
                    ),
                )); 
                $response = curl_exec($curl); curl_close($curl);

                //Runs through each bill
                $bills = json_decode($response, true)['data'];
                foreach ($bills as $bill) {
                    $html_dom->load($bill[0]);

                    //Makes sure you can get the code properly
                    if (!preg_match('/[A-Za-z]/', explode(' ', $html_dom->find('a')[0]->plaintext)[2])) {
                        //Interprets the data
                        $enforceDate = $page.'-01-01'; $enactDate = $enforceDate; $lastactDate = $enactDate;
                        $ID = $country.'-'.strtoupper($typeCode).explode(' ', $html_dom->find('a')[0]->plaintext)[2].$page;
                        $name = $html_dom->find('a')[0]->plaintext;

                        //Gets the regime
                        switch(true) {
                            case strtotime('1862') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1981'):
                                $regime = 'The Crown Colony of British Honduras';
                                break;
                            case strtotime('21 September 1981') < strtotime($enactDate) && strtotime($enactDate) < strtotime(date('d M Y')):
                                $regime = 'Belize';
                                break;
                        }

                        //Gets the rest of the values
                        $type = $typeName; $status = 'Valid';
                        $source = $html_dom->find('a')[0]->href;

                        //Makes sure there are no quotes in the title
                        if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                        //if (str_contains($Title, '"')) {$Title = str_replace('"', "\'", $Title);}
                        //if (str_contains($Title, '""')) {$Title = str_replace('""', "\'", $Title);}

                        //JSONifies the title
                        $name = '{"en":"'.$name.'"}';
                        $source = '{"en":"https://www.agm.gov.bz'.$source.'"}'; $PDF = $source;

                        //Makes sure there are no duplicates and adds law to the table
                        $SQL2 = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                        $result = $conn->query($SQL2);
                        if ($result->num_rows === 0) {
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `status`, `source`, `PDF`) 
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
                            if (!$test) {$conn->query($SQL2);}
                        }
                    }
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