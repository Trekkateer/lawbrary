<html>
<head>
    <?php /*Imports the head*/ include('../lbheads/division.php');?>
</head>
<body>
    <div id="centerdiv">
        <div id="contentdiv">
            <?php //Fetches the laws
                $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
                $result = $lawConn->query($SQL);

                if ($result->num_rows > 0) {
                    //Outputs data
                    while ($row = $result->fetch_assoc()) {
                        $SQL2 = "SELECT * FROM `".strtolower($country)."` WHERE `country`='".$ID."'";
                        $result2 = $lawConn->query($SQL2);

                        if ($result2->num_rows > 0) {
                            //Tells us the number of rows
                            echo '<p id=rowCounter>'.$result2->num_rows.' results — Database last updated '.date('d M Y', strtotime($row["lawsUpdated"])).'</p>';

                            //Outputs data
                            $loadLaws = function($offset = 0) use ($lawConn, $row, $ID, $lang) {
                                $SQL3 = "SELECT * FROM `".strtolower($country)."` WHERE `country`='".$ID."'";// ORDER BY `".strtolower($country)."`.`enactDate` DESC";
                                $result3 = $lawConn->query($SQL3);
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
                } else {echo str_replace('$name', $name, $translations["NOLAWS"]);}
            ?>
        </div>
    </div>
    <?php /*Imports the body*/ include('../lbbodies/division.php');?>
    <?php /*Closes the DB connection*/ $lawConn->close();?>
</body>
</html>