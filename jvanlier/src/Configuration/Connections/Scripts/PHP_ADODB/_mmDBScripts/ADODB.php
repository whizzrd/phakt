<?php
function KT_ErrorHandler($errno, $errstr, $errfile, $errline) { 
	global $HTTP_SERVER_VARS, $HTTP_SESSION_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS, $f;
	$errortype = array ( 
		1   =>  "Error", 
		2   =>  "Warning", 
		4   =>  "Parsing Error", 
		8   =>  "Notice", 
		16  =>  "Core Error", 
		32  =>  "Core Warning", 
		64  =>  "Compile Error", 
		128 =>  "Compile Warning", 
		256 =>  "User Error", 
		512 =>  "User Warning", 
		1024=>  "User Notice" 
	); 
	$str = $str = sprintf("[%s]\n%s:\t%s\nFile:\t\t'%s'\nLine:\t\t%s\n\n", date("d-m-Y H:i:s"),@$errortype[@$errno], @$errstr,@$errfile,@$errline);
	
	if (error_reporting() != 0) {
			@fwrite($f, $str);
			if (@$errno == 2){
				$error = "<ERRORS>\n";
				$error .= "<ERROR><DESCRIPTION>An Warning Type error appeared. The error is logged into the log file.</DESCRIPTION></ERROR>\n";
				$error .= "</ERRORS>\n";
				echo $error;
			}
	}
}
if (!isset($debug_to_file)){
		$debug_to_file = false; 
}
if ($debug_to_file){
		$old_error_handler = set_error_handler("KT_ErrorHandler");
}

if (file_exists("../adodb/adodb.inc.php")){
	require_once("../adodb/adodb.inc.php");
}else{
	$error = "<ERRORS>\n";
	$error .= "<ERROR><DESCRIPTION>Please check the existence of ADOdb library on local and remote server.</DESCRIPTION></ERROR>\n";
	$error .= "</ERRORS>\n";
	if ($debug_to_file){
			@fwrite($f, "\n".$error."\n");
			@fclose($f);
	}
	echo $error;
	die();
}

class ADODBConnection {
	var $isOpen;
	var $hostname;
	var $database;
	var $username;
	var $password;
	var $timeout;
	var $connectionId;
	var $connection;
	var $dbtype;

	function ADODBConnection ($ConnectionString, $Timeout, $Host, $DB, $UID, $Pwd) {
		$this->isOpen = false;
		$this->timeout = $Timeout;
		
		if ($DB) {
			$DBType = preg_replace("/:.*$/", "", $DB);
			$DB = preg_replace("/^.*?:/", "", $DB);
		} else {
			$DBType = "";
		}
		
		if ($Host) { 
			$this->hostname = $Host;
		}
		elseif (ereg("host=([^;]+);", $ConnectionString, $ret)) {
			$this->hostname = $ret[1];
		}
		
		if ($DB) {
			$this->database = $DB;
		} elseif (ereg("db=([^;]+);",   $ConnectionString, $ret)) {
			$this->database = preg_replace("/^.*?:/", "", $ret[1]);
		}
		
		if ($UID) {
			$this->username = $UID;
		}
		elseif (ereg("uid=([^;]+);",  $ConnectionString, $ret)) {
			$this->username = $ret[1];
		}
		
		if ($Pwd) {
			$this->password = $Pwd;
		} elseif (ereg("pwd=([^;]+);",  $ConnectionString, $ret)) {
			$this->password = $ret[1];
		}

		if ($DBType) { 
			$this->dbtype = $DBType;
		} elseif (ereg("db=([^;]+);", $ConnectionString, $ret)) {
			$this->dbtype = preg_replace("/:.*$/", "", $ret[1]);
		}
	}

