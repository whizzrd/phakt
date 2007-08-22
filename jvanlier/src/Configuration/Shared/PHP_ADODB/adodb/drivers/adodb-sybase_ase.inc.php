<?php
/* 
Created by InterAKT (www.interakt.ro)
*/
@require_once(ADODB_DIR.'/drivers/adodb-sybase.inc.php');

class ADODB_sybase_ase extends ADODB_sybase{
	var $databaseType = "sybase_ase";	
}	
/*--------------------------------------------------------------------------------------
	 Class Name: Recordset
--------------------------------------------------------------------------------------*/

class ADORecordset_sybase_ase extends ADORecordset_sybase{	
	var $databaseType = "sybase_ase";
}

class ADORecordSet_array_sybase_ase extends  ADORecordSet_array_sybase{
}
?>
