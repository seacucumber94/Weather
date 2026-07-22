<?php  $scrpt_vrsn_dt  = 'PWS_blocks.php|01|2021-07-30|';  # multi AQ-station + popup-not + extra blocks + soil block header | release 2012_lts
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
#-----------------------------------------------
$stck_lst       .= basename(__FILE__).'('.__LINE__.')  loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
$graphs_txt     = lang('Graphs');
$lihgtning_txt  = lang('Lightning');

$year_txt       = ' 360 '.lang('days') ;
$month_txt      = ' 30 '.lang('days');
$today_txt      = lang('Today');
$fct_txt        = lang('Forecast');
$AQ_txt         = lang('Air Quality');
$hist_txt       = lang('History');
$metar_txt      = lang('Airport');
$quake_txt      = lang('Earthquakes');
$map_txt        = lang('Map');
$radar_txt      = lang('Radar');
$moon_txt       = lang('Moon info');
$aurora_txt     = lang('Aurora');
$meteors_txt    = lang('Meteors');
$cam_txt        = lang('Enlarge');
$movie_txt      = lang('Movie');
$uv_guide_txt   = lang('UV Guide');
$extra_snsrs    = lang('Extra sensors');
$fct_daily      = lang('Daily');
$fct_hourly     = lang('Hourly');
$fct_page       = lang('Full page');
$fct_details    = lang('Details');
$fct_texts      = lang('Texts');
#
$head_baro      = lang('Barometer').' - '.lang($weather["barometer_units"]);
$head_temp      = lang('Temperature')        .' &deg;'.$weather['temp_units'];
$head_temp_smll = lang('Max-Min Temperature').' &deg;'.$weather['temp_units'];
$head_temp_in   = lang('Indoor Temperature') .' &deg;'.$weather["temp_units"];
$head_rain      = lang('Rainfalltoday').' - '.lang($weather["rain_units"]); 
$head_rain_smll = lang('Annual Rainfall');
$head_wind      = lang('Wind') .' | '. lang('Gust').' - '.lang($weather["wind_units"]); 
$head_wind_smll = lang('Max').' '.$head_wind ;
$head_lightning = lang('Lightning');
$head_quake     = lang('Earthquake');
$head_quake_smll= lang('Last Earthquake');
$head_ccn       = lang('Currentsky');
$head_sun       = lang('SunPosition');
$head_moon      = lang('Moonphase information');
$head_webcam    = lang('LiveWebCam');
$head_extr_smll = lang('Extra block');
$head_extr_blck = lang('Extra block large');
$head_temp_soil = lang('Soil info');
$head_uv_solar  = lang('Solar - UV-Index - Lux');
$head_AQ_prpl   = lang('Our PurpleAir sensor');
$head_AQ_lftdtn = lang('Our Luftdaten AQ sensor');
$head_AQ_fficl  = lang('Official AQ sensor station');
$head_AQ_cwtt   = lang('Our station AQ sensor');
$head_AQ_davis  = lang('Our Davis AQ sensor');
$head_fct       = lang('Forecast'); // used for DarkSky
$head_fct_wu    = lang('WeatherUnderground forecast');
$head_fct_wxsim = lang('Our WXSIM forecast');
$head_fct_ec    = lang('Environment Canada forecast');
#
# refresh times for the blocks  !  not for the data loads
$rfrsh_temp     = 110;
$rfrsh_rain     = 90;
$rfrsh_wind     = 40;
$rfrsh_baro     = 190;
$rfrsh_lightning= 120;
$rfrsh_quakes   = 250;
$rfrsh_sun_moon = 280;
$rfrsh_uv_solar = 110;
$rfrsh_webcam   = 120;
$rfrsh_AQ       = 300;
$rfrsh_CCN      = 180;  
$rfrsh_fct      = 1800;
$rfrsh_fct_DS   = 500;
$rfrsh_others   = 110;
$rfrsh_small    = 100;  #extra wait time for high-low small blocks
#
$blck_ttls      = array();      // title of a block
$blck_rfrs      = array();      // refresh specific for this block IN SECONDS
$blck_ppp       = array();      // available popups
$blck_setup     = array();      // for easyweather
$blck_type      = array();      // to select which block where
#
$script                 = 'advisory_c_small.php';
$blck_ttls[$script]     = '';
$blck_rfrs[$script]     = $rfrsh_others;  
$blck_setup[$script]    = '';
$blck_type[$script]     = 'x';  
#
$script                 = 'clock_c_small.php';    
$blck_ttls[$script]     = '';
$blck_rfrs[$script]     = false;                // needs no refresh
$blck_setup[$script]    = '';
$blck_type[$script]     = 'x'; 
#
$script                 = 'earthquake_c_small.php';
$blck_ttls[$script]     = $head_quake_smll;
$blck_rfrs[$script]     = $rfrsh_quakes;  
$blck_setup[$script]    = 'Earthquakes';  
$blck_type[$script]     = 's'; 
#
$script                 = 'extra_tmp_c_small.php';
$blck_ttls[$script]     = $head_extr_smll;
$blck_rfrs[$script]     = $rfrsh_small + $rfrsh_temp;; 
$blck_setup[$script]    = 'Extra temp-hum';  
$blck_type[$script]     = 's'; 
#
$script                 = 'lightning_boltek_small.php';
$blck_ttls[$script]     = $head_lightning;
$blck_rfrs[$script]     = $rfrsh_lightning;  
$blck_setup[$script]    = 'Lightning detection using your Boltek sensor';  
$blck_type[$script]     = 's'; 
#
$script                 = 'lightning_station_small.php';
$blck_ttls[$script]     = $head_lightning;
$blck_rfrs[$script]     = $rfrsh_lightning;  
$blck_setup[$script]    = 'Lightning detector from your station';  
$blck_type[$script]     = 's'; 
#
$script                 = 'lightning_wf_small.php';
$blck_ttls[$script]     = $head_lightning;
$blck_rfrs[$script]     = $rfrsh_lightning;  
$blck_setup[$script]    = 'Lightning detection using your WeatherFlow device';  
$blck_type[$script]     = 's'; 
#
$script                 = 'rain_c_small.php';
$blck_ttls[$script]     = $head_rain_smll;
$blck_rfrs[$script]     = $rfrsh_small + $rfrsh_rain;
$blck_setup[$script]    = 'Rainfall year - month';  
$blck_type[$script]     = 's'; 
#
$script                 = 'sky_small.php';
$blck_ttls[$script]     = $head_ccn;  
$blck_rfrs[$script]     = $rfrsh_CCN;  
$blck_setup[$script]    = 'Sky conditions';  
$blck_type[$script]     = 's'; 
#
$script                 = 'soil_tmp_mst_small.php';      // max min temp
$blck_ttls[$script]     = $head_temp_soil; #$head_temp_small;
$blck_rfrs[$script]     = 500; #$rfrsh_small + $rfrsh_temp;  
$blck_setup[$script]    = 'Soil information'; 
$blck_type[$script]     = 's'; 
#
$script                 = 'temp_c_small.php';      // max min temp
$blck_ttls[$script]     = $head_temp_smll; #$head_temp_small;
$blck_rfrs[$script]     = $rfrsh_small + $rfrsh_temp;  
$blck_setup[$script]    = 'Maximum/Mininimum temperatures';
$blck_type[$script]     = 's'; 
#
$script                 = 'wind_c_small.php';
$blck_ttls[$script]     = $head_wind_smll;  
$blck_rfrs[$script]     = $rfrsh_small + $rfrsh_wind;  
$blck_setup[$script]    = 'Wind-gust max today';  
$blck_type[$script]     = 's'; 
# 
if ($itsday)  { $itsnght= false;
                $headxx = $head_uv_solar ;
                $rfrshxx= $rfrsh_uv_solar;} 
