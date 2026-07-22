<?php  $scrpt_vrsn_dt  = 'PWS_listdata.php|01|2020-12-17|';  # debug info | release 2012_lts
#
# Used in debug-console 
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
   header('Cache-Control: no-cache, must-revalidate');
   header('Content-type: text/plain; charset=UTF-8');
   header('Accept-Ranges: bytes');
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
#
ini_set('display_errors', 'On'); error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
header('Content-type: text/html; charset=UTF-8');
#-----------------------------------------------------------------------
#  used to display the contents of a the weather-data from live data.
#-----------------------------------------------------------------------
$stck_lst ='';
$script = 'PWS_livedata.php';
include ($script);
ksort ($weather);
echo '<pre>Contents of $weather-'.print_r($weather,true);
#
if (isset ($weatherflow) && is_array($weatherflow) ) 
     {  echo PHP_EOL.'Contents of $weatherflow-'.print_r($weatherflow,true);}

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}