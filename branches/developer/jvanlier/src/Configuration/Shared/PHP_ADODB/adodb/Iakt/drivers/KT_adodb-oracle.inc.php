<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.
class KT_ADODB_oracle extends ADODB_oracle {

	function Connect($str,$user='',$pwd='',$db='',$locale='')
	{		   
		if (!function_exists('ora_plogon')){
				return false;
		}
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
	function _setLocale($locale = 'Us')
	{
		switch (strtoupper($locale))
		{
			case 'EN':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='DD-MM-YYYY'");
				break;
			case 'US':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD'");
				break;
			case 'EUS':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='MM-DD-YYYY'");
				break;
			case 'RO':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='DD-MM-YYYY'");
				break;
			case 'FR':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='DD-MM-YYYY'");
				break;
			case 'IT':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='DD-MM-YYYY'");
				break;
			case 'GE':
				$this->Execute("ALTER SESSION SET NLS_DATE_FORMAT='DD.MM.YYYY'");
				break;
			default :
			
		}
	}
	function ErrorMsg(){
		if (!function_exists('ora_plogon')){
					return 'Your PHP doesn\'t contain the Oracle connection module!';
		}
		return parent::ErrorMsg(); 
	}	
	
		
}
?>