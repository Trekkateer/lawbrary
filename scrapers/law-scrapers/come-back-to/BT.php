<html><body>
    <?php //Getting the ID is impossible and language is very difficult
        //Settings
        $test = true; $scraper = 'BT';

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";  $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Preloop arrays
        $IDs = array(
            'dz'=>array(
                'འབྲུག་གི་གདམ་ཁ་ཅན་གྱི་འཁོན་འདུམ་བཅའ་ཁྲིམས་༢༠༡༣ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་ངན་ལྷད་བཀག་སྡོམ་བཅའ་ཁྲིམས་༢༠༠༦ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་ངན་ལྷད་བཀག་སྡོམ་བཅའ་ཁྲིམས་༢༠༡༡ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་རྩིས་ཞིབ་བཅའ་ཁྲིམས་སྤྱི་ལོ་༢༠༠༦ཅན་མ།'=>array('', ''),

                'འབྲུག་གི་མ་རྩ་རྩ་སྟོང་བཅའ་ཁྲིམས་༡༩༩༩ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་མི་ཁུངས་བཅའ་ཁྲིམས་༡༩༨༥ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་མི་ཁུངས་བཅའ་ཁྲིམས་༡༩༧༧ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་ཤེས་རིག་གྲོང་ཁྱེར་བཅའ་ཁྲིམས་༢༠༡༢ཅན་མ།'=>array('', ''),

                'འབྲུག་གི་བརྡ་དོན་བརྒྱུད་འབྲེལ་དང་བརྡ་བརྒྱུད་བཅའ་ཁྲིམས་༢༠༠༦ཅན་མ།'=>array('', ''),
                'འབྲུག་ཁྲོམ་སྡེའི་བཅའ་ཁྲིམས་༡༩༩༩ཅན་མ།'=>array('', ''),
                'འབྲུག་འགྲེམ་རང་སྐྱོང་ལས་འཛིན་གྱི་བཅའ་ཁྲིམས་༡༩༩༩ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་གནས་ཚད་བཅའ་ཁྲིམས་༢༠༡༠ཅན་མ།'=>array('', ''),

                'འབྲུག་བརྒྱུད་འཕྲིན་གྱི་བཅའ་ཁྲིམས་༡༩༩༩ཅ་ནམ།'=>array('', ''),
                'འབྲུག་གི་སྐྱེ་ལྡན་རིགས་སྣའི་བཅའ་ཁྲིམས་༢༠༠༣ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་སྐྱེ་ལྡན་ཉེན་སྲུང་བཅའ་ཁྲིམས་༢༠༡༥ཅན་མ།'=>array('', ''),
                'མི་རྩིས་ལག་དེབ་༡༩༩༣ཅན་མ།'=>array('', ''),

                'འབྲུག་གི་ཚོགས་འདུ་ཆེན་མོའི་ཚོགས་དཔོན་ཡིག་ཚང་གི་བཅའ་ཁྲིམས་༡༩༩༦ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་བུ་ཚབ་བཅའ་ཁྲིམས་༢༠༡༢ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་ཨ་ལོ་གཅེས་སྐྱོང་དང་ཉེན་སྐྱོབ་བཅའ་ཁྲིམས་༢༠༡༡ཅན་མ།'=>array('', ''),
                'འབྲུག་གི་ཞི་རྩོད་དང་ཉེས་རྩོད་བྱ་བའི་གནད་སྤྱོད་ཀྱི་བཅའ་ཁྲིམས་ (འཕྲི་སྣོན) ༢༠༡༡ཅན་མ།'=>array('', '')
            ),
            'en'=>array(
                'Alternative Dispute Resolution Act 2013'=>array('', 'Alternative Dispute Resolution Act 2013'),
                'Anti-Corruption Act of Bhutan, 2006'=>array('', 'Anti-Corruption Act of Bhutan, 2006'),
                'Anti-Corruption Act of Bhutan, 2011'=>array('', 'Anti-Corruption Act of Bhutan, 2011'),
                'Audit Act of Bhutan, 2006'=>array('', 'Audit Act of Bhutan, 2006'),

                'Anti-Corruption (Amendment) Act of Bhutan, 2022'=>array('', 'Anti-Corruption (Amendment) Act of Bhutan, 2022'),


                'Bankruptcy Act of the Kingdom of Bhutan, 1999'=>array('', 'Bankruptcy Act of the Kingdom of Bhutan, 1999'),
                'Bhutan Citizenship Act, 1985'=>array('', 'Bhutan Citizenship Act, 1985'),
                'Bhutan Citizenship Act, 1977'=>array('', 'Bhutan Citizenship Act, 1977'),
                'Bhutan Education City Act, 2012'=>array('', 'Bhutan Education City Act, 2012'),
                'Bhutan Information Communications and Media Act, 2006'=>array('', 'Bhutan Information Communications and Media Act, 2006'),
                'Bhutan Municipal Act, 1999'=>array('', 'Bhutan Municipal Act, 1999'),
                'Bhutan Postal Corporation Act, 1999'=>array('', 'Bhutan Postal Corporation Act, 1999'),
                'Bhutan Standards Act, 2010'=>array('', 'Bhutan Standards Act, 2010'),
                'Bhutan Telecom Act, 1999'=>array('', 'Bhutan Telecom Act, 1999'),
                'Biodiversity Act of Bhutan, 2003'=>array('', 'Biodiversity Act of Bhutan, 2003'),
                'Biosafety Act of Bhutan, 2015'=>array('', 'Biosafety Act of Bhutan, 2015'),


                'Census Hand Book, 1993'=>array('', 'Census Hand Book, 1993'),
                'Chathrim for the office of the Speaker of the National Assembly of the Kingdom of Bhutan, 1996'=>array('', 'Chathrim for the office of the Speaker of the National Assembly of the Kingdom of Bhutan, 1996'),
                'Child Adoption Act of Bhutan, 2012'=>array('', 'Child Adoption Act of Bhutan, 2012'),
                'Civil and Criminal Procedure Code (Amendment) Act of Bhutan, 2011'=>array('', 'Civil and Criminal Procedure Code (Amendment) Act of Bhutan, 2011'),
                'Civil and Criminal Procedure Code of Bhutan, 2001'=>array('', 'Civil and Criminal Procedure Code of Bhutan, 2001'),
                'Civil and Criminal Procedure Code (Amendment) Act of Bhutan 2021'=>array('', 'Civil and Criminal Procedure Code (Amendment) Act of Bhutan 2021'),
                'Civil Aviation Act of Bhutan'=>array('', 'Civil Aviation Act of Bhutan'),
                'Civil Society Organizations Act of Bhutan, 2007'=>array('', 'Civil Society Organizations Act of Bhutan, 2007'),
                'Civil Service Act of Bhutan, 2010'=>array('', 'Civil Service Act of Bhutan, 2010'),

                'Civil Liability Act of Bhutan, 2023'=>array('', 'Civil Liability Act of Bhutan, 2023'),

                'Civil Service Reform  Act of Bhutan,2022'=>array('', 'Civil Service Reform Act of Bhutan, 2022'),

                'Civil Society  Organizations (Amendment) Act of Bhutan, 2022'=>array('', 'Civil Society Organizations (Amendment) Act of Bhutan, 2022'),
                'Co-operatives Acts of Bhutan, 2001'=>array('', 'Co-operatives Acts of Bhutan, 2001'),
                'Commercial Sale of Goods Act, 2001'=>array('', 'Commercial Sale of Goods Act, 2001'),
                'Companies Act of the Kingdom of Bhutan, 2000'=>array('', 'Companies Act of the Kingdom of Bhutan, 2000'),
                'Constitution of the Kingdom of Bhutan, 2008'=>array('', 'Constitution of the Kingdom of Bhutan, 2008'),
                'Consumer Protection Act of Bhutan, 2012'=>array('', 'Consumer Protection Act of Bhutan, 2012'),
                'Contract Act of Bhutan,2013'=>array('', 'Contract Act of Bhutan, 2013'),
                'Copyright Act of Bhutan, 2001'=>array('', 'Copyright Act of Bhutan, 2001'),


                'Disaster Management Act of Bhutan, 2013'=>array('', 'Disaster Management Act of Bhutan, 2013'),
                'Domestic Violence Prevention Act, 2013'=>array('', 'Domestic Violence Prevention Act, 2013'),
                'Druk Gyalpo Relief Fund Act, 2012'=>array('', 'Druk Gyalpo Relief Fund Act, 2012'),
                'Dzongkhag Yargay Tshogdu Chathrim, 2002'=>array('', 'Dzongkhag Yargay Tshogdu Chathrim, 2002'),


                'Election Act of Bhutan, 2008'=>array('', 'Election Act of Bhutan, 2008'),
                'Electricity Act of Bhutan, 2001'=>array('', 'Electricity Act of Bhutan, 2001'),
                'Enabling Act for Suppression of Terrorism, 1991'=>array('', 'Enabling Act for Suppression of Terrorism, 1991'),
                'Entitlement & Service Conditions Act for the Holders, Members & Commissioners of Constitutional Offices of Bhutan, 2010'=>array('', ''),
                'Entitlement & Service Conditions (Amendment) Act for the Holders, Members & Commissioners of Constitutional Offices of Bhutan, 2015'=>array('', 'Entitlement & Service Conditions (Amendment) Act for the Holders, Members & Commissioners of Constitutional Offices of Bhutan, 2015'),
                'Entitlement and Service Conditions(Amendment)Act for the Holders,Members and Commission of Constitutional Offices of Bhutan 2021'=>array('', 'Entitlement and Service Conditions (Amendment) Act for the Holders, Members and Commission of Constitutional Offices of Bhutan, 2021'),
                'Environmental Assessment Act of Bhutan, 2000'=>array('', 'Environmental Assessment Act of Bhutan, 2000'),
                'Evidence Act of Bhutan, 2005'=>array('', 'Evidence Act of Bhutan, 2005'),
                'Extradition Act, 1991'=>array('', 'Extradition Act, 1991'),


                'Financial Institution Act, 1992'=>array('', 'Financial Institution Act, 1992'),
                'Financial Services Act, 2011'=>array('', 'Financial Services Act, 2011'),
                'Fire Arms and Ammunition Act of Bhutan, 1990'=>array('', 'Fire Arms and Ammunition Act of Bhutan, 1990'),
                'Fiscal Incentives (Amendment) Act of Bhutan 2020'=>array('', 'Fiscal Incentives (Amendment) Act of Bhutan, 2020'),
                'Food Act of Bhutan, 2005'=>array('', 'Food Act of Bhutan, 2005'),
                'Forest and Nature Conservation Act of Bhutan, 1995'=>array('', 'Forest and Nature Conservation Act of Bhutan, 1995'),


                'Geog Yargay Tshogchhung Chathrim, 2002'=>array('', 'Geog Yargay Tshogchhung Chathrim, 2002'),
                

                'Immigration Act of the Kingdom of Bhutan, 2007'=>array('', 'Immigration Act of the Kingdom of Bhutan, 2007'),
                'Income Tax Act of the Kingdom of Bhutan, 2001'=>array('', 'Income Tax Act of the Kingdom of Bhutan, 2001'),
                'Industrial Property Act of Bhutan, 2001'=>array('', 'Industrial Property Act of Bhutan, 2001'),
                'Inheritance Act of Bhutan, 1980'=>array('', 'Inheritance Act of Bhutan, 1980'),


                'Jabmi Act of the Kingdom of Bhutan, 2003'=>array('', 'Jabmi Act of the Kingdom of Bhutan, 2003'),
                'Judicial Service Act of Bhutan, 2007'=>array('', 'Judicial Service Act of Bhutan, 2007'),


                'Kadyon(ka, kha, ga, nga, cha & chha), 1976'=>array('', 'Kadyon (ka, kha, ga, nga, cha & chha), 1976'),


                'Labour and Employment Act of Bhutan, 2007'=>array('', 'Labour and Employment Act of Bhutan, 2007'),
                'Land Act of Bhutan, 2007'=>array('', 'Land Act of Bhutan, 2007'),
                'Land Act of Bhutan, 1979'=>array('', 'Land Act of Bhutan, 1979'),
                'Legal Deposit Act, 1999'=>array('', 'Legal Deposit Act, 1999'),
                'Lhengye Zhungtshog Act of Bhutan, 1999'=>array('', 'Lhengye Zhungtshog Act of Bhutan, 1999'),
                'Livestock Act of Bhutan, 1980'=>array('', 'Livestock Act of Bhutan, 1980'),
                'Livestock Act of Bhutan, 2001'=>array('', 'Livestock Act of Bhutan, 2001'),
                'Loan Act of Bhutan, 1981'=>array('', 'Loan Act of Bhutan, 1981'),
                'Local Government (Amendment) Act of Bhutan, 2014'=>array('', 'The Local Government (Amendment) Act of Bhutan, 2014'),
                'Local Government Act of Bhutan, 2009'=>array('', 'The Local Government Act of Bhutan, 2009'),
                'Local Governments Act of Bhutan, 2007'=>array('', 'Local Governments Act of Bhutan, 2007'),
                'Local Governments Members Entitlement Act of Bhutan, 2015'=>array('', 'Local Governments Members Entitlement Act of Bhutan, 2015'),


                'Marriage (Amendment) Act of Bhutan. 2009'=>array('', 'Marriage (Amendment) Act of Bhutan, 2009'),
                'Mechanism for Vote of Confidence in the Druk Gyalpo, 1999'=>array('', 'Mechanism for Vote of Confidence in the Druk Gyalpo, 1999'),
                'Medical and Health Council Act of the Kingdom of Bhutan, 2002'=>array('', 'Medical and Health Council Act of the Kingdom of Bhutan, 2002'),
                'Medicine Act of Kingdom of Bhutan, 2003'=>array('', 'Medicine Act of Kingdom of Bhutan, 2003'),
                'Mines and Minerals Management Acts of Bhutan, 1995'=>array('', 'Mines and Minerals Management Acts of Bhutan, 1995'),
                'Movable Cultural Property Act of Bhutan, 2005'=>array('', 'Moveable Cultural Property Act of Bhutan, 2005'),
                'Moveable & Immovable Act, 1999'=>array('', 'Moveable & Immovable Act, 1999'),

                'Marriage Act of Bhutan, 1980'=>array('', 'Marriage Act of Bhutan, 1980'),


                'Narcotic Drugs, Psychotropic Substances & Substance Abuse Act of Bhutan, 2005'=>array('', 'Narcotic Drugs, Psychotropic Substances & Substance Abuse Act of Bhutan, 2005'),
                'Narcotic Drugs, Psychotropic Substances & Substance Abuse Act of Bhutan, 2015'=>array('', 'Narcotic Drugs, Psychotropic Substances & Substance Abuse Act of Bhutan, 2015'),

                'Narcotic Drugs, Psychotropic Substances and Substance Abuse-Amendment Act of Bhutan, 2018'=>array('', 'Narcotic Drugs, Psychotropic Substances and Substance Abuse (Amendment) Act of Bhutan, 2018'),
                'National Assembly Act of the Kingdom of Bhutan, 2008'=>array('', 'National Assembly Act of the Kingdom of Bhutan, 2008'),
                'National Assembly (Amendment) Act of the Kingdom of Bhutan, 2014'=>array('', 'National Assembly (Amendment) Act of the Kingdom of Bhutan, 2014'),
                'National Assembly Committees Act of the Kingdom of Bhutan, 2004'=>array('', 'National Assembly Committees Act of the Kingdom of Bhutan, 2004'),
                'National Council Act of the Kingdom of Bhutan, 2008'=>array('', 'National Council Act of the Kingdom of Bhutan, 2008'),
                'National Council (Amendment )Act of the Kingdom of Bhutan, 2014'=>array('', 'National Council (Amendment) Act of the Kingdom of Bhutan, 2014'),
                'National Environment Protection Act of Bhutan, 2007'=>array('', 'National Environment Protection Act of Bhutan, 2007'),
                'National Referendum Act of the Kingdom of Bhutan, 2008'=>array('', 'National Referendum Act of the Kingdom of Bhutan, 2008'),
                'National Security Act of Bhutan, 1992'=>array('', 'National Security Act of Bhutan, 1992'),
                'Nationality Law of Bhutan, 1958'=>array('', 'Nationality Law of Bhutan, 1958'),
                'Negotiable Instruments Act of Kingdom of Bhutan, 2000'=>array('', 'Negotiable Instruments Act of Kingdom of Bhutan, 2000'),
                'Negotiable Instruments (Amendment) Act of Bhutan 2021'=>array('', 'Negotiable Instruments (Amendment) Act of Bhutan, 2021'),

                'National Digital Identity Act of Bhutan, 2023'=>array('', 'National Digital Identity Act of Bhutan, 2023'),


                'Office of the Attorney General Act of Bhutan, 2015'=>array('', 'Office of the Attorney General Act of Bhutan, 2015'),
                'Office of the Attorney General Act of Bhutan, 2016'=>array('', 'Office of the Attorney General Act of Bhutan, 2016'),


                'Parliamentary Entitlements Act of the Kingdom of Bhutan, 2008'=>array('', 'Parliamentary Entitlements Act of the Kingdom of Bhutan, 2008'),
                'Parliamentary Entitlements (Amendment )Act of the Kingdom of Bhutan, 2014'=>array('', 'Parliamentary Entitlements (Amendment) Act of the Kingdom of Bhutan, 2014'),
                
                'Pay Structure Reform Act of Bhutan, 2022'=>array('', 'Pay Structure Reform Act of Bhutan, 2022'),
                'Penal Code (Amendment) Act of Bhutan, 2011'=>array('', 'Penal Code (Amendment) Act of Bhutan, 2011'),
                'Penal Code of Bhutan, 2004'=>array('', 'Penal Code of Bhutan, 2004'),
                'Penal Code (Amendment) Act of Bhutan 2021'=>array('', 'Penal Code (Amendment) Act of Bhutan 2021'),
                'Pesticides Act of Bhutan, 2000'=>array('', 'Pesticides Act of Bhutan, 2000'),
                'Plant Quarantine Act of Bhutan, 1993'=>array('', 'Plant Quarantine Act of Bhutan, 1993'),
                'Prison Act of Bhutan, 1982'=>array('', 'Prison Act of Bhutan, 1982'),
                'Prison Act of Bhutan, 2009'=>array('', 'Prison Act of Bhutan, 2009'),

                'Property Tax Act of Bhutan, 2022'=>array('', 'Property Tax Act of Bhutan, 2022'),
                'Public Election Fund Act of the Kingdom of Bhutan, 2008'=>array('', 'Public Election Fund Act of the Kingdom of Bhutan, 2008'),
                'Public Finance Act of Bhutan, 2007'=>array('', 'Public Finance Act of Bhutan, 2007'),
                'Public Finance (amendment) act of Bhutan, 2012'=>array('', 'Public Finance (Amendment) Act of Bhutan, 2012'),


                'Religious Organizations Act of Bhutan, 2007'=>array('', 'Religious Organizations Act of Bhutan, 2007'),
                'Road Act of the Kingdom of Bhutan, 2004'=>array('', 'Road Act of the Kingdom of Bhutan, 2004'),
                'Road Act of the Kingdom of Bhutan, 2013'=>array('', 'Road Act of the Kingdom of Bhutan, 2013'),
                'Road Safety and Transport Act of Bhutan, 1991'=>array('', 'Road Safety and Transport Act of Bhutan, 1991'),
                'Royal Bhutan Police Act of Bhutan, 1980'=>array('', 'Royal Bhutan Police Act of Bhutan, 1980'),
                'Royal Bhutan Police Act of Bhutan, 2009'=>array('', 'Royal Bhutan Police Act of Bhutan, 2009'),

                'Royal Bhutan Police  (Amendment) Act of Bhutan, 2022'=>array('', 'Royal Bhutan Police (Amendment) Act of Bhutan, 2022'),
                'Royal Monetary Authority Act of Bhutan, 2010'=>array('', 'Royal Monetary Authority Act of Bhutan, 2010'),
                'Royal Monetary Authority of Bhutan Act, 1982'=>array('', 'Royal Monetary Authority of Bhutan Act, 1982'),


                'Sales Tax, Customs and Excise Act of the Kingdom of Bhutan, 2000'=>array('', 'Sales Tax, Customs and Excise Act of the Kingdom of Bhutan, 2000'),
                'Sale Tax, Customs and Excise (Amendment) Act of Bhutan, 2012'=>array('', 'Sales Tax, Customs and Excise (Amendment) Act of Bhutan, 2012'),
                'Seeds Act of Bhutan, 2000'=>array('', 'Seeds Act of Bhutan, 2000'),
                'Speaker Act of the National Assembly of Bhutan, 2004'=>array('', 'Speaker Act of the National Assembly of Bhutan, 2004'),
                'Speaker Act of the National Assembly of Bhutan, 1996'=>array('', 'Speaker Act of the National Assembly of Bhutan, 1996'),
                'Stamp Act of Bhutan, 1968'=>array('', 'Stamp Act of Bhutan, 1968'),


                'Tax Act of Bhutan, 2022'=>array('', 'Tax Act of Bhutan, 2022'),

                'Tenancy Act of the Kingdom of Bhutan, 2004'=>array('', 'Tenancy Act of the Kingdom of Bhutan, 2004 '),
                'Tenancy Act of the Kingdom of Bhutan, 2015'=>array('', 'Tenancy Act of the Kingdom of Bhutan, 2015'),
                'The Companies Act of Bhutan, 2016'=>array('', 'The Companies Act of Bhutan, 2016'),
                'Thrimzhung Chhenmo, 1953'=>array('', 'Thrimzhung Chhenmo, 1953'),
                'Thromde Act of Bhutan, 2007'=>array('', 'Thromde Act of Bhutan, 2007'),
                'The-companies-act-2016'=>array('', 'The Companies Act, 2016'),

                'Tobacco Control Act of Bhutan, 2010'=>array('', 'Tobacco Control Act of Bhutan, 2010'),

                'Tobacco Control (Amendment) Act of Bhutan, 2012'=>array('', 'Tobacco Control (Amendment) Act of Bhutan, 2012'),
                'Tobacco Control (Amendment) Act of Bhutan, 2014'=>array('', 'Tobacco Control (Amendment) Act of Bhutan, 2014'),


                'University of Medical Sciences Act of Bhutan, 2012'=>array('', 'University of Medical Sciences Act of Bhutan, 2012'),


                'Wage Rate, Recruitment Agencies & Workmen’s Compensation Act, 1994'=>array('', 'Wage Rate, Recruitment Agencies & Workmen’s Compensation Act, 1994'),
                'Waste Prevention and Management Act'=>array('', 'Waste Prevention and Management Act, 2009'),
                'Water Act of Bhutan, 2011'=>array('', 'Water Act of Bhutan, 2011'),


                'Zhapto Lemi Chathrim, 1996'=>array('', 'Zhapto Lemi Chathrim, 1996')
            )
        );

        //Gets the HTML for Bhutanese
        $html_dom = file_get_html('https://oag.gov.bt/language/en/acts/');
        //Processes the data in the table
        $laws = $html_dom->find('div.is-layout-constrained.entry-content.wp-block-post-content')[0]->find('a[href^="https://]');
        
        //Gets the HTML for Bhutanese
        /*$html_dom = file_get_html('https://oag.gov.bt/language/en/acts/'); $lang = 'dz';
        //Processes the data in the table
        $lawParagraghs = $html_dom->find('div.is-layout-constrained.entry-content.wp-block-post-content')[0]->find('p > a[name]');
        foreach ($lawParagraghs as $lawParagragh) {
            $laws = explode('<br>', $lawParagragh->innerHTML);
            foreach ($laws as $law) {
                //Turns the law into a DOM
                $law_dom = new simple_html_dom();
                $law_dom->load($law);

                //Gets the values
                $ID = $scraper.'-'.$IDs[$lang][trim(explode('.pdf', explode('/uploads/', $law_dom->find('a')[0]->href)[1])[0])][0];
                $name = trim($law_dom->find('span')[0]->plaintext);
                $regime = 'The Kingdom of Bhutan';
                $type = 'Law'; $status = 'Valid';

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                
                //JSONifies the name and href
                $name = '{"'.$lang.'":"'.$name.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($scraper)."`(`ID`, `name`, `regime`, `type`, `status`)
                        VALUES ('".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."')";

                //Executes the SQL
                echo $SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }*/

        //Gets the HTML for English
        /*$html_dom->load('https://oag.gov.bt/language/en/resources/acts-2/'); $lang = 'en';
        //Processes the data in the table
        $lawParagraghs = $html_dom->find('div.is-layout-constrained.entry-content.wp-block-post-content')[0]->find('p a[name]');
        for ($i = 4; $i <= sizeof($lawParagraghs); $i++) {
            $lawParagragh = $lawParagraghs[$i];
            $laws = explode('<br>', $lawParagragh->innerHTML);
            foreach ($laws as $law) {
                if (str_contains($law, '<a href="')) {
                    //Turns the law into a DOM
                    $law_dom->load($law);

                    //Gets the values
                    $ID = $scraper.'-'.IDs[explode('.pdf', explode('/uploads/', $law_dom->find('a')[0]->href)[1])[0]];
                    $enactDate = end(explode(' ', trim($law_dom->find('a')[0]->plaintext))).'-01-01'; $enforceDate = $enactDate; $lastactDate = $enactDate;
                    $name = trim($law_dom->plaintext, ' (');
                }
            }
        }*/
        

        //Connect to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>