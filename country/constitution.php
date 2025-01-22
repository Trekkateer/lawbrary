<html>
<head>
    <?php /*Imports the head*/ include('../lbheads/country.php');?>
</head>
<body>
    <div id="centerdiv">
        <?php //Fetches the laws
            $SQL = "SELECT * FROM `constitutions` WHERE `country`='".$ID."'";
            $result = $conn->query($SQL);

            if ($result->num_rows > 0) {
                //Outputs data
                while ($row = $result->fetch_assoc()) {
                    if (isset(json_decode($row['source'], true)[$lang])) {
                        if (json_decode($row['source'], true)[$lang] === 'local') {
                            $src = '/documents/constitutions/'.$ID.'-'.$lang.'.pdf';
                        } else {$src = json_decode($row['source'], true)[$lang];}
                        echo '<object width="500px" height="678px" data="'.$src.'" type="application/pdf">
                            <p>Unable to display PDF file. <a href="'.$src.'" target="blank">Download</a> instead.</p>
                        </object>';
                    } else {//In case there is no translation available
                        $enLink = '<a href="//en.'.$basedomain.$path.'">English</a>';
                        echo str_replace('$enLink', $enLink, $translations["NOCONST"]);
                    }
                }
            }
        ?>
    </div>
    <?php /*Imports the body*/ include('../lbbodies/country.php');?>
    <?php /*Closes the connection to database*/ $conn->close();?>
</body>
</html>