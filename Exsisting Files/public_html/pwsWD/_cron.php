<?php  $scrpt_vrsn_dt  = '_cron.php|01|2021-01-25|';  # cleaned log data |release 2012_lts
#
ini_set('display_errors', 'On');   error_reporting(E_ALL);  # error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
#
#  to log results of the cron-job
#  not for general use
#
$script = 'PWS_cron_stationcron.php';
$_REQUEST['test']='test';
ob_start();
include ($script);  
$return = ob_get_contents();
$file   = getcwd().'/_log.txt';
file_put_contents ($file,$return,FILE_APPEND);

