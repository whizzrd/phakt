<?php

class ADODB_postgres64 extends ADOConnection{
	var $databaseType = 'postgres64';
	var $dataProvider = 'postgres';
	var $hasInsertID = true;
	var $_resultid = false;
  	var $concat_operator='||';
	//var $metaTablesSQL = "select tablename from pg_tables where tablename not like 'pg\_%' order by 1";
	//IAKT
	var $metaTablesSQL = "SELECT c.relname AS tablename FROM pg_class c WHERE ((c.relhasrules AND (EXISTS (SELECT r.rulename FROM pg_rewrite r WHERE r.ev_class = c.oid AND bpchar(r.ev_type) = '1'))) OR (c.relkind = 'v')) AND c.relname NOT LIKE 'pg_%' UNION SELECT tablename FROM pg_tables WHERE tablename NOT LIKE 'pg_%' ORDER BY tablename";
 	//IAKT
	var $metaColumnsSQL = "SELECT a.attname,t.typname,a.attlen,a.atttypmod,a.attnotnull,a.atthasdef,a.attnum  FROM pg_class c, pg_attribute a,pg_type t WHERE (relkind = 'r' OR relkind = 'v') AND c.relname='%s' AND attname not like '%%pg.dropped%%' AND a.attnum > 0 AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum";
	
	var $isoDates = true; // accepts dates in ISO format
	var $sysDate = "CURRENT_DATE";
	var $sysTimeStamp = "CURRENT_TIMESTAMP";
	var $blobEncodeType = 'C';
	
	//var $metaColumnsSQL = "SELECT a.attname,t.typname,a.attlen,a.atttypmod,a.attnotnull,a.atthasdef,a.attnum  FROM pg_class c, pg_attribute a,pg_type t WHERE relkind = 'r' AND c.relname='%s' AND a.attnum > 0 AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum";
	var $metaKeySQL = "SELECT ic.relname AS index_name, a.attname AS column_name,i.indisunique AS unique_key, i.indisprimary AS primary_key FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid AND (i.indkey[0] = a.attnum OR i.indkey[1] = a.attnum OR i.indkey[2] = a.attnum OR i.indkey[3] = a.attnum OR i.indkey[4] = a.attnum OR i.indkey[5] = a.attnum OR i.indkey[6] = a.attnum OR i.indkey[7] = a.attnum) AND a.attrelid = bc.oid AND bc.relname = '%s'";
	
	var $hasAffectedRows = true;
	var $hasLimit = false;	// set to true for pgsql 7 only. support pgsql/mysql SELECT * FROM TABLE LIMIT 10
	var $true = 't';		// string that represents TRUE for a database
	var $false = 'f';		// string that represents FALSE for a database
	var $fmtDate = "'Y-m-d'";	// used by DBDate() as the default date format used by the database
	var $fmtTimeStamp = "'Y-m-d G:i:s'"; // used by DBTimeStamp as the default timestamp fmt.
	var $hasMoveFirst = true;
	var $hasGenID = true;
	var $_genIDSQL = "SELECT NEXTVAL('%s')";
	var $_genSeqSQL = "CREATE SEQUENCE %s START %s";
	var $_dropSeqSQL = "DROP SEQUENCE %s";
	var $metaDefaultsSQL = "SELECT d.adnum as num, d.adsrc as def from pg_attrdef d, pg_class c where d.adrelid=c.oid and c.relname='%s' order by d.adnum";
	
	
	function ADODB_postgres64() 
	{
	// changes the metaColumnsSQL, adds columns: attnum[6]
	}
	
	function ServerInfo()
	{
		$arr['description'] = $this->GetOne("select version()");
		$arr['version'] = ADOConnection::_findvers($arr['description']);
		return $arr;
	}
	
