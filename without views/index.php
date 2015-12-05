<?php

 //ini_set('display_errors','ON');
 require_once  'autoload.php';
 date_default_timezone_set('GMT');
 // -- get requested url --
 $currentEnv = realpath('.currentEnv')?file_get_contents('.currentEnv'):'production';
 $baseUrl    = ($currentEnv=='production')?'/btcapi/':'/';
 $baseDir    = __DIR__;
 
 define("APPLICATION_BASE",$baseUrl);
 define("APPLICATION_DIR",$baseDir);
 
 $url            = preg_replace('~^'.preg_quote($baseUrl).'~','',$_SERVER['REQUEST_URI']);
 $parsedUrl      = parse_url($url);
 $explodedPath   = explode('/', $parsedUrl['path']);
 $serviceClass   = 'services\\'.ucfirst($explodedPath[0]);
  $objResquest = new services\helpers\Request();
  
 if(!class_exists($serviceClass)){  
    $objResquest->sendErrorResponse(404, 404, 'invalid service request');
 } 
 $serviceAction  = $explodedPath[1];
 $serviceAction  = str_replace('-','','action'.preg_replace("/(\w+)/e","ucfirst('\\1')", ucfirst($serviceAction))); 
 $objService     = new $serviceClass;
 if(!method_exists($objService, $serviceAction)){
    $objResquest->sendErrorResponse(404, 404, 'invalid service request');
 }
 $objService->$serviceAction();


?>
