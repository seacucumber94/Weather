<?php $scrpt_vrsn_dt  = 'indoor_c_block.php|01|2020-11-02|';  # release 2012_lts
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
#             check if indoor values are present
#-----------------------------------------------
if (!array_key_exists ('temp_indoor',$weather) 
  || $weather['temp_indoor'] === 'n/a') {echo '<br />'.lang('No valid sensors found'); return;}
#-----------------------------------------------
#                script settings && translations
#-----------------------------------------------
#
$house_temp     = true; // true:  rounded temp in the house,  false: the house should be empty
#
$cbr_hgh_clr    =  '#d65b4a';  // marker high
$cbr_lw_clr     =  '#01a4b4';   // marker low
$cbr_nw_clr     =  '#9aba2f';   // marker current
#
$rise_l = lang('Rising') .' &uarr;';
$fall_l = lang('Falling').' &darr;'; 
$stdy_l = lang('Steady') .' &harr;';
$humi_l = lang('Humidity');
$tmp_i_l= lang('Indoor Temperature');
$feels_l= lang('Feels like');
$min_l  = lang('Min');
$max_l  = lang('Max');
$curt_l = lang('Current');
#
$b_clrs['extreme']      = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$d      = array();
$d[] = 'd=" M 7.00  8.01 C 17.00  8.00 27.00  8.00 37.00  8.00 C 37.00  8.75 37.00 10.25 37.00 11.00 C 27.00 11.00 17.00 11.00 7.00 11.00 C 7.00 10.25 7.00  8.75 7.00  8.01 Z" />';
$d[] = 'd=" M 7.00 12.00 C 17.00 12.00 27.00 12.00 37.00 12.00 C 37.00 13.67 37.00 15.33 37.00 17.00 C 27.00 17.00 17.00 17.00 7.00 17.00 C 7.00 15.33 7.00 13.67 7.00 12.00 Z" />';
$d[] = 'd=" M 7.00 18.00 C 17.00 18.00 27.00 18.00 37.00 18.00 C 37.00 19.67 37.00 21.33 37.00 23.00 C 27.00 23.00 17.00 23.00 7.00 23.00 C 7.00 21.33 7.00 19.67 7.00 18.00 Z" />';
$d[] = 'd=" M 7.00 24.00 C 17.00 24.00 27.00 24.00 37.00 24.00 C 37.00 25.67 37.00 27.33 37.00 29.00 C 27.00 29.00 17.00 29.00 7.00 29.00 C 7.00 27.33 7.00 25.67 7.00 24.00 Z" />';
$d[] = 'd=" M 7.00 30.00 C 17.00 30.00 27.00 30.00 37.00 30.00 C 37.00 31.67 37.00 33.33 37.00 35.00 C 27.00 35.00 17.00 35.00 7.00 35.00 C 7.00 33.33 7.00 31.67 7.00 30.00 Z" />';
$d[] = 'd=" M 7.00 36.00 C 17.00 36.00 27.00 36.00 37.00 36.00 C 37.00 37.67 37.00 39.33 37.00 41.00 C 27.00 41.00 17.00 41.00 7.00 41.00 C 7.00 39.33 7.00 37.67 7.00 36.00 Z" />';
$d[] = 'd=" M 7.00 42.00 C 17.00 41.99 27.00 42.00 37.00 42.00 C 37.00 43.67 37.00 45.33 37.00 47.00 C 27.00 47.00 17.00 47.00 7.00 47.00 C 7.00 45.33 7.00 43.67 7.00 42.00 Z" />';
$d[] = 'd=" M 7.00 48.00 C 17.00 48.00 27.00 48.00 37.00 48.00 C 37.00 49.67 37.00 51.33 37.00 53.00 C 27.00 53.00 17.00 53.00 7.00 53.00 C 7.00 51.33 7.00 49.67 7.00 48.00 Z" />';
$d[] = 'd=" M 7.00 54.00 C 17.00 54.00 27.00 54.00 37.00 54.00 C 37.00 55.67 37.00 57.33 37.00 59.00 C 27.00 59.00 17.00 59.00 7.00 59.00 C 7.00 57.33 7.00 55.67 7.00 54.00 Z" />';
$d[] = 'd=" M 7.00 60.00 C 17.00 60.00 27.00 60.00 37.00 60.00 C 37.00 61.67 37.00 63.33 37.00 65.00 C 27.00 65.00 17.00 65.00 7.00 65.00 C 7.00 63.33 7.00 61.67 7.00 60.00 Z" />';
$d[] = 'd=" M 7.00 66.00 C 17.00 66.00 27.00 66.00 37.00 66.00 C 37.00 67.67 37.00 69.33 37.00 71.00 C 27.00 71.00 17.00 71.00 7.00 71.00 C 7.00 69.33 7.00 67.67 7.00 66.00 Z" />';
$d[] = 'd=" M 7.00 72.00 C 17.00 72.00 27.00 72.00 37.00 72.00 C 37.00 73.67 37.00 75.33 37.00 77.00 C 27.00 77.00 17.00 77.00 7.00 77.00 C 7.00 75.33 7.00 73.67 7.00 72.00 Z" />';
#
$lvl_humid      = array(99,90,80,70,60,50,
                        45,40,30,20,10,-999);                        
