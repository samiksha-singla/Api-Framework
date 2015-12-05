<?php 
namespace controllers;

class Users extends Basecontroller{
   
  // action to handle the login from all sites
   public function actionSso(){
      //logout previous sso session
      \utilities\Registry::clearRegistry();
      
      $isRequestPost   = $this->_request->isPost();
      if($isRequestPost){
         // check if every required parameter is set or not
         $username  = $this->_request->getParam('username',null);
         $password  = $this->_request->getParam('password',null);
         $referrer  = $this->_request->getParam('spentityid',null);
         if(!$username){
           $this->_response->renderJson(array('message'=>'Username is not set'));
         }
         if(!$password){
           $this->_response->renderJson(array('message'=>'Password is not set'));
         }
         if(!$referrer){
            $this->_response->renderJson(array('message'=>'Referrer not set'));
         }
         $objDbUserauth = new \models\Users();
         
         // check if user is authenticated or not
         $userAuthenticationStatus = $objDbUserauth->authenticate($username, $password);
         
         // user locked due to 5 invalid attempts 
         if(\models\Users::ERROR_USER_LOCKED === $userAuthenticationStatus){
            $this->_response->renderJson(array('message'=>'Your account is locked due to 5 invalid attempts',
                                               'authstatus'=>$userAuthenticationStatus));
         }
         
         //user password is expired
         if(\models\Users::ERROR_USER_PWD_EXPIRED === $userAuthenticationStatus){
            $this->_response->renderJson(array('message'=>'Your password is expired','authstatus'=>$userAuthenticationStatus));
         }
         
         //user authentication is successfull
         if($userAuthenticationStatus === true){
            $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
            $idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
            $idp = \SimpleSAML_IdP::getById('saml2:' . $idpEntityId);
            \sspmod_saml_IdP_SAML2::receiveAuthnRequest($idp);
            assert('FALSE');
         }
         else{
            //handle invalid attempts
            $objInvalidAttempts = new \models\UserLoginAttempts();
            $loginAttemptsLeft = $objInvalidAttempts->handleInvalidLoginAttempts($username);
            $invalidAttempt = false; // if attempt is invalid username is wrong
            $message = "Invalid credentials";
            if($loginAttemptsLeft !== false){
               // if last attempt was hit then show that account is locked
               if($loginAttemptsLeft === 0){
                  $this->_response->renderJson(array('message'=>'Your account is locked due to 5 invalid attempts',
                                            'authstatus'=>\models\Users::ERROR_USER_LOCKED));
               }
               $invalidAttempt = true;
               $message = "Incorrect Password.You have {$loginAttemptsLeft} attempts left";
            }
            $this->_response->renderJson(array('message'=>$message,'invalidAttempt'=>$invalidAttempt));
            exit();
         } 
      }
      $this->_response->renderJson(array('message'=>'Only post request are accepted'));
   }
   
   //function to authenticate user
   
   public function actionAuthenticateUser(){
      
      $params = array('username','password');
       try {
           $isRequestValid = $this->_validator->validateRequest($params);
           if ($isRequestValid) {
               $username  = $this->_request->getParam('username',null);
               $password  = $this->_request->getParam('password',null);
               $objDbUserauth = new \models\Users();
               // check if user is authenticated or not
               $isUserAuthenticatedStatus = $objDbUserauth->authenticate($username, $password);
               if($isUserAuthenticatedStatus === true){
                  $userRow = \R::findOne('users',"user_name = :un OR email = :un",array(':un'=>$username));
                  $this->_response->renderJson(array(
                                                        'message'=>'User successfully authenticated',
                                                        'status'=>1,
                                                        'userdata'=>array(
                                                                            'cimba_auth_id'=>$userRow->id,
                                                                            'email'=>$userRow->email,
                                                                            'firstname'=>$userRow->first_name,
                                                                            'lastname'=>$userRow->last_name,
                                                                            'username'=>$userRow->user_name
                                                                         )        
                                                     )
                                               );
                  exit();
               }
               elseif($isUserAuthenticatedStatus === \models\Users::ERROR_USER_PWD_EXPIRED){
                  $this->_response->renderJson(array('message'=>'Invalid Credentials','status'=>0,'status_code'=>'password_expired')); 
               }
               elseif($isUserAuthenticatedStatus === \models\Users::IDENTITY_NOT_FOUND){
                  $this->_response->renderJson(array('message'=>'Invalid Credentials','status'=>0,'status_code'=>'false')); 
               }
               elseif($isUserAuthenticatedStatus === \models\Users::ERROR_USER_LOCKED){
                  $this->_response->renderJson(array('message'=>'Invalid Credentials','status'=>0,'status_code'=>'user_locked')); 
               }
               else{
                  $this->_response->renderJson(array('message'=>'Invalid Credentials','status'=>0,'status_code'=>'failure'));
               } 
           } else {
              $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
           }
       } catch (\Exception $e) {
          $this->_response->renderJson(array('message'=>$e->getMessage()),500);
       }  
   }

