<?php  $scrpt_vrsn_dt  = 'baro_c_block.php|01|2020-11-04|';  # release 2012_lts
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
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
if (!isset ($KISS) ) { $KISS = false;}
$my_KISS        = $KISS;   
#$my_KISS        = false;
#
# ------------------------- translation of texts
$rise_l = lang('Rising') .' &uarr;';
$fall_l = lang('Falling').' &darr;'; 
$stdy_l = lang('Steady') ; #.' &harr;';
$min_l  = lang('Min');
$max_l  = lang('Max');
$curt_l = lang('Current');
#
# -------------------------------- marker colors
$cbr_hgh_clr    =  '#d65b4a';  // marker high
$cbr_lw_clr     =  '#01a4b4';   // marker low
$cbr_nw_clr     =  '#9aba2f';   // marker current
#
# ----------- test values
#$weather["temp_units"]          = 'C';   #$weather["temp_units"]          = 'F'; 
#$weather["barometer"]           = 1012;  #$weather["barometer"]           = 29.5;
#$weather["barometer_min"]       = 1012;   #$weather["barometer_min"]       = 28;
#$weather["barometer_max"]       = 1040;   #$weather["barometer_max"]       = 31;
#$weather['barometer_trend'] = 0;
# --------EO test values
#
$baro_high      = number_format($weather["barometer_max"],$dec_baro,'.','');
$baro_low       = number_format($weather["barometer_min"],$dec_baro,'.','');
$baro_act       = number_format($weather["barometer"],$dec_baro,'.','');
if ($baro_high == $baro_low && $baro_act ==  $baro_low)
     {  $high_lows   = false; }  #### 2018-10-28
else {  $high_lows   = true;}  #### 2018-10-28
#
$baro_unit      = lang($weather['barometer_units']);
if ($weather["temp_units"]=='C')
     {  $baro_other     = number_format($baro_act *0.029529983071445,$dec_baro+1,'.','');
        $baro_other_u   = lang('inHg');}
else {  $baro_other     = number_format($baro_act *33.863886666667,$dec_baro-1,'.','');
        $baro_other_u   = lang('hPa');}
#
$trnd_clr       = $cbr_nw_clr;
$trnd_txt       = $trnd_num     ='';
$trnd_num       = '<!-- '.$weather['barometer_trend'].' -->';
if ( (string) $weather['barometer_trend'] <> 'n/a' )  // 'n/a'  and 0 are sometimes equal
     {  $trnd   = number_format((float)$weather['barometer_trend'],$dec_baro+1); 
        if     ($trnd > 20)     { $trnd = 0;}
        if ($trnd > 0 )         { $trnd_clr     =   $cbr_hgh_clr; $trnd_txt =  $rise_l;}
        elseif ($trnd < 0)      { $trnd_clr     =   $cbr_lw_clr;  $trnd_txt =  $fall_l;}
        else                    { $trnd_clr     =   $cbr_nw_clr;  $trnd_txt =  $stdy_l;}
        $trnd_num       = $trnd.' '.$baro_unit;}
if (isset ($weather['barometer_trend_text']) && (string) $weather['barometer_trend_text'] <> 'n/a')
     {  $trnd_txt       = lang($weather['barometer_trend_text']);
	$string         = ' '.strtolower($weather['barometer_trend_text']);
        if     (strpos ($string,'rising') > 0)  {$trnd_clr     =   $cbr_hgh_clr;}
        elseif (strpos ($string,'falling') > 0) {$trnd_clr     =   $cbr_lw_clr;}}
$trnd_txt       .=  '<br />'.$trnd_num;
#
#-----------------------------------------------
#                                  generate html
#-----------------------------------------------
#
# ------------            date time of last data
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# -------------                 the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------                   left column
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;
if ($high_lows) { 
        echo '<!-- lowest value -->
<div class="PWS_div_left" style="border-right-color: '.$cbr_lw_clr.';">'
        .$min_l.'<br /><b >'
        .$baro_low.'&nbsp;'.$baro_unit.'</b>'
        .'</div>'.PHP_EOL;}
echo '<!-- other unit block -->
<div class="PWS_div_left" style="border-right-color: '.$cbr_nw_clr.';">'
        .$curt_l.'<br /><b >'
        .$baro_other.'&nbsp;'.$baro_other_u.'</b>'
        .'</div>'.PHP_EOL;
echo '</div>
<!-- END of left values -->'.PHP_EOL;       
#
# ----------------                   middle area
if ($my_KISS == true && $current_theme == 'dark')
     {  $black  = 'color: silver;';} 
else {  $black  = 'color: black;'; }
#
echo '<!-- middle part  -->
<div class="PWS_middle" style="width: 130px; height: 130px; margin-left:4px; margin-top: 0px; text-align: center; ">
        <div style=" height: 130px; margin: 0 auto; ">'.PHP_EOL;
