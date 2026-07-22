<?php $scrpt_vrsn_dt  = 'fct_yrno_popup.php|01|2023-09-09|';  # PHP8.2 graph | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$use_tabs       = true; # tabs to be used in pop-up 
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
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
# ------------------- save list of loaded scrips
$stck_lst       = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.date('c').' tz difference='.date('P').PHP_EOL; 
#----------------------------------------------
$ltxt_clsppp    = lang('Close');
#
$round_crnr     = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     { $round_crnr     = 50;}  
#  
# -----------------   load general metno fct code
$scrpt          = 'fct_yrno_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include $scrpt;  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.date('c').' tz difference='.date('P').PHP_EOL; 
if (count ($frct_mtn_dp) == 0) {echo '??'; return false;} 
#
if ($clockformat == '24') 
     {  $date_time_frmt = 'l  j  F';
        $month_day_frmt = 'j  F';}
else {  $date_time_frmt = 'D M j';
        $month_day_frmt = 'F  j';}
#
if (!function_exists('windlabel') ) {
#----------------------------------------------- 
#      windlabel convert degrees to compass name   
#----------------------------------------------- 
$windlabel_dfld = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
                       'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
$windlabel_shrt = array ('N','NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S',
		        'SSW','SW', 'WSW', 'W', 'WNW', 'NW', 'NNW');
function windlabel($value, $short = false)
     {  global $windlabel_dfld, $windlabel_shrt;
        $degr   = (int) $value;
        $key    = (int) fmod((($degr + 11) / 22.5),16);   # 2022-03-29
        if ($short <> false)
             {  return $windlabel_dfld[$key];}
        else {  return $windlabel_shrt[$key];}
        }
}
$toKnots        = 1.943844;
# ------------------------- translation of texts
$norain         = '-';
$nouv           = '-';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $close  = '    <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>Met.no Forecast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden; width:100%; height: 100%; ">
<div class="PWS_module_title font_head" style="width: 100%; padding: 2px;" >
'.$close.'
    <span class="tab" style="margin: 0 auto; font-size: 12px;">
        <label class="tablinks active"   onclick="openTab(event, \'containerTemp\')" id="defaultOpen">'       .lang('Graph').'</label>
        <label class="tablinks"          onclick="openTab(event, \'Forecast\')">'     .lang('Forecast').'</label>
        <label class="tablinks"          onclick="openTab(event, \'6-hour\')">'.lang('Forecast 6 hour interval').'</label>
        <label class="tablinks"          onclick="openTab(event, \'Hourly\')">'       .lang('Hourly forecast').'</label>
    </span>
    <small style="float: right; padding-top: 0px;">
        <a href="https://api.met.no/" target="_blank" style="color: grey">'
        .lang("Powered by").':&nbsp;Norwegian Meteorological Institute and the NRK&nbsp;&nbsp;</a>
        </small> 
</div>'.PHP_EOL;
# start of different tabs contents, all in 1 div
echo '<div id="div_height" class= "div_height" style="width: 100%; text-align: left; overflow: auto;">'.PHP_EOL;
# -------------- METNO MOS TABLE
if ( 1 == 1) {
$tblmos_col     = 10;
$row_day_part       = '<tr class="fc_night font_head">';
$row_day_cond_img   = '<tr class="fc_day">';
$row_day_cond_txt   = '<tr class="fc_day">';
$row_day_temp       = '<tr class="fc_day" style="text-shadow: black 1px 1px;font-weight: bolder;">';
$row_day_uv_txt     = '<tr class="fc_night  font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('UV').'</td>';
$row_day_uv_desc    = '<tr class="fc_day">';
$row_day_rain_txt   = '<tr class="fc_night  font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Precipitation').'<small> '.lang($rainunit).'</small></td>';
$row_day_rain_value = '<tr class="fc_day">';
$row_day_rain_total = 0;
$row_day_wind_txt   = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Wind from').'</td>';
$row_day_wind_img   = '<tr class="fc_day">';
$row_day_wind_value = '<tr class="fc_day">';
$row_day_wind_speed = '<tr class="fc_day">';
$row_day_wspeed_txt = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Windspeed').'<small> '.lang($windunit).'</small></td>';
$row_day_bft_value  = '<tr class="fc_day">';
$row_day_bft_level  = '<tr class="fc_day">';
$row_bft_text_lvl   = false;        // very high beaufort value occured
$row_day_baro_txt   = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Pressure').'<small> '.lang($pressureunit).'</small></td>';
$row_day_baro_value = '<tr class="fc_day">';
$row_baro_tekst_lvl = false;        // high drop / rise in pressure 
$nr_of_dayparts = 1;
$fc_first_time  = true;
$fc_col_width   = round(100/$tblmos_col,3,PHP_ROUND_HALF_DOWN);
$baro_yesterday = 0;
$msg_daypart    = array ( 'Night', 'Morning', 'Afternoon', 'Evening');
foreach ($msg_daypart as $key => $text)
     {  $msg_daypart[$key]      = lang($text);}
