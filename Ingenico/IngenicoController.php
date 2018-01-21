<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

class IngenicoController extends Controller
{
    
	public function config(){
		return array('email'=>'',//Insert Your official email address.
                        'token'=>'',//Insert Your Token provided by Paypal.
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
            $result['product'][$i]['currency'] = 'USD';
        }

        for($i=0;$i<0;$i++){  //make $i<0 if no subscription
          $result['subscription'][$i]['product_id'] = $i;
            $result['subscription'][$i]['product_name'] = 'subscription_name '.$i;
            $result['subscription'][$i]['amount'] = $i+1;
            $result['subscription'][$i]['currency'] = 'USD';
            $result['subscription'][$i]['frequency'] = 'ww'; //week, day, year, month
            $result['subscription'][$i]['moment'] = '3'; //Day to on which amount will be deducted. It is not required in case of daily deduction.
            $result['subscription'][$i]['cycle'] = '5'; // 5 times
            $result['subscription'][$i]['start'] = '';//Starting date of subscription.
            $result['subscription'][$i]['end'] = '';//Starting date of subscription.
			
			
        }
        return $result;
    }


	public function ingenico(Request $request){
	    	
	    	$input = $this->sample_products();
	        $config = $this->config();
			
			if(sizeof($input['subscription'])==1){
	        	return $this->paymentWithSubscription($input, $config);
			}else{
			    return $this->paymentWithoutSubscription($input, $config);
			}		
	}

	public function paymentwithSubscription($input, $config){
	 	$SHAIN = ''; //you may change it from dashboard
	 	$PSPID = '';

	 	$client_name = '';//Client name.

	 	$ACCEPTURL = $this->getBaseUrl().'/payment_response';
	 	$CANCELURL = $this->getBaseUrl().'/payment_response';
	 	$DECLINEURL = $this->getBaseUrl().'/payment_response';
	 	$EXCEPTIONURL = $this->getBaseUrl().'/payment_response';
	 	$BACKURL = $this->getBaseUrl().'/payment_response';
	 	$HOMEURL = $this->getBaseUrl().'/payment_response';
	 	$CATALOGURL = $this->getBaseUrl().'/payment_response';


		$orderno=50;//Retrieve the last orderno from database
		$orderno++;//Update the new orderno
		for($i=0;$i<sizeof($input['product']);$i++){
			$amount=$input['product'][$i]['amount'];//Total amount of all products in the cart: 
		}
		$amount=$amount*100;//Multiplying amount with 100 to pass as parameter
		$currency=$input['subscription'][0]['currency'];
		$lang="en_US";
		$subid=$input['subscription'][0]['product_id'];
		$sub_amount=$input['subscription'][0]['amount'];// Amount to be paid in each bill cycle
		$sub_amount=$sub_amount*100;//Multiplying amount with 100 to pass as parameter
		$sub_title=$input['subscription'][0]['product_name'];//Get the the title of new subscription
		$sub_order_id=11;//place the sub_order_id;
		$sub_period_no=$input['subscription'][0]['cycle'];//No. of bill cycle
		$sub_period_moment=$input['subscription'][0]['moment'];//Day to on which amount will be deducted. It is not required in case of daily deduction.
		$pro_desc=" ";
		for($i=0;$i<sizeof($input['product']);$i++){
			$pro_desc=$pro_desc.",".$input['product'][$i]['product_name']."=".$input['product'][$i]['amount'];
		}

		$sha = sha1("ACCEPTURL=".$ACCEPTURL.$SHAIN."AMOUNT=".$amount.$SHAIN."BACKURL=".$BACKURL.$SHAIN."CANCELURL=".$CANCELURL.$SHAIN."CATALOGURL=".$CATALOGURL.$SHAIN."CN=".$client_name.$SHAIN."CURRENCY=".$currency.$SHAIN."DECLINEURL=".$DECLINEURL.$SHAIN."EMAIL=".$config['email'].$SHAIN."EXCEPTIONURL=".$EXCEPTIONURL.$SHAIN."HOMEURL=".$HOMEURL.$SHAIN."LANGUAGE=".$lang.$SHAIN."ORDERID=".$orderno.$SHAIN."PSPID=".$PSPID.$SHAIN."SUBSCRIPTION_ID=".$subid.$SHAIN."SUB_AMOUNT=".$sub_amount.$SHAIN."SUB_COM=".$sub_title.$SHAIN."SUB_COMMENT=".$pro_desc.$SHAIN."SUB_ENDDATE=".$input['subscription'][0]['end'].$SHAIN."SUB_ORDERID=".$sub_order_id.$SHAIN."SUB_PERIOD_MOMENT=".$sub_period_moment.$SHAIN."SUB_PERIOD_NUMBER=".$sub_period_no.$SHAIN."SUB_PERIOD_UNIT=".$input['subscription'][0]['frequency'].$SHAIN."SUB_STARTDATE=".$input['subscription'][0]['start'].$SHAIN."SUB_STATUS=1".$SHAIN);

		return view('ingenico_with_subscription',['PSPID'=>$PSPID,'AMOUNT'=>$amount,'CURRENCY'=>$currency,'LANGUAGE'=>$lang,'SUBSCRIPTION_ID'=>$subid,'SUB_AMOUNT'=>$sub_amount,'SUB_COM'=>$sub_title,'SUB_ORDERID'=>$sub_order_id,'ORDERID'=>$orderno, 'SUB_PERIOD_UNIT'=>$input['subscription'][0]['frequency'],'SUB_PERIOD_NUMBER'=>$sub_period_no,'SUB_PERIOD_MOMENT'=>$sub_period_moment,'SUB_STARTDATE'=>$input['subscription'][0]['start'],'SUB_ENDDATE'=>$input['subscription'][0]['end'],'SUB_STATUS'=>1, 'SUB_COMMENT'=>$pro_desc, 'SHASIGN'=>$sha, 'ACCEPTURL'=>$ACCEPTURL,'DECLINEURL'=>$DECLINEURL,'EXCEPTIONURL'=>$EXCEPTIONURL,'CANCELURL'=>$CANCELURL,'BACKURL'=>$BACKURL,'HOMEURL'=>$HOMEURL,'CATALOGURL'=>$CATALOGURL,'CN'=>$client_name,'EMAIL'=>$config['email']]);
	 }
	
    
	public function paymentWithoutSubscription($input, $config){

		$SHAIN = ''; //you may change it from dashboard
		$PSPID = '';

		$client_name = '';//Insert client name.

	 	$ACCEPTURL = $this->getBaseUrl().'/payment_response';
	 	$CANCELURL = $this->getBaseUrl().'/payment_response';
	 	$DECLINEURL = $this->getBaseUrl().'/payment_response';
	 	$EXCEPTIONURL = $this->getBaseUrl().'/payment_response';
	 	$BACKURL = $this->getBaseUrl().'/payment_response';
	 	$HOMEURL = $this->getBaseUrl().'/payment_response';
	 	$CATALOGURL = $this->getBaseUrl().'/payment_response';


		$orderno=56;//Retrieve the last orderno from database
		$orderno++;//Update the new orderno
		for($i=0;$i<sizeof($input['product']);$i++){
			$amount=$input['product'][$i]['amount'];//Total amount of all products in the cart: 
		}
		$amount=$amount*100;//Multiplying amount with 100 to pass as parameter
		$currency=$input['product'][0]['currency'];
		$lang="en_US";

		$sha = sha1("ACCEPTURL=".$ACCEPTURL.$SHAIN."AMOUNT=".$amount.$SHAIN."BACKURL=".$BACKURL.$SHAIN."CANCELURL=".$CANCELURL.$SHAIN."CATALOGURL=".$CATALOGURL.$SHAIN."CN=".$client_name.$SHAIN."CURRENCY=".$currency.$SHAIN."DECLINEURL=".$DECLINEURL.$SHAIN."EMAIL=".$config['email'].$SHAIN."EXCEPTIONURL=".$EXCEPTIONURL.$SHAIN."HOMEURL=".$HOMEURL.$SHAIN."LANGUAGE=".$lang.$SHAIN."ORDERID=".$orderno.$SHAIN."PSPID=".$PSPID.$SHAIN);

		return view('ingenico_without_subscription',['PSPID'=>$PSPID,'AMOUNT'=>$amount,'CURRENCY'=>$currency,'LANGUAGE'=>$lang,'ORDERID'=>$orderno,'ACCEPTURL'=>$ACCEPTURL,'DECLINEURL'=>$DECLINEURL,'EXCEPTIONURL'=>$EXCEPTIONURL,'CANCELURL'=>$CANCELURL,'BACKURL'=>$BACKURL,'HOMEURL'=>$HOMEURL,'CATALOGURL'=>$CATALOGURL,'CN'=>$client_name,'EMAIL'=>$config['email'],'SHASIGN'=>$sha]);
	}

