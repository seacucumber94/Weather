<?php  $scrpt_vrsn_dt  = 'ccn_shared.php|01|2021-05-15|';  # html-colors + removed n/a |release 2012_lts
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# --------------------   translate texts
$ltr_hourly     = lang('One hour forecast');
$ltr_rain       = lang('Rain');
$ltr_lightning  = lang('Lightning');
$ltr_caution    = lang('Caution');
$ltr_nearby_l   = lang('Nearby Lightning');
$ltr_conditions = lang('Conditions');
$ltr_heavyrain  = lang('heavy').' '.lang('rain');
$ltr_flooding   = lang('Flooding Possible');
$ltr_modrtrain  = lang('moderate').' '.lang('rain');
$ltr_stdytrain  = lang('steady').' '.lang('rain');
$ltr_lightrain  = lang('light').' '.lang('rain');
$ltr_offline    = lang('Offline');
$ltr_temperature= lang('Temperature');
$ltr_feels      = lang('Feels');
$ltr_windspd    = lang('Windspeed');
$ltr_gust       = lang('Gust');
$ltr_forecast   = lang('forecast');
$ltr_uvi        = lang('UVI');
$ltr_snow       = lang('Snow');
# -----------------------  colors => CSS
$clr_red        = '<b style="color: #d65b4a;">';
$clr_orange     = '<b style="color: #FF7C39;">';
$clr_blue       = '<b style="color: #01A4B4;">';
$clr_yellow     = '<b style="color: #c1b01e;">';
$clr_green      = '<b style="color: #9aba2f;">';
#
if ( $itsday == true) { $nt_icn = '';} else { $nt_icn = 'nt_';} 
#
$icon   = '';   // 
$text   = '';   // used for the icon
$text2  = '';   // used as text next to the icon
#
$alert="<svg id='firealert' viewBox='0 0 32 32' width='11px' height='11px' fill='none' stroke='currentcolor' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'>
<path d='M16 3 L30 29 2 29 Z M16 11 L16 19 M16 23 L16 25' /></svg>";
# ------  set optional weather-fields to default 
if (!isset ($weather['lightningtimeago']) )    { $weather['lightningtimeago']   = -1;}
if (!isset ($weather['rain_rate']) )           { $weather['rain_rate']          = -1;}
#
# -------  set fields to compare to correct unit
$dif_tmp_dew    = anyToC($weather['temp']) - anyToC($weather['dewpoint']);
$tmp_cmp        = anyToC($weather["temp"]);
$rain_rate      = convert_precip($weather['rain_rate'], $rainunit,'mm');
#
# -------------------------------------lightning
if ($weather['lightningtimeago'] > 0  && $weather['lightningtimeago'] < 600)
     {  if ($rain_rate > 0)         
             { $icon    = 'ovc_thun_rain_dark';     $text = 'rain lightning';
               $text2   = $clr_blue.$ltr_rain.'</b> '.$clr_orange.$ltr_lightning.' '.$alert.'</b><br />'.$ltr_caution;}

        else { $icon    = 'ovc_thun_dark';  $text = 'lightning';
                $text2  = $clr_orange.$ltr_nearby_l.'  '.$alert.'</b>';} 
} // eo ligtning
#
# ----------------------------------------- rain 
elseif ($rain_rate > 10 )
     {  $icon = 'ovc_rain';       $text = 'heavy rain';}
elseif ($rain_rate > 0  )          
     {  $icon = 'mc_rain';       $text = 'rain';}
#
# ---------------------------------          fog 
elseif( $dif_tmp_dew <0.5  && $tmp_cmp  > 5)
     {  $text = 'fog';  $icon = 'mc_fog';   } 
#
#  ----------------------------   windy moderate
elseif ($weather["wind_speed_avg"]* $toKnots> 15 )
     {  $text = 'windy'; $icon = 'ovc_windy';}
#
if ($icon <> '')
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') icon from own data $icon='.$icon.PHP_EOL;
        $icon           = './pws_icons/'.$icon.'.svg'; }
#
# ------------------------------------------rain
elseif ($rain_rate >= 7 ) {$text2  = $ltr_heavyrain      .'<br />'.$ltr_flooding;}
elseif ($rain_rate >= 5 ) {$text2  = $ltr_heavyrain;}
elseif ($rain_rate >= 3 ) {$text2  = $ltr_modrtrain;}
elseif ($rain_rate >= 1 ) {$text2  = $ltr_stdytrain;}
elseif ($rain_rate >  0 ) {$text2  = $ltr_lightrain ;}
#
# if 1 hour forecast n/a
#
if (!isset ($onehrfct_missing )  || $onehrfct_missing <> true) {$onehrfct_missing = false;}
echo '<div class="PWS_ol_time">'.PHP_EOL;
if (time() - $timeXX > 3600)
     {  echo '<b class="PWS_offline"> '.$online.$ltr_offline.' </b>';}
