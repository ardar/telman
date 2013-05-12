<?php
interface IHtmlElement 
{
	public function GetId();
	public function GetName();
	public function GetTitle();
	public function GetBody();
	public function __toString();
}