	public function payment_response(Request $request){

		$all = $request->all();
		var_dump($all);
	}

	public function details(Request $request){
	
		$ORDERID=57;//Retrieve order id from database.
		$PAYID=;//Insert Payid.

		$PSPID = '';
		$PSWD = '';
		$USERID = '';

		return view('ingenico_details',['PSPID'=>$PSPID,'ORDERID'=>$ORDERID,'PAYID'=>$PAYID,'PSWD'=>$PSWD,'USERID'=>$USERID]);
	}

	public function refund(Request $request){
	
		$ORDERID=56;//Get the order no. you want to refund
		$AMOUNT=300;// amount to be refunded
		$PAYID=;//get the payid of the transaction as recieved after successful transaction
		
		$PSPID = '';
		$PSWD = '';
		$USERID = '';

		$OPERATION = 'RFD';//Insert operation to be performed RFD means refund.

		$SHAIN = ''; //you may change it from dashboard

		$SHASIGN = sha1("AMOUNT=".$AMOUNT.$SHAIN."OPERATION=".$OPERATION.$SHAIN."ORDERID=".$ORDERID.$SHAIN."PAYID=".$PAYID.$SHAIN."PSPID=".$PSPID.$SHAIN."PSWD=".$PSWD.$SHAIN."USERID=".$USERID.$SHAIN);

		return view('ingenico_refund',['PSPID'=>$PSPID,'AMOUNT'=>$AMOUNT,'ORDERID'=>$ORDERID,'OPERATION'=>$OPERATION,'PAYID'=>$PAYID,'PSWD'=>$PSWD,'USERID'=>$USERID,'SHASIGN'=>$SHASIGN]);
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

}