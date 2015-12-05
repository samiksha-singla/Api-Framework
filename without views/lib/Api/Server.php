<?php

namespace lib\Api; 

class Server extends Api
{
   /**
    * Add authentication related and version parameters
    */
   private $_parameters = array();

   private $_accessKeyId;

   private $_secretKey;

   private $_serviceUrl;

   public function __construct ($parameters, $accessKeyId, $secretKey = null,  $serviceUrl)
   {
      $this->_accessKeyId = $accessKeyId;
      $this->_secretKey  = $secretKey;
      $this->_serviceUrl = $serviceUrl;
      $this->_parameters = $parameters;
   }

   
   public function isValidTimestamp ($gmtDateTime)
   {
      $phpDateObj = \DateTime::createFromFormat('Y-m-d\TH:i:s.uZ',$gmtDateTime, new \DateTimeZone('GMT'));
      if ($phpDateObj) {
         $clientTime = $phpDateObj->getTimestamp();
         $serverTime = $this->_getTime();
         $difference = $serverTime - $clientTime;
         
         #--debug--
         //file_put_contents('test.log', $difference);
         
         if ($difference <= 5 * 60 && $difference >= - 5 * 60) {
            // Time difference is not more than 5 minutes
            return true;
        }
      }
      return false;
   }
   
   private function _getTime ()
   {
//        $timestamp = false;
//         try {
//                $timeSyncObj = new \lib\TimeSync\TimeSync(array('generic'  => 'time.nist.gov',
//                                                                'fallback' => 'nist1.datum.com',
//                                                                'reserve'  => 'fr.pool.ntp.org'));
//                $timestamp = $timeSyncObj->getTimestamp();
//
//           }                               
//         catch (Exception $e){
//
//         }
      
         $timestamp = time();
         return $timestamp;
   }
   
   public function isValidSignature ($signature)
   {
      $this->_addRequiredParameters();
      $clientSignature = $signature;
      $serverSignature = $this->_parameters['signature'];
      if ($clientSignature === $serverSignature) {
         return true;
      }
      return $serverSignature;
   }

   private function _addRequiredParameters ()
   {
      $this->_parameters['access_key'] = $this->_accessKeyId;
      $this->_parameters['signature'] = $this->_signParameters(
      $this->_parameters, $this->_secretKey);
      return $this->_parameters;
   }

   private function _signParameters (array $parameters, $key)
   {
      $stringToSign = $this->_calculateStringToSign($parameters);
      return $this->_sign($stringToSign, $key);
   }

   private function _calculateStringToSign (array $parameters)
   {
      $data = 'POST';
      $data .= "\n";
      $endpoint = parse_url($this->_serviceUrl);
      $data .= $endpoint['host'];
      $data .= "\n";
      $uri = array_key_exists('path', $endpoint) ? preg_replace('~^'.preg_quote(APPLICATION_BASE).'~','',$endpoint['path']) : null;
      if (! isset($uri)) {
         $uri = "/";
      }
      $uriencoded = implode("/", 
            array_map(
                  array(
                        $this,
                        "_urlencode"
                  ), explode("/", $uri)));
      $data .= $uriencoded;
      $data .= "\n";
      uksort($parameters, 'strcmp');
      $data .= $this->_getParametersAsString($parameters); 
      
      //debug
      //file_put_contents('test.log', $data);
      return $data;
   }

   private function _urlencode ($value)
   {
      return str_replace('%7E', '~', rawurlencode($value));
   }

   /**
    * Convert paremeters to Url encoded query string
    */
   private function _getParametersAsString (array $parameters)
   {
      $queryParameters = array();
      foreach ($parameters as $key => $value) {
         $queryParameters[] = $key . '=' . $this->_urlencode($value);
      }
      return implode('&', $queryParameters);
   }

   /**
    * Computes RFC 2104-compliant HMAC signature.
    */
   private function _sign ($data, $key, $hash = 'sha256')
   {
      
      $signature =  base64_encode(hash_hmac($hash, $data, $key, true)); // make it true
      return $signature;
   }

   public function setSecretKey ($secretKey)
   {
      $this->_secretKey = $secretKey;
      return $this;
   }
   
   
   
}