<?php $scrpt_vrsn_dt  = 'PWS_wuhistory.php|01|2023-02-15|'; # beta release 2012_lts 
#-----------------------------------------------
#                 EXTRA SETTINGS for this script
$wu_fldr= '../wuhistory/'; # next to pwsWD/
#$wu_fldr= './wuhistory/'; # inside pwsWD/
#
$dateLongFormat = 'l d F Y';
#
$allow_download = false;  // visitors can not download the .csv data
$allow_download = true;  // visitors can download the .csv data  
#
$color          = 'green'; # use  'green'; 'blue';   'pastel';   'red';   'orange'; 
# 
$useBothUnits   = true;         // true use metric and english
#$useBothUnits   = false;        // C / F as in the template settings
#
# ==============================================
#                  DO NOT CHANGE BELOW THIS LINE
# ==============================================
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------------------------------ constants
$wu_css         = $this_server.$wu_fldr.'styleHist.css';  # 2023-02-15 use settings
$save_lang      = $lang;
$lang		= substr($used_lang.'en',0,2);			
list ($y,$m,$d) = explode ('-',$wu_start.'-');
$wustart 	= $d.'-'.$m.'-'.$y;
$wuid		= $wuID;
$WUdatastr      = $this_server.'PWS_DailyHistory.php';
$showSolar      = false;
$def_unit       = strtolower($temp_his);		
$charset	= 'UTF-8';
$wsDebug        = true;
#
# --------------------------- load old functions
load_missing_wustart();    
#
echo '<div class="PWS_weather_item '.$color.'" style="color: black; width: 100%; height: 100%; margin: 2px;">'.PHP_EOL;
#
$scrpt          = $wu_fldr.'WU-History4.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include $scrpt;
#
echo '<h3 class="blockHead" style="margin: 0 auto;"><small>'.langtransstr('Our weather-data as stored at').
'<u><a href="https://www.wunderground.com/personal-weather-station/dashboard?ID='.$wuid.'" target="_blank"> Weather Underground</a></u></small></h3>
</div>'.PHP_EOL; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') loading  =>'.$wu_css.PHP_EOL;
echo ws_css_insert ($wu_css,true);
ws_debug_info(); 
#
$lang = $save_lang;
#
function load_missing_wustart() {
    if (!function_exists ('ws_message') ){
        function ws_message ($message,$always=false,&$string=false) 
             {  global $wsDebug;
                $echo	= $always;
                if ( $echo == false && isset ($wsDebug) && $wsDebug == true ) 			{$echo = true;}
                if ( $echo == false && isset ($SIE['wsDebug']) && $SIE['wsDebug'] == true ) 	{$echo = true;}
                if ( $echo == true  && $string === false) {echo $message.PHP_EOL;}
                if ( $echo == true  && $string <> false) {$string .= $message.PHP_EOL;}}
    } // check ws_message
    if (!function_exists ('ws_makeRequest') ){
        function ws_makeRequest($file, $get_contents = false, $fake= '') 
              { $ws_script_environment = '<!-- module '.basename(__FILE__).' '.__FUNCTION__;
                if ($fake == '') {$fake = $file;}
                if ($get_contents === false || $get_contents == '')   // use CURL to load http(s) data
                     {  ws_message($ws_script_environment.' ('.__LINE__.'): data will be loaded from '.$fake.' -->',true ); 
                        return ws_curl($file,  $fake);}
                if ($get_contents === true)    // use file_get_contents to load http(s) data
                     {  $file_to_load   = $file; } 
                else {  $file_to_load   = $get_contents;} // use file_get_contents to load local (or test) file
                ws_message($ws_script_environment.' ('.__LINE__.'): data will be loaded from file '.$file_to_load.' using file get contents -->',true );
                return file_get_contents($file_to_load);} // eof ws_makeRequest
    } // eo check ws_makeRequest
    if (!function_exists ('ws_curl') ){
        function ws_curl ($url = '',$fake = '') {
                global $ws_msg_string, $ws_curl_follow, $ws_timeout, $ws_user_agent ;
                $ws_script_environment = '<!-- module '.basename(__FILE__).' '.__FUNCTION__;
                if ($fake == '') {$fake = $url;}
                if (!isset ($ws_timeout) || $ws_timeout < 10)   { $ws_timeout   = 10;}
                if (!isset ($ws_user_agent) )                   { $ws_user_agent= $_SERVER['HTTP_USER_AGENT'];}
                ws_message(ws_debug_times('module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'):'),true);
                ws_message($ws_script_environment.' ('.__LINE__.'): data loaded from '.$url.' -->',false , $ws_msg_string );		
                $ch     = curl_init();                                  // initialize a cURL session
                curl_setopt ($ch, CURLOPT_URL, $url);                   // connect to provided URL
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);           // verify peer off,removes a lot of errors with older hosts
                curl_setopt ($ch, CURLOPT_USERAGENT, $ws_user_agent);   // most host checks this nowadays
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $ws_timeout); // connection timeout
                curl_setopt ($ch, CURLOPT_TIMEOUT, $ws_timeout);        // data timeout
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);        // return the data transfer
                curl_setopt ($ch, CURLOPT_NOBODY, false);               // do the download request without getting the body
                curl_setopt ($ch, CURLOPT_HEADER, false);               // include header information
                if (! isset ($ws_curl_follow) ) {$ws_curl_follow = 1;}
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $ws_curl_follow); // follow Location: redirect allowed
                curl_setopt($ch, CURLOPT_MAXREDIRS, 1);                 //   but only one time
                $rawData= curl_exec ($ch);
                ws_message(ws_debug_times('module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'):'),true);
                $cinfo  = curl_getinfo($ch);    // get info on curl exec.
                $errors = curl_error($ch);
                curl_close ($ch);
                unset ($ch);
                $Debug  = " HTTP stats: " ." RC=".$cinfo['http_code'] ;
                if(isset($cinfo['primary_ip']))         {  $Debug .= " dest=".$cinfo['primary_ip'];}
                if(isset($cinfo['primary_port']))       {  $Debug .= " port=".$cinfo['primary_port'] ;}
                if(isset($cinfo['local_ip']))           {  $Debug .= " (from sce=" . $cinfo['local_ip'] . ")";}
                $Debug .= " Times:" 
                        ." dns=".sprintf("%01.3f",round($cinfo['namelookup_time'],3))
                        ." conn=".sprintf("%01.3f",round($cinfo['connect_time'],3))
                        ." pxfer=".sprintf("%01.3f",round($cinfo['pretransfer_time'],3));
                if($cinfo['total_time'] - $cinfo['pretransfer_time'] > 0.0000) 
                     {  $Debug .= " get=". sprintf("%01.3f",round($cinfo['total_time'] - $cinfo['pretransfer_time'],3));}
                $Debug .= " total=".sprintf("%01.3f",round($cinfo['total_time'],3)) 
                        . " secs ";
                ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__. '('.__LINE__.'): '.$Debug.' -->',true);
                ws_message($ws_script_environment.' ('.__LINE__.'): Error  codes:'.print_r ($errors,true).' -->',true);
                if (strlen($rawData) > 2) { return $rawData;}
        #        echo '<p><b>No data</b> was retrieved for '.$url.'</p>'.PHP_EOL;
                return false;}  // eo curl function 
    } // eo check ws_curl
    if (!function_exists ('ws_debug_times') ){
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
    }  // eo exists ws_debug_times?
    if (!function_exists ('ws_debug_info') ){
        function ws_debug_info($start = '') {
                global $missingTrans, $ws_start_time, $ws_passed_time, $lang;
                if (isset ($missingTrans) && is_array ($missingTrans) && count ($missingTrans) > 0 )
                     {  echo '<!-- Missing translations for '.$lang.PHP_EOL;
                        foreach ($missingTrans as $key => $value)
                             {  echo 'langlookup|'.$key.'|'.$key.'|'.PHP_EOL;}
                        echo ' -->'.PHP_EOL;}
                ws_message(ws_debug_times('module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'):'),true);
                $size   = memory_get_peak_usage(true);
                $unit   = array('b','kb','mb','gb','tb','pb');
                $used   = round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
                $seconds= microtime(true) - $ws_start_time;
                ws_message('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): Max memory used: '.$used.'('.$size.' bytes). Duration: '.round($seconds,5).' seconds -->',true);
        }
    } // eo exists ws_debug_info

    if (!function_exists ('ws_css_insert') ){
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
     } // eo missing ws_css_insert
    if (!function_exists ('langtrans') ){
     function langtrans ( $item ) {
	$trans  = langtransstr ( $item ); 
	echo $trans;}  // eof langtrans  Translate and echo   
}     // eo missing langtrans    
    if (!function_exists ('langtransstr') ){
   function langtransstr ( $item ) {
	global $LANGLOOKUP,$missingTrans, $langLower;
	if ($langLower) {$string = trim(strtolower($item));} else {$string = trim( $item );}
	if (isset($LANGLOOKUP[$string])) {		// did we have a translation
		$string = $LANGLOOKUP[$string];
		return $string;
	}
	if (!$langLower) {				// try lowercase input
		$string = trim(strtolower( $item ));
		if (isset($LANGLOOKUP[$string])) {
			$string = $LANGLOOKUP[$string];
			return $string;
		}
	}
	if(isset($string) and $string <> '') {		// no translation found
		$string = trim($item);
		$missingTrans[$string] = true; 
	}
	return $string;} // langtransstr Translate string
} // eo missing langtransstr
#-----------------------------------------------
#                       Language array construct  
#       This is NOT a function and only executed 
#                  once at load of the script !
#-----------------------------------------------
        global $LANGLOOKUP,$missingTrans, $langLower, $charset, $wu_fldr ,$lang, $ws_msg_string;  # echo __LINE__.print_r($lang,true); exit;
        if (!is_array ($LANGLOOKUP) )
             {  ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Creating lang translate array for '.$lang.' -->',true,$ws_msg_string);
                $LANGLOOKUP     = array();	// array with FROM and TO languages
                $missingTrans	= array();}	// array with strings with missing translation requests
        $langfile	= $wu_fldr.'wulanguage-'.$lang.'.txt';
        if (!file_exists($langfile) ) 
             {  ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Langfile '.$langfile.' does not exist -->',true,$ws_msg_string);
                $langfile = $wu_fldr.'wulanguage-en.txt';}
        if (!file_exists($langfile) )
             {  ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'):no language files found -->',true,$ws_msg_string);
                return;}
        ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Trying to load langfile '.$langfile.'  -->',false,$ws_msg_string);
	$loaded         = $nLanglookup = $skipped = $invalid = 0;
	$lfile 		= file($langfile);		// read the file
	if (!isset ($langLower) ) {$langLower = false;}
	if (!isset ($charset) )   {$charset   = 'UTF-8';}
	foreach ($lfile as $rec) 
	     {  $loaded++;
		$recin = trim($rec);
		list($type, $item,$translation) = explode('|',$recin . '|||||');
		if ($type <> 'langlookup') {$skipped++; continue;}
		if ($item && $translation) 
		     {  $translation	= trim($translation);
			$item 		= trim($item);
			if ($charset <> 'UTF-8')        {$translation   = iconv("UTF-8",$charset.'//TRANSLIT', $translation);}
			if ($langLower) 	        {$translation   = strtolower($translation);}
			$LANGLOOKUP[$item]      = $translation;
			$nLanglookup++;} 
		else {  $invalid++; }  // eo is langlookup
	        }  // eo for each lang record
	 ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): loaded: '.$loaded.' - skipped: '.$skipped.' - invalid: '.$invalid.' - used: '.$nLanglookup.' entries of file '.$langfile.' -->',false,$ws_msg_string);


} // eof load_missing_wustart