foreach ($frct_mtn_dp as $key => $arr) {  #print_r($arr); exit;
/*
    [air_pressure_at_sea_level] => 1014.2
    [air_temperature] => 19.9
    [cloud_area_fraction] => 95.3
    [cloud_area_fraction_high] => 93
    [cloud_area_fraction_low] => 22.7
    [cloud_area_fraction_medium] => 26.6
    [dew_point_temperature] => 16.8
    [fog_area_fraction] => 0
    [relative_humidity] => 83.4
    [ultraviolet_index_clear_sky] => 0.2
    [wind_from_direction] => 206.6
    [wind_speed] => 2.3
    [unix] => 1632672000
    [datetime] => 2021-09-26T16:00:00Z
    [org_nr] => 1
    [symbol_code] => cloudy
    [air_temperature_max] => 18.8
    [air_temperature_min] => 16.1
    [precipitation_amount] => 0.1 */
# First part should be M = morning or E = evening
        $tm_strt        = $arr['unix'];
        $hr_start       = date('H',$tm_strt);
        $hr_end         = date('H',$tm_strt + 6*3600);
        $result         = (int) ($hr_start / 6);
        if ($fc_first_time == true && $result <> 1 && $result <> 3) {continue;}
# SKIP NIGHT PART IF EARLY MORNING
        if ($fc_first_time == true && date('H') < 6 &&  $result == 0) {continue;}
        $fc_first_time  = false;  # $arr['precipitation_amount'] = 0;
        $fc_day_name    = lang(date('l',$arr['unix']));
        
        if ($result == 1 || $result == 3) 
             {  if ($baro_yesterday == 0) {  $baro_yesterday = $arr['air_pressure_at_sea_level']; }    
# NAME of the day
                $row_day_part           .= 
                        '<td style="width: '.$fc_col_width.'%;">'. ucfirst($fc_day_name);
# CONDITION icon(s) first icon always displayed
                if ($result == 1)  { $fc_class=  'fc_day';} else  { $fc_class=  'fc_night';} 
                $first_symbol   = $arr['symbol_code'];
                $content        = str_replace ('polartwilight','day',$first_symbol);
                $icon           = $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;
                $first_text     = $mtn_icons[$content]['txt'];
                $url            = '<img src="'.$icon.'" class="fc_icon" alt="'.$first_text.'">';
                $row_day_cond_img       .= 
                        '<td class="'.$fc_class.'">'.$url;
# CONDITION description(s) first part always displayed
                     // save to compare second part
                $row_day_cond_txt       .= 
                        '<td>'.lang($first_text); 
# TEMPERATURE all done in second part 
                if ($result == 1 ) 
                     {  $first_temp     = $arr['air_temperature_max'];   }
                else {  $first_temp     = $arr['air_temperature_min'];   }    
# UV
                $row_day_uv_desc        .= '<td>';
                if (array_key_exists ('ultraviolet_index_clear_sky',$arr) )
                     {  $UV = $arr['ultraviolet_index_clear_sky'];} 
                else {  $UV = '';}             
# RAIN sum of 2*6 hours generated in Afternoon Night part N                
                $precip_2p              = $arr['precipitation_amount'];   // save value to add in second part
# WIND direction / speed
# WIND direction arrow
                $compass= windlabel ($arr['wind_from_direction']); #$windlabel[ fmod((((int) $arr['wind_from_direction'] + 11) / 22.5),16) ]; 
                $url    = '<img src="img/windicons/'.$compass.'.svg" width="20" height="20"  alt="'.$compass.'">';
                $row_day_wind_img       .= '<td>'.$url;
                $row_day_wind_value     .= '<td>'.lang($compass);
# WIND speed
                $windspeed              = convert_speed ($arr['wind_speed'], 'm/s', $windunit); 
                $row_day_wind_speed     .= '<td>'.$windspeed;
# WIND force in beaufort
 /*               $beafort        = $arr['bft'];
                $bft_txt        = $arr['bft_txt']; # => Light air
                $bft_lvl        = $arr['bft_lvl']; #  => true / false
                $bft_bgc        = $arr['bft_bgc']; #  => transparent
                $bft_txc        = $arr['bft_txc']; #  => black  */
                $spd_knts       = round ($arr['wind_speed'] * $toKnots , 1);
                foreach ($lvl_bft as $key => $n)
                     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                        break;}
                $bft_nr         = $key;
                $row_day_bft_value     .= 
                        '<td>'.$bft_nr; 
                $bft_txt_l      = lang($bft_txt[$key]); 
# PRESSURE  all done in second part           
        } // eo morning evening
        else { 
# DATE / night of the day
                if ($result == 2) 
                     {  $text   = trans_long_date (date ($month_day_frmt,$arr['unix']));}
                else {  $text   = $msg_daypart[0]; } 
                $row_day_part   .= 
                        '<small><br>'.$text.'</small></td>';
# CONDITION icon only displayed if different from first one
                $url    = $text = '';
                if ($first_symbol  <> $arr['symbol_code']) 
                    {   $content= str_replace ('polartwilight','day',$arr['symbol_code']);
                        $icon   = $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;
                        $text   = $mtn_icons[$content]['txt'];
                        $url    = ' &nbsp; <img src="'.$icon.'" class="fc_icon" alt="'.$text.'">';}
                $row_day_cond_img      .= $url.'</td>';
# CONDITION description only displayed if different form first one
                if ($text <> '') { $row_day_cond_txt .= ' &#8594; '.lang($text);}
                $row_day_cond_txt      .= '</td>';
# TEMPERATURE
                if ($result == 2) 
                     {  $second_temp    = $arr['air_temperature_max'];   }
                else {  $second_temp    = $arr['air_temperature_min'];   }    
                if ($second_temp > $first_temp)
                     {  $high   = $second_temp; $low    = $first_temp;}
                else {  $high   = $first_temp;  $low    = $second_temp;}
                if ($result == 0) 
                     {  $temp    = $low; } 
                else {  $temp    = $high;}
                $temp   = round(convert_temp  ($temp, 'c', $tempunit));     
                $row_day_temp   .=
                        '<td style="font-size: 20px; color: '.temp_color($temp).'">'.$temp.' &deg;'.$tempunit.'</td>';  #### 2021-10-09
# UV
                if (array_key_exists ('ultraviolet_index_clear_sky',$arr) )
                     {  $UV2 = $arr['ultraviolet_index_clear_sky'];                        
                        if ((float) $UV2 > (float) $UV) {$UV = $UV2;}
                        }
                if (trim($UV)  <> '' && (float) $UV <> 0)
                     {  $value  = (int) $UV;
                        if ($value > 11) {$value = 11;}
                        $return = '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.round($UV).'</div>';}
                else {  $return = $nouv;} 
                $row_day_uv_desc        .= $return.'</td>'; 
# RAIN sum of 2*6 hours
                $precip_2p              = (float) $precip_2p + (float) $arr['precipitation_amount'];
                $row_day_rain_total     = $row_day_rain_total  + $precip_2p;
                $string         = $norain;
                if ($precip_2p <> 0)
                     {  $rain           = convert_precip ($precip_2p, 'mm', $rainunit);
                        $string         = $rain;}
                $row_day_rain_value    .= '<td>'.$string.'</td>';
# WIND direction / speed : arrow & abbreviation
                $compas2= windlabel($arr['wind_from_direction']); # $windlabel[ fmod((((int) $arr['wind_from_direction'] + 11) / 22.5),16) ]; 
                if ($compass  <> $compas2)  // if wind direction different display second value
                    {   $url    = ' &nbsp; <img src="img/windicons/'.$compas2.'.svg" class="fc_wnd"  alt="'.$compas2.'">';
                        $row_day_wind_img      .= $url;
                        $row_day_wind_value    .= ' &#8594; '.lang($compas2);}
                $row_day_wind_img       .= '</td>';
                $row_day_wind_value     .= '</td>';
# WIND speed    
                $windspeed2     = convert_speed ($arr['wind_speed'], 'm/s', $windunit); 
                if ($windspeed <> $windspeed2) 
                    {   $row_day_wind_speed     .= ' &#8594; '.$windspeed2;}
                $row_day_wind_speed      .= '</td>';
# WIND beaufort value
                $spd_knts       = round ($arr['wind_speed'] * $toKnots , 1);
                foreach ($lvl_bft as $key => $n)
                     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                        break;}
                $bft_n2 = $key;
                $bft2   = '';
                if ($windspeed <> $windspeed2 && $bft_nr <> $bft_n2)  
                     {  $bft2   = '<small> &#8594; </small>'.$bft_n2; }
                $row_day_bft_value     .= $bft2.' '.lang('Bft').'</td>';
# WIND beaufort description                
                if ($windspeed < $windspeed2)
                     {  $bft_txt_l      = lang($bft_txt[$key]);}
                $row_day_bft_level      .= '<td>'.$bft_txt_l .'</td>';  
# PRESSURE  
                $now            = $arr['air_pressure_at_sea_level'];
                $difference     = $arr['air_pressure_at_sea_level'] - $baro_yesterday;
                $baro_yesterday = $now;
                if      ($difference > 0 )      {$arrow = '<b>&#x2191;</b>';}
                elseif  ($difference < 0 )      {$arrow = '<b>&#x2193;</b>';}
                else                            {$arrow = '';}   
                $baro   = convert_baro ( (float) $now, 'hpa',  $pressureunit); 
                $row_day_baro_value       .= 
                        '<td>'.$arrow.$baro.'</td>';
                $nr_of_dayparts++;       
        } // eo afternoon night 
        if ($nr_of_dayparts > $tblmos_col) {break;}
} // eo each daypart