	function pg_insert_id($tablename,$fieldname)
	{
		$result=pg_exec($this->_connectionID, "SELECT last_value FROM ${tablename}_${fieldname}_seq");
		if ($result) {
			$arr = @pg_fetch_row($result,0);
			pg_freeresult($result);
			if (isset($arr[0])) return $arr[0];
		}
		return false;
	}
	// Interakt
	function _insertid($pKeyCol, $table) {
		$lastId = -1;
		if (version_compare(phpversion(),'4.2.0','>=')) {
			$oid = pg_last_oid($this->_resultid);
		} else {
			$oid = pg_getlastoid($this->_resultid);
		}
		if (!$pKeyCol) {
			$query = 'SELECT ic.relname AS index_name, bc.relname AS tab_name, 
															ta.attname AS column_name, 
															i.indisunique AS unique_key, 
															i.indisprimary AS primary_key 
															FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia 
															WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid 
															AND ia.attrelid = i.indexrelid AND ta.attrelid = bc.oid 
															AND bc.relname = \'' . $table . '\' AND ta.attrelid = i.indrelid 
															AND ta.attnum = i.indkey[ia.attnum-1] AND i.indisprimary = \'t\' ';
			$qId = $this->_query($query,"");
			$rs = new ADORecordSet_postgres64($qId);
			$rs->Init();
			$pKeyCol = $rs->Fields('column_name');
	
		}
		$qId = $this->_query("select ".$pKeyCol." from ".$table." where oid = ".$oid);
		$rs = new ADORecordSet_postgres64($qId);
		$rs->Init();
		$lastId = $rs->Fields(0);
		return $lastId;
	}

