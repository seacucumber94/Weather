<?php  $scrpt_vrsn_dt  = 'startHere.php|01|2023-05-01|';  # backup website + GD + extra info CURL + xml | release 2012_lts
/*   >
<span style="color: red;"><pre><b>
 W         W      A      RRRR    N     N  III  N     N   GGGG
 W         W     A A     R   R   N N   N   I   N N   N  G
  W   W   W     A   A    RRRR    N  N  N   I   N  N  N  G  GGG
   W W W W     AAAAAAA   R   R   N   N N   I   N   N N  G    G
    W   W     A       A  R    R  N     N  III  N     N   GGGG

If you see this text:
==============> your webserver (f.i. Apache) is not set to support PHP
    
==============> The start script for PWS_Dashboard is index.<b>php</b>
<br /><br /><br />
==============> Contact your provider and ask how to enable PHP on your webserver.

<!--   */
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
else {  ini_set('display_errors', 'On');   error_reporting(E_ALL & ~E_NOTICE ); } 
#
if (isset ($_REQUEST['info']) ) { echo '<pre>'.phpinfo().'</pre>'; exit; }
#
$tst_folder     = './jsondata/';
#
echo '<pre>This small program is used to check your web server settings if they prohibit installing the PWS_Dashboard.
<br />You only need to re-run this program if you encounter unknown errors.'.PHP_EOL;
$fatalErrors    = false;
$curlErrors     = false;
$errorKind	= '<b style="color: red;"> FATAL</b>';
#
$checkWhat 	= '$_SERVER["DOCUMENT_ROOT"]';
echo '<br />check 1   : '.$checkWhat.': result = ';
if (!array_key_exists ('DOCUMENT_ROOT', $_SERVER) ) 
     {  echo 'ERROR 1 '.$checkWhat.' is not defined, '.$errorKind.PHP_EOL;
	$fatalErrors	= true;} 
else {  echo ' OK, server = '.$_SERVER["SERVER_NAME"].PHP_EOL;}
#
$version        = phpversion();
echo '<br />check 2.1 : Your current PHP version is  : ' . phpversion().PHP_EOL;
if (substr($version,0,1) < 7)
     {  echo '            PHP 7+ is advised for this template but it will probably run also with PHP 5.6.3 or higher'
        .'<br />            You can check if your current version is supported at '
        .'<a href="https://www.php.net/supported-versions.php" target ="_blank">php.net -> supported-versions</a>'.PHP_EOL; }       
#
$checkWhat 	= 'CURL support'; 
echo '<br />check 2.2 : '.$checkWhat.': result = ';
if (!function_exists ('curl_init') ) 
     {  echo 'ERROR 2.2 '.$checkWhat.' is not supported, '.$errorKind.PHP_EOL;
	$fatalErrors	= true;
	$curlErrors     = true;} 
else {  echo ' OK'.PHP_EOL;}
#
$checkWhat 	= 'simplexml support'; 
echo '<br />check 2.3 : '.$checkWhat.': result = ';
if (!extension_loaded('simplexml') ) 
      { echo 'ERROR 2.3 '.$checkWhat.' is not supported, '.$errorKind.PHP_EOL;
     $fatalErrors	= true;} 
else {  echo ' OK'.PHP_EOL;}
#
$errorKind	= '<b style="color: red;"> WARNING</b>';
$checkWhat 	= 'GD support'; 
echo '<br />check 2.4 : '.$checkWhat.': result = ';
if (!function_exists('gd_info') )
     {  echo 'ERROR 2.4 '.$checkWhat.' is not installed, '.$errorKind.PHP_EOL;}
else {  $info = gd_info();
        echo ' OK: '.$info['GD Version'].PHP_EOL;}
#
$errorKind	= '<b style="color: red;"> FATAL</b>';
$reason		= 'open_basedir restriction in effect';
$checkWhat 	= 'file_exists';
echo '<br />check 3.1 : '.$checkWhat.': result = ';
if (!file_exists('startHere.php')) 
     {  echo 'ERROR 3.1 '.$checkWhat.' is not supported, ?'.$reason.$errorKind.PHP_EOL;
	$fatalErrors	= true;} 
else {  echo ' OK'.PHP_EOL;}
#
$checkWhat 	= 'chdir';
echo '<br />check 3.2 : '.$checkWhat.': result = ';
$errorKind	= ' WARNING';
$ret	        =  chdir ($tst_folder);
if ($ret == false) 
     {  echo 'ERROR 3.2 '.$checkWhat.' is not supported, ?'.$reason.$errorKind.PHP_EOL;} 
else {  echo ' OK'.PHP_EOL; 
        chdir ('../');}