else          { $itsnght= true;
                $headxx = $head_moon ;
                $rfrshxx= $rfrsh_sun_moon;} 

$script = 'baro_c_block.php';
$blck_ttls[$script]     = $head_baro;
$blck_rfrs[$script]     = $rfrsh_baro; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'PWS_graph_xx.php?type=baro',    'chartinfo' => 'popup',  'text' => $graphs_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'fct_windy_popup.php',           'chartinfo' => 'popup',  'text' => $fct_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=baro_ao' ,   'chartinfo' => 'popup',  'text' => $map_txt  );
$blck_setup[$script]    = 'Barometer information block';
$blck_type[$script]     = 'b'; 

$script = 'sky_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_rfrs[$script]     = $rfrsh_CCN; 
$blck_ppp [$script][]   = array ('show' => true,          'popup' => 'history_popup.php',       'chartinfo' => 'popup',  'text' => $hist_txt );
$blck_ppp [$script][]   = array ('show' => 'aeris_popup', 'popup' => 'metar_aeris_popup.php',   'chartinfo' => 'popup',  'text' => $metar_txt );
$blck_ppp [$script][]   = array ('show' => 'metar_popup', 'popup' => 'metar_popup.php',         'chartinfo' => 'popup',  'text' => $metar_txt );
$blck_ppp [$script][]   = array ('show' => true,          'popup' => 'earthquake_c_popup.php',  'chartinfo' => 'popup',  'text' => $quake_txt  );
$blck_ppp [$script][]   = array ('show' => true,          'popup' => 'image_popup.php?nr=bo',   'chartinfo' => 'popup',  'text' => $lihgtning_txt  );
$blck_setup[$script]    = 'Current weather / Sky conditions';
$blck_type[$script]     = 'b'; 
# following ccn blocks will be loaded by sky blocks
$script = 'ccn_metar_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_setup[$script]    = 'Default Current weather / Sky conditions using METAR (needs API key)';
$blck_type[$script]     = 'c'; 
$script = 'ccn_aeris_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_setup[$script]    = 'Current weather / Sky conditions by Aeris (needs API key)';
$blck_type[$script]     = 'c'; 
$script = 'ccn_cltraw_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_setup[$script]    = 'Current weather / Sky conditions from your clientraw file';
$blck_type[$script]     = 'c'; 
$script = 'ccn_darksky_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_setup[$script]    = 'Current weather / Sky conditions  from Darksky (needs API key)';
$blck_type[$script]     = 'c'; 
$script = 'ccn_ec_block.php';
$blck_ttls[$script]     = $head_ccn;
$blck_setup[$script]    = 'Current weather / Sky conditions from Environment Canada (Canada only)';
$blck_type[$script]     = 'c'; 
#
$fct = 'fct_block.php';
$blck_ttls[$fct]        = $head_fct;
$blck_rfrs[$fct]        = $rfrsh_fct;
$blck_setup[$fct]       = 'Your weather-forecast';
$blck_type[$fct]        = 'b'; 
# following ccn blocks will be loaded by fct_block.php
$script = 'fct_yrno_block.php';
$blck_ttls[$script]     = $head_fct;
$blck_setup[$script]    = 'Default forecast using free Yr.no  data';
$blck_type[$script]     = 'f';
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_yrno_popup.php',         'chartinfo' => 'popup',  'text' => $fct_details  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_yrno_popup_hrs.php',     'chartinfo' => 'popup',  'text' => $fct_hourly  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'yrnoPP' ,                    'chartinfo' => 'page',   'text' => $fct_page  );
if ($fct_default == $script) { 
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_yrno_popup.php',         'chartinfo' => 'popup',  'text' => $fct_details  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_yrno_popup_hrs.php',     'chartinfo' => 'popup',  'text' => $fct_hourly  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'yrnoPP' ,                    'chartinfo' => 'page',   'text' => $fct_page  );
}
$script = 'fct_aeris_block.php';
$blck_ttls[$script]     = $head_fct;
$blck_setup[$script]    = 'Forecast using Aeris data (needs API key)';
$blck_type[$script]     = 'f'; 
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_aeris_popup.php',        'chartinfo' => 'popup',  'text' => $fct_daily );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_aeris_popup_hrs.php',    'chartinfo' => 'popup',  'text' => $fct_hourly );
if ($fct_default == $script) {
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_aeris_popup.php',        'chartinfo' => 'popup',  'text' => $fct_daily );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_aeris_popup_hrs.php',    'chartinfo' => 'popup',  'text' => $fct_hourly );
}
$script = 'fct_darksky_block.php';
$blck_ttls[$script]     = $head_fct;
$blck_setup[$script]    = 'Forecast using DarkSky data (needs API key)';
$blck_type[$script]     = 'f'; 
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_darksky_popup_daily.php',  'chartinfo' => 'popup',  'text' => $fct_daily );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_darksky_popup_hourly.php', 'chartinfo' => 'popup',  'text' => $fct_hourly  );
if ($fct_default == $script) {
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_darksky_popup_daily.php',  'chartinfo' => 'popup',  'text' => $fct_daily );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_darksky_popup_hourly.php', 'chartinfo' => 'popup',  'text' => $fct_hourly  );
}
$script = 'fct_ec_block.php';
$blck_ttls[$script]     = $head_fct;
$blck_setup[$script]    = 'Forecast using free Environment Canada  data (Canada only)';
$blck_type[$script]     = 'f'; 
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_ec_popup_daily.php',     'chartinfo' => 'popup',  'text' => $fct_daily  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_ec_popup_text.php',      'chartinfo' => 'popup',  'text' => $fct_texts  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_ec_popup_hourly.php',    'chartinfo' => 'popup',  'text' => $fct_hourly  );
if ($fct_default == $script) {
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_ec_popup_daily.php',     'chartinfo' => 'popup',  'text' => $fct_daily  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_ec_popup_text.php',      'chartinfo' => 'popup',  'text' => $fct_texts  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_ec_popup_hourly.php',    'chartinfo' => 'popup',  'text' => $fct_hourly  );
}
$script = 'fct_wu_block.php';
$blck_ttls[$script]     = $head_fct;
$blck_setup[$script]    = 'Forecast using WeatherUnderground data (needs API key)';
$blck_type[$script]     = 'f'; 
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_wu_popup_daily.php',     'chartinfo' => 'popup',  'text' => $fct_details  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_wu_popup_text.php',      'chartinfo' => 'popup',  'text' => $fct_texts  );
if ($fct_default == $script) {
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_wu_popup_daily.php',     'chartinfo' => 'popup',  'text' => $fct_details  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_wu_popup_text.php',      'chartinfo' => 'popup',  'text' => $fct_texts  );
}
$script = 'fct_wxsim_block.php';
$blck_ttls[$script]     = $head_fct;  ## 2020-12-19
$blck_setup[$script]    = 'Forecast using WXSIM data from extra PC-program data';
$blck_type[$script]     = 'f'; 
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_wxsim_popup_daily.php',  'chartinfo' => 'popup',  'text' => $fct_details  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'fct_wxsim_popup_text.php',   'chartinfo' => 'popup',  'text' => $fct_texts  );
$blck_ppp [$script][]   = array ('show' => true,        'popup' => 'wxsimPP' ,                                    'chartinfo' => 'page',   'text' => $fct_page  );
if ($fct_default == $script) {
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_wxsim_popup_daily.php',  'chartinfo' => 'popup',  'text' => $fct_details  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'fct_wxsim_popup_text.php',   'chartinfo' => 'popup',  'text' => $fct_texts  );
        $blck_ppp [$fct][]      = array ('show' => true,'popup' => 'wxsimPP' ,                                    'chartinfo' => 'page',   'text' => $fct_page  );
}

