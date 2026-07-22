<?php  $scrpt_vrsn_dt  = 'PWS_index2.php|01|2023-09-09|'; #  phone width 100% + unit + bot problem + cookie + hist + stripmost | link + refresh small +X fro frames + fadeout +  header inside + clr bg + before txt + missing popup +  more blocks |release 2012_lts
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
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);} 
# -------------------save list of loaded scrips;
$stck_lst       = basename(__FILE__).'('.__LINE__.')  loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;     
#
$read_net_data  = true;
#
$scrpt          = 'PWS_livedata.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL;
include $scrpt;
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$body_image     ='img/background-rain2.jpg';
$clr_offline    = '#ff8841';
$clr_online     = 'green';
$clr_trans      = '1';
$clr_trans      = '0.9';  // '1';

if ($current_theme == 'light') 
     {  $clr_htmlbg     = '#dcdcdc';    /* bckground  html      */
        $clr_mnbg       = '#efeded';    /* bckground  menu/header   */
        $clr_hdrdrk      = '#cecece';   /* bckground  text headers    */
        $clr_hdrbg      = '#e0e0e0';    /* bckground  block title    */
        $clr_blckbg     = 'rgba(239,237,237,'.$clr_trans.')'; #'#efeded';    /* bckground  block     */
        $clr_brdr       = '#F0F0F0';    /* color  border        */
        $clr_txt        = '#000';  }    /* color  text          */  
elseif ($current_theme == 'user') {
        if (!file_exists('./_my_settings/user_theme.txt') )
             {  $clr_htmlbg     = 'rgba(233,241,226)';    /* bckground  html      */
                $clr_mnbg       = '#b0cba0';    /* bckground  menu/header   */
                $clr_hdrdrk     = '#669966';    /* bckground  text headers    */
                $clr_hdrbg      = '#b0cba0';    /* bckground  block title    */
                $clr_blckbg     = 'rgba(233,241,226, 0.4)';    /* bckground  block     */
                $clr_brdr       = '#108400';    /* color  border        */
                $clr_txt        = '#000';       /* color  text          */
                $clr_offline    = '#f37867';}     
        else {  $lines = file( './_my_settings/user_theme.txt');  #echo print_r ($lines, true); exit;
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Processing ./_my_settings/user_theme.txt'.PHP_EOL;
                foreach ($lines as $line)
                     {  if (substr($line,0,1) <> '|') {continue;}
                        list ($none,$key,$color) = explode ('|',$line.'|||');
                        $key    = trim($key);
                        $color  = trim($color);
                        if ($key == '' || $color == '') {continue;}
                        $$key   = $color; } // eo foreach
                }  // customized colors
        } // eo user theme
else  { $clr_htmlbg     = '#151819';    /* bckground  html      */
        $clr_mnbg       = '#393D40';    /* bckground  menu/header   */
        $clr_hdrdrk     = '#5b6165';    /* bckground  text headers   */
        $clr_hdrbg      = '#787f841a';  /* bckground  headers   */
        $clr_blckbg     = 'rgba(36,38,43,'.$clr_trans.')'; # '#24262B';    /* bckground  block     */
        $clr_brdr       = 'transparent';       /* color  border        */
        $clr_txt        = '#aaa'; }     /* color  text          */ 
#
$strng_style = 
'html          { color: '.$clr_txt.'; 
                background-color: '.$clr_htmlbg.'; 
                height: 100%; }
a             { color: '.$clr_txt.'; }
h1            { background-color: '.$clr_mnbg.'; }
.PWS_weather_item , .PWS_weather_item_s
              { border-color: '.$clr_brdr.';
                background-color: '.$clr_blckbg.'; }
.PWS_module_title 
              { background-color: '.$clr_hdrbg.'; }
.sidebarMenuInner .separator    { border-top: 1px  '.$clr_hdrdrk.' solid; 
                border-bottom: 1px  '.$clr_hdrdrk.' solid;  } 
