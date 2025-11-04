<?php //Hungary
    //Settings
    $test = false; $scraper = 'HU';
    $start = 1;//Which page to start from
    $step = 50;//How many laws there are on each page
    $limit = null;//How many pages there are

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Translates status into English
    $statuses = array(
        'ico now'=>'Valid',
        'ico past'=>'Invalid',
        'ico gazette'=>'Valid',
        'ico future'=>'Not Yet in Force'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["HU"]'; $type = 'Act'; $status = 'Valid';
    $publisher = '{"hu":"Magyarország Nemzeti Jogszabálytár","en":"The National Law Database of Hungary"}';

    //Gets the limit
    $dom = file_get_html('https://njt.hu/p/search/-:-:-:-:-:-:-:-:-:-:-/'.$start.'/'.$step);
    $limit = $limit ?? explode(' / ', $dom->find('#page-count', 0)->plaintext)[1];
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page += $step) {
        //Processes the data
        $dom = file_get_html('https://njt.hu/p/search/-:-:-:-:-:-:-:-:-:-:-/'.$page.'/'.$step);
        $laws = $dom->find('#results', 0)->find('div.resultItemWrapper');
        foreach ($laws as $law) {
            //Gets the values
            $enactDate = $enforceDate = strtr(explode('. –', $law->find('div.resultItem', 0)->find('span.resultDate', 0)->plaintext)[0], array('. '=>'-'));
            $lastactDate = strtr($law->find('div.resultItem', 0)->find('div[id^="timestatus"]', 0)->find('span.tsEl', 0)->find('a.version.orig', 0)->plaintext ?? $enactDate, array('.'=>'', ' '=>'-'));
            $ID = $scraper.':'.end(explode('/', str_replace('-', '', $law->find('div.resultItem', 0)->find('a', 0)->href)));
            $name = fixQuotes(array('hu'=>trim($law->find('div.resultItem', 0)->find('a', 0)->plaintext)));
            //Gets the regime
            switch(true) {
                case strtotime('895-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('25 December 1000'):
                    $regime = '{"hu":"A Magyar Hercegség", "en":"The Principality of Hungary"}';
                    break;
                case strtotime('25 December 1000') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 January 1301'):
                    $regime = '{"hu":"A korai Magyar Királyság", "en":"The Early Kingdom of Hungary"}';
                    break;
                case strtotime('14 January 1301') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 August 1526'):
                    $regime = '{"hu":"A későbbi Magyar Királyság", "en":"The Latter Kingdom of Hungary"}';
                    break;
                case strtotime('29 August 1526') < strtotime($enactDate) && strtotime($enactDate) < strtotime('30 March 1867'):
                    $regime = '{"hu":"A Habsburg Magyar Királyság", "en":"The Hapsburg Kingdom of Hungary"}';
                    break;
                case strtotime('30 March 1867') < strtotime($enactDate) && strtotime($enactDate) < strtotime('16 November 1918'):
                    $regime = '{"hu":"Az Osztrák-Magyar Birodalom", "en":"The Austro-Hungarian Empire"}';
                    break;
                case strtotime('16 November 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 February 1920'):
                    $regime = '{"hu":"Az első Magyar Köztársaság", "en":"The First Hungarian Republic"}';
                    break;
                case strtotime('29 February 1920') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 February 1946'):
                    $regime = '{"hu":"A két világháború közötti Magyar Királyság", "en":"The Interwar Kingdom of Hungary"}';
                    break;
                case strtotime('1 February 1946') < strtotime($enactDate) && strtotime($enactDate) < strtotime('20 August 1949'):
                    $regime = '{"hu":"A második Magyar Köztársaság", "en":"The Second Hungarian Republic"}';
                    break;
                case strtotime('20 August 1949') < strtotime($enactDate) && strtotime($enactDate) < strtotime('23 October 1989'):
                    $regime = '{"hu":"A Magyar Népköztársaság", "en":"The People’s Republic of Hungary"}';
                    break;
                case strtotime('23 October 1989') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                    $regime = '{"hu":"A Harmadik Magyar Köztársaság", "en":"The Third Hungarian Republic"}';
                    break;
            }
            //Gets the rest of the values
            $status = $statuses[$law->find('span', 0, 0)->class];
            $source = array('hu'=>'https://njt.hu/'.$law->find('div.resultItem', 0)->find('a', 0)->href);
            $PDF = 'NULL';

            //Gets PDF translations and JSONifies them, plus the name and source
            $translations = $law->find('div.resultItem', 0)->find('a.resultItem.translation') ?? NULL;
            if ($translations) {
                $PDF = array();
                foreach ($translations as $translation) {
                    $lang = explode('/', $translation->href)[2];
                    $name[$lang] = $translation->title;
                    $source[$lang] = 'https://njt.hu/'.$translation->href;
                    $PDF[$lang] = 'https://njt.hu'.$translation->href;
                }
                $PDF = "'".json_encode($PDF, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES)."'";
            }
            $name = json_encode($name, JSON_UNESCAPED_UNICODE);
            $source = json_encode($source, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES);

            //Creates and runs SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`)
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', ".$PDF.")";
            echo 'P'.$page.': '.$SQL2.'<br/>';
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

    //Closes the connection
    $conn->close(); $conn2->close();
?>