<?php 

namespace models;

class UserSyncStatus extends BaseModel{
   
   protected $_name = 'user_sync_status';
   
   /**
    * mark user as unsynced in cased user is updated
    * @param int $userId id of the user to be marked as unsynced
    * @return boolean
    * **/
   public function markUserAsUnsynced($userId){         
      $userRow = \R::findOrCreate($this->_name,array("users_id"=>$userId));
      $userRow->ams_sync_status = 0;
      $userRow->amt_sync_status = 0;
      $userRow->dmb_sync_status = 0;
    
      try{
         $this->_redBeans->store($userRow);
       
//         //lets send ipn to all clients
//         $objIpnHandler = new ipn\IpnHandler();
//         $objIpnHandler->sendIpnForUpdatedUser($userId);
         return true;
      
      }
      catch(\Exception $ex){
         return false;
      } 
      return false;
     
   }
   
   /**
    * Function to get row from user id
    * @param int $userId id of the user
    * @return \RedBeanPHP\OODBBean |boolean
    * **/
   public function getRowByUserId($userId){
      $userRow = \R::findOne($this->_name,'users_id = :uId',array(":uId"=>$userId));
      return $userRow;
   }
}
?>