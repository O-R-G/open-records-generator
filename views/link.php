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
						<input class = 'search_input' type = 'text'><a href = '#null'  class = 'search_btn'>search</a>
						<select name='wires_toid[]'><?
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
					<a href = '#null' id = 'btn_add_item'>Add new item</a>
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
<script>
	var search_btn = document.getElementsByClassName('search_btn')[0];
	var search_btn_index = 0; // index of focused select-container
	var sSelect_container = document.querySelector('.select-container');
	var select_container_template = sSelect_container.cloneNode(true);
	var btn_remove_btn = document.createElement('button');
	btn_remove_btn.className = 'btn_remove_btn';
	btn_remove_btn.innerText = 'Remove item';
	select_container_template.appendChild(btn_remove_btn);
	var sBtn_add_item = document.getElementById('btn_add_item');

	sBtn_add_item.addEventListener('click', function(){
		search_btn_index ++;
		var new_item = select_container_template.cloneNode(true);
		sSelect_container.parentNode.insertBefore(new_item, sBtn_add_item);
		
		new_item_add_listener(search_btn_index);
	});
	

	function new_item_add_listener(index){
		var this_search_btn = document.getElementsByClassName('search_btn')[index];
		var this_select_container = this_search_btn.parentNode;
		var this_input = this_select_container.querySelector('.search_input');
		var this_options = this_select_container.querySelectorAll('option');
		var this_btn_remove = this_select_container.querySelector('.btn_remove_item');

		this_search_btn.addEventListener('click', function(){
			var hasFound = false;
			var filter = this_input.value.toUpperCase();
			for (i = 0; i < this_options.length; i++) {
				var txtValue = this_options[i].textContent || this_options[i].innerText;
				console.log(txtValue.toUpperCase().indexOf(filter) > -1);
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

		if(search_btn_index!==0){
			this_btn_remove.addEventListener('click', function(){
				search_btn_index--;
				this_select_container.parentNode.removeChild(this_select_container);
			});
		}
	}

	new_item_add_listener(search_btn_index);
	
</script>