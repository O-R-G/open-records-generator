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
				<a href="<? echo $admin_path."edit/".$uu->urls(); ?>">EDIT... </a>
			</span>
			<span class="action">
				<a href="<? echo $admin_path."delete/".$uu->urls(); ?>">DELETE... </a>
			</span><?
		}
		?></div><?
	}
		// children		
		$children = $oo->children($uu->id);
		$num_children = count($children);
		?><div id="children"><?
		if($num_children)
		{
			$pad = floor(log10($num_children)) + 1;
			if($pad < 2)
				$pad = 2;
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
			?><div id="object-actions">
				<span class="action">
					<a href="<? echo $admin_path."add/".$uu->urls(); ?>">ADD OBJECT... </a>
				</span>
				<span class="action">
					<a href="<? echo $admin_path."link/".$uu->urls(); ?>">LINK... </a>
				</span>
			</div>
		</div>
	</div>
</div>