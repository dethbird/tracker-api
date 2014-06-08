<?php
	/**
	* 
	*    _____                                 __                
  	*   /  _  \ ______ ______     ______ _____/  |_ __ ________  
 	*  /  /_\  \\____ \\____ \   /  ___// __ \   __\  |  \____ \ 
	* /    |    \  |_> >  |_> >  \___ \\  ___/|  | |  |  /  |_> >
	* \____|__  /   __/|   __/  /____  >\___  >__| |____/|   __/ 
	*         \/|__|   |__|          \/     \/           |__|    
	*/
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	define("APPLICATION_PATH", __DIR__ . "/..");
	date_default_timezone_set('America/Los_Angeles');

	//read env file
	// # just points to environment config yml
	global $configs;
	$env = parse_ini_file("../env.ini");
	$configs = parse_ini_file($env['config_file']);

	// Ensure src/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		APPLICATION_PATH ,
	    APPLICATION_PATH . '/src',
	    get_include_path(),
	)));

	/**
	* __________               __                                
	* \______   \ ____   _____/  |_  __________________  ______  
 	* |    |  _//  _ \ /  _ \   __\/  ___/\_  __ \__  \ \____ \ 
 	* |    |   (  <_> |  <_> )  |  \___ \  |  | \// __ \|  |_> >
 	* |______  /\____/ \____/|__| /____  > |__|  (____  /   __/ 
	*         \/                        \/             \/|__|    
	*/

	require '../vendor/autoload.php';
	require_once '../vendor/php-activerecord/php-activerecord/ActiveRecord.php';

	ActiveRecord\Config::initialize(function($cfg)
 	{
 		global $configs;
 		$cfg->set_model_directory('../models');
 		$cfg->set_connections(array('development' =>
 		"mysql://". $configs['mysql_user'] .":" .$configs['mysql_password']. "@" .$configs['mysql_host']. "/" .$configs['mysql_database']. "?charset=utf8"));
 	});

 	global $app, $user;
	$app = new \Slim\Slim();
	$app->response->headers->set('Content-Type', 'application/json'); //default response type
	$app->response->headers->set("Access-Control-Allow-Origin", "*"); // CORS
	$app->response->headers->set("Access-Control-Allow-Methods", "*");
	$app->response->headers->set("Access-Control-Allow-Headers", "*");



	/**
	* __________                   .__                       
	* \______   \ ____  ________ __|__|______   ____   ______
 	* |       _// __ \/ ____/  |  \  \_  __ \_/ __ \ /  ___/
 	* |    |   \  ___< <_|  |  |  /  ||  | \/\  ___/ \___ \ 
 	* |____|_  /\___  >__   |____/|__||__|    \___  >____  >
	*         \/     \/   |__|                     \/     \/ 	
	*/
	require_once('House/Service/UserService.php');
	require_once('House/Service/ActivityService.php');


	/**
	* __________               __  .__                
	* \______   \ ____  __ ___/  |_|__| ____    ____  
 	* |       _//  _ \|  |  \   __\  |/    \  / ___\ 
 	* |    |   (  <_> )  |  /|  | |  |   |  \/ /_/  >
 	* |____|_  /\____/|____/ |__| |__|___|  /\___  / 
	*         \/                           \//_____/  	
	*/

	/**
	* Authentication should be run as middleware before each route
	*/
	$authenticate = function($app) 
	{
		return function () use ( $app ) 
		{
			global $user;
			
			$request = $app->request;

			$service = new UserService();
			$response = $service->findByAuthToken($request->headers('Auth-Token'));

			if($response->status!==true){
				$app->response->setStatus(403);
				$app->stop();
			} else {
				$user = $response->getData();
			}
		};
	};


	$app->post('/user', $authenticate($app), function () use ($app) {
		
		$request = $app->request;

		//find user by email
		$service = new UserService();
		$response = $service->findByEmail($request->params('email'));
		
		// if not found create one
		if(!$response->isOk()){
			$response = $service->create($request->params());
		}

		//return user
		$app->response->setBody(json_encode($response));


	});

	$app->get('/user/token-auth',  function () use ($app) {
		$request = $app->request;

		$service = new UserService();
		$response = $service->findByAuthToken($request->headers('Auth-Token'));
		$app->response->setBody(json_encode($response));

	});

	$app->get('/activity/log',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->getLog(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));
		
	});


	$app->post('/activity',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();


		$response = $service->create(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));
		
	});

	$app->delete('/activity',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->delete(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));
		
	});

	$app->get('/activity/type', $authenticate($app), function () use ($app) {
		global $user;

		
		$request = $app->request;
		$service = new ActivityService();
		
		$response = $service->findType(array("user_id"=>$user['id']));

		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		} 

	});

	$app->post('/activity/type',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->createType(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));

	});

	$app->patch('/activity/type/:id',  $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		 $params = $request->params();
		 $params['id'] = $id;
		 $params['user_id'] = $user['id'];

		$response = $service->updateType($params);

		$app->response->setBody(json_encode($response));

	});


	$app->get('/activity/type/:id',  $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->findType(array_merge(array("user_id"=>$user['id'], "id"=>$id)));

		$app->response->setBody(json_encode($response));
		
	});

	$app->get('/activity/report/by/:timeframe',  $authenticate($app), function ($timeframe) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->report(array_merge(
			array(
				"user_id"=>$user['id'],
				"timeframe"=>$timeframe
			), 
			$request->params()
		));

		$app->response->setBody(json_encode($response));
		
	});


	/**
	* __________            ._._._.
	* \______   \__ __  ____| | | |
 	* |       _/  |  \/    \ | | |
 	* |    |   \  |  /   |  \|\|\|	
 	* |____|_  /____/|___|  /_____
	*        \/           \/\/\/\/	
	*/
	$app->run();
?>