	function Open() {
		ADOLoadCode($this->dbtype);
		ob_start();
		$this->connection= &KTNEWConnection($this->dbtype);//&ADONewConnection($this->dbtype);
		$connectionError = ob_get_contents();
		ob_end_clean();
		//added to support the adodb failures -- cristic
		if (false === $this->connection){
				$error  = "<ERRORS>";
				$error .= "<ERROR><DESCRIPTION> ADOdb driver failed to initialise. </DESCRIPTION></ERROR>";
				$error .= "<ERROR><DESCRIPTION>" . $connectionError . "</DESCRIPTION></ERROR>";
				$error .= "</ERRORS>";
				$this->isOpen = false;
				return $error;			
		}

		if (!$this->database) {
			if (preg_match("/postgres/", $this->dbtype)) {
				$this->database = "template1";
			}
		}
    if($this->dbtype == "access" || $this->dbtype == "odbc" || $this->dbtype == "odbc_mssql"){
     	$this->connectionId = $this->connection->Connect($this->database, $this->username,$this->password);
  	} else if(($this->dbtype == "ibase") or ($this->dbtype == "firebird")) {
     	$this->connectionId = $this->connection->Connect($this->hostname.":".$this->database,$this->username,$this->password);
  	} else {
     	$this->connectionId = $this->connection->Connect($this->hostname,$this->username,$this->password,$this->database);
	  }

		$connectionError = ob_get_contents();	
		if ($this->connectionId) {
			$this->isOpen = true;
			return true;
	  } else {
			// this error information gets added in test open
			if (is_object($this->connection) && method_exists($this->connection, 'ErrorMsg')){ 
					$error_message = $this->connection->ErrorMsg() ;
			}
			
			if (!isset($error_message) || $error_message == "") {
					$error_message = "Unable to Establish Connection to Host '" . $this->hostname . "', database '".$this->database."' for user '" . $this->username."'" ;
			}
			
			$error  = "<ERRORS>";
			$error .= "<ERROR><DESCRIPTION>" . @$error_message . "</DESCRIPTION></ERROR>";
			$error .= "<ERROR><DESCRIPTION>" . @$connectionError . "</DESCRIPTION></ERROR>";
			$error .= "</ERRORS>";
			$this->isOpen = false;

			return $error;
		}	
	}

	function TestOpen() {
		return ($this->isOpen) ? "<TEST status=true></TEST>" : $this->HandleException();
	}

	function Close() {
		if ($this->connectionId && $this->isOpen) {
			if ($this->connection->Close()) {
				$this->isOpen = false;
				$this->connectionId = 0;
			}
		}
	}

	function GetTables() {
		$xmlOutput = "";
		$result = $this->connection->MetaTables('TABLES');
		
		// RST - bug was reported 
		sort($result);
		reset($result);

		if ($result)
		{
			$xmlOutput = "<RESULTSET><FIELDS>";

			// Columns are referenced by index, so Schema and
			// Catalog must be specified even though they are not supported
			$xmlOutput .= "<FIELD><NAME>TABLE_CATALOG</NAME></FIELD>";		// column 0 (zero-based)
			$xmlOutput .= "<FIELD><NAME>TABLE_SCHEMA</NAME></FIELD>";		// column 1
			$xmlOutput .= "<FIELD><NAME>TABLE_NAME</NAME></FIELD>";		// column 2

			$xmlOutput .= "</FIELDS><ROWS>";
			$tableCount = sizeof($result);

			for ($i=0; $i < $tableCount; $i++)
			{
				$xmlOutput .= "<ROW><VALUE/><VALUE/><VALUE>";
				$xmlOutput .= $result[$i];
				$xmlOutput .= "</VALUE></ROW>";
			}

			$xmlOutput .= "</ROWS></RESULTSET>";
		}

		return $xmlOutput;
	}

	function GetViews()
	{
		$xmlOutput = "";
		$result = $this->connection->MetaTables('VIEWS');

		if ($result)
		{
			$xmlOutput = "<RESULTSET><FIELDS>";

			// Columns are referenced by index, so Schema and
			// Catalog must be specified even though they are not supported
			$xmlOutput .= "<FIELD><NAME>TABLE_CATALOG</NAME></FIELD>";		// column 0 (zero-based)
			$xmlOutput .= "<FIELD><NAME>TABLE_SCHEMA</NAME></FIELD>";		// column 1
			$xmlOutput .= "<FIELD><NAME>TABLE_NAME</NAME></FIELD>";		// column 2

			$xmlOutput .= "</FIELDS><ROWS>";
			$tableCount = sizeof($result);

			for ($i=0; $i < $tableCount; $i++)
			{
				$xmlOutput .= "<ROW><VALUE/><VALUE/><VALUE>";
				$xmlOutput .= $result[$i];
				$xmlOutput .= "</VALUE></ROW>";
			}

			$xmlOutput .= "</ROWS></RESULTSET>";
		}

		return $xmlOutput;
	}

