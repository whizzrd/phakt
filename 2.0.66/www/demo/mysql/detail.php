<?php require_once('Connections/phaktconn.php'); ?>
<?php
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

