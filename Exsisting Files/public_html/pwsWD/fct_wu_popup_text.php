<?php   $scrpt_vrsn_dt  = 'fct_wu_popup_text.php|01|2021-06-10|';  # error check + check file | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color          = "#FF7C39";    // important color
$clrwrm         = "#FF7C39";    // warm / daytime color
$clrcld         = "#01A4B4";    // cold
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
# ------------------------- translation of texts
$ltxt_clsppp    = lang('Close');
$ltxt_url       = 'WeatherUnderground '.lang('Forecast');
#-----------------------------------------------
#        compose file name && load file in array
#-----------------------------------------------
$name           = 'wufct_'.$locale_wu.'_'.$wu_fct_unit.'.txt';
$file           = $fl_folder.$name;
$json           = $response     = false;
if (file_exists ($file) )
     {  $json           = file_get_contents($file); 
        $response       = json_decode($json, true);}
#
# ----------   check if forecast data is present
if (   $json == false || $response == false
    || !is_array ($response['daypart']) 
    || count ($response['daypart'][0]) < 4 )   
#         if not correct. display small messasge  
     {  echo '<p style="color: red;">WU forecast file not ready</p>'; 
        return; }  
#-----------------------------------------------   
#           the json coantains all kinds of data
#                          load forecast in $arr
#-----------------------------------------------
$arr            = $response['daypart'][0];  
$rows           = count ($arr['daypartName']);
#
# search for first valid forecast, sometimes first fct is empty 
$start = 0;
if ($arr['dayOrNight'][0] == '') {$start++;}
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
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'      <span style="color: '.$color.'">'.$ltxt_url.'</span>
    </div>'.PHP_EOL;
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">';
#
#-----------------------------------------------
#                           print text forecasts
#-----------------------------------------------
$start = 0;
if ($arr['dayOrNight'][0] == '') {$start++; }
#
for ($n = $start; $n < $rows; $n++) // print 1 row / daypart 
     {  if ($arr['dayOrNight'][$n] == 'D')   { $color = $clrwrm; } else { $color = $clrcld; }
        $string = str_replace (' ','&nbsp;',$arr['daypartName'][$n]);
        echo '<tr>
<td style="border-bottom: 1px grey solid; height: 18px; text-align: right; padding-right: 6px; color: '
.$color.'">&nbsp;'.$string.'&nbsp;&nbsp;</td>
<td style="border-bottom: 1px grey solid; text-align: left;">'
.$arr['narrative'][$n].'</td></tr>'.PHP_EOL;
} // eo rows
echo '</table>
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