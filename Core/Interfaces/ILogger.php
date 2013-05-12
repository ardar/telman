<?php
interface ILogger 
{
	public function __construct($logdest);
	
	public function Log($level, $msg, $params);
	
	public function Flush();
}