<?php  $scrpt_vrsn_dt  = 'PWS_settings.php|01|2023-10-31|';  # purpleAPI cron problem + link cumulus + cookie + default + clean + always lang US | 500 error | weewx logo | release 2012_lts
#
$PWS_version    = '2012';   // every version gets a new number
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
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header('Content-type: text/plain; charset=UTF-8');
   header('Accept-Ranges: bytes');
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);      error_reporting(0);}
else {  ini_set('display_errors', 'On');   error_reporting(E_ALL);}  
#
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#-----------------------------------------------
#            load the user  easyweather-settings
$scrpt          = __DIR__.'/'.'_my_settings/settings.php';  //
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include =>'.$scrpt.PHP_EOL;
include $scrpt; 
#-----------------------------------------------
#               VALID USER SETTINGS
#these are known themes as used in other scripts
$valid_themes   = array ('dark', 'light', 'user'); 
#                      and these are valid units   
$vld_units      = array ('us', 'uk', 'metric', 'scandinavia') ;
#
#-----------------------------------------------
#                   DATA           refresh-times
#
$non_cron       = 120;  // seconds
#
$metarRefresh   = 600;  // 10 minutes
$fcts_refresh   = 900;  // for all forecast
$quakesRefresh  = 600;
$kindexRefresh  = 300;
$purpleRefresh  = 800;  # upped from 120
$luftRefresh    = 120;
#-----------------------------------------------
#                   DATA               locations
$fl_folder      = __DIR__.'/'.'jsondata/'; ## 2020-03-06  realpath(dirname(__FILE__)).'/'.'jsondata/';  ## 2020-01-12 add full path
$mtr_fl         = 'metar34'.$icao1.'.txt';
$qks_fl         = 'eqnotification.txt'; #  $fl_folder.$qks_fl   # 'jsondata/eqnotification.txt'
$kndx_fl        = 'kindex.txt';         #  $fl_folder.$kndx_fl  # 'jsondata/kindex.txt'
$prpl_fl        = 'purpleair.txt';      #  $fl_folder.$prpl_fl  # "jsondata/purpleair.txt"
$lfdtn_fl       = 'luftdaten.txt';      #  $fl_folder.$lfdtn_fl # "jsondata/luftdaten.txt"
$gvaqi_fl       = 'gov_aqi.txt';        #  $fl_folder.$gvaqi_fl # "jsondata/gov_aqi.txt"
#-----------------------------------------------
$popup_css      = './css/popup_css.css';
#-----------------------------------------------
ini_set('session.use_cookies', '1');            ######  ???
list ($php_version,$none)   = explode ('.',phpversion().'..');  #### 2021-11-28 move cookie to function
$ck_time= time()+ 600;                                          #### warning SameSite (FF) is removed 
$ck_skip= true;  // true = do not use cookies                   #### default cookie will not be used
#
function pws_cookie ($name,$value)
    {   global $php_version,$read_net_data, $ck_time, $ck_skip;
        if ($read_net_data == false) {return; } // only PWS_index2.php allowed to set cookies
        if ($ck_skip == true)        {return; } // do not want cookies
        if ( (int) $php_version < 7 ) // old php version
             {  SetCookie($name, $value,$ck_time,'/; samesite=Lax');}
        else {  $ck_arr = array('expires' => $ck_time, 'samesite' => 'Lax'); 
                SetCookie($name, $value,$ck_arr);}
}                                                               #### 2021-11-28 move cookie to function
#-----------------------------------------------
#                  THEME      which theme to use
#these are known themes as used in other scripts
$valid_themes   = array ('dark', 'light', 'user'); 
#
$current_theme  = $valid_themes[0];  // dark
#
if (in_array($theme1, $valid_themes) )
     {  $current_theme  = $theme1; } // from easyweather
#
if (array_key_exists('KISS', $_GET) )
     {  $KISS = true;}
#
if (array_key_exists('theme', $_GET) )
     {  $key    = trim($_GET['theme']);
        if (in_array($key, $valid_themes) )
             {  $current_theme  = $key;
                pws_cookie('theme', $current_theme); }  #### 2021-11-28
        } // eo $_GET
elseif (array_key_exists('theme', $_COOKIE) )
     {  $key    = trim($_COOKIE['theme']);
        if (in_array($key, $valid_themes) )
             {  $current_theme = $key; } 
        } // eo cookie
