<?php
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
# VERSION       : 4.00
# Author:       : Wim van der Kuil
# Documentation 
#   and support : https://leuven-template.eu/
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) 
     {  $filenameReal = __FILE__;			
        $download_size = filesize($filenameReal);
        header('Pragma: public');
        header('Cache-Control: private');
        header('Cache-Control: no-cache, must-revalidate');
        header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
        header("Content-Length: $download_size");
        header('Connection: close');
        readfile($filenameReal);
        exit;}
#-----------------------------------------------
$pageName	= 'wsReportsDaily.php';
$pageVersion	= '4.00 2017-12-16'; 
$pageUpdated	= 'first version '; 
#--------------------------------------- History
# 4.00 2017-12-16 first version	
# ---------------------------------- Houskeeping
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion.' | '.$pageUpdated;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
$SITE['wsModules'][$pageLoaded]	= $string;
ws_message ('<!-- loaded script: '.$pageLoaded.' '.$string.' -->');
# ------------------------------------- SETTINGS
#
#  this script generates the month table
#
# --------- create table for this year/season and fill with '---' or empty cells for not existing days (feb 30 example)
$arrResults = array();
#	set column and row descriptions
if (!isset ($yearArray[0]) ) {$year = date ('Y');}
for ($i = 0; $i < 12; $i++) {
	$month = (string) $i+1;
	if (strlen ( $month == 1 ) ){$month = '0'.$month;} 
	for ($k = 1; $k <= 31; $k++) {
		$day	= (string) $k;
		if (strlen ( $day == 1 ) ){$day = '0'.$day;}
		$date	= strtotime ($year.'-'.$month.'-'.$day);
		$compare= date('d',$date);
		if ($day == $compare) {$fill = $noValue;} else {$fill = $empty;}
		for ($n = 0; $n < $valuesMonth; $n++) {
	#		$col	= $n +$valuesMonth*$i;
			$result	= $monthsArr[(int)$month]; 	// contains the starting colom if there is only one value
			$col	= (int) ($n +  ($valuesMonth * ($result) ) );	
			$arrResults[$k][$col] = $fill;
		}
	}
}
#
# -------------    load data in resulting array  -------------------------------
if (!isset ($yearArray[0]) ) {
	echo '<h3 style="text-align: center;">'.wsReporttransstr($trans.'no data for the selected period available "yet"').'</h3>';
	$endInput	= 0;} 
else {  $endInput	= count ($yearArray[0]); }  // input array
$endRequest	= count($request);
for ($i = 0; $i < $endInput; $i++) {
	$date	= $yearArray[0][$i];
	$month	= (int) substr($date,4,2);
	$day	= (int) substr($date,6,2);
	for ($k = 0; $k < $endRequest; $k++) {
		$value	= $yearArray[$k+1][$i];
		$result	= $monthsArr[$month]; 	// contains the starting colom if there is only one value
		$col	= (int) ($k +  ($valuesMonth * ($result) ) );
		$arrResults[$day][$col]	= $value;		
	}
}
$colWidthMonth = $colWidthLevel = 'style="width: ';
switch ($valuesMonth) {
    case 0:
    case 1:
    	$colWidthMonth .= '7.5%"';
    	$colWidthLevel	= '';
    break;
    case 2:
    	$colWidthMonth	= '';
    	$colWidthLevel .= '3.75%"';
    break;
    default:
    	$colWidthMonth	= '';
    	$colWidthLevel .= '1.75%"';
}

