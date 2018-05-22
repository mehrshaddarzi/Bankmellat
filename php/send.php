<?php

include_once('bank.mellat.php');
$terminalId		= "xxx";
$userName		= "xxx";
$userPassword	= "xxxx";
$mellat = new MellatBank($terminalId, $userName, $userPassword);
$mellat->startPayment(1000, "https://digiyadaki.com/bank/ver.php");

/*
include_once('nusoap.php');
$terminalId		= "3514710";					// Terminal ID
$userName		= "dig89";					// Username
$userPassword	= "95583968";					// Password
$orderId		= time();						// Order ID
$amount 		= "1000";						// Price / Rial
$localDate		= date('Ymd');					// Date
$localTime		= date('Gis');					// Time
$additionalData	= '';
$callBackUrl	= "https://digiyadaki.com/bank/ver.php";	// Callback URL
$payerId		= 356;

//-- تبدیل اطلاعات به آرایه برای ارسال به بانک
$parameters = array(
	'terminalId' 		=> $terminalId,
	'userName' 			=> $userName,
	'userPassword' 		=> $userPassword,
	'orderId' 			=> $orderId,
	'amount' 			=> $amount,
	'localDate' 		=> $localDate,
	'localTime' 		=> $localTime,
	'additionalData' 	=> $additionalData,
	'callBackUrl' 		=> $callBackUrl,
	'payerId' 			=> $payerId);

$client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
$namespace='http://interfaces.core.sw.bps.com/';
$result 	= $client->call('bpPayRequest', $parameters, $namespace);
//-- بررسی وجود خطا
if ($client->fault)
{
	//-- نمایش خطا
	echo "There was a problem connecting to Bank";
	exit;
} 
else
{
	$err = $client->getError();
	if ($err)
	{
		//-- نمایش خطا
		echo "Error : ". $err;
		exit;
	} 
	else
	{
		$res 		= explode (',',$result);
		$ResCode 	= $res[0];
		if ($ResCode == "0")
		{
			//-- انتقال به درگاه پرداخت
			echo '<form name="myform" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
					<input type="hidden" id="RefId" name="RefId" value="'. $res[1] .'">
				</form>
				<script type="text/javascript">window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>';
			exit;
		}
		else
		{
			//-- نمایش خطا
			echo "Error : ". $result;
			exit;
		}
	}
}*/
?>
