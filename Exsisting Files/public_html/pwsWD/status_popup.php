<?php  $scrpt_vrsn_dt  = 'status_popup.php|01|2022-11-22|';  # bot problem + beta release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color_head     = "#FF7C39";    // attention color  head line
$status_arr     = './_my_settings/status.arr';
$hist_arr       = './_my_settings/history.txt';
#
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
#
# ------------------------- translation of texts
$ltxt_url       = lang('xxxxxxxxx');
$ltxt_clsppp    = lang('Close');
#-----------------------------------------------
$errors = '';
$rows           = 99;
#
#
if (!file_exists ($status_arr) )
     {  $error  = '<span style="color: red;">No valid status file found</span>';
        $statuses = array();
        $statuses['_statussen'] = array(
                'fl_name' => $status_arr, 
                'fl_url'  => $error,
                'fl_allw' => 300,
                'last'    => 0);}
else {  $stat_time      = filemtime ( $status_arr);
        $statuses       = unserialize (file_get_contents($status_arr));  
        $statuses['_statuses'] = array(
                'fl_name' => $status_arr, 
                'fl_url'  => 'n/a',
                'fl_allw' => 300,
                'last'    => $stat_time);}
#
unset ($statuses['Aurora-kindex']);
#
if (!file_exists ($hist_arr) )
     {  $error  = '<span style="color: red;">No valid history file found yet</span>';
        $statuses['_history'] = array(
                'fl_name' => $hist_arr, 
                'fl_url'  => $error,
                'fl_allw' => 600,
                'last'    => 0);}
else {  $hist_time      = filemtime ( $hist_arr);
        $history        = unserialize (file_get_contents($hist_arr));
        $statuses['_history'] = array(
                'fl_name' => $hist_arr, 
                'fl_url'  => 'n/a',
                'fl_allw' => 600,
                'last'    => $hist_time);
        if (array_key_exists ('last_roll_over',$history) )
             {  $last_rol       = $history['last_roll_over']; } # => 1610582102
        else {  $last_rol       = 0;}
        $statuses['_history_rollover'] = array(
                'fl_name' => '$history["last_roll_over"]', 
                'fl_url'  => 'n/a',
                'fl_allw' => 23*3600,
                'last'    => $last_rol);
        }  
#
if (!file_exists ($livedata) )
     {  $error  = '<span style="color: red;">No valid weather-data file found yet</span>';  
        $live_time      =  0;
        $statuses['_livedata'] = array(
                'fl_name' => $livedata, 
                'fl_url'  => $error,
                'fl_allw' => 300,
                'last'    => $live_time);}
else {  $live_time      = filemtime ( $livedata);
        $statuses['_livedata'] = array(
                'fl_name' => $livedata, 
                'fl_url'  => 'n/a',
                'fl_allw' => 300,
                'last'    => $live_time);}
#
if (!array_key_exists ('datetime',$weather) )
     {  $error  = '<span style="color: red;">No weather-data time known</span>'; 
        $statuses['_livedata_time'] = array(
                'fl_name' => 'n/a', 
                'fl_url'  => $error,
                'fl_allw' => 300,
                'last'    => $live_time);}
else {  $statuses['_livedata_time'] = array(
                'fl_name' => '$weather["datetime"]', 
                'fl_url'  => 'n/a',
                'fl_allw' => 300,
                'last'    => $weather['datetime']);} #  echo __LINE__.'<pre>'.print_r($statuses,true); exit;
#
#$have_extra = true;
if(isset ($have_extra) && $have_extra == true)
     {  if (!file_exists ($extra_data) )
             {  $error  = '<span style="color: red;">No valid extra sensor data file found yet</span>';  
                $statuses['_livedata_extra'] = array(
                        'fl_name' => $extra_data, 
                        'fl_url'  => $error,
                        'fl_allw' => 300,
                        'last'    => 0);}
        else {  $extra_time     = filemtime ( $extra_data);
                $statuses['_livedata_extra'] = array(
                        'fl_name' => $extra_data, 
                        'fl_url'  => 'n/a',
                        'fl_allw' => 300,
                        'last'    => $extra_time);}
        }  # $charts_from = "DB";
