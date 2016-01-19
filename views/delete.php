<?
// the current object is linked elsewhere if (and only if?) it 
// exists in the tree (returned by $oo->traverse(0)) multiple times
$all_paths = $oo->traverse(0);
$l = 0; // is this declaration necessary?
$is_linked = false;
foreach($all_paths as $p) 
{
	if(end($p) == $uu->id)
	{
		// break when second link is found
		// no need to cycle through entire tree
		if($l) 
		{
			$is_linked = true;
			break;
		}
		else
			$l++; 
	}
}
?><div id="body-container">
	<div id="body" class="centre"><?
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
	
	// display form
	if(strtolower($rr->action) != "delete") 
	{
		// if this object does not exist elsewhere in the tree,
		// check to see if its descendents are linked elsewhere
		// (or will be deleted with the deletion of this object)
		if(!$is_linked || !empty($dep_paths))
		{
			$all_paths = $oo->traverse(0);
			$dep_paths = $oo->traverse($uu->id);
			$dep_prefix = implode("/", $uu->ids)."/";
			$dp_len = strlen($dep_prefix);
			$dep = array(); // ids only
			$all = array(); // ids only
		
			foreach($dep_paths as $p)
				$dep[] = end($p);
		
			// compare the beginning of $each path $p to $dep_prefix
			// will that work?
			foreach($all_paths as $p)
				if(!(substr(implode("/", $p), 0, $dp_len) == $dep_prefix))
					$all[] = end($p);
		
			$dependents = array_diff($dep, $all);
			$k = count($dependents);
		}
		// this code is duplicated in:
		// + link.php
		// + add.php
		?><div class="self-container">
			<div class="self">
				<a href="<? echo $browse_url; ?>"><? echo $name; ?></a>
			</div><?
			// display warning
			if($is_linked)
			{ 
			?><p>This Object is linked elsewhere, so the original will not be deleted.</p><?
			}
			else
			{
			?><p>Warning! You are about to permanently delete this Object.</p><?
				if($k) 
				{ 
			?><p>The following <? 
					if($k > 1)
						echo $k." Objects";
					else
						echo "Object"; 
					?> will also be deleted as a result:</p><?	
					$padout = floor(log10($k)) + 1;
					if ($padout < 2) 
						$padout = 2;
					$j = 1;
			?><div class="children-container"><?		
					foreach($dependents as $d) 
					{
						$child = $oo->get($d);
						$url = $admin_path."browse/".$uu->urls()."/".$child["url"];
						$child_name = strip_tags($child["name1"]);
						$j_pad = str_pad($j++, $padout, "0", STR_PAD_LEFT);
						?><div class="child">
							<span><? echo $j_pad; ?></span>
							<a href="<? echo $url; ?>"><? echo $child_name; ?></a>
						</div><?
					}
			
				}
			}
		?><form 
				action="<? echo $admin_path.'delete/'.$uu->urls(); ?>" 
				method="post"
			>
				<div class="button-container">
					<input
						type='hidden'
						name='action'
						value='delete'
					>
					<input 
						type='button'
						name='cancel' 
						value='Cancel' 
						onClick="<? echo $js_back; ?>"
					> 
					<input
						type='submit' 
						name='submit'  
						value='Delete Object'
					>
				</div>
			</form>
			</div>
		</div><?
	}
	// processs form
	else
	{
		//  get wire that goes to this object to be deleted
		if (sizeof($uu->ids) < 2) 	
			$fromid = 0;
		else
			$fromid = $uu->ids[sizeof($uu->ids) - 2];
		$message = $ww->delete_wire($fromid, $uu->id);
		// if object doesn't exist anywhere else, deactivate it
		if(!$is_linked)
			$oo->deactivate($uu->id);
	?><div class="self-container">
		<div class="self"><? echo $message; ?></div>
	</div><?
	}
	?></div>
</div>