<?php
    //Settings
    $test = false; $scraper = 'RW';
    $start = 0;//Which law to start from
    $limit = null;//Total number of pages desired. Set to null to get number automatically

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings only
    error_reporting(E_ALL & ~E_WARNING);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Creates function to capitalize with exceptions
    $exceptions = [
        'ba', 'bwo', 'ku', 'mu', 'rya', 'ryo', 'wa', 'ya', 'yo',
        'a', 'à', 'aux', 'ce', 'de', 'des', 'du', 'en', 'et', 'la', 'le', 'les', 'ou', 'par', 'por', 'sa', 'sur', 'tel', 'un', 'une', 'que',
        'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'up'
    ];
    function ucwordsexcept($str, $delims=' ') {global $exceptions;
        //Capitalizes words in a string except for those in the exceptions array
        $out = array(trim($str));
        foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
            if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
            $out[$key+1] = '';
            foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                $out[$key+1] .= !in_array($word, $exceptions) ? mb_strtoupper($word[0], 'UTF-8').substr($word, 1).$delim:$word.$delim;
            }
            $out[$key+1] = rtrim($out[$key+1], $delim);
        }
        return ucfirst(end($out));
    }

    //Sanitizes the title
    $sanitizeFirst = [
        'ТО'=>'to', 'ТНЕ'=>'the', '–'=>'-', '•\t'=>'', '•'=>'', ' "'=>' \\"', '"'=>'\\"', '“'=>'\\"', '‘’'=>'\\"', '’’'=>'”', ' °'=>'° ', ' ̊'=>'° ', 'À'=>'À', '( '=>'(', '  '=>' ',
    ];
    $sanitizeAfter = ['declet-loi'=>'décret-loi', 'decret'=>'décret', 'decret-loi'=>'décret-loi', 'decret loi'=>'Décret-loi', 'decrete-loi'=>'Décret-loi', 'décret - loi'=>'Décret-loi', 'décret -loi'=>'Décret-loi', 'decret –loi'=>'Décret-loi', 'décrêt -loi'=>'Décret-loi', 'conventon'=>'Convention', 'ordonnance -loi'=>'Ordonnance-loi', 'ordonance'=>'ordonnance', 'organic-law'=>'Organic Law', 'min. decree'=>'Ministerial Decree', 'ministrial order'=>'Ministerial Order',
                        'pre si dent i al'=>'Presidential', 'a n d d e t e r m i n i n g i t s'=>'and determining its', 'law(mhc)law'=>'Law (MHC) Law',
                        'Protocol ?�ν Amendments ?�ο ?�ηε Act of ?�ηε African Uνιον'=>'Protocol & Amendments to the Act of the African Union',
                        'p.o '=>'PO ', 'anti- '=>'anti-', 'ii)'=>'II)', 'xxviii'=>'XXVIII',
                        '(accidents'=>'(Accidents','(africare'=>'(Africare', '(agriculture'=>'(Agriculture', '(ama)'=>'(AMA)', '(aripo)'=>'(ARIPO)', '(biological'=>'(Biological', '(building'=>'(Building', '(cab international)'=>'(CAB International)', '(c.e.p.g.l)'=>'(CEPGL)', '(cdf)'=>'(CDF)', '(chu)'=>'(CHU)', '(ciciba)'=>'(CICIBA)', '(cloque'=>'(Cloque', '(cma)'=>'(CMA)', '(cross'=>'(Cross', '(dasso)'=>'(DASSO)', '(eala)'=>'(EALA)', '(eccas)'=>'(ECCAS)', '(electrogaz'=>'(Electrogaz', '(employment'=>'(Employment', '(esaamlg)'=>'(ESAAMLG)', '(fagace)'=>'(FAGACE)', '(fees'=>'(Fees', '(fer)'=>'(FER)', '(fonerwa)'=>'(FONERWA)', '(guadalajara'=>'(Guadalajara', '(hamburg'=>'(Hamburg', '(hida)'=>'(HIDA)', '(ilpd)'=>'(ILPD)', '(inmr)'=>'(INMR)', '(isa)'=>'(ISA)', '(isae)'=>'(ISAE)','(isar)'=>'(ISAR)', '(khi)'=>'(KHI)', '(kiac)'=>'(KIAC)', '(kie)'=>'(KIE)', '(kist)'=>'(KIST)', '(licensing'=>'(Licensing', '(lnr)'=>'(LNR)', '(loda)'=>'(LODA)', '(meteo'=>'(Meteo', '(mhc)'=>'(MHC)', '(nafa)'=>'(NAFA)', '(naeb)'=>'(NAEB)', '(nica)'=>'(NICA)', '(nisr)'=>'(NISR)', '(nlc)'=>'(NLC)', '(npo)'=>'(NPO)', '(o.a.m.caf)'=>'(OAMCAF)', '(occupational'=>'(Occupational', '(ocir)'=>'(OCIR)', '(ocir-the)'=>'(OCIR)', '(ogmr)'=>'(OGMR)', '(ortpn)'=>'(ORTPN)', '(pct)'=>'(PCT)', '(palindaba'=>'(Palindaba', '(public'=>'(Public', '(protocol'=>'(Protocol', '(rab)'=>'(RAB)', '(rada)'=>'(RADA)', '(ralsa)'=>'(RALSA)', '(rarda)'=>'(RARDA)', '(rbc)'=>'(RBC)', '(rbs)'=>'(RBS)', '(rcaa)'=>'(RCAA)', '(rcs)'=>'(RCS)', '(rdb)'=>'(RDB)', '(rdrc)'=>'(RDRC)', '(reb)'=>'(REB)', '(rema)'=>'(REMA)', '(revised'=>'(Revised', '(rha)'=>'(RHA)', '(rhoda)'=>'(RHODA)', '(rldsf)'=>'(RLDSF)', '(rlrc)'=>'(RLRC)', '(rmi)'=>'(RMI)', '(rmh)'=>'(RMH)', '(rmf)'=>'(RMF)', '(r.n.c.u.)'=>'(RNCU)', '(rnra)'=>'(RNRA)', '(rppa)'=>'(RPPA)', '(rra)'=>'(RRA)', '(rsb)'=>'(RSB)', '(rssb)'=>'(RSSB)', '(rtda)'=>'(RTDA)', '(rura)'=>'(RURA)', '(seabed'=>'(Seabed', '(sez)'=>'(SEZ)', '(sfar)'=>'(SFAR)', '(sfb'=>'(SFB)', '(sgf)'=>'(SGF)', '(social'=>'(Social', '(trac'=>'(Trac', '(tig)'=>'(TIG)', '(t bonds)'=>'(T-Bonds)', '(t-bonds)'=>'(T-Bonds)', '(unesco)'=>'(UNESCO)', '(underground'=>'(Underground', '(unr)'=>'(UNR)', '(upu)'=>'(UPU)', '(ur)'=>'(UR)', '(warda)'=>'(WARDA)', '(wda)'=>'(WDA)', '(women'=>'(Women',
                        'n°'=>'N° ', 'n˚'=>'N° ', 'n0'=>'N° ', 'nº'=>'N° ', ' n o '=>' N° ', 'no.'=>'N° ', 'n°'=>'N° ',  'no 0'=>'N° 0', 'no 1'=>'N° 1', 'no 2'=>'N° 2', 'no 3'=>'N° 3', 'no 4'=>'N° 4', 'no 5'=>'N° 5', 'no 6'=>'N° 6', 'no 7'=>'N° 7', 'no 8'=>'N° 8', 'no 9'=>'N° 9',
                        '`s'=>'’s', '‟s'=>'’s', "'"=>'’', ' "'=>' “', '"'=>'”', '_'=>' ',
                        '  '=>' '
    ];

    //Detects the language and translates the types
    $typesByLang = array(
        'rw'=>array(
            'Itegeko'=>'Act',
            'Amabwiriza ya Minisitiri'=>'Ministerial Instruction',
            'Amabwiriza ya Komisoyo Y’igihugu Y’amatora'=>'National Election Commission Instruction',
            'Amabwiriza'=>'Instruction',
            'Iteka rya Perezida'=>'Presidential Decree',
            'Uburyo'=>'Procedure',
        ),
        'fr'=>array(
            'Loi Organique'=>'Organic Law',
            'Loi'=>'Act',
            'Circulaire Presidentielle'=>'Presidential Circular',
            'Constitution de'=>'Constitution',
            'Déclaration'=>'Declaration',
            'Arrete du Premier Ministre'=>'Order of the Prime Minister',
            'Arrete'=>'Order',
            'Décret-loi'=>'Legal Decree',
            'Décret'=>'Decree',
            'Arret Ministeriel'=>'Ministerial Decree',
            'Arrete Ministeriel'=>'Ministerial Decree',
            'Arrete Presidentiel'=>'Presidential Decree',
            'Ordonnance-loi'=>'Legal Ordinance',
            'Ordonnance'=>'Ordinance',
            'Mo N°'=>'Ministerial Decree',
            'MO N°'=>'Ministerial Decree',
            'Po N°'=>'Presidential Decree',
            'PO N°'=>'Presidential Decree',
            'Ap N°'=>'Presidential Order',
            'AP N°'=>'Presidential Order',
        ),
        'en'=>array(
            'Law'=>'Act',
            'Agreement'=>'Agreement',
            'Basic Text'=>'Basic Text',
            'Bond Issuance'=>'Bond Issuance',
            'Charter'=>'Charter',
            'Code'=>'Code',
            'Constitution of'=>'Constitution',
            'Constitution (unesco)'=>'Constitution',
            'Constitutive Act'=>'Constitutive Act',
            'Convention'=>'Convention',
            'Covenant'=>'Covenant',
            'Declaration'=>'Declaration',
            'Ministerial Decree'=>'Ministerial Decree',
            'Presidential Decree'=>'Presidential Decree',
            'Practice Directions of the President of the Supreme Court'=>'Practice Directions',
            'Commissioner General Directives'=>'Directive',
            'Directives of the Commissioner General'=>'Directive',
            'Directives'=>'Directive',
            'Directive'=>'Directive',
            'Guidelines'=>'Guidelines',
            'Ministerial Instruction'=>'Ministerial Instruction',
            'Prime Minister’s Instructions'=>'Instructions of the Prime Minister',
            'Instructions'=>'Instructions',
            'Nstructions'=>'Instructions',
            'Organic Law'=>'Organic Law',
            'Memorandum'=>'Memorandum',
            'Ministerial Order'=>'Ministerial Order',
            'Presidential Order'=>'Presidential Order',
            'Prime Minister Order'=>'Order of the Prime Minister',
            'Prime Minister’s Order'=>'Order of the Prime Minister',
            'Order'=>'Order',
            'Pact'=>'Pact',
            'Protocol'=>'Protocol',
            'Regulation'=>'Regulation',
            'Regulations'=>'Regulation',
            'Commissioner General Rules'=>'Rules',
            'Rules'=>'Rules',
            'Statutes'=>'Statute',
            'Statute'=>'Statute',
            'Treaty'=>'Treaty',
            'Reaty'=>'Treaty',
        )
    );

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["RW"]';
    $publisher = '{"rw":"Leta y’u Rwanda", "fr":"Le Gouvernement du Rwanda", "en":"The Government of Rwanda"}';

    //Loops through the languages
    foreach (array('rw', 'fr', 'en') as $lang) {
        //Finds the limit
        $API_Call = json_decode(file_get_contents('https://apis.amategeko.gov.rw/v1/site/documents/search?start='.$start.'&length=0&language='.$lang.'&sections=1.1,1.2'), true);
        $limit = $limit ?? $API_Call['data']['recordsFiltered'];
        //Gets the laws
        $laws = json_decode(file_get_contents('https://apis.amategeko.gov.rw/v1/site/documents/search?start='.$start.'&length='.$limit.'&language='.$lang.'&sections=1.1,1.2'), true)['data']['data'];
        foreach ($laws as $lawNum => $law) {
            //Resets the language
            $langCurr = $lang;

            //Interprets the data
            $enactDate = $enforceDate = $lastactDate = $law['_source']['document_date'];
            $ID = $scraper.':'.$law['_id'];
            $name = ucwordsexcept(strtr(mb_strtolower(strtr($law['_source']['document_name'], $sanitizeFirst), 'UTF-8'), $sanitizeAfter), ' ');
            //Gets the regime
            if (strtotime($enactDate) < strtotime('0 January 1897')) {
                $regime = '{"rw":"Ubwami bw’u Rwanda", "fr":"Le Royaume du Rwanda", "en":"The Kingdom of Rwanda"}';
            } else if (strtotime($enactDate) < strtotime('6 May 1916')) {
                $regime = '{"rw":"Ubwami bw’u Budage", "fr":"L’Empire Allemand", "en":"The German Empire"}';
            } else if (strtotime($enactDate) < strtotime('1 July 1962')) {
                $regime = '{"rw":"Ubwami bw’u Bubiligi", "fr":"Le Royaume de Belgique", "en":"The Kingdom of Belgium"}';
            } else if (strtotime($enactDate) <= strtotime('today')) {
                $regime = '{"rw":"Republika y’u Rwanda", "fr":"La République du Rwanda", "en":"The Republic of Rwanda"}';
            }
            //Gets the type
            $type = '';
            foreach ($typesByLang as $language => $types) {
                foreach ($types as $search => $replacement) {
                    if (str_contains($name, $search.' ') || str_contains($name, $search.', ') || str_ends_with($name, ' '.$search)) {
                        $langCurr = $language;
                        $type = $replacement;
                        break 2;
                    }
                }
            }
            $isAmend = str_contains($name, 'Amendment') || str_contains($name, 'Amending') || str_contains($name, 'Ivugururwa') || str_contains($name, 'Modification') || str_contains($name, 'Modifying') || str_contains($name, 'Revision') ? 1:0;
            //Gets the rest of the values
            $status = 'Valid';
            $topicRW = fixQuotes($law['_source']['document_category_name_rw'], 'rw'); $topicFR = fixQuotes($law['_source']['document_category_name_fr'], 'fr'); $topicEN = fixQuotes($law['_source']['document_category_name'], 'en');
            $source = 'https://amategeko.gov.rw/view/toc/doc/'.$law['_source']['document_id'].'/'.$law['_id'];

            //JSONifies the topic
            $topic = '{"rw":"'.$topicRW.'", "fr":"'.$topicFR.'", "en":"'.$topicEN.'"}';

            //Queries to see if the law already exists
            $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
            $result = $conn->query($SQL);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    //JSONifies the name
                    $compoundedName = json_decode($row['name'], true);
                    $compoundedName[$langCurr] = $name;
                    $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                    //JSONifies the href
                    $compoundedSource = json_decode($row['source'], true);
                    $compoundedSource[$langCurr] = $source;
                    $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                    $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                }
            } else {
                //JSONifies the name and href
                $name = '{"'.$langCurr.'":"'.$name.'"}';
                $source = '{"'.$langCurr.'":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `topic`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$topic."', '".$source."')";
            }

            echo $lang.$lawNum.' '.$SQL2.'<br/>';
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