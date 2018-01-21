<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;


use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

use PayPal\Api\Plan;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Currency;
use PayPal\Api\ChargeModel;

use PayPal\Api\PatchRequest;
use PayPal\Api\Patch;
use PayPal\Common\PayPalModel;

use PayPal\Api\Agreement;
use PayPal\Api\ShippingAddress;


class PaypalController extends Controller
{
    
	public function config(){
		return array('email'=>'',//Insert Your official email address.
                        'token'=>'',//Insert Your Token provided by Paypal.
                        'action_url'=>'https://www.sandbox.paypal.com/cgi-bin/webscr',
                        'return_url'=>'/paypal_cancel',
                       'notify_url'=>'');
	}

	public function sample_products(){ //sample cart
        $result = [];
        $result['product'] = array();
        $result['subscription'] = array();
		
        for($i=0;$i<3;$i++){  //$i<0 if no products
           $result['product'][$i]['product_id'] = $i;
            $result['product'][$i]['product_name'] = 'name '.$i;
            $result['product'][$i]['amount'] = $i+1;
            $result['product'][$i]['currency'] = 'USD';//Change the currency as per your business model.
        }

        for($i=0;$i<0;$i++){  //make $i<0 if no subscription
          $result['subscription'][$i]['product_id'] = $i;
            $result['subscription'][$i]['product_name'] = 'subscription_name '.$i;
            $result['subscription'][$i]['amount'] = $i+1;
            $result['subscription'][$i]['currency'] = 'USD';//Change the currency as per your business model.
            $result['subscription'][$i]['frequency'] = 'WEEK'; //week, day, year, month
            $result['subscription'][$i]['frequency_inteval'] = '3'; // how frequently
            $result['subscription'][$i]['cycle'] = '5'; // 5 times
        }
        return $result;
    }

    public function paypal(Request $request){
    	
    	$input = $this->sample_products();
        $config = $this->config();
		
		if(sizeof($input['subscription'])==1){
        	$this->paymentWithSubscription($input, $config);
		}else{
		    $this->paymentWithoutSubscription($input, $config);
		}

    }

