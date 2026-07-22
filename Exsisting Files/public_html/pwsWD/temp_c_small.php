<?php  $scrpt_vrsn_dt  = 'temp_c_small.php|01|2021-12-08|';  # PHP 8.1 + month text + missing history + font 12px + multi temps | release 2012_lts
#
$my_choice      = 'day';                // day
$my_choice      = 'multi';              // day / month / year
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
# ------------------------- translation of texts
$name_l = lang ('Today');
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
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
#                 temp_color
if (!function_exists ('temp_color') ) {
function temp_color ( $value)
     {  global $tempunit, $maxTemp, $temp_colors;
        if ($value === 'n/a' || $value === false) 
            {   return '<!-- no value '.$value.' -->'.PHP_EOL; return;}
        $tmp    = (float) $value; 
        if ($tempunit <> 'C')
             {  $tmp    =  5*( ($tmp -32)/9) ;}
        $tmp    = round ($tmp);  # 2021-12-08
        $n      = 32 + $tmp;
        if ($n < 0) {$n=0;}
        if ($n > $maxTemp)      
             {  $color  = $temp_colors[$maxTemp];}
        else {  $color  = $temp_colors[$n];}
        return $color;}
}
#                 temp_nr
if (!function_exists ('tempnr') ) {
function tempnr ($value)
     {  global $dec_tmp;
        return number_format ($value,$dec_tmp);}
}
if (!function_exists ('one_td') ) {
function one_td ($value,$arrow)
     {  global $dec_tmp, $color;
        return '<td style=" vertical-align:bottom; background-color: '.$color.'; color: black;"><span style="font-size: 12px; font-weight: 700;">'.$arrow.tempnr ($value).'&deg; </span></td>';
        }
}
if (!function_exists ('one_mnth_d') ) {
function one_mnth_d ($date)   #### 2021-04-12
     {  global $clockformat;  
        $value  = (int) $date;   #### 2021-04-12
        if ( $clockformat == '24')
             {  $text = date ('j ',$value) .lang( date ('M ',$value));}
        else {  $text = lang( date ('M ',$value)).date (', j',$value);}
        return $text; 
        }
}
# ===========================
if ($my_choice == 'multi') {
        #
        $time_ft        = str_replace (':s','',$timeFormat); 
        echo '<table style=" width: 100%; height: 60px; font-size: smaller;">';  #### 2021-01-26
        $high           = $hist['temp']['HghV'];
        $high_tm        = $hist['temp']['HghV_D'];
        $low            = $hist['temp']['LowV'];
        $low_tm         = $hist['temp']['LowV_D'];
        $txt_l          = '(';
        $txt_r          = ')';
        $arrowH         = '&uarr;';
        $arrowL         = '&darr;';  
        $remove_extras  = true;         //  (..) and arrows for temp removed, less clutter
#        $remove_extras  = false; 
        if ($txt_border === 'border: none;' || $txt_border === false || $remove_extras == true)        
             {  $txt_l  = $txt_r= '';
                $arrowL =$arrowH= ''; }        
        $from           = $temp_his;
        $to             = $weather['temp_units'];
        echo '<tr>'.PHP_EOL;
        $value  = convert_temp   ($high['today'],$from,$to);
        $color  = temp_color ( $value);
        echo one_td ($value,$arrowH).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ($time_ft,(int) $high_tm['today']).$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        echo '<td style=" vertical-align:bottom;"><b>'.$name_l.'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ($time_ft,(int) $low_tm['today']).$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        $value  = convert_temp   ($low['today'],$from,$to);
        $color  = temp_color ( $value);
        echo one_td ($value,$arrowL).PHP_EOL;
        echo '</tr>';

        echo '<tr>'.PHP_EOL;
        $text   = lang (date ('F') );  #### 2021-04-12  2021-05-23
        $value  = convert_temp   ($high['month'],$from,$to);
        $color  = temp_color ( $value);
        echo one_td ($value,$arrowH).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ('j',(int) $high_tm['month']) .$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        echo '<td style=" vertical-align:bottom;"><b>'.$text.'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ('j',(int) $low_tm['month']) .$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        $value  = convert_temp   ($low['month'],$from,$to);
        $color  = temp_color ( $value);
        echo one_td ($value,$arrowL).PHP_EOL;
        echo '</tr>';

        echo '<tr>'.PHP_EOL;
        $value  = convert_temp   ($high['year'],$from,$to);
        $color  = temp_color ( $value);
        
        echo one_td ($value,$arrowH).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.one_mnth_d($high_tm['year']).$txt_r.'</td>'.PHP_EOL;
        echo '<td style=" vertical-align:bottom;"><b>'.date ('Y').'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.one_mnth_d($low_tm['year']) .$txt_r.'</td>'.PHP_EOL;

        $value  = convert_temp   ($low['year'],$from,$to);
        $color  = temp_color ( $value);
        echo one_td ($value,$arrowL).PHP_EOL;
        echo '</tr>';

        echo '</table>';
        return; }

# ------------------     max temperature
$tmp    = $weather['temp_high'];
$color  = temp_color ($tmp);
$arrow  = '&uarr;';
if ($weather["temp_high_time"] === 'n/a') {$wtime = '';} else {$wtime = $weather["temp_high_time"];} 
$box_style      = 'width: 55px; height: 55px; margin: 2px; padding-top: 12px; background-color: transparent; border-width: 1px; color: black;';
#
echo '<div class="PWS_div_left PWS_round" style="'.$box_style.' float: left;  background-color: '.$color.';">'
        .'<span style="font-size: 14px; font-weight: 700;">'.$arrow.tempnr ($tmp).'&deg;</span>'
        .'<br /><small>'.$wtime.'</small>
</div>'.PHP_EOL;
# ------------------     min temperature
$tmp    = $weather['temp_low'];
$color  = temp_color ($tmp);
$arrow  = '&darr;';
if ($weather["temp_low_time"] === 'n/a') {$wtime = '';} else {$wtime = $weather["temp_low_time"];} 
echo '<div class="PWS_div_right PWS_round" style="'.$box_style.' float: right; background-color: '.$color.';">'
        .'<span style="font-size: 14px; font-weight: 700;">'.$arrow.tempnr ($tmp).'&deg;</span>'
        .'<br /><small>'.$wtime.'</small>
</div>'.PHP_EOL;
#
echo '<span class="large orange" style="display: block; padding-top: 15px;">'.$name_l.'</span>';

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}