   function _affectedrows()
   {
   		if (!is_resource($this->_resultid)) return false;
	   return pg_cmdtuples($this->_resultid);	  
   }

	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
		return @pg_Exec($this->_connectionID, "begin");
	}
	
	function RowLock($tables,$where) 
	{
		if (!$this->transCnt) $this->BeginTrans();
		return $this->GetOne("select 1 as ignore from $tables where $where for update");
	}
	function CommitTrans($ok=true) 
	{ 
		if ($this->transOff) return true;
		if (!$ok) return $this->RollbackTrans();
		
		$this->transCnt -= 1;
		return @pg_Exec($this->_connectionID, "commit");
	}
	function RollbackTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt -= 1;
		return @pg_Exec($this->_connectionID, "rollback");
	}
	
	function SQLDate($fmt, $col=false)
	{	
		if (!$col) $col = $this->sysDate;
		$s = '';
		
		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			if ($s) $s .= '||';
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= "date_part('year',$col)";
				break;
			case 'Q':
			case 'q':
				$s .= "date_part('quarter',$col)";
				break;
				
			case 'M':
			case 'm':
				$s .= "lpad(date_part('month',$col),2,'0')";
				break;
			case 'D':
			case 'd':
				$s .= "lpad(date_part('day',$col),2,'0')";
				break;
			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				$s .= $this->qstr($ch);
				break;
			}
		}
		return $s;
	}

	function UpdateBlobFile($table,$column,$path,$where,$blobtype='BLOB') 
	{ 
		pg_exec ($this->_connectionID, "begin"); 
		$oid = pg_lo_import ($path); 
		pg_exec ($this->_connectionID, "commit"); 
		$rs = ADOConnection::UpdateBlob($table,$column,$oid,$where,$blobtype); 
		$rez = !empty($rs); 
		return $rez; 
	} 

	function BlobDecode( $blob ) 
	{ 
		@pg_exec("begin"); 
		$fd = @pg_lo_open($blob,"r");
		if ($fd === false) {
			@pg_exec("commit");
			return $blob;
		}
		$realblob = @pg_loreadall($fd); 
		@pg_loclose($fd); 
		@pg_exec("commit"); 
		return $realblob;
	} 
	function BlobEncode($blob)
	{ // requires php 4.0.5
		$badch = array(chr(92),chr(0),chr(39));
		$fixch = array('\\\\134','\\\\000','\\\\047');
		return adodb_str_replace($badch,$fixch,$blob);
		
		// note that there is a pg_escape_bytea function only for php 4.2.0 or later
	}
	
	function UpdateBlob($table,$column,$val,$where,$blobtype='BLOB')
	{
		return $this->Execute("UPDATE $table SET $column=? WHERE $where",array(BlobEncode($bal))) != false;
	}
	
	function POSTGRESTypes($type) {
		switch (strtoupper($type)) {
		case 'CHAR':
		case 'CHARACTER':
			return 'char';
		case 'VARCHAR':
		case 'NAME':
		case 'BPCHAR':
			//if ($len <= $this->blobSize)
			return 'varchar';
		case 'TEXT':
			return 'text';
		case 'IMAGE': // user defined type
		case 'BLOB': // user defined type
		case 'BIT':	// This is a bit string, not a single bit, so don't return 'L'
		case 'VARBIT':
		case 'BYTEA':
			return 'blob';
		case 'BOOL':
		case 'BOOLEAN':
			return 'boolean';
		case 'DATE':
			return 'date';
		case 'TIME':
			return 'time';
		case 'DATETIME':
			return 'datetime';
		case 'TIMESTAMP':
			return 'timestamp';
		case 'SMALLINT':
		case 'INT2':
				return 'smallint';
		case 'BIGINT':
		case 'INTEGER':
		case 'INT8':
		case 'INT4':
			return 'integer';
		case 'OID':
		case 'SERIAL':
			return 'integer';
		default:
			return 'float';
		}
    }
	function OffsetDate($dayFraction,$date=false)
	{		
		if (!$date) $date = $this->sysDate;
		return "($date+interval'$dayFraction days')";
	}
	

	function &MetaColumns($table) 
	{
	global $ADODB_FETCH_MODE;
	
		if (!empty($this->metaColumnsSQL)) { 
			$save = $ADODB_FETCH_MODE;
			$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
			
			$rs = $this->Execute(sprintf($this->metaColumnsSQL,($table)));
			
			$ADODB_FETCH_MODE = $save;
			
			if ($rs === false) return false;
			
			if (!empty($this->metaKeySQL)) {
				
				$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
				
				$rskey = $this->Execute(sprintf($this->metaKeySQL,($table)));
				$keys = $rskey->GetArray();
				
				$ADODB_FETCH_MODE = $save;
				
				$rskey->Close();
				unset($rskey);
			}

			$rsdefa = array();
			if (!empty($this->metaDefaultsSQL)) {
				$sql = sprintf($this->metaDefaultsSQL, ($table));
				$rsdef = $this->Execute($sql);
				if ($rsdef) {
					while (!$rsdef->EOF) {
						$num = $rsdef->Fields('num');
						$s = $rsdef->Fields('def');
						if (substr($s, 0, 1) == "'") { /* quoted strings hack... for now... fixme */
							$s = substr($s, 1);
							$s = substr($s, 0, strlen($s) - 1);
						}
	
						$rsdefa[$num] = $s;
						$rsdef->MoveNext();
					}
				} else {
					ADOConnection::outp( "==> SQL => " . $sql);
				}
				unset($rsdef);
			}
		
			$retarr = array();
			while (!$rs->EOF) { 	
				$fld = new ADOFieldObject();
				$fld->name = $rs->fields[0];
				$fld->type = $this->POSTGRESTypes($rs->fields[1]);
				$fld->max_length = $rs->fields[2];
				if ($fld->max_length <= 0) $fld->max_length = $rs->fields[3]-4;
				if ($fld->max_length <= 0) $fld->max_length = -1;
				$fld->has_default = ($rs->fields[5] == 't');
				if ($fld->has_default) {
					$fld->default_value = $rsdefa[$rs->fields[6]];
				}

				if ($rs->fields[4] == $this->true) {
					$fld->not_null = true;
				}
				
				if (is_array($keys)) {
					reset ($keys);
					while (list($x,$key) = each($keys)) {
						if ($fld->name == $key['column_name'] AND $key['primary_key'] == $this->true) 
							$fld->primary_key = true;
						if ($fld->name == $key['column_name'] AND $key['unique_key'] == $this->true) 
							$fld->unique = true; // What name is more compatible?
					}
				}
				
				$retarr[($fld->name)] = $fld; //Interakt
				
				$rs->MoveNext();
			}
			$rs->Close();
			return $retarr;	
		}
		return false;
	}

	 function &MetaDatabases()
	 {
	 	$arr = array();
	  	$sql="select datname from pg_database";
		$rs = $this->Execute($sql);
		if (!$rs) return false;
		while (!$rs->EOF) {
			$arr[] = reset($rs->fields);
			$rs->MoveNext();
		}
		
		return $arr;
	 }


	function _connect($str,$user='',$pwd='',$db='',$locale='')
	{		   
		if ($user || $pwd || $db) {
		   	if ($str)  {
			 	$host = split(":", $str);
				if ($host[0]) $str = "host=$host[0]";
				else $str = 'localhost';
				if (isset($host[1])) $str .= " port=$host[1]";
			}
		   		if ($user) $str .= " user=".$user;
		   		if ($pwd)  $str .= " password=".$pwd;
			if ($db)   $str .= " dbname=".$db;
		}
		
		//if ($user) $linea = "user=$user host=$linea password=$pwd dbname=$db port=5432";
		$this->_connectionID = pg_connect($str);
		if ($this->_connectionID === false) return false;
			// Interakt
		$this->_setLocale($locale);
		// Interakt
		$this->Execute("set datestyle='ISO'");
				return true;
	}
	
	// returns true or false
	//
	// examples:
	// 	$db->PConnect("host=host1 user=user1 password=secret port=4341");
	// 	$db->PConnect('host1','user1','secret');
	function _pconnect($str,$user='',$pwd='',$db='', $locale='')
	{
		if ($user || $pwd || $db) {
		   		if ($str)  {
			 	$host = split(":", $str);
				if ($host[0]) $str = "host=$host[0]";
				else $str = 'localhost';
				if (isset($host[1])) $str .= " port=$host[1]";
			}
		   		if ($user) $str .= " user=".$user;
		   		if ($pwd)  $str .= " password=".$pwd;
			if ($db)   $str .= " dbname=".$db;
		}//print $str;
		$this->_connectionID = pg_pconnect($str);
		if ($this->_connectionID === false) return false;
		// Interakt
		$this->_setLocale($locale);
		// Interakt
		return true;
	}

	function _query($sql,$inputarr='')
	{
		$rez = pg_Exec($this->_connectionID,$sql);
		// check if no data returned, then no need to create real recordset
		if ($rez && pg_numfields($rez) <= 0) {
			$this->_resultid = $rez;
			return true;
		}
		return $rez;
	}
	
	function ErrorMsg() 
	{
	global $ADODB_PHPVER;
		if ($ADODB_PHPVER >= 0x4300) {
			//if (!empty($this->_resultid)) $this->_errorMsg = @pg_result_error($this->_resultid);
			if (!empty($this->_connectionID)) $this->_errorMsg = @pg_last_error($this->_connectionID);
			else $this->_errorMsg = @pg_last_error();
		} else {
			if (empty($this->_connectionID)) $this->_errorMsg = @pg_errormessage();
			else $this->_errorMsg = @pg_errormessage($this->_connectionID);
		}
		return $this->_errorMsg;
	}
	
	function ErrorNo()
	{
		return (strlen($this->ErrorMsg())) ? -1 : 0;
	}
	function _close()
	{
		if ($this->transCnt) $this->RollbackTrans();
		$this->_resultid = false;
		@pg_close($this->_connectionID);
		$this->_connectionID = false;
		return true;
	}
	
 	function ActualType($meta)
	{
		switch($meta) {
		case 'C': return 'VARCHAR';
		case 'X': return 'TEXT';
		
		case 'C2': return 'VARCHAR';
		case 'X2': return 'TEXT';
		
		case 'B': return 'BYTEA';
			
		case 'D': return 'DATE';
		case 'T': return 'DATETIME';
		case 'L': return 'SMALLINT';
		case 'R': return 'SERIAL';
		case 'I': return 'INTEGER'; 
		
		case 'F': return 'FLOAT8';
		case 'N': return 'NUMERIC';
		default:
			return false;
		}
	}
	function CharMax()
	{
		return 1000000000;  // should be 1 Gb?
	}
	
	function TextMax()
	{
		return 1000000000; // should be 1 Gb?
	}
	/** Interakt
	*  Change the SQL connection locale to a specified locale.
	*  This is used to get the date formats written depending on the client locale.
	*/
	function _setLocale($locale = 'US')
	{	
		switch (strtoupper($locale))
		{
			case 'US':
				$this->Execute("set datestyle='ISO'");
				break;
			case 'EN':
				$this->Execute("set datestyle='SQL,European'");
				break;
			case 'EUS':
				$this->Execute("set datestyle='SQL,US'");
				break;
			case 'RO':
				$this->Execute("set datestyle='SQL,European'");
				break;
			case 'FR':
				$this->Execute("set datestyle='SQL,European'");
				break;
			case 'IT':
				$this->Execute("set datestyle='SQL,European'");
				break;
			case 'GE':
				$this->Execute("set datestyle='SQL,European'");
				break;
			default :
		}
	}
	// Interakt
	
		
}
	
