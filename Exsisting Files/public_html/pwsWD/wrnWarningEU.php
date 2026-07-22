<?php  $scrpt_vrsn_dt  = 'wrnWarningEU.php|01|2023-04-19|';  # new / old formatstimout to 10 seconds | release 2012_lts
#
#                            These are the scripts to use for the meteoalarm.org website
#             Be warned that you have to find the new area codes, visit the wwforum topic
#
$script = 'wrnWarningEU-RSS.php';   // old fashined feed
$script = 'wrnWarningEU-ATOM.php';  //     use this one if both others fail
$script = 'wrnWarningEU-CAP.php';  // latest version
#
$legal = '<p style="width: 100%; margin: 0px auto; padding-bottom: 5px; background-color: silver;">
This warning data courtesy of and Copyright © EUMETNET-METEOalarm (http://www.meteoalarm.org/). 
Used with permission.
<br>Time delays between this website and the www.meteoalarm.org website are possible, 
for the most up to date information about alert levels as published by the participating National Meteorological Services 
please use <a href="http://www.meteoalarm.org" target="_blank" style="color: blue;"><u>www.meteoalarm.org</u></a></p>';
#
# Display a list of warnings from  metealarm.org
#                  used in Advisory box top left   
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['print']) && strtolower($_REQUEST['print']) == 'print' ) {
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
#
if ($script <> false)
     {  $return = include $script;
        return $return;}
