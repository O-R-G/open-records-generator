<?php
date_default_timezone_set('America/New_York');

// database settings
$db_name = "ica";

// $host = "http://o-r-g.com/";
$host = "http://".$_SERVER["HTTP_HOST"]."/";
$root = $_SERVER["DOCUMENT_ROOT"]."/";

$admin_path = $host . "open-records-generator/";
$admin_root = $root . "open-records-generator/";

// AWS Bucket Environental Variables
$bucket_id = getenv("BUCKETEER_BUCKET_NAME");
$bucket_region = getenv("BUCKETEER_AWS_REGION");
$bucket_key = getenv("BUCKETEER_AWS_ACCESS_KEY_ID");
$bucket_secret = getenv("BUCKETEER_AWS_SECRET_ACCESS_KEY");

if ($bucket_id) {
	$media_path = "https://" . $bucket_id . ".s3.amazonaws.com/public/";
	$media_root = $media_path; // is this ok?
} else {
	// Regular Storage Environmental Variable
	$media_path = $host . "media/"; // don't forget to set permissions on this folder
	$media_root = $root . "media/";
}

$models_root = $admin_root . "models/";

$lib_root = $admin_root . "lib/";

require_once($models_root."model.php");
require_once($models_root."objects.php");
require_once($models_root."wires.php");
require_once($models_root."media.php");
require_once($lib_root."lib.php");
require_once($lib_root."url-base.php");

$max_uploads = 5;
$m_pad = 5;
$resize = false;
$resize_scale = 65;
$resize_root = $media_root . "hi/";

// namespace stuff, for markdown parser
set_include_path($lib_root);
spl_autoload_register(function ($class) {
	$file = preg_replace('#\\\|_(?!.+\\\)#','/', $class) . '.php';
	if (stream_resolve_include_path($file))
		require $file;
});

// connect to database (called in head.php)
function db_connect($remote_user)
{
	$users = array();
	$creds = array();

	// Admin MySQL URL string
	$adminURLString = getenv("CLEARDB_DATABASE_URL");
	// Read Only MySQL URL String
	$readOnlyURLString = getenv("CLEARDB_DATABASE_URL");

	if ($adminURLString) {
		// IF YOU ARE USING ENVIRONMENTAL VARIABLES (you should)
		$urlAdmin = parse_url($adminURLString);
		$host = $urlAdmin["host"];
		$dbse = substr($urlAdmin["path"], 1);

		$creds['rw']['db_user'] = $urlAdmin["user"];
		$creds['rw']['db_pass'] = $urlAdmin["pass"];

		$urlReadOnly = parse_url($readOnlyURLString);
		$creds['r']['db_user'] = $urlReadOnly["user"];
		$creds['r']['db_pass'] = $urlReadOnly["pass"];

	} else {
			// IF YOU ARE NOT USING ENVIRONMENTAL VARIABLES
			$host = "db153.pair.com";
			$dbse = "reinfurt_onrungo";
			// full access
			$creds['full']['db_user'] = "reinfurt_42";
			$creds['full']['db_pass'] = "vNDEC89e";

			// read / write access
			// (can't create / drop tables)
			$creds['rw']['db_user'] = "reinfurt_42_w";
			$creds['rw']['db_pass'] = "Rh5JrwEP";

			// read-only access
			$creds['r']['db_user'] = "reinfurt_42_r";
			$creds['r']['db_pass'] = "8hPxYMS9";
	}

	// users
	$users["main"] = $creds['rw'];
	$users["guest"] = $creds['r'];

	$user = $users[$remote_user]['db_user'];
	$pass = $users[$remote_user]['db_pass'];

	$db = new mysqli($host, $user, $pass, $dbse);
	if($db->connect_errno)
		echo "Failed to connect to MySQL: " . $db->connect_error;
	return $db;
}
?>
