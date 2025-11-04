<?php //Austria
    //TODO: Find a way to get the type of law and origin thereof
    //Settings
    $test = false; $scraper = 'AT';
    $start = 1;//Which law to start from
    $step = 100;//How many laws there are on the page
    $limit = null;//Total number of laws desired.

    //Opens my library
    require '../skrapateer.php';

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Sets the jurisdictions and clears their tables
    $jurisdictions = array('Bundesnormen'=>'AT', 'Burgenland'=>'AT-1', 'Kärnten'=>'AT-2', 'Niederösterreich'=>'AT-3', 'Oberösterreich'=>'AT-4', 'Salzburg'=>'AT-5', 'Steiermark'=>'AT-6', 'Tirol'=>'AT-7', 'Vorarlberg'=>'AT-8', 'Wien'=>'AT-9');
    foreach ($jurisdictions as $jurisdiction => $ID) {
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($ID)."`"; echo $SQL1.'<br/>';
        if (!$test) {$conn->query($SQL1);}
    } echo '<br/>';
    
    //Sets the old ID
    $lastID = null;

    //Sets up the querying function
    $HTTP_Call = function($offset, $searchParams) use ($step) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.ris.bka.gv.at/Ergebnis.wxe?'.$searchParams.'&Index=&Titel=&Gesetzesnummer=&VonArtikel=&BisArtikel=&VonParagraf=&BisParagraf=&VonAnlage=&BisAnlage=&Typ=&Kundmachungsnummer=&Unterzeichnungsdatum=&FassungVom='.date('d.m.Y').'&VonInkrafttretedatum=&BisInkrafttretedatum=&VonAusserkrafttretedatum=&BisAusserkrafttretedatum=&NormabschnittnummerKombination=Und&ImRisSeitVonDatum=&ImRisSeitBisDatum=&ImRisSeit=Undefined&ResultPageSize='.$step.'&Suchworte=&Position='.$offset.'&Sort=1%7cDesc',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: RIS_Cookie=ffffffff09c20f1245525d5f4f58455e445a4a423660'
            ),
        ));
        
        $response = curl_exec($curl); curl_close($curl);
        return $response;
    };

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["AT"]'; $type = 'Law'; $status = 'Valid';
    $publisher = '{"de":"Rechtsinformationssystem des Bundeskanzleramt Österreich", "en":"The Legal Information System of the Federal Chancellery of Austria"}';

    //Loops through the jurisdictions
    foreach ($jurisdictions as $jurisdiction => $LBpage) {
        //Sets the beginning of the URL
        if ($jurisdiction === 'Bundesnormen') {
            $searchParams = 'Abfrage=Bundesnormen&Kundmachungsorgan=';
        } else {$searchParams = 'Abfrage=Landesnormen&Kundmachungsorgan=&Bundesland='.$jurisdiction.'&BundeslandDefault='.$jurisdiction;}

        //Gets the limit
        $dom = new simple_html_dom($HTTP_Call($start, $searchParams));
        $limit = $limit ?? trim(explode(' von ', $dom->find('span.NumberOfDocuments', 0)->plaintext)[1], '.');
        //Gets the laws
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the data from congress.gov API
            $dom->load($HTTP_Call($offset, $searchParams));
            $laws = $dom->find('table.bocListTable', 0)->find('tbody.bocListTableBody', 0)->find('tr.bocListDataRow');
            foreach ($laws as $law) {
                //Makes sure the law is not the same as the one above
                if ($lastID !== $LBpage.':'.explode('Gesetzesnummer=', $law->find('td.bocListDataCell', 7)->find('a', 0)->href)[1]) {
                    //Gets the values
                    $ID = $lastID = $LBpage.':'.explode('Gesetzesnummer=', $law->find('td.bocListDataCell', 7)->find('a', 0)->href)[1];
                    $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law->find('td.bocListDataCell', 3)->plaintext));
                        if (trim($law->find('td.bocListDataCell', 4)->plaintext) !== '&nbsp;') {$endDate = "'".date('Y-m-d', strtotime($law->find('td.bocListDataCell', 4)->plaintext))."'";} else {$endDate = 'NULL';}
                    $name = fixQuotes($law->find('td.bocListDataCell', 2)->plaintext.' '.trim($law->find('td.bocListDataCell', 5)->plaintext), 'de_'.$scraper);
                    //Gets the regime
                    switch(true) {
                        case strtotime($enactDate) <= strtotime('today'):
                            $regime = '{"de":"Zweite Republik Österreich", "en":"The Second Republic of Austria"}';
                            break;
                        case strtotime($enactDate) < strtotime('27 April 1945'):
                            $regime = '{"de":"Anschluß Österreichs", "en":"Anschluss Austria"}';
                            break;
                        case strtotime($enactDate) < strtotime('13 March 1938'):
                            $regime = '{"de":"Bundesstaat Österreich", "en":"The Federal State of Austria"}';
                            break;
                        case strtotime($enactDate) < strtotime('1 May 1934'):
                            $regime = '{"de":"Erste Österreichische Republik", "en":"The First Austrian Republic"}';
                            break;
                        case strtotime($enactDate) < strtotime('10 September 1919'):
                            $regime = '{"de":"Österreichisch-Ungarische Monarchie", "en":"The Austro-Hungarian Empire"}';
                            break;
                        case strtotime($enactDate) < strtotime('30 March 1867'):
                            $regime = '{"de":"Kaisertum Österreich", "en":"The Austrian Empire"}';
                            break;
                        case strtotime($enactDate) < strtotime('11 August 1804'):
                            $regime = '{"de":"Das Herzogtum Österreich", "en":"The Duchy of Austria"}';
                            break;
                    }
                    //Gets the rest of the values
                    $source = 'https://www.ris.bka.gv.at'.$law->find('td.bocListDataCell', 6)->find('span.nativeDocumentLinkCell', 0)->find('a', 0)->href;
                    $PDF = 'https://www.ris.bka.gv.at'.$law->find('td.bocListDataCell', 6)->find('span.nativeDocumentLinkCell', 0)->find('a', 2)->href;

                    //JSONifies the title and source
                    $name = '{"de":"'.$name.'"}';
                    $source = '{"de":"'.$source.'"}';
                    $PDF = '{"de":"'.$PDF.'"}';

                    //Creates SQL
                    if ($enactDate !== '&nbsp;') {
                        $SQL2 = "INSERT INTO `".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `endDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`, `PDF`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', ".$endDate.", '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
                        echo '<a href="https://www.ris.bka.gv.at/Ergebnis.wxe?'.$searchParams.'&Index=&Titel=&Gesetzesnummer=&VonArtikel=&BisArtikel=&VonParagraf=&BisParagraf=&VonAnlage=&BisAnlage=&Typ=&Kundmachungsnummer=&Unterzeichnungsdatum=&FassungVom='.date('d.m.Y').'&VonInkrafttretedatum=&BisInkrafttretedatum=&VonAusserkrafttretedatum=&BisAusserkrafttretedatum=&NormabschnittnummerKombination=Und&ImRisSeitVonDatum=&ImRisSeitBisDatum=&ImRisSeit=Undefined&ResultPageSize='.$step.'&Suchworte=&Position='.$offset.'&Sort=1%7cDesc" target="_blank">Off. '.$offset.'</a>: '.$SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
                }
            }
        }
    }

    //Updates the date on the countries and divisions tables
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    $SQL31 = "UPDATE `divisions` SET `lawsUpdated`='".$saveDate."' WHERE `parent`='".$scraper."'"; echo '<br/><br/>'.$SQL31;
    if (!$test) {$conn2->query($SQL3); $conn2->query($SQL31);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>