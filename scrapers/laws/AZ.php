<?php //Azerbaijan
    //Settings
    $test = false; $scraper = 'AZ';
    $start = 0;//What page to start from
    $step = 1000;//How many laws per page
    $limit = null;//Total number of pages desired

    //Include my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Creates function to capitalize with exceptions
    $exceptions = [
        'və', 'üzrə', 'yanında'
    ];
    function mb_ucwordsexcept($str, $delims=' ', $encoding='UTF-8') {
        global $exceptions;
        $out = array(trim($str));
        foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
            if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
            $out[$key+1] = '';
            foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                $out[$key+1] .= !in_array($word, $exceptions) ? mb_strtoupper(mb_substr($word, 0, 1, $encoding), $encoding).mb_substr($word, 1, strlen($word)-1, $encoding).$delim:$word.$delim;
            }
            $out[$key+1] = rtrim($out[$key+1], $delim);
        }
        return ucfirst(end($out));
    }
    
    //Translates the types
    $types = array(
        'Məcəllələr' => 'Code',

        'Konstitusiya' => 'Constitution',

        'Müəyyən Etdiyi Hallar' => 'Court Decision',

        'Sərəncamlari' => 'Decree',

        'Təsvirlər' => 'Descriptions',

        'Köməkçi Sənədlər' => 'Supporting Documents',

        'Nümunələr' => 'Examples',

        'Beynəlxalq Müqavilələri' => 'International Treaties',
        'Beynəlxalq Müqavilələr' => 'International Treaty',
        'Beynəlxalq Hüquqi Bəyannamələr' => 'International Legal Declarations',
        'Beynəlxalq Hüquqi Memorandumlar' => 'International Legal Memorandums',

        'Konsti̇tusi̇ya Qanunlari' => 'Constitutional Law',
        'Qanunlar' => 'Law',

        'Siyahılar' => 'List',

        'Fərmanlari' => 'Order',

        'Referendum Aktları' => 'Referendum',

        'Nizamnamələr' => 'Regulation',

        'Qərarlari, Təli̇matlari və İzahlari' => 'Resolution',
        'Qərarları' => 'Resolution',
        'Qərarlari' => 'Resolution',
        'Qərarı' => 'Decision',
        'Qərar' => 'Decision',

        'Qaydalar' => 'Rules',

        'Standartlar' => 'Standards',
    );
    //Translates the origins
    $origins = array(
        '' => NULL,

        'Dövlət Dəniz Administrasiyası' => 'The State Maritime Administration',

        'Qida Təhlükəsizliyi Agentliyi' => 'The Food Safety Agency',
        'Dövlət Turizm Agentliyi' => 'The State Tourism Agency',
        'Azərbaycan Respublikasının Prezidenti yanında Vətəndaşlara Xidmət və Sosial İnnovasiyalar üzrə Dövlət Agentliyi' => 'The State Agency for Public Service and Social Innovations under the President',
        'Alternativ və Bərpa Olunan Enerji Mənbələri üzrə Dövlət Agentliyi' => 'The State Agency for Alternative and Renewable Energy Sources',
        'Azərbaycan Respublikasının Satınalmalar üzrə Dövlət Agentliyi' => 'The State Procurement Agency',
        'Azərbaycan Respublikası Müəllif Hüquqları Agentliyi' => 'The Copyright Agency',

        'Azərbaycan Respublikasının' => 'The Republic of Azerbaijan',

        'Azərbaycan Respubli̇kasi Nazi̇rlər Kabi̇neti̇ni̇n' => 'The Cabinent of Ministers',

        'Mərkəzi Bankın' => 'The Central Bank',

        'Tələbə Qəbulu üzrə Dövlət Komissiyası' => 'The State Commission on Student Admission',
        'Azərbaycan Respubli̇kasi Mərkəzi̇ Seçki̇ Komi̇ssi̇yasinin' => 'The Central Elections Commission',
        'Azərbaycan Respublikası Prezidenti yanında Dövlət Qulluğu Məsələləri üzrə Komissiya' => 'The Presidential Commission on Civil Service Issues',

        'Maliyyə Bazarlarına Nəzarət Palatasının' => 'The Financial Markets Control Chamber',

        'Diasporla İş üzrə Dövlət Komitəsi' => 'The State Committee on Work with the Diaspora',
        'Dövlət Gömrük Komitəsi' => 'The State Customs Committee',
        'Ailə, Qadın və Uşaq Problemləri üzrə Dövlət Komitəsi' => 'The State Committee on Family, Women and Children’s Issues',
        'Dövlət Şəhərsalma və Arxitektura Komitəsi' => 'The State Committee for Urban Developement and Architecture',
        'Dövlət Torpaq və Xəritəçəkmə Komitəsi' => 'The State Land and Mapping Committee',
        'Əmlak Məsələləri Dövlət Komitəsi' => 'The State Committee on Property Affairs',
        'Qaçqınların və Məcburi Köçkünlərin İşləri üzrə Dövlət Komitəsi' => 'The State Committee on the Affairs of Refugees and IDPs',
        'Dini Qurumlarla İş üzrə Dövlət Komitəsi' => 'The State Committee on Work with Religious Institutions',
        'Qiymətli Kağızlar üzrə Dövlət Komitəsi' => 'The State Security Committee',
        'Standartlaşdırma, Metrologiya və Patent üzrə Dövlət Komitəsi' => 'The State Committee on Standardization, Metrology, and Patents',
        'Dövlət Statistika Komitəsi' => 'The State Statistics Committee',

        'Azərbaycan Respubli̇kasi Audi̇ovi̇zual Şurasinin' => 'The Audiovisual Council',
        'Azərbaycan Respublikasının Tarif (qiymət) Şurası' => 'The Tariff Council',

        'Konsti̇tusi̇ya Məhkəməsi̇ni̇n' => 'The Constitutional Court',
        'İnsan Hüquqları üzrə Avropa Məhkəməsi' => 'The European Court of Human Rights',
        'Azərbaycan Respublikası Ali Məhkəməsinin' => 'The Supreme Court',

        'Dövlət Sosial Müdafiə Fondu' => 'The State Social Security Fund',

        'Məhkəmə-hüquq Şurasinin' => 'The Judiciary',

        'Yerli̇ İcra Haki̇mi̇yyəti̇ Orqanlarinin' => 'The Local Executive Authorities',
        'Yerli̇ Özünüi̇darə Orqanlarinin' => 'Local Government Bodies',

        'Kənd Təsərrüfatı Nazirliyi' => 'The Ministry of Agriculture',
        'Mədəniyyət Nazirliyi' => 'The Ministry of Culture',
        'Mədəniyyət və Turizm Nazirliyi' => 'The Ministry of Culture and Tourism',
        'Müdafiə Sənayesi Nazirliyi' => 'The Ministry of Defence Industry',
        'Rəqəmsal İnkişaf və Nəqliyyat Nazirliyi' => 'The Ministry of Digital Development and Transport',
        'Ekologiya və Təbii Sərvətlər Nazirliyi' => 'The Ministry of Ecology and Natural Resources',
        'İqtisadiyyat Nazirliyi' => 'The Ministry of the Economy',
        'Fövqəladə Hallar Nazirliyi' => 'The Ministry of Emergency Affairs',
        'Energetika Nazirliyi' => 'The Ministry of Energy',
        'Maliyyə Nazirliyi' => 'The Ministry of Finance',
        'Xarici İşlər Nazirliyi' => 'The Ministry of Foreign Affairs',
        'Səhiyyə Nazirliyi' => 'The Ministry of Health',
        'Daxili İşlər Nazirliyi' => 'The Ministry of Internal Affairs',
        'Ədliyyə Nazirliyi' => 'The Ministry of Justice',
        'Əmək və Əhalinin Sosial Müdafiəsi Nazirliyi' => 'The Ministry of Labor and Social Protection of the Population',
        'Milli Təhlükəsizlik Nazirliyi' => 'The Ministry of National Security',
        'Elm və Təhsil Nazirliyi' => 'The Ministry of Science and Eduaction',
        'Vergilər Nazirliyi' => 'The Ministry of Taxes',
        'Nəqliyyat Nazirliyi' => 'The Ministry of Transport',
        'Gənclər və İdman Nazirliyi' => 'The Ministry of Youth and Health',

        'Azərbaycan Respubli̇kasi Mi̇lli̇ Məcli̇si̇ni̇n' => 'The National Assembly of Azerbaijan',

        'Milli Arxiv İdarəsi' => 'The National Archives Office',

        'Azərbaycan Respubli̇kasi Prezi̇denti̇ni̇n' => 'The President of Azerbaijan',

        'Dövlət Sərhəd Xidməti' => 'The State Border Service',
        'Səfərbərlik və Hərbi Xidmətə Çağırış üzrə Dövlət Xidməti' => 'The State Service for Mobilization and Conscription',
        'Maliyyə Monitorinqi Xidməti' => 'The Financial Monitering Service',
        'Dövlət Miqrasiya Xidməti' => 'The State Migration Service',
        'Dövlət Təhlükəsizliyi Xidməti' => 'The State Security Service',

        'BMT-nin Təhlükəsizlik Şurası' => 'The UN Security Council',

        'Azərbaycan Respubli̇kasinin' => 'The Republic of Azerbaijan',
    );
    //Fixes the statuses
    $statuses = array(
        'Qüvvədədir'=>'In Force'
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["AZ"]';
    $publisher = '{"az":"Azərbaycan Respublikasının Ədliyyə Nazirliyi Qanunvericilik baş idarəsi", "en":"The Legislative Directorate of the Ministry of Justice of the Republic of Azerbaijan"}';

    //Finds the limit
    $API_Call = function($start, $step) {return json_decode(file_get_contents('https://api.e-qanun.az/getDetailSearch?start='.$start.'&length='.$step.'&orderColumn=8&orderDirection=desc&title=true&codeType=1&dateType=1&statusId=1&secondType=2&specialDate=false&array='), true);};
    $limit = $limit ?? $API_Call($start, 1)['totalCount'];
    //Gets the laws
    for ($page = $start; $page <= $limit; $page += $step) {
        //Gets the data from congress.gov API
        $laws = $API_Call($page, $step)['data'];
        foreach ($laws as $law) { echo '<br/>';
            //Interprets the data
            $enactDate = $law['classCode']; $enforceDate = $law['effectDate'] ?? $enactDate; $lastactDate = $enactDate;
            $ID = $scraper.':'.$law['id'];
            if (strtotime($law['classCode']) <= 687740400) {$regime = '{"az":"Azərbaycan SSR", "en":"The Azerbaijani SSR", "ru":"Азербайджанская ССР"}';}
                else {$regime = '{"az":"Azərbaycan Respublikası", "en":"The Republic of Azerbaijan"}';}
            $name = fixQuotes(trim($law['title']), 'az');
            //Gets the type and origin
            $law['typeName'] = strtr(mb_ucwordsexcept(mb_strtolower($law['typeName'], 'UTF-8')), array('Bmt-nin'=>'BMT-nin', 'İ'=>'İ'));
            $type = NULL;
            foreach ($types as $typeAZ => $typeEN) {
                if (str_contains($law['typeName'], $typeAZ)) {
                    $type = $typeEN;
                    $law['typeName'] = trim(str_replace($typeAZ, '', $law['typeName']), ' ,');
                    break;
                }
            }
            if ($type == NULL && str_contains($law['typeName'], 'Nazirliyi')) {$type = 'Decision';}
            $origin = $origins[$law['typeName']] ? '\'{"az":"'.$law['typeName'].'", "en":"'.$origins[$law['typeName']].'"}\'':'NULL';
            //Gets the rest of the values
            $status = $statuses[$law['statusName']];
            $source = 'https://e-qanun.az/framework/'.$law['id'];
            $PDF = 'https://frameworks.e-qanun.az/'.substr($law['id'], 0, strlen($law['id'])-3).'/'.$law['id'].'.pdf';

            //JSONifies the title and source
            $name = '{"az":"'.$name.'"}';
            $source = '{"az":"'.$source.'"}';
            $PDF = '{"az":"'.$PDF.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', '".$status."', '".$source."', '".$PDF."')";
            echo $law['rowNum'].'. '.$SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
        }
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>