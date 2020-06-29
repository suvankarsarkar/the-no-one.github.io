<?php
session_start();
include '../_php/header.php';
include '../_php/db.php';

/*Note : After completing transaction process it is recommended to make an enquiry call with PayU to validate the response received and then save the response to DB or display it on UI*/

echo '
<br>
<br>
<br>
<br>
<h2> Payment Failed</h2>
<br>
<br>
<br>';
include '../_php/footer.php';
?>
