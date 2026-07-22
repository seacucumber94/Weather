<?php $scrpt_vrsn_dt  = 'earthquake_c_small.php|01|2020-12-24|';  # check file exists | release 2012_lts
# 
#  earthquake_c_small.php script runs in small box in top row
#  earthquake_block.php uses large box in middle part
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
if (isset ($$string) ) {echo 'earthquake small info is already displayed'; return;}
$$string = $string;
#
# -------------------------------- load settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -----------------------  general functions aso  
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$notifyDistEQ       = 3000;     // km regional EQ
#
# ------------------------- translation of texts
$ltxt_miles     = lang('Miles');
$ltxt_km        = lang('KMs');
$ltxt_time      = lang('Time');
$ltxt_depth     = lang('Depth');
$ltxt_distance  = lang('Distance');
$ltxt_RegionalE = lang('RegionalE');
$ltxt_largest   = lang('Largest');
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
$fl_t_ld        = $fl_folder.$qks_fl;  #### 2020-12-24
$json_string    = $parsed_json  = false;
if (file_exists ($fl_t_ld))
     {  $json_string    = file_get_contents($fl_t_ld);  # 'jsondata/eqnotification.txt'
        $filetime       = filemtime  ($fl_t_ld);
        $parsed_json    = json_decode($json_string,true);}
if ( $parsed_json == FALSE) 
     {  $dataFALSE = basename(__FILE__).' ('.__LINE__.'):<br />Invalid / no JSON data '.$qks_fl; 
        echo $dataFALSE.'<br />Check settings and data'; return;}
#
$frst_lcl       = -1;
$frst_lcl_5     = -1;
$frst_lcl_4     = -1;
$frst_lcl_x     = -1;
$other_key      = -1;
$other_mgn      = -1;
#
if ($windunit <> 'km/h')    // script uses km for distance calculation
     {  $comp_dist      = $notifyDistEQ / 0.621371; }
else {  $comp_dist      = $notifyDistEQ;}
#
# first we have to sort the data on time of earthquake as it is not always sorted
#
foreach ($parsed_json as $arr)
     {  $time_key       = strtotime($arr['date_time']);
        $lati           = $arr['latitude'];
	$longi          = $arr['longitude'];
	$distance       = 
	$arr['distance']= round(distance($lat, $lon, $lati, $longi)) ;
        $key            = $time_key;
        $key            = 100000 - $distance;
        $arr_to_srt[ $key]= $arr;}
#
unset ($parsed_json);
krsort($arr_to_srt); # echo '<pre>'.count($arr_to_srt).print_r($arr_to_srt,true); exit;
#
foreach ($arr_to_srt as $key => $arr)
     {  $dist   = $arr['distance'];  
        $magn   = (float) $arr['magnitude'];
# find a regional earthquake 
#       and select the first > 5, 
#       and the first > 4
#       and the first > 0
        if ($dist < $comp_dist)         // distance OK -> this is a regional earthquake 
             {  if ($frst_lcl == -1)    // set first regonal found
                     {  $frst_lcl = $key;}
                if ($magn >= 5)                   // most severe 
                     {  if ( $frst_lcl_5 == -1 ) // first one is stored
                             { $frst_lcl_5 = $key;  continue;} }
                elseif ($magn >= 4)
                     {  if ( $frst_lcl_4 == -1 ) 
                             { $frst_lcl_4 = $key;  continue;}}
                else {  if ( $frst_lcl_x == -1 ) 
                             { $frst_lcl_x = $key;  continue;} }
        } // eo  distance   OK 
#
# if not a regional one save the one with the greatest magnitude found    
        elseif ($other_mgn  < $magn)    
             {  $other_mgn      = $magn;
                $other_key      = $key; 
        } // eo non-regional ones
} // eo foreach      
#
if ($frst_lcl <> -1 )   // a regional earth quake was found
     {  $region_text    = '<span class="red">'.$ltxt_RegionalE;
        if ($frst_lcl_5 <> -1)          // first one is 5>
             {  $key    = $frst_lcl_5;}
        elseif ($frst_lcl_4 <> -1)      // first one is 4>
             {  $key    = $frst_lcl_4;}
        else {  $key    = $frst_lcl_x;}
        } // eo regional found
else {  $region_text    = '<span class="orange">'.$ltxt_largest;
        $key            = $other_key;
        } // non regional 
#
# now we are going to print the selected earthquake
$arr            = $arr_to_srt[$key];  # echo '<pre>'.print_r($arr,true); exit;  # for testing
$magnitude      = round($arr['magnitude'],1);
$eqtitle        = $arr['location'];
$time           = strtotime($arr['date_time']);#   echo __LINE__.' $key='.$key.print_r($arr_to_srt[$key],true); exit;
$eventime       = date( $dateFormat,$time) . " " . set_my_time( $time , true);
$distance       = $arr['distance'];
$depth          = $arr['depth'];
$magn           = (int) floor ($magnitude);
if ($magn > 10) {$magn = 10;}
$color          = $b_clrs[$magn];
$class          = $classEQ[$magn];

if ($distanceunit ==  'mi') 
     {  $dist   = round($distance * 0.621371) .' '.$ltxt_miles; }
else {  $dist   = round($distance) .' '.$ltxt_km; }
# 
$box_style      = 'width: 55px; height: 55px; float: left; margin: 2px; padding: 2px; padding-top: 12px;  border-right-width: 1px;';
# 
echo '<div class= "PWS_div_left PWS_round" style="'.$box_style.' background-color: '.$color.'; color: black;">
     <span style="font-size: 14px; font-weight: 700;">'.$magnitude.'</span><br />
    <svg id="i-activity" viewBox="0 0 32 32" width="10" height="10" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
        <path d="M4 16 L11 16 14 29 18 3 21 16 28 16" />
    </svg>
</div>
<div style="font-size:8px; padding-top: 10px;">
<b>'.$region_text.' '.$class.'</span></b><br />
'.$eqtitle.'<br />
'.$ltxt_time.': '.$eventime.'<br />'.$ltxt_depth.': '.$depth.' '.$ltxt_km
.' '.$ltxt_distance.': '.$dist.'
</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}


