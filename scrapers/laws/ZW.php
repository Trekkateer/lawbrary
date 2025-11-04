<?php //Zimbabwe
    //Settings
    $test = false; $scraper = 'ZW';
    $start = 1;//Which page to start from
    $step = 20;//How many laws are on each page
    $limit = null;//How many pages there are

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

    //Creates function to capitalize with exceptions
    $exceptions = ['and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'up'];
    function ucwordsexcept($str, $exceptions, $delims=' ',) {
        $out = array(trim($str));
        foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
            if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
            $out[$key+1] = '';
            foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                $out[$key+1] .= !in_array($word, $exceptions) ? mb_strtoupper($word[0], 'UTF-8').substr($word, 1).$delim:$word.$delim;
            }
            $out[$key+1] = rtrim($out[$key+1], $delim);
        }
        return ucfirst(end($out));
    }

    //Sanitize Title
    $sanitizeTitle = [' )'=>')'];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["ZW"]';
    $type = 'Act'; $status = 'Valid';
    $publisher = '{"en":"law.co.zw"}';

    //Gets the limit
    $dom = file_get_html('https://www.law.co.zw/acts-of-parliament/');
    $limit = $limit ?? end($dom->find('a[class="page-numbers"]'))->plaintext;
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {
        //Gets the HTML
        $dom = file_get_html('https://www.law.co.zw/acts-of-parliament/?page='.$page);

        //Processes the data in the table
        $laws = $dom->find('#content_wpdm_package_1', 0)->find('div.row', 0)->find('div.list-group');
        foreach ($laws as $lawNum => $law) {
            //Gets values
            $enactDate = date('Y-m-d', strtotime($law->find('div', 3)->find('span.badge', 0)->plaintext));
            $ID = $scraper.':'.str_pad($page.$lawNum, 4, '0', STR_PAD_LEFT);
            $name = ucwordsexcept(fixQuotes(strtolower(str_replace(array_keys($sanitizeTitle), array_values($sanitizeTitle), $law->find('div', 0)->find('a', 0)->plaintext)), 'en'), $exceptions, ' (');
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
            //Gets the rest of the values
            //$isAmend = str_contains($name, 'Amendment') ? 1:0;
            $source = $law->find('div', 0)->find('a', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enactDate."', '".$enactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }
    }
    
    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>