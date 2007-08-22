var _KT_CheckDupKey = new URLTextField("CheckNewUsername.htm", "KT_CheckDupKey", "");
var _user_field = new ListMenu("CheckNewUsername.htm", "user_field");
var _InsertSB, _connName, _tableName, _variableList, _fieldsArr, _frmValuesArr;


//--------------------------------------------------------------------
// FUNCTION:
//   initializeUI
//
// DESCRIPTION:
//   Prepare the dialog and controls for user input
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------
function initializeUI()
{
  var elts;

  _KT_CheckDupKey.initializeUI();

	_fieldsArr = _InsertSB.getColumnList();
	_variableList = new Array();
	_frmValuesArr = new Array(); 
	for (var i=0, k=0; _fieldsArr && _fieldsArr.length > i; i++){
		 if (_fieldsArr[i].getVariableName() != ''){
		 		_variableList[k] = _fieldsArr[i].getColumnName();
				_frmValuesArr[k] = _fieldsArr[i].getVariableName();
				k++;
		 }
	}
	_user_field.initializeUI(_variableList, _variableList);

  elts = document.forms[0].elements;
  if (elts && elts.length)
    elts[0].focus();
}


//--------------------------------------------------------------------
// FUNCTION:
//   findServerBehaviors
//
// DESCRIPTION:
//   Returns an array of ServerBehavior objects, each one representing
//   an instance of this Server Behavior on the page
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   JavaScript Array of ServerBehavior objects
//--------------------------------------------------------------------
function findServerBehaviors()
{
  _KT_CheckDupKey.findServerBehaviors();
  _user_field.findServerBehaviors();
 
  sbArray = dwscripts.findSBs();

  return sbArray;
}


//--------------------------------------------------------------------
// FUNCTION:
//   canApplyServerBehavior
//
// DESCRIPTION:
//   Returns true if a Server Behavior can be applied to the current
//   document
//
// ARGUMENTS:
//   sbObj - ServerBehavior object - one of the objects returned
//           from findServerBehaviors
//
// RETURNS:
//   boolean - true if the behavior can be applied, false otherwise
//--------------------------------------------------------------------
function canApplyServerBehavior(sbObj)
{
  var success = true;
	var SBs = dwscripts.getServerBehaviorsByFileName("InsertRecord.htm");
	if (SBs.length==0) {
			alert ('This server behaviour requires the presence of the Insert Record behaviour on the page. Add an Insert Record behaviour and try again.');
			success = false;
	} else {
			_InsertSB = SBs[0];		
	}

	var other_same = dwscripts.getServerBehaviorsByFileName("CheckNewUsername.htm");
	if (success && other_same.length != 0 && !sbObj){
			alert ('Only one instance of this behaviour is allowed on a page. Modify the existing behaviour by double clicking it in the server behaviour panel.');
			success = false;
	}

 	if (success){
		_connName = _InsertSB.getParameter("ConnectionName");
		_tableName = _InsertSB.getParameter("TableName");

		var check_table = dwscripts.getTableNames(_connName);
		if (!check_table){
				alert ('No tables in the connection used by Insert Record server behaviour.');
				success = false;			
		}
		if (success){
			success = false;
			for (var i=0; check_table && !success && check_table.length > i ;i++){
				if (check_table[i] == _tableName){
					  	success = true;
				}
			}
			if (!success){
					alert ("The Table was not found");
			}
		}
	
	}

	if (success)
  {
    success = _KT_CheckDupKey.canApplyServerBehavior(sbObj);
  }
  if (success)
  {
    success = _user_field.canApplyServerBehavior(sbObj);
  }
  if (success)
  {
    success = dwscripts.canApplySB(sbObj, false); // preventNesting is false
  }
  return success;
}


//--------------------------------------------------------------------
// FUNCTION:
//   applyServerBehavior
//
// DESCRIPTION:
//   Collects values from the form elements in the dialog box and
//   adds the Server Behavior to the user's document
//
// ARGUMENTS:
//   sbObj - ServerBehavior object - one of the objects returned
//           from findServerBehaviors
//
// RETURNS:
//   string - empty upon success, or an error message
//--------------------------------------------------------------------
function applyServerBehavior(sbObj)
{
  var paramObj = new Object();
  var errStr = "";

  if (!errStr)
  {
    errStr = _KT_CheckDupKey.applyServerBehavior(sbObj, paramObj);
  }
  if (!errStr)
  {
    errStr = _user_field.applyServerBehavior(sbObj, paramObj);
  }

	if (!errStr && paramObj && paramObj.KT_CheckDupKey == ""){
		errStr = 'You must supply a redirect page for this server behaviour.';
	}
	if (!errStr){
			for (var i=0; i<_variableList.length; i++){
					if (paramObj.user_field == _variableList[i]){
								paramObj.frmVariable = _frmValuesArr[i];
					}
			}
			paramObj.tableName = _tableName;
			paramObj.connName = _connName;
	}

  if (!errStr)
  {
    dwscripts.fixUpSelection(dw.getDocumentDOM(), true, true);
    dwscripts.applySB(paramObj, sbObj);
  }
  return errStr;
}


//--------------------------------------------------------------------
// FUNCTION:
//   inspectServerBehavior
//
// DESCRIPTION:
//   Sets the values of the form elements in the dialog box based
//   on the given ServerBehavior object
//
// ARGUMENTS:
//   sbObj - ServerBehavior object - one of the objects returned
//           from findServerBehaviors
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------
function inspectServerBehavior(sbObj)
{
  _KT_CheckDupKey.inspectServerBehavior(sbObj);
  _user_field.inspectServerBehavior(sbObj);
}


//--------------------------------------------------------------------
// FUNCTION:
//   deleteServerBehavior
//
// DESCRIPTION:
//   Remove the specified Server Behavior from the user's document
//
// ARGUMENTS:
//   sbObj - ServerBehavior object - one of the objects returned
//           from findServerBehaviors
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------
function deleteServerBehavior(sbObj)
{
  _KT_CheckDupKey.deleteServerBehavior(sbObj);
  _user_field.deleteServerBehavior(sbObj);
  
  dwscripts.deleteSB(sbObj);
}


//--------------------------------------------------------------------
// FUNCTION:
//   analyzeServerBehavior
//
// DESCRIPTION:
//   Performs extra checks needed to determine if the Server Behavior
//   is complete
//
// ARGUMENTS:
//   sbObj - ServerBehavior object - one of the objects returned
//           from findServerBehaviors
//   allRecs - JavaScripts Array of ServerBehavior objects - all of the
//             ServerBehavior objects known to Dreamweaver
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------
function analyzeServerBehavior(sbObj, allRecs)
{
  _KT_CheckDupKey.analyzeServerBehavior(sbObj, allRecs);
  _user_field.analyzeServerBehavior(sbObj, allRecs);
}


//--------------------------------------------------------------------
// FUNCTION:
//   updateUI
//
// DESCRIPTION:
//   Called from controls to update the dialog based on user input
//
// ARGUMENTS:
//   controlName - string - the name of the control which called us
//   event - string - the name of the event which triggered this call
//           or null
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------
function updateUI(controlName, event)
{
  if (window["_" + controlName] != null)
  {
    var controlObj = window["_" + controlName];

    if (_KT_CheckDupKey.updateUI != null)
    {
      _KT_CheckDupKey.updateUI(controlObj, event);
    }
    if (_user_field.updateUI != null)
    {
      _user_field.updateUI(controlObj, event);
    }
   
  }
}

function onClickKTCheckDupKey(){
   _KT_CheckDupKey.browseForFile();
}

