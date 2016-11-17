<?php
/*
 * ===============================================================
 * Class to send push notifications to the devices
 * ===============================================================
 */

/*
 *  CLASS TO SEND PUSH NOTIFICATION
 */

class push
{
   
   // constructor
   public function __construct() 
   {
      // static info for android google cloud messaging
      //$this->gcm_api_key  = "AIzaSyCjbYQgszczUSbKkUe5JBaz7f_am7XPstE";
       $this->gcm_api_key  = "AIzaSyCbqDsj3HKgKJa07nhi_K7nnHYeUjSXlgQ";
      $this->gcm_api_url  = "https://android.googleapis.com/gcm/send";   
   }
   
   
   /** ----- This function is to send notification to ios device  (1) -------------------------------
   * @PARAMS: message and device tokens on which notification is to be sent 
   * @RETURN: void
   * **/
   private function push_to_ios($message = '', $receivers=array())
   {
      #--------------- initializations ------------------------------
      $message           = substr($message, 0, 255);
      $sound          = 'default';
      $development    = false;
      $badge          = '1';
      $payload['aps'] = array(
                               'alert' => $message, 
                               'badge' => intval($badge), 
                               'sound' => $sound
                             );
      $payload        = json_encode($payload);
      $apns_url       = NULL;
      $apns_cert      = NULL;
      $apns_port      = 2195;
      #-------------------------(/initializations)---------------

      // developement or live server certificate
      if($development)
      {
          $apns_url = 'gateway.sandbox.push.apple.com';
          $apns_cert = __DIR__.'/Sobersystems.pem';
          
      }
      else
      {
          $apns_url = 'gateway.push.apple.com';
          $apns_cert = __DIR__.'/Sobersystems.pem';
      }

      $stream_context = stream_context_create();
      stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);

      $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT, $stream_context);
  
      $device_tokens  = $receivers;
    
      
      foreach($device_tokens as $device_token)
      {
          $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token)) . chr(0) . chr(strlen($payload)) . $payload;
          fwrite($apns, $apns_message);
      }

      @socket_close($apns);
      fclose($apns);
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
      curl_exec($curl_channel);
      curl_close($curl_channel);
      #-------------------------------(/curl)---------------------------------------

   }
   
   
   /** ----- This function is to detect device from user agent  (3) -------------------------------
   * @PARAMS: none
   * @RETURN: type of device(1: ios, 2:android)
   * **/
   
   /*
   public function detect_device()
   {
      $iPod               = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
      $iPhone             = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
      $iPad               = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
      $android            = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

      //do something with this information
      if( $iPod || $iPhone || $iPad)
      {
         return 1;
      }
      else if($android)
      {
         return 2;
      }
      else 
      {
         return 0;   
      }
   }
    */
   #-----------------------------------(/3)-----------------------------------
   
   
  /** ----- This function is to send push notification to device (4) -------------------------------
   * @PARAMS: message, device_token, device_type
   * @RETURN: none
   * **/
  
   public function send_notification($message = '' , $receivers=array(), $device_type='')
   {
      #-- debug--
      /*echo $device_type;
      print_r($receivers);
      echo $message;
      echo realpath('library/Sobersystems.pem');*/
      #---(/debug)--
      
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