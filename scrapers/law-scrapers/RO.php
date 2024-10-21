<html><body>
    <?php
        //Settings
        $test = true; $country = 'RO';
        $start = 1; //Which page to start from
        $step = 50; //How many laws are on each page
        $limit = null; //Which page to end at

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

        //Makes sure there are five digits in every outputed number
        function zero_buffer ($inputNum, $outputLen=5) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Translates the types
        $types = array(
            'ORDIN' => 'Order',
        );

        //Gets the page limit
        $html_dom = file_get_html('https://legislatie.just.ro/Public/RezultateCautare?page='.$start.'&rezultatePerPagina='.$step/10);
        $limit = $limit ?? explode('?page=', explode('&rezultate', end($html_dom->find('div#textarticol')[0]->find('ul.pagination')[0]->find('li.PagedList-skipToLast'))->find('a')[0]->href)[0])[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) {
            //Loops through the laws
            $laws = $html_dom->find('div#textarticol')[0]->find('div.search_result_page')[0]->find('div.search_result_item');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = date('Y-m-d', strtotime(str_replace('/', '-', end(explode(' ', $law->find('p')[0]->find('a')[0]->plaintext))))); $lastactDate = $enactDate;
                $enforceDate = $enactDate;
                $ID = $country.'-'.str_replace('.', '', explode(' ', $law->find('p')[1]->find('span.S_DEN')[0]->plaintext)[2]);
                //Gets the regime
                switch(true) {
                    case strtotime('2 October 1888') < strtotime($enactDate) && strtotime($enactDate) < strtotime('9 September 1914'):
                        $regime = 'The German Empire';
                        break;
                    case strtotime('9 September 1914') < strtotime($enactDate) && strtotime($enactDate) < strtotime('29 September 1923'):
                        $regime = 'The British Empire';
                        break;
                    case strtotime('29 September 1923') < strtotime($enactDate) && strtotime($enactDate) < strtotime('26 August 1942'):
                        $regime = 'The Commonwealth of Australia';
                        break;
                    case strtotime('26 August 1942') < strtotime($enactDate) && strtotime($enactDate) < strtotime('13 September 1945'):
                        $regime = 'The Empire of Japan';
                        break;
                    case strtotime('13 September 1945') < strtotime($enactDate) && strtotime($enactDate) < strtotime('31 January 1968'):
                        $regime = 'The Commonwealth of Australia';
                        break;
                    case strtotime('31 January 1968') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                        $regime = 'The Republic of Nauru';
                        break;
                }
                //Gets the rest of the values
                $name = trim($law->find('p')[1]->find('span.S_PAR')[0]->plaintext);
                $type = $types[explode(' ', $law->find('p')[0]->find('a')[0]->plaintext)[1]];
                    if (str_contains(strtolower($name), 'amend')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $source = 'https://legislatie.just.ro'.$law->find('p')[0]->find('a')[0]->href;
                //$PDF = $law->find('td')[2]->find('a')[0]->href ?? NULL;

                //Makes sure there are no quotes in the title
                $name = strtr($name, array("'"=>"’", ' "'=>' “', '"'=>'”'));

                //JSONifies the values
                $name = '{"ro":"'.$name.'"}';
                $source = '{"ro":"'.$source.'"}';
                $PDF = isset($PDF) ? '\'{"ro":"https://lawphil.net/'.$PDF.'"}\'':'NULL';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', ".$PDF.")"; echo $SQL2.'<br/>';
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