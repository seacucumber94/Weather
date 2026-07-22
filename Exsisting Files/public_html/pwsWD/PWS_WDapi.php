<?php   $scrpt_vrsn_dt  = 'PWS_WDapi.php|01|2020-11-04|';  # release 2012_lts
#
# WeatherDisplay API
# saves file when script is called by WeastherDisplay with fresh data 
#
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
$check_password  = false;
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
else {  ini_set('display_errors','On'); error_reporting(E_ALL);  
        $test = true;}  
#-----------------------------------------------
$filename       = './jsondata/WDapi.txt';
$protected      = true;
#
if ($check_password  <> false) 
     {  # ------------  load settings 
        $scrpt          = 'PWS_settings.php';  
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt; 
        if( !isset($_GET['pw']) ) 
             {  $string         = ' Security error '.__LINE__;
                $protected      = false;} 
        $txt    = trim($_GET['pw']);
        if ($txt <> $password )   
             {  $string         = ' Security error '.__LINE__;
                $protected      = false;} 
        } // eo check password
#
if(!isset($_GET['d'])  && $protected == true)
     {  $string = 'ERROR PWS_WDapi.php ('.__LINE__.'): No data part in HTTP request';}

elseif( isset($_GET['d']) && $protected == true) 
     {  $string         = $_GET['d'];
        $search         = array('+');
        $file_contents  = trim(str_replace($search,' ',$string));  ## ???????????
        if ($file_contents == '' || strlen ($file_contents) < 50) 
             {  echo 'file-empty';} 
        else {  file_put_contents($filename,$file_contents);
                echo 'success '; }
        }
#-----------------------------------------------
#                     FOR TESTING RECEIVE SCRIPT 
# $test = true;
if (isset ($test))
     {  $text   =  PHP_EOL.date('c').' | first 60 characters of data received is | '.substr($string,0,60);   
        file_put_contents('./jsondata/log.txt',$text,FILE_APPEND);
        }
#-----------------------------------------------
