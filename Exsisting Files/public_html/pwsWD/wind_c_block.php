<?php $scrpt_vrsn_dt  = 'wind_c_block.php|01|2022-11-22|';  # php 8 windavg + PHP 8.1 + null in winddir + php8 | release 2012_lts | color black | td 50%
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
# -----------------------------  script settings
#
$use_windrun    = true;
#
if (!isset ($KISS)) {$KISS = false;}
$my_KISS        = $KISS;    
#$my_KISS        = false;
#
# ------------------------- translation of texts
$avg_lc = lang('Avg ');
$avg_l  = lang('Average');
$gust_l = lang('Gust');
$wndr_l = lang('Wind Run');
$frc_l  = lang('Force');
$bft_l  = lang('Bft');

$kts_l  = lang('Kts');
$wnd_l  = lang('Wind');
$units_l= lang($weather['wind_units']);
$max_l  = lang('Max');
$calm_l = lang('Calm');
$dir_l  = lang('Direction');
$run_l  = lang('Wind run');
#
$compass        = windlabel ($weather['wind_direction']) ;      # 2021-12-08
$compass_avg    = windlabel ( (int) $weather['wind_direction_avg']);   # 2022-05-10
$cmp_l          = lang($compass);
$cmp_avg_l      = lang($compass_avg); 
$beaufort0= '<svg id="bft00" width="9px" height="9px" viewBox="0 0 42 42" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 17.043 1.641 C 23.433 0.35 30.425 2.322 35.007 7.003 C 38.305 10.648 40.936 15.272 40.9 20.314 C 41.517 30.81 32.355 40.789 21.78 40.831 C 11.426 41.583 1.824 32.754 1.171 22.527 C 0.285 12.932 7.575 3.386 17.043 1.641 M 17.121 7.435 C 10.958 9.137 6.362 15.449 6.823 21.853 C 7.157 29.059 13.731 35.442 21.029 35.2 C 28.092 35.42 34.454 29.47 35.177 22.541 C 35.908 16.449 32.028 10.222 26.383 7.946 C 23.468 6.733 20.149 6.641 17.121 7.435 Z" style=""/>
  <path fill="#fff" stroke="#fff" stroke-width="0.09375" opacity="1.00" d="M 19.448 11.408 C 24.434 10.947 29.001 15.592 28.427 20.564 C 28.086 25.386 23.087 29.131 18.363 28.039 C 13.122 27.415 9.739 20.996 12.179 16.323 C 13.434 13.443 16.328 11.557 19.448 11.408 Z" style=""/>
