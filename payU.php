<?php
session_start();
if(isset($_SERVER['HTTP_ORIGIN'])) $origin = $_SERVER['HTTP_ORIGIN']; else $origin ="";
$allowed_domains = [
  'https://beanstalkedu.com',
  'https://atheneumglobal.education',
  'http://localhost:3000',
  'http://localhost:8080',
  'http://localhost:8085',
  'http://localhost:8081',
  'https://user.atheneumglobal.com',
  'https://user.atheneumglobal.education',
  'https://admin.atheneumglobal.education',
  'https://teeny.beanstalkedu.com',
  'https://udaan.beanstalkedu.com'
];
if (in_array($origin, $allowed_domains) && isset($_POST['amount']) && isset($_POST['email']) && isset($_POST['phone'])){
  $amnt=$_POST['amount']; //$amnt=('%0.2f', $_POST['amount']);
  $email=$_POST['email'];
  $phone=$_POST['phone'];
  $name=$_POST['name'];
  $PG="payU";
  $Obj2 = new stdClass();
  $Obj2->data ="dd";
  $Obj2->data2 ="moreData";
  $dbJson = json_encode($Obj2);

  header('Access-Control-Allow-Origin: ' . $origin);
  include("_php/db.php");
  $txnid=time()."-".rand(100,999); $key=KEY; $salt=SALT;
  $_SESSION['salt'] = $salt;
  $fnm=explode(' ', $name);
  $productinfo=$_POST['programType']." in ". $_POST['programName'];
  $dd=$key.'|'.$txnid.'|'.$amnt.'|'.$productinfo.'|'.$fnm[0].'|'.$email.'|||||'.$_POST['udf5'].'||||||'.$salt;
  $hash=hash('sha512', $dd);
  $errorm = "";
  $myObj = new stdClass();
  $myObj->data = "dd";
  $myObj->key = $key;
  $myObj->txnid = $txnid;
  $myObj->productinfo = $productinfo;
  $myObj->amount = $amnt;
  $myObj->firstname = $fnm[0];
  $myObj->phone = $phone;
  $myObj->email = $email;
  $myObj->udf5 = $_POST['udf5'];
  $myObj->hash = $hash;
  $myObj->dd = $dd;

  $link = new mysqli(MYSQL_HOST,MYSQL_USER,MYSQL_PASS,MYSQL_DB);
  if ($link->connect_error) $errorm ="connection failed: " . $link->connect_error;

  $link->set_charset("utf8");
  $stmt = $link->prepare("INSERT INTO `TRANSACTION` (`T_TXNID`, `T_DOMAIN`, `T_PROVIDER`, `T_AMOUNT`, `T_NAME`, `T_EMAIL`, `T_PHONE`, `T_PRODUCT`, `T_JSON`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssdsssss", $T_TXNID, $T_DOMAIN, $T_PROVIDER, $T_AMOUNT, $T_NAME, $T_EMAIL,$T_PHONE, $T_PRODUCT, $T_JSON);
  $T_TXNID= $txnid; $T_DOMAIN=$origin; $T_PROVIDER=$PG; $T_AMOUNT= $amnt; $T_NAME=$name; $T_EMAIL= $email; $T_PHONE= $phone; $T_PRODUCT= $productinfo; $T_JSON=$dbJson;

  $exe=$stmt->execute();
  if ($exe) $errorm = "0"; else $errorm = $stmt->error;//echo $myJSON; else echo $errorm;
  $stmt->close();$link->close();

  $myObj->errorm = $errorm;
  $myJSON = json_encode($myObj);
  echo $myJSON;
}
else echo "not allowed!";
?>
