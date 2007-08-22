<?php
// Copyright 2001-2003 Interakt Online. All rights reserved.
class KT_ADODB_sybase extends ADODB_sybase {
 	
	
	/** Interakt
	*  Change the SQL connection locale to a specified locale.
	*  This is used to get the date formats written depending on the client locale.
	*/

	function ErrorMsg(){
		if (!function_exists('sybase_connect')){
				return 'Your PHP doesn\'t contain the Sybase connection module!';
		}
		return parent::ErrorMsg();	
	}
}

class kt_adorecordset_sybase extends ADORecordset_sybase{
	
}
?>
