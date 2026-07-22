<?php  $scrpt_vrsn_dt  = 'wrnWarningUK.php|01|2021-01-22|';  # release 2012_lts
#
# Display a list of warnings from  metealarm.eu
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).'('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#  ------------------------------------ settings 
$warnDebug      = true;      
$cache_max_age  = 900;
$detail_page    = true;
$detail_page_url= './index.php?frame=weatheralarms'; 
#
#  --------------------------------- test values 
#$weatheralarm   = 'uk';
#$alarm_area       = 'ee'; $alarm_area
#$warnDebug      = true;      
#
# styling
# -------------------------------------- styling
#      
$warncolors[0]  = '#fff';               
$warncolors[1]  = '#FBEA55'; 
$warncolors[2]  = '#F19E39'; 
$warncolors[3]  = '#BB2739'; 
#
# --------------include warnings on every page ?
if ($weatheralarm <> 'uk') 
     {  echo 'error '.__LINE__.PHP_EOL;
        return false;}
#
$warn_cache     = './jsondata/warningUK_'.$alarm_area;                                   
$warn_url	= 'https://www.metoffice.gov.uk/public/data/PWSCache/WarningsRSS/Region/'.$alarm_area; 
$fl_to_load     = 'UK_warnings'; 
#
$now            = time();
if (is_file($warn_cache) )
     {  $cache_age      = $now  - filemtime($warn_cache);}
else {  $cache_age      = $now;}
#
if ($cache_age > $cache_max_age)
     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') loading from UK-site as file is '.$cache_age.' seconds old'.PHP_EOL; 
        $start_time     =  microtime(true);
        $ch             = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$warn_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,10);        // data timeout 
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0'); #### 2020-09-30
        $result         = curl_exec ($ch);
        $info	        = curl_getinfo($ch);
        $error          = curl_error($ch);
        curl_close ($ch);
        $end            = microtime(true);
        $passed         = $end - $start_time;
        if ($passed < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($passed,4);}
        $CHECK_HTTP_CODES = array ('404', '429','502', '500');
        if (in_array ($info['http_code'],$CHECK_HTTP_CODES) ) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' - PROBLEM => http_code: '.$info['http_code'].', no valid data '.$warn_url.PHP_EOL;
                return false;} 
        if ($error <> '')
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  invalid CURL '.$error.' '.$warn_url.PHP_EOL; 
                return false;}
        else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  CURL OK for '.$warn_url.PHP_EOL; }

        $result   = trim($result);		
	libxml_use_internal_errors(true);
	libxml_clear_errors();
	$doc    = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($result);
	$errors = libxml_get_errors();
	unset ($doc);
	if(!empty($errors))
	     {  foreach(libxml_get_errors() as $error) 
	             { $stck_lst .= basename(__FILE__).' ('.__LINE__.') rawData error '.trim($error->message).PHP_EOL;}
		return false;}
#
        libxml_clear_errors();
        $result = new SimpleXMLElement(trim($result) );
        if ($result === false) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.')  errors processing xml ';
                foreach(libxml_get_errors() as $error) 
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.')  error '.$error->message.PHP_EOL;}
                libxml_clear_errors();
                return false;}               
#
	$json   = json_encode($result);
	unset ($result);
        $array  = json_decode($json,TRUE); # echo '<pre>'.print_r($array,true); exit;
        unset ($json);
        $warns  = array();
        if (!array_key_exists ('channel',$array) 
         || !array_key_exists ('item',$array['channel']) )
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.')  no warnings found ';
                $error  = file_put_contents($warn_cache,serialize($warns) ); 
                return false;}
        elseif (array_key_exists (0,$array['channel']['item']) )
             {  $warns  = $array['channel']['item'];}
        else {  $warns[0]= $array['channel']['item'];}         
        $error  = file_put_contents($warn_cache,serialize($warns) ); 
        } // eo load new data
else {  $warns  = unserialize (file_get_contents($warn_cache) );}
	
#echo '<pre>'.print_r($warns,true); 
$count  = count($warns);
if ($count == 0) {return false;}
$max_color      = 0;
$link           = '';
foreach ($warns as $arr)
     {  $title  = ' '.$arr['title'];
        $color  = substr ($title, 0, 20);
        $loc    = strpos ($title,'Yellow');
        if ($loc > 0) 
             {  if ( $max_color == 0) 
                     {  $max_color = 1;
                        $link   = $arr['link'];}
                continue;}
        $loc    = strpos ($title,'Amber');
        if ($loc > 0) 
             {  if ( $max_color < 2) 
                     {  $max_color = 2;;
                        $link   = $arr['link'];}
                continue;}
        $loc    = strpos ($title,'Red');
        if ($loc > 0) 
             {  if ( $max_color < 3) 
                     {  $max_color = 3;;
                        $link   = $arr['link'];}
                continue;}
        } // eo for each

$icon   =  '<svg style="vertical-align: bottom;" id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>';      
if ($count > 1) { $text =  'Multiple warnings' ;}
else            { $text =  'Warning';}
$wrnStrings    = '<div style="text-align: center; position: absolute;top: 18px;  width: 100%; height: 60px;  font-size: 12px; background-color: '.$warncolors[$max_color].';">
<div style="color: black;   margin-top: 4px;"><b>MetOffice</b> '.$text.'<br />';
if ($detail_page <> '') 
     {	$wrnHref 	= '<a href="'.$detail_page_url.'">'; }                         //      then we send them there on the same page
else {  $wrnHref 	= '<a href="'.$link.'" target="_blank">';}	//  on a new page		
$wrnStrings    .= $wrnHref.$icon.'
</a>
</div>
</div>';
##if ($count == 1)  ## iframe does not work as cookies are requested every time
##     {  $ownpagehtml = '<iframe src="'.$arr['link'].'" style="width: 100%; margin: 0px 0px auto; height: 1200px;">MetOffice</iframe>';
##       return true;}
$from = array (': ','valid');
$to   = array (':</td></tr>'.PHP_EOL.'<tr><td><br />','<br /><br />valid');
$ownpagehtml = '<table style="width: 80%; margin: 0 auto;">'.PHP_EOL;
foreach ($warns as $arr)
      { $ownpagehtml .= '<tr><td style="vertical-align: center;">'.PHP_EOL;
        $ownpagehtml .= '<a href="'.$arr['link'].'" target="_blank">'.$icon.'</a>'.PHP_EOL;
        
        $ownpagehtml .= str_replace($from,$to,$arr['description']).'<hr></td></tr>'.PHP_EOL;}
$ownpagehtml .= '</table>'; 
return true; 


	
	


