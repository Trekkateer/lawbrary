<?php
    //Connects to the content database
    $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
    $conn = new mysqli("localhost", $username, $password, $database);
    $conn->select_db($database) or die("Unable to select database");

    //Creates the folder for the documents of countries
    $sql = "SELECT * FROM `countries`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        //Creates a folder for each country
        $destination = '../documents/'.$row['ID'];
        mkdir($destination);

        //Creates a folder for every language in that country
        foreach (json_decode($row['langs'], true)['Display'] as $language) {
            if (!file_exists($destination.'/'.$language)) mkdir($destination.'/'.$language);
        }
    }

    //Creates the folder for the documents of divisions
    /*$sql = "SELECT * FROM `divisions`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        //Creates a folder for each country
        $destination = '../documents/'.$row['ID'];
        mkdir($destination);

        //Creates a folder for every language in that country
        foreach (json_decode($row['langs'], true)['Display'] as $language) {
            if (!file_exists($destination.'/'.$language)) mkdir($destination.'/'.$language);
        }
    }

    //Creates the folder for the documents of secondary divisions
    $sql = "SELECT * FROM `divisions2`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        //Creates a folder for each country
        $destination = '../documents/'.$row['ID'];
        mkdir($destination);

        //Creates a folder for every language in that country
        foreach (json_decode($row['langs'], true)['Display'] as $language) {
            if (!file_exists($destination.'/'.$language)) mkdir($destination.'/'.$language);
        }
    }*/

    //Closes the connection
    $conn->close();
?>