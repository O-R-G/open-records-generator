<?
$document_root = $_SERVER["DOCUMENT_ROOT"];
$config = $document_root."/open-records-generator/config/config.php";
require_once($config);
$db = db_connect("main");
$oo = new Objects();
$ww = new Wires();
require_once($document_root.'/static/php/function.php');

$data = file_get_contents( "php://input" );
$data = json_decode($data, true);
$s_urls = $data['s_urls'];

$new = array();
foreach($data as $key => $d){
	if($key != 'id' && $key != 'action' && $key != 's_urls' && $key != 'parent_id'){
		if(empty($d))
			$this_value = "null";
		else
		{
			if($data['action'] == 'insert' && $key == 'name1')
				$this_value = '.' . htmlentities(addslashes($d));
			else if($key == 'body')
				$this_value = addslashes($d);
			else
				$this_value = htmlentities(addslashes($d));
			$this_value = "'".$this_value."'";
		}
		
		$new[$key] = $this_value;
	}
}
if($data['action'] == 'update'){
	$id = $oo->update($data['id'], $new);
}
else if($data['action'] == 'insert'){

	$new_url = slug($data['name1']);
	$new['url'] = "'".$new_url."'";
	$id = $oo->insert($new);

	$urlIsValid = validate_url($new_url, $s_urls);
	if( !$urlIsValid )
	{
		$url = valid_url($new_url, strval($id), $s_urls);
		$new['url'] = "'".$url."'";
		$oo->update($id, $new);
	}
	if($id)
		$ww->create_wire($data['parent_id'], $id);
}

header('Content-Type: application/json');
echo json_encode($id);
?>