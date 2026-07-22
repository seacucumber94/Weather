<?php  $scrpt_vrsn_dt  = 'PWS_hwa.php|01|2022-03-28|';  # windlabel + missing values + PHP 8.1 +small apadtions | release 2012_lts
#-----------------------------------------------
#  generate a file for HWA network for those
#  users who do not have a weahter=program
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
   header('Content-type: text/plain; charset=UTF-8');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/plain; charset=UTF-8');
#-----------------------------------------------
$stck_lst       = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;    
#
# -------------------   load latest station data
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;     #   echo '<pre>'.print_r($weather,true); exit;
#
$hhmm                   = date ('Gi', time());
$no_data_after          = 2355; // Hour-minutes after wich no day data should be processed in history
$no_data_before         = 0005; // Same before
#
if (    $hhmm  >= $no_data_after 
     || $hhmm  <= $no_data_before)  
     {  header("HTTP/1.0 404 Not Found");
        die();}
#
# 
if (!isset ($temp_his) ) {$temp_his = $sql_u_temp;}
if (!isset ($baro_his) ) {$baro_his = $sql_u_baro;}
if (!isset ($wind_his) ) {$wind_his = $sql_u_wind;}
#
function check_hwa ($item)
     {  global $weather;
        if (array_key_exists ($item,$weather) )
             {  return $weather[$item];}
        else {  return '---';}
        }
#
$hwa_string = 'BOF
$date = "'.                     date('d/m/Y',$weather['datetime']) .'";
$time = "'.                     date('H:i',$weather['datetime']) .'";
$stationDate = "'.              date('d-m-Y',$weather['datetime']) .'";
$stationTime = "'.              date('H:i',$weather['datetime']) .'";
$utcdate = "'.                  gmdate('d-m-Y',$weather['datetime']) .'";
$utctime = "'.                  gmdate('H:i',$weather['datetime']) .'";
$tempUnit = "'.                 $weather["temp_units"].'"; # '.$temp_his.'
$humUnit = "%";
$barUnit = "'.                  $weather["barometer_units"].'";
$rainUnit = "'.                 $weather["rain_units"].'"; 
$rateUnit = "'.                 $weather["rain_units"].'/h";
$windUnit = "'.                 $weather["wind_units"].'"; 
$sunriseTime = "'.              date('H:i',$sunrs2) .'";
$sunsetTime = "'.               date('H:i',$suns2).'";

$outsideTemp = "'.              $weather['temp'].'";
$hiOutsideTemp = "'.            $weather['temp_high'].'";
$hiOutsideTempTime = "'.        $weather['temp_high_time'].'";
$lowOutsideTemp = "'.           $weather['temp_low'].'";
$lowOutsideTempTime = "'.       $weather['temp_low_time'].'";     
$hiMonthlyOutsideTemp = "'.     round( convert_temp   ($hist['temp']['HghV']['month'],$temp_his,$weather["temp_units"]),1).'";
$lowMonthlyOutsideTemp = "'.    round( convert_temp   ($hist['temp']['LowV']['month'],$temp_his,$weather["temp_units"]),1).'";
$hiYearlyOutsideTemp = "'.      round( convert_temp   ($hist['temp']['HghV']['year'], $temp_his,$weather["temp_units"]),1).'";
$lowYearlyOutsideTemp = "'.     round( convert_temp   ($hist['temp']['LowV']['year'], $temp_his,$weather["temp_units"]),1).'";

$outsideHumidity = "'.          $weather['humidity'].'";
$hiHumidity = "'.               $hist['humd']['HghV']['today'].'";
$hiHumidityTime = "'.           date('H:i',$hist['humd']['HghV_D']['today']).'";
$lowHumidity = "'.              $hist['humd']['LowV']['today'].'";
$lowHumidityTime = "'.          date('H:i',$hist['humd']['LowV_D']['today']).'";
$hiMonthlyHumidity = " '.       $hist['humd']['HghV']['month'].'";
$lowMonthlyHumidity = "'.       $hist['humd']['LowV']['month'].'";
$hiYearlyHumidity = "'.         $hist['humd']['HghV']['year'].'";
$lowYearlyHumidity = "'.        $hist['humd']['LowV']['year'].'";

