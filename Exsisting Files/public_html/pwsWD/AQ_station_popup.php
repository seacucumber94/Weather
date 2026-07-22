<?php $scrpt_vrsn_dt  = 'AQ_station_popup.php|01|2023-09-09|';  # average pointer + close optional | release 2012_lts
# 
# -----------------     SETTINGS for this script
$pm_sensor_1    = 1;     $pm_text_1     = 'Outside';    // 1 to 4 sensors can be avialable
$pm_sensor_2    = 3;     $pm_text_2     = 'Inside';     // 1 to 4 sensors can be avialable
$pm_sensor_3    = false; $pm_text_3     = 'xxxxxx';
$pm_sensor_4    = false; $pm_text_4     = 'xxxxxx';
#
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$station_link   = '';
$explain_link   = 'https://www.airnow.gov/aqi/aqi-basics/';
$popup_css      = './css/popup_css.css';
#
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_info      = 'More information';
$ltxt_aq_info   = 'Air Quality Index';
$ltxt_luftdaten = 'Station sensor';
$ltxt_url       = 'Our station sensor';
$ltxt_sensor    = 'Sensor data captured at';
$ltxt_id        = 'Sensor_ID';
$ltxt_crrnt     = 'Current';
$ltxt_24h       = '24 hours';
#$ltxt_pm10      = 'PM10';
$ltxt_pm25      = 'PM25';
$ltxt_disclaim2 = 'The health effects should be based on 24 hours average values only.';
$htxt_period    = 'period';
$htxt_particle  = 'particle';
$htxt_contration= 'concentration';
$htxt_AQI       = 'AQI';
$htxt_risk      = 'Risk';
$htxt_loc       = 'Location';
#
$clssfctn_txts  = array();
$clssfctn[7]    = 'unkown / error in data';
$clssfctn[6]    = 'Health alert: everyone may experience more serious health effects';
$clssfctn[5]    = 'Health warnings of emergency conditions. The entire population is more likely to be affected';
$clssfctn[4]    = 'Everyone may begin to experience health effects; members of sensitive groups may experience more serious health effects.';
$clssfctn[3]    = 'Members of sensitive groups may experience health effects. The general public is not likely to be affected.';
$clssfctn[2]    = 'Air quality is acceptable; however, for some pollutants there may be a moderate health concern for a very small number of people who are unusually sensitive to air pollution.';
$clssfctn[1]    = 'Air quality is considered satisfactory, and air pollution poses little or no risk';
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
#     check if data is usable
#-----------------------------------------------
$dataFALSE              = '';
if (! array_key_exists ('pm25_crnt'.$pm_sensor_1, $weather) ) 
     {  $dataFALSE = __LINE__.': No PM sensor number '.$pm_sensor_1.'  found'; }
if ($dataFALSE<> '')
     {  echo 'Problem '.$dataFALSE.'<br />Check settings and data'; return;}
#
#-----------------------------------------------
#      general functions / tables for AQ scripts
#-----------------------------------------------
$scrpt          = 'AQ_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;  
#
#-----------------------------------------------
#                        get the data to display 
#-----------------------------------------------
# 
$aq_unix        = $weather['datetime'];
$values         = array();
function get_aq_data  ($nr) 
     {  global $weather, $values;
        $key    = 'pm25_crnt'.$nr;
        if (! array_key_exists ($key, $weather) ) {return false;}
        $values[$nr]['pm']      = 
        $pm                     = number_format($weather[$key],1);
        $values[$nr]['aq']      = number_format(pm25_to_aqi($pm),1 );
#
        $key    = 'pm25_24avg'.$nr;
        if (array_key_exists ($key, $weather) )
              { $values[$nr]['pm_avg']  = 
                $pm                     = number_format($weather[$key],1);
                $values[$nr]['aq_avg']  = number_format(pm25_to_aqi($pm),1 );}
        else {  $values[$nr]['pm_avg']  = false; }
#      
        return true;
} // eof get_aq_data

for ($n = 1; $n <5; $n++)
     {  $key    = 'pm_sensor_'.$n;
        if (isset ($$key) && $$key <> false ) 
             {  $result = get_aq_data  ($$key);
                if ($result == false) {continue;}
                $txt    = 'pm_text_'.$n;
                $values[$$key]['text'] = $$txt;}
        } // eo for
#echo '<pre>'.__LINE__.print_r($values,true);
#
# --------------------------------   test values
#$values[1]['pm']       = 13.0;
#$values[1]['aq']       = 52.1;
#$values[1]['pm_avg']   = 6.1;
#$values[1]['aq_avg']   = 25.4;
#echo '<pre>'.__LINE__.print_r($values,true); exit;
# --------------------------------   test values
#
$ltxt_url       = lang($ltxt_url);
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $ltxt_clsppp    = lang($ltxt_clsppp); #### 2021-02-11
        $close          = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close          = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body class="dark" style="background-color: transparent;">
    <div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;">
