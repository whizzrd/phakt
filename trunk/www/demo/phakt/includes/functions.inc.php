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
    return preg_replace("/&" . $paramName . "=[^&]+/", "", $qstring);
  }


  function KT_keepParams($paramName) {
    global $MM_keepURL, $MM_keepForm, $MM_keepBoth, $MM_keepNone, $HTTP_GET_VARS, $HTTP_POST_VARS;
    $MM_keepURL="";
    $MM_keepForm="";
    $MM_keepBoth="";
    $MM_keepNone="";
    // add the URL parameters to the MM_keepURL string
    while (list($key, $value)=each($HTTP_GET_VARS)) {
      if ($key != $paramName) {
        $MM_keepURL .= "&" . $key . "=" . urlencode($value);
      }
    }

    // add the Form variables to the MM_keepForm string
    while (list($key, $value)=each($HTTP_POST_VARS)) {
      if ($key != $paramName) {
        $MM_keepForm .= "&" . $key . "=" . urlencode($value);
      }
    }

    // create the Form + URL string and remove the intial '&' from each of the strings
    $MM_keepBoth = $MM_keepURL . $MM_keepForm;
    if (strlen($MM_keepBoth) > 0) $MM_keepBoth = substr($MM_keepBoth,1);
    if (strlen($MM_keepURL) > 0)  $MM_keepURL  = substr($MM_keepURL,1);
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
            if($value=="NULL"){
                $value="";
            }
            $this->fields[$field]=$value;
        }
        
        function Fields($field){
            return $this->fields[$field];
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
      if(eregi("$param=[^&]*",$KT_Url)){
         $KT_Url = eregi_replace("$param=[^\&]*", "$param=$value", $KT_Url);
      }else {
         $KT_Url .="$sep$param=$value";
      }
      if ($value == "") {
        $KT_Url = preg_replace("/$param=/", "", $KT_Url);
      }
      $KT_Url = str_replace("?&", "?", $KT_Url);
      $KT_Url = eregi_replace("&+$", "", $KT_Url);
      $KT_Url = preg_replace("/\?$/", "", $KT_Url);
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
        if (ereg ("%d[/|-]%m[/|-]%Y %H:%M:%S", $inFmt)) {
            if(ereg ("([0-9]{1,2})[/|-]([0-9]{1,2})[/|-]([0-9]{2,4}) *([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}:{0,1}([0-9]{1,2}){0,1}", $date, $regs)){
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
			if (isset($HTTP_SERVER_VARS['HTTPS']) && ($HTTP_SERVER_VARS['HTTPS'] == "on")) {
				$protocol = "https://";;
			}
			if (preg_match("#^/#", $url)) {
				//$url = "http://".$HTTP_SERVER_VARS["HTTP_HOST"].$url;
				$url = $protocol.$HTTP_SERVER_VARS["HTTP_HOST"].$url;
			} else if (!preg_match("#^[a-z]+://#", $url)) {
				//$url = "http://".$HTTP_SERVER_VARS["HTTP_HOST"].(preg_replace("#/[^/]*$#";, "/", $HTTP_SERVER_VARS["PHP_SELF"])).$url;
				$url = $protocol.$HTTP_SERVER_VARS["HTTP_HOST"].(preg_replace("#/[^/]*$#", "/", $HTTP_SERVER_VARS["PHP_SELF"])).$url;
			}
			header("Location: ".$url);
			exit;
		}
?>