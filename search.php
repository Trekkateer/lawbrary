<html>
<head>
    <meta charset="utf-8">
    <title>Lawbrary | All the world's laws in one place</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <?php
        function console_log($output, $with_script_tags = true) {
            $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
            if ($with_script_tags) {
                $js_code = '<script>' . $js_code . '</script>';
            }
            echo $js_code;
        }
        function redirect($destination) {
            exit ("<script>window.location.replace('".$destination."');</script>");
        }
    ?>
    <?php //Gets URL Parameters and makes sure they are valid
        $url = $_SERVER['REQUEST_URI'];
        if (isset(explode('?', $url)[1])) {parse_str(parse_url($url)['query'], $params);}
        //Replaces "%20" with space and redirects if q is not set
        if (isset($params['q'])) {
            $params['q'] = str_replace("%20", " ", $params['q']);
        } else {
            redirect('search.php?q=&type='.$params['type']);
        }
        //Redirects if type is not set
        if (!isset($params['type'])) {
            redirect('search.php?q='.$params['q'].'&type=global');
        }
    ?>
    <?php //Gets language
        $domain = $_SERVER['HTTP_HOST'];
        $basedomain = 'l'.explode('.l', $domain)[1]; //Using '.l' instead of '.' allows for testing with localhost
        $subdomain = explode('.l', $domain)[0]; //Determines the language we're using
        //Makes sure that the language is used by the country
        if (strlen($subdomain) === 2) {$lang = $subdomain;}
    ?>
    <?php //Gets translations for text on the website
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");
        $conn2->query("SET NAMES 'utf8'");

        $SQL2 = 'SELECT * FROM `languages` WHERE `ID`="'.$lang.'"';
        $result = $conn2->query($SQL2);
        if ($result->num_rows > 0) {
            //Gets the translations
            $translations = json_decode($result->fetch_assoc()['translations'], true);
        }
    ?>
    <style>
        h1, h2 {
            color: brown;
        }

        #dashboard {
            position: sticky;
            width: 100%; height: 100px;
        } #search-form {
            position: absolute;
            width: 620px; height: 44px;
            left: 50%; top: 50%;
			transform: translate(-50%, -50%);
        } #searchbar {
            position: absolute;
            width: 350px; height: 44px;
            left: 0%; top: 50%;
			transform: translate(0%, -50%);
            border: 7.5px double brown;
            font-size: 25px;
            padding: 0px 3px;
        } #type-selector {
            position: absolute;
            width: 120px; height: 44px;
            left: 365px; top: 50%;
            transform: translate(0%, -50%);
            border: 7.5px double brown;
            font-size: 16px;
        } #country-selector {
            position: absolute;
            width: 120px; height: 44px;
            right: 0%; top: 50%;
			transform: translate(0%, -50%);
            border: 7.5px double brown;
            font-size: 16px;
        }

        #results {
            position: absolute;
            left: 50%; top: 15%;
            transform: translate(-50%, 0%);
            width: 684px; height: 100px;
        }
    </style>
    <!--link rel="stylesheet" type="text/css" href="styles/search_style.css"></link-->
</head>
<body style="background: lightgrey">
    <header id="dashboard">
        <!--img id="logo" src="/images/favicon64.png" width="64" style="position: absolute; left: 50px; top: 50%; transform: translate(-50%, -50%);"></img-->
        <?php /*Gets the searchbar*/ require('searchbar.php');
            echo "<script>
                document.getElementById('searchbar').value='".$params['q']."'
                document.getElementById('type-selector').value='".$params['type']."'
            </script>";
        ?>
    </header>

    <div id="results">
    <?php //Search Results
        //Connects to the Law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        if ($params['q']) { echo "<h1>Search results for '".$params['q']."'</h1>";
            if ($params['type'] === "global" || $params['type'] === "country") {
                $sql = "SELECT * FROM `countries` WHERE JSON_EXTRACT(`name`, '$.".$lang."') LIKE '%".$params['q']."%' OR `ID`='".strtoupper($params['q'])."'";
                $result = $conn2->query($sql);
                if ($result->num_rows > 0) {
                    echo "<h2>Countries</h2>";
                    while ($row = $result->fetch_assoc()) {
                        $name = json_decode($row["name"], true)[$lang] ?? json_decode($row["name"], true)['en'];
                        if (strtoupper($params['q']) === strtoupper($name) || strtoupper($params['q']) === $row['ID']) {
                            redirect('/country.php?id='.strtolower($row['ID']));
                        } else {echo "<a href='/country.php?id=".strtolower($row['ID'])."'>".$name."</a><br>";}
                    }
                }
            } if ($params['type'] === "global" || $params['type'] === "division") {
                $sql = "SELECT * FROM `divisions` WHERE JSON_EXTRACT(`name`, '$.".$lang."') LIKE '%".$params['q']."%' OR `ID`='".strtoupper($params['q'])."'";
                $result = $conn2->query($sql);

                if ($result->num_rows > 0) {
                    echo "<h2>Divisions</h2>";
                    while ($row = $result->fetch_assoc()) {
                        $name = json_decode($row["name"], true)[$lang] ?? json_decode($row["name"], true)['en'];
                        if (strtoupper($params['q']) === strtoupper($name) || strtoupper($params['q']) === $row['ID']) {
                            redirect('/division.php?id='.strtolower($row['ID']));
                        } else {echo "<a href='/division.php?id=".strtolower($row['ID'])."'>".$name."</a><br>";}
                    }
                }
            } if ($params['type'] === "global" || $params['type'] === "organization") {
                $sql = "SELECT * FROM `organizations` WHERE JSON_EXTRACT(`name`, '$.".$lang."') LIKE '%".$params['q']."%' OR `ID`='".strtoupper($params['q'])."'";
                $result = $conn2->query($sql);

                if ($result->num_rows > 0) {
                    echo "<h2>Organizations</h2>";
                    while ($row = $result->fetch_assoc()) {
                        $name = json_decode($row["name"], true)[$lang] ?? json_decode($row["name"], true)['en'];
                        if (strtoupper($params['q']) === strtoupper($name) || strtoupper($params['q']) === $row['ID']) {
                            redirect('/treaty.php?id='.strtolower($row['ID']));
                        } else {echo "<a href='/treaty.php?id=".strtolower($row['ID'])."&doc=laws'>".$name."</a><br>";}
                    }
                }
            }/* if ($params['type'] === "global" || $params['type'] === "law") {
                $sql = "SELECT * FROM `".strtolower($country)."` WHERE JSON_EXTRACT(`name`, '$.".$lang."') LIKE '%".$params['q']."%' OR `ID`='".strtoupper($params['q'])."'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<h2>Laws</h2>";
                    while ($row = $result->fetch_assoc()) {
                        $name = json_decode($row["name"], true)[$lang] ?? json_decode($row["name"], true)[array_keys(json_decode($row["name"], true))[0]];
                        if (strtoupper($params['q']) === strtoupper($name) || strtoupper($params['q']) === $row['ID']) {
                            redirect('/law.php?id='.strtolower($row['ID']));
                        } else {echo "<a href='/law.php?id=".strtolower($row['ID'])."'>".$name."</a><br>";}
                    }
                }
            }*/
        }
    ?>
    </div>
</body>
</html>