<?php
#-ini settings --
ini_set('display_errors','ON');
require_once  'autoload.php';
date_default_timezone_set('GMT');
 // -- get requested url --
 $currentEnv = realpath('.currentEnv')?file_get_contents('.currentEnv'):'production';
 $baseUrl    = ($currentEnv=='production')?'/btcapi/':'/';
 define("APPLICATION_BASE",$baseUrl);
 define("APPLICATION_ENV",$currentEnv);
 
$objTestService = new \tests\Testservices();
$objTestService->testForgotPassword();
?>

 