<?php  $scrpt_vrsn_dt  = 'sensorluft.php|01|2020-11-06|';  # release 2012_lts 
# ------------------------------------- SETTINGS 
$timezone       = 'Europe/Brussels';   
#$timezone       = 'America/New_York';
#
$data_dir       = '../jsondata';
#$data_dir       = './cache';
#
$save_last_mes  = true;         // copy upload to disk also
#$save_last_mes  = false;

$load_regional  = true; // load sensor data to LOCAL site (f.i. leuvenair) needs userid-pw
$load_regional  = false; 
#------------------------------- END OF SETTINGS 
#
#        DO NOT CHANGE BELOW IF YOU ARE NOT SURE 
#
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
#
# Original script from opendata-stuttgart
#
# https://github.com/opendata-stuttgart/madavi-api/blob/master/data.php
#-----------------------------------------------
#  display source of script if requested so
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
#-----------------------------------------------
# adapted to be used 
#       in the template environement
#       to upload to local / regional website
#-----------------------------------------------
$pageName	= 'sensorluft.php'; 
$pageVersion	= '4.00 2018-03-29';
$pageUpdated	= 'release version'; 
#--------------------------------------- History
# 4.00 2018-02-17 release version
# 4.00 2018-03-29 RC version, new 24hour / 7days file
# ----------------------------------------------
$test_mode      = false;
#$test_mode      = true; 
$test_text      = basename(__FILE__,'.php').' running in test_mode: '; 
#
$luft_keys['P1']                = 'P1';
$luft_keys['P2']                = 'P2';
$luft_keys['SDS_P1']            = 'P1';
$luft_keys['SDS_P2']            = 'P2';
$luft_keys['temperature']       = 'temp';
$luft_keys['humidity']          = 'hum';
$luft_keys['pressure']          = 'baro';
$luft_keys['BME280_temperature']= 'temp';
$luft_keys['BME280_humidity']   = 'hum';
$luft_keys['BME280_pressure']   = 'baro';
$luft_keys['BMP_temperature']   = 'temp';
$luft_keys['BMP_pressure']      = 'baro';
$luft_keys['samples']           = 'samples';
$luft_keys['min_micro']         = 'min_micro';
$luft_keys['max_micro']         = 'max_micro';
$luft_keys['signal']            = 'signal';
#
if (isset ($timezone)) 
     {  if (!function_exists('date_default_timezone_set')) 
             {  putenv("TZ=" . $timezone);} 
        else {  date_default_timezone_set($timezone);} }
#
# read header check ? sensor ID ('esp8266-'+ChipID)
#-----------------------------------------------
if      (isset($_SERVER['HTTP_SENSOR']))   { $header_sensor = $_SERVER['HTTP_SENSOR']; }
elseif  (isset($_SERVER['HTTP_X_SENSOR'])) { $header_sensor = $_SERVER['HTTP_X_SENSOR'];}
elseif  ($test_mode == false) { die ('unknown problem');}  // invalid access, some tries to mess things up
else  { $header_sensor = 'we are testing';}
#
#---------------------------read data for sensor 
if ($test_mode == true)
     {  $json   = '{"esp8266id": "1329006", "software_version": "NRZ-2017-099", "sensordatavalues":[{"value_type":"SDS_P1","value":"2.03"},{"value_type":"SDS_P2","value":"1.43"},{"value_type":"temperature","value":"20.40"},{"value_type":"humidity","value":"49.80"},{"value_type":"samples","value":"605932"},{"value_type":"min_micro","value":"230"},{"value_type":"max_micro","value":"157026"},{"value_type":"signal","value":"-66"}]}'; 
        $json   = '{"esp8266id": "6389717", "software_version": "NRZ-2020-129", "sensordatavalues":[{"value_type":"BME280_temperature","value":"25.20"},{"value_type":"BME280_pressure","value":"101081.06"},{"value_type":"BME280_humidity","value":"47.67"},{"value_type":"samples","value":"4398705"},{"value_type":"min_micro","value":"32"},{"value_type":"max_micro","value":"20026"},{"value_type":"signal","value":"-36"}]}';      
        echo __LINE__.' '.$test_text . ' testfile loaded:'.$json.PHP_EOL;}
else {  $json   = file_get_contents('php://input');}
if ($json == false) { return;}  // invalid access, someone tries to mess things up
#
#------------------------ check if file is valid
$parsed_json= json_decode($json,true);  
$dataFALSE              = '';
if ( $parsed_json == FALSE) 
     {  $dataFALSE = __LINE__.': Invalid / no JSON data'; }
elseif (!array_key_exists ('sensordatavalues', $parsed_json) )
     {  $dataFALSE = __LINE__.': No data found'; } 
elseif (!array_key_exists ('value_type', $parsed_json['sensordatavalues'][0]) )
     {  $dataFALSE = __LINE__.': No measurements found'; } 
$p1found=$p2found=false;
if ($dataFALSE == '')
     {  foreach ( $parsed_json['sensordatavalues'] as $key => $arr)  
            {   #echo '<pre>'.print_r($arr,true); exit;
                if (    $arr['value_type'] == 'SDS_P2'
                     || $arr['value_type'] == 'P2' ) {$p2found = true;}
                if (    $arr['value_type'] == 'SDS_P1'
                     || $arr['value_type'] == 'P1' ) {$p1found = true;}
                } // eo data exists
        } // eo correct joson
if (  $p1found == false ||   $p2found == false  ) 
     {  $dataFALSE = __LINE__.' No P1 & P2 measurements found'; }       
