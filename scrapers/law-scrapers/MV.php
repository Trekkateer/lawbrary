<html><body>
    <?php
        //TODO: Find a way to get more than just court documents
        //Settings
        $test = true; $country = 'MV';
        $start = 1;//Which page to start from
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

        //Makes sure there are four digits in every outputed number
        $zero_buffer = function ($inputNum, $outputLen=4) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

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

        //Gets the court documents
        //Gets the limit
        $html_dom = file_get_html('https://legalarchive.mv/legal-documents');
        $limit = $limit ?? $html_dom->find('div.container.pt-5')[0]->find('ul.pagination.justify-content-center')[0]->find('li.page-item')[11]->plaintext;
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page ++) {echo '<br/>Page '.$page.'<br/>';
            //Gets the HTML
            $html_dom = file_get_html('https://legalarchive.mv/legal-documents?page='.$page);

            //Processes the data in the table
            $laws = $html_dom->find('div.card.mb-5.shadow-sm.legal-document-card div.card-body div.row');
            foreach ($laws as $law) {
                //Gets values
                $datesplode = explode(' ', strtr($law->find('div.col-md-3.my-auto.meta')[0]->find('p.published-at')[0]->plaintext, array('‫'=>'')));
                    $enactDate = $datesplode[2].$months[$datesplode[1]].$datesplode[0]; $enforceDate = $enactDate; $lastactDate = $enforceDate;
                $ID = $country.'-'.explode('/', $law->find('div.col-md-9')[0]->find('a.text-decoration-none')[0]->href)[4];
                //Gets regime
                switch(true) {
                    case strtotime($enactDate) < strtotime('26 July 1965'):
                        $regime = 'The British Protectorate of the Maldives';
                        break;
                    case strtotime('26 July 1965') < strtotime($enactDate) && strtotime($enactDate) < strtotime('11 November 1968'):
                        $regime = 'The Sultanate of the Maldives';
                        break;
                    case strtotime('11 November 1968') < strtotime($enactDate) && strtotime($enactDate) < strtotime(date('d M Y')):
                        $regime = 'The Republic of the Maldives';
                        break;
                }
                //Gets the rest of the values
                $name = trim($law->find('div.col-md-9')[0]->find('a.text-decoration-none')[0]->plaintext);
                $type = $types[trim($law->find('div.col-md-3.my-auto.meta')[0]->find('div.category a')[0]->plaintext ?? 'Act')];
                $status = 'Valid';
                $origin = $origins[trim($law->find('div.col-md-3.my-auto.meta')[0]->find('a.text-decoration-none')[0]->plaintext ?? 'NULL')];
                $source = $law->find('div.col-md-9')[0]->find('a.text-decoration-none')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the values
                $name = '{"dv":"'.$name.'"}';
                $source = '{"dv":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `origin`, `source`) 
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', ".$origin.", '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        $html_dom = file_get_html('https://mvlaw.gov.mv/dv/legislations'); echo $html_dom;
        
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