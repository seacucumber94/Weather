<?php $scrpt_vrsn_dt  = 'simple.php|01|2023-02-15|';  # release 2012_lts
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#                                  your settings                
#-----------------------------------------------
#
$allow_lng_slct = true;         // set to false if not needed as in most cases
$allow_refresh  = true;        // set to false if text not wanted
$use_header     = true;         // set to false if no headline with stagtion name BUT no refresh counter visible in that case also
$reloadTime     = 30;           // time between loading new data from website
$max_width      = 1030;         // 3 blocks / row  set to comment if you want 4 blocks / row 
#$max_width      = 1360;        // 4 blocks / row ! remove # in first position if you want 4 blocks / row 
#
#-----------------------------------------------
#                                    colors used 
#-----------------------------------------------
#   => remove # on first position on line to use. 
#   => Set a # on first position of all other lines
# 
#$my_color       = '        .my_color    { background-color: rgba(154,186, 47, 0.6); color: white;}';   // green  
#$my_color       = '        .my_color    { background-color: rgba(80,80, 80, 0.6); color: white;}';     // dark
#$my_color       = '        .my_color    { background-color: rgba(220,220,220, 0.6); color: black;}';   // light
$my_color       = '        .my_color    { background-color: rgba(43,111,203, 0.2); color: white;}';    // blue
#$my_color       = '        .my_color    { background-color: rgba(239,127, 61, 0.6); color: white;}';   // orange
#
#-----------------------------------------------
#                           Which blocks to show 
#-----------------------------------------------
# one block definition in each row row
#
# set a # at the first on a row to not use that block
#
# set the rows for all blocks you want 
#             in the sequence you want.
#
$my_blocks = '
#| ---------------- large value + header text ---------------|---left value+arrow or text--|----- right value+arrow or text --  |
#|  weather item  |  header text   | extra | unit-easyweather| low/left item | arrow/text  | right /high item   | arrow/text    |                           
#
 | temp           | Temperature    | °     | temp_units      | temp_low      | arrow       | temp_high          | arrow         |
 | barometer      | Pressure       |       | barometer_units | barometer_min | arrow       | barometer_max      | arrow         |
 | humidity       | Humidity       | %     |                 | humidity_low  | arrow       | humidity_high      | arrow         |
 | wind_speed     | Wind           |       | wind_units      |               |             | wind_speed_max     | arrow         |
 | wind_gust_speed| Gust           |       | wind_units      |               |             | wind_gust_speed_max| arrow         |
 | wind_compass   | Compass        |       |                 | wind_direction| °           |                    |               |
 | rain_today     | Rain           |       | rain_units      | rain_month    | Month       | rain_year          | Year          |
 | temp_indoor    | Indoor         | °     | temp_units      |               |             | humidity_indoor    | Humidity      |
 | uv             | UV-Index       |       |                 | solar         | Solar       | lux                | Lux           |
 | lightning      | Lightning      |       |                 | lightningkm   | km          | lightningDT        |               |  for metric use
#| lightning      | Lightning      |       |                 | lightningmi   | mi          | lightningDT        |               |  for use with miles
#
#| ---------------- large value + header text----------------|---left value+arrow or text--|----- right value+arrow or text --  |
#|  weather item  |  header text   | extra | unit-easyweather| low/left item | arrow/text  | right /high item   | arrow/text    |                           
#
 | pm25_c_aqi1    | AQ sensor 1    | -     | aqhi_type       | pm25_a_aqi1   | average     | pm25_crnt1         | um<sup>3</sup>|
 | pm25_c_aqi2    | AQ sensor 2    | -     | aqhi_type       | pm25_a_aqi2   | average     | pm25_crnt2         | um<sup>3</sup>|
 | pm25_c_aqi3    | AQ sensor 3    | -     | aqhi_type       | pm25_a_aqi3   | average     | pm25_crnt3         | um<sup>3</sup>|

 | co2            | co2            | ppm   |                 | co2_24h       | average     | pm25_24avgco2      | pm25 AQI      |

 | soil_mst1      | Soil moisture  |       | soilmoist_type  |               |             | extra_tmp1         | Temperature   |
 | soil_mst2      | Soil moisture-2|       | soilmoist_type  |               |             | extra_tmp2         | Temperature   |

 | extra_tmp1     | extra_tmp1     | °     | temp_units      |               |             | extra_hum1         | Humidity      |
 | extra_tmp2     | extra_tmp2     | °     | temp_units      |               |             | extra_hum2         | Humidity      |
