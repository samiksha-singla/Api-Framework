<?php
namespace utilities;
class ServerInfo
{
    /**
     * Scheme
     *
     * @var string
     */
    protected $_scheme;

    /**
     * Host (including port)
     *
     * @var string
     */
    protected $_host;
    
    protected $_referrers;
    

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        switch (true) {
            case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
            case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
            case (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)):
                $scheme = 'https';
                break;
            default:
            $scheme = 'http';
        }
        $this->setScheme($scheme);

        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            $this->setHost($_SERVER['HTTP_HOST']);
        } else if (isset($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'])) {
            $name = $_SERVER['SERVER_NAME'];
            $port = $_SERVER['SERVER_PORT'];

            if (($scheme == 'http' && $port == 80) ||
                ($scheme == 'https' && $port == 443)) {
                $this->setHost($name);
            } else {
                $this->setHost($name . ':' . $port);
            }
        }
        
        
        $config = include APPLICATION_DIR.'/config/config.php';
        $this->_referrers = array(  
                                    '1'=>$config['amsUrl'],
                                    "2"=>$config['amtUrl'],
                                    "3"=>$config['dmbUrl'],
                                 );
        
    }

    /**
     * View helper entry point:
     * Returns the current host's URL like http://site.com
     *
     * @param  string|boolean $requestUri  [optional] if true, the request URI
     *                                     found in $_SERVER will be appended
     *                                     as a path. If a string is given, it
     *                                     will be appended as a path. Default
     *                                     is to not append any path.
     * @return string                      server url
     */
    public function serverUrl($requestUri = null)
    {
        if ($requestUri === true) {
            $path = $_SERVER['REQUEST_URI'];
        } else if (is_string($requestUri)) {
            $path = $requestUri;
        } else {
            $path = '';
        }

        return $this->getScheme() . '://' . $this->getHost() . $path;
    }

    /**
     * Returns host
     *
     * @return string  host
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Sets host
     *
     * @param  string $host                new host
     * @return Zend_View_Helper_ServerUrl  fluent interface, returns self
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Returns scheme (typically http or https)
     *
     * @return string  scheme (typically http or https)
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Sets scheme (typically http or https)
     *
     * @param  string $scheme              new scheme (typically http or https)
     * @return Zend_View_Helper_ServerUrl  fluent interface, returns self
     */
    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
        return $this;
    }
    
    /**
     * Function to get host from refferer 
     * @param ini $referrerId
     * @return string url of the host 
     * **/
    public function getHostFromRefferer($reffererId){
       return $this->_referrers[$reffererId];
    }
    
}