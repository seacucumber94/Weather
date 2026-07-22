<?php $scrpt_vrsn_dt  = 'AQ_davis_popup.php|01|2021-05-25|';  # test missing file + close optional |release 2012_lts
#
# -----------------     SETTINGS for this script
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$davis_link     = 'https://www.weatherstations.co.uk/airlink.htm';
$popup_css      = './css/popup_css.css';
#
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_info      = 'More information';
$ltxt_aq_info   = 'Air Quality Index';
$ltxt_davis     = 'Davis sensor';
$ltxt_url       = 'Our Davis AQ sensor';
$ltxt_sensor    = 'Sensor data captured at';
$ltxt_temp      = 'Temperature';
$ltxt_hum       = 'Humidity';
$ltxt_dewp      = 'Dewpoint';
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
if ($dwl_AQ == 0) {$dwl_AQ = '';}  #### 2020-10-04 replace empty station ID when using DWL for data also.
$fl_t_ld                = $fl_folder.'wlcomv2API'.$dwl_AQ.'.json';  # " jsondata/wlcomv2API97452.json  "
$json_string = $parsed_json = false;    #### 2021-05-25
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);
        $parsed_json    = json_decode($json_string,true); } #echo '<pre>'.print_r ($parsed_json,true); 
#                                       #### 2021-05-25
#----------------------  check if data is usable
$dataTEXT       = '';
$dataOK         = false;
if ( $parsed_json == FALSE) 
     {  $dataTEXT = __LINE__.': Invalid / no JSON data'; }
elseif (!array_key_exists ('sensors', $parsed_json) )
     {  $dataTEXT = __LINE__.': No sensors found'; } 
else {  foreach ($parsed_json['sensors'] as $nr => $sensor)
             {  #echo '<pre>'.print_r ($sensor,true);
                if ($sensor['data_structure_type'] == '16' 
                     && array_key_exists ('data',$sensor)
                     && array_key_exists ( 0, $sensor['data']) )
                     {  $dataOK = true;
                        $arr    = $sensor['data'][0];
                        break;}     
                } // foreach
        } // else
if ($dataTEXT <> '')
     {  echo 'Problem '.$dataTEXT.'<br />Check settings and data'; return;}
if ($dataOK == false)
     {  echo 'Error: '.__LINE__.' No valid data fround, script ends'; return;}
#
#-----------------------------------------------
#      general functions / tables for AQ scripts
#-----------------------------------------------
$scrpt          = 'AQ_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;  
# 
$aq_ftime       = $arr['last_report_time'] ; #echo '<pre>$aq_ftime ='.$aq_ftime.print_r ($arr,true); exit;
$aq_ftemp       = $arr['temp'];
$aq_hum         = $arr['hum'];
$aq_fdewp       = $arr['dew_point'];
$aqi            = round((float) $arr['aqi_val']);  
#
$plltnts= array ('pm_2p5', 'pm_10'); #,'pm_1');  // those we want to show
$periods= array ('','_1_hour','_3_hour','_24_hour'); #,'_nowcast');
$values = array ();
foreach ($plltnts as $plltnt)
     {  foreach ($periods as $period)
             {  $key    = $plltnt.$period;
                $values [$key]['aq']= -1;
                if (array_key_exists($key,$arr) )
                     {  $values [$key]['pm']    = round($arr[$key],4);
                        if ($plltnt == 'pm_10')  {$values [$key]['aq'] = number_format(round(pm10_to_aqi($arr[$key]),1),1);}
                        if ($plltnt == 'pm_2p5') {$values [$key]['aq'] = number_format(round(pm25_to_aqi($arr[$key]),1),1);}
                        }
                else {  $values [$key]['pm']= -1;
                        $values [$key]['aq']= -1;
                        }
                }

        } #echo '<pre>'.print_r ($values,true);
#if ($aqhi_type <> 'epa') {
        $aqi = $values['pm_2p5']['aq'];
        if ( $values['pm_10']['aq'] > $aqi)
             {  $aqi = $values['pm_10']['aq'];}
#}
if (isset ($_REQUEST['test']))
     {  echo '<!-- '.print_r($values, true).' -->'.PHP_EOL;}
