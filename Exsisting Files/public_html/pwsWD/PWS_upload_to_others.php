<?php  $scrpt_vrsn_dt  = 'PWS_upload_to_others.php|01|2021-02-08|';  # cleaned logging + check_value | release 2012_lts
#
# to upload your weatherdata to other weather-networks
# uploading is done by the PWS_cron_stationcron.php script
#
# You have to set the upload to true and insert your keys
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#   The script will upload current stations data
#               to weather-nets, for those users 
#            where the weather-program/-net does 
#                               not support that
#-----------------------------------------------
if (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
#
#  check also here  https://www.wxforum.net/index.php?topic=38500.msg396026#msg396026
#
# AWEKAS        Protocol type: WU
# Server:       ws.awekas.at  Port: 80
# Path:         /weatherstation/updateweatherstation.php?
# Station ID:   Your AWEKAS station ID
# Station Key:  Your AWEKAS password
#
$awekas_upload  = false;      
$awekas_id      = '_insert_your_id_here_';
$awekas_pw      = '_insert_your_password_here_';
#
#
# PWSWEATHER    Protocol type: WU
# Server:       pwsweather.com  Port: 80
# Path:         /pwsupdate/pwsupdate.php?
# siteid:       Your PWSWEATHER station ID
# Station Key:  Your PWSWEATHER password
#
$pwsweather_upload  = false;      
$pwsweather_id      = '_insert_your_id_here_';
$pwsweather_pw      = '_insert_your_password_here_';
#
#
# WOW-metoffice Protocol type: WU
# Server:       wow.metoffice.gov.uk Port: 80
# Path:         /automaticreading? 
# siteid:       The unique numeric id of the site  f.i. f5456a81-9aca-ea11-bf21-0003ff59a78d
# AuthenticationKey:  A pin number, chosen by the user to authenticate with WOW  234567
#
$wowweather_upload  = true;      
$wowweather_id      = '_insert_your_id_here_';
$wowweather_pw      = '_insert_your_password_here_';
#
/*    [ID] => 123456
    [PASSWORD] => 678901
    [tempf] => 63.0
    [humidity] => 81
    [dewptf] => 57.0
    [windchillf] => 63.0
    [winddir] => 314
    [windspeedmph] => 0.89
    [windgustmph] => 2.24
    [rainin] => 0.000
    [dailyrainin] => 0.000
    [weeklyrainin] => 0.098
    [monthlyrainin] => 1.909
    [yearlyrainin] => 12.571
    [solarradiation] => 85.88
    [UV] => 1
    [indoortempf] => 68.4
    [indoorhumidity] => 69
    [baromin] => 30.050
    [AqPM2_5] => 10.0
    [soilmoisture] => 37
    [soilmoisture2] => 48
    [lowbatt] => 0
    [dateutc] => now
    [softwaretype] => GW1000A_V1.6.1
    [action] => updateraw
    [realtime] => 1
    [rtfreq] => 5   */
#
# uploads to array
$uto    = array();
$n      = -1;
if ($awekas_upload == true && $awekas_id <> '_insert_your_id_here_' && $awekas_pw <> '_insert_your_password_here_')
     {  $n++;
        $uto[$n]['key'] = 'awekas';
        $uto[$n]['id']  = 'ID='.$awekas_id;
        $uto[$n]['pw']  = 'PASSWORD='.htmlentities ($awekas_pw);
        $uto[$n]['url'] = 'ws.awekas.at/weatherstation/updateweatherstation.php?';}

if ($pwsweather_upload == true && $pwsweather_id <> '_insert_your_id_here_' && $pwsweather_pw <> '_insert_your_password_here_')
     {  $n++;
        $uto[$n]['key'] = 'pwsweather';
        $uto[$n]['id']  = 'ID='.$pwsweather_id;
        $uto[$n]['pw']  = 'PASSWORD='.htmlentities ($pwsweather_pw);
        $uto[$n]['url'] = 'pwsweather.com/pwsupdate/pwsupdate.php?';}  #print_r($uto);  

if ($wowweather_upload == true && $wowweather_id <> '_insert_your_id_here_' && $wowweather_pw <> '_insert_your_password_here_')
     {  $n++;
        $uto[$n]['key'] = 'wow';
        $uto[$n]['id']  = 'siteid='.$wowweather_id;
        $uto[$n]['pw']  = 'siteAuthenticationKey='.htmlentities ($wowweather_pw);
        $uto[$n]['url'] = 'wow.metoffice.gov.uk/automaticreading?';}  #print_r($uto);  

$dateutc= str_replace (':','%3A',gmdate ('Y-m-d+H:i:s'));

foreach ($uto as $key => $upl)
     {  $params = '';  #print_r($upl); 
        $params.= $upl['id'].'&';
        $params.= $upl['pw'].'&';
        if ($key == 'wow' || $key == 'awekas') {$now = $dateutc; } else {$now = 'now';}
        $params.= 'dateutc='.           $now.'&';
        $params.= 'softwaretype='.      str_replace(' ','_',trim($weather['swversion'])).'&'; #### 2021-01-25
        $params.= 'action='.            'updateraw'.'&';
#        $params.= 'realtime='.          '1'.'&';
#        $params.= 'rtfreq='.            '5'.'&';

        $params.= 'baromin='.           convert_baro  ( $weather['barometer'],  $pressureunit,'in').'&'; 
        $params.= 'dailyrainin='.       convert_precip( $weather['rain_today'],$rainunit,'in').'&';  
        $params.= 'dewptf='.            convert_temp (  $weather['dewpoint'],   $tempunit,'f')  .'&';
        $params.= 'humidity='.                          $weather['humidity']                    .'&';
        $params.= 'rainin='.            '0.00'.'&'; #convert_precip( $weather[''],$rainunit,'mph').'&';  
        if ( check_value ('soil_mst1') )
             {  $params.= 'soilmoisture='.              $weather['soil_mst1']                   .'&'; }
        $params.= 'tempf='.             convert_temp (  $weather['temp'],       $tempunit,'f')  .'&';
        $params.= 'winddir='.                           $weather['wind_direction']              .'&'; 
        $params.= 'windspeedmph='.      convert_speed ( $weather['wind_speed'], $windunit,'mph').'&';  
        $params.= 'windgustmph='.       convert_speed ( $weather['wind_gust_speed'],$windunit,'mph').'&';  

        $params.= 'solarradiation='.                    $weather['solar']                       .'&';
        $params.= 'windchillf='.        convert_temp (  $weather['windchill'],  $tempunit,'f')  .'&';          
        $params.= 'UV='.                                $weather['uv']                          .'&';
        if ( check_value ('temp_indoor') ) 
             {  $params.= 'indoortempf='.convert_temp ( $weather['temp_indoor'],$tempunit,'f')  .'&';
                $params.= 'indoorhumidity='.            $weather['humidity_indoor']             .'&'; }
        if ( check_value ('pm25_crnt1') )
             {  $params.= 'AqPM2_5='.                   $weather['pm25_crnt1']                  .'&'; }
        $params.= 'realtime='.          '1'.'&';
        $params.= 'rtfreq='.            '5';
        $ch     = curl_init(); 
        $url    = $upl['url'].$params;
        $timeout= 10;
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);        // data timeout 
        $result = curl_exec ($ch);
        $info	= curl_getinfo($ch);
        $error  = curl_error($ch);
        curl_close ($ch);
  #     echo '<!-- $error='.$error.' $result='.$result.PHP_EOL.print_r($info,true).PHP_EOL.' -->';  exit;
        if ($error <> '')  {  echo __LINE__.'invalid CURL '.$error.PHP_EOL; }
       } // eo foreach      
      
echo ' + '.count ($uto).' uploads ';
