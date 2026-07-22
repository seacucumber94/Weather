<?php $scrpt_vrsn_dt  = 'PWS_Dark_Openweather.php|01|2023-09-09|';  # new beta replacement for darksky users
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
# --------------------------------- TEST
$dark_alt_vrs   = 'ow';
$owm_version    = '2.5';
#$dark_apikey    = ''; // add extra / new key for testing here
# --------------------------------- TEST
if (!isset ($dark_alt_vrs) || $dark_alt_vrs <> 'ow')
     {  echo '<small style="color: red;">First run easyweather setup</small>'; die();}
$ow_api = $dark_apikey;  // for testing use the api-key as text
#
$ow_url         = 'https://api.openweathermap.org/data/'.$owm_version.'/onecall?';
$ow_unit        = 'metric';
if ($EW_unit == 'us') { $ow_unit = 'imperial';}
#
$ow_temp= 'C';          
$ow_wind= 'm/h';
$ow_rain= 'mm';
$ow_snow= 'cm';   // n/u
$ow_baro= 'hPa';
if ($ow_unit == 'imperial') 
     {  $ow_temp= 'F';
        $ow_wind= 'mph';
        $ow_rain= 'mm';
        $ow_snow= 'mm'; // n/u
        $ow_baro= 'hPa';}   
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

$stck_lst       = basename(__FILE__).' ('.__LINE__.') ow units: '.$ow_temp.'|'.$ow_wind.'|'.$ow_rain.'|'.$ow_snow.'|'.$ow_baro.'| dc units: '.$ds_temp.'|'.$ds_wind.'|'.$ds_rain.'|'.$ds_snow.'|'.$ds_baro.'|'.PHP_EOL;
#
$ow_icn = array();
$ow_icn['01d']  = 'clear-day';          #clear sky
$ow_icn['01n']  = 'clear-night';
$ow_icn['02d']  = 'partly-cloudy-day';            #few clouds
$ow_icn['02n']  = 'partly-cloudy-night';
$ow_icn['03d']  = 'partly-cloudy-day';            #scattered clouds
$ow_icn['03n']  = 'partly-cloudy-night';
$ow_icn['04d']  = 'cloudy';                #broken clouds
$ow_icn['04n']  = 'nt_cloudy';
$ow_icn['09d']  = 'rain';            #shower rain
$ow_icn['09n']  = 'nt_rain';
$ow_icn['10d']  = 'rain';            #rain
$ow_icn['10n']  = 'nt_rain';
$ow_icn['11d']  = 'thunderstorm';      #thunderstorm
$ow_icn['11n']  = 'nt_thunderstorm';
$ow_icn['13d']  = 'snow';       #snow
$ow_icn['13n']  = 'nt_snow';
$ow_icn['50d']  = 'fog';             #mist
$ow_icn['50n']  = 'nt_fog';
#
$ow_lngs= array('ar','az','bg','ca','da','de','el','en','es','fi','fr','he','hi','hr','hu','id','it','ja','kr','la','no','nl','pl','pt','ro','ru','sv','se','sk','sr','tr','uk','zh');

$ow_lng = substr($locale_wu,0,2);  # echo __LINE__.$locale_wu; die();
if (!in_array($ow_lng,$ow_lngs) ) {$ow_lng = 'en';};
#
$ow_fct = $fl_folder.'openweather'.'_'.$ow_unit.'_'.$ow_lng.'.json';  #echo $ow_fct; die();

# $lat.','.$lon.'/next8days?unitGroup='.$ow_unit.'&lang='.$ow_lng.'&key='.$ow_api.'&contentType=json'; 
$ow_url .= 'lat='.$lat.'&lon='.$lon.'&appid='.$ow_api.'&lang='.$ow_lng.'&units=metric';  // m/s
$ow_url .= '&tbeta='.time();
#echo '$ow_url='.$ow_url.PHP_EOL.'$ow_fct='.$ow_fct;  die;
$ow_load= true;
$ow_age = 600; #$ow_age = 600*100;
if (    file_exists ($ow_fct) 
     && $ow_age > (time() - filemtime ($ow_fct))  )
     {  $ow_load= false; }
