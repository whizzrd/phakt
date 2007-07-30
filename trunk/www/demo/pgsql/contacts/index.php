<?php require_once('../Connections/test.php'); ?>
<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
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

// begin Recordset
$maxRows_rsContacts = 5;
$pageNum_rsContacts = 0;
if (isset($HTTP_GET_VARS['pageNum_rsContacts'])) {
  $pageNum_rsContacts = $HTTP_GET_VARS['pageNum_rsContacts'];
}
$startRow_rsContacts = $pageNum_rsContacts * $maxRows_rsContacts;
$colname__rsContacts = '1';
if (isset($HTTP_SESSION_VARS['userId'])) {
  $colname__rsContacts = $HTTP_SESSION_VARS['userId'];
}
$query_rsContacts = sprintf("SELECT * FROM contacts_con WHERE idusr_con = %s ORDER BY id_con ASC", $colname__rsContacts);
$rsContacts = $test->SelectLimit($query_rsContacts, $maxRows_rsContacts, $startRow_rsContacts) or die($test->ErrorMsg());
if (isset($HTTP_GET_VARS['totalRows_rsContacts'])) {
  $totalRows_rsContacts = $HTTP_GET_VARS['totalRows_rsContacts'];
} else {
  $all_rsContacts = $test->SelectLimit($query_rsContacts) or die($test->ErrorMsg());
  $totalRows_rsContacts = $all_rsContacts->RecordCount();
}
$totalPages_rsContacts = (int)(($totalRows_rsContacts-1)/$maxRows_rsContacts);
// end Recordset

//PHP ADODB document - made with PHAkt 2.0.60

// rebuild the query string by replacing pageNum and totalRows with the new values
$queryString_rsContacts = KT_removeParam("&" . $HTTP_SERVER_VARS['QUERY_STRING'], "pageNum_rsContacts");
$queryString_rsContacts = KT_replaceParam($queryString_rsContacts, "totalRows_rsContacts", $totalRows_rsContacts);

//keep all parameters except recordID
KT_keepParams('recordID');

//keep all parameters except idusr_con
KT_keepParams('idusr_con');
 //PHP ADODB document - made with PHAkt 2.0.60?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table border="1" align="center" width="70%">
  <tr> 
    <th>ID</th>
    <th>Firstname</th>
    <th>Lastname</th>
    <th colspan="2">Action</th>
  </tr>
  <?php
    while (!$rsContacts->EOF) {
  ?>
  <tr> 
    <td><a href="detail.php?<?php echo $MM_keepURL . (($MM_keepURL!="")?"&":"") . "recordID=" . urlencode($rsContacts->Fields('id_con')) ?>"><?php echo $rsContacts->Fields('id_con')?> </a></td>
    <td><?php echo $rsContacts->Fields('firstname_con')?> </td>
    <td><?php echo $rsContacts->Fields('lastname_con')?> </td>
    <td><a href="update.php?id_con=<?php echo $rsContacts->Fields('id_con'); ?>">Edit</a></td>
    <td><a href="delete.php?id_con=<?php echo $rsContacts->Fields('id_con'); ?>">Delete</a></td>
  </tr>
  <?php
    $rsContacts->MoveNext();
  }
?>
</table>
<br>
<table border="0" width="50%" align="center">
  <tr> 
    <td width="23%" align="center"> <?php if ($pageNum_rsContacts > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsContacts=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], 0, $queryString_rsContacts); ?>">First</a> 
      <?php } // Show if not first page ?> </td>
    <td width="31%" align="center"> <?php if ($pageNum_rsContacts > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsContacts=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], max(0, $pageNum_rsContacts - 1), $queryString_rsContacts); ?>">Previous</a> 
      <?php } // Show if not first page ?> </td>
    <td width="23%" align="center"> <?php if ($pageNum_rsContacts < $totalPages_rsContacts) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsContacts=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], min($totalPages_rsContacts, $pageNum_rsContacts + 1), $queryString_rsContacts); ?>">Next</a> 
      <?php } // Show if not last page ?> </td>
    <td width="23%" align="center"> <?php if ($pageNum_rsContacts < $totalPages_rsContacts) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsContacts=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], $totalPages_rsContacts, $queryString_rsContacts); ?>">Last</a> 
      <?php } // Show if not last page ?> </td>
  </tr>
</table>
<p align="center">Records <?php echo (min($startRow_rsContacts + 1, $totalRows_rsContacts)) ?>to <?php echo min($startRow_rsContacts + $maxRows_rsContacts, $totalRows_rsContacts) ?> of <?php echo $totalRows_rsContacts ?></p>
<p>&nbsp;</p>
<table align="CENTER" width="50%" border="0">
  <tr>
    <td><a href="insert.php">Add new contact</a></td>
    <td><a href="logout.php">Logout</a></td>
  </tr>
</table>
</body>
</html>
<?php
$rsContacts->Close();

$rsContacts->Close();
?>

