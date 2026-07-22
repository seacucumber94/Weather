<?php $scrpt_vrsn_dt  = 'PWS_graph_xx.php|01|2021-05-15|'; # test + loading image + translations + upd /js + remove scroll + lang do-it + jquery + mod CURL | release 2012_lts
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;  
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header('Content-type: text/plain; charset=UTF-8');
   header('Accept-Ranges: bytes');
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
#
$form_test      = '';                                           #### 2021-05-10
if ( array_key_exists('test',$_REQUEST) && $_REQUEST['test'] <> 'test')
     {  $form_test = '<input type="hidden" name="test" value ="'.$_REQUEST['test'].'"/>';}
$form_action    = '#';
if ( array_key_exists('PHP_SELF',$_SERVER) )
     {  $form_action    = $_SERVER['PHP_SELF'];}                #### 2021-05-10
#
header('Content-type: text/html; charset=UTF-8');
#
$stck_lst       = basename(__FILE__).'('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# load settings when run stand-alone
$scrpt          = './PWS_livedata.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$charts_select  = true;
$charts_menu    = false;         // add menu with print and save as top right
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
$color          = "#FF7C39"; // head line
$ltxt_clsppp    = lang ('Close');
$ltxt_excppp    = lang ('Do it!');  #### 2020-02-11
$ltxt_print     = lang ('Print');
$ltxt_save      = lang ('Save as');
$ltxt_png       = $ltxt_save.' '.lang ('PNG');
$ltxt_jpg       = $ltxt_save.' '.lang ('JPEG');
$ltxt_reset     = lang ('Reset');
$ltxt_pan       = lang ('Pan');
$ltxt_zoom      = lang ('Zoom');
$ltxt_more      = lang('More options');
#
# 	built on CanvasJs 
#       canvasJs.js is protected by CREATIVE COMMONS LICENCE BY-NC 3.0 
# 	free for non commercial use and credit must be left in tact .
#
$grph_prd='day';
if (isset ($_REQUEST['period']) )
    {   $request        = trim($_REQUEST['period']);
        $allowed_period = array ('day','month','year');
        if ($charts_from == 'WU' && $charts_select == true) 
             {  $allowed_period = array ('day','month','year','day1','month1','year1');}
        if (in_array ($request,$allowed_period) ) {  $grph_prd= $request; } 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.')  period set to '. $grph_prd .' from request '.$request.PHP_EOL;}
#
$grph_val='baro';
if (isset ($_REQUEST['type']) )
    {   $request        = trim($_REQUEST['type']);
        $allowed_type   = array ('temp','baro','rain','wind');
        if (in_array ($request,$allowed_type) ) {  $grph_val= $request; } 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') type set to '. $grph_val .' from request '.$request.PHP_EOL;}
#
$s_grph_prd     = $grph_prd;
function fnct_load_wu()
     {  global $grph_prd, $s_date, $this_server, $wuID, $arr_l, $d,$y,$m;
        $now    = time();
        $allowed= array ('d','m','y');
        foreach ($allowed as $check)
             {  if (array_key_exists ($check, $_REQUEST)) 
                     { $$check = (int) trim($_REQUEST[$check]);}
                } # echo $day1.'-'.$month1.'-'.$year1; exit;
                 #?ID=IVLAAMSG47&day=20&month=06&year=2020&format=1&graphspan=month
        switch ($grph_prd) {
            case 'year1'  : $m = 1; 
            case 'month1' : $d = 1;
            default: 
                $grph_prd = substr($grph_prd,0,-1); 
                $string = '&day='.$d.'&month='.$m.'&year='.$y.'&format=1&graphspan='.$grph_prd;
                $s_date = mktime(12,12,12,$m,$d,$y);
        } // eo switch period       
        $url    = $this_server.'PWS_DailyHistory.php?ID='.$wuID.$string; # echo     $url; exit;   
        $ch             = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0'); #### 2020-11-28
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,20);        // data timeout 30 seconds
        $result = curl_exec ($ch);
        $info	= curl_getinfo($ch);
        $error  = curl_error($ch);
        curl_close ($ch);
        if ($error <> '') {
                echo  basename(__FILE__).' ('.__LINE__.') Error '.$error.' when trying to load WU data'.PHP_EOL;
                $arr_l  = array();}
        else {  $arr_l  = explode("\n", $result);}   
        } // eof fnct_load_wu
