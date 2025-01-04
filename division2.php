<html>
<head>
    <?php
        require 'lbheads/division2.php';
    ?>
</head>
<body>
    <div id="centerdiv"></div>

    <?php 
        require 'lbbodies/division2.php';
    ?>

    <?php //Closes the connection to database
        $conn->close();
    ?>
</body>
</html>