	function GetProcedures()
	{
		
		if (method_exists($this->connection, 'getProcedureList') && false){
			$result = $this->connection->getProcedureList('public');
		}else{
			return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";	
		}

		$xmlOutput = "";

		if ($result)
		{
			$xmlOutput = "<RESULTSET><FIELDS>";

			// Columns are referenced by index, so Schema and
			// Catalog must be specified even though they are not supported
			$xmlOutput .= "<FIELD><NAME>PROCEDURE_CATALOG</NAME></FIELD>";		// column 0 (zero-based)
			$xmlOutput .= "<FIELD><NAME>PROCEDURE_SCHEMA</NAME></FIELD>";		// column 1
			$xmlOutput .= "<FIELD><NAME>PROCEDURE_NAME</NAME></FIELD>";		// column 2
			$xmlOutput .= "<FIELD><NAME>PROCEDURE_TYPE</NAME></FIELD>";		// column 3
			$xmlOutput .= "<FIELD><NAME>PROCEDURE_DEFINITION</NAME></FIELD>";		// column 4
			$xmlOutput .= "<FIELD><NAME>DESCRIPTION</NAME></FIELD>";		// column 5
			$xmlOutput .= "<FIELD><NAME>DATE_CREATED</NAME></FIELD>";		// column 6
			$xmlOutput .= "<FIELD><NAME>DATE_MODIFIED</NAME></FIELD>";		// column 7

			$xmlOutput .= "</FIELDS><ROWS>";
			$tableCount = sizeof($result);
			if (is_array($result)){
					foreach($result as $key=>$value)
					{
								$xmlOutput .= "<ROW>";
								$xmlOutput .= '<VALUE>'.@$value['procedure_catalog'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_schema'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_name'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_type'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_definition'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_description'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_date_created'].'</VALUE>';
								$xmlOutput .= '<VALUE>'.@$value['procedure_date_modified'].'</VALUE>';
								$xmlOutput .= "</ROW>";
	  			}
			}
			$xmlOutput .= "</ROWS></RESULTSET>";
		}else{
			return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";	
                }

		return $xmlOutput;
	}

	function GetColumnsOfTable($TableName)
	{
		if (function_exists("utf8_decode")){
		$TableName = utf8_decode($TableName);
		}
		$xmlOutput = "";
		$result = $this->connection->MetaColumns($TableName);
		if (!$result) {
			if (is_object($this->connection) && method_exists($this->connection, 'ErrorMsg')){ 
			$errStr = $this->connection->ErrorMsg();
			}
			if (@$errStr == "") {
				$errStr = "Unable to retrive column information of table " . $TableName;
			}
			$error  = "<ERRORS>";
			$error .= "<ERROR><DESCRIPTION>".$errStr."</DESCRIPTION></ERROR>";
			$error .= "</ERRORS>";
			return $error;
		} else {
			$xmlOutput = "<RESULTSET><FIELDS>";

			// Columns are referenced by index, so Schema and
			// Catalog must be specified even though they are not supported
			$xmlOutput .= "<FIELD><NAME>TABLE_CATALOG</NAME></FIELD>";		// column 0 (zero-based)
			$xmlOutput .= "<FIELD><NAME>TABLE_SCHEMA</NAME></FIELD>";		// column 1
			$xmlOutput .= "<FIELD><NAME>TABLE_NAME</NAME></FIELD>";			// column 2
			$xmlOutput .= "<FIELD><NAME>COLUMN_NAME</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>DATA_TYPE</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>IS_NULLABLE</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>COLUMN_SIZE</NAME></FIELD>";

			$xmlOutput .= "</FIELDS><ROWS>";

			// The fields returned from DESCRIBE are: Field, Type, Null, Key, Default, Extra
			while (list ($field, $row) = each ($result))
			{
				$xmlOutput .= "<ROW><VALUE/><VALUE/><VALUE/>";

				if (preg_match("/^(.+)\((\d+)\)/", $row->type, $ret))
				{
					$row->type = $ret[1];
					$row->max_length = $ret[2];
				}
				
				if($row->max_length == -1){
					$row->max_length = "";
				}
				if (function_exists("utf8_encode")){
						$xmlOutput .= "<VALUE>" . (utf8_encode($row->name)) 			. "</VALUE>";
				}else{
						$xmlOutput .= "<VALUE>".$row->name."</VALUE>";
				}
				$xmlOutput .= "<VALUE>" . $row->type                    . "</VALUE>";
				$xmlOutput .= "<VALUE>" . (($row->not_null)?"NO":"YES") . "</VALUE>";
				$xmlOutput .= "<VALUE>" . $row->max_length         		. "</VALUE></ROW>";
			}
			$xmlOutput .= "</ROWS></RESULTSET>";
		}

		return $xmlOutput;
	}

