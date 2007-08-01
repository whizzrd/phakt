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

	function ErrorMsg(){
		if (!function_exists('pg_connect')){
				return 'Your PHP doesn\'t contain the PostgreSQL connection module!';
		}
		return parent::ErrorMsg();
	}
	
	function getProcedureList($schema='all_schema'){
			$KT_sql = "SELECT DISTINCT ON (p.proname) p.proname,p.prosrc AS definition,  pg_catalog.format_type(p.prorettype, NULL) AS return_type, n.nspname AS schema FROM  pg_catalog.pg_proc p LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace WHERE ".($schema=='all_schema' || $schema==''?"":(($schema=='without_system')?"n.nspname != 'pg_catalog' AND ":"n.nspname = '".$schema."' AND "))."p.prorettype <> 'pg_catalog.cstring'::pg_catalog.regtype AND p.proargtypes[0] <> 'pg_catalog.cstring'::pg_catalog.regtype AND NOT p.proisagg AND pg_catalog.pg_function_is_visible(p.oid) ORDER BY p.proname, return_type";

			$rs=parent::Execute($KT_sql);
			$list=false;
			if ($rs && is_object($rs)){
				$i=0;
				while (!$rs->EOF){
					$i++;
					$list[$i]['procedure_catalog'] = '';
					$list[$i]['procedure_schema'] = $rs->Fields('schema');
					$list[$i]['procedure_name'] = $rs->Fields('proname');
					$list[$i]['procedure_type'] = $rs->Fields('return_type');
					$list[$i]['procedure_definition'] = $rs->Fields('definition');
					$list[$i]['procedure_description'] = '';
					$list[$i]['procedure_date_created'] = '';
					$list[$i]['procedure_date_modified'] = '';
					$rs->MoveNext();
				}
			}
			return $list;
	}	
	
	function getProcedureParameters($ProcedureName, $schema='all_schema'){
						$result = false;
						$KT_sql = "SELECT DISTINCT ON (p.proname) p.proname,p.prosrc AS definition, pg_catalog.oidvectortypes(p.proargtypes) AS arguments, pg_catalog.format_type(p.prorettype, NULL) AS return_type, n.nspname AS schema FROM  pg_catalog.pg_proc p LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace WHERE ".($schema=='all_schema' || $schema==''?"":(($schema=='without_system')?"n.nspname != 'pg_catalog' AND ":"n.nspname = '".$schema."' AND "))."p.prorettype <> 'pg_catalog.cstring'::pg_catalog.regtype AND p.proargtypes[0] <> 'pg_catalog.cstring'::pg_catalog.regtype AND NOT p.proisagg AND pg_catalog.pg_function_is_visible(p.oid) AND p.proname='".$ProcedureName."' ORDER BY p.proname, return_type";
						$rs = parent::Execute($KT_sql);
						if ($rs && is_object($rs) && !$rs->EOF){
								$i=0;
								while (!$rs->EOF){
											$arguments = explode(',',$rs->Fields('arguments'));
											if (is_array($arguments)){
													// let's try to get their names from trigger description
													$matches=preg_replace("/DECLARE(.*?)BEGIN.*/ims","$1",$rs->Fields('definition'));
													if (isset($matches)){
															$temp = explode(';',$matches);
													}
													if (isset($temp) && is_array($temp)){
																foreach($temp as $index=>$value){
																		preg_match("/([^\s]+) alias for \\$([0-9]+)/i",$value, $match);
																		if (isset($match[2])){
																				$name[(int)$match[2]-1] = $match[1];
																		}
																}
													}

													foreach($arguments as $key=>$value){
																$i++;
																$result[$i]['procedure_catalog']="";
																$result[$i]['procedure_schema']=$rs->Fields('schema');
																$result[$i]['procedure_name']=$rs->Fields('proname');
																$result[$i]['parameter_name']=((isset($name[$key]) && $name[$key]!='')?$name[$key]:"unknown_".$i);
																$result[$i]['ordinal_position']=$i;
																$result[$i]['parameter_type']="null";
																$result[$i]['parameter_hasdefault']="";
																$result[$i]['parameter_default']="";
																$result[$i]['is_nullable']="";
																$result[$i]['data_type']="";
																$result[$i]['character_maximum_length']="";
																$result[$i]['character_octet_legth']="";
																$result[$i]['numeric_precision']="";
																$result[$i]['numeric_scale']="";
																$result[$i]['description']="";
																$result[$i]['type_name']="";
																$result[$i]['local_type_name']=trim($value);
																$result[$i]['ss_data_type']="";
													}
											}
											$rs->MoveNext();
								}
						}
						
						return $result;
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
