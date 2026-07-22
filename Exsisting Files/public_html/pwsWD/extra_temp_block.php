<?php $scrpt_vrsn_dt  = 'extra_temp_block.php|01|2020-11-02|';  # release 2012_lts
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
# -------------  texts and weather values to use
#
$hum_l  = 'Humidity';
$tmp_l  = 'Temperature';
#
$t_low_C= array(20,20,20,20,20,20,20,20,20);  # lower temps get color $t_lo_c = 'blue';
$t_hgh_C= array(30,30,30,30,30,30,30,30,30);  # higher  temps get color $t_hi_c = 'red';
$t_low_F= array(70,70,70,70,70,70,70,70,70);  # lower temps get color $t_lo_c = 'blue';
$t_hgh_F= array(90,90,90,90,90,90,90,90,90);  # higher  temps get color $t_hi_c = 'red';
if ($weather['temp_units'] <> 'F')
     {  $t_low  = $t_low_C;  $t_hgh  = $t_hgh_C; }
else {  $t_low  = $t_low_F;  $t_hgh  = $t_hgh_F; }

$h_low  = array(40,40,40,40,40,40,40,40,40);  # for humidity similar as temps
$h_hgh  = array(80,80,80,80,80,80,80,80,80);
#
$t_lo_c = $t_hi_c = $h_lo_c = $h_hi_c = false; //we set all to high-low colors off first
# now define colors to be used for to-high / low temps / hums
# If you do not want to use those colors, add a #  at the first postion
#
$t_lo_c = '#01A4B4';  # text color to use for low temp. 
$t_hi_c = '#FF7C39';   # text color to use for high temp
$h_lo_c = '#01A4B4';   # same for humidity
$h_hi_c = '#FF7C39';   #
#
$name   = array();
$name[] = 'extra_tmp1';
$name[] = 'extra_tmp2';
$name[] = 'extra_tmp3';
$name[] = 'extra_tmp4';
$name[] = 'extra_tmp5';
$name[] = 'extra_tmp6';
$name[] = 'extra_tmp7';
$name[] = 'extra_tmp8';
# -----------------------
$t_key  = array();
$t_key[]= 'extra_tmp1';
$t_key[]= 'extra_tmp2';
$t_key[]= 'extra_tmp3';
$t_key[]= 'extra_tmp4';
$t_key[]= 'extra_tmp5';
$t_key[]= 'extra_tmp6';
$t_key[]= 'extra_tmp7';
$t_key[]= 'extra_tmp8';
$h_key  = array();
$h_key[]= 'extra_hum1';
$h_key[]= 'extra_hum2';
$h_key[]= 'extra_hum3';
$h_key[]= 'extra_hum4';
$h_key[]= 'extra_hum5';
$h_key[]= 'extra_hum6';
$h_key[]= 'extra_hum7';
$h_key[]= 'extra_hum8';
#
$lines  = 8;
# ------------------------------test values
# $weather['extra_tmp1']  = 40.5;  $name[0] = 'gym';
# $weather['extra_tmp2']  = 20.5;  $name[1] = 'workshop';
# $weather['extra_tmp3']  = 29.5;  $name[2] = 'greenhouse';
# $weather['extra_tmp4']  = 10.5;  $name[3] = 'wine cellar';
#
# $weather['extra_hum1']  = 30.2;
# $weather['extra_hum2']  = 40.2;
# $weather['extra_hum3']  = 79.2;
# $weather['extra_hum4']  = 83.2;
# ------------------------------test values
#

$count  = 0;
$string = '';
for ($n = 0; $n < $lines; $n++) {
        $exists = 0;
        $key    = $t_key[$n];
        if (!array_key_exists($key,$weather)) {$t_key[$n] = false;} else {$exists++;}
        $key    = $h_key[$n];
        if (!array_key_exists($key,$weather)) {$h_key[$n] = false;} else {$exists++;}
        if ($exists > 0) {$count++;}
} 
#
if ($count == 0)
     {  echo '<small style="color: red;">Extra sensors  not available, script ends</small>';
        return;}
if ($count < 5) {$fnt_size = ' font-size: 14px; ';} else {$fnt_size = ' font-size: 12px; ';}
#
$hum_l  = lang($hum_l).' %';
$tmp_l  = lang($tmp_l).'&deg;'.$weather['temp_units'];
#
# ---------------------  generate the HTML
# ----     date time of last livedata file
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content">'.PHP_EOL;
#
#echo '<pre>'.print_r($t_key,true).print_r($h_key,true).print_r($weather,true); exit;

echo '<table style="font-size:10px; width: 100%; padding: 2px; text-align: center; height: 154px; border-collapse: collapse;">
<tr style="height: 16px; border-bottom: 1px grey solid; "><th>'.$tmp_l.'</th><th>&nbsp;</th><th>'.$hum_l.'</th></tr>'.PHP_EOL;
for ($n = 0; $n < $lines; $n++) {
        $key_t  = $t_key[$n];
        $key_h  = $h_key[$n];  
        if ($key_t == false &&  $key_h == false) {continue;} 
        $tmp = $hum = $t_clr = $h_clr = '';
        if ($key_t <> false) 
             {  $tmp    = $weather[$key_t];
                if     ($tmp < $t_low[$n] && $t_lo_c <> false) 
                     {  $t_clr = ' color: '.$t_lo_c.';';}
                elseif ($tmp > $t_hgh[$n] && $t_hi_c <> false) 
                     {  $t_clr = ' color: '.$t_hi_c.';';}
                }
        if ($key_h <> false) 
             {  $hum    = $weather[$key_h];
                if     ($hum < $h_low[$n] && $h_lo_c <> false) 
                     {  $h_clr = ' font-weight: bold; color: '.$h_lo_c.';';}
                elseif ($hum > $h_hgh[$n] && $h_hi_c <> false)  
                     {  $h_clr = ' font-weight: bold; color: '.$h_hi_c.';';}
                }
        
        echo '<tr style="border-top: 1px grey solid; "><td style="  font-weight: bold; '.$fnt_size.$t_clr.'">'.$tmp.'</td><td>'
                .lang($name[$n]).'</td><td style=" font-weight: bold;'.$fnt_size.$h_clr.'">'.$hum.'</td></tr>'.PHP_EOL;         
}        
echo '</table>'.PHP_EOL;


# ----------------   end of module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}

