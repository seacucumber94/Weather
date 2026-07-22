<?php
#-----------------------------------------------
#                         CREDIT - DO NOT REMOVE
# Author        : Wim van der Kuil
# Support       : https://pwsdashboard.com
$pageVersion	= '4.04 2021-05-10';    // pwsWD
$pageName	= 'wsReportsFreeze.php';
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
# ---------------------------------- Houskeeping
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
ws_message ('<!-- loaded script: '.basename(__FILE__).' ('.__LINE__.'): '.$string.' -->');
# ------------------------------------- SETTINGS
$end		= count ($yearArray[0]);
#
if ($latitude > 0)	{
        $mmddFrom	= '0600'; $mmddUntil    = '0532'; 
        $textFrom       = wsReporttransstr($trans.'June'); 
        $textUntil      = wsReporttransstr($trans.'May');}
else {  $mmddFrom	= '1200'; $mmddUntil    = '1132'; 
        $textFrom       = wsReporttransstr($trans.'December'); 
        $textUntil      = wsReporttransstr($trans.'November');} 
# for all records
if ($uomTemp == '&deg;C') {$below_freeze   = 0; } else {$below_freeze   = 32;}

$seasons_found  = 0;
$total_freeze   = array();
function init_season ($nr) 
     {  global $total_freeze;
        $total_freeze[$nr]['start_season']     = false;  
        $total_freeze[$nr]['end_season']       = false;
        $total_freeze[$nr]['start']     = false;        
        $total_freeze[$nr]['end']       = false;
        $total_freeze[$nr]['first']     = false;
        $total_freeze[$nr]['first_temp']= false;  
        $total_freeze[$nr]['last']      = false;
        $total_freeze[$nr]['last_temp'] = false; 
        $total_freeze[$nr]['freezesL']  = 0;
        $total_freeze[$nr]['freezesH']  = 0;
        $total_freeze[$nr]['freezesP']  = 0;}
init_season ($seasons_found) ;
$first_date                     = $yearArray[0][0];
$mmddRecord	                = substr($first_date,4,4);
$year                           = substr($first_date,0,4);
if ($mmddRecord < $mmddFrom) {  $year   = $year -1;}
$season_start                   = $year.$mmddFrom;
$season_end                     = ($year+1).$mmddUntil;
$nr                             = $seasons_found;
$total_freeze[$nr]['start_season']     = $season_start;  
$total_freeze[$nr]['end_season']       = $season_end;
$total_freeze[$nr]['start']     = $first_date;
$cont_days                      = 0;
for ($i = 0; $i < $end; $i++) {
#       $yearArray[0][$i] = date
#       $yearArray[1][$i] = temp high
#       $yearArray[2][$i] = temp low
        $date		= $yearArray[0][$i];
        $year           = substr($date,0,4);
        $mmddRecord     = substr($date,4,4);
        if ($date > $season_end) {
                 $seasons_found++;
                init_season ($seasons_found) ;
                $nr     = $seasons_found;                 
                $total_freeze[$nr]['start']     =  $date;
                $year                           = substr($date,0,4);
                $season_start                   = $year.$mmddFrom;
                $season_end                     = ($year+1).$mmddUntil;
                $total_freeze[$nr]['start_season']     = $season_start;  
                $total_freeze[$nr]['end_season']       = $season_end;
        }
        $total_freeze[$nr]['end']       =  $date;
        $low_temp       = $yearArray[2][$i];
        if ($low_temp <= $below_freeze) {
                $total_freeze[$nr]['freezesL']++;
                $cont_days++;
                if ($cont_days > $total_freeze[$nr]['freezesP']) {$total_freeze[$nr]['freezesP'] = $cont_days;}
                if (!$total_freeze[$nr]['first']) {
                        $total_freeze[$nr]['first'] = $date;
                        $total_freeze[$nr]['first_temp']= $low_temp;                              
                }
                $total_freeze[$nr]['last']      = $date;
                $total_freeze[$nr]['last_temp'] = $low_temp; 
        } else {$cont_days      = 0;}
        $high_temp      = $yearArray[1][$i];
        if ($high_temp <= $below_freeze) {
                $total_freeze[$nr]['freezesH'] ++;
        }        
}
#echo '<pre>';   print_r ($total_freeze); 
$earliest_first_freeze_nr       = 400;
$earliest_first_freeze_date     = '';
$earliest_last_freeze_nr        = 400;
$earliest_last_freeze_date      = '';
$latest_first_freeze_nr         = 0;
$latest_first_freeze_date       = '';
$latest_last_freeze_nr          = 0;
$latest_last_freeze_date        = '';

