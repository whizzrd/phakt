<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.
class KT_ADODB_ibase extends ADODB_ibase {

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
	function _setLocale($locale = 'Us') {
		switch ($locale) {
			case 'En':
				ibase_timefmt('%d-%m-%Y');
				break;
			case 'EUS':
				ibase_timefmt('%m-%d-%Y');
				break;
			case 'Ro':
				ibase_timefmt('%d-%m-%Y');
				break;
			case 'Fr':
				ibase_timefmt('%d-%m-%Y');
				break;
			case 'It':
				ibase_timefmt('%d-%m-%Y');
				break;
			case 'Ge':
				ibase_timefmt('%d.%m.%Y');
				break;
			case 'Us':
				ibase_timefmt('%Y-%m-%d');
				break;
			default :
                ibase_timefmt('%Y-%m-%d');
                break;
		}
	}
	
		
}
?>