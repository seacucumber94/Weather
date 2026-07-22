<?php  $scrpt_vrsn_dt  = 'PWS_tweet_this.php|01|2021-12-04|';  # PHP 8.1 +release 2012_lts
#
#  very specialized script - not for general use
#
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) 
     {  $filenameReal = __FILE__;			
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
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ------------------------  all translated texts
$name_l         = lang ('Today');
$start_l        = lang ('The weather at');   // Het weer om 13:00 :
$last_tweet_l   = lang ('Todays last tweet at');
$sunrise_l      = lang ('Sunrise');
$sunset_l       = lang ('Sunset');
$temp_shrt_l    = lang ('Temp');
$temp_HL_shrt_l = lang ('HiLo');
$humid_shrt_l   = lang ('Humid');
$wind_shrt_l    = lang ('Wind');
$from_shrt_l    = lang ('from');
$baro_shrt_l    = lang ('Baro');
$rain_shrt_l    = lang ('Rain');
$uv_shrt_l      = lang ('UV_index');
#
$daylight_l     = lang ('There is  #hh# hour and #hh# minutes of daylight.');
#
# ------------------------    which weather data
#
$temp_data      = true; // Temp:22°c 
$temp_HL        = true; // (H22/L15) 
$humid_data     = true; // Luchtv:53%
$wind_data      = true; // Wind:1km/h uit O
$baro_data      = true; // Baro: 1015.7hPa
$rain_data      = true; // Regen 0.0 mm 
$webcam_hours   = 3;    // webcam only every so many hours + first & last message of the day
$round_nill     = true; // no rounding of temp a.s.o.  Overrides settings
#
$show_first     = 7;    // no tweets before this hour 
$show_last      = 25;   // no tweets after this hour
$show_only_sun  = true; // only tweets in day-time = from sunrise to sunset 
#
$extra_txt      = '';   // example ' #new_york'
#
#
#-----------------------------------------------
#                      calculate important hours
$now            = time();               // time script is started
#
$hour           = (int) date('H',$now+120); // hour this script is started
#
$sun_arr        = date_sun_info((int) $now,$lat, $lon);  # 2021-12-04 PHP 8.1
$sunrise        = $sun_arr['sunrise'];  #date_sunrise($now, SUNFUNCS_RET_TIMESTAMP, $lat, $lon);  
$sunrise_hr     = (int) date('H', $sunrise);
if ($show_only_sun  == true && $sunrise_hr > $show_first)
     {  $show_first     = $sunrise_hr; }
#
$sunset 	= $sun_arr['sunset'];  #date_sunset ($now, SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
$sunset_hr      = (int) date('H', $sunset);
if ($show_only_sun  == true && $sunset_hr < $show_last)
     {  $show_last      = $sunset_hr + 1; }
#
#-----------------------------------------------
#         skip all tweets outside the set period
if ( $hour < $show_first ||  $hour > $show_last ) {  echo 'succes, no tweet needed'; return; }        
#
#-----------------------------------------------
#  PWS_found:  scan for texts in a string
#-----------------------------------------------
if (!function_exists( 'PWS_found') ) {
        function PWS_found($haystack, $needle){
                $pos = strpos(' '.$haystack, $needle);
                if ($pos === false) 
                     { return false;} 
                else { return true;}
        }  // eof PWS_found
} // eo function_exists
#
#-----------------------------------------------
#                                 load key files
$your_ConsumerKey       = $your_ConsumerSecret  = 
$your_AccessToken       = $your_AccessTokenSecret = '';
#  
require_once('./_my_settings/twitter_keys.php'); 
if ($your_ConsumerKey == '')    { echo 'failed key-file missing'; return;}
#
#-----------------------------------------------
#      check REQUEST params, check if valid user
if (!isset ($_GET['check']) )   {  echo 'failed check not available'; return;}
$check  = trim($_GET['check']);
if (!isset ($your_check) || $your_check <> $check || $check == '')
                                {  echo 'failed- check invalid'; return;}
#
#---------------------------------    initialize
#
# --------------------------------  set rounding
if ($round_nill == true) {$dec_tmp = $dec_wnd = 0; }
#
# --- find the name of the server this script is running on
$url  = '';
if (isset($_SERVER['HTTPS'])) 
     { $url = 'https://'.$_SERVER['SERVER_NAME']; } else {$url = 'http://'.$_SERVER['SERVER_NAME'];}
if (isset($_SERVER['SCRIPT_NAME'])) 
     { $url  .= str_replace (basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);}
if ($url <> '') {$extra_txt      = ' '.$url;}
#
$timeFormatShort       = $timeFormatShort;     // how to display the sunrise / sunset time hours:minutes
$weather_time   = date($timeFormatShort,$now); // time will be displayed according to the settings
#
# --------------------------------------  webcam 
$show_webcam    = $mywebcam;    // from settings
# $show_webcam    = false;       // remove comment marker at first position if you do not want your webcam images in the tweet
$media_files    = array();  
#
#  All webcam images are stored in 1 array
#  Do not use http/https links but use './name.jpg' type links
#
if ($show_webcam === true) {
        $media_files[]  = 'https://admin.meteobridge.com/cam/2879431bf0d9c0992dd71df8ccf19fc9/camplus.jpg';
#       $media_files[]  = 'another image';          
}
# No we scan https/https , load the images and save in cache
foreach ($media_files as $key => $link) //  check if media files are http/https ones
     {  $string = strtolower ($link);
        if (PWS_found($string, 'https://') === false
        &&  PWS_found($string, 'http://')  === false)
             {  continue;}
        if      (PWS_found($string, '.jpg') === true )  {$img_type = '.jpg';}
        elseif  (PWS_found($string, '.png') === true )  {$img_type = '.png';}
        else                                            {$img_type = '.jpeg';} 
#        
        $ch             = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_URL, $link);
        curl_setopt ($ch, CURLOPT_FILETIME, 1);
        curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        $rawData        = curl_exec ($ch);
        if (isset($_REQUEST['debug']) && substr(strtolower($_REQUEST['debug']).' ',0,1 ) == 'y' ) 
             {  $info	        = curl_getinfo($ch);
                $errors         = curl_error($ch);
                $return         .= '<p><b>Return</b> codes: <pre>';
                $return         .= print_r ($info,true);
                $return         .= '</p>'.PHP_EOL;
                $return         .= '<p><b>Error</b> codes: '.$errors.'</p>'.PHP_EOL;
                echo $return;}
        curl_close ($ch);
        file_put_contents ( './demodata/media'.$key.'.jpg',$rawData); 
        $media_files[$key]      = './demodata/media'.$key.$img_type;
} // eo foreach media file
# echo '<pre>test1'.print_r($media_files,true); exit;
#
#-----------------------------------------------
#                 add webcam every so many hours
if ( $show_webcam == true && ($hour % $webcam_hours) === 0)  
     {  $webcam = true;}