#
if ($charts_from == "DB")
     {  $db_day = 'today.txt';
        $db_mnth= date('Y_m',$now).'.txt';	
        $db_year= date('Y',$now).'.txt';
        $check  = './chartsmydata/'.$db_day;
        if (!file_exists ($check) )
             {  $error  = '<span style="color: red;">No chart-data file for today found yet</span>'; 
                $chart_time     =  0;
                $statuses['_DB_'.$db_day] = array(
                        'fl_name' => $check, 
                        'fl_url'  => $error,
                        'fl_allw' => 300,
                        'last'    => $chart_time);}                  
        else {  $chart_time = filemtime ( $check);
                $statuses['_DB_'.$db_day] = array(
                        'fl_name' => $check, 
                        'fl_url'  => 'n/a',
                        'fl_allw' => 300,
                        'last'    => $chart_time);}  
        $check  = './chartsmydata/'.$db_mnth;
        if (!file_exists ($check) )
             {  $error  = '<span style="color: red;">No chart-data file for this month found yet</span>';
                $chart_time     =  0;
                $statuses['_DB_'.$db_mnth] = array(
                        'fl_name' => $check, 
                        'fl_url'  => $error,
                        'fl_allw' => 24*3600,
                        'last'    => $chart_time);}                  
        else {  $chart_time = filemtime ( $check);
                $statuses['_DB_'.$db_mnth] = array(
                        'fl_name' => $check, 
                        'fl_url'  => 'n/a',
                        'fl_allw' => 24*3600,
                        'last'    => $chart_time);} 
        $check  = './chartsmydata/'.$db_year;
        if (!file_exists ($check) )
             {  $error  = '<span style="color: red;">No chart-data file for this year found yet</span>';
                $chart_time     =  0;
                $statuses['_DB_'.$db_year] = array(
                        'fl_name' => $check, 
                        'fl_url'  => $error,
                        'fl_allw' => 24*3600,
                        'last'    => $chart_time);}                  
        else {  $chart_time = filemtime ( $check);
                $statuses['_DB_'.$db_year] = array(
                        'fl_name' => $check, 
                        'fl_url'  => 'n/a',
                        'fl_allw' => 24*3600,
                        'last'    => $chart_time);}         
        } // eo charts_from == "DB"
#
$now    = time();
$sorted = array();
#
function age_readable($diff)
     {  $days = $hrs = $min = $secs = '';
        if ($diff > 24*3600)
             {  $days   = floor ($diff / (24*3600));
                $diff   = $diff - $days * 24*3600;
                $days   = $days.' '.lang('days').' ';}
        if ($diff > 3600)
             {  $hrs    = floor ($diff / 3600);
                $diff   = $diff - $hrs * 3600;
                $hrs    = $hrs.' '.lang('hrs').' ';}
        if ($diff > 60)
             {  $min    = floor ($diff / 60);
                $diff   = $diff - $min * 60;
                $min    = $min.' '.lang('mins').' ';}
        if ($diff > 0)
             {  $secs   = round($diff).' '.lang('seconds');}

        return  $days.$hrs.$min.$secs;}

foreach ($statuses as $key => $arr)
     {  $key = strtolower ($key); #echo __LINE__.' '.$key.print_r($arr,true);
        $arr['difference']      =  $diff = $now - $arr['last']; # filemtime($arr['fl_name']); 2022-08-29  # $arr['last']; # 2022-04-30 
        $arr['age_text'] = age_readable($diff);
        $arr['allw_text']= age_readable($arr['fl_allw']);        
        if ($arr['last'] == 0)
             {  $arr['status']  = 'Missing';
                $arr['age_text']= 'n/a';}
        elseif ($arr['fl_allw']  <  $diff)
             {  $arr['status']  = 'Stalled';
                $arr['age_text'] .= '<br/>'.lang('max').' = ' .$arr['allw_text'];} 
        else {  $arr['status']  = 'OK';}
        $sorted[$key]    = $arr;
        }
ksort ($sorted, SORT_NATURAL); #echo __LINE__.'<pre>'.print_r($sorted,true); exit;

$table  =  '<hr style="border-color:'.$color_head.'; width: 100%; margin: 0;">
<table style="color: black; font-size: 12px; border-collapse: collapse; width: 100%; background-color: white;"><tr>
<th style="text-align: right;">(File / Field) Name</th>
<th style="text-align: center;">Data</th>
<th style="text-align: center;">Status</th>
<th style="text-align: left;">Age</th>
<th style="text-align: left;">URL</th></tr>'.PHP_EOL;
foreach ($sorted as $key => $arr)
     {  $fl_name        = str_replace (__DIR__.'/','./',$arr['fl_name']);
        $fl_url         = $arr['fl_url'];
        if ($arr['last'] <> 0 && $fl_url <> 'n/a')
                {  $fl_url = substr ($fl_url,0,30).' . . .';}
#        $fl_name        = substr ($fl_name,-30);
        $table .= '<tr>
<td style="text-align: right;">'.$fl_name.'</td>
<td style="text-align: center;">&nbsp;&nbsp;'.$key.'&nbsp;&nbsp;</td>
<td style="text-align: center;">&nbsp;&nbsp;'.$arr['status'].'&nbsp;&nbsp;</td>
<td style="text-align: left;">'.$arr['age_text'].'</td>
<td style="text-align: left;">'.$fl_url.'</td>
</tr>'.PHP_EOL; 
        }

#echo __LINE__.'<pre>'.print_r($sorted,true); 


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
<html lang="'.substr($user_lang,0,2).'" style="background-color: transparent; ">
<head>
    <meta charset="UTF-8">
    <title>Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="background-color: transparent; overflow: hidden;">
    <div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;">
'.$close.'
        <span style="color: #FF7C39; ">Status</span>
    </div>
<div class= "div_height" style="width: 100%; padding: 0px; text-align: left; font-size: 14px; overflow-x: hidden; overflow-y: automatic; ">'.PHP_EOL;
echo $table.'
</div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL; 
#
# style is printed in the header 
function my_style()
     {  global $popup_css, $color_head ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
             
# add pop-up specific css
        $return .= '
        td {border-bottom: 1px solid silver;}  
        th {background-color: #393D40; color: '.$color_head.'; border-bottom: 1px solid silver;}  '.PHP_EOL;       
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
