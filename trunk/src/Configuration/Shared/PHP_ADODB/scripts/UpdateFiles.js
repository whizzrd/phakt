//SHARE-IN-MEMORY=true
// Copyright 2001-2003 Interakt Online. All rights reserved.

/**
* @class
*   UpdateFiless
*		Static Class. Contains functions used to update site files for new versions
*
**/

function UpdateFiles() {
	alert("This is a static class!"); // Display an error message if we try to instantiate this class
}

if(!MM.toPut) {
	MM.toPut = new Array();
}

UpdateFiles.getSiteFoldersAndFiles = UpdateFiles_getSiteFoldersAndFiles;
UpdateFiles.removeParticle = UpdateFiles_removeParticle;

UpdateFiles.getNumericVersion = UpdateFiles_getNumericVersion;
UpdateFiles.upgradeVersions = UpdateFiles_upgradeVersions;
UpdateFiles.synchronized_upgradeVersions = UpdateFiles_synchronized_upgradeVersions;

UpdateFiles.copyFiles = UpdateFiles_copyFiles;
UpdateFiles.versionTxt2Num = UpdateFiles_versionTxt2Num;
UpdateFiles.checkVersion = UpdateFiles_checkVersion;
UpdateFiles.lockFolder = UpdateFiles_lockFolder;
UpdateFiles.isLocked = UpdateFiles_isLocked;
UpdateFiles.put = UpdateFiles_put;
UpdateFiles.updateConnection = UpdateFiles_updateConnection;

/**
* 	recursively get the folders and files from the site
*		the folder preceds it's files
*
*	 @param
*		location - URL -location to start
*		files - Array() (void). This is initially an empty array and 
*						then is used to append other files since the function 
*						is recursive
*
* @return
*		true if succes, false otherwise
**/
function UpdateFiles_getSiteFoldersAndFiles(location,files) {
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
		newFiles[newFiles.length] = tempLoc + foldersOfFolder[folderIdx];
		newFiles = this.getSiteFoldersAndFiles(tempLoc + foldersOfFolder[folderIdx],newFiles);
	}
	return newFiles;
}

/** 
 * 		Remove a particle from an array of strings
 * 
 *  @param
 * 		obj - the array (or Object) of strings (is also the output object)
 * 		particle - string - the paticle to be removed
 * 
 *  @return
 * 		none
 */
function UpdateFiles_removeParticle(obj, particle) {
	var objIdx;
	var start;
	for (objIdx in obj) {	
		start = obj[objIdx].indexOf(particle);
		if (start != -1) {	
			obj[objIdx] = obj[objIdx].substr(0,start) + obj[objIdx].substr(start + particle.length);
		}
	}
}



/** 
 * Convert the string version from a version file into a numeric one
 * 
 *  @param
 *		dir - folder from where we want to extract version number;
 * 
 *  @return
 * 		numeric - version number
 */
function UpdateFiles_getNumericVersion(dir) {
	var remoteVerTxt = DWfile.read(dir + '/version.txt');
	if (!remoteVerTxt) {
		return 0;
	}
	return UpdateFiles.versionTxt2Num(remoteVerTxt);
}

/** 
 * Convert the string version into a numeric one
 * 
 *  @param
 *		verTxt - version in tect format
 * 
 *  @return
 * 		numeric - version number
 */
function UpdateFiles_versionTxt2Num(verTxt) {
	if (!verTxt) {
		return 0;
	}
	var tmArr = verTxt.split('.');
	var idx;
	var verNum = 0;
	for (idx = 0;idx < tmArr.length;idx++) {
		verNum = verNum*1000 + parseInt(tmArr[idx]);
	}
	return verNum;
}

/**
 * 	Check if the config dir version is greater that the version installed on server.
 * 	If yes upgrade the server dir
 * 	
 * 	@param
 * 		configDir - the folder (relative to DW configuration folder) from 
 * 											which we copy the files
 * 		serverDir - destination folder on server
 *	@return
 *		none
 * 											
 */

function UpdateFiles_upgradeVersions(configDir, serverDir) {
	if (!MM.lockPut) {
		UpdateFiles.synchronized_upgradeVersions(configDir, serverDir);
	} else {
		setTimeout(function () { return UpdateFiles.upgradeVersions(configDir, serverDir); }, 1000);
	}
}

function UpdateFiles_synchronized_upgradeVersions(configDir, serverDir) {
	var siteRoot = dw.getSiteRoot();
	var configVer = UpdateFiles.getNumericVersion(dw.getConfigurationPath() + configDir);
	var siteVer = UpdateFiles.getNumericVersion(siteRoot + serverDir)
	if (!UpdateFiles.isLocked(serverDir)) {
		if (parseInt(configVer) > parseInt(siteVer)) {
			var myMessage = '';
			if (MM.KTUPDT_Messages && MM.KTUPDT_Messages[serverDir] && siteVer != 0) {
				myMessage = MM.KTUPDT_Messages[serverDir];
			} else {
				myMessage = dwscripts.sprintf('Are you sure you want to update %s?',serverDir);
			}
			if (siteVer == 0 || window.confirm(myMessage)) {
				UpdateFiles.copyFiles(configDir, serverDir, true);
				MM.toPut.push(serverDir);
				MM.needPut = true;
			} else {
				UpdateFiles.lockFolder(serverDir);
			}
		}
	}
}

/**
 * 	put all the queued folders. 
 * 	
 * 	@param
 *		none
 *	@return
 *		none
 * 											
 */
