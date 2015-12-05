<?php 
namespace models;
use db\Connect;
use utilities\Registry;
class BaseModel{   
   protected $_redBeans;
   protected $_redBeansToolBox;
   
   public function __construct() {
      $objDbConnect = new Connect();
      $this->_redBeans = $objDbConnect->getRb();
      $this->_redBeansToolBox = $objDbConnect->getRbToolBox();
   }
   
   public function getRedBeans(){
      return $this->_redBeans;
   }
}
?>