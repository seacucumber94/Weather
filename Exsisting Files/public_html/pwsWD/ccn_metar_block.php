<?php  $scrpt_vrsn_dt  = 'ccn_metar_block.php|01|2022-11-22|';  # no 1 hr forecast windchill + return true + release 2012_lts
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
#-----------------------------------------------
$alt_scrpt  = 'not available';
#
if      ($fct_ec_block_used == true)              {  $alt_scrpt  = 'ec';}   #### 2020-10-14  // Environment Canada
elseif  ($dark_apikey <> '' 
      && $dark_apikey <> 'ADD YOUR API KEY')     {  $alt_scrpt  = 'darksky';}
elseif  ($aeris_access_id <> '' 
      && $aeris_access_id <> 'ADD YOUR API KEY') {  $alt_scrpt  = 'aeris';}
#
# $alt_scrpt   = 'not available';  # for test
# $metarapikey = 'ADD YOUR API KEY'; # for test
# 
#-----------------------------------------------
# check if metar script is available
#-----------------------------------------------
if  ($metarapikey === ''  || $metarapikey == 'ADD YOUR API KEY')     
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') this script needs metar API-key !'.PHP_EOL; 
        if ($alt_scrpt <> 'not available')
             {  echo '<small style="color:red">Selected script needs metar API-key - '.$alt_scrpt.' data used</small>';
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$alt_scrpt.'_ccn_block.php'.PHP_EOL; 
                include_once 'ccn_'.$alt_scrpt.'_block.php';}
        else {  echo '<small style="color:red">Selected script needs metar API-key - no alt script found</small>';}
        return;}
# -----------------   load metar current conditions script 
$scrpt          = 'metar_load_funct.php'; # deciphers METAR
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
$return = include_once $scrpt;
if ($return == false)
     {  return false;}
#-----------------------------------------------
#  assemble condition icon and texts 
#-----------------------------------------------
$timeXX         = $forecastime;  
$textXX         = $sky_desc;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') conditions from provider $textXX='.$sky_desc.PHP_EOL; 
$iconXX         = $sky_icon;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') icon from provider data $sky_icon='.$sky_icon.PHP_EOL;
#
if (isset ($ccn_small) ) { return true;} # 2021-12-04
#-----------------------------------------------
#  Load 1 hour forecast data, if available
#-----------------------------------------------
if ($alt_scrpt <> 'not available')
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$alt_scrpt.'_ccn_block.php'.PHP_EOL; 
        include 'ccn_'.$alt_scrpt.'_block.php';
        if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
        return;}
#-----------------------------------------------
#  get other info when no 1 hour fct is available
#-----------------------------------------------  
$onehrfct_missing       = true;
$hourlySummary          = $textXX;
$hourlyTemp             = $weather['temp'];
$tempC                  = convert_temp ($hourlyTemp,'C',$tempunit,0); 
$hourlychill            =                                               # 2022-06-19
$chillC                 = 'n/a';
if (array_key_exists ('windchill',$weather) && $weather['windchill'] <> 'n/a')
     {  $hourlychill    = $weather['windchill'];
        $chillC         = convert_temp ($hourlychill,'C',$tempunit,0); }# 2022-06-19
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
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