function UpdateFiles_put() {
	if (MM.needPut) {
		site.refresh("local");
		MM.needPut = false;
	}
	if (!MM.lockPut) {
		MM.lockPut = true;
		while (MM.toPut.length >0) {
			var tmPut = MM.toPut.shift();
			if (tmPut.indexOf('_mmServerScripts') == -1) {
				site.locateInSite("local",dreamweaver.getSiteRoot() + tmPut);
				site.put('site');
			} else {
				var tmFiles = DWfile.listFolder(dreamweaver.getSiteRoot() + tmPut,'files');
				var fileIdx;
				for (fileIdx = 0;fileIdx < tmFiles.length;fileIdx++) {
					site.put(dreamweaver.getSiteRoot() + tmPut + '/' + tmFiles[fileIdx]);
				}
			}
		}
		MM.lockPut = false;
	}
}
	
/**
	lock a folder to prevent further upgrades
	
	@param
	serverDir - folder on server to be locked for further upgrade
	
	@return
	true on success false otherwise
*/
function UpdateFiles_lockFolder(serverDir) {
	var siteRoot = dw.getSiteRoot();
	var dstFolder = siteRoot + serverDir;
	if (!DWfile.exists(dstFolder)) {
		if (!DWfile.createFolder(dstFolder)) {
			return false;
		}
	}
	DWfile.write(dstFolder + '/lock.txt','lock');
}

/**
	check if a folder is locked for further upgrades
	
	@param 
	serverDir - folder on server that mau be locked
	
	@return 
		true if folder is locked , false otherwise
*/
function UpdateFiles_isLocked(serverDir) {
	var siteRoot = dw.getSiteRoot();
	return DWfile.exists(siteRoot + serverDir + '/lock.txt');
}

/**
 * 	copy the tNG include Files (tNG PHP classes) and upload them to server
 * 
 *  @param
 * 		configurationDir - the folder (relative to DW configuration folder) from 
 * 											which we copy the files
 * 		serverDir - destination folder on server
 * 		force - force overwriting
 * 
 *  @return
 * 		true if succes, false otherwise
 * 
 */
function UpdateFiles_copyFiles(configurationDir,serverDir,force) {
	var files = Array();
	var siteRoot = dw.getSiteRoot();
	//siteRoot = siteRoot.substr(0,siteRoot.length - 1);
	files.push(dw.getConfigurationPath() + configurationDir);
	files = this.getSiteFoldersAndFiles(dw.getConfigurationPath() + configurationDir,files);
	this.removeParticle(files,dw.getConfigurationPath() + configurationDir);
	var fileIdx;
	for (fileIdx = 0;fileIdx < files.length;fileIdx++) {
		srcFile = dw.getConfigurationPath() + configurationDir + files[fileIdx];
		dstFile = siteRoot + serverDir + files[fileIdx];
		if (DWfile.getAttributes(srcFile) == 'D') {
			if (!DWfile.exists(dstFile)) {
				if (!DWfile.createFolder(dstFile)) {
					return false;
				}
			}
		} else {
			if (!DWfile.exists(dstFile) || force) {
				if (!DWfile.copy(srcFile,dstFile)) {
					return false;
				}
			}
		}
	}
}

//--------------------------------------------------------------------
// FUNCTION:
//   checkVersion
//
// DESCRIPTION:
//		check for a new version of a product on the Interakt Site
//
// ARGUMENTS:
//		productName - string
//		productURL - string - URL of the interakt Version server
//		productVersion - string - current product version
//		newMessage - string - messafe template to display when a new version is found
// RETURNS:
//		none
//--------------------------------------------------------------------
function UpdateFiles_checkVersion(productName, productURL, productVersion,newMessage) {
	//check  version only once in a week
	var path = dreamweaver.getConfigurationPath() + '/Shared/' + productName;
  var metaFile;

	var dat = new Date();
	var firstTime = false;
  metaFile = MMNotes.open(path); 
  if (!metaFile) {
  	metaFile = MMNotes.open(path,true); // Force create the note file.
	 	MMNotes.set(metaFile, 'last', dat.valueOf());
	 	firstTime = true;
  }
	var aa = MMNotes.get(metaFile,"last");
	if(dat.valueOf() - aa > 604800000 || firstTime) {
		//reset the date
		MMNotes.set(metaFile, 'last', dat.valueOf());
		if (confirm(dwscripts.sprintf("Do you want to check Interakt Server for a new version of %s?",productName))) {
			var sRemoteFilepath = productURL;
			var httpReply = MMHttp.getFile(sRemoteFilepath);
			if(httpReply.statusCode == 200){
				var remoteDom = dreamweaver.getDocumentDOM(httpReply.data);
				var eRemoteExtensionVersion = remoteDom.getElementsByTagName('version');
				if (eRemoteExtensionVersion[0].hasChildNodes()) {
					var nRemoteVersionNumber = eRemoteExtensionVersion[0].childNodes[0].innerHTML;
				} else {
					var nRemoteVersionNumber = eRemoteExtensionVersion[0].innerHTML;
				}
				if(	parseInt(UpdateFiles.versionTxt2Num(nRemoteVersionNumber)) > 
						parseInt(UpdateFiles.versionTxt2Num(productVersion))) {
					alert(dwscripts.sprintf(newMessage, nRemoteVersionNumber));
				}
			}
		}
	}
  MMNotes.close(metaFile);

}

function UpdateFiles_updateConnection(file) {
	file = dw.getSiteRoot() + file;
	var str = DWfile.read(file);
	if (str.match(/@@cname@@/)) {
		var clist = MMDB.getConnectionList();
		if (clist.length>0) {
			str = str.replace(/@@cname@@/g, clist[0]);
			DWfile.write(file,str);
		}
	}
}

