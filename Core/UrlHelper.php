<?php

$EnumUrlType = array(
	'index'		=>	'',
	'home'		=>	'index.php',
	'ucp'		=>	'index.php',
	'frame'		=>	'index.php',
	'misc'		=>	'misc.php?proc={1}{2}{3}{4}{5}',
	'login'		=>	'index.php?controller=Account&action=login',
	'register'		=>	'index.php?controller=Account&action=register',
	'profile'		=>	'index.php?controller=AccountAdmin&action=profile&username={1}',
	'action'		=>	'index.php?controller={1}&action={2}{3}{4}{5}',
);

function urlhelper($type='other',$param1='',$param2='',$param3='',$param4='',$param5='')
{
	global $EnumUrlType;
	if(isset($EnumUrlType[$type])){
		$urlp = $EnumUrlType[$type];
		$url = str_replace(array('{1}','{2}','{3}','{4}','{5}'),array($param1,$param2,$param3,$param4,$param5),$urlp);
	}
	else{
		$url = $param1;
	}
	return $url;
}