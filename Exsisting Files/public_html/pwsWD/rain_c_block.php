<?php  $scrpt_vrsn_dt  = 'rain_c_block.php|01|2021-11-30|';  # max rain + last rain +  removed unit + type unit + 500 error + snow | release 2012_lts
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
#-----------------------------------------------
$normal_max_rain = 20;  // in mm ! expected max rain on normal rainy days # 2021-08-30
$no_rain= 'silver;';
$mm_rain= '#01a4b4;';
$leaf_nr= false; 
$leaf_nr= 1; 
$snow_max_temp = 5; // degrees above freezing, if temp = higher no snow values are shown 
# ------------------------- translation of texts
$unit_l = lang($weather['rain_units']);
$mnth_l = lang(date('F'));
$lhr_l  = lang('Last Hour');
$ystd_l = lang('Yesterday');
$rate_l = lang('Rate');
$et_l   = lang('ET');
$leaf_l = lang('Leaf wetness');
#
$unit_s  = ''; # <span>'.$unit_l.'</span>';
#
if (!isset ($weather['rain_today']) ) { $weather['rain_today']  = 0; }
$normal_max_rain = (float)  $normal_max_rain;    # 2021-08-30
if (trim($weather['rain_units']) <> 'mm' ) 
     {  $normal_max_rain = convert_precip (20,'mm',$weather['rain_units']);}
# ------------------ TEST VALUES
# $weather['rain_today'] = 0.9;
# $weather['rain_today'] = 38.5;
# ------------------ TEST VALUES
#
# calculate water level based on average < - > actual rain
#
$value  = (float) $weather['rain_today'];
if ( $value > $normal_max_rain )
     {  $empty  = 0;
        $water  = 100;}
elseif ($value == 0)
     {  $empty  = 100;
        $water  = 0;}
else {  $water  = (int) floor (100 *  $value / $normal_max_rain);
        $empty  = 100 -  $water;}
#
function rainnr ($value)
     {  global $dec_rain;
        return number_format ($value,$dec_rain,'.','');}        # 2019-12-29

