<?php  $scrpt_vrsn_dt  = 'earthquake_c_block.php|01|2020-12-24|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$sort_distance  = false;  # Default latest 2 earthquakes, set to true display nearest 2 in last 24 hrs
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

#
# ------------------------- translation of texts
$ltxt_url       = lang('Earthquake information courtesy of');
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
$ltxt_today     = lang('Today');
$ltxt_yday      = lang('Yesterday');
#
$ltxt_none      = '';
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
#
$span_values    = '<span style="color: #FF7C39;>';
$span_end       = '</span>';
#
$fl_t_ld        = $fl_folder.$qks_fl;
$json_string    = $parsed_json = $filetime = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);  # 'jsondata/eqnotification.txt'
        $filetime       = filemtime  ($fl_t_ld);
        $parsed_json    = json_decode($json_string,true);}
#-----------------------------------------------
#     check if data is usable
#-----------------------------------------------
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = basename(__FILE__).' ('.__LINE__.'):<br />Invalid / no JSON data '.$qks_fl; 
        echo $dataFALSE.'<br />Check settings and data'; return;}
#
$arr_to_srt     = array ();
foreach ($parsed_json as $arr)
     {  $time_key       = strtotime($arr['date_time']);
        $lati           = $arr['latitude'];
	$longi          = $arr['longitude'];
	$distance       = 
	$arr['distance']= round(distance($lat, $lon, $lati, $longi)) ;
	if ($sort_distance === true)
	     {	$key    = 100000 - $arr['distance']; }
        else {  $key    = $time_key;}
        $arr_to_srt[ $key]= $arr;}
#
unset ($parsed_json);
krsort($arr_to_srt); # echo '<pre>'.count($arr_to_srt).print_r($arr_to_srt,true); exit;
#
if (trim($windunit) == 'mph') {$dist   = $ltxt_miles;} else {$dist   = $ltxt_km;}
#
# --------------------------------------
# ---------------          generate html
#
# ---------------- the date time
echo '<div class="PWS_ol_time">'.PHP_EOL;
if (time() - $filetime > 3600)
     {  echo '<b class="PWS_offline"> '.$online.lang('Offline').' </b>';}
else {  echo '<b class="PWS_online"> ' .$online.set_my_time_lng($filetime,true).' </b>' ;}
echo '</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content">'.PHP_EOL;
#
# ----------------   left column
$n      = 1;
$hr     = '<hr style="clear: both; margin: 2px;">'.PHP_EOL;
#
$now    = time();
$today  = date('Ymd',$now);
$yday   = date('Ymd',($now - 24 * 60 * 60));
foreach ($arr_to_srt as $arr) 
     {  $magnitude      = $arr['magnitude'];
        $title          = $arr['title'];
        $eqtitle        = $arr['location'];
        $depth          = $arr['depth'];
        $time1          = $arr['date_time'];
        $time1_unix     = strtotime($time1);
        $ymd            = date('Ymd', $time1_unix);
        if     ($ymd == $today) {$time_first = $ltxt_today; }
        elseif ($ymd == $yday)  {$time_first = $ltxt_yday; }
        else                    {$time_first = date( $dateFormat,$time1_unix);}
        echo '<!-- $ymd='.$ymd.' $today='.$today.' $yday='.$yday.' -->';
        $lati           = $arr['latitude'];
        $longi          = $arr['longitude'];
        $eqdist         = $arr['distance'];
        if (trim($windunit) == 'mph') 
             {$distance = round($eqdist * 0.621371);} 
        else {$distance = round($eqdist);}
        $eventime       = $time_first. ' @ '. set_my_time($time1);
        $shorttime      = set_my_time($time1);
        
        #
        $key            = (int) floor ($magnitude);
        if ($key > 10) {$key = 10;}
        $color          = $b_clrs[$key];
        $class          = $classEQ[$key];
        #
        echo '<!-- quake '.$n.' -->'.PHP_EOL;
        echo '<div class="PWS_right PWS_round" style="margin: 6px;  margin-top: 10px; height: 50px; width: 50px; padding: 10px;
        text-align: center; background-color: '.$color.';  color: black; 
        border: 1px solid silver;">
        <b class="large" >'.round($magnitude,1).'</b>
</div>'.PHP_EOL;  
        echo '<div class="PWS_right" style=" margin: 4px; margin-top: 10px; width: 230px;">'.PHP_EOL;
        echo '<b class="orange">'.$class.'</b><br />'
        .$eqtitle
        .'<br />'.$eventime
        .'<br />'.$ltxt_depth.': <b>'.$depth.'</b> '.$ltxt_km   // always in km 2019-07-31
        .' - '.$ltxt_distance.': <b>'.$distance.'</b> '.$dist;  // in km or miles 2019-07-31
        echo '</div>
<!-- END quake '.$n.' -->'.PHP_EOL;               
        if  ($n == 1)  
             {  echo '<hr style="clear: both; margin: 2px;">'.PHP_EOL;
                $n = 2;
                continue; } 
        else {  break;}
        } // eo foreach 
                
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}