echo return_baro().'
        </div>
        <div class="narrow" style="position: absolute; top: 50px; margin: 30px 15px; '.$black.'">
          <span class="large" >'.$baro_act.'</span>
        </div> 
</div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ----------------                  right column
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
if ($high_lows) 
     {  echo '<!-- highest value -->
<div class="PWS_div_right" style="border-left-color: '.$cbr_hgh_clr.';"><!-- max value -->'
        .$max_l.'<br /><b >'
        .$baro_high.'&nbsp;'.$baro_unit.'</b>'
        .'</div>'.PHP_EOL;
        if ( $weather['barometer_trend'] === 'n/a' && (string) $weather['barometer_trend_text'] === 'n/a')
             {  echo '<div class="PWS_div_right" style="border-color: transparent;"></div>'.PHP_EOL;}
        else {  echo '<div class="PWS_div_right" style="border-left-color: '.$trnd_clr.';"><!-- trend -->'
        .'<b>'
        .$trnd_txt.'</b>'
        .'</div>'.PHP_EOL;} 
        }
echo '</div><!-- END of right values -->'.PHP_EOL; 
#
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
return;
#
function return_baro () {
global  $lang, $weather, $current_theme,  $my_KISS,
        $baro_high,  $baro_act,   $baro_low ,
        $cbr_lw_clr, $cbr_nw_clr, $cbr_hgh_clr;  #$my_KISS= true;
#
if ($weather['temp_units'] == 'C')
     {  if     ($baro_high <=  1035  && $baro_low >=  963 && 1==1) 
             {  $baro_max = 1036; $baro_min  = 964;  $baro_zero = 1018; $step = 3;}
        elseif ($baro_high <=  1047  && $baro_low >=  951 && 1==1) 
             {  $baro_max = 1048; $baro_min  = 952;  $baro_zero = 1024; $step = 4;}                        
        else {  $baro_max = 1060; $baro_min  = 940;  $baro_zero = 1030; $step = 5;} # echo __LINE__.' '.$baro_high.' '.$baro_low; exit;
        $baro_lngth     = $baro_max - $baro_min; // 120hPa
        $rotate2        = 360 * ($baro_act  - $baro_zero) /  $baro_lngth;        # *2.7 - 225;   #  * 0.02953 * 50.6; 
        $rotate4        = 360 * ($baro_low  - $baro_zero) /  $baro_lngth;        #*2.7 - 225;  #  * 0.02953 * 50.6;
        $rotate3        = 360 * ($baro_high - $baro_zero) /  $baro_lngth;       #*2.7 - 225; } #  * 0.02953 * 50.6; 
        $arr    = array();
        $arr[]  = 1000; 
        for ($n = 1; $n < 15 ; $n++) {$arr[] = 1000 - $step*$n;  $arr[] = 1000 + $step*$n;  } #echo '<pre>'.print_r($arr,true); exit;
        }
else {  $baro_max = 31.5;       
        $baro_min = 27.5;       
        $baro_zero= 30.5;
        $arr  = array ('29.5','','','','','29.0','30.0','','','','','28.5','30.5','','','','','28.0','31.0','','','','','27.5','31.5'); #echo '<pre>'.print_r($arr,true); exit;        
        $baro_lngth     = $baro_max - $baro_min; // 4.0 inHg
        $rotate2        = 360 * ($baro_act  - $baro_zero) /  $baro_lngth;  
        $rotate4        = 360 * ($baro_low  - $baro_zero) /  $baro_lngth; 
        $rotate3        = 360 * ($baro_high - $baro_zero) /  $baro_lngth;    
        }  #echo __LINE__.' $rotate2='.$rotate2.' $rotate3='.$rotate3.' $rotate4='.$rotate4; exit;
#
$hgh = $low = true;
if ($baro_high  === $baro_low && $baro_low   === $baro_act)  
     {  $hgh = false;    ######  2020-10-28
        $low = false; }  ######  2020-10-28
#
if ($current_theme <> 'dark')
     {  $dial_clr       = 'rgb(250, 250, 250)';
        $dial_txt       = 'rgb(40, 40, 40)';
        $white_clr      = 'rgb(229, 229, 229)';}
else {  $dial_clr       = 'rgb(90, 90, 90)';
        $dial_txt       = 'rgb(255, 255, 255)';
        $white_clr      = 'rgb(160, 160, 160)';}
#
if ($my_KISS == true) 
     {  $rad = 48; 
        $pnts   ='111 72 100 65 111 58';
        $pntsHL ='115 70 122 65 115 60';} 
else {  $rad    = 63; 
        $pnts   ='108 72 122 65 108 58'; 
        $pntsHL ='116 65 126 61 126 69';}
#
# // start generate svg
$return = '
<svg width="130" height="130" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
  <circle id="baroBack"  r="'.$rad.'" cx="65" cy="65" style=" fill:none;  stroke-width: 4px; stroke: grey; " />';
#
if ($my_KISS == true ) // keep it simple
     {  $return .= '
  <text x="63"  y="120"                      style="fill: grey ;   font-size: 8px sans-serif;">||</text>    
  <text x="62"  y="124" text-anchor="end"    style="fill: '.$cbr_lw_clr.';  font: bold 8px sans-serif; ">'.$baro_min.'</text>
  <text x="68"  y="124" text-anchor="start"  style="fill: '.$cbr_hgh_clr.'; font: bold 8px sans-serif; ">'.$baro_max.'</text>
  <polygon id="baroCrnt" points="'.$pnts.'"  style="fill: '.$cbr_nw_clr.';" transform="rotate('.$rotate2.' 65 65)"></polygon>';
        if ($hgh == true) { $return .= '
  <polygon id="baroHgh" points="'.$pntsHL.'"  style="fill: '.$cbr_hgh_clr.';" transform="rotate('.$rotate3.' 65 65)"></polygon>';}
        if ($low == true) { $return .= '
  <polygon id="baroLow" points="'.$pntsHL.'"  style="fill: '.$cbr_lw_clr.';" transform="rotate('.$rotate4.' 65 65)"></polygon>';}
        } // eo simple
else  { $return .= '
  <circle id="baroDial"  style="fill: '.$dial_clr.';" cx="65" cy="65" r="61"/>
  <text x="57" y="15"    style=" fill: '.$dial_txt.' ; font-size: 8px;">'.$arr[0].'</text>
  <text x="44"  y="20"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[1].'</text>
  <text x="78"  y="20"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[2].'</text>
  <text x="32"  y="24"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[3].'</text>
  <text x="90"  y="24"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[4].'</text>  
  <text x="22"  y="31"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[5].'</text>
  <text x="98"  y="31"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[6].'</text>
  <text x="15"  y="43"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[7].'</text>
  <text x="107" y="43"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[8].'</text>  
  <text x="10"  y="55"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[9].'</text>
  <text x="111" y="55"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[10].'</text>  
  <text x="07"  y="67"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[11].'</text>
  <text x="112" y="67"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[12].'</text>  
  <text x="10"  y="80"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[13].'</text>
  <text x="111" y="80"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[14].'</text> 
  <text x="14"  y="92"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[15].'</text>
  <text x="107" y="92"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[16].'</text>
  <text x="22" y="104"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[17].'</text>
  <text x="97" y="104"   style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[18].'</text>
  <text x="35" y="112"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[19].'</text>
  <text x="87" y="112"   style=" fill: '.$dial_txt.' ; font-size: 5px;">'.$arr[20].'</text>
  <text x="65" y="120"  text-anchor="middle" style=" fill: '.$dial_txt.' ; font-size: 16px;">|</text>
  <text x="62" y="120"  text-anchor="end"    style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[23].'</text>  
  <text x="68" y="120"  text-anchor="start"  style=" fill: '.$dial_txt.' ; font-size: 6px;">'.$arr[24].'</text> ';
#
        if ($weather['temp_units'] == 'C')
             {  for ($n = 0; $n< 12; $n++) {
                $rotate       = round( ($n * 360 /24),1);
                $return .= '  <line style="stroke: '.$dial_txt.';" x1="19" y1="65" x2="111" y2="65" transform="rotate('.$rotate.' 65 65)"></line>'.PHP_EOL;
                }
        } 
        else {  for ($n = 0; $n< 4; $n++) {
                $rotate       = round( ($n * 360 /8),1);
                $return .= '  <line style="stroke: '.$dial_txt.';" x1="19" y1="65" x2="111" y2="65" transform="rotate('.$rotate.' 65 65)"></line>'.PHP_EOL;
                }
        }
        $return .= '  <circle id="baroWhite" style="stroke-width: 0;  fill: '.$white_clr.';" cx="65" cy="65" r="44"></circle>';
        $return .= '
  <polygon id="baroCrnt" points="'.$pnts.'"  style="fill: '.$cbr_nw_clr.';" transform="rotate('.$rotate2.' 65 65)"></polygon>';
        if ($hgh == true) { $return .= '
  <polygon id="baroHgh" points="'.$pntsHL.'"  style="fill: '.$cbr_hgh_clr.';" transform="rotate('.$rotate3.' 65 65)"></polygon>';}
        if ($low == true) { $return .= '
  <polygon id="baroLow" points="'.$pntsHL.'"  style="fill: '.$cbr_lw_clr.';" transform="rotate('.$rotate4.' 65 65)"></polygon>';}
        } // eo extensive 
#
$return .= '
</svg>';
return $return; 
} // eof return_baro
