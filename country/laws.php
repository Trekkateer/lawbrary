<html>
<head>
    <?php /*Imports the head*/ include('../lbheads/country.php');?>
    <?php //Sets the page
        $page = $params['page'] ?? 1;
        if ($page <= 0) {$page = 1;}

        //Sets the sort order
        $sort = explode('.', $params['sort'] ?? 'enactDate.desc')[0] ?? 'enactDate';
        $sortOrder = explode('.', $params['sort'] ?? 'enactDate.desc')[1] ?? 'desc';
    ?>
</head>
<body>
    <div id="centerdiv">
        <?php //Fetches the laws
            $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
            $result = $dataConn->query($SQL); //Gets the last-updated date

            if ($result->num_rows > 0) {
                //Checks to make sure the country has laws
                while ($row = $result->fetch_assoc()) {
                    $SQL2 = "SELECT * FROM `".strtolower($ID)."`";
                    $result2 = $lawConn->query($SQL2);

                    if ($result2->num_rows > 0) {
                        //Tells us the number of rows and when the database was last updated
                        echo '<div style="border: 1px solid gray; border-radius: 5px; top: 130px;">
                                <p id=rowCounter style="text-align: left; margin-left: 10px;">Showing '.(($page-1)*50+1).'-'.($page*50).' of '.$result2->num_rows.' results — database last updated '.date('d M Y', strtotime($row["lawsUpdated"])).'</p>';

                        //Sort order selection
                        echo '<div style="text-align: right;">
                                <select onchange="location.href=\'?id='.$ID.'&sort='.$sort.'.\'+this.value+\''.'&page='.$page.'\';" style="margin-right: 5px; padding: 5px 10px; background-color: silver; border: silver; border-radius: 3px;">
                                  <option value="asc" '.($sortOrder == 'asc' ? 'selected' : '').'>Asc</option>
                                  <option value="desc" '.($sortOrder == 'desc' ? 'selected' : '').'>Desc</option>
                                </select>';

                        //Sort selection
                        echo   '<select onchange="location.href=\'?id='.$ID.'&sort=\'+this.value+\'.'.$sortOrder.'&page='.$page.'\';" style="margin-right: 5px; padding: 5px 10px; background-color: silver; border: silver; border-radius: 3px;">
                                  <option value="enactDate" '.($sort == 'enactDate' ? 'selected' : '').'>Date</option>
                                  <option value="name" '.($sort == 'name' ? 'selected' : '').'>Name</option>
                                </select>';

                        //Pagination buttons
                        if ($page > 1) {
                            echo '<a class="paginator" href="?id='.$ID.'&sort='.$sort.'&page='.($page - 1).'">< '.($page - 1).'</a>';
                        }
                        if ($page * 50 < $result2->num_rows) {
                            echo '<a class="paginator" href="?id='.$ID.'&sort='.$sort.'&page='.($page + 1).'">'.($page + 1).' ></a>';
                        }
                        echo '</div></div>';

                        //Gets the laws and outputs them by date 50 at a time
                        echo '<div style="border: 1px solid gray; border-radius: 5px; margin-top: 5px; margin-bottom: 5px;">';
                            $SQL3 = "SELECT * FROM `".strtolower($ID)."` ORDER BY `".$sort."` ".strtoupper($sortOrder)." LIMIT 50 OFFSET ".($page-1)*50;
                            $result3 = $lawConn->query($SQL3);
                            //Outputs data
                            while ($row3 = $result3->fetch_assoc()) {
                                //Creates container div, number and date
                                echo '<div class="law"><p>'.$row3['enactDate'].': ';

                                //Gets the name
                                $lawName = json_decode($row3['name'], true)[$lang] ?? json_decode($row3['name'], true)[$defaultLang];

                                //Creates the link and ends div
                                echo '<a class="lawlink" href="/law.php?id='.$row3['ID'].'" target="_blank">'.$lawName.'</a>';
                                echo '</p></div>';
                            }
                        echo '</div>';
                    } else {
                        echo '<div style="border: 1px solid gray; border-radius: 5px; top: 130px; width: 435px; margin: auto;">
                                <p id=rowCounter style="text-align: center;">'.str_replace('[name]', $name, $translations["NO_LAWS"]).'</p>
                              </div>';
                    }
                }
            }
        ?>
    </div>

    <?php /*Imports the body*/ include('../lbbodies/country.php');?>
    <?php /*Closes the DB connection*/ $lawConn->close();?>
</body>
</html>