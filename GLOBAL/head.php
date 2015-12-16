<?php
date_default_timezone_set('Europe/Berlin'); 

require_once("config.php");
require_once("settings.inc");

$pageName = basename($_SERVER['PHP_SELF'], ".php");
$object = $_REQUEST['object'];
$id = $_REQUEST['id'];
$action = $_REQUEST['action'];
$name1 = $_REQUEST['name1'];
$deck = $_REQUEST['deck'];
$body = $_REQUEST['body'];
$notes = $_REQUEST['notes'];
$url = $_REQUEST['url'];
$begin = $_REQUEST['begin'];
$end = $_REQUEST['end'];
$rank = $_REQUEST['rank'];
$uploadsMax = $_REQUEST['uploadsMax'];
$submit = $_REQUEST['submit'];
$wirestoid = $_REQUEST['wirestoid'];
$mediaCaption = $_REQUEST['mediaCaption'];	
$mediaId = $_REQUEST['mediaId'];	
$mediaRank = $_REQUEST['mediaRank'];
$mediaDelete = $_REQUEST['mediaDelete'];
$mediaType = $_REQUEST['mediaType'];

// Debug           
// print_r($_REQUEST);




    ////////////////
   //            //
  //   Log In   //
 //            //
////////////////

// by .htaccess and .htpasswd
$dbUserSelected = 2;

// settings
$settings_file = getcwd()."/GLOBAL/settings.store";
if(file_exists($settings_file))
{
	$f = file_get_contents($settings_file);
	$settings = unserialize($f);
	$uploadsMax = $settings->num_uploads;
}
else
	$uploadsMax = 5;

  ///////////////////////////////////////////////
 //  Connect to database as authenticated user//
///////////////////////////////////////////////
  
dbConnectMain($dbUserSelected);







  ///////////////////
 //  Format Date  //
///////////////////


function utc2est($date, $time) {

	//  Determine UTC
	// $utc = date(mktime(substr($time, 0, 2), substr($time, 3, 2), substr($time, 6, 2), substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4)));
	//echo date("d F Y h:ia", $utc) ."<br/>";
	//  Determine Daylight Savings
	// $dst = date("I", $utc) * 3600;
	//echo $dst ."<br/>";
	//  Subtract 5 or 6 hours to get EST
	// $est = date($utc - (5 * 3600) - $dst);
	//echo date("d F Y h:ia", $est) ."<br/>";
	return $est;
}
function dateFormat($date, $time) {

	// $est = date("D d M Y h:ia", utc2est($date, $time));
	// return $est;
}












  ///////////////////
 //  URL objects  //
///////////////////

// Handle multiple objects if any

$objects = explode(",", $object);

// $object refers to most recent of the $objects

$object = $objects[sizeof($objects) - 1];
if (!$object) $object = 0;

// Check that selected object exists

if ($object && is_numeric($object)) {
	$sql = "SELECT id, active, name1, name2 FROM objects WHERE id = '$object' and active = '1' LIMIT 1";
	$result = MYSQL_QUERY($sql);
	$myrow = MYSQL_FETCH_ARRAY($result);
	if (!$myrow["id"]) {
		$urlTemp = "?object=";
		for ($i = 0; $i < sizeof($objects)-1; $i++) {
			$urlTemp .= $objects[$i];
			if ($i < sizeof($objects)-2) $urlTemp .= ",";
		}
		header("location:". $dbAdmin ."browse.php". $urlTemp);
	}
	$name = $myrow["name1"];
	if ($myrow["name2"]) $name .= " ". $myrow["name2"];
	$documentTitle = $name;
}

// Clean up $objects because explode() gives it an array size even if null!

if (sizeof($objects) == 1 && empty($objects[sizeof($objects) - 1])) unset($objects);














  /////////////////
 //  Build URL  //
/////////////////

function urlData() {

	global $objects;
	$url = "?object=";
	for ($i = 0; $i < sizeof($objects); $i++) {
		$url .= $objects[$i];
		if ($i < sizeof($objects) - 1) $url .= ",";
	}
	return $url;
}
function urlBack() {

	global $objects;
	$url = "?object=";
	for ($i = 0; $i < sizeof($objects) - 1; $i++) {
		$url .= $objects[$i];
		if ($i < sizeof($objects) - 2) $url .= ",";
	}
	return $url;
}













  ///////////////////////
 //  Document HEADER  //
///////////////////////

if ($documentTitle) {
	$documentTitle = $dbClient ." | ". $documentTitle;
} else {
	$documentTitle = $dbClient;
}
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>

