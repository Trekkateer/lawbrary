<html><body>
    <?php //!!The source website is not responding!!
        //Settings
        $test = true; $country = 'SE';
        $start = 11;//What page to start from
        $limit = null;//Total number of pages desired. Set to null to get number automatically

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php'; // '../' refers to the parent directory
        $status_dom = new simple_html_dom();
        $law_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sanitizes the name
        $sanitizeName = ['/rl/'=>''];

        //Translates the topics
        $topicMemo = [];
        $topics = [
            'Ädellövskogs'=>'Deciduous Forests',
            'Åklagardata'=>'Prosecution Data',
            'Åklagar'=>'Prosecution',
            'Äktenskaps'=>'Marriages',
            'Aktiekonto'=>'Stock Accounts',
            ' av andra staters anslutning till vissa konventioner'=>'On the accession of other states to certain conventions',
            ' av ändring av beloppsgränser i Montrealkonventionen'=>'On the amendment of monetary limits in the Montreal Convention',
            'Anställnings'=>'Employment',
            'Aktiebolags'=>'Stock Companies',
            'Alkohol'=>'Alcohol',
            'Apoteksdata'=>'Pharmacy Data',
            'Anslags'=>'Appropriations',
            'Arbetsmarknads'=>'The Labor Market',
            'Arbetstids'=>'Working Hours',
            'Arkiv'=>'Archives',
            'Årsredovisnings'=>'Annual Reports',
            'Artskydds'=>'Species Protection',
            'Atomansvarighets'=>'Nuclear Responsibility',
            'Automatspels'=>'Automated Games',
            'Avfalls'=>'Waste',
            'Avgasrenings'=>'Exhaust Gas Cleaning',
            'Avgifts'=>'Charges',
            'Badvatten'=>'Bathing Water',
            'Bankaktiebolags'=>'Banking Companies',
            'Bankrörelse'=>'Banking',
            'Barlastvatten'=>'Ballast Water',
            'Barnbidrags'=>'Child Support',
            'Begränsnings'=>'Restrictions',
            'Begravnings'=>'Funerals',
            'Beredskaps'=>'Emergency Preparedness',
            'Biblioteks'=>'Libraries',
            'Bilavgas'=>'Car Exhaust',
            'Biobanks'=>'Biobanks',
            'Bidragsbrotts'=>'Donation Fraud',
            'Bilskrotnings'=>'Car Scrapping',
            'Bokförings'=>'Accounting',
            'Börsdata'=>'Stock Exchange Data',
            'Börs'=>'Stock Exchange',
            'Bostadsbidrags'=>'Housing Permits',
            'Bostadsrätts'=>'Housing Rights',
            'Brottsdata'=>'Crime Data',
            'Brottsskade'=>'Crime Damage',
            'Brotts'=>'Crime',
            'Budget'=>'Budget',
            'Byggprodukt'=>'Construction Products',
            'Data'=>'Data',
            'Delgivnings'=>'Service',
            'Departements'=>'Departments',
            'Diskriminerings'=>'Discrimination',
            'Djurskydds'=>'Animal Protection',
            'Domkapitels'=>'Judgements',
            'Domstolsdata'=>'Court Data',
            'Donations'=>'Donations',
            'Drivmedels'=>'Fuel',
            'Efterlysnings'=>'Search Warrants',
            'Efterforsknings'=>'Research',
            'Elberedskaps'=>'Electrical Readiness',
            'Elför'=>'Electricity',
            'Elinstallatörs'=>'Electrical Installations',
            'Elsäkerhets'=>'Electrical Safety',
            'El'=>'Electricity',
            'Elsäkerhets'=>'Electrical Safety',
            'Epizooti'=>'Epizootic',
            'Ersättnings'=>'Compensation',
            'Familjebidrags'=>'Family Support',
            'Fängelse'=>'Prison',
            'Fartygssäkerhets'=>'Ship Safety',
            'Fastbränsle'=>'Solid Fuel',
            'Fastighetsmäklar'=>'Real Estate Brokers',
            'Fastighetstaxerings'=>'Property Taxes',
            'Fiskeri'=>'Fisheries',
            'Fiske'=>'Fishing',
            'Fjärrkyle'=>'District Cooling',
            'Fjärrvärme'=>'District Heating',
            'Folkbokförings'=>'Census',
            'folkrätts'=>'International Law',
            'Fonds'=>'Funds',
            'Föräldraledighets'=>'Parental Leave',
            'Föreningsbanks'=>'Association Banks',
            'Fordonsskatte'=>'Vehicle Taxes',
            'Fordonsskatt'=>'Vehicle Taxes',
            'Fordons'=>'Vehicles',
            'Förköps'=>'Advance Purchases',
            ' om ändring av vissa skadelivräntor'=>'On the amendment of certain injury pensions',
            ' om ändring i förordningen'=>'On Amendments to Regulations',
            ' om gällande inlåningsränta i ungdomsbosparandet'=>'On the current deposit rate in youth savings',
            ' om gällande räntesats för allemanssparandet'=>'On the current interest rate for public savings',
            ' om nationella järnvägssystem'=>'On National Railway Systems',
            ' om preventiva vistelseförbud'=>'On Preventive Residence Bans',
            ' om upphävande av Rikspolisstyrelsens föreskrifter och allmänna råd'=>'On the repeal of the National Police Agency’s regulations and general advice',
            ' med kompletterande bestämmelser till EU:s förordning om digitala tjänster'=>'On supplementary provisions to the EU regulation on digital services',
            ' med instruktion för Myndigheten för press, radio och tv'=>'On the instructions for the Authority of the Press, Radio and Television',
            ' om statligt stöd till avskiljning, transport och geologisk lagring av koldioxid med biogent ursprung'=>'On state aid for the separation, transport and geological storage of carbon dioxide of biogenically origin',
            ' om statligt stöd för vissa åtgärder som syftar till att bevara eller återställa biologisk mångfald'=>'On state aid for certain measures aimed at preserving and restoring biodiversity',
            ' om upphävande av Statistiska centralbyråns föreskrifter'=>'On the repeal of the regulations of the Central Bureau of Statistics',
            'Förmynderskaps'=>'Guardianship',
            'Försäkringsavtals'=>'Insurance Contracts',
            'Försäkringskassans '=>'The Insurance Fund',
            'Försäkringsrörelse'=>'Insurance Businesses',
            'Församlings'=>'Parishes',
            'Förvaltnings'=>'Administration',
            'Frihandels'=>'Free Trade',
            'Frihets'=>'Freedom',
            'Frivårds'=>'Probation',
            'Garanti'=>'Guarantees',
            'Grundskole'=>'Elementary School',
            'Gymnasie'=>'High School',
            'Häktes'=>'Arrests',
            'Hälsoskydds'=>'Health Protection',
            'Hälso- och sjukvårds'=>'Healthcare',
            'Handelskammar'=>'The Chambers of Commerce',
            'Havsmiljö'=>'The Marine Environment',
            'Havsplanerings'=>'Maritime Planning',
            'Hemförsäljnings'=>'Home Sales',
            'Hemvärns'=>'Home Guard',
            'Högskole'=>'College',
            'Indrivnings'=>'Recovery',
            'Industriutsläpps'=>'Industrial Emissions',
            'Inkasso'=>'Debt Collection',
            'Inkomstskatte'=>'Income Taxes',
            'Inrättande'=>'Establishments',
            'Inregräns'=>'Internal Borders',
            'Insiderstraff'=>'Insider Penalties',
            'Insider'=>'Insiders',
            'Inskrivnings'=>'Enrollment',
            'Installatörs'=>'Installers',
            'Internrevisions'=>'Internal Audits',
            'Isbrytar'=>'Icebreakers',
            'Jakt'=>'Hunting',
            'Jämställdhets'=>'Gender Equality',
            'Järnvägsmarknads'=>'The Railway Market',
            'Järnvägssäkerhets'=>'Railway Safety',
            'Järnvägsteknik'=>'Railway Engineering',
            'Järnvägstrafik'=>'Railway Traffic',
            'Järnvägs'=>'Railways',
            'Jordförvärvs'=>'Land Acquisition',
            'Kameraövervaknings'=>'Camera Surveillance',
            'Kamerabevaknings'=>'Camera Surveillance',
            'Kapitalförsörjnings'=>'Provision of Capital',
            'Karantäns'=>'Quarantine',
            'Kasino'=>'Casinos',
            'Klimatrapporterings'=>'Climate Reporting',
            'Klimat'=>'Climate',
            'Kommissions'=>'Commissions',
            'Kommitté'=>'Committee',
            'Kommunalförbunds'=>'Municipal Associations',
            'Kommunal'=>'Municipal',
            'Konkurrensskade'=>'Damage from Competition',
            'Konkurrens'=>'Competition',
            'Konkurs'=>'Bankruptcy',
            'Konsumentköp'=>'Consumer Purchases',
            'Konsumentkredit'=>'Consumer Credit',
            'Konsumenttjänst'=>'Consumer Services',
            'Kontinentalsockels'=>'Continental Shelves',
            'Kontoförings'=>'Accounting',
            'Köp'=>'Purchases',
            'Körkorts'=>'Driver’s Licenses',
            'Kreditupplysnings'=>'Credit Information',
            'Kriminalvårdsdata'=>'Criminal Justice Data',
            'Kulturmiljö'=>'The Cultural Environment',
            'Kupongskatte'=>'Coupon Taxes',
            'Kustbevakningsdata'=>'Coast Guard Data',
            'Kustbevaknings'=>'Coast Guard',
            'Kyrklig '=>'Church',
            'Kyrkofonds'=>'Church Funds',
            'Kyrko'=>'Church',
            'Läkemedels'=>'Medicines',
            'Laponia'=>'Lapland',
            'Livsmedels'=>'Food',
            'Lokalradio'=>'Local Radio',
            'Lönegaranti'=>'Wage Guarantees',
            'Lotteri'=>'The Lottery',
            'Luftfarts'=>'Aviation',
            'Luftkvalitets'=>'Air Quality',
            'Luftvårds'=>'Air Traffic Control',
            'Marknadsförings'=>'Marketing Activities',
            'Marknadsmissbruks'=>'Market Abuse',
            'Medborgarskaps'=>'Citezenship',
            'Mediestöds'=>'Media Support',
            'Mervärdesskatte'=>'Value Added Tax',
            'Militärtrafik'=>'Military Traffic',
            'Miljöbedömnings'=>'Environmental Assessment',
            'Miljöprövnings'=>'Environmental Review',
            'Miljöskade'=>'Environmental Damage',
            'Miljöskydds'=>'Environmental Protection',
            'Miljötillsyns'=>'Environmental Supervision',
            'Miljö'=>'The Environment',
            'Mineral'=>'Minerals',
            'Mönsterskydds'=>'Pattern Protection',
            'Mönstrings'=>'Patterns',
            'Musei'=>'Museums',
            'Myndighets'=>'Officials',
            'Namn'=>'Names',
            'Narkotikastraff'=>'Drug Offenses',
            'Närradio'=>'Local Radio',
            'Nationalparks'=>'National Parks',
            'Nationalstadsparks'=>'National City Parks',
            'Naturgas'=>'Natural Gas',
            'Notarie'=>'Notaries',
            'Nybyggnadslåne'=>'New Construction Loans',
            'Offentlighets- och sekretess'=>'Publicity and Confidentiality',
            'Officers'=>'Officers',
            'Ombyggnadslåne'=>'Reconstruction Loans',
            'Omplacerings'=>'Relocations',
            'Ordningsbots'=>'Order Fines',
            'Ordnings'=>'Orders',
            'Paketrese'=>'Package Transportation',
            'Pantbanks'=>'Pawn Shops',
            'Patent'=>'Patents',
            'Patientdata'=>'Patient Data',
            'Patientjournal'=>'Patient Records',
            'Patientsäkerhets'=>'Patient Safety',
            'Patientskade'=>'Patient Injury',
            'Patient'=>'Patients',
            'Personalföreträdar'=>'Personnel Representatives',
            'Personalkontroll'=>'Personnel Control',
            'Personuppgifts'=>'Personal Data',
            'Plan- och bygg'=>'Planning and Construction',
            'Polisdata'=>'Police Data',
            'Polisregister'=>'Police Register',
            'Polisutbildnings'=>'Police Training',
            'Polis'=>'Police',
            'Postverkets '=>'The Post Office',
            'Post'=>'Mail',
            'Prästanställnings'=>'Clerical Employments',
            'Presstöds'=>'Press Support',
            'Prisinformations'=>'Price Information',
            'Prisreglerings'=>'Price Regulation',
            'Pris'=>'Price',
            'Privatskol'=>'Private School',
            'Produktansvars'=>'Product Liability',
            'Produktsäkerhets'=>'Product Safety',
            'Räddningstjänst'=>'Rescue Services',
            'Radio- och tv-'=>'Radio and TV',
            'Radio- och TV-'=>'Radio and TV',
            'Radioansvarighets'=>'Radio Responsibility',
            'Radiostörnings'=>'Radio Interference',
            'Radioutrustnings'=>'Radio Equipment',
            'Radio'=>'Radio',
            'Rättshjälps'=>'Legal Aid',
            'Rättsinformations'=>'Legal Information',
            'Rehabiliterings'=>'Rehabilitation',
            'Renhållnings'=>'Cleaning',
            'Rennärings'=>'Reindeer Keeping',
            'Resegaranti'=>'Travel Insurance',
            'Reservofficers'=>'Reserve Officers',
            'Revisions'=>'Audits',
            'Revisors'=>'Auditors',
            'Rikförsäkringsverkets '=>'The National Insurance Agency',
            'Riksåklagarens '=>'The National Prosecutor',
            'Riksbankens '=>'The Central Bank',
            'Riksdags'=>'Parliament',
            'Riksförsäkringsverkets '=>'The National Insurance Agency',
            'Säkerhetsskydds'=>'Safety and Security',
            'Sambo'=>'Sambo',
            'Sameskol'=>'Sami School',
            'Sametings'=>'The Sami Parliament',
            'Särskole'=>'Special School',
            'Spel'=>'Games',
            'Språk'=>'Language',
            'Sjöarbetstids'=>'Maritime Working Hours',
            'Sjöförklarings'=>'Maritime Declarations',
            'Sjöfartsstyrelsens '=>'Maritime Administration',
            'Sjömans'=>'Seamanship', 
            'Sjötrafik'=>'Maritime Traffic',
            'Sjö'=>'Maritime',
            'Skattebetalnings'=>'Tax Payments',
            'Skattebrottsdata'=>'Tax Offense Data',
            'Skattebrotts'=>'Tax Offenses',
            'Skatteförfarande'=>'Tax Procedure',
            'Skatte'=>'Taxes',
            'Skogsvårds'=>'Forestry',
            'Skol'=>'School',
            'Skuldsanerings'=>'Debt Restructuring',
            'Skydds'=>'Protection',
            'Smittskydds'=>'Outbreak Prevention',
            'Socialavgifts'=>'Social Security Contributions',
            'Socialförsäkringsregister'=>'Social Insurance Register',
            'Socialförsäkrings'=>'Social Insurance',
            'Socialtjänst'=>'Social Services',
            'Sparbanks'=>'Savings Banks',
            'Specialskole'=>'Special School',
            'Strafföreläggande'=>'Penalty Orders',
            'Starkströms'=>'High Voltages',
            'Statens bostadskreditnämnds '=>'The State Housing Credit Board',
            'Statsflygs'=>'State Aviation',
            'Statskalender'=>'State Calendar',
            'Stiftelse'=>'Foundation',
            'Strafftids'=>'Penalty Time',
            'Strålskydds'=>'Radiation Protection',
            'Studentkårs'=>'Student Unions',
            'Studiestödsdata'=>'Study Support Data',
            'Studiestöds'=>'Study Support',
            'Svävarfarts'=>'Air Traffic',
            'Svavel'=>'Sulfur',
            'Tandvårds'=>'Dental Care',
            'Taxerings'=>'Taxation',
            'Taxitrafik'=>'Taxi Traffic',
            'Tele'=>'Telecommunications',
            'Terroristbrotts'=>'Terrorist Crimes',
            'Tidsbegränsnings'=>'Time Restrictions',
            'Tillträdes'=>'Access',
            'Tjänsteexport'=>'Export of Services',
            'Tjänstledighets'=>'Leave of Absence',
            'Tobaks'=>'Tobacco',
            'Totalförsvarsdata'=>'Total Defence Data',
            'Totalförsvarets '=>'Total Defence',
            'Trafik'=>'Traffic',
            'Tullbefogenhets'=>'The Costoms Authority',
            'Tullbrottsdata'=>'Costoms Violation Data',
            'Tullbrotts'=>'Costoms Violations',
            'Tullfrihets'=>'Costoms Exemptions',
            'Tullregister'=>'Costoms Records',
            'Tulltaxe'=>'Costoms Taxes',
            'Tull'=>'Costoms',
            'Tul'=>'Costoms',
            'Uppbörds'=>'Collection',
            'Upphandlings'=>'Procurement',
            'Upphovsrätts'=>'Copyrights',
            'Utlandsrese'=>'International Travel',
            'Utlänningsdata'=>'Foreigners Data',
            'Utlännings'=>'Foreigners',
            'Utsädes'=>'Seeds',
            'Utsökningsregister'=>'Search Registers',
            'Utsöknings'=>'Searches',
            'Vägför'=>'Roads',
            'Vägmärkes'=>'Road Signs',
            'Vägsäkerhets'=>'Road Safety',
            'Vägtrafikdata'=>'Road Traffic Data',
            'Vägtrafikskatte'=>'Road Traffic Taxes',
            'Vägtrafik'=>'Road Traffic',
            'Validerings'=>'Validations',
            'Val'=>'Election',
            'Vapen'=>'Weapons',
            'Varumärkes'=>'Branded',
            'Vattenboks'=>'Water Books',
            'Vattenförvaltnings'=>'Water Management',
            'Vattenrätts'=>'Water Rights',
            'Vatten'=>'Water',
            'Växtförädlarrätts'=>'Plant Breeding Rights',
            'Växtskydds'=>'Plant Protection',
            'Verks'=>'Works',
            'Viltskade'=>'Game Damage',
            'Virkesmätnings'=>'Timber Measurement',
            'Vuxenutbildnings'=>'Adult Education',
            'Yrkestrafik'=>'Professional Traffic',
            'Yttrandefrihetsgrund'=>'Freedom of Expression',
            'Zoonos'=>'Zoonoses',
        ];
        //Tanslates the types
        $typeMemo = [];
        $types = [
            'Indelningslag'=>'Subdivision Act',
            'Lag'=>'Act',
            'Ag'=>'Act',

            'Tillkännagivande'=>'Announcement',
            'Kungörelse'=>'Announcement',

            'Balk'=>'Code',

            'Meddelande'=>'Communication',
            
            'Regeringens beslut'=>'Decision of the Government',
            'Beslut'=>'Decision',

            'Cirkulär'=>'Circular',

            'Anvisningar'=>'Instructions',
            'Instruktion'=>'Instruction',

            'Internationell'=>'International Treaty',
            'Folkrättsförordning'=>'International Law',

            'Departementsprotokoll'=>'Departmental Protocol',

            'Ordning'=>'Order',

            'Föreskrifter'=>'Regulation',
            'Föresrifter'=>'Regulation',
            'Föreskrift'=>'Regulation',
            'Förordningen'=>'Regulation',
            'Förordning'=>'Regulation',
            'Budgetförordning'=>'Budget Regulation',
            'Reglemente'=>'Regulation',

            'Stadga'=>'Statute',
        ];
        //Translates the origins
        $originMemo = [];
        $origins = [
            'Riksarkivets '=>'The National Archives',

            'Finansinspektionens '=>'The Financial Supervisory Authority',
            'Försäkringsinspektionens '=>'The Insurance Supervisory Authority',

            'Transportnämndens '=>'The Transport Board',

            'Statistiska centralbyråns '=>'The Central Bureau of Statistics',

            'Fartygsuttagningskommissionens '=>'The Ship Selection Commission',

            'Utrikesdepartementets '=>'The Department of Foreign Affairs',

            'Statens provningsanstalts '=>'The State Testing Institute',

            'Kungl. Maj:ts '=>'The King',
            'H.M. Konungens '=>'The King',

            'Riksrevisionsverkets '=>'The National Audit Office',
            'Riksgäldskontorets '=>'The Office of National Debt',
            'Rikgäldskontorets '=>'The Office of National Debt',
            'Riksgäldskontoretets '=>'The Office of National Debt',
            'Statskontorets '=>'The State Office',
        ];
        
        //Finds the limit
        $limit = $limit ?? json_decode(file_get_contents('https://www.riksdagen.se/api/data/?url=%2Fsv%2Fsok%2F%3Fdoktyp%3Dsfs'), true)['search']['numberOfPages']; echo $limit.'<br/>';
        //Gets the laws
        for ($page = $start; $page <= $limit; $page++) {
            //Gets the data from congress.gov API
            $laws = json_decode(file_get_contents('https://www.riksdagen.se/api/data/?url=%2Fsv%2Fsok%2F%3Fdoktyp%3Dsfs%26p%3D'.$page), true)['search']['documents'];
            foreach ($laws as $law) {
                //Interprets the data
                $status_dom->load($law['statusRow']);
                    $enactDate = $lastactDate = trim($status_dom->find('dd')[0]->plaintext ?? explode('-', $law['id'])[1].'-01-01');
                    $enforceDate = explode('Träder i kraft I:', explode('/', trim($law['summary'], ' /'))[0])[1] ?? $enactDate;
                $ID = $country.'-'.strtoupper(str_replace('-', '', $law['id']));
                //Gets the regime
                switch (true) {
                    case strtotime($enactDate) < strtotime('today'):
                        $regime = '{"sv":"Konungariket Sverige", "en":"The Kingdom of Sweden"}';
                    case strtotime($enactDate) < strtotime('6 June 1523'):
                        $regime = '{"sv":"Kalmarunionen", "en":"The Kalmar Union"}';
                    case strtotime($enactDate) < strtotime('17 June 1397'):
                        $regime = '{"sv":"Konungariket Sverige", "en":"The Kingdom of Sweden"}';
                        break;
                }
                //Gets the rest of the data
                $name = trim(strtr($law['title'], $sanitizeName), ' ;');
                $summary = strtr(trim($law['summary'] ?? 'NULL'), array("\n" => ' ', "\r" => ' ', "\t" => ' ', ' '=>' '));
                //Gets the type, topic and origin
                $type = $type0 = trim(end(explode('/ ', explode('19', explode('(', $name)[0])[0])));
                $topic = 'NULL'; $origin = 'NULL';
                if (isset($types[$type])) {
                    $type = $types[$type];
                } elseif (isset($typeMemo[$type])) {
                    $topic = $topicMemo[$type] ?? 'NULL'; echo 'USED: \''.$type0.'\'=>\''.$topic.'\'<br/>';
                    $origin = $originMemo[$type] ?? 'NULL'; echo 'USED: \''.$type0.'\'=>\''.$origin.'\'<br/>';
                    $type = $typeMemo[$type]; echo 'USED: \''.$type0.'\'=>\''.$type.'\'<br/>';
                } else {
                    //Detects the topics
                    foreach ($topics as $key => $val) {
                        if (str_contains($type0, $key)) {
                            //Gets the topic and adds it to the memo
                            $topicMemo[$type0] = $topic = '{"sv":"'.ucfirst(trim($key)).'", "en":"'.$val.'"}'; echo 'ADDED: \''.$type0.'\'=>\''.$topic.'\'<br/>';

                            //Changes the type
                            $type = str_replace($key, '', $type);
                            break;
                        }
                    }
                    //Detects the origins
                    foreach ($origins as $key => $val) {
                        if (str_contains($type0, $key)) {
                            //Gets the origin and adds it to the memo
                            $originMemo[$type0] = $origin = '{"sv":"'.ucfirst(trim($key)).'", "en":"'.$val.'"}'; echo 'ADDED: \''.$type0.'\'=>\''.$origin.'\'<br/>';

                            //Changes the type
                            $type = str_replace($key, '', $type);
                            break;
                        }
                    }
                    //Finalizes the type and adds it to the memo
                    $type = $types[ucfirst(trim($type))];
                    $typeMemo[$type0] = $type;
                    echo 'ADDED: \''.$type0.'\'=>\''.$type.'\'<br/>';
                }
                //Gets the rest of the values
                if (str_contains($name, 'ändring')) {$isAmmend = 1;} else {$isAmmend = 0;}
                $status = 'Valid';
                $source = $law['url'];
                $PDF = $law['attachedFileList']['files'][0]['url'] ?? 'NULL';

                //Makes sure there are no quotes in the title or summary
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}
                if (str_contains($summary, "'")) {$summary = str_replace("'", "’", $summary);}

                //JSONifies the title and source
                $name = '{"sv":"'.$name.'"}';
                $summary = isset($law['summary']) ? '\'{"sv":"'.$summary.'"}\'':'NULL';
                $source = '{"sv":"'.$source.'"}';
                $PDF = isset($law['attachedFileList']['files'][0]['url']) ? '{"sv":"'.$PDF.'"}':$PDF;

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `regime`, `name`, `summary`, `topic`, `type`, `isAmmend`, `status`, `origin`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$regime."', '".$name."', ".$summary.", ".$topic.", '".$type."', ".$isAmmend.", '".$status."', '".$origin."', '".$source."', ".$PDF.")"; echo $page.'. '.$SQL2.'<br/><br/>';
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