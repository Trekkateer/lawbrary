<?php //Fiji
    //Settings
    $test = false; $scraper = 'FJ';

    //Opens my library
    include '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
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

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["FJ"]'; $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"The Office of the Attorney-General of Fiji"}';

    //Loops through all the letters
    foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W') as $letter) {
        //Gets the HTML
        $dom = file_get_html('https://www.laws.gov.fj/acts/actlist/'.$letter);

        //Processes the data in the table
        $laws = $dom->find('table', 0)->find('a');
        foreach ($laws as $law) {
            //Gets raw values
            $enactDate = $enforceDate = $lastactDate = explode(' ', $law->innertext)[sizeof(explode(' ', $law->innertext))-1].'-01-01';
            $ID = $scraper.':'.explode('/', $law->href)[3];
            $name = fixQuotes(capitalize_title(trim(explode($enactDate, $law->innertext)[0]), array(' ', '(')), 'en');
                if ($ID === 'FJ-3062') {$name = 'Appropriation Act';}
            //Gets the regime
            $regime = '{"en":"The Republic of Fiji", "fj":"Matanitu Tugalala o Viti", "hif":"फ़िजी गणराज्य"}';
            if (strtotime($enactDate) < strtotime('1987-10-06')) {
                $regime = '{"en":"The Dominion of Fiji", "fj":"Na Tugalala o Viti", "hif":"फिजी डोमिनियन"}';
            }
            //Gets the source
            $source = 'https://www.laws.gov.fj'.$law->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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