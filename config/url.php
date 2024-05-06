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
	// $this->urls, $this->url, $this->ids, $this->id are not set

	function __construct($urls=null) {

		global $oo;

		if ($urls === null) {
    		// get script url and request url
       	 	$script = explode('/', rtrim($_SERVER['SCRIPT_NAME'], '/'));
    		$urls = explode('/', rtrim( strtok($_SERVER['REQUEST_URI'],"?"), '/'));
    		// compare and shift until they diverge
    		while(isset($script[0]) && isset($urls[0]) && $script[0] == $urls[0]) {
                array_shift($script);
        		array_shift($urls);
    		}
    	}

		// check that the object that this URL refers to exists
		try {
			$ids = $oo->urls_to_ids($urls);
		} catch(Exception $e) {
            // nothing
        }                

		if (sizeof($ids) !== sizeof($urls) ||
		    sizeof($ids) == 1 && empty($ids[0]))
			$ids = null;

        /*
            three possible results for $id

            1. $id = '[1..x]'       valid url
            2. $id = '0'            home url
            3. $id = null           invalid
        */    

        $id = $ids ? end($ids) : (count($urls) == 0 ? '0' : null);

		$this->urls = $urls;
		$this->url = end($urls);
		$this->ids = $ids;
		$this->id = $id;
		$this->url_str = implode($urls);
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
