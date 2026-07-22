<?php $scrpt_vrsn_dt  = 'fct_aeris_shared.php|01|2023-05-01|';  # space-comma + was missing from zips ? extra checks + pressure + translation | release 2012_lts
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
# Aeris api forecast / daypart
#
#$fl_folder      = './jsondata/';
$ars_fct_fl     = 'aeris_fct_dp.json';  // 7 days or 14 parts format 
#
$json_string    = $data_fct = false;
if (file_exists ($fl_folder.$ars_fct_fl))
     {  $json           = file_get_contents($fl_folder.$ars_fct_fl);
        $data_fct       = json_decode($json, true); }# echo '<pre>json data'.print_r($data_fct, true) ; exit;    
if ($data_fct == null || $data_fct == false) 
     {  echo '<p style="color: red;"><b>invalid data found for </b>'.$ars_fct_fl.'</p>'; 
        return false;}
#
$parts          = array();
$aeris_fct_time = filemtime($fl_folder.$ars_fct_fl);
#
$type           = $data_fct['response'][0]['interval'];  # echo '<pre>'.$type.PHP_EOL;
$periods        = $data_fct['response'][0]['periods'];
#
$valid          = time() - 6*3600; # echo date ('c',$valid).PHP_EOL;
foreach ($periods as $period)
     {  $arr    = array();
        if ( $valid > $period['timestamp']) 
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') record skipped, '.date('c',$period['timestamp']).PHP_EOL; 
                continue;}  // skip to old forecast parts
        $day    = lang(date ('l', $period['timestamp']));
        $isDay  = $period['isDay'];
        if (!$isDay) 
             {  $day .= '<br />'.lang('Night');}
        $arr['isDay']   = $isDay;
        $arr['Utime']   = $period['timestamp'];
        $arr['part']    = $day;
        
        if ($isDay) {   $first  = 'max'; } else { $first  = 'min'; }
        $arr['temp']    = $period[ $first.'Temp'.$tempunit];
        $arr['dewp']    = $period[ 'min'.'Dewpoint'.$tempunit];
        $arr['feel']    = $period[ $first.'Feelslike'.$tempunit];
        $arr['humi']    = $period[ 'humidity'];
        $arr['uvuv']    = $period[ 'uvi'];
        $unit           = strtoupper($rainunit);
        $arr['r_ch']    = $period[ 'pop'];
        $arr['rain']    = $period[ 'precip'.$unit];
        if ($pressureunit == "hPa") { $unit = 'MB'; } else { $unit = 'IN'; } #### 2021-01-10
        $arr['baro']    = $period[ 'pressure'.$unit]; 
        $arr['clds']    = $period[ 'sky'];
        $arr['wdir']    = $period[ 'windDir'];  # "windDir": "WNW",
# KTS KPH  MPH
# --  km/h mph m/s = MPH *   0.44704  
        $unit           = strtoupper (str_replace ('/','',$windunit ));    
        $multiply       = 1;
        if ($unit == 'MS')  { $unit = 'MPH'; $multiply = 0.44704; }
        if ($unit == 'KMH') { $unit = 'KPH'; }
        $arr['wspd']    = round ($period['windSpeed'.   $unit]*$multiply);
        $max            = round ($period['windSpeedMax'.$unit]*$multiply);
        $min            = round ($period['windSpeedMin'.$unit]*$multiply);
        $arr['w_ft']    = (string)$min .'-'.(string) $max;
        $original       = $period['weather'];
        $translated = '';                                       #### 2020-12-09
        $blocks = explode ('with',$original); 
        foreach ($blocks as $key => $block)
             {  $or_BL  = trim ($block);
                $tr_BL  = lang ($or_BL);
                if ($or_BL == $tr_BL)
                     {  $words  = explode (' ',$or_BL); 
                        foreach ($words as $key => $word) { $translated .= lang ($word).' ';}
                        $translated = trim($translated);}
                else {  $translated .= $tr_BL;}
                $translated .= '#';}
        $translated = substr($translated,0,-1);         
        $translated = str_replace ('#',', ',$translated);     # 2023-04-23  #### 2020-12-09                 
        $arr['desc']    = $translated;        
        $arr['icnx']    = $period['icon'];
        $look   = str_replace ('.png','',$period['icon']);
        $arr['icon']    = icon_trns ($look);
        $parts[]        = $arr;            
}
if (count($parts) == 0) 
     {  echo '<p style="color: red;"><b>no valid data found </b>'.$ars_fct_fl.'</p>'; 
        return false;}
