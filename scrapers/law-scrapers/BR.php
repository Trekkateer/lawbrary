<html><body>
    <?php
        //Settings
        $test = true; $country = 'BR';
        $start = 0;//What page to start from
        $step = 1000;//How many laws per page
        $limit = null;//Total number of pages desired

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Fixes the types and origins
        $types = array(
            'medida.provisoria'=>'Provisional Measure', 'MPV'=>'Provisional Measure',
            'lei'=>'Law',
            'lei.complementar'=>'Complementary Law',
            'emenda.constitucional'=>'Constitutional Amendment',
            'AIEMC'=>'International Act with the Force of a Constitutional Amendment'
        );
        //Fixes the statuses
        $statuses = array(
            'Prejudicada'=>'Impaired',
            'Convertida em Lei'=>'Converted to Law',
            'Promulgada como Lei'=>'Enacted as Law',
            'Perda de Eficácia'=>'No Longer Effective',
            'Vigência Encerrada'=>'Out of Force',
            'Reeditada com Alteração'=>'Reissued with Amendment',
            'Reeditada sem Alteração'=>'Reissued without Amendment',
            'Rejeitada'=>'Rejected',
            'Revogada'=>'Repealed',
            'Inconstitucional'=>'Unconstitutional',
            ''=>'Valid'
        );
        
        //Finds the limit
        $API_Call = function($page, $step) {return json_decode(file_get_contents('https://legis.senado.leg.br/sigen/api/catalogo/basico?anoInicial=1889&anoFinal='.date('Y').'&pagina='.$page.'&tamanhoPagina='.$step), true);};
        $limit = $limit ?? $API_Call($start, 1)['totalHits']/$step-1;
        //Gets the laws
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the data from API
            $laws = $API_Call($page, $step)['searchHits'];
            foreach ($laws as $law) {
                //Interprets the data
                $enactDate = $law['content']['assinatura']; $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.($law['content']['numero'] !== 0 ? $law['content']['numero']:$law['content']['status'][0]['documento']);

                //Gets the regime
                switch(true) {
                    case strtotime($enactDate) < strtotime('7 September 1822'):
                        $regime = 'The Kingdom of Portugal';
                        break;
                    case strtotime('7 September 1822') < strtotime($enactDate) && strtotime($enactDate) < strtotime('15 November 1889'):
                        $regime = 'The Empire of Brazil';
                        break;
                    case strtotime('15 November 1889') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('5 October 1988'):
                        $regime = 'The Republic of Brazil';
                        break;
                    case strtotime('5 October 1988') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                        $regime = 'The Federative Republic of Brazil';
                        break;
                }
                
                //Gets the rest of the values
                $name = trim($law['content']['nome']);
                $type = $types[$law['content']['tipo'] !== '' ? $law['content']['tipo']:explode('-', $law['content']['apelido'])[0]];
                    if (str_contains($name, 'Emenda') && $type !== 'Constitutional Amendment') {$type = 'Ammendment to '.$type;}
                $status = $statuses[$law['content']['status'][0]['nomeSituacao'] ?? ''];
                $summary = $law['content']['ementa'];
                $source = 'https://normas.leg.br/?urn='.$law['id'];

                //Makes sure there are no quotes in the title or summary
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}

                //JSONifies the title and source
                $name = '{"pt":"'.$name.'"}';
                $summary = '{"pt":"'.$summary.'"}';
                $source = '{"pt":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `summary`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$summary."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
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