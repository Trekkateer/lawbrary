<html><body>
    <?php
        //Settings
        $test = true; $country = 'SE';
        $start = 1;//What page to start from
        $limit = null;//Total number of pages desired. Set to null to get number automatically

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

        //Fixes the types
        $types  = array('Svensk författningssamling'=>'Swedish Code of Statutes',);
        
        //Finds the limit
        $API_Call = json_decode(file_get_contents('https://www.riksdagen.se/api/data/?url=%2Fsv%2Fsok%2F%3Favd%3Ddokument%26doktyp%3Dsfs'), true);
        $limit = $limit ?? $API_Call['search']['numberOfPages']; echo $limit.'<br/>';
        //Gets the laws
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the data from congress.gov API
            $laws = json_decode(file_get_contents('https://www.riksdagen.se/api/data/?url=%2Fsv%2Fsok%2F%3Favd%3Ddokument%26doktyp%3Dsfs%26p%3D'.$page), true)['search']['documents'];
            foreach ($laws as $law) {
                //Interprets the data
                $html_dom->load($law['statusRow']);
                    $enactDate = trim(str_replace('-', '/', $html_dom->find('dd')[0]->plaintext ?? explode('-', $law['id'])[1].'/01/01')); $enforceDate = $enactDate;
                $ID = $country.'-'.str_replace('-', '', $law['id']);
                $name = $law['title'];
                $summary = '<span>'.trim($law['summary']).'</span>';
                $type = $types[$law['debateName']]; $status = 'Valid';
                $source = $law['url']; $PDF = $law['attachedFileList']['files'][0]['url'] ?? 'NULL';

                //Makes sure there are no quotes in the title or summary
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}

                //JSONifies the title and source
                $name = '{"sv":"'.$name.'"}';
                $summary = isset($law['summary']) ? '{"sv":"'.$summary.'"}':'NULL';
                $source = '{"sv":"'.$source.'"}';
                $PDF = isset($law['attachedFileList']['files'][0]['url']) ? '{"sv":"'.$PDF.'"}':'NULL';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `summary`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$summary."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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