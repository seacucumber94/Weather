<?php  $scrpt_vrsn_dt  = 'fct_wu_popup_daily.php|01|2021-12-08|';  # PHP 8.1 + check file | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$norain         = '-';          // if no data we print
$nouv           = '-';
$color          = "#FF7C39";    // important color
$clrwrm         = "#FF7C39";    // warm / daytime color
$clrcld         = "#01A4B4";    // cold
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
#
if (strtolower (trim($pressureunit)) == 'hpa') {$dec_pres= 1; } else {$dec_pres = 2;}
if (strtolower (trim($rainunit))     == 'mm')  {$dec_rain= 1; } else {$dec_rain = 2;}
#
$round_crnr             = 5;    // for uv-nr in square or round background
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;}   
# ------------------------- translation of texts
$ltxt_clsppp    = lang('Close');
$ltxt_url       = 'WeatherUnderground '.lang('Forecast');
#-----------------------------------------------
#                     arrays to drive the script
#-----------------------------------------------
$cl_cntnt       = array ( #   available coloms to display 
                        'dayOrNight','daypartName','iconCode','narrative',
                        'precipChance', 'precipType',
                        'temperature', 'temperatureHeatIndex', 'temperatureWindChill', 
                        'uvIndex', 'uvDescription',
                        'relativeHumidity',
                        'windSpeed', 'windDirection', 'windDirectionCardinal', 'windPhrase',
                        'wxPhraseLong',    );                        
#
$cl_headers     = array ( # coloms used with the header-row names
                        'daypartName'   => '',
                        'iconCode'      => '',
                        'wxPhraseLong'  => lang('Conditions'),             
                        'temperature'   => lang('Temp'),         
                        'precipChance'  => lang('Precipitation'),      
                        'windSpeed'     => lang('Windspeed'),
                        'windDirection' => lang('Direction'),
                        'uvIndex'       => lang('UV index'), );   
#
$WU_icn_tr      = array ( # translate WU icon-names to our icon-names
'tornado','tornado','tornado','ovc_thun_rain_dark','ovc_thun_rain_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_rain.svg',
'ovc_sleet','mc_rain','mc_rain','ovc_flurries','ovc_flurries','ovc_flurries','ovc_flurries','ovc_sleet','ovc_sleet','dust',
'ovc_fog','ovc_fog','ovc_fog','ovc_windy','ovc_windy','ovc_flurries','ovc','mc_night','mc','mc_night',
'pc_day','clear_night','clear_day','few_night','few_day','ovc_sleet_dark','clear_day','ovc_thun_dark','ovc_thun_dark','mc_rain',
'mc_rain_dark','ovc_flurries','ovc_flurries_dark','ovc_flurries_dark','unknown','ovc_rain_dark','ovc_flurries_dark','ovc_thun_dark');
#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$fll_uv  = array();     // uv-levels with the correct color
$fll_uv[0]  = $b_clrs['green'];
$fll_uv[1]  = $b_clrs['green'];
$fll_uv[2]  = $b_clrs['green'];
$fll_uv[3]  = $b_clrs['yellow'];
$fll_uv[4]  = $b_clrs['yellow'];
$fll_uv[5]  = $b_clrs['yellow'];
$fll_uv[6]  = $b_clrs['orange'];
$fll_uv[7]  = $b_clrs['orange'];
$fll_uv[8]  = $b_clrs['red'];
$fll_uv[9]  = $b_clrs['red'];
$fll_uv[10] = $b_clrs['red'];
$fll_uv[11] = $b_clrs['maroon'];

#-----------------------------------------------
#        compose file name && load file in array
#-----------------------------------------------
$name           = 'wufct_'.$locale_wu.'_'.$wu_fct_unit.'.txt'; 
$file           = $fl_folder.$name;             # ./jsondata/wufct_en_m.txt
$json           = $response     = false;
if (file_exists ($file) )
     {  $json           = file_get_contents($file); 
        $response       = json_decode($json, true);}
#
# ----------   check if forecast data is present
if ( $json == false || $response == false
  || !is_array ($response['daypart']) 
  || count ($response['daypart'][0]) < 4 ) 
#         if not correct. display small messasge  
     {  echo '<p style="color: red;">WU forecast file not ready</p>'; 
        return false; }  
