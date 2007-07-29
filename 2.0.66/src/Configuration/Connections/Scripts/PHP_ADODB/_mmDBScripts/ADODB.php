<?php

require_once("../adodb/adodb.inc.php");

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
			$DB = preg_replace("/^.*:/", "", $DB);
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
			$this->database = preg_replace("/^.*:/", "", $ret[1]);
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
		$this->connection=&ADONewConnection($this->dbtype);
		
		if (!$this->database) {
			if (preg_match("/postgres/", $this->dbtype)) {
				$this->database = "template1";
			}
		}
    if($this->dbtype == "access" || $this->dbtype == "odbc"){
      $this->connectionId = $this->connection->Connect($this->database, $this->username,$this->password);
    } else if($this->dbtype == "ibase") {
      $this->connectionId = $this->connection->Connect($this->hostname.":".$this->database,$this->username,$this->password);
    } else {
      $this->connectionId = $this->connection->Connect($this->hostname,$this->username,$this->password,$this->database);
    }

		if ($this->connectionId) {
			$this->isOpen = true;
	  } else {
			// this error information gets added in test open
		
			$error_message = $this->connection->ErrorMsg() ;
			
			if ($error_message == "") {
				$error_message = "Unable to Establish Connection to " . $this->hostname . " for user " . $this->username ;
			}
			
			echo("<ERRORS><ERROR><DESCRIPTION>" . $error_message . "</DESCRIPTION></ERROR></ERRORS>");

			$this->isOpen = false;
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
		$result = $this->connection->MetaTables();

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
		// not supported
		return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";
	}

	function GetProcedures()
	{
		// not supported
		return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";
	}

	function GetColumnsOfTable($TableName)
	{
		$TableName = utf8_decode($TableName);
		$xmlOutput = "";
		$result = $this->connection->MetaColumns($TableName) or die("<ERRORS><ERROR Identification=\"" . $this->connection->ErrorNo() .
		   "\"><DESCRIPTION>" . $this->connection->ErrorNo() . " " . $this->connection->ErrorMsg() . "</DESCRIPTION></ERROR></ERRORS>");

		
		if ($result)
		{
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

				$xmlOutput .= "<VALUE>" . (utf8_encode($field)) 			. "</VALUE>";
				$xmlOutput .= "<VALUE>" . $row->type                    . "</VALUE>";
				$xmlOutput .= "<VALUE>" . (($row->not_null)?"NO":"YES") . "</VALUE>";
				$xmlOutput .= "<VALUE>" . $row->max_length         		. "</VALUE></ROW>";
			}
			$xmlOutput .= "</ROWS></RESULTSET>";
		}

		return $xmlOutput;
	}

	function GetParametersOfProcedure($ProcedureName, $SchemaName, $CatalogName)
	{
		// not supported
		return "<RESULTSET><FIELDS></FIELDS><ROWS></ROWS></RESULTSET>";
	}

	function ExecuteSQL($aStatement, $MaxRows)
	{
		if ( get_magic_quotes_gpc() )
		{
			$aStatement = stripslashes( $aStatement ) ;
		}
				
		$xmlOutput = "";
		$result = $this->connection->Execute($aStatement) or die("<ERRORS><ERROR Identification=\"" . $this->connection->ErrorNo() .
		   "\"><DESCRIPTION>" . $this->connection->ErrorNo() . " " . $this->connection->ErrorMsg() . "</DESCRIPTION></ERROR></ERRORS>");
		
		if ($result)
		{
			$xmlOutput = "<RESULTSET><FIELDS>";

			$fieldCount = $result->FieldCount();
			for ($i=0; $i < $fieldCount; $i++)
			{
				$meta = $result->FetchField($i);
				if ($meta)
				{
					$xmlOutput .= "<FIELD";
					$xmlOutput .= " type=\""			. $meta->type;
					$xmlOutput .= "\" max_length=\""	. $meta->max_length;
					$xmlOutput .= "\" table=\""			. ($meta->table);
					$xmlOutput .= "\" not_null=\""		. $meta->not_null;
					$xmlOutput .= "\" numeric=\""		. $meta->numeric;
					$xmlOutput .= "\" unsigned=\""		. $meta->unsigned;
					$xmlOutput .= "\" zerofill=\""		. $meta->zerofill;
					$xmlOutput .= "\" primary_key=\""	. $meta->primary_key;
					$xmlOutput .= "\" multiple_key=\""	. $meta->multiple_key;
					$xmlOutput .= "\" unique_key=\""	. $meta->unique_key;
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
		return "<SUPPORTSPROCEDURE status=false></SUPPORTSPROCEDURE>";
	}

	function HandleException()
	{
		return "<ERRORS><ERROR Identification=\"" . $this->connection->ErrorNo() .
			   "\"><DESCRIPTION>". $this->connection->ErrorNo() . " " . $this->connection->ErrorMsg() . "</DESCRIPTION></ERROR></ERRORS>";
	}

	function GetDatabaseList()
	{
		$xmlOutput = "<RESULTSET><FIELDS><FIELD><NAME>NAME</NAME></FIELD></FIELDS><ROWS>";
		$dbList = $this->connection->MetaDatabases();

		for ($i=0; $i<sizeof($dbList); $i++)
		{
			$xmlOutput .= "<ROW><VALUE>" . ($dbList[$i]) . "</VALUE></ROW>";
		}

		$xmlOutput .= "</ROWS></RESULTSET>";

		return $xmlOutput;
	}

	function GetPrimaryKeysOfTable($TableName)
	{
		$xmlOutput = "";
		$result = $this->connection->MetaColumns($TableName) or die("<ERRORS><ERROR Identification=\"" . $this->connection->ErrorNo() .
		   "\"><DESCRIPTION>" . $this->connection->ErrorNo() . " " . $this->connection->ErrorMsg() . "</DESCRIPTION></ERROR></ERRORS>");
		
		
		if ($result)
		{
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
			  if ($row->primary_key){
  				$xmlOutput .= "<ROW><VALUE/><VALUE/><VALUE/>";
  
				if (preg_match("/^(.+)\((\d+)\)/", $ret->type, $ret))
				{
					$ret->type = $ret[1];
					$ret->max_length = $ret[2];
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