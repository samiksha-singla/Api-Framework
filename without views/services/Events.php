<?php
namespace services;

class Events extends BaseService{
   
   public function actionCreate(){    
     $objValidator     = new helpers\Validation();
     $params = array('event_name', 'city','country','description','user_token','starttime','endtime','longitude','latitude','address');

        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);

                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }
                
                $eventData = array(
                                     'event_name'   =>$this->_request->getPost('event_name'),
                                     'city'         =>$this->_request->getPost('city'),
                                     'country'      =>$this->_request->getPost('country'),
                                     'description'  =>$this->_request->getPost('description'),
                                     'address'      =>$this->_request->getPost('address'), 
                                     'start_time'   =>strtotime($this->_request->getPost('starttime')),
                                     'end_time'     =>strtotime($this->_request->getPost('endtime')),
                                     'longitude'    =>$this->_request->getPost('longitude'),
                                     'latitude'     =>$this->_request->getPost('latitude'),
                                     'date_created' =>date('Y-m-d H:i:s')
                                  );
                try{
                  $isEventInserted = $objEventsModel->insertEvent($eventData,$userDetails);
                  if($isEventInserted){
                       // send push notification 
                       $objNotifier = new \models\Pushnotifications();
                       $objNotifier->eventCreateNotifications($eventData, $userDetails);
                       $this->_request->sendSuccessResponse('Event Inserted');         
                  }
                  else{
                       $this->_request->sendErrorResponse(500,500,'Error inserting event');
                  }         
                }
                catch(\Exception $ex ){
                    $this->_request->sendErrorResponse(500,500,$ex->getMessage());
                }
                   
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     public function actionGetEventDetails(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','event_id');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $eventId        = $this->_request->getPost('event_id',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);

                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                $eventDetails = $objEventsModel->getEventDetails($eventId,$userDetails->id);
                if($eventDetails){
                     $this->_request->sendSuccessResponse('Success',$eventDetails);         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'Error finding event details');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }  
          
     // get events created by user
     public function actionGetEventsByUser(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                $eventsList = $objEventsModel->getUserOwnedEvents($userDetails);
                if($eventsList){
                     $this->_request->sendSuccessResponse('Success',$eventsList);         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'This user has no event associated');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     //search events
     public function actionSearchEvents(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','query');
         
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                $query      = $this->_request->getPost('query',null);
                $eventsList = $objEventsModel->searchEvents($query);
                if($eventsList){
                     $this->_request->sendSuccessResponse('Success',$eventsList);         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'Error finding events');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     
     // get nearby events
     public function actionNearby(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','longitude','latitude');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $longitude      = $this->_request->getPost('longitude',null);
                $latitude       = $this->_request->getPost('latitude',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);

                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                $eventsList = $objEventsModel->getEventNearbyEvents($longitude,$latitude);
                if($eventsList){
                     $this->_request->sendSuccessResponse('Success',$eventsList);         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'No events found');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     /**
      * Function to update event
      * **/
     public function actionEventUpdate(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','eventid','updates');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $eventId        = $this->_request->getPost('eventid',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                
                //check if event exists or not
                $eventRow = $objEventsModel->findEventById($eventId);
                if(!$eventRow){
                   $this->_request->sendErrorResponse(403,403, 'Invalid Event'); 
                }
                
                // check if event is owned by user
                $isUserEventOwner = ($eventRow->users_id == $userDetails->id)?true:false;
                
                if(!$isUserEventOwner){
                    $this->_request->sendErrorResponse(403,403, 'User is not authenticated to edit event');
                }
                
                $updates    = $this->_request->getPost('updates',null);
                $updateArr  = json_decode($updates,true);
                
                // check if update data is valid json or not
                if(!$updateArr){
                   $this->_request->sendErrorResponse(403,403, 'Invalid update data');
                }
                
                
                $isEventUpdated = $objEventsModel->updateEvent($eventRow,$updateArr );
                if($isEventUpdated){
                     // send push notification 
                     $objNotifier = new \models\Pushnotifications();
                     $objNotifier->participationNotifications($eventRow, $userDetails);
                     $this->_request->sendSuccessResponse('Event Successfully updated');         
                }
                else{
                     $this->_request->sendErrorResponse(404,404,'Error updating event');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     /**
      * Public function action delete event
      * 
      * **/
     public function actionDeleteEvent(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','eventid');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $eventId        = $this->_request->getPost('eventid',null);
                $objUserAuthMdl = new \models\Users();
                $objEventsModel = new \models\Events();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                
                //check if event exists or not
                $eventRow = $objEventsModel->findEventById($eventId);
                if(!$eventRow->id){
                   $this->_request->sendErrorResponse(403,403, 'Invalid Event'); 
                }               
              
                // check if event is owned by user
                $isUserEventOwner = ($eventRow->users_id == $userDetails->id)?true:false;
                
                if(!$isUserEventOwner){
                    $this->_request->sendErrorResponse(403,403, 'User is not authenticated to delete event');
                }
               
                try{
                   \R::trash($eventRow); 
                   $this->_request->sendSuccessResponse('Event successfully deleted');
                   
                }
                catch(Exception $ex){
                   $this->_request->sendErrorResponse(403,403, 'Error deleting event');
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

