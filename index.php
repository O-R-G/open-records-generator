<?php
$request = $_SERVER['REQUEST_URI'];
$requestclean = strtok($request,"?");
$uri = explode('/', $requestclean);
if(count($uri) > 2 && empty(end($uri))) array_pop($uri);

require_once("views/head.php");

// UGH THIS IS HIDEOUS
// $view is declared in head.php, in url_array()
// (should definitely be somewhere else)
// else is for backwards compatibility (maybe not necessary?)
if($view)
	$view_path = "views/".$view.".php";
try {
	if(!file_exists($view_path))
		throw new Exception("404");
}
catch(Exception $e) {
	$view_path = "views/errors/".$e->getMessage().".php";
}

require_once($view_path);
require_once("views/foot.php"); 

?>