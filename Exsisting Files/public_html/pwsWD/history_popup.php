<?php  $scrpt_vrsn_dt  = 'history_popup.php|01|2020-11-03|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$show_alltime   = true;         // set to false to remove right column
$color          = "#FF7C39";    // attention color  head line
#
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
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
#-----------------------   language translations
$ltxt_url       = lang('Station weather history');
$ltxt_clsppp    = lang('Close');
#
# what weathertypes do we have some history of
$ltxt_type      = array();
$ltxt_type['temp']      = lang('Temperature');
$ltxt_type['rain']      = lang('Rain');
$ltxt_type['humd']      = lang('Humidity');
$ltxt_type['dewp']      = lang('Dewpoint');
$ltxt_type['baro']      = lang('Pressure');
$ltxt_type['wind']      = lang('Wind');
$ltxt_type['gust']      = lang('Gust');
$ltxt_type['uvuv']      = lang('UV');
$ltxt_type['solr']      = lang('Solar');
#-----------------------------------------------
#          The following arrays drive the script
#-----------------------------------------------
# used for "Current" data: the name in the weather array  
$ltxt_value['temp']     = 'temp';
$ltxt_value['rain']     = 'rain_lasthour';
$ltxt_value['humd']     = 'humidity';
$ltxt_value['dewp']     = 'dewpoint';
$ltxt_value['baro']     = 'barometer';
$ltxt_value['wind']     = 'wind_speed';
$ltxt_value['gust']     = 'wind_gust_speed';
$ltxt_value['uvuv']     = 'uv';
$ltxt_value['solr']     = 'solar';
#
# the unit used in the history files 
# that is the unit of the weather-program data 
$ltxt_from['temp']      = $temp_his;  # $weather['temp_units'];
$ltxt_from['rain']      = $rain_his;  #$weather['rain_units'];
$ltxt_from['humd']      = '%';
$ltxt_from['dewp']      = $temp_his;  # $weather['temp_units'];
$ltxt_from['baro']      = $baro_his;  # $weather['barometer_units'];
$ltxt_from['wind']      = $wind_his;  #$weather['wind_units'];
$ltxt_from['gust']      = $wind_his;  #$weather['wind_units'];
$ltxt_from['uvuv']      = 'Index';
$ltxt_from['solr']      = 'w/m<sup>2</sup>'; 
#
# the currenly used unit by the visior of the website
$ltxt_u_to['temp']      = $weather['temp_units'];
$ltxt_u_to['rain']      = $weather['rain_units'];
$ltxt_u_to['humd']      = '%';
$ltxt_u_to['dewp']      = $weather['temp_units'];
$ltxt_u_to['baro']      = $weather['barometer_units'];
$ltxt_u_to['wind']      = $weather['wind_units'];
$ltxt_u_to['gust']      = $weather['wind_units'];
$ltxt_u_to['uvuv']      = 'Index';
$ltxt_u_to['solr']      = 'w/m<sup>2</sup>'; 
#
# the currently used decimals
$dcmls          = array();
$dcmls['temp']  = $dcmls['dewp']=$dec_tmp;
$dcmls['rain']  = $dec_rain;
$dcmls['baro']  = $dec_baro;
$dcmls['wind']  = $dcmls['gust']= $dec_wnd;
$dcmls['solr']  = $dcmls['humd']= 0;
$dcmls['uvuv']  = 1;
#
# the conversion script to use 
$ltxt_func['temp']      = 'temp'; #$weather['temp_units'];
$ltxt_func['rain']      = 'rain'; #$weather['rain_units'];
$ltxt_func['humd']      = false; #'%';
$ltxt_func['dewp']      = 'temp'; #$weather['temp_units'];
$ltxt_func['baro']      = 'baro'; #$weather['barometer_units'];
$ltxt_func['wind']      = 'wind'; #$weather['wind_units'];
$ltxt_func['gust']      = 'wind'; #$weather['wind_units'];
$ltxt_func['uvuv']      = false; #'Index';
$ltxt_func['solr']      = false; #'w/m<sup>2</sup>'; 
#
# also show the low value for those items
$ltxt_lows      = array ('temp',  'dewp', 'baro', 'humd');
#
# the coloms which can be in the table
$ltxt_col       = array();
$ltxt_col['type']    = '';
$ltxt_col['crnt']    = lang('Current');
$ltxt_col['today']   = lang('Today');
$ltxt_col['yday']    = lang('Yesterday');
$ltxt_col['month']   = lang(date('F')); #lang('This month');
$ltxt_col['year']    = date('Y'); #lang('This year');
$ltxt_col['all']     = lang('All time');
#
# the rows which can be in the table
$show           = array('temp', 'rain', 'humd', 'dewp', 'baro', 'wind', 'gust' );
if ($uvsolarsensors == 'both' || $uvsolarsensors == 'wf' || $uvsolarsensors == 'darksky') {
        $show[]         = 'solr';
        if ($uvsolarsensors <> 'darksky') {$show[]  = 'uvuv';}
}
# 
$hist = unserialize (file_get_contents('_my_settings/history.txt'));   #echo '<pre>'.print_r ($hist,true);
if (! is_array ($hist) ) {die (basename(__FILE__).' ('.__LINE__.') no correct data file found');}
#
# -------------  convert values to current units used by visitor
# --------the data-units uploaded is not always the unit to show
#
$high_lows      = array ('HghV', 'LowV');
$periods        = array ('today', 'yday', 'month', 'year');
if ($show_alltime === true) 
     {  $periods[] = 'all';}
