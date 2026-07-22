<?php  $scrpt_vrsn_dt  = 'soil_tmp_mst_popup.php|01|2021-12-08|';  # PHP 8.1 +no-value + close optional |  release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = true;         // use generalsetting 
#$show_close_x   = false;        // set to false to switch of regardless of settings
$fnt_size       = ' font-size: 12px; ';
$row_border     = 'border-bottom: 1px grey solid;';
$cnd_border     = 'border: 1px; border-radius: 5px;';
#
# ------------------------------     texts used
$ltxt_clsppp    = 'Close';
$ltxt_url       = 'Soil sensor - more information';
$ltxt_hd1       = 'Current planting and watering guide';
$ltxt_hd2       = 'Explanation';
$moisture       = 'Moisture';
$temperature    = 'Temperature';
$lococation     = 'Location';
$mst_expplain1  ='Water potential is commonly measured in units of bars (and centibars in the English system of measurement) or kilopascals (in metric units).<br />
One bar is approximately equal to one atmosphere (14.7 lb/in 2 ) of pressure. One centibar is equal to one kilopascal.';
$mst_textArr[1] ='Saturated Soil. Occurs for a day or two after irrigation.';
$mst_textArr[2] ='Soil is adequately wet (except coarse sands which are drying out at this range)';
$mst_textArr[3] ='Usual range to irrigate or water (except heavy clay soils)<br />
Irrigate at the upper end of this range in cool humid climates and with higher water-holding capacity soils.';
$mst_textArr[4] ='Usual range to irrigate heavy clay soils';
$mst_textArr[5] ='Soil is becoming dangerously dry for maximum production.';
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
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#-----------------------------------------------
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $ltxt_clsppp    = lang($ltxt_clsppp); #### 2021-02-11
        $close          = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close          = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.lang($ltxt_url).'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body style="width: 100%; overflow: auto; padding: 2px;   box-sizing: border-box;">
<div class="PWS_module_title font_head" style="width: 100%; " >
'.$close.'</div>
<div  style="width: 100%;  background-color: #FEFEFE; padding-top: 4px; " >';
#
# ------------- -----------weather values to use
#
#
$name   = array();      // used for translation if no "warning texts used 
$name[] = 'soil1';      // use the language files for translation to your text.
$name[] = 'soil2';
$name[] = 'soil3';
$name[] = 'soil4';
$name[] = 'soil5';
$name[] = 'soil6';
$name[] = 'soil7';
$name[] = 'soil8';
# -----------------------
$t_key  = array();      // default $weather[ __sensor_name__']
$t_key[]= 'soil_tmp1';  // Only change if you want another sequence
$t_key[]= 'soil_tmp2';  // the script itself will check if the sensors exists
$t_key[]= 'soil_tmp3';  //    and skip non-existing sensors
$t_key[]= 'soil_tmp4';
$t_key[]= 'soil_tmp5';
$t_key[]= 'soil_tmp6';
$t_key[]= 'soil_tmp7';
$t_key[]= 'soil_tmp8';
$h_key  = array();      // same  as above for temp
$h_key[]= 'soil_mst1';
$h_key[]= 'soil_mst2';
$h_key[]= 'soil_mst3';
$h_key[]= 'soil_mst4';
$h_key[]= 'soil_mst5';
$h_key[]= 'soil_mst6';
$h_key[]= 'soil_mst7';
$h_key[]= 'soil_mst8';
#
$lines  = 8;  // max number of 8 moist and 8 temp sensors
#
# ------------------------------test values
# $weather['soilmoist_type'] = 'cb';
# $weather['temp_units']  = 'F';

