<?php  $scrpt_vrsn_dt  = 'WLCOM_summary.php|01|2021-05-15|';  # et_year  extra's + release 2012_lts
#
#  only usable for wl.com v1 API
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
# Extra sensors:  solar, uv, soil sensors, leaf 
#---------------------------------test_settings
#
#$weather['et_year']= ''; #echo __LINE__.'<pre>'.print_r($weather,true); exit;
#
if (array_key_exists ('uv',$weather)       && $weather['uv'] <> '')       {$wl_is_uv    = true;} else {$wl_is_uv    = false;}
if (array_key_exists ('solar',$weather)    && $weather['solar'] <> '')    {$wl_is_solar = true;} else {$wl_is_solar = false;} #$wl_is_solar = false;
if (array_key_exists ('et_year',$weather)  && $weather['et_year'] <> '')  {$wl_is_ET    = true;} else {$wl_is_ET    = false;} 
#$debug='';
for ($n1 = 1; $n < 9; $n++)
     {  $key    = 'wl_is_t_extra'.$n;
        if (array_key_exists ('temp_extra_'.$n,$weather) ) {$$key = true;} else {$$key = false;}
        $key    = 'wl_is_h_extra'.$n;
        if (array_key_exists ('relative_humidity_'.$n,$weather) ) {$$key = true;} else {$$key = false;}
        }
for ($n1 = 1; $n < 5; $n++)
     {  $key    = 'wl_is_t_soil'.$n;
        if (array_key_exists ('temp_soil_'.$n,$weather) ) {$$key = true;} else {$$key = false;}
        $key    = 'wl_is_m_soil'.$n;
        if (array_key_exists ('soil_moisture_'.$n,$weather) ) {$$key = true;} else {$$key = false;}
        $key    = '$wl_is_t_leaf'.$n;
        if (array_key_exists ('temp_leaf_'.$n,$weather) ) {$$key = true;} else {$$key = false;}
        $key    = '$wl_is_w_leaf'.$n;
        if (array_key_exists ('leaf_wetness_'.$n,$weather) ) {$$key = true;} else {$$key = false;} 
        }
#
# ----- the date format in the heading line 
# metric style   Wednesday, 3 October 2018 13:17 (CEST)       
$wl_date_long   = 'l, j F Y H:i (T)';    # date format metric style    
# imperial style  1:17 pm Wednesday, Oct 3, 2018 (CEST)
#$wl_date_long   = 'g:i a l, M j, Y (T)'; # date format US style      
#
# the default language to use, choose from  'en'  'nl' 'fr' 'de' 
$wl_lang        = 'en';
#
# --------------------------------- housekeeping
$wl_langs       = array ('nl','fr','de','en');
#
if (isset($_REQUEST['lang']))
     {  $lang   = strtolower(substr(trim($_REQUEST['lang']).'en',0,2));
        if (in_array($lang,$wl_langs) )
             {  $wl_lang= $lang;}     
        }
else {  $lang   = substr ($used_lang,0,2);
        if (in_array($lang,$wl_langs) )
             {  $wl_lang= $lang;}     
        }
#
if (!isset ($wl_only_extra) )   # show only extra sensors 
     {  $wl_only_extra  = false; }
#
$string         = file_get_contents($livedata);
$davis          = json_decode($string, true);
$xml            = (array) $davis['davis_current_observation']; # $xml['et_day']=''; # print_r($xml); exit;
#
#------------------ load translations to be used
wl_trans_loads($wl_lang); #print_r($WL_TRANS); exit;
#
$from   = array ('April','August','December','February','January','July','June','March','May','November','October','September',
                 'Friday','Monday','Saturday','Sunday','Thursday','Tuesday','Wednesday');
$to     = array ();
foreach ($from as $value) {$to[]  = wl_trans($value);}
#
#--------- get unix date-time from download data
$wl_obs_time	= strtotime ( (string) $davis['observation_time_rfc822']);   //'<observation_time_rfc822>Fri, 24 Oct 2014 09:01:16 +0200</observation_time_rfc822>';
$wl_obs_string	= wl_trans('Current conditions at').' ';
$date   = date($wl_date_long, $wl_obs_time);
#
#----- create date time string in local language
$wl_obs_string	.= str_replace ($from, $to, $date);
#
#-------------------  set all extras to not used
$wl_is_t_extra  = $wl_is_h_extra = $wl_is_t_soil = $wl_is_m_soil = $wl_is_t_leaf = $wl_is_w_leaf = false;
#
#--------- load array with all field definitions
$wl             = array();
wl_load_array(); # echo '$wl='.print_r($wl,true); exit;
#
#--  $val will contain all values for the fields
$val            = array();
#
# ---  UOM's 
$fr_tmp	        = 'F';
$fr_baro        = 'inHg';
$fr_wind        = 'mph';
$fr_rain        = 'in';
#
# -------  windlabels to convert degrees to text
$wl_wind_lbl    = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
		 "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
