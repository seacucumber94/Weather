<?php $scrpt_vrsn_dt  = 'soil_tmp_mst.php|01|2021-11-30|';  # middle text + fixed hdt height | release 2012_lts
#
#---------------------- settings for this script
#  Remove / add comment marks  # at postion 1
#       for following adaptions
#
#  Add the sensor-name to the descriptive texts
$use_field_names= false;  // false = use condition texts only
$use_field_names= true;   // true  = add names if text fits.
$max_lines_names= 8;      // more then x? sensors will not show names if long names are used
#
#  Use grey background behind sensor values
$color_background = ' background-color: #e5e5e5; '; // light grey background
$color_background = ' '; // no color as background
#
$row_border     = 'border-top: 1px grey solid;';
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       

# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
# -------------  texts and weather values to use
#
$hum_l  = 'Moisture';
$tmp_l  = 'Temperature';
#
$name   = array();    // used for translation if no "warning texts used 
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
$lines  = 8;
#
if (!array_key_exists ('soilmoist_type',$weather) )
     {  $moist_unit = '%'; } 
else {  $moist_unit = $weather['soilmoist_type'];}#$moist_unit = 'cb';
#
if ($moist_unit == '%') 
     {  $soilArr        = array (100,75,40,25,0);      // % levels not yet verified   
	$smArr          = array();
#       moist level      text color  condition          
        $no_result_mst  =  
        $smArr['101']   =    'grey|ERROR no reading'; 
	$smArr['75']    =        '|Saturated';  
	$smArr['40']    =        '|Adequate' ;
	$smArr['25']    = '#FF0000|Irrigation needed';
	$smArr['0' ]    = '#FF0000|Dangerous Dry';}  
#
else {  $soilArr        = array (0,11,26,61,101,240);  
	$smArr          = array();                     
#       moist level      text color  condition          
	$no_result_mst  = 
	$smArr['240']   = 'grey|ERROR no reading';
	$smArr['101']   = '#9933CC|Dangerous Dry';
	$smArr['61']    = '#FF0000|Irrigation needed';
	$smArr['26']    = '#FF0000|Irrigation desired';
 	$smArr['11']    = '#15A904|Adequate' ;         
	$smArr['0']     = '#15A904|Saturated';  }    

#
$tempArr        = array (-18,-6,0,10,15,21,37);  
$stArr = array();
#                  text color condition          
$stArr['-18']   =    'grey|ERROR no reading';
$stArr['-6']    = '#003399|Deep freeze';
$stArr['0']     = '#003399|Frost line';
$stArr['10']    = '#FF0000|Too cold to plant';
$stArr['15']    =        '|Minimum growth';
$stArr['21']    =        '|Optimal growth';
$stArr['37']    =        '|Ideal growth';	
$no_result_tmp  =    'grey|ERROR no reading';  // when to large  found
#
# ------------------------------test values
# $weather['soilmoist_type'] = 'cb';
# $weather['temp_units']  = 'F';
# $weather['soil_tmp1']  = 30;  #$name[0] = 'gym';
# $weather['soil_tmp2']  = 18;  #$name[1] = 'workshop';
# $weather['soil_tmp2']  = false;  #$name[1] = 'workshop';
# $weather['soil_tmp3']  = 12;  $name[2] = 'greenhouse';
# $weather['soil_tmp4']  = 6;  #$name[3] = 'wine cellar';
# $weather['soil_tmp5']  = -2;  #$name[3] = 'wine cellar';
# $weather['soil_mst1']  = 90;
# $weather['soil_mst2']  = 80;
# $weather['soil_mst3']  = 120;
# $weather['soil_mst4']  = 10;
# $weather['soil_mst5']  = false;
# $weather['soil_mst6']  = false;
# $weather['soil_mst7']  = false;
# $weather['soil_mst8']  = false; 
# ------------------------------test values
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
}  #echo print_r($h_key,true).print_r($t_key,true); exit;
#
if ($count == 0)
     {  echo '<small style="color: red;">Soil sensors  not available, script ends</small>';
        return;}
#
if ($count <= 6) // set font-size based on number of lines to display
     {  $fnt_size = ' font-size: 14px; ';} 
else {  $fnt_size = ' font-size: 12px; ';}
#
# ---------------------  generate the HTML
# ----     date time of last livedata file
echo '<div class="PWS_ol_time">'.$online_txt_ld.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content">'.PHP_EOL;
#
$middle_text    = $divider = $moist_col = $temp_col = '';
if ($moists > 0) // colomn moist header
     {  $moist_col = '<th style="width: 30px;">'.$moist_unit.'</th>'; 
        $middle_text    = lang($hum_l);
        $divider = ' | ';}
