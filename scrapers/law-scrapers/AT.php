<html><body>
    <?php //To many different types and origins
        //Settings
        $test = true; $country = 'AT';
        $start = 1;//Which law to start from
        $step = 100;//How many laws there are on the page
        $limit = null;//Total number of laws desired.

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

        //Sets the old ID
        $lastID = null;

        //Gets the limit
        $html_dom = file_get_html('https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Kundmachungsorgan=&Index=&Titel=&Gesetzesnummer=&VonArtikel=&BisArtikel=&VonParagraf=&BisParagraf=&VonAnlage=&BisAnlage=&Typ=&Kundmachungsnummer=&Unterzeichnungsdatum=&FassungVom='.date('d-m-Y').'&VonInkrafttretedatum=&BisInkrafttretedatum=&VonAusserkrafttretedatum=&BisAusserkrafttretedatum=&NormabschnittnummerKombination=Und&ImRisSeitVonDatum=&ImRisSeitBisDatum=&ImRisSeit=Undefined&ResultPageSize='.$step.'&Suchworte=&Position='.$start.'&Sort=1%7cDesc');
        $limit = $limit ?? explode(' von ', $html_dom->find('span.NumberOfDocuments')[0]->plaintext)[1];
        //Gets the laws
        for ($page = $start; $page <= $limit; $page += $step) {
            //Gets the data from congress.gov API
            $html_dom = file_get_html('https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Kundmachungsorgan=&Index=&Titel=&Gesetzesnummer=&VonArtikel=&BisArtikel=&VonParagraf=&BisParagraf=&VonAnlage=&BisAnlage=&Typ=&Kundmachungsnummer=&Unterzeichnungsdatum=&FassungVom='.date('d-m-Y').'&VonInkrafttretedatum=&BisInkrafttretedatum=&VonAusserkrafttretedatum=&BisAusserkrafttretedatum=&NormabschnittnummerKombination=Und&ImRisSeitVonDatum=&ImRisSeitBisDatum=&ImRisSeit=Undefined&ResultPageSize='.$step.'&Suchworte=&Position='.$page.'&Sort=1%7cDesc');
            $laws = $html_dom->find('table.bocListTable')[0]->find('tbody.bocListTableBody')[0]->find('tr.bocListDataRow');
            foreach ($laws as $law) {
                if ($lastID !== $country.'-'.explode('Gesetzesnummer=', $law->find('td.bocListDataCell')[7]->find('a')[0]->href)[1]) {
                    //Gets the values
                    $ID = $lastID = $country.'-'.explode('Gesetzesnummer=', $law->find('td.bocListDataCell')[7]->find('a')[0]->href)[1];
                    $enactDate = date('Y-m-d', strtotime($law->find('td.bocListDataCell')[3]->plaintext)); $enforceDate = $enactDate; $lastactDate = $enactDate;
                        if (trim($law->find('td.bocListDataCell')[4]->plaintext) !== '&nbsp;') {$endDate = "'".date('Y-m-d', strtotime($law->find('td.bocListDataCell')[4]->plaintext))."'";} else {$endDate = 'NULL';}
                    $name = trim($law->find('td.bocListDataCell')[5]->plaintext);

                    //Gets the regime
                    switch(true) {
                        case strtotime('17 September 1156') < strtotime($enactDate) && strtotime($enactDate) < strtotime('11 August 1804'):
                            $regime = 'The Duchy of Austria';
                            break;
                        case strtotime('11 August 1804') < strtotime($enactDate) && strtotime($enactDate) < strtotime('30 March 1867'):
                            $regime = 'The Austrian Empire';
                            break;
                        case strtotime('30 March 1867') < strtotime($enactDate) && strtotime($enactDate) < strtotime('10 September 1919'):
                            $regime = 'The Austro-Hungarian Empire';
                            break;
                        case strtotime('10 September 1919') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 May 1934'):
                            $regime = 'The First Austrian Republic';
                            break;
                        case strtotime('1 May 1934') < strtotime($enactDate) && strtotime($enactDate) < strtotime('13 March 1938'):
                            $regime = 'The Federal State of Austria';
                            break;
                        case strtotime('13 March 1938') < strtotime($enactDate) && strtotime($enactDate) < strtotime('27 April 1945'):
                            $regime = 'Anschluss Austria';
                            break;
                        case strtotime('27 April 1945') < strtotime($enactDate) && strtotime($enactDate) < strtotime(date('d M Y')):
                            $regime = 'The Second Austrian Republic';
                            break;
                    }

                    //Gets the rest of the values
                    $type = 'Law'; $status = 'Valid';
                    $source = 'https://www.ris.bka.gv.at'.$law->find('td.bocListDataCell')[7]->find('a')[0]->href;

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                    //JSONifies the title and source
                    $name = '{"de":"'.$name.'"}';
                    $source = '{"de":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `endDate`, `ID`, `name`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', ".$endDate.", '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }

        //Connects to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username, $password, $database);
        $conn2->select_db($database) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>