<?php //The Marshall Islands
    //Settings
    $test = false; $scraper = 'MH';

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';

    //Opens my scraper library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Helps resolve the types of specific laws
    $lawTypes = array(
        'Act'=>'Act',
        'EUADA'=>'Act',
        'Facilitation'=>'Act',
        'Fund'=>'Act',
        'Ministries'=>'Act',
        'Nitijela'=>'Act',
        'Office'=>'Act',
        'RADA'=>'Act',
        'Code'=>'Code',
        'Islands'=>'Constitution',
        'Seamen'=>'Act',
        'Worker'=>'Act',
    );

    //Sets static variables
    $saveDate = date('Y-m-d'); $country = '["MH"]'; $status = 'Valid';
    $publisher = '{"en":"The Nitijeļā of the Marshall Islands", "mh":"Nitijelā eo an Marshall Islands"}';

    //Loops through the letters
    foreach(array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z') as $letter) {
        //Gets data
        $dom = file_get_html('https://www.rmiparliament.org/cms/legislation/acts-of-nitijela/by-alphabetical-order.html?submit4='.$letter);

        //Processes the data in the table
        $laws = $dom->find('table.table.table-bordered.table-hover.table-condensed', 0)->find('tbody.tablebg', 0)->find('tr.row0');
        foreach($laws as $law) {
            $vals = array(//Sets up the values for each law
                'Source'=>'',
                'PDF' => '',
                'Notes' => '',
                'ID' => '',
                'Title' => '',
                'Topic' => '',
                'Enactment Date' => '',
                'Enforcement Date' => ''
            );

            //Gets the datapoints from cells
            $cells = $law->find('td');
            for($cell = 2; $cell <= 6; $cell++) {
                $vals[array_keys($vals)[$cell]] = $cells[$cell]->innertext;
            }

            //Creates doms out of some of the values
            $titleDom = new simple_html_dom($vals['Title']);
            $notesDom = new simple_html_dom($vals['Notes']);
            $enactDom = new simple_html_dom($vals['Enactment Date']);

            //Finalizes the values
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($enactDom->find('div', 0)->{'data-bs-content'}));
            $ID = $scraper.':'.strtr(explode('<hr ', end(explode('Number: ', $notesDom->find('div', 0)->{'data-bs-content'})))[0], array(' '=>'-'));
            $name = fixQuotes(explode('&nbsp;[', $titleDom->find('a', 0)->plaintext)[0], 'en');
            //Sets the regime
            switch(true) {
                case strtotime('29 August 1885') < strtotime($enactDate) && strtotime($enactDate) < strtotime('28 June 1919'):
                    $regime = '{"en":"The German Empire", "mh":"Imweo an German"}';
                    break;
                case strtotime('28 June 1919') < strtotime($enactDate) && strtotime($enactDate) < strtotime('18 June 1947'):
                    $regime = '{"en":"The Empire of Japan", "mh":"Imweo an Japan"}';
                    break;
                case strtotime('18 June 1947') < strtotime($enactDate) && strtotime($enactDate) < strtotime('22 December 1990'):
                    $regime = '{"en":"The United States of America", "mh":"Amedka"}';
                    break;
                case strtotime('22 December 1990') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                    $regime = '{"en":"The Republic of the Marshall Islands", "mh":"Republic eo an Marshall Islands"}';
                    break;
            }
            //Gets the rest of the values
            $type = $lawTypes[end(explode(' ',  trim(strtr(preg_replace('/[^A-Za-z ]/', '', $name), array(' of'=>' ')))))];
            $summary = str_contains($notesDom->find('div', 0)->{'data-bs-content'}, "<hr class='notes'>") ? fixQuotes(explode("<hr class='notes'>", $notesDom->find('div', 0)->{'data-bs-content'})[1], 'en'):'NULL';
            $topic = explode('<br>', explode(' - ', $vals['Topic'])[1])[0];
            $source = $PDF = 'https://www.rmiparliament.org'.$titleDom->find('a', 0)->href;

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $summary = $summary !== 'NULL' ? '\'{"en":"'.$summary.'"}\'' : 'NULL';
            $topic = '{"en":"'.$topic.'"}';
            $source = '{"en":"'.$source.'"}';
            $PDF = '{"en":"'.$PDF.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`,`name`, `country`, `regime`, `publisher`, `type`, `topic`, `status`, `summary`, `source`, `PDF`)
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$topic."', '".$status."', ".$summary.", '".$source."', '".$PDF."')";

            //Executes the SQL
            echo $SQL2.'<br/>';
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