# $weather['soil_tmp1']  = 10;  #$name[0] = 'gym';
# $weather['soil_tmp2']  = 30;  #$name[1] = 'workshop';
# $weather['soil_tmp2']  = false;  #$name[1] = 'workshop';
# $weather['soil_tmp3']  = 36;  $name[2] = 'greenhouse';
# $weather['soil_tmp4']  = -6;  #$name[3] = 'wine cellar';
#
# $weather['soil_mst1'] = $weather['soil_mst2'] = $weather['soil_mst3'] = $weather['soil_mst4'] = false;
# $weather['soil_mst5'] = $weather['soil_mst6'] = $weather['soil_mst7'] = $weather['soil_mst8'] = false;
#
# $weather['soil_mst1']  = 10;
# $weather['soil_mst2']  = 40;
# $weather['soil_mst3']  = 999;
# $weather['soil_mst4']  = 83;
# $weather['soil_mst5']  = false;
# $weather['soil_mst6']  = false;
# $weather['soil_mst7']  = false;
# $weather['soil_mst8']  = false; 
# ------------------------------test values
#
if (!array_key_exists ('soilmoist_type',$weather) )
     {  $moist_unit = '%'; } 
else {  $moist_unit = $weather['soilmoist_type'];}
#
$smArr = array();
if ($moist_unit == '%')    // % is used by  ecowitt & ambient
     {  $soilArr = array (100,75,50,25,0);  // % levels not yet verified  
#                     background color
#                             text color
#                                    condition          range (used in pop-up)      link to docs
        $no_result_mst= 
        $smArr['101']= 'grey|white|ERROR no reading|?? %|0';
        $smArr['75'] = '#15A904|white|Saturated|'         .$soilArr[1].' - '.$soilArr[0].' %|1';  // 100 - 75
	$smArr['50'] = '#15A904|white|Adequate|'          .$soilArr[2].' - '.$soilArr[1].' %|2';
	$smArr['25'] = '#FF0000|white|Irrigation needed|' .$soilArr[3].' - '.$soilArr[2].' %|4';
	$smArr['0' ] = '#9933CC|white|Dangerous Dry|'     .$soilArr[4].' - '.$soilArr[3].' %|5';}  
#
else {  $soilArr = array (0,11,26,61,101,240);     // cb levels 
        $no_result_mst= 
        $smArr['240']= 'grey|white|ERROR no reading|?? cb|0';                
	$smArr['101']= '#9933CC|white|Dangerous Dry|'      .$soilArr[4].' - '.$soilArr[5].' cb|5';
	$smArr['61'] = '#FF0000|white|Irrigation needed|'  .$soilArr[3].' - '.$soilArr[4].' cb|4';
	$smArr['26'] = '#FF0000|white|Irrigation desired|' .$soilArr[2].' - '.$soilArr[3].' cb|3';
	$smArr['11'] = '#15A904|white|Adequate|'           .$soilArr[1].' - '.$soilArr[2].' cb|2';
        $smArr['0']  = '#15A904|white|Saturated|'          .$soilArr[0].' - '.$soilArr[1].' cb|1';} // 0 - 11
#
$tempArr        = array (-18,-6,0,10,15,21,37);  
$stArr = array();
#                     background color
#                             text color
#                                    condition          range (used in pop-up)
	$stArr['-18']   = 'grey|white|ERROR no reading|'.            ' &lt; ' .$tempArr[0];
	$stArr['-6']    = '#003399|white|Deep freeze|'.              ' &lt; ' .$tempArr[1];
	$stArr['0']     = '#003399|white|Frost line|' .      $tempArr[1].' - '.$tempArr[2];
	$stArr['10']    = '#FF0000|white|Too cold to plant|'.$tempArr[2].' - '.$tempArr[3];
	$stArr['15']    = '#15A904|white|Minimum growth|'.   $tempArr[3].' - '.$tempArr[4];
	$stArr['21']    = '#15A904|white|Optimal growth|'.   $tempArr[4].' - '.$tempArr[5];
	$stArr['37']    = '#15A904|white|Ideal growth|'.     $tempArr[5].' - '.$tempArr[6];
$no_result_tmp  =    'grey|ERROR no reading';  // when to large  found # 2021-12-04

#
$count  = $temps = $moists = 0;  
$string = '';
for ($n = 0; $n < $lines; $n++) {
        $exists = 0;
        $key    = $t_key[$n];
        if (!array_key_exists($key,$weather) || $weather[$key] === false) 
             {  $t_key[$n] = false;} 
        else {  $exists++;
                $temps++;}
        $key    = $h_key[$n];
        if (!array_key_exists($key,$weather) || $weather[$key] === false) 
             {  $h_key[$n] = false;} 
        else {  $exists++;
                $moists++;}
        if ($exists > 0) {$count++;}
} 
# 
if ($count == 0)
     {  echo '<small style="color: red;">Soil sensors  not available, script ends</small>';
        return;}
