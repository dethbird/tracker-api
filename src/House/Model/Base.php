<?php
/**
 * Extends ActiveRecord  base model
 *
 * @author rsatsangi
 */
class Base extends ActiveRecord\Model {
  
    static $before_save = array('set_timestamps'); # new OR updated records
    
    public function set_timestamps(){
        if($this->date_added==""){
            $this->date_added = date("F j, Y, g:i a"); 
        }
        $this->date_updated = date("F j, Y, g:i a");
    }
}

?>
