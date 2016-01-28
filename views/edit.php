<?
$vars = array("name1", "deck", "body", "notes",  "url", "rank", "begin", "end");

$kvars = array();
$kvars["name1"] = "text";
$kvars["deck"] = "textarea";
$kvars["body"] = "textarea";
$kvars["notes"] = "textarea";
$kvars["begin"] = "datetime-local";
$kvars["end"] = "datetime-local";
$kvars["url"] = "text";
$kvars["rank"] = "text";

$var_labels = array();
$var_labels["name1"] = "Name";
$var_labels["deck"] = "Synopsis";
$var_labels["body"] = "Detail";
$var_labels["notes"] = "Notes";
$var_labels["begin"] = "Begin";
$var_labels["end"] = "End";
$var_labels["url"] = "URL Slug";
$var_labels["rank"] = "Rank";

$browse_url = $admin_path.'browse/'.$uu->urls();

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
if ($rr->action != "update" && $uu->id)
{
	// get existing image data
	$medias = $oo->media($uu->id);
	$num_medias = count($medias);
	
	// add associations to media arrays:
	// $medias[$i]["file"] is url of media file
	// $medias[$i]["display"] is url of display file (diff for pdfs)
	// $medias[$i]["type"] is type of media (jpg, 
	for($i = 0; $i < $num_medias; $i++)
	{
		$m_padded = "".m_pad($medias[$i]['id']);
		$medias[$i]["file"] = $media_path . $m_padded . "." . $medias[$i]["type"];
		if ($medias[$i]["type"] == "pdf")
			$medias[$i]["display"] = $admin_path."media/pdf.png";
		else
			$medias[$i]["display"] = $medias[$i]["file"];
	}
// object contents
?><div id="form-container">
		<div class="self">
			<a href="<? echo $browse_url; ?>"><?php echo $name; ?></a>
		</div>
		<form
			method="post"
			enctype="multipart/form-data" 
			action="<?php echo htmlspecialchars($admin_path.'edit/'.$uu->urls()); ?>" 
		>
			<div class="form"><?php
				// show object data
				foreach($vars as $var)
				{
				?><div class="field"><?
					if($kvars[$var] == "datetime-local")
					{
					?><span class="field-name"><? echo $var_labels[$var]; ?></span>
					<span>
						<input 
							name='<? echo $var; ?>' 
							type='<? echo $kvars[$var]; ?>'
							value='<? echo $item[$var]; ?>'
						>
					</span><?
					}
					else
					{
					?><div class="field-name"><? echo $var_labels[$var]; ?></div>
					<div><?
						if($kvars[$var] == "textarea")
						{
						?><textarea name='<? echo $var; ?>' class='large'><?
							if($item[$var])
							{
								echo $item[$var];
							}
						?></textarea><?
						}
						else
						{
						?><input name='<? echo $var; ?>' 
								type='<? echo $kvars[$var]; ?>'
								value='<? echo htmlspecialchars($item[$var], ENT_QUOTES); ?>'><?
						}
					?></div><?
					}
				?></div><?
				}
				// show existing images
				for($i = 0; $i < $num_medias; $i++)
				{
				?><div class="existing-image">
					<div class="field-name">Image <? echo str_pad($i+1, 2, "0", STR_PAD_LEFT);?></div>
					<div class='preview'>
						<a href="<? echo $medias[$i]['file']; ?>" target="_blank">
							<img src="<? echo $medias[$i]['display']; ?>">
						</a>
					</div>
					<textarea name="captions[]"><?php
						echo $medias[$i]["caption"];
					?></textarea>
					<span>rank</span>
					<select name="ranks[<? echo $i; ?>]"><?php
						for($j = 1; $j <= $num_medias; $j++)
						{
							if($j == $medias[$i]["rank"])
							{
							?><option selected value="<? echo $j; ?>"><? 
								echo $j; 
							?></option><?php
							}
							else
							{
							?><option value="<? echo $j; ?>"><? 
								echo $j; 
							?></option><?php
							}
						}
					?></select>
					<label>
						<input
							type="checkbox"
							name="deletes[<? echo $i; ?>]"
						>
					delete image</label>
					<input 
						type="hidden"
						name="medias[<? echo $i; ?>]"
						value="<? echo $medias[$i]['id']; ?>"
					>
					<input 
						type="hidden"
						name="types[<? echo $i; ?>]"
						value="<? echo $medias[$i]['type']; ?>"
					>
				</div><?php
				}
				// upload new images
				for($j = 0; $j < $max_uploads; $j++)
				{
				?><div class="image-upload">
					<span class="field-name">Image <?php echo str_pad(++$i, 2, "0", STR_PAD_LEFT); ?></span>
					<span>
						<input type="file" name="uploads[]">
					</span>
					<!--textarea name="captions[]"><?php
							echo $medias[$i]["caption"];
					?></textarea-->
				</div><?php
				} ?>
				<div class="button-container">
					<input
						type='hidden'
						name='action'
						value='update'
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
						value='Update Object'
					>
				</div>
			</div>
		</form>
	</div>
<?php
}
// THIS CODE NEEDS TO BE FACTORED OUT SO HARD
// basically the same as what is happening in add.php
else 
{	
	// objects
	foreach($vars as $var)
	{
		$$var = addslashes($rr->$var);
		$item[$var] = addslashes($item[$var]);
	}

	//  process variables
	if (!$name1) 
		$name1 = "untitled";
	$begin = ($begin) ? date("Y-m-d H:i:s", strToTime($begin)) : NULL;
	$end = ($end) ? date("Y-m-d H:i:s", strToTime($end)) : NULL;
	if(!$url)
		$url = slug($name1);
	
	// check that the desired URL is valid
	// URL is valid if it is not the same as any of its siblings
	// siblings are all the children of any of this object's direct
	// parents (object could be linked elsehwere -- any updated
	// URL cannot conflict with those siblings, either)
	$siblings = $oo->siblings($uu->id);
	$valid_url = true;
	foreach($siblings as $s_id)
	{
		$valid_url = ($url != $oo->get($s_id)["url"]);
		if(!$valid_url)
			break;
	}
	?><div class="self-container">
		<p><a href="<? echo $browse_url; ?>"><?php echo $name; ?></a></p><?
	if($valid_url)
	{
		// check for differences
		foreach($vars as $var)
		{
			if($item[$var] != $$var)
				$arr[$var] = $$var ? "'".$$var."'" : "null";
		}
	
		// update if modified
		$updated = false;
		if($arr)
		{
			$arr["modified"] = "'".date("Y-m-d H:i:s")."'";
			$updated = $oo->update($uu->id, $arr);
		}

		// process new media
		$updated = (process_media($uu->id) || $updated);
		
		// delete media
		// check to see if $rr->deletes exists (isset) 
		// because if checkbox is unchecked that variable "doesn't exist" 
		// although the expected behaviour is for it to exist but be null.
		if(isset($rr->deletes))
		{
			foreach($rr->deletes as $key => $value)
			{
				$m = $rr->medias[$key];
				$mm->deactivate($m);
				$updated = true;
			}
		}
	
		// update caption, weight, rank  
		$num_captions = sizeof($rr->captions);
		if (sizeof($rr->medias) < $num_captions)
			$num_captions = sizeof($rr->medias);
	
		for ($i = 0; $i < $num_captions; $i++) 
		{
			unset($m_arr);
			$m_id = $rr->medias[$i];
			$caption = addslashes($rr->captions[$i]);
			$rank = addslashes($rr->ranks[$i]);

			$m = $mm->get($m_id);
			if($m["caption"] != $caption)
				$m_arr["caption"] = "'".$caption."'";
			if($m["rank"] != $rank)
				$m_arr["rank"] = "'".$rank."'";

			if($m_arr)
			{
				$arr["modified"] = "'".date("Y-m-d H:i:s")."'";
				$updated = $mm->update($m_id, $m_arr);
			}
		}
			// Job well done?
			if($updated)
			{
			?><p>Record successfully updated.</p><?
			}
			else
			{
			?><p>Nothing was edited, therefore update not required.</p><?
			}
	}
	else
	{
		?><p>Record not updated, <a href="<? echo $js_back; ?>">try again</a>.</p><?
	}
	?></div><?
} 
?></div>
</div>