#-----------------------------------------------
#                               correct timezone      
$check = date_default_timezone_set($TZ);  #### 2021-03-24 
if ($check === false) 
     {  echo 'FATAL error, invalid timezone '.$TZ.' reset to UTC => redo your easyweather setup'; 
        $TZ     = 'UTC';
        date_default_timezone_set($TZ);}  #### 2021-03-24 
$in_dst = date('I');    // is daylight savings on?
list ($hours,$mins) = explode (':',date ('P'));
#
if ($in_dst  <> 0             // yes we are in DS
        && isset ($noDST)     // DST override setting exists
        && $noDST <> 'DST' )  // DST not acceptable, back to normal time
     {  $UTC = $hours + ($mins/60); # $UTC    = (int)date ('P') - (int) $in_dst;
        $TZnw     =  timezone_name_from_abbr("", $UTC * 3600, $in_dst);
        if ($TZnw <> false) 
             {  $TZ = $TZnw;
                date_default_timezone_set($TZ);}
        } // eo overrides for no DST wanted
#
$UTC = $hours + ($mins/60); #$UTC = (int)date ('P');     // used in the javescript clock to display server time
# 2020-08-21
#-----------------------------------------------
#             LANGUAGE     
#
if(isset($_GET['test']) )  { $lang_select = true;}
#
$user_lang      = $defaultlanguage;     // from the settings;
#
$file_lng       = __DIR__.'/'.'_my_settings/languages.txt';
$lngsArr= array ();    // array with all valid languages
if (file_exists ($file_lng)) {
        $arr    = file ($file_lng);  // this site language settings              
        foreach ($arr as $string) 
             {  if (    trim($string)       == ''  // skip comments and empty lines
                     || substr($string,0,1) == '#'
                     || substr($string,0,1) == ' ')  {  continue;} 
                list ($lng_key, $lng_flag, $lng_locale, $lng_file, $lng_txt,$ctrFlg,$locale_wu) = explode ('|',$string);
                if (trim($lng_key) <> '')    {$lng_key    = trim($lng_key);}    else {continue;}
                if (trim($lng_flag) <> '')   {$lng_flag   = trim($lng_flag);}   else {continue;}
                if (trim($lng_locale) <> '') {$lng_locale = trim($lng_locale);} else {continue;}
                if (trim($lng_file) <> '')   {$lng_file   = trim($lng_file);}   else {continue;}
                if (trim($lng_txt) <> '')    {$lng_txt     = trim($lng_txt);}   else {continue;}
                if (trim($locale_wu) <> '')  {$locale_wu   = trim($locale_wu);} else {$locale_wu = trim($lng_locale);}
                $lngsArr[$lng_key]  =array ('flag' => $lng_flag, 'locale' => $lng_locale, 'file' => $lng_file, 'txt' => $lng_txt, 'wu' => $locale_wu);
        }   # echo '<pre>'.print_r ($lngsArr, true);
} 
if (! array_key_exists('en-us',$lngsArr) )
     {  $lngsArr['en-us'] = array ('flag' => 'us.svg', 'locale' => 'en', 'file' => 'lang_en.txt', 'txt' => 'English', 'wu' => 'en-US');}
# echo '$lngsArr='.print_r($lngsArr,true); exit;
#
$save_lang      = false;
if ( $lang_select === true)
     {  if (isset($_GET['lang']) )
             {  $user_lang      = trim($_GET['lang']);
                $save_lang      = true;}
        elseif(isset($_COOKIE['lang']))
             {  $user_lang = $_COOKIE['lang'];}    
        if (!isset ($lngsArr[$user_lang] ) ) // just to be sure
             {  $user_lang = $defaultlanguage;}   
        if ($user_lang <> $defaultlanguage || $save_lang == true)  
             {  pws_cookie('lang', $user_lang);}        #### 2021-11-28
        } // eo lang_select possible
#
$used_lang      = $user_lang; # echo '$used_lang='.$used_lang; exit;
#
#
$we_use_lng     = $lngsArr[$used_lang];
$lang_file      = __DIR__.'/languages/'.$we_use_lng['file'];   # 2023-09-12  // lang_de.txt
$lang_locale    = $we_use_lng['locale'];   // de-at
$locale_wu      = $we_use_lng['wu'];
$drksk_fl       = 'darksky-'.substr($locale_wu,0,2).'.txt';
#echo '$lang_file='.$lang_file.' $locale_wu='.$locale_wu.print_r($lngsArr,true); exit;
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $defaultlanguage='.$defaultlanguage.' $used_lang ='.$used_lang.' $locale_wu='.$locale_wu.' $lang_locale='.$lang_locale.' $lang_file='.$lang_file.PHP_EOL; 

