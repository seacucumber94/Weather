<?php   $scrpt_vrsn_dt  = 'PWS_livedata.php|01|2023-10-31|'; # wl.com rain day month + invalid WF SUCCESS + cltrw-MH php 8 + uv solar hghV + barotrend ecowitt + lightning + ecowitt baro + 2-new amb light + cltrw extra + gust + cltrw + \n windows + ecowitt lightning pm + missing cltrw & ambient + new ecowitt fields + co2 + typo + ambient + rain year/month ecowitt a.o. + snow + weewx heat |release 2012_lts
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
#
if (!isset ($stck_lst) )  {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
$result = date_sun_info(time(), $lat, $lon);
$suns2  = $result['sunset'];
$sunrs2 = $result['sunrise'];
$now    = time();
#
if ($now >= $sunrs2 && $now <= $suns2) 
     {  $itsday = true;}  else {  $itsday = false; } 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $itsday ='.$itsday.PHP_EOL;  
#
$hist           = array();
$hist_file      = __DIR__.'/'.'_my_settings/history.txt'; # die ($hist_file);  ## 2020-03-05  problem cron-hostory
if (file_exists ($hist_file) ) 
     {  $hist   = unserialize (file_get_contents($hist_file)); } # echo '<pre>'.print_r ($hist,true);   ## 2020-03-05  problem cron-hostory
else {  $hist   = new_history(); }  ## 2020-03-05  problem cron-hostory

if (!isset ($read_net_data)) {$read_net_data = false; }  #echo '<pre>'.print_r ($hist,true); exit;
# 
# ---------------------- Ecowitt "Custom" upload 

if ($livedataFormat == 'ecoLcl') 
     {  $check_hist_HL  = true;
        $fr_temp        = 'F';
        $fr_baro        = 'inHg'; 
        $fr_wind        = 'mph'; 
        $fr_rain        = 'in';
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;
	$arr    = false;                #### 2020-12-24
	if (is_file ($livedata) )       
	     {  $arr            = unserialize(file_get_contents($livedata));}  #echo '<pre>'.$livedata.PHP_EOL.print_r($arr,true); # exit;
	if (!is_array($arr) )  
	     {  $error_txt      = 'error livedata '.$livedata.' not a correct '.$livedataFormat.' file'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24
	$recordDate             =       
	$weather["datetime"]    = strtotime ($arr['dateutc'].' UTC');  #2020-03-07 08:03:55
	$weather["date"]        = date($dateFormat, $recordDate);
	$weather["time"]        = date($timeFormat, $recordDate);
# exchange values for malfunctioning sensors
        $fr     = ''; # print_r($arr);
        if (isset ($fr) && $fr <> '') 
             {  $replace                = $arr['tempf'];
                $arr['tempf']           = $arr['temp'.$fr.'f'];
                $arr['temp'.$fr.'f']    = $replace;
                $replace                = $arr['humidity'];
                $arr['humidity']        = $arr['humidity'.$fr];
                $arr['humidity'.$fr.'f']= $replace; }  # print_r($arr); exit;          
#
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $arr['tempf'];
        if (!array_key_exists('baromrelin',$arr) && array_key_exists('barominrelin',$arr))
             {  $arr['baromrelin'] = $arr['barominrelin'];}
	$sql_barometer  = $arr['baromrelin'];  # or barominrelin   baromabsin
	$sql_raintoday  = $arr['dailyrainin'];
        if (isset ($arr['uv']))
             {  $sql_uv = round ( (float)  $arr['uv'],1);}
	else {  $sql_uv = '';}
	$sql_windgust   = $arr['windspeedmph'];
	$sql_windspeed  = $arr['windgustmph'];
        if (isset ($arr['solarradiation']))
             {  $sql_solar      = round ( (float) $arr['solarradiation'],1);}
        else {  $sql_solar      = '';}
 # https://www.wxforum.net/index.php?topic=25440.0  Thanks goes to Jáchym
        $humidity       = $arr['humidity'];    
        $temp_C         = convert_temp ($sql_temp,'F','C');
	$dewpoint       = round(((pow(($humidity/100), 0.125))*(112+0.9*$temp_C)+(0.1*$temp_C)-112),1);
	$sql_dewpoint   = convert_temp ($dewpoint,'C','F');
#
	$sql_rainrate   = $arr['rainratein'];
	$sql_direction  = $arr['winddir'];
	$sql_lightning  = 0;
# baro
        $from           = strtolower($fr_baro);
        $to             = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($sql_barometer ,$from,$to);
	$weather["barometer_trend_text"]= 'n/a';
	$weather["barometer_trend"]     = 'n/a'; 
	if (array_key_exists('pchange3',$arr) )                #### added for FOSHKplugin  2021-04-18
#	     {	 $weather["barometer_trend"]     = (float) $arr['pchange3'];}  # 2022-01-29
	     {   $weather["barometer_trend"]     = convert_baro ( (float) $arr['pchange3'] ,$from,$to);}
	if (array_key_exists('ptrend3',$arr) )
	     {  $trend  = (int) $arr['ptrend3'];
	        if      ($trend >= 2)  { $weather["barometer_trend_text"] = 'Rising Rapidly';}
	        elseif  ($trend >= 1)  { $weather["barometer_trend_text"] = 'Rising Slowly';}
	        elseif  ($trend <= -2) { $weather["barometer_trend_text"] = 'Falling Rapidly';}
	        elseif  ($trend <= -1) { $weather["barometer_trend_text"] = 'Falling Slowly';}
	        else                   { $weather["barometer_trend_text"] = 'Steady';}
	        }                                                #### added for FOSHKplugin  2021-04-18
        $weather["barometer_units"]     = $pressureunit;
# temperature type fields        
        $from                           = strtolower($fr_temp);
        $to                             = trim(strtolower($tempunit));
        $weather["temp_units"]          = $tempunit;
# indoor
        if (!isset ($arr['tempinf']) ) 
             {  $weather["temp_indoor"] = 'n/a'; }
        else {  $weather["temp_indoor"]         = convert_temp ($arr['tempinf'],$from,$to);
                $weather["temp_indoor_feel"]    = heatIndex($weather["temp_indoor"], $arr['humidityin']); } 
# dewpoint
	$weather["dewpoint"]            = convert_temp ($sql_dewpoint,$from,$to);
# temp out
        $value                          =
        $weather["temp"]                = convert_temp ($sql_temp,$from,$to);
        $weather["temp_feel"]           = heatIndex($weather["temp"], $arr['humidity']); ;
        $weather["wetbulb"] 		= 'n/a';
        $weather["heat_index"]          = 'n/a';
        $weather["windchill"]           = 'n/a';
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';	
	$weather["temp_trend"]          = 'n/a'; 
# extra temp fields
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'temp'.$n.'f'; # [temp1f] => 37.22
                if (array_key_exists ($key, $arr))
                     {  $name   = 'extra_tmp'.$n;
                        $value  = $arr[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}
                $key    = 'batt'.$n; 
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_th'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value === 1) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value; }
                } // eo extra temp fields
# extra temp WN34 fields  often soil, maybe for pool / pond also  use soil as default
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'tf_ch'.$n; # [tf_ch1] => 66.0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'soil_tmp'.$n;
                        $value  = $arr[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}                
                $key    = 'tf_batt'.$n;  # [tf_batt1] => 1.46
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_soilt'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value === 1) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value; }
                } // eo extra temp WN34 fields
# humidity
	$weather["humidity"]            = $arr['humidity'];
	$weather["humidity_indoor"]     = $arr['humidityin'];
	$weather["humidity_trend"]      = 'n/a';
# extra humidity fields
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'humidity'.$n; 
                if (array_key_exists ($key, $arr))
                     {  $name   = 'extra_hum'.$n;
                        $weather[$name] = (int) $arr[$key];}
                } // eo extra humidity fields
# moisture fields 
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'soilmoisture'.$n; 
                if (array_key_exists ($key, $arr))
                     {  $name   = 'soil_mst'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'soilbatt'.$n;
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_moist'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value <= 1.2) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}     
                } // moisture fields
# leaf wetness fields
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'leafwetness_ch'.$n; #  [leafwetness_ch1] => 0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'leaf_wetness'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'leaf_batt'.$n;  # [leaf_batt1] => 1.60
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_leaf'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value <= 1.2) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}     
                } // extra leaf wettness
# WH55  leak sensor                                             # 2021-09-08
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'leak_ch'.$n; # [leak_ch1] => 0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'leak_ch'.$n;
                        $weather[$name] = (int) $arr[$key];}
                $key    = 'leakbatt'.$n; # [leakbatt1] => 3
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_leak'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value <= 1)  {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}
                }  #leak sensor                                 # 2021-09-08       
# rain
        $from                           = strtolower($fr_rain);
        $to                             = trim(strtolower($rainunit));
	$weather["rain_rate"]           = 0;                                    #### 2021-03-28  new / missing fields
	$weather["rain_lasthour"]       = 0; 
	if (array_key_exists ('rainratein',$arr) ) 
	     {  $weather["rain_rate"]   = convert_precip ($arr['rainratein'],$from,$to,4); }
	elseif	(array_key_exists ('hourlyrainin',$arr) ) 
	     {  $weather["rain_rate"]   = convert_precip ($arr['hourlyrainin'],$from,$to,4); }
	if (array_key_exists ('hourlyrainin',$arr) ) 
	     {  $weather["rain_lasthour"]   = convert_precip ($arr['hourlyrainin'],$from,$to,4); }
	                                                                        #### 2021-03-28  new / missing fields
	$weather["rain_today"]          = convert_precip ($arr['dailyrainin'],$from,$to); 
	$weather["rain_yday"]           = convert_precip ($hist['rain']['HghV']['yday'],$rain_his,$to);
 	$weather["rain_month"]          = convert_precip ($arr['monthlyrainin'],$from,$to);
 	if (array_key_exists ('yearlyrainin',$arr))                                                      #### 2021-01-16
 	     {  $weather["rain_year"]   = convert_precip ($arr['yearlyrainin'],$from,$to);}
 	else {  $weather["rain_year"]   = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['year'],$rain_his,$to);} #### 2021-01-16
	$weather["rain_units"]          = $rainunit;  
# UV
	if (!isset ($arr['uv']) )
	     {  $weather["uv"]                  = 'n/a';}
	else {  $weather["uv"]                  = (float) $arr['uv'];}
# solar
	if (!isset ($arr['solarradiation']))
	     {  $weather["solar"]       = 'n/a';}
	else {  $weather["solar"]       = (float) $arr['solarradiation'];}
# lux
        $weather["lux"]                 = number_format((float)$weather["solar"]/0.0079*0.95299*1.0012,0, '.', '');
# windspeed type fields
        $from                           = strtolower($fr_wind);                
        $to                             = trim(strtolower($windunit));
	$weather["wind_speed"]          = 
	$weather["wind_speed_avg"]      = convert_speed ($arr['windspeedmph'],$from,$to);
        $weather["wind_run"]            = 'n/a';
	$weather["wind_units"]          = $windunit;
#  gust       
	$weather["wind_gust_speed"]     = convert_speed ($arr['windgustmph'],$from,$to);      
        $weather["wind_gust_speed_max"] = $weather["wind_gust_speed"];
        if (array_key_exists ('maxdailygust',$arr) )                            #### 2021-03-28  new / missing fields
              { $weather["wind_gust_speed_max"] = convert_speed ($arr['maxdailygust'],$from,$to); }
        if ($weather["wind_gust_speed"]  > $weather["wind_gust_speed_max"]) 
              { $weather["wind_gust_speed_max"] = $weather["wind_gust_speed"];} 
#
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = 
	$weather["wind_direction_avg"]  = $arr['winddir'];
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
#  AQ
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'pm25_ch'.$n; # [pm25_ch1] => 140.0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'pm25_crnt'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'pm25_avg_24h_ch'.$n; # [pm25_avg_24h_ch1] => 60.2
                if (array_key_exists ($key, $arr))
                     {  $name   = 'pm25_24avg'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'pm25batt'.$n; # [pm25batt1] => 3
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_pm'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value <= 1)  {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}
                }
#AQ combined CO2
        $keys   = array ('pm25_co2', 'pm10_co2', 'pm25_24h_co2',   'pm10_24h_co2',   'co2', 'co2_24h',   'humi_co2'); #  'co2_batt' 'tf_co2'
        $names  = array ('pm25_co2', 'pm10_co2', 'pm25_co2_24avg', 'pm10_co2_24avg', 'co2', 'co2_24avg', 'humi_co2'); #  'co2_batt' 'tf_co2'
        foreach ($keys as $nr => $key) 
             {  if (array_key_exists ($key, $arr)) 
                     {  $name   = $names[$nr];
                        $weather[$name] = (float) $arr[$key];}
                }
        if (array_key_exists ('tf_co2', $arr) )
             {  $weather['tf_co2']      = convert_temp ( (float) $arr['tf_co2'],'f', strtolower($weather["temp_units"]) );
                $weather['temp_co2']    = $weather['tf_co2'];}
        $key    = 'co2_batt';  #    [co2_batt] => 6
        if (array_key_exists ($key, $arr))
             {  $name   = 'batt_co2';
                $value  = (float) $arr[$key];
                $weather[$name.'_t'] = 'OK';
                if ($value <= 5) {$weather[$name.'_t'] = 'low';}
                $weather[$name.'_v'] = $value;}     
