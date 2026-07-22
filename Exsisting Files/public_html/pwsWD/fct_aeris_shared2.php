<?php $scrpt_vrsn_dt  = 'fct_aeris_shared.php|01|2023-09-09|';   # PHP8.2 graph| release 2012_lts
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
#
$lvl_bft        = array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
$bft_txt        = array( /* Beaufort 0 to 12 in English */
	'Calm', 
	'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale force', 'Stronggale', 'Storm',
	'Violent storm', 'Hurricane');
$windlabel      = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
		         'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
if (!function_exists('windlabel') ) {
#----------------------------------------------- 
#      windlabel convert degrees to compass name   
#----------------------------------------------- 
$windlabel_dfld = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
                       'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
$windlabel_shrt = array ('N','NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S',
		        'SSW','SW', 'WSW', 'W', 'WNW', 'NW', 'NNW');
function windlabel($value, $short = false)
     {  global $windlabel_dfld, $windlabel_shrt;
        $degr   = (int) $value;
        $key    = (int) fmod((($degr + 11) / 22.5),16);   # 2022-03-29
        if ($short <> false)
             {  return $windlabel_dfld[$key];}
        else {  return $windlabel_shrt[$key];}
        }
}
foreach ($periods as $period)
     {  $arr    = array();
        if ( $valid > $period['timestamp']) 
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') record skipped, '.date('c',$period['timestamp']).PHP_EOL; 
                continue;}  // skip to old forecast parts
        $day    = lang(date ('l', $period['timestamp']));
        $isDay  = $period['isDay'];
        if (!$isDay) 
             {  $day .= '<br>'.lang('Night');}
        $arr['isDay']   = $isDay;
        $arr['Utime']   = $period['timestamp'];
        $arr['part']    = $day; 
        if ($isDay) 
             {  $first  = 'max'; 
                $dew    = 'min';} 
        else {  $first  = 'min'; 
                $dew    = 'max';}
        $arr['temp']    = $period[ $first .'Temp'       .$tempunit];
        $arr['dewp']    = $period[ $dew   .'Dewpoint'   .$tempunit];
        $arr['feel']    = $period[ $first .'Feelslike'  .$tempunit];
        $arr['humi']    = $period[ $dew   .'Humidity'];
        $arr['uvuv']    = $period[ 'uvi'];
        $arr['solr']    = $period[ 'solrad'.ucfirst($first).'WM2'];
        $unit           = strtoupper($rainunit);  // 'mm' or 'in'
        $arr['r_ch']    = $period[ 'pop'];
        $arr['rain']    = $period[ 'precip'.$unit];
        if ($pressureunit == "hPa") { $unit = 'MB'; } else { $unit = 'IN'; } #### 2021-01-10
        $arr['baro']    = $period[ 'pressure'.$unit]; 
        $arr['clds']    = $period[ 'sky'];
        $arr['wdir']    = $period[ 'windDir'];  # "windDir": "WNW",
        $arr['wdeg']    = $period[ 'windDirDEG'];
        $wdir	= windlabel($arr['wdeg']);
	if (strlen ($wdir) > 3) { $wdir = substr ($wdir,0,1);} 
	$arr['wicn']    = '<img src="img/windicons/'.$wdir.'.svg" class=""  alt="'.$wdir.'">';
# KTS KPH  MPH
# --  km/h mph m/s = MPH *   0.44704  
        $unit           = strtoupper (str_replace ('/','',$windunit ));    
        $multiply       = 1;
        if ($unit == 'MS')  { $unit = 'MPH'; $multiply = 0.44704; }
        if ($unit == 'KMH') { $unit = 'KPH'; }
        $arr['wspd']    = round ($period['windSpeed'.   $unit]*$multiply);
        $arr['wmax']    =
        $max            = round ($period['windSpeedMax'.$unit]*$multiply);
        $min            = round ($period['windSpeedMin'.$unit]*$multiply);
        $arr['w_ft']    = (string)$min .'-'.(string) $max;
        $arr['gust']    = round ($period['windGust'.   $unit]*$multiply);
        $spd_knts       = $period['windSpeedMaxKTS'];
        foreach ($lvl_bft as $key => $lvl)
             {  if ($spd_knts > $lvl) {continue;}  # $key=12; # for test 
                break;}
        $arr['bftn']    = $key;
        $arr['bftt']    = lang($bft_txt[$key]); 
#       
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
        $translated = str_replace ('#',' , ',$translated);      #### 2020-12-09                 
        $arr['desc']    = $translated;        
        $arr['icnx']    = $period['icon'];
        $look   = str_replace ('.png','',$period['icon']);
        $arr['icon']    = icon_trns ($look);
        $parts[]        = $arr;            
}
unset ($periods);
if (count($parts) == 0) 
     {  echo '<p style="color: red;"><b>no valid data found </b>'.$ars_fct_fl.'</p>'; 
        return false;}
$replace= array (lang ('Today'), lang ('Tonight'), lang ('Tomorrow'), lang ('Tomorrow').'<br>'.lang('Night'));
if ($parts[0]['isDay'] == false)  { array_shift($replace);}
for ($n = 0; $n < count($replace); $n++) {$parts[$n]['part'] = $replace[$n];}
#
if (!function_exists ('temp_color') ) {
        function temp_color ( $value)
             {  global $tempunit, $maxTemp, $temp_colors;
                if ($value === 'n/a' || $value === false) 
                    {   return '<!-- no value '.$value.' -->'.PHP_EOL; return;}
                $tmp    = (float) $value; 
                if ($tempunit <> 'C')
                     {  $tmp    = round (    5*( ($tmp -32)/9) );}
                $n      = 32 + (int) $tmp;
                if ($n > $maxTemp)      
                     {  $color  = $temp_colors[$maxTemp];}
                else {  $color  = $temp_colors[$n];}
                return $color;}
} // eo exist temp_color
#
$temp_colors = array(
        '#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
        '#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
        '#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
        '#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
        '#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
        '#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
        '#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
        '#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',);
$maxTemp        = count($temp_colors) - 1;

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
