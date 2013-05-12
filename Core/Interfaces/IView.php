<?php
interface IView
{
	public function Init($setting);
	
	public function Assign($field, $value);
	
	public function Display($template, $chacheid, $params);
	
	public function GetOutput($template, $chacheid, $params);
}