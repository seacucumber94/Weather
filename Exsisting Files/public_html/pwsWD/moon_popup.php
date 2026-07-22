<?php  $scrpt_vrsn_dt  = 'moon_popup.php|01|2022-11-22|';  # moon set today + PHP 8.1 + 24 hr light dark release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color          = "#FF7C39";    // head line
$start_tomorrow = 0;            // 2022-08-12 public domain scripts somtimes fail if today moonrise/set is used
$start_tomorrow = 24*3600;      // 2022-08-12 set comment this line if you ewant to use today set/rise
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
     {  ini_set('display_errors', 0); error_reporting(0);}
else {  ini_set('display_errors', 1); error_reporting(1);}
#
header('Content-type: text/html; charset=UTF-8'); 
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# -------------------------------- test settings
#$lat = -(float) "78.7836";
#$lat =  (float) "50.7836";
#$lon = (float) "50.5278";
#$lon = (float) "4.5278";
#-----------------------------------------------
#                                    date format
# https://www.timeanddate.com/moon/belgium/brussels
#-----------------------------------------------
if ($clockformat == '24')  
     {  $date_time_frmt = 'D j M';}
else {  $date_time_frmt = 'D M j';}
# ------------------------- translation of texts
#
$ltxt_url       = lang('Moon Phase / Sun-Moon  Rise-Set Information');
$ltxt_clsppp    = lang('Close');
$ltxt_hd1       = lang('Moonphase');
$ltxt_hd2       = lang('SunRise/Set');
$ltxt_hd3       = lang('Moon Rise/Set Information');
$ltxt_hd4       = lang('Sun Rise/Set Information');
$ltxt_mnrs      = lang('Moonrise');
$ltxt_mnst      = lang('Moonset');
$ltxt_snrs      = lang('Sunrise');
$ltxt_snrs2     = lang('Sun rose at');
$ltxt_snst      = lang('Sun will set');
$ltxt_snst2     = lang('Sun set');

$ltxt_dl        = lang('Daylight');
$ltxt_drk       = lang('Darkness');
$ltxt_nxtfull   = lang('Next Full Moon');
$ltxt_nxtnew    = lang('Next New Moon');
$ltxt_mnccle    = lang('Current Moon cycle is'); 
$ltxt_mnccle2   = lang('days old');
$ltxt_tmrrw     = lang('Tomorrow');
$ltxt_td        = lang('Today');
$ltxt_dbfr      = lang('day before');
$ltxt_tllsnrs   = lang('Till Sunrise');
$ltxt_dlght     = lang('Of Daylight');
$ltxt_hrs       = lang('hrs ');
$ltxt_mins      = lang('mins ');
$ltxt_secs      = lang('secs ');
$ltxt_today     = trans_long_date (date($date_time_frmt));
$ltxt_luninance = lang('Luminance'); 
#-----------------------------------------------
#                                      functions
#-----------------------------------------------

function PWS_moon_info ()
     {  global $lat, $lon, $start_tomorrow;
        include './others/MoonPhase.php';
        include './others/moon.php';
        $now    = time();
        list ($month, $day,$year) = explode ('|', date ('n|j|Y', $now + $start_tomorrow)); # 2022-08-12
        $object = (array) Moon::calculateMoonTimes($month, $day, $year, $lat, $lon);
        $result['moonrise']     = $object['moonrise'];
        $result['moonset']      = $object['moonset']; #echo '<pre>'.'<br />'.date('c',$object['moonrise']).'<br />'.date('c',$object['moonset']).print_r($object, true); 
#
        $tomorrow       = $now + 24*3600 + $start_tomorrow;  # 2022-08-12
        list ($month, $day,$year) = explode ('|', date ('n|j|Y',$tomorrow));  
        $object = (array) Moon::calculateMoonTimes($month, $day, $year, $lat, $lon);
        $result['moonrise2']    = $object['moonrise'];
        $result['moonset2']     = $object['moonset'];  #echo '<pre>'.'<br />'.date('c',$object['moonrise']).'<br />'.date('c',$object['moonset']).print_r($object, true);       
        return $result;}
