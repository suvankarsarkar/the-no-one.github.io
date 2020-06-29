<?php
session_start();
include '../_php/header.php';
include '../_php/db.php';

/*Note : After completing transaction process it is recommended to make an enquiry call with PayU to validate the response received and then save the response to DB or display it on UI*/

$postdata = $_POST;
$msg = '';
$salt = SALT;
if (isset($postdata ['key'])) {
	$key				=   $postdata['key'];
	$txnid 				= 	$postdata['txnid'];
  $amount      		= 	$postdata['amount'];
	$productInfo  		= 	$postdata['productinfo'];
	$firstname    		= 	$postdata['firstname'];
	$email        		=	$postdata['email'];
	$udf5				=   $postdata['udf5'];
	$status				= 	$postdata['status'];
	$resphash			= 	$postdata['hash'];
	//Calculate response hash to verify
	$keyString 	  		=  	$key.'|'.$txnid.'|'.$amount.'|'.$productInfo.'|'.$firstname.'|'.$email.'|||||'.$udf5.'|||||';
	$keyArray 	  		= 	explode("|",$keyString);
	$reverseKeyArray 	= 	array_reverse($keyArray);
	$reverseKeyString	=	implode("|",$reverseKeyArray);
	$CalcHashString 	= 	strtolower(hash('sha512', $salt.'|'.$status.'|'.$reverseKeyString)); //hash without additionalcharges

	//check for presence of additionalcharges parameter in response.
	$additionalCharges  = 	"";

	If (isset($postdata["additionalCharges"])) {
       $additionalCharges=$postdata["additionalCharges"];
	   //hash with additionalcharges
	   $CalcHashString 	= 	strtolower(hash('sha512', $additionalCharges.'|'.$salt.'|'.$status.'|'.$reverseKeyString));
	}
	//Comapre status and hash. Hash verification is mandatory.
	if ($status == 'success'  && $resphash == $CalcHashString) {
		$msg = "Transaction Successful, Hash Verified...<br />";
		//Do success order processing here...
		//Additional step - Use verify payment api to double check payment.
		if(verifyPayment($key,$salt,$txnid,$status)){

      $msg = "Admission success for <b>".$postdata['productinfo']."</b> soon you will get a mail to access course material!";

      $link = new mysqli(MYSQL_HOST,MYSQL_USER,MYSQL_PASS,MYSQL_DB);
      if ($link->connect_error) $errorm ="connection failed: " . $link->connect_error;

      $stmt = $link->prepare("UPDATE `TRANSACTION` SET `T_STATUS` = '1' WHERE `T_TXNID` = ?");
      // $stmt = $link->prepare("INSERT INTO `TRANSACTION` (`T_TXNID`, `T_DOMAIN`, `T_PROVIDER`, `T_AMOUNT`, `T_NAME`, `T_EMAIL`, `T_PHONE`, `T_PRODUCT`, `T_JSON`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("s", $txnid);
      $exe=$stmt->execute();
      if ($exe) $errorm = "Payment Status Updated!"; else $errorm = $stmt->error;//echo $myJSON; else echo $errorm;
      $stmt->close();$link->close();
      $msg = $msg. '<br>'.$errorm;
    }
		else
			$msg = "Transaction Successful, Hash Verified...Payment Verification failed...";
	}
	else {
		//tampered or failed
		$msg = "Payment failed for Hash not verified...";
	}
}
else exit(0);



echo '
<br>
<br>
<br>
<br> <div class="container"> '.$msg.' </div>
<br>
<br>
<br>';
include '../_php/footer.php';
?>
