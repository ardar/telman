<?php
class DataField
{
	const DISPEDIT = 1;
	const DISPADD = 2;
	const DISPLIST = 4;
	const DISPALL = 7;
	
	public $Name;
	public $Title;
	public $Type;
	public $SortBy;
	public $Display;//all,edit,add,list
	
	public function __construct($name, $title, $type, $display=null, $sortby=null)
	{
		$this->Name = $name;
		$this->Title = $title;
		$this->Type = $type;
		$this->SortBy = $sortby;
		$this->Display = $display;
	}
}