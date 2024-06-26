<?
/*---------------------------------------------------------
	class for interaction with the OBJECTS table

	fields
	+ blob
		- deck
		- body
		- notes
	+ int
		- id
		- active
		- rank
	+ text
		- address1
		- address2
	+ tinytext
		- name1
		- name2
		- city
		- state
		- zip
		- country
		- phone
		- fax
		- url
		- email
		- head
	+ datetime
		- created
		- modified
		- begin
		- end
		- date
---------------------------------------------------------*/
class Objects extends Model
{
	const table_name = "objects";
	
	// return the name of object with id $o
	public function name($o)
	{
		$item = $this->get($o);
		return $item["name1"];
	}
	
	// return the children of object with id $o
	public function children($o)
	{
		$fields = array("objects.*");
		$tables = array("objects", "wires");
		$where	= array("wires.fromid = '".$o."'",
						"wires.active = 1",
						"wires.toid = objects.id",
						"objects.active = '1'");
		$order 	= array("objects.rank", "objects.begin", "objects.end", "objects.name1");
        /* exception for ICA, applies globally */
        // $order 	= array("objects.rank", "objects.modified DESC", "objects.end", "objects.begin", "objects.name1");
    
		return $this->get_all($fields, $tables, $where, $order);
	}
	
	// returns: the ids of all children of object with id $o
	public function children_ids($o)
	{
		$fields = array("objects.id AS id",
						"objects.rank",
						"objects.name1");
		$tables = array("objects", "wires");
		$where	= array("wires.fromid = '".$o."'",
						"wires.active = 1",
						"wires.toid = objects.id",
						"objects.active = '1'");
        $order 	= array("objects.rank", "objects.begin", "objects.end", "objects.name1");
        $res = $this->get_all($fields, $tables, $where, $order);
		$ids = array();
		foreach($res as $r)
			$ids[] = $r['id'];

		return $ids;
	}
	
	public function children_ids_nav($o)
	{
		$fields = array("objects.*");
		$tables = array("objects", "wires");
		$where	= array("wires.fromid = '".$o."'",
						"wires.active = 1",
						"wires.toid = objects.id",
						"objects.active = '1'",
						"objects.name1 not like '.%'");
        $order 	= array("objects.rank", "objects.begin", "objects.end", "objects.name1");
		$res = $this->get_all($fields, $tables, $where, $order);
		$ids = array();
		foreach($res as $r)
			$ids[] = $r['id'];

		return $ids;
	}

    public function siblings($o)
	{
		global $db;
		$siblings = array();

        $sql = "SELECT wires.fromid FROM wires, objects 
                WHERE wires.toid = '" . $o . "' 
                AND ((objects.id = wires.fromid 
                AND objects.active = '1' )
                OR (wires.fromid = '0' AND objects.id = wires.toid))";
        $res = $db->query($sql);
        if(!$res)
			throw new Exception($db->error);
		$fromid_arr = array();
		while ($obj = $res->fetch_assoc())
			$fromid_arr[] = $obj['fromid'];
		$res->close();

		foreach($fromid_arr as $parent_id)
		{
			$this_siblings = $this->children_ids($parent_id);
			foreach($this_siblings as $key => $s_id)
			{
				if($s_id == $o)
					unset($this_siblings[$key]);
			}
			$this_siblings = array_values($this_siblings);
			$siblings = array_merge($siblings, $this_siblings);
		}
		return  $siblings;
	}
	
	// check that URL is of valid object here
	// throw 404 exception if not
	public function urls_to_ids($u)
	{
		$fromid = 0;
		$objects = array();
		for($i = 0; $i < count($u); $i++)
		{
			$fields = array("objects.id",
							"objects.name1");
			$tables = array("objects", "wires");
			$where 	= array("wires.fromid = '".$fromid."'",
							"wires.toid = objects.id",
							"objects.url = '".$u[$i]."'",
							"wires.active = '1'",
							"objects.active = '1'");
            $order 	= array("objects.rank", "objects.begin", "objects.end", "objects.name1");
			$tmp = $this->get_all($fields, $tables, $where, $order);
			if(!empty($tmp))
			{
				$fromid = $tmp[0]['id'];
				if(!$fromid)
					throw new Exception($i);
				$objects[] = $fromid;
			}
		}
		return $objects;
	}
	
	public function ids_to_urls($objects)
	{
		$u = array();
		for($i = 0; $i < count($objects); $i++)
		{
			$o = $this->get($objects[$i]);
			$u[] = $o['url'];
		}
		return $u;
	}
	
	// returns: the ids of all ancestors of object with id $o
	//
	// ancestors are obtained by traversing tree, 
	// going through in-order list of traversals,
	// recording potential parents,
	// breaking when $o is found,
	// reporting the actual parents at the time of finding
	// repeats this process through the entire tree array, in case
	// object is linked elsewhere

