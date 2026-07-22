<?php $scrpt_vrsn_dt  = 'aurora_popup.php|01|2021-06-18|';  # new file kindex | release 2012_lts
#
# -----------------     SETTINGS for this script
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color          = "#FF7C39";    // attention color  head line
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
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
// K-INDEX & SOLAR DATA | RADIO HAMS REJOICE
#-----------------------------------------------
$kp             = 0;
$filetime       = 0;
if (file_exists ($fl_folder.$kndx_fl) ) 
     {  $str            = file_get_contents($fl_folder.$kndx_fl); 
        $filetime       = filemtime ($fl_folder.$kndx_fl);
        $arr            = json_decode($str,true); # echo '<pre>'.print_r($arr,true); exit;
        if ($arr <> NULL && $arr <> false && count ($arr) > 0)    
             {  $last  = count ($arr) - 1;
                if ( array_key_exists ('kp_index', $arr[$last]) )
                     {  $kp     =  $arr[$last]['kp_index']; }
                } // check last item in json array
        } 
if (time() - $filetime > $kindexRefresh)
     {  $txt_updated    = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>';}
else {  $txt_updated    = '<b class="PWS_online"> ' .$online.set_my_time_lng($filetime,true).' </b>' ;}
# ---------------------  for testing
# $kp =4;
# ---------------------  for testing
#
# ------------------------- translation of texts
$ltxt_url       = lang('Visual Aurora Borealis/Northern Lights and VHF Radio Aurora Indicators');
$ltxt_clsppp    = lang('Close');
$ltxt_hd1       = lang('Geomagnetic Storm');
$ltxt_hd1_a     = lang('KP-PLANETARY INDEX');
$ltxt_hd2       = lang('Radio Aurora Indicators');
$ltxt_kp        = lang('KP-INDEX');
$ltxt_kp2       = lang('KP-PLANETARY INDEX');
$ltxt_hd3       =  $ltxt_kp .' '.lang('Information');
$ltxt_a         = lang('A-INDEX');
$ltxt_hd4       = $ltxt_a.' '.lang('Information');
$ltxt_updated   =lang('Last Updated');
$ltxt_by        =lang('Data Provided by');
#
$b_clrs['maroon']       = 'rgba(208, 80, 65, 0.7)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['yellow']       = '#ecb454'; #rgba(186, 146, 58, 1)';
$b_clrs['green']        = '#9aba2f';
#
# generate texts/colors for Geomagnetic Storm
if    ($kp>=9)  { $txt1 = 'G5 '.lang('Geomagnetic Severe Storm'); $clr1 = $b_clrs['maroon'];} 
elseif($kp>=8)  { $txt1 = 'G4 '.lang('Geomagnetic Major Storm');  $clr1 = $b_clrs['purple'];} 
elseif($kp>=7)  { $txt1 = 'G3 '.lang('Geomagnetic Major Storm');  $clr1 = $b_clrs['red'];} 
elseif($kp>=6)  { $txt1 = 'G2 '.$ltxt_hd1;                        $clr1 = $b_clrs['red'];} 
elseif($kp>=5)  { $txt1 = 'G1 '.$ltxt_hd1;                        $clr1 = $b_clrs['orange'];} 
elseif($kp>=4)  { $txt1 = lang('Minor').' G1 '.$ltxt_hd1;         $clr1 = $b_clrs['yellow'];}  
elseif($kp>=3.5){ $txt1 = lang('Weak') .' '   .$ltxt_hd1;         $clr1 = $b_clrs['green'];}  
elseif($kp>=0)  { $txt1 = lang('Quiet No').' '.$ltxt_hd1;         $clr1 = $b_clrs['green'];} 
#
if    ($kp>7)   { $txt2 = lang('Excellent Aurora Viewing Possible');}
elseif($kp>6)   { $txt2 = lang('Mid to High Latitude Aurora Viewing Possible');}
elseif($kp>4)   { $txt2 = lang('High Latitude Aurora Viewing Possible');}
elseif($kp>3.5) { $txt2 = lang('Weak High Latitude Aurora Viewing Possible');}
else            { $txt2 = lang('No Aurora');}
#

if    ($kp>=8.9)  {$clr3 = $b_clrs['maroon'];   $txt3 = '400';}
elseif($kp>=7.9)  {$clr3 = $b_clrs['maroon'];   $txt3 = '208';}
elseif($kp>=6.9)  {$clr3 = $b_clrs['purple'];   $txt3 = '132';}
elseif($kp>=6)    {$clr3 = $b_clrs['red'];      $txt3 = '80';}
elseif($kp>=4.9)  {$clr3 = $b_clrs['orange'];   $txt3 = number_format($kp*6,0);}
elseif($kp>=3.9)  {$clr3 = $b_clrs['yellow'];   $txt3 = number_format($kp*5,0);}
elseif($kp>=2.9)  {$clr3 = $b_clrs['yellow'];   $txt3 = number_format($kp*4,0);}
elseif($kp>=2)    {$clr3 = $b_clrs['green'];    $txt3 = number_format($kp*2,0);}
else              {$clr3 = $b_clrs['green'];    $txt3 = number_format($kp*2,0);}

if($kp < 3.5)   { $txt4 = lang('NOT ACTIVE');}
else            { $txt4 = lang('RADIO AURORA ACTIVE');}

if    ($kp>=9)  { $txt5 = 'G5 '.lang('Severe Storm');         $clr5 = $b_clrs['maroon'];} 
elseif($kp>=8)  { $txt5 = 'G4 '.lang('Major Storm');          $clr5 = $b_clrs['purple'];} 
elseif($kp>=7)  { $txt5 = 'G3 '.lang('Major Storm');          $clr5 = $b_clrs['red'];} 
elseif($kp>=6)  { $txt5 = 'G2 '.lang('Storm');                $clr5 = $b_clrs['red'];} 
elseif($kp>=5)  { $txt5 = 'G1 '.lang('Storm');                $clr5 = $b_clrs['orange'];} 
elseif($kp>=4)  { $txt5 = lang('Minor').' G1 '.lang('Storm'); $clr5 = $b_clrs['yellow'];}  
elseif($kp>=3.5){ $txt5 = lang('Weak') .' '   .lang('Storm'); $clr5 = $b_clrs['green'];}  
elseif($kp>=0)  { $txt5 = lang('Quiet No').' '.lang('Storm'); $clr5 = $b_clrs['green'];} 

if    ($kp>7)    { $txt6 =  lang('Strong Radio Aurora')             .'<br>28-433MHZ '.lang('Possible');}
elseif($kp>6)    { $txt6 =  lang('Mid-High Latitude Radio Aurora')  .'<br>28-144MHZ '.lang('Possible');}
elseif($kp>5)    { $txt6 =  lang('Radio Aurora')                    .'<br>50-144MHZ '.lang('Possible');}
elseif($kp>=4)   { $txt6 =  lang('High Latitude Radio Aurora')      .'<br>50-144MHZ '.lang('Possible');}
elseif($kp>=3.5) { $txt6 =  lang('High Latitude Weak Radio Aurora') .'<br>50-144MHZ '.lang('Possible');}
else             { $txt6 =  lang('No Radio Aurora');}
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
<html lang="'.substr($used_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body class="dark">
    <div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;" >
'.$close.'
    <span style="color: '.$color.'; ">'.$ltxt_url.'</span>
    <span style="float:right;" class="invisible"><small>'.$txt_updated.'&nbsp;&nbsp;</small></span>
    </div>
    <div class="PWS_weather_container"><!-- toprow -->
<!-- weatheritem 1  KP index -->
        <div class="PWS_weather_item" style="position: relative;">
            <div class="PWS_module_title"><div class="title">'.$ltxt_hd1.'</div></div>';
#
$value  = number_format($kp,0);
echo 
'                <p style="text-align: left; padding-left: 10px; width: 100%">
<span style="font-size: 36px; text-align: left;">
<span style="color: '.$clr1.';">'.$value.'</span>
<span style="font-size: 14px;" > '.$ltxt_kp.'</span>
</span>
                </p>
                <p style="text-align: center;  font-size: 14px; width: 100%">
<span style=" font-size: 16px; color: '.$clr1.'"><strong>'.$txt1 .'</strong></span>
<br ><br >'.$ltxt_kp2.'<br ><br />'.$txt2.'
                </p>         
        </div>
<!-- eo weatheritem 1  KP index -->
<!-- weatheritem 2  A index -->
        <div class="PWS_weather_item">
            <div class="PWS_module_title"><div class="title">'.$ltxt_hd2.'</div></div>
                <div style="width: 75%; height: 100%; float: left;">
                <p style="text-align: left; padding-left: 10px; width: 100%">
<span style="font-size: 36px; text-align: left;">
<span style="color: '.$clr3.';">'.$txt3.'</span>
<span style="font-size: 14px;" > '.$ltxt_a.'</span>
</span>
                </p>
                <p style="text-align: center;  font-size: 14px; width: 100%">
<span style=" font-size: 16px; color: '.$clr5.'"><strong>'.$txt5 .'</strong></span>
<br ><br >'.$txt4.'<br ><br />'.$txt6.'
                </p>                
                </div>
                <div style="width: 20%; float: right;">
                <br /><br /><br />
<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>
';
$lvl    = array(9,9,8,8,7,6,5,4,3,2,1,0);
$fll    = array();
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['purple']; 
$fll[] = $b_clrs['purple'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['orange'];
$fll[] = $b_clrs['yellow']; 
$fll[] = $b_clrs['yellow'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
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
for ($i = 0; $i < 12; $i++)
     {  if ($kp <   $lvl[$i]) {$fll[$i] = 'currentcolor';}
        echo '<path fill="'.$fll[$i].'"  opacity="1.00" '.$d[$i].PHP_EOL;}
echo '</svg>
                </div>';
        
echo '        
        </div>
<!-- eo weatheritem 2  A index -->
    </div><!-- eo toprow -->
    <div class="PWS_weather_container" style="height: 170px;"><!-- second row -->
<!-- weatheritem 3 info -->
        <div class="PWS_weather_item" style="height: 160px;">
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd3.'</div></div>
        <p style="padding: 10px;  text-align: left; font-size: 14px;">
<span style="color: '.$b_clrs['green'].';">'.$ltxt_kp.'</span> ' 
.lang('provides a good indicator of viewing the Aurora Borealis or Northern Lights.').' '
.lang('The higher the Kp-Index the greater the probability of viewing.').' '
.lang('The estimated 3-hour Planetary Kp-index data is collected from ground-based magnetometers.').'
</p>
        </div>
<!-- eo weatheritem 3 -->
<!-- weatheritem  4 info -->
        <div class="PWS_weather_item" style="height: 160px;">
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd4.'</div></div>
        <p style="padding: 10px;  text-align: left; font-size: 14px;">
<span style="color: '.$b_clrs['green'].';">'.$ltxt_a.'</span> '
.lang('is an indicator of possible enhanced VHF radio communication at a range of 1000 miles or more.').' '
.lang('Strong solar activity may allow long-distance radio communications at 28-433 MHz in mid to high latitudes.').'
        </p>
        </div>
<!-- eo weatheritem details sun --> 
    </div><!-- eo second row -->
<span style="color: grey;"><br />'.$ltxt_by.'  
<a href="http://services.swpc.noaa.gov" title="services.swpc.noaa.gov" target="_blank" style="color: grey;">
http://services.swpc.noaa.gov</a>&nbsp;
&copy;'.date('Y').'
<br /><br /></span>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->';}
echo ' </body>
</html>'.PHP_EOL;
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
