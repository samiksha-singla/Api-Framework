<?php
namespace models;
class UsersProfiles extends BaseModel{

   protected $_name = 'users_profiles';

  
  /** Function to insert new Event
   * @param  RedBeanPHP\OODBBean $userId id of the user for whom profile is to be inserted
   * @param  int $profileId id of the profile
   * @retrun boolean
   * **/
  public function insertUserProfile($userId,$profileId){
    
     try{        
        $associatedProfiles = \R::findOne($this->_name,'users_id = :uid AND profiles_id = :pId', array(':uid'=>$userId,':pId'=>$profileId));      
        if(!$associatedProfiles->id){
           $objUserProfileBean  = $this->_redbeans->dispense($this->_name);
           $objUserProfileBean->users_id = $userId;
           $objUserProfileBean->profiles_id = $profileId;
           return $this->_redbeans->store($objUserProfileBean);
        }
        else {
           return -1; //profile is already there
        }
        
     }
     catch(\Exception $e){
        return false;
     }
     return false;
  }
  
  /**
   *Function to search having profile id
   * @param  string $query query text for which profiles are to be found
   * @return array
   * **/
  public function searchUsersByProfile($query){
     try{
        $return = array();
        
        $sql = "SELECT u.id,u.first_name,u.last_name ,u.phone ,GROUP_CONCAT(p.name) as speciality "
                ."FROM users u "
                ."INNER JOIN users_profiles up ON u.id = up.users_id "
                ."INNER JOIN profiles p ON up.profiles_id = p.id WHERE p.name like :query "
                ."GROUP BY u.id";
        
        $profiles = \R::getAll($sql,array(':query'=>"%{$query}%"));
        
        $objServerInfo    = new \services\helpers\ServerInfo();
        $usersProfiles = array();
        // details f other participants
         foreach($profiles as $profile){
           $profilepic = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$profile['id'].'_pp.jpg';
           $picPath    = APPLICATION_DIR."/images/{$profile['id']}_pp.jpg";
           $profile['profilepic'] = (@is_file($picPath))?$profilepic:null;
           $profile['speciality'] = explode(",", $profile['speciality']);
           $usersProfiles[] = $profile;
         }
                  
        return $usersProfiles;
     }
     catch (\Exception $ex){
        return false;
     }
  }
 
  
  /**
   *Function to search having with given location
   * @param  string $query location for which experts are to be searched
   * @return array
   * **/
  public function searchUsersByLocation($query){
     $sql = "SELECT u.id,u.first_name,u.last_name ,u.phone ,GROUP_CONCAT(p.name) as speciality ".
            "FROM `users` u "
          . "INNER JOIN `users_profiles` up ON u.id = up.users_id "
          . "INNER JOIN profiles p ON up.profiles_id = p.id "
          . "WHERE city LIKE :query OR country LIKE :query OR address LIKE :query "
          . "GROUP BY u.id";
        
      $profiles = \R::getAll($sql,array(':query'=>"%$query%"));
      $objServerInfo    = new \services\helpers\ServerInfo();
      $usersProfiles = array();
      foreach($profiles as $profile){
           $profilepic = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'images/'.$profile['id'].'_pp.jpg';
           $picPath    = APPLICATION_DIR."/images/{$profile['id']}_pp.jpg";
           $profile['profilepic'] = (@is_file($picPath))?$profilepic:null;
           $profile['speciality'] = explode(",", $profile['speciality']);
           $usersProfiles[] = $profile;
         }
      
      return $usersProfiles;    
  }

}
?>

