<?php  $scrpt_vrsn_dt  = 'PWS_print_file.php|01|2022-12-23|';  # nw rel + clrtw wind-g + \n windows + typo - time WC | release 2012_lts
#
# used in debug dashboard to print the used fileds 
#   as contained in  the standard weatherdata file
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
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
# -------------------------------- load settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# -----------------------  general functions aso  
$scrpt          = 'PWS_shared.php'; ###### needed ??
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
# include_once $scrpt;
#   
# ----------------------------- settings
$data           = 'Default demo values used';
$used_only      = true;
# --------------------------------------
#                              check URL
if (!isset ($_REQUEST['type']) )  // we need to know the type of file 
     {  $type   = strtolower($livedataFormat);}
else {  $type   = strtolower(trim($_REQUEST['type']));}
#
$types_known    = array ('ecolcl',  'dwl',  'awapi',  'wf','wu', 'dwl_v2api',
                        'cumulus', 'weathercat', 'weewx', 'weatherlink', 'wifilogger', 'mb_rt',
                        'wd', 'meteohub', 'wswin',  'wdapi');             
$seperators     = array ('', '', '',  '', '', '',
                         ' ', ' ', ' ', "\n", ' ', "\n", 
                         ' ', ' ', ' ', ' ');
$data_types     = array ('ecolcl',  'dwl', 'awapi', 'wf','wu','dwl_v2api',
                         'rtxt', 'rtxt', 'rtxt', 'rtxt', 'rtxt', 'rtxt',
                         'cltrw', 'cltrw', 'cltrw', 'wdapi'); 

if (!in_array($type,$types_known) )
     {  echo __LINE__.' datatype '.$type.' not supported'; return;}
#
if (isset ($_REQUEST['file']) )  // we need to now the link to the file 
     {  $file  = trim($_REQUEST['file']);
        if ( strpos (' '.$file,'_keys')    > 0  
          || strpos (' '.$file,'settings') > 0 
          || strpos (' '.$file,'../')      > 0      
   #       || strpos (' '.$file,'.php')     > 0   // 2022-12-23 allow use of php data f.i. cvt.php
          || substr ($file,0,1) == '/'          // new version of apache ? one.com problem  ?? test
          || !is_file($file)  )     
             {  echo 'file not found, check your typing';
                return;} // eo check filename
        $file   = trim($_REQUEST['file']);}
else {  $file   = $livedata;}
#
if (!file_exists ($file) )
     {  echo __LINE__.' no file '.$file.' found'; return;}
else {  $data = file_get_contents ($file);
        if ($data  == false) 
             {  echo __LINE__.' no file or empty file '.$file.' found'; return;}
}   // eo check file exists and is usable
#
if (isset ($_REQUEST['showall']) )  
     {  $used_only = false; }
#
# --------------------------------------
function array_flatten($array) 
     {  $return = array();
        foreach ($array as $key => $value) {
                if (is_array($value))
                     {  $return = array_merge($return, array_flatten($value));} 
                else {  $return[$key] = $value;}
        } // eo fe
    return $return;} // eo array_flatten
#
$seperator      = '';
foreach ($types_known as $key => $value) 
     {  if ($value <> $type) {  continue;} 
        $seperator = $seperators[$key];
        $data_type = $data_types[$key];
        break;}
if ($seperator <> '')                   // seperate string to values
     {  $frmAPM = array (' PM ',' AM ', ' am ', ' pm ');  #### 2020-12-31
        $toAPM  = array ('&nbsp;PM ','&nbsp;AM ', '&nbsp;am ','&nbsp;pm ');
        $data   = str_replace ($frmAPM, $toAPM, $data);   #### 2020-12-31
        $fields = explode ($seperator,$data);}
else {  switch ($data_type) {           // not an easy string,
             case 'ecolcl':  
                $fields = unserialize($data); # echo __LINE__.'<pre>'.print_r($fields,true); exit;
                if ($fields == false || !is_array ($fields) || count($fields) < 1)
                     {  echo __LINE__.' no valid array '.substr($data,0,50); 
                        return;}
                unset ($fields['PASSKEY']);
                break; 
             case 'wu':          
             case 'dwl':
             case 'dwl_v2api':
                $array = json_decode($data,true);  
                if(json_last_error() <> JSON_ERROR_NONE)
                     {  echo __LINE__.' no valid json data '.substr($data,0,50); 
                        return;}
                $fields = array_flatten($array); # echo __LINE__.'<pre>'.print_r($fields,true); exit;  
                ksort($fields);
                break;
             case 'awapi':
                $array = json_decode($data,true);  
                if(json_last_error() <> JSON_ERROR_NONE)
                     {  echo __LINE__.' no valid json data '.substr($data,0,50); 
                        return;}
                foreach ($array as $key => $fields)
                    {   if ($fields['macAddress'] <> $aw_did) 
                             {  continue;}
                        break;}
                $fields = array_flatten($fields); # echo __LINE__.'<pre>'.print_r($fields,true); #exit;  
                break;
             case 'wf':  
                $array = json_decode($data,true); #echo __LINE__.'<pre>'.print_r($array          ,true); #exit;
                if(json_last_error() <> JSON_ERROR_NONE)
                     {  echo __LINE__.' no valid json data '.substr($data,0,50); 
                        return;}                
                unset  ($array['outdoor_keys']);
                $fields = array_flatten($array);  # echo __LINE__.'<pre>'.print_r($fields,true); #exit;  
             break;
             
             } // eo case              
        }  // echo '<pre>'.print_r($fields,true); exit; 
$func   = 'load_'.$data_type;
$return = $func();   #    echo '<pre>'.print_r($specs,true); #exit;

$tr_style       = 'style="text-align: center; padding: 4px; margin: 4px;background-color: #D4D7D9;"';
$td_center      = 'style="padding: 4px;"';
$div_style      = 'style="text-align: left; max-width: 700px; margin: 0 auto;"';
echo '<div  style="text-align: left; max-width: 90%; margin: 0 auto;">
<p style="padding: 4px; margin: 0; font-size: 10pt;">
<strong>File used: </strong>'.$file.'<br />
<strong>Loaded raw data:</strong><br /><span style="padding: 4px 0px; font-family: Courier, mono-space; font-size: 8pt;">'.str_replace ("\",\"","\", \"",$data).'</span></p>
<div '.$div_style.'>';
echo '<table style="text-align: left; max-width: 700px; font-family: Helvetica, sans-serif; font-size: 8pt;">'.PHP_EOL;
echo '<tr '.$tr_style.'><th '.$td_center.'>key</th><th '.$td_center.'>field-name</th><th '.$td_center.'>used</th><th '.$td_center.'>optional</th><th '.$td_center.'>contents</th><th '.$td_center.'>comment</th></tr>'.PHP_EOL;
#
foreach ($specs as $key => $arr)
     {  $label          = trim($arr['label']);
        $used           = trim($arr['used']);
        $optional       = trim($arr['optional']);
        $comment        = trim($arr['comment']);
        if ($used_only == true && trim($used) <> 'Y' && trim($used) <> '?' ) 
             {  unset ($specs[$key]);
                unset ($fields[$key]);
                continue;}
        if (isset ($fields[$key]) ) 
             {  $value  = $fields[$key];}
        elseif ($used_only == true  && $optional == 'Y')
             {  unset ($specs[$key]);
                unset ($fields[$key]);
                continue;} 
        else {  $value  = 'missing';}
        $empty          = false;
        if ($value === 'missing' || $value === 'n/a' || $value === '--' || $value === '--:--' || $value === NULL) 
             {  $empty = true; } else { $empty = false;} 
        if ( $empty && $used == 'Y' && $optional <> 'Y')
             {  $td_err = 'style="padding: 4px; background-color: red;"'; } 
        else {  $td_err = $td_center; } 
        echo '<tr '.$tr_style.'><td '.$td_center.'>'.$key.'</td><td '.$td_center.'>'.$label.'</td><td '.$td_center.'>'.$used.'</td><td '.$td_center.'>'.$optional.'</td><td '.$td_err.'>'.$value.'</td>'
                .'<td>'.$comment.'</td></tr>'.PHP_EOL;
        unset ($specs[$key]);
        unset ($fields[$key]);
        }
