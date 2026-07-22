<?php $scrpt_vrsn_dt  = 'wrnWarningAU.php|01|2020-11-04|';  # release 2012_lts
#
# Used in advisory box to load AU-warnings
#
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
$pageName	= 'wrnWarningAU.php';
$pageVersion	= '0.00 2019-05-09';        # 2016-01-21';    
$pageUpdated	= 'HWS version '; 
#--------------------------------------- History
# 0.00 2016-01-21 first version	 2.6/7/8 support 
# 0.00 2019-05-09 adapted for HWS-template	
# ------------------------------------- SETTINGS
#       styling for the box in which the warning is displayed
$styleBox       = 'margin: 5px; border-radius: 5px; border: 1px solid grey; border-bottom: 3px solid grey; color: black; text-align: center; overflow: hidden; min-height: 15px;';
$styleH3        = 'style = ""';
#$styleH3        = 'class="blockHead"';  // example Leuven-template
# 
#       color for message box
$white          = '#fff';       // Missing, insufficient, outdated or suspicious data.
$yellow         = '#FFFD38';    // The weather is potentially dangerous for outside activities.
$green          = '#99ff99';    // The weather provides no immediate danger.
#
$warnMsgColor   = $yellow;      // or any color you specify  with '#123456' or 'transparent'
# ----------------------------------------------------------------------
#           REALLY , I mean it:         do not change below this point
# ----------------------------------------------------------------------
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion.' | '.$pageUpdated;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
$SITE['wsModules'][$pageLoaded]	= $string;
$stck_lst      .= basename(__FILE__)." (".__LINE__.") version =>".$pageLoaded.' '.$string.PHP_EOL;       // save list of loaded scrips;
$wrnStrings     = '<!-- loaded script: '.$pageLoaded.' '.$string.' -->'.PHP_EOL;
# ----------------------------------------------------------------------
$my  = basename(__FILE__);
#-----------------------------------------------------------------------
$au_warning_urls        = array();              
$au_warning_urls['nsw'] = 'http://www.bom.gov.au/fwo/IDZ00054.warnings_nsw.xml';  # http://www.bom.gov.au/fwo/IDZ00054.warnings_nsw.xml
$au_warning_urls['vic'] = 'http://www.bom.gov.au/fwo/IDZ00059.warnings_vic.xml';
$au_warning_urls['qld'] = 'http://www.bom.gov.au/fwo/IDZ00056.warnings_qld.xml';
$au_warning_urls['wa']  = 'http://www.bom.gov.au/fwo/IDZ00060.warnings_wa.xml';
$au_warning_urls['sa']  = 'http://www.bom.gov.au/fwo/IDZ00057.warnings_sa.xml';
$au_warning_urls['tas'] = 'http://www.bom.gov.au/fwo/IDZ00058.warnings_tas.xml';
$au_warning_urls['nt']  = 'http://www.bom.gov.au/fwo/IDZ00055.warnings_nt.xml';
#
$test                   = false;
$au_warning_refetch     = 900;  // fetch every 15 minutes
#$au_warning_refetch     = 10000000000; # ----------------------for test
$warningFailed          = true; // print message as warnings site is unavailable!
$warnTable              = 'style= "border-collapse: collapse; width: 100%; color: black;text-align: center;"';
$wrnBox                 = 'style= "'.$styleBox;
# -------------------------2019-05-09
#$auwarningURL           = 'http://www.bom.gov.au/'.$SITE['warnArea'].'/warnings/';
#$warnImg	        = $SITE['imgDir'].'wrnImages/warn_';
#$infoImg                = $SITE['imgDir'].'wrnImages/i_symbol.png';
#$warningDetail          = intval ($SITE['warningDetail']);      // maximum number of warnings before we switch to one warning with automatic expansion
#if ($warningDetail < 1 ) {$warningDetail = 1;} 
#$warn1Box	        = $SITE['warn1Box'];	                // true = put all warnings in one box;  false = one box for every warning
#
if (isset ($weatheralarm) && $weatheralarm == 'au')
     {  $SITE['warnings'] = true; 
        $SITE['warnArea'] = $alarm_area;}
