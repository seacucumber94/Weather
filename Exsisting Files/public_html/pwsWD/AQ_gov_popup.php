<?php $scrpt_vrsn_dt  = 'AQ_gov_popup.php|01|2021-02-28|';  # close optional | release 2012_lts
#
# -----------------     SETTINGS for this script
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$popup_css      = './css/popup_css.css'; // leave as is !
# 
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_info      = 'More information';
$ltxt_aq_info   = 'EPA Air Quality Index';
$ltxt_aqi       = 'AQI';
$ltxt_period    = 'period';
$ltxt_url       = 'Official AQ sensor station';
$ltxt_sensor    = 'Sensor data captured at';
$ltxt_crrnt     = 'Current';
$ltxt_frcst     = 'Forecast';
$ltxt_clssfctn  = 'classification';
$ltxt_pm10      = 'PM10';
$ltxt_pm25      = 'PM2.5';
$ltxt_O3        = 'O<sub>3</sub>';
$ltxt_disclaim2 = 'The health effects should be based on 24 hours average values only.';
$ltxt_min       = 'min';
$ltxt_max       = 'max';
$ltxt_avg       = 'avg';
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
#   ------------------  SUPPORT  ONLY EPA VALUES 
#$save_aqhi_type = $aqhi_type;
#
#$aqhi_type = 'eea';     
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#                      
#-----------------------------------------------
#                                  load the data 
#-----------------------------------------------
$fl_t_ld                = $fl_folder.$gvaqi_fl;  # "jsondata/gov_aqi.txt"
$json_string = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string            = file_get_contents($fl_t_ld);
        $parsed_json            = json_decode($json_string,true);} #echo '<pre>'.print_r ($parsed_json,true); 
#
#----------------------  check if data is usable
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = __LINE__.': Invalid / no JSON data'; }
elseif (!array_key_exists ('data', $parsed_json) )
     {  $dataFALSE = __LINE__.': No data found'; } 
elseif (!array_key_exists ('aqi', $parsed_json['data']) )
     {  $dataFALSE = __LINE__.': No AQindex found'; } 
if ($dataFALSE <> '')
     {  echo 'Problem '.$dataFALSE.'<br />Check settings and data'; return;}
#
if (!array_key_exists('data',$parsed_json) )
     {  echo 'Error: '.__LINE__.' No valid data fround, script ends'; return;}
#
#-----------------------------------------------
#      general functions / tables for AQ scripts
#-----------------------------------------------
$scrpt          = 'AQ_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL;  
include $scrpt;  #
aq_array_load ();
#-----------------------------------------------
#                        get the data to display 
#-----------------------------------------------
#    
$arr    =  $parsed_json['data'];   # echo '<pre>'.__LINE__.print_r($arr,true);   exit; 
$aqi    =  (float) $arr['aqi'];     
$dom_pol=  $arr['dominentpol'];
$city   = $arr['city']['name'];
$unix   = $arr['time']['v'];
#
function gov_cnvrt ($pol,$value) {
        global $aqhi_type;
        if ($aqhi_type <> 'epa' && $value <> 0) 
             {  $return = round(aq_epa_eea($pol,$value),1); #echo __LINE__.' $pol='.$pol.' $value='.$value.' $return='.$return.PHP_EOL; 
                return $return;}
        return $value;
} // eof gov_cnvrt
#
if (!array_key_exists('iaqi',$arr) ) 
     {  $iaqi   = array ();}
