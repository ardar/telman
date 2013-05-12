<?php
class IndexController extends ControllerBase
{	
	public function indexAction()
	{
		header("Location:index.php?controller=Frame");
	}
}