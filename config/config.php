<?php

/*
    use environment variables
    set in server block directive, read via php
    
    apache:

    SetEnv MYSQL_R_DATABASE_URL mysql2://user:pass:@host/database
    SetEnv MYSQL_RW_DATABASE_URL mysql2://user:pass:@host/database

    nginx:
  
    fastcgi_param   MYSQL_R_DATABASE_URL mysql2://user:pass:@host/database
    fastcgi_param   MYSQL_RW_DATABASE_URL mysql2://user:pass:@host/database
*/

// get environment variables
date_default_timezone_set('America/New_York');
$db_name = "open-records-generator";
$host = "//".$_SERVER["HTTP_HOST"]."/";
$root = $_SERVER["DOCUMENT_ROOT"]."/";
$admin_path = $host . "open-records-generator/";
$admin_root = $root . "open-records-generator/";
$adminURLString = getenv("MYSQL_RW_DATABASE_URL");
$readOnlyURLString = getenv("MYSQL_R_DATABASE_URL");
$media_path = $host . "media/"; // don't forget to set permissions on this folder
$media_root = $root . "media/";
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
function db_connect($remote_user) {
	global $adminURLString;
	global $readOnlyURLString;

	$users = array();
	$creds = array();

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
		$host = "localhost";
		$dbse = "database_name";

		// full access
		$creds['full']['db_user'] = "user_full";
		$creds['full']['db_pass'] = "pass_full";

		// read / write access
		// (can't create / drop tables)
		$creds['rw']['db_user'] = "user_rw";
		$creds['rw']['db_pass'] = "pass_rw";

		// read-only access
		$creds['r']['db_user'] = "user_r";
		$creds['r']['db_pass'] = "pass_r";
	}

	// users
	$users["admin"] = $creds['full'];
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
