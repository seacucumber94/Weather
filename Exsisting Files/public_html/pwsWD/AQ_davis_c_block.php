<?php $scrpt_vrsn_dt  = 'AQ_davis_c_block.php|01|2021-01-02|';  # widen blocks | release 2012_lts
# -----------------     SETTINGS for this script
$allowed_age    = 1200 * 4; 
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
if ($dwl_AQ == 0) {$dwl_AQ = '';}  #### 2020-10-03 replace empty station ID when using DWL for data also.
$file_mame              = 'wlcomv2API'.$dwl_AQ.'.json'; # echo $fl_t_ld; # " jsondata/wlcomv2API97452.json  "
$fl_t_ld                = $fl_folder.$file_mame;
$json_string = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);
        $parsed_json    = json_decode($json_string,true); } #echo '<pre>'.print_r ($parsed_json,true); 
#
#----------------------  check if data is usable
$dataTEXT       = '';
$dataOK         = false;
if ( $parsed_json == FALSE) 
     {  $dataTEXT = __LINE__.': Invalid / no JSON data<br />'.$file_mame; }
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
#ksort($arr)   ;  echo '<pre>'.print_r ($arr,true);
#
/*    aqi_1_hour_desc] => Good
    [aqi_1_hour_val] => 0.49363312
    [aqi_desc] => Good
    [aqi_nowcast_desc] => Good
    [aqi_nowcast_val] => 14.9973345
    [aqi_type] => United States EPA AQI
    [aqi_val] => 0
    [dew_point] => 55.3
    [heat_index] => 76.7
    [hum] => 47
    [last_report_time] => 1601563500
    [pct_pm_data_1_hour] => 100
    [pct_pm_data_24_hour] => 100
    [pct_pm_data_3_hour] => 100
    [pct_pm_data_nowcast] => 100
    [pm_1] => 0
    [pm_10] => 0
    [pm_10_1_hour] => 0.6462766
    [pm_10_24_hour] => 5.833966
    [pm_10_3_hour] => 9.18472
    [pm_10_nowcast] => 5.0264535
    [pm_2p5] => 0
    [pm_2p5_1_hour] => 0.11847195
    [pm_2p5_24_hour] => 4.6208005
    [pm_2p5_3_hour] => 6.837155
    [pm_2p5_nowcast] => 3.5993605
    [temp] => 77.1
    [ts] => 1601563500
    [wet_bulb] => 60.8 */
#-----------------------------------------------
#      general functions / tables for AQ scripts
#-----------------------------------------------
$scrpt          = 'AQ_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;  
# 
$timestamp      = $arr['ts'] ; 
$aqi            =  number_format(round((float) $arr['aqi_val'],1),1);  
#
$plltnts= array ('pm_2p5', 'pm_10'); #,'pm_1');  // those we want to show
$periods= array ('','_1_hour','_3_hour','_24_hour'); # ,'_nowcast');
$values = array ();
foreach ($plltnts as $plltnt)
     {  foreach ($periods as $period)
             {  $key    = $plltnt.$period;
                $values [$key]['aq']= -1;
                if (array_key_exists($key,$arr) )
                     {  #$arr[$key] = 100*$arr[$key]; ## test              
                        $values [$key]['pm']= round($arr[$key],1);
                        if ($plltnt == 'pm_10')  {$values [$key]['aq'] = number_format(round(pm10_to_aqi($arr[$key]),1),1);}
                        if ($plltnt == 'pm_2p5') {$values [$key]['aq'] = number_format(round(pm25_to_aqi($arr[$key]),1),1);}
                        }
                else {  $values [$key]['pm']= -1;
                        $values [$key]['aq']= -1;
                        }
                }
        } # echo '<pre>'.print_r ($values,true); 
#if ($aqhi_type <> 'epa') {
        $aqi = $values['pm_2p5']['aq'];
        if ( $values['pm_10']['aq'] > $aqi)
             {  $aqi = $values['pm_10']['aq'];}
#}
if (isset ($_REQUEST['test']))
     {  echo '<!-- '.basename(__FILE__).' ('.__LINE__.') '.print_r($values, true).' -->'.PHP_EOL;}
