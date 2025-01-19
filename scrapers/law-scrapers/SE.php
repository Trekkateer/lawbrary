<html><body>
    <?php
        //Settings
        $test = true; $scraper = 'SE';
        $start = 0;//What page to start from
        $limit = null;//Total number of pages desired. Set to null to get number automatically

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php'; // '../' refers to the parent directory
        $status_dom = new simple_html_dom();
        $law_dom = new simple_html_dom();

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Sanitizes the name
        $sanitizeName = ["/rl/"=>"", "\n" => "", "\r" => " ", "\t" => " "];

        //Translates the topics
        $topicMemo = [];
        $topics = [
            'Abort'=>'Abortion',
            'Ackords'=>'Accords',
            'Ädellövskogs'=>'Deciduous Forests',
            'Åklagardata'=>'Prosecution Data',
            'Åklagar'=>'Prosecution',
            'Äktenskaps'=>'Marriages',
            'Aktiefonds'=>'Equity Funds',
            'Aktiekonto'=>'Stock Accounts',
            ' av andra staters anslutning till vissa konventioner'=>'On the accession of other states to certain conventions',
            ' av ändring av beloppsgränser i Montrealkonventionen'=>'On the amendment of monetary limits in the Montreal Convention',
            'Anläggnings'=>'Facilities',
            'Anställnings'=>'Employment',
            'Aktiebolags'=>'Stock Companies',
            'Alkohol'=>'Alcohol',
            'Apoteksdata'=>'Pharmacy Data',
            'Anslags'=>'Appropriations',
            'Arbetsmarknads'=>'The Labor Market',
            'Arbetsmiljö'=>'The Work Environment',
            'Arbetstids'=>'Working Hours',
            'Arbetsrättslig'=>'Labor Law',
            'Arkiv'=>'Archives',
            'Årsredovisnings'=>'Annual Reports',
            'Artskydds'=>'Species Protection',
            'Återlåne'=>'Repayment of Loans',
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
            'Berberis'=>'Berberis',
            'Beredskaps'=>'Emergency Preparedness',
            'Bevakningsföretags'=>'Security Companies',
            'Bevissäkrings'=>'Circuits of Evidence',
            'Biblioteks'=>'Libraries',
            'Bilavgas'=>'Car Exhaust',
            'Bilregister'=>'Car Registry',
            'Biobanks'=>'Biobanks',
            'Bidragsbrotts'=>'Donation Fraud',
            'Bilskrotnings'=>'Car Scrapping',
            'Bisjukdoms'=>'Bacterial Desease',
            'Bokförings'=>'Accounting',
            'Börsdata'=>'Stock Exchange Data',
            'Börs'=>'Stock Exchange',
            'Bötesverkställighets'=>'Penalty Enforcement',
            'Bostadsanvisnings'=>'Housing Instructions',
            'Bostadsbidrags'=>'Housing Permits',
            'Bostadsfinansierings'=>'Housing Finance',
            'Bostadsförvaltnings'=>'Housing Administration',
            'Bostadslåne'=>'Housing Loans',
            'Bostadsrätts'=>'Housing Rights',
            'Bostadssanerings'=>'Housing Renovations',
            'Brottsdata'=>'Crime Data',
            'Brottsskade'=>'Crime Damage',
            'Brotts'=>'Crime',
            'Budget'=>'Budget',
            'budget'=>'Budget',
            'Byggprodukt'=>'Construction Products',
            'Container'=>'Containers',
            'Data'=>'Data',
            'Delgivnings'=>'Service',
            'Deltids'=>'Part Time',
            'Departements'=>'Departments',
            'Diskriminerings'=>'Discrimination',
            'Djurskydds'=>'Animal Protection',
            'Djurstall'=>'Animal Stables',
            'Domkapitels'=>'Judgements',
            'Domstolsdata'=>'Court Data',
            'Donations'=>'Donations',
            'Drivmedels'=>'Fuel',
            'Dumpnings'=>'Dumping',
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
            'Expropriations'=>'Expropriations',
            'Familjebidrags'=>'Family Support',
            'Fängelse'=>'Prison',
            'Fartygsregister'=>'Ship Register',
            'Fartygssäkerhets'=>'Ship Safety',
            'Fastbränsle'=>'Solid Fuel',
            'Fastighetsbildnings'=>'Real Estate Development',
            'Fastighetsboks'=>'Real Estate Records',
            'Fastighetsdata'=>'Real Estate Data',
            'Fastighetsmäklar'=>'Real Estate Brokers',
            'Fastighetsregister'=>'Property Records',
            'Fastighetstaxerings'=>'Property Taxes',
            'Firma'=>'Companies',
            'fiskeloggboks'=>'Fishing Log Books',
            'Fiskeri'=>'Fisheries',
            'Fiske'=>'Fishing',
            'Fjärrkyle'=>'District Cooling',
            'Fjärrvärme'=>'District Heating',
            'Folkbokförings'=>'Census',
            'Folkhögskole'=>'Folk High School',
            'Folkomröstnings'=>'Referendums',
            'Folkrätts'=>'International Law',
            'Fonds'=>'Funds',
            'Fondkommissions'=>'Fund Commissions',
            'Föräldraledighets'=>'Parental Leave',
            'Föreningsbanks'=>'Association Banks',
            'Fordonsskatte'=>'Vehicle Taxes',
            'Fordonsskatt'=>'Vehicle Taxes',
            'Fordons'=>'Vehicles',
            'Förköps'=>'Advance Purchases',
            ' om ändring av vissa skadelivräntor'=>'On the amendment of certain injury pensions',
            ' om ändring i förordningen'=>'On Amendments to Regulations',
            ' om belopp för bidrag till utbildning av utlandssvenska barn och ungdomar för bidragsåret 2025'=>'on the amount of grants for the education of Swedish children and young people living abroad for the financial year 2025',
            ' om gällande inlåningsränta i ungdomsbosparandet'=>'On the current deposit rate in youth savings',
            ' om gällande räntesats för allemanssparandet'=>'On the current interest rate for public savings',
            ' om nationella järnvägssystem'=>'On National Railway Systems',
            ' om preventiva vistelseförbud'=>'On Preventive Residence Bans',
            ' om upphävande av Rikspolisstyrelsens föreskrifter och allmänna råd'=>'On the repeal of the National Police Agency’s regulations and general advice',
            ' om upphävande av Skatteverkets föreskrifter'=>'On the repeal of the Tax Agency’s regulations',
            ' om upphävande av verkets föreskrifter'=>'On the repleal of the agency’s regulations',
            ' med kompletterande bestämmelser till EU:s förordning om digitala tjänster'=>'On supplementary provisions to the EU regulation on digital services',
            ' med instruktion för Myndigheten för press, radio och tv'=>'On the instructions for the Authority of the Press, Radio and Television',
            ' om skolindex för 2025'=>'On the 2025 school index',
            ' om statligt stöd till avskiljning, transport och geologisk lagring av koldioxid med biogent ursprung'=>'On state aid for the separation, transport and geological storage of carbon dioxide of biogenically origin',
            ' om statligt stöd för vissa åtgärder som syftar till att bevara eller återställa biologisk mångfald'=>'On state aid for certain measures aimed at preserving and restoring biodiversity',
            ' om upphävande av Statistiska centralbyråns föreskrifter'=>'On the repeal of the regulations of the Central Bureau of Statistics',
            'Förbättringslåne'=>'Improvement Loans',
            'Författningssamlings'=>'The Constituent Assembly',
            'Förfogande'=>'Availability',
            'Förmånsrätts'=>'Preferential Rights',
            'Förmynderskaps'=>'Guardianship',
            'Försäkringsavtals'=>'Insurance Contracts',
            'Försäkringskassans '=>'The Insurance Fund',
            'Försäkringsrörelse'=>'Insurance Businesses',
            'Församlings'=>'Parishes',
            'Förvaltningsprocess'=>'Administrative Processes',
            'Förvaltnings'=>'Administration',
            'Frihandels'=>'Free Trade',
            'Frihets'=>'Freedom',
            'Frivårds'=>'Probation',
            'Garanti'=>'Guarantees',
            'Gravationsbevis'=>'Certificate of Engraving',
            'Gruv'=>'Mines',
            'Grundskole'=>'Elementary School',
            'Gymnasie'=>'High School',
            'Häktes'=>'Arrests',
            'Hälsoskydds'=>'Health Protection',
            'Hälso- och sjukvårds'=>'Healthcare',
            'Handelskammar'=>'The Chambers of Commerce',
            'Handelsregister'=>'Commercial Records',
            'Handräcknings'=>'Contracts',
            'Havsmiljö'=>'The Marine Environment',
            'Havsplanerings'=>'Maritime Planning',
            'Hemförsäljnings'=>'Home Sales',
            'Hemvärns'=>'Home Guard',
            'Högskole'=>'College',
            'Hyresförhandlings'=>'Rent Negotiation',
            'Indrivnings'=>'Recovery',
            'Industriutsläpps'=>'Industrial Emissions',
            'Inkasso'=>'Debt Collection',
            'Inkomstskatte'=>'Income Taxes',
            'Inrättande'=>'Establishments',
            'Inregräns'=>'Internal Borders',
            'Insiderstraff'=>'Insider Penalties',
            'Insider'=>'Insiders',
            'Inskrivningsregister'=>'Enrollment Records',
            'Inskrivnings'=>'Enrollment',
            'Installatörs'=>'Installers',
            'Internrevisions'=>'Internal Audits',
            'Isbrytar'=>'Icebreakers',
            'Jakttids'=>'The Hunting Season',
            'Jakt'=>'Hunting',
            'Jämställdhets'=>'Gender Equality',
            'Järnvägsmarknads'=>'The Railway Market',
            'Järnvägssäkerhets'=>'Railway Safety',
            'Järnvägsteknik'=>'Railway Engineering',
            'Järnvägstrafik'=>'Railway Traffic',
            'Järnvägs'=>'Railways',
            'Jordbruksbokförings'=>'Agricultural Accounting',
            'Jordförvärvs'=>'Land Acquisition',
            'Jorda'=>'Land',
            'Kameraövervaknings'=>'Camera Surveillance',
            'Kamerabevaknings'=>'Camera Surveillance',
            'Kapitalförsörjnings'=>'Provision of Capital',
            'Karantäns'=>'Quarantine',
            'Kartsekretess'=>'Map Secrecy',
            'Kasino'=>'Casinos',
            'Klampnings' => 'Clamping',
            'Klimatrapporterings'=>'Climate Reporting',
            'Klimat'=>'Climate',
            'Kommissions'=>'Commissions',
            'Kommitté'=>'Committee',
            'Kommunalförbunds'=>'Municipal Associations',
            'Kommunal'=>'Municipal',
            'Konkurrensskade'=>'Damage from Competition',
            'Konkurrens'=>'Competition',
            'Konkurs'=>'Bankruptcy',
            'Konsumentförsäkrings'=>'Consumer Insurance',
            'Konsumentköp'=>'Consumer Purchases',
            'Konsumentkredit'=>'Consumer Credit',
            'Konsumenttjänst'=>'Consumer Services',
            'Kontinentalsockels'=>'Continental Shelves',
            'Kontoförings'=>'Accounting',
            'Köp'=>'Purchases',
            'Körkorts'=>'Driver’s Licenses',
            'Köttbesiktnings'=>'Meat Inspection',
            'Kreditupplysnings'=>'Credit Information',
            'Krigsveterinär'=>'War Veterinarians',
            'Kriminalregister'=>'Criminal Records',
            'Kriminalvårdsdata'=>'Criminal Justice Data',
            'Kulturmiljö'=>'The Cultural Environment',
            'Kupongskatte'=>'Coupon Taxes',
            'Kustbevakningsdata'=>'Coast Guard Data',
            'Kustbevaknings'=>'Coast Guard',
            'Kyrklig '=>'Church',
            'Kyrkofonds'=>'Church Funds',
            'Kyrko'=>'Church',
            'Läkarvårds'=>'Medical Care',
            'Läkemedels'=>'Medicines',
            'Lantmäteri'=>'Surveying',
            'Laponia'=>'Lapland',
            'Ledningsrätts'=>'Management Rights',
            'Livsmedels'=>'Food',
            'Lokalradio'=>'Local Radio',
            'Lönegaranti'=>'Wage Guarantees',
            'Lotteri'=>'The Lottery',
            'Luftfarts'=>'Aviation',
            'Luftkvalitets'=>'Air Quality',
            'Luftvårds'=>'Air Traffic Control',
            'Marknadsförings'=>'Marketing Activities',
            'Marknadsmissbruks'=>'Market Abuse',
            'Mätnings'=>'Measuring',
            'Medborgarskaps'=>'Citezenship',
            'Mediestöds'=>'Media Support',
            'Mervärdesskatte'=>'Value Added Tax',
            'Militära vägtrafik'=>'Military Road Traffic',
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
            'Narkotika'=>'Drugs',
            'narkotika'=>'Drugs',
            'Närradio'=>'Local Radio',
            'Nationalparks'=>'National Parks',
            'Nationalstadsparks'=>'National City Parks',
            'Naturgas'=>'Natural Gas',
            'Naturvårds'=>'Conservation of Nature',
            'Notarie'=>'Notaries',
            'Nybyggnadslåne'=>'New Construction Loans',
            'Obduktions'=>'Autopsies',
            'Offentlighets- och sekretess'=>'Publicity and Confidentiality',
            'Officers'=>'Officers',
            'Oljekris'=>'The Oil Crisis',
            'Ombyggnadslåne'=>'Reconstruction Loans',
            'Omplacerings'=>'Relocations',
            'Ordningsbots'=>'Order Fines',
            'Ordningsvakts'=>'Law Enforcement Officers',
            'Ordnings'=>'Orders',
            'Överförmyndar'=>'Gaurdianship',
            'Paketrese'=>'Package Transportation',
            'Pantbanks'=>'Pawn Shops',
            'Pass'=>'Passports',
            'Patent'=>'Patents',
            'Patientdata'=>'Patient Data',
            'Patientjournal'=>'Patient Records',
            'Patientsäkerhets'=>'Patient Safety',
            'Patientskade'=>'Patient Injury',
            'Patient'=>'Patients',
            'Permutations'=>'Permutations',
            'Personalföreträdar'=>'Personnel Representatives',
            'Personalkontroll'=>'Personnel Control',
            'Personuppgifts'=>'Personal Data',
            'Plan- och bygg'=>'Planning and Construction',
            'Polisdata'=>'Police Data',
            'Polisregister'=>'Police Records',
            'Polisutbildnings'=>'Police Training',
            'Polis'=>'Police',
            'Prästanställnings'=>'Clerical Employments',
            'Preskriptions'=>'Prescriptions',
            'Presstöds'=>'Press Support',
            'Prisinformations'=>'Price Information',
            'Prisreglerings'=>'Price Regulation',
            'Pris'=>'Price',
            'Privatskol'=>'Private School',
            'Process'=>'Processes',
            'Produktansvars'=>'Product Liability',
            'Produktsäkerhets'=>'Product Safety',
            'Prokura'=>'Procurement',
            'Protokolls'=>'Protocols',
            'Räddningstjänst'=>'Rescue Services',
            'Radio- och tv-'=>'Radio and TV',
            'Radio- och TV-'=>'Radio and TV',
            'Radioansvarighets'=>'Radio Responsibility',
            'Radiostörnings'=>'Radio Interference',
            'Radioutrustnings'=>'Radio Equipment',
            'Radio'=>'Radio',
            'Ransonerings'=>'Rationing',
            'Räntelåne'=>'Interest Loans',
            'räntelåne'=>'Interest Loans',
            'Ränte'=>'Interest Rates',
            'Rättshjälps'=>'Legal Aid',
            'Rättsinformations'=>'Legal Information',
            'Register'=>'Records',
            'Rehabiliterings'=>'Rehabilitation',
            'Renhållnings'=>'Cleaning',
            'Rennärings'=>'Reindeer Keeping',
            'Resegaranti'=>'Travel Insurance',
            'Reservbefäls'=>'Reserve Commanders',
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
            'Sekretess'=>'Secrecy',
            'Semester'=>'Holidays',
            'Spel'=>'Games',
            'Språk'=>'Language',
            'Sjöarbetstids'=>'Maritime Working Hours',
            'Sjöförklarings'=>'Maritime Declarations',
            'Sjöfartsstyrelsens '=>'Maritime Administration',
            'Sjömans'=>'Seamanship', 
            'Sjötrafik'=>'Maritime Traffic',
            'Sjö'=>'Maritime',
            'Sjukrese'=>'Medical Leave',
            'Skadestånds'=>'Indemnification',
            'Skattebetalnings'=>'Tax Payments',
            'Skattebrottsdata'=>'Tax Offense Data',
            'Skattebrotts'=>'Tax Offenses',
            'Skatteförfarande'=>'Tax Procedure',
            'Skatteregister'=>'Tax Records',
            'Skatte'=>'Taxes',
            'Skogsvårds'=>'Forestry',
            'Skol'=>'School',
            'Skuldsanerings'=>'Debt Restructuring',
            'Skyddsrums'=>'Protected Areas',
            'Skydds'=>'Protection',
            'Smittskydds'=>'Outbreak Prevention',
            'Socialavgifts'=>'Social Security Contributions',
            'Socialförsäkringsregister'=>'Social Insurance Records',
            'Socialförsäkrings'=>'Social Insurance',
            'Socialtjänst'=>'Social Services',
            'Sparbanks'=>'Savings Banks',
            'Specialskole'=>'Special School',
            'Stadsregister'=>'City Register',
            'Starkströms'=>'High Voltages',
            'Statens bostadskreditnämnds '=>'The State Housing Credit Board',
            'Statsflygs'=>'State Aviation',
            'Statskalender'=>'State Calendar',
            'Steriliserings'=>'Sterilization',
            'Stiftelse'=>'Foundation',
            'Strafföreläggande'=>'Penalty Orders',
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
            'Terrängkörnings'=>'Off-road Driving',
            'Terrängtrafik'=>'Off-road Traffic',
            'Terroristbrotts'=>'Terrorist Crimes',
            'Tidsbegränsnings'=>'Time Restrictions',
            'Tillträdes'=>'Access',
            'Tjänstebrevs'=>'Business Letters',
            'tjänstebrevs'=>'Business Letters',
            'Tjänsteexport'=>'Export of Services',
            'Tjänstledighets'=>'Leave of Absence',
            'Tobaks'=>'Tobacco',
            'Tomträttsboks'=>'Land Title Register',
            'Totalförsvarsdata'=>'Total Defence Data',
            'Trafikförsäkrings'=>'Traffic Insurance',
            'Trafikskade'=>'Traffic Injury',
            'Trafik'=>'Traffic',
            'Transplantations'=>'Transplantations',
            'Tullbefogenhets'=>'The Costoms Authority',
            'Tullbrottsdata'=>'Costoms Violation Data',
            'Tullbrotts'=>'Costoms Violations',
            'Tullfrihets'=>'Costoms Exemptions',
            'Tullregister'=>'Costoms Records',
            'Tulltaxe'=>'Costoms Taxes',
            'Tull'=>'Costoms',
            'Tul'=>'Costoms',
            'Turistvagns'=>'Tourist Carriages',
            'Uppbörds'=>'Collection',
            'Upphandlings'=>'Procurement',
            'Upphovsrätts'=>'Copyrights',
            'upphovsrätts'=>'Copyrights',
            'Uppskovs'=>'Defferals',
            'Utlandsrese'=>'International Travel',
            'Utlänningsdata'=>'Foreigners Data',
            'Utlännings'=>'Foreigners',
            'Utsädes'=>'Seeds',
            'Utsökningsregister'=>'Search Records',
            'Utsöknings'=>'Searches',
            'Vägför'=>'Road Transportation',
            'Vägmärkes'=>'Road Signs',
            'Vägsäkerhets'=>'Road Safety',
            'Vägtrafikdata'=>'Road Traffic Data',
            'Vägtrafikskatte'=>'Road Traffic Taxes',
            'Vägtrafik'=>'Road Traffic',
            'Väg'=>'Roads',
            'Vakts'=>'Gaurds',
            'Validerings'=>'Validations',
            'Val'=>'Election',
            'Vapen'=>'Weapons',
            'Värnpliktsförmåns'=>'Benefits for Military Service',
            'Värnpliktsutbildnings'=>'Compulsory Military Training',
            'Varumärkes'=>'Branded',
            'Vattenboks'=>'Water Books',
            'Vattenförvaltnings'=>'Water Management',
            'Vattenrätts'=>'Water Rights',
            'Vatten'=>'Water',
            'Växtförädlarrätts'=>'Plant Breeding Rights',
            'Växtskydds'=>'Plant Protection',
            'Verks'=>'Works',
            'Veterinärtaxe'=>'Veterinary Taxes',
            'Viltskade'=>'Game Damage',
            'Virkesmätnings'=>'Timber Measurement',
            'Vuxenutbildnings'=>'Adult Education',
            'Yrkestrafik'=>'Professional Traffic',
            'Yttrandefrihetsgrund'=>'Freedom of Expression',
            'Zoonos'=>'Zoonoses',
            'Post'=>'Mail',//Post needs to be last to avoid confusing it with the post office origin
        ];
        //Translates the origins
        $originMemo = [];
        $origins = [
            'Fiskeristyrelsens '=>'The Swedish Fisheries Agency',
            'Riksskatteverkets '=>'The National Tax Agency',

            'Riksarkivets och krigsarkivets '=>'The National Archives and War Archives',
            'Riksarkivets '=>'The National Archives',

            'CECA-'=>'ECSC',

            'Finansinspektionens '=>'The Financial Supervisory Authority',
            'Försäkringsinspektionens '=>'The Insurance Supervisory Authority',

            'Transportnämndens '=>'The Transport Board',
            'Socialstyrelsens '=>'The National Board of Health',
            'Statens förhandlingsnämnds '=>'The State Negotiation Board',

            'Statistiska centralbyråns '=>'The Central Bureau of Statistics',

            'Fartygsuttagningskommissionens '=>'The Ship Selection Commission',

            'Utrikesdepartementets '=>'The Department of Foreign Affairs',

            'Statens provningsanstalts '=>'The State Testing Institute',

            'Totalförsvarets '=>'The Total Defense Force',

            'Kammarkollegiets '=>'The Kammarkollegiets',

            'Kungl. Maj:t '=>'The King’s Council',
            'Kungl Maj:ts '=>'The King’s Council',
            'Kungl. Maj:ts '=>'The King’s Council',
            'Kungl. Maj.ts '=>'The King’s Council',
            'Kungl. Majt:s '=>'The King’s Council',
            'H.M. Konungens '=>'The King',

            'Riksrevisionsverkets '=>'The National Audit Office',
            'Riksgäldskontorets '=>'The Office of National Debt',
            'Rikgäldskontorets '=>'The Office of National Debt',
            'Riksgäldskontoretets '=>'The Office of National Debt',
            'Statskontorets '=>'The State Office',

            'Postverkets '=>'The Post Office',
        ];
        //Tanslates the types
        $typeMemo = [];
        $types = [
            'Indelningslag'=>'Subdivision Act',
            'Lag'=>'Act',
            'Beredskapslag'=>'Contingency Law',
            'Ag'=>'Act',

            'Tillkännagivande'=>'Announcement',
            'Kungörelsen'=>'Announcement',
            'Kungörelse'=>'Announcement',
            'Kungörandeförordning'=>'Legal-Announcement',
            'Ordenskungörelse'=>'Order-Announcement',

            'Balk'=>'Code',

            'Cirkulär'=>'Circular',

            'Meddelande'=>'Communication',
            
            'Regeringens beslut'=>'Decision of the Government',
            'Beslut'=>'Decision',

            'Förteckningar'=>'List',

            'Anvisningar'=>'Instructions',
            'Instruktion'=>'Instruction',

            'Internationell förordning'=>'International Regulation',
            'Folkrättsförordning'=>'International Law',
            'Internationell'=>'International Treaty',

            'Brev'=>'Letter',

            'Departementsprotokoll'=>'Departmental Protocol',

            'Ordning'=>'Order',

            'Föreskrifter'=>'Regulation',
            'Föresrifter'=>'Regulation',
            'Föreskrift'=>'Regulation',
            'Förordningen'=>'Regulation',
            'Förordning'=>'Regulation',
            'Reglemente'=>'Regulation',

            'Stadga'=>'Statute',

            'Taxa'=>'Tariff'
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
                    $enforceDate = isset($law['summary']) && str_contains($law['summary'], 'Träder i kraft I:') ? explode('Träder i kraft I:', explode('/', trim($law['summary'], ' /'))[0])[1]:$enactDate;
                $ID = $scraper.':'.strtoupper(str_replace('-', '', $law['id']));
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
                $name = trim(str_replace(array_keys($sanitizeName), array_values($sanitizeName), $law['title']), ' -;,');
                $summary = trim(strtr(trim($law['summary'] ?? 'NULL'), array("\n" => " ", "\r" => " ", "\t" => " ")), ' -;,');
                //Gets the type, topic and origin
                $type = $typeLine = trim(end(explode('/', explode('19', explode('(', $name)[0])[0])), ' ;');
                $topic = 'NULL'; $origin = 'NULL';
                if (isset($types[$type])) {
                    $type = $types[$type];
                } elseif (isset($typeMemo[$type])) {
                    $topic = $topicMemo[$type] ?? 'NULL'; echo "USED: '".$typeLine."'=>'".$topic."'<br/>";
                    $origin = $originMemo[$type] ?? 'NULL'; echo "USED: '".$typeLine."'=>'".$origin."'<br/>";
                    $type = $typeMemo[$type]; echo "USED: '".$typeLine."'=>'".$type."'<br/>";
                } else {
                    //Detects the topics
                    foreach ($topics as $key => $val) {
                        if (str_contains($typeLine, $key)) {
                            //Gets the topic and adds it to the memo
                            $topicMemo[$typeLine] = $topic = '{"sv":"'.ucfirst(trim($key)).'", "en":"'.$val.'"}'; echo "ADDED: '".$typeLine."'=>'".$topic."'<br/>";
                            //Changes the type
                            $type = ucfirst(trim(str_replace($key, '', $type), "\n "));
                            break;
                        }
                    }
                    //Detects the origins
                    foreach ($origins as $key => $val) {
                        if (str_contains($typeLine, $key)) {
                            //Gets the origin and adds it to the memo
                            $originMemo[$typeLine] = $origin = '{"sv":"'.ucfirst(trim($key)).'", "en":"'.$val.'"}'; echo "ADDED: '".$typeLine."'=>'".$origin."'<br/>";
                            //Changes the type
                            $type = ucfirst(trim(str_replace($key, '', $type), "\n "));
                            break;
                        }
                    }
                    //Finalizes the type and adds it to the memo
                    $type = $types[$type];
                    $typeMemo[$typeLine] = $type;
                    echo "ADDED: '<span>".$typeLine."</span>'=>'<span>".$type."</span>'<br/>";
                }
                //Gets the rest of the values
                $status = 'Valid';
                $source = $law['url'];
                $PDF = $law['attachedFileList']['files'][0]['url'] ?? 'NULL';

                //Makes sure there are no quotes in the title or summary
                $name = strtr($name, array(" '"=>" ‘", "'"=>"’"));
                $summary = strtr($summary, array(" '"=>" ‘", "'"=>"’"));

                //JSONifies the title and source
                $name = '{"sv":"'.$name.'"}';
                $summary = isset($law['summary']) ? '\'{"sv":"'.$summary.'"}\'':'NULL';
                $source = '{"sv":"'.$source.'"}';
                $PDF = isset($law['attachedFileList']['files'][0]['url']) ? '{"sv":"'.$PDF.'"}':$PDF;

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `origin`, `summary`, `topic`, `type`, `status`, `source`, `PDF`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', ".$origin.", ".$summary.", ".$topic.", '".$type."', '".$status."', '".$source."', ".$PDF.")"; echo $page.'. '.$SQL2.'<br/><br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>