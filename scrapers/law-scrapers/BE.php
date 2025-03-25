<html><body>
    <?php
        //Settings
        $test = true; $scraper = 'BE';
        $start = array("fr"=>1,    "nl"=>1);//What page to start from. 1 is the first
        $limit = array("fr"=>null, "nl"=>null);//Total number of pages desired

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Clears the table(s)
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/>';
        if (!$test) {$conn->query($SQL1);}
        $SQL10 = "SELECT `ID` FROM `dbupm726ysc0bg`.`divisions` WHERE `parent`='".$scraper."'";
        $result10 = $conn2->query($SQL10);
        while ($row10 = $result10->fetch_assoc()) {
            $SQL11 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($row10['ID'])."`"; echo $SQL11.'<br/>';
            if (!$test) {$conn->query($SQL11);}
        } echo '<br/>';

        //Translates the types into many datapoints
        $natures = array(
            'fr' => array(//The values are: [type, LBpage, country, origin, topic] TODO: Add topics based on the origin
                'Accord de Cooperation (National)' => ['id'=>'CAN-', 'tp'=>'National Cooperation Agreement'],
                'Arret Cour Arbitrage'           => ['id'=>'CD-CA-', 'tp'=>'Court Decision', 'og'=>[["nl"=>"Arbitragehof", "fr"=>"Cour d’Arbitrage", "en"=>"The Court of Arbitration"]]],
                'Arret Cour Constitutionnelle'   => ['id'=>'CD-CC-', 'tp'=>'Court Decision', 'og'=>[["nl"=>"Grondwettelijk Hof", "fr"=>"Cour constitutionnelle", "en"=>"The Constitutional Court"]]],
                'Arrete (Bruxelles)'             => ['id'=>'DCN-B-', 'tp'=>'Decision', 'lb'=>'BE-BRU'],
                'Arrete Communaute Francaise'    => ['id'=>'DCN-CF-', 'tp'=>'Decision'],
                'Arrete Communaute Germanophone' => ['id'=>'DCN-CG-', 'tp'=>'Decision'],
                'Arrete Executif Flamand'        => ['id'=>'DCN-EF-', 'tp'=>'Decision', 'lb'=>'BE-VLG'],
                'Arrete Gouvernement Flamand'    => ['id'=>'DCN-GF-', 'tp'=>'Decision', 'lb'=>'BE-VLG'],
                'Arrete-Loi'                     => ['id'=>'DL-', 'tp'=>'Decree-Law'],
                'Arrete Ministeriel'             => ['id'=>'DCN-M-', 'tp'=>'Ministerial Decision'],
                'Arrete Region Wallonne'         => ['id'=>'DCN-RW-', 'tp'=>'Decision', 'lb'=>'BE-WAL'],
                'Arrete du Regent'               => ['id'=>'DCN-RE-', 'tp'=>'Decision', 'og'=>[["nl"=>"Regent", "fr"=>"Regent", "en"=>"The Regent"]]],
                'Arrete Royal'                   => ['id'=>'DCN-R-', 'tp'=>'Royal Decree'],
                'Circulaire Ministerielle'       => ['id'=>'CIR-M-', 'tp'=>'Ministerial Circular'],
                'Code Belge de la Navigation'    => ['id'=>'C-BNAV-', 'tp'=>'Code', 'to'=>[["nl"=>"Navigation", "fr"=>"Navigation", "en"=>"Navigation"]]],
                "Code Bruxellois de l'Amenagement du Territoire" => ['id'=>'C-BTP-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Ruimtelike Ordening", "fr"=>"L’Amenagement du Territoire", "en"=>"Territorial Planning"]]],
                "Code Bruxellois de l'Air, du Climat et de la Maitrise de l'Energie" => ['id'=>'C-BACEM-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Lucht", "fr"=>"Air", "en"=>"Air"], ["nl"=>"Klimaat", "fr"=>"Climat", "en"=>"Climate"], ["nl"=>"Energiebeheersing", "fr"=>"Maitrise de l’Energie", "en"=>"Energy Management"]]],
                'Code Bruxellois du Logement'    => ['id'=>'C-BH-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Code Civil'                     => ['id'=>'C-CIV-', 'tp'=>'Code', 'to'=>[["nl"=>"Burgerlijk recht", "fr"=>"Droit civil", "en"=>"Civil Law"]]],
                'Code Consulaire'                => ['id'=>'C-CON-', 'tp'=>'Code', 'to'=>[["nl"=>"Consulaire wet", "fr"=>"Droit consulaire", "en"=>"Consular Law"]]],
                'Code de Commerce'               => ['id'=>'C-COM-', 'tp'=>'Code', 'to'=>[["nl"=>"Koophandel", "fr"=>"Commerce", "en"=>"Commerce"]]],
                'Code de Droit Economique'       => ['id'=>'C-EL-', 'tp'=>'Code', 'to'=>[["nl"=>"Economisch Recht", "fr"=>"Droit Economique", "en"=>"Economic Law"]]],
                'Code de Droit International Prive' => ['id'=>'C-PIL-', 'tp'=>'Code', 'to'=>[["nl"=>"Internationaal Privaatrecht", "fr"=>"Droit International Prive", "en"=>"Private International Law"]]],
                'Code de la Democratie Locale et de la Decentralisation' => ['id'=>'C-LDD-', 'tp'=>'Code', 'to'=>[["nl"=>"Plaatselijke Democratie en Decentralisatie", "fr"=>"Democratie Locale et de la Decentralisation", "en"=>"Local Democracy and Decentralization"]]],
                'Code de Fonction Publique Wallonne' => ['id'=>'C-WPS-', 'tp'=>'Code', 'to'=>[["nl"=>"Waalse Ambtenarencode", "fr"=>"Fonction Publique Wallonne", "en"=>"The Walloon Public Service"]]],
                'Code de Nationalite Belge'      => ['id'=>'C-BNAT-', 'tp'=>'Code', 'to'=>[["nl"=>"Belgische Nationaliteit", "fr"=>"Nationalite Belge", "en"=>"The Belgian Nationality"]]],
                'Code de la Taxe sur la Valeur Ajoutee' => ['id'=>'C-VAT-', 'tp'=>'Code', 'to'=>[["nl"=>"Belasting over de Toegevoegde Waarde", "fr"=>"Taxes sur la Valeur Ajoutee", "en"=>"Value Added Taxes"]]],
                "Code de l'Enseignment Fondamental et Secondaire" => ['id'=>'C-PSE-', 'tp'=>'Code', 'to'=>[["nl"=>"Basis- en Secundair Onderwijs", "fr"=>"L’Enseignment Fondamental et Secondaire", "en"=>"Primary and Secondary Education"]]],
                "Code de l'Enseignment Secondaire" => ['id'=>'C-SE-', 'tp'=>'Code', 'to'=>[["nl"=>"Secundair Onderwijs", "fr"=>"L’Enseignment Secondaire", "en"=>"Secondary Education"]]],
                "Code de l'Enseignment Superieur" => ['id'=>'C-HE-', 'tp'=>'Code', 'to'=>[["nl"=>"Hoger Onderwijs", "fr"=>"L’Enseignment Superieur", "en"=>"Higher Education"]]],
                'Code de Procedure Penale Militaire' => ['id'=>'C-MCP-', 'tp'=>'Code', 'to'=>[["nl"=>"Militaire strafvordering", "fr"=>"Procedure Penale Militaire", "en"=>"Military Criminal Procedure"]]],
                "Code des Droits d'Enregistrement, d'Hypotheque et de Greffe" => ['id'=>'C-RMRR-', 'tp'=>'Code', 'to'=>[["nl"=>"Registratie-, Hypotheek- en Griffierechten", "fr"=>"Droits d’Enregistrement, d’Hypotheque et de Greffe", "en"=>"Rights of Registration, Mortgage and Registry"]]],
                'Code des Droits de Succession'   => ['id'=>'C-IR-', 'tp'=>'Code', 'to'=>[["nl"=>"Successierechten", "fr"=>"Droits de Succession", "en"=>"Inheritance Rights"]]],
                'Code des Droits de Timbre'       => ['id'=>'C-SD-', 'tp'=>'Code', 'to'=>[["nl"=>"Zegelrechten", "fr"=>"Doits de Timbre", "en"=>"Stamp Duties"]]],
                'Code des Droits et Taxes Divers' => ['id'=>'C-VRT-', 'tp'=>'Code', 'to'=>[["nl"=>"Diverse Rechten en Taksen", "fr"=>"Droits et Taxes Divers", "en"=>"Various Rights and Taxes"]]],
                'Code des Impots sur les Revenus' => ['id'=>'C-IT-', 'tp'=>'Code', 'to'=>[["nl"=>"Inkomstenbelastingen", "fr"=>"Impots sur les Revenus", "en"=>"Income Taxes"]]],
                'Code des Societes'               => ['id'=>'C-S-', 'tp'=>'Code', 'to'=>[["nl"=>"Vennootschappen", "fr"=>"Societes", "en"=>"Companies"]]],
                'Code des Taxes Assimilees aux Impots sur les Revenus' => ['id'=>'C-TSIT-', 'tp'=>'Code', 'to'=>[["nl"=>"Inkomstenbelastingen Gelijkgestelde Belastingen", "fr"=>"Taxes Assimilees aux Impots sur les Revenus", "en"=>"Taxes Similar to Income Taxes"]]],
                'Code des Societes et des Associations' => ['id'=>'C-SA-', 'tp'=>'Code', 'to'=>[["nl"=>"Vennootschappen en Verenigingen", "fr"=>"Societes et des Associations", "en"=>"Companies and Associations"]]],
                'Code des Taxes Assimilees au Timbre' => ['id'=>'C-TSSD-', 'tp'=>'Code', 'to'=>[["nl"=>"Belastingen vergelijkbaar met zegelrechten", "fr"=>"Taxes Assimilees au Timbre", "en"=>"Taxes Similar to Stamp Duties"]]],
                "Code d'Instruction Criminelle"   => ['id'=>'C-CP-', 'tp'=>'Code', 'to'=>[["nl"=>"Strafvordering", "fr"=>"d’Instruction Criminelle", "en"=>"Criminal Procedure"]]],
                'Code du Bien Etre au Travail'    => ['id'=>'C-WBW-', 'tp'=>'Code', 'to'=>[["nl"=>"Welzijn op het werk", "fr"=>"Bien Etre au Travail", "en"=>"Well-being at Work"]]],
                'Code du Developpement Territorial' => ['id'=>'C-TD-', 'tp'=>'Code', 'to'=>[["nl"=>"Ruimtelijke Ontwikkeling", "fr"=>"Developpement Territorial", "en"=>"Territorial Development"]]],
                'Code du Logement'                => ['id'=>'C-H-', 'tp'=>'Code', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Code du Recouvrement Amiable et Force des Creances ...' => ['id'=>'C-AFRC-', 'tp'=>'Code', 'to'=>[["nl"=>"Minnelijke en Gedwongen Invordering van Fiscale en Niet-Fiscale", "fr"=>"Recouvrement Amiable et Force des Creances", "en"=>"Amicable and Forced Recovery of Claims"]]],
                'Code Electoral'                  => ['id'=>'C-E-', 'tp'=>'Code', 'to'=>[["nl"=>"Verkiezingen", "fr"=>"Élections", "en"=>"Elections"]]],
                'Code Electoral Communal Bruxellois' => ['id'=>'C-BME-', 'tp'=>'Code', 'to'=>[["nl"=>"Verkiezingscommunalbruxellois", "fr"=>"Electoral Communal Bruxellois", "en"=>"Brussels Municipal Elections"]]],
                'Code Ferroviaire'                => ['id'=>'C-R-', 'tp'=>'Code', 'to'=>[["nl"=>"Spoorwegen", "fr"=>"Ferroviaire", "en"=>"Railways"]]],
                'Code Flamand de la Fiscalite'    => ['id'=>'C-FT-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Fiscaliteit", "fr"=>"Fiscalite", "en"=>"Taxation"]]],
                "Code Flamand de l'Amenagement du Territoire" => ['id'=>'C-FTP-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Ruimtelike Ordening", "fr"=>"L’Amenagement du Territoire", "en"=>"Territorial Planning"]]],
                "Code Flamand de l'Enseignement Secondaire" => ['id'=>'C-FSE-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Secundair Onderwijs", "fr"=>"L’Enseignment Secondaire", "en"=>"Secondary Education"]]],
                'Code Flamand du Logement'        => ['id'=>'C-FH-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Code Forestier'                  => ['id'=>'C-F-', 'tp'=>'Code', 'to'=>[["nl"=>"Bosbouw", "fr"=>"Forestier", "en"=>"Forestry"]]],
                'Code Judiciaire'                 => ['id'=>'C-J-', 'tp'=>'Code', 'to'=>[["nl"=>"Gerechtelijk", "fr"=>"Code Judiciaire", "en"=>"Judicial Law"]]],
                'Code Penal'                      => ['id'=>'C-P-', 'tp'=>'Code', 'to'=>[["nl"=>"Strafwetboek", "fr"=>"Code Penal", "en"=>"Penal Code"]]],
                'Code Penal Militaire'            => ['id'=>'C-MP-', 'tp'=>'Code', 'to'=>[["nl"=>"Militair Strafwetboek", "fr"=>"Code Penal Militaire", "en"=>"Military Penal Code"]]],
                'Code Penal Social'               => ['id'=>'C-SP-', 'tp'=>'Code', 'to'=>[["nl"=>"Sociaal Strafwetboek", "fr"=>"Code Penal Social", "en"=>"Social Penal Code"]]],
                'Code Procedure Armee de Terre'   => ['id'=>'C-AP-', 'tp'=>'Code', 'to'=>[["nl"=>"Rechtspleging Landmacht", "fr"=>"Procedure Armee de Terre", "en"=>"Army Procedure"]]],
                'Code Rural'                      => ['id'=>'C-RA-', 'tp'=>'Code', 'to'=>[["de"=>"Veld", "fr"=>"Rural", "en"=>"Rural Areas"]]],
                "Code Wallon de l'Action Sociale et de la Sante" => ['id'=>'C-WSAH-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Sociale Actie", "fr"=>"Action Sociale", "en"=>"Social Action"], ["nl"=>"Gezondheid", "fr"=>"Sante", "en"=>"Health"]]],
                "Code Wallon de l'Agriculture"    => ['id'=>'C-WA-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Landbouw", "fr"=>"Agriculture", "en"=>"Agriculture"]]],
                "Code Wallon de l'Amenagement du Territoire..." => ['id'=>'C-WTP-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Ruimtelike Ordening", "fr"=>"L’Amenagement du Territoire", "en"=>"Territorial Planning"]]],
                "Code Wallon de l'Habitation Durable" => ['id'=>'C-WSH-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Duurzame Wonen", "fr"=>"Habitation Durable", "en"=>"Sustainable Housing"]]],
                "Code Wallon de l'Environnement"  => ['id'=>'C-WE-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Leefmilieu", "fr"=>"Environnement", "en"=>"The Environment"]]],
                'Code Wallon du Bien-Etre des Animaux' => ['id'=>'C-WAW-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Dierenwelzijns", "fr"=>"Bien-Etre des Animaux", "en"=>"Animal Welfare"]]],
                'Code Wallon du Developpement Territorial' => ['id'=>'C-WTD-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Ruimtelijke Ontwikkeling", "fr"=>"Developpement Territorial", "en"=>"Territorial Development"]]],
                'Code Wallon du Tourisme'         => ['id'=>'C-WT-', 'tp'=>'Code', 'lb'=>'BE-WAL', 'to'=>[["nl"=>"Toerisme", "fr"=>"Tourisme", "en"=>"Tourism"]]],
                'Constitution 1831'               => ['id'=>'CONST-', 'tp'=>'Constitution'],
                'Constitution 1994'               => ['id'=>'CONST-', 'tp'=>'Constitution'],
                'Convention Collective de Travail' => ['id'=>'CA-L-', 'tp'=>'Collective Agreement', 'to'=>[["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"]]],
                'Decret (Bruxelles)'              => ['id'=>'DCR-B-', 'tp'=>'Decree', 'lb'=>'BE-BRU'],
                'Decret Communaute Francaise'     => ['id'=>'DCR-CFR-', 'tp'=>'Decree'],
                'Decret Communaute Germanophone'  => ['id'=>'DCR-CG-', 'tp'=>'Decree'],
                'Decret Conseil Flamand'          => ['id'=>'DCR-CF-', 'tp'=>'Decree', 'lb'=>'BE-VLG'],
                'Decret Region Wallonne'          => ['id'=>'DCR-RW-', 'tp'=>'Decree', 'lb'=>'BE-WAL'],
                'Directive CEE'                   => ['id'=>'DIR-EEC-', 'tp'=>'Directive', 'co'=>'EEC'],
                'Divers'                          => ['id'=>'MIS-', 'tp'=>'Miscellaneous'],
                'Indice de Prix'                  => ['id'=>'IND-', 'tp'=>'Price Index'],
                'Loi'                             => ['id'=>'L-', 'tp'=>'Law'],
                'Loi Communale'                   => ['id'=>'L-M-', 'tp'=>'Municipal Law'],
                'Loi Detention Preventive'        => ['id'=>'L-PD-', 'tp'=>'Law', 'to'=>[["nl"=>"Voorlopige Hechtenis", "fr"=>"Detention Preventive", "en"=>"Preventive Detention"]]],
                'Loi Provincial'                  => ['id'=>'LP-', 'tp'=>'Provincial Law'],
                'Ordonnance (Bruxelles)'          => ['id'=>'O-B-', 'tp'=>'Ordinance', 'lb'=>'BE-BRU'],
                'Reglement (Bruxelles)'           => ['id'=>'R-B-', 'tp'=>'Regulation', 'lb'=>'BE-BRU'],
                'Reglement CEE'                   => ['id'=>'R-EEC-', 'tp'=>'Regulation', 'co'=>'EEC'],
                "Reglement d'Ordre Interieur"     => ['id'=>'IR-', 'tp'=>'Internal Regulations'],
                'Reglement General pour la Protection du Travail' => ['id'=>'RG-LP', 'tp'=>'General Regulation', 'to'=>[["nl"=>"Arbeidsbescherming", "fr"=>"Protection du Travial", "en"=>"Labor Protection"]]],
                'Traite'                          => ['id'=>'T-', 'tp'=>'Treaty'],
                'Traite CECA'                     => ['id'=>'T-ECSC-', 'tp'=>'Treaty', 'co'=>'ECSC'],
                'Traite EURATOM'                  => ['id'=>'T-EURATOM-', 'tp'=>'Treaty', 'co'=>'EURATOM'],
                'Vlarem'                          => ['id'=>'V-', 'tp'=>'Regulation', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Milieu", "fr"=>"Environnement", "en"=>"The Environment"]]],
            ),
            'nl' => array(//The values are: [type, LBpage, country, origin, topic] TODO: Add topics based on the origin
                'Samenwerkingsakkoord (Nationaal)' => ['id'=>'CAN-', 'tp'=>'National Cooperation Agreement'],
                'Arrest Arbitragehof'             => ['id'=>'CD-CA-', 'tp'=>'Court Decision', 'og'=>[["nl"=>"Arbitragehof", "fr"=>"Cour d’Arbitrage", "en"=>"The Court of Arbitration"]]],
                'Arrest Grondwettelijk Hof'       => ['id'=>'CD-CC-', 'tp'=>'Court Decision', 'og'=>[["nl"=>"Grondwettelijk Hof", "fr"=>"Cour constitutionnelle", "en"=>"The Constitutional Court"]]],
                'Besluit (Brussel)'               => ['id'=>'DCN-B-', 'tp'=>'Decision', 'lb'=>'BE-BRU'],
                'Besluit Franse Gemeenschap'      => ['id'=>'DCN-CF-', 'tp'=>'Decision'],
                'Besluit Duitstalige Gemeenschap' => ['id'=>'DCN-CG-', 'tp'=>'Decision'],
                'Besluit Vlaamse Executieve'      => ['id'=>'DCN-EF-', 'tp'=>'Decision', 'lb'=>'BE-VLG'],
                'Besluit Vlaamse Regering'        => ['id'=>'DCN-GF-', 'tp'=>'Decision', 'lb'=>'BE-VLG'],
                'Besluitwet'                      => ['id'=>'DL-', 'tp'=>'Decree-Law'],
                'Ministerieel Besluit'            => ['id'=>'DCN-M-', 'tp'=>'Ministerial Decision'],
                'Besluit Waalse Gewest'           => ['id'=>'DCN-RW-', 'tp'=>'Decision', 'lb'=>'BE-WAL'],
                'Regentsbesluit'                  => ['id'=>'DCN-RE-', 'tp'=>'Decision', 'og'=>[["nl"=>"Regent", "fr"=>"Regent", "en"=>"The Regent"]]],
                'Koninklijk Besluit'              => ['id'=>'DCN-R-', 'tp'=>'Royal Decree'],
                'Ministeriele Omzendbrief'        => ['id'=>'CIR-M-', 'tp'=>'Ministerial Circular'],
                'Belgisch Scheepvaartwetboek'     => ['id'=>'C-BNAV-', 'tp'=>'Code', 'to'=>[["nl"=>"Navigation", "fr"=>"Navigation", "en"=>"Navigation"]]],
                'Brussels Wetboek van Ruimtelijke Ordening' => ['id'=>'C-BTP-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Ruimtelike Ordening", "fr"=>"L’Amenagement du Territoire", "en"=>"Territorial Planning"]]],
                'Brussels Wetboek van Lucht, Klimaat en Energiebeheersing' => ['id'=>'C-BACEM-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Lucht", "fr"=>"Air", "en"=>"Air"], ["nl"=>"Klimaat", "fr"=>"Climat", "en"=>"Climate"], ["nl"=>"Energiebeheersing", "fr"=>"Maitrise de l’Energie", "en"=>"Energy Management"]]],
                'Brusselse Huisvestingscode'      => ['id'=>'C-BH-', 'tp'=>'Code', 'lb'=>'BE-BRU', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Burgerlijk Wetboek'              => ['id'=>'C-CIV-', 'tp'=>'Code', 'to'=>[["nl"=>"Burgerlijk recht", "fr"=>"Droit civil", "en"=>"Civil Law"]]],
                'Consulair Wetboek'               => ['id'=>'C-CON-', 'tp'=>'Code', 'to'=>[["nl"=>"Consulaire wet", "fr"=>"Droit consulaire", "en"=>"Consular Law"]]],
                'Wetboek van Koophandel'          => ['id'=>'C-COM-', 'tp'=>'Code', 'to'=>[["nl"=>"Koophandel", "fr"=>"Commerce", "en"=>"Commerce"]]],
                'Wetboek van Economisch Recht'    => ['id'=>'C-EL-', 'tp'=>'Code', 'to'=>[["nl"=>"Economisch Recht", "fr"=>"Droit Economique", "en"=>"Economic Law"]]],
                'Wetboek van Internationaal Privaatrecht' => ['id'=>'C-PIL-', 'tp'=>'Code', 'to'=>[["nl"=>"Internationaal Privaatrecht", "fr"=>"Droit International Prive", "en"=>"Private International Law"]]],
                'Wetboek van Plaatselijke Democratie en Decentralisatie' => ['id'=>'C-LDD-', 'tp'=>'Code', 'to'=>[["nl"=>"Plaatselijke Democratie en Decentralisatie", "fr"=>"Democratie Locale et de la Decentralisation", "en"=>"Local Democracy and Decentralization"]]],
                'Waalse Ambtenarencode'           => ['id'=>'C-WPS-', 'tp'=>'Code', 'to'=>[["nl"=>"Waalse Ambtenarencode", "fr"=>"Fonction Publique Wallonne", "en"=>"The Walloon Public Service"]]],
                'Wetboek van de Belgische Nationaliteit' => ['id'=>'C-BNAT-', 'tp'=>'Code', 'to'=>[["nl"=>"Belgische Nationaliteit", "fr"=>"Nationalite Belge", "en"=>"The Belgian Nationality"]]],
                'Wetboek van de Belasting over de Toegevoegde Waarde' => ['id'=>'C-VAT-', 'tp'=>'Code', 'to'=>[["nl"=>"Belasting over de Toegevoegde Waarde", "fr"=>"Taxes sur la Valeur Ajoutee", "en"=>"Value Added Taxes"]]],
                'Wetboek voor het Basis- en Secundair Onderwijs' => ['id'=>'C-PSE-', 'tp'=>'Code', 'to'=>[["nl"=>"Basis- en Secundair Onderwijs", "fr"=>"L’Enseignment Fondamental et Secondaire", "en"=>"Primary and Secondary Education"]]],
                'Codex Secundair Onderwijs'       => ['id'=>'C-SE-', 'tp'=>'Code', 'to'=>[["nl"=>"Secundair Onderwijs", "fr"=>"L’Enseignment Secondaire", "en"=>"Secondary Education"]]],
                'Codex Hoger Onderwijs'           => ['id'=>'C-HE-', 'tp'=>'Code', 'to'=>[["nl"=>"Hoger Onderwijs", "fr"=>"L’Enseignment Superieur", "en"=>"Higher Education"]]],
                'Wetboek der Registratie-, Hypotheek- en Griffierechten' => ['id'=>'C-RMRR-', 'tp'=>'Code', 'to'=>[["nl"=>"Registratie-, Hypotheek- en Griffierechten", "fr"=>"Droits d’Enregistrement, d’Hypotheque et de Greffe", "en"=>"Rights of Registration, Mortgage and Registry"]]],
                'Wetboek der Successierechten'    => ['id'=>'C-IR-', 'tp'=>'Code', 'to'=>[["nl"=>"Successierechten", "fr"=>"Droits de Succession", "en"=>"Inheritance Rights"]]],
                'Wetboek der Zegelrechten'        => ['id'=>'C-SD-', 'tp'=>'Code', 'to'=>[["nl"=>"Zegelrechten", "fr"=>"Doits de Timbre", "en"=>"Stamp Duties"]]],
                'Wetboek Diverse Rechten en Taksen' => ['id'=>'C-VRT-', 'tp'=>'Code', 'to'=>[["nl"=>"Diverse Rechten en Taksen", "fr"=>"Droits et Taxes Divers", "en"=>"Various Rights and Taxes"]]],
                'Wetboek van de Inkomstenbelastingen' => ['id'=>'C-IT-', 'tp'=>'Code', 'to'=>[["nl"=>"Inkomstenbelastingen", "fr"=>"Impots sur les Revenus", "en"=>"Income Taxes"]]],
                'Wetboek met Inkomstenbelastingen Gelijkgestelde Belastingen' => ['id'=>'C-TSIT-', 'tp'=>'Code', 'to'=>[["nl"=>"Inkomstenbelastingen Gelijkgestelde Belastingen", "fr"=>"Taxes Assimilees aux Impots sur les Revenus", "en"=>"Taxes Similar to Income Taxes"]]],
                'Wetboek van Strafvordering'      => ['id'=>'C-CP-', 'tp'=>'Code', 'to'=>[["nl"=>"Strafvordering", "fr"=>"d’Instruction Criminelle", "en"=>"Criminal Procedure"]]],
                'Codex over het Welzijn op het Werk' => ['id'=>'C-WBW-', 'tp'=>'Code', 'to'=>[["nl"=>"Welzijn op het werk", "fr"=>"Bien Etre au Travail", "en"=>"Well-being at Work"]]],
                'Wetboek van Vennootschappen'     => ['id'=>'C-S-', 'tp'=>'Code', 'to'=>[["nl"=>"Vennootschappen", "fr"=>"Societes", "en"=>"Companies"]]],
                'Wetboek van Vennootschappen en Verenigingen' => ['id'=>'C-SA-', 'tp'=>'Code', 'to'=>[["nl"=>"Vennootschappen en Verenigingen", "fr"=>"Societes et des Associations", "en"=>"Companies and Associations"]]],
                'Wetboek van de Belastingen vergelijkbaar met zegelrechten' => ['id'=>'C-TSSD-', 'tp'=>'Code', 'to'=>[["nl"=>"Belastingen vergelijkbaar met zegelrechten", "fr"=>"Taxes Assimilees au Timbre", "en"=>"Taxes Similar to Stamp Duties"]]],
                'Vlaamse Wooncode'                => ['id'=>'C-FH-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Boswetboek'                      => ['id'=>'C-F-', 'tp'=>'Code', 'to'=>[["nl"=>"Bosbouw", "fr"=>"Forestier", "en"=>"Forestry"]]],
                'Gerechtelijk Wetboek'            => ['id'=>'C-J-', 'tp'=>'Code', 'to'=>[["nl"=>"Gerechtelijk", "fr"=>"Code Judiciaire", "en"=>"Judicial Law"]]],
                'Strafwetboek'                    => ['id'=>'C-P-', 'tp'=>'Code', 'to'=>[["nl"=>"Strafwetboek", "fr"=>"Code Penal", "en"=>"Penal Code"]]],
                'Militair Strafwetboek'           => ['id'=>'C-MP-', 'tp'=>'Code', 'to'=>[["nl"=>"Militair Strafwetboek", "fr"=>"Code Penal Militaire", "en"=>"Military Penal Code"]]],
                'Sociaal Strafwetboek'            => ['id'=>'C-SP-', 'tp'=>'Code', 'to'=>[["nl"=>"Sociaal Strafwetboek", "fr"=>"Code Penal Social", "en"=>"Social Penal Code"]]],
                'Wetboek Rechtspleging Landmacht' => ['id'=>'C-AP-', 'tp'=>'Code', 'to'=>[["nl"=>"Rechtspleging Landmacht", "fr"=>"Procedure Armee de Terre", "en"=>"Army Procedure"]]],
                'Codex Ruimtelijke Ontwikkeling'  => ['id'=>'C-TD-', 'tp'=>'Code', 'to'=>[["nl"=>"Ruimtelijke Ontwikkeling", "fr"=>"Developpement Territorial", "en"=>"Territorial Development"]]],
                'Huisvestingscode'                => ['id'=>'C-H-', 'tp'=>'Code', 'to'=>[["nl"=>"Huisvesting", "fr"=>"Logement", "en"=>"Housing"]]],
                'Wetboek van de Minnelijke en Gedwongen Invordering van Fiscale en Niet-Fiscale' => ['id'=>'C-AFRC-', 'tp'=>'Code', 'to'=>[["nl"=>"Minnelijke en Gedwongen Invordering van Fiscale en Niet-Fiscale", "fr"=>"Recouvrement Amiable et Force des Creances", "en"=>"Amicable and Forced Recovery of Claims"]]],
                'Brussels Gemeentelijk Kieswetboek' => ['id'=>'C-BME-', 'tp'=>'Code', 'to'=>[["nl"=>"Verkiezingscommunalbruxellois", "fr"=>"Electoral Communal Bruxellois", "en"=>"Brussels Municipal Elections"]]],
                'Kieswetboek'                     => ['id'=>'C-E-', 'tp'=>'Code', 'to'=>[["nl"=>"Verkiezingen", "fr"=>"Élections", "en"=>"Elections"]]],
                'Spoorcodex'                      => ['id'=>'C-R-', 'tp'=>'Code', 'to'=>[["nl"=>"Spoorwegen", "fr"=>"Ferroviaire", "en"=>"Railways"]]],
                'Veldwetboek'                     => ['id'=>'C-RA-', 'tp'=>'Code', 'to'=>[["nl"=>"Veld", "fr"=>"Rural", "en"=>"Rural Areas"]]],
                'Waalse Landbouwwetboek'          => ['id'=>'C-WA-', 'tp'=>'Code', 'to'=>[["nl"=>"Landbouw", "fr"=>"Agriculture", "en"=>"Agriculture"]]],
                'Waalse Wetboek van Sociale Actie en Gezondheid' => ['id'=>'C-WSAH', 'tp'=>'Code', 'to'=>[["nl"=>"Sociale Actie", "fr"=>"Action Sociale", "en"=>"Social Action"]], [["nl"=>"Gezondheid", "fr"=>"Sante", "en"=>"Health"]]],
                'Waalse Wetboek van Duurzame Wonen' => ['id'=>'C-WSH-', 'tp'=>'Code', 'to'=>[["nl"=>"Duurzame Wonen", "fr"=>"Habitation Durable", "en"=>"Sustainable Housing"]]],
                'Waalse Milieuwetboek'            => ['id'=>'C-WE-', 'tp'=>'Code', 'to'=>[["nl"=>"Leefmilieu", "fr"=>"Environnment", "en"=>"The Environment"]]],
                'Waalse Wetboek van Dierenwelzijn' => ['id'=>'C-WAW-', 'tp'=>'Code', 'to'=>[["nl"=>"Dierenwelzijns", "fr"=>"Bien-Etre des Animaux", "en"=>"Animal Welfare"]]],
                'Waalse Wetboek van Ruimtelijke Ontwikkeling' => ['id'=>'C-WTD-', 'tp'=>'Code', 'to'=>[["nl"=>"Ruimtelijke Ontwikkeling", "fr"=>"Developpement Territorial", "en"=>"Territorial Development"]]],
                'Waalse Toerismewetboek'          => ['id'=>'C-WT-', 'tp'=>'Code', 'to'=>[["nl"=>"Toerisme", "fr"=>"Tourisme", "en"=>"Tourism"]]],
                'Vlaamse Codex Fiscaliteit'       => ['id'=>'C-FT-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Fiscaliteit", "fr"=>"Fiscalite", "en"=>"Taxation"]]],
                'Vlaamse Codex Ruimtelike Ordening' => ['id'=>'C-FTP-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Ruimtelike Ordening", "fr"=>"L’Amenagement du Territoire", "en"=>"Territorial Planning"]]],
                'Vlaamse Codex Secundair Onderwijs' => ['id'=>'C-FSE-', 'tp'=>'Code', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Secundair Onderwijs", "fr"=>"L’Enseignment Secondaire", "en"=>"Secondary Education"]]],
                'Grondwet 1831'                   => ['id'=>'CONST-', 'tp'=>'Constitution'],
                'Grondwet 1994'                   => ['id'=>'CONST-', 'tp'=>'Constitution'],
                'Collectieve Arbeidsovereenkomst' => ['id'=>'CA-L-', 'tp'=>'Collective Agreement', 'to'=>[["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"]]],
                'Decreet (Brussel)'               => ['id'=>'DCR-B-', 'tp'=>'Decree', 'lb'=>'BE-BRU'],
                'Decreet Franse Gemeenschap'      => ['id'=>'DCR-CFR-', 'tp'=>'Decree'],
                'Decreet Duitstalige Gemeenschap' => ['id'=>'DCR-CG-', 'tp'=>'Decree'],
                'Decreet Vlaamse Raad'            => ['id'=>'DCR-CF-', 'tp'=>'Decree', 'lb'=>'BE-VLG'],
                'Decreet Waalse Gewest'           => ['id'=>'DCR-RW-', 'tp'=>'Decree', 'lb'=>'BE-WAL'],
                'EEG-Richtlijn'                   => ['id'=>'DIR-EEC-', 'tp'=>'Directive', 'co'=>'EEC'],
                'Varia'                           => ['id'=>'MIS-', 'tp'=>'Miscellaneous'],
                'Indexcijfer'                     => ['id'=>'IND-', 'tp'=>'Price Index'],
                'Wet'                             => ['id'=>'L-', 'tp'=>'Law'],
                'Gemeentewet'                     => ['id'=>'L-M-', 'tp'=>'Municipal Law'],
                'Wet Voorlopige Hechtenis'        => ['id'=>'L-PD-', 'tp'=>'Law', 'to'=>[["nl"=>"Voorlopige Hechtenis", "fr"=>"Detention Preventive", "en"=>"Preventive Detention"]]],
                'Provinciewet'                    => ['id'=>'LP-', 'tp'=>'Provincial Law'],
                'Ordonnantie (Brussel)'           => ['id'=>'O-B-', 'tp'=>'Ordinance', 'lb'=>'BE-BRU'],
                'Verordening (Brussel)'           => ['id'=>'R-B-', 'tp'=>'Regulation', 'lb'=>'BE-BRU'],
                'EEG-Verordening'                 => ['id'=>'R-EEC-', 'tp'=>'Regulation', 'co'=>'EEC'],
                'Vlarem'                          => ['id'=>'R-FEA', 'tp'=>'Regulation', 'lb'=>'BE-VLG', 'to'=>[["nl"=>"Milieu", "fr"=>"Environnement", "en"=>"The Environment"]]],
                'Huishoudelijk Reglement'         => ['id'=>'IR-', 'tp'=>'Internal Regulations'],
                'Algemmen Reglement Arbeidsbescherming' => ['id'=>'GR-', 'tp'=>'General Regulation', 'to'=>[["nl"=>"Arbeidsbescherming", "fr"=>"Protection du Travial", "en"=>"Labor Protection"]]],
                'Verdrag'                         => ['id'=>'T-', 'tp'=>'Treaty'],
                'EEG-Verdrag'                     => ['id'=>'T-EEC-', 'tp'=>'Treaty', 'co'=>'EEC'],
                'EGA-Verdrag'                     => ['id'=>'T-EURATOM-', 'tp'=>'Treaty', 'co'=>'EURATOM'],
                'EGKS-Verdrag'                    => ['id'=>'T-ECSC-', 'tp'=>'Treaty', 'co'=>'ECSC'],
            )
        );
        //Translates the origins
        $origins = array(
            //French origins
            'Agence Fédérale des Médicaments et des Produits de Sante' => [[["nl"=>"Federaal Agentschap voor Geneesmiddelen en Gezondheidsproducten", "fr"=>"Agence Fédérale des Médicaments et des Produits de Sante", "en"=>"The Federal Agency for Medicines and Health Products"]]],
            'Agence pour le Commerce extérieur' => [[["nl"=>"Agentschap voor Buitenlandse Hande", "fr"=>"Agence pour le Commerce extérieur", "en"=>"The Agency for Foreign Trade"]]],
            'Agence Fédérale de Contrôle nucléaire' => [[["nl"=>"Federaal Agentschap voor Nucleaire Controle", "fr"=>"Agence Fédérale de Contrôle nucléaire", "en"=>"The Federal Agency for Nuclear Control"]]],
            'Agence Fédérale pour la Sécurité de la Chaine Alimentaire' => [[["nl"=>"Federaal Agentschap voor de Veiligheid van de Voedselketen", "fr"=>"Agence Fédérale pour la Sécurité de la Chaine Alimentaire", "en"=>"The Federal Agency for the Safety of the Food Chain"]]],
            'Assemblée Réunie de la Commission Communautaire Commune' => [[["nl"=>"Verenigde Vergadering van de Gemeenschappelijke Gemeenschapscommissie", "fr"=>"Assemblée Réunie de la Commission Communautaire Commune", "en"=>"The Joint Assembly of the Joint Community Commission"]]],
            'Autorité flamande'                    => [[["nl"=>"Vlaamse Overheid", "fr"=>"Autorité flamande", "en"=>"The Flemish Government"]], 'BE-VLG'],
            'Banque nationale de Belgique'         => [[["nl"=>"Nationale Bank van België", "fr"=>"Banque nationale de Belgique", "en"=>"The National Bank of Belgium"]]],
            "Centre fédéral pour l'analyse des flux migratoires, la protection des droits fondamentaux des étrangers et la lutte contre la traite des êtres humains" => [[["nl"=>"Federaal Centrum voor de analyse van de migratiestromen, de bescherming van de grondrechten van de vreemdelingen en de strijd tegen de mensenhandel", "fr"=>"Centre fédéral pour l’analyse des flux migratoires, la protection des droits fondamentaux des étrangers et la lutte contre la traite des êtres humains", "en"=>"The Federal Center for the Analysis of Migration Flows, the Protection of the Fundamental Rights of Foreigners and the Fight against Human Trafficking"]]],
            "Centre interfédéral pour l'égalité des chances et la lutte contre le racisme et les discriminations" => [[["nl"=>"Interfederaal Centrum voor Gelijke Kansen en Bestrijding van Discriminatie en Racisme", "fr"=>"Centre interfédéral pour l’égalité des chances et la lutte contre le racisme et les discriminations", "en"=>"The Interfederal Center for Equal Opportunities and the Fight against Racism and Discrimination"]]],
            'Chambres Fédérales'                   => [[["nl"=>"Federale Kamers", "fr"=>"Chambres Fédérales", "en"=>"The Federal Chambers"]]],
            'Chancellerie et Services généraux'    => [[["nl"=>"Kanselarij en Algemene Diensten", "fr"=>"Chancellerie et Services généraux", "en"=>"The Chancellery and General Services"]]],
            'Chancellerie du Premier Ministre'     => [[["nl"=>"Kanselarij van de Eerste Minister", "fr"=>"Chancellerie du Premier Ministre", "en"=>"The Chancellery of the Prime Minister"]]],
            'Communauté flamande'                  => [[["nl"=>"Vlaamse Gemeenschap", "fr"=>"Communauté flamande", "en"=>"The Flemish Community"]]],
            'Communauté française'                 => [[["nl"=>"Franse Gemeenschap", "fr"=>"Communauté française", "en"=>"The French Community"]]],
            'Communauté germanophone'              => [[["nl"=>"Duitstalige Gemeenschap", "fr"=>"Communauté germanophone", "en"=>"The German-speaking Community"]]],
            'Commission bancaire, financière et des assurances' => [[["nl"=>"Commissie voor het Bank-, Financie- en Assurantiewezen", "fr"=>"Commission bancaire, financière et des assurances", "en"=>"The Commission of Banking, Financial and Insurance"]]],
            'Commission communautaire commune'     => [[["nl"=>"Gemeenschappelijke Gemeenschapscommissie", "fr"=>"Commission communautaire commune", "en"=>"The Joint Community Commission"]]],
            'Commission communautaire flamande'    => [[["nl"=>"Vlaamse Gemeenschapscommissie", "fr"=>"Commission communautaire flamande", "en"=>"The Commission of the Flemish Community"]]],
            'Commission communautaire française'   => [[["nl"=>"Franse Gemeenschapscommissie", "fr"=>"Commission communautaire française", "en"=>"The Commission of the French Community"]]],
            'Commission pour la protection de la vie privée' => [[["nl"=>"Commissie voor de bescherming van de persoonlijke levenssfeer", "fr"=>"Commission pour la protection de la vie privée", "en"=>"The Commission for the Protection of Privacy"]]],
            "Commission de Régulation de l'Electricité et du Gaz" => [[["nl"=>"Commissie voor de Regulering van de Elektriciteit en het Gas", "fr"=>"Commission de Régulation de l’Electricité et du Gaz", "en"=>"The Commission for the Regulation of Electricity and Gas"]]],
            "Corps interfédéral de l'inspection des finances" => [[["nl"=>"Het interfederaal korps voor de inspectie van financiën", "fr"=>"Corps interfédéral de l’inspection des finances", "en"=>"The Interfederal Corps for the Inspection of Finances"]]],
            'COUR D ARBITRAGE'                     => [[["nl"=>"Arbitragehof", "fr"=>"Cour d’Arbitrage", "en"=>"The Court of Arbitration"]]],
            "COUR  D'ARBITRAGE"                    => [[["nl"=>"Arbitragehof", "fr"=>"Cour d’Arbitrage", "en"=>"The Court of Arbitration"]]],
            'Cour des Comptes'                     => [[["nl"=>"Rekenhof", "fr"=>"Cour des Comptes", "en"=>"The Court of Auditors"]]],
            'Cour constitutionnelle'               => [[["nl"=>"Grondwettelijk Hof", "fr"=>"Cour constitutionnelle", "en"=>"The Constitutional Court"]]],
            'Institut belge des services Postaux et des Télécommunications' => [[["nl"=>"Belgisch Instituut voor Postdiensten en Telecommunicatie", "fr"=>"Institut belge des services Postaux et des Télécommunications", "en"=>"The Belgian Institute for Postal Services and Telecommunications"]]],
            "Institut national d'assurance maladie-invalidité" => [[["nl"=>"Rijksinstituut voor Ziekte en Invaliditeitsverzekering", "fr"=>"Institut national d’assurance maladie-invalidité", "en"=>"The National Institute for Health and Disability Insurance"]]],
            "Institut pour l'égalité des femmes et des hommes" => [[["nl"=>"Instituut voor de gelijkheid van vrouwen en mannen", "fr"=>"Institut pour l’égalité des femmes et des hommes", "en"=>"The Institute for the Equality of Women and Men"]]],
            'Institut Professionnel des Agents Immobiliers' => [[["nl"=>"Beroepsinstituut van vastgoedmakelaars", "fr"=>"Institut Professionnel des Agents Immobiliers", "en"=>"The Professional Institute of Real Estate Agents"]]],
            'Institut Scientifique de Santé Publique' => [[["nl"=>"Wetenschappelijk Instituut Volksgezondheid", "fr"=>"Institut Scientifique de Santé Publique", "en"=>"The Scientific Institute of Public Health"]]],
            'Office national des pensions'          => [[["nl"=>"Rijksdienst voor Pensioenen", "fr"=>"Office national des pensions", "en"=>"The National Pensions Office"]]],
            'Office national des vacances annuelles' => [[["nl"=>"Rijksdienst voor Jaarlijkse Vakantie", "fr"=>"Office national des vacances annuelles", "en"=>"The National Office for Annual Holidays"]]],
            'Ordre des Barreaux flamands'          => [[["nl"=>"Orde van Vlaamse Balies", "fr"=>"Ordre des Barreaux flamands", "en"=>"The Order of the Flemish Bars"]], 'BE-VLG'],
            'Ordre des Barreaux francophones et germanophone' => [[["nl"=>"Orde van Franstalige en Duitstalige Balies", "fr"=>"Ordre des Barreaux francophones et germanophone", "en"=>"The Order of the French-speaking and German-speaking Bars"]]],
            'Premier Ministre'                     => [[["nl"=>"Eerste Minister", "fr"=>"Premier Ministre", "en"=>"The Prime Minister"]]],
            'Régie des Bâtiments'                  => [[["nl"=>"Regie der Gebouwen", "fr"=>"Régie des Bâtiments", "en"=>"The Buildings Agency"]]],
            'Région de Bruxelles-Capitale'         => [[["nl"=>"Brussels Hoofdstedelijk Gewest", "fr"=>"Région de Bruxelles-Capitale", "en"=>"The Brussels-Capital Region"]], 'BE-BRU'],
            'Région Bruxelloise'                   => [[["nl"=>"Brussels Gewest", "fr"=>"Région Bruxelloise", "en"=>"The Region of Brussels"]], 'BE-BRU'],
            'Région Wallonne'                      => [[["nl"=>"Waalse Gewest", "fr"=>"Région Wallonne", "en"=>"The Walloon Region"]], 'BE-WAL'],
            'REGION WALLONNE'                      => [[["nl"=>"Waalse Gewest", "fr"=>"Région Wallonne", "en"=>"The Walloon Region"]], 'BE-WAL'],
            'Service public régional de Bruxelles' => [[["nl"=>"Gewestelijke Overheidsdienst Brussel", "fr"=>"Service public régional de Bruxelles", "en"=>"The Public Service of the Region of Brussels"]]],
            'Service public de Wallonie'           => [[["nl"=>"Waalse Overheidsdienst", "fr"=>"Service public de Wallonie", "en"=>"The Public Service of Wallonia"]]],

            //Dutch origins
            'Agentschap voor Buitenlandse Handel' => [[["nl"=>"Agentschap voor Buitenlandse Handel", "fr"=>"Agence pour le Commerce extérieur", "en"=>"The Agency for Foreign Trade"]]],
            'Federaal Agentschap voor Nucleaire Controle' => [[["nl"=>"Federaal Agentschap voor Nucleaire Controle", "fr"=>"Agence Fédérale de Contrôle nucléaire", "en"=>"The Federal Agency for Nuclear Control"]]],
            'Federaal Agentschap voor de Veiligheid van de Voedselketen' => [[["nl"=>"Federaal Agentschap voor de Veiligheid van de Voedselketen", "fr"=>"Agence Fédérale pour la Sécurité de la Chaine Alimentaire", "en"=>"The Federal Agency for the Safety of the Food Chain"]]],
            'Federaal Agentschap voor Geneesmiddelen en Gezondheidsproducten' => [[["nl"=>"Federaal Agentschap voor Geneesmiddelen en Gezondheidsproducten", "fr"=>"Agence Fédérale des Médicaments et des Produits de Sante", "en"=>"The Federal Agency for Medicines and Health Products"]]],
            'Nationale Bank van België'            => [[["nl"=>"Nationale Bank van België", "fr"=>"Banque nationale de Belgique", "en"=>"The National Bank of Belgium"]]],
            'Arbitragehof'                         => [[["nl"=>"Arbitragehof", "fr"=>"Cour d’Arbitrage", "en"=>"The Court of Arbitration"]]],
            'Grondwettelijk Hof'                   => [[["nl"=>"Grondwettelijk Hof", "fr"=>"Cour constitutionnelle", "en"=>"The Constitutional Court"]]],
            'Rekenhof'                             => [[["nl"=>"Rekenhof", "fr"=>"Cour des Comptes", "en"=>"The Court of Auditors"]]],
            'INTERFEDERAAL CENTRUM VOOR GELIJKE KANSEN EN BESTRIJDING VAN DISCRIMINATIE EN RACISME' => [[["nl"=>"Interfederaal Centrum voor Gelijke Kansen en Bestrijding van Discriminatie en Racisme", "fr"=>"Centre interfédéral pour l’égalité des chances et la lutte contre le racisme et les discriminations", "en"=>"The Interfederal Center for Equal Opportunities and the Fight against Racism and Discrimination"]]],
            'Federaal Centrum voor de analyse van de migratiestromen, de bescherming van de grondrechten van de vreemdelingen en de strijd tegen de mensenhandel' => [[["nl"=>"Federaal Centrum voor de analyse van de migratiestromen, de bescherming van de grondrechten van de vreemdelingen en de strijd tegen de mensenhandel", "fr"=>"Centre fédéral pour l’analyse des flux migratoires, la protection des droits fondamentaux des étrangers et la lutte contre la traite des êtres humains", "en"=>"The Federal Center for the Analysis of Migration Flows, the Protection of the Fundamental Rights of Foreigners and the Fight against Human Trafficking"]]],
            'Federale Kamers'                      => [[["nl"=>"Federale Kamers", "fr"=>"Chambres Fédérales", "en"=>"The Federal Chambers"]]],
            'Belgisch Instituut voor Postdiensten en Telecommunicatie' => [[["nl"=>"Belgisch Instituut voor Postdiensten en Telecommunicatie", "fr"=>"Institut belge des services Postaux et des Télécommunications", "en"=>"The Belgian Institute for Postal Services and Telecommunications"]]],
            'Beroepsinstituut van vastgoedmakelaars' => [[["nl"=>"Beroepsinstituut van vastgoedmakelaars", "fr"=>"Institut Professionnel des Agents Immobiliers", "en"=>"The Professional Institute of Real Estate Agents"]]],
            'Wetenschappelijk Instituut Volksgezondheid' => [[["nl"=>"Wetenschappelijk Instituut Volksgezondheid", "fr"=>"Institut Scientifique de Santé Publique", "en"=>"The Scientific Institute of Public Health"]]],
            'Regie der Gebouwen'                   => [[["nl"=>"Regie der Gebouwen", "fr"=>"Régie des Bâtiments", "en"=>"The Buildings Agency"]]],
            'Brussels Hoofdstedelijk Gewest'       => [[["nl"=>"Brussels Hoofdstedelijk Gewest", "fr"=>"Région de Bruxelles-Capitale", "en"=>"The Brussels-Capital Region"]], 'BE-BRU'],
            'Brusselse Gewest'                     => [[["nl"=>"Brussels Gewest", "fr"=>"Région Bruxelloise", "en"=>"The Region of Brussels"]], 'BE-BRU'],
            'Waalse Gewest'                        => [[["nl"=>"Waalse Gewest", "fr"=>"Région Wallonne", "en"=>"The Walloon Region"]], 'BE-WAL'],
            'Vlaamse Overheid'                     => [[["nl"=>"Vlaamse Overheid", "fr"=>"Autorité flamande", "en"=>"The Flemish Government"]], 'BE-VLG'],
            'Vlaamse Gemeenschap'                  => [[["nl"=>"Vlaamse Gemeenschap", "fr"=>"Communauté flamande", "en"=>"The Flemish Community"]]],
            'Commissie voor het Bank-, Financie- en Assurantiewezen' => [[["nl"=>"Commissie voor het Bank-, Financie- en Assurantiewezen", "fr"=>"Commission bancaire, financière et des assurances", "en"=>"The Commission of Banking, Financial and Insurance"]]],
            'Commissie voor de bescherming van de persoonlijke levenssfeer' => [[["nl"=>"Commissie voor de bescherming van de persoonlijke levenssfeer", "fr"=>"Commission pour la protection de la vie privée", "en"=>"The Commission for the Protection of Privacy"]]],
            'Commissie voor de Regulering van de Elektriciteit en het Gas' => [[["nl"=>"Commissie voor de Regulering van de Elektriciteit en het Gas", "fr"=>"Commission pour la protection de la vie privée", "en"=>"The Commission for the Regulation of Electricity and Gas"]]],
            'Vlaamse Gemeenschapscommissie'        => [[["nl"=>"Vlaamse Gemeenschapscommissie", "fr"=>"Commission communautaire flamande", "en"=>"The Commission of the Flemish Community"]]],
            'Franse Gemeenschap'                   => [[["nl"=>"Franse Gemeenschap", "fr"=>"Communauté française", "en"=>"The French Community"]]],
            'Franse Gemeenschapscommissie'         => [[["nl"=>"Franse Gemeenschapscommissie", "fr"=>"Commission communautaire française", "en"=>"The Commission of the French Community"]]],
            'Rijksdienst voor Jaarlijkse Vakantie' => [[["nl"=>"Rijksdienst voor Jaarlijkse Vakantie", "fr"=>"Office national des vacances annuelles", "en"=>"The National Office for Annual Holidays"]]],
            'Rijksdienst voor Pensioenen'          => [[["nl"=>"Rijksdienst voor Pensioenen", "fr"=>"Office national des pensions", "en"=>"The National Pensions Office"]]],
            'Rijksinstituut voor Ziekte en Invaliditeitsverzekering' => [[["nl"=>"Rijksinstituut voor Ziekte en Invaliditeitsverzekering", "fr"=>"Institut national d’assurance maladie-invalidité", "en"=>"The National Institute for Health and Disability Insurance"]]],
            'Gemeenschappelijke Gemeenschapscommissie' => [[["nl"=>"Gemeenschappelijke Gemeenschapscommissie", "fr"=>"Commission communautaire commune", "en"=>"The Joint Community Commission"]]],
            'Duitstalige Gemeenschap'              => [[["nl"=>"Duitstalige Gemeenschap", "fr"=>"Communauté germanophone", "en"=>"The German-speaking Community"]]],
            'Orde van Vlaamse Balies'              => [[["nl"=>"Orde van Vlaamse Balies", "fr"=>"Ordre des Barreaux flamands", "en"=>"The Order of the Flemish Bars"]], 'BE-VLG'],
            'Orde van Franstalige en Duitstalige Balies' => [[["nl"=>"Orde van Franstalige en Duitstalige Balies", "fr"=>"Ordre des Barreaux francophones et germanophone", "en"=>"The Order of the French-speaking and German-speaking Bars"]]],
            'Kanselarij en Algemene Diensten'      => [[["nl"=>"Kanselarij en Algemene Diensten", "fr"=>"Chancellerie et Services généraux", "en"=>"The Chancellery and General Services"]]],
            'Kanselarij van de Eerste Minister'    => [[["nl"=>"Kanselarij van de Eerste Minister", "fr"=>"Chancellerie du Premier Ministre", "en"=>"The Chancellery of the Prime Minister"]]],
            'Instituut voor de gelijkheid van vrouwen en mannen' => [[["nl"=>"Instituut voor de gelijkheid van vrouwen en mannen", "fr"=>"Institut pour l’égalité des femmes et des hommes", "en"=>"The Institute for the Equality of Women and Men"]]],
            'Eerste Minister'                      => [[["nl"=>"Eerste Minister", "fr"=>"Premier Ministre", "en"=>"The Prime Minister"]]],
            'Verenigde Vergadering van de Gemeenschappelijke Gemeenschapscommissie' => [[["nl"=>"Verenigde Vergadering van de Gemeenschappelijke Gemeenschapscommissie", "fr"=>"Assemblée Réunie de la Commission Communautaire Commune", "en"=>"The Joint Assembly of the Joint Community Commission"]]],
            'Waalse Overheidsdienst'               => [[["nl"=>"Waalse Overheidsdienst", "fr"=>"Service public de Wallonie", "en"=>"The Public Service of Wallonia"]]],
            'Gewestelijke Overheidsdienst Brussel' => [[["nl"=>"Gewestelijke Overheidsdienst Brussel", "fr"=>"Service public régional de Bruxelles", "en"=>"The Public Service of the Region of Brussels"]]],
            'Beroepsinstituut van vastgoedmakelaars' => [[["nl"=>"Beroepsinstituut van vastgoedmakelaars", "fr"=>"Institut Professionnel des Agents Immobiliers", "en"=>"The Professional Institute of Real Estate Agents"]]],
        );
        $topics = array(
            //French topics
            'Affaires économiques'                       => [["nl"=>"Economische Zaken", "fr"=>"Affaires économiques", "en"=>"Economic Affairs"]],
            'Affaires étrangères'                        => [["nl"=>"Buitenlandse Zaken", "fr"=>"Affaires étrangères", "en"=>"Foreign Affairs"]],
            'Affaires sociales'                          => [["nl"=>"Sociale Zaken", "fr"=>"Affaires sociales", "en"=>"Social Affairs"]],
            'Affaires étrangères, Commerce extérieur et Coopération au développement' => [["nl"=>"Buitenlandse Zaken", "fr"=>"Affaires étrangères", "en"=>"Foreign Affairs"], ["nl"=>"Buitenlandse Handel", "fr"=>"Commerce extérieur", "en"=>"Foreign Trade"], ["nl"=>"Ontwikkelingssamenwerking", "fr"=>"Coopération au développement", "en"=>"Developmental Cooperation"]],
            'Agriculture'                                => [["nl"=>"Landbouw", "fr"=>"Agriculture", "en"=>"Agriculture"]],
            'Budget et Contrôle de la gestion'           => [["nl"=>"Begroting", "fr"=>"Budget", "en"=>"Budget"], ["nl"=>"Beheerscontrole", "fr"=>"Contrôle de la gestion", "en"=>"Management Control"]],
            'Classes Moyennes'                           => [["nl"=>"Middenstand", "fr"=>"Classes Moyennes", "en"=>"The Middle Classes"]],
            'Commerce extérieur'                         => [["nl"=>"Buitenlandse Handel", "fr"=>"Commerce extérieur", "en"=>"Foreign Trade"]],
            'Communications'                             => [["nl"=>"Communicatie", "fr"=>"Communications", "en"=>"Communications"]],
            'Coopération au Développement'               => [["nl"=>"Ontwikkelingssamenwerking", "fr"=>"Coopération au Développement", "en"=>"Developmental Cooperation"]],
            'Coopération internationale'                 => [["nl"=>"Internationale samenwerking", "fr"=>"Coopération internationale", "en"=>"International Cooperation"]],
            'Défense Nationale'                          => [["nl"=>"Landsverdediging", "fr"=>"Défense Nationale", "en"=>"National Defense"]],
            'Développement Durable'                      => [["nl"=>"Duurzame Ontwikkeling", "fr"=>"Développement Durable", "en"=>"Sustainable Development"]],
            'Economie, PME, Classes moyennes et Energie' => [["nl"=>"Economie", "fr"=>"Economie", "en"=>"Economy"], ["nl"=>"KMO", "fr"=>"PME", "en"=>"SME"], ["nl"=>"Middenstand", "fr"=>"Classes Moyennes", "en"=>"The Middle Classes"], ["nl"=>"Energie", "fr"=>"Energie", "en"=>"Energy"]],
            'Education nationale'                        => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"]],
            'Education nationale et Culture française'   => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"], ["nl"=>"Franse Cultuur", "fr"=>"Culture française", "en"=>"French Culture"]],
            'Education nationale et Culture néerlandaise' => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"], ["nl"=>"Nederlandse Cultuur", "fr"=>"Culture néerlandaise", "en"=>"Dutch Culture"]],
            'Emploi et Travail'                          => [["nl"=>"Werkgelegenheid", "fr"=>"Emploi", "en"=>"Employment"], ["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"]],
            'Emploi, Travail et Concertation sociale'    => [["nl"=>"Werkgelegenheid", "fr"=>"Emploi", "en"=>"Employment"], ["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"], ["nl"=>"Sociaal Overleg", "fr"=>"Concertation sociale", "en"=>"Social Dialogue"]],
            'Enseignement'                               => [["nl"=>"Onderwijs", "fr"=>"Enseignement", "en"=>"Education"]],
            'Finances'                                   => [["nl"=>"Financiën", "fr"=>"Finances", "en"=>"Finances"]],
            'Fonction publique'                          => [["nl"=>"Openbaar Ambt", "fr"=>"Fonction publique", "en"=>"Public Service"]],
            'Intégration Sociale, Lutte Contre la Pauvreté et Economie Sociale' => [["nl"=>"Sociale integratie", "fr"=>"Intégration Sociale", "en"=>"Social Integration"], ["nl"=>"Armoedebestrijding", "fr"=>"Lutte Contre la Pauvreté", "en"=>"Poverty Reduction"], ["nl"=>"Sociale economie", "fr"=>"Economie Sociale", "en"=>"The Social Economy"]],
            'Intérieur'                                  => [["nl"=>"Binnenlandse Zaken", "fr"=>"Intérieur", "en"=>"Interior"]],
            'Justice'                                    => [["nl"=>"Justitie", "fr"=>"Justice", "en"=>"Justice"]],
            'Mobilité et Transports'                     => [["nl"=>"Mobiliteit", "fr"=>"Mobilité", "en"=>"Mobility"], ["nl"=>"Vervoer", "fr"=>"Transports", "en"=>"Transportation"]],
            'MOBILITE ET TRANSPORT'                      => [["nl"=>"Mobiliteit", "fr"=>"Mobilité", "en"=>"Mobility"], ["nl"=>"Vervoer", "fr"=>"Transports", "en"=>"Transportation"]],
            'Pensions'                                   => [["nl"=>"Pensioenen", "fr"=>"Pensions", "en"=>"Pensions"]],
            'Personnel et Organisation'                  => [["nl"=>"Personeel", "fr"=>"Personnel", "en"=>"Personnel"], ["nl"=>"Organisatie", "fr"=>"Organisation", "en"=>"Organization"]],
            'Politique Scientifique'                     => [["nl"=>"Wetenschapsbeleid", "fr"=>"Politique Scientifique", "en"=>"Scientific Policy"]],
            'Prévoyance Sociale'                         => [["nl"=>"Sociale Voorzorg", "fr"=>"Prévoyance Sociale", "en"=>"Social Security"]],
            'Protection des consommateurs'               => [["nl"=>"Consumentenbescherming", "fr"=>"Protection des consommateurs", "en"=>"Consumer Protection"]],
            'Santé Publique'                             => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"]],
            'Santé Publique et Environnement'            => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Leefmilieu", "fr"=>"Environnement", "en"=>"The Environment"]],
            'Santé Publique et Famille'                  => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Gezin", "fr"=>"Famille", "en"=>"Family"]],
            'Sante Publique, Sécurité de la Chaine Alimentaire et Environnement' => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Veiligheid van de Voedselketen", "fr"=>"Sécurité de la Chaine Alimentaire", "en"=>"Food Chain Security"], ["nl"=>"Leefmilieu", "fr"=>"Environnement", "en"=>"The Environment"]],
            'Sécurité sociale'                           => [["nl"=>"Sociale Zekerheid", "fr"=>"Sécurité sociale", "en"=>"Social Security"]],
            'Stratégie et Appui'                         => [["nl"=>"Beleid", "fr"=>"Stratégie", "en"=>"Strategy"], ["nl"=>"Ondersteuning", "fr"=>"Appui", "en"=>"Support"]],
            "Technologie de l'information et de la communication" => [["nl"=>"Informatie- en Communicatietechnologie", "fr"=>"Technologie de l’information et de la communication", "en"=>"Information and Communication Technology"]],
            'Télécommunications'                         => [["nl"=>"Telecommunicatie", "fr"=>"Télécommunications", "en"=>"Telecommunications"]],
            'Travaux publics'                            => [["nl"=>"Openbare Werken", "fr"=>"Travaux publics", "en"=>"Public Works"]],
            "Structure de Coordination de l'information patrimoniale" => [["nl"=>"Coördinatiestructuur voor patrimoniuminformatie", "fr"=>"Structure de Coordination de l’information patrimoniale", "en"=>"The Coordination Structure of Heritage Information"]],

            //Dutch topics
            'Begroting en Beheerscontrole'               => [["nl"=>"Begroting", "fr"=>"Budget", "en"=>"Budget"], ["nl"=>"Beheerscontrole", "fr"=>"Contrôle de la gestion", "en"=>"Management Control"]],
            'Beleid en Ondersteuning'                    => [["nl"=>"Beleid", "fr"=>"Stratégie", "en"=>"Strategy"], ["nl"=>"Ondersteuning", "fr"=>"Appui", "en"=>"Support"]],
            'Binnenlandse Zaken'                         => [["nl"=>"Binnenlandse Zaken", "fr"=>"Intérieur", "en"=>"Interior"]],
            'Buitenlandse Zaken'                         => [["nl"=>"Buitenlandse Zaken", "fr"=>"Affaires étrangères", "en"=>"Foreign Affairs"]],
            'Buitenlandse Handel'                        => [["nl"=>"Buitenlandse Handel", "fr"=>"Commerce extérieur", "en"=>"Foreign Trade"]],
            'Buitenlandse Zaken, Buitenlandse Handel en Ontwikkelingssamenwerking' => [["nl"=>"Buitenlandse Zaken", "fr"=>"Affaires étrangères", "en"=>"Foreign Affairs"], ["nl"=>"Buitenlandse Handel", "fr"=>"Commerce extérieur", "en"=>"Foreign Trade"], ["nl"=>"Ontwikkelingssamenwerking", "fr"=>"Coopération au développement", "en"=>"Developmental Cooperation"]],
            'Coördinatiestructuur voor patrimoniuminformatie' => [["nl"=>"Structure de Coordination de l’information patrimoniale", "fr"=>"Structure de Coordination de l’information patrimoniale", "en"=>"The Coordination Structure of Heritage Information"]],
            'Duurzame Ontwikkeling'                      => [["nl"=>"Duurzame Ontwikkeling", "fr"=>"Développement Durable", "en"=>"Sustainable Development"]],
            'Economische Zaken'                          => [["nl"=>"Economische Zaken", "fr"=>"Affaires économiques", "en"=>"Economic Affairs"]],
            'Economie, KMO, Middenstand en Energie'      => [["nl"=>"Economie", "fr"=>"Economie", "en"=>"Economy"], ["nl"=>"KMO", "fr"=>"PME", "en"=>"SME"], ["nl"=>"Middenstand", "fr"=>"Classes Moyennes", "en"=>"The Middle Classes"], ["nl"=>"Energie", "fr"=>"Energie", "en"=>"Energy"]],
            'Financiën'                                  => [["nl"=>"Financiën", "fr"=>"Finances", "en"=>"Finances"]],
            'Informatie- en Communicatietechnologie'     => [["nl"=>"Informatie- en Communicatietechnologie", "fr"=>"Technologie de l’information et de la communication", "en"=>"Information and Communication Technology"]],
            'Internationale samenwerking'                => [["nl"=>"Internationale samenwerking", "fr"=>"Coopération internationale", "en"=>"International Cooperation"]],
            'Justitie'                                   => [["nl"=>"Justitie", "fr"=>"Justice", "en"=>"Justice"]],
            'Landbouw'                                   => [["nl"=>"Landbouw", "fr"=>"Agriculture", "en"=>"Agriculture"]],
            'Landsverdediging'                           => [["nl"=>"Landsverdediging", "fr"=>"Défense Nationale", "en"=>"National Defense"]],
            'Maatschappelijke Integratie, Armoedebestrijding en Sociale Economie' => [["nl"=>"Maatschappelijke Integratie", "fr"=>"Intégration Sociale", "en"=>"Social Integration"], ["nl"=>"Armoedebestrijding", "fr"=>"Lutte Contre la Pauvreté", "en"=>"Poverty Reduction"], ["nl"=>"Sociale Economie", "fr"=>"Economie Sociale", "en"=>"Social Economy"]],
            'Middenstand'                                => [["nl"=>"Middenstand", "fr"=>"Classes Moyennes", "en"=>"The Middle Classes"]],
            'Mobiliteit en Vervoer'                      => [["nl"=>"Mobiliteit", "fr"=>"Mobilité", "en"=>"Mobility"], ["nl"=>"Vervoer", "fr"=>"Transports", "en"=>"Transportation"]],
            'Onderwijs'                                  => [["nl"=>"Onderwijs", "fr"=>"Enseignement", "en"=>"Education"]],
            'ONDERWIJS'                                  => [["nl"=>"Onderwijs", "fr"=>"Enseignement", "en"=>"Education"]],
            'Ontwikkelingssamenwerking'                  => [["nl"=>"Ontwikkelingssamenwerking", "fr"=>"Coopération au Développement", "en"=>"Developmental Cooperation"]],
            'Openbaar Ambt'                              => [["nl"=>"Openbaar Ambt", "fr"=>"Fonction publique", "en"=>"Public Service"]],
            'Openbare Werken'                            => [["nl"=>"Openbare Werken", "fr"=>"Travaux publics", "en"=>"Public Works"]],
            'Nationale Opvoeding'                        => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"]],
            'Nationale Opvoeding en Franse Cultuur'      => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"], ["nl"=>"Franse Cultuur", "fr"=>"Culture française", "en"=>"French Culture"]],
            'Nationale Opvoeding en Nederlandse Cultuur' => [["nl"=>"Nationale Opvoeding", "fr"=>"Education nationale", "en"=>"National Education"], ["nl"=>"Nederlandse Cultuur", "fr"=>"Culture néerlandaise", "en"=>"Dutch Culture"]],
            'Pensioenen'                                 => [["nl"=>"Pensioenen", "fr"=>"Pensions", "en"=>"Pensions"]],
            'Personeel en Organisatie'                   => [["nl"=>"Personeel", "fr"=>"Personnel", "en"=>"Personnel"], ["nl"=>"Organisatie", "fr"=>"Organisation", "en"=>"Organization"]],
            'Sociale Voorzorg'                           => [["nl"=>"Sociale Voorzorg", "fr"=>"Prévoyance Sociale", "en"=>"Social Security"]],
            'Sociale Zaken'                              => [["nl"=>"Sociale Zaken", "fr"=>"Affaires sociales", "en"=>"Social Affairs"]],
            'Sociale Zekerheid'                          => [["nl"=>"Sociale Zekerheid", "fr"=>"Sécurité sociale", "en"=>"Social Security"]],
            'Tewerkstelling en Arbeid'                   => [["nl"=>"Werkgelegenheid", "fr"=>"Emploi", "en"=>"Employment"], ["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"]],
            'Verkeerswezen'                              => [["nl"=>"Verkeerswezen", "fr"=>"Trafic", "en"=>"Traffic"]],
            'Volksgezondheid'                            => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"]],
            'Volksgezondheid en Gezin'                   => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Gezin", "fr"=>"Famille", "en"=>"Family"]],
            'Volksgezondheid en Leefmilieu'              => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Leefmilieu", "fr"=>"Environnement", "en"=>"The Environment"]],
            'Volksgezondheid, Veiligheid van de Voedselketen en Leefmilieu' => [["nl"=>"Volksgezondheid", "fr"=>"Santé Publique", "en"=>"Public Health"], ["nl"=>"Veiligheid van de Voedselketen", "fr"=>"Sécurité de la Chaine Alimentaire", "en"=>"Food Chain Security"], ["nl"=>"Leefmilieu", "fr"=>"Environnement", "en"=>"The Environment"]],
            'Werkgelegenheid, Arbeid en Sociaal Overleg' => [["nl"=>"Werkgelegenheid", "fr"=>"Emploi", "en"=>"Employment"], ["nl"=>"Arbeid", "fr"=>"Travail", "en"=>"Labor"], ["nl"=>"Sociaal Overleg", "fr"=>"Concertation sociale", "en"=>"Social Dialogue"]],
            'Wetenschapsbeleid'                          => [["nl"=>"Wetenschapsbeleid", "fr"=>"Politique Scientifique", "en"=>"Scientific Policy"]],
        );
        //Translates the months. Only Dutch is used because "û" and "ü" aren't being recognized
        $months = array(
            //French                                    //Dutch               //German
            'janvier'  => '-01-',                       'januari'  => '-01-', 'januar'   => '-01-',
            'février'  => '-02-',                       'februari' => '-02-', 'februar'  => '-02-',
            'mars'     => '-03-',                       'maart'    => '-03-', 'märz'     => '-03-',
            'avril'    => '-04-',                       'april'    => '-04-', 'april'    => '-04-',
            'mai'      => '-05-',                       'mei'      => '-05-', 'mai'      => '-05-',
            'juin'     => '-06-',                       'juni'     => '-06-', 'juni'     => '-06-',
            'juillet'  => '-07-',                       'juli'     => '-07-', 'juli'     => '-07-',
            'août'     => '-08-', 'aout' => '-08-',     'augustus' => '-08-', 'august'   => '-08-',
            'septembre'=> '-09-',                       'september'=> '-09-', 'september'=> '-09-',
            'octobre'  => '-10-',                       'oktober'  => '-10-', 'oktober'  => '-10-',
            'novembre' => '-11-',                       'november' => '-11-', 'november' => '-11-',
            'décembre' => '-12-', 'decembre' => '-09-', 'december' => '-12-', 'dezember' => '-12-'
        );

        //Sanitizes the name
        $sanitizeName = array(
            'D CEMBRE'      => 'décembre',

            '. _ '          => '. - ',
            '1991 - Arrêté' => '1991. - Arrêté',
            '1991. Besluit' => '1991. - Besluit',
            '1992. Besluit' => '1992. - Besluit',
            '1993, - Arrêté' => '1993. - Arrêté',
            '1993. Collectieve' => '1993. - Collectieve',
            '1993. Paritair' => '1993. - Paritair',
            '1944 _ Arrêté' => '1944. - Arrêté',
            '1994 - Allocation' => '1994. - Allocation',
            '1994 - Circulaire' => '1994. - Circulaire',
            '1994.- Circulaire' => '1994. - Circulaire',
            '1994. -Omzendbrief' => '1994. - Omzendbrief',
            '1995. Collectieve' => '1995. - Collectieve',
            '1995 - Collectieve' => '1995. - Collectieve',
            '1995. Omzendbrief' => '1995. - Omzendbrief',
            '1995. Paritair' => '1995. - Paritair',
            '1996. Verkiezingen' => '1996. - Verkiezingen',
            '1996. Omzendbrief' => '1996. - Omzendbrief',
            '1997 - Decreet' => '1997. - Decreet',
            '1998. Arrêté'  => '1998. - Arrêté',
            '1999. Omzendbrief' => '1999. - Omzendbrief',
            '2000. Overeenkomst' => '2000. - Overeenkomst',
            '2006.. Arrêté' => '2006. - Arrêté',

            'Omzendbrief nr. 416 van 6 juli 1995. - Bevordering' => '6 juli 1995. - Omzendbrief nr. 416 - Bevordering',
        );
        
        //Loops through the languages
        foreach (array('fr', 'nl') as $lang) {
            //Loops through the types
            foreach ($natures[$lang] as $natureBE => $natureUS) {
                //Finds the limit
                $limit[$lang] = $limit[$lang] ?? explode('&trier=', explode('&page=', file_get_html('https://www.ejustice.just.fgov.be/cgi_wet/list.pl?language=nl&sum_date=&trier=Verk%FCndung&dt='.strtr(strtoupper($natureBE), array(' '=>'+')).'&'.$lang.'='.$lang[0])->find('a.pagination-button.pagination-last')[0]->href)[1])[0];
                //Gets datapoints from the type
                $IDPreset = $natureUS['id'];
                $type = $natureUS['tp'];
                $LBpage = $LBPreset = $natureUS['lb'] ?? $scraper;
                $country = json_encode(isset($natureUS['ct']) ? [$scraper, $natureUS['ct']]:[$scraper]);
                $origin = $originPreset = isset($natureUS['og']) ? $natureUS['og']:NULL;
                $topic = $topicPreset = isset($natureUS['to']) ? $natureUS['to']:NULL;
                $status = 'Valid';
                //Gets the laws
                for ($page = $start[$lang]; $page <= $limit[$lang]; $page++) {
                    //Gets the data from API
                    $laws = file_get_html('https://www.ejustice.just.fgov.be/cgi_wet/list.pl?language=nl&sum_date=&trier=Verk%FCndung&dt='.strtr(strtoupper($natureBE), array(' '=>'+')).'&'.$lang.'='.$lang[0].'&page='.$page)->find('div.list-item');
                    foreach ($laws as $law) {
                        //Gets the main line and sanitizes it
                        $nameLine = str_replace(array_keys($sanitizeName), array_values($sanitizeName), $law->find('div.list-item--content')[0]->find('a')[0]->plaintext);
                        //Gets the date. For some reason, the month of 'März' is not working but it's the only one so I hardcoded it
                        $enactDate = $enforceDate = $lastactDate = trim(explode(' ', $nameLine)[3], '.').$months[strtolower(explode(' ', $nameLine)[2])].preg_replace('/[a-z]/', '', explode(' ', $nameLine)[1]);
                        //Gets the ID and name
                        $ID = $LBpage.':'.$IDPreset.trim($law->find('div.list-item--button')[0]->find('a')[0]->plaintext);
                        $name = trim(explode('. - ', $nameLine)[1], ' .');
                        //Gets the regime
                        switch(true) {
                            case strtotime($enactDate) < strtotime('today'):
                                $regime = '{"fr":"Le Royaume de Belgique", "nl":"Het Koninkrijk België", "de":"Das Königreich Belgien", "en":"The Kingdom of Belgium"}';
                                break;
                            case strtotime($enactDate) < strtotime('19 April 1839'):
                                $regime = '{"fr":"Le Royaume-Uni des Pays-Bas", "nl":"Het Verenigd Koninkrijk der Nederlanden", "de":"Das Vereinigte Königreich der Niederlande", "en":"The United Kingdom of the Netherlands"}';
                                break;
                        }
                        //Gets the origin and topic
                        $origin = $originPreset; $topic = $topicPreset;
                        $subtitle = trim($law->find('div.list-item--content')[0]->find('p.list-item--subtitle')[0]->plaintext);
                        if ($subtitle) {
                            foreach (explode(' - ', $subtitle) as $subtitle) {
                                //echo '<br/>'.json_encode(array_merge($origins, $topics)[$subtitle], JSON_UNESCAPED_UNICODE);
                                //Gets the origin
                                if (isset($origins[$subtitle])) {
                                    //Sets the origin
                                    if (is_array($origin)) {
                                        $origin = array_merge($origin, $origins[$subtitle][0]);
                                        $origin = array_map("json_decode", array_unique(array_map(function($el) { 
                                            return json_encode($el, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 
                                        }, $origin)));
                                    } else {
                                        $origin = $origins[$subtitle][0];
                                    }
                                    //Resets the LBpage if needed
                                    $LBpage = $origins[$subtitle][1] ?? $LBPreset;
                                }
                                //Gets the topic
                                if (isset($topics[$subtitle])) {
                                    if (is_array($topic)) {
                                        $topic = array_merge($topic, $topics[$subtitle]);
                                        $topic = array_map("json_decode", array_unique(array_map(function($el) { 
                                            return json_encode($el, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 
                                        }, $topic)));
                                    } else {
                                        $topic = $topics[$subtitle];
                                    }
                                }
                            }
                        }
                        //Gets the link
                        $source = 'https://www.ejustice.just.fgov.be/cgi_wet/'.$law->find('div.list-item--button')[0]->find('a')[0]->href;

                        //Makes sure there are no quotes in the title or source
                        $name = strtr($name, array("'"=>"\\'"));
                        $source = strtr($source, array("'"=>"%27"));

                        //JSONifies the values, making sure to incluse any already stored translations
                        $SQLFind = "SELECT * FROM `".strtolower($LBpage)."` WHERE `id`='".$ID."'";
                        $result = $conn->query($SQLFind);
                        if ($result->num_rows > 0) {
                            echo $SQLFind.'<br/>';
                            while ($row = $result->fetch_assoc()) {
                                //JSONifies the name
                                $nameJSON = json_decode($row['name'], true);
                                $nameJSON[$lang] = $name;
                                $name = json_encode($nameJSON, JSON_UNESCAPED_UNICODE);
                                $name = strtr($name, array("'"=>"\\'"));//Makes sure there are no quotes

                                //JSONifies the source
                                $sourceJSON = json_decode($row['source'], true);
                                $sourceJSON[$lang] = $source;
                                $source = json_encode($sourceJSON, JSON_UNESCAPED_UNICODE);

                                //Updates the table
                                $SQL2 = "UPDATE `".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                            }
                        } else {//If there is no existing entry
                            $name = '{"'.$lang.'":"'.$name.'"}';
                            $origin = isset($origin) ? "'".json_encode($origin, JSON_UNESCAPED_UNICODE)."'":'NULL';
                            $topic = isset($topic) ? "'".json_encode($topic, JSON_UNESCAPED_UNICODE)."'":'NULL';
                            $source = '{"'.$lang.'":"'.$source.'"}';

                            //Inserts the law to the table
                            $SQL2 = "INSERT INTO `".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `status`, `topic`, `source`) 
                                    VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$type."', '".$status."', ".$topic.", '".$source."')";
                        }

                        //Makes the query
                        echo '<a href="'.json_decode($source, true)[$lang].'" target="_blank">p. '.$page.':<a/> '.$SQL2.'<br/>';
                        if (!$test) {$conn->query($SQL2);}
                    }
                }
            }
        }

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>