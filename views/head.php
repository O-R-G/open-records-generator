<?
// path to config file
$config_dir = __DIR__."/../config/";
require_once($config_dir."config.php");

// specific to this 'app'
require_once($config_dir."url.php");
require_once($config_dir."request.php");

// logged in user via .htaccess, .htpasswd
$user = $_SERVER['REMOTE_USER'];
$db = db_connect($user);

$oo = new Objects();
$mm = new Media();
$ww = new Wires();
$uu = new URL();
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
?><!DOCTYPE html>
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