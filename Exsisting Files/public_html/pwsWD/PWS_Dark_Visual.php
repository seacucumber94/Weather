<?php $scrpt_vrsn_dt  = 'PWS_Dark_Visual.php|01|2023-02-15|';  #  replacement for darksky users
#
header('Content-type: application/json; charset=UTF-8');
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
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------------  load the settings
# load settings when run stand-alone
$scrpt          = 'PWS_settings.php';
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#
if (!isset ($dark_alt_vrs) || $dark_alt_vrs <> 'vc')
     {  echo '<small style="color: red;">First run easyweather setup</small>'; die();}
$vc_api = $dark_apikey;  // for testing use the api-key as text
#
$vc_url = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/';
$vc_unit= 'metric';
if ($EW_unit == 'us' || $EW_unit == 'uk') { $vc_unit = $EW_unit;}
#
$vc_temp= 'C';          
$vc_wind= 'km/h';
$vc_rain= 'mm';
$vc_snow= 'cm';
$vc_baro= 'hPa';
if ($vc_unit == 'uk') 
     {  $vc_wind= 'mph';}
elseif ($vc_unit == 'us') 
     {  $vc_temp= 'F';
        $vc_wind= 'mph';
        $vc_rain= 'in';
        $vc_snow= 'in';
        $vc_baro= 'hPa';}   
#
$ds_unit= $darkskyunit; 
        #si:Standard ISO!ca#ca: same as si, windSpeed km/h!uk2#uk: same as si,windSpeed mph!us#us: Imperial units (NON METRIC) |  
$ds_temp= 'C';          
$ds_wind= 'km/h';
$ds_rain= 'mm';
$ds_snow= 'cm';
$ds_baro= 'hPa';
if ($ds_unit == 'si') { $ds_wind= 'm/s';}
if ($ds_unit == 'uk') { $ds_wind= 'mph';}
if ($ds_unit == 'us')
     {  $ds_temp= 'F';
        $ds_wind= 'mph';
        $ds_rain= 'in';
        $ds_snow= 'in';
        $ds_baro= 'inhg';}   

$stck_lst       = basename(__FILE__).' ('.__LINE__.') vc units: '.$vc_temp.'|'.$vc_wind.'|'.$vc_rain.'|'.$vc_snow.'|'.$vc_baro.'| dc units: '.$ds_temp.'|'.$ds_wind.'|'.$ds_rain.'|'.$ds_snow.'|'.$ds_baro.'|'.PHP_EOL;
#
#
$vc_icn = array();
#$vc_icn['clear-day']           = 'yy';
#$vc_icn['clear-night']         = 'yy';
#$vc_icn['cloudy']              = 'yy';
#$vc_icn['fog']                 = 'yy';
#$vc_icn['partly-cloudy-day']   = 'yy';
#$vc_icn['partly-cloudy-night'] = 'yy';
#$vc_icn['rain']                = 'yy';
#$vc_icn['snow']                = 'yy';
#$vc_icn['wind']                = 'yy';
$vc_icn['showers-day']          = 'rain';
$vc_icn['showers-night']        = 'nt_rain';
$vc_icn['snow-showers-day']     = 'snow';
$vc_icn['snow-showers-night']   = 'nt_snow';
$vc_icn['thunder-rain']         = 'thunderstorm';
$vc_icn['thunder-showers-day']  = 'thunderstorm';
$vc_icn['thunder-showers-night']= 'thunderstorm'; 
#
$vc_lngs= array('de','en','es','fi','fr','it','ja','ko','pt','ru','nl','sr','zh');
$vc_lng = substr($locale_wu,0,2);  # echo __LINE__.$locale_wu; die();
if (!in_array($vc_lng,$vc_lngs) ) {$vc_lng = 'en';};
#
$vc_fct = $fl_folder.'visual'.'_'.$vc_unit.'_'.$vc_lng.'.json';  #echo $vc_fct; die();

