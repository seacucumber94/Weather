<?php  $scrpt_vrsn_dt  = 'PWS_shared.php|01|2022-11-22|';  # PHP 8 + missing langs | release 2012_lts
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
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
# set_my_time => format time am / pm
#-----------------------------------------------
function set_my_time($time, $int=false) 
     {  global $timeFormatShort;
        $fr_ampm= array ('_am','_pm',);
        $to_ampm= array ('am','pm',); 
        $time   = str_replace($fr_ampm, $to_ampm, strtolower($time) ); 
        if ($int) 
             {  $return = date($timeFormatShort,(int) $time); }
        else {  $return = date($timeFormatShort, strtotime ($time)); }
        $fr_ampm= array ('am','pm');
        $to_ampm= array ('<sup>am</sup>','<sup>pm</sup>'); 
        return str_replace($fr_ampm, $to_ampm, strtolower($return) );  }
#-----------------------------------------------
# set_my_time => format time am / pm        
#-----------------------------------------------
function set_my_time_lng($time, $int=false) 
     {  global $timeFormat;
        $fr_ampm= array ('am','pm');
        $to_ampm= array ('<sup>am</sup>','<sup>pm</sup>'); 
        if ($int) 
             {  $return = date($timeFormat,$time); }
        else {  $return = date($timeFormat, strtotime ($time)); }
        return str_replace($fr_ampm, $to_ampm, strtolower($return) );  }
#-----------------------------------------------
# check_value 
#     => check if tag exists && contains a value        
#-----------------------------------------------
function check_value ($key, $arr = '')     # check if tag contains correct  value
     {  global $weather;
        if ($arr == '') 
            {   if (!array_key_exists ($key,$weather) ) { return false;} 
                $value  = trim($weather[$key]);}
        elseif (!is_array ($arr) )                      { return false;} 
        else {  if (!array_key_exists ($key,$arr) )     { return false;} 
                $value  = trim($arr[$key]);}
#
        if     ( (string) $value === 'n/a' )    { return false;}
        elseif ( (string) $value === '---' )    { return false;} 
        elseif (          $value === '' )       { return false;}        
        elseif (          $value === false )    { return false;}
        elseif (          $value === NULL )     { return false;}
        return true;}
#----------------------------------------------- 
# anyToC => return celcius value from a temp
#----------------------------------------------- 
function anyToC($field)
     {  global $weather;
	if ($weather["temp_units"] == 'C')
	     { return (float) $field;}  # 2021-12-08
	else { return convert_temp ($field,'f','c',1); }}
#----------------------------------------------- 
# convert_temp  => from C=>F  and F=>C
#----------------------------------------------- 
function convert_temp ($num,$from,$to,$dec=1) 
     {  global $stck_lst;
        $amount	        = (float) str_replace(',','.',$num);
        $from   = strtolower($from);
        $to     = strtolower($to);
	if ($from == $to)                       { $out = $amount;}
	elseif (($from == 'c') && ($to = 'f'))  { $out = 32 +(9*$amount/5);}
	elseif (($from == 'f') && ($to = 'c'))  { $out = 5*($amount -32)/9;}
        else                                    { $out = -999;}
        $return         = round($out,$dec);
# $stck_lst      .= basename(__FILE__).' ('.__LINE__.') $num='.$num.'  $from='.$from.'  $to='.$to.' $return='.$return.PHP_EOL;
        return $return;} 
#----------------------------------------------- 
#                       feel / heat calculations
#----------------------------------------------- 
# heatIndexLow  =>  Calculates "real feel" heat index 
#  valid only at lower temperatures (up to 79 F)
#----------------------------------------------- 
function heatIndexLow($temp, $hum)  # !!! Assumes Fahrenheit
     {  $t      = (float) $temp;        # 2021-12-08
        $rh     = (float) $hum;         # 2021-12-08
        return 0.5 * ($t + 61.0 + (($t - 68.0) * 1.2) + ($rh * 0.094));}