else {  $webcam = false;}  
# $webcam = true;  // for testing
#-----------------------------------------------
#             calculate day-light time for today
$dlength        = $sunset - $sunrise;
$dlengthHr      = floor ($dlength /3600);
$dlengthMin     = round (($dlength - (3600 * $dlengthHr) ) / 60);
#-----------------------------------------------
#          generate standard texts for this hour
$this_hr_txt    = $start_l.': '.$weather_time.PHP_EOL;
$this_day_txt   = $daylight_txt = $bye_txt      = '';
#

if ($hour === $show_first )     // first tweet of the day
     {  $this_day_txt    = $sunrise_l.': '.date($timeFormatShort,$sunrise).'.'.PHP_EOL;
        $from           = array ('#hh#','#mm#');
        $to             = array ($dlengthHr,$dlengthMin);
        $daylight_txt   = str_replace ($from, $to, $daylight_l).PHP_EOL; # 'Er is '.$dlengthHr.' uur en '.$dlengthMin.' minuten daglicht.'.PHP_EOL;
        $webcam         = $show_webcam;}
if ($hour === $show_last)   // last tweet of the day
     {  $this_hr_txt    = $last_tweet_l .': '.$weather_time.PHP_EOL;
        $webcam         = $show_webcam;}
if ($hour === $sunset_hr)   // sunset
     {  $this_day_txt   = $sunset_l.': '.date($timeFormatShort,$sunset).'.'.PHP_EOL;  }