$script = 'earthquake_c_block.php';
$blck_ttls[$script]     = $head_quake;
$blck_rfrs[$script]     = $rfrsh_quakes; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'earthquake_c_popup.php',  'chartinfo' => 'popup',  'text' => $quake_txt  );
$blck_setup[$script]    = 'Earthquake information';
$blck_type[$script]     = 'b'; 

$script = 'extra_temp_block.php';
$blck_ttls[$script]     = $head_extr_blck;
$blck_rfrs[$script]     = $rfrsh_small + $rfrsh_temp;; 
$blck_setup[$script]    = 'Extra temp-hum sensors if available from your station';
$blck_type[$script]     = 'b'; 

$script = 'AQ_davis_c_block.php';
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ttls[$script]     = $head_AQ_davis;
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_davis_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',            'chartinfo' => 'popup',  'text' => $map_txt );
$blck_setup[$script]    = 'AQ sensor by Davis'; 
$blck_type[$script]     = 'b'; 

$script = 'AQ_gov_c_block.php';
$blck_ttls[$script]     = $head_AQ_fficl;
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_gov_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'airqualityPP' ,                       'chartinfo' => 'page',   'text' => $fct_page  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',           'chartinfo' => 'popup',  'text' => $map_txt );
$blck_setup[$script]    = 'AQ data from nearby official station'; 
$blck_type[$script]     = 'b'; 

