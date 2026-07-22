<?php  $scrpt_vrsn_dt  = 'moon_c_block.php|01|2022-11-22|';  # meteors peak re-added PHP 8.1 + 24 d/l + extra room for text |release 2012_lts
#
# -------------------------------  user settings
#
$use_clear_moon = true;                // true = use photo of lighted moon  || false use yellow color for light
$moon_image     = './img/moon.png';     // location of .png or .svg
$tilt_image     = true;                 // slight tilt so the image looks more natural
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
#
/*
# ----------------------- general meteors script
$scrpt          = 'meteors_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
if ($meteor_link_blck <> '') {$meteor_default  = $meteor_link_blck;}  */

#
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
# -------------------------------- test settings
#$lat = (float) "78.7836";
#$lat =  (float) "50.7836";
#$lon = (float) "16.5278";
#$lon = (float) "4.5278";
#
#                           
$orange ='orange';  // color for large text in the middle
#$orange = ''; # default text is black or silver 
#
if (!isset ($KISS)) 
     {  $KISS = false; }  // keep it simple graphs
$my_KISS        = $KISS;
#$my_KISS        = true;       // to set for this block only.
#
if ($my_KISS == true)         { $use_clear_moon = false;}
if ($use_clear_moon == false) { $moon_image = '';}
#
$clr_light      = '#ff8841';
$clr_dark       = 'dimgrey';
$clr_sun_up     = '#e8c400'; #'#f6ff00';
$now            = time();
$date           = date ('Y-m-d');
#
# ------------------------- translation of texts
$ltxt_tmrrw     = lang('Tomorrow');
$ltxt_td        = lang('Today');
$ltxt_mnrs      = lang('Moonrise');
$ltxt_mnst      = lang('Moonset');
$ltxt_nxtfull   = lang('Next Full Moon');
$ltxt_nxtnew    = lang('Next New Moon');
$ltxt_luninance = lang('Luminance'); 
#-----------------------------------------------
#                                    date format
#-----------------------------------------------
if ($clockformat == '24') 
     {  $date_time_frmt = 'D j M';}
