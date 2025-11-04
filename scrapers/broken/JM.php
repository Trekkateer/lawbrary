<?php //Jamaica
    //!!No way to get the date!!

    //Settings
    $test = true; $scraper = 'JM';
    $start = 0;//Which law to start from
    $step = 500;//How many laws on a page
    $limit = NULL;//Total number of laws desired

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $title_dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["JM"]';
    $type = 'Revised Statute'; $status = 'In Force';
    $publisher = '{"en":"The Ministry of Justice"}';

    //Gets the total number of laws
    $limit = $limit ?? json_decode(file_get_contents('https://laws.moj.gov.jm/library/statutes/revised?draw=2&columns[0][data]=shortTitle&columns[0][name]=&columns[0][searchable]=true&columns[0][orderable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=legalAreas&columns[1][name]=&columns[1][searchable]=false&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=actions&columns[2][name]=&columns[2][searchable]=false&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&start='.$start.'&length='.$step.'&search[value]=&search[regex]=false&_dt=dt'), true)['recordsFiltered'];
    for ($page = $start; $page <= $limit; $page += $step) {
        //Interprets the data
        $laws = json_decode(file_get_contents('https://laws.moj.gov.jm/library/statutes/revised?draw=2&columns[0][data]=shortTitle&columns[0][name]=&columns[0][searchable]=true&columns[0][orderable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=legalAreas&columns[1][name]=&columns[1][searchable]=false&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=actions&columns[2][name]=&columns[2][searchable]=false&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&start='.$page.'&length='.$step.'&search[value]=&search[regex]=false&_dt=dt'), true)['data'];// echo json_encode($laws).'<br/>';
        foreach ($laws as $law) {
            //Interprets the data
            $ID = $scraper.':'.$law['DT_RowId'];
            $name = fixQuotes($title_dom->load($law['shortTitle'])->find('a', 0)->plaintext, 'en');
            //Sets the regime
            $regime = strtotime($enactDate) < strtotime('6 August 1962') ? '{"en":"The Crown Colony of Jamaica"}' : '{"en":"Jamaica"}';
            $topic = '{"en":"'.$law['legalAreas'].'"}';
            $source = 'https://laws.moj.gov.jm'.$title_dom->load($law['shortTitle'])->find('a', 0)->href;

            //JSONifies the title and source
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `topic`, `source`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$topic."', '".$source."')"; echo $SQL2.'<br/>';
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