<html><body>
    <?php
        //Settings
        $test = true; $country = 'HU';
        $start = 1;//Which page to start from
        $step = 50;//How many laws there are on each page
        $limit = null;//How many pages there are

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

        //Translates status into English
        $statuses = array('ico now'=>'Valid',
                        'ico past'=>'Invalid',
                        'ico gazette'=>'Valid',
                        'ico future'=>'Not Yet in Force');

        //Gets the limit
        $html_dom = file_get_html('https://njt.hu/p/search/-:-:-:-:-:-:-:-:-:-:-/'.$start.'/'.$step);
        $limit = $limit ?? explode(' / ', $html_dom->find('#page-count')[0]->plaintext)[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page += $step) {
            //Processes the data
            $html_dom = file_get_html('https://njt.hu/p/search/-:-:-:-:-:-:-:-:-:-:-/'.$page.'/'.$step);
            $laws = $html_dom->find('#results')[0]->find('div.resultItemWrapper');
            foreach ($laws as $law) {
                //Gets the values
                $enactDate = strtr(explode('. –', $law->find('div.resultItem')[0]->find('span.resultDate')[0]->plaintext)[0], array('. '=>'-')); $enforceDate = $enactDate;
                $lastactDate = strtr($law->find('div.resultItem')[0]->find('div[id^="timestatus"]')[0]->find('span.tsEl')[0]->find('a.version.orig')[0]->plaintext ?? $enactDate, array('.'=>'', ' '=>'-'));
                $ID = $country.'-'.end(explode('/', str_replace('-', '', $law->find('div.resultItem')[0]->find('a')[0]->href)));
                $name = array('hu'=>trim($law->find('div.resultItem')[0]->find('a')[0]->plaintext));
                //Gets the regime
                switch(true) {
                    case strtotime('895-01-01') < strtotime($enactDate) && strtotime($enactDate) < strtotime('25 December 1000'):
                        $regime = 'The Principality of Hungary';
                        break;
                    case strtotime('25 December 1000') < strtotime($enactDate) && strtotime($enactDate) < strtotime('14 January 1301'):
                        $regime = 'The Early Kingdom of Hungary';
                        break;
                    case strtotime('14 January 1301') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 August 1526'):
                        $regime = 'The Later Kingdom of Hungary';
                        break;
                    case strtotime('29 August 1526') < strtotime($enactDate) && strtotime($enactDate) < strtotime('30 March 1867'):
                        $regime = 'The Hapsburg Kingdom of Hungary';
                        break;
                    case strtotime('30 March 1867') < strtotime($enactDate) && strtotime($enactDate) < strtotime('16 November 1918'):
                        $regime = 'The Austro-Hungarian Empire';
                        break;
                    case strtotime('16 November 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 February 1920'):
                        $regime = 'The First Hungarian Republic';
                        break;
                    case strtotime('29 February 1920') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 February 1946'):
                        $regime = 'The Interwar Kingdom of Hungary';
                        break;
                    case strtotime('1 February 1946') < strtotime($enactDate) && strtotime($enactDate) < strtotime('20 August 1949'):
                        $regime = 'The Second Hungarian Republic';
                        break;
                    case strtotime('20 August 1949') < strtotime($enactDate) && strtotime($enactDate) < strtotime('23 October 1989'):
                        $regime = 'The People’s Republic of Hungary';
                        break;
                    case strtotime('23 October 1989') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                        $regime = 'The Third Hungarian Republic';
                        break;
                }
                //Gets the rest of the values
                $type = 'Law';
                $status = $statuses[$law->find('span')[0]->class];
                $source = array('hu'=>'https://njt.hu/'.$law->find('div.resultItem')[0]->find('a')[0]->href);
                $PDF = 'NULL';

                //Gets translations
                $translations = $law->find('div.resultItem')[0]->find('a.resultItem.translation') ?? NULL;
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

                //Makes sure there are no appostophes in the title
                $name = str_replace("'", "’", $name);

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `status`, `source`, `PDF`)
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."', '".$source."', ".$PDF.")";

                //Executes the SQL
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>