<html><body>
    <?php //!Failed to get type!
        //Settings
        $test = true; $country = 'KZ';
        $start = 1948; //Which year to start from
        $step = 100; //How many laws are on each page
        $limit = null; //Which year to stop at

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';
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

        //Preloop Arrays
        $months = array('қаңтар'=>'jan', 'қаңтарында'=>'jan', 'қаңтардағы'=>'jan',
                        'ақпан'=>'feb', 'ақпандағы'=>'feb',
                        'наурыз'=>'mar', 'наурызда'=>'mar', 'наурыздағы'=>'mar',
                        'сәуір'=>'apr', 'сәуiр'=>'apr', 'сәуірде'=>'apr', 'сәуірдегі'=>'apr', 'сәуiрдегi'=>'apr', 'сәуiрдегі'=>'apr',
                        'мамыр'=>'may', 'мамырдағы'=>'may',
                        'маусым'=>'jun', 'маусымдағы'=>'jun',
                        'шiлде'=>'jul', 'шілде'=>'jul', 'шілдегі'=>'jul', 'шілдедегі'=>'jul', 'шiлдедегi'=>'jul', 'шiлдедегі'=>'jul',
                        'тамыз'=>'aug', 'тамызында'=>'aug', 'тамыздағы'=>'aug', 'тамыздағы'=>'aug',
                        'қыркүйек'=>'sep', 'қыркүйектегі'=>'sep', 'қыркүйектегi'=>'sep', 'қыркүйекте'=>'sep',
                        'қазан'=>'oct', 'қазандағы'=>'oct',
                        'қараша'=>'nov', 'қарашадағы'=>'nov', 'қарашасындағы'=>'nov', 'қарашада'=>'nov',
                        'желтоқсан'=>'dec', 'желтоқсанда'=>'dec', 'желтоқсандағы'=>'dec', 'желтоқсанында'=>'dec');
        /*$types =  array('Қазақстан Республикасының Конституциялық заңы'=>'Constitutional Law',
                        'Қазақстан Республикасының Конституциялық Заңы'=>'Constitutional Law',
                        'Қазақстан Республикасының Заңы'=>'Law',
                        'Қазақстан Республикасының Кодексі'=>'Code',
                        'Қаулы Қазақ КСР Жоғарғы Соты Пленумы'=>'Supreme Court Resolution',
                        'Қаулы Қазақстан Республикасы Жоғарғы Соты Пленумы'=>'Supreme Court Resolution',
                        'Декларация Біріккен Ұлттар Ұйымы Бас Ассамблеясының резолюциясымен'=>'UN Declaration',
                        'Қазақстан Республикасы Президентiнiң Жарлығы'=>'Decree',
                        'Қазақ Советтiк Социалистiк Республикасы Президентінің Жарлығы'=>'Decree',
                        'Конституциялық Заң күші бар Жарлық'=>'Decree with the force of Constitutional Law',
                        );*/
        /*$types =  array('K'=>'Constitution'
                        'Z'=>'Law',
                        'U'=>'Decree')*/

        //Sets up querying function
        $HTTP_Call = function ($href) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $href,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: JSESSIONID=E7C8785D1176544E536BC3C6C7B55A3C; rememberMe=98911a55-6eef-4a8e-a4eb-3ac0091d0a5a'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return $response;
        };

        //Loops through languages
        foreach (array('kaz'=>'kk', 'rus'=>'ru'/*, 'eng'=>'en'*/) as $locale => $lang) {
            $limit = $limit ?? date('Y');
            for ($year = $start; $year <= $limit; $year ++) {echo 'The year is '.$year.'<br/>';
                //Gets the limit of the pages
                $html_dom->load($HTTP_Call("https://adilet.zan.kz/".$locale."/index/docs/dt=".$year."-&pagesize=".$step));
                $pageLimit = explode('&page=', explode('&pagesize=', $html_dom->find('a.nextpostslink')[1]->href)[0])[1] ?? 1; echo 'Pagelimit: '.$pageLimit.'<br/>';
                for ($page = 1; $page <= $pageLimit; $page++) {
                    //Gets the new page
                    $html_dom->load($HTTP_Call("https://adilet.zan.kz/".$locale."/index/docs/dt=".$year."-&page=".$page."&pagesize=".$step));
                    //Processes the data
                    $laws = $html_dom->find('div.serp')[0]->find('div.post_holder');
                    foreach ($laws as $law) {
                        //Gets data
                        $explodeDate = explode(' ', $law->find('p')[0]->innertext);
                            foreach ($explodeDate as $partNum => $part) {//Gets the date based on its relation to a certain word
                                if ($part === 'жылғы' || $part === 'жылғы' || $part === 'жылдың' || $part === 'ж.') {
                                    echo 'Keyword: '.$part.'<br/>';
                                    $enactDate = date('Y-m-d', strtotime($months[trim($explodeDate[$partNum+2], ' .')].' '.$explodeDate[$partNum+1].', '.$explodeDate[$partNum-1]));
                                    //Handles exceptions
                                    switch ($explodeDate[$partNum+2].' '.$explodeDate[$partNum+1].', '.$explodeDate[$partNum-1]) {
                                        case 'қарары. 1984/47, 1984':
                                            $enactDate = '1984-05-25';
                                            break;
                                        case '5-i қарашаның, 1991':
                                            $enactDate = '1991-11-05';
                                            break;
                                        case '16-сы қазанның, 1991':
                                            $enactDate = '1991-10-16';
                                            break;
                                        case 'N 23-тамыз, 1991':
                                            $enactDate = '1991-08-23';
                                            break;
                                        case 'қаңтардағы 11, 19994':
                                            $enactDate = '1994-01-11';
                                            break;
                                        case '1 15, 1998':
                                            $enactDate = '1998-07-01';
                                            break;
                                    }
                                    echo "enactDate: ".$enactDate."<br/>";
                                    break;
                                }
                            } $enforceDate = $enactDate; $lastactDate = $enactDate;
                        $ID = $country.'-'.explode('docs/', $law->find('a')[0]->href)[1];
                        //Gets the regime
                        if (strtotime($enactDate) < strtotime('16 December 1991')) {
                            $regime = 'The Kazakh Soviet Republic';
                        } else {$regime = 'The Republic of Kazakhstan';}
                        //Gets the rest of the values
                        $name = $law->find('a')[0]->innertext;
                        //$type = $types[trim(explode($year, $law->find('p')[0]->innertext)[0], ' .')];
                        //$type = $types[preg_replace('/[0-9]/', '', end(explode('/', $law->find('a')[0]->href)))];
                        if ($law->find('span.status')[0]->innertext === 'Күшін жойған') {$status = 'Canceled';} else {$status = 'Valid';}
                        $source = 'https://adilet.zan.kz'.$law->find('a')[0]->href;

                        //Makes sure there are no appostophes in the title
                        $name = str_replace("'", "’", $name);

                        //Creates SQL to check if the law is already stored
                        $SQL = "SELECT * FROM `laws".strtolower($country)."` WHERE `ID`='".$ID."'";
                        $result = $conn->query($SQL);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                //JSONifies the name
                                $compoundedName = json_decode($row['name'], true);
                                $compoundedName[$lang] = $name;
                                $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                                //JSONifies the href
                                $compoundedSource = json_decode($row['source'], true);
                                $compoundedSource[$lang] = $source;
                                $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                                $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                            }
                        } else {
                            //JSONifies the name and href
                            $name = '{"'.$lang.'":"'.$name.'"}';
                            $source = '{"'.$lang.'":"'.$source.'"}';

                            //Creates SQL
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `status`, `source`)
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$status."', '".$source."')";
                        }

                        //Executes the SQL, excluding constitution
                        if ($ID !== 'K950001000_') {echo $SQL2.'<br/>';}
                        if (!$test && $ID !== 'K950001000_') {$conn->query($SQL2);}
                    }
                }
                echo '<br/><br/>';
            }
        }
        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>