# lightning
        if (array_key_exists ('lightning',$arr)) {  #echo '<pre>'.print_r($hist,true); exit;
                if ( (int) $arr['lightning_time'] == 0 && array_key_exists('lightningmi',$hist) == false)
                     {  $weather['lightningtimeago']    = 
                        $weather['lightningtime']       =
                        $weather['lightningmi']         =
                        $weather['lightningkm']         =
                        $weather['lightning']           = 'n/a';}
                elseif ( (int) $arr['lightning_time'] == 0)
                     {  $weather['lightningtime']       = $hist['lightningtime'];
                        $weather['lightningmi']         = $hist['lightningmi'];
                        $weather['lightningkm']         = $hist['lightningkm'];
                        $weather['lightning']           = $hist['lightning'];
                        $weather['lightningtimeago']    = time() - $weather['lightningtime'];}
                else {  $weather['lightningtime']       = (int) $arr['lightning_time'];
                        $weather['lightningtimeago']    = time() - $arr['lightning_time'];
                        $weather['lightningmi']         = round (0.621371 * (float) $arr['lightning'], 0);        
                        $weather['lightningkm']         = (float) $arr['lightning']; #round ($weather['lightningmi'] / 0.621371);
                        $weather['lightning']           = $arr['lightning_num']; }      
        }
# Battery status
        $fo_dev = array ('wh65' => 'bit','wh68' => 'vlt','wh80' => 'vlt2', 'co2' => 'vlt2', 
                         'wh25' => 'bit','wh26' => 'bit', 'wh57' => 'vlt');
        foreach ($fo_dev as $string => $type) 
             {  $key    = $string.'batt'; # [wh65batt] => 0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_'.$string.'_v';
                        $value  = (float) $arr[$key];
                        $weather[$name] = $value;
                        $name   = 'batt_'.$string.'_t';
                        $weather[$name] = 'OK';
                        switch ($type) {
                            case 'bit': 
                                if ($value === 1)  {$weather[$name] = 'low';}
                                break;
                            case 'vlt':
                                if ($value <= 1.2) {$weather[$name] = 'low';}
                                break;
                            case 'vlt2':
                                if ($value <= 2.4) {$weather[$name] = 'low';}
                                break;
                            }
                        }
                }

# other  	
        $weather["station_mdl"]         = $arr['model'];
        $weather["swversion"]           = 'Ecowitt_Lcl';       
 }
