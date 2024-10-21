<html><body>
    <?php
        //Settings
        $test = false; $country = 'SB';
        $start = 0;//Which year to start from
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

        //Properly capitalizes the name
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

        //Fixes the laws that have no spaces
        $spaceFixer = array(
            'CorrectionalServicesAct2007' => 'Correctional Services Act 2007',
            'MagistratesCourts(AmendmentAct2007' => 'Magistrates Courts (Amendment) Act 2007',
            'PrescriptionofMinistersAct2007' => 'Prescription of Ministers Act 2007',
            'PrescriptionofParliamentaryPrivilegesImmunitiesandPowersAct2007' => 'Prescription of Parliamentary Privileges Immunities and Powers Act 2007',
            'StateOwnedEnterprisesAct2007' => 'State Owned Enterprises Act 2007',
            'The Income Taxt (Amendment) (NO. 2) Act1991' => 'The Income Taxt (Amendment) (NO. 2) Act 1991',
            'SOLOMON ISLANDS RED CROSS SOCIETY ACT1983' => 'Solomon Islands Red Cross Society Act 1983',
        );

        //Gets the limit
        $html_dom = file_get_html('https://www.parliament.gov.sb/index.php/acts-parliament');
        $limit = $limit ?? explode('page=', $html_dom->find('a[title="Go to last page"]')[0]->href)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://www.parliament.gov.sb/index.php/acts-parliament?page='.$page);
            $laws = $html_dom->find('a[href^="/sites/default/files/"]');
            foreach ($laws as $lawNum => $law) {
                //Gets values
                $enactDate = preg_replace('/[A-Za-z]/', '', explode('/', $law->href)[4]) === '' ? end(explode(' ', strtr(strtr($law->plaintext, $spaceFixer), array('.pdf'=>'', '.PDF'=>'', '_'=>' ')))).'-01-01':explode('/', $law->href)[4].'-01'; $enforceDate = $enactDate; $lastactDate = $enforceDate;
                $ID = $country.'-'.$page.$lawNum;
                $regime = 'The Solomon Islands';
                $name = capitalize_title(strtr(strtr($law->plaintext, $spaceFixer), array('.pdf'=>'', '.PDF'=>'', '_'=>' ', '( '=>'(', ' )'=>')', '(('=>'(', ')A'=>') A')), array(' ', '('));
                $type = 'Act';
                    if (str_contains($name, 'Amendment')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                    if (str_contains($name, 'Repealed')) {$status = 'Repealed';}
                $source = 'https://www.parliament.gov.sb'.$law->href; $pdf = $source;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"en":"'.$name.'"}';
                $source = '{"en":"'.$source.'"}';
                $pdf = '{"en":"'.$pdf.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `pdf`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
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