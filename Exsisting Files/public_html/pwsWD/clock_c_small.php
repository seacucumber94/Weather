<?php    $scrpt_vrsn_dt  = 'clock_c_small.php|01|2020-11-02|';  # release 2012_lts
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ------------------------- translation of texts
$wd_en  = array("Sun ","Mon ","Tue ","Wed ","Thur ","Fri ","Sat ");
$mon_en = array("Jan ","Feb ","Mar ","Apr ","May ","Jun ","Jul ","Aug ","Sep ","Oct ","Nov ","Dec ");
$wd_lcl = '';
$comma  = '';
foreach ($wd_en as  $trans) {$wd_lcl  .= $comma.'"'.lang($trans).'"'; $comma = ',';}
$mon_lcl= '';
$comma  = '';
foreach ($mon_en as $trans) {$mon_lcl .= $comma.'"'.lang($trans).'"'; $comma = ',';}
#
# ----------------- for testing
# $clockformat = '12';
# ----------------- for testing
#
if ($clockformat == '24') 
     {  $m_d = '+b+" "+h+';
        $imp = 'var imp = "";';
        $cmm = 'var cmm = " ";';}
else {  $m_d = '+h+" "+b+';
        $imp = 'var imp = "&nbsp;pm";';
        $cmm = 'var cmm = ", ";';}
#        
$time_js        = date('c');
echo '
<span id="theTime" style="font-size: 12px; padding: 0px;">
</span>
<script>
var clockID;
'.$imp.'
'.$cmm.'
var yourTimeZoneFrom='.$UTC.';
var d=new Date("'.$time_js.'");
var x=new Date();
var weekdays=['.$wd_lcl.'];
var months=['.$mon_lcl.'];
var tzDifference=yourTimeZoneFrom*60+d.getTimezoneOffset();
var offset=tzDifference*60*1000;
function UpdateClock()
     {  var e   = new Date(new Date().getTime()+offset);
        var hrs = e.getHours();
        var c   = hrs;
        if (c < 10) { c = "0"+c;}
        if (imp != "") 
             {  var ce  = hrs;
                if (ce > 12)  { ce  = ce - 12;}
                if (ce == 0)  { ce  = 12;}
                if (hrs < 12) { imp = "&nbsp;am";}
                if (hrs > 11) { imp = "&nbsp;pm";}
                c = ce;}
        var a   = e.getMinutes();
        if (a < 10) { a = "0"+a;}
        var g   = e.getSeconds();
        if (g < 10) { g = "0"+g;}      
        var f   = cmm+e.getFullYear();
        var h   = months[e.getMonth()];
        var b   = e.getDate();
        var i   = weekdays[e.getDay()];   
        document.getElementById("positionlastmt").innerHTML = 
        "<span  style=\' position: relative;  top: 2px; font-family: \"Lucida Sans\", Monaco, monospace; font-weight: bold; font-size: 13px; color: #FF7C39;\'>"
        +c+":"+a+":"+g+imp+"&emsp;</span><span style=\' position: relative;  top: 2px;  \'> "+" "+i+" "'.$m_d.'""+f+"</span>";
        }
function StartClock(){clockID=setInterval(UpdateClock,1000)}
StartClock();
</script>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
