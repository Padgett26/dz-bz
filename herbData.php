<?php
include "include/header.php";

if (filter_input ( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT )) {
	$getId = filter_input ( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
} else {
	$stmt = $db->prepare ( "SELECT id FROM herbData ORDER BY RAND() LIMIT 1" );
	$stmt->execute ();
	$row = $stmt->fetch ();
	$getId = $row ['id'];
}

$word = (filter_input ( INPUT_GET, 'word', FILTER_SANITIZE_STRING )) ? filter_input ( INPUT_GET, 'word', FILTER_SANITIZE_STRING ) : "";

$stmt = $db->prepare ( "SELECT * FROM herbData WHERE id=?" );
$stmt->execute ( array (
		$getId
) );
$row = $stmt->fetch ();
if ($row) {
	$name = $row ['name'];
	$dataSheet = nl2br ( $row ['dataSheet'] );
	$pic1 = $row ['pic1'];
	$pic2 = $row ['pic2'];
	$pic3 = $row ['pic3'];
	$pic1title = $row ['pic1title'];
	$pic2title = $row ['pic2title'];
	$pic3title = $row ['pic3title'];
} else {
	$name = "";
	$dataSheet = "";
	$pic1 = "x.png";
	$pic2 = "x.png";
	$pic3 = "x.png";
	$pic1title = "";
	$pic2title = "";
	$pic3title = "";
}

$letterLink = array ();
$stmt = $db->prepare ( "SELECT id, indexItem FROM symptomIndex ORDER BY indexItem" );
$stmt->execute ();
while ( $row = $stmt->fetch () ) {
	$symId = $row ['id'];
	$letterLink [$symId] = $row ['indexItem'];
}

foreach ( $letterLink as $s => $l ) {
	$dataSheet = str_ireplace ( $l, "<a href='symptoms.php#$s' style='text-decoration:underline; color:#008800;'>$l</a>", $dataSheet );
}

if ($word != "") {
	$dataSheet = str_ireplace ( $word, "<span style='background-color:yellow'>$word</span>", $dataSheet );
}

echo "<div style='text-align:center; font-size:3em;'><i>$name</i></div>";
?>
            <div style="border:0px; padding:0px; margin:20px; float:right; text-align:center; font-weight:bold;">
                <?php
																if ($pic1 && file_exists ( "image/herbPics/$pic1" )) {
																	echo "<img src='image/herbPics/$pic1' alt='$pic1title' style='max-width:300px;' /><br />$pic1title";
																}
																if (($pic1 && $pic2) || ($pic1 && $pic3 && ! $pic2)) {
																	echo "<br /><br />";
																}
																if ($pic2 && file_exists ( "image/herbPics/$pic2" )) {
																	echo "<img src='image/herbPics/$pic2' alt='$pic2title' style='max-width:300px;' /><br />$pic2title";
																}
																if ($pic2 && $pic3) {
																	echo "<br /><br />";
																}
																if ($pic3 && file_exists ( "image/herbPics/$pic3" )) {
																	echo "<img src='image/herbPics/$pic3' alt='$pic3title' style='max-width:300px;' /><br />$pic3title";
																}
																?>
            </div>
            <?php
												echo "<div style='text-align:left; padding:20px;'>" . $dataSheet . "</div>";
												echo "</div>";
												include "include/footer.php";
												?>