else {  echo '<b class="PWS_online"> ' .$online.set_my_time_lng($timeXX,true).' </b>' ;}   # echo $online. PHP_EOL.date($timeFormat,$timeXX);
echo '</div>
<table style="font-size: 11px; width: 98%; padding-top: 8px; margin: 0 auto; text-align: center; height: 154px;">
<tr>
<td>'; 
if ($icon <> '')
     {  echo '<img style="vertical-align: bottom; width : 60px;" rel="prefetch" src="'.$icon  .'" alt="'.$text.'">'.PHP_EOL;}
else {  echo '<img style="vertical-align: bottom; width : 60px;" rel="prefetch" src="'.$iconXX.'" alt="'.$textXX.'">'.PHP_EOL;} 

echo '</td>
<td style="width: 50%; text-align: left;">'.$textXX.'<br />'.$text2.'</td>
</tr>
<tr>
<td colspan="2" style="border-top: 1px grey solid; ">';
if ($onehrfct_missing == false) { echo '<span style="padding: 4px;">'.$ltr_hourly.':</span><br />'.PHP_EOL;}
echo ' '.$ltr_temperature.' ';
#celsius
if ($tempC  >= 20)          { echo $clr_orange; } 
elseif ($tempC  <= 10)      { echo $clr_blue;   }
else                        { echo $clr_green;  }
echo $hourlyTemp.'</b>&deg;';  

if ($chillC <> '' && $chillC <> 'n/a'  && (float) $chillC <> 0 && ( (float) $tempC - (float) $chillC ) > 4) #### 2021-01-23
      { echo ' '.$ltr_feels.': '; # echo '$tempC='.$tempC.' $chillC='.$chillC; exit;
        if ($chillC >= 20)          { echo $clr_orange;} 
        elseif ($chillC <= 10)      { echo $clr_blue;  }
        else                        { echo $clr_green; }   
        echo $hourlychill.'</b>&deg;';}
        
elseif ($hudxC <> 'n/a' && $hudxC <> '' && (float) $hudxC <> 0 && ( (float) $hudxC - (float) $tempC ) > 4) #### 2021-01-23
      { echo ' '.$ltr_feels.': ';
        if ($hudxC >= 20)          { echo $clr_orange;} 
        elseif ($hudxC <= 10)      { echo $clr_blue;  }
        else                       { echo $clr_green; }
        echo  $hourlyhudx.'</b>&deg;';  }
echo $tempunit.PHP_EOL;          
if ($onehrfct_missing == false) 
     {  echo  '<br />'.$clr_green   .$hourlySummary.' </b>'.PHP_EOL;}
echo  '<br /><span>'.$ltr_windspd;
if ($hourlyWindGust > 0)
     {  echo  '-'  .$ltr_gust;}
echo '</span> '.PHP_EOL;

$highspeed      =  convert_speed (40,'kmh',$windunit,0);
if ($hourlyWindGust >= $highspeed)      
     {  echo $clr_orange;}  else { echo $clr_green;}
echo $hourlyWindSpeed;
if ($hourlyWindGust > 0)
     {  echo '-'.$hourlyWindGust;}
echo '</b> '.$windunit.PHP_EOL;
# rain
$break = '<br />';  
if ($onehrfct_missing == true) {$ltr_forecast = '';}
if (isset ($hourlyuv) && $hourlyuv <> false && $hourlyuv >= 1)
     {  echo $break; $break = '';
        echo $ltr_uvi.' '.$ltr_forecast.' ';
        if ($hourlyuv > 6) 
             {  echo $clr_orange;}  else { echo $clr_green;}
        echo $hourlyuv.'</b>&nbsp;&nbsp;'; }
if (isset ($hourlyPrecipProb) )
     {  echo $break;
        if (isset ($hourlyPrecipType) && $hourlyPrecipType <> 'snow')
             {  echo $ltr_rain.' '.PHP_EOL.$rainsvg.PHP_EOL; } 
        else {  echo $ltr_snow.' '.PHP_EOL.$snowflakesvg.PHP_EOL;}
        echo ' '.$hourlyPrecipProb. '%'; }  
echo PHP_EOL.'</td>
</tr>
</table>';