#----------------------------------------------- 
# heatIndexHigh =>  Calculates "real feel" heat index 
#   valid only at higher temperatures (beginning around 79-80 F)
#   the traditional heat index formula
function heatIndexHigh($temp, $hum) # !!! Assumes Fahrenheit
     {  $t      = (float) $temp;        # 2021-12-08
        $rh     = (float) $hum;         # 2021-12-08
        $heatIndex = -42.379 + 2.04901523 * $t + 10.1433127*$rh - .22475441*$t*$rh 
                - .00683783 *$t * $t - .05481717 * $rh * $rh + .00122874*$t*$t*$rh 
                + .00085282 *$t * $rh *$rh - .00000199 *$t *$t *$rh * $rh;
	# Adjustment formula, adding or subtracting as much as a couple degrees at extreme ends of temperature/humidity ranges
	$a = 0;
	if ($rh < 13 && ($t >= 80 && $t <= 112)) 
	     {  $a=((13 - $rh ) / 4) * sqrt((17-abs($t - 95))/17);
		$a = -$a;};
	if ($rh > 85 && ($t >= 80 && $t <= 87)) 
	     {  $a=(($rh - 85)/10) * ((87 - $t) / 5);};
	$heatIndex += $a;
	return $heatIndex;}
#----------------------------------------------- 
# heatIndex =>  Ruthfusz heat index formula 
# => http://www.wpc.ncep.noaa.gov/html/heatindex_equation.shtml
#----------------------------------------------- 
function heatIndex($temp, $rh) 
     {  global $weather;
	$unit   = $weather["temp_units"];
	if ($unit == 'C') 
	     {  $t = convert_temp ($temp,'c','f',1); } 
	else {  $t = $temp;} 
# First try simple formula, valid when calculated heat index <= 79 degrees F
	$heatIndex = heatIndexLow($t, $rh);
# If too warm, do the complicated formula instead
	if ($heatIndex >= 79)
	     {  $heatIndex = heatIndexHigh($t, $rh);}
        if ($unit == 'C') 
             {  $heatIndex = convert_temp ($heatIndex,'f','c',1); } 
	return round($heatIndex, 1);}
#----------------------------------------------- 
#   convert_baro                        Pressure
#-----------------------------------------------  
function convert_baro ($num,$from,$to,$dec='') 
     {  $amount	        = (float) str_replace(',','.',$num);
        $repl		= array ('/',' ','hg','mb');
	$with		= array ('' ,'' ,'','hpa');
	$convertArr	= array
	    (   "hpa"	=> array('hpa' => 1    ,   'mm' => 0.75006 	, 'in' => 0.02953),
		"mm"	=> array('hpa' => 1.3332 , 'mm' => 1 	        , 'in' => 0.03937 ),
		"in"	=> array('hpa' => 33.864 , 'mm' => 25.4 	, 'in' => 1) );
	$fromUnit 	= trim(str_replace ($repl,$with,strtolower($from)));
	$toUnit   	= trim(str_replace ($repl,$with,strtolower($to)));
        if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
             {  $out 	= 1;} 
	else {  $out    = $convertArr[$fromUnit][$toUnit];}
        if ($dec == '')
              { if ($toUnit == 'hpa') 
                     {  $dec = 1;} 
                else {  $dec = 2;} 
        } // eo empty decimals
        $return	= round($out*$amount,$dec); #echo '$num='.$num.' $from='.$from.' $to='.$to.' $dec='.$dec.' $return='.$return.'<br />'.PHP_EOL;
        return $return; } // eof convert_baro
