<?php 
namespace controllers;

class Ssoservice extends Basecontroller{

   public function actionSso(){
      $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
      $idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
      $idp = \SimpleSAML_IdP::getById('saml2:' . $idpEntityId);
      \sspmod_saml_IdP_SAML2::receiveAuthnRequest($idp);
      assert('FALSE');
   }
   
   public function actionSlo(){
      $returnUrl = $this->_request->getParam('return');  
      \utilities\Registry::clearRegistry();
      $auth = new \SimpleSAML_Auth_Simple('authinstance');
      $auth->logout($returnUrl);
      assert('FALSE');
   }
   
}

?>