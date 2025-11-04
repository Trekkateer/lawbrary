<?php //The Court Listener scraper
    //Settings
    $test = true; $table = 'US';
    $start = 82;//the API does not have data for congresses before 82
    $step = 250;//Number of laws on each page
    $limit = null;//Total number of laws desired for each congress

    //Only displays errors
    error_reporting(E_ERROR | E_PARSE);

    //Opens simple html dom
    include 'simple_html_dom.php';
    $preamble_dom = new simple_html_dom();

    //Opens my library
    include 'skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($table)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Gets the static variables
    $saveDate = date('Y-m-d'); $country = '["US"]';
    $type = 'Court Opinion'; $status = 'Active';
    $regime = '{"en":"The United States of America"}';
    $publisher = '{"en":"The Free Law Project"}';

    //Creates function to crawl the website with curl
    function CURL_Call ($href) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $href,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Authorization: Token 45be3678f850c620d9a72fa09f448ebead577414'),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return json_decode($response, true);
    };

    //Creates function to capitalize with exceptions
    $exceptions = [
        'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'up', 'vs', 'v'
    ];
    function ucwordsexcept($str, $delims=' ') {global $exceptions;
        //Capitalizes words in a string except for those in the exceptions array
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

    //Gets the limit and next page link
    $limit = CURL_Call('https://www.courtlistener.com/api/rest/v4/opinions/?count=on&order_by=date_created')['count']; echo $limit.'<br/>';
    $next = 'https://www.courtlistener.com/api/rest/v4/opinions/?order_by=date_created'; echo $next.'<br/><br/>';
    //Gets case laws from CourtListener API
    for ($page = 0; $page <= $limit/$step; $page++) {
        //Gets the results from the API
        $laws = CURL_Call($next)['results'];
        foreach ($laws as $law) {
            //Gets the data
            $enactDate = $enforceDate = $law['date_created']; $lastactDate = $law['date_modified'];
            $ID = $table.':'.$law['id'];
            $name = fixQuotes(ucwords(strtr(explode('/', $law['absolute_url'])[3], ['-' => ' '])), 'en');
            $HTML = $law['html_with_citations'];
            $preamble = strtr($preamble_dom->load($HTML)->find('pre', 0)->innertext, ['   '=> ' ', '  '=> ' ']);
            $origin = str_replace('For', 'for', trim(explode("No.", $preamble)[0]));
            //$text = fixQuotes($law['plain_text'], 'en');
            $source = 'https://www.courtlistener.com'.$law['absolute_url'];
            $pdf = 'https://storage.courtlistener.com'.$law['local_path'];

            //JSONifies the title and source and text
            $name = '{"en":"'.$name.'"}';
            $origin = '{"en":"'.$origin.'"}';
            $source = '{"en":"'.$source.'"}';
            $pdf = '{"en":"'.$pdf.'"}';
            //$text = '{"en":"'.$text.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($table)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `origin`, `type`, `status`, `source`, `pdf`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$origin."', '".$type."', '".$status."', '".$source."', '".$pdf."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }

        //Sets the next page link
        $next = CURL_Call($next)['next'];
    }

    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$table."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>