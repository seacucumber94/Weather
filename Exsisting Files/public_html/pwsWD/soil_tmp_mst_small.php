<?php  $scrpt_vrsn_dt  = 'soil_tmp_mst_small.php|01|2021-02-08|';  # typo error msg | release 2012_lts  
# 
# ----------------------------------    Settings
$tmp_key                = 'soil_tmp1';  // sensor to be used
$hum_key                = 'soil_mst1';
#
$head_description       = 'soil1';      // will be translated
#
$hum_l                  = 'Moisture';   // will be translated
$tmp_l                  = 'Temperature';// will be translated
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$box_style      = 'width: 55px; height: 55px; margin: 2px; padding-top:16px; color: black; border-width: 1px;';
#
# ------------------------------test values
#$weather[$tmp_key]  =  10; 
#$weather[$hum_key]  = 45;
# ------------------------------test values
#
# check if moist-field-data exists
if ($tmp_key <> false && !array_key_exists($tmp_key, $weather) ) {$tmp_key = false;} 
if ($hum_key <> false && !array_key_exists($hum_key, $weather) ) {$hum_key = false;} 
#
if ($tmp_key == false && $hum_key == false)
     {  echo '<small style="color: red;">Soil-sensor not available, script ends</small>';
        return;}
#
$how_much_text  = 0;
$left_block     = $right_block    = '';
$middle_hum_txt = $middle_tmp_txt = '';
#
if (!isset ($_REQUEST['id_blck'])) // at first loading set the block heading
     {  echo '<script> id_blck = id_blck + "mt_s";  document.getElementById(id_blck).innerHTML = "'.lang ($head_description).'" </script>'.PHP_EOL;}
#
if (!array_key_exists ('soilmoist_type',$weather) )
     {  $moist_unit = '%'; } else {$moist_unit = $weather['soilmoist_type'];}
#
if ($hum_key <> false && $moist_unit == '%')
     {  $smArr          = array();
#       moist level      text color  condition
        $result         = 
        $smArr['101']   =    'grey|black|ERROR no reading'; 
	$smArr['75']    = '#15A904|black|Saturated';  
	$smArr['50']    = '#15A904|black|Adequate' ;
	$smArr['25']    = '#FF0000|white|Irrigation needed';
	$smArr['0' ]    = '#9933CC|white|Dangerous Dry';} 
elseif ($hum_key <> false)
     {  $smArr          = array();                     
#       moist level      text color  condition 
	$result         =
	$smArr['240']   = 'grey|white|ERROR no reading';
	$smArr['101']   = '#9933CC|white|Dangerous Dry';
	$smArr['61']    = '#FF0000|white|Irrigation needed';
	$smArr['26']    = '#FF0000|white|Irrigation desired';
 	$smArr['11']    = '#15A904|black|Adequate' ;         
	$smArr['0']     = '#15A904|black|Saturated';  }    
#
if ($hum_key <> false) 
     {  $moist  = (int) $weather[$hum_key];
        foreach ($smArr as $level => $texts)
             {  if ($moist >= (int) $level)
                     {  $result = $texts;
                        break;}
                continue;}
        list ($color_b, $color_t, $text) = explode ('|',$result);
        $how_much_text++;  
        $left_block =   '<div class="PWS_div_left PWS_round" '
                .'style="' .$box_style.' float: left;  background-color: '.$color_b.'; color: '.$color_t.';">'
                .'<span style="font-size: 16px; ">'.$moist.'</span><b><small><br />'.$moist_unit.'</small></b>'.PHP_EOL.'</div>'.PHP_EOL;
        $middle_hum_txt = lang($hum_l).':<br />'.lang($text); }
#                
if ($tmp_key <> false) 
     {  $tempArr        = array (-18,-6,0,10,15,21,37);  
        $stArr = array();
        #                  text color condition          
	$stArr['-18']   = 'grey|red|ERROR no reading|';
	$stArr['-6']    = '#003399|white|Deep freeze|';
	$stArr['0']     = '#003399|white|Frost line|';
	$stArr['10']    = '#FF0000|white|Too cold to plant|';
	$stArr['15']    = '#15A904|black|Minimum growth|';
	$stArr['21']    = '#15A904|black|Optimal growth|';
	$stArr['37']    = '#15A904|black|Ideal growth|';
        $result         =   '|grey|red|ERROR no reading|';  // when to large  found
        $tmp            = (float) $weather[$tmp_key];  // get the temp value
        if ($weather['temp_units'] == 'F')
              { $key = (int) convert_temp ($tmp,'F','C'); } 
        else  { $key = (int) $tmp;}    
        foreach ($stArr as $level => $texts)
             {  if ($key < (int) $level)
                     {  $result = $texts;
                        break;}
                continue;}  
        list ($color_b_t, $color_t_t, $text_t) = explode ('|',$result);
        $how_much_text++;  
        $right_block  = '<div class="PWS_div_left PWS_round" '
                .'style="'.$box_style.' float: right; background-color: '.$color_b_t.'; color: '.$color_t_t.';">'
                .'<span style="font-size: 16px;">'.$tmp.'&deg;</span>'.PHP_EOL.'</div>'.PHP_EOL;
        $middle_tmp_txt = lang($tmp_l).':<br />'.lang($text_t);}
#
if ($how_much_text == 1)
    {   $style          = 'display: block; padding: 18px; font-size: 12px;'; 
        $extra_br       = '';} 
else {  $style          = 'display: block; padding: 4px;  font-size: 10px;';  
        $extra_br       = '<br />';} 
#
$middle_block =  '<span class=" " style="display: block;'.$style.'">'.$middle_hum_txt.$extra_br.$middle_tmp_txt.'</span>';
#
echo $left_block.$right_block.$middle_block;
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $color_b= '.$color_b.' $color_t= '.$color_t.' $text= '.$text.PHP_EOL;

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
