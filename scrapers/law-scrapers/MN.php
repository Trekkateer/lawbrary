<?php
    //Settings
    $test = false; $scraper = 'MN';
    $limit = []; //Total number of laws desired. Uses $typeID as key

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php'; // '../' refers to the parent directory

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Function to make the HTTP request
    function HTTP_Call ($page, $path, $typeID, $code) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://legalinfo.mn/'.$path,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'filtercategorytypeid' => $typeID,
                'code' => $code,
                'page' => $page,
                'filteractive' => '1',
                'isactive' => '1',
                'sort' => 'enacteddate',
                'sortType' => 'asc'
            ),
            CURLOPT_HTTPHEADER => array('Cookie: 8281324d871b97eb1ff5cbbcd6c443af=8dck3tm2r43h32sbrpc5c5206m'),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return json_decode($response, true)['Html'];
    };

    //Stores the type IDs
    $typeIDs = array(
        'Constitution' => [[26, 0], 'NULL'],
        'Law' => [[27, 1], '\'{"en":"The Parliament of Mongolia","mn":"Их Хурал"}\''],
        'Resolution' => [[28, 2], '\'{"en":"The Parliament of Mongolia","mn":"Их Хурал"}\''],
        'International Treaty' => [[29, 3], 'NULL'], //TODO: Add countries array
        'Decree' => [[30, 4], '\'{"en":"The President of Mongolia","mn":"Монгол Улсын Ерөнхийлөгч"}\''],
        'Court Decision' => [[31, 5], '\'{"en":"The Constitutional Court","mn":"Үндсэн хуулийн цэцийн"}\''],
        'Court Decision' => [[32, 6], '\'{"en":"General Council of the Judiciary","mn":"Шүүхийн ерөнхий зөвлөл"}\''],
        'Government Resolution' => [[33, 7], 'NULL'],
        'Order' => [[34, 8], 'NULL'], //('Сайдын тушаал') TODO: Add ministries array
        'Order' => [[35, 9], 'NULL'], //('Засгийн газрын агентлагийн даргын тушаал') TODO: Add agencies array
        'Order' => [[36, 10], 'NULL'], //('УИХ-аас томилогддог байгууллагын дарга, түүнтэй адилтгах албан тушаалтны шийдвэр')
        'Decision' => [[37, 11], 'NULL'], //('Аймаг, нийслэлийн ИТХ-ын шийдвэр') TODO: Add provinces array
        'Decision' => [[38, 12], '{"mn":"Аймаг, нийслэлийн Засаг дарга", "en":"The Governor of the Aimag and the Capital City"}'],
        'Decision' => [[180, 20], 'NULL'],
        'Decision' => [[186, 87], '{"mn":"Зөвлөл, хороо, бусад байгууллага", "en":"The Council, District, and Other Organizations"}'],
        'Decision' => [[390, 390], '{"mn":"Хууль, хяналтын байгууллага", "en":"The Legal and Regulatory Authority"}'],
    );

    //Loops through the languages
    foreach (array('mn'=>'mn/ajaxList/', /*'en'=>'en/eajaxList/'*/) as $lang => $path) {//Scraping the English page is not necessary
        //Loops through the types
        foreach ($typeIDs as $type => $typeInfo) {
            //Gets the limit
            $limit = $limit[$typeInfo[0][0]] ?? explode('/', str_get_html(HTTP_Call(1, $path, $typeInfo[0][0], $typeInfo[0][1]))->find('div.shine-huuli-footer', 0)->find('ul', 0)->find('li.number', 0)->plaintext)[1];
            //Loops through the pages
            for ($page = 1; $page <= $limit; $page++) {
                //Processes the data in the table
                $dom = str_get_html(HTTP_Call($page, $path, $typeInfo[0][0], $typeInfo[0][1]));
                $laws = $dom->find('div.shine-huuli-body', 0)->find('div.shine-huuli-content');
                foreach ($laws as $law) {$law = $law->find('div', 0);
                    //Gets the date
                    $enactDate = trim($law->find('div[data-block="enacteddate"]', 0)->plaintext);
                    $enforceDate = trim($law->find('div[data-block="enforcementdate"]', 0)->plaintext) ?: $enactDate;
                    //Gets the regime and the publisher
                    switch (true) {
                        default:
                            $regime = '{"mn":"Монгол Улс", "en":"Mongolia"}'; break;
                        case strtotime($enactDate) < strtotime('12 February 1992'):
                            $regime = '{"mn":"Бүгд Найрамдах Монгол Ард Улс", "en":"The Mongolian Peopleꞌs Republic"}'; break;
                        case strtotime($enactDate) < strtotime('26 November 1924'):
                            $regime = '{"mn":"Монгол Улс", "en":"The Bogd Khaanate of Mongolia"}'; break;
                        case strtotime($enactDate) < strtotime('29 December 1911'):
                            $regime = '{"mn":"Чин улс", "en":"The Great Qing Dynasty"}'; break;
                        case strtotime($enactDate) < strtotime('1691'):
                            $regime = '{"mn":"Их Монгол Улс", "en":"The Mongol Empire"}'; break;
                    }
                    $publisher = '{"en":"The Unified Legal Information System","mn":"Эрх зүйн мэдээллийн нэгдсэн систем"}';
                    //Gets the ID
                    $ID = $scraper.':'.explode('lawId=', $law->find('div[data-block="title"]', 0)->find('a', 0)->href)[1];
                    //Gets the name
                    $name = array($lang => trim($law->find('div[data-block="title"]', 0)->find('a.act-name', 0)->plaintext));
                    if ($law->find('div[data-block="title"]', 0)->find('a[title^="Орчуулга: "]', 0)) {//TODO: Find a way to get the English name
                        $name['en'] = trim($law->find('div[data-block="title"]', 0)->find('a[title^="Орчуулга: "]', 0)->href);
                    }
                    //Gets the rest of the values
                    $origin = $typeInfo[1];
                    $status = $law->find('div[data-block="inactive"]', 0)->class === 'fa fa-check' ? 'Valid':'Invalid';
                    $source = $law->find('div[data-block="title"]', 0)->find('a.act-name', 0)->href;

                    //Makes sure there are no quotes in the title
                    $name = array_map(function ($item) {return strtr($item, ["'" => "ꞌ"]);}, $name);

                    //JSONifies the values
                    $name = json_encode($name, JSON_UNESCAPED_UNICODE);
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = '{"'.$lang.'":"'.$source.'"}';
                    
                    //Inserts the new laws
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `savedDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `status`, `source`) 
                                VALUES ('".$enactDate."', '".$enforceDate."', '".date('Y-m-d')."', '".$ID."', '".$name."', '".'["MN"]'."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }
    }
    
    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>