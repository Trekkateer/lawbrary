<html><body>
    <?php
        //Settings
        $test = true; $country = 'CH';
        $start = 0;//Which law to start from
        $step = 1000;//How many laws per page
        $limit = null;//Total number of laws desired.

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

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
        $API_Call = function ($offset) use ($step) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.fedlex.admin.ch/elasticsearch/proxy/_search?index=data',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{"size":'.$step.',"from":'.$offset.',"aggs":{"results_language_oc":{"filter":{"match_all":{}},"aggs":{"http://publications.europa.eu/resource/authority/language/DEU":{"filter":{"bool":{"should":[{"exists":{"field":"deContent"}},{"term":{"facets.language.keyword":"http://publications.europa.eu/resource/authority/language/DEU"}},{"exists":{"field":"facets.title.de"}},{"term":{"included.references.language.keyword":"http://publications.europa.eu/resource/authority/language/DEU"}}],"minimum_should_match":1}}},"http://publications.europa.eu/resource/authority/language/FRA":{"filter":{"bool":{"should":[{"exists":{"field":"frContent"}},{"term":{"facets.language.keyword":"http://publications.europa.eu/resource/authority/language/FRA"}},{"exists":{"field":"facets.title.fr"}},{"term":{"included.references.language.keyword":"http://publications.europa.eu/resource/authority/language/FRA"}}],"minimum_should_match":1}}},"http://publications.europa.eu/resource/authority/language/ITA":{"filter":{"bool":{"should":[{"exists":{"field":"itContent"}},{"term":{"facets.language.keyword":"http://publications.europa.eu/resource/authority/language/ITA"}},{"exists":{"field":"facets.title.it"}},{"term":{"included.references.language.keyword":"http://publications.europa.eu/resource/authority/language/ITA"}}],"minimum_should_match":1}}},"http://publications.europa.eu/resource/authority/language/ROH":{"filter":{"bool":{"should":[{"exists":{"field":"rmContent"}},{"term":{"facets.language.keyword":"http://publications.europa.eu/resource/authority/language/ROH"}},{"exists":{"field":"facets.title.rm"}},{"term":{"included.references.language.keyword":"http://publications.europa.eu/resource/authority/language/ROH"}}],"minimum_should_match":1}}},"http://publications.europa.eu/resource/authority/language/ENG":{"filter":{"bool":{"should":[{"exists":{"field":"enContent"}},{"term":{"facets.language.keyword":"http://publications.europa.eu/resource/authority/language/ENG"}},{"exists":{"field":"facets.title.en"}},{"term":{"included.references.language.keyword":"http://publications.europa.eu/resource/authority/language/ENG"}}],"minimum_should_match":1}}}}},"in_force_title":{"filter":{"exists":{"field":"data.attributes.dateEntryInForce.xsd:date"}},"aggs":{"in_force":{"filter":{"bool":{"minimum_should_match":1,"should":[{"bool":{"must_not":{"term":{"data.references.inForceStatus.keyword":"https://fedlex.data.admin.ch/vocabulary/enforcement-status/1"}},"must":[{"range":{"data.attributes.dateEntryInForce.xsd:date":{"lte":"2024-07-19"}}},{"bool":{"minimum_should_match":1,"should":[{"bool":{"must_not":{"exists":{"field":"data.attributes.dateEndApplicability.xsd:date"}}}},{"range":{"data.attributes.dateEndApplicability.xsd:date":{"gte":"2024-07-19"}}}]}},{"bool":{"minimum_should_match":1,"should":[{"bool":{"must_not":{"exists":{"field":"data.attributes.dateNoLongerInForce.xsd:date"}}}},{"range":{"data.attributes.dateNoLongerInForce.xsd:date":{"gt":"2024-07-19"}}}]}}]}}]}}},"not_in_force":{"filter":{"bool":{"minimum_should_match":1,"should":[{"bool":{"must":[{"exists":{"field":"data.attributes.dateEndApplicability.xsd:date"}},{"bool":{"must":{"range":{"data.attributes.dateEndApplicability.xsd:date":{"lte":"2024-07-18"}}}}}]}},{"bool":{"must":[{"exists":{"field":"data.attributes.dateNoLongerInForce.xsd:date"}},{"bool":{"must":{"range":{"data.attributes.dateNoLongerInForce.xsd:date":{"lte":"2024-07-19"}}}}}]}},{"bool":{"must":[{"exists":{"field":"data.references.inForceStatus.keyword"}},{"bool":{"must":{"term":{"data.references.inForceStatus.keyword":"https://fedlex.data.admin.ch/vocabulary/enforcement-status/1"}}}}]}}]}}},"not_yet_in_force":{"filter":{"bool":{"must_not":{"term":{"data.references.inForceStatus.keyword":"https://fedlex.data.admin.ch/vocabulary/enforcement-status/1"}},"must":{"range":{"data.attributes.dateEntryInForce.xsd:date":{"gte":"2024-07-20"}}}}}}}},"facets.typeDocumentBroader.keyword":{"terms":{"field":"facets.typeDocumentBroader.keyword","size":500}},"data.attributes.typeDocument.rdfs:Resource.keyword":{"terms":{"field":"data.attributes.typeDocument.rdfs:Resource.keyword","size":500}},"data.attributes.processType.rdfs:Resource.keyword":{"terms":{"field":"data.attributes.processType.rdfs:Resource.keyword","size":500}},"facets.basicAct.processType.keyword":{"terms":{"field":"facets.basicAct.processType.keyword","size":500}},"data.references.legalResourcePublicationCompleteness.keyword":{"terms":{"field":"data.references.legalResourcePublicationCompleteness.keyword","size":500}},"facets.publicationCompleteness.keyword":{"terms":{"field":"facets.publicationCompleteness.keyword","size":500}},"data.attributes.legalResourceGenre.rdfs:Resource.keyword":{"terms":{"field":"data.attributes.legalResourceGenre.rdfs:Resource.keyword","size":500}},"facets.explanatoryReportListPerLanguage.en":{"terms":{"field":"facets.explanatoryReportListPerLanguage.en","size":500}},"rights_collections":{"filter":{"bool":{"should":[{"exists":{"field":"facets.theme.themeId"}},{"exists":{"field":"facets.taxonomyId"}}],"minimum_should_match":1}},"aggs":{"internal":{"filter":{"bool":{"must_not":[{"bool":{"filter":{"prefix":{"facets.theme.themeId":"0."}}}},{"bool":{"filter":{"prefix":{"facets.taxonomyId":"0."}}}}]}}},"international":{"filter":{"bool":{"should":[{"bool":{"filter":{"prefix":{"facets.theme.themeId":"0."}}}},{"bool":{"filter":{"prefix":{"facets.taxonomyId":"0."}}}}],"minimum_should_match":1}}}}},"facets.theme.themeUri.keyword":{"terms":{"field":"facets.theme.themeUri.keyword","size":500}},"data.references.responsibilityOf.keyword":{"terms":{"field":"data.references.responsibilityOf.keyword","size":500}},"facets.basicAct.responsibilityOf.keyword":{"terms":{"field":"facets.basicAct.responsibilityOf.keyword","size":500}},"result_count":{"value_count":{"field":"data.uri.keyword"}}},"query":{"bool":{"filter":[{"bool":{"filter":[{"terms":{"data.type.keyword":["Act"]}},{"bool":{"minimum_should_match":1,"should":[{"match":{"included.attributes.memorialName.xsd:string.keyword":"RO"}},{"match":{"included.attributes.memorialName.xsd:string.keyword":"AS"}},{"match":{"included.attributes.memorialName.xsd:string.keyword":"RU"}},{"match":{"included.attributes.memorialName.xsd:string.keyword":"OC"}},{"match":{"included.attributes.memorialName.xsd:string.keyword":"CU"}}]}}]}}],"must":[{"match_all":{}},{"bool":{"should":[[{"bool":{"must_not":{"term":{"data.references.inForceStatus.keyword":"https://fedlex.data.admin.ch/vocabulary/enforcement-status/1"}},"must":[{"range":{"data.attributes.dateEntryInForce.xsd:date":{"lte":"2024-07-19"}}},{"bool":{"minimum_should_match":1,"should":[{"bool":{"must_not":{"exists":{"field":"data.attributes.dateEndApplicability.xsd:date"}}}},{"range":{"data.attributes.dateEndApplicability.xsd:date":{"gte":"2024-07-19"}}}]}},{"bool":{"minimum_should_match":1,"should":[{"bool":{"must_not":{"exists":{"field":"data.attributes.dateNoLongerInForce.xsd:date"}}}},{"range":{"data.attributes.dateNoLongerInForce.xsd:date":{"gt":"2024-07-19"}}}]}}]}}]],"minimum_should_match":1}}],"should":[]}},"sort":{"data.attributes.dateEntryInForce.xsd:date":{"order":"desc"}}}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl); curl_close($curl);
            return json_decode($response, true);
        };

        //Gets the limit
        $limit = $limit ?? $API_Call(0)['aggregations']['result_count']['value'];

        //Loops through the pages
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets values
            foreach ($API_Call($offset)['hits']['hits'] as $law) {
                $enactDate = $lastactDate = date('Y-m-d', strtotime($law['_source']['data']['attributes']['dateDocument']['xsd:date']));
                $enforceDate = date('Y-m-d', strtotime($law['_source']['data']['attributes']['dateEntryInForce']['xsd:date'] ?? $law['_source']['data']['attributes']['dateDocument']['xsd:date']));
                $ID = $country.'-'.str_replace('/', '', explode('/', $law['_source']['data']['uri'])[5].explode('/', $law['_source']['data']['uri'])[6]);
                $regime = 'The Swiss Confederation';
                $name = json_encode($law['_source']['facets']['title'], JSON_UNESCAPED_UNICODE);
                $type = $law['_source']['data']['type'][0];
                $status = 'In Force';//At present, only in force laws are collected
                $source = array();
                    foreach ($law['_source']['data']['references']['isRealizedBy'] as $URI) {$source[end(explode('/', $URI))] = strtr($URI, array('fedlex.data.'=>'fedlex.'));}
                    $source = json_encode($source, JSON_UNESCAPED_UNICODE);
                $PDF = array();
                    foreach ($law['_source']['data']['references']['isRealizedBy'] as $URI) {$PDF[end(explode('/', $URI))] = 'https://fedlex.admin.ch/filestore/'.explode('https://', $URI)[1].'/pdf-a/'.strtr(explode('https://', $URI)[1], array('/'=>'-', '.'=>'-')).'-pdf-a-1.pdf';}
                    $PDF = json_encode($PDF, JSON_UNESCAPED_UNICODE);

                //Makes sure there are no quotes in the name
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `source`, `PDF`)
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$source."', '".$PDF."')";

                //Makes the query
                echo $SQL2.'<br/>';
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