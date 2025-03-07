<?php
    //Settings
    $test = true;

    //Opens the parser
    include 'simple_html_dom.php';
 
    //Connects to the content database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");


    //Crawls Wikipedia for the titles
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
        if (!$test) $conn->query($SQL2);
    }
?>