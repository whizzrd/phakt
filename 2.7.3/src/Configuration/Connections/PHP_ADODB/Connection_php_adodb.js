// Copyright 2001-2002 Macromedia, Inc. All rights reserved.

// *************** GLOBALS VARS *****************

var HELP_DOC = MM.HELP_connPHP;

var PARTICIPANT_FILE = "connection_includefile";

var CONN_NAME_OBJ;
var HOST_NAME_OBJ;
var DB_NAME_OBJ;
var DB_TYPE_OBJ;
var USERNAME_OBJ;
var PASSWORD_OBJ;
var CONN_TYPE_OBJ;
var DSN_NAME_OBJ;
var DSN_LIST = new Array();

var msglocaleObj, connTypeObj;
var localeObj;

var USE_HTTP = true;
var KT_copiedADODB = false;

var MSG_WantsHostName = 
   "You have not specified a hostname. Most production environments\n" +
   "require a host name for database connectivity.\n";
var MSG_WantsUserName =
   "You have not specified a username. Most production environments\n" +
   "require a username for database connectivity.\n";
var MSG_WantsPassword =
   "You have not specified a password. Most production environments\n" +
   "require a password for database connectivity.\n";
var MSG_RequiresDatabase = 
   "Please specify a Database. Most production environments\n" +
   "require a database name.\n" ;

// ******************* API **********************

//--------------------------------------------------------------------
// FUNCTION:
//   commandButtons
//
// DESCRIPTION:
//   Returns the array of buttons that should be displayed on the
//   right hand side of the connection dialog.  The array is comprised
//   of name, handler function name pairs.
//
//   Note: the handler functions for OK and Cancel are left blank,
//   because these are handled automatically by the Conection dialog
//   API.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   array of strings - name, handler function name pairs
//--------------------------------------------------------------------

function commandButtons()
{
  return new Array(MM.BTN_OK,     "", 
                   MM.BTN_Cancel, "", 
                   MM.BTN_Test,   "clickedTest()", 
                   MM.BTN_Help,   "displayHelp()")
}

function KT_dbtypeChanged(){
    var KT_dbtype = DB_TYPE_OBJ.object.options[DB_TYPE_OBJ.object.selectedIndex].value;
    switch (KT_dbtype) {
    	case "access" :
    	case "odbc" :
    	case "ado" :
			HOST_NAME_OBJ.disabled = true;
    		break;
    	default:
			HOST_NAME_OBJ.disabled = false;
    }
}

//--------------------------------------------------------------------
// FUNCTION:
//   findConnection
//
// DESCRIPTION:
//   Returns a JavaScript object which indicates the parameters
//   found in the given connection file text.  If no parameters
//   are found, null is returned.
//
// ARGUMENTS:
//   text - string - the text of a connection file
//
// RETURNS:
//   JavaScript object - connection parameters
//--------------------------------------------------------------------

function findConnection(text)
{
  var part = new Participant(PARTICIPANT_FILE);
  var connParams = part.findInString(text);
  
  if (connParams != null)
  {
    if (dwscripts.IS_MAC)  // only use http connectivity on the mac
    {
      connParams.http = "true";
    }

    if (connParams.designtimeType == undefined) // Migrate from v4 to v5
    {
        connParams.designtimeType = connParams.type;
        connParams.type = "ADODB";
    }

    // specify the include statement that's used to include this connection
    connParams.includePattern = 
        "/<\\?php\\s+iakt(require|include)(_once)?\\([\"']([^'\"]*)Connections\\/" + connParams.cname +  "\\.php[\"']\\);?\\s*\\?>/"
    if (connParams.http == "true")
    {
      connParams.usesDesigntimeInfo = false;
      connParams.string = "type=ADODB;host=" + connParams.hostname + ";db="  + connParams.databasename + ";uid=" + connParams.username + ";pwd=" + connParams.password + ";";
    }
    else
    {
      connParams.usesDesigntimeInfo = true;
    }

    // specify the variables that are defined in the connection file
    connParams.variables = new Object();
    connParams.variables["$hostname_" + connParams.cname] = '"' + connParams.hostname + '"';
    connParams.variables["$database_" + connParams.cname] = '"' + connParams.databasename + '"';
    connParams.variables["$username_" + connParams.cname] = '"' + connParams.username + '"';
    connParams.variables["$password_" + connParams.cname] = '"' + connParams.password + '"';
    connParams.variables["$" + connParams.cname] = "$" + connParams.cname;
  }

  return connParams;
}


