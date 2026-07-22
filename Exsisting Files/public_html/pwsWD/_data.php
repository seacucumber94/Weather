<?php $scrpt_vrsn_dt  = '_data.php|01|2023-02-15|';  # release 2012_lts
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#-----------------------------------------------
$from_dir       = getcwd().'/'; # echo 'from_dir = '.$from_dir . PHP_EOL;
$live_dir       = __DIR__.'/';  # echo 'live_dir = '.$live_dir . PHP_EOL;
$result = chdir ($live_dir);
if ($result == false) {echo 'chdir failed for '.$live_dir.PHP_EOL;  return;}
if (array_key_exists ('no_sky',$_REQUEST) ) 
     {  $need_sky = false;}
elseif (!isset($need_sky)) 
     {  $need_sky = true;}
ob_start();
if ($need_sky) 
     {  $scrpt          = 'sky_block.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include $scrpt;
        $weather['iconXX']              = $iconXX;
        $weather['textXX']              = $textXX;}
else {  $scrpt         = 'PWS_livedata.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt;} //  PWS_livedata.php  if no sky-icon is needed
#
# check if pm values are present, if so calculate AQI
#
$pmvalues       = false;
$checks = array ('pm25_crnt1','pm25_crnt2','pm25_crnt3','pm25_crnt4','pm25_co2');
foreach ($checks as $check)
      { if (!array_key_exists('pm25_crnt1',$weather) ) 
             {  continue;}
        else {  $pmvalues       = true;  break;}
        }
if ($pmvalues== true)
     {  $scrpt          = 'AQ_shared.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL; 
        include_once  'AQ_shared.php'; 
        $weather['aqhi_type']   = $aqhi_type;
        if ($aqhi_type == 'epa') {$round_me = 0;} else {$round_me = 1;}
        if (array_key_exists('pm25_crnt1',$weather) )
             {  $weather['pm25_c_aqi1'] = round (pm25_to_aqi($weather['pm25_crnt1']), $round_me);
                $weather['pm25_a_aqi1'] = round (pm25_to_aqi($weather['pm25_24avg1']), $round_me);}
        if (array_key_exists('pm25_crnt2',$weather) )
             {  $weather['pm25_c_aqi2'] = round (pm25_to_aqi($weather['pm25_crnt2']), $round_me);
                $weather['pm25_a_aqi2'] = round (pm25_to_aqi($weather['pm25_24avg2']), $round_me);}
        if (array_key_exists('pm25_crnt3',$weather) )
             {  $weather['pm25_c_aqi3'] = round (pm25_to_aqi($weather['pm25_crnt3']), $round_me);
                $weather['pm25_a_aqi3'] = round (pm25_to_aqi($weather['pm25_24avg3']), $round_me);}
        if (array_key_exists('pm25_crnt4',$weather) )
             {  $weather['pm25_c_aqi4'] = round (pm25_to_aqi($weather['pm25_crnt4']), $round_me);
                $weather['pm25_a_aqi4'] = round (pm25_to_aqi($weather['pm25_24avg4']), $round_me);}
        if (array_key_exists('pm25_co2',$weather) )
             {  $weather['pm25_co2_aqi']= round (pm25_to_aqi($weather['pm25_co2']), $round_me);
                $weather['pm25_ao2_aqi']= round (pm25_to_aqi($weather['pm25_co2_24avg']), $round_me);}
        }
ob_end_clean();        
if (isset ($body_image) && $body_image <> '' && file_exists($body_image) )
     {  $weather['body_image']     = $body_image; }   #echo '<pre>'.print_r($lngsArr,true); exit;
$result = chdir ($from_dir);
if ($result == false) {echo 'chdir failed for '.$from_dir.PHP_EOL;  return;}
#
$windlabel      = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
		         'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
$compass                        = $windlabel[ (int) fmod((($weather['wind_direction'] + 11) / 22.5),16) ]; 
$weather['wind_compass']        = lang($compass);
$weather['humidity_high']       = $hist['humd']['HghV']['today'];
$weather['humidity_low']        = $hist['humd']['LowV']['today'];
$weather['ajaxdate']            = $weather['datetime'];
$weather['distanceunit']        = $distanceunit;
#
if (!array_key_exists ('soilmoist_type',$weather) )
     {  $weather['soilmoist_type'] = '%'; } 
#

#unset ($hist);
unset ($weather['loaded_from']);
#--------- test values ---
#$weather['rain_today'] = 0.05;  $weather['rain_month'] = 1.05; $weather['rain_year'] = 11.19;
#$weather['uv'] = 0.5;
#$weather['lightning'] = 14;
#$weather['lightningkm']=6; $weather['lightningmi']=4;
#echo '<pre>'.print_r($weather,true).'<pre>'; exit;
#--------- test values ---
if (array_key_exists ('lightning',$weather) &&  $weather['lightning'] == '')
     {  $weather['lightning']   = 0;}
$weather['lightningDT'] = '';
if (array_key_exists ('lightningtime',$weather) && (int) $weather['lightningtime'] <> 0 )
     {  $weather['lightningDT'] = "<span style='font-size: 12px;'>".date ($dateFormat.' '.$timeFormatShort,$weather['lightningtime']).'</span>';}  // $dateFormat  $timeFormat

ksort ($weather);
if (!isset ($_REQUEST['ajax']))
     {  return; }
#
$string = '';
foreach ($weather as $key => $value)
     {  if ($value === 'n/a') { continue; }
        $string .= 'ajaxVars["'.$key.'"] = "'.$value.'";'."\n";}
echo $string;