#
$m      = date ('m',$now);
$y      = date ('Y',$now);
$d      = date ('d',$now);
#       
if ($charts_from == 'WU') 
     {  if (!isset ($chartdata) ) {$chartdata   = 'chartswudata';}
        if (!isset ($wuID) )      {$wuID        = $id;}
        if (trim($wuID) == '') 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') No valid WU station name found '.PHP_EOL; 
                echo 'No valid WU station name found'; return;}
        
        $nm_prt1        = './'.$chartdata.'/'.$wuID;  //   chartswudata/IVLAAMSG47
        $txt    = '';
        if      ($grph_prd == 'month') {$txt = 'YM.txt';}
        elseif  ($grph_prd == 'year')  {$txt = 'Y.txt';}
        elseif  ($grph_prd == 'day')   {$txt = 'YMD.txt';} // this period data for WU	
        if ($txt  <> '')
             {  $weatherfile    = $nm_prt1.$txt;
                if (!file_exists ($weatherfile) )
                     {  echo '<p style="color: red;">No valid data found for '.$weatherfile.'</p>'; 
                        return false;}
                $arr_l          = file($weatherfile);}
        else {  fnct_load_wu();
                $weatherfile = ' from WU';}  #echo $arr_l; exit;
        } // if wu
elseif ($grph_prd == 'day')
     {  if (!isset ($chartdata) ) {$chartdata   = 'chartsmydata';}
        $weatherfile    = './'.$chartdata.'/'.'today.txt';
        $arr_l          = file($weatherfile);}
else {  if (!isset ($chartdata) ) {$chartdata   = 'chartsmydata';}
        $year   = date('Y');
        $last   = $chartdata.'/'.$year.'.txt';
        $prev   = $chartdata.'/'.($year - 1).'.txt';
        if      ($grph_prd == 'month') {$needed = 31;} else {$needed = 361;}
        $arr_l  = array();
        $arr_p  = array();
        if (file_exists ($last) ) 
             {  $arr_l  = file ($last);}
        $cnt_l  = count ($arr_l);
        if ($needed > $cnt_l &&  file_exists ($prev) )
             {  $arr_p  = file ($prev);
                array_shift ( $arr_l);
                $arr_l = array_merge($arr_p,$arr_l);}
        $cnt_max        = count($arr_l);
        $strt           = 0;            # echo'<pre>'.' $last='.$last.' $prev='.$prev.print_r($arr_p,true).PHP_EOL;exit;
        $strt           = $cnt_max - $needed;
        if ($strt > 0)
             {  $arr_l  = array_slice ($arr_l,$strt,$needed);}
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.')  $needed='.$needed. ' $cnt_max='.$cnt_max.' $strt='.$strt.' count='.count($arr_l).PHP_EOL;  #print_r($arr_l);exit;               
} // eo databse 
 $stck_lst    .= basename(__FILE__).' ('.__LINE__.') loading  =>'.$weatherfile .PHP_EOL; 
# copy file to javascript arrau
$data_str= '<script>
var allLinesArray = [];'.PHP_EOL;  // contains all datapoint values
#$data   = file ($weatherfile);
$n      = -1;
$fld_nms= '' ;# print_r($data); exit; 
foreach ($arr_l as $string) 
     {  $string = str_replace('<br>','',$string);
        $string = trim($string);
        if ($string <> '')
             {  if ($fld_nms == '' ) 
                     {$fld_nms = $string;}
                else {  $n++;
                        $data_str .=' allLinesArray['.$n.'] = "'.$string.'";'.PHP_EOL;} }
        } // eo for each
