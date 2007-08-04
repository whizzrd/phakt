<?php require_once('../Connections/phaktconn.php'); ?><?php
// begin Recordset
$KTParam1__Recordset1 = '-1';
if (isset($HTTP_GET_VARS['recordID'])) {
  $KTParam1__Recordset1 = $HTTP_GET_VARS['recordID'];
}
$query_Recordset1 = sprintf("SELECT * FROM employees_emp WHERE id_emp = %s", $KTParam1__Recordset1);
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
<table border="1" align="center">
  <tr>
    <td>id_emp</td>
    <td><?php echo $Recordset1->Fields('id_emp')?></td>
  </tr>
  <tr>
    <td>firstname_emp</td>
    <td><?php echo $Recordset1->Fields('firstname_emp')?></td>
  </tr>
  <tr>
    <td>lastname_emp</td>
    <td><?php echo $Recordset1->Fields('lastname_emp')?></td>
  </tr>
  <tr>
    <td>address_emp</td>
    <td><?php echo $Recordset1->Fields('address_emp')?></td>
  </tr>
  <tr>
    <td>code_emp</td>
    <td><?php echo $Recordset1->Fields('code_emp')?></td>
  </tr>
  <tr>
    <td>email_emp</td>
    <td><?php echo $Recordset1->Fields('email_emp')?></td>
  </tr>
  <tr>
    <td>phone_emp</td>
    <td><?php echo $Recordset1->Fields('phone_emp')?></td>
  </tr>
  <tr>
    <td>fax_emp</td>
    <td><?php echo $Recordset1->Fields('fax_emp')?></td>
  </tr>
  <tr>
    <td>childreno_emp</td>
    <td><?php echo $Recordset1->Fields('childreno_emp')?></td>
  </tr>
  <tr>
    <td>marital_emp</td>
    <td><?php echo $Recordset1->Fields('marital_emp')?></td>
  </tr>
  
</table>


</body>
</html><?php
$Recordset1->Close();
?>
