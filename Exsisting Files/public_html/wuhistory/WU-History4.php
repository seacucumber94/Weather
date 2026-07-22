<?php   
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
# VERSION         : 4.03
# Original Author : Jim McMurry - jmcmurry@mwt.net - jcweather.us
# Adapted         : Wim van der Kuil
# Documentation 
#   and support   : https://leuven-template.eu/
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) 
     {  $filenameReal = __FILE__;			
        $download_size = filesize($filenameReal);
        header('Pragma: public');
        header('Cache-Control: private');
        header('Cache-Control: no-cache, must-revalidate');
        header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
        header("Content-Length: $download_size");
        header('Connection: close');
        readfile($filenameReal);
        exit;}
#-----------------------------------------------
$pageName	= 'WU-History4.php'; 
$pageVersion	= '4.05 2021-05-16'; 
$pageUpdated	= 'pwsdashboard version'; 
#--------------------------------------- History
# 4.00 2018-03-02 first version	
# 4.01 2020-06-25 removed error day-link  with day# or month# started with a zero, also restart script.
# 4.02 2020-11-06 re-added decimals for baro in period display
# 4.03 2020-11-07 cleaned some settings 
# 4.05 2021-05-16 removed leuven
# ---------------------------------- Houskeeping
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion.' | '.$pageUpdated;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
ws_message ('<!-- loaded script: '.$pageLoaded.' '.$string.' -->');
# ----------------------------------------------
#                 SETTINGS
# ------------------------------  Selection area 
$showSelectArea = true;
$selectionTxt   = 'Select another period';
$use_reCAPTCHA  = false;	// true = yes
#
# ----------------------------------summary area
$summaryText    = 'Summary for ';
#
# ------------------------------  sky-conditions
$showSky        = false; 
#
# -----------------------  show the tabular data
$showTable    = true;  
#
# --------------------------  use meteic+english
$useBothUnits   = true;         // true use metric and english
$useBothUnits   = false;        // C / F as in the template settings
#
# ---------------------------------- month names
$mnth_org       = array ();
$mnth_org[]     = 'error';
$mnth_org[]     = 'January';
$mnth_org[]     = 'February';
$mnth_org[]     = 'March';
$mnth_org[]     = 'April';
$mnth_org[]     = 'May';
$mnth_org[]     = 'June';
$mnth_org[]     = 'July';
$mnth_org[]     = 'August';
$mnth_org[]     = 'September';
$mnth_org[]     = 'October';
$mnth_org[]     = 'November';
$mnth_org[]     = 'December';
$days_org       = array ();
$days_org[]     = 'Sunday';
$days_org[]     = 'Monday';
$days_org[]     = 'Tuesday';
$days_org[]     = 'Wednesday';
$days_org[]     = 'Thursday';
$days_org[]     = 'Friday';
$days_org[]     = 'Saturday';
#
# ----------------------- translated month names
$mnthname       = array ();
for ($n = 0; $n < count($mnth_org); $n++) {$mnthname[$n] = langtransstr($mnth_org[$n]);} 
# -----------------------   translated day names
$daynames       = array ();
$daynames_sh    = array();
for ($n = 0; $n < count($days_org); $n++) 
        {       $string                 = $days_org[$n];
                $daynames[$n]           = langtransstr($string);
                $daynames_sh[$string]   = langtransstr(substr($string,0,3));}  #echo '<pre>'.print_r($daynames_sh,true); exit;
#
#-----  translated constants/texts Summary Table
$Langtabs       = array();
$Langtabs[]     = 'error';
$Langtabs[]     = langtransstr('Daily');
$Langtabs[]     = langtransstr('Weekly');
$Langtabs[]     = langtransstr('Monthly');
$Langtabs[]     = langtransstr('Yearly');
$Langtabs[]     = langtransstr('Custom range');
$Langtabs[]     = langtransstr('Directly to');
#
$LangSumHeadsTp = langtransstr("Temperature");
$LangSumHeadsDp = langtransstr("Dew Point");
$LangSumHeadsHu = langtransstr("Humidity");
$LangSumHeadsWs = langtransstr("Wind Speed");
$LangSumHeadsWg = langtransstr("Wind Gust");
$LangSumHeadsWd = langtransstr("Wind Direction");
$LangSumHeadsBa = langtransstr("Pressure");
$LangSumHeadsRa = langtransstr("Precipitation");
$LangSumHeadsSo = langtransstr("Solar");
#       
$LangSumTxtC    = langtransstr("Current");
$LangSumTxtH    = langtransstr("High");
$LangSumTxtL    = langtransstr("Low");
$LangSumTxtA    = langtransstr("Average");
#
$LSumfor        = langtransstr("Summary for");
$LTabfor        = langtransstr("Tabular details for");
#
$Lunits         = langtransstr("Units");
$Lboth          = langtransstr("Both");
$Lenglish       = langtransstr("English");
$Lmetric        = langtransstr("Metric");
#
$Lperiod        = langtransstr("Period");
$Ldate          = langtransstr("Date");
$Lnext          = langtransstr("Next");
#
$Ltarget        = array();
$Ltarget[]      = 'error';
$Ltarget[]      = langtransstr("Day");
$Ltarget[]      = langtransstr("Week");
$Ltarget[]      = langtransstr("Month");
$Ltarget[]      = langtransstr("Year");
#
$Lview          = langtransstr("Show me");
#
#---------------   column header Tabular listing
$Lheadings      = array();
$Lheadings[0]   = langtransstr('Time');
$Lheadings[1]   = langtransstr('Temp.');
$Lheadings[2]   = langtransstr('Dew pt.');
$Lheadings[3]   = langtransstr('Pressure');
$Lheadings[4]   = langtransstr('From');
$Lheadings[5]   = langtransstr('Wind');
$Lheadings[6]   = langtransstr('Gust');
$Lheadings[7]   = langtransstr('Humid.');
$Lheadings[8]   = langtransstr('Rainrate');
$Lheadings[9]   = langtransstr('Solar');
$Lheadings[10]  = langtransstr('Cond.');
#
$Lheadings[11]  = langtransstr('Date');
#
#---  Headings when in weekly, monthly etc modes
$Lhdngs2        = array();
$Lhdngs2[0]      = $Lheadings[1];
$Lhdngs2[1]      = $Lheadings[2];
$Lhdngs2[2]      = $Lheadings[7];
$Lhdngs2[3]      = $Lheadings[3];
$Lhdngs2[4]      = $Lheadings[5];
$Lhdngs2[5]      = $Lheadings[6];
$Lhdngs2[6]      = langtransstr("Precip.");

$Lcols2         = array();
$Lcols2[]       = langtransstr("high");
$Lcols2[]       = langtransstr("avg");
$Lcols2[]       = langtransstr("low");
$Lcols2[]       = langtransstr("sum"); 
#
$Lcommatext     = langtransstr('Download your copy of this data-set');
$Lhere          = langtransstr('here');

