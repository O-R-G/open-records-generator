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

		return $this->get_all($fields, $tables, $where, $order, $descending);
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
        $res = $this->get_all($fields, $tables, $where, $order, $descending);
		$ids = array();
		foreach($res as $r)
			$ids[] = $r['id'];

		return $ids;
	}
	
	public function children_ids_nav($o, $descending = FALSE)
	{
		$fields = array("objects.*");
		$tables = array("objects", "wires");
		$where	= array("wires.fromid = '".$o."'",
						"wires.active = 1",
						"wires.toid = objects.id",
						"objects.active = '1'",
						"objects.name1 not like '.%'");
        $order 	= array("objects.rank", "objects.begin", "objects.end", "objects.name1");
		$res = $this->get_all($fields, $tables, $where, $order, $limit, $descending);
		$ids = array();
		foreach($res as $r)
			$ids[] = $r['id'];

		return $ids;
	}

	public function siblings($o)
	{
		$all = $this->traverse(0);
		$siblings = array();
		
		for($i = 0; $i < count($all); $i++)
		{
			if(end($all[$i]) == $o)
			{
				$p = (count($all[$i]) > 1) ? $all[$i][count($all[$i])-2] : 0;
				$s = $this->children_ids($p);
				$siblings = array_merge($siblings, $s);
			}
		}
		$siblings = array_unique($siblings);
		$k = array_search($o, $siblings);
		unset($siblings[$k]);
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
			$fromid = $tmp[0]['id'];
			if(!$fromid)
				throw new Exception($i);
			$objects[] = $fromid;
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
	// object is linked elswhere
	public function ancestors($o)
	{
		$all = $this->traverse(0);
		$ancestors = array();
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
		$order 	= array("rank", "modified", "created", "id");
		
		return $this->get_all($fields, $tables, $where, $order);
	}
	
	public function media_ids($o)
	{
		$fields = array("id");
		$tables = array("media");
		$where 	= array("object = '".$o."'", 
						"active = '1'");
		$order 	= array("rank", "modified", "created", "id");
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
	public function unlinked_list($o)
	{	
		$all = $this->traverse(0);
		$all_ids = array();
		foreach($all as $a)
			$all_ids[] = end($a);
		
		$exclude_ids = $this->children_ids($o);
		$exclude_ids[] = $o;
		$exclude_ids = array_merge($exclude_ids , $this->ancestors($o));
		$include_ids = array_unique(array_diff($all_ids, $exclude_ids));
		return $include_ids;
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
	public function nav($ids, $root_id=0, $descending = FALSE)
	{
		$nav = array();
		$pass = true;
		
		$top = $this->children_ids_nav($root_id);
		$root_index = array_search($root_id, $ids);
		if($root_index === FALSE)
			$root_index = 0;
		else
			$root_index++;
		
		foreach($top as $t)
		{
			$o = $this->get($t);
			$d = $root+1;
			$urls = array($o['url']);
			$url = implode("/", $urls);			
			$nav[] = array('depth'=>$d, 'o'=>$o, 'url'=>$url);
			
			if($pass && $t == $ids[$root_index])
			{
				$pass = false; // short-circuit if statement

				$kids = $this->children_ids_nav(end($ids), $descending);
				if(empty($kids) && count($ids) > 1)
				{
					$kids = $this->children_ids_nav($ids[count($ids)-2], $descending);
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
