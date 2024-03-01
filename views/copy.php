<?php
$browse_url = $admin_path."browse/".$uu->urls();
$l_url = $admin_path."copy";
if($uu->urls())
{
	$l_url .= "/".$uu->urls();
}
?><div id="body-container">
	<div id="body"><?php
	// $all_items = $oo->traverse(0);
	// for($i = 0; $i < 10; $i++)
	// {
	// 	echo implode(' > ', $all_items[$i]) . '<br>';
	// }
	// $all_items_recursive = $oo->traverse_recursive(0);
	// for($i = 0; $i < 10; $i++)
	// {
	// 	echo implode(' > ', $all_items_recursive[$i]) . '<br>';
	// }
	// die();
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
			<a href="<?php echo $a_url; ?>"><?php echo $ancestor["name1"]; ?></a>
		</div><?php
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
				onsubmit="checkFamilyRole(event);"
			>
				<div class="form">
					<div class="select-container">
						<select name='wires_toid'><?
							// $all_items = $oo->traverse(0);
							// $all_items = $oo->traverse_recursive(0);
							$all_items = $oo->traverse_recursive(0, $uu->id);
							foreach($all_items as $itm)
							{
								$i = $itm['path'];
								$m = end($i);
								$role = $itm['role'];
							?><option value="<? echo $m; ?>" data-family-role="<?php echo $role; ?>"><?
								echo $itm['indent'] . $itm['name1'];
							?></option><?
							}
						?></select>
						<div class = 'filter-container'>
							<input class = 'filter_input' type = '' placeholder = 'Filter by keywords'><a href = '#null' class = 'filter_btn'>FILTER...</a><a href = '#null' class = 'clear_btn'>CLEAR...</a>
						</div>
					</div>
					<div class="button-container">
						<div>
							<input 
								id="isDeep"
								name='isDeep' 
								type='checkbox' 
								value="true"
								checked
							>
							<label for="isDeep">Copy children</label>
						</div>
						<br>
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
						<button>Copy Object</button>
						
					</div>
				</div>
			</form>
		</div>
		<script>
			/* 
			    filter 
			*/
			let filter_btn = document.getElementsByClassName('filter_btn')[0];
			let options = document.querySelectorAll('.select-container option');
			let filter_input = document.getElementsByClassName('filter_input')[0];
			let clear_input = document.getElementsByClassName('clear_btn')[0];
			let select = document.querySelector('.select-container > select');
			let full_options = select.innerHTML;

			filter_btn.addEventListener('click', function(){
				let hasFound = false;
				let filter = filter_input.value.toUpperCase();
				select.innerHTML = full_options;
				options = document.querySelectorAll('.select-container option');
				for (let i = 0; i < options.length; i++) {
					let txtValue = options[i].textContent || options[i].innerText;
					if (txtValue.toUpperCase().indexOf(filter) > -1) {
						if( !hasFound ){
							hasFound = true;
							options[i].setAttribute('selected', 'selected');
						}
					} else {
						// display block/none won't work for <option> in safari
						select.removeChild(options[i]);
					}
				}
				if(!hasFound){
					alert('no results found');
					select.innerHTML = full_options;
				}
			});
			clear_input.addEventListener('click', function(){
				filter_input.value = '';
				select.innerHTML = full_options;
			});
			function checkFamilyRole(event){
				event.preventDefault();
				
				let form = event.target;
				let select = form.querySelector('select');
				let selected = select.options[select.selectedIndex];
				let role = selected.getAttribute('data-family-role');
				if(role && role !== 'child') {
					console.log(selected.getAttribute('data-family-role'));
					if(isDeepInput = form.querySelector('input[name="isDeep"]')) { 
						// console.log(isDeepInput.value);
						if(isDeepInput.checked) {
							alert('You can\'t copy this record when "Copy children" is checked');
							return;
						}
					}
				}
				
				form.submit();
			}
		</script>
	<? 
	}
	else 
	{
		// create / reactivate wire 
		// TODO:
		// + look for an inactive wire with the same fromid and toid?
		//   to avoid re-creating wires that are just inactive?
		//   is this worth the computation?
		function copyRecord($toid, $fromid, $isDeep = false, $isUnique = false){
			global $vars;
			global $db;
			global $media_root;
			global $ww;
			global $oo;

			$vars_string = implode('`, `', $vars);
			$vars_string = '`' . $vars_string . '`';
			$vars_string .= ', `created`, `modified`';
			// duplicate object record
			$sql = "INSERT INTO objects (".$vars_string.") SELECT ".$vars_string." FROM objects WHERE id = '".$toid."'";
			$res = $db->query($sql);
			$insert_id = $db->insert_id;

			/* 
			   add '. * (copy)' to name1
               add '-copy' to url
			*/
			if(!$isUnique)
			{
				$sql = "SELECT name1, url FROM objects WHERE id = '$toid' AND active = 1";
				$res = $db->query($sql);
				$row = $res->fetch_assoc();
				$name1 = "." . $row['name1'] . " (copy)";
				$url = $row['url'] . "-(copy)";
				$sql = "UPDATE objects SET name1 = '" . $name1 . "', url = '" . $url . "' WHERE id = " . $insert_id . "";
				$res = $db->query($sql);
			}
            
			/* 
			  duplicate media
			  get media file attached to object being copied
			*/
			$sql = "SELECT * from media where object = '$toid' AND active = '1'";
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
			$ww->create_wire($fromid, $insert_id);
			if($isDeep) {
				$children = $oo->children($toid);
				foreach($children as $child){
					$copied_id = copyRecord($child['id'], $insert_id, $isDeep, true);
					// $descendant = $oo->children($child['id']);
					// if( count($descendant) ) loopChildren($child['id'], $descendant);
				}
				// loopChildren($uu->id, $children);
			}

			return $insert_id;
		}

		// function loopChildren($id, $children){
		// 	global $oo;
		// 	foreach($children as $child){
		// 		$copied = copyRecord($child['id'], $id, true);
		// 		$descendant = $oo->children($child['id']);
		// 		if( count($descendant) ) loopChildren($child['id'], $descendant);
		// 	}
		// }

		if($rr->wires_toid)
		{
			$wires_toid = addslashes($rr->wires_toid);
			$copied_id = copyRecord($wires_toid, $uu->id, $rr->isDeep);
			
		?><div>Record copied successfully.</div><?
		}
		else
		{
		?><div>Record not copied, please <a href="<? echo $js_back; ?>">try again</a>.</div><?
		}
	}
	?></div>
</div>