$script = 'AQ_luftdaten_c_block.php';
$blck_ttls[$script]     = $head_AQ_lftdtn;
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_luftdaten_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'airqualityLD' ,                       'chartinfo' => 'page',   'text' => $map_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',           'chartinfo' => 'popup',  'text' => $map_txt.'-2');
$blck_setup[$script]    = 'AQ from Luftdaten  sensor website';
$blck_type[$script]     = 'b'; 

$script = 'AQ_luftdaten2_c_block.php';
$blck_ttls[$script]     = $head_AQ_lftdtn;
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_luftdaten2_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'airqualityLD' ,                       'chartinfo' => 'page',   'text' => $map_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',           'chartinfo' => 'popup',  'text' => $map_txt.'-2');
$blck_setup[$script]    = 'AQ from Luftdaten  sensor local storage';
$blck_type[$script]     = 'b'; 

$script = 'AQ_purpleair_c_block.php';
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ttls[$script]     = $head_AQ_prpl;
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_purpleair_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'airqualityPU' ,                       'chartinfo' => 'page',   'text' => $map_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',           'chartinfo' => 'popup',  'text' => $map_txt );
$blck_setup[$script]    = 'AQ from your Purpleair AQ sensor';
$blck_type[$script]     = 'b'; 

$script = 'AQ_station_block.php';
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ttls[$script]     = $head_AQ_cwtt;
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_station_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',            'chartinfo' => 'popup',  'text' => $map_txt );
$blck_setup[$script]    = 'AQ data from your weather-station';
$blck_type[$script]     = 'b'; 
#
$script = 'AQ_station_block2.php';
$blck_rfrs[$script]     = $rfrsh_AQ; 
$blck_ttls[$script]     = lang('Our station AQ sensor 2');
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'AQ_station_popup.php',       'chartinfo' => 'popup',  'text' => $AQ_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=aq_map',            'chartinfo' => 'popup',  'text' => $map_txt );
$blck_setup[$script]    = 'AQ-second sensor from your weather-station';
$blck_type[$script]     = 'b'; 
#
$script = 'indoor_c_block.php';
$blck_ttls[$script]     = $head_temp_in;
$blck_rfrs[$script]     = $rfrsh_temp; 
$blck_setup[$script]    = 'Indoor Temp- Hum sensor';
$blck_type[$script]     = 'b'; 

