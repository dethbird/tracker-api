<?php
require_once("models/User.php");
require_once("models/UserInstagram.php");
require_once("models/UserTwitter.php");
require_once("House/Service/BaseService.php");

class UserService extends BaseService
{
	
    public function findByApiKey($key)
    {
    	$users = User::find('all', array('conditions' => array('api_key = ?', $key)));

    	if(count($users)<1){
    		$this->response->addError("user.auth.invalid_key");
    	} else {
    		$this->response->setData($users[0]->to_array());
    	}
    	return $this->response;
    }

    public function find($id)
    {
        $users = User::find('all', array('conditions' => array('id = ?', $id)));

        if(count($users)<1){
            $this->response->addError("user.not_found");
        } else {
            $this->response->setData($users[0]->to_array());
        }
        return $this->response;
    }

    public function findByAuthToken($token)
    {
        $users = User::find('all', array('conditions' => array('auth_token = ?', $token)));

        if(count($users)<1){
            $this->response->addError("user.not_found");
        } else {
            $this->response->setData($users[0]->to_array());
        }
        return $this->response;
        
    }


    public function findByEmail($email)
    {
        $users = User::find('all', array('conditions' => array('email = ?', $email)));

        if(count($users)<1){
            $this->response->addError("user.not_found");
        } else {
            $this->response->setData($users[0]->to_array());
        }
        return $this->response;
        
    }

    public function create($params)
    {
    
        try {
            $object = User::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData(array_merge($object->to_array(), array("is_new"=>1)));
        return $this->response;
    }

    /**
    * INSTAGRAM
    */

    public function findInstagram($criteria)
    {
        $sql = '
        SELECT
            *
            FROM  `user_instagram` 
            
            WHERE  1
            '. ( isset($criteria['user_id']) ? ' AND  `user_instagram`.`user_id` = '.$criteria['user_id'] : null) .'
            '. ( isset($criteria['instagram_user_id']) ? ' AND  `user_instagram`.`instagram_user_id` = "'.$criteria['instagram_user_id'].'"' : null) .'
             
        ';

        $items = UserInstagram::find_by_sql($sql);

        $this->response->setData($this->resultsToArray($items));
        return $this->response;
    }

    public function createInstagram($params)
    {
        try {
            $object = UserInstagram::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function deleteInstagram($params)
    {

        try {
            $object = UserInstagram::find($params['id']);
            if($object->user_id != $params['user_id']){
                throw new \Exception ("Invalid user");
            }
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function updateInstagram($criteria)
    {
        $type = UserInstagram::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    }

    /**
    * GITHUB
    */

    public function findGithub($criteria)
    {
        $sql = '
        SELECT
            *
            FROM  `user_github` 
            
            WHERE  1
            '. ( isset($criteria['user_id']) ? ' AND  `user_github`.`user_id` = '.$criteria['user_id'] : null) .'
            '. ( isset($criteria['github_user_id']) ? ' AND  `user_github`.`github_user_id` = "'.$criteria['github_user_id'].'"' : null) .'
            '. ( isset($criteria['username']) ? ' AND  UPPER(`user_github`.`username`) = "'.strtoupper($criteria['username']).'"' : null) .'
             
        ';

        $items = UserGithub::find_by_sql($sql);

        $this->response->setData($this->resultsToArray($items));
        return $this->response;
    }

    public function createGithub($params)
    {
        try {
            $object = UserGithub::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function deleteGithub($params)
    {

        try {
            $object = UserGithub::find($params['id']);
            if($object->user_id != $params['user_id']){
                throw new \Exception ("Invalid user");
            }
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function updateGithub($criteria)
    {
        $type = UserGithub::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    }


    /**
    * Twitter
    */

    public function findTwitter($criteria)
    {
        $sql = '
        SELECT
            *
            FROM  `user_twitter` 
            
            WHERE  1
            '. ( isset($criteria['user_id']) ? ' AND  `user_twitter`.`user_id` = '.$criteria['user_id'] : null) .'
            '. ( isset($criteria['twitter_user_id']) ? ' AND  `user_twitter`.`twitter_user_id` = "'.$criteria['twitter_user_id'].'"' : null) .'
            '. ( isset($criteria['username']) ? ' AND  UPPER(`user_twitter`.`username`) = "'.strtoupper($criteria['username']).'"' : null) .'
             
        ';


        $items = UserTwitter::find_by_sql($sql);

        $this->response->setData($this->resultsToArray($items));
        return $this->response;
    }

    public function createTwitter($params)
    {
        try {
            $object = UserTwitter::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function deleteTwitter($params)
    {

        try {
            $object = UserTwitter::find($params['id']);
            if($object->user_id != $params['user_id']){
                throw new \Exception ("Invalid user");
            }
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function updateTwitter($criteria)
    {
        $type = UserTwitter::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    } 

    /**
    * FLICKR
    */

    public function findFlickr($criteria)
    {
        $sql = '
        SELECT
            *
            FROM  `user_flickr` 
            
            WHERE  1
            '. ( isset($criteria['user_id']) ? ' AND  `user_flickr`.`user_id` = '.$criteria['user_id'] : null) .'
            '. ( isset($criteria['nsid']) ? ' AND  `user_flickr`.`nsid` = "'.$criteria['nsid'].'"' : null) .'
             
        ';

        $items = UserFlickr::find_by_sql($sql);

        $this->response->setData($this->resultsToArray($items));
        return $this->response;
    }

    public function createFlickr($params)
    {
        try {
            $object = UserFlickr::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function deleteFlickr($params)
    {

        try {
            $object = UserFlickr::find($params['id']);
            if($object->user_id != $params['user_id']){
                throw new \Exception ("Invalid user");
            }
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function updateFlickr($criteria)
    {
        $type = UserFlickr::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    }

    /**
    * FOURSQUARE
    */

    public function findFoursquare($criteria)
    {
        $sql = '
        SELECT
            *
            FROM  `user_foursquare` 
            
            WHERE  1
            '. ( isset($criteria['user_id']) ? ' AND  `user_foursquare`.`user_id` = '.$criteria['user_id'] : null) .'
            '. ( isset($criteria['foursquare_user_id']) ? ' AND  `user_foursquare`.`foursquare_user_id` = "'.$criteria['foursquare_user_id'].'"' : null) .'
             
        ';

        $items = UserFoursquare::find_by_sql($sql);

        $this->response->setData($this->resultsToArray($items));
        return $this->response;
    }

    public function createFoursquare($params)
    {
        try {
            $object = UserFoursquare::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function deleteFoursquare($params)
    {

        try {
            $object = UserFoursquare::find($params['id']);
            if($object->user_id != $params['user_id']){
                throw new \Exception ("Invalid user");
            }
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function updateFoursquare($criteria)
    {
        $type = UserFoursquare::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    }    

}
