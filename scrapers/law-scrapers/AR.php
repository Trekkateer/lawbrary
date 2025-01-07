<html>
<head>
    <?php //Some functions we will need
        function console_log($output, $with_script_tags = true) {
            $js_code = 'console.log('.json_encode($output, JSON_HEX_TAG).');';
            if ($with_script_tags) {
                $js_code = '<script>'.$js_code.'</script>';
            }
            echo $js_code;
        }
    ?>
</head>
<body>
    <?php
        //Settings
        $test = true; $scraper = 'AR';
        $start = 0;//Which law to start from
        $step = 1000;//How many laws per page
        $limit = 100000;//Total number of laws desired. 
        /*Max is 100000 due to broken API*/

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select legal database");

        //Connect to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select content database");

        //Clears the table(s)
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}
        $SQL10 = "SELECT `ID` FROM `dbupm726ysc0bg`.`divisions` WHERE `parent` = '".$scraper."'";
        $result10 = $conn2->query($SQL10);
        while ($row10 = $result10->fetch_assoc()) {
            $SQL11 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($row10['ID'])."`"; echo $SQL11.'<br/><br/>';
            if (!$test) {$conn->query($SQL11);}
        }

        //Translates the provinces
        $provinces = array(
            'AR' => 'AR',
            '' => 'AR',
            'Buenos Aires' => 'AR-B',
            'AR-B' => 'AR-B',
            'Ciudad Autónoma de Buenos Aires' => 'AR-C',
            'AR-C' => 'AR-C',
            'Catamarca' => 'AR-K',
            'AR-K' => 'AR-K',
            'Chaco' => 'AR-H',
            'AR-H' => 'AR-H',
            'Chubut' => 'AR-U',
            'AR-U' => 'AR-U',
            'Córdoba' => 'AR-X',
            'AR-X' => 'AR-X',
            'Corrientes' => 'AR-W',
            'AR-W' => 'AR-W',
            'Entre Ríos' => 'AR-E',
            'AR-E' => 'AR-E',
            'Formosa' => 'AR-P',
            'AR-P' => 'AR-P',
            'Jujuy' => 'AR-Y',
            'AR-Y' => 'AR-Y',
            'La Pampa' => 'AR-L',
            'AR-L' => 'AR-L',
            'La Rioja' => 'AR-F',
            'AR-F' => 'AR-F',
            'Mendoza' => 'AR-M',
            'AR-M' => 'AR-M',
            'Misiones' => 'AR-N',
            'AR-N' => 'AR-N',
            'Neuquén' => 'AR-Q',
            'AR-Q' => 'AR-Q',
            'Río Negro' => 'AR-R',
            'AR-R' => 'AR-R',
            'Salta' => 'AR-A',
            'AR-A' => 'AR-A',
            'San Juan' => 'AR-J',
            'AR-J' => 'AR-J',
            'San Luis' => 'AR-D',
            'AR-D' => 'AR-D',
            'Santa Cruz' => 'AR-Z',
            'AR-Z' => 'AR-Z',
            'Santa Fe' => 'AR-S',
            'AR-S' => 'AR-S',
            'Santiago del Estero' => 'AR-G',
            'AR-G' => 'AR-G',
            'Tierra del Fuego' => 'AR-V',
            'AR-V' => 'AR-V',
            'Tucumán' => 'AR-T',
            'AR-T' => 'AR-T',
        );
        //Translates the types
        $types = array(
            'ACO' => 'Agreement',
            'CAD' => 'Code',
            'CCA' => 'Code',
            'CCC' => 'Code',
            'CCN' => 'Code',
            'CEL' => 'Code',
            'CFP' => 'Code',
            'CMI' => 'Code',
            'CPC' => 'Code',
            'CPE' => 'Code',
            'CPM' => 'Code',
            'CPP' => 'Code',
            'CPT' => 'Code',
            'CON' => 'Constitution',
            'DEC' => 'Decree',
            'DNU' => 'Emergency Decree',
            'DLE' => 'Legal Decree',
            'DOR' => 'Ordinal Decree',
            'DAN' => 'Decision',
            'NJF' => 'De Facto Legal Norm',
            'DIS' => 'Disposition',
            'LEY' => 'Law',
            'REA' => 'Resolution',
            'RES' => 'Resolution',
            'RSC' => 'Resolution',
            'TOR' => 'Text Order',
            'TOD' => 'Text Order',
            'TRA' => 'Treaty'
        );
        //Translates the statuses
        $statuses = array(
            'Vigente, de alcance general' => 'In Force',
            'Individual, Solo Modificatoria o Sin Eficacia' => 'In Force',
            'Derogada' => 'Repealed',
            'Vetada' => 'Vetoed',
            'A' => 'Authorized'
        );
        //Translates the origins !!Needs to be finished
        $origins = array(
            'NULL' => 'NULL',

            'Administración Federal de Ingresos Públicos' => 'The Federal Public Revenue Administration',
            'Administración General de Puertos'           => 'The General Port Administration',
            'Administración Nacional de Aviación Civil'   => 'The National Civil Aviation Administration',
            'Administración Nacional de Medicamentos, Alimentos y Tecnología Médica' => 'The National Administration of Drugs, Food and Medical Technology',
            'Administración Nacional de Seguridad Social' => 'The National Social Security Administration',
            'Administración de Parques Nacionales'        => 'The Administration of National Parks',
            'Administración de Programas Especiales'      => 'The Administration of Special Programs',

            'Agencia de Administración de Bienes del Estado' => 'The State Property Administration Agency',
            'Agencia Federal de Inteligencia'              => 'The Federal Intelligence Agency',
            'Agencia de Planificación'                     => 'The Planning Agency',
            'Agencia Nacional de Materiales Controlados' => 'The National Agency for Controlled Materials',
            'Agencia Nacional de Seguridad Vial' => 'The National Road Safety Agency',

            'Autoridad de Cuenca Matanza Riachuelo' => 'The Matanza Riachuelo Basin Authority',
            'Autoridad Federal de Servicios de Comunicación Audiovisual' => 'The Federal Authority for Audiovisual Communication Services',

            'Banco Central de la República Argentina' => 'The Central Bank of the Argentine Republic',

            'Jefatura de Gabinete de Ministros' => 'The Chief of the Cabinet of Ministers',
            
            'Comisión Administradora del Río de La Plata' => 'The Administrative Commission of the La Plata River',
            'Comisión Administradora del Río Uruguay' => 'The Administrative Commission of the Uruguay River',
            'Comisión Arbitral de Convenio Multilateral del 18.8.77' => 'The Arbitration Commission of the Multilateral Convention of 18.8.77',
            'Comisión Bicameral de Monitoreo e Implementación del Nuevo Código Procesal Penal de la Nación' => 'The Bicameral Commission for Monitoring and Implementing the New Criminal Procedure Code of the Nation',
            'Comisión Federal de Impuestos' => 'The Federal Tax Commission',
            'Comisión Nacional de Comunicaciones' => 'The National Communications Commission',
            'Comisión Nacional para los Refugiados' => 'The National Commission for Refugees',
            'Comisión Nacional de Regulación del Transporte' => 'The National Transport Regulation Commission',
            'Comisión Nacional de Trabajo Agrario' => 'The National Agricultural Labor Commission',
            'Comisión Nacional de Trabajo en Casas Particulares' => 'The National Commission for Work in Private Homes',
            'Comisión Nacional de Trabajos en Casas Particulares' => 'The National Commission for Work in Private Homes',
            'Comisión Nacional de Valores' => 'The National Securities Commission',
            'Comisión de Planificación y Coordinación Estratégica del Plan Nacional de Inversiones Hidrocarburíferas' => 'The Planning and Strategic Coordination Commission of the National Hydrocarbon Investment Plan',

            'Congreso de la Nación' => 'The National Congress',

            'Consejo Federal Pesquero' => 'The Federal Council of Fisheries',
            'Consejo Gremial de Enseñanza Privada' => 'The Council of the Private Teaching Union',
            'Consejo de la Magistratura' => 'The Council of the Judiciary',
            'Consejo Profesional de Ingeniería Agronómica' => 'The Professional Council of Agricultural Engineering',
            'Consejo Profesional de Ingeniería Industrial' => 'The Professional Council of Industrial Engineering',
            'Consejo del Registro Nacional de Constructores y Firmas Consultoras de Obras Públicas' => 'The Council of the National Register of Builders and Public Works Consulting Firms',
            'Consejo Nacional del Empleo, la Productividad y el Salario Mínimo, Vital y Móvil' => 'The National Council for Employment, Productivity and the Minimum, Vital and Mobile Salary',
            'Consejo Superior de la Universidad de Buenos Aires' => 'The Superior Council of the University of Buenos Aires',

            'Corte Suprema de Justicia de la Nación' => 'The Supreme Court of Justice of the Nation',

            'Defensoría del Público de Servicios de Comunicación Audiovisual' => 'The Public Defender of Audiovisual Communication Services',

            'Dirección General Infraestructura y Servicios Aeroportuarios' => 'The General Directorate for Airport Infrastructure and Services',
            'Dirección de Regulación del Sistema Nacional Integrado de Venta y Distribución de Diarios, Revistas y Afines' => 'The Directorate of Regulation of the National Integrated System for the Sale and Distribution of Newspapers, Magazines and the Like',
            'Dirección Nacional de Asociaciones Sindicales' => 'The National Directorate of Trade Union Associations',
            'Dirección Nacional de Comercio Interior'       => 'The National Directorate of Internal Commerce',
            'Dirección Nacional de Compras Públicas y Desarrollo de Proveedores' => 'The National Directorate of Public Procurement and Supplier Development',
            'Dirección Nacional de Defensa del Consumidor'  => 'The National Directorate of Consumer Defense',
            'Dirección Nacional de Derechos del Autor'      => 'The National Directorate of Copyright',
            'Dirección Nacional Electoral'                  => 'The National Electoral Directorate',
            'Dirección Nacional del Registro Nacional de las Personas' => 'The National Directorate of the National Registry of Persons',
            'Dirección Nacional del Registro Oficial'       => 'The National Directorate of the Official Register',
            'Dirección Nacional de Mediación y Métodos Participativos de Resolución de Conflictos' => 'The National Directorate of Mediation and Participatory Methods for Conflict Resolution',
            'Dirección Nacional de Migraciones'               => 'The National Directorate of Migrations',
            'Dirección Nacional de Orientación y Formación Profesional' => 'The National Directorate for Vocational Guidance and Training',
            'Dirección Nacional de Protección de Datos Personales' => 'The National Directorate of Personal Data Protection',
            'Dirección Nacional del Registro Nacional de Tierras Rurales' => 'The National Directorate of the National Registry of Rural Lands',
            'Dirección Nacional del Registro de la Propiedad Automotor' => 'The National Directorate of Motor Vehicle Property Registries',
            'Dirección Nacional de los Registros Nacionales de la Propiedad del Automotor y de Créditos Prendarios' => 'The National Directorate of the National Motor Vehicle Ownership and Credit Registries',
            'Dirección Nacional de Relaciones con Organizaciones de la Sociedad' => 'The National Directorate of Relations with Social Organizations',
            'Dirección Nacional de Transporte Aéreo'        => 'The National Directorate of Air Transport',
            'Dirección Nacional de Vialidad'                => 'The National Highway Directorate',

            'Subdirección Nacional Electoral' => 'The National Electoral Subdirectorate',
            'Subdirección Nacional de los Registros Nacionales de la Propiedad del Automotor y de Créditos Prendarios' => 'The National Subdirectorate of the National Motor Vehicle Ownership and Credit Registries',

            'Ente Regulador de Agua y Saneamiento'       => 'The Water and Sanitation Regulatory Entity',
            'Ente Nacional de Comunicaciones'            => 'The National Communications Entity',
            'Ente Nacional Regulador de la Electricidad' => 'The National Electricity Regulatory Entity',
            'Ente Nacional Regulador del Gas'            => 'The National Gas Regulatory Entity',

            'Inspección General de Justicia' => 'The General Inspectorate of Justice',

            'Instituto de Estadística y Registro de la Construcción' => 'The Institute of Statistics and Construction Registry',
            'Instituto Nacional de Asociativismo y Economía Social' => 'The National Institute of Associativism and Social Economy',
            'Instituto Nacional de Asuntos Indígenas' => 'The National Institute of Indigenous Affairs',
            'Instituto Nacional Central Único Coordinador de Ablación e Implante' => 'The National Central Institute Coordinator of Ablation and Implant',
            'Instituto Nacional de Cine y Artes Audiovisuales' => 'The National Institute of Cinema and Audiovisual Arts',
            'Instituto Nacional contra la Discriminación, la Xenofobia y el Racismo' => 'The National Institute against Discrimination, Xenophobia and Racism',
            'Instituto Nacional de Educación Tecnológica' => 'The National Institute of Technological Education',
            'Instituto Nacional de Música' => 'The National Institute of Music',
            'Instituto Nacional de la Propiedad Industrial' => 'The National Institute of Industrial Property',
            'Instituto Nacional de Semillas' => 'The National Seed Institute',
            'Instituto Nacional de Servicios Sociales para Jubilados y Pensionados' => 'The National Institute of Social Services for Retirees and Pensioners',
            'Instituto Nacional de Tecnología Industrial' => 'The National Institute of Industrial Technology',
            'Instituto Nacional de Vitivinicultura' => 'The National Institute of Viticulture',
            'Instituto Nacional de la Yerba Mate' => 'The National Institute of Yerba Mate',

            'Gerencia de Prevención' => 'The Prevention Management',

            'Honorable Cámara de Diputados de la Nación' => 'The Honorable Chamber of Deputies of the Nation',

            'Ministerio de Agricultura'          => 'The Ministry of Agriculture',
            'Ministerio de Agricultura, Ganadería y Pesca' => 'The Ministry of Agriculture, Livestock and Fisheries',
            'Ministerio de Agroindustria'        => 'The Ministry of Agroindustry',
            'Ministerio de Ambiente y Desarrollo Sustentable' => 'The Ministry of the Environment and Sustainable Development',
            'Ministerio de Ciencia y Tecnología e Innovación Productiva' => 'The Ministry of Science, Technology and Productive Innovation',
            'Ministerio de Comunicaciones'       => 'The Ministry of Communications',
            'Ministerio de Cultura'              => 'The Ministry of Culture',
            'Ministerio de Defensa'              => 'The Ministry of Defense',
            'Ministerio de Desarrollo Social'    => 'The Ministry of Social Development',
            'Ministerio de Economía y Finanzas Públicas y Ministerio de Industria' => 'The Ministry of the Economy and Public Finance and the Ministry of Industry',
            'Ministerio de Economía y Finanzas Públicas' => 'The Ministry of the Economy and Public Finance',
            'Ministerio de Economía y Obras y Servicios Públicos' => 'The Ministry of the Economy and Public Works and Services',
            'Ministerio de Educación'            => 'The Ministry of Education',
            'Ministerio de Educación y Deportes' => 'The Ministry of Education and Sports',
            'Ministerio de Energía y Minería'    => 'The Ministry of Energy and Mining',
            'Ministerio de Finanzas'                     => 'The Ministry of Finance',
            'Ministerio de Hacienda'             => 'The Ministry of the Treasury',
            'Ministerio de Hacienda y Finanzas Públicas' => 'The Ministry of the Treasury and Public Finance',
            'Ministerio de Industria'                    => 'The Ministry of Industry',
            'Ministerio del Interior, Obras Públicas y Vivienda' => 'The Ministry of the Interior, Public Works and Housing',
            'Ministerio del Interior y Transporte'       => 'The Ministry of the Interior and Transportation',
            'Ministerio de Justicia y Derechos Humanos'         => 'The Ministry of Justice and Human Rights',
            'Ministerio de Justicia, Seguridad y Derechos Humanos' => 'The Ministry of Justice, Security and Human Rights',
            'Ministerio de Justicia'             => 'The Ministry of Justice',
            'Ministerio de Modernización'        => 'The Ministry of Modernization',
            'Ministerio de Planificación Federal, Inversión Pública y Servicios' => 'The Ministry of Federal Planning, Public Investment and Services',
            'Ministerio de la Producción, Ciencia y Tecnología' => 'The Ministry of Production, Science and Technology',
            'Ministerio de Producción'           => 'The Ministry of Production',
            'Ministerio de Relaciones Exteriores y Culto' => 'The Ministry of Foreign and Religious Affairs',
            'Ministerio de Salud'                => 'The Ministry of Health',
            'Ministerio de Seguridad'            => 'The Ministry of Security',
            'Ministerio de Trabajo, Empleo y Seguridad Social' => 'The Ministry of Labor, Employment and Social Security',
            'Ministerio de Transporte'           => 'The Ministry of Transportation',
            'Ministerio de Turismo'              => 'The Ministry of Tourism',
            
            'Defensoría General de la Nación' => 'The National Public Defender’s Office',

            'Oficina Nacional de Contrataciones' => 'The National Contracting Office',
            'Oficina Nacional de Tecnologías de Información' => 'The National Office of Information Technologies',

            'Organismo Regulador del Sistema Nacional de Aeropuertos' => 'The Regulatory Body of the National Airport System',

            'Órgano de Control de Concesiones Viales' => 'The Road Concessions Control Body',

            'Policía de Seguridad Aeroportuaria' => 'The Airport Security Police',

            'Prefectura Naval Argentina' => 'The Argentine Naval Prefecture',

            'Procuración General de la Nación' => 'The Attorney General of the Nation',
            'Procuración del Tesoro de la Nación' => 'The Procurement of the National Treasury',

            'Registro de la Propiedad Inmueble de la Capital Federal' => 'The Real Estate Registry of the Federal Capital',
            'Registro Nacional de Armas'                              => 'The National Registry of Weapons',
            'Registro Nacional de las Personas'                       => 'The National Registry of Persons',
            'Registro Nacional de Reincidencia'                       => 'The National Registry of Recidivism',
            'Registro Nacional de Tierras Rurales'                    => 'The National Registry of Rural Lands',
            'Registro Nacional de Trabajadores y Empleadores Agrarios' => 'The National Registry of Agricultural Workers and Employers',
            'Registro Nacional de Trabajadores Rurales y Empleadores' => 'The National Registry of Rural Workers and Employers',

            'Sistema Federal de Medios y Contenidos Públicos' => 'The Federal System of Media and Public Content',

            'Secretaría de Agregado de Valor'            => 'The Secretariat of Acrued Value',
            'Secretaría de Agricultura Familiar, Coordinación y Desarrollo Territorial' => 'The Secretariat of Family Agriculture, Coordination and Territorial Development',
            'Secretaría de Agricultura, Ganadería y Pesca' => 'The Secretariat of Agriculture, Livestock and Fisheries',
            'Secretaría de Ambiente y Desarrollo Sustentable' => 'The Secretariat of the Environment and Sustainable Development',
            'Secretaría de Asuntos Políticos e Institucionales' => 'The Secretariat of Political and Institutional Affairs',
            'Secretaría de Comercio Interior'            => 'The Secretariat of Internal Commerce',
            'Secretaría de Comercio'                     => 'The Secretariat of Commerce',
            'Secretaría de Comunicaciones'               => 'The Secretariat of Communications',
            'Secretaría de Comunicación Pública'         => 'The Secretariat of Public Communication',
            'Secretaría de Coordinación y Monitoreo Institucional' => 'The Secretariat of Coordination and Institutional Monitoring',
            'Secretaría de Culto'                        => 'The Secretariat of Worship',
            'Secretaría de Cultura y Creatividad'        => 'The Secretariat of Culture and Creativity',
            'Secretaría de Cultura'                      => 'The Secretariat of Culture',
            'Secretaría de Deporte, Educación Física y Recreación' => 'The Secretariat of Sports, Physical Education and Recreation',
            'Secretaría de Derechos Humanos'             => 'The Secretariat of Human Rights',
            'Secretaría de Energía Eléctrica'            => 'The Secretariat of Electric Energy',
            'Secretaría de Energía'                      => 'The Secretariat of Energy',
            'Secretaría de Empleo Público'               => 'The Secretariat of Public Employment',
            'Secretaría de Empleo'                       => 'The Secretariat of Employment',
            'Secretaría de Emprendedores y de la Pequeña y Mediana Empresa' => 'The Secretariat of Entrepreneurs and Small and Medium Enterprises',
            'Secretaría de Ética Pública, Transparencia y Lucha contra la Corrupción' => 'The Secretariat for Public Ethics, Transparency, and the Fight against Corruption',
            'Secretaría de Finanzas'                     => 'The Secretariat of Finance',
            'Secretaría de Gabinete y Coordinación Administrativa' => 'The Secretariat of the Cabinet and Administrative Coordination',
            'Secretaría de Gabinete'                     => 'The Secretariat of the Cabinet',
            'Secretaría de Gestión de Transporte'        => 'The Secretariat of Transportation Management',
            'Secretaría de Hacienda'                     => 'The Secretariat of the Treasury',
            'Secretaría de Industria y Servicios'        => 'The Secretariat of Industry and Services',
            'Secretaría de Industria'                    => 'The Secretariat of Industry',
            'Secretaría de Integración Productiva'       => 'The Secretariat of Productive Integration',
            'Secretaría de Justicia'                     => 'The Secretariat of Justice',
            'Secretaría Legal y Administrativa'          => 'The Legal and Administrative Secretariat',
            'Secretaría Legal y Técnica'                 => 'The Legal and Technical Secretariat',
            'Secretaría de Mercados Agroindustriales'    => 'The Secretariat of Agroindustrial Markets',
            'Secretaría de Minería'                      => 'The Secretariat of Mining',
            'Secretaría de Modernización Administrativa' => 'The Secretariat of Administrative Modernization',
            'Secretaría de Obras Públicas'               => 'The Secretariat of Public Works',
            'Secretaría de la Pequeña y Mediana Empresa y Desarrollo Regional' => 'The Secretariat of Small and Medium Enterprises and Regional Development',
            'Secretaría de Política Económica y Planificación del Desarrollo' => 'The Secretariat of Economic Policy and Development Planning',
            'Secretaría de Políticas Universitarias'     => 'The Secretariat of University Policies',
            'Secretaría de Políticas Integrales sobre Drogas de la Nación Argentina' => 'The Secretariat of Comprehensive Drug Policies of the Argentine Nation',
            'Secretaría de Políticas, Regulación e Institutos' => 'The Secretariat of Policies, Regulation and Institutes',
            'Secretaría de Programación para la Prevención de la Drogadicción y la Lucha contra el Narcotráfico' => 'The Secretariat of Programming for the Prevention of Drug Addiction and the Fight against Drug Trafficking',
            'Secretaría de Promoción y Programas Sanitarios' => 'The Secretariat of Health Promotion and Programs',
            'Secretaría de Recursos Hidrocarburíferos'   => 'The Secretariat of Hydrocarbon Resources',
            'Secretaría de Seguridad Social'             => 'The Secretariat of Social Security',
            'Secretaría de Tecnologías de la Información y las Comunicaciones' => 'The Secretariat of Information and Communication Technologies',
            'Secretaría de Trabajo'                         => 'The Secretary of Labor',
            'Secretaría de la Transformación Productiva'     => 'The Secretariat of Productive Transformation',
            'Secretaría de Transporte'                        => 'The Secretariat of Transportation',
            'Secretaría General de la Presidencia de la Nación' => 'The Secretary General of the President of the Nation',

            'Subsecretaría de Asuntos Registrales'                      => 'The Undersecretariat of Registration Affairs',
            'Subsecretaría de Comercio Interior'                        => 'The Undersecretariat of Internal Commerce',
            'Subsecretaría de Coordinación Administrativa'              => 'The Undersecretariat of Administrative Coordination',
            'Subsecretaría de Coordinación y Control de Gestión'        => 'The Undersecretariat of Coordination and Management Control',
            'Subsecretaría de Coordinación Económica'                   => 'The Undersecretariat of Economic Coordination',
            'Subsecretaría de Coordinación'                             => 'The Undersecretariat of Coordination',
            'Subsecretaría de Defensa del Consumidor'                   => 'The Undersecretariat of Consumer Defense',
            'Subsecretaría de Economía Creativa'                        => 'The Undersecretariat of the Creative Economy',
            'Subsecretaría de Financiamiento de la Producción'          => 'The Undersecretariat of Production Financing',
            'Subsecretaría de Fiscalización del Trabajo y de la Seguridad Social' => 'The Undersecretariat of Labor and Social Security Oversight',
            'Subsecretaría de Gestión Integral de Riesgos de Desastres' => 'The Undersecretariat for the Comprehensive Management of Disaster Risks',
            'Subsecretaría de Gobierno Digital'                         => 'The Undersecretariat of Digital Government',
            'Subsecretaría de Lechería'                                 => 'The Undersecretariat of Dairy',
            'Subsecretaría de Política Criminal'                        => 'The Undersecretariat of Criminal Policy',
            'Subsecretaría de Políticas, Regulación y Fiscalización'    => 'The Undersecretariat of Policies, Regulation and Supervision',
            'Subsecretaría de Puertos y Vías Navegables'                => 'The Undersecretariat of Ports and Navigable Waterways',
            'Subsecretaría de Recursos Hídricos'                        => 'The Undersecretariat of Water Resources',
            'Subsecretaría de Servicios Tecnológicos y Productivos'     => 'The Undersecretariat of Technological and Productive Services',
            'Subsecretaría de Tecnologías de Gestión'                   => 'The Undersecretariat of Management Technologies',
            'Subsecretaría de Transporte Automotor'                     => 'The Undersecretariat of Motor Transport',

            'Subtesorería General de la Nación' => 'The General Sub-Treasury of the Nation',

            'Servicio de Conciliación Laboral Obligatoria' => 'The Mandatory Labor Conciliation Service',
            'Servicio Nacional de Rehabilitación'          => 'The National Rehabilitation Service',
            'Servicio Nacional de Sanidad y Calidad Agroalimentaria' => 'The National Service for Health and the Quality of Foodstuffs',

            'Sindicatura General de la Nación' => 'The General Syndicate of the Nation',

            'Sistema de Prestaciones Básicas de Atención Integral a favor de las Personas con Discapacidad' => 'The System of Basic Comprehensive Care Benefits for People with Disabilities',

            'Lotería Nacional Sociedad del Estado' => 'The National Lottery State Society',

            'Superintendencia de Seguros de la Nación' => 'The National Insurance Superintendency',
            'Superintendencia de Servicios de Salud'   => 'The Health Services Superintendency',
            'Superintendencia de Riesgos del Trabajo'  => 'The Superintendency of Labor Risks',

            'Tesorería General de la Nación' => 'The General Treasury of the Nation',

            'Tribunal de Tasaciones de la Nación' => 'The National Court of Appraisals',

            'Unidad de Coordinación y Evaluación de Subsidios al Consumo Interno' => 'The Coordination and Evaluation Unit for Subsidies to Internal Consumption',
            'Unidad de Información Financiera' => 'The Financial Information Unit',

            'Universidad Nacional de Buenos Aires' => 'The National University of Buenos Aires',
        );
        //Translates the topics
        $topics = array(
            'Adhesiónes'         => 'Accessions',
            'Adhesión'           => 'Accession',
            'Adscripciones'      => 'Subscriptions',
            'Aeropuertos'        => 'Airports',
            'Aguas'              => 'Waters',
            'Artesanias'         => 'Crafts',
            'Ascensos-promociones' => 'Promotions',
            'Becas'              => 'Scholarships',
            'Caza'               => 'Hunting',
            'Celebración'        => 'Celebration',
            'Competencia'        => 'Competition',
            'Condecoraciónes'    => 'Decorations',
            'Condecoración'      => 'Decoration',
            'Congresos'          => 'Congresses',
            'Conmemoraciones'    => 'Commemorations',
            'Convenios'          => 'Agreements',
            'Convenio'           => 'Agreement',
            'Cunicultura'        => 'Rabbit Farming',
            'Declaración'        => 'Declaration',
            'Denominación'       => 'Denomination',
            'Deportes'           => 'Sports',
            'Derogación'         => 'Repeal',
            'Desburocratizacion' => 'Debureaucratization',
            'Designaciones'      => 'Designations',
            'Designación'        => 'Designation',
            'Disposiciones'      => 'Provisions',
            'Docentes'           => 'Teachers',
            'Donaciónes'         => 'Donations',
            'Donación'           => 'Donation',
            'Educación'          => 'Education',
            'Empréstito'         => 'Loans',
            'Enfermeria'         => 'Nursing',
            'Entidad'            => 'Entity',
            'Exportaciones'      => 'Exports',
            'Expropiacion'       => 'Expropriation',
            'Extranjeros'        => 'Foreigners',
            'Fideicomiso'        => 'Trusts',
            'Gerenciamiento'     => 'Management',
            'Hidrocarburos'      => 'Hydrocarbons',
            'Homenaje'           => 'Tribute',
            'Humanos'            => 'Human Rights',
            'Impuestos'          => 'Taxes',
            'Indultos'           => 'Pardons',
            'Inmuebles-expropiación' => 'Real Estate Expropriation',
            'Inmuebles/transferencia' => 'Real Estate Transfer',
            'Immuebles'          => 'Real Estate',
            'Importaciones'      => 'Imports',
            'Inmuebles'          => 'Real Estate',
            'Inmueble'           => 'Real Estate',
            'Justicia'           => 'Justice',
            'Mediación'          => 'Mediation',
            'Microempresas'      => 'Microenterprises',
            'Migraciones'        => 'Migrations',
            'Modificatoria'      => 'Amendment',
            'Monumentos'         => 'Monuments',
            'Nombramiento'       => 'Appointment',
            'Nombre'             => 'Name',
            'Peaje'              => 'Toll',
            'Pensiones'          => 'Pensions',
            'Pesquero'           => 'Fishing',
            'Prestamos'          => 'Loans',
            'Presupuesto'        => 'Budget',
            'Promociónes'        => 'Promotions',
            'Promoción'          => 'Promotion',
            'Prórroga'           => 'Extension',
            'Puertos'            => 'Ports',
            'Radiodifusion'      => 'Broadcasting',
            'Ratificación'       => 'Ratification',
            'Reduccion'          => 'Reduction',
            'Remuneraciones'     => 'Remunerations',
            'Resoluciones-terminos' => 'Resolutions-Terms',
            'Subsidios'          => 'Subsidies',
            'Suramericanas'      => 'South American',
            'Telecomunicaciones' => 'Telecommunications',
            'Telefonos'          => 'Telephones',
            'Transferencias'     => 'Transfers',
            'Vacunación'         => 'Vaccination',
            'Vitivinicultura'    => 'Viticulture',
            'Viviendas'          => 'Housing',
            'Vivienda'           => 'Housing',
        );

        //Translates the countries
        $countries = array(
            'Banco Asiático De Inversión en Infraestructura (BAII)' => 'AIIB',
            'Estados Partes del Mercosur' => 'MERCOSUR',
            'Organización de las Naciones Unidas para la Alimentación y la Agricultura (FAO)' => 'UN',
            'Unión Postal Universal' => 'UN',

            'Emiratos Árabes Unidos' => 'AE',
            'República de Chile' => 'CL',
            'República Dominicana' => 'DO',
            'Estado de Israel' => 'IL',
            'República de Kenia' => 'KE',
            'Estado de Kuwait' => 'KW',
            'Estados Unidos Mexicanos' => 'MX',
            'Estado de Qatar' => 'QA',
            'Federación de Rusia' => 'RU',
            'República Popular China' => 'TW',
        );

        //Creates function to capitalize with exceptions
        $exceptions = [
            'and',
            'a', 'al', 'ante', 'como', 'con', 'de', 'del', 'e', 'en', 'el', 'la', 'las', 'los', 'para', 'por', 'que', 'sobre', 'un', 'una', 'vs.', 'y'
        ];
        $capsLock = [
            '(afip)', '(anses)', 'a.r.a.', 'bid', '(bid)', 'birf', 'caf', '(caf)', 'covid', '(cmr)', '(girsar)', 'ii', 'ii)', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', '(iosfa)', '(fonplata)', '(promace)', '(prosat', 's.a', 's.a.'
        ];
        function mb_ucwordsexcept($str, $delims=' ', $encoding='UTF-8') {//TODO: Make it so that it capitalizes words with ( and “
            global $exceptions; global $capsLock;
            $out = array(trim($str));
            foreach (str_split($delims) as $key => $delim) {//Loops through the delimiters
                if (!str_contains($out[$key], $delim)) {break;}//Breaks if delimiter not present
                $out[$key+1] = '';
                foreach (explode($delim, $out[$key]) as $word) {//Loops through the words and capitalizes if not in exceptions
                    $out[$key+1] .= in_array($word, $capsLock) ? mb_strtoupper($word, $encoding).$delim:(in_array($word, $exceptions) ? $word.$delim:mb_strtoupper(substr($word, 0, 1), $encoding).mb_substr($word, 1, strlen($word)-1, $encoding).$delim);
                }
                $out[$key+1] = rtrim($out[$key+1], $delim);
            }
            return ucfirst(end($out));
        }

        //Fixes some of the values
        $fixName = array(
            'diadelavocacionpolitica' => 'Día de la vocación política',
            'instituyeseel“barriodevelezsarsfield”' => 'Institúyese el “barrio de vélez sársfield”',
            'leyn°2754-prorrogandolaimplementacióndelaleyn°2699-leydemediaciónintegral,hastaeldía31deoctubrede2014' => 'Ley N° 2754 - Prorrogando la implementación de la ley N° 2699 - Ley de mediación integral, hasta el día 31 de octubre de 2014',
        );
        $fixOrigin = array(
            'Secretaria' => 'Secretaría',
            'Ministerio de Agricultura , Ganadería y Pesca' => 'Ministerio de Agricultura, Ganadería y Pesca',
            'Ministerio de Justicia , Seguridad y Derechos Humanos' => 'Ministerio de Justicia, Seguridad y Derechos Humanos',
            'Ministerio de la Producción , Ciencia y Tecnología' => 'Ministerio de la Producción, Ciencia y Tecnología',
            'Secretaría de Programación para la Prevención de la drogadicción y la lucha contra el narcotráfico' => 'Secretaría de Programación para la Prevención de la Drogadicción y la Lucha contra el Narcotráfico',
            'PREFECTURA NAVAL ARGENTINA' => 'Prefectura Naval Argentina',
            'SUPERINTENDENCIA DE RIESGOS DEL TRABAJO' => 'Superintendencia de Riesgos del Trabajo',
        );
        $fixTopic = array(
            'Adhesion'  => 'Adhesión',
            'Condecoracion' => 'Condecoración',
            'Declaracion' => 'Declaración',
            'Donacion'  => 'Donación',
            'Educacion' => 'Educación',
            'Inmuelbe'  => 'Inmueble',
            'Promocion' => 'Promoción',
            'Prorroga'  => 'Prórroga',
        );
        $fixSummary = array(
            'adhesionprovincialcupofemeninoartistasobrasmusicalesculturayeducacion' => 'Adhesión provincial copa femenino artistas sobre música, les culturay educación',
            'asistenciaysalvamentoarmadaargentinaconmemoraciones' => 'Asistencia y salvamento armada Argentina conmemoraciones',
            'conmemoracionesdeportesfutbol' => 'Conmemoraciones deportes fútbol',
            'conmemoracionesenfermedadesdonaciondesangre' => 'Conmemoraciones enfermedades donación de sangre',
            'declaraciondecapitalprovincialbahiablanca' => 'Declaración de capital provincial Bahía Blanca',
            'declaraciondeciudad' => 'Declaración de ciudad',
            'declaraciondeciudadanoilustre' => 'Declaración de ciudadano ilustre',
            'declaraciondeciudadanoilustrereconocimientopostmortem' => 'Declaración de ciudadano ilustre reconocimiento post mortem',
            'declaraciondeutilidadpublicaexpropiacion' => 'Declaración de utilidad pública expropiación',
            'declaraciondeutilidadpublicaexpropiacioninmuebles' => 'Declaración de utilidad pública expropiación inmuebles',
            'declaraciondeutilidadpublicaexpropiacionterrenos' => 'Declaración de utilidad pública expropiación terrenos',
            'declaraciondeutilidadpublicaexpropiacionterrenosobraspublicas' => 'Declaración de utilidad pública expropiación terrenos obras públicas',
            'emprendedoresempresasrelacionadasinversioneseconomiayfinanzas' => 'Emprendedores empresas relacionadas inversiones economía y finanzas',
            'facilidadesdepagobeneficiosimpositivosvaluacionfiscaldeudafiscalingresosbrutos' => 'Facilidades de pago beneficios impositivos valuación fiscal deuda fiscal ingresos brutos',
            'inmigranteconmemoracionesculturayeducacionadhesionprovincial' => 'Inmigrante conmemoraciones cultura y educación adhesión provincial',
            'leyimpositivaimpuestosregimentributarioexencionesimpositivas' => 'Ley impositiva impuestos régimen tributario exenciones impositivas',
            'leymodificatoriapartidospoliticosprovincialescreaciondecargoseticapublica' => 'Ley modificatoria partidos políticos provinciales creación de cargos ética pública',
            'leymodificatoriaregistrodelaspersonasdelaprovinciadebuenosairesregistrodelestadocivilycapacidaddelaspersonasdelaprovinciadebuenosaires' => 'Ley modificatoria registro de las personas de la Provincia de Buenos Aires registro del estado civil y capacidad de las personas de la Provincia de Buenos Aires',
            'matriculacionprofesionalterapiasalternativasejercicioprofesionalcolegioprofesionaldeterapiaocupacional' => 'Matriculación profesional terapias alternativas ejercicio profesional colegio profesional de terapia ocupacional',
            'monumentoshistoricospatrimoniocultural' => 'Monumentos históricos patrimonio cultural',
            'personalidaddestacadaderechoshumanos' => 'Personalidad destacada derechos humanos',
            'personalidaddestacadamedicinalegal' => 'Personalidad destacada medicina legal',
            'planessocialessubsidiosinstitutodelaviviendadelaprovinciadebuenosaires' => 'Planes sociales subsidios instituto de la vivienda de la Provincia de Buenos Aires',
            'presupuestomunicipalgastoseconomiayfinanzas' => 'Presupuesto municipal gastos economía y finanzas',
            'presupuestoprovincialadministracionpublicaprovincialeconomiayfinanzas' => 'Presupuesto provincial administración pública provincial economía y finanzas',
            'presupuestoprovincialleydepresupuestoadministracionpublicaprovincialeconomiayfinanzas' => 'Presupuesto provincial ley de presupuesto administración pública provincial economía y finanzas',
            'regimendepromociondelaeconomiadelconocimientoactividadeseconomicasregistronacionaldebeneficiariosdelregimendepromociondelaeconomiadelconocimiento' => 'Régimen de promoción de la economía del conocimiento actividades económicas registro nacional de beneficiarios del régimen de promoción de la economía del conocimiento',

            '*gran cruz de la orden del merito civil*' => '“Gran Cruz de la Orden del Mérito Civil”',
            '*condecoracion del aguila azteca en el grado de banda*' => '“Condecoración del Águila Azteca en el grado de Banda”',
            'la gran cruz de la orden *el sol del peru*' => 'la Gran Cruz de la Orden “El Sol del Perú”',

            'ciudadanos,por' => 'Ciudadanos, por',
            'condecoraciones ,titulos' => 'Condecoraciones, títulos',

            'coronavirus' => 'Coronavirus',

            'antartida' => 'Antártida',
            'argentinos' => 'Argentinos',
            'armenia' => 'Armenia',
            'banco asiatico de inversion en infraestructura' => 'Banco Asiático de Inversión en Infraestructura',
            'provincia de buenos aires' => 'Provincia de Buenos Aires',
            'buenos aires' => 'Buenos Aires',
            'canal de beagle' => 'Canal de Beagle',
            'catamarca' => 'Catamarca',
            'municipalidad de chajari' => 'Municipalidad de Chajarí',
            'chile' => 'Chile',
            'china' => 'China',
            'provincia de chubut' => 'Provincia de Chubut',
            'chubut' => 'Chubut',
            'cordoba' => 'Cordoba',
            'corrientes' => 'Corrientes',
            'covid' => 'COVID',
            'cruz roja' => 'Cruz Roja',
            'cuenca del plata' => 'Cuenca del Plata',
            'cuenca de la laguna la picasa' => 'Cuenca de la Laguna la Picasa',
            'eduardo menen' => 'Eduardo Menen',
            'provincia de entre rios' => 'Provincia de Entre Ríos',
            'entre rios' => 'Entre Ríos',
            'españa' => 'España',
            'estado bariloche' => 'Estado Bariloche',
            'estados unidos mexicanos' => 'Estados Unidos Mexicanos',
            'provincia de formosa' => 'Provincia de Formosa',
            'fernando de la bua' => 'Fernando de la Bua',
            'georgias del sur' => 'Georgias del Sur',
            'ibera' => 'Iberá',
            '(inac)' => '(INAC)',
            'islas del atlantico sur' => 'Islas del Atlantico Sur',
            'israel' => 'Israel',
            'jujuy' => 'Jujuy',
            'general de brigada julian perez dorrego' => 'General de Brigada Julián Pérez Dorrego',
            'kuwait' => 'Kuwait',
            'la pampa' => 'la Pampa',
            'la plata' => 'la Plata',
            'las islas malvinas' => 'las Islas Malvinas',
            'mar del plata' => 'Mar del Plata',
            'mayo' => 'Mayo',
            'mercosur' => 'MERCOSUR',
            'naciones unidas' => 'Naciones Unidas',
            'pacifico' => 'Pacífico',
            'paraguay' => 'Paraguay',
            'puerto madryn chubut' => 'Puerto Madryn, Chubut',
            'puerto san julian' => 'Puerto San Julián',
            'republica dominicana' => 'República Dominicana',
            'republica peruana' => 'República Peruana',
            'rey carlos iii' => 'Rey Carlos III',
            'rey juan carlos i' => 'Rey Juan Carlos I',
            'rio negro' => 'Rio Negro',
            'rusia' => 'Rusia',
            'salta' => 'Salta',
            'san juan' => 'San Juan',
            'sandwich del sur' => 'Sandwich del Sur',
            'estado santa cruz' => 'Estado Santa Cruz',
            'provincia de santa cruz' => 'Provincia de Santa Cruz',
            'santa fe' => 'Santa Fe',
            'suiza' => 'Suiza',
            'universidad nacional de tierra del fuego' => 'Universidad Nacional de Tierra del Fuego',
            'villa dos trece' => 'Villa dos Trece'
        );
        
        //Gets the laws
        $limit = $limit ?? json_decode(file_get_contents('http://www.saij.gob.ar/busqueda?o='.$start.'&p='.$step.'&f=Total%7CTipo+de+Documento%2FLegislaci%C3%B3n%7CFecha%7COrganismo%7CPublicaci%C3%B3n%7CTema%7CEstado+de+Vigencia%7CAutor%7CJurisdicci%C3%B3n&s=&v=colapsada'), true)['searchResults']['categoriesResultList'][0]['facetChildren'][0]['facetHits'];
        for ($offset = $start; $offset <= $limit; $offset += $step) {
            //Gets the data from legislation.gov API
            $laws = json_decode(file_get_contents('http://www.saij.gob.ar/busqueda?o='.$offset.'&p='.$step.'&f=Total%7CTipo+de+Documento%2FLegislaci%C3%B3n%7CFecha%7COrganismo%7CPublicaci%C3%B3n%7CTema%7CEstado+de+Vigencia%7CAutor%7CJurisdicci%C3%B3n&s=&v=colapsada'), true)['searchResults']['documentResultList'];
            foreach ($laws as $law) {
                //Decodes the data
                $law = json_decode($law['documentAbstract'], true)['document'];

                //Gets the province
                $LBpage = $provinces[$law['content']['provincia'] ?? $scraper];

                //Interprets the data
                $enforceDate = $enactDate = $lastactDate = $law['content']['fecha'];
                $ID = $LBpage.':'.($law['content']['mecanografico'] ?? $law['content']['id-infojus'] ?? explode(' ', $law['content']['standard-normativo'])[0]);
                $regime = '{"es":"La República Argentina", "en":"The Argentine Republic"}';
                //Gets the name, and topic if there is one
                $name = NULL;
                if (isset($law['content']['titulo-norma'])) {
                    if (!str_contains($law['content']['titulo-norma'], ' ') && !in_array($law['content']['titulo-norma'], $fixName)) {
                        //Gets the topic
                        $topic = '\'{"es":"'.str_replace(array_keys($fixTopic), array_values($fixTopic), trim(ucfirst(mb_strtolower($law['content']['titulo-norma'], 'UTF-8')), ' .')).'", "en":"'.$topics[str_replace(array_keys($fixTopic), array_values($fixTopic), trim(ucfirst(mb_strtolower($law['content']['titulo-norma'], 'UTF-8')), ' .'))].'"}\'';
                        $name = NULL;
                    } else {
                        $name = $law['content']['titulo-norma'];
                    }
                }
                $name = trim(($name ?? $law['content']['titulo_1'] ?? $law['content']['titulo_noticia'] ?? $law['content']['nombre-coloquial'] ?? $law['content']['asunto'] ?? $law['content']['tipo-norma']['texto'].' '.$law['content']['id-infojus']), ' .');
                //Gets the type
                $type = $types[$law['content']['tipo-norma']['codigo']];
                //Gets the country
                $country = ["AR"];
                    if ($type === 'Treaty') {
                        foreach ($countries as $countryName => $countryID) {
                            if (str_contains($name, $countryName)) {
                                $country[] = $countryID;
                            }
                        }
                    }
                    $country = json_encode($country);
                //Gets status
                $status = $statuses[$law['content']['estado'] ?? $law['content']['status']];
                //Gets the origin
                $origin = $law['content']['org_emisor'] ?? $law['content']['organismo-emisor']['organismo'] ?? NULL;
                    $origin = isset($origin) ? '\'[{"es":"'.trim(str_replace(array_keys($fixOrigin), array_values($fixOrigin), $origin)).'", "en":"'.$origins[trim(str_replace(array_keys($fixOrigin), array_values($fixOrigin), $origin))].'"}]\'':'NULL';
                //Gets the rest of the values
                $summary = trim(isset($law['content']['sumario']) ? (is_array($law['content']['sumario']) ? implode('; ', $law['content']['sumario']):($law['content']['sumario'] ?? NULL)):NULL, ' .;');
                $topic = $topic ?? 'NULL';
                $source = 'http://www.saij.gob.ar/'.$law['metadata']['friendly-url']['description'].'/'.$law['metadata']['uuid'];

                //Makes sure there are no quotes in the title
                $name = strtr($name, array(" '"=>" ‘", "'"=>"’", ' "'=> " “", '"'=>"”", '' => '“', '' => '”'));
                $summary = strtr($summary, array(" '"=>" ‘", "'"=>"’", ' "'=>" “", '"'=>"”", '' => '“', '' => '”'));
                if (substr($name, 0, 1) === '’') {$name[0] = '‘';} if (substr($summary, 0, 1) === '’') {$summary[0] = '‘';}
                if (substr($name, 0, 1) === '”') {$name[0] = '“';} if (substr($summary, 0, 1) === '”') {$summary[0] = '“';}

                //Makes sure the name and summary are capitalized properly
                if (!preg_match('/[a-z]/', $name)) {$name = mb_ucwordsexcept(str_replace(array_keys($fixName), array_values($fixName), mb_strtolower($name, 'UTF-8')));}
                if (!preg_match('/[a-z]/', $summary)) {$summary = ucfirst(str_replace(array_keys($fixSummary), array_values($fixSummary), mb_strtolower($summary, 'UTF-8')));}

                //JSONifies the values
                $name = '{"es":"'.$name.'"}';
                $summary = $summary ? '\'{"es":"'.$summary.'"}\'':'NULL';
                $source = '{"es":"'.$source.'"}';

                //Makes sure there are no duplicates and adds law to the table
                echo '<br/>'; console_log($ID); console_log($law);
                $SQL2 = "INSERT INTO `laws".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `country`, `regime`, `name`, `type`, `status`, `origin`, `summary`, `topic`, `source`) 
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$country."', '".$regime."', '".$name."', '".$type."', '".$status."', ".$origin.", ".$summary.", ".$topic.", '".$source."')";
                echo 'p. '.$offset.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Updates the date on the countries and divisions tables
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        $SQL31 = "UPDATE `divisions` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `parent`='".$scraper."'"; echo '<br/><br/>'.$SQL31;
        if (!$test) {$conn2->query($SQL3); $conn2->query($SQL31);}

        //Closes the connections
        $conn->close(); $conn2->close();
    ?>
</body>
</html>