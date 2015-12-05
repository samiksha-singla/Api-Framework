<?php 

namespace models;

class UserLoginAttempts extends BaseModel{
   
   protected $_name = 'users_login_attempts';
   
   /**
    * Function to handle login attemps
    * @param string $username email or username of user
    * @return int login attempts left
    * **/
   public function handleInvalidLoginAttempts($username){
     $userRow = \R::findOne('users','email=:ui OR user_name = :ui',array(':ui'=>$username));
     if($userRow && $userRow->locked == 0){
        $objRequest  = new \utilities\Request();
        $login_attempts_left = 0;
        // check if user has already login attemps 
        $userLoginAttemptsRow = \R::findOne($this->_name,'user_id = :ui',array(':ui'=>$userRow->id));
        if($userLoginAttemptsRow){
           
           // lock user account if login attempts are =5
           if($userLoginAttemptsRow->invalid_login_attempts == 5){
              $userRow->locked = 1;
              $userRow->locked_status_updatetime = time();
              $this->_redBeans->store($userRow);
              return 0;
           }
           //else modify ther required details in user_login_attempts_table
           $invalidLoginAttempts = $userLoginAttemptsRow->invalid_login_attempts+1;
           $userLoginAttemptsRow->user_id = $userRow->id;      
           $userLoginAttemptsRow->last_attempt = time();      
           $userLoginAttemptsRow->invalid_login_attempts = $invalidLoginAttempts;
           $userLoginAttemptsRow->ipaddress = inet_pton($objRequest->getServer('REMOTE_ADDR'));  // ip address for tracking
           $login_attempts_left = 5-$invalidLoginAttempts;
        }
        //insert new row
        else{
           $userLoginAttemptsRow = $this->_redBeans->dispense($this->_name);
           $userLoginAttemptsRow->user_id = $userRow->id;      
           $userLoginAttemptsRow->last_attempt = time();      
           $userLoginAttemptsRow->invalid_login_attempts = 1;
           $userLoginAttemptsRow->ipaddress = inet_pton($objRequest->getServer('REMOTE_ADDR'));  // ip address for tracking
           $login_attempts_left = 4;
        }
        
        $this->_redBeans->store($userLoginAttemptsRow);
        return $login_attempts_left;
     }
     return false;
   }
   
   /**
   * Function to clear invalid login attempts 
   * @param string $username email or username of user
   * @return int login attempts left
   * **/
   public function clearInvalidLoginAttempts($username){
     $userRow = \R::findOne('users','email=:ui OR user_name = :ui',array(':ui'=>$username));
     if($userRow){
        $userAttemptRow = \R::findOne($this->_name,'user_id = :ui',array(':ui'=>$userRow->id));
        if($userAttemptRow){
           \R::trash($userAttemptRow);
        }
     }
   }
   
   
}
?>