if ($dataFALSE<> '')
     {  echo $dataFALSE.'<br />Check settings and data';
        $sf = fopen('./stats.txt','a');// append to log file
        if($sf) 
             {  $time   = gmdate ('r');
                fwrite($sf,$time.' = '.$json."\n");
                fclose($sf);} 
        return;}
#
#-------- load last measurement to regional site
if ($load_regional <> false && $test_mode <> true)
     {  include 'sensorregional.php';
        if (isset ($regional_user) &&  $regional_user <> 'user')
             {  $ch = curl_init();
                curl_setopt ($ch, CURLOPT_URL, $regional_url);
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt( $ch, CURLOPT_HTTPHEADER,array(
                        'Content-Type: application/json',
                        'X-Sensor: '.$header_sensor,
                        'Content-Length: '.strlen($json),
                        'Connection: close'));
                $result = curl_exec($ch);
                curl_close ($ch);} }
#
#------------------- save last measurements-json
if ($save_last_mes) 
     {  $lastdata = $data_dir.'/last_luft_data_'.$header_sensor.'.json';
        if ($test_mode == true) 
             {  echo __LINE__.' '.$test_text.' last data saved as: '.$lastdata.PHP_EOL;}
        $ok     = file_put_contents($lastdata,$json);
        if ($ok == false) {echo __LINE__.' '.$test_text.' saving '.$lastdata.' not OK'.PHP_EOL;}
        }
#
#-----------------------------   create 24h file
if ($test_mode == true) echo __LINE__.' '.$test_text.' start create 24h file'.PHP_EOL; 
#
$results= $parsed_json;
#
header_remove();        // ??
#
#------- check if data dir exists, create if not
if (!file_exists($data_dir)) 
     {  mkdir($data_dir, 0755, true);}
#
$file24 = $data_dir.'/luft_24data_'.$header_sensor.'.arr';
if (is_file($file24) && 1 == 1)
     {  $data           = unserialize (file_get_contents($file24));}
else {  $data           = array();
        $data['last']   = '';
        $data['P1']     = 0.0;
        $data['P2']     = 0.0;
        $data['temp']   = 0.0;
        $data['hum']    = 0.0;
        $data['baro']   = 0.0;
        $data['P1_24h'] = 0.0;
        $data['P2_24h'] = 0.0;
        $data['P1_7d']  = 0.0;
        $data['P2_7d']  = 0.0;
        $data['samples']   = 0.0;
        $data['min_micro'] = 0.0;
        $data['max_micro'] = 0.0;
        $data['signal']    = 0.0;
        $data['hours']  = array();      # 24 hours / YYMMDDHH count total 
        $data['days']   = array();      # 7 days   / YYMMDD   count total 
        file_put_contents($file24,serialize ($data));}    // add log for errors
#
$now            = time();    
$today          = date('Ymd',$now); 
$this_hour      = date('YmdH',$now); 
$to_old_hours   = date('YmdH',($now - 24*3600) ); 
$to_old_days    = date('Ymd', ($now - 7*24*3600) ); 
#
$data['last']   = $now;
#
#------  copy sensor data values to values array
if ($test_mode == true) echo __LINE__.' '.$test_text.' sensor values: '.print_r($parsed_json["sensordatavalues"],true).PHP_EOL;
#
foreach ($parsed_json["sensordatavalues"] as $arr) 
     {  $type           = $arr["value_type"];
        $key            = $luft_keys[$type];
        $data[$key]     = round( (float) $arr["value"],2); }
#
# ------------------ check if current hour exist in data
if (!isset ($data['hours'][$this_hour])) // new sensor or hour
     {  $data['hours'][$this_hour] = 
        array ( 'count'   => 0, 
                'P1_hour' => 0,   
                'P2_hour' => 0,) ;}
#
# ---------------------------- add new data to this hour
$data['hours'][$this_hour]['P1_hour']  += $data['P1'];
$data['hours'][$this_hour]['P2_hour']  += $data['P2'];
$data['hours'][$this_hour]['count']++;
#
$total_nr  = $total_P1 = $total_P2 = 0;
#
foreach ($data['hours'] as $key => $arr)
     {  if ($key < $to_old_hours)
             {  unset ($data['hours'][$key]);
                continue;}
        $total_nr       += $arr['count'];
        $total_P1       += $arr['P1_hour'];
        $total_P2       += $arr['P2_hour'];}
$data['P1_24h'] = round ($total_P1 / $total_nr, 2);
$data['P2_24h'] = round ($total_P2 / $total_nr, 2);
#
# ------------------  check if current day exist in data
if (!isset ($data['days'][$today])) // new sensor or hour
     {  $data['days'][$today] = 
        array ( 'count'   => 0, 
                'P1_day'  => 0,   
                'P2_day'  => 0,) ;}
#
# ---------------------------- add new data to this day
$data['days'][$today]['P1_day']  += $data['P1'];
$data['days'][$today]['P2_day']  += $data['P2'];
$data['days'][$today]['count']++;
#
$total_nr  = $total_P1 = $total_P2 = 0;
#
foreach ($data['days'] as $key => $arr)
     {  if ($key < $to_old_days)
             {  unset ($data['days'][$key]);
                continue;}
        $total_nr       += $arr['count'];
        $total_P1       += $arr['P1_day'];
        $total_P2       += $arr['P2_day'];}
$data['P1_7d'] = round ($total_P1 / $total_nr, 2);
$data['P2_7d'] = round ($total_P2 / $total_nr, 2);
#
#---------------------- print transmitted values
if ($test_mode == true) echo __LINE__.' '.$test_text.' values found: '.print_r($data,true); 
#
file_put_contents($file24,serialize ($data));
?>
ok