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
        function redirect($destination) {//Redirects to another page on the domain
            exit('<script>window.location.replace("'.$destination.'");</script>');
        }
    ?>
    <?php //Gets the url params, redirects if they are missing
        $path = $_SERVER['REQUEST_URI'];
        if (isset(explode('?', $path)[1])) {
            parse_str(parse_url($path)['query'], $params);
        } else {redirect('/errors/404.html');}

        //Gets Language
        $domain = $_SERVER['HTTP_HOST'];
        $basedomain = 'l'.explode('.l', $domain)[1]; //Using '.l' instead of '.' allows for testing with localhost
        $subdomain = explode('.l', $domain)[0]; //Determines which language we're using

        //Sets ID and country
        $ID = strtoupper($params['id']);
        $country = explode(':', $params['id'])[0];
    ?>
    <?php //Redirects if the law is not local
        //Connects to the law database
        $username="u9vdpg8vw9h2e"; $password="f1x.A1pgN[BwX4[t"; $database="dbpsjng5amkbcj";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        $SQL = "SELECT * FROM `".strtolower($country)."` WHERE `ID`='".$ID."'";
        $result = $conn->query($SQL);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //Makes sure that the language is used by the country
                if (strlen($subdomain) === 2 && str_contains($row['name'], '"'.$subdomain.'"')) {
                    $lang = $subdomain;
                } else {redirect('//en.'.$basedomain.$path);}

                //Redirects if the law is not local
                //Gets the source
                if ($row['source'] !== 'local') {
                    $src = json_decode($row['source'], true)[$lang] ?? json_decode($row['source'], true)['en'];
                    redirect($src);
                }

                //Gets appropriate name
                $name = json_decode($row['name'], true)[$lang];
            }
        } else {redirect('errors/404.php');}
    ?>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico"></link>
</head>
<body>

</body>
</html>