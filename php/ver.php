<?php

include_once('bank.mellat.php');
$terminalId		= "3514710";
$userName		= "dig89";
$userPassword	= "95583968";
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