$errorKind	= '<b style="color: red;"> FATAL</b>';
$checkWhat 	= 'file_put_contents';
echo '<br />check 3.3 : '.$checkWhat.': result = ';
$file           = $tst_folder.'test.txt';
$ret	        = file_put_contents ($file, 'this is some test data');
if ($ret == false) 
     {  echo 'ERROR 3.3 '.$checkWhat.' is not supported, ?'.$reason.'  or <b>'.$tst_folder.'</b> folder not writable'.$errorKind.PHP_EOL;
        $fatalErrors	= true; } 
else {  echo ' OK'.PHP_EOL;
        $errorKind	= ' WARNING';
        $checkWhat 	= 'chmod';
        $string =  '<br />check 3.4 : '.$checkWhat.': result = ';
        echo $string;
        $ret	= chmod ($file, '755');
        if ($ret == false) 
             {  echo 'ERROR 3.4 '.$checkWhat.' is not supported, ?'.$reason.'  or  <b>'.$tst_folder.'</b> folder not writable'.$errorKind.PHP_EOL;} 
        else {  echo ' OK'.PHP_EOL;}
        $errorKind	= ' WARNING';
        $checkWhat 	= 'unlink';
        $string         =  '<br />check 3.5 : '.$checkWhat.': result = ';
        echo $string;
        $ret	        = unlink ($file);
        if ($ret == false) 
             {  echo 'ERROR 3.4 '.$checkWhat.' is not supported, ?'.$reason.'  or <b>'.$tst_folder.'</b> folder not writable'.$errorKind.PHP_EOL;} 
        else { echo ' OK'.PHP_EOL;} 
        } 
#
$errorKind	= '<b style="color: red;"> FATAL</b>';
$checkWhat = 'json support';
echo '<br />check 4   : '.$checkWhat.': result = ';
if (function_exists('json_encode') ) 
     {  echo 'OK'.PHP_EOL; } 
else {  echo 'error. no json support in PHP found '.$errorKind.PHP_EOL;
 	$fatalErrors = true;} 
#	
$length		= 100;
function curl_test($weatherApiUrl)
      { global $rawData, $info, $error, $file_errors;
        $ch             = curl_init();
        curl_setopt($ch, CURLOPT_URL,$weatherApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,5);        // data timeout 
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0'); #### 2020-09-30
        $rawData= curl_exec ($ch);
        $info	= curl_getinfo($ch);
        $error  = curl_error($ch);
        echo '<!-- 
        error='.print_r($error,true).'  
        info='.print_r($info,true).' -->';
        curl_close ($ch); 
        $file_errors    = false;
        if (!$rawData || strlen($rawData) < 50) 
             {  echo 'ERROR '.$checkWhat.' failed, no data or to few data chars'.$errorKind.PHP_EOL
                .' data retieved:>'.$rawData.'<end of data';
                $file_errors    = true;} 
        else {  $string = substr($rawData,0,9);
                if ($string <> 'Test file') 
                     {  echo 'ERROR'.$checkWhat.' failed, invalid data'.$errorKind.PHP_EOL
                        .' data retieved:  '.substr($rawData,0,20);
                        $file_errors    = true;}
                } 
        if ($file_errors == false)
             {  echo ' OK'.PHP_EOL;}
        } // eof curl_test
#
if ($curlErrors == false) 
     {  $weatherApiUrl	= 'https://pwsdashboard.com/startHere.txt';
        $checkWhat 	= 'load file from <b style="color: blue;"><a href="'.$weatherApiUrl.'" target="_blank">updates site </a></b>';
        echo '<br />check 5.1 : '.$checkWhat.': result = ';
        $rawData        = $info = $error = '';
        curl_test($weatherApiUrl);  // first try
        #
        if ( $file_errors <> false )
             {  $weatherApiUrl	= 'https://pwsdashboard.be/startHere.txt';
                $checkWhat 	= 'load file from <b style="color: blue;"><a href="'.$weatherApiUrl.'" target="_blank">backup updates site </a></b>';
                echo '<br />check 5.2 : '.$checkWhat.': result = ';
                $rawData        = $info = $error = '';
                curl_test($weatherApiUrl); 
                if ($file_errors <> false) { $fatalErrors = true;}
                }
        }
else {  echo '<br />check 5.1 : load file from external site could not be tested, no CURL support'.PHP_EOL; }
#
if ($fatalErrors == true) 
     {  echo '<br /><br />There are fatal errors:<strong>
<br />If you can not remove the errors yourself post your questions at'.PHP_EOL;} 
else {  ini_set('display_errors', 0);      error_reporting(0);
        echo '<br /><br />No fatal errors,
<br />To continue with installing the PWS_Dashboard, click here to start <a href="PWS_easyweathersetup.php">easyweathersetup</a>
<br />If you have questions post those questions at ';}
echo ' the "<a href="https://www.weather-watch.com/smf/index.php/board,77.0.html" target="_blank">PWS_Dashboard part" of the WW-forum </a>"';

