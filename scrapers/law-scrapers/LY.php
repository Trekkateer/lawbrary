<html><body>
    <?php
        //TODO: Figure out how to do ammendments

        //Settings
        $test = true; $country = 'LY';
        $start = array('ar'=>1, 'en'=>1);//Which page to start from
        $limit = array('ar'=>null, 'en'=>null);//How many pages there are

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Makes sure there are four digits in every outputed number
        $zero_buffer = function ($inputNum, $outputLen=4) {
            $outputNum = trim(''.$inputNum);
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //The typenames on the website
        $dataNames = array('Title:'=>'', 'العنوان:'=>'', 'Source:'=>'', 'المصدر:'=>'', 'Category:'=>'', 'التشريع:'=>'', 'No:'=>'', 'الرقم:'=>'', 'Status:'=>'', 'الحالة:'=>'', 'Date:'=>'', 'التاريخ:'=>'', '0020'=>'1920');

        //Translates Arabic
        //Months
        $months = array(
            'January'=>'-01-',
            'يناير'=>'-01-',
            'February'=>'-02-',
            'فبراير'=>'-02-',
            'March'=>'-03-',
            'مارس'=>'-03-',
            'April'=>'-04-',
            'أبريل'=>'-04-',
            'May'=>'-05-',
            'مايو'=>'-05-',
            'June'=>'-06-',
            'يونيو'=>'-06-',
            'July'=>'-07-',
            'يوليو'=>'-07-',
            'August'=>'-08-',
            'أغسطس'=>'-08-',
            'September'=>'-09-',
            'سبتمبر'=>'-09-',
            'October'=>'-10-',
            'أكتوبر'=>'-10-',
            'November'=>'-11-',
            'نوفمبر'=>'-11-',
            'December'=>'-12-',
            'ديسمبر'=>'-12-',
        );
        //Types
        $types = array(
            'Decision'=>'Decision',
            'القرارات'=>'Decision',
            'القرارات, المراسيم'=>'Decision',
            'القرارات, اللوائح'=>'Regulatory Decision',
            'اللوائح, القرارات'=>'Regulatory Decision',
            'القرارات, القوانين'=>'Legal Decision',
            'Decrees'=>'Decree',
            'المراسيم'=>'Decree',
            'المراسيم, القرارات'=>'Decree',
            'Decrees, Regulation'=>'Regulatory Decree',
            'المراسيم, اللوائح'=>'Regulatory Decree',
            'اللوائح, المراسيم'=>'Regulatory Decree',
            'القوانين, المراسيم'=>'Legal Decree',
            'المراسيم, القوانين'=>'Legal Decree',
            'المناشير'=>'Circular',
            'Constitutional'=>'Constitutional Law',
            'الدستور'=>'The Constitution',
            'المشاريع, الدستور'=>'Constitutional Project',
            ''=>'Law',
            'Laws'=>'Law',
            'القوانين'=>'Law',
            'القوانين, اللوائح'=>'Regulatory Law',
            'المشاريع'=>'Project',
            'Regulation'=>'Regulation',
            'اللوائح'=>'Regulation',
            'Resolution'=>'Resolution',
            'Laws, Resolution'=>'Legal Resolution',
        );
        //Statuses
        $statuses = array(
            ''=>'Valid',
            'Effective'=>'Valid',
            'ساري'=>'Valid',
            'الأصلي, ساري'=>'Valid',
            'الأصلي, معدل'=>'Valid',
            'ساري, معدل'=>'Valid',
            'معدل'=>'Valid',
            'الأصلي'=>'Valid',
            'مشروع'=>'Valid',
            'ملغي'=>'Canceled',
            'ساري, ملغي'=>'Canceled',
            'الأصلي, ملغي'=>'Canceled'
        );
        //Origins
        $origins = array(
            ''=>'NULL',

            'الهيئة العامة للإتصالات و المعلوماتية'=>'The General Authority for Communications and Information Technology',
            'الهيئة العامة للنقل البحري والموانئ'=>'The General Authority for Maritime Transport and Ports',
            'مصلحة التسجيل العقاري'=>'The Real Estate Registration Authority',
            'الهيئة العامة للبيئة'=>'The Environment Public Authority',
            'النائب العام'=>'The Attorny General',
            'الجمعية الوطنية الليبية'=>'The Libyan National Assembly',

            'ديوان المحاسبة'=>'The Audit Bureau',
            'High National Elections Commission'=>'The High National Elections Commission',
            'مجلس المفوضية الوطنية العليا للانتخابات'=>'The Board of the High National Elections Commission',
            'الهيئة التأسيسية لصياغة مشروع الدستور'=>'The Founding Body for Drafting the Constitution',

            'مصرف ليبيا المركزي'=>'The Central Bank',
            'البنك الوطني الليبي'=>'The National Bank of Libya',
            'مجلس الوزراء'=>'The Cabinent',
            'القائد الأعلى للجيش الليبي'=>'The Supreme Commander of the Libyan Army',
            'المؤتمر الوطني العام'=>'The General National Congress',
            "General Peoples' Congress"=>'The General People\\\'s Congress',
            'مؤتمر الشعب العام'=>'The General People\\\'s Congress',
            'مؤتمر الشعب العام, وزارة المالية'=>'The General People\\\'s Congress and the Ministry of Finance',
            'الأمانة العامة لمؤتمر الشعب العام'=>'The General Secretariat of the General People\\\'s Congress',
            'الأمانة العامة للمؤتمرات الشعبية الأساسية'=>'The General Secretariat of the Basic People\\\'s Congresses',

            'لجنة قيد محرري العقود'=>'The Contract Editors Registration Committee',
            'لجنة قيد محرري العقود, وزارة العدل'=>'The Contract Editors Registration Commitee and the Ministry of Justice',
            'اللجنة الشعبية العامة'=>'The General People\\\'s Commitee',
            'اللجنة الشعبية العامة, وزارة الإسكان والتعمير'=>'The General People\\\'s Commitee and the Ministry of Housing and Development',
            'اللجنة الشعبية العامة, وزارة الثروة البحرية'=>'The General People\\\'s Committee, the Ministry of Marine Resources',
            'اللجنة الشعبية العامة, وزارة الزراعة والثروة الحيوانية'=>'General People\\\'s Committee, Ministry of Agriculture and Livestock',
            'هيئة الرقابة الإدارية'=>'The Administrative Control Commitee',
            'اللجنة الإدارية للإعلام الثوري'=>'The Administrative Committee for Revolutionary Media',
            'لجنة شؤون الأحزاب'=>'The Party Affairs Commitee',

            'مفوضية المجتمع المدني'=>'The Civil Society Commission',
            
            'المؤسسة الوطنية للنفط'=>'The National Oil Corporation',

            'إدارة التفتيش على الهيئات القضائية'=>'The Department of Inspection of Judicial Bodies',
            'رئيس مصلحة الجمارك'=>'The Head of the Customs Department',

            'مجلس المنافسة'=>'The Competition Council',
            'المجلس الأعلى للقضاء'=>'The Supreme Judicial Council',
            'المجلس الأعلى للدولة'=>'The Supreme Council of the State',
            'المجلس الرئاسي'=>'The Presidential Council',
            'The Libyan Presidential Council'=>'The Libyan Presidential Council',
            'المجلس الرئاسي الليبي'=>'The Libyan Presidential Council',
            'Council of Ministers'=>'The Council of Ministers',
            'National Transitional Council'=>'The National Transitional Council',
            'المجلس الوطني الانتقالي'=>'The National Transitional Council',
            'The Revolutionary Command Council'=>'The Revolutionary Command Council',
            'مجلس قيادة الثورة'=>'The Revolutionary Command Council',
            'مجلس الوزراء, وزارة الاقتصاد'=>'The Council of Ministers and the Ministry of the Economy',

            'رئيس الدولة'=>'The Head of State',
            'دار الإفتاء'=>'The House of Fatwa',
            'House of Representatives'=>'The House of Representatives',
            'مجلس النواب'=>'The House of Representatives',

            'إدريس الأول'=>'King Idris I',
            'إدريس الأول, مجلس الوزراء'=>'King Idris I and the Council of Ministers',
            'إدريس الأول, مجلس قيادة الثورة'=>'King Idris I and the Revolutionary Command Council',
            'إدريس الأول, وزارة العدل'=>'King Idris I and the Ministry of Justice',
            'إدريس الأول, رئيس مجلس الوزراء'=>'King Idris I and the Prime Minister',
            'إدريس الأول, رئيس مجلس الوزراء, وزارة الاقتصاد'=>'King Idris I, the Prime Minister, and the Ministry of the Economy',
            'إدريس الأول, رئيس مجلس الوزراء, وزارة العدل'=>'King Idris I, the Prime Minister, and the Ministry of Justice',

            'الجامعة العربية'=>'The Arab League',

            'Prime Minister'=>'The Prime Minister',
            'رئيس مجلس الوزراء'=>'The Prime Minister',
            'رئيس مجلس الوزراء, وزارة الخارجية'=>'The Prime Minister and The Ministry of Foreign Affairs',
            'Minister of Economy and Trade'=>'The Minister of Economy and Trade',
            'Minister of Transportation'=>'The Minister of Transportation',

            'Ministry of Agriculture, Livestock and Marine'=>'The Ministry of Agriculture, Livestock and Marine',
            'وزارة الزراعة والثروة الحيوانية'=>'The Ministry of Agriculture and Livestock',
            'وزارة الثقافة والتنمية المعرفية'=>'The Ministry of Culture and Knowledge Development',
            'وزارة الدفاع'=>'The Ministry of Defense',
            'وزارة الاقتصاد'=>'The Ministry of the Economy',
            'وزارة الاقتصاد, وزارة العدل'=>'The Ministry of the Economy and the Ministry of Justice',
            'وزارة التعليم'=>'The Ministry of Education',
            'وزارة التعليم العالي والبحث العلمي'=>'The Ministry of Higher Education and Scientific Research',
            'وزارة التعليم التقني والفني'=>'The Ministry of Technical and Vocational Education',
            'وزارة البيئة'=>'The Ministry of the Environment',
            'Ministry of Finance'=>'The Ministry of Finance',
            'وزارة المالية'=>'The Ministry of Finance',
            'وزارة الخارجية'=>'The Ministry of Foreign Affairs',
            'وزارة الصحة'=>'The Ministry of Health',
            'وزارة الإسكان والتعمير'=>'The Ministry of Housing and Development',
            'وزارة الصناعة والمعادن'=>'The Ministry of Industry and Minerals',
            'Ministry of Interior'=>'The Ministry of the Interior',
            'وزارة الداخلية'=>'The Ministry of the Interior',
            'وزارة العدل'=>'The Ministry of Justice',
            'وزارة العمل والتأهيل'=>'The Ministry of Labor and Rehabilitation',
            'وزارة الحكم المحلي'=>'The Ministry of Local Government',
            'وزارة الحكم المحلي, وزارة الداخلية'=>'The Ministry of Local Government and the Ministry of the Interior',
            'وزارة الثروة البحرية'=>'The Ministry of Marine Resources',
            'وزارة التخطيط'=>'The Ministry of Planning',
            'وزارة الشؤون الاجتماعية'=>'The Ministry of Social Affairs',
            'وزارة الرياضة'=>'The Ministry of Sports',
            'وزارة السياحة والصناعات التقليدية'=>'The Ministry of Tourism and Handicrafts',
            'وزارة المواصلات'=>'The Ministry of Transportation',
            'وزارة الشباب'=>'The Ministry of Youth',

            'بلدية سرت'=>'The Municipality of Sirte',

            'المنظمة الليبية للإعلام المستقل'=>'The Libyan Organization for Independant Media',

            'Parliament'=>'Parliament',

            'صندوق الضمان الاجتماعي'=>'The Social Security Fund'
        );

        //Loops through the languages
        foreach (array('ar'=>'', 'en'=>'en/') as $lang => $directory) {
            //Gets the limit
            $html_dom = file_get_html('https://www.lawsociety.ly/'.$directory);
            //TODO: Find a way to get these automatically
            switch ($lang) {
                case 'ar':
                    $limit[$lang] = $limit[$lang] ?? $html_dom->find('#search-filter-results-52')[0]->find('ul.uk-pagination.uk-flex-center.uk-margin-medium-top')[0]->find('li')[9]->plaintext;
                    break;
                case 'en':
                    $limit[$lang] = $limit[$lang] ?? 5;
                    break;
            }
            //Loops through the pages
            for ($page = $start[$lang]; $page <= $limit[$lang]; $page ++) {echo 'Page: '.$page.'<br/>';
                //Gets the HTML
                $html_dom = file_get_html('https://www.lawsociety.ly/'.$directory.'page/'.$page.'/?sf_paged='.$page);

                //Processes the data in the table
                $laws = $html_dom->find('table#table')[0]->find('tbody')[0]->find('tr.el-item');
                foreach ($laws as $law) {
                    //Gets values
                    $enactDate = explode(' ', trim(strtr($law->find('td')[5]->plaintext, $dataNames)))[2].$months[explode(' ', trim(strtr($law->find('td')[5]->plaintext, $dataNames)))[1]].$zero_buffer(explode(' ', trim(strtr($law->find('td')[5]->plaintext, $dataNames)))[0], 2);
                        $enforceDate = $enactDate; $lastactDate = $enforceDate;
                    $ID = $country.'-'.explode(' ', trim(strtr($law->find('td')[5]->plaintext, $dataNames)))[2].$zero_buffer(strtr($law->find('td')[3]->plaintext, $dataNames));
                    //Gets regime
                    switch(true) {
                        case strtotime($enactDate) < strtotime('5 November 1911'):
                            $regime = 'Ottoman Tripolitania';
                            break;
                        case strtotime('5 November 1911') < strtotime($enactDate) && strtotime($enactDate) < strtotime('10 February 1947'):
                            $regime = 'Italian Libya';
                            break;
                        case strtotime('10 February 1947') < strtotime($enactDate) && strtotime($enactDate) < strtotime('24 December 1951'):
                            $regime = 'Allied Occupation of Libya';
                            break;
                        case strtotime('24 December 1951') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 September 1969'):
                            $regime = 'The Kingdom of Libya';
                            break;
                        case strtotime('1 September 1969') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('2 March 1977'):
                            $regime = 'The Libyan Arab Republic';
                            break;
                        case strtotime('2 March 1977') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('23 October 2011'):
                            $regime = 'The Socialist People\\\'s Libyan Arab Jamahirya';
                            break;
                        case strtotime('23 October 2011') < strtotime($enactDate) && strtotime($enactDate) <= strtotime(date('d M Y')):
                            $regime = 'The State of Libya';
                            break;
                    }
                    //Gets the rest of the values
                    $name = trim(strtr($law->find('td')[0]->plaintext, array('Title:'=>'', 'العنوان: '=>'')));
                    $type = $types[trim(strtr($law->find('td')[2]->plaintext, $dataNames))];
                        if (str_contains($law->find('td')[4]->plaintext, 'معدل')) {$type = 'Amendment to '.$type;}
                    $status = $statuses[trim(strtr($law->find('td')[4]->plaintext, $dataNames))];
                    $origin = $origins[trim(strtr($law->find('td')[1]->plaintext, $dataNames))];
                    $source = $law->find('td')[0]->find('a')[0]->href;

                    //Makes sure there are no quotes in the title or href
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                    if (str_contains($source, "'")) {$source = str_replace("'", "%27", $name);}

                    //Allows for NULL values
                    if ($type !== 'NULL') {$type = "'".$type."'";}
                    if ($status !== 'NULL') {$status = "'".$status."'";}
                    if ($origin !== 'NULL') {$origin = "'".$origin."'";}

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
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `status`, `origin`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', ".$type.", ".$status.", ".$origin.", '".$source."')";
                    }
                    
                    //Inserts the new laws
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }

                echo '<br/>';
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