<?php require_once('Connections/phaktconn.php'); ?>
<?php
// begin Recordset
$query_rsDyn = "SELECT * FROM marital_mar";
$rsDyn = $phaktconn->SelectLimit($query_rsDyn) or die($phaktconn->ErrorMsg());
$totalRows_rsDyn = $rsDyn->RecordCount();
// end Recordset
 //PHP ADODB document - made with PHAkt 2.0.37?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <p> 
    <input <?php if (!(strcmp($rsDyn->Fields('radio_mar'),"0"))) {echo "CHECKED";} ?>   type="radio" name="radio" value="0">
    <input <?php if (!(strcmp($rsDyn->Fields('radio_mar'),"1"))) {echo "CHECKED";} ?>   type="radio" name="radio" value="1">
  </p>
  <p>
    <input <?php if (!(strcmp($rsDyn->Fields('checkbox_mar'),1))) {echo "checked";} ?> type="checkbox" name="checkbox" value="checkbox">
  </p>
</form>
</body>
</html>
<?php
$rsDyn->Close();
?>