	function GetParametersOfProcedure($ProcedureName, $SchemaName, $CatalogName)
	{					$result = "";

					if (method_exists($this->connection, 'getProcedureParameters') && false){
							$result = $this->connection->getProcedureParameters($ProcedureName, 'public');
					}else{
								return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";	
					}
					$xmlOutput = "<RESULTSET><FIELDS>";
					$xmlOutput .= "<FIELD><NAME>PROCEDURE_CATALOG</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PROCEDURE_SCHEMA</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PROCEDURE_NAME</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PARAMETER_NAME</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>ORDINAL_POSITION</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PARAMETER_TYPE</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PARAMETER_HASDEFAULT</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>PARAMETER_DEFAULT</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>IS_NULLABLE</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>DATA_TYPE</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>CHARACTER_MAXIMUM_LENGTH</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>CHARACTER_OCTET_LENGTH</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>NUMERIC_PRECISION</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>NUMERIC_SCALE</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>DESCRIPTION</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>TYPE_NAME</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>LOCAL_TYPE_NAME</NAME></FIELD>";
					$xmlOutput .= "<FIELD><NAME>SS_DATA_TYPE</NAME></FIELD>";
					$xmlOutput .= "</FIELDS><ROWS>";

					if (is_array($result)){
							foreach($result as $key=>$value){
											$xmlOutput .= "<ROW>";
											$xmlOutput .= "<VALUE>".@$value['procedure_catalog']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['procedure_schema']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['procedure_name']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['parameter_name']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['ordinal_position']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['parameter_type']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['parameter_hasdefault']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['parameter_default']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['is_nullable']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['data_type']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['character_maximum_length']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['character_octet_legth']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['numeric_precision']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['numeric_scale']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['description']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['type_name']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['local_type_name']."</VALUE> ";
											$xmlOutput .= "<VALUE>".@$value['ss_data_type']."</VALUE> ";
											$xmlOutput .= "</ROW>";
							}					
					}
					$xmlOutput .= "</ROWS></RESULTSET>";
					return $xmlOutput;
		// not supported
	}

	function ExecuteSQL($aStatement, $MaxRows)
	{
		if ( get_magic_quotes_gpc() )
		{
			$aStatement = stripslashes( $aStatement ) ;
		}
				
		$xmlOutput = "";
		$result = $this->connection->SelectLimit($aStatement,$MaxRows);
		if (!$result) {
			$result = $this->connection->Execute($aStatement);
		}
		if (!$result) {
			if (is_object($this->connection) && method_exists($this->connection, 'ErrorMsg')){ 
			$errorMsg = $this->connection->ErrorMsg();
			}
			if (@$errorMsg == "") {
				$errorMsg = "Error executing query: " . $aStatement;
			}
			$error  = "<ERRORS>";
			$error .= "<ERROR><DESCRIPTION>" . $errorMsg . "</DESCRIPTION></ERROR>";
			$error .= "</ERRORS>";
			return $error;
		} else {
			$xmlOutput = "<RESULTSET><FIELDS>";

			$fieldCount = $result->FieldCount();
			for ($i=0; $i < $fieldCount; $i++)
			{
				$meta = $result->FetchField($i);
				if ($meta)
				{
					$xmlOutput .= "<FIELD";
					$xmlOutput .= " type=\""			. @$meta->type;
					$xmlOutput .= "\" max_length=\""	. @$meta->max_length;
					$xmlOutput .= "\" table=\""			. (@$meta->table);
					$xmlOutput .= "\" not_null=\""		.@$meta->not_null;
					$xmlOutput .= "\" numeric=\""		. @$meta->numeric;
					$xmlOutput .= "\" unsigned=\""		. @$meta->unsigned;
					$xmlOutput .= "\" zerofill=\""		. @$meta->zerofill;
					$xmlOutput .= "\" primary_key=\""	. @$meta->primary_key;
					$xmlOutput .= "\" multiple_key=\""	. @$meta->multiple_key;
					$xmlOutput .= "\" unique_key=\""	. @$meta->unique_key;
					$xmlOutput .= "\"><NAME>"			. $meta->name;
					$xmlOutput .= "</NAME></FIELD>";
				}
			}

			$xmlOutput .= "</FIELDS><ROWS>";

			while(!$result->EOF)
			{
				$xmlOutput .= "<ROW>";

				for ($i=0; $i<$fieldCount; $i++)
				{
					$xmlOutput .= "<VALUE>";
					$xmlOutput .= htmlspecialchars($result->fields[$i]);
					$xmlOutput .= "</VALUE>";
				}

 				$xmlOutput .= "</ROW>";
 				$result->MoveNext();
			}

			$result->Close();

			$xmlOutput .= "</ROWS></RESULTSET>";
		}
				
		return $xmlOutput;
	}

