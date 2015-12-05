<?php
namespace services;

class Expert extends BaseService{ 
   
   public function actionGetAllProfiles(){
      $objValidator     = new helpers\Validation();
        $params = array('user_token');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $objProfileMdl  =  new \models\Profiles();
               
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $profiles = $objProfileMdl->getAllProfiles();
                if($profiles){
                     $this->_request->sendSuccessResponse('success',$profiles);                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Profiles not found');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
   }


   public function actionSetUserProfile(){
        $objValidator     = new helpers\Validation();
        $params = array('profile_id','user_token');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $objUserProfileMdl  =  new \models\UsersProfiles();
                $profileId = $this->_request->getPost('profile_id',null);
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $isProfileAdded = $objUserProfileMdl->insertUserProfile($userDetails->id,$profileId);
                if($isProfileAdded === true){
                     $this->_request->sendSuccessResponse('User Profile Inserted');                  
                }
                elseif($isProfileAdded === -1){
                   $this->_request->sendErrorResponse(404,404,'User is already registered with this profile');
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Error inserting user profile');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     } 
     
     
     public function actionGetExpertsByProfile(){
        $objValidator     = new helpers\Validation();
        $params = array('user_token','profile_id');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $obUserProfileMdl  =  new \models\UsersProfiles();
               
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $profileId = $this->_request->getPost('profile_id',null);
                $profiles = $obUserProfileMdl->searchUsersByProfile($profileId);
                if($profiles){
                     $this->_request->sendSuccessResponse('success',$profiles);                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Profiles not found');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     public function actionSearchExpertsByProfile(){
        $objValidator     = new helpers\Validation();
        $params = array('user_token','query');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $obUserProfileMdl  =  new \models\UsersProfiles();
               
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $profileQuery = $this->_request->getPost('query',null);
                $profiles = $obUserProfileMdl->searchUsersByProfile($profileQuery);
                if($profiles){
                     $this->_request->sendSuccessResponse('success',$profiles);                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Profiles not found');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     public function actionSearchExpertsByLocation(){
        $objValidator     = new helpers\Validation();
        $params = array('user_token','query');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $obUserProfileMdl  =  new \models\UsersProfiles();
               
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $query = $this->_request->getPost('query',null);
                $profiles = $obUserProfileMdl->searchUsersByLocation($query);
                if($profiles){
                     $this->_request->sendSuccessResponse('success',$profiles);                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Profiles not found');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
}
?>