$Lgoleft        = langtransstr('previous');
$Lgoright       = langtransstr('next');
$Lgotoday       = langtransstr('today');
$Lgoselect      = langtransstr('With this date');
$Lbothranges    = langtransstr('use both date-selectors for a custom range');
#
$wub_styles = array();
#
if (!isset($phpselftop)) {$phpselftop='';}
# ----------------------- end of manual settings
#
# ---------------------------- template settings
$WUID		= $wuid;
if ($ws_commontemp_type == 'c')  // Default units which are changeable at runtime.    #### 2020-11-07
     {  $units	= 'M';}         //  Metric,
else {  $units	= 'E';}         //  English
if ($useBothUnits) {$units = 'B';} // Both
#
# Stations first day of operation format dd-mm-yyyy.  
# This will determine years on the date selector.
$birthday	= $wustart; 
#
#
$long_date      = $dateLongFormat;      // l M j Y = Friday Jan 22 2015  |  l d F Y = Friday, 5 februari 2013
#
# ------------------------------ other settings
$WUgraphstr     = "https://www.wunderground.com/cgi-bin/wxStationGraphAll";
#$WUdatastr      = "http://release230/pwsWDxx/PWS_DailyHistory.php";  // replaced for pwsdashboard
#
// Set some dates to today
$da     = $da2  = date("d");    // Day of the month, 2 digits with leading zeros
$mo     = $mo2  = date("m");    // Numeric representation of a month, with leading zeros
$yr     = $yr2  = date("Y");    // A full numeric representation of a year, 4 digits
#
$LAST_YEAR      = (int) $yr;
$FIRST_YEAR     = (int) substr($birthday,6,4);
if ($FIRST_YEAR > $yr or $FIRST_YEAR < 1990) 
     {  $FIRST_YEAR     = $yr; }
#
$mode   = 1;
$prv    = '';
$formName       = 'xyz';
# $use_reCAPTCHA
if (!isset ($use_reCAPTCHA)         || $use_reCAPTCHA == false     ||
    !isset ($SITE["recap_public"])  || $SITE["recap_public"] == '' ||
    !isset ($SITE["recap_secret"])  || $SITE["recap_secret"] == '' )
     {  $use_reCAPTCHA = false; }
else {  echo '<script src="https://www.google.com/recaptcha/api.js?hl='.$lang.'" async defer></script>
<script>
function onSubmit(token) {document.getElementById("xyz").submit();}
</script>'.PHP_EOL;}
#
#-------  pass request parms  into PHP variables
if (count($_POST) > 1 )
     {  ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): posted '.print_r($_REQUEST,true).' -->',true);
        if (!empty($_POST['day']) )    { $da   = $_POST['day'];  }
        if (!empty($_POST['month']))   { $mo   = $_POST['month'];}
        if (!empty($_POST['year']))    { $yr   = $_POST['year']; }
        if (!empty($_POST['dayend']))  { $da2  = $_POST['dayend'];}
        if (!empty($_POST['monthend'])){ $mo2  = $_POST['monthend'];}
        if (!empty($_POST['yearend'])) { $yr2  = $_POST['yearend'];}
        if (!empty($_POST['prevnext'])){ $prv  = $_POST['prevnext'];}
        if (!empty($_POST['units']))   { $units= $_POST['units'];}
        if (!empty($_POST['mode']))    { $mode = $_POST['mode'];}}
        
#
if($use_reCAPTCHA && isset ($_POST['mode']) )
     {  $secret         = $SITE["recap_h_secret"];
        if ($_POST['g-recaptcha-response'] == '') 
             {   exit ( '<META HTTP-EQUIV="refresh" CONTENT="0; url=https://www.google.com/recaptcha/intro/">');}  
        $response       = $_POST['g-recaptcha-response'];
        $remoteip       = $_SERVER["REMOTE_ADDR"];
        $postfields     = 'secret=' .$secret.'&response='.$response.'&remoteip='.$remoteip;
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $rawdata= curl_exec($ch);
        $info	= curl_getinfo($ch);
        $errors = curl_error($ch);
        curl_close ($ch);
        ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Return codes: '.print_r ($info,true).' -->');
        ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Error  codes: '.print_r ($errors,true).' -->');
        $resp   = json_decode($rawdata,true);
        ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Reponse: '.print_r ($resp,true).' -->');
        if ($resp['success']  <> true) // What happens when the CAPTCHA was entered incorrectly
             {  exit ( '<META HTTP-EQUIV="refresh" CONTENT="0; url=https://www.google.com/recaptcha/intro/">');}}
#
#----------------------- Check validity if dates
#
if ($prv      == 'previous' ) { $calc = -1;}
elseif ($prv  == 'next' )     { $calc = +1;}
else                          { $calc = 0;}
#
if     ($mode    == 2)      {$calc  = $calc * 7;  }   // weeks extra
elseif ($mode    == 3)      {$calc  = $calc * 31; }   // month
elseif ($mode    == 4)      {$calc  = $calc * 365;}   // year
if ($calc <> 0)
     {  $nday   = $da + $calc;
        $newdate= mktime (0,0,0,$mo,$nday,$yr);
        $dates  = getdate($newdate); # echo '<pre>'.print_r($arr,true); exit;
        $da     = $dates['mday'];
        $mo     = $dates['mon'];
        $yr     = $dates['year'];}
$mo     = substr('0'.$mo,-2);
$da     = substr('0'.$da,-2);

$ymd_asked      = $yr.$mo.$da;
$ymd_now        = date('Ymd');
if ($ymd_asked == $ymd_now) 
     {  $isToday= true; } 
else {  $isToday= false; } 
if ($isToday == false || $mode <> 1)
     {  list ($y,$m,$d) = explode ('-',date('Y-m-d'));
        $goToday= "'$y','$m','$d'";}
else {  $goToday= false;}
#
if ($ymd_now < $ymd_asked)      // no dates in the future allowed
     {  $da     = date("d");
        $mo     = date("m");
	$yr     = date("Y");}
#
$int_date       = strtotime($yr.$mo.$da.'T120000');
$txt_date       = date ($long_date, $int_date); 
$txt_date       = str_replace ($mnth_org,$mnthname,$txt_date);
$txt_date       = str_replace ($days_org,$daynames,$txt_date);
#
$mo2    = substr('0'.$mo2,-2);
$da2    = substr('0'.$da2,-2);
$int_date       =strtotime($yr2.$mo2.$da2.'T120000');
$txt_date2      = date ($long_date, $int_date); 
$txt_date2      = str_replace ($mnth_org,$mnthname,$txt_date2);
$txt_date2      = str_replace ($days_org,$daynames,$txt_date2);
# 
if ($mode== 5 && $yr <> $yr2)
     {  $yr_pls = true;}        // custom period in multiple years
