<?
$browse_url = $admin_path."browse/".$uu->urls();
$l_url = $admin_path."link";
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
	if($rr->action != "link") 
	{
		?><div id="form-container">
			<div>
				<p>You are linking to an existing object.</p>
				<p>The linked object will remain in its original location and also appear here.</p> 
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
							value='link'
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
							value="Link to Object"
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
			$ww->create_wire($uu->id, $wires_toid);
		?><div>Record linked successfully.</div><?
		}
		else
		{
		?><div>Record not linked, please <a href="<? echo $js_back; ?>">try again</a>.</div><?
		}
	}
	?></div>
</div>