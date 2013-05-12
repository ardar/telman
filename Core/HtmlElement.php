<?php
abstract class HtmlElement implements IHtmlElement
{
	protected $_id;
	protected $_title;
	protected $_name;
	
	public function GetId()
	{
		return $this->_id;
	}
	
	public function GetName()
	{
		return $this->_name;
	}
	
	public function GetTitle()
	{
		return $this->_title;
	}
	
	public function __toString()
	{
		return $this->GetTitle()." ".$this->GetBody();
	}
}