else {  $yr_pls = false;}       // custom period in same year
#------------------------------ format the URL's 
# first the default parts
$str_day        = '&day='   . $da;
$str_month      = '&month=' . $mo;
$str_year       = '&year='  . $yr;
$str_other      = '&format=1&graphspan=';
$str_ID         = '?ID='    . $WUID;


if    ( $mode == '1') { $str_other .= 'day';}  
elseif( $mode == '2') { $str_other .= 'week';} 	
elseif( $mode == '3') { $str_other .= 'month';} 
elseif ($mode == '4') { $str_other .= 'year';} 
elseif ($mode == '5') { $str_other .= 'custom'.'&monthend=' . $mo2 .'&dayend=' .$da2 .'&yearend='. $yr2;}
	
$wu_url         = $WUdatastr .$str_ID.$str_day.$str_month.$str_year.$str_other;
$wunderCSVstring= str_replace("&","&amp;",$wu_url);	// So the link to the csv output will validate in html checker
#
$wu_test_file   = './data/wucsv'.$mode.'.csv';
$wu_test_file   = false;
# 
#--------- load the required csv from WU 
$rawData        = ws_makeRequest($wu_url,$wu_test_file);
$rawData        = str_replace ('<br>', '',$rawData); 
$raw            = explode ("\n", $rawData);
#
$csvarray       = array();
$field_names    = array ();
#
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): cleaning CSV, filesize incl. names-line : '.count($raw).' -->');
foreach ($raw as $key => $line) 
     {  if (strlen($line) < 10) 
             {  echo '<!-- Removing line '. $key .' no fields -->'.PHP_EOL; 
                continue;} 
        $fields = explode (',',trim($line).','); 
        $check  = $fields[1];    // seems to be always the temperature
        if ($check[0] == 'T')
             {  $field_names    = $fields;
                continue;}        
        if (!is_numeric ($check) || $check < -100 || $check > 150) 
             {  echo '<!-- Removing line '. $key .' invalid data '.$check.' -->'.PHP_EOL; 
                continue;}
        $check  = $fields[0][0];// seems to be always the century / date 
        if ($check <> '2') 
             {  echo '<!-- Removing line '. $key .' no date in first field '.$fields[1].'-->'.PHP_EOL; 
                continue;}
       $csvarray[] = $fields; }
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): cleaned CSV, filesize EXCL. names-line : '.count($csvarray).' -->');
#
#----------- check if at least one line is valid
if (count($csvarray) < 1)
     {  ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): no valid data for the period found, script restarts -->',true);
        unset ($_POST);
        echo '<h3 style="text-align: center; color: red;">'.langtransstr('No valid data for the period found').'<br /><br /><br />'; 
        echo '<a href="./startWUhistory.php"> Click here to go back to the script';
        return;}
#
#---------------- which units is the csv-data in
$rawunits       = 'metric';
if ($mode == 1)                         // get Baro  to determine whether raw data is metric or not
     {  $check  = $csvarray[0][3];  }   // 
else {  $check  = $csvarray[0][10];}    // Baro is in a different position in the other modes
if ($check < 50) {$rawunits = "english";}
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): raw units : '.$rawunits.' based on pressure '.$check.' -->');
#
#-------------- assemble important texts
$tabular_headline   = $Langtabs[$mode] . ' ' . $LTabfor . ' ';
$summary_headline   = $Langtabs[$mode] . ' ' . $LSumfor . ' ';
 
if ($mode == 1 || $mode == 2) 
     {  $tabular_headline  .= $txt_date;
        $summary_headline  .= $txt_date;} 
elseif ($mode == 3) 
     {  $tabular_headline  .= $mnthname[intval($mo)] . ' ' . $yr;
        $summary_headline  .= $mnthname[intval($mo)] . ' ' . $yr;} 
elseif ($mode == 4) 
     {  $tabular_headline  .= $yr;
        $summary_headline  .= $yr;} 
else {  $tabular_headline = $LTabfor.' '.$txt_date. ' - '.$txt_date2;
        $summary_headline = $LSumfor.' '.$txt_date. ' - '.$txt_date2;}
#
#------------------ what units must be displayed
$Smetric = $Senglish = $Sboth = '';
if     ( $units == 'M' ) {$Smetric  = 'checked';}
elseif ( $units == 'E' ) {$Senglish = 'checked';}
elseif ( $units == 'B' ) {$Sboth    = 'checked';}
#
if ($units == "M") 
     {  $tsym = " &deg;C";
        $bsym = " hPa";
        $wsym = " km/h";
#        $vsym = " km";
        $rsym = " cm";}
elseif ($units == "E") {
        $tsym = " &deg;F";
        $bsym = " in";
        $wsym = " mph";
#        $vsym = " mi";
        $rsym = " in";} 
else {  $tsym = " &deg;F/&deg;C";
        $bsym = " in/hPa";
        $wsym = " mph/km/h";
#        $vsym = " mi/km";
        $rsym = " in/cm";}
#
#---------------------------  assemble hide show
$formName       = 'xyz';
$aTxt           = 'abc';
$hide_txt       = langtransstr('hide this area');
$show_txt       = langtransstr('click here');
if ($showSelectArea == true) 
     {  $yy     = 'block'; $ztxt = $hide_txt;}
else {  $yy     = 'none';  $ztxt = $show_txt;}    
$string = "'$formName','$aTxt' ";
$xx             = '( <a id ="'.$aTxt.'" href="javascript:wu_select_click('.$string.')">'.$ztxt. '</a> )';
#
#----------------------  assemble selection area
$Smode = array ('','','','','','');
$Smode[$mode]   = 'checked';
echo '<div id="select" class="ws_wub">
<h4 class="blockHead" style="height: 14px;">'.langtransstr($selectionTxt).' '.$xx.'</h4>
<script>
function wu_select_click(xyz,aaa) 
{       if (!document.getElementById)    return;
        which   = document.getElementById(xyz);
        repl    = document.getElementById(aaa);
        if (which.style.display == "block")
             {  which.style.display     = "none";
                repl.innerHTML          = "'.$show_txt.'";}
        else {  which.style.display     ="block"
                repl.innerHTML          = "'.$hide_txt.'"}
} // eof wu_select_click
</script>
<form id="'.$formName.'" method="post" action="'.$phpself.$phpselftop.'" style="display: '.$yy.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="p" 	 value ="'. $p .'"/>
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="lang" value ="'. $lang .'"/>
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="link" value =""/>
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="prevnext" value =""/>'.PHP_EOL;
#
if (array_key_exists ('stripall',$_REQUEST) )  
     {  echo '<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="stripall" value =""/>'.PHP_EOL;}
echo '<table class="" style="">
<thead>

