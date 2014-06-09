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

    public function find($criteria = array())
    {

       $activities = Activity::find_by_sql('
            SELECT *
            FROM  `activity` 
            LEFT JOIN  `activity_type` ON  `activity`.`activity_type_id` =  `activity_type`.`id` 
            WHERE  `activity`.`user_id` = '.$criteria['user_id'].'
            AND  `activity`.`id` = '.$criteria['id'].'
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
        if(isset($criteria['user_id'])){
            $conditionString .= " AND user_id = ?";
            $conditionBindings[] = $criteria['user_id'];
        }


        $types = ActivityType::find('all', array(
            'order' => 'name asc',
            'conditions' => array_merge(array($conditionString), $conditionBindings)
        ));

        $this->response->setData($this->resultsToArray($types));
        return $this->response;
    }

    public function updateType($criteria = array())
    {
        $type = ActivityType::find($criteria['id']);
        $type->update_attributes($criteria);

        $this->response->setData($type->to_array());
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
            $report->activity_types = array();

            $report->date_range = array(
                "start_date" => null,
                "end_date" => null
            );
            $report->polarity = array(
                "good" => null,
                "bad" => null
            );

            foreach ($activities as $activity ){

                // TIMEFRAME STATS
                $date = date("Y-m-d", strtotime($activity->date_added));
                if($currentDate != $date){
                    $currentDate = $date;
                    $report->timeframes[$currentDate] = array();
                    $report->timeframes[$currentDate]['polarity'] = array();
                    $report->timeframes[$currentDate]['polarity']['good'] = 0;
                    $report->timeframes[$currentDate]['polarity']['bad'] = 0;
                    $report->timeframes[$currentDate]['occurrence'] = array();
                    $report->timeframes[$currentDate]['quantity'] = array();
                }

                $report->timeframes[$currentDate]['logs'][] = array(
                    "id" => $activity->id,
                    "activity_type_id" => $activity->activity_type_id,
                    "quantity" => $activity->quantity,
                    "name" => $activity->name,
                    "note" => $activity->note,
                    "polarity" => $activity->polarity,
                    "date_added" => $activity->date_added
                );

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

                // ACTIVITY TYPE STATS
                if(!isset($report->activity_types[$activity->activity_type_id])) {
                    $report->activity_types[$activity->activity_type_id] = array(
                        "activity_type_id" => $activity->activity_type_id,
                        "quantity" => 0,
                        "occurrence" => 0,
                        "name" => $activity->name,
                        "polarity" => $activity->polarity,
                        "start_date" => null,
                        "end_date" => null
                    );
                }
                $report->activity_types[$activity->activity_type_id]['quantity'] += $activity->quantity;
                $report->activity_types[$activity->activity_type_id]['occurrence']++;

                if (is_null($report->activity_types[$activity->activity_type_id]['start_date'])) {
                    $report->activity_types[$activity->activity_type_id]['start_date'] = $activity->date_added;
                } else if (strtotime($report->activity_types[$activity->activity_type_id]['start_date']) > strtotime($activity->date_added) ) {
                    $report->activity_types[$activity->activity_type_id]['start_date'] = $activity->date_added;
                }

                if (is_null($report->activity_types[$activity->activity_type_id]['end_date'])) {
                    $report->activity_types[$activity->activity_type_id]['end_date'] = $activity->date_added;
                } else if (strtotime($report->activity_types[$activity->activity_type_id]['end_date']) < strtotime($activity->date_added) ) {
                    $report->activity_types[$activity->activity_type_id]['end_date'] = $activity->date_added;
                }

                // OVERALL STATS
                if (is_null($report->date_range['start_date'])) {
                    $report->date_range['start_date'] = $activity->date_added;
                } else if (strtotime($report->date_range['start_date']) > strtotime($activity->date_added) ) {
                    $report->date_range['start_date'] = $activity->date_added;
                }

                if (is_null($report->date_range['end_date'])) {
                    $report->date_range['end_date'] = $activity->date_added;
                } else if (strtotime($report->date_range['end_date']) < strtotime($activity->date_added) ) {
                    $report->date_range['end_date'] = $activity->date_added;
                }
                if($activity->polarity > 0){
                    $report->polarity['good']++;
                } else {
                    $report->polarity['bad']++;
                }


            }

            $this->response->setData($report);
            return $this->response;
        }
    }


}