#
# ------------------------------ Ambient Weather 
elseif ($livedataFormat == 'AWapi') 
     {  if ($read_net_data) 
             {  $non_cron       = 90;
                $scrpt          = 'PWS_load_files.php'; 
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
                include_once $scrpt;}
        $content = $json= false;
	if (is_file ($livedata) )               #### 2020-12-24
             {  $content = file_get_contents($livedata); 
                if (substr('{"error":"above-user-rate-limit"}',0,10) == substr($content,0,10))
                     {  $livedata= $livedata.'old';
                        $content = false;
                        if (is_file ($livedata) )       #### 2020-12-24  
                             {  $content = file_get_contents($livedata); }
                        if (substr('{"error":"above-user-rate-limit"}',0,10) == substr($content,0,10))
                             {  die ('no valid data found, please retry later') ; }        
                        }
                }
        $check_hist_HL  = true;
        if ($content <> false)
             {  $json = json_decode($content, true);} 
        if ($json === NULL || $content == false) 
             {  $error_txt      = 'error livedata '.$livedata.' not a correct '.$livedataFormat.' file'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata;
        $fr_temp = "F";      
        $fr_baro = "inHg";  
        $fr_wind = "mph";     
        $fr_rain = "in";  
        $item   = array();
        if (count ($json) == 1) 
             {  $item   = $json[0]['lastData'];}
        else {  foreach($json as $arr)    
                     {  if ($arr['macAddress'] <> $aw_did) {continue;}
                        $item   = $arr['lastData']; # echo '<pre>'.print_r($item,true); exit;
                        break;}
        }
        if ($item['dateutc'] > (time() + 100*24*3600) ) {$item['dateutc'] = $item['dateutc'] / 1000;}
        $recordDate             = 
	$weather["datetime"]    = $item['dateutc'];
	$weather["date"]        = date($dateFormat, $recordDate);
	$weather["time"]        = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $item['tempf'];
	$sql_barometer  = $item['baromrelin'];
	$sql_raintoday  = $item['dailyrainin'];
        if (isset ($item['uv']))
             {  $sql_uv = (int) $item['uv'];}
	else {  $sql_uv = '';}
	$sql_windgust   = $item['windgustmph'];
	$sql_windspeed  = $item['windspeedmph'];
        if (isset ($item['solarradiation']))
             {  $sql_solar      = (int) $item['solarradiation'];}
        else {  $sql_solar      = '';}
	$sql_dewpoint   = $item['dewPoint'];
	$sql_rainrate   = $item['hourlyrainin'];
	$sql_direction  = $item['winddir'];  # $item['winddir_avg2m']; #### 2021-03-30
	$sql_lightning  = '';
# baro
        $from   = strtolower($fr_baro);
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($sql_barometer ,$from,$to);
	$weather["barometer_trend"]     = 'n/a';
	$weather["barometer_trend_text"]= 'n/a';
        $weather["barometer_units"]     = $pressureunit;
# temperature type fields        
        $from   = strtolower($fr_temp);
        $to     = trim(strtolower($tempunit));
        $weather["temp_units"]          = $tempunit;
# indoor
        if (!isset ($item['tempinf']) ) 
             {  $weather["temp_indoor"] = 'n/a'; }
        else {  $weather["temp_indoor"]         = convert_temp ($item['tempinf'],$from,$to);}
#   some devices have infoor feel data      #### 2020-10-18
        if (!isset ($item['feelsLikein']) ) 
             {  $num                            = heatIndex($item['tempinf'], $item['humidityin']); // must set temp_units first
                $weather["temp_indoor_feel"]    = $num; } #convert_temp ($num,$from,$to);}  
        else {  $weather["temp_indoor_feel"]    = convert_temp ($item['feelsLikein'],$from,$to);}   #### 2020-10-18
# dewpoint
	$weather["dewpoint"]            = convert_temp ($sql_dewpoint,$from,$to);
# temp out
        $value                          =
        $weather["temp"]                = convert_temp ($sql_temp,$from,$to);
        $weather["temp_feel"]           = convert_temp ($item['feelsLike'],$from,$to);
        $weather["wetbulb"] 		= 'n/a';
        $weather["heat_index"]          = 'n/a';
        $weather["windchill"]           = 'n/a';
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';
	$weather["temp_trend"]          = 'n/a'; 
# extra temp fields
        for ($n = 1; $n <= 10 ; $n++)
             {  $key    = 'temp'.$n.'f'; # [temp1f] => 37.22
                if (array_key_exists ($key, $item))
                     {  $name   = 'extra_tmp'.$n;
                        $value  = $item[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}
                $key    = 'batt'.$n; 
                if (array_key_exists ($key, $item))
                     {  $name   = 'batt_th'.$n;
                        $value  = (float) $item[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value === 1) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}
                }
# soiltemp fields 
        for ($n = 1; $n <= 10 ; $n++)
             {  $key    = 'soiltemp'.$n.'f';
                if (array_key_exists ($key, $item))
                     {  $name   = 'soiltmp'.$n;
                        $value  = (float) $item[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}
                $key    = 'soilbatt'.$n; # [?????batt1] => 3
                if (array_key_exists ($key, $item))
                     {  $name   = 'batt_soilt'.$n;
                        $value  = (float) $item[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value === 1) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}     
                }
# humidity
	$weather["humidity"]            = $item['humidity'];
	$weather["humidity_indoor"]     = $item['humidityin'];
	$weather["humidity_trend"]      = 'n/a';
# extra humidity fields
        for ($n = 1; $n <= 10 ; $n++)
             {  $key    = 'humidity'.$n; # [humidity1] => 98
                if (array_key_exists ($key, $item))
                     {  $name   = 'extra_hum'.$n;
                        $weather[$name] = (int) $item[$key];}
                }
# moisture fields 
        for ($n = 1; $n <= 8 ; $n++)
             {  $key    = 'soilhum'.$n; # soilhum1...soilhum10 - Temperature 1...10, %  #2021-09-07
                if (array_key_exists ($key, $item))
                     {  $name   = 'soil_mst'.$n;
                        $weather[$name] = (float) $item[$key];}
                $key    = 'battsm'.$n; # [battsm1] => 1   #2021-09-07
                if (array_key_exists ($key, $item))
                     {  $name   = 'batt_moist'.$n;
                        $value  = (float) $item[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value < 1) {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}     
                }
# rain
        $from                           = strtolower($fr_rain);
        $to                             = trim(strtolower($rainunit));
	$weather["rain_rate"]           = convert_precip ($item['hourlyrainin'],$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($item['dailyrainin'],$from,$to); 
	$weather["rain_yday"]           = convert_precip ($hist['rain']['HghV']['yday'],$rain_his,$to);
 	$weather["rain_month"]          = convert_precip ($item['monthlyrainin'],$from,$to); 
 	if (array_key_exists ('yearlyrainin',$arr))                                             #### 2021-01-24                                              
 	     {  $weather["rain_year"]   = convert_precip ($arr['yearlyrainin'],$from,$to);}
 	else {  $weather["rain_year"]   = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['year'],$rain_his,$to);} #### 2021-01-24
	$weather["rain_lasthour"]       = convert_precip ($item['hourlyrainin'],$from,$to); 
	$weather["rain_units"]          = $rainunit;  
 # UV
	if (!isset ($item['uv']) )
	     {  $weather["uv"]          = 'n/a';}
	else {  $weather["uv"]          = (float) $item['uv'];}
 # solar
	if (!isset ($item['solarradiation']))
	     {  $weather["solar"]       = 'n/a';}
	else {  $weather["solar"]       = (float) $item['solarradiation'];}
# lux
        $weather["lux"]                 = number_format((float)$weather["solar"]/0.0079*0.95299*1.0012,0, '.', '');
# windspeed type fields
        $from   = strtolower($fr_wind);                
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = 
	$weather["wind_speed_avg"]      = convert_speed ($item['windspeedmph'],$from,$to); #### 2021-03-30
	if (array_key_exists ('windspdmph_avg10m', $item) )                                #### 2021-03-30
	     {  $weather["wind_speed_avg"]      = convert_speed ($item['windspdmph_avg10m'],$from,$to);}
	elseif ( array_key_exists ('windspdmph_avg2m', $item)  )
	     {  $weather["wind_speed_avg"]      = convert_speed ($item['windspdmph_avg2m'],$from,$to);}
        $weather["wind_run"]            = 'n/a';
	$weather["wind_units"]          = $windunit;
#  gust       
	$weather["wind_gust_speed"]     = convert_speed ($item['windgustmph'],$from,$to);
        $weather["wind_gust_speed_max"] = convert_speed ($item['maxdailygust'],$from,$to);   
        if ($weather["wind_gust_speed"]  > $weather["wind_gust_speed_max"]) 
              { $weather["wind_gust_speed_max"] = $weather["wind_gust_speed"];} 
#
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $item['winddir'];
	if (array_key_exists ('winddir_avg10m', $item) )   #### 2021-03-30
	     {  $weather["wind_direction_avg"]  = $item['winddir_avg10m'];}
	elseif (array_key_exists ('winddir_avg2m', $item) )             # 2021-09-07
	     {  $weather["wind_direction_avg"]  = $item['winddir_avg2m'];}
	else {  $weather["wind_direction_avg"]  = $item['winddir'];}
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
#  AQ
        $key    = 'pm25'; 
        $n      = '1';
        if (array_key_exists ($key, $item))
             {  $name   = 'pm25_crnt'.$n;
                $weather[$name] = (float) $item[$key];}
        $key    = 'pm25_24h';  
        if (array_key_exists ($key, $item))
             {  $name   = 'pm25_24avg'.$n;
                $weather[$name] = (float) $item[$key];}
        $key    = 'batt_25';
        if (array_key_exists ($key, $item))
             {  $name   = 'batt_pm'.$n;
                $value  = (int) $item[$key];
                $weather[$name.'_t'] = 'OK';
                if ($value == 1)  {$weather[$name.'_t'] = 'low';}
                $weather[$name.'_v'] = $value;}
        $key    = 'pm25_in';
        $n      = '2'; 
        if (array_key_exists ($key, $item))
             {  $name   = 'pm25_crnt'.$n;
                $weather[$name] = (float) $item[$key];}
        $key    = 'pm25_in_24h';  
        if (array_key_exists ($key, $item))
             {  $name   = 'pm25_24avg'.$n;
                $weather[$name] = (float) $item[$key];}
        $key    = 'batt_25_in';        # ????????
        if (array_key_exists ($key, $item))
             {  $name   = 'batt_pm'.$n;
                $value  = (int) $item[$key];
                $weather[$name.'_t'] = 'OK';
                if ($value == 1)  {$weather[$name.'_t'] = 'low';}
                $weather[$name.'_v'] = $value; }
# lightning
        if ( array_key_exists ('lightning_num',$item) 
          || array_key_exists ('lightning_day',$item) ) 
             {  if ( (int) $item['lightning_time'] == 0 && !array_key_exists('lightningtime',$hist) )
                     {  $weather['lightningtimeago']    = 
                        $weather['lightningtime']       =
                        $weather['lightningmi']         =
                        $weather['lightningkm']         =
                        $weather['lightning']           = 'n/a';}
                elseif ( (int) $item['lightning_time'] == 0)
                     {  $weather['lightningtime']       = $hist['lightningtime'];
                        $weather['lightningmi']         = $hist['lightningmi'];
                        $weather['lightningkm']         = $hist['lightningkm'];
                        $weather['lightning']           = $hist['lightning'];
                        $weather['lightningtimeago']    = time() - $weather['lightningtime'];}
                else {  $weather['lightningtime']       = (int) $item['lightning_time'] / 1000;
                        $weather['lightningtimeago']    = time() - $weather['lightningtime'];   # 2021-09-07
                        $weather['lightningmi']         = round ( (float) $item['lightning_distance']);
                        $weather['lightningkm']         = round ( (float) $item['lightning_distance'] / 0.621371 )  ; #round ($weather['lightningmi'] / 0.621371);
                        if ( array_key_exists ('lightning_num',$item) )
                             {  $weather['lightning']           = $item['lightning_num']; }
                        else {  $weather['lightning']           = $item['lightning_day']; }
                        }
                $key = 'batt_lightning';
                if (array_key_exists ($key, $item))
                     {  $name   = 'batt_light';
                        $value  = (int) $item[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value == 0)  {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value; }
               # print_r (  $item); print_r (  $weather); exit;  
        }
# Battery status outside
        $key    = 'battout';
        if (array_key_exists ($key, $item))
             {  $name   = $key.'_v';
                $value  = (float) $item[$key];
                $weather[$name] = $value;
                $name   = $key.'_t';
                $weather[$name] = 'OK';
                if ($value == 1)  {$weather[$name] = 'low';}
        }
# other  	
        $weather["swversion"]           = 'AWN-API';
}
#
# ---------------------------------- WeatherFlow
elseif ($livedataFormat == 'wf') 
     {  if ($read_net_data) 
             {  $non_cron       = 90;
                $scrpt          = 'PWS_load_files.php'; 
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
                include_once $scrpt;}
        $check_hist_HL  = true;
        if (!is_file ($livedata) )      #### 2020-12-24   
             {  $error_txt      = 'error livedata '.$livedata.': file not exists'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;} 
	$content = file_get_contents($livedata);
        $json = json_decode($content, true);
        if (    !is_array($json) 
             || !array_key_exists ('obs',$json) 
             || count($json['obs']) == 0  )        # 2023-07-18
             {  $error_txt      = 'error livedata '.$livedata.' not a correct or an incomplete '.$livedataFormat.' file'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata;
        $fr_temp = "C";      #$json['station_units']['units_temp'];
        $fr_baro = "hPa";    #$json['station_units']['units_pressure'] ;  
        $fr_wind = "m/s";    #$json['station_units']['units_wind'];        
        $fr_rain = "mm";     #$json['station_units']['units_precip']; 
        $item   = array();
        foreach($json['obs'] as $item) {break;}
        $recordDate             = 
	$weather["datetime"]    = $item['timestamp'];
	$weather["date"]        = date($dateFormat, $recordDate);
	$weather["time"]        = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $item['air_temperature'];
	$sql_barometer  = $item['sea_level_pressure'];
	$sql_raintoday  = $item['precip_accum_local_day'];
        if (isset ($item['uv']))
             {  $sql_uv = (int) $item['uv'];}
	else {  $sql_uv = '';}
	$sql_windgust   = $item['wind_gust'];
	$sql_windspeed  = $item['wind_avg'];
        if (isset ($item['solar_radiation']))
             {  $sql_solar      = (int) $item['solar_radiation'];}
        else {  $sql_solar      = '';}
	$sql_dewpoint   = $item['dew_point'];
	$sql_rainrate   = $item['precip_accum_last_1hr'];
	if (array_key_exists('wind_direction', $item) )
	     {  $sql_direction  = $item['wind_direction'];}
	else {  $sql_direction  = '0';}
	$sql_lightning  = $item['lightning_strike_count'];
# baro
        $from           = strtolower($fr_baro);
        $to             = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($sql_barometer ,$from,$to);
	$weather["barometer_trend"]     = 'n/a';
	if (array_key_exists ('pressure_trend',$item) )
	     {  $weather["barometer_trend_text"]= $item['pressure_trend'];}
	else {  $weather["barometer_trend_text"]= 'n/a';}
        $weather["barometer_units"]     = $pressureunit;
# temperature type fields        
        $from           = strtolower($fr_temp);
        $to             = trim(strtolower($tempunit));
	$weather["temp_indoor"]         = 'n/a';
	$weather["temp_indoor_feel"]    = 'n/a';
	$weather["dewpoint"]            = convert_temp ($sql_dewpoint,$from,$to);
#
        $weather["temp"]                = convert_temp ($sql_temp,$from,$to);
        $weather["temp_feel"]           = convert_temp ($item['feels_like'],$from,$to);
        $weather["wetbulb"] 		= convert_temp ($item['wet_bulb_temperature'],$from,$to);
        $weather["heat_index"]          = convert_temp ($item['heat_index'],$from,$to);
        $weather["windchill"]           = convert_temp ($item['wind_chill'],$from,$to);
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';
	$weather["temp_trend"]          = 'n/a'; 
	$weather["temp_units"]          = $tempunit;          
 # humidity
	$weather["humidity"]            = $item['relative_humidity'];
	$weather["humidity_indoor"]     = 'n/a';
	$weather["humidity_trend"]      = 'n/a';
# rain
        $from           = strtolower($fr_rain);
        $to             = trim(strtolower($rainunit));
	$weather["rain_rate"]           = convert_precip ($item['precip'],$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($item['precip_accum_local_day'],$from,$to); 
        if (array_key_exists ('precip_accum_local_yesterday',$item) )
             {	$weather["rain_yday"]   = convert_precip ($item['precip_accum_local_yesterday'],$from,$to); }
        else {  $weather["rain_yday"]   = 0;}
 	$weather["rain_month"]          = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['month'],$rain_his,$to);    #### 2021-01-16
	$weather["rain_year"]           = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['year'],$rain_his,$to);     #### 2021-01-16
	$weather["rain_lasthour"]       = convert_precip ($item['precip_accum_last_1hr'],$from,$to);  ## 2019-11-1§5
	$weather["rain_units"]          = $rainunit;  

 # UV /solar
	$weather["uv"]                  = $item['uv'];
	$weather["solar"]               = $item['solar_radiation'];
# lux	
        $weather["lux"]                 = $item['brightness'];
        $weatherflow['time']            = $item['timestamp'];	        	
        $weatherflow['solar']           = $item['solar_radiation'];
        $weatherflow['uv']              = $item['uv'];
        $weatherflow['lux']             = $item['brightness']; 

# windspeed type fields
        $from   = strtolower($fr_wind);                
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($item['wind_avg'],$from,$to);
	$weather["wind_speed_avg"]      = $weather["wind_speed"];
# gust         
	$weather["wind_gust_speed"]     = convert_speed ($item['wind_gust'],$from,$to);
        $weather["wind_run"]            = 'n/a';
	$weather["wind_units"]          = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction_avg"]  =
	$weather["wind_direction"]      = 'n/a';
	if (array_key_exists('wind_direction', $item) )
	     {  $weather["wind_direction"]      = $item['wind_direction'];}
	$weather["wind_direction_avg"]  = 'n/a';
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# lightning        
        if (!array_key_exists('lightning_strike_last_epoch',$item ) )    
             {  $weatherflow['lastlightningtime']       = 0;}
        else {  $weatherflow['lastlightningtime']       = $item['lightning_strike_last_epoch'];}
        if (!array_key_exists('lightning_strike_last_distance',$item ) ) 
             {  $weatherflow['lightningdistance']       = 0;}
        else {  $weatherflow['lightningdistance']       = $item['lightning_strike_last_distance'];}
        if (!array_key_exists('lightning_strike_count',$item ) ) 
             {  $weatherflow['lightning'] = 0;}
        else {  $weatherflow['lightning'] = $item['lightning_strike_count'];}
        if (!array_key_exists('lightning_strike_count_last_3hr',$item ) ) 
             {  $weatherflow['lightning3hr'] = 0;}
        else {  $weatherflow['lightning3hr'] = $item['lightning_strike_count_last_3hr'];}
        
        $weatherflow['dist_units']      = $json['station_units']['units_distance'];
        if ( trim(strtolower($weatherflow['dist_units'])) <> 'km')
             {  $toKM   = 1.609344; $toMI   = 1;}
        else {  $toKM   = 1;        $toMI   = 0.621371;}
        $distance       = (float) $weatherflow['lightningdistance'];
        $weatherflow['lightningdistance']   = round ($distance , 0);
        $weatherflow['lightningdistanceKM'] = round ($distance * $toKM, 0);
        $weatherflow['lightningdistanceMI'] = round ($distance * $toMI, 0);
        $weather['lightningtime']       = $weatherflow['lastlightningtime'];
        $weather['lightningtimeago']    = time() - $weatherflow['lastlightningtime'];
        $weather['lightningkm']         = $weatherflow['lightningdistanceKM'];
        $weather['lightningmi']         = $weatherflow['lightningdistanceMI'];        
        $weather['lightning']           = $weatherflow['lightning'];
        if ($weather['lightningtime'] == 0 && $weather['lightning'] == 0 && array_key_exists('lightningtime',$hist) )
             {  $weather['lightningtime']       = $hist['lightningtime'];
                $weather['lightningmi']         = $hist['lightningmi'];
                $weather['lightningkm']         = $hist['lightningkm'];
                $weather['lightning']           = $hist['lightning'];
                $weather['lightningtimeago']    = time() - $weather['lightningtime'];}
# other  	
        $weather["swversion"]           = 'WF-API';
}
#
# -------------------------  Weather Underground
elseif ($livedataFormat == 'wu') 
     {  if ($read_net_data) 
             {  $non_cron       = 90;
                $scrpt          = 'PWS_load_files.php'; 
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
                include_once $scrpt;}
        $check_hist_HL  = true;
        if ($wu_unit == 'e') // only imperial and metric supported for day/yday
             {  $wu_data        = 'imperial';
                $fr_temp        = 'F';
                $fr_baro        = 'inHg'; 
                $fr_wind        = 'mph'; 
                $fr_rain        = 'in';}
        else {  $fr_temp        = 'C';
                $fr_baro        = 'hPa'; 
                $fr_wind        = 'km/h'; 
                $fr_rain        = 'mm';
                $wu_data        = 'metric';}   
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;       
        $arr    = false;                #### 2020-12-24	
        if (is_file ($livedata) ) 
             {  $json           = file_get_contents($livedata);
                $arr            = json_decode($json, true);}  # echo '<pre>'.print_r($arr,true); exit;
	if (!is_array($arr) || !array_key_exists('observations',$arr) || !array_key_exists(0,$arr['observations']) )
	     {  $error_txt      = 'error livedata '.$livedata.' not a correct '.$livedataFormat.' file'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24	
	$ccn            = $arr['observations'][0]; # echo '<pre>'.print_r($ccn,true); exit;
        $recordDate             = 
	$weather["datetime"]    = $ccn['epoch'];
	$weather["date"]        = date($dateFormat, $recordDate);
	$weather["time"]        = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $ccn[$wu_data]['temp'];
	$sql_barometer  = $ccn[$wu_data]['pressure'];
	$sql_raintoday  = $ccn[$wu_data]['precipTotal'];
        if (isset ($ccn['uv']))
             {  $sql_uv = (int) $ccn['uv'];}
	else {  $sql_uv = '';}
	$sql_windgust   = $ccn[$wu_data]['windGust'];
	$sql_windspeed  = $ccn[$wu_data]['windSpeed'];
        if (isset ($ccn['solarRadiation']))
             {  $sql_solar      = (int) $ccn['solarRadiation'];}
        else {  $sql_solar      = '';}
	$sql_dewpoint   = $ccn[$wu_data]['dewpt'];
	$sql_rainrate   = $ccn[$wu_data]['precipRate'];
	$sql_direction  = $ccn['winddir'];
	$sql_lightning  = 0;
# baro
        $from   = strtolower($fr_baro);
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($ccn[$wu_data]['pressure'],$from,$to);
	$weather["barometer_trend"]     = 'n/a';
	$weather["barometer_trend_text"]= 'n/a';
        $weather["barometer_units"]     = $pressureunit;
# temperature type fields        
        $from   = strtolower($fr_temp);
        $to     = trim(strtolower($tempunit));
        $weather["temp_units"]          = $tempunit;
	$weather["temp_indoor"]         = 'n/a';
	$weather["temp_indoor_feel"]    = 'n/a';
	$weather["dewpoint"]            = convert_temp ($ccn[$wu_data]['dewpt'],$from,$to);
# temp
        $value                          =
        $weather["temp"]                = convert_temp ($ccn[$wu_data]['temp'],$from,$to);
        $weather["temp_feel"]           = heatIndex($value, $ccn['humidity']); // must set temp_units first
        $weather["wetbulb"] 		= 'n/a';
        $weather["heat_index"]          = convert_temp ($ccn[$wu_data]['heatIndex'],$from,$to);
	$weather["windchill"]           = convert_temp ($ccn[$wu_data]['windChill'],$from,$to);
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';
	$weather["temp_trend"]          = 'n/a'; 
	$weather["temp_units"]          = $tempunit;
# humidity
	$weather["humidity"]            = $ccn['humidity'];
	$weather["humidity_indoor"]     = 'n/a';
	$weather["humidity_trend"]      = 'n/a';
# rain
        $from   = strtolower($fr_rain);
        $to     = trim(strtolower($rainunit));
	$weather["rain_rate"]           = convert_precip ($ccn[$wu_data]['precipRate'],$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($ccn[$wu_data]['precipTotal'],$from,$to); 	
	$weather["rain_yday"]           = convert_precip ($hist['rain']['HghV']['yday'],$rain_his,$to); 
 	$weather["rain_month"]          = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['month'],$rain_his,$to); #### 2021-01-16
	$weather["rain_year"]           = $weather["rain_today"]  + convert_precip ($hist['rain']['HghV']['year'],$rain_his,$to);  #### 2021-01-16
	$weather["rain_lasthour"]       = 'n/a'; 
	$weather["rain_units"]          = $rainunit;  
# UV /solar
        $weather["uv"]                  = $ccn['uv'];
# solar
	$weather["solar"]               = $ccn['solarRadiation'];
#lux	
	$weather["lux"]                 = number_format((float)$weather["solar"]/0.0079*0.95299*1.0012,0, '.', '');
# windspeed type fields
        $from   = strtolower($fr_wind);                
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($ccn[$wu_data]['windSpeed'],$from,$to);
	$weather["wind_speed_avg"]      = $weather["wind_speed"];
# gust
	$weather["wind_gust_speed"]     = convert_speed ($ccn[$wu_data]['windGust'],$from,$to);	
        $weather["wind_run"]            = 'n/a';
	$weather["wind_units"]          = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $ccn['winddir'];
	$weather["wind_direction_avg"]  = $ccn['winddir'];
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# other  	
        $weather["swversion"]           = 'WU-api';
}
#
# ------------------   Davis WeatherLink network APIv2 no min-max !
elseif ($livedataFormat == 'DWL_v2api')
     {  $check_hist_HL  = true;
        if ($read_net_data) 
             {  $non_cron       = 90;
                $scrpt          = 'PWS_load_files.php'; 
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
                include_once $scrpt; }
#  load data
        $values = array();
        $double = 0;
	$arr    = false;                #### 2020-12-24
	if (is_file ($livedata) )       
	     {  $arr    = json_decode(file_get_contents($livedata), true);}
	if (!is_array($arr) || !array_key_exists ('sensors', $arr) )
	     {  $error_txt      = 'error livedata '.$livedata.' not a correct '.$livedataFormat.' file'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24	
        $recordDate     = $arr['generated_at'];
        foreach ($arr['sensors'] as $arr2)
             {  #echo __LINE__.print_r($arr2,true); 
                foreach ($arr2['data'] as $arr3)
                     {  #echo __LINE__.' '.print_r($arr3,true);  exit;
                        foreach ($arr3 as $key => $value)
                             {  if (array_key_exists ($key,$values) ) 
                                     {  $double++;
                                        $key    = '_'.$key.'_'.$double;}
                                $values[$key]   = $value;}
                        } // foreach data
                } // eo for each sensor
        unset ($arr);
        ksort($values);
#
        $fr_temp = 'F';
        $fr_baro = "inHg";
	$fr_wind = "mph";
	$fr_rain = "in";  
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;
	$weather["datetime"]            = $recordDate;
	$weather["date"]                = date($dateFormat, $recordDate);
	$weather["time"]                = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $values['temp'];
	$sql_barometer  = $values['bar_sea_level'];
# 2023-10-07
        if     ( array_key_exists ('rainfall_daily_in', $values) )
	     {  $sql_raintoday  = $values['rainfall_daily_in']; }
	elseif ( array_key_exists ('rainfall_day_in', $values) )
	     {  $sql_raintoday  = $values['rainfall_day_in']; }
	else {	$sql_raintoday  =  '0';}
# 2023-10-07
	if (isset ($values['uv_index']))
	     {  $sql_uv = (int) $values['uv_index'];}
	else {  $sql_uv = '';}
	$sql_windgust   = $values['wind_speed_hi_last_10_min'];
	$sql_windspeed  = $values['wind_speed_avg_last_10_min'];
	$sql_solar      = (int) $values['solar_rad'];
	$sql_dewpoint   = $values['dew_point'];
	$sql_rainrate   = $values['rain_rate_last_in'];
	$sql_direction  = $values['wind_dir_at_hi_speed_last_2_min'];
	$sql_lightning  = 0;
# baro
        $from   = strtolower($fr_baro);
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($sql_barometer ,$from,$to);
	$weather["barometer_trend"]     = convert_baro ($values['bar_trend'] ,$from,$to);
	$weather["barometer_trend_text"]= 'n/a';
        $weather["barometer_units"]     = $pressureunit;
# temperature type fields        
        $from   = strtolower($fr_temp);
        $to     = trim(strtolower($tempunit));
        $weather["temp_units"]          = $tempunit;
        if (!isset ($values['temp_in']) ) 
             {  $weather["temp_indoor"]         =
                $weather["temp_indoor_feel"]    =  'n/a'; }
        else {  $weather["temp_indoor"]         = convert_temp ($values['temp_in'],$from,$to);	
                $weather["temp_indoor_feel"]    = convert_temp ($values['heat_index_in'],$from,$to);}
# dewpoint
	$weather["dewpoint"]            = convert_temp ($sql_dewpoint,$from,$to);
# temp out
        $value                          =
        $weather["temp"]                = convert_temp ($sql_temp,$from,$to);
        $weather["temp_feel"]           = convert_temp ($values['heat_index'],$from,$to);
        $weather["wetbulb"] 		= 'n/a';
        $weather["heat_index"]          = $weather["temp_feel"];
        $weather["windchill"]           = convert_temp ($values['wind_chill'],$from,$to);
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';
	$weather["temp_trend"]          = 'n/a'; 
	$weather["thw_index"]           = convert_temp ($values['thw_index'],$from,$to);
# extra temp fields
#        for ($n = 1; $n <= 10 ; $n++) {}
# soiltemp fields 
#        for ($n = 1; $n <= 10 ; $n++) {}
# humidity
	$weather["humidity"]            = $values['hum'];
	$weather["humidity_indoor"]     = $values['hum_in'];
	$weather["humidity_trend"]      = 'n/a';
# extra humidity fields
#        for ($n = 1; $n <= 10 ; $n++) {}
# moisture fields 
#        for ($n = 1; $n <= 8 ; $n++) {}
# rain
        $from                           = strtolower($fr_rain);
        $to                             = trim(strtolower($rainunit));
	$weather["rain_rate"]           = convert_precip ($values['rain_rate_hi_in'],$from,$to,4); 
# 2023-11-06
	if     ( array_key_exists ('rainfall_daily_in', $values) )
	     {  $weather["rain_today"]  = convert_precip ($values['rainfall_daily_in'],$from,$to); }
	elseif ( array_key_exists ('rainfall_day_in', $values) )
	     {  $weather["rain_today"]  = convert_precip ($values['rainfall_day_in'],$from,$to); }
	else {	$weather["rain_today"]  = 'n/a';}

	if     ( array_key_exists ('rainfall_monthly_in', $values) )
	     {  $weather["rain_month"]  = convert_precip ($values['rainfall_monthly_in'],$from,$to); }
	elseif ( array_key_exists ('rainfall_month_in', $values) )
	     {  $weather["rain_month"]  = convert_precip ($values['rainfall_month_in'],$from,$to); }
	else {	$weather["rain_month"]  = 'n/a';}
# 2023-11-06

	$weather["rain_yday"]           = 'n/a';
	$weather["rain_year"]           = convert_precip ($values['rainfall_year_in'],$from,$to); 
	$weather["rain_lasthour"]       = convert_precip ($values['rainfall_last_60_min_in'],$from,$to); 	
	$weather["rain_units"]          = $rainunit;  
 # UV
	if (!isset ($values['uv_index']) || $values['uv_index'] === NULL)
	     {  $weather["uv"]          = 'n/a';}
	else {  $weather["uv"]          = (float) $values['uv_index'];}
# solar
	if (!isset ($values['solar_rad'])|| $values['solar_rad'] === NULL)
	     {  $weather["solar"]       = 'n/a';}
	else {  $weather["solar"]       = (float) $values['solar_rad'];}
# lux
        $weather["lux"]                 = number_format((float)$weather["solar"]/0.0079*0.95299*1.0012,0, '.', '');
# windspeed type fields
        $from   = strtolower($fr_wind);                
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($values['wind_speed_last'],$from,$to);
	$weather["wind_speed_avg"]      = convert_speed ($values['wind_speed_avg_last_10_min'],$from,$to);
        $weather["wind_run"]            = 'n/a';
	$weather["wind_units"]          = $windunit;
#  gust       
	$weather["wind_gust_speed"]     = convert_speed ($values['wind_speed_hi_last_10_min'],$from,$to);
        $weather["wind_gust_speed_max"] = 'n/a';   
#
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $values['wind_dir_at_hi_speed_last_2_min'];
	$weather["wind_direction_avg"]  = $values['wind_dir_at_hi_speed_last_10_min'];
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# Battery status outside
        $key    = 'battery_voltage';
        if (array_key_exists ($key, $values))
             {  $name           = 'battout_v';
	        $value          = (float) $values[$key];
	        $weather[$name] = $value;
	        $name           = 'battout_v';
	        $weather[$name] = 'n/a';}
# other  	
        $weather["swversion"]   = 'DWL_APv2';
}
# ------------------   Davis WeatherLink network
elseif ($livedataFormat == 'DWL') 
     {  if ($read_net_data) 
             {  $non_cron       = 90;
                $scrpt          = 'PWS_load_files.php'; 
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
                include_once $scrpt;}   # $livedata = './jsondata/wlcom.json';  ##### test
#  load data
	$crr    = false;                #### 2020-12-24
	if (is_file ($livedata) )       
	     {  $crr    = json_decode(file_get_contents($livedata), true);}
        if (!is_array($crr) ||  !array_key_exists ('davis_current_observation', $crr) )
             {  $error_txt      = 'error for:'.$livedataFormat.', the livedata-file: '.$livedata.' is not correct / incomplete'; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24
        $hst            = $crr['davis_current_observation'];    // $hst   has mostly min-max values
#
        $recordDate     = strtotime($crr['observation_time_rfc822']);
        $weather["temp_units"]          = "F";
        $weather["barometer_units"]     = "in";
	$weather["wind_units"]          = "mph";
	$weather["rain_units"]          = "in";
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;
	$weather["datetime"]            = $recordDate;
	$weather["date"]                = date($dateFormat, $recordDate);
	$weather["time"]                = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $crr['temp_f'];
	$sql_barometer  = $crr['pressure_in'];
	$sql_raintoday  = $hst['rain_day_in'];
	if (isset ($hst['uv_index']))
	     {  $sql_uv = (int) $hst['uv_index'];}
	else {  $sql_uv = '';}
	$sql_windgust   = $hst['wind_ten_min_gust_mph'];
	$sql_windspeed  = $hst['wind_ten_min_avg_mph'];
	if (isset ($hst['solar_radiation']))              ##### 2020-12-08
	     {  $sql_solar = (int) $hst['solar_radiation'];}
	else {  $sql_solar = '';}
#	$sql_solar      = (int) $hst['solar_radiation'];  ##### 2020-12-08
	$sql_dewpoint   = $crr['dewpoint_f'];
	$sql_rainrate   = $hst['rain_rate_in_per_hr'];
	$sql_direction  = $crr['wind_degrees'];
	$sql_lightning  = 0;
# barometer/pressure	
        $from   = 'in';
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($crr ['pressure_in'],$from,$to);
	$weather["barometer_max"]       = convert_baro ($hst['pressure_day_high_in'],$from,$to);
	$weather["barometer_max_time"]  = set_my_time($hst['pressure_day_high_time']);
	$weather["barometer_min"]       = convert_baro ($hst['pressure_day_low_in'],$from,$to);
	$weather["barometer_min_time"]  = set_my_time($hst['pressure_day_low_time']);
	$weather["barometer_trend"]     = 'n/a';
	$weather["barometer_trend_text"]= $hst['pressure_tendency_string'];
 
	$weather["barometer_units"]     = $pressureunit; 
# temperature type fields
        $from   = 'f';
        $to     = trim(strtolower($tempunit));
        $weather["temp_units"]          = $tempunit;
	$value                          = 
	$weather["temp_indoor"]         = convert_temp ($hst['temp_in_f'],$from,$to);
	$weather["temp_indoor_feel"]    = heatIndex($value, $hst['relative_humidity_in']); // must set temp_units first
	$weather["dewpoint"]            = convert_temp ($crr['dewpoint_f'],$from,$to);
	$weather["dewpoint_low"]        = convert_temp ($hst['dewpoint_day_low_f'],$from,$to);
	$weather["dewpoint_low_time"]   = set_my_time($hst['dewpoint_day_low_time']);
	$weather["temp"]                = convert_temp ($crr['temp_f'],$from,$to);
	$weather["temp_high"]           = convert_temp ($hst['temp_day_high_f'],$from,$to);
	$weather["temp_high_time"]      = set_my_time($hst['temp_day_high_time']);
	$weather["temp_low"]            = convert_temp ($hst['temp_day_low_f'],$from,$to);
	$weather["temp_low_time"]       = set_my_time($hst['temp_day_low_time']);
	$weather["temp_trend"]          = 'n/a';
        $weather["temp_feel"]           = heatIndex($weather["temp"], $crr['relative_humidity']); // must set temp_units first
        if ($crr['heat_index_f'] <> null)
	     { $weather["heat_index"]   = convert_temp ($crr['heat_index_f'],$from,$to);}
	else { $weather["heat_index"]   = 'n/a';}
#	$weather["temp_feel"]           = $weather["heat_index"];
	$weather["windchill"]           = convert_temp ($crr['windchill_f'],$from,$to);
	$weather["windchill_low"]       = convert_temp ($hst['windchill_day_low_f'],$from,$to);
	$weather["windchill_low_time"]  = set_my_time($hst['windchill_day_low_time']);
	$weather["wetbulb"] 		= 'n/a'; 
# extra temp fields
        for ($n = 1; $n <= 7 ; $n++)
             {  $key    = 'temp_extra_'.$n; #             [temp_extra_1] => 54.0
                if (array_key_exists ($key, $hst))
                     {  $name   = 'extra_tmp'.$n;
                        $value  = $hst[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}
                }
# soiltemp fields 
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'temp_soil_'.$n;  # [temp_soil_1] => 44.0
                if (array_key_exists ($key, $hst))
                     {  $name   = 'soil_tmp'.$n;
                        $value  = (float) $hst[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}                        
                }
# soiltemp fields 
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'temp_leaf_'.$n;  # [temp_soil_1] => 44.0
                if (array_key_exists ($key, $hst))
                     {  $name   = 'leaf_tmp'.$n;
                        $value  = (float) $hst[$key];
                        $weather[$name] = convert_temp ($value,$from,$to);}                        
                }
	  
# humidity
	$weather["humidity"]            = $crr['relative_humidity'];
	$weather["humidity_indoor"]     = $hst['relative_humidity_in'];
	$weather["humidity_trend"]      = 'n/a';
# extra humidity fields
        for ($n = 1; $n <= 7 ; $n++)
             {  $key    = 'relative_humidity_'.$n; # [relative_humidity_1] => 61
                if (array_key_exists ($key, $hst))
                     {  $name   = 'extra_hum'.$n;
                        $weather[$name] = (int) $hst[$key];}
                }
# moisture fields 
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'soil_moisture_'.$n; # [soil_moisture_1] => 22
                if (array_key_exists ($key, $hst))
                     {  $name   = 'soil_mst'.$n;
                        $weather[$name] = (float) $hst[$key];}
                }
        for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'leaf_wetness_'.$n; # leaf_wetness_
                if (array_key_exists ($key, $hst))
                     {  $name   = 'leaf_wetness'.$n;
                        $weather[$name] = (float) $hst[$key];}
                }
# rain
        $from   = 'in';
        $to     = trim(strtolower($rainunit));
	$weather["rain_rate"]           = convert_precip ($hst['rain_rate_in_per_hr'],$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($hst['rain_day_in'],$from,$to); 	
	$weather["rain_month"]          = convert_precip ($hst['rain_month_in'],$from,$to);
	$weather["rain_year"]           = convert_precip ($hst['rain_year_in'],$from,$to);	
	$weather["rain_lasthour"]       = 'n/a'; # convert_precip ($hst['rain_day_in'],$from,$to); 
	if (array_key_exists ('et_day', $hst))
	    {   $weather['et_today']    = convert_precip ($hst['et_day'],$from,$to,4);
	        $weather['et_month']    = convert_precip ($hst['et_month'],$from,$to,4);
	        $weather['et_year']     = convert_precip ($hst['et_year'],$from,$to,4);}
	$weather["rain_yday"]           = convert_precip ($hist['rain']['HghV']['yday'],$rain_his,$to); 
	$weather["rain_units"]          = $rainunit;  
# UV /solar
	if (isset ($hst['uv_index']))                              ##### 2020-12-08
	     {  $weather["uv"]  = (int) $hst['uv_index'];}
	else {  $weather["uv"]  = '';}
#	$weather["uv"]                  = $hst['uv_index'];
	if (isset ($hst['solar_radiation']))              
	     {  $weather["solar"] = (int) $hst['solar_radiation'];}
	else {  $weather["solar"] = '';}
#	$weather["solar"]               = $hst['solar_radiation']; ##### 2020-12-08
	$weather["lux"]                 = number_format((float)$weather["solar"]/0.0079*0.95299*1.0012,0, '.', '');  ##### 2020-12-08
        $weather["solar_max"]           = $weather["uv_max"]      = 'n/a';
        $weather["solar_max_time"]      = $weather["uv_max_time"] = 'n/a';
	if (isset ($hst['solar_radiation_day_high'])) 
	     {  $weather["solar_max"]     = $hst['solar_radiation_day_high'];
	        $weather["solar_max_time"]= set_my_time($hst['solar_radiation_day_high_time']);}
	if (isset ($hst['uv_index_day_high'])) 
	     {  $weather["uv_max"]        = $hst['uv_index_day_high'];
	        $weather["uv_max_time"]   =set_my_time( $hst['uv_index_day_high_time']);}
# windspeed type fields
        $from   = 'mph';                
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($crr['wind_mph'],$from,$to);
	$weather["wind_speed_avg"]      = convert_speed ($hst['wind_ten_min_avg_mph'],$from,$to);
	$weather["wind_gust_speed"]     = convert_speed ($hst['wind_ten_min_gust_mph'],$from,$to);
        $weather["wind_run"]            = 'n/a';
	if (!isset ($hst['wind_day_high_mph']))
	     {  $hst['wind_day_high_mph']= 0;
	        $weather["wind_speed_max_time"]	= 
	        $weather["wind_gust_speed_max_time"] = 'n/a';}      
	else {  $weather["wind_speed_max"]      = convert_speed ($hst['wind_day_high_mph'],$from,$to);
                $weather["wind_speed_max_time"]	= set_my_time($hst['wind_day_high_time']);}
# check max from history
	$value                          = $hist['gust']['HghV']['today'];
	$time                           = $hist['gust']['HghV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = -2000;}
        $weather["wind_gust_speed_max"] = convert_speed ($value,$wind_his,$to);
        if ($weather["wind_gust_speed"]  > $weather["wind_gust_speed_max"]) 
              { $weather["wind_gust_speed_max"] = $weather["wind_gust_speed"];
                $time                           = $recordDate; } 
        $weather["wind_gust_speed_max_time"] = set_my_time($time,true);
	$weather["wind_units"]          = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $crr['wind_degrees'];
	$weather["wind_direction_avg"]  = 'n/a'; #$crr['avgbearing'];
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# other
        $weather['soilmoist_type']      = 'cb';
        $weather["swversion"]           = 'DWL';
} // eo Davis WL
#
# --------------------realtime.txt 
elseif (($livedataFormat == 'cumulus'     || $livedataFormat == 'weathercat' || $livedataFormat == 'weewx' 
      || $livedataFormat == 'weatherlink' || $livedataFormat == 'wifilogger' || $livedataFormat == 'MB_rt' )  && $livedata) 
     {  if (!is_file ($livedata) )      #### 2020-12-24
	     {  $error_txt      = 'error: livedataFormat='.$livedataFormat.' no data found at '.$livedata; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24
#
        $file   = file_get_contents($livedata);
        if ($livedataFormat == 'weatherlink' || $livedataFormat == 'MB_rt')  
             {  $explode_char   = "\n";}
        else {  $explode_char   = ' ';}
        if ($livedataFormat == 'weathercat') 
             {  $frmAPM = array (' PM ',' AM ', ' am ', ' pm ');
                $toAPM  = array ('_PM ','_AM ', '_am ','_pm ');
                $file   = str_replace ($frmAPM, $toAPM, $file);}
        $cu     = explode($explode_char,$file); #echo '<pre>'.print_r($cu,true); exit;
        if ($livedataFormat == 'weatherlink' || $livedataFormat == 'MB_rt')  # remove remaining cr | lf
             {  for ($i = 0; $i < count ($cu); $i++) 
                     {  $cu[$i] = trim($cu[$i]);
                        if ($cu[$i] === '--') {$cu[$i] = 'n/a';}
                        }
                }
# save units as used in the file
        $weather["temp_units"]          = $data_temp = $cu[14]; // C or F
	if(preg_match('/C/i',$weather["temp_units"]))  // handle WeatherCat WCT_Realtime.txt TEMPUNITS$ format
	     {  $weather["temp_units"] = 'C';} 
	else {  $weather["temp_units"] = 'F';}  // thanks to KenTrue for finding this probpem 2019-02-11

        $weather["barometer_units"]     = $data_baro = $cu[15]; // mb or hPa or in
	$weather["wind_units"]          = $data_wind = $cu[13]; // m/s or mph or km/h or kts
	$weather["rain_units"]          = $data_rain = $cu[16]; // mm or in
	$weather["loaded"]              = $livedataFormat ;
	$weather["loaded_from"]         = $livedata ;

#$liveYMD= 'a b c ';     # Y M D
#$liveYMD= 'c b a ';     # D M Y	
#$liveYMD= 'c a b'; 	# M D Y

#  Pull out hour, minute, second, month, day, year
#	list ($y, $m, $d) = explode ('/',$cu[0].'///');
        $from   = array ('0','1','2','3','4','5','6','7','8','9');
        $rest   = str_replace ($from,'',trim($cu[0]));
        $char   = substr($rest,0,1);
        if ($char == '') 
             {  echo 'invalid date format, can not process this file '.$livedata.' - date = '.trim($cu[0]) ; }
        list ($a, $b, $c) = explode ($char,$cu[0].$char.$char.$char);
        if (trim($c) == '') 
             {  echo 'invalid date format-seperator: '.$char.', can not process this file '.$livedata.' - date = '.trim($cu[0]);
                }
        
        list ($h, $i, $s) = explode (':',$cu[1].':00:00:');        
        list ($y_l, $m_l, $d_l) = explode (' ',$liveYMD.' ');
        $y      = $$y_l;
        $m      = $$m_l;
        $d      = $$d_l;
	if (strlen($y) < 4) {$y = '20'.$y;}
$recordDate = mktime( $h, $i, $s, $m, $d, $y ); #echo '$cu[0]='.$cu[0].' $cu[1]='.$cu[1].' '.$h.'-'.$i.'-'.$s.'-'.$m.'-'.$d.'-'.$y; 
	$weather["datetime"]           = $recordDate;
	$weather["date"]               = date($dateFormat, $recordDate);
	$weather["time"]               = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
# remove , from float values
        $sql_temp       = (float) str_replace(',','.',$cu[2]);
	$sql_barometer  = (float) str_replace(',','.',$cu[10]);
	$sql_raintoday  = (float) str_replace(',','.',$cu[9]);
	$sql_uv         = (int)   str_replace(',','.',$cu[43]);
	$sql_windgust   = (float) str_replace(',','.',$cu[40]);
	$sql_windspeed  = (float) str_replace(',','.',$cu[6]);
	$sql_solar      = (float) str_replace(',','.',$cu[45]);
	$sql_dewpoint   = (float) str_replace(',','.',$cu[4]);
	$sql_rainrate   = (float) str_replace(',','.',$cu[8]);
	$sql_direction  = (float) str_replace(',','.',$cu[7]);
	$sql_lightning  = 0;
# barometer/pressure	
        $from   = trim(strtolower($weather["barometer_units"]));
        $to     = trim(strtolower($pressureunit));
        $cu[10] = str_replace (',','',$cu[10]);  #### 2020-12-31
	$weather["barometer"]           = convert_baro ($cu[10],$from,$to);
#	$weather["barometer_trend"]     = convert_baro ($cu[18],$from,$to);
#echo '.>'.$cu[18].'<'; exit;
        $weather["barometer_trend"]     = $weather["barometer_trend_text"]= 'n/a';
	if ($livedataFormat == 'weatherlink')
	     {  $weather["barometer_trend_text"]= str_replace ('_',' ',$cu[18]);}
	if (trim($cu[18]) <> 'n/a' && trim($cu[18]) <> '---'  ) 
             {  $weather["barometer_trend"]     = convert_baro ($cu[18],$from,$to);}
        $cu[34] = str_replace (',','',$cu[34]);  #### 2020-12-31	
	$weather["barometer_max"]       = convert_baro ($cu[34],$from,$to);
	$weather["barometer_max_time"]  = set_my_time(str_replace ('_',' ',$cu[35]));
        $cu[36] = str_replace (',','',$cu[36]);  #### 2020-12-31
	$weather["barometer_min"]       = convert_baro ($cu[36],$from,$to);
	$weather["barometer_min_time"]  = set_my_time(str_replace ('_',' ',$cu[37]));
	$weather["barometer_units"]     = $pressureunit;
# temperature type fields
        $from   = trim(strtolower($weather["temp_units"]));  
        $to     = trim(strtolower($tempunit));  
	$weather["temp_indoor"]         = convert_temp ($cu[22],$from,$to);
	$num                            = heatIndex((float)$cu[22], (float)$cu[23]);  #### 2020-12-08
        $weather["temp_indoor_feel"]    = convert_temp ($num,$from,$to);
	$weather["dewpoint"]            = convert_temp ($cu[4],$from,$to);
	$weather["dewpoint_low"]        = 'n/a';
	$weather["dewpoint_low_time"]   = 'n/a';
	
	$weather["temp"]                = convert_temp ($cu[2],$from,$to);
	$num                            = heatIndex($cu[2], $cu[3]); 
	$weather["temp_feel"]           = convert_temp ($num,$from,$to);
	$weather["wetbulb"] 		= 'n/a';
	if (trim($cu[41]) == '---' || trim($cu[41]) == 'n/a') 
	     {  $weather["heat_index"]  = 'n/a';}
	else {  $weather["heat_index"]  = convert_temp ($cu[41],$from,$to);}
	$weather["windchill"]           = convert_temp ($cu[24],$from,$to);
	$weather["windchill_low"]       = 'n/a';
	$weather["windchill_low_time"]  = 'n/a';

	$weather["temp_high"]           = convert_temp ($cu[26],$from,$to);
	$weather["temp_low"]            = convert_temp ($cu[28],$from,$to);
	if (trim($cu[25]) == '---' || trim($cu[25]) == 'n/a') 
	     {  $weather["temp_trend"]  = 'n/a';}
	else {  $new                    = $cu[2] + $cu[25];                 #######       2020-10-01
	        $newOK                  = convert_temp ($new,$from,$to);    
	        $weather["temp_trend"]  = $newOK - $weather["temp"];   }    #######     #convert_temp ($cu[25],$from,$to);  
	        
	$weather["temp_units"]          = $tempunit;
	$weather["temp_high_time"]      = set_my_time(str_replace ('_',' ',$cu[27]));
	$weather["temp_low_time"]       = set_my_time(str_replace ('_',' ',$cu[29]));
# humidity
        $weather["humidity"]            = (int) $cu[3];	
	$weather["humidity_indoor"]     = (int) $cu[23];
	$weather["humidity_trend"]      = 'n/a';
# rain
        $from   = trim(strtolower($weather["rain_units"]));
        $to     = trim(strtolower($rainunit));
        $num                            = $cu[8];
	$weather["rain_rate"]           = convert_precip ($num,$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($cu[9],$from,$to);
	$weather["rain_month"]          = convert_precip ($cu[19],$from,$to);
	$weather["rain_year"]           = convert_precip ($cu[20],$from,$to);
	if (trim($cu[47]) == 'n/a' || trim($cu[47]) == '---') 
	     {  $weather["rain_lasthour"]       = 'n/a';}
	else {  $weather["rain_lasthour"]       = convert_precip ($cu[47],$from,$to);}
	if (trim($cu[21]) == 'n/a' || trim($cu[21]) == '---' ) 
	     {  $weather["rain_yday"]           = 'n/a';}
	else {  $weather["rain_yday"]           = convert_precip ($cu[21],$from,$to);}
	$weather["rain_units"]          = $rainunit;
# UV /solar
	if ((string) $cu[43] === 'n/a' || (string) $cu[43] === '---')
	     {  $weather["uv"]          = 'n/a'; }
	else {  if ((float) $cu[43] > 10) {$cu[43] = (float) $cu[43] / 10;}
	        $weather["uv"]          = (int) $cu[43];} # echo '$cu[43]='.$cu[43]. ' - $weather["uv"]='.$weather["uv"]; exit;
	if ((string) $cu[45] === 'n/a' || (string) $cu[45] === '---')
	     {  $weather["solar"]       = 'n/a'; 
	        $weather["lux"] 	= 'n/a';}
	else {  $weather["solar"]       = round( (float) str_replace(',','.',$cu[45]),0);
	        $weather["lux"] 	= number_format($weather["solar"]/0.0084555*1.035,0, '.', '');}
	$weather["solar_max"]           = 'n/a';
	$weather["solar_max_time"]      = 'n/a';	
	$weather["uv_max"]              = 'n/a';
	$weather["uv_max_time"]         = 'n/a';
	
# windspeed type fields
        $from   = trim(strtolower($weather["wind_units"]));
        $to     = trim(strtolower($windunit));          #echo '$from='.$from.' $to='.$to; exit;
	$weather["wind_speed"]         = convert_speed ($cu[6],$from,$to);
	$weather["wind_speed_avg"]     = convert_speed ($cu[5],$from,$to);
	$weather["wind_gust_speed"]    = convert_speed ($cu[40],$from,$to);
	$weather["wind_speed_max"]     = convert_speed ($cu[30],$from,$to);
	if ($cu[40] === 'n/a') 
	     {  $weather["wind_gust_speed"] = $weather["wind_speed_max"];
	        $weather["wind_speed_max"]  = 'n/a';}
	$weather["wind_gust_speed_max"]= convert_speed ($cu[32],$from,$to); #echo '<pre>'.print_r($weather,true); exit;
### ? convert windrun ????
        $run    = (string) trim($cu[17]);       ## 2020-05-02
        if ($run === 'n/a' || $run === '--'  ) 
             {  $weather["wind_run"]    = 'n/a';}
	else {  $weather["wind_run"]    = convert_speed ($cu[17],$from,$to);}
	$weather["wind_speed_max_time"]         = set_my_time(str_replace ('_',' ',$cu[31]));
	$weather["wind_gust_speed_max_time"]    = set_my_time(str_replace ('_',' ',$cu[33]));	
	$weather["wind_units"]         = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]     = $cu[7];
	if ((string) $cu[46] === '---')         #### 2021-01-18
	     {  $weather["wind_direction_avg"] = 'n/a';}
	else {  $weather["wind_direction_avg"] = $cu[46];}
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# other
	$weather["swversion"]           = ucfirst($livedataFormat).'_'.$cu['38'];
#print_r($weather); exit; 
} // eo realtimetext 	
#	
# ------------------------- clientraw
elseif ( ($livedataFormat == 'wd' || $livedataFormat == 'meteohub' || $livedataFormat == 'wswin') && $livedata) 
     {  if (!is_file ($livedata) )      #### 2020-12-24
	     {  $error_txt      = 'error: livedataFormat='.$livedataFormat.' no data found at '.$livedata; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24     
#
        $weather["temp_units"]          = $data_temp = "C";
        $weather["barometer_units"]     = $data_baro = "hPa";
	$weather["wind_units"]          = $data_wind = "kts";
	$weather["rain_units"]          = $data_rain = "mm";	
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;
#  load clientraw
	if (!is_file ($livedata) )      #### 2020-12-24
	     {  $error_txt      = 'error: livedataFormat='.$livedataFormat.' no data found at '.$livedata; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24     
        $file_live      = file_get_contents($livedata);
	$wd             = explode(" ", $file_live);
#  Pull out hour, minute, second, month, day, year
	$recordDate = mktime($wd[29], $wd[30], $wd[31], $wd[36], $wd[35], $wd[141]);
	$weather["datetime"]            = $recordDate;
	$weather["date"]                = date($dateFormat, $recordDate);
	$weather["time"]                = date($timeFormat, $recordDate);
# find acceptable gust speed                                    #### 2021-05-23 
#if ( (float) $wd[133] > (float) $wd[2] ) {$wd[2] = $wd[133];}  #### 2021-05-23 
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $wd[4];
	$sql_barometer  = $wd[6];
	$sql_raintoday  = $wd[7];
	$sql_uv         = (int) $wd[79];
	$sql_windgust   = (float) $wd[133];     #### 2021-05-23 Max gust last hour
	$sql_windspeed  = (float) $wd[2];       #### 2021-05-23 Current windspeed (kts)
	$sql_solar      = round((float) $wd[127],0);  # 2023-02-22  PHP 8
	$sql_dewpoint   = $wd[72];
	$sql_rainrate   = $wd[10]*60;;
	$sql_direction  = $wd[3];
	$sql_lightning  = 0;
# barometer/pressure	
        $from   = trim(strtolower($weather["barometer_units"]));
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($wd[6],$from,$to);
	$weather["barometer_max"]       = convert_baro ($wd[131],$from,$to);
	$weather["barometer_max_time"]  = 'n/a'; # clientrawextra 799
	$weather["barometer_min"]       = convert_baro ($wd[132],$from,$to);
	$weather["barometer_min_time"]  = 'n/a'; # clientrawextra 800
	$weather["barometer_trend"]     = convert_baro ($wd[50],$from,$to);
	$weather["barometer_trend_text"]= 'n/a';
	$weather["barometer_units"]     = $pressureunit;
# temperature type fields
        $from   = trim(strtolower($weather["temp_units"]));
        $to     = trim(strtolower($tempunit));
	$weather["temp_indoor"]         = convert_temp ($wd[12],$from,$to);
	$num                            = heatIndex($wd[12], $wd[13]); // must set temp_units first
	$weather["temp_indoor_feel"]    = convert_temp ($num,$from,$to);
	$weather["dewpoint"]            = convert_temp ($wd[72],$from,$to);
	$weather["dewpoint_low"]        = convert_temp ($wd[139],$from,$to);
	$weather["dewpoint_low_time"]   = 'n/a'; # clientrawextra 816	
	$weather["temp"]                = convert_temp ($wd[4],$from,$to);
        $num                            = heatIndex($wd[4], $wd[5]); // must set temp_units first
	$weather["temp_feel"]           = convert_temp ($num,$from,$to);
	$weather["wetbulb"] 		= convert_temp ($wd[159],$from,$to);
	$weather["heat_index"]          = convert_temp ($wd[112],$from,$to);
	$weather["windchill"]           = convert_temp ($wd[44],$from,$to);
	$weather["windchill_low"]       = convert_temp ($wd[78],$from,$to);	
	$weather["windchill_low_time"]  = set_my_time($wd[166]);	
	
	$weather["temp_high"]           = convert_temp ($wd[46],$from,$to);
	$weather["temp_low"]            = convert_temp ($wd[47],$from,$to);
	$weather["temp_high_time"]      = $weather["temp_low_time"] = 'n/a';
	if (array_key_exists (174,$wd) )
	     {  $weather["temp_high_time"]      = set_my_time($wd[174]);}
	if (array_key_exists (175,$wd) )
	     {  $weather["temp_low_time"]       = set_my_time($wd[175]);}
	$weather["temp_trend"]          = convert_temp ((float) $wd[99],$from,$to)   - convert_temp ((float) $wd[90],$from,$to); ###### 2020-10-01 error c-f |2019-01-22  replace [143] which is login not value
# EXTRA'S
        $weather['extra_tmp1']          = convert_temp ($wd[20],$from,$to);
        $weather['extra_tmp2']          = convert_temp ($wd[21],$from,$to);
        $weather['extra_tmp3']          = convert_temp ($wd[22],$from,$to);
        $weather['extra_hum1']          = (float) $wd[26];
        $weather['extra_hum2']          = (float) $wd[27];
        $weather['extra_hum3']          = (float) $wd[28];
        $weather['soilmoist_type']      = 'cb';
        if ($wd[14] <> 0)
             {  $weather['soil_tmp1']   = convert_temp ($wd[14],$from,$to);
                $weather['soil_mst1']   = (float) $wd[157]; }
        if ($wd[156] <> 0)
             {  $weather['leaf_wetness1'] = (float) $wd[156];}
         
# EXTRA'S
	$weather["temp_units"]          = $tempunit;
# humidity
	$weather["humidity"]            = $wd[5];
	$weather["humidity_indoor"]     = $wd[13];
	$weather["humidity_trend"]      = $wd[144];
# rain
        $from   = trim(strtolower($weather["rain_units"]));
        $to     = trim(strtolower($rainunit));
	$num                            = $wd[10]*60;
	$weather["rain_rate"]           = convert_precip ($num,$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($wd[7],$from,$to); 	
	$weather["rain_month"]          = convert_precip ($wd[8],$from,$to);
	$weather["rain_year"]           = convert_precip ($wd[9],$from,$to);	
	$value                          = (float) $wd[109] - (float) $wd[100];  # 2019-01-14 -21
	$weather["rain_lasthour"]       = convert_precip ($value ,$from,$to);   # 2019-01-14
	$weather["rain_yday"]		= convert_precip ($wd[19],$from,$to);	
	$weather["rain_units"]          = $rainunit;
# UV /solar
	$weather["uv"]                  = $wd[79];
	$weather["uv_max"]              = 'n/a';
	$weather["uv_max_time"]         = 'n/a';
	$weather["solar"]               = round( (float) $wd[127],0);  # 2023-02-22  PHP 8 #  CR 034	solar reading
	$weather["solar_max"]           = 'n/a'; # clientrawextra  817
	$weather["solar_max_time"]      = 'n/a'; # clientrawextra  818
	$weather["lux"]                 = number_format((float)$wd[127]/0.0079*0.95299*1.0012,0, '.', '');
# windspeed type fields
        $from   = trim(strtolower($weather["wind_units"]));
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($wd[2],$from,$to);     #### 2021-05-23 Current windspeed (kts)
	$weather["wind_speed_avg"]      = convert_speed ($wd[158],$from,$to);   #### 2021-05-23 10 minute average windspeed (kts)
	$weather["wind_gust_speed"]     = convert_speed ($wd[133],$from,$to);   #### 2021-05-23 Max gust last hour
	$weather["wind_speed_max"]      = convert_speed ($wd[113],$from,$to);   #### 2021-05-23 Maximum average speed for the day (kts)
	$weather["wind_gust_speed_max"] = convert_speed ($wd[71],$from,$to);    #### 2021-05-23 maximum gust for the day (kts)
	if (array_key_exists (173,$wd) )   #### 2021-03-30
             {  if ($to == 'm/s' || $to == 'km/h') 
                     {  $weather["wind_run"]    = round((float)$wd[173]);}
                else {  $weather["wind_run"]    = round(convert_speed ($wd[173],'km/h',$to));}}
        else {  $weather["wind_run"]            = 'n/a';}  #### 2021-03-30
	$weather["wind_speed_max_time"]		= set_my_time($wd[135]);        #### 2021-05-23  Max gust today time as no max wind time available
	$weather["wind_gust_speed_max_time"]	= set_my_time($wd[135]);        #### 2021-05-23  Max gust today time
	$weather["wind_units"]          = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $wd[3];
	$weather["wind_direction_avg"]  = $wd[117];
# current conditions
        $from = array ('/','_'); $to = array ('. ',' ');
	$weather["currentdescription"]  = str_replace ($from, $to, $wd[49]);	
	$weather["currentweathericon"]  = $wd[48];	
# other
        $last   = count ($wd) - 1;
        if (isset ($wd[$last]) )
             {  $string = str_replace ('!','',$wd[$last]); }
        else {  $string = 'n/a';}
        $weather["swversion"] = "Clientraw ".$string;
	$wd[177]        = $string;
} // eo $livedataFormat == clientraw
#
# ------------------------- WDapi
elseif ($livedataFormat == 'WDapi') 
     {  $livedata       = './jsondata/WDapi.txt';
        if (!is_file ($livedata) )      #### 2020-12-24
	     {  $error_txt      = 'error: livedataFormat='.$livedataFormat.' no data found at '.$livedata; 
	        $weather["loaded"] = $error_txt; 
	        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$error_txt.PHP_EOL;
	        return;}                #### 2020-12-24        
        $weather["temp_units"]          = $data_temp = "C";
        $weather["barometer_units"]     = $data_baro = "hPa";
	$weather["wind_units"]          = $data_wind = "m/s";
	$weather["rain_units"]          = $data_rain = "mm";	
	$weather["loaded"]      = $livedataFormat ;
	$weather["loaded_from"] = $livedata ;
#  load clientraw
        $file_live      = file_get_contents($livedata);
	$wd             = explode(" ", $file_live); #echo '<pre>'.print_r($wd,true);
#  Pull out hour, minute, second, month, day, year from 19/10/2017 21:24:00
	$recordDate = mktime((int)substr($wd[1], 0, 2),(int) substr($wd[1], 3, 2), (int)substr($wd[1], 6, 2),
	(int)substr($wd[0], 3, 2), (int)substr($wd[0], 0, 2), (int)substr($wd[0], 6, 4));
	$weather["datetime"]            = $recordDate;
	$weather["date"]                = date($dateFormat, $recordDate);
	$weather["time"]                = date($timeFormat, $recordDate);
# create SQL values before conversion to user units
	$sql_date       = date('d M',$recordDate);
	$sql_updated    = date('G:i',$recordDate);
        $sql_temp       = $wd[2];
	$sql_barometer  = $wd[10];
	$sql_raintoday  = $wd[9];
	$sql_uv         = (int) $wd[39];
	$sql_windgust   = $wd[6];
	$sql_windspeed  = $wd[5];
	$sql_solar      = round((int)$wd[41],0);
	$sql_dewpoint   = $wd[4];
	$sql_rainrate   = $wd[8]*60;;
	$sql_direction  = $wd[7];
	$sql_lightning  = 0;
# barometer/pressure	
        $from   = trim(strtolower($weather["barometer_units"]));
        $to     = trim(strtolower($pressureunit));
	$weather["barometer"]           = convert_baro ($wd[10],$from,$to);
	$weather["barometer_max"]       = convert_baro ($wd[30],$from,$to);
	$weather["barometer_max_time"]  = set_my_time($wd[31]);	
	$weather["barometer_min"]       = convert_baro ($wd[32],$from,$to);
	$weather["barometer_min_time"]  = set_my_time($wd[33]);
	$value                          = convert_baro ($wd[14],$from,$to); // pressure 60 minutes
	$weather["barometer_trend"]     = $weather["barometer"]  - $value;
	$weather["barometer_trend_text"]= 'n/a';
	$weather["barometer_units"]     = $pressureunit;
# temperature type fields
        $from   = trim(strtolower($weather["temp_units"]));
        $to     = trim(strtolower($tempunit));
	$weather["temp_indoor"]         = convert_temp ($wd[18],$from,$to);
	$num                            = heatIndex($wd[18], $wd[19]);  // temp hum
	$weather["temp_indoor_feel"]    = convert_temp ($num,$from,$to); 
	$weather["dewpoint"]            = convert_temp ($wd[4],$from,$to);
	$weather["dewpoint_low"]        = convert_temp ($wd[60],$from,$to);
	$weather["dewpoint_low_time"]   = set_my_time($wd[61]);	
	$weather["temp"]                = convert_temp ($wd[2],$from,$to);
        $num                            = heatIndex($wd[2], $wd[3]); 
	$weather["temp_feel"]           = convert_temp ($num,$from,$to);
	$weather["wetbulb"] 		= 'n/a';        #convert_temp ($wd[159],$from,$to);
	$num                            = heatIndex($wd[2], $wd[3]);    // temp hum
	$weather["heat_index"]          = convert_temp ($num,$from,$to);
	$weather["windchill"]           = convert_temp ($wd[20],$from,$to);
	$weather["windchill_low"]       = 'n/a';	
	$weather["windchill_low_time"]  = 'n/a';	

	$weather["temp_high"]           = convert_temp ($wd[22],$from,$to);
	$weather["temp_low"]            = convert_temp ($wd[24],$from,$to);
	$weather["temp_high_time"]      = set_my_time($wd[23]); 
	$weather["temp_low_time"]       = set_my_time($wd[25]);

	$weather["temp_trend"]          = $weather["temp"] - convert_temp ($wd[62],$from,$to);
	$weather["temp_units"]          = $tempunit;
# humidity
	$weather["humidity"]            = $wd[3];
	$weather["humidity_indoor"]     = $wd[19];
	$weather["humidity_trend"]      = $weather["humidity"] - $wd[63];
# rain
        $from   = trim(strtolower($weather["rain_units"]));
        $to     = trim(strtolower($rainunit));
	$num                            = $wd[8]*60;
	$weather["rain_rate"]           = convert_precip ($num,$from,$to,4); 	
	$weather["rain_today"]          = convert_precip ($wd[9],$from,$to); 	
	$weather["rain_month"]          = convert_precip ($wd[15],$from,$to);
	$weather["rain_year"]           = convert_precip ($wd[16],$from,$to);	
	$weather["rain_lasthour"]       = convert_precip ($wd[43],$from,$to);
	$weather["rain_yday"]		= convert_precip ($wd[17],$from,$to);	
	$weather["rain_units"]          = $rainunit;
# UV /solar
	$weather["uv"]                  = (float) $wd[39];
	$weather["solar"]               = round((float) $wd[41],0);
	$weather["lux"]                 = number_format((float) $wd[41]/0.0079*0.95299*1.0012,0, '.', '');
	$weather["solar_max"]           = 'n/a';
	$weather["solar_max_time"]      = 'n/a';
	$weather["uv_max"]              = 'n/a';
	$weather["uv_max_time"]         = 'n/a';
# windspeed type fields
        $from   = trim(strtolower($weather["wind_units"]));
        $to     = trim(strtolower($windunit));
	$weather["wind_speed"]          = convert_speed ($wd[5],$from,$to);
	$weather["wind_speed_avg"]      = convert_speed ($wd[36],$from,$to);
	$weather["wind_gust_speed"]     = convert_speed ($wd[6],$from,$to);
	$weather["wind_speed_max"]      = convert_speed ($wd[26],$from,$to);
	$weather["wind_gust_speed_max"] = convert_speed ($wd[28],$from,$to);
	$weather["wind_run"]            = 'n/a'; #convert_speed ($wd[173],$from,$to);
	$weather["wind_speed_max_time"]		= set_my_time($wd[27]);
	$weather["wind_gust_speed_max_time"]	= set_my_time($wd[29]);
	$weather["wind_units"]          = $windunit;
# Keep track of the conversion factor for windspeed to knots because it is useful in multiple places
        if     ($weather["wind_units"] == 'mph') { $toKnots = 0.868976;} 
        elseif ($weather["wind_units"] == 'km/h'){ $toKnots = 0.5399568;} 
        elseif ($weather["wind_units"] == 'm/s') { $toKnots = 1.943844;}
        else                                     { $toKnots = 1;}
# (wind) direction 
	$weather["wind_direction"]      = $wd[7];
	$weather["wind_direction_avg"]  = $wd[47];
# current conditions
	$weather["currentdescription"]  = 'n/a'; 
	$weather["currentweathericon"]  = 'n/a';
# other
	$weather["swversion"]           = 'WD-api '.$wd[34];
# clean up
# EXTRA'S
        $from   = trim(strtolower($data_temp));
        $to     = trim(strtolower($weather["temp_units"]));
        $weather['extra_tmp1']          = convert_temp ($wd[67],$from,$to);
        $weather['extra_tmp2']          = convert_temp ($wd[68],$from,$to);
        $weather['extra_tmp3']          = convert_temp ($wd[69],$from,$to);
        $weather['extra_hum1']          = (float) $wd[70];
        $weather['extra_hum2']          = (float) $wd[71];
        $weather['extra_hum3']          = (float) $wd[72]; 
        
} // eo $livedataFormat == 'WDapi'
#
if (isset ($check_hist_HL) )
    {    $to             = trim(strtolower($pressureunit));   # echo print_r(   $weather ); 
# check max baro from history
	$value                          = $hist['baro']['HghV']['today'];
	$time                           = $hist['baro']['HghV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = -2000;}
        $weather["barometer_max"]       = convert_baro ($value,$baro_his,$to);
        if ($weather["barometer"] > $weather["barometer_max"]) 
             {  $weather["barometer_max"]= $weather["barometer"];
                $time                   = $recordDate;}
	$weather["barometer_max_time"]  = set_my_time($time,true); 
# check min baro from history
	$value                          = $hist['baro']['LowV']['today'];
	$time                           = $hist['baro']['LowV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = +2000;}
        $weather["barometer_min"]       = convert_baro ($value,$baro_his,$to);
        if ($weather["barometer"] < $weather["barometer_min"]) 
             {  $weather["barometer_min"]= $weather["barometer"];
                $time                   = $recordDate;}		        
	$weather["barometer_min_time"]  = set_my_time($time,true); 
#
        $to                             = trim(strtolower($tempunit));
# check min dewp from history
	$value                          = $hist['dewp']['LowV']['today'];
	$time                           = $hist['dewp']['LowV_D']['today'];     
	if ((string) $value === 'n/a')  { $value  = +2000;}
        $weather["dewpoint_low"]        = convert_temp ($value,$temp_his,$to);
        if ($weather["dewpoint"] < $weather["dewpoint_low"]) 
             {  $weather["dewpoint_low"] = $weather["dewpoint"];
                $time                   = $recordDate;}		        
	$weather["dewpoint_low_time"]   = set_my_time($time,true);
# check max temp from history
	$value                          = $hist['temp']['HghV']['today'];
	$time                           = $hist['temp']['HghV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = -2000;}
        $weather["temp_high"]           = convert_temp ($value,$temp_his,$to);
        if ($weather["temp"] > $weather["temp_high"]) 
              { $weather["temp_high"]   = $weather["temp"];
                $time                   = $recordDate;}
        $weather["temp_high_time"]      = set_my_time($time,true);
# check min temp from history
	$value                          = $hist['temp']['LowV']['today'];
	$time                           = $hist['temp']['LowV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = +2000;}
        $weather["temp_low"]            = convert_temp ($value,$temp_his,$to);
        if ($weather["temp"] < $weather["temp_low"]) 
              { $weather["temp_low"]    = $weather["temp"];
                $time                   = $recordDate;}
        $weather["temp_low_time"]       = set_my_time($time,true); 
# check max UV from history
        if ( (string) $weather["uv"] <> 'n/a')  # 2022-03-03
             {  $value                          = $hist['uvuv']['HghV']['today'];
                $time                           = $hist['uvuv']['HghV_D']['today'];
                if ((string) $value === 'n/a')  { $value  = -2000;}
                $weather["uv_max"]              = $value;
                if ((float) $weather["uv"] > (float) $weather["uv_max"] ) {
                        $weather["uv_max"]      = $weather["uv"];
                        $time                   = $recordDate;}      
                $weather["uv_max_time"]         = set_my_time($time,true);}
        if ( (string) $weather["solar"] <> 'n/a')  # 2022-03-03
# check max solar from history
             {  $value                          = $hist['solr']['HghV']['today'];
                $time                           = $hist['solr']['HghV_D']['today'];
                if ((string) $value === 'n/a')  { $value  = -2000;}
                $weather["solar_max"]           = $value;
                if ((float)$weather["solar"] > (float) $weather["solar_max"] ) {
                        $weather["solar_max"]   = $weather["solar"];
                        $time                   = $recordDate;}      
                $weather["solar_max_time"]      = set_my_time($time,true); }
#
        $to                             = trim(strtolower($windunit));
# check max wind from history
	$value                          = $hist['wind']['HghV']['today'];
	$time                           = $hist['wind']['HghV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = -2000;}
        $weather["wind_speed_max"]      = convert_speed ($value,$wind_his,$to);
        if ($weather["wind_speed"]  > $weather["wind_speed_max"]) {
                $weather["wind_speed_max"]      = $weather["wind_speed"];
                $time                           = $recordDate; } 
        $weather["wind_speed_max_time"] = set_my_time($time,true);
# check max gust from history
	$value                          = $hist['gust']['HghV']['today'];
	$time                           = $hist['gust']['HghV_D']['today'];
	if ((string) $value === 'n/a')  { $value  = -2000;}
        $weather["wind_gust_speed_max"] = convert_speed ($value,$wind_his,$to);
        if ($weather["wind_gust_speed"]  > $weather["wind_gust_speed_max"]) 
              {  $weather["wind_gust_speed_max"] = $weather["wind_gust_speed"];
                $time                           = $recordDate; } 
        $weather["wind_gust_speed_max_time"] = set_my_time($time,true); # echo print_r(   $weather ); exit;
}
#
$load_ecowitt_option    = false;
if ( isset ($ecowittoption) 
     && $ecowittoption == true 
     && isset ($ecowittfile) 
     && file_exists ($ecowittfile) && filesize ($ecowittfile) > 4)
     {  $weather['loaded_from'].= PHP_EOL.$ecowittfile;
        $arr            = unserialize(file_get_contents($ecowittfile));
        if (!is_array($arr) )
             {  $weather['loaded_from'].= PHP_EOL.'invalid ecowitt-file';}
        else {  $load_ecowitt_option    = true;
                $weather['AQ_time']     = filemtime($ecowittfile);}
        }
if ($load_ecowitt_option <> false && isset ($ecowittAQ) && $ecowittAQ == true)     #  AQ
     {  for ($n = 1; $n <= 4 ; $n++)
             {  $key    = 'pm25_ch'.$n; # [pm25_ch1] => 140.0
                if (array_key_exists ($key, $arr))
                     {  $name   = 'pm25_crnt'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'pm25_avg_24h_ch'.$n; # [pm25_avg_24h_ch1] => 60.2
                if (array_key_exists ($key, $arr))
                     {  $name   = 'pm25_24avg'.$n;
                        $weather[$name] = (float) $arr[$key];}
                $key    = 'pm25batt'.$n; # [pm25batt1] => 3
                if (array_key_exists ($key, $arr))
                     {  $name   = 'batt_pm'.$n;
                        $value  = (float) $arr[$key];
                        $weather[$name.'_t'] = 'OK';
                        if ($value <= 1)  {$weather[$name.'_t'] = 'low';}
                        $weather[$name.'_v'] = $value;}
                }
        $keys   = array ('pm25_co2', 'pm10_co2', 'pm25_24h_co2',   'pm10_24h_co2',   'co2', 'co2_24h',   'humi_co2'); #  'co2_batt' 'tf_co2'
        $names  = array ('pm25_co2', 'pm10_co2', 'pm25_co2_24avg', 'pm10_co2_24avg', 'co2', 'co2_24avg', 'humi_co2'); #  'co2_batt' 'tf_co2'
        foreach ($keys as $nr => $key) 
             {  if (array_key_exists ($key, $arr)) 
                     {  $name   = $names[$nr];
                        $weather[$name] = (float) $arr[$key];}
                }
        if (array_key_exists ('tf_co2', $arr) )
             {  $weather['tf_co2']      = convert_temp ( (float) $arr['tf_co2'],'f', strtolower($weather["temp_units"]) );}
        } // AQ 
if ($load_ecowitt_option <> false && isset ($ecowittlightning) && $ecowittlightning == true)   # lightning
     { if (array_key_exists ('lightning',$arr)) 
             {  if ( (int) $arr['lightning_time'] == 0 && !array_key_exists('lightningtime',$hist) )
                     {  $weather['lightningtimeago']    = 
                        $weather['lightningtime']       =
                        $weather['lightningmi']         =
                        $weather['lightningkm']         =
                        $weather['lightning']           = 'n/a';}
                elseif ( (int) $arr['lightning_time'] == 0)
                     {  $weather['lightningtime']       = $hist['lightningtime'];
                        $weather['lightningmi']         = $hist['lightningmi'];
                        $weather['lightningkm']         = $hist['lightningkm'];
                        $weather['lightning']           = $hist['lightning'];
                        $weather['lightningtimeago']    = time() - $weather['lightningtime'];}
                else {  $weather['lightningtime']       = (int) $arr['lightning_time'];
                        $weather['lightningtimeago']    = time() - $arr['lightning_time'];
                        $weather['lightningmi']         = round (0.621371 * (float) $arr['lightning'], 0);        
                        $weather['lightningkm']         = (float) $arr['lightning']; #round ($weather['lightningmi'] / 0.621371);
                        $weather['lightning']           = $arr['lightning_num']; }      
        } // eo lightning found
        } // eo use for lightning
#
if (    $livedataFormat <> 'wf'
     && $weatherflowoption == true 
     && file_exists ('./jsondata/weatherflow.txt') && filesize ('./jsondata/weatherflow.txt') > 1)
    {   $stck_lst      .= basename(__FILE__).' ('.__LINE__.') WeatherFlow option used: Checking data ./jsondata/weatherflow.txt'.PHP_EOL;
        $content = file_get_contents('./jsondata/weatherflow.txt');
        $json = json_decode($content, true);
        if (    !array_key_exists('obs',$json)                                  # 2023-07-18  
            ||  !is_array($json['obs'])                                         # 2023-07-18
            ||  count($json['obs']) == 0  )                                     # 2023-07-18
             {  if (    array_key_exists ('status', $json)                      # 2023-07-18                      
                     && is_array($json['status'])                               # 2023-07-18
                     && array_key_exists ('status_message', $json['status']) )  # 2023-07-18
                     {  $message        = $json['status']['status_message'];}   # 2023-07-18
                else {  $message        = 'No explaining message found in data';}  # 2023-07-18
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') invalid data => '.$message.'. WeatherFlow option is now switched off. '.PHP_EOL; }   # 2023-07-18
        else {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Processing ./jsondata/weatherflow.txt'.PHP_EOL;   # 2023-07-18
                foreach($json['obs'] as $item) {break; }
                $weatherflow['dist_units']      = $json['station_units']['units_distance'];
                $weatherflow['time']            = $item['timestamp'];	        	
                $weatherflow['solar']           = $item['solar_radiation'];
                $weatherflow['uv']              = $item['uv'];
                $weatherflow['lux']             = $item['brightness']; 
                if (!array_key_exists('lightning_strike_last_epoch',$item ) )    
                     {  $weatherflow['lastlightningtime']       = 0;}
                else {  $weatherflow['lastlightningtime']       = $item['lightning_strike_last_epoch'];}
                if (!array_key_exists('lightning_strike_last_distance',$item ) ) 
                     {  $weatherflow['lightningdistance']       = 0;}
                else {  $weatherflow['lightningdistance']       = $item['lightning_strike_last_distance'];}
                if (!array_key_exists('lightning_strike_count',$item ) ) 
                     {  $weatherflow['lightning'] = 0;}
                else {  $weatherflow['lightning'] = $item['lightning_strike_count'];}
                if (!array_key_exists('lightning_strike_count_last_3hr',$item ) ) 
                     {  $weatherflow['lightning3hr'] = 0;}
                else {  $weatherflow['lightning3hr'] = $item['lightning_strike_count_last_3hr'];}
                if ( trim(strtolower($weatherflow['dist_units'])) <> 'km')
                     {  $toKM   = 1.609344; $toMI   = 1;}
                else {  $toKM   = 1;        $toMI   = 0.621371;}
                $distance       = (float) $weatherflow['lightningdistance'];
                $weatherflow['lightningdistance']   = round ($distance , 0);
                $weatherflow['lightningdistanceKM'] = round ($distance * $toKM, 0);
                $weatherflow['lightningdistanceMI'] = round ($distance * $toMI, 0);
        #
                if (!array_key_exists ('lightningtimeago',$weather) )
                     {  $weather['lightningtimeago']    = time() - $weatherflow['lastlightningtime'];
                        $weather['lightningtime']       = $weatherflow['lastlightningtime'];
                        $weather['lightningkm']         = (float) $weatherflow['lightningdistance'];
                        $weather['lightningmi']         = round (0.621371 * $weatherflow['lightningdistance'],0);        
                        $weather['lightning']           = $weatherflow['lightning']; }
                if ($weather['lightningtime'] == 0 && $weather['lightning'] == 0 && array_key_exists('lightningtime',$hist) )
                     {  $weather['lightningtime']       = $hist['lightningtime'];
                        $weather['lightningmi']         = $hist['lightningmi'];
                        $weather['lightningkm']         = $hist['lightningkm'];
                        $weather['lightning']           = $hist['lightning'];
                        $weather['lightningtimeago']    = time() - $weather['lightningtime'];}
                }
        }
#
$sql_lightning  = 0;
if (array_key_exists ('lightning',$weather)) {$sql_lightning  = $weather['lightning'];}
#
# clean up
if ($weather["heat_index"] === null || $weather["heat_index"] === 'n/a' || $weather["heat_index"] === 0)
     {  $weather["heat_index"] = heatIndex($weather["temp"], $weather["humidity"]);}

$online = '
<svg viewBox="0 0 32 32" width="7" height="7" fill="currentcolor">
<circle cx="16" cy="16" r="14"></circle>
</svg>
';
#
$online_txt_ld  =  '<b class="PWS_offline"> '.$online.lang('Offline').' </b>';
if (array_key_exists("datetime", $weather)) 
     {  if (time() - $weather["datetime"] < 520 )
             {  $online_txt_ld   = '<b class="PWS_online"> '.$online.set_my_time_lng($weather['datetime'],true).' </b>';}
} 
#

# Check for snow data if not already in the data file
if (isset ($snow_show) && $snow_show == "manual" )
     {  $scrpt          = 'PWS_snow.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        $return         = include_once $scrpt;
        if (is_array ($return) )
             {  # echo '<pre>'.print_r($return); exit;
                $now    = time();
                $today  = date ('Ymd', $now);
                $yday   = date ('Ymd', ($now - 24*3600));

                $weather['snow_depth']  = $return['c_hght'];
                $weather['snow_season'] = $return['c_fall'];
                
                if (array_key_exists($today, $arr_snw))
                     {  $arr    = $arr_snw[$today];
                        $weather['snow_today'] = $arr['c_fall'];}
                else {  $weather['snow_today'] = 0;}

                if (array_key_exists($yday, $arr_snw))
                     {  $arr    = $arr_snw[$yday];
                        $weather['snow_yday']   = $arr['c_fall'];}
                else {  $weather['snow_yday']   = 0;}

                $lst_snw                = $return['c_date'];

                if      ($lst_snw == $today) { $lst_txt  = lang('Today');}
                elseif  ($lst_snw == $yday)  { $lst_txt  = lang('Yesterday');}
                else {  $y      = substr ($lst_snw,0,4);
                        $m      = substr ($lst_snw,4,2);
                        $d      = substr ($lst_snw,6,2);
                        $date   = mktime (0,0,0,$m,$d,$y);
                        $mnth   = lang(date('M ',$date) );  #### 2021-02-11
                        $lst_txt= $mnth.' <b>'.(int) $d.'th</b>';
                        }
                $weather['snow_last'] = $lst_txt;
                } // eo array
        } // eo snow
#
# Check for extra sensors if not already in the data file
#       $have_extra = true;   // an extra file is uploaded
#       $extra_data = "demodata/extra_sensors.txt";
#
if (!isset($have_extra) || $have_extra <> true) {$have_extra = false; return; }
if (!isset($extra_data) )                       {$have_extra = false; return; }
if ($extra_data == 'use demodata') // for testing used also
     {  $extra_data = './demodata/extra_sensors.txt'; }     
if (!file_exists ($extra_data) )                {$have_extra = false; return; }
#
$scrpt          = 'PWS_extra_data.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
#echo '<pre>'.print_r($weather,true); exit;
return;
function new_history()
     {  $hist = array ();
        if (file_exists ('_my_settings/history.txt') ) {
                $hist           = unserialize (file_get_contents('_my_settings/history.txt')); } 
        elseif (file_exists ('chartsmydata/history_empty.txt') ) {  // first time use
                $hist           = unserialize (file_get_contents('chartsmydata/history_empty.txt')); }  
        else {  $hist           = array();
                $wthr_types     = array ('temp','dewp','rain','humd','baro','wind','gust');
                $types_values   = array ('HghV','HghV_D','HghV_D_T','LowV','LowV_D','LowV_D_T');
                $values_period  = array ('today','yday','month','year','all');
                foreach ($wthr_types as $type) {
                        foreach ($types_values as $value) {
                                foreach ($values_period as $period) {
                                        $hist[$type][$value][$period]   = 'n/a';
                                } // eo period
                        } // eo values
                } // eo types   
        } // eo no file yet
        return $hist;
}