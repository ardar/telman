<?php
interface IRequest
{
	public function GetInput($field, $fieldtype=DT_STR, $errMsg=null, $default=null);
	
	public function GetInputFrom($group, $field, $datatype='str',$errmsg=null, $default=null);
	
	public function SetInput($field, $fieldvalue);
}