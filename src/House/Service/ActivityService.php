<?php
require_once("models/Activity.php");
require_once("models/ActivityType.php");
require_once("House/Service/BaseService.php");
require_once("House/Service/GoalService.php");

class ActivityService extends BaseService
{

    public function find($criteria = array())
    {

        $sql = '
            SELECT 
            `activity`.id,
            `activity`.activity_type_id,
            `activity`.quantity,
            `activity`.note,
            `activity`.date_added,
            `activity_type`.name,
            `activity_type`.polarity,
            IF(`goal`.`id` IS NULL,NULL,"Y") as has_goal
            FROM  `activity` 
            LEFT JOIN  `activity_type` ON  `activity`.`activity_type_id` =  `activity_type`.`id`
            LEFT JOIN `goal` ON `goal`.`activity_type_id` = `activity_type`.`id`
            WHERE  `activity`.`user_id` = '.$criteria['user_id'].'
            '. ( isset($criteria['id']) ? ' AND  `activity`.`id` = '.$criteria['id'] : null) .'
            '. ( isset($criteria['start_date']) ? ' AND  `activity`.`date_added` >= "'.date("Y-m-d g:i:s", $criteria['start_date']).'"' : null) .' 
             
            GROUP BY `activity`.`id`
            ORDER BY  `activity`.`date_added` DESC 
        ';

        $activities = Activity::find_by_sql($sql);

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

    public function update($criteria)
    {
        $activity = Activity::find($criteria['id']);
        $activity->update_attributes($criteria);

        $this->response->setData($activity->to_array());
        return $this->response;
    }

    public function report($criteria)
    {
        $activities = Activity::find_by_sql('
            SELECT 
            `activity`.*,  
            `activity_type`.name, 
            `activity_type`.polarity,
            IF(`goal`.`id` IS NULL,NULL,"Y") as has_goal
            FROM  `activity` 
            LEFT JOIN  `activity_type` ON  `activity`.`activity_type_id` =  `activity_type`.`id`
            LEFT JOIN `goal` ON `goal`.`activity_type_id` = `activity_type`.`id`
            WHERE  `activity`.`user_id` = '.$criteria['user_id'].'
            ORDER BY  `activity`.`date_added` ASC
        ');

        $goals = GoalService::find($criteria);
        $goalsByActivity = array();
        foreach($goals->data as $goal) {
            $goalsByActivity[$goal['activity_type_id']] = $goal;
        }

        // REPORT BY DAY
        if($criteria['timeframe']=="day"){
            
            $currentDate = null;

            $report = new stdClass();
            $report->days = array();
            $report->weeks = array();
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
                    $report->days[$currentDate] = array();
                    $report->days[$currentDate]['polarity'] = array();
                    $report->days[$currentDate]['polarity']['good'] = 0;
                    $report->days[$currentDate]['polarity']['bad'] = 0;
                    $report->days[$currentDate]['occurrence'] = array();
                    $report->days[$currentDate]['quantity'] = array();
                    $report->days[$currentDate]['goals'] = array(
                        "day" => array(),
                        "week" => array()
                    );

                    //build week based array
                    //find the monday before this date if today is not monday
                    $currentWeek = date('N', strtotime($date)) == 1 ? $date : date("Y-m-d", strtotime('previous monday', strtotime($date)));
                    if(!isset($report->weeks[$currentWeek])){
                        $report->weeks[$currentWeek] = array(
                            "goals"=>array()
                        );
                    }

                }

                $report->days[$currentDate]['logs'][] = array(
                    "id" => $activity->id,
                    "activity_type_id" => $activity->activity_type_id,
                    "quantity" => $activity->quantity,
                    "name" => $activity->name,
                    "note" => $activity->note,
                    "polarity" => $activity->polarity,
                    "has_goal" => $activity->has_goal,
                    "date_added" => $activity->date_added
                );

                if($activity->polarity > 0){
                    $report->days[$currentDate]['polarity']['good']++;
                } else {
                    $report->days[$currentDate]['polarity']['bad']++;
                }

                if(!isset($report->days[$currentDate]['occurrence'][$activity->activity_type_id])){
                    $report->days[$currentDate]['occurrence'][$activity->activity_type_id] = 0;
                }
                $report->days[$currentDate]['occurrence'][$activity->activity_type_id]++;

                if(!isset($report->days[$currentDate]['quantity'][$activity->activity_type_id])){
                    $report->days[$currentDate]['quantity'][$activity->activity_type_id] = 0;
                }
                $report->days[$currentDate]['quantity'][$activity->activity_type_id] += $activity->quantity;

                //goals for the day
                if(isset($goalsByActivity[$activity->activity_type_id])){
                    $goal = $goalsByActivity[$activity->activity_type_id];

                    //if weekly, then grab this week's existing entries for this weekly goal
                    if ($goal['timeframe']=="week") {
                        if(!isset($report->weeks[$currentWeek]['goals'][$goal['id']])){
                            $report->weeks[$currentWeek]['goals'][$goal['id']] = array(
                                "occurrence_count" => 0,
                                "activity_type_id" => $goal['activity_type_id'],
                                "operator" => $goal['operator'],
                                "occurrence" => $goal['occurrence'],
                                "timeframe" => $goal['timeframe']
                            );
                        }
                        $report->weeks[$currentWeek]['goals'][$goal['id']]['occurrence_count']++;

                        $report->days[$currentDate]['goals'][$goal['timeframe']][$goal['id']] = $report->weeks[$currentWeek]['goals'][$goal['id']];

                    } else if ($goal['timeframe']=="day") {
                        if(!isset($report->days[$currentDate]['goals'][$goal['timeframe']][$goal['id']])){
                            $report->days[$currentDate]['goals'][$goal['timeframe']][$goal['id']] = array(
                                "occurrence_count" => 0,
                                "activity_type_id" => $activity->activity_type_id,
                                "operator" => $goal['operator'],
                                "occurrence" => $goal['occurrence'],
                                "timeframe" => $goal['timeframe']
                            );
                        }

                        $report->days[$currentDate]['goals'][$goal['timeframe']][$goal['id']]['occurrence_count']++;
                    }

                }


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

            $report->days = array_reverse($report->days);
            foreach ($report->days as $date=>$day) {

                $report->days[$date]['logs'] = array_reverse($day['logs']);
            }
            // die();
            $this->response->setData($report);
            return $this->response;
        }
    }


}
