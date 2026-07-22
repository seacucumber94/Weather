<?php  $scrpt_vrsn_dt  = 'sky_small.php|01|2022-11-22|';  # removed random error - check missing data | release 2012_lts
#
$ccn_scripts            = array();
$ccn_scripts['ccn_metar_block.php']   = 'ccn_metar_block.php';
$ccn_scripts['ccn_ec_block.php']      = 'ccn_ec_block.php';
$ccn_scripts['ccn_darksky_block.php'] = 'ccn_darksky_block.php';
$ccn_scripts['ccn_aeris_block.php']   = 'ccn_aeris_block.php';
$ccn_scripts['ccn_cltraw_block.php']  = 'ccn_cltraw_block.php';  
#-----------------------------------------------
# $sky_default            = 'cltraw';  # tests
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
#            check which ccn scripts can be used 
#-----------------------------------------------
if ($metarapikey == ''  || $metarapikey == 'ADD YOUR API KEY' )   
     {  unset ( $ccn_scripts['metar']); }
if (!isset ($fct_ec_block_used) ||  $fct_ec_block_used == false)  
     {  unset ( $ccn_scripts['ec']); }
if ($dark_apikey == ''  || $dark_apikey == 'ADD YOUR API KEY')        
     {  unset ( $ccn_scripts['darksky']); }
if ($aeris_access_id == '' || $aeris_access_id == 'ADD YOUR API KEY') 
     {  unset ( $ccn_scripts['aeris']); }    
if ($livedataFormat <> 'wd' && $livedataFormat <> 'meteohub' && $livedataFormat <> 'WSWIN')  
     {  unset ( $ccn_scripts['cltraw']); } 
#
#     check if requested default ccn script is available
if (!array_key_exists ($sky_default,$ccn_scripts) ) 
     {  foreach ( $ccn_scripts as $key => $script_used){ break;}
        } 
else {  $script_used    = $ccn_scripts[$sky_default];}  
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $script_used='.$script_used.' $sky_default='.$sky_default.PHP_EOL; 
#
$ccn_small = true;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$script_used.PHP_EOL;
$return = include_once $script_used; 
#if ($return == false) {return; }
#
echo '<table style="font-size: 11px; width: 98%; padding-top: 0px; margin: 0 auto; text-align: center; max-height: 60px;">
<tbody><tr>
<td><img style="vertical-align: bottom; width : 54px;" rel="prefetch" src="'.$iconXX .'" alt="'.$textXX .'"></td>
<td style="text-align: left;">'.$textXX .'</td>
</tr></tbody>
</table>'.PHP_EOL;
#
unset ($ccn_small);

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