#-----------------------------------------------
#             OTHER      
#       
$showFeelsLike  = true;    // whether to always show either the heat index (when temp > 80F/27C) or real feel (when temp between 50F/10C and 80F/27C) even when no concern
#
# Thresholds for warnings or notifications
$notifyUV           = 8;
$notifyWindGust     = 22; // knots; 22 knots, which is 40.7 km/h, 35 = 40.2 mph, 40 = 20.5  m/s
$notifyHeatIndex    = 30; // degrees C, default is 30 celsius which is 86 degrees F 
# 
if (!isset ($waqitoken) )        { $waqitoken         = '78312c3771cd11ecb372836690df98c2db1bcc82'; } 
if (!isset ($purpleairAPI) )     { $purpleairAPI      = '72F1F60C-C16B-11EA-982D-42010A800242'; }  # 2023-10-10
if (!isset ($somethinggoeshere) ){ $somethinggoeshere ='d4586dec-e7a2-47ae-99b6-25527b2563c9';  } # wf
#
#-----------------------------------------------
#             UNITS       
$units_used     = 
$EW_unit        = $unit; # $unit is set by easyweather  # 2019-09-05
if (!isset ($cron) || $cron == false){
#                                   check $_GET 
        if   (array_key_exists('units', $_GET))         // new change by visitor 
             {  $key    = trim($_GET['units']);
                if (in_array($key,$vld_units)) 
                     {  $units_used = $key;
                        pws_cookie('units', $key); }  // and store in cookie    #### 2021-11-28
                } // eo $_GET
#                               and check COOKIE
        elseif   (array_key_exists('units', $_COOKIE)
           && in_array($_COOKIE['units'],$vld_units ) ) 
             {  $units_used = $_COOKIE['units']; }   // use last change by visitor as stored in cookie
}
# ---------------  save settings for use in urls
$url_theme      = 'theme='.$current_theme;
if (isset ($KISS) && $KISS == true)
     {  $url_kis= '&KISS';}
else {  $url_kis= '';}
$url_lang       = 'lang='.$user_lang;
$url_units      = 'units='.$units_used;
#-----------------------------------------------
#             UNITS       defaults are set  here   
$windunit       = 'km/h';  # C_km/h_hPa_mm_km
$tempunit       = 'C'; 
$rainunit       = 'mm'; 
$pressureunit   = "hPa"; 
$distanceunit   = 'km';
$windconv       = "1"; 
$rainfallconv   = '10'; 
$pressureinterval= "0.5"; 
$rainfallconvmm = '10';
$wu_unit        = 'm'; 
$wu_fct_unit    = 'm';
$ds_page_unit   = 'ca12';
if (!isset($dec_tmp)) { $dec_tmp  = 1;}
if (!isset($dec_rain)){ $dec_rain = 1;}  
if (!isset($dec_baro)){ $dec_baro = 1;}
if (!isset($dec_wnd)) { $dec_wnd  = 1;}
if ($units_used == 'metric')      # use defaults
     {  }
elseif ($units_used == 'mmhg')
     {  $pressureunit   = "mmHg"; 
        $dec_baro++;}
#
elseif ($units_used == 'uk')   # C_mph_hPa_mm_mi
     {  $windunit       = 'mph'; 
        $distanceunit   = 'mi';
        $windconv       = "0.621371"; 
        $wu_fct_unit    = 'h';
        $ds_page_unit   = 'uk212';}
#        
elseif ($units_used == 'scandinavia')  # C_m/s_hPa_mm_km
     {  $windunit       = 'm/s'; 
        $windconv       = "0.277778"; 
        $wu_fct_unit    = 's';
        $ds_page_unit   = 'si12';} 
#
else {       // units == us
        $windunit       = 'mph';  #  F_mph_inHg_in_mi
        $tempunit       = 'F'; 
        $rainunit       = 'in'; 
        $pressureunit   = "inHg"; 
        $distanceunit   = 'mi';
        $windconv       = "1"; 
        $rainfallconv   = '1'; 
        $pressureinterval= "0.5"; 
        $rainfallconvmm = '1';
        $dec_rain++;
        $dec_baro++;
        $wu_fct_unit    = 'e';
        $ds_page_unit   = 'us12';}
if ($EW_unit == 'us') 
     {  $wu_unit = 'e';}   # wu only english or metric

$wind_his       = 'km/h';  # C_km/h_hPa_mm_km
$temp_his       = 'C'; 
$rain_his       = 'mm'; 
$baro_his       = "hPa";
if ($EW_unit == 'metric')
     {  }
