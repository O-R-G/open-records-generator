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
	public $name2;
	public $address1;
	public $address2;
	public $deck;
	public $body;
	public $notes;
	public $begin;
	public $end;
	public $url;
	public $rank;
	public $head;
	public $city;
	public $state;
	public $zip;

	// link
	public $wires_toid;

	public $m; // media id
	public $medias; // array
	public $types;
	public $captions;
	public $ranks;
	public $deletes;

	public $uploads;

	function __construct()
	{
		$this->page = basename($_SERVER['PHP_SELF'], ".php");

		// post variables
		$vars = array(	'name1', 'name2', 'head', 'city', 'state', 'zip', 'address1', 'address2', 'deck', 'body', 'notes', 'begin', 'end', 'url', 'rank',
						'medias', 'types', 'captions', 'ranks', 'deletes',
						'submit', 'action',
						'wires_toid',
						'uploads');

		foreach($vars as $v)
			$this->$v = $_POST[$v];
	}
}

?>
