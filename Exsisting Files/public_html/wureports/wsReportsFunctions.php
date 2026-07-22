<?php
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
# VERSION       : 4.00
# Author:       : Wim van der Kuil
# Documentation 
#   and support : https://leuven-template.eu/
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) 
     {  $filenameReal = __FILE__;			
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
#-----------------------------------------------
$pageName	= 'wsReportsFunctions.php';
$pageVersion	= '4.01 2018-07-22';
$pageUpdated	= 'Release version'; 
#--------------------------------------- History
# 4.00 2017-12-16 first version	
# 4.01 2018-07-22 Release version
# ---------------------------------- Houskeeping
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion.' | '.$pageUpdated;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
ws_message ('<!-- loaded script: '.$pageLoaded.' '.$string.' -->');
#-----------------------------------------------
#              Adapted for wsreports stand-alone
#
#-----------------------------------------------
#                    function  Convert windspeed
function wsReportConvertWind($amount, $from, $to) 
     {  $amount		= (float) str_replace(',','.',$amount);  // just to make sure that the value is having a decimal point!
 	$convertArr= array (
                "kmh"=> array('kmh' => 1	, 'kts' => 0.5399568034557235	, 'ms' => 0.2777777777777778 	, 'mph' => 0.621371192237334 ),
                "kts"=> array('kmh' => 1.852	, 'kts' => 1 			, 'ms' => 0.5144444444444445 	, 'mph' => 1.1507794480235425),
                "ms" => array('kmh' => 3.6	, 'kts' => 1.9438444924406046	, 'ms' => 1 			, 'mph' => 2.236936292054402 ),
                "mph" => array('kmh' => 1.609344, 'kts' => 0.8689762419006479	, 'ms' => 0.44704 	        , 'mph' => 1 ) );   
	$return = $convertArr[$from][$to] * $amount;
	ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): input speed: '.$amount.' - unitFrom: '.$from.' - unitTo: '.$to.' - out = '.$return.' -->');	
	return $return; } // eof convert windspeed
#-----------------------------------------------
#                function  Convert baro pressure
#-----------------------------------------------
function wsReportConvertBaro($amount, $from, $to) 
     {  $amount		= (float) str_replace(',','.',$amount);  // just to make sure that the value is having a decimal point!
        $convertArr	= array (
                "mb" 	=> array('mb' => 1	, 'hpa' => 1            , 'mmhg' => 0.75006 	, 'inhg' => 0.02953 ),
                "hpa"	=> array('mb' => 1	, 'hpa' => 1            , 'mmhg' => 0.75006 	, 'inhg' => 0.02953),
                "mmhg"	=> array('mb' => 1.3332	, 'hpa' => 1.3332       , 'mmhg' => 1 		, 'inhg' => 0.03937 ),
                "inhg"	=> array('mb' => 33.864	, 'hpa' => 33.864       , 'mmhg' => 25.4 	, 'inhg' => 1) );
        $return = $convertArr[$from][$to] * $amount;
	ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): input pressure: '.$amount.' - unitFrom: '.$from.' - unitTo: '.$to.' - out = '.$return.' -->');
	return$return; } // eof convert baropressure
#-----------------------------------------------
#                     function  Convert rainfall
#-----------------------------------------------
function wsReportConvertRain($amount, $from,$to) 
     {  $amount		= (float) str_replace(',','.',$amount);  // just to make sure that the value is having a decimal point!
        $convertArr= array (
                "mm"=> array('mm' => 1		,'in' => 0.03937007874015748 	, 'cm' => 0.1 ),
                "in"=> array('mm' => 25.4	,'in' => 1			, 'cm' => 2.54),
                "cm"=> array('mm' => 10		,'in' => 0.3937007874015748 	, 'cm' => 1 ) );
        $return = $convertArr[$from][$to] * $amount;
	ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): input rain: '.$amount.' - unitFrom: '.$from.' - unitTo: '.$to.' - out = '.$return.' -->');
	return $return;} // eof convert rainfall
#-----------------------------------------------
#                   function Convert temperature
#-----------------------------------------------
function wsReportConvertTemp($amount, $from,$to) {
	global $wsDebug;
	$amount		= (float) str_replace(',','.',$amount);  // just to make sure that the value is having a decimal point!
	if ($from == 'c')
	        {$return        = 32 +(9*$amount/5);}
	else    {$return        = 5*($amount -32)/9;}
	ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): input temp: '.$amount.' - unitFrom: '.$from.' - unitTo: '.$to.' - out = '.$return.' -->');
	return $return;  } // eof convert temperature
#-----------------------------------------------
#                     function  Convert distance
#-----------------------------------------------
function wsReportConvertRun($amount, $from,$to) 
     {  $amount		= (float) str_replace(',','.',$amount);  // just to make sure that the value is having a decimal point!
        $convertArr= array (
                "km"	=> array('km' => 1			, 'mi' => 0.621371192237	, 'ft' => 3280.83989501 , 'm' => 1000 ),
                "mi"	=> array('km' => 1.609344000000865	, 'mi' => 1			, 'ft' => 5280		, 'm' => 1609.344000000865 ),
                "ft"	=> array('km' => 0.0003048		, 'mi' => 0.000189393939394	, 'ft' => 1		, 'm' => 0.30480000000029017 ),
                "m"	=> array('km' => 0.001			, 'mi' => 0.000621371192237	, 'ft' => 3.28083989501 , 'm' => 1 ) );
        $return = $convertArr[$from][$to] * $amount;
	ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.'): input distance: '.$amount.' - unitFrom: '.$from.' - unitTo: '.$to.' - out = '.$return.' -->');
	return $return;  } // eof convert distance
#-----------------------------------------------
#                    function language translate
#-----------------------------------------------
function wsReporttransstr ($string) 
     {  global $trans,  $wsReportLOOKUP, $missingTrans;	
	$value	= trim ($string);
	if (!isset ($wsReportLOOKUP[$value]) )
             {  $return		        = str_replace ($trans,'',$string);
		$missingTrans[$value]	= $return; } 
	else {  $return	= $wsReportLOOKUP[$value]; }   
	return $return; }  // eof wsReporttransstr
#-----------------------------------------------
#  function curl load data from web
#-----------------------------------------------
function wsReportCurl ($string) 
     {  ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__. '('.__LINE__.'): Weather data loaded from url: '.$string.'  -->');
	$ch     = curl_init();
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt ($ch, CURLOPT_URL, $string);
	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);         // relatively long, but wu sometimes need that 1 minute
	$rawData= curl_exec ($ch);
	$cinfo  = curl_getinfo($ch);    // get info on curl exec.
	curl_close ($ch);
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
	if (empty($rawData))
	     {ws_message ('<!-- module '.basename(__FILE__).' '.__FUNCTION__. '('.__LINE__.'): ERROR Weather data loaded from url: '.$string.' - FAILED  -->',true);
		return false;}
        return $rawData;}  // eo curl function 
#-----------------------------------------------
#  generate a complete td with correct value and color
#-----------------------------------------------
function tdGenerate ($value) 
     {  global $numFormat, $round, $noValue, $empty;
	$level	= colorLookup ($value);	 
	if ( $value <> $empty && $value <> $noValue ) 
	     {  $value	= round($value, $round);
		$value	= sprintf($numFormat,$value);}
	return '<td class="'.$level.'">'.$value.'</td>';} // eof tdGenerate
#-----------------------------------------------
#                    function find correct color
#-----------------------------------------------
function colorLookup ($value) 
     {  global $levelArr, $$levelArr, $noValue, $empty, $color, $kind;
	if (isset ($color) && $color == false) 	{$color == true; return 'level_nocolor';}
        if ($value === $noValue)                {return 'level_novalue';}
        if ($value === 0 && $kind == 'rain')    {return 'level_novalue';}
        if ($value === $empty) 	                {return 'level_empty';}
        $limit = count($$levelArr);
        for ($i = 0; $i < $limit; $i++)
             {  if ($value <= ${$levelArr}[$i]) 
                     {return 'level_'.($i); }}
        return 'level_'.($limit-1); } // eof colorLookup
#-----------------------------------------------
#                           function convert uom
#-----------------------------------------------
function convertUom ($value) 
     {  global $uomOut, $uomInput, $wsDebug;
	if ($uomOut == $uomInput) 	{ return $value; }
	if     ($uomInput	== 'c'   || $uomInput	== 'f' ) 	{$value	= wsReportConvertTemp($value, $uomInput,$uomOut);} 
	elseif ($uomInput	== 'cm'  || $uomInput	== 'in') 	{$value	= wsReportConvertRain($value, $uomInput,$uomOut);}
	elseif ($uomInput	== 'hpa' || $uomInput	== 'inhg') 	{$value	= wsReportConvertBaro($value, $uomInput,$uomOut);}
	elseif ($uomInput	== 'kmh' || $uomInput	== 'mph') 	{$value	= wsReportConvertWind($value, $uomInput,$uomOut);}
	elseif ($uomInput	== 'km'  || $uomInput	== 'mi') 	{$value	= wsReportConvertRun ($value, $uomInput,$uomOut);}
	else {echo 'Program / input error, unknown UOM in:'.$uomInput.' UOM out:'.$uomOut.'. Program halted'; exit;}
	return $value; }
#-----------------------------------------------
#                       Language array construct  
#       This is NOT a function and only executed 
#                  once at load of the script !
#-----------------------------------------------
ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Creating lang translate array -->');
$ownTranslate	= true;
$wsReportLOOKUP = array();	// array with FROM and TO languages
$missingTrans	= array();	// array with strings with missing translation requests
$langfile	= $wuLang.'wulanguage-'.$lang.'.txt';
if (!file_exists($langfile) ) 
     {  ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Langfile '.$langfile.' does not exist -->',true);
	$langfile = $wuLang.'wulanguage-en.txt';
}
ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Trying to load langfile '.$langfile.'  -->');
if (file_exists($langfile) ) 
     {  ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): Langfile '.$langfile.' loading -->');
	$loaded         = $nLanglookup = $skipped = $invalid = 0;
	$lfile 		= file($langfile);		// read the file
	foreach ($lfile as $rec) 
	     {  $loaded++;
		$recin = trim($rec);
		list($type, $item,$translation) = explode('|',$recin . '|||||');
		if ($type <> 'langlookup') {$skipped++; continue;}
		if ($item && $translation) 
		     {  $translation	= trim($translation);
			$item 		= trim($item);
			if ($charset <> 'UTF-8')        {$translation   = iconv("UTF-8",$charset.'//TRANSLIT', $translation);}
			if ($lower) 	                {$translation   = strtolower($translation);}
			$wsReportLOOKUP[$item]  = $translation;
			$nLanglookup++;} 
		else {  $invalid++; }  // eo is langlookup
	        }  // eo for each lang record
	 ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): loaded: '.$loaded.' - skipped: '.$skipped.' - invalid: '.$invalid.' - used: '.$nLanglookup.' entries of file '.$langfile.' -->');
} // eo file exist