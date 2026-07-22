<?php $scrpt_vrsn_dt  = 'AQ_luftdaten_c_block.php|01|2020-11-25|';  # release 2012_lts
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
$fl_t_ld        = $fl_folder.$lfdtn_fl;
$json_string    = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);
        $parsed_json    = json_decode($json_string,true); } #echo '<pre>'.print_r ($parsed_json,true);
#-----------------------------------------------
#     check if data is usable
#-----------------------------------------------
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = __LINE__.': Invalid / no JSON data<br />'.$lfdtn_fl; }
elseif (!array_key_exists (0, $parsed_json) )
     {  $dataFALSE = __LINE__.': No data found'; } 
elseif (!array_key_exists ('sensordatavalues', $parsed_json[0]) )
     {  $dataFALSE = __LINE__.': No sensordata found'; } 
elseif (!array_key_exists ('value_type', $parsed_json[0]['sensordatavalues'][0]) )
     {  $dataFALSE = __LINE__.': No measurements found'; } 
if ($dataFALSE<> '')
     {  echo 'Problem '.$dataFALSE.'<br />Check settings and data'; return;}
#
#-----------------------------------------------
#                        get the data to display 
#-----------------------------------------------
# 
$values                 = $parsed_json[0]['sensordatavalues'];
$pm25   = $pm10         = -1;                          # echo '<pre>'.print_r ($values,true);
foreach ($parsed_json as  $measurements)
    {   $values        = $measurements['sensordatavalues'];
        foreach ($values as  $arr) 
             {  # echo '<pre>'.print_r ($arr,true);
                if ($arr['value_type']  == 'P2') 
                     {  $value  = (float) $arr['value'];
                        if ($value > (float) $pm25) { $pm25 =  $value;}
                        } 
                if ($arr['value_type']  == 'P1') 
                     {  $value  = (float) $arr['value'];
                        if ($value > (float) $pm10) { $pm10 =  $value;}
                        } 
                }
        $time           = $measurements['timestamp'];
        $country        = $measurements['location']['country'];
        $lat_lon        = ' ('.round($measurements['location']['latitude'],2).','.round($measurements['location']['longitude'],2).')';
        $sensor         = $measurements['sensor']['id']; 
        } # echo '<pre>$measurements='.print_r($measurements,true); exit;
#
if ($pm25 == -1) {echo 'Problem '.__LINE__.': No valid data fround, script ends'; return;}
#
#
#-----------------------------------------------
#      general functions / tables for AQ scripts
#-----------------------------------------------
$scrpt          = 'AQ_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#
#-----------------------------------------------
#                         gather data to be used 
#-----------------------------------------------
#
# $pm10 = 10*$pm10;
$forecastime    = strtotime($time . 'UTC');
$aqiweather     = array();
$aqiweather['time']     = set_my_time($forecastime,true);
$aqiweather['unix']     = $forecastime;
$aqiweather['city']     = $sensor;  // no city description for luftdaten, only a code, use sensor for this.
$aqiweather['pm25']     = $pm25;
$aqiweather['pm10']     = $pm10;
#
$aqiweather['aqi25']    = $aqi  = number_format(pm25_to_aqi($aqiweather['pm25']),1 );
$aqiweather['aqi10']    = $aqi10= number_format(pm10_to_aqi($aqiweather['pm10']),1 );
if ($aqi10 > $aqi) { $aqi =  $aqi10;}
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $pm25='.$pm25.' $aqi='.$aqi.PHP_EOL;    
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
        .$sensor.'<br /><br />'
        .$country.'<br />'.$lat_lon;
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
$right_text     = 
'<div class="PWS_right" style="height: 110px;">
    <div class="PWS_div_right" style="height: 110px;  margin: 0px 5px; font-size: 10px; ">
        <span  class="normal" >'.PHP_EOL;
#        
if (isset ($aqiweather['pm25']) ) 
     {  $right_text    .= '<br />'
        .'<b>PM2.5<br />'.$aqiweather['pm25'].'</b><small> ug/m3</small><br />'
        .'<b>'.$aqiweather['aqi25'].'</b> <small>AQI</small><br />'
        .PHP_EOL;}
#  
if (isset ($aqiweather['pm10']) ) 
     {  $right_text    .= '<br />'
        .'<b>PM10<br />' .$aqiweather['pm10'].'</b><small> ug/m3</small><br />'
        .'<b>'.$aqiweather['aqi10'].'</b> <small>AQI</small><br />'
        .PHP_EOL;}
$right_text    .= '        </span>
    </div>
</div>'.PHP_EOL;

$bottom_text = '<div style="clear: both; width: 100%; font-size: 16px; padding-top: 5px;">'.lang($text).'</div>
<!-- eo weatheritem 2 -->'.PHP_EOL;

if (isset ($PWS_popup))   {  return; }  // are we in a popup 
#
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
echo '<br />'.$left_txt.$middle_text.$right_text.$bottom_text;
#
#  print needed debug messages
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}