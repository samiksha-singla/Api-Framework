<?php 

namespace models;

class Users extends BaseModel{
   
   const EMAIL_ALREADY_EXISTS = -1;
   const ERROR_USER_LOCKED = -2;
   const ERROR_USER_PWD_EXPIRED  = -3;
   const IDENTITY_NOT_FOUND = -4;
   
   protected $_name = 'users';
      
   /**
    * Function to authenticate user 
    * @param  string $username user name
    * @param  string $password password
    * @return boolean
    * **/
   
   public function authenticate($username, $password){
      $userRow = \R::findOne($this->_name,'(user_name = :un OR email = :un) AND status =1',array(":un"=>$username));   
      if($userRow){
         
         // check if password is expired or not
         $isPasswordExpired = strtotime($userRow->pwd_exp_time)-time() <= 0;
         if($isPasswordExpired){
            return self::ERROR_USER_PWD_EXPIRED;
         }
         //check if user is locked or not
         $isUserLocked = ($userRow->locked == 1)?true:false;
         if($isUserLocked){
            return self::ERROR_USER_LOCKED;
         }
         
         if(md5($password.$userRow->salt) == $userRow->password){
            
            // clear invalid login attempts 
            $objInvalidAttempts = new UserLoginAttempts;
            $objInvalidAttempts->clearInvalidLoginAttempts($username);
            
            //write user data in session
            \utilities\Registry::setRegistry('user',$userRow->export());
            return true;
         }
         return false;
      }
      return self::IDENTITY_NOT_FOUND;
   }
   
   /**
    * Temporary function to migrate user from different sites
    * @param array $userData Data of the user to be inserted in array format
    * @return int|boolean
    * **/
   public function tempMigrate(array $userData){
      $userRow = \R::findOne($this->_name,'email = :em',array(":em"=>$userData['email']));
      if($userRow){
         return $userRow->id; // if email already exists then return the id so that site can update that in its record
      }
      else
      {
         $password = $userData['password'];
         $salt = $userData['salt'];
         $firstName = $userData['first_name'];
         $lastName = $userData['last_name'];
         $email = $userData['email'];
         $row = $this->_redBeans->dispense($this->_name);
         $row->user_name = $userData['username'];
         $row->email = $email;
         $row->first_name = $firstName;
         $row->last_name = $lastName;
         $row->password = $password;
         $row->salt = $salt;
         $row->date_registered = date('Y-m-d H:i:s'); 
         $row->pwd_exp_time = date('Y-m-d H:i:s',time()+90*24*60*60);
         $userId = $this->_redBeans->store($row);
         return $userId;
      }
      return false;
   }
   
   
   /**
    * Function to insert user
    * @param array $userData Data of the user to be inserted in array format
    * @return int|boolean
    * **/
   public function insert(array $userData){
      $userRow = $userRow = \R::findOne($this->_name,'user_name = :un OR email = :em',array(":un"=>$userData['username'],":em"=>$userData['email']));
      if($userRow){
         return self::EMAIL_ALREADY_EXISTS; // email already exixts
      }
      else
      {
         $password = $this->_generatePassword();
         $salt = $this->_generateSalt();
         $firstName = $userData['first_name'];
         $lastName = $userData['last_name'];
         $email = $userData['email'];
         $row = $this->_redBeans->dispense($this->_name);
         $row->user_name = $userData['username'];
         $row->email = $email;
         $row->first_name = $firstName;
         $row->last_name = $lastName;
         $row->password = md5($password.$salt);
         $row->salt = $salt;
         $row->pwd_exp_time = date('Y-m-d H:i:s',time()+90*24*60*60);
         $row->date_registered = date('Y-m-d H:i:s');        
         $userId = $this->_redBeans->store($row);
    
         // send mail
         if($userId){
            // send email
            $objMailer = new \mailer\Appmailer;
            $subject = 'Cimba User Registeration';
            $message = array(
                'greeting_message'=>'You are successfully registered on cimba.Please login with following details',
                'salutation_message'=>"Dear {$firstName} {$lastName}",
                'message_content'=>array('Email:'.$email,'password:'.$password)
            );
            $objMailer->send($subject, $email, $message);
         }
         return $userId;
      }
      return false;
   }
   
    
   /**
    * Function to validate if email is available or not user
    * @param string $email email to be checked
    * @return boolean
    * **/
   public function checkEmailAvailability($email, $userId=null){
      $userRow = $userRow = \R::findOne($this->_name,'email = :em',array(":em"=>$email));
      if($userRow){
         if($userId){
            if($userId == $userRow->id){
               return true;
            }
            return false;
         }
      }
      return true;
   }
   
   
   
