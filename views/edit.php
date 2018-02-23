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
			<div class="form">
				<script>
				var active = '';

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
					active = name;
					var tb = document.getElementById(name + '-toolbar');
					tb.style.display = 'block';
				}

				function hideToolBars() {
					active = '';
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
					if (textarea.style.display !== 'block') {
						var html = editable.innerHTML;
						textarea.value = html;    // update textarea for form submit
					} else {
						var html = textarea.value;
						editable.innerHTML = html;    // update editable
					}
				}

				function showrich(name) {
					var bold = document.getElementById(name + '-bold');
					var italic = document.getElementById(name + '-italic');
					var link = document.getElementById(name + '-link');
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
					link.style.visibility = 'visible';
					image.style.visibility = 'visible';

					var html = textarea.value;
					editable.innerHTML = html;    // update editable
				}

				function sethtml(name) {
					var bold = document.getElementById(name + '-bold');
					var italic = document.getElementById(name + '-italic');
					var link = document.getElementById(name + '-link');
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
					link.style.visibility = 'hidden';
					image.style.visibility = 'hidden';
					imagecontainer.style.display = 'none';

					var html = editable.innerHTML;
					textarea.value = pretty(html);    // update textarea for form submit
					window.scrollBy(0, textarea.getBoundingClientRect().top); // scroll to the top of the textarea
				}

				function resetViews() {
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
						if (names[i] !== active)
							showrich(names[i]);
					}
				}

				window.onscroll = function() {
					if (active !== '') {
						var tb = document.getElementById(active + '-toolbar');
						var editable = document.getElementById(active + '-editable');
						if (tb && tb.style.display == 'block') {
							// console.log(tb.height);
							 if (editable.getBoundingClientRect().top - tb.offsetHeight < 0 && editable.getBoundingClientRect().bottom > 0) {
								 tb.style.position = 'fixed';
								 tb.style.top = 0;
							 } else {
								 tb.style.position = '';
								 tb.style.top = '';
							 }
						}
					}
				}

				// pretifies html (barely) by adding a new line after a </div>
				function pretty(str) {
			    return (str + '').replace(/<\/div>(?!\n)/g, '</div>\n\n');
				}

				// add "autosave functionality" every 5 sec
				setInterval(function() {
					commitAll();
				}, 5000);
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
													<a id="<? echo $var; ?>-html" class='right' href="#null" onclick="sethtml('<? echo $var; ?>');">html</a>
													<a id="<? echo $var; ?>-txt" class='right dontdisplay' href="#null" onclick="showrich('<? echo $var; ?>');">done.</a>
													<a id="<? echo $var; ?>-bold" class='' href="#null" onclick="document.execCommand('bold',false,null);">bold</a>
	                        <a id="<? echo $var; ?>-italic" class='' href="#null" onclick="document.execCommand('italic',false,null);">italic</a>
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
																				document.execCommand("insertImage", 0, "'. $medias[$i]['display'] .'");
																			}
																		})();
																		</script>';
																	}
																}
															?>;
															</div>
													</div>
												</div>

												<div name='<? echo $var; ?>' class='large editable' contenteditable='true' id='<? echo $var; ?>-editable' onclick="showToolBar('<? echo $var; ?>'); resetViews();" onblur="commit('<? echo $var; ?>');"><?
                            if($item[$var])
                                echo $item[$var];
                        ?></div>

                        <textarea name='<? echo $var; ?>' class='large dontdisplay' id='<? echo $var; ?>-textarea' onclick="showToolBar('<? echo $var; ?>');" onblur="showrich('<? echo $var; ?>'); commit('<? echo $var; ?>');"><?
                            if($item[$var])
                                echo $item[$var];
                        ?></textarea>

												<script>
													addListeners('<?echo $var; ?>');
												</script>
												<?

                        // ** end minimal wysiwig toolbar **

						}
						elseif($var == "url")
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo urldecode($item[$var]); ?>'
								onclick="hideToolBars(); resetViews();"
						><?
						}
						else
						{
						?><input name='<? echo $var; ?>'
								type='<? echo $var_info["input-type"][$var]; ?>'
								value='<? echo htmlspecialchars($item[$var], ENT_QUOTES); ?>'
								onclick="hideToolBars(); resetViews();"
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
					<textarea name="captions[]" onclick="hideToolBars(); resetViews();"><?
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
						onclick='commitAll();'
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
