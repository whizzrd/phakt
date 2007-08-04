<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php
// *** Restrict Access To Page: Grant or deny access to this page
$KT_authorizedUsers=" 10";
$KT_authFailedURL="login.php";
$KT_grantAccess=0;
session_start();
if (isset($HTTP_SESSION_VARS["KT_Username"])) {
  if (false || !(isset($HTTP_SESSION_VARS["KT_userAuth"])) || $HTTP_SESSION_VARS["KT_userAuth"]=="" || strpos($KT_authorizedUsers, $HTTP_SESSION_VARS["KT_userAuth"])) {
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
$maxRows_rsUsers = 10;
$pageNum_rsUsers = 0;
if (isset($HTTP_GET_VARS['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $HTTP_GET_VARS['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;
$query_rsUsers = "SELECT * FROM users_usr ORDER BY username_usr ASC";
$rsUsers = $test->SelectLimit($query_rsUsers, $maxRows_rsUsers, $startRow_rsUsers) or die($test->ErrorMsg());
if (isset($HTTP_GET_VARS['totalRows_rsUsers'])) {
  $totalRows_rsUsers = $HTTP_GET_VARS['totalRows_rsUsers'];
} else {
  $all_rsUsers = $test->SelectLimit($query_rsUsers) or die($test->ErrorMsg());
  $totalRows_rsUsers = $all_rsUsers->RecordCount();
}
$totalPages_rsUsers = (int)(($totalRows_rsUsers-1)/$maxRows_rsUsers);
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.60?>
<?php
// rebuild the query string by replacing pageNum and totalRows with the new values
$queryString_rsUsers = KT_removeParam("&" . $HTTP_SERVER_VARS['QUERY_STRING'], "pageNum_rsUsers");
$queryString_rsUsers = KT_replaceParam($queryString_rsUsers, "totalRows_rsUsers", $totalRows_rsUsers);

//keep all parameters except id_usr
KT_keepParams('id_usr');
?>


<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?php if ($totalRows_rsUsers == 0) { // Show if recordset empty ?>
<p>The users table is empty.</p>
<?php } // Show if recordset empty ?>

<table width="100%" border="0">
  <?php
  while (!$rsUsers->EOF) {
?>
  <tr> 
    <td><?php echo $rsUsers->Fields('id_usr'); ?></td>
    <td><?php echo $rsUsers->Fields('username_usr'); ?></td>
    <td><?php echo $rsUsers->Fields('password_usr'); ?></td>
    <td><?php echo $rsUsers->Fields('level_usr'); ?></td>
    <td><A HREF="update.php?<?php echo $MM_keepNone . (($MM_keepNone!="")?"&":"") . "id_usr=" . urlencode($rsUsers->Fields('id_usr')) ?>">Edit</A></td>
    <td><A HREF="delete.php?<?php echo $MM_keepNone . (($MM_keepNone!="")?"&":"") . "id_usr=" . urlencode($rsUsers->Fields('id_usr')) ?>">Delete</A></td>
  </tr>
  <?php
    $rsUsers->MoveNext();
  }
?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><a href="insert.php">Add</a></td>
  </tr>
</table>

<table border="0" width="50%" align="center">
  <tr> 
    <td width="23%" align="center"><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], 0, $queryString_rsUsers); ?>">First</a> 
      <?php } // Show if not first page ?></td>
    <td width="31%" align="center"><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], max(0, $pageNum_rsUsers - 1), $queryString_rsUsers); ?>">Previous</a> 
      <?php } // Show if not first page ?></td>
    <td width="23%" align="center"><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], min($totalPages_rsUsers, $pageNum_rsUsers + 1), $queryString_rsUsers); ?>">Next</a> 
      <?php } // Show if not last page ?></td>
    <td width="23%" align="center"><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $HTTP_SERVER_VARS["PHP_SELF"], $totalPages_rsUsers, $queryString_rsUsers); ?>">Last</a> 
      <?php } // Show if not last page ?></td>
  </tr>
</table>
<p align="right"><a href="logout.php">Logout</a></p>
</body>
</html>
<?php
$rsUsers->Close();
?>

