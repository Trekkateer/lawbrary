<html>
<head>
    <?php
        include('../generic_heads/division.php')
    ?>
</head>
<body>
    <div id="centerdiv">
        <div id="contentdiv">
            <?php //Fetches the laws
                $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    //Outputs data
                    while ($row = $result->fetch_assoc()) {
                        $sql2 = "SELECT * FROM `laws` WHERE `country`='".$ID."'";
                        $result2 = $conn->query($sql2);

                        if ($result2->num_rows > 0) {
                            //Tells us the number of rows
                            echo '<p id=rowCounter>'.$result2->num_rows.' results — Database last updated '.date('d M Y', strtotime($row["lawsUpdated"])).'</p>';

                            //Outputs data
                            $loadLaws = function($offset = 0) use ($conn, $row, $ID, $lang) {
                                $sql3 = "SELECT * FROM `laws` WHERE `country`='".$ID."'";// ORDER BY `laws`.`enactDate` DESC";
                                $result3 = $conn->query($sql3);
                                //Outputs data
                                while ($row3 = $result3->fetch_assoc()) {
                                    //Creates container div, number and date
                                    echo '<div id="law"><p>'.$row3['enactDate'].': ';
    
                                    //Gets the name
                                    $lawName = json_decode($row3['name'], true)[$lang] ?? json_decode($row3['name'], true)[$row['defaultLang']];
    
                                    //Creates the link and ends div
                                    echo '<a class="lawlink" href="/law.php?id='.$row3['ID'].'" target="_blank">'.$lawName.'</a>';
                                    echo '</p></div>';
                                }
                            };
    
                            $loadLaws();
                        }
                    }
                } else {echo strtr($translations[4], array('[name]'=>$name));}
            ?>
        </div>
    </div>

    <?php
        include('../generic_bodies/division.php')
    ?>

    <?php //Closes the connection to database
        $conn->close();
    ?>
</body>
</html>