<?php  $scrpt_vrsn_dt  = 'metar_aeris_popup.php|01|2020-11-06|';  # release 2012_lts

#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$color          = "#FF7C39"; // head line
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
$ltxt_url       = lang('Metar Aviation Weather Data');
$ltxt_clsppp    = lang('Close');

$ltxt_hd1       = lang('Temperature');
$ltxt_hd2       = lang('Wind');
$ltxt_hd3       = lang('Current Conditions');
$ltxt_hd4       = lang('Airport');
$ltxt_temp      = lang('Temperature');
$ltxt_dewp      = lang('Dewpoint');
$ltxt_baro      = lang('Pressure');
$ltxt_humi      = lang('Humidity');
$ltxt_visi      = lang('Visibility');
$ltxt_code      = lang('Airport-code');
$ltxt_loca      = lang('Location');
$ltxt_dist      = lang('Distance');
$ltxt_time      = lang('Time');
$ltxt_calm      = lang('Calm');
$ltxt_duni      = lang($distanceunit); // km   mi
$ltxt_hPa       = lang('hPa');
$ltxt_inHg      = lang('inHg');
$ltxt_km        = lang('km');
$ltxt_mi        = lang('mile');
$ltxt_kmh       = lang('KM/H');
$ltxt_mph       = lang('MPH');
$ltxt_ms        = lang('M/S');
$ltxt_kts       = lang('KTS');

#-----------------------------------------------
#                     arrays to drive the script
#-----------------------------------------------
$b_clrs                 = array();
$b_clrs['maroon']       = 'rgba(208, 80, 65, 0.7)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841;';
$b_clrs['yellow']       = '#ecb454'; #rgba(186, 146, 58, 1)';
$b_clrs['green']        = '#9aba2f';
#
$temp_colors = array(
        '#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
        '#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
        '#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
        '#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
        '#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
        '#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
        '#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
        '#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',);
$maxTemp        = count($temp_colors) - 1;
#
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
$ars_ccnt_fl    = 'aeris_ccn.json';  // metar data
$json           = file_get_contents($fl_folder.$ars_ccnt_fl);
$data_ccn       = json_decode($json, true); # echo '<pre>json data'.print_r($data_ccn, true) ; exit;    
if ($data_ccn == null ) 
     {  echo '<span style="color: red;"><b>invalid data</b></span>'; return;}
elseif ($data_ccn['success'] <> 1)
     {  echo '<span style="color: red;"><b>invalid data</b><br />'
                .$data_ccn['error'].'</span>'; return;}
#
if (isset ($_REQUEST['test']))
     {  echo '<!-- '.print_r($data_ccn,true).' -->'.PHP_EOL; } #exit;
#
$metar_id       = $data_ccn['response']['id'];
if (!isset ($airport1) || $airport1 == 'AirportName')
     {  $metar_name     = $data_ccn['response']['place']['name'];}
else {  $metar_name     = $airport1;}
$metar_time     = $data_ccn['response']['obTimestamp'];
$metar_raw      = $data_ccn['response']['raw'];
$ltxt_url      .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$metar_id.' '.$metar_name;
$ccn            = $data_ccn['response']['ob'];
$metar_temp_c   = $ccn['tempC'];
$metar_temp_f   = $ccn['tempF'];
$metar_dewp_c   = $ccn['dewpointC'];
$metar_dewp_f   = $ccn['dewpointF'];
$metar_humidity = $ccn['humidity'];
$metar_wind_kts = $ccn['windKTS'];
$metar_wind_mph = $ccn['windMPH'];
$metar_wind_mps = round($metar_wind_mph * 0.44704);
$metar_wind_kmh = $ccn['windKPH'];
$metar_wind_deg = $ccn['windDirDEG'];
$metar_wind_dir = $ccn['windDir'];
$look           = str_replace ('.png','',$ccn['icon']);
$icon           = icon_trns_hrs ($look);
$iconXX         = './pws_icons/'.$icon.'.svg';
$original       = $ccn['weather'];
$translated     = lang($original);
if ($original == $translated)
     {  $translated = '';
        $blocks = explode ('with',$original); 
        foreach ($blocks as $key => $block)
             {  $or_BL  = trim ($block);
                $tr_BL  = lang ($or_BL);
                if ($or_BL == $tr_BL)
                     {  $words  = explode (' ',$or_BL); 
                        foreach ($words as $key => $word) { $translated .= lang ($word).' ';}
                        $translated = trim($translated);}
                else {  $translated .= $tr_BL;}
                $translated .= '#';}
        $translated = substr($translated,0,-1);
      #  $translated = str_replace ('#',' '.lang('with').' ',$translated);
        $translated = str_replace ('#',' , ',$translated);
        }                       