$fll_humid      = array ('extreme', 'purple', 'red', 'orange', 'yellow', 'green', 
                         'green', 'green', 'yellow', 'purple', 'extreme', 'extreme' );
$lvl_feels      = array(999,40,30,28,26,24,
                        23,20,18,14,12,-999);                        
$fll_feels      = array ('extreme', 'purple', 'red', 'orange', 'yellow', 'green', 
                         'green', 'green', 'yellow', 'blue', 'blue', 'extreme' );
#                        
$svg_start      = '<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>'.PHP_EOL;
$svg_end        = '</svg>'.PHP_EOL;
#
if (!function_exists ('fill_bucket') ) {
function fill_bucket($value, $lvl, $colors )
     {  global $d, $b_clrs, $clr; 
        $string = $clr = '';
        for ($i = 0; $i < 12; $i++)
             {  if ($value <  $lvl[$i]) 
                     {  $clr1   =  'currentcolor';}
                else {  $key    = $colors[$i];
                        $clr1   = $b_clrs[$key];
                        if ($clr == '') {$clr = $clr1;}
                      } 
                $string .= ' <path fill="'.$clr1.'" opacity="1.0" '.$d[$i].PHP_EOL;
                } 
        return $string;
        } // eo fill_bucket                
}
#
if (!function_exists ('tempnr') ) {
function tempnr ($value)
     {  global $dec_tmp;
        return number_format ($value,$dec_tmp);}
}
#
# ----------- test values
#    $weather['humidity']         = 48;
#    $weather['humidity_indoor']  = 40;
#    $weather['humidity_trend']   = 'n/a';
#    $weather['temp_indoor']      = 24.8;
#    $weather['temp_indoor_feel'] = 24.4;
#
# ----------- test values
#
# ---------------------  generate the HTML
# ----     date time of last livedata file
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;
$arrow  = '';   # $weather['temp_humidity_trend'] = -1;   # for test
if (array_key_exists ('temp_indoor_trend',$weather) ) 
     {  $value  = (float) $weather['temp_indoor_trend'];
        if     ($value > 0) { $arrow  = ' &uarr;';}
        elseif ($value < 0) { $arrow  = ' &darr;';}}
echo '<div class="PWS_div_right" style="width: 58px; border-left-width: 1px;">'.PHP_EOL;
echo $feels_l.'<br />'.tempnr ($weather['temp_indoor_feel']).'&deg;'.$arrow.PHP_EOL;
echo '</div>'.PHP_EOL;
echo '<div class="PWS_bar">'.PHP_EOL; 
echo $svg_start;
# 2019-05-01
$tmp    = round($weather['temp_indoor_feel']);
if ($tempunit <> 'C') 
     {  $tmp  = round (5*($weather['temp_indoor_feel'] -32)/9);}
# 2019-05-01
echo fill_bucket($tmp,$lvl_feels, $fll_feels);
echo $svg_end;
echo '</div>'.PHP_EOL;

echo '</div><!-- END of left values -->'.PHP_EOL;       
#
# ----------------  middle area
echo '<!-- middle part  -->
<div class="PWS_middle" style="height: 132px;">
<!-- middle texts -->'.PHP_EOL;
if ($house_temp == true) 
     { echo '<div class="PWS_div_left" style=" border-right-width: 1px; border-color: transparent;"><br /></div>'.PHP_EOL;}
