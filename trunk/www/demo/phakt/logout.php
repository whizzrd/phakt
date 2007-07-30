<?php 
//Aditional Functions
require_once('includes/functions.inc.php'); 
?>
<?php
// *** Logout the current user. true
$KT_logoutRedirectPage = "login.php";
session_start();
session_unregister("KT_Username");
if ($KT_logoutRedirectPage != "") {
  KT_redir($KT_logoutRedirectPage);
}
?>
<?php //PHP ADODB document - made with PHAkt 2.0.60?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
</body>
</html>
