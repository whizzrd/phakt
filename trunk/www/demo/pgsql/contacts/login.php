<?php require_once('../Connections/test.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php
// *** Start the session
session_start();
// *** Validate request to log in to this site.
$KT_LoginAction = $HTTP_SERVER_VARS["REQUEST_URI"];
if (isset($HTTP_POST_VARS["username"])) {
  $KT_valUsername = $HTTP_POST_VARS['username'];
  $KT_fldUserAuthorization = "level_usr";
  $KT_redirectLoginSuccess = "index.php";
  $KT_redirectLoginFailed = "login.php?failed=1";
  $KT_rsUser_Source = "SELECT username_usr, password_usr ";
  if ($KT_fldUserAuthorization != "") $KT_rsUser_Source .= "," . $KT_fldUserAuthorization;
  $KT_rsUser_Source .= " FROM users_usr WHERE username_usr='" . $KT_valUsername . "' AND password_usr='" . $HTTP_POST_VARS['password'] . "'";
  $KT_rsUser=$test->Execute($KT_rsUser_Source) or DIE($test->ErrorMsg());
  if (!$KT_rsUser->EOF) {
    // username and password match - this is a valid user
    $KT_Username=$KT_valUsername;
    session_unregister("KT_Username");
    session_register("KT_Username");
    if ($KT_fldUserAuthorization != "") {
      $KT_userAuth=$KT_rsUser->Fields($KT_fldUserAuthorization);
    } else {
      $KT_userAuth="";
    }
    session_unregister("KT_userAuth");
    session_register("KT_userAuth");
    if (isset($HTTP_GET_VARS['accessdenied']) && false) {
      $KT_redirectLoginSuccess = $HTTP_GET_VARS['accessdenied'];
    }
    $KT_rsUser->Close();
    session_unregister("KT_login_failed");
    session_register("KT_login_failed");
    $KT_login_failed = false;
    // Add code here if you want to do something if login succeded

$query = "SELECT * from users_usr where username_usr like '".$KT_Username."'";
$KT_rsUser=$test->Execute($query) or DIE($test->ErrorMsg());
$userId = $KT_rsUser->Fields('id_usr');
session_register('userId');

KT_redir($KT_redirectLoginSuccess);
  }
  $KT_rsUser->Close();
  $KT_login_failed = true;
  session_unregister("KT_login_failed");
  session_register("KT_login_failed");
  // Add code here if you want to do something if login fails

KT_redir($KT_redirectLoginFailed);
}
 //PHP ADODB document - made with PHAkt 2.0.60?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?php
	if (isset($failed) && $failed == 1) {
		echo "Your Username and password are incorrect. Please Re Login";
	}
?>
<p><a href="../index.php">Back</a></p>
<form name="form1" method="POST" action="<?php echo $KT_LoginAction?>">
  
  <p>Username: 
    <input name="username" type="text" maxlength="8" id="username">
  </p>
  <p> Password: 
    <input name="password" type="text" maxlength="8" id="password">
  </p>
  <p>
    <input type="submit" name="Submit" value="Submit">
  </p>
</form>
</body>
</html>
