// Copyright 2001-2002 Macromedia, Inc. All rights reserved.

//******************* API **********************

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
  var sbObj;
  var sbList = dwscripts.findSBs(MM.LABEL_TitleRecordset + " (@@RecordsetName@@)", SBRecordsetPHP);
  
  for (var i=0; i < sbList.length; i++) {
    var rsName = sbList[i].getParameter("RecordsetName");
		if (rsName) {
			sbList[i].setTitle(MM.LABEL_TitleRecordset + " (" + rsName + ")");
		}
		//fill specific parameters for every rsType
		for (var j = 0;j < MM.rsTypes.length;j++) {
	    domCommand = dw.getDocumentDOM(dw.getConfigurationPath() + "/Commands/" + MM.rsTypes[j].command); 
			if (domCommand) {
				windowCommand = domCommand.parentWindow;
				if (windowCommand.fillAditionalParameters) {
					sbList[i] = windowCommand.fillAditionalParameters(sbList[i]);
				}			}
		}
  }
  
  return sbList;
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

  if (success)
  {
    dwscripts.canApplySB(sbObj, false); // preventNesting is false
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
//   priorSBRecordset - SBRecordsetPHP object - one of the objects returned
//     from findServerBehaviors
//
// RETURNS:
//   string - empty upon success, or an error message
//--------------------------------------------------------------------

function applyServerBehavior(priorSBRecordset)
{
  var paramObj = new Object();
  var errStr = "";

  var sbObj = priorSBRecordset;
  if (!sbObj)
  {
    sbObj = new SBRecordsetPHP();
    
    // Check if any default values are set for us (i.e., drag and drop 
    //   operations from the database panel set the default connection
    //   and table name values and invoke the recordset sb).  
    if (MM.recordsetSBDefaults)
    {
      sbObj.setConnectionName(MM.recordsetSBDefaults.connectionName);
      sbObj.setDatabaseCall(MM.recordsetSBDefaults.sql, new Array());
      
      // Clear out the default values.
      MM.recordsetSBDefaults = null;
    }
  }
  var newSBRecordset = recordsetDialog.display(sbObj);
	
  //                                             "ServerBeh-PHP4-SimpRS.htm",
	//                                            "ServerBeh-PHP4-AdvRS.htm");
  
  if (newSBRecordset)
  {
    dwscripts.fixUpSelection(dw.getDocumentDOM(), true, true);
    dwscripts.applySB(newSBRecordset.getParameters(), priorSBRecordset);

    // Refresh the cache for recordset.
    MMDB.refreshCache(true);
    
    // Update references to the recordset on name change.
    newSBRecordset.updateRecordsetRefs();
    
    MM.RecordsetApplied = true;
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
  
   sbObj.analyze()
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
}


//-------------------------------------------------------------------
// FUNCTION:
//   copyServerBehavior
//
// DESCRIPTION:
//
// ARGUMENTS: 
//
// RETURNS:
//--------------------------------------------------------------------
function copyServerBehavior(sbObj) 
{
  sbObj.preprocessForSerialize();
  return true;
}


//-------------------------------------------------------------------
// FUNCTION:
//   pasteServerBehavior
//
// DESCRIPTION:
//
// ARGUMENTS: 
//
// RETURNS:
//--------------------------------------------------------------------
function pasteServerBehavior(sbObj) 
{
 
  sbObj.postprocessForDeserialize();

  sbObj.setPageSize("0"); // do not paste any paging code

  var rsName = sbObj.getRecordsetName();

  if (!sbObj.isUniqueRecordsetName(rsName, ""))
  {
    rsName = sbObj.getUniqueRecordsetName(); 
    sbObj.setRecordsetName(rsName);
  }

  // Apply the edits.
  sbObj.queueDocEdits();

  dwscripts.applyDocEdits();
}

//--------------------------------------------------------------------
// FUNCTION:
//   createServerBehaviorObj
//
// DESCRIPTION:
//   This function is called from UltraDev when pasting a ServerBehavior.
//   If you plan to implement copyServerBehavior and pasteServerBehavior for 
//   your SB, you must implement this function to return an empty instance of  
//   the ServerBehavior object or of your subclass of ServerBehavior. 
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   empty ServerBehavior instance
//--------------------------------------------------------------------
function createServerBehaviorObj()
{
  return new SBRecordsetPHP();
}