$data_str .='</script>'.PHP_EOL;
#      
# calculate conversion factors
if ($charts_from == 'WU') 
      { $names  = explode (',',$fld_nms);
        $unitsWU= ''; #echo '<pre>'.print_r($names,true); exit;
        if      (in_array ('TemperatureHighC',$names) )  {$unitsWU = 'metric';}
        elseif  (in_array ('TemperatureHighF',$names) )  {$unitsWU = 'imperial';}
        elseif  (in_array ('TemperatureC',$names) )      {$unitsWU = 'metric';}
        elseif  (in_array ('TemperatureF',$names) )      {$unitsWU = 'imperial';}
        if ($unitsWU == '')
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.')Line-names contains '.$fld_nms.PHP_EOL; 
                echo '<h3> No valid data file found, script ends</h3><br />DEBUG<br /><pre>'.$stck_lst; return;}
        if ($unitsWU == 'metric')
             {  $temp_fl = 'C';
                $wind_fl = 'km/h';
                if ($grph_prd == 'day') {$rain_fl = 'mm';} else {$rain_fl = 'cm';}
                $baro_fl = 'hPa';}
        else {  $temp_fl = 'F';
                $wind_fl = 'mph';
                $rain_fl = 'in';
                $baro_fl = 'inHg';} 
        } // get units from file for WU
else {  $temp_fl = $temp_his;
        $wind_fl = $wind_his;
        $rain_fl = $rain_his;
        $baro_fl = $baro_his;}

if (strtolower($tempunit)  ==  strtolower($temp_fl) ) 
     {  $temperatureconv = 1;} 
elseif (strtolower($temp_fl) == 'c') 
     {  $temperatureconv = -1; }
else {  $temperatureconv = 0; }
#
$repl		= array ('/',' ','hg','mb');
$with		= array ('' ,'' ,'','hpa');
$convertArr	= array
    (   "hpa"	=> array('hpa' => 1    ,   'mm' => 0.75006 	, 'in' => 0.02953),
        "mm"	=> array('hpa' => 1.3332 , 'mm' => 1 	        , 'in' => 0.03937 ),
        "in"	=> array('hpa' => 33.864 , 'mm' => 25.4 	, 'in' => 1));
$fromUnit 	= trim(str_replace ($repl,$with,strtolower($baro_fl)));
$toUnit   	= trim(str_replace ($repl,$with,strtolower($pressureunit)));
if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
     {  $pressureconv = 1;} 
else {  $pressureconv = $convertArr[$fromUnit][$toUnit];}
#
$repl = array ('/',' ','p');
$with = array ('','','');
$convertArr= array
   (    "kmh"   => array('kmh' => 1	, 'kts' => 0.5399568034557235	, 'ms' => 0.2777777777777778 	, 'mh' => 0.621371192237334 ),
        "kts"   => array('kmh' => 1.852	, 'kts' => 1 			, 'ms' => 0.5144444444444445 	, 'mh' => 1.1507794480235425),
        "ms"    => array('kmh' => 3.6	, 'kts' => 1.9438444924406046	, 'ms' => 1 			, 'mh' => 2.236936292054402 ),
        "mh"    => array('kmh' => 1.609344	, 'kts' => 0.8689762419006479	, 'ms' => 0.44704 		, 'mh' => 1 ));
$fromUnit 	= trim(str_replace ($repl,$with,strtolower($wind_fl)));
$toUnit   	= trim(str_replace ($repl,$with,strtolower($windunit)));
if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
     {  $windconv = 1;} 
else {  $windconv = $convertArr[$fromUnit][$toUnit];}
#
$repl 		= array ('l/m','/',' ','ch');
$with 		= array ('mm' ,'' ,'' ,'');
$convertArr	= array
     (  "mm"    => array('mm' => 1	,'in' => 0.03937007874015748 	, 'cm' => 0.1 ),
        "in"    => array('mm' => 25.4	,'in' => 1			, 'cm' => 2.54),
        "cm"    => array('mm' => 10	,'in' => 0.3937007874015748 	, 'cm' => 1 )   );
$fromUnit 	= trim(str_replace ($repl,$with,strtolower($rain_fl)));
$toUnit   	= trim(str_replace ($repl,$with,strtolower($rainunit)));
if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
     {  $rainfallconv = 1;} 
else {  $rainfallconv = $convertArr[$fromUnit][$toUnit];} 
#
if ($toUnit == 'in') {$dcmls = 2;} else {$dcmls = 1;}

