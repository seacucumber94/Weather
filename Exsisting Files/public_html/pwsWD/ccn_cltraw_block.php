<?php $scrpt_vrsn_dt  = 'ccn_cltraw_block.php|01|2020-11-21|';  # release 2012_lts
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#-----------------------------------------------
# check which forecast is used to get the "this hour" data 
# OR 
# can be usaed as alternative if no clientraw available
#
#-----------------------------------------------
$alt_scrpt  = 'not available';
#
if      ($fct_ec_block_used == true)             {  $alt_scrpt  = 'ec';}     // Environment Canada   #### 2020-10-14
elseif  ($dark_apikey <> '' 
      && $dark_apikey <> 'ADD YOUR API KEY')     {  $alt_scrpt  = 'darksky';}
elseif  ($aeris_access_id <> '' 
      && $aeris_access_id <> 'ADD YOUR API KEY') {  $alt_scrpt  = 'aeris';}
elseif  ($metarapikey <> ''  
      && $metarapikey <> 'ADD YOUR API KEY')     {  $alt_scrpt  = 'metar';}
#$alt_scrpt  = 'not available';  # for test
# 
#-----------------------------------------------
# check which livadata format is used, 
# this script only works with clientraw
#-----------------------------------------------
if ($livedataFormat <> 'wd' && $livedataFormat <> 'meteohub'  && $livedataFormat <> 'wswin')
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') this script only works with clientraw files!'.PHP_EOL; 
        if ($alt_scrpt <> 'not available')
             {  echo '<small style="color:red">Selected script for clientraw only - '.$alt_scrpt.' data used</small>';
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$alt_scrpt.'_ccn_block.php'.PHP_EOL; 
                include_once 'ccn_'.$alt_scrpt.'_block.php';}
        else {  echo '<small style="color:red">Selected script for clientraw only - no alt script found</small>';}
        return;}
#-----------------------------------------------
#  assemble current conditions icon and text  
#-----------------------------------------------
$timeXX         = $weather["datetime"];  
$arr            = explode ('.',$weather["currentdescription"].'.'); 
$string         = '';
foreach ($arr as $str) {if (trim($str) <> '') {$string .= lang (trim($str)).'. '; }}
$textXX         = $string;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') conditions from provider $textXX='.$textXX.PHP_EOL; 
$key    = (int) $weather['currentweathericon'];
# translation array for WD-nr to WD-icon
$WD_icn_tr      = array ('clear_day','clear_night','ovc','pc_day','mc','clear_day','ovc_fog','haze_day','mc_rain','few_day',
'ovc_fog','ovc_fog','ovc_rain_dark','mc_night','ovc_rain_dark','mc_rain_dark','ovc_flurries_dark','ovc_thun_dark','ovc','pc_day',
'ovc_rain','ovc_rain','mc_rain','ovc_sleet','ovc_sleet','mc_flurries','mc_flurries','mc_flurries','clear_day','ovc_thun_dark',
'ovc_thun_dark','ovc_thun_dark','tornado','ovc_windy','pc_day','ovc_rain');
if (array_key_exists($key,$WD_icn_tr) )
     {  $icon   = $WD_icn_tr[$key];} else { $icon = 'unknown';}
$iconXX         = 'pws_icons/'.$icon.'.svg';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') icon from provider data  currentweathericon='.$key.' $iconXX='.$iconXX.PHP_EOL;
#
if (isset ($ccn_small) ) { return;}        
#-----------------------------------------------
#  Load 1 hour forecast data, if available
#-----------------------------------------------
if ($alt_scrpt <> 'not available'  &&  $alt_scrpt  <> 'metar') 
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$alt_scrpt.'_ccn_block.php'.PHP_EOL; 
        include_once 'ccn_'.$alt_scrpt.'_block.php';      
        return; }  // the alt script will do the printing also
#
#-----------------------------------------------
#  get other info when no 1 hour fct is available
#-----------------------------------------------  
$onehrfct_missing       = true;
$hourlySummary          = $textXX;
$hourlyTemp             = $weather['temp'];
$tempC                  = convert_temp ($hourlyTemp,'C',$tempunit,0); 
$hourlychill            = $weather['windchill'];
$chillC                 = convert_temp ($hourlychill,'C',$tempunit,0); 
$hourlyhudx             = 'n/a';   
$hudxC                  = 'n/a';     
$hourlyWinddir          = $weather['wind_direction_avg'];
$hourlyWindSpeed        = $weather['wind_speed'];
$hourlyWindGust         = $weather['wind_gust_speed_max'];
$hourlyuv               = $weather['uv'];
if ($weatherflowoption == true && (float) $hourlyuv == 0) 
     {  $hourlyuv       = round($weatherflow['uv']);}
#-----------------------------------------------
#  print all information
#-----------------------------------------------  
$script                 = 'ccn_shared.php'; # echo $stck_lst; exit;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$script.'_ccn_block.php'.PHP_EOL; 
include_once $script;
#