'.$close.'
   <span style="color: #FF7C39; ">'.$ltxt_url.'</span>
    </div>'.PHP_EOL;
#
$arrow_black  = '<td> <div style="border-left:10px solid transparent;
border-right:10px solid transparent;
border-top:10px solid #000;
height:0;
width:0;
margin:0 auto;
"></div></td>';
#
$nr_data_coloms = 6;    // nr of values in the table
$str_hr         = '<tr style="height: 4px;"><td style="height: 4px;" colspan ="'.($nr_data_coloms+$nr_blocks ).'"><hr /></td></tr>'.PHP_EOL;
$string        = '<div id="aqhi_davis" style="display: block;  width: 100%;  background-color: white; color: black;">
<br />
<table class="" style = "width: 98%; border-collapse: collapse; margin: 0 auto;">
<tr> 
<th style="text-align: left;   "><small>'.    lang($htxt_period)        .'</small></th>
<th style="text-align: left;   "><small>'.    lang($htxt_loc)           .'</small></th>
<th style="text-align: left;    "><small>'.   lang($htxt_particle)      .'</small></th>
<th style="text-align: left;   "><small>'.    lang($htxt_contration)    .'</small></th>
<th style="text-align: center; "><small>'.    lang($htxt_AQI )          .'</small></th>
<th style="text-align: left;    "><small>'.   lang($htxt_risk)          .'</small></th>'.PHP_EOL;
$string         .= str_repeat ('<th>&nbsp;</th>',$nr_blocks); # echo __LINE__.' $nr_blocks = '.$nr_blocks; exit;
$string         .= '</tr>'.PHP_EOL;
echo $string; 
$strng_crnt     = $strng_avg = '';
foreach ($values as $sensor) 
     {  $text   = $sensor['text'];
        $arr    = aq_set_clrs ($sensor['aq']);  #echo '<pre>'.__LINE__.print_r($arr,true); exit;        
        $clmn   = $arr['clmn'];
        $risk   = lang($arr['text']);
        $strng_crnt .= '<tr><td style="text-align: left;"><small>'.lang($ltxt_crrnt).'</small></td>
<td style="text-align: left;"><small>'.lang($text).'</small></td>
<td style="text-align: left;"><small>'.lang($ltxt_pm25).'</small></td>
<td style="text-align: left;"><small>'.$sensor['pm'].' ug/m3</small></td>
<td style="text-align: center;"><small><b>'.$sensor['aq'].'</b></small></td>
<td style="text-align: left;"><small>'.$risk.'</small></td>'.PHP_EOL;
        for ($p = 1; $p <= $nr_blocks ; $p++)  
             {  if ($clmn == $p) 
                     {  $strng_crnt .= $arrow_black;} 
                else {  $strng_crnt .= '<td>&nbsp;</td>'; } 
        }
        $strng_crnt .= '</tr>'.PHP_EOL;   
#
        if ($sensor['pm_avg'] == false) {continue;}
        $arr    = aq_set_clrs ($sensor['aq_avg']); # echo '<!-- (line:'.__LINE__.') sensor: '.$sensor['aq_avg'].' = '.print_r($arr,true).' -->'.PHP_EOL;# exit;        
        $risk   = lang($arr['text']);
        $clmn   = $arr['clmn'];  # 2023-09-09
        $strng_avg .= '<tr><td style="text-align: left;"><small>'.lang($ltxt_24h).'</small></td>
<td style="text-align: left;"><small>'.lang($text).'</small></td>
<td style="text-align: left;"><small>'.lang($ltxt_pm25).'</small></td>
<td style="text-align: left;"><small>'.$sensor['pm_avg'].' ug/m3</small></td>
<td style="text-align: center;"><small><b>'.$sensor['aq_avg'].'</b></small></td>
<td style="text-align: left;"><small>'.$risk.'</small></td>'.PHP_EOL;
        for ($p = 1; $p <= $nr_blocks ; $p++)  
             {  if ($clmn == $p) 
                     {  $strng_avg .= $arrow_black;} 
                else {  $strng_avg .= '<td>&nbsp;</td>'; } 
        }
        $strng_avg .= '</tr>'.PHP_EOL;           
}
#
echo $strng_crnt.$str_hr.$strng_avg;
#
$string = '<tr>'.str_repeat('<td>&nbsp;</td>',$nr_data_coloms).PHP_EOL;
$width_color = floor(50/$nr_blocks);
for ($n = 1; $n <= $nr_blocks; $n++) 
     {  $string .= '<td style = "width: '.$width_color.'%; text-align: center; border: 1px solid grey; font-weight:bold;"'
                        .' class= "'.$aq_class[$n].'">'.'</td>'.PHP_EOL;}
