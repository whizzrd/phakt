<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.

class KT_ADODB_postgres7 extends ADODB_postgres7 {

	function Connect($str,$user='',$pwd='',$db='',$locale='')
	{		   
		$ret = parent::Connect($str,$user,$pwd,$db);
			// Interakt
		$this->_setLocale($locale);
		
		return $ret;
	}
	

	function PConnect($str,$user='',$pwd='',$db='',$locale='')
	{		   
		$ret = parent::PConnect($str,$user,$pwd,$db);
			// Interakt
		$this->_setLocale($locale);
		
		return $ret;
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
				$this->Execute("set datestyle='SQL,German'");
				break;
			default :
		}
	}

	/**
	 * @return  the last inserted ID. Not all databases support this.
	 */ 
		function Insert_ID($pKeyCol="",$table="")
		{
				if ($this->hasInsertID) return $this->_insertid($pKeyCol,$table);
				if ($this->debug) ADOConnection::outp( '<p>Insert_ID error</p>');
				return false;
		}
	

	function _query($sql,$inputarr="")
	{
		return parent::_query($sql,$inputarr);
	}


	function _insertid($pKeyCol, $table) {
		$lastId = -1;
		if (version_compare(phpversion(),'4.2.0','>=')) {
			$oid = pg_last_oid($this->_resultid);
		} else {
			$oid = pg_getlastoid($this->_resultid);
		}
		if (!$oid) {
			return false;
		}
		if (!$pKeyCol) {
			$query = 'SELECT ic.relname AS index_name, bc.relname AS tab_name, ta.attname AS column_name, 
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
		if ($pKeyCol) {
			$qId = $this->_query("select ".$pKeyCol." from ".$table." where oid = ".$oid);
			$rs = new ADORecordSet_postgres64($qId);
			$rs->Init();
			$lastId = $rs->Fields(0);
		} else {
			$lastIs = -1;
		}
		return $lastId;
	} 


	// get the last id - never tested
	function pg_insert_id($tablename,$fieldname)
	{
		if (!is_resource($this->_resultid)) {
			return false;
		}
		$oid = pg_getlastoid($this->_resultid);
		$result=pg_exec($this->_connectionID, "SELECT $fieldname FROM $tablename where oid=$oid");
		if ($result) {
			$arr = @pg_fetch_row($result,0);
			pg_freeresult($result);
			if (isset($arr[0])) return $arr[0];
		}
		return false;
	}

		
}



/*
	by InterAKT
	extends base class to implement FieldHasChange method
*/
class KT_ADORecordSet_postgres7 extends ADORecordSet_postgres7 {
	var $exfields = false; //copy of the fields

	function MoveFirst() 
	{
		//reset fields
		$this->exfields = false;
		parent::MoveFirst();
	}

	function MoveNext() 
	{
		//save the old fields before moving further
		if (!$this->EOF) {
			$this->exfields = $this->fields;//INTERAKT
		}
		return parent::MoveNext();
	}

	/*
		return the old value of a field
		@param
		$colname - the name of the field
		
		@return
		field value
	*/
	function ExFields($colname){
		if ($this->exfields && isset($this->exfields[$colname])) {
			return $this->exfields[$colname];
		} else {
			return null;
		}
	}
	
	/*
		check if the specific field has changed his value on MoveNext
		@param
			$field - the name of the field that we want to watch for
		@return
			boolean true if the field has changed from the previos value , false otherwise
	*/
	function FieldHasChanged($field){
		if ($this->exfields) {
			return ($this->Fields($field) != $this->ExFields($field));
		} else {
			return true;
		}
	}

}

?>