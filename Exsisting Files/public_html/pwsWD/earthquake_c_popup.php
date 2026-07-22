<?php  $scrpt_vrsn_dt  = 'earthquake_c_popup.php|01|2022-11-22|';  # update new layout release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color_head     = "#FF7C39";    // attention color  head line
#
$adapt_date     = true;         // Translate "Month, nn year' to Today or Yesterday | set to false if you do not want that.
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
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# ----------------------------- link to provider 
$txt_company    = 'www.emsc-csem.org/';         #'earthquake-report.com';
$txt_href       = 'https://www.emsc-csem.org/'; #'https://earthquake-report.com';
$arr_eq_lngs    = array (); # array ('nl','fr','de','el','hu','it','pl','pt','ru','es','tr');
#
# ------------------------- translation of texts
$ltxt_url       = lang('Earthquake information courtesy of');
$ltxt_clsppp    = lang('Close');
$ltxt_magnitude = lang('Magnitude');
$ltxt_depth     = lang('Depth');
$ltxt_distance  = lang('Distance');
$ltxt_time      = lang('Time');
$ltxt_title     = lang('Description');
$ltxt_link      = lang('Link');
$ltxt_latlon    = lang('Coordinates');
$ltxt_miles     = lang('Miles');
$ltxt_km        = lang('KMs');
$ltxt_updated   = lang('Updated');
$ltxt_is_sorted = lang('Table is sorted by');
$ltxt_sort_on   = lang('Click to sort the table on');
$ltxt_sort_on2  = lang('or on');
$ltxt_here      = lang('here');
$ltxt_today     = lang('Today');
$ltxt_yday      = lang('Yesterday');
#
$rows           = 99;
#
$sort           = 'dist'; 
if (isset ($_REQUEST['sort']))
     {  if (trim($_REQUEST['sort']) == 'dist')
             {  $sort  = 'dist';}
        elseif (trim($_REQUEST['sort']) == 'magn')
             {  $sort  = 'magn';}
        else {  $sort  = 'time';} 
        }
if ($sort == 'dist')
     {  $srt_nw = strtolower($ltxt_distance).' ('.$distanceunit.')';
        $srt_aa = strtolower($ltxt_time);
        $srt_bb = strtolower($ltxt_magnitude);
        $srt_a  = 'time';
        $srt_b  = 'magn';}
elseif ($sort == 'time')
     {  $srt_nw = strtolower($ltxt_time);
        $srt_aa = strtolower($ltxt_distance).' ('.$distanceunit.')';
        $srt_bb = strtolower($ltxt_magnitude);
        $srt_a  = 'dist';
        $srt_b  = 'magn';}
else {  $srt_nw = strtolower($ltxt_magnitude);
        $srt_aa = strtolower($ltxt_time);
        $srt_bb = strtolower($ltxt_distance).' ('.$distanceunit.')';
        $srt_a  = 'time';
        $srt_b  = 'dist';}
#
$ltxt_minor     = lang('MinorE');
$ltxt_light     = lang('LightE');
$ltxt_moderate  = lang('ModerateE');
$ltxt_strong    = lang('StrongE');
$ltxt_major     = lang('MajorE');
$ltxt_great     = lang('GreatE');
#
$classEQ[0]     = $ltxt_minor;
$classEQ[1]     = $ltxt_minor;
$classEQ[2]     = $ltxt_minor;
$classEQ[3]     = $ltxt_minor; 
$classEQ[4]     = $ltxt_light; 
$classEQ[5]     = $ltxt_moderate; 
$classEQ[6]     = $ltxt_strong; 
$classEQ[7]     = $ltxt_major; 
$classEQ[8]     = $ltxt_great; 
$classEQ[9]     = $ltxt_great; 
$classEQ[10]    = $ltxt_great; 
#
$b_clrs[0]      = '#F6F6F6';  #< 2 
$b_clrs[1]      = '#F6F6F6';  # < 2 
$b_clrs[2]      = '#9999F8';  # 2.0 >
$b_clrs[3]      = '#9999F8'; # 3.0 >
$b_clrs[4]      = '#A6FCFE'; # 4
$b_clrs[5]      = '#F9DE4B'; # 5
$b_clrs[6]      = '#F09737'; # 6
$b_clrs[7]      = '#EB3323'; # 7  EEC44C
$b_clrs[8]      = '#7D160D'; # 8 DA8B43
$b_clrs[9]      = '#7D160D'; # 9
$b_clrs[10]     = '#7D160D'; # 10

# https://earthquake.usgs.gov/learn/topics/shakingsimulations/colors.php
# https://volcanoes.usgs.gov/observatories/hvo/felt_earthquakes.html
#
if (!file_exists ($fl_folder.$qks_fl) )
     {  echo '<p style="color: red;">No valid earthquake data found '.$qks_fl.'</p>';
        return;}  
