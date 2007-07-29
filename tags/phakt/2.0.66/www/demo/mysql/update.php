<?php require_once('Connections/phaktconn.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
<?php
// build the form action
$editFormAction = $HTTP_SERVER_VARS['PHP_SELF'] . (isset($HTTP_SERVER_VARS['QUERY_STRING'])) ? ("?" . $HTTP_SERVER_VARS['QUERY_STRING']) : "";

if ((isset($HTTP_POST_VARS["MM_update"])) && ($HTTP_POST_VARS["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE employees_emp SET firstname_emp=%s, lastname_emp=%s, address_emp=%s, code_emp=%s, email_emp=%s, phone_emp=%s, fax_emp=%s, childreno_emp=%s, marital_emp=%s WHERE id_emp=%s",
                       GetSQLValueString($HTTP_POST_VARS['firstname_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['lastname_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['address_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['code_emp'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['email_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['phone_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['fax_emp'], "text"),
                       GetSQLValueString($HTTP_POST_VARS['childreno_emp'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['marital_emp'], "int"),
                       GetSQLValueString($HTTP_POST_VARS['id_emp'], "int"));

  $Result1 = $phaktconn->Execute($updateSQL) or die($phaktconn->ErrorMsg());
  $updateGoTo = "list.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($updateGoTo);
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

// begin Recordset
$query_rsMar = "SELECT * FROM marital_mar ORDER BY status_mar ASC";
$rsMar = $phaktconn->SelectLimit($query_rsMar) or die($phaktconn->ErrorMsg());
$totalRows_rsMar = $rsMar->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.37?>


<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method="POST" name="form1" action="<?php echo $editFormAction; ?>">
  <table align="center">
    <tr valign="baseline"> 
      <td nowrap align="right">Id_emp:</td>
      <td><?php echo $rsEmp->Fields('id_emp'); ?></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Firstname_emp:</td>
      <td><input type="text" name="firstname_emp" value="<?php echo $rsEmp->Fields('firstname_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Lastname_emp:</td>
      <td><input type="text" name="lastname_emp" value="<?php echo $rsEmp->Fields('lastname_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Address_emp:</td>
      <td><input type="text" name="address_emp" value="<?php echo $rsEmp->Fields('address_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Code_emp:</td>
      <td><input type="text" name="code_emp" value="<?php echo $rsEmp->Fields('code_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Email_emp:</td>
      <td><input type="text" name="email_emp" value="<?php echo $rsEmp->Fields('email_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Phone_emp:</td>
      <td><input type="text" name="phone_emp" value="<?php echo $rsEmp->Fields('phone_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Fax_emp:</td>
      <td><input type="text" name="fax_emp" value="<?php echo $rsEmp->Fields('fax_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Childreno_emp:</td>
      <td><input type="text" name="childreno_emp" value="<?php echo $rsEmp->Fields('childreno_emp'); ?>" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Marital_emp:</td>
      <td> <select name="marital_emp">
          <?php 
  while(!$rsMar->EOF){
?>
          <option value="<?php echo $rsMar->Fields('id_mar')?>" <?php if (!(strcmp($rsMar->Fields('id_mar'), $rsEmp->Fields('marital_emp')))) {echo "SELECTED";} ?>><?php echo $rsMar->Fields('status_mar')?></option>
          <?php
    $rsMar->MoveNext();
  }
  $rsMar->MoveFirst();
?>
        </select> </td>
    <tr> 
    <tr valign="baseline"> 
      <td nowrap align="right">&nbsp;</td>
      <td><input type="submit" value="Update Record"></td>
    </tr>
  </table>
  <input type="hidden" name="MM_update" value="form1">
  <input type="hidden" name="id_emp" value="<?php echo $rsEmp->Fields('id_emp'); ?>">
</form>
<p>&nbsp;</p>
  
</body>
</html>
<?php
$rsEmp->Close();

$rsMar->Close();
?>