else {
echo '<div class="PWS_div_left" style=" border-right-width: 1px;">'.PHP_EOL;
echo $tmp_i_l.'<br />'.tempnr ($weather['temp_indoor']).'&deg;'.PHP_EOL;
echo '</div>'.PHP_EOL;}
echo '<div class="PWS_bar" style=" ">
<svg id="house" width="100px" height="100px" viewBox="0 0 300 300" fill="currentcolor" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="gradient-0" gradientUnits="userSpaceOnUse" x1="149.731" y1="20.819" x2="149.731" y2="271.715">
      <stop offset="0" style="stop-color: #bada55"/>
      <stop offset="1" style="stop-color: #758d29"/>
    </linearGradient>
  </defs>
  <path d="M 141.986 22.216 C 149.103 19.456 157.863 20.8 163.474 26.123 C 175.22 37.715 186.794 49.523 198.891 60.755 C 199.044 53.204 198.675 45.644 199.044 38.103 C 199.342 31.589 205.477 26.213 211.918 26.357 C 221.282 26.384 230.646 26.366 240.01 26.366 C 247.326 26.375 253.659 33.466 252.594 40.782 C 252.504 64.273 252.612 87.755 252.54 111.237 C 252.513 112.717 252.495 114.395 253.821 115.369 C 262.806 124.11 271.746 132.897 280.704 141.666 C 283.419 144.363 286.568 146.799 288.291 150.317 C 291.196 155.694 291.34 162.496 288.58 167.962 C 285.53 174.394 278.683 178.941 271.529 178.977 C 265.224 179.122 258.918 178.995 252.612 179.013 C 252.486 209.911 252.63 240.817 252.531 271.715 C 183.997 271.697 115.454 271.706 46.921 271.715 C 46.821 240.817 46.966 209.911 46.839 179.013 C 40.534 178.995 34.219 179.122 27.922 178.977 C 20.552 178.896 13.488 174.124 10.600999999999999 167.358 C 7.462 160.619 8.761 152.157 13.705 146.618 C 39.559 120.43 66.253 95.089 92.198 68.982 C 104.385 56.813 116.708 44.778 129.004 32.726 C 133.109 28.991 136.618 24.219 141.986 22.216 Z" style="stroke: url(#gradient-0); fill: rgb(192, 192, 192);"/>
  <path d="M 141.3 33.258 C 128.77 45.689 116.257 58.139 103.628 70.471 C 76.826 96.596 50.529 123.244 23.637 149.27 C 21.445 151.399 19.162 153.691 18.332 156.731 C 16.357 162.856 21.517 169.866 27.913 170.091 C 37.178 170.227 46.46 170.154 55.734 170.091 C 55.734 201.079 55.608 232.058 55.635 263.045 C 118.359 263.109 181.092 263.109 243.825 263.045 C 243.844 232.058 243.717 201.079 243.726 170.091 C 252.693 170.191 261.669 170.154 270.645 170.145 C 273.992 170.2 277.33 168.576 279.324 165.878 C 282.382 162.026 282.256 156.145 278.846 152.545 C 267.29 141.566 256.139 130.164 244.764 119.004 C 243.392 118.039 243.735 116.271 243.645 114.837 C 243.717 90.443 243.627 66.05 243.699 41.666 C 243.654 39.889 243.744 37.868 242.499 36.443 C 240.803 34.981 238.413 35.324 236.356 35.243 C 228.508 35.324 220.659 35.207 212.82 35.288 C 210.655 35.135 207.903 36.515 208.102 39.005 C 207.912 53.502 208.165 67.999 207.993 82.505 C 204.232 79.42 200.984 75.793 197.484 72.437 C 184.15 59.302 170.998 45.987 157.556 32.961 C 153.217 28.576 145.495 28.784 141.3 33.258 Z" style="fill: '.$clr.';"/>
</svg>
</div>'.PHP_EOL;
if ($house_temp == true) 
     { echo '<div class="" style ="position: relative; top: -60px; margin: 0 auto; text-align: center;
  /*  height: 40px; */
    color: #fff;
    line-height: 13px;
    font-size: 30px;
    font-family: Helvetica,sans-seriff;
    font-weight: 400;">
<br />'.round($weather['temp_indoor']).'&deg;
</div>'.PHP_EOL;}
echo ' </div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ---------------- right column
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
$arrow  = '';   # $weather['temp_humidity_trend'] = -1;   # for test
if (array_key_exists ('temp_humidity_trend',$weather) ) 
     {  $value  = (float) $weather['temp_humidity_trend'];
        if     ($value > 0) { $arrow  = ' &uarr;';}
        elseif ($value < 0) { $arrow  = ' &darr;';}}
echo '<div class="PWS_div_left" style="width: 58px; border-right-width: 1px;">'.PHP_EOL;
echo $humi_l.'<br />'.$weather['humidity_indoor'].'%'.$arrow.PHP_EOL;
echo '</div>'.PHP_EOL;
echo '<div class="PWS_bar">'.PHP_EOL; 
echo $svg_start;
echo fill_bucket($weather['humidity_indoor'],$lvl_humid, $fll_humid);
echo $svg_end;
echo '</div>'.PHP_EOL;

echo '</div><!-- END of right values -->'.PHP_EOL; 
#
# ----------------   end of module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}

