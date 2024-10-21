<?php
    //Updates the date on the countries table
    foreach(array('countries'/*, 'divisions', 'divisions2', 'organizations'*/) as $table) {
        //Connects to the content database
        $username="ug0iy8zo9nryq";
        $password="T_1&x+$|*N6F";
        $database="dbupm726ysc0bg";
        $conn = new mysqli("localhost", $username, $password, $database);
        $conn->select_db($database) or die("Unable to select database");

        $SQL = "SELECT * FROM `".$table."`";
        $result = $conn->query($SQL);

        while($row = $result->fetch_assoc()) {
            //Connects to the Law database
            $username2="u9vdpg8vw9h2e";
            $password2="f1x.A1pgN[BwX4[t";
            $database2="dbpsjng5amkbcj";
            $conn2 = new mysqli("localhost", $username2, $password2, $database2);
            $conn2->select_db($database2) or die("Unable to select database");

            //Deletes old tables
            $SQL2 = "DROP TABLE IF EXISTS `laws".$row["ID"]."`";
            $conn2->query($SQL2);

            //Creates new tables
            $SQL3 = "CREATE TABLE IF NOT EXISTS `laws".$row['ID']."` (
                `enactDate` date NOT NULL,
                `enforceDate` date NOT NULL,
                `lastactDate` date NOT NULL,
                `endDate` date,
                `ID` text NOT NULL,
                `regime` text NOT NULL,
                `name` text NOT NULL,
                `type` text NOT NULL,
                `status` text NOT NULL,
                `origin` text,
                `signers` text,
                `summary` text,
                `topic` text,
                `keywords` text,
                `note` text,
                `source` text NOT NULL,
                `PDF` text,
                `HTML` text,
                `text` text
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $conn2->query($SQL3);
        }
    }
?>