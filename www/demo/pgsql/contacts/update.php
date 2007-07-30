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

if ((isset($HTTP_POST_VARS["MM_update"])) && ($HTTP_POST_VARS["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE contacts_con SET idusr_con=%s, firstname_con=%s, lastname_con=%s, email_con=%s, birthdate_con=%s WHERE id_con=%s",
                       GetSQLValueString($HTTP_POST_VARS['idusr_con'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['firstname_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['lastname_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['email_con'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['birthdate_con'], "date"),
                       GetSQLValueString($HTTP_POST_VARS['id_con'], "int"));

  $Result1 = $test->Execute($updateSQL) or die($test->ErrorMsg());
  $updateGoTo = "index.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($updateGoTo);
}

// begin Recordset
$colname__rsContact = '1';
if (isset($HTTP_GET_VARS['id_con'])) {
  $colname__rsContact = $HTTP_GET_VARS['id_con'];
}
$query_rsContact = sprintf("SELECT * FROM contacts_con WHERE id_con = %s", $colname__rsContact);
$rsContact = $test->SelectLimit($query_rsContact) or die($test->ErrorMsg());
$totalRows_rsContact = $rsContact->RecordCount();
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
      <td nowrap align="right">Id_con:</td>
      <td><?php echo $rsContact->Fields('id_con'); ?></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Firstname_con:</td>
      <td><input type="text" name="firstname_con" value="<?php echo $rsContact->Fields('firstname_con'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Lastname_con:</td>
      <td><input type="text" name="lastname_con" value="<?php echo $rsContact->Fields('lastname_con'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Email_con:</td>
      <td><input type="text" name="email_con" value="<?php echo $rsContact->Fields('email_con'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Birthdate_con:</td>
      <td><input type="text" name="birthdate_con" value="<?php echo $rsContact->Fields('birthdate_con'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">&nbsp;</td>
      <td><input type="submit" value="Update Record"></td>
    </tr>
  </table>
  <input type="hidden" name="idusr_con" value="<?php echo $rsContact->Fields('idusr_con'); ?>">
  <input type="hidden" name="MM_update" value="form1">
  <input type="hidden" name="id_con" value="<?php echo $rsContact->Fields('id_con'); ?>">
</form>
<p><a href="index.php">Back</a></p>
  
</body>
</html>
<?php
$rsContact->Close();
?>

