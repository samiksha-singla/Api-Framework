<?php
namespace models;
class DogBreed extends BaseModel{
   protected $_name = 'dog_breed';
  
  /** Function to get all dog breed
   * @param  none
   * @return array
   **/
  public function getDogBreedDetails(){
    $dogbreeds   = $this->_redbeans->find($this->_name);
    $return = array();
    foreach($dogbreeds as $breed){
       $return[] = $breed->export(false);
    }
    return $return;
  }
}
?>

