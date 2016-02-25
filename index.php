<?php
require_once("views/head.php");

// UGH THIS IS HIDEOUS
// $view is declared in head.php, in url_array()
// (should definitely be somewhere else)
// else is for backwards compatibility (maybe not necessary?)
if($view)
	$view_path = "views/".$view.".php";
else
{
	$uri = explode('/', $_SERVER['REQUEST_URI']);
	$view_path = "views/";
	$view_path.= $uri[2] ? $uri[2]: "cover";
	$view_path.= ".php";
}

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