//--------------------------------------------------------------------
// FUNCTION:
//   applyConnection
//
// DESCRIPTION:
//   Returns the code that should be inserted into the connection
//   include file.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   string - connection code
//--------------------------------------------------------------------

function applyConnection()
{
  var code = "";
  
  if (isValid())
  {  
	if (!KT_copiedADODB) {
		//install ADODB files in the site root
		installADODB();
		KT_copiedADODB = true;
	}

    // build tokens array
    connParams = new Object();
    connParams.cname = dwscripts.trim(CONN_NAME_OBJ.value);
    connParams.hostname = dwscripts.trim(HOST_NAME_OBJ.value);
    connParams.username = dwscripts.trim(USERNAME_OBJ.value);
    connParams.password = escapeForDoubleQuotes(PASSWORD_OBJ.value);
    connParams.databasename = dwscripts.trim(DB_TYPE_OBJ.object.options[DB_TYPE_OBJ.object.selectedIndex].value)+":"+dwscripts.trim(DB_NAME_OBJ.value);
    connParams.filename = "Connection_php_adodb.htm";
    connParams.dbtype = DB_TYPE_OBJ.object.options[DB_TYPE_OBJ.object.selectedIndex].value;
    connParams.QUBCaching = "false";
    connParams.type = "ADODB";

	connParams.locale = document.theForm.locale.options[document.theForm.locale.selectedIndex].value;
  	connParams.msglocale = document.theForm.msglocale.options[document.theForm.msglocale.selectedIndex].value;
  	connParams.ctype = document.theForm.ctype.options[document.theForm.ctype.selectedIndex].value;


		//QUB Caching params
		if (document.theForm.QUBCaching) {
			connParams.QUBCaching = (document.theForm.QUBCaching.checked ? "true" : "false");
		} else {
			connParams.QUBCaching = false;
		}
		
		if (USE_HTTP)
    {
      connParams.http = "true";
      connParams.designtimeType = "ADODB";
      connParams.designtimeString = "";
    }
    else
    {
      connParams.http = "false";
      connParams.designtimeType = "ADO";

      // var dsn = DSN_NAME_OBJ.object.value;
      // JALBANO: connParams.designtimeString = "\"" + buildDSNConnectionString(dsn) + "\"";
      // connParams.designtimeString = buildDSNConnectionString(dsn, connParams.username, connParams.password);
    }

    var part = new Participant(PARTICIPANT_FILE);

    code = part.getInsertString(connParams);
  }
  
  return code;
}


