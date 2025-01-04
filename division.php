<html>
<head>
    <?php
        require 'lbheads/division.php';
    ?>
</head>
<body>
    <?php
        require 'lbbodies/division.php';
    ?>

    <?php //Closes the database connection
        $conn->close();
    ?>
</body>
</html>