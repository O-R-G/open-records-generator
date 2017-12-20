<?
$browse_url = $admin_path.'browse/'.$uu->urls();

$vars = array("name1", "deck", "body", "notes",  "url", "rank", "begin", "end");

$var_info = array();

$var_info["input-type"] = array();
$var_info["input-type"]["name1"] = "text";
$var_info["input-type"]["deck"] = "textarea";
$var_info["input-type"]["body"] = "textarea";
$var_info["input-type"]["notes"] = "textarea";
$var_info["input-type"]["begin"] = "text";
$var_info["input-type"]["end"] = "text";
$var_info["input-type"]["url"] = "text";
$var_info["input-type"]["rank"] = "text";

$var_info["label"] = array();
$var_info["label"]["name1"] = "Name";
$var_info["label"]["deck"] = "Synopsis";
$var_info["label"]["body"] = "Detail";
$var_info["label"]["notes"] = "Notes";
$var_info["label"]["begin"] = "Begin";
$var_info["label"]["end"] = "End";
$var_info["label"]["url"] = "URL Slug";
$var_info["label"]["rank"] = "Rank";

// return false if object not updated,
// else, return true
function update_object(&$old, &$new, $siblings, $vars)
{
	global $oo;

	// set default name if no name given
	if(!$new['name1'])
		$new['name1'] = "untitled";

	// add a sort of url break statement for urls that are already in existence
	// (and potentially violate our new rules?)
	$url_updated = urldecode($old['url']) != $new['url'];

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
		<form
			method="post"
			enctype="multipart/form-data"
			action="<? echo $form_url; ?>"
		>
			<div class="form"><?php
				// show object data
				foreach($vars as $var)
				{
				?><div class="field">
					<div class="field-name"><? echo $var_info["label"][$var]; ?></div>
					<div><?
						if($var_info["input-type"][$var] == "textarea")
						{

                        // ** start experimental minimal wysiwig toolbar **

                        ?><script>
                        function link(name) {
                            var linkURL = prompt('Enter a URL:', 'http://');
														if (linkURL === null || linkURL === "") {
															return;
														}

														document.execCommand('createlink', false, linkURL);
                        }
												function image(name) {
													var imagebox = document.getElementById(name + '-imagebox');
													// toggle image box
													if (imagebox.style.display !== 'flex') {
														imagebox.style.display = 'flex';

														if (imagebox.firstChild) {
															return;
														}

														var existingImages = document.getElementsByClassName('existing-image');
														var imgs = [];

														for (var i = 0; i < existingImages.length; i++) {
															var images = existingImages[i].getElementsByTagName('img');
															for (var j = 0; j < images.length; j++) {
																// check if pdf placeholder
																if (images[j].src.indexOf('pdf.png') === -1)
																	imgs.push(images[j].src);
															}
														}

														for (var i = 0; i < imgs.length; i++) {
															var imgsrc = imgs[i];

															var image = document.createElement('img');
															image.setAttribute('src', imgsrc);

															// check if video placeholder
															if (image.naturalHeight > 10) {
																var container = document.createElement('div');
																container.setAttribute('class','image-container');
																container.appendChild(image);
																imagebox.appendChild(container);

																container.onclick = (function() {
																	// closure for variable issue
																	var imgSource = imgsrc;
																	return function() {
																		imagebox.style.display = 'none';
																		document.getElementById(name + '-editable').focus();
																		document.execCommand('insertImage', 0, imgSource);
																	}
																})();
															}
														}
													} else {
														imagebox.style.display = 'none';
													}
												}

                        function edit(name) {
                            var edit = document.getElementById(name + '-edit');
                            var bold = document.getElementById(name + '-bold');
                            var italic = document.getElementById(name + '-italic');
                            var link = document.getElementById(name + '-link');
														var image = document.getElementById(name + '-image');
														var imagebox = document.getElementById(name + '-imagebox');
                            var htmltxt = document.getElementById(name + '-htmltxt');
                            var editable = document.getElementById(name + '-editable');
                            var textarea = document.getElementById(name + '-textarea');
                            if (editable.contentEditable == 'true') {
                                editable.contentEditable = 'false';
                                bold.style.visibility = 'hidden';
                                italic.style.visibility = 'hidden';
                                link.style.visibility = 'hidden';
																image.style.visibility = 'hidden';
																imagebox.style.display = 'none';
                                htmltxt.style.visibility = 'hidden';
                                editable.style.backgroundColor = '#FFF';
                                edit.innerHTML='edit...';

																if (textarea.style.display != 'block') {
																	var html = editable.innerHTML;
	                                textarea.value = html;    // update textarea for form submit
																} else {
																	togglehtml(name);
																}
                            } else {
                                editable.contentEditable = 'true';
                                bold.style.visibility = 'visible';
                                italic.style.visibility = 'visible';
                                link.style.visibility = 'visible';
																image.style.visibility = 'visible';
																// imagebox.style.visibility = 'visible';
                                htmltxt.style.visibility = 'visible';
                                editable.style.backgroundColor = '#FFF';
                                edit.innerHTML='done.';
                            }
                        }
                        function togglehtml(name) {
														var bold = document.getElementById(name + '-bold');
														var italic = document.getElementById(name + '-italic');
														var link = document.getElementById(name + '-link');
														var image = document.getElementById(name + '-image');
														var imagebox = document.getElementById(name + '-imagebox');
                            var htmltxt = document.getElementById(name + '-htmltxt');
                            var editable = document.getElementById(name + '-editable');
                            var textarea = document.getElementById(name + '-textarea');
                            if (textarea.style.display == 'block') {
                                textarea.style.display = 'none';
                                editable.style.display = 'block';
                                htmltxt.innerHTML='html';

																bold.style.visibility = 'visible';
                                italic.style.visibility = 'visible';
                                link.style.visibility = 'visible';
																image.style.visibility = 'visible';
																// imagebox.style.display = 'block';

																var html = textarea.value;
                                editable.innerHTML = html;    // update editable
                            } else {
                                textarea.style.display = 'block';
                                editable.style.display = 'none';
                                htmltxt.innerHTML='text';

																bold.style.visibility = 'hidden';
                                italic.style.visibility = 'hidden';
                                link.style.visibility = 'hidden';
																image.style.visibility = 'hidden';
																imagebox.style.display = 'none';

																var html = editable.innerHTML;
																textarea.value = html;    // update textarea for form submit
                            }
                        }

                        </script>

												<div class="right">
													<a id="<? echo $var; ?>-htmltxt" class='hide' href="#null" onclick="togglehtml('<? echo $var; ?>');" style="margin-right:6px;">html</a>
													<a id="<? echo $var; ?>-edit" class='' href="#null" onclick="edit('<? echo $var; ?>');">edit...</a>
												</div>
                        <a id="<? echo $var; ?>-bold" class='hide' href="#null" onclick="document.execCommand('bold',false,null);">bold</a>
                        <a id="<? echo $var; ?>-italic" class='hide' href="#null" onclick="document.execCommand('italic',false,null);">italic</a>
                        <a id="<? echo $var; ?>-link" class='hide' href="#null" onclick="link('<? echo $var; ?>');">link</a>
												<a id="<? echo $var; ?>-image" class='hide' href="#null" onclick="image('<? echo $var; ?>');">image</a>

												<div id="<? echo $var; ?>-imagebox" class='imagebox dontdisplay'></div>
                        <div name='<? echo $var; ?>' class='large editable' contenteditable='false' id='<? echo $var; ?>-editable'><?
                            if($item[$var])
                                echo $item[$var];
                        ?></div>

                        <textarea name='<? echo $var; ?>' class='large dontdisplay' id='<? echo $var; ?>-textarea'><?
                            if($item[$var])
                                echo $item[$var];
                        ?></textarea>
												<?

                        // ** end minimal wysiwig toolbar **

						}
						elseif($var == "url")
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo urldecode($item[$var]); ?>'
						><?
						}
						else
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo htmlspecialchars($item[$var], ENT_QUOTES); ?>'
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
					<textarea name="captions[]"><?
						echo $medias[$i]["caption"];
					?></textarea>
					<span>rank</span>
					<select name="ranks[<? echo $i; ?>]"><?
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
					$im = str_pad(++$i, 2, "0", STR_PAD_LEFT);
				?><div class="image-upload">
					<span class="field-name">Image <? echo $im; ?></span>
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
	}
	else
	{
	?><p>Nothing was edited, therefore update not required.</p><?
	}
	?></div><?
}
?></div>
</div>
