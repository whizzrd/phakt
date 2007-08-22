<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.

//Added by Interakt
//New Connection class
//this will implement FieldHasChange for 

global $ADODB_NEWCONNECTION;
$ADODB_NEWCONNECTION = 'KTNEWConnection';

function &KTNEWConnection($db='') {
	GLOBAL $ADODB_LASTDB;
	if (!defined('ADODB_ASSOC_CASE')){
			define('ADODB_ASSOC_CASE',2);
	}
	$errorfn = (defined('ADODB_ERROR_HANDLER')) ? ADODB_ERROR_HANDLER : false;
	$rez = true;
	if ($db) {
		if ($ADODB_LASTDB != $db){
				ADOLoadCode($db);
		}
	} else { 
		if (!empty($ADODB_LASTDB)) {
			ADOLoadCode($ADODB_LASTDB);
		} else {
			 $rez = false;
		}
	}
	
	if (!$rez) {
		 if ($errorfn) {
			// raise an error
			$errorfn('ADONewConnection', 'ADONewConnection', -998,
					 "Could not load the database driver for $db",
					 $dbtype);
		} else{
			 ADOConnection::outp( "<p>ADONewConnection: Unable to load database driver for '$db'</p>",false);
		}
		return false;
	}
	
	
	$cls = 'ADODB_'.$ADODB_LASTDB;
	
	//check clases
	$KT_Derived_Conn = array('mysql','postgres7','ibase','mssql','mysqlt','oracle','postgres64', 'sybase_ase', 'sybase');
	if (in_array($ADODB_LASTDB,$KT_Derived_Conn)) {
		$cls = 'KT_' . $cls;
		include_once(ADODB_DIR."/Iakt/drivers/KT_adodb-".$ADODB_LASTDB.".inc.php");
	}
	
	$obj =& new $cls();
	
	//check recordsets
	
	$KT_Derived_Rec = array('mysql','postgres7','mssql','mssqlpo','mysqlt','oci8','oci8po','odbc','postgres64');
	if (in_array($ADODB_LASTDB,$KT_Derived_Conn)) {
		$obj->rsPrefix = "KT_ADORecordSet_";
	}
	
	if ($errorfn){
                         $obj->raiseErrorFn = $errorfn;
	}
	
	return $obj;
}


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
			if(ereg ("([0-9]{1,2})[/\.-]([0-9]{1,2})[/\.-]([0-9]{2,4})*([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
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
			if(ereg ("([0-9]{4})[/\.-]([0-9]{1,2})[/\.-]([0-9]{1,2}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
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
				if(ereg ("([0-9]{1,2})[/\.-]([0-9]{1,2})[/\.-]([0-9]{2,4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
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
?>