class ADORecordSet_postgres64 extends ADORecordSet{
	var $_blobArr;
	var $databaseType = "postgres64";
	var $canSeek = true;
	function ADORecordSet_postgres64($queryID,$locale='',$mode=false) 
	{
		if ($mode === false) { 
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM: $this->fetchMode = PGSQL_NUM; break;
		case ADODB_FETCH_ASSOC:$this->fetchMode = PGSQL_ASSOC; break;
		default:
		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:$this->fetchMode = PGSQL_BOTH; break;
		}
		$this->ADORecordSet($queryID);
	}
	
	function &GetRowAssoc($upper=true)
	{
		if ($this->fetchMode == PGSQL_ASSOC && !$upper) return $rs->fields;
		return ADORecordSet::GetRowAssoc($upper);
	}

	function _initrs()
	{
	global $ADODB_COUNTRECS;
		$this->_numOfRows = ($ADODB_COUNTRECS)? @pg_numrows($this->_queryID):-1;
		$this->_numOfFields = @pg_numfields($this->_queryID);
		// cache types for blob decode check
		for ($i=0, $max = $this->_numOfFields; $i < $max; $i++) { 
			$f1 = $this->FetchField($i);
			if ($f1->type == 'bytea') $this->_blobArr[$i] = $f1->name;
		}		
	}

