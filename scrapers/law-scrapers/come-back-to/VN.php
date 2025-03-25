<html><body>
    <?php //!! Exceeds the Xampp time limit for executions, and runs out of memory
        //Settings
        $test = true; $scraper = 'VN';
        $start = ["vi"=>0, "en"=>0]; //Which page to start from
        $step  = 50; //How many laws there are on each page
        $limit = ["vi"=>5635, "en"=>NULL]; //How many pages there are
                                          //After page 5635, there is no valid ID

        //Opens the parser (HTML_DOM)
        include '../../simple_html_dom.php';

        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select legal database");

        //Connect to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select content database");

        //Clears the table(s)
        $SQL1 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($scraper)."`"; echo $SQL1.'<br/>';
        if (!$test) {$conn->query($SQL1);}
        $SQL10 = "SELECT `ID` FROM `dbupm726ysc0bg`.`divisions` WHERE `parent` = '".$scraper."'";
        $result10 = $conn2->query($SQL10);
        while ($row10 = $result10->fetch_assoc()) {
            $SQL11 = "TRUNCATE TABLE `dbpsjng5amkbcj`.`".strtolower($row10['ID'])."`"; echo $SQL11.'<br/>';
            if (!$test) {$conn->query($SQL11);}
        }

        //Detects if the ID is valid
        $isValidID = function($ID) {
            return (preg_match('/[A-Z]/', $ID) || preg_match('/[a-z]/', $ID)) && preg_match('/[0-9]/', $ID) ? $ID:'NULL';
        };

        //Sanitizes the name
        $sanitizeName = array(
            'vi'=>array(
                //Fixes some IDs
                '- TTHT'=>'-TTHT',
                '/ CT-TTHT'=>'/CT-TTHT',
                '1 BC'=>'1BC', '2 BC'=>'2BC', '3 BC'=>'3BC', '4 BC'=>'4BC', '5 BC'=>'5BC', '6 BC'=>'6BC', '7 BC'=>'7BC', '8 BC'=>'8BC', '9 BC'=>'9BC', '0 BC'=>'0BC',
                '1 BKH/K'=>'1BKH/K', '2 BKH/K'=>'2BKH/K', '3 BKH/K'=>'3BKH/K', '4 BKH/K'=>'4BKH/K', '5 BKH/K'=>'5BKH/K', '6 BKH/K'=>'6BKH/K', '7 BKH/K'=>'7BKH/K', '8 BKH/K'=>'8BKH/K', '9 BKH/K'=>'9BKH/K', '0 BKH/K'=>'0BKH/K',
                '1 /BTTTT-'=>'1/BTTTT-', '2 /BTTTT-'=>'2/BTTTT-', '3 /BTTTT-'=>'3/BTTTT-', '4 /BTTTT-'=>'4/BTTTT-', '5 /BTTTT-'=>'5/BTTTT-', '6 /BTTTT-'=>'6/BTTTT-', '7 /BTTTT-'=>'7/BTTTT-', '8 /BTTTT-'=>'8/BTTTT-', '9 /BTTTT-'=>'9/BTTTT-', '0 /BTTTT-'=>'0/BTTTT-',
                '1/ BTNMT-'=>'1/BTNMT-', '2/ BTNMT-'=>'2/BTNMT-', '3/ BTNMT-'=>'3/BTNMT-', '4/ BTNMT-'=>'4/BTNMT-', '5/ BTNMT-'=>'5/BTNMT-', '6/ BTNMT-'=>'6/BTNMT-', '7/ BTNMT-'=>'7/BTNMT-', '8/ BTNMT-'=>'8/BTNMT-', '9/ BTNMT-'=>'9/BTNMT-', '0/ BTNMT-'=>'0/BTNMT-',
                '1 /BXD-HĐXD'=>'1/BXD-HĐXD', '2 /BXD-HĐXD'=>'2/BXD-HĐXD', '3 /BXD-HĐXD'=>'3/BXD-HĐXD', '4 /BXD-HĐXD'=>'4/BXD-HĐXD', '5 /BXD-HĐXD'=>'5/BXD-HĐXD', '6 /BXD-HĐXD'=>'6/BXD-HĐXD', '7 /BXD-HĐXD'=>'7/BXD-HĐXD', '8 /BXD-HĐXD'=>'8/BXD-HĐXD', '9 /BXD-HĐXD'=>'9/BXD-HĐXD', '0 /BXD-HĐXD'=>'0/BXD-HĐXD',
                '1 /CCT-'=>'1/CCT', '2 /CCT-'=>'2/CCT', '3 /CCT-'=>'3/CCT', '4 /CCT-'=>'4/CCT', '5 /CCT-'=>'5/CCT', '6 /CCT-'=>'6/CCT', '7 /CCT-'=>'7/CCT', '8 /CCT-'=>'8/CCT', '9 /CCT-'=>'9/CCT', '0 /CCT-'=>'0/CCT',
                '1 CĐ/BCT-ATMT'=>'1CĐ/BCT-ATMT', '2 CĐ/BCT-ATMT'=>'2CĐ/BCT-ATMT', '3 CĐ/BCT-ATMT'=>'3CĐ/BCT-ATMT', '4 CĐ/BCT-ATMT'=>'4CĐ/BCT-ATMT', '5 CĐ/BCT-ATMT'=>'5CĐ/BCT-ATMT', '6 CĐ/BCT-ATMT'=>'6CĐ/BCT-ATMT', '7 CĐ/BCT-ATMT'=>'7CĐ/BCT-ATMT', '8 CĐ/BCT-ATMT'=>'8CĐ/BCT-ATMT', '9 CĐ/BCT-ATMT'=>'9CĐ/BCT-ATMT', '0 CĐ/BCT-ATMT'=>'0CĐ/BCT-ATMT',
                '1 CTY/'=>'1CTY/', '2 CTY/'=>'2CTY/', '3 CTY/'=>'3CTY/', '4 CTY/'=>'4CTY/', '5 CTY/'=>'5CTY/', '6 CTY/'=>'6CTY/', '7 CTY/'=>'7CTY/', '8 CTY/'=>'8CTY/', '9 CTY/'=>'9CTY/', '0 CTY/'=>'0CTY/',
                '1 /CT'=>'1/CT', '2 /CT'=>'2/CT', '3 /CT'=>'3/CT', '4 /CT'=>'4/CT', '5 /CT'=>'5/CT', '6 /CT'=>'6/CT', '7 /CT'=>'7/CT', '8 /CT'=>'8/CT', '9 /CT'=>'9/CT', '0 /CT'=>'0/CT',
                '1/ CT'=>'1/CT', '2/ CT'=>'2/CT', '3/ CT'=>'3/CT', '4/ CT'=>'4/CT', '5/ CT'=>'5/CT', '6/ CT'=>'6/CT', '7/ CT'=>'7/CT', '8/ CT'=>'8/CT', '9/ CT'=>'9/CT', '0/ CT'=>'0/CT',
                '1 CV/'=>'1CV/', '2 CV/'=>'2CV/', '3 CV/'=>'3CV/', '4 CV/'=>'4CV/', '5 CV/'=>'5CV/', '6 CV/'=>'6CV/', '7 CV/'=>'7CV/', '8 CV/'=>'8CV/', '9 CV/'=>'9CV/', '0 CV/'=>'0CV/',
                '1 HTPT/BL-'=>'1HTPT/BL-', '2 HTPT/BL-'=>'2HTPT/BL-', '3 HTPT/BL-'=>'3HTPT/BL-', '4 HTPT/BL-'=>'4HTPT/BL-', '5 HTPT/BL-'=>'5HTPT/BL-', '6 HTPT/BL-'=>'6HTPT/BL-', '7 HTPT/BL-'=>'7HTPT/BL-', '8 HTPT/BL-'=>'8HTPT/BL-', '9 HTPT/BL-'=>'9HTPT/BL-', '0 HTPT/BL-'=>'0HTPT/BL-',
                '1 LĐ'=>'1LĐ', '2 LĐ'=>'2LĐ', '3 LĐ'=>'3LĐ', '4 LĐ'=>'4LĐ', '5 LĐ'=>'5LĐ', '6 LĐ'=>'6LĐ', '7 LĐ'=>'7LĐ', '8 LĐ'=>'8LĐ', '9 LĐ'=>'9LĐ', '0 LĐ'=>'0LĐ',
                '1 /LD'=>'1/LD', '2 /LD'=>'2/LD', '3 /LD'=>'3/LD', '4 /LD'=>'4/LD', '5 /LD'=>'5/LD', '6 /LD'=>'6/LD', '7 /LD'=>'7/LD', '8 /LD'=>'8/LD', '9 /LD'=>'9/LD', '0 /LD'=>'0/LD',
                '1 /LĐ'=>'1/LĐ', '2 /LĐ'=>'2/LĐ', '3 /LĐ'=>'3/LĐ', '4 /LĐ'=>'4/LĐ', '5 /LĐ'=>'5/LĐ', '6 /LĐ'=>'6/LĐ', '7 /LĐ'=>'7/LĐ', '8 /LĐ'=>'8/LĐ', '9 /LĐ'=>'9/LĐ', '0 /LĐ'=>'0/LĐ',
                '1 NQ'=>'1NQ', '2 NQ'=>'2NQ', '3 NQ'=>'3NQ', '4 NQ'=>'4NQ', '5 NQ'=>'5NQ', '6 NQ'=>'6NQ', '7 NQ'=>'7NQ', '8 NQ'=>'8NQ', '9 NQ'=>'9NQ', '0 NQ'=>'0NQ',
                '1 /2004/NQ-HĐ'=>'1/2004/NQ-HĐ', '2 /2004/NQ-HĐ'=>'2/2004/NQ-HĐ', '3 /2004/NQ-HĐ'=>'3/2004/NQ-HĐ', '4 /2004/NQ-HĐ'=>'4/2004/NQ-HĐ', '5 /2004/NQ-HĐ'=>'5/2004/NQ-HĐ', '6 /2004/NQ-HĐ'=>'6/2004/NQ-HĐ', '7 /2004/NQ-HĐ'=>'7/2004/NQ-HĐ', '8 /2004/NQ-HĐ'=>'8/2004/NQ-HĐ', '9 /2004/NQ-HĐ'=>'9/2004/NQ-HĐ', '0 /2004/NQ-HĐ'=>'0/2004/NQ-HĐ',
                '1 /QĐ-UBND'=>'1/QĐ-UBND', '2 /QĐ-UBND'=>'2/QĐ-UBND', '3 /QĐ-UBND'=>'3/QĐ-UBND', '4 /QĐ-UBND'=>'4/QĐ-UBND', '5 /QĐ-UBND'=>'5/QĐ-UBND', '6 /QĐ-UBND'=>'6/QĐ-UBND', '7 /QĐ-UBND'=>'7/QĐ-UBND', '8 /QĐ-UBND'=>'8/QĐ-UBND', '9 /QĐ-UBND'=>'9/QĐ-UBND', '0 /QĐ-UBND'=>'0/QĐ-UBND',
                '1 TP/'=>'1TP/', '2 TP/'=>'2TP/', '3 TP/'=>'3TP/', '4 TP/'=>'4TP/', '5 TP/'=>'5TP/', '6 TP/'=>'6TP/', '7 TP/'=>'7TP/', '8 TP/'=>'8TP/', '9 TP/'=>'9TP/', '0 TP/'=>'0TP/',
                '1 TCHQ/'=>'1TCHQ/', '2 TCHQ/'=>'2TCHQ/', '3 TCHQ/'=>'3TCHQ/', '4 TCHQ/'=>'4TCHQ/', '5 TCHQ/'=>'5TCHQ/', '6 TCHQ/'=>'6TCHQ/', '7 TCHQ/'=>'7TCHQ/', '8 TCHQ/'=>'8TCHQ/', '9 TCHQ/'=>'9TCHQ/', '0 TCHQ/'=>'0TCHQ/',
                '1 TCHQ-'=>'1TCHQ-', '2 TCHQ-'=>'2TCHQ-', '3 TCHQ-'=>'3TCHQ-', '4 TCHQ-'=>'4TCHQ-', '5 TCHQ-'=>'5TCHQ-', '6 TCHQ-'=>'6TCHQ-', '7 TCHQ-'=>'7TCHQ-', '8 TCHQ-'=>'8TCHQ-', '9 TCHQ-'=>'9TCHQ-', '0 TCHQ-'=>'0TCHQ-',
                '1 TCT'=>'1TCT', '2 TCT'=>'2TCT', '3 TCT'=>'3TCT', '4 TCT'=>'4TCT', '5 TCT'=>'5TCT', '6 TCT'=>'6TCT', '7 TCT'=>'7TCT', '8 TCT'=>'8TCT', '9 TCT'=>'9TCT', '0 TCT'=>'0TCT',
                '1 /TCT-'=>'1/TCT-', '2 /TCT-'=>'2/TCT-', '3 /TCT-'=>'3/TCT-', '4 /TCT-'=>'4/TCT-', '5 /TCT-'=>'5/TCT-', '6 /TCT-'=>'6/TCT-', '7 /TCT-'=>'7/TCT-', '8 /TCT-'=>'8/TCT-', '9 /TCT-'=>'9/TCT-', '0 /TCT-'=>'0/TCT-',
                '1/ TCT-'=>'1/TCT-', '2/ TCT-'=>'2/TCT-', '3/ TCT-'=>'3/TCT-', '4/ TCT-'=>'4/TCT-', '5/ TCT-'=>'5/TCT-', '6/ TCT-'=>'6/TCT-', '7/ TCT-'=>'7/TCT-', '8/ TCT-'=>'8/TCT-', '9/ TCT-'=>'9/TCT-', '0/ TCT-'=>'0/TCT-',
                '1/ TTg-KTN'=>'1/TTg-KTN', '2/ TTg-KTN'=>'2/TTg-KTN', '3/ TTg-KTN'=>'3/TTg-KTN', '4/ TTg-KTN'=>'4/TTg-KTN', '5/ TTg-KTN'=>'5/TTg-KTN', '6/ TTg-KTN'=>'6/TTg-KTN', '7/ TTg-KTN'=>'7/TTg-KTN', '8/ TTg-KTN'=>'8/TTg-KTN', '9/ TTg-KTN'=>'9/TTg-KTN', '0/ TTg-KTN'=>'0/TTg-KTN',

                //Weird Quotations
                "25/2015/QĐ-UBND''" => '25/2015/QĐ-UBND',
                "72/2013/QĐ-UBND'" => '72/2013/QĐ-UBND',

                //Missing a space
                'BảohiểmxãhộithànhphốHồ' => 'Bảo hiểm xã hội thành phố Hồ',
                'định1' => 'định 1',
                'ThuếThành' => 'Thuế Thành',
                'Tcủa' => 'T của',
                'báo1' => 'báo 1',
                'cáo1' => 'cáo 1',
                'số1' => 'số 1',
                'văn1' => 'văn 1',

                //Country Misspellings and Misscapitalizations
                ' Cộng hoà Chi Lê' => ' Cộng hòa Chi-lê',
                ' Cộng hòa Hồi giáo Iran' => ' Cộng hòa Hồi giáo I-ran',
                ' Cộng hoà dân chủ nhân dân Lào' => ' Cộng hòa dân chủ nhân dân Lào',

                //Origin Misspellings and Misscapitalizations
                ' của Quốc Hội' => ' của Quốc hội',
                ' của Ủy ban nhan dân tỉnh Bình Thuận' => ' của Ủy ban nhân dân tỉnh Bình Thuận',
                ' của Uỷ ban nhân dân thành phố Hà Nội' => ' của Uỷ ban nhân dân Thành phố Hà Nội',
                ' của Uỷ ban Thường vụ Quốc hội' => ' của Ủy ban Thường vụ Quốc hội',
                ' của Cục hàng hải Việt Nam' => ' của Cục Hàng hải Việt Nam',
                ' của Cục Thuế Thành phố Thành phố Hồ Chí Minh' => ' của Cục Thuế Thành phố Hồ Chí Minh',
                ' của Cục Thuế tính Bắc Ninh'  => ' của Cục Thuế tỉnh Bắc Ninh',
                ' của Cục Thuế TP. Hà Nội'     => ' của Cục Thuế Thành phố Hà Nội',
                ' của Cục Thuế TP Hải Phòng'   => ' của Cục Thuế Thành phố Hải Phòng',
                ' của Cục Thuế TP. Đà Nẵng'    => ' của Cục Thuế Thành phố Đà Nẵng',
                ' của Toà án nhân dân tối cao' => ' của Tòa án nhân dân tối cao',
                ' của Cục Thuế Tỉnh'          => ' của Cục Thuế tỉnh',
                ' của Cục Thuế thành phố' => ' của Cục Thuế Thành phố',
                ' của Cục thuế' => ' của Cục Thuế',
                ' của Tổng cục thuế' => ' của Tổng cục Thuế',

                //Topic Misspellings and Misscapitalizations
                'Quân nhân chuyên nghiệp, công nhân và viên chức Quốc phòng' => 'Quân nhân chuyên nghiệp, công nhân và viên chức quốc phòng',
                'thủ tục bắt giữ tàu biển' => 'Thủ tục bắt giữ tàu biển',
                'phòng, chống bạo lực gia đình' => 'Phòng, chống bạo lực gia đình',
                'phòng, chống bệnh truyền nhiễm' => 'Phòng, chống bệnh truyền nhiễm',
                'bảo vệ công trình quan trọng liên quan đến an ninh quốc gia' => 'Bảo vệ công trình quan trọng liên quan đến an ninh quốc gia',
                'chống trợ cấp hàng hóa nhập khẩu vào Việt Nam' => 'Chống trợ cấp hàng hóa nhập khẩu vào Việt Nam',
                'Tín ngưỡng, tôn giáo' => 'Tín ngưỡng, Tôn giáo',
                'Dự trữ Quốc gia' => 'Dự trữ quốc gia',
                'Thi đua, Khen thưởng' => 'Thi đua, khen thưởng',

                //Type Misspellings and Misscapitalizations
                'đặc xá' => 'Đặc xá',
                'người lao động Việt Nam đi làm việc ở nước ngoài theo hợp đồng' => 'Người lao động Việt Nam đi làm việc ở nước ngoài theo hợp đồng lao động',
                'Tổ chức Viện kiểm sát nhân dân' => 'Tổ chức Viện Kiểm sát nhân dân',
                'Thuế Thu nhập doanh nghiệp' => 'Thuế thu nhập doanh nghiệp',
                'ban hành văn bản quy phạm pháp luật' => 'Ban hành văn bản quy phạm pháp luật',
                'Phòng, chống ma tuý' => 'Phòng, chống ma túy',
                'Thông bao' => 'Thông báo',
                'Thông báo' => 'Thông báo',//Not the same UNICODE
                'Thông tư liên tich' => 'Thông tư liên tịch',
                'Thông tư Liên tịch' => 'Thông tư liên tịch',
                'Thông tư­ liên tịch' => 'Thông tư liên tịch',
                'Thống tư' => 'Thông tư',
                'Thông từ' => 'Thông tư',
                'cQuyết định' => 'Quyết định',//These all have different UNICODE
                'Quyết điịnh' => 'Quyết định',
                'Quyết địnhh' => 'Quyết định',
                'Quyết định' => 'Quyết định',
                'Quyết định' => 'Quyết định',
                'Quyết đinh' => 'Quyết định',
                'Quyêt định' => 'Quyết định',
                'Quỵết định' => 'Quyết định',
                'Quyết dịnh' => 'Quyết định',
                'Quyết định​' => 'Quyết định',
                'Quyét định' => 'Quyết định',
                'Quết định' => 'Quyết định',
                'Nghị đinh' => 'Nghị định',
                'Văn bản hơp nhất' => 'Văn bản hợp nhất',
                'Chị thị' => 'Chỉ thị',
                'Chỉ Thị' => 'Chỉ thị',
                'Chỉ thi' => 'Chỉ thị',
                'Huớng dẫn' => 'Hướng dẫn',
                'Hướng dẫn' => 'Hướng dẫn',
                'Côngvăn' => 'Công văn',
                'Côg văn' => 'Công văn',
                'Côn văn' => 'Công văn',
                'Cồn văn' => 'Công văn',
                'Công căn' => 'Công văn',
                'Cong văn' => 'Công văn',
                'Công Văn' => 'Công văn',
                'công văn' => 'Công văn',
                'CÔng văn' => 'Công văn',
                'C«ng v¨n' => 'Công văn',
                'Công văna' => 'Công văn',
                'Công văng' => 'Công văn',
                'Coong văn' => 'Công văn',
                'Công điện' => 'Công điện',
                'Nghị quyết' => 'Nghị quyết',//Not the same UNICODE
                'Kế hoạch' => 'Kế hoạch',//Not the same UNICODE
                'K​ế hoạch' => 'Kế hoạch',
                'Kế hoạch​' => 'Kế hoạch',
            ),

            'en'=>array(
                //Missing Spaces
                'No.1' => 'No. 1',
            )
        );
        //Sanitizes the ID
        $sanitizeID = array(
            'Đ'=>'D',
            '/'=>'-', '’'=>''
        );

        //Translates the topics
        $topics = array(
            'Phòng không nhân dân'           => 'Air Defense',
            'Đặc xá'                         => 'Amnesty',
            'Chăn nuôi'                      => 'Animal Husbandry',
            'Kiến trúc'                      => 'Architecture',
            'trưng mua, trưng dụng tài sản'  => 'Purchase and Seizure of Assets',
            'Tiếp cận thông tin'             => 'Access to Information',
            'Kế toán'                        => 'Accounting',
            'Tố tụng hành chính'             => 'Administrative Procedures',
            'Nuôi con nuôi'                  => 'Adoption',
            'Quảng cáo'                      => 'Advertizing',
            'Lưu trữ'                        => 'Archives',
            'năng lượng nguyên tử'           => 'Atomic Energy',
            'Đấu giá tài sản'                => 'Auction of assets',
            'Kiểm toán độc lập'              => 'Independent Audits',
            'Hàng không dân dụng Việt Nam'   => 'Civil Aviation of Vietnam',
            'Ngân hàng Nhà nước Việt Nam'    => 'The State Bank of Vietnam',
            'Phá sản'                        => 'Bankruptcy',
            'Đấu thầu'                       => 'Bidding',
            'Đa dạng sinh học'               => 'Biodiversity',
            'Biên giới Quốc gia'             => 'National Borders',
            'Thủ đô'                         => 'Capital',
            'Hóa chất'                       => 'Chemicals',
            'Trẻ em'                         => 'Children',
            'Bảo vệ, chăm sóc và giáo dục trẻ em' => 'Protection, Care and Education of Children',
            'Điện ảnh'                       => 'Cinema',
            'Hộ tịch'                        => 'Citizenship',
            'Tiếp công dân'                  => 'Citizen Reception',
            'Phòng thủ dân sự'               => 'Civil Defense',
            'Viên chức'                      => 'Civil Servants',
            'Cán bộ, công chức'              => 'Officials and Civil Servants',
            'Cảnh sát biển Việt Nam'         => 'The Coast Guard of Vietnam',
            'Thương mại'                     => 'Commerce',
            'Trọng tài thương mại'           => 'Commercial Arbitration',
            'Cạnh tranh'                     => 'Competition',
            'Khiếu nại'                      => 'Complaints',
            'Thực hành tiết kiệm, chống lãng phí' => 'Conservation and Anti-Waste Practices',
            'Xây dựng'                       => 'Construction',
            'Bảo vệ quyền lợi người tiêu dùng' => 'The Protection of Consumer Rights',
            'Thuế tiêu thụ đặc biệt'         => 'Special Consumption Tax',
            'Thi đua, khen thưởng'           => 'Contests and Awards',
            'Hợp tác xã'                     => 'Cooperatives',
            'Thuế thu nhập doanh nghiệp'     => 'Corporate Income Tax',
            'Doanh nghiệp nhà nước'          => 'State-Owned Corporations',
            'Doanh nghiệp'                   => 'Corporations',
            'Các tổ chức tín dụng'           => 'Credit Institutions',
            'Trồng trọt'                     => 'Crop Production',
            'Cơ yếu'                         => 'Cryptography',
            'Di sản văn hóa'                 => 'Cultural Heritage',
            'Hải quan'                       => 'Customs',
            'An ninh mạng'                   => 'Cybersecurity',
            'An toàn thông tin mạng'         => 'Cybersecurity',
            'Tố cáo'                         => 'Denunciation',
            'Bảo hiểm tiền gửi'              => 'Deposit Insurance',
            'Thi hành tạm giữ, tạm giam'     => 'Temporary Detention and Custody',
            'Người khuyết tật'               => 'Disabled Persons',
            'hiến, lấy, ghép mô, bộ phận cơ thể người và hiến, lấy xác' => 'Donation and Transplantation of Human Body Parts and Donation and Removal of Corpses',  
            'Đê điều'                        => 'Dykes',
            'Sử dụng năng lượng tiết kiệm và hiệu quả' => 'Economic and Efficient Energy Use',
            'Giáo dục đại học'               => 'Higher Education',
            'Giáo dục nghề nghiệp'           => 'Vocational Education',
            'Giáo dục quốc phòng và an ninh' => 'National Defense and Security Education',
            'Giáo dục'                       => 'Education',
            'Giao dịch điện tử'              => 'Electronic Transactions',
            'Biên phòng Việt Nam'            => 'The Border Guard of Vietnam',
            'Lực lượng tham gia bảo vệ an ninh, trật tự ở cơ' => 'Forces Participating in the Protection of Security and Order at the Grassroots Level',
            'Bảo hiểm y tế'                  => 'Health Insurance',
            'Người cao tuổi'                 => 'The Elderly',
            'Bầu cử Đại biểu Hội đồng nhân dân' => 'Election of the People’s Council',
            'Bầu cử đại biểu Quốc hội và đại biểu Hội đồng nhân dân' => 'Election of National Assembly Deputies and People’s Council Deputies',
            'Điện lực'                       => 'Electricity',
            'Việc làm'                       => 'Employment',
            'Nhập cảnh, xuất cảnh, quá cảnh, cư trú của người nước ngoài tại Việt Nam' => 'Entry, Exit, Transit, and Residence of Foreigners in Vietnam',
            'Bảo vệ môi trường'              => 'Environmental Protection',
            'Thuế bảo vệ môi trường'         => 'Environmental Protection Tax',
            'Thi hành án dân sự'             => 'Enforcement of Civil Judgements',
            'Thi hành án hình sự'            => 'Execution of Criminal Judgements',
            'Xuất cảnh, nhập cảnh của công dân Việt Nam' => 'Exit and Entry of Vietnamese Citizens',
            'Mặt trận Tổ quốc Việt Nam'      => 'The Vietnam Fatherland Front',
            'Phí và lệ phí'                  => 'Fees and Charges',
            'Phòng cháy và chữa cháy'        => 'Fire Prevention and Fighting',
            'Phòng cháy, chữa cháy và cứu nạn, cứu hộ' => 'Fire Prevention, Fire Fighting, Rescue and Relief',
            'Thủy sản'                       => 'Fisheries',
            'Vệ sinh an toàn thực phẩm'      => 'Food Safety and Hygiene',
            'An toàn thực phẩm'              => 'Food Safety',
            'Ngoại hối'                      => 'Foreign Exchange',
            'Quản lý ngoại thương'           => 'Foreign Trade Management',
            'Lâm nghiệp'                     => 'Forestry',
            'Bảo vệ và Phát triển rừng'      => 'Forest Protection and Development',
            'Bình đẳng giới'                 => 'Gender Equality',
            'Xử lý vi phạm hành chính'       => 'Handling of Administrative Violations',
            'Công nghệ cao'                  => 'High-Tech',
            'Nhà ở'                          => 'Housing',
            'Thực hiện dân chủ ở xã, phường, thị trấn' => 'Implementing Democracy at the Commune, Ward and Town Level',
            'Công nghệ thông tin'            => 'Information Technology',
            'Thanh tra'                      => 'Inspection',
            'Kinh doanh bảo hiểm'            => 'Insurance Business',
            'Sở hữu trí tuệ'                 => 'Intellectual Property',
            'Thủy lợi'                       => 'Irrigation',
            'Lý lịch tư pháp'                => 'Judicial Records',
            'Phòng, chống mua bán người'     => 'Prevention and Combat of Human Trafficking',
            'Quản lý nợ công'                => 'Public Debt Management',
            'Khí tượng thủy văn'             => 'Hydrometeorology',
            'Căn cước công dân'              => 'Citizen Identity Cards',
            'Căn cước'                       => 'Identity Cards',
            'Thực hiện dân chủ ở cơ sở'      => 'Implementing Democracy at the Local Level',
            'Thỏa thuận quốc tế'             => 'International Agreements',
            'Đầu tư theo phương thức đối tác công tư' => 'Investment in Public-Private Partnerships',
            'Đầu tư công'                    => 'Public Investment',
            'Đầu tư'                         => 'Investment',
            'Giám định tư pháp'              => 'Judicial Expertise',
            'Tư pháp người chưa thành niên'  => 'Juvenile Justice',
            'Đất đai'                        => 'Land',
            'Thuế sử dụng đất phi nông nghiệp' => 'Non-agricultural Land Use Taxes',
            'Luật sư'                        => 'Lawyers',
            'Trợ giúp pháp lý'               => 'Legal Aid',
            'tương trợ tư pháp'              => 'Legal Assistance',
            'Phổ biến, giáo dục pháp luật'   => 'Legal Education and Dissemination of Legal Knowledge',
            'Thư viện'                       => 'Libraries',
            'Hòa giải ở cơ sở'               => 'Local Reconcialiation',
            'Quản lý, bảo vệ công trình quốc phòng và khu quân sự' => 'Management and Protection of National Defense Facilities and Military Areas',
            'Quản lý, sử dụng tài sản công'  => 'Management and use of Public Assets',
            'Quản lý, sử dụng tài sản nhà nước' => 'Management and use of State Assets',
            'Quản lý, sử dụng vốn Nhà nước đầu tư vào sản xuất, kinh doanh tại doanh nghiệp' => 'Management and use of State Capital Invested in Production and Business at Enterprises',
            'Quản lý, sử dụng vũ khí, vật liệu nổ và công cụ hỗ trợ' => 'Management and use of Weapons, Explosives and Support Tools',
            'Hôn nhân và Gia đình'           => 'Marriage and Family',
            'Tài nguyên, môi trường biển và hải đảo' => 'Marine and Island Resources and Environment',
            'Đo lường'                       => 'Measurement',
            'Hòa giải, đối thoại tại Tòa án' => 'Mediation and Dialogue in Court',
            'Khám bệnh, chữa bệnh'           => 'Medical Examination and Treatment',
            'Nghĩa vụ quân sự'               => 'Military Service',
            'Dân quân tự vệ'                 => 'The Militia',
            'Khoáng sản'                     => 'Minerals',
            'Cảnh sát cơ động'               => 'Mobile Police',
            'Phòng chống rửa tiền'           => 'Money Laundering',
            'Quốc phòng'                     => 'National Defense',
            'Công nghiệp quốc phòng, an ninh và động viên công nghiệp' => 'National Defense Industry, Security and Industrial Encouragement',
            'Công nghiệp quốc phòng'         => 'The National Defense Industry',
            'Dự trữ Quốc gia'                => 'The National Reserve',
            'An ninh Quốc gia'               => 'National Security',
            'Quốc tịch Việt Nam'             => 'Vietnamese Nationality',
            'Phòng chống thiên tai'          => 'Natural Disaster Prevention',
            'Thuế tài nguyên'                => 'Natural Resources Tax',
            'Các công cụ chuyển nhượng'      => 'Negotiable Instruments',
            'Công chứng'                     => 'Notaries',
            'An toàn, vệ sinh lao động'      => 'Occupational Safety and Health',
            'Tổ chức Chính phủ'              => 'Government Organization',
            'Tổ chức điều tra hình sự'       => 'Organization of Criminal Investigation',
            'Tổ chức cơ quan điều tra hình sự' => 'Organization of Criminal Investigation Agencies',
            'Tổ chức chính quyền địa phương' => 'Organization of Local Government',
            'Tổ chức Quốc hội'               => 'Organization of the National Assembly',
            'Tổ chức Hội đồng nhân dân và Uỷ ban nhân dân' => 'Organization of the People’s Council and People’s Committee',
            'Tổ chức Tòa án nhân dân'        => 'Organization of the People’s Courts',
            'Tổ chức Viện Kiểm sát nhân dân' => 'Organization of the People’s Procuracy',
            'thuế thu nhập cá nhân'          => 'Personal Income Tax',
            'Dầu khí'                        => 'Petroleum',
            'Dược'                           => 'Pharmacy',
            'Quy hoạch đô thị và nông thôn'  => 'Urban and Rural Planning',
            'Quy hoạch đô thị'               => 'Urban Planning',
            'Quy hoạch'                      => 'Planning',
            'Bảo vệ và kiểm dịch thực vật'   => 'Plant Protection and Quarantine',
            'Công an xã'                     => 'Communal Police',
            'Bưu chính, viễn thông'          => 'Postage and Telecommunications',
            'Bưu chính'                      => 'Postage',
            'Ưu đãi người có công với cách mạng' => 'Preferential Treatment for Revolutionaries',
            'Báo chí'                        => 'The Press',
            'Chống trợ cấp hàng hóa nhập khẩu vào Việt Nam' => 'Prevention of Subsidies on Imported Goods Into Vietnam',
            'Chống bán phá giá hàng hóa nhập khẩu vào Việt Nam' => 'Prevention of the Dumping of Imported Goods Into Vietnam',
            'Phòng, chống tham nhũng'        => 'Prevention and Combat of Corruption',
            'Phòng, chống bạo lực gia đình'  => 'Prevention and Control of Domestic Violence',
            'Phòng, chống ma túy'            => 'Prevention and Control of Drugs',
            'Phòng, chống tác hại của rượu, bia' => 'Prevention and Control the Harmful Effects of Alcohol and Beer',
            'Phòng chống tác hại của thuốc lá' => 'Prevention and Control the Harmful Effects of Tobacco',
            'Phòng, chống rửa tiền'          => 'Prevention and Combat of Money Laundering',
            'Phòng, chống khủng bố'          => 'Prevention and Combat of Terrorism',
            'Phòng, chống bệnh truyền nhiễm' => 'Prevention and Control of Infectious Diseases',
            'Phòng, chống nhiễm vi rút gây ra hội chứng suy giảm miễn dịch mắc phải ở người (HIV/AIDS)' => 'Prevention and Control of Virus Infection That Causes Acquired Immunodeficiency Syndrome (HIV/AIDS)',
            'Giá'                            => 'Prices',
            'Thủ tục bắt giữ tàu biển'       => 'Procedures for Seizing Ships',
            'Quân nhân chuyên nghiệp, công nhân và viên chức quốc phòng' => 'Professional Military Personnel, Defense Workers and Officers',
            'Ban hành văn bản quy phạm pháp luật' => 'Promulgation of Legal Documents',
            'Bảo vệ công trình quan trọng liên quan đến an ninh quốc gia' => 'Protecting Important Facilities Related to National Security',
            'Xuất bản'                       => 'Publication',
            'chất lượng, sản phẩm hàng hoá'  => 'Quality, Product Goods',
            'Đường sắt'                      => 'Railways',
            'Kinh doanh bất động sản'        => 'Real Estate Business',
            'Hoạt động chữ thập đỏ'          => 'Red Cross Activities',
            'Trưng cầu ý dân'                => 'Referendums',
            'Tiêu chuẩn và Quy chuẩn kỹ thuật' => 'Standards and Technical Regulations',
            'Cơ quan đại diện nước Cộng hòa xã hội chủ nghĩa Việt Nam ở nước ngoài' => 'Representative Offices of the Socialist Republic of Vietnam Abroad',
            'Tín ngưỡng, Tôn giáo'           => 'Religion and Belief',
            'Lực lượng dự bị động viên'      => 'Reserve Forces',
            'Cư trú'                         => 'Residence',
            'Khoa học và công nghệ'          => 'Science and Technology',
            'Biển Việt Nam'                  => 'The Sea of Vietnam',
            'Cảnh vệ'                        => 'Security',
            'Công an nhân dân'               => 'Public Security',
            'Bảo hiểm xã hội'                => 'Social Security',
            'Ký kết và Thực hiện thoả thuận quốc tế' => 'Signing and Implementing International Agreements',
            'Thể dục, Thể thao'              => 'Physical and Digital Sports',
            'Kiểm toán Nhà nước'             => 'State Audit',
            'Ngân sách Nhà nước'             => 'State Budget',
            'Trách nhiệm bồi thường của Nhà nước' => 'State Compensation Liability',
            'Bảo vệ bí mật Nhà nước'         => 'The Protection of State Secrets',
            'Thống kê'                       => 'Statistics',
            'Chứng khoán'                    => 'Stocks',
            'Hoạt động giám sát'             => 'Supervision',
            'Hoạt động giám'                 => 'Supervision',
            'Hỗ trợ doanh nghiệp nhỏ và vừa' => 'Support for Small and Medium Enterprises',
            'Đo đạc và bản đồ'               => 'Surveying and Mapping',
            'Quản lý thuế'                   => 'Tax Administration',
            'Viễn thông'                     => 'Telecommunications',
            'Chuyển giao công nghệ'          => 'Digital Technology Transfer',
            'Du lịch'                        => 'Tourism',
            'Công đoàn'                      => 'Trade Unions',
            'Tần số vô tuyến điện'           => 'Radio Frequencies',
            'Đường bộ'                       => 'Roads',
            'Giao thông đường bộ'            => 'Road Traffic',
            'Trật tự, an toàn giao thông đường bộ' => 'Road Traffic Order and Safety',
            'Ký kết, gia nhập và thực hiện điều ước quốc tế' => 'Ký kết, gia nhập và thực hiện điều ước quốc tế; Signing, Accession to and Implementation of International Treaties',
            'Quy hoạch đô thị'               => 'Urban Planning',
            'Điều ước quốc tế'               => 'International Treaties',
            'Thuế xuất khẩu, thuế nhập khẩu' => 'Export and Import Tax',
            'Thuế giá trị gia tăng'          => 'Value Added Tax',
            'Cựu chiến binh'                 => 'Veterans',
            'Thú y'                          => 'Veterinary Medicine',
            'Người lao động Việt Nam đi làm việc ở nước ngoài theo hợp đồng lao động' => 'Vietnamese Workers Working Abroad Under Labor Contracts',
            'Người lao động Việt Nam đi làm việc ở nước ngoài theo hợp đồng' => 'Vietnamese Workers Working Abroad Under Contract',
            'Dạy nghề'                       => 'Vocational Training',
            'Tài nguyên nước'                => 'Water Resources',
            'Giao thông đường thủy nội địa'  => 'Inland Waterways',
            'Thanh niên'                     => 'The Youth',
        );
        //Translates the origins
        $origins = array(
            ' của Cục Hàng không Việt Nam' => [["vi"=>"Cục Hàng không Việt Nam", "en"=>"The Civil Aviation Administration of Vietnam"], ["VN"]],
            ' của Cục Hàng hải Việt Nam' => [["vi"=>"Cục Hàng hải Việt Nam", "en"=>"The Vietnam Maritime Administration"], ["VN"]],
            ' của Cục Quản lý Dược' => [["vi"=>"Cục Quản lý Dược", "en"=>"The Administration of Drugs"], ["VN"]],
            ' của Tổng cục Đường bộ Việt Nam' => [["vi"=>"Tổng cục Đường bộ Việt Nam", "en"=>"The Vietnam Road Administration"], ["VN"]],
            ' của Cục Điều tiết điện lực' => [["vi"=>"Cục Điều tiết điện lực", "en"=>"The of Electricity Regulatory Authority"], ["VN"]],

            ' của Quốc hội' => [["vi"=>"Quốc hội", "en"=>"The National Assembly"], ["VN"]],

            ' của Ngân hàng Chính sách xã hội' => [["vi"=>"Ngân hàng Chính sách xã hội", "en"=>"The Social Policy Bank"], ["VN"]],
            ' của Ngân hàng Nhà nước Việt Nam' => [["vi"=>"Ngân hàng Nhà nước Việt Nam", "en"=>"The State Bank of Vietnam"], ["VN"]],

            ' của Ủy ban Dân tộc' => [["vi"=>"Ủy ban Dân tộc", "en"=>"The Committee of Nationalities"], ["VN"]],

            ' của Ủy ban nhân dân tỉnh An Giang'          => [["vi"=>"Ủy ban nhân dân tỉnh An Giang", "en"=>"The People’s Committee of An Giang Province"], ["VN-44"]],
            ' của Ủy ban nhân dân tỉnh Bà Rịa - Vũng Tàu' => [["vi"=>"Ủy ban nhân dân tỉnh Bà Rịa - Vũng Tàu", "en"=>"The People’s Committee of Ba Ria - Vung Tau Province"], ["VN-43"]],
            ' của Ủy ban nhân dân tỉnh Bắc Giang'         => [["vi"=>"Ủy ban nhân dân tỉnh Bắc Giang", "en"=>"The People’s Committee of Bac Giang Province"], ["VN-54"]],
            ' của Ủy ban nhân dân tỉnh Bắc Kạn'           => [["vi"=>"Ủy ban nhân dân tỉnh Bắc Kạn", "en"=>"The People’s Committee of Bac Kan Province"], ["VN-53"]],
            ' của Ủy ban nhân dân tỉnh Bạc Liêu'          => [["vi"=>"Ủy ban nhân dân tỉnh Bạc Liêu", "en"=>"The People’s Committee of Bac Lieu Province"], ["VN-55"]],
            ' của Ủy ban nhân dân tỉnh Bắc Ninh'          => [["vi"=>"Ủy ban nhân dân tỉnh Bắc Ninh", "en"=>"The People’s Committee of Bac Ninh Province"], ["VN-56"]],
            ' của Ủy ban nhân dân tỉnh Bến Tre'           => [["vi"=>"Ủy ban nhân dân tỉnh Bến Tre", "en"=>"The People’s Committee of Ben Tre Province"], ["VN-50"]],
            ' của Ủy ban nhân dân tỉnh Bình Định'         => [["vi"=>"Ủy ban nhân dân tỉnh Bình Định", "en"=>"The People’s Committee of Binh Dinh Province"], ["VN-31"]],
            ' của Ủy ban nhân dân tỉnh Bình Dương'        => [["vi"=>"Ủy ban nhân dân tỉnh Bình Dương", "en"=>"The People’s Committee of Binh Duong Province"], ["VN-57"]],
            ' của Ủy ban nhân dân tỉnh Bình Phước'        => [["vi"=>"Ủy ban nhân dân tỉnh Bình Phước", "en"=>"The People’s Committee of Binh Phuoc Province"], ["VN-58"]],
            ' của Ủy ban nhân dân tỉnh Bình Thuận'        => [["vi"=>"Ủy ban nhân dân tỉnh Bình Thuận", "en"=>"The People’s Committee of Binh Thuan Province"], ["VN-40"]],
            ' Ủy ban nhân dân tỉnh Bình Thuận'            => [["vi"=>"Ủy ban nhân dân tỉnh Bình Thuận", "en"=>"The People’s Committee of Binh Thuan Province"], ["VN-40"]],
            ' của Ủy ban nhân dân tỉnh Cà Mau'            => [["vi"=>"Ủy ban nhân dân tỉnh Cà Mau", "en"=>"The People’s Committee of Ca Mau Province"], ["VN-59"]],
            ' của Ủy ban nhân dân tỉnh Cao Bằng'          => [["vi"=>"Ủy ban nhân dân tỉnh Cao Bằng", "en"=>"The People’s Committee of Cao Bang Province"], ["VN-04"]],
            ' của Ủy ban nhân dân tỉnh Đắk Lắk'           => [["vi"=>"Ủy ban nhân dân tỉnh Đắk Lắk", "en"=>"The People’s Committee of Dak Lak Province"], ["VN-33"]],
            ' của Ủy ban nhân dân tỉnh Đắk Nông'          => [["vi"=>"Ủy ban nhân dân tỉnh Đắk Nông", "en"=>"The People’s Committee of Dak Nong Province"], ["VN-72"]],
            ' của Ủy ban nhân dân tỉnh Điện Biên'         => [["vi"=>"Ủy ban nhân dân tỉnh Điện Biên", "en"=>"The People’s Committee of Dien Bien Province"], ["VN-71"]],
            ' của Ủy ban nhân dân tỉnh Đồng Nai'          => [["vi"=>"Ủy ban nhân dân tỉnh Đồng Nai", "en"=>"The People’s Committee of Dong Nai Province"], ["VN-39"]],
            ' của Ủy ban nhân dân tỉnh Đồng Tháp'         => [["vi"=>"Ủy ban nhân dân tỉnh Đồng Tháp", "en"=>"The People’s Committee of Dong Thap Province"], ["VN-45"]],
            ' của Ủy ban nhân dân tỉnh Gia Lai'           => [["vi"=>"Ủy ban nhân dân tỉnh Gia Lai", "en"=>"The People’s Committee of Gia Lai Province"], ["VN-30"]],
            ' của Ủy ban nhân dân tỉnh Hà Giang'          => [["vi"=>"Ủy ban nhân dân tỉnh Hà Giang", "en"=>"The People’s Committee of Ha Giang Province"], ["VN-03"]],
            ' của Ủy ban nhân dân tỉnh Hà Nam'            => [["vi"=>"Ủy ban nhân dân tỉnh Hà Nam", "en"=>"The People’s Committee of Ha Nam Province"], ["VN-63"]],
            ' của Ủy ban nhân dân tỉnh Hà Tĩnh'           => [["vi"=>"Ủy ban nhân dân tỉnh Hà Tĩnh", "en"=>"The People’s Committee of Ha Tinh Province"], ["VN-23"]],
            ' của Ủy ban nhân dân tỉnh Hải Dương'         => [["vi"=>"Ủy ban nhân dân tỉnh Hải Dương", "en"=>"The People’s Committee of Hai Duong Province"], ["VN-61"]],
            ' của Ủy ban nhân dân tỉnh Hậu Giang'         => [["vi"=>"Ủy ban nhân dân tỉnh Hậu Giang", "en"=>"The People’s Committee of Hau Giang Province"], ["VN-73"]],
            ' của Ủy ban nhân dân tỉnh Hòa Bình'          => [["vi"=>"Ủy ban nhân dân tỉnh Hòa Bình", "en"=>"The People’s Committee of Hoa Binh Province"], ["VN-14"]],
            ' của Ủy ban nhân dân tỉnh Hưng Yên'          => [["vi"=>"Ủy ban nhân dân tỉnh Hưng Yên", "en"=>"The People’s Committee of Hung Yen Province"], ["VN-66"]],
            ' của Ủy ban nhân dân tỉnh Khánh Hòa'         => [["vi"=>"Ủy ban nhân dân tỉnh Khánh Hòa", "en"=>"The People’s Committee of Khanh Hoa Province"], ["VN-34"]],
            ' của Ủy ban nhân dân tỉnh Kiên Giang'        => [["vi"=>"Ủy ban nhân dân tỉnh Kiên Giang", "en"=>"The People’s Committee of Kien Giang Province"], ["VN-47"]],
            ' của Ủy ban nhân dân tỉnh Kon Tum'           => [["vi"=>"Ủy ban nhân dân tỉnh Kon Tum", "en"=>"The People’s Committee of Kon Tum Province"], ["VN-28"]],
            ' của Ủy ban nhân dân tỉnh Lai Châu'          => [["vi"=>"Ủy ban nhân dân tỉnh Lai Châu", "en"=>"The People’s Committee of Lai Chau Province"], ["VN-02"]],
            ' của Ủy ban nhân dân tỉnh Lâm Đồng'          => [["vi"=>"Ủy ban nhân dân tỉnh Lâm Đồng", "en"=>"The People’s Committee of Lam Dong Province"], ["VN-35"]],
            ' của Ủy ban nhân dân tỉnh Lạng Sơn'          => [["vi"=>"Ủy ban nhân dân tỉnh Lạng Sơn", "en"=>"The People’s Committee of Lang Son Province"], ["VN-09"]],
            ' của Ủy ban nhân dân tỉnh Lào Cai'           => [["vi"=>"Ủy ban nhân dân tỉnh Lào Cai", "en"=>"The People’s Committee of Lao Cai Province"], ["VN-01"]],
            ' của Ủy ban nhân dân tỉnh Long An'           => [["vi"=>"Ủy ban nhân dân tỉnh Long An", "en"=>"The People’s Committee of Long An Province"], ["VN-41"]],
            ' của Ủy ban nhân dân tỉnh Nam Định'          => [["vi"=>"Ủy ban nhân dân tỉnh Nam Định", "en"=>"The People’s Committee of Nam Dinh Province"], ["VN-67"]],
            ' của Ủy ban nhân dân tỉnh Nghệ An'           => [["vi"=>"Ủy ban nhân dân tỉnh Nghệ An", "en"=>"The People’s Committee of Nghe An Province"], ["VN-22"]],
            ' của Ủy ban nhân dân tỉnh Ninh Bình'         => [["vi"=>"Ủy ban nhân dân tỉnh Ninh Bình", "en"=>"The People’s Committee of Ninh Binh Province"], ["VN-18"]],
            ' của Ủy ban nhân dân tỉnh Ninh Thuận'        => [["vi"=>"Ủy ban nhân dân tỉnh Ninh Thuận", "en"=>"The People’s Committee of Ninh Thuan Province"], ["VN-36"]],
            ' của Ủy ban nhân dân tỉnh Phú Thọ'           => [["vi"=>"Ủy ban nhân dân tỉnh Phú Thọ", "en"=>"The People’s Committee of Phu Tho Province"], ["VN-68"]],
            ' của Ủy ban nhân dân tỉnh Phú Yên'           => [["vi"=>"Ủy ban nhân dân tỉnh Phú Yên", "en"=>"The People’s Committee of Phu Yen Province"], ["VN-32"]],
            ' của Ủy ban nhân dân tỉnh Quảng Bình'        => [["vi"=>"Ủy ban nhân dân tỉnh Quảng Bình", "en"=>"The People’s Committee of Quang Binh Province"], ["VN-24"]],
            ' của Ủy ban nhân dân tỉnh Quảng Nam'         => [["vi"=>"Ủy ban nhân dân tỉnh Quảng Nam", "en"=>"The People’s Committee of Quang Nam Province"], ["VN-27"]],
            ' của Ủy ban nhân dân tỉnh Quảng Ngãi'        => [["vi"=>"Ủy ban nhân dân tỉnh Quảng Ngãi", "en"=>"The People’s Committee of Quang Ngai Province"], ["VN-29"]],
            ' của Ủy ban nhân dân tỉnh Quảng Ninh'        => [["vi"=>"Ủy ban nhân dân tỉnh Quảng Ninh", "en"=>"The People’s Committee of Quang Ninh Province"], ["VN-13"]],
            ' của Ủy ban nhân dân tỉnh Quảng Trị'         => [["vi"=>"Ủy ban nhân dân tỉnh Quảng Trị", "en"=>"The People’s Committee of Quang Tri Province"], ["VN-25"]],
            ' của Ủy ban nhân dân tỉnh Sóc Trăng'         => [["vi"=>"Ủy ban nhân dân tỉnh Sóc Trăng", "en"=>"The People’s Committee of Soc Trang Province"], ["VN-52"]],
            ' của Ủy ban nhân dân tỉnh Sơn La'            => [["vi"=>"Ủy ban nhân dân tỉnh Sơn La", "en"=>"The People’s Committee of Son La Province"], ["VN-05"]],
            ' của Ủy ban nhân dân tỉnh Tây Ninh'          => [["vi"=>"Ủy ban nhân dân tỉnh Tây Ninh", "en"=>"The People’s Committee of Tay Ninh Province"], ["VN-37"]],
            ' của Ủy ban nhân dân tỉnh Thái Bình'         => [["vi"=>"Ủy ban nhân dân tỉnh Thái Bình", "en"=>"The People’s Committee of Thai Binh Province"], ["VN-20"]],
            ' của Ủy ban nhân dân tỉnh Thái Nguyên'       => [["vi"=>"Ủy ban nhân dân tỉnh Thái Nguyên", "en"=>"The People’s Committee of Thai Nguyen Province"], ["VN-69"]],
            ' của Ủy ban nhân dân tỉnh Thanh Hóa'         => [["vi"=>"Ủy ban nhân dân tỉnh Thanh Hóa", "en"=>"The People’s Committee of Thanh Hoa Province"], ["VN-21"]],
            ' của Ủy ban nhân dân tỉnh Thừa Thiên Huế'    => [["vi"=>"Ủy ban nhân dân tỉnh Thừa Thiên Huế", "en"=>"The People’s Committee of Thua Thien Hue Province"], ["VN-26"]],
            ' của Ủy ban nhân dân tỉnh Tiền Giang'        => [["vi"=>"Ủy ban nhân dân tỉnh Tiền Giang", "en"=>"The People’s Committee of Tien Giang Province"], ["VN-46"]],
            ' của Ủy ban nhân dân tỉnh Trà Vinh'          => [["vi"=>"Ủy ban nhân dân tỉnh Trà Vinh", "en"=>"The People’s Committee of Tra Vinh Province"], ["VN-51"]],
            ' của Ủy ban nhân dân tỉnh Tuyên Quang'       => [["vi"=>"Ủy ban nhân dân tỉnh Tuyên Quang", "en"=>"The People’s Committee of Tuyen Quang Province"], ["VN-07"]],
            ' của Ủy ban nhân dân tỉnh Vĩnh Long'         => [["vi"=>"Ủy ban nhân dân tỉnh Vĩnh Long", "en"=>"The People’s Committee of Vinh Long Province"], ["VN-49"]],
            ' của Ủy ban nhân dân tỉnh Vĩnh Phúc'         => [["vi"=>"Ủy ban nhân dân tỉnh Vĩnh Phúc", "en"=>"The People’s Committee of Vinh Phuc Province"], ["VN-70"]],
            ' của Ủy ban nhân dân tỉnh Yên Bái'           => [["vi"=>"Ủy ban nhân dân tỉnh Yên Bái", "en"=>"The People’s Committee of Yen Bai Province"], ["VN-06"]],
            ' của Ủy ban nhân dân Thành phố Hà Nội'       => [["vi"=>"Ủy ban nhân dân Thành phố Hà Nội", "en"=>"The People’s Committee of Hanoi"], ["VN-HN"]],
            ' của Ủy ban nhân dân Thành phố Hồ Chí Minh'  => [["vi"=>"Ủy ban nhân dân Thành phố Hồ Chí Minh", "en"=>"The People’s Committee of Ho Chi Minh City"], ["VN-SG"]],
            ' của Ủy ban nhân dân Thành phố Hải Phòng'    => [["vi"=>"Ủy ban nhân dân Thành phố Hải Phòng", "en"=>"The People’s Committee of Hai Phong City"], ["VN-HP"]],
            ' của Ủy ban nhân dân Thành phố Đà Nẵng'      => [["vi"=>"Ủy ban nhân dân Thành phố Đà Nẵng", "en"=>"The People’s Committee of Da Nang City"], ["VN-DN"]],
            ' của Ủy ban nhân dân Thành phố Cần Thơ'      => [["vi"=>"Ủy ban nhân dân Thành phố Cần Thơ", "en"=>"The People’s Committee of Can Tho City"], ["VN-CT"]],
            ' của Ủy ban nhân dân Thành phố Vĩnh Long'    => [["vi"=>"Ủy ban nhân dân Thành phố Vĩnh Long", "en"=>"The People’s Committee of Vinh Long City"], ["VN-49"]],

            ' của Ban Chấp hành Trung ương' => [["vi"=>"Ban Chấp hành Trung ương", "en"=>"The Central Executive Committee"], ["VN"]],
            ' của Ban Tuyên giáo Trung ương'       => [["vi"=>"Ban Tuyên giáo Trung ương", "en"=>"The Central Propaganda Committee"], ["VN"]],
            ' của Ủy ban Thường vụ Quốc hội' => [["vi"=>"Ủy ban Thường vụ Quốc hội", "en"=>"The Standing Committee of the National Assembly"], ["VN"]],
            ' của Kiểm toán Nhà nước' => [["vi"=>"Kiểm toán Nhà nước", "en"=>"The State Audit"], ["VN"]],

            ' và Hội đồng nhân dân' => [["vi"=>"Hội đồng nhân dân", "en"=>"The People’s Council"], ["VN"]],
            ' của Hội đồng nhân dân' => [["vi"=>"Hội đồng nhân dân", "en"=>"The People’s Council"], ["VN"]],

            ' của Tòa án nhân dân tối cao' => [["vi"=>"Tòa án nhân dân tối cao", "en"=>"The Supreme People’s Court"], ["VN"]],
            ' của Viện kiểm sát nhân dân tối cao' => [["vi"=>"Viện kiểm sát nhân dân tối cao", "en"=>"The Supreme People’s Procuracy"], ["VN"]],

            ' của Vụ Tổ chức cán bộ Bộ Tư pháp' => [["vi"=>"Vụ Tổ chức cán bộ Bộ Tư pháp", "en"=>"The Department of Personnel Organization of the Ministry of Justice"], ["VN"]],
            
            ' của Cục Trồng trọt'                  => [["vi"=>"Cục Trồng trọt", "en"=>"The Department of Agriculture"], ["VN"]],
            ' của Cục Kế toán Nhà nước Kho bạc Nhà nước' => [["vi"=>"Cục Kế toán Nhà nước Kho bạc Nhà nước", "en"=>"The State Accounting Department of the State Treasury"], ["VN"]],
            ' của Cục Chăn nuôi'                   => [["vi"=>"Cục Chăn nuôi", "en"=>"The Department of Animal Husbandry"], ["VN"]],
            ' của Cục Quản lý đăng ký kinh doanh'  => [["vi"=>"Cục Quản lý đăng ký kinh doanh", "en"=>"The Department of Business Registration Management"], ["VN"]],
            ' của Cục Điện ảnh'                    => [["vi"=>"Cục Điện ảnh", "en"=>"The Department of Cinema"], ["VN"]],
            ' của Cục Hộ tịch, quốc tịch, chứng thực' => [["vi"=>"Cục Hộ tịch, quốc tịch, chứng thực", "en"=>"The Department of Civil Status, Nationality and Identification"], ["VN"]],
            ' của Cục Biến đổi khí hậu'            => [["vi"=>"Cục Biến đổi khí hậu", "en"=>"The Department of Climate Change"], ["VN"]],
            ' của Cục Lãnh sự'                     => [["vi"=>"Cục Lãnh sự", "en"=>"The Department of Consular Affairs"], ["VN"]],
            ' của Tổng cục Hải quan'               => [["vi"=>"Tổng cục Hải quan", "en"=>"The General Customs Department"], ["VN"]],
            ' của Cục Quản lý đê điều và Phòng, chống thiên tai' => [["vi"=>"Cục Quản lý đê điều và Phòng, chống thiên tai", "en"=>"The Department of Dyke Management and Natural Disaster Prevention and Control"], ["VN"]],
            ' của Cục Kinh tế hợp tác và Phát triển nông thôn' => [["vi"=>"Cục Kinh tế hợp tác và Phát triển nông thôn", "en"=>"The Department of Cooperative Economy and Rural Development"], ["VN"]],
            ' của Tổng cục Giáo dục nghề nghiệp' => [["vi"=>"Giáo dục nghề nghiệp", "en"=>"The General Department of Vocational Education"], ["VN"]],
            ' của Sở Giáo dục và Đào tạo'          => [["vi"=>"Sở Giáo dục và Đào tạo", "en"=>"The Department of Education and Training"], ["VN"]],
            ' của Cục Việc làm'                    => [["vi"=>"Cục Việc làm", "en"=>"The Department of Employment"], ["VN"]],
            ' của Cục Kiểm soát ô nhiễm môi trường' => [["vi"=>"Cục Kiểm soát ô nhiễm môi trường", "en"=>"The Department of Environmental and Pollution Control"], ["VN"]],
            ' của Cục Cảnh sát Phòng cháy chữa cháy và Cứu' => [["vi"=>"Cục Cảnh sát Phòng cháy chữa cháy và Cứu", "en"=>"The Department of Fire Prevention and Rescue Police"], ["VN"]],
            ' của Cục Thủy sản'                    => [["vi"=>"Cục Thủy sản", "en"=>"The Department of Fisheries"], ["VN"]],
            ' của Cục Lâm nghiệp'                  => [["vi"=>"Cục Lâm nghiệp", "en"=>"The Department of Forestry"], ["VN"]],
            ' của Cục Kiểm lâm'                    => [["vi"=>"Cục Kiểm lâm", "en"=>"The Department of Forest Protection"], ["VN"]],
            ' của Cục Phòng, chống HIV/AIDS'       => [["vi"=>"Cục Phòng, chống HIV/AIDS", "en"=>"The Department of HIV/AIDS Prevention and Control"], ["VN"]],
            ' của Cục Xuất nhập khẩu'              => [["vi"=>"Cục Xuất nhập khẩu", "en"=>"The Department of Imports and Exports"], ["VN"]],
            ' của Cục Thuế xuất nhập khẩu'         => [["vi"=>"Cục Thuế xuất nhập khẩu", "en"=>"The Department of Import and Export Taxes"], ["VN"]],
            ' của Cục An toàn thông tin'           => [["vi"=>"Cục An toàn thông tin", "en"=>"The Department of Information Security"], ["VN"]],
            ' của Cục Công nghệ thông tin'         => [["vi"=>"Cục Công nghệ thông tin", "en"=>"The Department of Information Technology"], ["VN"]],
            ' của Cục Quản lý, giám sát bảo hiểm'  => [["vi"=>"Cục Quản lý, giám sát bảo hiểm", "en"=>"The Department of Insurance Management and Supervision"], ["VN"]],
            ' của Cục Sở hữu trí tuệ'              => [["vi"=>"Cục Sở hữu trí tuệ", "en"=>"The Department of Intellectual Property"], ["VN"]],
            ' của Cục Thuế doanh nghiệp lớn'       => [["vi"=>"Cục Thuế doanh nghiệp lớn", "en"=>"The Department of Large Enterprise Taxation"], ["VN"]],
            ' của Cục Quản lý môi trường y tế'     => [["vi"=>"Cục Quản lý môi trường y tế", "en"=>"The Department of Environmental Health Management"], ["VN"]],
            ' của Cục Thuỷ sản'                    => [["vi"=>"Cục Thuỷ sản", "en"=>"The Department of Fisheries"], ["VN"]],
            ' của Cục An toàn thực phẩm'           => [["vi"=>"Cục An toàn thực phẩm", "en"=>"The Department of Food Safety"], ["VN"]],
            ' của Cục An toàn lao động'            => [["vi"=>"Cục An toàn lao động", "en"=>"The Department of Labor Safety"], ["VN"]],
            ' của Cục Đăng ký và Dữ liệu thông tin đất đai' => [["vi"=>"Cục Đăng ký và Dữ liệu thông tin đất đai", "en"=>"The Department of Land Registration and Information Data"], ["VN"]],
            ' của Cục Giám sát quản lý'            => [["vi"=>"Cục Giám sát quản lý", "en"=>"The Department of Management and Supervision"], ["VN"]],
            ' của Cục Quản lý khám, chữa bệnh'     => [["vi"=>"Cục Quản lý khám, chữa bệnh", "en"=>"The Department of Medical Examination and Treatment Management"], ["VN"]],
            ' của Cục Quản lý Y, Dược cổ truyền'   => [["vi"=>"Cục Quản lý Y, Dược cổ truyền", "en"=>"The Department of Traditional Medicine and Pharmacy Management"], ["VN"]],
            ' của Cục Quản lý lao động ngoài nước' => [["vi"=>"Cục Quản lý lao động ngoài nước", "en"=>"The Department of Overseas Labor Management"], ["VN"]],
            ' của Cục Thể dục thể thao'            => [["vi"=>"Cục Thể dục thể thao", "en"=>"The Department of Sports and Physical Education"], ["VN"]],
            ' của Cục Thương mại điện tử và Kinh tế số' => [["vi"=>"Cục Thương mại điện tử và Kinh tế số", "en"=>"The Department of E-commerce and Digital Economy"], ["VN"]],
            ' của Cục Bảo vệ thực vật'             => [["vi"=>"Cục Bảo vệ thực vật", "en"=>"The Department of Plant Protection"], ["VN"]],
            ' của Cục Y tế dự phòng'               => [["vi"=>"Cục Y tế dự phòng", "en"=>"The Department of Preventive Medicine"], ["VN"]],
            ' của Cục Quản lý chất lượng Bộ Giáo dục và Đào tạo' => [["vi"=>"Cục Quản lý chất lượng Bộ Giáo dục và Đào tạo", "en"=>"The Department of Quality Management of the Ministry of Education and Training"], ["VN"]],
            ' của Cục Quản lý chất lượng'          => [["vi"=>"Cục Quản lý chất lượng", "en"=>"The Department of Quality Management"], ["VN"]],
            ' của Cục Đường sắt Việt Nam'          => [["vi"=>"Cục Đường sắt Việt Nam", "en"=>"The Department of Railways of Vietnam"], ["VN"]],
            ' của Cục Đường bộ Việt Nam'           => [["vi"=>"Cục Đường bộ Việt Nam", "en"=>"The Department of Roads of Vietnam"], ["VN"]],
            ' của Cục Thông tin khoa học và công nghệ quốc gia' => [["vi"=>"Cục Thông tin khoa học và công nghệ quốc gia", "en"=>"The Department of Scientific and Technological Information"], ["VN"]],
            ' của Cục Thủy lợi'                    => [["vi"=>"Cục Thủy lợi", "en"=>"The Department of Irrigation"], ["VN"]],

            ' của Sở Nông nghiệp và Phát triển nông thôn Thành phố Hà Nội' => [["vi"=>"Sở Nông nghiệp và Phát triển nông thôn Thành phố Hà Nội", "en"=>"The Hanoi City Department of Agriculture and Rural Development"], ["VN-HN"]],
            ' của Sở Xây dựng Thành phố Hồ Chí Minh'            => [["vi"=>"Sở Xây dựng Thành phố Hồ Chí Minh", "en"=>"The Ho Chi Minh City Department of Construction"], ["VN-SG"]],
            ' của Cục Hải quan Thành phố Hồ Chí Minh'           => [["vi"=>"Cục Hải quan Thành phố Hồ Chí Minh", "en"=>"The Ho Chi Minh City Customs Department"], ["VN-SG"]],
            ' của Sở Giáo dục và Đào tạo Thành phố Hồ Chí Minh' => [["vi"=>"Sở Giáo dục và Đào tạo Thành phố Hồ Chí Minh", "en"=>"The Ho Chi Minh City Department of Education and Training"], ["VN-SG"]],
            ' của Sở Giáo dục và Đào tạo Thành phố Hà Nội'      => [["vi"=>"Sở Giáo dục và Đào tạo Thành phố Hà Nội", "en"=>"The Hanoi City Department of Education and Training"], ["VN-HN"]],
            ' của Sở Y tế Thành phố Hồ Chí Minh'                => [["vi"=>"Sở Y tế Thành phố Hồ Chí Minh", "en"=>"The Ho Chi Minh City Department of Health"], ["VN-SG"]],
            ' của Sở Tài nguyên và Môi trường Thành phố Hà Nội' => [["vi"=>"Sở Tài nguyên và Môi trường Thành phố Hà Nội", "en"=>"The Hanoi City Department of Natural Resources and Environment"], ["VN-HN"]],
            ' của Sở Giao thông Vận tải Thành phố Hà Nội'       => [["vi"=>"Sở Giao thông Vận tải Thành phố Hà Nội", "en"=>"The Hanoi City Department of Transportation"], ["VN-HN"]],

            ' của Ban Chỉ đạo' => [["vi"=>"Ban Chỉ đạo", "en"=>"The General Directorate"], ["VN"]],
            ' của Ban Chỉ đạo 389 tỉnh Bình Thuận' => [["vi"=>"Ban Chỉ đạo 389 tỉnh Bình Thuận", "en"=>"Directorate 389 of Binh Thuan Province"], ["VN-40"]],
            ' của Ban Chỉ đạo phòng, chống tác hại thuốc lá tỉnh Thừa Thiên Huế' => [["vi"=>"Ban Chỉ đạo phòng, chống tác hại thuốc lá tỉnh Thừa Thiên Huế", "en"=>"The Directorate for the Prevention and Control of Tobacco Harms of Thua Thien Hue Province"], ["VN-26"]],

            ' của Sở Giao dịch Chứng khoán Thành phố Hồ Chí Minh' => [["vi"=>"Sở Giao dịch Chứng khoán Thành phố Hồ Chí Minh", "en"=>"The Ho Chi Minh City Stock Exchange"], ["VN-SG"]],

            ' của Chính phủ'                       => [["vi"=>"Chính phủ", "en"=>"The Government"], ["VN"]],
            ' của Thanh tra Chính phủ'             => [["vi"=>"Thanh tra Chính phủ", "en"=>"The Government Inspectorate"], ["VN"]],
            ' của Văn phòng Chính phủ'             => [["vi"=>"Văn phòng Chính phủ", "en"=>"The Office of the Government"], ["VN"]],

            ' của Bảo hiểm xã hội Việt Nam'        => [["vi"=>"Bảo hiểm xã hội Việt Nam", "en"=>"The Social Insurance of Vietnam"], ["VN"]],
            ' của Bảo hiểm xã hội Thành phố Hà Nội' => [["vi"=>"Bảo hiểm xã hội Thành phố Hà Nội", "en"=>"The Social Insurance of Hanoi City"], ["VN-HN"]],
            ' của Bảo hiểm xã hội Thành phố Hồ Chí Minh' => [["vi"=>"Bảo hiểm xã hội Thành phố Hồ Chí Minh", "en"=>"The Social Insurance of Ho Chi Minh City"], ["VN-SG"]],

            ' của Cục Đăng kiểm Việt Nam'          => [["vi"=>"Cục Đăng kiểm Việt Nam", "en"=>"The Vietnam Registry Office"], ["VN"]],
            ' của Tổng cục Thống kê'               => [["vi"=>"Tổng cục Thống kê", "en"=>"The General Office of Statistics"], ["VN"]],
            ' của Tổng cục Thuế'                   => [["vi"=>"Tổng cục Thuế", "en"=>"The General Office of Taxation"], ["VN"]],
            ' của Cục Thuế tỉnh An Giang'          => [["vi"=>"Cục Thuế tỉnh An Giang", "en"=>"The Tax Office of An Giang Province"], ["VN-44"]],
            ' của Cục Thuế tỉnh Bà Rịa - Vũng Tàu' => [["vi"=>"Cục Thuế tỉnh Bà Rịa-Vũng Tàu", "en"=>"The Tax Office of Ba Ria-Vung Tau Province"], ["VN-43"]],
            ' của Cục Thuế tỉnh Bà Rịa-Vũng Tàu'   => [["vi"=>"Cục Thuế tỉnh Bà Rịa-Vũng Tàu", "en"=>"The Tax Office of Ba Ria-Vung Tau Province"], ["VN-43"]],
            ' của Cục Thuế tỉnh Bắc Giang'         => [["vi"=>"Cục Thuế tỉnh Bắc Giang", "en"=>"The Tax Office of Bac Giang Province"], ["VN-54"]],
            ' của Cục Thuế tỉnh Bắc Kạn'           => [["vi"=>"Cục Thuế tỉnh Bắc Kạn", "en"=>"The Tax Office of Bac Kan Province"], ["VN-53"]],
            ' của Cục Thuế tỉnh Bạc Liêu'          => [["vi"=>"Cục Thuế tỉnh Bạc Liêu", "en"=>"The Tax Office of Bac Lieu Province"], ["VN-55"]],
            ' của Cục Thuế tỉnh Bắc Ninh'          => [["vi"=>"Cục Thuế tỉnh Bắc Ninh", "en"=>"The Tax Office of Bac Ninh Province"], ["VN-56"]],
            ' của Cục Thuế tỉnh Bến Tre'           => [["vi"=>"Cục Thuế tỉnh Bến Tre", "en"=>"The Tax Office of Ben Tre Province"], ["VN-50"]],
            ' của Cục Thuế tỉnh Bình Định'         => [["vi"=>"Cục Thuế tỉnh Bình Định", "en"=>"The Tax Office of Binh Dinh Province"], ["VN-31"]],
            ' của Cục Thuế tỉnh Bình Dương'        => [["vi"=>"Cục Thuế tỉnh Bình Dương", "en"=>"The Tax Office of Binh Duong Province"], ["VN-57"]],
            ' của Cục Thuế tỉnh Bình Phước'        => [["vi"=>"Cục Thuế tỉnh Bình Phước", "en"=>"The Tax Office of Binh Phuoc Province"], ["VN-58"]],
            ' của Cục Thuế tỉnh Bình Thuận'        => [["vi"=>"Cục Thuế tỉnh Bình Thuận", "en"=>"The Tax Office of Binh Thuan Province"], ["VN-40"]],
            ' của Cục Thuế tỉnh Cà Mau'            => [["vi"=>"Cục Thuế tỉnh Cà Mau", "en"=>"The Tax Office of Ca Mau Province"], ["VN-59"]],
            ' của Cục Thuế tỉnh Cần Thơ'           => [["vi"=>"Cục Thuế tỉnh Cần Thơ", "en"=>"The Tax Office of Can Tho Province"], ["VN-CT"]],
            ' của Cục Thuế tỉnh Cao Bằng'          => [["vi"=>"Cục Thuế tỉnh Cao Bằng", "en"=>"The Tax Office of Cao Bang Province"], ["VN-04"]],
            ' của Cục Thuế tỉnh Đắk Lắk'           => [["vi"=>"Cục Thuế tỉnh Đắk Lắk", "en"=>"The Tax Office of Dak Lak Province"], ["VN-33"]],
            ' của Cục Thuế tỉnh Đắk Nông'          => [["vi"=>"Cục Thuế tỉnh Đắk Nông", "en"=>"The Tax Office of Dak Nong Province"], ["VN-72"]],
            ' của Cục Thuế tỉnh Điện Biên'         => [["vi"=>"Cục Thuế tỉnh Điện Biên", "en"=>"The Tax Office of Dien Bien Province"], ["VN-71"]],
            ' của Cục Thuế tỉnh Đồng Nai'          => [["vi"=>"Cục Thuế tỉnh Đồng Nai", "en"=>"The Tax Office of Dong Nai Province"], ["VN-39"]],
            ' của Cục Thuế tỉnh Đồng Tháp'         => [["vi"=>"Cục Thuế tỉnh Đồng Tháp", "en"=>"The Tax Office of Dong Thap Province"], ["VN-45"]],
            ' của Cục Thuế tỉnh Gia Lai'           => [["vi"=>"Cục Thuế tỉnh Gia Lai", "en"=>"The Tax Office of Gia Lai Province"], ["VN-30"]],
            ' của Cục Thuế tỉnh Hà Giang'          => [["vi"=>"Cục Thuế tỉnh Hà Giang", "en"=>"The Tax Office of Ha Giang Province"], ["VN-03"]],
            ' của Cục Thuế tỉnh Hà Nam'            => [["vi"=>"Cục Thuế tỉnh Hà Nam", "en"=>"The Tax Office of Ha Nam Province"], ["VN-63"]],
            ' của Cục Thuế tỉnh Hà Tĩnh'           => [["vi"=>"Cục Thuế tỉnh Hà Tĩnh", "en"=>"The Tax Office of Ha Tinh Province"], ["VN-23"]],
            ' của Cục Thuế tỉnh Hải Dương'         => [["vi"=>"Cục Thuế tỉnh Hải Dương", "en"=>"The Tax Office of Hai Duong Province"], ["VN-61"]],
            ' của Cục Thuế tỉnh Hậu Giang'         => [["vi"=>"Cục Thuế tỉnh Hậu Giang", "en"=>"The Tax Office of Hau Giang Province"], ["VN-73"]],
            ' của Cục Thuế tỉnh Hòa Bình'          => [["vi"=>"Cục Thuế tỉnh Hòa Bình", "en"=>"The Tax Office of Hoa Binh Province"], ["VN-14"]],
            ' của Cục Thuế tỉnh Hưng Yên'          => [["vi"=>"Cục Thuế tỉnh Hưng Yên", "en"=>"The Tax Office of Hung Yen Province"], ["VN-66"]],
            ' của Cục Thuế tỉnh Khánh Hòa'         => [["vi"=>"Cục Thuế tỉnh Khánh Hòa", "en"=>"The Tax Office of Khanh Hoa Province"], ["VN-34"]],
            ' của Cục Thuế tỉnh Kiên Giang'        => [["vi"=>"Cục Thuế tỉnh Kiên Giang", "en"=>"The Tax Office of Kien Giang Province"], ["VN-47"]],
            ' của Cục Thuế tỉnh Kon Tum'           => [["vi"=>"Cục Thuế tỉnh Kon Tum", "en"=>"The Tax Office of Kon Tum Province"], ["VN-28"]],
            ' của Cục Thuế tỉnh Lai Châu'          => [["vi"=>"Cục Thuế tỉnh Lai Châu", "en"=>"The Tax Office of Lai Chau Province"], ["VN-02"]],
            ' của Cục Thuế tỉnh Lâm Đồng'          => [["vi"=>"Cục Thuế tỉnh Lâm Đồng", "en"=>"The Tax Office of Lam Dong Province"], ["VN-35"]],
            ' của Cục Thuế tỉnh Lạng Sơn'          => [["vi"=>"Cục Thuế tỉnh Lạng Sơn", "en"=>"The Tax Office of Lang Son Province"], ["VN-09"]],
            ' của Cục Thuế tỉnh Lào Cai'           => [["vi"=>"Cục Thuế tỉnh Lào Cai", "en"=>"The Tax Office of Lao Cai Province"], ["VN-01"]],
            ' của Cục Thuế tỉnh Long An'           => [["vi"=>"Cục Thuế tỉnh Long An", "en"=>"The Tax Office of Long An Province"], ["VN-41"]],
            ' của Cục Thuế tỉnh Nam Định'          => [["vi"=>"Cục Thuế tỉnh Nam Định", "en"=>"The Tax Office of Nam Dinh Province"], ["VN-67"]],
            ' của Cục Thuế Nam Định'               => [["vi"=>"Cục Thuế Nam Định", "en"=>"The Tax Office of Nam Dinh Province"], ["VN-67"]],
            ' của Cục Thuế tỉnh Nghệ An'           => [["vi"=>"Cục Thuế tỉnh Nghệ An", "en"=>"The Tax Office of Nghe An Province"], ["VN-22"]],
            ' của Cục Thuế tỉnh Ninh Bình'         => [["vi"=>"Cục Thuế tỉnh Ninh Bình", "en"=>"The Tax Office of Ninh Binh Province"], ["VN-18"]],
            ' của Cục Thuế tỉnh Ninh Thuận'        => [["vi"=>"Cục Thuế tỉnh Ninh Thuận", "en"=>"The Tax Office of Ninh Thuan Province"], ["VN-36"]],
            ' của Cục Thuế tỉnh Phú Thọ'           => [["vi"=>"Cục Thuế tỉnh Phú Thọ", "en"=>"The Tax Office of Phu Tho Province"], ["VN-68"]],
            ' của Cục Thuế tỉnh Phú Yên'           => [["vi"=>"Cục Thuế tỉnh Phú Yên", "en"=>"The Tax Office of Phu Yen Province"], ["VN-32"]],
            ' của Cục Thuế tỉnh Quảng Bình'        => [["vi"=>"Cục Thuế tỉnh Quảng Bình", "en"=>"The Tax Office of Quang Binh Province"], ["VN-24"]],
            ' của Cục Thuế tỉnh Quảng Nam'         => [["vi"=>"Cục Thuế tỉnh Quảng Nam", "en"=>"The Tax Office of Quang Nam Province"], ["VN-27"]],
            ' của Cục Thuế tỉnh Quảng Ngãi'        => [["vi"=>"Cục Thuế tỉnh Quảng Ngãi", "en"=>"The Tax Office of Quang Ngai Province"], ["VN-29"]],
            ' của Cục Thuế tỉnh Quảng Ninh'        => [["vi"=>"Cục Thuế tỉnh Quảng Ninh", "en"=>"The Tax Office of Quang Ninh Province"], ["VN-13"]],
            ' của Cục Thuế tỉnh Quảng Trị'         => [["vi"=>"Cục Thuế tỉnh Quảng Trị", "en"=>"The Tax Office of Quang Tri Province"], ["VN-25"]],
            ' của Cục Thuế tỉnh Sóc Trăng'         => [["vi"=>"Cục Thuế tỉnh Sóc Trăng", "en"=>"The Tax Office of Soc Trang Province"], ["VN-52"]],
            ' của Cục Thuế tỉnh Sơn La'            => [["vi"=>"Cục Thuế tỉnh Sơn La", "en"=>"The Tax Office of Son La Province"], ["VN-05"]],
            ' của Cục Thuế tỉnh Tây Ninh'          => [["vi"=>"Cục Thuế tỉnh Tây Ninh", "en"=>"The Tax Office of Tay Ninh Province"], ["VN-37"]],
            ' của Cục Thuế tỉnh Thái Bình'         => [["vi"=>"Cục Thuế tỉnh Thái Bình", "en"=>"The Tax Office of Thai Binh Province"], ["VN-20"]],
            ' của Cục Thuế tỉnh Thái Nguyên'       => [["vi"=>"Cục Thuế tỉnh Thái Nguyên", "en"=>"The Tax Office of Thai Nguyen Province"], ["VN-69"]],
            ' của Cục Thuế tỉnh Thanh Hóa'         => [["vi"=>"Cục Thuế tỉnh Thanh Hóa", "en"=>"The Tax Office of Thanh Hoa Province"], ["VN-21"]],
            ' của Cục Thuế tỉnh Thừa Thiên Huế'    => [["vi"=>"Cục Thuế tỉnh Thừa Thiên Huế", "en"=>"The Tax Office of Thua Thien Hue Province"], ["VN-26"]],
            ' của Cục Thuế tỉnh Tiền Giang'        => [["vi"=>"Cục Thuế tỉnh Tiền Giang", "en"=>"The Tax Office of Tien Giang Province"], ["VN-46"]],
            ' của Cục Thuế tỉnh Trà Vinh'          => [["vi"=>"Cục Thuế tỉnh Trà Vinh", "en"=>"The Tax Office of Tra Vinh Province"], ["VN-51"]],
            ' của Cục Thuế Trà Vinh'               => [["vi"=>"Cục Thuế tỉnh Trà Vinh", "en"=>"The Tax Office of Tra Vinh Province"], ["VN-51"]],
            ' của Cục Thuế tỉnh Tuyên Quang'       => [["vi"=>"Cục Thuế tỉnh Tuyên Quang", "en"=>"The Tax Office of Tuyen Quang Province"], ["VN-07"]],
            ' của Cục Thuế tỉnh Vĩnh Long'         => [["vi"=>"Cục Thuế tỉnh Vĩnh Long", "en"=>"The Tax Office of Vinh Long Province"], ["VN-49"]],
            ' của Cục Thuế tỉnh Vĩnh Phúc'         => [["vi"=>"Cục Thuế tỉnh Vĩnh Phúc", "en"=>"The Tax Office of Vinh Phuc Province"], ["VN-70"]],
            ' của Cục Thuế tỉnh Yên Bái'           => [["vi"=>"Cục Thuế tỉnh Yên Bái", "en"=>"The Tax Office of Yen Bai Province"], ["VN-06"]],
            ' của Cục Thuế Thành phố Hà Nội'       => [["vi"=>"Cục Thuế Thành phố Hà Nội", "en"=>"The Tax Office of Hanoi"], ["VN-HN"]],
            ' của Cục Thuế Thành phố Hồ Chí Minh'  => [["vi"=>"Cục Thuế Thành phố Hồ Chí Minh", "en"=>"The Tax Office of Ho Chi Minh City"], ["VN-SG"]],
            ' của Cục Thuế Thành phố Hải Phòng'    => [["vi"=>"Cục Thuế Thành phố Hải Phòng", "en"=>"The Tax Office of Hai Phong City"], ["VN-HP"]],
            ' của Cục Thuế Thành phố Đà Nẵng'      => [["vi"=>"Cục Thuế Thành phố Đà Nẵng", "en"=>"The Tax Office of Da Nang City"], ["VN-DN"]],
            ' của Cục Thuế Thành phố Cần Thơ'      => [["vi"=>"Cục Thuế Thành phố Cần Thơ", "en"=>"The Tax Office of Can Tho City"], ["VN-CT"]],

            ' của Bộ Nông nghiệp và Phát triển nông thôn' => [["vi"=>"Bộ Nông nghiệp và Phát triển nông thôn", "en"=>"The Ministry of Agriculture and Rural Development"], ["VN"]],
            ' của Bộ Thương'                       => [["vi"=>"Bộ Thương", "en"=>"The Ministry of Commerce"], ["VN"]],
            ' của Bộ Xây dựng'                     => [["vi"=>"Bộ Xây dựng", "en"=>"The Ministry of Construction"], ["VN"]],
            ' của Bộ Văn hóa, Thể thao và Du lịch' => [["vi"=>"Bộ Văn hóa, Thể thao và Du lịch", "en"=>"The Ministry of Culture, Sports and Tourism"], ["VN"]],
            ' của Bộ Quốc phòng'                   => [["vi"=>"Bộ Quốc phòng", "en"=>"The Ministry of Defense"], ["VN"]],
            ' Bộ và Khoa học Ucraina'              => [["vi"=>"Bộ và Khoa học Ucraina", "en"=>"The Ministry of Education and Science of Ukraine"], ["VN", "UA"]],
            ' của Bộ Giáo dục và Đào tạo'          => [["vi"=>"Bộ Giáo dục và Đào tạo", "en"=>"The Ministry of Education and Training"], ["VN"]],
            ' của Bộ Tài chính'                    => [["vi"=>"Bộ Tài chính", "en"=>"The Ministry of Finance"], ["VN"]],
            ' của Bộ Ngoại giao về việc Ma-lai-xi-a' => [["vi"=>"Bộ Ngoại giao về việc Ma-lai-xi-a", "en"=>"The Ministry of Foreign Affairs of Malaysia"], ["VN", "MY"]],
            ' của Bộ Ngoại giao'                   => [["vi"=>"Bộ Ngoại giao", "en"=>"The Ministry of Foreign Affairs"], ["VN"]],
            ' của Bộ Y'                            => [["vi"=>" Bộ Y", "en"=>"The Ministry of Health"], ["VN"]],
            ' của Bộ Công Thương'                  => [["vi"=>"Bộ Công Thương", "en"=>"The Ministry of Industry and Trade"], ["VN"]],
            ' của Bộ Thông tin và Truyền thông'    => [["vi"=>"Bộ Thông tin và Truyền thông", "en"=>"The Ministry of Information and Communications"], ["VN"]],
            ' của Bộ Nội vụ'                       => [["vi"=>"Bộ Nội vụ", "en"=>"The Ministry of Internal Affairs"], ["VN"]],
            ' của Bộ Tư pháp'                      => [["vi"=>"Bộ Tư pháp", "en"=>"The Ministry of Justice"], ["VN"]],
            ' của Bộ Lao động Thương binh và Xã hội' => [["vi"=>"Bộ Lao động Thương binh và Xã hội", "en"=>"The Ministry of Labor, War Invalids and Social Affairs"], ["VN"]],
            ' của Bộ Tài nguyên và Môi trường'     => [["vi"=>"Bộ Tài nguyên và Môi trường", "en"=>"The Ministry of Natural Resources and Environment"], ["VN"]],
            ' của Bộ Kế hoạch và Đầu tư'           => [["vi"=>"Bộ Kế hoạch và Đầu tư", "en"=>"The Ministry of Planning and Investment"], ["VN"]],
            ' của Bộ trưởng Bộ Kế hoạch và Đầu tư' => [["vi"=>"Bộ trưởng Bộ Kế hoạch và Đầu tư", "en"=>"The Minister of Planning and Investment"], ["VN"]],
            ' của Bộ Chính trị'                    => [["vi"=>"Bộ Chính trị", "en"=>"The Ministry of Politics"], ["VN"]],
            ' của Bộ Khoa học và Công nghệ'        => [["vi"=>"Bộ Khoa học và Công nghệ", "en"=>"The Ministry of Science and Technology"], ["VN"]],
            ' của Bộ Công an'                      => [["vi"=>"Bộ Công an", "en"=>"The Ministry of Public Security"], ["VN"]],
            ' của Bộ Giao'                         => [["vi"=>"Bộ Giao", "en"=>"The Ministry of Transportation"], ["VN"]],

            ' của Chủ tịch nước'                   => [["vi"=>"Chủ tịch nước", "en"=>"The President"], ["VN"]],

            ' của Thủ tướng Chính phủ' => [["vi"=>"Thủ tướng Chính phủ", "en"=>"The Prime Minister"], ["VN"]],

            ' của Ban Bí thư' => [["vi"=>"Ban Bí thư", "en"=>"The Secretariat"], ["VN"]],

            ' của Kho bạc Nhà nước' => [["vi"=>"Kho bạc Nhà nước", "en"=>"The State Treasury"], ["VN"]],

            ' của Công đoàn ngành Giáo dục Thành phố Hồ Chí Minh' => [["vi"=>"Công đoàn ngành Giáo dục Thành phố Hồ Chí Minh", "en"=>"The Trade Union of the Education Sector of Ho Chi Minh City"], ["VN-SG"]],
            ' của Liên đoàn lao động Thành phố Hồ Chí Minh' => [["vi"=>"Liên đoàn lao động Thành phố Hồ Chí Minh", "en"=>"The Labor Union of Ho Chi Minh City"], ["VN-SG"]],
        
            //Organizations
            ' ASEAN'            => [["vi"=>"Hiệp hội các quốc gia Đông Nam", "en"=>"The Association of Southeast Asian Nations"], ["VN", "ASEAN"]],

            //Countries
            //TODO: Add more countries
            ' Cộng hòa Ác-hen-ti-na' => [["vi"=>"Ác-hen-ti-na", "en"=>"Argentina"], ["VN", "AR"]],
            ' Cộng hòa Bê-la-rút'   => [["vi"=>"Bê-la-rút", "en"=>"Belarus"], ["VN", "BY"]],
            ' Cộng hòa Chi-lê'     => [["vi"=>"Chi-lê", "en"=>"Chile"], ["VN", "CL"]],
            ' Trung Hoa'           => [["vi"=>"Trung Hoa", "en"=>"China"], ["VN", "CN"]],
            ' Trung Quốc'          => [["vi"=>"Trung Quốc", "en"=>"China"], ["VN", "CN"]],
            ' Vương quốc Campuchia' => [["vi"=>"Campuchia", "en"=>"Cambodia"], ["VN", "KH"]],
            ' Liên minh Kinh tế Á Âu' => [["vi"=>"Liên minh Kinh tế Á Âu", "en"=>"The Eurasian Economic Union"], ["VN", "EAEU"]],
            ' Liên minh Châu Âu'   => [["vi"=>"Liên minh châu Âu", "en"=>"The European Union"], ["VN", "EU"]],
            ' EU'                  => [["vi"=>"Liên minh châu Âu", "en"=>"The European Union"], ["VN", "EU"]],
            ' Cộng hoà Liên bang Đức' => [["vi"=>"Đức", "en"=>"Germany"], ["VN", "DE"]],
            ' Chính quyền Bang Hessen' => [["vi"=>"Hessen", "en"=>"Hessen"], ["VN", "DE-HE"]],
            ' Cộng hoà hồi giáo I-ran' => [["vi"=>"I-ran", "en"=>"Iran"], ["VN", "IR"]],
            ' Cộng hòa I-ta-li-a'   => [["vi"=>"I-ta-li-a", "en"=>"Italy"], ["VN", "IT"]],
            ' Cộng hòa dân chủ nhân dân Lào' => [["vi"=>"Lào", "en"=>"Laos"], ["VN", "LA"]],
            ' Liên bang Mê-hi-cô'  => [["vi"=>"Mê-hi-cô", "en"=>"Mexico"], ["VN", "MX"]],
            ' Cộng hòa Môn-đô-va'  => [["vi"=>"Môn-đô-va", "en"=>"Moldova"], ["VN", "MD"]],
            ' Cộng hòa Na-mi-bi-a' => [["vi"=>"Na-mi-bi-a", "en"=>"Namibia"], ["VN", "NA"]],
            ' Cộng hòa Pa-na-ma'   => [["vi"=>"Pa-na-ma", "en"=>"Panama"], ["VN", "PA"]],
            ' Cộng hòa Ba Lan'     => [["vi"=>"Ba Lan", "en"=>"Poland"], ["VN", "PL"]],
            ' Chính phủ Liên bang Nga' => [["vi"=>"Nga", "en"=>"Russia"], ["VN", "RU"]],
            ' Liên bang Nga'       => [["vi"=>"Nga", "en"=>"Russia"], ["VN", "RU"]],
            ' Cộng hoà Xi-ê-ra Lê-ôn' => [["vi"=>"Xi-ê-ra Lê-ôn", "en"=>"Sierra Leone"], ["VN", "SL"]],
            ' Đại Hàn Dân Quốc'    => [["vi"=>"Đại Hàn Dân Quốc", "en"=>"South Korea"], ["VN", "KR"]],
            ' Cộng hoà Tuy-ni-di'  => [["vi"=>"Tuy-ni-dí", "en"=>"Tunisia"], ["VN", "TN"]],
            ' Các Tiểu vương quốc Ả-rập Thống nhất' => [["vi"=>"Các Tiểu vương quốc Ả-rập Thống nhất", "en"=>"The United Arab Emirates"], ["VN", "AE"]],
            ' Liên hiệp Vương quốc Anh và Bắc Ai-len' => [["vi"=>"Liên hiệp Vương quốc", "en"=>"The United Kingdom"], ["VN", "GB"]],
            ' Hoa Kỳ'              => [["vi"=>"Hợp chủng quốc Hoa Kỳ", "en"=>"The United States of America"], ["VN", "US"]],
            ' Cộng hòa U-dơ-bê-ki-xtan' => [["vi"=>"U-dơ-bê-ki-xtan", "en"=>"Uzbekistan"], ["VN", "UZ"]],
        );
        //Translates the types
        $types = array(
            'vi' => array(
                'Luật' => 'Act',
                /*'Thoả thuận bổ sung' => 'Additional Agreement',
                'Thoả thuận' => 'Agreement',//International
                'Hiệp định lãnh sự' => 'Consular Agreement',
                'Hiệp định hợp tác' => 'Cooperation Agreement',
                'Hiệp định' => 'Agreement',*///International (In most cases)
                'Thỏa thuận liên ngành' => 'Inter-Ministerial Agreement',
                'Thông báo liên tịch'   => 'Joint Announcement',
                'Thông báo'             => 'Announcement',
                'Công bố'               => 'Announcement',
                'Điều lệ Công'          => 'Charter',
                'Thông tư liên tịch'    => 'Joint Circular',
                'Thông tiên tịch'       => 'Joint Circular',
                'Thông tư'              => 'Circular',
                'Giải đáp'              => 'Clarification',

                'Bộ luật Dân sự'          => 'Code',
                'Bộ luật Tố tụng hình sự' => 'Code',
                'Bộ luật hình sự'         => 'Code',
                'Bộ luật Hình sự'         => 'Code',
                'Bộ luật Lao động'        => 'Code',
                'Bộ luật Hàng hải Việt Nam' => 'Code',
                'Bộ luật Hàng hải'        => 'Code',
                'Bộ luật Tố tụng dân sự'  => 'Code',
                'Bộ luật Tố tụng Hình sự' => 'Code',

                'Cam kết'               => 'Commitment',
                'Thông tri'             => 'Communiqué',
                'Kết luận thanh tra'    => 'Conclusion of Inspection',
                'Kết luận'              => 'Conclusion',
                //'Công ước'              => 'Convention',//International
                'Sao lục'               => 'Copy',
                'Đính chính'            => 'Correction',
                'Quyết định đính chính' => 'Decision Correction',
                'Quyết định'            => 'Decision',
                'Quyết'                 => 'Decision',
                'Nghị định thư'         => 'Protocol',
                'Nghị định'             => 'Decree',
                'Chỉ thị liên tịch'     => 'Joint Directive',
                'Chỉ thị về'            => 'Directive',
                'Chỉ thị'               => 'Directive',
                'Văn bản hợp nhất'      => 'Consolidated Document',
                'Phiếu chuyển Công văn' => 'Document Transfer Slip',
                'Hướng dẫn liên ngành'  => 'Inter-Ministerial Instruction',
                'Hướng dẫn tạm thời'    => 'Interim Instruction',
                'Hướng dẫn liên tịch'   => 'Joint Instruction',
                'Hướng dẫn'             => 'Instruction',
                'Công ty'               => 'Official Letter',
                'Công văn'              => 'Official Dispatch',
                'Công điện khẩn'        => 'Urgent Official Telegram',
                'Công điện'             => 'Official Telegram',
                'Công việc'             => 'Official Work',
                'Công'                  => 'Official Letter',
                'Lệnh'                  => 'Order',
                'Pháp lệnh lực lượng'   => 'Forced Ordinance',
                'Pháp lệnh về việc'     => 'Ordinance',
                'Pháp lệnh'             => 'Ordinance',
                'Kế hoạch hành động'    => 'Action Plan',
                'Kế hoạch thẩm định'    => 'Assessment Plan',
                'Kế hoạch khung'        => 'Framework Plan',
                'Kế hoạch triển khai'   => 'Implementation Plan',
                'Kế hoạch phối hợp'     => 'Coordinated Plan',
                'Kế hoạch liên tịch'    => 'Joint Plan',
                'Kế hoạch'              => 'Plan',
                'Phương án'             => 'Plan',
                'Quy trình'             => 'Procedure',
                'Chương trình phối hợp' => 'Program',
                'Chương trình hành động' => 'Program',
                'Chương trình'          => 'Program',
                'Đề án'                 => 'Project',
                'Tờ trình tóm tắt'      => 'Summary Proposal',
                'Tờ trình'              => 'Proposal',
                'Trưng cầu ý dân'       => 'Referendum',
                'Quy định'              => 'Regulation',
                'Quy chế phối hợp liên ngành' => 'Inter-Ministerial Coordinated Regulations',
                'Quy chế liên ngành'    => 'Inter-Ministerial Regulations',
                'Quy chế phối hợp'      => 'Coordinated Regulations',
                'Quy chế'               => 'Regulation',
                'Báo cáo thẩm định'     => 'Assessment Report',
                'Báo cáo tổng hợp'      => 'Comprehensive Report',
                'Báo cáo nhanh'         => 'Quick Report',
                'Báo cáo tóm tắt'       => 'Summary Report',
                'Báo cáo'               => 'Report',
                'Nghị quyết liên tịch'  => 'Joint Resolution',
                'Nghị quyết'            => 'Resolution',
            ),
            'en' => array(
                'Joint Circular',
                'Circular',
                'Decision',
                'Decree',
                'Regulation',
                'Resolution',
            ),
        );

        //Does the Vietnamese
        $lang = 'vi';
        //Gets the limit
        $html_dom = file_get_html('https://luatvietnam.vn/van-ban-luat-viet-nam.html?pSize='.$step);
        $limit[$lang] = $limit[$lang] ?? $html_dom->find('div.pag-right')[0]->find('span[class="page-numbers"] + a.page-numbers')[0]->plaintext;
        //Loops through the pages
        for ($page = $start[$lang]; $page <= $limit[$lang]; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://luatvietnam.vn/van-ban-luat-viet-nam.html?pSize='.$step.'&page='.$page);
            $laws = $html_dom->find('div.block-content')[0]->find('article.doc-article');
            foreach ($laws as $law) {
                //Resets LBpage
                $LBpage = $scraper;
                //Gets the values
                $enactDate = $enforceDate = date('Y-m-d', strtotime(str_replace('/', '-', $law->find('div.doc-clumn2')[0]->find('div.post-meta-doc')[0]->find('div.doc-dmy')[0]->find('span.w-doc-dmy2')[0]->plaintext)));
                    $lastactDate = date('Y-m-d', strtotime(str_replace('/', '-', end($law->find('div.doc-clumn2')[0]->find('div.post-meta-doc')[0]->find('div.doc-dmy.m-hide'))->find('span.w-doc-dmy2')[0]->plaintext)));
                $name = trim(str_replace(array_keys($sanitizeName[$lang]), array_values($sanitizeName[$lang]), $law->find('div.doc-clumn2')[0]->find('div.post-doc')[0]->find('div.post-type-doc')[0]->find('a')[0]->plaintext), ' ​.');
                //Gets the regime
                $country = array("VN");
                switch (true) {
                    case strtotime($enactDate) <= strtotime('today'):
                        $regime = '[{"vi":"Cộng hòa Xã hội chủ nghĩa Việt Nam", "en":"The Socialist Republic of Vietnam"}'; break;
                    case strtotime($enactDate) < strtotime('2 September 1945'):
                        $regime = '[{"vi":"Đế quốc Nhật Bản", "en":"The Empire of Japan"}'; break;
                }
                //Gets the topic
                $nameLine = $name;
                $topic = array();
                foreach ($topics as $topicVN => $topicEN) {
                    if (str_contains($nameLine, $topicVN)) {
                        $nameLine = str_replace($topicVN, '', $nameLine);
                        $topic[] = array('vi'=>$topicVN, 'en'=>$topicEN);
                        break;
                    }
                }
                $topic = sizeof($topic) > 0 ? "'".json_encode($topic, JSON_UNESCAPED_UNICODE)."'":'NULL';
                //Gets the origin and country/page
                $origin = array();
                foreach ($origins as $originKey => $originVal) {
                    if (str_contains($nameLine, $originKey)) {
                        $nameLine = str_replace($originKey, '', $nameLine);
                        $LBpage = $originVal[1][0];
                        if (isset($originVal[1][1]) && (str_starts_with($nameLine, 'Hiệp định') || str_starts_with($nameLine, 'Thoả thuận'))) {$country[] = $originVal[1][1];}
                        $origin[] = $originVal[0];
                    }
                }
                $country = json_encode($country, JSON_UNESCAPED_UNICODE);
                $origin = sizeof($origin) > 0 ? "'".json_encode($origin, JSON_UNESCAPED_UNICODE)."'":'NULL';
                //Gets the type and ID
                $type = NULL; $ID = NULL;
                foreach ($types[$lang] as $typeVN => $typeEN) {
                    if (str_starts_with($nameLine, $typeVN)) {
                        $nameLine = explode($typeVN.' ', $nameLine)[1];
                        $type = $typeEN;
                        $nameLine = trim(str_replace([', số ', 'số ', 'sô ', 'của '], ['', '', '', ''], $nameLine));
                        $ID = $LBpage.':'.$isValidID(strtr(explode(' ', $nameLine)[0], $sanitizeID));
                        break;
                    }
                }
                if ($country !== '["VN"]')
                if ($ID === $LBpage.':NULL' || !isset($ID)) echo 'ERR: BAD-ID:<span>'.$nameLine.'</span><br/>';//For debugging
                //Gets the rest of the values
                $isAmend = 0;
                $status = 'Valid';
                $source = 'https://luatvietnam.vn'.$law->find('div.doc-clumn2')[0]->find('div.post-type-doc')[0]->find('a')[0]->href;
                //$PDF = <!--come back to this-->

                //Makes sure there are no appostophes in the title
                $name = strtr($name, array("'" => "’", ' "' => " “", '"' => "”"));

                //JSONifies the name and href
                $name = '{"'.$lang.'":"'.$name.'"}';
                $source = '{"'.$lang.'":"'.$source.'"}';

                //Creates SQL
                $SQL2 = "INSERT INTO `".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `origin`, `type`, `status`, `topic`, `source`)
                        VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', '".$country."', '".$regime."', ".$origin.", '".$type.", '".$status."', ".$topic.", '".$source."')";

                //Executes the SQL
                echo 'p.'.$page.':'.$law->find('div.doc-clumn1')[0]->find('span.doc-number')[0]->plaintext.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Does the English
        $lang = 'en';
        //Gets the limit
        $html_dom = file_get_html('https://english.luatvietnam.vn/official-gazette.html?pSize='.$step);
        $limit[$lang] = $limit[$lang] ?? ceil(strtr($html_dom->find('div.row-results.mgb8')[0]->find('div.sort-left')[0]->find('strong')[0]->plaintext, array(','=>''))/$step);
        //Loops through the pages
        for ($page = $start[$lang]; $page <= $limit[$lang]; $page++) {
            //Processes the data
            $html_dom = file_get_html('https://english.luatvietnam.vn/official-gazette.html?pSize='.$step.'&page='.$page);
            $laws = $html_dom->find('#legal-normative-documents')[0]->find('article.article-document');
            foreach($laws as $law) {
                //Gets the values
                $enactDate = $enforceDate = $lastactDate = date('Y-m-d', strtotime(str_replace('/', '-', $law->find('div.col2-document')[0]->find('div.date-row')[0]->find('div.date-col2')[0]->plaintext)));
                $name = trim(str_replace(array_keys($sanitizeName[$lang]), array_values($sanitizeName[$lang]), $law->find('div.col1-document')[0]->find('div.post-document')[0]->find('a')[0]->plaintext));
                //Gets the regime
                $country = array("VN");
                switch (true) {
                    case strtotime($enactDate) <= strtotime('today'):
                        $regime = '[{"vi":"Cộng hòa Xã hội chủ nghĩa Việt Nam", "en":"The Socialist Republic of Vietnam"}'; break;
                    case strtotime($enactDate) < strtotime('2 September 1945'):
                        $regime = '[{"vi":"Đế quốc Nhật Bản", "en":"The Empire of Japan"}'; break;
                }
                //Gets the type and ID
                $type = NULL;
                $nameLine = str_replace([' No.'], [''], $name);
                foreach($types[$lang] as $typeEN) {
                    if (str_starts_with($nameLine, $typeEN)) {
                        $type = $typeEN;
                        $nameLine = explode($typeEN.' ', $nameLine)[1];
                        $ID = $isValidID(strtr(explode(' ', $nameLine)[0], $sanitizeID));
                        break;
                    }
                }
                if ($ID === $LBpage.':NULL' || !isset($ID)) echo 'ERR: BAD-ID:<span>'.$nameLine.'</span><br/>';//For debugging
                //Gets the rest of the values
                $isAmend = 0;
                $status = 'Valid';
                $source = 'https://luatvietnam.vn'.$law->find('div.col1-document')[0]->find('div.post-document')[0]->find('a')[0]->href;
                //$PDF = <!--come back to this-->

                //Makes sure there are no appostophes in the title
                if (str_contains($name, "'")) {$name = str_replace("'", "’", $name);}

                //Creates SQL
                $SQL = "SELECT * FROM `".strtolower($LBpage)."` WHERE `ID`='".$ID."'";
                $result = $conn->query($SQL);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        //JSONifies the name
                        $compoundedName = json_decode($row['name'], true);
                        $compoundedName[$lang] = $name;
                        $name = json_encode($compoundedName, JSON_UNESCAPED_UNICODE);

                        //JSONifies the href
                        $compoundedSource = json_decode($row['source'], true);
                        $compoundedSource[$lang] = $source;
                        $source = json_encode($compoundedSource, JSON_UNESCAPED_UNICODE);

                        $SQL2 = "UPDATE `".strtolower($LBpage)."` SET `name`='".$name."', `source`='".$source."' WHERE `ID`='".$ID."'";
                    }
                } else {
                    //JSONifies the name and href
                    $name = '{"'.$lang.'":"'.$name.'"}';
                    $source = '{"'.$lang.'":"'.$source.'"}';

                    //Creates SQL
                    $SQL2 = "INSERT INTO `".strtolower($LBpage)."`(`enactDate`, `enforceDate`, `lastactDate`, `ID`, `name`, `country`, `regime`, `type`, `status`, `source`)
                            VALUES ('".$enactDate."', '".$enforceDate."', '".$lastactDate."', '".$ID."', '".$name."', ".$country."', '".$regime."', '".$type."', ".$status."', '".$source."')";
                }

                //Executes the SQL
                echo 'p.'.$page.':'.$law->find('div.count-document')[0]->find('span.count-text')[0]->plaintext.' '.$SQL2.'<br/>';
                if (!$test) {$conn->query($SQL2);}
            }
        }

        //Updates the date on the countries table
        $SQL3 = "UPDATE `countries` SET `lawsUpdated`='".date('Y-m-d')."' WHERE `ID`='".$scraper."'"; echo '<br/><br/>'.$SQL3;
        if (!$test) {$conn2->query($SQL3);}
    ?>
</body></html>