$filetime       = filemtime ( $fl_folder.$qks_fl);
if (time() - $filetime > $quakesRefresh)
     {  $txt_updated    = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>';}
else {  $txt_updated    = '<b class="PWS_online"> ' .$online.set_my_time_lng($filetime,true).' </b>' ;}
#
$json_string    = $parsed_json  = false;
$json_string    = file_get_contents($fl_folder.$qks_fl);  # 'jsondata/eqnotification.txt'
if ($json_string <> false )
     {  $parsed_json    = json_decode($json_string,true);}
if (  $json_string == false
   || $parsed_json == false
   || count ($parsed_json) == 0)
     {  echo '<p style="color: red;">No valid earthquake data found '.$qks_fl.'</p>';
        return;}
$arr_to_srt     = array ();
$n=0;
foreach ($parsed_json as $arr)
     {  $time_key       = strtotime($arr['date_time']);
        $lati           = $arr['latitude'];
	$longi          = $arr['longitude'];
	$distance       = 
	$arr['distance']= round(distance($lat, $lon, $lati, $longi)) ;
	$magnitude      = $arr['magnitude'];
	if ($sort == 'time')
	     {  $key    = $time_key;}
	elseif ($sort == 'dist')
	     {  $key    = 100000 - $distance;}
	else {  $n++;
	        $key    = round($magnitude,1);
	        $key    = (string) $key .'-'.$n;}
        $arr_to_srt[$key]= $arr;}
#
krsort($arr_to_srt);  #echo '<pre>'.count($arr_to_srt).print_r($arr_to_srt,true); exit;
$i      = 0;
if (trim($windunit) == 'mph') {$dist   = $ltxt_miles;} else {$dist   = $ltxt_km;}
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
<html lang="'.substr($user_lang,0,2).'" style="background-color: white; ">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.' '.$txt_company.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="background-color: transparent; overflow: hidden;">
    <div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;">
'.$close.'
        <span style="color: #FF7C39; ">'.$ltxt_url
        .' <a href="'.$txt_href.'" target="_blank" style="color: white;">'
        .$txt_company.'</a></span>
        <span style="float:right;"><small>'.$txt_updated.'&nbsp;&nbsp;</small></span>   
    </div>
<div class= "div_height" style="width: 100%; padding: 0px; text-align: left; font-size: 14px; overflow-x: hidden; overflow-y: scroll; ">
    <div style="height: 14px; font-size: 12px; padding: 2px; text-align: center; color: black;">'
        .$ltxt_is_sorted.': <b>'.$srt_nw.'</b>.&nbsp;&nbsp;&nbsp;'
        .$ltxt_sort_on. ' <a href="./earthquake_c_popup.php?sort='.$srt_a.'"><b>'.$srt_aa.' </b> '.$ltxt_here.'.</a> '
        .$ltxt_sort_on2.' <a href="./earthquake_c_popup.php?sort='.$srt_b.'"><b>'.$srt_bb.' </b> '.$ltxt_here.'.</a>
</div><hr style="margin: 0px;"/>
<table class="font_head" style=" width: 100%; margin: 0px auto; text-align: center; background-color: white; color: black; overflow: auto;">
<tr><th>'.$ltxt_magnitude.'</th><th>'.$ltxt_depth.'</th><th>'.$ltxt_distance.'</th><th>'
        .$ltxt_time.'</th><th>'.$ltxt_link.'</th><th style="text-align: left;">'.$ltxt_title.'</th></tr>'.PHP_EOL;  
### 2020-05-24
$now    = time();
$today  = date('Ymd',$now);
$yday   = date('Ymd',$now - 24*3600);
if (!isset ($adapt_date) ) {$adapt_date = false;}
#
foreach ($arr_to_srt as $key => $parsed_json) 
     {  #if ($i > $rows) {break;}
        $magnitude      = round ($parsed_json['magnitude'],2);
        $eqtitle        = $parsed_json['title'];
#
        $newtxt         = $extra  = '';
        $txts           = explode ('-',$eqtitle);
        $key            = (int) floor ($magnitude);
        if ($key > 10) {$key = 10;}
        $color          = $b_clrs[$key];
# 2022-10-09
        $txts[0]        = $classEQ[$key].' ';  // added ' '
# 2022-10-09
        $key    = count ($txts) - 1;
        $date   = strtotime($parsed_json['date_time']);
        $check  = date('Ymd',$date);  #echo $check.' '.$today,' '.$yday; exit;
        if ($adapt_date <> true) {$check = '12345678';}
# 2022-10-09
        $date_time      = $txts[$key];   // last item has date time
        if     ( $check ==  $today) { $date_time = ' '.$ltxt_today;}
        elseif ( $check ==  $yday ) { $date_time = ' '.$ltxt_yday; }
        $cnt            = count($txts) - 1;
        for ($n = 0; $n < $cnt; $n++)
             {  $newtxt .= $extra.$txts[$n]; 
                $extra  = '-';}
        $eqtitle        = $newtxt.' - '.$date_time;
/*               
       if     ( $check ==  $today) { $txts[$key] = ' '.$ltxt_today;}
        elseif ( $check ==  $yday ) { $txts[$key] = ' '.$ltxt_yday;}
        foreach ($txts as $txt)
             {  $newtxt .= $extra.$txt; 
                $extra  = ' -';} 
        $eqtitle        = $newtxt;  */   
# 2022-10-09
        $depth          = $parsed_json['depth'];
        $eqtime         = set_my_time( $parsed_json['date_time']);
        $lati           = $parsed_json['latitude'];
        $longi          = $parsed_json['longitude'];
        $eqdist         = $parsed_json['distance'];
        $link           = $parsed_json['link'];
        if (trim($windunit) == 'mph') 
             {$distance = round($eqdist * 0.621371);} 
        else {$distance = round($eqdist);}
        $key            = (int) floor ($magnitude);
        if ($key > 10) {$key = 10;}
        $color          = $b_clrs[$key];
        echo '<tr>';
        echo  '<td style=" background-color: '.$color.'"><b>'.$magnitude.'</b></td>';
        echo  '<td>'.$depth.'</td>';
        echo  '<td>'.$distance.'<!-- '.$lati.','.$longi.' --></td>';
        echo  '<td>'.$eqtime.'</td>';
        echo  '<td><a href="'.$link.'" target="_blank">'.$ltxt_link.'</a></td>';
        echo  '<td style="text-align: left;"> '.$eqtitle.'</td>';
        echo '</tr>'.PHP_EOL;;
        }
echo '</table>
<br />
</div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
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
        $return .= '
        td {border-bottom: 1px solid silver;}  '.PHP_EOL;       
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
