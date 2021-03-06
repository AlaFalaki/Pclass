<?php
session_start();

/*
 * This is a class for Pasargad bank internet gateway.
 * Version : 1.0
 * Code by Ala Alam Falaki  /  2014, may
 * All rights recieved by Blog.AlaFalaki.ir
 * Released under MIT License.
 */
 
class PasargadBank_GateWay{
	private $merchantCode = 00000; //Enter merchant code here . (get from bank.)
	private $terminalCode = 00000; // Enter terminal code here . (get from bank.)
	private $redirectAddress = "http://127.0.0.1/pasargadGateway/verify.php"; // Enter redirect url here . in this example This is The Address .


	
	/*
	 * SEND ORDER TO BANK
	 * 
	 * @param $invoiceNumber int
	 * @param invoiceDate date()
	 * @param amount int/Rial
	 * 
	 * Return False if there is a error,
	 * redirect to gateway if everything OK.
	 */
	function sendOrder($invoiceNumber = NULL, $invoiceDate = NULL, $amount = NULL){
		if(isset($invoiceNumber) AND isset($invoiceDate) AND isset($amount)){
			require_once("libraries/RSAProcessor.class.php");
			$processor = new RSAProcessor("publicKey.xml",RSAKeyType::XMLFile);
			date_default_timezone_set('Asia/Tehran');
			$timeStamp = date("Y/m/d H:i:s");
			$action = "1003";
			$_SESSION['pasargadAmount'] = $amount;
			
			$data = "#". $this->merchantCode ."#". $this->terminalCode ."#". $invoiceNumber ."#". $invoiceDate ."#". $amount ."#". $this->redirectAddress ."#". $action ."#". $timeStamp ."#";
			$data = sha1($data,true);
			$data =  $processor->sign($data);
			$result =  base64_encode($data);
			echo "
			<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
			<html>
			  	<body>
			  		<div id='WaitToSend' style='margin:0 auto; width: 600px; text-align: center;'>درحال انتقال به درگاه بانک<br>لطفا منتظر بمانید .</div>
					<form Id='GateWayForm' Method='post' Action='https://pep.shaparak.ir/gateway.aspx' style='display: none;'>
						invoiceNumber<input type='text' name='invoiceNumber' value='$invoiceNumber' />
						invoiceDate<input type='text' name='invoiceDate' value='$invoiceDate' />
						amount<input type='text' name='amount' value='$amount' />
						terminalCode<input type='text' name='terminalCode' value='$this->terminalCode' />
						merchantCode<input type='text' name='merchantCode' value='$this->merchantCode' />
						redirectAddress<input type='text' name='redirectAddress' value='$this->redirectAddress' />
						timeStamp<input type='text' name='timeStamp' value='$timeStamp' />
						action<input type='text' name='action' value='$action' />
						sign<input type='text' name='sign' value='$result' />
					</form>
					<script language='javascript'>document.forms['GateWayForm'].submit();</script>
				</body>
			</html>";
		}else{
			return FALSE;
		}
	}

	/*
	 * Check Order If Exist iN Shaparak Co.
	 * 
	 * @param $tref int
	 * 
	 * Return False if there is a error,
	 * send an array to output if not.
	 */
	function getOrder($tref = NULL){
		if(isset($tref)){
			include_once 'libraries/parser.php';
		
			$fields = array('invoiceUID' => $tref );
			$result = post2https($fields,'https://pep.shaparak.ir/CheckTransactionResult.aspx');
			$array = makeXMLTree($result);
			
		    if($array["resultObj"]["result"] == "True"){
		    	return $array;
			}else{
				return FASLE;
			}
		}else{
			return FALSE;
		}
	}
	
	/*
	 * Verify Order iN Shaparak Co.
	 * 
	 * @param $amount int/Rials
	 * 
	 * Return False if there is a error,
	 * return True if everything OK.
	 */
	function verifyOrder(){
		require_once("libraries/RSAProcessor.class.php"); 
		require_once ("libraries/parser.php");
		$amount = $_SESSION['pasargadAmount'];
		
		$fields = array(
							'MerchantCode' => $this->merchantCode,
							'TerminalCode' => $this->terminalCode,
							'InvoiceNumber' => $_GET['iN'],
							'InvoiceDate' => $_GET['iD'],
							'amount' => $amount,
							'TimeStamp' => date("Y/m/d H:i:s"),
							'sign' => ''
						);
		
		$processor = new RSAProcessor("publicKey.xml",RSAKeyType::XMLFile);
		
		$data = "#". $fields['MerchantCode'] ."#". $fields['TerminalCode'] ."#". $fields['InvoiceNumber'] ."#". $fields['InvoiceDate'] ."#". $fields['amount'] ."#". $fields['TimeStamp'] ."#";
		$data = sha1($data,true);
		$data =  $processor->sign($data);
		$fields['sign'] =  base64_encode($data);
		
		$verifyresult = post2https($fields,'https://pep.shaparak.ir/VerifyPayment.aspx');
		$array = makeXMLTree($verifyresult);

		if($array['actionResult']['result'] == "True"){
			return TRUE;
			unset($_SESSION['pasargadAmount']);
		}else{
			return FALSE;
			unset($_SESSION['pasargadAmount']);
		}
	}
}
