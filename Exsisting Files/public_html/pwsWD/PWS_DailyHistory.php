<?php $scrpt_vrsn_dt  = 'PWS_DailyHistory.php|01|2021-12-08|';  # PHP 8.1 + problem first day of year |  release 2012_lts
#
header('Content-type: text/plain; charset=UTF-8');
#
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
#------------------  EO display source of script
#
# -------------------  load the settings
# load settings when run stand-alone
$scrpt          = 'PWS_settings.php';
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
# $wu_csv_unit = "us";
# $wuID = "KNCMATTH2";
# $wu_start = "2021-01-01";
$testmode       = false;
$my_wu_ID       = trim($wuID); 

if ($wu_csv_unit == 'us') 
     {$my_units = 'english'; }
else {$my_units = 'metric';}

$my_api         = trim($wu_apikey);
$wu_data        = '/wudata/';

list ($my_first_year, $my_first_month, $my_first_day) = explode ('-',$wu_start.'-');
$now            = time();
$thisYear       = date ('Y', $now);
$thisMonth      = date ('m', $now);
$thisDay        = date ('d', $now);  $this_date=$thisYear.'-'.$thisMonth.'-'.$thisDay;
if ( (int) $my_first_year > $thisYear || (int) $my_first_year < 2008 ) 
     {  $my_first_year  = '2019';}
if ( (int) $my_first_month > 12 || (int) $my_first_month < 1)
     {  $my_first_month = '01';}
if ( (int) $my_first_day > 31 || (int) $my_first_day < 1)
     {  $my_first_day = '01';}  
wumessage ( __LINE__.' my startdate ='.$my_first_year.$my_first_month.$my_first_day.PHP_EOL);
$my_first_unix  = strtotime($my_first_year.$my_first_month.$my_first_day.'T000000');
#
# -----------------------  check the URL
check_url_params ();
# -----------------------
$thisYearMonth  = $thisYear.$thisMonth; 
$thisYMD        = $thisYearMonth.$thisDay;
$thisMonthDay   = $thisMonth.$thisDay;
$yesterdayUnix  = $now - 24*3600;
$yesterdayYMD   = date ('Ymd',$yesterdayUnix);
#
$max_days       = 30;  // number of days which can be requested in 1 call
$wu_data        = './wudata/';  
$filename_tmpl  = $wu_data.$my_wu_ID.'-'.$my_units.'-#YEAR#.arr';     #   IVLBRABA2-metric-2018.arr
#

