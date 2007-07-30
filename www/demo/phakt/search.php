<?php
//Aditional Functions
require_once('includes/functions.inc.php'); 

// *** Restrict Access To Page: Grant or deny access to this page
$KT_authorizedUsers=" ";
$KT_authFailedURL="login.php";
$KT_grantAccess=0;
session_start();
if (isset($HTTP_SESSION_VARS["KT_Username"])) {
  if (true || !(isset($HTTP_SESSION_VARS["KT_userAuth"])) || $HTTP_SESSION_VARS["KT_userAuth"]=="" || strpos($KT_authorizedUsers, $HTTP_SESSION_VARS["KT_userAuth"])) {
    $KT_grantAccess = 1;
  }
}
if (!$KT_grantAccess) {
  $KT_qsChar = "?";
  if (strpos($KT_authFailedURL, "?")) $KT_qsChar = "&";
  $KT_referrer = $HTTP_SERVER_VARS["REQUEST_URI"];
  $KT_authFailedURL = $KT_authFailedURL . $KT_qsChar . "accessdenied=" . urlencode($KT_referrer);
  KT_redir($KT_authFailedURL);
}
 //PHP ADODB document - made with PHAkt 2.0.37?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="get" action="list.php">
  <p>&nbsp; </p>
  <table width="32%" border="0">
    <tr>
      <td width="12%">Name:</td>
      <td width="88%"><input name="name" type="text" id="name"></td>
    </tr>
    <tr>
      <td>Code:</td>
      <td><input name="code" type="text" id="code"></td>
    </tr>
    <tr>
      <td>Address:</td>
      <td><input name="address" type="text" id="address"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="Submit" value="Submit">
        <input type="reset" name="Submit2" value="Reset"></td>
    </tr>
  </table>
  <p>&nbsp; </p>
</form>
<p><a href="menu.php">Back to menu</a></p>
</body>
</html>
