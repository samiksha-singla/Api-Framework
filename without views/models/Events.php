<?php
namespace models;
class Events extends BaseModel{

   protected $_name = 'events';

  
  /** Function to insert new Event
   * @param  array $eventData containg to be inserted for event
   * @param  \RedBeanPHP\OODBBean $user user who is creating the event
   * @retrun boolean
   * **/
  public function insertEvent(array $eventData,\RedBeanPHP\OODBBean $user){
     try{        
         //check if event with same name is already associated with user
         $associatedRow = $user->withCondition('event_name = ? LIMIT 1',array($eventData['event_name']))
                                ->ownEventsList;
         if(!$associatedRow){
            $event = $this->_redbeans->dispense('events');
            foreach($eventData as $column=>$data){
              $event->$column = $data;
            }
            $user->ownEventsList[] = $event;
            return $this->_redbeans->store($user);
         }
        else {
           throw new \Exception('Cannot insert event with same name');
        }
     }
     catch(\Exception $e){
        return false;
     }
     return false;
  }
  
 
 /**
  * Function to get event details by event id
  * @param  int $eventId Id of the event for which details are to be found
  * @param  int $userId  Id of the user who is seeing the event
  * @return array
  * **/
  public function getEventDetails($eventId,$userId){
     
     $return['event_details']            = array();
     $return['userParticiaptionStatus']  = null;
     $return['participantDetails']       = array();
     // get event details
     $eventDetails                =  \R::findOne($this->_name,'id = :eid AND status = 1',array(':eid'=>$eventId));
     if($eventDetails){
         // get details of participants
         $getUserParticipationDetails = "SELECT u.id as userid ,u.first_name,u.last_name ,upe.participation_id
                                        FROM users_events_participation upe 
                                        JOIN users u ON upe.users_id = u.id AND upe.events_id = :eId
                                        WHERE upe.participation_id IN (1,3)
                                        ORDER BY (u.id = :uId) DESC, u.id";

         $rows                        = \R::getAll($getUserParticipationDetails, array(':eId'=>$eventId,':uId'=>$userId));
         $return['event_details']     = $eventDetails->export(false);    
         $usersParticpating = array(); // array containing profile pic of the user who are participating
         $objServerInfo    = new \services\helpers\ServerInfo();
         if( !empty($rows)){
            // if first row is for given user then handle it accordingly
            if($rows[0]['userid'] == $userId){
                $return['userParticiaptionStatus']  = $rows[0]['participation_id'];
               unset($rows[0]);
            }
            // details f other participants
            foreach($rows as $record){
              $profilepic = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$record['userid'].'_pp.jpg';
              $picPath    = APPLICATION_DIR."/images/{$record['userid']}_pp.jpg";
              $record['profilepic'] = (@is_file($picPath))?$profilepic:null;
              $usersParticpating[] = $record;
            }    
            
         }

         $return['participantDetails'] = $usersParticpating;
        
      }       
     return $return;  
  }
  
  /**
   * Public funtion to get user events
   * @param  \RedBeanPHP\OODBBean $userDetails user for whom events are to be searched
   * @return array
   * **/
  
