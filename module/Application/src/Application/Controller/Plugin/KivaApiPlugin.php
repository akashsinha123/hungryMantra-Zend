<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class KivaApiPlugin extends AbstractPlugin{
  public $em;
  
  public function getEntityManager(){
    if (null === $this->em) {
      $this->em =  $this->getController()->getServiceLocator()->get('doctrine.entitymanager.orm_default'); 
    } 
    return $this->em;
  } 

  public function getUserDetails () {
  	$config = $this->getController()->getServiceLocator()->get('Config');
    $kivaAccessArray = $this->setKivaConfig($config);
    $resource_url = $config['resource_url'].'my/balance.json';

    $res_req = OAuthRequest::from_consumer_and_token($kivaAccessArray['consumer'], $kivaAccessArray['access_token'], "GET", $resource_url);
    $res_req->sign_request($kivaAccessArray['sig_method'], $kivaAccessArray['consumer'], $kivaAccessArray['access_token']);

    if($config['server'] == 'dev') {
        $credentials = $config['kiva_credentials'];
        $opts = array('http' =>
            array(
                'ignore_errors'  => true,
                'method'  => 'GET',
                'header'  => "Authorization: Basic " . base64_encode($credentials)
            )
        );
        $context  = stream_context_create($opts);
        $res_response = file_get_contents($res_req, false, $context);
    } else {
        $resource_response = file_get_contents($res_req);
    }

    $res_obj = json_decode($res_response);
  	return $res_obj;
  }

  public function fundLoan($data) {
    $config = $this->getController()->getServiceLocator()->get('Config');
  	$kivaAccessArray = $this->setKivaConfig($config);
    // $time_start = microtime(true);
    // echo "start time: ".$time_start."\n";
    // $time_start = microtime(true);
  	$basket_obj = $this->createBasket($kivaAccessArray, $config);
    if(!empty($basket_obj->code)) {
      if($basket_obj->code == 'org.kiva.InvalidCredentials') {
        return "Invalid credentials";
      }
    }
    if(empty($basket_obj))
      return "Basket is empty";
    // echo "step1: ".(microtime(true) - $time_start)."\n";
    // $time_start = microtime(true);
  	$basket_obj = $this->addLoanBasket($kivaAccessArray, $basket_obj, $config, $data);
    // echo "step2: ".(microtime(true) - $time_start)."\n";
    if(empty($basket_obj))
      return "amount is exceed";
  	$basket_obj = $this->checkoutLoan($kivaAccessArray, $basket_obj, $config);
    // echo "step3: ".(microtime(true) - $time_start)."\n";

	  return $basket_obj;
  }

  private function checkoutLoan($kivaAccessArray, $basket_obj, $config) {
    $basket_id = $basket_obj->basket->id;
  	$basket_url = $config['resource_url'].'my/basket/'. $basket_id.'/checkout';
  	$basket_req = OAuthRequest::from_consumer_and_token($kivaAccessArray['consumer'], $kivaAccessArray['access_token'], "POST", $basket_url.'.json');
    $basket_req->sign_request($kivaAccessArray['sig_method'], $kivaAccessArray['consumer'], $kivaAccessArray['access_token']);

    if($config['server'] == 'dev') {
      $credentials = $config['kiva_credentials'];
      $opts = array('http' =>
          array(
              'ignore_errors'  => true,
              'method'  => 'POST',
              'header'  => "Authorization: Basic " . base64_encode($credentials)
          )
      );
    } else {
      $basket_arr = preg_split('/\?/',$basket_req);
      $opts = array('http' =>
          array(
              'ignore_errors'  => true,
              'method'  => 'POST',
              'header'  => "Content-type: application/json\r\n".
                           $basket_req->to_header()
          )
      );
      $basket_req = $basket_arr[0];
    }
    $context  = stream_context_create($opts);
    $basket_response = file_get_contents($basket_req, false, $context);
    $basket_obj = json_decode($basket_response);

  	return $basket_obj;
  }

  private function addLoanBasket($kivaAccessArray, $basket_obj, $config, $data) {
  	$basket_id = $basket_obj->basket->id;
  	$basket_url = $config['resource_url'].'my/basket/'.$basket_id;
  	$basket_req = OAuthRequest::from_consumer_and_token($kivaAccessArray['consumer'], $kivaAccessArray['access_token'], "PUT", $basket_url.'.json');
  	$basket_req->sign_request($kivaAccessArray['sig_method'], $kivaAccessArray['consumer'], $kivaAccessArray['access_token']);

    foreach ($data as $key => $loan) {
      $loan = get_object_vars($loan);
      $basket_array['basket']['loans'][$loan['kiva_loan_id']]['amount'] =  $loan['amount'];
    }
  	$basket = json_encode($basket_array);
  	if($config['server'] == 'dev') {
      $credentials = $config['kiva_credentials'];
      $opts = array('http' =>
        array(
            'ignore_errors'  => true,
            'method'  => 'PUT',
            'header'  => "Content-type: application/json\r\n".
                "Authorization: Basic " . base64_encode($credentials),
            'content' => $basket
        )
      );
    } else {
      $basket_arr = preg_split('/\?/',$basket_req);
      $opts = array('http' =>
          array(
              'ignore_errors'  => true,
              'method'  => 'PUT',
              'header'  => "Content-type: application/json\r\n".
                           $basket_req->to_header(),
              'content' => $basket
          )
      );
      $basket_req = $basket_arr[0];
    }
    $context  = stream_context_create($opts);
    $basket_response = file_get_contents($basket_req, false, $context);
    $basket_obj = json_decode($basket_response);

  	return $basket_obj;
  }

  private function createBasket($kivaAccessArray, $config) {
    $basket_url = $config['resource_url'].'my/basket';
    $credentials = $config['kiva_credentials'];
  	$basket_req = OAuthRequest::from_consumer_and_token($kivaAccessArray['consumer'], $kivaAccessArray['access_token'], "POST", $basket_url.'.json');
 
  	$basket_req->sign_request($kivaAccessArray['sig_method'], $kivaAccessArray['consumer'], $kivaAccessArray['access_token']);
  	
    if($config['server'] == 'dev') {
      $credentials = $config['kiva_credentials'];
      $opts = array('http' =>
          array(
              'ignore_errors'  => true,
              'method'  => 'POST',
              'header'  => "Authorization: Basic " . base64_encode($credentials)
          )
      );
    } else {
      $basket_arr = preg_split('/\?/',$basket_req);
      $opts = array('http' =>
          array(
              'ignore_errors'  => true,
              'method'  => 'POST',
              'header'  => "Content-type: application/json\r\n".
                           $basket_req->to_header()
          )
      );
      $basket_req = $basket_arr[0];
    }
    $context  = stream_context_create($opts);
  	$basket_response = file_get_contents($basket_req, false, $context);
    $basket_obj = json_decode($basket_response);

  	return $basket_obj;
  }

  public function setKivaConfig($config) {
  	$key = $config['kiva_key']; 
    $secret = $config['kiva_secret'];
    $callback_url = $config['callback_url'];
    $resource_url = $config['resource_url'];
    $request_token_url = $config['request_token_url'];
    $access_token_url =  $config['access_token_url'];

    $access_token = new \Application\Controller\Plugin\KivaOAuthConsumer($config['kiva_access_token'], $config['kiva_access_secret'], 																	 $config['kiva_callback_url']);
    $consumer = new \Application\Controller\Plugin\KivaOAuthConsumer($key, $secret, NULL);
    $sig_method = new \Application\Controller\Plugin\OAuthSignatureMethod_HMAC_SHA1();

    return array('access_token' => $access_token, 'consumer' => $consumer, 'sig_method' => $sig_method);

  }

   function getLoanLenderPayment($loan_ids) {
      $loans_array = array_chunk($loan_ids, 100);
      foreach($loans_array as $loans) {
        $kiva_loan_ids = array();
        foreach ($loans as $key => $loan) {
          $kiva_loan_ids[$loan['kiva_loan_id']] =  $loan;
        }
        $loan_ids = implode(",", array_keys($kiva_loan_ids));
        $this->getLenderPaymentApiDetails($loan_ids, $kiva_loan_ids);
    }
    
  }

  function getLenderPaymentApiDetails($ids, $kiva_loan_ids) {
    $config = $this->getController()->getServiceLocator()->get('Config');
    $kivaAccessArray = $this->setKivaConfig($config);
    $resource_url = $config['resource_url']."my/loans/".$ids."/balances.json";
    $credentials = $config['kiva_credentials'];
    $basket_req = OAuthRequest::from_consumer_and_token($kivaAccessArray['consumer'], $kivaAccessArray['access_token'], "GET", $resource_url.'.json');
 
    $basket_req->sign_request($kivaAccessArray['sig_method'], $kivaAccessArray['consumer'], $kivaAccessArray['access_token']);
    
    $opts = array('http' =>
      array(
          'ignore_errors'  => true,
          'method'  => 'GET',
          'header'  => "Authorization: Basic " . base64_encode($credentials)
      )
    );
    $context  = stream_context_create($opts);
    $loan_balances = file_get_contents($basket_req, false, $context);
    $loan_balances_obj = json_decode($loan_balances);
    foreach($loan_balances_obj->balances as $balance) {
      $loan = $kiva_loan_ids[$balance->id];
      $loan = $this->getEntityManager()->getRepository('Loan\Entity\Loan')->find($loan['id']);
      $loan->setTotalAMountDenotedApi($balance->total_amount_purchased);
      $this->getEntityManager()->persist($loan);
      $this->getEntityManager()->flush();
    }

    return $loan_balances_obj;
  }

  function updateLoanRepayment($loan_ids) {
    $kivaApiPlugin = $this->getController()->KivaPlugin();
    $kivaAccessArray = $kivaApiPlugin->divideArrayChunks($loan_ids);
  }

}


?>