   /**
    * Public function update user
    * @param  int $id id of the user whose data is to be updated
    * @param  array $params array
    * @return id of the updated user or false 
    * **/
    public function update($id,array $params){
      $userRow = $this->_redBeans->load($this->_name, $id);
      $isEmailAvailable = ($this->checkEmailAvailability($params['email'], $userRow->id)) ;
      if($isEmailAvailable){
         if($userRow){
            foreach($params as $dbColumn=>$valueToUpdate){
               if($valueToUpdate){
                  $userRow->$dbColumn = $valueToUpdate; 
               }
            } 
            $this->_redBeans->store($userRow);
            return $id;
        }
      }
      return false;
    }
   
    
    /**
     * Function to change password
     * @param init $cimbaAuthId Id of the user for whom data is to be changed
     * @param string $oldPassword old password of user
     * @param string $newPassword new password of user
     * @return boolean
     * **/
    public function changePassword($cimbaAuthId, $oldPassword, $newPassword){
      $userRow = $this->_redBeans->load($this->_name, $cimbaAuthId); 
      if($userRow){
        $oldPasswordMd5 = $userRow->password;
        $isOldPasswordMatched = (md5($oldPassword.$userRow->salt) == $oldPasswordMd5)?true:false;
        if($isOldPasswordMatched){
           $newSalt = $this->_generateSalt();
           $newPasswordHashed = md5($newPassword.$newSalt);
           $userRow->salt = $newSalt;
           $userRow->password = $newPasswordHashed;
           $this->_redBeans->store($userRow);
           return true;
        }
        else{
           return -4; // old and new password does not match
        }
      }
      return false;
    }
    
    /**
     * Function to reset password 
     * @param string $token token for which password is to be reset
     * @param string $npassword new password
     * @return boolean
     * **/
    public function resetPassword($token,$npassword){
       $userRow = \R::findOne($this->_name,"token=:t",array(":t"=>$token));
       if($userRow){
          $passwordExpTime = time() + 90 * 24 * 60 * 60;
          $salt =  $this->_generateSalt();
          $userRow->token = null;
          $userRow->locked = 0;
          $userRow->locked_status_updatetime = time();
          $userRow->pwd_exp_time = date('Y-m-d H:i:s',$passwordExpTime);
          $userRow->salt = $salt;
          $userRow->password = md5($npassword.$salt);
          $this->_redBeans->store($userRow);
          
          //clear invalid attempts 
          $objInvalidAttempts = new \models\UserLoginAttempts;
          $objInvalidAttempts->clearInvalidLoginAttempts($userRow->email);
          
          return true;
       }
       return false;
    }
    
   /**
    * Function to generate password
    * @param void 
    * @return string
    * **/
   private function _generatePassword(){
     $length = 10;
     $chars = array_merge(range(0,9), range('a','z'), range('A','Z'));
     shuffle($chars);
     $password = implode(array_slice($chars, 0, $length));
     return $password;
   }
   
   /**
    * Function to generate salt
    * @param void 
    * @return string
    * **/
   private function _generateSalt(){
      return uniqid('',true);
   }
   
   
   /**
    * Function to generate random token to reset password
    * @param void
    * @return string
    * **/
   public function generateToken(){
      $token = md5(uniqid('', true));
      $isRow = \R::findOne($this->_name,"token=:token",array(":token"=>$token));
      if ($isRow) {
         $this->generateToken();
      }
      return $token;
   } 

   /**
    * function to get user from id
    * @param  int $userId
    * @return \RedBeanPHP\OODB |Boolean
    * **/
   public function getUserById($userId)
   {
      $userRow = $this->_redBeans->load($this->_name,$userId);
      if($userRow->id){
         return $userRow;
      }
      return false;
   }
   
}
?>