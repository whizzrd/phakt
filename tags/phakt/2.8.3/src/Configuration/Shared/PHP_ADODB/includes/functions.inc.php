<?php
  // PHP ADODB document - made with PHAkt
  // functions needed by PHAkt generated pages
  
  function KT_replaceParam($qstring, $paramName, $paramValue) {
    if (preg_match("/&" . $paramName . "=/", $qstring)) {
      return preg_replace("/&" . $paramName . "=[^&]+/", "&" . $paramName . "=" . urlencode($paramValue), $qstring);
    } else {
      return $qstring . "&" . $paramName . "=" . urlencode($paramValue);
    }
  }

  function KT_removeParam($qstring, $paramName) {
    if($qstring == "&"){
      $qstring = "";
    }
    $tmp = preg_replace("/&" . $paramName . "=[^&]*/", "", $qstring);
	if ($tmp == $qstring) {
    	$tmp = preg_replace("/\?" . $paramName . "=[^&]*/", "?", $tmp);
    	$tmp = str_replace("?&", "?", $tmp);
    	$tmp = preg_replace("#\?$#", "", $tmp);
	}
	return $tmp;
  }


/***
* KT_keep_arrayParams
* Description: Transform an array into an URL string delimited with &
* Parameters:
* $the_array - The array which it should be transformed
* $the_var - The initial string to which it will add this URL string representation
* $keepName - The name to be keeped if the array has multiple levels 
* $paramName - A key/array of keys name from the array whitch it should be eliminated
*/

function KT_keep_arrayParams($the_array, $the_var, $keepName='', $paramName=''){
	while (list($key, $value) = each($the_array)) {
		if ($paramName == '' OR (!(is_array($paramName)) AND $key != $paramName) OR (is_array($paramName) AND !(in_array($key, $paramName, TRUE)))) {
			if (!is_array($value)){
				$the_var .= "&" . ($keepName!=''?($keepName."["):"").urlencode($key) .($keepName!=''?"]":""). "=" . urlencode($value);
			}else{
				$the_var = KT_keep_arrayParams($value, $the_var, ($keepName!=''?($keepName."["):"").urlencode($key).($keepName!=''?"]":""),$paramName); 
			}
		}
	}
	return $the_var;
}