.PWS_bar      { color: '.$clr_hdrbg.';}
.PWS_border   { border-color: '.$clr_brdr.';}
.PWS_offline  { color: '.$clr_offline.';}
.PWS_online   { color: '.$clr_online.';}
#sidebarMenu  { background-color: '.$clr_mnbg.'; }'.PHP_EOL;
#
if (isset ($_REQUEST['noborders']) ) {$txt_border = 'border: none;';}
elseif (! isset ($txt_border) )      {$txt_border = '';}
elseif ($txt_border == true)         {$txt_border = '';}
else                                 {$txt_border = 'border: none;';}
#
header('Content-type: text/html; charset=UTF-8');
echo '<!DOCTYPE html>
<html  lang="'.substr($used_lang,0,2).'"  class="'.$current_theme.'" >
'; 
$frame_ok       = false; // no invalid frames allowed
$title_text     = ' Home Weather Station ('.$livedataFormat.' version)';
#
if (isset ($_REQUEST['frame']) && strlen(trim($_REQUEST['frame']) ) < 20 ) #### 2021-01-07
     {  $scrpt          = 'PWS_frames.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL;
        include $scrpt;
        #
        if (array_key_exists ($_REQUEST['frame'], $frm_ttls) )
             {  $key    = $_REQUEST['frame'];
                if ( isset ($frm_ttls[$key]) )  { $title_text = ' '.$frm_ttls[$key];}
                $frame_ok       = true;}
} // eo check for menupage / frame
?>
<head>
<title><?php echo $stationlocation.$title_text; ?></title>
<meta content="Personal Home weather station with the weather conditions for <?php echo $stationlocation;?>" name="description">
<!-- Facebook Meta Tags -->
<meta property="og:url" content="">
<meta property="og:type" content="website">
<meta property="og:title" content="PWS_Dashboard at <?php echo $stationlocation;?>">
<meta property="og:description" content="Personal Weather Station with the weather conditions for <?php echo $stationlocation;?>">
<!-- Twitter Meta Tags -->
<meta property="twitter:card" content="summary">
<meta property="twitter:url" content="">
<meta property="twitter:title" content="">
<meta property="twitter:description" content="Weather conditions for <?php echo $stationlocation;?>">
<meta content="INDEX,FOLLOW" name="robots">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name=apple-mobile-web-app-title content="Personal Weather Station">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<link rel="manifest" href="css/manifest.json">
<link rel="icon" href="img/icon.png" type="image/x-icon" />
<link href="css/featherlight.css" type="text/css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="js/featherlight.js"></script>
<style>
.featherlight .featherlight-inner {background:url(./img/loading.gif) top center no-repeat;  }        
.featherlight-content    { background: transparent; max-width: 850px; width: 100%;} 
.featherlight-content .featherlight-close-icon { top: 0px; left: 0px; width: 60px; height: 20px; background: transparent;}
.featherlight-content iframe {width: 100%; height: 100%;} 
@keyframes fadeOut {  0% {opacity: 2;} 50% {opacity: 1;}100% {opacity: 0;}} 

*, html       { box-sizing: border-box;       
                text-align: center; 
                font-family: arial,sans-serif;}
body          { margin: 0 auto; 
                padding: 0;    
                font-size: 14px;  
                line-height: 1.2;}                               
small         { line-height: 12px;}
sup           { vertical-align: 20%;
                font-size: smaller;}
a             { text-decoration: none;}
div           { display: block;}
h1            { font-size: 15px;}
img           { vertical-align: middle;}
.PWS_weather_container 
              { display: flex; 
                justify-content: center; flex-wrap: wrap; flex-direction: row; align-items: flex-start;
                overflow: hidden; 
                margin: 0 auto;}
.PWS_weather_item 
              { position: relative; 
                width: 316px; min-width: 316px; float: left;
                height: 192px; 
                margin: 2px;
                border: 1px solid #000;  
                font-size: 12px;  }
.PWS_weather_item_s 
              { position: relative; 
                min-width: 200px; float: left;
                height: 80px; 
                margin: 2px;
                border: 1px solid #000;  
                font-size: 12px; }
.PWS_module_title 
              { width:100%;  
                height: 18px; 
                border: none;}
.PWS_module_content 
              { font-size: 10px; 
                vertical-align: middle;}             
.PWS_ol_time  { margin-top: -14px; 
                margin-right: 2px; 
                font-size: 10px;
                line-height: 10px; 
                float: right;} 
.PWS_left    { float: left;  width: 86px;  margin-left:  2px;  border: none;}
.PWS_right   { float: right; width: 86px;  margin-right: 2px;  border: none; }
.PWS_middle  { float: left;  width: 136px; margin: 0 auto;      border: none; }
.PWS_2_heigh { height: 80px; vertical-align: middle;}
.PWS_3_heigh { height: 53px; vertical-align: middle;}
.PWS_4_heigh { height: 40px; vertical-align: middle;}
.PWS_div_left{ height: 28px; margin: 0 auto; margin-top: 10px; font-size: 10px; 
                border-radius: 3px; border: 1px solid silver; 
                border-right: 3px solid silver;  padding: 1px; <?php echo $txt_border; ?>}    
.PWS_div_right{ height: 28px; margin: 0 auto; margin-top: 10px; font-size: 10px; 
                border-radius: 3px; border: 1px solid silver; 
                border-left: 3px solid silver;  padding: 1px; <?php echo $txt_border; ?>}        
