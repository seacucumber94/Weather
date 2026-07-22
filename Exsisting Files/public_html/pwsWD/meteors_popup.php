<?php  $scrpt_vrsn_dt  = 'meteors_popup.php|01|2020-11-04|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color                  = "#FF7C39"; // head line
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
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ------------------------- translation of texts
$ltxt_url       = lang('Active Sky Events');
$ltxt_clsppp    = lang('Close');
$ltxt_wikipedia = lang('Wikipedia');
$ltxt_credit    = lang('Data Provided by');
$ltxt_from      = lang('Visible');
$ltxt_peak      = lang('Peak');
$meteor_default = lang('No Active Sky Events');

$ltxt_hd1       = lang('Current Events');
$ltxt_hd2       = lang('Next Events');
$ltxt_hd3       = lang('Annual Meteor Showers');
$ltxt_hd4       = lang('Guide');
#
$mtr_mtrs       = lang ('Meteor Showers');
$mtr_mtr        = lang ('Meteor Shower');
$mtr_guide1     = mtr_long_txt ('Meteors are best viewed during the night hours, though meteors enter the atmosphere at any time of the day.');
$mtr_guide2     = mtr_long_txt ('They are just harder to see in the daylight apart from dawn and dusk.');
$mtr_guide3     = mtr_long_txt ('Any nearby ambientlight, Moonlight can make it difficult viewing.'); 
$mtr_guide4     = mtr_long_txt ('Meteor showers are best viewed away from the city lights.');

$mtr_ext_lnk1   = 'https://en.wikipedia.org/wiki/Meteor_shower';
$mtr_ext_lnk2   = 'https://www.imo.net/resources/calendar/';
$mtr_ext_lnk3   = 'https://en.wikipedia.org/wiki/Template:Solar_eclipse_set_2018%E2%80%932021';

$ltxt_solar     = lang('Solar').' '.lang('Eclipse');
$ltxt_lunar     = lang('Lunar').' '.lang('Eclipse');
$ltxt_annular   = lang('Annular');
$ltxt_total     = lang('Total');
$ltxt_partial   = lang('Partial');
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
#                       mtr_long_txt
# There are different versions of translation files
# with or without spaces in the texts.
# this function tries to find a suitable translation
#-----------------------------------------------
function mtr_long_txt ($trans) {
        global $lang;
        if (isset ($lang[$trans]) && $lang[$trans] <> $trans) {    return  $lang[$trans];} 
        $text   = str_replace (' ','',$trans);
        if (isset ($lang[$text]) &&  $lang[$text] <> $text)   {    return  $lang[$text];} 
        return $trans;
} // eof mtr_long_txt
#echo '>'.$mtr_guide1.'<'; exit;
#
# ----------------------- general meteors script
$scrpt          = 'meteors_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
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
.my_style()
.'</head>
<body class="dark">
    <div class="PWS_module_title" style="width: 100%; height: 20px; padding-top: 4px; font-size: 14px;" >
'.$close.
'      <span style="color: '.$color.'; font-size: 14px;">'.$ltxt_url.'</span>
    </div>
    <div class="PWS_weather_container" style="height: 144px; "><!-- toprow -->
        <div class="PWS_weather_item" style="position: relative; height: 142px;"><!-- weatheritem 1 -->
            <div class="PWS_module_title"><div class="title">'.$ltxt_hd1.' @ '.date($dateFormat,$now).'</div></div>
              <div style="font-size: 14px; line-height: 1.3; padding: 4px;">
'.$crrnt_string.'
                </div>
        </div><!-- eo weatheritem 1 -->
        <div class="PWS_weather_item" style="position: relative; height: 142px;"><!-- weatheritem 2 -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd2.'</div></div>
                <div style="font-size: 14px; line-height: 1.3;  padding: 4px;">
'.$nxt_string.'
                </div>
        </div><!-- eo weatheritem 2 -->
    </div><!-- eo toprow -->
    <div class="PWS_weather_container" style="height: 264px; "><!-- second row -->
        <div class="PWS_weather_item " style="position: relative; height: 262px;"><!-- weatheritem 3 info -->
            <div class="PWS_module_title">
                <div class="title">'.$ltxt_hd3.'</div>
            </div>
            <div style="height: 204px;padding: 10px; font-size: 10px; text-align: left; overflow-y: hidden;">
<table style="margin: 0 auto;">'.$guide_string.'</table>           
            </div>
            <div class="PWS_module_title">
                <div class="title"><a style="color: #AAA;" href="'.$mtr_ext_lnk2.'" target="_blank">'.$ltxt_credit.' IMO</a></div>
            </div>
        </div><!-- eo weatheritem 3 -->
        <div class="PWS_weather_item " style="position: relative; height: 262px;"><!-- weatheritem  4 info -->
            <div class="PWS_module_title">
                <div class="title">'.$ltxt_hd4.'</div>
            </div>
            <div style="height: 204px;padding: 10px; font-size: 12px; text-align: left;">'
.$mtr_guide1.' '
.$mtr_guide2.'<br /><br />'
.$mtr_guide3.' '
.$mtr_guide4.'<br />'
           .'</div>
            <div class="PWS_module_title">
                <div class="title"><a style="color: #AAA;" href="'.$mtr_ext_lnk1.'" target="_blank">'.$ltxt_wikipedia.'</a></div>
            </div>
        </div><!-- eo weatheritem 4 -->
    </div><!-- eo second row -->'.PHP_EOL;
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