#---------------------------------------------------------------
# First main loop:
# All needed fields will be loaded from the xml/json data
# If necessary a field will be converted to the requested UOM 
# The UOM will be added also
# Alle ready to be printed fields are stored in the $val array
#---------------------------------------------------------------
#
$count  = count ($wl); #$debug .=  __LINE__.'$wl array='.print_r($wl,true);
for ($n = 0; $n < $count; $n++)
     {  $field  = $wl[$n];        
        list ($name, $type, $location, $key) = explode ('|',$field);
        $location       = trim($location);
        $key            = trim($key);
# added to be downwards compatible with PHP 5.6.32
        if ($location == 'xml') // most fields are stored in this array
             {  if (!isset ($xml[$key]) )
                     {  unset ($wl[$n]);
                        continue;}       // some fields are optional
                else {  $value  = $xml[$key];} }
        else {  if (!isset ($davis[$key]) )
                     {  unset ($wl[$n]);
                        continue;}
                else {  $value  = $davis[$key];} }      
# added to be downwards compatible with PHP 5.6.32
        if ($value <> 'n/a')                    // if there is a value for a field
             {  switch (trim($type))            // we convert the field based on its type
                  { case 'none': break;
                    case 'rain':
                        $num    = (float) $value;  
                        $value  = convert_precip ($num,$fr_rain,$rainunit,$dec_rain).' '.lang($rainunit);                          
                        break;
                    case 'wind':
                        $num    = (float) $value;
                        $value  = convert_speed ($num,$fr_wind,$windunit,$dec_wnd) .' '.lang($windunit)  ;
                        break;
                    case 'temp':
                        $num    = (float) $value;
                        $value  = convert_temp ($num,$fr_tmp,$tempunit,$dec_tmp).'&deg;'.$tempunit;
                        break;
                    case 'time':
                        $int    = strtotime($value);
                        $value  = date ($timeFormatShort,$int);
                        break;
                     case 'humi':
                        $value  = (int)$value.'%';
                        break;
                     case 'baro':
                        $num  = (float) $value;
                        $value  = convert_baro ($num,$fr_baro,$pressureunit,$dec_baro).' '.lang($pressureunit); 
                        break;
                     case 'degr':
                        $val['wl_compass']      = wl_trans($wl_wind_lbl[ fmod((($value + 11) / 22.5),16) ]);
                        $value  = $value.'&deg;'; 
                        break;
                     case 'solar':      # w/m<sup>2</sup>
                        $value  = (int) $value.' w/m<span style="font-size: 75%;"><sup>2</sup></span>';
                        break;
                     case 'uv':
                        $value  = wl_number ($value,1).'<small> index</small>';
                        break;
                     case 'trnd':
                        $value  = wl_trans($value);                
                        break;
                     case 'moist':
                        $value  = (int) $value.' cb';                
                        break;

                     default:      
                        break;} // eo switch
                } // eo value exist
        $name   = trim($name);
        $val[$name]     = $value;           
        }  #$debug .=  __LINE__.'$val array='.print_r($val,true);// eo foreach
#---------------------------------------------------------------
# Now make an array with the specifications 
#       of all rows in the table we have to generate  
# The entry foir a row  contains
#     TYPE   |  
#     TEXT about what the row contains  |
#     STRING with current value |
#     STRING with todays High value |  STRING with the time this high value occured |
#     STRING with todays Low value |  STRING with the time this low value occured |
# Not every line has all fields, TYPE is always needed
#---------------------------------------------------------------
$wl_lines       = array(); #$wl_popup=true;
if (!isset ($wl_popup) ) {
$wl_lines[]     = 'head|Station Summary|Current|Today\'s Highs|Today\'s Lows|';
#$wl_lines[]     = 'hr|';
$wl_lines[]     = 'detail|Outside Temp|wl_temp_out_act|wl_temp_out_max|wl_temp_out_max_T|wl_temp_out_min|wl_temp_out_min_T|';
$wl_lines[]     = 'detail|Outside Humidity|wl_humi_out_act|wl_humi_out_max|wl_humi_out_max_T|wl_humi_out_min|wl_humi_out_min_T|';
$wl_lines[]     = 'hr|';
if (isset ($show_indoor) && $show_indoor == true)
     {  $wl_lines[]     = 'detail|Inside Temp|wl_temp_in_act|wl_temp_in_max|wl_temp_in_max_T|wl_temp_in_min|wl_temp_in_min_T|';
        $wl_lines[]     = 'detail|Inside Humidity|wl_humi_in_act|wl_humi_in_max|wl_humi_in_max_T|wl_humi_in_min|wl_humi_in_min_T|';
        $wl_lines[]     = 'hr|';}
$wl_lines[]     = 'detail|Heat Index|wl_heat_out_act|wl_heat_out_max|wl_heat_out_max_T| | |';
$wl_lines[]     = 'detail|Wind Chill|wl_chil_out_act| | |wl_chil_out_min|wl_chil_out_min_T|';
$wl_lines[]     = 'detail|Dew Point|wl_dewp_out_act|wl_dewp_out_max|wl_dewp_out_max_T|wl_dewp_out_min|wl_dewp_out_min_T|';
$wl_lines[]     = 'hr|';
$wl_lines[]     = 'detail|Barometer|wl_baro_act|wl_baro_max|wl_baro_max_T|wl_baro_min|wl_baro_min_T|';
$wl_lines[]     = 'detail|Bar Trend|wl_baro_trend| | | | |';
$wl_lines[]     = 'hr|';
$wl_lines[]     = 'detail|Wind Speed|wl_wind_act|wl_wind_max|wl_wind_max_T| | |';
$wl_lines[]     = 'text|Wind Direction|'.$val['wl_compass'].' '.$val['wl_wind_dir'].'| | | | |';
$wl_lines[]     = 'hr|';
if (    (isset ($wl_is_uv)    && $wl_is_uv    == true)       
     || (isset ($wl_is_solar) && $wl_is_solar == true) ) 
     {  $wl_lines[]     = 'hr|';}
if ( (isset ($wl_is_solar) && $wl_is_solar == true) ) 
     {  $wl_lines[]     = 'detail|Solar Radiation|wl_sol_act|wl_sol_max|wl_sol_max_T| | |'; }
if ( (isset ($wl_is_uv) && $wl_is_uv == true) ) 
     {  $wl_lines[]     = 'detail|UV Radiation|wl_uv_act|wl_uv_max|wl_uv_max_T| | |'; }
$wl_lines[]     = 'head6|Wind|2 Minute|10 Minute| | | |';
$wl_lines[]     = 'detail|Average Wind Speed|wl_wind_avg_2m|wl_wind_avg_10m| | | |';
$wl_lines[]     = 'detail|Wind Gust Speed| |wl_wind_avg_10m| | | |';
}
$wl_lines[]     = 'head6|Rain|Rate|Day|Storm|Month|Year|';
$wl_lines[]     = 'detail|Rain|wl_rain_rate|wl_rain_today|wl_rain_storm|wl_rain_month|wl_rain_year|';
$wl_lines[]     = 'detail|Last Hour Rain|wl_rain_rate_hr| | | | |';
if ( (isset ($wl_is_ET) && $wl_is_ET == true) ) 
     {  $wl_lines[]     = 'detail|ET| |wl_ET_day| |wl_ET_month|wl_ET_year|';}
