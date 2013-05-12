<?php

class HttpRequest implements IRequest
{
	protected $_headers;
	protected $_inputs;
	
	public function __construct()
	{
		global  $_COOKIE,$_SESSION,$_SERVER,$_FILES,$_GET,$_POST;
		$this->_headers = array();
		$this->_inputs = array();
		$this->_inputs[INPUT_GET] = OpenDev::daddslashes($_GET);
		$this->_inputs[INPUT_POST] = OpenDev::daddslashes($_POST);
		$this->_inputs[INPUT_COOKIE] = OpenDev::daddslashes($_COOKIE);
		$this->_inputs[INPUT_SESSION] = OpenDev::daddslashes($_SESSION);
		$this->_inputs[INPUT_SERVER] = OpenDev::daddslashes($_SERVER);
		$this->_inputs[INPUT_FILE]= $_FILES;
	}
	
	public function __destruct()
	{
		
	}
	
	public function GetInput($field, $fieldtype=DT_STR, $errMsg=null, $default=null)
	{
		if( isset($this->_inputs[INPUT_POST][$field]) )
		{
			return $this->GetInputFrom(INPUT_POST,$field,$fieldtype,$errMsg, $default);
		}
		else 
		{
			return $this->GetInputFrom(INPUT_GET,$field,$fieldtype,$errMsg, $default);
		}
	}
	
	public function GetInputFrom($group, $field, $datatype='string',$errmsg=null, $default=null)
	{
		$value = null;
		if(isset($this->_inputs[$group]))
		{
			$value =  $this->_inputs[$group][$field];
		}
		if($value===null)
		{
			if($errmsg)
				throw new ArgumentException($errmsg, LOCATION_BACK);
			else 
				$value=$default;
		}
		switch ($datatype)
		{
			case DT_INT:
				$value = intval($value);
				if(!$value && $errmsg) 
				{
					throw new ArgumentException($errmsg, LOCATION_BACK);
				}
				break;
			case DT_NUMBER:
				if(!is_numeric($value)) 
				{
					if($errmsg)
						throw new ArgumentException($errmsg, LOCATION_BACK);
					else
						$value = 0;
				}
				break;
			case DT_ARRAY :
				if (!is_array($value))
				{
					if($errmsg)
						throw new ArgumentException($errmsg, LOCATION_BACK);
					else
						$value = null;
				}
				break;
			case DT_FILE :
				break;
			case DT_STR :
				$value = trim($value);
				if ($errmsg && strlen($value)==0)
				{
					throw new ArgumentException($errmsg, LOCATION_BACK);
				}
				break;
			default: 
				$value = trim($value);//string
		}
		return $value;
	}
	
	public function SetInput($field, $fieldvalue)
	{
		$this->_inputs[INPUT_POST][$field] = $fieldvalue;
	}
}