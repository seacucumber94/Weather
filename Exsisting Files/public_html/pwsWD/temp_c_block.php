<?php  $scrpt_vrsn_dt  = 'temp_c_block.php|01|2021-12-08|';  # PHP 8.1 + degr trend + html wrng | release 2012_lts
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
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -----------------------------  script settings
if (!isset ($KISS)) {$KISS = false;}
$my_KISS        = $KISS;    
#$my_KISS        = false;
#
# ------------------------- translation of texts
$heat_l = lang('Heatindex');
$fls_l  = lang('Feels like');
$chll_l = lang('Windchill');
$wb_l   = lang('Wet Bulb');
$hum_l  = lang('Humidity');
$dwp_l  = lang('Dewpoint');
$c_l    = lang('Celsius');
$f_l    = lang('Fahrenheit');
$ind_l  = lang('Indoor');
#
$temp_colors = array(
        '#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
        '#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
        '#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
        '#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
        '#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
        '#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
        '#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
        '#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',);
$maxTemp        = count($temp_colors) - 1;
#
$nearr = '
<svg viewBox="0 0 10 10" width="10px" height="10px" xmlns="http://www.w3.org/2000/svg" >
  <g transform="matrix(0.707107, -0.707107, 0.707107, 0.707107, -3.14214, 5.581865)">
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="1" y1="5" x2="11" y2="5"/>
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="6" y1="1" x2="11" y2="5"/>
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="6" y1="9" x2="11" y2="5"/>
  </g>
</svg>';
$swarr = '
<svg viewBox="0 0 10 10" width="10px" height="10px" xmlns="http://www.w3.org/2000/svg" >
  <g transform="matrix(-0.707107, 0.707107, -0.707107, -0.707107, 12.414218, 4.167655)">
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="1" y1="5" x2="11" y2="5"/>
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="6" y1="1" x2="11" y2="5"/>
    <line style="stroke: rgb(0, 0, 0); stroke-linecap: round; stroke-width: 1px;" x1="6" y1="9" x2="11" y2="5"/>
  </g>
