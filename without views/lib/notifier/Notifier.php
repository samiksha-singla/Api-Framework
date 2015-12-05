<?php
/*
 * ===============================================================
 * Class to send push notifications to the devices
 * ===============================================================
 */

namespace notifier;

class Notifier
{
   
   // constructor
   public function __construct() 
   {
      // static info for android google cloud messaging
       $this->gcm_api_key  = "AIzaSyD1Ouc5q4m2lxnv6m9GiWz04Ke0JPnuJWk";
       $this->gcm_api_url  = "https://android.googleapis.com/gcm/send";   
   }
   
   
   /** ----- This function is to send notification to ios device  (1) -------------------------------
   * @PARAMS: message and device tokens on which notification is to be sent 
   * @RETURN: void
   * **/
   private function push_to_ios($message = '', $receivers=array())
   {
      $postData = array('message'=>$message,'receivers'=>  json_encode($receivers));
      $url = "http://www3.gwenpaul.com/btcapi/pushnotifier/push-to-ios"; //  using dedicated server to send notification
      $curl_channel = curl_init();
      curl_setopt( $curl_channel, CURLOPT_URL, $url );
      curl_setopt( $curl_channel, CURLOPT_POST, true );
      curl_setopt( $curl_channel, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $curl_channel, CURLOPT_POST, true );
      curl_setopt( $curl_channel, CURLOPT_POSTFIELDS, $postData );
      $result = curl_exec($curl_channel);
      curl_close($curl_channel);
   }
   #----------------------------------------------(/1)-----------------------------------
   
   
   /** ----- This function is to send notification to android device  (2) -------------------------------
   * @PARAMS: message and registeration ids of devices
   * @RETURN: void
   * **/
   
   private function push_to_android($message = '', $receivers=array())
   {
      #------- initializations(init)------------
      $apiKey          = $this->gcm_api_key;
      $url             = $this->gcm_api_url;
      
      $random_collapse = rand(11, 100);
      $fields          = array(
                               'registration_ids'  => $receivers,
                               'data'              => array( "message" => $message ),
                               );

      $headers         = array( 
                                 'Authorization: key=' . $apiKey,
                                 'Content-Type: application/json'
                              );

      #---------------(/init)--------------------

     
      #--- debug---
      /*echo "<pre>";
      print_r(json_encode( $fields ));
      echo "</pre>";*/
      #---(/debug)---
      
      #------- send notificatin using curl(curl)-------------------------
      $curl_channel = curl_init();
      curl_setopt( $curl_channel, CURLOPT_URL, $url );
      curl_setopt( $curl_channel, CURLOPT_POST, true );
      curl_setopt( $curl_channel, CURLOPT_HTTPHEADER, $headers);
      curl_setopt( $curl_channel, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $curl_channel, CURLOPT_POSTFIELDS, json_encode( $fields ) );
      $response = curl_exec($curl_channel);
      curl_close($curl_channel);
      #-------------------------------(/curl)---------------------------------------

   }
   
   
  /** ----- This function is to send push notification to device (4) -------------------------------
   * @PARAMS: message, device_token, device_type
   * @RETURN: none
   * **/
  
   public function send_notification($message = '' , $receivers=array(), $device_type='')
   {
      
      $is_device_ios      = ($device_type == 1)?true:false;
      $is_device_android  = ($device_type == 2)?true:false;
      if( $is_device_ios )
      {
         $this->push_to_ios($message,$receivers);
      }
      if( $is_device_android )
      {
         $this->push_to_android($message,$receivers);
      }
   }
   #--------------------------------------(/4)---------------------------------
   
}
?>