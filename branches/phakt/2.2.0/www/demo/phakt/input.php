<?php require_once('Connections/phaktconn.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
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
$editFormAction = $HTTP_SERVER_VARS['PHP_SELF'] . (isset($HTTP_SERVER_VARS['QUERY_STRING'])) ? ("?" . $HTTP_SERVER_VARS['QUERY_STRING']) : "";

if ((isset($HTTP_POST_VARS["MM_insert"])) && ($HTTP_POST_VARS["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO employees_emp (firstname_emp, lastname_emp, address_emp, code_emp, email_emp, phone_emp, fax_emp, childreno_emp, marital_emp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($HTTP_POST_VARS['firstname_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['lastname_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['address_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['code_emp'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['email_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['phone_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['fax_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['childreno_emp'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['marital_emp'], "int"));

  $Result1 = $phaktconn->Execute($insertSQL) or die($phaktconn->ErrorMsg());

  $insertGoTo = "list.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($insertGoTo);
}

// begin Recordset
$query_rsMar = "SELECT * FROM marital_mar ORDER BY status_mar ASC";
$rsMar = $phaktconn->SelectLimit($query_rsMar) or die($phaktconn->ErrorMsg());
$totalRows_rsMar = $rsMar->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.37?>


<html>
<head>
<title>The manually created insert record</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
  <p>First name : 
    <input name="firstname_emp" type="text" id="firstname_emp">
    <br>
    Last name : 
    <input name="lastname_emp" type="text" id="lastname_emp">
    <br>
    Address: 
    <input name="address_emp" type="text" id="address_emp">
    <br>
    Code: 
    <input name="code_emp" type="text" id="code_emp">
    <br>
    Email: 
    <input name="email_emp" type="text" id="email_emp">
    <br>
    Phone: 
    <input name="phone_emp" type="text" id="phone_emp">
    <br>
    Fax: 
    <input name="fax_emp" type="text" id="fax_emp">
    <br>
    Children: 
    <input name="childreno_emp" type="text" id="childreno_emp">
    <br>
    Marital status: 
    <select name="marital_emp" id="marital_emp">
      <?php
  while(!$rsMar->EOF){
?>
      <option value="<?php echo $rsMar->Fields('id_mar')?>"><?php echo $rsMar->Fields('status_mar')?></option>
      <?php
    $rsMar->MoveNext();
  }
  $rsMar->MoveFirst();
?>
    </select>
  </p>
  <p> 
    <input type="submit" name="Submit" value="Submit">
    <input type="reset" name="Submit2" value="Reset">
  </p>
  <input type="hidden" name="MM_insert" value="form1">
</form>
<p><a href="list.php">Back</a></p>
</body>
</html>
<?php
$rsMar->Close();
?>

