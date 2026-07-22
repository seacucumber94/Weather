<?php $scrpt_vrsn_dt  = 'AQ_purpleair_c_block.php|01|2022-11-22|';  # new file format + release 2012_lts
# 
#------------------------- EXTRA sensor settings
$Voc            = false;
$iaq_Bosch      = 'iaqB';
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
# -------------load settings / shared data and common scripts
$scrpt          = 'PWS_settings.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#-----------------------------------------------
#      load the data 
#-----------------------------------------------
#
$fl_t_ld        = $fl_folder.$prpl_fl;  // ./jsondata/purpleair.txt
$json_string    = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string            = file_get_contents($fl_t_ld);
        $parsed_json            = json_decode($json_string,true);}#echo '<pre>'.print_r ($parsed_json,true); exit;
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
#  PM measurements                              echo '<pre>'.print_r($parsed_json['sensor'],true); exit;
$pm25 = $pm25_24 = $pm10 = 0;
#  pm25
if ( array_key_exists ('pm2.5', $parsed_json['sensor']['stats'] ) )
     {  $pm25   = $parsed_json['sensor']['stats']['pm2.5'];}
#  pm25_24
if ( array_key_exists ('pm2.5_24hour', $parsed_json['sensor']['stats'] ) )
     {  $pm25_24= $parsed_json['sensor']['stats']['pm2.5_24hour'];}
#  pm10
if ( array_key_exists('pm10.0_atm_a',$parsed_json['sensor'] ) )
     {  $pm10   = $parsed_json['sensor']['pm10.0_atm_a'];}
elseif  (array_key_exists('pm10.0_atm_b',$parsed_json['sensor'] ) )
     {  $pm10   = $parsed_json['sensor']['pm10.0_atm_b'];}
#
#  Voc measurements  to be added as no sensor available to test
if ($Voc <> false && array_key_exists('Voc',$parsed_json['sensor']) ) 
     {  $Voc = $parsed_json['sensor']['Voc'];  }   // ?????????
#
#  other data
$forecastime    = $parsed_json['data_time_stamp'];
if ( array_key_exists('LastSeen', $parsed_json['sensor']) )
     {  $forecastime    = (int) $parsed_json['sensor']['LastSeen'];}
$time           = set_my_time($forecastime,true);
#
if ( array_key_exists('latitude', $parsed_json['sensor']) )
     {  $lat_lon= '('.round($parsed_json['sensor']['latitude'],2).','
                     .round($parsed_json['sensor']['longitude'],2).')';}
else {  $lat_lon= '';}
#
$sensorID       = $parsed_json['sensor']['sensor_index'];
$city           = $parsed_json['sensor']['name'];
#
# calculate AQI
$aqi            = round ((float) pm25_to_aqi($pm25),1 ); 
$AQ10           = round ((float) pm10_to_aqi($pm10) ,1);
$aqi24          = round ((float) pm25_to_aqi($pm25_24),1 ); #echo __LINE__.' '.$aqi24;
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $pm25='.$pm25.' $aqi='.$aqi.PHP_EOL; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $pm10='.$pm10.' $AQ10='.$AQ10.PHP_EOL;    
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $pm25_24='.$pm25_24.' $aqi24='.$aqi24.PHP_EOL;    #echo '<PRE>'.$stck_lst; exit;
#
$now            = time();
$diff           = time() - $forecastime; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $forecastime='.$forecastime.' $now='.$now.' $diff='.$diff.' '.PHP_EOL; 
#
if (filesize($fl_t_ld) < 1 || $diff > 3600)
	{ $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else    { $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($forecastime,true).' </b>';}
#
# ------------------- define colors for this AQI
foreach ($AQ_levels as $n => $value) 
     {  if ($aqi > $value) { continue;}
        $icon   = $aq_icon[$n];
        $class  = 'dottedcircle'.$aq_class[$n];
        $color  = $b_color = $aq_color[$n];
        $text   = $aq_text[$n];
        break;}
# -------------------------------- assemble html
$left_txt = 
'<div class="PWS_left" style="height: 110px; "><!-- some facts -->
    <div class="PWS_div_left" style="height: 110px; margin: 0px 5px; font-size: 10px; ">
        <span  class="normal" ><b>'
        .$tr_station.':</b><br /><br />#'
        .$sensorID.'<br /><br />'
        .$city.'<br />'.$lat_lon;
$left_txt .= '    </span>
</div>
</div>'.PHP_EOL;
#
$middle_text = 
'<div class="PWS_middle" style="height: 100px;"><!-- large icon -->
    AQI:&nbsp;<b  class=""  style="font-size: 20px; line-height: 1.0;">'.$aqi.'</b>
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
if ($Voc <> false) {$extra = '';} else {$extra = '<br />';}
$right_text     = 
'<div class="PWS_right" style="height: 110px;">
    <div class="PWS_div_right" style="height: 110px;  margin: 0px 5px; font-size: 10px; ">
        '.$extra.'
        <span  class="normal" ><b>PM2.5</b>:
        <br />
        <b>'.$pm25.'</b><small> ug/m3</small><br /><br /></span>
        <span  class="normal" ><b>PM10 </b>:
        <br />
        <b>'.$pm10.'</b><small> ug/m3</small><br /></span>
        <b>'.$AQ10.'</b><small> AQI</small>
        <br /><br />';
if ($Voc <> false) 
     {  $right_text    .= '<span  class="normal" ><b>Voc</b>:
        <br />
        <b>'.$Voc.'</b><small> '.lang($iaq_Bosch).'</small></span>';}
 
$right_text     .=
'    </div>
</div>'.PHP_EOL;
#
$bottom_text = '<div style="clear: both; width: 100%; font-size: 16px; padding-top: 5px;">'.lang($text).'</div>
<!-- eo weatheritem 2 -->'.PHP_EOL;
if (isset ($PWS_popup))   {  return; }  // are we in a popup 
#
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
echo '<br />'.$left_txt.$middle_text.$right_text.$bottom_text;
#
#  print needed debug messages
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}