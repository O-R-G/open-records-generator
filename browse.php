<?php require_once("GLOBAL/head.php"); 









  ////////////////////////
 //  Attached Objects  //
////////////////////////

//  Get attached objects (if any)
$sql = "SELECT *, objects.id AS objectsId FROM objects, wires WHERE wires.fromid = '". $objects[$o] ."' AND wires.toid = objects.id AND wires.active = '1' AND objects.active = '1' ORDER BY weight DESC, objects.rank, begin, end DESC, begin DESC, name1, name2, objects.modified DESC, objects.created DESC";
$result = MYSQL_QUERY($sql);
$numrows = MYSQL_NUM_ROWS($result);
$padout = floor(log10($numrows)) + 1;
if ($padout < 2) $padout = 2;
$i = 1;
$myrow["id"] = $myrow["objectsId"];
while ($myrow = MYSQL_FETCH_ARRAY($result)) {

	$name = $myrow["name1"];
	if ($myrow["name2"]) $name .= " ". $myrow["name2"];
	if (strlen($name) > 60) $name = substr($name, 0, 60) ."...";
	echo STR_PAD($i++, $padout, "0", STR_PAD_LEFT) ." ". "\n<a href='". $dbAdmin ."browse.php". urlData();
	if (sizeof($objects)) echo ","; 
	echo $myrow["objectsId"] ."'>";
	echo strip_tags($name) ."</a><br />";
}
echo "\n<br /><br />\n<a href='add.php". urlData() ."'>ADD OBJECT...</a>&nbsp;<a href='link.php". urlData() ."'>LINK ...</a><br />";












require_once("GLOBAL/foot.php"); ?>