$script = 'moon_c_block.php';
$blck_ttls[$script]     = $head_moon;
$blck_rfrs[$script]     = $rfrsh_sun_moon;
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'moon_popup.php',     'chartinfo' => 'popup',  'text' => $moon_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'meteors_popup.php',  'chartinfo' => 'popup',  'text' => $meteors_txt  );
$blck_setup[$script]    = 'Moonphase information';
$blck_type[$script]     = 'b'; 

$script = 'rain_c_block.php';
$blck_ttls[$script]     = $head_rain;
$blck_rfrs[$script]     = $rfrsh_rain; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'PWS_graph_xx.php?type=rain', 'chartinfo' => 'popup',  'text' => $graphs_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'fct_windy_popup.php',        'chartinfo' => 'popup',   'text' => $fct_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=rain',    'chartinfo' => 'popup',   'text' => $radar_txt  );
$blck_setup[$script]    = 'Rain information';
$blck_type[$script]     = 'b'; 

$script = 'sun_c_block.php';
$blck_ttls[$script]     = $head_sun;
$blck_rfrs[$script]     = $rfrsh_sun_moon; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'moon_popup.php',              'chartinfo' => 'popup',  'text' => $moon_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'aurora_popup.php',            'chartinfo' => 'popup',  'text' => $aurora_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'meteors_popup.php',           'chartinfo' => 'popup',  'text' => $meteors_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=earth',    'chartinfo' => 'popup',  'text' => $map_txt  );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => '_my_settings/iss_popup.php',  'chartinfo' => 'popup',  'text' => 'ISS' );
$blck_setup[$script]    = 'Sun information';
$blck_type[$script]     = 'b'; 

