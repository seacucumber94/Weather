<?php   $scrpt_vrsn_dt  = 'webcam2_c_block.php|01|2020-12-02|';  # release 2012_lts
#
#-------- set the link to your webcam image here
#
$webcam_img     = './img/camplus.jpg';  // demo value, treplace with your link
$add_timestamp  = true;                 // The imagelink will get a unique timestamp
$click_large    = 'image';              // click in the box will popup a large image
#
                // remove the # on the first position of the next line  IF
                // you have modified that movie popup script
#$click_large    = './_my_settings/webfilm_popup.php'; 
                // You can also use a link to your own script
#
#-------------how big / wide should the image be
$webcam_height  = ' height:100%; ';     // always 
$webcam_width   = ' width: 100%; ';     // this will stretch to fit
#$webcam_width   = ' ';                 // this will NOT stretch the picture, remove comment mark if you want this
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load settings / shared data and common scripts
$scrpt          = 'PWS_settings.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$extra  = '';
if (isset ($add_timestamp) && $add_timestamp == true) 
     {  if (strpos ('?',$mywebcamimg) == false)
             {  $extra  = '?_'.time();}
        else {  $extra  = '&_'.time();}
        $webcam_img     = $webcam_img.$extra;
} // eo add timestamp
#
if (file_exists ($click_large) )
     {  echo '<a href="'.$click_large.'" data-featherlight="iframe" title="WEATHERSTATION WEBCAM">'.PHP_EOL;}
elseif ($click_large === 'image')
     {  echo '<a href="image_popup.php?nr=wcam2" data-featherlight="iframe" title="WEATHERSTATION WEBCAM">'.PHP_EOL;}  
else {  echo '<a href="webfilm_popup.php" data-featherlight="iframe" title="WEATHERSTATION WEBCAM">'.PHP_EOL;}

echo '<img src="'.$webcam_img.'" alt="weathercam" style="'.$webcam_width.$webcam_height.';" />
</a>'.PHP_EOL;
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
