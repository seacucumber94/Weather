<?php  $scrpt_vrsn_dt  = 'fct_ec_shared.php|01|2021-05-25|';  # check file | release 2012_lts
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
# ----------------- save list of loaded scripst;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#-----------------------------------------------
# data https://dd.meteo.gc.ca/citypage_weather/xml/ON/s0000024_f.xml
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
if (substr($used_lang,0,2) == 'fr') { $ec_e_f = 'f';} else { $ec_e_f = 'e';}
#
$cacheFile      = './jsondata/ec_'.$province.'_'.$EC_area.'_'.$ec_e_f.'.xml';
$cacheAllowed   = '9999999'; 
#-----------------------------------------------
#                          load dfata from cache
#-----------------------------------------------
$ec_error       = '';
$rawData        = ECloadFromCache($cacheFile);
if ($rawData == '' || $rawData == false) 
     {  $ec_error       = '<p style="color: red;"> No data could be loaded for '.$cacheFile.'</p>';
        echo $ec_error;
        $stck_lst      .= $ec_error.PHP_EOL;
        echo '<!-- '.$stck_lst.' -->'.PHP_EOL;
        $stck_lst       = '';
        return false; }
#
#                       check character set used
if (ECfound($rawData,'ISO-8859-1') ) 
     {  $rawData = str_replace('ISO-8859-1','UTF-8',$rawData);
        $rawData = utf8_encode ($rawData);}  #### 2021-03-28  mb_convert_encoding($rawData,'UTF-8','ISO-8859-1');}
#
#                     check if it is correct xml
$data   = EC_check_xml();
if ($data === false)
     {  $ec_error       = basename(__FILE__).' ('.__LINE__.')  No valid data available';
        echo $ec_error;
        $stck_lst      .= $ec_error.PHP_EOL;
        echo '<!-- '.$stck_lst.' -->'.PHP_EOL;
        return false;}   # echo '<pre>'.print_r($data,true);     
#
#               check for a forecast in the data
if (!array_key_exists ('forecastGroup',$data) || count ($data['forecastGroup']) < 1) 
     {  $ec_error       = basename(__FILE__).' ('.__LINE__.') No valid forecast in data  ';
        echo $ec_error;
        $stck_lst      .= $ec_error.PHP_EOL;
        echo '<!-- '.$stck_lst.' -->'.PHP_EOL;
        return false; }
#
#   load all arrays for processing / translating 
EC_load_arrays(); 
#
#       check for current conditions in the data
$ccn_arr= array();
if (!array_key_exists ('currentConditions',$data)) 
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') No current conditions in data  '.PHP_EOL;
        $ccn_arr['condition']   = false;}
else {  $arr                    = (array) $data['currentConditions']; 
        $ccn_arr['station']     = (string)$arr['station'];
        $ccn_arr['datetime']    = strtotime((string) $arr['dateTime'][0]->timeStamp.' UTC');
        $ccn_arr['condtext']    = (string)$arr['condition'];
        $ccn_arr['condicon']    = (string)$arr['iconCode'];
        $ccn_arr['temperature'] = (float) $arr['temperature'];
        if (isset ($arr['windChill'])) 
             {  $ccn_arr['windChill']   = (float) $arr['windChill'];}
        else {  $ccn_arr['windChill']   = '';}
        $ccn_arr['pressure']    = (float) $arr['pressure'];
        $ccn_arr['humidity']    = (int)   $arr['relativeHumidity'];
        $arr2                   = (array) $arr['wind']; 
        $ccn_arr['windspeed']   = (int)   $arr2['speed'];
        $ccn_arr['windgust']    = (int)   $arr2['gust'];
        $ccn_arr['bearing']     = (float) $arr2['bearing'];
        $ccn_arr['windunit']    = (string)$arr['wind']->speed['units'];
} // ccn processed
#
if (!array_key_exists ('location',$data))
     {  $EC_region      = '';}