else {  $return = 'No warnings wanted in script.  Check settings as they conflict ';
        return $return; }
#
$myfolder       = './';
$warnImg	= $myfolder.'wrnImages/warn_';
$infoImg        = $myfolder.'wrnImages/i_symbol.png'; 
$warningDetail	= 999;
$warn1Box	= true;	
$warningGreen   = false; 
$auwarningURL   = 'http://www.bom.gov.au/'.$SITE['warnArea'].'/warnings/';
$warnPage	= './index.php?frame=weatheralarms';    $warnPage	=  $auwarningURL.'"'.' target = "_blank';   
$cacheDir	= $myfolder.'jsondata/';
# -------------------------2019-05-09
#-----------------------------------------------------------------------
# Load warning from rss feed based  on code for urls array
#  or use testfile is second param is set
#-----------------------------------------------------------------------
if (!array_key_exists ($SITE['warnArea'], $au_warning_urls) )
     {  $error  = '$SITE["warnArea"] contains inalid are: '.$SITE['warnArea'];
        $return = 'Unknown warn-area '.$SITE['warnArea'].'. Check your settings';
        return $return; }
#
$arr_warnings   = au_warning_get ($SITE['warnArea'],$test);
#
if ($arr_warnings == false) 
     {  if ($warningFailed <> true) 
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.'): no warnings retrieved, script ends '.PHP_EOL;
                return false;}
	$wrnStrings .= '<div '.$wrnBox.' background-color: white;">
<p style="margin: 2px;">There is a communication problem. Go directly to the BoM site <a href="'.$auwarningURL.'" target="_blank">www.bom.gov.au/</a> and check the Weather-warnings there.</p>
</div>'.PHP_EOL;
	return true;}  
#
# print_r ($arr_warnings); exit;
#
if ($arr_warnings['warn'][0]['title'] == 'no warnings') 
     {  $stck_lst      .= $my.' ('.__LINE__.'): no warnings in order, leaving script'.PHP_EOL;
        return false;}
#
#-----------------------------------------------------------------------------------------
# do processing for each warning and store texts in array
$wrnRows	= array ();			// every warning is first printed to array	
$arr            = $arr_warnings['warn'];
$count          = count($arr);
#
$arr_events['green']    = '01';
$arr_events['wind']     = '11';
$arr_events['snow']     = '12';
$arr_events['ice']      = '12';
$arr_events['thunder']  = '13';
$arr_events['fog']      = '14';
$arr_events['high temp']= '15';
$arr_events['low temp'] = '16';
$arr_events['fire']     = '18';
$arr_events['avalanche']= '19';
$arr_events['rain']     = '20'; 
$arr_events['flood']    = '122';
#