#-----------------------------------------------   
#           the json coantains all kinds of data
#                          load forecast in $arr
#-----------------------------------------------
$arr            = $response['daypart'][0];
$rows           = count ($arr['daypartName']); // 1 row for every daypart
#-----------------------------------------------
#                       check if fct is complete
#   there are often missing data items, we  then
#  have to remove them from the coloms displayed
#
$cols           = count($cl_cntnt); // max coloms to display 
for ($n = 0; $n < $cols; $n++)
     {  $key    = $cl_cntnt[$n];
        if (!is_array($arr[$key]) )  // check if WU suplplied that data  
             {  if (array_key_exists($key, $cl_headers )) 
                     {  unset ($cl_headers[$key]);}  // remove from header list
                $cl_cntnt[$n]   = 'n/a';} // set as unavailable
        } // eo check each data field
#
$cols           = count($cl_headers);   // nr of colomns to print
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'      <span style="color: '.$color.'">'.$ltxt_url.'</span>
    </div>'.PHP_EOL;
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
<tr style="border-bottom: 1px grey solid; ">
';  
#
#                          header row with names
foreach ($cl_headers as $key => $header)
     {  echo PHP_EOL.'<th>'.$header.'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL; 
#
$start = 0;
if ($arr['dayOrNight'][0] == '') {$start++; }
#
for ($n = $start; $n < $rows; $n++) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">'; 
        if ($arr['dayOrNight'][$n] == 'D')   { $color = $clrwrm; } else { $color = $clrcld; }
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   $content        =  $arr[$key][$n];
                switch ($key){
                    case 'daypartName':
                        echo PHP_EOL.'<td style=" text-align: right;">';    // if extra style is needed
                        echo '<span style="color: '.$color.';">'.$content.'</span>'; 
                        break;
                    case 'iconCode':
                        $icon   = $WU_icn_tr [$content];
                        echo PHP_EOL.'<td><img src="./pws_icons/'.$icon.'.svg" width="60" height="24" alt="'.$content.'" style="vertical-align: top;">';
                        break;
                     case 'wxPhraseLong': 
                        echo PHP_EOL.'<td>'.$content;
                        $string = $arr['qualifierPhrase'][$n];
                        if ($string <> '') {echo '<br />'.$string;}
                        break;
                    case 'temperature': 
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$color.';">'.$content.'&deg;</span>';
                        break;
                    case 'temperatureHeatIndex':
                        $temp   = $arr['temperature'][$n];
                        $diffH  = (float)$content - (float) $temp;
                        $chill  = $arr['temperatureWindChill'][$n];
                        $diffC  = (float) $temp - (float)$chill;
                        if     ($diffC > $diffH && $diffC > 3) {$value = $chill;}
                        elseif ($diffH > $diffC && $diffH > 3) {$value = $content;}
                        else {$value = '';}
                        if ($value <> '')
                             {  $value = '<span style="font-size: 20px; color: '.$color.';">'.$value.'<small>&deg;</small></span>';}  
                        echo PHP_EOL.'<td>'.$value;
                        break;
                    case 'precipChance': 
                        echo PHP_EOL.'<td>'; # $content= 5;
                        if (trim($content) == '' || (int) $content == 0 ) {echo $norain; break;}
                        $type   = $arr['precipType'][$n];
                        $string = ''; # $content.'% - ';
                        if ($type == 'snow')
                             {  if ((float)$arr['qpfSnow'][$n] > 0) { $string .= $arr['qpfSnow'][$n].$rainunit.' ';}
                                $string .=  $content.'% '.$snowflakesvg;}
                        else {  if ((float)$arr['qpf'][$n] > 0)     { $string .= $arr['qpf'][$n].$rainunit.' ';}
                                $string .= $content.'% '.$rainsvg;}
                        echo $string;
                        break;              
                    case 'windSpeed':  
                        echo PHP_EOL.'<td>'.$content.' '.$windunit; 
                        break;
                    case 'windDirection': 
                        $bearing        = (int) $content; 
                        $compass = windlabel ($bearing);  # 2021-12-08
                        echo PHP_EOL.'<td>';
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$arr['windDirectionCardinal'][$n].'"  style="vertical-align: bottom;"> ';
                        echo $arr['windDirectionCardinal'][$n];
                        break;
                    case 'uvIndex': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' || $value == '0')   {  echo $nouv;  break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;            
                    default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
                } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
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
        .my_uv  { background-color: lightgrey;  margin: 0 auto; border-radius: '.$round_crnr.'%;
                    height: 20px; width: 20px;  color: #fff;
                    line-height: 20px;font-size: 16px;
                    font-family: Helvetica,sans-seriff;
                    border: 1px solid #FFFFFF;} '.PHP_EOL;   
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
