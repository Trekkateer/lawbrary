<html>
<head>
    <?php /*Imports the head*/ include('../lbheads/country.php');?>
    <?php //Sets the page
        $page = $params['page'] ?? 1;
        if ($page <= 0) {$page = 1;}
    ?>
</head>
<body>
    <div id="centerdiv">
        <?php //Fetches the laws
            $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
            $result = $conn->query($SQL);

            if ($result->num_rows > 0) {
                //Outputs data
                while ($row = $result->fetch_assoc()) {
                    $SQL2 = "SELECT * FROM `".strtolower($ID)."` ORDER BY `enactDate` DESC";
                    $result2 = $conn2->query($SQL2);

                    if ($result2->num_rows > 0) {
                        //Tells us the number of rows and when the database was last updated
                        echo '<p id=rowCounter>Showing '.(($page-1)*50+1).'-'.($page*50).' of '.$result2->num_rows.' results — Database last updated '.date('d M Y', strtotime($row["lawsUpdated"])).'</p>';

                        //Creates language selector

                        //Creates buttons to select different laws
                        //echo '<p class="blue_sm_11" onclick="">next</p>';
                        $SQL3 = "SELECT * FROM `".strtolower($ID)."` ORDER BY `enactDate` DESC LIMIT 50 OFFSET ".($page-1)*50;
                        $result3 = $conn2->query($SQL3);
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
                    } else {echo str_replace('$name', $name, $translations["NOLAWS"]);}
                }
            }
        ?>
    </div>

    <?php /*Imports the body*/ include('../lbbodies/country.php');?>
    <?php /*Closes the DB connection*/ $conn->close();?>
</body>
</html>