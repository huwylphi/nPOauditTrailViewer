<?php

/*
 * version: 1.0
 * date: 2015-03-04
 * developer: Ph. Huwyler
 */
 
// get reportOutputPath from URL or args
parse_str(implode('&', array_slice($argv, 1)), $CMDARG);

// get out path
if ( array_key_exists('out', $_GET) ) {
	$reportOutputPath = $_GET['out'];
} else if (array_key_exists('out', $CMDARG)) {
	$reportOutputPath = $CMDARG['out'];
}
if( !isset($reportOutputPath) ) {
	die("out arg not defined");
}

// get startDate
if ( array_key_exists('startDate', $_GET) ) {
	$startDate = $_GET['startDate'];
} else if (array_key_exists('startDate', $CMDARG)) {
	$startDate = $CMDARG['startDate'];
}
if( !isset($startDate) ) {
	die("startDate arg not defined");
}

// get endDate
if ( array_key_exists('endDate', $_GET) ) {
	$endDate = $_GET['endDate'];
} else if (array_key_exists('endDate', $CMDARG)) {
	$endDate = $CMDARG['endDate'];
}
if( !isset($endDate) ) {
	die("endDate arg not defined");
}

// case $reportOutputPath is not a file path but a dir path, generate a default file name
$path_parts = pathinfo($reportOutputPath);
if ( !array_key_exists('extension', $path_parts) ) {
	$reportOutputPath = $reportOutputPath."\\report_".$startDate."_".$endDate.".csv";
}

// read config file
$config_json = file_get_contents("config.json.txt");
$config=json_decode($config_json);

// get report data
$json = json_decode(file_get_contents($config->{'hostURL'}."getData.php?startDate=$startDate&endDate=$endDate"));

// write output csv file to $reportOutputPath if path is set
if (isset($reportOutputPath) && file_exists(dirname($reportOutputPath))) {
	$fp = fopen($reportOutputPath, 'w');

	if( $fp === FALSE ) {
		die(print_r(error_get_last()));
	}

	$startStr = date('d/m/Y', mktime(0, 0, 0, $json->{'period'}->{'from'}->{'month'}, $json->{'period'}->{'from'}->{'day'}, $json->{'period'}->{'from'}->{'year'}));
	$endStr = date('d/m/Y', mktime(0, 0, 0, $json->{'period'}->{'to'}->{'month'}, $json->{'period'}->{'to'}->{'day'}, $json->{'period'}->{'to'}->{'year'}));
	fputcsv($fp, array("Zeit: ".$startStr." - ".$endStr), $config->{'csv'}->{'delimiter'});

	$i=0;
	foreach ($json->{'data'} as $fields) {
		if($i <= 0) {
			fputcsv($fp, array_keys((array)$fields), $config->{'csv'}->{'delimiter'});
		}
		$trimmed_array=array_map('trim',(array)$fields);
		fputcsv($fp, $trimmed_array, $config->{'csv'}->{'delimiter'});
		$i++;
	}
	fclose($fp);
}
?>