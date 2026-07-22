<?php $scrpt_vrsn_dt  = 'fct_aeris_block.php|01|2020-11-02|';  # release 2012_lts
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
# -----------------   load general Aeris code
$scrpt          = 'fct_aeris_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return         = include_once $scrpt; 
#
if ($return == false) { return false;}  
#
$windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
         "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
#
if ( ($fcts_refresh + $aeris_fct_time) < time() )
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').'<!-- '.date('c',$aeris_fct_time).' --> </b>'; }
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($aeris_fct_time,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
#
echo '<div style="width: 310px; overflow: hidden;">
<table style="font-size: 10px; margin: 0px auto; height: 154px; text-align: center; ">'.PHP_EOL;
$cols           = 5;
$rw_cntnt       = array ('part','icnc','temp','wspd','idir','rain');
$rows           = count($rw_cntnt); 
$wunit          = '<small>'.lang($windunit).'</small>';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";
$norain         = '-';
$nouv           = '-';
$icn_prefix     = './pws_icons/';
$icn_post       = '.svg';
#
$rain           = false;

#
for ($n = 0; $n < $rows; $n++)
     {  $one_tr = '<tr>'.PHP_EOL;;
        $item   = $rw_cntnt[$n];
        for ($i = 0; $i < $cols; $i++)
            {   $one_tr.= '<td>'.PHP_EOL;
                $arr    = $parts[$i]; #echo '<!-- '.print_r($arr,true).' -->';
                if ($arr['isDay']) {$color = $clrwrm; } else {$color = $clrcld;}
                switch ($item){
                    case 'part': 
                        $one_tr.=  '<span style="color: '.$color.';">'.$arr['part'].'</span>'; 
                        break;
                    case 'icnc': 
                        $icon   = $icn_prefix.$arr['icon'].$icn_post;
                        $one_tr.=  '<img src="'.$icon.'" width="60" height="32" style="vertical-align: top;" alt="'.$arr['desc'].'" > ';
                        break;
                    case 'temp': 
                        $one_tr.=  '<span style="font-size: 14px; color: '.$color.';">'.$arr['temp'].'&deg;</span>';
                        break;
                    case 'wspd': 
                        $content= $arr['w_ft']; 
                        $one_tr.=  $content.' '.lang($windunit); 
                        break;
                    case 'idir': 
                        $compass = $arr['wdir'];
                        $one_tr.=  '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$compass.'"  style="vertical-align: bottom;"> ';
                        $one_tr.=  lang($compass);
                        break;
                    case 'rain': 
                        $change = (int)   $arr['r_ch']; 
                        $amount = (float) $arr['rain']; 
                        if (trim($change) == '' || $change == 0 || $amount == 0) 
                             {  $one_tr.= $norain; 
                                break; }
                        $rain   = true;
                        $unit   = lang($rainunit); 
                        $one_tr.=  $amount.'<small> '.$unit. ' '.$change.'%</small>';
                        break;              
                   default: $one_tr.= $n.'-'.$i.'-'.$item;
                             
                        } // eo switch
                $one_tr.='</td>'.PHP_EOL;
                } // eo cols
        $one_tr.= '</tr>'.PHP_EOL;        
        if ($item == 'rain' && $rain === false) 
             { $one_tr = ''; }
        echo $one_tr;      
        } // eo rows
echo '</table>
</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
