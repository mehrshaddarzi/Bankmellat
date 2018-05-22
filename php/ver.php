<?php

include_once('bank.mellat.php');
$terminalId		= "xxx";
$userName		= "xxx";
$userPassword	= "xxx";
$mellat = new MellatBank($terminalId, $userName, $userPassword);
 $results = $mellat->checkPayment($_POST);
  if($results['status']=='success') {
	  # تراکنش با موفقیت انجام شده است.
	  echo $results['trans']; # شماره تراکنش
  }
  else {
	  # تراکنش موفق نبوده است .
	  die(var_dum($results));
  }
