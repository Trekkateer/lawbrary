<html><body>
    <?php
        //Settings
        $test = true; $LBpage = 'IT';
        $start = 0;//Which page to start from
        $step = 20;//How many laws are on each page
        $limit = NULL;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}
        
        //Translates the types and origins
        $types = array(
            'COSTITUZIONE'                            => ['Constitution', NULL],

            'DECRETO'                                 => ['Decree', NULL],
            'DECRETO DEL PRESIDENTE DELLA REPUBBLICA' => ['Presidential Decree', '[{"it":"Il Presidente", "en":"The President"}]'],
            'DECRETO LUOGOTENENZIALE'                 => ['Lieutenential Decree', NULL],
            'DECRETO-LEGGE'                           => ['Decree-Law', '[{"it":"Parlamento", "en":"Parliament"}]'],
            'DECRETO LEGISLATIVO'                     => ['Legislative Decree', NULL],
            'DECRETO LEGISLATIVO DEL CAPO PROVVISORIO DELLA STATO' => ['Legislative Decree', '[{"it":"Il Capo dello Stato provvisorio", "en":"The Provisional Head of State"}]'],
            'DECRETO-LEGGE LUOGOTENENZIALE'           => ['Lieutenential Decree-Law', '[{"it":"Parlamento", "en":"Parliament"}]'],
            'DECRETO DEL CAPO PROVVISORIO DELLA STATO' => ['Decree', '[{"it":"Il Capo dello Stato provvisorio", "en":"The Provisional Head of State"}]'],
            'DECRETO LEGISLATIVO LUOGOTENENZIALE'     => ['Lieutenential Legislative Decree', NULL],
            'DECRETO MINISTERIALE'                    => ['Ministerial Decree', '[{"it":"Il Gabinetto dei Ministri", "en":"The Cabinent of Ministers"}]'],
            'DECRETO DEL PRESIDENTE DEL CONSIGLIO DEI MINISTRI' => ['Decree', '[{"it":"Il Presidente del Consiglio dei Ministri", "en":"The President of the Council of Ministers"}]'],
            'DECRETO LEGISLATIVO PRESIDENZIALE'       => ['Presidential Legislative Decree', '[{"it":"Il Presidente", "en":"The President"}]'],
            'DECRETO DEL DUCE DEL FASCISMO, CAPO DEL GOVERNO' => ['Decree', '[{"it":"Il Duce del Fascismo", "en":"The Duke of Fascism"}, {"it":"Il Capo del Governo", "en":"The Head of Government"}]'],
            'DECRETO PRESIDENZIALE'                   => ['Presidential Decree', '[{"it":"Il Presidente", "en":"The President"}]'],
            'DECRETO REALE'                           => ['Royal Decree', '[{"it":"Il Monarca", "en":"The Monarch"}]',],
            'DECRETO DEL DUCE'                        => ['Decree', '[{"it":"Il Duce", "en":"The Duke"}]'],
            'DECRETO DEL CAPO DEL GOVERNO, PRIMO MINISTRO SEGRITARIO DI STATO' => ['Decree', '[{"it":"Il Capo del Governo", "en":"The Head of Government"}, {"it":"Primo ministro segritario di Stato", "en":"Prime Minister Secretary of State"}]'],
            'DECRETO DEL CAPO DEL GOVERNO, PRIMO MINISTRO SEGRETARIO DI STATO' => ['Decree', '[{"it":"Il Capo del Governo", "en":"The Head of Government"}, {"it":"Primo ministro segritario di Stato", "en":"Prime Minister Secretary of State"}]'],
            'DECRETO DEL CAPO DEL GOVERNO'            => ['Decree', '[{"it":"Capo del Governo", "en":"The Head of Government"}]'],
            'DECRETO LEGISLATIVO DEL CAPO PROVVISORIO DELLO STATO' => ['Legislative Decree', '[{"it":"Il Capo provvisorio dello Stato", "en":"The Provisional Head of State"}]'],
            'DECRETO DEL CAPO PROVVISORIO DELLO STATO' => ['Decree', '[{"it":"Il Capo provvisorio dello Stato", "en":"The Provisional Head of State"}]'],

            'DELIBERAZIONE'                           => ['Deliberation', NULL],

            'DETERMINAZIONE INTERCOMMISSARIALE'       => ['Determination', NULL],
            'DETERMINAZIONE DEL COMMISSARIO PER LA PRODUZIONE BELLICA' => ['Determination', '[{"it":"Il Commissario alla produzione bellica", "en":"The Commissioner of War Production"}]'],
            'DETERMINAZIONE DEL COMMISSARIO PER LE FINANZE' => ['Determination', '[{"it":"Il Commissario delle Finanze", "en":"The Commissioner of Finance"}]'],

            'LEGGE'                                   => ['Law', '[{"it":"Parlamento", "en":"Parliament"}]'],
            'LEGGE COSTITUZIONALE'                    => ['Constitutional Law', '[{"it":"Parlamento", "en":"Parliament"}]'],

            'ORDINANZA'                               => ['Ordinance', NULL],

            'REGIO DECRETO'                           => ['Royal Decree', '[{"it":"Il Monarca", "en":"The Monarch"}]'],
            'REGIO DECRETO-LEGGE'                     => ['Royal Decree-Law', '[{"it":"Parlamento", "en":"Parliament"}]'],
            'REGIO DECRETO LEGISLATIVO'               => ['Royal Legislative Decree', '[{"it":"Il Monarca", "en":"The Monarch"}]'],
           
            'REGOLAMENTO'                             => ['Regulation', NULL],
        );

        //Gets the limit
        $html_dom = file_get_html('https://www.normattiva.it/ricerca/avanzata/0?language=en');
        $limit = $limit ?? ceil((int)trim(str_replace(',', '', explode('records', $html_dom->find('div[class="col-12 box paginatore_info py-2"]')[0]->plaintext)[0]))/$step)-1;
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the HTML
            $html_dom = file_get_html('https://www.normattiva.it/ricerca/avanzata/'.$page.'?language=en');// echo $html_dom;

            //Processes the data in the table
            $laws = $html_dom->find('div[class="collapse-div boxAtto px-3 pt-3"]');
            foreach ($laws as $law) {echo '<br/>';
                //Gets values
                $source = trim('https://www.normattiva.it'.$law->find('div.collapse-header')[0]->find('p')[0]->find('a')[0]->href);
                    parse_str(parse_url($source)['query'], $params);
                $ID = $LBpage.':'.$params['atto_codiceRedazionale'];
                $name = str_replace(' ((. . .))', '', trim($law->find('p')[1]->plaintext, ' [].'));
                //Gets the date and type
                foreach (explode(' ', $law->find('p')[0]->find('a')[0]->plaintext) as $partNum => $part) {
                    if (ctype_digit($part)) {
                        $type = trim(explode($part, $law->find('p')[0]->find('a')[0]->plaintext)[0]);
                        $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(explode(', ', explode($type, $law->find('p')[0]->find('a')[0]->plaintext)[1])[0]));
                        break;
                    }
                }
                //Gets the country and regime
                $country = '["IT"]';
                switch(true) {
                    case strtotime('17 March 1861') < strtotime($enactDate) && strtotime($enactDate) < strtotime('12 June 1946'):
                        $regime = '{"en":"The Kingdom of Italy"}';
                        break;
                   case strtotime('12 June 1946') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                        $regime = '{"en":"The Republic of Italy"}';
                        break;
                }
                //Gets the rest of the values
                $origin = isset($types[$type][1]) ? "'".$types[$type][1]."'":'NULL';
                $type = $types[$type][0];//TODO: Find a way to tell if it's an amendment and what it amends
                $status = "Valid";

                //Makes sure there are no quotes in the title
                $name = strtr($name, array(' "'=>' “', '"'=>'”', "'"=>"’"));

                //JSONifies the values
                $name = '{"it":"'.$name.'"}';
                $source = '{"it":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `status`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$type."', '".$status."', '".$source."')";
                echo 'p. '.$page.': '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
        
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$LBpage."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>