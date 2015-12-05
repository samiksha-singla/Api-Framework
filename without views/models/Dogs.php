<?php
namespace models;
class Dogs extends BaseModel{
   protected $_name = 'dogs';
  
  /** Function to insert new dog profile
   * @param  array $data containg to data to be inserted
   * @param  \RedBeanPHP\OODBBean $user user who is creating the event
   * @retrun boolean
   * **/
  public function insertDogProfile(array $data,$user){
     $data['date']  = date('Y-m-d H:i:s');
     try{
            $dogBreed = $data['dog_breed_id'];
            $dogBreedBean = $this->_redbeans->load('dog_breed',$dogBreed);
            if(!$dogBreedBean->id){
               return false;
            }    
            $data['dog_breed_id'] = $dogBreedBean->id;
            $dog     = $this->_redbeans->dispense($this->_name);           
            $dogPic = $data['dog_pic'];
            $isDogPicPosted = ($dogPic !== null)?true:false;
            
            //handle dog pic
            if($isDogPicPosted){
               $objImageProcessor  = new \services\helpers\ImageProcessor();
               $picName            = time().'.jpg';
               $dogPicPath         = __DIR__.'/../dogimages/'.$picName;
               $objImageProcessor->convertBase64ToImage($dogPic, $dogPicPath);
               $data['dog_pic']   = $picName;
            }
            
            foreach($data as $column=>$value){
              $dog->$column = $value;
            }
            $user->ownDogsList[]     = $dog;
            $isSaved                 = $this->_redbeans->store($user);
            return $isSaved;
     }
     catch(\Exception $e){
        return false;
     }
     return false;
  }  
  
  /** Function to get dog Profile 
   * @param  int $dogId id of the dog
   * @return array
   **/
  public function getDogProfile($dogId){
    $dog   =  $this->_redbeans->load($this->_name,$dogId);
    if($dog){
       $objServerInfo    = new \services\helpers\ServerInfo();
       $dogProfile = $dog->export(false);
       $dogUrl  = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE.'dogimages/'.$dog['dog_pic'];
       $picPath = APPLICATION_DIR."/dogimages/{$dog['dog_pic']}";
       $dogProfile['dog_pic'] = (@is_file($picPath))?$dogUrl:null; 
       return $dogProfile;
    }
    return array();
  }
  
  /**
   * Function to update dog profile
   * @param $data data of dog to update
   * **/
  public function updateDogProfile($data,$userId){
     $data['date']  = date('Y-m-d H:i:s');
     try{
            $dogBreed = $data['dog_breed_id'];
            $dogBreedBean = $this->_redbeans->load('dog_breed',$dogBreed);
            //invalid dog breed
            if(!$dogBreedBean->id){
               return false;
            }    
            
            $data['dog_breed_id'] = $dogBreedBean->id;
            $dog     = $this->_redbeans->load($this->_name,$data['id']); 
            
            // dog profile not found
            if(!$dog){
               return false;
            }
            
            // unauthenticated user
            if($dog->users_id != $userId){
               return false;
            }
            
            
            $dogPic = $data['dog_pic'];
            $isDogPicPosted = ($dogPic !== null)?true:false;
            
            //handle dog pic
            if($isDogPicPosted){
               $objImageProcessor  = new \services\helpers\ImageProcessor;
               $oldDogPic          = $dog->dog_pic;   
               if($oldDogPic){
                  @unlink(__DIR__.'/../dogimages/'.$oldDogPic);
               }
               
               $picName            = time().'.jpg';
               $dogPicPath         = __DIR__.'/../dogimages/'.$picName;
               $objImageProcessor->convertBase64ToImage($dogPic, $dogPicPath);
               $data['dog_pic']   = $picName;
            }
            
            foreach($data as $column=>$value){
              $dog->$column = $value;
            }
            $isSaved                 = $this->_redbeans->store($dog);
            return $isSaved;
     }
     catch(\Exception $e){
        return false;
     }
     return false;
  }
  
  
  /**
   * Public funtion to get event by id
   * @param  int $dogId Id of the event to be found
   * @return \RedBeanPHP\OODBBean
   * **/
  public function findDogById($dogId){
     $event = $this->_redbeans->load($this->_name, $dogId);
     return $event;
  }
  
}
?>