echo '<table id="'.$levelArr.'" class="genericTable" style="">'.PHP_EOL;
# headers
if ($seasonal) {$rowspan = $valuesMonth + 1;} else {$rowspan = $valuesMonth;}
echo '<tr>';
echo '<th rowspan="'.$rowspan.'" class="table-top">'.wsReporttransstr($trans.'day').'</th>';
if ($seasonal) {
	$colspan = 3* $valuesMonth;
	echo '<th colspan = "'.$colspan.'" class="table-top">'.
	wsReporttransstr($trans.'winter').'</th><th colspan = "'.$colspan.'" class="table-top">'.
	wsReporttransstr($trans.'spring').'</th><th colspan = "'.$colspan.'" class="table-top">'.
	wsReporttransstr($trans.'summer').'</th><th colspan = "'.$colspan.'" class="table-top">'.
	wsReporttransstr($trans.'autumn').'</th></tr>'.PHP_EOL;
}
$levels		= '<tr>';
$headStr	= '';
for ($i = 1; $i <= $months; $i++) {
	$head	= wsReporttransstr($trans.$monthNamesShort[$i]);
	$headStr.= '<th colspan="'.$valuesMonth.'" class="table-top" '.$colWidthMonth.'>'.$head.'</th>';
	if ($valuesMonth > 1) {
		for ($k = 0; $k < $valuesMonth; $k++) {
			$num		= $request[$k];
			$fieldname	= $fields[$num];
			$from = array ('C','F','MPH','KMH','In','CM','hPa');
			$fieldname	= str_replace($from,'',$fieldname);
			$text 		= wsReporttransstr($trans.$fieldLookup[$fieldname]['level']);
			$levels	.= '<th class="table-top" '.$colWidthLevel.'>'.$text.'</th>';
		}	
	}
}
echo $headStr.'</tr>'.PHP_EOL;
if ($valuesMonth > 1) { echo $levels.'</tr>'.PHP_EOL;}
#
# details
#
$arrTotals	= array();
for ($i = 0; $i < 6; $i++) {
	for ($k = 0; $k < $cols; $k++) {
		if ($i == 3) {$arrTotals[$i][$k]		= 9999;} 
		elseif ($i == 0) {$arrTotals[$i][$k]	= -100;}
		else {$arrTotals[$i][$k]	= 0;}
	}
}
#var_dump($arrTotals); exit;
for ($i = 1; $i <= 31; $i++) {
	echo '<tr><td class="table-top">'.$i.'</td>';
	for ($k = 0; $k < $cols; $k++) {
		echo tdGenerate ($arrResults[$i][$k]);
		$value 	= $arrResults[$i][$k];
		if ($value == $noValue || $value ==  $empty) {continue;}		// count all real values in this month column
		$value 	= (float) $value; 
		if ($value > $arrTotals[0][$k]) {$arrTotals[0][$k] = $value;}	// [0] = highest 
		$arrTotals[1][$k]++;											// [1] = number of values
		$arrTotals[2][$k] = $arrTotals[2][$k] + $value;					// [2] = total of daily values 
		if ($value < $arrTotals[3][$k]) {$arrTotals[3][$k] = $value;}	// [3] = lowest 
		if ($value > 0 ) {$arrTotals[4][$k]++;}							// [4] = number of not equal to null values
	} 
	echo '</tr>'.PHP_EOL;
}
echo '<tr class="separator"><td colspan="'.($cols+1).'">&nbsp;</td></tr>'.PHP_EOL;
#
#   high/lows
#
echo '<tr><td  class="table-top">&nbsp;</td>'.$headStr.'</tr>'.PHP_EOL;
$labels	= $kind.'Labels';
#var_dump ($$labels);
$count	= count ($$labels);
for ($n = 0; $n < $count; $n++) {
	$code	= trim(${$labels}[$n]);
	echo '<tr><td class="table-top">'.wsReporttransstr($trans.$code).'</td>';
	for ($k = 0; $k < $cols; $k=$k+$valuesMonth) {
		$value 	= $noValue;
		if ($arrTotals[1][$k] <> 0) {		// only when there are values for this month
			switch ($code) {
				case 'High':
					$value = -100;
					for ($i = 0; $i < $valuesMonth; $i++) {
						if ($value < $arrTotals[0][$k+$i]) {$value = $arrTotals[0][$k+$i];}
					}
				break;
				case 'Avg':
					$value = $arrTotals[2][$k+1]/$arrTotals[1][$k+1];
				break;				
				case 'High Avg':
					$value = $arrTotals[2][$k]/$arrTotals[1][$k];
				break;
				case 'Avg High':
					$value = $arrTotals[0][$k+1];
				break;				
				case 'Low Avg':
					$step	= $valuesMonth - 1;
					$value 	= $arrTotals[2][$k+$step]/$arrTotals[1][$k+$step];
				break;
				case 'Low':
					$value = 10000;
					for ($i = 0; $i < $valuesMonth; $i++) {
						if ($value > $arrTotals[3][$k+$i]) {$value = $arrTotals[3][$k+$i];}
					}
				break;
				case 'Mean':			
					$step	= $valuesMonth - 1;
					$value	= ($arrTotals[2][$k] + $arrTotals[2][$k+$step]) / ($arrTotals[1][$k] + $arrTotals[1][$k+$step]);	
				break;
				case 'Raindays':
					$color	= false;
					$value	= $arrTotals[1][$k];
				break;
				case 'Month total':
					$color	= true;
					$value	= $arrTotals[2][$k];
				break;
				case 'YTD total':
					$color	= false;
					$value	= 0;
					for ($x = 0; $x <= $k; $x++) {
						$value =  $value + $arrTotals[2][$x]; 
					}
				break;
				
								
			}
		}
		$level	= colorLookup ($value);
		if ( ! ($value === $empty || $value === $noValue) ) {
			$value	= round($value, $round);
			$value	= sprintf($numFormat,$value);
		}
		echo '<td class="'.$level.'" colspan="'.$valuesMonth.'">'.$value.'</td>';	
	} 
	echo '</tr>'.PHP_EOL;
}
echo '</table>';
#
#  legenda
#
echo '<table id="legend" class="genericTable" style="width: 100%;">'.PHP_EOL;
echo '<tr class="separator"><td colspan="'.($increments + 1).'">&nbsp;</td></tr>'.PHP_EOL;
$text	= wsReporttransstr($trans.'Color key');
echo '<tr class="table-top"><td colspan="'.($increments + 1).'">'.$text.'</td></tr>'.PHP_EOL;
echo '<tr>';

$width	= 'style="width: '.round(100/($increments+1),3).'%;"';
for ($i = 0; $i <= $increments; $i++) {
	if ($i == 0) {
		$from	= '&lt; ';
		$to		= ${$levelArr}[$i];
	} elseif ($i == $increments) {
		$from	= ${$levelArr}[$i-1];
		$to = ' &gt; ';
	} else {
		$from	= ${$levelArr}[$i-1].' - ';
		$to		= ${$levelArr}[$i];
	}
	echo '<td class="level_'.($i).'" '.$width.'>'.$from.$to.'</td>';
}
echo '</tr></table>'.PHP_EOL;