</svg>';
$beaufort1= '<svg id="bft01" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 73.92 17.875 C 77.12 14.085 82.8 13.435 87.34 14.795 C 91.48 15.995 93.99 19.835 96 23.375 L 96 30.565 C 94 34.125 91.49 37.975 87.34 39.175 C 82.8 40.535 77.13 39.885 73.92 36.095 C 72.32 34.265 71.03 32.175 69.69 30.145 C 46.47 29.745 23.23 30.105 0 29.985 L 0 23.985 C 23.23 23.865 46.47 24.225 69.69 23.825 C 71.03 21.795 72.31 19.715 73.92 17.875 Z"/>
</svg>';
$beaufort2= '<svg id="bft02" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 5.883 C 1.68 4.963 3.19 3.783 4.67 2.593 C 10.04 9.223 15.42 15.863 20.8 22.503 C 37.23 22.493 53.66 22.533 70.09 22.483 C 71.41 19.713 72.76 16.733 75.39 14.953 C 79.66 12.043 85.6 11.953 90.03 14.573 C 93.26 16.583 95.26 20.143 96 23.813 L 96 27.293 C 95.15 31.183 92.92 34.973 89.3 36.843 C 84.74 39.123 78.83 38.793 74.79 35.593 C 72.52 33.793 71.31 31.073 70.1 28.523 C 46.73 28.473 23.37 28.523 0 28.503 L 0 22.443 C 4.33 22.543 8.66 22.503 13 22.493 C 8.62 17.423 4.88 11.783 0 7.183 L 0 5.883 Z"/>
</svg>';
$beaufort3= '<svg id="bft03" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 5.524 C 1.67 4.584 3.17 3.364 4.64 2.144 C 10.04 8.784 15.41 15.444 20.8 22.084 C 37.24 22.074 53.68 22.104 70.12 22.064 C 71.39 19.274 72.76 16.324 75.38 14.544 C 79.66 11.634 85.61 11.544 90.05 14.164 C 93.25 16.174 95.22 19.684 96 23.314 L 96 26.984 C 95.16 30.564 93.2 34.044 90.01 36.034 C 85.59 38.614 79.71 38.524 75.44 35.664 C 72.79 33.884 71.39 30.914 70.12 28.104 C 52.75 28.064 35.38 28.094 18.01 28.084 C 11.92 21.024 6.53 13.324 0 6.664 L 0 5.524 Z"/>
</svg>';
$beaufort4= '<svg id="bft04" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 6.872 C 1.62 5.862 3.17 4.752 4.69 3.582 C 10.05 10.222 15.43 16.852 20.8 23.492 C 22.27 23.472 23.73 23.472 25.2 23.472 C 22.39 20.082 19.61 16.672 16.8 13.282 C 18.34 12.012 19.87 10.752 21.4 9.482 C 25.26 14.152 29.13 18.812 33 23.482 C 45.36 23.472 57.72 23.502 70.08 23.462 C 71.35 20.912 72.52 18.152 74.84 16.352 C 79.08 13.052 85.34 12.822 89.94 15.502 C 93.23 17.492 95.21 21.072 96 24.752 L 96 28.322 C 95.16 31.932 93.23 35.462 89.99 37.432 C 85.38 40.132 79.11 39.922 74.86 36.632 C 72.54 34.832 71.34 32.062 70.08 29.502 C 52.72 29.462 35.37 29.492 18.01 29.482 C 11.92 22.452 6.6 14.712 0 8.152 L 0 6.872 Z"/>
</svg>';
$beaufort5= '<svg id="bft05"  width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 4.55 3.008 C 10.04 9.618 15.37 16.368 20.82 23.008 C 22.21 22.998 23.6 22.988 25 22.988 C 20.67 17.658 16.33 12.328 12 6.998 C 13.53 5.668 15.07 4.338 16.6 3.008 C 22.01 9.668 27.39 16.348 32.82 23.008 C 45.26 22.978 57.71 23.018 70.15 22.988 C 71.41 19.908 73.07 16.768 76.03 15.018 C 79.4 13.118 83.56 12.628 87.24 13.848 C 91.95 15.108 95.08 19.628 96 24.228 L 96 28.028 C 95.11 31.558 93.16 34.968 90.02 36.948 C 85.6 39.528 79.71 39.448 75.44 36.578 C 72.8 34.788 71.34 31.858 70.15 29.008 C 52.77 28.988 35.39 28.998 18.02 28.998 C 11.93 21.898 6.44 14.238 0 7.478 L 0 6.528 C 1.72 5.638 3.15 4.318 4.55 3.008 Z"/></svg>';
$beaufort6= '<svg id="bft06"  width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 4.55 2.66 C 10.03 9.27 15.37 16 20.81 22.65 C 22.2 22.65 23.6 22.65 24.99 22.64 C 20.67 17.3 16.33 11.99 12.01 6.65 C 13.53 5.31 15.07 3.98 16.6 2.65 C 22.02 9.32 27.39 16.03 32.84 22.67 C 34.22 22.66 35.6 22.64 36.98 22.63 C 32.67 17.29 28.31 11.99 24.01 6.64 C 25.54 5.31 27.07 3.98 28.6 2.66 C 34.01 9.32 39.39 16 44.81 22.65 C 53.26 22.64 61.71 22.66 70.15 22.64 C 71.04 20.65 71.89 18.6 73.36 16.96 C 76.67 13.08 82.45 11.99 87.19 13.48 C 91.91 14.73 95.07 19.25 96 23.87 L 96 27.4 C 95.2 31.38 92.83 35.22 89.13 37.09 C 84.81 39.13 79.42 38.92 75.43 36.23 C 72.8 34.44 71.34 31.51 70.15 28.66 C 52.77 28.64 35.39 28.66 18.01 28.65 C 11.92 21.59 6.51 13.87 0 7.21 L 0 6.1 C 1.83 5.45 3.11 3.88 4.55 2.66 Z"/></svg>';
$beaufort7= '<svg id="bft07"  width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 7.27 C 1.53 5.94 3.03 4.56 4.61 3.29 C 10.06 9.91 15.35 16.66 20.85 23.24 C 22.23 23.28 23.6 23.28 24.98 23.23 C 20.67 17.9 16.31 12.62 12.04 7.26 C 13.53 5.9 15.05 4.57 16.61 3.29 C 22.05 9.91 27.35 16.65 32.84 23.24 C 34.22 23.28 35.6 23.28 36.98 23.24 C 32.69 17.9 28.3 12.63 24.04 7.26 C 25.53 5.9 27.05 4.57 28.61 3.29 C 34.05 9.91 39.36 16.65 44.83 23.24 C 46.35 23.28 47.86 23.28 49.38 23.25 C 46.62 19.83 43.78 16.48 41.03 13.06 C 42.53 11.78 44.05 10.5 45.61 9.29 C 49.51 13.91 53.29 18.64 57.22 23.24 C 61.55 23.29 65.88 23.26 70.21 23.25 C 71.17 20.55 72.62 17.93 74.86 16.1 C 78.91 12.98 84.66 12.69 89.2 14.86 C 92.85 16.75 95.26 20.58 96 24.56 L 96 27.92 C 95.11 33.3 90.82 38.39 85.16 38.84 C 78.59 40.32 71.9 35.69 70.21 29.27 C 52.82 29.23 35.43 29.3 18.04 29.24 C 11.96 21.97 6.04 14.57 0 7.27 L 0 7.27 Z"/></svg>';
$beaufort8= '<svg id="bft08"  width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 4.64 3.12 C 10.05 9.75 15.41 16.42 20.82 23.06 C 22.21 23.05 23.6 23.05 25 23.04 C 20.66 17.71 16.33 12.38 12 7.05 C 13.54 5.72 15.07 4.39 16.6 3.05 C 22.01 9.72 27.4 16.4 32.81 23.06 C 34.21 23.05 35.6 23.04 37 23.04 C 32.66 17.71 28.33 12.38 24 7.05 C 25.54 5.72 27.07 4.39 28.6 3.05 C 34.01 9.72 39.4 16.4 44.82 23.06 C 46.21 23.05 47.6 23.05 49 23.04 C 44.66 17.71 40.33 12.38 36 7.05 C 37.54 5.72 39.07 4.39 40.6 3.05 C 46.01 9.72 51.4 16.4 56.81 23.06 C 58.34 23.05 59.86 23.05 61.39 23.04 C 58.6 19.64 55.8 16.25 53 12.85 C 54.54 11.58 56.07 10.32 57.61 9.06 C 61.73 13.84 65.44 18.99 69.89 23.47 C 71.21 20.75 72.41 17.78 74.89 15.88 C 79.11 12.63 85.3 12.41 89.89 15.04 C 93.19 17.01 95.2 20.6 96 24.28 L 96 27.82 C 95.21 31.48 93.21 35.05 89.94 37.03 C 85.52 39.6 79.63 39.48 75.4 36.6 C 72.77 34.82 71.38 31.86 70.11 29.06 C 52.74 29.04 35.38 29.06 18.01 29.05 C 11.92 22 6.57 14.28 0 7.69 L 0 6.45 C 1.68 5.54 3.18 4.35 4.64 3.12 Z"/></svg>';
$beaufort9= '<svg id="bft09"  width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 3.031 C 5.29 9.401 10.66 15.711 15.99 22.051 C 16.01 15.721 15.99 9.401 16 3.071 C 21.61 9.731 27.19 16.401 32.8 23.061 C 45.26 23.051 57.71 23.081 70.16 23.041 C 71.97 16.721 78.38 12.091 85.02 13.411 C 90.73 13.931 95.12 18.931 96 24.421 L 96 27.611 C 95.18 33.141 90.75 38.201 85 38.721 C 78.37 40.021 71.98 35.401 70.16 29.081 C 46.77 29.041 23.39 29.071 0 29.061 L 0 3.031 Z"/></svg>';
$beaufort10='<svg id="bft010" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 2.837 C 5.57 9.547 11.2 16.197 16.81 22.867 C 34.6 22.857 52.38 22.887 70.16 22.857 C 71.98 16.497 78.44 11.867 85.1 13.227 C 90.77 13.767 95.07 18.737 96 24.157 L 96 27.537 C 95.15 32.197 91.95 36.757 87.21 38.037 C 82.45 39.537 76.62 38.427 73.32 34.507 C 71.87 32.877 71.03 30.847 70.16 28.877 C 46.77 28.857 23.39 28.877 0 28.867 L 0 2.837 Z"/></svg>';
$beaufort11='<svg id="bft011" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 0 C 5.55 6.72 11.21 13.35 16.8 20.04 C 18.26 20.03 19.73 20.03 21.19 20.03 C 18.93 17.3 16.67 14.57 14.4 11.83 C 15.94 10.58 17.47 9.31 19 8.04 C 22.34 12.04 25.66 16.05 29.01 20.04 C 42.72 20.02 56.43 20.07 70.14 20.02 C 71.17 17.86 72.07 15.54 73.83 13.85 C 77.91 9.66 84.85 9.19 89.85 11.98 C 93.15 14.01 95.29 17.6 96 21.37 L 96 24.6 C 95.35 28.42 93.17 32.05 89.84 34.1 C 85.44 36.56 79.67 36.46 75.46 33.64 C 72.81 31.85 71.37 28.91 70.15 26.06 C 46.76 26.02 23.38 26.05 0 26.04 L 0 0 Z"/></svg>';
$beaufort12='<svg id="bft0xx" width="20px" height="9px" viewBox="0 0 96 40" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="currentcolor" stroke="currentcolor" stroke-width="0.09375" opacity="1.00" d="M 0 2.845 C 5.3 9.205 10.66 15.525 15.99 21.855 C 16.01 15.535 15.99 9.215 16 2.895 C 21.62 9.545 27.19 16.225 32.81 22.875 C 34.2 22.875 35.6 22.865 36.99 22.875 C 33.74 18.865 30.46 14.885 27.21 10.875 C 28.66 9.545 30.12 8.215 31.58 6.885 C 36.02 12.195 40.38 17.565 44.81 22.875 C 53.27 22.865 61.72 22.895 70.18 22.865 C 71.39 19.725 73.14 16.565 76.15 14.835 C 80.11 12.585 85.11 12.505 89.2 14.465 C 92.87 16.375 95.27 20.215 96 24.225 L 96 27.435 C 95.18 32.955 90.75 38.015 85.02 38.525 C 78.4 39.845 71.95 35.225 70.18 28.885 C 46.79 28.855 23.39 28.885 0 28.875 L 0 2.845 Z"/></svg>';
#
if($weather["wind_run"]  > 0)
    {   $from           = array ('mph', 'km/h', 'm/s');
        $to             = array (' mi',  ' km',  ' km');
        $run_unit       = str_replace ($from, $to, $weather["wind_units"]);}