if ($wl_is_t_extra  || $wl_is_h_extra || $wl_is_t_soil || $wl_is_m_soil 
 || $wl_is_t_leaf   || $wl_is_w_leaf)
     {  $wl_lines[]     = 'head|Extra Sensors|Current|Today\'s Highs|Today\'s Lows|'; }
if ($wl_is_t_extra)
     {  $string = 'detail|Extra Temp#|wl_temp_E#_act|wl_temp_E#_max|wl_temp_E#_max_T|wl_temp_E#_min|wl_temp_E#_min_T|';
        if (isset ($wl_is_t_extra1) && $wl_is_t_extra1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_t_extra2) && $wl_is_t_extra2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        if (isset ($wl_is_t_extra3) && $wl_is_t_extra3 == true ) {  $wl_lines[] = str_replace('#','3',$string); }
        if (isset ($wl_is_t_extra4) && $wl_is_t_extra4 == true ) {  $wl_lines[] = str_replace('#','4',$string); }
        if (isset ($wl_is_t_extra5) && $wl_is_t_extra5 == true ) {  $wl_lines[] = str_replace('#','5',$string); }
        if (isset ($wl_is_t_extra6) && $wl_is_t_extra6 == true ) {  $wl_lines[] = str_replace('#','6',$string); }
        if (isset ($wl_is_t_extra7) && $wl_is_t_extra7 == true ) {  $wl_lines[] = str_replace('#','7',$string); }
        $wl_lines[]     = 'hr|';} 

if ($wl_is_h_extra)
     {  $string = 'detail|Extra Humidity#|wl_humi_E#_act|wl_humi_E#_max|wl_humi_E#_max_T|wl_humi_E#_min|wl_humi_E#_min_T|';
        if (isset ($wl_is_h_extra1) && $wl_is_h_extra1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_h_extra2) && $wl_is_h_extra2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        if (isset ($wl_is_h_extra3) && $wl_is_h_extra3 == true ) {  $wl_lines[] = str_replace('#','3',$string); }
        if (isset ($wl_is_h_extra4) && $wl_is_h_extra4 == true ) {  $wl_lines[] = str_replace('#','4',$string); }
        if (isset ($wl_is_h_extra5) && $wl_is_h_extra5 == true ) {  $wl_lines[] = str_replace('#','5',$string); }
        if (isset ($wl_is_h_extra6) && $wl_is_h_extra6 == true ) {  $wl_lines[] = str_replace('#','6',$string); }
        if (isset ($wl_is_h_extra7) && $wl_is_h_extra7 == true ) {  $wl_lines[] = str_replace('#','7',$string); }
        $wl_lines[]     = 'hr|';} 
if ($wl_is_t_soil) 
     {  $string = 'detail|Soil Temp#|wl_soil_temp#_act|wl_soil_temp#_max|wl_soil_temp#_max_T|wl_soil_temp#_min|wl_soil_temp#_min_T|';
        if (isset ($wl_is_t_soil1) && $wl_is_t_soil1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_t_soil2) && $wl_is_t_soil2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        if (isset ($wl_is_t_soil3) && $wl_is_t_soil3 == true ) {  $wl_lines[] = str_replace('#','3',$string); }
        if (isset ($wl_is_t_soil4) && $wl_is_t_soil4 == true ) {  $wl_lines[] = str_replace('#','4',$string); }
        $wl_lines[]     = 'hr|';}
if ($wl_is_m_soil) 
     {  $string = 'detail|Soil Moisture#|wl_soil_moist#_act|wl_soil_moist#_max|wl_soil_moist#_max_T|wl_soil_moist#_min|wl_soil_moist#_min_T|';
        if (isset ($wl_is_m_soil1) && $wl_is_m_soil1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_m_soil2) && $wl_is_m_soil2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        if (isset ($wl_is_m_soil3) && $wl_is_m_soil3 == true ) {  $wl_lines[] = str_replace('#','3',$string); }
        if (isset ($wl_is_m_soil4) && $wl_is_m_soil4 == true ) {  $wl_lines[] = str_replace('#','4',$string); }
        $wl_lines[]     = 'hr|';}
if ($wl_is_t_leaf)     
     {  $string = 'detail|Leaf Temp#|wl_leaf_temp#_act|wl_leaf_temp#_max|wl_leaf_temp#_max_T|wl_leaf_temp#_min|wl_leaf_temp#_min_T|';
        if (isset ($wl_is_t_leaf1) && $wl_is_t_leaf1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_t_leaf2) && $wl_is_t_leaf2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        $wl_lines[]     = 'hr|';}     
if ($wl_is_w_leaf)     
     {  $string = 'detail|Leaf Wetness#|wl_leaf_wetn#_act|wl_leaf_wetn#_max|wl_leaf_wetn#_max_T|wl_leaf_wetn#_min|wl_leaf_wetn#_min_T|';
        if (isset ($wl_is_w_leaf1) && $wl_is_w_leaf1 == true ) {  $wl_lines[] = str_replace('#','1',$string); }
        if (isset ($wl_is_w_leaf2) && $wl_is_w_leaf2 == true ) {  $wl_lines[] = str_replace('#','2',$string); }
        $wl_lines[]     = 'hr|';}  
#
#$debug .=  __LINE__.'$val='.print_r($val,true).PHP_EOL; 
#$debug .=  __LINE__.'$wl_lines='.print_r($wl_lines,true).PHP_EOL;  
#exit;
#-----------------------------------------------
#  Print the table. Start with the general part
#-----------------------------------------------
echo '<h3 style="margin: 0px auto; width: 100%; padding: 2px; text-align: center;">'. lang('Stations data at WL.com').'</h3>
<div style="width: 100%; max-width: 800px; margin: 0 auto; padding: 0;">
<table  class="left_table" style ="width: 100%; margin: 0 auto; padding: 2px; border-spacing: 0px; border-width: 0px;
                font-family: Arial, Helvetica, Verdana, Geneva, sans-serif; 
                font-size: 18px; border-collapse: collapse; text-align: left;">'.PHP_EOL;
