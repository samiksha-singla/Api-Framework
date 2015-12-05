<?php
namespace models;
class Profiles extends BaseModel{

   protected $_name = 'profiles';

   /** Function to get all profiles 
   * @param  none
   * @return array
   **/
  public function getAllProfiles(){
    $profiles   = $this->_redbeans->find($this->_name);
    $return = array();
    foreach($profiles as $profile){
       $return[] = $profile->export(false);
    }
    return $return;
  }
}
?>