$string .= '</tr>'.PHP_EOL;
echo $string;
echo  '</table><br />'.PHP_EOL;  
#----------------------------------------------- 
#---------------------------   EXPLANATION BLOCK
#-----------------------------------------------
$coloms         = array ('band','index','P25','P10','risk');
$nr_cols        = count($coloms);
$string = '<table style="text-align: center; width: 98%; margin: 0 auto; border-collapse: collapse; border: 1px solid grey;">
<thead style="background-color: #ccc;">
<tr style="border-bottom: 1px solid grey;">'.PHP_EOL;
$string .= '<th><small>'.lang('classification').'</small></th>'.PHP_EOL;
$string .= '<th><small>'.lang('AQI')           .'</small></th>'.PHP_EOL;
$string .= '<th><small>'.lang('pm10')          .'</small></th>'.PHP_EOL;
$string .= '<th><small>'.lang('pm2.5')         .'</small></th>'.PHP_EOL;
$string .= '<th style="width: 50%;"><small>'.lang($ltxt_disclaim2) .'</small></th>'.PHP_EOL;
$string .= '</tr></thead>
<tbody>'.PHP_EOL;
echo $string;
$explain= '';
$rowspan= '';  # 'rowspan="'.$rowspan.'" ';
for ($p = 1; $p <= $nr_blocks; $p++) 
    {   $explain .= ' <tr style="border-bottom: 1px solid grey;">'.PHP_EOL;
        $explain .=  '<td style="font-weight:bold;" class= "' 
                .$aq_class[$p].'">' 
                .lang($aq_text[$p]).'</td>'.PHP_EOL;
        $explain .= '<td style="font-weight:bold; border: 1px solid grey;">';       
        if ($p == 0) 
             {  $explain .= '1-'.$AQ_levels[$p].'</td>'.PHP_EOL;}
        elseif ($p < $nr_blocks)
             {  $explain .= $AQ_levels[$p-1] .'-'.$AQ_levels[$p].'</td>'.PHP_EOL;}
        else {  $explain .= '> '.$AQ_levels[$p-1].'</td>'.PHP_EOL;}
        $extra = '';
        if ($p < $nr_blocks) 
             {  $tekst  = '<small>&lt;&nbsp;'.$pm10_levels[$p].'&nbsp;&mu;g/m<sup>3</sup></small>';}
        else {  $tekst  = '<small>&gt;&nbsp;'.$pm10_levels[$p-1].'&nbsp;&mu;g/m<sup>3</sup></small>';}
        $explain .= '<td style="border: 1px solid grey;">'.$tekst.'</td>'.PHP_EOL;
        if ($p < $nr_blocks) 
             {  $tekst  = '<small>&lt;&nbsp;'.$pm25_levels[$p].'&nbsp;&mu;g/m<sup>3</sup></small>';}
        else {  $tekst  = '<small>&gt;&nbsp;'.$pm25_levels[$p-1].'&nbsp;&mu;g/m<sup>3</sup></small>';}
        $explain .= '<td style="border: 1px solid grey;">'.$tekst.'</td>'.PHP_EOL;
        $explain .= '<td '.$rowspan.' style="max-width: 50%;"><small>'.lang($clssfctn[$p]).'</small></td></tr>'.PHP_EOL;
             
        }
#
$explain .= '<tr style="background-color: #ccc;"><td colspan="'.$nr_cols.'" style="border-top: 1px solid grey;">&nbsp;'
        .lang($ltxt_info).':&nbsp;'
        .'<a href="'.$explain_link.'" target="_blank">'
        .strtoupper($aqhi_type).'&nbsp;&nbsp;'.lang($ltxt_aq_info).'</a>&nbsp;&nbsp;&nbsp;&nbsp;<small>'
        .lang($ltxt_sensor).':&nbsp;'.date($dateFormat.' '.$timeFormatShort,$aq_unix).' &nbsp;'
        .'</small></td></tr>
</tbody>
</table>
<br />
</div>'.PHP_EOL;
echo $explain;

if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo '</body>
</html>'.PHP_EOL;
#
# style is printed in the header 
function my_style()
     {  global $popup_css , $nr_blocks, $aq_class, $aq_color, $aq_color_txt;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
        for ($n = 1; $n <= $nr_blocks; $n++)
             {  $return .= '.'.$aq_class[$n].' { background: '.$aq_color[$n].'; color: '.$aq_color_txt[$n].';}'.PHP_EOL;
             }
/*$return .='
        .green  { background: rgb(  0, 228,   0); color: black;}
        .yellow { background: rgb(255, 255,   0); color: black;}
        .orange { background: rgb(255, 126,   0); color: black;}
        .red    { background: rgb(255,   0,   0); color: black;}
        .purple { background: rgb(143,  63, 151); color: white;}
        .maroon { background: rgb(126,   0,  35); color: white;}
';  */           
# add pop-up specific css
        $return         .= '    </style>'.PHP_EOL;
        return $return;

 }
