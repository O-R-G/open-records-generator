<?
$browse_url = $admin_path.'browse/'.$uu->urls();

$urlIsValid = true;
// return false if object not updated,
// else, return true
function update_object(&$old, &$new, $siblings, $vars)
{
	global $oo;
	global $urlIsValid;

	// set default name if no name given
	if(!$new['name1'])
		$new['name1'] = "untitled";

	// add a sort of url break statement for urls that are already in existence
	// (and potentially violate our new rules?)
    // urldecode() is for query strings, ' ' -> '+'
    // rawurldecode() is for urls, ' ' -> '%20'
	$url_updated = rawurldecode($old['url']) != $new['url'];

	if($url_updated)
	{
		// slug-ify url
		if($new['url'])
			$new['url'] = slug($new['url']);

		// if the slugified url is empty,
		// or the original url field is empty,
		// slugify the name of the object
		if(empty($new['url']))
			$new['url'] = slug($new['name1']);

		// make sure url doesn't clash with urls of siblings
		$s_urls = array();
		foreach($siblings as $s_id)
			$s_urls[] = $oo->get($s_id)['url'];

		$urlIsValid = validate_url($new['url'], $s_urls);
		if( !$urlIsValid )
			$new['url'] = valid_url($new['url'], strval($old['id']), $s_urls);
	}
	// deal with dates
	if(!empty($new['begin']))
	{
		$dt = strtotime($new['begin']);
		$new['begin'] = date($oo::MYSQL_DATE_FMT, $dt);
	}

	if(!empty($new['end']))
	{
		$dt = strtotime($new['end']);
		$new['end'] = date($oo::MYSQL_DATE_FMT, $dt);
	}

	// check for differences
	$arr = array();
	foreach($vars as $v)
		if($old[$v] != $new[$v])
			$arr[$v] = $new[$v] ?  "'".$new[$v]."'" : "null";

	$updated = false;
	if(!empty($arr))
	{
		$updated = $oo->update($old['id'], $arr);
	}

	return $updated;
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
if ($rr->action != "update" && $uu->id)
{
	// get existing image data
	$medias = $oo->media($uu->id);
	$num_medias = count($medias);

	// add associations to media arrays:
	// $medias[$i]["file"] is url of media file
	// $medias[$i]["display"] is url of display file (diff for pdfs)
	// $medias[$i]["type"] is type of media (jpg, gif, pdf, mp4, mp3)
	for($i = 0; $i < $num_medias; $i++)
	{
		$m_padded = "".m_pad($medias[$i]['id']);
		$medias[$i]["fileNoPath"] = '/media/'.$m_padded.".".$medias[$i]["type"];
		$medias[$i]["file"] = $media_path.$m_padded.".".$medias[$i]["type"];
		if ($medias[$i]["type"] == "pdf")
			$medias[$i]["display"] = $admin_path."media/pdf.png";
		else if ($medias[$i]["type"] == "mp4")
			$medias[$i]["display"] = $admin_path."media/mp4.png";
		else if ($medias[$i]["type"] == "mp3")
			$medias[$i]["display"] = $admin_path."media/mp3.png";
		else
			$medias[$i]["display"] = $medias[$i]["file"];
	}
	$form_url = $admin_path."edit/".$uu->urls();
// object contents
?><div id="form-container">
		<div class="self">
			<a href="<? echo $browse_url; ?>"><? echo $name; ?></a>
		</div>
		<!-- <form
			method="post"
			enctype="multipart/form-data"
			action="<? echo $form_url; ?>"
		> -->
			<div class="form">
				<script>
				var default_editor_mode = '<?= $default_editor_mode; ?>';
				function link(name) {
						var linkURL = prompt('Enter a URL:', 'http://');
						if (linkURL === null || linkURL === "") {
							return;
						}

						document.execCommand('createlink', false, linkURL);
				}

				function addListeners(name) {
					document.getElementById(name + '-html').addEventListener('click', function(e) {resignImageContainer(name);}, false);
					document.getElementById(name + '-bold').addEventListener('click', function(e) {resignImageContainer(name);}, false);
					document.getElementById(name + '-italic').addEventListener('click', function(e) {resignImageContainer(name);}, false);
					document.getElementById(name + '-link').addEventListener('click', function(e) {resignImageContainer(name);}, false);
					document.getElementById(name + '-indent').addEventListener('click', function(e) {resignImageContainer(name);}, false);
					document.getElementById(name + '-reset').addEventListener('click', function(e) {resignImageContainer(name);}, false);
				}

				function resignImageContainer(name) {
					var imagecontainer = document.getElementById(name + '-imagecontainer');
					if (imagecontainer.style.display === 'block') {
						imagecontainer.style.display = 'none';
					}
				}
				function image(name) {
					var imagecontainer = document.getElementById(name + '-imagecontainer');
					var imagebox = document.getElementById(name + '-imagebox');
					// toggle image box
					if (imagecontainer.style.display !== 'block') {
						imagecontainer.style.display = 'block';
					} else {
						imagecontainer.style.display = 'none';
					}
				}

				function showToolBar(name) {
					hideToolBars();
					var tb = document.getElementById(name + '-toolbar');
					tb.style.display = 'block';
				}

				function hideToolBars() {
					var tbs = document.getElementsByClassName('toolbar');
					Array.prototype.forEach.call(tbs, function(tb) { tb.style.display = 'none'});

					var ics = document.getElementsByClassName('imagecontainer');
					Array.prototype.forEach.call(ics, function(ic) { ic.style.display = 'none'});
				}

				function commitAll() {
					var names = <?
						$textnames = [];
						foreach($vars as $var) {
							if($var_info["input-type"][$var] == "textarea") {
								$textnames[] = $var;
							}
						}
						echo '["' . implode('", "', $textnames) . '"]'
						?>;

					for (var i = 0; i < names.length; i++) {
						commit(names[i]);
					}
				}
				function commit(name) {
					var editable = document.getElementById(name + '-editable');
					var textarea = document.getElementById(name + '-textarea');
					if (editable.style.display === 'block') {
						var html = editable.innerHTML;
						textarea.value = html;    // update textarea for form submit
					} else {
						var html = textarea.value;
						editable.innerHTML = html;    // update editable
						textarea.value = editable.innerHTML;
					}
				}

				function showrich(name) {
					var bold = document.getElementById(name + '-bold');
					var italic = document.getElementById(name + '-italic');
					var link = document.getElementById(name + '-link');
					var indent = document.getElementById(name + '-indent');
					var reset = document.getElementById(name + '-reset');
					var image = document.getElementById(name + '-image');
					var imagecontainer = document.getElementById(name + '-imagecontainer');
					var html = document.getElementById(name + '-html');
					var txt = document.getElementById(name + '-txt');
					var editable = document.getElementById(name + '-editable');
					var textarea = document.getElementById(name + '-textarea');

					textarea.style.display = 'none';
					editable.style.display = 'block';

					html.style.display = 'block';
					txt.style.display = 'none';

					bold.style.visibility = 'visible';
					italic.style.visibility = 'visible';
					indent.style.visibility = 'visible';
					reset.style.visibility = 'visible';
					link.style.visibility = 'visible';
					image.style.visibility = 'visible';

					var html = textarea.value;
					editable.innerHTML = html;    // update editable
				}

				function sethtml(name, editorMode = 'regular') {
					var bold = document.getElementById(name + '-bold');
					var italic = document.getElementById(name + '-italic');
					var link = document.getElementById(name + '-link');
					var indent = document.getElementById(name + '-indent');
					var reset = document.getElementById(name + '-reset');
					var image = document.getElementById(name + '-image');
					var imagecontainer = document.getElementById(name + '-imagecontainer');
					var html = document.getElementById(name + '-html');
					var txt = document.getElementById(name + '-txt');
					var editable = document.getElementById(name + '-editable');
					var textarea = document.getElementById(name + '-textarea');

					textarea.style.display = 'block';
					editable.style.display = 'none';

					html.style.display = 'none';
					txt.style.display = 'block';

					bold.style.visibility = 'hidden';
					italic.style.visibility = 'hidden';
					indent.style.visibility = 'hidden';
					reset.style.visibility = 'hidden';
					link.style.visibility = 'hidden';
					image.style.visibility = 'hidden';
					imagecontainer.style.display = 'none';

					var html = editable.innerHTML;
					textarea.value = pretty(html);    // update textarea for form submit
					if(editorMode == 'regular')
						window.scrollBy(0, textarea.getBoundingClientRect().top); // scroll to the top of the textarea
				}

				function resetViews(name, editorMode = 'regular') {
					commitAll();
					var names = <?
						$textnames = [];
						foreach($vars as $var) {
							if($var_info["input-type"][$var] == "textarea") {
								$textnames[] = $var;
							}
						}
						echo '["' . implode('", "', $textnames) . '"]'
						?>;

					
					if(editorMode == 'regular')
					{
						for (var i = 0; i < names.length; i++) {
							if (!(name && name === names[i]))
								showrich(names[i]);
						}
					}
					else if(editorMode == 'html')
					{
						for (var i = 0; i < names.length; i++) {
							if (!(name && name === names[i]))
								sethtml(names[i], default_editor_mode);
						}
					}
						
					
				}

				// pretifies html (barely) by adding two new lines after a </div>
				function pretty(str) {
					while(str.charCodeAt(0) == '9' || str.charCodeAt(0) == '10'){
						str = str.substring(1, str.length);
					}
			        // return (str + '').replace(/(?<=<\/div>)(?!\n)/gi, '\n\n');
                    return str;
				}

				function getSelectionText() {
				    var text = "";
				    if (window.getSelection) {
				        text = window.getSelection().toString();
				    } else if (document.selection && document.selection.type != "Control") {
				        text = document.selection.createRange().text;
				    }
				    return text;
				}

				function indent(name){
	                document.execCommand('formatBlock',false,'blockquote');
                }

				function reset(name){
	                document.execCommand('formatBlock',false,'div');
	                document.execCommand('removeFormat',false,'');
                }

				// add "autosave functionality" every 5 sec
				// setInterval(function() {
				// 	commitAll();
				// }, 5000);
				</script>
				<?php
				// show object data
				foreach($vars as $var)
				{
				?><div class="field">
					<div class="field-name"><? echo $var_info["label"][$var]; ?></div>
					<div><?
						if($var_info["input-type"][$var] == "textarea")
						{

                        // ** start experimental minimal wysiwig toolbar **

                        ?>

						<div id="<?echo $var;?>-toolbar" class="toolbar dontdisplay">
							<?php if ($user == 'admin'): ?>
								<a id="<? echo $var; ?>-html" class='right' href="#null" onclick="sethtml('<? echo $var; ?>', default_editor_mode);">html</a>
								<a id="<? echo $var; ?>-txt" class='right dontdisplay' href="#null" onclick="showrich('<? echo $var; ?>');">rtf</a>
							<?php endif; ?>
							<a id="<? echo $var; ?>-bold" class='' href="#null" onclick="document.execCommand('bold',false,null);">bold</a>
                            <a id="<? echo $var; ?>-italic" class='' href="#null" onclick="document.execCommand('italic',false,null);">italic</a> 
                            <a id="<? echo $var; ?>-indent" class='' href="#null" onclick="indent('<? echo $var; ?>');">indent</a>
                            <a id="<? echo $var; ?>-reset" class='' href="#null" onclick="reset('<? echo $var; ?>');">&nbsp;&times;&nbsp;</a>
                            &nbsp;
                            <a id="<? echo $var; ?>-link" class='' href="#null" onclick="link('<? echo $var; ?>');">link</a>
							<a id="<? echo $var; ?>-image" class='' href="#null" onclick="image('<? echo $var; ?>');">image</a>
							<div id="<?echo $var; ?>-imagecontainer" class='imagecontainer dontdisplay' style="background-color: #999;">
								<span style="color: white;">insert an image...</span>
								<div id="<? echo $var; ?>-imagebox" class='imagebox'>
									<?
										for($i = 0; $i < $num_medias; $i++) {
											if ($medias[$i]["type"] != "pdf" && $medias[$i]["type"] != "mp4" && $medias[$i]["type"] != "mp3") {
												echo '<div class="image-container" id="'. m_pad($medias[$i]['id']) .'-'. $var .'"><img src="'. $medias[$i]['display'] .'"></div>';
												echo '<script>
												document.getElementById("'. m_pad($medias[$i]['id']) .'-'. $var .'").onclick = (function() {
													// closure for variable issue
													return function() {
														document.getElementById("'. $var .'-imagecontainer").style.display = "none";
														document.getElementById("'. $var .'-editable").focus();
														document.execCommand("insertImage", 0, "'. $medias[$i]['fileNoPath'] .'");
													}
												})();
												</script>';
											}
										}
									?>
									</div>
							</div>
						</div>

						<?php if ($user == 'guest'): ?>
							<div name='<? echo $var; ?>' class='large editable' contenteditable='false' id='<? echo $var; ?>-editable' onclick="" style="display: block;">
						<?php else: ?>
							<div name='<? echo $var; ?>' class='large editable' contenteditable='true' id='<? echo $var; ?>-editable' onclick="showToolBar('<? echo $var; ?>'); resetViews('<? echo $var; ?>', default_editor_mode);" style="display: block;">
						<?php endif; ?>
						<?
                            if($item[$var])
                                echo $item[$var];
                        ?></div>

			<textarea name='<? echo $var; ?>' class='large dontdisplay' id='<? echo $var; ?>-textarea' onclick="showToolBar('<? echo $var; ?>'); resetViews('<? echo $var; ?>', default_editor_mode);" onblur="" style="display: none;" form="edit-form"><?
                            if($item[$var])
                                echo htmlentities($item[$var]);
                        ?></textarea>

						<script>
							addListeners('<?echo $var; ?>');
							<? 
							if($user == 'admin' && $default_editor_mode == 'html') { ?>
								sethtml('<? echo $var; ?>', default_editor_mode);
							<? } ?>
						</script>
						<?
						// ** end minimal wysiwig toolbar **
						}
						elseif($var == "url")
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo rawurldecode($item[$var]); ?>'
								onclick="hideToolBars(); resetViews('', default_editor_mode);"
								<?php if ($user == 'guest'): ?>
									disabled = "disabled"
								<?php endif; ?>
								form="edit-form"
						><?
						}
						else
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo htmlspecialchars($item[$var], ENT_QUOTES); ?>'
								onclick="hideToolBars(); resetViews('', default_editor_mode);"
								<?php if ($user == 'guest'): ?>
									disabled = "disabled"
								<?php endif; ?>
								form="edit-form"
						><?
						}
					?></div>
				</div><?
				}
				// show existing images
				for($i = 0; $i < $num_medias; $i++)
				{
					$im = str_pad($i+1, 2, "0", STR_PAD_LEFT);
				?><div class="existing-image">
					<div class="field-name">Image <? echo $im; ?></div>
					<div class='preview'>
						<a href="<? echo $medias[$i]['file']; ?>" target="_blank">
							<img src="<? echo $medias[$i]['display']; ?>">
						</a>
					</div>
					<textarea name="captions[]" onclick="hideToolBars(); resetViews('', default_editor_mode);" form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
					><?
						echo $medias[$i]["caption"];
					?></textarea>
					<span>rank</span>
					<select name="ranks[<? echo $i; ?>]" form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
						><?
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
							form="edit-form"
							<?php if ($user == 'guest'): ?>
								disabled = "disabled"
							<?php endif; ?>
						>
					delete image</label>
					<input
						type="hidden"
						name="medias[<? echo $i; ?>]"
						value="<? echo $medias[$i]['id']; ?>"
						form="edit-form"
					>
					<input
						type="hidden"
						name="types[<? echo $i; ?>]"
						value="<? echo $medias[$i]['type']; ?>"
						form="edit-form"
					>
				</div><?php
				}
				// upload new images
				if ($user != 'guest') {
					for($j = 0; $j < $max_uploads; $j++)
					{
						$im = str_pad(++$i, 2, "0", STR_PAD_LEFT);
					?><div class="image-upload">
						<span class="field-name">Image <? echo $im; ?></span>
						<span>
							<input type="file" name="uploads[]" form="edit-form">
						</span>
						<!--textarea name="captions[]"><?php
								echo $medias[$i]["caption"];
						?></textarea-->
					</div><?php
					}
				} ?>
				<div class="button-container">
					<input
						type='hidden'
						name='action'
						value='update'
						form="edit-form"
					>
					<input
						type='button'
						name='cancel'
						value='Cancel'
						onClick="<? echo $js_back; ?>"
						form="edit-form"
					>
					<input
						type='submit'
						name='submit'
						value='Update Object'
						onclick='commitAll();'
						form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
					>
				</div>
			</div>
		<!-- </form> -->
		<form
			method="post"
			enctype="multipart/form-data"
			action="<? echo $form_url; ?>"
			id="edit-form"
		>
		</form>
	</div>
