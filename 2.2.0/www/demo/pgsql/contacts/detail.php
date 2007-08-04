<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php
// begin Recordset
$KTParam1__Recordset1 = '-1';
if (isset($HTTP_GET_VARS['recordID'])) {
  $KTParam1__Recordset1 = $HTTP_GET_VARS['recordID'];
}
$colname__Recordset1 = '1';
if (isset($HTTP_SESSION_VARS['userId'])) {
  $colname__Recordset1 = $HTTP_SESSION_VARS['userId'];
}
$query_Recordset1 = sprintf("SELECT * FROM contacts_con WHERE id_con = %s AND  idusr_con = %s", $KTParam1__Recordset1,$colname__Recordset1);
$Recordset1 = $test->SelectLimit($query_Recordset1) or die($test->ErrorMsg());
$totalRows_Recordset1 = $Recordset1->RecordCount();
// end Recordset

//keep all parameters except idusr_con
KT_keepParams('idusr_con');
 //PHP ADODB document - made with PHAkt 2.0.60?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table border="1" align="center">
  <tr>
    <td>id_con</td>
    <td><?php echo $Recordset1->Fields('id_con')?></td>
  </tr>
  <tr>
    <td>idusr_con</td>
    <td><?php echo $Recordset1->Fields('idusr_con')?></td>
  </tr>
  <tr>
    <td>firstname_con</td>
    <td><?php echo $Recordset1->Fields('firstname_con')?></td>
  </tr>
  <tr>
    <td>lastname_con</td>
    <td><?php echo $Recordset1->Fields('lastname_con')?></td>
  </tr>
  <tr>
    <td>email_con</td>
    <td><?php echo $Recordset1->Fields('email_con')?></td>
  </tr>
  <tr>
    <td>birthdate_con</td>
    <td><?php echo $Recordset1->Fields('birthdate_con')?></td>
  </tr>
  
</table>


<p>&nbsp;</p><table align="CENTER" width="50%" border="0">
  <tr>
    <td><a href="index.php">Back to main</a></td>
    <td><A HREF="logout.php?<?php echo $MM_keepNone . (($MM_keepNone!="")?"&":"") . "idusr_con=" . urlencode($Recordset1->Fields('id_con')) ?>">Logout</A></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html><?php
$Recordset1->Close();
?>
