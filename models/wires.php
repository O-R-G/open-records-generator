<?
/*--------------------------------------------------------- 
	class for interaction with the WIRES table 
	
	fields
	+ blob
		- notes
	+ int
		- id
		- active
		- fromid
		- toid
	+ float
		- weight
	+ datetime
		- created
		- modified
---------------------------------------------------------*/
class Wires extends Model
{
	const table_name = "wires";
	
	public function get_wire($fromid, $toid)
	{
		$fields = array("*");
		$tables = array(static::table_name);
		$where 	= array("fromid = '".$fromid."'",
						"toid = '".$toid."'",
						"active = '1'");
		$order 	= array();
		$limit 	= '1';
		
		$wire = $this->get_all($fields, $tables, $where, $order, $limit);
		return $wire[0];
	}
	
	public function get_wires_to($toid)
	{
		$fields = array("*");
		$tables = array(static::table_name);
		$where  = array("toid = '".$toid."'", 
						"active = '1'");
		$order = array();
		$wires = $this->get_all($fields, $tables, $where, $order);
		return $wires;
	}
	
	public function get_wires_from($fromid)
	{
		$fields = array("*");
		$tables = array(static::table_name);
		$where  = array("fromid = '".$fromid."'", 
						"active = '1'");
		$order = array();
		$wires = $this->get_all($fields, $tables, $where, $order);
		return $wires;
	}
	
	public function delete_wire($fromid, $toid)
	{
		$item = $this->get_wire($fromid, $toid);
		$w = $item["id"];
		return $this->deactivate($w);
	}
	
	public function create_wire($fromid, $toid)
	{
		$dt = date("Y-m-d H:i:s");
// 		$arr["created"] = "'".$dt."'";
// 		$arr["modified"] = "'".$dt."'";
		$arr["fromid"] = $fromid;
		$arr["toid"] = $toid;
		return $this->insert($arr);
	}
}

?>