$textXX         = $translated;
$metar_baro_mb  = $ccn['pressureMB'];
$metar_baro_hg  = $ccn['pressureIN'];
$metar_visb_mi  = round($ccn['visibilityMI']);
$metar_visb_km  = round($ccn['visibilityKM']);
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
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body class="dark" >
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close.'
    <span style="color: '.$color.'; ">'.$ltxt_url.'</span>
    </div>
    <div class="PWS_weather_container"><!-- toprow -->
        <div class="PWS_weather_item" style="position: relative;"><!-- weatheritem 1 -->
                <div class="PWS_module_title"><div class="title">'.$ltxt_hd1.'</div></div>
                <div class="PWS_left" style="width: 54%; text-align: left; padding-left: 4px;" >';
#

#-----------------------------------------------
#                                     temp block
#-----------------------------------------------
#                temp dew humid on the left side
echo '<table>                    
<tr class="PWS_3_heigh "><!-- temp -->'.colortemp ($metar_temp_c, $metar_temp_f, $ltxt_temp).'</tr>
<tr class="PWS_3_heigh "><!-- dew -->'.colortemp ($metar_dewp_c, $metar_dewp_f, $ltxt_dewp).'</tr>
<tr class="PWS_3_heigh "><!-- hum -->';
$n      = round(0.8 * $metar_humidity);
if ($n > $maxTemp)      { $color = $temp_colors[$maxTemp];}
else                    { $color = $temp_colors[$n];}
echo '<td style="text-align: right;">
<span style="font-size: 36px; color: '.$color.';">'.$metar_humidity.'</span><td><sup style="font-size: 12px;" >% '
.$ltxt_humi.'</sup></td></tr>
</table>
                </div><!-- eo left part -->
                <div class="PWS_right" style="width: 40%; text-align: center; padding-right: 4px;" >
                <br /><br /><br />