#
#
';
#  
$a_low          = '&#9660;';    // arrow low value at left
$a_high         = '&#9650;';    // arrow high value at right
#
# ========= no more settings below  ============
#
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
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
# ----------------------------- load weatherdata
$scrpt          = '_data.php'; 
$need_sky       = false;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL; 
include $scrpt; 
#
# --------------------------------   test values
#
#$weather['rain_today'] = 0.05;  $weather['rain_month'] = 1.05; $weather['rain_year'] = 11.19;
#$weather['uv'] = 0.5;
#$weather['soil_tmp1'] = 18;     
#echo '<pre>'.print_r($weather,true).'<pre>'; exit;
#
# -----------END OF --------------   test values
#
# ----------------- check settings -------------
if (!isset ($pws_fldr )) {$pws_fldr  = '';}
#
if (!isset ($allow_lng_slct)  || $allow_lng_slct <> false  ) 
     {  $allow_lng_slct = true;} 
else {  $allow_lng_slct = false;}
#
if (!isset ($allow_refresh)  || $allow_refresh <> false  ) 
     {  $allow_refresh  = true;} 
else {  $allow_refresh  = false;}
#
if (!isset ($use_header)  || $use_header <> false  ) 
     {  $use_header     = true;} 
else {  $use_header     = false;}
#
if (!isset ($reloadTime ) 
     || !is_numeric ($reloadTime))  
     {  $reloadTime  = 30;}
$reloadTime     = (int) $reloadTime;
if ( $reloadTime < 5) { $reloadTime = 30;}
#
if (!isset ($max_width ) 
     || !is_numeric ($max_width))  
     {  $reloadTime  = 1030;}
#
if (!isset ($a_low )) { $a_low  = '&#9660;';}
if (!isset ($a_high )){ $a_high = '&#9650;';}
# ---------- generate blocks array -------------
#
$parts  = array(); 
$my_parts = explode ("\n",$my_blocks."\n");  #echo '<pre>'; #.__LINE__.print_r($my_parts,true); exit;
foreach ($my_parts as $string)
     {  if (substr ($string.'#',0,1) == '#') {continue;}
        list ($none, $item, $header, $extra,$unit,$low,$low_text,$high,$high_text) = explode ('|',$string.'|||||||||||||||');
        $item   = trim($item);
        if (!array_key_exists ($item,$weather) ) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') no weather-Item >'.$item.'< in line:'.$string .PHP_EOL;
                continue;}
        $low_text       = trim($low_text);
        if     ($low_text == 'arrow')  { $low_text  = true;} 
        elseif ($low_text == '')       { $low_text  = false;} 
        $high_text      = trim($high_text);
        if     ($high_text == 'arrow') { $high_text = true;} 
        elseif ($high_text == '')      { $high_text = false;}         
        $parts[]= array($item,trim($low),trim($high),trim($header),$low_text,$high_text,false,$extra,trim($unit));
        } // eo for each line
#
# --------- assemble javascript array ----------
#     of all weather-items as used in all blocks 
#
$n      = 0;
$arrJS  = 'var ajaxIDs=[];'.PHP_EOL;
foreach ($parts as $arr)
     {  $arrJS .= '    ajaxIDs['.$n.'] = "'.$arr[0].'";'.PHP_EOL; $n++;
        if ($arr[1] <> false) 
             {  $arrJS .= '    ajaxIDs['.$n.'] = "'.$arr[1].'";'.PHP_EOL;  $n++;}
        if ($arr[2] <> false) 
             {  $arrJS .= '    ajaxIDs['.$n.'] = "'.$arr[2].'";'.PHP_EOL;  $n++;}  
        } 
