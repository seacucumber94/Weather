<?php $scrpt_vrsn_dt  = 'AQ_gov_c_block.php|01|2020-11-02|';  # release 2012_lts
# 
# -----------------     SETTINGS for this script
$allowed_age    = 3600 * 4; // most official stations 
#$allowed_age    = 3600 * 12; // some stations in italy
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
#   --------------------------   ONLY EPA VALUES 
if (!isset ($aqhi_type) ) {$aqhi_type = 'epa'; } 
$save_aqhi_type = $aqhi_type;
#$aqhi_type = 'eea';     
#
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#
$pm25 = $aqi = false;  // empty needed fields
#                      
#-----------------------------------------------
#                                  load the data 
#-----------------------------------------------
$fl_t_ld                = $fl_folder.$gvaqi_fl;  # "jsondata/gov_aqi.txt"
$json_string = $parsed_json = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);
        $parsed_json    = json_decode($json_string,true);} #echo '<pre>'.print_r ($parsed_json,true); 
#
#----------------------  check if data is usable
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = __LINE__.': Invalid / no JSON data<br />'.$gvaqi_fl; }
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
include_once $scrpt;  
aq_array_load ();
#
if (!function_exists ('gov_cnvrt') ) {
        function gov_cnvrt ($pol,$value) {
                global $aqhi_type;
                if ($aqhi_type <> 'epa' && $value <> 0) 
                     {  $return = round(aq_epa_eea($pol,$value),1); #echo __LINE__.' $pol='.$pol.' $value='.$value.' $return='.$return.PHP_EOL; 
                        return $return;}
                return $value;
        } // eof gov_cnvrt
} // eo check function
#    
$arr    =  $parsed_json['data'];   # echo '<pre>'.__LINE__.print_r($arr,true);  exit; 
$aqi    =  (float) $arr['aqi'];     
$dom_pol=  $arr['dominentpol'];
#
if (!array_key_exists('iaqi',$arr) ) 
     {  $iaqi   = array ();}
else {  $iaqi   = $arr['iaqi'];}
if ($aqhi_type == 'eea')
     {  $aqi    = 0;
        $dom_pol= '';
        $pollutants     = array('o3','pm10','pm25');  // those we want to show
        foreach ($iaqi as $key => $value)
             {  if (!in_array ($key, $pollutants) )
                     {  unset ($iaqi[$key]);
                        continue;}
                $value  = $iaqi[$key]['v'];
                $iaqi[$key]['v'] =  gov_cnvrt ($key,$value);
                }
        }
else {  $pollutants     = array($dom_pol,'o3','pm10','pm25','no2', 'so2'); } // those we want to show
#
#echo '<pre>'.__LINE__.print_r($iaqi,true);
#
foreach ($pollutants as $pollutant)
    {   if (!array_key_exists($pollutant,$iaqi) )     
             {  $iaqi[$pollutant]['v']  = false;}
        else {  $value  = (float) $iaqi[$pollutant]['v'];
                if ($value <= $aqi) {  continue;}   
                $aqi    = $value;  # echo __LINE__.' $aqi='.$aqi.' $pollutant='.$pollutant.PHP_EOL; 
                $dom_pol= $pollutant;}
} // eo foreach pollutant
if ($iaqi [$dom_pol]['v'] == false ) {unset ($iaqi [$dom_pol]);} 
#echo '<pre>'.__LINE__.' '.print_r($iaqi,true); exit;
#-----------------------------------------------
#                         gather data to be used 
#-----------------------------------------------
$aqiweather             = array();
$aqiweather['aqi']      = $aqi;  # echo '<pre>'.__LINE__.$aqi; exit;
$timestamp              = $arr['time']['v'];
$aqiweather['time']     = set_my_time_lng(gmdate($timeFormatShort,$timestamp));
$aqiweather['city']     = $city = $arr['city']['name'];
/*if (strlen($aqiweather['city']) > 26) 
     {  $city   = '<small>'.$aqiweather['city'].'</small>';}
else {  $city   = $aqiweather['city'];} #echo '<pre>'.__LINE__.print_r($aqiweather,true); exit;
*/
#
if (isset ($pm10)) {unset ($pm10);}
if (isset ($pm25)) {unset ($pm25);}
#
if (filesize($fl_t_ld) < 1 
   || (time() - $timestamp + date('Z')) > $allowed_age)
	{ $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else    { $online_txt   = '<b class="PWS_online"> ' .$online.$aqiweather['time'].' </b>' ;}
#
# ------------------- define colors for this AQI
if (!is_array($AQ_levels)) {aq_array_load ();}
foreach ($AQ_levels as $n => $value) 
     {  if ($aqi > $value) { continue;}
        $icon   = $aq_icon[$n]; 
        $class  = 'dottedcircle'.$aq_class[$n];
        $b_color= $aq_color[$n];
        $text   = $aq_text[$n];
        break;}
# -------------------------------- assemble html
$left_txt = 
'<div class="PWS_left" style="height: 110px; "><!-- some facts -->
    <div class="PWS_div_left" style="height: 110px; margin: 0px 5px; font-size: 10px; overflow: hidden;">
        <span  class="normal" ><b>'
        .$tr_station.'</b>:<br /><br />'
        .str_replace(',','<br />',$city).'</span>'.PHP_EOL;
if (isset ($PWS_popup))   
     {  $left_txt .= '<br /><br /><b>'.$tr_updt.'</b><br />'.$aqiweather['time'];}
$left_txt .=
'    </div>
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
        <span  class="normal" ><b> AQI</b>:
        <br /><br /></span>
        <table style="width: 100%; font-size: 10px;">'.PHP_EOL;
#
$n      = 1;
foreach ($pollutants as $pollutant)
     {  if ($iaqi [$pollutant]['v'] == false ) {continue;}
        $right_text    .= '            <tr><td>'.$iaqi[$pollutant]['v'].'</td><td>'.$pollutant.'</td></tr>'.PHP_EOL;
        $iaqi [$pollutant]['v'] = false;
        if ($n > 4) break;
        $n++;}
$right_text    .= '        </table>
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

 $aqhi_type = $save_aqhi_type;