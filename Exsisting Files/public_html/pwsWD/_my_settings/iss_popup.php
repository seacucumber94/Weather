<?php $scrpt_vrsn_dt  = 'iss_popup.php|01|2020-11-03|';  # release 2012_lts
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
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ISS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="overflow: auto; font-family: arial, sans-serif; text-align: center; margin: 0; overflow: hide; background-color: lightgrey">
<div style="padding-top: 2px; background-color: black; color: white;">
<span style="float: left">&nbsp;X&nbsp;&nbsp;<small>Close</small></span>
<span style="color: #FF7C39">ISS</span>
</div>
<script>
var size_n2yo = 'medium';
var allpasses_n2yo = '0';
</script>
<script type="text/javascript" src="https://www.n2yo.com/js/widget-tracker.js"></script>
</body>
</html>

