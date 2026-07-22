<?php  $scrpt_vrsn_dt  = 'sun_c_block.php|01|2021-06-10|';  # error in 24 hr light/dark |release 2012_lts | orange text off
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
#
#------------------------------- script settings 
#                             
$orange ='orange';  // css class for large text in the middle
$orange = ''; # default text is black or silver #### 2020-12-15
#
if (!isset ($KISS)) {$KISS = false;}  // keep it simple graphs
$my_KISS        = $KISS;
#$my_KISS        = false;       // to set for this block only.
# -------------------------------- test settings
#$lat = (float) "78.7836";
#$lat =  (float) "50.7836";
#$lon = (float) "16.5278";
#$lon = (float) "4.5278";
#
#------------ colors for small blks left / right
$clr_light      = '#e8c400'; # '#ff8841';  
$clr_dark       = 'dimgrey';
$clr_sun_up     = '#e8c400'; # '#ecb454';
#
# ----------------------------------- texts used
$hrs_l  = ' '.lang('hrs ');
$min_l  = ' '.lang('min ');
$none   = lang('none');
$dayl_l = lang('Daylight');
$sunr_l = lang('Sunrise');
$suns_l = lang('Sunset');
$azim_l = lang('Azimuth');
$drkl_l = lang('Darkness');
$elv_l  = lang('Elevation');
$estim_l= lang('Estimated');
$tmrrw_l= lang('Tomorrow');
$td_l   = lang('Today');
$spcl_l = lang('till midnight');
$tllsr_l= lang('Till Sunrise');
$dlght_l= lang('Of Daylight');
$t24h_s = lang('Midnight sun');
$t24h_d = lang('No sunshine today');
#
$result         = date_sun_info(time(), $lat, $lon);  #  echo '<pre>'.time().print_r($result,true);
$nextday        = time() + 24*60*60;
$result2        = date_sun_info($nextday,$lat, $lon);  # echo '<pre>'.print_r($result2,true); 
#
$now = time(); # 2022-08-30
$Tyear          = date ('Y',$now);
$Tmonth         = date ('m',$now);
$Tday           = date ('d',$now);
$Ustart         = mktime (0,0,0,(int) $Tmonth,(int) $Tday,(int) $Tyear); 
$Uend           = $Ustart + 24*60*60;
$sunrise        = $sunset = true;    # echo '<pre>'.print_r($result,true); exit;   #  echo date ('c',$Ustart); 
if ((int)$result['sunset']  === 1) // sun at 24:00  ((bool)$result['sunset']  === true)
     {  $result['sunset']  = $Uend;    
        $sunset = false;
        $dlght_l = $tllsr_l = $spcl_l;}
elseif ((bool)$result['sunset']  === false)
      { $sunset = false;
        $dlght_l = $tllsr_l = $spcl_l;}
if ((int)$result['sunrise'] === 1)  // sun at 00:00
     {  $result['sunrise'] = $Ustart;  
        $sunrise = false;
        $dlght_l = $tllsr_l = $spcl_l;}      
elseif ((bool)$result['sunrise'] === false)
      { $sunrise = false;
        $dlght_l = $tllsr_l = $spcl_l;}  
$light          = $result['sunset'] - $result['sunrise'];
if ( (int) $light == 0)
     {  $daylight       = $none;}
else {  $hrs            = gmdate('G',$light);
        if ((int) $hrs == 0) 
             {  $hrs = 24; $min = '';}
        else {  $min    = gmdate('i',$light).$min_l;}
        $daylight       = $hrs.$hrs_l.$min;}
#
$dark           = 24*60*60 - $light;
if ( (int) $dark == 0)
     {  $daydark        = $none;}
else {  $hrs            = gmdate('G',$dark);
        if ((int) $hrs == 0) 
             {  $hrs = 24; $min = '';}
        else {  $min    = gmdate('i',$dark).$min_l;}
        $daydark        = $hrs.$hrs_l.$min;}


$nextrise       = $result['sunrise'];
$now            = time();
#
if ($sunrise === true) {
        if ($now > $result['sunrise'] && (bool) $result2['sunrise'] <> false)
             {  $nextrise       = set_my_time($result2['sunrise'], true);  
                $nextrisetxt    = $tmrrw_l;}
        else {  $nextrisetxt    = $td_l ;
                $nextrise       = set_my_time($nextrise, true);} 
        }
