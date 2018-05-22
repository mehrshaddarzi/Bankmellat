<?php

//Payment Online ZarinPal
add_action_once('wp', [ $this, 'SendZarinPal']);
add_action_once('wp', [ $this, 'ReceiveZarinPal']);


public function SendZarinPal()
    {
        if(isset($_GET['paymentfactor']) and isset($_GET['step']) and $_GET['step'] =="go") {

            $url_back = ACL::get_instance()->UserPanelPage().'?run=myshop';

            //Not User Login
            if(ACL::get_instance()->is_user_logged_in() ===false) {
                wp_redirect($url_back);
                exit;
            }

            //Check Validation request
            $factor = FactorDB::where('user_id', '=', get_current_user_id())->where('id', '=', $_GET['paymentfactor'])->where('status', '=', '1')->where('pay_type', '=', '1')->get();
            if($factor->count() >0) {


				include( get_template_directory() . '/includes/nusoap/nusoap.php');
				//Mellat
				include( get_template_directory() . '/includes/bank.mellat.php');

				$MerchantID = $this->redux['zarinpal-id'];
                $Amount = $this->FactorPrice($_GET['paymentfactor']);
                $Description = 'بابت پرداخت آنلاین فاکتور خرید به شماره '.$_GET['paymentfactor'];

                //Tax ZarinPal
                $price = $Amount;
                /*if($this->redux['is_tax_zarinpal'] =="yes") {
                    $price = round($Amount + round($Amount*0.010));
                }*/
				
				$price_in_db = $price;
				//check if is Rial
				//if($this->MoneyUnit() =="ریال") {
				//	$price = $price / 10;
				//}
				

                //Add Payment to database
                $payment = PaymentDB::create([
                    'type'=> 1,
                    'status'=> 1,
                    'factor_id'=> $_GET['paymentfactor'],
                    'user_id'=> get_current_user_id(),
                    'price'=> $price_in_db,
                    'online_payid'=> '',
                    'type_pay'=> '1',
                    'bank_account'=> '',
                    'receipt_number'=> '',
                    'receipt_date'=> '',
                    'comment'=> $Description,
                    'date_create'=> current_time('mysql'),
                ]);
                $payment->save();
                if( $payment->exists ) {

                    $idpay = $payment->id;
                    $CallbackURL = $url_back.'&paymentfactor='.$_GET['paymentfactor'].'&step=receive&price='.$price.'&payid='.$idpay; // Required
                    $Email = ucfirst(wp_get_current_user()->user_email);
                    $Mobile = ACL::get_instance()->GetUserMeta('mobile');

                    //Sent request tozarinpal
                   /* $client = new \nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
                    $client->soap_defencoding = 'UTF-8';
                    $result = $client->call('PaymentRequest', array(
                            array(
                                'MerchantID' 	=> $MerchantID,
                                'Amount' 		=> $price,
                                'Description' 	=> $Description,
                                'Email' 		=> $Email,
                                'Mobile' 		=> $Mobile,
                                'CallbackURL' 	=> $CallbackURL
                            )
                        )
                    );*/
					
					//mellat
					$terminalId		= "3514710";
					$userName		= "dig89";
					$userPassword	= "95583968";
					$mellat = new \MellatBank($terminalId, $userName, $userPassword);
					$order_id = time().rand(10000,99999);
					
                    //Update Order Id Pay
                    //PaymentDB::where('id', '=', $idpay)->update(['online_payid' => ltrim($result['Authority'], '0')]);
                    PaymentDB::where('id', '=', $idpay)->update(['online_payid' => $order_id]);

                    //Check request zarinPal is True / False
                    /*if($result['Status'] == 100)
                    {
                        Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result['Authority']);
                        exit;
                    } else {
                        wp_redirect($url_back.'&factor_id='.$_GET['paymentfactor'].'&PaymentStatus='.$idpay);
                        exit;
                    }*/
					
					$mellat->startPayment($price, $CallbackURL, $order_id , get_current_user_id(), $Description);


                } else {
                    wp_redirect($url_back);
                    exit;
                }

            } else {
                wp_redirect($url_back);
                exit;
            }

            exit;
        }
    }


    public function ReceiveZarinPal()
    {
        //if(isset($_GET['paymentfactor']) and isset($_GET['step']) and $_GET['step'] =="receive" and isset($_GET['payid']) and isset($_GET['Status'])) {
        if(isset($_GET['paymentfactor']) and isset($_GET['step']) and $_GET['step'] =="receive" and isset($_GET['payid'])) {

            $url_back = ACL::get_instance()->UserPanelPage().'?run=myshop&factor_id='.$_GET['paymentfactor'].'&PaymentStatus='.$_GET['payid'];

            include( get_template_directory() . '/includes/nusoap/nusoap.php');
            $MerchantID = $this->redux['zarinpal-id'];
            $Amount = $_GET['price']; //Amount will be based on Toman
            //$Authority = $_GET['Authority'];

			//mellat
			include( get_template_directory() . '/includes/bank.mellat.php');
			$terminalId		= "3514710";
			$userName		= "dig89";
			$userPassword	= "95583968";
			$mellat = new \MellatBank($terminalId, $userName, $userPassword);
			
			
			$results = $mellat->checkPayment($_POST);
		  if($results['status']=='success') {
			  # تراکنش با موفقیت انجام شده است.
			  //echo $results['trans']; # شماره تراکنش
			  
			  
			  //Update Detail in Factor
			FactorDB::where('id', '=', $_GET['paymentfactor'])->update(['status' => '2']);

			//Update ditail Factor
			PaymentDB::where('id', '=', $_GET['payid'])->update(['status' => '2']);

			//Sms for Admin
			SMS::get_instance()->Send(SMS::get_instance()->Content(6,[
				'factor_id' =>  $_GET['paymentfactor'],
				'factor_price' => $Amount,
			]) ,'admin');


			wp_redirect($url_back);
			exit;

			  
		  }
		  else {
			  # تراکنش موفق نبوده است .
			  //die(var_dum($results));
			  
			  //Payment is canseled
                wp_redirect($url_back);
                exit;
		  }
					
			
			
			
			
            /*if($_GET['Status'] == 'OK') {
                // URL also Can be https://ir.zarinpal.com/pg/services/WebGate/wsdl
                $client = new \nusoap_client('https://de.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
                $client->soap_defencoding = 'UTF-8';
                $result = $client->call('PaymentVerification', array(
                        array(
                            'MerchantID' => $MerchantID,
                            'Authority' => $Authority,
                            'Amount' => $Amount
                        )
                    )
                );
                if ($result['Status'] == 100) {

                    //Update Detail in Factor
                    FactorDB::where('id', '=', $_GET['paymentfactor'])->update(['status' => '2']);

                    //Update ditail Factor
                    PaymentDB::where('id', '=', $_GET['payid'])->update(['status' => '2']);

                    //Sms for Admin
                    SMS::get_instance()->Send(SMS::get_instance()->Content(6,[
                        'factor_id' =>  $_GET['paymentfactor'],
                        'factor_price' => $Amount,
                    ]) ,'admin');


                    wp_redirect($url_back);
                    exit;
                } else {
                    //Problem in Payment
                    wp_redirect($url_back);
                    exit;
                }
            } else {
                //Payment is canseled
                wp_redirect($url_back);
                exit;
            }*/

            exit;
        }
    }
