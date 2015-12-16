<?php require_once("GLOBAL/head.php");
echo "\n\n\n\n<br /><br />\n\n";








if ($action != "update") {




	if ($object) {


		  ///////////////////////
		 //  OBJECT CONTENTS  //
		///////////////////////

		$sql = "SELECT * FROM objects WHERE id = '". $objects[$o] ."' AND active = 1 LIMIT 1";
		$result = MYSQL_QUERY($sql);
		$myrow = MYSQL_FETCH_ARRAY($result);
		?>


		<br />
		<table class="edit" cellpadding="0" cellspacing="0" border="0">
		<form enctype="multipart/form-data" action="<?php echo $dbAdmin ."edit.php". urlData(); ?>" method="post" style="margin: 0; padding: 0;">
		<input name='id' type='hidden' value='<?php echo $id; ?>'>
		<input name='action' type='hidden' value='update'>

		<tr><td width="90">Name&nbsp; </td>
		<td><textarea name='name1' cols='40' rows='3'><?php echo $myrow["name1"]; ?></textarea></td></tr>

		<tr><td>Synopsis&nbsp; </td>
		<td><textarea name='deck' cols='40' rows='3'><?php echo $myrow["deck"]; ?></textarea></td></tr>

		<tr><td>Detail&nbsp; </td>
		<td><textarea name='body' cols='40' rows='12'><?php echo $myrow["body"]; ?></textarea></td></tr>

		<tr><td>Notes&nbsp; </td>
		<td><textarea name='notes' cols='40' rows='3'><?php echo $myrow["notes"]; ?></textarea></td></tr>

		<tr><td>Begin&nbsp; </td>
		<td><textarea name='begin' cols='40' rows='3'><?php echo $myrow["begin"]; ?></textarea></td></tr>

		<tr><td>End&nbsp; </td>
		<td><textarea name='end' cols='40' rows='3'><?php echo $myrow["end"]; ?></textarea></td></tr>

		<tr><td>URL&nbsp; </td>
		<td><textarea name='url' cols='40' rows='3'><?php echo $myrow["url"]; ?></textarea></td></tr>

		<tr><td>Rank&nbsp; </td>
		<td><textarea name='rank' cols='3' rows='3'><?php echo $myrow["rank"]; ?></textarea>
		<br /><br />&nbsp;</td></tr>
		
		
		<?php


		//  Show Existing Images

		$objectID = $myrow["id"];
		$i = 1;
		$sql = "SELECT * FROM media WHERE object = '". $objectID ."' AND active = '1' ORDER BY rank, modified, created, id";
		$result = MYSQL_QUERY($sql);
		$num_rows = MYSQL_NUM_ROWS($result);

		while ($myrow = MYSQL_FETCH_ARRAY($result)) {

			$j = $i - 1;  // There's good reason for this, I swear
			$mediaNum = "". STR_PAD($myrow["id"], 5, "0", STR_PAD_LEFT);
			$mediaFile = $dbMedia . $mediaNum .".". $myrow["type"];	
			$mediaFileDisplay = ($myrow["type"] == "pdf") ? "MEDIA/pdf.gif" : $mediaFile;
			echo "\n\n<tr><td>Image ". STR_PAD($i, 2, "0", STR_PAD_LEFT) ."&nbsp; </td>";
			echo "\n<td><a href='$mediaFile' target='_blank'><img src='". $mediaFileDisplay ."' width='160' border='0'></a>";
			echo "\n<input type='hidden' name='mediaId[". $j ."]' value='". $myrow["id"] ."' />";
			echo "\n<input type='hidden' name='mediaType[". $j ."]' value='". $myrow["type"] ."' />";
			echo "\n<br /><textarea name='mediaCaption[". $j ."]' cols='40' rows='3'>". $myrow["caption"] ."</textarea>";
			echo "\n<br /><select name='mediaRank[". $j ."]'>";	
			for ($k = 1; $k <= $num_rows; $k++) {
				if ($k == $myrow["rank"]) echo "\n<option selected value=".$k.">".$k."</option>";
				else echo "\n<option value=".$k.">".$k."</option>"; 
			}
			echo "\n<br /></select> Rank";			
			echo "\n&nbsp;&nbsp;<input name='mediaDelete[". $j ."]' type='checkbox' /> Delete Image<br />&nbsp;";
			$i++;
		}


		//  Upload New Images
		// $uploadsMax = 5;
		for ($j = 0; $j < $uploadsMax; $j++) {

			echo "\n\n<tr><td>Image ". STR_PAD($i++, 2, "0", STR_PAD_LEFT) ."&nbsp; </td>";
			echo "\n<td><input type='file' name='upload". $j ."' /><br />";
			echo "&nbsp;"; //echo "\n<textarea name='mediaCaption[". ($j + $i)  ."]' cols='40' rows='3'>". $myrow["caption"] ."</textarea><br />&nbsp;";
			echo "\n</td></tr>";
		}
		echo "\n<input type='hidden' name='uploadsMax' value='". $uploadsMax ."' />";
		echo "\n</table>";
		?>


		<br /><br /><br />
		<input name='action' type='hidden' value='update' />
		<input name='cancel' type='button' value='Cancel' onClick="javascript:location.href='<?php echo "browse.php". urlBack(); ?>';" /> 
		<input name='submit' type='submit' value='Update Object' />
		</form><br />&nbsp;
		<?php
	}










} else {

	// echo "*1*";








	  /////////////////////
	 //  UPDATE Object  //
	/////////////////////

	$sql = "SELECT * FROM objects WHERE id = '$object' LIMIT 1";
	$result = MYSQL_QUERY($sql);
	$myrow = MYSQL_FETCH_ARRAY($result);

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
	$z = NULL;


	//  Check for differences

	if ($myrow["name1"] 	!= $name1) 	$z .= "name1='$name1', ";
	if ($myrow["name2"] 	!= $name2) 	$z .= "name2='$name2', ";
	if ($myrow["deck"] 	!= $deck) 	$z .= "deck='$deck', ";
	if ($myrow["body"] 	!= $body) 	$z .= "body='$body', ";
	if ($myrow["notes"] 	!= $notes) 	$z .= "notes='$notes', ";
	if ($myrow["url"] 	!= $url) 	$z .= "url='$url', ";
	if ($myrow["begin"] 	!= $begin) 	$z .= ($begin) ? "begin ='$begin', " : "begin = null, ";
	if ($myrow["end"] 	!= $end) 	$z .= ($end) ? "end ='$end', " : "end = null, ";
	if ($myrow["rank"] 	!= $rank) 	$z .= ($rank) ? "rank ='$rank', " : "rank = null, ";


	//  Update edited fields only

	if ($z) {

		$sql = "UPDATE objects SET ". $z ."modified='". date("Y-m-d H:i:s") ."' WHERE id = '$object'";
		$result = MYSQL_QUERY($sql);
	}



	// echo "*2*";





	  ////////////////////
	 //  DELETE Media  //
	////////////////////

	$m = FALSE;
	for ($i = 0; $i < sizeof($mediaType); $i++) {

		//  Use sizeof(mediaType) because if checkbox is unchecked that variable "doesn't exist"
		//  although the expected behavior is for it to exist but be null.

		if ($mediaDelete[$i]) {

			$mediaIdThis = $mediaId[$i];
			$mediaTypeThis = $mediaType[$i];
			$killPath = $dbMedia;
			$killFile = STR_PAD($mediaIdThis, 5, "0", STR_PAD_LEFT) .".". $mediaTypeThis;	
			//$killed = unlink($killPath.$killFile);
			//if ($killed) {
				//echo "Delete $killFile FAILED<br />";
				$sql = "UPDATE media SET active = '0', modified = '". date("Y-m-d H:i:s") ."' WHERE id = '$mediaIdThis'";
				$result = MYSQL_QUERY($sql);
			//} else {
				//echo "Delete $killFile FAILED<br />";
				//printf ("ERROR %s<P>", $_FILES['imagefile'. $i]['error']);
			//}
			$m = TRUE;
		}
	}




	// echo "*3*";







	  ////////////////////
	 //  UPLOAD Media  //
	////////////////////

	for ($i = 0; $i < $uploadsMax; $i++) {

		if ($imageName = $_FILES["upload".$i]["name"]) {

			$sql = "SELECT id FROM media ORDER BY id DESC LIMIT 1";
			$result = MYSQL_QUERY($sql);
			$myrow = MYSQL_FETCH_ARRAY($result);

			$nameTemp = $_FILES["upload". $i]['name'];
			$typeTemp = explode(".", $nameTemp);
			$type = $typeTemp[sizeof($typeTemp) - 1];

			$targetPath = "../MEDIA/"; //$dbMedia;
			$targetFile = str_pad(($myrow["id"]+1), 5, "0", STR_PAD_LEFT) .".". $type;
			$target = $targetPath . $targetFile;


			// ** Image Resizing **

			// Only if folder ../MEDIA/_HI exists
			// Assume uploads are at 300dpi
			// Scale image down from 300 to 72 (24%)
			
			// First upload the raw image to ../MEDIA/_HI/ folder
			// If upload works, then resize and copy to main ../MEDIA/ folder
			// To turn on set $resize = TRUE; FALSE by default

			$resize = FALSE; 
			$resizeScale = 65;
			$targetPath = ($resize) ? "../MEDIA/_HI/" : "../MEDIA/";

			$target = $targetPath . $targetFile;

			// echo "wants to upload |". $_FILES["upload". $i]['name'] ."| to |". $target ."|<br /><br />";
			
			$copy = copy($_FILES["upload".$i]['tmp_name'], $target);
			
			if ($copy) {
			
				if ($resize) {
	
					include('_Extensions/SimpleImage.php');
								
					$image = new SimpleImage();
					$image->load($target);
					$image->scale($resizeScale);
	
					$targetPath = "../MEDIA/"; //$dbMedia;
					$target = $targetPath . $targetFile;	
					
					$image->save($target);
					
					echo "Upload $imageName SUCCESSFUL<br />";
					echo "Copy $target SUCCESSFUL<br />";
				}

								
				// Add to DB's image list
								
				$sql = "INSERT INTO media (type, caption, object, created, modified) VALUES ('$type', '". $mediaCaption[sizeof($mediaId) + $i] ."', '$object', '". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."')";
								
				$result = MYSQL_QUERY($sql);
				
			} else {
				
				//echo "Upload $imageName FAILED<br />";
				//printf ("ERROR %s<br/>", $_FILES['imagefile'. $i]['error']);
			}
			
			$m = TRUE;
			
		}
	}







	// echo "*4*";



	  ////////////////////////////////////
	 //  UPDATE Caption, Weight, Rank  //
	////////////////////////////////////

	$mediaCaptionLimit = sizeof($mediaCaption);

	/* 
	echo "*5*";
	echo "# captions: " . $mediaCaptionLimit . " ";
	echo "# mediaId: " . sizeof($mediaId)  . " ";
	*/
	
	if (sizeof($mediaId) < $mediaCaptionLimit) $mediaCaptionLimit = sizeof($mediaId);
	for ($i = 0; $i < $mediaCaptionLimit; $i++) {

		// echo "*6*";

		$mediaIdThis = $mediaId[$i];
		$sql = "SELECT * FROM media WHERE id = '$mediaIdThis' LIMIT 1";
		$result = MYSQL_QUERY($sql);
		$myrow = MYSQL_FETCH_ARRAY($result);

		if (!get_magic_quotes_gpc()) {

			$mediaCaption[$i] = 	addslashes($mediaCaption[$i]);
			$mediaRank[$i] 	  = 	addslashes($mediaRank[$i]);
			//$weight[$i] = 	addslashes($weight[$i]);
		}

		$z2 = NULL;
		
		if ($myrow["caption"] != $mediaCaption[$i]) 	$z2 .= "caption='". $mediaCaption[$i] ."', ";
		if ($myrow["rank"] != $mediaRank[$i]) 	        $z2 .= "rank='". $mediaRank[$i] ."', ";
		
		//if ($myrow["weight"] != $weight[$i]) 		$z2 .= "weight='". $weight[$i] ."', ";

		if ($z2) {
					
			$sql = "UPDATE media SET ". $z2 ."modified = '". date("Y-m-d H:i:s") ."' WHERE id = '$mediaIdThis'";
			$result = MYSQL_QUERY($sql);
			$m = TRUE;
			
			// echo "*7*";
			// echo $sql;	
		}
	}








	//  Job well done?

	if ($z || $m || $z2) {

		echo "Record successfully updated.<br />";

	} else {

		echo "Nothing was edited, therefore update not required.<br />";
	}
	echo "<br /><br /><a href='". $dbAdmin ."edit.php". urlData() ."'>REFRESH OBJECT</a><br />&nbsp;";
}








require_once("GLOBAL/foot.php"); ?>
