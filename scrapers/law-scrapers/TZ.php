<html><body>
    <?php
        //Settings
        $test = true; $country = 'TZ';

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Gets the data from API
        $laws = json_decode(file_get_contents('https://www.parliament.go.tz/polis/api/acts/async?_=1722797794113'), true)['data'];
        foreach ($laws as $law) {
            //Interprets the data
            $enactDate = $law['posted'].'-01-01'; $enforceDate = $enactDate; $lastactDate = $enforceDate;
            $ID = $country.'-'.$law['id'];
            $regime = 'The United Republic of Tanzania';
            $name = $law['title_sw'];
            $type = 'Act'; $status = 'Valid';
            $source = 'https://www.parliament.go.tz/polis'.str_replace(' ', '%20', $law['file_url']); $PDF = $source;

            //Makes sure there are no quotes in the title or summary
            if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
            if (str_contains($source, "'")) {$source = str_replace("'", "%27", $source);}
            if (str_contains($PDF, "'")) {$PDF = str_replace("'", "%27", $PDF);}

            //JSONifies the title and source
            $name = '{"sw":"'.$name.'"}';
            $source = '{"sw":"'.$source.'"}';
            $PDF = '{"sw":"'.$PDF.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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