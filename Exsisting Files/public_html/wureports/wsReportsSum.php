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
$pageName	= 'wsReportsSum.php';
$pageVersion	= '0.01 2014-06-09';
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
$arrLabelDescriptions = array ();
$arrLabelDescriptions['High']		=wsReporttransstr($trans.'Maximums ');
$arrLabelDescriptions['High Avg']	=wsReporttransstr($trans.'Average  of the maximums');
$arrLabelDescriptions['Mean']		=wsReporttransstr($trans.'Mean');
$arrLabelDescriptions['Low Avg']	=wsReporttransstr($trans.'Average Low');
$arrLabelDescriptions['Low']		=wsReporttransstr($trans.'Low');
$arrLabelDescriptions['Raindays']	=wsReporttransstr($trans.'Raindays');
$arrLabelDescriptions['Month total']    =wsReporttransstr($trans.'Month total');
$arrLabelDescriptions['YTD total']	=wsReporttransstr($trans.'Year-to-day total');
$arrLabelDescriptions['Avg High']	=wsReporttransstr($trans.'Highest of the averages');
$arrLabelDescriptions['Avg']		=wsReporttransstr($trans.'Average');
#
#echo '<pre>'.PHP_EOL;
# 
$eof 		= false;
$i		= 0;
$end		= count ($yearArray[0]);
$arrayTotalsAll = array();
$arrResults	= array();
# make selection of data based on one year (jan-dec) / 4 seasons  dec-nov or jun-mai
$arrarrGrandTotal= array();
for ($n = 0; $n < 7; $n++) 	// initialize grand-total array with appopriate values 
     {  for ($k = 0; $k < $cols+2; $k++) 
             {  if ($n == 3) {$arrGrandTotal[$n][$k]	= 9999;} // 3	= lowest
		elseif ($n == 0) {$arrGrandTotal[$n][$k]= -100;} // 0	= highest
		else {$arrGrandTotal[$n][$k]	= 0;}}}
