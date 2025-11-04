<?php //TODO: Find a way to get more than just court documents
    //Settings
    $test = false; $scraper = 'MV';
    $start = 1;//Which page to start from
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

    //Translates the months
    $months = array(
        'ޖެނުއަރީ'=>'-01-',
        'ފެބްރުއަރީ'=>'-02-',
        'މާރޗް'=>'-03-',
        'އޭޕްރިލް'=>'-04-',
        'މެއި'=>'-05-',
        'ޖޫން'=>'-06-',
        'ޖުލައި'=>'-07-',
        'އޯގަސްޓް'=>'-08-',
        'ސެޕްޓެމްބަރ'=>'-09-',
        'އޮކްޓޯބަރ'=>'-10-',
        'ނޮވެމްބަރ'=>'-11-',
        'ޑިސެމްބަރ'=>'-12-'
    );
    //Translates the types
    $types = array(
        'Act'=>'Act',
        'އިސްތިޝާރީ ލަފާ'=>'Preferential Advice',
        'ޙުކުމް'=>'Court Decision',
        'ޢާއްމުސިޓީ'=>'Municipal Decision',
        'އަމުރު'=>'Order',
        'ޤަރާރު'=>'Resolution',
        'ރޫލިންގްސް'=>'Ruling'
    );
    //Translates the institutions
    $origins = array(
        'NULL'=>'NULL',
        'ސުޕްރީމް ކޯޓު'=>'\'The Supreme Court\'',
        'ހައިކޯޓު'=>'\'High Court\'',
        'ސިވިލް ކޯޓް'=>'\'Civil Court\'',
    );

    //Sets the Static Variables
    $country = '["MV"]'; $saveDate = date('Y-m-d');
    $publisher = '{"en":"archive.mv"}';

    //Gets the court documents
    //Gets the limit
    $dom = file_get_html('https://legalarchive.mv/legal-documents');
    $limit = $limit ?? $dom->find('div.container.pt-5', 0)->find('ul.pagination.justify-content-center', 0)->find('li.page-item')[11]->plaintext;
    //Loops through the pages
    for ($page = $start; $page <= $limit; $page++) {echo '<br/>Page '.$page.'<br/>';
        //Gets the HTML
        $dom = file_get_html('https://legalarchive.mv/legal-documents?page='.$page);

        //Processes the data in the table
        $laws = $dom->find('div.card.mb-5.shadow-sm.legal-document-card div.card-body div.row');
        foreach ($laws as $law) {
            //Gets values
            $datesplode = explode(' ', strtr($law->find('div.col-md-3.my-auto.meta', 0)->find('p.published-at', 0)->plaintext, array('‫'=>'')));
                $enactDate = $enforceDate = $lastactDate = $datesplode[2].$months[$datesplode[1]].$datesplode[0];
            $ID = $scraper.':'.explode('/', $law->find('div.col-md-9', 0)->find('a.text-decoration-none', 0)->href)[4];
            //Gets regime
            switch(true) {
                case strtotime($enactDate) < strtotime('26 July 1965'):
                    $regime = '{"en":"The British Protectorate of the Maldives"}';
                    break;
                case strtotime('26 July 1965') < strtotime($enactDate) && strtotime($enactDate) < strtotime('11 November 1968'):
                    $regime = '{"en":"The Sultanate of the Maldives"}';
                    break;
                case strtotime('11 November 1968') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                    $regime = '{"en":"The Republic of the Maldives"}';
                    break;
            }
            //Gets the rest of the values
            $name = fixQuotes(trim($law->find('div.col-md-9', 0)->find('a.text-decoration-none', 0)->plaintext), 'dv');
            $type = $types[trim($law->find('div.col-md-3.my-auto.meta', 0)->find('div.category a', 0)->plaintext ?? 'Act')];
            $status = 'Valid';
            $origin = $origins[trim($law->find('div.col-md-3.my-auto.meta', 0)->find('a.text-decoration-none', 0)->plaintext ?? 'NULL')];
            $source = $law->find('div.col-md-9', 0)->find('a.text-decoration-none', 0)->href;

            //JSONifies the values
            $name = '{"dv":"'.$name.'"}';
            $source = '{"dv":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `origin`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', ".$origin.", '".$source."')"; echo $SQL2.'<br/>';
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