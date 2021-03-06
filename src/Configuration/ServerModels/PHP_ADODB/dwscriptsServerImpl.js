// Copyright 2002 Macromedia, Inc. All rights reserved.

// *************** GLOBALS VARS *****************


//--------------------------------------------------------------------
// FUNCTION:
//   getFieldNames
//
// DESCRIPTION:
//   Returns an array of the field names for the given data source.
//
// ARGUMENTS:
//   dataSourceName - string - the name of the data source
//
// RETURNS:
//   JavaScript array of strings
//--------------------------------------------------------------------

function getFieldNames(dataSourceName) {
  sbList = dw.sbi.getServerBehaviors();
  for (i = 0;i < sbList.length;i++) {
    //if (sbList[i].name == "Recordset") {
	if (sbList[i].serverBehavior == "Recordset.htm") {
		if (dataSourceName == sbList[i].getParameter("RecordsetName")) {
			theSQL = sbList[i].getSQLForTest();
			return MMDB.getColumnList(sbList[i].getParameter("ConnectionName"),theSQL);
		}
	}
  }
  return Array();
}

//--------------------------------------------------------------------
// FUNCTION:
//   queueDefaultDocEdits
//
// DESCRIPTION:
//   This function is called before the docEdits are applied to the
//   page to allow any default doc edits to be added.  We will add
//   the PHP_ADODB language tag.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function queueDefaultDocEdits()
{
  var partList = dw.getParticipants("PageDirective_lang");
  if (!partList || partList.length == 0)  // does not currently exist
  {
    var paramObj = new Object();
    paramObj.version = MM.PHAkt_Version;
    dwscripts.queueDocEditsForParticipant("PageDirective_lang",paramObj);
  }
  else
  {
    dwscripts.queueParticipantInfo("PageDirective_lang", partList[0].participantNode);
  }
}


// ***************** LOCAL FUNCTIONS  ******************

