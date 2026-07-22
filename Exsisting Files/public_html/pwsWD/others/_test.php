<?php  $scrpt_vrsn_dt  = '_test.php|01|2020-11-04|';  # release 2012_lts 
#
#                    general test script
#                it sets error reporting 
#      and then loads the script to test
#   all missing translations are printed
ini_set('display_errors', 'On');   
error_reporting(E_ALL);  
# error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
#
$script = 'index.php';
if (!isset ($_GET['test'])  ) 
     {  die ('need "?test=scriptname.php"' );}
else {  $script = trim($_GET['test']);}
$missing = array();
include ($script);  
if (isset ($missing) && is_array ($missing) && count ($missing) > 0)
     {  echo '<pre>'.PHP_EOL;
        foreach ($missing as $txt) {echo $txt;}
        echo '</pre>'.PHP_EOL;}
