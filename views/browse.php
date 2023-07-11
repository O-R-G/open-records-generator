<div id="body-container">
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

	// self
	if($uu->id)
	{
		?><div class="self-container"><?
		if($name)
		{
			?><span id="object-name"><? echo $name; ?></span>
			<span class="action">
				<?php if ($user != 'guest'): ?>
					<a href="<? echo $admin_path."edit/".$uu->urls(); ?>">EDIT... </a>
				<?php else: ?>
					<a href="<? echo $admin_path."edit/".$uu->urls(); ?>">VIEW... </a>
				<?php endif; ?>
			</span>
			<?php if ($user != 'guest'): ?>
				<span class="action">
					<a href="<? echo $admin_path."delete/".$uu->urls(); ?>">DELETE... </a>
				</span>
			<?php endif; ?><?
		}
		?></div><?
	}
		// children
		if($settings['order_type'] == 'chronological')
		{
			$fields = array("objects.*");
			$tables = array("objects", "wires");
			$where	= array("wires.fromid = '".$uu->id."'",
							"wires.active = 1",
							"wires.toid = objects.id",
							"objects.active = '1'");
			$order 	= array("objects.begin DESC", "objects.rank", "objects.name1", "objects.end");
	    	$children = $oo->get_all($fields, $tables, $where, $order);

		}
		else if($settings['order_type'] == 'alphabetical')
		{
			$fields = array("objects.*");
			$tables = array("objects", "wires");
			$where	= array("wires.fromid = '".$uu->id."'",
							"wires.active = 1",
							"wires.toid = objects.id",
							"objects.active = '1'");
			$order 	= array("objects.name1", "objects.rank", "objects.begin", "objects.end");
	    	$children = $oo->get_all($fields, $tables, $where, $order);
		}
		else
			$children = $oo->children($uu->id);
		$num_children = count($children);
		$paged = false;
		$page_total = intval( $num_children / 100 ) +1;

		if($num_children && $num_children > 100){
			$paged = true;
		?><div class = 'children-index'>PAGE<?
		for($i = 0; $i < $page_total; $i++){
			?><a href = '#null' class = 'folio'><? echo $i+1 ?></a><?
		}
		?><a href = '#null' class = 'children-prev'><</a><a href = '#null' class = 'children-next'>></a></div><?
		}
		?><div id="children"><?
		
		if($num_children)
		{	
			$pad = floor(log10($num_children)) + 1;
			if($pad < 2)
				$pad = 2;

			if($paged){
				// $page_current = 1;
				
				for ($k = 0; $k < $page_total; $k++){
					$style =  ($k == 0) ? 'display:block;' : 'display:none;';
					?><div id = 'children-page<? echo ($k+1); ?>' class = 'children-page' style = '<? echo $style ?>'><?
						for($i = $k * 100; $i <  ($k+1) * 100 ; $i++)
						{
							if(!isset($children[$i]))
								break;
							$c = $children[$i];
							$j = $i + 1;
							$j_pad = str_pad($j, $pad, "0", STR_PAD_LEFT);

							// this is to avoid adding an extra slash
							// in child urls of the root object
							$url = $admin_path."browse/";
							if($uu->urls())
								$url.= $uu->urls()."/";
							$url.= $c["url"];

							?><div class="child">
								<span><? echo $j_pad; ?></span>
								<a href="<? echo $url; ?>"><? echo $c["name1"]; ?></a>
							</div><?
						}
					?></div><?
				}
				?>
					<!-- <div class = 'children-index'><span class = 'children-prev'><<</span><?
					for($i = 0; $i < $page_total; $i++){
						?><span class = 'folio'><? echo $i+1 ?></span><?
					}
					?><span class = 'children-next'>>></span></div> -->
					<?
			}else{
				for($i = 0; $i < $num_children; $i++)
				{
					$c = $children[$i];
					$j = $i + 1;
					$j_pad = str_pad($j, $pad, "0", STR_PAD_LEFT);

					// this is to avoid adding an extra slash
					// in child urls of the root object
					$url = $admin_path."browse/";
					if($uu->urls())
						$url.= $uu->urls()."/";
					$url.= $c["url"];

					?><div class="child">
						<span><? echo $j_pad; ?></span>
						<a href="<? echo $url; ?>"><? echo $c["name1"]; ?></a>
					</div><?
				}
			}

			
		}
			?><div id="object-actions">
				<?php if ($user != 'guest'): ?>
					<span class="action">
						<a href="<? echo $admin_path."add/".$uu->urls(); ?>">ADD OBJECT... </a>
					</span>
					<span class="action">
						<a href="<? echo $admin_path."link/".$uu->urls(); ?>">LINK... </a>
					</span>
					<?php if ($user == 'admin'): ?>
						<span class="action">
							<a href="<? echo $admin_path."copy/".$uu->urls(); ?>">COPY... </a>
						</span>
					<?php endif; ?>
					<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script>
	var sChildren_page = document.getElementsByClassName('children-page');
	if(sChildren_page.length != 0){
		var page_index = 0;
		var page_num = sChildren_page.length;
		var sChildren_index = document.querySelectorAll('.children-index');

		function next_page(){
			sChildren_page[page_index].style.display = 'none';
			deactivate_folio();
			page_index ++;
			if (page_index == page_num)
				page_index = 0;
			sChildren_page[page_index].style.display = 'block';
			activate_folio(page_index);
		}
		function prev_page(){
			sChildren_page[page_index].style.display = 'none';
			deactivate_folio();
			page_index --;
			if (page_index == -1)
				page_index = page_num-1;
			sChildren_page[page_index].style.display = 'block';
			activate_folio(page_index);
		}
		function jump_to_page(index){
			sChildren_page[page_index].style.display = 'none';
			deactivate_folio();
			page_index = index;
			sChildren_page[page_index].style.display = 'block';
			activate_folio(page_index);
		}

		function activate_folio(index){
			Array.prototype.forEach.call(sChildren_index, function(el, i){
				var folio_to_activate = el.getElementsByClassName('folio')[index];
				folio_to_activate.classList.add('active');
			});
			window.scrollTo(0,0);
		}
		function deactivate_folio(){
			var active_folio = document.querySelectorAll('.folio.active');
			Array.prototype.forEach.call(active_folio, function(el, i){
				el.classList.remove('active');
			});
		}


		Array.prototype.forEach.call(sChildren_index, function(el, i){
			var this_prev = el.getElementsByClassName('children-prev')[0];
			var this_next = el.getElementsByClassName('children-next')[0];
			var this_folio = el.getElementsByClassName('folio');
			this_prev.addEventListener('click', function(){
				prev_page();
			});
			this_next.addEventListener('click', function(){
				next_page();
			});
			Array.prototype.forEach.call(this_folio, function(ell, ii){
				ell.addEventListener('click', function(){
					jump_to_page(ii);
				});
			});
		});

		// add active to page 1
		Array.prototype.forEach.call(sChildren_index, function(el, i){
			var folio_to_activate = el.getElementsByClassName('folio')[page_index];
			folio_to_activate.classList.add('active');
		});
	}
</script>