# for all records
while (!$eof) 
     {  $arrTotals	= array();	//  initialize totals 
	for ($n = 0; $n < 7; $n++) 
	     {  for ($k = 0; $k < $cols+2; $k++) 
	             {  if ($n == 3) {$arrTotals[$n][$k]	= 9999;} 
			elseif ($n == 0) {$arrTotals[$n][$k]	= -100;}
			else {$arrTotals[$n][$k]	= 0;}}}
# echo '<pre>'.print_r($arrTotals,true); exit;
	# read one record and detect period for record selection
	$date		= $yearArray[0][$i];
	$year		= substr($date,0,4);
	$mmddRecord	= substr($date,4,4);	// $mmddFrom	= '0600'; $mmddUntil   = '0532';
	if (!$seasonal) 
	     {  $validFrom = $year.$mmddFrom; 		
	        $validUntil = $year.$mmddUntil;}
	elseif ($mmddRecord > $mmddFrom) 	
	     {  $validFrom = $year.$mmddFrom; 		
	        $validUntil = ($year+1).$mmddUntil;}
	else {  $validFrom = ($year-1).$mmddFrom;	
	        $validUntil = $year.$mmddUntil;}
#	echo PHP_EOL.'date = '.$date.PHP_EOL.'period = '.$validFrom.' - '.$validUntil.PHP_EOL;  exit;
	#
	# while there are still records (not EOF not end of period))
	while (!$eof) 
	     {  $month	= (int) substr($date,4,2);
		$day	= (int) substr($date,6,2);
		# put one row of data in single row array
		for ($n = 0; $n < $cols; $n++) 		// init row
		     {  $arrResults[$n] =  $empty;}
		for ($k = 0; $k < count($request); $k++) // $request is number of fields to process: high / avrage / low = 3
		     {  $fieldNr= $request[$k];		// $fieldNr points to field in one row of csv 
			$value	= $yearArray[$k+1][$i];	// $i points to row in the converted csv file
#			$value	= convertUom ($value);
		# compute col nr based on month (season does not start at month 1
			$result	= $monthsArr[$month]; 		// contains the starting colom if there is only one value
			$col	= (int) ($k +  ($valuesMonth * ($result) ) );
			$arrResults[$col]	= $value;}	// different than table script		
#
		for ($k = 0; $k < $cols; $k++) 		// $cols is 12 (one value) / month  24  2 values / month / ?? future 3 values /month
		     {  $value 	= $arrResults[$k];
			if ($value === $empty)    {continue;}	// count only real days
			if ($value === $noValue ) {continue;}
			$value 	= (float) $value;
			if ($value > (float) $arrTotals[0][$k]) 		
			     {  $arrTotals[0][$k]       = $value;}	// [0] = highest   This year/period
			if ($value > (float) $arrGrandTotal[0][$k])	
			     {  $arrGrandTotal[0][$k]   = $value;}	// [0] = highest  All years
			$arrTotals[1][$k]++;													// [1] = number of values
			$arrGrandTotal[1][$k]++;
			$arrTotals[2][$k] 	= $arrTotals[2][$k] + $value;	// [2] = total of daily values 
			$arrGrandTotal[2][$k]   = $arrGrandTotal[2][$k] + $value;	

			if ($value < (float) $arrTotals[3][$k]) 	
			     {  $arrTotals[3][$k] = $value;}	// [3] = lowest 
			if ($value < (float) $arrGrandTotal[3][$k]) 	
			     {  $arrGrandTotal[3][$k] = $value;}	

			if ($value > 0 ) {$arrTotals[4][$k]++;}									// [4] = number of not equal to null values
			if ($value > 0 ) {$arrGrandTotal[4][$k]++;}
		} // eo for $k = 0; $k < $cols; $k++
# read one record and raise EOF if there is EOF
		$i++;					// next record
		if ($i >= $end) {$eof = true; break;}	// eof 
		$date	= $yearArray[0][$i];
		if ($date > $validUntil) { break;}      // date outside allowed range break
# echo '<pre>'.print_r($arrTotals,true); exit;
	} 	//  eo still  in selection
# echo '<pre>'.print_r($arrTotals,true); exit;

# add period and save totals for further processing
	$arrayTotalsAll[] = array('periodFrom'	=>  $validFrom,
				'periodUntil'	=>  $validUntil,
				'totals'	=>  $arrTotals,);
#
	if ($eof) {break;}
}  // while all records
# 
#echo '<pre>'.__LINE__.' arrayTotalsAll '.print_r ($arrayTotalsAll,true).PHP_EOL.'arrGrandTotal'.print_r ($arrGrandTotal,true); 
# generate output
$endTotals	= count ($arrayTotalsAll);
$labels	        = $kind.'Labels';
#var_dump ($$labels);
$count	        = count ($$labels);
for ($n = 0; $n < $count; $n++) 
     {  $code	= trim(${$labels}[$n]);
	$arrayTABLE[$code]='';}
