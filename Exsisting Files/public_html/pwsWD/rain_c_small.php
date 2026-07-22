<?php   $scrpt_vrsn_dt  = 'rain_c_small.php|01|2020-11-02|';  # release 2012_lts
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
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#
# ------------------------- translation of texts
$ltxt_unit      = lang($weather["rain_units"]);
$mnth_l         = lang(date('F'));
#
$yearly = number_format((float) $weather["rain_year"],$dec_rain,'.','');
#
$monthly= number_format((float) $weather["rain_month"],$dec_rain,'.','');
#
$box_style      = 'width: 44%; margin: 4px; padding: 4px;';
#
echo '<div style="'.$box_style.' float: left;">
     <span class="orange" style="font-size: 18px;">'.$yearly.'</span> '.$ltxt_unit
     .'<br />'.date('Y').'
</div>'.PHP_EOL;
echo '<div style="'.$box_style.' float: right;">
     <span class="orange" style="font-size: 18px;">'.$monthly.'</span> '.$ltxt_unit
     .'<br />'.$mnth_l.'
</div>'.PHP_EOL;

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}

