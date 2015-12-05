<?php
namespace services;

class BaseService{
 
   protected $_request;
   
   public function __construct() {
      $this->_setRequest();
   }
   
   private function _setRequest(){
      if(!$this->_request){
       $this->_request = new helpers\Request();
      }
   }
   
   
}
?>