$script = 'temp_c_block.php';
$blck_ttls[$script]     = $head_temp;
$blck_rfrs[$script]     = $rfrsh_temp; 
$blck_ppp [$script][]   = array ('show' => true,     'popup' => 'PWS_graph_xx.php?type=temp',   'chartinfo' => 'popup',  'text' => $graphs_txt );
$blck_ppp [$script][]   = array ('show' => true,     'popup' => 'fct_windy_popup.php',          'chartinfo' => 'popup',  'text' => $fct_txt );
$blck_ppp [$script][]   = array ('show' => 'DWL',    'popup' => 'WLCOM_popup.php',              'chartinfo' => 'popup',  'text' => $extra_snsrs );
$blck_setup[$script]    = 'Temperature information';
$blck_type[$script]     = 'b'; 

$script = 'uv_moon_block.php';
$blck_ttls[$script]     = $headxx;
$blck_rfrs[$script]     = $rfrshxx; 
$blck_ppp [$script][]   = array ('show' => 'itsday',  'popup' => 'uvsolarlux_popup.php',        'chartinfo' => 'popup',  'text' => $uv_guide_txt  );
$blck_ppp [$script][]   = array ('show' => false,     'popup' => 'image_popup.php?nr=uv_map',   'chartinfo' => 'popup',  'text' => $map_txt  );
$blck_ppp [$script][]   = array ('show' => 'itsnght', 'popup' => 'moon_popup.php',              'chartinfo' => 'popup',  'text' => $moon_txt  );
$blck_ppp [$script][]   = array ('show' => 'itsnght', 'popup' => 'meteors_popup.php',           'chartinfo' => 'popup',  'text' => $meteors_txt  );
$blck_setup[$script]    = 'UV-info in daytime - Moon-info at night';
$blck_type[$script]     = 'b'; 

$script = 'uvsolarlux_c_block.php';
$blck_ttls[$script]     = $head_uv_solar;
$blck_rfrs[$script]     = $rfrsh_uv_solar; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'uvsolarlux_popup.php',  'chartinfo' => 'popup',  'text' => $uv_guide_txt  );
$blck_setup[$script]    = 'UV , Solar + Lux from your station, Weatherflow or Darksky';
$blck_type[$script]     = 'b'; 

$script = 'webcam_c_block.php';
$blck_ttls[$script]     = $head_webcam;
$blck_rfrs[$script]     = $rfrsh_webcam; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=wcam1',            'chartinfo' => 'popup',    'text' => $cam_txt  );
$blck_ppp [$script][]   = array ('show' => false,     'popup' => '_my_settings/webfilm_popup.php',    'chartinfo' => 'popup',    'text' => $movie_txt  );
$blck_setup[$script]    = 'Webcam: add url of your webcam to webcam_c_block.php';
$blck_type[$script]     = 'b'; 

$script = 'webcam2_c_block.php';
$blck_ttls[$script]     = $head_webcam;
$blck_rfrs[$script]     = $rfrsh_webcam; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'image_popup.php?nr=wcam2',          'chartinfo' => 'popup',    'text' => $cam_txt  );
$blck_ppp [$script][]   = array ('show' => false,     'popup' => '_my_settings/webfilm_popup.php',  'chartinfo' => 'popup',    'text' => $movie_txt  );
$blck_setup[$script]    = 'Second webcam script webcam2_c_block.php';
$blck_type[$script]     = 'b'; 