#
$spd_knts       = round ($weather['wind_speed']*$toKnots , 1);
$lvl_bft        = array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
$bft_txt        = array( /* Beaufort 0 to 12 in English */
	'Calm', 
	'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale force', 'Stronggale', 'Storm',
	'Violent storm', 'Hurricane');
$bft_clr = array(
	"transparent", 
	"transparent", "transparent", "transparent", "transparent", "transparent", 
	 "#FFFF53",    "#F46E07",     "#F00008",     "#F36A6A",     "#6D6F04", 
	 "#640071",    "#650003");	

$txt            = 'beaufort0'; // default value ??
foreach ($lvl_bft as $key => $n)
     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
        $txt = 'beaufort'.$key;
        break;}

$img_bft        = $$txt;        
$bft_nr         = $key;
$bft_txt_l      = lang($bft_txt[$key]);
$bft_clr_l      = $bft_clr[$key];
$wind_speed_avg = number_format($weather['wind_speed_avg'],$dec_wnd);
if ($weather['wind_speed_max'] <> 'n/a')
     {  $wind_speed_max = number_format($weather['wind_speed_max'],$dec_wnd);}
else {  $wind_speed_max = '';}
if ($weather['wind_gust_speed_max'] <> 'n/a')
     {  $gust_speed_max = number_format($weather['wind_gust_speed_max'],$dec_wnd);}