elseif ($EW_unit == 'uk')
     {  $wind_his       = 'mph'; }
elseif ($EW_unit == 'scandinavia')
     {  $wind_his       = 'm/s'; }
else {  $wind_his       = 'mph';  
        $temp_his       = 'F'; 
        $rain_his       = 'in'; 
        $baro_his       = "inHg"; }           
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $units_used ='.$units_used.' $windunit='.$windunit.' $tempunit='.$tempunit.' $rainunit='.$rainunit.' $pressureunit='.$pressureunit.' $distanceunit='.$distanceunit.' $wu_unit='.$wu_unit.PHP_EOL; 
#-----------------------------------------------
#  #### 20201102     encoding set server to  
if (function_exists ('mb_internal_encoding') ) {mb_internal_encoding('UTF-8'); }  
if (function_exists ('mb_http_output') )       {mb_http_output('UTF-8'); }
#if (function_exists ('mb_http_input') )        {mb_http_input('UTF-8'); } #### 2020-12-08
if (function_exists ('mb_language') )          {mb_language('uni'); }
if (function_exists ('mb_regex_encoding') )    {mb_regex_encoding('UTF-8'); }
#-----------------------------------------------
#         LANGUAGE TRANSLATION FILE       loaded
#
$lang   = array();
if (file_exists ($lang_file)) 
     {  $arr    = file ($lang_file); 
        foreach ($arr as $string)
             {  $first  = substr ($string,0,1);
                if ($first == '#' || trim($string) == '') {continue;}
                list ($spaces,$left,$right) = explode ('|',$string.'|||'); #### 2020-12-08
                $lang[$left]    = $right;}
}
else {  $arr    = file (__DIR__.'/languages/lang_en.txt'); # 2023-09-12  // cron problerm
        foreach ($arr as $string)
             {  $first  = substr ($string,0,1);
                if ($first == '#' || trim($string) == '') {continue;}
                list ($spaces,$left,$right) = explode ('|',$string.'|||'); #### 2020-12-08
                $lang[$left]    = $right;}
}
# ----------------- check for supported forecast
$arr_fcts  = array ('fct_wu_block.php', 'fct_wxsim_block.php','fct_ec_block.php', 'fct_darksky_block.php', 'fct_yrno_block.php');
#### 2020-11-28
$fct_used_set   = '';                 
foreach ($arr_fcts as $fct)
     {  if ( $fct_default == $fct)
             {  $set    = true;}
        else {  $set    = false;}
        $$fct   = $set;                // initilize every possible forecast.php to "not used", only the one used will get true
        $name   = str_replace ('.php','_used',$fct);
        $$name  = $set;                // initilize every possible forecast_used to "not used"
}  // eo check   
#### 2020-11-28 
#-----------------------------------------------
#          WEATHER-PROGRAM  set images and links
$supported = array ('cumulus','weewx','weathercat','DWL','DWL_v2api',
                    'meteohub','wswin','wu','weatherlink','wifilogger',
                    'wf','AWapi','ecoLcl','wd','leuven');  #2021-09-22
foreach ($supported as $wx) {$$wx = false;}
$$livedataFormat= true; 
#
$weatherprogram = array();
#
if ($livedataFormat == 'cumulus') {
        $livedata       = clean_livedata ($livedata);  #####  2021-03-03 
        $weatherprogram['href'] = 'https://cumulus.hosiene.co.uk/';
        $weatherprogram['img']  = '<img src="img/cumulus.svg" width="135" height="35" alt="Cumulus">';} 
elseif ($livedataFormat == 'weewx') {
        $livedata       = clean_livedata ($livedata);  #####  2021-03-03 
        $weatherprogram['href'] = 'http://www.weewx.com/';
        $weatherprogram['img']  = '<img src="img/weewx-logo-128x128.png" width="35" height="35" alt="Weewx" style="background-color: lightgrey; padding: 2px;">';} 
elseif ($livedataFormat == 'weathercat') {
        $livedata       = clean_livedata ($livedata);  #####  2021-03-03 
        $weatherprogram['href'] = 'https://athena.trixology.com/';
        $weatherprogram['img']  = '<img src="img/weathercat.png"  alt="WeatherCat">';} 
elseif ($livedataFormat == 'DWL') {
        $livedata       =  $fl_folder.'wlcom.json'; 
        $weatherprogram['href'] = 'https://www.weatherlink.com/';
        $weatherprogram['img']  = 'WeatherLink Cloud';} 
