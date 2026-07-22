<?php   $scrpt_vrsn_dt  = 'cvt.php|01|2021-08-16|';  # indoor feel added | release 2012_lts
#
# example script when we want to use json data as extra data
# after adapting the name of the script is used in easyweather settings 
# the PWS_extra_data.php script will check if the file is php and do an include
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
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
#-----------------------------------------------
#                 where do we find the json file
$location       = './demodata/Extradata.txt';
#
#                       check if file is present
$content = false;
$content = file_get_contents($location);
if ($content == false) { echo '# '.__LINE__.' file '.$location.' not found'; return;}
#
#        check if content of file  is valid json
$json = json_decode($content, true);  # echo __LINE__.print_r($json,true).print_r($content,true);
if ($json === NULL) {echo '# '.__LINE__.' error decoding json'; return;} 
#
#               check if station data is present
if (!array_key_exists ('pws',$json) )  {echo '# '.__LINE__.' no correct data found'; return;}
$arr = $json['pws'];  # echo __LINE__.print_r($arr, true);
#
#               now we convert the needed fields 
#                      to the extrasensor format
#
if (    !isset ($weather)
     || !is_array ($weather) ) {$weather["temp_units"] = 'C';}
include_once 'PWS_shared.php';
#
$inhumi = $intemp = $infeel = 'n/a';   # not found is default   
if (array_key_exists ('inHumi',$arr) )  { $inhumi = $arr['inHumi'];} 
if (array_key_exists ('inTemp',$arr) )  { $intemp = $arr['inTemp'];} 
$infeel = heatIndex((float) $intemp,(float)$inhumi  );
#
# generate the file
$strng  = '# extra sensor-data '.date('c').PHP_EOL;
$strng .= '|fromtemp|uom|C|!
|temp_indoor|temp|'.$intemp.'|!
|humidity_indoor|hum|'.$inhumi.'|!
|temp_indoor_feel|temp|'.$infeel.'|!
';                              # echo $strng;
$extra_data = $location;
return $strng;  