#-----------------------------------------------
# Second MAIN loop
# Print every single line of the table based on its type
#-----------------------------------------------
$val[' ']=' ';  // needed for empty values
#
$extra = '';
$head   = '';
foreach ($wl_lines as $string)
     {  list ($type,$txt,$val1,$val2,$val3,$val4,$val5)       = explode ('|',$string.'||||||');
        
        switch (trim($type))
          { case 'detail':              // standard detail line
                $all = $val[$val1].$val[$val2].$val[$val3].$val[$val4].$val[$val5]; 
                if (strlen($all) == 0) {break;}
                echo $head.$extra.'<tr><td>&nbsp;' .wl_trans($txt)
                        .'</td><td>'.$val[$val1].'</td><td>'.$val[$val2].'</td><td>'.$val[$val3]
                        .'</td><td>'.$val[$val4].'</td><td>'.$val[$val5].'</td></tr>'.PHP_EOL;
                $extra = $head = '';
                break;
            case 'hr':                  //  horizontal row
                $extra = '<tr style="height: 7px;"><td colspan="6"><hr style="height: 1px; margin: 4px 0px 2px 0px; "></td></tr>'.PHP_EOL;
                break;
            case 'head':                // standard header row
                $head = '<tr style="height: 7px;"><td colspan="6"><hr style="height: 1px; margin: 4px 0px 2px 0px; "></td></tr>'
                        .'<tr style="background: #cccccc; font-weight: bold; font-size: 13px; text-align: left; color: black;">'
                        .'<td style="font-style: italic;">&nbsp;' .wl_trans($txt)
                        .'</td><td>'.wl_trans($val1).'</td><td colspan="2">'.wl_trans($val2)
                        .'</td><td colspan="2">'.wl_trans($val3).'</td></tr>'
                        .'<tr style="height: 7px;"><td colspan="6"><hr style="height: 1px; margin: 4px 0px 2px 0px; "></td></tr>'.PHP_EOL;  
                $extra = '';
                break;
            case 'head6':               // more detailed header row
                $head =    '<tr style="height: 7px;"><td colspan="6"><hr style="height: 1px; margin: 4px 0px 2px 0px; "></td></tr>'
                        .'<tr style="background: #cccccc; font-weight: bold; font-size: 13px; text-align: left; color: black;">'
                        .'<td style="font-style: italic;">&nbsp;'.wl_trans($txt)
                        .'</td><td>'.wl_trans($val1).'</td><td>'.wl_trans($val2).'</td><td>'.wl_trans($val3)
                        .'</td><td>'.wl_trans($val4).'</td><td>'.wl_trans($val5).'</td></tr>'
                        .'<tr style="height: 7px;"><td colspan="6"><hr style="height: 1px; margin: 4px 0px 2px 0px; "></td></tr>'.PHP_EOL;
                $extra = '';
                break;
            case 'text':                // detail fields, but only text, used for non standard lines
                echo $extra.'<tr><td>&nbsp;' .wl_trans($txt)
                        .'</td><td>'.$val1.'</td><td>'.$val2.'</td><td>'.$val3
                        .'</td><td>'.$val4.'</td><td>'.$val5.'</td></tr>'.PHP_EOL;
                $extra = '';
                break;             
          }  // switch
        }  // eo foreach 
#-----------------------------------------------
#  Print closing part of the table
#-----------------------------------------------
echo $extra.'
<tr><td colspan="6" style="text-align: center; font-weight: bold;  font-size: 13px; font-style: italic; ">'.$wl_obs_string.'</td></tr>
</table>
<br />
</div>
<!--END: SUMMARY DISPLAY WL.COM v2 -->'.PHP_EOL;#echo'<pre>'.$debug;
#
return;
#-----------------------------------------------
#       F U N C T I O N S 
#-----------------------------------------------
# format a numeric value: nr decimals, 
#       decimal point or comma , seperator
#-----------------------------------------------
function wl_number ($num, $dec = 1, $point = '', $sep = '') 
     {  global $wl_comma_dec;
        $amount	= (float) $num;
        if ($wl_comma_dec) 
             {  $commaDecimal = ',';} 
        else {  $commaDecimal = '.';} 
        if ($point <> '')          
             {  $commaDecimal = $point;} 
        return number_format (round($amount,$dec),$dec,$commaDecimal,$sep); } // eof wl_number