else {  $gust_speed_max = '';}
/*  [wind_units] => km/h
    [wind_speed] => 16
    [wind_speed_avg] => 16.1
    [wind_gust_speed] => 31
    [wind_speed_max] => 19.3
    [wind_gust_speed_max] => 31
    [wind_run] => 97
    [wind_speed_max_time] => 02:11
    [wind_gust_speed_max_time] => 08:30
    [wind_direction] => 84
    [wind_direction_avg] => 106*/
#
# ---------------- the date time
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column  <div class="PWS_div_left" style="border-right: 1px;">
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;

echo '<!-- average speed -->
<div class="PWS_div_left" style="border-right-width: 1px;">'
        .$wnd_l.' ('.$avg_lc.')'
        .'<br /><b >'.$wind_speed_avg.' '.$units_l.'</b>'  
        .'</div>'.PHP_EOL;

if ($bft_nr > 5)
     {  $border = 'border-right-color: '.$bft_clr_l.';';}
else {  $border = 'border-right-width: 1px;';}

echo'<!-- beaufort   -->
<div class="PWS_div_left" style="'.$border.'">'
        .$img_bft.' '.$bft_nr.' '.$bft_l
        .'<br /><b>'.$bft_txt_l.'</b>'
        .'</div>'.PHP_EOL; 
        
