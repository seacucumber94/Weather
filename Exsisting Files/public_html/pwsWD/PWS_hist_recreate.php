<?php  $scrpt_vrsn_dt  = 'PWS_hist_recreate.php|01|2021-07-30|';  # typo | release 2012_lts
# 
#  Generate a history file from your  WU data
#
# This script will 
# 1. Load all yearly WU .arr data as created by the PWS_DailyHistory.php
# 2. loads all high-lows to the history table
#
#-----------------------------------------------
# SETTINGS
#-----------------------------------------------
$wu_data_dir    = './wudata/';
$hist_dir       = './_my_settings/';
$cron           = true;         // force default units,
#
header('Content-type: text/plain; charset=UTF-8');
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
   header('Accept-Ranges: bytes');
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
#
echo basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;      
#
#----------------------  load units and settings 
$scrpt          = 'PWS_livedata.php'; 
echo basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#------------------  check if user is legitimate
#
if (!isset($_REQUEST['pw']) || $_REQUEST['pw'] <> $password)   { die ('Unknown security error');}
#
#-----------------------------  check .arr files
#
$my_wu_ID       = trim($wuID); 

if ($wu_csv_unit == 'us') 
     {  $my_units       = 'english'; 
        $csv_uoms       = 'f,inhg,in,mph';}
else {  $my_units       = 'metric';
        $csv_uoms       = 'c,hpa,cm,kmh';}

$my_api         = trim($wu_apikey);

list ($my_first_year, $my_first_month, $my_first_day) = explode ('-',$wu_start.'-');
$now            = time();
$thisYear       = date ('Y', $now);
$thisMonth      = date ('m', $now);
$thisDay        = date ('d', $now);
if ( (int) $my_first_year > $thisYear || (int) $my_first_year < 2008 ) 
     {  $my_first_year  = '2019';}
if ( (int) $my_first_month > 12 || (int) $my_first_month < 1)
     {  $my_first_month = '01';}
if ( (int) $my_first_day > 31 || (int) $my_first_day < 1)
     {  $my_first_day = '01';}
#
$filename_tmpl  = $wu_data_dir.$my_wu_ID.'-'.$my_units.'-#YEAR#.arr';   #echo  $filename_tmpl; exit; #   IVLBRABA2-metric-2018.arr
#
for ($n = $my_first_year; $n <= $thisYear; $n++)
     {  echo  basename(__FILE__).' ('.__LINE__.') Checking if file for year '.$n.' is present. ';
        $file_name      = str_replace ('#YEAR#',$n,$filename_tmpl);
        if (!file_exists ($file_name))   # recreate_curl ($url, $wuapi='')
             {  echo ' File '.$filename.' does not exist'.PHP_EOL;
                echo basename(__FILE__).' ('.__LINE__.') loading: '.$file_name.PHP_EOL;
                $url    = $this_server.'PWS_DailyHistory.php?ID='.$my_wu_ID.'&graphspan=year&year='.$n; 
                recreate_curl ($url, $my_api);} 
        else {  echo ' File '.$file_name.' is present'.PHP_EOL; }    ####  2021-07-29
           
} // eo for every year
#
#-----------------------------------------------
#                   generate empty history table
#-----------------------------------------------
$hist_file      = __DIR__.'/'.'_my_settings/history.txt'; 
if     (file_exists ($hist_file) ) {
        $hist           = unserialize (file_get_contents($hist_file)); 
        echo basename(__FILE__).' ('.__LINE__.') using existing "'.$hist_file.'" file'.PHP_EOL;} 
elseif (file_exists (__DIR__.'/'.'chartsmydata/history_empty.txt') ) {  // first time use
        $hist           = unserialize (file_get_contents(__DIR__.'/'.'chartsmydata/history_empty.txt')); 
        echo basename(__FILE__).' ('.__LINE__.') using history_empty.txt'.PHP_EOL;}  
