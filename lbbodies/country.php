<div id="leftdiv">
    <?php
    $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        // output countries data
        while ($row = $result->fetch_assoc()) {
            if ($row['hasMap']) {
                //Pathname needs the English version
                $dashedName = strtr(strtolower(json_decode($row['name'], true)['en']), ' ', '-');
                if ($row['type'] === 'US State') {
                    if ($ID === 'US-DE' || $ID === 'US-NH' || $ID === 'US-RI' || $ID === 'US-VT') {
                        $src='usa/state/'.$dashedName.'/map-of-'.$dashedName.'.jpg';
                    } else {
                        $src='usa/state/'.$dashedName.'/map-of-'.$dashedName.'-max.jpg';
                    }
                } elseif ($row['type'] === 'US Capital') {
                    $src='usa/city/'.$dashedName.'/map-of-'.$dashedName.'-max.jpg';
                } elseif ($row['type'] === 'British Kingdom') {
                    $src='uk/'.$dashedName.'/administrative-divisions-map-of-'.$dashedName.'-max.jpg';
                } elseif ($ID === 'AE') {
                    $src='uae/map-of-uae.jpg';
                } elseif ($ID === 'AL' || $ID === 'AR' || $ID === 'BZ' || $ID === 'HR' || $ID === 'IE' || $ID === 'IL' || $ID === 'FI' || $ID === 'HI' || $ID === 'JP' || $ID === 'LI' || $ID === 'LU' || $ID === 'ME' || $ID === 'MZ' || $ID === 'NZ' || $ID === 'PT' || $ID === 'TH' || $ID === 'TN' || $ID === 'KR' || $ID === 'VN') {
                    $src=$dashedName.'/map-of-'.$dashedName.'.jpg';
                } else if ($ID === 'AQ') {
                    $src='https://ontheworldmap.com/'.$dashedURL2Name.'/'.$dashedURL2Name.'-map-with-country-claims-max.jpg';
                } elseif ($ID === 'AU') {
                    $src=$dashedName.'/'.$dashedName.'-map-2-max.jpg';
                } elseif ($ID === 'AT') {
                    $src=$dashedName.'/'.$dashedName.'-map-max.jpg';
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
                    $src='philippines/map-of-philippines.jpg';
                } elseif ($ID === 'SE') {
                    $src=$dashedName.'/political-map-of-'.$dashedName.'.jpg';
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
                    $src=$dashedName.'/map-of-'.$dashedName.'-max.jpg';
                }
                echo '<img id="mapImg" src="https://ontheworldmap.com/'.$src.'" usemap="#Map" alt="'.str_replace('[name]', $name, $translations["MAP_OF"]).'">';
            }
        }
    }
    ?>
    <map name="Map"></map>

    <?php //Territorial Dispute Disclaimer
    $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

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
                            //Gets names based on ISO code
                            $SQL2 = "SELECT * FROM `countries` WHERE `ID`='".$val2."'";
                            $result2 = $dataConn->query($SQL2);

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
    $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($SQL);

    if ($result->num_rows > 0) {
        //Output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($row['children']) {
                $children = json_decode($row['children'], true);
                foreach ($children as $childType => $val) {
                    if ($val) {
                        echo '<h2>'.$childType.'</h2>';
                        foreach ($val as $value) {
                            $SQL2 = "SELECT * FROM `divisions` WHERE `ID`='".$value."'";
                            $result2 = $dataConn->query($SQL2);
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
    <?php //Gets the emblem of the country
        $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
        $result = $dataConn->query($SQL);

        if ($result->fetch_assoc()['hasSeal']) {
            $src='/images/seals/'.$ID.'.svg.png';
            echo '<img id="seal" height="150px" src='.$src.' alt="'.strtr($translations["SEAL_OF"], array('[name]'=>$name)).'">';
        }
    ?>

    <?php //Outputs the country type
        $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
        $result = $dataConn->query($SQL);

        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                if ($row['type']) {
                    echo '<p id="country-type">'.$row['type'].'</p>';
                }
            }
        }
    ?>

    <?php //Outputs the country's languages
        echo '<h2>Languages</h2>';
        foreach($languages as $languageType => $languageIDs) {
            if ($languageType !== "Display") {
                echo '<h3>'.$languageType.' languages</h3><ul>';
                foreach($languageIDs as $language) {
                    $SQL = "SELECT * FROM `languages` WHERE `ID`='".$language."'";
                    $result = $dataConn->query($SQL);
                    $result = $result->fetch_assoc();
                    echo '<li>'.json_decode($result["name"], true)[$lang].'</li><br>';
                }
                echo '</ul>';
            }
        }
    ?>

    <?php //Loads organizations
    $SQL = "SELECT * FROM `organizations` WHERE JSON_EXTRACT(`children`, '$.Members') LIKE '%\"".$ID."\"%'";
    $result = $dataConn->query($SQL);

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
            //Gets the name of the treaty/organization
            if (isset(json_decode($row['name'], true)[$lang])) {
                $treatyName = json_decode($row['name'], true)[$lang];
            } else {$treatyName = json_decode($row['name'], true)['en'];}
            echo '<a class="orgLink" href="/organization.php?id='.strtolower($row['ID']).'">'.$treatyName.$memberType.'</a><br>';
        }
    }
    ?>
