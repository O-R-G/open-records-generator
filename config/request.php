<?php
// wrapper class for http post variables
// should this be custom per view? perhaps
class Request
{
	public $page = ''; 	// is this variable even used?
		
	// add, edit, delete, link
	public $submit;
	public $action;
	
	// add, edit
	public $name1;
	public $deck;
	public $body;
	public $notes;
	public $begin;
	public $end;
	public $url;
	public $rank;
	
	// link
	public $wires_toid;
	
	public $m; // media id
	public $medias; // array
	public $types;
	public $captions;
	public $ranks;
	public $deletes;
	
	function __construct()
	{
		$this->page = basename($_SERVER['PHP_SELF'], ".php");
		
		// post variables
		$vars = array(	'name1', 'deck', 'body', 'notes', 'begin', 'end', 'url', 'rank',
						'medias', 'types', 'captions', 'ranks', 'deletes',
						'submit', 'action',
						'wires_toid');

		foreach($vars as $v)	
			$this->$v = $_POST[$v];
	}
}

?>