if ($charts_from == 'WU')
     {  $grphs        = array();  ## 2019-01-27
        $grphs['rain|day']    = '|'.       '|0|'.lang('Rainfall')   .'|'.lang('Rate').     '|1|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|12|9|LT|12|||';
        $grphs['rain|month']  = '|YYYY-MM-DD|0|'.lang('Rainfall')   .'|'.                  '|0|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|15|-1|MMM Do|2|c||';
        $grphs['rain|year']   = '|YYYY-MM-DD|0|'.lang('Rainfall')   .'|'.                  '|0|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|15|-1|MMM Do|30|c||';
        $grphs['temp|day']    = '|'.       '|0|'.lang('Temperature').'|'.lang('Dewpoint'). '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|2|LT|20|||';
        $grphs['temp|month']  = '|YYYY-MM-DD|0|'.lang('High').       '|'.lang('Low').      '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|3|MMM Do|2|||';
        $grphs['temp|year']   = '|YYYY-MM-DD|0|'.lang('High').       '|'.lang('Low').      '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|3|MMM Do|30|||';        
        $grphs['baro|day']    = '|'.       '|0|'.lang('Barometer')  .'|'.                  '|0|'.lang('Pressure').   '|'.$pressureconv.   '|'.$pressureunit. '|3|-1|LT|12|||';
        $grphs['baro|month']  = '|YYYY-MM-DD|0|'.lang('High')       .'|'.lang('Low').      '|1|'.lang('Pressure').   '|'.$pressureconv.   '|'.$pressureunit.'|10|11|MMM Do|2|||';
        $grphs['baro|year']   = '|YYYY-MM-DD|0|'.lang('High')       .'|'.lang('Low').      '|1|'.lang('Pressure').   '|'.$pressureconv   .'|'.$pressureunit.'|10|11|MMM Do|30|||';     
        $grphs['wind|day']    = '|'.       '|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.     '|6|7|LT|12|||';
        $grphs['wind|month']  = '|YYYY-MM-DD|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.    '|12|14|ddd Do|2|||';
        $grphs['wind|year']   = '|YYYY-MM-DD|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.    '|12|14|MMM Do|30|||';     
        $key = trim($grph_val).'|'.trim($grph_prd);
        list ($null,$grph_x_dt, $grph_x, $lng_high, $lng_low, $shw_lgnd2, $txt_val, $value_conv ,$dataunit, $graph_fld_1, $graph_fld_2, $grph_x_frmt, $grph_x_int,$grph_type,$grph_wdth) = explode('|',$grphs[$key]);
        if      ($grph_type == '')  {$grph_type = 'line';}  #### {$grph_type = 'spline';}
        elseif  ($grph_type == 'c') {$grph_type = 'column';}
        if      ($grph_wdth == '')  {$grph_wdth = 0;}
        $stored_at = lang ('stored at WU').' ';} // eo wu
else {  $grphs['rain|day']    = '|hh:mm|0|'.lang('Rainfall')   .'|'.lang('Rate').     '|1|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|3|9|LT|12|||';
        $grphs['rain|month']  = '|'.  '|0|'.lang('Rainfall')   .'|'.                  '|0|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|5|-1|MMM Do|2|c||';
        $grphs['rain|year']   = '|'.  '|0|'.lang('Rainfall')   .'|'.                  '|0|'.lang('Rainfall')   .'|'.$rainfallconv.   '|'.$rainunit.     '|5|-1|MMM Do|30|c||';
        $grphs['temp|day']    = '|hh:mm|0|'.lang('Temperature').'|'.lang('Dewpoint'). '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|8|LT|20|||';
        $grphs['temp|month']  = '|'.  '|0|'.lang('High').       '|'.lang('Low').      '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|2|MMM Do|2|||';
        $grphs['temp|year']   = '|'.  '|0|'.lang('High').       '|'.lang('Low').      '|1|'.lang('Temperature').'|'.$temperatureconv.'|'.$tempunit.     '|1|2|MMM Do|30|||';        
        $grphs['baro|day']    = '|hh:mm|0|'.lang('Barometer')  .'|'.                  '|0|'.lang('Pressure').   '|'.$pressureconv.   '|'.$pressureunit. '|2|-1|LT|12|||';
        $grphs['baro|month']  = '|'.  '|0|'.lang('High')       .'|'.lang('Low').      '|1|'.lang('Pressure').   '|'.$pressureconv.   '|'.$pressureunit.'|9|10|MMM Do|2|||';
        $grphs['baro|year']   = '|'.  '|0|'.lang('High')       .'|'.lang('Low').      '|1|'.lang('Pressure').   '|'.$pressureconv   .'|'.$pressureunit.'|9|10|MMM Do|30|||';     
        $grphs['wind|day']    = '|hh:mm|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.     '|6|5|LT|12|||';
        $grphs['wind|month']  = '|'.  '|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.    '|7|6|MMM Do|2|||';
        $grphs['wind|year']   = '|'.  '|0|'.lang('Wind')       .'|'.lang('Gust').     '|1|'.lang('Wind - Gust').'|'.$windconv.       '|'.$windunit.    '|7|6|MMM Do|30|||';     
        $key = trim($grph_val).'|'.trim($grph_prd);
        list ($null,$grph_x_dt, $grph_x, $lng_high, $lng_low, $shw_lgnd2, $txt_val, $value_conv ,$dataunit, $graph_fld_1, $graph_fld_2, $grph_x_frmt, $grph_x_int,$grph_type,$grph_wdth) = explode('|',$grphs[$key]);
        if      ($grph_type == '')  {$grph_type = 'spline';}
        elseif  ($grph_type == 'c') {$grph_type = 'column';}
        if      ($grph_wdth == '')  {$grph_wdth = 0;}
        $stored_at = '';} // eo our own server
#
#$lng_period     = ' this '.$grph_prd;
if (!isset ($s_date)) {$s_date = time();}
$txt_unit       = '';
if (strtolower($dataunit) == 'f' || strtolower($dataunit) == 'c' ) {$txt_unit = '°';} 
$lng_title      = $txt_val.' ('.$txt_unit.lang($dataunit).') ';
$forthe = lang('the last'); #lang('for the last');
$days   = lang('days');
$day    = lang('today'); #lang('for this day');
$for1   = ''; #lang('for').' ';
$trans_period   = array('day'   => $day, 'month' => $forthe.' 30 '. $days,  'year' => $forthe.' 360 '. $days,
                        'day1'  => $for1.date($dateFormat,$s_date),
                        'month1'=> $for1.lang(date('F',$s_date)).' '.date('Y',$s_date),
                        'year1' => $for1.date('Y',$s_date));
$ltxt_url       = $lng_title.' '.$trans_period[$s_grph_prd];
#
$trans_types    = array('temp'  => lang('Temperature'), 'baro' => lang('Pressure'), 'rain' => lang('Rainfall'), 'wind' => lang('Wind - Gust') );
$trans_periods  = array('day'   => $day,        
                        'month' => $forthe.' 30 '. $days,  
                        'year'  => $forthe.' 360 '. $days);
if ($charts_from == 'WU') {
        $trans_periods['day1']  = $for1. lang('any day') ; 
        $trans_periods['month1']= $for1. lang('any month');
        $trans_periods['year1'] = $for1. lang('any year') ;}
#
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
#
echo '<!DOCTYPE html>
<html lang="'.substr($used_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name=apple-mobile-web-app-title content="Personal Weather Station">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, viewport-fit=cover">
    <script src="js/canvasjs.min.js"></script>
    <script src="js/moment-with-locales.min.js"></script>'
.my_style().'
</head>
<body class="dark" style="overflow: auto; background: #393D40 url(./img/loading.gif)  no-repeat; background-position: 50% 20px;">   
    <div class="PWS_module_title font_head" style="width: 100%;" >'.PHP_EOL;
if ($close_popup <> false ) // optional close text and large X
     { echo '<div style="padding-top: 2px;"><span style="float: left;">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>';}
echo '    <span style="color: '.$color.'; ">'.$ltxt_url.'</span></div>
    </div>
