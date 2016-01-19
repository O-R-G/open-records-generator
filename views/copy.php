<?php 
// require_once("GLOBAL/head.php"); 

// $t = "";
$t = 0; 
function traverse($n)
{
	global $t;
	$c = get_children($n);
	if(empty($c))
		return;
	else
	{
		// $t.="+";
		$t++;
		foreach($c as $child)
		{
			$s = "";
			for($i = 1; $i < $t; $i++)
				$s.="&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<option value=".$child.">".$s.get_name($child)."</option>";
			traverse($child);
		}
		$t--;
		// $t = substr($t, strlen($t)-1);
	}
		
}

function get_children($n)
{
	$c = NULL;
	$sql = "SELECT toid
			FROM wires
			INNER JOIN objects
			ON wires.fromid = objects.id
			WHERE
				objects.id = ".$n."
				AND wires.active = 1
			ORDER BY objects.rank";
	$result = MYSQL_QUERY($sql);
	while($myrow = MYSQL_FETCH_ARRAY($result))
		$c[] = $myrow['toid'];

	return $c;
}

function get_name($n)
{
	$sql = "SELECT name1
			FROM objects
			WHERE objects.id = ".$n;
	$result = MYSQL_QUERY($sql);
	$myrow = MYSQL_FETCH_ARRAY($result);
	return $myrow['name1'];
}

if ($action != "copy") 
{
?>
<!--  LINK TO EXISTING OBJECT  -->
	<p>You are copying an existing object.</p>
	<p>The copied object will remain in its original location and also appear here.</p> 
	<p>Please choose from the list of active objects:</p>

	<table cellpadding="0" cellspacing="0" border="0">
	<form 
		enctype="multipart/form-data" 
		action="<?php echo $dbAdmin ."link.php". urlData(); ?>" 
		method="post" 
		style="padding: 0px 0px 0px 0px; margin: 0px 0px 0px 0px;"
	>
		<tr>
			<td colspan = '2'>
			<select name='wirestoid'><?
			$sql = "SELECT objects.id
					FROM 
						objects, 
						wires
					WHERE 
						wires.fromid = 0
						AND wires.toid = objects.id
						AND wires.active = 1";
			$result = MYSQL_QUERY($sql);
			while($myrow = MYSQL_FETCH_ARRAY($result))
				traverse($myrow['id']);
			?></select>
			<br />&nbsp;
			</td>
		</tr>
	</table><?

	?><br /><br /><br />
	<input name='action' type='hidden' value='copy' />
	<input name='cancel' type='button' value='Cancel' onClick="javascript:location.href='<?php
	echo "browse.php". urlData();
	?>';" /> 
	<input name='submit' type='submit' value='Copy Object' />
	</form><br />&nbsp;
	<?php

} 
else 
{
	if (!get_magic_quotes_gpc()) 
	{
		$wirestoid = addslashes($wirestoid);
	}
	//  Process variables
	// $begin = ($begin) ? date("Y-m-d H:i:s", strToTime($begin)) : NULL;
	// $end = ($end) ? date("Y-m-d H:i:s", strToTime($end)) : NULL;
	
	// duplicate object record
	// edit created / modified
	$sql = "INSERT INTO objects (created, modified, name1, url, notes, deck, body, begin, end, rank)
			SELECT created, modified, name1, url, notes, deck, body, begin, end, rank
			FROM objects
			WHERE id = '$wirestoid'";
	$result = MYSQL_QUERY($sql);
	$insertId = MYSQL_INSERT_ID();
	
	// duplicate media
	
	// get media files attached to object being copied
	$sql = "SELECT * from media where object = '$wirestoid' AND active = '1'";
	$result = MYSQL_QUERY($sql);
	echo $sql;
	
	$marrarr = array();
	while($myrow = mysql_fetch_array($result))
	{
		$marr = array();
		$m = "".STR_PAD($myrow['id'], 5, "0", STR_PAD_LEFT);
		$mfile = $dbMedia.$m.".".$myrow['type'];
		$marr['f'] = $mfile;
		$marr['id'] = $myrow['id'];
		$marrarr[] = $marr;
	}
	
	// copy media attached to old object and attach to new object
	foreach($marrarr as $marr)
	{
		$sql = "SELECT id FROM media ORDER BY id DESC LIMIT 1";
		$result = MYSQL_QUERY($sql);
		$myrow = mysql_fetch_array($result);
		
		$targetType = explode(".", $mfile);
		$targetFile = str_pad(($myrow["id"]+1), 5, "0", STR_PAD_LEFT) .".". $targetType;
		$target = $dbMedia.$targetFile;
		
		copy($mfile, $target);
		
		$sql = "INSERT INTO media (type, caption, object, created, modified)
				SELECT type, caption, '$insertId', created, modified
				FROM media
				WHERE id = '".$marr['id']."'";
		$result = MYSQL_QUERY($sql);
	}
	  /////////////
	 //  WIRES  //
	/////////////

	$sql = "INSERT INTO wires (created, modified, fromid, toid) VALUES('". date("Y-m-d H:i:s") ."', '". date("Y-m-d H:i:s") ."', '$object', '$insertId')";
	echo $sql;
	$result = MYSQL_QUERY($sql);

	//echo "wirestoid = " . $wirestoid . " / wiresfromid = " . $object . "<br />";
	echo "Object linked successfully.<br /><br />";
	echo "<a href='". $dbAdmin ."browse.php". urlData() ."'>CONTINUE...</a>";
}

//require_once("GLOBAL/foot.php"); 
?>
