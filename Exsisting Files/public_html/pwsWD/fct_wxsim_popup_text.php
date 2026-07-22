<?php $scrpt_vrsn_dt  = 'fct_wxsim_popup_text.php|01|2020-11-04|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color                  = "#FF7C39";    // important color
$clrwrm                 = "#FF7C39";    // warm / daytime color
$clrcld                 = "#01A4B4";    // cold
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
$SITE['defaultlang']    = substr($used_lang,0,2);   // setting for Saratoga parser
#-----------------------------------------------
# load general WXSIM code which loads the fct also
#-----------------------------------------------
$scrpt          = './fct_wxsim_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#---------------------  check if fct is complete
if (!isset ($arr_pp) || count ($arr_pp) < 6 ) 
     {  echo '<b style="color: red;"><small>wxsim file not ready</small></b>'; return;}
#
#-----------------------   language translations
$ltxt_fct_for   = lang('Forecast for');
$ltxt_fct_by    = lang('by');
$ltxt_fct_updtd = lang('Updated');
$ltxt_clsppp    = lang('Close');
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
$ltxt_url       = $ltxt_fct_for.' '.$arr_pp[0]['city'].' '.$ltxt_fct_by.' '.$arr_pp[0]['station'];
$ltxt_updated   = '<small class="invisible" style="padding-top: 2px; color: '.$color.'; float: right;">'.$ltxt_fct_updtd.': '.$arr_pp[0]['updated'].'&nbsp;&nbsp;</small>';
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
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'      <span style="color: '.$color.'">'.$ltxt_url.'</span>
      '.$ltxt_updated.'
    </div>'.PHP_EOL;

echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">'.PHP_EOL;
#
# ------------------- process every day-part
$rows           = count ($arr_pp);
for ($i = 0; $i < $rows; $i++)  
     {  $arr    = $arr_pp[$i];
        $from   = array ('<br />','<br/>','<br>');
        $text   = str_replace ($from,' ',$arr['text']);
        if ($arr['tphl'] <> 'blue') {$color = $clrwrm; } else {$color = $clrcld; }
        echo '<tr style="border-bottom: 1px grey solid; ">
<td style="text-align: right; padding-right: 4px; color: '.$color.'">&nbsp;'.str_replace(' ','&nbsp;',$arr['part']).'&nbsp;&nbsp;</td>
<td style="text-align: left; ">'.$text.'</td></tr>'.PHP_EOL;
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