<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>
';
#                           generate temp basket
$lvl    = array(40,35,30,25,20,15,10,5,0,-5,-10,-30); // temp level in basket from +40 -> -30
$fll    = array();
foreach ($lvl as $value) {$fll[] = '"'.$temp_colors[$value + 30].'"';}
$d      = array();
$d[] = 'd=" M 7.00  8.01 C 17.00  8.00 27.00  8.00 37.00  8.00 C 37.00  8.75 37.00 10.25 37.00 11.00 C 27.00 11.00 17.00 11.00 7.00 11.00 C 7.00 10.25 7.00  8.75 7.00  8.01 Z" />';
$d[] = 'd=" M 7.00 12.00 C 17.00 12.00 27.00 12.00 37.00 12.00 C 37.00 13.67 37.00 15.33 37.00 17.00 C 27.00 17.00 17.00 17.00 7.00 17.00 C 7.00 15.33 7.00 13.67 7.00 12.00 Z" />';
$d[] = 'd=" M 7.00 18.00 C 17.00 18.00 27.00 18.00 37.00 18.00 C 37.00 19.67 37.00 21.33 37.00 23.00 C 27.00 23.00 17.00 23.00 7.00 23.00 C 7.00 21.33 7.00 19.67 7.00 18.00 Z" />';
$d[] = 'd=" M 7.00 24.00 C 17.00 24.00 27.00 24.00 37.00 24.00 C 37.00 25.67 37.00 27.33 37.00 29.00 C 27.00 29.00 17.00 29.00 7.00 29.00 C 7.00 27.33 7.00 25.67 7.00 24.00 Z" />';
$d[] = 'd=" M 7.00 30.00 C 17.00 30.00 27.00 30.00 37.00 30.00 C 37.00 31.67 37.00 33.33 37.00 35.00 C 27.00 35.00 17.00 35.00 7.00 35.00 C 7.00 33.33 7.00 31.67 7.00 30.00 Z" />';
$d[] = 'd=" M 7.00 36.00 C 17.00 36.00 27.00 36.00 37.00 36.00 C 37.00 37.67 37.00 39.33 37.00 41.00 C 27.00 41.00 17.00 41.00 7.00 41.00 C 7.00 39.33 7.00 37.67 7.00 36.00 Z" />';
$d[] = 'd=" M 7.00 42.00 C 17.00 41.99 27.00 42.00 37.00 42.00 C 37.00 43.67 37.00 45.33 37.00 47.00 C 27.00 47.00 17.00 47.00 7.00 47.00 C 7.00 45.33 7.00 43.67 7.00 42.00 Z" />';
$d[] = 'd=" M 7.00 48.00 C 17.00 48.00 27.00 48.00 37.00 48.00 C 37.00 49.67 37.00 51.33 37.00 53.00 C 27.00 53.00 17.00 53.00 7.00 53.00 C 7.00 51.33 7.00 49.67 7.00 48.00 Z" />';
$d[] = 'd=" M 7.00 54.00 C 17.00 54.00 27.00 54.00 37.00 54.00 C 37.00 55.67 37.00 57.33 37.00 59.00 C 27.00 59.00 17.00 59.00 7.00 59.00 C 7.00 57.33 7.00 55.67 7.00 54.00 Z" />';
$d[] = 'd=" M 7.00 60.00 C 17.00 60.00 27.00 60.00 37.00 60.00 C 37.00 61.67 37.00 63.33 37.00 65.00 C 27.00 65.00 17.00 65.00 7.00 65.00 C 7.00 63.33 7.00 61.67 7.00 60.00 Z" />';
$d[] = 'd=" M 7.00 66.00 C 17.00 66.00 27.00 66.00 37.00 66.00 C 37.00 67.67 37.00 69.33 37.00 71.00 C 27.00 71.00 17.00 71.00 7.00 71.00 C 7.00 69.33 7.00 67.67 7.00 66.00 Z" />';
$d[] = 'd=" M 7.00 72.00 C 17.00 72.00 27.00 72.00 37.00 72.00 C 37.00 73.67 37.00 75.33 37.00 77.00 C 27.00 77.00 17.00 77.00 7.00 77.00 C 7.00 75.33 7.00 73.67 7.00 72.00 Z" />';
#
for ($i = 0; $i < 12; $i++)  # fill each line in basket
     {  if ($metar_temp_c <  $lvl[$i]) {$fll[$i] = '"currentcolor" ';}  # with no color
        echo ' <path fill='.$fll[$i].'  opacity="0.7" '.$d[$i].PHP_EOL;}       # or the color for that temp level           
echo '</svg>
                </div><!-- eo right part -->'.PHP_EOL;         
echo '        </div><!-- eo weatheritem 1 Temperatuur -->'.PHP_EOL; # --- temp block 
#
#-----------------------------------------------
#                                     wind block
#-----------------------------------------------
#                       speed in 4 units _ graph
#
echo '        <div class="PWS_weather_item"><!-- weatheritem 2 wind -->
                <div class="PWS_module_title"><div class="title">'.$ltxt_hd2.'</div></div>
                <div class="PWS_left" style="width: 40%; text-align: left; padding-left: 4px;" ><table>'.PHP_EOL;
# $metar34windspeedkmh   $metar34windspeedkmh $metar34windspeedkts
$lvl_speed      = array(40,35,20,-1);                   // based on km/h speed
$lvl_sclr       = array('red','orange','yello','green');// we apply these colors
foreach ($lvl_speed as $n => $speed) {if ($metar_wind_kts > $speed) {$class = $lvl_sclr[$n]; break;}}
#
# ----------------------   define html for  km/h
#if ((int) $metar34windspeedkmh < 10)    
#     {  $extra_space = '&nbsp;';} else {$extra_space = '';}
$wndkmh = '<tr class="PWS_4_heigh "><!-- wind kmh -->'
        .'<td style="text-align: right;"><span style="font-size: 36px;" class="'
        .$class.'">'
        .$metar_wind_kmh.'</span></td><td><sup style="font-size: 12px;" >  '
        .$ltxt_kmh.' </sup></td></tr>'.PHP_EOL;