    /*  
        major performance issues on ica.art. the issue is:

            > $all = $this->traverse(0);

        which does not scale efficiently to the size of ica.art database
    */

    // added $force to ONLY call if absolutely required 
    // added $all (if available) to avoid traverse(0) again
	public function ancestors($o, $all = NULL, $force = FALSE)
	{
		$ancestors = array();
        if ($force) {
            if (!$all)
                $all = $this->traverse(0);
            $a = array();
            for($i = 0; $i < count($all); $i++)
            {
                if(end($all[$i]) == $o)
			    {
				    $d = count($all[$i]);
                    $ancestors = array_merge($ancestors, array_slice($a, 0, $d-1));
                }
                $d = count($all[$i]);
                $a[$d-1] = end($all[$i]);
            }
        }
		return array_unique($ancestors);
	}
	
	public function ancestors_single($root, $o)
	{
		$all = $this->traverse($root);
		$ancestors = array();
		$a = array();
		for($i = 0; $i < count($all); $i++)
		{
			if(end($all[$i]) == $o)
			{
				$d = count($all[$i]);
				$ancestors = array_slice($a, 0, $d-1);
			}
			$d = count($all[$i]);
			$a[$d-1] = end($all[$i]);
		}
		return $ancestors;
	}
	
	// returns: the ids of all descedants of object with id $o
	// children, grandchildren, etc
	public function descendants($o)
	{
		$desc = $this->traverse($o);
		$descendants = array();
		foreach($desc as $d)
			$descendants[] = end($d);
		return $descendants;
	}
	
	// return media attached to this object
	public function media($o)
	{
		$fields = array("*");
		$tables = array("media");
		$where 	= array("object = '".$o."'", 
						"active = '1'");
		$order 	= array("media.rank", "media.modified", "media.created", "media.id");
		
		return $this->get_all($fields, $tables, $where, $order);
	}
	
	public function media_ids($o)
	{
		$fields = array("id");
		$tables = array("media");
		$where 	= array("object = '".$o."'", 
						"active = '1'");
		$order 	= array("media.rank", "media.modified", "media.created", "media.id");
		$res = $this->get_all($fields, $tables, $where, $order);
		$ids = array();
		foreach($res as $r)
			$ids[] = $r['id'];

		return $ids;
	}
	
	// returns a list of objects $o can link to
	// $o cannot link to its children 
	// (because it is already linked to them) 
	// or any of its direct ancestors 
	// (because doing so would create a loop)        
    // optimized using mysql query in place of multiple array_merge()
    // get every record that is neither parent nor child nor itself
    // return only one record per object even if linked multiple times 
    // ie, multiple wires in database
	// public function link_list($o)
	// {	
    //     global $db;
    //     $id = $o;
	// 	$tab = '&nbsp;';
	// 	// ids_to_exclude: ancestors, self, children
	// 	$ids_to_exclude = array();
	// 	$sql_getAncestors = "WITH RECURSIVE cte (fromid) AS ( 
	// 		SELECT fromid FROM wires WHERE toid = '$id' AND active = '1' 
	// 		UNION ALL 
	// 		SELECT w.fromid FROM cte c JOIN wires w ON c.fromid = w.toid WHERE c.fromid != '0') 
	// 	SELECT * FROM cte";
	// 	$res = $db->query($sql_getAncestors);
	// 	while ($obj = $res->fetch_assoc())
	// 		$ids_to_exclude[] = $obj['fromid'];
	// 	$ids_to_exclude[] = $id;
	// 	$ids_to_exclude = array_merge($ids_to_exclude, $this->children_ids($id));
    //     return $this->traverse_recursive($id, $ids_to_exclude);
	// }
	public function get_ancestors_and_children($o)
	{	
        global $db;
        $id = $o;
		$tab = '&nbsp;';
		$output = array(
			'ancestors' => array(),
			'self' => $id,
			'children' => array()
		);
		$sql_getAncestors = "WITH RECURSIVE cte (fromid) AS ( 
			SELECT fromid FROM wires WHERE toid = '$id' AND active = '1' 
			UNION ALL 
			SELECT w.fromid FROM cte c JOIN wires w ON c.fromid = w.toid WHERE c.fromid != '0') 
		SELECT * FROM cte";
		$res = $db->query($sql_getAncestors);
		while ($obj = $res->fetch_assoc())
			$output['ancestors'][] = $obj['fromid'];
		$output['children'] = $this->children_ids($id);
		
