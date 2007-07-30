<?php
	class ADORecordSet {

	var $dataProvider = "native";
	var $fields = false; 	/// holds the current row data
	var $blobSize = 64; 	/// any varchar/char field this size or greater is treated as a blob
							/// in other words, we use a text area for editting.
	var $canSeek = false; 	/// indicates that seek is supported
	var $sql; 				/// sql text
	var $EOF = false;		/// Indicates that the current record position is after the last record in a Recordset object. 
	
	var $emptyTimeStamp = '&nbsp;'; /// what to display when $time==0
	var $emptyDate = '&nbsp;'; /// what to display when $time==0
	var $debug = false;
	var $timeCreated=0; 	/// datetime in Unix format rs created -- for cached recordsets

	// Interakt - Begin
	var $types = false;	// holds the current row types
	// Interakt - End
	var $bind = false; 		/// used by Fields() to hold array - should be private?
	var $fetchMode;			/// default fetch mode
	var $connection = false; /// the parent connection
	/*
	 *	private variables	
	 */
	var $_numOfRows = -1;	/** number of rows, or -1 */
	var $_numOfFields = -1;	/** number of fields in recordset */
	var $_queryID = -1;		/** This variable keeps the result link identifier.	*/
	var $_currentRow = -1;	/** This variable keeps the current row in the Recordset.	*/
	var $_closed = false; 	/** has recordset been closed */
	var $_inited = false; 	/** Init() should only be called once */
	var $_obj; 				/** Used by FetchObj */
	var $_names;			/** Used by FetchObj */
	
	var $_currentPage = -1;	/** Added by Iván Oliva to implement recordset pagination */
	var $_atFirstPage = false;	/** Added by Iván Oliva to implement recordset pagination */
	var $_atLastPage = false;	/** Added by Iván Oliva to implement recordset pagination */
	var $_lastPageNo = -1; 
	var $_maxRecordCount = 0;
	var $exfields = false;

	function ADORecordSet($queryID) 
	{
		$this->_queryID = $queryID;
	}
	
	
	
	function Init()
	{
		if ($this->_inited) return;
		$this->_inited = true;
		
		if ($this->_queryID) @$this->_initrs();
		else {
			$this->_numOfRows = 0;
			$this->_numOfFields = 0;
		}
		if ($this->_numOfRows != 0 && $this->_numOfFields && $this->_currentRow == -1) {
			$this->_currentRow = 0;
			$this->EOF = ($this->_fetch() === false);
		} else  
			$this->EOF = true;
	}
	

	function GetArray($nRows = -1) 
	{
		$results = array();
		$cnt = 0;
		while (!$this->EOF && $nRows != $cnt) {
			$results[$cnt++] = $this->fields;
			$this->MoveNext();
		}
		
		return $results;
	}
	function NextRecordSet()
	{
		return false;
	}

	function GetArrayLimit($nrows,$offset=-1) 
	{
		if ($offset <= 0) return $this->GetArray($nrows);
		$this->Move($offset);
		
		$results = array();
		$cnt = 0;
		while (!$this->EOF && $nrows != $cnt) {
			$results[$cnt++] = $this->fields;
			$this->MoveNext();
		}
		
		return $results;
	}
	

	function GetRows($nRows = -1) 
	{
		return $this->GetArray($nRows);
	}

	function Free()
	{
		return $this->Close();
	}
	

	function NumRows()
	{
		return $this->_numOfRows;
	}
	

	function NumCols()
	{
		return $this->_numOfFields;
	}
	

	function FetchRow()
	{
		if ($this->EOF) return false;
		$arr = $this->fields;
		$this->_currentRow++;
		if (!$this->_fetch()) $this->EOF = true;
		return $arr;
	}
	
	
	function FetchInto(&$arr)
	{
		if ($this->EOF) return (defined('PEAR_ERROR_RETURN')) ? new PEAR_Error('EOF',-1): false;
		$arr = $this->fields;
		$this->MoveNext();
		return 1; // DB_OK
	}
	

	function MoveFirst() 
	{
		$this->exfields = false;
		if ($this->_currentRow == 0) return true;
		return $this->Move(0);			
	}			


	function MoveLast() 
	{
		if ($this->_numOfRows >= 0) return $this->Move($this->_numOfRows-1);
				while (!$this->EOF) $this->MoveNext();
		return true;
	}

	function MoveNext() 
	{
		if (!$this->EOF) {
			$this->_currentRow++;
			$this->exfields = $this->fields;//INTERAKT
			if ($this->_fetch()) return true;
		}
		$this->EOF = true;
		return false;
	}	
	
	function Move($rowNumber = 0) 
	{
		if ($rowNumber == $this->_currentRow) return true;
		if ($rowNumber > $this->_numOfRows)
	   		if ($this->_numOfRows != -1) $rowNumber = $this->_numOfRows-1;
   
		if ($this->canSeek) {
			if ($this->_seek($rowNumber)) {
				$this->_currentRow = $rowNumber;
				if ($this->_fetch()) {
					$this->EOF = false;	
								   //  $this->_currentRow += 1;			
					return true;
				}
			} else 
				return false;
		} else {
			if ($rowNumber < $this->_currentRow) return false;
			while (! $this->EOF && $this->_currentRow < $rowNumber) {
				$this->_currentRow++;
				if (!$this->_fetch()) $this->EOF = true;
			}
			return !($this->EOF);
		}
		
		$this->fields = null;	
		$this->EOF = true;
		return false;
	}
	
	function Fields($colname)
	{
		return unescapeQuotes($this->fields[$colname]);
	}
	
	function ExFields($colname){
		if ($this->exfields && isset($this->exfields[$colname])) {
			return $this->exfields[$colname];
		} else {
			return -1;
		}
	}
	
	function FieldHasChanged($field){
		if ($this->exfields) {
			return ($this->Fields($field) != $this->ExFields($field));
		} else {
			return true;
		}
	}
	
	function Close() 
	{
		// free connection object - this seems to globally free the object
		// and not merely the reference, so don't do this...
		// $this->connection = false; 
		if (!$this->_closed) {
			$this->_closed = true;
			return $this->_close();		
		} else
			return true;
	}
	
	function RecordCount() {return $this->_numOfRows;}
	

	function MaxRecordCount()
	{
		return ($this->_maxRecordCount) ? $this->_maxRecordCount : $this->RecordCount();
	}
	
	function RowCount() {return $this->_numOfRows;} 

	function PO_RecordCount($table="", $condition="") {
		
		$lnumrows = $this->_numOfRows;
		// the database doesn't support native recordcount, so we do a workaround
		if ($lnumrows == -1 && $this->connection) {
			IF ($table) {
				if ($condition) $condition = " WHERE " . $condition; 
				$resultrows = &$this->connection->Execute("SELECT COUNT(*) FROM $table $condition");
				if ($resultrows) $lnumrows = reset($resultrows->fields);
			}
		}
		return $lnumrows;
	}

	function CurrentRow() {return $this->_currentRow;}

	function AbsolutePosition() {return $this->_currentRow;}

	function FieldCount() {return $this->_numOfFields;}   


	function &FetchField($fieldoffset) 
	{
		// must be defined by child class
	}	
	function FieldTypesArray()
	{
		$arr = array();
		for ($i=0, $max=$this->_numOfFields; $i < $max; $i++) 
			$arr[] = $this->FetchField($i);
		return $arr;
	}
	
	/**
	* Return the fields array of the current row as an object for convenience.
	* The default case is lowercase field names.
	*
	* @return the object with the properties set to the fields of the current row
	*/
	function &FetchObj()
	{
		return FetchObject(false);
	}
	function &FetchObject($isupper=true)
	{
		if (empty($this->_obj)) {
			$this->_obj = new ADOFetchObj();
			$this->_names = array();
			for ($i=0; $i <$this->_numOfFields; $i++) {
				$f = $this->FetchField($i);
				$this->_names[] = $f->name;
			}
		}
		$i = 0;
		$o = &$this->_obj;
		for ($i=0; $i <$this->_numOfFields; $i++) {
			$name = $this->_names[$i];
			if ($isupper) $n = strtoupper($name);
			else $n = $name;
			
			$o->$n = $this->Fields($name);
		}
		return $o;
	}
	function &FetchNextObj()
	{
		return FetctNextObject(false);
	}
	function &FetchNextObject($isupper=true)
	{
		$o = false;
		if ($this->_numOfRows != 0 && !$this->EOF) {
			$o = $this->FetchObject($isupper);	
			$this->_currentRow++;
			if ($this->_fetch()) return $o;
		}
		$this->EOF = true;
		return $o;
	}
	
	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
	// changed in 2.32 to hashing instead of switch stmt for speed...
	static $typeMap = array(
		'VARCHAR' => 'C',
		'VARCHAR2' => 'C',
		'CHAR' => 'C',
		'C' => 'C',
		'STRING' => 'C',
		'NCHAR' => 'C',
		'NVARCHAR' => 'C',
		'VARYING' => 'C',
		'BPCHAR' => 'C',
		'CHARACTER' => 'C',
		##
		'LONGCHAR' => 'X',
		'TEXT' => 'X',
		'M' => 'X',
		'X' => 'X',
		'CLOB' => 'X',
		'NCLOB' => 'X',
		'LONG' => 'X',
		'LVARCHAR' => 'X',
		##
		'BLOB' => 'B',
		'NTEXT' => 'B',
		'BINARY' => 'B',
		'VARBINARY' => 'B',
		'LONGBINARY' => 'B',
		'B' => 'B',
		##
		'DATE' => 'D',
		'D' => 'D',
		##
		'TIME' => 'T',
		'TIMESTAMP' => 'T',
		'DATETIME' => 'T',
		'TIMESTAMPTZ' => 'T',
		'T' => 'T',
		##
		'BOOLEAN' => 'L', 
		'BIT' => 'L',
		'L' => 'L',
		##
		'COUNTER' => 'R',
		'R' => 'R',
		'SERIAL' => 'R', // ifx
		##
		'INT' => 'I',
		'INTEGER' => 'I',
		'SHORT' => 'I',
		'TINYINT' => 'I',
		'SMALLINT' => 'I',
		'I' => 'I',
		##
		'LONG' => 'N', // interbase is numeric, oci8 is blob
		'BIGINT' => 'N', // this is bigger than PHP 32-bit integers
		'DECIMAL' => 'N',
		'DEC' => 'N',
		'REAL' => 'N',
		'DOUBLE' => 'N',
		'DOUBLE PRECISION' => 'N',
		'SMALLFLOAT' => 'N',
		'FLOAT' => 'N',
		'NUMBER' => 'N',
		'NUM' => 'N',
		'NUMERIC' => 'N',
		'MONEY' => 'N',
		## informix 9.2
		'SQLINT' => 'I', 
		'SQLSERIAL' => 'I', 
		'SQLSMINT' => 'I', 
		'SQLSMFLOAT' => 'N', 
		'SQLFLOAT' => 'N', 
		'SQLMONEY' => 'N', 
		'SQLDECIMAL' => 'N', 
		'SQLDATE' => 'D', 
		'SQLVCHAR' => 'C', 
		'SQLCHAR' => 'C', 
		'SQLDTIME' => 'T', 
		'SQLINTERVAL' => 'N', 
		'SQLBYTES' => 'B', 
		'SQLTEXT' => 'X' 
		);
		
		
		$tmap = false;
		$t = strtoupper($t);
		$tmap = @$typeMap[$t];
		switch ($tmap) {
		case 'C':
			if (!empty($this)) {
				if ($len <= $this->blobSize) return 'C';
			} else if ($len <= 250) {
				return 'C';
			}
			// ok, the char field is too long, return as text field... 
			return 'X';
			
		case 'I':
			if (!empty($fieldobj->primary_key)) return 'R';
			return 'I';
		
		case false:
			return 'N';
			
		case 'B':
			 if (isset($fieldobj->binary)) 
				 return ($fieldobj->binary) ? 'B' : 'X';
			return 'B';
			
		default: 
			if ($t == 'LONG' && $this->dataProvider == 'oci8') return 'B';
			return $tmap;
		}
	}
	
	function _close() {}
	function AbsolutePage($page=-1)
	{
		if ($page != -1) $this->_currentPage = $page;
		return $this->_currentPage;
	}
	
	function AtFirstPage($status=false)
	{
		if ($status != false) $this->_atFirstPage = $status;
		return $this->_atFirstPage;
	}
	
	function LastPageNo($page = false)
	{
		if ($page != false) $this->_lastPageNo = $page;
		return $this->_lastPageNo;
	}
	function AtLastPage($status=false)
	{
		if ($status != false) $this->_atLastPage = $status;
		return $this->_atLastPage;
	}
} // end class ADORecordSet

?>