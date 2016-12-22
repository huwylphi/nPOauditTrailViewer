<?php

/*
 * version: 1.0
 * date: 2015-03-04
 * developer: Ph. Huwyler
 */
 
// get start-date and end-date for last month
$startDate = date('Y-m-d', strtotime(date('Y')."-01-01 -1 year"));
$endDate = date('Y-m-d', strtotime(date('Y')."-01-01 -1 day"));

// export data to csv (out path will be got in exportCsv.php).
require('exportCsv.php');
?>