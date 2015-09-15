<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Session;
class Module
{
    public function onBootstrap(MvcEvent $e) {
        $serviceManager = $e->getApplication()->getServiceManager();
        $config = $serviceManager->get('config');
        if($config['server_loc'] == 'live' && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '54.69.27.231') {
            $response = $e->getResponse();
            $response->setStatusCode(401);
            $response->sendHeaders();
            exit();
        } 
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this -> initAcl($e);
        $e->getApplication() -> getEventManager() -> attach('route', array($this, 'checkAcl'));
    }

    public function initAcl(MvcEvent $e) {
 
        $acl = new \Zend\Permissions\Acl\Acl();
        $roles = include __DIR__ . '/config/module.acl.roles.php';
        $allResources = array();
        foreach ($roles as $role => $resources) {

            $role = new \Zend\Permissions\Acl\Role\GenericRole($role);
            $acl -> addRole($role);

            //$allResources = array_merge($resources, $allResources);

            //adding resources
            foreach ($resources as $resource) {
                 // Edit 4
                 if(!$acl ->hasResource($resource))
                    $acl -> addResource(new \Zend\Permissions\Acl\Resource\GenericResource($resource));
            }
            //adding restrictions
            foreach ($resources as $resource) {
                $acl -> allow($role, $resource);
            }
        }

        //setting to view
        $e -> getViewModel() -> acl = $acl;

    }

    public function checkAcl(MvcEvent $e) {
        $request = $e->getRequest();
        $route = $e -> getRouteMatch() -> getMatchedRouteName();
        $controller = $e->getRouteMatch ()->getParam ( 'controller' );
        $action = $e->getRouteMatch ()->getParam ( 'action' );
        if($action == '') {
            if($request->isPost()) {
                $action = 'create';
            }else if($request->isGet()) {
                 $action = 'get';
            }
            else if($request->isPut()) {
                 $action = 'update';
            }
            else if($request->isDelete()) {
                 $action = 'delete';
            }
        }

        
        
        $requestedResourse = $controller . "-" . $action;

       
        $serviceManager = $e->getApplication()->getServiceManager();
        $authService = $serviceManager->get('doctrine.authenticationservice.orm_default');
        $loggedInUser = $authService->getIdentity();
        //you set your role
        //$userRole = ($loggedInUser && $loggedInUser->role) ? $loggedInUser->role : 'guest';
        // if(!empty($loggedInUser)) {
        //     $idleTimeout = 600; // timeout after 1 hour</span>
        //     if(isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) {
        //       $e->getRouteMatch()->setParam('controller', 'User\Controller\User')->setParam('action', 'forceLogout');
        //     }
        //     $_SESSION['timeout_idle'] = time() + $idleTimeout;
        // }

        // if ((!($e -> getViewModel() -> acl ->hasResource($requestedResourse)) || !$e -> getViewModel() -> acl -> isAllowed($userRole, $requestedResourse)) && !$e -> getViewModel() -> acl -> isAllowed('all', $requestedResourse)) {
        //     $url = 'invalidAccess';       
        //     $response = $e -> getResponse();
        //     $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ) );
        //     $response->setStatusCode ( 401 );
        //     $response->sendHeaders ();
        //     exit ();
        // }
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'mail.transport' => function (ServiceManager $serviceManager) {
                    $config = $serviceManager->get('Config'); 
                    $transport = new Smtp();                
                    $transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));
                    return $transport;
                },
            )
            
        );
    }
}