elseif ($livedataFormat == 'DWL_v2api') {
        $livedata       =  $fl_folder.'wlcomv2API.json'; 
        $weatherprogram['href'] = 'https://www.weatherlink.com/';
        $weatherprogram['img']  = 'WeatherLink Cloud<br />v2 API';} 
elseif ($livedataFormat == 'meteohub') {
        $livedata       = clean_livedata ($livedata);  #####  2021-03-03 
        $weatherprogram['href'] = 'https://wiki.meteohub.de/Main_Page';
        $weatherprogram['img']  = '<img src="img/meteohub.png" width="120"  alt="meteohub">';} 
elseif ($livedataFormat == 'wswin') {
        $livedata       = clean_livedata ($livedata);  #####  2021-03-03 
        $weatherprogram['href'] = 'https://www.pc-wetterstation.de/';
        $weatherprogram['img']  = '<img src="img/wswin.gif" width="32"  alt="wswin">';} 
elseif ($livedataFormat == 'wu') {
        $livedata       = $fl_folder.'wucom.txt';
        $weatherprogram['href'] = 'https://www.wunderground.com/dashboard/pws/'.$wuID;
        $weatherprogram['img']  = '<img src="img/wunderground1.svg" width="120"  alt="weatherdisplay">';} 
elseif ($livedataFormat == 'weatherlink') {
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28      
        $weatherprogram['href'] = 'https://www.davisinstruments.com/product/weatherlink-computer-software/';
        $weatherprogram['img']  = '<img src="img/wl.jpg" width="64" height="32" alt="weatherlink">';} 
elseif ($livedataFormat == 'wifilogger') {
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28     
        $weatherprogram['href'] = 'https://wifilogger.net/';
        $weatherprogram['img']  = '<img src="img/WFL_logo.png"  alt="weatherlink">';} 
elseif ($livedataFormat == 'MB_rt') {
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28      
        $weatherprogram['href'] = 'https://www.meteobridge.com/wiki/index.php/Home';
        $weatherprogram['img']  = '<img src="img/meteobridge.png"  alt="meteobridge_rt"  >';} 
elseif ($livedataFormat == 'wf') {
        $livedata       = $fl_folder.'weatherflow.txt';
        $weatherprogram['href'] = 'https://weatherflow.com/';
        $weatherprogram['img']  = '<img src="img/wflogo.svg" width="100" alt="weatherflow"  >';} 
elseif ($livedataFormat == 'AWapi') {
        $livedata       = $fl_folder.'ambient.txt';
        $weatherprogram['href'] = 'https://ambientweather.net/';
        $weatherprogram['img']  = '<img src="img/ambient-weather-logo.png"  alt="weatherflow"  >';} 
elseif ($livedataFormat == 'ecoLcl') {
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28
        $weatherprogram['href'] = 'https://www.ecowitt.com/';
        $weatherprogram['img']  = '<img src="img/ecowitt.png"  alt="weatherflow"  >';} 
elseif ($livedataFormat == 'WDapi') {
        $livedata       = $fl_folder.'WDapi.txt';
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28
        $weatherprogram['href'] = 'http://www.weather-display.com/';
        $weatherprogram['img']  = '<img src="img/wdbanner.jpg" width="135"  alt="weatherdisplay">';}
elseif ($livedataFormat == 'wd') {
        $livedata       = clean_livedata ($livedata);  #####  2020-09-30  cron problem  #### 2020-11-28
        $weatherprogram['href'] = 'http://www.weather-display.com/';
        $weatherprogram['img']  = '<img src="img/wdbanner.jpg" width="135"  alt="weatherdisplay">';}
else {  $livedata       = clean_livedata ($livedata);                           #  2021-09-22  add default
        $weatherprogram['href'] = 'https://pwsdashboard.com/';
        $weatherprogram['img']  = ''; #<img src="img/wdbanner.jpg" width="135"  alt="weatherdisplay">';
        }                                              
if (isset ($use_left_img)  && trim($use_left_img) <> '')
     {  $weatherprogram['img']  = '<img src="'.$use_left_img.'" max-width="135"  alt="image">';
        }
#                                                                               #  2021-09-22  add default
#
function clean_livedata ($live)   #####  2020-09-30  ecowitt cron problem 
     {  if     (substr($live,0,2) == './')   {  $return = __DIR__.substr($live,1); }
        elseif (substr($live,0,3) == '../')  {  $return = __DIR__.'/'.$live; }
        else                                 {  $return = $live;}
        return $return;
} // eof  clean_livedata 