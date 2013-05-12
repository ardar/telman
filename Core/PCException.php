<?php
class PCException extends Exception
{
	protected $_session = null;
	protected $_nextlocation = "";
	
	public function __construct($message, $nextlocation=LOCATION_BACK, $code=null, $previous=null)
	{
		parent::__construct($message, $code, $previous);
		$this->_nextlocation = $nextlocation;
		$this->_session = $_SESSION;
	}
	
	public function GetNextLocation()
	{
		return $this->_nextlocation;
	}
	
	public function __toString()
	{
		return parent::__toString()." session:".$this->_session;
	}
}