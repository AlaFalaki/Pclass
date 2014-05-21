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


	$gateWay = new PasargadBank_GateWay();
	date_default_timezone_set('Asia/Tehran');
	$gateWay->SendOrder(1000,date("Y/m/d H:i:s"),1000);