#
for ($totals = 0; $totals < $endTotals+1; $totals++) 
     {  if ($totals <> $endTotals ) 
             {  $arrTotals	= $arrayTotalsAll[$totals]['totals'];
		$period		= substr($arrayTotalsAll[$totals]['periodFrom'],0,4);
		$until		= substr($arrayTotalsAll[$totals]['periodUntil'],0,4);
		if ($period <> $until) {$period .= '-'.$until;}}
	else {  $period		= wsReporttransstr($trans.'All years');
		$arrTotals	= $arrGrandTotal;}
	#   high/lows
	#
	$stringTABLE	=  '';
	for ($n = 0; $n < $count; $n++) 
	     {  $stringTR	= '';
		$code	= trim(${$labels}[$n]);
		$stringTR .= '<tr><td class="table-top">'.$period.'</td>';
		$arrTotals[0][$cols]  = $noValue;
		$arrTotals[0][$cols+1]  = 0;
		for ($k = 0; $k < $cols; $k=$k+$valuesMonth) 
		     {  $value 	= $noValue;
			if ($arrTotals[1][$k] <> 0) 		// only when there are values for this month
			     {	switch ($code) 
                               { case 'High':
                                        $value = -100;
                                        for ($i = 0; $i < $valuesMonth; $i++) {
                                                if ($value < $arrTotals[0][$k+$i]) {$value = $arrTotals[0][$k+$i];}
                                                if ($arrTotals[0][$cols] == $noValue ||$arrTotals[0][$cols] < $value ) {
                                                        $arrTotals[0][$cols] 	= $value;
                                                        $arrTotals[0][$cols+1]	= 1;  // highest is one value only
                                                }
                                        }
                                break;
                                case 'Avg':
                                        $value = $arrTotals[2][$k+1]/$arrTotals[1][$k+1];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;
                                        $arrTotals[0][$cols+1]++;
                                break;				
                                case 'High Avg':
                                        $value = $arrTotals[2][$k]/$arrTotals[1][$k];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value; #####
                                        $arrTotals[0][$cols+1]++;
                                break;
                                case 'Avg High':
                                        $value = $arrTotals[0][$k+1];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;
                                        $arrTotals[0][$cols+1]++;
                                break;				
                                case 'Low Avg':
                                        $step	= $valuesMonth - 1;
                                        $value 	= $arrTotals[2][$k+$step]/$arrTotals[1][$k+$step];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;  #####
                                        $arrTotals[0][$cols+1]++;
                                break;
                                case 'Low':
                                        $value = 10000;
                                        for ($i = 0; $i < $valuesMonth; $i++) {
                                                if ($value > $arrTotals[3][$k+$i]) {$value = $arrTotals[3][$k+$i];}
                                                if ($arrTotals[0][$cols] == $noValue ||$arrTotals[0][$cols] > $value ) {
                                                        $arrTotals[0][$cols] 	= $value;
                                                        $arrTotals[0][$cols+1]	= 0;  // highest is one value only
                                                }
                                        }
                                break;
                                case 'Mean':			
                                        $step	= $valuesMonth - 1;
                                        $value	= ($arrTotals[2][$k] + $arrTotals[2][$k+$step]) / ($arrTotals[1][$k] + $arrTotals[1][$k+$step]);	
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;  #####
                                        $arrTotals[0][$cols+1]++;
                                break;
                                case 'Raindays':
                                        $color	= false;
                                        $value	= $arrTotals[1][$k];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;
                                        $arrTotals[0][$cols+1]=1;
                                break;
                                case 'Month total':
                                        $color	= true;
                                        $value	= $arrTotals[2][$k];
                                        $arrTotals[0][$cols] = (float) $arrTotals[0][$cols] + $value;
                                        $arrTotals[0][$cols+1]=1;
                                break;
                                case 'YTD total':
                                        $color	= false;
                                        $value	= 0;
                                        for ($x = 0; $x <= $k; $x++) {
                                                $value =  $value + (float) $arrTotals[2][$x]; 
                                        }
                                        if ($arrTotals[0][$cols] 	== $noValue || $arrTotals[0][$cols] < $value ) {
                                                $arrTotals[0][$cols] 	= $value;
                                                $arrTotals[0][$cols+1]	= 0;  // highest is one value only
                                        }
                                break;
                        
								
				} // eo switch
			} // eo if $arrTotals[1][$k] <> 0
			$level	= colorLookup ($value);
			if ( ! ($value === $empty || $value === $noValue) ) 
			     {  $value	= round($value, $round);
				$value	= sprintf($numFormat,$value);}
			if ($arrTotals[6][$k] <> 0) 
			     {  $value = $value.'*'; 
				print_r ($arrTotals[6]); exit;}
			$stringTR .= '<td class="'.$level.'">'.$value.'</td>';	
		} // eofor $k = 0; $k < $cols; $k=$k+$valuesMonth
		$value	=  $arrTotals[0][$cols];
		if ($arrTotals[0][$cols+1] <> 0) {$value = $value / $arrTotals[0][$cols+1]; }
		$level	= colorLookup ($value);
                if ( ! ($value === $empty || $value === $noValue) ) 
                     {  $value	= round($value, $round);
                        $value	= sprintf($numFormat,$value);}
		$stringTR .= '<td class="'.$level.'">'.$value.'</td>';	 
		$stringTR .= '</tr>'.PHP_EOL;
		if ($totals == $endTotals ) 
		     {$arrayTABLE[$code] .= '<tr  class="separator" style="height: 4px;"><td colspan="14"></td></tr>'.PHP_EOL.$stringTR;} 
		else {  $arrayTABLE[$code] = $stringTR .$arrayTABLE[$code];}
	} // eo for $n = 0; $n < $count; $n++
} // eofor $totals = 0; $totals < $endTotals+1; $totals++
if ($seasonal) {$rowspan = 2;} else {$rowspan = 1;}
$colWidthMonth = $colWidthLevel = 'style="width: '.'7%"';
$colWidthLevel	= '';
$colspan = 1;

