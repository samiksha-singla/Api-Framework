<?php
namespace models;
class UsersEventsParticipation extends BaseModel{

   protected $_name = 'users_events_participation';
  
  /** Function to insert new event participation
   * @param  array $data containg to be inserted for user
   * @retrun boolean
   * **/
  public function insertUserEventParticipation(array $data){
  
     $data['date']  = date('Y-m-d H:i:s');
     try{
        
        $query                 = "users_id = :uid AND events_id = :eId";
        $userParticipationRow  = \R::findOne($this->_name,$query,array(':uid'=>$data['users_id'],':eId'=>$data['events_id']));
        if(!$userParticipationRow){
           $userParticipationRow  = $this->_redbeans->dispense($this->_name);
           $userParticipationRow->events_id   = $data['events_id'];
           $userParticipationRow->users_id    = $data['users_id'];
        }       
        $userParticipationRow->participation_id  = $data['participation_id'];  
        return $this->_redbeans->store($userParticipationRow);
     }
     catch(\Exception $e){
        echo $e->getMessage();
        return false;
     }
     return false;
  }
  
  
  
  
}
?>

