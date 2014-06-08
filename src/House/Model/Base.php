<?php
/**
 * Extends ActiveRecord  base model
 *
 * @author rsatsangi
 */
class Base extends ActiveRecord\Model {
  
    static $before_save = array('set_timestamps'); # new OR updated records
    static $after_create = array('create_tokens'); # new records only
    
    public function set_timestamps(){
    	if( array_key_exists('date_added', $this->attributes()) ) {
	        if($this->date_added==""){
	            $this->date_added = date("F j, Y, g:i a"); 
	        }
    	}
        if( array_key_exists('date_updated', $this->attributes()) ) {
        	$this->date_updated = date("F j, Y, g:i a");	
        }
        
    }

    public function create_tokens(){
        if( array_key_exists('auth_token', $this->attributes()) ) {
            if($this->auth_token==""){
                $this->auth_token =md5($this->id); 
                $this->save();
            }
        }
        
    }
}

?>