#----------------------------------------------- 
# convert_precip                   Precipitation
#----------------------------------------------- 
function convert_precip ($num,$from,$to,$dec='') 
     {  $amount	        = (float) str_replace(',','.',$num);
	$repl 		= array ('l/m','/',' ','ch');
	$with 		= array ('mm' ,'' ,'' ,'');
	$convertArr	= array
	     (  "mm"=> array('mm' => 1		,'in' => 0.03937007874015748 	, 'cm' => 0.1 ),
		"in"=> array('mm' => 25.4	,'in' => 1			, 'cm' => 2.54),
		"cm"=> array('mm' => 10		,'in' => 0.3937007874015748 	, 'cm' => 1 )   );
	$fromUnit 	= trim(str_replace ($repl,$with,strtolower($from)));
	$toUnit   	= trim(str_replace ($repl,$with,strtolower($to)));
        if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
             {  $out 	= 1;} 
	else {  $out    = $convertArr[$fromUnit][$toUnit];}
        if ($dec == '')
             {  if ($toUnit == 'in' || $toUnit == 'cm') 
                     {  $dec = 2;}
                else {  $dec = 1;}
         }  // eo empty decimals
        $return	= round($out*$amount,$dec);
        return $return;} // eof convert_precip 
#----------------------------------------------- 
# convert_speed               Wind / Gust  Speed
#----------------------------------------------- 
function convert_speed ($num,$from,$to,$dec='') 
     {  $amount	        = (float) str_replace(',','.',$num);
        $repl = array ('/',' ','p');
	$with = array ('','','');
	$convertArr= array
           (    "kmh"=> array('kmh' => 1	, 'kts' => 0.5399568034557235	, 'ms' => 0.2777777777777778 	, 'mh' => 0.621371192237334 ),
                "kts"=> array('kmh' => 1.852	, 'kts' => 1 			, 'ms' => 0.5144444444444445 	, 'mh' => 1.1507794480235425),
                "ms" => array('kmh' => 3.6	, 'kts' => 1.9438444924406046	, 'ms' => 1 			, 'mh' => 2.236936292054402 ),
                "mh" => array('kmh' => 1.609344	, 'kts' => 0.8689762419006479	, 'ms' => 0.44704 		, 'mh' => 1 ));
	$fromUnit 	= trim(str_replace ($repl,$with,strtolower($from)));
	$toUnit   	= trim(str_replace ($repl,$with,strtolower($to)));
        if (!isset ($convertArr[$fromUnit][$toUnit]) ) 
             {  $out 	= 1;} 
	else {  $out    = $convertArr[$fromUnit][$toUnit];}
	if ($dec === '') { $dec = 1;}
        $return	        = round($out*$amount,$dec);
        return $return;
} // eof convert_speed
#----------------------------------------------- 
#   distance                            distance
#----------------------------------------------- 
function distance($lat, $lon, $lati, $longi) 
     {  $lat1 = deg2rad($lati);
	$lat2 = deg2rad($lat);
	$long1 = deg2rad($longi);
	$long2 = deg2rad($lon);
	// Great circle calculation uses the radius of earth, 6371 km
	return 6371 * acos(sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($long2-$long1));}
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
#----------------------------------------------- 
# lang                        language translate
#----------------------------------------------- 
function lang($text) 
     {  global $lang, $lang_file , $used_lang; #### 2021-02-11
        global $missing;
        if (trim($text) == '') { return $text;}
        if (isset ($lang[$text]) )    {  return $lang[$text];} 
        $txtsmll= strtolower (str_replace (' ','',$text) );
        if (isset ($lang[$txtsmll]) ) {  return $lang[$txtsmll];} 
# skip for UK / US lang if not test mode $lang_file
        if ( $lang_file == 'languages/lang_en.txt') {return $text;} # 2021-12-08
# save missing translation                      #### 2021-02-28
        $arr    = debug_backtrace(); 
        $file   = $arr[0]['file'];
        $arr    = explode ('/',$file);
        $n      = count($arr) - 1;
        if ($n < 0) {$n = 0;}
        $script = '#'.$arr[$n];  
        if (substr($used_lang,0,2) == 'en') {$script ='';}        
        $missing[$text]= ' |'.$text.'|'.$text.'|       '.$script.PHP_EOL;  #### 2021-02-11
        return $text;} 