$arr_mn_nf      = PWS_moon_info ();
$ltxt_phase     = lang($arr_mn_nf['phase_name']); 
$age    = $arr_mn_nf['age'];
$now    = time();
#-----------------------------------------------
#                                   calculations 
#  ----------------------------            BOX 1
# calculate moon info 
if ($age > 14)  // after 14 days less light
     {  $light  = abs($age - 29);  $turn   = 180;}
else {  $light  = $age;            $turn   = 0;}
$mn_hd  = 107 - $light * 8;     // to draw curve on moon
if ($lat < 0) { $turn += 180;}  // southern hemisphere the moon looks different
#
#
#  ----------------------------       BOX 2 /  3
# load 3 days of sunrise / set info
$result1        = date_sun_info(time(), $lat, $lon);   #echo '<pre>'.time().print_r($result1,true);
$nextday        = time() + 24*60*60;
$result2        = date_sun_info($nextday,$lat, $lon); # echo '<pre>'.print_r($result2,true);
$prevday        = time() - 24*60*60;
$result0        = date_sun_info($prevday,$lat, $lon); # echo '<pre>'.print_r($result0,true).print_r($result1,true);
# todays light dark periods
$light1         = $result1['sunset'] - $result1['sunrise'];
$daylight1      = gmdate('G ',$light1).$ltxt_hrs.gmdate(' i ',$light1).$ltxt_mins;
$dark1          = $result2['sunrise'] - $result1['sunset'];
$daydark1       = gmdate('G ',$dark1).$ltxt_hrs.gmdate(' i ',$dark1).$ltxt_mins;
# tomorrow light period
$light2         = $result2['sunset'] - $result2['sunrise'];
$daylight2      = gmdate('G ',$light2).$ltxt_hrs.gmdate(' i ',$light2).$ltxt_mins;
# next rise sets
$nextrise1      = $result1['sunrise'];
$nextset1       = $result1['sunset'];
$nextrise2      = $result2['sunrise'];
$nextset2       = $result2['sunset'];
# yesterdays light
$light0         = $result0['sunset'] - $result0['sunrise'];
# calculate  + or - daylight today <> yesterday
$value          = $light1 - $light0;
if ($value > 0) {$sign = '+ ';} else {$sign = '- '; $value = abs ( $value);}
$min_plus1      = $sign.gmdate('i ', $value).$ltxt_mins.gmdate('s ', $value).$ltxt_secs;
# calculate  + or - daylight today <> tomorrow
$value          = $light2 - $light1;
if ($value > 0) {$sign = '+ ';} else {$sign = '- '; $value = abs ( $value);}
$min_plus2      = $sign.gmdate('i ', $value).$ltxt_mins.gmdate('s ', $value).$ltxt_secs;
# calculate  + or - nighttime today <> yesterday
$dark0          = $result1['sunrise'] - $result0['sunset'];
$value          = $dark1 - $dark0; # echo $value; exit;
if ($value > 0) {$sign = '+ ';} else {$sign = '- '; $value = abs ( $value);}
$plus_min1      = $sign.gmdate('i ', $value).$ltxt_mins.gmdate('s ', $value).$ltxt_secs;
#
$now            = time(); # echo '<pre>'.print_r($result1,true).'</pre>'; #  exit;
#
# calculate large times box2
if ((int) $result1['sunset'] > 100  ) 
       { if ($now < $nextrise1) 
            {   $time   = $nextrise1 - $now;
                $hrs    = gmdate ('G',$time);
                $min    = gmdate ('i',$time);
                $txt    = $ltxt_tllsnrs;}
        elseif ($now < $nextset1)
            {   $time   = $nextset1 - $now;
                $hrs    = gmdate ('G',$time);
                $min    = gmdate ('i',$time);
                $txt    = $ltxt_dlght;}
        else {  $time   = $nextrise2 - $now;
                $hrs    = gmdate ('G',$time);
                $min    = gmdate ('i',$time);
                $txt    = $ltxt_tllsnrs;} }
else {  if (!$result1['sunset'] === true)
              { $txt = $ltxt_drk ;}
        else  { $txt = $ltxt_dl ;}
        $hrs    = '24';
        $min    = '00';
        }
                
# 
# generate rows sun box 4
$rows_sun       = array();
$rows_sun[]     = '<b class="orange">'.$ltxt_td.'</b>:';
if ($now < $nextrise1) 
     {  $txt2   = $ltxt_snrs; } // today still dark
