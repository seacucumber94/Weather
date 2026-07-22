<?php    $scrpt_vrsn_dt  = '_test.php|01|2023-05-01|'; # added time spent and memory used;
#
#                    general test script
#                it sets error reporting 
#      and then loads the script to test
#   all missing translations are printed
ini_set('display_errors', 'On');   
error_reporting(E_ALL);  # error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
$ws_start_time  = $ws_passed_time = microtime(true);
#
$script         = 'index.php';
$stck_lst       = '';
if (isset ($_GET['test'])  )    
     {  $script = trim($_GET['test']);}
else {  $_REQUEST['test'] = $script; }
$missing = array();
include ($script);  

$size   = memory_get_peak_usage(true);
$unit   = array('b','kb','mb','gb','tb','pb');
$used   = round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
$seconds= microtime(true) - $ws_start_time;
$string =  'Max memory used: '.$used.'('.$size.' bytes). Duration: '.round($seconds,5).' seconds. PHP_version: '.phpversion();
$stck_lst.= basename(__FILE__) .' ('.__LINE__.'): '.$string.PHP_EOL;

echo '<!-- '.$stck_lst.' -->';
if (isset ($missing) && is_array ($missing) && count ($missing) > 0)
     {  $string = '';
        foreach ($missing as $txt)
             {  $string.= $txt;}
        echo '<pre>'.PHP_EOL;
        echo $string;
        echo '</pre>'.PHP_EOL;
 #       file_put_contents('./languages/lang_nl.txt',$string,FILE_APPEND);
        }

