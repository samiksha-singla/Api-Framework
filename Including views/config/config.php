<?php
$config = array(
    'amsUrl'=>'http://aboutmyspeech.com',
    'amtUrl'=>'http://aboutmy360.com',
    'dmbUrl'=>'http://cimbaqa.a1technology.asia'
);

if(APPLICATION_ENV == 'dev'){
   $config = array(
    'amsUrl'=>'http://ams.a1technology.asia',
    'amtUrl'=>'http://amt.a1technology.asia',
    'dmbUrl'=>'http://209.160.65.49:2001');
}

if(APPLICATION_ENV == 'local'){
   $config = array(
    'amsUrl'=>'http://aboutmyspeech.loc',
    'amtUrl'=>'http://aboutmy360.loc',
    'dmbUrl'=>'http://cimba.dev'
   );  
}

return $config;
?>