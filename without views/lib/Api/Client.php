<?php
namespace lib\Api; 
Class Client extends Api{
   /**
    * Add authentication related and version parameters
    */
   private $_parameters = array();
   private $_accessKeyId;
   private $_secretKey;
   private $_serviceUrl;
   private $_requiredParams = array();
   
   public function __construct($parameters = array(), $accessKeyId, $secretKey, $serviceUrl, $requiredParams = array()){
      $this->_accessKeyId = $accessKeyId;
      $this->_secretKey = $secretKey;
      $this->_serviceUrl = $serviceUrl;
      $this->_parameters = $parameters;
      $this->_requiredParams = $requiredParams;
   }
   
   private function _addRequiredParameters()
   {
      $this->_parameters['access_key'] = $this->_accessKeyId;
      $this->_parameters['timestamp']  = $this->_getFormattedTimestamp();
      //By default All params are required
      $dataToBeSigned = array();
      if(count($this->_requiredParams) > 0){
         $this->_requiredParams[] = 'access_key';
         $this->_requiredParams[] = 'timestamp';
         foreach ($this->_requiredParams as $paramName){
            $dataToBeSigned[$paramName] = $this->_parameters[$paramName];
         }
      }
      else{
         $dataToBeSigned = $this->_parameters;
      }
      $this->_parameters['signature'] = $this->_signParameters($dataToBeSigned, $this->_secretKey);
      
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
            array_map(array(
                  $this,
                  "_urlencode"
            ), explode("/", $uri)));
      $data .= $uriencoded;
      $data .= "\n";
      uksort($parameters, 'strcmp');
      $data .= $this->_getParametersAsString($parameters);
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
   
   private function _getTime ()
   {
         //        $timestamp = false;
         //         try {
         //                $timeSyncObj = new \lib\TimeSync\TimeSync(array('generic'  => 'time.nist.gov',
         //                                                  'fallback' => 'nist1.datum.com',
         //                                                  'reserve'  => 'fr.pool.ntp.org'));
         //                $timestamp = $timeSyncObj->getTimestamp();
         //
         //           }                               
         //         catch (Exception $e){
         //
         //         }
         return time();
   }
   /**
    * Formats date as ISO 8601 timestamp
    */
   private function _getFormattedTimestamp ()
   {
      $timestamp = $this->_getTime();
      return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", $timestamp);
   }
   /**
    * Computes RFC 2104-compliant HMAC signature.
    */
   private function _sign ($data, $key, $hash = 'sha256')
   { 
      $signature =  base64_encode(hash_hmac($hash, $data, $key, true));       
      return $signature;
   }
   
   protected function _doRequest(){

      $response = false;
      try{
           $this->_addRequiredParameters();
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL,  $this->_serviceUrl);
           curl_setopt($ch, CURLOPT_POST, true);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_parameters);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           $response = curl_exec($ch);
      }
      catch (\Exception $e){
         //TODO log message
      }
      return array('responseObj'=>$response);
   }
   
   public function request()
   {
      $result = $this->_doRequest();
      $response = $result ['responseObj'];
      echo "<pre>";
      echo "\n response \n";
      print_r ( $response );
      echo "<pre/>";
      die ();
   }

  
}