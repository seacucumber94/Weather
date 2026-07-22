<?php $scrpt_vrsn_dt  = 'fct_darksky_popup_daily.php|01|2023-02-15|';  #  switch to Pirate weather & VirtualCrossing + PHP 8.1 +release 2012_lts
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
#
$ltxt_clsppp    = lang('Close');
#
$round_crnr             = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;}   
#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$fll_uv  = array();
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
# -----------------   load general DarkSky code
$scrpt          = 'fct_darksky_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#
if (!isset ($darkskydayCond) || count ($darkskydayCond) < 4 )   
     {  echo '<b style="color: red;"><small>Darksky file not ready</small></b>'; 
        return; }  // if no correct data output small messasge
#
# ------------------------- translation of texts
$ltxt_url       = lang('Forecast');
#
$rows           = count ($darkskydayCond);
$cl_cntnt       = array ('time','summary','icon','precipIntensity','precipProbability', 
                         'temperatureHigh','apparentTemperatureHigh','dewPoint', 'humidity', 'pressure',
                         'windSpeed', 'windGust', 'windBearing', 
                         'cloudCover', 'uvIndex', 'visibility', 'ozone');
$cl_headers     = array (
                        'time' => '', 'icon' => '', 'summary'=> lang('Conditions'),              # 3
                        'temperatureHigh' => lang('Temp'),                                # 1+1
               #         'apparentTemperatureHigh' => 'Feels',                                    # 1  + ? dewpoint
                        'precipIntensity' => lang('Precipitation'),                              # 1
                        'windSpeed' => lang('Windspeed'),'windBearing' => lang('Direction'), # 2
                        'uvIndex' => lang('UV index'),                                           # 1
                        'pressure' => lang('Pressure')                                           # 1
                        );

# -------   check if all data is present
$cols           = count($cl_cntnt);
$arr            = $darkskydayCond[0]; #echo  '<pre>'.print_r($arr,true).'</pre>';  exit;
for ($n = 0; $n < $cols; $n++)
     {  $key    = $cl_cntnt[$n];
        if (!array_key_exists($key,$arr) )  
             {  if (array_key_exists($key, $cl_headers )) 
                     {  unset ($cl_headers[$key]);}
                $cl_cntnt[$n]   = 'n/a';} 
        } 
$cols           = count($cl_headers);
$norain         = '-';
$nouv           = '-';
$color = $clrwrm = "#FF7C39";
$clrcld = "#01A4B4";
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
.my_style().'</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'      <span style="color: '.$color.'">'.$darkskydaySummary.'</span>
    </div>'.PHP_EOL;
echo '<div class= "div_height" style="width: 100%;text-align: left; overflow: auto;">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">
<tr style="border-bottom: 1px grey solid; ">';  // print start enclosing div table
#
if (strtolower (trim($pressureunit)) == 'hpa') {$dec_pres= 1; } else {$dec_pres = 2;}
if (strtolower (trim($rainunit))      == 'mm') {$dec_rain= 1; } else {$dec_rain = 2;}
#
foreach ($cl_headers as $key => $header)
     {  echo PHP_EOL.'<th>'.$header.'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
#
for ($n = 0; $n < $rows; $n++) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';
        $arr    = $darkskydayCond[$n]; #  echo  '<pre>'.print_r($arr,true); exit;
        
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   if (isset ($arr[$key]) ) { $content  = $arr[$key]; } else {$content = 'n/a';}
                switch ($key){
                    case 'time': 
                        echo PHP_EOL.'<td>';    // if extra style is needed
                        $text   = date('l',(int)$content);
                        echo '<span style="color: '.$color.';">'.lang($text).'</span>'; 
                        break;
                    case 'icon':
                        $icon   = DSicon_trns ($content);
                        echo PHP_EOL.'<td><img src="./'.$icon.'" width="60" height="32" alt="'.$content.'" style="vertical-align: top;">';
                        break;
                     case 'summary': 
                        echo PHP_EOL.'<td>'.$content;
                        break;
                    case 'temperatureHigh': 
                        $tempH  = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                        $content        = $arr['temperatureLow'];
                        $tempL  = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$color.';">'.$tempH.'&deg;</span>&darr;';
                        echo '<span style="font-size: 20px; color: '.$clrcld.';">'.$tempL.'&deg;</span>';
                     
                        #$clrcld 
                        break;
                    case 'apparentTemperature':
                        if ($content == 'n/a') { $value = $arr['temperature'];}                         
                        $diff   = (float)$content - (float) $arr['temperature'];
                        if (abs ( $diff ) < 3 )  { $value = ' ';}
                        else {  $temp   = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                                $value = '<span style="font-size: 20px; color: '.$color.';">'.$temp.'<small>&deg;</small></span>';} 
                        echo PHP_EOL.'<td>'.$value;
                        break;
                    case 'precipIntensity': 
                        echo PHP_EOL.'<td>'; # $content= 5;
                        $content        = (float) $content;
                        if (trim($content) == '' || $content == 0 ) {echo $norain; break;}
                        $content= convert_precip(24 * $content,$darksky_used_rain,$rainunit,2);
                        #$content= number_format($content,2) ;        
                        $unit   = lang($rainunit);
                        echo $content.'<small> '.$unit.'</small>';
                        if (isset ($arr['precipProbability']) )
                             {  $content = 100 * (float) $arr['precipProbability'];
                                $content = (int) $content;
                                if ($content == 0 ) { break;}}
                        echo ' <small>'.$content.'%</small>';
                        break;              
                    case 'windSpeed':  
                        $wspd   = convert_speed ((float) $content,$darksky_used_wind,$windunit,0) ;
                        echo PHP_EOL.'<td>'.$wspd;
                        if (isset ($arr['windGust']) )
                             {  $content = $arr['windGust'];             
                                if (trim($content) <> '') {echo '-'.convert_speed ((float) $content,$darksky_used_wind,$windunit,0);}}
                        echo  ' '.$windunit; 
                        break;
                    case 'windBearing': 
                        $bearing        = (int) $content; 
                        $compass = windlabel($bearing); # 2021-12-08
                        echo PHP_EOL.'<td>';
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$compass.'"  style="vertical-align: bottom;"> ';
                        $compass = lang($compass);  # 2019-04-18
                        echo $compass;
                        break;
                    case 'uvIndex': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' || $value == '0')   {  echo $nouv;  break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;
                    case 'pressure':
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' )  {  echo '-';  break;}
                        $value =  convert_baro ($content,$darksky_used_baro,$pressureunit,$dec_pres).' '.$pressureunit; 
                        echo number_format((float) $value,$dec_pres,'.','');
                        $i = $n+1;
                        if ($i >= $rows ) {break;}
                        $diff = $darkskydayCond[$i]['pressure'] - (float)$content; 
                        if ($diff > 0 ) {echo ' &uarr;';}
                        elseif ($diff < 0 ) {echo ' &darr;';}
                        break;
                        
                    default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
                } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '<tr><td  colspan="'.$cols.'">
<span style="float: right; font-size: 10px;"><a href="'.$ds_href.'" target="_blank" style="color: grey">
'.lang("Powered by").': '.$ds_name.'&nbsp;&nbsp;</a></span>
</td></tr></table>
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
                    border: 1px solid #FFFFFF;} '.PHP_EOL;   
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