else {  $date_time_frmt = 'D M j';}
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
#               PWS_moon_info
#-----------------------------------------------
if  (function_exists ('PWS_moon_info') ) {echo 'Moon info is already displayed'; return;}
if (!function_exists ('PWS_moon_info') ) {
function PWS_moon_info ()
     {  global $lat,$lon;
        include './others/MoonPhase.php';
        include './others/moon.php';
        $now    = time();
        list ($month, $day,$year) = explode ('|', date ('n|j|Y', $now));
        $object = (array) Moon::calculateMoonTimes($month, $day, $year, $lat, $lon);
        $result['moonrise']     = $object['moonrise'];
        $result['moonset']      = $object['moonset'];
        if (date ('Gis',$result['moonrise']) == '000000') {$result['moonrise'] = 0;}
        if (date ('Gis',$result['moonset']) == '000000')  {$result['moonset'] = 0;}
        
#
        $tomorrow       = $now + 24*3600;
        list ($month, $day,$year) = explode ('|', date ('n|j|Y',$tomorrow));  
        $object = (array) Moon::calculateMoonTimes($month, $day, $year, $lat, $lon);
        $result['moonrise2']    = $object['moonrise'];
        $result['moonset2']     = $object['moonset'];
        if (date ('Gis',$result['moonrise2']) == '000000') {$result['moonrise2'] = 0;}
        if (date ('Gis',$result['moonset2']) == '000000')  {$result['moonset2'] = 0;}
        
        $result['now']          = time();      
        return $result;}
}
#-----------------------------------------------
#               trans_long_date
#-----------------------------------------------
if (!function_exists ('trans_long_date') ) {
function trans_long_date ($date)
     {  $from   = array ( 
                'Apr ','Aug ','Dec ','Feb ','Jan ','Jul ','Jun ','Mar ','May ','Nov ','Oct ','Sep ',
                'April','August','December','February','January','July','June','March','May','November','October','September',
                'Mon ','Tue ','Wed ','Thu ','Fri ','Sat ','Sun ',
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        foreach ($from  as $txt) {$to_dates[] = lang($txt).' ';} # echo '-'.$txt.'-'.lang($txt).PHP_EOL;

        return str_replace ($from, $to_dates, $date.' ');}    #### 2018-07-18
}      
#
$arr    = PWS_moon_info ();
#
echo '<!-- '.PHP_EOL.print_r ($arr,true).' -->'.PHP_EOL; 
#
$moonrise       = $arr['moonrise'];
$moonrise_nxt   = ''; #'<br />'.$ltxt_td;
if ($moonrise < $now) 
     {  $moonrise       = $arr['moonrise2']; 
        $moonrise_nxt   = '<br />'.$ltxt_tmrrw;}
$moonrise_time  = set_my_time($moonrise , true);
#
$moonset        = $arr['moonset'];
$moonset_nxt    = '<br />'.$ltxt_td;
if ($moonset < $now) 
     {  $moonset        = $arr['moonset2']; 
        $moonset_nxt    = '<br />'.$ltxt_tmrrw;}
$moonset_time   = set_my_time($moonset , true );
#
$fullmoon       = (int) $arr['full_moon'] ; # 2021-12-04 var_dump($fullmoon); exit;
$fullmoon_date  = date ('Y-m-d',$fullmoon);
if ($date > $fullmoon_date) 
     {  $fullmoon       = (int) $arr['next_full_moon'];}   # 2021-12-04
$fullmoon_time  = trans_long_date(date($date_time_frmt,$fullmoon));  # date($dateFormat,$fullmoon );
if ($date == $fullmoon_date) 
     { $fullmoon_time   = $ltxt_td;}

$newmoon       = (int) $arr['new_moon'] ; # 2021-12-04
$newmoon_date   = date ('Y-m-d',$newmoon);
if ($now > $arr['new_moon']) 
     {  $newmoon       = (int) $arr['next_new_moon'];} # 2021-12-04
$newmoon_time  = trans_long_date(date($date_time_frmt,$newmoon));  # date($dateFormat,$newmoon );
if ($date == $newmoon_date) 
     { $newmoon_date    = $ltxt_td;}
#
$ltxt_phase     = lang($arr['phase_name']); 
#
if ($arr['illumination'] < 0.01 || $arr['illumination'] > 0.99) 
     {  $luminance      = round(100*$arr['illumination'],1);}
else {  $luminance      = round(100*$arr['illumination'],0);}
#
# ---------------  generate html
#
# ---------------- the date time
echo '<div class="PWS_ol_time"><b class="PWS_online"> ' .$online.set_my_time_lng(time(),true).' </b></div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column  border-right-color:yellow;  height: 40px;
echo '<!-- left values -->
<div class="PWS_left" style="height: 125px;">
<!-- moonrise -->'.PHP_EOL;
if ($moonrise <> 0)
     {  echo '<div class="PWS_div_left" style="border-right-color: '.$clr_light.'; height: 40px;">'
        .$ltxt_mnrs.$moonrise_nxt
        .'<br /><b>'.$moonrise_time.'</b>'
        .'</div>'.PHP_EOL;}
echo '<div class="PWS_div_left" style="border-right-color: '.$clr_sun_up.';">'
        .$ltxt_nxtfull
        .'<br /><b>'.$fullmoon_time.'</b>'
        .'</div>
</div>'.PHP_EOL;       
#
# ---------------- middle column
# ------- test values
#$arr['phase']        = .6;
#$arr['illumination'] = 0.9;   $luminance      = round(100*$arr['illumination']);
# ------- test values

# -----     middle column       // comment base on the Northern part of the earth
#
$lighted= round($arr['illumination'],3); // percent (0.0 - 1.0)
if ($lighted > 0.0  && $lighted < 0.04 ) { $lighted = 0.04;} // to make very small lighted part it visible
if ($lighted > 0.95 && $lighted < 1.0 ) {  $lighted = 0.96;} // same for remaining dark part.
#
if ($use_clear_moon <> true)    // normal photo image or VERY bright photo image 
      { $color_light    = 'rgba(255, 255, 0, 0.70);';   // yellow used for lighted part
        $color_dark     = 'rgba(10, 10, 10, 0.40);'; }  // 20% black on image for shadow part
else {  $color_light    = 'transparent';                // when moon is VERY bright by itself           
        $color_dark     = 'rgba(0, 0, 0, 0.60);';  }    // 60% black on image for shadow part
#
if ($arr['phase'] < 0.5)        // new moon to full moon (0 - 0.5)
     {  $color1 = $color_light; 
        $color2 = $color_dark;
        $pos    = 350 -  $lighted * 400;}    
else {  $color2 = $color_light; 
        $color1 = $color_dark;
        $pos    = $lighted * 400 - 50;}      
# 
if ($tilt_image == true && $my_KISS == false) {$tilt = 20; } else { $tilt = 0;}       
#
if ( $lat < 0)                  // rotate for the south part of the earth
     {  $south = 'transform: rotate('.(180 + $tilt).'deg);'; } // tilt is 20 degrees to look more normal
else {  $south = 'transform: rotate('. $tilt.'deg);'; }
#
if ($current_theme == 'dark' || $use_clear_moon == true)
     {  $tcolor         = 'white';
        $shadow         = 'text-shadow: 2px 2px 10px black;'; }
else {  $tcolor         = 'black';
        $shadow         = 'text-shadow: 2px 2px 10px yellow;' ; }  
#
if (isset ($my_KISS) && $my_KISS == false ) 
     {  $size = 110; 
        $class= 'large';}
else {  $size = 100;
        $class= '';} 
#
echo '<!-- middle texts -->
<div class="PWS_middle" style="height: 125px;">
    <div style="width: 130px;  margin: 0px auto;">
     <div style="width: '.$size.'px;  margin: 0 auto; margin-top: 10px; ';
if ($moon_image <> '') { echo '
        background-image: url('.$moon_image.'); background-size: '.$size.'px '.$size.'px; background-repeat: no-repeat;';}
echo '">'.PHP_EOL;

if (isset ($my_KISS) && $my_KISS == true ) 
     {  $rad    = 62; 
        $lnght  = 2 * $rad * M_PI;
        $from   = $lighted * $lnght;
        $to     = $lnght; 
        $rotate = -0.5 *  $lighted * 360;
        if ($arr['phase'] > 0.5) {$rotate = $rotate + 180;}
        echo '     <svg width="'.$size.'" height="'.$size.'" viewBox="0 0 130 130"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  style="'.$south.'">
        <circle id="sunBack"  r="'.$rad.'" cx="65" cy="65" style=" fill:none; stroke-width: 4px; stroke: grey; " />   
        <circle id="sunRing"  r="'.$rad.'" cx="65" cy="65" style=" fill:none; stroke-width: 6px; stroke: yellow;
                stroke-dasharray:'.$from.' '.$to.';" 
                transform = "rotate('.$rotate.'  65 65 )"/>
     </svg>
        <div style="position: absolute; top: 55px; width: '.$size.'px;">
        <div style=" margin: 0 auto; font-size: 10px;">
          <b style="font-size: 18px;" >'.$luminance.'%</b>        
          <br /><b style="color: '.$orange.';">'.$ltxt_luninance.'</b>
          <hr style="margin: 1px 4px 4px 3px;">
          <b  style="color: '.$orange.';">'.$ltxt_phase.'</b>
        </div>
        </div>'.PHP_EOL;}

else {  echo '    <svg width="'.$size.'" height="'.$size.'" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="'.$south.'">
        <path d="M 150 0 C '.$pos.' 10 '.$pos. ' 290 150 300  350 290 350 10 150 0"  style="paint-order: fill;  fill: '.$color1.';" />
        <path d="M 150 0 C '.$pos.' 10 '.$pos. ' 290 150 300  -50 290 -50 10 150 0"  style="paint-order: fill;  fill: '.$color2.'; " /> 
     </svg>
        <div style="position: absolute; top: 60px; width: '.$size.'px;">
        <div style=" margin: 0 auto; color: '.$tcolor.'; '.$shadow.' ">
          <b class="large" >'.$luminance.'%</b>        
          <br /><b >'.$ltxt_luninance.'</b>
          <hr style="margin: 1px; margin-bottom: 4px;">
          <b >'.$ltxt_phase.'</b>
        </div>
        </div>'.PHP_EOL;}   
echo '</div>
</div>'.PHP_EOL;

#
/*
if ($meteor_default <> '' ) # only displayed if there is information
     {  echo '
        <svg xmlns="http://www.w3.org/2000/svg" width="12px" height="12px" viewBox="0 0 16 16">
            <path fill="currentcolor" d="M0 0l14.527 13.615s.274.382-.081.764c-.355.382-.82.055-.82.055L0 0zm4.315 1.364l11.277 10.368s.274.382-.081.764c-.355.382-.82.055-.82.055L4.315 1.364zm-3.032 2.92l11.278 10.368s.273.382-.082.764c-.355.382-.819.054-.819.054L1.283 4.284zm6.679-1.747l7.88 7.244s.19.267-.058.534-.572.038-.572.038l-7.25-7.816zm-5.68 5.13l7.88 7.244s.19.266-.058.533-.572.038-.572.038l-7.25-7.815zm9.406-3.438l3.597 3.285s.094.125-.029.25c-.122.125-.283.018-.283.018L11.688 4.23zm-7.592 7.04l3.597 3.285s.095.125-.028.25-.283.018-.283.018l-3.286-3.553z"/>
        </svg> '
        .$meteor_default. PHP_EOL;}
else {  echo '<!-- no meteor showers today -->'.PHP_EOL;} */
echo '   </div>  
<!-- eo middle -->'.PHP_EOL;
#
# ---------------- right column
echo '<!-- right area  new and full moon -->
<div class="PWS_right" style="height: 125px;">'.PHP_EOL;
if ($moonset <> 0)
     {  echo '<div class="PWS_div_right" style="border-left-color: '.$clr_light.'; height: 40px;">'
        .$ltxt_mnst.$moonset_nxt.'<br /><b >'
        .$moonset_time.'</b>'
        .'</div>'.PHP_EOL;}
echo '<div class="PWS_div_right" style="">'
        .$ltxt_nxtnew.'<br /><b >'
        .$newmoon_time.'</b>'
        .'</div>
</div><!-- eo right area -->'.PHP_EOL;
#
$mtr_links      = array();
$mtr_links['Quadrantids']       = 'https://en.wikipedia.org/wiki/Quadrantids';
$mtr_links['Lyrids']            = 'https://en.wikipedia.org/wiki/Lyrids';
$mtr_links['eta Aquariids']     = 'https://en.wikipedia.org/wiki/Eta_Aquariids';
$mtr_links['Delta Aquariids']   = 'https://en.wikipedia.org/wiki/Southern_Delta_Aquariids';
$mtr_links['alpha Capricornids']= 'https://en.wikipedia.org/wiki/Alpha_Capricornids';
$mtr_links['Perseids']          = 'https://en.wikipedia.org/wiki/Perseids';
$mtr_links['Southern Taurids']  = 'https://en.wikipedia.org/wiki/Southern_Taurids';
$mtr_links['Orionids']          = 'https://en.wikipedia.org/wiki/Orionids';
$mtr_links['Northern Taurids']  = 'https://en.wikipedia.org/wiki/Northern_Taurids';
$mtr_links['Leonids']           = 'https://en.wikipedia.org/wiki/Leonids';
$mtr_links['Geminids']          = 'https://en.wikipedia.org/wiki/Geminids';
$mtr_links['Ursids']            = 'https://en.wikipedia.org/wiki/Ursids';
#
$meteor_default = ''; #lang('No Meteor Showers');
$mtrs[] = array('from' => mktime(0, 0, 0, 1, 3),	'title' => 'Quadrantids peak',    'to' => mktime(23, 59, 59, 1, 4),      'link' => 'Quadrantids');
$mtrs[] = array('from' => mktime(0, 0, 0, 1, 5),	'title' => 'Quadrantids',         'to' => mktime(23, 59, 59, 1, 12),     'link' => 'Quadrantids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 28,2019),	'title' => 'Quadrantids',         'to' => mktime(23, 59, 59, 1, 2,2020), 'link' => 'Quadrantids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 28,2020),	'title' => 'Quadrantids',         'to' => mktime(23, 59, 59, 1, 2,2021), 'link' => 'Quadrantids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 28,2021),	'title' => 'Quadrantids',         'to' => mktime(23, 59, 59, 1, 2,2022), 'link' => 'Quadrantids');
$mtrs[] = array('from' => mktime(0, 0, 0, 4, 9),	'title' => 'Lyrids',              'to' => mktime(23, 59, 59, 4, 20),     'link' => 'Lyrids');
$mtrs[] = array('from' => mktime(0, 0, 0, 4, 21),	'title' => 'Lyrids peak',         'to' => mktime(23, 59, 59, 4, 22),     'link' => 'Lyrids');
$mtrs[] = array('from' => mktime(0, 0, 0, 4, 23),	'title' => 'Lyrids',              'to' => mktime(23, 59, 59, 4, 25),     'link' => 'Lyrids');
$mtrs[] = array('from' => mktime(0, 0, 0, 5, 4),	'title' => 'ETA Aquarids',        'to' => mktime(23, 59, 59, 5, 7),      'link' => 'eta Aquariids');
$mtrs[] = array('from' => mktime(0, 0, 0, 7, 21),	'title' => 'Delta Aquarids',      'to' => mktime(23, 59, 59, 7, 23),     'link' => 'Delta Aquariids');
$mtrs[] = array('from' => mktime(0, 0, 0, 8, 1),	'title' => 'Perseids ',           'to' => mktime(23, 59, 59, 8, 10),     'link' => 'Perseids');
$mtrs[] = array('from' => mktime(0, 0, 0, 8, 11),	'title' => 'Perseids peak',       'to' => mktime(23, 59, 59, 8, 13),     'link' => 'Perseids');
$mtrs[] = array('from' => mktime(0, 0, 0, 8, 14),	'title' => 'Perseids passed',     'to' => mktime(23, 59, 59, 8, 18),     'link' => 'Perseids');
$mtrs[] = array('from' => mktime(0, 0, 0, 10, 7),	'title' => 'Draconids peak',      'to' => mktime(23, 59, 59, 10, 7),     'link' => '');
$mtrs[] = array('from' => mktime(0, 0, 0, 10, 20),	'title' => 'Orionids peak',       'to' => mktime(23, 59, 59, 10, 21),    'link' => 'Orionids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 4),	'title' => 'South Taurids peak',  'to' => mktime(23, 59, 59, 11, 5),     'link' => 'Southern Taurids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 11),	'title' => 'North Taurids peak',  'to' => mktime(23, 59, 59, 11, 11),    'link' => 'Northern Taurids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 13),	'title' => 'Leonids ',            'to' => mktime(23, 59, 59, 11, 16),    'link' => 'Leonids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 17),	'title' => 'Leonids peak',        'to' => mktime(23, 59, 59, 11, 18),    'link' => 'Leonids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 19),	'title' => 'Leonids ',            'to' => mktime(23, 59, 59, 11, 29),    'link' => 'Leonids');
$mtrs[] = array('from' => mktime(0, 0, 0, 11, 30),	'title' => 'Geminids ',           'to' => mktime(23, 59, 59, 12, 12),    'link' => 'Geminids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 13),	'title' => 'Geminids peak',       'to' => mktime(23, 59, 59, 12, 14),    'link' => 'Geminids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 17),	'title' => 'Ursids ',             'to' => mktime(23, 59, 59, 12, 20),    'link' => 'Ursids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 21),	'title' => 'Ursids peak',         'to' => mktime(23, 59, 59, 12, 22),    'link' => 'Ursids');
$mtrs[] = array('from' => mktime(0, 0, 0, 12, 23),	'title' => 'Ursids ',             'to' => mktime(23, 59, 59, 12, 25),    'link' => 'Ursids');
$now       = time();
$meteorOP  = false;
foreach ($mtrs as $arr_mtr) 
     {  if ($arr_mtr['from'] <= $now &&  $now  <= $arr_mtr['to'] ) 
             {  $meteorOP       = true;
                $meteor_default = $arr_mtr['title'];
                $link           = $arr_mtr['link'];
                if ($link<> '') 
                     {  $meteor_default = '<a target="_blank" href="'.$mtr_links[$link].'"><u class="orange">'.$meteor_default.'</u></a>';}
                break;}
        };

if ($meteor_default <> '' ) # only displayed if there is information
     {  echo '
        <svg xmlns="http://www.w3.org/2000/svg" width="12px" height="12px" viewBox="0 0 16 16">
            <path fill="currentcolor" d="M0 0l14.527 13.615s.274.382-.081.764c-.355.382-.82.055-.82.055L0 0zm4.315 1.364l11.277 10.368s.274.382-.081.764c-.355.382-.82.055-.82.055L4.315 1.364zm-3.032 2.92l11.278 10.368s.273.382-.082.764c-.355.382-.819.054-.819.054L1.283 4.284zm6.679-1.747l7.88 7.244s.19.267-.058.534-.572.038-.572.038l-7.25-7.816zm-5.68 5.13l7.88 7.244s.19.266-.058.533-.572.038-.572.038l-7.25-7.815zm9.406-3.438l3.597 3.285s.094.125-.029.25c-.122.125-.283.018-.283.018L11.688 4.23zm-7.592 7.04l3.597 3.285s.095.125-.028.25-.283.018-.283.018l-3.286-3.553z"/>
        </svg> '
        .$meteor_default. PHP_EOL;}
else {  echo '<!-- no meteor showers today -->'.PHP_EOL;} 
# ----------------   end of html
echo '</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}       