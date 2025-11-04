<?php //Kazakhstan
    //!!Getting the type is very difficult and requires many special exceptions!!

    //Settings
    $test = true; $scraper = 'KZ';
    $langs = array('kaz'=>'kk', 'rus'=>'ru'/*, 'eng'=>'en'*/); //Which languages to use
    $start = 1947; //Which year to start from
    $step = 100; //How many laws are on each page
    $limitY = null; //Which year to stop at

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $html_dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Translates the months
    $months = array(
        'қаңтарында'=>'01', 'қаңтардағы'=>'01', 'қаңтар'=>'01',
        'ақпандағы'=>'02', 'ақпан'=>'02',
        'наурызда'=>'03', 'наурыздағы'=>'03', 'наурыз'=>'03',
        'сәуірде'=>'04', 'сәуірдегі'=>'04', 'сәуiрдегi'=>'04', 'сәуiрдегі'=>'04', 'сәуір'=>'04', 'сәуiр'=>'04',
        'мамырдағы'=>'05', 'мамыр'=>'05',
        'маусымдағы'=>'06', 'маусым'=>'06',
        'шілдегі'=>'07', 'шілдедегі'=>'07', 'шiлдедегi'=>'07', 'шiлдедегі'=>'07', 'шiлде'=>'07', 'шілде'=>'07',
        'тамызында'=>'08', 'тамыздағы'=>'08', 'тамыздағы'=>'08', 'тамыз'=>'08',
        'қыркүйектегі'=>'09', 'қыркүйектегi'=>'09', 'қыркүйекте'=>'09', 'қыркүйек'=>'09',
        'қазандағы'=>'10', 'қазан'=>'10',
        'қарашадағы'=>'11', 'қарашасындағы'=>'11', 'қарашада'=>'11', 'қараша'=>'11',
        'желтоқсандағы'=>'12', 'желтоқсанында'=>'12', 'желтоқсанда'=>'12', 'желтоқсан'=>'12'
    );
    //Translates the types
    $types_origins = array(
        'kk' => [
            'Қаулы Жоғарғы Соты Пленумы' => ['Resolution', '\'The Supreme Court Plenum\''],
            'БҰҰ-ның Бас Ассамблея Бұрыштамасымен' => ['Resolution', '\'The UN General Assembly\'', 'UN'],

            'Конституциялық Кеңесінің' => ['NULL', '\'The Constitutional Council\''],
            'Конституциялық Кеңесі'    => ['NULL', '\'The Constitutional Council\''],
            'Жоғарғы Советінің'        => ['NULL', '\'The Supreme Council\''],
            'Жоғарғы Соты Пленумының'  => ['NULL', '\'The Supreme Court Plenum\''],
            'Жоғарғы Соты Пленумы'     => ['NULL', '\'The Supreme Court Plenum\''],
            'Жоғарғы Сотының'          => ['NULL', '\'The Supreme Court\''],
            'Жоғарғы Соты'             => ['NULL', '\'The Supreme Court\''],
            'Президентiнiң'            => ['NULL', '\'The President\''],

            'Білім, ғылым және мәдениет мәселелері бойынша Біріккен Ұлттар Ұйымы, Париж,' => ['NULL', '\'UNESCO\'', 'UN'],
            'Біріккен Ұлттар Ұйымы Бас Ассамблеясының' => ['NULL', '\'The UN General Assembly\'', 'UN'],
            'БҰҰ Бас Ассамблеясының'   => ['NULL', '\'The UN General Assembly\'', 'UN'],
            'БҰҰ Бас Ассамблеяның'     => ['NULL', '\'The UN General Assembly\'', 'UN'],
            'БҰҰ-ның Бірінші Конгрессінде' => ['NULL', '\'The First Congress of the UN\'', 'UN'],
            'БҰҰ-ның Экономикалық және Әлеуметтік Кеңесінің' => ['NULL', '\'The Economic and Social Council of the UN\'', 'UN'],
            'Бас Ассамблеяның'         => ['NULL', '\'The UN General Assembly\'', 'UN'],
            'Біріккен Ұлттар Ұйымының' => ['NULL', '\'The United Nations\'', 'UN'],

            'Келісім'                  => ['Agreement', 'NULL'],
            'Конституциялық заңы'      => ['Constitutional Law', 'NULL'],
            'Заңы'                     => ['Law', 'NULL'],
            'Кодексі'                  => ['Code', 'NULL'],
            'Декларациясы'             => ['Declaration', 'NULL'],
            'Декларация'               => ['Declaration', 'NULL'],
            'Жарлығы'                  => ['Decree', 'NULL'],
            'Конституциялық Заң күші бар Жарлық' => ['Decree with the force of Constitutional Law', 'NULL'],
            'Жолдауы'                  => ['Message', 'NULL'],
            'қарарымен'                => ['Resolution', 'NULL'],
            'қарары'                   => ['Resolution', 'NULL'],
            'Қаулысы'                  => ['Resolution', 'NULL'],
            'қаулысы'                  => ['Resolution', 'NULL'],
            'Қаулы'                    => ['Resolution', 'NULL'],
        ],
        'ru' => [],
        'en' => []
    );

    //Sanitizes the name
    $sanitizeName = array(
        '(гатт' => '(Гатт',
        ' () ' => '',
    );
    //Sanitizes the footer
    $sanitizeFooter = array(
        '1961-жылғы 7-желтоқсан' => '1961 жылғы 7 желтоқсан',
        'қарары. 1984/47, 1984'=>'қарары. 1984 жылғы 5 мамыр',
        '5-i қарашаның, 1991'=>'1991 жылғы 5 қараша',
        '16-сы қазанның, 1991'=>'1991 жылғы 16 қазан',
        'N 23-тамыз, 1991'=>'1991 жылғы 23 тамыз',
        'қаңтардағы 11, 19994'=>'1994 жылғы 11 қаңтар',
        '1 15, 1998'=>'1998 жылғы 15 қаңтар',

        'Қазақ ССР' => '',
        'Қазақ КСР' => '',
        'Қазақcтан Республикасы' => '',
        'Қазақстан Республикасы' => '',
        'Қазақ Советтiк Социалистiк Республикасы' => '',
        'Қазақстан Республикасының' => '',
        'Қазақстан Республикасы' => '',

        ' () ' => '',
    );

    //Defines the positions of the date components
    $yearNames = array(
        'kk' => ['жылғы', 'жылдың', 'ж.'],
        'ru' => ['года', 'год', 'г.'],
    );
    $d = [
        'kk' => ['Y'=>-1, 'm'=>2, 'd'=>1],
        'ru' => ['Y'=>-1, 'm'=>-2, 'd'=>-3],
    ];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["KZ"]';
    $publisher = '{"kk":"Қазақстан Республикасы Әділет министрлігі Заңнама және құқықтық ақпарат институты", "ru":"Министерство юстиции Республики Казахстан Институт законодательства и правовой информации", "en":"The Ministry of Justice of the Republic of Kazakhstan Institute of Legislation and Legal Information"}';

    //Loops through languages
    foreach ($langs as $locale => $lang) {
        $limitY = $limitY ?? date('Y');
        for ($year = $start; $year <= $limitY; $year ++) {echo 'Y: '.$year.'<br/>';
            //Gets the limit of the pages
            $limitP = explode('&page=', explode('&pagesize=', file_get_html('https://adilet.zan.kz/kaz/index/docs/dt='.$year.'-')->find('a.nextpostslink', 1)->href ?? '&page=1&pageSize=')[0])[1];
            for ($page = 1; $page <= $limitP; $page++) {
                //Processes the data
                $laws = file_get_html('https://adilet.zan.kz/kaz/index/docs/dt='.$year.'-&page='.$page)->find('div.serp', 0)->find('div.post_holder');
                foreach ($laws as $law) {
                    $footer = trim(str_replace(array_keys($sanitizeFooter), array_values($sanitizeFooter), $law->find('p', 0)->plaintext), ' .'); echo '<br/>'.$footer.'<br/>';
                    //Gets the date
                    $enactDate = NULL;
                    $explodeDate = explode(' ', $footer);
                        foreach ($explodeDate as $partNum => $part) {//Gets the date based on its relation to a certain word
                            if ($part === 'жылғы' || $part === 'жылдың' || $part === 'ж.' ) {
                                $enactDate = $explodeDate[$partNum+$d[$lang]['Y']].'-'.$months[$explodeDate[$partNum+$d[$lang]['m']]].'-'.$explodeDate[$partNum+$d[$lang]['d']];
                                break;
                            }
                        }
                    $enactDate = $enforceDate = $lastactDate = $enactDate ?? $year.'-01-01';
                    //Gets the ID and name
                    $ID = $scraper.':'.explode('docs/', $law->find('a', 0)->href)[1];
                    $name = $law->find('a', 0)->plaintext; $name = str_replace(array_keys($sanitizeName), array_values($sanitizeName), ($name === strtoupper($name) ? mb_substr($name, 0, 1).mb_strtolower(mb_substr($name, 1)):$name));
                    //Gets the type, origin, and country
                    $type = 'NULL';
                    $origin = 'NULL';
                    $country = array('KZ');
                    $footer = trim(explode($year, $footer)[0]); echo $footer.'<br/>';
                    foreach ($types_origins[$lang] as $key => $val) {
                        if (str_contains($footer, $key)) {
                            if ($type === 'NULL') {$type = $val[0];}
                            if ($origin === 'NULL') {$origin = $val[1];}
                            if (isset($val[2])) {$country[] = $val[2];}
                            $footer = trim(str_replace($key, '', $footer));
                        }
                    }
                    if ($type === 'NULL') {//In case the type is found after the date
                        foreach ($types_origins[$lang] as $key => $val) {
                            if (substr($name, strlen($name), -strlen($key)) === $key) {
                                if ($type === 'NULL') {$type = $val[0];}
                                if ($origin === 'NULL') {$origin = $val[1];}
                                if (isset($val[2])) {$country[] = $val[2];}
                                $name = str_replace($key, '', $name);
                            }
                        }
                    }
                    echo $footer.'<br/>';
                    $country = json_encode($country, JSON_UNESCAPED_UNICODE);
                    //Gets the regime
                    if (strtotime($enactDate) < strtotime('16 December 1991')) {
                        $regime = '{"kk":"Қазақ Советтік Социалистік Республикасы", "ru":"Казахская Советская Социалистическая Республика", "en":"The Kazakh Soviet Socialist Republic"}';
                    } else {$regime = '{"kk":"Қазақстан Республикасы", "ru":"Республика Казахстан", "en":"The Republic of Kazakhstan"}';}
                    //Gets the rest of the values
                    //$type = $types[trim(explode($year, $law->find('p', 0)->innertext)[0], ' .')];
                    $status = $law->find('span.status', 0)->plaintext === 'Күшін жойған' ? $status = 'Canceled':'Valid';
                    $source = 'https://adilet.zan.kz'.$law->find('a', 0)->href;

                    //Makes sure there are no appostophes in the title
                    if ($name[0] === '"') {$name = '«'.mb_substr($name, 1);}
                    $name = strtr($name, array(' "'=>' «', '"'=>'»', "'"=>"’"));

                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
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

                            $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$origin."', '".$publisher."', '".$type."', '".$status."', '".$source."')";
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
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>