#
if (!function_exists( 'array_key_last' ) )
     { function array_key_last ($array)
             {  end($array);  
                $return = key($array); #echo $return; exit;
                return $return;}
} // eo function exists
#
# ----------------------------------------------------------------------
# The day request are directly loaded from WU using the new API
# ----------------------------------------------------------------------
if ($url_graphspan == 'day')     
     {  $req_Ymd        = $url_year.$url_month.$url_day; # echo __LINE__.' '.$req_Ymd; exit;
        $hdr_metric_day = 'Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedKMH,WindSpeedGustKMH,'
                .'Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SolarRadiationWatts/m^2,SoftwareType,DateUTC'.PHP_EOL;
        $hdr_english_day = 'Time,TemperatureF,DewpointF,PressureIn,WindDirection,WindDirectionDegrees,WindSpeedMPH,WindSpeedGustMPH,'
                .'Humidity,HourlyPrecipIn,Conditions,Clouds,dailyrainIn,SolarRadiationWatts/m^2,SoftwareType,DateUTC'.PHP_EOL;
        $url    =  'https://api.weather.com/v2/pws/history/all?stationId='          .$my_wu_ID.'&format=json&numericPrecision=decimal&units='.substr($my_units,0,1).'&apiKey='.$my_api.'&date='.$req_Ymd  ;
        $urlTODAY= 'https://api.weather.com/v2/pws/observations/all/1day?stationId='.$my_wu_ID.'&format=json&numericPrecision=decimal&units='.substr($my_units,0,1).'&apiKey='.$my_api;
# https://api.weather.com/v2/pws/observations/all/1day?stationId=IVLAAMSG47&format=json&numericPrecision=decimal&units=m&apiKey=zzzzzz
#
#  check if correct old data is found
#
        $file_location  = false;
        $allowed_age    = 1800;
        if ($req_Ymd   <> $thisYMD)  // other days data cached only if setting = true
             {  $dirlocation    = $wu_data .$url_year.'/'.$url_year.$url_month.'-daily/';
                $file_location  = $dirlocation.$my_wu_ID.'-day-'.$req_Ymd  .'.txt'; #2019-07/IVLAAMSG47-day-20190711.txt 
                $allowed_age    = 400*24*3600;}
        elseif ($req_Ymd   == $thisYMD )                //  todays data cached for 30 minutes to not overload WU-api restrictions
             {  $dirlocation    = $wu_data ;
                $file_location  = $dirlocation.'today.txt';
                $allowed_age    = 1800;}       
        if ($file_location && file_exists ($file_location)  && time() < filemtime ($file_location) + $allowed_age)
             {  $hdr    = array();
                $hdr[]  = 'Pragma: public';
                $hdr[]  = 'Content-type: text/plain; charset=UTF-8';
                $hdr[]  = 'Accept-Ranges: bytes';
                $hdr[]  = 'Cache-Control:  max-age='.$allowed_age;
                $download_size  = filesize($file_location);
                $hdr[]  = 'Content-Length: '.$download_size;
                $hdr[]  = 'Connection: close';
                foreach ($hdr as $header) { header($header); }
                readfile($file_location);
                exit;     
        } // eo cache exist and is usefull 
        if ($req_Ymd  == $thisYMD) {$useURL = $urlTODAY;} else  {$useURL = $url;}
        $rawdata= file_get_contents_curl ($useURL);
        if ($rawdata == '' && $file_location && file_exists ($file_location))
             {  $hdr    = array();
                $hdr[]  = 'Pragma: public';
                $hdr[]  = 'Content-type: text/plain; charset=UTF-8';
                $hdr[]  = 'Accept-Ranges: bytes';
                $hdr[]  = 'Cache-Control:  max-age='.$allowed_age;
                $download_size  = filesize($file_location);
                $hdr[]  = 'Content-Length: '.$download_size;
                $hdr[]  = 'Connection: close';
                foreach ($hdr as $header) { header($header); }
                readfile($file_location);
                exit;     
        } // eo cache exist and is usefull and loaded data is empty
        $json   = json_decode ($rawdata,true );  # echo '<pre>'.__LINE__.' '.print_r($json,true); exit;
        if ($json == false || !is_array($json) || !is_array($json['observations']))
             {  header("HTTP/1.0 400 Not Found");
                die ('{"success":false,"error":"Data could not be loaded from WU<br />'.$logstring.'"}');}
#Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedKMH,WindSpeedGustKMH,Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SoftwareType,DateUTC
#2019-01-07 00:04:31,5.0,3.8,1035.1,West,278,0.0,1.6,92,0.0,,,0.0,meteobridge,2019-01-06 23:04:31,
#Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedKMH,WindSpeedGustKMH,Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SolarRadiationWatts/m^2,SoftwareType,DateUTC
#2019-11-10 00:02:43,2.2,-2.3,1007.3,ENE,58,0.0,0.0,72,0.0,,,0.0,0.00,meteobridge,2019-11-09 23:02:43,
        $windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
                 "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
# no windlabel function available without shared functions  # 2021-12-08
        if ($my_units == 'metric')
             {  $string =  'Time,TemperatureC,DewpointC,PressurehPa,WindDirection,WindDirectionDegrees,WindSpeedKMH,WindSpeedGustKMH,'
                .'Humidity,HourlyPrecipMM,Conditions,Clouds,dailyrainMM,SoftwareType,SolarRadiationWatts/m^2,DateUTC'.PHP_EOL;
                $rnd = 1;  
                $obs_unit =  'metric';   }
        else {  $string =  'Time,TemperatureF,DewpointF,PressureIn,WindDirection,WindDirectionDegrees,WindSpeedMPH,WindSpeedGustMPH,'
                .'Humidity,HourlyPrecipIn,Conditions,Clouds,dailyrainIn,SoftwareType,SolarRadiationWatts/m^2,DateUTC'.PHP_EOL;
                $rnd = 2;
                $obs_unit =  'imperial';  }  
        foreach( $json['observations'] as $upload) 
            {   $value          = $upload['obsTimeLocal'];  # [obsTimeLocal] => 2019-06-03 00:04:00
                $this_day       = substr($value,0,4).substr($value,5,2).substr($value,8,2); # echo $this_day; exit;        
                if ($this_day > $req_Ymd  )  {break;}                                                     
                $string .= (string) ($value).',';
                $value = (float) $upload[$obs_unit]['tempHigh'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
                $value = (float) $upload[$obs_unit]['dewptHigh'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
                $value = (float) $upload[$obs_unit]['pressureMax'];     $string .= (string) number_format( round ($value ,$rnd) ,$rnd ,'.','').',';  #### 2021-01-24
                $value = (int)   $upload['winddirAvg'];                 $string .= $windlabel[ (int) fmod((($value + 11) / 22.5),16) ].',';
                                                                        $string .= (string) ($value).',';
                $value = (float) $upload[$obs_unit]['windspeedHigh'];	$string .= (string) round ($value ,0).',';
                $value = (float) $upload[$obs_unit]['windgustHigh'];	$string .= (string) round ($value ,0).',';       
                $value = (float) $upload['humidityHigh'];	        $string .= (string) round ($value ,0).',';
                $value = (float) $upload[$obs_unit]['precipRate'];	$string .= (string) number_format( round ($value ,($rnd+1)) ,($rnd+1) ,'.','').',';
                $value = 'unknown';	                                $string .= (string) $value.','; # Conditions
                $value = 'unknown';	                                $string .= (string) $value.','; # Clouds
                $value = (float) $upload[$obs_unit]['precipTotal'];	$string .= (string) number_format( round ($value ,($rnd+1)) ,($rnd+1) ,'.','').',';
                $value = (float) $upload['solarRadiationHigh'];         $string .= (string) number_format( round ($value , 2) ,2,'.','').',';
                $value = 'unknown';	                                $string .= (string) $value.','; # Software type
                $value = $upload['obsTimeUtc'];                         $string .= (string) $value.PHP_EOL;  
        } // eo convert each json observation to 1 line in  .CSV
        $hdr    = array();
        $hdr[]  = 'Pragma: public';
        $hdr[]  = 'Cache-Control:  max-age='.$allowed_age;
        $hdr[]  = 'Content-type: text/plain; charset=UTF-8';
        $hdr[]  = 'Accept-Ranges: bytes';
        $download_size = strlen($string);
        $hdr[]  = 'Content-Length: '.$download_size;
        $hdr[]  = 'Connection: close';
        foreach ($hdr as $header) { header($header); }
        echo $string; # echo $file_location; 
        if (!$file_location) {exit;}
        file_put_contents('./temp.txt',$string);
        if (!is_dir ($dirlocation) ) {mkdir($dirlocation,0777,true);}
        rename('./temp.txt',$file_location);
        exit;    
} // eo day processing
# ----------------------------------------------------------------------
# All other requests are using the .arr files 
# ----------------------------------------------------------------------
#
# --- load the array(s) for the requested period
$data           = array();
$first_year     = $last_year = (int) $url_year;    
wumessage ( __LINE__.' '.$url_year.$url_month.$url_day.PHP_EOL); 
#
if ($url_graphspan == 'custom' && $url_year2 > $url_year)
     {  $last_year = (int) $url_year2;}
for ($n = $first_year; $n <= $last_year; $n++)
     {  $file_name      = str_replace ('#YEAR#' , $n, $filename_tmpl);  
        wumessage ( __LINE__.' '.$file_name.PHP_EOL);
#
# check if we have a new year to process
        if (!file_exists ($file_name) )
             {  $arr    = array();      // empty file for a new year
                file_put_contents ($file_name, serialize ($arr) );}
        else {  $arr    = unserialize ( file_get_contents($file_name));
                $filetime       = filemtime($file_name);}         # print_r($arr); exit;
#
# --- check if the year file is up-to-date
        if ($n == $thisYear)
             {  $untilDate       = $yesterdayYMD; } // this year should be have data until yesterday
        else {  $untilDate       = $n.'1231';}      // other years until Dec 31        
#
# --- check last item in array
        if (count ($arr) == 0)
             {  $last_key = false;
                $from_nr  = 0;
                $from_unix      = strtotime(($n - 1).'1231T000000');}        
        else {  $last_key =  array_key_last($arr); 
# --- remove errornous data
                if ($arr[$last_key] == '2019-12-31,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,')
                     {  unset ($data[$last_key]); 
                        $last_key =  array_key_last($arr);}
#
                $from_unix      = strtotime($last_key.'T000000');
                $from_nr        = date('z',$from_unix); } // eo check last item in array
#
        if ($from_unix < $my_first_unix) 
             {  wumessage (__LINE__.' $from_unix='.$from_unix.' $my_first_unix='.$my_first_unix.PHP_EOL); 
                $from_unix = $my_first_unix;    #### 2021-01-20 $from_unix = $my_first_unix - 3600; 
                $from_nr   = date('z',$from_unix);                
                }
#
        wumessage (__LINE__.' Year of file = '.$n.' Last item in array = '.$last_key.' Last item we need = '.$untilDate.' $url_graphspan ='.$url_graphspan.PHP_EOL);
        wumessage (__LINE__.' $from_unix='.date('c',$from_unix).' $my_first_unix='.date('c',$my_first_unix).' $from_nr='.$from_nr.PHP_EOL);
#
# ---- check last key in array against last key it should be
        if ($last_key <  $untilDate)
             {  wumessage (__LINE__.' load_missing_data'.PHP_EOL);
                load_missing_data() ;  }
        $data   = $data + $arr;
        unset ($arr);
} // eo load years
#
$file_location  = false;        // used to check if old but valid version is available
$allowed_age    = 6*3600;
switch ($url_graphspan) {
    case 'year': 
        $first  = $url_year.'0101';
        $last   = $url_year.'1231';
        $dirlocation    = $wu_data .$url_year.'/';
        $file_location  = $dirlocation.$my_wu_ID.'-year-'.$url_year.'.txt'; #IVLAAMSG47-year-2019.txt
        if ($url_year < $thisYear )     // old and complete years have at least 400 days
             {  $allowed_age    = 400*24*3600;}     
        break;
    case 'month': 
        $first  = $url_year.$url_month.'01';
        $last   = $url_year.$url_month.'31';
        $dirlocation    = $wu_data .$url_year.'/';
        $file_location  = $dirlocation.$my_wu_ID.'-month-'.$url_year.$url_month.'.txt'; #2019-07/IVLAAMSG47-month-201907.txt 
        if ($url_year.$url_month < $thisYearMonth ) 
             {  $allowed_age    = 400*24*3600;} 
        break;
    case 'week':
        $start  = strtotime($url_year.$url_month.$url_day.'T000000');
        $week_day_nr    = (int) date('w',$start);         // 0 (for Sunday) through 6 (for Saturday)
        $start_unix     = $start - 24*3600*$week_day_nr;
        $end_unix       = $start_unix + 6*24*3600;
        $start_ymd      = date ('Ymd',$start_unix);
        $end_ymd        = date ('Ymd',$end_unix);        
        $url_year       = substr($start_ymd,0,4);       $url_month      = substr($start_ymd,4,2);       $url_day        = substr($start_ymd,6,2);
        $url_year2      = substr($end_ymd,0,4);         $url_month2     = substr($end_ymd,4,2);         $url_day2       = substr($end_ymd,6,2);   
        $url_graphspan  = 'custom';    
    case 'custom': 
        $first  = $url_year.$url_month.$url_day;
        $last   = $url_year2.$url_month2.$url_day2;
        if ($url_year2.$url_month2.$url_day2 < $thisYMD ) 
             {  $allowed_age = 400*24*3600;} 
}   wumessage (__LINE__. ' $first = '.$first.' $last = '.$last.' $url_graphspan = '.$url_graphspan); 
# 
if ($my_units == 'metric')
     {  $string = 'Date,TemperatureHighC,TemperatureAvgC,TemperatureLowC,DewpointHighC,DewpointAvgC,DewpointLowC,'
        .'HumidityHigh,HumidityAvg,HumidityLow,PressureMaxhPa,PressureMinhPa,WindSpeedMaxKMH,WindSpeedAvgKMH,GustSpeedMaxKMH,PrecipitationSumCM'.PHP_EOL;}
else {  $string = 'Date,TemperatureHighF,TemperatureAvgF,TemperatureLowF,DewpointHighF,DewpointAvgF,DewpointLowF,'
        .'HumidityHigh,HumidityAvg,HumidityLow,PressureMaxIn,PressureMinIn,WindSpeedMaxMPH,WindSpeedAvgMPH,GustSpeedMaxMPH,PrecipitationSumIn'.PHP_EOL;}
$hdr    = array();
$hdr[]  = 'Pragma: public';
$hdr[]  = 'Cache-Control:  max-age='.$allowed_age;
$hdr[]  = 'Content-type: text/plain';
$hdr[]  = 'Accept-Ranges: bytes';
# check if .CSV is already assembled and if so => to user        
if ($file_location && file_exists ($file_location) && time() < filemtime ($file_location) + $allowed_age  )
     {  $download_size  = filesize($file_location);
        $hdr[]  = 'Content-Length: '.$download_size;
        $hdr[]  = 'Connection: close';
        foreach ($hdr as $header) { header($header); }
        readfile($file_location);
        exit;     }  
# assemble .CSV string 
$today  = date ('Ymd',time());
foreach ($data as $key => $line)
     {  if ($key < $first) {continue;}
        if ($key > $last)  {break;}
        if ($key > $today) {break;}
        $string .= $line.PHP_EOL;
}  // eo assemble string
$download_size = strlen($string);
$hdr[]  = 'Content-Length: '.$download_size;
$hdr[]  = 'Connection: close';
foreach ($hdr as $header) { header($header); }
echo $string;  
# echo '$file_location='.$file_location;
if (!$file_location) { exit;}
file_put_contents('./temp.txt',$string);
if (!is_dir ($dirlocation) ) { mkdir($dirlocation,0777,true); }
$result = rename('./temp.txt', $file_location);
exit;
#
function check_url_params ()
     {  global  $my_wu_ID, $my_units,$my_first_year, $my_first_month, $my_first_day,
                $url_ID, $url_graphspan, 
                $url_year,  $url_month,  $url_day,
                $url_year2, $url_month2, $url_day2;
# ---- check basic url-params
        if (    !array_key_exists ('ID',$_REQUEST)        ||  trim($_REQUEST['ID'])        == ''  
             || !array_key_exists ('graphspan',$_REQUEST) ||  trim($_REQUEST['graphspan']) == ''   ) 
             {  header("HTTP/1.0 400 Not Found");
                die ('{"success":false,"error":"Not enough basic parameters for this api"}');
        } // eo basic check
#
# ---- check graphspan
        $graphspan      = strtolower( trim($_REQUEST['graphspan']) );
        $allowed_grsp   = array ('year','month','week','day','custom');
        if (!in_array ($graphspan,$allowed_grsp) )
             {  header("HTTP/1.0 400 Not Found");
                die ('{"success":false,"error":'.__LINE__.' "invalid graphspan used"}');}
#
# check if "custom range' is requested => extra params needed
        if ($graphspan == 'custom')
             {  if (    !array_key_exists ('monthend',$_REQUEST) || trim($_REQUEST['monthend']) == '' 
                     || !array_key_exists ('dayend',$_REQUEST)   || trim($_REQUEST['dayend'])   == '' 
                     || !array_key_exists ('yearend',$_REQUEST)  || trim($_REQUEST['yearend'])  == '' 
                     || !array_key_exists ('year',$_REQUEST)     || trim($_REQUEST['year'])  == ''    
                     || !array_key_exists ('month',$_REQUEST)    || trim($_REQUEST['month'])  == '' 
                     || !array_key_exists ('day',$_REQUEST)      || trim($_REQUEST['day'])  == ''  )
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' graphspan needs extra parameters"}');} 
                else {  $url_year2  = substr('0000'.trim($_REQUEST['yearend']), -4);
                        $url_month2 = substr ('00' .trim($_REQUEST['monthend']),-2);
                        $url_day2   = substr ('00'  .trim($_REQUEST['dayend']), -2);}
        } // custom params
        else {  $url_month2 = $url_day2 = $url_year2= false;} // no custom needed

        $url_ID       = trim($_REQUEST['ID']);
        if ($url_ID <> $my_wu_ID )
             {  header("HTTP/1.0 400 Not Found");
                die ('{"success":false,"error":'.__LINE__.' "conflict between URL and Settings: WU-ID\'s  are different"}');}
               
        $url_graphspan= $graphspan;

#       add missing values
        $now            = time();
        $thisYear       = date ('Y', $now);
        $thisMonth      = date ('m', $now); 
        $thisDay        = date ('d', $now);

        if (array_key_exists ('year',$_REQUEST)  )    { $url_year  = substr('0000'.trim($_REQUEST['year']), -4);} else { $url_year  = $thisYear;}
        if (array_key_exists ('month',$_REQUEST) )    { $url_month = substr ('00' .trim($_REQUEST['month']),-2);} else { $url_month = $thisMonth;}
        if (array_key_exists ('day',$_REQUEST)   )    { $url_day   = substr ('00' .trim($_REQUEST['day']),  -2);} else { $url_day   = $thisDay;}
        
        switch ($url_graphspan) 
          { case  'custom': 
                if ($url_year2.$url_month2.$url_day2 < $url_year.$url_month.$url_day)  // it should be from date to date
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' invalid period '.$url_year2.$url_month2.$url_day.' parameters used"}');} 
                if  (   $url_year.$url_month.$url_day < $my_first_year.$my_first_month.$my_first_day) 
                    {   $url_year = $my_first_year; $url_month = $my_first_month; $url_day = $my_first_day; } 
                if  (   $url_year2 > $thisYear 
                   ||   $url_year2.$url_month2 > $thisYear.$thisMonth 
                   ||   $url_year2.$url_month2.$url_day2 > $thisYear.$thisMonth.$thisDay )
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' invalid period '.$url_year2.$url_month2.$url_day.' parameters used"}');}                                             
            case  'day': 
            case  'week':
                if ($url_year == '0000' && $url_month == '00' && $url_day == '00')   // alsp used for custom checking
                     {  $url_year       = $thisYear; 
                        $url_month      = $thisMonth;
                        $url_day        = $thisDay;}
                if  (  $url_year > $thisYear # || $url_year < $my_first_year
                  ||   $url_year.$url_month > $thisYear.$thisMonth #|| $url_year.$url_month < $my_first_year.$my_first_month
                  ||   $url_year.$url_month.$url_day > $thisYear.$thisMonth.$thisDay  
              #    ||   $url_year.$url_month.$url_day < $my_first_year.$my_first_month.$my_first_day
                        )
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' invalid period '.$url_year.$url_month.$url_day.' parameters used"}');}  
                break;
            case  'month':  
                $url_day= '';
                if ($url_year == '0000' && $url_month == '0000')        // no day needed
                     {  $url_year       = $thisYear; 
                        $url_month      = $thisMonth;}
                $url_month              = substr ('0'.$url_month,-2);
                if   (  $url_year > $thisYear                           || $url_year < $my_first_year
                  ||    $url_year.$url_month > $thisYear.$thisMonth     || $url_year.$url_month < $my_first_year.$my_first_month)
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' invalid period '.$url_year.$url_month.' parameters used"}');}  
                break;    
            case  'year':   
                $url_month =  $url_day = '';
                if ($url_year == '0000')  { $url_year = $thisYear; }
                if (  $url_year > $thisYear  || $url_year < $my_first_year)
                     {  header("HTTP/1.0 400 Not Found");
                        die ('{"success":false,"error":"'.__LINE__.' invalid period '.$url_year.' parameter used"}');}
                break;
        } // eo switch period
                   
} // eof    check_url_params           
#
function file_get_contents_curl ($WUsourceFile,$false=false)
     {  global $logstring;  
        $url    = str_replace  ('HTTP://', 'HTTPS://',$WUsourceFile);
	$ch     = curl_init();                                  // initialize a cURL session
	curl_setopt ($ch, CURLOPT_URL, $url);                   // connect to provided URL
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);           // verify peer off,removes a lot of errors with older hosts
#	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   // most host checks this nowadays
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);           // connection timeout
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5);                  // data timeout
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);        // return the data transfer
        curl_setopt ($ch, CURLOPT_NOBODY, false);               // do the download request without getting the body
        curl_setopt ($ch, CURLOPT_HEADER, false);               // include header information
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);            // follow Location: redirect allowed
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);                 //   but only one time
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	$rawdata= curl_exec ($ch);
	$info	= curl_getinfo($ch);
        $errors = curl_error($ch);
	curl_close ($ch);
        unset ($ch);
        $logstring      =  __LINE__.PHP_EOL.'<pre>'.print_r($errors,true).PHP_EOL.print_r($info,true).PHP_EOL.'</pre><br />'.PHP_EOL;
        return $rawdata;
} // eof file_get_contents_curl
#   
function json_assemble_csv ($oneday)
     {  global $my_units, $this_day;
        if ($my_units == 'metric') {$obs_unit =  'metric';}  else {$obs_unit =  'imperial';} 
        $string = substr($oneday['obsTimeLocal'],0,10).',';
        $value = (float) $oneday[$obs_unit]['tempHigh'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday[$obs_unit]['tempAvg'];	        $string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday[$obs_unit]['tempLow'];	        $string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday[$obs_unit]['dewptHigh'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday[$obs_unit]['dewptAvg'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday[$obs_unit]['dewptLow'];	$string .= (string) number_format( round ($value ,1) ,1 ,'.','').',';
        $value = (float) $oneday['humidityHigh'];	        $string .= (string) round ($value ,0).',';
        $value = (float) $oneday['humidityAvg'];	        $string .= (string) round ($value ,0).',';
        $value = (float) $oneday['humidityLow'];	        $string .= (string) round ($value ,0).',';
        if ($my_units == 'metric') {$rnd = 0;} else {$rnd = 2;}
        $value = (float) $oneday[$obs_unit]['pressureMax'];     $string .= (string) number_format( round ($value ,$rnd) ,$rnd ,'.','').',';  #### 2021-01-24
        $value = (float) $oneday[$obs_unit]['pressureMin'];     $string .= (string) number_format( round ($value ,$rnd) ,$rnd ,'.','').',';  #### 2021-01-24
        $value = (float) $oneday[$obs_unit]['windspeedHigh'];	$string .= (string) round ($value ,0).',';
        $value = (float) $oneday[$obs_unit]['windspeedAvg'];	$string .= (string) round ($value ,0).',';
        $value = (float) $oneday[$obs_unit]['windgustHigh'];	$string .= (string) round ($value ,0).',';       
        $value = (float) $oneday[$obs_unit]['precipTotal'];
        if ($my_units == 'metric') {$value = $value / 10;}      $string .= (string) number_format( round ($value ,2) ,2 ,'.','');
        return $string;
        }
#
function load_missing_data() 
     {  global  $arr,   // to add the missing data into
                $file_name, $my_wu_ID, $my_units, $my_api,  // to assemble the URL
                $untilDate, $logstring,  $max_days,
                $from_nr , $from_unix              ; 
# 
        $units_short    = substr($my_units,0,1);
#
        $until_unix     = strtotime($untilDate.'T000000');
        $until_nr       = date('z',$until_unix);
#                
        $missing_cnt    = 1 + $until_nr - $from_nr;
        
        $step_unix      = $max_days * 24*3600;
        $start_unix     = $from_unix;
        $periods        = array();
 wumessage (__LINE__.' $units_short='.$units_short.' $untilDate='.$untilDate.' - '.date('c',$until_unix).' $until_nr='.$until_nr.' $from_nr='.$from_nr.' $missing_cnt='.$missing_cnt.' $step_unix='.$step_unix.' $start_unix='.$start_unix.PHP_EOL); 
        while ($missing_cnt > 0)
             {  $missing_cnt    = $missing_cnt - $max_days;
                $from_day       = date('Ymd',$start_unix);
                $to_unix        = $start_unix + $step_unix;      
                if ($to_unix > $until_unix )
                     {  $to_unix = $until_unix; }                
                $to_day         = date('Ymd',$to_unix);
                $periods[]      = array ('from' => $from_day, 'until' => $to_day );    
                $start_unix     = $start_unix + $step_unix + 24*3600;
        } //eo while still missing
        wumessage (__LINE__.' '.print_r($periods,true).PHP_EOL); # die(__LINE__.' ');
        $new_wustring   = 'https://api.weather.com/v2/pws/history/daily?stationId=#wu_id#&format=json&units=#units#&startDate=#start#&endDate=#end#&numericPrecision=decimal&apiKey=#api#';
        $newdata        = array();
        foreach ($periods as $from_until)
             {  $from_date     = $from_until['from'];
                $until_date    = $from_until['until'];
                wumessage (__LINE__.' $from_date='.$from_date.' $until_date='.$until_date.PHP_EOL);
                $from   = array ('#wu_id#', '#units#', '#start#', '#end#', '#api#');
                $to     = array ($my_wu_ID, $units_short,$from_date,$until_date, $my_api);
                $url    = str_replace ($from, $to,$new_wustring);  
                wumessage (__LINE__.' '.$url.PHP_EOL);   
                $rawdata = file_get_contents_curl ($url);
                $json   = json_decode ($rawdata,true ); #  echo '<pre>'.print_r($json,true); 
                if ($json == false || !is_array($json) || !is_array($json['observations']))
                     {  wumessage (__LINE__.' no valid json for this period '.substr($rawdata,0,50).PHP_EOL); 
                        continue; }
                foreach( $json['observations'] as $one_day)
                     {  $string         = $one_day['obsTimeLocal'];  #wumessage (__LINE__.' Processing '.$string.PHP_EOL);); # exit;
                        $this_day       = substr($string,0,4).substr($string,5,2).substr($string,8,2); # echo $this_day; exit;
                        $jsonCSV        = json_assemble_csv ($one_day);
                        $newdata[$this_day]= $jsonCSV;
                        $save_data      = true;
                } // eo for each observation               
        } // eo foreach period to load from wu;
#echo print_r($newdata,true); exit;
        if (count($newdata) > 0)  // extra lines .CSV were assembled
             {  $arr= $arr + $newdata;
                ksort ($arr);
                $check  = file_put_contents ($file_name, serialize ($arr) );
                if ($check == false)
                     {  $logstring = __LINE__.'NEW DATA not saved'.PHP_EOL.$logstring;
                        file_put_contents ('./errorlog', $logstring,FILE_APPEND );
                } // eo log errors
        } // eo save new data
} // eof load_missing_data
#
function wumessage ($string) 
     {  global $testmode, $logstring;
        if (isset ($testmode) && $testmode){ echo $string; } else {$logstring .= $string;}
} // eof wumessage