if ( $ow_load == true )
     {  file_get_contents_curl ($ow_url); 
        if ($errors <> '')
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be loaded'.PHP_EOL;
                return false;}
        #
        $lngth  = file_put_contents($ow_fct, $rawdata); 
        if ( (int) $lngth == 0) 
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be saved'.PHP_EOL;}      
        }
else {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Existing data used'.PHP_EOL;
        $rawdata= file_get_contents ($ow_fct);}  
$ow_arr = json_decode ($rawdata, true); unset ($rawdata);
$ds_arr = array();
#
# convert general part
$ds_arr['latitude']     = $ow_arr['lat'];
$ds_arr['longitude']    = $ow_arr['lon'];
$ds_arr['timezone']     = $ow_arr['timezone'];
$ds_arr['flags']['units'] = $ds_unit;
#
# convert current condition
$ds_crt = array();
$ow_crt = $ow_arr['current'];                 #       echo print_r($ow_crt,true); 
$ds_crt['time']         = $ow_crt['dt']; # time(); #filemtime ($ow_fct); #$ow_crt['datetimeEpoch'];     # 	1670573494
if (    array_key_exists('weather',$ow_crt ) 
    &&  array_key_exists(0,$ow_crt['weather'] ) 
    &&  is_array($ow_crt['weather'][0]) )
     {  $ow_cc  = $ow_crt['weather'][0];}
else {  $ow_cc  = array();}
if ( array_key_exists('description',$ow_cc ) ) 
     {  $ds_crt['summary']      = $ow_cc['description']; } 
else {  $ds_crt['summary']      = '';}      
if ( array_key_exists('icon',$ow_cc ) ) 
     {  $ds_crt['icon']         = ow_icon ($ow_cc['icon']); } 
