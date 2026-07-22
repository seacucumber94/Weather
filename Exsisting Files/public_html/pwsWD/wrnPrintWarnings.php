<?php   $scrpt_vrsn_dt  = 'wrnPrintWarnings.php|01|2021-01-22|';  # metoffice | release 2012_lts
#
# MENU: Display a list of warnings from other sources
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
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
switch ($weatheralarm)
     {  case 'canada': 
                if (isset ($_REQUEST['url'])) {$url=$_REQUEST['url'];}
                else {  include 'wrnWarningEC.php';
                        if (!isset ($lnk_url_EC) || $lnk_url_EC == '')
                             {echo '<h3>No active warnings  </h3><h3 style="color: black;">No active warnings  </h3>';}  
                        $url = $lnk_url_EC;}   #echo '$url='.$url; exit;
                echo '<iframe src="'.$url.'" style="width: 100%; height: 800px; 2px 2px auto; ">loading EC warnings </iframe>';
        break;
        case 'europe':
                if (!isset ($ownpagehtml) ) {include 'wrnWarningEU.php';}
        case 'uk':
                if (!isset ($ownpagehtml) ) {include 'wrnWarningUK.php';}
                if (!isset ($ownpagehtml) ) {echo '<h3>No active warnings  </h3><h3 style="color: black;">No active warnings  </h3>';}
                echo '<div style="width: 100%; margin: 2px 2px auto; background-color: #ECECEC; color: black;">'.PHP_EOL;
                echo $ownpagehtml;
                echo '</div>'.PHP_EOL;
        break;
        case 'curly':
                echo '<iframe src="./nws-alerts/nws-summary.php" style="width: 100%; margin: 2px 2px auto; height: 700px;">loading NOAA warnings </iframe>';
        break;
        case 'au':
                include './_my_settings/settings.php';
                echo '<iframe src="http://www.bom.gov.au/'.$alarm_area.'/warnings/" style="width: 100%; margin: 2px 2px auto;  height: 900px;">loading BOM.gov.au warnings </iframe>';
        break;        
        default:
                echo '<h3 style="color: white;">No warning-provider set, use Eysyweather to configure  </h3>
<h3 style="color: black;">No warning-provider set, use Eysyweather to configure </h3>';
        }
return;