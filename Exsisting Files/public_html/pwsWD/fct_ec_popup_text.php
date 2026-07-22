<?php  $scrpt_vrsn_dt  = 'fct_ec_popup_text.php|01|2020-11-04|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
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
     {  ini_set('display_errors', 0); error_reporting(0);}
else {  ini_set('display_errors', 1); error_reporting(1);}  
#
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
#
# these are settings for retrieving information
# from https://dd.meteo.gc.ca/citypage_weather/xml/ON/s0000024_f.xml
#------------------------------------------------
#
$EC_area	= $alarm_area;
# -----------------------for testing
#$province       = 'ON'; #
#$EC_area	= 's0000024';  # for testing
# -----------------------for testing
#
$fct_d_needed   = true;
#
# ----------------------   load general EC code
$scrpt          = 'fct_ec_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
$return = include_once $scrpt; 
if ($return == false) { echo 'script ends'; return false;}  
#
# ------------------------- translation of texts
$ltxt_url       = lang('Forecast');
$ltxt_clsppp    = lang('Close');
$color          = $clrwrm = "#FF7C39";
$clrcld         = "#01A4B4";
if ($EC_region <> '') { $EC_region = ' - '.$EC_region;}
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%;" >
'.$close
.'      <span style="color: '.$color.';">'.$ltxt_url.$EC_region.'</span>
    </div>'.PHP_EOL;
#
# ------------------- process every day-part
#
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
';
foreach ($fcts_arr as $arr) // print 1 row / daypart with all data in coloms
     {  if ($arr['temptype'] <> 'low') 
             {  $color = $clrwrm; } else {$color = $clrcld; }
        $string = str_replace (' ','&nbsp;',  $arr['daypart']);
        echo '<tr style="border-bottom: 1px grey solid; ">
<td style="text-align: right; padding-right: 6px; color: '.$color.'">&nbsp;'
.$string.'&nbsp;&nbsp;</td>
<td style="text-align: left; ">'
.$arr['text'].'</td></tr>'.PHP_EOL;

        } // eo for each row
echo '<tr><td  colspan="2">
<span style="font-size: 9px;">'.lang('Powered by').':&nbsp;&nbsp;
<a href="https://weather.gc.ca/canada_e.html" target="_blank" style="color: grey">
<b>Environment Canada</b></a></span>
</td></tr></table>
</div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;

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