<div class="chartContainer">
<div id="chartContainer" class="chartContainer" style="position: absolute; left: 0; margin: 0px; padding: 0px; background-color: black; text-align: left;">
</div>
</div>'.PHP_EOL;
# generate vars for javascript
echo $data_str;
if ($charts_menu == true) {$charts_menu = 'true';} else {$charts_menu = 'false';}
echo '<script>
var lng_title   = "'.$ltxt_url.'"
var bck_color   = "#000000";
var grd_color   = "RGBA(64, 65, 66, 0.8)";
var dcmls       = '.$dcmls.';
var lng_high    = "'.$lng_high.'"
var lng_low     = "'.$lng_low.'"
var val_convert = "'.$value_conv.'";
var graph_fld_1 = '.$graph_fld_1.';
var graph_fld_2 = '.$graph_fld_2.';
var grph_x      = '.$grph_x.';
var grph_x_dt  = "'.$grph_x_dt.'";
var grph_x_frmt = "'.$grph_x_frmt.'";
var grph_x_int  = '.$grph_x_int.';
var data_unit   = "'.$dataunit.'";
var shw_lgnd2   = '.$shw_lgnd2.';
var grph_type   =  "'.$grph_type.'";
var grph_wdth   = "'.$grph_wdth.'";
var ltxt_print  = "'.$ltxt_print.'";
var ltxt_png    = "'.$ltxt_png.'";
var ltxt_jpg    = "'.$ltxt_jpg.'";
var ltxt_save   = "'.$ltxt_save.'";
var ltxt_reset  = "'.$ltxt_reset.'";
var ltxt_pan    = "'.$ltxt_pan.'";
var ltxt_zoom   = "'.$ltxt_zoom.'";
var ltxt_more   = "'.$ltxt_more.'";
var charts_menu = '.$charts_menu.';
moment.locale("'.$used_lang.'");
used_lang_shrt = "'.substr($used_lang.'en',0,2).'"; 

</script>'.PHP_EOL;
#
?>
<script>
    //barometermonth
var dataPoints1 = [];	
var dataPoints2 = [];
var n = allLinesArray.length-1;
for (var i = 0; i <= n; i++) 
     {  var rowData = allLinesArray[i].split(',');
        if ( rowData.length >1)
             {  xvalue  = moment(rowData[grph_x],grph_x_dt).format(grph_x_frmt);
                if (val_convert > 0) 
                     {  var yvalue1 = parseFloat(rowData[graph_fld_1]* val_convert);
                        if (graph_fld_2 != -1)  
                             {  var yvalue2 = parseFloat(rowData[graph_fld_2]* val_convert);}} 
                else if (val_convert == -1)  // C to F parseFloat((rowData[xxxxx] *1.8) +32
                     {  var yvalue1 = parseFloat((rowData[graph_fld_1]*1.8)  +32);
                        if (graph_fld_2 != -1)  
                             {  var yvalue2 = parseFloat((rowData[graph_fld_2]* 1.8) +32);}} 
                else // F to C parseFloatparseFloat((rowData[xxxx]- 32) / 1.8
                     {  var yvalue1 = parseFloat((rowData[graph_fld_1]- 32)  / 1.8);
                        if (graph_fld_2 != -1)  
                             {  var yvalue2 = parseFloat((rowData[graph_fld_2] -32 )/ 1.8);}} 
                dataPoints1.push(
                     {  label: xvalue ,
                        y:yvalue1  });
                if (graph_fld_2 != -1) 
                      { dataPoints2.push(
                              { label: xvalue,
                                y:yvalue2 });
                        }
                }
        }
CanvasJS.addCultureInfo(used_lang_shrt,
                {   savePNGText: ltxt_png,
                    saveJPGText: ltxt_jpg,
                    printText:   ltxt_print,
                    resetText:   ltxt_reset,
                    menuText:    ltxt_more,
                    zoomText:    ltxt_zoom,
                    panText:     ltxt_pan,
                    
               });
var chart = new CanvasJS.Chart("chartContainer", 
    {  // backgroundColor: bck_color,
        culture: used_lang_shrt,
        theme: "dark2",
        zoomEnabled: true,
        animationEnabled: true,
        exportEnabled: charts_menu,
        exportFileName: lng_title,
        dataPointMaxWidth: 5,
        toolTip:{ 
                fontStyle: "normal",
                backgroundColor: 'white', fontColor:'black',
                toolTipContent: " x: {x} y: {y} <br /> name: {name}, label:{label}",
                fontSize: 12,
                shared: true, 
                borderThickness: 0,
                },
        axisX: {margin: 0,
                gridThickness: 0,
                lineThickness: 0.5,
                labelFontSize: 8,
                labelFontFamily: "arial",	
                },	
        axisY:{ margin: 0,
                gridThickness: 0.2,		
                lineThickness: 0.5,		
                includeZero: false,
                crosshair: {
			enabled: true,
			snapToDataPoint: true, 
		},
                labelFontSize: 8,
                labelFormatter: function ( e ) {
                        return e.value .toFixed(dcmls) ;   },		 
                labelFontFamily: "arial",
                },  
        legend:{fontFamily: "arial",
                fontSize: 10,
                dockInsidePlotArea: true, horizontalAlign: "right" },
        data: [
            {   type: grph_type,
                color:"#ff9350",
                markerSize:3,
                showInLegend:true,
                legendMarkerType: "circle",
                lineThickness: 1,
                markerType: "circle",
                name: lng_high,
                dataPoints: dataPoints1,
                yValueFormatString: "#0.## " + data_unit,},
            {   type: grph_type,
                color:"#00A4B4",
                markerSize:3,
                showInLegend: shw_lgnd2,
                legendMarkerType: "circle",
                lineThickness: 1,
                markerType: "circle",
                name: lng_low,
                dataPoints: dataPoints2,
                yValueFormatString: "#0.## " + data_unit ,}
                ]
        }
        );
