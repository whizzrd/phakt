<?php 
	# PHP ADODB document - made with PHAkt
	# FileName="Connection_php_adodb.htm"
	# Type="ADODB"
	# HTTP="true"
	# DBTYPE="postgres7"
	
	$MM_test_HOSTNAME = "your.database.server";
	$MM_test_DATABASE = "postgres7:phakt_mx";
	$MM_test_DBTYPE   = preg_replace("/:.*$/", "", $MM_test_DATABASE);
	$MM_test_DATABASE = preg_replace("/^.*?:/", "", $MM_test_DATABASE);
	$MM_test_USERNAME = "username";
	$MM_test_PASSWORD = "password";
	$MM_test_LOCALE = "Us";
	$MM_test_MSGLOCALE = "En";
	$MM_test_CTYPE = "P";
	$KT_locale = $MM_test_MSGLOCALE;
	$KT_dlocale = $MM_test_LOCALE;
	$KT_serverFormat = "%Y-%m-%d %H:%M:%S";
	$QUB_Caching = "false";
	
	switch (strtoupper ($MM_test_LOCALE)) {
		case 'EN':
				$KT_localFormat = "%d-%m-%Y %H:%M:%S";
		break;
		case 'EUS':
				$KT_localFormat = "%m-%d-%Y %H:%M:%S";
		break;
		case 'FR':
				$KT_localFormat = "%d-%m-%Y %H:%M:%S";
		break;
		case 'RO':
				$KT_localFormat = "%d-%m-%Y %H:%M:%S";
		break;
		case 'IT':
				$KT_localFormat = "%d-%m-%Y %H:%M:%S";
		break;
		case 'GE':
				$KT_localFormat = "%d-%m-%Y %H:%M:%S";
		break;
		case 'US':
				$KT_localFormat = "%Y-%m-%d %H:%M:%S";
		break;
		default :
				$KT_localFormat = "none";			
	}


	
	if (!defined('CONN_DIR')) define('CONN_DIR',dirname(__FILE__));
	require_once(CONN_DIR."/../adodb/adodb.inc.php");
	ADOLoadCode($MM_test_DBTYPE);
	$test=&ADONewConnection($MM_test_DBTYPE);

	if($MM_test_DBTYPE == "access" || $MM_test_DBTYPE == "odbc"){
		if($MM_test_CTYPE == "P"){
			$test->PConnect($MM_test_DATABASE, $MM_test_USERNAME,$MM_test_PASSWORD, 
			$MM_test_LOCALE);
		} else $test->Connect($MM_test_DATABASE, $MM_test_USERNAME,$MM_test_PASSWORD, 
			$MM_test_LOCALE);
	} else if (($MM_test_DBTYPE == "ibase") or ($MM_test_DBTYPE == "firebird")) {
		if($MM_test_CTYPE == "P"){
			$test->PConnect($MM_test_HOSTNAME.":".$MM_test_DATABASE,$MM_test_USERNAME,$MM_test_PASSWORD);
		} else $test->Connect($MM_test_HOSTNAME.":".$MM_test_DATABASE,$MM_test_USERNAME,$MM_test_PASSWORD);
	}else {
		if($MM_test_CTYPE == "P"){
			$test->PConnect($MM_test_HOSTNAME,$MM_test_USERNAME,$MM_test_PASSWORD,
   			$MM_test_DATABASE,$MM_test_LOCALE);
		} else $test->Connect($MM_test_HOSTNAME,$MM_test_USERNAME,$MM_test_PASSWORD,
   			$MM_test_DATABASE,$MM_test_LOCALE);
   }

	if (!function_exists("updateMagicQuotes")) {
		function updateMagicQuotes($HTTP_VARS){
			if (is_array($HTTP_VARS)) {
				foreach ($HTTP_VARS as $name=>$value) {
					if (!is_array($value)) {
						$HTTP_VARS[$name] = addslashes($value);
					} else {
						foreach ($value as $name1=>$value1) {
							if (!is_array($value1)) {
								$HTTP_VARS[$name1][$value1] = addslashes($value1);
							}
						}
						
					}
					global $$name;
					$$name = &$HTTP_VARS[$name];
				}
			}
			return $HTTP_VARS;
		}
		
		if (!get_magic_quotes_gpc()) {
			$HTTP_GET_VARS = updateMagicQuotes($HTTP_GET_VARS);
			$HTTP_POST_VARS = updateMagicQuotes($HTTP_POST_VARS);
			$HTTP_COOKIE_VARS = updateMagicQuotes($HTTP_COOKIE_VARS);
		}
	}
	if (!isset($HTTP_SERVER_VARS['REQUEST_URI'])) {
		$HTTP_SERVER_VARS['REQUEST_URI'] = $PHP_SELF;
	}
?>
