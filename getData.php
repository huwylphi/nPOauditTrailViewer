<?php

/*
 * version: 1.3
 * date: 2015-09-24
 * developer: Ph. Huwyler

 * URL/ARG PARAMETERS:
 *
 * startDate (optional):	start period of report, default is 1970-01-01
 * endDate (optional):		end period of report, default is now
 * dynFilterList (optional):an AND SQL filter url encoded
 * top (optional):			select the top x items
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
 
$startTimestamp = "'1970-01-01 00:00'";			// default start period = begin of Unix timestamps
$endTimestamp = "'".date("Y-m-d H:i:s")."'";	// default end period = now

parse_str(implode('&', array_slice($argv, 1)), $CMDARG);

// Get startDate from URL
if ( array_key_exists('startDate', $_GET) ) {
	$startTimestamp = "'".$_GET['startDate']." 00:00'";
} else if (array_key_exists('startDate', $CMDARG)) {
	$startTimestamp = $CMDARG['startDate'];
}

// Get endDate from URL
if ( array_key_exists('endDate', $_GET) ) {
	$endTimestamp = "'".$_GET['endDate']." 23:59:59'";
} else if (array_key_exists('endDate', $CMDARG)) {
	$endTimestamp = $CMDARG['endDate'];
}

// Get dynFilterList from URL
if ( array_key_exists('dynFilterList', $_GET) ) {
	$dynFilterListParam = $_GET['dynFilterList'];
} else if (array_key_exists('dynFilterList', $CMDARG)) {
	$dynFilterListParam = $CMDARG['dynFilterList'];
}

// Get top param from URL
if ( array_key_exists('top', $_GET) ) {
	$topParam = $_GET['top'];
} else if (array_key_exists('top', $CMDARG)) {
	$topParam = $CMDARG['top'];
}

// Read config file
$config_json = file_get_contents("config.json.txt");
$config=json_decode($config_json);

// Connect to SQL-Server DB
$connectionInfo = array(
	"Database"=>$config->{'sqlServer'}->{'DBname'},
	"UID"=>$config->{'sqlServer'}->{'userName'},
	"PWD"=>$config->{'sqlServer'}->{'password'},
	'ReturnDatesAsStrings'=>true
);

$conn = sqlsrv_connect(
	$config->{'sqlServer'}->{'serverName'},
	$connectionInfo
);

if( $conn === FALSE ) {
	die("Connection could not be established. ".print_r( sqlsrv_errors(), true));
}

//Execute the SQL-query with a scrollable cursor so we can determine the number of rows returned.
$params = array(&$_POST['query']);
$cursorType = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

$arrSqlResult = array();

// Build static filter list from config file
$staticFilterList = "";
$i=0;
foreach($config->{'sqlServer'}->{'staticFilterList'} as $staticFilter) {
	if(!empty($staticFilter)) {
		if($i<=0) {
			$staticFilterList .= $staticFilter;
			$i++;
		} else {
			$staticFilterList .= " AND ".$staticFilter;
			$i++;
		}
	}
}

// Build dynamic filter list from url params
$dynFilterList = "";
if(isset($dynFilterListParam)) {
	foreach(json_decode($dynFilterListParam) as $dynFilter) {
		if(!empty($dynFilter)) {
			$dynFilterList .= " AND ".$dynFilter;
		}
	}
}

// Build top filter from url params
$top = "";
if(isset($topParam)) {
	if(is_numeric($topParam)) {
		$top = "TOP ".abs(intval($topParam));
	}
}

// Check if VfiTag DB is attached. If yes get TAG description too
$sqlQuery = "SELECT name FROM master..sysdatabases WHERE name = 'VfiTag'";
$stmt = sqlsrv_query( $conn, $sqlQuery, $params, $cursorType);
$VfiTagDBattached = false;
if( sqlsrv_num_rows ($stmt)>0 ) {
	$VfiTagDBattached = true;
}
sqlsrv_free_stmt($query);

// Execute SQL-query
if($VfiTagDBattached) {
	$sqlQuery = "SELECT ".$top." a.* FROM
	((SELECT "
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserName],"
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceType],"
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceID],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[Action],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagValue],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagStr],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[ZoneName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[MacroName],"
	//.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction],"	// no formating: row as in db table
	."CONVERT(VARCHAR(16),".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction], 120) as TimeAction,"		// date formating: yyyy-mm-dd hh:mm
	//."CONVERT(VARCHAR(19),".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction], 120) as TimeAction,"	// date formating: yyyy-mm-dd hh:mm:ss
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserFullName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserDescription],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[StationName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[PreviousTagValue],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[PreviousTagStr],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[Object],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[info1],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[info2],
	[VfiTag].[dbo].[VfiTagRef].description as TagDescription
		FROM ".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail]
		INNER JOIN [VfiTag].[dbo].[VfiTagRef] ON RIGHT(".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName], LEN(".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName]) - CHARINDEX(':', ".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName])) = RIGHT([VfiTag].[dbo].[VfiTagRef].[name], LEN([VfiTag].[dbo].[VfiTagRef].[name]) - CHARINDEX(':', [VfiTag].[dbo].[VfiTagRef].[name]))
		WHERE TimeAction >= ".$startTimestamp." AND TimeAction <= ".$endTimestamp.")
	UNION
	(SELECT "
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserName],"
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceType],"
	.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[SourceID],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[Action],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagValue],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagStr],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[ZoneName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[MacroName],"
	//.$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction],"	// no formating: row as in db table
	."CONVERT(VARCHAR(16),".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction], 120) as TimeAction,"		// date formating: yyyy-mm-dd hh:mm
	//."CONVERT(VARCHAR(19),".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TimeAction], 120) as TimeAction,"	// date formating: yyyy-mm-dd hh:mm:ss
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserFullName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[UserDescription],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[StationName],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[PreviousTagValue],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[PreviousTagStr],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[Object],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[info1],"
    .$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[info2],
	'' as TagDescription
		FROM ".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail]
		WHERE ".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail].[TagName] = '' AND TimeAction >= ".$startTimestamp." AND TimeAction <= ".$endTimestamp.")) as a
	WHERE ".$staticFilterList.$dynFilterList."
	ORDER BY a.TimeAction desc";
} else {
	$sqlQuery = "SELECT ".$top." *
		FROM ".$config->{'sqlServer'}->{'DBname'}.".[dbo].[AuditTrail]
		WHERE TimeAction >= ".$startTimestamp." AND TimeAction <= ".$endTimestamp." AND ".$staticFilterList.$dynFilterList."
		ORDER BY TimeAction desc";	
}
//echo $sqlQuery; //for testing
$stmt = sqlsrv_query( $conn, $sqlQuery, $params, $cursorType);

if( $stmt === FALSE ) {
	 die( print_r( sqlsrv_errors(), true));
}

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
	array_push( $arrSqlResult, $row);
}
//print_r($arrSqlResult); //for testing

// Free the statement and connection resources
sqlsrv_free_stmt( $stmt );

// Close the connection
sqlsrv_close( $conn );

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}
// Return result array an json string
echo "{\"period\":{\"from\":".json_encode(date_parse(str_replace("'","",$startTimestamp))).",\"to\":".json_encode(date_parse(str_replace("'","",$endTimestamp)))."},\"data\":".json_encode( utf8ize($arrSqlResult) )."}";
?>