<html><body>
    <?php //!!The only way to get the correct page is to have the right viewstate, a very long string of random characters...
        //Settings
        $test = true; $country = 'UZ';
        $start = 0;//Which law to start from
        $step = 20;//How many laws there are on each page
        $limit = null;//Total number of laws desired.

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory
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
                    '__VIEWSTATE' => 'LiFXTDi/qJdc0zLQQt8H18b45ZA1P8J3uQo1AccjWsL5Zc3JeCMrFSWVTCl66ufF141tdXCtMB/Tdzg1ZpBrSx90lyMQbMxh3zbCvNiRQt+RbsKjIhbQAUFfKfy0C4Ciky69MY4y+KyHgcoYJpZtP46rqYAYjavoKQeqBkGOfK+HjRnlC9CkkUXNLzQL50DzCQ6HJVg3MYZodOQ2J8ZR+5ujdW225jqOsBpGVoJtnSFwvMpC2kcXyFSf6uzXSgA63ZpNi/Me8bw+VkpBm0J0/sCxk3y/IMHktuK6PqR8ZQG4j7Bjy5fRFgeQoBANfdpe5nqllLB+orHLwwcgh172C/+S99m1R+lOqQl1hFI6cKD9y8I5X0tC1v91PjRubfOnMck5baBRIRF/LEtrwsJFi53NDut3q7ohLtm0Tc2bDPmaaMBFHfIuGQ6lh/KoTa8uxXm8uyXKE+cm8c+n+rsJ753NN28R1YkREYbnboO5P0ujieZMFBM1amvuDv8LlQR/AaHoavtmCpMxV8W6eazltx7bA5avliKuUu14DF4y+yaYsuZrYAtVmFAa9/DKanBiFVROitZIU6f6HYCl6Tqf1KaWINZjB2T7X280WkP2dIbUD6CAN1ZKb/RpqxwhhDq20XDRfbVOtNsFSbtec8t+nZlHdf8cnIdhWiA6IojN/z7S9uk3+iO+ye8NBMhPZhPv',
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

        //Gets the page codes
        $pageCodes = array(
            0=>'00',
            1=>'01',
            2=>'02',
            3=>'03',
            4=>'04',
            5=>'05',
            6=>'06',
            7=>'07',
            8=>'08',
            9=>'09',
            10=>'01',
            11=>'01',
            12=>'01',
            13=>'01',
            14=>'01',
            15=>'01',
            16=>'01',
            17=>'01',
            18=>'01',
            19=>'01',
            20=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
            1=>'01',
        );

        //Gets the types
        $types = array(
            'O‘zbekiston Respublikasi Sog‘liqni saqlash vazirligi, O‘zbekiston Respublikasi Investitsiyalar, sanoat va savdo vazirligi, O‘zbekiston Respublikasi Davlat aktivlarini boshqarish agentligining qarori, '=>'Decree',
            'O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligi, Kambag‘allikni qisqartirish va bandlik vazirligi, Iqtisodiyot va moliya vazirligining qarori, '=>'Decree',
            'O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligi, O‘zbekiston Respublikasi Maktabgacha va maktab ta’limi vazirligining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Oliy sudi Plenumining qarori, '=>'Supreme Court Decision',
            'O‘zbekiston Respublikasi Vazirlar Mahkamasining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Vazirlar Mahkamasi huzuridagi Soliq qo‘mitasining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Vazirlar Mahkamasi huzuridagi O‘zbekiston texnik jihatdan tartibga solish agentligi direktorining buyrug‘i, '=>'Resolution',
            'O‘zbekiston Respublikasi Markaziy banki boshqaruvining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Markaziy saylov komissiyasining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi qishloq xo‘jaligi vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi iqtisodiyot va moliya vazirining buyrug‘i, '=>'Order',
            'O‘zbekistan Respublikasi iqtisodiyot va moliya vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi ichki ishlar vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi adliya vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi favqulodda vaziyatlar vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Resublikasi kambag‘allikni qisqartirish va bandlik vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi istiqbolli loyihalar milliy agentligi direktorining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi Istiqbolli loyihalar milliy agentligi direktorining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi cog‘liqni saqlash vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi sog‘liqni saqlash vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi transport vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi energetika vazirining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi Qurilish va uy-joy kommunal xo‘jaligi vazirligi va Iqtisodiyot va moliya vazirligining qarori, '=>'Decree',
            'O‘zbekiston Respublikasi Davlat aktivlarini boshqarish agentligi direktorining buyrug‘i, '=>'Order',
            'O‘zbekiston Respublikasi Davlat xavfsizlik xizmati va Sog‘liqni saqlash vazirligining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Prezidentining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Davlat xavfsizlik xizmati va Sog‘liqni saqlash vazirligining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Prezidenti huzuridagi Statistika agentligi, Iqtisodiyot va moliya vazirligi, Investitsiyalar, sanoat va savdo vazirligining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Maktabgacha va maktab ta’limi vazirligi hamda Iqtisodiyot va moliya vazirligining qarori, '=>'Resolution',
            'O‘zbekiston Respublikasi Prezidentining Farmoni, '=>'Presidential Decree',
            'O‘zbekiston Respublikasi Prezidentining farmoyishi, '=>'Presidential Decree',
            'O‘zbekiston Respublikasining Qonuni, '=>'Law',

            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
        );

        $origins = array(
            'O‘zbekiston Respublikasi Vazirlar Mahkamasining qarori, '=>'The Cabinent of Ministers',
            'O‘zbekiston Respublikasi Vazirlar Mahkamasi huzuridagi Soliq qo‘mitasining qarori, '=>'The Cabinent of Ministers',
            'O‘zbekiston Respublikasi Vazirlar Mahkamasi huzuridagi O‘zbekiston texnik jihatdan tartibga solish agentligi direktorining buyrug‘i, '=>'The Director of the Agency for Technical Regulation under the Cabinet of Ministers',
            'O‘zbekiston Respublikasi Markaziy banki boshqaruvining qarori, '=>'The Central Bank',
            'O‘zbekiston Respublikasi Markaziy saylov komissiyasining qarori, '=>'The Central Elections Commission',
            'O‘zbekiston Respublikasi Davlat aktivlarini boshqarish agentligi direktorining buyrug‘i, '=>'The Director of the State Activities Management Agency',
            'O‘zbekiston Respublikasi istiqbolli loyihalar milliy agentligi direktorining buyrug‘i, '=>'The Director of the National Agency for Promising Projects',
            'O‘zbekiston Respublikasi Istiqbolli loyihalar milliy agentligi direktorining buyrug‘i, '=>'The Director of the National Agency for Promising Projects',
            'O‘zbekiston Respublikasi Prezidenti huzuridagi Statistika agentligi, Iqtisodiyot va moliya vazirligi, Investitsiyalar, sanoat va savdo vazirligining qarori, '=>'The Statistics Agency; Ministry of Economy and Finance; Ministry of Investments, Industry and Trade',
            'O‘zbekiston Respublikasi Davlat xavfsizlik xizmati va Sog‘liqni saqlash vazirligining qarori, '=>'The State Security Service; The Ministry of Health',
            'O‘zbekiston Respublikasining Qonuni, '=>'The Legislature',
            'O‘zbekiston Respublikasi qishloq xo‘jaligi vazirining buyrug‘i, '=>'The Ministry of Agriculture',
            'O‘zbekiston Respublikasi Qurilish va uy-joy kommunal xo‘jaligi vazirligi va Iqtisodiyot va moliya vazirligining qarori, '=>'The Ministry of Construction and Housing; The Ministry of Economy and Finance',
            'O‘zbekiston Respublikasi iqtisodiyot va moliya vazirining buyrug‘i, '=>'The Ministry of Economy and Finance',
            'O‘zbekistan Respublikasi iqtisodiyot va moliya vazirining buyrug‘i, '=>'The Ministry of Economy and Finance',
            'O‘zbekiston Respublikasi favqulodda vaziyatlar vazirining buyrug‘i, '=>'The Ministry of Emergency Siduations',
            'O‘zbekiston Respublikasi energetika vazirining buyrug‘i, '=>'The Ministry of Energy',
            'O‘zbekiston Respublikasi cog‘liqni saqlash vazirining buyrug‘i, '=>'The Ministry of Health',
            'O‘zbekiston Respublikasi sog‘liqni saqlash vazirining buyrug‘i, '=>'The Ministry of Health',
            'O‘zbekiston Respublikasi Sog‘liqni saqlash vazirligi, O‘zbekiston Respublikasi Investitsiyalar, sanoat va savdo vazirligi, O‘zbekiston Respublikasi Davlat aktivlarini boshqarish agentligining qarori, '=>'The Ministry of Health; The Ministry of Investments, Industry and Trade; The State Activities Management Agency',
            'O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligi, Kambag‘allikni qisqartirish va bandlik vazirligi, Iqtisodiyot va moliya vazirligining qarori, '=>'The Ministry of Higher Education, Science and Innovation; The Ministry of Poverty Reduction and Employment; The Ministry of Economy and Finance',
            'O‘zbekiston Respublikasi Oliy ta’lim, fan va innovatsiyalar vazirligi, O‘zbekiston Respublikasi Maktabgacha va maktab ta’limi vazirligining qarori, '=>'The Ministry of Higher Education, Science and Innovation; The Ministry of Preschool and School Education',
            'O‘zbekiston Respublikasi ichki ishlar vazirining buyrug‘i, '=>'The Ministry of Internal Affairs',
            'O‘zbekiston Respublikasi adliya vazirining buyrug‘i, '=>'The Ministry of Justice',
            'O‘zbekiston Resublikasi kambag‘allikni qisqartirish va bandlik vazirining buyrug‘i, '=>'The Ministry of Poverty and Employment',
            'O‘zbekiston Respublikasi Maktabgacha va maktab ta’limi vazirligi hamda Iqtisodiyot va moliya vazirligining qarori, '=>'The Ministry of Preschool and School Education; The Ministry of Finance',
            'O‘zbekiston Respublikasi transport vazirining buyrug‘i, '=>'The Ministry of Transportation',
            'O‘zbekiston Respublikasi Prezidentining qarori, '=>'The President',
            'O‘zbekiston Respublikasi Prezidentining Farmoni, '=>'The President',
            'O‘zbekiston Respublikasi Prezidentining farmoyishi, '=>'The President',
            'O‘zbekiston Respublikasi Oliy sudi Plenumining qarori, '=>'The Supreme Court',

            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
            ''=>'',
        );

        //Gets the years
        $years = array(
            'uz'=>' yilda',
            'ru'=>' г.',
            'en'=>' г.'
        );

        //Gets the location of date in string
        $dateLoc = array(
            'uz'=>1,
            'ru'=>2,
            'en'=>2
        );

        //Loops through the languages
        foreach (array('uz'=>4/*, 'ru'=>1, 'en'=>3*/) as $lang => $langCode) {
            //Gets the limit
            $html_dom->load($HTTP_Call('https://lex.uz/'.$lang.'/search/nat?lang='.$langCode, '00'));
            $limit = $limit ?? ceil((int)preg_replace('/[A-Za-z]/', '', $html_dom->find('div.refind__result-export__title.mb-3')[0]->plaintext)/$step);

            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Processes the data
                $html_dom->load($HTTP_Call('https://lex.uz/'.$lang.'/search/nat?lang='.$langCode, $zero_buffer($page)));
                $laws = $html_dom->find('div.dd-table__main')[0]->find('tr.dd-table__main-item');
                foreach($laws as $law) {
                    //Gets values
                    $enactDate = date('Y-m-d', strtotime(end(explode(', ', explode($years[$lang], $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0])))); $enforceDate = $enactDate;
                    $ID = $country.'-'.str_replace('-', '', end(explode(' ', explode(',', $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[1])));
                    $name = $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->plaintext;
                    $type = $types[explode(end(explode(', ', explode($years[$lang], $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0])), $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0]];
                    $origin = $origins[explode(end(explode(', ', explode($years[$lang], $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0])), $law->find('span.badge.badge-pill.badge-nine')[0]->plaintext)[0]];
                    $status = 'Valid';
                    $source = 'https://lex.uz/'.$lang.'/docs/'.explode('/-', $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->href)[1];
                    $PDF = '{"uz":"https://lex.uz/pdffile/'.explode('/-', $law->find('div.dd-table__main-left-desc')[0]->find('a.lx_link')[0]->href)[1].'"}';

                    //Makes sure there are no quotes in the title
                    if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

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

                            //Creates SQL
                            $SQL2 = "UPDATE `laws".strtolower($country)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `name`, `type`, `origin`, `status`, `source`, `PDF`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$ID."', '".$name."', '".$type."', '".$origin."', '".$status."', '".$source."', '".$PDF."')";
                    }

                    //Makes the query
                    echo $law->find('span.dd-table__main-item_number')->plaintext.' '.$SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
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