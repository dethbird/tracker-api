<?php
require_once("models/User.php");
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

    // public function auth($email, $password)
    // {
    //     $users = User::find('all', array('conditions' => array('email = ? AND password = MD5(?)', $email, $password)));

    //     if(count($users)<1){
    //         $this->response->addError("user.auth.invalid_user", "Invalid email / password");
    //     } else {
    //         $this->response->setData($users[0]->to_array());
    //     }
    //     return $this->response;
    // }    
}