#----------------------------------------------- 
#
#----------------------------------------------- 
#                  SVGs used in multiple scripts
#----------------------------------------------- 
$lightningsvg="<svg  viewBox='0 0 32 32' width='15' height='15' fill='none' stroke='currentcolor' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'>
    <path d='M18 13 L26 2 8 13 14 19 6 30 24 19 Z' /></svg>";
$online = '<svg viewBox="0 0 32 32" width="7" height="7" fill="currentcolor"><circle cx="16" cy="16" r="14"></circle></svg>';
$rainsvg= '<svg viewBox="0 0 400 500" width="8px" fill="#01a4b5" stroke="#01a4b5" stroke-width="3%" xmlns="http://www.w3.org/2000/svg">
  <g transform="matrix(0.920767, 0, 0, 0.856022, -36.138042, 33.74263)" style="">
    <g>
      <path d="M348.242,124.971C306.633,58.176,264.434,4.423,264.013,3.889C262.08,1.433,259.125,0,256,0 c-3.126,0-6.079,1.433-8.013,3.889c-0.422,0.535-42.621,54.287-84.229,121.083c-56.485,90.679-85.127,161.219-85.127,209.66 C78.632,432.433,158.199,512,256,512c97.802,0,177.368-79.567,177.368-177.369C433.368,286.19,404.728,215.65,348.242,124.971z M256,491.602c-86.554,0-156.97-70.416-156.97-156.97c0-93.472,123.907-263.861,156.971-307.658 C289.065,70.762,412.97,241.122,412.97,334.632C412.97,421.185,342.554,491.602,256,491.602z"/>
    </g>
  </g>
  <g transform="matrix(1, 0, 0, 1, -198.610708, -193.744378)">
    <g>
      <path d="M275.451,86.98c-1.961-2.815-3.884-5.555-5.758-8.21c-3.249-4.601-9.612-5.698-14.215-2.45 c-4.601,3.249-5.698,9.613-2.45,14.215c1.852,2.623,3.75,5.328,5.688,8.108c1.982,2.846,5.154,4.369,8.377,4.369 c2.012,0,4.046-0.595,5.822-1.833C277.536,97.959,278.672,91.602,275.451,86.98z"/>
    </g>
  </g>
