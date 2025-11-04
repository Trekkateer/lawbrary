<?php //Bahrain
    //Settings
    $test = false; $scraper = 'BH';
    $start = 1939;//Which year to start from (independence day is 1971 August 15); First is 1939
    $step = 10000;//How many laws per page
    $limit = 1981;//Which year to end at

    //Opens the parser (HTML_DOM)
    require '../simple_html_dom.php';

    //Opens my library
    require '../skrapateer.php';

    //Connects to the Law database
    $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Clears the table
    $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/><br/>';
    if (!$test) {$conn->query($SQL1);}

    //Sets up querying function
    function API_Call($locale, $year, $page) {
        global $step;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.lloc.gov.bh/'.$locale.'Legislation/Search',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{PostParam: {"IsSearchTitle":true,"LegNum":"","YearFrom":"'.$year.'","YearTo":"'.$year.'","OGFrom":"","OGTo":"","TypeID":"","SourceID":"","CategoryID":"","KeywordAll":"","KeywordAny":"","KeywordPhrase":"","KeywordNot":"","IsSearchTreaty":false,"IsSearchWomen":false,"IsSearchEnglish":false,"SortBy":"SortDate"}, PageNum: '.$page.', PageSize: '.$step.'}',
            CURLOPT_HTTPHEADER => array(
                'Cookie: __RequestVerificationToken=Oc9-0Anbg0E_ZP9w5cR36cTNBs6Sulc8ZfEt9NEah6b4QQJx9e258xbEyp9nWUy5wc1X_H-JFonRPUC4n2biW2JgPk4Kq1OltLJvffiQLQ41; __RequestVerificationToken_L2Vu0=aLvB9-GU6M55F3XNoGIrpTnVaRygABrD67SFOkU8GQQCKscwrXbQL4tvr8_JJPjjHJcAFSUoSI45VrhidHZR_TGRjIeTgmWEVM4UJFIvR941; ASP.NET_SessionId=1ei2zz1ewec5y2svn001syrr',
                'Content-Type: application/json',
                'requestverificationtoken: W3bt_BzsPHUPNmdj2uwDsVinidYsLy_hc1OBULkEYpfe8Dh6UOPDxGIX8OvzcKlbsi4_BZhjTiaSAGH3MwDMs5LQYDnF7Bul4ErOf-Z4qpM1:xk1areadC7ib27ZjgfRO_Rpg90oYJizGIpNbEP1lyzs323oCKbAntTg7nh6t-WKSUjjlb1Ujgd_9NXqWQZugUwIxeWD0K_GnfONCpL4E86A1'
            ),
        ));
        $response = curl_exec($curl); curl_close($curl);
        return new simple_html_dom($response);
    }

    //Translates the months
    $months = array(
        'يناير'  => '01', 'January' => '01',
        'فبراير' => '02', 'February'=> '02',
        'مارس'   => '03', 'March'   => '03',
        'أبريل'  => '04', 'April'   => '04',
        'مايو'   => '05', 'May'     => '05',
        'يونيو'  => '06', 'June'    => '06',
        'يوليو'  => '07', 'July'    => '07',
        'أغسطس'  => '08', 'August'  => '08',
        'سبتمبر' => '09', 'September'=> '09',
        'أكتوبر' => '10', 'October' => '10',
        'نوفمبر' => '11', 'November'=> '11',
        'ديسمبر' => '12', 'December'=> '12',
    );
    //Translates the types
    $amendTerms = array('وتعديلات على', 'تعديل', 'استدراك بشان', 'استدراك', 'اصلاحات', 'المعدل');
    $typeCodes = array(
        'K'    => ['Act'],
        'KN'   => ['Act'], 
        'A'    => ['Amiri Decree'],
        //'C'
        //'CN'
        'The Constitution' => ['Constitution'],
        'D'    => ['Decree'],
        'DN'   => ['Decree'],
        'L'    => ['Decree-Law'],
        'LN'   => ['Decree-Law'],
        'N'    => ['Document'],
        //'NN'
        'O'    => ['Order'],
        'ON'   => ['Order'],
        'OCAB' => ['Order', 'The Cabinet'],
        'RCAB' => ['Resolution', 'The Cabinet'],
        'RCAF' => ['Resolution', ["ar"=>"وزير الدولة لشئون مجلس الوزراء", "en"=>"The Minister of State for Cabinet Affairs"]],
        'RCAG' => ['Resolution'],
        'RCMC' => ['Resolution'],
        'RFNE' => ['Resolution', ["ar"=>"وزير المالية و الإقتصاد الوطني", "en"=>"The Minister of Finance and National Economy"]],
        'RHOS' => ['Resolution', ["ar"=>"وزير الإسكان", "en"=>"The Minister of Housing"]],
        'RINT' => ['Resolution', ["ar"=>"وزير الداخلية", "en"=>"The Minister of Interior"]],
        'RLSA' => ['Resolution'],
        'RMAG' => ['Resolution'],
        'RJUS' => ['Resolution', ["ar"=>"وزير العدل", "en"=>"The Minister of Justice"]],
        'RHEL' => ['Resolution', ["ar"=>"وزير الصحة", "en"=>"The Minister of Health"]],
        'RN'   => ['Resolution'],
        'RNCAB' => ['Resolution', 'The Cabinet'],
        'RNMAG' => ['Resolution'],
    );
    $typeNames = array(
        'سن قانون' => 'Act',//On website search menu as 'سن قانون أو مرسوم بقانون'
        'القانون' => 'Act',
        'اصدار قانون' => 'Act',
        'قانون' => 'Act',//On website search menu as 'قانون أو مرسوم بقانون'
        'قوانين' => 'Act',

        'تحليل' => 'Analysis',

        'إعلان' => 'Announcement',//On website search menu
        'اعلان' => 'Announcement',
        'اعلانات' => 'Announcement',

        'الاستئناف' => 'Appeal',
        
        'تطبيق لائحة' => 'Application of Regulations',

        'تعيين' => 'Appointment',

        'ميزانية' => 'Budget',
        'الميزانية' => 'Budget',

        'تعميم' => 'Circular',//On website search menu

        'دستور دولة البحرين' => 'Constitution',

        'استدراك' => 'Correction',

        'مراسلات الجرائد' => 'Newspaper Correspondence',

        'قرار' => 'Resolution',//On website search menu
        'قرارات' => 'Resolution',

        'مرسوم بقانون' => 'Decree-Law',//On website search menu as 'قانون أو مرسوم بقانون'
        'مرسوم' => 'Decree',//On website search menu

        'بعض القرارات' => 'Decisions',
        'القرارات' => 'Decisions',

        'تنفيذ قانون' => 'Implementation of Law',

        'التعليمات' => 'Instructions',
        'الصكوك المتداولة' => 'Circulating Instruments',

        'اجتماع' => 'Meeting',

        'المذكرات' => 'Notes',

        'المذكرات المتبادلة' => 'Exchanged Notes',
        'المذكره الايضاحية' => 'Explanatory Note',

        'أمر إداري' => 'Administrative Order',
        'أمر بتعيين' => 'Appointment Order',
        'أمر ملكي' => 'Royal Order',//On website search menu as 'أمر ملكي أو أميري'
        'أمر سام' => 'Royal Order',
        'الاوامر' => 'Orders',
        'أمر' => 'Order',

        'اجراءات' => 'Procedures',
        'بعض الاجراءات' => 'Procedures',
        'الاجراءات الجنائية' => 'Criminal Procedures',

        'لائحة' => 'Regulations',

        'استقالة' => 'Resignation',

        'أحكام المحكمة الدستورية' => 'Constitutional Court Rulings', //On website search menu

        'ملخص الجلسة' => 'Session Summary',

        'البيان السنوى' => 'Annual Statement',
        'البيان السنوى ل' => 'Annual Statement',
        'ملخص عمليات مجلس المناقصات والمزايدات لعام 2024 التقرير السنوي' => 'Summary of Annual Report',
        'بيان' => 'Statement',//On website search menu

        'ترجمة' => 'Translation',

        'معاهدة' => 'Treaty',

        'اخرى' => 'Document',//On website search menu
        '' => 'Document',
    );
    //Translates the origins
    $origins = array(
        'مجلس تنظيم مزاولة المهن الهندسية' => ['The Engineering Professionals Regulatory Council'],

        'اداره محاكم البحرين' => ['The Bahrain Courts Administration'],

        'هيئة الري والزراعة' => ['The Irrigation and Agriculture Authority'],

        'جمعية كشافة البحرين' => ['The Bahrain Scouts Association'],

        'مجلس الوزراء' => ['The Cabinet'],

        'اللجنة الوطنية لليونسكو' => ['The National Commission for UNESCO'],

        'لجنه منكوبى حريق القضيبية' => ['The Qudaybiya Fire Disaster Committee'],

        'مجلس بلدية البحرين' => ['The Bahrain Municipal Council'],

        'دائرة الاوقاف الجعفرية' => ['The Jaafari Endowments Department'],
        'دائرة الشئون القروية' => ['The Rural Affairs Department'],
        'دائرة البلديات والزراعة' => ['The Department of Municipalities and Agriculture'],

        'مدير الامن العام' => ['The Director of Public Security'],

        'محافظ مصرف البحرين المركزي' => ['The Governor of the Central Bank of Bahrain'],
        'حكومة البحرين' => ['The Government of Bahrain', 'BH'],
        'المملكة المتحدة لبريطانيا العظمى وشمال ايرلندا' => ['The United Kingdom of Great Britain and Northern Ireland', 'GB'],

        'رئيس مجلس الوزراء' => ['The Prime Minister'],
        'وزير المالية و الإقتصاد الوطني' => ['The Minister of Finance and National Economy'],
        'وزير الصحة' => ['The Minister of Health'],
        'وزير الإسكان والتخطيط العمراني' => ['The Minister of Housing and Urban Planning'],
        'وزير الإسكان' => ['The Minister of Housing'],
        'وزير الصناعة والتجارة' => ['The Minister of Industry and Commerce'],
        'وزير الداخلية' => ['The Minister of the Interior'],
        'وزير العدل والشئون الإسلامية والأوقاف' => ['The Minister of Justice, Islamic Affairs and Endowments'],
        'وزير العمل' => ['The Minister of Labor'],
        'وزير شئون البلديات والزراعة' => ['The Minister of Municipalities and Agriculture'],
        'وزير البلديات والزراعة' => ['The Minister of Municipalities and Agriculture'],
        'وزير التنمية الاجتماعية' => ['The Minister of Social Development'],
        'وزير المواصلات والاتصالات' => ['The Minister of Transportation and Telecommunications'],

        'بلدية الحد' => ['The Municipality of Al-Hidd'],
        'لبلدية المنامة' => ['The Municipality of Manama'],
        'بلدية المنامة' => ['The Municipality of Manama'],
        'بلدية المحرق' => ['The Municipality of Muharraq'],

        'رئيس ديوان الخدمة المدنية' => ['The President of the Civil Service Bureau'],
    );
    //Translates the topics
    $topics = array(
        'حوادث الطائرات'       => 'Aircraft accidents',
        'انظمة الملاحة الجوية' => 'Air navigation systems',
        'تعيينات قضائية'      => 'Judicial Appointments',
        'الآثار للبحرين'       => 'Archaeological sites in Bahrain',
        'تحديد سرعة السيارات' => 'Car speed limit',
        'فحص السيارات'        => 'Car inspection',
        'ضريبه بنزين السيارات' => 'Car gasoline tax',
        'فحص وتسجيل السيارات' => 'Examination and registration of vehicles',
        'تسجيل السيارات والدراجات' => 'Registration of cars and motorcycles',
        'السيارات'            => 'Automobiles',
        'المعرض السنوى لدائرة معارف البنات' => 'The annual exhibition of the Girls Education Department',
        'الاشخاص الموقوفين'    => 'Arrested persons',
        'المادة 4 من نظام تعاليم قضاة الجعفرية' => 'Article 4 of the judges of Jaafari Law',
        'حفلة سباق الخيل لفصل الخريف' => 'Autumn horse racing festival',
        'مطار البحرين المدني' => 'Bahrain International Airport',
        'الدلالين'              => 'Brokers',
        'بناء اغطية للبلوعات فى الشوارع' => 'Building covers for street drains',
        'المخالفات المدنية'  => 'Civil offenses',
        'التهم فى القضايا الجنائية' => 'Charges in criminal cases',
        'الخيرية'              => 'Charity',
        'الكحول التجارى'      => 'Commercial alcohol',
        'السجل التجاري'       => 'Commercial register',
        'قانون العقود'        => 'Contract Law',
        'تعويض موظفي'          => 'Compensation for employees',
        'تاليف مظلة السوق وقيمة الايجار داخلها' => 'The composition of the Market umbrella and the value of rent within it',
        'الكنكريت من الرفاع'  => 'Concrete from Riffa',
        'نظر الدعاوى على متوفى امام محكمه الشرع التى ينتسب اليها' => 'The consideration of cases against a deceased person before the Sharia Court to which he belongs',
        'اصفات النقد'         => 'Currency characteristics',
        'النقد'               => 'Currency',
        'الجمارك'             => 'Customs',
        'تسجيل رخص الغوص'     => 'Diving license registration',
        'تأسيس نادى سيدات البحرين' => 'The establishment of the Bahrain Ladies Club',
        'المتفجرات'           => 'Explosives',
        'استملاك'               => 'Expropriation',
        'تصدير الحديد القديم' => 'The export of old iron',
        'بشأن عطلة عيد الفطر المبارك لعام 1446هـ' => 'The Eid al-Fitr Holiday for the year 1446 AH',
        'بإلغاء رخصة مكتب أوال للاستشارات الهندسية' => 'The cancellation of the license of the Awal Engineering Consultancy Office',
        'بإلغاء رخصة شركة درة الخليج ذ.م.م' => 'The cancellation of the license of Durrat Al-Khalij LLC',
        'الغاء تاشيرات السفر بين امارات الخليج العربية' => 'The cancellation of travel visas between the Gulf Arab Emirates',
        'شروط فتح دكاكين او محلات تجارية بواسطة الاجانب' => 'Conditions for opening shops or commercial stores by foreigners',
        'الحفاظ على مياه البحرين' => 'The conservation of Bahrainꞌs Water',
        'العملة المزيفة'        => 'Counterfeit currency',
        'أصول المحاكمات الجز'   => 'Criminal Procedure Code',
        'مراقبة العقاقير الخطره' => 'Dangerous drugs monitoring',
        'مراقبه العقاقير الخطره' => 'Dangerous drugs monitoring',
        'اوراق اسناد الدين والرهانة والبيع الخيارى والوكالة' => 'Debt securities, pledge and sale options and agency documents',
        'تحديد اجارات المحلات التجارية فى المنامة للعام 1955' => 'Determining the rents of commercial shops in Manama for 1955',
        'التصرف فى مالية واملاك اى شخص متوفى' => 'The disposal of the fanances and properties of a deceased person',
        'اجراءات الطلاق فى المحكمة الشرعية' => 'Divorce procedures in the Sharia Court',
        'الرسوم والمصاريف في القضايا المدنيه' => 'Fees and expenses in civil cases',
        'رفع الدعاوى القضائية' => 'Filing lawsuits',
        'جمع التبرعات'         => 'Fundraising',
        'اصلاحات عامه للتشريعات البحرينية' => 'General reforms of Bahraini legislation',
        'الصاغه'               => 'Goldsmiths',
        'النظام الصحى'        => 'The Health system',
        'تضمين قطع النخيل لبعض القرى' => 'The inclusion of palm trees in some villages',
        'المنظمات الدولية'    => 'International organizations',
        'المهاجرة'             => 'Immigration',
        'الحصانات والامتيازات' => 'Immunities and privileges',
        'ضريبه دخل البحرين'   => 'Income tax in Bahrain',
        'الامتيازات الصناعية والتصميمات والعلامات التجارية' => 'Industrial franchises, designs, and trademarks',
        'الامتيازات الصناعية' => 'Industrial franchises',
        'التركات'              => 'Inheritance',
        'المسكرات'             => 'Intoxicants',
        'التملك للاجانب'       => 'Ownership for foreigners',
        'إجراءات الترخيص في العمل للأجانب' => 'Procedures for licensing foreign workers',
        'الطابو الصادرة'      => 'Land registry',
        'ترخيص الجمعيات والنوادي' => 'Licensing of associations and clubs',
        'القاء المخلفات فى الشوارع' => 'Littering in the streets',
        'اليانصيب'             => 'The lottery',
        'مكافحة الملاريا'      => 'Malaria control',
        'اسعار اللحوم'        => 'Meat prices',
        'تسجيل والدرجات النارية' => 'Motorcycle registration',
        'مشروع استخراج وتجهيز الغاز الطبيعى فى البحرين' => 'The project to extract and process natural gas in Bahrain',
        'الجنسية البحرينية'   => 'Bahraini nationality',
        'مدارس جديده للبنات'  => 'New schools for girls',
        'نظام الجوازات'       => 'The Passport system',
        'الجوازات'             => 'Passports',
        'اللؤلؤ'               => 'Pearls',
        'سن قانون عقوبات'     => 'Penal Code',
        'قانون عقوبات'       => 'Penal Code',
        'الرهن من الاوراق المسجلة من ادارة الطابو' => 'Pledge of documents registered by the land registry',
        'ميناء سترة'           => 'The Port of Sitra',
        'حيازه الاسلحه والمتاجره بها' => 'Possession and trade in weapons',
        'حيازة الاسلحة والمتاجرة بها' => 'Possession and trade in weapons',
        'حيازة الاسلحة والمتاجرة فيها' => 'Possession and trade in weapons',
        'الطوابع البريدية الجديدة' => 'The new postage stamps',
        'لسلطات رئيس العمل للتفيش' => 'The powers of the employer to inspect',
        'الصلاحيات والواجبات' => 'Powers and duties',
        'عرض الروايات والاستعراضات المسرحية والحفلات الموسيقية' => 'Presenting plays, theatrical performances and musical concerts',
        'الصحافة'              => 'Press',
        'منع اخذ الرمل من منطقه الحاله' => 'The prohibition of taking sand from the Al-Hala area',
        'ضبط الاسعار'           => 'Price control',
        'أسعار اللحوم'        => 'Meat prices',
        'المطبوعات والنشر'    => 'Printing and publishing',
        'السجون'               => 'Prisons',
        'التعليم الخصوصي'     => 'Private education',
        'عدم السماح باقتناء عقار غير منقول الى اى قاصر مولود فى البحرين من اب اجنبى' => 'The prohibition of the acquisition of real estate by a minor born in Bahrain to a foreign father',
        'تحريم الخمور و والمخدرات فى البحرين' => 'The prohibition of alcohol and drugs in Bahrain',
        'تحريم الخمور والمسكرات والمخدرات فى البحرين' => 'The prohibition of alcohol, intoxicants and drugs in Bahrain',
        'منع التعامل والمتاجرة مع اسرائيل' => 'The prohibition of dealing and trading with Israel',
        'منع اخذ طين من ساحل البحر' => 'The prohibition of taking mud from the coast of the sea',
        'منع اخذ الرمال من المنطقه حول محطه مضخات شركه نفط البحرين' => 'The prohibition of taking sand from the area around the Bahrain Oil Company pumping station',
        'الصحة العامة'         => 'Public health',
        'رخص نقليات عامة'      => 'Public transport licenses',
        'رخص وسائل النقل العامة' => 'Public transport licenses',
        'السيارات العموميه'    => 'Public vehicles',
        'تسجيل الاوقاف لدى دائرة الاوقاف' => 'The registration of endowments with the endowments department',
        'تنظيم تصنيع الايسكريم' => 'The regulation of the manufacture of ice cream',
        'تنظيم تصنيع المياه الغازية' => 'The regulation of the manufacture of soft drinks',
        'تنظيم بيع تذاكر السينما والمسرح ومنع بيعها لما دون الخامسة عشر' => 'The regulation of the sale of cinema and theater tickets and the prohibition of selling them to those under the age of fifteen',
        'اجار الفرشة الواحدة بمناسبة افتتاح سوق القضيبية الجديد' => 'The rent of a single mat on the occasion of the opening of the new Qudaybiya market',
        'الايجارات'              => 'Rentals',
        'الايجار'                => 'Rents',
        'تبليغ الاحضاريات'      => 'Reporting of attendances',
        'نظام الاقامة فى البحرين' => 'Residence in Bahrain',
        'التملك السكنى لاراضى الحكومة' => 'Residential ownership of government lands',
        'طرق'                   => 'Roads',
        'بيع عن طريق العطاءات' => 'Sale by tender',
        'الرواتب'              => 'Salaries',
        'عقوبات الامم المتحدة' => 'UN Sanctions',
        'الرمل والاحجار والكتكريت' => 'Sand, stones and concrete',
        'تدابير أمن الدولة'    => 'State Security Measures',
        'الأمن'                  => 'Security',
        'وكلاء شركات الملاحة او الطيران فى البحرين' => 'Shipping or airline agents in Bahrain',
        'تعليمات للسفن'        => 'Instructions for ships',
        'السفن'                 => 'Ships',
        'الرقيق'                => 'Slavery',
        'الحجارة والكنكريت والرمل' => 'Stones, concrete and sand',
        'اخذ الرمال من البديع' => 'Taking sand from Bidi’',
        'اساءة استعمال التيلفون' => 'Telephone abuse',
        'مناقصة تشييد مكتب لبلدية الرفاع' => 'The tender for the construction of the Riffa Municipality Office',
        'السير والمرور'       => 'Traffic',
        'المرور'               => 'Traffic',
        'نقل الرمل او الحصى من حدود منطقه الزيت' => 'The transfer of sand or gravel from the boundaries of the oil region',
        'نقل الاملاك الثابتة'    => 'The transfer of fixed assets',
        'انتقال السلطة على الصوماليين' => 'The transfer of authority over the Somalis',
        'زيارة الاماكن المقدسة' => 'Visiting Holy places',
        'العمل فى محاكم البحرين العدلية' => 'Work in the justice courts of Bahrain',
        'سوق الاربعاء'           => 'Wednesday market',
        'بيوت العمال الواقعة على الجانب الغربي من شارع الجفير بالمنامة' => 'Worker’s houses located on the west side of Al-Juffair Street in Manama',
        'ساعات العمل بمكاتب الحكومة خلال شهر رمضان المبارك' => 'Working hours in government offices during the holy month of Ramadan',
        'العمل'                => 'Labor',
    );

    //Defines which years to skip
    $skip = array(1940, 1942, 1943, 1949, 1950);

    //Sets the static variables
    $saveDate = date('Y-m-d'); $status = 'In Force';
    $publisher = '{"ar":"لجنة التشريع والرأي القانوني", "en":"The Legislation and Legal Opinion Commission"}';

    //Loops through languages
    foreach (array('ar'=>'', 'en'=>'en/') as $lang=>$locale) {
        //Gets the limit
        $limit = $limit ?? date('Y');
        //Loops through the years
        for ($year = $start; $year <= $limit; $year++) {
            //Skips the year if it is in the skip array
            if (in_array($year, $skip)) continue;
            //Loops through the laws
            $dom = API_Call($locale, $year, $page=1);
            foreach ($dom->find('div.legislations', 0)->find('div.legislation') as $law) {
                $dateline = trim($law->find('div.legislationdetails', 0)->find('div.otherdetails', 0)->find('div.dt', 0)->find('span.hvalue', 0)->plaintext);
                    $enactDate = $enforceDate = $lastactDate = explode('-', $dateline)[2].'-'.$months[explode('-', $dateline)[1]].'-'.str_pad(explode('-', $dateline)[0], 2, '0', STR_PAD_LEFT);
                $ID = end(explode('/', explode('.', $law->find('div.options', 0)->find('div.links', 0)->find('a', 0)->href)[0]));
                $name = fixQuotes(trim($law->find('div.legislationdetails', 0)->find('div.'.ucfirst($lang).'Title', 0)->plaintext), $lang);
                $regime = strtotime('today') > strtotime('1971 August 15') ? '{"ar":"مملكة البحرين", "en":"The Kingdom of Bahrain"}':'{"en":"The British Empire"}';
                //Checks if the law is an amendment
                $typorigin = trim(explode(' رقم ', explode('(', $name)[0])[0]);
                //echo '<span>'.$typorigin.'</span><br/>';
                $isAmend = 0;
                foreach ($amendTerms as $amendTerm) {
                    if (str_starts_with($typorigin, $amendTerm)) {
                        $isAmend = 1;
                        $typorigin = trim(str_replace($amendTerm, '', $typorigin));
                        break;
                    }
                }
                if ($isAmend == 0) {
                    foreach ($amendTerms as $amendTerm) {
                        if (str_contains($name, $amendTerm)) {
                            $isAmend = 1;
                            break;
                        }
                    }
                }
                //Gets the origin, topic and type
                //echo '<span>'.$typorigin.'</span><br/>';
                $country = array('BH'); $origin = array();
                foreach ($origins as $originAR=>$originEN) {
                    if (str_contains($typorigin, $originAR)) {
                        if (isset($originEN[1]) && $originEN[1] !== $scraper) $country[] = $originEN[1];
                        $origin[] = array('ar'=>$originAR, 'en'=>$originEN[0]);
                        $typorigin = trim(str_replace($originAR, '', $typorigin));
                    }
                }

                $country = json_encode($country, JSON_UNESCAPED_UNICODE);
                $origin = empty($origin) ? 'NULL':'\''.json_encode($origin, JSON_UNESCAPED_UNICODE).'\'';
                $topic = array();
                foreach ($topics as $topicAR=>$topicEN) {
                    if (str_contains($typorigin, $topicAR)) {
                        $topic[] = array('ar'=>$topicAR, 'en'=>$topicEN);
                        //Translates the title if title and topic are the same
                        if ($lang == 'ar' && $name == $topicAR) {
                            $name = '{"ar":"'.$name.'", "en":"'.$topicEN.'"}';
                            $isTopicTitle = true;
                        } else $isTopicTitle = false;
                        //Removes topic from the carrier string
                        $typorigin = trim(str_replace($topicAR, '', $typorigin));
                    }
                }
                $topic = empty($topic) ? 'NULL':'\''.json_encode($topic, JSON_UNESCAPED_UNICODE).'\'';
                echo '<span>'.$typorigin.'</span><br/>';
                $type = $typeNames[$typorigin] ?? $typeNames[explode(' ', $typorigin)[0]];
                if ($type === NULL) {
                    foreach ($typeNames as $typeAR=>$typeEN) {
                        if (str_starts_with($typorigin, $typeAR)) {
                            $type = $typeEN;
                            $typorigin = trim(str_replace($typeAR, '', $typorigin));
                        }
                    }
                }
                $ohyeah = $typeCodes[preg_replace('/[0-9-]/', '', $ID)][0];
                //Gets the rest of the values
                $PDF = isset($law->find('a[href*="PDF"]', 0)->href) ? 'https://www.lloc.gov.bh'.$law->find('a[href*="PDF"]', 0)->href.'':NULL;
                $source = isset($law->find('a[href*="HTM"]', 0)->href) ? 'https://www.lloc.gov.bh/Legislation/HTM/'.strtr($ID, [' '=>'%20']):$PDF;
                //Finalizes the ID
                $ID = $scraper.':'.strtr($ID, [' '=>'-']);

                //Creates SQL
                $SQL = "SELECT * FROM `".strtolower($scraper)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $name;
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `".strtolower($scraper)."` SET `name`='".$name."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    if (!$isTopicTitle) $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';
                    $PDF = isset($PDF) ? '\'{"'.$lang.'":"'.$PDF.'"}\'':'NULL';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($scraper)."`(`enactDate`, `enforceDate`, `lastactDate`, `saveDate`, `ID`, `name`, `country`, `regime`, `origin`, `publisher`, `type`, `isAmend`, `status`, `topic`, `source`, `PDF`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$saveDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$publisher."', '".$type."', ".$isAmend.", '".$status."', '".$topic."', '".$source."', ".$PDF.")";
                }

                //Makes the query
                echo 'Y'.$year.'P'.$page.': '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }
    }

    //Connects to the content database
    $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
    $conn2 = new mysqli("localhost", $username2, $password2, $database2);
    $conn2->select_db($database2) or die("Unable to select database");

    //Updates the date on the countries table
    $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".$saveDate."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
    if (!$test) {$conn2->query($SQL3);}

    //Closes the connections
    $conn->close(); $conn2->close();
?>