else {  $ds_crt['icon']         = '';}       #echo '<pre>'.' '.$stck_lst.print_r($ow_cc,true).print_r($ds_crt,true); die();  
# rain/snow
$ds_crt['precipType']           = 'rain'; 
$ds_crt['precipIntensity']      = 0; 
$ds_crt['precipProbability']    = 0;
#
$ds_crt['temperature']          = ow_temp ($ow_crt['temp']);    
$ds_crt['apparentTemperature']  = ow_temp ($ow_crt['feels_like']); 
$ds_crt['dewPoint']             = ow_temp ($ow_crt['dew_point']);  
$ds_crt['humidity']             = round ($ow_crt['humidity']/100 , 2);
$ds_crt['pressure']             = ow_baro ($ow_crt['pressure']);
$ds_crt['windSpeed']            = ow_wind ($ow_crt['wind_speed']);
$ds_crt['windGust']             = ow_wind ($ow_crt['wind_gust']);
$ds_crt['windBearing']          = $ow_crt['wind_deg'];        
$ds_crt['cloudCover']           = round ($ow_crt['clouds']/100 , 2);  
$ds_crt['uvIndex']              = $ow_crt['uvi'];
$ds_crt['visibility']           = $ow_crt['visibility'];    
$ds_crt['ozone']                = 'null';                       
$ds_arr['currently']            = $ds_crt;                     #      echo '<pre>'.$stck_lst.print_r($ds_arr,true); die();
#
$ds_arr['daily']                = array();
$ds_arr['daily']['summary']     = '';
$ds_arr['daily']['icon']        = '';
$ds_arr['daily']['data']        = array();
#
$ds_arr['hourly']                = array();
$ds_arr['hourly']['summary']     = '';
$ds_arr['hourly']['icon']        = '';
$ds_arr['hourly']['data']        = array();
#
# convert day-parts
$count  = 0;
$max    = 8; # count($ow_arr['days']) ; 
$ow_ymdh= date ('YmdH', time()); # echo '<!-- '.__LINE__.' '. $ow_ymdh . ' -->'.PHP_EOL; // current hour
foreach ($ow_arr['daily'] as $ow_day)
     {  $count++;
        if ($count > $max) { break;}
        $ds_day = array ();
        $ds_day['time']         = $ow_day['dt'];
        if (    array_key_exists('weather',$ow_day ) 
            &&  array_key_exists(0,$ow_day['weather'] ) 
            &&  is_array($ow_day['weather'][0]) )
             {  $ow_cc  = $ow_day['weather'][0];}
        else {  $ow_cc  = array();}
        if ( array_key_exists('description',$ow_cc ) ) 
             {  $ds_day['summary']      = $ow_cc['description']; } 
        else {  $ds_day['summary']      = '';}        
        if ( array_key_exists('icon',$ow_cc ) ) 
             {  $ds_day['icon'] = ow_icon ($ow_cc['icon']); } 
        else {  $ds_day['icon'] = '';}      

        $ds_day['sunriseTime']  = $ow_day['sunrise'];
        $ds_day['sunsetTime']   = $ow_day['sunset'];
        $ds_day['moonPhase']    = $ow_day['moon_phase'];
        $ds_day['windBearing']  = $ow_day['wind_deg'];
        $ds_day['uvIndex']      = $ow_day['uvi'];
        $ds_day['visibility']   = 'null';     
        $ds_day['ozone']        = 'null';
        $ds_day['humidity']     = round ($ow_day['humidity']/100 , 2);
        $ds_day['precipProbability']   
                                = round ($ow_day['pop']/100 , 2);
        $ds_day['cloudCover']   = round ($ow_day['clouds']/100 , 2);
# temps
        $ds_day['temperatureHigh']              = 
        $ds_day['temperatureMax']               = ow_temp ($ow_day['temp']['max']);
        $ds_day['temperatureLow']               =
        $ds_day['temperatureMin']               = ow_temp ($ow_day['temp']['min']);
        $ds_day['apparentTemperatureHigh']      =
        $ds_day['apparentTemperatureMax']       = ow_temp ($ow_day['feels_like']['day']);  
        $ds_day['apparentTemperatureLow']       = 
        $ds_day['apparentTemperatureMin']       = ow_temp ($ow_day['feels_like']['day']);
        $ds_day['dewPoint']                     = ow_temp ($ow_day['dew_point']);
# rain / snow
        if (array_key_exists ('rain', $ow_day) )
             {  $ds_day['precipType']           = 'rain';
                $ds_day['precipIntensity']      = 
                $ds_day['precipIntensityMax']   = round(ow_rain ($ow_day['rain'])/24,3); }
        elseif (array_key_exists ('snow', $ow_day) )
             {  $ds_day['precipType']           = 'snow';
                $ds_day['precipIntensity']      = 
                $ds_day['precipIntensityMax']   = round(ow_snow ($ow_day['snow'])/24,3); }
        else {  $ds_day['precipType']           = 'rain';
                $ds_day['precipIntensity']      = 
                $ds_day['precipIntensityMax']   = 0;}
# wind
        $ds_day['windSpeed']                    = ow_wind ($ow_day['wind_speed']);
        $ds_day['windGust']                     = ow_wind ($ow_day['wind_gust']);
# pressure
        $ds_day['pressure']                     = ow_baro ($ow_day['pressure']);
# save 1 day 
        $ds_arr['daily']['data'][]      = $ds_day;
        }
              
