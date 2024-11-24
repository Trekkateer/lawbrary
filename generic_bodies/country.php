<div id="leftdiv">
    <?php
    $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // output countries data
        while ($row = $result->fetch_assoc()) {
            if ($row['hasMap']) {
                //Pathname needs the English version
                $pathName = explode(' ', strtolower(json_decode($row['name'], true)['en']));
                $dashedURLName = '';
                for ($i=0; $i<count($pathName); $i++) {//Replaces spaces with dashes
                    $dashedURLName = $dashedURLName.$pathName[$i].'-';
                } $dashedURLName = substr($dashedURLName, 0, strlen($dashedURLName)-1);
                if ($row['type'] === 'US State') {
                    if ($ID === 'US-DE' || $ID === 'US-NH' || $ID === 'US-RI' || $ID === 'US-VT') {
                        $src='usa/state/'.$dashedURLName.'/map-of-'.$dashedURLName.'.jpg';
                    } else {
                        $src='usa/state/'.$dashedURLName.'/map-of-'.$dashedURLName.'-max.jpg';
                    }
                } elseif ($row['type'] === 'US Capital') {
                    $src='usa/city/'.$dashedURLName.'/map-of-'.$dashedURLName.'-max.jpg';
                } elseif ($row['type'] === 'British Kingdom') {
                    $src='uk/'.$dashedURLName.'/administrative-divisions-map-of-'.$dashedURLName.'-max.jpg';
                } elseif ($ID === 'AE') {
                    $src='uae/map-of-uae.jpg';
                } elseif ($ID === 'AL' || $ID === 'AR' || $ID === 'BZ' || $ID === 'HR' || $ID === 'IE' || $ID === 'IL' || $ID === 'FI' || $ID === 'HI' || $ID === 'JP' || $ID === 'LI' || $ID === 'LU' || $ID === 'ME' || $ID === 'MZ' || $ID === 'NZ' || $ID === 'PH' || $ID === 'PT' || $ID === 'TH' || $ID === 'TN' || $ID === 'KR' || $ID === 'VN') {
                    $src=$dashedURLName.'/map-of-'.$dashedURLName.'.jpg';
                } elseif ($ID === 'AU') {
                    $src=$dashedURLName.'/'.$dashedURLName.'-map-2-max.jpg';
                } elseif ($ID === 'AT') {
                    $src=$dashedURLName.'/'.$dashedURLName.'-map-max.jpg';
                } elseif ($ID === 'BS') {
                    $src='bahamas/map-of-bahamas-max.jpg';
                } elseif ($ID === 'CD') {
                    $src='democratic-republic-of-the-congo/map-of-dr-congo-max.jpg';
                } elseif ($ID === 'CF') {
                    $src='central-african/map-of-central-african-max.jpg';
                } elseif ($ID === 'CG') {
                    $src='republic-of-the-congo/map-of-republic-of-the-congo-max.jpg';
                } elseif ($ID === 'CI') {
                    $src='cote-d-ivoire/map-of-cote-d-ivoire-max.jpg';
                } elseif ($ID === 'NORTHERN-CYPRUS') {
                    $src='cyprus/map-of-cyprus-max.jpg';
                } elseif ($ID === 'CZ') {
                    $src='czech-republic/map-of-czech-republic-max.jpg';
                } elseif ($ID === 'GB') {
                    $src='uk/united-kingdom-map-max.jpg';
                } elseif ($ID === 'GM') {
                    $src='gambia/map-of-gambia-max.jpg';
                } elseif ($ID === 'MK') {
                    $src='macedonia/map-of-macedonia-max.jpg';
                } elseif ($ID === 'MM') {
                    $src='burma/map-of-burma.jpg';
                } elseif ($ID === 'MH') {
                    $src='marshall-islands/map-of-marshall-islands-max.jpg';
                } elseif ($ID === 'NL') {
                    $src='netherlands/map-of-netherlands-max.jpg';
                } elseif ($ID === 'PS-GAZA' || $ID === 'PS-WEST-BANK') {
                    $src='palestine/map-of-palestine.jpg';
                } elseif ($ID === 'PH') {
                    $src='philippines/map-of-philippines-max.jpg';
                } elseif ($ID === 'SE') {
                    $src=$dashedURLName.'/political-map-of-'.$dashedURLName.'.jpg';
                } elseif ($ID === 'ST') {
                    $src='sao-tome-and-principe/map-of-sao-tome-and-principe-max.jpg';
                } elseif ($ID === 'SC') {
                    $src='seychelles/map-of-seychelles-1000.jpg';
                } elseif ($ID === 'SB') {
                    $src='solomon-islands/map-of-solomon-islands-max.jpg';
                } elseif ($ID === 'TC') {
                    $src='turks-and-caicos/map-of-turks-and-caicos-max.jpg';
                } elseif ($ID === 'TL') {
                    $src='timor-east/map-of-timor-east-max.jpg';
                } elseif ($ID === 'TR') {
                    $src='turkey/map-of-turkey-max.jpg';
                } elseif ($ID === 'US') {
                    $src='usa/us-map-max.jpg';
                } else {
                    $src=$dashedURLName.'/map-of-'.$dashedURLName.'-max.jpg';
                }
                echo '<img id="mapImg" src="https://ontheworldmap.com/'.$src.'" usemap="#Map" alt="'.strtr($translations[2], array('[name]'=>$name)).'">';
            }
        }
    }
    ?>
    <map name="Map"></map>

    <?php //Territorial Dispute Disclaimer
    $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        //Output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['disputes']) {
                $disputes = json_decode($row['disputes'], true);
                $echoString = '<h4 id="disputeDisclaimer">!!';
                foreach ($disputes as $key => $val) {
                    $index = array_search($key, array_keys($disputes));//gets the numeric index
                    if ($val) {
                        if ($index === 0) {$echoString = $echoString.$name.' ';}
                        $echoString = $echoString.$key;
                        foreach ($val as $key2 => $val2) {
                            //Converts ISOs to Names
                            $sql2 = "SELECT * FROM `countries` WHERE `ID`='".$val2."'";
                            $result2 = $conn->query($sql2);

                            if ($result2->num_rows > 0) {
                                //Output data of each row
                                while ($row2 = $result2->fetch_assoc()) {
                                    $disputerName = json_decode($row2['name'], true)[$lang] ?? json_decode($row2['name'], true)['en'];
                                    $countryLink = '<a href="country.php?id='.strtolower($val2).'">'.$disputerName.'</a>';
                                }
                            }

                            //Adds the country to the string
                            if ($key2 !== 0 && count($val) > 1) {
                                $echoString = $echoString.' and ';
                            }
                            $echoString = $echoString.' '.$countryLink;
                        }
                        if ($index !== count($disputes)-1) {
                            $echoString = $echoString.' and ';
                        }
                    }
                }
                $echoString = $echoString.'!!</h4>';
                echo $echoString;
            }
        }
    }
    ?>

    <?php //List of divisions
    $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        //Output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['children']) {
                $children = json_decode($row['children'], true);
                foreach ($children as $childType => $val) {
                    if ($val) {
                        echo '<h2>'.$childType.'</h2>';
                        foreach ($val as $value) {
                            $sql2 = "SELECT * FROM `divisions` WHERE `ID`='".$value."'";
                            $result2 = $conn->query($sql2);
                            if ($result2->num_rows > 0) {
                                while ($row2 = $result2->fetch_assoc()) {
                                    if (isset(json_decode($row2['name'], true)[$lang])) {
                                        $divisionName = json_decode($row2['name'], true)[$lang];
                                    } else {$divisionName = json_decode($row2['name'], true)['en'];}
                                    if (str_contains($row2['type'], 'Capital')) {$divisionName = $divisionName.' (Capital)';}
                                }
                            }
                            echo '<a href="/division.php?id='.strtolower($value).'">'.$divisionName.'</a><br>';
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
    <?php /*Country Flag*/ echo '<img id="flag" src='.$flagSrc.' alt="'.strtr($translations[3], array('[name]'=>$name)).'">';?>

    <?php //Outputs the country type
        $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                if ($row['type']) {
                    echo '<p id="country-type">'.$row['type'].'</p>';
                }
            }
        }
    ?>

    <?php //Loads organizations
    $sql = "SELECT * FROM `organizations` WHERE `children`->'$.Members' LIKE '%\"".$ID."\"%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<h2 id="organizationsHeading">Organizations</h2>';
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
            //Gets the name of the treaty
            if (isset(json_decode($row['name'], true)[$lang])) {
                $treatyName = json_decode($row['name'], true)[$lang];
            } else {$treatyName = json_decode($row['name'], true)['en'];}
            echo '<a class="treaty" href="/treaty.php?id='.strtolower($row['ID']).'">'.$treatyName.$memberType.'</a><br>';
        }
    }
    ?>
