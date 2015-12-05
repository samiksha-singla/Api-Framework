<?php
namespace models;
class ApiProducts extends BaseModel
{
   protected $_name = 'api_products';

   public function isValidAccessKey($accessKeyId){
      $row  = \R::findOne($this->_name,'access_key_id = :akid',array(':akid' =>$accessKeyId) );
      if($row){
         return $row;
      }
      return false;
   }
}
