<?php
interface IExtension
{
    public function GetName();
    
	public function Init($options);
	
	public function GetMenu();
	
	public function GetSubMenus();
		
	public function GetPriveleges();
}