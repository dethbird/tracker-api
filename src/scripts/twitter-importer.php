<?php
	
	require_once("include.php");
	use \Guzzle\Http\Client;
	use \Guzzle\Plugin\Oauth\OauthPlugin;

	$users = User::find('all');
	$activityService = new ActivityService();
	$userService = new UserService();

	foreach ($users as $user) {
		$response = $userService->findTwitter(
			array(
				"user_id" => $user->id
			)
		);
		$twitters = $response->getData();
		if(count($twitters) > 0){
			foreach($twitters as $twitter){


				$c = new Client('https://api.twitter.com/{version}', array(
			        'version' => '1.1'
			    ));

				$c->addSubscriber(new OauthPlugin(array(
				    'consumer_key'    => $configs['twitter.key'],
				    'consumer_secret' => $configs['twitter.secret'],
				    'token'           => $twitter['access_token'],
				    'token_secret'    => $twitter['access_token_secret'],
				)));

				$r = $c->get('statuses/user_timeline.json?count=200')->send();

				$twitter['_data'] = json_decode($r->getBody(true));

				foreach($twitter['_data'] as $tweet) {
					// var_dump($tweet->id);

					$activities = Activity::find_by_sql('SELECT * FROM `activity` WHERE user_id = ' . $user->id . ' AND social_media_id = "' . $tweet->id .'"');
					
					$activity = null;
					if (count($activities)>0) {
						//update
						$activity = $activities[0];
					} else {
						//new
						$activity = new Activity();
						$activity->user_id = $user->id;
						$activity->activity_type_id = 35;
						$activity->type = 'socialmedia';
						$activity->social_media_id = $tweet->id;
						$activity->social_user_id = $twitter['twitter_user_id'];
					}

					$activity->json = json_encode($tweet);
					$activity->date_added = $tweet->created_at;

					if($activity->id != ""){
						$response = $activityService->update($activity->to_array());
					} else {
						$activity->save();
					}

					Logger::log($response);
				}	
			}
		}

	}