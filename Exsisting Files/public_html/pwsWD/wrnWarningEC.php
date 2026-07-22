<?php  $scrpt_vrsn_dt  = 'wrnWarningEC.php|01|2020-11-04|';  # release 2012_lts
#
# Display a list of warnings from  Env. Canada
# used in Advisory box top left   
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
#
#-----------------------------------------------------------------------------------------
# these are your settings for retrieving information 
# from https://dd.meteo.gc.ca/citypage_weather/xml/ON/s0000024_e.xml
#-----------------------------------------------------------------------------------------
#
$myfolder       = './';                         
#
$warnings	= $weatheralarm;		// do we want to print warnings
$warnArea	= $alarm_area;		        //      the area we want the warnings for
# -----------------------for testing
#$province       = 'ON'; #
#$warnArea	= 's0000024';  # for testing
# -----------------------for testing
#
if ($warnings <> 'canada') 
     {  echo '<!-- No warnings wanted in script check settings as they conflict -->'.PHP_EOL;
        return false;}
#
if (substr($used_lang,0,2) == 'fr') { $x = 'f';} else { $x = 'e';}

$cacheFile      = './jsondata/ec_'.$province.'_'.$warnArea.'_'.$x.'.xml';
$warnPage	= './index.php?frame=weatheralarms';        //      or the exact name of the page to load in saratoga
#
$wrnStrings     = '<!-- warnings and other info goes here  -->'.PHP_EOL;
#
$testFile       = ''; #'./jsondata/ec-test.xml'; #./jsondata/ec_ON_s0000024_e.xml';
$rawData        = '';
#
if (isset($testFile) && $testFile <> '') 
     {  $wrnStrings     .= '<!-- '.basename(__FILE__).' ('.__LINE__.') TESTING   Severe weatherdata loaded from test file: '.$testFile.'  -->'.PHP_EOL;
        $rawData        = file_get_contents($testFile);} 
else {  $rawData        = file_get_contents($cacheFile);}

if ($rawData == '') 
     {  $wrnStrings     .= '<!-- '.basename(__FILE__).' ('.__LINE__.')  No data could be loaded for '.$cacheFile.' -->'.PHP_EOL;
        return false; }
#
$data   = wrn_check_xml();

if ($data === false)
     {  $wrnStrings     .= '<!-- '.basename(__FILE__).' ('.__LINE__.')  No valid data available -->'.PHP_EOL;
        echo $wrnStrings;
        return false;}   # echo '<pre>'.print_r($data,true);

echo $wrnStrings;

if (!array_key_exists ('warnings',$data) || count ($data['warnings']) < 1) 
     {  $wrnStrings     .= '<!-- '.basename(__FILE__).' ('.__LINE__.') No warnings in data   -->'.PHP_EOL;
        echo $wrnStrings; 
        return false; }
#print_r($data); exit;
$warnings       = $data['warnings']; 
$lnk_url_EC     = (string) $data['warnings']['url']; 
$lnk_url_EC     = str_replace ('http://','https://',$lnk_url_EC);
echo '<!-- url='.print_r( $lnk_url_EC, true).' -->'.PHP_EOL;
$n              = -1; // nr of valid warnings;
$maxType	= -1;
$maxPrio	= -1;  # echo '<pre>2'.print_r($warnings,true);
$validWarningTypes			= array ();
$validWarningTypes['ended']		= -1;
$validWarningTypes['advisory']		= 2;
$validWarningTypes['warning']		= 1;
$validWarningTypes['watch']		= 0;
$validWarningTypes['statement']		= 0;
$validWarningPriorities			= array ();
$validWarningPriorities['low']		= 0;
$validWarningPriorities['medium']	= 1;
$validWarningPriorities['high']		= 2;
$validWarningPriorities['urgent']	= 3;
$validWarningcolors			= array ('yellow', 'orange', 'red');
$validWarningtextclr			= array ('black',  'white',  'white');

foreach ($warnings->event as  $arr) 
     {  $type   = (string) $arr['type'];
        if ($type == 'ended') { continue;  }
        $n++;
        if (isset ($validWarningTypes[$type]) 
              &&   $validWarningTypes[$type] > $maxType) 
             {  $maxType = $validWarningTypes[$type];}       
        $priority= (string) $arr['priority'];
        if (isset ($validWarningPriorities[$priority]) 
              &&   $validWarningPriorities[$priority] > $maxPrio) 
             {  $maxPrio = $validWarningPriorities[$priority];}
        $descr  = (string) $arr['description'];   
        }
if ($n < 0) 
     {  $wrnStrings     .= '<!-- '.basename(__FILE__).' ('.__LINE__.') No active warnings in data   -->'.PHP_EOL;
        echo $wrnStrings; 
        return false; }
$bcolor  = $validWarningcolors[$maxType];
$tcolor  = $validWarningtextclr[$maxType];
$wrnStrings     .= '<div style="text-align: center; position: absolute;top: 18px; left: 0px; width: 100%; height: 60px;  font-size: 12px; color: '.$tcolor.'; background-color: '.$bcolor.';">
<div style="margin-top: 4px;"><b>Environment Canada</b><br />';
if ($n > 0)    { $wrnStrings    .=  'multiple warnings' ;}
 else          { $wrnStrings    .=  $descr ;}
$wrnStrings    .=  '
<br />
<a href="'.$warnPage.'&url='.$lnk_url_EC.'">
<svg id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="'.$tcolor.'" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>
</a>
</div>
</div>';       #  echo $wrnStrings; 
return true;

#-----------------------------------------------
#  check xml
#-----------------------------------------------
function wrn_check_xml()
     {  global $wrnStrings, $rawData;
	$data_ok= true;
	$data   = trim($rawData);		
	libxml_use_internal_errors(true);
	libxml_clear_errors();
	$doc    = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($data);
	$errors = libxml_get_errors();
	unset ($doc);
	if(!empty($errors))
	     {  foreach(libxml_get_errors() as $error) 
	             {  $wrnStrings .= '<!-- '.basename(__FILE__).' ('.__LINE__.') rawData error '.trim($error->message).' -->'.PHP_EOL;}
		return false;}
#
        libxml_clear_errors();
        $data = new SimpleXMLElement(trim($rawData) );
        if ($data === false) 
             {  $wrnStrings .= '<!-- '.basename(__FILE__).' ('.__LINE__.')  errors processing xml  -->';
                foreach(libxml_get_errors() as $error) 
                     {  $wrnStrings .= '<!-- '.basename(__FILE__).' ('.__LINE__.')  error '.$error->message.' -->'.PHP_EOL;}
                libxml_clear_errors();
                return false;}
#
	return (array) $data;	
} // eo wrn_check_xml
