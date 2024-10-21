<html>
<head>
    <meta charset="UTF-8">
    <?php
    function console_log($output, $with_script_tags = true) {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }
    ?>
</head>
<body>
    <?php
    //Inputs
    $test = false;
    $lang = 'ar';

    //Creates curl handler for search
    $ch_search = curl_init();
    curl_setopt_array($ch_search, [
        CURLOPT_URL => 'https://constituteproject.org/service/constitutions?in_force=true&is_draft=true&ownership=all&lang='.$lang,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CUSTOMREQUEST => 'GET'
    ]);
    $response = curl_exec($ch_search);
    
    //Closes the handler
    curl_close($ch_search);

    //Creates json from the response
    $json=json_decode($response, true);

    //Connects to the Lawbrary database
    $username="ug0iy8zo9nryq";
    $password="T_1&x+$|*N6F";
    $database="dbupm726ysc0bg";

    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Changes name
    for ($i=0; $i<count($json); $i++) {
        if (!$json[$i]['is_draft']) {
            //Gets Name
            $explodeID = explode('_', $json[$i]['id']);//Bhutan_2008
            $year = $explodeID[count($explodeID) - 1];//2008
            $countryName = trim(str_replace('_', ' ', explode('_'.$year, $json[$i]['id'])[0]));//Bhutan

            //Gets the relevant dates
            $CP_year_enacted = $json[$i]['year_enacted'];
            $CP_year_reinstated = $json[$i]['year_reinstated'];
            $CP_year_revised = $json[$i]['year_revised'];

            //Gets the other
            $realName = trim(explode($CP_year_enacted, $json[$i]['title'])[0]);
            $CP_country = $json[$i]['country'];
            $CP_country_id = $json[$i]['country_id'];
            $CP_title_short = $json[$i]['title_short'];

            //Gets the country data from lawbrary
            $sql1 = 'SELECT * FROM `countries` WHERE `name` LIKE "%'.$countryName.'%" OR `name` LIKE "%'.$realName.'%" OR `name` LIKE "%'.$CP_country.'%" OR `name` LIKE "%'.$CP_country_id.'%" OR `name` LIKE "%'.$CP_title_short.'%" LIMIT 1';
            $result1 = $conn->query($sql1);

            if ($result1->num_rows > 0) {
                //Gets the id and adjective
                while ($row = $result1->fetch_assoc()) {
                    //Gets data
                    $name = json_decode($row['name'], true);
                    $sql2 = "UPDATE `countries` SET `name`='{";
                    foreach ($name as $key => $value) {
                        if ($key !== $lang) {
                            $sql2 .= '"'.$key.'":"'.$value.'", ';
                        } else {$sql2 = null; break;}
                    }
                    if (isset($sql2)) {
                        $sql2 .= '"'.$lang.'":"'.$CP_title_short.'"}\' WHERE `id`=\''.$row['ID']."'";
                    }
                }
            }
                    
            //Executes SQL2
            echo $sql2.'<br><br>';
            if (!$test && isset($sql2)) {$conn->query($sql2);}
        }
    }

    //Closes MySQL connection
    $conn->close();
    ?>
</body>
</html>