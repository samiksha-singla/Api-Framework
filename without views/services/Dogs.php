<?php
namespace services;

class Dogs extends BaseService{ 
     public function actionInsertDog(){
        $objValidator     = new helpers\Validation();
        $params = array('name', 'date_of_birth','breed','gender','user_token');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $objDogMdl  =  new \models\Dogs();
                $dataToInsert = array('name'            =>$this->_request->getPost('name',null),
                                      'date_of_birth'   =>$this->_request->getPost('date_of_birth',null),
                                      'dog_breed_id'    =>$this->_request->getPost('breed',null),
                                      'gender'          =>$this->_request->getPost('gender',null),
                                      'dog_pic'         =>$this->_request->getPost('dogpic',null),
                                     );
                
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $isDogInserted = $objDogMdl->insertDogProfile($dataToInsert,$userDetails);
                if($isDogInserted){
                     $this->_request->sendSuccessResponse('Dog Profile Inserted');                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Error inserting dog profile');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
     }
   
      public function actionGetDogProfile(){
      	$objValidator     = new helpers\Validation();
        $params = array('user_token','dog_id');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken    = $this->_request->getPost('user_token',null);
                $dogId        = $this->_request->getPost('dog_id',null);
                $objUserMdl   =  new \models\Users();
                $objDogMdl    =  new \models\Dogs();                        
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $dogDetails = $objDogMdl->getDogProfile($dogId,$userDetails);
                if($dogDetails){
                     $this->_request->sendSuccessResponse('Success',$dogDetails);                    
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Error getting dog profile');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
      }
    
      public function actionGetDogBreeds(){
        $objValidator     = new helpers\Validation();
        $params = array('user_token');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken    = $this->_request->getPost('user_token',null);
                $objUserMdl   =  new \models\Users();
                $objDogBreed  =  new \models\DogBreed();                      
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $dogBreedDetails = $objDogBreed->getDogBreedDetails();
                if($dogBreedDetails){
                     $this->_request->sendSuccessResponse('Success',$dogBreedDetails);                    
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Error getting dog breeds');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
      }
     
      public function actionDelete(){
        $objValidator  = new helpers\Validation();
        $params        = array('user_token','dogid');
        try {
            $isRequestValid = $objValidator->validateRequest($params);
            if ($isRequestValid) {
                $userToken      = $this->_request->getPost('user_token',null);
                $dogId        = $this->_request->getPost('dogid',null);
                $objUserAuthMdl = new \models\Users();
                $objDogsModel = new \models\Dogs();
                $userDetails    = $objUserAuthMdl->getUserFromToken($userToken);
                if(!$userDetails){
                     $this->_request->sendErrorResponse(403,403, 'User token invalid');
                }            
                
                //check if event exists or not
               
             
                $dogRow = $objDogsModel->findDogById($dogId);
                if(!$dogRow->id){
                   $this->_request->sendErrorResponse(403,403, 'Invalid dog profile'); 
                }               
              
                // check if event is owned by user
                $isUserDogOwner = ($dogRow->users_id == $userDetails->id)?true:false;
                
                if(!$isUserDogOwner){
                    $this->_request->sendErrorResponse(403,403, 'User is not authenticated to delete dog');
                }
               
                try{
                   \R::trash($dogRow); 
                   $this->_request->sendSuccessResponse('Dog successfully deleted');
                   
                }
                catch(Exception $ex){
                   $this->_request->sendErrorResponse(403,403, 'Error deleting dog');
                } 
               
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
      }
      
      
      
      
      public function actionUpdate(){
        $objValidator     = new helpers\Validation();
        $params = array('dog_id','name', 'date_of_birth','breed','gender','user_token');
        try {
              $isRequestValid = $objValidator->validateRequest($params);
              if ($isRequestValid) {
                $userToken  = $this->_request->getPost('user_token',null);
                $objUserMdl =  new \models\Users();
                $objDogMdl  =  new \models\Dogs();
                $dataToInsert = array('name'            =>$this->_request->getPost('name',null),
                                      'date_of_birth'   =>$this->_request->getPost('date_of_birth',null),
                                      'dog_breed_id'    =>$this->_request->getPost('breed',null),
                                      'gender'          =>$this->_request->getPost('gender',null),
                                      'dog_pic'         =>$this->_request->getPost('dogpic',null),
                                      'id'              =>$this->_request->getPost('dog_id',null),
                                     );
                
                // check if user is valid or not
                $userDetails    = $objUserMdl->getUserFromToken($userToken);
                if(!$userDetails){
                    $this->_request->sendErrorResponse(403,403,'User token invalid');
                }               
                $isDogInserted = $objDogMdl->updateDogProfile($dataToInsert,$userDetails->id);
                if($isDogInserted){
                     $this->_request->sendSuccessResponse('Dog Profile updated');                  
                }
                else{
                    $this->_request->sendErrorResponse(404,404,'Error updating dog profile');
                }            
                
            } else {
                $this->_request->sendErrorResponse(403,403,'Request cannot be validated');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
             $this->_request->sendErrorResponse(404,404,$e->getMessage());
        }
      }

}
?>

