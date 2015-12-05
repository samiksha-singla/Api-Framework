<?php

namespace services;

class Pushnotifier extends BaseService {

   public function actionPushToIos() {

      $receivers = json_decode($this->_request->getParam('receivers', null), true);
      $message = $this->_request->getParam('message', null);

      #--------------- initializations ------------------------------
      $message = substr($message, 0, 255);
      $sound = 'default';
      $development = false;
      $badge = '1';
      $payload['aps'] = array(
          'alert' => $message,
          'badge' => intval($badge),
          'sound' => $sound
      );
      $payload = json_encode($payload);
      $apns_url = NULL;
      $apns_cert = NULL;
      $apns_port = 2195;
      #-------------------------(/initializations)---------------
      // developement or live server certificate
      if ($development) {
         $apns_url = 'gateway.sandbox.push.apple.com';
         $apns_cert = APPLICATION_DIR . '/lib/notifier/btcapi_dev.pem';
      } else {
         $apns_url = 'gateway.push.apple.com';
         $apns_cert = APPLICATION_DIR . '/lib/notifier/btcapi_prod.pem';
      }


      $device_tokens = $receivers;
      
      foreach ($device_tokens as $device_token) {
         //$caFile= __DIR__.'/entrust_2048_ca.cer';

         $stream_context = stream_context_create();
         //stream_context_set_option($stream_context, 'ssl', 'cafile', $caFile);
         stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
         stream_context_set_option($stream_context, 'ssl', 'verify_peer', FALSE);

         $apns = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 2, STREAM_CLIENT_CONNECT | STREAM_CLIENT_CONNECT, $stream_context);
         $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token)) . chr(0) . chr(strlen($payload)) . $payload;
         $result = fwrite($apns, $apns_message, strlen($apns_message));
         @socket_close($apns);
         fclose($apns);
      }
   }

}
?>