//--------------------------------------------------------------------
// FUNCTION:
//   inspectConnection
//
// DESCRIPTION:
//   Set the UI controls based on the given connection parameters.
//   This function is called after initializeUI.
//
//   NOTE: This function does not work like the other JavaScript API
//     inspect functions.  This function is always called, even 
//     for a new connection, and in this case, it is used to clear 
//     the form element fields.
//
// ARGUMENTS:
//   connParams - object - connection record returned from findConnection
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function inspectConnection(connParams)
{ 
/*
  if (connParams.updateExisting)
  {
    USE_HTTP = (connParams.http == "true");
    if (dwscripts.IS_MAC)
    {
      USE_HTTP = true;
    }
  }
*/
	testServerScripts();
  CONN_NAME_OBJ.value = connParams.name;
  HOST_NAME_OBJ.value = (typeof(connParams.hostname) == "undefined") ? "" : connParams.hostname;
  DB_NAME_OBJ.value = (typeof(connParams.databasename) == "undefined") ? "" : connParams.databasename.replace(/^.*:/, "");
  var temp = ((typeof(connParams.databasename) == "undefined") ? "mysql" : connParams.databasename.replace(/:.*$/, ""));
  for (i=0;i<DB_TYPE_OBJ.object.options.length;i++) {
  	if (DB_TYPE_OBJ.object.options[i].value == temp) {
  		DB_TYPE_OBJ.object.selectedIndex = i;
  		break;
  	}
  }
  if (i==DB_TYPE_OBJ.object.options.length) {
  	DB_TYPE_OBJ.object.selectedIndex = 0;
  }
  //DB_TYPE_OBJ.object.value = ((typeof(connParams.databasename) == "undefined") ? "mysql" : connParams.databasename.replace(/:.*$/, ""));
  USERNAME_OBJ.value = connParams.username;
  PASSWORD_OBJ.value = unescapeForDoubleQuotes(connParams.password);

  // Set design-time connect radio button
  if (CONN_TYPE_OBJ != null)
  {
    var index = 0; //(USE_HTTP) ? 0 : 1;
    CONN_TYPE_OBJ[index].checked = true;

    // DSN_LIST = MMDB.getLocalDsnList();
    // DSN_NAME_OBJ.setAll(DSN_LIST, DSN_LIST);

    // if (!USE_HTTP)
    // {
    //  var decodedContents = decodeDSNConnectionString(connParams.designtimeString);

    //  if (decodedContents.length > 0 &&
    //    !DSN_NAME_OBJ.pickValue(decodedContents[0]))
    //  {
    //    DSN_NAME_OBJ.setIndex(0);
    //  }
    // }
  }

	// Initialize the QUB Caching CheckBox
	QUBCaching = findObject("QUBCaching");
	if (QUBCaching) {
		QUBCaching.checked = (connParams.QUBCaching == "true" ? true : false);
	}

	// Initialize the Locale variables
	var temp = ((typeof(connParams.locale) == "undefined") ? "Us" : connParams.locale);
	localeObj.pickValue(temp);
	var temp = ((typeof(connParams.msglocale) == "undefined") ? "En" : connParams.msglocale);
	msglocaleObj.pickValue(temp);
	var temp = ((typeof(connParams.ctype) == "undefined") ? "P" : connParams.ctype);
	connTypeObj.pickValue(temp);
	

  updateControls(USE_HTTP);

  // this function is always called, not just on re-edit,
  // so we check the updateExisting flag to determine
  // if we are re-editing.
  if (connParams.updateExisting)
  {
    CONN_NAME_OBJ.setAttribute("disabled","true");
    HOST_NAME_OBJ.focus();
  }
  else
  {
    CONN_NAME_OBJ.focus();
  }
  KT_dbtypeChanged();  
}


//--------------------------------------------------------------------
// FUNCTION:
//   clickedTest
//
// DESCRIPTION:
//   Tests the current connection and displays a success or failure
//   message.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function clickedTest()
{
  if (isValid())
  {  
    testConnection(true);
  }
}


//--------------------------------------------------------------------
// FUNCTION:
//   testConnection
//
// DESCRIPTION:
//   Validates the current connection and displays a success or failure
//   message if specified.
//
// ARGUMENTS:
//   showMessage  true/false to display messages from MMDB.testConnection
//
// RETURNS:
//   true/false
//--------------------------------------------------------------------

function testConnection(showMessage)
{
	if (!KT_copiedADODB) {
		//install ADODB files in the site root
		installADODB();
		KT_copiedADODB = true;
	}

  // build tokens array
  var tokens = new Object();

	tokens.hostname = dwscripts.trim(HOST_NAME_OBJ.value);
	tokens.username = dwscripts.trim(USERNAME_OBJ.value);
	tokens.password = PASSWORD_OBJ.value;
	tokens.databasename = DB_TYPE_OBJ.object.options[DB_TYPE_OBJ.object.selectedIndex].value+":"+dwscripts.trim(DB_NAME_OBJ.value);
    
	if (USE_HTTP)
    {
      tokens.http = "true";
      tokens.type = "ADODB";
      tokens.string = "type=ADODB;host=" + tokens.hostname + ";db="  + tokens.databasename + ";uid=" + tokens.username + ";pwd=" + tokens.password + ";";
      tokens.customURLParams = "Host=" + tokens.hostname + "&Database=" + tokens.databasename;
    }
    else
    {
      tokens.http = "false";
      tokens.type = "ADO";

      // var dsn = DSN_NAME_OBJ.object.value;
      // JALBANO: tokens.string = "\"" + buildDSNConnectionString(dsn) + "\"";
      // tokens.string = buildDSNConnectionString(dsn, tokens.username, tokens.password);
    }
    var temp = MMDB.testConnection(tokens, showMessage);
    return temp;
}  


