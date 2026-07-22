<?php  $scrpt_vrsn_dt  = 'test.php|01|2020-11-05|';  # release 2012_lts
ini_set('display_errors', 'On');   error_reporting(E_ALL); 
$script = 'index.php'; 
$_REQUEST['test']='test';
include ($script);