<tr class="table-top">
<th class="slct">'.     $Lunits  .'</th>
<th class="slct">'.     $Lperiod .'</th>
<th class="center">' .$Langtabs[5].'</th>
<th class="center">'. $Langtabs[6] .'</th>
</thead>
<tbody style="vertical-align: top;">
<tr>
<td rowspan="2" class="slct">  
<table>
<tr><td><input type="radio" name="units" value="M" '.$Smetric.'></td>
<td><input type="radio" name="units" value="E" '.$Senglish.'></td>
<td><input type="radio" name="units" value="B" '.$Sboth.'></td></tr>
<tr><td>'.$Lmetric.'</td>
<td>'    .$Lenglish.'</td>
<td> '   .$Lboth.'</td></tr></table>
</td>
<td class="slct"> 
<table>
<tr><td><input type="radio" name="mode" value="1" '.$Smode[1].' ></td>
<td><input type="radio" name="mode" value="2" '.$Smode[2].' ></td>
<td><input type="radio" name="mode" value="3" '.$Smode[3].' ></td>
<td><input type="radio" name="mode" value="4" '.$Smode[4].' ></td></tr>
<tr><td>'.$Langtabs[1].'</td>
<td>'    .$Langtabs[2].'</td>
<td>'    .$Langtabs[3].'</td>
<td>'    .$Langtabs[4].'</td></tr> </table>
</td>
<td  class="slct">
<table><tr><td><input type="radio" onclick="wsBoldFunction(\'setbold\')" name="mode" value="5" '.$Smode[5].' ></td></tr>'.
'<tr><td><span id="setbold">'.$Lbothranges.'</span></td></tr></table>
</td>
<td style="vertical-align: middle;">';
if ($mode < 5)
     {  if ($goToday) 
             {  echo'<b style="cursor: pointer; color: blue; text-decoration: underline;" onclick="wsPostFunction('.$goToday.')">'.$Lgotoday.'</b><br />'; } 
        echo'<b style="cursor: pointer; color: blue; text-decoration: underline;" onclick="wsPostNextPrev(\'previous\')">'.$Lgoleft.' '.$Ltarget[$mode].'</b>'; 
        if (!$isToday) 
             {  echo '<br /><b style="cursor: pointer; color: blue; text-decoration: underline;" onclick="wsPostNextPrev(\'next\')">'.$Lgoright.' '.$Ltarget[$mode].'</b>'; }}
