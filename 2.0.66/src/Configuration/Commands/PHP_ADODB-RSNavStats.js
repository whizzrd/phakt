// Copyright 2000-2002 Macromedia, Inc. All rights reserved.

//*************** GLOBALS  *****************


var m_Recordset = "";
var helpDoc = MM.HELP_objRecordsetStatistics;

//******************* API **********************

function commandButtons()
{
   return new Array( BTN_OK, "okClicked()",
                     BTN_Cancel, "window.close()",
                     BTN_Help, "displayHelp()");
}


function canInsertObject()
{
  var retVal = true;
  
  var errMsgStr = "";
  
  if (dwscripts.getRecordsetNames().length == 0) 
  { 
    errMsgStr = dwscripts.sprintf(MM.MSG_NeedRecordsetForObject, dwscripts.getRecordsetDisplayName());
  }
  
  if (errMsgStr)
  {
    alert (errMsgStr);
    retVal = false;
  }
  
  return retVal;
}
 
//***************** LOCAL FUNCTIONS  ******************

function initializeUI() {

  var errMsg ="";


  //Display the example text
  var spanObj = findObject("exampleSpan");
  if (spanObj)
  {
  spanObj.innerHTML = MM.LABEL_RSNavExampleText;
  }

  //Build Recordset menu
  LIST_RS = new ListControl("Recordset");
  var rsNames = dwscripts.getRecordsetNames();
  LIST_RS.setAll(rsNames,rsNames);

  if (LIST_RS.object)
  {
    LIST_RS.object.focus();
  }
}


function okClicked() {
  var dataOkay = getDataFromUI();
  if (dataOkay) {
     applyRecordsetStats();
     window.close();
  }
}


function applyRecordsetStats() {
  var DEBUG = false;

  fixUpSelection(dreamweaver.getDocumentDOM());

  if (DEBUG) var debugMsg="COMPOUND SB OBJECT TEST:\n";

  var paramObj = new Object();
  var sbObj = null;

  //create new, empty custom group
  var customGroup = new Group();

  //set the recordset
  paramObj.rsName = m_Recordset;
  paramObj.RecordsetName = m_Recordset;

  var rsStatsGroup = new Group("RSStatsAll");
  
  customGroup.addParticipants(rsStatsGroup.getParticipants("afterSelection"));

  customGroup.apply(paramObj, sbObj);

}

function getDataFromUI()
{
  m_Recordset  = LIST_RS.getValue();
  if(m_Recordset != "") {
    return true;
  } else {
    alert(MM.MSG_invalidRS);
    return false;
  }
}
