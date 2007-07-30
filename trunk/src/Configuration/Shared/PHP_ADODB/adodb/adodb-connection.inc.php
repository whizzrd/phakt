<?php
	class ADOConnection {
	//
	// PUBLIC VARS 
	//
	var $dataProvider = 'native';
	var $databaseType = '';		/// RDBMS currently in use, eg. odbc, mysql, mssql					
	var $database = '';			/// Name of database to be used.	
	var $host = ''; 			/// The hostname of the database server	
	var $user = ''; 			/// The username which is used to connect to the database server. 
	var $password = ''; 		/// Password for the username. For security, we no longer store it.
	var $debug = false; 		/// if set to true will output sql statements
	var $maxblobsize = 64000; 	/// maximum size of blobs or large text fields -- some databases die otherwise like foxpro
	var $concat_operator = '+'; /// default concat operator -- change to || for Oracle/Interbase	
	var $fmtDate = "'Y-m-d'";	/// used by DBDate() as the default date format used by the database
	var $fmtTimeStamp = "'Y-m-d, h:i:s A'"; /// used by DBTimeStamp as the default timestamp fmt.
	var $true = '1'; 			/// string that represents TRUE for a database
	var $false = '0'; 			/// string that represents FALSE for a database
	var $replaceQuote = "\\'"; 	/// string to use to replace quotes
	var $charSet=false; 		/// character set to use - only for interbase
	var $metaTablesSQL = '';
	//--
	var $hasInsertID = false; 		/// supports autoincrement ID?
	var $hasAffectedRows = false; 	/// supports affected rows for update/delete?
	var $hasTop = false;			/// support mssql/access SELECT TOP 10 * FROM TABLE
	var $hasLimit = false;			/// support pgsql/mysql SELECT * FROM TABLE LIMIT 10
	var $readOnly = false; 			/// this is a readonly database - used by phpLens
	var $hasMoveFirst = false;  /// has ability to run MoveFirst(), scrolling backwards
	var $hasGenID = false; 		/// can generate sequences using GenID();
	var $hasTransactions = true; /// has transactions
	//--
	var $genID = 0; 			/// sequence id used by GenID();
	var $raiseErrorFn = false; 	/// error function to call
	var $upperCase = false; 	/// uppercase function to call for searching/where
	var $isoDates = false; /// accepts dates in ISO format
	var $cacheSecs = 3600; /// cache for 1 hour
	var $sysDate = false; /// name of function that returns the current date
	var $sysTimeStamp = false; /// name of function that returns the current timestamp
	var $arrayClass = 'ADORecordSet_array'; /// name of class used to generate array recordsets, which are pre-downloaded recordsets
	
	var $noNullStrings = false; /// oracle specific stuff - if true ensures that '' is converted to ' '
	var $numCacheHits = 0; 
	var $numCacheMisses = 0;
	var $pageExecuteCountRows = true;
	var $uniqueSort = false; /// indicates that all fields in order by must be unique
	var $leftOuter = false; /// operator to use for left outer join in WHERE clause
	var $rightOuter = false; /// operator to use for right outer join in WHERE clause
	var $ansiOuter = false; /// whether ansi outer join syntax supported
	var $autoRollback = false; // autoRollback on PConnect().
	var $poorAffectedRows = false; // affectedRows not working or unreliable
	var $fnExecute = false;
	var $fnCacheExecute = false;
	var $blobEncodeType = false; // false=not required, 'I'=encode to integer, 'C'=encode to char
	 //
	 // PRIVATE VARS
	 //
	var $_connectionID	= false;	/// The returned link identifier whenever a successful database connection is made.	
	var $_errorMsg = '';		/// A variable which was used to keep the returned last error message.  The value will
								/// then returned by the errorMsg() function	
						
	var $_queryID = false;		/// This variable keeps the last created result link identifier
	
	var $_isPersistentConnection = false;	/// A boolean variable to state whether its a persistent connection or normal connection.	*/
	var $_bindInputArray = false; /// set to true if ADOConnection.Execute() permits binding of array parameters.
	var $autoCommit = true; 	/// do not modify this yourself - actually private
	var $transOff = false; 		/// temporarily disable transactions
	var $transCnt = 0; 			/// count of nested transactions
	
	var $fetchMode=false;
	
	/**
	 * Constructor
	 */
	function ADOConnection()			
	{
		die('Virtual Class -- cannot instantiate');
	}
	
	function ServerInfo()
	{
		return array('description' => '', 'version' => '');
	}
	
	function _findvers($str)
	{
		if (preg_match('/([0-9]+\.([0-9\.])+)/',$str, $arr)) return $arr[1];
		else return '';
	}
	
	function outp($msg,$newline=true)
	{
	global $HTTP_SERVER_VARS;
	
		if (defined('ADODB_OUTP')) {
			$fn = ADODB_OUTP;
			$fn($msg,$newline);
			return;
		}
		
		if ($newline) $msg .= "<br>\n";
		
		if (isset($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) echo $msg;
		else echo strip_tags($msg);
		flush();
	}
	
	function Connect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "", $argLocale = "", $forceNew = false) 
	{
		if ($argHostname != "") $this->host = $argHostname;
		if ($argUsername != "") $this->user = $argUsername;
		if ($argPassword != "") $this->password = $argPassword; // not stored for security reasons
		if ($argDatabaseName != "") $this->database = $argDatabaseName;		
		
		$this->_isPersistentConnection = false;	
		$this->_setDateLocale($argLocale);
		if ($fn = $this->raiseErrorFn) {
			if ($forceNew) {
				if ($this->_nconnect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
			} else {
				 if ($this->_connect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
			}
			$err = $this->ErrorMsg();
			if (empty($err)) $err = "Connection error to server '$argHostname' with user '$argUsername'";
			$fn($this->databaseType,'CONNECT',$this->ErrorNo(),$err,$this->host,$this->database);
		} else {
			if ($forceNew) {
				if ($this->_nconnect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
			} else {
				if ($this->_connect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
			}
		}
		if ($this->debug) ADOConnection::outp( $this->host.': '.$this->ErrorMsg());
		
		return false;
	}	
	
	 function _nconnect($argHostname, $argUsername, $argPassword, $argDatabaseName, $argLocale)
	 {
	 	return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabaseName,$argLocale);
	 }
	
 
	function NConnect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "", $argLocale = "") 
	{
		return $this->Connect($argHostname, $argUsername, $argPassword, $argDatabaseName, $argLocale, true);
	}

	function PConnect($argHostname = "", $argUsername = "", $argPassword = "", $argDatabaseName = "", $argLocale = "")
	{
		if ($argHostname != "") $this->host = $argHostname;
		if ($argUsername != "") $this->user = $argUsername;
		if ($argPassword != "") $this->password = $argPassword;
		if ($argDatabaseName != "") $this->database = $argDatabaseName;		
                //interakt -begin
		$this->_setDateLocale($argLocale);	
                //interakt - end
		$this->_isPersistentConnection = true;	
		
		if ($fn = $this->raiseErrorFn) {
			if ($this->_pconnect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
			$err = $this->ErrorMsg();
			if (empty($err)) $err = "Connection error to server '$argHostname' with user '$argUsername'";
			$fn($this->databaseType,'PCONNECT',$this->ErrorNo(),$err,$this->host,$this->database, $this->locale);
		} else {

			if ($this->_pconnect($this->host, $this->user, $this->password, $this->database, $this->locale)) return true;
		}
		if ($this->debug) ADOConnection::outp( $this->host.': '.$this->ErrorMsg());
		return false;
	}
	/** InterAKT
	 *
	 * Specifies if the db server supports LOCALES
	 *
	 * @return true or false
	 */
	function HasLocale() {
	  return true;
	}
	// InterAKT	
	
	/**
	*  Change the SQL connection locale to a specified locale.
	*  This is used to get the date formats written depending on the client locale.
	*/
	function _setDateLocale($locale = 'En')
	{
		$this->locale = $locale;
		switch ($locale)
		{
			case 'En':
				$this->fmtDate="Y-m-d";
				$this->fmtTimeStamp = "Y-m-d H:i:s";
				break;
			case 'Fr':
				$this->fmtDate="d-m-Y";
				$this->fmtTimeStamp = "d-m-Y H:i:s";
				break;
			case 'Ro':
				$this->fmtDate="d-m-Y";
				$this->fmtTimeStamp = "d-m-Y H:i:s";
				break;
			case 'It':
				$this->fmtDate="d-m-Y";
				$this->fmtTimeStamp = "d-m-Y H:i:s";
				break;
			case 'Ge':
				$this->fmtDate="d.m.Y";
				$this->fmtTimeStamp = "d.m.Y H:i:s";
				break;
			default :
				$this->fmtDate="Y-m-d";
				$this->fmtTimeStamp = "Y-m-d H:i:s";
		}
	}


	// Format date column in sql string given an input format that understands Y M D
	function SQLDate($fmt, $col=false)
	{	
		if (!$col) $col = $this->sysDate;
		return $col; // child class implement
	}

	function Prepare($sql)
	{
		return $sql;
	}

	function PrepareSP($sql)
	{
		return $this->Prepare($sql);
	}
	
	function Quote($s)
	{
		return $this->qstr($s,get_magic_quotes_gpc());
	}


	function ErrorNative()
	{
		return $this->ErrorNo();
	}

	function nextId($seq_name)
	{
		return $this->GenID($seq_name);
	}

	function RowLock($table,$where)
	{
		return false;
	}
	
	function CommitLock($table)
	{
		return $this->CommitTrans();
	}
	
	function RollbackLock($table)
	{
		return $this->RollbackTrans();
	}

	function SetFetchMode($mode)
	{	
		$old = $this->fetchMode;
		$this->fetchMode = $mode;
		
		if ($old === false) {
		global $ADODB_FETCH_MODE;
			return $ADODB_FETCH_MODE;
		}
		return $old;
	}
	

	function &Query($sql, $inputarr=false)
	{
		$rs = &$this->Execute($sql, $inputarr);
		if (!$rs && defined('ADODB_PEAR')) return ADODB_PEAR_Error();
		return $rs;
	}


	function &LimitQuery($sql, $offset, $count)
	{
		$rs = &$this->SelectLimit($sql, $count, $offset); // swap 
		if (!$rs && defined('ADODB_PEAR')) return ADODB_PEAR_Error();
		return $rs;
	}


	function Disconnect()
	{
		return $this->Close();
	}


	function Parameter(&$stmt,&$var,$name,$isOutput=false,$maxLen=4000,$type=false)
	{
		return false;
	}
	

	function getmicrotime(){ 
   		list($usec, $sec) = explode(" ",microtime()); 
   		return ((float)$usec + (float)$sec); 
   	} 
	function &Execute($sql,$inputarr=false,$arg3=false) 
	{
		$time_start = $this->getmicrotime();
		if ($this->fnExecute) {
			$fn = $this->fnExecute;
			$fn($this,$sql,$inputarr);
		}
		if (!$this->_bindInputArray && $inputarr) {
			$sqlarr = explode('?',$sql);
			$sql = '';
			$i = 0;
			foreach($inputarr as $v) {

				$sql .= $sqlarr[$i];
				// from Ron Baldwin <ron.baldwin@sourceprose.com>
				// Only quote string types	
				if (gettype($v) == 'string')
					$sql .= $this->qstr($v);
				else if ($v === null)
					$sql .= 'NULL';
				else
					$sql .= $v;
				$i += 1;
	
			}
			$sql .= $sqlarr[$i];
			if ($i+1 != sizeof($sqlarr))	
				ADOConnection::outp( "Input Array does not match ?: ".htmlspecialchars($sql));
			$inputarr = false;
		}
		// debug version of query
		if ($this->debug) {
		global $HTTP_SERVER_VARS;
		
			$ss = '';
			if ($inputarr) {
				foreach ($inputarr as $kk => $vv)  {
					if (is_string($vv) && strlen($vv)>64) $vv = substr($vv,0,64).'...';
					$ss .= "($kk=>'$vv') ";
				}
				$ss = "[ $ss ]";
			}
			if (is_array($sql)) $sqlTxt = $sql[0];
			else $sqlTxt = $sql;
			
			// check if running from browser or command-line
			$inBrowser = isset($HTTP_SERVER_VARS['HTTP_USER_AGENT']);
			
			if ($inBrowser)
				ADOConnection::outp( "<hr />\n($this->databaseType): ".htmlspecialchars($sqlTxt)." &nbsp; <code>$ss</code>\n<hr />\n",false);
			else
				ADOConnection::outp(  "=----\n($this->databaseType): ".($sqlTxt)." \n-----\n",false);
			flush();
			
			$this->_queryID = $this->_query($sql,$inputarr,$arg3);
			if ($this->databaseType == 'mssql') { 
			// ErrorNo is a slow function call in mssql, and not reliable
			// in PHP 4.0.6
				if($emsg = $this->ErrorMsg()) {
					$err = $this->ErrorNo();
					if ($err) {
						ADOConnection::outp($err.': '.$emsg);
						flush();
					}
				}
			} else 
				if (!$this->_queryID) {
					ADOConnection::outp( $this->ErrorNo().': '.$this->ErrorMsg() );
					flush();
				}
		} else {
			// non-debug version of query
			$this->_queryID =@$this->_query($sql,$inputarr,$arg3);
		}
		// error handling if query fails
		if ($this->_queryID === false) {
			$fn = $this->raiseErrorFn;
			if ($fn) {
				$fn($this->databaseType,'EXECUTE',$this->ErrorNo(),$this->ErrorMsg(),$sql,$inputarr);
			}
			return false;
		} else if ($this->_queryID === true) {
		// return simplified empty recordset for inserts/updates/deletes with lower overhead
			$rs = new ADORecordSet_empty();
			return $rs;
		}
		
		// return real recordset from select statement
		$rsclass = "ADORecordSet_".$this->databaseType;
		$rs = new $rsclass($this->_queryID,$this->locale,$this->fetchMode); // &new not supported by older PHP versions
		$rs->connection = &$this; // Pablo suggestion
		$rs->Init();
		if (is_array($sql)) $rs->sql = $sql[0];
		else $rs->sql = $sql;
			
		global $ADODB_COUNTRECS;
		if ($rs->_numOfRows <= 0 && $ADODB_COUNTRECS) {
			if (!$rs->EOF){ 
				$rs = &$this->_rs2rs($rs);
				$rs->_queryID = $this->_queryID;
			} else
				$rs->_numOfRows = 0;
		}
		global $temporizare;
		global $TEMP_FILE;
		global $TEMP_TABLE;
		$time_stop = $this->getmicrotime();
		$time = ($time_stop - $time_start)*1000;
		
		if ($temporizare == "file") {
			$fp = fopen ($TEMP_FILE, "a");
			$str = sprintf("Execute query: $sql in %f msec\n",$time);
			fwrite($fp, $str, strlen($str));
			fclose($fp);
		} else if($temporizare== "sql") {
			$sql = "INSERT INTO ".$TEMP_TABLE." (sql_qtm,time_qtm) values ('".$sql."',".$time.")";
			$this->_query($sql, $inputarr);  	
		}
		return $rs;
	}

	function CreateSequence($seqname='adodbseq',$startID=1)
	{
		if (empty($this->_genSeqSQL)) return false;
		return $this->Execute(sprintf($this->_genSeqSQL,$seqname,$startID));
	}
	
	function DropSequence($seqname)
	{
		if (empty($this->_dropSeqSQL)) return false;
		return $this->Execute(sprintf($this->_dropSeqSQL,$seqname));
	}


	function GenID($seqname='adodbseq',$startID=1)
	{
		if (!$this->hasGenID) {
			return 0; // formerly returns false pre 1.60
		}
		
		$getnext = sprintf($this->_genIDSQL,$seqname);
		$rs = @$this->Execute($getnext);
		if (!$rs) {
			$createseq = $this->Execute(sprintf($this->_genSeqSQL,$seqname,$startID));
			$rs = $this->Execute($getnext);
		}
		if ($rs && !$rs->EOF) $this->genID = reset($rs->fields);
		else $this->genID = 0; // false
	
		if ($rs) $rs->Close();

		return $this->genID;
	}	


     function Insert_ID($pKeyCol="",$table="")
		{
				if ($this->hasInsertID) return $this->_insertid($pKeyCol,$table);
				if ($this->debug) ADOConnection::outp( '<p>Insert_ID error</p>');
				return false;
		}
	

		function PO_Insert_ID($table="", $id="") 
		{
		   if ($this->hasInsertID){
			   return $this->Insert_ID();
		   } else {
			   return $this->GetOne("SELECT MAX($id) FROM $table");
		   }
		}	
	
	
	 function Affected_Rows()
	 {
		  if ($this->hasAffectedRows) {
				 $val = $this->_affectedrows();
				 return ($val < 0) ? false : $val;
		  }
				  
		  if ($this->debug) ADOConnection::outp( '<p>Affected_Rows error</p>',false);
		  return false;
	 }
	
	
	function ErrorMsg()
	{
		return '!! '.strtoupper($this->dataProvider.' '.$this->databaseType).': '.$this->_errorMsg;
	}
	

	function ErrorNo() 
	{
		return ($this->_errorMsg) ? -1 : 0;
	}
	

	function MetaPrimaryKeys($table, $owner=false)
	{
	// owner not used in base class - see oci8
		$p = array();
		$objs = $this->MetaColumns($table);
		if ($objs) {
			foreach($objs as $v) {
				if (!empty($v->primary_key))
					$p[] = $v->name;
			}
		}
		if (sizeof($p)) return $p;
		return false;
	}
	
	
	function SelectDB($dbName) 
	{return false;}

	function &SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$arg3=false,$secs2cache=0)
	{
		if ($this->hasTop && $nrows > 0) {
		// suggested by Reinhard Balling. Access requires top after distinct 
		 // Informix requires first before distinct - F Riosa
			$ismssql = (strpos($this->databaseType,'mssql') !== false);
			if ($ismssql) $isaccess = false;
			else $isaccess = (strpos($this->databaseType,'access') !== false);
			
			if ($offset <= 0) {
				
					// access includes ties in result
					if ($isaccess) {
						$sql = preg_replace(
						'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.$nrows.' ',$sql);

						if ($secs2cache>0) return $this->CacheExecute($secs2cache, $sql,$inputarr,$arg3);
						else return $this->Execute($sql,$inputarr,$arg3);
					} else if ($ismssql){
						$sql = preg_replace(
						'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.$nrows.' ',$sql);
					} else {
						$sql = preg_replace(
						'/(^\s*select\s)/i','\\1 '.$this->hasTop.' '.$nrows.' ',$sql);
					}
			} else {
				$nn = $nrows + $offset;
				if ($isaccess || $ismssql) {
					$sql = preg_replace(
					'/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 '.$this->hasTop.' '.$nn.' ',$sql);
				} else {
					$sql = preg_replace(
					'/(^\s*select\s)/i','\\1 '.$this->hasTop.' '.$nn.' ',$sql);
				}
			}
		}
		global $ADODB_COUNTRECS;
		
		$savec = $ADODB_COUNTRECS;
		$ADODB_COUNTRECS = false;
			
		if ($offset>0){
			if ($secs2cache>0) $rs = &$this->CacheExecute($secs2cache,$sql,$inputarr,$arg3);
			else $rs = &$this->Execute($sql,$inputarr,$arg3);
		} else {
			if ($secs2cache>0) $rs = &$this->CacheExecute($secs2cache,$sql,$inputarr,$arg3);
			else $rs = &$this->Execute($sql,$inputarr,$arg3);
		}
		$ADODB_COUNTRECS = $savec;
		
		if ($rs && !$rs->EOF) {
			return $this->_rs2rs($rs,$nrows,$offset);
		}
		//print_r($rs);
		return $rs;
	}
	 
	/**
	* Convert recordset to an array recordset
	* input recordset's cursor should be at beginning, and
	* old $rs will be closed.
	*
	* @param rs			the recordset to copy
	* @param [nrows]  	number of rows to retrieve (optional)
	* @param [offset] 	offset by number of rows (optional)
	* @return 			the new recordset
	*/
	function &_rs2rs(&$rs,$nrows=-1,$offset=-1)
	{
		if (! $rs) return false;
		$arr = &$rs->GetArrayLimit($nrows,$offset);
		$flds = array();
		for ($i=0, $max=$rs->FieldCount(); $i < $max; $i++)
			$flds[] = &$rs->FetchField($i);
		$rs->Close();
		
		$arrayClass = $this->arrayClass;
		
		$rs2 = new $arrayClass();
		$rs2->connection = &$this;
		$rs2->sql = $rs->sql;
		$rs2->InitArrayFields($arr,$flds);
		return $rs2;
	}
	
	function GetAll($sql,$inputarr=false)
	{
		$rs = $this->Execute($sql,$inputarr);
		if (!$rs) 
			if (defined('ADODB_PEAR')) return ADODB_PEAR_Error();
			else return false;
		$arr = $rs->GetArray();
		$rs->Close();
		return $arr;
	}

	function GetRow($sql,$inputarr=false)
	{
		$rs = $this->Execute($sql,$inputarr);
		if ($rs) {
			$arr = false;
			if (!$rs->EOF) $arr = $rs->fields;
			$rs->Close();
			return $arr;
		}
		return false;
	}
	
	function CacheGetRow($secs2cache,$sql=false,$inputarr=false)
	{
		$rs = $this->CacheExecute($secs2cache,$sql,$inputarr);
		if ($rs) {
			$arr = false;
			if (!$rs->EOF) $arr = $rs->fields;
			$rs->Close();
			return $arr;
		}
		return false;
	}
	
	function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
	{
		return $this->Execute("UPDATE $table SET $column=? WHERE $where",array($val)) != false;
	}


	function UpdateBlobFile($table,$column,$path,$where,$blobtype='BLOB')
	{
		$fd = fopen($path,'rb');
		if ($fd === false) return false;
		$val = fread($fd,filesize($path));
		fclose($fd);
		return $this->UpdateBlob($table,$column,$val,$where,$blobtype);
	}

	function BlobDecode($blob)
	{
		return $blob;
	}
	
	function BlobEncode($blob)
	{
		return $blob;
	}
	function UpdateClob($table,$column,$val,$where)
	{
		return $this->UpdateBlob($table,$column,$val,$where,'CLOB');
	}
	
	
 	function ActualType($meta)
	{
		switch($meta) {
		case 'C':
		case 'X':
			return 'VARCHAR';
		case 'B':
			
		case 'D':
		case 'T':
		case 'L':
		
		case 'R':
			
		case 'I':
		case 'N':
			return false;
		}
	}

	function CharMax()
	{
		return 255; // make it conservative if not defined
	}
	

	function TextMax()
	{
		return 4000; // make it conservative if not defined
	}
	
	function Close() 
	{
		return $this->_close();
		
		// "Simon Lee" <simon@mediaroad.com> reports that persistent connections need 
		// to be closed too!
		//if ($this->_isPersistentConnection != true) return $this->_close();
		//else return true;	
	}

	function BeginTrans() {return false;}
	

	function CommitTrans($ok=true) 
	{ return true;}
	

	function RollbackTrans() 
	{ return false;}


		function MetaDatabases() 
		{return false;}

	function MetaTables() 
	{
	global $ADODB_FETCH_MODE;
	
		if ($this->metaTablesSQL) {
			// complicated state saving by the need for backward compat
			$save = $ADODB_FETCH_MODE; 
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM; 
			
			if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
			
			$rs = $this->Execute($this->metaTablesSQL);
			if (isset($savem)) $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save; 
			
			if ($rs === false) return false;
			$arr = $rs->GetArray();
			$arr2 = array();
			for ($i=0; $i < sizeof($arr); $i++) {
				$arr2[] = $arr[$i][0];
			}
			$rs->Close();
			return $arr2;
		}
		return false;
	}
	

	function MetaColumns($table,$upper=true) 
	{
	global $ADODB_FETCH_MODE;
	
		if (!empty($this->metaColumnsSQL)) {
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
			$rs = $this->Execute(sprintf($this->metaColumnsSQL,($upper)?strtoupper($table):$table));
			if (isset($savem)) $this->SetFetchMode($savem);
			$ADODB_FETCH_MODE = $save;
			if ($rs === false) return false;

			$retarr = array();
			while (!$rs->EOF) { //print_r($rs->fields);
				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[0];
				$fld->type = $rs->fields[1];
				$fld->max_length = $rs->fields[2];
				$retarr[strtoupper($fld->name)] = $fld;	
				
				$rs->MoveNext();
			}
			$rs->Close();
			return $retarr;	
		}
		return false;
	}

	function MetaColumnNames($table) 
	{
		$objarr = $this->MetaColumns($table);
		if (!is_array($objarr)) return false;
		
		$arr = array();
		foreach($objarr as $v) {
			$arr[] = $v->name;
		}
		return $arr;
	}
 
	function Concat()
	{	
		$arr = func_get_args();
		return implode($this->concat_operator, $arr);
	}
	
	/**
	 * Correctly quotes a string so that all strings are escaped. We prefix and append
	 * to the string single-quotes.
	 * An example is  $db->qstr("Don't bother",magic_quotes_runtime());
	 * 
	 * @param s			the string to quote
	 * @param [magic_quotes]	if $s is GET/POST var, set to get_magic_quotes_gpc().
	 *				This undoes the stupidity of magic quotes for GPC.
	 *
	 * @return  quoted string to be sent back to database
	 */
	function qstr($s,$magic_quotes=false)
	{	
		if (!$magic_quotes) {
		
			if ($this->replaceQuote[0] == '\\'){
				// only since php 4.0.5
				$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
				//$s = str_replace("\0","\\\0", str_replace('\\','\\\\',$s));
			}
			return  "'".str_replace("'",$this->replaceQuote,$s)."'";
		}
		
		// undo magic quotes for "
		$s = str_replace('\\"','"',$s);
		
		if ($this->replaceQuote == "\\'")  // ' already quoted, no need to change anything
			return "'$s'";
		else {// change \' to '' for sybase/mssql
			$s = str_replace('\\\\','\\',$s);
			return "'".str_replace("\\'",$this->replaceQuote,$s)."'";
		}
	}

	
} // end class ADOConnection

?>
