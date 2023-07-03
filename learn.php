<?php
include "include/header.php";
?>
	<table style="width: 90%; text-align: center;">
		<tr>
			<td colspan="3"
				style="padding: 10px; font-size: 2em; font-style: bold; vertical-align: top;">
				Learn about Oils and Herbs:</td>
		</tr>
		<tr>
             <?php
            $substmt = $db->prepare(
                    "SELECT id,name FROM herbData ORDER BY name");
            $substmt->execute();
            $t = 1;
            while ($subrow = $substmt->fetch()) {
                echo "<td style='width:30%; padding:10px; vertical-align:top;'><a href='herbData.php?id=" .
                        $subrow['id'] .
                        "' style='color:#008000; font-size:1.5em; font-style:bold;'>" .
                        $subrow['name'] . "</a></td>";
                if ($t % 3 == 0) {
                    echo "</tr><tr>";
                }
                $t ++;
            }
            ?>
    	</tr>
	</table>
<?php
include "include/footer.php";
?>