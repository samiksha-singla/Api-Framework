<?php
// class for other common functions

namespace utilities;

class CommonFunctions{
   
   /**
    * Function to change hypen seperated string to camel case
    * @param string $hypenedString 
    * @return string
    * **/
   
   public function hypenToCamel($hypenedString){
      $hypenedCamelCasedString  = preg_replace_callback(
                                       "/(\w+)/",
                                       function($matches){
                                           foreach($matches as $match){
                                               return ucfirst($match);
                                           }
                                       }, 
                                       ucfirst($hypenedString)
                                   );
      return str_replace("-", '', $hypenedCamelCasedString);
                                       
   }
   
}

?>