    public function paymentwithSubscription($input, $config){
    	$plan = new Plan();
    	$plan->setName($input['subscription'][0]['product_name'])
	    ->setDescription('Subscription Description')
	    ->setType('fixed');

	    $paymentDefinition = new PaymentDefinition();

	    $paymentDefinition->setName('Regular Payments')
		    ->setType('REGULAR')
		    ->setFrequency($input['subscription'][0]['frequency'])
		    ->setFrequencyInterval($input['subscription'][0]['frequency_inteval'] )
		    ->setCycles($input['subscription'][0]['cycle'])
		    ->setAmount(new Currency(array('value' =>  $input['subscription'][0]['amount'], 'currency' => $input['subscription'][0]['currency'])));

		$chargeModel = new ChargeModel();
		$chargeModel->setType('SHIPPING')
		    ->setAmount(new Currency(array('value' => 0, 'currency' => $input['subscription'][0]['currency'])));
		    $setup_fee=0;
		    for($i=0;$i<sizeof($input['product']);$i++){
		        $setup_fee=$setup_fee+$input['product'][$i]['amount'];
		    }

		$paymentDefinition->setChargeModels(array($chargeModel));


		$merchantPreferences = new MerchantPreferences();
		$baseUrl = $this->getBaseUrl();

		$merchantPreferences->setReturnUrl("$baseUrl/paypal_success_subscription?success=true")
		    ->setCancelUrl($baseUrl.$config['return_url'])
		    ->setAutoBillAmount("yes")
		    ->setInitialFailAmountAction("CONTINUE")
		    ->setMaxFailAttempts("2")
		    ->setSetupFee(new Currency(array('value' => $setup_fee, 'currency' => $input['subscription'][0]['currency'])));


		$plan->setPaymentDefinitions(array($paymentDefinition));
		$plan->setMerchantPreferences($merchantPreferences);

		$apiContext = $this->getApiContext();

		// ### Create Plan
		try {
		    $createdPlan = $plan->create($apiContext);

		    try {
			    $patch = new Patch();

			    $value = new PayPalModel('{
				       "state":"ACTIVE"
				     }');

			    $patch->setOp('replace')
			        ->setPath('/')
			        ->setValue($value);
			    $patchRequest = new PatchRequest();
			    $patchRequest->addPatch($patch);

			    $createdPlan->update($patchRequest, $apiContext);

			    $output = Plan::get($createdPlan->getId(), $apiContext); //activate the plan

			    $agreement = new Agreement();

				$desc="Decription of Subscription"."\n";
				for($i=0;$i<sizeof($input['product']);$i++)
				{
				    $desc=$desc."\n".$input['product'][$i]['product_name'].": ".$input['product'][$i]['amount'].$input['product'][$i]['currency'];
				}
				$time = gmdate("Y-m-d\TH:i:s\Z", time()+180);

				$agreement->setName('Basic Agreement')
				    ->setDescription($desc)
				    ->setStartDate($time);


				// Please note that the plan Id should be only set in this case.
				$plan = new Plan();
				$plan->setId($output->getId());
				$agreement->setPlan($plan);

				// Add Payer
				$payer = new Payer();
				$payer->setPaymentMethod('paypal');
				$agreement->setPayer($payer);

				// Add Shipping Address
				$shippingAddress = new ShippingAddress();
				//Set Shipping addess if required
				$shippingAddress->setLine1('111 First Street')
				    ->setCity('Saratoga')
				    ->setState('CA')
				    ->setPostalCode('95070')
				    ->setCountryCode('US');
				$agreement->setShippingAddress($shippingAddress);

				try {
				    // Please note that as the agreement has not yet activated, we wont be receiving the ID just yet.
				    $agreement = $agreement->create($apiContext);

				    $approvalUrl = $agreement->getApprovalLink();
					\Redirect::to($approvalUrl)->send();

				} catch (Exception $ex) {

				   die($ex);
				   exit(1);
				}

			} catch (Exception $ex) {
			  ResultPrinter::printError("Updated the Plan to Active State", "Plan", null, $patchRequest, $ex);
			    exit(1);
			}
		} catch (Exception $ex) {
		    die($ex);
		    ResultPrinter::printError("Created Plan", "Plan", null, $request, $ex);
		    exit(1);
		}

    }

    public function paymentWithoutSubscription($input, $config){
    	
    	$payer = new Payer();
        $payer->setPaymentMethod("paypal");  //setup payer method

        $totalamount=0;
        $items = array();
		for($i=0;$i<sizeof($input['product']);$i++){
			$item[$i] = new Item();

			$item[$i]->setName($input['product'][$i]['product_name'])  //create paypal product 
			    ->setCurrency($input['product'][$i]['currency'])
			    ->setQuantity(1)
			    ->setPrice($input['product'][$i]['amount']);
			array_push($items,$item[$i]);
			$totalamount=$totalamount+$input['product'][$i]['amount']; //total amount
		}

		$itemList = new ItemList();
		$itemList->setItems($items);          //set item list

		$amount = new Amount();        //set amount and currency
		$amount->setCurrency($input['product'][0]['currency'])->setTotal($totalamount); 

		$transaction = new Transaction();    //create a transaction
		$transaction->setAmount($amount)
					->setItemList($itemList)
				    ->setDescription("Payment Description")
				    ->setInvoiceNumber(uniqid());

		$baseUrl = $this->getBaseUrl();

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl("$baseUrl/paypal_success_product?success=true")
		    ->setCancelUrl("");//insert your cancel url


		$payment = new Payment();    //create payment resource
		$payment->setIntent("sale")
				->setPayer($payer)
			    ->setRedirectUrls($redirectUrls)
			    ->setTransactions(array($transaction));


		try {
			$apiContext = $this->getApiContext();
		    $payment->create($apiContext);
		} catch (Exception $ex) {
		  die($ex);
		}

		$approvalUrl = $payment->getApprovalLink();
		\Redirect::to($approvalUrl)->send();

    }

