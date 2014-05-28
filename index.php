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
	define("APPLICATION_PATH", __DIR__);
	date_default_timezone_set('America/Los_Angeles');

	// Ensure src/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		__DIR__ ,
	    __DIR__ . '/src',
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

	require 'vendor/autoload.php';
	require_once 'vendor/php-activerecord/php-activerecord/ActiveRecord.php';

	ActiveRecord\Config::initialize(function($cfg)
 	{
 		$cfg->set_model_directory('models');
 		$cfg->set_connections(array('development' =>
 		'mysql://tracker:P1zzaP4rty!!!@localhost/tracker?charset=utf8'));
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
	require_once('House/Service/ProgramService.php');


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
			// $response = $service->findByApiKey($request->params('api_key'));
			$response = $service->find($request->params('id'));

			var_dump($response);

			if(!$response->isOk()){
				
				// @todo if request is not GET, and user does not have write access, then send 403 as well

				$app->response->setStatus(403);
				$app->stop();
			} else {
				$user = $response->getData();
			}
		};
	};

	$app->get('/hello/:name',  $authenticate($app), function ($name) {
		global $user;
    	var_dump($user);
	});

	$app->post('/auth/', $authenticate($app), function () {
		global $app;
		
		$request = $app->request;

		$service = new UserService();
		
		$response = $service->auth($request->params('email'), $request->params('password'));
		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->response->setStatus(404);
			$app->stop();
		} 
	});


	$app->get('/nowplaying/',  $authenticate($app), function () {
		global $app;
		
		$request = $app->request;
		$service = new ProgramService();
		
		$response = $service->nowPlaying();
		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->response->setStatus(404);
			$app->stop();
		}
	});

	$app->get('/playnow/:uuid/',  $authenticate($app), function ($uuid) {
		global $app;
		
		$request = $app->request;
		$service = new ProgramService();
		
		$response = $service->playNow($uuid);
		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		}
	});

	/**
	* Get all programs from now or timeslot
	* 
	* @param $timeslot (optional) (default = now) unix timestamp
	* @return array //collection of programs
	*/
	$app->get('/programs/',  $authenticate($app), function () {
		global $app;
		
		$request = $app->request;
		$service = new ProgramService();
		
		//build criteria
		$criteria = array();
		$criteria['timeslot'] = is_null($request->get('timeslot')) ? time() : $request->get('timeslot');
		
		$response = $service->find($criteria);
		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		}
	});

	$app->get('/programs/:id/',  $authenticate($app), function ($id) {
		global $app;
		
		$request = $app->request;
		$service = new ProgramService();
		
		//build criteria
		$criteria = array();
		$criteria['id'] = $id;
		
		$response = $service->find($criteria);
		$app->response->setBody(json_encode($response));
		
		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		}
	});


	$app->post('/programs/',  $authenticate($app), function () {
		global $app;
		global $user;
		
		$request = $app->request;
		$service = new ProgramService();
		
		if ($request->params('id')==='new' ||  $request->params('id')==""){

			$service->create(array_merge($request->params(), array("user_id"=>$user['id'])));
		} else {
			$service->update($request->params());
		}

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