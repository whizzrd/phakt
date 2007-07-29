<?php require_once('Connections/phaktconn.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
<?php
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
$query_Recordset1 = "SELECT * FROM marital_mar ORDER BY status_mar ASC";
$Recordset1 = $phaktconn->SelectLimit($query_Recordset1) or die($phaktconn->ErrorMsg());
$totalRows_Recordset1 = $Recordset1->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.37?>


<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
  <table align="center">
    <tr valign="baseline"> 
      <td nowrap align="right">Firstname_emp:</td>
      <td><input type="text" name="firstname_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Lastname_emp:</td>
      <td><input type="text" name="lastname_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Address_emp:</td>
      <td><input type="text" name="address_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Code_emp:</td>
      <td><input type="text" name="code_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Email_emp:</td>
      <td><input type="text" name="email_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Phone_emp:</td>
      <td><input type="text" name="phone_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Fax_emp:</td>
      <td><input type="text" name="fax_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Childreno_emp:</td>
      <td><input type="text" name="childreno_emp" value="" size="32"></td>
    </tr>
    <tr valign="baseline"> 
      <td nowrap align="right">Marital_emp:</td>
      <td> <select name="marital_emp">
          <?php 
  while(!$Recordset1->EOF){
?>
          <option value="<?php echo $Recordset1->Fields('id_mar')?>" ><?php echo $Recordset1->Fields('status_mar')?></option>
          <?php
    $Recordset1->MoveNext();
  }
  $Recordset1->MoveFirst();
?>
        </select> </td>
    <tr> 
    <tr valign="baseline"> 
      <td nowrap align="right">&nbsp;</td>
      <td><input type="submit" value="Insert Record"></td>
    </tr>
  </table>
  <input type="hidden" name="MM_insert" value="form1">
</form>
<p>&nbsp;</p>
  
</body>
</html>
<?php
$Recordset1->Close();
?>