$row_day_part       .= '</tr>'.PHP_EOL;
$row_day_cond_img   .= '</tr>'.PHP_EOL;
$row_day_cond_txt   .= '</tr>'.PHP_EOL;
$row_day_temp       .= '</tr>'.PHP_EOL;
$row_day_uv_txt     .= '</tr>'.PHP_EOL;
$row_day_uv_desc    .= '</tr>'.PHP_EOL; 
$row_day_rain_txt   .= '</tr>'.PHP_EOL;
$row_day_rain_value .= '</tr>'.PHP_EOL;
$row_day_wind_txt   .= '</tr>'.PHP_EOL;
$row_day_wind_img   .= '</tr>'.PHP_EOL;
$row_day_wind_value .= '</tr>'.PHP_EOL;
$row_day_wspeed_txt .= '</tr>'.PHP_EOL;
$row_day_wind_speed .= '</tr>'.PHP_EOL;
$row_day_bft_value  .= '</tr>'.PHP_EOL;
$row_day_bft_level  .= '</tr>'.PHP_EOL;
$row_day_baro_txt   .= '</tr>'.PHP_EOL;
$row_day_baro_value .= '</tr>'.PHP_EOL;
#
$fc6_mos_html = '<table class= "tabcontent div_height  forecast" id="Forecast" 
        style=" width: 100%;  margin: 0px auto; text-align: center; border-spacing: 2px; background-color: transparent; color: black;">'
#        .$row_station
        .$row_day_part
        .$row_day_cond_img
        .$row_day_cond_txt
        .$row_day_temp
        .$row_day_uv_txt
        .$row_day_uv_desc;
if ($row_day_rain_total <> 0 ) { 
     $fc6_mos_html .=
        $row_day_rain_txt
        .$row_day_rain_value;} 
else {  $fc6_mos_html .= 
        '<tr class="fc_night  font_head"><td colspan = "'.$tblmos_col.'">'
                .lang('No measurable rain expected during this period.').'</td></tr>'.PHP_EOL;}
$fc6_mos_html .=
        $row_day_wind_txt
        .$row_day_wind_img
        .$row_day_wind_value
        .$row_day_wspeed_txt
        .$row_day_wind_speed
        .$row_day_bft_level
        .$row_day_bft_value
        .$row_day_baro_txt
        .$row_day_baro_value
        .'</table>'.PHP_EOL;
