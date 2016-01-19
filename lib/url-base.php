<?
class URL_Base
{
	public $urls; 	// array of all object urls
	public $url; 	// this object's url
	public $url_str;
	public $back_url;
	public $ids; 	// array of all object ids
	public $id;		// this object's id

	// return a string of the current urls
	// defaults to empty string if none
	public function urls()
	{
		return implode("/", $this->urls);
	}
	
	// return a string of this object's parent url
	// ish. not sure if this works very well.
	// e.g, only works in browse, right?
	public function back()
	{
		$urls = $this->urls;
		array_pop($urls);
		return implode("/", $urls);
	}
}
?>