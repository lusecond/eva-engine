<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Session
 */

namespace Core;

use Eva\Api;
use Zend\Authentication\AuthenticationService;
use Zend\Config\Config;
use Zend\Di\Di;
use Zend\Di\Config as DiConfig;


/**
 * Session ManagerInterface implementation utilizing ext/session
 *
 * @category   Zend
 * @package    Zend_Session
 */
class Auth
{
    const STORAGE_NAMESPACE = 'Eva_Auth';

    protected $authService;

    public function getAuthService()
    {
        return $this->authService;
    }

    public function getDiConfig(array $config = array())
    {
        $globalConfig = Api::_()->getConfig();
        $defaultConfig = array('instance' => array(
            'Zend\Authentication\Storage\Session' => array(
                'parameters' => array(
                    //TODO : set session manager here
                    'namespace'  => self::STORAGE_NAMESPACE,
                ),
            ),
            'Eva\Authentication\Adapter\Config' => array(
                'parameters' => array(
                    'configArray'  => $globalConfig['superadmin'],
                ),
            ),
            'Zend\Authentication\AuthenticationService' => array(
                'parameters' => array(
                    'storage'              => 'Zend\Authentication\Storage\Session',
                    'adapter'              => 'Eva\Authentication\Adapter\Config',
                )
            ),
        ));

        if(isset($globalConfig['authentication'])){
            $defaultConfig = $this->merge($defaultConfig, $globalConfig['authentication']);
            $config = $this->merge($defaultConfig, $config);
        } else {
            $config = $this->merge($defaultConfig, $config);
        } 
        return $config;
    }


    public function configAuthenticate($username, $password, array $autoConfig = array())
    {
        $config = array('instance' => array(
            'Eva\Authentication\Adapter\Config' => array(
                'parameters' => array(
                    'username' => $username,
                    'password' => $password,
                ),
            ),
        ));
        $config = $this->getDiConfig($config);

        $di = new Di();
        $di->configure(new DiConfig($config));
        $this->authService = $di->get('Zend\Authentication\AuthenticationService');

        return $this->authService->getAdapter()->authenticate();
    }

    public function dbAuthenticate()
    {

    }

    public function restfulAuthenticate()
    {

    }

    protected function merge(array $global, array $local)
    {
        if(!$local) {
            return $global;
        }

        $global = new Config($global);
        $local = new Config($local);
        $global->merge($local);
        return $global->toArray();
    }
}