if ($weather['wind_direction_avg'] <> 'n/a')
     {  echo'<!-- wind direction average   -->
<div class="PWS_div_left" style="border-right-width: 1px;">'
                .$dir_l.' ('.$avg_lc.')'.'<br /><b>'
                .$cmp_avg_l.' '.(int) $weather['wind_direction_avg'].'&deg;</b>'
                .'</div>'.PHP_EOL;}
elseif ($wind_speed_max <> '') 
    {   echo'<!-- wind speed max    -->
<div class="PWS_div_left" style="border-right-width: 1px;">'
        .$wnd_l.' ('.$max_l.')'
        .'<br /><b >'.$wind_speed_max.' '.$units_l.'</b>'  
        .'</div>'.PHP_EOL;}

echo '</div>
<!-- END of left values -->'.PHP_EOL;       
#
# ----------------  middle area
#
if ($my_KISS == true && $current_theme == 'dark')
     {  $black  = ' silver';} 
else {  $black  = ' black'; } 
#
echo '<!-- middle part  -->
<div class="PWS_middle" style="width: 130px; height: 130px; margin-left:4px; margin-top: 0px; text-align: center; ">
  <div style=" height: 130px; margin: 0 auto; ">'.PHP_EOL;
echo return_compass().'
  </div>
          <div class="narrow" style="text-align: center; position: absolute; top: 0px; margin-top: 64px;width: 130px; color:' .$black.';">'
 .'<table style="border-collapse: collapse; font-size: 12px; margin: 0 auto;  color:' .$black.';">
<tr>
<td style="font-size: 15px; text-align: right; border-right: 1px solid '.$black.';"><b>'.round($weather['wind_speed']).'</b>&nbsp;</td>
<td style="font-size: 15px; text-align: left;  width: 50%; ">&nbsp;<b>'     #### 2020-12-15
        .round($weather['wind_gust_speed'])  .'</b></td>
</tr>
<tr>
<td style="text-align: right; border-right: 1px solid  '.$black.';">'.$wnd_l.'&nbsp;</td>
<td style="text-align: left;">&nbsp;'.$gust_l.'</td>
</tr>
<tr>
<td colspan="2" style="height: 24px; text-align: center; border-top: 1px solid  '.$black.';">'.(int) $weather['wind_direction'].'&deg;  <b>'.$cmp_l.'</b></td>
</tr>
</table>
</div>
</div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ---------------- right column
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
$n_blcks= 0;
if ($gust_speed_max <> '') 
     {  $n_blcks++; 
        echo '<!-- max speed  -->
<div class="PWS_div_right" style="border-left-width: 1px;">'
        .$gust_l.' ('.$max_l.')'
        .'<br /><b>'.$gust_speed_max.' '.$units_l.'</b>'  
        .'</div>'.PHP_EOL;}
