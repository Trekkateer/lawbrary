<html><body>
    <?php //To many different types and origins
        //Settings
        $test = true; $country = 'UA';
        $start = 25;//Which law to start from
        $step = 100;//How many laws there are on the page
        $limit = null;//Total number of laws desired.

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

        $types = array('_-'=>'International Agreement',
                        '_a'=>'International Agreement',
                        '_f'=>'Ruling of an International Court',
                        '_g'=>'Ruling of an International Court',
                        '_h'=>'Ruling of an International Court',
                        '_i'=>'Ruling of an International Court',
                        '_j'=>'Ruling of an International Court',
                        '--р'=>'Decree',
                        '/-РГ'=>'Decree of the Chairman of the Verkhovna Rada',
                        'к/-РГ'=>'Decree of the Chairman of the Verkhovna Rada',
                        'k/-РГ'=>'Decree of the Chairman of the Verkhovna Rada',
                        'vp-'=>'Decision of the Constitutional Court',
                        'vr-'=>'Decision of the National Bank',
                        'vn-'=>'Order of the National Bank',
                        'n-'=>'Notice',
                        'z-'=>'Order',
                        'za-'=>'Order of the Foreign Intelligence Service',
                        'zа-'=>'Order of the Foreign Intelligence Service',
                        'zb-'=>'Ministerial Order',
                        'v_-'=>'Order of the Office of the Prosecutor General',
                        'va_-'=>'Ruling of the Supreme Court',
                        'vb_-'=>'Ruling of the Supreme Court',
                        'vc_-'=>'Ruling of the Supreme Court',
                        '/'=>'Presidential Decree',
                        '/-рп'=>'Presidential Decree',
                        'nd-'=>'Dissenting Opinion',
                        'nad-'=>'Seperate Opinion',
                        'nbd-'=>'Dissenting/Divergent Opinion',
                        'ncd-'=>'Dissenting Opinion',
                        'ndd-'=>'Dissenting Opinion',
                        'ned-'=>'Dissenting Opinion',
                        'nfd-'=>'Dissenting Opinion',
                        'ngd-'=>'Dissenting Opinion',
                        'nhd-'=>'Dissenting Opinion',
                        '--п'=>'Resolution of the Cabinent of Ministers',
                        '-IX'=>'Resolution of the Verkhovna Rada',
                        'а-IX'=>'Act of the Verkhovna Rada',
                        'б-IX'=>'Act of the Verkhovna Rada',
                        'v-'=>'Resolution',
                        'va-'=>'Ruling of the Supreme Court',
                        'vu-'=>'Ruling of the Supreme Court',
                        'vau-'=>'Ruling of the Constitutional Court',
                        'vbu-'=>'Ruling of the Constitutional Court',
                        'vcu-'=>'Ruling of the Constitutional Court',
                        'vdu-'=>'Ruling of the Constitutional Court',
                        'vap-'=>'Ruling of the Constitutional Court',
                        'v_u-'=>'Ruling of the Constitutional Court',
                        ''=>'',
                        ''=>'',
                        ''=>'',
                        ''=>'');

        //Sets the origin translations
        $origins = array('Адміністрація судноплавства'=>'{"en":"The Shipping Administration", "uk":"Адміністрація судноплавства"}',

                        'Постанова Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Розпорядження Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Угода Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Кабінет Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Лист Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Договір Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Рішення Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        'Указ Кабінету Міністрів України'=>'{"en":"The Cabinent of Ministers", "uk":"Кабінет Міністрів"}',
                        
                        'Центрвиборчком'=>'{"en":"The Central Elections Commission", "uk":"Центрвиборчком"}',
                        
                        'Розпорядження Голови Верховної Ради України'=>'{"en":"The Chairman of the Verkhovna Rada", "uk":"Голова Верховної Ради"}',

                        'Ухвала Конституційного суду України'=>'{"en":"The Constitutional Court", "uk":"Конституційний Суд"}',
                        'Рішення Конституційного суду України'=>'{"en":"The Constitutional Court", "uk":"Конституційний Суд"}',
                        'Конституційний Суд України'=>'{"en":"The Constitutional Court", "uk":"Конституційний Суд"}',
                        'Постанова Конституційного суду України'=>'{"en":"The Constitutional Court", "uk":"Конституційний Суд"}',

                        'Рада асоціації України та ЄС'=>'{"en":"EU-Ukraine Association Council", "uk":"Рада асоціації України та ЄС"}',
                        
                        'Служба зовнішньої розвідки'=>'{"The Foreign Intelligence Service":"", "":"Служба зовнішньої розвідки"}',

                        'Мінагрополітики'=>'{"en":"The Ministry of Agrarian Policy and Food", "uk":"Міністерство аграрної політики та продовольства"}',
                        'Міноборони України'=>'{"en":"The Ministry of Defense", "uk":"Міністерство оборони"}',
                        'Мінцифри'=>'{"en":"The Ministry of Digital Transformation", "uk":"Міністерство цифрової трансформації"}',
                        'Міністерство економіки'=>'{"en":"The Ministry of Economics", "uk":"Міністерство економіки"}',
                        'МОН України'=>'{"en":"The Ministry of Education and Science", "uk":"Міністерство освіти і науки"}',
                        'Міненерго'=>'{"en":"The Ministry Energy", "uk":"Міністерство енергетики"}',
                        'Мінфін України'=>'{"en":"The Ministry of Finance", "uk":"Міністерство фінансів"}',
                        'МЗС України'=>'{"en":"The Ministry of Foreign Affairs", "uk":"Міністерство закордонних справ"}',
                        'МОЗ України'=>'{"en":"The Ministry of Health", "uk":"Міністерство охорони здоров’я"}',
                        'Мінінфраструктури'=>'{"en":"The Ministry of Infrastructure", "uk":"Міністерство інфраструктури"}',
                        'МВС України'=>'{"en":"The Ministry of Internal Affairs", "uk":"Міністерство внутрішніх справ"}',
                        "Мін'юст України"=>'{"en":"The Ministry of Justice", "uk":"Міністерство юстиції"}',
                        'Угода Міністерства юстиції України'=>'{"en":"The Ministry of Justice", "uk":"Міністерство юстиції"}',
                        'Наказ Міністерства юстиції України'=>'{"en":"The Ministry of Justice", "uk":"Міністерство юстиції"}',//v-
                        'Лист Міністерства юстиції України'=>'{"en":"The Ministry of Justice", "uk":"Міністерство юстиції"}',//v-
                        'Мінреінтеграції'=>'{"en":"The Ministry of Reintegration", "uk":"Міністерство реінтеграції"}',
                        'Мінсоцполітики України'=>'{"en":"The Ministry of Social Policy", "uk":"Міністерство соціальної політики"}',
                        'Мінмолодьспорт'=>'{"en":"The Ministry of Youth and Sports", "uk":"Міністерство молоді та спорту"}',

                        'Національний банк'=>'{"en":"The National Bank", "uk":"Національний банк"}',
                        'Постанова Національного банку України'=>'{"en":"The National Bank", "uk":"Національний банк"}',
                        'Рішення Національного банку України'=>'{"en":"The National Bank", "uk":"Національний банк"}',
                        'Наказ Національного банку України'=>'{"en":"The National Bank", "uk":"Національний банк"}',

                        'Національна поліція'=>'{"en":"The National Police", "uk":"Національна поліція"}',

                        'РНБО'=>'{"en":"The National Security and Defense Council", "uk":"РНБО"}',

                        'Нац. антикорупційне бюро'=>'{"en":"The National Anti-Corruption Bureau", "uk":"Нац. антикорупційне бюро"}',

                        'Нацком.енергетики'=>'{"en":"National Committee for Energy, Utilities and Services", "uk":"Національний комітет з питань енергетики, комунальних послуг та послуг"}',

                        'Офіс Генерального прокурора'=>'{"en":"The Office of the Prosecutor General", "uk":"Офіс Генерального прокурора"}',
                        
                        'Президент України'=>'{"en":"The President", "uk":"Президент"}',
                        'Указ Президента України'=>'{"en":"The President", "uk":"Президент"}',
                        'Розпорядження Президента України'=>'{"en":"The President", "uk":"Президент"}',
                        
                        'Державіаслужба України'=>'{"en":"The State Aviation Administration", "uk":"Державна авіаційна служба"}',
                        'Укрдержархів'=>'{"en":"The State Archival Service", "uk":"Державна архівна служба"}',
                        'Адміністрація Держкордонслужби'=>'{"en":"The State Border Guard Service", "uk":"Державна прикордонна служба"}',
                        'Держпродспоживслужба'=>'{"en":"The State Food Safety and Consumer Protection Service", "uk":"Державна служба з питань безпечності харчових продуктів та захисту споживачів"}',
                        'Держфінмоніторинг України'=>'{"en":"The State Financial Monitoring Service", "uk":"Державна служба фінансового моніторингу"}',
                        'Держгеокадастр'=>'{"en":"The State Geodesy, Cartography and Cadastre Service", "uk":"Державна служба геодезії, картографії та кадастру"}',
                        'Держатомрегулювання'=>'{"en":"The State Nuclear Regulatory Service", "uk":"Державна служба ядерного регулювання"}',
                        'Служба безпеки України'=>'{"en":"The Security Service", "uk":"Служба безпеки"}',
                        'ДКА України'=>'{"en":"The State Space Agency", "uk":"Державне космічне агентство"}',
                        'Адміністрація Держспецзв’язку'=>'{"en":"The State Special Communications Service", "uk":"Держспецзв’язку"}',
                        'Держстат України'=>'{"en":"The State Statistics Service", "uk":"Держстат"}',
                        
                        'Верховний Суд'=>'{"en":"The Supreme Court", "uk":"Верховний Суд"}',
                        
                        'Постанова Верховної Ради України'=>'{"en":"The Verkhovna Rada", "uk":"Верховна Рада"}',
                        'Закон України'=>'{"en":"The Verkhovna Rada", "uk":"Верховна Рада"}',
                        'Уповноважений ВР з прав людини'=>'{"en":"The Verkhovna Rada", "uk":"Верховна Рада"}',
                        
                        'Україна'=>'{"en":"Ukraine", "uk":"Україна"}',
                        
                        'Європейський суд з прав людини'=>'{"en":"The European Court of Human Rights", "un":"Європейський суд з прав людини"}');

        //Sets the status translations
        $statuses = array('Втратив чинність'=>'Out of Force',
                        'Не набрав чинності'=>'Not in Force',
                        'Набирає чинності'=>'Not Yet in Force',
                        'Чинний'=>'Valid',
                        'Дію зупинено'=>'Invalid',
                        'Не застосовується на території України'=>'Not Applicable on the Territory of Ukraine',
                        'Доступ до документу відсутній'=>'There is no access to this document');

        //Gets the limit
        $html_dom = file_get_html('https://data.rada.gov.ua/laws/main/a/page1/sp:max'.$step);
        $limit = $limit ?? explode('/', explode('/page', $html_dom->find('a.page-link[title="в кінець"]')[0]->href)[1])[0];
        //Gets the laws
        for ($page = $start; $page <= $limit; $page ++) {
            //Gets the data from congress.gov API
            $html_dom = file_get_html('https://data.rada.gov.ua/laws/main/a/page'.$page.'/sp:max'.$step);
            $laws = $html_dom->find('ol.doc-list')[0]->find('li');
            foreach ($laws as $law) {
                $number = end($law->find('sup'))->plaintext; echo '<br/>'.$number.'<br/>';

                //Interprets the data
                $enactDate = date('Y-m-d', strtotime($law->find('div.doc')[0]->find('div.doc-card')[0]->find('span')[0]->innertext)); $enforceDate = $enactDate;
                $ID = $country.'-'.str_replace('-', '', explode('<span>', $law->find('div.doc')[0]->find('small')[0]->innertext)[0]);
                $name = trim($law->find('div.doc')[0]->find('a')[0]->plaintext);
                $type = $types[trim(preg_replace('/[0-9]/', '', explode('<span>', $law->find('div.doc')[0]->find('small')[0]->plaintext)[0]))];
                    if ($law->find('div.doc')[0]->find('div.doc-card')[0]->find('div.reg')[0]->innertext) {$type = 'Ammendment to '.$type;}
                $status = $statuses[$law->find('div.doc')[0]->find('small')[0]->find('span')[0]->find('b')[0]->plaintext ?? 'Чинний'];
                $origin = $origins[trim(explode('Зареєстровано:', explode('від ', $law->find('div.doc')[0]->find('div.doc-card')[0]->find('div.reg')[0]->innertext)[0])[1] ?? explode(',', explode(';', $law->find('div.doc')[0]->find('div.doc-card')[0]->find('em')[0]->plaintext)[0])[0])];
                $source = 'https://data.rada.gov.ua'.$law->find('div.doc')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                //if (str_contains($name, '"')) {$name = str_replace('"', '\"', $name);}

                //JSONifies the title and source
                $name = '{"uk":"'.$name.'"}';
                $source = '{"uk":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `origin`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$origin."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
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