<?php 
	# PHP ADODB document - made with PHAkt
	# FileName="Connection_php_adodb.htm"
	# Type="ADODB"
	# HTTP="true"
	# DBTYPE="mysql"
	
	$MM_phaktconn_HOSTNAME = "dbserver";
	$MM_phaktconn_DATABASE = "mysql:phakt";
	$MM_phaktconn_DBTYPE   = preg_replace("/:.*$/", "", $MM_phaktconn_DATABASE);
	$MM_phaktconn_DATABASE = preg_replace("/^.*:/", "", $MM_phaktconn_DATABASE);
	$MM_phaktconn_USERNAME = "root";
	$MM_phaktconn_PASSWORD = "pass";
	$MM_phaktconn_LOCALE = "Us";
	$MM_phaktconn_MSGLOCALE = "En";
	$MM_phaktconn_CTYPE = "P";
	$KT_locale = $MM_phaktconn_MSGLOCALE;
	$KT_dlocale = $MM_phaktconn_LOCALE;
	$KT_serverFormat = "%Y-%m-%d %H:%M:%S";
	$QUB_Caching = "false";
	
	switch (strtoupper ($MM_phaktconn_LOCALE)) {
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
				$KT_localFormat = "%Y-%m-%d %H:%M:%S";			
	}


	
	if (!defined('CONN_DIR')) define('CONN_DIR',dirname(__FILE__));
	require_once(CONN_DIR."/../adodb/adodb.inc.php");
	ADOLoadCode($MM_phaktconn_DBTYPE);
	$phaktconn=&ADONewConnection($MM_phaktconn_DBTYPE);

	if($MM_phaktconn_DBTYPE == "access" || $MM_phaktconn_DBTYPE == "odbc"){
		if($MM_phaktconn_CTYPE == "P"){
			$phaktconn->PConnect($MM_phaktconn_DATABASE, $MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD, 
			$MM_phaktconn_LOCALE);
		} else $phaktconn->Connect($MM_phaktconn_DATABASE, $MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD, 
			$MM_phaktconn_LOCALE);
	} else if($MM_phaktconn_DBTYPE == "ibase") {
		if($MM_phaktconn_CTYPE == "P"){
			$phaktconn->PConnect($MM_phaktconn_HOSTNAME.":".$MM_phaktconn_DATABASE,$MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD);
		} else $phaktconn->Connect($MM_phaktconn_HOSTNAME.":".$MM_phaktconn_DATABASE,$MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD);
	}else {
		if($MM_phaktconn_CTYPE == "P"){
			$phaktconn->PConnect($MM_phaktconn_HOSTNAME,$MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD,
   			$MM_phaktconn_DATABASE,$MM_phaktconn_LOCALE);
		} else $phaktconn->Connect($MM_phaktconn_HOSTNAME,$MM_phaktconn_USERNAME,$MM_phaktconn_PASSWORD,
   			$MM_phaktconn_DATABASE,$MM_phaktconn_LOCALE);
   }

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
				$$name = $HTTP_VARS[$name];
			}
		}
		return $HTTP_VARS;
	}
	
	if (!get_magic_quotes_gpc()) {
		$HTTP_GET_VARS = updateMagicQuotes($HTTP_GET_VARS);
		$HTTP_POST_VARS = updateMagicQuotes($HTTP_POST_VARS);
		$HTTP_COOKIE_VARS = updateMagicQuotes($HTTP_COOKIE_VARS);
	}


   
?>