.orange      { color: #ff8841;}
.green       { color: #9aba2f;}
.blue        { color: #01a4b4;}
.yellow      { color: #ecb454;}
.red         { color: #f37867;}
.purple      { color: #916392;}
.maroon      { color: rgb(208, 80, 65);}
.grey        { color: #aaaaaa;}
.large       { font-size: 20px;}
.narrow      { width: 100px;}      
.PWS_bucket {   
        height:105px; width:108px;
        border:         4px solid  silver;
        border-top:     1px dotted rgb(233, 235, 241);
        background:     url("img/rain/marker.png");
        background-size:cover;
        margin: 0px auto;}
.PWS_bucket .water {
        background:     url("img/rain/water.png");
        border: 0px;}
.PWS_bucket .clouds {
        background:     rgba(159, 163, 166, 0.4);
        border:         0px;
        border-top:     1px dotted rgb(255, 124, 57);}
.PWS_bucket .empty {
        background-color: transparent;
        border: 0px;}
.PWS_border             {  border: 1px solid silver; }
.PWS_notify 
    {   width:  250px;
        right:  10px;
        top:    120px;
        z-index: 9999;
        position: fixed;
        font-family: Arial, Helvetica, sans-serif;
        animation-fill-mode: both;
        animation-name: fadeOut;       }
.PWS_notify_box 
    {   position: relative;
        min-height: 80px;
        margin-bottom: 8px;
        font-size: 15px;
        background: rgb(97, 106, 114)}
.PWS_notify_box .PWS_notify_header
    {   position: relative;
        height: 26px;
        color: #aaa;
        background-color: rgb(61, 64, 66);} 
.PWS_notify_box .content
    {   padding: 8px;
        background: rgba(97, 106, 114, 1);
        color: #fff;
        text-align: center;}
.PWS_notify_box .PWS_notify_left
    {   float: left;
        text-align: left;
        padding: 3px;}
.PWS_notify_box .PWS_notify_right
    {   float: right;
        text-align: right;
        padding: 3px;}
@media screen and (max-width: 639px) {
        .PWS_weather_item, .PWS_weather_item_s {margin: 2px auto 0; float: none; width: 99%;}   /* # 2023-08-01 */
        .PWS_middle {display: inline-block; float: none; }                                      /* # 2023-08-01 */
        .invisible {display: none;}
        .cposition4 {display: none;}
        .cposition3 {display: none;}
        .cposition2 {display: none;}
        .featherlight-content {height: 250px;}
}
@media screen and (min-width: 640px){
        .PWS_weather_container {width: 640px;}
        .cposition4 {display: none;}
        .cposition3 {display: none;}
        .PWS_weather_item_s {width: 209px;}
        .featherlight-content {height: 350px;}
}
@media screen and (min-width: 850px){
        .featherlight-content {height: 550px;}
}
@media screen and (min-width: 960px)  {
        .PWS_weather_container {width: 960px;}
        .cposition4 {display: block;}
        .cposition3 {display: none;}
        .PWS_weather_item_s {width: 236px;}
        .featherlight-content {height: 550px;}
        .left_table td {text-align: left;}
}
<?php if ((int) $cols_extra > 0) {  ?>
@media screen and (min-width: 1280px) {
        .PWS_weather_container {width: 1280px;}
        .cposition4 {display: block;}
        .cposition3 {display: block;}
        .PWS_weather_item_s {width: 252px;}
}
<?php  }  ?>
#sidebarMenu {   
    position: absolute;
    left: 0;
    width: 240px;
    top: 2px;
    float: left;
    z-index: 30}
.sidebarMenuInner {
    margin: 0;
    padding: 0;
    width: 240px;
    float: left;}
.sidebarMenuInner li {
    list-style: none;
    padding: 5px 5px 5px 10px;
    cursor: pointer;
    border-bottom: 0;
    float: left;
    width: 240px;
    font-size: 12px;
    font-weight: 400}
.sidebarMenuInner .separator {
    cursor: default;
    margin: 5px 0px;
    font-weight: bold;}
.sidebarMenuInner li a {
    cursor: pointer;
    text-decoration: none;
    float: left;
    font-size: 12px;}
.sidebarMenuInner li a:hover {
    color: #f5650a;}
</style>
<?php 
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $strng_style .= '.PWS_round { border-radius: 50%;}'.PHP_EOL; }
else {  $strng_style .= '.PWS_round { border-radius: 3px;}'.PHP_EOL; }

$stripall       = 
$stripmost      =       #### 2021-08-03
$bd_stl1        =
$bd_stl2        = '';   
if (isset ($_REQUEST['stripall'])) 
     {  $stripall       = 'display: none; '.PHP_EOL; 
        $bd_stl1        = 'overflow: hidden; '.PHP_EOL; } 
elseif (isset ($_REQUEST['stripmost']))  #### 2021-08-03
     {  $stripmost      = 'display: none; '.PHP_EOL; 
        $bd_stl1        = 'overflow: hidden; '.PHP_EOL; } #### 2021-08-03
if (isset ($body_image) && $body_image <> '' && file_exists($body_image) )
     {  $bd_stl2        = "background: transparent url('$body_image') no-repeat fixed center;  background-size: cover; background-attachment: fixed; margin:  0; ";}
echo '<style>'.PHP_EOL
.$strng_style   
.'html { '.$bd_stl2 .'}'
.PHP_EOL;
echo '</style>
</head>'.PHP_EOL;
#
$homeicon='<svg width=14 height=14 fill=currentcolor stroke=currentcolor  viewBox="0 0 93 97.06" >
<g><path d="M92.56,45.42l-45-45a1.54,1.54,0,0,0-2.12,0l-45,45a1.5,1.5,0,0,0,0,2.12l8.12,8.12a1.54,1.54,0,0,0,2.12,0l2.16-2.16V95.56a1.5,1.5,0,0,0,1.5,1.5H78.66a1.5,1.5,0,0,0,1.5-1.5V53.5l2.16,2.16a1.5,1.5,0,0,0,2.12,0l8.12-8.12A1.5,1.5,0,0,0,92.56,45.42ZM37.66,94.06V70.65H55.34V94.06ZM77.16,50.63V94.06H58.34V69.15a1.5,1.5,0,0,0-1.5-1.5H36.16a1.5,1.5,0,0,0-1.5,1.5V94.06H15.84V50.63s0-.08,0-.11L46.5,19.84,77.17,50.51S77.16,50.59,77.16,50.63Zm6.23,1.86L47.56,16.66a1.54,1.54,0,0,0-2.12,0L9.62,52.48l-6-6L46.5,3.6,89.38,46.48Z"/></g>
</svg>';
$menuicon='<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" xml:space="preserve">
<g fill="#01a4b4"><path d="M0,3  l0,2 21,0 0,-2z" /><path d="M0,7 l0,2 21,0 0,-2z" /><path d="M0,11 l0,2 21,0 0,-2z" /></g>
</svg>';
#
echo '<body style="height: 100%;">
<!-- begin top layout -->'.PHP_EOL;
if (file_exists('_my_settings/before.txt')) 
     {  include '_my_settings/before.txt';}  #### 2021-04-17
echo '
<div class="PWS_weather_container "  style="clear: both;  margin: 0 auto; ">
<div class="PWS_weather_item" style="'.$stripall.$stripmost.' width: 100%; height: 46px; margin: 2px; ">'.PHP_EOL;
#
echo '<h1  style="padding: 10px; padding-top: 15px;  margin: 0 auto; width: 100%; height: 44px;" >'.PHP_EOL;
echo '<script>
  function altmenuclick() { showmenu(document.getElementById("sidebarMenu")) }
  function showmenu(which){
    if (!document.getElementById)
    return
    if (which.style.display=="block")
    which.style.display="none"
    else
    which.style.display="block"
  }
</script>
<a style="float: left; color: #01a4b4;  height: 20px; z-index: 4; cursor: pointer;" onclick="altmenuclick()">
'.$menuicon.'&nbsp;'.lang('Menu').'&nbsp;&nbsp;</a>
<a style="float: left; color: #01a4b4; height: 20px; z-index: 4; cursor: pointer;" href="./index.php?'
.$url_theme     # 2021-12-01
.'&'.$url_lang
.'&'.$url_units # 2021-12-01
.'">&nbsp;&nbsp;
'.$homeicon.'&nbsp;'.lang('Home').'&nbsp;&nbsp;</a>'.PHP_EOL;
if (isset ($frame_ok) && $frame_ok == true) 
     {  echo '<a href="./index.php?'
.$url_theme     # 2021-12-01
.'&'.$url_lang
.'&'.$url_units # 2021-12-01
.'">
<span style="float: left; 
        color: white; border-radius: 1px; box-sizing: content-box;
        width: 16px; height: 16px; margin-left: 10px; margin-top: -1px;
        background: #ff7c39; 
        align-items: center; justify-content: center; cursor: pointer;">X
</span>  </a>        
     ';}
if(trim($units_used) != 'us') 
     {  $o_units = 'us'; $text='F'; } 
elseif ( $EW_unit <> $units_used )   // 'us' is not the default unit
     {  $o_units = $EW_unit; $text='C'; }  // 2022-12-27
else {  $o_units = 'metric'; $text='C'; }  // 2022-12-27
echo '<span class="" style="float: right; margin-right: 10px;">
<a class="" href="./index.php?units='.$o_units
.'&'.$url_theme # 2021-12-01
.'&'.$url_lang  # 2021-12-01
.'">
<span style="display: flex; color: white; border-radius: 3px; box-sizing: content-box;
        width: 18px; height: 18px; padding: 1px; 
        background: #ff7c39; font-weight: 600; font-size: 16px;
        align-items: center; justify-content: center;">&deg;'.$text.'</span>
</a>
</span>
<b class=" invisible" >'.$stationName.'&nbsp; &#8226;&nbsp; '.$stationlocation.'</b>
<span id="positionClock" style="float: right; width: 30px; display: block; background: transparent" class="invisible">
&nbsp;
</span>'.PHP_EOL;



echo '</h1>
</div>
<!-- end top layout -->'.PHP_EOL;
$PWSpopup = '<svg viewBox="0 0 32 32" width="12" height="10" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">
        <path d="M14 9 L3 9 3 29 23 29 23 18 M18 4 L28 4 28 14 M28 4 L14 18"></path>
        </svg>'.PHP_EOL;
#
if ($frame_ok == true)
     {  $frame = $key;
        if ( isset ($frm_type[$key]) )  { $type  = $frm_type[$key]; } else { $type  = 'frame'; }
        if (!isset ($frm_wdth[$frame])) 
                     {  $width = 'width: 100%;';
                        $pagew = '';} 
                else {  $width = 'width: '.$frm_wdth[$frame].'px;';
                        $pagew = ' style="'.$width.'"'; }
        echo '<!-- begin frame or extra page  -->
<div class="PWS_weather_container " '.$pagew.'>'.PHP_EOL;              
        if ($type == 'frame') { echo '<iframe id="'.$frame.'" title="'.$frame.'" 
        style=" '.$width.' height: '.$frm_hgth[$frame].'px; background: white url(./img/loading.gif)  no-repeat; background-position: 50% 20px; margin: 2px 2px auto; border: none;"
        src="'.$frm_src[$frame].'">
        </iframe>'.PHP_EOL;}
        #
        if ($type == 'img') 
             {  if (!isset ($frm_wdth[$frame])) 
                     {  $width = 'max-width: 100%; height: auto;';} 
                else {  $width = 'max-width: '.$frm_wdth[$frame].'px; width: 100%; height: auto;';}
                echo '
<img src="'.$frm_src[$frame].'"  alt="'.$frame.'" 
style="'.$width.' margin: 2px 2px auto;  padding: 2px; ">
        '.PHP_EOL;} #  width: 100%; max-height: '.$frm_hgth[$frame].'px;
        # 
        if ($type == 'div') { include $frm_src[$frame];}
        #
        echo '<!-- end of container for external scripts -->
</div>'.PHP_EOL;        
        } // eo check and optional display extra page
#
else { // there was no frame / optional page
        $reload_js_code = '';           // assemble reload script  #### 2021-06-18
        $scrpt          = 'PWS_blocks.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL;
        include $scrpt;
        foreach ($blck_ppp  as $script => $arr_ppp) 
             {  #echo '<pre>'.print_r($arr_ppp,true);
                foreach ($arr_ppp as $key => $value)
                     {  $check  = $value['show'];
                        if ($check === true)  {continue;}
                        if ($check === false) {continue;}               # echo '<pre>'.print_r($blck_ppp[$script][$key],true);
                        $blck_ppp[$script][$key]['show'] = $$check;     # echo '<pre>'.print_r($blck_ppp[$script][$key],true); exit;
                        }
                }
        if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
        if ($stripall == '') {
                $scrpt          = 'clock_c_small.php';
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL;
                include $scrpt;
        #
                function  PWS_weather_item ($id,$script)
                     {  global $info, $blck_ttls, $wdth, $reload_js_code, $used_lang, $current_theme, $blck_rfrs, $positionlast; #### 2021-06-18
                        echo '<div class="PWS_weather_item_s c'.$id.'" style="height: 80px; '.$wdth.' ">
                    <div class="PWS_module_title" id="'.$id.'mt"><span style="position: relative;  top: 2px;" id="'.$id.'mt_s">'.$blck_ttls[$script].'</span></div>
                    <script> var id_blck= "'.$id.'"; </script>
                    <div id="'.$id.'">'.PHP_EOL;
                        if (array_key_exists($script,$blck_ttls) && $script <> $positionlast)                      #### 2021-08-03 2021-06-18
                             {  $reload_js_code .='function '.$id.'()'.PHP_EOL;
                                $reload_js_code .='     {  $.ajax ( { cache:false,'.PHP_EOL;
                                $reload_js_code .='                   success:function(a){$("#'.$id.'").html(a); },'.PHP_EOL;
                                $reload_js_code .='                   type:"GET",url:"'.$script.'?lang='.$used_lang.'&theme='.$current_theme.'&id_blck='.$id.'"'.PHP_EOL;
                                $reload_js_code .='                 } )} '.PHP_EOL;
                                if ($blck_rfrs[$script] > 0) 
                                     {  $reload_js_code .='setInterval('.$id.',' . '1000*'.$blck_rfrs[$script].');'.PHP_EOL;}
                                $reload_js_code .='//'.PHP_EOL;
                                } // load releoad                                       #### 2021-06-18
                    } // eof PWS_weather_item
                    
                $end_block= '
                    </div>
                </div>'.PHP_EOL;
                #
                echo '<div class="PWS_weather_container" style="clear: both; " >'.PHP_EOL;
                PWS_weather_item ('positionlast',$positionlast);echo '<br /><img src="./img/loading.gif" alt="loading">'; echo $end_block; 
                PWS_weather_item ('position1',$position1); include $position1; echo $end_block;
                PWS_weather_item ('position2',$position2); include $position2; echo $end_block;
                if ((int)$cols_extra > 0) { 
                        PWS_weather_item ('position3',$position3); include $position3; echo $end_block; }
                PWS_weather_item ('position4',$position4); include $position4; echo $end_block; 
                echo '</div> <!--end top layout -->'.PHP_EOL;
                } // eo stripall
#
# build middle part of page with x * y blocks        
#
        $loaded_bloks   = array();      // register every block loaded once.
# fill old default blocks
        $clean  = array('position11' => 'temp_c_block.php', 
                        'position21' => 'wind_c_block.php', 'position22' => 'baro_c_block.php', 'position23' => 'sun_c_block.php', 
                        'position31' => 'rain_c_block.php');
        foreach ($clean as $key => $default)
             { if (!isset ($$key) || $$key === '') {$$key = $default;}}
# reset old block numers
        if (isset ($extra3used ) && $extra3used == 'wide' )
             {  $cols_extra     = 1;
                unset ($extra3used);
                $position14     = $position1e; unset ($position1e);
                $position24     = $position2e; unset ($position2e);
                $position34     = $position3e; unset ($position3e);}
        elseif (isset ($extra3used ) && $extra3used == 'row' ) 
              { $rows_extra     = 1;
                unset ($extra3used);
                $position41     = $position1e; unset ($position1e);
                $position42     = $position2e; unset ($position2e);
                $position43     = $position3e; unset ($position3e);}             
# eo reset old block numers
        if (!isset ($rows_extra) ) {$rows_extra = 0;}
        if (!isset ($cols_extra) ) {$cols_extra = 0;}
# 
        echo '<!-- begin outside/station data section -->
        <div class="PWS_weather_container " >
<!-- first row of three or four -->        '.PHP_EOL;
#$rows_extra = 2;
        $cols   = 3 + $cols_extra;
        $rows   = 3 + $rows_extra;  echo '<!-- $cols_extra='.$cols_extra.' $rows_extra='.$rows_extra.' -->'.PHP_EOL;
        $blcks  = array();
        for ($n = 1; $n <= $rows; $n++)
             { for ($m = 1; $m <= $cols; $m ++)
                     {  $blcks[]= $m + (10 * $n); }
                }                       # echo '<pre>'.print_r($blcks,true); exit;
        foreach ($blcks as $nr)
             {  $id     = 'position'.$nr;       // postion11
                if (!isset ($$id) )             // check if $position11 is defined (in settings)
                     {  $script = 'dummy'; 
                        $title  = 'dummy'; }
                elseif ($$id == 'none')
                     {  continue; }
                elseif ($$id == 'dummy')
                     {  $script = 'dummy'; 
                        $title  = 'dummy'; }
                elseif (! is_file ($$id) )
                     {  $script = $$id;
                        $title  = 'not found'; }
                elseif (substr ($$id,-4) <> '.php' )
                     {  $script = $$id;
                        $title  = 'not a script'; }
                else {  $script = $$id;         // contents of $position11 => script name
                        if (!array_key_exists($script,$blck_ttls) )
                             {  $title  = 'not yet defined'; }
                        else {  $title  = $blck_ttls[$script];}
                        }
# start block 
                echo '  <div class="PWS_weather_item"><!-- '.$id.' '.$script.' -->'.PHP_EOL;
# block header
                echo '    <div class="PWS_module_title">'.PHP_EOL;
                echo '        <span style="position: relative;  top: 2px;">'.$title.'</span>'.PHP_EOL;
                echo '    </div>'.PHP_EOL;
# block contents
                echo '    <div id="'.$id.'" style="height: 154px;">'.PHP_EOL;
# first check what we have to do for a block
                if ($script <> 'dummy' && array_key_exists ($script,$loaded_bloks) )
                     {  echo 'already loaded at '.$loaded_bloks[$script];}  // already used
                elseif ($script == 'dummy')
                     {  echo 'dummy placeholder ';}             // empty block
                else {  $loaded_bloks[$script]  = $id;
                        $s_script       = $script;  #### 2021-03-24
                        include $script;
                        $script = $s_script;        #### 2021-03-24                    
# now add $reload_js_code  if it is a defined script
                        if (array_key_exists($script,$blck_ttls) )
                             {  $reload_js_code .='function '.$id.'()'.PHP_EOL;
                                $reload_js_code .='     {  $.ajax ( { cache:false,'.PHP_EOL;
                                $reload_js_code .='                   success:function(a){$("#'.$id.'").html(a); },'.PHP_EOL;
                                $reload_js_code .='                   type:"GET",url:"'.$script.'?lang='.$used_lang.'&theme='.$current_theme.'&units='.$units_used.'&id_blck='.$id.'"'.PHP_EOL;
                                $reload_js_code .='                 } )} '.PHP_EOL;
                                if ($blck_rfrs[$script] > 0) 
                                     {  $reload_js_code .='setInterval('.$id.',' . '1000*'.$blck_rfrs[$script].');'.PHP_EOL;}
                                $reload_js_code .='//'.PHP_EOL;
                                }
                        } // eo check if normal block
# eo  block contents                               
                echo '    </div>'.PHP_EOL;
# block footer with links
                echo '    <div class="PWS_module_title" style="text-align: left; font-size: 10px; padding-top: 4px;">'.PHP_EOL;
                if (key_exists($script,$blck_ppp))
                     {  $img    = '&nbsp;'.$PWSpopup.'&nbsp;';
                        foreach ($blck_ppp [$script] as $arr)
                             {  if ($arr['show'] === false) {continue;} 
                                if ($arr['chartinfo'] == 'popup' )
                                     {  $string = $arr['popup'];
                                        if (strpos(' '.$string,'?') <> false ) 
                                             {  $string .= '&amp;script='.$script;} 
                                        else {  $string .= '?script='.$script;}
                                        $string .=  '&'.$url_theme      # 2021-12-01
                                                   .'&'.$url_lang
                                                   .'&'.$url_units;     # 2021-12-01
                                        echo '<span><a href="'
                                                .$string
                                                .'" data-featherlight="iframe" >'
                                                .$img
                                                .$arr['text']
                                                .'</a></span>'.PHP_EOL; 
                                                }
                                elseif ($arr['chartinfo'] == 'page' && $stripall == '')
                                     {  echo '<a href="index.php?frame='.$arr['popup']
                                                .'&'.$url_theme      # 2021-12-01
                                                .'&'.$url_lang
                                                .'&'.$url_units      # 2021-12-01
                                                .'">'
                                                .$img.$arr['text'].'</a>';}
                                elseif ($arr['chartinfo'] == 'external' )
                                     {  echo '<a href="'.$arr['popup'].'" target="_parent">'.$img.$arr['text'].'</a>';}
                                $img = ' - ';
                                } // eo for each
                        } // eo if key-exists
                echo '    </div> ' .PHP_EOL;                    
                echo '<br></div>'.PHP_EOL; } 
        #
        echo '</div><!-- end all blocks -->'.PHP_EOL;
} // eo normal page display
#
# footer area
#
echo '<div class="PWS_weather_container invisible" style="border: 0px; margin: 2px; '.$stripall.$stripmost.'">
<table class="PWS_weather_item" style="width: 100%; height: 40px; margin: 0px auto; padding: 0px; font-size: 12px; ">
<tr>
<td style="text-align: left; min-width: 120px; vertical-align: top;"><a href="'.$weatherprogram['href'].'" target="_blank" title="'.$livedataFormat.'">'.$weatherprogram['img'].'</a></td>
<td style="text-align: center; width: 100%; vertical-align: top;">
<span style=" margin: 0 auto;">';
#
# check age of history
#
$max_age= 24 * 3600;  
$title = 'Status ';
if (!file_exists ('./_my_settings/history.txt'))
     {  $hst_age= 0;}
else {  $hst_age= time() - filemtime('./_my_settings/history.txt') ;}
if ( $hst_age > $max_age ) {$title .= 'history: stalled ';}
if (!isset ($check_cron) ) {$check_cron = true; }  #$check_cron = false;
if (!file_exists ($livedata) )
     {  $live_time      = 9999999;}
else {  $live_time      = time() - filemtime ( $livedata);}
if ( $live_time > 300) { $title .= ' Livedata: stalled';}
if ( (time() - $weather['datetime']) > 400) { $title .= ' Weatherdata: old';}

if ( !$check_cron === false ) # 2022-04-30 
     {  if ( $hst_age > $max_age || $live_time > 300) { $bg  =  'background-color: red; '; } else { $bg =  ''; /* color: transparent; '; */}
        echo '<a href="status_popup.php" data-featherlight="iframe">'
        .'<span class="PWS_round" title="'.$title.'" style="cursor: help; '.$bg.' height: 12px; width:12px; ">'
                .'<span class="PWS_round" id="statuses">&nbsp;&nbsp;&#x2713;&nbsp;&nbsp;</span>'
        .'</span></a>  ';  }
else { echo '<!-- $hst_age='.$hst_age.' $check_cron=false -->'; }
#
echo $weather['swversion'].'&nbsp;&nbsp;-&nbsp;&nbsp;'
     .$hardware.'&nbsp;&nbsp;-&nbsp;&nbsp;'
     .$stationlocation.'&nbsp; <img src="img/flags/'.$country_flag.'"  title="'.$PWS_version.'" width="15" alt="flag">'.PHP_EOL;
if (trim($personalmessage) <> '') { echo '<br />'.lang($personalmessage);}
echo '</span>
</td>
<td style="float: right; text-align: right; min-width: 120px; font-size: 8px; vertical-align: top;">';
# show weather station icon
if ($manufacturer == 'davis')
     {  echo '<a href="https://www.davisinstruments.com/pages/about-us" title="https://www.davisinstruments.com/pages/about-us" target="_blank">
        <img src="img/davis.svg" width="95" height="20" alt="Davis Instruments&reg;" ></a>';}
elseif ($manufacturer == 'fineoffset')
     {  echo '<a href="http://www.foshk.com/" title="Fine Offset" target="_blank">
       <img src="img/foshk_logo.png" width="100" alt="www.foshk.com" ></a>';}
elseif ($manufacturer == 'weatherflow' ||  $weatherflowoption == true)
     {  echo '<a href="https://weatherflow.com/" title="https://weatherflow.com/" target="_blank">
        <img src="img/wflogo.svg" width="100" alt="http://weatherflow.com/" ></a>';}
else echo '';
# show bio link
echo '<br /><br /><a href="bio_popup.php?lang='
        .$user_lang   # 2021-12-01
        .'"data-featherlight="iframe" title="Contact WEATHERSTATION Info" tabindex="-1">'.lang('Credits, contact and').' . . .<svg viewBox="0 0 32 32" width="12" height="10" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">
        <path d="M14 9 L3 9 3 29 23 29 23 18 M18 4 L28 4 28 14 M28 4 L14 18"></path>
        </svg>
 </a>
</td>
</tr>
</table>
</div>
<div id="notifications"></div>'.PHP_EOL;
#
# generate reload of all blocks.
#
if ($frame_ok == false)
     {  if (!isset ($test_started) ) {$test_started = '';}
        echo '<script>'.PHP_EOL;
        echo '// load all data  - first functions using time-out  = immidate execution, sleep later
$(document).ready(function(){stationcron()});
function stationcron()
     {  $.ajax ({cache:false, 
                 success: function(a) {$("#statuses").html(a); setTimeout(stationcron,1000*'.$non_cron.')},
                 type:"GET",url:"PWS_load_files.php?lang='.$used_lang.$test_started.'"})};
//
$(document).ready(function(){positionlast()});
// advisory script  needs to load external scripts and external data
function positionlast()
    {   $.ajax({cache:false,
                success:function(a){$("#positionlast").html(a);
                setTimeout(positionlast,' . '1000*'.$blck_rfrs[$positionlast].')},
                type:"GET",url:"'.$positionlast.'?lang='.$used_lang.'"})};
// now all functions with setInterval = sleep first run after'.PHP_EOL;
#
        echo $reload_js_code;
        if (!isset ($_REQUEST['stripall'])) {$extra = '&stripall';} else {$extra = '';}
        echo 'function notifications(){  
        $.ajax ({cache:false, 
        success: function(a) {$("#notifications").html(a);},
        type:"GET",url:"PWS_notifications.php?lang='.$used_lang.$extra.$test_started.'"})};
$(document).ready(function()
     {  notifications();
        setInterval(notifications,1000*300);
        });
//
</script>'.PHP_EOL;
}
# now the menu is generated
#
if (!isset ($_REQUEST['stripall'])) {
        $scrpt          = 'PWS_menu.php';  
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt; 
}
if (isset ($_REQUEST['test']) ) echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL;
if (file_exists('_my_settings/hooks.txt')) {include '_my_settings/hooks.txt';}
?>
<br />
</div>
<br />
</body>
</html>
<?php
if (isset ($_REQUEST['test']) && isset ($missing))
     {  echo '<!-- ';
        foreach ($missing as $txt) {echo $txt;}
        echo ' -->'.PHP_EOL; }