  public function getUserOwnedEvents(\RedBeanPHP\OODBBean $userDetails){
     
     $query = "SELECT e.* , u.first_name,u.last_name, COUNT( IF( upe.participation_id = 1, 1, NULL ) ) as num_users_psyes,
             COUNT( IF( upe.participation_id = 3, 1, NULL ) )as num_users_psmaybe
             FROM events e
             INNER JOIN users u on u.id = e.users_id
             LEFT JOIN `users_events_participation` upe ON upe.events_id = e.id AND upe.users_id = u.id
             WHERE e.users_id = :uid
             GROUP BY e.id";    
     $events =  \R::getAll($query,array(':uid'=>$userDetails->id));
     return $events;
  } 
  
  
  /**
   * public function search event 
   * @param string $query query to be used for search
   * @return array 
   * **/
  public function searchEvents($query){
     $sql = "SELECT e.id,event_name,e.city,e.country,start_time,end_time,description,
             u.first_name,u.last_name, COUNT( IF( upe.participation_id = 1, 1, NULL ) ) as num_users_psyes,u.id as userid,
             COUNT( IF( upe.participation_id = 3, 1, NULL ) )as num_users_psmaybe
             FROM events e
             INNER JOIN users u on u.id = e.users_id
             LEFT JOIN `users_events_participation` upe ON upe.events_id = e.id 
             WHERE e.country like :query OR e.city like :query OR e.address like :query
             GROUP BY e.id";
     
     $rowset = \R::getAll($sql,array(':query'=>"%$query%"));
     $objServerInfo    = new \services\helpers\ServerInfo();
     $return = array();
     if($rowset){
        foreach($rowset as $row){
           $profileUrl = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$row['userid']."_pp.jpg";
           $picPath    = APPLICATION_DIR."/ïmages/{$row['userid']}_pp.jpg";
           $profilePic = (@is_file($profileUrl))?$profileUrl:null;
           $row['profilepic'] = $profileUrl;
           $return[] = $row;
        }
        return $return;
     }
     return array();
  } 


 /**
   * Public function to get nearby events
   * @param string $longitude longitude of current user location
   * @param string $latitude  latitude of current user location
   * @return array
   * **/
  public function getEventNearbyEvents($longitude, $latitude){
     $qry = "SELECT e.*,(((acos(sin((:lat*pi()/180)) * 
             sin((e.`Latitude`*pi()/180))+cos((:lat*pi()/180)) * 
             cos((e.`Latitude`*pi()/180)) * cos(((:long- e.`Longitude`)* 
             pi()/180))))*180/pi())*60*1.1515
        ) as distance ,u.first_name,u.last_name,u.id as userid,
        COUNT( IF( upe.participation_id = 1, 1, NULL ) ) as num_users_psyes,COUNT( IF( upe.participation_id = 3, 1, NULL ) )as num_users_psmaybe
        FROM `events` e
        INNER JOIN users u on u.id = e.users_id
        LEFT JOIN `users_events_participation` upe ON upe.events_id = e.id
        GROUP BY e.id
        HAVING distance <= 19 ORDER BY distance"; // distance in miles 30 kms
          
      $rowset = \R::getAll($qry,array(':lat'=>$latitude,':long'=>$longitude));
      $return = array();
      $objServerInfo    = new \services\helpers\ServerInfo();
      if($rowset){
         foreach($rowset as $row){
            unset($row['status']);
            unset($row['users_id']);
            unset($row['distance']);
            $profileUrl = $objServerInfo->getScheme()."://".$objServerInfo->getHost().'/images/'.$row['userid']."_pp.jpg";
            $profilePic = (@getimagesize($profileUrl))?$profileUrl:null;
            $row['profilepic'] =$profilePic ;
            $return[] = $row;
         }
      }
      return $return;
  }
  
  
  /**
   * Function to update event
   * @param  \RedBeanPHP\OODBBean $event event to be updated
   * @param  array $updates data to be updated
   * @return boolean
   * **/
  public function updateEvent(\RedBeanPHP\OODBBean $event , array $updates){
     
     $updates['start_time'] = strtotime($updates['starttime']);
     $updates['end_time'] = strtotime($updates['endtime']);
     
     unset($updates['starttime']);
     unset($updates['endtime']);
     
     
     // set data to be saved
     foreach($updates as $dbColumn=>$value){
        $event->$dbColumn = $value;
     }

     try{
        $this->_redbeans->store($event);
     }
     catch(\Exception $e){
        echo $e->getMessage();
        return false;
     }
     return true;
  }
    
  
  /**
   * Public funtion to get event by id
   * @param  int $eventId Id of the event to be found
   * @return \RedBeanPHP\OODBBean
   * **/
  public function findEventById($eventId){
     $event = $this->_redbeans->load($this->_name, $eventId);
     return $event;
  }

}
?>

