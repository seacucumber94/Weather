<?php   $scrpt_vrsn_dt  = 'cvt2.php|01|2023-02-15|';  # release 2012_lts   example using a json file
#
# example script when we want to use json data as extra data
# after adapting the name of the script is used in easyweather settings 
# the PWS_extra_data.php script will check if the file is php and do an include
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
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
#-----------------------------------------------
#                 where do we find the json file
$location       = './jsondata/wlcomv2API130329.json';
#
$arr_use_as     = array(                // set items you do not want to use at comment
        'pm_1'          => 'my_pm1_crt',
#
        'pm_10_nowcast' => 'my_pm10_nc',
        'pm_10'         => 'my_pm10_crt',
        'pm_10_1_hour'  => 'my_pm10_1hr',                
        'pm_10_3_hour'  => 'my_pm10_3hr',        
        'pm_10_24_hour' => 'my_pm10_24h',
#
        'pm_2p5_nowcast'=> 'my_pm2p5_nc',
        'pm_2p5'        => 'my_pm2p5_crt',
        'pm_2p5'        => 'pm25_crnt1',        // will be used as station sensor value
        'pm_2p5_1_hour' => 'my_pm2p5_1hr',
        'pm_2p5_3_hour' => 'my_pm2p5_3hr',        
        'pm_2p5_24_hour'=> 'my_pm2p5_24h',
        'pm_2p5_24_hour'=> 'pm25_24avg1',       // will be used as station sensor value
#
        'aqi_nowcast_desc'      => 'my_aqTxt_nc',
        'aqi_desc'              => 'my_aqTxt_crt',
        'aqi_1_hour_desc'       => 'my_aqTxt_1hr',
        'aqi_nowcast_val'       => 'my_aqi_nc',
        'aqi_val'               => 'my_aqi_crt',
        'aqi_1_hour_val'        => 'my_aqi_1hr',
#
); 
$arr_replace_as = array (
        'temp'          => array ( 'as' => 'temp', 'item' => 'temp_indoor2'),
        'hum'           => array ( 'as' => 'txt',  'item' => 'humidity_indoor2'),
        'dew_point'     => array ( 'as' => 'temp', 'item' => 'dewpoint__indoor2'),
        'heat_index'    => array ( 'as' => 'temp', 'item' => 'temp_indoor_feel2'),
);        
#
#                       check if file is present
$content = false;
$content = file_get_contents($location);
if ($content == false) { echo '# '.__LINE__.' file '.$location.' not found'; return;}
#
#        check if content of file  is valid json
$json = json_decode($content, true);  # echo __LINE__.print_r($json,true).print_r($content,true);
if ($json === NULL) {echo '# '.__LINE__.' error decoding json'; return;} 
#
# flatten the arrays 
function array_flatten($array) 
     {  $return = array();
        foreach ($array as $key => $value) {
                if (is_array($value))
                     {  $return = array_merge($return, array_flatten($value));} 
                else {  $return[$key] = $value;}
        } // eo fe
    return $return;} // eo array_flatten
$fields = array_flatten($json) ;  
ksort ($fields);      #  echo __LINE__.'<pre>'.print_r($fields,true);# exit;  
#
$strng     = '# extra_data from '.$location.' by '.$scrpt_vrsn_dt.PHP_EOL;
#|my_davis_pm10_c|text|%purpleair10_0davis%|!
foreach ($arr_use_as as $key => $value)
     {  if (!array_key_exists ($key,$fields) ) {continue;}
        $strng  .= '|'.$arr_use_as[$key].'|text|'.$fields[$key].'|! '.PHP_EOL;
        }
$unit_txt       = '|fromtemp|uom|F|!'.PHP_EOL;
foreach ($arr_replace_as as $key => $arr)
     {  if (!array_key_exists ($key,$fields) ) {continue;}  
        $strng  .= $unit_txt.'|'.$arr['item'].'|'.$arr['as'].'|'.$fields[$key].'|! '.PHP_EOL; 
        $unit_txt = '';
        }
$extra_data = $location;
return $strng;
