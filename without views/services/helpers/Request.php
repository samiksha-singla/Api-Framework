<?php

namespace services\helpers;

class Request{
   
   public function getRequest(){
      $request = $_REQUEST;
      return $request;
   }
   
   // function to get parameter from request
   public function getParam($key = null, $default = null){
     if (null === $key) {
            return $_REQUEST;
        }
     return (isset($_REQUEST[$key])) ? $_REQUEST[$key] : $default;
   }
   
      /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
    
     /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public function isGet()
    {
        if ('GET' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    private function _sendJson($header,array $data){
       header('Content-type: application/json',true,$header);
       echo json_encode($data);
       exit;
    }
    
    public function sendErrorResponse($header, $status, $statusText){
       $responseData = array('status'=>$status, 'statusText'=>$statusText);
       $this->_sendJson($header, $responseData);
    }
    
    public function sendSuccessResponse($statusText, array $data = array()){
        $responseData = array('status'=>200, 'statusText'=>$statusText);
        if($data){
           $responseData['data'] = $data;
        }
        $this->_sendJson(200, $responseData);
    }
    
}