$nextset        = $result['sunset'];
if ($sunset === true) { 
        if ($now > $nextset)
             {  $nextset        = set_my_time($result2['sunset'], true);
                $nextsettxt     = $tmrrw_l;;}
        else {  $nextsettxt     = $td_l;
                $nextset        = set_my_time($nextset, true);} 
}
$firstrise      =   $result['sunrise'];
$secondrise     =   $result2['sunrise'];    
$firstset       =   $result ['sunset']; 
#
$sun_color      = $clr_dark;
if ($now < $firstrise) 
    {   $time   = $firstrise - $now;
        $hrs    = gmdate ('G',$time);
        $min    = gmdate ('i',$time);
        $txt    = $tllsr_l;}
elseif ($now < $firstset)
    {   $time   = $firstset - $now;
        $hrs    = gmdate ('G',$time);
        $min    = gmdate ('i',$time);
        $sun_color      = $clr_sun_up;
        $txt    = $dlght_l;}
else {  $time   = $secondrise - $now;
        $hrs    = gmdate ('G',$time);
        $min    = gmdate ('i',$time);
        $txt    = $tllsr_l;}
#
function get_azimuth ()
    {   global $lat, $lon, $TZ,$azimuth,$elevation,$sunazi, $echo ,$stck_lst, $sundir;
        $scrpt          = './others/azimuth.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt;
        $azimuth        = round($sunazi[25],1);
        $elevation      = round($sunpos[25],1); 
        $sundir         = lang ($sundir);}
#        
$azimuth=$elevation=0;
get_azimuth ();
#
# ---------------- the date time
echo '<div class="PWS_ol_time"><b class="PWS_online"> ' .$online.set_my_time_lng(time(),true).' </b></div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column <div class="PWS_div_left" style="border-right-color: '.$clr.';">  height: 40px;
echo '<!-- left values -->
<div class="PWS_left">
<div class="PWS_div_left" style="border-right-color: '.$clr_light.';">'
        .$dayl_l.'<br /><b >'
        .$daylight.'</b>'
        .'</div>'.PHP_EOL;
if ($sunrise === true )
     {  echo '<div class="PWS_div_left" style="border-right-color: '.$clr_sun_up.'; height: 40px;">'
                .$sunr_l.'<br /><b >'
                .$nextrise.'</b><br />'
                .$nextrisetxt
                .'</div>'.PHP_EOL;}
echo '<div class="PWS_div_left" style="border-right-width: 1px;">'
        .$azim_l.'<br /><b>'
        .$azimuth.'&deg; '.$sundir.'</b>'
        .'</div>'.PHP_EOL;     
echo '</div>
<!-- END of left values -->'.PHP_EOL;       
#
# ----------------  middle area

echo '<!-- middle part  -->
<div class="PWS_middle" style="width: 130px; height: 130px; margin-left:4px; margin-top: 0px; text-align: center; ">
    <div style=" height: 130px; margin: 0 auto; ">'.PHP_EOL;
#
echo return_clock();
#
if ($my_KISS == true && $current_theme == 'dark')
     {  $txt_clr= 'color: silver;';}
else {  $txt_clr= 'color: black;'; } 
#
echo '
    </div>'.PHP_EOL;
