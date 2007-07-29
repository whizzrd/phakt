<?php require_once('Connections/phaktconn.php'); ?>
<?php require_once('includes/functions.inc.php'); ?>
<?php
if ((isset($HTTP_GET_VARS['id_emp'])) && ($HTTP_GET_VARS['id_emp'] != "")) {
  $deleteSQL = sprintf("DELETE FROM employees_emp WHERE id_emp=%s",
                       GetSQLValueString($HTTP_GET_VARS['id_emp'], "int"));

  $Result1 = $phaktconn->Execute($deleteSQL) or die($phaktconn->ErrorMsg());

  $deleteGoTo = "list.php";
  if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $HTTP_SERVER_VARS['QUERY_STRING'];
  }
  KT_redir($deleteGoTo);
}
 //PHP ADODB document - made with PHAkt 2.0.37?>
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
</body>
</html>