else {  $EC_region      = $data['location'] ->region;} #echo '$EC_area='.$EC_area; echo '<pre>'.print_r($data,true); exit;
#
$cnt    = count($data['forecastGroup']->forecast); # echo '$cnt    ='.$cnt; exit;
$fcts_arr       = array();
for ($n = 0; $n < $cnt; $n++)
     {  $fct_arr                = array();
        $fct                    = $data['forecastGroup']->forecast[$n];

        $fct_arr['period']      = (string) $fct->period;
        $fct_arr['daypart']     = (string) $fct->period['textForecastName'];
        $fct_arr['text']        = (string) $fct->textSummary;
        $fct_arr['condtext']    = (string) $fct->abbreviatedForecast->textSummary;
        $fct_arr['condicon']    = (string) $fct->abbreviatedForecast->iconCode;
 
        $fct_arr['temperature'] = (int)    $fct->temperatures->temperature;
        $fct_arr['temptype']    = (string) $fct->temperatures->temperature['class'];
        
        $fct_arr['windChill']   = (int)    $fct->windChill->calculated;
        $fct_arr['windspeed']   = (int)    $fct->winds->wind->speed;
        $fct_arr['windgust']    = (int)    $fct->winds->wind->gust;
if (isset ($fct->winds->wind->speed['units']) )
     {  $fct_arr['windunit']    = (string) $fct->winds->wind->speed['units']; }
else {  $fct_arr['windunit']    = "km/h";}
       
        $fct_arr['winddeg']     = (int)    $fct->winds->wind->bearing;
        $fct_arr['winddir']     = (string) $fct->winds->wind->direction;
        if (isset ($fct->precipitation->precipType) )
             {  $fct_arr['preciptype']  = (string) $fct->precipitation->precipType;}
        else {  $fct_arr['preciptype']  = '';}      
        if (isset ($fct->precipitation->accumulation->amount) )
             {  $fct_arr['precipacc']   = (string) $fct->precipitation->accumulation->amount;
                $fct_arr['precipunit']  = (string) $fct->precipitation->accumulation->amount['units'];}
        else {  $fct_arr['precipacc']   = 0;}      
        $fct_arr['winddir']     = (string) $fct->winds->wind->direction;

        $fct_arr['humidity']     = (string) $fct->relativeHumidity;
        $fcts_arr[$n]     = $fct_arr;      
        } #  echo '<pre>'.print_r ($data['forecastGroup']->forecast[0],true).print_r ($fcts_arr[0],true); exit;
#
$cnt    = count($data['hourlyForecastGroup']->hourlyForecast); # echo '$cnt    ='.$cnt; exit;
$dtls_arr       = array();
for ($n = 0; $n < $cnt; $n++)
     {  $fct_arr                = array();
        $fct                    = $data['hourlyForecastGroup']->hourlyForecast[$n];
        $fct_arr['time']        = (int)    $fct['dateTimeUTC'];
        $fct_arr['condtext']    = (string) $fct->condition;
        $fct_arr['condicon']    = (string) $fct->iconCode;        
        $fct_arr['temperature'] = (int)    $fct->temperature;
        $fct_arr['chill']       = (int)    $fct->windChill;
        $fct_arr['humidex']     = (int)    $fct->humidex;
        $fct_arr['windspeed']   = (int)    $fct->wind->speed;
        $fct_arr['windunit']    = (string) $fct->wind->speed['units'];
        $fct_arr['windgust']    = (int)    $fct->wind->gust;
        $fct_arr['winddir']     = (string) $fct->wind->direction;       
        $fct_arr['lop']         = (int)    $fct->lop;
        $fct_arr['loptext']     = (string) $fct->lop['category'];
        $dtls_arr[$n]   = $fct_arr;
        }  
