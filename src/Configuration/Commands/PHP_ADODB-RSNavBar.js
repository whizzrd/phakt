// Copyright 2000-2002 Macromedia, Inc. All rights reserved.


//*************** GLOBALS  *****************

var bUseTables = false;
var bHideLinks = false;
var bUseImages = false;
var m_Recordset = "";
var FIRST_file = "First.gif"
var PREVIOUS_file = "Previous.gif"
var NEXT_file = "Next.gif"
var LAST_file = "Last.gif"
var MODEL_IS_CF = (dw.getDocumentDOM().serverModel.getServerName() == "Cold Fusion");
var DISPLAY_TYPE;
var helpDoc = MM.HELP_objRecordsetNavigation;
var DEBUG = false;

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
  
  //Build Recordset menu
  LIST_RS = new ListControl("Recordset");
  var rsNames = dwscripts.getRecordsetNames();
  LIST_RS.setAll(rsNames,rsNames);

  DISPLAY_TYPE = findObject("DisplayType");
  
  if (LIST_RS.object)
  {
    LIST_RS.object.focus();
  }
}


function okClicked() {
  
  var dataOkay = getDataFromUI();
  if (dataOkay) {
     applyRSNavigation();
     window.close();
  }
}


function applyRSNavigation() {

  var DEBUG = false;
  if (DEBUG) var debugMsg="COMPOUND SB OBJECT TEST:\n";

  fixUpSelection(dreamweaver.getDocumentDOM());

  var paramObj = new Object();
  paramObj.relpath = getConnectionsPath();
  var sbObj = null;

  //create new, empty custom group
  //var customGroup = new Group();

  //Open the 4 existing Move To group files we are borrowing from
//  for(i=0;i<moveToFirstGroup.participants.length;i++){
//  	for(a in moveToFirstGroup.participants[i]) eval("alert ('"+a+",'+moveToFirstGroup.participants[i]."+a+")");
//  }
  var moveToFirstGroup = new Group("MoveToFirstPage");
  var moveToPrevGroup = new Group("MoveToPrevPage");
  var moveToNextGroup = new Group("MoveToNextPage");
  var moveToLastGroup = new Group("MoveToLastPage");
  
  //get "directive" participants from Move To groups and add to customGroup
  //customGroup.addParticipants(moveToFirstGroup.getParticipants("aboveHTML"));
  //customGroup.addParticipants(moveToPrevGroup.getParticipants("aboveHTML"));
  //customGroup.addParticipants(moveToNextGroup.getParticipants("aboveHTML"));
  //customGroup.addParticipants(moveToLastGroup.getParticipants("aboveHTML"));


  if (DEBUG) debugMsg += "\nAdded all directive participants from Move To, a total of "+customGroup.participants.length;


  //var customPart;

  //customPart = new Participant("rsNav_Table");
  //customGroup.addParticipants(Array(customPart));   

  //Get the groups handling the Show Regions for first and last record.
  var showRegion_firstRecord = new Group("ShowIfNotFirstPage");
  var showRegion_lastRecord = new Group("ShowIfNotLastPage");

  //Get the insertion strings. We know that these objects don't have any
  //parameters, hence the object with no properties added to it. 
  var emptyParamObject = new Object();

  paramObj.hideLinksFirstBegin = showRegion_firstRecord.getInsertStrings(emptyParamObject, "beforeSelection").join("");
  paramObj.hideLinksFirstEnd = showRegion_firstRecord.getInsertStrings(emptyParamObject, "afterSelection").join("");
  paramObj.hideLinksLastBegin = showRegion_lastRecord.getInsertStrings(emptyParamObject, "beforeSelection").join("");
  paramObj.hideLinksLastEnd = showRegion_lastRecord.getInsertStrings(emptyParamObject, "afterSelection").join("");

  //set parameter values for Move To stuff (from UI settings)
  paramObj.rsName  = m_Recordset;
  paramObj.RecordsetName  = m_Recordset;
  paramObj.col     = "";
  paramObj.paramName = "";

  if(bUseImages)
  {
    CopyImageFiles();
    paramObj.firstStr = '<img src="' + FIRST_file + '" border=0>';
    paramObj.prevStr = '<img src="' + PREVIOUS_file + '" border=0>';
    paramObj.nextStr = '<img src="' + NEXT_file + '" border=0>';
    paramObj.lastStr = '<img src="' + LAST_file + '" border=0>';


  } else {
    //set parameter values for compound object from localizeable globals file.
	  if (dreamweaver.appVersion && dreamweaver.appVersion.indexOf('ja') != -1) {  // Japanese version?
		var charSet = dw.getDocumentDOM().getCharSet();
		charSet = charSet.toLowerCase();
		if (charSet == "shift_jis" || charSet == "x-sjis" || charSet == "euc-jp" || charSet == "iso-2022-jp") 
		{
			paramObj.firstStr = MM.LABEL_NewMoveToFirstLinkLabel;  
			paramObj.prevStr  = MM.LABEL_NewMoveToPrevLinkLabel;
			paramObj.nextStr  = MM.LABEL_NewMoveToNextLinkLabel;
			paramObj.lastStr  = MM.LABEL_NewMoveToLastLinkLabel;
		} else {
		  paramObj.firstStr = MM.LABEL_EngNewMoveToFirstLinkLabel;  
		  paramObj.prevStr  = MM.LABEL_EngNewMoveToPrevLinkLabel;
		  paramObj.nextStr  = MM.LABEL_EngNewMoveToNextLinkLabel;
		  paramObj.lastStr  = MM.LABEL_EngNewMoveToLastLinkLabel;
		}
	  }
	  else {
	  	paramObj.firstStr = entityNameEncode(MM.LABEL_NewMoveToFirstLinkLabel);  //"First" in English
		paramObj.prevStr  = entityNameEncode(MM.LABEL_NewMoveToPrevLinkLabel);
		paramObj.nextStr  = entityNameEncode(MM.LABEL_NewMoveToNextLinkLabel);
		paramObj.lastStr  = entityNameEncode(MM.LABEL_NewMoveToLastLinkLabel);
	}

  }

  //set parameter values extracted from Move To groups, in this case HREF strings
  var hrefArray = moveToFirstGroup.getInsertStrings(paramObj,"nodeAttribute+href");
  
  if (hrefArray && hrefArray.length) paramObj.moveFirstHref = hrefArray[0];
  var hrefArray = moveToPrevGroup.getInsertStrings(paramObj,"nodeAttribute+href");
  if (hrefArray && hrefArray.length) paramObj.movePrevHref = hrefArray[0];
  var hrefArray = moveToNextGroup.getInsertStrings(paramObj,"nodeAttribute+href");
  if (hrefArray && hrefArray.length) paramObj.moveNextHref = hrefArray[0];
  var hrefArray = moveToLastGroup.getInsertStrings(paramObj,"nodeAttribute+href");
  if (hrefArray && hrefArray.length) paramObj.moveLastHref = hrefArray[0];
  
  setMoveToParamsForJsp(paramObj);

  //DEBUG
  if (DEBUG) debugMsg += "\ninserting a total of "+customGroup.participants.length+" participants.";
  if (DEBUG) alert(debugMsg);

  //Apply everything to the page
  MM.setBusyCursor();
  fixUpInsertionPoint();
	
	queueParticipantsByLocation("MoveToFirstPage","aboveHTML",paramObj);
 	dwscripts.queueDocEditsForParticipant("rsNav_Table",paramObj);
  dwscripts.applyDocEdits();

  //customGroup.apply(paramObj,sbObj);
  MM.clearBusyCursor();

}