#
if ($webcam == true)  { $extra_txt     .= ' #weathercam';}  
#
#-----------------------------------------------
#     generate all weather data
$data='';
if ($temp_data) {
        $temp_x = number_format($weather['temp'],$dec_tmp);
        $data   .= $temp_shrt_l.' '.$temp_x.'°'.$tempunit;
        if ($temp_HL) {
                $temp_h = number_format($weather['temp_high'],$dec_tmp);
                $temp_l = number_format($weather['temp_low'], $dec_tmp);
                $data   .= ' ('.$temp_HL_shrt_l.' '.$temp_h.'-'.$temp_l.')';
        }
        $data   .= ', ';
} // eo temp data
if ($humid_data) {
        $data           .= $humid_shrt_l.' '.$weather['humidity'].'%, ';
} // eo humid_data
if ($wind_data) {
        $wind_x         = number_format($weather['wind_speed'],$dec_wnd);
        $data           .= $wind_shrt_l.' '.$wind_x.' '.$windunit.' ';
        $compass        = windlabel($weather['wind_direction']); 
        $cmp_l          = lang($compass);
        $data   .= $from_shrt_l.' '.$cmp_l.', ';
} // eo wind_data
if ($baro_data) {
        $baro_x         = number_format($weather["barometer"],$dec_baro,'.','');
        $data           .= $baro_shrt_l.' '.$baro_x.' '.$pressureunit.', ';        
} // eo baro_data
if ($rain_data) {
        $rain_x         = number_format((float) $weather['rain_today'],$dec_rain);
        $data           .= $rain_shrt_l.' '.$rain_x.' '.$rainunit; 
} // eo rain_data
# 
# ---------------------------------   weatherflow option
if ($weatherflowoption == true && $uvsolarsensors == 'wf')
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') using weatherflow data '.PHP_EOL;
        $weather['uv']          = $weatherflow['uv'];}
if ( $uvsolarsensors == 'darksky')
     {  $scrpt          = 'fct_darksky_shared.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
        include_once $scrpt;
        if (isset ($darkskyhourlyuv))
             { $weather['uv']  = (float) $darkskyhourlyuv;}
        }
if ($uvsolarsensors <> false) {
        $uv_x   = round ($weather['uv']);
        $data   .= ', '.$uv_shrt_l.' '.$uv_x;
} // eo UV_data
#
#-----------------------------------------------
#       combine all texts to tweet into 1 string
$twitter_txt    =  $this_day_txt. $daylight_txt. $this_hr_txt . $data. $bye_txt. $extra_txt;  # echo $twitter_txt; exit;
#
#-----------------------------------------------
#                          load codebird scripts
require_once('./others/codebird.php'); 
#
#
\Codebird\Codebird::setConsumerKey($your_ConsumerKey, $your_ConsumerSecret);
$cb = \Codebird\Codebird::getInstance(); 
$cb->setToken($your_AccessToken,$your_AccessTokenSecret); 
#
#-----------------------------------------------
#                    load optional webcam images
if ($webcam == true)
     {  $media_ids      = array();
        #
        foreach ($media_files as $file)                         // upload all media files
             {  $reply          = $cb->media_upload(['media' => $file]);
                $media_ids[]    = $reply->media_id_string; }    // and collect their IDs
        $string         = implode(',', $media_ids);}            // convert media ids to string list
else {  $string         = '';}
#
$params = array(
  'status'      => $twitter_txt
 ,'media_ids'   => $string
); 
$reply = $cb->statuses_update($params); # print_r ($reply);
echo 'succes';
