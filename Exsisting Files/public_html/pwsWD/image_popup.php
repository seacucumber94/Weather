<?php $scrpt_vrsn_dt  = 'image_popup.php|01|2022-11-22|';  # nostretch + new links +timestamp webcam | release 2012_lts
#
#       add timestamp for webcam images
$extra_cam      = '?_'.time();
#$extra_cam      = '&_'.time();
#$extra          = '';
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
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------------------------- load settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -----------------------  general functions aso  
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$show_close_x   = $close_popup;  // set to false or true to overrde settings
$ltxt_clsppp    = lang('Close');
# images
$pop_img        = array();
$n      = 'earth';
$pop_img[$n]['url']     = 'https://www.fourmilab.ch/cgi-bin/Earth?img=learth.evif&imgsize=320&dynimg=y&opt=-p&lat=&lon=&alt=&tle=&date=0&utc=&jd='.'&_'.time();
$pop_img[$n]['txt']     = 'Color map 24 hours';
$pop_img[$n]['head']    = '&nbsp;|&nbsp;This image courtesy of <a style="color: black;" href="https://www.fourmilab.ch/" target="_blank">Fourmilab Switzerland</a>';
$n      = 'baro_ao';
$pop_img[$n]['url']     = 'https://ocean.weather.gov/A_sfc_full_ocean_color.png'.'?_'.time();
$pop_img[$n]['txt']     = 'Atlantic Ocean Pressuremap';
$pop_img[$n]['head']    = '';
$n      = 'baro_po';
$pop_img[$n]['url']     = 'https://ocean.weather.gov/P_sfc_full_ocean_color.png'.'?_'.time();
$pop_img[$n]['txt']     = 'Pacific Ocean Pressuremap';
$pop_img[$n]['head']    = '';
$n      = 'baro_us';
$pop_img[$n]['url']     = 'https://www.wpc.ncep.noaa.gov/sfc/ussatsfc.gif'.'?_'.time();
$pop_img[$n]['txt']     = 'Pressuremap';
$pop_img[$n]['head']    = '';

$n      = 'rain_eu';
$pop_img[$n]['url']     = 'https://www.meteox.com/images.aspx?jaar=-3&voor=&soort=exp&c=&amp;n=&tijdid='.time();
$pop_img[$n]['txt']     = 'rain radar'; 
$pop_img[$n]['head']    = '';
$n      = 'rain_us';
$pop_img[$n]['url']     = 'https://s.w-x.co/staticmaps/wu/wu/radsum1200_cur/usshd/animate.png';
$pop_img[$n]['txt']     = 'rain radar';  
$pop_img[$n]['head']    = '';
$n      = 'rain';
$pop_img[$n]['url']     = 'https://www.rainviewer.com/map.html?loc='.$lat.','.$lon.',6'; #'https://www.rainviewer.com/map.html';
#'https://www.rainviewer.com/map.html?loc='.$lat.','.$lon.',6&oFa=1&oC=0&oU=0&oCS=1&oF=0&oAP=0&rmt=4&c=1&o=83&lm=0&th=0&sm=1&sn=1';
$pop_img[$n]['txt']     = 'rain radar';  
$pop_img[$n]['head']    = '';
$pop_img[$n]['show']    = 'frame';
$n      = 'aq_map';    // find your region here http://www.temis.nl/airpollution/no2col/data/omi/nrt/
$pop_img[$n]['url']     = $this_server.'fct_windy_popup.php?script=AQ';
$pop_img[$n]['txt']     = 'Air pollution';  
$pop_img[$n]['head']    = '';
$pop_img[$n]['show']    = 'frame';#
if ('replace wiht link'  == $mywebcamimg) {$mywebcamimg = './img/camplus.jpg';}
$n      = 'wcam1';
$pop_img[$n]['url']     = $mywebcamimg.$extra_cam;
$pop_img[$n]['txt']     = 'webcam';
$pop_img[$n]['head']    = '';
$pop_img[$n]['show']    = 'other';
$n      = 'wcam2';
$pop_img[$n]['url']     = './img/camplus.jpg';
$pop_img[$n]['txt']     = 'webcam';
$pop_img[$n]['head']    = '';
$n      = 'wcam3';
$pop_img[$n]['url']     = './img/camplus.jpg';
$pop_img[$n]['txt']     = 'webcam';
$pop_img[$n]['head']    = '';
$n      = 'bo';
$pop_img[$n]['url']     = 'https://map.blitzortung.org/?y#5/'.$lat.'/'.$lon.'';
$pop_img[$n]['txt']     = 'blitzortung';
$pop_img[$n]['head']    = '';
$pop_img[$n]['show']    = 'frame';
$n      = 'uv_map';
$pop_img[$n]['url']     = 'http://www.temis.nl/uvradiation/UVI/uvief0_eu.gif';
$pop_img[$n]['txt']     = 'xxxx';
$pop_img[$n]['head']    = '';
#$pop_img[$n]['show']    = 'frame';

$nr = 'earth';                                       
if (array_key_exists('nr',$_REQUEST) ) {  $nr = trim($_REQUEST['nr']); }  
if (!array_key_exists($nr,$pop_img) )  
     {  foreach ($pop_img as $nr => $value) {break;}
        }       
#
$show_url       = $pop_img[$nr]['url'];
$ltxt_url       = $pop_img[$nr]['txt'];
$ltxt_header    = $pop_img[$nr]['head'];
$background     = 'background';
if (isset ($pop_img[$nr]['show']) ) {$background = $pop_img[$nr]['show'];}
#
#  optional close X in the top-left.
if ($show_close_x == true )
     {  $closehtml = '<span style="background-color: white; color: black;  position: absolute; top:2px; left: 2px; font-size: 14px;">&nbsp;X&nbsp;&nbsp;<small>'
                .$ltxt_clsppp.'</small> <span style="color: black;">'.$ltxt_header .'</span></span>';}
else {  $closehtml = ''; }
# stretched to fit
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'"  style="width: 100%; height: 100%;">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>'.PHP_EOL;
if ($background == 'background') 
     {  echo '<body style="background: transparent url(\''.$show_url.'\') no-repeat fixed center;  background-size: 100% 100%; margin:  0;">'.PHP_EOL
.$closehtml.'
</body>
</html>';}
elseif ($background == 'frame')
 { echo '<body style="overflow: hidden; ">
<iframe src="'.$show_url.'"  allow="fullscreen" style="border-width: 0; width: 100%;  height: 100%; overflow: hidden; background:url(./img/loading.gif) top center no-repeat; ">
</iframe>'.$closehtml.'
</body>
</html>';}
else { echo '<body style="overflow: hidden; height: 100vh;">
<img style="width: 100%; vertical-align: top;" ;
src="'.$show_url.'">'
.$closehtml.'
</body>
</html>';}
#
# style is printed in the header 
function my_style()
     {  global $popup_css ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css

        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }