<?php  $scrpt_vrsn_dt  = 'wind_c_small.php|01|2021-06-10|';  # missing colours + month text + legibility + decimals + missing history + font 12px + multi periods | release 2012_lts
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
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#
$name_l = lang ('Today');
$month_l= lang ('Month');
$year_l = lang ('Month');
#
$bft_clrs = array(
	"lightgrey", 
	"lightgrey", "lightgrey", "lightgrey", "lightgrey", "lightgrey", 
	 "#FFFF53",  "#F46E07",   "#F00008",   "#F36A6A",   "#6D6F04", 
	 "#640071",  "#650003");	
#
$fnt_clrs = array(
	"black", 
	"black",    "black",   "black",   "black",   "black", 
	"black",    "black",   "black",   "black",   "silver", 
	"silver",   "silver");

$lvl_bft= array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
#
if (!function_exists ("wind_color") ){
function wind_color ( $value)  
     {  global $windunit,$bft_clrs, $toKnots , $fnt_clrs, $lvl_bft;       
        $windspd        = $value;
        if ($value === 'n/a' || $value === false) 
            {   $value   = 0;}
        $windspd        = (float) $value;    
        $spd_knts = round ($windspd * $toKnots , 1);  #echo $spd_knts; exit;
        foreach ($lvl_bft as $key => $n)
             {  if ($spd_knts > $n) {continue;}  # $key=12; # for test 
                break;}
        $clr    = $bft_clrs[$key];
        $fnt    = $fnt_clrs[$key];
        return array($clr,$fnt);}
} 
if (!function_exists ('sml_td_wnd') ) {
function sml_td_wnd ($value,$arrow='')
     {  global $dec_wnd;
        $arr    = wind_color ( $value);
        return '<td style=" vertical-align:bottom; background-color: '.$arr[0]
                .'; color: '.$arr[1].';"><span style="font-size: 12px; font-weight: 700;">'
                .round ($value,$dec_wnd)  
                .'</span></td>';
        }
}
if (!function_exists ('one_mnth_d') ) {
function one_mnth_d ($date)      #### 2021-04-12
     {  global $clockformat;
        $value  = (int) $date;   #### 2021-04-12
        if ( $clockformat == '24')
             {  $text = date ('j ',$value) .lang( date ('M ',$value));}
        else {  $text = lang( date ('M ',$value)).date (', j',$value);}
        return $text; }
}
# ===========================
if ($my_choice == 'multi') {
        #
        $time_ft        = str_replace (':s','',$timeFormat); 
        echo '<table style=" width: 100%; height: 60px; font-size: smaller;">';  #### 2021-01-26
        $wind           = $hist['wind']['HghV'];
        $wind_tm        = $hist['wind']['HghV_D'];
        $gust           = $hist['gust']['HghV'];
        $gust_tm        = $hist['gust']['HghV_D'];
# test values ----------------------------------
#$windunit='kts';
#$wind_his='kts';
#$wind['today']  = 1;  
#$wind['month']  = 5;
#$wind['year']   = 12;
#$gust['today']  = 42;
#$gust['month']  = 49;
#$gust['year']   = 57;
# test values ----------------------------------
        $txt_l          = '(';
        $txt_r          = ')';
        $remove_extras  = true;         //  (..) and arrows for temp removed, less clutter
#        $remove_extras  = false; 
        if ($txt_border === 'border: none;' || $txt_border === false || $remove_extras == true)        
             {  $txt_l  = $txt_r= ''; }
        $from           = $wind_his;
        $to             = $weather['wind_units'];
        echo '<tr>'.PHP_EOL;
        $value  = convert_speed   ($wind['today'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ($time_ft,(int) $wind_tm['today']).$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        echo '<td style=" vertical-align:bottom;"><b>'.$name_l.'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ($time_ft,(int) $gust_tm['today']).$txt_r.'</td>'.PHP_EOL;  #### 2021-04-12
        $value  = convert_speed   ($gust['today'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '</tr>';

        echo '<tr>'.PHP_EOL;
        $text   = lang (date ('F') );   #### 2021-04-12   2021-05-23
        $value  = convert_speed   ($wind['month'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ('j',(int) $wind_tm['month']) .$txt_r.'</td>'.PHP_EOL;   #### 2021-04-12
        echo '<td style=" vertical-align:bottom;"><b>'.$text.'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.date ('j',(int) $gust_tm['month']) .$txt_r.'</td>'.PHP_EOL;   #### 2021-04-12
        $value  = convert_speed   ($gust['month'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '</tr>';

        echo '<tr>'.PHP_EOL;
        $value  = convert_speed   ($wind['year'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '<td style=" vertical-align:bottom;">'.$txt_l.one_mnth_d($wind_tm['year']).$txt_r.'</td>'.PHP_EOL;
        echo '<td style=" vertical-align:bottom;"><b>'.date ('Y').'</b></td>';
        echo '<td style=" vertical-align:bottom;">'.$txt_l.one_mnth_d($gust_tm['year']).$txt_r.'</td>'.PHP_EOL;
        $value  = convert_speed   ($gust['year'],$from,$to);
        echo sml_td_wnd ($value).PHP_EOL;
        echo '</tr>';

        echo '</table>';
        return; }

# test values ----------------------------------
#$windunit='kts';
#$weather['wind_speed_max'] = 65;  
#$weather['wind_gust_speed_max']  = 100;
# ------------------     max wind
$wnd    = (float) $weather['wind_speed_max']; 
if ($wnd > 99) {$dec = 0;} else {$dec = $dec_wnd;}
$arr    = wind_color ($wnd); # print_r($arr);
$box_style      = 'width: 55px; height: 55px; margin: 2px; padding-top: 8px; border-width: 1px;';
#
echo '<div class="PWS_div_left PWS_round" style="'.$box_style.' float: left; background-color: '.$arr[0].'; color:'.$arr[1].';">'
        .'<span style="font-size: 16px; ">'.number_format ($wnd,$dec).'</span>'
        .'<br />'.$weather["wind_speed_max_time"].'
</div>'.PHP_EOL;
# ------------------     max gust
if ((string)$weather['wind_gust_speed_max'] <> 'n/a')
     {  $wnd  = (float) $weather['wind_gust_speed_max'];
        if ($wnd > 99) {$dec = 0;} else {$dec = $dec_wnd;}
        $arr    = wind_color ($wnd); #print_r($arr);
        echo '<div class="PWS_div_left PWS_round" style="'.$box_style.' float: right; background-color: '.$arr[0].'; color:'.$arr[1].';">'
                .'<span style="font-size: 16px; ">'.number_format ($wnd,$dec_wnd).'</span>'
                .'<br />'.$weather["wind_gust_speed_max_time"].'
        </div>'.PHP_EOL;}
#
echo '<span class="large orange" style="display: block; padding-top: 15px;">'.$name_l.'</span>';

if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
