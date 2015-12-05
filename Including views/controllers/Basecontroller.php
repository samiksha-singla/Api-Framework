<?php
namespace controllers;
class Basecontroller {
   protected $_request;
   protected $_response;
   protected $_validator;
   protected $_serverinfo;
   protected $_viewpath;
   public function __construct() {
      $this->_request  = new \utilities\Request();
      $this->_response = new \utilities\Response();
      $this->_validator = new \utilities\Validator();
      $this->_serverinfo = new \utilities\ServerInfo();
      $this->_viewpath = APPLICATION_DIR."/views/";
   }
   
   public function render($view,$data){
      $viewFile = $this->_viewpath.$view.".php";
      if(file_exists($viewFile)){
         extract($data);
         ob_start();
         ob_implicit_flush(false);
         require_once $viewFile;
         ob_flush();   
      }
      else{
         throw new Exception("View file does not exixts");   
      }
      
   }
}
?>