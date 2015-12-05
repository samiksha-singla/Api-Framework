<?php
namespace models;
require_once('lib/mailer/class.phpmailer.php');
class Users extends BaseModel{

   protected $_name = 'users';
   const ERROR_DUPLICATE_EMAIL = -1;
   const ERROR_INVALID_EMAIL   = -2;
   /**
    *  Function to authenticate user 
    *  @param  string $email    email
    *  @param  string $password password
    *  @return boolean
    **/
  public  function authenticate($email, $password){
     $row  = \R::findOne($this->_name,'email = :email AND password = :password AND is_active = 1',array(':email' =>$email,':password'=>md5($password)) );     
     if($row){
         $row->token      = static::generateToken();
         $row->last_login = date('Y-m-d H:i:s'); 
         $this->_redbeans->store($row);
         return $row;
      }
      return false;
  }
  
  /** Function to insert new user
   * @param  array $data containg to be inserted for user
   * @retrun boolean|integer
   * **/
  public function insertUser(array $data){
  
     $data['date_reg']  = date('Y-m-d H:i:s');
     try{
        //check if valid email
        $emailStatus = $this->_validateEmail($data['email']);
        if($emailStatus !== true){
           return $emailStatus;
        }
        $user = $this->_redbeans->dispense('users');
        foreach ($data as $dbColumn=>$value){
           $user->$dbColumn = $value;
        }
        $userId = $this->_redbeans->store($user);
        return $userId;
     }
     catch(\Exception $e){
        echo $e->getMessage();
        return false;
     }
     return false;
  }
  
  
  /**
   *  Function to update user
   *  @param  array data containg to be inserted for user
   *  @param  \RedBeanPHP\OODBBean $user user who is being updated
   *  @retrun boolean
   * **/
  public function updateUser(array $data, $user){
     $data['date_updated']  = date('Y-m-d H:i:s');
     try{
        //check if valid email
        if($data['email']){
            $emailStatus = $this->_validateEmail($data['email']);
            if($emailStatus !== true){
              return $emailStatus;
           }
        }   
        foreach ($data as $dbColumn=>$value){          
           if($value){  // don't update null values
              $user->$dbColumn = $value;
           }        
        }
        $userId = $this->_redbeans->store($user);
        return $userId;
     }
     catch(\Exception $e){
        return false;
     }
  }
  
  
  /**
   * Function to validate email
   * @param  string $email email
   * @return return error or boolean
   * **/
  private function _validateEmail($email){
     $user = \R::findOne($this->_name,'email = :email',array(':email' =>$email));
     if($user){
        return self::ERROR_DUPLICATE_EMAIL;
     }
     if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return self::ERROR_INVALID_EMAIL;
     }  
     return true;
  }
  
  /**
   * Function to generate token
   * @param none
   * @retrun string
   * **/
  private  static function generateToken(){
    return  md5(uniqid(mt_rand(), true));
  }
  
  
  /**
   * Function to get user from token
   * @param  string $token token for user
   * @return \RedBeanPHP\OODBBean |boolean
   * **/
  public function getUserFromToken($token){
     try{
        $user = \R::findOne($this->_name,'token = :token',array(':token' =>$token));
        return $user;
     }
     catch(\Exception $e){
        return false;
     }
     return false;
  }
  
  /**
   * Get User profiles
   * @param  \RedBeanPHP\OODBBean $userDetails
   * @return array
   * **/
  public function getUserProfiles($userDetails){
     $return = array();
     $associatedProfiles = $userDetails->via('users_profiles')->sharedProfiles;
     if($associatedProfiles){
        foreach($associatedProfiles as $profile){
           $return[] = $profile->export();
        }
     }
     return $return;
  }
  
  
  /**
   * Function to get dogs owned by user
   * @param int $userId id of the user
   * @return array 
   * **/
  public function getUserOwnedDogs($userId){
     
     $query = "SELECT d.id as dogid,dog_pic,name,gender,date_of_birth,dog_breed_id,db.breed_name "
             ."FROM dogs d "
             ."INNER JOIN dog_breed db ON db.id = d.dog_breed_id "
             ."WHERE users_id = :userid";
             
     $userOwnedDogs = \R::getAll($query,array(':userid' =>$userId)); 
     $return = array();
     $objServerInfo    = new \services\helpers\ServerInfo();
     foreach($userOwnedDogs as $dog){
        
        $dogUrl  = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'dogimages/'.$dog['dog_pic'];
        $picPath = APPLICATION_DIR."/dogimages/{$dog['dog_pic']}";
        $dog['dog_pic'] = (@is_file($picPath))?$dogUrl:null;      
        $return[] = $dog;
     }
     return $return;    
     
  }
  
  /**
   * Function to reset password
   * @param  $email
   * @return status integer indicating status
   * **/
  public function resetPassword($email){
     $userRow = \R::findOne($this->_name,'email=:email',array(':email'=>$email));
     $return = 1;
     
     if($userRow){
       
        // generate random password
        $length = 10;
        $chars = array_merge(range(0,9), range('a','z'), range('A','Z'));
        shuffle($chars);
        $password = implode(array_slice($chars, 0, $length));
        $userRow->password = md5($password);
        
        // save user row with password
        $this->_redbeans->store($userRow);
        
        //send email to user
        
         $mail             = new \PHPMailer(); // defaults to using php "mail()"
         $mail->IsSMTP(); // telling the class to use SendMail transport       
         //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
         $mail->SMTPAuth   = true;                  // enable SMTP authentication
         $mail->Host       = "smtp.gmail.com"; // sets the SMTP server
         $mail->SMTPSecure = "tls";// sets the SMTP secure
         $mail->Port       = 587;                    // set the SMTP port for the GMAIL server
         $mail->Username   = "mailfromapps@gmail.com"; // SMTP account username
         $mail->Password   = "apps@gmail";
         $mail->SetFrom('noreplybtc@gmail.com', 'Walking Dogs');
         $address = $userRow->email;
         $mail->AddAddress($address);
         $mail->Subject    = "Password Reset";
         $body =  "<html>"
                     . "<body>"
                        . "<div>Dear {$userRow->first_name} {$userRow->last_name}</div><div>Your password is reset to {$password}</div>"
                     . "</body>"
                  . "</html>";
         $mail->MsgHTML($body);
         if(!$mail->Send()) {
           return -2; // error sending mail
         } else {
           return 1; // success 
         }         
     }
     else{
        return -1; //user not found
     }
     
  }
  
  /**
   * Public  function to expire user token
   * @param  string $userToken user token which is to be expired
   * @return null
   * **/
  public function voidUserToken($userToken){
     $userRow = $this->getUserFromToken($userToken);
     if($userRow){
        $userRow->token = null;
        $this->_redbeans->store($userRow);
        return true;
     }
     return false;
  }
  
  /**
   * Function to search for users with given distance
   * @params int $distance distance in kms within which users are to be found
   * @params string $longitude longitude 
   * @params string $latitude latitude
   * @params string $eventOwnerId id of the owner of the event
   * @return array
   * **/
  public function getUserDevicesWithinDistance($distance,$longitude,$latitude,$eventOwnerId){
     
     $distanceInMiles = $distance*0.621371;
     
     $sql = "SELECT device_token,device_type, (((acos(sin((:lat*pi()/180)) * 
             sin((`Latitude`*pi()/180))+cos((:lat*pi()/180)) * 
             cos((`Latitude`*pi()/180)) * cos(((:long- `Longitude`)* 
             pi()/180))))*180/pi())*60*1.1515
            ) as distance
            FROM users 
            WHERE (((acos(sin((:lat*pi()/180)) * 
             sin((`Latitude`*pi()/180))+cos((:lat*pi()/180)) * 
             cos((`Latitude`*pi()/180)) * cos(((:long- `Longitude`)* 
             pi()/180))))*180/pi())*60*1.1515
            ) <= :distance AND id != :uid";  //distance in miles
     $requiredUsers  = \R::getAll($sql, array(':uid'=>$eventOwnerId,':long'=>$longitude,':lat'=>$latitude,':distance'=>$distanceInMiles));
     return $requiredUsers;
  }
  
}
?>

