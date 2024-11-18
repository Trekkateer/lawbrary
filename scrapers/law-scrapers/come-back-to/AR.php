<html>
<head>
    <?php //Some functions we will need
        function console_log($output, $with_script_tags = true) {
            $js_code = 'console.log('.json_encode($output, JSON_HEX_TAG).');';
            if ($with_script_tags) {
                $js_code = '<script>'.$js_code.'</script>';
            }
            echo $js_code;
        }
        function redirect($destination) {//Redirects to another page on the domain
            exit('<script>window.location.replace("'.$destination.'");</script>');
        }
    ?>
</head>
<body>
    <?php //!!Not all laws have valid name
        //Settings
        $test = true; $country = 'AR';
        $start = 0;//Which law to start from
        $step = 1000;//How many laws per page
        $limit = null;//Total number of laws desired. 

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the provinces
        $provinces = array(
            'AR'=>'AR',
            ''=>'AR',
            'Buenos Aires'=>'AR-B',
            'AR-B'=>'AR-B',
            'Ciudad Autónoma de Buenos Aires'=>'AR-C',
            'AR-C'=>'AR-C',
            'Catamarca'=>'AR-K',
            'AR-K'=>'AR-K',
            'Chaco'=>'AR-H',
            'AR-H'=>'AR-H',
            'Chubut'=>'AR-U',
            'AR-U'=>'AR-U',
            'Córdoba'=>'AR-X',
            'AR-X'=>'AR-X',
            'Corrientes'=>'AR-W',
            'AR-W'=>'AR-W',
            'Entre Ríos'=>'AR-E',
            'AR-E'=>'AR-E',
            'Formosa'=>'AR-P',
            'AR-P'=>'AR-P',
            'Jujuy'=>'AR-Y',
            'AR-Y'=>'AR-Y',
            'La Pampa'=>'AR-L',
            'AR-L'=>'AR-L',
            'La Rioja'=>'AR-F',
            'AR-F'=>'AR-F',
            'Mendoza'=>'AR-M',
            'AR-M'=>'AR-M',
            'Misiones'=>'AR-N',
            'AR-N'=>'AR-N',
            'Neuquén'=>'AR-Q',
            'AR-Q'=>'AR-Q',
            'Río Negro'=>'AR-R',
            'AR-R'=>'AR-R',
            'Salta'=>'AR-A',
            'AR-A'=>'AR-A',
            'San Juan'=>'AR-J',
            'AR-J'=>'AR-J',
            'San Luis'=>'AR-D',
            'AR-D'=>'AR-D',
            'Santa Cruz'=>'AR-Z',
            'AR-Z'=>'AR-Z',
            'Santa Fe'=>'AR-S',
            'AR-S'=>'AR-S',
            'Santiago del Estero'=>'AR-G',
            'AR-G'=>'AR-G',
            'Tierra del Fuego'=>'AR-V',
            'AR-V'=>'AR-V',
            'Tucumán'=>'AR-T',
            'AR-T'=>'AR-T',
        );
        //Gets the types
        $types = array(
            'ACO'=>'Agreement',
            'CCC'=>'Code',
            'CCN'=>'Code',
            'CEL'=>'Code',
            'CFP'=>'Code',
            'CMI'=>'Code',
            'CPC'=>'Code',
            'CPM'=>'Code',
            'CPP'=>'Code',
            'CPT'=>'Code',
            'CON'=>'Constitution',
            'DEC'=>'Decree',
            'DNU'=>'Emergency Decree',
            'DLE'=>'Legal Decree',
            'DOR'=>'Ordinal Decree',
            'DAN'=>'Decision',
            'NJF'=>'De Facto Legal Norm',
            'DIS'=>'Disposition',
            'LEY'=>'Law',
            'RES'=>'Resolution',
            'RSC'=>'Resolution',
            'TOR'=>'Text Order',
            'TOD'=>'Text Order',
            'TRA'=>'Treaty'
        );
        //Gets the statuses
        $statuses = array(
            'Vigente, de alcance general'=>'In Force',
            'Individual, Solo Modificatoria o Sin Eficacia'=>'In Force',
            'Derogada'=>'Repealed',
            'Vetada'=>'Vetoed',
            'A'=>'Authorization'
        );
        //Gets the origins !!Needs to be finished
        $origins = array(
            'AFIP'=>'{"es":"Administración Federal de Ingresos Públicos", "en":"The Federal Public Revenue Administration"}',
            'JGM'=>'{"es":"Jefatura de Gabinete de Ministros", "en":"The Chief of the Cabinet of Ministers"}',
        );
        
        //Gets the laws
        $limit = $limit ?? json_decode(file_get_contents('http://www.saij.gob.ar/busqueda?o='.$start.'&p='.$step.'&f=Total%7CTipo+de+Documento%2FLegislaci%C3%B3n%7CFecha%7COrganismo%7CPublicaci%C3%B3n%7CTema%7CEstado+de+Vigencia%7CAutor%7CJurisdicci%C3%B3n&s=&v=colapsada'), true)['searchResults']['categoriesResultList'][0]['facetChildren'][0]['facetHits'];
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the data from legislation.gov API
            $laws = json_decode(file_get_contents('http://www.saij.gob.ar/busqueda?o='.$offset.'&p='.$step.'&f=Total%7CTipo+de+Documento%2FLegislaci%C3%B3n%7CFecha%7COrganismo%7CPublicaci%C3%B3n%7CTema%7CEstado+de+Vigencia%7CAutor%7CJurisdicci%C3%B3n&s=&v=colapsada'), true)['searchResults']['documentResultList'];
            foreach ($laws as $law) {
                //Decodes the data
                $data = json_decode($law['documentAbstract'], true); console_log($data);

                //Gets the province
                $country = $provinces[$data['document']['content']['provincia'] ?? $country];

                //Interprets the data
                $enforceDate = $data['document']['content']['fecha']; $enactDate = $enforceDate; $lastactDate = $enactDate;
                $ID = $country.'-'.($data['document']['content']['mecanografico'] ?? $data['document']['content']['id-infojus']);
                    echo 'ID: '.$ID.'; ';
                $regime = 'The Argentine Republic';
                $name = $data['document']['content']['titulo-norma'] ?? $data['document']['content']['titulo_1'] ?? $data['document']['content']['titulo_noticia'] ?? $data['document']['content']['nombre-coloquial'];
                $type = $types[$data['document']['content']['tipo-norma']['codigo']];
                    echo 'Type: '.$data['document']['content']['tipo-norma']['codigo'].', '.$data['document']['content']['tipo-norma']['texto'].', '.$type.'<br/>';
                    if (str_contains('Modificación', $name)) {$type = 'Amendment to '.$type;}
                $status = $statuses[$data['document']['content']['estado'] ?? $data['document']['content']['status']];
                $origin = isset($data['document']['content']['sigla_emisor']) ? $origins[$data['document']['content']['sigla_emisor']]:'NULL';
                $summary = $data['document']['content']['sumario'] ?? $data['document']['content']['sintesis'] ?? NULL;
                $source = 'http://www.saij.gob.ar/'.$data['document']['metadata']['friendly-url']['description'].'/'.$data['document']['metadata']['uuid'];

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($name, '"')) {$name = str_replace('"', "\'", $name);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}
                if (str_contains($summary, '"')) {$summary = str_replace('"', "\'", $summary);}

                //JSONifies the name
                $name = '{"es":"'.$name.'"}';
                $summary = isset($summary) ? "'{\"es\":\"".$summary."\"}'":'NULL';
                $source = '{"es":"'.$source.'"}';

                //Makes sure there are no duplicates and adds law to the table
                if ($country === 'AR') {
                    $SQL2 = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                    $result = $conn->query($SQL2);
                    if ($result->num_rows === 0) {
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `origin`, `summary`, `source`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', ".$origin.", ".$summary.", '".$source."')"; echo $SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    } else {echo 'ID: '.$ID.'<br/>';}
                } else {echo 'Country: '.$country.'<br/>';}

                echo '<br/>';
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
</body>
</html>