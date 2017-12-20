<?php
/*
    class for dealing with URLs
*/

class URL extends URL_Base {

	// $this->urls = array of all object urls
	// $this->url = this object's url
	// $this->ids = array of all object ids
	// $this->id = this object's id
	// if no object selected,
	// $this->urls, $this->url, and $this->ids are not set
	// $this->id is 0

	function __construct($urls=null) {

		global $oo;

		if (!$urls) {
            		// get script url and request url
           	 	$script = explode('/', rtrim($_SERVER['SCRIPT_NAME'], '/'));
            		$urls = explode('/', rtrim($_SERVER['REQUEST_URI'], '/'));

            		// compare and shift until they diverge
            		while($script[0] == $urls[0]) {
		                array_shift($script);
                		array_shift($urls);
            		}
        	}

		// check that the object that this URL refers to exists
		try {
			$ids = $oo->urls_to_ids($urls);
		}
		catch(Exception $e) {
			$urls = array_slice($urls, 0, $e->getMessage());
			$ids = $oo->urls_to_ids($urls);
			// $loc = $host.implode("/".$base)."/".implode("/", $urls);
			// header("Location: ".$loc);
		}

		$id = $ids[count($ids)-1];
		if(!$id)
			$id = 0;
		if(sizeof($ids) == 1 && empty($ids[0]))
			unset($ids);

		$this->urls = $urls;
		$this->url = end($urls);
		$this->ids = $ids;
		$this->id = $id;
		$this->url_str = implode($urls);
		// $this->back_url =
	}

    public function parents()
    {
        global $oo;
        global $admin_path;
        $urls = $this->urls;
        $ids = $this->ids;
        $parents[] = "";

        for($i = 0; $i < count($urls)-1; $i++)
        {
            $parents[$i]['url'] = $admin_path."browse/";
            for($j = 0; $j < $i + 1; $j++)
            {
                $parents[$i]['url'].= $urls[$j];
                if($j < $i)
                    $parents[$i]['url'].= "/";
            }
            $parents[$i]["name"] = $oo->name($ids[$i]);
        }
        if($parents[0] == "")
            unset($parents);
        return $parents;
    }
}
?>
