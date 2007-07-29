<?php require_once('Connections/phaktconn.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
<?php
// begin Recordset
$maxRows_rsEmployees = 10;
$pageNum_rsEmployees = 0;
if (isset($HTTP_GET_VARS['pageNum_rsEmployees'])) {
  $pageNum_rsEmployees = $HTTP_GET_VARS['pageNum_rsEmployees'];
}
$startRow_rsEmployees = $pageNum_rsEmployees * $maxRows_rsEmployees;
$MMColParam1__rsEmployees = ''%'';
if (isset($HTTP_POST_VARS["address"])) {
  $MMColParam1__rsEmployees = $HTTP_POST_VARS["address"];
}
$MMColParam2__rsEmployees = ''%'';
if (isset($HTTP_POST_VARS["code"])) {
  $MMColParam2__rsEmployees = $HTTP_POST_VARS["code"];
}
$MMColParam3__rsEmployees = ''%'';
if (isset($HTTP_POST_VARS["name"])) {
  $MMColParam3__rsEmployees = $HTTP_POST_VARS["name"];
}
$query_rsEmployees = sprintf("SELECT * FROM employees_emp WHERE firstname_emp LIKE  '%%%s%%' and code_emp like '%%%s%%' and address_emp like '%%%s%%'", $MMColParam1__rsEmployees,$MMColParam2__rsEmployees,$MMColParam3__rsEmployees);
$rsEmployees = $phaktconn->SelectLimit($query_rsEmployees, $maxRows_rsEmployees, $startRow_rsEmployees) or die($phaktconn->ErrorMsg());
if (isset($HTTP_GET_VARS['totalRows_rsEmployees'])) {
  $totalRows_rsEmployees = $HTTP_GET_VARS['totalRows_rsEmployees'];
} else {
  $all_rsEmployees = $phaktconn->SelectLimit($query_rsEmployees) or die($phaktconn->ErrorMsg());
  $totalRows_rsEmployees = $all_rsEmployees->RecordCount();
}
$totalPages_rsEmployees = (int)(($totalRows_rsEmployees-1)/$maxRows_rsEmployees);
// end Recordset

// rebuild the query string by replacing pageNum and totalRows with the new values
$queryString_rsEmployees = KT_removeParam("&" . $HTTP_SERVER_VARS['QUERY_STRING'], "pageNum_rsEmployees");
$queryString_rsEmployees = KT_replaceParam($queryString_rsEmployees, "totalRows_rsEmployees", $totalRows_rsEmployees);

//keep all parameters except id_emp
KT_keepParams('id_emp');
 //PHP ADODB document - made with PHAkt 2.0.37?>



<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="100%" border="0">
  <tr> 
    <td>ID</td>
    <td>First Name</td>
    <td>Last Name</td>
    <td>Address</td>
    <td>Code</td>
    <td>Email</td>
    <td>Phone</td>
    <td>Fax</td>
    <td>Childno</td>
    <td>Marital</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <?php
  while (!$rsEmployees->EOF) {
?>
  <tr> 
    <td><A HREF="detail.php?<?php echo $MM_keepBoth . (($MM_keepBoth!="")?"&":"") . "id_emp=" . urlencode($rsEmployees->Fields('id_emp')) ?>"><?php echo $rsEmployees->Fields('id_emp'); ?></A></td>
    <td><?php echo $rsEmployees->Fields('firstname_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('lastname_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('address_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('code_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('email_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('phone_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('fax_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('childreno_emp'); ?></td>
    <td><?php echo $rsEmployees->Fields('marital_emp'); ?></td>
    <td><A HREF="update.php?<?php echo $MM_keepBoth . (($MM_keepBoth!="")?"&":"") . "id_emp=" . urlencode($rsEmployees->Fields('id_emp')) ?>">Update</A></td>
    <td><A HREF="delete.php?<?php echo $MM_keepBoth . (($MM_keepBoth!="")?"&":"") . "id_emp=" . urlencode($rsEmployees->Fields('id_emp')) ?>">Delete</A></td>
  </tr>
  <?php
    $rsEmployees->MoveNext();
  }
?>
</table>
<p>&nbsp;
  <?php if ($pageNum_rsEmployees > 0) { // Show if not first page ?>
  <a href="<?php printf("%s?pageNum_rsEmployees=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], max(0, $pageNum_rsEmployees - 1), $queryString_rsEmployees); ?>">Previous</a> 
  <?php } // Show if not first page ?>
  <?php if ($pageNum_rsEmployees < $totalPages_rsEmployees) { // Show if not last page ?>
  <a href="<?php printf("%s?pageNum_rsEmployees=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], min($totalPages_rsEmployees, $pageNum_rsEmployees + 1), $queryString_rsEmployees); ?>">Next</a> 
  <?php } // Show if not last page ?> 
</p>
</body>
</html>
<?php
$rsEmployees->Close();
?>

