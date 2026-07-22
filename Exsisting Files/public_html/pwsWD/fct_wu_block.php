<?php $scrpt_vrsn_dt  = 'fct_wu_block.php|01|2021-12-08|';  # PHP 8.1 +release 2012_lts
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
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#      translate WU icon-names to our icon-names
$WU_icn_tr      = array ('tornado','tornado','tornado','ovc_thun_rain_dark','ovc_thun_rain_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_sleet_dark','ovc_rain.svg',
'ovc_sleet','mc_rain','mc_rain','ovc_flurries','ovc_flurries','ovc_flurries','ovc_flurries','ovc_sleet','ovc_sleet','dust',
'ovc_fog','ovc_fog','ovc_fog','ovc_windy','ovc_windy','ovc_flurries','ovc','mc_night','mc','mc_night',
'pc_day','clear_night','clear_day','few_night','few_day','ovc_sleet_dark','clear_day','ovc_thun_dark','ovc_thun_dark','mc_rain',
'mc_rain_dark','ovc_flurries','ovc_flurries_dark','ovc_flurries_dark','unknown','ovc_rain_dark','ovc_flurries_dark','ovc_thun_dark');
#-----------------------------------------------
#      genereate file name && load file in array
#-----------------------------------------------
$json   = $response = $filetime =false;
$file_nm= 'wufct_'.$locale_wu.'_'.$wu_fct_unit.'.txt';
$file   = $fl_folder.$file_nm; #echo $file; exit; // ./jsondata/wufct_en_m.txt
if (file_exists ($file))
     {  $json           = file_get_contents($file); 
        $filetime       = filemtime ($file);
        $response       = json_decode($json, true);}  # echo '<pre>response["daypart"]= '.print_r($response['daypart'], true) ; exit;    	
else {  echo '<b style="color: red;"><small>('.__LINE__.'): WU forecast file not ready<br />'.$file_nm.'</small></b>';
        return;}

#-----------------------------------------------
#                     check age of forecast file
#-----------------------------------------------
if ( ($fcts_refresh + $filetime) < time() ) // fcts_refresh from settings is allowed age
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($filetime,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
#-----------------------------------------------
#                       check if fct is complete
if (!is_array ($response['daypart']) || count ($response['daypart'][0]) < 4 )   
#         if not correct. display small messasge
     {  echo '<b style="color: red;"><small>('.__LINE__.'): WU forecast file not usable '.$file_nm.'</small></b>'; 
        return; }  
#-----------------------------------------------   
#           the json coantains all kinds of data
#                          load forecast in $arr
#-----------------------------------------------
$arr            = $response['daypart'][0]; # echo '<pre>response["daypart"][0]= '.print_r($arr, true) ; exit;    
#
#                         first part of the html
echo '<div style="padding: 0px; height: 154px; text-align: center; overflow: hidden;">
<table style="font-size: 10px; height: 100%; width: 100%; ">'.PHP_EOL;

#                        nr of forecast in block
$cols           = 4;  
#                      what fields in each row
$rw_cntnt       = array ('daypartName','iconCode','temperature', 'windSpeed', 'windDirection','precipChance');
$rows           = count($rw_cntnt); 
#                     some other settings
$wunit          = '<small>'.lang($windunit).'</small>'; // wind unit to print
$nouv           = '-';                                  // if no data  print -
$norain         = '-';
$color = $clrwrm = "#FF7C39";                           // warm and text color
$clrcld = "#01A4B4";                                    // cold color
#
# search for first valid forecast, sometimes first fct is empty 
$start = 0;
if ($arr['dayOrNight'][0] == '') {$start++; $cols++;}
#                         print forecast
for ($n = 0; $n < $rows; $n++)
     {  echo '<tr>'.PHP_EOL;
        for ($i = $start; $i < $cols; $i++)  // start with first valid fct
            {   echo '<td>'; 
                $item   = $rw_cntnt[$n];
                $content= $arr[$item][$i];   
                if ($arr['dayOrNight'][$i] == 'D')   { $color = $clrwrm; } else { $color = $clrcld; }
                switch ($item){         // switch to find type of field to be printed
                    case 'daypartName': 
                        echo '<span style="color: '.$color.';">'.$content.'</span>'; 
                        break;
                    case 'iconCode': 
                        $icon   = $WU_icn_tr [$content];
                        echo '<img src="./pws_icons/'.$icon.'.svg" width="60" height="32" alt="'.$content.'" style="vertical-align: top;">';
                        break;
                    case 'temperature': 
                        echo '<span style="font-size: 20px; color: '.$color.';">'.$content.'&deg;</span>';
                        break;
                    case 'windDirection': 
                        $bearing        = (int) $content; 
                        $compass = windlabel($bearing); # 2021-12-08
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$arr['windDirectionCardinal'][$i].'"  style="vertical-align: bottom;"> ';
                        echo $arr['windDirectionCardinal'][$i];
                        break;                    
                    case 'windSpeed': 
                        echo $content. ' '.$windunit; 
                        break;
                    case 'precipChance':  
                        if (trim($content) == '' || (int) $content == 0 ) {echo $norain; break;}
                        $type   = $arr['precipType'][$i];
                        $string = ''; # $content.'% - ';
                        if ($type == 'snow')
                             {  if ((float)$arr['qpfSnow'][$i] > 0) { $string .= $arr['qpfSnow'][$i].$rainunit.' ';}
                                $string .=  $content.'% '.$snowflakesvg;}
                        else {  if ((float)$arr['qpf'][$i] > 0)     { $string .= $arr['qpf'][$i].$rainunit.' ';}
                                $string .= $content.'% '.$rainsvg;}
                        echo $string;  
                        break;              
                   default: echo $n.'-'.$i.'-'.$item;  // this is to catch programming errors                            
                        } // eo switch
                echo '</td>'.PHP_EOL;
                } // eo cols
#echo PHP_EOL.PHP_EOL.PHP_EOL.'<pre style="text-align: left;">'.print_r($fcts_arr[$i],true); exit;
        echo '</tr>'.PHP_EOL;                     
        } // eo rows
echo '</table>
</div>'.PHP_EOL;
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
#echo '<pre>'.print_r ($ccn_arr,true).print_r ($fcts_arr[0],true).print_r ($dtls_arr[0],true)   ; exit;
#
