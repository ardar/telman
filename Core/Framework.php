<?php
error_reporting(E_ALL ^ E_NOTICE);

define("FW_DIR", realpath("../")."/");
define("PUBLIC_DIR", realpath("./")."/");

require_once FW_DIR.'Configs/AppSetting.php';
require_once FW_DIR.'Configs/ModuleConfig.php';

require_once FW_DIR.'Core/Constants.php';
require_once FW_DIR.'Core/Interfaces/IApplication.php';
require_once FW_DIR.'Core/Interfaces/IAuthentication.php';
require_once FW_DIR.'Core/Interfaces/IView.php';
require_once FW_DIR.'Core/Interfaces/IExtension.php';
require_once FW_DIR.'Core/Interfaces/IRequest.php';
require_once FW_DIR.'Core/Interfaces/ILogger.php';
require_once FW_DIR.'Core/Interfaces/ICache.php';
require_once FW_DIR.'Core/Interfaces/IDatabase.php';
require_once FW_DIR.'Core/Interfaces/IHtmlElement.php';
require_once FW_DIR.'Core/Application.php';
require_once FW_DIR.'Core/UrlHelper.php';

spl_autoload_register('Application::AutoLoader');