	function GetProviderTypes()
	{
		// not supported?
		return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";
	}

	function ExecuteSP($aProcStatement, $TimeOut, $Parameters)
	{
		// not supported
		return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";
	}

	function ReturnsResultSet($ProcedureName)
	{
		// not supported
		return "<RETURNSRESULTSET status=false></RETURNSRESULTSET>";
	}

	function SupportsProcedure()
	{	
		return "<SUPPORTSPROCEDURE status=".((method_exists($this->connection, 'getProcedureList') && false)?"true":"false")."></SUPPORTSPROCEDURE>";
	}

	function HandleException()
	{
		if (is_object($this->connection) && method_exists($this->connection, 'ErrorMsg')){ 
		$errorMsg = $this->connection->ErrorMsg();
		}
		if ($errorMsg == "") {
			$errorMsg = "Unable to establish connection to the server!";
		}
		return "<ERRORS><ERROR><DESCRIPTION>" . $errorMsg . "</DESCRIPTION></ERROR></ERRORS>";
	}

	function GetDatabaseList()
	{
		$xmlOutput = "<RESULTSET><FIELDS><FIELD><NAME>NAME</NAME></FIELD></FIELDS><ROWS>";
		$dbList = $this->connection->MetaDatabases();

		foreach ($dbList as $key=>$value)
		{
			$xmlOutput .= "<ROW><VALUE>" . ($value) . "</VALUE></ROW>";
		}

		$xmlOutput .= "</ROWS></RESULTSET>";

		return $xmlOutput;
	}

	function GetPrimaryKeysOfTable($TableName)
	{
		$xmlOutput = "";
		$result = $this->connection->MetaColumns($TableName);
		if (!$result) {
			if (is_object($this->connection) && method_exists($this->connection, 'ErrorMsg')){ 
			$errorMsg = $this->connection->ErrorMsg();
			}
			if (@$errorMsg == "") {
				$errorMsg = "Unable to get primary key of table " . $TableName;
			}
			return "<ERRORS><ERROR><DESCRIPTION>" . $errorMsg . "</DESCRIPTION></ERROR></ERRORS>";
		} else {
			$xmlOutput = "<RESULTSET><FIELDS>";

			// Columns are referenced by index, so Schema and
			// Catalog must be specified even though they are not supported
			$xmlOutput .= "<FIELD><NAME>TABLE_CATALOG</NAME></FIELD>";		// column 0 (zero-based)
			$xmlOutput .= "<FIELD><NAME>TABLE_SCHEMA</NAME></FIELD>";		// column 1
			$xmlOutput .= "<FIELD><NAME>TABLE_NAME</NAME></FIELD>";			// column 2
			$xmlOutput .= "<FIELD><NAME>COLUMN_NAME</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>DATA_TYPE</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>IS_NULLABLE</NAME></FIELD>";
			$xmlOutput .= "<FIELD><NAME>COLUMN_SIZE</NAME></FIELD>";
			$xmlOutput .= "</FIELDS><ROWS>";

			// The fields returned from DESCRIBE are: Field, Type, Null, Key, Default, Extra
			foreach ($result as $field=>$row)
			{
			  if (isset($row->primary_key) && $row->primary_key){
  				$xmlOutput .= "<ROW><VALUE/><VALUE/><VALUE/>";
  
				if (preg_match("/^(.+)\((\d+)\)/", $row->type, $ret))
				{
					 $row->type = $ret[1];
					 $row->max_length = $ret[2];
				}

				if($row->max_length == -1){
					$row->max_length = "";
				}

				$xmlOutput .= "<VALUE>" . ($field) 			. "</VALUE>";
				$xmlOutput .= "<VALUE>" . $row->type                    . "</VALUE>";
				$xmlOutput .= "<VALUE>" . (($row->not_null)?"NO":"YES") . "</VALUE>";
				$xmlOutput .= "<VALUE>" . $row->max_length         		. "</VALUE></ROW>";
  			  }
			}

			$xmlOutput .= "</ROWS></RESULTSET>";
		}
		return $xmlOutput;
	}

}	// class ADODBConnection
?>
