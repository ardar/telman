<?php
define("WEBSERVICE",1);

require_once '../Core/Framework.php';

$setting = new AppSetting();

$application = new Application();

$application->Init($setting);

$application->GetRequest()->SetInput(FIELD_CONTROLLER, "WebService");

$application->Run();

//$application->DebugInfo();