#
# -----------------------    define html for  m/h
#if ((int) $metar34windspeedmph < 10) 
#     {  $extra_space = '&nbsp;';} else {$extra_space = '';}
$wndmph = '<tr class="PWS_4_heigh "><!-- wind kmh -->'
        .'<td style="text-align: right;"><span style="font-size: 36px;" class="'
        .$class.'">'
        .$metar_wind_mph.'</span></td><td><sup style="font-size: 12px;" >  '
        .$ltxt_mph.' </sup></td></tr>'.PHP_EOL;
#
# -----------------------    define html for  m/s
#if ((int) $metar34windspeedmps < 10) 
#     {  $extra_space = '&nbsp;';} else {$extra_space = '';}
$wndms  = '<tr class="PWS_4_heigh "><!-- wind kmh -->'
        .'<td style="text-align: right;"><span style="font-size: 36px;" class="'
        .$class.'">'
        .$metar_wind_mps.'</span></td><td><sup style="font-size: 12px;" >  '
        .$ltxt_ms.' </sup></td></tr>'.PHP_EOL;
#
# ----------------------    define html for  kts
if ((int) $metar_wind_kts < 10) 
     {  $extra_space = '&nbsp;';} else {$extra_space = '';}
$wndkts = '<tr class="PWS_4_heigh "><!-- wind kmh -->'
        .'<td style="text-align: right;"><span style="font-size: 36px;" class="'
        .$class.'">'
        .$metar_wind_kts.'</span></td><td><sup style="font-size: 12px;" >  '
        .$ltxt_kts.' </sup></td></tr>'.PHP_EOL;
#
# ------------------------ print used unit first
#
if ($windunit == 'km/h') 
     {  echo $wndkmh.$wndmph.$wndms.$wndkts; } 
elseif ($windunit == 'mph') 
     {  echo $wndmph.$wndkmh.$wndms.$wndkts; } 
elseif ($windunit == 'm/s') 
     {  echo $wndms.$wndmph.$wndkmh.$wndkts; } 
else {  echo $wndkts.$wndms.$wndmph.$wndkmh; }
#
# -------------------------------  print compass
#

echo '              </table> </div>
                <div class="PWS_right" style="width: 50%; text-align: center; padding-right: 4px;" >
<div class="metar_compass1">
    <div class="metar_compass-line1">
        <div class="thearrow2"></div>
    </div>
    <div><br /><br /><br />
        <div class="large">';
if( $metar_wind_deg==0) {  echo $ltxt_calm ;}  else {  echo $metar_wind_deg,'&deg;';}
echo '</div><!-- large -->
        <div class="windirectiontext1">';
#
#$windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
#         "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
$compass        = $metar_wind_dir; #$windlabel[ fmod((($metar34windir + 11) / 22.5),16) ];
echo lang($compass).'</div><!-- windirectiontext1 --> 
    </div><!-- text1 -->          
</div><!-- metar_compass1 -->   
                </div><!-- PWS_right -->        
        </div><!-- eo weatheritem 2 wind -->'.PHP_EOL; // eo wind block
echo '    </div><!-- eo toprow -->'.PHP_EOL; // eo top row

echo '    <div class="PWS_weather_container" style="height: 220px;"><!-- second row -->'.PHP_EOL; // second row
#
# ------------  print current conditions  block
#
echo '        <div class="PWS_weather_item " style="position:relative; height: 216px; "><!-- weatheritem 3 info -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd3.'</div></div>
            <br />
            <table style="font-size: 11px; width: 98%; padding-top: 8px; margin: 0 auto; text-align: center;">
                <tbody><tr>
                <td><img style="vertical-align: bottom; width : 100px;" rel="prefetch" src="'.$iconXX.'" alt="'.$ccn['weather'].'"></td>
                <td style="width: 50%; text-align: left; font-size: 20px;">'.$textXX.'</td>
                </tr>
                <tr><td colspan="2" style="font-size: 16px;">'.PHP_EOL;
