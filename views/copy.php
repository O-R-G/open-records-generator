<?
$browse_url = $admin_path."browse/".$uu->urls();
$l_url = $admin_path."copy";
if($uu->urls())
{
	$l_url .= "/".$uu->urls();
}
?><div id="body-container">
	<div id="body"><?
	// TODO: this code is duplicated in 
	// + add.php 
	// + browse.php
	// + edit.php
	// + link.php
	// ancestors
	$a_url = $admin_path."browse";
	for($i = 0; $i < count($uu->ids)-1; $i++)
	{
		$a = $uu->ids[$i];
		$ancestor = $oo->get($a);
		$a_url.= "/".$ancestor["url"];
		?><div class="ancestor">
			<a href="<? echo $a_url; ?>"><? echo $ancestor["name1"]; ?></a>
		</div><?
	}
	// END TODO
	
	// this code is duplicated in:
	// + link.php
	// + add.php
	if($uu->id)
	{
	?><div class="self-container">
		<div class="self">
			<a href="<? echo $browse_url; ?>"><? echo $name; ?></a>
		</div>
	</div><?
	}
	if($rr->action != "copy") 
	{
		?><div id="form-container">
			<div>
				<p>You are copying an existing object.</p>
				<p>The copied object will remain in its original location and also appear here.</p> 
				<p>Please choose from the list of active objects:</p>
			</div>
			<form 
				enctype="multipart/form-data"
				action="<? echo $l_url; ?>"
				method="post" 
			>
				<div class="form">
					<div class="select-container">
						<select name='wires_toid'><?
							$items = $oo->unlinked_list($uu->id);
							$all_items = $oo->traverse(0);
							foreach($all_items as $i)
							{
								$m = end($i);
								if(!in_array($m, $items))
									$m = 0; 
								$d = count($i); 
								$t = "&nbsp;&nbsp;&nbsp;";
							?><option value="<? echo $m; ?>"><?
								for($j=1; $j < $d; $j++)
									echo $t;
								if(!$m)
									echo "(".$oo->name(end($i)).")";
								else
									echo $oo->name(end($i));
							?></option><?
							}
						?></select>
					</div>
					<div class="button-container">
						<input 
							name='action' 
							type='hidden' 
							value='copy'
						>
						<input 
							name='cancel' 
							type='button' 
							value='Cancel' 
							onClick="<? echo $js_back; ?>"
						>
						<input 
							name='submit' 
							type='submit' 
							value="Copy Object"
						>
					</div>
				</div>
			</form>
		</div><? 
	}
	else 
	{
		// create / reactivate wire 
		// TODO:
		// + look for an inactive wire with the same fromid and toid?
		//   to avoid re-creating wires that are just inactive?
		//   is this worth the computation?
		if($rr->wires_toid)
		{
			$wires_toid = addslashes($rr->wires_toid);
			
			// duplicate object record
			$sql = "INSERT INTO objects (created, modified, name1, url, notes, deck, body, begin, end, rank)
			SELECT created, modified, name1, url, notes, deck, body, begin, end, rank
			FROM objects
			WHERE id = '$wires_toid'";
			$res = $db->query($sql);
			$insert_id = $db->insert_id;
			
			// duplicate media
			// get media file attached to object being copied
			$sql = "SELECT * from media where object = '$wires_toid' AND active = '1'";
			$res = $db->query($sql);
			$m_arr_arr = array();
			
			while($row = $res->fetch_assoc())
			{
				$m_arr = array();
				$m_file = m_root($row);
				
				$sql = "SELECT id FROM media ORDER BY id DESC LIMIT 1";
				$res_n = $db->query($sql);
				$n = $res_n->fetch_assoc()["id"] + 1;
				
				$m_file_copy = $media_root.m_pad($n).".".$row['type'];
				copy($m_file, $m_file_copy);
				
				$sql = "INSERT INTO media (type, caption, object, created, modified)
				SELECT type, caption, '$insert_id', created, modified
				FROM media
				WHERE id = '".$row['id']."'";
				$db->query($sql);
			}
			
			// make wires
			$ww->create_wire($uu->id, $insert_id);
		?><div>Record copied successfully.</div><?
		}
		else
		{
		?><div>Record not copied, please <a href="<? echo $js_back; ?>">try again</a>.</div><?
		}
	}
	?></div>
</div>