else {  $iaqi   = $arr['iaqi'];}
$pollutants     = array('o3','pm10','pm25');  // those we want to show
$crrnt          = array();
foreach ($pollutants as $pollutant)
    {   if (!array_key_exists($pollutant,$iaqi) )     
             {  $crrnt[$pollutant]      = false;}
        else {  $crrnt[$pollutant]      = gov_cnvrt ($pollutant, $iaqi[$pollutant]['v']);}
} // eo foreach pollutant
#
if (!array_key_exists('forecast',$arr) )            { $fct_aq   = array();}
elseif (!array_key_exists('daily',$arr['forecast'])){ $fct_aq   = array();}
else                                                { $fct_aq   = $arr['forecast']['daily'];}
$fct_arr        = array();
foreach ($fct_aq as $pollutant => $days)
     {  if (!in_array($pollutant,$pollutants) ) {continue;}
        foreach ($days as $values) 
             {  $key    = $values['day'];
                $arr    = array();
                $arr['min']     = gov_cnvrt ($pollutant, $values['min']);
                $arr['max']     = gov_cnvrt ($pollutant, $values['max']);
                $arr['avg']     = gov_cnvrt ($pollutant, $values['avg']);
                $fct_arr[$key][$pollutant]= $arr;
        } // fe day
} // eo fe pol             
#
#echo '<pre>'.__LINE__.print_r($fct_aq, true).print_r($fct_arr, true); exit;
#
$ltxt_url       = lang($ltxt_url).' - '.$city;
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
$nr_data_coloms = 12;    // nr of values in the table
$str_hr         = '<tr style="height: 4px;"><td style="height: 4px;" colspan ="'.($nr_data_coloms+$nr_blocks ).'"><hr /></td></tr>'.PHP_EOL;
#
echo  '<div id="aqhi_davis" style="display: block;  width: 100%;  background-color: white; color: black;"><br />
<table class="" style = "width: 98%; border-collapse: collapse; margin: 0 auto;">'.PHP_EOL;
echo '<tr> 
<th style="text-align: left;   "><small>'               .'&nbsp;'             .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_pm25)     .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_pm10)     .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_O3)       .'</small></th>
<th style="text-align: left;  "><small>'                .lang($ltxt_aqi)      .'</small></th>
<th style="text-align: left;  "><small>'                .lang($ltxt_clssfctn) .'</small></th>'.PHP_EOL;
echo str_repeat ('<th>&nbsp;</th>',$nr_blocks); 
echo '</tr>'.PHP_EOL;
 
$aqi    = $crrnt['o3'];
if ($crrnt['pm10'] > $aqi) {$aqi = $crrnt['pm10'];}
if ($crrnt['pm25'] > $aqi) {$aqi = $crrnt['pm25'];}
$result = aq_set_clrs ($aqi);
#echo '<pre>'.__LINE__.' $nr_blocks='. $nr_blocks.' '.print_r($result, true); 
/*    [icon] => aq_green.svg
    [class] => dottedcirclegreen
    [color] => rgb(0,228,0)
    [text] => GoodAQ
    [clmn] => 1 */
echo '<tr> 
<th style="text-align: left;   "><small>'               .lang($ltxt_crrnt)      .'</small></th>
<td style="text-align: center;" colspan="3" ><small>'   .$crrnt['pm25']         .'</small></td>
<td style="text-align: center;" colspan="3" ><small>'   .$crrnt['pm10']         .'</small></td>
<td style="text-align: center;" colspan="3" ><small>'   .$crrnt['o3']           .'</small></td>
<td style="text-align: left;  "><small>'                .$aqi                   .'</small></td>
<td style="text-align: left;  "><small>'                .lang($result['text'])  .'</small></td>'.PHP_EOL;
for ($p = 1; $p <= $nr_blocks ; $p++)  
     {  if ($result['clmn'] == $p) 
             {  echo $arrow_black;} 
        else {  echo '<td>&nbsp;</td>'; } 
}
echo '</tr>'.PHP_EOL;
echo $str_hr; 
# forecast
$string = '<tr> 
<th style="text-align: left;   "><small>'               .'&nbsp;'             .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_pm25)     .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_pm10)     .'</small></th>
<th style="text-align: center;" colspan="3" ><small>'   .lang($ltxt_O3)       .'</small></th>
<th style="text-align: left;  "><small>'                .lang($ltxt_aqi)      .'</small></th>
<th style="text-align: left;  "><small>'                .lang($ltxt_clssfctn) .'</small></th>'.PHP_EOL;
$string .= str_repeat ('<th>&nbsp;</th>',$nr_blocks); 
$string .= '</tr>'.PHP_EOL;
# echo $string;
$min    = lang($ltxt_min);
$max    = lang($ltxt_max);
$avg    = lang($ltxt_avg);

echo '<tr> 
<th style="text-align: left;  " ><small>'.lang($ltxt_frcst)     .'</small></th>';

$string = '<th style="text-align: center;" ><small>'.$min       .'</small></th>
<th style="text-align: center;" ><small>'.$avg       .'</small></th>
<th style="text-align: center;" ><small>'.$max       .'</small></th>';

