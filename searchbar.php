<div id="search-form" lang="en">
    <input id="searchbar" type="search" placeholder=<?php echo '"'.$translations["SEARCH"].'"';?>>
    <select id="type-selector">
        <option value="global"></option>
        <option value="country">Country</option>
        <option value="division">Division</option>
        <option value="organization">Organization</option>
        <option value="law">Law</option>
    </select>
    <select id="country-selector">
        <option value="WW" lang="en">🌍 Globe</option>
        <?php //Gets the list of countries with flags
            //Connects to the content database
            $username="ug0iy8zo9nryq"; $password="T_1&x+$|*N6F"; $database="dbupm726ysc0bg";
            $conn = new mysqli("localhost", $username, $password, $database);
            $conn->select_db($database) or die("Unable to select database");
            $conn->query("SET NAMES 'utf8'");

            $sql = "SELECT * FROM `countries` WHERE 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output countries data
                while($row = $result->fetch_assoc()) {
                    $names = json_decode($row["name"], true);
                    $emoji = $row["emoji"];
                    if ($names[$lang]) {
                        echo "<option value='".$row['ID']."' lang='".$lang."'>".$emoji.' '.$names[$lang]."</option>";
                    } else {echo "<option value='".$row['ID']."' lang='".$lang."'>".$emoji.' '.$names["en"]."</option>";}
                }
            }
        ?>
    </select>

    <script>
        document.getElementById('searchbar').onkeydown = function (e) {
            var q = this.value.toLowerCase();
            var type = document.getElementById('type-selector').value;
            //var country = document.getElementById('country-selector').value;
            var url = "/search.php?q=" + q + "&type=" + type /*+ "&id=" + country.toLowerCase()*/;
            if (e.keyCode == 13) {
                window.location.href = url;
            }
        };
    </script>
</div>