if ($temps > 0) // colomn temp header
     {  $temp_col  = '<th style="width: 30px;">'.'&deg;'. $weather['temp_units'].'</th>';
        $middle_text    .= $divider.lang($tmp_l);}   # 2021-09-08 
#
echo '<table style="font-size:10px; width: 100%; text-align: center; height: 154px; border-collapse: collapse;">
<tr style="height: 16px; border-bottom: 1px grey solid;">'.$moist_col.'<th>'.$middle_text.'</th>'.$temp_col.'</tr>'.PHP_EOL;
#
for ($n = 0; $n < $lines; $n++) 
     {  $key_t  = $t_key[$n];   # echo print_r($t_key,true).'$weather[$key_t]='.$weather[$key_t]; exit;
        $key_m  = $h_key[$n];   # echo print_r($h_key,true).'$weather[$key_m]='.$weather[$key_m]; exit;       
#
        if ($key_t == false &&  $key_m == false) {continue;} // sensor # no moist or temp
#
        if ($key_t <> false)   # we have soil-temp sensor
             {  $tmp    = $weather[$key_t];  // get the temp value
                if ($weather['temp_units'] == 'F')
                      { $key = (int) convert_temp ($tmp,'F','C'); } 
                else  { $key = (int) $tmp;}    
                $result = $no_result_tmp;       // default eror values
                foreach ($stArr as $level => $texts)
                     {  if ($key < (int) $level)
                             {  $result = $texts;
                                break;}
                        continue;}  
                list ($color_b_t, $text_t) = explode ('|',$result);}
        else {  $color_b_t= $text_t = $tmp = '';} #echo '$result ='.$result.' $key='.$key.' $key_t='.$key_t.' $color_b_t='.$color_b_t.' $text_t='.$text_t; exit;
# now check moist sensor
        $color_b = $text = $hum  = '';
        $result = $no_result_mst; // default eror values
#
#  check if we have soil-moist sensor 
        if ($key_m <> false )
             {  $hum  = $weather[$key_m]; // get the moist (humidity) value
                $result = $no_result_mst;
                foreach ($smArr as $level => $texts)
                     {  if ($hum >= (int) $level)
                             {  $result = $texts;
                                break;}
                        continue; } // eo for each
                list ($color_b, $text) = explode ('|',$result);} // eo moist %  
# html for moist colomn                 
        if ($moists > 0) 
             {  $m_clr  = '';
                if ($color_b <> '') 
                     {  $m_clr  = ' color: '.$color_b.'; text-shadow: 1px 1px 20px '.$color_b.'; ';}
                $moist_col = PHP_EOL.'<td style=" font-weight: bold; '
                        .$color_background.' ' 
                        .$fnt_size
                        .$m_clr.'">'  
                        .$hum.'</td>'; }
# html temp colomn
        if ($temps > 0) // last colomn temp values
             {  $t_clr  = '';
                if ($color_b_t <> '') 
                     {  $t_clr  = ' color: '.$color_b_t.'; text-shadow: 1px 1px 20px '.$color_b_t.'; ';}
                $temp_col  =  PHP_EOL.'<td style=" font-weight: bold; '
                        .$color_background.' '
                        .$fnt_size
                        .$t_clr.'">'
                        .$tmp.'</td>';}
# middle colom description
        $name_txt       = ' ';
        if ($use_field_names)
             {  $name_txt       .= '<b>'.lang ($name[$n]).'</b>:';}
        $extra  = ' ';
        if (trim($text)    <> '') { $text   = lang (trim($text));}
        if (trim($text_t)  <> '') { $text_t = lang (trim($text_t));}
        if ($text  <> '' && $text_t <> '') 
             {  $extra .= ' | ';}  // add | between moist / temp texts
        $disp_text      = $text.$extra.$text_t;
        if ( $use_field_names) 
             {  $disp_text = $name_txt. ' '.$disp_text; }         
#
        echo PHP_EOL.'<tr style="'.$row_border.'">'
                .$moist_col
                .'<td>'.$disp_text.'</td>'
                .$temp_col.'</tr>'; 
}   // eo each sensor     
echo '
</table>'.PHP_EOL;
# ----------------   end of module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
