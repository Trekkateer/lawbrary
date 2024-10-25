<html><body>
    <?php //!!Too many different types and origins, program runs out of memory!!
        //Settings
        $test = true; $country = 'RO';
        $start = 596; //Which page to start from
        $step = 50; //How many laws are on each page
        $limit = null; //Which page to end at

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

        //Makes sure there are five digits in every outputed number
        function zero_buffer ($inputNum, $outputLen=5) {
            $outputNum = ''.$inputNum;
            while (strlen($outputNum)<$outputLen) {$outputNum = '0'.$outputNum;}
            return $outputNum;
        };

        //Translates the months
        $months = array(
            'ianuarie' => 'January',
            'februarie' => 'February',
            'martie' => 'March',
            'aprilie' => 'April',
            'mai' => 'May',
            'iunie' => 'June',
            'iulie' => 'July',
            'august' => 'August',
            'septembrie' => 'September',
            'octombrie' => 'October',
            'noiembrie' => 'November',
            'decembrie' => 'December'
        );
        //Corrects some of the origins
        $origin_corrections = array(
            'ş'=>'ș', 'ţ'=>'ț',
            'Banca Nationala a Romaniei' => 'Banca Națională a României',
            'Ministerul Finantelor' => 'Ministerul Finanțelor',
        );
        //Translates the origins
        $origins = array(
            'Academia Română' => 'The Romanian Academy',
            'Academia de Științe Agricole și Silvice' => 'The Academy of Agricultural and Forestry Sciences',
            'Academia de Științe Juridice din România' => 'The Academy of Legal Sciences of Romania',

            'Act Internațional' => 'International Actor',

            'Administrația Națională a Penitenciarelor' => 'The National Administration of Penitentiaries',
            'Administrația Națională a Rezervelor de Stat și Probleme Speciale' => 'The National Administration of State Reserves and Special Issues',

            'Agenția Națională a Funcționarilor Publici' => 'The National Agency of Civil Servants',
            'Agenția Națională Antidoping' => 'The National Anti-Doping Agency',
            'Agenția de Cooperare Internațională pentru Dezvoltare' => 'The Agency for International Development Cooperation',
            'Agenția Națională pentru Ocuparea Forței de Muncă' => 'The National Employment Agency',
            'Agenția Națională de Administrare Fiscală' => 'The National Fiscal Administration Agency',
            'Agenția Națională de Integritate' => 'The National Integrity Agency',
            'Agenția Națională pentru Achiziții Publice' => 'The National Agency for Public Procurement',
            'Agenția Națională de Cadastru și Publicitate Imobiliară' => 'The National Agency for Land and Property Advertising',
            'Agenția Națională pentru Resurse Minerale' => 'The National Agency of Mineral Resources',
            'Agenția pentru monitorizarea și evaluarea performanțelor Întreprinderilor publice' => 'The Agency for the Monitoring and Evaluation of the Performances of Public Enterprises',
            'Agentia Romana pentru Investitii si Comert Exterior' => 'The Romanian Agency for Investments and Foreign Trade',
            'Agenția Română de Asigurare a Calității în Învățământul Superior' => 'The Romanian Agency for Quality Assurance in Higher Education',
            'Agenția Națională pentru Sport' => 'The National Sports Agency',

            'Autoritatea pentru Administrarea Activelor Statului' => 'The Authority for the Administration of State Assets',
            'Autoritatea Națională pentru Protecția Consumatorilor' => 'The National Authority for Consumer Protection',
            'Autoritatea Vamală Română' => 'The Romanian Customs Authority',
            'Autoritatea pentru Digitalizarea României' => 'The Authority for the Digitalization of Romania',
            'Autoritatea Electorală Permanentă' => 'The Permanent Electoral Authority',
            'Autoritatea Națională de Reglementare în Domeniul Energiei' => 'The National Regulatory Authority for Energy',
            'Autoritatea de Supraveghere Financiară' => 'The Financial Supervision Authority',
            'Autoritatea Națională de Reglementare în Domeniul Minier, Petrolier și al Stocării geologice a dioxidului de carbon' => 'The National Regulatory Authority for Mining, Petroleum and Geological Storage of Carbon Dioxide',
            'Autoritatea Națională de Reglementare pentru Serviciile Comunitare de Utilități Publice - A.N.R.S.C.' => 'The National Regulatory Authority for Public Community Utility Services - A.N.R.S.C.',
            'Autoritatea Națională pentru Protecția Drepturilor Persoanelor cu Dizabilități' => 'The National Authority for the Protection of the Rights of Disabled Persons',
            'Autoritatea Națională Sanitară Veterinară și pentru Siguranța Alimentelor' => 'The National Veterinary Health and Food Safety Authority',
            'Autoritatea Națională pentru Administrare și Reglementare în Comunicații' => 'The National Authority for Administration and Regulation in Communications',
            'Autoritatea Națională de Management al Calității în Sănătate' => 'The National Authority for Quality Management in Health',
            'Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal' => 'The National Authority for the Supervision of the Processing of Personal Data',
            'Autoritatea pentru Reformă Feroviară' => 'The Authority for Railway Reform',
            'Autoritatea pentru Supravegherea Publică a Activității de Audit Statutar' => 'The Authority for Public Oversight of Statutory Audit Activity',

            'Asociația Națională a Evaluatorilor Autorizați' => 'The National Association of Authorized Evaluators',

            'Banca Națională a României' => 'The National Bank',
            'Banca Nationala a Romaniei' => 'The National Bank',

            'Biroul Electoral Central pentru Alegerea Membrilor din România în Parlamentul European' => 'The Central Electoral Bureau for the Election of Members from Romania to the European Parliament',
            'Biroul Electoral Central' => 'The Central Electoral Bureau',
            'Biroul Român de Metrologie Legală' => 'The Romanian Bureau of Legal Metrology',
            'Biroul Permanent al Senatului' => 'The Standing Bureau of the Senate',
            'Biroul Permanent al Camerei Deputaților' => 'The Standing Bureau of the Chamber of Deputies',
            'Birourile Permanente ale Camerei Deputaților și Senatului' => 'The Standing Bureaus of the Chamber of Deputies and the Senate',

            'Corpul Experților Contabili' => 'The Body of Accounting Experts',

            'Centrul Euro-Atlantic pentru Reziliență' => 'The Euro-Atlantic Resilience Center',

            'Camera Auditorilor din România' => 'The Chamber of Auditors of Romania',
            'Camera de Comerț și Industrie' => 'The Chamber of Commerce and Industry',
            'Camera Deputaților' => 'The Chamber of Deputies',
            'Camera Consultanților Fiscali' => 'The Chamber of Fiscal Consultants',
            'Camera Auditorilor Financiari din România' => 'The Chamber of Financial Auditors of Romania',

            'Colegiul Medicilor Stomatologi din România' => 'The College of Dental Doctors of Romania',
            'Colegiul Medicilor din România' => 'The College of Doctors of Romania',
            'Colegiul Psihologilor din România' => 'The College of Psychologists of Romania',
            'Colegiul Farmaciștilor din România' => 'The College of Pharmacists of Romania',
            'Colegiul Fizioterapeuților din România' => 'The College of Physiotherapists of Romania',
            'Colegiul Național al Asistenților Sociali' => 'The National College of Social Workers',

            'Comitetul Interministerial de Finanțări, Garanții și Asigurări' => 'The Interministerial Committee for Financing, Guarantees and Insurance',
            'Comitetul de Inițiativă Legislativă' => 'The Legislative Initiative Committee',
            'Comitetul Național pentru Situații de Urgență' => 'The National Committee for Emergency Situations',
            'Comitetul Național pentru Supraveghere Macroprudențială' => 'The National Committee for Macroprudential Supervision',

            'Comisia Centrală de Rechiziții' => 'The Central Procurement Commission',
            'Comisia Națională pentru Contrololul Activităților Nucleare' => 'The National Commission for the Control of Nuclear Activities',
            'Comisia Națională pentru Controlul Activităților Nucleare' => 'The National Commission for the Control of Nuclear Activities',
            'Comisia de Insolvență la Nivel Central' => 'The Central Insolvency Commission',
            'Comisia Națională de Strategie și Prognoză' => 'The National Strategy and Forecast Commission',
            'Consiliul Superior al Registrului Urbaniștilor din România' => 'The High Council of the Register of Urban Planners of Romania',

            'Consiliul Concurenței' => 'The Competition Council',
            'Consiliul pentru Dezvoltare Regională' => 'The Council for Regional Development',
            'Consiliul Europei' => 'The Council of Europe',
            'Consiliul Național de Integritate' => 'The Council of National Integrity',
            'Consiliul pentru Dezvoltare Regionala al Regiunii de Dezvoltare Sud-Est' => 'The Council for Regional Development of the South-East Development Region',
            'Consiliul pentru Dezvoltare Regională Sud Vest Oltenia' => 'The Council for Regional Development South West Oltenia',
            'Consiliul Superior al Magistraturii' => 'The High Council of the Judiciary',
            'Consiliul Național al Audiovizualului' => 'The National Audiovisual Council',
            'Consiliul Național pentru Combaterea Discriminării' => 'The National Council for Combating Discrimination',
            'Consiliul de Monitorizare a Implementării Convenției privind Drepturile Persoanelor cu Dizabilități' => 'The Council for Monitering the Implementation of the Convention on the Rights of Persons with Disabilities',
            'Consiliul de Securitate' => 'The Security Council',
            'Consiliul Național de Soluționare a Contestațiilor' => 'The National Council for the Settlement of Appeals',

            'Curtea de Conturi' => 'The Court of Accounts',
            'Curtea Constituțională' => 'The Constitutional Court',
            'Curtea de Apel Constanța' => 'The Constanța Court of Appeals',
            'Curtea de Apel Constanța - Secția a II-a Civilă, de Contencios Administrativ și Fiscal' => 'The Constanța Court of Appeals - Section II Civil, Administrative and Fiscal Litigation',
            'Curtea Europeană a Drepturilor Omului' => 'The European Court of Human Rights',
            'Curtea de Apel Tg. Mureș - Sc II CV+CA+FISC' => 'The Tg. Mureș Court of Appeals - Sc II CV+CA+FISC',
            'Curtea de Apel Brașov - Secția Contencios Administrativ și Fiscal' => 'The Brașov Court of Appeals - Section of Administrative and Fiscal Litigation',
            'Curtea de Apel București-Secția a VIII-a Contencios Administrativ, Fiscal' => 'The Bucharest Court of Appeals - Section VIII Administrative and Fiscal Litigation',
            'Curtea de Apel București-Secția a IX-a Contencios Administrativ și Fiscal' => 'The Bucharest Court of Appeals - Section IX Administrative and Fiscal Litigation',
            'Curtea de Apel Cluj - Secția a III-a Contencios Administrativ și Fiscal' => 'The Cluj Court of Appeals - Section III Administrative and Fiscal Litigation',
            'Curtea de Apel Iași' => 'The Iași Court of Appeals',
            'Curtea de Apel Pitești' => 'The Pitești Court of Appeals',
            'Curtea de Apel Suceava' => 'The Suceava Court of Appeals',
            'Curtea de Apel Timisoara-Sc.Cnt.Ad+Fisc' => 'The Timisoara Court of Appeals - Sc.Cnt.Ad+Fisc',
            'Înalta Curte de Casație și Justiție' => 'The High Court of Cassation and Justice',

            'Departamentul pentru Relația cu Republica Moldova' => 'The Department for Relations with the Republic of Moldova',

            'Direcția Generală de Informații a Apărării' => 'The General Directorate of Defence Information',
            'Direcția Generală pentru Evidența Persoanelor' => 'The General Directorate for the Registration of Persons',
            'Directoratul Național de Securitate Cibernetică' => 'The National Directorate of Cyber Security',

            'Fondul de Compensare a Investitorilor' => 'The Investor Compensation Fund',
            'Fondul de Garantare a Depozitelor Bancare' => 'The Banking Deposit Guarantee Fund',

            'Guvernul' => 'The Government',

            'Grupul de Suport Tehnico-Științific privind Gestionarea Bolilor Înalt Contagioase pe Teritoriul României' => 'The Technical-Scientific Support Group for the Management of Highly Contagious Diseases on the Territory of Romania',

            'CREDITCOOP Casa Centrală' => 'CREDITCOOP Central House',
            'Casa Națională de Asigurări de Sănătate' => 'The National Health Insurance House',
            'Casa Națională de Pensii Publice' => 'The National Public Pension House',

            'Inspecția de Stat pentru Controlul Cazanelor, Recipientelor sub Presiune și Instalațiilor de Ridicat' => 'The State Inspection for the Control of Boilers, Pressure Vessels and Lifting Installations',
            'Inspectoratul General pentru Situații de Urgență' => 'The General Inspectorate for Emergency Situations',
            'Inspecția Judiciară' => 'The Judicial Inspection',

            'Institutul Național de Statistică (și Studii Economice)' => 'The National Institute of Statistics (and Economic Studies)',
            'Institutul Național de Administrație' => 'The National Institute of Administration',

            'Ministerul Culturii' => 'The Ministry of Culture',
            'Ministerul Culturii și Identității Naționale' => 'The Ministry of Culture and National Identity',
            'Ministerul Apărării Naționale' => 'The Ministry of National Defence',
            'Ministerul Agriculturii și Dezvoltării Rurale' => 'The Ministry of Agriculture and Rural Development',
            'Ministerul Agriculturii, Pădurilor și Dezvoltării Rurale' => 'The Ministry of Agriculture, Forestry and Rural Development',
            'Ministerul Dezvoltarii, Lucrarilor Publice si Administratiei' => 'The Ministry of Development, Public Works and Administration',
            'Ministerul Dezvoltării, Lucrărilor Publice și Locuințelor' => 'The Ministry of Development, Public Works and Housing',
            'Ministerul Economiei' => 'The Ministry of the Economy',
            'Ministerul Antreprenoriatului și Turismului' => 'The Ministry of Entrepreneurship and Tourism',
            'Ministerul Economiei, Antreprenoriatului si Turismului' => 'The Ministry of the Economy, Entrepreneurship and Tourism',
            'Ministerul Educației' => 'The Ministry of Education',
            'Ministerul Educației, Cercetării și Inovării' => 'The Ministry of Education, Research and Innovation',
            'Ministerul Educației Naționale' => 'The Ministry of National Education',
            'Ministerul Mediului, Apelor și Pădurilor' => 'The Ministry of Environment, Waters and Forests',
            'Ministerul Energiei' => 'The Ministry of Energy',
            'Ministerul Familiei, Tineretului și Egalității de Șanse' => 'The Ministry of Family, Youth and Equal Opportunities',
            'Ministerul Finanțelor' => 'The Ministry of Finance',
            'Ministerul Afacerilor Externe' => 'The Ministry of Foreign Affairs',
            'Ministerul Sănătății' => 'The Ministry of Health',
            'Ministerul Investitiilor si Proiectelor Europene' => 'The Ministry of Investments and European Projects',
            'Ministerul Afacerilor Interne' => 'The Ministry of Internal Affairs',
            'Ministerul Justiției' => 'The Ministry of Justice',
            'Ministerul Muncii, Familiei și Egalității de Şanse' => 'The Ministry of Labour, Family and Equal Opportunities',
            'Ministerul Muncii și Justiției Sociale' => 'The Ministry of Labour and Social Justice',
            'Ministerul Muncii și Solidarității Sociale' => 'The Ministry of Labour and Social Solidarity',
            'Ministerul Sănătății Publice' => 'The Ministry of Public Health',
            'Ministerul Dezvoltării Regionale și Administrației Publice' => 'The Ministry of Regional Development and Public Administration',
            'Ministerul Cercetarii, Inovarii si Digitalizarii' => 'The Ministry of Research, Innovation and Digitalization',
            'Ministerul Sportului' => 'The Ministry of Sports',
            'Ministerul Turismului' => 'The Ministry of Tourism',
            'Ministerul Transporturilor și Infrastructurii' => 'The Ministry of Transport and Infrastructure',
            'Ministerul Apelor și Protecției Mediului' => 'The Ministry of Water and Environmental Protection',
            'Ministerul Apelor și Pădurilor' => 'The Ministry of Water and Forestry',
            'Ministerul Tineretului și Sportului' => 'The Ministry of Youth and Sports',

            'Oficiul Român pentru Drepturile de Autor' => 'The Romanian Copyright Office',
            'Parchetul de pe Lângă Înalta Curte de Casație și Justiție' => 'The Prosecutor’s Office Attached to the High Court of Cassation and Justice',
            'Oficiul Național pentru Jocuri de Noroc' => 'The National Office for Gambling',
            'Oficiul Registrului Național al Informațiilor Secrete de Stat' => 'The Office of the National Registry of State Secrets',
            'Oficiul de Film si Investitii Culturale' => 'The Office of Film and Cultural Investment',
            'Oficiul Național de Prevenire și Combatere a Spălării Banilor' => 'The National Office for the Prevention and Combating of Money Laundering',

            'Ordinul Asistenților Medicali Generaliști, Moașelor și Asistenților Medicali din România' => 'The Order of General Medical Assistants, Midwives and Medical Assistants in Romania',
            'Ordinul Arhitecților din România' => 'The Order of Architects in Romania',

            'Organizația Maritimă Internațională' => 'The International Maritime Organization',
            'Organizația Internațională de Asistență Maritimă pentru Navigație' => 'The International Organization for Maritime Assistance to Navigation',

            'Parlamentul' => 'Parliament',

            'Partide Politice' => 'Political Parties',

            'Președintele României' => 'The President',
            'Prim-Ministrul' => 'The Prime Minister',

            'Societatea Națională de Cruce Roșie din România' => 'The National Red Cross of Romania',

            'Secretariatul General al Guvernului' => 'The General Secretariat of the Government',
            'Secretariatul de Stat pentru Culte' => 'The State Secretariat of Religious Affairs',
            
            'Secretarul General al Camerei Deputaților' => 'The Secretary General of the Chamber of Deputies',

            'Senatul' => 'The Senate',

            'Serviciul de Informații Externe' => 'The Foreign Intelligence Service',
            'Serviciul Român de Informații' => 'The Romanian Intelligence Service',
            'Serviciul de Protecție și Pază' => 'The Protection and Security Service',
            'Serviciul de Pază și Protecție' => 'The Security and Protection Service',
            'Serviciul de Telecomunicații Speciale' => 'The Special Telecommunications Service',

            'Statul Major al Apărării' => 'The Defence Staff',

            'Uniunea Națională a Executorilor Judecătorești' => 'The National Union of Balliffs',
            'Uniunea Națională a Practicienilor în Insolvență' => 'The National Union of Insolvency Practitioners',
            'Uniunea Națională a Notarilor Publici' => 'The National Union of Public Notaries',
            'Uniunea Națională a Barourilor din România' => 'The National Union of Romanian Bars',

            'Organizația Națiunilor Unite' => 'The United Nations',
        );
        //Translates the types
        $types = array(
            'ACT' => 'Act',
            'ACORD' => 'Agreement',
            'ANEXA' => 'Appendix',
            'AMENDAMENT' => 'Amendment',
            'CUANTUM' => 'Amount',
            'ARANJAMENT' => 'Arrangement',
            'ATRIBUTII' => 'Attributions',
            'CIRCULARA' => 'Circular',
            'COD' => 'Code',
            'CODUL' => 'Code',
            'COMUNICAT' => 'Communication',
            'INCHEIERE' => 'Conclusion',
            'CONDITII' => 'Conditions',
            'COND+PROC' => 'Conditions and Procedures',
            'CONTRACT' => 'Contract',
            'CONVENTIE' => 'Convention',
            'CRITERII' => 'Criteria',
            'HOTARARE' => 'Decision',
            'DECIZIE' => 'Decision',
            'DECLARATIE' => 'Declaration',
            'DECRET' => 'Decree',
            'DISPOZITIE' => 'Disposition',
            'GHID' => 'Guide',
            'INITIATIVA' => 'Initiative',
            'INSTRUCTIUNI' => 'Instructions',
            'ORD' => 'Order',
            'ORDIN' => 'Order',
            'ORDONANTA' => 'Ordinance',
            'LEGE' => 'Law',
            'PR-LEGE' => 'Draft Law',
            'LISTA' => 'List',
            'MASURI' => 'Measure',
            'MECANISME' => 'Mechanism',
            'METODOLOGIE' => 'Methodology',
            'MODALITATE' => 'Methodology',
            'MEMORANDUM' => 'Memorandum',
            'NORMA' => 'Norm',
            'NORMATIV' => 'Normative',
            'PLAN' => 'Plan',
            'PRESCRIPTII' => 'Prescriptions',
            'PRINCIPII' => 'Principles',
            'PROCEDURA' => 'Procedure',
            'PROCES' => 'Process',
            'PROGRAM' => 'Program',
            'PROTOCOL' => 'Protocol',
            'RECTIFICARE' => 'Rectification',
            'RAPORT' => 'Report',
            'REGISTRU' => 'Register',
            'REGULAMENT' => 'Regulations',
            'REGLEMENTARI' => 'Regulations',
            'CERINTE' => 'Requirements',
            'REZOLUTIE' => 'Resolution',
            'REGULI' => 'Rules',
            'SCHEMA' => 'Scheme',
            'SENTINTA' => 'Sentencing',
            'STANDARD' => 'Standard',
            'STATUT' => 'Statute',
            'STRATEGIE' => 'Strategy',
            'SISTEM' => 'System',
            'TABLOU' => 'Table',
            'TRATAT' => 'Treaty',
        );

        //Gets the page limit
        $html_dom = file_get_html('https://legislatie.just.ro/Public/RezultateCautare?page='.$start.'&rezultatePerPagina='.$step/10);
        $limit = $limit ?? explode('?page=', explode('&', $html_dom->find('div#textarticol')[0]->find('ul.pagination')[0]->find('li.PagedList-skipToLast')[0]->find('a')[0]->href)[0])[1];
        //Loops through the pages
        for ($page = $start; $page <= $limit; $page++) { echo ('<br/>Page '.$page.'<br/>');
            //Skips page 384
            if ($page == 384 || $page == 389 || $page == 407 || $page == 409 || $page == 582 || $page == 584 || $page = 597) {continue;}
            //Loops through the laws
            $html_dom = file_get_html('https://legislatie.just.ro/Public/RezultateCautare?page='.$page.'&rezultatePerPagina='.$step/10);
            $laws = $html_dom->find('div#textarticol')[0]->find('div.search_result_page')[0]->find('div.search_result_item');
            foreach ($laws as $law) {
                //Gets values
                $enactDate = date('Y-m-d', strtotime(strtr(explode(' din ', $law->find('p')[1]->find('span.S_DEN')[0]->plaintext)[1], $months))); $lastactDate = $enactDate;
                $enforceDate = date('Y-m-d', strtotime(strtr(explode('Data intrarii in vigoare: ', trim($law->find('p')[2]->plaintext))[1], $months)));
                $ID = $country.'-'.str_replace('.', '', explode(' ', $law->find('p')[1]->find('span.S_DEN')[0]->plaintext)[2]);
                //Gets the regime
                switch(true) {
                    case strtotime($enactDate) < strtotime('5 February 1859'):
                        $regime = 'The Ottoman Empire';
                        break;
                    case strtotime('5 February 1859') < strtotime($enactDate) && strtotime($enactDate) < strtotime('25 March 1881'):
                        $regime = 'The United Principalities of Moldavia and Wallachia';
                        break;
                    case strtotime('25 March 1881') < strtotime($enactDate) && strtotime($enactDate) < strtotime('1 December 1918'):
                        $regime = 'The Kingdom of Romania';
                        break;
                    case strtotime('1 December 1918') < strtotime($enactDate) && strtotime($enactDate) < strtotime('30 December 1947'):
                        $regime = 'The Great Union of Romania';
                        break;
                    case strtotime('30 December 1947') < strtotime($enactDate) && strtotime($enactDate) < strtotime('8 December 1991'):
                        $regime = 'The People’s Republic of Romania';
                        break;
                    case strtotime('8 December 1991') < strtotime($enactDate) && strtotime($enactDate) <= strtotime('today'):
                        $regime = 'Romania';
                        break;
                }
                //Gets the rest of the values
                $name = trim($law->find('p')[1]->find('span.S_PAR')[0]->plaintext);
                $type = $types[explode(' ', $law->find('p')[0]->find('a')[0]->plaintext)[1]];
                    if (str_contains(strtolower($name), 'amend')) {$type = 'Amendment to '.$type;}
                $status = 'Valid';
                $origin = strtr(trim($law->find('table.S_EMT')[0]->find('tr')[0]->find('td')[1]->plaintext), $origin_corrections);
                $source = 'https://legislatie.just.ro'.$law->find('p')[0]->find('a')[0]->href;

                //Makes sure there are no quotes in the title
                $name = strtr($name, array("'"=>"’", ' "'=>' “', '"'=>'”'));

                //JSONifies the values
                $name = '{"ro":"'.$name.'"}';
                $origin = '{"ro":"'.$origin.'", "en":"'.$origins[$origin].'"}';
                $source = '{"ro":"'.$source.'"}';
                
                //Inserts the new laws
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `type`, `status`, `origin`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', '".$type."', '".$status."', '".$origin."', '".$source."')"; echo $SQL2.'<br/>';
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