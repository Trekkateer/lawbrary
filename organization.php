<html lang="en">
<head>
<meta charset="utf-8">
    <?php //Some functions we will need
    function console_log($output, $with_script_tags = true) {
        $js_code = 'console.log('.json_encode($output, JSON_HEX_TAG).');';
        if ($with_script_tags) {
            $js_code = '<script>'.$js_code.'</script>';
        }
        echo $js_code;
    }
    function redirect($destination) {
        exit("<script>window.location.replace('".$destination."');</script>");
    }
    ?>
    <?php //Gets URL Parameters
    $path = $_SERVER['REQUEST_URI'];
    if (explode('?', $path)[1]) {//Gets the url params
        parse_str(parse_url($path)['query'], $params);
    } else {redirect('search.php?q=&type=country');}
    if ($params && !$params['doc']) {
        redirect($path.'&doc=laws');
    }
    ?>
    <?php //Gets language and treaty name
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $lawConn = new mysqli("localhost", $username, $password, $database);
    $lawConn->select_db($database) or die("Unable to select database");
    
    $sql = "SELECT * FROM `organizations` WHERE `ID`='".strtoupper($params['id'])."'";
    $result = $lawConn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //Gets Language
            $domain = $_SERVER['HTTP_HOST'];
            $basedomain = 'l'.explode('.l', $domain)[1]; //Using '.l' instead of '.' allows for testing with localhost
            $subdomain = explode('.l', $domain)[0]; //Determines which language we're using
            //Makes sure that the language is used by the treaty
            if (strlen($subdomain) === 2 && str_contains($row['name'], '"'.$subdomain.'"')) {
                $lang = $subdomain;
            } else {redirect('//en.'.$basedomain.$path);}

            //Gets appropriate name
            $name = json_decode($row['name'], true)[$lang];
        }
    } else {redirect('errors/404.php');}
    ?>
    <?php /*Creates title*/echo "<title>".$name." - Lawbrary</title>";?>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico"></link><!--Favicon-->
    <link rel="stylesheet" type="text/css" href="styles/country.css"></link><!--CSS Stylesheet-->
</head>
<body>
    <div id="leftdiv">
        <?php
        $sql = "SELECT * FROM `organizations` WHERE `ID`='".strtoupper($params['id'])."'";
        $result = $lawConn->query($sql);

        if ($result->num_rows > 0) {
            //Output data of each row
            while ($row = $result->fetch_assoc()) {
                $members = json_decode($row["children"], true);
                foreach ($members as $key => $val) {
                    if ($val) {
                        echo "<h2>".$key."</h2>";
                        foreach ($val as $value) {
                            $sql2 = "SELECT * FROM `countries` WHERE `ID`='".$value."'";
                            $result2 = $lawConn->query($sql2);
                            if ($result2->num_rows > 0) {
                                while ($row2 = $result2->fetch_assoc()) {
                                    if (isset(json_decode($row2['name'], true)[$lang])) {
                                        $memberName = json_decode($row2['name'], true)[$lang];
                                    } else {$memberName = json_decode($row2['name'], true)["en"];}
                                    $memberType = "country";
                                }
                            } else {
                                $sql2 = "SELECT * FROM `divisions` WHERE `ID`='".$value."'";
                                $result2 = $lawConn->query($sql2);
                                while ($row2 = $result2->fetch_assoc()) {
                                    if (isset(json_decode($row2['name'], true)[$lang][0])) {
                                        $memberName = json_decode($row2['name'], true)[$lang];
                                    } else {$memberName = json_decode($row2['name'], true)["en"];}
                                    $memberType = "division";
                                }
                            }
                            echo "<a href='".$memberType.".php?id=".strtolower($value)."&doc=laws'>".$memberName."</a><br>";
                        }
                        echo "<br>";
                    }
                }
            }
        }
        ?>
    </div>

    <div id="centerdiv"></div>

    <div id="rightdiv">
        <?php
        $sql = "SELECT * FROM `organizations` WHERE `ID`='".strtoupper($params['id'])."'";
        $result = $lawConn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                if ($row["hasFlag"]) {
                    $src="https://flagpedia.net/data/org/w580/".$params['id'].".webp";
                    echo "<img id='flag' height='150px' src=".$src." alt='Flag of ".$name."'>";
                }
                echo "<p id='country-type'>".$row["type"]."</p>";
            }
        }
        ?>
    </div>

    <div id="topdiv">
        <div id="language-div">
            <?php //Language flag
            echo "<img id='language-flag' height='32px' src='images/languageFlags/".$lang.".png'>"
            ?>
            <select id="language-selector" onchange="langChange()">
                <?php
                //Language selector
                $sql = "SELECT * FROM `organizations` WHERE `ID`='".strtoupper($params['id'])."'";
                $names = json_decode($lawConn->query($sql)->fetch_assoc()['name'], true);
                foreach ($names as $key => $val) {
                    $sql2 = "SELECT * FROM `languages` WHERE `ID`='".$key."'";
                    $result = $lawConn->query($sql2)->fetch_assoc();
                    $selected = $result['ID'] === $lang ? "selected":"";
                    echo "<option value='".$result['ID']."'".$selected.">".$result['name']."</option>";
                }
                ?>
            </select>
            <?php //Creates onchange parameter
            echo "<script>
                function langChange () {
                    window.location.replace('//'+document.getElementById('language-selector').value+'.".$basedomain.$path."')
                };
            </script>";
            ?>
        </div>

        <div id="title">
            <?php /*Country Name*/ echo "<h1 id='country-name' style='margin: 0px;'>".$name."</h1>";?>

            <?php //Queries database and creates anchor tag
            $sql = "SELECT `homesite` FROM `organizations` WHERE `ID`='".strtoupper($params['id'])."'";
            $homesite = $lawConn->query($sql)->fetch_assoc()["homesite"];
            if ($homesite) {echo "<a id='official-website' href='".$homesite."' target='blank'>Official Website</a>";}
            ?>
        </div>

        <!--Searchbar-->
        <?php include 'searchbar.php';?>
        
        <!--Closes the connection-->
        <?php $lawConn->close();?>
    </div>
</body>
</html>