#
if ((string) $weather["wind_run"] <> 'n/a' && $weather["wind_run"]  > 0  && $use_windrun == true)
    {   $n_blcks++; 
        echo '<!-- max speed  -->
<div class="PWS_div_right" style="border-left-width: 1px;">'
        .$run_l
        .'<br /><b>'.$weather["wind_run"].' '.$run_unit.'</b>'  
        .'</div>'.PHP_EOL;}
#
#
$not    = $weather['wind_units']; # km/h m/s mph kts  convert_speed ($num,$from,$to,$dec='')

$speed_arr      = array ('km/h', 'm/s', 'mph', 'kts');

$string = $wnd_l.'<br />'.number_format($weather['wind_speed'],$dec_wnd).' '.$weather['wind_units'].' = ';
foreach ($speed_arr as $value) 
     {  if ($value == $not) {continue;}
        $speed  = convert_speed ($weather['wind_speed'],$not,$value,1);
        $string .= '<br />'.number_format($speed,$dec_wnd).' '.$value;}
$height = '64px';
if ($gust_speed_max == '') 
     {  $string .= '<br /><b>'.$gust_l.'</b><br />'.number_format($weather['wind_gust_speed'],$dec_wnd).' '.$weather['wind_units'].' = ';
        foreach ($speed_arr as $value) 
             {  if ($value == $not) { continue;}
                $speed  = convert_speed ($weather['wind_gust_speed'],$not,$value,1);
                $string .= '<br />'.number_format($speed,$dec_wnd).' '.$value;}
        $height = '126px';}
#
if ($n_blcks < 2) 
     { echo '<div class="PWS_div_right" style="border-left-width: 1px; height: '.$height.'; ">'
        .$string
        .'</div>'.PHP_EOL;}
