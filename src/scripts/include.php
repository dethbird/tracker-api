<?php

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	define("APPLICATION_PATH", __DIR__ . "/../..");
	date_default_timezone_set('America/New_York');

	// Ensure src/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		APPLICATION_PATH ,
	    APPLICATION_PATH . '/src',
	    get_include_path(),
	)));

	$env = parse_ini_file("env.ini");
	$configs = parse_ini_file($env['config_file']);

	require_once "vendor/autoload.php";
	require_once 'src/logger.php';
	require_once 'vendor/php-activerecord/php-activerecord/ActiveRecord.php';

	ActiveRecord\Config::initialize(function($cfg)
 	{
 		global $configs;
 		$cfg->set_model_directory(APPLICATION_PATH . '/models');
 		$cfg->set_connections(array('development' =>
 		"mysql://". $configs['mysql_user'] .":" .$configs['mysql_password']. "@" .$configs['mysql_host']. "/" .$configs['mysql_database']. "?charset=utf8"));
 	});



	// ---------------------------------------------------------------

	require_once("models/User.php");
	require_once("models/Activity.php");
	require_once("House/Service/ActivityService.php");
	require_once("House/Service/UserService.php");
