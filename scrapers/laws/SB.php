<?php
    //Settings
    $test = false; $scraper = 'SB';
    $start = 0;//Which year to start from
    $limit = null;//Which year to end at

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Properly capitalizes the name
    $exceptions = array('of', 'and', 'the');
    function ucwordsexcept($string, $delims=' ') {
        global $exceptions;
        $string = strtolower($string);
        foreach (str_split($delims) as $delim) {
            $temp = explode($delim, $string);
                array_walk($temp, function (&$value) {
                    if (!in_array($value, $exceptions)) {
                        $value = ucfirst($value);
                    }
                });
            $string = implode($delim, $temp);
        }
        return $string;
    }

    //Fixes the laws that have no spaces
    $sanitizeName = array(
        'CorrectionalServicesAct2007' => 'Correctional Services Act 2007',
        'MagistratesCourts(AmendmentAct2007' => 'Magistrates Courts (Amendment) Act 2007',
        'PrescriptionofMinistersAct2007' => 'Prescription of Ministers Act 2007',
        'PrescriptionofParliamentaryPrivilegesImmunitiesandPowersAct2007' => 'Prescription of Parliamentary Privileges Immunities and Powers Act 2007',
        'StateOwnedEnterprisesAct2007' => 'State Owned Enterprises Act 2007',
        'The Income Taxt (Amendment) (NO. 2) Act1991' => 'The Income Taxt (Amendment) (NO. 2) Act 1991',
        'SOLOMON ISLANDS RED CROSS SOCIETY ACT1983' => 'Solomon Islands Red Cross Society Act 1983',

        '.pdf'=>'', '.PDF'=>'', '_'=>' ', '( '=>'(', ' )'=>')', '(('=>'(', ')A'=>') A', '  '=>' '
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["SB"]'; $type = 'Act';
    $regime = '{"en":"The Solomon Islands"}';
    $publisher = '{"en":"The National Parliament of the Solomon Islands"}';

    //Gets the limit
    $dom = file_get_html('https://www.parliament.gov.sb/index.php/acts-parliament');
    $limit = $limit ?? explode('page=', $dom->find('a[title="Go to last page"]', 0)->href)[1];
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Processes the data
        $dom = file_get_html('https://www.parliament.gov.sb/index.php/acts-parliament?page='.$page);
        $laws = $dom->find('a[href^="/sites/default/files/"]');
        foreach ($laws as $lawNum => $law) {
            //Gets values
            $enactDate = $enforceDate = $lastactDate = preg_replace('/[A-Za-z]/', '', explode('/', $law->href)[4]) === '' ? end(explode(' ', strtr($law->plaintext, $sanitizeName))).'-01-01':explode('/', $law->href)[4].'-01';
            $ID = $scraper.':'.$page.$lawNum;
            $name = fixQuotes(ucwordsexcept(strtr($law->plaintext, $sanitizeName), array(' (')), 'en');
            $isAmend = str_contains($name, 'Amendment') ? 1:0;
            $status = str_contains($name, 'Repealed') ? 'Repealed':'Valid';
            $source = 'https://www.parliament.gov.sb'.$law->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = $PDF = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }
    }
    
    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>