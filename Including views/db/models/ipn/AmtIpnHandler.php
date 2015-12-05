<?php
namespace models\ipn;

class AmtIpnHandler extends \api\Client{
   
   
   public function __construct() {
      
      $config = $this->_getConfig();   
      $url = $config['amtUrl']."/api/ssoipn";
      $accessKeyId = '6E756D365AAEFDCEF892FF64C1FE9';
      $secretKey = 'D92881BCEA22376D54B4FFA74DA4F';
      parent::__construct($url, $accessKeyId, $secretKey);
   }

   /**
    * Function to send notification to all clients
    * @param int $userId id of the user for which ipn request is to be sent
    * @return void
    * **/
   public function sendIpnForUser($userId){
      $objUserModel = new \models\Users;
      $userRow = $objUserModel->getUserById($userId);
      if($userRow){
         $dataToSend = array(
                             'first_name'=>$userRow->first_name,
                             'last_name'=>$userRow->last_name,
                             'email_address'=>$userRow->email,
                             'username'=>$userRow->user_name,
                             'cimba_auth_id'=>$userRow->id,
                            ); 
         $this->_parameters = $dataToSend;
         $clientResponse = $this->_request();
         $status = -1; //mark synced status as ipn request sent
         $objUserSyncStatusModel = new \models\UserSyncStatus;
         $userSyncStatusRow = $objUserSyncStatusModel->getRowByUserId($userRow->id);
         if($clientResponse){
            $status = $clientResponse['data']['status']; 
            
         }
         if($userSyncStatusRow){
            $userSyncStatusRow->amt_sync_status = $status;         
            $objUserSyncStatusModel->getRedBeans()->store($userSyncStatusRow);
         }
      }
      
   }
}


?>
