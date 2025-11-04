<?php //Scrapes the titles of countries and subdivisions from Wikipedia and the Constitute Project
    //Settings
    $test = [true, true];

    //Opens the parser
    include 'simple_html_dom.php';
 
    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Crawls Wikipedia for the titles
    echo '--------------------<br/>Crawling Wikipedia<br/>--------------------<br/>';
    $dom = file_get_html('https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes');
    $rows = $dom->find('table', 0)->find('tbody', 0)->find('tr');
    foreach ($rows as $row) {
        if ($row->find('td', 3) == null) continue;

        //Gets the title
        $title = '{"en":"'.ucfirst(trim(strtr($row->find('td', 1)->find('a', 0)->plaintext, ["'"=>"ꞌ"]))).'"}';

        //Gets the ID
        $ID = explode('#', $row->find('td', 3)->find('a', 0)->href)[1];

        //Skips the US Outlying Islands because it's name is weird
        if ($ID == 'UM') continue;
        else if ($ID == 'PS') $ID = 'PS-WEST-BANK';

        //Finds the country or subdivision in the content database
        $SQL1 = "SELECT * FROM `countries` WHERE `id`='".$ID."'";
        $resultC = $conn->query($SQL1);
        if ($resultC->num_rows == 1) {
            //Updates the title
            $SQL2 = "UPDATE `countries` SET `title`='".$title."' WHERE `id`='".$ID."'";
        } else {
            //Finds the division in the content database
            $SQL1 = "SELECT * FROM `divisions` WHERE `id`='".$ID."'";
            $resultD = $conn->query($SQL1);
            if ($resultD->num_rows == 1) {
                //Updates the title
                $SQL2 = "UPDATE `divisions` SET `title`='".$title."' WHERE `id`='".$ID."'";
            } else {
                //Finds the second level in the content database
                $SQL1 = "SELECT * FROM `divisions2` WHERE `id`='".$ID."'";
                $result2 = $conn->query($SQL1);
                if ($result2->num_rows == 1) {
                    //Updates the title
                    $SQL2 = "UPDATE `divisions2` SET `title`='".$title."' WHERE `id`='".$ID."'";
                } else {echo('No match found for '.$ID.'<br>');}
            } 
        }

        //Queries the database
        echo $SQL2.'<br>';
        if (!$test[0]) $conn->query($SQL2);
    }

    //Cralws the Constitute Project for titles
    echo '<br/>--------------------<br/>Crawling the Constitute Project<br/>--------------------<br/>';
    foreach (array('ar', /*'en',*/ 'es') as $lang) {
        //Creates an array to store already visited countries
        $visited = array();

        //Connects to the API
        $dom = file_get_contents('https://constituteproject.org/service/constitutions?ownership=all&lang='.$lang);
        $docs = json_decode($dom, true);

        //Goes through the documents
        foreach ($docs as $doc) {
            //Matches the CP country to a country in our database
            $countryName = strtr(explode('_'.preg_replace('/[^0-9]/', '', $doc['id']), $doc['id'])[0], ['_'=>' ']);
            $CP_country = $doc['country'];
            $CP_country_id = $doc['country_id'];
            $CP_title_short = $doc['title_short'];
            switch ($CP_country) {
                case "Denmark": $CP_country = "The Danish Realm"; break;
                case "Palestine": $CP_country = "The West Bank"; break;
                case "UK": $CP_country = "The United Kingdom"; break;
            }

            //Gets the country data from lawbrary
            $SQL1 = 'SELECT * FROM `countries` WHERE `name` LIKE "%'.$countryName.'%" OR `name` LIKE "%'.$CP_country.'%" OR `name` LIKE "%'.$CP_country_id.'%" OR `name` LIKE "%'.$CP_title_short.'%" LIMIT 1';
            $result1 = $conn->query($SQL1);
            if ($result1->num_rows == 1) {
                while ($row = $result1->fetch_assoc()) {
                    //Gets the ID
                    $ID = $row['ID'];
                    
                    //Adds the country to the visited list, or skips it if it's already there
                    if (!in_array($ID, $visited)) $visited[] = $ID;
                    else continue(2);

                    //Updates the title JSON
                    $titleJSON = json_decode($row['title'], true);
                    $titleJSON[$lang] = $doc['country'];
                    $title = json_encode($titleJSON, JSON_UNESCAPED_UNICODE);

                    //Queries the database
                    $SQL2 = "UPDATE `countries` SET `title`='".$title."' WHERE `id`='".$ID."'";
                    echo $SQL2.'<br>';
                    if (!$test[1]) $conn->query($SQL2);
                }
            }
        }
    }
?>