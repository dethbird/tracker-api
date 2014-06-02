<?php
require_once("models/Activity.php");
require_once("models/ActivityType.php");
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
    
        try {
            $object = Activity::create($params);
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function delete($params)
    {
    
        try {
            $object = Activity::find($params['id']);
            $object->delete();
        }
        catch (Exception $e) {
            print_r($e);
        }
        $this->response->setData($object->to_array());
        return $this->response;
    }

    public function createType($params)
    {
    
        try {
            $object = ActivityType::create($params);
        }
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

    public function report($criteria)
    {
        $activities = Activity::find_by_sql('
            SELECT `activity`.*,  `activity_type`.name, `activity_type`.polarity
            FROM  `activity` 
            LEFT JOIN  `activity_type` ON  `activity`.`activity_type_id` =  `activity_type`.`id` 
            WHERE  `activity`.`user_id` = '.$criteria['user_id'].'
            ORDER BY  `activity`.`date_added` DESC
        ');


        if($criteria['timeframe']=="day"){
            
            $currentDate = null;

            $report = new stdClass();
            $report->timeframes = array();
            $report->ativity_types = array();

            foreach ($activities as $activity ){

                $report->ativity_types[$activity->activity_type_id] = $activity->to_array();

                $date = date("Y-m-d", strtotime($activity->date_added));
                if($currentDate != $date){
                    $currentDate = $date;
                    // echo $currentDate." ";
                    $report->timeframes[$currentDate] = array();
                    $report->timeframes[$currentDate]['polarity'] = array();
                    $report->timeframes[$currentDate]['polarity']['good'] = 0;
                    $report->timeframes[$currentDate]['polarity']['bad'] = 0;
                    $report->timeframes[$currentDate]['occurrence'] = array();
                    $report->timeframes[$currentDate]['quantity'] = array();
                }

                $report->timeframes[$currentDate]['logs'][] = $activity->to_array();

                if($activity->polarity > 0){
                    $report->timeframes[$currentDate]['polarity']['good']++;
                } else {
                    $report->timeframes[$currentDate]['polarity']['bad']++;
                }

                if(!isset($report->timeframes[$currentDate]['occurrence'][$activity->activity_type_id])){
                    $report->timeframes[$currentDate]['occurrence'][$activity->activity_type_id] = 0;
                }
                $report->timeframes[$currentDate]['occurrence'][$activity->activity_type_id]++;

                if(!isset($report->timeframes[$currentDate]['quantity'][$activity->activity_type_id])){
                    $report->timeframes[$currentDate]['quantity'][$activity->activity_type_id] = 0;
                }
                $report->timeframes[$currentDate]['quantity'][$activity->activity_type_id] += $activity->quantity;

            }

            $this->response->setData($report);
            return $this->response;
        }
    }


}