//--------------------------------------------------------------------
// FUNCTION:
//   displayHelp
//
// DESCRIPTION:
//   Displays the built-in Dreamweaver help.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function displayHelp()
{
  // Replace the following call if you are modifying this file for your own use.
  dwscripts.displayDWHelp(HELP_DOC);
}


// ***************** LOCAL FUNCTIONS  ******************

//--------------------------------------------------------------------
// FUNCTION:
//   initializeUI
//
// DESCRIPTION:
//   Get the DOM objects for the various UI controls
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function initializeUI() 
{ 
  CONN_NAME_OBJ = dwscripts.findDOMObject("ConnectionName");
  HOST_NAME_OBJ = dwscripts.findDOMObject("HostName");
  DB_NAME_OBJ = dwscripts.findDOMObject("DatabaseName");
  DB_TYPE_OBJ = new ListControl("dbtype");
  USERNAME_OBJ = dwscripts.findDOMObject("UserName");
  PASSWORD_OBJ = dwscripts.findDOMObject("Password");

  localeObj = new ListControl("locale");
  localeObj.init();
  msglocaleObj = new ListControl("msglocale");
  msglocaleObj.init();
  connTypeObj= new ListControl("ctype");
  connTypeObj.init();
  // CONN_TYPE_OBJ = dwscripts.findDOMObject("connectType");
  // DSN_NAME_OBJ = new ListControl("dsn");
  // Add the QUB Caching checkbox if QUB is installed
	
	
	
	//if (DWfile.exists(dreamweaver.getConfigurationPath() + "/shared/QUB/scripts/utils.js")) {
	var qubVer = UpdateFiles.versionTxt2Num(MM.QUB_Version);
	var testVer = UpdateFiles.versionTxt2Num("2.7.0");
	if (qubVer && (qubVer < testVer)) {
		var mmParamsTag = findObject("theForm").getElementsByTagName("mmParams").item(0);
		mmParamsTag.innerHTML = "<tr bgcolor='#999999' align='left'><td colspan='3'> <b><font color='#FFFFFF'>QUB Caching </font></b></td></tr>";
		mmParamsTag.innerHTML += "<tr><td align='right'>QUB Caching</td><td><input type='Checkbox' name='QUBCaching'></td></tr>";

	}
  USE_HTTP = true ;
    
  // if (CONN_TYPE_OBJ == null)
  // {
  //  USE_HTTP = true;
  // }
  // {
    // set the default value
    var index = (USE_HTTP ? 0 : 1);
    // CONN_TYPE_OBJ[index].checked = true;
  // }
  
  CONN_NAME_OBJ.setAttribute("disabled","false");  
  CONN_NAME_OBJ.focus();
}


//--------------------------------------------------------------------
// FUNCTION:
//   updateControls
//
// DESCRIPTION:
//   Updates the enabled/disabled state of the controls based on
//   the selected connection type.
//
// ARGUMENTS:
//   useHTTP - boolean - true if using HTTP connectivity, false otherwise.
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function updateControls(useHTTP)
{
  USE_HTTP = useHTTP;
  
  // if (DSN_NAME_OBJ != null)
  // {
    if (USE_HTTP)
    {
      // document.theForm.dsn.setAttribute("disabled","true");
      // document.theForm.ODBC_button.setAttribute("disabled","true");
      document.theForm.DB_button.setAttribute("disabled","false");
    }
    else
    {
      // document.theForm.dsn.setAttribute("disabled","false");
      // document.theForm.ODBC_button.setAttribute("disabled","false");
      document.theForm.DB_button.setAttribute("disabled","true");
    }
  // }
}


