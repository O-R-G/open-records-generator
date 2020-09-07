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
							$items = $oo->unlinked_list($uu->id);
							foreach($items as $i) {
                                ?><option value="<? echo $i['id']; ?>"><?
                                echo $i['name1'];
                                ?></option><?
                            }
						?></select>
						<div class = 'filter_ctner'>
							<input class = 'filter_input' type = '' placeholder = 'Filter by keywords'><a href = '#null' class = 'filter_btn'>FILTER...</a><a href = '#null' class = 'clear_btn'>CLEAR...</a>
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
			/* ========= for linking multiple objects =========
			wei: If linking multiple objects is reactivated someday,
			it needs some modification like the part hiding/showing filtered results. 

			var filter_btn = document.getElementsByClassName('filter_btn')[0];
			var filter_btn_index = 0; // index of focused select-container
			var sSelect_container = document.querySelector('.select-container');
			var select_container_template = sSelect_container.cloneNode(true);
			var btn_remove_btn = document.createElement('button');
			btn_remove_btn.className = 'btn_remove_item';
			btn_remove_btn.innerText = 'Remove item';
			select_container_template.appendChild(btn_remove_btn);
			var sBtn_add_item = document.getElementById('btn_add_item');

			sBtn_add_item.addEventListener('click', function(e){
				e.preventDefault();
				filter_btn_index ++;
				var new_item = select_container_template.cloneNode(true);
				sSelect_container.parentNode.insertBefore(new_item, sBtn_add_item);
				
				new_item_add_listener(filter_btn_index);
			});
			

			function new_item_add_listener(index){
				var this_filter_btn = document.getElementsByClassName('filter_btn')[index];
				var this_select_container = this_filter_btn.parentNode;
				var this_input = this_select_container.querySelector('.filter_input');
				var this_options = this_select_container.querySelectorAll('option');
				var this_btn_remove = this_select_container.querySelector('.btn_remove_item');

				this_filter_btn.addEventListener('click', function(e){
					e.preventDefault();
					var hasFound = false;
					var filter = this_input.value.toUpperCase();
					for (i = 0; i < this_options.length; i++) {
						var txtValue = this_options[i].textContent || this_options[i].innerText;
						if (txtValue.toUpperCase().indexOf(filter) > -1) {
							if( !hasFound ){
								hasFound = true;
								this_options[i].setAttribute('selected', 'selected');
							}
							this_options[i].style.display = "block";
							this_options[i].disabled = false;
						} else {
							this_options[i].style.display = "none";
							this_options[i].disabled = true;
						}
						
					}
					if(!hasFound){
						alert('no results found');
						for (i = 0; i < this_options.length; i++) {
								this_options[i].style.display = "block";
								this_options[i].disabled = false;				
						}
					}
				});
				if(filter_btn_index!==0){
					this_btn_remove.addEventListener('click', function(e){
						e.preventDefault();
						filter_btn_index--;
						this_select_container.parentNode.removeChild(this_select_container);
					});
				}
			}

			new_item_add_listener(filter_btn_index);
			*/

			/* ========= for linking single object ========== */

			var filter_btn = document.getElementsByClassName('filter_btn')[0];
			var options = document.querySelectorAll('.select-container option');
			var filter_input = document.getElementsByClassName('filter_input')[0];
			var clear_input = document.getElementsByClassName('clear_btn')[0];
			var select = document.querySelector('.select-container > select');
			var full_options = select.innerHTML;

			filter_btn.addEventListener('click', function(){
				var hasFound = false;
				var filter = filter_input.value.toUpperCase();
				select.innerHTML = full_options;
				options = document.querySelectorAll('.select-container option');
				for (i = 0; i < options.length; i++) {
					var txtValue = options[i].textContent || options[i].innerText;
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
