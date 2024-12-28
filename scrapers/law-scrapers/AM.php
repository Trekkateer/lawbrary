<html><body>
    <?php //TODO: Implement code to tell which law an amendment is amending
        //Settings
        $test = false; $LBpage = 'AM';

        //Opens the parser (HTML_DOM)
        define('MAX_FILE_SIZE', 6000000);//From simple_html_dom
        include '../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($LBpage)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Preloop arrays
        $types = array('ՀՕ'=>'Law', 'Ն'=>'Decision', 'ԱԺՈ'=>'Decision', 'ԱԺՆՈ'=>'Decision', 'ԱԺՆՈ'=>'Decision', 'Հ'=>'Statement', 'Ու'=>'Address', 
                       'ЗР'=>'Law', 'Н'=>'Decision', 'ПНС'=>'Decision', 'З'=>'Statement',
                       'LA'=>'Law', 'N'=>'Law',      'NAD'=>'Decision', 'S'=>'Statement', 'D'=>'Statement');
        $typeFixer = array('1279'=>'ՀՕ', '4756'=>'ՀՕ', '4925'=>'Ն', '2040'=>'ՀՕ', '4780'=>'ՀՕ', '1281'=>'ՀՕ', '1793'=>'ՀՕ', '1795'=>'ՀՕ', '2698'=>'Ն', '2911'=>'ՀՕ', '3037'=>'Ն', '4584'=>'Ն', '3183'=>'Ն', '3186'=>'Ն', '3185'=>'Ն', '3184'=>'Ն', '1298'=>'ՀՕ', '1484'=>'ՀՕ', '1305'=>'ՀՕ', '1272'=>'ՀՕ', '1552'=>'ՀՕ', '7758'=>'Ն');
        $dateFixer = array('2230'=>'2004-02-02', '1915'=>'2003-12-03', '2804'=>'2006-12-21', '3058'=>'2007-06-07', '3057'=>'2007-06-07', '3059'=>'2007-06-07', '3087'=>'2007-11-12', '3061'=>'2007-06-07', '3056'=>'2007-06-07', '3053'=>'2007-06-07', '4925'=>'2014-04-29', '3055'=>'2007-06-07', '1976'=>'2003-09-09', '3052'=>'2007-06-07', '3086'=>'2007-11-05', '5805'=>'2015-12-06', '1'=>'1995-07-05', '2698'=>'2006-07-07', '3037'=>'2007-06-26', '2386'=>'2005-09-28', '3175'=>'2008-02-05', '2584'=>'1991-07-29', '1974'=>'2004-03-15', '1978'=>'2003-09-10', '3054'=>'2007-06-07', '3127'=>'2007-12-06', '3183'=>'2008-03-04', '3186'=>'2008-03-04', '3185'=>'2008-03-04', '3184'=>'2008-03-04', '3051'=>'2007-06-07', '3060'=>'2007-06-07', '3050'=>'2007-06-07', '2614'=>'1994-07-04', '1977'=>'2003-09-10', '1281'=>'2001-04-26');

        //Loops through languages
        foreach (array('arm'=>'hy', 'rus'=>'ru', 'eng'=>'en', 'frn'=>'fr') as $locale=>$lang) {
            //Gets the HTML 
            $html_dom = file_get_html('http://www.parliament.am/legislation.php?sel=alpha&lang='.$locale);

            //Processes the data in the table
            $laws = $html_dom->find('a[target="_new"]');
            foreach ($laws as $law) {
                //Gets Hyperref
                $source = 'http://www.parliament.am'.$law->href;
                if ($source !== 'http://www.parliament.am#') {//Makes sure we can get the source
                    //Gets ID, date, name, and type
                    $ID = $LBpage.'-'.explode('&lang', explode('ID=', $source)[1])[0];
                        if ($law->class === "blue_mid_norm") {$lastID = $ID;}
                    $enactDate = date('Y-m-d', strtotime(substr(end(explode(' (', $law->plaintext)), 0, 10)));
                        if ($enactDate === '1970-01-01' || $ID === 'AM-1281') {$enactDate = $dateFixer[explode('-', $ID)[1]];} $enforceDate = $enactDate; $lastactDate = $enforceDate;
                    $name = trim(explode(' ('.substr(end(explode(' (', $law->plaintext)), 0, 10), $law->plaintext)[0]);
                    $country = '["AM"]';
                    if (strtotime($enactDate) < strtotime('23 September 1991')) {$regime = '{"hy":"Հայկական ԽՍՀ", "en":"The Armenian SSR", "ru":"Армянская ССР"';}
                        else {$regime = '{"hy":"Հայաստանի Հանրապետություն", "en":"The Republic of Armenia", "ru":"Республика Армения"}';}
                    $type = explode(')', end(explode(explode('-', $enactDate)[0].' ', $law->plaintext)))[0];
                        if (explode('-', $type)[0] === '' || $ID === 'AM-1281') {$type = $typeFixer[explode('-', $ID)[1]];}
                        $type = $types[explode('-', $type)[0]];
                    //What to do if the law is an amendment
                    if ($law->class === "blue_sm_11") {
                        $amends = "'[\"".$lastID."\"]'";
                        //Changes the amendedBy field of the last law
                        $SQL21 = "SELECT * FROM `laws".strtolower($LBpage)."` WHERE `ID`='".$lastID."'";
                        $result = $conn->query($SQL21);
                        $row = $result->fetch_assoc();
                        $amendedBy = json_decode($row['amendedBy'], true);
                        $amendedBy[] = $ID;
                        $amendedBy = json_encode($compoundedAmendedBy, JSON_UNESCAPED_UNICODE);
                        $SQL21 = "UPDATE `laws".strtolower($LBpage)."` SET `amendedBy`='".$amendedBy."' WHERE `ID`='".$lastID."'";
                        echo $SQL21.'<br/>'; $conn->query($SQL21);
                        //Changes the lastactDate
                        $SQL23 = "UPDATE `laws".strtolower($LBpage)."` SET `lastactDate`='".$enactDate."' WHERE `ID`='".$lastID."'";
                        echo '<br/>'.$SQL23.'<br/>'; $conn->query($SQL23);
                    } else {$amends = 'NULL';}
                    $status = 'Valid';

                    //Makes sure there are no appostophes in the title
                    $name = strtr($name, array(" '"=>" ‘", "'"=>"’"));

                    //Creates SQL
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

                            $SQL2 = "UPDATE `laws".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `type`, `amends`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$type."', ".$amends.", '".$status."', '".$source."')";
                    }

                    //Executes the SQL
                    if ($ID !== 'AM-8251' && $ID !== 'AM-2592') {
                        echo $SQL2.'<br/>';
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
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$LBpage."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>