else {  echo '';}
echo'</td>
</tr>
<tr>
<td class="center">'.PHP_EOL;
echo $Lgoselect.' ';
echo '<select name="year">';
for ($n = $FIRST_YEAR; $n <= $LAST_YEAR; $n++)
     {  if ($n == $yr) {$selected = 'selected="selected"';}
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.'>'.$n.'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '<select name="month">'.PHP_EOL;
for ($n = 1; $n <= 12; $n++) 
    {   if ($n == $mo ) $selected = 'selected="selected"';
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.'>'.$mnthname[$n].'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '<select name="day">';
for ($n = 1; $n <= 31; $n++) 
    {   if ($n == $da ) $selected = 'selected="selected"';
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.' >'.$n.'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '</td>
<td class="center">'.PHP_EOL;
#
echo '<select name="yearend">';
for ($n = $FIRST_YEAR; $n <= $LAST_YEAR; $n++)
     {  if ($n == $yr2) {$selected = 'selected="selected"';}
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.'>'.$n.'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '<select name="monthend">'.PHP_EOL;
for ($n = 1; $n <= 12; $n++) 
    {   if ($n == $mo2 ) $selected = 'selected="selected"';
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.'>'.$mnthname[$n].'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '<select name="dayend">';
for ($n = 1; $n <= 31; $n++) 
    {   if ($n == $da2 ) $selected = 'selected="selected"';
        else {  $selected = '';}
        echo '<option value="'.$n.'" '.$selected.' >'.$n.'</option>'.PHP_EOL;}
echo '</select>'.PHP_EOL;
#
echo '</td>
<td class="center">
<input type="submit" onClick="wsValidate();" value="'.$Lview.'" style="cursor: pointer;"></td>
</tbody>
</table>
</form>  <!-- end of id="xyz" -->
</div>'.PHP_EOL;  // eo assemble selection area
#
#
if ($showTable  == true && $mode == 1)  // daily table
     {  $html =  $hdht = '';
     
    #    $hdht  = '<thead class="light;" style="border-color: white;">' . "\n";
        $hdht  .= '<tr class="table-top" style="border-color: white;">' . "\n";
        $hdht  .= '<th style="color: black;">' . $Lheadings[0] . '</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[1] . ' (' . $tsym . ' )</th>' . "\n"; 
        $hdht  .= '<th>' . $Lheadings[2] . ' (' . $tsym . ' )</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[5] . ' (' . $wsym . ' )</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[4] . '</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[6] . ' (' . $wsym . ' )</th>' . "\n";        
        $hdht  .= '<th>' . $Lheadings[3] . ' (' . $bsym . ' )</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[7] . '( % )</th>' . "\n";
        $hdht  .= '<th>' . $Lheadings[8] . ' (' . $rsym . ' )</th>' . "\n";
        if ($showSolar){ $hdht  .= '<th>' . $Lheadings[9]  . ' ( W/m<sup>2  )</sup></th>' . "\n";}
        if ($showSky)  { $hdht  .= '<th>' . $Lheadings[10] . '</th>' . "\n";}
        $hdht  .= '</tr>' . "\n";
 #       $hdht  .= '</thead>' . "\n";
        $char   = '&nbsp;/&nbsp;';      
#----- calc high / avg  /low
        $arr    = $csvarray[0];
        $tempA  = $dewpA  = $humiA  = $baroA  = $windA  = $gustA  = $rainA  = 0;
        $tempH  = $tempL  = (float) $arr[1];
        $dewpH  = $dewpL  = (float) $arr[2];
        $humiH  = $humiL  = (float) $arr[8];
        $baroH  = $baroL  = (float) $arr[3];                
        $windH  = $windL  = (float) $arr[6]; 
        $gustH  = $gustL  = (float) $arr[7];
        $rainH  = $rainL  = (float) $arr[9];
        $cntRn  = 0;
        $windT  = array();
        $solarT = $solarC = $solarH = 0; 
# -------------------------- details row
        $html   = '';
        for ($row=0; $row<count($csvarray); $row++) 
             {  $html   .= '<tr>' .PHP_EOL;
# csv field time
                $arr    = $csvarray[$row]; #$html .= '<pre>'.print_r($arr,true); exit;	
                $html  .= '<td>'.substr($arr[0],11,5).'</td>' .PHP_EOL;          		
# csv field temp
                $value  = (float) $arr[1];  
                if ($value  > $tempH) {$tempH = $value;} 
                if ($value  < $tempL) {$tempL = $value;}
                $tempA +=  $value;            
                $data   = convertTemps($value);        
                $html  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
# csv field dew
                $value  = (float) $arr[2];
                if ($value  > $dewpH) {$dewpH = $value;} 
                if ($value  < $dewpL) {$dewpL = $value;}
                $dewpA +=  $value;            
                $data   =  convertTemps($value);        
                $html  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
# csv field windspeed
                $value = (float) $arr[6]; 
                if ($value  > $windH) {$windH = $value;} 
                if ($value  < $windL) {$windL = $value;}
                $windA +=  $value;            
                if ($value == 0)         
                     {  $data   = '&nbsp;';}
                else {  $data   = convertWind($value);
                        $data   = show_data($data,$char);}
                $html  .= '<td>'.$data.'</td>' .PHP_EOL; 
# csv field winddir compass heading, field 5 winddir in deg skipped 
                $data   = $arr[4]; 
                $value  = (float) $arr[6];                     
                if ($value == 0) // no wind speed, set direction to calm                         
                     {  $data   = 'Calm';}
                $html  .= '<td>'.langtransstr($data).'</td>' .PHP_EOL;
                if (!isset ($windT[$data]))
                     {  $windT[$data]   = array ('speed' => $value, 'count' => 1); }
                else {  $windT[$data]['speed'] += $value;
                        $windT[$data]['count'] += 1;}
# csv field wind gust                
                $value  = (float) $arr[7];  
                if ($value  > $gustH) {$gustH = $value;} 
                if ($value  < $gustL) {$gustL = $value;}
                $gustA +=  $value;            
                if ($value == 0)         
                     {  $data   = '&nbsp;';}
                else {  $data   = convertWind($value);
                        $data   = show_data($data,$char);}
                $html  .= '<td>'.$data.'</td>' .PHP_EOL;
# csv field pressure
                $value  = (float) $arr[3];
                if ($value  > $baroH) {$baroH = $value;} 
                if ($value  < $baroL) {$baroL = $value;}
                $baroA +=  $value;            
                $data   = convertBaro($value);         
                $html  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
# csv field humidity 
                $value  = (float) $arr[8];
                if ($value  > $humiH) {$humiH = $value;} 
                if ($value  < $humiL) {$humiL = $value;}
                $humiA +=  $value;            
                $html  .= '<td>'.$value.'</td>' .PHP_EOL;
# csv field rain / hr
                $value  = (float) $arr[9]; 
                if ($value  > $rainH) {$rainH = $value;} 
                if ($value  < $rainL) {$rainL = $value;}
                $rainA += $value;
                if ($value == 0)
                     {  $data   = '-';}
                else {  $rainA += $value;
                        $cntRn++;
                        $data   = convertRainMM($value);
                        $data   = show_data($data,$char); }
                $html  .= '<td>'.$data.'</td>' .PHP_EOL; 
# csv field  13 solar                
                if ($showSolar) 
                     {  $data   = (float) $arr[13];
                        if ($data > $solarH) ($solarH = $data);
                        if ($data == 0)
                             {  $data   = '-';}
                        else {  $solarT+= $data;
                                $solarC++; }
                        $html  .= '<td>'.$data.'</td>' .PHP_EOL;}
# csv field  11 sky condition		
                if ($showSky) 
                     {  $html  .= '<td>'.$arr[11].'</td>' .PHP_EOL; }
                $html  .= '</tr>'.PHP_EOL;
        } // eo for $row
# ------------------------- summary rows
        $cols   = 9;
        if ($showSolar) {$cols++;}
        if ($showSky)   {$cols++;}
# high
        $hsum   = '';
        $hsum  .= '<tr class="dark" style="border-color: white;">' .PHP_EOL;
        $hsum  .= '<td>'.$LangSumTxtH.'</td>';
        $data   = convertTemps($tempH);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpH);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        $data   = convertWind($windH);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
#echo '<!-- '. print_r($windT,true). ' -->';
        $speed  = 0; $speeddir  = '';  // most wind
        $speedT = 0;
        $count  = 0; $countdir = '';   // most dir
        foreach ($windT as $key => $arr) 
             {  $speedT += $arr['speed'];
                if ($arr['speed'] > $speed) 
                     {  $speed = $arr['speed']; $speeddir = $key;} 
                if ($arr['count'] > $count) 
                     {  $count = $arr['count']; $countdir = $key;} }
        $txt    = langtransstr($speeddir).' ( '.round($speed).'/'.round($speedT).' )';
        $hsum  .= '<td>'.$txt.'</td>' .PHP_EOL;
        $data   = convertWind($gustH);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $data   = convertBaro($baroH);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiH.'</td>' .PHP_EOL;     
        if ($rainH == 0)
             {  $data   = '-';}
        else {  $data   = convertRainMM($rainH);
                $data   = show_data($data,$char); }
        $hsum  .= '<td>'.$data.'</td>' .PHP_EOL; 
        if ($showSolar) {  $hsum  .= '<td>'.$solarH.'</td>' .PHP_EOL;}
        if ($showSky)   {  $hsum  .= '<td>nbsp;</td>' .PHP_EOL;}
        $hsum  .= '</tr>'.PHP_EOL;   
# average 
        $row    = count($csvarray);    
        $hsum  .= '<tr >' .PHP_EOL;
        $hsum  .= '<td>'.$LangSumTxtA.'</td>';
        $tempA  = round ($tempA / $row,1);
        $data   = convertTemps($tempA);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        $dewpA  = round ($dewpA / $row,1);
        $data   = convertTemps($dewpA);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        $windA  = round ($windA / $row,1);
        $data   = convertWind($windA);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $txt    = langtransstr($countdir).' #'.$count.'/'.$row;       
        $hsum  .= '<td>'.$txt.'</td>' .PHP_EOL;
        $gustA  = round ($gustA / $row,1);
        $data   = convertWind($gustA);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $baroA  = round ($baroA / $row,1);
        $data   = convertBaro($baroA);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $humiA  = round ($humiA / $row,0);
        $hsum  .= '<td>'.$humiA.'</td>' .PHP_EOL; 
        if ($rainA == 0 || $cntRn == 0)
             {  $data   = '-';}
        else {  $rainA  = round ($rainA / $cntRn,1);
                $data   = convertRainMM($rainA); 
                $data   = show_data($data,$char);}
        $hsum  .= '<td>'.$data.'</td>' .PHP_EOL; 
        if ($showSolar == true)   {  $hsum  .= '<td>'.round($solarT/$solarC).' #'.$solarC.'/'.$row.'</td>' .PHP_EOL;}  ############
        if ($showSky   == true)   {  $hsum  .= '<td>nbsp;</td>' .PHP_EOL;}
        $hsum  .= '</tr>'.PHP_EOL;   
# low     
        $hsum  .= '<tr class="dark">' .PHP_EOL;
        $hsum  .= '<td>'.$LangSumTxtL.'</td>';
        $data   = convertTemps($tempL);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpL);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL; 
        if ($windL == 0) 
             {  $hsum  .= '<td>-</td>' .PHP_EOL;}    
        else {  $data   = convertWind($windL);
                $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;}
        $hsum  .= '<td>&nbsp;</td>' .PHP_EOL;
        $data   = convertWind($gustL);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $data   = convertBaro($baroL);        
        $hsum  .= '<td>'.show_data($data,$char).'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiL.'</td>' .PHP_EOL;
        if ($rainL == 0)
             {  $data   = '-';}
        else {  $data   = convertRainMM($rainL);
                $data   = show_data($data,$char); }
        $hsum  .= '<td>'.$data.'</td>' .PHP_EOL; 
 
        if ($showSolar) {  $hsum  .= '<td>&nbsp;</td>' .PHP_EOL;}
        if ($showSky)   {  $hsum  .= '<td>&nbsp;</td>' .PHP_EOL;}
        $hsum  .= '</tr>'.PHP_EOL;   
        $maxH   = 1000;
        $headH  = 142;
        $diffH  = (int) $maxH - $headH;
        echo    '<div id="wu'.$str_other[$mode].'" class="ws_wub" style="display: block; max-height: 100%; width: 100%; '.
                'overflow: hidden; position: relative; background-color: #EEEEEE;">'; 
        echo    '<table >' . "\n";
        echo    '<tbody>' . "\n";
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$summary_headline.'</span></td></tr>'.PHP_EOL;
        $string = str_replace ("color: black;","color: transparent;",$hdht);
        echo    $string; 
        echo    $hsum;             
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$tabular_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht;
        echo    $html;
        echo    '</tbody>';
        echo    '</table>';
        echo    '</div>';
/*        echo    '<div style="position: relative; max-height: '.$diffH.'px;">
<div id="wuwrap" class="ws_wub" style="display: block; max-height: '.$maxH.'px; width: 100%; '.
        'position: relative;  top: -'.$headH.'px; overflow: auto;">'.PHP_EOL;
        echo    '<table >' . "\n";
        echo    '<tbody>' . "\n";
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$summary_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht; 
        echo    $hsum;             
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$tabular_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht;
        echo    $html;
        echo    '</tbody>';
        echo    '</table>';
        echo    '</div>'; 
        echo    '</div>'; */

        } // eo  daily table
#
if ($showTable  == true && $mode <> 1)	// other tables	
     {  $html   = $hdht = '';
        $hdht  .= '<tr class="table-top" style="border-color: white;">' . "\n";
        $hdht  .= '<th colspan="2" rowspan="2" style="color: black; vertical-align: middle;">' .$Lheadings[11] . '</td>' . "\n";
        $hdht  .= '<th colspan="3">' . $Lhdngs2[0] . ' (' . $tsym . ')</td>' . "\n";
        $hdht  .= '<th colspan="3">' . $Lhdngs2[1] . ' (' . $tsym . ')</td>' . "\n";
        $hdht  .= '<th colspan="3">' . $Lhdngs2[4] . ' (' . $wsym . ')</td>' . "\n";
 #       $hdht  .= '<th>' . $Lhdngs2[5] . '</td>' . "\n";
        $hdht  .= '<th colspan="2">' . $Lhdngs2[3] . ' (' . $bsym . ')</td>' . "\n";
        $hdht  .= '<th colspan="3">' . $Lhdngs2[2] . ' (%)</td>' . "\n";
        $hdht  .= '<th>' . $Lhdngs2[6] . ' (' . $rsym . ')</td>' . "\n";
        $hdht  .= '</tr>' . "\n";
        $hdht  .= '<tr class="table-top" style="border-color: white;">' . "\n";
        $hdht  .= '<th>' . $Lcols2[0] . '</td>' . "\n"; // temp
        $hdht  .= '<th>' . $Lcols2[1] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[2] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[0] . '</td>' . "\n"; // dewp
        $hdht  .= '<th>' . $Lcols2[1] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[2] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[0] . '</td>' . "\n"; // wind
        $hdht  .= '<th>' . $Lcols2[1] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lhdngs2[5] . '</td>' . "\n"; // gust
        $hdht  .= '<th>' . $Lcols2[0] . '</td>' . "\n"; // baro
        $hdht  .= '<th>' . $Lcols2[2] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[0] . '</td>' . "\n"; // humi
        $hdht  .= '<th>' . $Lcols2[1] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[2] . '</td>' . "\n";
        $hdht  .= '<th>' . $Lcols2[3] . '</td>' . "\n"; // rain
        $hdht  .= '</tr>' . "\n";
#----- calc high / avg  /low
        $arr    = $csvarray[0];
        $tempHa = $tempAa = $tempLa = $dewpHa = $dewpAa = $dewpLa = $humiHa = $humiAa = $humiLa = 0;
        $baroHa = $baroLa = $windHa = $windAa = $gustHa = 0;
        $tempHh = $tempHl = (float) $arr[1];
        $tempAh = $tempAl = (float) $arr[2];
        $tempLh = $tempLl = (float) $arr[3];
        $dewpHh = $dewpHl = (float) $arr[4];
        $dewpAh = $dewpAl = (float) $arr[5];
        $dewpLh = $dewpLl = (float) $arr[6];
        $humiHh = $humiHl = (float) $arr[7];
        $humiAh = $humiAl = (float) $arr[8];
        $humiLh = $humiLl = (float) $arr[9];
        $baroHh = $baroHl = (float) $arr[10];     
        $baroLh = $baroLl = (float) $arr[11];     
        $windHh = $windHl = (float) $arr[12]; 
        $windAh = $windAl = (float) $arr[13]; 
        $gustHh = $gustHl = (float) $arr[14];        
        $rainT  = 0;
# -------------------------- details row
        $html   = '';
        for ($row=0; $row<count($csvarray); $row++) {
                $arr    = $csvarray[$row];
                $html  .= '<tr>' . "\n";
# csv field 0 Date/Time  table col 0
                $data   = $arr[0]; 
                list ($pyr,$pmo,$pda) = explode ('-',$data);
                $newdate= mktime (0,0,0,$pmo,$pda, $pyr);
                $dates  = getdate($newdate); # echo '<pre>'.print_r($arr,true); exit;
                $string = langtransstr(substr($dates['month'],0,3));
                if ($yr_pls) {$string = $pyr.' '.$string;}
                $html  .= '<td class = "date">' . $string . '</td>';
                $html  .= '<td class = "date"><b style="cursor: pointer; color: blue; text-decoration: underline;" '.
                                'onclick="wsPostFunction(\''.$pyr."','".(int)$pmo."','".(int)$pda.'\')" >'. $pda . '</b></td>'; 
# csv field temp high  table col 1
                $value  = (float) $arr[1];
                if ($value  > $tempHh) {$tempHh = $value;} 
                if ($value  < $tempHl) {$tempHl = $value;}
                $tempHa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td class="dark">' . show_data($data) . '</td>';
# csv field 2 temp avg  table col 2
                $value  = (float) $arr[2];
                if ($value  > $tempAh) {$tempAh = $value;} 
                if ($value  < $tempAl) {$tempAl = $value;}
                $tempAa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td class="dark">' . show_data($data)  . '</td>';
# csv field 3 temp low  table col 3
                $value  = (float) $arr[3];
                if ($value  > $tempLh) {$tempLh = $value;} 
                if ($value  < $tempLl) {$tempLl = $value;}
                $tempLa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td class="dark">' . show_data($data)  . '</td>';
# csv field 4 dewp high  table col 4
                $value  = (float) $arr[4];
                if ($value  > $dewpHh) {$dewpHh = $value;} 
                if ($value  < $dewpHl) {$dewpHl = $value;}
                $dewpHa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
# csv field 5 dewp avg  table col 5
                $value  = (float) $arr[5];
                if ($value  > $dewpAh) {$dewpAh = $value;} 
                if ($value  < $dewpAl) {$dewpAl = $value;}
                $dewpAa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
# csv field 6 dewp low  table col 6
                $value  = (float) $arr[6];
                if ($value  > $dewpLh) {$dewpLh = $value;} 
                if ($value  < $dewpLl) {$dewpLl = $value;}
                $dewpLa +=  $value;            
                $data   = convertTemps($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
# csv field 12 windspeed high   table col 7
                $value  = (float) $arr[12];
                if ($value  > $windHh) {$windHh = $value;} 
                if ($value  < $windHl) {$windHl = $value;}
                $windHa +=  $value;            
                $data   = convertWind($value,0);
                $html  .= '<td class="dark">' . show_data($data)  . '</td>';
# csv field 13 windspeed  avg  table col 8
                $value  = (float) $arr[13];
                if ($value  > $windAh) {$windAh = $value;} 
                if ($value  < $windAl) {$windAl = $value;}
                $windAa +=  $value;                                 
                $data   = convertWind($value,0);
                $html  .= '<td class="dark">' . show_data($data)  . '</td>';
# csv field 14 gust speed   table col 9                
                $value  = (float) $arr[14];
                if ($value  > $gustHh) {$gustHh = $value;} 
                if ($value  < $gustHl) {$gustHl = $value;}
                $gustHa +=  $value;                                 
                $data   = convertWind($value,0);
                $html  .= '<td class="dark">' . show_data($data)  . '</td>';
# csv field 10 pressure high   table col 10
                $value  = (float) $arr[10];
                if ($value  > $baroHh) {$baroHh = $value;} 
                if ($value  < $baroHl) {$baroHl = $value;}
                $baroHa +=  $value;                                 
                $data   = convertBaro($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
# csv field 11 pressure  low  table col 11
                $value  = (float) $arr[11];
                if ($value  > $baroLh) {$baroLh = $value;} 
                if ($value  < $baroLl) {$baroLl = $value;}
                $baroLa +=  $value;                                 
                $data   = convertBaro($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
# csv field 7-9 humid high avg low  table col 12,13,14
                $value  = (float) $arr[7];
                if ($value  > $humiHh) {$humiHh = $value;} 
                if ($value  < $humiHl) {$humiHl = $value;}
                $humiHa +=  $value;
                $html  .= '<td class="dark">' . $value . '</td>';
                $value  = (float) $arr[8];
                if ($value  > $humiAh) {$humiAh = $value;} 
                if ($value  < $humiAl) {$humiAl = $value;}
                $humiAa +=  $value;
                $html  .= '<td class="dark">' . $value . '</td>';
                $value  = (float) $arr[9];
                if ($value  > $humiLh) {$humiLh = $value;} 
                if ($value  < $humiLl) {$humiLl = $value;}
                $humiLa +=  $value;
                $html  .= '<td class="dark">' . $value . '</td>';                
# csv field 15 precipitation   table col 15  
                $value  = (float) $arr[15];
                $rainT += $value;
                $data   = convertRainCM($value);
                $html  .= '<td>' . show_data($data)  . '</td>';
                $html  .= '</tr>';
        } // eo Rows
# ------------------------- summary rows
        $cols   = 17;
# high
        $hsum   = '';
        $hsum  .= '<tr class="dark" style="border-color: white;">' .PHP_EOL;
        $hsum  .= '<td colspan="2">'.$LangSumTxtH.'</td>';
# temp high  avg low
        $data   = convertTemps($tempHh);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($tempAh);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($tempLh);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# dewp high  avg low
        $data   = convertTemps($dewpHh);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpAh);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpLh);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# wind high  avg low
        $data   = convertWind($windHh,0);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertWind($windAh,0);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# gust high  
        $data   = convertWind($gustHh);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# pressure high low
        $data   = convertBaro($baroHh);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $data   = convertBaro($baroLh);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
# pressure high avg  low 
        $hsum  .= '<td>'.$humiHh.'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiAh.'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiLh.'</td>' .PHP_EOL;
# rain
        $data   = convertRainCM($rainT);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $hsum  .= '</tr>';
# ------------------------- summary rows
# average
        $hsum  .= '<tr>' .PHP_EOL;
        $hsum  .= '<td colspan="2">'.$LangSumTxtA.'</td>';
# temp high  avg low
        $tempHa = round($tempHa /$row,1);
        $data   = convertTemps($tempHa);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $tempAa = round($tempAa /$row,1);
        $data   = convertTemps($tempAa);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $tempLa = round($tempLa /$row,1);
        $data   = convertTemps($tempLa);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# dewp high  avg low
        $dewpHa = round($dewpHa /$row,1);
        $data   = convertTemps($dewpHa);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $dewpAa = round($dewpAa /$row,1);         
        $data   = convertTemps($dewpAa);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $dewpLa = round($dewpLa /$row,1); 
        $data   = convertTemps($dewpLa);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# wind high  avg low
        $windHa = round($windHa /$row,1);
        $data   = convertWind($windHa,0);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $windAa = round($windAa /$row,1);
        $data   = convertWind($windAa,0);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# gust high 
        $gustHa = round($gustHa /$row,1); 
        $data   = convertWind($gustHa,0);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# pressure high low
        $baroHa = round($baroHa /$row,1); 
        $data   = convertBaro($baroHa);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $baroLa = round($baroLa /$row,1); 
        $data   = convertBaro($baroLa);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
# humidity high avg  low 
        $humiHa = round($humiHa /$row,0); 
        $hsum  .= '<td>'.$humiHa.'</td>' .PHP_EOL;
        $humiAa = round($humiAa /$row,0); 
        $hsum  .= '<td>'.$humiAa.'</td>' .PHP_EOL;
        $humiLa = round($humiLa /$row,0);         
        $hsum  .= '<td>'.$humiLa.'</td>' .PHP_EOL;
# rain
        $hsum  .= '<td>-</td>' .PHP_EOL;
        $hsum  .= '</tr>';
# ------------------------- summary rows
# low
        $hsum  .= '<tr class="dark">' .PHP_EOL;
        $hsum  .= '<td colspan="2">'.$LangSumTxtL.'</td>';
# temp high  avg low
        $data   = convertTemps($tempHl);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($tempAl);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($tempLl);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# dewp high  avg low
        $data   = convertTemps($dewpHl);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpAl);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertTemps($dewpLl);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# wind high  avg low
        $data   = convertWind($windHl,0);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
        $data   = convertWind($windAl,0);         
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# gust high  
        $data   = convertWind($gustHl,0);        
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL; 
# pressure high low
        $data   = convertBaro($baroHl);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
        $data   = convertBaro($baroLl);
        $hsum  .= '<td>'.show_data($data).'</td>' .PHP_EOL;
# pressure high avg  low 
        $hsum  .= '<td>'.$humiHl.'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiAl.'</td>' .PHP_EOL;
        $hsum  .= '<td>'.$humiLl.'</td>' .PHP_EOL;
# rain
        $hsum  .= '<td>-</td>' .PHP_EOL;
        $hsum  .= '</tr>';
        $maxH   = 10000;
        $headH  = 192;
        $diffH  = (int) $maxH - $headH;
        echo    '<div id="wu'.$str_other[$mode].'" class="ws_wub" style="display: block; max-height: 100%; width: 100%; '.
                'overflow: hidden; position: relative;  background-color: #EEEEEE;">'; 
        echo    '<table >' . "\n";
        echo    '<tbody>' . "\n";
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$summary_headline.'</span></td></tr>'.PHP_EOL;
        $string = str_replace ("color: black;","color: transparent;",$hdht);
        echo    $string; 
        echo    $hsum;             
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$tabular_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht;
        echo    $html;
        echo    '</tbody>';
        echo    '</table>';
        echo    '</div>';
 /*       echo    '<div style="position: relative; max-height: '.$diffH.'px;">
<div id="wuwrap" class="ws_wub" style="display: block; max-height: '.$maxH.'px; width: 100%; '.
        'position: relative;  top: -'.$headH.'px; overflow: auto;">'.PHP_EOL;
        echo    '<table >' . "\n";
        echo    '<tbody>' . "\n";
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$summary_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht; 
        echo    $hsum;             
        echo    '<tr style="height: 4px;"><td class="blockHead" colspan="'.$cols.'"><span >'.$tabular_headline.'</span></td></tr>'.PHP_EOL;
        echo    $hdht;
        echo    $html;
        echo    '</tbody>';
        echo    '</table>';
        echo    '</div>'; 
        echo    '</div>'; */
        } // eo other tables	
#
if ($showTable  == true && isset ($allow_download) && $allow_download== true)// display link to csv data
     {  echo '<hr style="margin: 2px;" />
<div class="ws_wub"><p class="light" style="text-align: center; vertical-align: middle; padding: 5px 0px 0px 0px; min-height: 20px;">'.
$Lcommatext .'&nbsp;&nbsp;<a href="'.
$wunderCSVstring . '" target="_blank">'. 
$Lhere . '</a></p>'. "\n";
        echo '</div>'.PHP_EOL;
} // End of showing tabular data
#
if ($use_reCAPTCHA)
     {  $echo   =  '        grecaptcha.execute();';}
else {  $echo   =  '        form.submit();';}
	 
echo '<script>
function wsPostFunction(a,b,c) {
        var form = document.getElementById("'.$formName.'");
        form.month.value = b;
        form.link.value= a+"|"+b+"|"+c;
        form.mode.value = 1;
        form.year.value = a;        
        form.day.value = c;
'.$echo.'}
//
function wsPostNextPrev(a) {
        var form = document.getElementById("'.$formName.'");
        form.prevnext.value= a;
'.$echo.'}
//
function wsBoldFunction(a) {
        var item = document.getElementById(a);
        item.style.fontWeight="bold";}
function wsValidate() {';
if ($use_reCAPTCHA)
     { echo ' event.preventDefault(); grecaptcha.execute();';}
echo '}
</script>'.PHP_EOL; 

function show_data($arr, $char='<br />')  // Takes an array and returns data in the proper units
     {  global $units;
	if ($units == "M") { return $arr[0]; }
        if ($units == "E") { return $arr[1]; }
        return  $arr[1] . $char. $arr[0];}

function convertTemps($raw, $prec = 1) // Returns an array with C in [0] and F in [1]
     {  global $rawunits;
        $prc    = (integer) $prec;
        $temp   = (float)   $raw;
        if ($rawunits == "english") 
             {  $amountC   = (($temp - 32) / 1.8 );
                $amountF   = $temp;} 
        else {  $amountC   = $temp;
                $amountF   = (1.8 * $temp) + 32;}
        $return[0] = number_format ($amountC, $prc,',','');
        $return[1] = number_format ($amountF, $prc,'.',''); 
        return $return;}

function convertBaro($raw, $prec = 2)  // Returns an array with mb in [0] and inches in [1]
     {  global $rawunits;
        $prc    = (integer) $prec;
        $baro   = (float)   $raw;
        if ($rawunits == "english") 
             {  $amountM        = $baro * 33.86;
                $amountE        = $baro;}
        else {  $amountM        = $baro;
                $amountE        = $baro / 33.86;}
        $return[0] = number_format ($amountM, $prc-1,',','');
        $return[1] = number_format ($amountE, $prc ,'.',''); 
        return $return;}

function convertWind($raw , $prec = 1)  // Returns an array with km in [0] and mi in [1]
     {  global $rawunits;
        $prc    = (integer) $prec;
        $wind   = (float)   $raw;
        if ($rawunits == "english") 
             {  $amountM= $wind * 1.6093;
                $amountE= $wind;}
        else {  $amountM= $wind;
                $amountE= $wind * .6213;}
        $return[0] = number_format ($amountM, $prc ,',','');
        $return[1] = number_format ($amountE, $prc ,'.',''); 
        return $return;}

function convertRainCM($raw, $prec = 2)   // Returns an array with cm in [0] and inches in [1]
     {  global $rawunits;
        $prc    = (integer) $prec;
        $rain   = (float)   $raw;
        if ($rawunits == "english") 
             {  $amountM= $rain * 2.54;;
                $amountE= $rain;}
        else {  $amountM= $rain;
                $amountE= $rain * 0.394;}
        $return[0] = number_format ($amountM, $prc ,',','');
        $return[1] = number_format ($amountE, $prc ,'.',''); 
        return $return;}

function convertRainMM($raw, $prec = 2)  // Returns an array with mm in [0] and inches in [1]
     {  global $rawunits;
        $prc    = (integer) $prec;
        $rain   = (float)   $raw;
        if ($rawunits == "english") 
             {  $amountM= $rain * 25.4;;
                $amountE= $rain;}
        else {  $amountM= $rain;
                $amountE= $rain * 3.94;}
        $return[0] = number_format ($amountM, $prc ,',','');
        $return[1] = number_format ($amountE, $prc ,'.',''); 
        return $return;}