$vc_url .= $lat.','.$lon.'/next8days?unitGroup='.$vc_unit.'&lang='.$vc_lng.'&key='.$vc_api.'&contentType=json'; 
#echo '$vc_url='.$vc_url.PHP_EOL.'$vc_fct='.$vc_fct;  die;

$vc_load= true;
$vc_age = 600;
if (    file_exists ($vc_fct) 
     && $vc_age > (time() - filemtime ($vc_fct))  )
     {  $vc_load= false; }
if ( $vc_load == true )
     {  file_get_contents_curl ($vc_url); 
        if ($errors <> '')
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be loaded'.PHP_EOL;
                return false;}
        #
        $lngth  = file_put_contents($vc_fct, $rawdata); 
        if ( (int) $lngth == 0) 
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be saved'.PHP_EOL;}      
        }
else {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Existing data used'.PHP_EOL;
        $rawdata= file_get_contents ($vc_fct);}  
$vc_arr = json_decode ($rawdata, true); unset ($rawdata);
$ds_arr = array();
#
# convert general part
$ds_arr['latitude']     = $vc_arr['latitude'];
$ds_arr['longitude']    = $vc_arr['longitude'];
$ds_arr['timezone']     = $vc_arr['timezone'];
$ds_arr['flags']['units'] = $ds_unit;
#
# convert current condition
$ds_crt = array();
$vc_crt = $vc_arr['currentConditions'];                 #       echo print_r($vc_crt,true); 
$ds_crt['time']         = time(); #filemtime ($vc_fct); #$vc_crt['datetimeEpoch'];     # 	1670573494
$ds_crt['summary']      = $vc_crt['conditions'];        # 	"Possible Light Rain"
$ds_crt['icon']         = vc_icon ($vc_crt['icon']);    # 	"rain"
# rain/snow
$type                   = 'rain';
if (is_array($vc_crt['preciptype']))
     {  $type           = $vc_crt['preciptype'][0];}    # 	"rain" / "snow"
$ds_crt['precipType']   = $type; 
if ($type == 'rain') 
     {  $ds_crt['precipIntensity'] = vc_rain ($vc_crt['precip']); } # 	0.3063
else {  $ds_crt['precipIntensity'] = vc_snow ($vc_crt['precip']); }
$ds_crt['precipProbability'] = 
        round ($vc_crt['precipprob']/100 , 2);          # 	0.25
#
$ds_crt['temperature']  = vc_temp ($vc_crt['temp']);    # 	5.23
$ds_crt['apparentTemperature'] = vc_temp ($vc_crt['feelslike']);  #       0.94
$ds_crt['dewPoint']     = vc_temp ($vc_crt['dew']);     # 	1.13
$ds_crt['humidity']     = round ($vc_crt['humidity']/100 , 2);# 0.75
$ds_crt['pressure']     = vc_baro ($vc_crt['pressure']);# 	1002.1
$ds_crt['windSpeed']    = vc_wind ($vc_crt['windspeed']);# 	6.62
$ds_crt['windGust']     = vc_wind ($vc_crt['windgust']);# 	6.63
$ds_crt['windBearing']  = $vc_crt['winddir'];           # 	228
$ds_crt['cloudCover']   = round ($vc_crt['cloudcover']/100 , 2);  # 	0.76
$ds_crt['uvIndex']      = $vc_crt['uvindex'];           # 	0
$ds_crt['visibility']   = $vc_crt['visibility'];        # 	16.093
$ds_crt['ozone']        = 'null';                       # 	381
$ds_arr['currently']    = $ds_crt;                      #       echo print_r($ds_arr,true); 
#
$ds_arr['daily']                = array();
$ds_arr['daily']['summary']     = $vc_arr['description'];
$ds_arr['daily']['icon']        = '';
$ds_arr['daily']['data']        = array();
#
$ds_arr['hourly']                = array();
$ds_arr['hourly']['summary']     = $vc_arr['days'][0]['description'];  #### 2022-12-12
$ds_arr['hourly']['icon']        = '';
$ds_arr['hourly']['data']        = array();