#
if ($temps == 0 || $moists == 0) 
     {  $cols   = 3;} 
else {  $cols   = 5;}
#
echo '<table style="font-size:10px; width: 98%;margin: 0 auto; padding: 2px; text-align: center; border-collapse: collapse; box-sizing: border-box;">
<tr style="'.$fnt_size.' '.$row_border.' box-sizing: border-box; "><th colspan="'.$cols.'">'.lang($ltxt_hd1).'</th></tr>
<tr style="'.$fnt_size.' '.$row_border.' box-sizing: border-box; "><th>'.lang($lococation).'</th>';
if ($moists <> 0)
     {  echo '<th>'.$moist_unit.'</th><th>'.lang($moisture).'</th>'; }
else {  echo '<th> &deg;'. $weather['temp_units'].'</th><th>'.lang($temperature).'</th>'; } # echo ' $moists='.$moists.' $temps='.$temps; exit;
if ($moists <> 0 && $temps <> 0)
     {  echo '<th>'.lang($temperature).'</th><th> &deg;'. $weather['temp_units'].'</th>'; }
echo '</tr>'.PHP_EOL;
# for each sensor
for ($n = 0; $n < $lines; $n++) {
# check if sensor is available
        $key_t  = $t_key[$n];
        $key_m  = $h_key[$n];          
        if ($key_t == false &&  $key_m == false) {continue;} 
        if ($key_t <> false) {
                $tmp       = $weather[$key_t];
                if ($weather['temp_units'] == 'F')
                      { $key = (int) convert_temp ($tmp,'F','C'); } 
                else  { $key = (int) $tmp;}    
                $result = $no_result_tmp;       // default eror values
                foreach ($stArr as $level => $texts)
                     {  if ($key < (int) $level)
                             {  $result = $texts;
                                break;}
                        continue;}  
                list ($color_b_t, $color_t_t, $text_t,$range_t) = explode ('|',$result.'||||');}  # 2021-12-04
        $result = $no_result_mst; // default eror values        
        if ($key_m <> false) 
             {  $moist    = $weather[$key_m];
                $result = $no_result_mst; // default eror values
                foreach ($smArr as $level => $texts)
                     {  if ($moist >= (int) $level)
                             {  $result = $texts;
                                break;}
                        continue; } // eo for each
                list ($color_b, $color_t, $text,$range) = explode ('|',$result );}
#
# print 1 row, first the name of the sensor
        echo '<tr style="'.$fnt_size.' '.$row_border.' "><td>'.lang($name[$n]).'</td>';
# first two colomns either moist or temp
        if ($moists <> 0 && $key_m <> false) {  
# if moist sensors available  print if this sensor has moist  
                echo '<td>'.$moist.'</td>';
                echo '<td><div style=" width: 98%; font-size: 70%; padding: 1px; '
                        .'background-color: '.$color_b.'; '
                        .'color: '.$color_t.'; '.$cnd_border.' "><b>'
                        .$text.'<br />( '.$range.' )</b>'
                        .'</div></td>' ;}
#   else if this moist not exists empty 2 *td
        elseif ($moists <> 0 ) {
                echo '<td> </td><td> </td>'; }
#  else no moist at all print temp
        else {  echo '<td>'.$tmp.'</td>';
                echo '<td><div  style=" width: 98%; font-size: 70%; padding: 1px; '
                        .'background-color: '.$color_b_t.'; '
                        .'color: '.$color_t_t.'; ' .$cnd_border.' "><b>'
                        .$text_t.'<br />( '.$range_t.' )</b>'
                        .'</div></td>' ;}
# It temp not printed yet 
if ($moists <> 0 && $temps <> 0 && $key_t <> false) {
        echo '<td><div  style=" width: 98%; font-size: 70%; padding: 1px; '
                .'background-color: '.$color_b_t.'; '
                .'color: '.$color_t_t.'; '.$cnd_border.' "><b>'
                .$text_t.'<br />( '.$range_t.' )</b>'
                .'</div></td>' ;
        echo '<td>'.$tmp.'</td>'; }
#
elseif ($moists <> 0 && $temps <> 0 && $key_t == false) {
        echo '<td>&nbsp;</td><td>&nbsp;</td>'; }
echo '</tr>'.PHP_EOL;
}        
echo '</table>
<br /><br />
<table style="font-size:10px; padding: 2px; margin: 0 auto;text-align: center; border-collapse: collapse; box-sizing: border-box;">
<tr style="'.$fnt_size.' '.$row_border.' box-sizing: border-box; "><th colspan="2">'.$ltxt_hd2.'</th></tr>'.PHP_EOL;
#
$start  = 1;
$n      = 0;
foreach($smArr as $key => $string)
     {  if ($n < $start) { $n++; continue;}
        list ($color_b, $color_t, $text, $range, $docs) = explode ('|',$string);
        echo '<tr style="'.$fnt_size.' '.$row_border.' ">';
        echo '<td ><div style=" width: 100px; font-size: 70%; padding: 1px; '
                        .'background-color: '.$color_b.'; '
                        .'color: '.$color_t.'; '.$cnd_border.' "><b>'
                        .$text.'<br />( '.$range.' )</b>'
                        .'</div></td>';
        echo '<td style="text-align: left;"><span style="padding-left: 4px;">'.lang($mst_textArr[$docs]).'</span></td></tr>';
        $n++; }