#
return true;
#echo '<pre>'.print_r ($ccn_arr,true).print_r ($fcts_arr[0],true).print_r ($dtls_arr[0],true)   ; exit;
#
# ----------------------------------------------
#                               F U N C T I O NS 
function ECloadFromCache($cacheFile)
     {  global $stck_lst, $cacheAllowed, $EC_filetime;
        $file_time      = 0;
        if (!file_exists($cacheFile))
             {  $stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')  Forecast data ('.$cacheFile.') not found in cache'.PHP_EOL;
                return '';}
        if (isset ($_REQUEST['force']) && strtolower($_REQUEST['force']) == 'ec') 
             {	$stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')  Forecast data ('.$cacheFile.') skipped from cache as force was used'.PHP_EOL;
                return '';}
        $file_time      = filemtime($cacheFile);    ## 2020-01-04  modified time
        $now            = time();
        $diff           = ($now     -   $file_time);
        $stck_lst      .=  basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')  Forecast data ('.$cacheFile.') :
        cache time   = '.date('c',$file_time).' from unix time '.$file_time.'
        current time = '.date('c',$now).' from unix time '.$now.' 
        difference   = '.$diff.' (seconds)
        diff allowed = '.$cacheAllowed.' (seconds)'.PHP_EOL;	
        if ($diff <= $cacheAllowed)
             {  $EC_filetime    = $file_time;
                $stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')   Forecast data ('.$cacheFile.') loaded from cache'.PHP_EOL;
                $rawData        =  file_get_contents($cacheFile);
                return $rawData;}
        
} // eof ECloadFromCache
#
#  
function EC_check_xml()
     {  global $stck_lst, $rawData;
	$data_ok= true;
	$data   = trim($rawData);		
	libxml_use_internal_errors(true);
	libxml_clear_errors();
	$doc    = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($data);
	$errors = libxml_get_errors();
	unset ($doc);
	if(!empty($errors))
	     {  foreach(libxml_get_errors() as $error) 
	             { $stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.') rawData error '.trim($error->message).PHP_EOL;}
		return false;}
#
        libxml_clear_errors();
        $data = new SimpleXMLElement(trim($rawData) );
        if ($data === false) 
             {  $stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')  errors processing xml ';
                foreach(libxml_get_errors() as $error) 
                     {  $stck_lst .= basename(__FILE__).' => '.__FUNCTION__.' ('.__LINE__.')  error '.$error->message.PHP_EOL;}
                libxml_clear_errors();
                return false;}
#
	return (array) $data;	
} // eo EC_check_xml
#
#
function ECfound($haystack, $needle){
$pos = strpos($haystack, $needle);
   if ($pos === false) 
        { return false;} 
   else { return true;}
}  // eof wsFound
#
#
function EC_icon ($key)
     {  global  $ecLookup;
        $icon_nr = trim($key);
        if (!array_key_exists($icon_nr,$ecLookup) )
              { $return = 'pws_icons/unknown.svg'; }
        else  { $return = 'pws_icons/'.$ecLookup[$icon_nr].'.svg'; }
        return $return;}
