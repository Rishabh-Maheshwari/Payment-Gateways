<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;


class BitpayController extends Controller
{  
public function config(){
		return array('email'=>'',//Insert your official email id.
                        'token'=>'',//Insert access token.
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
            $result['product'][$i]['currency'] = 'usd';
        }

        for($i=0;$i<0;$i++){  //make $i<0 if no subscription
          $result['subscription'][$i]['product_id'] = $i;
            $result['subscription'][$i]['product_name'] = 'subscription_name '.$i;
            $result['subscription'][$i]['amount'] = $i+1;
            $result['subscription'][$i]['currency'] = 'USD';
            $result['subscription'][$i]['frequency'] = 'ww'; //week, day, year, month
            $result['subscription'][$i]['moment'] = '3'; //Day to on which amount will be deducted. It is not required in case of daily deduction.
            $result['subscription'][$i]['cycle'] = '5'; // 5 times
            $result['subscription'][$i]['start'] = '';//insert start date.
            $result['subscription'][$i]['end'] = '';//Insert end date.
			
			
        }
        return $result;
    }




	//to do a payment
public function bitpay(Request $request){
												
	
		
		$input = $this->sample_products();
	        $config = $this->config();
			return $this->invoice($input, $config);
}

public function token(Request $request)
{
$private = new \Bitpay\PrivateKey();
    $public = new \Bitpay\PublicKey();
    $sin = new \Bitpay\SinKey();
    try {
        // Generate Private Key values
        $private->generate();
        // Generate Public Key values
        $public->setPrivateKey($private);
        $public->generate();
        // Generate Sin Key values
        $sin->setPublicKey($public);
        $sin->generate();
    } catch (\Exception $e) {
        debuglog('[Error] In Bitpay plugin, generate_keys() function on line ' . $e->getLine() . ', with the error "' . $e->getMessage() . '" .');
        throw $e;
    }
	
// var_dump($private);
//echo"----------------";
$myfilepri = fopen("private.txt", "w") or die("Unable to open file!");
$txtpri=serialize($private);
fwrite($myfilepri, $txtpri);
fclose($myfilepri);

$myfilepub = fopen("public.txt", "w") or die("Unable to open file!");
$txtpub=serialize($public);
fwrite($myfilepub, $txtpub);
fclose($myfilepub);


 //var_dump($pri_encode);
 /*echo"----------------";
 $pri_decode=unserialize($pri_encode);
 var_dump($pri_decode);
 */
 //, $public, $sin);

 $client = new \Bitpay\Client\Client();

/**
 * The network is either livenet or testnet. You can also create your
 * own as long as it implements the NetworkInterface. In this example
 * we will use testnet
 */

 $network = new \Bitpay\Network\Testnet();

/**
 * The adapter is what will make the calls to BitPay and return the response
 * from BitPay. This can be updated or changed as long as it implements the
 * AdapterInterface
 */

 $adapter = new \Bitpay\Client\Adapter\CurlAdapter();

/**
 * Now all the objects are created and we can inject them into the client
 */
//$pri_dec=unserialize($_SESSION['pri_enc']);
//$pub_dec=unserialize($_SESSION['pub_enc']);


 $client->setPrivateKey($private);
$client->setPublicKey($public);
$client->setNetwork($network);
$client->setAdapter($adapter);

/**
 * Visit https://test.bitpay.com/api-tokens and create a new pairing code. Pairing
 * codes can only be used once and the generated code is valid for only 24 hours.
 */
 
$pairingCode = '';//Insert pairing code.

try {
    $token = $client->createToken(
        array(
            'pairingCode' => $pairingCode,
            'label'       => '',//Insert label for your transaction.
            'id'          => (string) $sin,
        )
    );
	//echo "privatekey =".$private;
	//echo "publickey =".$public;
	//echo "sinkey =".$sin;
} catch (\Exception $e) {

    /**
     * The code will throw an exception if anything goes wrong, if you did not
     * change the $pairingCode value or if you are trying to use a pairing
     * code that has already been used, you will get an exception. It was
     * decided that it makes more sense to allow your application to handle
     * this exception since each app is different and has different requirements.
     */
    echo "Pairing failed. Please check whether you're trying to pair a production pairing code on test.";
    $request  = $client->getRequest();
    $response = $client->getResponse();
    /**
     * You can use the entire request/response to help figure out what went
     * wrong, but for right now, we will just var_dump them.
     */
    echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
    echo (string) $response.PHP_EOL.PHP_EOL;
	
    /**
     * NOTE: The `(string)` is include so that the objects are converted to a
     *       user friendly string.
     */

    exit(1); // We do not want to continue if something went wrong
}

/**
 * You will need to persist the token somewhere, by the time you get to this
 * point your application has implemented an ORM such as Doctrine or you have
 * your own way to persist data. Such as using a framework or some other code
 * base such as Drupal.
 */
 $persistThisValue = $token->getToken();
 $myfiletoken = fopen("intoken.txt", "w") or die("Unable to open file!");
$txttoken = $persistThisValue;
fwrite($myfiletoken, $txttoken);
fclose($myfiletoken);
 echo 'Token obtained: '.$persistThisValue.PHP_EOL;

/**
 * Make sure you persist the token, you will need it for the next tutorial
 */

 }
public function invoice($input,$config)
{
	$txtpri=file_get_contents("private.txt");
	$pri_dec=unserialize($txtpri);
	$txtpub=file_get_contents("public.txt");
$pub_dec=unserialize($txtpub);
	
$client        = new \Bitpay\Client\Client();
$network       = new \Bitpay\Network\Testnet();
$adapter       = new \Bitpay\Client\Adapter\CurlAdapter();
$client->setPrivateKey($pri_dec);
$client->setPublicKey($pub_dec);
$client->setNetwork($network);
$client->setAdapter($adapter);
// ---------------------------

/**
 * The last object that must be injected is the token object.
 */
$token = new \Bitpay\Token();
$txttoken=file_get_contents("intoken.txt");
$token->setToken($txttoken);
//'); // UPDATE THIS VALUE

/**
 * Token object is injected into the client
 */
$client->setToken($token);

/**
 * This is where we will start to create an Invoice object, make sure to check
 * the InvoiceInterface for methods that you can use.
 */
$invoice = new \Bitpay\Invoice();

$buyer = new \Bitpay\Buyer();
//get the buyer email id in $buyerid and save this id in database for future recurring payments
$buyer
    ->setEmail('');//Insert buyer id.
	//->setEmail($buyerid);

// Add the buyers info to invoice
$invoice->setBuyer($buyer);

/**
 * Item is used to keep track of a few things
 */
$item = new \Bitpay\Item();
$amount=0;
$desc=" ";
if(sizeof($input['product'])!=0){
for($i=0;$i<3;$i++){
$amount=$amount+$input['product'][$i]['amount'];
$desc=$desc." ".$input['product'][$i]['product_id']." - ".$input['product'][$i]['product_name'].",";
}
}
if(sizeof($input['subscription'])==1){
$desc=$desc." and 1 subscription: ".$input['subscription'][0]['product_id']." - ".$input['subscription'][0]['product_name'];
            $amount=$amount+$input['subscription'][0]['amount'];
$sub_amount=$input['subscription'][0]['amount'];
}//update the subscription amount in the database in the recuuring customer profile.
$item
    ->setCode('skuNumber')
    ->setDescription($desc)
    ->setPrice($amount);
$invoice->setItem($item);

/**
 * BitPay supports multiple different currencies. Most shopping cart applications
 * and applications in general have defined set of currencies that can be used.
 * Setting this to one of the supported currencies will create an invoice using
 * the exchange rate for that currency.
 *
 * @see https://test.bitpay.com/bitcoin-exchange-rates for supported currencies
 */
$invoice->setCurrency(new \Bitpay\Currency($input['product'][0]['currency']));

// Configure the rest of the invoice
//generate an invoice id for your system and place it in set orderid
$invoice
    ->setOrderId('OrderIdFromYourSystem')//place your invoice id
    // You will receive IPN's at this URL, should be HTTPS for security purposes!
    ->setNotificationUrl('http://13.126.149.237/api/v1/auth/user');


/**
 * Updates invoice with new information such as the invoice id and the URL where
 * a customer can view the invoice.
 */
try {
    $client->createInvoice($invoice);
} catch (\Exception $e) {
    $request  = $client->getRequest();
    $response = $client->getResponse();
    echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
    echo (string) $response.PHP_EOL.PHP_EOL;
    exit(1); // We do not want to continue if something went wrong
}
$url=$invoice->getUrl();
//header('Location: '.$url);
echo 'Invoice "'.$invoice->getId().'" created, see '.$invoice->getUrl().PHP_EOL;

}
public function status(Request $request)
{


$raw_post_data = file_get_contents('php://input');
$myfile = fopen("status.txt", "a");
if (false === $raw_post_data) {
    fwrite($myfile, $date . " : Error. Could not read from the php://input stream or invalid Bitpay IPN received.\n");
    fclose($myfile);
    throw new \Exception('Could not read from the php://input stream or invalid Bitpay IPN received.');
}
else{
$ipn = json_decode($raw_post_data);
 fwrite($myfile, $ipn);
fclose($myfile);
}
} 

public function trigger(Request $request)
{
	$date=date("Y/m/d");
	// check the subscription date which matches with current date
	//save the date $sub_date and its information in $sub_"attribute" variable
	$url="http://recurring/".$sub_id;
	mail($sub_mail,"Your subscription payment time",$url);
}
public function recurring($id)
{
//get the details of the subscription from the database using $id
$result = [];
        $result['product'] = array();
        $result['subscription'] = array();
		//store all the details of subscription retrieved in $result['subscription']['attributes']
		//please use the same name for attributes as used in sample_products function at line 17.
	        $config = $this->config();
			return $this->invoice($result, $config);
 	
}
}