else {  $txt2   = $ltxt_snrs2; }
$rows_sun[]     = $txt2.' '.set_my_time($nextrise1,true);
$rows_sun[]     = $ltxt_dl.' '.$daylight1.' ('.$min_plus1.')';
if ($now < $nextset1)
     {  $txt2   = $ltxt_snst; } // today still dark
else {  $txt2   = $ltxt_snst2; }
$rows_sun[]     = $txt2.' '.set_my_time($nextset1, true);
$rows_sun[]     = $ltxt_drk.' '.$daydark1.' ('.$plus_min1.')';

$rows_sun[]     = '<br /><b class="orange">'.$ltxt_tmrrw.'</b>:';
$rows_sun[]     = $ltxt_snrs.' '.set_my_time($nextrise2,true);
$rows_sun[]     = $ltxt_dl.' '.$daylight2.' ('.$min_plus2.')';
$rows_sun[]     = $ltxt_snst.' '.set_my_time($nextset2,true);#echo '<pre>'.print_r($rows_sun,true); exit;
#
# generate rows BLOCK 3
$rows_mn        = array(); #echo '<pre>'.print_r($arr_mn_nf,true); exit;
$long_now       = ''; // date long last printed
$arr            = array();
$key            = $arr_mn_nf['moonrise']; 
$value          = $ltxt_mnrs;
$arr[$key]      = $value;
$key            = $arr_mn_nf['moonrise2']; 
$arr[$key]      = $value;
$key            = $arr_mn_nf['moonset']; 
$value          = $ltxt_mnst;
$arr[$key]      = $value;
$key            = $arr_mn_nf['moonset2']; 
$arr[$key]      = $value;
ksort($arr); #echo '<pre>'.print_r($arr,true).'</pre></pre></pre></pre></pre>'; 
$prev_long_date = '';
foreach ($arr as $date => $type) 
     {  $check = date('is',$date);
        if ($check == '0000' ) {continue;} // problem with missingrise set
        $long_date      = date($date_time_frmt,$date);
        if ($long_date <> $prev_long_date)
             {  $prev_long_date = $long_date;
                $rows_mn[]      = '<b class="orange">'.trans_long_date($long_date).'</b>';}      
        $rows_mn[]      = $type.': '.set_my_time($date, true);}
# next main moon faces
if ($now < $arr_mn_nf['new_moon'] )     { $value1 = $arr_mn_nf['new_moon'];}  else { $value1 = $arr_mn_nf['next_new_moon'];}
$line1          = $ltxt_nxtnew.': '.trans_long_date(date($date_time_frmt,(int)$value1));
if ($now < $arr_mn_nf['full_moon'] )    { $value2 = $arr_mn_nf['full_moon'];} else { $value2 = $arr_mn_nf['next_full_moon'];}
$line2          = $ltxt_nxtfull.': '.trans_long_date(date($date_time_frmt,(int)$value2));
if ($value1 < $value2) 
     {  $rows_mn[]      = '<br />'.$line1;       $rows_mn[]      = $line2;}
else {  $rows_mn[]      = '<br />'.$line2;       $rows_mn[]      = $line1;}
# moon age
$rows_mn[]   = $ltxt_mnccle.' '.round($age).' '.$ltxt_mnccle2;
#echo '<pre>'.print_r($rows_mn,true); exit; 
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style()
.'</head>
<body class="dark">
<div class="PWS_module_title font_head" style="width: 100%; " >
'.$close.
'      <span style="color: '.$color.'">'.$ltxt_url.'</span> 
</div>    
<div class="PWS_weather_container"><!-- toprow -->
<div class="PWS_weather_item" style="position: relative;"><!-- weatheritem moon -->
    <div class="PWS_module_title">
        <div class="title">'.$ltxt_hd1. ' '.$ltxt_today.'
        </div>
    </div>';

$lighted= round($arr_mn_nf['illumination'],3); // percent (0.0 - 1.0)
if ($lighted > 0.0  && $lighted < 0.04 ) { $lighted = 0.04;} // to make very small lighted part it visible
if ($lighted > 0.95 && $lighted < 1.0 ) {  $lighted = 0.96;} // same for remaining dark part.
#
$color_light    = 'rgba(255, 255, 0, 0.70);';   // yellow used for lighted part
$color_dark     = 'rgba(50, 50, 50, 1);';    // 20% black on image for shadow part
#
if ($arr_mn_nf['phase'] < 0.5)        // new moon to full moon (0 - 0.5)
     {  $color1 = $color_light; 
        $color2 = $color_dark;
        $pos    = 350 -  $lighted * 400;}    