echo $fc6_mos_html;
} // eo mos table
# -------- METNO MOS TABLE ENDED
# ----------------- 6 HOUR PARTS  
if ( 1 == 1) {
$cl_headers     = array (
        'unix'                          => '', 
        'symbol_code'                   => '<span style="float: left; padding-left: 10px;">'.lang('Conditions').'</span>', 
        'air_temperature'               => lang('Temperature'), 
        'precipitation_amount'          => lang('Precipitation'), 
        'wind_speed'                    => lang('Windspeed'),
        'wind_from_direction'           => lang('Direction'), 
        'ultraviolet_index_clear_sky'   => lang('UV index'), 
        'cloud_area_fraction'           => lang('Clouds'), 
        'air_pressure_at_sea_level'     => lang('Pressure')  );                      
/*  [2020-07-31T10:00:00Z] => Array(
    [air_pressure_at_sea_level] => 1014.7
    [air_temperature] => 29.4
    [cloud_area_fraction] => 0
    [cloud_area_fraction_high] => 0
    [cloud_area_fraction_low] => 0
    [cloud_area_fraction_medium] => 0
    [dew_point_temperature] => 12.6
    [fog_area_fraction] => 0
    [relative_humidity] => 35.8
    [ultraviolet_index_clear_sky] => 6.4
    [wind_from_direction] => 112.9
    [wind_speed] => 3.4
    [unix] => 1596189600
    [symbol_code] => clearsky_day
    [air_temperature_max] => 34.4
    [air_temperature_min] => 31.3
    [precipitation_amount] => 0 )
 */
# -------   check if all data is present
$cols           = count($cl_headers); # echo '<pre>'.print_r($cl_headers);
#
$head_str = '<tr style="border-bottom: 1px grey solid; color: white; background-color: DIMGRAY; ">';
foreach ($cl_headers as $key => $header)
     {  $head_str .='<td>'.$header.'</td>';}// print   the table headers
$head_str .= PHP_EOL.'</tr>'.PHP_EOL;
#
echo '<table class= "tabcontent div_height font_head" id="6-hour" 
        style="  overflow: auto; width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">'.PHP_EOL;
#
$ymd_old        = 0;
$parts  = array ('Night','Morning','Afternoon','Evening');
#
foreach ($frct_mtn_dp as $key => $arr) { #  echo  '<pre>'.__LINE__.' '.print_r($arr,true) ; exit;
#
# first check if new day arraived:
        $ymd    = date('Ymd',$arr['unix']);
        if ($ymd <> $ymd_old)
             {  $sun_arr= date_sun_info((int) $arr['unix'], $lat, $lon);
                $sunrise= $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $sunset = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $day_nm = trans_long_date(date($date_time_frmt,$arr['unix']));
                echo '<tr style="border-bottom: 1px grey solid;  background-color: DIMGRAY; color: white;  height: 22px;" ><td colspan="'
                        .$cols.'"><span style="padding: 4px; font-size: 14px;">&nbsp;<b>'
                        .$day_nm;
                if (!$sunrise == false &&  !$sunset == false)  
                     {  $rise   = date($timeFormatShort,$sunrise);
                        $set    = date($timeFormatShort,$sunset);
                        $length = $sunset -  $sunrise; # echo $length; exit;
                        $length = gmdate('H:i',$length); 
                        $length = str_replace (':',' '.lang ('hrs '),$length);
                        $length .= ' '.lang ('mins ');
                        echo '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="img/sunrise.svg" style="width: 32px; height: 16px;" alt="sunrise">&nbsp;&nbsp;'
                                .$rise.'&nbsp;&nbsp;<img src="img/sunset.svg"  style="vertical-align: bottom; width: 32px; height: 16px;" alt="sunset">&nbsp;&nbsp;'
                                .$set.'&nbsp;&nbsp;&nbsp;'
                                .lang ('Daylight') .': '
                                .$length; 
                        }
                echo '&nbsp;</span></td></tr>'
                        .PHP_EOL.$head_str;	        
                $head_str = '';
                $ymd_old  = $ymd;}
        echo '<tr style="border-bottom: 1px grey solid; ">';
        $tm_strt        = $arr['unix'];
        $hr_start       = date('H',$tm_strt);
        $hr_end         = date('H',$tm_strt + 6*3600);
        $result         = (int) ($hr_start / 6);
        if ( $result == 0 || $result == 3 )
             {  $colorx = $clrcld;}  else {  $colorx = $clrwrm;}
        $text = $parts[$result];
        
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   if (isset ($arr[$key]) ) 
                     {  $content  = $arr[$key]; } 
                else {  echo '<td>'.$norain.'</td>';
                        continue; }
                switch ($key){
                    case 'unix': 
                        echo PHP_EOL.'<td><span style="color: '.$colorx.';">'.lang($text).'</span><span><br>'.$hr_start.' -  '.$hr_end.'</span>'; 
                        break;
                    case 'symbol_code':
                        $content= str_replace ('polartwilight','day',$content);
                        $icon   = $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;
                        $text   = $mtn_icons[$content]['txt'];
                        echo PHP_EOL.'<td><span style="float: left;"><img src="'.$icon.'" width="60" height="32" alt="'.$text.'" style="vertical-align: middle;">';
                        echo lang($text).'</span>'; 
                        break;
                    case 'air_temperature': 
                        $max    = round(convert_temp  ($arr['air_temperature_max'], 'c', $tempunit));
                        $max_clr= temp_color($max);
                        $min    = round(convert_temp  ($arr['air_temperature_min'], 'c', $tempunit));
                        $min_clr= temp_color($min);
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$min_clr.';">'.$min;
                        echo '</span> - <span style="font-size: 20px; color: '.$max_clr.';">'.$max;
                        echo '&deg;</span>';
                        break;
                    case 'precipitation_amount': 
                        echo PHP_EOL.'<td>';
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  echo $norain; }
                        else {  $amount = convert_precip ($amount, 'mm', $rainunit);
                                echo  $amount.'<small> '.lang($rainunit).'</small>';}
                        break;              
                    case 'cloud_area_fraction':
                        echo PHP_EOL.'<td>';
                        if ( (float) $content < 10  ) 
                             {  echo   $norain; }
                        else {  echo round( (float)$content,0).'<small> %</small>';}
                        break;       
                    case 'wind_speed':  
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  echo '<td>'.$norain;  }
                        else {  $amount         = convert_speed ($amount, 'm/s', $windunit);
                                $spd_knts       = round ((float) $content * $toKnots , 1);
                                foreach ($lvl_bft as $key => $n)
                                     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                                        break;}
                                $bft_nr         = $key;
                                $bft_txt_l      = lang($bft_txt[$key]); 
                                echo'<!-- debug '.__LINE__.' $content='.$content.' $amount='.$amount.' bft='.$bft_nr.' txt='.$bft_txt[$key].' -->'.PHP_EOL;
                                echo PHP_EOL.'<td>'.$amount.'<small> '.lang($windunit).'</small>&nbsp;&nbsp;&nbsp;'.$bft_txt_l;}
                        break;
                    case 'wind_from_direction': 
                        echo PHP_EOL.'<td>';
                        $compass        = windlabel($content); #$windlabel[ fmod((((int)$content + 11) / 22.5),16) ]; 
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$content.'"  style="vertical-align: bottom;"> ';
                        echo lang($compass);
                        break;
                    case 'ultraviolet_index_clear_sky': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($content == '' ||$content == 'n/a' || $content == NULL || (int) $content == 0) 
                             {  echo $nouv;  break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;
                    case 'air_pressure_at_sea_level':
                        $slp    = convert_baro ( (float) $content, 'hpa',  $pressureunit);   #### 2021-01-10
                        echo PHP_EOL.'<td>'.$slp.'<small> '.lang($pressureunit).'</small>';
                        break;         
                   # default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
        } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '</table>'.PHP_EOL;
} // eo 6 hour parts
# ---------- END OF 6 HOUR PARTS 
# ----------------- 1 HOUR PARTS 
if ( 1 == 1) {
$cl_headers     = array (
        'unix'                         => lang('Period'), 
        'symbol_code'                  => '<span style="float: left; padding-left: 10px;">'.lang('Conditions').'</span>', 
        'air_temperature'              => lang('Temperature'), 
        'precipitation_amount'         => lang('Precipitation'),
        'wind_speed'                   => lang('Windspeed'),
        'wind_from_direction'          => lang('Direction'), 
        'ultraviolet_index_clear_sky'  => lang('UV index'),
        'cloud_area_fraction'          => lang('Clouds'), 
        'air_pressure_at_sea_level'    => lang('Pressure')  );
/*[2020-08-02T16:00:00Z] => Array (
    [air_pressure_at_sea_level] => 1015.7
    [air_temperature] => 18.8
    [cloud_area_fraction] => 0
    [cloud_area_fraction_high] => 0
    [cloud_area_fraction_low] => 0
    [cloud_area_fraction_medium] => 0
    [dew_point_temperature] => 9.9
    [fog_area_fraction] => 0
    [relative_humidity] => 55.6
    [ultraviolet_index_clear_sky] => 0.6
    [wind_from_direction] => 110.8
    [wind_speed] => 3.5
    [unix] => 1596175200
    [symbol_code] => clearsky_day
    [precipitation_amount] => 0 )
    */
$cols           = count($cl_headers); # echo '<pre>'.print_r($cl_headers);
$norain         = '-';
$nouv           = '-';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";  
$head_str = '<tr style="border-bottom: 1px grey solid; background-color: DIMGRAY; color: white; ">';
foreach ($cl_headers as $key => $header)
     {  $head_str .='<td>'.$header.'</td>';} // print   the table headers
$head_str .= PHP_EOL.'</tr>'.PHP_EOL;
#
echo '<table class= "tabcontent div_height font_head" id="Hourly" 
        style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">'.PHP_EOL;
#
$ymd_old        = 0;
#
foreach ($frct_mtn_1hr as $key => $arr) {  # echo  '<pre>'.__LINE__.' '.print_r($arr,true) ; exit;
#
# first check if new day arraived:
        $ymd    = date('Ymd',$arr['unix']);
        if ($ymd <> $ymd_old)
             {  $sun_arr= date_sun_info((int) $arr['unix'], $lat, $lon);
                $sunrise= $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $sunset = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $day_nm = trans_long_date(date($date_time_frmt,$arr['unix']));
                echo '<tr style="border-bottom: 1px grey solid;  background-color: DIMGRAY; color: white; height: 22px;" ><td colspan="'
                        .$cols.'"><span style="padding: 4px; font-size: 14px;">&nbsp;<b>'
                        .$day_nm;
                if (!$sunrise == false &&  !$sunset == false)  
                     {  $rise   = date($timeFormatShort,$sunrise);
                        $rise_hr= date('H',$sunrise);
                        $set_hr= date('H',$sunset); # if ($set_hr == '00') {$set_hr = 24;}
                        $set    = date($timeFormatShort,$sunset);
                        $length = $sunset -  $sunrise;
                        $length = gmdate('H:i',$length); 
                        $length = str_replace (':',' '.lang ('hrs '),$length);
                        $length .= ' '.lang ('mins ');              
                        echo '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="img/sunrise.svg" style="width: 32px; height: 16px;" alt="sunrise">&nbsp;&nbsp;'
                                .$rise.'&nbsp;&nbsp;<img src="img/sunset.svg"  style="vertical-align: bottom; width: 32px; height: 16px;" alt="sunset">&nbsp;&nbsp;'
                                .$set.'&nbsp;&nbsp;&nbsp;'
                                .lang ('Daylight') .': '
                                .$length;
                        }
                echo '&nbsp;</span></td></tr>'
                        .PHP_EOL.$head_str;	        
                $ymd_old  = $ymd;}
        echo '<tr style="border-bottom: 1px grey solid; ">';
        $from24 = date('H',$arr['unix']);
        $to24   = date('H',$arr['unix']+3600 );
        if ($to24 == 0) {$to24 = 24;}   
        if ($from24 > $rise_hr && $to24 <= $set_hr) {$colorx = $clrwrm; } else {$colorx = $clrcld;}
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   if (array_key_exists ($key,$arr)) 
                     {  $content  = $arr[$key]; } 
                else {  # echo  '<pre>'.__LINE__.' '.print_r($arr,true) ; exit; ##################
                        echo '<td>'.$norain.'</td>';
                        continue; }
                switch ($key){
                    case 'unix': 
                        $fromtxt        = str_replace (':00','',set_my_time($content,true));
                        $totxt          = str_replace (':00','',set_my_time($content+3600,true));
                        echo PHP_EOL.'<td><span style="font-size: 14px; color: '.$colorx.';">'.$fromtxt.' -  '.$totxt.'</span>'; 
                        break;
                    case 'symbol_code':  
                        $content= str_replace ('polartwilight','day',$content);
                        $icon   = $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;  
                        $text   = $mtn_icons[$content]['txt'];     
                        echo PHP_EOL.'<td><span style="float: left;"><img src="'.$icon.'" width="60" height="32" alt="'.$text.'" style="vertical-align: middle;">';
                        echo lang($text).'</span>'; # echo '<pre>'.__LINE__.'content='.$content.print_r($mtn_icons[$content],true); exit;
                        break; 
                    case 'air_temperature': 
                        $temp   = convert_temp  ($content, 'c', $tempunit);
                        $tclr   = temp_color($temp);  
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$tclr.';">'.$temp;  ##### 2021-01-10
                        echo '&deg;</span>';
                        break;
                    case 'precipitation_amount': 
                        echo PHP_EOL.'<td>';
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  echo $norain;  }
                        else {  $amount = convert_precip ($amount, 'mm', $rainunit);
                                echo  $amount.'<small> '.lang($rainunit).'</small>';}
                        break; 
                    case 'cloud_area_fraction':
                        echo PHP_EOL.'<td>';
                        if ( (float) $content < 10  ) 
                             {  echo   $norain; }
                        else {  echo round( (float)$content,0).'<small> %</small>';}
                        break;       
                    case 'wind_speed':
                        $bft    = 'xx';  
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  echo '<td>'.$norain;  }
                        else {  $amount         = convert_speed ($amount, 'm/s', $windunit);
                                $spd_knts       = round ( (float) $content * $toKnots , 1);
                                foreach ($lvl_bft as $key2 => $n)
                                     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                                        break;}
                                $bft_nr         = $key2;
                                $bft_txt_l      = lang($bft_txt[$key2]); 
                                echo PHP_EOL.'<td>'.$amount.'<small> '.lang($windunit).'</small>&nbsp;&nbsp;&nbsp;'.$bft_txt_l;}
                        break;
                    case 'wind_from_direction': 
                        echo PHP_EOL.'<td>';
                        $compass        = windlabel($content); #$windlabel[ fmod((((int)$content + 11) / 22.5),16) ]; 
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$content.'"  style="vertical-align: bottom;"> ';
                        echo lang($compass);
                        break;
                    case 'ultraviolet_index_clear_sky': 
                        echo PHP_EOL.'<td>';
                        if ($content == '' ||$content == 'n/a' || $content == NULL || (int) $content == 0)   
                             {  echo $nouv;  break;}
                        $value  = (int) $content; 
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;
                    case 'air_pressure_at_sea_level':
                        $slp    = convert_baro ( (float) $content, 'hpa',  $pressureunit);  #### 2021-01-10
                        echo PHP_EOL.'<td>'.$slp.'<small> '.lang($pressureunit).'</small>';
                        break;         
                   # default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
        } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '</table>'.PHP_EOL;
} // eo 1 hour parts
# ---------- END OF 1 HOUR PARTS 
# ------------------------ GRAPH 
if ( 1 == 1) {
$stck_lst      .= basename(__FILE__) .' ('.__LINE__.'): METNO CHART / GRAPH STARTED'.PHP_EOL;
$arrTimeGraph 	= array ();
$arrIconGraph	= array ();
$arrTempGraph	= array ();
$arrRainGraph	= array ();
$arrWindGraph	= array ();
$arrWdirGraph	= array ();
$arrBaroGraph	= array ();
$arrHumGraph	= array ();

$graphTempMin   = 100;
$graphTempMax   = -100;
$graphRainMax   = 0;
$graphWindMax   = 0;
$graphBaroMax   = 0;
$graphBaroMin   = 99999;
$graphHumMax    = 0;

$graphsData	= '';		// we store all javascript data here
$graphLines     = 0;            // number of processed graphlines
$utcDiff 	= date('Z');
$first_time  = true;
#
/*
[air_pressure_at_sea_level] => 1008.4
[air_temperature] => 24.6
[cloud_area_fraction] => 86.7
[cloud_area_fraction_high] => 41.4
[cloud_area_fraction_low] => 2.3
[cloud_area_fraction_medium] => 80.5
[dew_point_temperature] => 18.9
[fog_area_fraction] => 0
[relative_humidity] => 71.1
[ultraviolet_index_clear_sky] => 5.3
[wind_from_direction] => 224.9
[wind_speed] => 3.9
[unix] => 1597917600
[datetime] => 2020-08-20T10:00:00Z
        [icon] => ./icons_cnd/mc_flurries.svg
        [text] => Light rain
        [compass] => SW
        [wind_icn] => ./icons_wnd/SW.svg
        [bft] => 1
        [bft_txt] => Light air
        [bft_lvl] => 
        [bft_bgc] => transparent
        [bft_txc] => black
        [part] => 2
        [day] => Thursday
        [previous] => 1007.2
[symbol_code] => lightrain
[air_temperature_max] => 27.9
[air_temperature_min] => 24.5
[precipitation_amount] => 0.7  */

foreach ($frct_mtn_dp as $key => $arr) {
# time
        $time           = $arr['unix'] + 6*3600;;
	$hr_start       = date('H',$time);
	$part           = (int) ($hr_start / 6);
	if ( $part == '1' || $first_time  == true )    // the next days forecast is there
	     {  $first_time     = false;
	        $sun_arr        = date_sun_info((int) $time, $lat, $lon);
                $fct_sunrise    = $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $fct_sunset     = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
	        $graphsDays[]	= 1000*($fct_sunrise + $utcDiff);
	        $graphsNights[]	= 1000*($fct_sunset  + $utcDiff);}
        $arrTimeGraph[$graphLines]	= $time + $utcDiff; // time  (X-axis) of graphpoint
# icon
        $content        = str_replace ('polartwilight','day',$arr['symbol_code']); 
        $arrIconGraph[$graphLines]	= $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;
# temp
	$part   = (int) date('H',$arr['unix']) / 6;
	if ($part == 1 || $part == 2 ) 
             {  $value                  = $arr['air_temperature_max'];   }
        else {  $value                  = $arr['air_temperature_min'];   } 
	$value 	                        = round( convert_temp ($value, 'c', $tempunit) , $dec_tmp);
	$arrTempGraph[$graphLines]	= $value;	
	if ($value > $graphTempMax) {$graphTempMax = $value;}
	if ($value < $graphTempMin) {$graphTempMin = $value;}
# rain	
        $value                          = $arr['precipitation_amount']; 
	if ($value <> 0)
	     {  $value                  = round (convert_precip ($value, 'mm', $rainunit),$dec_rain);}
	$arrRainGraph[$graphLines]      = $value;
	if ($value > $graphRainMax)   { $graphRainMax   = $value;}
# wind
	$value 			        = $arr['wind_speed'];
	if ($value <> 0)
	     {  $value                  = round (convert_speed ($value, 'm/s', $windunit), $dec_wnd);}
	$arrWindGraph[$graphLines]	= $value;	
	if ($value > $graphWindMax) {$graphWindMax = $value;}
#
	$value                          = windlabel($arr['wind_from_direction']); # $windlabel[ fmod((((int)$arr['wind_from_direction'] + 11) / 22.5),16) ];
	if (strlen ($value) > 3) { $value = substr ($value,0,1);}
	$arrWdirGraph[$graphLines]	= $value;
# baro	
	$value 	                        = $arr['air_pressure_at_sea_level'];
	$value                          = round (convert_baro ( (float) $value, 'hpa',  $pressureunit),$dec_baro); 
	$arrBaroGraph[$graphLines]	= $value;
	if ($value > $graphBaroMax) {$graphBaroMax = $value;} 
	if ($value < $graphBaroMin) {$graphBaroMin = $value;} 
# hum
	$value 	                        = round ($arr['relative_humidity']);
	$arrHumGraph[$graphLines]	= $value;
	if ($value > $graphHumMax)  {$graphHumMax = $value;} 
# ADD the graphsdata table row for use in javascript.
	$graphsData	.= 	'tsv['.$graphLines.'] ="'.
# all data fields have the same time field
		$arrTimeGraph[$graphLines].'|'.
		$arrTempGraph[$graphLines].'|'.
		$arrBaroGraph[$graphLines].'|'.
		$arrWindGraph[$graphLines].'|'.
		$arrWdirGraph[$graphLines].'|'.
		$arrRainGraph[$graphLines].'|'.
		$arrIconGraph[$graphLines].'|'.
		$arrHumGraph[$graphLines].'|'.date('c',$time).'";'.PHP_EOL;			
	$graphLines++;  
} // eo for each daypart      echo '<pre>'.$graphsData; exit;
#
# now we are going to generate the javascript graphs
# calculate Y axis steps for graphs
$graphNrLines	= 6;

# TEMP  Y-axis values
$graphTempMin	= floor ($graphTempMin);        // round down
$graphTempMax	= ceil 	($graphTempMax);        // round up
$tempMin        = $graphTempMin;                // save value 
$graphTempStep	= 2* ceil(($graphTempMax - $graphTempMin) / $graphNrLines);
#
# $stringY is composed for debugging purposes
        $stringY = 'temp max: '.$graphTempMax. ' temp min: '.$graphTempMin. ' temp step: '.$graphTempStep;
#
$graphTempMax	= $graphTempStep * ceil($graphTempMax/$graphTempStep);
$tempMax	= $graphTempMax;
$tempMin	= $tempMin - $graphTempStep;
$graphTempMax	= $graphTempMax	+  $graphTempStep;
$graphTempMin   = $graphTempMax - (1+ $graphNrLines) * $graphTempStep;
#
        $stringY .= '  temp max: '.$graphTempMax.' temp min: '.$graphTempMin;
#
# ICON  Y-axis values
$graphIconYvalue = $graphTempMax - ($graphTempStep/4);
#
        $stringY .= ' icon: '.$graphIconYvalue.PHP_EOL;
#
# RAIN  Y-axis values
        $stringY .= 'rain max start: '.$graphRainMax;
$rainMax	=  $graphRainMax;
if (trim(strtolower($rainunit)) == 'mm') 
     {  if ($graphRainMax < 3.5) { $graphRainMax = 3.5;}
	$graphRainStep	= round (($graphRainMax / $graphNrLines),0);} 
else {  #if ($graphRainMax < 1.3) { $graphRainMax = 14;} 
        $graphRainStep	= (ceil (10* $graphRainMax / $graphNrLines))/ 10;	
	$graphRainMax	= $graphRainStep * $graphNrLines;}
$graphRainMax	= 2 * $graphRainStep * $graphNrLines;   // 2 * because we want the graph
$graphRainStep	= 2 * $graphRainStep ;                  // only in bottom half of graphs-area
$rainMax	= $rainMax + $graphRainStep;
#
        $stringY .= ' rain max: '.$graphRainMax.'   rain step: '.$graphRainStep.PHP_EOL;
#
# BARO  Y-axis values
$baroMax	= $graphBaroMax;
$baroMin	= $graphBaroMin;

if (trim(strtolower($pressureunit)) == 'hpa') 
     {  $graphBaroDiff  = $graphBaroMax - $graphBaroMin;
	if (ceil($graphBaroDiff / 15) <= $graphNrLines) 
	      { $graphBaroStep = 15; } 
	else  { $graphBaroStep = 20;}
	$graphBaroMax   = $graphBaroStep * (ceil($graphBaroMax / $graphBaroStep));
	if ($graphBaroMax < 1035) 
	      { $graphBaroMax = 1035;}
	$graphBaroMin   = $graphBaroMax - $graphNrLines * $graphBaroStep;} 
else {  $graphBaroMax   = 32; 
        $graphBaroMin   = 28.5; 
        $graphBaroStep  = .5;}
$baroMax		= $baroMax + $graphBaroStep;
$baroMin		= $baroMin - $graphBaroStep;
#
        $stringY .='baro max: '.$graphBaroMax.' baro min: '.$graphBaroMin.PHP_EOL;
#
# WIND  Y-axis values
if ($graphWindMax < $graphNrLines) 
     {  $graphWindMax   = $graphNrLines; }
$graphWindStep  = ceil ($graphWindMax / $graphNrLines);
$graphWindMax   = $graphNrLines * $graphWindStep;
$windMax	= $graphWindMax;
$graphWindMax   = 2 * $graphWindMax;    // 2 * because we want the graph
$graphWindStep  = 2 * $graphWindStep;   // only in bottom half of graphs-area
#
        $stringY .='wind max: '.$graphWindMax.' wind step: '.$graphWindStep.PHP_EOL;
#
        $stck_lst .= basename(__FILE__) .' ('.__LINE__.'): '.PHP_EOL.$stringY.PHP_EOL;
#
# list of translations of daynames
$graphDaysString  = "var days        = {";
$graphDaysString .= "'Sun':'".lang('Sunday')."','Mon':'".lang('Monday')."',";
$graphDaysString .= "'Tue':'".lang('Tuesday')."','Wed':'".lang('Wednesday')."',";
$graphDaysString .= "'Thu':'".lang('Thursday')."','Fri':'".lang('Friday')."',";
$graphDaysString .= "'Sat':'".lang('Saturday')."'};";
#
# begin / end of grpahs
$graphsStart 	= 1000 * ($arrTimeGraph[0]  - 3600);
$n		= count($arrTimeGraph)-1;
$graphsStop	= 1000 * ($arrTimeGraph[$n] + 3600);
#
#  shaded background every other day
$ddays		= ''; #echo '<pre> $graphsDays= '.print_r($graphsDays,true); exit;
for($i=0 ; $i < count($graphsDays); $i++) {
        $ddays.= '{ from: '.$graphsDays[$i].', to: '.$graphsNights[$i].', color: "rgba(255, 255, 255, 0.9)" },';
} // eo for each day
$ddays          = substr($ddays, 0, -1); // remove last ,  after last {day}
#
# javascript does not like html characters, also remove  not needed characters for graphs
$from           = array ('&deg;',' ','/');
$uomRain        = str_replace ($from,'',$rainunit);
$uomTemp        = str_replace ($from,'',$tempunit);
$uomBaro        = str_replace ($from,'',$pressureunit);
$uomWind        = str_replace ($from,'',$windunit);
$negValue       = "return '<span style=\"fill: blue;\">' + this.value + '</span>'";
$posValue       = "return '<span style=\"fill: red;\">' + this.value + '</span>'";
if ($clockformat <> '24') 
     {  $threshold      = 32; 
        $hour_min       = '%l:%M %P';
        $hour_ft        = '%l%P';}
else {  $threshold      = 0; 
        $hour_min       = '%H:%M';
        $hour_ft        = '%H';}
#
$degree   = '°';
#if (!isset ($fc5_charset) || $fc5_charset <> 'UTF-8') {$degree  = utf8_decode ('°');}
#
if (!isset ($metno_meteogram_count) ) {$metno_meteogram_count = 10;}
$endgraphs      = $graphsStart + 1000*((24*3600*$metno_meteogram_count));
if ($graphsStop > $endgraphs) {$graphsStop = $endgraphs;}
#
$fc5_mgraph_html     = '<div id="containerTemp" class="tabcontent fc_day div_height" 
        style="display: flex; width: 100%; overflow: auto; background-color: #f1f1f1;">here the graph will be drawn</div>'.PHP_EOL;
$fc5_mgraph_html .='<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script>
'.$graphDaysString.';
var globalX = [
{       type: "datetime",
        min: '.$graphsStart.',
        max: '.$graphsStop.',
        plotBands: ['.$ddays.'],
        title: {text: null},
        dateTimeLabelFormats: {day: "%H",hour: "%H"},        
        tickInterval: 6 * 3600 * 1000,        
        gridLineWidth: 0.4,      
        lineWidth: 0,
        labels: {y: 10,x: 0, rotation: 0, style:{fontWeight: "normal",fontSize:"9px"}, 
                formatter: function() { 
                        if (this.value <= '.($graphsStop).') 
                                {return Highcharts.dateFormat("'.$hour_ft.'", this.value);}
                        else {return "";}
                } // eo formatter
        } // eo labels
},
{       type: "datetime",linkedTo: 0,
        min: '.$graphsStart.',
        max: '.$graphsStop.',
        title: {text: null},
        dateTimeLabelFormats: {day: "%a"},        
        tickInterval: 24 * 3600 * 1000,        
        gridLineWidth: 2,      
        lineWidth: 0,
        labels: {y: 10,  x: 10, rotation: 0, align: "left", style:{ fontWeight: "normal",fontSize:"9px"}, 
                formatter: function() { 
                        if (this.value <= '.($graphsStop - 12*3600*1000).') 
                        { return days[Highcharts.dateFormat("%a", this.value)]; }
                        else {return "";}
                }
        }
}];
var tsv = [];
'.$graphsData.'var temps = [],
wsps    = [],
baros   = [],
precs   = [],
icos    = [],
hums    = [];
for (j = 0; j < tsv.length; j++) {
        var line =[];
        line     = tsv[j].split("|");
        if(line[0].length > 0 && parseInt(line[0]) != "undefined"){
                date    = 1000 * parseInt(line[0]);
                if (date <= '.$graphsStop.') {
                        d       = new Date (date);
                        temps.push([date, parseFloat(line[1])]);
                        baros.push([date, parseFloat(line[2])]);
                        mkr     = "img/windicons/" +line[4]+".svg";
                        str     = {x:date,  y:parseFloat(line[3]), marker:{symbol:"url("+mkr+")", width: 14, height: 14}};
                        wsps.push(str);
                        if (line[5] != "-") {
                                precs.push([date, parseFloat(line[5])]);
                        }	
                        mkr     = line[6];
                        str     = {x:date,y:'.$graphIconYvalue.', marker:{symbol:"url("+mkr+")", width: 20, height: 20}};
                        icos.push(str);
                        hums.push([date, parseFloat(line[7])]);
                } // line is in range 
        } // Line contains correct data           
}; // eo for each tsv';
$fc5_mgraph_html .='
var yTitles 	= {color: "#000000", fontWeight: "normal", fontSize:"10px"};
var yLabels 	= {color: "#4572A7", fontWeight: "bold",   fontSize:"8px"};
var yLabelsWind = {color: "#1485DC", fontWeight: "bold",   fontSize:"8px"};
var yLabelsBaro = {color: "#9ACD32", fontWeight: "bold",   fontSize:"8px"};
var legendStyle = {color: "#000000", fontWeight: "normal", fontSize:"10px"};
var my_options = new
	Highcharts.setOptions({
		chart: {
		        spacingTop:4, 
		        spacingBottom:4,
			renderTo: "containerTemp",
			defaultSeriesType: "spline",
			backgroundColor: "rgba(255, 255, 255, 0.1)",
			plotBackgroundColor: {linearGradient: [0, 0, 0, 250],stops: [[0, "#ddd"],[1, "rgba(255, 255, 255, 0.2)"]]},
			plotBorderColor: "#88BCCE",
			plotBorderWidth: 0.5,
			marginRight: 60,
			marginTop: 30,
			marginLeft: 60,
			zoomType: "x",
			style: {fontFamily: "Verdana,Helvetica,sans-serif",fontSize:"11px"}
		},
		title: {text: ""},
		xAxis: globalX,
		lang: {thousandsSep: ""},
		credits: {enabled: false},
		plotOptions: {
			series: {marker: { radius: 0,states: {hover: {enabled: true}}}},
			spline: {lineWidth: 1.5, shadow: false, cursor: "pointer",states:{hover:{enabled: false}}},
			column: {pointWidth:15},
			areaspline: {lineWidth: 1.5, shadow: false,states:{hover:{enabled: false}}}
		},
		legend: { x: 0, y: 0, align: "left", verticalAlign: "top", rtl: true, margin: 0, itemStyle:legendStyle},
		exporting: {enabled:true},
		tooltip: {
                        positioner: function () {return { x: 0};},
                        backgroundColor: "silver",
                        borderColor: "#fff",
                        borderRadius: 3,
                        borderWidth: 0,  
                        shared: true,
                        useHTML: true,
                        crosshairs: { width: 0.5,color: "#666"},
                        style: {lineHeight: "1.3em",fontSize: "11px",color: "#000"},
                        formatter: function() {
                                var head= days[Highcharts.dateFormat("%a", this.x)]+" "+ Highcharts.dateFormat("'.$hour_min.'", this.x)  +"<br>----------------";
                                var s   = "";
                                this.points.forEach (function( point , i) {
                                        if (point.series.name != " ") {
                                                var unit = {
                                                   "'.lang('Precipitation')     .'": " '.$uomRain.'",
                                                   "'.lang('Wind')              .'": " '.$uomWind.'",
                                                   "'.lang('Temperature')       .'": "'.$degree.$uomTemp.'",
                                                   " ": "",
                                                   "'.lang('Pressure')          .'": " '.$uomBaro.'",
                                                   "'.lang('Humidity')          .'": " %"
                                                }[point.series.name];
                                                s  = "<br>"+point.series.name+": <b>"+point.y+unit+"</b>" + s;
                                        }                            
                                });  // eo each
                                return head+s;
                        } // eo formatter
                } // eo tooltip
	});  // eo set general options
const my_chart  = new Highcharts.Chart({
        chart: { renderTo: "containerTemp" },		
      	yAxis: [
      	{ lineWidth: 2, tickAmount: 8,
          gridLineWidth: 0.4, min: '.$graphTempMin.',max:'.$graphTempMax.',tickInterval:'.$graphTempStep.', offset: 25,
          title: {text: "'.$degree.$uomTemp.'", rotation: 0, align:"high", offset: 0, y: -8, margin: 0, style:yTitles},
          labels: {x: -4, y: 1, formatter: function() {
                if (this.value < '.$tempMin.' || this.value > '.$tempMax.' )
                        { return ""; } 
                 else   {if (this.value < '.$threshold.') {'.$negValue.';} else {'.$posValue.';}}
          },style:yLabels}       
      	},
      	{ tickAmount: 8,
          gridLineWidth: 0, min: 0,max:'.$graphRainMax.',tickInterval:'.$graphRainStep.', offset: 0,
          title: {text: "'.$uomRain.'", rotation: 0, align:"low", offset: 0,x: -30, y: 15, style:yTitles},
          labels: {align: "left", x: -20, y: 1,  formatter: function() {if (this.value < 0 || this.value > '.$rainMax.' ){ return ""; } else {return this.value;}},style:yLabels}
      	},
      	{ tickAmount: 8,
          gridLineWidth: 0, min: 0, max: '.$graphWindMax.', tickInterval: '.$graphWindStep.', opposite: true,
          title: {text: "'.$uomWind.'", rotation:0, align:"low", offset: 25,x: 0, y: 15, style:yTitles},      
          labels: {align: "right",x: 25, y: 1, formatter: function() {if (this.value < 0 || this.value > '.$windMax.' ){ return ""; } else {return this.value;}},style:yLabelsWind}      
      	},
      	{ lineWidth: 2, tickAmount: 8,
          gridLineWidth: 0, min: '.$graphBaroMin.',max: '.$graphBaroMax.',tickInterval: '.$graphBaroStep.',opposite: true, offset: 30,
          title: {text:"'.$uomBaro.'", rotation: 0, align:"high", offset: 0, y: -8, style:yTitles},        
          labels: {align: "left",x: 4, y: 1, formatter: function() {if (this.value < '.$baroMin.' || this.value >= '.$baroMax.' ){ return ""; } else {return this.value;}},style:yLabelsBaro}
        },
         { lineWidth: 2, tickAmount: 8,
          gridLineWidth: 0, min: 0, max: 300, tickInterval: 20,opposite: true, offset: 30,
          title: {text:"%", rotation: 0, align:"low", offset: 0,x: 20, y: 15, style:yTitles},        
          labels: {align: "left",x: 4, y: 1, formatter: function() {if (this.value > 100 || this.value < 1){ return ""; } else {return this.value;}},style:yLabels}
        }
        ],
      	series: [
      		{name: "'.lang('Precipitation'). '", data: precs, color:"#AAAAAA",type:"column",yAxis:1},
      		{name: "'.lang('Humidity').      '", data: hums,  color:"#356297", yAxis:4, dashStyle:"Dot"},
      		{name: "'.lang('Wind').          '", data: wsps,  color:"#1485DC", yAxis:2},
      		{name: "'.lang('Pressure').      '", data: baros, color:"#9ACD32", yAxis:3},
      		{name: "'.lang('Temperature').   '", data: temps, color:"#EE4643", threshold: '.$threshold.', negativeColor: "#4572EE"},
      		{name: " '.                              '", data: icos , color:"transparent",type:"",events:{legendItemClick:false}},
      		
      	],
      	navigation: {buttonOptions: {verticalAlign: \'top\', y: -0, x: -50} }

        });  // eo chart    
</script>'.PHP_EOL;
echo  $fc5_mgraph_html;
$stck_lst .= basename(__FILE__) .' ('.__LINE__.'): METNO CHART / GRAPH ENDED'.PHP_EOL;
}
# ------ METNO CHART / GRAPH ENDED
echo '</div>
<script>
function openTab(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  if (cityName == "containerTemp") { document.getElementById(cityName).style.display = "flex";}
  else  { document.getElementById(cityName).style.display = "table";}
  evt.currentTarget.className += " active";
//  const g_width   = document.getElementsByTagName("body")[0].clientWidth;
//  const g_height  = document.getElementsByTagName("body")[0].clientHeight; 
//  alert (">"+g_width+"<>"+g_height+"<");
//  my_chart.setSize(g_width,g_height); 
//  my_chart.reflow();
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
# style is printed in the header 
function my_style()
     {  global $popup_css ,$round_crnr;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css
        $return .= '
        .my_uv  { background-color: lightgrey;  margin: 0 auto; border-radius: '.$round_crnr.'%;
                    height: 20px; width: 20px;  color: #fff;
                    line-height: 20px;font-size: 16px;
                    font-family: Helvetica,sans-seriff;
                    border: 1px solid #FFFFFF;}
        .rTxt   { padding: 3px; text-align: right; float: right;  } 
        .fc_icon{ width: 20px; height: 20px; vertical-align: bottom;}
        .fc_day  { background-color: silver;}
        .fc_night{ background-color: grey; color: white; }
        .forecast table {border-collapse: separate; box-sizing: border-box;} 
        .forecast td {padding: 2px; } 
        .forecast img  {width: 24px; height: 24px;}
        .font_head {height: 14px;}
        .tab {
          overflow: hidden;
          text-align: center;
          border: 0px solid #ccc;
          height: 18px;
          padding-top: 3px;
        /* background-color: grey; */
        }
        /* Style the buttons inside the tab */
        .tab label {
         /* float: left; */
          border: none;
          outline: 1px solid black;
          cursor: pointer;
          padding: 0px 4px;
          transition: 0.3s;
        /*  font-size: 16px;  */
          color: orange;
          background-color: grey;
        }
        /* Change background color of labels on hover */
        .tab label:hover {
          background-color: #000;
        }
        /* Create an active/current tablink class */
        .tab label.active {
          background-color: white;
          color: black;
        }
        /* Style the tab content */
        .tabcontent {
          display: none;
        /*  padding: 6px 12px; */
          border: 0px solid #ccc; 
          border-top: none;
        }  
';      
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
 
function trans_long_date ($date)
     {  $from   = array ( 
                'Apr ','Aug ','Dec ','Feb ','Jan ','Jul ','Jun ','Mar ','May ','Nov ','Oct ','Sep ',
                'April','August','December','February','January','July','June','March','May','November','October','September',
                'Mon ','Tue ','Wed ','Thu ','Fri ','Sat ','Sun ',
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        foreach ($from  as $txt) {$to_dates[] = lang($txt).' ';} # echo '-'.$txt.'-'.lang($txt).PHP_EOL;
        return str_replace ($from, $to_dates, $date.' ');  #### 2018-07-18
        }       