foreach ($ow_arr['hourly']  as $ow_hour )
      { $this_ymdh                      = date ('YmdH',$ow_hour['dt']); #echo '<!-- '.__LINE__.' '. $this_ymdh ; // data hour
        if ($this_ymdh <  $ow_ymdh) { continue;} 
        $ds_1hr = array ();      
        $ds_1hr['time']                 = $ow_hour['dt'];
        if (    array_key_exists('weather',$ow_hour ) 
            &&  array_key_exists(0,$ow_hour['weather'] ) 
            &&  is_array($ow_hour['weather'][0]) )
             {  $ow_cc  = $ow_hour['weather'][0];}
        else {  $ow_cc  = array();}
        if ( array_key_exists('description',$ow_cc ) ) 
             {  $ds_1hr['summary']      = $ow_cc['description']; } 
        else {  $ds_1hr['summary']      = '';}        
        if ( array_key_exists('icon',$ow_cc ) ) 
             {  $ds_1hr['icon']         = ow_icon ($ow_cc['icon']); } 
        else {  $ds_1hr['icon']         = '';}      
                
        $ds_1hr['uvIndex']              = $ow_hour['uvi'];
        $ds_1hr['windBearing']          = $ow_hour['wind_deg'];
        $ds_1hr['ozone']                = 'null';
        $ds_1hr['humidity']             = round ($ow_hour['humidity']/100 , 2);
        $ds_1hr['precipProbability']    = round ($ow_hour['pop']/100 , 2);
        $ds_1hr['cloudCover']           = round ($ow_hour['clouds']/100 , 2);
# temps
        $ds_1hr['temperature']          = ow_temp ($ow_hour['temp']);
        $ds_1hr['apparentTemperature']  = ow_temp ($ow_hour['feels_like']);
        $ds_1hr['dewPoint']             = ow_temp ($ow_hour['dew_point']);
# rain / snow
        $ds_1hr['precipProbability']    = $ow_hour['pop'];
        if (array_key_exists('rain',$ow_hour) 
         && array_key_exists('1h',$ow_hour['rain']) )
             {  $ds_1hr['precipType']           = 'rain';
                $ds_1hr['precipIntensity']      = ow_rain ($ow_hour['rain']);}
        elseif (array_key_exists('snow',$ow_hour) 
             && array_key_exists('1h',$ow_hour['snow']) )
             {  $ds_1hr['precipType']           = 'snow';
                $ds_1hr['precipIntensity']      = ow_snow ($ow_hour['precip']);}
        else {  $ds_1hr['precipType']           = 'rain';
                $ds_1hr['precipIntensity']      = 0;}
# wind
        $ds_1hr['windSpeed']            = ow_wind ($ow_hour['wind_speed']);
        $ds_1hr['windGust']             = ow_wind ($ow_hour['wind_gust']);
# pressure
        $ds_1hr['pressure']             = ow_baro ($ow_hour['pressure']);
# save 1 day        
        $ds_arr['hourly']['data'][] = $ds_1hr;
}
#echo print_r($ds_arr,true);
        
echo json_encode($ds_arr);

function ow_icon ($value)
     {  global $ow_icn;
        $key    = trim ($value);
        if ($key <> '' && array_key_exists($value,$ow_icn))
             {  $return = $ow_icn[$key];}
        else {  $return = $key;}
        return $return;}

function ow_temp ($value)
     {  global $ow_temp, $ds_temp, $dec_tmp;
        return  convert_temp ($value,$ow_temp,$ds_temp,$dec_tmp); }

function ow_baro ($value)
     {  global $ow_baro, $ds_baro, $dec_baro;
        return  convert_baro ($value,$ow_baro,$ds_baro,$dec_baro); }
 
function ow_snow ($value)
     {  global $ow_snow, $ds_snow, $dec_rain;
        return  convert_precip ($value,$ow_snow,$ds_snow,$dec_rain); }

function ow_rain($value)
     {  global $ow_rain, $ds_rain, $dec_rain;
        return  convert_precip ($value,$ow_rain,$ds_rain,$dec_rain); }

function ow_wind ($value)
     {  global $ow_wind, $ds_wind, $dec_wnd;
        return  convert_speed ($value,$ow_wind,$ds_wind,$dec_wnd); }

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
