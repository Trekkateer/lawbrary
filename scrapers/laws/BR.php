<?php //Brazil
    //Settings
    $test = false; $scraper = 'BR';
    $start = 0;//What page to start from
    $step = 1000;//How many laws per page
    $limit = null;//Total number of pages desired

    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
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

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["BR"]';
    $publisher = '{"pt":"O Portal normas.leg.br", "en":"The normas.leg.br Portal"}';
    
    //Finds the limit
    $API_Call = function($page, $step) {return json_decode(file_get_contents('https://legis.senado.leg.br/sigen/api/catalogo/basico?anoInicial=1889&anoFinal='.date('Y').'&pagina='.$page.'&tamanhoPagina='.$step), true);};
    $limit = $limit ?? $API_Call($start, 1)['totalHits']/$step-1;
    //Gets the laws
    for ($page = $start; $page <= $limit; $page++) {
        //Gets the data from API
        $laws = $API_Call($page, $step)['searchHits'];
        foreach ($laws as $law) {
            //Interprets the data
            $enactDate = $enforceDate = $lastactDate = $law['content']['assinatura'];
            $ID = $scraper.':'.($law['content']['numero'] !== 0 ? $law['content']['numero']:$law['content']['status'][0]['documento']);

            //Gets the country and regime
            switch(true) {
                case strtotime($enactDate) < strtotime('7 September 1822'):
                    $regime = '{"en":"The Kingdom of Portugal", "pt":"O Reino de Portugal"}';
                    break;
                case strtotime('7 September 1822') < strtotime($enactDate) && strtotime($enactDate) < strtotime('15 November 1889'):
                    $regime = '{"en":"The Empire of Brazil", "pt":"O Império do Brasil"}';
                    break;
                case strtotime('15 November 1889') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('5 October 1988'):
                    $regime = '{"en":"The Republic of Brazil", "pt":"A República do Brasil"}';
                    break;
                case strtotime('5 October 1988') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                    $regime = '{"en":"The Federative Republic of Brazil", "pt":"A República Federativa do Brasil"}';
                    break;
            }
            
            //Gets the rest of the values
            $name = fixQuotes(trim($law['content']['nome']), 'pt_BR');
            $type = $types[$law['content']['tipo'] !== '' ? $law['content']['tipo']:explode('-', $law['content']['apelido'])[0]];
            //$isAmend = str_contains($name, 'Emenda') ? 1:0;
            $status = $statuses[$law['content']['status'][0]['nomeSituacao'] ?? ''];
            $summary = fixQuotes($law['content']['ementa'], 'pt_BR');
            $source = 'https://normas.leg.br/?urn='.$law['id'];

            //JSONifies the title and source
            $name = '{"pt":"'.$name.'"}';
            $summary = '{"pt":"'.$summary.'"}';
            $source = '{"pt":"'.$source.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `summary`, `source`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$summary."', '".$source."')";
            echo '<a href="https://legis.senado.leg.br/sigen/api/catalogo/basico?anoInicial=1889&anoFinal='.date('Y').'&pagina='.$page.'&tamanhoPagina='.$step.'" target="_blank">P'.$page.'</a>: '.$SQL2.'<br/>';
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