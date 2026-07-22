<?php $scrpt_vrsn_dt  = 'index.php|01|2023-07-14|';  # nocache release 2012_lts 
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
$test_started = $scan_initial =  '';
if (isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 'On');   error_reporting(E_ALL & ~E_NOTICE );
        $test_started ='&test';}  // all errors except notices about new releases a.s.o.
else {  ini_set('display_errors', 0);      error_reporting(0);}                   // no reporting at all => for production sites
#
# remove comment mark on two lines below if asked so by support only
# $_REQUEST['test'] = true;
# ini_set('display_errors', 'On');   error_reporting(E_ALL & ~E_NOTICE );  // used for full testing of new releases
#
/*if (!is_file('./js/installed.arr') )
     {  $scan_initial   = true;
        include '_scan.php';}     */      // support: generate up-to-date list of available scripts.

header('Cache-Control: no-cache, no-store, must-revalidate');   # 2023-06-25
header('Pragma: no-cache');                                     # 2023-06-25
header('Expires: 0');                                           # 2023-06-25

if (!is_file('./_my_settings/settings.php') )
     {  include 'startHere.php';}  // first time we need to set the settings
else {  include 'PWS_index2.php';}
