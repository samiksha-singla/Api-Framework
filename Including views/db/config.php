<?php

// in case of developement return this configuration
if(APPLICATION_ENV == 'local'){
   return array(
      'host'     =>'localhost',
      'dbname'   =>'cimba_sso',
      'user'     =>'root',
      'password' =>'welcome'
   ) ;
}

// return production configuration by default
return array(
    'host'     =>'localhost',
    'dbname'   =>'CimbaAuth',
    'user'     =>'CimbaQ',
    'password' =>'CiMb@!@Q'
 ) ;
?>

