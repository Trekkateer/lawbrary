<?php //Somaliland
    //Settings
    $test = false; $scraper = 'SOMALILAND';
    $start = 1;//Which law to start from
    $limit = null;//Total number of laws desired.

    //Opens the parser (HTML_DOM)
    include '../simple_html_dom.php';
    $dom = new simple_html_dom();

    //Opens my library
    include '../skrapateer.php';

    //Suppress warnings only
    error_reporting(E_ALL & ~E_WARNING);

    //Connects to the Law database
    $username="u9vdpg8vw9h2e";  $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    $HTTP_Call = function ($href, $cookie) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $href,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Cookie: lan='.$cookie.';'),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return $response;
    };

    //Sanitizes the titles
    $sanitizeName = [
        '_'=>' ', '-'=>' '
    ];

    //Sets the static variables
    $saveDate = date('Y-m-d'); $country = '["SOMALILAND"]';
    $regime = '{"so":"Jamhuuriyadda Somaliland", "ar":"جمهورية الصوماليلاند", "en":"The Republic of Somaliland"}';
    $publisher = '{"so":"Dawladda Somaliland", "ar":"حكومة الصوماليلاند", "en":"The Government of Somaliland"}';

    //Loops through the languages
    foreach (array('so'=>'d381c96dfd672c98ffcf982811c98afdfb84bbcds%3A2%3A%22so%22%3B'/*, 'ar'=>'a502f8b7649acf5894cc0c93b5ca5715d13dc7fas%3A2%3A%22ar%22%3B', 'en'=>'07f7f786c05a4320e4aaf9fba41169aabef2e545s%3A2%3A%22en%22%3B'*/) as $lang => $cookie) {
        //Loops through the types
        foreach (array('parliament-acts-2' => 'Act') as $typePage => $type) {
            //Gets the limit
            $dom->load($HTTP_Call('https://govsomaliland.org/articles/'.$typePage.'?page=0', $cookie));
            $limit = $limit ?? explode('page=', $dom->find('li.last', 0)->find('a', 0)->href)[1];

            //Loops through the pages
            for ($page = $start; $page <= $limit; $page++) {
                //Processes the data
                $dom->load($HTTP_Call('https://govsomaliland.org/articles/'.$typePage.'?page='.$page, $cookie));
                $laws = $dom->find('div.col-md-12.ministry');
                foreach($laws as $lawNum => $law) {
                    //Gets the source
                    $source = 'https://govsomaliland.org'.$law->find('h1', 0)->find('a', 0)->href;
                    //Gets the rest of the values
                    $law_dom = new simple_html_dom();
                    $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime($law_dom->load($HTTP_Call($source, $cookie))->find('div.col-lg-9', 0)->find('div.singlepost', 0)->find('div.postmeta', 0)->plaintext));
                    $ID = $scraper.':'.($page-1).$lawNum;
                    $name = fixQuotes(strtolower(strtr($law->find('h1', 0)->find('a', 0)->plaintext, $sanitizeName)), $lang);
                    $status = 'Valid';

                    //Creates SQL to check if the law is already stored
                    $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
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
                            $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                        }
                    } else {
                        //JSONifies the name and href
                        $name = '{"'.$lang.'":"'.$name.'"}';
                        $source = '{"'.$lang.'":"'.$source.'"}';

                        //Creates SQL
                        $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `publisher`, `type`, `status`, `source`)
                                VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', '".$publisher."', '".$type."', '".$status."', '".$source."')";
                    }

                    //Makes the query
                    echo $SQL2.'<br/>';
                    if (!$test) {$conn->query($SQL2);}
                }
            }
        }
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq";  $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>