for ($n = 0; $n < $count; $n++) {	// for every warning
#       check type warning
        $string = strtolower ($arr[$n]['title']);
        $img    = '03';
        foreach ($arr_events as $key => $nr_img) 
             {  $pos    = strpos('..'.$string,$key);
                if ($pos > 0) 
                     {  $img    = $nr_img;
                        break;}
        }
#       make row for table
        $string = '';
        $string .= '<td style="width: 50px;"><img src="'.$warnImg.$img.'.gif" alt="" style="vertical-align: bottom;" /></td>'.PHP_EOL;
        $string .= '<td>'.$arr[$n]['title'].'</td>'.PHP_EOL;
        $wrnHref   = '<a href="'.$warnPage.'" target="_blank">'; 
        $string .= '<td style="width: 50px;">'.$wrnHref.'</a></td>'.PHP_EOL;  
        $wrnRows[$n]    = $string;     
} // eo for every warning
#
#-----------------------------------------------------------------------------------------
#               one compressed box with 1 line for all warnings
#                       generated when  max allowed is less than nummber of warnings
if ($warningDetail  < $count) {
	$firstText	= 'There are multiple Weather-warnings';
	$secondText	= 'Click here for more information';
	$warncolor      = ' background-color: '.$yellow;
        $wrnStrings     .= '<!-- box with 1 line for all warnings plus javascript extension -->'.PHP_EOL;
	$wrnStrings	.= '<!-- compressed warnings -->'.PHP_EOL;
	$wrnStrings     .= '<div '.$wrnBox.$warncolor.'" >'.PHP_EOL;
	$wrnStrings     .= '<span>'.$firstText.'&nbsp;<a href="javascript:hideshow(document.getElementById(\'warnExtra\'))">';
	$wrnStrings     .= '<img src="'.$infoImg.'" alt=" " style="vertical-align: bottom; padding: 2px; width: 18px;"/></a>&nbsp;';
	$wrnStrings     .= $secondText.'</span>'.PHP_EOL;
	$wrnStrings     .= '</div>
<script>
  function hideshow(which){
    if (!document.getElementById)
    return
    if (which.style.display=="block")
    which.style.display="none"
    else
    which.style.display="block"
  }
</script>'.PHP_EOL;
	$wrnStrings     .= '<div id="warnExtra" style="display:none;">'.PHP_EOL;
	for ($i = 0; $i < $count; $i++) {	
	        $wrnStrings     .= '<div '.$wrnBox.$warncolor.'" >'.PHP_EOL;
	        $wrnStrings     .= '<table '.$warnTable.'>'.PHP_EOL;
		$wrnStrings     .= '<tr>'.PHP_EOL.$wrnRows[$i].'</tr>'.PHP_EOL;
		$wrnStrings     .= '</table>'.PHP_EOL;
		$wrnStrings     .= '</div>'.PHP_EOL;
	}  // eo for loop every warning	
	$wrnStrings     .= '</div>'.PHP_EOL;
	$wrnStrings     .= '<!-- eo compressed warnings -->'.PHP_EOL;
	return true;
} // eo compressed box
#
#-----------------------------------------------------------------------------------------
#               generate all information one warning per box
#                       generated when no combination box is set 
if (!isset ($warn1Box) || $warn1Box == false) {
        $wrnStrings     .= '<!-- one warning per box -->'.PHP_EOL;
	for ($i = 0; $i < $count; $i++) {	
	        $warncolor      = ' background-color: '.$yellow;
	        $wrnStrings     .= '<div '.$wrnBox.$warncolor.'" >'.PHP_EOL;
		$wrnStrings     .= '<table '.$warnTable.'>'.PHP_EOL;
		$wrnStrings     .= '<tr>'.PHP_EOL.$wrnRows[$i].'</tr>'.PHP_EOL;
		$wrnStrings     .= '</table>'.PHP_EOL;
		$wrnStrings     .= '</div>'.PHP_EOL;
	}  // eo for loop every warning	
	$wrnStrings .= '<!-- end warnings -->'.PHP_EOL;
	return;
}
#-----------------------------------------------------------------------------------------
$warnHWS = true;                                # test 2019-005-09
if (isset ($warnHWS) || $warnHWS == true)
     {  $wrnStrings    .= '<div style="text-align: center; position: absolute;top: 18px;  width: 100%;height: 60px;    font-size: 12px; background-color: '.$yellow.';">
<div style="margin-top: 4px;"><b>bom.gov.au</b> warns for<br />';
        if ($warningDetail > 1) { $wrnStrings    .=  'Multiple warnings';}
        else          { $wrnStrings    .=  lang($return[0]['types']) ;}
        $wrnStrings    .=  '
<br /><a href="'.$warnPage.'">
<svg id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%">
<path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>
</a>
</div>';  
    #    echo $wrnStrings; 
        return true;  }
