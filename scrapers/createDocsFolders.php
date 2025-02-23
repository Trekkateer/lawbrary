<?php
    //Connects to the content database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Creates the folder for the documents
    $sql = "SELECT * FROM `countries`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        //Creates a folder for each country
        $destination = '../documents/'.$row['ID'];
        if (!file_exists($destination)) mkdir($destination);

        //Creates a folder for every language
        foreach (json_decode($row['langs'], true)['Display'] as $language) {
            $destination .= '/'.$language;
            if (!file_exists($destination)) mkdir($destination);
        }
    }

    //Closes the connection
    $conn->close();
?>