// Copyright 1999 Macromedia, Inc. All rights reserved.



//******************* API **********************

function findRs(){
	MM.rsTypes = new Array();
	xmlFiles = new Array();
	xmlFiles = getSiteFiles(dw.getConfigurationPath() + "/Shared/Recordset/",xmlFiles);
	for (fileIdx = 0;fileIdx < xmlFiles.length;fileIdx++) {
		rsDom = dw.getDocumentDOM(xmlFiles[fileIdx]);
		recordsets = rsDom.getElementsByTagName("recordset");
		for (i = 0; i < recordsets.length;i++ ) {
			tm = new Object();
			tm.serverModel = recordsets[i].serverModel;
			tm.type = recordsets[i].type;
			tm.command = recordsets[i].command;
			tm.priority = recordsets[i].priority;
			tm.saveUI = recordsets[i].saveUI;
			tm.preferedName = recordsets[i].preferedName;
			tm.subTypes = getSubTypes(recordsets[i]);
			MM.rsTypes.push(tm);
		}
	}
	//alert(MM.rsTypes.length);
	MM.rsTypes = unifyAndSort(MM.rsTypes);
	//alert(MM.rsTypes.length);
}


// recursively get the php and asp files from the site
function getSiteFiles(location,files) {
	var newFiles=files; //the new list of file
	var fileIdx;
	var folderIdx;
	var filesOfFolder;
	var foldersOfFolder;
	var tempLoc=location;
	if (tempLoc[tempLoc.length - 1] != '/') {
		tempLoc+="/";
	}

	filesOfFolder=DWfile.listFolder(tempLoc,"files");
	// and the php and asp files
	for (fileIdx = 0;fileIdx < filesOfFolder.length; fileIdx++) {
		newFiles[newFiles.length] = tempLoc + filesOfFolder[fileIdx];
	}
	foldersOfFolder=DWfile.listFolder(tempLoc,"directories");
	// recursively add the folders content
	for (folderIdx = 0;folderIdx < foldersOfFolder.length; folderIdx++) {
		//newFiles[newFiles.length] = tempLoc + foldersOfFolder[folderIdx];
		newFiles = getSiteFiles(tempLoc + foldersOfFolder[folderIdx],newFiles);
	}
	return newFiles;
}

function prioritySort(a,b) {
	return a.priority - b.priority;
}

//get the subtypes from a recordset node
function getSubTypes(rsNode) {
	subTypes = rsNode.getElementsByTagName("subType");
	types = new Array();
	for (ii = 0;ii < subTypes.length;ii++) {
		tmObj = new Object;
		tmObj.name = subTypes[ii].name;
		tmObj.value = subTypes[ii].value;
		tmObj.priority = subTypes[ii].priority;
		types.push(tmObj);
	}
	return types;
}

// unify the information conatained in fields with the same type by unifying the subtypes
// IAKT: Edited by BRI on 08/07/02
function unifyAndSort(rsType) {
	newTypes = new Array();
	for (i = 0;i < rsType.length;i++) {
		position = arrayContainsElement(newTypes,rsType[i],"type", "serverModel");

		if (position < 0) {
			newTypes.push(rsType[i]);
		} else {
			newTypes[position] = unifyElements(newTypes[position],rsType[i]);
		}
	}
	for (i =0;i < newTypes.length;i++) {
		newTypes[i].subTypes.sort(prioritySort);
	}
	newTypes.sort(prioritySort);
	return newTypes;
}


//-------------------------------------------------------
// FUNCTION:
//					unifyElements
// DESCRIPTION:
//				copy the subTypes of the second element in the first element if there isn't allready
// PARAMETERS:
// 				e1 - first rsType element
//				e2 - second rsType element
// RETURN VALUE:
//			the new element
//-------------------------------------------------------
function unifyElements(e1,e2) {
	var ii;
	newEl = e1;
	for (ii = 0;ii < e2.subTypes.length;ii++) {
		if (arrayContainsElement(newEl.subTypes,e2.subTypes[ii],"name","value") < 0) {
			newEl.subTypes.push(e2.subTypes[ii]);
		}
	}
	return newEl;
}



//-------------------------------------------------------
// FUNCTION:
//					arrayContainsElement
// DESCRIPTION:
//				check if an array containes an element and returns the position of the element in the array
// PARAMETERS:
// 				arrayVal - array variable
//				element - the element to be searched
//          ... properties of element that need to be matched
// RETURN VALUE:
//			the index of the matched element , -1 otherwise
// -----------------------------------------------------------

function arrayContainsElement(arrayVal,element) {
	var ii,jj;
	for (ii = 0;ii < arrayVal.length;ii++) {
		// if the function more that 2 arguments mean that we have specified the arguments to be matched
		if (arrayContainsElement.arguments.length > 2) { 
			isEqual = true;
			for (jj = 2;jj < arrayContainsElement.arguments.length;jj++) {
				property = arrayContainsElement.arguments[jj];
				if (arrayVal[ii][property] != element[property]) {
					isEqual = false;
				}
			}
			if (isEqual) {
				return ii;
			}
		} else {// if not we compare the two elements normally
			if (arrayVal[ii] == element) {
				return ii;
			}
		}
	}
	return -1;
}