else {  unset ($ltxt_col['all']);}
#
foreach ($ltxt_func as $type => $func) {
        $from   = $ltxt_from[$type];
        $to     = $ltxt_u_to[$type];
        if (strtolower ($ltxt_from[$type]) == strtolower ($ltxt_u_to[$type] ) ){ continue;}
        foreach ($high_lows as $high_low) {
                foreach ($periods as $period) {
                        $value  = $hist[$type][$high_low][$period];
                        if ( (string) $value === 'n/a') {continue;}
                        if ($func == 'temp')     { $hist[$type][$high_low][$period] =  convert_temp   ($value,$from,$to);}
                        elseif ($func == 'rain') { $hist[$type][$high_low][$period] =  convert_precip ($value,$from,$to);} 
                        elseif ($func == 'baro') { $hist[$type][$high_low][$period] =  convert_baro   ($value,$from,$to);}
                        elseif ($func == 'wind') { $hist[$type][$high_low][$period] =  convert_speed  ($value,$from,$to);}
                } // eo each period
        } // eo each high low
} // eo each ltxt_func
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
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;" >
'.$close.'
    <span style="color: '.$color.'; ">'.$ltxt_url.'</span>    </div>'.PHP_EOL;
echo '<div class= "div_height" style="width: 100%;text-align: left;  overflow: auto;">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center;">'.PHP_EOL;
echo '<tr>';  
foreach ($ltxt_col as $col) {
        echo '<th>'.$col.'</th>';
} // eo each col-hd
echo '</tr>'.PHP_EOL;
foreach ($show as $type) {
        if (in_array ($type,  $ltxt_lows) ){ 
                $h_arrow = '&uarr;&nbsp;'; 
                $l_arrow = '&darr;&nbsp;';} 
        else {  $h_arrow = ''; $l_arrow = '';}
        echo '<tr>';
# first the name 
        echo '<td>'.$ltxt_type[$type].'<small style="color: grey;">&nbsp;('.$ltxt_u_to[$type].')</small></td>';
# and current value
        $key    = $ltxt_value[$type];
        echo '<td>'.$weather[$key].'</td>'.PHP_EOL;
        foreach ($ltxt_col as $col => $text) {
                if ($col == 'type' || $col == 'crnt') {continue;}
                $high_value     = $low_value    = $high_date    =  $low_date    = 'n/a';
                if (isset ($hist[$type]['HghV'][$col]) )   {$high_value  = number_format((float) $hist[$type]['HghV'][$col],$dcmls[$type],'.',''); }
                if (isset ($hist[$type]['LowV'][$col]) )   {$low_value   = number_format((float) $hist[$type]['LowV'][$col],$dcmls[$type],'.',''); }
                if (isset ($hist[$type]['HghV_D'][$col]) ) {$high_date   = (int) $hist[$type]['HghV_D'][$col]; }
                if (isset ($hist[$type]['LowV_D'][$col]) ) {$low_date    = (int) $hist[$type]['LowV_D'][$col];}
                $dtHigh  = $dtLow  =  '';
                switch ($col) {
                   case 'all' :   $dtHigh  = date ('Y ',(int) $high_date); 
                                  $dtLow   = date ('Y ', (int)$low_date);
                   case 'year':   $dtHigh .= lang (date ('M ', (int)$high_date));
                                  $dtLow  .= lang (date ('M ', (int)$low_date )); 
                   case 'month':  $dtHigh .= date ('j', (int)$high_date); 
                                  $dtLow  .= date ('j', (int)$low_date); 
                }
                if ($type <> 'rain') 
                     {  $dtHigh = '<small style="color: grey;">&nbsp;('.$dtHigh .')</small>';
                        $dtLow  = '<small style="color: grey;">&nbsp;('.$dtLow .')</small>';} 
                else {  $dtHigh = $dtLow = '';}
                $time_ft        = str_replace (':s','',$timeFormat); 
                switch ($col) {
                        case 'today':
                        case 'yday':
                                if ( (string) $high_date === 'n/a') {$time_txt  = $high_date; } else {$time_txt  = date ($time_ft, $high_date);}
                                if ($type <> 'rain') 
                                     {  $time_string = '<small style="color: grey;">&nbsp;('.$time_txt.')</small>';} 
                                else {  $time_string = '';}
                                echo  '<td>'.$h_arrow.$high_value.$time_string;
                                if (in_array ($type,  $ltxt_lows) ){ 
                                        if ( (string) $low_date === 'n/a') {$time_txt  = $low_date; } else {$time_txt  = date ($time_ft, $low_date);}
                                        if ($type <> 'rain') 
                                             {  $time_string = '<small style="color: grey;">&nbsp;('.$time_txt.')</small>';} 
                                        else {  $time_string = '';}
                                        echo '<br />'.$l_arrow.$low_value .$time_string; }
                                echo '</td>';
                                break;
                        default:
                                echo  '<td>'.$h_arrow .$high_value.$dtHigh;
                                if (in_array ($type,  $ltxt_lows) )
                                     {  echo '<br />'.$l_arrow.$low_value .$dtLow; }
                                echo '</td>';
                                break;
                } // eo switch 
        } // eo each col
        echo '</tr>'.PHP_EOL;
} // eo each type
echo '</table>
</div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
# style is printed in the header 
function my_style()
     {  global $popup_css , $round_crnr;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css
        $return .= '   
        td , th {border-bottom: 1px solid silver; }
        table   {border-collapse: collapse; }'.PHP_EOL;
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
