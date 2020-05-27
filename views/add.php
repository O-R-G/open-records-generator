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

// for use on add.php
// return false if process fails
// (siblings must not have same url slug as object)
// return id of new object on success
function insert_object(&$new, $siblings)
{
	global $oo;

	// set default name if no name given
	if(!$new['name1'])
		$new['name1'] = 'untitled';

	// slug-ify url
	if($new['url'])
		$new['url'] = slug($new['url']);

	if(empty($new['url']))
		$new['url'] = slug($new['name1']);

	// make sure url doesn't clash with urls of siblings
	$s_urls = array();
	foreach($siblings as $s_id)
		$s_urls[] = $oo->get($s_id)['url'];

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

	// make mysql happy with nulls and such
	foreach($new as $key => $value)
	{
		if($value)
			$new[$key] = "'".$value."'";
		else
			$new[$key] = "null";
	}

	$id = $oo->insert($new);

	// need to strip out the quotes that were added to appease sql
	$u = str_replace("'", "", $new['url']);
	$url = valid_url($u, strval($id), $s_urls);
	if($url != $u)
	{
		$new['url'] = "'".$url."'";
		$oo->update($id, $new);
	}

	return $id;
}

?><div id="body-container">
	<div id="body" class="centre"><?
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
		?><div class="self-container">
			<div class="self">
				<a href="<? echo $browse_url; ?>"><? echo $name; ?></a>
			</div>
		</div><?


		// show form
		if($rr->action != "add")
		{
			$form_url = $admin_path."add";
			if($uu->urls())
				$form_url.="/".$uu->urls();
		?><div id="form-container">
			<div class="self">You are adding a new object.</div>
			<form
				enctype="multipart/form-data"
				action="<? echo $form_url; ?>"
				method="post"
			>
				<div class="form"><?
				// object data
				foreach($vars as $var)
				{
					?><div class="field">
						<div class="field-name"><? echo $var_info["label"][$var]; ?></div>
						<div><?
						if($var_info["input-type"][$var] == "textarea")
						{
						?><textarea name='<? echo $var; ?>' class='large'></textarea><?
						}
						else
						{
						?><input
							name='<? echo $var; ?>'
							type='<? echo $var_info["input-type"][$var]; ?>'
						><?
						}
						?></div>
					</div><?
				}
				//  upload new images
				for ($j = 0; $j < $max_uploads; $j++)
				{
					?><div class="field">
						<span class="field-name">Image <? echo $j+1; ?></span>
						<span>
							<input type='file' name='uploads[]'>
							<!-- textarea name="captions[]" class="caption"></textarea -->
						</span>
					</div><?
				}
				?></div>
				<div class="button-container">
					<input
						type='hidden'
						name='action'
						value='add'
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
						value='Add Object'
					>
				</div>
			</form>
		</div><?
		}
		// process form
		else
		{
			$f = array();
			// objects
			foreach($vars as $var)
				$f[$var] = addslashes($rr->$var);
			$siblings = $oo->children_ids($uu->id);
			$toid = insert_object($f, $siblings);
			if($toid)
			{
				// wires
				$ww->create_wire($uu->id, $toid);
				// media
				process_media($toid);
			?><div>Record added successfully.</div><?
			}
			else
			{
			?><div>Record not created, please <a href="<? echo $js_back; ?>">try again.</a></div><?
			}
		}
	?></div>
</div>
