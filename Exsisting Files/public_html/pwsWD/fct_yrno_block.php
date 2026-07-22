<?php $scrpt_vrsn_dt  = 'fct_yrno_block.php|01|2022-03-28|';  # overflow +  PHP 8.1 | release 2012_lts
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
$scrpt          = 'fct_yrno_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return = include_once $scrpt; 
#
if ($return == false) {echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst=''; return false;}  
#
if ( (3600 + $yrno_fct_time) < time() )
     {  $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').'<!-- '.date('c',$yrno_fct_time).' --> </b>'; }
else {  $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($yrno_fct_time,true).' </b>' ;}
echo '<div class="PWS_ol_time">'.$online_txt.'</div>'.PHP_EOL;
#
echo '<div style="width: 314px; overflow: hidden;">
<table style="font-size: 10px; margin: 0px auto; min-width: 314px; border-spacing: 1px; border-collapse: collapse; height: 154px; text-align: center; ">'.PHP_EOL;
$cols           = 5;  // number of day-parts to use, default 5. Lower if truncation of day-names occur # 2021-12-31
$rw_cntnt       = array ('unix','symbol_code','air_temperature','wind_speed','wind_from_direction','precipitation_amount');
$rows           = count($rw_cntnt); 
$wunit          = '<small>'.lang($windunit).'</small>';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";
$norain         = '-';
#
$rain           = false;        # die ('fct_yrno_block '.__LINE__);
$dayp_txts      = array ( lang('Night'), lang('Morning') ,lang('Afternoon'),lang( 'Evening'));
#
echo '<!-- '.print_r($frct_mtn_dp[0],true).' -->';              #### 2022-07-22
#if (!array_key_exists('symbol_code',$frct_mtn_dp[0]) || trim($frct_mtn_dp[0]['symbol_code']) == '') 
#     {  array_shift ( $frct_mtn_dp );
#        echo '<!-- new first item '.print_r($frct_mtn_dp[0],true).' -->'; }    #### 2022-07-22  
for ($n = 0; $n < $rows; $n++)
     {  $one_tr = '<tr>'.PHP_EOL;
        $item   = $rw_cntnt[$n];
        for ($i = 0; $i < $cols; $i++)
            {   $one_tr        .= '<td>'.PHP_EOL;
                $arr            = $frct_mtn_dp[$i]; #echo '<!-- '.print_r($arr,true).' -->'; 
                $tm_strt        = $arr['unix'];
                $hr_start       = date('H',$tm_strt);
                $hr_end         = date('H',$tm_strt + 6*3600);
                $result         = (int) ($hr_start / 6);
                if ( $result == 0 || $result == 3 )
                     {  $colorx = $clrcld;}  else {  $colorx = $clrwrm;}
                $text = $dayp_txts[$result];
                $content= $arr[$item];
                switch ($item){
                    case 'unix': 
                        $wday   = lang(date('l',$arr['unix'])).'<br>'.$text;
                        $one_tr.=  '<span style="color: '.$colorx.';">'.$wday.'</span>';
                        break;
                    case 'symbol_code': 
                        $content= str_replace ('polartwilight','day',$content);
                        $icon   = $icn_prefix.$mtn_icons[$content]['svg'].$icn_post;
                        $text   = $mtn_icons[$content]['txt'];
                        $one_tr.=  '<img src="'.$icon.'" width="60" height="32" style="vertical-align: top;" alt="'.$text.'" > ';
                        break;
                    case 'air_temperature': 
                        $max    = round(convert_temp  ($arr['air_temperature_max'], 'c', $tempunit));
                        $min    = round(convert_temp  ($arr['air_temperature_min'], 'c', $tempunit));
                        $one_tr.=  '<span style="font-size: 12px; color: '.$colorx.';">'.$min;
                        $one_tr.=  '</span> - <span style="font-size: 12px; color: '.$colorx.';">'.$max.'&deg;</span>';
                        break;
                    case 'wind_speed': 
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  $one_tr.= $norain;  }
                        else {  $amount         = convert_speed ($amount, 'm/s', $windunit);
                                $spd_knts       = round ($amount*$toKnots , 1);
                                $one_tr.= $amount.'<small> '.lang($windunit).'</small>';}
                        break;
                    case 'wind_from_direction': 
                        $compass= windlabel ($content); # 2021-12-08
                        $one_tr.=   '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$content.'"  style="vertical-align: bottom;"> ';
                        $one_tr.=   lang($compass);
                        break;
                    case 'precipitation_amount': 
                        $amount = (float) $content; 
                        if ($amount == 0) 
                             {  $one_tr.=  $norain; }
                        else {  $rain   = true;
                                $amount = convert_precip ($amount, 'mm', $rainunit);
                                $one_tr.=   $amount.'<small> '.lang($rainunit).'</small>';}                                         
                        break;              
                   default: $one_tr.= $n.'-'.$i.'-'.$item;        
                        } // eo switch
                $one_tr.='</td>'.PHP_EOL; 
                } // eo cols
        $one_tr.= '</tr>'.PHP_EOL;        
        if ($item == 'precipitation_amount' && $rain === false) 
             { $one_tr = ''; }
        echo $one_tr;      
        } // eo rows
echo '</table>
</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
#
