<?php  $scrpt_vrsn_dt  = 'fct_wxsim_block.php|01|2020-11-02|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$allow_age_hrs  = 6;                    // number of hours wxsim data will considered to old
$cols           = 4;                    // nr of forecasts to show for PWS_Dashboard
$rw_cntnt       = array ('part','icnc','temp','wspd','idir','rain');  // what data to show
$norain         = '-';                  // what to show for no-rain
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
# load general WXSIM code which loads the fct also
#-----------------------------------------------
$scrpt          = './fct_wxsim_shared.php'; # echo $SITE['defaultlang']; exit;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
date_default_timezone_set($TZ); // reset timezone back to correct value if parser not correctly set
#---------------------  check if fct is complete
if (!isset ($arr_pp) || count ($arr_pp) < 6 ) 
     {  echo '<b style="color: red;"><small>Problem ('.__LINE__.'): wxsim file not ready, check easyweather AND plaintext-parser settings</small></b>'; return;}
#
#-----------------------  check if fct is recent
$fct_time       = $arr_pp[0]['updated']; 
list ($none,$datetime) = explode (',',$fct_time.',');
$fct_time       = strtotime($datetime); 
if (time() - $fct_time > 3600 * (int) $allow_age_hrs)
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').'<!-- '.$arr_pp[0]['updated'].'  -->'.' </b>';}
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($fct_time,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.
'</div>'.PHP_EOL;
#
#
#---------------------    first part of the html
echo '<table style="font-size: 10px; width: 100%; margin: 0px auto; height: 100%; text-align: center; overflow: hidden;">'.PHP_EOL;
$rows           = count($rw_cntnt);
$wunit          = $arr_pp[0]['wunt'];
$wunit          = '<small>'.lang($wunit).'</small>';    // unit text for wind
#
# -------------------------- print each forecast
for ($n = 0; $n < $rows; $n++)  // row data
     {  echo '<tr>'.PHP_EOL;
        for ($i = 0; $i < $cols; $i++)  // of each forecast
            {   echo '<td>';
                $comtent        = $rw_cntnt[$n];
                switch ($comtent){
                    case 'part': 
                        if ($arr_pp[$i]['tphl'] <> 'blue') {$color = "#FF7C39"; } else {$color = "#01A4B4"; }
                        $text   = $arr_pp[$i]['part']; #str_replace(' ','<br />',$arr_pp[$i]['part']);
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icnc': 
                        #echo $arr_pp[$i]['icnc'];
                        echo '<img src="'.$arr_pp[$i]['icDS'].'" width="60" height="32" alt="'.$arr_pp[$i]['icDS'].'" style="vertical-align: top;"> ';
                        break;
                    case 'temp': 
                        if ($arr_pp[$i]['tphl'] <> 'blue') {$color = "#FF7C39"; } else {$color = "#01A4B4"; }
                        echo '<span style="font-size: 20px; color: '.$color.';">'.$arr_pp[$i]['temp'].'<small>&deg;</small></span>'; 
                        break;
                    case 'wspd': 
                        echo $arr_pp[$i]['wspd'].$wunit; 
                        break;
                    case 'idir': 
                        $dir    = $arr_pp[$i]['idir'];
                        echo '<img src="img/windicons/'.$dir.'.svg" width="20" height="20" alt="'.$dir.'" style="vertical-align: bottom;"> ';
                        echo lang($dir);
                        break;
                    case 'rain': 
                        $rain   = $arr_pp[$i]['rain'];
                        $unit   = lang($arr_pp[$i]['runt']);
                        if (trim($rain) == '') {echo $norain; break;}
                        echo $rainsvg.' '.$rain.'<small>'.$unit.'</small>';
                        $pop    = $arr_pp[$i]['popp'];
                        if (trim($pop) <> '') echo ' <small>'.$pop.'%</small>';
                        break;              
                    default: echo $n.'-'.$i.'-'.$comtent;
                }              
                echo '</td>';
                } // eo coloms
        echo '</tr>'.PHP_EOL;
} // eo rows
echo '</table>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}


