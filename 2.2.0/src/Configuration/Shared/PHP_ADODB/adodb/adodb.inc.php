<?php 
 
 if (!defined('_ADODB_LAYER')) {
 	define('_ADODB_LAYER',1);
	
	//==============================================================================================	
	// CONSTANT DEFINITIONS
	//==============================================================================================	

	define('ADODB_BAD_RS','<p>Bad $rs in %s. Connection or SQL invalid. Try using $connection->debug=true;</p>');
	
	define('ADODB_FETCH_DEFAULT',0);
	define('ADODB_FETCH_NUM',1);
	define('ADODB_FETCH_ASSOC',2);
	define('ADODB_FETCH_BOTH',3);
	
	if (!defined('ADODB_ASSOC_CASE')) define('ADODB_ASSOC_CASE',2); 
	
	// allow [ ] @ and . in table names
	define('ADODB_TABLE_REGEX','([]0-9a-z_\.\@\[-]*)');
	
	if (!defined('MAX_BLOB_SIZE')) define('MAX_BLOB_SIZE',999999); // 900K
	
	if (!defined('ADODB_PREFETCH_ROWS')) define('ADODB_PREFETCH_ROWS',10);

	if (!defined('ADODB_DIR')) define('ADODB_DIR',dirname(__FILE__));
	
	if (strpos(strtoupper(PHP_OS),'WIN') !== false) {
	// on windows, negative timestamps are illegal as of php 4.2.0
		define('TIMESTAMP_FIRST_YEAR',1970);
		$ADODB_CACHE_DIR = 'c:/windows/temp';
	} else {
		$ADODB_CACHE_DIR = '/tmp';
		define('TIMESTAMP_FIRST_YEAR',1904);
	}
	
	//==============================================================================================	
	// GLOBAL VARIABLES
	//==============================================================================================	

	GLOBAL 
		$ADODB_PHPVER,
		$ADODB_vers, 		// database version
		$ADODB_Database, 	// last database driver used
		$ADODB_COUNTRECS,	// count number of records returned - slows down query
		$ADODB_CACHE_DIR,	// directory to cache recordsets
	 	$ADODB_FETCH_MODE,	// DEFAULT, NUM, ASSOC or BOTH. Default follows native driver default...
	 	$temporizare,
	 	$TEMP_FILE,
	 	$TEMP_TABLE;	
	
	//==============================================================================================	
	// GLOBAL SETUP
	//==============================================================================================	
	
	if (strnatcmp(PHP_VERSION,'4.3.0')>=0) {
		$ADODB_PHPVER = 0x4300;
	} else if (strnatcmp(PHP_VERSION,'4.2.0')>=0) {
		$ADODB_PHPVER = 0x4200;
	} else if (strnatcmp(PHP_VERSION,'4.0.5')>=0) {
		$ADODB_PHPVER = 0x4050;
	} else {
		$ADODB_PHPVER = 0x4000;
	}

	
	/**
	 	Accepts $src and $dest arrays, replacing string $data
	*/
	function ADODB_str_replace($src, $dest, $data)
	{
	global $ADODB_PHPVER;
	
		if ($ADODB_PHPVER >= 0x4050) return str_replace($src,$dest,$data);
		
		$s = reset($src);
		$d = reset($dest);
		while ($s !== false) {
			$data = str_replace($s,$d,$data);
			$s = next($src);
			$d = next($dest);
		}
		return $data;
	}
	
	function ADODB_Setup()
	{
	GLOBAL 
		$ADODB_vers, 		// database version
		$ADODB_Database, 	// last database driver used
		$ADODB_COUNTRECS,	// count number of records returned - slows down query
		$ADODB_CACHE_DIR,	// directory to cache recordsets
	 	$ADODB_FETCH_MODE,
		$temporizare,
	 	$TEMP_FILE,
	 	$TEMP_TABLE;
		
		$ADODB_FETCH_MODE = ADODB_FETCH_DEFAULT;
	$temporizare = "none";
	$TEMP_FILE = "query_time_log";
	$TEMP_TABLE = "querytime_qtm";	
		if (!isset($ADODB_CACHE_DIR)) {
			$ADODB_CACHE_DIR = 'c:/windows/temp';
		} else {
			// do not accept url based paths, eg. http:/ or ftp:/
			if (strpos($ADODB_CACHE_DIR,'://') !== false) 
				die("Illegal path http:// or ftp://");
		}
		
			
		// Initialize random number generator for randomizing cache flushes
		srand(((double)microtime())*1000000);
		
		/**
		 * Name of last database driver loaded into memory. Set by ADOLoadCode().
		 */
		$ADODB_Database = '';
		
		/**
		 * ADODB version as a string.
		 */
		$ADODB_vers = 'V2.91 3 Jan 2003 (c) 2000-2003 John Lim (jlim@natsoft.com.my). All rights reserved. Released BSD & LGPL.';
	
		$ADODB_COUNTRECS = true; 
	}
	
	
	//==============================================================================================	
	// CHANGE NOTHING BELOW UNLESS YOU ARE CODING
	//==============================================================================================	
	
	ADODB_Setup();

	//==============================================================================================	
	// CLASS ADOFieldObject
	//==============================================================================================	
	/**
	 * Helper class for FetchFields -- holds info on a column
	 */
	class ADOFieldObject { 
		var $name = '';
		var $max_length=0;
		var $type="";

		// additional fields by dannym... (danny_milo@yahoo.com)
		var $not_null = false; 
		// actually, this has already been built-in in the postgres, fbsql AND mysql module? ^-^
		// so we can as well make not_null standard (leaving it at "false" does not harm anyways)

		var $has_default = false; // this one I have done only in mysql and postgres for now ... 
			// others to come (dannym)
		var $default_value; // default, if any, and supported. Check has_default first.
	}
	
	
	//==============================================================================================	
	// CLASS ADOConnection
	//==============================================================================================	
	
	include_once(ADODB_DIR.'/adodb-connection.inc.php');
	
	
	
	//==============================================================================================	
	// CLASS ADOFetchObj
	//==============================================================================================	
		
	/**
	* Internal placeholder for record objects. Used by ADORecordSet->FetchObj().
	*/
	class ADOFetchObj {
	};
	
	//==============================================================================================	
	// CLASS ADORecordSet_empty
	//==============================================================================================	
	
	/**
	* Lightweight recordset when there are no records to be returned
	*/
	class ADORecordSet_empty
	{
		var $dataProvider = 'empty';
		var $EOF = true;
		var $_numOfRows = 0;
		var $fields = false;
		var $connection = false;
		function RowCount() {return 0;}
		function RecordCount() {return 0;}
		function PO_RecordCount(){return 0;}
		function Close(){return true;}
		function FetchRow() {return false;}
		function FieldCount(){ return 0;}
	}
	
	//==============================================================================================	
	// CLASS ADORecordSet
	//==============================================================================================	
	
	include_once(ADODB_DIR.'/adodb-recordset.inc.php');
	
	//==============================================================================================	
	// CLASS ADORecordSet_array
	//==============================================================================================	
	
	/**
	 * This class encapsulates the concept of a recordset created in memory
	 * as an array. This is useful for the creation of cached recordsets.
	 * 
	 * Note that the constructor is different from the standard ADORecordSet
	 */
	
	class ADORecordSet_array extends ADORecordSet
	{
		var $databaseType = 'array';

		var $_array; 	// holds the 2-dimensional data array
		var $_types;	// the array of types of each column (C B I L M)
		var $_colnames;	// names of each column in array
		var $_skiprow1;	// skip 1st row because it holds column names
		var $_fieldarr; // holds array of field objects
		var $canSeek = true;
		var $affectedrows = false;
		var $insertid = false;
		var $sql = '';
		/**
		 * Constructor
		 *
		 */
		function ADORecordSet_array($fakeid=1)
		{
		global $ADODB_FETCH_MODE;
		
			$this->ADORecordSet($fakeid); // fake queryID		
			$this->fetchMode = $ADODB_FETCH_MODE;
		}
		
		
		/**
		 * Setup the Array. Later we will have XML-Data and CSV handlers
		 *
		 * @param array		is a 2-dimensional array holding the data.
		 *			The first row should hold the column names 
		 *			unless paramter $colnames is used.
		 * @param typearr	holds an array of types. These are the same types 
		 *			used in MetaTypes (C,B,L,I,N).
		 * @param [colnames]	array of column names. If set, then the first row of
		 *			$array should not hold the column names.
		 */
		function InitArray($array,$typearr,$colnames=false)
		{
			$this->_array = $array;
			$this->_types = $typearr;	
			if ($colnames) {
				$this->_skiprow1 = false;
				$this->_colnames = $colnames;
			} else $this->_colnames = $array[0];
			
			$this->Init();
		}
		/**
		 * Setup the Array and datatype file objects
		 *
		 * @param array		is a 2-dimensional array holding the data.
		 *			The first row should hold the column names 
		 *			unless paramter $colnames is used.
		 * @param fieldarr	holds an array of ADOFieldObject's.
		 */
		function InitArrayFields(&$array,&$fieldarr)
		{
			$this->_array = &$array;
			$this->_skiprow1= false;
			if ($fieldarr) {
				$this->_fieldobjects = &$fieldarr;
			} 
			
			$this->Init();
		}
		
		function _initrs()
		{
			$this->_numOfRows =  sizeof($this->_array);
			if ($this->_skiprow1) $this->_numOfRows -= 1;
		
			$this->_numOfFields =(isset($this->_fieldobjects)) ?
				 sizeof($this->_fieldobjects):sizeof($this->_types);
		}
		
		/* Use associative array to get fields array */
		function Fields($colname)
		{
			if ($this->fetchMode & ADODB_FETCH_ASSOC) return $this->fields[$colname];
	
			if (!$this->bind) {
				$this->bind = array();
				for ($i=0; $i < $this->_numOfFields; $i++) {
					$o = $this->FetchField($i);
					$this->bind[strtoupper($o->name)] = $i;
				}
			}
			return $this->fields[$this->bind[strtoupper($colname)]];
		}
		
		function &FetchField($fieldOffset = -1) 
		{
			if (isset($this->_fieldobjects)) {
				return $this->_fieldobjects[$fieldOffset];
			}
			$o =  new ADOFieldObject();
			$o->name = $this->_colnames[$fieldOffset];
			$o->type =  $this->_types[$fieldOffset];
			$o->max_length = -1; // length not known
			
			return $o;
		}
			
		function _seek($row)
		{
			if (sizeof($this->_array) && $row < $this->_numOfRows) {
				$this->fields = $this->_array[$row];
				return true;
			}
			return false;
		}
		
		function _fetch()
		{
			$pos = $this->_currentRow;
			
			if ($this->_skiprow1) {
				if ($this->_numOfRows <= $pos-1) return false;
				$pos += 1;
			} else {
				if ($this->_numOfRows <= $pos) return false;
			}
			
			$this->fields = $this->_array[$pos];
			return true;
		}
		
		function _close() 
		{
			return true;	
		}
	
	} // ADORecordSet_array
	
	//==============================================================================================	
	// HELPER FUNCTIONS
	//==============================================================================================			
	
	/**
	 * Synonym for ADOLoadCode.
	 *
	 * @deprecated
	 */
	function ADOLoadDB($dbType) 
	{ 
		return ADOLoadCode($dbType);
	}
		
	/**
	 * Load the code for a specific database driver
	 */
	function ADOLoadCode($dbType) 
	{
	GLOBAL $ADODB_Database;
	
		if (!$dbType) return false;
		$ADODB_Database = strtolower($dbType);
		switch ($ADODB_Database) {
			case 'maxsql': $ADODB_Database = 'mysqlt'; break;
			case 'pgsql': $ADODB_Database = 'postgres7'; break;
		}
		// Karsten Kraus <Karsten.Kraus@web.de> 
		return @include_once(ADODB_DIR."/drivers/adodb-".$ADODB_Database.".inc.php");		
	}

	/**
	 * synonym for ADONewConnection for people like me who cannot remember the correct name
	 */
	function &NewADOConnection($db='')
	{
		return ADONewConnection($db);
	}
	
	/**
	 * Instantiate a new Connection class for a specific database driver.
	 *
	 * @param [db]  is the database Connection object to create. If undefined,
	 * 	use the last database driver that was loaded by ADOLoadCode().
	 *
	 * @return the freshly created instance of the Connection class.
	 */
	function &ADONewConnection($db='')
	{
	GLOBAL $ADODB_Database;
		
		$rez = true;
		if ($db) {
			if ($ADODB_Database != $db) ADOLoadCode($db);
		} else { 
			if (!empty($ADODB_Database)) {
				ADOLoadCode($ADODB_Database);
			} else {
				 $rez = false;
			}
		}
		
		$errorfn = (defined('ADODB_ERROR_HANDLER')) ? ADODB_ERROR_HANDLER : false;
		if (!$rez) {
			 if ($errorfn) {
				// raise an error
				$errorfn('ADONewConnection', 'ADONewConnection', -998,
						 "could not load the database driver for '$db",
						 $dbtype);
			} else
				 ADOConnection::outp( "<p>ADONewConnection: Unable to load database driver '$db'</p>",false);
				
			return false;
		}
		
		$cls = 'ADODB_'.$ADODB_Database;
		$obj = new $cls();
		if ($errorfn) {
			$obj->raiseErrorFn = $errorfn;
		}
		return $obj;
	}
	
	
} // defined



  /* InterAKT
   * Converts a date from a format into another format
   * @param date  - string containg a date to convert
   * @param inFmt - format of the date to convert
   * @param outFmt- format of the outputed data
   *
   * @return date in the specified format
   */
	function KT_ADOconvertDate($date, $inFmt, $outFmt) {
		if(ereg("^[0-9]+[/|-][0-9]+[/|-][0-9]+$", $date)) {
			$outFmt = eregi_replace(" +.+$", "", $outFmt);
		}
		if (ereg ("%d[/|-]%m[/|-]%Y %H:%M:%S", $inFmt)) {
			if(ereg ("([0-9]{1,2})[/|-]([0-9]{1,2})[/|-]([0-9]{4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
				for ($i=1;$i<7;$i++) {
					if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
				}
				$outdate = $outFmt;
				$outdate = ereg_replace("%Y",$regs[3],$outdate);
				$outdate = ereg_replace("%m",$regs[2],$outdate);
				$outdate = ereg_replace("%d",$regs[1],$outdate);
				$outdate = ereg_replace("%H",$regs[4],$outdate);
				$outdate = ereg_replace("%M",$regs[5],$outdate);
				$outdate = ereg_replace("%S",$regs[6],$outdate);
			} else {
				$outdate = $date;
			}
		} else if (ereg ("%Y[/|-]%m[/|-]%d %H:%M:%S", $inFmt)) {
			if(ereg ("([0-9]{4})[/|-]([0-9]{1,2})[/|-]([0-9]{1,2}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
				for ($i=1;$i<7;$i++) {
					if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
				}
				$outdate = $outFmt;
				$outdate = ereg_replace("%Y",$regs[1],$outdate);
				$outdate = ereg_replace("%m",$regs[2],$outdate);
				$outdate = ereg_replace("%d",$regs[3],$outdate);
				$outdate = ereg_replace("%H",$regs[4],$outdate);
				$outdate = ereg_replace("%M",$regs[5],$outdate);
				$outdate = ereg_replace("%S",$regs[6],$outdate);
			} else {
					$outdate = $date;
			}
		} else if (ereg ("%m[/|-]%d[/|-]%Y %H:%M:%S", $inFmt)) {
				if(ereg ("([0-9]{1,2})[/|-]([0-9]{1,2})[/|-]([0-9]{2,4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
					for ($i=1;$i<7;$i++) {
						if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
					}
					$outdate = $outFmt;
					$outdate = ereg_replace("%Y",$regs[3],$outdate);
					$outdate = ereg_replace("%m",$regs[1],$outdate);
					$outdate = ereg_replace("%d",$regs[2],$outdate);
					$outdate = ereg_replace("%H",$regs[4],$outdate);
					$outdate = ereg_replace("%M",$regs[5],$outdate);
					$outdate = ereg_replace("%S",$regs[6],$outdate);
			} else {
				$outdate = $date;
			}

		} else {
   		$outdate = "00-00-00";
		}      
		return $outdate;
	}

	/*
	NAME:
		unescapeQuotes
	DESCRIPTION:
		if the magic_quotes_runtime are on unescape the text
		ADDED BY IAKT!
	PARAMETERS:
		$text - string - escaped string
	RETURN:
		string - unescaped string
	*/
	function unescapeQuotes($text) {
		if (get_magic_quotes_runtime()) {
			return stripslashes($text);
		} else {
			return $text;
		}
	}

	// InterAKT

?>