if ($used_only == false)
     {  $message =  '<tr '.$tr_style.'><td colspan="6"><b>More data found </b></td></tr>'.PHP_EOL;
        foreach ($fields as $key => $arr)
            {   $field  = $fields[$key];
                echo $message.'<tr '.$tr_style.'><td>'.$key.'</td><td></td><td></td><td></td><td> '.$field.' </td><td></td></tr>'.PHP_EOL;
                $message = '';}
        }
echo '</table>';
echo '</div>'.PHP_EOL;   
#  ---------------------------------------------
echo '<!-- '.$stck_lst.' -->';
#  ---------------------------------------------
function load_wf()
     { global $specs;
        $string = '
timestamp	        |datetime	|Y| |1590831705| |
sea_level_pressure	|barometer	|Y| |1023.1| |
dew_point	        |dewpoint	|Y| |4.3| |
air_temperature	        |temp	        |Y| |19.8| |
heat_index	        |heat_index	|Y| |19.8| |
wind_chill	        |windchill	|Y| |19.8| |
relative_humidity	|humidity	|Y| |36| |
precip	                |rain_rate	|Y| |0| |
precip_accum_local_day	|rain_today	|Y| |0| |
precip_accum_local_yesterday	|rain_yday	|Y| |0.186171| |
precip_accum_last_1hr	|rain_lasthour	|Y| |0| |
uv	                |uv	        |Y| |6.36| |
solar_radiation	        |solar	        |Y| |894| |
brightness	        |lux	        |Y| |107391| |
wind_avg	        |wind_speed<br />wind_speed_avg|Y| |0.6| |
wind_gust	        |wind_gust	|Y| |1.7| |
wind_direction	        |wind_direction	|Y| |96| |
lightning_strike_last_epoch	|lightningtimeago	|Y| |1588411787| |
lightning_strike_last_distance	|lightningkm	|Y| |20| |
lightning_strike_count	|lightning	|Y| |0| |
pressure_trend	        |barometer_trend_text|Y| |rising| |

station_id	|xx	|-| |2167| |
station_name	|xx	|-| |sluispark| |
public_name	|xx	|-| |sluispark| |
latitude	|xx	|-| |50.88632| |
longitude	|xx	|-| |4.70032| |
timezone	|xx	|-| |Europe/Brussels| |
elevation	|xx	|-| |42.2| |
is_public	|xx	|-| |1| |
status_code	|xx	|-| |0| |
status_message	|xx	|-| |SUCCESS| |
units_temp	|xx	|-| |c| |
units_wind	|xx	|-| |kph| |
units_precip	|xx	|-| |mm| |
units_pressure	|xx	|-| |hpa| |
units_distance	|xx	|-| |km| |
units_direction	|xx	|-| |degrees| |
units_other	|xx	|-| |metric| |
barometric_pressure	|xx	|-| |1018| |
station_pressure	|xx	|-| |1018| |
precip_minutes_local_day	|xx	|-| |0| |
precip_minutes_local_yesterday	|xx	|-| |1| |
wind_lull	|xx	|-| |0| |
lightning_strike_count_last_1hr	|xx	|-| |0| |
lightning_strike_count_last_3hr	|xx	|-| |0| |
feels_like	|xx	|-| |19.8| |
wet_bulb_temperature	|xx	|-| |11.8| |
delta_t	|xx	|-| |8| |
air_density	|xx	|-| |1.21056| |
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             { if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key = trim ($key);
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
} // eof load_wf
#  ---------------------------------------------
function load_wdapi()
     { global $specs;
        $string = '
00|date                 |?| |26/06/2019 | |
01|time                 |?| |15:56:16   | |
02|temp                 |Y| |14.8       | |
03|humidity             |Y| |78         | |
04|dewpoint             |Y| |14,5       | |
05|windspeed            |Y| |2.059      | |
06|wind_gust_speed      |Y| |4.632      | |
07|wind_direction       |Y| |335        | |
08|rain_rate            |Y| |0.00       | |
09|rain_today           |Y| |0.0        | |
10|barometer            |Y| |1016.9     | |
11|wind_dir2            |?|Y|335        | |
12|wind_dir2            |?|Y|2          | |
13|--                   |N|Y|--         | |
14|barometer_trend      |Y| |1026.4     | |
15|rain_month           |Y| |37.2       | |
16|rain_year            |Y| |179.8      | |
17|rain_yday            |Y| |0.2        | |
18|temp_indoor          |Y| |24.0       | |
19|humidity_indoor      |Y| |55         | |
20|windchill            |Y| |14.8       | |
21|temp 1 hour ago      |N|Y|18.5       | |
22|temp_high            |Y| |23.5       | |
23|temp_high_time       |Y| |00:00      | |
24|temp_low             |Y| |17.1       | |
25|temp_low_time        |Y| |10:35      | |
26|wind_speed_max       |Y| |5.147      | |
27|wind_speed_max_time  |Y| |10:35      | |
28|wind_gust_speed_max  |Y| |6.279      | |
29|wind_gust_speed_max_time|Y| |09:16      | |
30|barometer_max        |Y| |1026.9     | |
31|barometer_max_time   |Y| |15:27      | |
32|barometer_min        |Y| |1018.1     | |
33|barometer_min_time   |Y| |00:03      | |
34|swversion            |Y| |10.37S     | |
35|build                |N|Y|98         | |
36|wind_speed_avg       |Y| |5.147      | |
37|--                   |N|Y|--         | |
38|--                   |N|Y|--         | |
39|uv                   |Y|Y|2.4        | |
40|--                   |N|Y|--         | |
41|solar                |Y|Y|2.4        | |
42|avg 10 minute wind   |?|Y|353        | |
43|rain_lasthour        |Y| |0.0        | |
44|--                   |N|Y|--         | |
45|--                   |N|Y|--         | |
46|--                   |N|Y|--         | |
47|wind_direction_avg   |Y| |353        | |
48|--                   |N|Y|--         | |
49|--                   |N|Y|--         | |
50|dayLength            |?|Y|--         | |
51|--                   |N|Y|--         | |
52|--                   |N|Y|--         | |
53|uv_max               |?|Y|5.3        | |
54|max humidity         |?|Y|80         | |
55|max humidity time    |?|Y|06:17      | |
56|min humidity         |?|Y|77         | |
57|min humidity time    |?|Y|00:00      | |
58|max dewpoint         |?|Y|19.2       | |
59|max dewpoint time    |?|Y|00:00      | |
60|min dewpoint         |?|Y|13.4       | |
61|min dewpoint time    |?|Y|10:41      | |
62|temp_trend           |Y| |18.6       | |
63|humidity_trend       |Y| |78         | |
64|dewpoint 15 min      |?|Y|15.3       | |
65|indoor temp 15 min   |?|Y|24.0       | |
66|indoor humi 15 min   |?|Y|55         | |
67|extra_tmp1           |Y|Y|-50.0      | |
68|extra_tmp2           |Y|Y|-50.0      | |
69|extra_tmp3           |Y|Y|-50.0      | |
70|extra_hum1           |Y|Y|-100.0     | |
71|extra_hum2           |Y|Y|-100.0     | |
72|extra_hum3           |Y|Y|-100.0     | |
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             { if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = (int) $key;
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
} // eof load_wdapi
#  ---------------------------------------------
function load_rtxt()            # https://www.cumuluswiki.org/a/Realtime.txt
     { global $specs;
        $string = '
0 |date               |Y| |19/08/09 ||
1 |time               |Y| |16:03:45 |hh:mm:ss|
2 |temp               |Y| |8.4      ||
3 |humidity           |Y| |82       ||
4 |dewpoint           |Y| |5.8      ||
5 |wind_speed_avg     |Y| |24.2     ||
6 |wind_speed         |Y| |13.2     ||
7 |wind_direction     |Y| |202      ||
8 |rain_rate          |Y| |0.0      ||
9 |rain_today         |Y| |1.0      ||
10|barometer          |Y| |999.7    ||
11|windDirection      |-| |WNW      ||
12|windspeedBft       |-| |6        ||
13|wind_units         |Y| |km/h     ||
14|temp_units         |Y| |C        ||
15|barometer_units    |Y| |hPa      ||
16|rain_units         |Y| |mm       ||
17|wind_run           |Y|Y|146.6    ||
18|barometer_trend    |Y|Y|+0.1     ||
19|rain_month         |Y| |85.2     ||
20|rain_year          |Y| |588.4    ||
21|rain_yday          |Y|Y|11.6     ||
22|temp_indoor        |Y| |20.3     ||
23|humidity_indoor    |Y| |57       ||
24|wind_chill         |Y| |3.6      ||
25|temp_trend         |Y|Y|-0.7     ||
26|temp_high          |Y| |10.9     ||
27|temp_high_time     |Y| |12.00    ||
28|temp_low           |Y| |7.8      ||
29|temp_low_time      |Y| |14:41    ||
30|wind_speed_max     |Y| |37.4     ||
31|wind_speed_max_time|Y| |14:38    ||
32|wind_gust_speed_max|Y| |44.6     ||
33|wind_gust_speed_max_time|Y| |14:28    ||
34|barometer_max      |Y| |999.8    ||
35|barometer_max_time |Y| |16:01    ||
36|barometer_min      |Y| |998.4    ||
37|barometer_min_time |Y| |12:06    ||
38|CumulusVersion     |-| |1.8.7    ||
39|CumulusBuild       |-| |819      ||
40|wind_gust_speed    |Y|Y|36.0     ||
41|heat_index         |Y|Y|10.3     ||
42|humidex            |-| |10.5     ||
43|uv                 |Y|Y|13       ||
44|evapotranspiration |-| |0.2      ||
45|solar              |Y|Y|14       ||
46|wind_direction_avg |Y|Y|260      ||
47|rain_lasthour      |Y|Y|2.3      ||
48|zambrettiNr        |-| |3        ||
49|dayNight           |-| |1        ||
50|sensorLost         |-| |1        ||
51|windDirAvg         |-| |NNW      ||
52|cloudBase          |-| |2040     ||
53|cloudBaseUnits     |-| |ft       ||
54|apperentTemp       |-| |12.3     ||
55|sunshineToday      |-| |11.1     ||
56|solarMaxPos        |-| |420.1    ||
57|sunnyYN            |-| |1        ||
58|uv_max             |-| |13       ||
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             { if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = (int) $key;
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
} // eof load_rtxt
#  ---------------------------------------------
function load_cltrw()
     {  global $specs;
        $specs = array (
 0 => array( 'seq' =>	'0', 'label' => 'beginning of file',		'used'  => '',  'optional'=> ' ', 'type' => 'L', 'comment' => ''),
 1 => array( 'seq' =>	'1', 'label' => 'wind-speed  60# avg ',  'used'  => 'Y', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 2 => array( 'seq' =>	'2', 'label' => 'wind-speed current',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 3 => array( 'seq' =>	'3', 'label' => 'wind_direction',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
 4 => array( 'seq' =>	'4', 'label' => 'temp',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 5 => array( 'seq' =>	'5', 'label' => 'humidity',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 6 => array( 'seq' =>	'6', 'label' => 'barometer',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'H', 'comment' => ''),
 7 => array( 'seq' =>	'7', 'label' => 'rain_today',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 8 => array( 'seq' =>	'8', 'label' => 'rain_month',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 9 => array( 'seq' =>	'9', 'label' => 'rain_year',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 10 => array( 'seq' => '10', 'label' => 'rain_rate',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 11 => array( 'seq' => '11', 'label' => 'rain rate max',		'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 12 => array( 'seq' => '12', 'label' => 'temp_indoor',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 13 => array( 'seq' => '13', 'label' => 'humidity_indoor',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 14 => array( 'seq' => '14', 'label' => 'temp soil',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 15 => array( 'seq' => '15', 'label' => 'forecast icon',		'used'  => '', 'optional'=> ' ', 'type' => 'I', 'comment' => ''),
 16 => array( 'seq' => '16', 'label' => 'wmr968 extra temp',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 17 => array( 'seq' => '17', 'label' => 'wmr968 extra hum',		'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 18 => array( 'seq' => '18', 'label' => 'wmr968 extra sensor',		'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 19 => array( 'seq' => '19', 'label' => 'rain_yday',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
 20 => array( 'seq' => '20', 'label' => 'temp extra_sensor',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 21 => array( 'seq' => '21', 'label' => 'temp extra_sensor', 		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 22 => array( 'seq' => '22', 'label' => 'temp extra_sensor', 		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 23 => array( 'seq' => '23', 'label' => 'temp extra_sensor',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 24 => array( 'seq' => '24', 'label' => 'temp extra_sensor', 		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 25 => array( 'seq' => '25', 'label' => 'temp extra_sensor', 		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 26 => array( 'seq' => '26', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 27 => array( 'seq' => '27', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 28 => array( 'seq' => '28', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 29 => array( 'seq' => '29', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => 'format depends on wd setting'),
 30 => array( 'seq' => '30', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => 'format depends on wd setting'),
 31 => array( 'seq' => '31', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => 'format depends on wd setting'),
 32 => array( 'seq' => '32', 'label' => 'station name',			'used'  => '?', 'optional'=> ' ', 'type' => 'L', 'comment' => ''),
 33 => array( 'seq' => '33', 'label' => 'dallas lightning count',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 34 => array( 'seq' => '34', 'label' => 'solar reading',		'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 35 => array( 'seq' => '35', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 36 => array( 'seq' => '36', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 37 => array( 'seq' => '37', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 38 => array( 'seq' => '38', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 39 => array( 'seq' => '39', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 40 => array( 'seq' => '40', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 41 => array( 'seq' => '41', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 42 => array( 'seq' => '42', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 43 => array( 'seq' => '43', 'label' => 'wmr968 batt',			'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
 44 => array( 'seq' => '44', 'label' => 'windchill',		'used'  => 'Y',  'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 45 => array( 'seq' => '45', 'label' => 'humidex',			'used'  => '',  'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 46 => array( 'seq' => '46', 'label' => 'temp_high',		'used'  => 'Y',  'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 47 => array( 'seq' => '47', 'label' => 'temp_low',		'used'  => 'Y',  'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 48 => array( 'seq' => '48', 'label' => 'currentweathericon',	'used'  => 'Y',  'optional'=> ' ', 'type' => 'I', 'comment' => ''),
 49 => array( 'seq' => '49', 'label' => 'currentdescription',	'used'  => 'Y',  'optional'=> ' ', 'type' => 'L', 'comment' => ''),
 50 => array( 'seq' => '50', 'label' => 'barometer_trend',	'used'  => 'Y',  'optional'=> ' ', 'type' => 'H', 'comment' => ''),
 51 => array( 'seq' => '51', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 52 => array( 'seq' => '52', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 53 => array( 'seq' => '53', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 54 => array( 'seq' => '54', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 55 => array( 'seq' => '55', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 56 => array( 'seq' => '56', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 57 => array( 'seq' => '57', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 58 => array( 'seq' => '58', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 59 => array( 'seq' => '59', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 60 => array( 'seq' => '60', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 61 => array( 'seq' => '61', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 62 => array( 'seq' => '62', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 63 => array( 'seq' => '63', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 64 => array( 'seq' => '64', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 65 => array( 'seq' => '65', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 66 => array( 'seq' => '66', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 67 => array( 'seq' => '67', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 68 => array( 'seq' => '68', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 69 => array( 'seq' => '69', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 70 => array( 'seq' => '70', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 71 => array( 'seq' => '71', 'label' => 'gust max today',	        'used'  => 'Y',   'optional'=> ' ', 'type' => 'K', 'comment' => 'used for wind speed chart'),
 72 => array( 'seq' => '72', 'label' => 'dewpoint',		        'used'  => 'Y',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 73 => array( 'seq' => '73', 'label' => 'cloud height',			'used'  => '',   'optional'=> ' ', 'type' => 'F', 'comment' => ''),
 74 => array( 'seq' => '74', 'label' => 'actual_date',			'used'  => '?', 'optional'=> ' ', 'type' => 'L', 'comment' => 'format depends on wd setting'),
 75 => array( 'seq' => '75', 'label' => 'humidex max',			'used'  => '',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 76 => array( 'seq' => '76', 'label' => 'humidex min',			'used'  => '',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 77 => array( 'seq' => '77', 'label' => 'windchill max',		'used'  => '',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 78 => array( 'seq' => '78', 'label' => 'windchill_low',	        'used'  => 'Y',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 79 => array( 'seq' => '79', 'label' => 'uv',		                'used'  => 'Y',   'optional'=> ' ', 'type' => 'N', 'comment' => ''),
 80 => array( 'seq' => '80', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 81 => array( 'seq' => '81', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 82 => array( 'seq' => '82', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 83 => array( 'seq' => '83', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 84 => array( 'seq' => '84', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 85 => array( 'seq' => '85', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 86 => array( 'seq' => '86', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 87 => array( 'seq' => '87', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 88 => array( 'seq' => '88', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 89 => array( 'seq' => '89', 'label' => 'wind speed hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
 90 => array( 'seq' => '90', 'label' => 'temp_trend',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 91 => array( 'seq' => '91', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 92 => array( 'seq' => '92', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 93 => array( 'seq' => '93', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 94 => array( 'seq' => '94', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 95 => array( 'seq' => '95', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 96 => array( 'seq' => '96', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 97 => array( 'seq' => '97', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 98 => array( 'seq' => '98', 'label' => 'temp hour',			'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
 99 => array( 'seq' => '99', 'label' => 'temp_trend',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
100 => array( 'seq' => '100', 'label' => 'rain_lasthour',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
101 => array( 'seq' => '101', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
102 => array( 'seq' => '102', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
103 => array( 'seq' => '103', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
104 => array( 'seq' => '104', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
105 => array( 'seq' => '105', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
106 => array( 'seq' => '106', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
107 => array( 'seq' => '107', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
108 => array( 'seq' => '108', 'label' => 'rain hour',			'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
109 => array( 'seq' => '109', 'label' => 'rain_lasthour',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => ''),
110 => array( 'seq' => '110', 'label' => 'heatindex max',		'used'  => '',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
111 => array( 'seq' => '111', 'label' => 'heatindex min',		'used'  => '',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
112 => array( 'seq' => '112', 'label' => 'heat_index',		'used'  => 'Y',   'optional'=> ' ', 'type' => 'C', 'comment' => ''),
113 => array( 'seq' => '113', 'label' => 'wind_speed_max',	'used'  => 'Y',   'optional'=> ' ', 'type' => 'K', 'comment' => ''),
114 => array( 'seq' => '114', 'label' => '# lightning strikes last min','used'  => '',   'optional'=> ' ', 'type' => 'N', 'comment' => ''),
115 => array( 'seq' => '115', 'label' => 'lightning last time',	        'used'  => '',   'optional'=> ' ', 'type' => 'T', 'comment' => ''),
116 => array( 'seq' => '116', 'label' => 'lightning last date',	        'used'  => '',   'optional'=> ' ', 'type' => 'L', 'comment' => ''),
117 => array( 'seq' => '117', 'label' => 'wind_direction_avg',	'used'  => 'Y',   'optional'=> ' ', 'type' => 'D', 'comment' => ''),
118 => array( 'seq' => '118', 'label' => 'nexstorm dist',		'used'  => '',   'optional'=> ' ', 'type' => 'N', 'comment' => ''),
119 => array( 'seq' => '119', 'label' => 'nexstorm bearing',		'used'  => '',   'optional'=> ' ', 'type' => 'D', 'comment' => ''),
120 => array( 'seq' => '120', 'label' => 'temp extra_sensor',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
121 => array( 'seq' => '121', 'label' => 'temp extra_sensor',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
122 => array( 'seq' => '122', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
123 => array( 'seq' => '123', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
124 => array( 'seq' => '124', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
125 => array( 'seq' => '125', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
126 => array( 'seq' => '126', 'label' => 'humidity extra_sensor',	'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
127 => array( 'seq' => '127', 'label' => 'solar',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
128 => array( 'seq' => '128', 'label' => 'temp indoor max',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
129 => array( 'seq' => '129', 'label' => 'temp indoor min',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
130 => array( 'seq' => '130', 'label' => 'apparent temp',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
131 => array( 'seq' => '131', 'label' => 'barometer_max',	        'used'  => 'Y', 'optional'=> ' ', 'type' => 'H', 'comment' => 'also: baro max'),
132 => array( 'seq' => '132', 'label' => 'barometer_min',	        'used'  => 'Y', 'optional'=> ' ', 'type' => 'H', 'comment' => 'also: baro min'),
133 => array( 'seq' => '133', 'label' => 'gust max last_hour',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
134 => array( 'seq' => '134', 'label' => 'gust max last_hour time',	'used'  => '', 'optional'=> ' ', 'type' => 'T', 'comment' => ''),
135 => array( 'seq' => '135', 'label' => 'gust max today time',         'used'  => 'Y', 'optional'=> ' ', 'type' => 'T', 'comment' => ''),
136 => array( 'seq' => '136', 'label' => 'apparent temp max',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
137 => array( 'seq' => '137', 'label' => 'apparent temp min',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
138 => array( 'seq' => '138', 'label' => 'dewpoint max',		'used'  => '', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
139 => array( 'seq' => '139', 'label' => 'dewpoint_low',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
140 => array( 'seq' => '140', 'label' => 'gust last min max',		'used'  => '', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
141 => array( 'seq' => '141', 'label' => 'datetime',			'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
142 => array( 'seq' => '142', 'label' => 'thsws',			'used'  => '', 'optional'=> ' ', 'type' => 'L', 'comment' => 'if enabled in wd'),
143 => array( 'seq' => '143', 'label' => 'temp trend',			'used'  => '', 'optional'=> ' ', 'type' => 'B', 'comment' => 'logic'),
144 => array( 'seq' => '144', 'label' => 'humidity_trend',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'B', 'comment' => 'logic'),
145 => array( 'seq' => '145', 'label' => 'humidex trend',		'used'  => '', 'optional'=> ' ', 'type' => 'B', 'comment' => 'logic'),
146 => array( 'seq' => '146', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
147 => array( 'seq' => '147', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
148 => array( 'seq' => '148', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
149 => array( 'seq' => '149', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
150 => array( 'seq' => '150', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
151 => array( 'seq' => '151', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
152 => array( 'seq' => '152', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
153 => array( 'seq' => '153', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
154 => array( 'seq' => '154', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
155 => array( 'seq' => '155', 'label' => 'wind dir hour',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => ''),
156 => array( 'seq' => '156', 'label' => 'leaf wetness',		'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
157 => array( 'seq' => '157', 'label' => 'moisture soil',		'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
158 => array( 'seq' => '158', 'label' => 'wind_speed_avg',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'K', 'comment' => ''),
159 => array( 'seq' => '159', 'label' => 'wetbulb',		'used'  => 'Y', 'optional'=> ' ', 'type' => 'C', 'comment' => ''),
160 => array( 'seq' => '160', 'label' => 'latitude',			'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => '- for southern hemisphere'),
161 => array( 'seq' => '161', 'label' => 'longitude',			'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => '- for east of gmt'),
162 => array( 'seq' => '162', 'label' => 'rain total 9am reset',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'M', 'comment' => 'default #7 is used '),
163 => array( 'seq' => '163', 'label' => 'humidity high day',		'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
164 => array( 'seq' => '164', 'label' => 'humidity low day ',		'used'  => '', 'optional'=> ' ', 'type' => 'P', 'comment' => ''),
165 => array( 'seq' => '165', 'label' => 'rain total midnight reset',   'used'  => '', 'optional'=> ' ', 'type' => 'M', 'comment' => 'mm'),
166 => array( 'seq' => '166', 'label' => 'windchill_low_time',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'T', 'comment' => ''),
167 => array( 'seq' => '167', 'label' => 'current cost channel 1',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => 'watts'),
168 => array( 'seq' => '168', 'label' => 'current cost channel 2',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
169 => array( 'seq' => '169', 'label' => 'current cost channel 3',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
170 => array( 'seq' => '170', 'label' => 'current cost channel 4',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
171 => array( 'seq' => '171', 'label' => 'current cost channel 5',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
172 => array( 'seq' => '172', 'label' => 'current cost channel 6',	'used'  => '', 'optional'=> ' ', 'type' => 'N', 'comment' => ''),
173 => array( 'seq' => '173', 'label' => 'wind_run',	        'used'  => 'Y', 'optional'=> ' ', 'type' => 'N', 'comment' => '9am or midnight reset'),
174 => array( 'seq' => '174', 'label' => 'temp_high_time',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'T', 'comment' => 'Time of daily max temp'),
175 => array( 'seq' => '175', 'label' => 'temp_low_time',	'used'  => 'Y', 'optional'=> ' ', 'type' => 'T', 'comment' => 'Time of daily min temp'),
176 => array( 'seq' => '176', 'label' => 'wind dir avg 10min',		'used'  => '', 'optional'=> ' ', 'type' => 'D', 'comment' => '10 minute average wind direction'),
177 => array( 'seq' => '177', 'label' => 'wd version - end of file',    'used'  => '', 'optional'=> ' ', 'type' => 'L', 'comment' => 'example: !!c10.37f!!'),
);
}// eof load_cltrw
#  ---------------------------------------------
function load_dwl_v2api()
     { global $specs;
        $string = '
generated_at    	        |datetime               |Y| |1632065947| |
bar_sea_level	                |barometer 	        |Y| |30.363     | |
bar_trend                       |barometer_trend 	|Y| |-0.002     | |
n/a                             |barometer_trend_text 	| | |           | |
temp_in	                        |temp_indoor 	        |Y| |66         | |
dew_point	                |dewpoint 	        |Y| |55.6       | |
temp 	                        |temp 	                |Y| |71.4       | |
heat_index	                |heat_index 	        |Y|Y|64.0       | |
wind_chill	                |windchill 	        |Y| |65.0| |
hum	                        |humidity        	|Y| |61| |
hum_in          	        |humidity_indoor 	|Y| |65| |
rain_rate_hi_in 	        |rain_rate       	|Y| |0.0000| |
rainfall_daily_in	        |rain_today 	        |Y| |0.0079| |
rainfall_monthly_in	        |rain_month 	        |Y| |0.3071| |
rainfall_year_in	        |rain_year 	        |Y| |15.6929| |
rainfall_last_60_min_in         |rain_lasthour          |?| |0  | |
uv_index	                |uv 	                |Y|Y|0.8| |
solar_rad     	                |solar 	                |Y|Y|148| |
wind_speed_last	                |wind_speed 	        |Y| |5.0| |
wind_speed_avg_last_10_min	|wind_speed_avg |Y| |4.0| |
wind_speed_hi_last_10_min	|wind_gust_speed|Y| |7.0| |
wind_dir_at_hi_speed_last_2_min |wind_direction |Y| |182| |
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             {  if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = trim ($key);
                if ( array_key_exists($key,$specs)) { echo $key.' exists <br />';}
                $specs[$key]  = array ('label' => trim($label), 'used' => $used, 
                                        'optional' => $optional, 'value' => $value, 'comment' => $comment );}
        } // eof 
#  ---------------------------------------------
function load_dwl()
     {   global $specs;
        $string = '
observation_time_rfc822	|datetime               |Y| |Thu, 12 Sep 2019 10:01:16 +0200| |
pressure_in	        |barometer 	        |Y| |30.363| |
pressure_day_high_in	|barometer_max 	        |Y| |30.363| |
pressure_day_high_time	|barometer_max_time 	|Y| |9:48am| |
pressure_day_low_in	|barometer_min 	        |Y| |30.255| |
pressure_day_low_time	|barometer_min_time 	|Y| |12:01am| |
pressure_tendency_string|barometer_trend_text 	|Y| |Rising Slowly| |
temp_in_f	        |temp_indoor 	        |Y| |72.1| |
dewpoint_f	        |dewpoint 	        |Y| |51.0| |
dewpoint_day_low_f	|dewpoint_low 	        |Y| |50| |
dewpoint_day_low_time	|dewpoint_low_time 	|Y| |8:26am| |
temp_f	                |temp 	                |Y| |65.3| |
temp_day_high_f	        |temp_high 	        |Y| |65.3| |
temp_day_high_time	|temp_high_time       	|Y| |9:57am| |
temp_day_low_f	        |temp_low 	        |Y| |62.3| |
temp_day_low_time	|temp_low_time 	        |Y| |7:52am| |
heat_index_f	        |heat_index 	        |Y|Y|64.0| |
windchill_f	        |windchill 	        |Y| |65.0| |
windchill_day_low_f	|windchill_low 	        |Y| |62| |
windchill_day_low_time	|windchill_low_time 	|Y| |7:41am| |
temp_extra_1            |extra_tmp1             |?|Y|54.0| |
temp_extra_2            |extra_tmp2             |?|Y|54.0| |
temp_extra_3            |extra_tmp3             |?|Y|54.0| |
temp_extra_4            |extra_tmp4             |?|Y|54.0| |
temp_extra_5            |extra_tmp5             |?|Y|54.0| |
temp_extra_6            |extra_tmp6             |?|Y|54.0| |
temp_extra_7            |extra_tmp7             |?|Y|54.0| |
temp_soil_1             |soil_tmp1              |?|Y|54.0| |
temp_soil_2             |soil_tmp2              |?|Y|54.0| |
temp_soil_3             |soil_tmp3              |?|Y|54.0| |
temp_soil_4             |soil_tmp4              |?|Y|54.0| |
temp_leaf_1             |leaf_tmp1              |?|Y|54.0| |
temp_leaf_2             |leaf_tmp2              |?|Y|54.0| |
temp_leaf_3             |leaf_tmp3              |?|Y|54.0| |
temp_leaf_4             |leaf_tmp4              |?|Y|54.0| |
relative_humidity	|humidity        	|Y| |61| |
relative_humidity_in	|humidity_indoor 	|Y| |65| |
relative_humidity_1     |extra_hum1             |?|Y|60| |
relative_humidity_2     |extra_hum2             |?|Y|60| |
relative_humidity_3     |extra_hum3             |?|Y|60| |
relative_humidity_4     |extra_hum4             |?|Y|60| |
relative_humidity_5     |extra_hum5             |?|Y|60| |
relative_humidity_6     |extra_hum6             |?|Y|60| |
relative_humidity_7     |extra_hum7             |?|Y|60| |
soil_moisture_1         |soil_mst1              |?|Y|100| |
soil_moisture_2         |soil_mst2              |?|Y|100| |
soil_moisture_3         |soil_mst3              |?|Y|100| |
soil_moisture_4         |soil_mst4              |?|Y|100| |
leaf_wetness_1          |leaf_wetness1          |?|Y|0| |
leaf_wetness_2          |leaf_wetness2          |?|Y|0| |
leaf_wetness_3          |leaf_wetness3          |?|Y|0| |
leaf_wetness_4          |leaf_wetness4          |?|Y|0| |
rain_rate_in_per_hr	|rain_rate       	|Y| |0.0000| |
rain_day_in	        |rain_today 	        |Y| |0.0079| |
rain_month_in	        |rain_month 	        |Y| |0.3071| |
rain_year_in	        |rain_year 	        |Y| |15.6929| |
et_day                  |et_day                 |?|Y|0.019| |
et_month                |et_month               |?|Y|1.210| |
et_year                 |et_year                |?|Y|28.220| |
uv_index	        |uv 	                |?|Y|0.8| |
solar_radiation	        |solar 	                |?|Y|148| |
solar_radiation_day_high	|solar_max 	|?|Y|234| |
solar_radiation_day_high_time	|solar_max_time |?|Y|9:53am| |
uv_index_day_high	|uv_index_day_high 	|?|Y|0.9| |
uv_index_day_high_time	|uv_index_day_high_time |?|Y|9:49am| |
wind_mph	        |wind_speed 	        |Y| |5.0| |
wind_ten_min_avg_mph	|wind_speed_avg 	|Y| |4.0| |
wind_ten_min_gust_mph	|wind_gust_speed 	|Y| |7.0| |
wind_day_high_mph	|wind_speed_max 	|Y| |11.0| |
wind_day_high_time	|wind_speed_max_time 	|Y| |12:04am| |
wind_degrees	        |wind_direction 	|Y| |182| |
credit	|not used 	|-| |Davis Instruments Corp.| || |
credit_URL	|not used 	|-| |http://www.davisnet.com| |
disclaimer_url	|not used 	|-| |http://www.davisnet.com/about/terms.asp| |
copyright_url	|not used 	|-| |http://www.davisnet.com/about/terms.asp| |
privacy_policy_url	|not used 	|-| |http://www.davisnet.com/about/privacy.asp| |
url	|not used 	|-| |http://www.weatherlink.com/images/Logo_Davis_reflxblu.jpg| |
title	|not used 	|-| |Davis WeatherLink| |
link	|not used 	|-| |http://www.weatherlink.com| |
suggested_pickup	|not used 	|-| |15 minutes after the hour| |
suggested_pickup_period	|not used 	|-| |60| |
dewpoint_c	|not used 	|-| |10.6| |
dewpoint_string	|not used 	|-| |51.0 F (10.6 C)| |
heat_index_c	|not used 	|-| |17.8| |
heat_index_string	|not used 	|-| |64.0 F (17.8 C)| |
location	|not used 	|-| |Leuven, Vlaanderen, Belgium| |
latitude	|not used 	|-| |50.90447| |
longitude	|not used 	|-| |4.69575| |
observation_time	|not used 	|-| |Last Updated on Sep 12 2019, 10:01 am CEST| |
pressure_mb	|not used 	|-| |1028.2| |
pressure_string	|not used 	|-| |1028.2 mb| |
station_id	|not used 	|-| |herent| |
temp_c	|not used 	|-| |18.5| |
temperature_string	|not used 	|-| |65.3 F (18.5 C)| |
wind_dir	|not used 	|-| |South| |
wind_kt	|not used 	|-| |4.3| |
windchill_c	|not used 	|-| |18.3| |
windchill_string	|not used 	|-| |65.0 F (18.3 C)| |
DID	|not used 	|-| |001D0A003B10| |
station_name	|not used 	|-| |Weerstation Wilsele-Herent| |
observation_age	|not used 	|-| |16| |
dewpoint_day_high_f	|not used 	|-| |53| |
dewpoint_day_high_time	|not used 	|-| |3:04am| |
dewpoint_month_high_f	|not used 	|-| |53| |
dewpoint_month_low_f	|not used 	|-| |33| |
dewpoint_year_high_f	|not used 	|-| |63| |
dewpoint_year_low_f	|not used 	|-| |-4| |
heat_index_day_high_f	|not used 	|-| |64| |
heat_index_day_high_time	|not used 	|-| |9:36am| |
heat_index_month_high_f	|not used 	|-| |71| |
heat_index_year_high_f	|not used 	|-| |105| |
pressure_month_high_in	|not used 	|-| |30.363| |
pressure_month_low_in	|not used 	|-| |29.891| |
pressure_year_high_in	|not used 	|-| |30.754| |
pressure_year_low_in	|not used 	|-| |29.104| |
rain_rate_day_high_in_per_hr	|not used 	|-| |0.0079| |
rain_rate_day_high_time	|not used 	|-| |7:00am| |
rain_rate_hour_high_in_per_hr	|not used 	|-| |0.0000| |
rain_rate_month_high_in_per_hr	|not used 	|-| |2.5354| |
rain_rate_year_high_in_per_hr	|not used 	|-| |7.3150| |
rain_storm_in	|not used 	|-| |0.0394| |
rain_storm_start_date	|not used 	|-| |9/11/2019| |
relative_humidity_day_high	|not used 	|-| |71| |
relative_humidity_day_high_time	|not used 	|-| |3:25am| |
relative_humidity_day_low	|not used 	|-| |61| |
relative_humidity_day_low_time	|not used 	|-| |9:50am| |
relative_humidity_month_high	|not used 	|-| |75| |
relative_humidity_in_day_high	|not used 	|-| |65| |
relative_humidity_in_day_high_time	|not used 	|-| |4:48am| |
relative_humidity_in_day_low	|not used 	|-| |64| |
relative_humidity_in_day_low_time	|not used 	|-| |12:00am| |
relative_humidity_in_month_high	|not used 	|-| |65| |
relative_humidity_in_month_low	|not used 	|-| |57| |
relative_humidity_in_year_high	|not used 	|-| |72| |
relative_humidity_in_year_low	|not used 	|-| |41| |
solar_radiation_month_high	|not used 	|-| |879| |
solar_radiation_year_high	|not used 	|-| |1169| |
sunrise	|not used 	|-| |7:11am| |
sunset	|not used 	|-| |8:03pm| |
temp_month_high_f	|not used 	|-| |72.5| |
temp_month_low_f	|not used 	|-| |42.9| |
temp_year_high_f	|not used 	|-| |107.0| |
temp_year_low_f	|not used 	|-| |19.0| |
temp_in_day_high_f	|not used 	|-| |72.1| |
temp_in_day_high_time	|not used 	|-| |12:03am| |
temp_in_day_low_f	|not used 	|-| |71.9| |
temp_in_day_low_time	|not used 	|-| |3:38am| |
temp_in_month_high_f	|not used 	|-| |75.0| |
temp_in_month_low_f	|not used 	|-| |71.7| |
temp_in_year_high_f	|not used 	|-| |78.0| |
temp_in_year_low_f	|not used 	|-| |65.0| |
uv_index_month_high	|not used 	|-| |5.1| |
uv_index_year_high	|not used 	|-| |6.9| |
wind_month_high_mph	|not used 	|-| |20.0| |
wind_year_high_mph	|not used 	|-| |46.0| |
windchill_month_low_f	|not used 	|-| |43| |
windchill_year_low_f	|not used 	|-| |19| |
time_to_generate	|not used 	|-| |0.005742 seconds| 
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             {  if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = trim ($key);
                if ( array_key_exists($key,$specs)) { echo $key.' exists <br />';}
                $specs[$key]  = array ('label' => trim($label), 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
}    // eof load_dwl    
#  ---------------------------------------------
function load_ecolcl()
     {  global $specs;
        $string = '
stationtype     |not used               |-| |GW1000A_V1.5.8||   
dateutc         |datetime               |Y| |2020-05-29 09:20:55| |
tempinf	        |temp_indoor            |Y|Y|72.7| |
humidityin	|humidity_indoor        |Y| |37| |
baromrelin	|barometer	        |Y| |30.295| |
baromabsin	|not used               |-| |30.295| |
tempf	        |temp	                |Y| |65.5| |
temp1f	        |extra_tmp1	        |Y|Y|63.86| |
temp2f	        |extra_tmp2	        |Y|Y|60.5| |
temp3f	        |extra_tmp3	        |Y|Y|60.5| |
temp4f	        |extra_tmp4	        |Y|Y|60.5| |
temp5f	        |extra_tmp5	        |Y|Y|60.5| |
temp6f	        |extra_tmp6	        |Y|Y|60.5| |
temp7f	        |extra_tmp7	        |Y|Y|60.5| |
temp8f	        |extra_tmp8	        |Y|Y|60.5| |
humidity	|humidity	        |Y| |46| |
humidity1	|extra_hum1	        |Y|Y|46| |
humidity2	|extra_hum2	        |Y|Y|46| |
humidity3	|extra_hum3	        |Y|Y|46| |
humidity4	|extra_hum4	        |Y|Y|46| |
humidity5	|extra_hum5	        |Y|Y|46| |
humidity6	|extra_hum6	        |Y|Y|46| |
humidity7	|extra_hum7	        |Y|Y|46| |
humidity8	|extra_hum8	        |Y|Y|46| |
rainratein	|not used               |-| |0.000
eventrainin	|not used               |-| |0.000
hourlyrainin	|rain_rate	        |Y| |0.000
dailyrainin	|dailyrainin	        |Y| |0.000
weeklyrainin	|not used               |-| |0.000
monthlyrainin	|rain_month             |Y| |0.177
yearlyrainin	|rain_year              |Y| |0.559
totalrainin	|not used               |-| |0.559
soilmoisture1   |soil_mst1              |Y|Y|49| |
soilmoisture2   |soil_mst2              |Y|Y|48| |
soilmoisture3   |soil_mst3              |Y|Y|47| |
soilmoisture4   |soil_mst4              |Y|Y|47| |
pm25_ch1        |pm25_crnt1             |Y|Y|3.0| |
pm25_ch2        |pm25_crnt2             |Y|Y|3.0| |
pm25_ch3        |pm25_crnt3             |Y|Y|3.0| |
pm25_ch4        |pm25_crnt4             |Y|Y|3.0| |
pm25_avg_24h_ch1|pm25_24avg1            |Y|Y|2.8| |
pm25_avg_24h_ch2|pm25_24avg2            |Y|Y|2.8| |
pm25_avg_24h_ch3|pm25_24avg3            |Y|Y|2.8| |
pm25_avg_24h_ch4|pm25_24avg4            |Y|Y|2.8| |
lightning_time  |lightningtime          |Y|Y| | |
lightning_num   |lightningmi            |Y|Y|0| |
lightning       |lightning              |Y|Y| | |
winddir	        |wind_direction         |Y| |111
windspeedmph	|wind_speed             |Y| |0.67
windgustmph	|windgustmph            |Y| |1.12
maxdailygust	|wind_gust_speed_max    |Y| |6.93
solarradiation	|solar                  |Y| |642.38
uv	        |uv                     |Y| |7
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             {  if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = trim ($key);
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
     } // eof load_ecolcl
#  ---------------------------------------------
function load_awapi()
     {  global $specs;
        $string = '
macAddress	|checked only	|-| |B8:D8:12:60:40:A7| |
dateutc	        |datetime	|Y| |1590826500000|milliseconds |
winddir	        |wind_direction	|Y| |130| |
windspeedmph	|wind_speed	|Y| |0.9| |
windgustmph	|wind_gust_speed	|Y| |6| |
maxdailygust	|wind_gust_speed_max	|Y| |6| |
winddir_avg10m	|wind_direction_avg	|Y| |323| |
windspdmph_avg10m	|wind_speed_avg	|Y| |0.8| |
tempf	        |temp	|Y| |68| |
humidity	|humidity	|Y| |40| |
baromrelin	|barometer	|Y| |30.26| |
tempinf	        |temp_indoor	|Y|Y|73.8| |
humidityin	|humidity_indoor	|Y| |29| |
hourlyrainin	|rain_lasthour<br />rain_rate	|Y| |0| |
dailyrainin	|rain_today |Y| |0| |
monthlyrainin	|rain_month	|Y| |0.19| |
yearlyrainin	|rain_year	|Y| |8.33| |
dewPoint	|dewpoint	|Y| |42.77| |

pm25            |pm25_crnt1     |?|Y|xx| |
pm25_24h        |pm25_24avg1    |?|Y|xx| |
pm25_in         |pm25_crnt2     |?|Y|xx| |
pm25_24h_in     |pm25_24avg2    |?|Y|xx| |

lightning_time  |lightningtime  |?|Y|xx| |
lightning_distance |lightningmi |?|Y|xx| |
lightning_num   |lightning      |?|Y|xx| |

uv              |uv     |?|Y|2 | |
solar           |solar     |?|Y|2 | |

temp1f          |extra_tmp1     |?|Y|68| |
temp2f          |extra_tmp2     |?|Y|68| |
temp3f          |extra_tmp3     |?|Y|68| |
temp4f          |extra_tmp4     |?|Y|68| |
temp5f          |extra_tmp5     |?|Y|68| |
temp6f          |extra_tmp6     |?|Y|68| |
temp7f          |extra_tmp7     |?|Y|68| |
temp8f          |extra_tmp8     |?|Y|68| |
soiltmp1f       |soil_tmp1      |?|Y|68| |
soiltmp2f       |soil_tmp2      |?|Y|68| |
soiltmp3f       |soil_tmp3      |?|Y|68| |
soiltmp4f       |soil_tmp4      |?|Y|68| |
soiltmp5f       |soil_tmp5      |?|Y|68| |
soiltmp6f       |soil_tmp6      |?|Y|68| |
soiltmp7f       |soil_tmp7      |?|Y|68| |
soiltmp8f       |soil_tmp8      |?|Y|68| |
extra_hum1      |extra_hum1     |?|Y|68| |
extra_hum2      |extra_hum2     |?|Y|68| |
extra_hum3      |extra_hum3     |?|Y|68| |
extra_hum4      |extra_hum4     |?|Y|68| |
extra_hum5      |extra_hum5     |?|Y|68| |
extra_hum6      |extra_hum6     |?|Y|68| |
extra_hum7      |extra_hum7     |?|Y|68| |
extra_hum8      |extra_hum8     |?|Y|68| |
soilmoisture1   |soil_mst1      |?|Y|68| |
soilmoisture2   |soil_mst2      |?|Y|68| |
soilmoisture3   |soil_mst3      |?|Y|68| |
soilmoisture4   |soil_mst4      |?|Y|68| |
soilmoisture5   |soil_mst5      |?|Y|68| |
soilmoisture6   |soil_mst6      |?|Y|68| |
soilmoisture7   |soil_mst7      |?|Y|68| |
soilmoisture8   |soil_mst8      |?|Y|68| |
windgustdir	|xx	|-| |105| |
winddir_avg2m	|xx	|-| |105| |
windspdmph_avg2m	|xx	|-| |0.5| |
baromabsin	|xx	|-| |30.11| |
battin	|xx	|-| |0| |
battout	|xx	|-| |0| |
feelsLike	|xx	|-| |68| |
feelsLikein	|xx	|-| |72.2| |
dewPointin	|xx	|-| |39.5| |
lastRain	|xx	|-| |2020-05-11T01:06:00.000Z| |
tz	|xx	|-| |Europe/Luxembourg| |
date	|xx	|-| |2020-05-30T08:15:00.000Z| |
name	|xx	|-| |Sluispark| |
lon	|xx	|-| |4.7001638412476| |
lat	|xx	|-| |50.885124731899| |
address	|xx	|-| |Sluisstraat 16, 3000 Leuven, Belgium| |
location|xx	|-| |Leuven| |
elevation	|xx	|-| |19.312223434448| |
type	|xx	|-| |Point| |
0	|xx	|-| |4.7001638412476| |
1	|xx	|-| |50.885124731899| |
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             {  if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = trim ($key);
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
     } // eof load_ecolcl
#  ---------------------------------------------
function load_wu()
     {  global $specs;
        $string = '
epoch	        |datetime	|Y| |1590835665| |
pressure	|barometer	|Y| |1024.04| |
dewpt	        |dewpoint	|Y| |4.4| |
temp	        |temp	        |Y| |22.3| |
heatIndex	|heat_index	|Y| |21.4| |
windChill	|windchill	|Y| |22.3| |
humidity	|humidity	|Y| |31| |
precipRate	|rain_rate	|Y| |0| |
precipTotal	|rain_today	|Y| |0| |
uv	        |uv	        |Y|Y|| |
solarRadiation	|solar  	|Y|Y|| |
windSpeed	|wind_speed	|Y| |0| |
windGust	|wind_gust_speed|Y| |6.4| |
winddir	        |wind_direction	|Y| |96| |

stationID	|xx	|-| |IVLAAMSG47| |
obsTimeUtc	|xx	|-| |2020-05-30T10:47:45Z| |
obsTimeLocal	|xx	|-| |2020-05-30 12:47:45| |
neighborhood	|xx	|-| |Leuven| |
softwareType	|xx	|-| |meteobridge| |
country	        |xx	|-| |BE| |
lon	        |xx	|-| |4.697543| |
realtimeFrequency|xx	|-| || |
lat	        |xx	|-| |50.89502| |
qcStatus	|xx	|-| |1| |
elev	        |xx	|-| |38.1| |
';
        $arr    = explode ("\n",$string);
        $specs  = array();
        foreach ($arr as $line)
             {  if (trim($line) == '') {continue;}
                list ($key,$label,$used,$optional,$value,$comment) = explode ('|',$line);
                $key    = trim ($key);
                $specs[$key]  = array ('label' => $label, 'used' => $used, 'optional' => $optional, 'value' => $value, 'comment' => $comment );}
     } // eof load_ecolcl
