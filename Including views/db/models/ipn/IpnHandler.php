<?php
namespace models\ipn;

class IpnHandler{
   
   /**
    * Function to send notification to all clients
    * **/
   public function sendIpnForUpdatedUser($userId){
      
     // send ipn to AMT
     $objAmtIpnHndlr = new AmtIpnHandler();
     $objAmtIpnHndlr->sendIpnForUser($userId);
      
   }
}


?>
