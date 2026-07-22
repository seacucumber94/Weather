<?php $scrpt_vrsn_dt  = 'fct_yrno_popup.php|01|2021-12-08|';  # PHP 8.1 + 24 hrs light/dark + hpa -> inhg | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
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
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.date('c').' tz difference='.date('P').PHP_EOL; 
#-----------------------------------------------
$ltxt_clsppp    = lang('Close');
#
$round_crnr             = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;}  
#  
# -----------------   load general metno fct code
$scrpt          = 'fct_yrno_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include $scrpt;  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.date('c').' tz difference='.date('P').PHP_EOL; 
if (count ($frct_mtn_dp) == 0) {echo '??'; return false;} 
#
if ($clockformat == '24') 
     {  $date_time_frmt = 'l  j  F';}
else {  $date_time_frmt = 'D M j';}
# ------------------------- translation of texts
$ltxt_url       = lang('Forecast');
#
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
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'    </div>'.PHP_EOL;
echo '<div class= "div_height" style="width: 100%;text-align: left; overflow: auto;">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">'.PHP_EOL;

$head_str = '<tr style="border-bottom: 1px grey solid; color: white; background-color: DIMGRAY; ">';
foreach ($cl_headers as $key => $header)
     {  $head_str .='<td>'.$header.'</td>';}// print   the table headers
$head_str .= PHP_EOL.'</tr>'.PHP_EOL;

#
$ymd_old        = 0;
$parts  = array ('Night','Morning','Afternoon','Evening');
#
foreach ($frct_mtn_dp as $key => $arr) { #  echo  '<pre>'.__LINE__.' '.print_r($arr,true) ; exit;
#
# first check if new day arraived:
        $ymd    = date('Ymd',(int) $arr['unix']);
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
                        echo PHP_EOL.'<td><span style="color: '.$colorx.';">'.lang($text).'</span><span><br />'.$hr_start.' -  '.$hr_end.'</span>'; 
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
                                $spd_knts       = round ($amount*$toKnots , 1);
                                foreach ($lvl_bft as $key => $n)
                                     {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                                        break;}
                                $bft_nr         = $key;
                                $bft_txt_l      = lang($bft_txt[$key]); 
                                echo PHP_EOL.'<td>'.$amount.'<small> '.lang($windunit).'</small>&nbsp;&nbsp;&nbsp;'.$bft_txt_l;}
                        break;
                    case 'wind_from_direction': 
                        echo PHP_EOL.'<td>';
                        $compass        = windlabel ($content);  #2021-12-08
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
echo '<tr><td  colspan="'.$cols.'">
<span style="float: right; font-size: 10px;">
Weather forecast data courtesy of the Norwegian Meteorological Institute and the NRK
</span></td></tr></table>
</div>'.PHP_EOL;
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
         .rTxt { padding: 3px; text-align: right; float: right;  }         '.PHP_EOL;  
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