#
# ----- assemble optional language selector ----  
#
if (isset ($allow_lng_slct) && $allow_lng_slct == true) 
     {  $string = '';
        foreach ($lngsArr as $key => $arr)
             {  if ($key == $user_lang) 
                     {  $selected = ' selected="selected"';}
                else {  $selected = '';}
                $string2= '';
                foreach ($_GET as $get => $value)
                     {  if ($get == 'lang') {continue;}
                        $string2.= '            <input type="hidden" style="padding: 0px; border: 0px; margin: 0px" name="'.$get.'" value="'.$value.'">'.PHP_EOL;}
                        $string .= '                <option value="'.$key.'"'.$selected.'>'.$arr['txt'].'</option>'.PHP_EOL;
                        }
        $lang_select = '<form method="get" name="lang_select" action="#" 
                style="padding: 0px; margin: 0px; color: white; background: transparent; position: absolute; top: 0px; left: 10px;">
'.$string2.'            <select id="lang" name="lang" style="color: white; font-weight: 400; background: transparent; border: none; position: relative; top: 0px; " onchange="this.form.submit();">
'.$string.'            </select>
        </form>';}
else {  $lang_select = '';} // eo language selector
#
# -----assemble link for ajax data-updates -----
#
$smpl_url       = '_data.php'.'?ajax&nosky';
foreach ($_GET as $get => $value)
    {   $smpl_url.= '&'.$get.'='.$value;}
#
# -------------- assemble blocks html ----------
#
$blks_html      = '';
foreach ($parts as $item)
     {  $text   = $item[3]; 
        $unit   = $item[8];
        if ($unit <> '')
             {  $unit   = lang($weather[$unit]);}
        $large  = $item[0];
        if (!array_key_exists ($large, $weather) 
           || $weather[$large] === 'n/a') // skip unknown / empty data
             {  continue;}
#             
        $left   = $right = '';
        if ($item[1] <> false) 
             {  $key    = $item[1];
                $left   = '<span class="ajax" id="'.$key.'">'.$weather[$key].'</span>';
                $check  = $item[4];
                if ($check === true) 
                     {  $left   = $left.$a_low;}
                elseif ($check <> false)
                     {  if (strlen($check) > 2)
                             {  $check  = ' <span style="font-size: 12px;">'.lang($check).'</span>';} 
                        $left   = $left.$check;}
                }
#
        if ($item[2] <> false) 
             {  $key    = $item[2];
                $right  = '<span class="ajax" id="'.$key.'">'.$weather[$key].'</span>';
                $check  = $item[5];
                if ($check === true) 
                     {  $right  = $a_high.$right;}
                elseif ($check <> false)
                    {   $right  = '<span style="font-size: 12px;">'.lang($check).'</span> '.$right;} 
                }
        $blks_html .= '    <div class="SMPL_weather_item my_color">
        <div class="SMPL_module_title my_color">'.lang($text).$item[7].$unit.'</div>
        <div class="SMPL_module_content"><span class="ajax" id="'.$large.'">'.$weather[$large].'</span></div>
        <div class="SMPL_module_low">'.$left.'</div>
        <div class="SMPL_module_high">'.$right.'</div>'.PHP_EOL;
        $blks_html .= '    </div>'.PHP_EOL;
        }    
#
# -------sent all html to the browser-----------
#   
echo '<!DOCTYPE html>
<html lang="'.substr($used_lang,0,2).'" style="height: 100%;">
<head>
  <meta charset="UTF-8">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name=apple-mobile-web-app-title content="Personal Weather Station">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <!-- link rel="manifest" href="css/manifest.json" -->
  <link rel="icon" href="img/icon.png" type="image/x-icon" />
  <link rel="stylesheet"  href="https://fonts.googleapis.com/css?family=Poppins">
  <title>'.$stationName.' </title>  
  <style>
        *, html 
             {  box-sizing: border-box;       
                text-align: center; 
                font-family: poppins,sans-serif;}';
if (array_key_exists  ('body_image',$weather) && file_exists ($weather['body_image']) )
     {  echo '
        html {  background: transparent url('.$pws_fldr.$weather['body_image'].') no-repeat fixed center;  background-size: cover; background-attachment: fixed; margin:  0;}';}
echo '       
        body {  margin: 0 auto; 
                padding: 0;    
                font-size: 14px;  
                line-height: 1.2;}                               
        div  {  display: block;}
        strongnumbers
             {  font-weight:600}
        .SMPL_weather_container 
              { display: flex;  overflow: hidden;  
                justify-content: center; flex-wrap: wrap; flex-direction: row; align-items: flex-start;} 
        .SMPL_weather_container > div
              {  flex-grow: 1; flex-shrink: 1; flex-basis: auto;}
        .SMPL_weather_item
              { position: relative; display: block;
                background-color: #D4D7D9; border-radius: 12px; margin: 2px;
                width: 330px; min-width: 280px; float: left;
                height: 136px; }
        @media screen and (max-width: 639px) 
             {  .SMPL_weather_item 
                     {  margin: 2px auto 0; float: none;  width:  100%;}   
                }
        @media screen and (min-width: 640px)  
             {  .SMPL_weather_container 
                     {  width: 100%;} 
                }
        @media screen and (min-width: 956px)  
             {  .SMPL_weather_container 
                     {  width: 100%; max-width: '.$max_width.'px; } 
                }
        .SMPL_module_title 
              { font-size: 24px; font-weight: bold; opacity: 1; 
                border: none; border-top-left-radius: 12px;  border-top-right-radius: 12px;}
        .SMPL_module_content 
              { font-size: 80px; position: relative; top: -4px;  } 
        .SMPL_module_low 
              { font-size: 24px; position: relative; top: -14px; left: 4px;  float: left;}            
        .SMPL_module_high 
              { font-size: 24px; position: relative; top: -14px; right: 4px; float: right;}
'.$my_color.' 	
        .large       { font-size: 26px;}
        .xlarge      { font-size: 46px;}
        .narrow      { width: 100px;}
        .low_item    { height: 165px;}
        .xlow_item   { height: 110px;}

        .smpl        { background-color: black; color: #AAA;} 
  </style>        
  <script>
    '.$arrJS
.'    var wsUrl       = "'.$smpl_url.'";   
    var reloadTime  = '.$reloadTime.';  
  </script>
</head>
<body class="dark" style="background-color: transparent; height: 100%; width: 100%;">'.PHP_EOL;
if (array_key_exists ('debug', $_REQUEST)) {$use_header = $allow_refresh = true;}
if ($allow_refresh)
     {  $t_refresh = lang('Refresh').': ';}
else {  $t_refresh = '';}
if ($use_header)
echo '<div class="SMPL_weather_container" style="margin: 0 auto;"><!-- header container -->
    <div class="SMPL_weather_item my_color large" style="width: 100%; margin: 2px 6px; padding: 0px; float: none; height: 40px; text-align: center;">
        <span style="position: relative; top: 4px;">'.$stationName.'</span>
        '.$lang_select.'
        <span id="ajaxcounter" style="font-size: 12px; position: absolute; top: 12px; right: 10px;">0</span>
        <span style="font-size: 12px; position: absolute; top: 12px; right: 30px">'.$t_refresh.'</span>
    </div>
</div><!-- eo header container -->'.PHP_EOL;;
echo '<div class="SMPL_weather_container" style="margin: 0 auto;"><!-- enclosing  blocks container -->
'.$blks_html.'
<br><br><br><span class="ajax" id="ajaxindicator"></span>
</div><!-- enclosing  blocks container  -->
<script>
// Load changing weathervalues into fields on screen, colorize changed values for some seconds
// Total rewrite from WD-tags as used in Saratoga / Leuven templates
// Version 2021-08-24  
// -- begin settings --------------------------------------------------------------------------
        var flashcolor 	        = "#00CC00";	// color to flash for changed observations
        var flashtime  	        = 3000; 	// miliseconds to keep flash color on (5000 = 5 seconds);
        var wsRequest 	        = false;	// to communicate with server to get the field-data
        var errorEval;
        var counterSecs         = reloadTime;
        var ajaxVars            = new Array();  
//
// find the handler for AJAX based on availability of the wsRequest object
//
        function ws_ajax_request () {
                wsRequest = false;
                try { wsRequest = new XMLHttpRequest()}          // non IE browser (or IE8 native)  
                catch(e1) 
                     {  wsRequest = false; 
                        alert("Sorry.. AJAX updates are not available for your browser.") ;} // eo catxh e1
                return wsRequest;
        } // eof ws_ajax_request
//
// wsRequest the data form the server
//
        function load_data(wsUrl) {                     
                if (wsRequest)   // is there a usable object
                     {  wsRequest.open("get", wsUrl );   
                        wsRequest.onreadystatechange = process_data;    // which function handles the returned data:
                        wsRequest.send(null);}                          // send the wsRequest to the server:
                else {  alert("Sorry.. AJAX updates are not available for your browser.");}
        }  // eof load_data: wsRequest the data form the server
//
//  when data is received 
//
        function process_data() {       // wait antoher second before starting to use the data
                if ( (wsRequest.readyState == 4) && (wsRequest.status == 200) ) { 
                        setTimeout("execute_data()", 1000);  }
        }
//  now we can use the latest data
        function execute_data() {               // Everything ok   
                eval (wsRequest.responseText);  // alert ("252"+wsRequest.responseText);  // alert ("242 " + ajaxVars["ajaxdate"]);   // eval the string 
                if  (undefined == ajaxVars["ajaxdate"]) {
                        var indicator   = "Error in data received, reload page to start";
                        errorEval = true; }
// use loop to process all ajax fields  2021-08-2
                else {  var indicator   = "";
                        var numelements = ajaxIDs.length;               // alert ("number of objects ="+numelements);
                        for (var index=0; index!=numelements; index++) {
                                var name        = ajaxIDs[index];       //alert ("name ="+name+" value="+ajaxVars[name]);
                                var element     = document.getElementById(name);
                                if (! element ) { continue; }
                                var oldValue    = element.innerHTML;
                                var lastobs     = element.getAttribute("lastobs");
                                var newValue    = ajaxVars[name];
                                if (!newValue)  {continue;}
                                element.innerHTML= newValue;
                                newValue        = element.innerHTML;
                                if (oldValue != newValue)
// store away the new current value in both the doc and the span as lastobs="value"
                                     {  element.setAttribute("lastobs",oldValue);
                                        element.style.color     = flashcolor;}
                                }
                        } //EO use loop to process all ajax fields  2021-08-2
                var element             = document.getElementById("ajaxindicator");
                element.innerHTML       = indicator;
                setTimeout("reset_ajax_color(\'\')",flashtime); // change text back to default color 
                ws_ajax_request ();				// load again after wait of so many milliseconds
                setTimeout("load_data(wsUrl)", reloadTime*1000); 
                counterSecs     = reloadTime;
        }   	// eof  execute_data: put every piece of data in the right place
//
// reset all the <span class="ajax"...> styles to have no color override
//
        function reset_ajax_color( usecolor ) {
                var numelements = ajaxIDs.length;               // alert ("number of objects ="+numelements);
                for (var index=0;index!=numelements;index++) {
                        var name = ajaxIDs[index];           // alert ("element ="+element+" value="+ajaxVars[element]);
                        var element     = document.getElementById(name);
                        if (! element ) { continue; }
                        element.style.color=usecolor;}
                }   // eof reset_ajax_color: reset all the <span class="ajax"...> styles to have no color override
//
// Mike Challis counter function (adapted by Ken True)
        function ajax_countup() {
                var element = document.getElementById("ajaxcounter");
                if (element) {
                        element.innerHTML = counterSecs;
                        counterSecs--;}
        }  				 // count time to new update 
//
// code below will be excuted when html is already loaded
//
window.setInterval("ajax_countup()", 1000);  // run the counter for seconds since update
//
wsRequest = ws_ajax_request ();	  // load for the first time after wait of so many milliseconds
setTimeout("load_data(wsUrl)", counterSecs*1000);	 //	
//
</script>
';
echo '<!-- '.$stck_lst.' -->'.PHP_EOL;
echo '</body>
</html>'.PHP_EOL;