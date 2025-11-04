<?php //Lebanon
    //!!Website: http://www.legallaw.ul.edu.lb/ not working !!\\

    //Settings
    $test = true; $scraper = 'LB';
    $start = 1910;//Which year to start from
    $limit = NULL;//Which year to end at
    $step = 20;//How many laws are on each page

    //Opens the parser (dom)
    require '../simple_html_dom.php';

    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Translates the types
    $types = array(
        'اعلان'=>'Announcement',
        'اعلام'=>'Informative Announcement',
        'كتاب'=>'Book',
        'الدستور'=>'The Constitution',
        'عقد'=>'Contract',
        'مرسوم'=>'Decree',
        'مرسوم نافذ حكما'=>'Legally enforceable Decree',
        'مرسوم إشتراعى'=>'Legislative Decree',
        'مرسوم منفذ بقانون'=>'Decree implemented by Law',
        'قرار بلدي'=>'Decision',
        'قرار اداري'=>'Administrative Decision',
        'قرار اساسي'=>'Basic Decision',
        'قرار نافذ حكما'=>'Legally enforceable Decision',
        'قرار وسيط'=>'Mediated Decision',
        'قرار مبدئي'=>'Preliminary Decision',
        'قرار المفوض السامي'=>'Decision of the High Commissioner',
        'ملحق'=>'Extension',
        'تعميم'=>'Generalization',
        'علم وخبر'=>'Information',
        'تعليمات'=>'Instruction',
        'دعوة'=>'Invitation',
        'قانون'=>'Law',
        ''=>'Law',
        'قانون نافذ حكما'=>'Law in Force',
        'قانون منفذ بمرسوم'=>'Law enforced by Decree',
        'لائحة'=>'List',
        'محضر جلسة'=>'Minutes of Hearing',
        'نموذج'=>'Model',
        'مختلف'=>'Multidimensional Law',
        'مذكرة'=>'Notice',
        'رأي'=>'Opinion',
        'أمر'=>'Order',
        'نظام'=>'System',
        'نظام داخلي'=>'Internal System',
        'محضر'=>'Record',
        'تقرير'=>'Report',
        'بلاغ'=>'Report',
        'قرار'=>'Resolution',
        'دفتر شروط'=>'Rulebook',
        'افادة'=>'Statement',
        'بيان'=>'Statement',
        'اتفاقية'=>'Treaty',
        'اتفاقية دولية'=>'Treaty',
        'انذار'=>'Warning',
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["LB"]';
    $publisher = '{"ar":"الجامعة اللبنانية","en":"The Lebanese University"}';

    //Gets the limit and loops through the years
    $limit = $limit ?? Date('Y');
    for ($year = $start; $year <= $limit; $year++) {echo '<br/>year: '.$year.'<br/>';
        //Gets the page limit
        $dom = file_get_html('http://www.legallaw.ul.edu.lb/LegisltaionSearch.aspx?AndOr=AND&optionID=-1&status=2445&lawYear='.$year); //echo $dom;
        $pageLimit = $dom->find('ul#MainContent_Pager1_ulPager')[0] ? explode('&pageNumber=', explode('&language', end($dom->find('ul#MainContent_Pager1_ulPager', 0)->find('li'))->find('a', 0)->href)[0])[1]:1; echo 'pageLimit: '.$pageLimit.'<br/>';
        for ($page = 1; $page <= $pageLimit; $page++) {echo "page: ".$page."<br/>";
            //Processes the data
            $dom = file_get_html('http://www.legallaw.ul.edu.lb/LegisltaionSearch.aspx?AndOr=AND&optionID=-1&status=2445&lawYear='.$year.'&pageNumber='.$page);
            //Gets the number of laws on a page
            if ($page == $pageLimit) {
                $lawsLimit = $dom->find('h2#MainContent_lbltotalCount')[0] ? explode('(', explode(')', $dom->find('h2#MainContent_lbltotalCount', 0)->plaintext)[0])[1]%$step:0;
            } else {$lawsLimit = $step;} echo 'lawsLimit: '.$lawsLimit.'<br/>';
            $laws = $dom->find('div#MainContent_mainLegTr')[0];
            for ($law = 0; $law < $lawsLimit; $law++) {
                //Gets values
                $enactDate = explode('/', explode(' ', trim($laws->find('span#MainContent_rptLaws_lbldate_'.$law, 0)->innertext))[1])[2].'-'.explode('/', explode(' ', trim($laws->find('span#MainContent_rptLaws_lbldate_'.$law, 0)->innertext))[1])[1].'-'.explode('/', explode(' ', trim($laws->find('span#MainContent_rptLaws_lbldate_'.$law, 0)->innertext))[1])[0]; $enforceDate = $enactDate; $lastactDate = $enactDate;
                $ID = $scraper.':'.explode('lawId=', $laws->find('div.extra-wrap')[$law]->find('a', 0)->href)[1];
                //Gets the regime
                switch (true) {
                    case strtotime($enactDate) < strtotime('1 September 1920'):
                        $regime = '{"ar":"الإمبراطورية العثمانية","en":"The Ottoman Empire"}';
                        break;
                    case strtotime('1 September 1920') < strtotime($enactDate) && strtotime($enactDate) < strtotime('22 November 1943'):
                        $regime = '{"ar":"الانتداب الفرنسي على لبنان","en":"The French Mandate for Lebanon"}';
                        break;
                    case strtotime('22 November 1943') < strtotime($enactDate) && strtotime($enactDate) < strtotime('today'):
                        $regime = '{"ar":"الجمهورية اللبنانية","en":"The Republic of Lebanon"}';
                        break;
                }
                //Gets the rest of the values
                $name = fixQuotes(trim($laws->find('div.extra-wrap')[$law]->find('a', 0)->plaintext), 'AR');
                $type = $types[$laws->find('span#MainContent_rptLaws_lblLawType_'.$law, 0)->innertext];
                //$isAmend = str_contains($name, 'تعديل') ? 1:0;
                $status = 'Valid';
                $source = 'http://www.legallaw.ul.edu.lb/'.$laws->find('div.extra-wrap')[$law]->find('a', 0)->href;

                //JSONifies the values
                $name = '{"ar":"'.$name.'"}';
                $source = '{"ar":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')"; echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }
    
    //Connects to the content database
    $username2 = "ug0iy8zo9nryq"; $password2 = "T_1&x+$|*N6F"; $database2 = "dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `Updated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>