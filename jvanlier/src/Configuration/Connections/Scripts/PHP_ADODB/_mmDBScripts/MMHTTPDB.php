<?php
$debug_to_file = false;
// security check
// $allowed_ips = array("192.168.0.75", "192.168.0.1");
$allowed_ips = array();
if (count($allowed_ips)>0 && isset($HTTP_SERVER_VARS["REMOTE_ADDR"])){
		   if (!in_array($HTTP_SERVER_VARS["REMOTE_ADDR"], $allowed_ips)) {
				 	 $error_string = "<ERRORS><ERROR><DESCRIPTION>You are not allowed to use this connection gateway. You should change the Connection parameters to add your IP or the Proxy server IP to the list.</DESCRIPTION></ERROR></ERRORS>";
				   die($error_string);
			 }
}
if(false && extension_loaded("mbstring"))
{
	$acceptCharsetHeader = "Accept-Charset: " . mb_internal_encoding();
	header( $acceptCharsetHeader );
	$head = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=" . mb_http_output() . "></head>";
	echo( $head );
}else{
	$head = "<html><head></head>";
	echo( $head );
}

if ($debug_to_file){
		@ini_set('display_errors', 1);
		@error_reporting(E_ALL);
		$f = @fopen("log.txt", "a");
		@fwrite($f, "\n--------------------------------\n");
		foreach($HTTP_POST_VARS as $key=>$value) {
				@fwrite($f, "\$HTTP_POST_VARS[\"".$key."\"] = \"".$value."\";\n");
		}
		if (isset($HTTP_POST_VARS['opCode']) && $HTTP_POST_VARS['opCode'] == "IsOpen" ){
			@fwrite($f, "\nPHP-Version: ".phpversion()."\n");
			@fwrite($f, "PHP-OS: ".PHP_OS."\n");
			@fwrite($f, "PHP-SAPI-NAME: ".php_sapi_name()."\n");
			@fwrite($f, "PHP-Extensions: ".var_export(get_loaded_extensions(),true)."\n");
			@fwrite($f, "PHP-lib-expat: ".(function_exists("utf8_decode")?"Found":"NOT FOUND")."\n");
		}
}


if (isset($HTTP_POST_VARS['Type']) && $HTTP_POST_VARS['Type'] == "ADODB") {
	require("ADODB.php");
	$oConn = new ADODBConnection($HTTP_POST_VARS['ConnectionString'], @$HTTP_POST_VARS['Timeout'], @$HTTP_POST_VARS['Host'], @$HTTP_POST_VARS['Database'], @$HTTP_POST_VARS['UserName'], @$HTTP_POST_VARS['Password']);
}else{
	$error ="<ERRORS><ERROR><DESCRIPTION>";
	$error.="The files from the _mmServerScripts folder are for the server model PHP-ADODB. You try to connect to a database using a a different server model ".@$HTTP_POST_VARS['Type'].".\n\nPlease remove this folder outside the Dreamweaver environment on both local and testing machines and try again.";
	$error.="</DESCRIPTION></ERROR></ERRORS>";
	if ($debug_to_file){
		@fwrite($f,"Error: ".$error);
	}
	echo $error;
	return;
}

if ($debug_to_file){
		@fwrite($f, "Connection Object: ".((isset($oConn) && $oConn)?"ADODB Connection":"Failed")."\n");
}

// Process opCode
if (isset($oConn) && $oConn) {
	ob_start();
	$answer = $oConn->Open();
	$errors[] = ob_get_contents();
	ob_end_clean();
	if ($oConn->isOpen && isset($HTTP_POST_VARS['opCode']) && $HTTP_POST_VARS['opCode'] == "IsOpen" && $answer == true) {
			$answer = $oConn->TestOpen();
	} elseif ($oConn->connectionId && $oConn->isOpen) {
				ob_start();
				switch ($HTTP_POST_VARS['opCode']===0 ? "":$HTTP_POST_VARS['opCode']){
							case "GetTables": $answer = $oConn->GetTables();
											break;
							case "GetColsOfTable": $answer = $oConn->GetColumnsOfTable($HTTP_POST_VARS['TableName']);
											break;
							case "ExecuteSQL": $answer = $oConn->ExecuteSQL($HTTP_POST_VARS['SQL'], $HTTP_POST_VARS['MaxRows']);
											break;
							case "GetODBCDSNs": $answer = $oConn->GetDatabaseList();
											break;
							case "SupportsProcedure": $answer = $oConn->SupportsProcedure();
											break;
							case "GetProviderTypes": $answer = $oConn->GetProviderTypes();
											break;
							case "GetViews": $answer = $oConn->GetViews();
											break;
							case "GetProcedures": $answer = $oConn->GetProcedures();
											break;
							case "GetParametersOfProcedure": $answer = $oConn->GetParametersOfProcedure($HTTP_POST_VARS['ProcName'], @$HTTP_POST_VARS['Schema'], @$HTTP_POST_VARS['Catalog']);
											break;
							case "ReturnsResultset": $answer = $oConn->ReturnsResultSet($HTTP_POST_VARS['RRProcName']);
											break;
							case "ExecuteSP": $answer = $oConn->ExecuteSP($HTTP_POST_VARS['ExecProcName'], 0, $HTTP_POST_VARS['ExecProcParameters']);
											break;
							case "GetKeysOfTable": $answer = $oConn->GetPrimaryKeysOfTable($HTTP_POST_VARS['TableName']);
											break;
							default: $answer = "<ERRORS>\n<ERROR><DESCRIPTION>The '".$HTTP_POST_VARS['opCode']."' command is not supported by the PHP-ADOdb.</DESCRIPTION></ERROR>\n</ERRORS>";
											break;
				}
				$errors[] = ob_get_contents();
				ob_end_clean();
	}
	if ($debug_to_file && $errors){
				@fwrite($f, "\nPHP Errors:\n".var_export($errors,true)."\n");
	}
	echo @$answer;

	if ($debug_to_file){
	 //		The following lines are for debug purpose only
					@fwrite($f, "\nAnswer From The Database:\n\n\t".@$answer."\n\n\n");
					@fclose($f);
	}
	$oConn->Close();
}else{
	if ($debug_to_file){
	 //		The following lines are for debug purpose only
					@fwrite($f, "\n oConn was not initialized properly for unknown reason;\n\n\n");
					@fclose($f);
	}
	echo "<ERRORS>\n<ERROR><DESCRIPTION>The Connection was not initialized properly for unknown reason</DESCRIPTION></ERROR>\n</ERRORS>";
}

echo( "</html>" );
?>
