<?php $scrpt_vrsn_dt  = 'AQ_purpleair_popup.php|01|2022-11-22|';  # new file format + check file + close optional + release 2012_lts
#
# -----------------     SETTINGS for this script
# ------------------------------   sensor to use
$purpleUse       = true; #  true  = use highest   A or B use first or second sensor 
#$purpleUse       ='B';  
#
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$purple_link    = 'https://www2.purpleair.com/';
$popup_css      = './css/popup_css.css';
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_info      = 'More information';
$ltxt_aq_info   = 'Air Quality Index';
$ltxt_purple    = 'Purpleair sensor';
$ltxt_url       = 'Our Purple AQ sensor';
$ltxt_sensor    = 'Sensor data captured at';
$ltxt_temp      = 'Temperature';
$ltxt_hum       = 'Humidity';
$ltxt_dewp      = 'Dewpoint';
$ltxt_disclaim1 = 'Our measurement values above are real-time values';
$ltxt_disclaim2 = 'The health effects should be based on 24 hours average values only.';
$ltxt_pm25      = 'pm2.5';
$ltxt_pm10      = 'pm10';
$ltxt_periods   = array (
  'Current', '10 min', '30 min', '1 hour', '6 hour', '24 hour','1 week');
$htxt_period    = 'period';
$htxt_particle  = 'particle';
$htxt_contration= 'concentration';
$htxt_AQI       = 'AQI';
$htxt_risk      = 'Risk';
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
# ------------  load settings and common scripts
$scrpt          = 'PWS_settings.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#
#-----------------------------------------------
#                                  load the data 
#-----------------------------------------------
$fl_t_ld                = $fl_folder.$prpl_fl;
$json_string    = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);
        $parsed_json    = json_decode($json_string,true); } # echo '<pre>'.__LINE__.' '.print_r ($parsed_json,true); exit;
#-----------------------------------------------
#     check if data is usable
#-----------------------------------------------
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = __LINE__.': Invalid / no JSON data<br />'.$prpl_fl; }
elseif (!array_key_exists ('sensor', $parsed_json) )
     {  $dataFALSE = __LINE__.': No sensor found'; } 
elseif (!array_key_exists ('stats', $parsed_json['sensor']) )
     {  $dataFALSE = __LINE__.': No sensor-data found'; } 
elseif (!array_key_exists ('pm2.5', $parsed_json['sensor']['stats']) )
     {  $dataFALSE = __LINE__.': No measurements found'; } 
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
$sensor = $parsed_json['sensor'];
$values = $sensor['stats'];            
$last_modified  = (int) $values['time_stamp']; # echo '<pre>'.__LINE__.print_r($values,true).' '.date('c',$last_modified); exit;
$pm25   = array();
$aq25   = array();
$pm25[0]= $values['pm2.5'];
$pm25[1]= $values['pm2.5_10minute'];
$pm25[2]= $values['pm2.5_30minute'];
$pm25[3]= $values['pm2.5_60minute'];
$pm25[4]= $values['pm2.5_6hour'];
$pm25[5]= $values['pm2.5_24hour'];
$pm25[6]= $values['pm2.5_1week'];
for ($n = 0; $n <=6; $n++)
     {  $aq25[$n] = number_format ((float) pm25_to_aqi($pm25[$n]) ,1);}
$pm10   = $sensor['pm10.0_atm_a'] ;
$AQ10   = number_format ((float) pm10_to_aqi($pm10) ,1);
 
/*\"v\":1.07, // Real time or current PM2.5 Value
\"v1\":1.3988595758168765, // Short term (10 minute average)
\"v2\":10.938131480857114, // 30 minute average
\"v3\":15.028685608345926, // 1 hour average
\"v4\":6.290537580116773, // 6 hour average
\"v5\":1.8393146177050788, // 24 hour average
\"v6\":0.27522764912064507, // One week average
\"pm\":1.07, // Real time or current PM2.5 Value
\"lastModified\":1490309930933, // Last modified time stamp for calculated average statistics
\"timeSinceModified\":69290 // Time between last two readings in milliseconds */
# --------------------------------   test values
#
# ------------------------- translation of texts
foreach ($ltxt_periods as $key => $text) {$ltxt_periods[$key]=lang($text);}
$ltxt_url       = lang($ltxt_url);
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $ltxt_clsppp    = lang($ltxt_clsppp); #### 2021-02-11
        $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
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
"></div></td>'.PHP_EOL;
#
$nr_data_coloms = 5;    // nr of values in the table
$str_hr = '<tr style="height: 4px;"><td style="height: 4px;" colspan ="'.($nr_data_coloms+$nr_blocks ).'"><hr /></td></tr>'.PHP_EOL;
$string = '<div id="aqhi_davis" style="display: block;  width: 100%;  background-color: white; color: black;">
<br />
<table class="" style = "width: 98%; border-collapse: collapse; margin: 0 auto;">
<tr> 
<th style="text-align: left;   "><small>'.    lang($htxt_period)    .'</small></th>
<th style="text-align: left;    "><small>'.   lang($htxt_particle)  .'</small></th>
<th style="text-align: left;   "><small>'.    lang($htxt_contration).'</small></th>
<th style="text-align: center; "><small>'.    lang($htxt_AQI )      .'</small></th>
<th style="text-align: left;    "><small>'.   lang($htxt_risk)      .'</small></th>'.PHP_EOL;
$string         .= str_repeat ('<th>&nbsp;</th>',$nr_blocks); # echo __LINE__.' $nr_blocks = '.$nr_blocks; exit;
$string         .= '</tr>'.PHP_EOL;
echo $string; 
$pmtxt  = lang($ltxt_pm10); 
$aq_nr  = $AQ10;
$prd_txt= $ltxt_periods[0];
foreach ($AQ_levels as $n => $value) 
     {  if ($aq_nr > $value) { continue;}
        $risk   = lang($aq_text[$n]);
        break;}