#
#
function EC_load_arrays () 
   {    global $ecLookup, 
        $ecWarningTypes, $ecWarningPriorities, $ecWarningColors, 
        $ec_french_winddir,
        $ec_french_precip;
        $ecLookup = array(
# 
0 => 'clear_day',       # Sunny,Day Conditions Only
1 => 'few_day',         # A few clouds,Day Conditions Only
2 => 'pc_day',          # A mix of sun and cloud,Day Conditions Only 
3 => 'pc_day',          # Cloudy periods,Day Conditions Only 
4 => 'mc',              # Increasing cloudiness,Day Conditions Only
5 => 'pc_day',          # Clearing,Day Conditions Only
6 => 'mc_rain',         # Chance of showers,Day Conditions Only
7 => 'mc_flurries',     # A few flurries or rain showers,Day Conditions Only
8 => 'mc_flurries',     # A few flurries,Day Conditions Only
9 => 'ovc_thun_rain_dark', # Chance of showers at times heavy or thundershowers,Day Conditions Only
# 
10 => 'ovc',            # Cloudy,Day and Night Conditions
11 => 'ovc_rain_dark',  # Precipitation,Day and Night Conditions
12 => 'ovc_rain_dark',  # Showers,Day and Night Conditions
13 => 'ovc_rain_dark',  # Rain,Day and Night Conditions
14 => 'ovc_sleet',      # Chance of freezing rain,Day and Night Conditions
15 => 'ovc_sleet',      # A few rain showers or flurries,Day and Night Conditions
16 => 'ovc_flurries',   # Flurries,Day and Night Conditions
17 => 'ovc_flurries',   # Snow at times heavy,Day and Night Conditions
18 => 'ovc_flurries_dark', # Blizzard,Day and Night Conditions
19 => 'ovc_thun_dark',   # A few showers or thundershowers,Day and Night Conditions
# 
20 => 'ovc',            #  ?
21 => 'ovc',            #  ?
22 => 'pc_day',         # A mix of sun and cloud,Day and Night Conditions
23 => 'mc_fog',         # Haze,Day and Night Conditions
24 => 'mc_fog',         # Fog,Day and Night Conditions
25 => 'ovc_flurries',   # Drifting Snow,Day and Night Conditions
26 => 'ovc_sleet',      # Ice Crystals,Day and Night Conditions   ?
27 => 'ovc_sleet',      # A few flurries mixed with ice pellets,Day and Night Conditions
28 => 'ovc_sleet',      # Possibility of drizzle mixed with freezing drizzle,Day and Night Conditions
29 => 'offline',        # Not available,Day and Night Conditions
# 
30 => 'clear_night',    # Clear,Night Conditions Only
31 => 'few_night',      # A few clouds,Night Conditions Only
32 => 'few_night',      # Cloudy periods,Night Conditions Only
33 => 'mc_night',       # Cloudy,Night Conditions Only
34 => 'mc_night',       # Increasing cloudiness,Night Conditions Only
35 => 'mc_night',       # Clearing,Night Conditions Only
36 => 'mc_rain_dark',   # Chance of drizzle or rain,Night Conditions Only
37 => 'ovc_flurries_dark', # A few flurries or showers,Night Conditions Only
38 => 'ovc_flurries_dark', # A few flurries,Night Conditions Only
39 => 'ovc_thun_dark',   # Chance of showers at times heavy or thunderstorms,Night Conditions Only
#
40 => 'ovc_flurries_dark', # Snow and blowing snow,Day and Night Conditions
41 => 'tornado',        # Funnel Cloud,Day and Night Conditions
42 => 'tornado',        # Tornado,Day and Night Conditions
43 => 'mc_windy',       # Windy,Day and Night Conditions
44 => 'mc_fog',         # Smoke,Day and Night Conditions
# 45 and higher only for CCN
45 => 'dust',           # Dust Devils,Day and Night Conditions
46 => 'ovc_thun_rain_dark', # Thunderstorm with Hail,Day and Night Conditions
47 => 'ovc_thun_rain_dark', # Thunderstorm with Blowing Dust,Day and Night Conditions
48 => 'ovc_thun_rain_dark', # Waterspout,Day and Night Conditions
);
$ecWarningTypes			= array ();
$ecWarningTypes['ended']	= -1;
$ecWarningTypes['advisory']	= 2;
$ecWarningTypes['warning']	= 1;
$ecWarningTypes['watch']	= 0;
$ecWarningTypes['statement']	= 0;
$ecWarningPriorities		= array ();
$ecWarningPriorities['low']	= 0;
$ecWarningPriorities['medium']	= 1;
$ecWarningPriorities['high']    = 2;
$ecWarningPriorities['urgent']	= 3;
$ecWarningColors		= array ('Yellow', 'Orange', 'Red');

$ec_french_winddir              = array ();
$ec_french_winddir['E']         = 'E';
$ec_french_winddir['ENE']       = 'ENE';
$ec_french_winddir['ESE']       = 'ESE';
$ec_french_winddir['Est']       = 'East';
$ec_french_winddir['N']         = 'N';
$ec_french_winddir['NE']        = 'NE';
$ec_french_winddir['NNE']       = 'NNE';
$ec_french_winddir['NNO']       = 'NNW';
$ec_french_winddir['NO']        = 'NW';
$ec_french_winddir['Nord']      = 'North';
$ec_french_winddir['S']         = 'S';
$ec_french_winddir['SE']        = 'SE';
$ec_french_winddir['SSE']       = 'SSE';
$ec_french_winddir['SSO']       = 'SSW';
$ec_french_winddir['SO']        = 'SW';
$ec_french_winddir['Sud']       = 'South';
$ec_french_winddir['O']         = 'W';
$ec_french_winddir['ONO']       = 'WNW';
$ec_french_winddir['OSO']       = 'WSW';
$ec_french_winddir['Ouest']     = 'West';
}
