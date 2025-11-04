<?php //Greece
    //!!Cookies need to be updated regularly. Website is blocking my requests!!

    //Settings
    $test = true; $scraper = 'GR';
    $start = 1;//Which law to start from
    $step = 10;//How many laws per page
    $limit = NULL;//Total number of laws desired

    //Opens my library
    include '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $API_Call = function ($page) use ($step) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.hellenicparliament.gr/api.ashx?q=laws&pageNo='.$page.'&pageSize='.$step,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                //Cookie is not working
                'Cookie: __Secure-ASPSESSION=tw20tkxf0r2kzsbsbekjhyew; cookiesession1=678B287D8901234ABCDEFGHIJKLM1F96;'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl); echo $response;
        return json_decode($response, true);
    };

    //Translates the types
    $types = array(
        'Νόμος'=>'Law',
        'Σχέδιο νόμου'=>'Draft',
        'Πρόταση νόμου'=>'Proposal',
        'Υπο κατάθεση Νομοσχέδια'=>'Bill Under Submission',
        'Διεθνής Σύμβαση'=>'Treaty',
        'Αποφάσεις Βουλής'=>'House Decision',
        'Προσχέδιο κρατικού προϋπολογισμού'=>'State Budget Draft',
        'Πρόταση αναθεώρησης του Συντάγματος'=>'Constitutional Amendment Proposal'
    );

    //Translates the origins
    $origins = array(
        'Επικρατείας (Άκης Σκέρτσος)'=>'The Ministry of State',
        'Επικρατείας (Μάκης Βορίδης)'=>'The Ministry of State',
        'Επικρατείας (Σταύρος Παπασταύρου)'=>'The Ministry of State',
        'Παρά τω Πρωθυπουργώ (Μυλωνάκης)'=>'The Prime Minister',
        'Παρά τω Πρωθυπουργώ αρμόδιος για θέματα κρατικής αρωγής και αποκατάστασης'=>'The Prime Minister',
        'στον  Πρωθυπουργό'=>'The Prime Minister',
        'Προς τον Πρωθυπουργό'=>'The Prime Minister',
        'Εθνικής Οικονομίας και Οικονομικών'=>'The Ministry of National Economy and Finance',
        'Ανάπτυξης και Επενδύσεων'=>'The Ministry of Developments and Investments',
        'Εξωτερικών'=>'The Ministry of Foreign Affairs',
        'Παιδείας, Θρησκευμάτων και Αθλητισμού '=>'The Ministry of Education, Religion and Sports',
        'Προστασίας του Πολίτη'=>'The Ministry of Citizen Protection',
        'Εθνικής Άμυνας'=>'The Ministry of National Defense',
        'Παιδείας και Θρησκευμάτων'=>'The Ministry of Education and Religion',
        'Εργασίας και Κοινωνικής Ασφάλισης'=>'The Ministry of Labor and Social Security',
        'Ανάπτυξης'=>'The Ministry of Development',
        'Υγείας'=>'The Ministry of Health',
        'Περιβάλλοντος και Ενέργειας'=>'The Ministry of Environment and Energy',
        'Πολιτισμού και Αθλητισμού'=>'The Ministry of Culture and Sports',
        'Δικαιοσύνης'=>'The Ministry of Justice',
        'Εσωτερικών'=>'The Ministry of the Interior',
        'Πολιτισμού'=>'The Ministry of Culture',
        'Μετανάστευσης και Ασύλου'=>'The Ministry of Immigration and Asylum',
        'Κοινωνικής Συνοχής και Οικογένειας'=>'The Ministry of Social Cohesion and Family',
        'Ψηφιακής Διακυβέρνησης'=>'The Ministry of Digital Governance',
        'Υποδομών και Μεταφορών'=>'The Ministry of Infrastructure and Transport',
        'Ναυτιλίας και Νησιωτικής Πολιτικής'=>'The Ministry of Maritime and Island Policy',
        'Αγροτικής Ανάπτυξης και Τροφίμων'=>'The Ministry of of Rural Development and Food',
        'Τουρισμού'=>'The Ministry of Tourism',
        'Κλιματικής Κρίσης και Πολιτικής Προστασίας'=>'The Ministry of Climate Crisis and Civil Protection',
        'Επικρατείας (Γεραπετρίτης)'=>'The Ministry of State',
        'Επικρατείας (Σκέρτσος)'=>'The Ministry of State',
        'Αιγαίου'=>'The Ministry of the Aegean Sea (Dissolved)',
        'Αναπτυξης'=>'The Ministry of Development (Dissolved)',
        'Ανάπτυξης Ανταγωνιστικότητας και Ναυτιλίας'=>'The Ministry of the Development of Competitiveness and Shipping (Dissolved)',
        'Ανάπτυξης και Ανταγωνιστικότητας'=>'The Ministry of the Development of Competitiveness (Dissolved)',
        'Ανάπτυξης, Ανταγωνιστικότητας, Υποδομών, Μεταφορών και Δικτύων'=>'The Ministry of Development, Competitiveness, Infrastructure, Transport and Networks (Dissolved)',
        'Απασχόλησης και Κοινωνικής Προστασίας'=>'The Ministry of Employment and Social Protection (Dissolved)',
        'Βιομηχανίας, Ενέργειας και Τεχνολογίας'=>'The Ministry of Industry, Energy and Technology (Dissolved)',
        'Γεωργίας'=>'The Ministry of Georgia (Dissolved)',
        'Δημόσιας Τάξης'=>'The Ministry of Public Order (Dissolved)',
        'Δημόσιας Τάξης και Προστασίας του Πολίτη'=>'The Ministry of Public Order and Citizen Protection (Dissolved)',
        'Δικαιοσύνης, Διαφάνειας και Ανθρωπίνων Δικαιωμάτων'=>'The Ministry of Justice, Transparency and Human Rights (Dissolved)',
        'Διοικητικής Ανασυγκρότησης'=>'The Ministry of Administrative Restructuring (Dissolved)',
        'Διοικητικής Μεταρρύθμισης και Ηλεκτρονικής Διακυβέρνησης'=>'The Ministry of Administrative Reform and Electronic Government (Dissolved)',
        'Εθνικής Οικονομίας'=>'The Ministry of National Economy (Dissolved)',
        'Εθνικής Παιδείας και Θρησκευμάτων'=>'The Ministry of National Education and Religion (Dissolved)',
        'Εμπορικής Ναυτιλίας'=>'The Ministry of Merchant Shipping (Dissolved)',
        'Εμπορικής Ναυτιλίας, Αιγαίου και Νησιωτικής Πολιτικής'=>'The Ministry of Merchant Shipping, the Aegean Sea and Island Policy (Dissolved)',
        'Εμπορίου'=>'The Ministry of Trade (Dissolved)',
        'Επικρατείας για την καταπολέμηση της διαφθορά'=>'The Ministry of State to fight corruption (Dissolved)',
        'Εργασίας και Κοινωνικής Αλληλεγγύης'=>'The Ministry of Labor and Social Solidarity (Dissolved)',
        'Εργασίας και Κοινωνικής Ασφάλισης.'=>'The Ministry of Labor and Social Security (Dissolved)',
        'Εργασίας και Κοινωνικών Ασφαλίσεων'=>'The Ministry of Labor and Social Insurance (Dissolved)',
        'Εργασίας, Κοινωνικής Ασφάλισης και Κοινωνικής Αλληλεγγύης'=>'The Ministry of Labour, Social Security and Social Solidarity (Dissolved)',
        'Εργασίας, Κοινωνικής Ασφάλισης και Πρόνοιας'=>'The Ministry of Labour, Social Security and Welfare (Dissolved)',
        'Εσωτερικών και Διοικητικής Ανασυγκρότησης'=>'The Ministry of Internal and Administrative Reconstruction (Dissolved)',
        'Εσωτερικών, Αποκέντρωσης και Ηλεκτρονικής Διακυβέρνησης'=>'The Ministry of Interior, Decentralization and Electronic Government (Dissolved)',
        'Εσωτερικών, Δημόσιας Διοίκησης και Αποκέντρωσης '=>'The Ministry of Interior, Public Administration and Decentralization (Dissolved)',
        'Θαλασσίων Υποθέσεων, Νήσων και Αλιείας'=>'The Ministry of Maritime Affairs, Islands and Fisheries (Dissolved)',
        'Μακεδονίας και Θράκης'=>'The Ministry of Macedonia and Thrace (Dissolved)',
        'Μακεδονίας-Θράκης'=>'The Ministry of Macedonia and Thrace (Dissolved)',
        'Μεταναστευτικής Πολιτικής'=>'The Ministry of Immigration Policy (Dissolved)',
        'Μεταφορών και Επικοινωνιών '=>'The Ministry of Transportation and Communication (Dissolved)',
        'Ναυτιλίας και Αιγαίου'=>'The Ministry of Shipping and the Aegean (Dissolved)',
        'Οικονομίας και Ανάπτυξης'=>'The Ministry of Economy and Development (Dissolved)',
        'Οικονομίας και Οικονομικών'=>'The Ministry of Economics and Finance (Dissolved)',
        'Οικονομίας, Ανάπτυξης και Τουρισμού'=>'The Ministry of Economy, Development and Tourism (Dissolved)',
        'Οικονομίας, Ανταγωνιστικότητας και Ναυτιλίας'=>'The Ministry of Economy, Competitiveness and Shipping (Dissolved)',
        'Οικονομίας, Υποδομών, Ναυτιλίας και Τουρισμού'=>'The Ministry of Economy, Infrastructure, Shipping and Tourism (Dissolved)',
        'Οικονομικών'=>'The Ministry of Finance (Dissolved)',
        'Παιδείας  Και  Θρησκευμάτων'=>'The Ministry of Education and Religion (Dissolved)',
        'Παιδείας και Θρησκευμάτων, Πολιτισμού και Αθλητισμού'=>'The Ministry of Education and Religion, Culture and Sports (Dissolved)',
        'Παιδείας, Δια Βίου Μάθησης και Θρησκευμάτων'=>'The Ministry of Education, Lifelong Learning and Religion (Dissolved)',
        'Περιβάλλοντος, Ενέργειας και Κλιματικής Αλλαγής'=>'The Ministry of Environment, Energy and Climate Change (Dissolved)',
        "Περιβάλλοντος, Χωροταξίας και Δημοσίων 'Εργων"=>'The Ministry of Environment, Spatial Planning and Public Works (Dissolved)',
        'Περιφερειακής Ανάπτυξης και Ανταγωνιστικότητας'=>'The Ministry of Regional Development and Competitiveness (Dissolved)',
        'Περιφερειακής Ανάπτυξης, Ανταγωνιστικότητας και Ναυτιλίας'=>'The Ministry of of Regional Development, Competitiveness and Shipping (Dissolved)',
        'Πολιτισμου'=>'The Ministry of Culture (Dissolved)',
        'Πολιτισμού και Τουρισμού'=>'The Ministry of Culture and Tourism (Dissolved)',
        'Πολιτισμού, Παιδείας και Θρησκευμάτων'=>'The Ministry of Culture, Education and Religions (Dissolved)',
        'Προεδρίας της Κυβέρνησης'=>'The Ministry of the Presidency (Dissolved)',
        'Τουριστικής Ανάπτυξης'=>'The Ministry of Tourism Development (Dissolved)',
        'Τύπου και Μέσων Μαζικής Ενημέρωσης'=>'The Ministry of Press and Mass Media (Dissolved)',
        'Υγείας και Κοινωνικής Αλληλεγγύης'=>'The Ministry of Health and Social Solidarity (Dissolved)',
        'Υγείας και Κοινωνικών Ασφαλίσεων'=>'The Ministry of Health and Social Insurance (Dissolved)',
        'Υγείας και Πρόνοιας'=>'The Ministry of Health and Welfare (Dissolved)',
        'Υποδομών, Μεταφορών και Δικτύων'=>'The Ministry of Infrastructure, Transport and Networks (Dissolved)',
        'ΥΠΟΥΡΓΙΚΟ ΣΥΜΒΟΥΛΙΟ'=>'The Council of Ministers (Dissolved)',
        'Υπουργού Επικρατείας με αρμοδιότητες Υπουργού Τύπου και ΜΜΕ'=>'The Ministry of State with the responsibilities of Ministry of Press and Media (Dissolved)',
        'Ψηφιακής Πολιτικής, Τηλεπικοινωνιών και Ενημέρωσης'=>'The Ministry of Digital Policy, Telecommunications and Information (Dissolved)',
        'Εργασίας και Κοινωνικών Υποθέσεων'=>'The Ministry of Labor and Social Affairs (Dissolved)',
        'Επικρατείας'=>'The Ministry of State (Dissolved)',
        'Επικρατείας αρμόδιο για θέματα Καθημερινότητας του Πολίτη'=>'The Ministry of State responsible for Citizen’s Daily Issues (Dissolved)',
        'Επικρατείας για το Συντονισμό Κυβερνητικού Έργου'=>'The Ministry of State for the Coordination of a Government Project (Dissolved)',
        'Επικρατείας και Κυβερνητικός Εκπρόσωπος'=>'A State and Government Representative (Dissolved)',
        'Επικρατείας (Πιερρακάκης)'=>'The Ministry of State (Dissolved)',
        'Αντιπρόεδρος της Κυβέρνησης '=>'The Vice President',
        'Κανονισμός Βουλής'=>'The Parliament',
        'Παρά τω Πρωθυπουργώ (Κοντογεώργης)'=>'The Prime Minister',
        'Παρά τω Πρωθυπουργώ (Μπρατάκος)'=>'The Prime Minister',
        'Παρά τω Πρωθυπουργώ αρμόδιος για θέματα Επικοινωνίας και Ενημέρωσης'=>'The Prime Minister',
        'Πρόταση για Αναθεώρηση Διατάξεων του Συντάγματος'=>'The Parliament',
        'στον Πρωθυπουργό και Κυβερνητικός Εκπρόσωπος'=>'The Prime Minister',
        'Αντιπρόεδρος της Κυβέρνησης'=>'The Vice President',
        'Παρά τω Πρωθυπουργώ αρμόδιος για τον συντονισμό του Κυβ. Έργου'=>'The Prime Minister',
        'στον Υφυπουργό στον Πρωθυπουργό'=>'The Prime Minister',
        'Υφυπουργός στον Πρωθυπουργό (για τη Δημόσια Ραδιοτηλεόραση)'=>'The Prime Minister (Dissolved)',
    );

    //Sets static values
    $saveDate = date('Y-m-d'); $country = '["GR"]';
    $status = 'Valid';
    $publisher = '{"el":"Βουλή των Ελλήνων", "en":"The Hellenic Parliament", "fr":"Le Parlement Hellénique"}';

    //Gets the total number of laws
    $limit = $limit ?? $API_Call($start)['TotalPages'].'<br/>';
    for ($page = $start; $page <= $limit; $page++) {
        //Interprets the data
        $laws = $API_Call($page)['Data'];
        foreach ($laws as $law) {
            //Gets the values
            echo $law['DateVoted'].'<br/>';
            $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law['DateVoted']));
            $ID = $scraper.':'.$law['LawNum'];
            $name = fixQuotes($law['Title'], 'el');
            //Gets the regime
            if (strtotime($enactDate) < strtotime('1 January 1822')) {
                $regime = '{"el":"Η Οθωμανική Αυτοκρατορία", "en":"The Ottoman Empire"}';
            } elseif (strtotime($enactDate) < strtotime('30 August 1832')) {
                $regime = '{"el":"Πρώτη Ελληνική Δημοκρατία", "en":"The First Hellenic Republic"}';
            } elseif (strtotime($enactDate) < strtotime('25 March 1924')) {
                $regime = '{"el":"Βασίλειον τῆς Ἑλλάδος", "en":"The Kingdom of Greece"}';
            } elseif (strtotime($enactDate) < strtotime('10 October 1935')) {
                $regime = '{"el":"Δευτέρα Ελληνική Δημοκρατία", "en":"The Second Hellenic Republic"}';
            } elseif (strtotime($enactDate) < strtotime('21 April 1967')) {
                $regime = '{"el":"Βασίλειον τῆς Ἑλλάδος", "en":"The Kingdom of Greece"}';
            } elseif (strtotime($enactDate) < strtotime('24 July 1974')) {
                $regime = '{"el":"Το καθεστώς των συνταγματαρχών", "en":"The Regime of the Colonels"}';
            } elseif (strtotime($enactDate) <= strtotime('today')) {
                $regime = '{"el":"Τρίτη Ελληνική Δημοκρατία", "en":"The Third Hellenic Republic"}';
            }
            //Gets the rest of the values
            $type = $types[$law['Type']];
            $origin = $origins[$law['Ministry']];
            $source = $law['VotedLaws'][0]['File'];

            //JSONifies the title
            $name = '{"el":"'.$name.'"}';
            $source = $PDF = '{"el":"'.$source.'"}';

            //Creates SQL
            $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `origin`, `type`, `status`, `source`, `PDF`) 
                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$origin."', '".$type."', '".$status."', '".$source."', '".$PDF."')"; echo $SQL2.'<br/>';
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