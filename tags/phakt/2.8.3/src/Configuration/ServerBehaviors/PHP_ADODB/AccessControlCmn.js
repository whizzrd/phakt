// Copyright 2001 Macromedia, Inc. All rights reserved.

// ***************** Global variables *******************
//

// Run-time code session variable names
var MM_USERNAME = "KT_Username";
var MM_USERAUTHORIZATION = "KT_UserAuthorization";

// ***************** Common functions *******************
//
  
function getssRecByType(type) {
  var retssRec = null;
  var ssRecs = dw.serverBehaviorInspector.getServerBehaviors();
  for (i=0; i<ssRecs.length; i++) {
    if (ssRecs[i].type == type) {
      retssRec = ssRecs[i];
      break;
    }
  }
  return retssRec;
}

function findConnectionNode(connName) {
  var connNode=null;
  var dom = dw.getDocumentDOM();
  var nodes = findNodes(dom, "cfinclude");

  var patt = RegExp(getServerData("PATT", "URLoginConnection"),"i");
  for (i=0; i<nodes.length; i++) {
    script = nodes[i].outerHTML; // get the script
    var tokens = script.match(patt);
    if (tokens != null && tokens.length>2 && tokens[2]==connName) {
      connNode = nodes[i];
      break;
    }
  }

  if (connNode==null && dw.getDocumentDOM().serverModel.getServerName()=="ASP") {
    connNode = findConnectionCommentNode(connName);
  }
  return connNode;
}

function findConnectionCommentNode(connName, theNode) {
  var connNode = null;
  if (!theNode) theNode = dw.getDocumentDOM();

  if (theNode.nodeType == Node.COMMENT_NODE) {
    if (theNode.data.search(RegExp("Connections/" + connName + "\\.", "i")) >=0) {
      return theNode;
    }
  }
  
  if (theNode.hasChildNodes()) {
    for (var i=0; i<theNode.childNodes.length; i++) {
      connNode = findConnectionCommentNode(connName, theNode.childNodes[i]);
      if (connNode != null) return connNode;
    }
  }
  return null;
}

var PATT_PHPADODBURLoginConnection = '<?php\\s*require_once\\(\'.*Connections/(.*)\\.php\'';
var MASK_PHPADODBURLoginConnection = '<?php require_once(\'@@connectionFile@@.php\'); ?>';
