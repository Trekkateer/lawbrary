<html><body>
    <?php
        //Settings
        $test = true; $country = 'AZ';
        $start = 0;//What page to start from
        $step = 1000;//How many laws per page
        $limit = null;//Total number of pages desired

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Fixes the types and origins
        $types = array(
            'Dövlət Dəniz Administrasiyası'=>array('The State Maritime Administration', 'Decision'),
            'Milli Arxiv İdarəsi'=>array('The National Archives Administration', 'Decision'),

            'Qida Təhlükəsizliyi Agentliyi'=>array('The Food Safety Agency', 'Decision'),
            'Dövlət Turizm Agentliyi'=>array('The State Tourism Agency', 'Decision'),
            'Azərbaycan Respublikasının Prezidenti yanında Vətəndaşlara Xidmət və Sosial İnnovasiyalar üzrə Dövlət Agentliyi'=>array('The State Agency for Service to Citizens and Social Innovations', 'Decision'),
            'Alternativ və Bərpa Olunan Enerji Mənbələri üzrə Dövlət Agentliyi'=>array('The State Agency for Alternative and Renewable Energy Sources', 'Decision'),
            'Azərbaycan Respublikasının Satınalmalar üzrə Dövlət Agentliyi'=>array('The State Procurement Agency', 'Decision'),
            'Azərbaycan Respublikası Müəllif Hüquqları Agentliyi'=>array('The Copyright Agency', 'Decision'),

            'Azərbaycan Respublikasının beynəlxalq müqavilələri'=>array('The Republic of Azerbaijan', 'Treaty'),
            'Beynəlxalq hüquqi bəyannamələr'=>array('The Republic of Azerbaijan', 'International Law'),
            'Beynəlxalq hüquqi memorandumlar'=>array('The Republic of Azerbaijan', 'International Law'),
            'Dövlət (milli) standartları və beynəlxalq standartlar'=>array('The Republic of Azerbaijan', 'Standards'),
            'Konstitusiya'=>array('The Republic of Azerbaijan', 'The Constitution'),

            'AZƏRBAYCAN RESPUBLİKASI NAZİRLƏR KABİNETİNİN QƏRARLARI'=>array('The Cabinent of Ministers', 'Decision'),
            'AZƏRBAYCAN RESPUBLİKASI NAZİRLƏR KABİNETİNİN SƏRƏNCAMLARI'=>array('The Cabinent of Ministers', 'Decree'),
            'Nazirlər Kabinetinin müəyyən etdiyi hallar'=>array('The Cabinent of Ministers', 'Court Decision'),
            'Nazirlər Kabinetinin təsdiq etdiyi nizamnamələr'=>array('The Cabinent of Ministers', 'Regulation'),
            'Nazirlər Kabinetinin təsdiq etdiyi nümunələr'=>array('The Cabinent of Ministers', 'Information'),
            'Nazirlər Kabinetinin təsdiq etdiyi qaydalar'=>array('The Cabinent of Ministers', 'Rules'),
            'Nazirlər Kabinetinin təsdiq etdiyi siyahılar'=>array('The Cabinent of Ministers', 'List'),
            'Nazirlər Kabinetinin təsdiq etdiyi təsvirlər'=>array('The Cabinent of Ministers', 'Descriptions'),
            'Nazirlər Kabinetinin qərarları'=>array('The Cabinent of Ministers', 'Decision'),

            'Mərkəzi bankın qərarı'=>array('The Central Bank', 'Decision'),

            'AZƏRBAYCAN RESPUBLİKASI MƏRKƏZİ SEÇKİ KOMİSSİYASININ QƏRARLARI, TƏLİMATLARI VƏ İZAHLARI'=>array('The Central Election Commission', 'Regulation'),
            'Tələbə Qəbulu üzrə Dövlət Komissiyası'=>array('The State Commission on Student Admission', 'Regulation'),
            'Azərbaycan Respublikası Prezidenti yanında Dövlət Qulluğu Məsələləri üzrə Komissiya'=>array('The Presidential Commission on Civil Service Issues', 'Regulation'),

            'Maliyyə Bazarlarına Nəzarət Palatasının qərarı'=>array('The Financial Markets Control Chamber', 'Decision'),

            'Diasporla İş üzrə Dövlət Komitəsi'=>array('The State Committee on Work with Diaspora', 'Decision'),
            'Dövlət Gömrük Komitəsi'=>array('The State Customs Committee', 'Decision'),
            'Ailə, Qadın və Uşaq Problemləri üzrə Dövlət Komitəsi'=>array('The State Committee on Family, Women and Children’s Issues', 'Decision'),
            'Dövlət Şəhərsalma və Arxitektura Komitəsi'=>array('The State Committee for Urban Developement and Architecture', 'Decision'),
            'Dövlət Torpaq və Xəritəçəkmə Komitəsi'=>array('The State Land and Mapping Committee', 'Decision'),
            'Əmlak Məsələləri Dövlət Komitəsi'=>array('The State Committee on Property Affairs', 'Decision'),
            'Qaçqınların və Məcburi Köçkünlərin İşləri üzrə Dövlət Komitəsi'=>array('The State Committee on the Affairs of Refugees and IDPs', 'Decision'),
            'Dini Qurumlarla İş üzrə Dövlət Komitəsi'=>array('The State Committee on Work with Religious Institutions', 'Decision'),
            'Qiymətli Kağızlar üzrə Dövlət Komitəsi'=>array('The State Security Committee', 'Decision'),
            'Standartlaşdırma, Metrologiya və Patent üzrə Dövlət Komitəsi'=>array('The State Committee on Standardization, Metrology, and Patents', 'Decision'),
            'Dövlət Statistika Komitəsi'=>array('The State Statistics Committee', 'Decision'),
            'Köməkçi Sənədlər'=>array('A State Committee', 'Supporting Documents'),

            'AZƏRBAYCAN RESPUBLİKASI AUDİOVİZUAL ŞURASININ QƏRARLARI'=>array('The Audiovisual Council', 'Resolution'),
            'Azərbaycan Respublikasının Tarif (qiymət) Şurası'=>array('The Tariff Council', 'Resolution'),

            'KONSTİTUSİYA MƏHKƏMƏSİNİN QƏRARLARI'=>array('The Constitutional Court', 'Court Decision'),
            'İnsan Hüquqları üzrə Avropa Məhkəməsi'=>array('The European Court of Human Rights', 'Court Decision'),
            'Azərbaycan Respublikası Ali Məhkəməsinin qərarları'=>array('The Supreme Court', 'Court Decision'),

            'MƏHKƏMƏ-HÜQUQ ŞURASININ QƏRARLARI'=>array('The Judiciary', 'Court Decision'),

            'YERLİ İCRA HAKİMİYYƏTİ ORQANLARININ QƏRARLARI'=>array('The Local Executive Authorities', 'Decision'),
            'YERLİ ÖZÜNÜİDARƏ ORQANLARININ QƏRARLARI'=>array('Local Government Bodies', 'Decision'),

            'Kənd Təsərrüfatı Nazirliyi'=>array('The Ministry of Agriculture', 'Decision'),
            'Mədəniyyət Nazirliyi'=>array('The Ministry of Culture', 'Decision'),
            'Mədəniyyət və Turizm Nazirliyi'=>array('The Ministry of Culture and Tourism', 'Decision'),
            'Müdafiə Sənayesi Nazirliyi'=>array('The Ministry of Defence Industry', 'Decision'),
            'Rəqəmsal İnkişaf və Nəqliyyat Nazirliyi'=>array('The Ministry of Digital Development and Transport', 'Decision'),
            'Ekologiya və Təbii Sərvətlər Nazirliyi'=>array('The Ministry of Ecology and Natural Resources', 'Decision'),
            'İqtisadiyyat Nazirliyi'=>array('The Ministry of Economics', 'Decision'),
            'Fövqəladə Hallar Nazirliyi'=>array('The Ministry of Emergency Affairs', 'Decision'),
            'Energetika Nazirliyi'=>array('The Ministry of Energy', 'Decision'),
            'Maliyyə Nazirliyi'=>array('The Ministry of Finance', 'Decision'),
            'Xarici İşlər Nazirliyi'=>array('The Ministry of Foreign Affairs', 'Decision'),
            'Səhiyyə Nazirliyi'=>array('The Ministry of Health', 'Decision'),
            'Daxili İşlər Nazirliyi'=>array('The Ministry of Internal Affairs', 'Decision'),
            'Ədliyyə Nazirliyi'=>array('The Ministry of Justice', 'Decision'),
            'Əmək və Əhalinin Sosial Müdafiəsi Nazirliyi'=>array('The Ministry of Labor and Social Protection of the Population', 'Decision'),
            'Milli Təhlükəsizlik Nazirliyi'=>array('The Ministry of National Security', 'Decision'),
            'Elm və Təhsil Nazirliyi'=>array('The Ministry of Science and Eduaction', 'Decision'),
            'Vergilər Nazirliyi'=>array('The Ministry of Taxes', 'Decision'),
            'Nəqliyyat Nazirliyi'=>array('The Ministry of Transport', 'Decision'),
            'Gənclər və İdman Nazirliyi'=>array('The Ministry of Youth and Health', 'Decision'),

            'AZƏRBAYCAN RESPUBLİKASI MİLLİ MƏCLİSİNİN QƏRARLARI'=>array('The National Assembly of Azerbaijan', 'Decision'),
            'Qanunlar'=>array('The National Assembly of Azerbaijan', 'Law'),
            'AZƏRBAYCAN RESPUBLİKASININ KONSTİTUSİYA QANUNLARI'=>array('The National Assembly of Azerbaijan', 'Constitutional Law'),
            'Məcəllələr'=>array('The National Assembly of Azerbaijan', 'Code'),
            'Referendum aktları'=>array('The National Assembly of Azerbaijan', 'Referendum'),

            'AZƏRBAYCAN RESPUBLİKASI PREZİDENTİNİN FƏRMANLARI'=>array('The President', 'Order'),
            'AZƏRBAYCAN RESPUBLİKASI PREZİDENTİNİN SƏRƏNCAMLARI'=>array('The President', 'Decree'),

            'Dövlət Sərhəd Xidməti'=>array('The State Border Service', 'Decision'),
            'Səfərbərlik və Hərbi Xidmətə Çağırış üzrə Dövlət Xidməti'=>array('The State Service for Mobilization and Conscription', 'Decision'),
            'Maliyyə Monitorinqi Xidməti'=>array('The Financial Monitering Service', 'Decision'),
            'Dövlət Miqrasiya Xidməti'=>array('The State Migration Service', 'Decision'),
            'Dövlət Təhlükəsizliyi Xidməti'=>array('The State Security Service', 'Decision'),

            'Dövlət Sosial Müdafiə Fondu'=>array('The State Social Security Fund', 'Resolution'),

            'BMT-nin Təhlükəsizlik Şurası'=>array('The UN Security Council', 'Resolution')
        );
        //Fixes the statuses
        $statuses = array(
            'Qüvvədədir'=>'In Force'
        );
        
        //Finds the limit
        $API_Call = function($start, $step) {return json_decode(file_get_contents('https://api.e-qanun.az/getDetailSearch?start='.$start.'&length='.$step.'&orderColumn=8&orderDirection=desc&title=true&codeType=1&dateType=1&statusId=1&secondType=2&specialDate=false&array='), true);};
        $limit = $limit ?? $API_Call($start, 1)['totalCount'];
        //Gets the laws
        for ($page = $start; $page <= $limit; $page += $step) {
            //Gets the data from congress.gov API
            $laws = $API_Call($page, $step)['data'];
            foreach ($laws as $law) {
                //Interprets the data
                $enactDate = $law['classCode']; $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $country.'-'.$law['id'];
                if (strtotime($law['classCode']) <= 687740400) {$regime = 'The Azerbaijan S.S.R.';}
                    else {$regime = 'The Republic of Azerbaijan';}
                $name = trim($law['title']);
                $type = $types[$law['typeName']][1];
                    if (str_contains($name, 'dəyişiklik')) {$type = 'Ammendment to '.$type;}
                $status = $statuses[$law['statusName']];
                $origin = $types[$law['typeName']][0];
                $source = 'https://e-qanun.az/framework/'.$law['id'];

                //Makes sure there are no quotes in the title or summary
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //JSONifies the title and source
                $name = '{"az":"'.$name.'"}';
                $source = '{"az":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `origin`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$origin."', '".$source."')"; echo $law['rowNum'].'. '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
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