#
# convert day-parts
$count  = 0;
$max    = 8; # count($vc_arr['days']) ; 
$vc_ymdh= date ('YmdH', time()); # echo '<!-- '.__LINE__.' '. $vc_ymdh . ' -->'.PHP_EOL; // current hour
foreach ($vc_arr['days'] as $vc_day)
     {  $count++;
        if ($count > $max) { break;}
        $ds_day = array ();
        $ds_day['time']         = $vc_day['datetimeEpoch'];
        $ds_day['summary']      = $vc_day['description'];
        $ds_day['sunriseTime']  = $vc_day['sunriseEpoch'];
        $ds_day['sunsetTime']   = $vc_day['sunsetEpoch'];
        $ds_day['moonPhase']    = $vc_day['moonphase'];
        $ds_day['windBearing']  = $vc_day['winddir'];
        $ds_day['uvIndex']      = $vc_day['uvindex'];
        $ds_day['visibility']   = $vc_day['visibility'];
        
        $ds_day['ozone']        = 'null';
#
        $ds_day['icon']         = vc_icon ($vc_day['icon']);      
# perc
        $ds_day['humidity']     = round ($vc_day['humidity']/100 , 2);
        $ds_day['precipProbability']   
                                = round ($vc_day['precipprob']/100 , 2);
        $ds_day['cloudCover']   = round ($vc_day['cloudcover']/100 , 2);
# temps
        $ds_day['temperatureHigh']              = 
        $ds_day['temperatureMax']               = vc_temp ($vc_day['tempmax']);
        $ds_day['temperatureLow']               =
        $ds_day['temperatureMin']               = vc_temp ($vc_day['tempmin']);
        $ds_day['apparentTemperatureHigh']      =
        $ds_day['apparentTemperatureMax']       = vc_temp ($vc_day['feelslikemax']);  
        $ds_day['apparentTemperatureLow']       = 
        $ds_day['apparentTemperatureMin']       = vc_temp ($vc_day['feelslikemin']);
        $ds_day['dewPoint']                     = vc_temp ($vc_day['dew']);
# rain / snow
        $type                                   = 'rain';
        if (is_array($vc_day['preciptype']))
             {  $type                           = $vc_day['preciptype'][0]; }
        $ds_day['precipType']                   = $type;      
        if ($type == 'rain')
             {  $ds_day['precipIntensity']      = 
                $ds_day['precipIntensityMax']   = round(vc_rain ($vc_day['precip'])/24,3); }
        else {  $ds_day['precipIntensity']      = 
                $ds_day['precipIntensityMax']   = round(vc_snow ($vc_day['precip'])/24,3); } # echo ' $ds_day["precipIntensityMax"]='.$ds_day['precipIntensityMax']; die;
# wind
        $ds_day['windSpeed']            = vc_wind ($vc_day['windspeed']);
        $ds_day['windGust']             = vc_wind ($vc_day['windgust']);
# pressure
        $ds_day['pressure']             = vc_baro ($vc_day['pressure']);
# save 1 day 
        $ds_arr['daily']['data'][]      = $ds_day;
        foreach ($vc_day['hours']  as $vc_hour )
              { $this_ymdh              = date ('YmdH',$vc_hour['datetimeEpoch']); #echo '<!-- '.__LINE__.' '. $this_ymdh ; // data hour
                if ($this_ymdh <  $vc_ymdh) { continue;} 
                $ds_1hr = array ();      
                $ds_1hr['time']         = $vc_hour['datetimeEpoch'];
                $ds_1hr['summary']      = $vc_hour['conditions'];
                $ds_1hr['icon']         = vc_icon ($vc_hour['icon']);                
                $ds_1hr['uvIndex']      = $vc_hour['uvindex'];
                $ds_1hr['windBearing']  = $vc_hour['winddir'];
                $ds_1hr['ozone']        = 'null';
                $ds_1hr['humidity']             = round ($vc_hour['humidity']/100 , 2);
                $ds_1hr['precipProbability']    = round ($vc_hour['precipprob']/100 , 2);
                $ds_1hr['cloudCover']           = round ($vc_hour['cloudcover']/100 , 2);
# temps
                $ds_1hr['temperature']          = vc_temp ($vc_hour['temp']);
                $ds_1hr['apparentTemperature']  = vc_temp ($vc_hour['feelslike']);
                $ds_1hr['dewPoint']             = vc_temp ($vc_hour['dew']);
# rain / snow
                $type                           = 'rain';
                if (is_array($vc_hour['preciptype']))
                     {  $type                   =  $vc_hour['preciptype'][0];}
                $ds_1hr['precipType']           = $type;
                if ($type == 'rain') 
                     {  $ds_1hr['precipIntensity']      = vc_rain ($vc_hour['precip']);}
                else {  $ds_1hr['precipIntensity']      = vc_snow ($vc_hour['precip']);}
# wind
                $ds_1hr['windSpeed']    = vc_wind ($vc_hour['windspeed']);
                $ds_1hr['windGust']     = vc_wind ($vc_hour['windgust']);
# pressure
                $ds_1hr['pressure']     = vc_baro ($vc_hour['pressure']);
# save 1 day        
                $ds_arr['hourly']['data'][] = $ds_1hr;
                }
        }#  echo print_r($ds_arr,true);
        