#--------------------------------------------------------------------------------------------------
#               default: when no other setting was executed: 
#                       generate one box with all warnings
#
$wrnStrings     .= '<!-- one box with all warnings -->'.PHP_EOL;
$wrnStrings     .= '<div '.$wrnBox.'" >'.PHP_EOL;
$wrnStrings     .= '<table '.$warnTable.'>'.PHP_EOL;
for ($i = 0; $i < $count; $i++) {
        $warncolor      = ' background-color: '.$yellow;	
	$wrnStrings .= '<tr style="'.$warncolor.'">'.PHP_EOL.$wrnRows[$i].'</tr>'.PHP_EOL;  // no extended 
}  // eo for loop every warning	
$wrnStrings     .= '</table>'.PHP_EOL;
$wrnStrings     .= '</div>'.PHP_EOL;
$wrnStrings     .= '<!-- end warnings -->'.PHP_EOL;
return true; 
#
function au_warning_get ($area,$test='') {
        global $SITE, $stck_lst, $cacheDir;
        global $au_warning_refetch , $au_warning_urls, $au_warn_cached;
        $my     = basename(__FILE__).' -> '.__FUNCTION__;
        $stck_lst      .= $my.' ('.__LINE__.'): this function loaded '.PHP_EOL;
# overrides
        if(isset($_REQUEST['force']) and strtolower($_REQUEST['force']) == 'warning') {
                $stck_lst      .=  '<!-- '.$my.' ('.__LINE__.'): No cache used as force=warning was added to url '.PHP_EOL;
                $au_warning_refetch     = 0;
        }
        if(isset($_REQUEST['cache']) and strtolower($_REQUEST['cache']) == 'warning') {
                $stck_lst      .=  '<!-- '.$my.' ('.__LINE__.'):  Cache used as cache=warning was added to url '.PHP_EOL;
                $au_warning_refetch     = 9999999;
        }
        $url    = $au_warning_urls[$area];
        $stck_lst      .= $my.' ('.__LINE__.'): loading warnings for '.$area.PHP_EOL;
# check cache
        $data_loaded     = false;
        $au_warn_cached = $cacheDir.'/au_warn_'.$area.'.txt';
        if ($au_warning_refetch > 0 && file_exists($au_warn_cached) ) {
                $file_time      = filemtime($au_warn_cached);
                $now            = time();
                $diff           = ($now     -   $file_time);
                $stck_lst      .= $my.' ('.__LINE__.'): 
        cache file   = '.$au_warn_cached.'
        cache time   = '.date('c',$file_time).' from unix time '.$file_time.'
        current time = '.date('c',$now).' from unix time '.$now.' 
        difference   = '.$diff.' (seconds)
        diff allowed = '.$au_warning_refetch.' (seconds) '.PHP_EOL;
                if ($diff < $au_warning_refetch) {
                        $stck_lst      .= $my.' ('.__LINE__.'): Data loaded from cache. '.PHP_EOL;
                        $return   = unserialize(file_get_contents($au_warn_cached) );
# echo '<pre>'.print_r($return); exit;
                        return $return;
                } // eo valid data found
        } // eo check cache
        if ($test <> '') { 
                $stck_lst      .= $my.' ('.__LINE__.'): data will be loaded from testfile '.PHP_EOL;
                $rawhtml        = au_warning_test ();    
        } 
        else{   $stck_lst      .= $my.' ('.__LINE__.'): data will be loaded from '.$url.PHP_EOL;
                $rawhtml        = au_warn_curl($url);
        }
        if (empty($rawhtml)){
                return false;
        }
#
        $xml    = simplexml_load_string($rawhtml);
#
# echo '<pre>'.print_r($xml);
#  
        $returnArray    = array ();
        $count = count ($xml->channel);
        if ($count < 1) {
                $stck_lst      .= $my.' ('.__LINE__.'): no channel found in xml '.PHP_EOL;        
                $returnArray['warn'][0]['title']       = 'no warnings';
                au_warn_to_cache ($returnArray);
                return $returnArray;
        }
        $returnArray['msg']     = (string) $xml -> channel -> description;
        $returnArray['date']    = (string) $xml -> channel -> pubDate;
        $returnArray['area']    = $area;
        $returnArray['url']     = $url;    
#        
        $count = count ($xml->channel->item);
        if ($count < 1) {
                $stck_lst      .= $my.' ('.__LINE__.'): no items found in xml '.PHP_EOL;       
                 $returnArray['warn'][0]['title']       = 'no warnings';
                au_warn_to_cache ($returnArray);
                return $returnArray;
        }

        for ($n = 0; $n < $count; $n++) {
                $stck_lst      .= $my.' ('.__LINE__.'): processing '.$count.' items, item # '.$n.PHP_EOL;   
                $item = $xml->channel->item [$n];

                $returnArray['warn'][$n]['title']       = (string) $item -> title;
                $returnArray['warn'][$n]['link']        = (string) $item -> link;
                $returnArray['warn'][$n]['pubDate']     = (string) $item -> pubDate;
                $returnArray['warn'][$n]['guid']        = (string) $item -> guid;
        }
        au_warn_to_cache ($returnArray);
        return $returnArray; 


} // eof au_warning_get

