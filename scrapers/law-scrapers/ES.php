<html><body>
    <?php //TODO: Implement crawling the specific law pages. Get PDF and types
          //TODO: Make a way for a law to have multiple origins
          //TODO: Implement ammendment support
        //Settings
        $test = true; $country = 'ES';
        $start = 0;//Which law to start from
        $step = 500;//How many laws there are on each page. 1000 and 2000 will break the program
        $limit = 10000;//How many laws to get. The current limit is 243,143

        //Opens the parser (HTML_DOM)
        include '../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e";
        $password="f1x.A1pgN[BwX4[t";
        $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        //Clears the table
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`laws".strtolower($country)."`"; echo $SQL1.'<br/><br/>';
        if (!$test) {$conn->query($SQL1);}

        //Translates the types
        $types = [
            'Acta'=>'Minutes',

            'Acuerdo'=>'Agreement',
            'Entrada en vigor del Acuerdo'=>'Enforcement of an Agreement',
            'Protocolo Adicional del Acuerdo'=>'Additional Protocol of an Agreement',

            'Adenda'=>'Addendum',

            'Aplicación provisional del Acuerdo'=>'Provisional Application of an Agreement',
            'Aplicación provisional del Canje de Notas'=>'Provisional Application of an Exchange of Letters',
            'Aplicación de las directrices interpretativas'=>'Application of Interpretative Guidelines',

            'Asunto'=>'Case',
            'Asuntos acumulados'=>'Consolidated Cases',

            'Auto'=>'Court Order',
            'Pleno. Auto'=>'Plenary Court Order',

            'Canje de Notas'=>'Exchange of Letters',
            'Circular'=>'Circular',
            'Comunicación'=>'Communication',

            'Conflicto entre órganos constitucionales'=>'Conflict between Constitutional Bodies',

            'Convenio'=>'Convention',
            'Anexo al Convenio'=>'Annex to a Convention',

            'Cuestión de inconstitucionalidad'=>'Question of Unconstitutionality',

            'Decisión de Ejecución'=>'Executive Decision',
            'Decisión del Tribunal General'=>'Court Decision',
            'Decisión'=>'Decision',

            'Declaración'=>'Declaration',
            'Declaraciones'=>'Declaration',

            'Decreto'=>'Decree',
            'Decreto-ley'=>'Legal Decree',
            'Decreto-Ley'=>'Legal Decree',
            'Decreto Legislativo'=>'Legislative Decree',
            'Decreto Foral Legislativo'=>'Legislative Foral Decree',
            'Real Decreto'=>'Royal Decree',
            'Real Decreto-ley'=>'Royal Legal Decree',

            'Denuncia del Convenio'=>'Denunciation of a Convention',
            'Denuncia'=>'Denunciation',

            'Directiva'=>'Directive',
            'Directrices interpretativas'=>'Interpretative Guidelines',

            'Estatutos'=>'Statutes',

            'Excepción'=>'Exception',

            'Impugnación de disposiciones'=>'Appeal of Provisions',

            'Instrucción'=>'Instruction',
            'Instrucciones'=>'Instruction',

            'Instrumento'=>'Instrument',
            /*'Instrumento de adhesión al Convenio'=>'Instrument of Accession to a Convention',
            'Instrumento de ratificación de la Convención'=>'Instrument of Ratification of a Convention',
            'Instrumento de ratificación del Protocolo al Tratado'=>'Instrument of Ratification of a Protocol to a Treaty',
            'Instrumento de ratificación del Protocolo'=>'Instrument of Ratification of a Protocol',*/

            'Ley'=>'Law',
            'Lista'=>'List',
            'Normas'=>'Rules',
            'Orden'=>'Order',
            'Orientación'=>'Guidance',
            'Orientaciones'=>'Guidelines',
            'Protocolo'=>'Protocol',
            'Recomendación'=>'Recommendation',

            //'Recurso de inconstitucionalidad'=>'Appeal of Unconstitutionality',
            'Recurso'=>'Appeal',

            'Reglamento de Ejecución'=>'Executive Regulation',
            'Reglamento'=>'Regulation',

            'Resolución'=>'Resolution',
            
            'Sentencia'=>'Judgement',
            'Pleno. Sentencia'=>'Plenary Judgement',

            'Suspensión de la aplicación'=>'Suspension of Application',

            'Tratado'=>'Treaty',
        ];
        //Translates the status
        $statuses = [
            'ico now'=>'Valid',
            'ico past'=>'Invalid',
            'ico gazette'=>'Valid',
            'ico future'=>'Not Yet in Force'
        ];
        //Translates the origins
        $origins = [//Organized by the Spanish name in alphabetical order
            'Agencia de Protección de Datos'=>array('The Agency of Data Protection', 'ES'),
            'Agencia Española de Protección de Datos'=>array('The Agency of Data Protection', 'ES'),

            'Banco de España'=>array('The Bank of Spain', 'ES'),

            'Comisión Interministerial de Retribuciones'=>array('The Interministerial Commission of Remunerations', 'ES'),
            'Comisión Nacional de los Mercados y la Competencia'=>array('The National Commission of Markets and Competition', 'ES'),
            'Comisión Nacional del Mercado de Valores'=>array('The National Commission of the Stock Market', 'ES'),

            'Comunidad Autónoma de Andalucía'=>array('The Autonomous Community of Andalusia', 'ES-AN'),
            'Comunidad Autónoma de Aragón'=>array('The Autonomous Community of Aragon', 'ES-AR'),
            'Comunidad Autónoma del Principado de Asturias'=>array('The Autonomous Community of the Principality of Asturias', 'ES-AS'),
            'Comunidad Autónoma de Canarias'=>array('The Autonomous Community of the Canaries', 'ES-CN'),
            'Comunidad Autónoma de Cantabria'=>array('The Autonomous Community of Cantabria', 'ES-CB'),
            'Comunidad de Castilla y León'=>array('The Community of Castile and León', 'ES-CL'),
            'Comunidad Autónoma de Castilla-La Mancha'=>array('The Community of Castile-La Mancha', 'ES-CM'),
            'Comunidad Autónoma de Cataluña'=>array('The Autonomous Community of Catalonia', 'ES-CT'),
            'Comunidad Autónoma de Extremadura'=>array('The Autonomous Community of Extremadura', 'ES-EX'),
            'Comunidad Autónoma de Galicia'=>array('The Autonomous Community of Galicia', 'ES-GA'),
            'Comunidad Autónoma de las Illes Balears'=>array('The Autonomous Community of the Balearic Islands', 'ES-IB'),
            'Comunidad Autónoma de las Islas Baleares'=>array('The Autonomous Community of the Balearic Islands', 'ES-IB'),
            'Comunidad Autónoma de La Rioja'=>array('The Autonomous Community of La Rioja', 'ES-RI'),
            'Comunidad de Madrid'=>array('The Autonomous Community of Madrid', 'ES-MD'),
            'Comunidad Autónoma de la Región de Murcia'=>array('The Autonomous Community of the Region of Murcia', 'ES-MC'),
            'Comunidad Foral de Navarra'=>array('The Chartered Community of Navarre', 'ES-NC'),
            'Comunidad Autónoma del País Vasco'=>array('The Autonomous Community of the Basque Country', 'ES-PV'),
            'Comunidad Valenciana'=>array('The Community of Valencia', 'ES-VC'),
            'Comunitat Valenciana'=>array('The Community of Valencia', 'ES-VC'),

            'Consejo de Estado'=>array('The Council of State', 'ES'),
            'Consejo General del Poder Judicial'=>array('The General Council of the Judiciary', 'ES'),
            'Consejo de Política Fiscal y Financiera de las CCAA'=>array('The Council of Fiscal and Financial Policy of the Autonomous Communities', 'ES'),
            'Consejo del Reino'=>array('The Council of the Kingdom', 'ES'),
            'Consejo de Seguridad Nuclear'=>array('The Council of Nuclear Security', 'ES'),

            'Delegación Nacional de Sindicatos'=>array('The National Delegation of Trade Unions', 'ES'),

            'Ente Público Radiotelevisión Española'=>array('The Public Entity of Spanish Radio and Television', 'ES'),

            'Fondo de Reestructuración Ordenada Bancaria'=>array('The Fund for Orderly Bank Restructuring', 'ES'),

            'Jefatura del Estado'=>array('The Head of State', 'ES'),
            'Jefatura Nacional del Movimiento'=>array('The National Head of the Movement', 'ES'),

            'Junta Electoral Central'=>array('The Central Electoral Board', 'ES'),
            'Junta Electoral General'=>array('The General Electoral Board', 'ES'),

            'Ministerio de Administración Territorial'=>array('The Ministry of Territorial Administration', 'ES'),
            'Ministerio de Administraciones Públicas'=>array('The Ministry of Public Administration', 'ES'),
            'Ministerio para las Administraciones Públicas'=>array('The Ministry of Public Administration', 'ES'),
            'Ministerio del Aire'=>array('The Ministry of the Air', 'ES'),
            'Ministerio de Agricultura'=>array('The Ministry of Agriculture', 'ES'),
            'Ministerio de Agricultura, Industria y Comercio'=>array('The Ministry of Agriculture, Industry and Trade', 'ES'),
            'Ministerio de Agricultura y Pesca'=>array('The Ministry of Agriculture and Fisheries', 'ES'),
            'Ministerio de Agricultura, Pesca y Alimentación'=>array('The Ministry of Agriculture, Fisheries and Food', 'ES'),
            'Ministerio de Agricultura y Pesca, Alimentación y Medio Ambiente'=>array('The Ministry of Agriculture and Fisheries, Food and the Environment', 'ES'),
            'Ministerio de Agricultura, Alimentación y Medio Ambiente'=>array('The Ministry of Agriculture, Food and the Environment', 'ES'),
            'Ministerio de Asuntos Económicos y Transformación Digital'=>array('The Ministry of Economic Affairs and Digital Transformation', 'ES'),
            'Ministerio del Ejército'=>array('The Ministry of the Army', 'ES'),
            'Ministerio de Asuntos Exteriores'=>array('The Ministry of Foreign Affairs', 'ES'),
            'Ministerio de Asuntos Exteriores y de Cooperación'=>array('The Ministry of Foreign Affairs and Cooperation', 'ES'),
            'Ministerio de Asuntos Exteriores, Unión Europea y Cooperación'=>array('The Ministry of Foreign Affairs, the European Union and Cooperation', 'ES'),
            'Ministerio de Asuntos Sociales'=>array('The Ministry of Social Affairs', 'ES'),
            'Ministerio de Ciencia'=>array('The Ministry of Science', 'ES'),
            'Ministerio de Ciencia e Innovación'=>array('The Ministry of Science and Innovation', 'ES'),
            'Ministerio de Ciencia, Innovación y Universidades'=>array('The Ministry of Science, Innovation and Universities', 'ES'),
            'Ministerio de Ciencia y Tecnología'=>array('The Ministry of Science and Technology', 'ES'),
            'Ministerio de Comercio'=>array('The Ministry of Trade', 'ES'),
            'Ministerio de Comercio y Turismo'=>array('The Ministry of Trade and Tourism', 'ES'),
            'Ministerio de Consumo'=>array('The Ministry of Consumption', 'ES'),
            'Ministerio de Cultura'=>array('The Ministry of Culture', 'ES'),
            'Ministerio de Cultura y Bienestar'=>array('The Ministry of Culture and Welfare', 'ES'),
            'Ministerio de Cultura y Bienestar Social'=>array('The Ministry of Culture and Social Welfare', 'ES'),
            'Ministerio de Cultura y Deporte'=>array('The Ministry of Culture and Sports', 'ES'),
            'Ministerio de Defensa'=>array('The Ministry of Defense', 'ES'),
            'Ministerio de Derechos Sociales'=>array('The Ministry of Social Rights', 'ES'),
            'Ministerio de Derechos Sociales y Agenda 2030'=>array('The Ministry of Social Rights and the 2030 Agenda', 'ES'),
            'Ministerio de Derechos Sociales, Consumo y Agenda 2030'=>array('The Ministry of Social Rights, Consumption and the 2030 Agenda', 'ES'),
            'Ministerio de Economía'=>array('The Ministry of Economy', 'ES'),
            'Ministerio de Economía y Comercio'=>array('The Ministry of Economy and Trade', 'ES'),
            'Ministerio de Economía y Competitividad'=>array('The Ministry of Economy and Competition', 'ES'),
            'Ministerio de Economía y Empresa'=>array('The Ministry of Economy and Business', 'ES'),
            'Ministerio de Economía y Hacienda'=>array('The Ministry of Economy and Finance', 'ES'),
            'Ministerio de Economía, Comercio y Empresa'=>array('The Ministry of Economy, Trade and Business', 'ES'),
            'Ministerio de Economía, Industria y Competitividad'=>array('The Ministry of Economy, Industry and Competition', 'ES'),
            'Ministerio de Educación'=>array('The Ministry of Education', 'ES'),
            'Ministerio de Educación y Ciencia'=>array('The Ministry of Education and Science', 'ES'),
            'Ministerio de Educación y Cultura'=>array('The Ministry of Education and Culture', 'ES'),
            'Ministerio de Educación Nacional'=>array('The Ministry of National Education', 'ES'),
            'Ministerio de Educación, Política Social y Deporte'=>array('The Ministry of Education, Social Policy and Sports', 'ES'),
            'Ministerio de Educación, Cultura y Deporte'=>array('The Ministry of Education, Culture and Sports', 'ES'),
            'Ministerio de Educación y Formación Profesional'=>array('The Ministry of Education and Vocational Training', 'ES'),
            'Ministerio de Educación, Formación Profesional y Deportes'=>array('The Ministry of Education, Vocational Training and Sports', 'ES'),
            'Ministerio de Empleo y Seguridad Social'=>array('The Ministry of Employment and Social Security', 'ES'),
            'Ministerio de Energía'=>array('The Ministry of Energy', 'ES'),
            'Ministerio de Energía, Turismo y Agenda Digital'=>array('The Ministry of Energy, Tourism and Digital Planning', 'ES'),
            'Ministerio de Estado'=>array('The Ministry of State', 'ES'),
            'Ministerio Fiscal'=>array('The Ministry of Finance', 'ES'),
            'Ministerio de Fomento'=>array('The Ministry of Development', 'ES'),
            'Ministerio de la Gobernación'=>array('The Ministry of Governance', 'ES'),
            'Ministerio de Guerra'=>array('The Ministry of War', 'ES'),
            'Ministerio de Hacienda'=>array('The Ministry of Finance', 'ES'),
            'Ministerio de Hacienda y Administraciones Públicas'=>array('The Ministry of Finance and Public Administration', 'ES'),
            'Ministerio de Hacienda y Economía'=>array('The Ministry of Finance and Economy', 'ES'),
            'Ministerio de Hacienda y Función Pública'=>array('The Ministry of Finance and the Civil Service', 'ES'),
            'Ministerio de Inclusión, Seguridad Social y Migraciones'=>array('The Ministry of Inclusion, Social Security and Migration', 'ES'),
            'Ministerio de Igualdad'=>array('The Ministry of Equality', 'ES'),
            'Ministerio de Industria'=>array('The Ministry of Industry', 'ES'),
            'Ministerio de Industria y Energía'=>array('The Ministry of Industry and Energy', 'ES'),
            'Ministerio de Industria y Comercio'=>array('The Ministry of Industry and Trade', 'ES'),
            'Ministerio de Industria y Turismo'=>array('The Ministry of Industry and Tourism', 'ES'),
            'Ministerio de Industria, Comercio y Turismo'=>array('The Ministry of Industry, Trade and Tourism', 'ES'),
            'Ministerio de Industria, Energía y Turismo'=>array('The Ministry of Industry, Energy and Tourism', 'ES'),
            'Ministerio de Industria, Turismo y Comercio'=>array('The Ministry of Industry, Tourism and Trade', 'ES'),
            'Ministerio de Información'=>array('The Ministry of Information', 'ES'),
            'Ministerio de Información y Turismo'=>array('The Ministry of Information and Tourism', 'ES'),
            'Ministerio de Instrucción Pública y Bellas Artes'=>array('The Ministry of Public Instruction and Fine Arts', 'ES'),
            'Ministerio del Interior'=>array('The Ministry of the Interior', 'ES'),
            'Ministerio de Justicia'=>array('The Ministry of Justice', 'ES'),
            'Ministerio de Justicia e Interior'=>array('The Ministry of Justice and the Interior', 'ES'),
            'Ministerio de Juventud e Infancia'=>array('The Ministry of Youth and Childhood', 'ES'),
            'Ministerio de Gracia y Justicia'=>array('The Ministry of Grace and Justice', 'ES'),
            'Ministerio de Marina'=>array('The Ministry of the Navy', 'ES'),
            'Ministerio de Medio Ambiente'=>array('The Ministry of the Environment', 'ES'),
            'Ministerio de Medio Ambiente, y Medio Rural y Marino'=>array('The Ministry of the Environment, the Rural Environment and Marine Environment', 'ES'),
            'Ministerio de Obras Públicas'=>array('The Ministry of Public Works', 'ES'),
            'Ministerio de Obras Públicas y Transportes'=>array('The Ministry of Public Works and Transportation', 'ES'),
            'Ministerio de Obras Públicas y Urbanismo'=>array('The Ministry of Public Works and Urban Planning', 'ES'),
            'Ministerio de Obras Públicas, Transportes y Medio Ambiente'=>array('The Ministry of Public Works, Transportation and the Environment', 'ES'),
            'Ministerio de Planificación del Desarrollo'=>array('The Ministry of Development Planning', 'ES'),
            'Ministerio del Portavoz del Gobierno'=>array('The Ministry of the Government Spokesperson', 'ES'),
            'Ministerio de Política Territorial'=>array('The Ministry of Territorial Policy', 'ES'),
            'Ministerio de Política Territorial y Función Pública'=>array('The Ministry of Territorial Policy and the Civil Service', 'ES'),
            'Ministerio de Política Territorial y Memoria Democrática'=>array('The Ministry of Territorial Policy and Democratic Memory', 'ES'),
            'Ministerio de Política Territorial y Administración Pública'=>array('The Ministry of Territorial Policy and Public Administration', 'ES'),
            'Ministerio de la Presidencia'=>array('The Ministry of the Presidency', 'ES'),
            'Ministerio de la Presidencia, Justicia y Relaciones con las Cortes'=>array('The Ministry of the Presidency, Justice and Relations with the Courts', 'ES'),
            'Ministerio de la Presidencia y para las Administraciones Territoriales'=>array('The Ministry of the Presidency and for Territorial Administrations', 'ES'),
            'Ministerio de la Presidencia, Relaciones con las Cortes y Memoria Democrática'=>array('The Ministry of the Presidency, Relations with the Courts and Democratic Memory', 'ES'),
            'Ministerio de la Presidencia, Relaciones con las Cortes e Igualdad'=>array('The Ministry of the Presidency, Relations with the Courts and Equality', 'ES'),
            'Ministerio de Relaciones con las Cortes y de la Secretaría del Gobierno'=>array('The Ministry of Relations with the Courts and the Secretary of the Government', 'ES'),
            'Ministerio de Sanidad'=>array('The Ministry of Health', 'ES'),
            'Ministerio de Sanidad y Consumo'=>array('The Ministry of Health and Consumption', 'ES'),
            'Ministerio de Sanidad y Política Social'=>array('The Ministry of Health and Social Policy', 'ES'),
            'Ministerio de Sanidad, Política Social e Igualdad'=>array('The Ministry of Health, Social Policy and Equality', 'ES'),
            'Ministerio de Sanidad, Consumo y Bienestar Social'=>array('The Ministry of Health, Consumption and Social Welfare', 'ES'),
            'Ministerio de Sanidad y Seguridad Social'=>array('The Ministry of Health and Social Security', 'ES'),
            'Ministerio de Sanidad, Servicios Sociales e Igualdad'=>array('The Ministry of Health, Social Services and Equality', 'ES'),
            'Ministerio de Trabajo'=>array('The Ministry of Labor', 'ES'),
            'Ministerio de Trabajo y Asuntos Sociales'=>array('The Ministry of Labor and Social Affairs', 'ES'),
            'Ministerio de Trabajo y Economía Social'=>array('The Ministry of Labor and Social Economy', 'ES'),
            'Ministerio de Trabajo e Inmigración'=>array('The Ministry of Labor and Immigration', 'ES'),
            'Ministerio de Trabajo, Sanidad y Seguridad Social'=>array('The Ministry of Labor, Health and Social Security', 'ES'),
            'Ministerio de Trabajo y Seguridad Social'=>array('The Ministry of Labor and Social Security', 'ES'),
            'Ministerio de Trabajo, Migraciones y Seguridad Social'=>array('The Ministry of Labor, Migration and Social Security', 'ES'),
            'Ministerio de Transformación Digital'=>array('The Ministry of Digital Transformation', 'ES'),
            'Ministerio para la Transformación Digital y de la Función Pública'=>array('The Ministry for Digital Transformation and the Civil Service', 'ES'),
            'Ministerio para la Transición Ecológica'=>array('The Ministry for Ecological Transition', 'ES'),
            'Ministerio para la Transición Ecológica y el Reto Demográfico'=>array('The Ministry for Ecological Transition and the Demographic Challenge', 'ES'),
            'Ministerio de Transportes'=>array('The Ministry of Transportation', 'ES'),
            'Ministerio de Transportes y Comunicaciones'=>array('The Ministry of Transportation and Communication', 'ES'),
            'Ministerio de Transportes y Movilidad Sostenible'=>array('The Ministry of Transportation and Sustainable Mobility', 'ES'),
            'Ministerio de Transportes, Movilidad y Agenda Urbana'=>array('The Ministry of Transportation, Mobility and Urban Planning', 'ES'),
            'Ministerio de Transportes, Turismo y Comunicaciones'=>array('The Ministry of Transportation, Tourism and Communication', 'ES'),
            'Ministerio de Universidades'=>array('The Ministry of Universities', 'ES'),
            'Ministerio de Universidades e Investigación'=>array('The Ministry of Universities and Research', 'ES'),
            'Ministerio de Vivienda'=>array('The Ministry of Housing', 'ES'),
            'Ministerio de la Vivienda'=>array('The Ministry of Housing', 'ES'),
            'Ministerio de Vivienda y Agenda Urbana'=>array('The Ministry of Housing and Urban Planning', 'ES'),

            'Organización Sindical'=>array('The Trade Union Organization', 'ES'),

            'Presidencia del Consejo de Ministros'=>array('The President of the Council of Ministers', 'ES'),
            'Presidencia de las Cortes Españolas'=>array('The Presidency of the Spanish Courts', 'ES'),
            'Presidencia del Directorio Militar'=>array('The Presidency of the Military Directorate', 'ES'),
            'Presidencia del Gobierno'=>array('The President of the Government', 'ES'),

            'Secretaría General del Movimiento'=>array('The General Secretariat of the Movement', 'ES'),

            'Cortes Españolas'=>array('The Spanish Courts', 'ES'),
            'Cortes Generales'=>array('The General Courts', 'ES'),
            'Cortes de la Monarquía Española'=>array('The Courts of the Spanish Monarchy', 'ES'),
            'Tribunal Constitucional'=>array('The Constitutional Court', 'ES'),
            'Tribunal de Cuentas'=>array('The Court of Auditors', 'ES'),
            'Tribunal Supremo'=>array('The Supreme Court', 'ES'),

            'Unión Europea'=>array('The European Union', 'ES'),
            'Comunidades Europeas'=>array('The European Community', 'ES'),

            'Universidades'=>array('Universities', 'ES')
        ];

        //Gets the limit
        $html_dom = file_get_html('https://boe.es/buscar/legislacion.php?accion=Mas&id_busqueda=ZmdTajg1THNnSHZVeFdPcVVVUm5hbEkvSTNsanpGTHRqT2w5dDMza1JuR2FSdGQySENEU3VuakNPeXNkYjhsS3dncTZyM3BGRWUrR3RnSjlvNnhSNGhvUUttOERhQlU3RzZKUThma2NvZjB1T1Y2cEZrRUllYUJmMFV4UTZlL3o1bnZlbnZjTzYrRFUxRXI0aEJpQ2NRQW9yY3cwdXRMZkZNNGZoMFdsMUNJPQ,,-'.$start.'-'.$step);
        $limit = $limit ?? str_replace('.', '', explode(' de ', $html_dom->find('div.paginar')[0]->plaintext)[1]);
        //Loops through the pages
        for ($offset = $start; $offset < $limit; $offset += $step) {
            //Processes the data
            $html_dom = file_get_html('https://boe.es/buscar/legislacion.php?accion=Mas&id_busqueda=ZmdTajg1THNnSHZVeFdPcVVVUm5hbEkvSTNsanpGTHRqT2w5dDMza1JuR2FSdGQySENEU3VuakNPeXNkYjhsS3dncTZyM3BGRWUrR3RnSjlvNnhSNGhvUUttOERhQlU3RzZKUThma2NvZjB1T1Y2cEZrRUllYUJmMFV4UTZlL3o1bnZlbnZjTzYrRFUxRXI0aEJpQ2NRQW9yY3cwdXRMZkZNNGZoMFdsMUNJPQ,,-'.$offset.'-'.$step);
            $laws = $html_dom->find('div#contenido')[0]->find('div.listadoResult')[0]->find('ul li.resultado-busqueda');
            foreach ($laws as $law) {
                //Gets the values
                $enactDate = date('Y-m-d', strtotime(str_replace('/', '-', end(explode(' ', trim($law->find('h3')[0]->plaintext, ' )')))))); $enforceDate = $enactDate; $lastactDate = $enactDate;
                $division = $origins[explode(' (', $law->find('h3')[0]->plaintext)[0]][1];
                    $ID = $division.'-'.str_replace('-', '', explode('Ref. ', $law->find('a')[0]->title)[1]);
                $name = trim($law->find('p')[0]->plaintext, ' .');
                $regime = 'The Kingdom of Spain';
                //Gets the type
                if (str_starts_with($name, 'Corrección') || str_starts_with($name, 'Enmienda') || str_starts_with($name, 'Modificación') || str_starts_with($name, 'Modificaciones') || str_starts_with($name, 'Reforma') || str_starts_with($name, 'Texto enmendado') || str_starts_with($name, 'Textos enmendados')) {$type = 'Ammendment to ';} else {$type = '';}
                foreach ($types as $search => $replace) {
                    $searchName = explode('Corrección de errores del ', $name)[1] ?? explode('Enmienda del ', $name)[1] ?? explode('Modificación del ', $name)[1] ?? explode('Modificaciones del ', $name)[1] ?? explode('Reforma del ', $name)[1] ?? explode('Texto enmendado del ', $name)[1] ?? explode('Textos enmendados ', $name)[1] ?? $name;
                    if (str_starts_with($searchName, $search.' ') || str_starts_with($searchName, $search.', ')) {
                        $type .= $replace;
                        break;
                    }
                } if ($type == '') {$type = 'Law';}
                //Gets the rest of the values
                $status = 'Valid';
                $origin = explode(' (', $law->find('h3')[0]->plaintext)[0];
                $source = 'https://boe.es'.explode('..', $law->find('a')[0]->href)[1];

                //Makes sure there are no appostophes in the title
                $name = str_replace("'", "’", $name);

                //JSONifies the values
                $name = '{"es":"'.$name.'"}';
                $origin = '{"es":"'.$origin.'", "en":"'.$origins[$origin][0].'"}';
                $source = '{"es":"'.$source.'&lang=es", "ca":"'.$source.'&lang=ca", "gl":"'.$source.'&lang=gl", "eu":"'.$source.'&lang=eu", "en":"'.$source.'&lang=en", "fr":"'.$source.'&lang=fr"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `laws".strtolower($country)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `regime`, `type`, `status`, `origin`, `source`)
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$regime."', '".$type."', '".$status."', '".$origin."', '".$source."')";

                //Executes the SQL
                echo 'O: '.$offset.', '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Connect to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
    
        $conn2 = new mysqli("localhost", $username, $password, $database);

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$country."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>