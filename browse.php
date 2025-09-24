<html>
<head>
    <title>Lawbrary - Browse Legislation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php //Some functions we will need
        function console_log($output, $with_script_tags = true) {
            $js_code = 'console.log('.json_encode($output, JSON_HEX_TAG).');';
            if ($with_script_tags) {
                $js_code = '<script>'.$js_code.'</script>';
            }
            echo $js_code;
        }
        function redirect($destination) {//Redirects to another page on the domain
            exit('<script>window.location.replace("'.$destination.'");</script>');
        }
    ?>
    <?php //Gets the url params
        $path = $_SERVER['REQUEST_URI'];
        if (isset(explode('?', $path)[1])) {
            parse_str(parse_url($path)['query'], $params);
        }

        //Gets Language
        $domain = $_SERVER['HTTP_HOST'];
        $basedomain = 'l'.explode('.l', $domain)[1]; //Using '.l' instead of '.' allows for testing with localhost
        $subdomain = $lang = explode('.l', $domain)[0]; //Determines which language we're using
    ?>
    <?php //Gets translations for text on the website
        //Connects to the content database
        $username2="ug0iy8zo9nryq"; $password2="T_1&x+$|*N6F"; $database2="dbupm726ysc0bg";
        $conn2 = new mysqli("localhost", $username2, $password2, $database2);
        $conn2->select_db($database2) or die("Unable to select database");

        $SQL2 = 'SELECT * FROM `languages` WHERE `ID`="'.$lang.'"';
        $result = $conn2->query($SQL2);
        if ($result->num_rows > 0) {
            //Gets the translations
            $translations = json_decode($result->fetch_assoc()['translations'], true);
        }
    ?>
    <?php //Database connections
        //Connects to the law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");
    ?>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico"></link>

    <style> /* Adds icon to external links */
        a[target="_blank"]::after {
            content: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAQElEQVR42qXKwQkAIAxDUUdxtO6/RBQkQZvSi8I/pL4BoGw/XPkh4XigPmsUgh0626AjRsgxHTkUThsG2T/sIlzdTsp52kSS1wAAAABJRU5ErkJggg==);
            margin: 0 3px 0 5px;
        }

        #topdiv {
            text-align: center;
            position: fixed;
            left: 50%; top: 0%;
            width: 100%; height: 80px;
            transform: translate(-50%, 0%);
            background-color: silver;
        } #logo {
            position: absolute;
            left: 1%; top: 45%;
            transform: translate(0%, -50%);
        } #language-div {
            position: absolute;
            left: 5%; top: 50%;
            transform: translate(0%, -50%);
        } #language-flag {
            position: absolute;
            left: 0%; top: 50%;
            transform: translate(0%, -50%);
        } #language-selector {
            position: absolute;
            height: 32px;
            left: 41px; top: 50%;
            transform: translate(0%, -50%);
            border: 5px double brown;
            font-size: 14px;
        } #title-div {
            position: absolute;
            left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            white-space: nowrap;
        } #search-form {
            position: absolute;
            width: 310px; height: 20px;
            left: 86%; top: 50%;
            transform: translate(-50%, -50%);
        } #searchbar {
            position: absolute;
            width: 175px; height: 32px;
            left: 0%; top: 50%;
            transform: translate(0%, -50%);
            border: 5px double brown;
            font-size: 14px;
        }/* #searchbar::-webkit-search-cancel-button {
            position:relative;
            right:20px;
            -webkit-appearance: none;
            height: 20px;
            width: 20px;
            border-radius:10px;
            background: url(images/search.png);
        }*/ #type-selector {
            position: absolute;
            width: 80px; height: 32px;
            left: 180px; top: 50%;
            transform: translate(0%, -50%);
            border: 5px double brown;
            font-size: 12px;
        } #country-selector {
            position: absolute;
            width: 45px; height: 32px;
            right: 0%; top: 50%;
            transform: translate(0%, -50%);
            border: 5px double brown;
            font-size: 12px;
        } #navbar {
            text-align: center;
            width: 100%;
        } #navlist {
            list-style-type: none;
            margin: 0; padding: 0;
            overflow: hidden;
            background-color: silver;
            display: inline-block;
        } .navlink {
            display: block;
            color: brown;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        } li {
            float: center;
        } li a:hover {background-color: gray;}

        #leftdiv {
            position: absolute;
            width: 20%;
            left: 12.5%; top: 125px;
        } #mapImg {
            width: 100%;
            border: 5px solid brown;
        } #disputeDisclaimer {
            width: 294px;
            word-wrap: normal;
        }

        #centerdiv {
            text-align: center;
            position: absolute;
            width: 45%;
            left: 50%; top: 135px;
            transform: translate(-50%, 0%);
        } .lawlink {word-wrap: normal;}

        #rightdiv {
            position: absolute;
            width: 20%;
            left: 87.5%; top: 125px;
        } #flag {
            width: 100%;
        } #country-type {
            color: brown;
            font-family: garamond, serif;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="leftdiv"> <!-- Left filter pane -->

    </div>

    <div id="topdiv"> <!-- Top navigation pane -->
        <!--Lawbrary logo-->
        <a id="logo" href="/"><img src="/images/favicon64.png" width="40px"></img></a>
        <div id="language-div">
            <?php //Language flag
                //Gets the language data
                $SQL = "SELECT * FROM `languages` WHERE `ID`='".$lang."'";
                $result = $conn2->query($SQL)->fetch_assoc();

                //Displays the language flag
                if ($result['hasFlag']) {
                    echo '<img id="language-flag" height="32px" src="/images/languageFlags/'.$lang.'.png">';
                }
            ?>
            <select id="language-selector" onchange="langChange(document.getElementById('language-selector').value)">
                <?php //Language selector
                    foreach ($languages["Display"] as $language) {
                        //Gets the language data
                        $SQL = "SELECT * FROM `languages` WHERE `ID`='".$language."'";
                        $result = $conn->query($SQL)->fetch_assoc();

                        //Outputs the selector
                        $selected = $language === $lang ? ' selected':'';
                        echo '<option value="'.$language.'"'.$selected.'>'.json_decode($result['name'], true)[$language].'</option>';
                    }
                ?>
            </select>
            <?php //Creates onchange parameter
            echo '<script>
                function langChange (newLang) {
                    window.location.replace("//"+newLang+".'.$basedomain.$path.'")
                };
            </script>';
            ?>
        </div>
        
        <!--Gets the searchbar-->
        <?php include __DIR__.'/searchbar.php';?>
    </div>
</body>
</html>