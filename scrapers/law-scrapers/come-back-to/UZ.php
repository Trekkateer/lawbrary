<html><body>
    <?php //!!The only way to get the correct page is to have the right _VIEWSTATE, which I don't know how to get!!
        //Settings
        $test = true; $LBpage = 'UZ';
        $start = 0;//Which law to start from
        $step = 20;//How many laws there are on each page
        $limit = null;//Total number of pages desired.

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php'; // '../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $HTTP_Call = function ($href, $page) {
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
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    '__EVENTTARGET' => 'ucFoundActsControl$rptPaging$ctl'.$page.'$lbPaging',
                    '__VIEWSTATE' => 'MPzFLgtzydJHxFE1c4Pa/1VBZlIVNIkV0FnIcThz3o8rXc4/7CBkeyzCuNo6S8xTSktS1iRUxAQEE67NBxvXS5AKn7jX0cmBSLu05qPPKRHADCtaExwDeE3Gb6ewMMmaQ9rW/5UTZFnqiJJxUttEQYCuT8TivEjL02U7QdcZ8V0gsVVK2HXCkWrnhoI/8Paf/aXqEefxgEbaMfF+BCvRrFUWMb47cIZGCy7uAU4O9J+H0gnen8xiMeyoP+eJ3uHWqf4x1govaZC5xZch+abAsEg9yU4q+l75LypnO96D873uDLY8wKGSak4wYoVWWbTT8Fc/R7k5CbqAoSDOQIZyc7/o1uOApNubMgWw2qjCzNoEf3fOkcTQ6L4GCuQ76T6k30X5EbXij72MaiHOEeY7ZlcSWi8Cx5u3MNSYD1m61iUA8cYwiGyIw4ZcOgvP9B+cRW6V7wj8dF5U6xbZ8fOkXZvnarf9VcgSNf2vvKVocCJUQAw0bM3kJVOV8W6HNqe7NgKJYRXOyzqt2LddVDWir712JPUYpKr24sZeSNCjRcQpK4msy/HpQJ6s6b18myHCl+Yk0HR8CkW7Rzy5Wys4xRcSZpPnM9DA4YAoaT1EOu3loOZR49wfw2M8qnaMO+xM48r8AzqT98LNQyGkrZfFT+olYmg7IPuF4qfVnp16CYk=',
                    '__VIEWSTATEGENERATOR' => '4CEDEDF5'
                )
            ));
            $response = curl_exec($curl); curl_close($curl);
            return $response;
        };

        //Makes sure there are two digits in every number
        $zero_buffer = function ($inputNum, $outputLen=2) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Translates the types
        $typesAndOrigins = array(
            "O‘zbekiston Respublikasi Qonuni, " => ["Act", ["en"=>"The Parliament of Uzbekistan", "uz"=>"Oliy Majlis"]],
            "O‘zbekiston Respublikasining Qonuni, " => ["Act", ["en"=>"The Parliament of Uzbekistan", "uz"=>"Oliy Majlis"]],
        );
        $sanitizeTypeLine = array(
            "O‘zbekiston Respublikasi " => "",

            "Ekologiya, atrof-muhitni muhofaza qilish va iqlim o‘zgarishi vazirligi" => "Ekologiya atrof-muhitni muhofaza qilish va iqlim o‘zgarishi vazirligi",
            "Oliy ta’lim, fan va innovatsiyalar vazirligi" => "Oliy ta’lim fan va innovatsiyalar vazirligi",
        );
        $types = array(
            ' qarori, '   => 'Decision',
            ' Qarori, '   => 'Decision',
            ' Farmoni, '  => 'Decree',
            ' Qonuni, '   => 'Law',
            ' buyrug‘i, ' => 'Order',
            ' farmoyishi, ' => 'Order',
        );
        $sanitizeOrigin = array(
            "agentligining" => "agentligi",
            "boshqaruvining" => "boshqaruvi",
            "departamentining" => "departamenti",
            "direktorining" => "direktori",
            "komissiyasining" => "komissiyasi",
            "Mahkamasining" => "Mahkamasi",
            "Plenumining" => "Plenumi",
            "Prezidentining" => "Prezident",
            "prokuraturasining" => "prokuraturasi",
            "qo‘mitasining" => "qo‘mitasi",
            "qo‘mondonining" => "qo‘mondoni",
            "raisining" => "raisi",
            "vazirining" => "vaziri",
            "vazirligining" => "vazirligi",
        );
        //Translates the origins
        $origins = array(
            "Korrupsiyaga qarshi kurashish agentligi" => "The Anti-Corruption Agency",
            "Istiqbolli loyihalar milliy agentligi" => "The National Agency of Prospective Projects",

            "Markaziy banki boshqaruvi" => "The Board of the Central Bank",

            "Vazirlar Mahkamasi" => "The Cabinet of Ministers",
            "Vazirlar Mahkamasi huzuridagi O‘zbekiston texnik jihatdan tartibga solish agentligi" => "The Agency for Technological Regulation under the Cabinet of Ministers",
            "Vazirlar Mahkamasi huzuridagi O‘zbekiston Texnik jihatdan tartibga solish agentligi" => "The Agency for Technological Regulation under the Cabenet of Ministers",

            "Davlat xavfsizlik xizmati raisi" => "The Chairman of the State Security Service",

            "Raqobatni rivojlantirish va iste’molchilar huquqlarini himoya qilish qo‘mitasi" => "The Competition Development and Consumer Protection Committee",
            "Vazirlar Mahkamasi huzuridagi Soliq qo‘mitasi" => "The Tax Committee under the Cabinet of Ministers",
            "Iqtisodiyot va moliya vazirligi Soliq qo‘mitasi" => "The Tax Committee of the Ministry of Economy and Finance",
            "Din ishlari bo‘yicha qo‘mitasi" => "The Committee on Religious Affairs",

            "Milliy gvardiyasi qo‘mondoni" => "The Commander of the National Guard",
            "Milliy Gvardiyasi qo‘mondoni" => "The Commander of the National Guard",

            "Markaziy saylov komissiyasi" => "The Central Election Commission",
            "Markaziy Saylov komissiyasi" => "The Central Election Commission",

            "Bosh prokuraturasi huzuridagi Iqtisodiy jinoyatlarga qarshi kurashish departamenti" => "The Department for Combating Economic Crimes under the Prosecutor General's Office",

            "Korrupsiyaga qarshi kurashish agentligi direktori"                => "The Director of the Anti-Corruption Agency",
            "Prezidenti huzuridagi davlat xizmatini rivojlantirish agentligi direktori" => "The Director of the State Services Development Agency under the President",
            "Transport vazirligi huzuridagi fuqaro aviatsiyasi agentligi direktori" => "The Director of the Agency for Civil Aviation at the Ministry of Transport",
            "Prezidenti huzuridagi Ijtimoiy himoya milliy agentligi direktori" => "The Director of the National Agency for Social Protection under the President",
            "Istiqbolli loyihalar milliy agentligi direktori"                  => "The Director of the National Agency for Project Management",
            "Prezidenti huzuridagi Statistika agentligi direktori"             => "The Director of the State Statistics Agency under the President",
            "Vazirlar Mahkamasi huzuridagi O‘zbekiston texnik jihatdan tartibga solish agentligi direktori" => "The Director of the Uzbek Agency for Technological Regulation under the Cabinet of Ministers",

            "Milliy gvardiyasi" => "The National Guard",

            "Qishloq xo‘jaligi vaziri"                      => "The Minister of Agriculture",
            "Iqtisodiyot va moliya vaziri"                  => "The Minister of Economy and Finance",
            "Ekologiya va atrof-muhitni muhofaza qilish va iqlim o‘zgarishi vaziri" => "The Minister of Ecology, Environmental Protection and Climate Change",
            "Favqulodda vaziyatlar vaziri"                  => "The Minister of Emergency Situations",
            "Mudofaa vaziri"                                => "The Minister of Defense",
            "Raqamli texnologiyalar vaziri"                 => "The Minister of Digital Development",
            "Sog‘liqni saqlash vaziri"                      => "The Minister of Health",
            "Tashqi ishlar vaziri"                          => "The Minister of Foreign Affairs",
            "Ichki ishlar vaziri"                           => "The Minister of Internal Affairs",
            "Adliya vaziri"                                 => "The Minister of Justice",
            "Kambag‘allikni qisqartirish va bandlik vaziri" => "The Minister of Poverty Reduction and Employment",
            "Fan va innovatsiyalar vaziri"                  => "The Ministry of Science and Innovation",
            "Sport vaziri"                                  => "The Minister of Sports",

            "Qishloq xo‘jaligi vazirligi"                      => "The Ministry of Agriculture",
            "Mudofaa vazirligi"                                => "The Ministry of Defence",
            "Ekologiya atrof-muhitni muhofaza qilish va iqlim o‘zgarishi vazirligi" => "The Ministry of Ecology, Environment and Climate Change",
            "Iqtisodiyot va moliya vazirligi"                  => "The Ministry of Economy and Finance",
            "Iqtisodiyot va Moliya vazirligi"                  => "The Ministry of Economy and Finance",
            "Favqulodda vaziyatlar vazirligi"                  => "The Ministry of Emergency Situations",
            "Energetika vazirligi"                             => "The Ministry of Energy",
            "Sog‘liqni saqlash vazirligi"                      => "The Ministry of Health",
            "Oliy ta’lim fan va innovatsiyalar vazirligi"      => "The Ministry of Higher Education, Science and Innovation",
            "Ichki ishlar vazirligi"                           => "The Ministry of Internal Affairs",
            "Adliya vazirligi"                                 => "The Ministry of Justice",
            "Kambag‘allikni qisqartirish va bandlik vazirligi" => "The Ministry of Poverty Reduction and Employment",

            "Oliy sudi Plenumi" => "The Plenum of the Supreme Court",
            "Oliy Sudi Plenumi" => "The Plenum of the Supreme Court",

            "Bosh prokuraturasi" => "The Office of the Prosecutor General",

            "Prezident" => "The President",

            "Davlat xavfsizlik xizmati" => "The State Security Service"
        );

        //Gets the years
        $yearWords = array(
            'uz'=>' yilda',
            'ru'=>' г.',
            'en'=>' г.'
        );

        //Loops through the languages
        foreach (array('uz'=>4/*, 'ru'=>1, 'en'=>3*/) as $lang => $langCode) {
            //Gets the limit
            $html_dom->load($HTTP_Call('https://lex.uz/'.$lang.'/search/nat?lang='.$langCode, '00'));
            $limit = $limit ?? ceil((int)preg_replace('/[^0-9]/', '', $html_dom->find('div.refind__result-export__title.mb-3')[0]->plaintext)/$step);

            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Processes the data
                $html_dom->load($HTTP_Call('https://lex.uz/'.$lang.'/search/nat?lang='.$langCode, $zero_buffer($page)));
                $laws = $html_dom->find('div.dd-table__main')[0]->find('tr.dd-table__main-item');
                foreach($laws as $law) {
                    //Gets values
                    $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(end(explode(', ', explode($yearWords[$lang], $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0]))));
                    $ID = $LBpage.':'.strtr(end(explode(' ', $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)), array('-'=>'', '’'=>'', '‘'=>''));
                    $name = $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->plaintext;
                    $country = '["UZ"]';
                    //Gets the regime
                    if (strtotime($enactDate) < strtotime('1991-08-31')) $regime = '{"uz":"O’zbekiston SSR", "en":"The Uzbek SSR"}';
                        else $regime = '{"uz":"O’zbekiston Respublikasi", "en":"The Republic of Uzbekistan"}';
                    //Gets the type and origin
                    $typeLine = explode(end(explode(', ', explode($yearWords[$lang], $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0])), $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0];
                    if (in_array($typeLine, array_keys($typesAndOrigins))) {
                        $type = $typesAndOrigins[$typeLine][0];
                        $origin = array($typesAndOrigins[$typeLine][1]);
                    } else {
                        $typeLine = str_replace(array_keys($sanitizeTypeLine), array_values($sanitizeTypeLine), $typeLine);
                        foreach ($types as $UZtype => $ENtype) {if (str_contains($typeLine, $UZtype)) {$type = $ENtype; $typeLine = explode($UZtype, $typeLine)[0]; break;}}
                        $origin = [];
                        if (in_array($typeLine, array_keys($origins))) {
                            $origin = ["uz"=>$typeLine, "en"=>$origins[$typeLine]];
                        } else {
                            foreach(explode(', ', $typeLine) as $originUZ) {
                                $originUZ = str_replace(array_keys($sanitizeOrigin), array_values($sanitizeOrigin), $originUZ);
                                $origin[] = ["uz"=>ucfirst($originUZ), "en"=>$origins[ucfirst($originUZ)]];
                            }
                        }
                    }
                    $origin = json_encode($origin, JSON_UNESCAPED_UNICODE);
                    //Gets the rest of the values
                    $status = 'Valid';
                    $source = 'https://lex.uz/'.$lang.'/docs/'.explode('/-', $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->href)[1];
                    $PDF = 'https://lex.uz/pdffile/'.explode('/-', $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->href)[1];

                    //Makes sure there are no quotes in the title
                    strtr($name, array("'" => "’", ' "'=>' “', '"' => "”"));

                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `laws".strtolower($LBpage)."` WHERE `ID`='".$ID."'";
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

                            //Creates SQL
                            $SQL2 = "UPDATE `laws".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';
                        $PDF = '{"uz":"'.$PDF.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `status`, `source`, `PDF`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$origin."', '".$type."', ".$status."', '".$source."', '".$PDF."')";
                    }

                    //Makes the query
                    echo 'p'.$page.': '.$law->find('span.dd-table__main-item_number')[0]->plaintext.' '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }

        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$LBpage."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>