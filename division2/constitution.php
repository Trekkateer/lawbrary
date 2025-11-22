<html>
<head>
    <?php /*Imports the head*/ include('../lbheads/division.php');?>
</head>
<body>
    <div id="centerdiv">
        <div id="contentdiv">
            <?php //Fetches the laws
                $SQL = "SELECT * FROM `constitutions` WHERE `country`='".$ID."'";
                $result = $lawConn->query($SQL);

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
                            echo str_replace('$enLink', $enLink, $translations["NO_CONST"]);
                        }
                    }
                }
            ?>
        </div>
    </div>
    <?php /*Imports the body*/ include('../lbbodies/division.php');?>
    <?php /*Closes the DB connection*/ $lawConn->close();?>
</body>
</html>