echo '</div><!-- END of right values -->'.PHP_EOL;
#
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
return; 
#
function return_compass() {
global $lang, $weather, $current_theme, $my_KISS;   
#
# ----------------- test values
#$weather['wind_direction']      = 0;
#$weather['wind_direction_avg']  = 45;
#
$rotate = 360 / 16;                             #  do not change north should be at the top
$rotate2= (float) $weather['wind_direction'] - 90;
if ($weather['wind_direction_avg'] <> 'n/a' && $weather['wind_direction_avg'] <> NULL && $weather['wind_direction_avg'] <> 'NULL')
     { $rotate3 = (float) $weather['wind_direction_avg'] - 90;  }
else { $rotate3 = $rotate2;}
if ($rotate2  == $rotate3) {$avg = false; } else {$avg = true; }
#
if ($current_theme <> 'dark')
     {  $dial_clr       = 'rgb(250, 250, 250)';
        $dial_txt       = 'rgb(40, 40, 40)';
        $white_clr      = 'rgb(229, 229, 229)';}
else {  $dial_clr       = 'rgb(90, 90, 90)';
        $dial_txt       = 'rgb(255, 255, 255)';
        $white_clr      = 'rgb(160, 160, 160)';}
#
# -------------------------------- marker colors
$cbr_hgh_clr    =  '#d65b4a';  // marker high
$cbr_lw_clr     =  '#01a4b4';   // marker low
$cbr_nw_clr     =  '#9aba2f';   // marker current
#
if ($my_KISS == true) 
     {  $rad = 48; 
        $pnts   ='111 72 100 65 111 58';   // current 
        $pntsHL ='115 70 122 65 115 60';}  // 
else {  $rad    = 63; 
        $pnts   ='114 65 128 58 128 72'; 
        $pntsHL ='116 65 126 61 126 69';}
#
$from   = 2* $rad * M_PI / 8;   // length of circle
#        
# start here
$return = '
<svg width="130" height="130" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg" >
  <defs></defs>';
# outer circle uses two circles to get one with 8 black/white wind directions  
$return .= '  
  <circle id="windBack"  r="'.$rad.'" cx="65" cy="65" style=" fill:none;  stroke-width: 4px; stroke: grey; " />
  <circle id="windRing"  r="'.$rad.'" cx="65" cy="65" stroke-width="4" stroke-dasharray="'.$from.'" 
       style="fill: none; stroke: '.$white_clr.'; " transform="rotate('.$rotate.' 65 65) "/>';
#
if (isset ($my_KISS) && $my_KISS == true ) 
     {  $return .= '
  <text x="65"  y="14" text-anchor="middle" style="fill: '.$dial_txt.' ; font-size: 10px;">'.lang('N').'</text>';}
# generate dials
else {  $arr_fr = array ('>N<', '>NNW<','>NNE<', '>NW<',
                         '>NE<','>WNW<','>ENE<', '>W<',
                        ' >E<', '>WSW<','>ESE<', '>SW<',
                        '>SE<','>SSW<' ,'>SSE<', '>S<');
        $arr_to = array ('>'.lang('N').'<', '>'.lang('NNW').'<','>'.lang('NNE').'<', '>'.lang('NW').'<',
                         '>'.lang('NE').'<','>'.lang('WNW').'<','>'.lang('ENE').'<','>'.lang('W').'<',
                         '>'.lang('E').'<', '>'.lang('WSW').'<','>'.lang('ESE').'<','>'.lang('SW').'<',
                         '>'.lang('SE').'<','>'.lang('SSW').'<','>'.lang('SSE').'<','>'.lang('S').'<');
        $return .= '
  <circle id="windDial"  style="fill: '.$dial_clr.';" cx="65" cy="65" r="61"/>'
  .str_replace ($arr_fr,$arr_to,'
  <text x="63"  y="16"   style="fill: '.$dial_txt.' ; font-size: 8px;">N</text>
  <text x="35"  y="20"   style="fill: '.$dial_txt.' ; font-size: 5px;">NNW</text>
  <text x="80"  y="20"   style="fill: '.$dial_txt.' ; font-size: 5px;">NNE</text>
  <text x="22"  y="31"   style="fill: '.$dial_txt.' ; font-size: 8px;">NW</text>
  <text x="98"  y="31"   style="fill: '.$dial_txt.' ; font-size: 8px;">NE</text>
  <text x="08"  y="50"   style="fill: '.$dial_txt.' ; font-size: 5px;">WNW</text>
  <text x="110" y="50"   style="fill: '.$dial_txt.' ; font-size: 5px;">ENE</text>  
  <text x="08"  y="67"   style="fill: '.$dial_txt.' ; font-size: 8px;">W</text>
  <text x="114" y="67"   style="fill: '.$dial_txt.' ; font-size: 8px;">E</text>  
  <text x="08"  y="86"   style="fill: '.$dial_txt.' ; font-size: 5px;">WSW</text>
  <text x="110" y="86"   style="fill: '.$dial_txt.' ; font-size: 5px;">ESE</text>
  <text x="22" y="105"   style="fill: '.$dial_txt.' ; font-size: 8px;">SW</text>
  <text x="99" y="105"   style="fill: '.$dial_txt.' ; font-size: 8px;">SE</text>
  <text x="35" y="114"   style="fill: '.$dial_txt.' ; font-size: 5px;">SSW</text>
  <text x="85" y="114"   style="fill: '.$dial_txt.' ; font-size: 5px;">SSE</text>
  <text x="63" y="121"   style="fill: '.$dial_txt.' ; font-size: 8px;">S</text>');
        for ($n = 0; $n< 8; $n++) {
                $rotate       = round( ($n * 360 /16),1);
                $return .= '  <line style="stroke: '.$dial_txt.';" x1="19" y1="65" x2="111" y2="65" transform="rotate('.$rotate.' 65 65)"></line>'.PHP_EOL;
        }  
        $return .= '  <circle id="windWhite" style="stroke-width: 0;  fill: '.$white_clr.';" cx="65" cy="65" r="44"></circle>';
} // eo generate dials
#
# generate arrows 
$return .= '
  <polygon id="windCrnt" points="'.$pnts.'"   style="fill: '.$cbr_nw_clr.';"  transform="rotate('.$rotate2.' 65 65)"></polygon>';
if ($avg == true) { $return .= '
  <polygon id="windAvg" points="'.$pntsHL.'"  style="fill: '.$cbr_hgh_clr.';" transform="rotate('.$rotate3.' 65 65)"></polygon>';}
$return .= '
</svg>';
return $return; 
} // eof return_compass
