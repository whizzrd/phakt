/**
*  Copy all newer ADODB files in the adodb root
*/

function installADODB(){
	var adodbRoot = dreamweaver.getSiteRoot()+"adodb/";
	var includesRoot = dreamweaver.getSiteRoot()+"includes/";
	if(!DWfile.exists(includesRoot)) {
		DWfile.createFolder(includesRoot);	
	}
	if(!DWfile.exists(adodbRoot)) {
		DWfile.createFolder(adodbRoot);	
	}
	if(!DWfile.exists(adodbRoot+"drivers/")) {
		DWfile.createFolder(adodbRoot+"drivers/");	
	}
  
	//install ADODB if necessary
	var adodbKit = dreamweaver.getConfigurationPath() + "/Shared/PHP_ADODB/adodb/";
	copyNewerFiles(adodbKit,adodbRoot);
	copyNewerFiles(adodbKit+"drivers/", adodbRoot+"drivers/");

	var includesKit = dreamweaver.getConfigurationPath() + "/Shared/PHP_ADODB/includes/";
	copyNewerFiles(includesKit,includesRoot);

	site.refresh("local");
	site.locateInSite("local",dreamweaver.getSiteRoot() + "adodb");
	site.put("site");
	site.locateInSite("local",dreamweaver.getSiteRoot() + "includes");
	site.put("site");
}

function copyNewerFiles(adodbKit,adodbRoot){
	rawFileList = DWfile.listFolder(adodbKit, "files");
	var i;
	for (i = 0; i < rawFileList.length; i++) {
		//for each candidate file
		if (DWfile.exists(adodbRoot+rawFileList[i])) {
			timeRoot = DWfile.getModificationDate(adodbRoot+rawFileList[i]);
		} else {
			timeRoot = DWfile.getModificationDate(adodbKit+"../PHAkt.gif");
		}
		var timeKit = DWfile.getModificationDate(adodbKit+rawFileList[i]);
		if(timeKit >= timeRoot) {  //copy newer files only
	    	copySucceded = DWfile.copy(adodbKit+rawFileList[i],adodbRoot+rawFileList[i]);
	  }
  }
}


//--------------------------------------------------------------------
// FUNCTION:
//   escapeForDoubleQuotes
//
// DESCRIPTION:
//	 escape the ",$ and \ from a string
//
// ARGUMENTS:
//		text - string - unescaped string
//
// RETURNS:
//   string - escaped string
//--------------------------------------------------------------------
function escapeForDoubleQuotes(text) {
	var tmp = text.replace(/(\\|"|\$)/g,"\\$1");
	return tmp;
}

//--------------------------------------------------------------------
// FUNCTION:
//   unescapeForDoubleQuotes
//
// DESCRIPTION:
//	 unescape the ",$ and \ from a string
//
// ARGUMENTS:
//		text - string - escaped string
//
// RETURNS:
//   string - unescaped string
//--------------------------------------------------------------------
function unescapeForDoubleQuotes(text) {
	var tmp = text.replace(/\\(\\|"|\$)/g,"$1");
	return tmp;
}