        return $output;
	}
	// returns an array of [path] of objects rooted at $o
	// depth is equal to the length of each path array
	public function traverse($o)
	{
		static $path = array();
		$children_ids = $this->children_ids($o);
		$paths = array();
		
		if(count($path) > 0)
			$paths[] = $path;
		if(!empty($children_ids)) // make children return an empty array?
		{
			foreach($children_ids as $c)
			{
				$path[] = $c;
				$paths = array_merge($paths, $this->traverse($c));
				array_pop($path);
			}
		}
		return $paths;
	}
	// public function traverse_recursive($o, $excludes = array()){
	// 	global $db;
	// 	$id = $o;
	// 	$tab = '&nbsp;&nbsp;&nbsp;';
	// 	$excludes_command = empty($excludes) ? 'false' : 'IF(wires.toid IN (' . implode(',', $excludes) . '), true,false)';
		
	// 	$sql = "WITH RECURSIVE cte ( `toid`, `name1`, `indent`, `path_string`, `path`, `exclude`) AS ( 
	// 			SELECT wires.toid, objects.name1, CAST( '' AS CHAR(512) ), objects.name1, CAST( objects.id AS CHAR(512) ), $excludes_command FROM wires, objects WHERE objects.active = '1' AND wires.active = '1' AND objects.id = wires.toid AND wires.fromid = '0'
	// 			UNION ALL
	// 			SELECT wires.toid, objects.name1, CONCAT( cte.indent, '$tab' ), CONCAT( cte.path_string, ' > ', objects.name1 ), CONCAT( cte.path, ',', objects.id ), $excludes_command FROM cte INNER JOIN wires ON cte.toid = wires.fromid INNER JOIN objects ON wires.toid = objects.id AND wires.active = '1' AND objects.active = '1'
	// 		)
	// 		SELECT * FROM cte ORDER BY `path_string`";
	// 	$items = array();
	// 	$res = $db->query($sql);
	// 	while($obj = $res->fetch_assoc()){
	// 		$obj['path'] = explode(',', $obj['path']);
	// 		$items[] = $obj;
	// 	}
    //     return $items;
	// }
	public function traverse_recursive($root, $reference=false, $excludes = array()){
		global $db;
		
		$reference = $reference ? $reference : $root;
		$id = $root;
		$tab = '&nbsp;&nbsp;&nbsp;';
		$excludes_command = empty($excludes) ? 'false' : 'IF(wires.toid IN (' . implode(',', $excludes) . '), true,false)';
		$ancestors_and_children = $this->get_ancestors_and_children($reference);
		// var_dump($reference);
		$ancestors_str = '(' . implode(',', $ancestors_and_children['ancestors']) . ')';
		$children_str = '(' . implode(',', $ancestors_and_children['children']) . ')';
		// $roles_command = "IF(wires.toid = $reference, 'self', IF(wires.toid IN $ancestors_str, 'ancestor', IF(wires.toid)))";
		$role_command = "CASE WHEN wires.toid = $reference THEN 'self'";
		if(!empty($ancestors_and_children['ancestors'])) $role_command .= " WHEN wires.toid IN (" . implode(',', $ancestors_and_children['ancestors']) . ") THEN 'ancestor'";
		if(!empty($ancestors_and_children['children'])) $role_command .= " WHEN wires.toid IN (" . implode(',', $ancestors_and_children['children']) . ") THEN 'child'";
		$role_command .= " ELSE '' END";
		$sql = "WITH RECURSIVE cte ( `toid`, `name1`, `indent`, `path_string`, `path`, `role`, `exclude`) AS ( 
			SELECT wires.toid, objects.name1, CAST( '' AS CHAR(512) ), objects.name1, CAST( objects.id AS CHAR(512) ), $role_command, $excludes_command FROM wires, objects WHERE objects.active = '1' AND wires.active = '1' AND objects.id = wires.toid AND wires.fromid = '$id'
			UNION ALL
			SELECT wires.toid, objects.name1, CONCAT( cte.indent, '$tab' ), CONCAT( cte.path_string, ' > ', objects.name1 ), CONCAT( cte.path, ',', objects.id ), $role_command, $excludes_command FROM cte INNER JOIN wires ON cte.toid = wires.fromid INNER JOIN objects ON wires.toid = objects.id AND wires.active = '1' AND objects.active = '1'
		)
		SELECT * FROM cte ORDER BY `path_string`";
		$items = array();
		$res = $db->query($sql);
		while($obj = $res->fetch_assoc()){
			$obj['path'] = explode(',', $obj['path']);
			$items[] = $obj;
		}
        return $items;
	}
	// takes: a tree constructed by $oo->traverse()
	// returns; an associative array of depth, name, url
	public function nav_full($paths)
	{
		$urls = array();
		$prevd = 0;
		$nav = array();
		foreach($paths as $path)
		{
			$d = count($path);
			$o = $this->get($path[(count($path)-1)]);
			
			$pops = $prevd - $d + 1;
			$urls = array_slice($urls, 0, count($urls) - $pops);
			$urls[] = $o['url'];
			$url = implode("/", $urls);
		
			$nav[] = array('depth'=>$d, 'o'=>$o, 'url'=>$url);
			$prevd = $d;
		}
		return $nav;
	}
	
	// takes: 
	// returns:
	// if end($ids) is a leaf (has no siblings), then return the siblings with the
	// tree
	public function nav($ids, $root_id=0)
	{
		$nav = array();
		$pass = true;
		$top = $this->children_ids_nav($root_id);
		
		$ids = array_search($root_id, $ids) === FALSE ? $ids : array_slice($ids, 1);
		$root_index = array_search($root_id, $ids) === FALSE ? 0 : array_search($root_id, $ids);
		
		foreach($top as $t)
		{
			$o = $this->get($t);
			$d = $root_index+1;
			$urls = array($o['url']);
			$url = implode("/", $urls);			
			$nav[] = array('depth'=>$d, 'o'=>$o, 'url'=>$url);
			
			if(!empty($ids) && isset($ids[$root_index]) && $pass && $t == $ids[$root_index])
			{
				$pass = false; // short-circuit if statement

				$kids = $this->children_ids_nav(end($ids));
				if(empty($kids) && count($ids) > 1)
				{
					$kids = $this->children_ids_nav($ids[count($ids)-2]);
					array_pop($ids); // leaf is included in siblings
				}
				array_shift($ids);
				
				// show direct ancestors (and self, if children)
				foreach(array_slice($ids, $root_index) as $id)
				{
					$d++;
					$o = $this->get($id);
					$urls[] = $o['url'];
					$url = implode("/", $urls);
					$nav[] = array('depth'=>$d, 'o'=>$o, 'url'=>$url);
				}
				// show children, if no children, show self + siblings
				$d++;
				foreach($kids as $k)
				{	
					$o = $this->get($k);
					$urls[] = $o['url'];
					$url = implode("/", $urls);
					$nav[] = array('depth'=>$d, 'o'=>$o, 'url'=>$url);
					array_pop($urls);
				}
			}
		}
		return $nav;
	}
	
	function nav_helper($type, $id, $d, &$urls)
	{
		$o = $this->get($id);
		$urls[] = $o['url'];
		$url = implode("/", $urls);
		return array('type'=>$type, 'id'=>$id,'o'=>$o,'depth'=>$d,'url'=>$url);
	}
	
	public function nav_test($ids, $root=0)
	{
		$top = $this->children_ids($root);
		
	}
	
	// takes: an array of ids
	// returns: an array of arrays corresponding to a 
	// very specific traversal of the tree
	// all top-level nodes are returned
	// all 'parents' w/r/t to the array of ids are returned
	// if the last node in ids has children, children are also returned
	// if not, siblings are returned
	public function nav_clean($ids, $root=0)
	{
		$records = array();
		$top = $this->children_ids($root);
		$pass = true;
		$root_i = array_search($root, $ids);
		if($root_i === FALSE)
			$root_i = 0;
		else
			$root_i++;
		
		foreach($top as $t_id)
		{
			$d = 1;
			
			// urls to be appended to the beginning of each menu item
			$urls = array("de");
			// if this top-level object is an ancestor of the current obj		
			if($pass && $t_id == $ids[$root_i])
			{
				$pass = false; // short-circuit if-statement
				$s_id = array_pop($ids);
				
				// parents
				foreach(array_slice($ids, $root_i) as $p_id)
					$records[] = $this->nav_helper("parent", $p_id, $d++, $urls);
				
				$kids = $this->children_ids($s_id);
				// self + siblings
				if(empty($kids))
				{
					if(count($ids))
					{
						$siblings = $this->children_ids(end($ids));
						foreach($siblings as $sib)
						{
							if($sib == $s_id)
								$records[] = $this->nav_helper("self", $sib, $d, $urls);
							else
								$records[] = $this->nav_helper("sibling", $sib, $d, $urls);
							array_pop($urls);
						}
					}
					else
						$records[] = $this->nav_helper("self", $s_id, $d, $urls);
				}
				// self + kids
				else
				{
					$records[] = $this->nav_helper("self", $s_id, $d++, $urls);
					foreach($kids as $k_id)
					{
						$records[] = $this->nav_helper("child", $k_id, $d, $urls);
						array_pop($urls);
					}
				}
			}
			else
				$records[] = $this->nav_helper("top", $t_id, $d, $urls);
		}
		return $records;
	}
}
?>
