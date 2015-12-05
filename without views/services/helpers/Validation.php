<?php
namespace services\helpers;
class Validation{
  public function validateRequest(array $requiredParams)
  {
     $requestObj = new Request() ;

     if ($requestObj->isPost ())
     {
        $accessKeyId = $requestObj->getPost ( 'access_key', false );
        $signature   = trim($requestObj->getPost ( 'signature', false ));
        $timestamp = $requestObj->getPost ( 'timestamp', false ); // Required to generate variable signature
        $parameters = array (
               'timestamp' => $timestamp
        );        
        
        $allParamsPresent = true;
        foreach ($requiredParams as $paramName){
           $paramValue = $requestObj->getPost($paramName, false);
           if($paramName){
              $parameters[$paramName] = $paramValue;
           }
           else{
              $allParamsPresent = false;
              break;
           }
        }
        if ($accessKeyId && $signature && $timestamp)
        {
           // Okay we have all required parameters
           // Let's identify user
           $requestParams    = $requestObj->getRequest();
           $objServerHelper  = new ServerInfo();
           $url              = $objServerHelper->serverUrl (true);    
           $userSyncRestMdl  = new \lib\Api\Server ( $parameters, $accessKeyId, null, $url );               
           $isValidTimestamp = $userSyncRestMdl->isValidTimestamp ( $timestamp );
           if(!$isValidTimestamp){
             $requestObj->sendErrorResponse(403,403,'Invalid timestamp'); 
           }
           $userProductMdl = new \models\ApiProducts ();
           $apiProductDetails = $userProductMdl->isValidAccessKey ( $accessKeyId );               
            if ($apiProductDetails)
            {
               // Valid access key
               $userSyncRestMdl->setSecretKey ( $apiProductDetails->secret_key );
               $isValidSignature = $userSyncRestMdl->isValidSignature ( $signature );
               if ($isValidSignature === true)
               {
                  return $apiProductDetails;
               }
               else{
                   //$requestObj->sendErrorResponse(403,403,'expected signature is : '. $isValidSignature);
                   $requestObj->sendErrorResponse(403,403,'Invalid signature');
               }
            }
            else{
                $requestObj->sendErrorResponse(403,403,'Invalid access key.');
            }
        }
        else{
            $requestObj->sendErrorResponse(403,403,'Required parameters are missing.');
        }
     }
     else{
        $requestObj->sendErrorResponse(403,403,'Only post request are accepted.');
     }
  }

}