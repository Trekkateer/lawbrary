<html>
<head>
    <?php 
        require 'generic_heads/country.php'
    ?>
</head>
<body>
    <div id="centerdiv">
        <?php //Fetches the laws
            $sql = "SELECT * FROM `countries` WHERE `id`='".$ID."'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                //Outputs data
                while ($row = $result->fetch_assoc()) {
                    if (isset(json_decode($row['overview'], true)[$lang])) {
                        
                    } else {}
                }
            }
        ?>
    </div>

    <?php
        require 'generic_bodies/country.php';
    ?>

    <?php //Closes the database connection
        $conn->close();
    ?>
</body>
</html>