else {  $color2 = $color_light; 
        $color1 = $color_dark;
        $pos    = $lighted * 400 - 50;}      
# 
$tilt = 0;       
#
if ( $lat < 0)                  // rotate for the south part of the earth
     {  $south = 'transform: rotate('.(180 + $tilt).'deg);'; } // tilt is 20 degrees to look more normal
else {  $south = 'transform: rotate('. $tilt.'deg);'; }
#
$tcolor         = 'white';
$shadow         = 'text-shadow: 2px 2px 10px black;'; 
#
$size = 110; 
$class= 'large';

if ($arr_mn_nf['illumination'] < 0.01 || $arr_mn_nf['illumination'] > 0.99) 
     {  $luminance      = round(100*$arr_mn_nf['illumination'],1);}
else {  $luminance      = round(100*$arr_mn_nf['illumination'],0);}
#
echo '
    <div style="width: 130px;  margin: 0px auto;">
        <div style="width: '.$size.'px;  margin: 0 auto; margin-top: 10px;">
            <svg width="'.$size.'" height="'.$size.'" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg" 
                        xmlns:xlink="http://www.w3.org/1999/xlink" style="'.$south.'">
                <path d="M 150 0 C '.$pos.' 10 '.$pos. ' 290 150 300  350 290 350 10 150 0"  style="paint-order: fill;  fill: '.$color1.';" />
                <path d="M 150 0 C '.$pos.' 10 '.$pos. ' 290 150 300  -50 290 -50 10 150 0"  style="paint-order: fill;  fill: '.$color2.'; " /> 
            </svg>
        </div>
    </div>';


echo '
    <span class="large orange">'.$ltxt_phase.'</span>
    <br />
    <span class="large">'.$ltxt_luninance.' <span class="orange">'.round(100*$arr_mn_nf['illumination'],0).'%</span></span>
</div><!-- eo weatheritem moon -->
<div class="PWS_weather_item"><!-- weatheritem sun -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd2. ' '.$ltxt_today.'</div></div>
        <div class="xlow_item">
        <div style="padding-top: 30px;"><span class="xlarge"> '.$hrs.'</span> '.$ltxt_hrs.' <span class="xlarge" >: '.$min.'</span> '.$ltxt_mins.'</div>
        </div>
        <span class="large orange">'.$txt.'</span>
        </div><!-- eo weatheritem sun -->
    </div><!-- eo toprow -->
    <div class="PWS_weather_container"><!-- second row -->
        <div class="PWS_weather_item " style="position: relative;"><!-- weatheritem details moon -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd3.'</div></div>
        <div style="text-align: left; margin-left: 5px; font-size: 12px;">';
        foreach ($rows_mn as $line) {echo $line.'<br />';}
echo '
        </div>       
        </div><!-- eo weatheritem details moon -->
        <div class="PWS_weather_item " style="position: relative;"><!-- weatheritem details sun -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd4.'</div></div>
        <div style="text-align: left; margin-left: 5px; font-size: 12px;">';
if ((int) $result1['sunset'] > 100  )  
      { foreach ($rows_sun as $line) {echo $line.'<br />';} }
echo '
        </div>
        </div><!-- eo weatheritem details sun -->
    </div><!-- eo second row -->'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
function trans_long_date ($date)
     {  if (!isset ($to) )
             {  static  $to     = array();
                static  $from   = array ( 
                        'Apr ','Aug ','Dec ','Feb ','Jan ','Jul ','Jun ','Mar ','May ','Nov ','Oct ','Sep ',
                        'April','August','December','February','January','July','June','March','May','November','October','September',
                        'Mon ','Tue ','Wed ','Thu ','Fri ','Sat ','Sun ',
                        'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
                foreach ($from  as $txt) {$to[] = lang($txt);}
                }
        return str_replace ($from, $to, $date.' ');}   #### 2018-07-18
#       
#
# style is printed in the header 
function my_style()
     {  global $popup_css ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css

        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
