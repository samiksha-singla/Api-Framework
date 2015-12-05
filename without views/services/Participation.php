<?php
namespace services;

class Participation extends BaseService{
  
     
     public function actionSetUserEventParticipation(){
        $objValidator     = new helpers\Validation();
        $params = array('event', 'user_token','participation');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $eventId    = $this->_request->getPost('event',null);
                $dataToInsert = array(
                                      'events_id'          =>$eventId,
                                      'participation_id'   =>$this->_request->getPost('participation',null),
                                     );
                
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403,'User token invalid');
                }             
                $objUsersEventParticipation = new \models\UsersEventsParticipation();
                $dataToInsert['users_id']    = $userDetails->id;  
                $isStatusSaved              = $objUsersEventParticipation->insertUserEventParticipation($dataToInsert,$userDetails);
                if($isStatusSaved){
                     // send push notification 
                      $objNotifier = new \models\Pushnotifications();
                      $objNotifier->participationNotifications($eventId, $userDetails);
                     $this->_request->sendSuccessResponse('Status Saved');         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'Error saving status');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
}
?>

