<html>
<head>
    <?php
        require 'generic_heads/division.php';
    ?>
</head>
<body>
    <?php
        require 'generic_bodies/division.php';
    ?>

    <?php //Closes the database connection
        $conn->close();
    ?>
</body>
</html>