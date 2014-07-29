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
	require_once 'vendor/php-activerecord/php-activerecord/ActiveRecord.php';

	ActiveRecord\Config::initialize(function($cfg)
 	{
 		global $configs;
 		$cfg->set_model_directory('../models');
 		$cfg->set_connections(array('development' =>
 		"mysql://". $configs['mysql_user'] .":" .$configs['mysql_password']. "@" .$configs['mysql_host']. "/" .$configs['mysql_database']. "?charset=utf8"));
 	});



	// ---------------------------------------------------------------

	require_once("models/User.php");
	require_once("models/Activity.php");
	require_once("House/Service/ActivityService.php");



	$users = User::find('all');
	$activityService = new ActivityService();

	foreach ($users as $user) {
		// print_r($user->to_array());

		$activities = Activity::find_by_sql('SELECT * FROM `activity` WHERE user_id = ' . $user->id);

		// echo print_r($activities);

		foreach ($activities as $activity) {
			// print_r($activity->to_array());	

			
			if ($activity->json!="") {

				$json = json_decode($activity->json);

				if($activity->type=="flickr") {
					
					// print_r($json); die();
					// print_r(date("Y-m-d g:i:s A", $json->checkin->createdAt)); die();
					$activity->date_added = date("Y-m-d g:i:s A", $json->photo->dates->attributes->posted);
					$activity->social_media_id = $json->photo->attributes->id;
				}

				if($activity->type=="foursquare") {
					// print_r($json); die();
					// print_r(date("Y-m-d g:i:s A", $json->checkin->createdAt)); die();
					$activity->social_media_id = $json->checkin->id;
				}

				if($activity->type=="instagram") {
					// print_r($json); die();
					$activity->date_added = date("Y-m-d g:i:s A", $json->created_time);
					$activity->social_media_id = $json->id;
				}
				// $activity->update();
				// print_r($activity->to_array());

				$response = $activityService->update($activity->to_array());

				print_r($response);
			}
				
			
		}

	}

?>