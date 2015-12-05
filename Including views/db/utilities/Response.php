<?php
namespace utilities;
class Response{
   
   protected $_allowedDomain;
   
   public function __construct() {
      $config = include APPLICATION_DIR.'/config/config.php';
      $this->_allowedDomain = array($config['amsUrl'],$config['amtUrl'],$config['dmbUrl']);
   }


   
   /**
    * Function to set header to enable CORS(Cross-origin resource sharing (CORS))
    * @param string $origin Origin from which request is 
    * @return void
    * '**/
   public function allowCors($origin){
      // check if origin is in allowed domains 
      if(in_array($origin, $this->_allowedDomain) && $origin){
         header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
         //if you need cookies or login etc
         header('Access-Control-Allow-Credentials: true');
      }
      
   }
   
   /**
    * Function to render json 
    * @param  array $array  Array which is to be sent in json
    * @param  array $header http headet to send 
    * @return void
    * **/
   public function renderJson(array $array, $header=200){
      if($array){
         header('content-type:application/json',true,$header);
         echo json_encode($array);
         exit;
      }
   }
   
   
   /**
    * Function to redirect 
    * **/
   public function redirect($url){
      if($url){
         header('Location:'.$url);
         exit();
      }
   }
   
}

?>
