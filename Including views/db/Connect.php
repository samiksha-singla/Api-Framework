<?php
namespace db;

// include redbeans file
require_once APPLICATION_DIR.'/db/rb.php';

class Connect {
   
   protected $_dbConnect = false;
   protected static $_dbname;
   protected static $_user;
   protected static $_password;
   protected static $_host;
   protected $_redbeans;
   protected $_rbToolbox;
   
   public function  __construct(){
      if(!\R::getDatabaseAdapter()){
         $dbConfig = include APPLICATION_DIR.'/db/config.php';       
         static::$_host = $dbConfig['host'];
         static::$_dbname = $dbConfig['dbname'];
         static::$_user = $dbConfig['user'];
         static::$_password = $dbConfig['password'];
         \R::setup('mysql:host='.static::$_host.';dbname='.static::$_dbname, static::$_user , static::$_password,true); //for both mysql or mariaDB
      }
      $this->_redbeans    = \R::getToolBox()->getRedBean();
      $this->_rbToolbox   = \R::getToolBox();
   }
   
   public function getRb(){
      return $this->_redbeans;
   }
   
   public function getRbToolBox(){
      return $this->_rbToolbox;
   }
   
}
?>