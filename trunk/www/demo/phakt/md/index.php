<?php require_once('../Connections/phaktconn.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php
// begin Recordset
$maxRows_Recordset1 = 10;
$pageNum_Recordset1 = 0;
if (isset($HTTP_GET_VARS['pageNum_Recordset1'])) {
  $pageNum_Recordset1 = $HTTP_GET_VARS['pageNum_Recordset1'];
}
$startRow_Recordset1 = $pageNum_Recordset1 * $maxRows_Recordset1;
$query_Recordset1 = "SELECT * FROM employees_emp";
$Recordset1 = $phaktconn->SelectLimit($query_Recordset1, $maxRows_Recordset1, $startRow_Recordset1) or die($phaktconn->ErrorMsg());
if (isset($HTTP_GET_VARS['totalRows_Recordset1'])) {
  $totalRows_Recordset1 = $HTTP_GET_VARS['totalRows_Recordset1'];
} else {
  $all_Recordset1 = $phaktconn->SelectLimit($query_Recordset1) or die($phaktconn->ErrorMsg());
  $totalRows_Recordset1 = $all_Recordset1->RecordCount();
}
$totalPages_Recordset1 = (int)(($totalRows_Recordset1-1)/$maxRows_Recordset1);
// end Recordset

// rebuild the query string by replacing pageNum and totalRows with the new values
$queryString_Recordset1 = KT_removeParam("&" . $HTTP_SERVER_VARS['QUERY_STRING'], "pageNum_Recordset1");
$queryString_Recordset1 = KT_replaceParam($queryString_Recordset1, "totalRows_Recordset1", $totalRows_Recordset1);

//keep all parameters except recordID
KT_keepParams('recordID');
 //PHP ADODB document - made with PHAkt 2.0.37?>


<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<table border="1" align="center">
  <tr> 
    <th>id_emp</th>
    <th>firstname_emp</th>
    <th>lastname_emp</th>
    <th>address_emp</th>
    <th>code_emp</th>
    <th>email_emp</th>
    <th>phone_emp</th>
    <th>fax_emp</th>
    <th>childreno_emp</th>
    <th>marital_emp</th>
  </tr>
  <?php
    while (!$Recordset1->EOF) {
  ?>
  <tr> 
    <td><a href="detail.php?<?php echo $MM_keepURL . (($MM_keepURL!="")?"&":"") . "recordID=" . urlencode($Recordset1->Fields('id_emp')) ?>"><?php echo $Recordset1->Fields('id_emp')?> </a></td>
    <td><?php echo $Recordset1->Fields('firstname_emp')?> </td>
    <td><?php echo $Recordset1->Fields('lastname_emp')?> </td>
    <td><?php echo $Recordset1->Fields('address_emp')?> </td>
    <td><?php echo $Recordset1->Fields('code_emp')?> </td>
    <td><?php echo $Recordset1->Fields('email_emp')?> </td>
    <td><?php echo $Recordset1->Fields('phone_emp')?> </td>
    <td><?php echo $Recordset1->Fields('fax_emp')?> </td>
    <td><?php echo $Recordset1->Fields('childreno_emp')?> </td>
    <td><?php echo $Recordset1->Fields('marital_emp')?> </td>
  </tr>
  <?php
    $Recordset1->MoveNext();
  }
?>
</table>
<br>
<table border="0" width="50%" align="center">
  <tr> 
    <td width="23%" align="center"> <?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], 0, $queryString_Recordset1); ?>">First</a> 
      <?php } // Show if not first page ?> </td>
    <td width="31%" align="center"> <?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], max(0, $pageNum_Recordset1 - 1), $queryString_Recordset1); ?>">Previous</a> 
      <?php } // Show if not first page ?> </td>
    <td width="23%" align="center"> <?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], min($totalPages_Recordset1, $pageNum_Recordset1 + 1), $queryString_Recordset1); ?>">Next</a> 
      <?php } // Show if not last page ?> </td>
    <td width="23%" align="center"> <?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], $totalPages_Recordset1, $queryString_Recordset1); ?>">Last</a> 
      <?php } // Show if not last page ?> </td>
  </tr>
</table>
Records <?php echo (min($startRow_Recordset1 + 1, $totalRows_Recordset1)) ?> to <?php echo min($startRow_Recordset1 + $maxRows_Recordset1, $totalRows_Recordset1) ?> of <?php echo $totalRows_Recordset1 ?> 
</body>
</html>
<?php
$Recordset1->Close();
?>

