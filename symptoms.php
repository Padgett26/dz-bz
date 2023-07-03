<?php
include "include/header.php";

$letterLink = array();
$wordLink = array();
$stmt = $db->prepare("SELECT indexItem FROM symptomIndex ORDER BY indexItem");
$stmt->execute();
while ($row = $stmt->fetch()) {
    $letterLink[] = strtoupper(substr($row['indexItem'], 0, 1));
    $wordLink[] = $row['indexItem'];
}
$letterList = array_unique($letterLink);
$wordList = array_unique($wordLink);

$w = (filter_input(INPUT_POST, 'getSymptom', FILTER_SANITIZE_STRING)) ? filter_input(
        INPUT_POST, 'getSymptom', FILTER_SANITIZE_STRING) : "";
?>
                <div style='text-align:center; margin:20px 0px;'><span style='font-size:3em; text-decoration:italics;'>Symptoms</span><br><br>
                    If you click on a link it will take you to the page on that herb/oil and will highlight the symptom you have clicked on.
                </div>
                <div style="text-align:center; margin:20px 0px;font-size:1.5em; color:#000000;">
                <a href="symptoms.php" style="font-size:1em; color:#000000;">Load the full list of Symptoms</a><br><br>
                <form action="symptoms.php" method="post">Jump to a specific symptom <select name="getSymptom" size="1">
                <option value=''></option>
                <?php
                foreach ($wordList as $word) {
                    echo "<option value='$word'>$word</option>\n";
                }
                ?>
                </select> <input type="submit" value=" GO "></form>
                </div>
                <?php
                if ($w == "") {
                    ?>
                <div style="">
                    <?php
                    foreach ($letterList as $let) {
                        echo "<a name='$let'><span style='text-align:left; font-size:2em; font-style:italic;'>$let</span></a><br>";
                        $stmt = $db->prepare(
                                "SELECT  * FROM symptomIndex WHERE indexItem LIKE '$let%' ORDER BY indexItem");
                        $stmt->execute();
                        while ($row = $stmt->fetch()) {
                            $symId = $row['id'];
                            $symptom = $row['indexItem'];
                            echo "<div style='font-weight:bold; padding:5px;'><a name='$symId'>$symptom</a> - ";
                            $substmt = $db->prepare(
                                    "SELECT id,name FROM herbData WHERE dataSheet LIKE '%$symptom%' ORDER BY name");
                            $substmt->execute();
                            $t = 1;
                            while ($subrow = $substmt->fetch()) {
                                if ($t != 1) {
                                    echo ", ";
                                }
                                echo "<a href='herbData.php?id=" . $subrow['id'] .
                                        "&word=$symptom' style='color:#008000;'>" .
                                        $subrow['name'] . "</a>";
                                $t ++;
                            }
                            echo "</div>";
                        }
                        echo "<br><br>";
                    }
                    ?>
                </div>
<?php
                } else {
                    echo "<div style='font-weight:bold; padding:5px;'>$w - ";
                    $substmt = $db->prepare(
                            "SELECT id,name FROM herbData WHERE dataSheet LIKE '%$w%' ORDER BY name");
                    $substmt->execute();
                    $t = 1;
                    while ($subrow = $substmt->fetch()) {
                        if ($t != 1) {
                            echo ", ";
                        }
                        echo "<a href='herbData.php?id=" . $subrow['id'] .
                                "&word=$symptom' style='color:#008000;'>" .
                                $subrow['name'] . "</a>";
                        $t ++;
                    }
                    echo "</div>";
                }
                include "include/footer.php";
                ?>