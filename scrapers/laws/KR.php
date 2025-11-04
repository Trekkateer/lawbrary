<?php //South Korea

    // WARNING: Takes up too much memory.

    //Settings
    $test = false; $scraper = 'KR';
    $start = 0; //which law to start from
    $step = 1000; //How many laws per page
    //$limit = array('ko'=>153557, 'en'=>2952); //Total number of laws desired. Current max is 153326 for Korean and 2879 for English

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';
    $dom = new simple_html_dom();
    
    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Translates the types and origins
    $typeOriginMemo = array(
        '조선총독부제령' => array('Ordinance', '[{"ko":"조선총독부","en":"The Governor-General of Korea"}]'),
        '조선총독부칙령' => array('Supplementary Ordinance', '[{"ko":"조선총독부","en":"The Governor-General of Korea"}]'),
    );
    $types = array(
        '군정법률'       => 'Act of Martial Law',
        '법률'          => 'Act',

        '황실령'         => 'Imperial Decree',
        '긴급명령'       => 'Emergency Decree',
        '대통령령'       => 'Presidential Decree',
        '부칙령'        => 'Supplementary Ordinance',
        '부흥부령'     => 'Revival Ordinance',

        '재정긴급처분'   => 'Financial Emergency Measures',
        '대통령긴급조치' => 'Presidential Emergency Measures',

        '각령'          => 'Order',
        '군정명령'      => 'Military Order',
        '대통령긴급재정경제명령' => 'Presidential Emergency Financial and Economic Order',

        '군정법령'      => 'Military Ordinance',
        '법령'          => 'Ordinance',
        '령'            => 'Ordinance',

        '군정포고'      => 'Proclamation of Martial Law',
        '포고'          => 'Proclamation',

        '입법회의규칙' => 'Rules of the Legislative Assembly',
        '대법원규칙'    => 'Supreme Court Rules',
        '헌법재판소규칙' => 'Rules of the Constitutional Court',
        '규칙'          => 'Rules',

        '헌법'           => 'Constitutional Law',

        '군정기타'      => 'Military Other',
        '기타'          => 'Other'
    );
    $origins = array(//TODO: check all of these
        '감사원'           => '{"ko":"감사원", "en":"The Board of Audit and Inspection"}',

        '병무청'           => '{"ko":"병무청", "en":"The Manpower Administration"}',
        '농촌진흥청'       => '{"ko":"농촌진흥청", "en":"The Rural Development Administration"}',
        '기상청'           => '{"ko":"기상청", "en":"The Korea Meteorological Administration"}',
        '국가유산청'       => '{"ko":"국가유산청", "en":"The Cultural Heritage Administration"}',
        '방위사업청'       => '{"ko":"방위사업청", "en":"The Defense Acquisition Program Administration"}',

        '새만금개발청'     => '{"ko":"새만금개발청", "en":"The Saemangeum Development and Investment Agency"}',
        '재외동포청'       => '{"ko":"재외동포청", "en":"The Overseas Koreans Agency"}',
        '소방청'           => '{"ko":"소방청", "en":"The National Fire Agency"}',
        '행정중심복합도시건설청' => '{"ko":"행정중심복합도시건설청", "en":"The Administrative City Construction Agency"}',

        '국회'             => '{"ko":"국회", "en":"The National Assembly"}',

        '경제기획원'        => '{"ko":"경제기획원", "en":"The Economic Planning Board"}',

        '방송통신위원회'    => '{"ko":"방송통신위원회", "en":"The Korea Communications Commission"}',
        '국가교육위원회'    => '{"ko":"국가교육위원회", "en":"The National Education Commission"}',
        '중앙선거관리위원회' => '{"ko":"중앙선거관리위원회", "en":"The Central Election Commission"}',
        '선거관리위원회'    => '{"ko":"선거관리위원회", "en":"The Election Commission"}',
        '국가인권위원회'    => '{"ko":"국가인권위원회", "en":"The National Human Rights Commission of Korea"}',
        '국민권익위원회'    => '{"ko":"국민권익위원회", "en":"The Anti-Corruption and Civil Rights Commission"}',
        '원자력안전위원회'  => '{"ko":"원자력안전위원회", "en":"The Nuclear Safety and Security Commission"}',
        '금융위원회'       => '{"ko":"금융위원회", "en":"The Financial Services Commission"}',
        '공정거래위원회'    => '{"ko":"공정거래위원회", "en":"The Fair Trade Commission"}',
        '진실화해를위한과거사정리위원회' => '{"ko":"진실화해를위한과거사정리위원회", "en":"The Truth and Reconciliation Commission"}',
        '개인정보보호위원회' => '{"ko":"개인정보보호위원회", "en":"The Personal Information Protection Commission"}',
        '가습기살균제사건과4.16세월호참사특별조사위원회' => '{"ko":"가습기살균제사건과4.16세월호참사특별조사위원회", "en":"The Special Investigation Commission on the Humidifier Disinfectant Incident and the April 16 Sewol Ferry Disaster"}',

        '친일반민족행위진상규명위원회' => '{"ko":"친일반민족행위진상규명위원회", "en":"The Committee for the Investigation of Pro-Japanese and Anti-National Activities"}',
        '제2의건국범국민추진위원회' => '{"ko":"제2의건국범국민추진위원회", "en":"The Second National Foundation Promotion Committee"}',
        '5.18민주화운동진상규명조사위원회' => '{"ko":"5.18민주화운동진상규명조사위원회", "en":"The May 18th Democratic Uprising Truth Commission"}',

        '국가재건최고회의'  => '{"ko":"국가재건최고회의", "en":"The Supreme Council for National Reconstruction"}',
        '국가안전보장회의'  => '{"ko":"국가안전보장회의", "en":"The National Security Council"}',
        '민주평화통일자문회의사무처' => '{"ko":"민주평화통일자문회의사무처", "en":"The Secretariat of the National Unification Advisory Council"}',
        '국무원'           => '{"ko":"국무원", "en":"The State Council"}',

        '헌법재판소'       => '{"ko":"헌법재판소", "en":"The Constitutional Court"}',
        '대법원'           => '{"ko":"대법원", "en":"The Supreme Court"}',

        '관세청'           => '{"ko":"관세청", "en":"The Korea Customs Service"}',

        '해양경찰청'       => '{"ko":"해양경찰청", "en":"The Korea Coast Guard"}',
        '경찰청'           => '{"ko":"경찰청", "en":"The National Police Department"}',

        '조선총독부'       => '{"ko":"조선총독부", "en":"The Governor-General of Korea"}',
        '임시정부'         => '{"ko":"임시정부", "en":"The Provisional Government"}',
        '서울특별시'       => '{"ko":"서울특별시", "en":"Seoul Metropolitan Government"}',

        '우주항공청'       => '{"ko":"우주항공청", "en":"The Korea Aerospace Research Institute"}',

        '농수산부'         => '{"ko":"농수산부", "en":"The Ministry of Agriculture and Fisheries"}',
        '농림축산식품부'    => '{"ko":"농림축산식품부", "en":"The Ministry of Agriculture, Food, and Rural Affairs"}',
        '농림수산식품부'    => '{"ko":"농림수산식품부", "en":"The Ministry of Agriculture, Food, and Rural Affairs"}',
        '농림수산부'       => '{"ko":"농림수산부", "en":"The Ministry of Agriculture, Forestry, and Fisheries"}',
        '농림부'           => '{"ko":"농림부", "en":"The Ministry of Agriculture and Forestry"}',
        '상공자원부'       => '{"ko":"상공자원부", "en":"The Ministry of Commerce, Industry and Energy"}',
        '건설부'           => '{"ko":"건설부", "en":"The Ministry of Construction"}',
        '문화부'           => '{"ko":"문화부", "en":"The Ministry of Culture"}',
        '문화공보부'       => '{"ko":"문화공보부", "en":"The Ministry of Culture and Public Information"}',
        '문화관광부'       => '{"ko":"문화관광부", "en":"The Ministry of Culture and Tourism"}',
        '상공부'           => '{"ko":"상공부", "en":"The Ministry of Trade, Industry and Energy"}',
        '문화체육관광부'   => '{"ko":"문화체육관광부", "en":"The Ministry of Culture, Sports and Tourism"}',
        '문화체육부'       => '{"ko":"문화체육부", "en":"The Ministry of Culture and Sports"}',
        '체육청소년부'     => '{"ko":"체육청소년부", "en":"The Ministry of Culture, Sports and Youth"}',
        '국방부'           => '{"ko":"국방부", "en":"The Ministry of Defense"}',
        '교육부'           => '{"ko":"교육부", "en":"The Ministry of Education"}',
        '문교부'           => '{"ko":"문교부", "en":"The Ministry of Education"}',
        '교육인적자원부'   => '{"ko":"교육인적자원부", "en":"The Ministry of Education and Human Resources Development"}',
        '교육과학기술부'   => '{"ko":"교육과학기술부", "en":"The Ministry of Education, Science and Technology"}',
        '고용노동부'       => '{"ko":"고용노동부", "en":"The Ministry of Employment and Labor"}',
        '동력자원부'      => '{"ko":"동력자원부", "en":"The Ministry of Energy and Resources"}',
        '환경부'          => '{"ko":"환경부", "en":"The Ministry of Environment"}',
        '재무부'           => '{"ko":"재무부", "en":"The Ministry of Finance"}',
        '식품의약품안전처' => '{"ko":"식품의약품안전처", "en":"The Ministry of Food and Drug Safety"}',
        '외교부'           => '{"ko":"외교부", "en":"The Ministry of Foreign Affairs"}',
        '외무부'           => '{"ko":"외무부", "en":"The Ministry of Foreign Affairs"}',
        '외교통상부'       => '{"ko":"외교통상부", "en":"The Ministry of Foreign Affairs and Trade"}',
        '여성부'          => '{"ko":"여성부", "en":"The Ministry of Gender Equality"}',
        '여성가족부'       => '{"ko":"여성가족부", "en":"The Ministry of Gender Equality and Family"}',
        '행정자치부'      => '{"ko":"행정자치부", "en":"The Ministry of Government Administration and Home Affairs"}',
        '보건부'          => '{"ko":"보건부", "en":"The Ministry of Health"}',
        '보건사회부'      => '{"ko":"보건사회부", "en":"The Ministry of Health and Social Affairs"}',
        '보건복지부'      => '{"ko":"보건복지부", "en":"The Ministry of Health and Welfare"}',
        '보건복지가족부'  => '{"ko":"보건복지가족부", "en":"The Ministry of Health, Welfare and Family Affairs"}',
        '산업자원부'      => '{"ko":"산업자원부", "en":"The Ministry of Industry and Resources"}',
        '과학기술정보통신부' => '{"ko":"과학기술정보통신부", "en":"The Ministry of Science and ICT"}',
        '정보통신부'      => '{"ko":"정보통신부", "en":"The Ministry of Information and Communication"}',
        '내무부'          => '{"ko":"내무부", "en":"The Ministry of the Interior"}',
        '행정안전부'       => '{"ko":"행정안전부", "en":"The Ministry of the Interior and Safety"}',
        '법무부'           => '{"ko":"법무부", "en":"The Ministry of Justice"}',
        '지식경제부'       => '{"ko":"지식경제부", "en":"The Ministry of Knowledge Economy"}',
        '국토해양부'       => '{"ko":"국토해양부", "en":"The Ministry of Land, Transport and Maritime Affairs"}',
        '노동부'          => '{"ko":"노동부", "en":"The Ministry of Labor"}',
        '해양수산부'       => '{"ko":"해양수산부", "en":"The Ministry of Oceans and Fisheries"}',
        '국가보훈부'      => '{"ko":"국가보훈부", "en":"The Ministry of Patriots and Veterans Affairs"}',
        '인사혁신처'      => '{"ko":"인사혁신처", "en":"The Ministry of Personnel Management"}',
        '기획재정부'       => '{"ko":"기획재정부", "en":"The Ministry of Planning and Finance"}',
        '공보부'          => '{"ko":"공보부", "en":"The Ministry of Public Information"}',
        '안전행정부'      => '{"ko":"안전행정부", "en":"The Ministry of Public Safety and Security"}',
        '국민안전처'      => '{"ko":"국민안전처", "en":"The Ministry of Public Safety and Security"}',
        '해당부처'         => '{"ko":"해당부처", "en":"The Relevant Ministry"}',
        '중소벤처기업부'   => '{"ko":"중소벤처기업부", "en":"The Ministry of SMEs and Startups"}',
        '과학기술부'      => '{"ko":"과학기술부", "en":"The Ministry of Science and Technology"}',
        '미래창조과학부'   => '{"ko":"미래창조과학부", "en":"The Ministry of Science, ICT and Future Planning"}',
        '체육부'          => '{"ko":"체육부", "en":"The Ministry of Sports"}',
        '사회부'          => '{"ko":"사회부", "en":"The Ministry of Social Affairs"}',
        '재정경제부'      => '{"ko":"재정경제부", "en":"The Ministry of Strategy and Finance"}',
        '체신부'          => '{"ko":"체신부", "en":"The Ministry of Posts and Telecommunications"}',
        '산업통상자원부'   => '{"ko":"산업통상자원부", "en":"The Ministry of Trade, Industry and Energy"}',
        '국토교통부'       => '{"ko":"국토교통부", "en":"The Ministry of Land, Infrastructure and Transport"}',
        '통상산업부'      => '{"ko":"통상산업부", "en":"The Ministry of Trade and Industry"}',
        '건설교통부'      => '{"ko":"건설교통부", "en":"The Ministry of Transportation and Construction"}',
        '교통부'          => '{"ko":"교통부", "en":"The Ministry of Transportation"}',
        '통일부'          => '{"ko":"통일부", "en":"The Ministry of Unification"}',

        '질병관리청'       => '{"ko":"질병관리청", "en":"The Office of Disease Management"}',
        '국무조정실'       => '{"ko":"국무조정실", "en":"The Office for Government Policy Coordination"}',
        '고위공직자범죄수사처' => '{"ko":"고위공직자범죄수사처", "en":"The Corruption Investigation Office for High-ranking Officials"}',
        '법제처'           => '{"ko":"법제처", "en":"The Office of Legal Affairs"}',
        '특허청'           => '{"ko":"특허청", "en":"The Patent Office"}',
        '대통령실'         => '{"ko":"대통령실", "en":"The Office of the President"}',
        '국무총리실'       => '{"ko":"국무총리실", "en":"The Office of the Prime Minister"}',
        '국가안보실'       => '{"ko":"국가안보실", "en":"The National Security Office"}',
        '특임장관실'       => '{"ko":"특임장관실", "en":"The Office of the Special Minister"}',
        '대검찰청'         => '{"ko":"대검찰청", "en":"The Office of the Supreme Prosecutor"}',

        '정부산하기관및위원회' => '{"ko":"정부산하기관및위원회", "en":"Government-affiliated Agencies and Committees"}',

        '대통령경호처'    => '{"ko":"대통령경호처", "en":"The Presidential Security Service"}',
        '대통령'          => '{"ko":"대통령", "en":"The President"}',
        '총리'            => '{"ko":"총리", "en":"The Prime Minister"}',

        '국무총리비서실' => '{"ko":"국무총리비서실", "en":"The Secretariat of the Prime Minister"}',

        '국가정보원'      => '{"ko":"국가정보원", "en":"The National Intelligence Service"}',
        '국세청'          => '{"ko":"국세청", "en":"The National Tax Service"}',
        '조달청'          => '{"ko":"조달청", "en":"The Public Procurement Service"}',
        '산림청'          => '{"ko":"산림청", "en":"The Korea Forest Service"}',

        '통계청'           => '{"ko":"통계청", "en":"Statistics Korea"}',

        '기타'             => '{"ko":"기타", "en":"Others"}',
    );
    //Fills up the memoization array
    /*foreach($types as $keyT => $valueT) {
        foreach($origins as $keyO => $valueO) {
            $typeOriginMemo[$keyT.$keyO] = array($valueT, array(json_decode($valueO, true)));
        }
        $typeOriginMemo[$keyT] = array($valueT, 'NULL');
    }
    echo json_encode($typeOriginMemo, JSON_UNESCAPED_UNICODE);*/

    //Sets up the querying function
    $HTTP_Call = function ($path, $page) use (&$step) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.law.go.kr/'.$path,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('q' => '*','outmax' => $step,'pg' => $page,'fsort'=>'20,11,31','section' => 'lawNm','lsiSeq' => 0),
            CURLOPT_HTTPHEADER => array(
                'Cookie: elevisor_for_j2ee_uid=2utyv1bjtwwm1; JSESSIONID=W2tMUjkIKJHhOLQby76G4jmv.LSW2'
            ),
        ));
        $response = new simple_html_dom(curl_exec($curl));
        curl_close($curl);
        return $response;
    };

    //Gets the other language
    $otherlang = array('ko'=>'en', 'en'=>'ko');

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["KR"]';
    $publisher = '{"ko":"대한민국 법제처", "en":"The National Law Information Center of Korea"}';

    //Loops through the languages
    $realPaths = array('ko'=>'lsSc.do', 'en'=>'LSW/eng/engLsSc.do');
    foreach(array('ko'=>'lsScListR.do', 'en'=>'LSW/eng/engLsScListWideR.do') as $lang=>$path) {
        //Gets the limit
        $limit = $limit[$lang] ?? preg_replace('/\D/', '', $HTTP_Call($path, 0)->find('#readNumDiv', 0)->plaintext); echo "The Limit is ".$limit."<br>";
        for ($page = floor($start/$step); $page < ceil($limit); $page++) {
            //Gets the laws
            $laws = $HTTP_Call($path, $page)->find('#viewHeightDiv', 0)->find('table', 0)->find('tbody', 0)->find('tr');
            foreach($laws as $row_num => $law) {
                $vals = array(//Array stores variables from a table
                    'Row' => '',
                    'Title' => '',
                    'enactDate' => '',
                    'Type' => '',
                    'ID' => '',
                    'enforceDate' => '',
                    'Revision' => '',
                    'Origin' => '',
                );

                //Gets the datapoints from cells
                $cells = $law->find('td');
                for($cell = 0; $cell <= 7; $cell++) {
                    //Gets the ID. Pulls the first sequence of numbers from the onclick function
                    if ($cell === 1) $ID = $scraper.':'.preg_split("/\D+/", explode('WideAll(', explode('liBgcolor0', trim($cells[$cell]->find('a', 0)->onclick))[0])[1])[1];
                    //Puts the rest of the vals into dropper array
                    $vals[array_keys($vals)[$cell]] = trim(strip_tags($cells[$cell]->innertext));
                }
                echo "Type: <span>".$vals['Type']."</span><br>";
                echo "Origin: <span>".$vals['Origin']."</span><br>";

                //Gets the source and status
                $vals['Source'] = 'https://www.law.go.kr/'.$realPaths[$lang].'?query='.str_replace(' ', '+', $vals['Title']).'#liBgcolor0';
                $status = 'Valid';

                //Finalizes date
                $vals['enactDate'] = $lastactDate = strtr($vals['enactDate'], ['. '=>'-', '.'=>'']);
                $vals['enforceDate'] = strtr($vals['enforceDate'], ['. '=>'-', '.'=>'']) ?? $vals['enactDate'];

                //Gets the regime
                switch(true) {
                    case strtotime('12 October 1897') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('22 August 1910'):
                        $regime = '{"ko":"대한제국","en":"The Empire of Korea"}';
                        break;
                    case strtotime('22 August 1910') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('15 August 1945'):
                        $regime = '{"ko":"일제강점기","en":"The Empire of Japan"}';
                        break;
                    case strtotime('15 August 1945') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('15 August 1948'):
                        $regime = '{"ko":"미군정","en":"The United States Military Government in Korea"}';
                        break;
                    case strtotime('15 August 1948') < strtotime($vals['enactDate']) && strtotime($vals['enactDate']) < strtotime('today'):
                        $regime = '{"ko":"대한민국","en":"The Republic of Korea"}';
                        break;
                }

                //Translates the type and origin
                $type = 'NULL'; $origin = 'NULL';
                $typeOrigin = strtr(trim($vals['Type']).trim($vals['Origin']), [' '=>'']);
                if ($typeOrigin !== '') {
                    if ($typeOriginMemo[$typeOrigin]) {
                        $type = $typeOriginMemo[$typeOrigin][0];
                        $origin = $typeOriginMemo[$typeOrigin][1];
                    } else {
                        //Does the type
                        foreach($types as $tKey => $tValue) {
                            if (str_contains($typeOrigin, $tKey)) {
                                $type = $tValue;
                                $typeOrigin = str_replace($tKey, '', $typeOrigin);
                                break;
                            }
                        }
                        //Does the origin
                        $origin = [];
                        foreach($origins as $oKey => $oValue) {
                            if (str_contains($typeOrigin, $oKey)) {
                                $origin[] = json_decode($oValue);
                                $typeOrigin = str_replace($oKey, '', $typeOrigin);
                            }
                        }
                        $origin = count($origin) > 0 ? json_encode($origin, JSON_UNESCAPED_UNICODE) : 'NULL';

                        //Stores the new value in the memoization array
                        $typeOriginMemo[strtr(trim($vals['Type']).trim($vals['Origin']), [' '=>''])] = array($type, $origin);

                        //Debugging output
                        echo "Added ".strtr(trim($vals['Type']).trim($vals['Origin']), [' '=>'']) ." => ".json_encode($typeOriginMemo[strtr(trim($vals['Type']).trim($vals['Origin']), [' '=>''])], JSON_UNESCAPED_UNICODE)." to the memo<br/><br/>";
                        if ($typeOrigin !== '') {echo "ALERT! Unknown type/origin: <span>".$typeOrigin."</span>!! <br/>";}
                    }
                }
                //Checks if the document is an amendment
                $isAmend = str_contains($vals['Revision'], '개정') ? 1:0;

                //Makes sure there are no appostophes in the title or origin
                $vals['Title'] = fixQuotes($vals['Title'], $lang);
                $vals['Source'] = fixQuotes($vals['Source'], $lang);

                //Makes sure that the law is not an ammendment
                //Creates SQL to check if the law is already stored
                $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $vals['Title'];
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                        //JSONifies the href
                        $compoundedSource = json_decode($row['source'], true);
                        $compoundedSource[$lang] = $vals['Source'];
                        $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the values
                    $name = '{"'.$lang.'":"'.$vals['Title'].'"}';
                    $source = '{"'.$lang.'":"'.$vals['Source'].'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`)
                            VALUES ('".$vals['enactDate']."', '".$vals['enforceDate']."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$origin."', '".$publisher."', '".$type."', '".$status."', '".$source."')";
                }
                
                //Executes the SQL
                echo $vals['Row'].'. '.$SQL2.'<br/><br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }

    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>