		/* Use associative array to get fields array */
	function Fields($colname)
	{
		if ($this->fetchMode != PGSQL_NUM) return @unescapeQuotes($this->fields[$colname]);
		
		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[($o->name)] = $i;
			}
		}
		 return unescapeQuotes($this->fields[$this->bind[($colname)]]);
	}

	function &FetchField($fieldOffset = 0) 
	{
		$off=$fieldOffset; // offsets begin at 0
		
		$o= new ADOFieldObject();
		$o->name = @pg_fieldname($this->_queryID,$off);
		$o->type = @pg_fieldtype($this->_queryID,$off);
		$o->max_length = @pg_fieldsize($this->_queryID,$off);
		return $o;	
	}

	function _seek($row)
	{
		return @pg_fetch_row($this->_queryID,$row);
	}
	
	function _decode($blob)
	{
		
		eval('$realblob="'.adodb_str_replace(array('"','$'),array('\"','\$'),$blob).'";');
		return $realblob;
		
	}
	function _fixblobs()
	{
		if ($this->fetchMode == PGSQL_NUM || $this->fetchMode == PGSQL_BOTH) {
			foreach($this->_blobArr as $k => $v) {
				$this->fields[$k] = ADORecordSet_postgres64::_decode($this->fields[$k]);
			}
		}
		if ($this->fetchMode == PGSQL_ASSOC || $this->fetchMode == PGSQL_BOTH) {
			foreach($this->_blobArr as $k => $v) {
				$this->fields[$v] = ADORecordSet_postgres64::_decode($this->fields[$v]);
			}
		}
	}
	function MoveNext() 
	{
		if (!$this->EOF) {		
			//INTERAKT
			$this->exfields = $this->fields;
			$this->_currentRow++;
			
			$f = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);
			
			if (is_array($f)) {
				$this->fields = $f;
				if (isset($this->_blobArr)) $this->_fixblobs();
				return true;
			}
		}
		$this->EOF = true;
		return false;
	}		
	
	function _fetch()
	{
		$this->fields = @pg_fetch_array($this->_queryID,$this->_currentRow,$this->fetchMode);
		if (isset($this->_blobArr)) $this->_fixblobs();
		return (is_array($this->fields));
	}

	function _close() {
		return @pg_freeresult($this->_queryID);
	}

	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		switch (strtoupper($t)) {
				case 'CHAR':
				case 'CHARACTER':
				case 'VARCHAR':
				case 'NAME':
		   		case 'BPCHAR':
					if ($len <= $this->blobSize) return 'C';
				
				case 'TEXT':
					return 'X';
		
				case 'IMAGE': // user defined type
				case 'BLOB': // user defined type
				case 'BIT':	// This is a bit string, not a single bit, so don't return 'L'
				case 'VARBIT':
				case 'BYTEA':
					return 'B';
				
				case 'BOOL':
				case 'BOOLEAN':
					return 'L';
				
				case 'DATE':
					return 'D';
				
				case 'TIME':
				case 'DATETIME':
				case 'TIMESTAMP':
				case 'TIMESTAMPTZ':
					return 'T';
				
				case 'SMALLINT': 
				case 'BIGINT': 
				case 'INTEGER': 
				case 'INT8': 
				case 'INT4':
				case 'INT2':
					if (isset($fieldobj) &&
				empty($fieldobj->primary_key) && empty($fieldobj->unique)) return 'I';
				
				case 'OID':
				case 'SERIAL':
					return 'R';
				
				 default:
				 	return 'N';
			}
	}

}
?>