# --------------------------------------
# ---------------          generate html
#
# ---------------- the date time
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;
#       
$count  = 0;
if (check_value ('rain_year') <> false)    
     {  if ($weather['rain_year'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;}  // border-right:'.$clr.'"
        echo '<div class="PWS_div_left" style="border-right-color: '.$clr.';">'
        .date('Y').'<br /><b >'
        .rainnr ($weather['rain_year']).'</b>'
        .$unit_s.'</div>'.PHP_EOL;}
if (check_value ('rain_month') <> false)    
     {  if ($weather['rain_month'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;}
        echo '<div class="PWS_div_left" style="border-right-color: '.$clr.';">'
        .$mnth_l.'<br /><b >'
        .rainnr ($weather['rain_month']).'</b>'
        .$unit_s.'</div>'.PHP_EOL;}
if (check_value ('rain_yday') <> false)    
     {  if ($weather['rain_yday'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;} 
        echo '<div class="PWS_div_left" style="border-right-color: '.$clr.';">'
        .$ystd_l.'<br /><b >'
        .rainnr ($weather['rain_yday']).'</b>'
        .$unit_s.'</div>'.PHP_EOL;}
echo '</div>
<!-- END of left values -->'.PHP_EOL;       
#
# ----------------  middle area
echo '<!-- middle part with bucket -->
<div class="PWS_middle">
    <div class="PWS_bucket" style="position: absolute; margin: 10px 15px;">
        <div class="empty" style="height: '.(int) $empty.'px;"></div>
        <div class="water" style="height: '.(int) $water.'px;"></div>
    <div class="orange" 
        style="position: inherit; width: 82px; top: 30px; left: 8px;  
        border-radius: 3px; border: 1px solid grey; 
        background-color: lightgray;">
        <b style="font-size: 20px; color: '.$mm_rain.'">'.rainnr ($weather['rain_today']).'</b>
    </div>
    </div>
</div>
<!-- END of middle part with bucket -->'.PHP_EOL;
#
# ---------------- right column
$rght_cnt       = 0;
$snw_pos        = true;
$tmpS           = $weather['temp'];
if ($tempunit <> 'C') { $tmpS = round (5*($tmpS -32)/9);} #### 2021-01-02
#
if ($tmpS > $snow_max_temp) { $snw_pos = false;}    ######   only at lower temps
if (! isset ($snow_show) || $snow_show == "none" )  ## in easyweather
     {  $snw_pos = false;}  
#
$cnt_snw = 0;                           ## how much room for snow info
if ($snw_pos == true 
     && ( check_value ('rain_lasthour') == false 
          ||  $weather['rain_lasthour'] == 0  ) )
     {  $cnt_snw++; }                   ## there is no rain lasthour 
#
if ($snw_pos == true 
     && ( check_value ('rain_rate') == false 
          ||  $weather['rain_rate'] == 0  ) )
     {  $cnt_snw++; }                   ## there is no rain lasthour 
#
if ($snw_pos == true 
     && !array_key_exists('snow_depth',$weather) )
     {  $snw_pos = false;}
# how much snow values are available, at least we have should have snow_depth
if ($snw_pos == true)
     {  $snow_items     = 1;
        if (array_key_exists('snow_today', $weather) ) { $snow_items++;}
        if (array_key_exists('snow_yday',  $weather) ) { $snow_items++;}
        if (array_key_exists('snow_season',$weather) ) { $snow_items++;}
        if (array_key_exists('snow_last',  $weather) ) { $snow_items++;}
        }    
# TEST
#$snow_items = 1;
#
if ($snw_pos  && $cnt_snw > 1 && $snow_items > 1) 
     {  if ($weather['snow_today'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;} 
        if ($rain_his == 'mm') { $snw_unit = 'cm';} else { $snw_unit = 'inch';}
        echo '<!-- right values with snow-->
<div class="PWS_right" style="height: 114px;">'.PHP_EOL;
        echo '<div class="PWS_div_right" style="height: 104px; border-left-color: '.$clr.';">';

        echo '<b>'.lang('Snow').'</b><small> ('.lang($snw_unit).')</small><br />'.PHP_EOL;
        echo lang('Height').': <b>'.$weather['snow_depth'].'</b><br />';

        echo '<br />';
        echo '<b>'.lang('Snowfall').'</b><br />'.PHP_EOL;
        if (array_key_exists('snow_last', $weather) ) 
             {  echo lang('Last')       .': '.$weather['snow_last'].'<br />';  
                $compare        = 0;}
        else {  $compare        = -1;}
        
        if (array_key_exists('snow_today', $weather) 
           && $weather['snow_today'] <> $compare)
             {  echo lang('Today')      .': <b>'.$weather['snow_today'].'</b><br />'; }
        if (array_key_exists('snow_yday', $weather)
           && $weather['snow_yday'] <> $compare)
             {  echo lang('Yesterday')  .': <b>'.$weather['snow_yday'].'</b><br />';}     
        if (array_key_exists('snow_season', $weather)
           && $weather['snow_season'] <> $compare)
             {  echo lang('Season')     .': <b>'.$weather['snow_season'].'</b><br />';}
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<!-- END of right values -->'.PHP_EOL;}

else {  echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
        if ($rain_his == 'mm') { $snw_unit = 'cm';} else { $snw_unit = 'inch';}  #### 2021-01-03
        if (check_value ('rain_lasthour') <> false)    
             {  if ($weather['rain_lasthour'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;} 
                echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .$lhr_l.'<br /><b >'
                .rainnr ($weather['rain_lasthour']).'</b>'
                .$unit_s.'</div>'.PHP_EOL;
                $rght_cnt++;}
        if ($rainrate <> '/h')
             {  $rate = number_format($weather['rain_rate']/60,4);}
        else {  $rate = number_format($weather['rain_rate'],3);}
        if (check_value ('rain_rate') <> false)   
             {  if ($weather['rain_rate'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;} 
                echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .$rate_l.lang($rainrate).'<br /><b >'
                .$rate.'</b>'
                .$unit_s.'</div>'.PHP_EOL;
                $rght_cnt++;}
        if ($snw_pos == true)
             {  if ($weather['snow_depth'] == 0) { $clr = $no_rain;} else { $clr = $mm_rain;} 
                echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .'<b>'.lang('Snow').'</b><small> ('.lang($snw_unit).')</small><br />'
                .lang('Height').': <b>'.$weather['snow_depth'].'</b>'
                .'</div>';
                $rght_cnt++;}
        if ($rght_cnt < 3 && check_value ('et_today') <> false)
             {  echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .$et_l.'<br /><b >'
                .$weather['et_today'].'</b>'
                .'</div>'.PHP_EOL;
                $rght_cnt++;}
        if ($rght_cnt < 3 
             && $leaf_nr <> false 
             && check_value ('leaf_wetness'.$leaf_nr) <> false)
             {  $leaf   = 'leaf_wetness'.$leaf_nr;
                echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .$leaf_l.'<br /><b >'
                .$weather[$leaf].'</b>'
                .'</div>'.PHP_EOL;
                $rght_cnt++;}
        if ($rght_cnt < 3
             && array_key_exists ('last_rained', $hist) )
             {  $text           = '';
                $clr            = $no_rain;
                $value          = $hist['last_rained'];
                $r_today        = date('Ymd',time());
                $r_last         = date('Ymd',$value);
                if ($r_today == $r_last) 
                     {  $text   = lang('Today');
                        $clr    = $mm_rain;}
                else {  $text   = date ($dateFormat, $value);}
                echo '<div class="PWS_div_right" style="border-left-color: '.$clr.';">'
                .lang('Last rain').'<br /><b >'
                .$text.'</b>'
                .'</div>'.PHP_EOL;
                $rght_cnt++;}     
        echo '<!-- END of right values -->'.PHP_EOL;
        echo '</div>'.PHP_EOL;}
#
# ----------------   end of html
echo '</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
