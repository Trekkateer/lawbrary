<html><body>
    <?php //TODO: Find a better database
        //Settings
        $test = false; $country = 'SS';

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

        //Gets the HTML
        $html_dom = file_get_html('https://mojca.gov.ss/laws/');

        //Capitalizes titles with exceptions
        $exceptions = [
            'a', 'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'up'
        ];
        $ucwordsexcept = function($str, $delims=' ') use ($exceptions) {
            $out = array(strtolower(trim($str)));
            foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
                if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
                $out[$key+1] = '';
                foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                    $out[$key+1] .= !in_array($word, $exceptions) ? mb_strtoupper($word[0], 'UTF-8').substr($word, 1).$delim:$word.$delim;
                }
                $out[$key+1] = rtrim($out[$key+1], $delim);
            }
            return ucfirst(end($out));
        };

        //Makes sure the ID is 2 digits long
        $randNum = 0;
        $zero_buffer = function ($inputNum='', $outputLen=2) use (&$randNum) {
            $outputNum = trim($inputNum);
            if ($outputNum === '') {$outputNum = $randNum; $randNum++;}
            while (strlen($outputNum) < $outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Gets the types
        $types = [
            ' Act '=>'Law',
            ' Circular '=>'Circular',
            ' Code '=>'Code',
            ' Constitution '=>'Constitution',
        ];

        //Processes the data in the table
        $laws = $html_dom->find('div[data-id="72d4f93"]')[0]->find('ul.elementor-icon-list-items')[0]->find('li.elementor-icon-list-item > a');
        foreach ($laws as $law) {
            //Gets values
            $name = $ucwordsexcept(strtr($law->find('span.elementor-icon-list-text')[0]->plaintext, array('-'=>' ')), ' (');
            $enactDate = $enforceDate = $lastActDate = end(explode(' ', explode('(Table of Sections)', trim($name, ')'))[0])).'-01-01';
            $ID = $country.'-'.$zero_buffer().explode('-', $enactDate)[0];
            //Gets the regime
            switch (true) {
                case (strtotime($enactDate) < strtotime('today')):
                    $regime = 'The Republic of South Sudan';
                case (strtotime($enactDate) < strtotime('2011-07-09')):
                    $regime = 'The Southern Sudan Autonomous Region';
                case (strtotime($enactDate) < strtotime('2005-07-09')):
                    $regime = 'The Republic of the Sudan';
                case (strtotime($enactDate) < strtotime('1985-04-06')):
                    $regime = 'The Democratic Republic of the Sudan';
                case (strtotime($enactDate) < strtotime('1969-05-25')):
                    $regime = 'The Republic of the Sudan';
                case (strtotime($enactDate) < strtotime('1956-01-01')):
                    $regime = 'The Anglo-Egyptian Condominium';
                    break;
            }
            //Gets the type
            for ($i = 0; $i < count($types); $i++) {
                if (str_contains($name, array_keys($types)[$i])) {
                    $type = array_values($types)[$i];
                    break;
                }
            }
            if (str_contains('Amendment', $name)) {$type = 'Amendment to '.$type;}
            //Gets the rest of the values
            $status = 'Valid';
            $source = $law->href;

            //Makes sure there are no quotes in the title
            if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

            //JSONifies the values
            $name = '{"en":"'.$name.'"}';
            $source = '{"en":"'.$source.'"}';
            
            //Inserts the new laws
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastActDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastActDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$source."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
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