#-----------------------------------------------
#                         gather data to be used 
#-----------------------------------------------
$aqiweather             = array();
$aqiweather['aqi']      = $aqi;  # echo '<pre>'.__LINE__.$aqi; exit;
$aqiweather['time']     = set_my_time_lng(date($timeFormatShort,$timestamp));
$aqiweather['city']     = $city = lang ('My city');;
/*if (strlen($aqiweather['city']) > 26) 
     {  $city   = '<small>'.$aqiweather['city'].'</small>';}
else {  $city   = $aqiweather['city'];} #echo '<pre>'.__LINE__.print_r($aqiweather,true); exit;
*/
#
if (isset ($pm10)) {unset ($pm10);}
if (isset ($pm25)) {unset ($pm25);}
#
if (filesize($fl_t_ld) < 1 
   || (time() - $timestamp) > $allowed_age)
	{ $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').'<!-- '.date('c', $timestamp).' --></b>'; }
else    { $online_txt   = '<b class="PWS_online"> ' .$online.$aqiweather['time'].'<!-- '.date('c', $timestamp).' --> </b>' ;}
#
# ------------------- define colors for this AQI
foreach ($AQ_levels as $n => $value) 
     {  if ($aqi > $value) { continue;}
        $icon   = $aq_icon[$n]; 
        $class  = 'dottedcircle'.$aq_class[$n];
        $b_color= $aq_color[$n];
        $text   = $aq_text[$n];
        break;}
# -------------------------------- assemble html  #### 2021-01-02  widen left wide block margin: 0px 5px;  => margin: 0px 2px;
$left_txt = 
'<div class="PWS_left" style="height: 110px; "><!-- some facts -->
    <div class="PWS_div_left" style="height: 110px; margin: 0px 2px; font-size: 10px; overflow: hidden;">
        <table style="width: 100%; font-size: 10px;">
        <tr><td colspan="3"><b>'.lang('PM10').'</b></td></tr>
        <tr><th><small>'.lang('hrs').'</small></th><th><small>AQI</small></th><th><small>ug/m<sup>3</sup></small></th></tr>'.PHP_EOL;;
$left_txt.= '        <tr><td> </td><td>'.$values['pm_10']['aq'].         '</td><td>'.round($values['pm_10']['pm'],3).        '</td></tr>'.PHP_EOL;     
$left_txt.= '        <tr><td>1</td><td>'.$values['pm_10_1_hour']['aq'].  '</td><td>'.round($values['pm_10_1_hour']['pm'],3). '</td></tr>'.PHP_EOL;     
$left_txt.= '        <tr><td>3</td><td>'.$values['pm_10_3_hour']['aq'].  '</td><td>'.round($values['pm_10_3_hour']['pm'],3). '</td></tr>'.PHP_EOL; 
$left_txt.= '        <tr><td>24</td><td>'.$values['pm_10_24_hour']['aq'].'</td><td>'.round($values['pm_10_24_hour']['pm'],3).'</td></tr>
        </table>
    </div>
</div>'.PHP_EOL;
#
$middle_text = 
'<div class="PWS_middle" style="height: 100px;"><!-- large icon -->
   AQI:&nbsp;<b class=""  style="font-size: 20px; line-height: 1.0; ">'.$aqi.'</b>
   <a href="'.$explain_link.'" target="_blank">'.$aqhi_type.' '.$to_outside.'</a><br />
    <div class="PWS_round" 
        style = "margin: 0 auto; margin-top: 5px;
                 width: 72px; 
                 height: 72px; 
                 background-color: '.$b_color.'; 
                 border: 0px solid silver;">
        <img src="./img/'.$icon.'" width="72" height="72" alt="Air quality: '.$text .' " title="Air quality: ' .$text.' " />
    </div>
</div>'.PHP_EOL;
#
$from   = 
$right_text     = 
'<div class="PWS_right" style="height: 110px;">
    <div class="PWS_div_right" style="height: 110px;  margin: 0px 2px; font-size: 10px; ">
        <table style="width: 100%; font-size: 10px;">
        <tr><td colspan="3"><b>'.lang('PM2.5').'</b></td></tr>
        <tr><th><small>'.lang('hrs').'</small></th><th><small>AQI</small></th><th><small>ug/m<sup>3</sup></small></th></tr>'.PHP_EOL;;
$right_text.= '        <tr><td> </td><td>'.$values['pm_2p5']['aq'].'</td><td>'.round($values['pm_2p5']['pm'],3).'</td></tr>'.PHP_EOL;     
$right_text.= '        <tr><td>1</td><td>'.$values['pm_2p5_1_hour']['aq'].'</td><td>'.round($values['pm_2p5_1_hour']['pm'],3).'</td></tr>'.PHP_EOL;     
$right_text.= '        <tr><td>3</td><td>'.$values['pm_2p5_3_hour']['aq'].'</td><td>'.round($values['pm_2p5_3_hour']['pm'],3).'</td></tr>'.PHP_EOL; 
$right_text.= '        <tr><td>24</td><td>'.$values['pm_2p5_24_hour']['aq'].'</td><td>'.round($values['pm_2p5_24_hour']['pm'],3).'</td></tr>
        </table>
    </div>
</div>'.PHP_EOL;

$bottom_text = '<div style="clear: both; width: 100%; font-size: 16px; padding-top: 5px;">'.lang($text).'</div>
<!-- eo weatheritem 2 -->'.PHP_EOL;
#
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
echo '<br />'.$left_txt.$middle_text.$right_text.$bottom_text;
#
#  print needed debug messages
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}