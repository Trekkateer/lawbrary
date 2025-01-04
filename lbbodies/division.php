<div id="topdiv">
    <!--Lawbrary logo-->
    <a id="logo" href="/"><img src="/images/favicon64.png" width="40px"></img></a>
    <div id="language-div">
    <?php //Language flag
            echo '<img id="language-flag" height="32px" src="images/languageFlags/'.$lang.'.png">'
        ?>
        <select id="language-selector" onchange="langChange(document.getElementById('language-selector').value)">
            <?php
            //Language selector
            $sql = "SELECT * FROM `languages` WHERE `dispIn`->'$.divisions' LIKE '%\"".$ID."\"%' OR `dispIn`->'$.divisions' LIKE '%\"GLOBAL\"%'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $langID = json_decode($row['ID'], true)['alpha2'] ?? json_decode($row['ID'], true)['alpha3'];
                    $selected = $langID === $lang ? 'selected':'';
                    echo '<option value="'.$langID.'"'.$selected.'>'.$row['name'].'</option>';
                }
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
            $sql = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
            $homesite = $conn->query($sql)->fetch_assoc()['source'];
            if ($homesite) {
                //Gets the link base on language
                $homesite = json_decode($homesite, true)[$lang] ?? json_decode($homesite, true)['en'] ?? array_values(json_decode($homesite, true))[0];
                foreach ($homesite as $siteNum => $siteRef) {
                    echo '<a id="source-website'.$siteNum.'" class="title" href="'.$siteRef.'" target="blank">'.$translations[1].'</a><br/>';
                }
            }
        ?>
    </div>
    
    <?php //Searchbar
    require('searchbar.php');
    ?>
</div>

<div id="leftdiv">
    <?php
    $sql = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

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
                if ($row['parent'] === 'US' && json_decode($row['type'], true)['en'] === 'State') {
                    if ($params['id'] === 'US-DE' || $params['id'] === 'US-NH' || $params['id'] === 'US-RI' || $params['id'] === 'US-VT') {
                        $src='https://ontheworldmap.com/usa/state/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'.jpg';
                    } else {
                        $src='https://ontheworldmap.com/usa/state/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'-max.jpg';
                    }
                } else if ($row['parent'] === 'GB' && json_decode($row['type'], true)['en'] === 'Country') {
                    $src='https://ontheworldmap.com/uk/'.$dashedURL2Name.'/administrative-divisions-map-of-'.$dashedURL2Name.'-max.jpg';
                } else if ($params['id'] === 'AE') {
                    $src='https://ontheworldmap.com/uae/map-of-uae.jpg';
                } else if ($params['id'] === 'DK' ||  $params['id'] === 'hi') {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'.jpg';
                } else if ($params['id'] === 'AQ') {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/'.$dashedURL2Name.'-map-with-country-claims-max.jpg';
                } else if ($params['id'] === 'AU') {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/'.$dashedURL2Name.'-map-2-max.jpg';
                } else if ($params['id'] === 'AT') {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/'.$dashedURL2Name.'-map-max.jpg';
                } else if ($params['id'] === 'BL') {
                    $src='https://ontheworldmap.com/st-barts/map-of-st-barts-max.jpg';
                } else if ($params['id'] === 'CW') {
                    $src='https://ontheworldmap.com/curacao/map-of-curacao-max.jpg';
                } else if ($params['id'] === 'NF') {
                    $src='https://ontheworldmap.com/norfolk/map-of-'.$dashedURL2Name.'-max.jpg';
                } else if ($params['id'] === 'RE') {
                    $src='https://ontheworldmap.com/reunion/map-of-reunion-max.jpg';
                } else if ($params['id'] === 'TC') {
                    $src='https://ontheworldmap.com/turks-and-caicos/map-of-turks-and-caicos-max.jpg';
                } else if ($params['id'] === 'VI') {
                    $src='https://ontheworldmap.com/virgin-islands-us/map-of-us-virgin-islands-max.jpg';
                } else if ($params['id'] === 'VG') {
                    $src='https://ontheworldmap.com/virgin-islands-british/map-of-british-virgin-islands-max.jpg';
                } else {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'-max.jpg';
                }
                echo '<img id="mapImg" width="294px" src="'.$src.'" usemap="#Map" alt="'.strtr($translations[2], array('[name]'=>$name)).'">';
            }
        }
    }
    ?>
    <map name="Map"></map>

    <?php //List of divisions
    $sql = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        //Output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['children']) {
                $Children = json_decode($row['children'], true);
                foreach ($Children as $key => $val) {
                    if ($val) {
                        echo '<h2>'.$key.'</h2>';
                        foreach ($val as $value) {
                            $sql2 = "SELECT * FROM `divisions2` WHERE `ID`='".$value."'";
                            $result2 = $conn->query($sql2);
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
    <?php //Gets the flag
    $sql = "SELECT * FROM `divisions` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row['hasFlag']) {
                if ($row['parent'] === 'US' && json_decode($row['type'], true)['en'] === 'State') {//If it's a US state
                    $src='https://flagpedia.net/data/us/w580/'.strtolower(substr($params['id'], 3, 5)).'.webp';
                } else {
                    $src='https://flagpedia.net/data/flags/w580/'.strtolower($params['id']).'.webp';
                }
                echo '<img id="flag" height="150px" src='.$src.' alt="'.strtr($translations[3], array('[name]'=>$name)).'">';
            }
            
            //Displays the type of division
            $sqlPar = "SELECT * FROM `countries` WHERE `ID`='".$row['parent']."'";
            $resultPar = $conn->query($sqlPar);
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
    $sql = "SELECT * FROM `organizations` WHERE `children`->'$.Members' LIKE '%\"".$ID."\"%'";
    $result = $conn->query($sql);

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
            echo '<a class="treaty" href="/treaty.php?id='.strtolower($row['ID']).'">'.json_decode($row['name'], true)[$lang].$memberType.'</a><br>';
        }
    }
    ?>
</div>