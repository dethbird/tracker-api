<?php
/**
 * Extends ActiveRecord  base model
 *
 * @author rsatsangi
 */
class Base extends ActiveRecord\Model {
  
    static $before_save = array('set_timestamps'); # new OR updated records
    
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
}

?>
