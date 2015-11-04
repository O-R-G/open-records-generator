<?php require_once("GLOBAL/head.php"); 






if ($action != "add") {







	?>

	<!--  ADD NEW OBJECT  -->

	You are adding a new object.<br /><br /><br />

	<table cellpadding="0" cellspacing="0" border="0">
	<form enctype="multipart/form-data" action="<?php echo $dbAdmin ."add.php". urlData(); ?>" method="post" style="padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;">

	<tr><td width="90">Name&nbsp; </td>
	<td><textarea name='name1' cols='40' rows='3'></textarea></td></tr>
 
	<tr><td>Synopsis&nbsp; </td>
	<td><textarea name='deck' cols='40' rows='3'></textarea></td></tr>

	<tr><td>Detail&nbsp; </td>
	<td><textarea name='body' cols='40' rows='12'></textarea></td></tr>

	<tr><td>Notes&nbsp; </td>
	<td><textarea name='notes' cols='40' rows='3'><?php echo $myrow["notes"]; ?></textarea></td></tr>

	<tr><td>Begin&nbsp; </td>
	<td><textarea name='begin' cols='40' rows='3'><?php echo $myrow["begin"]; ?></textarea></td></tr>

	<tr><td>End&nbsp; </td>
	<td><textarea name='end' cols='40' rows='3'><?php echo $myrow["end"]; ?></textarea></td></tr>

	<tr><td>URL&nbsp; </td>
	<td><textarea name='url' cols='40' rows='3'></textarea></td></tr>

	<tr><td>Rank&nbsp; </td>
	<td><textarea name='rank' cols='3' rows='3'></textarea>
	<br /><br />&nbsp;</td></tr>

	<?php






	//  Upload New Images

	for ($j = 0; $j < $uploadsMax; $j++) {

		echo "\n\n<tr><td>Image ". STR_PAD(++$i, 2, "0", STR_PAD_LEFT) ."&nbsp; </td>";
		// echo "\n\n<tr><td>Image&nbsp; </td>";
		echo "\n<td><input type='file' name='upload". $j ."' /><br />&nbsp;";
		echo "\n</td></tr>";
	}
	echo "<input type='hidden' name='uploadsMax' value='". $j ."' />";
	echo "</table>";
	?>






	<br /><br /><br />
	<input name='action' type='hidden' value='add' />
	<input name='cancel' type='button' value='Cancel' onClick="javascript:location.href='<?php
	echo "browse.php". urlData();
	?>';" /> 
	<input name='submit' type='submit' value='Add Object' />
	</form><br />&nbsp;
	<?php










} else {









	  //////////////
	 //  OBJECT  //
	//////////////

	if (!get_magic_quotes_gpc()) {

		$name1 = 	addslashes($name1);
		$name2 = 	addslashes($name2);
		$deck = 	addslashes($deck);
		$body = 	addslashes($body);
		$notes =  	addslashes($notes); 
		$url =  	addslashes($url); 
		$begin =  	addslashes($begin);
		$end =  	addslashes($end);
		$rank = 	addslashes($rank);
	}


	//  Process variables

	if (!$name1) $name1 = "Untitled";
	$begin = ($begin) ? date("Y-m-d H:i:s", strToTime($begin)) : NULL;
	$end = ($end) ? date("Y-m-d H:i:s", strToTime($end)) : NULL;


	//  Add object to database

	$sql = "INSERT INTO objects (created, modified, name1, url, notes, deck, body, begin, end, rank) VALUES('". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."', '$name1', '$url', '$notes', '$deck', '$body', ";
	$sql .= ($begin)  ? "'$begin', " : "null, ";
	$sql .= ($end)  ? "'$end', " : "null, ";
	$sql .= ($rank) ? "'$rank')" : "null)";
	$result = MYSQL_QUERY($sql);
	$insertId = MYSQL_INSERT_ID();








	  /////////////
	 //  WIRES  //
	/////////////

	$sql = "INSERT INTO wires (created, modified, fromid, toid) VALUES('". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."', '$object', '$insertId')";
	$result = MYSQL_QUERY($sql);








	  /////////////
	 //  MEDIA  //
	/////////////

	for ($i = 0; $i < $uploadsMax; $i++) {

		if ($imageName = $_FILES["upload".$i]["name"]) {

			$sql = "SELECT id FROM media ORDER BY id DESC LIMIT 1";
			$result = MYSQL_QUERY($sql);
			$myrow = MYSQL_FETCH_ARRAY($result);

			$nameTemp = $_FILES["upload". $i]['name'];
			$typeTemp = explode(".", $nameTemp);
			$type = $typeTemp[sizeof($typeTemp) - 1];
			$targetFile = str_pad(($myrow["id"]+1), 5, "0", STR_PAD_LEFT) .".". $type;				


			// ** Image Resizing **

			// Only if folder ../MEDIA/_HI exists
			// Assume uploads are at 300dpi
			// Scale image down from 300 to 72 (24%)
			// First upload the raw image to ../MEDIA/_HI/ folder
			// If upload works, then resize and copy to main ../MEDIA/ folder
			// To turn on set $resize = TRUE; FALSE by default

			$resize = FALSE; 
			$targetPath = ($resize) ? "../MEDIA/_HI/" : "../MEDIA/";

			$target = $targetPath . $targetFile;

			// echo "wants to upload |". $_FILES["upload". $i]['name'] ."| to |". $target ."|<br /><br />";
			
			$copy = copy($_FILES["upload".$i]['tmp_name'], $target);
			
			if ($copy) {
			
				if ($resize) {
	
					include('_Extensions/SimpleImage.php');
								
					$image = new SimpleImage();
					$image->load($target);
					$image->scale(24);
	
					$targetPath = "../MEDIA/"; //$dbMedia;
					$target = $targetPath . $targetFile;	
					
					$image->save($target);
					
					echo "Upload $imageName SUCCESSFUL<br />";
					echo "Copy $target SUCCESSFUL<br />";
				}

								
				// Add to DB's image list
				
				$sql = "INSERT INTO media (type, caption, object, created, modified) VALUES ('$type', '', '$insertId', '". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."')";
				$result = MYSQL_QUERY($sql);
				
			} else {
				
				//echo "Upload $imageName FAILED<br />";
				//printf ("ERROR %s<br/>", $_FILES['imagefile'. $i]['error']);
			}
			
			$m = TRUE;
		}
	}
	echo "Object added successfully.<br /><br />";
	echo "<a href='". $dbAdmin ."browse.php". urlData() ."'>CONTINUE...</a>";
}






require_once("GLOBAL/foot.php"); ?>
