<?php $scrpt_vrsn_dt  = 'webfilm_popup.php|01|2020-11-06|'; # release 2012_lts # example script
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
     {  ini_set('display_errors', 0); error_reporting(0);}
else {  ini_set('display_errors', 1); error_reporting(1);}  
#
die ('Script needs to be adapted to your movie first ') ;
#
# example of a video feed.
echo
'<body style="margin: 0;">
<iframe src="https://q0ne.dyndns.org:8100/Mjpeg/3?authToken=cb30f183-c49f-4a2f-82d0-5af505b02da4" style="margin: 0 auto; width: 100%; height: 100%; "  frameborder="0" allowfullscreen>
</iframe>
</body>';

