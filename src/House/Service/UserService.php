<?php
require_once("models/User.php");
require_once("models/UserInstagram.php");
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

}
