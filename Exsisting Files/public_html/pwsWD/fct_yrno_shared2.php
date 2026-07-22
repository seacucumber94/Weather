<?php $scrpt_vrsn_dt  = 'fct_yrno_shared.php|01|2023-09-09|';  # PHP 8.2 graph ||release 2012_lts
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
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;     
#
$fl_folder      = './jsondata/';
$fct_loaded     = $fl_folder.'metno2complete.json';
$fct_cached     = $fl_folder.'metno2complete.arr';
$icn_prefix     = './pws_icons/';
$icn_post       = '.svg';
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
$windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
         "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
#
$lvl_bft        = array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
$bft_txt        = array( /* Beaufort 0 to 12 in English */
	'Calm', 
	'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale force', 'Stronggale', 'Storm',
	'Violent storm', 'Hurricane');
$windlabel      = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
		         'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$fll_uv  = array();
$fll_uv[0]  = $b_clrs['green'];
$fll_uv[1]  = $b_clrs['green'];
$fll_uv[2]  = $b_clrs['green'];
$fll_uv[3]  = $b_clrs['yellow'];
$fll_uv[4]  = $b_clrs['yellow'];
$fll_uv[5]  = $b_clrs['yellow'];
$fll_uv[6]  = $b_clrs['orange'];
$fll_uv[7]  = $b_clrs['orange'];
$fll_uv[8]  = $b_clrs['red'];
$fll_uv[9]  = $b_clrs['red'];
$fll_uv[10] = $b_clrs['red'];
$fll_uv[11] = $b_clrs['maroon'];
#
#-----------------------------------------------
# Icon translation
#-----------------------------------------------
if (1 == 1) {
$mtn_icons = array();
                      $mtn_icons['clearsky_day'] = array ("old" => "1",   "txt" => "Clear sky",                         "svg" => "clear_day");
                    $mtn_icons['clearsky_night'] = array ("old" => "1",   "txt" => "Clear sky",                         "svg" => "clear_night");
                            $mtn_icons['cloudy'] = array ("old" => "4",   "txt" => "Cloudy",                            "svg" => "ovc_dark");
                          $mtn_icons['fair_day'] = array ("old" => "2",   "txt" => "Fair",                              "svg" => "few_day");
                        $mtn_icons['fair_night'] = array ("old" => "2",   "txt" => "Fair",                              "svg" => "few_night");
                               $mtn_icons['fog'] = array ("old" => "15",  "txt" => "Fog",                               "svg" => "mc_fog");
                         $mtn_icons['heavyrain'] = array ("old" => "10",  "txt" => "Heavy rain",                        "svg" => "mc_rain");
               $mtn_icons['heavyrainandthunder'] = array ("old" => "11",  "txt" => "Heavy rain and thunder",            "svg" => "ovc_thun_rain_dark");
              $mtn_icons['heavyrainshowers_day'] = array ("old" => "41",  "txt" => "Heavy rain showers",                "svg" => "mc_flurries");
            $mtn_icons['heavyrainshowers_night'] = array ("old" => "41",  "txt" => "Heavy rain showers",                "svg" => "mc_flurries_night");
    $mtn_icons['heavyrainshowersandthunder_day'] = array ("old" => "25",  "txt" => "Heavy rain showers and thunder",    "svg" => "ovc_thun_rain_dark");
  $mtn_icons['heavyrainshowersandthunder_night'] = array ("old" => "25",  "txt" => "Heavy rain showers and thunder",    "svg" => "ovc_thun_rain_dark");
                        $mtn_icons['heavysleet'] = array ("old" => "48",  "txt" => "Heavy sleet",                       "svg" => "mc_flurries");
              $mtn_icons['heavysleetandthunder'] = array ("old" => "32",  "txt" => "Heavy sleet and thunder",           "svg" => "ovc_thun_rain_dark");
             $mtn_icons['heavysleetshowers_day'] = array ("old" => "43",  "txt" => "Heavy sleet showers",               "svg" => "mc_flurries");
           $mtn_icons['heavysleetshowers_night'] = array ("old" => "43",  "txt" => "Heavy sleet showers",               "svg" => "mc_flurries_night");
   $mtn_icons['heavysleetshowersandthunder_day'] = array ("old" => "27",  "txt" => "Heavy sleet showers and thunder",   "svg" => "ovc_thun_rain_dark");
 $mtn_icons['heavysleetshowersandthunder_night'] = array ("old" => "27",  "txt" => "Heavy sleet showers and thunder",   "svg" => "ovc_thun_rain_dark");
                         $mtn_icons['heavysnow'] = array ("old" => "50",  "txt" => "Heavy snow",                        "svg" => "mc_flurries_night");
               $mtn_icons['heavysnowandthunder'] = array ("old" => "34",  "txt" => "Heavy snow and thunder",            "svg" => "ovc_thun_rain_dark");
              $mtn_icons['heavysnowshowers_day'] = array ("old" => "45",  "txt" => "Heavy snow showers",                "svg" => "mc_flurries");
            $mtn_icons['heavysnowshowers_night'] = array ("old" => "45",  "txt" => "Heavy snow showers",                "svg" => "mc_flurries_night");
    $mtn_icons['heavysnowshowersandthunder_day'] = array ("old" => "29",  "txt" => "Heavy snow showers and thunder",    "svg" => "ovc_thun_rain_dark");
  $mtn_icons['heavysnowshowersandthunder_night'] = array ("old" => "29",  "txt" => "Heavy snow showers and thunder",    "svg" => "ovc_thun_rain_dark");
                         $mtn_icons['lightrain'] = array ("old" => "46",  "txt" => "Light rain",                        "svg" => "mc_flurries");
                     $mtn_icons['lightrain_day'] = array ("old" => "46",  "txt" => "Light rain",                        "svg" => "mc_flurries");
                   $mtn_icons['lightrain_night'] = array ("old" => "46",  "txt" => "Light rain",                        "svg" => "mc_flurries_night");
               $mtn_icons['lightrainandthunder'] = array ("old" => "30",  "txt" => "Light rain and thunder",            "svg" => "ovc_thun_rain_dark");
           $mtn_icons['lightrainandthunder_day'] = array ("old" => "30",  "txt" => "Light rain and thunder",            "svg" => "ovc_thun_rain_dark");
         $mtn_icons['lightrainandthunder_night'] = array ("old" => "30",  "txt" => "Light rain and thunder",            "svg" => "ovc_thun_rain_dark");
              $mtn_icons['lightrainshowers_day'] = array ("old" => "40",  "txt" => "Light rain showers",                "svg" => "mc_flurries");
            $mtn_icons['lightrainshowers_night'] = array ("old" => "40",  "txt" => "Light rain showers",                "svg" => "mc_flurries_night");
    $mtn_icons['lightrainshowersandthunder_day'] = array ("old" => "24",  "txt" => "Light rain showers and thunder",    "svg" => "ovc_thun_rain_dark");
  $mtn_icons['lightrainshowersandthunder_night'] = array ("old" => "24",  "txt" => "Light rain showers and thunder",    "svg" => "ovc_thun_rain_dark");
                        $mtn_icons['lightsleet'] = array ("old" => "47",  "txt" => "Light sleet",                       "svg" => "mc_flurries");
              $mtn_icons['lightsleetandthunder'] = array ("old" => "31",  "txt" => "Light sleet and thunder",           "svg" => "ovc_thun_rain_dark");
             $mtn_icons['lightsleetshowers_day'] = array ("old" => "42",  "txt" => "Light sleet showers",               "svg" => "mc_flurries");
           $mtn_icons['lightsleetshowers_night'] = array ("old" => "42",  "txt" => "Light sleet showers",               "svg" => "mc_flurries_night");
                         $mtn_icons['lightsnow'] = array ("old" => "49",  "txt" => "Light snow",                        "svg" => "mc_flurries");
               $mtn_icons['lightsnowandthunder'] = array ("old" => "33",  "txt" => "Light snow and thunder",            "svg" => "ovc_thun_rain_dark");
              $mtn_icons['lightsnowshowers_day'] = array ("old" => "44",  "txt" => "Light snow showers",                "svg" => "mc_flurries");
            $mtn_icons['lightsnowshowers_night'] = array ("old" => "44",  "txt" => "Light snow showers",                "svg" => "mc_flurries");
  $mtn_icons['lightssleetshowersandthunder_day'] = array ("old" => "26",  "txt" => "Lights sleet showers and thunder",  "svg" => "ovc_thun_rain_dark");
$mtn_icons['lightssleetshowersandthunder_night'] = array ("old" => "26",  "txt" => "Lights sleet showers and thunder",  "svg" => "ovc_thun_rain_dark");
   $mtn_icons['lightssnowshowersandthunder_day'] = array ("old" => "28",  "txt" => "Lights snow showers and thunder",   "svg" => "ovc_thun_rain_dark");
 $mtn_icons['lightssnowshowersandthunder_night'] = array ("old" => "28",  "txt" => "Lights snow showers and thunder",   "svg" => "ovc_thun_rain_dark");
                  $mtn_icons['partlycloudy_day'] = array ("old" => "3",   "txt" => "Partly cloudy",                     "svg" => "pc_day");
                $mtn_icons['partlycloudy_night'] = array ("old" => "3",   "txt" => "Partly cloudy",                     "svg" => "pc_night");
                              $mtn_icons['rain'] = array ("old" => "9",   "txt" => "Rain",                              "svg" => "mc_rain");
                    $mtn_icons['rainandthunder'] = array ("old" => "22",  "txt" => "Rain and thunder",                  "svg" => "ovc_thun_rain_dark");
                   $mtn_icons['rainshowers_day'] = array ("old" => "5",   "txt" => "Rain showers",                      "svg" => "mc_rain");
                 $mtn_icons['rainshowers_night'] = array ("old" => "5",   "txt" => "Rain showers",                      "svg" => "mc_rain");
         $mtn_icons['rainshowersandthunder_day'] = array ("old" => "6",   "txt" => "Rain showers and thunder",          "svg" => "ovc_thun_rain_dark");
       $mtn_icons['rainshowersandthunder_night'] = array ("old" => "6",   "txt" => "Rain showers and thunder",          "svg" => "ovc_thun_rain_dark");
                             $mtn_icons['sleet'] = array ("old" => "12",  "txt" => "Sleet",                             "svg" => "ovc_sleet");
                   $mtn_icons['sleetandthunder'] = array ("old" => "23",  "txt" => "Sleet and thunder",                 "svg" => "ovc_thun_rain_dark");
                  $mtn_icons['sleetshowers_day'] = array ("old" => "7",   "txt" => "Sleet showers",                     "svg" => "ovc_sleet");
                $mtn_icons['sleetshowers_night'] = array ("old" => "7",   "txt" => "Sleet showers",                     "svg" => "ovc_sleet");
        $mtn_icons['sleetshowersandthunder_day'] = array ("old" => "20",  "txt" => "Sleet showers and thunder",         "svg" => "ovc_thun_rain_dark");
      $mtn_icons['sleetshowersandthunder_night'] = array ("old" => "20",  "txt" => "Sleet showers and thunder",         "svg" => "ovc_thun_rain_dark");
                              $mtn_icons['snow'] = array ("old" => "13",  "txt" => "Snow",                              "svg" => "ovc_flurries");
                    $mtn_icons['snowandthunder'] = array ("old" => "14",  "txt" => "Snow and thunder",                  "svg" => "ovc_thun_rain_dark");
                   $mtn_icons['snowshowers_day'] = array ("old" => "8",   "txt" => "Snow showers",                      "svg" => "ovc_sleet");
                 $mtn_icons['snowshowers_night'] = array ("old" => "8",   "txt" => "Snow showers",                      "svg" => "ovc_sleet");
         $mtn_icons['snowshowersandthunder_day'] = array ("old" => "21",  "txt" => "Snow showers and thunder",          "svg" => "ovc_thun_rain_dark");
       $mtn_icons['snowshowersandthunder_night'] = array ("old" => "21",  "txt" => "Snow showers and thunder",          "svg" => "ovc_thun_rain_dark");
}#
#
#---------------  check filetimes json and array
$time_loaded    = $time_cached  = 0;
if   ( file_exists($fct_loaded) )    {  $time_loaded    = filemtime ($fct_loaded); }
if   ( file_exists($fct_cached) )    {  $time_cached    = filemtime ($fct_cached); }
$age    = time() - $time_loaded;
# ----------
if (  array_key_exists ('force',$_REQUEST ) 
   && $_REQUEST['force'] == 'metno') 
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') data will be reloaded as force was set '.PHP_EOL;  
        $age    = 9999; }   // skip cache reads requested by user