//--------------------------------------------------------------------
// FUNCTION:
//   encodeDynamicExpression
//
// DESCRIPTION:
//   This function prepares a dynamic expression for insertion onto
//   the page.  It is assumed that this expression will be used
//   within a larger dynamic statement, therefore all server markup
//   is stripped.
//
// ARGUMENTS:
//   expression - string - the dyanmic expression to encode
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function encodeDynamicExpression(expression)
{
  var retVal = "";
  expression = expression.toString();

  if (hasServerMarkup(expression))
  {
    retVal = trimServerMarkup(expression);
  }
  else
  {
    var parameters = expression.match(/(true|false|[-]?\d+[\.]?\d*)/);
    if (parameters && parameters[0].length == expression.length) // matches, return exact
    {
      retVal = expression;
    }
    else
    {
      if (!dwscripts.isQuoted(expression))
      {
        retVal = "\"" + dwscripts.escQuotes(expression) + "\"";
      }
      else
      {
        retVal = expression;
      }
    }
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   decodeDynamicExpression
//
// DESCRIPTION:
//   This function prepares a dynamic expression for display within
//   a dialog box.  Quotes are removed,a nd server markup is re-added.
//
// ARGUMENTS:
//   expression - string - the dynamic expression to prepare for display
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function decodeDynamicExpression(expression)
{
  var retVal = "";

  expression = dwscripts.trim(expression.toString());
  var unquoted = dwscripts.trimQuotes(expression);

  var parameters = unquoted.match(/(true|false|[-]?\d+[\.]?\d*)/);
  if (parameters && parameters[0].length == unquoted.length) // matches, return exact
  {
    retVal = expression;
  }
  else
  {
    if (dwscripts.isQuoted(expression))
    {
      retVal = dwscripts.unescQuotes(unquoted);
    }
    else
    {
      retVal = "<?php echo " + expression + " ?>";
    }
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   hasServerMarkup
//
// DESCRIPTION:
//   This function returns true if the given expression contains
//   server markup.
//
// ARGUMENTS:
//   expression - string - the expression to test for server markup
//
// RETURNS:
//   boolean
//--------------------------------------------------------------------

function hasServerMarkup(expression)
{
  expression = expression.toString();
  return (expression.indexOf("<?") != -1 && expression.indexOf("?>") != -1);
}


//--------------------------------------------------------------------
// FUNCTION:
//   trimServerMarkup
//
// DESCRIPTION:
//   This function returns the given expression with any server markup
//   removed.
//
// ARGUMENTS:
//   expression - string - the expression to remove server markup from
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function trimServerMarkup(expression)
{
  var retVal = expression.toString();

  // Look for: <?php...?>, <?...?> and <?=...?>

  if (retVal.length)
  {
    var begininlineindex = retVal.indexOf("<?php");
    var endinlineindex  =  retVal.indexOf("?>");

  if ((begininlineindex != -1) && (endinlineindex!=-1))
    {
      retVal = retVal.substring(begininlineindex+5, endinlineindex);
    }
    else
    {
      begininlineindex = retVal.indexOf("<?=");

    if ((begininlineindex != -1) && (endinlineindex!=-1))
      {
        retVal = retVal.substring(begininlineindex+3, endinlineindex);
      }
    else
    {
        begininlineindex = retVal.indexOf("<?");

    if ((begininlineindex != -1) && (endinlineindex!=-1))
        {
          retVal = retVal.substring(begininlineindex+2, endinlineindex);
        }
    }
    }

    retVal = dwscripts.trim(retVal);

    // now look for echo at the start of the string

    var beginindex = retVal.indexOf("echo");

    if (beginindex == 0)
    {
      retVal = retVal.substring(4);
    }

    // strip the ; at the end of the string
    if (retVal.length > 0 && retVal.charAt(retVal.length - 1) == ";")
    {
      retVal = retVal.slice(0, retVal.length - 1);
    }

    retVal = dwscripts.trim(retVal);
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   getColumnValueNode
//
// DESCRIPTION:
//   This function returns a platform specific instance of the
//   ColumnValueNode class.  This function is called by dwscripts,
//   and serves as a factory method.
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   PHPColumnValueNode object
//--------------------------------------------------------------------

function getColumnValueNode()
{
  var retVal = new PHPColumnValueNode();
  return retVal;
}

//--------------------------------------------------------------------
// CLASS:
//   PHPColumnValueNode
//
// DESCRIPTION:
//   This class represents the mapping of a database column to a value.
//
// PUBLIC PROPERTIES:
//   None
//
// PUBLIC FUNCTIONS:
//   See the base class in:
//     Configuration/Shared/Common/Scripts/ColumnValueNodeClass.js
//
//--------------------------------------------------------------------

//--------------------------------------------------------------------
// FUNCTION:
//   PHPColumnValueNode
//
// DESCRIPTION:
//   Consructor function for the PHP specific ColumnValueNode class
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function PHPColumnValueNode()
{
  this.initialize();
}

// Inherit from the ColumnValueNode class.
PHPColumnValueNode.prototype.__proto__ = ColumnValueNode.prototype;

PHPColumnValueNode.prototype.getRuntimeValue = PHPColumnValueNode_getRuntimeValue;
PHPColumnValueNode.prototype.setRuntimeValue = PHPColumnValueNode_setRuntimeValue;
PHPColumnValueNode.prototype.getRuntimePlaceholder = PHPColumnValueNode_getRuntimePlaceholder;

PHPColumnValueNode.prototype.getDBColumnTypeAsString = PHPColumnValueNode_getDBColumnTypeAsString;

//--------------------------------------------------------------------
// FUNCTION:
//   PHPColumnValueNode.getRuntimeValue
//
// DESCRIPTION:
//   Returns the runtime value suitable for insertion into a SQL
//   statement, for this column value mapping.
//
//   NOTE: This is an override of a base class method
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function PHPColumnValueNode_getRuntimeValue()
{
  this.runtimeValue = "";

  if (this.varName)
  {
    var refType = ((! this.wrapChar) ? "none" : this.wrapChar) + "," + ((! this.altValue) ? "none" : this.altValue) + "," + ((! this.defaultValue) ? "none" : this.defaultValue);
    refType = refType.toLowerCase();

    var paramObj = new Object();
    paramObj.varName = this.varName.replace(/form./i,"");

    if (refType == "',none,none") // text
    {
      paramObj.colType = "text";
    }
    else if (refType == "none,0,null")
    {
      paramObj.colType = "int";
    }
    else if (refType == "none,.,null")
    {
      paramObj.colType = "double";
    }
    else if (refType == "',none,null")
    {
      paramObj.colType = "date";
    }
    else if (refType == "none,y,n")
    {
      paramObj.colType ="defined";
      paramObj.altVal = "'" + this.altValue + "'";
      paramObj.defVal = "'" + this.defaultValue + "'";
    }
    else if (refType == "none,1,0")
    {
      paramObj.colType ="defined";
      paramObj.altVal = this.altValue;
      paramObj.defVal = this.defaultValue;
    }
    else if (refType == "none,-1,0")
    {
      paramObj.colType ="defined";
      paramObj.altVal = this.altValue;
      paramObj.defVal = this.defaultValue;
    }
    else
    {
      alert("ERROR: unrecognized submitAs type: " + refType);
    }

    if (paramObj.colType != "defined")
    {
      this.runtimeValue = "GetSQLValueString(" + paramObj.varName + ", \"" + paramObj.colType + "\")";
    }
    else
    {
      this.runtimeValue = "GetSQLValueString(isset(" + paramObj.varName + ") ? \"true\" : \"\", \"" + paramObj.colType + "\",\"" + paramObj.altVal + "\",\"" + paramObj.defVal + "\")";
    }
  }

  return this.runtimeValue;
}



//--------------------------------------------------------------------
// FUNCTION:
//   PHPColumnValueNode.setRuntimeValue
//
// DESCRIPTION:
//   Given a runtime value from a SQL statement, this function sets
//   the properties of this object to match this runtime code.
//
//   NOTE: This is an override of a base class function
//
// ARGUMENTS:
//   runtimeValue - string - a SQL column value mapping
//
// RETURNS:
//   nothing
//--------------------------------------------------------------------

function PHPColumnValueNode_setRuntimeValue(runtimeValue)
{
  this.runtimeValue = runtimeValue;

  var colType = "";
  var altVal  = "";
  var defVal  = "";

  var match = runtimeValue.match(/GetSQLValueString\s*\((?:isset\()?\s*(\$HTTP_\w+_VARS\['\w+'\])\s*(?:\)\s*\?\s*"true"\s*:\s*""\s*)?,\s*"(\w+)"\s*(?:,"([^"]*)"\s*,\s*"([^"]*)"\s*)?\)/i);

  if (match)
  {
    this.varName = match[1];
    colType = match[2];
    if (colType == "defined")
    {
      altVal = (match[3]) ? match[3] : "Y";
      defVal = (match[4]) ? match[4] : "N";
    }
  }


  this.wrapChar     = "";
  this.altValue     = "";
  this.defaultValue = "";

  if (colType == "text")
  {
    this.wrapChar     = "'";
    this.altValue     = "none";
    this.defaultValue = "none"
  }
  else if (colType == "int")
  {
    this.wrapChar     = "none";
    this.altValue     = "0";
    this.defaultValue = "NULL";
  }
  else if (colType == "double")
  {
    this.wrapChar     = "none";
    this.altValue     = ".";
    this.defaultValue = "NULL";
  }
  else if (colType == "date")
  {
    this.wrapChar     = "'";
    this.altValue     = "none";
    this.defaultValue = "NULL";
  }
  else if (colType == "defined")
  {
    this.wrapChar     = "none";
    if (altVal == "'Y'")
    {
      this.altValue     = "Y";
    }
    else
    {
    this.altValue     = altVal;
    }
    if (defVal == "'N'")
    {
      this.defaultValue = "N";
    }
    else
    {
    this.defaultValue = defVal;
  }
}
}


//--------------------------------------------------------------------
// FUNCTION:
//   PHPColumnValueNode.getRuntimePlaceholder
//
// DESCRIPTION:
//   This function returns the value to use in the sql sprintf 
//   statement
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function PHPColumnValueNode_getRuntimePlaceholder()
{
  return "%s";
}


//--------------------------------------------------------------------
// FUNCTION:
//   PHPColumnValueNode.getDBColumnTypeAsString
//
// DESCRIPTION:
//   Returns a string representation of a column type, suitable
//   for passing to the GetSQLValueString() function which is inserted
//   into PHP documents for Edit Ops server behaviors
//
// ARGUMENTS:
//   none
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function PHPColumnValueNode_getDBColumnTypeAsString()
{
  var retVal = "";

  // TODO - DHULSE - support for "defined" type for GetSQLValueString()...

  if (dwscripts.isStringDBColumnType(this.columnType)){
    retVal = "text";
  }
  else if (dwscripts.isIntegerDBColumnType(this.columnType)){
    retVal = "int";
  }
  else if (dwscripts.isDoubleDBColumnType(this.columnType)){
    retVal = "double";
  }
  else if (dwscripts.isDateDBColumnType(this.columnType)){
    retVal = "date";
  }
  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   getParameterTypeArray
//
// DESCRIPTION:
//   Get list of available parameter types.
//
// ARGUMENTS:
//   bRemoveEnteredVal - boolean (optional). 'true' if should remove 'Entered Value'
//     as a possible parameter type. Defaults to 'false'.
//
// RETURNS:
//   array of strings - localized list of parameter types.
//--------------------------------------------------------------------

function getParameterTypeArray(bRemoveEnteredVal)
{
  // Make a copy of MM.LABEL_PHP_Param_Types. We may need to alter it and we
  //   don't want to affect the original array.
  var paramTypes = new Array();
  for (var i = 0; i < MM.LABEL_PHP4_Param_Types.length; ++i)
  {
    paramTypes.push(MM.LABEL_PHP4_Param_Types[i]);
  }

  if (bRemoveEnteredVal)
  {
    paramTypes.splice(paramTypes.length - 1, 1);
  }

  return paramTypes;
}

//--------------------------------------------------------------------
// FUNCTION:
//   getParameterCodeFromType
//
// DESCRIPTION:
//   Gets the runtime code and default value for the parameter type.
//
// ARGUMENTS:
//   paramType - string. one of elements returned from getParameterTypeArray.
//   paramNameOrValue - string. Value for the parameter.
//
// RETURNS:
//   object - with runtimeVal, defaultVal, and nameVal properties. null
//     if no parameter is used.
//--------------------------------------------------------------------

function getParameterCodeFromType(paramType, paramNameOrValue, paramDefault)
{
  var runtimeVal = dwscripts.sprintf(MM.MSG_UnknownParamType, paramType);
  var nameVal = "";
  var defaultVal = "-1";

  switch(paramType)
  {
    case MM.LABEL_PHP4_Param_Types[0]:
      runtimeVal = "$HTTP_GET_VARS['" + paramNameOrValue + "']";
      nameVal = runtimeVal;
      break;
    case MM.LABEL_PHP4_Param_Types[1]:
      runtimeVal = "$HTTP_POST_VARS['" + paramNameOrValue + "']";
      nameVal = runtimeVal;
      break;
    case MM.LABEL_PHP4_Param_Types[2]:
      runtimeVal = "$HTTP_COOKIE_VARS['" + paramNameOrValue + "']";
      nameVal = runtimeVal;
      break;
    case MM.LABEL_PHP4_Param_Types[3]:
      runtimeVal = "$HTTP_SESSION_VARS['" + paramNameOrValue + "']";
      nameVal = runtimeVal;
      break;
    case MM.LABEL_PHP4_Param_Types[4]:
      runtimeVal = "$HTTP_SERVER_VARS['" + paramNameOrValue + "']";
      nameVal = runtimeVal;
      break;
    case MM.LABEL_PHP4_Param_Types[5]:
      runtimeVal = paramNameOrValue;
      nameVal = runtimeVal;
      break;
  }

  var outObj = new Object();
  if (paramType == MM.LABEL_PHP4_Param_Types[6])
  {
    outObj = null;
  }
  else
  {
    outObj.defaultVal = defaultVal;
    outObj.runtimeVal = runtimeVal;
    outObj.nameVal = nameVal;
  }

  return outObj;
}


//--------------------------------------------------------------------
// FUNCTION:
//   getParameterTypeFromCode
//
// DESCRIPTION:
//   Get parameter type and name from its runtime value.
//
// ARGUMENTS:
//   runtimeValue - string - the runtime code
//
// RETURNS:
//   object - contains paramType (one of elements returned from getParameterTypeArray)
//     and paramName properties.
//--------------------------------------------------------------------

function getParameterTypeFromCode(runtimeValue)
{
  var runtimeVal = runtimeValue;

  var outObj = new Object();

  var paramType = -1;
  var paramName = "";

  if (runtimeVal.search(/\s*\$HTTP_GET_VARS\['([^']*)'\]\s*/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[0];
  }
  else if (runtimeVal.search(/\s*\$HTTP_POST_VARS\['([^']*)'\]\s*/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[1];
  }
  else if (runtimeVal.search(/\s*\$HTTP_COOKIE_VARS\['([^']*)'\]\s*/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[2];
  }
  else if (runtimeVal.search(/\s*\$HTTP_SESSION_VARS\['([^']*)'\]\s*/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[3];
  }
  else if (runtimeVal.search(/\s*\$HTTP_SERVER_VARS\['([^']*)'\]\s*/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[4];
  }
  else if (runtimeVal.search(/^\s*\$/) != -1)
  {
    paramType = MM.LABEL_PHP4_Param_Types[5];
  } else {
    paramType = MM.LABEL_PHP4_Param_Types[6];
	}

  if ((paramType == MM.LABEL_PHP4_Param_Types[5]) ||
			(paramType == MM.LABEL_PHP4_Param_Types[6]))
  {
    paramName = runtimeValue;
  }
  else
  {
    paramName = RegExp.$1;
  }

  if (paramType != -1)
  {
    outObj.paramType = paramType;
    outObj.paramName = paramName;
    return outObj;
  }
  else
  {
    return false;
  }
}


//--------------------------------------------------------------------
// FUNCTION:
//   canInsertPageNavDisplay
//
// DESCRIPTION:
//   This function determines if a pageNavDisplay behavior can be added
//   to the current document.  It returns an appropriate error message
//   if one cannot be inserted.
//
// ARGUMENTS:
//   recordsetName - string - the recordset that the item will be
//     associated with.
//   ignoreCase - boolean - ignore case when matching recordset name
//   isAll - boolean - indicates if the page nav display being inserted
//     will loop over all records.
//
// RETURNS:
//   string - empty if successful, or error message
//--------------------------------------------------------------------

function canInsertPageNavDisplay(recordsetName, ignoreCase, isAll )
{
  var retVal = "";

  var sbObjs = dwscripts.getPageNavDisplaySBs(recordsetName, ignoreCase, true);
  if (sbObjs && sbObjs.length > 0)
  {
    retVal = dwscripts.sprintf(MM.MSG_RepeatRegionExistsForRecordset,recordsetName);
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   encodeSQLTableRef
//
// DESCRIPTION:
//   Returns a table reference suitable for use within a SQL statement.
//   Wraps the reference in back ticks if the table name contains a space,
//   begins with an underscore, or begins with a number.
//
// ARGUMENTS:
//   tableName - string - the table name we are referencing
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function encodeSQLTableRef(tableName)
{
  var retVal = tableName;

  if (tableName.charAt(0) == "_")
  {
    retVal = "`" + retVal + "`";
  }
  else if (dwscripts.isNumber(tableName.charAt(0)))
  {
    retVal = "`" + retVal + "`";
  }
  else if (tableName.indexOf(" ") != -1)
  {
    retVal = "`" + retVal + "`";
  }
  else if (dwscripts.isSQLReservedWord(tableName))
  {
    retVal = "`" + retVal + "`";
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   encodeSQLColumnRef
//
// DESCRIPTION:
//   Returns a column reference suitable for use within a SQL statement.
//   Adds the table name qualifier if needed, and wraps the reference
//   in quotes if the column name contains a space, starts with an
//   underscore, or starts with a number.
//
// ARGUMENTS:
//   tableName - string - the table name to be used if the column is
//     not unique
//   columnName - string - the column name to return a reference to
//   fromClause - string - the from clause of the SQL statement that
//     this reference is being generated for. If the fromClause
//     lists only one table, and it matches tableName, then the table
//     name is not used as a prefix to the reference.
//
// RETURNS:
//   string
//--------------------------------------------------------------------

function encodeSQLColumnRef(tableName, columnName)
{
  var retVal = "";

  cName = columnName;
  if (columnName.charAt(0) == "_")
  {
    cName = "`" + cName + "`";
  }
  else if (dwscripts.isNumber(columnName.charAt(0)))
  {
    cName = "`" + cName + "`";
  }
  else if (columnName.indexOf(" ") != -1)
  {
    cName = "`" + cName + "`";
  }
  else if (dwscripts.isSQLReservedWord(columnName))
  {
    cName = "`" + cName + "`";
  }

  var tName = encodeSQLTableRef(tableName);

  if (tName)
  {
    retVal = tName + "." + cName;
  }
  else
  {
    retVal = cName;
  }

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   decodeSQLTableRef
//
// DESCRIPTION:
//   <description>
//
// ARGUMENTS:
//   <arg1> - <type and description>
//
// RETURNS:
//   <type and description>
//--------------------------------------------------------------------

function decodeSQLTableRef(theRef)
{
  var retVal = theRef;

  if (theRef.indexOf("`") != -1)
  {
    retVal = dwscripts.stripChars(retVal, "`");
  }

  retVal = dwscripts.trim(retVal);

  return retVal;
}


//--------------------------------------------------------------------
// FUNCTION:
//   decodeSQLColumnRef
//
// DESCRIPTION:
//   <description>
//
// ARGUMENTS:
//   <arg1> - <type and description>
//
// RETURNS:
//   <type and description>
//--------------------------------------------------------------------

function decodeSQLColumnRef(theRef)
{
  var retVal = theRef;

  if (theRef.indexOf("`") != -1)
  {
    retVal = dwscripts.stripChars(retVal, "`");
  }

  retVal = dwscripts.trim(retVal);

  // remove the table prefix if it exists
  if (retVal.lastIndexOf(".") != -1)
  {
    retVal = retVal.substring(retVal.lastIndexOf(".")+1);
  }

  return retVal;
}


function getDBColumnTypeEnum(columnType) {

	// Set the default to 129 to work around a bug, where the "text"
	//  data type for SQL Server was returning blank.
	var retVal = 129;

	columnType = String(columnType);
	if (dwscripts.isNumber(columnType) && dwscripts.getNumber(columnType) >= 0)
	{
		retVal = dwscripts.getNumber(columnType);
	}
	else
	{
		if (dwscripts.DB_COLUMN_ENUM_MAP == null)
		{
			// TODO: read in from a configuration file
			var a = new Array();

			//from the ASP book
			a["empty"] = 0;
			a["smallint"] = 2;
			a["integer"] = 3;
			a["single"] = 4;
			a["double"] = 5;
			a["currency"] = 6;
			a["date"] = 7;
			a["bstr"] = 8;
			a["idispatch"] = 9;
			a["error"] = 10;
			a["boolean"] = 11;
			a["variant"] = 12;
			a["iunknown"] = 13;
			a["decimal"] = 14;
			a["tinyint"] = 16;
			a["unsignedtinyint"] = 17;
			a["unsignedsmallint"] = 18;
			a["unsignedint"] = 19;
			a["bigint"] = 20;
			a["ebigint"] = 20; // matched to bigint
			a["unsignedbigint"] = 21;
			a["guid"] = 72;
			a["binary"] = 128;
			a["char"] = 129;
			a["wchar"] = 130;
			a["numeric"] = 131;
			a["varnumeric"] = 131;
			a["userdefined"] = 132;
			a["dbdate"] = 133;
			a["dbtime"] = 134;
			a["dbtimestamp"] = 135;
			a["varchar"] = 200;
			a["longchar"] = 201;
			a["longvarchar"] = 201;
			a["memo"] = 201;
			a["varwchar"] = 202;
			a["string"]=201;
			a["longvarwchar"] = 203;
			a["varbinary"] = 204;
			a["longvarbinary"] = 204; 
			a["longbinary"] = 205; // matched to longvarbinary

			//others
			a["money"] = 6;
			a["int"] = 3; //integer
			a["counter"] = 131; //numeric
			a["logical"] = 901; //bit
			a["byte"] = 16; //tinyint

			// firebird 
			a["varying"] = 200; //varchar

			//oracle
			a["varchar2"] = 200;
			a["smalldatetime"] = 135;
			a["datetime"] = 135;
			a["number"] = 5; //double
			a["ref cursor"] = 900; //Arbitrary ID Val
			a["refcursor"] = 900;
			a["bit"] = 901; //Arbitrary ID Val;
			a["long raw"] = 20; // Match it to BigInt
			a["clob"] = 129; //char
			a["long"] = 20; // bigint
			a["double precision"] = 131; //numeric
			a["raw"]  = 204;// Match it to Binary
			a["nclob"]  = 204;//Match it to Binary
			a["bfile"]  = 204;//Match it to Binary
			a["rowid"]  = 129 ;//Match it to Hexadecimal String 
			a["urowid"] = 129 ;//Match it to Hexadecimal String

			//odbc
			a["empid"] = 129; //char
			a["tid"] = 129;
			a["bit"] = 901; 
			a["id"] = 200; //varchar

			// SQL Server 7
			a["smallmoney"] = 6; //currency
			a["float"] = 5; //double
			a["nchar"] = 200; //varchar
			a["real"] = 131; //numeric
			a["text"] = 200; //varchar
			a["blob"] = 200       // matched to text
			a["tinyblob"] = 200   // matched to text
			a["mediumblob"] = 200 // matched to text
			a["longblob"]  = 200  // matched to text
			a["timestamp"] = 135; //numeric
			a["sysname"] = 129;
			a["int identity"] = 131; //numeric counter
			a["smallint identity"] = 131; //numeric counter
			a["tinyint identity"] = 131; //numeric counter
			a["bigint identity"] = 131; //numeric counter
			a["decimal() identity"] = 131; //numeric counter
			a["numeric() identity"] = 131; //numeric counter
			a["uniqueidentifier"] = 131;//numeric
			a["ntext"]  = 200; //varchar
			a["nvarchar"] = 200; //varchar
			a["nvarchar2"] = 200; //varchar
			a["image"]  =  204 ;// binary

			// DB2
			a["time"] = 135; // needs '
			a["character () for bit data"] = 129;
			// the following entries are already defined to 200 for SQL Server
			//a["blob"] = 128; //binary
			//a["tinyblob"] = 128; //binary
			//a["mediumblob"] = 128; //binary
			//a["longblob"] = 128; //binary
			a["long varchar for bit data"] = 200; //varchar
			a["varchar () for bit data"] = 200; //varchar
			a["long varchar"] = 131; //numeric
			a["character"] = 129; //char

			//JDBC Specifc constants
			a["-8"] = 200; //JDBC varchar
			a["-9"] = 200; //JDBC varchar
			a["-10"] = 200; //JDBC varchar
			a["other"] = 200; //JDBC varchar

			//MySQL
			a["year"] = 133; //dbdate
			a["tinytext"] = 200; //varchar
			a["mediumtext"] = 200; //varchar
			a["longtext"] = 201; //longvarchar
			a["mediumint"] = 3; //integer
			a["enum"] = 200; //var char
			a["int unsigned"] = 19;
			a["tinyint unsigned"] = 19;
			
			//PostgreSQL
			a["abstime"] = 135; //
			a["aclitem"] = 132; //userdefined
			a["box"] = 	200; //varchar
			a["bytea"] = 3;		//integer
			a["varbit"] = 3;	//integer
			a["float8"] = 5; //double
			a["float4"] = 5; //double
			a["real"] = 5; //double
			a["int8"] = 3; //integer
			a["int4"] = 3; //integer
			a["int2vector"] = 3; //integer
			a["int2"] = 2; //small int
			a["timestamptz"] = 135;
			a["bool"] = 200; //varchar
			a["bpchar"] = 200; //varchar
			a["serial"] = 2; //small int
			a["character_data"] = 200; //varchar
			a["cardinal_number"] = 3; //varchar
			
			a["set"] = 132; //userdefined
			a["double unsigned zerofill"] = 5; //double
			a["float unsigned zerofill"] = 5; //double
			
			//Access
			a["i"] = 4;
			a['I'] = 4;
			a["c"] = 8;
			a["C"] = 8;
			a["N"] = 16;
			a["n"] = 200;
			a["d"] = 7;
			a["L"] = 3;
			a["x"] = 200;
			a["X"] = 200;

			dwscripts.DB_COLUMN_ENUM_MAP = a;
		}

		if (dwscripts.DB_COLUMN_ENUM_MAP != null)
		{
			var cType = columnType.toLowerCase();
			cType = cType.replace(/\s*unsigned/i,"");
			cType = cType.replace(/\s*zerofill/i,"");
			retVal = dwscripts.DB_COLUMN_ENUM_MAP[cType];

			if (retVal == null)
			{
				alert(dwscripts.sprintf(MM.MSG_SQLTypeAsNumNotInMap,columnType));
				retVal = 0;
			}
		}
	}
	
	return retVal;
}
