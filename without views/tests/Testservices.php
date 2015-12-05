<?php
namespace tests;
class Testservices{
   
    protected $_accessKey;
    protected $_secret;
    protected $token;
    protected $_serviceurl  = '';

    public function __construct() {
        $this->_accessKey = '88b43206bc04bb027f1e14ab428ac830';
        $this->_secret    = 'b316e31501577669aac92578252053e5';
        $this->token      = '4e6c3665df07a9905e28467c551355a5a1';
        $objServerInfo    = new \services\helpers\ServerInfo();
        $this->_serviceurl = $objServerInfo->getScheme()."://".$objServerInfo->getHost().APPLICATION_BASE;
    }
    
    
   /**
    * Function to test user login
    * **/
   public function testUserLogin(){
       $url            = $this->_serviceurl.'users/authenticate';
       $parameters     = array('email'=>'samiksha.singla@gmail.com',
                               'password'=>'9htC1DSm0X',
                               'devicetoken'=>'56464',
                               'devicetype'=>'1'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   
   /**
    * Function to forgot password
    * **/
   public function testForgotPassword(){
      $url            = $this->_serviceurl.'users/forgot-password';
      $parameters     = array('email'=>'samiksha.singla@gmail.com');
      $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
      $objUserWorkout->request();
   }
   
    /**
    * Function to test user signup
    * **/
   public function testUserSignup(){
       $url            = $this->_serviceurl.'users/signup';
       $parameters     = array(
                                 'first_name' =>'samiksha',
                                 'last_name'=>'singla',
                                 'email'    =>'samiksha.singla@mailinator.com',
                                 'password' =>'welcome',
                                 'city'     =>'Mohali',
                                 'country'  =>'country',
                                 'longitude'=>'26.46',
                                 'latitude' =>'22.20',
                                 'gender'   =>'F',
                                 'phone'    =>'1234560',
                                 'address'  =>'test address',
                                 'devicetoken'=>'56464',
                                 'devicetype'=>'1',
                                 'websiteurl'=>'http://bcapi.loc',
                                 'profilepic'=>base64_encode(file_get_contents('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQu1eEvPbpxomk08_01BG7oFkNCC7_15MT0coZxTD2MIDzO4ztwSUMhX2c'))
                              );
       $params = array('first_name','last_name','email','city','country','password','longitude','latitude','gender','phone','address');
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url,$params);
       $objUserWorkout->request();
   }
   
    /**
    * Function to test user update
    * **/
   public function testUserUpdate(){
       $url            = $this->_serviceurl.'users/update-user-profile';
       $encodedImage   = base64_encode(file_get_contents('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQu1eEvPbpxomk08_01BG7oFkNCC7_15MT0coZxTD2MIDzO4ztwSUMhX2c'));
       // not updating email here intentionally
       $parameters     = array(
                                 'profilepic'   => $encodedImage,
                                 'password'     =>'qwerty',
                                 'city'         =>'Mohali updated',
                                 'country'      =>'country updated',
                                 'name'    =>'samiksha updated',
                                 'user_token'   =>'0214c55ae08da611e9976ce4db3a0d93',
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url,  array('user_token'));
       $objUserWorkout->request();
   }
   
    /**
    * Function to test user details
    * **/
   public function testGetUserDetails(){
       $url            = $this->_serviceurl.'users/get-user-details';
       
       // not updating email here intentionally
       $parameters     = array(
                                 'user_token'   =>'f3b270954bb94691ebe9ca2678b5f602',
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   
    /**
    * Function to test user details
    * **/
   public function testGetUserById(){
       $url            = $this->_serviceurl.'users/get-user-by-id';
       
       // not updating email here intentionally
       $parameters     = array(
                                 'user_token'=>'61eca780461009b470dbee788d846dc8',
                                 'user_id'=>1000
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   
   /**
    * Function to test event insert
    * **/
   public function testEventInsert(){
       $url            = $this->_serviceurl.'events/create';
       $parameters     = array(
                                 'event_name'   =>'test notification3',
                                 'description'  =>'welcome to red beanswelcome to red beanswelcome to red beanswelcome to red beanswelcome to red beanswelcome to red beanswelcome to red beans',
                                 'city'         =>'Mohali',
                                 'country'      =>'country',
                                 'user_token'   =>'f3b270954bb94691ebe9ca2678b5f603',
                                 'starttime'    =>'2015-08-30 16:24:24',
                                 'endtime'      =>'2015-09-02 16:24:24',
                                 'longitude'    =>'76.739796',
                                 'latitude'     =>'30.678450',
                                 'address'      =>'test address'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   
   /**
    * Function to test dog insert
    * **/
   public function testDogInsert(){
       $url            = $this->_serviceurl.'dogs/insert-dog';
       $encodedImage   = base64_encode(file_get_contents('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQu1eEvPbpxomk08_01BG7oFkNCC7_15MT0coZxTD2MIDzO4ztwSUMhX2c'));
       $parameters     = array(
                                 'name'             =>'Roney',
                                 'date_of_birth'    =>'1998-11-10',
                                 'breed'            => 1,
                                 'gender'           =>'M',                                
                                 'user_token'       =>'f3b270954bb94691ebe9ca2678b5f602',
                                 'dogpic'           => $encodedImage
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url,array('name', 'date_of_birth','breed','gender','user_token'));
       $objUserWorkout->request();
   }
   
   /**
    * Function to test dog insert
    * **/
   public function testDogupdate(){
       $url            = $this->_serviceurl.'dogs/update';
       $encodedImage   = base64_encode(file_get_contents('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQu1eEvPbpxomk08_01BG7oFkNCC7_15MT0coZxTD2MIDzO4ztwSUMhX2c'));
       $parameters     = array(
                                 'name'             =>'Roney',
                                 'date_of_birth'    =>'1998-11-10',
                                 'breed'            => 1,
                                 'gender'           =>'M',                                
                                 'user_token'       =>'f3b270954bb94691ebe9ca2678b5f602',
                                 'dogpic'           => $encodedImage,
                                 'dog_id'           => 1
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url,array('dog_id','name', 'date_of_birth','breed','gender','user_token'));
       $objUserWorkout->request();
   }
   
   
   /**
    * Function to test event user participation
    * **/
   public function testUserEventParticipationSet(){
       $url            = $this->_serviceurl.'participation/set-user-event-participation';
       $parameters     = array(
                                 'event'            =>'23',
                                 'participation'    => 1,
                                 'user_token'       =>'f3b270954bb94691ebe9ca2678b5f603'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   
   /**
    * Function to test eventdetails
    * **/
   public function testEventDetails(){
       $url            = $this->_serviceurl.'events/get-event-details';
       $parameters     = array(
                                 'event_id'         =>'4',
                                 'user_token'       =>'f3b270954bb94691ebe9ca2678b5f602'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   /**
    * Function to get dog profile
    * **/
    public function testDogDetails(){
       $url            = $this->_serviceurl.'dogs/get-dog-profile';
       $parameters     = array(
                                 'dog_id'         =>'1',
                                 'user_token'      =>'88570601bec141becf09953e44debcca'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }

 /**
    * Function to get dog profile
    * **/
    public function testDogBreeds(){
       $url            = $this->_serviceurl.'dogs/get-dog-breeds';
       $parameters     = array(
                                 'user_token' =>'88570601bec141becf09953e44debcca',
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
 
   
   
 /**
    * Function to get dog profile
    * **/
    public function testNearbyEvents(){
       $url            = $this->_serviceurl.'events/nearby';
       $parameters     = array(
                                 'user_token'   =>'7302417fdc0901a7ce8b76b8dd4b0da7',
                                 'longitude'    =>'76.702019',
                                 'latitude'     =>'30.702705',
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   /**
    * test user logout
    * **/
  public function testUserLogout(){
     $url            = $this->_serviceurl.'users/logout';
     $parameters     = array(
                                'user_token'      =>'f3b270954bb94691ebe9ca2678b5f604',
                             );
     $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
     $objUserWorkout->request();
  }


  /**
    * test events owned by user
    */
   public function testUserOwnedEvents(){
       $url            = $this->_serviceurl.'events/get-events-by-user';
       $parameters     = array(
                                 'user_token'      =>'6f2aecf9fc0e310f3b8af61ca4a25fb0',
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   /**
    * test search events by query
    */
   public function testSearchEvents(){
       $url            = $this->_serviceurl.'events/search-events';
       $parameters     = array(
                                 'user_token' =>'6ed876c4207d41176fe0ca3f658b0b8a',
                                 'query' =>'Mohali'
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   /**
    * Test update event
    * **/
   public function testUpdateEvent(){
       $url            = $this->_serviceurl.'events/event-update';
       $parameters     = array(
                                 'user_token' =>'61eca780461009b470dbee788d846dc8',
                                 'eventid' =>1,
                                 'updates' => json_encode(array(
                                                                 'event_name'=>'Dog Function ss ',
                                                                 'description' =>'2015-06-15 16:25:17',
                                                                 'latitude' =>'30.704389',
                                                                 'longitude'=>'76.718087',
                                                                 'country' =>'Indian',
                                                                 'address'=>'3442',
                                                                 'city' => 'Mohalii',
                                                                 'starttime' =>'2015-06-10 16:25:17',
                                                                 'endtime'  =>'2015-06-15 16:25:17',
                                                               )
                                                         )
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request();
   }
   
   /**
    * test delete event
    * **/
   public function testDeleteEvent(){
       $url            = $this->_serviceurl.'events/delete-event';
       $parameters     = array(
                                 'user_token' =>'f3b270954bb94691ebe9ca2678b5f602',
                                 'eventid' =>4,                                
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request(); 
   }
   
   /**
    * test delete dog
    * **/
   public function testDeleteDog(){
       $url            = $this->_serviceurl.'dogs/delete';
       $parameters     = array(
                                 'user_token' =>'f3b270954bb94691ebe9ca2678b5f602',
                                 'dogid' =>1,                                
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request(); 
   }
   
   /**
    * test insert user profile
    * **/
    public function testInsertUserProfile(){
       $url            = $this->_serviceurl.'expert/set-user-profile';
       $parameters     = array(
                                 'user_token' =>'61eca780461009b470dbee788d846dc8',
                                 'profile_id' =>1,                                
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request(); 
   }
   
   /**
    * test all user profile
    * **/
    public function testGetProfiles(){
       $url            = $this->_serviceurl.'expert/get-all-profiles';
       $parameters     = array(
                                 'user_token' =>'61eca780461009b470dbee788d846dc8',                           
                              );
       $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
       $objUserWorkout->request(); 
   }
   
   
   /**
    * test search user from 
    * **/
   public function testSearchUserFromProfile(){
      $url            = $this->_serviceurl.'expert/search-experts-by-profile';
      $parameters     = array(
                                'user_token' =>'aa03b368e3aece70e2274fd12d261aa1',    
                                'query' =>'1', 
                             );
      $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
      $objUserWorkout->request(); 
  }
  
   /**
    * test search user from 
    * **/
   public function testSearchUserFromLocation(){
      $url            = $this->_serviceurl.'expert/search-experts-by-location';
      $parameters     = array(
                                'user_token' =>'f3b270954bb94691ebe9ca2678b5f603',    
                                'query' =>'mohali', 
                             );
      $objUserWorkout = new \lib\Api\Client($parameters, $this->_accessKey, $this->_secret, $url);
      $objUserWorkout->request(); 
  }
   
}

?>