#
# --------------------------------   test values
#$test   = 0;
#$values ['pm_10_1_hour']['pm']       = $test;
#$values ['pm_10_1_hour']['aq']       = round(pm10_to_aqi($test));;
#$values ['pm_2p5_1_hour']['pm']       = $test;
#$values ['pm_2p5_1_hour']['aq']       = round(pm25_to_aqi($test));;
# ------------------------- translation of texts
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
"></div></td>'.PHP_EOL;
#
$nr_data_coloms = 5;    // nr of values in the table
$str_hr = '<tr style="height: 4px;"><td style="height: 4px;" colspan ="'.($nr_data_coloms+$nr_blocks ).'"><hr /></td></tr>'.PHP_EOL;
$string = '<div id="aqhi_davis" style="display: block;  width: 100%;  background-color: white; color: black;">
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
$current_AQ     = '';
foreach ($periods as $period)
     {  $done   = false;
        if ($period == '') 
             {  $prd_txt= lang('current'); }
        else {  $prd_txt= lang($period); }
        foreach ($plltnts as $plltnt)
             {  $key    = $plltnt.$period; 
                $count  = $values[$key]['pm'].' ug/m3';
                $aq_nr  = $values[$key]['aq'];
                foreach ($AQ_levels as $n => $value) 
                     {  if ($aq_nr > $value) { continue;}
                        $text   = $aq_text[$n];
                        break;}
                $risk   = lang($text);
                $string = '<tr><td style="text-align: left;"><small>'.$prd_txt.'</small></td>
                <td style="text-align: left;"><small>'.lang($plltnt).'</small></td>
                <td style="text-align: left;"><small>'.$count.'</small></td>
                <td style="text-align: center;"><small><b>'.$aq_nr.'</b></small></td>
                <td style="text-align: left;"><small>'.$risk.'</small></td>'.PHP_EOL;
                if ($n == 0) {$n = 1;}
                for ($p = 1; $p <= $nr_blocks ; $p++)  
                     {  if ($n == $p) 
                             {  $string .= $arrow_black;} 
                        else {  $string .= '<td>&nbsp;</td>'; } 
                }
                $string .= '</tr>'.PHP_EOL;               
                echo $string;
                } // eo all pms
        echo $str_hr;
      } // eoforeach value

#     
$string = '<tr>'.str_repeat('<td>&nbsp;</td>',$nr_data_coloms);
$width_color = floor(50/$nr_blocks);
for ($n = 1; $n <= $nr_blocks; $n++) 
     {  $string .= '<td style = "width: '.$width_color.'%; text-align: center; border: 1px solid grey; font-weight:bold;"'
                        .' class= "'.$aq_class[$n].'"></td>'.PHP_EOL;}
$string .= '</tr>'.PHP_EOL;
echo $string;

if (time() > ($aq_ftime  + 1800) ) { $time_color = 'red'; } else { $time_color = 'black'; }

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
if ($tempunit == 'C') 
     {  $aq_ftemp = convert_temp ($aq_ftemp,'F',$tempunit,1);
        $aq_fdewp = convert_temp ($aq_fdewp,'F',$tempunit,1);}
$explain .= '<tr style="background-color: #ccc;"><td colspan="'.$nr_cols.'" style="border-top: 1px solid grey;">&nbsp;'
        .lang($ltxt_info).':&nbsp;'
        .'<a href="'.$explain_link.'" target="_blank">&nbsp;'.strtoupper($aqhi_type).'&nbsp;&nbsp;'.lang($ltxt_aq_info).'</a>&nbsp;&nbsp;&nbsp;&nbsp;'
        .'<a href="'.$davis_link.'"   target="_blank">&nbsp;'.lang($ltxt_davis).'</a>&nbsp; <small>'
        .lang($ltxt_sensor).':&nbsp;'.date($dateFormat.' '.$timeFormatShort,$aq_ftime).' &nbsp;'
        .lang($ltxt_temp).':&nbsp;'.$aq_ftemp.'&deg;'.$tempunit.'&nbsp;'
        .lang($ltxt_hum) .':&nbsp;'.$aq_hum  .'%&nbsp;'
        .lang($ltxt_dewp).':&nbsp;'.$aq_fdewp.'&deg;'.$tempunit
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
