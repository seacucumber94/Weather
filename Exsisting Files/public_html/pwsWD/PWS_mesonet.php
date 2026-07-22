<?php  $scrpt_vrsn_dt  = 'PWS_mesonet.php|01|2021-12-04|';  # \n windows + force load of recent net data | release 2012_lts
#
# to generate a mesonet file for memebers without a weather-program
#
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
   header('Content-type: text/plain; charset=UTF-8');
   header('Cache-Control: no-cache, must-revalidate');
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/plain; charset=UTF-8');
#-----------------------------------------------
#                       load latest station data
$read_net_data  = true;  #### 2021-03-22 to force load of latests network data if no cron is used
#
$scrpt          = 'PWS_livedata.php'; 
if (!file_exists ($scrpt) ) {$scrpt = 'w34_livedata.php';}
#
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;     #   echo '<pre>'.print_r($weather,true); exit;
#
#-----------------------------------------------
#        check age of raw data for pws-dashboard
$link           =  str_replace ("\n",'|',$weather['loaded_from']);
list ($link)    =  explode ('|',$link);
#
if (file_exists ($link) ) 
     {  $last_modified_time   = filemtime ($link);}
else {  $last_modified_time   = time();}
#
#-----------------------------------------------
#
header('Cache-Control: must-revalidate');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $last_modified_time));
#
$ms_string      = '';
$ms_string      .= date('H:i:s',$weather['datetime']) . ','; 
$ms_string      .= date('d/m/Y',$weather['datetime']) . ','; 
$ms_string      .= $weather['temp'] . ',';       
$ms_string      .= $weather['heat_index'] . ','; 
$ms_string      .= $weather['windchill'] . ','; 
$ms_string      .= $weather['humidity'] . ',';  
$ms_string      .= $weather['dewpoint'] . ',';  
$ms_string      .= $weather['barometer'] . ','; 
$ms_string      .= $weather['barometer_trend'] . ','; 
$ms_string      .= $weather['wind_speed'] . ','; 
$ms_string      .= windlabel($weather['wind_direction'],true). ','; 
$ms_string      .= $weather['rain_today'] . ',';
$ms_string      .= ',';                                 #current conditions,
$ms_string      .= date('H:i',$sunrs2) . ',';      
$ms_string      .= date('H:i',$suns2) . ',';     
$ms_string      .= $weather['wind_speed'] . ',';     
$ms_string      .= $weather['wind_gust_speed'] . ',';     
$ms_string      .= $weather["temp_units"].'|'
                  .$weather["wind_units"].'|'
                  .$weather["barometer_units"].'|'
                  .$weather["rain_units"];       
echo $ms_string;
