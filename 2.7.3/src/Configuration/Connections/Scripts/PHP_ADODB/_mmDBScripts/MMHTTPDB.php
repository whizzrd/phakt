<?php
$debug_to_file = false;
if(false && extension_loaded("mbstring"))
{
	$acceptCharsetHeader = "Accept-Charset: " . mb_internal_encoding();
	header( $acceptCharsetHeader );
	$head = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=" . mb_http_output() . "></head>";
	echo( $head );
}

if ($debug_to_file){
$f = fopen("log.txt", "a");
fwrite($f, "\n--------------------------------\n");
while (list($key, $value)=each($HTTP_POST_VARS)) {
				@fwrite($f, "\$HTTP_POST_VARS[\"".$key."\"] = \"".$value."\";\n");
		}
		@fwrite($f, "\nPHP-Version: ".phpversion()."\n");
		@fwrite($f, "PHP-OS: ".PHP_OS."\n");
		@fwrite($f, "PHP-SAPI-NAME: ".php_sapi_name()."\n");
		@fwrite($f, "PHP-Extensions: ".var_export(get_loaded_extensions(),true)."\n");
		@fwrite($f, "PHP-lib-expat: ".(function_exists("utf8_decode")?"Found":"NOT FOUND")."\n");
}


if (isset($HTTP_POST_VARS['Type']) && $HTTP_POST_VARS['Type'] == "ADODB") {
	require("ADODB.php");
	$oConn = new ADODBConnection($HTTP_POST_VARS['ConnectionString'], $HTTP_POST_VARS['Timeout'], $HTTP_POST_VARS['Host'], $HTTP_POST_VARS['Database'], $HTTP_POST_VARS['UserName'], $HTTP_POST_VARS['Password']);
}

// Process opCode
if (isset($oConn) && $oConn) {
	$oConn->Open();

	if (isset($HTTP_POST_VARS['opCode']) && $HTTP_POST_VARS['opCode'] == "IsOpen") {
		$answer = $oConn->TestOpen();
	} elseif ($oConn->connectionId && $oConn->isOpen) {
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
							case "GetParametersOfProcedure": $answer = $oConn->GetParametersOfProcedure($HTTP_POST_VARS['ProcName']);
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
	}
	echo $answer;

	if ($debug_to_file){
	 //		The following lines are for debug purpose only
					@fwrite($f, "\nAnswer From The Database:\n\n\t".var_export($answer,true)."\n\n\n");
					@fclose($f);
	}
	$oConn->Close();
}

echo( "</html>" );
?>