#
if ($age > 7200) // load files did not load latest metno file
     {  $fct_default    = 'fct_yrno_block.php';  // make sure metno data is loaed, even if yrno is not the default forecast
        $cron           =  true;
        $scrpt          = 'PWS_load_files.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;  
        include_once $scrpt;
# check if file was loaded from metno server
        if   (!file_exists($fct_loaded) )
             {  echo '<small style="color: red;">Problem '.__LINE__.': No valid forecast found, script ends</small>';
                exit;}
        else {  $time_loaded    = filemtime ($fct_loaded); }
        } // eo reload to old file
#
$yrno_fct_time = $time_loaded;
#
if   (  file_exists($fct_cached) && $time_cached > $time_loaded ) {
        $fct_metno      = unserialize (file_get_contents($fct_cached)); 
        $frct_mtn_geo   = $fct_metno[0];
        $frct_mtn_1hr   = $fct_metno[1];
        $frct_mtn_dp    = $fct_metno[2];
        $stck_lst       .= basename(__FILE__).' ('.__LINE__.') cache '.$fct_cached.' used'.PHP_EOL;    
        unset ($fct_metno);  
        return true;}

$arr1   = false;
if (file_exists($fct_loaded) )  // can we use a file, even if it is obsolete
     {  $arr1   = json_decode(file_get_contents( $fct_loaded), true);}