#-----------------------------------------------
# load array with all field specifications  
#   name in script, type, location in xml, xml-name
#-----------------------------------------------
function wl_load_array()
     {  global  $wl , $xx,
                $wl_is_t_extra , $wl_is_t_extra1, $wl_is_t_extra2, $wl_is_t_extra3, $wl_is_t_extra4, 
                                 $wl_is_t_extra5, $wl_is_t_extra6, $wl_is_t_extra7,
                $wl_is_h_extra , $wl_is_h_extra1, $wl_is_h_extra2, $wl_is_h_extra3, $wl_is_h_extra4,
                                 $wl_is_h_extra5, $wl_is_h_extra6, $wl_is_h_extra7,
                $wl_is_t_soil,   $wl_is_t_soil1 , $wl_is_t_soil2,  $wl_is_t_soil3,  $wl_is_t_soil4,
                $wl_is_m_soil,   $wl_is_m_soil1 , $wl_is_m_soil2,  $wl_is_m_soil3,  $wl_is_m_soil4,
                $wl_is_t_leaf,   $wl_is_t_leaf1 , $wl_is_t_leaf2,  
                $wl_is_w_leaf,   $wl_is_w_leaf1,  $wl_is_w_leaf2,
                $wl_is_solar ,   $wl_is_ET ,
                $wl_is_uv;
        #-----------------------------------------------
        # subfunction to generate same field specs 
        #   such as extra temp1-7  soil 1-4
        #-----------------------------------------------
        function load_extra($nr,&$type) 
             {  global $wl, $xx;
                foreach ($xx as $string)
                     {  $string = str_replace('#',$nr,$string);
                        $wl[]   = $string; }
                $type   = true; } // eof load_extra
                        
# -----------------  temperature heat chill dewp
        $wl[]   = 'wl_temp_out_act      |temp   |davis  |temp_f         |';    
        $wl[]   = 'wl_temp_out_max      |temp   |xml    |temp_day_high_f|';     
        $wl[]   = 'wl_temp_out_max_T    |time   |xml    |temp_day_high_time|'; 
        $wl[]   = 'wl_temp_out_min      |temp   |xml    |temp_day_low_f|';  
        $wl[]   = 'wl_temp_out_min_T    |time   |xml    |temp_day_low_time|'; 

        $wl[]   = 'wl_temp_in_act       |temp   |xml    |temp_in_f         |'; 
        $wl[]   = 'wl_temp_in_max       |temp   |xml    |temp_in_day_high_f|';     
        $wl[]   = 'wl_temp_in_max_T     |time   |xml    |temp_in_day_high_time|';  
        $wl[]   = 'wl_temp_in_min       |temp   |xml    |temp_in_day_low_f|';  
        $wl[]   = 'wl_temp_in_min_T     |time   |xml    |temp_in_day_low_time|';

        $wl[]   = 'wl_heat_out_act      |temp   |davis  |heat_index_f         |';    
        $wl[]   = 'wl_heat_out_max      |temp   |xml    |heat_index_day_high_f|';     
        $wl[]   = 'wl_heat_out_max_T    |time   |xml    |heat_index_day_high_time|'; 

        $wl[]   = 'wl_chil_out_act      |temp   |davis  |windchill_f         |';    
        $wl[]   = 'wl_chil_out_min      |temp   |xml    |windchill_day_low_f|';  
        $wl[]   = 'wl_chil_out_min_T    |time   |xml    |windchill_day_low_time|'; 

        $wl[]   = 'wl_dewp_out_act      |temp   |davis  |dewpoint_f         |';    
        $wl[]   = 'wl_dewp_out_max      |temp   |xml    |dewpoint_day_high_f|';     
        $wl[]   = 'wl_dewp_out_max_T    |time   |xml    |dewpoint_day_high_time|'; 
        $wl[]   = 'wl_dewp_out_min      |temp   |xml    |dewpoint_day_low_f|';  
        $wl[]   = 'wl_dewp_out_min_T    |time   |xml    |dewpoint_day_low_time|'; 

# ---------------------- extra temperature 1 - 7
        $xx     = array();
        $xx[]   = 'wl_temp_E#_act       |temp   |xml    |temp_extra_#         |'; 
        $xx[]   = 'wl_temp_E#_max       |temp   |xml    |temp_extra_#_day_high|';     
        $xx[]   = 'wl_temp_E#_max_T     |time   |xml    |temp_extra_#_day_high_time|';  
        $xx[]   = 'wl_temp_E#_min       |temp   |xml    |temp_extra_#_day_low|';  
        $xx[]   = 'wl_temp_E#_min_T     |time   |xml    |temp_extra_#_day_low_time|'; 
        if (isset ($wl_is_t_extra1) && $wl_is_t_extra1 == true) { load_extra('1',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra2) && $wl_is_t_extra2 == true) { load_extra('2',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra3) && $wl_is_t_extra3 == true) { load_extra('3',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra4) && $wl_is_t_extra4 == true) { load_extra('4',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra5) && $wl_is_t_extra5 == true) { load_extra('5',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra6) && $wl_is_t_extra6 == true) { load_extra('6',$wl_is_t_extra); }
        if (isset ($wl_is_t_extra7) && $wl_is_t_extra7 == true) { load_extra('7',$wl_is_t_extra); }
       
# -------------------------------- pressure baro
        $wl[]   = 'wl_baro_act          |baro   |davis  |pressure_in         |';    
        $wl[]   = 'wl_baro_max          |baro   |xml    |pressure_day_high_in|';     
        $wl[]   = 'wl_baro_max_T        |time   |xml    |pressure_day_high_time|'; 
        $wl[]   = 'wl_baro_min          |baro   |xml    |pressure_day_low_in|';  
        $wl[]   = 'wl_baro_min_T        |time   |xml    |pressure_day_low_time|'; 
        
        $wl[]   = 'wl_baro_trend        |trnd   |xml    |pressure_tendency_string|';  
# ------------------------------------- humidity
        $wl[]   = 'wl_humi_out_act      |humi   |davis  |relative_humidity    |';    
        $wl[]   = 'wl_humi_out_max      |humi   |xml    |relative_humidity_day_high|';     
        $wl[]   = 'wl_humi_out_max_T    |time   |xml    |relative_humidity_day_high_time|'; 
        $wl[]   = 'wl_humi_out_min      |humi   |xml    |relative_humidity_day_low|';  
        $wl[]   = 'wl_humi_out_min_T    |time   |xml    |relative_humidity_day_low_time|'; 

        $wl[]   = 'wl_humi_in_act       |humi   |xml    |relative_humidity_in   |';    
        $wl[]   = 'wl_humi_in_max       |humi   |xml    |relative_humidity_in_day_high|';     
        $wl[]   = 'wl_humi_in_max_T     |time   |xml    |relative_humidity_in_day_high_time|'; 
        $wl[]   = 'wl_humi_in_min       |humi   |xml    |relative_humidity_in_day_low|';  
        $wl[]   = 'wl_humi_in_min_T     |time   |xml    |relative_humidity_in_day_low_time|'; 
# ----------------------------extra humidity 1-7
        $xx     = array();
        $xx[]   = 'wl_humi_E#_act       |humi   |xml    |relative_humidity_#    |';    
        $xx[]   = 'wl_humi_E#_max       |humi   |xml    |relative_humidity_#_day_high|';     
        $xx[]   = 'wl_humi_E#_max_T     |time   |xml    |relative_humidity_#_day_high_time|'; 
        $xx[]   = 'wl_humi_E#_min       |humi   |xml    |relative_humidity_#_day_low|';  
        $xx[]   = 'wl_humi_E#_min_T     |time   |xml    |relative_humidity_#_day_low_time|'; 
        if (isset ($wl_is_h_extra1) && $wl_is_h_extra1 == true) { load_extra('1', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra2) && $wl_is_h_extra2 == true) { load_extra('2', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra3) && $wl_is_h_extra3 == true) { load_extra('3', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra4) && $wl_is_h_extra4 == true) { load_extra('4', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra5) && $wl_is_h_extra5 == true) { load_extra('5', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra6) && $wl_is_h_extra6 == true) { load_extra('6', $wl_is_h_extra); }
        if (isset ($wl_is_h_extra7) && $wl_is_h_extra7 == true) { load_extra('7', $wl_is_h_extra); }

# ------------------------- wind speed direction
        $wl[]   = 'wl_wind_dir          |degr   |davis  |wind_degrees |';
        $wl[]   = 'wl_wind_act          |wind   |xml    |wind_ten_min_avg_mph |';
        $wl[]   = 'wl_wind_max          |wind   |xml    |wind_day_high_mph |';
        $wl[]   = 'wl_wind_max_T        |time   |xml    |wind_day_high_time |';
        $wl[]   = 'wl_wind_avg_2m       |wind   |davis  |wind_mph |';
        $wl[]   = 'wl_wind_avg_10m      |wind   |xml    |wind_ten_min_avg_mph |';
        $wl[]   = 'wl_wind_gust_10m     |wind   |xml    |wind_ten_min_gust_mph |';
if (isset ($wl_is_solar) && $wl_is_solar == true) {        
# ---------------------------------------- solar
                $wl[]   = 'wl_sol_act           |solar  |xml    |solar_radiation |';
                $wl[]   = 'wl_sol_max           |solar  |xml    |solar_radiation_day_high |';
                $wl[]   = 'wl_sol_max_T         |time   |xml    |solar_radiation_day_high_time |';}
if (isset ($wl_is_ET ) && $wl_is_ET  == true) {        
# ---------------------------------------- ET
                $wl[]   = 'wl_ET_day            |rain   |xml    |et_day |'; 
                $wl[]   = 'wl_ET_month          |rain   |xml    |et_month |';
                $wl[]   = 'wl_ET_year           |rain   |xml    |et_year |';   }
if (isset ($wl_is_uv) && $wl_is_uv == true) {        
# ------------------------------------------- uv
                $wl[]   = 'wl_uv_act            |uv     |xml    |uv_index |';
                $wl[]   = 'wl_uv_max            |uv     |xml    |uv_index_day_high |';
                $wl[]   = 'wl_uv_max_T          |time   |xml    |uv_index_day_high_time |';}
# ----------------------------------------- rain
        $wl[]   = 'wl_rain_today        |rain   |xml    |rain_day_in |';
        $wl[]   = 'wl_rain_month        |rain   |xml    |rain_month_in |';
        $wl[]   = 'wl_rain_year         |rain   |xml    |rain_year_in |';
        $wl[]   = 'wl_rain_rate         |rain   |xml    |rain_rate_in_per_hr |';
        $wl[]   = 'wl_rain_storm        |rain   |xml    |rain_storm_in |';
        $wl[]   = 'wl_rain_rate_hr      |rain   |xml    |rain_rate_hour_high_in_per_hr |';
# ------------------------------------ soil temp   
        $xx     = array();
        $xx[]   = 'wl_soil_temp#_act    |temp   |xml    |temp_soil_#         |';    
        $xx[]   = 'wl_soil_temp#_max    |temp   |xml    |temp_soil_#_day_high|';     
        $xx[]   = 'wl_soil_temp#_max_T  |time   |xml    |temp_soil_#_day_high_time|'; 
        $xx[]   = 'wl_soil_temp#_min    |temp   |xml    |temp_soil_#_day_low|';  
        $xx[]   = 'wl_soil_temp#_min_T  |time   |xml    |temp_soil_#_day_low_time|'; 
        if (isset ($wl_is_t_soil1) && $wl_is_t_soil1 == true) { load_extra('1', $wl_is_t_soil); }
        if (isset ($wl_is_t_soil2) && $wl_is_t_soil2 == true) { load_extra('2', $wl_is_t_soil); }
        if (isset ($wl_is_t_soil3) && $wl_is_t_soil3 == true) { load_extra('3', $wl_is_t_soil); }
        if (isset ($wl_is_t_soil4) && $wl_is_t_soil4 == true) { load_extra('4', $wl_is_t_soil); }
#echo '<pre>'.$wl_is_t_soil.$wl_is_t_soil1.$wl_is_t_soil2.print_r($wl,true); exit;
# ------------------------------------ soil moist   
        $xx     = array();
        $xx[]   = 'wl_soil_moist#_act    |moist  |xml    |soil_moisture_#         |';    
        $xx[]   = 'wl_soil_moist#_max    |moist  |xml    |soil_moisture_#_day_high |';     
        $xx[]   = 'wl_soil_moist#_max_T  |time   |xml    |soil_moisture_#_day_high_time|'; 
        $xx[]   = 'wl_soil_moist#_min    |moist  |xml    |soil_moisture_#_day_low |';  
        $xx[]   = 'wl_soil_moist#_min_T  |time   |xml    |soil_moisture_#_day_low_time |'; 
        if (isset ($wl_is_m_soil1) && $wl_is_m_soil1 == true) { load_extra('1', $wl_is_m_soil); }
        if (isset ($wl_is_m_soil2) && $wl_is_m_soil2 == true) { load_extra('2', $wl_is_m_soil); }
        if (isset ($wl_is_m_soil3) && $wl_is_m_soil3 == true) { load_extra('3', $wl_is_m_soil); }
        if (isset ($wl_is_m_soil4) && $wl_is_m_soil4 == true) { load_extra('4', $wl_is_m_soil); } 
# ------------------------------------ leaf temp   
        $xx     = array();
        $xx[]   = 'wl_leaf_temp#_act    |temp   |xml    |temp_leaf_#         |';    
        $xx[]   = 'wl_leaf_temp#_max    |temp   |xml    |temp_leaf_#_day_high|';     
        $xx[]   = 'wl_leaf_temp#_max_T  |time   |xml    |temp_leaf_#_day_high_time|'; 
        $xx[]   = 'wl_leaf_temp#_min    |temp   |xml    |temp_leaf_#_day_low|';  
        $xx[]   = 'wl_leaf_temp#_min_T  |time   |xml    |temp_leaf_#_day_low_time|'; 
        if (isset ($wl_is_t_leaf1) && $wl_is_t_leaf1 == true) { load_extra('1', $wl_is_t_leaf); }
        if (isset ($wl_is_t_leaf2) && $wl_is_t_leaf2 == true) { load_extra('2', $wl_is_t_leaf); }
# ----------------------------------leaf wetness   
        $xx     = array();
        $xx[]   = 'wl_leaf_wetn#_act    |none   |xml    |leaf_wetness_#         |';    
        $xx[]   = 'wl_leaf_wetn#_max    |none   |xml    |leaf_wetness_#_day_high|';     
        $xx[]   = 'wl_leaf_wetn#_max_T  |time   |xml    |leaf_wetness_#_day_high_time|'; 
        $xx[]   = 'wl_leaf_wetn#_min    |none   |xml    |leaf_wetness_#_day_low|';  
        $xx[]   = 'wl_leaf_wetn#_min_T  |time   |xml    |leaf_wetness_#_day_low_time|'; 
        if (isset ($wl_is_w_leaf1) && $wl_is_w_leaf1 == true) { load_extra('1', $wl_is_w_leaf); }
        if (isset ($wl_is_w_leaf2) && $wl_is_w_leaf2 == true) { load_extra('2', $wl_is_w_leaf); }
# ------------------------------------- others
        $wl[]   = 'wl_station_name      |none   |xml    |station_name|';} // eof wl_load_array
