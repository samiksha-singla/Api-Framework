<?php
namespace services;

class Users extends BaseService{
   
   public function actionForgotPassword(){
     $objValidator     = new helpers\Validation();
     $params = array('email');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $email = $this->_request->getPost('email');
                $objUserAuthMdl =  new \models\Users();
                // check if user is valid or not
                $return = $objUserAuthMdl->resetPassword($email);
                if($return === 1){
                   $this->_request->sendSuccessResponse('success',array());         
                }
                else if($return === -1)
                {
                   $this->_request->sendErrorResponse(403,403,'User not found');
                }
                else if($return === -2){
                   $this->_request->sendErrorResponse(404,404,'Error sending mail');
                }
                else{
                   $this->_request->sendErrorResponse(404,404,'oops!! something went wrong');
                }
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }      
   }
   
   
   public function actionLogout(){
     $objValidator     = new helpers\Validation();
        $params = array('user_token');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $dataToInsert = array(
                                      'events_id'          =>$this->_request->getPost('event',null),
                                      'participation_id'   =>$this->_request->getPost('participation',null),
                                     );
                
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403,'User token invalid');
                }             
                $objUsersModel = new \models\Users();
                try{
                   $objUserMdl->voidUserToken($userToken);
                   $this->_request->sendSuccessResponse('User successfully logout');
                }
                catch (\Exception $ex){
                    $this->_request->sendErrorResponse(500,500,'Unable to logout user');
                }
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        } 
   }
   
   public function actionAuthenticate(){    
     $objValidator     = new helpers\Validation();
     $params = array('email', 'password','devicetoken','devicetype');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userName = $this->_request->getPost('email');
                $password = $this->_request->getPost('password');
                $deviceToken = $this->_request->getPost('devicetoken');
                $deviceType = $this->_request->getPost('devicetype');
                $objUserAuthMdl =  new \models\Users();

                // check if user is valid or not
                $userRow = $objUserAuthMdl->authenticate($userName, $password);
                if($userRow){
                     // update user device token and type
                     $userRow->device_token = $deviceToken;
                     $userRow->device_type = $deviceType;
                     \R::store($userRow);
                     $this->_request->sendSuccessResponse('User successfully logged in',array('token'=>$userRow->token));         
                 }
                 else
                 {
                     $this->_request->sendErrorResponse(404,404,'Invalid username or password');
                 }
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
   
     
     
     public function actionSignup(){
        $objValidator     = new helpers\Validation();
        $params = array('first_name','last_name','email','city','country','password','longitude','latitude','gender','phone','address','devicetoken','devicetype');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $email    = $this->_request->getPost('email',null);
                $password = $this->_request->getPost('password',null);
                $objUserAuthMdl =  new \models\Users();                
                $dataToInsert = array('first_name' =>$this->_request->getPost('first_name',null),
                                      'last_name'  =>$this->_request->getPost('last_name',null),
                                      'email'      =>$email,
                                      'password'   =>md5($password),
                                      'city'       =>$this->_request->getPost('city',null),
                                      'country'    =>$this->_request->getPost('country',null),
                                      'longitude'  =>$this->_request->getPost('longitude',null),
                                      'latitude'   =>$this->_request->getPost('latitude',null),
                                      'gender'      =>$this->_request->getPost('gender',null),
                                      'phone'       =>$this->_request->getPost('phone',null),
                                      'address'     =>$this->_request->getPost('address',null),
                                      'websiteurl'  =>$this->_request->getPost('websiteurl',null),
                                      'device_token'=> $this->_request->getPost('devicetoken',null),
                                      'device_type' => $this->_request->getPost('devicetype',null)
                                     );
                
                // check if user is valid or not
                $return  = $objUserAuthMdl->insertUser($dataToInsert);
                if($return && $return > 0){
                     $profilePic = $this->_request->getPost('profilepic',null);
                     $isProfilePicPosted = ($profilePic !== null)?true:false;
                     $isProfilePicSaved  = true;
                     if($isProfilePicPosted){
                        // handle profile picture 
                        $userId             = $return;
                        $objImageProcessor  = new helpers\ImageProcessor();
                        $dpPath             = __DIR__.'/../images/'.$userId.'_pp.jpg';
                        $isProfilePicSaved  = $objImageProcessor->convertBase64ToImage($profilePic, $dpPath);
                     }
                     if($isProfilePicSaved){
                        // authenticate user and return token
                        $userAuthRow = $objUserAuthMdl->authenticate($email, $password);                       
                        $this->_request->sendSuccessResponse('User successfully registered',array('token'=>$userAuthRow->token));  
                     }
                     else{
                        $this->_request->sendErrorResponse(404,404,'User registered but profile picture not saved');
                     }
                            
                 }
                 else
                 {
                    if($return == -1){
                         $this->_request->sendErrorResponse(404,404,'Email alredy exists');
                    }
                    else  if($return == -2){
                          $this->_request->sendErrorResponse(404,404,'Invalid email address');
                    }
                    else{
                         $this->_request->sendErrorResponse(404,404,'Error registering user please try later');
                    }
                   
                 }
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     public function actionGetUserDetails(){
        $objValidator     = new helpers\Validation();
        $objServerInfo    = new helpers\ServerInfo();
        $params = array('user_token');

        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
               $objUserAuthMdl  =  new \models\Users();           
               $userToken       = $this->_request->getPost('user_token',null);
               $userDetails     = $objUserAuthMdl->getUserFromToken($userToken);
              
               if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403,'User token invalid');
               }
               else
               {              
                   $data               = $userDetails->export();                  
                   $userOwnedDogs      = $objUserAuthMdl->getUserOwnedDogs($userDetails->id);
                   $profilepic         = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$data['id']."_pp.jpg";
                   $picFilePath        = APPLICATION_DIR."/images/{$data['id']}_pp.jpg";
                   $data['profilepic'] = (@is_file($picFilePath))?$profilepic:null;
                   $data['owneddogs']  = $userOwnedDogs;
                   $data['expertData'] = $objUserAuthMdl->getUserProfiles($userDetails);
                   $this->_request->sendSuccessResponse('Success',$data);         
               }                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     
     public function actionGetUserById(){
        $objValidator     = new helpers\Validation();
        $objServerInfo    = new helpers\ServerInfo();
        $params = array('user_token','user_id');

        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
               $objUserAuthMdl  =  new \models\Users();           
               $userToken       = $this->_request->getPost('user_token',null);
               $userDetails     = $objUserAuthMdl->getUserFromToken($userToken);
              
               if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403,'User token invalid');
               }
               else
               {           
                   $userId             = $this->_request->getPost('user_id',null);
                   $userRow            = \R::load('users', $userId);
                   if($userRow->id){
                      $data               = $userRow->export();                  
                      $userOwnedDogs      = $objUserAuthMdl->getUserOwnedDogs($userId);
                      $profilepic         = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$data['id']."_pp.jpg";
                      $picFilePath        = APPLICATION_DIR."/images/{$data['id']}_pp.jpg";
                      $data['profilepic'] = (@is_file($picFilePath))?$profilepic:null;
                      $data['owneddogs']  = $userOwnedDogs;
                      $data['expertData'] = $objUserAuthMdl->getUserProfiles($userRow);
                      $this->_request->sendSuccessResponse('Success',$data); 
                   }
                  else {
                    $this->_request->sendErrorResponse(404,404,'Profile not found');
                  }        
               }                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
     
     
     
     
     public function actionUpdateUserProfile(){
        $objValidator       = new helpers\Validation();
        $objImageProcessor  = new helpers\ImageProcessor();
        $params             = array('user_token');     
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            
            if ($isRequestValid) {
                $objUserAuthMdl =  new \models\Users();           
                $userToken      = $this->_request->getPost('user_token',null);
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403,'User token invalid');
                }
                $profilePhoto = null;
                $profilePostedPic = $this->_request->getPost('profilepic',null);
                $dpPath      = __DIR__.'/../images/'.$userDetails->id.'_pp.jpg';
                if($profilePostedPic && $objImageProcessor->convertBase64ToImage($profilePostedPic, $dpPath)){
                   $profilePhoto = $userDetails->id.'_pp.jpg';
                }
                
                $password = $this->_request->getPost('password',null);
                $dataToUpdate = array('first_name' =>$this->_request->getPost('first_name',null),
                                      'last_name'  =>$this->_request->getPost('last_name',null),
                                      'email'      =>$this->_request->getPost('email',null),
                                      'password'   =>($password)?md5($password):null,
                                      'city'       =>$this->_request->getPost('city',null),       
                                      'country'    =>$this->_request->getPost('country',null),
                                      'gender'     =>$this->_request->getPost('gender',null),
                                      'phone'      =>$this->_request->getPost('phone',null),
                                      'address'    =>$this->_request->getPost('address',null),
                                      'websiteurl' =>$this->_request->getPost('websiteurl',null),
                                     );
                $fileterdArray = array_filter($dataToUpdate);
                if(empty($fileterdArray)){
                   $this->_request->sendErrorResponse(404,404,'Please pass data to update');
                }
                
                // check if user is valid or not
                $return  = $objUserAuthMdl->updateUser($dataToUpdate,$userDetails);
               
                if($return && $return > 0){
                     $this->_request->sendSuccessResponse('User successfully updated');         
                 }
                 else
                 {
                    if($return == -1){
                         $this->_request->sendErrorResponse(404,404,'Email alredy exists');
                    }
                    else  if($return == -2){
                        $this->_request->sendErrorResponse(404,404,'Invalid email address');
                    }
                    else{
                         $this->_request->sendErrorResponse(404,404,'Error updating user please try latere');
                    }
                   
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