echo '</table><br />
</div><br />
</body>
</html>'.PHP_EOL;
#
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
#
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
/*
function getSoilMoistIndex ( $moist ) {     
        global $color_b, $color_t, $text, $range, $soilArr,$smArr;
# check value + no decimals	 
	$moist   = (int) $moist;
        $result = 'grey|white|ERROR no reading|??|';  // for invalid moist
        foreach ($smArr as $level => $texts)
             {  if ($moist >= (int) $level)
                     {  $result = $texts;
                        break;}
                continue;}
        list ($color_b, $color_t, $text,$range) = explode ('|',$result );
        return;
}  // eof get_SoilMoistIndex
#
function getSoilTempIndex ( $soiltemp ) {
	global $weather,  $stck_lst, $color_b_t, $color_t_t, $text_t,$range_t;
#
	if ($weather['temp_units'] == 'F')
	      { $key = (int) convert_temp ($soiltemp,'F','C'); } 
	else  { $key = (int) $soiltemp;}    
#
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') $soiltemp='.$soiltemp.' $key='.$key.PHP_EOL;
        echo '<!-- '.basename(__FILE__).' ('.__LINE__.') $soiltemp='.$soiltemp.' $key='.$key.' -->'.PHP_EOL;
#
	$tempArr        = array (-18,-6,0,10,15,21,37);  
	$stArr = array();
#                     background color
#                             text color
#                                    condition          range (used in pop-up)
	$stArr['-18']   = 'grey|white|ERROR no reading|'.            ' &lt; ' .$tempArr[0];
	$stArr['-6']    = '#003399|white|Deep freeze|'.              ' &lt; ' .$tempArr[1];
	$stArr['0']     = '#003399|white|Frost line|' .      $tempArr[1].' - '.$tempArr[2];
	$stArr['10']    = '#FF0000|white|Too cold to plant|'.$tempArr[2].' - '.$tempArr[3];
	$stArr['15']    = '#15A904|black|Minimum growth|'.   $tempArr[3].' - '.$tempArr[4];
	$stArr['21']    = '#15A904|black|Optimal growth|'.   $tempArr[4].' - '.$tempArr[5];
	$stArr['37']    = '#15A904|black|Ideal growth|'.     $tempArr[5].' - '.$tempArr[6];
#	
	$result         = 'grey|white|ERROR no reading|'. ' &gt; '.$tempArr[6];  // when to large  found
        foreach ($stArr as $level => $texts)
             {  if ($key < (int) $level)
                     {  $result = $texts;
                        break;}
                continue;}
        list ($color_b_t, $color_t_t, $text_t,$range_t) = explode ('|',$result);
	$range_t .= ' &deg;'.$weather['temp_units'];;
}  // end getSoilTempIndex
*/
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
