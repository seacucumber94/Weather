<?php  $scrpt_vrsn_dt  = 'fct_ec_popup_daily.php|01|2020-11-04|';  # release 2012_lts
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
#-----------------------------------------------
#                                  data settings
#
# these are settings for retrieving information
# from https://dd.meteo.gc.ca/citypage_weather/xml/ON/s0000024_f.xml
#------------------------------------------------
#
$EC_area	= $alarm_area;
# -----------------------for testing
#$province       = 'ON'; #
#$EC_area	= 's0000024';  # for testing
# -----------------------for testing
#
$fct_d_needed   = true;
#
# ----------------------   load general EC code
$scrpt          = 'fct_ec_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
$return = include_once $scrpt; 
if ($return == false) { echo 'script ends'; return false;}  
#
# --------------------------      other settings
$color          = $clrwrm = "#FF7C39";
$clrcld         = "#01A4B4";
if ($EC_region <> '') { $EC_region = ' - '.$EC_region;}
# ------------------------- translation of texts
$ltxt_url       = lang('Forecast');
$ltxt_clsppp    = lang('Close');
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
    <div class="PWS_module_title font_head" style="width: 100%;" >
'.$close
.'      <span style="color: '.$color.';">'.$ltxt_url.$EC_region.'</span>
    </div>'.PHP_EOL;

$cl_cntnt       = array ('part','icnc','cond','humi','temp','feel', 'rain','wspd','idir');
$cols           = count($cl_cntnt);
$w_clc_unit     = $wxsim_wnd    = 'km/h';
$wunit          = '<small>'.lang($w_clc_unit).'</small>';
$t_clc_unit     = $wxsim_tmp    = 'C';
$runit          = $wxsim_rn     = 'mm';
$cl_headers     = array ('part' => '', 'icnc' => '', 'cond'=> lang('Conditions'),
                        'temp' => lang('Temp'), 'feel' => 'Feels', 'rain' => lang('Precipitation'),
                        'wspd' => lang('Windspeed'),'idir' => lang('Direction'),'humi' => lang('Humidity') );
$norain         = '-';
#
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
<tr style="border-bottom: 1px grey solid; ">';  // print start enclosing div table
#
for ($i = 0; $i < $cols; $i++)
     {  $code   = $cl_cntnt[$i];
        echo PHP_EOL.'<th>'.$cl_headers[$code].'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
/*
    [period] => Thursday
    [daypart] => Today
    [text] => Periods of snow and local blowing snow. Amount 2 cm. Wind northwest 30 km/h gusting to 50. High minus 25. Wind chill near minus 43. Risk of frostbite.
    [condtext] => Periods of snow
    [condicon] => 16
    [temperature] => -25
    [temptype] => high
    [windChill] => -43
    [windspeed] => 20
    [windgust] => 40
    [windunit] => km/h
    [winddeg] => 32
    [winddir] => NW
    [preciptype] => snow
    [precipacc] => 2
    [precipunit] => cm
    [humidity] => 60*/
foreach ($fcts_arr as $arr) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';    #   echo '<pre>'.print_r($arr,true); exit;
        if ($arr['temptype'] <> 'low') 
             {  $color = $clrwrm; } else {$color = $clrcld; }  
        for ($i = 0; $i < $cols; $i++)  // for every data value = column we want to print
            {   $content        = $cl_cntnt[$i];
                switch ($content)
                  { case 'part': 
                        if ($ec_e_f <> 'f') 
                             {  $txts   = explode (' ',$arr['daypart']);
                                $text   = '';
                                foreach ($txts as $str) {$text .= lang($str).' ';}}
                        else {  $text   = $arr['daypart'];} 
                        echo PHP_EOL.'<td>';
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icnc':
                        $key    = (int)$arr['condicon'];
                        $icon   = EC_icon ($key);
                        echo PHP_EOL.'<td>';
                        echo '<img src="'.$icon.'" width="60" height="20" alt="'.$arr['condtext'].'" style="vertical-align: top;"> ';
                        break;
                     case 'cond': 
                        echo PHP_EOL.'<td>';
                        $cond   =  $arr['condtext'];  // ##### translate  ??
                        echo $cond;
                        break;
                    case 'temp': 
                        echo PHP_EOL.'<td>';
                        echo '<span style="font-size: 18px; color: '.$color.';">'.$arr['temperature'].'<small>&deg;</small></span>'; 
                        break;
                    case 'feel':  ## add heat hudx ??
                        echo PHP_EOL.'<td>';
                        $value = '';
                        if (trim($arr['windChill']) <> '' && $arr['windChill'] <> 0) 
                             {  $value  = '<span style="font-size: 18px; color: '.$clrcld.';">'.$arr['windChill'].'<small>&deg;</small></span>'; }
                        echo $value;
                        break;
                    case 'rain': 
                        echo PHP_EOL.'<td>';
                        $precip = $arr['preciptype'];
                        $amount = (int) $arr['precipacc']; 
                        if (!isset ($arr['precipunit']) )
                             {  $unit   = '';}
                        else {  $unit   = lang($arr['precipunit']);}
                        if ($precip == '' && $amount == 0 ) {echo $norain; break;}               
                        if ($precip == 'snow' || $precip == 'neige') 
                             {  echo $snowflakesvg;} else { echo $rainsvg;}
                        if ($amount <> 0)
                             {  echo ' '.$amount.'<small>'.$unit.'</small>';  }
                        break;
                    case 'wspd': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  echo $arr['windspeed'];
                                if (trim($arr['windgust']) <> '' && $arr['windgust'] <> 0) 
                                     {  echo '-'.$arr['windgust'];}
                                echo  $wunit; }
                        else {  echo $norain;}
                        break;
                    case 'idir': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  $dir    = $dirt = $arr['winddir'];
                                if ($ec_e_f == 'f' && $dir <> '') {$dir = $ec_french_winddir[$dir];}
                                if ($dir <> 'VR')
                                     {  echo '<img src="img/windicons/'.$dir.'.svg" width="20" height="20" alt="'.$dir.'" style="vertical-align: bottom;"> ';
                                        echo $dirt;}
                                else {  echo lang('VR');}}
                        else {  echo ' ';}
                        break;
                    case 'humi':
                        echo PHP_EOL.'<td>';
                        echo $arr['humidity'].'%';
                     break; 
                } // eo switch
                echo '</td>';
                } // eo coloms
        echo '</tr>'.PHP_EOL;
        } // eo for each
echo '<tr><td  colspan="'.$cols.'">
<span style="font-size: 9px;">'.lang('Powered by').':&nbsp;&nbsp;
<a href="https://weather.gc.ca/canada_e.html" target="_blank" style="color: grey">
<b>Environment Canada</b></a></span>
</td></tr></table>
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

        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