else {  echo basename(__FILE__).' ('.__LINE__.') no historyfile found, generating one'.PHP_EOL;
        $hist           = array();
        $wthr_types     = array ('temp','dewp','rain','humd','baro','wind','gust');
        $types_values   = array ('HghV','HghV_D','HghV_D_T','LowV','LowV_D','LowV_D_T');
        $values_period  = array ('today','yday','month','year','all');
        foreach ($wthr_types as $type) {
                foreach ($types_values as $value) {
                        foreach ($values_period as $period) {
                                $hist[$type][$value][$period]   = 'n/a';
                        } // eo period
                } // eo values
        } // eo types   
} // eo no file yet
#
echo basename(__FILE__).' ('.__LINE__.') Loading all files to find high/lows'.PHP_EOL;
#
for ($i = $my_first_year; $i <= $thisYear; $i++)
     {  echo  basename(__FILE__).' ('.__LINE__.') Checking  file for year '.$i.PHP_EOL;
        $file_name      = str_replace ('#YEAR#',$i,$filename_tmpl);
        if (!file_exists ($file_name))   
             {  echo  basename(__FILE__).' ('.__LINE__.') File '.$file_name.' missing, no high lows for that year'.PHP_EOL;
                continue;}
        $arr    = unserialize ( file_get_contents($file_name));
        foreach ($arr as $key => $string)
             {  $arr_csv[]      = $string; }
        list ($csvTmp, $csvBaro, $csvRain, $csvWind) = explode (',',$csv_uoms);
        $decimals = $dec_in  = 1;
        if ($baro_his == 'in') {$dec_in  = 2; }  
        foreach ($arr_csv as $line) {  
                $arr    = explode (',',$line.',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,');
 # -- Date    => date    _D
                $string         = $arr[0];        
                list ($csvY,$csvM,$csvD)        = explode ('-',$string.'---');
                $time           = mktime (23,55,55,(int)$csvM, (int)$csvD,(int)$csvY);  
                if ((int)$csvY <> $i) {continue; } // old or next year records
/*  [0] => Date
    [1] => TemperatureHighC
    [2] => TemperatureAvgC
    [3] => TemperatureLowC
    [4] => DewpointHighC
    [5] => DewpointAvgC
    [6] => DewpointLowC
    [7] => HumidityHigh
    [8] => HumidityAvg
    [9] => HumidityLow
    [10] => PressureMaxhPa
    [11] => PressureMinhPa
    [12] => WindSpeedMaxKMH
    [13] => WindSpeedAvgKMH
    [14] => GustSpeedMaxKMH
    [15] => PrecipitationSumCM  */      
        recreate_hilo ('temp', $arr[1]);
        recreate_hilo ('temp', $arr[3]);
        if ((int)$arr[4] > -10)  {recreate_hilo ('dewp', $arr[4]);}
        if ((int)$arr[6] > -10)  {recreate_hilo ('dewp', $arr[6]);}
        recreate_hilo ('humd', $arr[7]);
        recreate_hilo ('humd', $arr[9]);
        recreate_hilo ('baro', $arr[10]);
        recreate_hilo ('baro', $arr[11]);
        recreate_hilo ('wind', $arr[12]);
        recreate_hilo ('gust', $arr[14]);
        recreate_hilo ('rain', $arr[15]);
#
        } // for each line
}   #print_r($hist['temp']); #print_r($hist['dewp']);  exit;
#
echo basename(__FILE__).' ('.__LINE__.') Updated history file "'.$hist_file.'" will be saved.'.PHP_EOL;
$result = file_put_contents($hist_file, serialize($hist));  # print_r($hist['temp']); #print_r($hist['dewp']);
if ($result === false)
     {  echo basename(__FILE__).' ('.__LINE__.') Unexpected error when trying to save updated file'.PHP_EOL; }   
else {  echo basename(__FILE__).' ('.__LINE__.') File: "'.$hist_file.'" saved.'.PHP_EOL;}
#
#-----------------------------------------------
#                     update empty history table
#-----------------------------------------------
function recreate_hilo ($type , $value     ) {
        global  $thisYear, $i, $thisMonth, $time, $arr, $hist,
                $csvTmp, $csvBaro, $csvRain, $csvWind,
                $temp_his, $baro_his, $rain_his, $wind_his;
#
# ---- convert value from WU.csv unit to basis station data unit
# ---- not to the units wanted but to the units of the data file
# ---- F.i.  in clientraw the wind is in knots, so the history is also in knots
#
        switch ($type) {
            case 'temp':
            case 'dewp':
                $clc_val= convert_temp   ($value, $csvTmp,  $temp_his); break;
            case 'rain':
                $clc_val= convert_precip ($value, $csvRain, $rain_his); break;         
            case 'baro':
                $clc_val= convert_baro   ($value, $csvBaro, $baro_his); break;                        
            case 'wind':
            case 'gust':
                $clc_val= convert_speed  ($value, $csvWind, $wind_his); break;                        
            default :
                $clc_val= (float)  $value;    
        } // eo switch
        $csv_mnth       = date ('Ym',$time);
#
        $loop   = array ('all','year','month');
        foreach ($loop as $period) {
                if ( $type == 'rain')
                     {  $hist['rain']['HghV']['all']    = $value + (float) $hist['rain']['HghV']['all'];
                        if ( $period == 'year'  && $i == $thisYear ) 
                             {  $hist['rain']['HghV']['year']   = $value + (float) $hist['rain']['HghV']['year'];}
                        elseif ( $period == 'year'  && $csv_mnth  <> $thisYear.$thisMonth ) 
                             {  $hist['rain']['HghV']['year']   = $value + (float) $hist['rain']['HghV']['year'];}   
                        continue; }    
                if ( $period == 'year'  && $i         <> $thisYear )  {continue;}
                if ( $period == 'month' && $csv_mnth  <> $thisYear.$thisMonth)  {continue;}
                $check  =  $hist[$type]['HghV'][$period];        
                if ( (string)  $check  === 'n/a' )
                     {  $check  = -2000; } else {  $check  =  (float) $check;}
                if ( $clc_val > $check ) {
                                $hist[$type]['HghV']    [$period]  = $clc_val;
                                $hist[$type]['HghV_D']  [$period]  = $time;
                                $hist[$type]['HghV_D_T'][$period]  = $arr[0];}
                $check  =  $hist[$type]['LowV'][$period];        
                if ( (string)  $check === 'n/a' )
                     {  $check  =  2000; } else {  $check  =  (float) $check;}
                if ( $clc_val < $check ) {
                                $hist[$type]['LowV']    [$period]  = $clc_val;
                                $hist[$type]['LowV_D']  [$period]  = $time;
                                $hist[$type]['LowV_D_T'][$period]  = $arr[0];}
        } 
} // eo function
#
function recreate_curl ($url, $wuapi='') {
        global $header, $result, $info, $error ;
        if ($wuapi <> '') 
             {  $fake   = str_replace ($wuapi, '_yourAPI_',$url); } 
        else {$fake   = $url; }
        echo  basename(__FILE__).' ('.__LINE__.') Trying to load '.$fake.PHP_EOL;
#
        $ch             = curl_init(); 
        if (isset ($header) && is_array($header) )
             {  curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                unset ($header); }
        curl_setopt($ch, CURLOPT_URL,$url);
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
                echo  basename(__FILE__).' ('.__LINE__.') Error '.$error.' when trying to load missing data'.PHP_EOL;}
        return $result;
}
#if (isset ($_REQUEST['test'])) echo '<br /><br />'.$stck_lst;