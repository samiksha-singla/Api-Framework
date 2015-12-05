<?php
namespace models;
class Pushnotifications extends \notifier\Notifier{
 
   /**
    * Function to send notification when events is created
    * @param array $eventDetails Details of event which is added
    * @param \RedBeanPHP\OODBBean $userDetails Details of owner of event
    * @return void
    * **/
   public function eventCreateNotifications(array $eventDetails,\RedBeanPHP\OODBBean $userDetails){
      // get all users in range of 100 kms
      $objUserModel = new Users;
      $requiredTokens = $objUserModel->getUserDevicesWithinDistance(100, $eventDetails['longitude'],$eventDetails['latitude'],$userDetails->id);
      $message = "{$userDetails->first_name} {$userDetails->last_name} has created event {$eventDetails['event_name']}";
      $this->_sendNotifications($requiredTokens, $message);  
   }
   
   /**
    * Function to send notification when events is updated
    * @param \RedBeanPHP\OODBBean $eventDetails Details of event which is added
    * @return void
    * **/
   public function eventUpdateNotifications(\RedBeanPHP\OODBBean $eventDetails){
      // get the users who are participating in event
      
      $getUserParticipationDetails = "SELECT u.device_token,u.device_type
                                        FROM users_events_participation upe 
                                        JOIN users u ON upe.users_id = u.id AND upe.events_id = :eId
                                        WHERE upe.participation_id IN (1,3)";
      
      $participantDevices  = \R::getAll($getUserParticipationDetails, array(':eId'=>$eventDetails->id));   
      
      $objUserModel = new Users;
      $requiredTokens = $objUserModel->getUserDevicesWithinDistance(100, $eventDetails->longitude, $eventDetails->latitude,$eventDetails->users_id);
      $message = "{$eventDetails->event_name} is updated";
      $this->_sendNotifications(array_merge($requiredTokens,$participantDevices), $message);     
   }
   
   
   /**
    * Function to send message to the devices 
    * @param $devices array of the devices
    * @param $message messages
    * @return void
    * **/
   public function _sendNotifications($devices, $message){
      $androidDevices = array();
      $iphoneDevices = array();
      foreach($devices as $device){
         switch ($device['device_type']){
            case 1:
               $iphoneDevices[] = $device['device_token'];
               break;
            case 2;
               $androidDevices[] = $device['device_token'];
               break;
         }
      }
      
      if(!empty($iphoneDevices)){
         $this->send_notification($message, $iphoneDevices, 1);
      }
      if(!empty($androidDevices)){
         $this->send_notification($message, $androidDevices, 2);
      }
   } 




  /**
    * Function to send notification when users changes the participation changes
    * @param int  $eventId id of event which is added
    * @param \RedBeanPHP\OODBBean $participantDetails Details of user who is participating
    * @return void
    * **/
   public function participationNotifications($eventId, $participantDetails){
      $eventDetails = \R::load('events',$eventId);     
      $eventOwnerDetails = \R::load('users', $eventDetails->users_id);
      $deviceType = $eventOwnerDetails->device_type;
      $deviceToken = $eventOwnerDetails->device_token;
      $message = "{$participantDetails->first_name} is coming to event {$eventDetails->event_name}";    
      $this->send_notification($message, array($deviceToken), $deviceType);
   }
   
}

?>