$script = 'webcam3_c_block.php';
$blck_ttls[$script]     = $head_webcam;
$blck_rfrs[$script]     = $rfrsh_webcam; 
$blck_ppp [$script][]   = array ('show' => true,       'popup' => 'image_popup.php?nr=wcam3',           'chartinfo' => 'popup',    'text' => $cam_txt  );
$blck_ppp [$script][]   = array ('show' => false,      'popup' => '_my_settings/webfilm_popup.php',   'chartinfo' => 'popup',    'text' => $movie_txt  );
$blck_setup[$script]    = 'Third webcam script webcam3_c_block.php';
$blck_type[$script]     = 'b'; 

$script = 'wind_c_block.php';
$blck_ttls[$script]     = $head_wind;  
$blck_rfrs[$script]     = $rfrsh_wind; 
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'PWS_graph_xx.php?type=wind',  'chartinfo' => 'popup',  'text' => $graphs_txt );
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'fct_windy_popup.php',         'chartinfo' => 'popup',  'text' => $fct_txt );
$blck_setup[$script]    = 'Wind - Gust information';
$blck_type[$script]     = 'b'; 

$script = 'soil_tmp_mst.php';
$blck_ttls[$script]     = $head_temp_soil;   // 2020-12-19 
$blck_rfrs[$script]     = $rfrsh_fct;       // not very fast cahnging values
$blck_setup[$script]    = 'Soil-moist-temp sensors of your station';   // used in easyweather setup
$blck_type[$script]     = 'b';              // also used in easuweather
$blck_ppp [$script][]   = array ('show' => true,      'popup' => 'soil_tmp_mst_popup.php',     'chartinfo' => 'popup',  'text' => 'Soil moist and temp info' );

$file_name      = './_my_settings/extra_blocks.txt';
if (!is_file($file_name) ) {return;}
$stck_lst       .= basename(__FILE__).'('.__LINE__.')  processing  =>'.$file_name.PHP_EOL;
$extra  = file($file_name);
$script = '';
foreach ($extra as $n => $line)
     {  $first  = substr($line,0,1);
        if ($first == '#' || trim($first == '') ) {continue;}
        list ($key,$value)      = explode ('=',$line.'=');
        $key    = trim($key); 
        list ($value,$none)     = explode ('#',$value.' #');
        $value  = trim($value);
        if ($script == '' && $key <> 'script')
             {  $stck_lst       .= basename(__FILE__).'('.__LINE__.')  invalid data  =>'.$key.' '.$value.PHP_EOL;
                $continue;}
        if ($key == 'end' ) 
             {  $script = '';
                continue; }
        if ($key == 'script' ) 
             {  if (!is_file($value) )
                     {  $stck_lst       .= basename(__FILE__).'('.__LINE__.')  script not found  =>'.$value.PHP_EOL;
                        continue;}
                $script = $value;                       
                $popup  = '';
                continue;}
        if ($key == 'title' )  {$blck_ttls[$script]     = lang($value); continue;}
        if ($key == 'refresh') {$blck_rfrs[$script]     = $value;       continue;}
        if ($key == 'setup' )  {$blck_setup[$script]    = $value;       continue;}
        if ($key == 'type' )   {$blck_type[$script]     = $value;       continue;}
        if (substr($key,0,5) <> 'popup')
             {  $stck_lst       .= basename(__FILE__).'('.__LINE__.')  invalid data  =>'.$key.' '.$value.' '.$n.PHP_EOL;
                $continue;}
        $nr     = (int) substr($key,-1);
        $popup  = substr($key,0,-1);
        if ($popup == 'popup-lnk') {$link = $value;                     continue;}
        if ($popup == 'popup_txt') 
             {  $blck_ppp [$script][$nr]= array ('show' => true, 'popup' => $link, 'chartinfo' => 'popup',  'text' => $value );}
        if ($popup == 'popup_not') 
             {  $blck_ppp [$script][$nr]= array ('show' => false, 'popup' => 'none', 'chartinfo' => 'popup',  'text' => 'none' );}
        continue;
        }
