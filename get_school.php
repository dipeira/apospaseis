<?php
header('Content-type: text/html; charset=iso8859-7');
require_once "config.php";
$conn = mysql_connect($db_host, $db_user, $db_password) or die ('Error connecting to mysql');
mysql_select_db($db_name);
mysql_query("SET NAMES 'greek'", $conn);
mysql_query("SET CHARACTER SET 'greek'", $conn);

$q = strtolower($_GET["q"]);
$dim = $_GET['dim'];
$omada = $_GET['omada'];
//$q = mb_strtolower($_GET["q"],'utf-8');
if (!$q) return;

//if ($dim == 2)
//    $sql = "select DISTINCT name from met_school where name LIKE '%$q%'";
//else

//$sql = "select DISTINCT name from apo_school where name LIKE '%$q%' AND dim='$dim'";
// new query with omada
$sql = "select DISTINCT name from apo_school where name LIKE '%$q%' AND dim='$dim' AND omada <> $omada";
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");
//echo $sql;
$rsd = mysql_query($sql,$conn);
while($rs = mysql_fetch_array($rsd)) {
	$cname = $rs['name'];
	echo "$cname\n";
}
?>