$outsideDewPt = "'.             $weather['dewpoint'].'";
$hiDewpoint = "'.               round( convert_temp   ($hist['dewp']['HghV']['today'],$temp_his,$weather["temp_units"]),1).'";
$hiDewpointTime = "'.           date('H:i',$hist['dewp']['HghV_D']['today']).'";
$lowDewpoint = "'.              round( convert_temp   ($hist['dewp']['LowV']['today'],$temp_his,$weather["temp_units"]),1).'";
$lowDewpointTime = "'.          date('H:i',$hist['dewp']['LowV_D']['today']).'";
$hiMonthlyDewpoint = "'.        round( convert_temp   ($hist['dewp']['HghV']['month'],$temp_his,$weather["temp_units"]),1).'";
$lowMonthlyDewpoint = "'.       round( convert_temp   ($hist['dewp']['LowV']['month'],$temp_his,$weather["temp_units"]),1).'";
$hiYearlyDewpoint = "'.         round( convert_temp   ($hist['dewp']['HghV']['year'], $temp_his,$weather["temp_units"]),1).'";
$lowYearlyDewpoint = "'.        round( convert_temp   ($hist['dewp']['LowV']['year'], $temp_his,$weather["temp_units"]),1).'";

$windSpeed = "'.                $weather['wind_speed'].'";
$wind10Avg = "'.                $weather['wind_speed_avg'].'";
$hiWindSpeed = "'.              $weather['wind_speed_max'].'";
$hiWindSpeedTime = "'.          $weather['wind_speed_max_time'].'";
$hiMonthlyWindSpeed = "'.       round( convert_speed  ($hist['wind']['HghV']['month'],$wind_his,$weather["wind_units"]),1).'";
$hiYearlyWindSpeed = "'.        round( convert_speed  ($hist['wind']['HghV']['year'], $wind_his,$weather["wind_units"]),1).'";

$windDir = "'.                  $weather['wind_direction'].'";
$windDirection = "'.            windlabel ($weather['wind_direction'],false).'"; 

$windChill = "'.                $weather['windchill'].'";
$lowWindchill = "'.             $weather['windchill_low'].'";
$lowWindchillTime = "'.         $weather['windchill_low_time'].'";
$lowMonthlyWindchill = "---";
$lowYearlyWindchill = "---";

$outsideHeatIndex = "'.         $weather["heat_index"].'";
$hiHeatIndex = "---";
$hiHeatIndexTime = "---";
$hiMonthlyHeatIndex = "---";
$hiYearlyHeatIndex = "---";

$thw = "---";
$hiTHSWindex = "---";
$hiMonthlyTHSWindex = "---";
$hiYearlyTHSWindex = "---";

$barTrend = "'.                 $weather["barometer_trend_text"].'";

$barometer = "'.                $weather["barometer"].'";
$hiBarometer = "'.              $weather["barometer_max"].'";
$hiBarometerTime = "'.          $weather["barometer_max_time"].'";
$lowBarometer = "'.             $weather['barometer_min'].'
$lowBarometerTime = "'.         $weather['barometer_min_time'].'
$hiMonthlyBarometer = "'.       round(convert_baro   ($hist['baro']['HghV']['month'],$baro_his,$weather["barometer_units"]),1).'";
$lowMonthlyBarometer = "'.      round(convert_baro   ($hist['baro']['LowV']['month'],$baro_his,$weather["barometer_units"]),1).'";
$hiYearlyBarometer = "'.        round(convert_baro   ($hist['baro']['HghV']['year'], $baro_his,$weather["barometer_units"]),1).'";
$lowYearlyBarometer = "'.       round(convert_baro   ($hist['baro']['LowV']['year'], $baro_his,$weather["barometer_units"]),1).'";

$stormRain = "---";
$dailyRain = "'.                $weather["rain_today"].'";
$monthlyRain = "'.              $weather["rain_month"].'";
$totalRain = "'.                $weather["rain_year"].'";

$rainRate = "'.                 $weather["rain_rate"].'";
$hiRainRate = "---";
$hiRainRateTime = "---";
$hiMonthlyRainRate = "---";
$hiYearlyRainRate = "---";

$solarRad = "'.                 $weather["solar"].'";
$hiSolarRad = "'.               check_hwa ("solar_max").'";
$hiSolarRadTime = "'.           check_hwa ("solar_max_time").'";  
$hiMonthlySolarRad = "'.        $hist['solr']['HghV']['month'].'";
$hiYearlySolarRad = "'.         $hist['solr']['HghV']['year'].'";

$uv = "'.                       $weather["uv"].'";
$hiUV = "'.                     check_hwa ("uv_max").'";
$hiUVTime = "'.                 check_hwa ("uv_max_time").'";
$hiMonthlyUV = "'.              $hist['uvuv']['HghV']['month'].'";
$hiYearlyUV = "'.               $hist['uvuv']['HghV']['year'].'";
EOF';
echo $hwa_string;