function au_warn_curl ($fullurl) 
     {  global $stck_lst;
        $my     = basename(__FILE__).' -> '.__FUNCTION__;
        $stck_lst      .= $my.' ('.__LINE__.'): this function loaded '.PHP_EOL;
        $ch             = curl_init();
        curl_setopt     ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt     ($ch, CURLOPT_URL, $fullurl);
        curl_setopt     ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt     ($ch, CURLOPT_TIMEOUT, 10);
        $rawdata        = curl_exec ($ch);
	$info	        = curl_getinfo($ch);
#	print_r ($info);
	$error          = curl_error($ch);
	curl_close ($ch);
	unset ($ch);
	$stck_lst      .= $my.' ('.__LINE__.'): Possible errors >'.$error.'< '.PHP_EOL;
        if (empty($rawdata)){
                $stck_lst      .= $my.' ('.__LINE__.'):  ERROR data empty for '.$fullurl.' '.PHP_EOL;       
        }
        return $rawdata;
}
function au_warn_to_cache ($returnArray) {
        global  $SITE, $au_warn_cached , $stck_lst, $cacheDir;
#
        $my     = basename(__FILE__).' -> '.__FUNCTION__;
        $stck_lst      .= $my.' ('.__LINE__.'): this function loaded '.PHP_EOL;
        
        if ($cacheDir   == '')  {
                $stck_lst      .= $my.' ('.__LINE__.'): WARNING  no cache specified for severe weatherdata  STRONGLY ADVISED TO RECTIFY THAT '.PHP_EOL; 
                return false;
        }
        if (file_put_contents($au_warn_cached, serialize($returnArray))){   
                $stck_lst      .= $my.' ('.__LINE__.'): Severe weatherdata ('.$au_warn_cached.') saved to cache  '.PHP_EOL;
                return;
        }
        $stck_lst      .= $my.' ('.__LINE__.'): FAILED to save weatherdata ('.$au_warn_cached.') to cache  '.PHP_EOL;
        return true;
}
function au_warning_test () {

        return   '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Weather Warnings for New South Wales / Australian Capital Territory. Issued by the Australian Bureau of Meteorology</title>
		<link>http://www.bom.gov.au/fwo/IDZ00054.warnings_nsw.xml</link>
		<atom:link href="http://www.bom.gov.au/fwo/IDZ00054.warnings_nsw.xml" rel="self" type="application/rss+xml" />
		<description>Current weather warnings for New South Wales / Australian Capital Territory, Australia including strong wind, gale, storm force and hurricane force wind warnings; tsunami; damaging waves; abnormally high tides; tropical cyclones; severe thunderstorms; severe weather; fire weather; flood; frost; driving; bushwalking; sheep graziers and other agricultural warnings.</description>
		<language>en-au</language>
		<copyright>Copyright: (C) Copyright Commonwealth of Australia 2010, Bureau of Meteorology (ABN 92637 533532), see http://www.bom.gov.au/other/copyright.shtml for terms and conditions of reuse.</copyright>
		<webMaster>webops@bom.gov.au (Help desk)</webMaster>
		<pubDate>Sat, 23 Jan 2016 08:24:38 GMT</pubDate>
		<lastBuildDate>Sat, 23 Jan 2016 08:24:38 GMT</lastBuildDate>
		<generator>Australian Bureau of Meteorology</generator>
		<ttl>10</ttl>
		<image>
			<url>http://www.bom.gov.au/images/bom_logo_inline.gif</url>
			<title>Weather Warnings for New South Wales / Australian Capital Territory. Issued by the Australian Bureau of Meteorology</title>
			<link>http://www.bom.gov.au/fwo/IDZ00054.warnings_nsw.xml</link>
		</image>
		<item>
			<title>23/16:11 EDT Marine Wind Warning Summary for New South Wales</title>
			<link>http://www.bom.gov.au/nsw/warnings/marinewind.shtml</link>
			<pubDate>Sat, 23 Jan 2016 05:11:03 GMT</pubDate>
			<guid isPermaLink="false">http://www.bom.gov.au/nsw/warnings/marinewind.shtml</guid>
		</item>
		<item>
			<title>22/09:15 EDT Flood Warning - Warrego River (NSW)</title>
			<link>http://www.bom.gov.au/cgi-bin/wrap_fwo.pl?IDN36631.html</link>
			<pubDate>Thu, 21 Jan 2016 22:15:47 GMT</pubDate>
			<guid isPermaLink="false">http://www.bom.gov.au/cgi-bin/wrap_fwo.pl?IDN36631.html</guid>
		</item>
		<item>
			<title>22/09:50 EDT Flood Warning - Paroo River (NSW)</title>
			<link>http://www.bom.gov.au/cgi-bin/wrap_fwo.pl?IDN36632.html</link>
			<pubDate>Thu, 21 Jan 2016 22:50:48 GMT</pubDate>
			<guid isPermaLink="false">http://www.bom.gov.au/cgi-bin/wrap_fwo.pl?IDN36632.html</guid>
		</item>
		<item>
			<title>23/19:16 EDT Severe Thunderstorm Warning (NSW)</title>
			<link>http://www.bom.gov.au/products/IDN65156.shtml</link>
			<pubDate>Sat, 23 Jan 2016 08:16:39 GMT</pubDate>
			<guid isPermaLink="false">http://www.bom.gov.au/products/IDN65156.shtml</guid>
		</item>
	</channel>
</rss>';
/*
return   '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Weather Warnings for Tasmania. Issued by the Australian Bureau of Meteorology</title>
		<link>http://www.bom.gov.au/fwo/IDZ00058.warnings_tas.xml</link>
		<atom:link href="http://www.bom.gov.au/fwo/IDZ00058.warnings_tas.xml" rel="self" type="application/rss+xml" />
		<description>Current weather warnings for Tasmania, Australia including strong wind, gale, storm force and hurricane force wind warnings; tsunami; damaging waves; abnormally high tides; tropical cyclones; severe thunderstorms; severe weather; fire weather; flood; frost; driving; bushwalking; sheep graziers and other agricultural warnings.</description>
		<language>en-au</language>
		<copyright>Copyright: (C) Copyright Commonwealth of Australia 2010, Bureau of Meteorology (ABN 92637 533532), see http://www.bom.gov.au/other/copyright.shtml for terms and conditions of reuse.</copyright>
		<webMaster>webops@bom.gov.au (Help desk)</webMaster>
		<pubDate>Sat, 23 Jan 2016 12:17:02 GMT</pubDate>
		<lastBuildDate>Sat, 23 Jan 2016 12:17:02 GMT</lastBuildDate>
		<generator>Australian Bureau of Meteorology</generator>
		<ttl>10</ttl>
		<image>
			<url>http://www.bom.gov.au/images/bom_logo_inline.gif</url>
			<title>Weather Warnings for Tasmania. Issued by the Australian Bureau of Meteorology</title>
			<link>http://www.bom.gov.au/fwo/IDZ00058.warnings_tas.xml</link>
		</image>
	</channel>
</rss>';
*/
}