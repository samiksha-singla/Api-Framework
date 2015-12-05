<?php
namespace utilities;
class Validator{
  public function validateRequest(array $requiredParams)
  {
     $objUtilResponse = new Response() ;
     $objUtilRequest  = new Request();
     
     if ($objUtilRequest->isPost ())
     {
        $accessKeyId = $objUtilRequest->getPost ( 'access_key', false );
        $signature   = trim($objUtilRequest->getPost ( 'signature', false ));
        $timestamp = $objUtilRequest->getPost ( 'timestamp', false ); // Required to generate variable signature
        $parameters = array (
               'timestamp' => $timestamp
        );

        $allParamsPresent = true;
        foreach ($requiredParams as $paramName){
           $paramValue = $objUtilRequest->getPost($paramName, false);
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
           $requestParams    = $objUtilRequest->getRequest();
           $objServerHelper  = new ServerInfo();
           $url              = $objServerHelper->serverUrl (true);
           $userSyncRestMdl  = new \api\Server ( $parameters, $accessKeyId, null, $url );
           $isValidTimestamp = $userSyncRestMdl->isValidTimestamp ( $timestamp );
           if(!$isValidTimestamp){
             $objUtilResponse->renderJson(array('status'=>403,'message'=>'Invalid Timestamp'),403);
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
                   $objUtilResponse->renderJson(array('status'=>403,'message'=>'Invalid Signature'),403);
               }
            }
            else{
                $objUtilResponse->renderJson(array('status'=>403,'message'=>'Invalid access key.'),403);
            }
        }
        else{
            $objUtilResponse->renderJson(array('status'=>403,'message'=>'Required parameters are missing.'),403);
        }
     }
     else{
        $objUtilResponse->renderJson(array('status'=>403,'message'=>'Only post requests are accepted'),403);
     }
  }

}