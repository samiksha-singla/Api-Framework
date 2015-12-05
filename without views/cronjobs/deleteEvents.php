<?php
 ini_set('display_errors','ON');
 require_once  '../autoload.php';
 date_default_timezone_set('GMT');
 // -- get requested url --
 $currentEnv = realpath('.currentEnv')?file_get_contents('.currentEnv'):'production';
 $baseUrl    = ($currentEnv=='production')?'/btcapi/':'/';
 $baseDir    = __DIR__;
 
 define("APPLICATION_BASE",$baseUrl);
 define("APPLICATION_DIR",$baseDir);
 
 $objEventsModel = new models\Events;
 $timeToDeleteBeans= time();
 $beansToDelete =  \R::findAll('events', 'end_time+(24*60*60) <= :time',array(':time'=>$timeToDeleteBeans));
 \R::trashAll($beansToDelete);
?>