   // function to register user 
   public function actionInsert(){
      $params = array('email','username','first_name','last_name');
       try {
           $isRequestValid = $this->_validator->validateRequest($params);
           if ($isRequestValid) {
              $username  = $this->_request->getParam('username',null);
              $email     = $this->_request->getParam('email',null); 
              $firstName = $this->_request->getParam('first_name',null); 
              $lastName  = $this->_request->getParam('last_name',null); 
              $objUsersModel = new \models\Users();
              
              $userData = array( 'email'=>$email,
                                 'username'=>$username,
                                 'first_name'=>$firstName,
                                 'last_name'=>$lastName);
              
              $userId = $objUsersModel->insert($userData);
              if($userId !== false && $userId >0){
                 $this->_response->renderJson(
                                                array('message'=>'User successfully registered',
                                                      'status'=>1,
                                                      'authId' =>$userId),
                                                200
                                              );
              }
              else if($userId === \models\Users::EMAIL_ALREADY_EXISTS){
                  $this->_response->renderJson(
                                                array('message'=>'This email is already registered',
                                                      'status'=>\models\Users::EMAIL_ALREADY_EXISTS,
                                                      'authId'=>0
                                                    ),
                                                200
                                              );
              }
              else{
                  $this->_response->renderJson(array('message'=>'Error registering user'),404);  
              }

           } else {
              $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
           }
       } catch (\Exception $e) {
          $this->_response->renderJson(array('message'=>'Unable to register user'),500);
       }
   }
   
   // function to register user 
   public function actionUpdate(){
      $params = array('cimba_auth_id'); //email,first_name,last_name ,user_name are optional
       try {
           $isRequestValid = $this->_validator->validateRequest($params);
           if ($isRequestValid) {
              $email     = $this->_request->getParam('email',null); 
              $firstName = $this->_request->getParam('first_name',null); 
              $lastName  = $this->_request->getParam('last_name',null);
              $cimbaAuthId = $this->_request->getParam('cimba_auth_id',null);
              $objUsersModel = new \models\Users();
              
              $userData = array( 'email'=>$email,
                                 'first_name'=>$firstName,
                                 'last_name'=>$lastName,
                                 'user_name'=> $this->_request->getParam('user_name',null)
                                );
              
              $userId = $objUsersModel->update($cimbaAuthId,$userData);  
              
          
              
              if($userId){
                   
                 
                 //mark user as unsynced
                 $objUserSyncStatus = new \models\UserSyncStatus;
                 $objUserSyncStatus->markUserAsUnsynced($userId);

                 
                 /**@todo : send notification to all other servers **/
                 
                 $this->_response->renderJson(
                                                array('message'=>'User successfully updated',
                                                      'success' =>true),
                                                200
                                              );
              }
              else{
                  $this->_response->renderJson(array('message'=>'Error updating user'),404);  
              }

           } else {
               $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
           }
       } catch (\Exception $e) {
          $this->_response->renderJson(array('message'=>$e->getMessage()),500);
       }
   }
   
   //Public function action change password
   public function actionChangePassword(){
      $params = array('cimba_auth_id','old_password','new_password');
      try {
           $isRequestValid = $this->_validator->validateRequest($params);
           if ($isRequestValid) {
              $cimbaAuthId = $this->_request->getParam('cimba_auth_id',null);
              $newPassword = $this->_request->getParam('new_password');
              $oldPassword = $this->_request->getParam('old_password');
              $objUsersModel = new \models\Users();
              
              // check if password matches the required criteria or not   
              $isPatternMatched = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{9,15}$/', $newPassword); 
              if($isPatternMatched){
               //change password
               $isPasswordChanged = $objUsersModel->changePassword($cimbaAuthId,$oldPassword,$newPassword);                      
               if($isPasswordChanged === true){
                  $this->_response->renderJson(
                                                 array('message'=>'User password successfully changed',
                                                       'success' =>'1'),
                                                 200
                                               );
               }
               //new and old password does not match
               elseif($isPasswordChanged === -4){
                   $this->_response->renderJson(
                                                 array('message'=>'Invalid Old Password',
                                                       'success' =>'0'),
                                                 200
                                               );
               }
               else{
                   $this->_response->renderJson(array('message'=>'Error updating password',"success"=>'0'),200);  
               }

              }
              else{
                 $this->_response->renderJson(
                                                 array('message'=>'Please enter minimum 9 and maximum 15 characters at least 1 uppercase alphabet, 1 lowercase alphabet, 1 number and 1 special character',
                                                       'success' =>'0'),
                                                 200
                                               );
              }
            } 
            else {
               $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
            }
       } catch (\Exception $e) {
          $this->_response->renderJson(array('message'=>$e->getMessage()),500);
       }
   }
   
