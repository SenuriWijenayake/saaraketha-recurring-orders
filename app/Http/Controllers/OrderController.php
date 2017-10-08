<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OrderController extends Controller

{   


public function reorder(Request $request){
file_put_contents("php://stderr", "####################\n");
       //================optimize later using threads and synchronizatioon ====================================
    //traverse through note attributes
	$note_attribute_count = (int)count($request['note_attributes']);
	
	for($r=0;$r<$note_attribute_count;$r++){
	
		$_name = $request['note_attributes'][$r]['name'];
		
		switch ($_name){
				 case 'include_gift_wrapping':
					$_include_gift_wrapping_name = $_name;
					$_include_gift_wrapping_value = (string)$request['note_attributes'][$r]['value'];
					break;
				 case 'subscribe_order':
					$_subscribe_order_name = $_name;
					$_subscribe_order_value =  (string)$request['note_attributes'][$r]['value'];
					break;
				 case 'recurring_duration_months':
					$_recurring_duration_months_name=$_name;
					file_put_contents("php://stderr", "$_recurring_duration_months_name\n");
					//$_recurring_duration_months_value = (string)$request['note_attributes'][$r]['value'];
					$_recurring_duration_months_value = (int)$request['note_attributes'][$r]['value'];
					file_put_contents("php://stderr", "$_recurring_duration_months_value\n");
					break;
				 case 'streamthing_delivery_date':
					$_streamthing_delivery_date_name = $_name;
					$_streamthing_delivery_date_value=date('m/d/Y', strtotime((string)$request['note_attributes'][$r]['value']));
				  	break;
// 				 case 'cut_off_date':     //add later after test
// 					$_cut_off_date_name =$_name; //format this later and bind
// 					//$_cut_off_date_value = date('m/d/Y', strtotime((string)$request['note_attributes'][$r]['value']));
// 				  	//file_put_contents("php://stderr", "$_cut_off_date_value\n");
// 					break;
// 				 case 'area': //add later after test
// 					$_area_name = $_name;
// 					$_area_value= (string)$request['note_attributes'][$r]['value'];
// 					break;
				default:
					break;
				}
		}
	file_put_contents("php://stderr", "$_subscribe_order_name\n");
	if($_subscribe_order_name=='subscribe_order'){
		if($_subscribe_order_value){
			$_recurring_duration = $_recurring_duration_months_value*4;
			for($i=0;$i<$_recurring_duration;$i++){
				file_put_contents("php://stderr", "$i\n");
				
				//edit order json
						//items from previous order
						$line_items = $request['line_items'];
						$gateway = $request['gateway']; //check with requirements
						$total_price= $request['total_price'];					
						$subtotal_price= $request['subtotal_price'];					
						$total_weight= $request['total_weight'];				
						$total_tax= $request['total_tax'];					
						$taxes_included= $request['taxes_included'];				
						$currency= $request['currency'];	
						$total_discounts = $request['total_discounts'];
						$total_line_items_price = $request['total_line_items_price'];
						$total_price_usd = $request['total_price_usd'];
													
						//calculate delivery date
						$_streamthing_delivery_date_value = date('Y-m-d', strtotime($_streamthing_delivery_date_value . " + 7 day"));
						file_put_contents("php://stderr", "$_streamthing_delivery_date_value\n");
						file_put_contents("php://stderr", "******\n");
						$_streamthing_delivery_date_value = date('F jS, Y', strtotime($_streamthing_delivery_date_value));
						file_put_contents("php://stderr", "$_streamthing_delivery_date_value\n");
				
						//calculate cut off date
						$_cut_off_date_value = date('F jS, Y', strtotime($_streamthing_delivery_date_value . " - 2 day"))." - 12:00 AM";
						file_put_contents("php://stderr", "$_cut_off_date_value\n");
						file_put_contents("php://stderr", "========\n");
					
						//root order details
						$root_order_id = $request['id'];
						$root_order_name = $request['name'];
				
						$note_attributes = array(
							//$_include_gift_wrapping_name => $_include_gift_wrapping_value, //add later after test
							'created_as_recurring' => true,
							'root_order_name' => $root_order_name,
							'root_order_id' => $root_order_id,
							'current_recurring_iteration'=> $i+1,
							'recurring_frequency' => $_recurring_duration,
							$_streamthing_delivery_date_name=>$_streamthing_delivery_date_value,
							'cut_off_date' =>$_cut_off_date_value, //set time later 
							//$_area_name =>$_area_value   //add later after test
							);
						$payment_gateway_names = $request['payment_gateway_names'];
						$contact_email = $request['contact_email'];
						$origin_location = $request['origin_location'];
						$destination_location = $request['destination_location'];
						$shipping_lines = $request['shipping_lines'];
						$billing_address = $request['billing_address'];
						$shipping_address=$request['shipping_address'];
				
						//set order name
						$order_name = $request['name'];
						$order_iteration = $i+1;
						$order_name_suffix = (string)$order_iteration;
						$order_name = $order_name."R".$order_name_suffix;
						file_put_contents("php://stderr", "$order_name\n");
				
				//set order
						$orderdata = array(
							'order' => array(
							'name' => $order_name,
							'email' => $request['email'],
							'line_items' => $line_items,
							'gateway' => $gateway,  //check with requirements
							'total_price'=>$total_price,
							'subtotal_price'=>$subtotal_price,
							'total_weight'=>$total_weight,
							'total_tax'=>$total_tax,
							'taxes_included'=>$taxes_included,
							'currency'=>$currency,
							'financial_status'=> 'pending',
							'total_discounts'=>$total_discounts,
							'total_line_items_price'=>$total_line_items_price,
							'total_price_usd'=>$total_price_usd,
							'payment_gateway_names '=>$payment_gateway_names ,
							'tags'=>'created_on_subscription',
							'contact_email'=>$contact_email,
							'origin_location'=>$origin_location,
							'destination_location'=>$destination_location,
							'shipping_lines'=>$shipping_lines,
							'billing_address'=>$billing_address,
							'shipping_address'=>$shipping_address,
							'note_attributes' => $note_attributes
							));
						$order = json_encode ($orderdata);
						$logcontent = "$order\n";
						file_put_contents("php://stderr", $logcontent);
					//post  reorder
						$this -> set_reorder($order);

			}
				
		}

}
	file_put_contents("php://stderr", "####################\n");
	
}

 private function set_reorder($order){
    //create client and post data
	$url =(string)('https://d69dc791fbc4e0f64edea9ec3ae422ea:3f2099e0135c61c8554819d7d294d125@saarai-test.myshopify.com/admin/orders.json');
	$client = new Client();
	$RequestResponse = $client->post($url, ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'], 'body' => $order]);
 }
}



