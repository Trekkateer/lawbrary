<?php //DO NOT DELETE. COULD BE USED AFTER PROVINCE SCRAPING
//Settings
$test = false;
$firstLevel = 'divisions'; $secondLevel = 'divisions2';

//Connects to the Lawbrary database
$username="ug0iy8zo9nryq";
$password="T_1&x+$|*N6F";
$database="dbupm726ysc0bg";

$conn = new mysqli("localhost", $username, $password, $database);
$conn->select_db($database) or die("Unable to select database");

$sql =  "SELECT * FROM `languages` WHERE 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    //Gets the id and adjective
    while ($row = $result->fetch_assoc()) {
        //Sets the needed arrays
        $JSONKeys = array(
            "countries" => array(),
            "divisions" => array(),
            "divisions2" => array(),
        );

        //Starts the new value
        $newJSON = '{';
        
        //In case the values are already set
        if (isset(json_decode($row['officIn'], true)['countries'])) {
            foreach (json_decode($row['officIn'], true)['countries'] as $country) {
                array_push($JSONKeys['countries'], $country);
            }
        }
        if (isset(json_decode($row['officIn'], true)['divisions'])) {
            foreach (json_decode($row['officIn'], true)['divisions'] as $division) {
                array_push($JSONKeys['divisions'], $division);
            }
        }
        if (isset(json_decode($row['officIn'], true)['divisions2'])) {
            foreach (json_decode($row['officIn'], true)['divisions2'] as $division2) {
                array_push($JSONKeys['divisions2'], $division2);
            }
        }

        $sql2 = "SELECT * FROM `".$secondLevel."` WHERE `name` LIKE '%\"".$row['ID']."\"%'";
        $result2 = $conn->query($sql2);
        if ($result2->num_rows > 0) {
            while ($row2 = $result2->fetch_assoc()) {
                if (isset(json_decode($row['officIn'], true)[$firstLevel])) {
                    if (in_array($row2['parent'], json_decode($row['officIn'], true)[$firstLevel])) {
                        array_push($JSONKeys[$secondLevel], $row2['ID']);
                    }
                }
            }
            $newJSON = substr($newJSON, 0, strlen($newJSON)-2).']';
        }

        //Echos the new officIn value
        //echo $row['name'].' has '.json_encode($JSONKeys);

        $sql3 = "UPDATE `languages` SET `officIn`='".json_encode($JSONKeys)."' WHERE `ID`='".$row['ID']."'";
        echo $sql3;

        if (!$test) {$conn->query($sql3);}

        //echo $newJSON;
        echo '<br>';
    }
}

?>

