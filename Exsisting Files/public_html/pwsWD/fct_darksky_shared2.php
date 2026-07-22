<?php $scrpt_vrsn_dt  = 'fct_darksky_shared2.php|01|2023-09-09|';  # PHP 8.2 Graphs + check file | release 2012_lts
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
// darksky api forecast and current conditions script 
//  gets data from jsondata/darksky.txt
#
if (   !isset ($dark_apikey)  
     || trim($dark_apikey) == ''   
     || $dark_apikey == 'ADD YOUR API KEY')
     {  echo '<small style="color: red;">Problem '.__LINE__.': Invalid settings, script ends</small>';
        exit;}
#
$fl_drksk       = $fl_folder.$drksk_fl; #### 2021-05-25
$time_loaded    = 0;
if   ( file_exists($fl_drksk) )    
     {  $time_loaded    = filemtime ($fl_drksk); }
$age    = time() - $time_loaded;
#
if (  array_key_exists ('force',$_REQUEST ) 
   && $_REQUEST['force'] == 'darksky') 
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') data will be reloaded as force was set '.PHP_EOL;  
        $age            = 9999; }   // skip cache reads requested by user
#
if (    $age > 7200 && $fct_default <> 'fct_darksky_block.php')    // file not exists or to old
     {  $fct_default    = 'fct_darksky_block.php';
        $scrpt          = 'PWS_load_files.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;  
        include_once $scrpt;
        if   (!file_exists($fl_drksk) )
             {  echo '<small style="color: red;">Problem '.__LINE__.': No valid forecast found, script ends</small>';
                return;}
        else {  $time_loaded    = filemtime ($fl_drksk); }  
        } // eo try to reload file
#
$json                   = file_get_contents($fl_drksk); 
$response               = json_decode($json, true);  #echo '<pre>json data'.print_r($response, true) ; exit;    
$darkskydayCond         = array();
$darkskyhourlyCond      = array();
if ($response === null) 
     {  echo '<small style="color: red;">Problem '.__LINE__.': No valid forecast found, script ends</small>';
        return;}
#
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') processing file '.$fl_drksk.PHP_EOL;   
# 
$darksky_used_unit      = $response['flags']['units']; #echo '$darksky_used_unit='.$darksky_used_unit; exit;
$darksky_used_temp      = 'C';              # currentconditionsDS  forecastDSblock  forecastDSdaily_popup forecastDShourly_popup
$darksky_used_wind      = 'm/s';
$toKnots                = 1.943844;
$darksky_used_rain      = 'mm';
$darksky_used_snow      = 'mm';
$darksky_used_baro      = 'hPa';
if ($darksky_used_unit == 'uk2') 
     {  $darksky_used_wind      = 'mph';
        $toKnots                = 0.868976;}
elseif ($darksky_used_unit == 'ca') 
     {  $darksky_used_wind      = 'km/h';
        $toKnots                = 0.5399568;}
elseif ($darksky_used_unit == 'us') 
     {  $darksky_used_temp      = 'F';
        $darksky_used_wind      = 'mph';
        $darksky_used_rain      = 'in';
        $darksky_used_snow      = 'in';
        $darksky_used_baro      = 'inHg';
        $toKnots                = 0.868976;}   
#darksky api Current SKY Conditions  
$darkskycurTime         = $response['currently']['time'];               // not used
$darkskycurSummary      = $response['currently']['summary'];            # currentconditionsDS
$darkskycurIcon         = $response['currently']['icon'];               # currentconditionsDS
$num                    = $response['currently']['temperature'];
$darkskycurTemp         = convert_temp ($num,$darksky_used_temp,$tempunit);  // not used
$darkskycurCover        = $response['currently']['cloudCover']*100;     // not used
# darksky UV
$darkskyhourlyuv        = $response['hourly']['data'][0]['uvIndex'];    # uvsolarlux_block   uvsolarlux_popup
# darksky api Hourly Forecast
$darkskyhourlySummary   = $response['hourly']['summary'];               // not
$darkskyhourlyIcon      = $response['hourly']['icon'];                  // not
$darkskyhourlyCond      = array();                                      # currentconditionsDS   forecastDShourly_popup  
foreach ($response['hourly']['data'] as $td) 
     {  $darkskyhourlyCond[] = $td; }
# darksky api Daily Forecast
if (isset ($response['hourly']['summary']) )
     {  $darkskyTodaySummary = $response['hourly']['summary'];}