   //action to handle forgot password
   public function actionForgotPassword(){
      $email     = $this->_request->getParam('email');
      $refferer  = $this->_request->getParam('referrer');     
      $objUserModel = new \models\Users;
      $useRow    = \R::findOne('users','email=:e',array(':e'=>$email));     
      if($useRow){
         //generate token to reset password
         $token = $objUserModel->generateToken();
         $useRow->token = $token;
         \R::store($useRow);
         
         // send mail in reset password link
         $resetUrl   = $this->_serverinfo->getScheme()."://".$this->_serverinfo->getHost()."/users/reset-password/?token={$token}&r={$refferer}";
         $objMailer = new \mailer\Appmailer;
         $subject = 'Reset Cimba Password';
         $message = array(
             'salutation_message'=>"Dear {$useRow->first_name} {$useRow->last_name}",
             'greeting_message'=>'Please click on the below link to reset your password on cimba',
             'message_content'=>array("<a href='{$resetUrl}'>{$resetUrl}</a>")
         );
         $objMailer->send($subject, $email, $message);      
         $this->_response->renderJson(array('message'=>"Please check your mail")); 
      }
      else{
         $this->_response->renderJson(array('message'=>"This email does not exists in our records."),401); 
      }
   }
   
   
   //action to reset password 
   public function actionResetPassword(){
      $token         = $this->_request->getParam('token');
      $referrer      = $this->_request->getParam('r');
      $message = null;
      if($this->_request->isPost()){
        $newPassword = $this->_request->getParam('newpassword');
        $confirmPassword = $this->_request->getParam('cnewpassword');
        
        // check if new password is equal to the confirm password 
        if($newPassword == $confirmPassword && !empty($newPassword)){
           
         // check if password matches the required criteria or not   
         $isPatternMatched = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{9,15}$/', $newPassword);  
         if($isPatternMatched){
            $objUserMdl = new \models\Users;
            $isTokenValid = \R::findOne("users","token=:t",array(':t'=>$token));
            // check of token is valid or not
            if($isTokenValid){
               $isPasswordReset = $objUserMdl->resetPassword($token,$newPassword);
               if($isPasswordReset){
                 $referrerUrl = $this->_serverinfo->getHostFromRefferer($referrer);
                 header("Location:{$referrerUrl}");
               }
            }
            else{
              $message =  "This token is not valid";
            }
         }   
         else{
            $message = "Please enter minimum 9 and maximum 15 characters at least 1 uppercase alphabet, 1 lowercase alphabet, 1 number and 1 special character";
         }  
        }
        else{
           $message =  "New password and old password does not match or password is empty";
        }
      }
      $viewData = array(
                     'message'=>$message
                   );
      $this->render('resetpassword', $viewData);
   }
   
   
   
   // function to register user 
   public function actionMigrate(){
      $params = array('email','username','first_name','last_name','password','salt');
       try {
           $isRequestValid = $this->_validator->validateRequest($params);
           if ($isRequestValid) {
              $username  = $this->_request->getParam('username',null);
              $email     = $this->_request->getParam('email',null); 
              $firstName = $this->_request->getParam('first_name',null); 
              $lastName  = $this->_request->getParam('last_name',null); 
              $password  = $this->_request->getParam('password',null);
              $salt      = $this->_request->getParam('salt',null);
              
              $objUsersModel = new \models\Users();
              
              $userData = array( 'email'=>$email,
                                 'username'=>$username,
                                 'first_name'=>$firstName,
                                 'last_name'=>$lastName,
                                 'password'=>$password,
                                 'salt'=>$salt,
                               );
              
              $userId = $objUsersModel->tempMigrate($userData);
              if($userId !== -1){
                 $this->_response->renderJson(
                                                array('message'=>'User successfully registered',
                                                      'authId' =>$userId),
                                                200
                                              );
              }
              else if($userId === -1){
                  $this->_response->renderJson(
                                                array('message'=>'This email is already registered'),
                                                404
                                              );
              }
              else{
                  $this->_response->renderJson(array('message'=>'Error registering user'),404);  
              }

           } else {
              $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
           }
       } catch (\Exception $e) {
          $this->_response->renderJson(array('message'=>$e->getMessage()),500);
       }
   }
   
   // action to check if email is available or not
   public function actionCheckEmailAvailability(){
      $params = array('email');
      try {
          $isRequestValid = $this->_validator->validateRequest($params);
          if ($isRequestValid) {
             $email     = $this->_request->getParam('email',null); 
             $userRow   = \R::findOne('users','email = :email',array(':email'=>$email));
             if($userRow){
                $this->_response->renderJson(array('message'=>'Email already exists','availability'=>"0"));
             }
             else{
                 $this->_response->renderJson(array('message'=>'Email is available exists','availability'=>"1"));
             }
          } else {
             $this->_response->renderJson(array('message'=>'Request cannot be validated'),400);
          }
      } catch (\Exception $e) {
         $this->_response->renderJson(array('message'=>$e->getMessage()),500);
      }
   }
   
}
?>