<?php
}
// THIS CODE NEEDS TO BE FACTORED OUT SO HARD
// basically the same as what is happening in add.php
else
{
	$new = array();
	// objects
	foreach($vars as $var)
	{
		$new[$var] = addslashes($rr->$var);
		$item[$var] = addslashes($item[$var]);
	}
	$siblings = $oo->siblings($uu->id);
	$updated = update_object($item, $new, $siblings, $vars);

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
    if (is_array($rr->captions)) {
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

			if(isset($m_arr))
			{
				$arr["modified"] = "'".date("Y-m-d H:i:s")."'";
				$updated = $mm->update($m_id, $m_arr);
			}
		}
    }

	?><div class="self-container"><?
		// should change this url to reflect updated url
		$urls = array_slice($uu->urls, 0, count($uu->urls)-1);
		$u = implode("/", $urls);
		$url = $admin_path."browse/";
		if(!empty($u))
			$url.= $u."/";
		$url.= $new['url'];
		?><p><a href="<? echo $url; ?>"><?php echo $new['name1']; ?></a></p><?
	// Job well done?
	if($updated)
	{
	?><p>Record successfully updated.</p><?
		if(!$urlIsValid)
		{
		?><p>*** The url of this record has been set to '<?= $new['url']; ?>' because of a conflict with another record. ***</p><?
		}
	}
	else
	{
	?><p>Nothing was edited, therefore update not required.</p><?
	}
	?></div><?
}
?></div>
</div>
