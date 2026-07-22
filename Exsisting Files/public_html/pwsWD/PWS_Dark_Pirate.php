<?php $scrpt_vrsn_dt  = 'PWS_Dark_Pirate.php|01|2023-02-23|';  # remove error + replacement for darksky users
#
header('Content-type: application/json; charset=UTF-8');
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
$stck_lst       .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------------  load the settings
# load settings when run stand-alone
$scrpt          = 'PWS_settings.php';
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';
$stck_lst       = basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#
if (!isset ($dark_alt_vrs) || $dark_alt_vrs <> 'pw')
     {  echo '<small style="color: red;">First run easyweather setup</small>'; die();}
$pw_api = $dark_apikey;
#
$pw_lngs= array('en');                  // known supported languages 2023-01-20
$rq_lng = substr($locale_wu,0,2);       // user language
if (!in_array($rq_lng,$pw_lngs) ) 
     {  $pw_lng = 'en';}
else {  $pw_lng = $rq_lng;}
#
$pw_fct = $fl_folder.'pirate'.'_'.$darkskyunit.'_'.$pw_lng.'.json';  #echo $pw_fct; die();

$pw_url =  'https://api.pirateweather.net/forecast/'.$pw_api.'/'
                .$lat.','.$lon.'?'
                .'lang='.$pw_lng.'&'
                .'units='.$darkskyunit ;  

$pw_load= true;
$pw_age = 600;          // API accesses / month 20.000
if (    file_exists ($pw_fct) 
     && $pw_age > (time() - filemtime ($pw_fct))  )
     {  $pw_load= false; }
if ( $pw_load == true )
     {  file_get_contents_curl ($pw_url); 
        if ($errors <> '')
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be loaded'.PHP_EOL;
                return false;}
        #
        $lngth  = file_put_contents($pw_fct, $rawdata); 
        if ( (int) $lngth == 0) 
             {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Data could not be saved'.PHP_EOL;}      
        }
else {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Existing data used'.PHP_EOL;
        $rawdata= file_get_contents ($pw_fct);}  
#
if ($pw_lng == $rq_lng)
     {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  No translations necessary'.PHP_EOL;
        } # 2023-02-23  return true;}
else {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Texts will be translated from '.$pw_lng.' to '.$rq_lng.PHP_EOL;}


#
$pw_arr = json_decode ($rawdata, true); unset ($rawdata);
if (!is_array($pw_arr) )
     {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  No correct data loaded, will try to use old data '.PHP_EOL;
        return false;}
$missing        = '';
$should_exist  = array ('currently', 'hourly', 'daily', 'flags');
foreach ($should_exist as $check)
if ( !array_key_exists ($check,$pw_arr) ) 
     {  $missing  .=  $check.' ';}
if ($missing <> '')
     {  $stck_lst .=  basename(__FILE__).' ('.__LINE__.')  Missing '.$missing.' data, will try to use old data '.PHP_EOL;
        return false;}
#echo __LINE__.'halt <pre>'.$stck_lst; die();
#
# currently
if (array_key_exists('summary',$pw_arr['currently']) ) 
     {  $pw_arr['currently']['summary'] = lang($pw_arr['currently']['summary']);}
else {  $pw_arr['currently']['summary'] = '';}
# minutely
if (array_key_exists('summary',$pw_arr['minutely']) ) 
     {  $pw_arr['minutely']['summary']  = lang($pw_arr['minutely']['summary']);}
else {  $pw_arr['minutely']['summary']  = '';}
# hourly
if (array_key_exists('summary',$pw_arr['hourly']) ) 
     {  $pw_arr['hourly']['summary']    = lang($pw_arr['hourly']['summary']);}
else {  $pw_arr['hourly']['summary']    = '';}
#
if (    array_key_exists('data',$pw_arr['hourly'])
     && is_array ($pw_arr['hourly']['data']) )
     {  foreach ($pw_arr['hourly']['data'] as $key => $arr)
             {  if ( array_key_exists('summary', $arr) )
                $pw_arr['hourly']['data'][$key]['summary'] = lang ($arr['summary']);
                }
        }
# daily
if (array_key_exists('summary',$pw_arr['daily']) ) 
     {  $pw_arr['daily']['summary']     = lang($pw_arr['daily']['summary']);}
else {  $pw_arr['daily']['summary']     = '';}
#
if (    array_key_exists('data',$pw_arr['daily'])
     && is_array ($pw_arr['daily']['data']) )
     {  foreach  ($pw_arr['daily']['data'] as $key => $arr)
             {  if ( array_key_exists('summary', $arr) )
                $pw_arr['daily']['data'][$key]['summary'] = lang ($arr['summary']);
                }
        }



#echo __LINE__.'<pre>'.print_r($pw_arr); die();
  
echo json_encode($pw_arr);

function file_get_contents_curl ($url,$false=false)
     {  global $stck_lst, $rawdata, $errors;  
	$ch     = curl_init();                                  // initialize a cURL session
	curl_setopt ($ch, CURLOPT_URL, $url);                   // connect to provided URL
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);           // verify peer off,removes a lot of errors with older hosts
#	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   // most host checks this nowadays
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);           // connection timeout
        curl_setopt ($ch, CURLOPT_TIMEOUT, 5);                  // data timeout
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);        // return the data transfer
        curl_setopt ($ch, CURLOPT_NOBODY, false);               // do the download request without getting the body
        curl_setopt ($ch, CURLOPT_HEADER, false);               // include header information
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);            // follow Location: redirect allowed
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);                 //   but only one time
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	$rawdata= curl_exec ($ch);  
	$info	= curl_getinfo($ch);
        $errors = curl_error($ch);
	curl_close ($ch);
        unset ($ch);
        $stck_lst .=  basename(__FILE__).' ('.__LINE__.')<pre>'.print_r($errors,true).PHP_EOL.print_r($info,true).PHP_EOL.'</pre>'.PHP_EOL;
} // eof file_get_contents_curl
