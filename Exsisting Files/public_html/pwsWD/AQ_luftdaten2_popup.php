<?php $scrpt_vrsn_dt  = 'AQ_luftdaten2_popup.php|01|2021-05-25|';  # check file exists + close optional |release 2012_lts
#
# -----------------     SETTINGS for this script
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$luftdaten_link = 'https://luftdaten.info/';
$popup_css      = './css/popup_css.css';
#
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_info      = 'More information';
$ltxt_aq_info   = 'Air Quality Index';
$ltxt_luftdaten = 'Luftdaten sensor';
$ltxt_url       = 'Our Luftdaten sensor';
$ltxt_sensor    = 'Sensor data captured at';
$ltxt_id        = 'Sensor_ID';
$ltxt_crrnt     = 'Current';
$ltxt_24hrs     = 'Last 24 hours';
$ltxt_7days     = 'Last 7 days';
$ltxt_pm10      = 'PM10';
$ltxt_pm25      = 'PM25';
$ltxt_disclaim1 = 'Our measurement values above are real-time values';
$ltxt_disclaim2 = 'The health effects should be based on 24 hours average values only.';
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
# ------------  load settings and common scripts
$scrpt          = 'PWS_settings.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#
$pm25 = $aqi = false;  // empty needed fields
#                      
#-----------------------------------------------
#                                  load the data 
#-----------------------------------------------
$my_sensor      = $luftdatenSensor;  // easyweather
$fl_t_ld        = $fl_folder.'luft_24data_esp8266-'.$my_sensor.'.arr';  
if (file_exists ($fl_t_ld))
     {  $data   = unserialize (file_get_contents($fl_t_ld)); }
else {  $data   = array();} #echo '<pre>'.__LINE__.print_r ($data,true); exit;
#
#----------------------  check if data is usable
$dataFALSE              = '';
if ( !array_key_exists ('last',$data) ) 
     {  $dataFALSE = __LINE__.': Invalid or no data'; }
elseif ( !array_key_exists ('hours',$data) )
     {  $dataFALSE = __LINE__.': No 24 hour data found'; } 
elseif ( !array_key_exists ('days',$data) )
     {  $dataFALSE = __LINE__.': No 7 day data found'; } 
if ($dataFALSE<> '')
     {  echo 'Problem '.$dataFALSE.'<br />Check settings and data'; 
        return;}
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
$pm10_crnt      = $data['P1']; 
$pm10_1day      = $data['P1_24h'];
$pm10_7day      = $data['P1_7d'];
$pm25_crnt      = $data['P2']; 
$pm25_1day      = $data['P2_24h'];
$pm25_7day      = $data['P2_7d'];
#
# --------------------------------   test values
# --------------------------------   test values
#
$aq10_crnt      = number_format(pm10_to_aqi($pm10_crnt),1 ); 
$aq10_1day      = number_format(pm10_to_aqi($pm10_1day),1 ); 
$aq10_7day      = number_format(pm10_to_aqi($pm10_7day),1 ); 
$aq25_crnt      = number_format(pm25_to_aqi($pm25_crnt),1 );
$aq25_1day      = number_format(pm25_to_aqi($pm25_1day),1 );
$aq25_7day      = number_format(pm25_to_aqi($pm25_7day),1 );
#
$unix           = $data['last'];
$sensorID       = $my_sensor;
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
          <hr/><br />
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
$nr_data_coloms = 5;    // nr of values in the table
$str_hr         = '<tr style="height: 4px;"><td style="height: 4px;" colspan ="'.($nr_data_coloms+$nr_blocks ).'"><hr /></td></tr>'.PHP_EOL;
$string        = '<div id="aqhi_davis" style="display: block;  width: 100%;  background-color: white; white; color: black;">
<br />
<table class="" style = "width: 98%; border-collapse: collapse; margin: 0 auto;">
<tr> 
<th style="text-align: left;   "><small>'.    lang('period')         .'</small></th>
<th style="text-align: left;    "><small>'.   lang('particle')       .'</small></th>
<th style="text-align: left;   "><small>'.    lang('concentration')  .'</small></th>
<th style="text-align: center; "><small>'.    lang('AQI')            .'</small></th>
<th style="text-align: left;    "><small>'.   lang('classification') .'</small></th>'.PHP_EOL;
$string         .= str_repeat ('<th>&nbsp;</th>',$nr_blocks); # echo __LINE__.' $nr_blocks = '.$nr_blocks; exit;
$string         .= '</tr>'.PHP_EOL;
echo $string; 
function print_1_line ($pm,$aqi,$period,$pol)
     {  global $AQ_levels , $aq_text, $arrow_black, $nr_blocks;
        foreach ($AQ_levels as $n => $value) 
             {  if ($aqi > $value) { continue;}
                $text   = lang($aq_text[$n]);
                break;}
        $string = '<tr><td style="text-align: left;"><small>'.lang($period).'</small></td>
<td style="text-align: left;"><small>'.lang($pol).'</small></td>
<td style="text-align: left;"><small>'.$pm.' ug/m3</small></td>
<td style="text-align: center;"><small><b>'.$aqi.'</b></small></td>
<td style="text-align: left;"><small>'.$text.'</small></td>'.PHP_EOL;
        for ($p = 1; $p <= $nr_blocks ; $p++)  
             {  if ($n == $p) 
                     {  $string .= $arrow_black;} 
                else {  $string .= '<td>&nbsp;</td>'; } 
        } // eo for
        $string .= '</tr>'.PHP_EOL;          
        echo $string;    
} //eof print_1_line

print_1_line ($pm25_crnt ,$aq25_crnt, $ltxt_crrnt, $ltxt_pm25);
print_1_line ($pm10_crnt ,$aq10_crnt, $ltxt_crrnt, $ltxt_pm10);
echo $str_hr;
print_1_line ($pm25_1day ,$aq25_1day, $ltxt_24hrs, $ltxt_pm25);
print_1_line ($pm10_1day ,$aq10_1day, $ltxt_24hrs, $ltxt_pm10);
echo $str_hr;
print_1_line ($pm25_7day ,$aq25_7day, $ltxt_7days, $ltxt_pm25);
print_1_line ($pm10_7day ,$aq10_7day, $ltxt_7days, $ltxt_pm10);
echo $str_hr;
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
$string .= '<th style="width: 50%;"><small>'.lang($ltxt_disclaim1).'<br />'.lang($ltxt_disclaim2) .'</small></th>'.PHP_EOL;
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
        .'<a href="'.$explain_link.'" target="_blank">&nbsp;'.strtoupper($aqhi_type).'&nbsp;&nbsp;'.lang($ltxt_aq_info).'</a>&nbsp;&nbsp;&nbsp;&nbsp;'
        .'<a href="'.$luftdaten_link.'"   target="_blank">&nbsp;'.lang($ltxt_luftdaten).'</a>&nbsp; <small>'
        .lang($ltxt_sensor).':&nbsp;'.date($dateFormat.' '.$timeFormatShort,$unix).' &nbsp;'
        .lang($ltxt_id).':&nbsp;'.$sensorID
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