    public function paypal_success_product(Request $request){
    	$input = $request->all();
    	if (isset($input['success']) && $input['success'] == 'true') {

    		// Get the payment Object by passing paymentId
		    // payment id was previously stored in session in
		    // CreatePaymentUsingPayPal.php
		    $apiContext = $this->getApiContext();
		    $paymentId = $input['paymentId'];
		    $payment = Payment::get($paymentId, $apiContext);

		    // ### Payment Execute
		    // PaymentExecution object includes information necessary
		    // to execute a PayPal account payment.
		    // The payer_id is added to the request query parameters
		    // when the user is redirected from paypal back to your site
		    $execution = new PaymentExecution();
		    $execution->setPayerId($input['PayerID']);

		    try {
		        // Execute the payment
		        $result = $payment->execute($execution, $apiContext);

		        ResultPrinter::printResult("Executed Payment", "Payment", $payment->getId(), $execution, $result);

		        try {
		            $payment = Payment::get($paymentId, $apiContext);
		        } catch (Exception $ex) {
		          ResultPrinter::printError("Get Payment", "Payment", null, null, $ex);
		            exit(1);
		        }
		    } catch (Exception $ex) {
		        ResultPrinter::printError("Executed Payment", "Payment", null, null, $ex);
		        exit(1);
		    }


		} else {
		    ResultPrinter::printResult("User Cancelled the Approval", null);
		    exit;
		}

    }

    public function paypal_success_subscription(Request $request){

    	$input = $request->all();
    	if (isset($input['success']) && $input['success'] == 'true') {
		    $token = $input['token'];
		    $apiContext = $this->getApiContext();
		    $agreement = new \PayPal\Api\Agreement();
		    try {
		        // ## Execute Agreement
		        // Execute the agreement by passing in the token
		        $agreement->execute($token, $apiContext);
		    } catch (Exception $ex) {
		    ResultPrinter::printError("Executed an Agreement", "Agreement", $agreement->getId(), $input['token'], $ex);
		        exit(1);
		    }

		   ResultPrinter::printResult("Executed an Agreement", "Agreement", $agreement->getId(), $input['token'], $agreement);

		    // ## Get Agreement
		    // Make a get call to retrieve the executed agreement details
		    try {
		        $agreement = \PayPal\Api\Agreement::get($agreement->getId(), $apiContext);
		        ResultPrinter::printResult("Get Agreement", "Agreement", $agreement->getId(), null, $agreement);
		    } catch (Exception $ex) {
		        ResultPrinter::printError("Get Agreement", "Agreement", null, null, $ex);
		        exit(1);
		    }
		} else {
		    ResultPrinter::printResult("User Cancelled the Approval", null);
		}
    }

    public function paypal_subscription_cancel($transaction_id){
    	$access_token = $this->get_access_token();
    	
    	$header = array();
	    $header[] = 'Content-type: application/json';
	    $header[] = 'Authorization: Bearer '.$access_token;
	    $url = 'https://api.sandbox.paypal.com/v1/payments/billing-agreements/'.$transaction_id.'/cancel';
	    $data ='{
	            "note": "Cancel The Subscription."
	        }';

	    //open connection
	    $ch = curl_init();

	    //set connection properties
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	    //curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);


	    //execute post
	    $result = curl_exec($ch);