if ($sunrise === true && $sunset === true )
     {  echo '    <div class="narrow" style="position: absolute; top: 40px; margin: 30px 15px; '.$txt_clr.'">
        <b class="'.$orange.'" >'.$estim_l.'</b>
        <br />
        <span class="large" > '.$hrs.'</span> '.$hrs_l.' <span class="large" >'.$min.'</span> '.$min_l.'
        <br />
        <b class="'.$orange.'">'.$txt.'</b>
    </div>'.PHP_EOL;}
elseif ((bool) $result['sunrise']  === true)
     {  echo '    <div class="narrow" style="position: absolute; top: 45px; margin: 30px 15px; '.$txt_clr.'">
        <span class="large" >'.$t24h_s.'</span>
        </div>'.PHP_EOL;}
else {  echo '    <div class="narrow" style="position: absolute; top: 30px; margin: 30px 15px; '.$txt_clr.'">
        <span class="large" >'.$t24h_d.'</span>
        </div>'.PHP_EOL;}
echo '</div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ---------------- right column
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
echo '<div class="PWS_div_right" >'
        .$drkl_l.'<br /><b >'
        .$daydark.'</b>'
        .'</div>'.PHP_EOL;     
if ($sunset === true )
     {  echo '<div class="PWS_div_right" style="height: 40px; border-left-color: '.$clr_sun_up.';">'
                .$suns_l.'<br /><b >'
                .$nextset.'</b><br />'
                .$nextsettxt
                .'</div>'.PHP_EOL; }    
echo '<div class="PWS_div_right" style="border-left-width: 1px;">'
        .$elv_l.'<br /><b >'
        .$elevation.'&deg;</b>'
        .'</div>
</div><!-- END of right values -->'.PHP_EOL;
#
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { 
        echo '<!-- '.$echo.' -->'.PHP_EOL;
        echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
function return_clock() {    
global $result, $clockformat, $current_theme, $sun_color, $my_KISS;  # $clockformat = '24';
$h      = (int) date ('H',$result['sunrise']);
$m      = round (date ('i',$result['sunrise']) / 60,1);
$rotate = -15 * (18 - $h - $m ); # echo '$rotate='.$rotate; exit;
$h      = (int) date ('H');
$m      = round (date ('i') / 60,1);
$rotate2= -15 * (18 - $h - $m );  #echo '$rotate2='.$rotate2; exit;

$black  = 24* 3600 - ($result['sunset'] - $result['sunrise']);
$black  = round ( $black / 3600, 1); # echo $black; exit;
$yellow = 24 - $black;

if ($my_KISS == true) {$rad = 48;} else {$rad = 63;}

$from   = round ( $yellow  * $rad * 2 * M_PI / 24 ); #echo PHP_EOL.__LINE__." ".'$from='.$from;
$to     = ceil(2* $rad * M_PI);  # echo PHP_EOL.__LINE__." ".'$to='.$to.' $from ='.$from; 
#$rotate = 90;
$arr_fr = array (' 8px;','>01<', '>02<', '>03<', '>04<', '>05<', '>06<', '>6<' , '>07<', '>08<', '>09<', '>10<',  '>11<',  '>12<',  '>13<', '>14<', '>15<', '>16<', '>17<', '>18<', '>19<', '>20<', '>21<', '>22<',  '>23<',  '>24<');
$arr_to = array (' 6px;','>1am<','>2am<','>3am<','>4am<','>5am<','>6am<','>6am<','>7am<','>8am<','>9am<','>10am<','>11am<','>12pm<','>1pm<','>2pm<','>3pm<','>4pm<','>5pm<','>6pm<','>7pm<','>8pm<','>9pm<','>10pm<','>11pm<','>12am<');
if ($clockformat <> '12') {$arr_fr = $arr_to = array();}
#
if ($current_theme <> 'dark')
     {  $dial_clr       = 'rgb(250, 250, 250)';
        $dial_txt       = 'rgb(40, 40, 40)';
        $white_clr      = 'rgb(229, 229, 229)';}
else {  $dial_clr       = 'rgb(90, 90, 90)';
        $dial_txt       = 'rgb(255, 255, 255)';
        $white_clr      = 'rgb(160, 160, 160)';}
#
$return = '<svg width="130" height="130" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg" >
  <circle id="sunBack"  r="'.$rad.'" cx="65" cy="65" 
        style=" fill:none;  stroke-width: 4px; stroke: grey; " />
  <circle id="sunRing"  r="'.$rad.'" cx="65" cy="65" 
         style=" fill:none; stroke-width: 4px; stroke: yellow; 
                stroke-dasharray:'.$from.' '.$to.';"
                transform = "rotate('.$rotate.'  65 65 )"/>';
if (isset ($my_KISS) && $my_KISS == true ) 
     {  $return .= str_replace ($arr_fr,$arr_to,'
  <text x="65"  y="13"  text-anchor="middle"  style="fill: '.$dial_txt.'; font: bold 8px sans-serif; ">12</text>
  <text x="65"  y="122" text-anchor="middle"  style="fill: '.$dial_txt.'; font: bold 8px sans-serif; ">24</text>
  <text x="13"  y="67"  text-anchor="middle" transform = "rotate(-90  13 67 )"  style="fill: '.$dial_txt.'; font: bold 8px sans-serif; ">6</text>
  <text x="119" y="67"  text-anchor="middle" transform = "rotate(90  119 67 )"  style="fill: '.$dial_txt.'; font: bold 8px sans-serif; ">18</text>');
        }
else  {$return .= '
  <circle id="sunDial"   style="fill: '.$dial_clr.';" cx="65" cy="65" r="61"/>'
  .str_replace ($arr_fr,$arr_to,'
  <text x="46"  y="17"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">11</text>
  <text x="74"  y="17"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">13</text>
  <text x="33"  y="21.5" style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">10</text>
  <text x="85"  y="21.5" style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">14</text>
  <text x="22"  y="31"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">09</text>
  <text x="98"  y="31"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">15</text>
  <text x="12"  y="42"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">08</text>
  <text x="106" y="42"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">16</text>
  <text x="08"  y="54"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">07</text>
  <text x="112" y="54"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">17</text>
  <text x="06"  y="67"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">06</text>
  <text x="114" y="67"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">18</text>
  <text x="08"  y="80"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">05</text>
  <text x="112" y="80"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">19</text>
  <text x="14"  y="92"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">04</text>
  <text x="107" y="92"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">20</text>
  <text x="22" y="103"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">03</text>
  <text x="99" y="103"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">21</text>
  <text x="35" y="113"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">02</text>
  <text x="87" y="113"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">22</text>
  <text x="46" y="119"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">01</text>
  <text x="75" y="119"   style="white-space: pre; fill: '.$dial_txt.' ; font-size: 8px;">23</text>').'
  <g id="sunSunIcn" 
    transform="matrix(0.149059, 0, 0, 0.152871, -13.689035, -16.503487)" style="display: inline;">
    <path d="M 531.2117,140.66424 L 531.49603,180 L 559.10956,151.98436 L 531.49603,180 L 570.83179,179.71567 L 531.49603,180 L 559.51167,207.61353 L 531.49603,180 L 531.78036,219.33576 
        L 531.49603,180 L 503.8825,208.01564 L 531.49603,180 L 492.16027,180.28433 L 531.49603,180 L 503.4804,152.38647 L 531.49603,180 L 531.2117,140.66424 z " 
        id="path2895" style="opacity:1;color:black;fill:none;fill-opacity:1;fill-rule:evenodd;stroke:'.$dial_txt.';stroke-width:4.25196838;stroke-linecap:butt;stroke-linejoin:bevel;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;visibility:visible;display:inline;overflow:visible"/>
    <path style="opacity: 1; color: black; fill-opacity: 1; fill-rule: evenodd; stroke: none; stroke-width: 3.57874; stroke-linecap: butt; stroke-linejoin: bevel; marker: none; stroke-miterlimit: 4; stroke-dasharray: none; stroke-dashoffset: 0; stroke-opacity: 1; visibility: visible; display: inline; overflow: visible; fill: rgb(238, 255, 0);" id="path2893" d="M 553.72396 180 A 22.227922 22.227922 0 1 1  509.26811,180 A 22.227922 22.227922 0 1 1  553.72396 180 z"/>
    <path d="M 545.5671 180 A 14.071068 14.071068 0 1 1  517.42496,180 A 14.071068 14.071068 0 1 1  545.5671 180 z" id="path2891" style="opacity:1;color:black;fill:none;fill-opacity:1;fill-rule:evenodd;stroke:black;stroke-width:4.25196838;stroke-linecap:butt;stroke-linejoin:bevel;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;visibility:visible;display:inline;overflow:visible"/>
  </g>
  <g id="sunMnIcn" transform="matrix(0.26522, 0, 0, 0.272001, 11.130922, 74.802582)" style="">   
      <path style="stroke: black; stroke-miterlimit: 4; stroke-dasharray: none; stroke-opacity: 1; display: inline; fill: rgb(251, 255, 0); stroke-width: 2.60749px;" d="M 208.147 145.626 C 198.366 146.345 191.012 154.867 191.731 164.648 C 192.451 174.429 200.973 181.783 210.753 181.064 C 211.687 180.995 212.204 180.85 213.088 180.645 C 207.226 177.414 203.457 171.38 202.93 164.212 C 202.373 156.638 206.012 149.713 211.883 145.738 C 210.667 145.571 209.42 145.532 208.147 145.626 Z" id="path-2"/> 
  </g>'.PHP_EOL;

        for ($n = 0; $n< 12; $n++) {
          $rotate       = round( ($n * 360 /24),1);
          $return .= '  <line style="stroke: '.$dial_txt.';" x1="19" y1="65" x2="111" y2="65" transform="rotate('.$rotate.' 65 65)"></line>'.PHP_EOL;
        }
        $return .= '  <circle id="sunWhite" style="stroke-width: 0;  fill: '.$white_clr.';" cx="65" cy="65" r="44"></circle>';

} // eo SVG extra
$return .= '
  <circle id="sun_Pntr" style="fill: '.$sun_color.';" cx="'.(62 + $rad).'" cy="65" r="6" transform="rotate('.$rotate2.' 65 65) "></circle>
</svg>'; 
echo $return;
}