</svg>';
#
# -------------------------------- marker colors
$cbr_hgh_clr    =  '#d65b4a';  // marker high
$cbr_lw_clr     =  '#01a4b4';   // marker low
$cbr_nw_clr     =  '#66cc33';   // marker current
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
#                 temp_in_c
if (!function_exists ('temp_in_c') ){
function temp_in_c ($value)
     {  global $tempunit;
        $return = (float) ($value);
        if ($tempunit <> 'C') 
             {  $return  = round (5*($return -32)/9);}
        else {  $return  = round ($return);}
        return $return; }
}
#                 temp_value
if (!function_exists ('temp_value') ) {
function temp_value ($in_C, $value, $text)
     {  global $maxTemp, $temp_colors;
        if ($value === 'n/a' || $value === false) 
            {   return '<!-- no value '.$value.' -->'.PHP_EOL; return;}
        $n      = 32 + round($in_C); # 2021-12-08
        if ($n < 0) {$n=0;}
        if ($n > $maxTemp)      
             {  $color  = $temp_colors[$maxTemp];}
        else {  $color  = $temp_colors[$n];}
        return '<div class="PWS_div_right" style="border-left-color: '.$color.';">'
        .$text.'<b><br />'.$value.'&deg;</b>'
        .'</div>'.PHP_EOL;}
}
#                 temp_nr
if (!function_exists ('tempnr') ){
function tempnr ($value)
     {  global $dec_tmp;
        return number_format ($value,$dec_tmp);}
}
#
if (!function_exists ('return_temp') ){
function return_temp () {
global  $lang, $weather, $current_theme, $max_min_txt,
        $color1, $color2, $hgh_temp, $low_temp, 
        $top, $dial, $rotate2, $rotate3, $rotate4, 
        $cbr_lw_clr, $cbr_nw_clr, $cbr_hgh_clr;
#
# ---------------  test values
#$current_theme = 'dark';
#$current_theme = 'light';
# ---------------  test values
#
if ($current_theme <> 'dark')  // light or user
     {  $dial_clr       = 'rgb(250, 250, 250)';
        $dial_txt       = 'rgb(40, 40, 40)';
#        $white_clr      = 'rgb(229, 229, 229)'; // not used for temp
        
        }
else {  $dial_clr       = 'rgb(90, 90, 90)';
        $dial_txt       = 'rgb(255, 255, 255)';
#        $white_clr      = 'rgb(190, 190, 190)';
        }
#
$arr    = array();
for ($n = 1; $n < 14; $n++)
    {   $arr[] = $top - $dial*$n;
        $arr[] = $top + $dial*$n;} #echo __LINE__.print_r($arr,true); exit;
$return ='
<svg width="130" height="130" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" style="stop-color:'.$color1.' ;stop-opacity:1" />
      <stop offset="100%" style="stop-color:'.$color2.' ;stop-opacity:1" />
    </linearGradient>
  </defs>
  <circle id="tempBack"  r="65" cx="65" cy="65" fill="grey"/>
  <circle id="tempDial"  style="fill: '.$dial_clr.';" cx="65" cy="65" r="61"/>
  <text x="62" y="15"    style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$top.'</text>
  
  <text x="46"  y="18"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[0].'</text>
  <text x="78"  y="18"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[1].'</text>
  
  <text x="32"  y="23"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[2].'</text>
  <text x="90"  y="23"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[3].'</text>  

  <text x="22"  y="32"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[4].'</text>
  <text x="101" y="32"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[5].'</text>

  <text x="13"  y="43"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[6].'</text>
  <text x="110" y="43"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[7].'</text>  

  <text x="8"   y="55"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[8].'</text>
  <text x="114" y="55"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[9].'</text>  

  <text x="07"  y="67"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[10].'</text>
  <text x="114" y="67"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[11].'</text> 
   
  <text x="9"   y="80"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[12].'</text>
  <text x="114" y="80"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[13].'</text> 

  <text x="13"  y="92"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[14].'</text>
  <text x="110" y="92"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[15].'</text>

  <text x="22"  y="104"  style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[16].'</text>
  <text x="101" y="104"  style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[17].'</text>
 
  <text x="34" y="112"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[18].'</text>
  <text x="90" y="112"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[19].'</text>
 
  <text x="46" y="117"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[20].'</text>  
  <text x="78" y="117"   style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[21].'</text>

  <text x="65" y="120"  text-anchor="middle" style=" fill: '.$dial_txt.' ; font-size: 16px;">|</text>
  <text x="62" y="124"  text-anchor="end"    style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[22].'</text>  
  <text x="68" y="124"  text-anchor="start"  style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[23].'</text> 
  
  '.PHP_EOL;   
for ($n = 0; $n< 12; $n++) {
  $rotate       = round( ($n * 360 /24),1);
  $return .= '  <line style="stroke: '.$dial_txt.';" x1="19" y1="65" x2="111" y2="65" transform="rotate('.$rotate.' 65 65)"></line>'.PHP_EOL;
}
$return .='  <circle id="tempWhite" style="stroke-width: 0;"  fill="url(#grad1)" cx="65" cy="65" r="44"></circle>
  <polygon id="tempCrnt" points="108 72 122 65 108 58"  style="fill: '.$cbr_nw_clr.';" transform="rotate('.$rotate2.' 65 65)"></polygon>';
if (array_key_exists ('temp_high', $weather) && $max_min_txt <> '') { $return .= '
  <polygon id="tempHgh" points="116 65 126 61 126 69"  style="fill: '.$cbr_hgh_clr.';" transform="rotate('.$rotate3.' 65 65)"></polygon>';}
if (array_key_exists ('temp_low', $weather) && $max_min_txt <> '') { $return .= '
  <polygon id="tempLow" points="116 65 126 61 126 69"  style="fill: '.$cbr_lw_clr.';" transform="rotate('.$rotate4.' 65 65)"></polygon>';}
$return .= '
</svg>';
return $return; 
}  // eof return_temp
} // eo function exists return_temp
#
# -------------test values
#$weather['heat_index'] = 32.4;
#$weather['windchill']  = 3.5;
#$weather['temp']  = 11.3;
#$weather['temp_high']  = 11.3;
#$weather['temp_low']  = 11.3;
#$weather["temp_trend"] = -1;
# -------------test values
#
# -----------------------------  set color range
if (array_key_exists ('temp_high', $weather)) 
     {  $hgh_temp       = $weather['temp_high'];
        $low_temp       = $weather['temp_low'];
        $max_temp       = ceil  ($hgh_temp + 5);
        $min_temp       = floor ($low_temp  - 5);
        if ($weather['temp_high'] == $weather['temp_low'] && $weather['temp_high'] == $weather['temp'])
             {  $max_min_txt    = '';}
        else {  $max_min_txt    = '&uarr;<b>'.tempnr($hgh_temp).'&deg;</b>  &darr;<b>' .tempnr($low_temp).'&deg;</b>';}
        }
else {  $hgh_temp       = false;
        $low_temp       = false;
        $max_temp       = ceil   (10 + $weather['temp']);
        $min_temp       = floor  ($weather['temp']) - 10;        
        $max_min_txt    = ''; }
#-----------------------------------------------
#                                  generate html
#-----------------------------------------------
#
# ---------------- the date time
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column
if (1 == 1) {
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;
# 
# ----------  temp in other unit
#
$temp   = $weather['temp'];
$in_C   = temp_in_c ($temp);
if ($tempunit == 'C')
     {  $o_unit = 'F';
        $u_txt  = $f_l;}
else {  $o_unit = 'C';
        $u_txt  = $c_l;}
$o_temp = convert_temp ($temp,$tempunit,$o_unit,1);
$n      = 32 + $in_C;
if ($n < 0) {$n=0;}
if ($n > $maxTemp)      
     {  $color  = $temp_colors[$maxTemp];}
else {  $color  = $temp_colors[$n];}
echo '<div class="PWS_div_left" style="border-right-color: '.$color.';">'
        .$u_txt.'<b><br />'.tempnr($o_temp) .'&deg;</b>'
        .'</div>'.PHP_EOL;
#
# ------------------ inside temp
#
if (isset ($show_indoor) && $show_indoor <> false && (string) $weather['temp_indoor'] <> 'n/a')
     {  $temp   = (float)$weather['temp_indoor'];
        $in_C   = temp_in_c ($temp);
        $n      = 32 + $in_C;
        if ($n < 0) {$n=0;}
        if ($n > $maxTemp)      
             {  $color  = $temp_colors[$maxTemp];}
        else {  $color  = $temp_colors[$n];}
echo '<div class="PWS_div_left" style="border-right-color: '.$color.';">'
        .$ind_l.'<b><br />'.tempnr($temp) .'&deg;</b>'
        .'</div>'.PHP_EOL;}
#
# --------------------  Humidity
$hum    = (int) $weather["humidity"];
if     ($hum  > 80) { $clr = 'orange';}
elseif ($hum  > 60) { $clr = 'green';}
else                { $clr = 'yellow';}
if(array_key_exists("humidity_trend",$weather))
     {  if     ( $weather["humidity_trend"] > 0) { $arrow       = '&uarr;';}
        elseif ( $weather["humidity_trend"] < 0) { $arrow       = '&darr;';}
        else                                     { $arrow       = '';}
}
echo '<div class="PWS_div_left" style="border-right-color: '.$color.';">'
        .$hum_l.'<b><br />'.$hum.'% '.$arrow.'</b>'
        .'</div>'.PHP_EOL;
#      
echo '</div>
<!-- END of left values -->'.PHP_EOL;       
} // eo left values
# ----------------  middle area
#
# ----------------- color range
$in_C   = temp_in_c ($max_temp);
$n      = 32 + $in_C;
if ($n < 0) {$n=0;}
if ($n > $maxTemp)      
     {  $color1 = $temp_colors[$maxTemp];}
else {  $color1 = $temp_colors[$n];}
$in_C_l = $in_C =temp_in_c ($min_temp);
$n      = 32 + $in_C;
if ($n < 0) {$n=0;}
if ($n > $maxTemp)      
     {  $color2 = $temp_colors[$maxTemp];}
else {  $color2 = $temp_colors[$n];}
#
# ------------------- temp trend
$arrow  = $trend_txt = '';
if (array_key_exists ('temp_trend', $weather) && $weather["temp_trend"] <> 'n/a') 
     {  if     ( $weather["temp_trend"] > 0) { $arrow       = $nearr;}
        elseif ( $weather["temp_trend"] < 0) { $arrow       = $swarr;}
        else                                 { $arrow       = '';}
        $trend_txt      = '<br /><b>'.tempnr($weather["temp_trend"]).'&deg;</b> '.$arrow;
        }
#
$lowest = $highest = $weather['temp'];
#
if (array_key_exists ('temp_high', $weather) ) 
     {  $highest= $weather['temp_high'];}
if (array_key_exists ('temp_low', $weather) ) 
     {  $lowest = $weather['temp_low']; }

$mid_h  = floor ($highest * 0.1);               
$mid_l  = ceil  ($lowest * 0.1) -1; 
$mid    = round ( ($mid_h + $mid_l) / 2);
if     ($mid > $mid_h) { $mid = $mid_h; }
elseif ($mid < $mid_l) { $mid = $mid_l; } $dbg = __LINE__.' $highest='.$highest.' $mid_h='.$mid_h.' $lowest ='.$lowest.' $mid_l='.$mid_l.PHP_EOL; 
$mid    = 10 * $mid;                      $dbg .= __LINE__.' $mid ='.$mid.PHP_EOL;
#
if ( $tempunit == 'F' ) 
     {  $dial   = 2;} 
else {  $dial   = 1;}                     $dbg .= __LINE__.' $tempunit ='.$tempunit.' $dial ='.$dial. PHP_EOL;
# ---- 12 values between top and bottom of scale
$range  = 12 * $dial;  
# ------------- check if values are out of range
if ( $highest > ($mid + $range) ) {$dial   = 2 * $dial; $range  = 12 * $dial; $dbg .= __LINE__.' $dial='.$dial.PHP_EOL;}
if ( $lowest  < ($mid - $range) ) {$dial   = 2 * $dial; $range  = 12 * $dial; $dbg .= __LINE__.' $dial='.$dial.PHP_EOL;}
if ( $highest > ($mid + $range) ) {$dial   = 2 * $dial; $range  = 12 * $dial; $dbg .= __LINE__.' $dial='.$dial.PHP_EOL;}
if ( $lowest  < ($mid - $range) ) {$dial   = 2 * $dial;}   $dbg .= __LINE__.' $dial ='.$dial.' $range ='.$range ;  
$top = $mid;
$zero   = $top + 6*$dial;
$step   = 360 / (24 * $dial);
$rotate2= $step * ($weather['temp']      - $zero);
$rotate3= $step * ($weather['temp_high'] - $zero);
$rotate4= $step * ($weather['temp_low']  - $zero);     
#
echo '<!-- middle part  -->
<div class="PWS_middle" style="width: 130px; height: 130px; margin-left:4px; margin-top: 0px; text-align: center;">'.PHP_EOL;
#
if ($max_min_txt == '') {$extra = '<br />';} else { $extra = '';}
#
if ($my_KISS == false) {
        echo return_temp ();
        echo '    <div style= "margin: 0 auto; position: absolute; top: 65px; width: 130px;">
        <div style=" margin: 0 auto; color: black; text-shadow: 2px 2px 10px yellow; font-size: 13px; ">
        '.$extra.'<b style="font-size: 20px;">'. tempnr($weather['temp']).'&deg;</b>
        <br />'
        .$max_min_txt
        .$trend_txt.'
        </div>
    </div>'.PHP_EOL;}
else {  echo '    <div class="PWS_round" style= "margin: 0 auto; margin-top: 12px; margin-bottom: 10px; height: 102px; width: 102px; 
        overflow: hidden; text-align: center; background: linear-gradient(90deg, '.$color1.', '.$color2.');  color: black;">
        '.$extra.'<br /><br /><b style="font-size: 26px;">'. tempnr($weather['temp']).'&deg;</b>
        <br /><span style="font-size: 12px;">'
        .$max_min_txt
        .$trend_txt.'</span>
        </div>'.PHP_EOL;}
#
echo '</div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ---------------- right column
if (1 == 1) { // right values
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
#
# ------  feels/ chill type temps
$heat   = temp_in_c ($weather['heat_index']);
$tmp    = temp_in_c ($weather['temp']);       
$feel   = temp_in_c ($weather['temp_feel']);
$chill  = temp_in_c ($weather['windchill']);

if ( $heat > $notifyHeatIndex  
|| ( $showFeelsLike == true  && $tmp > 27) )
     {  if ($tmp < $feel )
             {  echo temp_value ($heat, $weather['heat_index'], $heat_l);}
        else {  echo temp_value ($tmp,  $weather['temp']      , $heat_l);}
        }
#
elseif ($weather['windchill'] === 'n/a' || $weather['windchill'] === '--' )
     {  echo temp_value ($feel,  tempnr($weather['temp_feel'])      , $fls_l);}
#
elseif ($chill < 5)
     {  echo temp_value ($chill,  tempnr($weather['windchill'])      , $chll_l);}
#
elseif ($showFeelsLike)
     {  echo temp_value ($feel,  tempnr($weather['temp_feel'])      , $fls_l);}
#
# -------- wetbulb
$Tc     = $tmp;
$P      = convert_baro ($weather['barometer'],$pressureunit,'hPa');
$RH     = $weather['humidity'];
#
$Tdc = (($Tc - (14.55 + 0.114 * $Tc) * (1 - (0.01 * $RH)) - pow((2.5 + 0.007 * $Tc) * (1 - (0.01 * $RH)) , 3) - (15.9 + 0.117 * $Tc) * pow(1 - (0.01 * $RH),  14)));
$E = (6.11 * pow(10 , (7.5 * $Tdc / (237.7 + $Tdc))));
$wetbulbcalc = (((0.00066 * $P) * $Tc) + ((4098 * $E) / pow(($Tdc + 237.7) , 2) * $Tdc)) / ((0.00066 * $P) + (4098 * $E) / pow(($Tdc + 237.7) , 2));
$wetbulbx       = number_format($wetbulbcalc,1);
$wet            = (float) $wetbulbx;
if ($tempunit <> 'C') 
     {  $wetbulbx = convert_temp ($wetbulbx,'C',$tempunit,1);}
echo temp_value ($wet,  tempnr($wetbulbx)      , $wb_l);
#
# --------  Dewpoint
$dewp   = temp_in_c ($weather['dewpoint']);
echo temp_value ($dewp,  tempnr($weather['dewpoint']) , $dwp_l);
echo '<!-- END of right values -->'.PHP_EOL;
} // right values
#
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
echo '</div>'.PHP_EOL;
#
if (isset ($_REQUEST['test']) ) { 
        echo '<!-- '.$dbg.' -->'.PHP_EOL;
        echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; 
        $stck_lst='';}
