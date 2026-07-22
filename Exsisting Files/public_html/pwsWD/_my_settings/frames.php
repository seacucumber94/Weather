<?php $scrpt_vrsn_dt  = 'frames.php|01|2023-09-09|'; # external example + release 2012_lts
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ================================================#
#                           Extra pages in the menu
# ================================================#
#
# this one is optionally  used if you installed wu history and reports
$show   = true;    
#$show   = false; // add the # on position 1 if you want these wu scripts
if ($show == true) {
        $frame                  = 'History_at_WU';
        $frm_ttls[$frame]       = 'History at WU ';  // name in menu
        $frm_src[$frame]        = './PWS_wuhistory.php'; # '../wuhistory/startWUhistory.php';
        $frm_hgth[$frame]       =  1000;  
        $frm_type[$frame]       = 'div';
#
        $frame                  = 'Reports_at_WU';
        $frm_ttls[$frame]       = 'Reports at WU ';  // name in menu
        $frm_src[$frame]        = './PWS_wureports.php';
        $frm_hgth[$frame]       =  1000;
        $frm_type[$frame]       = 'div';
        }      
#
$show   = false;
#$show   = true; // remove the # on position 1 if you do not want the nws forecast
#
#     A nice NWS forecast, usablle only in the US,  
if ($wu_csv_unit == "us" && $show == true ) {
$frame                  = 'nwsforecast';
$frm_ttls[$frame]       = 'NWS forecast';  // name in menu
$frm_src[$frame]        = 'https://forecast.weather.gov/MapClick.php?lon='.$lon.'&lat='.$lat.'#.XQCe29MzayF';
$frm_hgth[$frame]       = 1500;   // height in pixels
}
# ================================================#
$show   = true; 
#$show   = false; // remove the # on position 1 if you do not want the aurora page
#
#                           More info about aurora
$above_lat = 52;   // show only above this latitude
#$above_lat = 0;   // remove # on position 1 to show always on northern hemisphere
#
if ((int) $lat < $above_lat ) {$show   = false;} //usable in northern countries
#
if ($show == true) {
$frame                  = 'aurora';
$frm_ttls[$frame]       = 'Aurora info';  // name in menu
$frm_src[$frame]        = 'https://services.swpc.noaa.gov/images/aurora-forecast-northern-hemisphere.jpg?t='.time();
$frm_type[$frame]       = 'img'; 
} 
# ================================================#
$show   = true; 
#$show   = false; // remove the # on position 1 if you do not want the atlantic map
#
#      Two maps from the northern hemisphere oceans
#
#                       Atlantic Ocean pressure map
if ($show == true) {
$frame                  = 'baromap';
$frm_ttls[$frame]       = 'Atlantic Ocean pressure map';  // name in menu
$frm_src[$frame]        = 'https://ocean.weather.gov/A_sfc_full_ocean_color.png';
$frm_type[$frame]       = 'img';       // set this to 'img' if you want to display an image only
}
$show   = true; 
$show   = false; // add the # on position 1 to use the pacific map
#
#
#                         Pacific Ocean pressure map
if ($show == true) {
$frame                  = 'baromap';
$frm_ttls[$frame]       = 'Pacific Ocean pressure map';  // name in menu
$frm_src[$frame]        = 'https://ocean.weather.gov/P_sfc_full_ocean_color.png';
$frm_type[$frame]       = 'img';       // set this to 'img' if you want to display an image only
}
#
# ================================================#
#  ADD BY MTH
# ================================================#
$show   = true;
$show   = false;
#
if ($show == true) {
$frame                  = 'ForecastDiscussion';
$frm_ttls[$frame]       = 'NWS Discussion';  // name in menu
$frm_src[$frame]        = 'https://forecast.weather.gov/product.php?site=LWX&issuedby=LWX&product=AFD&format=txt&version=2&glossary=1&highlight=off';
#$frm_wdth[$frame]       = 800;   // optional set this to the width if resizing is to ugly
$frm_hgth[$frame]       = 1500; 
}      
# ================================================#
$show   = true; 
#$show   = false; // add the # on position 1 if you want to adapt and use it
#
#                      More AirQuality information
#
# You have to adapt the link yourself  by searching on https://air-quality.com/ 
#
if ($show == true) {
$frame                  = 'airqualityPP';
$frm_ttls[$frame]       = 'air-quality.com';  // name in menu
$frm_src[$frame]        = 'https://air-quality.com/place//c8a81c23?lang=en&standard=aqi_us';
$frm_hgth[$frame]       = 1500; 
} 
# ================================================#
$show   = true; 
#$show   = false; // add the # on position 1 if you want to adapt and use it
#
#                               Purpleair map
#
if ($purpleairhardware == true  && $show == true) {
$frame                  = 'airqualityPU';
$frm_ttls[$frame]       = 'PurpleAir Map';  // name in menu
$frm_src[$frame]        = 'https://www.purpleair.com/map?opt=1/mAQI/a10/cC0#7.17/'.round($lat,3).'/'.round($lon,3);
$frm_hgth[$frame]       = 800; 
}
# ================================================#
$show   = true; 
#$show   = false; // add the # on position 1 if you want to adapt and use it
#
#                             Luftdaten  map
#
if ($luftdatenhardware == true  && $show == true) {
$frame                  = 'airqualityLD';
$frm_ttls[$frame]       = 'Luftdaten Map';  // name in menu
$frm_src[$frame]        = 'https://deutschland.maps.sensor.community/#9/'.round($lat,4).'/'.round($lon,4);
$frm_hgth[$frame]       = 800; 
}
# ================================================#
#  E X A M P L E a text file
# ================================================#

$show   = true; 
$show   = false; 
#
if ($show == true) {
$frame                  = 'BWIClimateReport';
$frm_ttls[$frame]       = lang('BWI Climate Report');  // name in menu
$frm_src[$frame]        = './_my_settings/display_text.php';
$frm_src[$frame]        = 'PWS_frame_text.php?showtext=https://tgftp.nws.noaa.gov/data/raw/cd/cdus41.klwx.cli.bwi.txt&type=url';
$frm_hgth[$frame]       = 800; 
}
# ================================================#
#  E X A M P L E new tab external website
# ================================================#

$show   = true; 
#$show   = false; 
#
if ($show == true) {
$key                    = 'CanadianWeather';
$frm_ttls[$key]         = 'Canadian Weather';  // name in menu
$frm_src[$key]          = 'https://weather.gc.ca/canada_e.html';
$frm_frme[$key]         = false;
}

#