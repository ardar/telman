<?php
require_once '../Core/Framework.php';

$setting = new AppSetting();

$application = new Application();

$application->Init($setting);

header("Content-Type: text/html; charset=utf-8");

$application->Run();
			
$application->DebugInfo();

