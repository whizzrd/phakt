<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.
class KT_ADODB_mssql extends ADODB_mssql {

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
	function _setLocale($locale = 'US') {
		$this->locale=strtoupper($locale);
		switch (strtoupper($locale)) {
			case 'US':
				$this->Execute("SET LANGUAGE 'English'");
				$this->Execute('SET DATEFORMAT ymd');
				$this->Execute('SET DATEFIRST 7');
				break;
			case 'EN':
				$this->Execute("SET LANGUAGE 'English'");
				$this->Execute('SET DATEFORMAT dmy');
				$this->Execute('SET DATEFIRST 1');
				break;
			case 'EUS':
				$this->Execute("SET LANGUAGE 'English'");
				$this->Execute('SET DATEFORMAT mdy');
				$this->Execute('SET DATEFIRST 1');
				break;
			case 'RO':
				$this->Execute("SET LANGUAGE 'Romanian'");
				$this->Execute('SET DATEFORMAT dmy');
				$this->Execute('SET DATEFIRST 1');
				break;
			case 'FR':
				$this->Execute("SET LANGUAGE 'French'");
				$this->Execute('SET DATEFORMAT dmy');
				$this->Execute('SET DATEFIRST 1');
				break;
			case 'GE':
				$this->Execute("SET LANGUAGE 'German'");
				$this->Execute('SET DATEFORMAT dmy');
				$this->Execute('SET DATEFIRST 1');
				break;
			case 'IT':
				$this->Execute("SET LANGUAGE 'Italian'");
				$this->Execute('SET DATEFORMAT dmy');
				$this->Execute('SET DATEFIRST 1');
				break;
			default :
		}
	}
	
		
}

/*
	by InterAKT
	extends base class to implement FieldHasChange method
*/
class KT_ADORecordset_mssql extends ADORecordset_mssql {
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