$replace= array (lang ('Today'), lang ('Tonight'), lang ('Tomorrow'), lang ('Tomorrow').'<br />'.lang('Night'));
if ($parts[0]['isDay'] == false)  { array_shift($replace);}
if (count($parts) == 0) 
     {  echo '<p style="color: red;"><b>no valid data found </b>'.$ars_fct_fl.'</p>'; 
        return false;}
for ($n = 0; $n < count($replace); $n++) {$parts[$n]['part'] = $replace[$n];}

#echo '<pre>data '.print_r($parts, true) ;
#-----------------------------------------------
# Icon translation
#-----------------------------------------------
function icon_trns ($icon)
     {  global $stck_lst;
        $icn_t['blizzard']      = 'ovc_flurries';
        $icn_t['blizzardn']     = 'ovc_flurries_dark';
        $icn_t['blowingsnow']   = 'ovc_flurries';
        $icn_t['blowingsnown']  = 'ovc_flurries_dark';
        $icn_t['clear']         = 'clear_day';
        $icn_t['clearn']        = 'clear_night';
        $icn_t['clearw']        = 'clear_windy_day';
        $icn_t['clearwn']       = 'clear_windy_night';
        $icn_t['cloudy']        = 'mc_day';
        $icn_t['cloudyn']       = 'mc_night';
        $icn_t['cloudyw']       = 'ovc_windy';
        $icn_t['cloudywn']      = 'ovc_windy_dark';
        $icn_t['cold']          = 'unknown';
        $icn_t['coldn']         = 'unknown';
        $icn_t['drizzle']       = 'mc_rain';
        $icn_t['drizzlef']      = 'mc_rain';
        $icn_t['drizzlen']      = 'mc_rain';
        $icn_t['dust']          = 'dust';
        $icn_t['dustn']         = 'dust';
        $icn_t['fair']          = 'few_day';
        $icn_t['fairn']         = 'few_night';
        $icn_t['fdrizzle']      = 'ovc_sleet';
        $icn_t['fdrizzlen']     = 'ovc_sleet_dark';
        $icn_t['flurries']      = 'mc_flurries';
        $icn_t['flurriesn']     = 'mc_flurries_night';
        $icn_t['flurriesw']     = 'mc_flurries';
        $icn_t['flurrieswn']    = 'mc_flurries_night';
        $icn_t['fog']           = 'haze_day';
        $icn_t['fogn']          = 'mc_fog_dark';
        $icn_t['freezingrain']  = 'ovc_flurries';
        $icn_t['freezingrainn'] = 'ovc_flurries_dark';
        $icn_t['hazy']          = 'haze_day';
        $icn_t['hazyn']         = 'mc_fog_dark';
        $icn_t['hot']           = 'uvstrong';
        $icn_t['mcloudy']       = 'mc_day';
        $icn_t['mcloudyn']      = 'mc_night';
        $icn_t['mcloudyr']      = 'mc_rain';
        $icn_t['mcloudyrn']     = 'mc_rain_dark';
        $icn_t['mcloudyrw']     = 'mc_rain_dark';
        $icn_t['mcloudyrwn']    = 'mc_rain_dark';
        $icn_t['mcloudys']      = 'mc_rain_dark';
        $icn_t['mcloudysf']     = 'mc_rain_dark';
        $icn_t['mcloudysfn']    = 'mc_rain_dark';
        $icn_t['mcloudysfw']    = 'mc_rain_dark';
        $icn_t['mcloudysfwn']   = 'mc_rain_dark';
        $icn_t['mcloudysn']     = 'ovc_flurries';
        $icn_t['mcloudysw']     = 'ovc_flurries';
        $icn_t['mcloudyswn']    = 'ovc_flurries_dark';
        $icn_t['mcloudyt']      = 'ovc_thun_dark';
        $icn_t['mcloudytn']     = 'ovc_thun_dark';
        $icn_t['mcloudytw']     = 'ovc_thun_dark';
        $icn_t['mcloudytwn']    = 'ovc_thun_dark';
        $icn_t['mcloudyw']      = 'mc_windy';
        $icn_t['mcloudywn']     = 'mc_windy_dark';
        $icn_t['na']            = 'unknown';
        $icn_t['pcloudy']       = 'mc_day';
        $icn_t['pcloudyn']      = 'mc_night';
        $icn_t['pcloudyr']      = 'mc_rain';
        $icn_t['pcloudyrn']     = 'mc_rain_dark';
        $icn_t['pcloudyrw']     = 'mc_rain_dark';
        $icn_t['pcloudyrwn']    = 'mc_rain_dark';
        $icn_t['pcloudys']      = 'mc_rain_dark';
        $icn_t['pcloudysf']     = 'mc_rain_dark';
        $icn_t['pcloudysfn']    = 'mc_rain_dark';
        $icn_t['pcloudysfw']    = 'mc_rain_dark';
        $icn_t['pcloudysfwn']   = 'mc_rain_dark';
        $icn_t['pcloudysn']     = 'ovc_flurries';
        $icn_t['pcloudysw']     = 'ovc_flurries';
        $icn_t['pcloudyswn']    = 'ovc_flurries_dark';
        $icn_t['pcloudyt']      = 'ovc_thun_dark';
        $icn_t['pcloudytn']     = 'ovc_thun_dark';
        $icn_t['pcloudytw']     = 'ovc_thun_dark';
        $icn_t['pcloudytwn']    = 'ovc_thun_dark';
        $icn_t['pcloudyw']      = 'mc_windy';
        $icn_t['pcloudywn']     = 'mc_windy_dark';
        $icn_t['rain']          = 'mc_rain';
        $icn_t['rainandsnow']   = 'ovc_sleet';
        $icn_t['rainandsnown']  = 'ovc_sleet_dark';
        $icn_t['rainn']         = 'mc_rain_dark';
        $icn_t['raintosnow']    = 'ovc_sleet';
        $icn_t['raintosnown']   = 'ovc_sleet_dark';
        $icn_t['rainw']         = 'mc_rain_dark';
        $icn_t['showers']       = 'mc_rain_dark';
        $icn_t['showersn']      = 'mc_rain_dark';
        $icn_t['showersw']      = 'mc_rain_dark';
        $icn_t['showerswn']     = 'mc_rain_dark';
        $icn_t['sleet']         = 'ovc_sleet';
        $icn_t['sleetn']        = 'ovc_sleet_dark';
        $icn_t['sleetsnow']     = 'ovc_sleet';
        $icn_t['sleetsnown']    = 'ovc_sleet_dark';
        $icn_t['smoke']         = 'tornado';
        $icn_t['smoken']        = 'tornado';
        $icn_t['snow']          = 'mc_flurries';
        $icn_t['snown']         = 'mc_flurries_night';
        $icn_t['snowshowers']   = 'mc_flurries';
        $icn_t['snowshowersn']  = 'mc_flurries';
        $icn_t['snowshowersw']  = 'mc_flurries';
        $icn_t['snowshowerswn'] = 'mc_flurries';
        $icn_t['snowtorain']    = 'ovc_sleet';
        $icn_t['snowtorainn']   = 'ovc_sleet_dark';
        $icn_t['snoww']         = 'mc_flurries';
        $icn_t['snowwn']        = 'mc_flurries_night';
        $icn_t['sunny']         = 'clear_day';
        $icn_t['sunnyn']        = 'clear_night';
        $icn_t['sunnyw']        = 'clear_windy_day';
        $icn_t['sunnywn']       = 'clear_windy_night';
        $icn_t['tstorm']        = 'ovc_thun_dark';
        $icn_t['tstormn']       = 'ovc_thun_dark';
        $icn_t['tstorms']       = 'ovc_thun_dark';
        $icn_t['tstormsn']      = 'ovc_thun_dark';
        $icn_t['tstormsw']      = 'ovc_thun_dark';
        $icn_t['tstormswn']     = 'ovc_thun_dark';
        $icn_t['wind']          = 'ovc_windy';
        $icn_t['wintrymix']     = 'ovc_sleet';
        $icn_t['wintrymixn']    = 'ovc_sleet_dark';   
        if (!isset ($icn_t[$icon]))
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') unknown icon '.$icon.PHP_EOL;
                $return = 'offline';}
        else {  $return = $icn_t[$icon];}
        return $return;}
#-----------------------------------------------
