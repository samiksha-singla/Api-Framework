<?php

/**
 * A PSR-4 compatible class loader.
 *
 * See http://www.php-fig.org/psr/psr-4/
 *
 * @author Alexander M. Turek <me@derrabus.de>
 */
class Psr4ClassLoader
{
    /**
     * @var array
     */
    private $prefixes = array();
    /**
     * @param string $prefix
     * @param string $baseDir
     */
    public function addPrefix($prefix, $baseDir)
    {
        $prefix = trim($prefix, '\\').'\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->prefixes[] = array($prefix, $baseDir);
    }
    /**
     * @param string $class
     *
     * @return string|null
     */
    public function findFile($class)
    {
        $class = ltrim($class, '\\');
        foreach ($this->prefixes as $current) {
            list($currentPrefix, $currentBaseDir) = $current;
            if (0 === strpos($class, $currentPrefix)) {
                $classWithoutPrefix = substr($class, strlen($currentPrefix));
                $file = $currentBaseDir.str_replace('\\', DIRECTORY_SEPARATOR, $classWithoutPrefix).'.php';
                if (file_exists($file)) {
                    return $file;
                }
            }
        }
    }
    /**
     * @param string $class
     *
     * @return bool
     */
    public function loadClass($class)
    {
        $file = $this->findFile($class);
        if (null !== $file) {
            require $file;
            return true;
        }
        return false;
    }
    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }
    /**
     * Removes this instance from the registered autoloaders.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }
}

$loader = new Psr4ClassLoader();

$loader->addPrefix('controllers\\', __DIR__.'/controllers');
$loader->addPrefix('models\\', __DIR__.'/models');
$loader->addPrefix('db\\', __DIR__.'/db');
$loader->addPrefix('utilities\\', __DIR__.'/utilities');
$loader->addPrefix('api\\', __DIR__.'/api');
$loader->addPrefix('timeSync\\', __DIR__.'/timeSync');
$loader->addPrefix('mailer\\', __DIR__.'/mailer');
$loader->register();


?>