</svg>';	
$snowflakesvg= '<svg x="0px" y="0px" viewBox="0 0 34.875 34.876"  width="8px" fill="#01a4b5" stroke="#01a4b5" stroke-width="3%"><g><path d="M32.916,24.087c-0.181-0.635-0.598-1.161-1.173-1.481c-1.062-0.592-2.462-0.25-3.179,0.697l-5.193-2.998l4.025-1.078
c0.268-0.072,0.425-0.348,0.354-0.613s-0.346-0.426-0.611-0.354l-4.992,1.336l-3.707-2.14l3.71-2.142l4.989,1.336
c0.043,0.012,0.087,0.017,0.13,0.017c0.221,0,0.423-0.147,0.481-0.371c0.07-0.267-0.087-0.541-0.354-0.612l-4.022-1.078
l5.197-3.001c0.463,0.624,1.183,1.015,1.972,1.015l0,0c0.417,0,0.833-0.108,1.2-0.312c1.119-0.625,1.553-1.996,1.046-3.148
c-0.031-0.071-0.054-0.143-0.092-0.212c-0.128-0.229-0.301-0.423-0.492-0.594c-0.766-0.68-1.942-0.874-2.867-0.36
c-0.979,0.546-1.43,1.663-1.193,2.704l-5.271,3.044l1.079-4.026c0.07-0.267-0.088-0.541-0.354-0.612
c-0.267-0.07-0.54,0.087-0.612,0.354l-1.338,4.992l-3.709,2.14v-4.282l3.652-3.652c0.195-0.195,0.195-0.512,0-0.707
c-0.194-0.195-0.512-0.195-0.707,0l-2.945,2.946V4.876c1.124-0.231,1.972-1.228,1.972-2.419c0-1.192-0.851-2.19-1.977-2.42
c0,0.008,0.005,0.015,0.005,0.023v0.012C17.778,0.03,17.612,0,17.439,0c-0.174,0-0.34,0.03-0.501,0.072V0.059
c0-0.008,0.004-0.015,0.005-0.023c-1.125,0.23-1.974,1.228-1.974,2.419c0,1.19,0.846,2.186,1.969,2.418v6.02l-2.946-2.945
c-0.195-0.195-0.512-0.195-0.707,0c-0.195,0.195-0.195,0.512,0,0.707l3.653,3.652v4.282l-3.708-2.141l-1.338-4.992
c-0.072-0.267-0.345-0.424-0.612-0.354c-0.267,0.071-0.425,0.346-0.354,0.612l1.079,4.026l-5.271-3.044
C6.97,9.654,6.519,8.538,5.54,7.991c-0.92-0.512-2.1-0.319-2.865,0.361C2.483,8.523,2.31,8.717,2.181,8.947
C2.143,9.015,2.12,9.088,2.089,9.158c-0.506,1.151-0.073,2.522,1.047,3.148c0.367,0.204,0.782,0.312,1.2,0.312
c0.789,0,1.51-0.392,1.972-1.015l5.197,3.001l-4.022,1.078c-0.268,0.071-0.425,0.346-0.354,0.612
c0.061,0.224,0.263,0.371,0.482,0.371c0.043,0,0.086-0.005,0.13-0.017l4.989-1.336l3.708,2.142l-3.707,2.14l-4.992-1.336
c-0.265-0.072-0.541,0.088-0.612,0.354c-0.07,0.269,0.087,0.541,0.354,0.613l4.025,1.078l-5.193,2.998
c-0.717-0.947-2.119-1.287-3.179-0.697c-0.575,0.32-0.992,0.849-1.173,1.481c-0.158,0.56-0.105,1.14,0.126,1.666
c0.031,0.069,0.055,0.144,0.093,0.211c0.128,0.229,0.298,0.425,0.485,0.599c0.451,0.418,1.041,0.67,1.673,0.67
c0.418,0,0.833-0.107,1.2-0.312c0.576-0.319,0.993-0.849,1.173-1.481c0.115-0.406,0.113-0.824,0.021-1.224l5.271-3.043
l-1.077,4.021c-0.07,0.269,0.088,0.541,0.354,0.613c0.043,0.012,0.087,0.018,0.13,0.018c0.221,0,0.423-0.147,0.482-0.371
l1.335-4.988l3.709-2.143v4.281l-3.653,3.652c-0.195,0.195-0.195,0.512,0,0.707c0.195,0.195,0.512,0.195,0.707,0l2.946-2.945v6.021
c-1.124,0.23-1.972,1.229-1.972,2.419s0.851,2.188,1.977,2.42c0-0.008,0.995-0.022,0.995-0.022c0,0.008,1.969-0.662,1.969-2.396
c0-1.189-0.846-2.188-1.969-2.418v-6.021l2.945,2.945c0.099,0.1,0.227,0.146,0.354,0.146s0.257-0.049,0.354-0.146
c0.195-0.193,0.195-0.512,0-0.707l-3.651-3.652v-4.281l3.709,2.142l1.335,4.988c0.061,0.223,0.263,0.37,0.481,0.37
c0.043,0,0.086-0.004,0.131-0.016c0.267-0.072,0.425-0.348,0.354-0.613l-1.076-4.021l5.271,3.043
c-0.093,0.4-0.095,0.816,0.021,1.223c0.18,0.635,0.598,1.162,1.173,1.482c0.367,0.204,0.782,0.312,1.2,0.312
c0.632,0,1.223-0.252,1.673-0.67c0.188-0.174,0.356-0.369,0.484-0.6c0.038-0.066,0.062-0.141,0.093-0.211
C33.021,25.229,33.073,24.646,32.916,24.087z M29.823,8.87c0.22-0.122,0.466-0.186,0.714-0.186c0.269,0,0.523,0.073,0.747,0.203
c0.222,0.131,0.409,0.319,0.538,0.551c0.042,0.076,0.074,0.155,0.102,0.234c0.229,0.661-0.038,1.413-0.669,1.765
c-0.219,0.122-0.465,0.186-0.713,0.186l0,0c-0.433,0-0.83-0.196-1.105-0.514c-0.064-0.075-0.13-0.151-0.181-0.24
c-0.125-0.224-0.178-0.467-0.179-0.708C29.076,9.644,29.339,9.139,29.823,8.87z M5.621,10.869
c-0.049,0.088-0.107,0.169-0.175,0.243c-0.443,0.497-1.225,0.659-1.824,0.326c-0.632-0.353-0.898-1.104-0.67-1.766
c0.027-0.079,0.06-0.158,0.102-0.234c0.13-0.232,0.316-0.42,0.539-0.551c0.224-0.13,0.479-0.203,0.747-0.203
c0.248,0,0.495,0.064,0.714,0.186C5.536,9.14,5.802,9.644,5.8,10.162C5.798,10.4,5.746,10.645,5.621,10.869z M5.75,25.165
c-0.106,0.378-0.354,0.69-0.697,0.882c-0.22,0.122-0.467,0.188-0.715,0.188c-0.267,0-0.523-0.072-0.747-0.203
c-0.222-0.131-0.408-0.319-0.538-0.553c-0.043-0.074-0.074-0.152-0.103-0.232c-0.099-0.283-0.111-0.588-0.028-0.883
c0.107-0.379,0.355-0.69,0.698-0.883c0.219-0.123,0.465-0.188,0.714-0.188c0.432,0,0.829,0.195,1.105,0.514
c0.064,0.075,0.13,0.151,0.18,0.24c0.123,0.221,0.178,0.463,0.18,0.707C5.8,24.893,5.789,25.029,5.75,25.165z M18.908,32.458
c0,0.634-0.406,1.17-0.97,1.376h-1c-0.564-0.205-0.972-0.742-0.972-1.376s0.406-1.171,0.972-1.376
c0.156-0.057,0.323-0.094,0.499-0.094c0.177,0,0.344,0.037,0.501,0.095C18.5,31.288,18.908,31.824,18.908,32.458z M17.439,3.927
c-0.177,0-0.344-0.036-0.501-0.094c-0.563-0.206-0.969-0.742-0.969-1.376c0-0.634,0.404-1.17,0.969-1.376
c0.157-0.058,0.324-0.094,0.501-0.094c0.176,0,0.343,0.036,0.499,0.093c0.563,0.205,0.972,0.742,0.972,1.376
c0,0.634-0.407,1.171-0.972,1.376C17.782,3.892,17.615,3.927,17.439,3.927z M31.923,25.244c-0.027,0.08-0.061,0.158-0.103,0.234
c-0.13,0.231-0.326,0.414-0.554,0.541c-0.438,0.244-0.992,0.278-1.445,0.025c-0.342-0.189-0.59-0.504-0.697-0.882
c-0.038-0.136-0.05-0.272-0.049-0.41c0.001-0.245,0.057-0.487,0.18-0.708c0.05-0.089,0.114-0.165,0.18-0.238
c0.275-0.318,0.674-0.516,1.104-0.516c0.249,0,0.495,0.062,0.714,0.188c0.344,0.19,0.59,0.504,0.698,0.881
C32.037,24.656,32.023,24.96,31.923,25.244z"/></g></svg>';		