	    $err = curl_errno($ch);
	    $errmsg = curl_error($ch) ;
	    $info = curl_getinfo($ch);
	    curl_close($ch);
	    if( $err ){
	    	echo 'error';
	    }if( $errmsg ){
	    	echo '<h3>Error</h3>'.$errmsg;
	    }
	    else{
	    	echo $result;
	    }
		curl_close($ch);
    }

    public function paypal_refund($resource_id){
    	$access_token = $this->get_access_token();
    	
    	$header = array();
	    $header[] = 'Content-type: application/json';
	    $header[] = 'Authorization: Bearer '.$access_token;
	    $url = 'https://api.sandbox.paypal.com/v1/payments/sale/'.$resource_id.'/refund';
	    $data ='{
	            "note": "Cancel The Subscription."
	        }';

	    //open connection
	    $ch = curl_init();

	    //set connection properties
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	    //curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);


	    //execute post
	    $result = curl_exec($ch);

	    $err = curl_errno($ch);
	    $errmsg = curl_error($ch) ;
	    $info = curl_getinfo($ch);
	    curl_close($ch);
	    if( $err ){
	    	echo 'error';
	    }if( $errmsg ){
	    	echo '<h3>Error</h3>'.$errmsg;
	    }
	    else{
	    	echo $result;
	    }
		curl_close($ch);
    }

    public function get_access_token(){
    	$ch = curl_init();
		
		$clientId = ""; //see account details
		$secret = ""; //see account details

		curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$secret);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

		$result = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($result);
		return $json->access_token;
    }


    public function getBaseUrl(){
	    
	    $protocol = 'http';
	    if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
	        $protocol .= 's';
	    }
	    $host = $_SERVER['HTTP_HOST'];
	    $request = $_SERVER['PHP_SELF'];
	    return dirname($protocol . '://' . $host . $request);
	}


	public function getApiContext(){
		$clientId = 'insert your client id';
		$clientSecret = '';//insert your client secret
		
		$apiContext = new ApiContext(
	        new OAuthTokenCredential(
	            $clientId,
	            $clientSecret
	        )
	    );

	    $apiContext->setConfig(
	        array(
	            'mode' => 'sandbox',
	            'log.LogEnabled' => false,
	            'log.FileName' => '',
	            'log.LogLevel' => 'DEBUG', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
	            'validation.level' => 'log',
	            'cache.enabled' => false,
	            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
	            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
	        )
	    );

    	return $apiContext;
	}
	public function paypal_update_recurring_details($start_date)
	{  $end_date=date("Y-m-d");
		
//Geting  new access token
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.sandbox.paypal.com/v1/oauth2/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "grant_type=client_credentials",
  CURLOPT_HTTPHEADER => array(
    "authorization: _insert_your_authorizarion_basic_id",
    "cache-control: no-cache",
    "content-type: application/x-www-form-urlencoded",
    "postman-token: 33973224-97bf-44b7-262e-8351666fee22"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
	$json = json_decode($response, true);
  $token=$json['access_token'];

  }
//Getting transaction id for a recurring payment


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.sandbox.paypal.com/v1/payments/billing-agreements/.'insert id here'./transactions?start_date=".$start_date."&end_date=".$end_date,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer ".$token,
    "cache-control: no-cache",
    "postman-token: aa1895ee-b0c4-7f2c-089b-510d45764579"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

	}
    
}

class ResultPrinter{

	    private static $printResultCounter = 0;

	    /**
	     * Prints HTML Output to web page.
	     *
	     * @param string     $title
	     * @param string    $objectName
	     * @param string    $objectId
	     * @param mixed     $request
	     * @param mixed     $response
	     * @param string $errorMessage
	     */
	    public static function printOutput($title, $objectName, $objectId = null, $request = null, $response = null, $errorMessage = null)
	    {
	        if (PHP_SAPI == 'cli') {
	            self::$printResultCounter++;
	            printf("\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n");
	            printf("(%d) %s", self::$printResultCounter, strtoupper($title));
	            printf("\n-------------------------------------------------------------\n\n");
	            if ($objectId) {
	                printf("Object with ID: %s \n", $objectId);
	            }
	            printf("-------------------------------------------------------------\n");
	            printf("\tREQUEST:\n");
	            self::printConsoleObject($request);
	            printf("\n\n\tRESPONSE:\n");
	            self::printConsoleObject($response, $errorMessage);
	            printf("\n-------------------------------------------------------------\n\n");
	        } else {

	            if (self::$printResultCounter == 0) {
	               
	            }
	            self::$printResultCounter++;
	            echo '
	        <div class="panel panel-default">
	            <div class="panel-heading '. ($errorMessage ? 'error' : '') .'" role="tab" id="heading-'.self::$printResultCounter.'">
	                <h4 class="panel-title">
	                    <a data-toggle="collapse" data-parent="#accordion" href="#step-'. self::$printResultCounter .'" aria-expanded="false" aria-controls="step-'.self::$printResultCounter.'">
	            '. self::$printResultCounter .'. '. $title . ($errorMessage ? ' (Failed)' : '') . '</a>
	                </h4>
	            </div>
	            <div id="step-'.self::$printResultCounter.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-'. self::$printResultCounter . '">
	                <div class="panel-body">
	            ';

	            if ($objectId) {
	                echo "<div>" . ($objectName ? $objectName : "Object") . " with ID: $objectId </div>";
	            }

	            echo '<div class="row hidden-xs hidden-sm hidden-md"><div class="col-md-6"><h4>Request Object</h4>';
	            self::printObject($request);
	            echo '</div><div class="col-md-6"><h4 class="'. ($errorMessage ? 'error' : '') .'">Response Object</h4>';
	            self::printObject($response, $errorMessage);
	            echo '</div></div>';

	            echo '<div class="hidden-lg"><ul class="nav nav-tabs" role="tablist">
	                        <li role="presentation" ><a href="#step-'.self::$printResultCounter .'-request" role="tab" data-toggle="tab">Request</a></li>
	                        <li role="presentation" class="active"><a href="#step-'.self::$printResultCounter .'-response" role="tab" data-toggle="tab">Response</a></li>
	                    </ul>
	                    <div class="tab-content">
	                        <div role="tabpanel" class="tab-pane" id="step-'.self::$printResultCounter .'-request"><h4>Request Object</h4>';
	            self::printObject($request) ;
	            echo '</div><div role="tabpanel" class="tab-pane active" id="step-'.self::$printResultCounter .'-response"><h4>Response Object</h4>';
	            self::printObject($response, $errorMessage);
	            echo '</div></div></div></div>
	            </div>
	        </div>';
	        }
	        flush();
	    }