<!DOCTYPE html
	PUBLIC "-//W3C//Dtd XHTML 1.0 Transitional//EN"
	"http://www.w3.org/tr/xhtml1/Dtd/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

	<head>
		<title><?php echo $documentTitle; ?></title>

		<meta http-equiv="Content-Type" content="text/xhtml; charset=utf-8" />
		<meta http-equiv="Title" content="<?php echo $documentTitle; ?>" />

		<meta name="distribution" content="all" />
		<meta name="rating" content="general" />
		<meta name="resource-type" content="document" />
		<meta name="revisit-after" content="2 days" />
		<meta name="classification" content="business" />
		<meta name="author" content="stewart A.T stewdio DO.T org" />
		<meta name="description" content="Open Records Generator 2.0: a light-weight, open-source content management solution." /> 
		<meta name="keywords" lang="en-us" 
			content="Content management system for <?php echo $dbClient; ?> by O R G" />

		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta http-equiv="imagetoolbar" content="no" />
		<link rel="shortcut icon" href="../../MEDIA/km.png">

		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $dbAdmin; ?>GLOBAL/global.css" />
		<!-- link rel="alternate" type="application/rss+xml" title="RSS" href="/rss" / -->
		<!-- link rel="icon" type="image/x-icon" href="MEDIA/favicon.ico" / --> 
		<!-- link rel="shortcut icon" href="MEDIA/favicon.ico" type="image/ico" / -->

		<style type="text/css">

			.dbClientStyle {
				color: #FFFFFF;
				background-color: #<?php echo $dbColor;?>; }

			.dbClientStyle a:link {
				color: #FFFFFF;
				background-color: #<?php echo $dbColor2;?>; }

			.dbClientStyle a:visited {
				color: #FFFFFF;
				background-color: #<?php echo $dbColor2;?>; }

			.dbClientStyle a:active {
				color: #FFFFFF;
				background-color: #<?php echo $dbColor2;?>; }

			.dbClientStyle a:hover {
				color: #FFFFFF;
				background-color: #<?php echo $dbColor3;?>; }

		</style>
	</head>
	<body>
		<table class="main" cellpadding="0" cellspacing="0" border="0">
			<!--  DATABASE NAME  -->
			<tr class="head">
				<td style="padding: 16px 16px 16px 16px; color: #FFFFFF;"><?php
				// if ($pageName != "index" && $object) 
				echo "<a href='". $dbAdmin ."browse.php" ."'>";
				echo strToUpper($dbClient) ." Database";
				// if ($pageName != "index" && $object) 
				echo "</a>";



if ($pageName != "index") {

	echo "</td></tr>";








	  //////////////////////////
	 //  Referenced Objects  //
	//////////////////////////

	for ($o = 0; $o < sizeof($objects) - 1; $o++) {

		$sql = "SELECT id, active, name1, name2 FROM objects WHERE id = '". $objects[$o] ."' AND active = 1 LIMIT 1";
		$result = MYSQL_QUERY($sql);
		$myrow = MYSQL_FETCH_ARRAY($result);
		$objectName = $myrow["name1"];
		if ($myrow["name2"]) $objectName .= " ". $myrow["name2"];
		$objectName = strip_tags($objectName);

		echo "\n\n\n\n<!--  ". $objectName ."  -->\n\n";
		// Alternate background color
		echo "<tr class='button' style='background-color: #";
		echo ($o % 2) ? "ECECEC" : "E3E3E3";
		// Each panel expands on title click
		echo ";'>";
		echo "\n<td style='padding: 16px 16px 16px 16px;'>";
		echo "<a href='". $dbAdmin ."browse.php?object=";
		for ($i = 0; $i < $o + 1; $i++) {
			echo $objects[$i];
			if ($i < $o) echo ",";
		}
		echo "'>". $objectName ."</a><br />";
		echo "\n</td></tr>";
	}








	  ///////////////////////
	 //  Selected Object  //
	///////////////////////

	$sql = "SELECT id, name1, name2 FROM objects WHERE id = '". $objects[$o] ."' AND active = 1 LIMIT 1";
	$result = MYSQL_QUERY($sql);
	$myrow = MYSQL_FETCH_ARRAY($result);
	$objectName = $myrow["name1"];
	if ($myrow["name2"]) $objectName .= " ". $myrow["name2"];
	$objectName = strip_tags($objectName);

	echo "\n\n\n<!--  ". $objectName ."  -->\n\n";
	echo "<tr class='button' style='background-color: #";
	echo ($o % 2) ? "ECECEC" : "E3E3E3";
	echo ";'>";
	echo "\n<td style='padding: 16px 16px 16px 16px;'>";
	if ($object) {
		echo "\n<span class='monoHvy'>";
		if ($pageName != "browse") echo "<a href='browse.php". urlData() ."'>";
		echo $objectName;
		if ($pageName != "browse") echo "</a>";
		echo "</span> &nbsp; ";
		if ($pageName == "browse") { 
			echo "\n<a href='edit.php". urlData() ."'>EDIT...</a> ";
			echo "\n<a href='delete.php". urlData() ."'>DELETE...</a><br />";
		}
		if ($pageName != "edit" && $pageName != "delete") {
			echo "\n</td></tr><tr style='background-color: #";
			echo (($o+1) % 2) ? "ECECEC" : "E3E3E3";
			echo ";'>";
			echo "\n<td style='padding: 16px 16px 16px 16px;'>";
		}
	}
}


?>
