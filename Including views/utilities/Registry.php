<?php
namespace utilities;

Class Registry{
   
   private static $_registry = array();
   
   public static function getRegistry($param = null){
      if($param){
         if(isset(self::$_registry[$param])){
            return self::$_registry[$param];
         }
         return;
      }
      return self::$_registry[$param];
   }
   
   public static function setRegistry($key,$value){
      self::$_registry[$key] = $value;
   }
   
   public static function clearRegistry(){
     self::$_registry = array();
   }
}

?>