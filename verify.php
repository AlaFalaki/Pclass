<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

/*
 * This is a class for Pasargad bank internet gateway.
 * Version : 1.0
 * Code by Ala Alam Falaki  /  2014, may
 * All rights recieved by Blog.AlaFalaki.ir
 * Released under MIT License.
 */
 
require_once ("pasargadGatewayClass.php");

$OrderStatus = new PasargadBank_GateWay();
$result = $OrderStatus->getOrder($_GET['tref']);
if($result['resultObj']['result'] == "True"){ // Check the result.
	if($result['resultObj']['amount'] == $_SESSION['pasargadAmount']){ // Check money with session value.		
		if($OrderStatus->verifyOrder()){
			echo "با موفقیت انجام شد.";
		}
	}
}else{
	echo "Your Order Faild.";
}
