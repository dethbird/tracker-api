<?php
require_once("models/Goal.php");
require_once("House/Service/BaseService.php");

class GoalService extends BaseService
{
	
    public function find($criteria = array())
    {

       $activities = Goal::find_by_sql('
            SELECT 
                `goal`.*,
                `activity_type`.name,
                `activity_type`.polarity
            FROM  `goal` 
            LEFT JOIN  `activity_type` ON  `goal`.`activity_type_id` =  `activity_type`.`id` 
            WHERE  `goal`.`user_id` = '.$criteria['user_id'].'
            '. ( isset($criteria['id']) ? ' AND  `goal`.`id` = '.$criteria['id'] : null) .'
            ORDER BY  `goal`.`date_added` DESC 
        ');

        $this->response->setData($this->resultsToArray($activities));
        return $this->response;
    }


    public function create($params)
    {
    
        try {
            $object = Goal::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData(array_merge($object->to_array(), array("is_new"=>1)));
        return $this->response;
    }


    public function update($criteria = array())
    {
        $type = Goal::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
        return $this->response;
    }
  
}