chart.render();
</script>
<form id="xyz" method="get" action="<?php echo $form_action; ?>" style="display: block;" class="font_foot">
<table style="margin: 0 auto;">
<?php 

$font_size      = ''; #'font-size: 12px;';
$is_selected    = 'selected="selected"';
#
#weather -value type 
$names          = '<tr><td>'.lang('Select').'</td>';
$selectors      = '<tr><td>';
$selectors     .= '<select name="type" class="font_foot">'.PHP_EOL;;
foreach ($trans_types as $trans=> $text) 
     {  if ($trans == $grph_val) {$extra  = $is_selected;} else { $extra  = '';}
        $selectors .= '<option value="'.$trans.'" '.$extra.'>'.$text.'</option>'.PHP_EOL;}
$selectors .= '</select></td>'.PHP_EOL;
#
# type
$names          .= '<td>'.lang('Graph type').'</td>';
$selectors      .= '<td><select name="period" class="font_foot">';
foreach ($trans_periods as $period=> $text) 
     {  if ($period == $s_grph_prd) {$extra  = $is_selected;} else { $extra  = '';}
        $selectors .=  '<option value="'.$period.'" '.$extra.'>'.$text.'</option>'.PHP_EOL;}
$selectors      .=  '</select></td>'.PHP_EOL;
#
#  wu data has more possibillities
if ($charts_from == 'WU') {
        $to_year        = date ('Y',time());
        $wu_year        = substr($wu_start,0,4);      #$wu_start = "2018-01-01";
        $to_year_month  = date ('Y-m',time());
        $wu_year_month  = substr($wu_start,0,7);      #$wu_start = "2018-01-01";
        $cnt            = 1 + $to_year - $wu_year;

        $names          .= '<td>'.lang('Year').'</td>';
        $selectors      .=  '<td><select name="y" class="font_foot">'.PHP_EOL;
        for ($n = $wu_year; $n <= $to_year; $n++) 
             {  if ($y == $n) {$extra  = $is_selected; } else { $extra  = '';}  
                $selectors      .=  '<option value="'.$n.'" '.$extra.'>'.$n.'</option>'.PHP_EOL;}
        $selectors      .=  '</select></td>'.PHP_EOL;
        
        $from = 1; $to = 12;
        $months = array ('','January','February','March','April','May','June','July','August','September','October','November','December');
        if ($to_year == $wu_year) 
             {  $from   = (int) substr($wu_start,5,2);
                $to     = date ('m',time());}
        if ($from < 1 || $from > 12) {$from = 1;}

        $names          .= '<td>'.lang('Month').'</td>';
        $selectors      .= '<td><select name="m" class="font_foot">'.PHP_EOL;
        for ($n = $from; $n <= $to; $n++) 
             {  if ($m == $n) {$extra  = $is_selected;} else { $extra  = '';}
                 $selectors      .=   '<option value="'.$n.'" '.$extra.'>'.lang($months[$n]).'</option>'.PHP_EOL;}
        $selectors      .=  '</select></td>'.PHP_EOL;

        $from = 1; $to = 31;
        if ($to_year_month == $wu_year_month) 
             {  $from   = (int) substr($wu_start,8,2);
                $to     = date ('d',time());}
        if ($from < 1 || $from > 31) {$from = 1;} 

        $names          .= '<td>'.lang('Month').'</td>';
        $selectors      .= '<td><select name="d" class="font_foot">'.PHP_EOL;

        for ($n = $from; $n <= $to; $n++) 
             {  if ($d == $n) {$extra  = $is_selected;} else { $extra  = '';}
                $selectors      .=  '<option value="'.$n.'" '.$extra.'>'.$n.'</option>'.PHP_EOL;}
        $selectors      .=  '</select></td>'.PHP_EOL;
} // eo extra for WU data
$names          .= '<td><small style="float: right;">('.$charts_from.')&nbsp;</small></td>';
$selectors      .= '<td><input type="submit" value="'.$ltxt_excppp.'" class="font_foot"></td>';  #### 2020-02-11

