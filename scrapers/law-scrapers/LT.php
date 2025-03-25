<html><body>
    <?php
        //Settings
        $test = true; $country = 'LT';
        $start = 0;//Where to start from
        $limit = NULL;//Total number of laws desired. Current max is 11224

        //Opens the parser (HTML_DOM) and HTML
        include '../simple_html_dom.php'; //'../' refers to the parent directory
        $html_dom = new simple_html_dom();

        //Connects to the Lawbrary database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sets up querying function
        $HTTP_Call = function($limit=0) use ($start) {
            $ch_search = curl_init();
            curl_setopt_array($ch_search, [
                CURLOPT_URL => 'https://www.e-tar.lt/portal/en/legalActSearch?buildNumber=2adcb4beef07d924c26c7b445990ba5dbda402c4',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query(array(
                    'javax.faces.partial.ajax' => true,
                    'javax.faces.source' => 'contentForm:searchParamPane:searchButton',
                    'javax.faces.partial.execute' => '@all',
                    'javax.faces.partial.render' => 'contentForm:resultsPanel contentForm:searchParamPane',
                    //'contentForm:searchParamPane:j_id_41:0:j_id_42' => 'contentForm:searchParamPane:j_id_41:0:j_id_42',//Selects decrees
                    //'contentForm:searchParamPane:j_id_41:1:j_id_42' => 'contentForm:searchParamPane:j_id_41:1:j_id_42',//Selects laws
                    //'contentForm:searchParamPane:j_id_41:2:j_id_42' => 'contentForm:searchParamPane:j_id_41:2:j_id_42',//Selects resolutions
                    'contentForm:searchParamPane:searchButton' => 'contentForm:searchParamPane:searchButton',
                    'contentForm:resultsTable_pagination' => true,
                    'contentForm:resultsTable_first' => $start,//Where to start from
                    'contentForm:resultsTable_rows' => $limit,//How many documents we want
                    'contentForm:resultsTable_encodeFeature' => true,
                    'contentForm_SUBMIT' => 1,
                    'javax.faces.ViewState' => //{
                        'rO0ABXVyABNbTGphdmEubGFuZy5PYmplY3Q7kM5YnxBzKWwCAAB4cAAAAAJ1cQB+AAAAAAACcHNyABFqYXZhLnV0aWwuSGFzaE1hcAUH2sHDFmDRAwACRgAKbG9hZEZhY3RvckkACXRocmVzaG9sZHhwP0AAAAAAADB3CAAAAEAAAAAidAAsY29udGVudEZvcm06c2VhcmNoUGFyYW1QYW5lOmpfaWRfNTQ6Y2FsZW5kYXJ1cQB+AAAAAAACdXEAfgAAAAAAAXVxAH4AAAAAAARwdXEAfgAAAAAABH5yAC5qYXZheC5mYWNlcy5jb21wb25lbnQuVUlDb21wb25lbnQkUHJvcGVydHlLZXlzAAAAAAAAAAASAAB4cgAOamF2YS5sYW5nLkVudW0AAAAAAAAAABIAAHhwdAAIYmluZGluZ3NzcgAramF2YXguZmFjZXMuY29tcG9uZW50Ll9BdHRhY2hlZFN0YXRlV3JhcHBlckSr5kB900/EAgACTAAGX2NsYXNzdAARTGphdmEvbGFuZy9DbGFzcztMABNfd3JhcHBlZFN0YXRlT2JqZWN0dAASTGphdmEvbGFuZy9PYmplY3Q7eHB2cgAzamF2YXguZmFjZXMuY29tcG9uZW50Ll9EZWx0YVN0YXRlSGVscGVyJEludGVybmFsTWFwhHIXGwejCVsCAAB4cQB+AAN1cQB+AAAAAAACdAAFbGFiZWxzcgA+b3JnLmFwYWNoZS5teWZhY2VzLnZpZXcuZmFjZWxldHMuZWwuTG9jYXRpb25WYWx1ZUV4cHJlc3Npb25VRUwZUz2DuoEIYAwAAHhyADtvcmcuYXBhY2hlLm15ZmFjZXMudmlldy5mYWNlbGV0cy5lbC5Mb2NhdGlvblZhbHVlRXhwcmVzc2lvbrHF44ghqQEwDAAAeHIAGGphdmF4LmVsLlZhbHVlRXhwcmVzc2lvbncKgFvgwP6RAgAAeHIAE2phdmF4LmVsLkV4cHJlc3Npb26jhYpT8lrSPAIAAHhwc3IARW9yZy5hcGFjaGUubXlmYWNlcy52aWV3LmZhY2VsZXRzLmVsLkNvbnRleHRBd2FyZVRhZ1ZhbHVlRXhwcmVzc2lvblVFTKPhD9oDeAHIDAAAeHIAQm9yZy5hcGFjaGUubXlmYWNlcy52aWV3LmZhY2VsZXRzLmVsLkNvbnRleHRBd2FyZVRhZ1ZhbHVlRXhwcmVzc2lvbgAAAAAAAAABDAAAeHEAfgAYc3IAL29yZy5hcGFjaGUud2ViYmVhbnMuZWwyMi5XcmFwcGVkVmFsdWVFeHByZXNzaW9uAAAAAAAAAAECAAFMAA92YWx1ZUV4cHJlc3Npb250ABpMamF2YXgvZWwvVmFsdWVFeHByZXNzaW9uO3hxAH4AGHNyACFvcmcuYXBhY2hlLmVsLlZhbHVlRXhwcmVzc2lvbkltcGwIjSL+h4ituAwAAHhxAH4AGHclABEje2NjLmF0dHJzLmxhYmVsfQAQamF2YS5sYW5nLk9iamVjdHBweHNyABlqYXZheC5mYWNlcy52aWV3LkxvY2F0aW9uAAAAAAAAAAECAANJAAZjb2x1bW5JAARsaW5lTAAEcGF0aHQAEkxqYXZhL2xhbmcvU3RyaW5nO3hwAAAAhAAAABR0ACQvcmVzb3VyY2VzL2NvbXBvc2l0ZXMvY2FsZW5kYXIueGh0bWx3BwAFdmFsdWV4cQB+ACV3BAAAAAF4fnEAfgAKdAANYXR0cmlidXRlc01hcHNxAH4ADnEAfgATdXEAfgAAAAAAAnQAHG9hbS5DT01NT05fUFJPUEVSVElFU19NQVJLRURzcgAOamF2YS5sYW5nLkxvbmc7i+SQzI8j3wIAAUoABXZhbHVleHIAEGphdmEubGFuZy5OdW1iZXKGrJUdC5TgiwIAAHhwAAAAQAIAAEFwcHB0ACxjb250ZW50Rm9ybTpzZWFyY2hQYXJhbVBhbmU6al9pZF80cTpjYWxlbmRhcnVxAH4AAAAAAAJ1cQB+AAAAAAABdXEAfgAAAAAABHB1cQB+AAAAAAAEcQB+AAxzcQB+AA5xAH4AE3VxAH4AAAAAAAJxAH4AFXNxAH4AFnNxAH4AG3NxAH4AHnNxAH4AIXclABEje2NjLmF0dHJzLmxhYmVsfQAQamF2YS5sYW5nLk9iamVjdHBweHEAfgAldwcABXZhbHVleHEAfgAldwQAAAABeHEAfgAnc3EAfgAOcQB+ABN1cQB+AAAAAAACcQB+ACtzcQB+ACwAAABAAgAAQXBwcHQAQ2NvbnRlbnRGb3JtOnNlYXJjaFBhcmFtUGFuZTpwYXJhbUV4UG9zdEFzc2Vzc21lbnREYXRlc0Zyb206Y2FsZW5kYXJ1cQB+AAAA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',//}
                    'contentForm:searchParamPane:paramToggleButtonTop_input' => 'on',
                    'contentForm:searchParamPane:titleSearchOptionSelect' => 'ALL_WORDS',
                    'contentForm:searchParamPane:textSearchOptionSelect' => 'ALL_WORDS',
                    'contentForm:searchParamPane:j_id_3j:calendar_input' => '1990-01-01',//Start date
                    'contentForm:searchParamPane:j_id_3n:calendar_input' => date('Y-m-d'),//End date
                    'contentForm:searchParamPane:j_id_6d:thesaurusSearchOptionSelect' => 'includeRelatedTerms',
                    'contentForm:searchParamPane:paramSortBy_input' => 'aevalidfrom',//Sort by date of acceptance
                    'contentForm:searchParamPane:sortOrderOptionSelect_input' => 'on',//Value 'on' to sort ascending
                    'contentForm:searchParamPane:paramToggleButton_input' => 'on',
                    'contentform:searchParamPane_active' => 0,
                    'contentForm:navigatorPanel_collapsed' => false
                ))
            ]);
            $response = curl_exec($ch_search); curl_close($ch_search);
            return new simple_html_dom($response);
        };

        //Translates the validities
        $validities = array(
            ''=>'Valid',
            'Galioja'=>'Valid',
            'NEGALIOJA'=>'Invalid',
            'Neįsigaliojęs'=>'Out of Force'
        );

        //Processes the data in the table
        $limit = $limit ?? explode('"totalRecords":', explode('}', $HTTP_Call()->find('extension[type="args"]')[0]->plaintext)[0])[1]; echo $limit.'<br/>';
        $laws = $HTTP_Call($limit)->find('update[id="contentForm:resultsPanel"]')[0]->find('table')[0]->find('tbody')[0]->find('tr[data-ri]');
        foreach($laws as $law) {//echo $law;
            $values = array(//Sets up the values for each law
                'Seq. No.' => '',
                'Type' => '',
                'Title' => '',
                'Institutional ID' => '',
                'Enactment Date' => '',
                'Enforcement Date' => '',
                'Validity' => ''
            );

            //Retrieves data from the table
            for ($cell = 0; $cell <= 6; $cell++) {
                $values[array_keys($values)[$cell]] = trim($law->find('td')[$cell+1]->innertext);
            }

            //Fixes the Enforcement Date value
            $date_dom = new simple_html_dom();
            $date_dom->load($values['Enforcement Date']);
            if ($date_dom->find('span.dateColumn', 0)) {
                $values['Enforcement Date'] = $date_dom->find('span.dateColumn', 0)->innertext;
            }

            //Extracts ID and link from title
            $title_dom = new simple_html_dom();//Creates a new DOM tree from the title
            $title_dom->load($values['Title']);
            if (isset($title_dom->find('a')[1])) {
                //Adds value for Hyperref
                $pageID = explode('legalAct/', $title_dom->find('a')[1]->href)[1];
                $values['Type'] = 'Amendment to '.$values['Type'];//Changes the type
                //Adds enforcement date
                $values['Enforcement Date'] = explode('to', explode('Valid consolidated version from ', $title_dom->find('a', 1)->innertext)[1])[0] ?? explode('Valid consolidated version from ', $title_dom->find('a', 1)->innertext)[1];
            } else if (isset($title_dom->find('a')[0])) {
                //Adds value for Hyperref
                $pageID = explode('legalAct/', $title_dom->find('a')[0]->href)[1];
            }
            //Gets the ID, source and title
            $values['Institutional ID'] = trim(explode('Identification No. ', explode('<br />', $values['Title'])[2])[1]);//Adds value for ID
                if (str_contains($values['Institutional ID'], '<div')) {$values['Institutional ID'] = trim(explode('<div', $values['Institutional ID'])[0]);}
            $values['Source'] = 'https://e-tar.lt/portal/lt/legalAct/'.$pageID;
            $values['Title'] = $title_dom->find('a')[0]->innertext;


            //Finalizes dates and ID
            $values['Enactment Date'] = date('Y-m-d', strtotime($values['Enactment Date']));
            $values['Enforcement Date'] = date('Y-m-d', strtotime($values['Enactment Date']));
            if (!$values["Institutional ID"]) {$ID = $country.'-'.$pageID;} else {$ID = $country.'-'.$values['Institutional ID'];}

            //Sets the regime
            $regime = 'The Republic of Lithuania';

            //Gets the validity
            $validity_dom = new simple_html_dom();
            $validity_dom->load($values['Validity']);
            $values['Validity'] = $validities[trim($validity_dom->find('div.ui-tooltip.ui-widget.ui-widget-content.ui-shadow.ui-corner-all')[0]->plaintext ?? $validity_dom->plaintext ?? $values['Validity'])];

            //Makes sure there are no appostophes in the title
            if (str_contains($values['Title'], '"')) {$values['Title'] = str_replace('"', '', $values['Title']);}

            //JSONifies the title and source
            $values['Title'] = '{"lt":"'.$values['Title'].'"}';
            $values['Source'] = '{"lt":"'.$values['Source'].'"}';

            
            //Pushes values to the array of laws
            $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `ID`, `regime`, `name`, `type`, `status`, `source`) 
                VALUES ('".$values['Enactment Date']."', '".$values['Enforcement Date']."', '".$ID.", '".$regime."', '".$values['Title']."', '".$values['Type']."', '".$values['Validity']."', '".$values['Source']."')"; echo $SQL2.'<br/>';
            if (!$test) {$conn->query($SQL2);}
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