#
#-----------------------------------------------
# translate "english" strings to local language
#-----------------------------------------------
function wl_trans($words) 
     {  global $WL_TRANS;
        if (isset ($WL_TRANS[$words]) ) 
             {  return $WL_TRANS[$words];}
        else {  return $words;}
} // eof   wl_trans
#-----------------------------------------------
# load language strings in $WL_TRANS array
#-----------------------------------------------
function wl_trans_loads($wl_lang) 
     {  global $WL_TRANS;
if ($wl_lang == 'nl')     {  $string      = '
#                               day_names_long
langlookup|Friday|Vrijdag|
langlookup|Monday|Maandag|
langlookup|Saturday|Zaterdag|
langlookup|Sunday|Zondag|
langlookup|Thursday|Donderdag|
langlookup|Tuesday|Dinsdag|
langlookup|Wednesday|Woensdag|
#                               month_names_long
langlookup|April|April|
langlookup|August|Augustus|
langlookup|December|December|
langlookup|February|Februari|
langlookup|January|Januari|
langlookup|July|Juli|
langlookup|June|Juni|
langlookup|March|Maart|
langlookup|May|Mei|
langlookup|November|November|
langlookup|October|Oktober|
langlookup|September|September|
#                               wind_and_compass
langlookup|East|Oosten|
langlookup|ENE|ONO|
langlookup|ESE|OZO|
langlookup|E|O|
langlookup|NE|NO|
langlookup|NNE|NNO|
langlookup|NNW|NNW|
langlookup|North|Noorden|
langlookup|NW|NW|
langlookup|N|N|
langlookup|SE|ZO|
langlookup|South|Zuiden|
langlookup|SSE|ZZO|
langlookup|SSW|ZZW|
langlookup|SW|ZW|
langlookup|S|Z|
langlookup|West|Westen|
langlookup|WNW|WNW|
langlookup|WSW|WZW|
langlookup|W|W|
# ---------------------------------  field names
langlookup|Outside Temp|Temperatuur buiten|
langlookup|Outside Humidity|Luchtvocht buiten|
langlookup|Inside Temp|Temperatuur binnen|
langlookup|Inside Humidity|Luchtvocht binnen|
langlookup|Heat Index|Heat Index|
langlookup|Wind Chill|Wind Chill|
langlookup|Dew Point|Dauwpunt|
langlookup|Barometer|Luchtdruk|
langlookup|Bar Trend|Baro trend|
langlookup|Wind Speed|Wind snelheid|
langlookup|Wind Direction|Wind richting|
langlookup|Solar Radiation|Zonnestraling|
langlookup|UV Radiation|UV intensiteit|
langlookup|12 Hour Forecast|12 uurs verwachting|
langlookup|Average Wind Speed|Wind snelheid gemiddeld|
langlookup|Wind Gust Speed|Windvlaag snelheid|
langlookup|Rain|Regen|
langlookup|Last Hour Rain|Regenval laatste uur|
# ------------------------------ colom names
langlookup|Station Summary|Station Summary|
langlookup|Current|Huidig|
langlookup|Today\'s Highs|Hoogste vandaag|
langlookup|Today\'s Lows|Laagste vandaag|
langlookup|2 Minute|2 Minuten|
langlookup|10 Minute|10 Minuten|
langlookup|Rate|Snelheid|
langlookup|Day|Dag|
langlookup|Storm|Storm|
langlookup|Month|Maand|
langlookup|Year|Jaar|
langlookup|Rain|Regen|
# ------------------------------ barometer trend
langlookup|Falling Rapidly|Snel dalend|
langlookup|Falling Slowly|Langzaam dalend|
langlookup|Rising Rapidly|Snel stijgend|
langlookup|Rising Slowly|Langzaam stijgend|
langlookup|Steady|Bestendig|
#
# ---------------------------------- other
langlookup|Calm|Kalm|
langlookup|Current conditions at|De weercondities op|';
## end of NL language
}
elseif ($wl_lang == 'en') {$string      = '
';}
elseif ($wl_lang == 'de') {$string      = '
#                               day_names_long
langlookup|Friday|Freitag|
langlookup|Monday|Montag|
langlookup|Saturday|Samstag|
langlookup|Sunday|Sonntag|
langlookup|Thursday|Donnerstag|
langlookup|Tuesday|Dienstag|
langlookup|Wednesday|Mittwoch|
#                               month_names_long
langlookup|April|April|
langlookup|August|August|
langlookup|December|Dezember|
langlookup|February|Februar|
langlookup|January|Januar|
langlookup|July|Juli|
langlookup|June|Juni|
langlookup|March|März|
langlookup|May|Mai|
langlookup|November|November|
langlookup|October|Oktober|
langlookup|September|September|

#                               wind_and_compass
langlookup|E|O|
langlookup|East|Osten|
langlookup|ENE|ONO|
langlookup|ESE|OSO|
langlookup|N|N|
langlookup|NE|NO|
langlookup|NNE|NNO|
langlookup|NNW|NNW|
langlookup|North|Norden|
langlookup|NW|NW|
langlookup|S|S|
langlookup|SE|SO|
langlookup|South|Süden|
langlookup|SSE|SSO|
langlookup|SSW|SSW|
langlookup|SW|SW|
langlookup|W|W|
langlookup|West|Westen|
langlookup|WNW|WNW|
langlookup|WSW|WSW|
# ---------------------------------  field names
langlookup|Outside Temp|Temperatur draußen|
langlookup|Outside Humidity|Luftfeuchtigkeit draußen|
langlookup|Inside Temp|Temperatur innen|
langlookup|Inside Humidity|Luftfeuchtigkeit innen|
langlookup|Heat Index|Hitzeindex|
langlookup|Wind Chill|Wind Chill|
langlookup|Dew Point|Dew Point|
langlookup|Barometer|Barometer|
langlookup|Bar Trend|Barometer Trend|
langlookup|Wind Speed|Windgeschwindigkeit|
langlookup|Wind Direction|Windrichtung|
langlookup|Solar Radiation|Sonnenstrahlung|
langlookup|UV Radiation|UV|
langlookup|Average Wind Speed|Durchschnittliche Windgeschwindigkeit|
langlookup|Wind Gust Speed|Gust Geschwindigkeit|
langlookup|Rain|Regen|
langlookup|Last Hour Rain|Letzte Stunde Regen|
# ------------------------------ colom names
langlookup|Station Summary|Stationsübersicht|
langlookup|Current|Current|
langlookup|Today\'s Highs|Heutige Höhen|
langlookup|Today\'s Lows|Heutige Tiefs|
langlookup|2 Minute|2 Minuten|
langlookup|10 Minute|10 Minuten|
langlookup|Rate|Niederschlagsrate|
langlookup|Day|Day|
langlookup|Storm|Regensturm|
langlookup|Month|Month|
langlookup|Year|Jahr|
# ------------------------------ barometer trend
langlookup|Falling Rapidly|Schnell Fallend|
langlookup|Falling Slowly|Langsam Fallend|
langlookup|Rising Rapidly|Schnell Steigend|
langlookup|Rising Slowly|Langsam Steigend|
langlookup|Steady|Gleichbleibend|
#
# ---------------------------------- other
langlookup|Calm|Windstille|
langlookup|Current conditions at|Aktuelle Bedingungen an|';}
elseif ($wl_lang == 'fr') { $string      = '

#                               day_names_long
langlookup|Friday|Vendredi|
langlookup|Monday|Lundi|
langlookup|Saturday|Samedi|
langlookup|Sunday|Dimanche|
langlookup|Thursday|Jeudi|
langlookup|Tuesday|Mardi|
langlookup|Wednesday|Mercredi|
#                               month_names_long
langlookup|April|avril|
langlookup|August|Août|
langlookup|December|décembre|
langlookup|February|février|
langlookup|January|janvier|
langlookup|July|juillet|
langlookup|June|juin|
langlookup|March|mars|
langlookup|May|Mai|
langlookup|November|novembre|
langlookup|October|octobre|
langlookup|September|septembre|
#                               wind_and_compass
langlookup|E|E|
langlookup|East|Est|
langlookup|ENE|ENE|
langlookup|ESE|ESE|
langlookup|N|N|
langlookup|NE|NE|
langlookup|NNE|NNE|
langlookup|NNW|NNO|
langlookup|North|Nord|
langlookup|NW|NO|
langlookup|S|S|
langlookup|SE|SE|
langlookup|South|Sud|
langlookup|SSE|SSE|
langlookup|SSW|SSO|
langlookup|SW|SO|
langlookup|W|O|
langlookup|West|Ouest|
langlookup|WNW|ONO|
langlookup|WSW|OSO|
# ---------------------------------  field names
langlookup|Outside Temp|Température extérieure|
langlookup|Outside Humidity|Humidité extérieure|
langlookup|Inside Temp|Température à l\'intérieur|
langlookup|Inside Humidity|Humidité à l\'intérieur|
langlookup|Heat Index|Indice de chaleur|
langlookup|Wind Chill|Refroidissement éolien|
langlookup|Dew Point|Point de rosée|
langlookup|Barometer|Pression atmosphérique|
langlookup|Bar Trend|Tendance du baromètre|
langlookup|Wind Speed|Vitesse du vent|
langlookup|Wind Direction|Direction du vent|
langlookup|Solar Radiation|Radiation solaire|
langlookup|UV Radiation|UV|
langlookup|Average Wind Speed|Vitesse moyenne du vent|
langlookup|Wind Gust Speed|Rafale de vent|
langlookup|Rain|Pluie|
langlookup|Last Hour Rain|Dernière heure de pluie|  Précipitations de dernière heure
# ------------------------------ colom names
langlookup|Station Summary|Résumé de la station|
langlookup|Current|Actuellement|
langlookup|Today\'s Highs|Maxima d\'aujourd\'hui|
langlookup|Today\'s Lows|Minima d\'aujourd\'hui|
langlookup|2 Minute|2 minutes|
langlookup|10 Minute|10 minutes|
langlookup|Rate|Taux de pluie|
langlookup|Day|Jour|
langlookup|Storm|Intensité pluvieuse|
langlookup|Month|Mois|
langlookup|Year|An|
# ------------------------------ barometer trend
langlookup|Falling Rapidly|Tombe rapidement|
langlookup|Falling Slowly|Tombe lentement|
langlookup|Rising Rapidly|Monte rapidement|
langlookup|Rising Slowly|Monte lentement|
langlookup|Steady|Constant|
#
# ---------------------------------- other
langlookup|Calm|calme|
langlookup|Current conditions at|Conditions actuelles à|
';}
        $arr            = explode ("\n",$string);
        $WL_TRANS       = array();
        foreach ($arr as $trans) 
             {  list ($text,$from,$to) = explode ('|',$trans.'|||');
                if ($text <> 'langlookup') {continue;}
                $from   = trim($from);
                $to     = trim($to);
                if ($from == '' || $to == '') {continue;}
                $WL_TRANS[$from]= $to;}
} // eof wl_trans_loads