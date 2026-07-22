<?php  $scrpt_vrsn_dt  = 'PWS_notifications.php|01|2021-04-21|';  # php8 |release 2012_lts | added extra check WU json correct
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
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
# -----------------for testing
# $weather['uv'] = 'n/a';
#  $weather['heat_index'] = 40;
#  $weather['wind_speed'] = 50;
#  $weather['lightningtimeago'] = 500; $weather['lightningkm']= 10;
# -----------------for testing
#
#
$fade_time      = 10; # nr of seconds message is shown
$ntfc_l         = ' '.lang('Notification');
$ntfc_uv_l      = lang('UV-Index Caution').'<br >'.lang('Reduce Sun Exposure');
$ntfc_heat_l    = lang('Heat Index Caution').'<br >'.lang('Heat Exhaustion');
$ntfc_gale_l    = lang('Wind Gusts Reaching Gale Force');
$ntfc_strng_l   = lang('Wind Gusts Becoming Strong Caution Required');

$dttm   = lang(date('l')).' '.set_my_time(time(),true); 

$arr    = array();
#
# -----------------------------------------   UV
if (isset ($weather['uv']) && (float) $weather['uv'] >= $notifyUV )   #### 2021-04-20
     {  $arr[]  = $ntfc_uv_l.' <b class="yellow">'.$weather['uv'].'</b>';}
#
#---------------------------- WeatherUnderground
if ($fct_used_set ==  '$fct_wu_block.php')    #### 2020-12-08
     {  $file           = $fl_folder.'wufct_'.$locale_wu.'_'.$wu_fct_unit.'.txt';  
        $json           = file_get_contents($file); 
        $response       = json_decode($json, true); # echo '<pre>response["daypart"]= '.print_r($response['daypart'], true) ; exit;     
        if ($json <> false && $response <> false ) 
             {  if ($response['daypart'][0]['dayOrNight'][0] == '') {$n = 1;} else {$n=0;}
                $uv             = (int) $response['daypart'][0]['uvIndex'][$n];
                $uv_text        = (string) $response['daypart'][0]['uvDescription'][$n];
                if ($uv > $notifyUV) 
                     {  if ($uv > 10) {$lass = 'red'; } else {$class = 'yellow';}
                        $arr[]  =   $ntfc_uv_l.' <b class="'.$lass.'">'.$uv.'</b>';}
                }
          } // eo WU
#
# ----------------------------------------- HEAT
$tmp    = $weather['heat_index'];
if ($tempunit <> 'C') {$tmp =  round (5*($tmp -32)/9);} 
if($tmp >= $notifyHeatIndex)
    {   $arr[]  = $ntfc_heat_l.' <b class="yellow">'.$weather['heat_index'].'&deg;'.$tempunit.'</b>';}
#
# ----------------------------------------- WIND
$spd_kts        = $weather['wind_speed'] * $toKnots;
if ($spd_kts >= $notifyWindGust )
    {   if  ($spd_kts > 34)    { $txt1  =  $ntfc_gale_l ;}
        else                   { $txt1  =  $ntfc_strng_l;}
        $arr[]  = $txt1 .' <b class="yellow">'.$weather['wind_speed'].'&nbsp;'.$weather['wind_units'].'</b>';}
#
# -----------------------------------  LIGHTNING
#
$lght  =  $dist = 0;
$dist_lightning = 50;   // maybe  extra settings 
$age_lightning  = 600;

if (isset ($boltek) && $boltek == true)
     {  $boltek_values  = true;
        $scrpt          = 'lightning_boltek_small.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt;}
#
if (isset ($weather['lightningtimeago'])) {$lght = (int)$weather['lightningtimeago'];} else {$lght = -1;}
if (isset ($weather['lightningkm']))      {$dist = (int)$weather['lightningkm'];     } else {$dist = 9999999;}   
if (    $lght > 0 && $lght < $age_lightning && $dist < $dist_lightning)
    {   $txt1   = lang('Lightning Strike Alert Detected');
        $txt2   = lang('Distance');
        $txt3   = lang('km');
        $txt4   = trim(lang('mile'));
        $txt5   = lang('minutes');
        $txt6   = lang('seconds ago');
        $txt7   = round($dist,0).' '.$txt3.' ('.round($dist*0.621371,1).' '.$txt4.') ';
        $txt8   = '';
        if ($dist <> 0)  {  $txt8 = '<br />'.$txt2.' '.$txt7;}  
        $txt9   = (int) gmdate('i',$lght);
        if ($txt9 < 10) {$txt9  = substr ((string) $txt9,-1);}
        $txt10  = gmdate('s',$lght);
        if ($txt10< 10) {$txt10 = substr ((string) $txt10,-1);}        
        $arr[]  = $txt1.$txt8.'<br >'.$txt9.' '.$txt5.' '.$txt10.' '.$txt6;}

if (count($arr) > 0)
     {  $notification   = '<svg viewBox="0 0 32 32" width=10 height=10 fill=#ff8841><circle cx=16 cy=16 r=14 /></svg>';
        echo '<div class="PWS_notify" 
        style="animation-fill-mode: both;  animation-name: fadeOut;  animation-duration: '.$fade_time.'s; ">';
        foreach ($arr as $txt)
             { echo '
    <div class="PWS_notify_box">
        <div class="PWS_notify_header">
                <div class="PWS_notify_left">'.$notification.' '.$ntfc_l.'</div>
                <div class="PWS_notify_right">'.$dttm.'</div>
        </div>
        <div class="content">     
            '.$txt.'
        </div>
    </div>';}
        echo '
</div>'.PHP_EOL;}
