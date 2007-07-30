<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
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

// build the form action
$editFormAction = $HTTP_SERVER_VARS['PHP_SELF'] . (isset($HTTP_SERVER_VARS['QUERY_STRING']) ? "?" . $HTTP_SERVER_VARS['QUERY_STRING'] : "");

if ((isset($HTTP_POST_VARS["MM_insert"])) && ($HTTP_POST_VARS["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO contacts_con (idusr_con, firstname_con, lastname_con, email_con, birthdate_con) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($HTTP_POST_VARS['idusr_con'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['firstname_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['lastname_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['email_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['birthdate_con'], "date"));

  $Result1 = $test->Execute($insertSQL) or die($test->ErrorMsg());

  $insertGoTo = "index.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($insertGoTo);
}
 //PHP ADODB document - made with PHAkt 2.0.60?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
  <table align="center">
    <tr valign="baseline"> 
      <td nowrap align="right">Id_con:</td>
      <td></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Firstname_con:</td>
      <td><input type="text" name="firstname_con" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Lastname_con:</td>
      <td><input type="text" name="lastname_con" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Email_con:</td>
      <td><input type="text" name="email_con" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Birthdate_con:</td>
      <td><input type="text" name="birthdate_con" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">&nbsp;</td>
      <td><input type="submit" value="Insert Record"></td>
    </tr>
  </table>
  <input type="hidden" name="idusr_con" value="<?php echo $HTTP_SESSION_VARS['userId']?>">
  <input type="hidden" name="MM_insert" value="form1">
</form>
<p><a href="index.php">Back</a></p>
  
</body>
</html>