echo  '<table class="genericTable">';
foreach ($arrayTABLE  as $key => $string) 
     {  echo '<tr><th class="table-top" colspan="14" >'.
	wsReporttransstr($trans.$kindArr[$kind]['desc']).': '.
	$arrLabelDescriptions[$key].
	'</th></tr>'.PHP_EOL;
        echo '<tr>';
        echo '<th rowspan="'.$rowspan.'" class="table-top">'.wsReporttransstr($trans.'Period').'</th>';
        if ($seasonal) 
             {  $colspan = 3;
                echo '<th colspan = "'.$colspan.'" class="table-top">'.
                wsReporttransstr($trans.'winter').'</th><th colspan = "'.$colspan.'" class="table-top">'.
                wsReporttransstr($trans.'spring').'</th><th colspan = "'.$colspan.'" class="table-top">'.
                wsReporttransstr($trans.'summer').'</th><th colspan = "'.$colspan.'" class="table-top">'.
                wsReporttransstr($trans.'autumn').'</th><th rowspan="'.$rowspan.'" colspan = "'.$colspan.'" class="table-top">'.
                wsReporttransstr($trans.'Year').'</th></tr>'.PHP_EOL; }
        $levels		= '<tr>';
        $headStr	= '';
        $colspan 	= 1;
        for ($i = 1; $i <= $months; $i++) 
             {  $head	= wsReporttransstr($trans.$monthNamesShort[$i]);
                $headStr.= '<th colspan="'.$colspan.'" class="table-top" '.$colWidthMonth.'>'.$head.'</th>';
                if ($valuesMonth > 1) 
                     {  for ($k = 0; $k < $valuesMonth; $k++) 
                             {  $num		= $request[$k];
                                $fieldname	= $fields[$num];
                                $from = array ('C','F','MPH','KMH','In','CM','hPa');
                                $fieldname	= str_replace($from,'',$fieldname);
                                $text 		= wsReporttransstr($trans.$fieldLookup[$fieldname]['level']);
                                $levels	.= '<th class="table-top" '.$colWidthLevel.'>'.$text.'</th>';} // eofor $k = 0; $k < $valuesMonth; $k++
                } // eo if $valuesMonth > 1
        } // eofor $i = 1; $i <= $months; $i++
        if (!$seasonal) 
             {  $headStr.= '<th colspan="'.$colspan.'" class="table-top" '.$colWidthMonth.'>'.wsReporttransstr($trans.'Year').'</th>';}
        echo $headStr.'</tr>'.PHP_EOL;	
	echo $string;
	echo '<tr><td class="separator" colspan="14">&nbsp;</td></tr>'.PHP_EOL;
} // eo foreach ($arrayTABLE  as $key => $string)
echo '</table>';
#
echo '<table id="legend" class="genericTable" style="width: 100%;">'.PHP_EOL;
$text	= wsReporttransstr($trans.'Color key');
echo '<tr class="table-top"><td colspan="'.($increments + 1).'">'.$text.'</td></tr>'.PHP_EOL;
echo '<tr>';
$width	= 'style="width: '.round(100/($increments+1),3).'%;"';
for ($i = 0; $i <= $increments; $i++)
      { if     ($i == 0) 
             {  $from	= '&lt; ';                  $to = ${$levelArr}[$i];} 
        elseif ($i == $increments) 
             {  $from	= ${$levelArr}[$i-1];       $to = ' &gt; ';} 
	else {  $from	= ${$levelArr}[$i-1].' - '; $to	= ${$levelArr}[$i];}
	echo '<td class="level_'.($i).'" '.$width.'>'.$from.$to.'</td>';
} // eo for ($i = 0; $i <= $increments; $i++)
echo '</table>'.PHP_EOL;
