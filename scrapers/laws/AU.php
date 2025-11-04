<?php //Australia
    //Settings
    $test = false; $scraper = 'AU';
    $start = 0;//Which law to start from
    $step = 100;//How many laws per page
    $limit = NULL;//Total number of laws desired.

    //Include my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Translates the types
    $types = array(
        'Act'=>'Act',
        'AdministrativeArrangementsOrder'=>'Administrative Arrangements Order',
        'ContinuedLaw'=>'Continued Law',
        'Constitution'=>'Constitution',
        'Gazette'=>'Gazette Item',
        'LegislativeInstrument'=>'Legislative Instrument',
        'NotifiableInstrument'=>'Notifiable Instrument',
        'PrerogativeInstrument'=>'Prerogative Instrument'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["AU"]';
    $publisher = '{"en":"The Federal Register of Legislation of Australia"}';
    
    //Gets the laws
    $limit = $limit ?? json_decode(file_get_contents('https://api.prod.legislation.gov.au/v1/titles/search(criteria=%27and(status(InForce),pointintime(Latest))%27)?$select=administeringDepartments,collection,hasCommencedUnincorporatedAmendments,id,isInForce,isPrincipal,name,number,optionalSeriesNumber,searchContexts,seriesType,subCollection,year&$expand=administeringDepartments,searchContexts($expand=fullTextVersion)&$orderby=searchcontexts/fulltextversion/registeredat%20desc&$count=true&$top='.$step.'&$skip=0'), true)['@odata.count'];
    for ($offset = $start; $offset <= $limit; $offset += $step) {
        //Gets the data from legislation.gov API
        $laws = json_decode(file_get_contents('https://api.prod.legislation.gov.au/v1/titles/search(criteria=%27and(status(InForce),pointintime(Latest))%27)?$select=administeringDepartments,collection,hasCommencedUnincorporatedAmendments,id,isInForce,isPrincipal,name,number,optionalSeriesNumber,searchContexts,seriesType,subCollection,year&$expand=administeringDepartments,searchContexts($expand=fullTextVersion)&$orderby=searchcontexts/fulltextversion/registeredat%20desc&$count=true&$top='.$step.'&$skip='.$offset), true)['value'];
        foreach ($laws as $law) {
            //Interprets the data
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law['searchContexts']['fullTextVersion']['start']));
                $endDate = $law['searchContexts']['fullTextVersion']['end'] === '9999-12-31T23:59:59.9999999' ? 'NULL':"'".date('Y-m-d', strtotime($law['searchContexts']['fullTextVersion']['end']))."'";
            $ID = $scraper.':'.$law['id'];
            $name = fixQuotes($law['name'], 'en');
            //Gets the regime
            switch (true) {
                case strtotime($enactDate) <= strtotime('today'):
                    $regime = '{"en":"The Commonwealth of Australia"}';
                    break;
                case strtotime($enactDate) < strtotime('1 January 1901'):
                    $regime = '{"en":"The British Empire"}';
                    break;
            }
            //Gets the origin, making sure there are no quotation marks
            $origin = "'".json_encode(array_map(function($origin) {return array("en"=>fixQuotes($origin['name'], 'en'));}, $law['administeringDepartments']))."'";
                if ($origin === "'[]'") $origin = 'NULL';
            //Gets the rest of the values
            $type = $types[$law['collection']];
            $status = $law['isInForce'] ? 'In Force':'Out of Force';
            $source = 'https://www.legislation.gov.au/'.$law['id'].'/latest/text';

            //JSONifies the name and source
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';

            //Makes sure there are no duplicates and adds law to the table
            $SQL2 = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
            $result = $conn->query($SQL2);
            if ($result->num_rows === 0) {
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `endDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', ".$endDate.", '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', '".$status."', '".$source."')";
                        echo 'off. '.$offset.': '.$SQL2.'<br/><br/>';
                if (!$test) {$conn->query($SQL2);}
            }
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