else {  $darkskyTodaySummary = 'n/a';}
$darkskydaySummary      = $response['daily']['summary'];                // not
$darkskydayIcon         = $response['daily']['icon'];                   // advisory-small
$darkskydayCond         = array();                                      // forecastDSblock  forecastDSdaily_popup
foreach ($response['daily']['data'] as $d) 
     {  $darkskydayCond[] = $d; }
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
$uv_texts        = array (
                0  => 'unknown',    1 => 'Low',         2 => 'Low',  
                3  => 'Medium',     4 => 'Medium',      5 => 'Medium',
                6  => 'High',       7 => 'High',        
                8  => 'Very high',  9  =>'Very high',  10 => 'Very high', 
                11 => 'Extreme'  );
for ($n = 1; $n <= 11; $n++) {$uv_texts[$n] = lang($uv_texts[$n]);}
#
$norain         = '-';
$nouv           = '-';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";
#
$lvl_bft        = array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
$bft_txt        = array( /* Beaufort 0 to 12 in English */
	'Calm', 
	'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale force', 'Stronggale', 'Storm',
	'Violent storm', 'Hurricane');
#
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
#
if (strtolower (trim($pressureunit))  == 'hpa') {$dec_pres= 1; } else {$dec_pres = 2;}
if (strtolower (trim($rainunit))      ==  'mm') {$dec_rain= 1; } else {$dec_rain = 2;}
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
#-----------------------------------------------
#  trans_long_date
#-----------------------------------------------
function trans_long_date ($date)
     {  $from   = array ( 
                'Apr ','Aug ','Dec ','Feb ','Jan ','Jul ','Jun ','Mar ','May ','Nov ','Oct ','Sep ',
                'April','August','December','February','January','July','June','March','May','November','October','September',
                'Mon ','Tue ','Wed ','Thu ','Fri ','Sat ','Sun ',
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        foreach ($from  as $txt) {$to_dates[] = lang($txt).' ';} # echo '-'.$txt.'-'.lang($txt).PHP_EOL;
        return str_replace ($from, $to_dates, $date.' ');  #### 2018-07-18
        }       
#-----------------------------------------------
#  DSfound
#-----------------------------------------------
function DSfound($haystack, $needle){
$pos = strpos($haystack, $needle);
   if ($pos === false) 
        { return false;} 
   else { return true;}
}  // eof wsFound
#-----------------------------------------------
# Icon translation
#-----------------------------------------------
function DSicon_trns ($icon)
     {  global $stck_lst;
        $DSicon_trns    = array();
        $DSicon_trns['chancerain']      = 'mc_rain';
        $DSicon_trns['clear-day']       = 'clear_day';
        $DSicon_trns['clear-night']     = 'clear_night';
        $DSicon_trns['cloudy']          = 'ovc';
        $DSicon_trns['fog']             = 'haze_day';
        $DSicon_trns['hail']            = 'ovc_sleet';
        $DSicon_trns['nt_chancerain']   = 'mc_rain_dark';
        $DSicon_trns['nt_clear-day']    = 'few_day';
        $DSicon_trns['nt_clear-night']  = 'few_night';
        $DSicon_trns['nt_cloudy']       = 'ovc_dark';
        $DSicon_trns['nt_fog']          = 'ovc_fog';
        $DSicon_trns['nt_hail']         = 'ovc_sleet_dark';
        $DSicon_trns['nt_partly-cloudy-day'] = 'mc';
        $DSicon_trns['nt_partly-cloudy-night'] = 'mc_night';
        $DSicon_trns['nt_rain']         = 'ovc_rain_dark';
        $DSicon_trns['nt_sleet']        = 'ovc_sleet_dark';
        $DSicon_trns['nt_snow']         = 'ovc_flurries_dark';
        $DSicon_trns['nt_thunderstorm'] = 'ovc_thun_dark';
        $DSicon_trns['nt_tornado']      = 'tornado';
        $DSicon_trns['nt_wind']         = 'mc_windy_dark';
        $DSicon_trns['offline']         = 'offline';
        $DSicon_trns['offlineforecast'] = 'offline';
        $DSicon_trns['partly-cloudy-day']= 'pc_day';
        $DSicon_trns['partly-cloudy-night'] = 'few_night';
        $DSicon_trns['rain']            = 'ovc_rain';
        $DSicon_trns['sleet']           = 'ovc_sleet';
        $DSicon_trns['snow']            = 'ovc_flurries';
        $DSicon_trns['thunderstorm']    = 'ovc_thun_dark';
        $DSicon_trns['tornado']         = 'tornado';
        $DSicon_trns['wind']            = 'mc_windy';
        if (!isset ($DSicon_trns[$icon]))
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') unknown icon '.$icon.PHP_EOL;
                $return = 'pws_icons/offline.svg';}
        else {  $return = 'pws_icons/'.$DSicon_trns[$icon].'.svg';}
        return $return;}
#-----------------------------------------------