echo $ltxt_baro.' <span class="green"> '
        . $metar_baro_mb .' </span>('.$ltxt_hPa.') - <span class="green"> '
        . $metar_baro_hg .' </span>('.$ltxt_inHg.') <br />'
        .$ltxt_visi.' <span class="yellow"> '
        .$metar_visb_mi.' </span>('.$ltxt_mi.') - <span class="yellow"> '
        .$metar_visb_km   .' </span>('.$ltxt_km.') 	
                </tr>
            </tbody></table>
        </div><!-- eo weatheritem 3 -->'.PHP_EOL;
#
# -------------------------   print airport info
#
echo '        <div class="PWS_weather_item " style="position: relative; height: 216px;"><!-- weatheritem  4 info -->'.PHP_EOL;
echo '        <div class="PWS_module_title"><div class="title">'.$ltxt_hd4.'</div></div>
            <div style="text-align: left; padding: 8px;  font-size: 13px;">'.PHP_EOL;
       
echo $ltxt_code.' <span class="large">'
        .$metar_id.'</span><br /><br />'.PHP_EOL;
echo $ltxt_loca.': <span class="yellow"> '
        .$metar_name.'</span><br />'
     .$ltxt_dist.': <span class="green"> '
        .$airport1dist.'</span> ('.$ltxt_duni.')<br /><br />'.PHP_EOL;
echo 'Metar:<br /><span style="font-family: monospace; font-size: 10px;">' .$metar_raw.'</span><br /><br />'.PHP_EOL;
echo $ltxt_time.': '.date($dateFormat,$metar_time).' '.set_my_time($metar_time,true).' '. date('T');
echo '<!-- $metar_time ='.$metar_time.' -->
            </div>
        </div><!-- eo weatheritem info --> 
    </div><!-- eo second row -->'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
function colortemp ($tempC, $tempF, $text)
     {  global $temp_colors, $maxTemp, $tempunit;
        $clrTemp        = round($tempC) + 32;
        if      ($clrTemp > $maxTemp )  { $color = $temp_colors[$maxTemp];}
        elseif 	($clrTemp < 0 )         { $color = $temp_colors[0];}
        else                            { $color = $temp_colors[$clrTemp];}
        if ($tempunit == 'C')           { $tempused  = $tempC;} 
        else                            { $tempused  = $tempF;}
        return '
<td style="text-align: right;"><span style="font-size: 36px; color: '.$color.'; ">'.$tempused.'</span></td><td><sup style="font-size: 12px;" >&deg;'.$tempunit.' '.$text.'</sup></td>';
        } // eof colortemp
