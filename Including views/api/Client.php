<?php
namespace api;
use timeSync\TimeSync;
class Client
{
   
   protected $_url;
   protected $_requiredParameters;
   protected $_parameters;
   protected $_accessKeyId;
   protected $_secretKey;

   public function __construct($url,$accessKeyId,$sceretKey){
      $this->_url = $url;
      $this->_accessKeyId = $accessKeyId;
      $this->_secretKey = $sceretKey;
   }
   
   protected function _request ()
   {
      $result = $this->_doRequest();
      if($result){
         return json_decode($result,true);
      }
      return $result;     
   }
      
      
   protected function _getConfig(){
      $config = include APPLICATION_DIR."/config/config.php";
      return $config;
   } 



   private function _addRequiredParameters ()
   {
      
      if(empty($this->_requiredParameters)){
         $this->_requiredParameters = array_keys($this->_parameters);
      }
      
      $parametersToSign = []; 
      
      foreach($this->_requiredParameters as $param){
         $parametersToSign[$param] = $this->_parameters[$param];
      }
      
      $parametersToSign['access_key'] = $this->_accessKeyId;
      $parametersToSign['timestamp'] = $this->_getFormattedTimestamp();
      
      $this->_parameters['access_key'] = $this->_accessKeyId;
      $this->_parameters['timestamp'] = $this->_getFormattedTimestamp();
      $this->_parameters['signature'] = $this->_signParameters($parametersToSign);
   }

   private function _signParameters (array $parameter)
   {
      $stringToSign = $this->_calculateStringToSign($parameter);
      return $this->_sign($stringToSign);
   }

   private function _calculateStringToSign (array $parameters)
   {
      $data = 'POST';
      $data .= "\n";
      $endpoint = parse_url($this->_url);      
      $data .= $endpoint['host'];
      $data .= "\n";
      $uri = array_key_exists('path', $endpoint) ? $endpoint['path'] : null;
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
      return $data;
   }

   /**
    * Formats date as ISO 8601 timestamp
    */
   private function _getFormattedTimestamp ()
   {
      $timestamp = $this->_getTime();
      return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", $timestamp);
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
   private function _sign ($data,$hash = 'sha256')
   {
      return base64_encode(hash_hmac($hash, $data, $this->_secretKey, true));
   }

   private function _doRequest ()
   {      
      $this->_addRequiredParameters();
      #--initiate curl channel
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,  $this->_url);
      curl_setopt($ch,CURLOPT_POST, true);
      curl_setopt($ch,CURLOPT_POSTFIELDS, $this->_parameters);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch,CURLOPT_HEADER, FALSE);
      $response = curl_exec($ch);   
      $curl_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
      if($curl_status == 200){
         return $response;
      }
      return false;
      
   }

   private function _getTime ()
   {
      $timestamp = false;
      try {
             $timeSyncObj = new TimeSync(array('generic'  => '0.pool.ntp.org',
                                               'fallback' => '1.pool.ntp.org',
                                               'reserve'  => '2.pool.ntp.org'));
             $timestamp = $timeSyncObj->getTimestamp();

        }                               
      catch (Exception $e){

      }
      return $timestamp;
   }
   
   public function validateSignature($parameters, $signature, $url){
      $parameters['access_key'] = $this->_accessKeyId;
      $calcSignature = $this->_signParameters($parameters, $this->_secretKey, $url);
      return ($signature === $calcSignature);
   }
}