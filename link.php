<?php require_once("GLOBAL/head.php"); 






if ($action != "link") {







	?>

	<!--  LINK TO EXISTING OBJECT  -->

	You are linking to an existing object. <br /><br />The linked object will remain in its original location and also appear here. Please choose from the list of active objects:<br /><br />

	<table cellpadding="0" cellspacing="0" border="0">
	<form enctype="multipart/form-data" action="<?php echo $dbAdmin ."link.php". urlData(); ?>" method="post" style="padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;">
	
	<tr>
	<td colspan = '2'>
	<select name='wirestoid'>
	<?php	
	
		// Developed query to only get currently active wires, but includes redundant records which is confusing so suppress in printout
		
		$sql = "SELECT objects.id, objects.name1, wires.fromid, wires.toid FROM objects, wires WHERE objects.active=1 AND wires.toid=objects.id AND wires.active = 1 AND wires.fromid != $object AND objects.id != $object ORDER BY objects.name1";

		// Simple query, just active objects
		
		// $sql = "SELECT objects.id, objects.name1 FROM objects WHERE objects.active=1 AND objects.id != $object ORDER BY objects.name1";
		
		$result = MYSQL_QUERY($sql);

		while ( $myrow = MYSQL_FETCH_ARRAY($result)) {
		
			// Suppress multiple appearances of an object
			// Suppress roots (+) and hidden objects (.)
			
			$lastObjectName = $thisObjectName;
			$thisObjectName = $myrow['name1'];

			if ( substr($thisObjectName, 0, 1) != "+" && substr($thisObjectName, 0,1) != "." && $thisObjectName != $lastObjectName ) echo "\n<option value=".$myrow['id'].">" . $myrow['name1'] . "</option>"; 
			
			//if ($thisObjectName != $lastObjectName) echo "\n<option value=".$myrow['id'].">" . $myrow['name1'] . "</option>"; 
		}
		
	?>
		
	</select>
	<br />&nbsp;</td></tr>

	</table>




	<br /><br /><br />
	<input name='action' type='hidden' value='link' />
	<input name='cancel' type='button' value='Cancel' onClick="javascript:location.href='<?php
	echo "browse.php". urlData();
	?>';" /> 
	<input name='submit' type='submit' value='Link to Object' />
	</form><br />&nbsp;
	<?php










} else {









	if (!get_magic_quotes_gpc()) {

		$wirestoid = 	addslashes($wirestoid);
	}


	//  Process variables

	// $begin = ($begin) ? date("Y-m-d H:i:s", strToTime($begin)) : NULL;
	// $end = ($end) ? date("Y-m-d H:i:s", strToTime($end)) : NULL;








	  /////////////
	 //  WIRES  //
	/////////////

	$sql = "INSERT INTO wires (created, modified, fromid, toid) VALUES('". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."', '$object', '$wirestoid')";
	$result = MYSQL_QUERY($sql);


	//echo "wirestoid = " . $wirestoid . " / wiresfromid = " . $object . "<br />";
	echo "Object linked successfully.<br /><br />";
	echo "<a href='". $dbAdmin ."browse.php". urlData() ."'>CONTINUE...</a>";
}






require_once("GLOBAL/foot.php"); ?>
