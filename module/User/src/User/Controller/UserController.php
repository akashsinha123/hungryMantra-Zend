<?php
namespace User\Controller;

use User\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

use User\Exception\NotFoundException;

class UserController extends AbstractRestfulJsonController{

    protected $em;

    public function getEntityManager(){
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    public function indexAction(){
        $users = $this->getEntityManager()->getRepository('User\Entity\User')->findAll();
        
        $users = array_map(function($user){
            return $user->toArray();
        }, $users);
        return new JsonModel($users);
    }
    
    public function getList(){   
        // Action used for GET requests without resource Id
        $users = $this->getEntityManager()->getRepository('User\Entity\User')->findAll();
        $users = array_map(function($user){
            return $user->toArray();
        }, $users);
        return new JsonModel($users);
    }

    public function get($id){   
        // Action used for GET requests with resource Id
        $user = $this->getEntityManager()->getRepository('User\Entity\User')->find($id);
        return new JsonModel(
            $user->toArray()
        );
    }

    public function getMyUsersAction(){
        return $this->getList();
    }

    public function create($data) {
        $user = $this->createUser($data);

        return new JsonModel(array('status'=>'ok','data'=>$user->toArray()));
    }

    public function createUser($data) {
        $this->getEntityManager();
        // $flag_image_type = false;
        // if(!empty($data['avatar']) && !empty($data['content_type']) && ($data['content_type'] == 'image/png' || $data['content_type'] == 'image/jpeg' || $data['content_type'] == 'image/jpg')) {
        //     $data['has_avatar'] = true;
        //     $data['image_ext'] = $data['extention'];
        //     $flag_image_type = true;
        // }
        // $geoCountry = $this->geoIpCountry();
        // $data['country'] = $geoCountry->getCountryFromIP($data['ip_address'], "name");
        // $data['random_number'] = rand();
        // $user = new \User\Entity\User($data);
        // $user->validate($this->em);
        // $user->setPassword($this->encriptPassword(
        //                    $this->getStaticSalt(), 
        //                    $user->getPassword()
        // ));
        // $user->setCreatedDate(date('Y-m-d'));
        
        // $this->getEntityManager()->persist($user);
        // $this->getEntityManager()->flush();

        // if(!empty($data['avatar']) && $user->getSource() == 'web' && $flag_image_type) {
        //     $this->uploadAvatar($data, $user);
        // }

        if($user->getSource() == 'web') {
            $config = $this->getServiceLocator()->get('Config');
            $viewContent = new \Zend\View\Model\ViewModel(
              array(
                  'random_number'    => $data['random_number'],
                  'user'             => $user->toArray(),
                  'message'          => 'Please click on the below link to activate your account',
                  'client_url'       => $config['client_url']
            ));
            $data['random_number'] = $data['random_number'];

            $data['subject'] = "Narwi Account Activate";
            $helper = $this->CommanHelper();
            $helper->send_mail($viewContent, $user->getEmail(), $data, 'activate_account');
        }
        
        return $user;
    }

    public function update($id, $data){
        // Action used for PUT requests
        $user = $this->getEntityManager()->getRepository('User\Entity\User')->find($id);
        $user->set($data);
        $user->validate($this->em);
        
        $this->getEntityManager()->flush();
        
        return new JsonModel($user->toArray());
    }

    public function delete($id){
        // Action used for DELETE requests
        $user = $this->getEntityManager()->getRepository('User\Entity\User')->find($id);
        $this->getEntityManager()->remove($user);
        
        $this->getEntityManager()->flush();
        
        return new JsonModel($user->toArray());
    }

    public function signupAction() {
       $request = $this->getRequest();
       $data = $this->getRequest()->getContent();
        if ($request->isPost() && !empty($data)) {
            $data = (!empty($data))? get_object_vars(json_decode($data)) : '';
            
            $data['random_number'] = rand();

            $config = $this->getServiceLocator()->get('Config');

            $viewContent = new \Zend\View\Model\ViewModel(
              array(
                  'random_number'    => $data['random_number'],
                  'message'          => 'Please click on the below link to activate your account'
            ));
            
            
            $data['subject'] = "hungrymantra Account Activate";
            // print_r($data);
            // print_r($viewContent);
            // die();
            $helper = $this->CommanHelper();
            $helper->send_mail($viewContent, $data['email'], $data, 'activate_account');
            
            return new JsonModel(array('status'=>'ok','message'=>'OTP has been sent to your gmail','data'=>$data));
        }
        $this->getResponse()->setStatusCode(400);
        return new JsonModel();
    }

    public function loginAction() {
       $request = $this->getRequest();
       $data = $this->getRequest()->getContent();
        if ($request->isPost() && !empty($data)) {
            $data = (!empty($data))? get_object_vars(json_decode($data)) : '';
            $data = $this->commonLogin($data, true);
            return new JsonModel($data);
        }
        $this->getResponse()->setStatusCode(400);
        return new JsonModel();
    }

    function commonLogin($data, $has_encrypt) {
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $adapter = $authService->getAdapter();
        $adapter->setIdentityValue($data['email']);
        if($has_encrypt)
            $data['password'] = $this->encriptPassword($this->getStaticSalt(),  $data['password']);
        $adapter->setCredentialValue($data['password']);
        $authResult = $authService->authenticate();
        if ($authResult->isValid()) {
            // if(!$authResult->getIdentity()->getIsActive()) {
            //     $data['random_number'] = rand();
            //     $helper = $this->CommanHelper();
            //     $user = $helper->updateUser($authResult->getIdentity()->getId(), $data);
            //     $authService->clearIdentity();
            //     $this->activateAccountEmail($user);
            //     return array('status'=>'error', 'data'=>'Please Activate your account , we sent an email with link');
            // }
            // if($authResult->getIdentity()->getIsloggedIn()) {
            //     $last_login_time = $authResult->getIdentity()->getLastLogin();
            //     $current_time = date("Y-m-d H:i:s");
            //     $newtimestamp = strtotime("$last_login_time + 10 minute");

            //     $last_login_time_plus_10_min = date('Y-m-d H:i:s', $newtimestamp);
            //     if($last_login_time_plus_10_min <= $current_time) {
            //         $this->commonLogout();
            //     }
            //     // $this->getResponse()->setStatusCode(400);
            //     return array('status'=>'error', 'data'=>'Sorry you have already logged in another system, your last session is not properly logged out please try after some time');
            // }
           // $data['is_logged_in'] = 1;
           // $data['last_login'] = date("Y-m-d H:i:s");
           $helper = $this->CommanHelper();
           $user = $helper->updateUser($authResult->getIdentity()->getId(), $data);
           $identity = $authResult->getIdentity();
           $sessionManager = new \Zend\Session\SessionManager();
           $sessionManager->regenerateId();
           $user = $identity->toArray();
           unset($user['password']);
           
           return array('status'=>'ok', 'data' => $user);
        } else {
            // $this->getResponse()->setStatusCode(400);
            return array('status'=> 'error','data'=>"Invalid Credentials");
        }
    }

    public function encriptPassword($staticSalt, $password) {
        $password      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $staticSalt ), $password, MCRYPT_MODE_CBC, md5( md5( $staticSalt ) ) ) );
        // return $password = md5($staticSalt . $password);
        return $password;
    }

    public function getStaticSalt() {
        $staticSalt = '';
        $config = $this->getServiceLocator()->get('Config');
        $staticSalt = $config['static_salt'];
        return $staticSalt;
    }

    public function commonLogout() {
        $data['is_logged_in'] = 0;
        $helper = $this->CommanHelper();

        $user = $helper->updateUser($this->identity()->getId(), $data);
        $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $auth->clearIdentity();
    }

    public function logoutAction() {
        $this->commonLogout();
        return new JsonModel(array('status'=>'successfully logged out'));
    }

}




// $message = new Message();
// $message->addTo($data['email'])
//         ->addFrom('akash.sinha@mantralabsglobal.com')
//         ->setSubject('OTP!')
//         ->setBody($data['random_number']);

// // Setup SMTP transport using LOGIN authentication
// $transport = new SmtpTransport();
// $options   = new SmtpOptions(array(
//     'name'              => 'localhost.localdomain',
//     'host'              => 'http://hungrymantra.localhost/',
//     'connection_class'  => 'login',
//     'connection_config' => array(
//         'username' => 'root',
//         'password' => '',
//     ),
// ));
// $transport->setOptions($options);
// $transport->send($message);







