<?php $scrpt_vrsn_dt  = 'wrnWarningCURLY.php|01|2022-03-28|';  # correct multiple warnings | release 2012_lts
#
# used in Advisory box top left to load message for US-curly warnings
#
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
#
chdir ('./nws-alerts/');
include './nws-alerts.php';
chdir ('../');
$wrnHref        = '<a href="./index.php?frame=weatheralarms">';
$alertBox       = '';
$alertfile      = './nws-alerts/cache/nws-alertsBoxData.php';
if (file_exists ($alertfile)
  #      && (time() - filemtime ($alertfile) ) < 630 
        )
      {  include $alertfile; }
if (trim($alertBox == '')) {return false;}
/*<!-- nws-alerts box -->
<div style="width:99%; border:solid thin #006699; margin:0px auto 0px auto;">
 <div style=" background-color:#E6E6E3; color: #000; padding:4px 8px 4px 8px; text-align: center"><a href="nws-summary.php" title=" &nbsp;View summary" style="text-decoration:none; color: #000;">No Warnings, Watches, or Advisories</a></div>
</div>
<!-- nws-alerts box -->
<div style="width:99%; border:solid thin #000; margin:0px auto 0px auto;">
 <div style=" background-color:#FFCC00; text-align:center; color: #000; padding:4px 8px 4px 8px">
  <span style="white-space: nowrap"> <img src="./alert-images/SUY.gif" width="12" height="12" alt="High Surf Advisory" title=" High Surf Advisory" />&nbsp;<a href="nws-summary.php" style="color: #000; text-decoration: none" title=" &nbsp;View summary"><b>HIGH&nbsp;SURF&nbsp;ADVISORY</b></a></span>&nbsp;-&nbsp;<a href="nws-details.php?a=FLZ202#WA1" style="color: #000; text-decoration: none" title=" &nbsp;Details for Escambia - High Surf Advisory">Escambia</a>  <br />
  <span style="white-space: nowrap"> <img src="./alert-images/WIY.gif" width="12" height="12" alt="Wind Advisory" title=" Wind Advisory" />&nbsp;<a href="nws-summary.php" style="color: #000; text-decoration: none" title=" &nbsp;View summary"><b>WIND&nbsp;ADVISORY</b></a></span>&nbsp;-&nbsp;<a href="nws-details.php?a=FLZ202#WA3" style="color: #000; text-decoration: none" title=" &nbsp;Details for Escambia - Wind Advisory">Escambia</a>  <br />
  <span style="white-space: nowrap"> <img src="./alert-images/RVS.gif" width="12" height="12" alt="Rip Current Statement" title=" Rip Current Statement" />&nbsp;<a href="nws-summary.php" style="color: #000; text-decoration: none" title=" &nbsp;View summary"><b>RIP&nbsp;CURRENT&nbsp;STATEMENT</b></a></span>&nbsp;-&nbsp;<a href="nws-details.php?a=FLZ202#WA2" style="color: #000; text-decoration: none" title=" &nbsp;Details for Escambia - Rip Current Statement">Escambia</a>  <br />
 </div>
</div>

*/
$search = 'No Warnings, Watches';               // are there any warnings ?
$pos    = strpos ($alertBox, $search);
if ($pos <> false) 
     {  echo '<!-- '.$search.' -->'; 
        return false;}                          // no warnings => return false
#
$search = 'background-color:';                  // search for color of first warning
$pos    = strpos ($alertBox, $search);
$pos   += strlen($search);
$color  = substr($alertBox,$pos,7);
$save_pos       = $pos;
#
$search = 'title="';                            // search for the title-text of first warning
$pos    = strpos ($alertBox,$search ,$pos);
$pos   += strlen($search);
$pos2   = strpos ($alertBox, '"',$pos);
$lngth  = $pos2 - $pos;
$text   = substr ($alertBox,$pos,$lngth);
#
# Now we have to check if there are multiple warnings.
$mlt_wrn        = '';
$search = 'background-color:';                  // search for color of first warning
$pos    = strpos ($alertBox, $search,$save_pos);
if ($pos == true)                               // multiple boxes
     {  $mlt_wrn= 'multi background color';}
else {  $search = 'white-space: nowrap';                   // search for the title-text of next warning
        $pos    = strpos ($alertBox,$search ,$save_pos);
        if ($pos == true)                               // multiple boxes
             {  $pos    = strpos ($alertBox,$search ,($pos + 10) );
                if ($pos == true)
                     {  $mlt_wrn= 'multi white-space';}
                }
        } 
/*
$alertfile      = './nws-alerts/cache/nws-alertsIconData.php';
if (file_exists ($alertfile) )
      {  include $alertfile; }
$cnt    = 1;
if (is_array($bigIcons) ) 
     {  $cnt = count ($bigIcons);}
     */
$extra_txt      = ''; # ' warns for ';  # 2022-01-15  removed unneeded text
$wrnStrings     = '<div style="text-align: center; position: absolute;top: 18px; left: 0px; width: 100%;height: 60px;  font-size: 12px; color: black; background-color: '.$color.';">
<div style="margin-top: 4px;"><b>NOAA-NWS</b>'.$extra_txt.'<br />';
if ($mlt_wrn <> '') 
     {  $wrnStrings    .=  'multiple warnings <!-- '.$mlt_wrn.' -->' ;}   # 2022-01-15  if ($cnt > 1) { $wrnStrings    .=  'multiple warnings' ;}
else {  $wrnStrings    .=  $text ;}
$wrnStrings    .=  '
<br />'.
$wrnHref.'
<svg id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>
</a>
</div>';  #       echo $wrnStrings; 
return true ;
