<?php $scrpt_vrsn_dt  = 'fct_darksky_shared.php|01|2023-02-15|';  #  switch to OWM & Pirate weather & VirtualCrossing + PHP 8.1 + no summary + chek file | release 2012_lts
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
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;     
#
// darksky api forecast and current conditions script 
//  gets data from jsondata/darksky.txt
#
$fl_drksk       = $fl_folder.$drksk_fl; #### 2021-05-25
if (! file_exists ($fl_drksk)  ) 
     {  echo '<p style="color: red;">No valid data-file found</p>';
        return false;}                  #### 2021-05-25
$json           = file_get_contents($fl_folder.$drksk_fl); 
$response       = json_decode($json, true);  #echo '<pre>json data'.print_r($response, true) ; exit;    
$darkskydayCond         = array();
$darkskyhourlyCond      = array();
if ($response != null) {  
        $darksky_used_unit      = $response['flags']['units']; #echo '$darksky_used_unit='.$darksky_used_unit; exit;
        $darksky_used_temp      = 'C';              # currentconditionsDS  forecastDSblock  forecastDSdaily_popup forecastDShourly_popup
        $darksky_used_wind      = 'm/s';
        $darksky_used_rain      = 'mm';
        $darksky_used_snow      = 'mm';
        $darksky_used_baro      = 'hPa';
        if ($darksky_used_unit == 'uk2') 
             {  $darksky_used_wind      = 'mph';}
        elseif ($darksky_used_unit == 'ca') 
             {  $darksky_used_wind      = 'km/h';}
        elseif ($darksky_used_unit == 'us') 
             {  $darksky_used_temp      = 'F';
                $darksky_used_wind      = 'mph';
                $darksky_used_rain      = 'in';
                $darksky_used_snow      = 'in';
                $darksky_used_baro      = 'inHg';}   # currentconditionsDS  forecastDSblock  forecastDSdaily_popup forecastDShourly_popup
#darksky api Current SKY Conditions  
        $darkskycurTime         = $response['currently']['time'];               # time for fct block
        $darkskycurSummary      = $response['currently']['summary'];            # currentconditionsDS
        $darkskycurIcon         = $response['currently']['icon'];               # currentconditionsDS
        $num                    = $response['currently']['temperature'];
        $darkskycurTemp         = convert_temp ($num,$darksky_used_temp,$tempunit);  // not used
        $darkskycurCover        = $response['currently']['cloudCover']*100;     // not used
# darksky UV
        $darkskyhourlyuv        = $response['hourly']['data'][0]['uvIndex'];    # uvsolarlux_block   uvsolarlux_popup
# darksky api Hourly Forecast
        if (array_key_exists ('summary',$response['hourly']) )
             {  $darkskyhourlySummary   = $response['hourly']['summary'];  }    // not
        else {  $darkskyhourlySummary   = '';}   # 2021-12-08
        $darkskyhourlyIcon      = $response['hourly']['icon'];                  // not
        $darkskyhourlyCond      = array();                                      # currentconditionsDS   forecastDShourly_popup  
        foreach ($response['hourly']['data'] as $td) 
             {  $darkskyhourlyCond[] = $td; }
# darksky api Daily Forecast
        if (isset ($response['hourly']['summary']) )
             {  $darkskyTodaySummary = $response['hourly']['summary'];}
        else {  $darkskyTodaySummary = 'n/a';}
        $darkskydaySummary      = $response['daily']['summary'];                // not
        $darkskydayIcon         = $response['daily']['icon'];                   // advisory-small
        $darkskydayCond         = array();                                      // forecastDSblock  forecastDSdaily_popup
        foreach ($response['daily']['data'] as $d) 
             {  $darkskydayCond[] = $d; }
} //end darksky api 
#
if (!isset ($dark_alt_vrs) )   { echo '<small style="color: red">Run easyweather-setup to adepts your settings</small>'; die();}
if     ($dark_alt_vrs == 'nu') 
     {  echo '<small style="color: red">Darksky scripts failure</small>'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Darksky / replacement scripts needed'.PHP_EOL;    
        return false;}
if     ($dark_alt_vrs == 'ds') { $ds_name ='DarkSky';        $ds_href = 'https://darksky.net/';}
elseif ($dark_alt_vrs == 'pw') { $ds_name ='PirateWeather';  $ds_href = 'https://pirateweather.net/';}
elseif ($dark_alt_vrs == 'ow') { $ds_name ='Openweather';    $ds_href = 'https://openweathermap.org/api';}
else                           { $ds_name ='VisualCrossing'; $ds_href = 'https://www.visualcrossing.com/';}
#-----------------------------------------------
#  DSfound
#-----------------------------------------------
function DSfound($haystack, $needle){
$pos = strpos($haystack, $needle);
   if ($pos === false) 
        { return false;} 
   else { return true;}
}  // eof wsFound
#-----------------------------------------------
# Icon translation
#-----------------------------------------------
function DSicon_trns ($icon)
     {  global $stck_lst;
        $DSicon_trns    = array();
        $DSicon_trns['chancerain']      = 'mc_rain';
        $DSicon_trns['clear-day']       = 'clear_day';
        $DSicon_trns['clear-night']     = 'clear_night';
        $DSicon_trns['cloudy']          = 'ovc';
        $DSicon_trns['fog']             = 'haze_day';
        $DSicon_trns['hail']            = 'ovc_sleet';
        $DSicon_trns['nt_chancerain']   = 'mc_rain_dark';
        $DSicon_trns['nt_clear-day']    = 'few_day';
        $DSicon_trns['nt_clear-night']  = 'few_night';
        $DSicon_trns['nt_cloudy']       = 'ovc_dark';
        $DSicon_trns['nt_fog']          = 'ovc_fog';
        $DSicon_trns['nt_hail']         = 'ovc_sleet_dark';
        $DSicon_trns['nt_partly-cloudy-day'] = 'mc';
        $DSicon_trns['nt_partly-cloudy-night'] = 'mc_night';
        $DSicon_trns['nt_rain']         = 'ovc_rain_dark';
        $DSicon_trns['nt_sleet']        = 'ovc_sleet_dark';
        $DSicon_trns['nt_snow']         = 'ovc_flurries_dark';
        $DSicon_trns['nt_thunderstorm'] = 'ovc_thun_dark';
        $DSicon_trns['nt_tornado']      = 'tornado';
        $DSicon_trns['nt_wind']         = 'mc_windy_dark';
        $DSicon_trns['offline']         = 'offline';
        $DSicon_trns['offlineforecast'] = 'offline';
        $DSicon_trns['partly-cloudy-day']= 'pc_day';
        $DSicon_trns['partly-cloudy-night'] = 'few_night';
        $DSicon_trns['rain']            = 'ovc_rain';
        $DSicon_trns['sleet']           = 'ovc_sleet';
        $DSicon_trns['snow']            = 'ovc_flurries';
        $DSicon_trns['thunderstorm']    = 'ovc_thun_dark';
        $DSicon_trns['tornado']         = 'tornado';
        $DSicon_trns['wind']            = 'mc_windy';
        if (!isset ($DSicon_trns[$icon]))
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') unknown icon '.$icon.PHP_EOL;
                $return = 'pws_icons/offline.svg';}
        else {  $return = 'pws_icons/'.$DSicon_trns[$icon].'.svg';}
        return $return;}
#-----------------------------------------------