$average_first_freeze   = 0;
$average_first_count    = 0;
$average_last_freeze    = 0;
$average_last_count     = 0;
#
$only_full_seasons      = false;
$full_seasons           = 0;
for ($nr = 0; $nr < count($total_freeze) ; $nr++) {
        $mmdd   = substr($total_freeze[$nr]['start'],4,4);
        $start  = $mmdd-1;
        $end    = substr($total_freeze[$nr]['end'],4,4) + 1;
        $use_earliest   = $use_last     = true;
        if ($only_full_seasons && $start <> $mmddFrom) {
                $use_earliest   = false;
                echo '<!-- season skipped - start = '.$start.' -->'.PHP_EOL;}
        if ($only_full_seasons && $end <> $mmddUntil) {
                $use_last       = false;
                echo '<!-- season skipped - end = '.$end.' -->'.PHP_EOL;}        
#print_r ($total_freeze[$nr]); 
        $first                  = $total_freeze[$nr]['first'];
        $int_first              = strtotime($first.'T000000');
        $int_start              = strtotime($total_freeze[$nr]['start_season']);  
        $nr_days                = $int_first - $int_start;
#echo '$nr_days int = '.$nr_days;        
        $nr_days                = $nr_days  / (24*60*60);
#echo ' $nr_days = '.$nr_days.PHP_EOL;
        if ($use_earliest == true) {
                $average_first_freeze   = $average_first_freeze + $nr_days;
                $average_first_count++;
                if ($nr_days < $earliest_first_freeze_nr) {
                        $earliest_first_freeze_nr       = $nr_days;
                        $earliest_first_freeze_date     = $first;
                }
                if ($nr_days > $latest_first_freeze_nr) {
                        $latest_first_freeze_nr         = $nr_days;
                        $latest_first_freeze_date       = $first;
                }
        }
        $last                   = $total_freeze[$nr]['last'];
        $int_last               = strtotime($last.'T000000');
        $nr_days                = $int_last - $int_start;
#echo '$nr_days int = '.$nr_days;        
        $nr_days                = $nr_days  / (24*60*60);
#echo ' $nr_days = '.$nr_days.PHP_EOL;
        if ($use_last == true) { 
                $average_last_freeze    = $average_last_freeze + $nr_days;
                $average_last_count++;
                if ($nr_days < $earliest_last_freeze_nr) {
                        $earliest_last_freeze_nr        = $nr_days;
                        $earliest_last_freeze_date      = $last;
                }
                if ($nr_days > $latest_last_freeze_nr) {
                        $latest_last_freeze_nr          = $nr_days;
                        $latest_last_freeze_date        = $last;
                }
        }
}
$start_time     = strtotime('1970'.$mmddFrom.'T000000');
if ($average_first_count <> 0) {
#echo '$average_first_freeze = '.$average_first_freeze.PHP_EOL;
        $average                = 24*60*60*round($average_first_freeze / $average_first_count, 0);
        $average_first_freeze   = $start_time + $average;
        $average_first_freeze   = date('Ymd',$average_first_freeze);}
if ($average_last_count <> 0) {
#echo '$average_last_freeze = '.$average_last_freeze.PHP_EOL;
        $average                = 24*60*60*round($average_last_freeze / $average_last_count, 0);
        $average_last_freeze    = $start_time + $average;
        $average_last_freeze    = date('Ymd',$average_last_freeze);}

/*
echo '<pre>';   print_r ($total_freeze); 
echo '
$earliest_first_freeze  = '.$earliest_first_freeze_date.'
$earliest_last_freeze   = '.$earliest_last_freeze_date.'
$latest_first_freeze    = '.$latest_first_freeze_date.'
$latest_last_freeze     = '.$latest_last_freeze_date.'
$average_first_freeze   = '.$average_first_freeze.'
$average_last_freeze    = '.$average_last_freeze;
*/
$style  = 'class="table-top"';


