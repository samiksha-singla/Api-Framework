<?php session_start();

// set application environment
$baseDir    = __DIR__;
$baseUrl    = '/';
defined('APPLICATION_DEBUG') or define('APPLICATION_DEBUG', (getenv('DEBUG') ? getenv('DEBUG') : false));
defined('APPLICATION_ENV') or define('APPLICATION_ENV', (getenv('ENV') ? getenv('ENV') : 'production'));
define("APPLICATION_DIR",$baseDir);

//enable error reporting if APPLICATION_DEBUG is ON
if(APPLICATION_DEBUG){
   ini_set('display_errors','ON');
   error_reporting(-1);
}

// set timezone for app
date_default_timezone_set ('GMT');

// autoload the required libraries
require_once 'autoload.php';


// set header to allow cross domain
use utilities\Response;
use utilities\Registry;
$objUtilResponse  = new Response();
$objUtilFunctions = new utilities\CommonFunctions();

if(isset($_SERVER['HTTP_ORIGIN'])){
  $objUtilResponse->allowCors($_SERVER['HTTP_ORIGIN']); //allow cross domain ajax request
}

// lets run the application 
 $url            = preg_replace('~^'.preg_quote($baseUrl).'~','',$_SERVER['REQUEST_URI']);
 $parsedUrl      = parse_url($url);
 $explodedPath   = explode('/', $parsedUrl['path']);
 $className      = ($explodedPath[0])?ucfirst($explodedPath[0]):'index';
 $className      = $objUtilFunctions->hypenToCamel($className);
 $serviceClass   = 'controllers\\'.ucfirst($className);

 //check if service class exixts or not
 if(!class_exists($serviceClass)){
    $objUtilResponse->renderJson(array('message'=>'invalid url request','status'=>'400'),400);
 }
 $objService     = new $serviceClass;
  
 // get action name to run
 $actionName  = (isset($explodedPath[1]) && !empty($explodedPath[1]))?$explodedPath[1]:'index';
 $actionName  = $objUtilFunctions->hypenToCamel($actionName);
 $serviceAction = 'action'.ucfirst($actionName);
 
 //check if action exists in service or not
 if(!method_exists($objService, $serviceAction)){
    $objUtilResponse->renderJson(array('message'=>'invalid url request','status'=>'400'),400);
 }
 
 //run service
 $objService->$serviceAction();
 
 // clear app registry for next http call;
 Registry::clearRegistry();
 
?>
