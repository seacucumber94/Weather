<?php  $scrpt_vrsn_dt  = 'fct_ec_block.php|01|2020-11-02|';  # release 2012_lts
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
#                                script settings
#
# these are settings for retrieving information
# from https://dd.meteo.gc.ca/citypage_weather/xml/ON/s0000024_f.xml
#------------------------------------------------
#
$EC_area	= $alarm_area;
# -----------------------for testing
#$province       = 'ON'; #
#$EC_area	= 's0000024';  # for testing
# -----------------------for testing
#
$fct_d_needed   = true;
#
# ----------------------   load general EC code
$scrpt          = 'fct_ec_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
$return = include_once $scrpt; 
if ($return == false) { return false;}  

if ( ($EC_filetime + 3600) < time() )
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($EC_filetime,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
#
echo '<table style="font-size: 10px; width: 310px; margin: 0px auto; height: 150px; text-align: center;">'.PHP_EOL;
$rows           = 5;
$cols           = 4;
$rw_cntnt       = array ('part','icnc','temp','wspd','idir','rain');
$rows           = count($rw_cntnt); 
$wunit          = $fcts_arr['0']['windunit'];
if ($wunit == '') {$wunit = 'km/h';}
$wunit          = '<small>'.lang($wunit).'</small>';
$norain         = '-';
#
for ($n = 0; $n < $rows; $n++)
     {  echo '<tr>'.PHP_EOL;
        for ($i = 0; $i < $cols; $i++)
            {   echo '<td>'; 
                $comtent        = $rw_cntnt[$n];
                $arr    = $fcts_arr[$i]; #echo '<!-- '.print_r($arr,true).' -->';
                switch ($comtent){
                    case 'part': 
                        if ($arr['temptype'] <> 'low') {$color = "#FF7C39"; } else {$color = "#01A4B4"; }
                        if ($ec_e_f <> 'f') 
                             {  $txts   = explode (' ',$arr['daypart']);
                                $text   = '';
                                foreach ($txts as $str) {$text .= lang($str).' ';}}
                        else {  $text   = $arr['daypart'];} 
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icnc': 
                        $key    = (int)$arr['condicon'];
                        $icon   = EC_icon ($key);
                        echo '<img src="'.$icon.'" width="60" height="32" alt="'.$icon.'" style="vertical-align: top;"> ';
                        break;
                    case 'temp': 
                        if ($arr['temptype'] <> 'low') {$color = "#FF7C39"; } else {$color = "#01A4B4"; }
                        if ($arr['windChill'] && 0 && $arr['windChill'] <> $arr['temperature'] && ($arr['temperature'] - $arr['windChill'])  > 3)
                             {  $fonts = '12';} else {$fonts = '16';}
                        $temp = convert_temp ($arr['temperature'],'C',$tempunit,0);  
                        echo '<span style="font-size: '.$fonts.'px; color: '.$color.';">'.$temp;
                        if ($fonts == '12')
                             {  $temp = convert_temp ($arr['windChill'],'C',$tempunit,0);
                                echo '<small>&rarr;</small>'.'<span style="font-size: 16px;">'.$temp.'</span>';}
                        echo '<small>&deg;'.$tempunit.'</small></span>';
                        break;
                    case 'wspd': 
                        $wind   = convert_speed ($arr['windspeed'],'kmh',$windunit,0) ;
                        echo $wind.' '.$windunit; 
                        break;
                    case 'idir': 
                        $dir    = $dirt = $arr['winddir'];
                        if ($ec_e_f == 'f' && $dir <> '') {$dir = $ec_french_winddir[$dir];}
                        if ($dir <> '')
                              { if ($dir <> 'VR' )
                                     {  echo '<img src="img/windicons/'.$dir.'.svg" width="20" height="20" alt="'.$dir.'" style="vertical-align: bottom;"> ';
                                        echo $dirt;}
                                else { echo lang('VR');}}
                        else {  echo $norain; }
                        break;
                    case 'rain': 
                        $precip = $arr['preciptype'];
                        if (!isset ($arr['precipunit']) )
                             {  $unit   = '';}
                        else {  $unit   = lang($arr['precipunit']);}
                        $amount = (int) $arr['precipacc']; 
                        if ($precip == '' && $amount == 0 ) {echo $norain; break;}
                        if ($precip == 'snow' || $precip == 'neige') {echo $snowflakesvg;} else { echo $rainsvg;}
                        if ($amount <> 0)
                             {  echo ' '.$amount.'<small>'.$unit.'</small>';  }
                        break;              
                    default: echo $n.'-'.$i.'-'.$comtent;
                             
                        } // eo switch
                } // eo cols
#echo PHP_EOL.PHP_EOL.PHP_EOL.'<pre style="text-align: left;">'.print_r($fcts_arr[$i],true); exit;
                                
        } // eo rows
echo '</table>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
#echo '<pre>'.print_r ($ccn_arr,true).print_r ($fcts_arr[0],true).print_r ($dtls_arr[0],true)   ; exit;
#