//--------------------------------------------------------------------
// FUNCTION:
//   selectDatabase
//
// DESCRIPTION:
//   Launches the database selection dialog, and update the UI
//   values based on the returned values.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function selectDatabase()
{
  if (String(HOST_NAME_OBJ.disabled) == "true") {
  	alert("Database list is not available for this database type!");
  	return;
  }
  var args = new Array();
  var retVal = "";

  if (USERNAME_OBJ.value == "" && PASSWORD_OBJ.value == "")
  {
    alert(MM.MSG_MustSelectUserNamePassword)
    return
  }
  args.push(dwscripts.trim(DB_NAME_OBJ.value));
  args.push("ADODB");
  args.push(dwscripts.trim(HOST_NAME_OBJ.value));
  args.push(dwscripts.trim(USERNAME_OBJ.value));
  args.push(PASSWORD_OBJ.value);
  args.push(DB_TYPE_OBJ.object.options[DB_TYPE_OBJ.object.selectedIndex].value);
  // Validate connection
  if (! testConnection(false)){
    // Show specific error message
    testConnection(true);
  }
  else{
    retVal = dwscripts.callCommand("SelectODatabase.htm", args);
  }
  if (retVal != "" && retVal)
  {
    // Selected database
    DB_NAME_OBJ.value = retVal[0];

    // Also, propagate changes to other fields back to connection
    // HOST_NAME_OBJ.value = retVal[1];
    // USERNAME_OBJ.value = retVal[2];
    // PASSWORD_OBJ.value = retVal[3];
  }
}


//--------------------------------------------------------------------
// FUNCTION:
//   clickedODBC
//
// DESCRIPTION:
//   Called when the user clicks the Define button
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function clickedODBC()
{
  MMDB.showOdbcDialog();
  // DSN_LIST = MMDB.getLocalDsnList();
  // DSN_NAME_OBJ.setAll(DSN_LIST, DSN_LIST);
}


//--------------------------------------------------------------------
// FUNCTION:
//   isValid
//
// DESCRIPTION:
//   Checks if the current values entered in the dialog are valid.
//   Displays an error message if a problem is found.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   boolean - true if the dialog values are valid
//--------------------------------------------------------------------

function isValid()
{
  var retVal = true;
  
  //connection name
  if (retVal)
  {
    retVal = isValidConnectionName(CONN_NAME_OBJ);
  }
  if (retVal)
  {
    if (DB_NAME_OBJ.value == "")
    {
      alert(MSG_RequiresDatabase);
      document.theForm.DatabaseName.focus();
      return false;
    }
  }
  
  //host name
  if (retVal)
  {
  	if (!HOST_NAME_OBJ.disabled) {
	    if (HOST_NAME_OBJ.value == "")
	    {
	      if ( dwscripts.informDontShow(MSG_WantsHostName,"Extensions\\Connections\\PHPConnection","SkipHostConnectionWarning") == true )
	      {
	        document.theForm.HostName.focus();
	        return false;
	      }
	    }
	}
  }

  //user name check
  if (retVal)
  {
    if (USERNAME_OBJ.value == "")
    {
      if ( dwscripts.informDontShow(MSG_WantsUserName,"Extensions\\Connections\\PHPConnection","SkipUserNameConnectionWarning") == true )
      {
        document.theForm.UserName.focus();
        return true;
      }
    }
  }


 //user name check
  if (retVal)
  {
    if (PASSWORD_OBJ.value == "")
    {
      if ( dwscripts.informDontShow(MSG_WantsPassword,"Extensions\\Connections\\PHPConnection","SkipPasswordConnectionWarning") == true )
      {
        document.theForm.Password.focus();
        return true;
      }
    }
  }

  return retVal;
}


//-------------------------------------------------------
// FUNCTION:
//	testServerScripts
// 
// ACTION:
//  Check if the correct _mmServerScripts file are uploaded and upload the new files if necessary
//
// ARGUMENTS:
//	none
//
// RETURN:
//  boolean: true if succeded, false otherwise
//-------------------------------------------------------
function 	testServerScripts() {
	serverFile = site.getAppServerPathToFiles() + "_mmServerScripts/MMHTTPDB.php";
	localFile = site.getLocalPathToFiles() + "_mmServerScripts/MMHTTPDB.php";
	configFile = dw.getConfigurationPath() + "/Connections/Scripts/PHP_ADODB/_mmDBScripts/MMHTTPDB.php";
	result = true;
	
	// check if the remote file is good and copy the good one if not
	temp = DWfile.read(serverFile);
	if ((!temp) || (temp.indexOf("ADODB") == -1)) {
		result = DWfile.copy(configFile,serverFile);
	}
	// return error if copy fails
	if (!result) return result;
	// check if the remote file is good and copy the good one if not
	temp = DWfile.read(localFile);
	if ((!temp) || (temp.indexOf("ADODB") == -1)) {
		return DWfile.copy(configFile,localFile);
	}
	return true;
}