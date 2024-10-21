<html><body>
    <?php
        //Settings
        $test = true; $country = 'MC';
        $start = 0;//Which law to start from
        $limit = null;//Total number of laws desired.

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $API_Call = function ($type, $limit) use ($start) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://legimonaco.mc/~~search/depot/_search',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{"size":'.$limit.',"track_total_hits":true,"_source":["path","title","type","date","abrogated","focus","abstract"],"query":{"bool":{"must":[{"terms":{"type":["'.$type.'"]}}],"must_not":[{"terms":{"processing":["home"]}},{"terms":{"trashed":["true"]}},{"terms":{"excludeFromSearch":["true"]}},{"range":{"startDate":{"gt":"now"}}},{"range":{"endDate":{"lte":"now"}}}]}},"sort":[{"date":{"order":"desc"}},{"typeOrder":{"order":"asc"}},{"number":{"order":"asc"}}],"from":'.$start.'}',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: BIGipServerPOOL_LEGIMCO_FRTO_PROD=204685322.20480.0000',
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return json_decode($response, true);
        };

        //Loops through the languages
        foreach (array('legislation', 'regulation') as $type) {
            //Gets the limit
            $limit = $limit ?? $API_Call($type, 1)['hits']['total']['value'];

            //Gets values
            foreach ($API_Call($type, $limit)['hits']['hits'] as $law) {
                $enactDate = date('Y-m-d', strtotime($law['_source']['date'])); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.$law['_id'];
                $regime = 'The Principality of Monaco';
                $name = $law['_source']['title'];
                $type = ucfirst($type); $status = 'Valid';
                $source = 'https://legimonaco.mc'.$law['_source']['path'];
                $PDF = $source.'?V=pdf&.pdf';

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', "\"", $name);}
                //if (str_contains($name, '""')) {$name = str_replace('""', "\'", $name);}

                //JSONifies the values
                $name = '{"fr":"'.$name.'"}';
                $source = '{"fr":"'.$source.'"}';
                $PDF = '{"fr":"'.$PDF.'"}';

                //Inserts the law to the table
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';

                //Makes the query
                if (!$test) {$conn->query($SQL2);}
            }

            //Resets the limit
            $limit = null;
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