function KT_keepParams($paramName) {
	global $MM_keepURL, $MM_keepForm, $MM_keepBoth, $MM_keepNone, $HTTP_GET_VARS, $HTTP_POST_VARS;
	$MM_keepNone="";

	// add the URL parameters to the MM_keepURL string
	$MM_keepURL = KT_keep_arrayParams($HTTP_GET_VARS, '', '', $paramName);
	
	// add the Form variables to the MM_keepForm string
	$MM_keepForm = KT_keep_arrayParams($HTTP_POST_VARS, '', '', $paramName);
	
	// create the Form + URL string and remove the intial '&' from each of the strings
	$MM_keepBoth = $MM_keepURL . $MM_keepForm;
	if (strlen($MM_keepBoth) > 0) $MM_keepBoth = substr($MM_keepBoth,1);
	if (strlen($MM_keepURL) > 0) $MM_keepURL = substr($MM_keepURL,1);
	if (strlen($MM_keepForm) > 0) $MM_keepForm = substr($MM_keepForm,1);
}
  
  function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
    switch ($theType) {
      case "text":
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;    
      case "long":
      case "int":
        $theValue = ($theValue != "") ? intval($theValue) : "NULL";
        break;
      case "double":
        $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
        break;
      case "date":
        // format the date according to the current locale
        global $KT_localFormat;
        global $KT_serverFormat;
        if ($theValue != "") {
            $theValue = KT_convertDate($theValue, $KT_localFormat, $KT_serverFormat);
        }
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;
      case "defined":
        $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
        break;
    }
    return $theValue;
  }


   class fakeRecordSet{
        var $fields=array();

        function prepareValue($field, $value){
            if($value==="NULL"){
                $value="";
            }
            $this->fields[$field]=$value;
        }
        
        function Fields($field){
            return @$this->fields[$field];
        }

        function Close(){
            unset($this->fields);
        }
    }
    
    function KT_parseError($a,$b) {
        //if(strstr($b, "Bad date external representation")){
        //    $b = "&nbsp;Data nu a fost bine introdusa.";
        //}
        echo "<font color=red><p class=\"error\">Error:<br>$b</p></font>";
    }
    
    function KT_DIE($a,$b) {
        echo "<p class=\"error\">An error occured!<br>Error no: $a<br>Error message: $b</p>";
        exit;
    }

   function addReplaceParam($KT_Url,$param,$value=""){
      $sep = (strpos($KT_Url, '?') == false)?"?":"&";
      $value = KT_descape($value);
      if(preg_match("#$param=[^&]*#",$KT_Url)){
         $KT_Url = preg_replace("#$param=[^\&]*#", "$param=$value", $KT_Url);
      }else {
         $KT_Url .="$sep$param=$value";
      }
      if ($value == "") {
        $KT_Url = preg_replace("#$param=#", "", $KT_Url);
      }
      $KT_Url = str_replace("?&", "?", $KT_Url);
      $KT_Url = preg_replace("#&+#", "&", $KT_Url);
      $KT_Url = preg_replace("#&$#", "", $KT_Url);
      $KT_Url = preg_replace("#\?$#", "", $KT_Url);
      return $KT_Url;
   }
   
   function KT_descape($KT_text){
     if(eregi("^'.*'$",$KT_text)){
         $KT_text = substr($KT_text, 1, strlen($KT_text)-2);
     }
     return $KT_text;
   }

   function KT_removeEsc($KT_text) {
          if (eregi("^'.*'$",$KT_text)) {
            return substr($KT_text, 1, strlen($KT_text)-2);
        } else {
            return $KT_text;
        }
   }
   
     function KT_convertDate($date, $inFmt, $outFmt) {
        if (($inFmt == "none") || ($outFmt == "none")) {
            return $date;
        }
        if(ereg("^[0-9]+[/|-][0-9]+[/|-][0-9]+$", $date)) {
            $outFmt = eregi_replace(" +.+$", "", $outFmt);
        }
        if (ereg ("%d[/.-]%m[/.-]%Y %H:%M:%S", $inFmt)) {
            if(ereg ("([0-9]{1,2})[/.-]([0-9]{1,2})[/.-]([0-9]{2,4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
                for ($i=1;$i<7;$i++) {
                    if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
                }
                $outdate = $outFmt;
                $outdate = ereg_replace("%Y",$regs[3],$outdate);
                $outdate = ereg_replace("%m",$regs[2],$outdate);
                $outdate = ereg_replace("%d",$regs[1],$outdate);
                $outdate = ereg_replace("%H",$regs[4],$outdate);
                $outdate = ereg_replace("%M",$regs[5],$outdate);
                $outdate = ereg_replace("%S",$regs[6],$outdate);
            } else {
                $outdate = $date;
            }
        } else if (ereg ("%Y[/|-]%m[/|-]%d %H:%M:%S", $inFmt)) {
            if(ereg ("([0-9]{2,4})[/|-]([0-9]{1,2})[/|-]([0-9]{1,2}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
                for ($i=1;$i<7;$i++) {
                    if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
                }
                $outdate = $outFmt;
                $outdate = ereg_replace("%Y",$regs[1],$outdate);
                $outdate = ereg_replace("%m",$regs[2],$outdate);
                $outdate = ereg_replace("%d",$regs[3],$outdate);
                $outdate = ereg_replace("%H",$regs[4],$outdate);
                $outdate = ereg_replace("%M",$regs[5],$outdate);
                $outdate = ereg_replace("%S",$regs[6],$outdate);
            } else {
                $outdate = $date;
            }
        } else if (ereg ("%m[/|-]%d[/|-]%Y %H:%M:%S", $inFmt)) {
            if(ereg ("([0-9]{1,2})[/|-]([0-9]{1,2})[/|-]([0-9]{2,4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
                for ($i=1;$i<7;$i++) {
                    if ($regs[$i]=="" || !isset($regs[$i])) $regs[$i]="00";
                }
                $outdate = $outFmt;
                $outdate = ereg_replace("%Y",$regs[3],$outdate);
                $outdate = ereg_replace("%m",$regs[1],$outdate);
                $outdate = ereg_replace("%d",$regs[2],$outdate);
                $outdate = ereg_replace("%H",$regs[4],$outdate);
                $outdate = ereg_replace("%M",$regs[5],$outdate);
                $outdate = ereg_replace("%S",$regs[6],$outdate);
            } else {
                $outdate = $date;
            }
        } else {
        KT_DIE("KT-Conversion-1", "Unknown data format : ".$inFmt." .");
        }      
        return $outdate;
    }
    
		function KT_redir($url) {
			global $HTTP_SERVER_VARS;
			$protocol = "http://";;
			$server_name = $HTTP_SERVER_VARS["HTTP_HOST"];
			if ($server_name != '') {
				$protocol = "http://";;
				if (isset($HTTP_SERVER_VARS['HTTPS']) && ($HTTP_SERVER_VARS['HTTPS'] == "on")) {
					$protocol = "https://";;;
				}
				if (preg_match("#^/#", $url)) {
					$url = $protocol.$server_name.$url;
				} else if (!preg_match("#^[a-z]+://#", $url)) {
					$url = $protocol.$server_name.(preg_replace("#/[^/]*$#", "/", $HTTP_SERVER_VARS["PHP_SELF"])).$url;
				}
				header("Location: ".$url);
			}
			exit;
		}
		

/**
	register a variable in the session taking in account the PHP version
	@params
		$varname - variable name
		$value - variable value
	@return 
		- none
*/
function KT_session_register($varname, $value = null) {
	global $$varname;
	if (is_null($value)) {
		$value = $$varname;
	}
	if (version_compare(phpversion(), "4.1.0", "<")) { //if the version is smaller than php 4.1.0
		if (ini_get('register_globals') == '1') { //if register globals is on
			if ($value != null) {
				$$varname = $value;
			}
			session_register($varname);
		}
	} else {
		$_SESSION[$varname] = $value;
	}
	global $HTTP_SESSION_VARS;
	$HTTP_SESSION_VARS[$varname] = $value;
}
/**
	unregister a variable from the session taking in account the PHP version
	@params
		$varname - variable name
	@return 
		- none
*/
function KT_session_unregister($varname) {
	if (version_compare(phpversion(), "4.1.0", "<")) { //if the version is smaller than php 4.1.0
		if (ini_get('register_globals') == '1') { //if register globals is on
			global $$varname;
			session_unregister($varname);
		} 
		global $HTTP_SESSION_VARS;
		unset($HTTP_SESSION_VARS[$varname]);
		
	} else {
		unset($_SESSION[$varname]);
	}
}
/**
	Search an level name into an array of comma separated levels
	@params
		$levels - allowed levels
		$element - the element to be searched
	@return 
		- true if was found
		- false otherwise
*/
function KT_strpos($levels, $element){
    $to_array = explode(',', substr($levels,1)); // the first char is a white space.
	return in_array($element, $to_array, true);	
}
//normalize SERVER and ENV vars
if (!isset($HTTP_SERVER_VARS['QUERY_STRING']) && isset($HTTP_ENV_VARS['QUERY_STRING'])) {
	$HTTP_SERVER_VARS['QUERY_STRING'] = $HTTP_ENV_VARS['QUERY_STRING'];
}
if (!isset($HTTP_SERVER_VARS['REQUEST_URI']) && isset($HTTP_ENV_VARS['REQUEST_URI'])) {
	$HTTP_SERVER_VARS['REQUEST_URI'] = $HTTP_ENV_VARS['REQUEST_URI'];
}
if (!isset($HTTP_SERVER_VARS['REQUEST_URI'])) {
	$HTTP_SERVER_VARS['REQUEST_URI'] = $HTTP_SERVER_VARS['SCRIPT_NAME'].(isset($HTTP_SERVER_VARS['QUERY_STRING'])?"?".$HTTP_SERVER_VARS['QUERY_STRING']:"");
}
if (!isset($HTTP_SERVER_VARS['HTTP_HOST']) && isset($HTTP_ENV_VARS['HTTP_HOST'])) {
	$HTTP_SERVER_VARS['HTTP_HOST'] = $HTTP_ENV_VARS['HTTP_HOST'];
}
if (!isset($HTTP_SERVER_VARS['HTTPS']) && isset($HTTP_ENV_VARS['HTTPS'])) {
	$HTTP_SERVER_VARS['HTTPS'] = $HTTP_ENV_VARS['HTTPS'];
}
if (!isset($HTTP_SERVER_VARS['PATH_TRANSLATED']) && isset($HTTP_ENV_VARS['PATH_TRANSLATED'])) {
	$HTTP_SERVER_VARS['PATH_TRANSLATED'] = $HTTP_ENV_VARS['PATH_TRANSLATED'];
}
if (!isset($HTTP_SERVER_VARS['SCRIPT_FILENAME']) && isset($HTTP_ENV_VARS['SCRIPT_FILENAME'])) {
	$HTTP_SERVER_VARS['SCRIPT_FILENAME'] = $HTTP_ENV_VARS['SCRIPT_FILENAME'];
}

if (!isset($HTTP_SERVER_VARS['HTTP_REFERER']) && isset($HTTP_ENV_VARS['HTTP_REFERER'])) {
	$HTTP_SERVER_VARS['HTTP_REFERER'] = $HTTP_ENV_VARS['HTTP_REFERER'];
}
if (!isset($HTTP_SERVER_VARS['HTTP_USER_AGENT']) && isset($HTTP_ENV_VARS['HTTP_USER_AGENT'])) {
	$HTTP_SERVER_VARS['HTTP_USER_AGENT'] = $HTTP_ENV_VARS['HTTP_USER_AGENT'];
}

?>
