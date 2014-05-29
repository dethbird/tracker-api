<?php
require_once("models/Activity.php");
require_once("House/Service/BaseService.php");

class ActivityService extends BaseService
{


    public function getLog($criteria = array())
    {

       $activities = Activity::find_by_sql('
            SELECT * 
            FROM  `activity` 
            LEFT JOIN  `activity_type` ON  `activity`.`activity_type_id` =  `activity_type`.`id` 
            WHERE  `activity`.`user_id` = '.$criteria['user_id'].'
            ORDER BY  `activity`.`date_added` DESC 
        ');

        
        $this->response->setData($this->resultsToArray($activities));
        return $this->response;
    }

    public function findType($criteria = array())
    {

        $conditionString = "1 = 1";
        $conditionBindings = array();
        if(isset($criteria['id'])){
            $conditionString .= " AND id = ?";
            $conditionBindings[] = $criteria['id'];
        }

        $types = ActivityType::find('all', array(
            'order' => 'name asc',
            'conditions' => array_merge(array($conditionString), $conditionBindings)
        ));


        if(count($types)<1){
            $this->response->addError("acitivity_type.find.no_results");
        } else {
            $this->response->setData($this->resultsToArray($types));
        }
        return $this->response;
    }

    public function create($params)
    {
    
        try {$object = Activity::create($params);}
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    // public function update($params)
    // {
    //     unset($params['api_key']);
    //     unset($params['$$hashKey']);
    //     if($params['extension']==""){
    //         $params['extension'] = null;
    //     }
        
    //     $program = Program::find($params['id']);
    //     if(!$program->uuid){
    //         $params['uuid'] = $this->gen_uuid();
    //     }
    //     $program->update_attributes($params);
    //     return $program;
    // }


}
