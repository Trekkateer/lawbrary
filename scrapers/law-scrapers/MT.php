<html><body>
    <?php
        //Settings
        $test = false; $country = 'MT';
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
        $API_Call = function ($culture, $searchType, $limit=1) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://legislation.mt/Search/SearchFinal',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'draw'=>1, 'columns[0][searchable]'=>true, 'columns[0][orderable]'=>false, 'columns[0][search][regex]'=>false, 'columns[1][data]'=>'ItemType', 'columns[1][searchable]'=>true, 'columns[1][orderable]'=>true, 'columns[1][search][regex]'=>false, 'columns[2][data]'=>'Chapter', 'columns[2][searchable]'=>true, 'columns[2][orderable]'=>true, 'columns[2][search][regex]'=>false, 'columns[3][data]'=>'ChapterTitle', 'columns[3][searchable]'=>true, 'columns[3][orderable]'=>true, 'columns[3][search][regex]'=>false, 'columns[4][searchable]'=>true, 'columns[4][orderable]'=>false, 'columns[4][search][regex]'=>false, 'order[0][column]'=>0, 'order[0][dir]'=>'asc', 'start'=>0, 'length'=>$limit, 'search[regex]'=>false, 'search[SearchOn]'=>'Whole Phrase', 'search[SearchBy]'=>'Title', 'search[SearchType]'=>$searchType, 'search[ContentSortSetting]'=>true, 'search[IsCustomSort]'=>false),
                CURLOPT_HTTPHEADER => array(
                    'Cookie: TiPMix=78.28662963729278; x-ms-routing-name=self; ASP.NET_SessionId=und2t5txiwwfbcxuuy31rvwj; ARRAffinity=9f138fe40e53e241408034e8cd47d4d452b331805ff6d9018b002451153e871e; ARRAffinitySameSite=9f138fe40e53e241408034e8cd47d4d452b331805ff6d9018b002451153e871e; _culture='.$culture
                ),
            ));
            $response = json_decode(curl_exec($curl), true); curl_close($curl);
            return $response;
        };

        //Gets the types
        $types = array(
            'ACTS'=>'Act',
            'CONS'=>'Consolidated Law',
            'SL'=>'Subsidiary Legislation',
            'LEGALNOTICES'=>'Legal Notice',
            'BYELAWS'=>'Bye-Law',
        );

        //Loops through the languages
        foreach (array('mt'=>'mt-MT', 'en'=>'en-GB') as $lang=>$culture) {
            //Loops through the types
            foreach (array('CONS'/*,'SL','ACTS','LEGALNOTICES','BYELAWS'*/) as $searchType) {
                //Gets the limit
                $limit = $limit ?? $API_Call($culture, $searchType)['recordsFiltered'];

                //Gets values
                foreach ($API_Call($culture, $searchType, $limit)['data'] as $law) {
                    if ($searchType === 'CONS' || $searchType === 'SL') {
                        $enactDate = 'NULL'; $enforceDate = $enactDate;
                        $ID = $country.'-'.$law['ID'];
                    } else if ($searchType === 'ACTS' || $searchType === 'LEGALNOTICES' || $searchType === 'BYELAWS') {
                        $enactDate = date('Y-m-d', strtotime(str_replace('.', '-', explode('<', explode('– ', $law['ChapterTitle'])[1])[0]))); $enforceDate = $enactDate;
                        $ID = $country.'-'.strtr($law['Chapter'], array(' of '=>'', ' tal-'=>'', ' '=>'', '/'=>''));
                    }
                    $name = explode('<', $law['ChapterTitle'])[0];
                    $type = $types[$searchType];
                        if (str_contains($law['ChapterTitle'], 'Amendment')) {$type = 'Amendment to '.$type;}
                    $status = 'Valid';
                        if (str_contains($law['ChapterTitle'], 'Repealed')) {$status = 'Repealed';}
                    $source = 'https://legislation.mt/'.$law['URL'];

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                    $result = $conn->query($SQL);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            //Encodes the name
                            $nameJSON = json_decode($row['name'], true);
                            $nameJSON[$lang] = $name;
                            $name = json_encode($nameJSON, JSON_UNESCAPED_UNICODE);

                            //Encodes the source
                            $sourceJSON = json_decode($row['source'], true);
                            $sourceJSON[$lang] = $source;
                            $source = json_encode($sourceJSON, JSON_UNESCAPED_UNICODE);

                            //Updates the table
                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {//If there is no existing entry
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Inserts the law to the table
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `status`, `source`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$status."', '".$source."')";
                    }

                    //Makes the query
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }

                //Resets the limit
                $limit = null;
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