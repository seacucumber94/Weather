<?php $scrpt_vrsn_dt  = 'lightning_station_small.php|01|2021-07-30|';  # correct time + check date |release 2012_lts
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
# --------------------------  testdata
#$weather['lightningtime']       = time() + 48*24*3600;
#$weather['lightningtimeago']    = 600;
#$weather['lightningmi']         = 100;         
#$weather['lightningkm']         = round ($weather['lightningmi'] / 0.621371);
#$weather['lightning']           = 10;
# --------------------------------------
#
if (  !array_key_exists('lightning',$weather) 
    || (string) $weather['lightning' ]=== 'n/a')
     {  echo '<small style="color: red">No lightning data found for this station (yet)</small>';  return false; }
#
# ------------------------------ text used
$latest_l       = lang('Latest strike at');
$dist_l         = lang('Distance');
$today_l        = lang('Today');
#
$strike1        = $weather['lightning'];
$date_text      = date($dateFormat,$weather['lightningtime']);
$time_text      = set_my_time($weather['lightningtime'],true);  ##### 2021-06-24
$ymd            = date ('Ymd');
$ymd_lightning  = date ('Ymd',$weather['lightningtime']);
#
if ($ymd_lightning <> $ymd)     // no todays values to display
     {  $strike1        = '';} 
#
if ( $ymd_lightning == $ymd )
     {  $date_text      = $today_l;}  
#
if ( $weather['lightningtime']  > time()  ) // eror in time value from station
     {  $time_text      = ''; 
        $date_text      = '';} 
#
if ($date_text.$time_text <> '')
     {  $date_text      = '<br />'.$date_text;}  
#
# use correct unit for strike-distance
#
$unit   = 'mi';
if ($distanceunit  == 'km' ) 
     {  $dist   = $weather['lightningkm'] ;
        $unit   = 'km';} 
else {  $dist   = $weather['lightningmi'];
        $unit   = 'mi';}
#
if ($strike1 <> '')  // a box with todays value
     {  $box_style      = 'width: 70px; height: 42px; float: left; margin: 4px; padding: 4px; margin-top: 10px; border-right-width: 1px;';
        echo '<div class= "PWS_div_left" style="'.$box_style.'">
     <b class="orange" style="font-size: 18px;">'.$strike1.'</b>
     <br /><span style="font-size: 10px;">'.$today_l.'</span>
</div>'.PHP_EOL;}
#
echo '<!-- '.$ymd.' '.$ymd_lightning.' -->'.PHP_EOL;
echo '<div style="font-size:12px; padding-top: 8px;">'
.'<span class="orange">'.$lightningsvg.'</span> '.$latest_l
.$date_text.' '.$time_text
.'<br />'.$dist_l.' '.$dist.' '.$unit
.'</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