$string = '<tr><td style="text-align: left;"><small>'.$prd_txt.'</small></td>
<td style="text-align: left;"><small>'.$pmtxt.'</small></td>
<td style="text-align: left;"><small>'.$pm10.'</small></td>
<td style="text-align: center;"><small><b>'.$AQ10.'</b></small></td>
<td style="text-align: left;"><small>'.$risk.'</small></td>'.PHP_EOL;
for ($p = 1; $p <= $nr_blocks ; $p++)  
     {  if ($n == $p) 
             {  $string .= $arrow_black;} 
        else {  $string .= '<td>&nbsp;</td>'; } 
}
$string .= '</tr>'.PHP_EOL;               
echo $string;
echo $str_hr;

$pmtxt  = lang($ltxt_pm25);   
foreach ($pm25 as $key => $count )
     {  $aq_nr  = $aq25[$key];
        $prd_txt= $ltxt_periods[$key];
        foreach ($AQ_levels as $n => $value) 
             {  if ($aq_nr > $value) { continue;}
                $risk   = lang($aq_text[$n]);
                break;}
        $string = '<tr><td style="text-align: left;"><small>'.$prd_txt.'</small></td>
<td style="text-align: left;"><small>'.$pmtxt.'</small></td>
<td style="text-align: left;"><small>'.$count.'</small></td>
<td style="text-align: center;"><small><b>'.$aq_nr.'</b></small></td>
<td style="text-align: left;"><small>'.$risk.'</small></td>'.PHP_EOL;
        for ($p = 1; $p <= $nr_blocks ; $p++)  
             {  if ($n == $p) 
                     {  $string .= $arrow_black;} 
                else {  $string .= '<td>&nbsp;</td>'; } 
        }
        $string .= '</tr>'.PHP_EOL;               
        echo $string;
        } // eo all pms
echo $str_hr;
#     
$string = '<tr>'.str_repeat('<td>&nbsp;</td>',$nr_data_coloms);
$width_color = floor(50/$nr_blocks);
for ($n = 1; $n <= $nr_blocks; $n++) 
     {  $string .= '<td style = "width: '.$width_color.'%; text-align: center; border: 1px solid grey; font-weight:bold;"'
                        .' class= "'.$aq_class[$n].'"></td>'.PHP_EOL;}
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
$aq_ftemp       = $sensor['temperature'];
$aq_hum         = $sensor['humidity']; 
$aq_ftime       = $sensor['last_seen'];
if ($tempunit == 'C') 
     {  $aq_ftemp = convert_temp ($aq_ftemp,'F',$tempunit,1);}
$explain .= '<tr style="background-color: #ccc;"><td colspan="'.$nr_cols.'" style="border-top: 1px solid grey;">&nbsp;'
        .lang($ltxt_info).':&nbsp;'
        .'<a href="'.$explain_link.'" target="_blank">&nbsp;'.strtoupper($aqhi_type).'&nbsp;&nbsp;'.lang($ltxt_aq_info).'</a>&nbsp;&nbsp;&nbsp;&nbsp;'
        .'<a href="'.$purple_link.'"   target="_blank">&nbsp;'.lang($ltxt_purple).'</a>&nbsp; <small>'
        . '#'.$purpleairID.' ' // .'('.$used.') '
        .lang($ltxt_sensor).':&nbsp;'.date($dateFormat.' '.$timeFormatShort,$aq_ftime).' &nbsp;'
        .lang($ltxt_temp).':&nbsp;'.$aq_ftemp.'&deg;'.$tempunit.'&nbsp;'
        .lang($ltxt_hum) .':&nbsp;'.$aq_hum  .'%&nbsp;'
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
/*
$return .='
        .green  { background: rgb(  0, 228,   0); color: black;}
        .yellow { background: rgb(255, 255,   0); color: black;}
        .orange { background: rgb(255, 126,   0); color: black;}
        .red    { background: rgb(255,   0,   0); color: black;}
        .purple { background: rgb(143,  63, 151); color: white;}
        .maroon { background: rgb(126,   0,  35); color: white;}
';      */       
# add pop-up specific css

        $return         .= '    </style>'.PHP_EOL;
        return $return;

 }
