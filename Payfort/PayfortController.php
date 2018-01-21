<?php

namespace App\Http\Controllers;
use Start;
use Start_Charge;
use Start_Token;
use Start_Refund;
use Illuminate\Http\Request;
use App\Http\Requests;



class PayfortController extends Controller
{
		public function config(){
		return array('email'=>'',//Insert your official email id.
                        'token'=>'',//Insert your access token.
                        'notify_url'=>'');
	}

	public function sample_products(){ //sample cart
        $result = [];
        $result['product'] = array();
       // $result['subscription'] = array();
		
        for($i=0;$i<3;$i++){  //$i<0 if no products
           $result['product'][$i]['product_id'] = $i;
            $result['product'][$i]['product_name'] = 'name '.$i;
            $result['product'][$i]['amount'] = $i+1;
            $result['product'][$i]['currency'] = 'usd';
        }

        return $result;
    }




	//to do a payment
public function payfort(Request $request){
												
	try {
		
		$input = $this->sample_products();
	        $config = $this->config();
			
		$open_api_key = "";//insert open api key.
		
		//Get the card details of the customer
		$card = array(
        "number" => "", 
        "exp_month" => ,
        "exp_year" => ,
        "cvc" => ""
    );
	
	$api_key_to_restore = Start::getApiKey();

        Start::setApiKey($open_api_key);

        $token = Start_Token::create($card);

        Start::setApiKey($api_key_to_restore);
		
		
		Start::setApiKey('');//insert api key.
		$amount=0;
		$desc=" ";
		for($i=0;$i<3;$i++){
			$amount=$amount+ $input['product'][$i]['amount'];
			$desc=$desc." ".$input['product'][$i]['product_name'].",";
			}



		$data = array(
            "amount" => $amount,
            "currency" => $input['product'][0]['currency'],
            "email" => "",//Insert email id.
            "card" => $token["id"],
			"ip"=>"",//Insert IP address.
            "description" => "$desc"
        );
		
		$charge = Start_Charge::create($data);

        //$this->assertEquals($charge["state"], "captured");

        return $charge;
} catch(Start_Error_Banking $e) {
  // Since it's a decline, Start_Error_Banking will be caught
  print('Status is:' . $e->getHttpStatus() . "\n");
  print('Code is:' . $e->getErrorCode() . "\n");
  print('Message is:' . $e->getMessage() . "\n");

} catch (Start_Error_Request $e) {
  // Invalid parameters were supplied to Start's API

} catch (Start_Error_Authentication $e) {
  // There's a problem with that API key you provided

} catch (Start_Error $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email

} catch (Exception $e) {
  // Something else happened, completely unrelated to Start
  
}
										  }
				//To refund the amount of a payment						  
	public function refund(Request $request){
												
	try {	
	$chargeid=""; // Get the charge id for which you want to refund
	Start::setApiKey('');//Insert api key.
	
	$refund = Start_Refund::create(array(
            "charge_id" => $chargeid
        ));
		return $refund;
	}catch(Start_Error_Banking $e) {
  // Since it's a decline, Start_Error_Banking will be caught
  print('Status is:' . $e->getHttpStatus() . "\n");
  print('Code is:' . $e->getErrorCode() . "\n");
  print('Message is:' . $e->getMessage() . "\n");

} catch (Start_Error_Request $e) {
  // Invalid parameters were supplied to Start's API

} catch (Start_Error_Authentication $e) {
  // There's a problem with that API key you provided

} catch (Start_Error $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email

} catch (Exception $e) {
  // Something else happened, completely unrelated to Start
  
}
	}
//To get the details of a payment
	public function details(Request $request){
												
	try {	
	$chargeid='';// Get the chargeid for which you want to get the details
	Start::setApiKey('');//Insert api key.
	
		$details=Start_Charge::get($chargeid);
		return $details;
		}catch(Start_Error_Banking $e) {
  // Since it's a decline, Start_Error_Banking will be caught
  print('Status is:' . $e->getHttpStatus() . "\n");
  print('Code is:' . $e->getErrorCode() . "\n");
  print('Message is:' . $e->getMessage() . "\n");

} catch (Start_Error_Request $e) {
  // Invalid parameters were supplied to Start's API

} catch (Start_Error_Authentication $e) {
  // There's a problem with that API key you provided

} catch (Start_Error $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email

} catch (Exception $e) {
  // Something else happened, completely unrelated to Start
  
}
	}
										  
}
?>