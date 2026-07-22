<?php $scrpt_vrsn_dt  = 'lightning_wf_small.php|01|2020-11-07|';  # release 2012_lts
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
if (!isset ($weatherflowoption) || $weatherflowoption == false)
    {   echo 'No Weatherflow device available '; return false; }
#
# ------------------------------ text used
$latest_l       = lang('Latest strike at');
$dist_l         = lang('Distance');
$last3hr_l      = lang('Last 3 hr');
$today_l        = lang('Today');
#
# strikes
$text1          = $last3hr_l; 
$strike1        = $weatherflow['lightning3hr']; 
if ( (int) $strike1 <> 0)
     {  $text1          = $last3hr_l;
        $strike1        = $weatherflow['lightning3hr'];}
else {  $text1          = $today_l;
        $strike1        = $weatherflow['lightning'];}
#
# use correct unit for strike-distance
#

$unit   = trim(strtolower($weatherflow['dist_units']));
if ($distanceunit == $unit) 
     {  $dist   = $weatherflow["lightningdistance"];}
elseif ($distanceunit  == 'km' ) 
     {  $dist   = $weatherflow["lightningdistanceKM"];
        $unit   = 'km';} 
else {  $dist   = $weatherflow["lightningdistanceMI"];
        $unit   = 'mi';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '
        .' lightningdistance='.$weatherflow["lightningdistance"]
        .' witn unit='.$weatherflow['dist_units']
        .' requested unit ='.$distanceunit
        .' calculated distance ='.$dist.PHP_EOL;  
#
if (date ('Ymd' ) <> date ('Ymd',$weatherflow['lastlightningtime']))
     {  $date_text      = '<br />'.date($dateFormat,$weatherflow['lastlightningtime']).' ';}
else {  $date_text      = ' ';}

$box_style      = 'width: 70px; height: 42px; float: left; margin: 4px; padding: 4px; margin-top: 10px; border-right-width: 1px;';
#
echo '<div class= "PWS_div_left" style="'.$box_style.'">
     <b class="orange" style="font-size: 18px;">'.$strike1.'</b>
     <br /><span style="font-size: 10px;">'.$text1.'</span>
</div> 
<div style="font-size:12px; padding-top: 8px;">'
.'<span class="orange">'.$lightningsvg.'</span> '.$latest_l
.$date_text.set_my_time($weatherflow['lastlightningtime'], true).'<br />'
.$dist_l.' '.$dist.' '.$unit
.'</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
