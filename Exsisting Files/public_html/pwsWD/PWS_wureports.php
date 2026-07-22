<?php $scrpt_vrsn_dt  = 'PWS_wureports.php|01|2023-02-15|'; # beta release 2012_lts
#-----------------------------------------------
#                 EXTRA SETTINGS for this script
$wu_fldr= '../wureports/'; # next to pwsWD/
#$wu_fldr= './wureports/'; # inside pwsWD/
$dateLongFormat = 'l d F Y';
#
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
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
$mypage         = basename(__FILE__);
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#-----------------------------------------------
$from_dir       = getcwd().'/'; # echo 'from_dir = '.$from_dir . PHP_EOL;
#$WU_dir       = __DIR__.'/';  # echo 'live_dir = '.$live_dir . PHP_EOL;
#
# -------------------------------- load settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$save_lang      = $lang;
#
list ($y,$m,$d) = explode ('-',$wu_start.'-');
$wustart 	= $d.'-'.$m.'-'.$y; #$wu_start;  list ($y,$m,$d) = explode ('-',$wustart.'-')
$wuid		= $wuID;
$WUdatastr      = $this_server.'PWS_DailyHistory.php';
$latitude	= $lat;	
$uomTemp 	= '&deg;'.$temp_his;		
$uomBaro 	= ' '.$baro_his;
$uomWind 	= ' '.$wind_his;
$uomRain	= ' '.$rain_his;
if ($wind_his <> 'mph') { $uomWrun= ' km';} else { $uomWrun= 'mi';}
$lang		= substr($used_lang.'en',0,2);			
$wsreportsDir	= $wu_fldr;			// folder where the report scripts are located, leave as is if you are running this script from the same folder
$charset	= 'UTF-8';		// 'ISO-8859-1'  'UTF-8'
$wsDebug = true;
echo '<div class="PWS_weather_item " style="width: 100%; height: 100%; margin: 2px;">'.PHP_EOL;
#
$script = $wu_fldr.'wsReports1part.php';
ws_message (  '<!-- module '.basename(__FILE__).' ('.__LINE__.'): loading '.$script.' -->');
include $script;
#
echo ws_css_insert ($wsreportsDir.'wsReports.css',true);
echo '</div>'.PHP_EOL;
$lang = $save_lang;
#
function ws_message ($message, $always=false, &$string=false) 
     {  global $wsDebug;
        if (isset ($wsDebug)  && $wsDebug == true )          
	     {  $echo   = true;}
	else {  $echo	= $always;}
	if ( $echo   === false ) { return; }
        if ( $string === false)  
             {  echo $message.PHP_EOL;}
	else {  $string .= $message.PHP_EOL;}
} // eof ws_message
#
function ws_debug_times($start = '') {
        global $ws_start_time, $ws_passed_time;
        if (!isset ($ws_passed_time)) 
             {  $ws_start_time = $ws_passed_time = microtime(true);
                return  '<!-- '.$start.' Debug timers initialized  -->';}
#
        $now            = microtime(true);
        $since_last     = $now - $ws_passed_time;
        $ws_passed_time = $now;
        
        if ($since_last < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($since_last,4);}
        $until_last     = $now - $ws_start_time;
        if ($until_last < 0.0001) {$string2 = '< 0.0001';} else {$string2 = round($until_last,4);}
       
        return '<!-- '.$start.' Time spent until here: '.$string2.' seconds. Since last timestamp: '.$string1.' seconds.  -->';
} // eof ws_debug_times

function ws_css_insert ($styles,$sheet = 'n/a')  
     {  $string = '<script>
if(typeof document.createStyleSheet === "undefined") {
    document.createStyleSheet = (function() {
        function createStyleSheet(href) {
            if(typeof href !== "undefined") {
                var element = document.createElement("link");
                element.type = "text/css";
                element.rel = "stylesheet";
                element.href = href;}
            else {
                var element = document.createElement("style");
                element.type = "text/css";}
            document.getElementsByTagName("head")[0].appendChild(element);
            var sheet = document.styleSheets[document.styleSheets.length - 1];
            if(typeof sheet.addRule === "undefined")
               { sheet.addRule = addRule;}
            if(typeof sheet.removeRule === "undefined")
               { sheet.removeRule = sheet.deleteRule;}
            return sheet;
        }
        function addRule(selectorText, cssText, index) {
            if(typeof index === "undefined") { index = this.cssRules.length;}
            this.insertRule(selectorText + " {" + cssText + "}", index);
        }
        return createStyleSheet;
    })();
}';
if ($sheet === 'n/a')
     {  $string .= '
var sheet = document.createStyleSheet();'.PHP_EOL;
        foreach ($styles as $key => $css) 
            {   $string .= 'sheet.addRule("'.$key.'","'.$css.'");'.PHP_EOL;} }
else {  $string .= '
var sheet = document.createStyleSheet("'.$styles.'");'.PHP_EOL;}
$string .= '</script>'.PHP_EOL;
return $string;}