<?php $scrpt_vrsn_dt  = 'fct_darksky_block.php|01|2021-12-08|';  # PHP 8.1 + release 2012_lts
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
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
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
#-----------------------------------------------
#  none 
# -----------------   load general DarkSky code
$scrpt          = 'fct_darksky_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return = include_once $scrpt; 
if ($return == false) { echo 'script ends'; return false;}  
#
#
if ( ($fcts_refresh + $darkskycurTime) < time() )
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($darkskycurTime,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
#
echo '<table style="font-size: 10px; width: 310px; margin: 0px auto; height: 154px; text-align: center; overflow: hidden;">'.PHP_EOL;
$rows           = 5;
$cols           = 4;
$rw_cntnt       = array ('part','icnc','temp','wspd','idir','rain');
$rows           = count($rw_cntnt); 
$wunit          = '<small>'.lang($windunit).'</small>';
$nouv           = '-';
$color = $clrwrm = "#FF7C39";
$clrcld = "#01A4B4";
$norain         = '-';
#
for ($n = 0; $n < $rows; $n++)
     {  echo '<tr>'.PHP_EOL;
        for ($i = 0; $i < $cols; $i++)
            {   echo '<td>'; 
                $item        = $rw_cntnt[$n];
                $arr    = $darkskydayCond[$i]; #echo '<!-- '.print_r($arr,true).' -->';
                switch ($item){
                    case 'part': 
                        $text   = lang(date('l',(int)$arr['time']));  # 2019-04-18
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icnc': 
                        $icon   = DSicon_trns ($arr['icon']);
                        echo '<img src="'.$icon.'" width="60" height="32" style="vertical-align: top;" alt="icon" > ';
                        break;
                    case 'temp': 
                        $content        = $arr['temperatureHigh'];
                        $tempH  = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                        $content        = $arr['temperatureLow'];
                        $tempL  = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                        echo '<span style="font-size: 14px; color: '.$color.';">'.$tempH.'&deg;</span>&darr;';
                        echo '<span style="font-size: 14px; color: '.$clrcld.';">'.$tempL.'&deg;</span>';
                        break;
                    case 'wspd': 
                        $content        = $arr['windSpeed']; 
                        $wspd   = convert_speed ((float) $content,$darksky_used_wind,$windunit,0) ;
                        echo $wspd;
                        if (isset ($arr['windGust']) )
                             {  $content = $arr['windGust'];             
                                if (trim($content) <> '') {echo '-'.convert_speed ((float) $content,$darksky_used_wind,$windunit,0);}}
                        echo ' '.$windunit; 
                        break;
                    case 'idir': 
                        $bearing        = (int) $arr['windBearing'];
                        $compass = windlabel ($bearing);  # 2021-12-08
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$compass.'"  style="vertical-align: bottom;"> ';
                        $compass = lang($compass);  # 2019-04-18
                        echo $compass;
                        break;
                    case 'rain': 
                        $content        = (float) $arr['precipIntensity']; 
                        if (trim($content) == '' || $content == 0 ) {echo $norain; break;}
                        $content= convert_precip(24 * $content,$darksky_used_rain,$rainunit,2);
                        #$content= number_format($content,2) ;        
                        $unit   = lang($rainunit);
                        echo $content.'<small> '.$unit.'</small>';
                        if (isset ($arr['precipProbability']) )
                             {  $content = 100 * (float) $arr['precipProbability'];
                                $content = (int) $content;
                                if ($content == 0 ) { break;}}
                        echo ' <small>'.$content.'%</small>';
                        break;              
                   default: echo $n.'-'.$i.'-'.$item;
                             
                        } // eo switch
                echo '</td>'.PHP_EOL;
                } // eo cols
#echo PHP_EOL.PHP_EOL.PHP_EOL.'<pre style="text-align: left;">'.print_r($fcts_arr[$i],true); exit;
        echo '</tr>'.PHP_EOL;                     
        } // eo rows
echo '</table>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
#echo '<pre>'.print_r ($ccn_arr,true).print_r ($fcts_arr[0],true).print_r ($dtls_arr[0],true)   ; exit;
#
