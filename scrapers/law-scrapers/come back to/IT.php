<html><body>
    <?php //Max execution time exceded. Need to fix getOrigin
        //Settings
        $test = false; $country = 'IT';
        $start = 0;//Which page to start from
        $limit = null;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Preloop functions
        function capitalize_after_delimiters($string='', $delimiters=array(' ')) {
            foreach ($delimiters as $delimiter) {
                $temp = explode($delimiter, $string);
                    array_walk($temp, function (&$value) {
                        $value = strtolower($value);
                        if ($value !== 'of' && $value !== 'and' && $value !== 'the' && $value !== 'del' && $value !== 'della' && $value !== 'dello' && $value !== 'la' && $value !== 'le' && $value !== 'di' && $value !== 'dei' && $value !== 'per') {
                            $value = ucfirst($value);
                        }
                    });
                $string = implode($delimiter, $temp);
            }
            return $string;
        }
        
        //Translates the type into English
        $types = array('Regio Decreto'=>'Royal Decree',
                        'Decreto del Presidente della Repubblica'=>'Presidential Decree',
                        'Legge'=>'Law',
                        'Regio Decreto-legge'=>'Royal Decree-Law',
                        'Decreto Luogotenenziale'=>'Lieutenential Decree',
                        'Decreto-legge'=>'Decree-Law',
                        'Decreto Legislativo'=>'Legislative Decree',
                        'Decreto'=>'Decree',
                        'Decreto Legislativo del Capo Provvisorio dello Stato'=>'Legislative Decree',
                        'Decreto-legge Luogotenenziale'=>'Lieutenential Decree-Law',
                        'Decreto del Capo Provvisorio dello Stato'=>'Decree',
                        'Decreto Legislativo Luogotenenziale'=>'Lieutenential Legislative Decree',
                        'Decreto Ministeriale'=>'Ministerial Decree',
                        'Decreto del Presidente del Consiglio dei Ministri'=>'Decree',
                        'Regio Decreto Legislativo'=>'Royal Legislative Decree',
                        'Decreto Legislativo Presidenziale'=>'Presidential Legislative Decree',
                        'Legge Costituzionale'=>'Constitutional Law',
                        'Decreto del Duce del Fascismo, Capo del Governo'=>'Decree',
                        'Ordinanza'=>'Ordinance',
                        'Decreto del Capo del Governo'=>'Decree',
                        'Deliberazione'=>'Deliberation',
                        'Decreto Presidenziale'=>'Presidential Decree',
                        'Decreto REALE'=>'Royal Decree',
                        'Decreto del Duce'=>'Decree',
                        'Decreto del Capo del Governo, Primo Ministro Segritario di Stato'=>'Decree',
                        'Determinazione Intercommissariale'=>'Inter-Commissioner Determination',
                        'Costituzione'=>'Constitution',
                        'Regolamento'=>'Regulation',
                        'Determinazione del Commissario per la Produzione Bellica'=>'Determination',
                        'Determinazione del Commissario per le Finanze'=>'Determination');

        //Gets the origins
        $getOrigin = function($type, $regime) {
            //Defines the array
            $origins = array('Regio Decreto'=>'The Monarch',
                'Decreto del Presidente della Repubblica'=>'The President of the Republic',
                'Legge'=>'The Parliament',
                'Regio Decreto-legge'=>'The Parliament',
                'Decreto Luogotenenziale'=>$regime,
                'Decreto-legge'=>'The Parliament',
                'Decreto Legislativo'=>$regime,
                'Decreto'=>$regime,
                'Decreto Legislativo del Capo Provvisorio dello Stato'=>'The Provisional Head of State',
                'Decreto-legge Luogotenenziale'=>'The Parliament',
                'Decreto del Capo Provvisorio dello Stato'=>'The Provisional Head of State',
                'Decreto Legislativo Luogotenenziale'=>$regime,
                'Decreto Ministeriale'=>'The Cabinent of Ministers',
                'Decreto del Presidente del Consiglio dei Ministri'=>'The President of the Council of Ministers',
                'Regio Decreto Legislativo'=>'The Monarch',
                'Decreto Legislativo Presidenziale'=>'The President',
                'Legge Costituzionale'=>'The Parliament',
                'Decreto del Duce del Fascismo, Capo del Governo'=>'The Duce of Fascism, Head of Government',
                'Ordinanza'=>$regime,
                'Decreto del Capo del Governo'=>'The Head of Government',
                'Deliberazione'=>$regime,
                'Decreto Presidenziale'=>'The President',
                'Decreto REALE'=>'The Monarch',
                'Decreto del Duce'=>'The Duce',
                'Decreto del Capo del Governo, Primo Ministro Segritario di Stato'=>'The Head of Government, Prime Minister Secretary of State',
                'Determinazione Intercommissariale'=>$regime,
                'Costituzione'=>$regime,
                'Regolamento'=>$regime,
                'Determinazione del Commissario per la Produzione Bellica'=>'The Commissioner for War Production',
                'Determinazione del Commissario per le Finanze'=>'The Commissioner for Finance'
            );

            //Returns value
            return $origins[$type];
        };

        //Gets the limit
        $html_dom = file_get_html('https://www.normattiva.it/ricerca/avanzata/0?language=en');
        $limit = $limit ?? ceil((int)trim(str_replace(',', '', explode('records', $html_dom->find('div[class="col-12 box paginatore_info py-2"]')[0]->innertext)[0]))/20)-1;
        //Loops through the pages
        for ($page = $start; $page < $limit; $page++) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.normattiva.it/ricerca/avanzata/'.$page.'?language=en');// echo $html_dom;

            //Processes the data in the table
            $body_rows = $html_dom->find('div[class="collapse-div boxAtto px-3 pt-3"]');
            foreach ($body_rows as $body_row) {
                //Gets values
                $source = 'https://www.normattiva.it'.$body_row->find('p')[0]->find('a')[0]->href;
                    parse_str(parse_url($source)['query'], $params);
                $ID = $country.'-'.$params['atto_codiceRedazionale'];
                $name = trim(explode('[', explode('. (', explode(']', $body_row->find('p')[1]->innertext)[0])[0])[1]);
                foreach (explode(' ', explode(', ', $body_row->find('p')[0]->find('a')[0]->innertext)[0]) as $partNum => $part) {//Gets the date
                    if (ctype_digit($part)) {
                        $type = capitalize_after_delimiters(trim(explode($part, $body_row->find('p')[0]->find('a')[0]->innertext)[0]));
                        $enactDate = date('Y-m-d', strtotime(explode(strtoupper($type), explode(', ', $body_row->find('p')[0]->find('a')[0]->innertext)[0])[1])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                        break;
                    }
                }
                //Gets the regime
                switch(true) {
                    case strtotime('17 March 1861') < strtotime($enactDate) && strtotime($enactDate) < strtotime('12 June 1946'):
                        $regime = 'The Kingdom of Italy';
                        break;
                   case strtotime('12 June 1946') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                        $regime = 'The Republic of Italy';
                        break;
                }
                //Gets the rest of the values
                $origin = $getOrigin($type, $regime); $type = $types[$type];
                $status = "Valid";

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"it":"'.$name.'"}';
                $source = '{"it":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `origin`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$origin."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test && !str_contains($name, '((') && !str_contains($ID, '[')) {$conn->query($SQL2);}
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