#
if   (  $arr1 == false   && file_exists($fct_cached) ) 
     {  $fct_metno      = unserialize  (file_get_contents($fct_cached));
        $frct_mtn_geo   = $fct_metno[0];
        $frct_mtn_1hr   = $fct_metno[1];
        $frct_mtn_dp    = $fct_metno[2];
        $stck_lst       .= basename(__FILE__).' ('.__LINE__.') invalid file '.$fct_loaded.' => cache '.$fct_cached.' used'.PHP_EOL;   
        unset ($fct_metno);
        return true;} 
elseif ($arr1 == false)  {
        echo '<small style="color: red;"><b>invalid data<br />metno2complete.json</b></small>'; 
        return false;}
#
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') processing file '.$fct_loaded.PHP_EOL;   
#
# fill the array with data
#
$frct_mtn_geo   = $arr1['geometry']['coordinates'];
$frct_mtn_dp    = array();      // dayparts
$frct_mtn_1hr   = array();      // hourly data
$baro_prev      = 0;
#
$diff   = date ('P');  // UTC hours difference 
list ($diff, $none)     = explode (':',$diff);
$diff   = 3600 * (int) $diff;
#
foreach ($arr1['properties']['timeseries'] as $key => $arr)   
     {  $time   = strtotime ($arr['time']);                     #  print_r($arr);   
        if ($baro_prev == 0) {$baro_prev = $arr['data']['instant']['details']['air_pressure_at_sea_level'];}

        if (!array_key_exists ('next_1_hours',$arr['data'])) {break;}
        $arr['data']['instant']['details']['unix']      = $time;
        $arr['data']['instant']['details']['datetime']  = $arr['time'];
        $arr['data']['instant']['details']['org_nr']    = $key;
        $frct_mtn_1hr[] = array_merge(
                $arr['data']['instant']['details'],
                $arr['data']['next_1_hours']['summary'],
                $arr['data']['next_1_hours']['details'] );              
        $hour   = date ('G',$time);
        $remain = $hour % 6;
        if   (  $remain == 0 && array_key_exists ('next_6_hours',$arr['data']) )  # echo $arr['time'].PHP_EOL;
             {  $frct_mtn_dp[] = array_merge(
                        $arr['data']['instant']['details'],
                        $arr['data']['next_6_hours']['summary'],
                        $arr['data']['next_6_hours']['details'] );
                $last   = $time;}
} // eo foreach time
#$last   = $last - $diff;  #echo __LINE__.' $key='.$key.' last='.$last.' diff='.$diff.' '.date('c',$last); exit;
$hours  = ceil($last/3600);
$last   = 3600*($hours+6);
foreach ($arr1['properties']['timeseries'] as $key => $arr)        
     {  $time   = strtotime ($arr['time']);                     #  print_r($arr);   
        if (!array_key_exists ('next_6_hours',$arr['data']) )
              { continue;}
        if ($time <= $last) { continue; }
        $hour   = gmdate ('G',$time);
        $remain = $hour % 6;
        if   (  $remain == 0 && array_key_exists ('next_6_hours',$arr['data']) ) 
             {                                                  # echo $arr['time'].PHP_EOL;
                $arr['data']['instant']['details']['unix']      = $time;
                $arr['data']['instant']['details']['datetime']  = $arr['time'];
                $arr['data']['instant']['details']['org_nr']    = $key;
                $frct_mtn_dp[] = array_merge(
                        $arr['data']['instant']['details'],
                        $arr['data']['next_6_hours']['summary'],
                        $arr['data']['next_6_hours']['details'] );
                } // 6 hours part
}  // all remaining timesrie items
#
$fct_metno      = array();
$fct_metno[0]   = $frct_mtn_geo;
$fct_metno[1]   = $frct_mtn_1hr;
$fct_metno[2]   = $frct_mtn_dp; # echo print_r ($frct_mtn_dp,true); exit;
$string         = serialize($fct_metno);
file_put_contents ($fct_cached, $string );
unset ($fct_metno, $string);
sleep (1);
return true;
#
#echo '<pre> $frct_mtn_geo='.print_r($frct_mtn_geo,true).' $frct_mtn_1hr='.print_r($frct_mtn_1hr,true).' $frct_mtn_dp='.print_r($frct_mtn_dp,true);