echo  '<table class="genericTable">
<tr><th '.$style.'>&nbsp;</th><th '.$style.'>'.
wsReporttransstr($trans.'First freeze').' (<= '.$below_freeze.$uomTemp.')</th><th '.$style.'>'.
wsReporttransstr($trans.'Last freeze').' (<= '.$below_freeze.$uomTemp.')</th></tr>
<tr><td '.$style.'>'.
wsReporttransstr($trans.'Average').'</td><td class="level_nocolor">'.freezes_longdate ($average_first_freeze,false).'</td><td class="level_nocolor">'.freezes_longdate ($average_last_freeze,false).'</td></tr>
<tr><td '.$style.'>'.
wsReporttransstr($trans.'Earliest').'</td><td class="level_nocolor">'.freezes_longdate ($earliest_first_freeze_date).'</td><td class="level_nocolor">'.freezes_longdate ($latest_first_freeze_date).'</td></tr>
<tr><td '.$style.'>'.
wsReporttransstr($trans.'Latest').'</td><td class="level_nocolor">'.freezes_longdate ($earliest_last_freeze_date).'</td><td class="level_nocolor">'.freezes_longdate ($latest_last_freeze_date).'</td></tr>
</table><br />'.PHP_EOL;

echo  '<table class="genericTable">
<tr><th '.$style.'>'.
wsReporttransstr($trans.'Period').'<br />'.$textFrom.' - '.$textUntil.'</th><th '.$style.'>'.
wsReporttransstr($trans.'First freeze').'<br />(<= '.$below_freeze.$uomTemp.')</th><th '.$style.'>'.
wsReporttransstr($trans.'Last freeze').'<br />(<= '.$below_freeze.$uomTemp.')</th><th '.$style.'>'.
wsReporttransstr($trans.'Days with low').'<br />(<= '.$below_freeze.$uomTemp.')</th><th '.$style.'>'.
wsReporttransstr($trans.'Days with high').'<br />(<= '.$below_freeze.$uomTemp.')</th><th '.$style.'>'.
wsReporttransstr($trans.'Consecutive days with').'<br />(<= '.$below_freeze.$uomTemp.')</th></tr>'.PHP_EOL;
$end = count($total_freeze) - 1;
for ($i = $end; $i >= 0; $i=$i - 1){
        echo '<tr> 
        <td '.$style.'>'.substr($total_freeze[$i]['start_season'],0,4).'-'.substr($total_freeze[$i]['end_season'],0,4).'</td>
        <td class="level_nocolor">'.freezes_longdate ( $total_freeze[$i]['first']).' ('.$total_freeze[$i]['first_temp'].$uomTemp.')</td>
        <td class="level_nocolor">'.freezes_longdate ( $total_freeze[$i]['last']).' ('.$total_freeze[$i]['last_temp'].$uomTemp.')</td>
        <td class="level_nocolor">'. $total_freeze[$i]['freezesL'].'</td>
        <td class="level_nocolor">'. $total_freeze[$i]['freezesH'].'</td>
        <td class="level_nocolor">'. $total_freeze[$i]['freezesP'].'</td>
        </tr>'.PHP_EOL;
} // 
echo '</table>'.PHP_EOL;
return;

function freezes_longdate ($time,$year=true) {
	global  $trans, $dateLongFormat;
	$int_time       = strtotime( $time.'T000000');       
	$freezeLong     = $dateLongFormat;  # 'l d F Y';  Vrijdag, 5 februari 2013 US   'l M j Y'; 
	$longDays	= array ("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	$longMonths	= array ("January","February","March","April","May","June","July","August","September","October","November","December");
#
	$longDate       = date ($freezeLong,$int_time);
	if ($year == false) {$longDate = str_replace (date('Y',$int_time), '', $longDate);}
	$from	        = array();
	$to		= array();
	for ($i = 0; $i < count($longDays); $i++) {
		if (freezesFound($longDate,$longDays[$i])) {
			$from[] = $longDays[$i];
			if ($year) {$to[] = wsReporttransstr($trans.$longDays[$i]);} else {$to[] 	= '';}
			break;
		}
	}
	for ($i = 0; $i < count($longMonths); $i++) {
		if (freezesFound($longDate,$longMonths[$i])) {
			$from[] = $longMonths[$i];
			$to[] 	= wsReporttransstr($trans.$longMonths[$i]);
			break;
		}
	}
	$longDate       = str_replace ($from, $to, $longDate);
	return $longDate;
}
function freezesFound($haystack, $needle){
$pos    = strpos($haystack, $needle);
   if ($pos === false) {
        return false;
   } else {
        return true;
   }
}
