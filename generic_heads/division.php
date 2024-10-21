
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
    exit('<script>window.location.replace("'.$destination.'");</script>');
}
?>
<?php //Gets URL Parameters
$path = $_SERVER['REQUEST_URI'];
if (explode('?', $path)[1]) {//Gets the url params
    parse_str(parse_url($path)['query'], $params);
} else {redirect('search.php?q=&type=division');}
//Sets ID
$ID = strtoupper($params['id']);
?>
<?php //Gets language and country name
$username="ug0iy8zo9nryq";
$password="T_1&x+$|*N6F";
$database="dbupm726ysc0bg";

$conn = new mysqli("localhost", $username, $password, $database);
$conn->select_db($database) or die("Unable to select database");

$sql = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
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
<?php /*Creates title*/echo '<title>'.$name.' - Lawbrary</title>';?>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link rel="stylesheet" type="text/css" href="styles/country_style.css"></link>
<!--link rel="stylesheet" type="text/css" href="styles/search_style.css"></link-->