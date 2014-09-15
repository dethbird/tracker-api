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
	date_default_timezone_set('America/New_York');

	// Ensure src/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		APPLICATION_PATH ,
	    APPLICATION_PATH . '/src',
	    get_include_path(),
	)));

	//read env file
	// # just points to environment config yml
	global $app,
		$configs,
		$user,
		$instagramClient;

	$env = parse_ini_file("env.ini");
	$configs = parse_ini_file($env['config_file']);



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
	require_once 'src/logger.php';
	use Jcroll\FoursquareApiClient\Client\FoursquareClient;	

	ActiveRecord\Config::initialize(function($cfg)
 	{
 		global $configs;
 		$cfg->set_model_directory('../models');
 		$cfg->set_connections(array('development' =>
 		"mysql://". $configs['mysql_user'] .":" .$configs['mysql_password']. "@" .$configs['mysql_host']. "/" .$configs['mysql_database']. "?charset=utf8"));
 	});

	$app = new \Slim\Slim();
	$app->response->headers->set('Content-Type', 'application/json'); //default response type
	$app->response->headers->set("Access-Control-Allow-Origin", "*"); // CORS
	$app->response->headers->set("Access-Control-Allow-Methods", "*");
	$app->response->headers->set("Access-Control-Allow-Headers", "*");


	require_once("../vendor/cosenary/instagram/instagram.class.php");
	$instagramClient = new Instagram(array(
      'apiKey'      => $configs['instagram.key'],
      'apiSecret'   => $configs['instagram.secret'],
      'apiCallback' => "http://". $_SERVER['HTTP_HOST'] . '/callback/oauth/instagram'
    ));



	/**
	* __________                   .__                       
	* \______   \ ____  ________ __|__|______   ____   ______
 	* |       _// __ \/ ____/  |  \  \_  __ \_/ __ \ /  ___/
 	* |    |   \  ___< <_|  |  |  /  ||  | \/\  ___/ \___ \ 
 	* |____|_  /\___  >__   |____/|__||__|    \___  >____  >
	*         \/     \/   |__|                     \/     \/ 	
	*/
	
	require_once('House/Service/ActivityService.php');
	require_once('House/Service/GoalService.php');
	require_once('House/Service/UserService.php');
	require_once('House/Service/Response/ServiceResponse.php');


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

	$app->get('/user/social', $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$response = new ServiceResponse();
		$responseBody = new stdClass();

		$service = new UserService();
		
		$instagramResponse = $service->findInstagram(array("user_id"=>$user['id']));
		$responseBody->instagram = $instagramResponse->data;
		
		$flickrResponse = $service->findFlickr(array("user_id"=>$user['id']));
		$responseBody->flickr = $flickrResponse->data;

		$foursquareResponse = $service->findFoursquare(array("user_id"=>$user['id']));
		$responseBody->foursquare = $foursquareResponse->data;

		$githubResponse = $service->findGithub(array("user_id"=>$user['id']));
		$responseBody->github = $githubResponse->data;
		
		$twitterResponse = $service->findTwitter(array("user_id"=>$user['id']));
		$responseBody->twitter = $twitterResponse->data;

		$response->setData($responseBody);

		$app->response->setBody(json_encode($response));

	});

	$app->delete('/user/social/delete', $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// print_r($request->params()); die();
		if ($request->params('type')=="instagram") {
			$response = $service->deleteInstagram(array_merge(array("user_id"=>$user['id']), $request->params()));
		} elseif ($request->params('type')=="flickr") {
			$response = $service->deleteFlickr(array_merge(array("user_id"=>$user['id']), $request->params()));
		} elseif ($request->params('type')=="foursquare") {
			$response = $service->deleteFoursquare(array_merge(array("user_id"=>$user['id']), $request->params()));
		} elseif ($request->params('type')=="github") {
			$response = $service->deleteGithub(array_merge(array("user_id"=>$user['id']), $request->params()));
		} elseif ($request->params('type')=="twitter") {
			$response = $service->deleteTwitter(array_merge(array("user_id"=>$user['id']), $request->params()));
		}


		$app->response->setBody(json_encode($response));

	});

	$app->post('/user/instagram', $authenticate($app),  function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// find instagram
		$instagramResponse = $service->findInstagram(array("instagram_user_id" => $request->params("instagram_user_id")));

		if(count($instagramResponse->data)<1){
			//create
			$response = $service->createInstagram(array_merge(array("user_id"=>$user['id']), $request->params()));
			$app->response->setBody(json_encode($response));
		} else {
			//belongs to this user?
			if($user['id'] != $instagramResponse->data[0]['user_id']){
				$app->response->setStatus(403);
				$app->stop();
			}

			$params = $request->params();
			$params['user_id'] = $user['id'];
			$params['id'] = $instagramResponse->data[0]['id'];

			$response = $service->updateInstagram($params);
			$app->response->setBody(json_encode($response));
		}

	});


	$app->post('/user/flickr', $authenticate($app),  function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// find flickr
		$flickrResponse = $service->findFlickr(array("nsid" => $request->params("nsid")));

		if(count($flickrResponse->data)<1){
			//create
			$response = $service->createFlickr(array_merge(array("user_id"=>$user['id']), $request->params()));
			$app->response->setBody(json_encode($response));
		} else {
			//belongs to this user?
			if($user['id'] != $flickrResponse->data[0]['user_id']){
				$app->response->setStatus(403);
				$app->stop();
			}

			$params = $request->params();
			$params['user_id'] = $user['id'];
			$params['id'] = $flickrResponse->data[0]['id'];

			$response = $service->updateFlickr($params);
			$app->response->setBody(json_encode($response));
		}

	});	

	$app->post('/user/foursquare', $authenticate($app),  function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// find foursquare
		$foursquareResponse = $service->findFoursquare(array("foursquare_user_id" => $request->params("foursquare_user_id")));

		if(count($foursquareResponse->data)<1){
			//create
			$response = $service->createFoursquare(array_merge(array("user_id"=>$user['id']), $request->params()));
			$app->response->setBody(json_encode($response));
		} else {
			//belongs to this user?
			if($user['id'] != $foursquareResponse->data[0]['user_id']){
				$app->response->setStatus(403);
				$app->stop();
			}

			$params = $request->params();
			$params['user_id'] = $user['id'];
			$params['id'] = $foursquareResponse->data[0]['id'];

			$response = $service->updateFoursquare($params);
			$app->response->setBody(json_encode($response));
		}

	});	

	$app->post('/user/github', $authenticate($app),  function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// find github
		$githubResponse = $service->findGithub(array("github_user_id" => $request->params("github_user_id")));

		if(count($githubResponse->data)<1){
			//create
			$response = $service->createGithub(array_merge(array("user_id"=>$user['id']), $request->params()));
			$app->response->setBody(json_encode($response));
		} else {
			//belongs to this user?
			if($user['id'] != $githubResponse->data[0]['user_id']){
				$app->response->setStatus(403);
				$app->stop();
			}

			$params = $request->params();
			$params['user_id'] = $user['id'];
			$params['id'] = $githubResponse->data[0]['id'];

			$response = $service->updateGithub($params);
			$app->response->setBody(json_encode($response));
		}

	});		


	$app->post('/user/twitter', $authenticate($app),  function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new UserService();

		// find twitter
		$twitterResponse = $service->findTwitter(array("twitter_user_id" => $request->params("twitter_user_id")));

		if(count($twitterResponse->data)<1){
			//create
			$response = $service->createTwitter(array_merge(array("user_id"=>$user['id']), $request->params()));
			$app->response->setBody(json_encode($response));
		} else {
			//belongs to this user?
			if($user['id'] != $twitterResponse->data[0]['user_id']){
				$app->response->setStatus(403);
				$app->stop();
			}

			$params = $request->params();
			$params['user_id'] = $user['id'];
			$params['id'] = $twitterResponse->data[0]['id'];

			$response = $service->updateTwitter($params);
			$app->response->setBody(json_encode($response));
		}

	});		

	// /social/activity/instagram
	$app->post('/social/activity/instagram', function () use ($app, $instagramClient) {

		$request = $app->request;
		$params = $request->params();
		
		//fetch the instagram account
		$service = new UserService();
		$instagramResponse = $service->findInstagram(array("instagram_user_id"=>$params["social_user_id"]));
		$instagram = $instagramResponse->getData();

		if(count($instagram) > 0) {

			$instagram = $instagram[0];
			$instagramClient->setAccessToken($instagram['access_token']);

			//get media
			$media = $instagramClient->getMedia($params['media_id']);
			$media = $media->data;

			// Logger::log(json_encode($media));

			$criteria = array(
				"activity_type_id" => 31,
				"quantity" => 1,
				// "note" => $note,
				"type" => "socialmedia",
				"social_user_id" => $params["social_user_id"],
				"social_media_id" => $params["media_id"],
				"json" => json_encode($media),
				"date_added" => date("Y-m-d g:i:s a"),
				"user_id" => $instagram['user_id']
			);

			$activityService = new ActivityService();
			$response = $activityService->create($criteria);

			$app->response->setBody(json_encode($response));
		}

	});

	$app->post('/social/activity/flickr', function () use ($app, $configs) {

		$request = $app->request;
		$params = $request->params();

		$service = new UserService();
		$flickrResponse = $service->findFlickr(array("nsid"=>$params["social_user_id"]));
		$flickr = $flickrResponse->getData();

		if(count($flickr) > 0) {

			$flickr = $flickr[0]; 
			$metadata = new Rezzza\Flickr\Metadata($configs['flickr.key'], $configs['flickr.secret']);
			$metadata->setOauthAccess($flickr['oauth_token'], $flickr['secret']);
						// var_dump($metadata); die();
			$factory  = new Rezzza\Flickr\ApiFactory($metadata, new Rezzza\Flickr\Http\GuzzleAdapter());

			$xml = $factory->call('flickr.photos.getInfo', array(
					"photo_id" => $params['media_id']
				)
			);

			$json = json_encode((array)$xml);
			$json = str_replace("@attributes", "attributes", $json);

			$criteria = array(
				"activity_type_id" => 32,
				"quantity" => 1,
				"type" => "socialmedia",
				"social_user_id" => $params["social_user_id"],
				"social_media_id" => $params["media_id"],
				"json" => $json,
				"date_added" => date("Y-m-d g:i:s a"),
				"user_id" => $flickr['user_id']
			);

			$activityService = new ActivityService();
			$response = $activityService->create($criteria);

			$app->response->setBody(json_encode($response));

		}

	});

	// /social/activity/foursquare
	$app->post('/social/activity/foursquare', function () use ($app, $configs) {

		$request = $app->request;
		$params = $request->params();

		//fetch the foursquare account
		$service = new UserService();
		$foursquareResponse = $service->findFoursquare(array("foursquare_user_id"=>$params["social_user_id"]));
		$foursquare = $foursquareResponse->getData();

		if(count($foursquare) > 0) {

			$client = FoursquareClient::factory(array(
			    'client_id'     => $configs['foursquare.key'],
			    'client_secret' => $configs['foursquare.secret']
			));
			$client->addToken($foursquare[0]['access_token']);
			$command = $client->getCommand('checkins', array("checkin_id" => $params["media_id"]));
			$result = $command->execute();
			$result = $result['response'];

			$criteria = array(
				"activity_type_id" => 33,
				"quantity" => 1,
				// "note" => $note,
				"type" => "socialmedia",
				"social_user_id" => $params["social_user_id"],
				"social_media_id" => $params["media_id"],
				"json" => json_encode($result),
				"date_added" => date("Y-m-d g:i:s a"),
				"user_id" => $foursquare[0]['user_id']
			);

			$activityService = new ActivityService();
			$response = $activityService->create($criteria);

			$app->response->setBody(json_encode($response));
		}

	});

	// /social/activity/instagram
	$app->post('/social/activity/github', function () use ($app) {

		$request = $app->request;
		$params = $request->params();
		
		//fetch the github account
		$service = new UserService();
		$githubResponse = $service->findGithub(array("username"=>$params["social_user_id"]));


		$github = $githubResponse->getData();

		if(count($github) > 0) {

			$github = $github[0];
			// $githubClient->setAccessToken($github['access_token']);

			//get media
			// $media = $githubClient->getMedia($params['media_id']);
			// $media = $media->data;

			// Logger::log(json_encode($media));

			$criteria = array(
				"activity_type_id" => 34,
				"quantity" => 1,
				// "note" => $note,
				"type" => "socialmedia",
				"social_user_id" => $params["social_user_id"],
				"social_media_id" => $params["media_id"],
				"json" => $params["json"],
				"date_added" => date("Y-m-d g:i:s a"),
				"user_id" => $github['user_id']
			);

			$activityService = new ActivityService();
			$response = $activityService->create($criteria);

			$app->response->setBody(json_encode($response));
		}

	});


	$app->get('/activity',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->find(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));
		
	});

	$app->get('/activity/report',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		var_dump($request->params()); die();

		$response = $service->find(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));
		
	});

	$app->get('/activity/type', $authenticate($app), function () use ($app) {

		global $user;

		$request = $app->request;
		$service = new ActivityService();
		// Logger::log($request->params());
		$response = $service->findType(array_merge(array("user_id"=>$user['id']), $request->params()));

		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		} 

	});

	$app->get('/activity/:id',  $authenticate($app), function ($id) use ($app) {

		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->find(array("user_id"=>$user['id'], "id" => $id));

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


	$app->get('/activity/type/:id',  $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$response = $service->findType(array_merge(array("user_id"=>$user['id'], "id"=>$id)));

		$app->response->setBody(json_encode($response));
		
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

	// edit activity
	$app->patch('/activity/:id',  $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();

		$params = $request->params();
		$params['id'] = $id;
		$params['user_id'] = $user['id'];

		$response = $service->update($params);

		$app->response->setBody(json_encode($response));
		
	});


	/**
	* GOALS
	*/	

	$app->get('/goals', $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new GoalService();

		$response = $service->find(array("user_id"=>$user['id']));

		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		} 

	});	


	$app->get('/goals/:id', $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new GoalService();

		$response = $service->find(array("user_id"=>$user['id'], "id"=>$id));

		$app->response->setBody(json_encode($response));

		if(!$response->isOk()){
			$app->halt(404, json_encode($response));
		} 

	});

	$app->post('/goals',  $authenticate($app), function () use ($app) {
		global $user;

		$request = $app->request;
		$service = new GoalService();

		$response = $service->create(array_merge(array("user_id"=>$user['id']), $request->params()));
		$app->response->setBody(json_encode($response));
		
	});


	$app->patch('/goals/:id',  $authenticate($app), function ($id) use ($app) {
		global $user;

		$request = $app->request;
		$service = new GoalService();

		 $params = $request->params();
		 $params['id'] = $id;
		 $params['user_id'] = $user['id'];

		$response = $service->update($params);

		$app->response->setBody(json_encode($response));

	});

	$app->get('/activity/report/by/:timeframe',  $authenticate($app), function ($timeframe) use ($app) {
		global $user;

		$request = $app->request;
		$service = new ActivityService();
		// var_dump($request->params()); die();
		$response = $service->report(array_merge(
			array(
				"user_id"=>$user['id'],
				"timeframe"=>$timeframe
			), 
			$request->params()
		));

		$app->response->setBody(json_encode($response));
		
	});



	$app->get('/feeds/users/:name', function ($name) use ($app) {
		global $user;

		$request = $app->request;
		$params = $request->params();
		$service = new ActivityService();

		$response = $service->userFeed(array_merge(
			array(
				"name"=>$name,
				"public"=>1
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