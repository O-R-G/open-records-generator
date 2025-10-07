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
		if($old[$v] !== $new[$v])
			$arr[$v] = $new[$v] ?  "'".$new[$v]."'" : "null";

	$updated = false;
	if(!empty($arr))
	{
		$updated = $oo->update($old['id'], $arr);
	}

	return $updated;
}
function appendLinebreakToBr($str){
	$pattern = array('/<br>(?:\r\n)?/');
	$replacement = array("<br>\r\n");
	$output = preg_replace($pattern, $replacement, $str);
	return $output;
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
			<a href="<?php echo $a_url; ?>"><?php echo $ancestor["name1"]; ?></a>
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
			<a href="<?php echo $browse_url; ?>"><?php echo $name; ?></a>
		</div>
		<!-- <form
			method="post"
			enctype="multipart/form-data"
			action="<?php echo $form_url; ?>"
		> -->
			<div class="form">
				<script>
				var default_editor_mode = '<?php echo $settings['default_editor_mode']; ?>';
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
					// console.log('commitAll');
					var names = <?
						$textnames = [];
						foreach($vars as $var) {
							if($var_info["input-type"][$var] == "textarea") {
								$textnames[] = $var;
							}
						}
						echo '["' . implode('", "', $textnames) . '"]'
						?>;

					for (let i = 0; i < names.length; i++) {
						commit(names[i]);
					}
				}
				function commit(name) {
					// if(name == 'deck')
					// 	console.log('commit');
					var editable = document.getElementById(name + '-editable');
					var textarea = document.getElementById(name + '-textarea');
					wrapFirstChildWithDiv(editable);
					if (editable.style.display === 'block') {
						// editable.innerHTML = pretty(editable.innerHTML);
						var html = editableIsEmpty(editable.innerHTML) ? '' : pretty(editable.innerHTML);
						textarea.value = html;    // update textarea for form submit
					} else {
						var html = textarea.value;
						editable.innerHTML = html;    // update editable
						if(!editableIsEmpty(editable.innerHTML)){
							textarea.value = editable.innerHTML;
						}
						else
							textarea.value = '';
					}
				}

				function showrich(name) {
					// console.log('showrich()');
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

				function sethtml(name, editorMode = 'rich_text') {
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
					if(editableIsEmpty(editable.innerHTML, true)) {
						editable.innerHTML = '';
					}
					else wrapFirstChildWithDiv(editable);
					var html = editable.innerHTML;
					textarea.value = pretty(html);    // update textarea for form submit
					if(editorMode == 'rich_text')
						window.scrollBy(0, textarea.getBoundingClientRect().top); // scroll to the top of the textarea
				}

				function resetViews(name, editorMode = 'rich_text') {
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

					if(editorMode == 'rich_text')
					{
						for (var i = 0; i < names.length; i++) {
							if (!(name && name === names[i])){
								showrich(names[i]);
							}
						}
					}
					else if(editorMode == 'html')
					{
						for (var i = 0; i < names.length; i++) {
							if (!(name && name === names[i])){
								sethtml(names[i], default_editor_mode);
							}
								
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

				function indent(name){
	                document.execCommand('formatBlock',false,'blockquote');
                }

				function reset(name){
	                document.execCommand('formatBlock',false,'div');
	                document.execCommand('removeFormat',false,'');
                }

                function getCaretPosition(editableDiv) {
					var caretPos = 0,
						sel, range;
					if (window.getSelection) {
						sel = window.getSelection();
						if (sel.rangeCount) {
							range = sel.getRangeAt(0);
							if (range.commonAncestorContainer.parentNode == editableDiv) {
								// console.log(range);
								caretPos = range.endOffset;
							}
						}
					} else if (document.selection && document.selection.createRange) {
						range = document.selection.createRange();
						if (range.parentElement() == editableDiv) {
							var tempEl = document.createElement("span");
							editableDiv.insertBefore(tempEl, editableDiv.firstChild);
							var tempRange = range.duplicate();
							tempRange.moveToElementText(tempEl);
							tempRange.setEndPoint("EndToEnd", range);
							caretPos = tempRange.text.length;
						}
					}
					// console.log(caretPos);
					return caretPos;
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
				
                function handleEditablePaste(e, editable) {
                	var clipboardData, pastedData;

					// Stop data actually being pasted into div
					e.stopPropagation();
					e.preventDefault();

					// Get pasted data via clipboard API
					clipboardData = e.clipboardData || window.clipboardData;
					pastedData = clipboardData.getData('text/plain');
					document.execCommand('insertText', false, pastedData);
					let h = divToBr(editable.innerHTML);
					if(h !== false) editable.innerHTML = h;
				}
				function strContainsOnlySpaces(str, report = false){
					/* check if a string contains only any type of space */
					return str.match(/^\s*$/s) !== null;
				}
				function editableIsEmpty(html, report = false){
					/* contains elements other than empty divs? */
					let output = html.replace(/<div>^\s*$<\/div	>/s, '');
					
					return strContainsOnlySpaces(output);
				}
				function wrapFirstChildWithDiv(editable){
					return;
					/* wrap non-div element(s) at the beginning with a div */
					if(!editable.firstChild || (editable.firstChild.nodeType === 1 && editable.firstChild.tagName.toLowerCase() === 'div')) return;
					// return;
					let div = document.createElement('DIV');
					let fc = editable.firstChild;
					while(fc.nodeType == 3 || (fc.nodeType == 1 && (fc.tagName.toLowerCase() !== 'div'))) {
						// if(editable.getAttribute('name') == 'deck') console.log(fc);
						if(fc.nodeType == 3 && strContainsOnlySpaces(fc.textContent)){
							editable.removeChild(fc);
						}
						else
							div.appendChild(fc);

						fc = editable.firstChild;
						if(!fc) break;
					}
					if(strContainsOnlySpaces(div.innerHTML)) return;
					else if(editable.firstChild) editable.insertBefore(div, editable.firstChild);
					else editable.appendChild(div);
				}
				/* divToBr */
				function divToBr(str, useP=false){
					/*
						1. collapse opening tags at the very beginning 
						/^(?:<div>)+/g => ''

						2. collapse closing tags at the very end
						/(?:<\/div>)+$/g => ''
						
						3. collapse closing tags that follow br
						/<br>(?:<\/div>)+/g => '<br>'

						4. replace the tag groups containg one or more tags with br
						/(?:<div>|<\/div>)+/g => '<br>'                
					*/
					str = pretty(str);
					if(strContainsOnlySpaces(str)) return '';
					if(str.indexOf('<div>') === -1) return false;
					console.log('start divToBr . . . ')
					let output = str;
					let search = [
						{
							'pattern': /(\r\n|\n|\r)/g,
							'replacement': ''
						},
						{
							'pattern': /<div(\s.*?)>(.*?)<\/div>/g,
							'replacement': '<section$1>$2</section>'
						},
						{
							'pattern': /^(?:<div>)+/g,
							'replacement': ''
						},
						{
							'pattern': /(?:<\/div>)+$/g,
							'replacement': ''
						},
						{
							'pattern': /<br>(?:<\/div>)+(?:<div>)*/g,
							'replacement': '<br>'
						},
						{
							'pattern': /(?:<div>|<\/div>)+/g,
							'replacement': '<br>'
						},
						{
							'pattern': /<section(\s.*?)>(.*?)<\/section>/g,
							'replacement': useP ? '<p$1>$2</p>' : '<div$1>$2</div>'
						}
					];
					for(let i = 0; i <  search.length; i++)
						output = output.replaceAll(search[i]['pattern'], search[i]['replacement']);

					return output;
				}
				function sliceFilename(fn){
					let dotPos = fn.lastIndexOf('.');
					return {
						'ext': fn.substring(dotPos + 1),
						'name': fn.substring(0, dotPos)
					};
				}
				function renderPreviewItem(file, idx=''){
					let output = document.createElement('div');
					output.className = 'to-upload-image';
					let p = document.createElement('div');
					p.className = 'preview';
					let ta = document.createElement('textarea');
					ta.name = 'captions[]';
					let rm = document.createElement('div');
					rm.className = 'remove-upload';
					rm.innerHTML = '&times;';
					let fn = document.createElement('div');
					fn.className = 'fieldName';
					fn.innerText = 'Upload';
					if(idx !== '') fn.innerText += idx > 8 ? ' ' + (idx + 1) : ' 0' + (idx + 1); 
					let img = document.createElement('img');
					let filename = sliceFilename(file.name);
					if (filename.ext == "pdf")
						img.src = "<?php echo $admin_path; ?>media/pdf.png";
					else if (filename.ext == "mp4")
						img.src = "<?php echo $admin_path; ?>media/mp4.png";
					else if (filename.ext == "mp3")
						img.src = "<?php echo $admin_path; ?>media/mp3.png";
					else {
						img.onload = function(){
							URL.revokeObjectURL(img.src);  // no longer needed, free memory
						}
						img.src = URL.createObjectURL(file); // set src to blob url

						let reader = new FileReader();
						reader.readAsDataURL(file); 
						reader.onloadend = function() {
							img.setAttribute('base64', reader.result);
						}
						
					}
					p.appendChild(img);
					output.appendChild(fn);
					output.appendChild(p);

					return output;
				}
				</script>
				<?php
				// show object data
				foreach($vars as $var)
				{
				?><div class="field">
					<div class="field-name"><?php echo $var_info["label"][$var]; ?></div>
					<div><?
						if($var_info["input-type"][$var] == "textarea")
						{

                        // ** start experimental minimal wysiwig toolbar **

                        ?>

						<div id="<?echo $var;?>-toolbar" class="toolbar dontdisplay">
							<?php if ($user == 'admin'): ?>
								<a id="<?php echo $var; ?>-html" class='right' href="#null" onclick="sethtml('<?php echo $var; ?>', default_editor_mode);">html</a>
								<a id="<?php echo $var; ?>-txt" class='right dontdisplay' href="#null" onclick="showrich('<?php echo $var; ?>');">rtf</a>
							<?php endif; ?>
							<a id="<?php echo $var; ?>-bold" class='' href="#null" onclick="document.execCommand('bold',false,null);">bold</a>
                            <a id="<?php echo $var; ?>-italic" class='' href="#null" onclick="document.execCommand('italic',false,null);">italic</a> 
                            <a id="<?php echo $var; ?>-indent" class='' href="#null" onclick="indent('<?php echo $var; ?>');">indent</a>
                            <a id="<?php echo $var; ?>-reset" class='' href="#null" onclick="reset('<?php echo $var; ?>');">&nbsp;&times;&nbsp;</a>
                            &nbsp;
                            <a id="<?php echo $var; ?>-link" class='' href="#null" onclick="link('<?php echo $var; ?>');">link</a>
							<a id="<?php echo $var; ?>-image" class='' href="#null" onclick="image('<?php echo $var; ?>');">image</a>
							<div id="<?echo $var; ?>-imagecontainer" class='imagecontainer dontdisplay' style="background-color: #999;">
								<span style="color: white;">insert an image...</span>
								<div id="<?php echo $var; ?>-imagebox" class='imagebox'>
									<?
										for($i = 0; $i < $num_medias; $i++) {
											if ($medias[$i]["type"] != "pdf" && $medias[$i]["type"] != "mp4" && $medias[$i]["type"] != "mp3") {
												?><div class="image-container" id="<?php echo m_pad($medias[$i]['id']) .'-'. $var; ?>"><img src="<?php echo $medias[$i]['display']; ?>"></div>
												<script>
												document.getElementById("<?php echo m_pad($medias[$i]['id']) .'-'. $var; ?>").onclick = (function() {
													// closure for variable issue
													return function() {
														let v = '<?php echo $var; ?>';
														document.getElementById(v + "-imagecontainer").style.display = "none";
														document.getElementById(v + "-editable").focus();
														let captionAttr = '<?php echo preg_replace(array('/\r\n/', '/\s+/', '/"/', '/\'/'), array('. ', ' ', '&quot;', '&apos;'), trim($medias[$i]['caption'])); ?>';
														if(captionAttr !== '') captionAttr = 'caption="' + captionAttr + '"';
														let caption = '<?php echo preg_replace(array('/\r\n/', '/\s+/', '/"/', '/\'/'), array('<br> ', ' ',  '&quot;', '&apos;'), trim($medias[$i]['caption'])); ?>';
														if(caption !== '') caption = '<blockquote class="caption">' + caption + '</blockquote><br>';
														let html = '<br><img src="<?php echo $medias[$i]['fileNoPath']; ?>" ' + captionAttr + '><br>'+caption;
														document.execCommand("insertHTML", 0, html);
														setTimeout(function(){
															document.getElementById(v + "-editable").blur();
														}, 0);
														
													}
												})();
												</script><?php
											}
										}
									?>
									</div>
							</div>
						</div>

						<?php if ($user == 'guest'): ?>
							<div name='<?php echo $var; ?>' class='large editable' contenteditable='false' id='<?php echo $var; ?>-editable' onclick="" style="display: block;">
						<?php else: ?>
							<div name='<?php echo $var; ?>' class='large editable' contenteditable='true' onpaste="handleEditablePaste(event, this);"  id='<?php echo $var; ?>-editable' onfocus="showToolBar('<?php echo $var; ?>'); resetViews('<?php echo $var; ?>', default_editor_mode);" style="display: block;">
						<?php endif; 
							if($item[$var] && trim($item[$var])) echo appendLinebreakToBr(trim($item[$var]));
						?></div>
                        <textarea name='<?php echo $var; ?>' class='large dontdisplay' id='<?php echo $var; ?>-textarea' onfocus="showToolBar('<?php echo $var; ?>'); resetViews('<?php echo $var; ?>', default_editor_mode);" onblur="" style="display: none;" form="edit-form"><?
                            if($item[$var] && trim($item[$var])) echo htmlentities(appendLinebreakToBr(trim($item[$var])));
                        ?></textarea>
						<script>
							addListeners('<?echo $var; ?>');
							// cleanEditableText(document.querySelector('div[name="<?php echo $var; ?>"]'));
							<?php 
							if($user == 'admin' && $settings['default_editor_mode'] == 'html') { ?>
								sethtml('<?php echo $var; ?>', default_editor_mode);
							<?php } ?>
						</script>
						<?
						// ** end minimal wysiwig toolbar **
						}
						elseif($var == "url")
						{
						?><input name='<?php echo $var; ?>'
								type='<?php echo $var_info["input-type"][$var]; ?>'
								value='<?php echo rawurldecode($item[$var]); ?>'
								onclick="hideToolBars(); resetViews('', default_editor_mode);"
								<?php if ($user == 'guest'): ?>
									disabled = "disabled"
								<?php endif; ?>
								form="edit-form"
						><?
						}
						else
						{
						?><input name='<?php echo $var; ?>'
								type='<?php echo $var_info["input-type"][$var]; ?>'
								value='<?php echo ($item[$var] && !empty($item[$var])) ? htmlspecialchars($item[$var], ENT_QUOTES) : ""; ?>'
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
				// check if the column 'metadata' exist in media
				$hasMediaMetadata = false;
				$sql_check_metadata = "SELECT COUNT(*) 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = 'media'
					AND COLUMN_NAME = 'metadata'";
				$result = $db->query($sql_check_metadata);
				if ($result) {
					// fetch_row() returns a numeric array; [0] is the COUNT(*)
					$count = $result->fetch_row()[0];

					if ($count > 0) {
						$hasMediaMetadata = true;
					}
					$result->free();   // optional, free result set
				} else {
					echo "Query error: " . $db->error;
				}
				for($i = 0; $i < $num_medias; $i++)
				{
					$im = str_pad($i+1, 2, "0", STR_PAD_LEFT);
				?><div class="existing-image">
					<div class="field-name">Image <?php echo $im; ?></div>
					<div class='preview'>
						<a href="<?php echo $medias[$i]['file']; ?>" target="_blank">
							<img src="<?php echo $medias[$i]['display']; ?>">
						</a>
					</div>
					<textarea name="captions[]" onclick="hideToolBars(); resetViews('', default_editor_mode);" form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
					><?
						echo $medias[$i]["caption"];
					?></textarea>
					<?php if($hasMediaMetadata): ?>
					<div class="field-name">Metadata</div>
					<textarea name="metadatas[]" onclick="hideToolBars(); resetViews('', default_editor_mode);" form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
					><?
						echo $medias[$i]["metadata"];
					?></textarea>
					<?php endif; ?>
					<span>rank</span>
					<select name="ranks[<?php echo $i; ?>]" form="edit-form"
						<?php if ($user == 'guest'): ?>
							disabled = "disabled"
						<?php endif; ?>
						><?
						for($j = 1; $j <= $num_medias; $j++)
						{
							if($j == $medias[$i]["rank"])
							{
							?><option selected value="<?php echo $j; ?>"><?
								echo $j;
							?></option><?php
							}
							else
							{
							?><option value="<?php echo $j; ?>"><?
								echo $j;
							?></option><?php
							}
						}
					?></select>
					<label>
						<input
							type="checkbox"
							name="deletes[<?php echo $i; ?>]"
							form="edit-form"
							<?php if ($user == 'guest'): ?>
								disabled = "disabled"
							<?php endif; ?>
						>
					delete image</label>
					<input
						type="hidden"
						name="medias[<?php echo $i; ?>]"
						value="<?php echo $medias[$i]['id']; ?>"
						form="edit-form"
					>
					<input
						type="hidden"
						name="types[<?php echo $i; ?>]"
						value="<?php echo $medias[$i]['type']; ?>"
						form="edit-form"
					>
				</div><?php
				}
				// upload new images
				if ($user != 'guest') {
					for($j = 0; $j < $settings['max_uploads']; $j++)
					{
						$im = str_pad(++$i, 2, "0", STR_PAD_LEFT);
					?><div class="image-upload">
						<span class="field-name">Image <?php echo $im; ?></span>
						<span>
							<input type="file" name="uploads[]" form="edit-form">
						</span>
					</div><?php
					}
					/*
					?>
					<div id="tbu-container"></div>
					<div class="image-upload">
						<span class="field-name">Images</span>
						<span>
							<input type="file" name="uploads[]" form="edit-form" multiple>
						</span>
					</div>
					<?php
					*/
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
						onClick="<?php echo $js_back; ?>"
						form="edit-form"
					>
					<input
						type='submit'
						value='Update Object'
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
			action="<?php echo $form_url; ?>"
			id="edit-form"
		>
		</form>
		<script>
			let input = document.querySelector('input[name="uploads[]"]');
			let tbu = document.getElementById('tbu-container');
			if(input && tbu)
			{
				input.addEventListener('change', function(){
					tbu.innerHTML = '';
					console.log('change');
					for(let i = 0; i < this.files.length; i++) {
						let item = renderPreviewItem(this.files[i], i);
						console.log(item);
						// if(item === false){
						// 	console.log('not webm/webp!');
						// 	alert('You can only upload webp and webm here.');
						// 	input.value = null;
						// 	tbu.innerHTML = '';
						// 	break;
						// }
						tbu.appendChild(item);
					}
				});

			}
			

			let editables = document.querySelectorAll('div[contenteditable="true"]');
			for(let i = 0; i < editables.length; i++) {
				let h = divToBr(editables[i].innerHTML);
				if(h !== false) editables[i].innerHTML = h;
				editables[i].addEventListener('focusout', function(e){
					console.log(editables[i].getAttribute('name') + ' focusout');
					if(!e.relatedTarget || e.relatedTarget.parentNode.parentNode !== editables[i].parentNode)
					{
						console.log('calling divToBr()');
						let h = divToBr(editables[i].innerHTML);
						if(h !== false) editables[i].innerHTML = h;
					}
				});
			}

			let editForm = document.getElementById('edit-form');
			// let submitBtn = document.querySelector('input[type="submit"]');
			editForm.addEventListener('submit', function(e){
				e.preventDefault();
				commitAll();
				let editables = document.querySelectorAll('div[contenteditable="true"]');
				let pass = true;
				for(let i = 0; i < editables.length; i++) {
					let n = editables[i].getAttribute('name');
					let ta = document.getElementById(n + '-textarea');
					if(!ta) {
						alert(name + ' doesnt have textarea');
						pass = false;
					}
					else if(ta.value != pretty(editables[i].innerHTML)) {
						alert(name + ': values of editable and textarea mismatch');
						console.log(ta.value);
						console.log(editables[i].innerHTML);
						pass = false;
					}
				}
				if(pass) editForm.submit();
			});
		</script>
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
		$item[$var] = empty($item[$var]) ? 'NULL' : addslashes($item[$var]);
	}
	// die();
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
	if (is_array($rr->metadatas)) {
	    $num_metadatas = sizeof($rr->metadatas);
	    if (sizeof($rr->medias) < $num_metadatas)
		    $num_metadatas = sizeof($rr->medias);
		for ($i = 0; $i < $num_metadatas; $i++)
		{
			unset($m_arr);
			$m_id = $rr->medias[$i];
			$metadata = addslashes($rr->metadatas[$i]);
			$rank = addslashes($rr->ranks[$i]);

			$m = $mm->get($m_id);
			if($m["metadata"] != $metadata)
				$m_arr["metadata"] = "'".$metadata."'";
			if($m["rank"] != $rank)
				$m_arr["rank"] = "'".$rank."'";

			if(isset($m_arr))
			{
				$arr["modified"] = "'".date("Y-m-d H:i:s")."'";
				$updated = $mm->update($m_id, $m_arr);
			}
		}
    }
	if(file_exists(__DIR__ . '/../lib/post-processing.php'))
		require_once(__DIR__ . '/../lib/post-processing.php');
	?><div class="self-container"><?
		// should change this url to reflect updated url
		$urls = array_slice($uu->urls, 0, count($uu->urls)-1);
		$u = implode("/", $urls);
		$url = $admin_path."browse/";
		if(!empty($u))
			$url.= $u."/";
		$url.= $new['url'];
		?><p><a href="<?php echo $url; ?>"><?php echo $new['name1']; ?></a></p><?
	// Job well done?
	if($updated)
	{
	?><p>Record successfully updated.</p><?
		if(!$urlIsValid)
		{
		?><p>*** The url of this record has been set to '<?php echo $new['url']; ?>' because of a conflict with another record. ***</p><?
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
