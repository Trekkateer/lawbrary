<html>
<head>
    <?php
        require 'generic_heads/division2.php';
    ?>
</head>
<body>
    <div id="centerdiv"></div>

    <?php 
        require 'generic_bodies/division2.php';
    ?>

    <?php //Closes the connection to database
        $conn->close();
    ?>
</body>
</html>