<html>
<head>
    <?php /*Imports the head*/ require 'lbheads/country.php'?>
</head>
<body>
    <div id="centerdiv">
        <?php //Fetches the laws
            $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
            $result = $dataConn->query($sql);

            if ($result->num_rows > 0) {
                //Outputs data
                while ($row = $result->fetch_assoc()) {
                    $overview = json_decode($row["overview"], true);
                    if (isset($overview[$lang])) {
                        echo '<p id="overview">'.$overview[$lang].'</p>';
                    }
                }
            }
        ?>
    </div>
    <?php /*Imports the body*/ require 'lbbodies/country.php';?>
    <?php /*Closes the DB connection*/ $dataConn->close();?>
</body>
</html>