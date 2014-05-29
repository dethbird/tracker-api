<?php
require_once("models/Activity.php");
require_once("House/Service/BaseService.php");

class ProgramService extends BaseService
{

    /**
    * Find the content that should be playing right now
    */
    public function nowPlaying()
    {

        $programs = Program::find_by_sql('SELECT * FROM `programs` WHERE timeslot < UNIX_TIMESTAMP(NOW()) ORDER BY timeslot DESC LIMIT 1');

        if(count($programs)<1){
            $this->response->addError("programs.find.no_results");
        } else {
            $this->response->setData($this->resultsToArray($programs));
        }
        return $this->response;
    }

    public function playNow($uuid)
    {
        //find by uuid
        $programs = Program::find_all_by_uuid($uuid);

        //none found by uuid
        if(count($programs)<1){
            $this->response->addError("programs.playnow.no_results");
            return $this->response;
        } 
        
        foreach($programs as $program){
            
            if($program->timeslot > time()){ //program is in future
                $this->response->addError("programs.playnow.in_future");
                return $this->response;
            } else { //program is in past

                if($program->extension==""){ //no extension
                    
                    //get current program
                    $_programs = Program::find_by_sql('SELECT * FROM `programs` WHERE timeslot < UNIX_TIMESTAMP(NOW()) ORDER BY timeslot DESC LIMIT 1');
                
                    //there is a current program    
                    if(count($_programs>0)){
                        $currentProgram = $_programs[0];
                
                        //is this link the current program?
                        if($program->id!=$currentProgram->id){ //not the current program
                            $this->response->addError("programs.playnow.not_current_program");
                            return $this->response;
                        }
                    }
                } else { //extension exists
                    if($program->extension < time()){ //extension has expired
                        $this->response->addError("programs.playnow.extension_expired");
                        return $this->response;
                    }
                }
            }
        }

        $this->response->setData($this->resultsToArray($programs));
        
    }

    public function find($criteria = array())
    {

        $conditionString = "1 = 1";
        $conditionBindings = array();
        if(isset($criteria['id'])){
            $conditionString .= " AND id = ?";
            $conditionBindings[] = $criteria['id'];
        }

    	$programs = Program::find('all', array(
            'order' => 'timeslot asc',
            'conditions' => array_merge(array($conditionString), $conditionBindings)
        ));

    	if(count($programs)<1){
    		$this->response->addError("programs.find.no_results");
    	} else {
    		$this->response->setData($this->resultsToArray($programs));
    	}
    	return $this->response;
    }

    public function create($params)
    {
        // unset($params['api_key']);
        // unset($params['$$hashKey']);
        // if($params['extension']==""){
        //     $params['extension'] = null;
        // }

        $params['id'] = null;
        // $params['uuid'] = $this->gen_uuid();
    
        return Activity::create($params);
    }

    public function update($params)
    {
        unset($params['api_key']);
        unset($params['$$hashKey']);
        if($params['extension']==""){
            $params['extension'] = null;
        }
        
        $program = Program::find($params['id']);
        if(!$program->uuid){
            $params['uuid'] = $this->gen_uuid();
        }
        $program->update_attributes($params);
        return $program;
    }


}