</div>

<div id="topdiv">
    <!--Lawbrary logo-->
    <a id="logo" href="/"><img src="/images/favicon64.png" width="40px"></img></a>
    <div id="language-div">
        <?php //Language flag
            //Gets the language data
            $SQL = "SELECT * FROM `languages` WHERE `ID`='".$lang."'";
            $result = $dataConn->query($SQL)->fetch_assoc();

            //Displays the language flag
            if ($result['hasFlag']) {
                echo '<img id="language-flag" height="32px" src="/images/langs/'.$lang.'.png">';
            }
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

    <div id="title-div">
        <div id="title-text">
            <h1 id="title">
                <?php //Creates the country's flag
                    $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
                    $result = $dataConn->query($SQL);

                    if ($result->fetch_assoc()['hasFlag']) {//Outputs the flag
                        if ($ID === 'DANISH-REALM') {
                            $flagSrc='https://flagpedia.net/data/flags/w580/dk.webp';
                        } else if ($ID === 'KOSOVO') {
                            $flagSrc='https://flagpedia.net/data/flags/w580/xk.webp';
                        } else if ($ID === 'PS-WEST-BANK') {
                            $flagSrc='https://flagpedia.net/data/flags/w580/ps.webp';
                        } else {$flagSrc='https://flagpedia.net/data/flags/w580/'.strtolower($ID).'.webp';}
                    }
                    echo '<img height=21.5px id="title-flag" src='.$flagSrc.'/>';
                ?>
                <?php /*Country Name*/ echo $name ?>
            </h1><br/><br/><br/>

            <?php //Creates link to source website
                $SQL = "SELECT * FROM `countries` WHERE `ID`='".$ID."'";
                $result = $dataConn->query($SQL);
                
                $homesites = $result->fetch_assoc()['source'];
                if ($homesites) {
                    //Gets the link base on language
                    foreach (json_decode($homesites, true) as $siteNum => $homesite) {
                        $homesite = $homesite[$lang] ?? $homesite['en'] ?? array_values($homesite)[0];
                        echo '<a height=20px id="source-website-'.$siteNum.'" href="'.$homesite.'" target="_blank">'.explode('/', $homesite)[2].'</a>';
                    }
                    echo '<br/>';
                }
            ?>
        </div>
    </div>
    
    <!--Navbar-->
    <div id="navbar">
        <ul id="navlist" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <li class="navbutton"><a class="navlink" href=<?php echo '/country.php?id='.$ID.'>'.$translations["OVERVIEW"];?></a></li>
            <li class="navbutton"><a class="navlink" href=<?php echo '/country/constitution.php?id='.$ID.'>'.$translations["CONSTITUTION"];?></a></li>
            <li class="navbutton"><a class="navlink" href=<?php echo '/country/case-laws.php?id='.$ID.'>'.$translations["CASE_LAWS"];?></a></li>
            <li class="navbutton"><a class="navlink" href=<?php echo '/country/laws.php?id='.$ID.'>'.$translations["LAWS"];?></a></li>
        </ul>
    </div>
    
    <!--Gets the searchbar-->
    <?php include __DIR__.'/../searchbar.php';?>
</div>