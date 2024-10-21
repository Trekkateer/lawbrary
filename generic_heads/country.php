<meta charset="utf-8">
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
<?php  //Gets the url params
    $path = $_SERVER['REQUEST_URI'];
    if (isset(explode('?', $path)[1])) {
        parse_str(parse_url($path)['query'], $params);
    } else {redirect('/errors/404.html');}

    //Sets ID
    $ID = strtoupper($params['id']);
?>
<?php //Gets language and name
    $username="ug0iy8zo9nryq";
    $password="T_1&x+$|*N6F";
    $database="dbupm726ysc0bg";

    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //Gets Language
            $domain = $_SERVER['HTTP_HOST'];
            $basedomain = 'l'.explode('.l', $domain)[1]; //Using '.l' instead of '.' allows for testing with localhost
            $subdomain = explode('.l', $domain)[0]; //Determines which language we're using
            //Makes sure that the language is used by the country
            if (str_contains($row['name'], '"'.$subdomain.'"')) {
                $lang = $subdomain;
            } else {redirect('//en.'.$basedomain.$path);}

            //Gets appropriate name
            $name = json_decode($row['name'], true)[$lang];
        }
    } else {redirect('errors/404.php');}
?>
<?php //Gets translations for text on the website
    $sql2 = 'SELECT * FROM `languages` WHERE `ID` LIKE \'%"'.$lang.'"%\'';
    $result = $conn->query($sql2);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //Lets the translations
            $translations = json_decode($row['translations'], true);
        }
    }
?>
<?php //Gets the flag
    $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['hasFlag']) {//Outputs the flag
                if (strtolower($params['id'] === 'danish-realm')) {
                    $flagSrc='https://flagpedia.net/data/flags/w580/dk.webp';
                } else if (strtolower($params['id'] === 'kosovo')) {
                    $flagSrc='https://flagpedia.net/data/flags/w580/xk.webp';
                } else if (strtolower($params['id']) === 'ps-west-bank') {
                    $flagSrc='https://flagpedia.net/data/flags/w580/ps.webp';
                } else {$flagSrc='https://flagpedia.net/data/flags/w580/'.strtolower($params['id']).'.webp';}
            }
        }
    }
    ?>
<?php /*Creates title*/echo '<title>'.$name.' - Lawbrary</title>';?>
<link rel="icon" type="image/x-icon" href="/images/favicon.ico"></link><!--Favicon-->
<link rel="stylesheet" type="text/css" href="/styles/country_style.css"></link><!--CSS Stylesheet-->
<style>
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
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: silver;
        width: 354.66;
        position: relative;
        left: 50%; top: 80px;
        transform: translate(-50%);
    }  .navlink {
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
        left: 50%; top: 125px;
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