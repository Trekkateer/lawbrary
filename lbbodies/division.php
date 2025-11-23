<div id="leftdiv">
    <?php
    $SQL = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row['hasMap']) {
                //Pathname needs the English version
                $pathName = explode(' ', strtolower(json_decode($row['name'], true)['en']));
                $dashedURL2Name = '';
                for ($i=0; $i<count($pathName); $i++) {//Replaces spaces with dashes
                    $dashedURL2Name = $dashedURL2Name.$pathName[$i].'-';
                } $dashedURL2Name = substr($dashedURL2Name, 0, strlen($dashedURL2Name)-1);
                if ($row['parent'] === 'US') {
                    if (json_decode($row['type'], true)['en'] === 'State') {
                        if ($ID === 'US-DE' || $ID === 'US-NH' || $ID === 'US-RI' || $ID === 'US-VT') {
                            $path='usa/state/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'.jpg';
                        } else {
                            $path='usa/state/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'-max.jpg';
                        }
                    } else if ($ID === 'VI') {
                        $path='virgin-islands-us/map-of-us-virgin-islands-max.jpg';
                    } 
                } else if ($row['parent'] === 'GB') {
                    if (json_decode($row['type'], true)['en'] === 'Country') {
                        $path='uk/'.$dashedURL2Name.'/administrative-divisions-map-of-'.$dashedURL2Name.'-max.jpg';
                    } else if ($ID === 'VG') {
                        $path='virgin-islands-british/map-of-british-virgin-islands-max.jpg';
                    } else if ($ID === 'TC') {
                        $path='turks-and-caicos/map-of-turks-and-caicos-max.jpg';
                    } 
                } else if ($ID === 'DK') {
                    $path=$dashedURL2Name.'/map-of-'.$dashedURL2Name.'.jpg';
                } else if ($ID === 'BL') {
                    $path='st-barts/map-of-st-barts-max.jpg';
                } else if ($ID === 'CW') {
                    $path='curacao/map-of-curacao-max.jpg';
                } else if ($ID === 'NF') {
                    $path='norfolk/map-of-'.$dashedURL2Name.'-max.jpg';
                } else if ($ID === 'RE') {
                    $path='reunion/map-of-reunion-max.jpg';
                } else {
                    $path=$dashedURL2Name.'/map-of-'.$dashedURL2Name.'-max.jpg';
                }
                echo '<img id="mapImg" width="294px" src="https://ontheworldmap.com/'.$path.'" usemap="#Map" alt="'.strtr($translations["MAP_OF"], array('[name]'=>$name)).'">';
            }
        }
    }
    ?>
    <map name="Map"></map>

    <?php //List of divisions
    $SQL = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        //Output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['children']) {
                $Children = json_decode($row['children'], true);
                foreach ($Children as $key => $val) {
                    if ($val) {
                        echo '<h2>'.$key.'</h2>';
                        foreach ($val as $value) {
                            $SQL2 = "SELECT * FROM `divisions2` WHERE `ID`='".$value."'";
                            $result2 = $dataConn->query($SQL2);
                            if ($result2->num_rows > 0) {
                                while ($row2 = $result2->fetch_assoc()) {
                                    $divisionName = json_decode($row2['name'], true)[$lang];
                                    if (str_contains($row2['type'], 'Capital')) {$divisionName = $divisionName.' (Capital)';}
                                }
                            }
                            echo '<a href="division2.php?id='.strtolower($value).'">'.$divisionName.'</a><br>';
                        }
                        echo '<br>';
                    }
                }
            }
        }
    }
    ?>
</div>

<div id="rightdiv">
    <?php //Gets the seal and type of division
    $SQL = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row['hasSeal']) {
                $src='/images/seals/'.$ID.'.svg.png';
                echo '<img id="seal" height="150px" src='.$src.' alt="'.strtr($translations["SEAL_OF"], array('[name]'=>$name)).'" style="align: center;">';
            }
            
            //Displays the type of division
            $SQLPar = "SELECT * FROM `countries` WHERE `ID`='".$row['parent']."'";
            $resultPar = $dataConn->query($SQLPar);
            if ($resultPar->num_rows > 0) {
                // output data of each row
                while($rowPar = $resultPar->fetch_assoc()) {
                    //Sets type 
                    $type = json_decode($row['type'], true)[$lang] ?? $type = json_decode($row['type'], true)['en'];
                    //Sets adjective
                    $adjective = json_decode($rowPar['adjective'], true)[$lang] ?? json_decode($rowPar['adjective'], true)['en'];
                    if (str_contains($adjective, '<')) {//Checks which should be first
                        $innertext = $type.' '.explode('<', $adjective)[1];
                    } else {$innertext = $adjective.' '.$type;}
                    echo '<p id="country-type">'.$innertext.'</p>';
                }
            }
        }
    }
    ?>

    <?php
    $SQL = "SELECT * FROM `organizations` WHERE JSON_EXTRACT(`children`, '$.Members') LIKE '%\"".$ID."\"%'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        echo '<h2 id="organizationsHeading">Organizations</h2>';
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $members = json_decode($row['children'], true);
            $memberType = '';
            foreach ($members as $key => $val) {
                foreach ($val as $value) {
                    if ($key !== 'Members' && $value === $ID) {
                        if ($key === 'Suspended') {$memberType = ' (Suspended)';}
                    }
                }
            }
            echo '<a class="orgLink" href="/organization.php?id='.strtolower($row['ID']).'">'.json_decode($row['name'], true)[$lang].$memberType.'</a><br>';
        }
    }
    ?>
</div>

<div id="topdiv">
    <!--Lawbrary logo-->
    <a id="logo" href="/"><img src="/images/favicon64.png" width="40px"></img></a>
    <div id="language-div">
        <?php //Language flag
            echo '<img id="language-flag" height="32px" src="images/langs/'.$lang.'.png">'
        ?>
        <select id="language-selector" onchange="langChange(document.getElementById('language-selector').value)">
            <?php //Language selector
                foreach ($languages["Display"] as $language) {
                    //Gets the language data
                    $SQL = "SELECT * FROM `languages` WHERE `ID`='".$language."'";
                    $result = $dataConn->query($SQL)->fetch_assoc();

                    //Outputs the selector
                    $selected = $language === $lang ? ' selected':'';
                    echo '<option value="'.$language.'"'.$selected.'>'.json_decode($result['name'], true)[$language].'</option>';
                }
            ?>
        </select>
        <?php //Creates onchange parameter
        echo '<script>
            function langChange (newLang) {
                window.location.replace("//"+newLang+".'.$basedomain.$path.'")
            };
        </script>';
        ?>
    </div>

    <div id='title'>
        <?php /*Country Name*/ echo '<h1 id="country-name" style="margin: 0px;">'.$name.'</h1>';?>

        <?php //Creates link to source website
            $SQL = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
            $homesite = $dataConn->query($SQL)->fetch_assoc()['source'];
            if ($homesite) {
                //Gets the link base on language
                $homesite = json_decode($homesite, true)[$lang] ?? json_decode($homesite, true)['en'] ?? array_values(json_decode($homesite, true))[0];
                foreach ($homesite as $siteNum => $siteRef) {
                    echo '<a id="source-website'.$siteNum.'" class="title" href="'.$siteRef.'" target="blank">'.explode('/', $homesite)[2].'</a><br/>';
                }
            }
        ?>
    </div>
    
    <?php //Searchbar
    require('searchbar.php');
    ?>
</div>