<html><body>
    <?php
        //Settings
        $test = true; $country = 'TW';
        $start = 0;//Which page to start from
        $step = 60;//How many laws there are on each page
        $limit = null;//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Fixes the date
        function caltrans($date, $cal) {
            if ($cal === 'zh') {
                return ((int)explode('-', $date)[0]+1911).'-'.explode('-', $date)[1].'-'.explode('-', $date)[2];
            } else {return $date;}
        }

        //Translates types into English
        $types = ['修正'=>'Amendment',
                  '公告'=>'Announcement',
                  '函'=>'Letter',
                  '令'=>'Order',
            
                  '法律'=>'Act',
                  '法規草案'=>'Draft',
                  '法規命令'=>'Order',
                  '地方法規'=>'Local Regulation',
                  '行政規則'=>'Administrative Rule',

                  'All'=>'Act',
                  'Act'=>'Act',
                  'Drafts'=>'Draft',
                  'Regulations'=>'Order',
                  'Directions'=>'Administrative Rule'
        ];
        //Translates the origins
        $sanitizeOrigins = ['、'=>'', '／'=>''];
        $origins = [
            '國家教育研究院' => 'The National Academy for Educational Research',

            '動植物防疫檢疫署'     => 'The Animal, Plant Health Inspection and Quarantine Administration',
            '中央健康保險署'       => 'The National Health Insurance Administration',
            '商業發展署'          => 'The Business Development Administration',
            '矯正署'              => 'The Corrections Administration',
            '關務署'              => 'The Customs Administration',
            '農糧署'              => 'The Agriculture and Food Administration',
            '食品藥物管理署'       => 'The Food and Drug Administration',
            '國民健康署'          => 'The National Health Administration',
            '農村發展及水土保持署' => 'The Rural Development and Soil Conservation Administration',
            '數位產業署'          => 'The Digital Industry Administration',
            '漁業署'              => 'The Fisheries Administration',
            '消防署'              => 'The Fire Department',
            '林業及自然保育署'     => 'The Forestry and Nature Conservation Administration',
            '移民署'              => 'The Immigration Administration',
            '資通安全署'          => 'The Information and Communication Security Administration',
            '國際貿易署'          => 'The International Trade Administration',
            '國民及學前教育署'     => 'The National Administration of Preschool Education',
            '能源署'              => 'The Energy Administration',
            '勞動力發展署'        => 'The Labor Force Development Administration',
            '產業發展署'          => 'The Industrial Development Administration',
            '職業安全衛生署'      => 'The Occupational Safety and Health Administration',
            '水利署北區水資源分署' => 'The Water Resources Northern District Office',
            '中小及新創企業署'     => 'The Small and Medium Enterprise Administration',
            '體育署'              => 'The Sports Administration',
            '國有財產署'          => 'The State Property Administration',
            '觀光署'              => 'The Tourism Administration',
            '中區水資源分署'       => 'The Water Resources Central District Office',
            '水利署'              => 'The Water Resources Administration',
            '青年發展署'          => 'The Youth Development Administration',

            '中央銀行' => 'The Central Bank',

            '檔案管理局'          => 'The Archives Bureau',
            '民用航空局'          => 'The Civil Aeronautics Authority',
            '影視及流行音樂產業局' => 'The Film, Television and Popular Music Industry Bureau',
            '文化資產局'          => 'The Bureau Cultural Heritage',
            '高速公路局'          => 'The Highways Bureau',
            '公路局'              => 'The Highway Bureau',
            '產業園區管理局'       => 'The Industrial Park Bureau',
            '智慧財產局'          => 'The Intellectual Property Bureau',
            '國土管理署'          => 'The Bureau of Land Administration',
            '法制局'              => 'The Legal Affairs Bureau',
            '國家安全局'          => 'The National Security Bureau',
            '新北市政府地政局'     => 'The New Taipei City Government Land Administration Bureau',
            '新北市政府城鄉發展局' => 'The New Taipei City Government Urban and Rural Development Bureau',
            '新北市政府水利局'     => 'The New Taipei City Government Water Resources Bureau',
            '警政署航空警察局'     => 'The Aviation Police Bureau',
            '鐵道局'              => 'The Railway Bureau',
            '標準檢驗局'          => 'The Standard Inspection Bureau',
            '航港局'              => 'The Bureau of Ports',

            '農業科技園區管理中心'     => 'The Agricultural Science and Technology Park Management Center',
            '客家文化發展中心'        => 'The Hakka Cultural Development Center',
            '勞動力發展署技能檢定中心' => 'The Labor Force Development Bureau Skill Testing Center',
            '地質調查及礦業管理中心'   => 'The Geological Survey and Mining Bureau',
            '技能檢定中心'            => 'The Skill Testing Center',
            '國立傳統藝術中心'        => 'The National Center for Traditional Arts',
            '國立臺灣工藝研究發展中心' => 'The National Taiwan Craft Research and Development Center',

            '中央選舉委員會'              => 'The Central Election Commission',
            '僑務委員會'                 => 'The Overseas Community Affairs Commission' ,
            '國家通訊傳播委員會'          => 'The National Communications Commission',
            '公平交易委員會'             => 'The Fair Trade Commission',
            '金融監督管理委員會'          => 'The Financial Supervisory Commission',
            '大陸委員會'                 => 'The Mainland Affairs Council',
            '國家發展委員會'              => 'The National Development Council',
            '國家科學及技術委員會'        => 'The National Science and Technology Commission',
            '核能安全委員會'              => 'The Nuclear Safety Commission',
            '海洋委員會'                  => 'The Ocean Affairs Council',
            '公共工程委員會'              => 'The Public Works Commission',
            '公務人員保障暨培訓委員會'     => 'The Civil Servants Protection and Training Commission',
            '國軍退除役官兵輔導委員會'     => 'The National Army Retired Officers and Soldiers Counseling Committee',
            '新北市政府研究發展考核委員會' => 'The New Taipei City Government Research and Development Assessment Committee',
            '客家委員會'                 => 'The Hakka Affairs Council',
            '原住民族委員會'              => 'The Council of Indigenous Peoples',

            '監察院' => 'The Control Yuan',
            '考試院' => 'The Examination Yuan',
            '行政院' => 'The Executive Yuan',
            '司法院' => 'The Judicial Yuan',

            '人事行政總處' => 'The Directorate-General of Personnel Administration',
            '主計總處'     => 'The Directorate-General of Budget, Accounting and Statistics',

            '苗栗區農業改良場'   => 'The Miaoli District Agricultural Improvement Farm',
            '臺中區農業改良場'   => 'The Taichung District Agricultural Improvement Farm',
            '桃園區農業改良場'   => 'The Taoyuan District Agricultural Improvement Farm',
            '茶及飲料作物改良場' => 'The Tea and Beverage Crop Improvement Farm',
            '種苗改良繁殖場'     => 'The Seed Improvement and Propagation Farm',

            '財團法人威權統治時期國家不法行為被害者權利回復基金會' => 'The Foundation for the Restoration of the Rights of Victims of Unlawful Acts during the Authoritarian Period',

            '新北市政府' => 'The New Taipei City Government',

            '農業試驗所'       => 'The Agricultural Testing Institute',
            '農業藥物試驗所'   => 'The Agricultural Drug Testing Institute',
            '生物多樣性研究所' => 'The Institute of Biodiversity Research',
            '工程材料技術所'   => 'The Institute of Engineering Materials Technology',
            '畜產試驗所'       => 'The Institute of Livestock Testing',
            '運輸研究所'       => 'The Institute of Transportation Research',

            '農業部'       => 'The Ministry of Agriculture',
            '審計部'       => 'The Ministry of Audit',
            '文化部'       => 'The Ministry of Culture',
            '國防部'       => 'The Ministry of National Defense',
            '數位發展部'   => 'The Ministry of Digital Development',
            '經濟部'       => 'The Ministry of Economic Affairs',
            '教育部'       => 'The Ministry of Education',
            '環境部'       => 'The Ministry of the Environment',
            '銓敘部'       => 'The Ministry of Examination',
            '衛生福利部'   => 'The Ministry of Health and Welfare',
            '衛生福利部'   => 'The Ministry of Health and Welfare', //Different UNICODE from above
            '衛生福利部'   => 'The Ministry of Health and Welfare', //Different UNICODE from above
            '財政部'       => 'The Ministry of Finance',
            '外交部'       => 'The Ministry of Foreign Affairs',
            '內政部'       => 'The Ministry of the Interior',
            '法務部'       => 'The Ministry of Justice',
            '勞動部'       => 'The Ministry of Labor',
            '交通部'       => 'The Ministry of Transportation',
            
            '國立彰化生活美學館'    => 'The National Changhua Living Arts Center',
            '國立新竹生活美學館'    => 'The National Hsinchu Living Arts Center',
            '國立臺南生活美學館'    => 'The National Tainan Living Arts Center',
            '國立臺灣藝術教育館'    => 'The National Taiwan Arts Education Center',
            '國家圖書館'            => 'The National Library',
            '國立臺灣圖書館'        => 'The National Library of Taiwan',
            '國家人權博物館'        => 'The National Museum of Human Rights',
            '國立臺灣文學館'        => 'The National Museum of Taiwan Literature',
            '國立海洋生物博物館'    => 'The National Museum of Marine Biology and Aquarium',
            '國立自然科學博物館'    => 'The National Museum of Natural Science',
            '國立故宮博物院'        => 'The National Palace Museum',
            '國立臺灣科學教育館'    => 'The National Museum of Science Education',
            '國立臺灣歷史博物館'    => 'The National Museum of Taiwanese History',
            '國立臺灣史前文化博物館' => 'The National Museum of Taiwanese Prehistory',

            '阿里山國家風景區管理處'          => 'The Ali Mountain National Scenic Area Management Office',
            '國立中正紀念堂管理處'            => 'The National Chiang Kai-shek Memorial Hall Management Office',
            '大鵬灣國家風景區管理處'          => 'The Dapeng Bay National Scenic Area Management Office',
            '東部海岸國家風景區管理處'        => 'The East Coast National Scenic Area Management Office',
            '花東縱谷國家風景區管理處'        => 'The Hualien-Taitung National Scenic Area Management Office',
            '國家公園署墾丁國家公園管理處'     => 'The Kenting National Park Management Office',
            '國家公園署金門國家公園管理處'     => 'The Kinmen National Park Management Office',
            '茂林國家風景區管理處'            => 'The Maolin National Scenic Area Management Office',
            '馬祖國家風景區管理處'            => 'The Matsu National Scenic Area Management Office',
            '北海岸及觀音山國家風景區管理處'   => 'The North Coast and Guanyin Mountain National Scenic Area Management Office',
            '東北角及宜蘭海岸國家風景區管理處' => 'The Northeast Coast and Yilan Coast National Scenic Area Management Office',
            '澎湖國家風景區管理處'            => 'The Penghu National Scenic Area Management Office',
            '參山國家風景區管理處'            => 'The Shei-Pa National Scenic Area Management Office',
            '國家公園署雪霸國家公園管理處'     => 'The Snow Mountain National Park Management Office',
            '日月潭國家風景區管理處'          => 'The Sun Moon Lake National Scenic Area Management Office',
            '國家公園署台江國家公園管理處'     => 'The Taijiang National Park Management Office',
            '國家公園署太魯閣國家公園管理處'   => 'The Taroko National Park Management Office',
            '國家公園署陽明山國家公園管理處'   => 'The Yangming Mountain National Park Management Office',
            '雲嘉南濱海國家風景區管理處'      => 'The Yunlin-Chiayi Coastal National Scenic Area Management Office',
            '西拉雅國家風景區管理處'          => 'The Xilaiya National Scenic Area Management Office',

            '國立臺灣交響樂團' => 'The National Taiwan Symphony Orchestra',
        ];
        

        //Loops through languages
        foreach (array(''=>'zh'/*, 'ENG/'=>'en'*/) as $locale => $lang) {
            //Gets the limit
            $html_dom = file_get_html('https://law.moj.gov.tw/'.$locale.'News/NewsList.aspx?psize='.$step);
            $limit = $limit ?? explode('&', explode('page=', $html_dom->find('#hlPage')[3]->href)[1])[0];

            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Processes the data
                $html_dom = file_get_html('https://law.moj.gov.tw/'.$locale.'News/NewsList.aspx?page='.$page.'&psize='.$step);
                $laws = $html_dom->find('table.table.table-hover.tab-list.tab-news')[0]->find('tbody')[0]->find('tr');
                foreach ($laws as $law) {
                    //Gets the type
                    $type = $types[trim($law->find('td')[2]->plaintext)];
                    if ($type !== 'Draft' && $type !== 'Local Regulation') {
                        //Gets the rest of the values
                        $enactDate = caltrans($law->find('td')[1]->plaintext, $lang); $enforceDate = $enactDate; $lastactDate = $enactDate;
                        $ID = $country.'-'.explode('&', (explode('id=', strtolower($law->find('td')[3]->find('a')[0]->href))[1] ?? explode('/Law/LawSearch/LawInformation/', $law->find('td')[3]->find('a')[0]->href)[1] ?? explode('/Law/LawSearch/LawInformation?sysNumber=', $law->find('td')[3]->find('a')[0]->href)[1] ?? explode('https://www.stat.gov.tw/News.aspx?n', $law->find('td')[3]->find('a')[0]->href)[1]))[0];
                        $regime = '{"zh":"中華民國", "en":"The Republic of China"}';
                        $name = trim($law->find('td')[3]->find('a')[0]->plaintext);
                            if (str_contains($name, '預告終止日')) {$lastactDate = caltrans(trim(explode('預告終止日', $name)[1], ' )'), $lang);}
                        //Gets the origin and type
                        if (str_contains($name, '：')) {
                            $originSubstr = strtr(explode('：', $name)[0], $sanitizeOrigins);
                            $origin = array();
                            $keyNum = 0;
                            foreach ($origins as $key => $value) {
                                if (str_contains($originSubstr, $key)) {
                                    $origin[$keyNum]['zh'] = $key; $origin[$keyNum]['en'] = $value;
                                    $originSubstr = str_replace($key, '', $originSubstr);
                                    $keyNum++;
                                }
                            }
                            $origin = isset($origin[0]) ? "'".json_encode($origin, JSON_UNESCAPED_UNICODE)."'":'NULL';
                            $type = $types[$originSubstr] ?? $type;
                        } else {$origin = 'NULL';}
                        //Gets the rest of the values
                        if (str_contains($name, '修正都') || str_contains($name, '修正條文')) {$isAmmend = 1;} else {$isAmmend = 0;}
                        $source = $law->find('td')[3]->find('a')[0]->href;
                            if (!str_contains($source, 'https://')) {$source = 'https://law.moj.gov.tw/'.$source;}
                        //$PDF = <!--come back to this-->

                        //Makes sure there are no appostophes in the title
                        if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                        //Creates SQL
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
                            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `origin`, `type`, `isAmmend`, `status`, `source`)
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', ".$origin.", '".$type."', ".$isAmmend.", '"."Valid"."', '".$source."')";
                        }

                        //Executes the SQL
                        echo $page.'. '.$SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
                }
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