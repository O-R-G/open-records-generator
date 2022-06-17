<?
// path to config file
$config_dir = __DIR__."/../config/";
require_once($config_dir."config.php");

// specific to this 'app'
require_once($config_dir."url.php");
require_once($config_dir."request.php");
require_once($config_dir."org-settings.php");

// logged in user via .htaccess, .htpasswd
// $user = $_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER'];
$user = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : $_SERVER['REDIRECT_REMOTE_USER'];
$db = db_connect($user);

// this function determines which part of the url contains
// object-specific information and which part is just
// incidental to the location of the o-r-g on the server
// it compares the request uri, eg:
// /PATH/open-records-generator/browse/parent/child
// to the script (which should be index, at
// /PATH/open-records-generator/index.php
// and thus parses out the important bits, in this case:
// the $view, 'browse'
// and the object path: ['parent', 'child']
function url_array()
{
	global $view;
	$s = explode('/', rtrim($_SERVER['SCRIPT_NAME'], '/'));
	$u = explode('/', rtrim( strtok($_SERVER['REQUEST_URI'],"?"), '/'));

	while( isset($s[0]) && isset($u[0]) && $s[0] == $u[0] ) {
		array_shift($s);
		array_shift($u);
	}

	$view = array_shift($u);

	// check IE
	preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
	if(count($matches)<2){
	  preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
	}
	if (count($matches)>1){
		// Yup is IE
		if ($view == "edit")
			$view = "edit_no_wysiwyg";
	}

	// if no view is selected, show the cover page
	$view = $view ? $view : "cover";

	return $u;
}

$urls = url_array();

$oo = new Objects();
$mm = new Media();
$ww = new Wires();
$uu = new URL($urls);
$rr = new Request();

$js_back = "javascript:history.back();";

// self
$item = $oo->get($uu->id);

// am i using the ternary operator correctly?
// if this url has an id, get the associated object,
// else, get the root object
$name = $item ? strip_tags($item["name1"]) : false;

// document title
$title = $db_name." | ".$name;

// $nav = $oo->nav_clean($uu->ids);

// used in add.php, edit.php, browse.php
$ancestors = $oo->ancestors($uu->id);

// settings
$settings_file = $config_dir."/settings.store";
$max_uploads = 5;
$default_editor_mode = 'regular';

if(file_exists($settings_file))
{
	$f = file_get_contents($settings_file);
	$settings = unserialize($f);
	if(isset($settings->num_uploads))
		$max_uploads = $settings->num_uploads;
	if(isset($settings->default_editor_mode))
		$default_editor_mode = $settings->default_editor_mode;
}

if ($view == "logout")
	header("HTTP/1.1 401 Unauthorized");

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta charset="utf-8">
		<meta name="description" content="anglophile">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="<? echo $admin_path;?>media/icon.png">
		<link rel="apple-touch-icon-precomposed" href="<? echo $admin_path;?>media/icon.png">
		<link rel="stylesheet" href="<? echo $admin_path; ?>static/main.css">
	</head>
	<body>
		<div id="page">
			<div id="header-container">
				<header class="centre">
					<div id="nav">
						<a href="<?php echo $admin_path; ?>browse"><?php echo $db_name; ?> Database</a>
					</div>
				</header>
			</div>