function getDataFromUI()
{
  bUseImages = (DISPLAY_TYPE[1].checked) ? true : false;

  if(bUseImages)
  {
    var dom = dreamweaver.getDocumentDOM();
  if (dom.URL == "")
  {
    alert(MM.MSG_rsNavigationBarSaveDocument);
    return false;
  }
  }
  m_Recordset  = LIST_RS.getValue();

  if(m_Recordset != "") {
    return true;
  }
  else {
    alert(MM.MSG_invalidRS);
    return false;
  }

}

function CopyImageFiles()
{
  var dom = dreamweaver.getDocumentDOM();
  var documentPath = dom.URL;
  var lastSlashIndex = documentPath.lastIndexOf('/');
  documentPath = documentPath.substring(0, lastSlashIndex+1);
  
  if(documentPath != ""){

    var configPath = dreamweaver.getConfigurationPath();
    configPath += "/Shared/UltraDev/Images/";
  
    bCopyFlag = true;

    CopyTheFiles(configPath+FIRST_file, documentPath+FIRST_file);
    CopyTheFiles(configPath+NEXT_file, documentPath+NEXT_file);
    CopyTheFiles(configPath+PREVIOUS_file, documentPath+PREVIOUS_file);
    CopyTheFiles(configPath+LAST_file, documentPath+LAST_file);
 
  } else bCopyFlag = false;
  if(!bCopyFlag)
  {
     alert(MM.MSG_rsNavigationBarSaveDocument);
  }

}

function CopyTheFiles(source, destination)
{
  if(!DWfile.exists(destination))
  {
    if(DWfile.exists(source))
      DWfile.copy(source, destination);
  else
    bCopyFlag = false;
  }

}

/*
given a group name and a location we take all the participants that have the specified location 
 and we queued them in DocEdits
*/
function queueParticipantsByLocation(grName,location,paramObj) {
	var i,partNames;
	partNames = extGroup.getParticipantNames(grName,location);
	for (i = 0;i < partNames.length;i++) {
		dwscripts.queueDocEditsForParticipant(partNames[i],paramObj);
	}
}
