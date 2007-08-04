<?php require_once('Connections/phaktconn.php'); ?>
<?php
//Aditional Functions
require_once('includes/functions.inc.php'); 
?>
<?php
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

// begin Recordset
$colname__rsEmp = '1';
if (isset($HTTP_GET_VARS['id_emp'])) {
  $colname__rsEmp = $HTTP_GET_VARS['id_emp'];
}
$query_rsEmp = sprintf("SELECT * FROM employees_emp WHERE id_emp = %s", $colname__rsEmp);
$rsEmp = $phaktconn->SelectLimit($query_rsEmp) or die($phaktconn->ErrorMsg());
$totalRows_rsEmp = $rsEmp->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.37?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<p><?php echo $rsEmp->Fields('firstname_emp'); ?></p>
<p> <?php echo $rsEmp->Fields('lastname_emp'); ?></p>
<p><?php echo $rsEmp->Fields('address_emp'); ?></p>
<p><a href="list.php">Back</a> </p>
</body>
</html>
<?php
$rsEmp->Close();
?>

