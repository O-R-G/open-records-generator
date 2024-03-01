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
						<select name='wires_toid[]'><?
                            // unlinked_list() massively optimized using mysql query
                            // in place of multiple array_merge()
							$all_items = $oo->traverse_recursive($uu->id);
							foreach($all_items as $i)
							{
								$m = $i['toid'];
								$t = $i['indent'];
								$n = $i['name1'];
								$disabled = $i['role'] ? 'disabled' : '';
							?><option value="<?php echo $m; ?>" <?php echo $disabled; ?>><?
								echo $t . $n;
							?></option><?
							}
						?></select>
						<div class = 'filter-container'>
							<input class = 'filter_input' type = '' placeholder = 'Filter by keywords'><a href = '#null' class = 'filter_btn'>FILTER...</a><a href = '#null' class = 'clear_btn'>CLEAR...</a>
						</div>
					</div>
					<!-- <button id = 'btn_add_item'>Add new item</button> -->
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
					if (txtValue.toUpperCase().indexOf(filter) > -1 && !options[i].disabled) {
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
		if($rr->wires_toid)
		{
			$wires_toids = $rr->wires_toid;
			foreach($wires_toids as $wid){
				$wires_toid = addslashes($wid);
				$ww->create_wire($uu->id, $wires_toid);
			}
			
		?><div>Record linked successfully.</div><?
		}
		else
		{
		?><div>Record not linked, please <a href="<? echo $js_back; ?>">try again</a>.</div><?
		}
	}
	?></div>
</div>
