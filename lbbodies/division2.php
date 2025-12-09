
<div id="topdiv">
    <!--Lawbrary logo-->
    <a id="logo" href="/"><img src="/images/logos/Graphic - Main Ribbon.svg" width="40px"></img></a>
    <div id="language-div">
    <?php //Language flag
            echo '<img id="language-flag" height="32px" src="images/langs/'.$lang.'.png">'
        ?>
        <select id="language-selector" onchange="langChange(document.getElementById('language-selector').value)">
            <?php
            //Language selector
            $sql = "SELECT * FROM `languages` WHERE `dispIn`->'$.divisions2' LIKE '%\"".$ID."\"%' OR `dispIn`->'$.divisions2' LIKE '%\"GLOBAL\"%'";
            $result = $dataConn->query($sql);

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

    <div id="title">
        <?php /*Country Name*/ echo '<h1 id="country-name" style="margin: 0px;">'.$name.'</h1>';?>

        <?php //Queries database and creates anchor tag
        $sql = "SELECT `homesite` FROM `divisions2` WHERE `ID`='".$ID."'";
        $homesite = $dataConn->query($sql)->fetch_assoc()['homesite'];
        if ($homesite) {echo '<a id="official-website" href="'.$homesite.'" target="blank">Official Website</a>';}
        ?>
    </div>
    
    <?php //Searchbar
    require('searchbar.php');
    ?>
</div>

<div id="leftdiv">
    <?php
    $sql = "SELECT * FROM `divisions2` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row['hasMap']) {
                //Pathname needs the English version
                $pathName = explode(' ', strtolower(json_decode($row['name'], true)['en']));
                $dashedURL2Name = '';
                for ($i=0; $i<count($pathName); $i++) {
                    $dashedURL2Name = $dashedURL2Name.$pathName[$i].'-';
                } $dashedURL2Name = substr($dashedURL2Name, 0, strlen($dashedURL2Name)-1);
                $src='https://ontheworldmap.com/'.$dashedURL2Name.'/map-of-'.$dashedURL2Name.'-max.jpg';
                echo '<img id="mapImg" width="294px" src="'.$src.'" usemap="#Map" alt="Map of '.$name.'">';
            }
        }
    }
    ?>
    <map name="Map"></map>
</div>

<div id="rightdiv">
    <?php
    $sql = "SELECT * FROM `divisions2` WHERE `ID`='".$ID."'";
    $result = $dataConn->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row['hasSeal']) {
                $src='/images/seals/'.$ID.'.svg.png';
                echo '<img id="seal" height="150px" src='.$src.' alt="'.strtr($translations["SEAL_OF"], array('[name]'=>$name)).'" style="align: center;">';
            }
            
            //Displays the type of division
            $sqlPar = "SELECT * FROM `countries` WHERE `ID`='".substr($row['parent'], 0, 2)."'";
            $resultPar = $dataConn->query($sqlPar);
            if ($resultPar->num_rows > 0) {
                // output data of each row
                while($rowPar = $resultPar->fetch_assoc()) {
                    $adjective = json_decode($rowPar['adjective'], true)['en'];
                    echo '<p id="country-type">'.$adjective.' '.$row['type'].'</p>';
                }
            }
        }
    }
    ?>

    <?php
    $sql = "SELECT * FROM `organizations` WHERE `children`->'$.Members' LIKE '%\"".$ID."\"%'";
    $result = $dataConn->query($sql);

    if ($result->num_rows > 0) {
        echo '<h2 id="organizationsHeading">organizations</h2>';
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
            echo '<a class="orgLink" href="/organization.php?id='.$row['ID'].'&doc=laws">'.json_decode($row['name'], true)[$lang].$memberType.'</a><br>';
        }
    }
    ?>
</div>