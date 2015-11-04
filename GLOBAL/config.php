<?php

   ////////////////////////////
  //  Kunstverein-Muenchen  //
 //   DB Settings   	   //
////////////////////////////

	// Client Name
	$dbClient = "OPEN-RECORDS-GENERATOR";

	// Client Color
	$dbColor = "000";
	$dbColor2 = "666";
	$dbColor3 = "333";

	// Client Username and Password -- read only
	$dbUser1 = "guest";
	$dbPass1 = "guest";

	// Client Username and Password -- read / write
	$dbUser2 = "main";
	$dbPass2 = "main";

	// Client Username and Password -- main
	$dbUser3 = "admin";
	$dbPass3 = "admin";

	// Database Start Date/Time
	$dbStart = mktime(13, 25, 00, 02, 10, 2015);
	// (hour, minute, second, month, day, year)

	// Client URL
	$dbHost = "http://www.kunstverein-muenchen.de/";

	// DB Admin
	$dbAdmin = $dbHost ."OPEN-RECORDS-GENERATOR/";

	// DB Media
	$dbMedia = $dbHost ."MEDIA/";
	$dbMediaAbs = "/kunden/98668_22767/rp-hosting/20082/20084/km-2015/web/MEDIA/";
	// Don't forget to set the permissions on this folder!



  ////////////////
 //  Database  //
////////////////

function dbConnectMain($dbUser)
{

	$dbMainHost = "mysql5.kunstverein-muenchen.de";
	$dbMainDbse = "db98668_71";

	if ($dbUser == 1)
	{
		$dbMainUser = "db98668_71";
		$dbMainPass = "muriel1919"; 
	}
	else if ($dbUser == 2) 
	{
		$dbMainUser = "db98668_71"; 
		$dbMainPass = "muriel1919"; 
	}
	else if ($dbUser == 3) 
	{
		$dbMainUser = "db98668_71";   	
		$dbMainPass = "muriel1919"; 
	}

	$dbConnect = MYSQL_CONNECT($dbMainHost, $dbMainUser, $dbMainPass);
	MYSQL_SELECT_DB($dbMainDbse, $dbConnect);
}

?>