$names = '';
echo $selectors.PHP_EOL.$names.'
</tr>
</table>
'.$form_test.'
</form>
</body>
</html>';
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
# style is printed in the header 
function my_style()
     {  global $popup_css ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_graphs) && $popup_graphs <> false)  
             {  $return .= file_get_contents ($popup_css);}
else {  $return .= "        body    { margin: 0; padding: 0; text-align: center; font-size: 10px; font-family: arial, sans-serif; vertical-align: middle; min-width: 300px;}
        div     { display: block;}
        strongnumbers{font-weight:600}
        
        .PWS_weather_container  { display: flex; width: 640px; height: 204px; overflow: hidden; margin: 0 auto;}
        .PWS_weather_item       { margin: 2px; width: 312px; height: 202px; border: 1px solid #F0F0F0; box-sizing: border-box; list-style: none; position: relative;}
        .PWS_module_title       { width:100%;  height: 20px; border: none; background-color: #F4F4F4; font-size: 12px; }
        .PWS_module_title .title {padding-top: 3px;}
        .PWS_popup_list         { width:100%;  height: 20px; border: none; background-color: #F4F4F4; font-size: 14px;
                                        position: absolute; bottom: 0;}
        .PWS_ol_time { margin-top: -15px; margin-right: 6px;color: #d65b4a; font: 700 10px arial, sans-serif;line-height: 10px; float: right;}    
        .PWS_left    { float: left;  width: 80px; padding-left:  2px; height: 160px; border: none; }
        .PWS_right   { float: left; width: 80px; padding-right: 2px; height: 160px; border: none; }
        .PWS_middle  { float: left;  width: 140px;      position: relative;               height: 160px; border: none; }
        .PWS_2_heigh { height: 80px; vertical-align: middle;}
        .PWS_3_heigh { height: 53px; vertical-align: middle;}
        .PWS_4_heigh { height: 40px; vertical-align: middle;}

        .PWS_offline { color: #ff8841;}
        .PWS_online  { color: green;}        

        .orange      { color: #ff8841;}
        .green       { color: #9aba2f;}
        .blue        { color: #01a4b4;}
        .red         { color: #d65b4a;}

        .large       { font-size: 26px;}
        .xlarge      { font-size: 46px;}
        .narrow      { width: 100px;}
        .low_item    { height: 165px;}
        .xlow_item   { height: 110px;}

        .dark        { background-color: black; color: #AAA;}
        .dark .PWS_module_title   {background-color: #393D40; }
        .dark .PWS_popup_list     {background-color: #393D40; }
        .dark .PWS_weather_item   {background-color: #24262B; border-color: #24262B; }

        @media screen and (max-width: 639px) {
                body            {height: 250px;}
                .PWS_module_title {height: 14px;}
                .div_height     {height: 232px;}
                .chartContainer{width:100%;  height:216px; }
                .font_foot      {font-size: 7px;} 
                .font_head      {font-size: 10px;} }
        @media screen and (min-width: 640px) {
                body            {height: 350px;}
                .PWS_module_title {height: 18px;}
                .div_height     {height: 332px;}
                .chartContainer{width:100%;  height:310px; }
                .font_foot      {font-size: 9px;}
                .font_head      {font-size: 12px;} }         
        @media screen and (min-width: 850px) {
                body            {height: 550px;}
                .div_height     {height: 530px;}
                .chartContainer{width:850px;  height:498px;}
                .font_foot      {font-size: 13px;}
                .font_head      {font-size: 13px;} }
        @media screen and (max-width: 800px) {
                .PWS_weather_item       {margin: 10px auto 0; float: none;}
                .PWS_weather_container  {display: inline; overflow: hidden;}}
";}
# add pop-up specific css
        $return         .= ' iframe {width: 100%;}'.PHP_EOL;        
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
