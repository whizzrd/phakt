<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php
// *** Restrict Access To Page: Grant or deny access to this page
$KT_authorizedUsers=" 10";
$KT_authFailedURL="login.php";
$KT_grantAccess=0;
session_start();
if (isset($HTTP_SESSION_VARS["KT_Username"])) {
  if (false || !(isset($HTTP_SESSION_VARS["KT_userAuth"])) || $HTTP_SESSION_VARS["KT_userAuth"]=="" || strpos($KT_authorizedUsers, $HTTP_SESSION_VARS["KT_userAuth"])) {
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

// build the form action
$editFormAction = $HTTP_SERVER_VARS['PHP_SELF'] . (isset($HTTP_SERVER_VARS['QUERY_STRING']) ? "?" . $HTTP_SERVER_VARS['QUERY_STRING'] : "");

if ((isset($HTTP_POST_VARS["MM_update"])) && ($HTTP_POST_VARS["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE users_usr SET username_usr=%s, password_usr=%s, level_usr=%s WHERE id_usr=%s",
                       GetSQLValueString($HTTP_POST_VARS['username_usr'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['password_usr'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['level_usr'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['id_usr'], "int"));

  $Result1 = $test->Execute($updateSQL) or die($test->ErrorMsg());
  $updateGoTo = "index.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($updateGoTo);
}

// begin Recordset
$colname__rsUser = '1';
if (isset($HTTP_GET_VARS['id_usr'])) {
  $colname__rsUser = $HTTP_GET_VARS['id_usr'];
}
$query_rsUser = sprintf("SELECT * FROM users_usr WHERE id_usr = %s", $colname__rsUser);
$rsUser = $test->SelectLimit($query_rsUser) or die($test->ErrorMsg());
$totalRows_rsUser = $rsUser->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.60?>


<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method="POST" name="form1" action="<?php echo $editFormAction; ?>">
  <table align="center">
    <tr valign="baseline"> 
      <td nowrap align="right">Id_usr:</td>
      <td><?php echo $rsUser->Fields('id_usr'); ?></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Username_usr:</td>
      <td><input type="text" name="username_usr" value="<?php echo $rsUser->Fields('username_usr'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Password_usr:</td>
      <td><input type="text" name="password_usr" value="<?php echo $rsUser->Fields('password_usr'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Level_usr:</td>
      <td><input type="text" name="level_usr" value="<?php echo $rsUser->Fields('level_usr'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">&nbsp;</td>
      <td><input type="submit" value="Update Record"></td>
    </tr>
  </table>
  <input type="hidden" name="MM_update" value="form1">
  <input type="hidden" name="id_usr" value="<?php echo $rsUser->Fields('id_usr'); ?>">
</form>
<p><a href="index.php">Back</a></p>
  
</body>
</html>
<?php
$rsUser->Close();
?>