echo $string.$string.$string.'
<th style="text-align: center;" ><small>'.'&nbsp;'   .'</small></th>
<th style="text-align: center;" ><small>'.'&nbsp;'   .'</small></th>'.PHP_EOL;
echo str_repeat ('<th>&nbsp;</th>',$nr_blocks); 
echo '</tr>'.PHP_EOL;
foreach ($fct_arr as $day => $pols)
     {  $aqi = 0;
        echo '<tr>'.PHP_EOL;
        $date_ts        = strtotime ($day.' 11:00:00');
        $day_ft         = date ($dateFormat,$date_ts);
        echo '<td style="text-align: left;    " ><small>'.$day_ft .'</small></td>';
        $keys   = array ('pm25','pm10','o3');
        foreach ($keys as $key) 
             {  echo  '<td style="text-align: center;  " ><small>'.$pols[$key]['min']    .'</small></td>';
                echo  '<td style="text-align: center;  " ><small>'.$pols[$key]['avg']    .'</small></td>';
                echo  '<td style="text-align: center;  " ><small>'.$pols[$key]['max']    .'</small></td>';
                if ($pols[$key]['max'] > $aqi) {$aqi = $pols[$key]['max'];}
        } // eo fe 
        $result = aq_set_clrs ($aqi);
        echo  '<td style="text-align: left;  " ><small>'.$aqi                   .'</small></td>';
        echo  '<td style="text-align: left;  " ><small>'.lang($result['text'])  .'</small></td>';
        for ($p = 1; $p <= $nr_blocks ; $p++)  
             {  if ($result['clmn'] == $p) 
                     {  echo $arrow_black;} 
                else {  echo '<td>&nbsp;</td>'; } 
        }
        echo '</tr>'.PHP_EOL;

}
$string = '<tr>'.str_repeat('<td>&nbsp;</td>',$nr_data_coloms).PHP_EOL;
$width_color = floor(50/$nr_blocks);
for ($n = 1; $n <= $nr_blocks; $n++) 
     {  $string .= '<td style = "width: '.$width_color.'%; text-align: center; border: 1px solid grey; font-weight:bold;"'
                        .' class= "'.$aq_class[$n].'">'.'</td>'.PHP_EOL;}
$string .= '</tr>'.PHP_EOL;
echo $string;
#echo '<pre>'.__LINE__.print_r($fct_arr, true); exit;
echo  '</table><br />'.PHP_EOL;  
#----------------------------------------------- 
#---------------------------   EXPLANATION BLOCK
#-----------------------------------------------
$coloms         = array ('band','index','risk');
$nr_cols        = count($coloms);
$string = '<table style="text-align: center; width: 98%; margin: 0 auto; border-collapse: collapse; border: 1px solid grey;">
<thead style="background-color: #ccc;">
<tr style="border-bottom: 1px solid grey;">'.PHP_EOL;
$string .= '<th><small>'.lang('classification').'</small></th>'.PHP_EOL;
$string .= '<th><small>'.lang('AQI')           .'</small></th>'.PHP_EOL;
#$string .= '<th><small>'.lang('pm10')          .'</small></th>'.PHP_EOL;
#$string .= '<th><small>'.lang('pm2.5')         .'</small></th>'.PHP_EOL;
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
        $explain .= '<td '.$rowspan.' style="max-width: 50%;"><small>'.lang($clssfctn[$p]).'</small></td></tr>'.PHP_EOL;
             
        }
#
$explain .= '<tr style="background-color: #ccc;"><td colspan="'.$nr_cols.'" style="border-top: 1px solid grey;">&nbsp;'
        .lang($ltxt_info).':&nbsp;'
        .'<a href="'.$explain_link.'" target="_blank">&nbsp;'.lang($ltxt_aq_info).'</a>&nbsp;&nbsp;&nbsp;&nbsp;'
        .'&nbsp; <small>'
        .lang($ltxt_sensor).':&nbsp;'.gmdate($dateFormat.' '.$timeFormatShort,$unix).' &nbsp;'
        .'</small></td></tr>
</tbody>
</table>
<br />
</div>'.PHP_EOL;   #date($dateFormat.' '.$timeFormatShort,$unix)
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