</div>

<div id="topdiv">
    <!--Lawbrary logo-->
    <a id="logo" href="/"><img src="/images/favicon64.png" width="40px"></img></a>
    <div id="language-div">
        <?php //Language flag
            echo '<img id="language-flag" height="32px" src="/images/languageFlags/'.$lang.'.png">'
        ?>
        <select id="language-selector" onchange="langChange(document.getElementById('language-selector').value)">
            <?php //Language selector
            $sql = "SELECT * FROM `languages` WHERE `dispIn`->'$.countries' LIKE '%\"".$ID."\"%' OR `dispIn`->'$.countries' LIKE '%\"GLOBAL\"%'";
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

    <div id="title-div">
        <!--?php /*Country Flag*/ echo '<img id="title-flag" src='.$flagSrc.' alt="'.strtr($translations[3], array('[name]'=>$name)).'"/>';?-->

        <div id="title-text" style="">
            <h1 id="title" class="title" style="margin: 0px;"><?php /*Country Name*/ echo $name ?></h1><br/><br/><br/>

            <?php //Creates link to source website
                $sql = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
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
    </div>
    
    <!--Gets the searchbar-->
    <?php include __DIR__.'/../searchbar.php';?>
    
    <ul id="navbar">
        <li><a class="navlink" href=<?php echo '/country.php?id='.$ID.'>'.$translations[6];?></a></li>
        <li><a class="navlink" href=<?php echo '/country/popular.php?id='.$ID.'>'.$translations[7];?></a></li>
        <li><a class="navlink" href=<?php echo '/country/constitution.php?id='.$ID.'>'.$translations[8];?></a></li>
        <li><a class="navlink" href=<?php echo '/country/laws.php?id='.$ID.'>'.$translations[9];?></a></li>
    </ul>
</div>