echo json_encode($ds_arr);

function vc_icon ($value)
     {  global $vc_icn;
        $key    = trim ($value);
        if ($key <> '' && array_key_exists($value,$vc_icn))
             {  $return = $vc_icn['$key'];}
        else {  $return = $key;}
        return $return;}

function vc_temp ($value)
     {  global $vc_temp, $ds_temp, $dec_tmp;
        return  convert_temp ($value,$vc_temp,$ds_temp,$dec_tmp); }

function vc_baro ($value)
     {  global $vc_baro, $ds_baro, $dec_baro;
        return  convert_baro ($value,$vc_baro,$ds_baro,$dec_baro); }
 
function vc_snow ($value)
     {  global $vc_snow, $ds_snow, $dec_rain;
        return  convert_precip ($value,$vc_snow,$ds_snow,$dec_rain); }

function vc_rain($value)
     {  global $vc_rain, $ds_rain, $dec_rain;
        return  convert_precip ($value,$vc_rain,$ds_rain,$dec_rain); }

function vc_wind ($value)
     {  global $vc_wind, $ds_wind, $dec_wnd;
        return  convert_speed ($value,$vc_wind,$ds_wind,$dec_wnd); }

function file_get_contents_curl ($url,$false=false)
     {  global $stck_lst, $rawdata, $errors;  
	$ch     = curl_init();                                  // initialize a cURL session
	curl_setopt ($ch, CURLOPT_URL, $url);                   // connect to provided URL
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);           // verify peer off,removes a lot of errors with older hosts
#	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   // most host checks this nowadays
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);           // connection timeout
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5);                  // data timeout
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);        // return the data transfer
        curl_setopt ($ch, CURLOPT_NOBODY, false);               // do the download request without getting the body
        curl_setopt ($ch, CURLOPT_HEADER, false);               // include header information
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);            // follow Location: redirect allowed
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);                 //   but only one time
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	$rawdata= curl_exec ($ch);  
	$info	= curl_getinfo($ch);
        $errors = curl_error($ch);
	curl_close ($ch);
        unset ($ch);
        $stck_lst .=  basename(__FILE__).' ('.__LINE__.')<pre>'.print_r($errors,true).PHP_EOL.print_r($info,true).PHP_EOL.'</pre>'.PHP_EOL;
} // eof file_get_contents_curl