#
# style is printed in the header 
function my_style()
     {  global $popup_css ,$b_clrs , $metar_wind_deg;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css
        $return .= '
        .orange      { color: '.$b_clrs['orange'].';}
        .green       { color: '.$b_clrs['green'].';}
        .blue        { color: #01a4b4;}
        .yellow      { color: '.$b_clrs['yellow'].';}
        .red         { color: '.$b_clrs['red'].';}
        .purple      { color: '.$b_clrs['purple'].';}
        .maroon      { color: '.$b_clrs['maroon'].';}
        .orange      { color: '.$b_clrs['orange'].';}
        .green       { color: '.$b_clrs['green'].';}
        .blue        { color: #01a4b4;}
        .yellow      { color: '.$b_clrs['yellow'].';}
        .red         { color: '.$b_clrs['red'].';}
        .purple      { color: '.$b_clrs['purple'].';}
        .maroon      { color: '.$b_clrs['maroon'].';}
 
        .metar_compass1{position:absolute;width:120px;height:120px;text-align:center;top:50px;z-index:1}
        .metar_compass1 .metar_compass-line1{
                position:absolute;
                z-index:10;
                right: 0px;left:0px;top:0px;bottom:0px;
                border-radius:100%;
                border-left:  8px solid rgba(95,96,97,.5);
                border-top:   8px solid rgba(95,96,97,.8);
                border-right: 8px solid rgba(95,96,97,.5);
                border-bottom:8px solid rgba(95,96,97,.8);
                margin:auto}

        .metar_compass1>.windirectiontext1{display:block;text-align:center;color:#aaa;
                font-family:Arial,sans-serif;font-weight:600;line-height:12px;font-size:11px;z-index:10;margin:0 0 auto}
        .windirectiontext1 span{color:#9aba2f}

        .thearrow2{transform:rotate('.$metar_wind_deg.'deg);
                position:absolute;z-index:200;top:0;left:50%;margin-left:-5px;width:10px;height:50%;
                transform-origin:50% 100%;}
        .thearrow2:after{content:"";position:absolute;left:50%;top:0;height:10px;width:10px;
                background-color: transparent;width:0;height:0;border-style:solid;border-width:14px 9px 0 9px;
                border-color:RGBA(255,121,58,1.00) transparent transparent transparent;
                transform:translate(-50%,-50%);}';
        $return         .= '    </style>'.PHP_EOL;
        return $return;
    }

#-----------------------------------------------
# Icon translation
#-----------------------------------------------
function icon_trns_hrs ($icon)
     {  global $stck_lst;
        $icn_t['blizzard']      = 'ovc_flurries';
        $icn_t['blizzardn']     = 'ovc_flurries_dark';
        $icn_t['blowingsnow']   = 'ovc_flurries';
        $icn_t['blowingsnown']  = 'ovc_flurries_dark';
        $icn_t['clear']         = 'clear_day';
        $icn_t['clearn']        = 'clear_night';
        $icn_t['clearw']        = 'clear_windy_day';
        $icn_t['clearwn']       = 'clear_windy_night';
        $icn_t['cloudy']        = 'mc_day';
        $icn_t['cloudyn']       = 'mc_night';
        $icn_t['cloudyw']       = 'ovc_windy';
        $icn_t['cloudywn']      = 'ovc_windy_dark';
        $icn_t['cold']          = 'unknown';
        $icn_t['coldn']         = 'unknown';
        $icn_t['drizzle']       = 'mc_rain';
        $icn_t['drizzlef']      = 'mc_rain';
        $icn_t['drizzlen']      = 'mc_rain';
        $icn_t['dust']          = 'dust';
        $icn_t['dustn']         = 'dust';
        $icn_t['fair']          = 'few_day';
        $icn_t['fairn']         = 'few_night';
        $icn_t['fdrizzle']      = 'ovc_sleet';
        $icn_t['fdrizzlen']     = 'ovc_sleet_dark';
        $icn_t['flurries']      = 'mc_flurries';
        $icn_t['flurriesn']     = 'mc_flurries_night';
        $icn_t['flurriesw']     = 'mc_flurries';
        $icn_t['flurrieswn']    = 'mc_flurries_night';
        $icn_t['fog']           = 'haze_day';
        $icn_t['fogn']          = 'mc_fog_dark';
        $icn_t['freezingrain']  = 'ovc_flurries';
        $icn_t['freezingrainn'] = 'ovc_flurries_dark';
        $icn_t['hazy']          = 'haze_day';
        $icn_t['hazyn']         = 'mc_fog_dark';
        $icn_t['hot']           = 'uvstrong';
        $icn_t['mcloudy']       = 'mc_day';
        $icn_t['mcloudyn']      = 'mc_night';
        $icn_t['mcloudyr']      = 'mc_rain';
        $icn_t['mcloudyrn']     = 'mc_rain_dark';
        $icn_t['mcloudyrw']     = 'mc_rain_dark';
        $icn_t['mcloudyrwn']    = 'mc_rain_dark';
        $icn_t['mcloudys']      = 'mc_rain_dark';
        $icn_t['mcloudysf']     = 'mc_rain_dark';
        $icn_t['mcloudysfn']    = 'mc_rain_dark';
        $icn_t['mcloudysfw']    = 'mc_rain_dark';
        $icn_t['mcloudysfwn']   = 'mc_rain_dark';
        $icn_t['mcloudysn']     = 'ovc_flurries';
        $icn_t['mcloudysw']     = 'ovc_flurries';
        $icn_t['mcloudyswn']    = 'ovc_flurries_dark';
        $icn_t['mcloudyt']      = 'ovc_thun_dark';
        $icn_t['mcloudytn']     = 'ovc_thun_dark';
        $icn_t['mcloudytw']     = 'ovc_thun_dark';
        $icn_t['mcloudytwn']    = 'ovc_thun_dark';
        $icn_t['mcloudyw']      = 'mc_windy';
        $icn_t['mcloudywn']     = 'mc_windy_dark';
        $icn_t['na']            = 'unknown';
        $icn_t['pcloudy']       = 'mc_day';
        $icn_t['pcloudyn']      = 'mc_night';
        $icn_t['pcloudyr']      = 'mc_rain';
        $icn_t['pcloudyrn']     = 'mc_rain_dark';
        $icn_t['pcloudyrw']     = 'mc_rain_dark';
        $icn_t['pcloudyrwn']    = 'mc_rain_dark';
        $icn_t['pcloudys']      = 'mc_rain_dark';
        $icn_t['pcloudysf']     = 'mc_rain_dark';
        $icn_t['pcloudysfn']    = 'mc_rain_dark';
        $icn_t['pcloudysfw']    = 'mc_rain_dark';
        $icn_t['pcloudysfwn']   = 'mc_rain_dark';
        $icn_t['pcloudysn']     = 'ovc_flurries';
        $icn_t['pcloudysw']     = 'ovc_flurries';
        $icn_t['pcloudyswn']    = 'ovc_flurries_dark';
        $icn_t['pcloudyt']      = 'ovc_thun_dark';
        $icn_t['pcloudytn']     = 'ovc_thun_dark';
        $icn_t['pcloudytw']     = 'ovc_thun_dark';
        $icn_t['pcloudytwn']    = 'ovc_thun_dark';
        $icn_t['pcloudyw']      = 'mc_windy';
        $icn_t['pcloudywn']     = 'mc_windy_dark';
        $icn_t['rain']          = 'mc_rain';
        $icn_t['rainandsnow']   = 'ovc_sleet';
        $icn_t['rainandsnown']  = 'ovc_sleet_dark';
        $icn_t['rainn']         = 'mc_rain_dark';
        $icn_t['raintosnow']    = 'ovc_sleet';
        $icn_t['raintosnown']   = 'ovc_sleet_dark';
        $icn_t['rainw']         = 'mc_rain_dark';
        $icn_t['showers']       = 'mc_rain_dark';
        $icn_t['showersn']      = 'mc_rain_dark';
        $icn_t['showersw']      = 'mc_rain_dark';
        $icn_t['showerswn']     = 'mc_rain_dark';
        $icn_t['sleet']         = 'ovc_sleet';
        $icn_t['sleetn']        = 'ovc_sleet_dark';
        $icn_t['sleetsnow']     = 'ovc_sleet';
        $icn_t['sleetsnown']    = 'ovc_sleet_dark';
        $icn_t['smoke']         = 'tornado';
        $icn_t['smoken']        = 'tornado';
        $icn_t['snow']          = 'mc_flurries';
        $icn_t['snown']         = 'mc_flurries_night';
        $icn_t['snowshowers']   = 'mc_flurries';
        $icn_t['snowshowersn']  = 'mc_flurries';
        $icn_t['snowshowersw']  = 'mc_flurries';
        $icn_t['snowshowerswn'] = 'mc_flurries';
        $icn_t['snowtorain']    = 'ovc_sleet';
        $icn_t['snowtorainn']   = 'ovc_sleet_dark';
        $icn_t['snoww']         = 'mc_flurries';
        $icn_t['snowwn']        = 'mc_flurries_night';
        $icn_t['sunny']         = 'clear_day';
        $icn_t['sunnyn']        = 'clear_night';
        $icn_t['sunnyw']        = 'clear_windy_day';
        $icn_t['sunnywn']       = 'clear_windy_night';
        $icn_t['tstorm']        = 'ovc_thun_dark';
        $icn_t['tstormn']       = 'ovc_thun_dark';
        $icn_t['tstorms']       = 'ovc_thun_dark';
        $icn_t['tstormsn']      = 'ovc_thun_dark';
        $icn_t['tstormsw']      = 'ovc_thun_dark';
        $icn_t['tstormswn']     = 'ovc_thun_dark';
        $icn_t['wind']          = 'ovc_windy';
        $icn_t['wintrymix']     = 'ovc_sleet';
        $icn_t['wintrymixn']    = 'ovc_sleet_dark';   
        if (!isset ($icn_t[$icon]))
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') unknown icon '.$icon.PHP_EOL;
                $return = 'offline';}
        else {  $return = $icn_t[$icon];}
        return $return;}
#-----------------------------------------------