	    /**
	     * Prints success response HTML Output to web page.
	     *
	     * @param string     $title
	     * @param string    $objectName
	     * @param string    $objectId
	     * @param mixed     $request
	     * @param mixed     $response
	     */
	    public static function printResult($title, $objectName, $objectId = null, $request = null, $response = null)
	    {
	        self::printOutput($title, $objectName, $objectId, $request, $response, false);
	    }

	    /**
	     * Prints Error
	     *
	     * @param      $title
	     * @param      $objectName
	     * @param null $objectId
	     * @param null $request
	     * @param \Exception $exception
	     */
	    public static function printError($title, $objectName, $objectId = null, $request = null, $exception = null)
	    {
	        $data = null;
	        if ($exception instanceof \PayPal\Exception\PayPalConnectionException) {
	            $data = $exception->getData();
	        }
	        self::printOutput($title, $objectName, $objectId, $request, $data, $exception->getMessage());
	    }

	    protected static function printConsoleObject($object, $error = null)
	    {
	        if ($error) {
	            echo 'ERROR:'. $error;
	        }
	        if ($object) {
	            if (is_a($object, 'PayPal\Common\PayPalModel')) {
	                /** @var $object \PayPal\Common\PayPalModel */
	                echo $object->toJSON(128);
	            } elseif (is_string($object) && \PayPal\Validation\JsonValidator::validate($object, true)) {
	                echo str_replace('\\/', '/', json_encode(json_decode($object), 128));
	            } elseif (is_string($object)) {
	                echo $object;
	            } else {
	                print_r($object);
	            }
	        } else {
	            echo "No Data";
	        }
	    }

	    protected static function printObject($object, $error = null)
	    {
	        if ($error) {
	            echo '<p class="error"><i class="fa fa-exclamation-triangle"></i> '.
	             $error.
	            '</p>';
	        }
	        if ($object) {
	            if (is_a($object, 'PayPal\Common\PayPalModel')) {
	                /** @var $object \PayPal\Common\PayPalModel */
	                echo '<pre class="prettyprint '. ($error ? 'error' : '') .'">' . $object->toJSON(128) . "</pre>";
	            } elseif (is_string($object) && \PayPal\Validation\JsonValidator::validate($object, true)) {
	                echo '<pre class="prettyprint '. ($error ? 'error' : '') .'">'. str_replace('\\/', '/', json_encode(json_decode($object), 128)) . "</pre>";
	            } elseif (is_string($object)) {
	                echo '<pre class="prettyprint '. ($error ? 'error' : '') .'">' . $object . '</pre>';
	            } else {
	                echo "<pre>";
	                print_r($object);
	                echo "</pre>";
	            }
	        } else {
	            echo "<span>No Data</span>";
	        }
	    }
	}
