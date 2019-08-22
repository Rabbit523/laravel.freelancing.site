<?php

// include_once("config.php");
include_once("paypal_functions.php");
include_once("paypal.class.php");

	$paypal= new MyPayPal();
	
	//Post Data received from product list page.
	if (!empty($_POST['transferToWallet']) && $_POST['transferToWallet'] == 'transferToWallet') {

			//-------------------- prepare products -------------------------
			$products = [];
			// set an item via POST request

			$products[0]['ItemName'] = "Wallet"; //Item Name
			$products[0]['ItemPrice'] = $_POST['WalletAmount']; //Item Price
			$products[0]['ItemNumber'] = 1; //Item Number
			$products[0]['ItemDesc'] = "Transfer money into wallet"; //Item Number
			$products[0]['ItemQty']	= 1; // Item Quantity

			//-------------------- prepare charges -------------------------

			$charges = [];

			//Other important variables like tax, shipping cost
			$charges['TotalTaxAmount'] = 0;  //Sum of tax for all items in this order.
			$charges['HandalingCost'] = 0;  //Handling cost for this order.
			$charges['InsuranceCost'] = 0;  //shipping insurance cost for this order.
			$charges['ShippinDiscount'] = 0; //Shipping discount for this order. Specify this as negative number.
			$charges['ShippinCost'] = 0; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.

			//------------------SetExpressCheckOut-------------------

			//We need to execute the "SetExpressCheckOut" method to obtain paypal token
			$paypal->SetExpressCheckOut($products, $charges);
		
	} elseif(_GET('token')!=''&&_GET('PayerID')!=''){

		//------------------DoExpressCheckoutPayment-------------------

		//Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
		//we will be using these two variables to execute the "DoExpressCheckoutPayment"
		//Note: we haven't received any payment yet.

		$paypal->DoExpressCheckoutPayment();
	}