<?php   $scrpt_vrsn_dt  = 'PWS_easyweathersetup.php|01|2023-10-31|';  # PA api + row 3 empty block + ' and " + OWM VP pirate nw rel + meteoalarm.eu/org missing weather + metar/aeris + extra selections + checkwx + new URL +  background + eco extra + extra rows + metoffice + snow + check_cron |release 2012_lts
$crt_rls        = '2012_lts'; 
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION https://www.checkwxapi.com/
# Author:       : Wim van der Kuil
# Documentation 
#   and support : https://pwsdashboard.com/
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['print']) && strtolower($_REQUEST['print']) == 'print' ) 
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
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
header('Content-type: text/html; charset=UTF-8');
# ----------------------------- SCRIPT  SETTINGS
$my             =  basename(__FILE__);
$sttngs_fl      = './_my_settings/settings.php';
$stck_lst       = '';
#-----------------------------------------------        
# load current settings file
#-----------------------------------------------
if (is_file($sttngs_fl) )
     {  include $sttngs_fl;
        $sttngs_ldd     = true;}  
else {  $pass = $password = '12345';
        $weather        = array();
        $fill           = array('barometer_units','temp_units','rain_units', 'wind_units'); 
        foreach ($fill as $value) {$weather[$value] = $value;}
        $itsday = $used_lang = $fct_default = $DWL = '';
        $sttngs_ldd     = false;}
$missing = '';
$used   = array(); 
# ----------------------------------------------
# Enclosing html
# $setup_lang
$supp_langs     = array ('en','nl', 'de', 'fr');
if (array_key_exists ('lang',$_REQUEST) ) 
     {  $get_lang       = strtolower(substr (trim($_REQUEST['lang']).'  ',0,2));
        if (in_array($get_lang,$supp_langs) )
             {  $setup_lang = $get_lang;}
        }
else {  $setup_lang = 'en';}
echo '<!DOCTYPE html>
<html lang="'.$setup_lang.'">
<head>
<meta charset="UTF-8"/>
<title>Easyweather setup</title>
<link rel="stylesheet" type="text/css" href="css/configure_css.css" />
</head>
<body style="margin: 0 auto;">
<!-- '.$setup_lang.' -->'.PHP_EOL; 
# ----------------------------------------------
if (isset($_POST['submit_pwd']))
     {  if (isset($_POST['passwd']) ) {$pass = $_POST['passwd']; } else {$pass = '';} 
        if ($pass != $password) 
             {  showForm('<b style="color: red;">A valid password needs to be entered</b>');   
                exit;}
        } 
elseif (!isset($_POST['submit']) )
     {  showForm('PWS_Dashboard setup (version '.$crt_rls.')'); 
        exit; }
if (isset($_REQUEST['lang']) )
     {  $setup_lang = substr(trim($_REQUEST['lang']).'  ',0,2);}
else {  $setup_lang = 'en';}
#else { echo '<pre>'.print_r($_REQUEST,true); exit;}
# ----------------------------------------------
# --------  dummy if lang functions did not load
if (!function_exists('lang') ) { function lang ($txt) { return ($txt);}}
#
# allowed default languages
$arr    = file ('_my_settings/languages.txt');  // this site language settings
$langs_select   = '';
$extra          = '';
foreach ($arr as $string) 
     {  if (substr($string,0,1) == '#') {continue;} // skip comments
        list ($lng_key, $lng_flag, $lng_locale, $lng_file, $lng_txt, $country_flag) = explode ('|',$string.'|||||||||');
        if (trim($lng_key) <> '')    {$lng_key    = trim($lng_key);}    else {continue;}
        if (trim($lng_flag) <> '')   {$lng_flag   = trim($lng_flag);}   else {continue;}
        if (trim($lng_locale) <> '') {$lng_locale = trim($lng_locale);} else {continue;}
        if (trim($lng_file) <> '')   {$lng_file   = trim($lng_file);}   else {continue;}
        if (trim($lng_txt) <> '')    {$lng_txt     = trim($lng_txt);}   else {continue;}
        if (trim($country_flag) <> '')    
             {  $country_flag   = trim($country_flag);}
        else {  $country_flag   = $lng_flag;}
        $lngsArr[$lng_key]=array ('flag' => $lng_flag, 'locale' => $lng_locale, 'file' => $lng_file, 'txt' => $lng_txt, 'ctrflg' => $country_flag);
        $langs_select  .= $extra .$lng_key.'#'.$lng_txt;
        $extra  = '!';
        #break;
}    #echo '<pre>'.print_r ($lngsArr, true); exit;
#
# allowed livedataFormats    # !';\n$arr_vals[]      = '
$livedata_select= '';
$extra          = '';
$arr_vals       = array();
$arr_vals[]     = 'ecoLcl#Ecowitt local upload';
$arr_vals[]     = 'AWapi#API - AmbientWeather.net';
$arr_vals[]     = 'WDapi#API - WeatherDisplay';
$arr_vals[]     = 'wf#API - WeatherFlow';
$arr_vals[]     = 'DWL_v2api#API - WeatherLink Cloud API v2';
$arr_vals[]     = 'DWL#API - WeatherLink Cloud';
$arr_vals[]     = 'wu#API - WeatherUnderground';
$arr_vals[]     = 'wd#Clientraw - WeatherDisplay';
$arr_vals[]     = 'meteohub#Clientraw - Meteohub';
$arr_vals[]     = 'wswin#Clientraw - WSWIN';
$arr_vals[]     = 'cumulus#realtime.txt - Cumulus';
$arr_vals[]     = 'MB_rt#realtime.txt - Meteobridge';
$arr_vals[]     = 'weathercat#realtime.txt - WeatherCat';
$arr_vals[]     = 'weatherlink#realtime.txt - WeatherLink program';
$arr_vals[]     = 'weewx#realtime.txt - Weewx';
$arr_vals[]     = 'wifilogger#realtime.txt - WifiLogger';
foreach ($arr_vals as $str) {$livedata_select.=$extra.$str; $extra = '!';} # echo __LINE__.$livedata_select.$stck_lst.print_r($arr_vals,true).PHP_EOL;
#
# allowed small blocks;
$smll_blck_select       = ''; 
$extra3                 = '';
$lrg_blck_select        = ''; 
$extra6                 = '';
$fct_blck_select        = ''; 
$extraFCT               = '';
$ccn_blck_select        = ''; 
$extraCCN               = '';
$weather                = array(); 
# added to use data from PWS_blocks.php without errors  2022-01-17
$weather["barometer_units"]     = 
$weather['temp_units']          =
$weather["rain_units"]          =
$weather["wind_units"]          = '';
$itsday                         = true;
# added to use data from PWS_blocks.php without errors  2022-01-17
#
include 'PWS_blocks.php'; #echo __LINE__.$stck_lst.$smll_blck_select;
foreach ($blck_type as $key => $type)
     {  if ($type == 's') 
             {  $smll_blck_select       .= $extra3.$key.'#'.$blck_setup[$key];
                $extra3 = '!';
                continue;}
        elseif ($type == 'b')
             {  $lrg_blck_select       .= $extra6.$key.'#'.$blck_setup[$key];
                $extra6 = '!';}
        elseif ($type == 'f')
             {  $fct_blck_select       .= $extraFCT.$key.'#'.$blck_setup[$key];
                $extraFCT = '!';}
        elseif ($type == 'c')
             {  $ccn_blck_select       .= $extraCCN.$key.'#'.$blck_setup[$key];
                $extraCCN = '!';}         
        continue;
 }  // eo every block  #echo __LINE__.$smll_blck_select.$lrg_blck_select;  print_r($ccn_blck_select); exit;
#
configure_now_strings(); # echo '<!-- '.print_r($LANGLOOKUP,true) .'-->';
#
$arr	        = explode  ("\n", $settings_txt);       #  echo '<pre>'.print_r($arr,true).'</pre>'.PHP_EOL; exit;
$form   = array();
foreach ($arr as $line) 
     {  if (substr($line,0,1)  <> '|') {continue;}
        $items	= explode ('|', $line);	# echo '<pre>';  print_r($items); exit;	
        if (!is_numeric ($items[1]) ) 
             {  echo '<!-- '.$my.' ('.__LINE__.'): settings text line '.$n.' skipped - nr not numeric -->'.PHP_EOL;	
                continue;}
        $nr		= $items[1];
        $text		= trim($items[2]);
        $set_wp		= $text;
        $set_region	= trim($items[3]);
        if (!isset ($items[4]) ) {      # echo $start_echo.'Error in line '.$line.$end_echo; continue;
                echo   '<!-- '.$my.' ('.__LINE__.'): Error in line '.$line.' -->'.PHP_EOL;
        }
        $set_key	= trim($items[4]);
        $set_type	= trim($items[5]);
        $set_old	= trim($items[6]);        
        $set_new        = '';
        if (isset ($$set_key) ) 
             {  $value  = $$set_key;
                if      ($value === true)  { $value = 'true';}
                elseif  ($value === false) { $value = 'false';}
                else                       { $value = $value;}
                $set_new = $set_old = $value;}
        if (!isset ($items[7]) ) 
             {  $set_values 	= ''; }
        else {	$set_values	= trim($items[7]);}
        $form [] = array ('wp' => $set_wp,'region' => $set_region,'setting' => $set_key,'type' => $set_type, 'new' => $set_new,'old' => $set_old ,'values' => $set_values);		
} // eo for each line
#echo '<pre>'.print_r($form,true); exit;
# ----------------------------------------------
# if entry-form was filled in, save all entered values to a file
#
if (isset ($_POST['submit']) )  
    {   $string = '<?php  $scrpt_vrsn_dt  = \'settings.php|01|'.gmdate ('c').'|\'; # release '.$crt_rls.PHP_EOL;
$stck_lst      .= basename(__FILE__)." (".__LINE__.") version =>".$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
# '.PHP_EOL;
        $lookup = array();
        $values = array();
        $sttng_lngth    = 22;
        $spaces = str_repeat(' ',$sttng_lngth);
        foreach ($form as $arr)
            {   if ($arr['type'] <> '#') 
                     {  $key            = $arr['setting'];
                        $lookup[$key]   = $arr['type'];}}
        $arr    = $_POST['settings']; # print_r($arr) ; exit;
        if (    !array_key_exists ('hash',$_POST)
            ||  password_verify($password,$_POST['hash']) == false
            ||  !array_key_exists ('password',$arr ) 
            ||  count($arr)  < 120 )
             {  sleep(4);
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not found');
                $output = '<html><head>';
                $output.= '<style type="text/css">body{width: 800px; font-family: Arial,Helvetica,sans-serif,sans;margin:0;}.centerIn{text-align:center;}.head{background:#DCDCDC;padding:12px;}</style>';
                $output.= '</head><body class="centerIn">';
                $output.= '<br /><br /><br /><h1>Error 404 - Not found</h1><p class="head" style="width: 800px;">Sorry, 
The current data could not be processed.
<br />Our systems have logged this problem and support will try to find a solution.
<br />Post your problem at the support forum and mention this error_code '.__LINE__.'-'.count($arr).' 
<br />and the date/time. '.date ('c').'</p>
<p>'.password_verify($password,$_POST['hash']).'</p>';
                $output.= '</body></html>';
	        die($output); }
# die ('all ok'); 
        $from   = array ('"','\'');
        $to     = array ('&quote;','&apos;');
        foreach ($arr as $key => $value)
             {  $name   = $key;
                $value  = str_replace($from,$to,$value);
                if     ($value == 'true')  {$value   = 'true';}
                elseif ($value == 'false') {$value   = 'false';}
                else                       {$value   = "'".$value."'";}
                $extra  = '';
                $type   = $lookup[$key];
                if ($type ==  'numberDecimal') {$extra = '(float) ';}
                elseif ($type ==  'noDecimal') {$extra = '(int) ';}
                $key = substr($spaces.'$'.$key,-$sttng_lngth);
                $string.= $key.' = '.$extra.$value.';'.PHP_EOL;
                $values[$name]  = $value;
                } // eo foreach

        $metar_popup = $aeris_popup = false;       
        $metarapikey    = str_replace("'",'',$values['metarapikey']);  # 2020-04-16
        if ($metarapikey <> 'ADD YOUR API KEY' && trim($metarapikey) <> '')
             {  $metar_popup = true; }
        $aeris_access_id= str_replace("'",'',$values['aeris_access_id']);  # 2020-04-16
        if ($aeris_access_id <> 'ADD YOUR API KEY' && trim($aeris_access_id) <> '')
             {  $aeris_popup = true; }

        $sky_selected   = str_replace("'",'',$values['sky_default']);    # 2021-08-29 
        if (    $sky_selected <> 'ccn_aeris_block.php' 
             && $metar_popup == true)
             {   $aeris_popup = false;}
        if ( $aeris_popup == true )
             {  $metar_popup = false; 
                $string.= '          $aeris_popup = true;'.PHP_EOL;}
        else {  $string.= '          $aeris_popup = false;'.PHP_EOL;}
        if ( $metar_popup == true )        
             {  $string.= '          $metar_popup = true; '.PHP_EOL;}
        else {  $string.= '          $metar_popup = false;'.PHP_EOL;}
                    
        $defaultlanguage= str_replace("'",'',$values['defaultlanguage']);
        $string        .= '         $country_flag = \''.$lngsArr[$defaultlanguage]['ctrflg'].'\'; '.PHP_EOL;

        $wu_csv_unit    = str_replace("'",'',$values['unit']);
        $string        .= '          $wu_csv_unit = \''.$wu_csv_unit.'\'; '.PHP_EOL;

        if (array_key_exists('HTTP_REFERER',$_SERVER))
             {  $from   = array ('PWS_easyweathersetup.php', 'easyweathersetup.php');
                $this_server    =  str_replace ($from,'',$_SERVER['HTTP_REFERER']);
                $string.= '          $this_server = \''.$this_server.'\';'.PHP_EOL.'#';}
# save new settings to file
        $fp = fopen($sttngs_fl, "w") or die("Unable to open ./_my_settings/settings.php file check file permissions !");
        fwrite($fp, $string);
        fclose($fp);
        $sttngs_ldd = true;
        showForm('PWS_Dashboard setup (version '.$crt_rl.')'); 
        exit();         
        } // eo entry form received
# convert old to new settings release 2012_lts_march
# $extra3used  wide row none
# position1e position2e position3e
if (isset ($extra3used) )
     {  echo '<!-- convert $extra3used '.$extra3used.' -->'.PHP_EOL;
        $cols_extra = $rows_extra = 0;
        if ($extra3used == 'wide') 
             {  $cols_extra = 1;
                $position14     = $position1e;
                $position24     = $position2e;
                $position34     = $position3e;}
        elseif ($extra3used == 'row')
             {  $rows_extra = 1;
                $position41     = $position1e;
                $position42     = $position2e;
                $position43     = $position3e;}
        unset ($extra3used);
        }
echo '<div id="config__manager" style="max-width: 1200px;">
<form action= "PWS_easyweathersetup.php" method="post">
<input type="hidden" name="hash" value="'.password_hash($password, PASSWORD_DEFAULT).'">
<input type="hidden" name="lang" value="'.$setup_lang.'">
<div class="tab" style="">
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Start\')" id="defaultOpen">'.langtransstr('Start').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Location\')">'.langtransstr('Location').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Data\')">'.langtransstr('Data').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Units\')">'.langtransstr('Units').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Devices\')">'.langtransstr('Devices').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Tiles\')">'.langtransstr('Tiles').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'API&amp;Keys\')">'.langtransstr('Api&amp;Keys').'</label>
  <label class="tablinks" style="margin-bottom: 0px;" onclick="openTab(event, \'Other\')">'.langtransstr('Other').'</label>
  <label style="margin-bottom: 0px; background-color: green; float: right;">
        <input type="submit" style="width: 200px; text-align: center; border: none;  background-color: green; color: white;" name="submit" class="button"
       value="'.langtransstr('Save your settings').'"></label>
</div>
<table id="Start" class="tabcontent">
<tr ><td colspan="2" style="text-align: center; padding: 4px;"><b>'
.langtransstr('Welcome by EasyweatherSetup').'</b><br />'
.langtransstr('The "settings" to adapt the dashboard to your situation are grouped into 8 pages.').' '
.langtransstr('Click on a grey button above to go the another set of questions.').'<br />'
.langtransstr('When you are finished, or if want to stop fo a while, save your answers by clicking at the green button at the top right.').'<br />&nbsp;'.PHP_EOL;
if ($sttngs_ldd) 
     {  echo '<br />'
        .langtransstr('If you finished all your settings, you should regularly check for updated scripts').' <a href="./PWS_updates.php"><b>'
        .langtransstr('here').'</b></a>'.PHP_EOL; }
echo '<br /></td></tr>';
#
#echo '<pre>'.print_r($form,true).'</pre>'.PHP_EOL; exit;

foreach ($form as $key => $arr) # generate the input form
     {  tr_setting($key, $arr);}
echo '</table><br />
</form>
</div><br />
<script>
function openTab(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "table";
  evt.currentTarget.className += " active";
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>'.PHP_EOL;
#   ksort ($used);  ksort ($LANGLOOKUP);  echo '<!-- '. $missing.' '.print_r ($used,true).' '.print_r ($LANGLOOKUP,true).' -->';
echo '</body>
</html>';  
return;
# ----------------------------------------------
function langtransstr($string)  # to translate texts
     {  global $LANGLOOKUP, $missing, $used;
        $used[$string] = 1;
        if (isset ($LANGLOOKUP[$string]))
             {  return $LANGLOOKUP[$string]; }
        else {  $missing .= $string.PHP_EOL;
                $LANGLOOKUP[$string] = $string;
                return $string;}} // eof langtransstr
# 
function showForm ($msg="LOGIN")# to display password form
     {  global $password, $sttngs_ldd , $LANGLOOKUP, $setup_lang, $crt_rl;
        #if (isset ($_POST)) {echo '<!-- '.print_r($_POST,true) .$password.' -->';}
        if (!isset ($setup_lang) ) {$setup_lang = 'en';}
if ($setup_lang == 'nl') {
        $LANGLOOKUP['PWS_Dashboard setup (version '.$crt_rl.')'] = 'PWS_Dashboard-configuratie (versie '.$crt_rl.')';
        $LANGLOOKUP['Back to the main page'] = 'Terug naar uw startpagina';
        $LANGLOOKUP['Enter your password to adapt your settings'] = 'Voer uw wachtwoord in om uw instellingen aan te passen';
        $LANGLOOKUP['Select the language to use during setup'] = 'Selecteer de taal die u wilt gebruiken tijdens de installatie';
        $LANGLOOKUP['Your current PHP version is'] = 'Uw huidige PHP-versie is';
        $LANGLOOKUP['HP 7+ is advised but PWS_Dashboard will run also with PHP 5.6.3 or higher']  
                = 'PHP 7+ wordt geadviseerd, maar PWS_Dashboard werkt ook met PHP 5.6.3 of hoger';
        $LANGLOOKUP['<b style="color: red;">A valid password needs to be entered</b>']  
                = '<b style="color: red;">Er moet een geldig wachtwoord worden ingevoerd</b>';
        }
elseif ($setup_lang == 'de') {
        $LANGLOOKUP['PWS_Dashboard setup (version '.$crt_rl.')'] = 'PWS_Dashboard-Setup (Version '.$crt_rl.')';
        $LANGLOOKUP['Back to the main page'] = 'Zurück zur Hauptseite';
        $LANGLOOKUP['Enter your password to adapt your settings'] = 'Geben Sie Ihr Passwort ein, um Ihre Einstellungen anzupassen';
        $LANGLOOKUP['Select the language to use during setup'] = 'Wählen Sie die Sprache aus, die während der Einrichtung verwendet werden soll';
        $LANGLOOKUP['Your current PHP version is'] = 'Ihre aktuelle PHP-Version ist';
        $LANGLOOKUP['HP 7+ is advised but PWS_Dashboard will run also with PHP 5.6.3 or higher']  
                = 'PHP 7+ wird empfohlen, aber PWS_Dashboard läuft auch mit PHP 5.6.3 oder höher';
        $LANGLOOKUP['<b style="color: red;">A valid password needs to be entered</b>']  
                = '<b style="color: red;">Es muss ein gültiges Passwort eingegeben werden</b>';
        }
elseif ($setup_lang == 'fr') {
        $LANGLOOKUP['PWS_Dashboard setup (version '.$crt_rl.')'] = 'Configuration de PWS_Dashboard (version '.$crt_rl.')';
        $LANGLOOKUP['Back to the main page'] = 'Retour à la page principale';
        $LANGLOOKUP['Enter your password to adapt your settings'] = 'Entrez votre mot de passe pour adapter vos paramètres';
        $LANGLOOKUP['Select the language to use during setup'] = 'Sélectionnez la langue à utiliser lors de la configuration';
        $LANGLOOKUP['Your current PHP version is'] = 'Votre version actuelle de PHP est';
        $LANGLOOKUP['HP 7+ is advised but PWS_Dashboard will run also with PHP 5.6.3 or higher']  
                = 'PHP 7+ est conseillé mais PWS_Dashboard fonctionnera également avec PHP 5.6.3 ou supérieur';
        $LANGLOOKUP['<b style="color: red;">A valid password needs to be entered</b>']  
                = '<b style="color: red;">Un mot de passe valide doit être entré</b>';
        }
#        
        echo  '<div style="text-align: center;"><br />'.langtransstr($msg).'<br /><br />'.PHP_EOL;
if ($sttngs_ldd == true) {   
        echo '<a href="./index.php">'. langtransstr('Back to the main page').'</a><br /><br />'.PHP_EOL; 
        }
echo '<div style = " width:600px; margin:0 auto; color:rgba(24, 25, 27, 1.000); border:solid 1px grey; padding:10px; border-radius:4px;" >
<form action = "PWS_easyweathersetup.php" method="post" name="pwd" > 
<center>
'.langtransstr('Enter your password to adapt your settings').'
    <br /><input name = "passwd" type= "password"  class = "input" />  
    <br /><br />'.langtransstr('Select the language to use during setup').'<br />
    <select id="lang" name="lang" style="font-size: 9px">'.PHP_EOL;
if ($setup_lang == 'en') {$txt  = ' selected="selected" '; } else {$txt  = '';} 
echo '        <option value="en"'.$txt.'>English</option>'.PHP_EOL;
if ($setup_lang == 'nl') {$txt  = ' selected="selected" '; } else {$txt  = '';} 
echo '        <option value="nl"'.$txt.'>Nederlands</option>'.PHP_EOL;
if ($setup_lang == 'fr') {$txt  = ' selected="selected" '; } else {$txt  = '';} 
echo '        <option value="fr"'.$txt.'>Français</option>'.PHP_EOL;
if ($setup_lang == 'de') {$txt  = ' selected="selected" '; } else {$txt  = '';} 
echo '        <option value="de"'.$txt.'>Deutsch</option>
    </select>
    <br /><br /><input type = "submit" name= "submit_pwd" value="Login" class="btn" />
</center>         
</form>
<hr />
<b>Info:</b>'.langtransstr('Your current PHP version is').'  : ' . phpversion(). ' <br>
'.langtransstr('PHP 7+ is advised but PWS_Dashboard will run also with PHP 5.6.3 or higher').'
</div>
</div>
</body>
</html>';
}

function configure_now_strings() # load forl all settings the description, allowed value a.s.o.
     {  global $settings_txt, $LANGLOOKUP, $LINKLOOKUP,$langs_select ,$livedata_select , 
                $smll_blck_select, $lrg_blck_select, $ccn_blck_select, $fct_blck_select ,
                $setup_lang, $supp_langs;
        $settings_txt = "
#---------------------------------------------------------------------------------------|	
#nr|xx	|xxxxx	|key =>  SITE	|type 		|old/default	|values allowed		|
#---------------------------------------------------------------------------------------|
|00|--	|--	|--		|#		|conf-password_hd|                      |
|00|--	|--	|password	|htmltext	| 		|                       |
|00|--	|--	|show_settings	|select	        |true           |true#Yes, we do.!false#All settings are OK, do not show that menu entry|
|00|--	|--	|--		|#		|conf-descriptions_hd|                      |
|00|--	|--	|stationName    |htmltext	|xyz-weatherstation|                    |
|00|--	|--	|stationlocation|htmltext	|A city in Belgium|                       |
|00|--	|--	|--		|#		|conf-contact_hd|                      |
|00|--	|--	|contact_show   |select	        |false          |true#Yes, we do.!false#Do not use this|
|00|--	|--	|email          |htmltext	|someone@dot.com|                       |
|00|--	|--	|twitterUser    |select	        |false          |true#Yes, we do.!false#Do not use this|
|00|--	|--	|twitter        |htmltext	|pwsweather     |       |
|00|--	|--	|facebookuser   |select	        |false          |true#Yes, we do.!false#Do not use this|
|00|--	|--	|facebook       |htmltext	|pwsweather     |       |
|00|--	|--	|--		|#		|conf-language_hd|                      |
|00|--	|--	|defaultlanguage|select	        |en	        |".$langs_select."|
|00|--	|--	|lang_select	|select	        |true	        |true#conf-lang_select_yes!false#Do not allow this|

|00|--	|--	|--		|#		|conf-future_hd|                      |
|00|--	|--	|solve_problem1 |htmltext	|not_used|                       |
|00|--	|--	|solve_problem2 |htmltext	|not_used|                       |

|01|--	|--	|--		|##		|Location|                      |
|01|--	|--	|--		|#		|conf-location_hd|                      |
|01|--	|--	|TZ             |htmltext	|Europe/Brussels|                       |
|01|--	|--	|noDST          |select	        |DST            |noDST#Officially they use Daylight Saving, but we personally do not want that.!DST#I do not need this, use always official time zone standards|
|01|--	|--	|lat            |numberDecimal	|50.8500        |                       |
|01|--	|--	|lon            |numberDecimal	|4.3400         |                       |
|01|--	|--	|icao1          |allcap	        |EBBR           |4                      |
|01|--	|--	|airport1       |htmltext	|AirportName    |                       |
|01|--	|--	|airport1dist   |noDecimal      |14             |                       |
|01|--	|--	|--		|#		|conf-hardware_hd|                      |
|01|--	|--	|hardware	|htmltext	|Davis Vantage Pro|                     |
|01|--	|--	|manufacturer   |select	        |other          |davis#Davis!weatherflow#Weatherflow!fineoffset#FineOffset!other#Other|

|03|--	|--	|--		|##		|Data|                      |
|03|--	|--	|--		|#		|conf-livedata_hd|                      |
|03|--	|--	|livedataFormat |select		|wd             |".$livedata_select."|
|03|--	|--	|livedata	|htmltext	|./demodata/clientraw.txt|                |
|03|--	|--	|liveYMD	|select	        |c b a          |a b c#Year  Month  Day!c b a#Day Month Year!c a b#Month Day Year|
|03|--	|--	|--		|#		|conf-extra_sensors_hd|                      |
|03|--	|--	|have_extra	|select	        |false          |true#Yes, we have extra sensors and upload the data.!false#Do not use this|
|03|--	|--	|extra_data	|htmltext	|use demodata|                |
|03|--	|--	|--		|#		|conf-snow_hd|                      |
|03|--	|--	|snow_show	|select	        |none           |none#We do not measure snow heights.!manual#We use the dashboard script to enter the snow heights!data#We enter and upload the values with our weather-program.|
|03|--	|--	|--		|#		|conf-skydata_hd|                      |
|03|--	|--	|sky_default    |select		|ccn_metar_block.php|".$ccn_blck_select."|
|03|--	|--	|--		|#		|conf-fctdata_hd|                      |
|03|--	|--	|fct_default    |select		|fct_yrno_block.php|".$fct_blck_select."|
|03|--	|--	|--		|#		|conf-weatheralarm_hd|                      |
|03|--	|--	|weatheralarm   |select	        |europe         |none#Do not use this!europe#Europe uses meteoalarm.org!uk#UK MetOfffice!curly#USA use NWS!canada#Use Environment Canada!au#Use bom.gov.au|
|03|--	|--	|alarm_area     |htmltext	|BE004          |                       |
|03|--	|--	|province       |htmltext	|ON             |    |
|03|--	|--	|--		|#		|conf-history_hd|                      |
|03|--	|--	|charts_from	|select	        |'WU'      	|WU#Use our WeatherUnderground data for charts!DB#Save the daily data for the charts|

|03|--	|--	|--		|#		|conf-cron_hd|                      |

|11|--	|--	|--		|##		|Units|       |
|11|--	|--	|--		|#		|conf-units_used_hd|       |
|11|--	|--	|unit           |select         |metric         |metric#metric: C_km/h_hPa_mm_km!us#imperial: F_mph_inHg_in_mi!uk#uk: C_mph_hPa_mm_mi!scandinavia#nordic: C_m/s_hPa_mm_m/s |
|11|--	|--	|dec_tmp        |select         |1              |0#No decimals for temperature!1#1 decimal for temperature values |
|11|--	|--	|dec_wnd        |select         |1              |0#No decimals for wind values!1#1 decimal for wind values|
|11|--	|--	|dec_rain       |select         |1              |0#No decimal for rain!1#1 decimal for rain in mm, 2 for rain in inches|
|11|--	|--	|dec_baro       |select         |1              |0#No decimal for barometerin hPa, 1 decimal for barometer in  inHg!1#1 decimal for barometer in hPa, 2 for barometer in inHg|
|11|--	|--	|rainrate       |select         |/h             |/h#per hour!/m#per minute |
|11|--	|--	|cloudbase      |select         |metres         |metres#metres!feet#feet  |
|11|--  |--     |aqhi_type      |select         |epa            |epa#EPA (worldwide)!eea#EEA Europe|

|11|--	|--	|--		|#		|conf-date_time_hd|       |
|11|--	|--	|dateFormat     |select         |d-m-Y           |d-m-Y#d-m-Y for DAY MONTH YEAR format (12-03-2017)!"
                                                                ."m-d-Y#m-d-Y for MONTH DAY YEAR format (03-12-2017)!"
                                                                ."Y-m-d#Y-m-d  for YEAR MONTH DAY format (2017-12-03)|
|11|--	|--	|clockformat    |select         |24              |24#24 Main Clock output example 17:32:12!"
                                                                ."12#12 Main Clock output example 5:32:12 pm|
|11|--	|--	|timeFormat     |select         |g:i a           |H:i:s#H:i:s  = 17:34:22 format!"
                                                                ."g:i:s a#g:i:s a = 05:34:22 am format!"
                                                                ."g:i a#g:i a = 05:34 am format!"
                                                                ."g:i:s#g:i:s = 05:34:22 format|
|11|--	|--	|timeFormatShort|select         |H:i             |H:i#H:i = 17:34 format!"
                                                                ."g:i a#g:i a = 5:34pm format|
###
|15|--	|--	|--		|##		|Devices|                      |
|15|--	|--	|--		|#		|conf-purpleair_aq_hd|                      |
|15|--	|--	|purpleairhardware|select       |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|purpleairID    |noDecimal      |00000          |                       |
|15|--	|--	|purpleairAPI   |htmltext       |ADD YOUR API KEY| |
|15|--	|--	|--		|#		|conf-luftdaten_aq_hd|                      |
|15|--	|--	|luftdatenhardware|select       |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|luftdatenID    |noDecimal      |0000           |                       |
|15|--	|--	|luftdatenSensor|noDecimal      |000000         |                       |
|15|--	|--	|--		|#		|conf-davis_aq_hd|                      |
|15|--	|--	|davis_aq_sensor|select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|dwl_AQ         |noDecimal      |0000           |                       |
|15|--	|--	|--		|#		|conf-official_aq_hd|                      |
|15|--	|--	|gov_aqi        |select         |false          |true#Yes, we want to use that AQI.!false#Do not use this|
|15|--	|--	|waqitoken      |htmltext       |               |                       |
|15|--	|--	|--		|#		|conf-boltek_lightnng_hd|                      |
|15|--	|--	|boltek         |select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|boltekfile     |htmltext       |demodata/NSRealtime.txt|               | # boltekfile = nexstorm
|15|--	|--	|--		|#		|conf-weatherflow_hd|                      |
|15|--	|--	|weatherflowoption|select       |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|weatherflowID  |noDecimal      |00000          |                       |
|15|--	|--	|--		|#		|conf-eco-extra_hd|                      |
|15|--	|--	|ecowittoption  |select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|ecowittfile    |htmltext       |./ecowitt/ecco_lcl.arr|                       |
|15|--	|--	|ecowittAQ       |select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|ecowittlightning|select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|--		|#		|conf-webcam_hd|                      |
|15|--	|--	|mywebcam       |select         |false          |true#Yes, we do.!false#Do not use this|
|15|--	|--	|mywebcamimg    |htmltext       |replace wiht link               |                       |
|15|--	|--	|--		|#		|conf-uv_solar_hd|                      |
|15|--	|--	|uvsolarsensors |select         |false          |false#Do not have these!"
                                                                ."both#Our station has both UV and solar!"
                                                                ."darksky#Our station has solar. Use Darksky UV forcast as the UV sensor!"
                                                                ."wf#We use a weatherflow device for UV and solar|
###
|19|--	|--	|--		|##		|Tiles|                      | 
|19|--	|--	|--		|#		|conf-top_small_hd|                      | 
|19|--	|--	|positionlast	|select 	|advisory_c_small.php  |advisory_c_small.php#Always the advices are displayed here|
|19|--	|--	|position1	|select 	|wind_c_small.php      |".$smll_blck_select."|
|19|--	|--	|position2	|select 	|temp_c_small.php      |".$smll_blck_select."|
|19|--	|--	|position3	|select 	|rain_c_small.php      |".$smll_blck_select."|
|19|--	|--	|position4	|select 	|earthquake_c_small.php|".$smll_blck_select."|

|20|--	|--	|--		|#		|conf-extra3wused_hd|                      |
|20|--	|--	|cols_extra     |select         |none              |0#Do not use this!"
                                                                  ."1#Yes, we need 1 extra block on the right of each row.|
                                                                
|20|--	|--	|--		|#		|conf-top_row_hd|
|20|--	|--	|position11	|select         |temp_c_block.php      |".$lrg_blck_select."| 
|20|--	|--	|position12	|select         |fct_block.php         |".$lrg_blck_select."|
|20|--	|--	|position13     |select         |sky_block.php         |".$lrg_blck_select."|
|20|--	|--	|position14     |select         |dummy                 |dummy#Select later!".$lrg_blck_select."|

|20|--	|--	|--		|#		|conf-middle_row_hd|                      |
|20|--	|--	|position21	|select         |wind_c_block.php      |".$lrg_blck_select."| 
|20|--	|--	|position22	|select         |baro_c_block.php      |".$lrg_blck_select."|
|20|--	|--	|position23     |select         |sun_c_block.php       |".$lrg_blck_select."|
|20|--	|--	|position24     |select         |dummy                 |dummy#Select later!".$lrg_blck_select."|

|20|--	|--	|--		|#		|conf-bottom_row_hd|                      |
|20|--	|--	|position31	|select         |rain_c_block.php      |".$lrg_blck_select."|
|20|--	|--	|position32	|select         |webcam_c_block.php    |".$lrg_blck_select."|
|20|--	|--	|position33 	|select 	|moon_c_block.php      |".$lrg_blck_select."|
|20|--	|--	|position34     |select         |dummy                 |dummy#Select later!none#Do not use this!".$lrg_blck_select."|

|20|--	|--	|--		|#		|conf-extrarows_hd|                      |
|20|--	|--	|rows_extra     |select         |none              |0#Do not use this!"
                                                                  ."1#Yes, we need a fourth row of blocks.!"
                                                                  ."2#Yes, we need a fourth and fifth row of blocks.|

|20|--	|--	|--		|#		|conf-extra_row1_hd|                      |
|20|--	|--	|position41     |select 	|uvsolarlux_c_block.php|dummy#Select later!".$lrg_blck_select."|
|20|--	|--	|position42     |select         |extra_temp_block.php  |dummy#Select later!".$lrg_blck_select."|
|20|--	|--	|position43     |select         |webcam_c_block.php    |dummy#Select later!none#Do not use this!".$lrg_blck_select."|
|20|--	|--	|position44     |select         |dummy                 |dummy#Select later!none#Do not use this!".$lrg_blck_select."|

|20|--	|--	|--		|#		|conf-extra_row2_hd|                      |
|20|--	|--	|position51     |select 	|uvsolarlux_c_block.php|dummy#Select later!".$lrg_blck_select."|
|20|--	|--	|position52     |select         |extra_temp_block.php  |dummy#Select later!".$lrg_blck_select."|
|20|--	|--	|position53     |select         |webcam_c_block.php    |dummy#Select later!none#Do not use this!".$lrg_blck_select."|
|20|--	|--	|position54     |select         |dummy                 |dummy#Select later!none#Do not use this!".$lrg_blck_select."|


|30|--	|--	|--		|##		|API&amp;Keys |                      |
|30|--	|--	|--		|#		|conf-suppliers_YRNO|                      |
|30|--	|--	|yrno_area 	|htmltext       |CHECK yr.no website|                      |

|30|--	|--	|--		|#		|conf-suppliers_AERIS|                      |
|30|--	|--	|aeris_access_id|htmltext       |ADD YOUR API KEY| |
|30|--	|--	|aeris_secret_key|htmltext      |ADD YOUR API KEY| |

|30|--	|--	|--		|#		|conf-suppliers_WU|                      |
|30|--	|--	|wu_apikey 	|htmltext       |ADD YOUR API KEY| |
|30|--	|--	|wuID 	        |htmltext       |no key| |
|30|--	|--	|wu_start 	|htmltext       |YYYY-MM-DD| |

|30|--	|--	|--		|#		|conf-suppliers_DS|                      |
|30|--	|--	|dark_alt_vrs 	|select	        |nu |nu#Not used!ow#OpenWeatherMap!vc#Virtual Crossing!ds#Original DarkSky!pw#PirateWeather|
|30|--	|--	|dark_apikey 	|htmltext       |ADD YOUR API KEY| |
|30|--	|--	|language 	|select	        |en |ar#Arabic!az#Azerbaijani!be#Belarusian!bg#Bulgarian!bs#Bosnian!ca#Catalan!cs#Czech!da#Danish!de#German!el#Greek!"
                                                        ."en#English (which is the default)!es#Spanish!et#Estonian!fi#Finnish!fr#French!he#Hebrew!hr#Croatian!hu#Hungarian!"
                                                        ."id#Indonesian!is#Icelandic!it#Italian!ja#Japanese!ka#Georgian!ko#Korean!kw#Cornish!lv#Latvian!nb#Norwegian Bokmål!"
                                                        ."nl#Dutch!no#Norwegian Bokmål (alias for nb)!pl#Polish!pt#Portuguese!ro#Romanian!ru#Russian!sk#Slovak!sl#Slovenian!sr#Serbian!sv#Swedish!"
                                                        ."tet#Tetum!tr#Turkish!uk#Ukrainian!x-pig-latin#Igpay Atinlay!zh#simplified Chinese!zh-tw#traditional Chinese|
|30|--	|--	|darkskyunit 	|select         |si|si#si:Standard ISO!ca#ca: same as si, windSpeed km/h!uk2#uk: same as si,windSpeed mph!us#us: Imperial units (NON METRIC) |

|30|--	|--	|--		|#		|conf-suppliers_METAR| 
|30|--	|--	|metarapikey    |htmltext       |ADD YOUR API KEY| |

|30|--	|--	|--		|#		|conf-suppliers_AW|                      |
|30|--	|--	|aw_key         |htmltext       |ADD YOUR API KEY| |
|30|--	|--	|aw_did         |htmltext       |your DID| |

|30|--	|--	|--		|#		|conf-suppliers_DWL|                      |
|30|--	|--	|dwl_api        |htmltext       |ADD YOUR API KEY| |
|30|--	|--	|dwl_did        |htmltext       |your DID| |
|30|--	|--	|dwl_pass       |htmltext       |wl.com password| |

|30|--	|--	|--		|#		|conf-suppliers_DWL2|                      |
|30|--	|--	|dwl_api2       |htmltext       |ADD YOUR API KEY| |
|30|--	|--	|dwl_secret     |htmltext       |ADD YOUR API KEY| |
|30|--	|--	|dwl_station    |noDecimal      |000000| |

|40|--	|--	|--		|##		|Other|                          |
|40|--	|--	|--		|#		|conf-menu_hd|                          |
|40|--	|--	|extralinks     |select         |false          |true#Yes, we do.!false#Do not use this|
|40|--	|--	|themes         |select         |true           |true#Allow visitor to switch colour themes.!false#Not allowed|
|40|--	|--	|theme1         |select         |user           |light#light!dark#dark!user#your own color set|

|40|--	|--	|--		|#		|conf-other_hd|                      |
|40|--	|--	|show_indoor    |select         |true          |true#Yes, we do.!false#Do not use this|
|40|--	|--	|body_image     |htmltext       |||

|40|--	|--	|check_cron     |select         |false         |true#Yes, we do.!false#Do not use this|
|40|--	|--	|KISS           |select         |false         |true#Yes, we do.!false#Do not use this|
|40|--	|--	|use_round      |select         |false         |true#Yes make items round.!false#We like the square items|
|40|--	|--	|txt_border     |select         |true          |true#Leave the small borders around text blocks, we like that.!false#We like \"Less is more\", remove those borders|
|40|--	|--	|close_popup    |select         |true          |true#Yes, show the close button/text in every pop-up!false#Do not use this|
|40|--	|--	|personalmessage|htmltext       |Never base important decisions that could result in harm to people or property on this weather information.||
|40|--	|--	|--		|#		|conf-cron_hd|                      |
";
#EOT;
/*        $supp_langs     = array ('en','nl');
        if (array_key_exists ('lang',$_GET) ) 
             {  $get_lang       = strtolower(substr (trim($_GET['lang']).'  ',0,2));
                if (in_array($get_lang,$supp_langs) )
                     {  $setup_lang = $supp_langs[$get_lang];}
                }
        if (!isset ($setup_lang)) {$setup_lang = 'en';}  */
        if ($setup_lang == 'nl' && !isset ($LANGLOOKUP['conf-password_hd'])) {
        if ( 1 == 1) {
$LANGLOOKUP['conf-password_hd']         = 'Bescherm uw instellingen met een wachtwoord';
$LANGLOOKUP['conf-password']            = 'Gebruik een string die u kunt onthouden, minimaal 10 karakters, gebruik geen " of \' karakters';
$LANGLOOKUP['conf-show_settings']       = 'Een menu-item gebruiken voor dit instellingen-script?';
$LANGLOOKUP['conf-language_hd']         = 'Kies de standaardtaal die u wilt weergeven en gebruiken.';
$LANGLOOKUP['conf-defaultlanguage']     = 'Taal die als standaard moet worden gebruikt';
$LANGLOOKUP['conf-lang_select']         = 'Mogen bezoekers de taal wijzigen?';
$LANGLOOKUP['conf-lang_select_yes']     = 'Een bezoeker kan de gebruikte taal wijzigen';
$LANGLOOKUP['conf-future_hd']           = 'Voor toekomstig gebruik wanneer nieuwe items nodig zijn';
$LANGLOOKUP['conf-solve_problem1']      = 'Laat het zoals het is';
$LANGLOOKUP['conf-solve_problem2']      = 'Wordt gebruikt als een "nog onbekend probleem" op duikt';
$LANGLOOKUP['conf-country_flag']        = 'Uw land vlag';
$LANGLOOKUP['conf-livedata_hd']         = 'Weerprogramma / databestand dat we zullen gebruiken';
$LANGLOOKUP['conf-livedataFormat']      = 'Hoe krijgen we onze weergegevens op uw website?<br />Wijzig alleen als u zeker weet dat uw gegevensbestand beschikbaar is via de juiste upload of via een API';
$LANGLOOKUP['conf-livedata']            = 'Pad naar het door uw weer-programma  <b>opgeladen</b> bestand.<br />Het juiste pad is essentieel voor uw  gegevensweergave.<br />Voorbeeld "../clientraw.txt" wanneer uw bestand zich in de root bevindt.<br />Wordt niet gebruikt met een API.';
$LANGLOOKUP['conf-skydata_hd']          = 'Wolken-Hemel condities';
$LANGLOOKUP['conf-sky_default']         = 'Hoe komen we aan de huidige "Sky"-condities?';
$LANGLOOKUP['conf-fctdata_hd']          = 'Weervoorspelling';
$LANGLOOKUP['conf-fct_default']         = 'Welke weersvoorspelling wilt u gebruiken?';
$LANGLOOKUP['conf-extra_sensors_hd']    = 'Aparte upload van de extra sensoren van uw weerstation';
$LANGLOOKUP['conf-have_extra']          = 'Bij gebruik van extra sensoren kan een extra databestand nodig zijn';
$LANGLOOKUP['conf-extra_data']          = 'Pad en bestandsnaam van het geüploade bestand (bijv. demodata/extra_sensors.txt )';
$LANGLOOKUP['conf-snow_hd']             = 'Sneeuwhoogtes meten en/of uploaden';
$LANGLOOKUP['conf-snow_show']           = 'Meet u manueel de sneeuwval en sneeuw-hoogte?';
$LANGLOOKUP['conf-liveYMD']             = 'Formaat van de datums in het geüploade realtime bestand';
$LANGLOOKUP['conf-history_hd']          = 'De gegevens die voor de grafieken moeten worden gebruikt';
$LANGLOOKUP['conf-charts_from']         = 'Het graph-script kan uw WU-station-data  gebruiken om  grafieken te genereren<br />Anders worden uw weergegevens gebruikt voor een kleine <b>subset</b> van de grafieken';
$LANGLOOKUP['conf-cron_hd']             = '<br /><i style="color: red">BELANGRIJK</i><br /><span style="font-weight: normal;"><br />Het Dashboard heeft veel gegevens nodig, die vaak niet allemaal beschikbaar zijn in uw weerprogramma.<br /><br />Nadat u al uw instellingen hebt ingevoerd en <b>opgeslagen</b>, <br />neem dan ruim de tijd om het document van 1 pagina over "Cron-jobs" op de pwsdashboard.com-website te lezen .<br /><br />Het gebruik van een cron-job geeft veel betere responstijden,<br />ververst verouderde informatie en zorgt ervoor dat ontbrekende hoog-laag gegevens worden berekend.<br />De cron vergemakkelijkt ook het optionele uploaden naar andere netwerken en dat met meer gedetailleerde historische gegevens.</span><br /><br />';
$LANGLOOKUP['conf-hardware_hd']         = 'Wat voor weer-station gebruikt u?';
$LANGLOOKUP['conf-hardware']            = 'Beschrijf uw weerstation';
$LANGLOOKUP['conf-manufacturer']        = 'Welk merk';
$LANGLOOKUP['conf-davis']               = 'Heeft u een Davis weerstation?';
$LANGLOOKUP['conf-descriptions_hd']     = 'Station/eigenaar details, houd de beschrijvingen kort';
$LANGLOOKUP['conf-stationName']         = 'De naam die voor uw station moet worden gebruikt';
$LANGLOOKUP['conf-stationlocation']     = 'Een relatief korte naam voor het gebied/regio waarin uw weerstation zich bevindt.';
$LANGLOOKUP['conf-contact_hd']          = 'De contact-pop-up';
$LANGLOOKUP['conf-contact_show']        = 'Willen we deze pop-up gebruiken?';
$LANGLOOKUP['conf-email']               = 'Het e-mailadres voor de contact-pop-up';
$LANGLOOKUP['conf-twitterUser']         = 'Heeft u een twitteraccount?';
$LANGLOOKUP['conf-twitter']             = 'Uw @twitter-account';
$LANGLOOKUP['conf-facebookuser']        = 'Wilt u een link naar een Facebook-account tonen?';
$LANGLOOKUP['conf-facebook']            = 'Uw Facebook-accountnaam';
$LANGLOOKUP['conf-location_hd']         = 'Uw station locatiegegevens';
$LANGLOOKUP['conf-noDST']               = 'Verander alleen als u uw klokken echt nooit reset';
$LANGLOOKUP['conf-TZ']                  = 'Stel de tijdzone in volgens de PHP-standaarden. <a target="_blank" href="http://php.net/manual/en/timezones.php">Kijk hier</a>';
$LANGLOOKUP['conf-lat']                 = 'Breedtegraad (en volgende veldlengtegraad) worden ook gespecificeerd in uw weerprogramma.<br />Voorbeeld: 50.8500 is voor Leuven in België. <a href="https://www.google.com/maps/" target="blank">Kijk hier.</a> <br />Noorden van de evenaar heeft geen teken. Ten zuiden van de evenaar staat een - teken.';
$LANGLOOKUP['conf-lon']                 = 'Voor lengtegraden <b>links</b> van Greenwich is een <b>-</b> teken nodig.<br />Dit is het <b style="color: red;">tegenovergestelde</b> zoals gebruikt in WeerDisplay!';
$LANGLOOKUP['conf-icao1']               = 'Voer uw dichtstbijzijnde luchthaven-code in (<b>XXXX</b>) die u hier <a target="_blank" href="https://www.travelmath.com/nearest-airport/"><b>hier kunt vinden </b></a><br />Voorbeeld: Voor Amsterdam-NL is dit Schiphol airport, de METAR code is <b>EHAM</b><br />Voorbeeld: Voor Amsterdam-NY is het Albany airport, de METAR-code is <b>KALB</b>';
$LANGLOOKUP['conf-metar']               = 'Pop-venster Dichtbij METAR weergeven';
$LANGLOOKUP['conf-metar_yes']           = 'We zullen dit gebruiken om de huidige voorwaarden weer te geven';
$LANGLOOKUP['conf-airport1']            = 'Korte beschrijvende naam van de luchthaven';
$LANGLOOKUP['conf-airport1dist']        = 'Afstand tussen uw station en luchthaven';
$LANGLOOKUP['conf-weatheralarm_hd']     = 'Welke weeralarm-service is beschikbaar?';
$LANGLOOKUP['conf-weatheralarm']        = 'Wilt u een weeralarm-service gebruiken?';
$LANGLOOKUP['conf-alarm_area']          = '<b>Europa:</b>korte code voor uw regio, b.v. BE004 <a href="http://pwsdashboard.be/pwsWD/list_warn_codes.php" target="blank">zoek hier</a><br /><b>VK MetOffice:</b><a href="https://www.metoffice.gov.uk/weather/guides/rss" target="blank">kijk hier</a>, klik op regio, laatste 2 letters van URL <br /><b>Canada:</b> <a href="http://dd.weather.gc.ca/citypage_weather/docs/site_list_en.csv" target="blank ">Download deze lijst.</a> Gebruik een editor om uw gebied te vinden.<br />Voorbeeld: s0000047 is de code voor Calgary';
$LANGLOOKUP['conf-province']            = 'Alleen Canada: de tweeletterige code voor uw provincie';
$LANGLOOKUP['conf-region']              = 'Regio gebruikt voor eenheidsconversie van WU-gegevens';
$LANGLOOKUP['conf-units_used_hd']       = 'Type eenheden (voorbeeld: C of F) dat moet worden gebruikt';
$LANGLOOKUP['conf-unit']                = 'Kies de instelling voor algemene eenheden';
$LANGLOOKUP['conf-dec_tmp']             = 'Selecteer het aantal decimalen dat u voor deze weer-waarden wilt gebruiken';
$LANGLOOKUP['conf-dec_wnd']             = '';
$LANGLOOKUP['conf-dec_rain']            = '';
$LANGLOOKUP['conf-dec_baro']            = '';
$LANGLOOKUP['conf-rainrate']            = 'Regenintensiteit per uur of minuut';
$LANGLOOKUP['conf-pressureunit']        = 'Unit voor luchtdruk / barometer';
$LANGLOOKUP['conf-distanceunit']        = 'Afstand';
$LANGLOOKUP['conf-cloudbase']           = 'Hoogte wolkenbasis';
$LANGLOOKUP['conf-aqhi_type']           = 'AQI-standaard te gebruiken als luchtkwaliteit-index';
$LANGLOOKUP['conf-date_time_hd']        = 'Selecteer de te gebruiken datum- en tijdnotaties';
$LANGLOOKUP['conf-dateFormat']          = 'Datumnotatie';
$LANGLOOKUP['conf-clockformat']         = 'Gebruik 24- of 12-uurs klok';
$LANGLOOKUP['conf-timeFormat']          = 'Tijd formaat';
$LANGLOOKUP['conf-timeFormatShort']     = 'Korte tijdnotatie gebruikt voor zon- en maanopkomst/ondergang';
$LANGLOOKUP['conf-menu_hd']             = 'Menu opties';
$LANGLOOKUP['conf-themes']              = 'Toon thema-selectie in Menu';
$LANGLOOKUP['conf-extralinks']          = 'Toon Extra links in Menu (standaard worden ze niet getoond)';
$LANGLOOKUP['conf-theme1']              = 'Standaard themakleur';
$LANGLOOKUP['conf-extra_devices_hd']    = 'We hebben de informatie nodig voor uw optionele apparaten';
$LANGLOOKUP['conf-purpleair_aq_hd']     = 'Purpleair Luchtkwaliteit-sensor';
$LANGLOOKUP['conf-purpleairhardware']   = 'Bent u in het bezit van een Purpleair-sensor?';
$LANGLOOKUP['conf-purpleairID']         = 'Als we er een hebben, wat is dan de sensor-ID?';
$LANGLOOKUP['conf-purpleairAPI']        = 'Purple Air sensor geheime sleutel';
$LANGLOOKUP['conf-luftdaten_aq_hd']     = 'Luftdaten Luchtkwaliteit-sensor, kan op twee verschillende manieren worden gebruikt';
$LANGLOOKUP['conf-luftdatenhardware']   = 'Heeft u een Luftdaten sensor?';
$LANGLOOKUP['conf-luftdatenID']         = 'De sensor-ID om de gegevens van de luftdaten <b>website</b> te krijgen?';
$LANGLOOKUP['conf-luftdatenSensor']     = 'Het sensor-nummer wanneer uw gegevens worden <b>geüpload naar deze website</b>';
$LANGLOOKUP['conf-davis_aq_hd']         = 'Davis AQ sensor cloud opslag';
$LANGLOOKUP['conf-davis_aq_sensor']     = 'Bent u in het bezit van een Davis AQ-sensor?';
$LANGLOOKUP['conf-dwl_AQ']              = 'Als we er een hebben, wat is dan de stations-ID? <br />U moet later ook de WeatherLink Cloud versie 2 API instellen';
$LANGLOOKUP['conf-official_aq_hd']      = 'Nabijgelegen officieel AQ-station';
$LANGLOOKUP['conf-gov_aqi']             = 'Wilt u een officieel AQ station in de buurt laten zien?';
$LANGLOOKUP['conf-waqitoken']           = 'Daar is een api-token voor nodig, gratis via <a href="https://aqicn.org/data-platform/token/#/" target="blank">deze site!</a>';
$LANGLOOKUP['conf-boltek_lightnng_hd']  = 'Bliksemgegevens met Nexstorm';
$LANGLOOKUP['conf-boltek']              = 'Heeft u een Nexstorm toestel (Astrogenic Systems)';
$LANGLOOKUP['conf-boltekfile']          = 'Stel het pad in naar uw NSRealtime.txt';
$LANGLOOKUP['conf-weatherflow_hd']      = 'WeatherFlow- of Tempest-apparaat: algemene weergegevens - bliksem - UV-Solar';
$LANGLOOKUP['conf-weatherflowoption']   = 'Heeft u een Weatherflow-station (AIR en SKY)';
$LANGLOOKUP['conf-weatherflowID']       = 'Weather-Flow STATION ID';
$LANGLOOKUP['conf-eco-extra_hd']        = 'Gebruik Ecowitt extra-sensoren met een ander weerprogramma';
$LANGLOOKUP['conf-ecowittoption']       = 'Gebruikt u de Ecowitt "Custom-upload"?';
$LANGLOOKUP['conf-ecowittfile']         = 'Locatie van het geüploade bestand';
$LANGLOOKUP['conf-ecowittAQ']           = 'Wilt u één of meerdere AQ sensoren gebruiken';
$LANGLOOKUP['conf-ecowittlightning']    = 'Wilt u een bliksemsensor gebruiken';
$LANGLOOKUP['conf-webcam_hd']           = 'Weercamera\'s';
$LANGLOOKUP['conf-mywebcam']            = 'Heeft u een webcam die u wilt laten zien?';
$LANGLOOKUP['conf-mywebcamimg']         = 'Geef de link naar uw webcam afbeelding op';
$LANGLOOKUP['conf-uv_solar_hd']         = 'Bronnen voor UV-zonnegegevens';
$LANGLOOKUP['conf-uvsolarsensors']      = 'Is er een UV- en zonnesensor beschikbaar?';
$LANGLOOKUP['conf-blocks_hd']           = 'Selecteer nu alle blokken die u wilt laten zien';
$LANGLOOKUP['conf-top_small_hd']        = 'Tot 5 kleine blokjes voor de bovenste rij';
$LANGLOOKUP['conf-position1']           = 'Positie bovenste rij - tweede kleine blok';
$LANGLOOKUP['conf-position2']           = 'Positie bovenste rij - derde kleine blok';
$LANGLOOKUP['conf-position3']           = 'Positie bovenste rij - vierde klein blok';
$LANGLOOKUP['conf-position4']           = 'Positie bovenste rij - laatste blokje, als het past';
$LANGLOOKUP['conf-positionlast']        = 'Links bovenaan vast';
$LANGLOOKUP['conf-extra3wused_hd']      = 'Normaal 3 blokken/rij. Wilt u 1 extra blok toevoegen om 4 blokken per rij te krijgen?';
$LANGLOOKUP['conf-cols_extra']          = 'Selecteer';
$LANGLOOKUP['conf-top_row_hd']          = 'Eerste rij blokken is altijd aanwezig, selecteer 3 of 4 blokken';
$LANGLOOKUP['conf-position11']          = 'Standaard temperatuurblok';
$LANGLOOKUP['conf-position12']          = 'Standaard prognose blok';
$LANGLOOKUP['conf-position13']          = 'Standaard "Sky" blok met huidige omstandigheden / 1 uur voorspelling';
$LANGLOOKUP['conf-position14']          = 'Kies optioneel 4e blok of beslis later';
$LANGLOOKUP['conf-middle_row_hd']       = 'Tweede rij blokken is altijd aanwezig, selecteer 3 of 4 blokken';
$LANGLOOKUP['conf-position21']          = 'Standaard wind - windvlaag blok';
$LANGLOOKUP['conf-position22']          = 'Standaard barometer-blok';
$LANGLOOKUP['conf-position23']          = 'Standaard Sun-informatieblok';
$LANGLOOKUP['conf-position24']          = 'Kies optioneel 4e blok of beslis later';
$LANGLOOKUP['conf-bottom_row_hd']       = 'Derde rij blokken is altijd aanwezig, selecteer 3 of 4 blokken';
$LANGLOOKUP['conf-position31']          = 'Standaard regenblok';
$LANGLOOKUP['conf-position32']          = 'Selecteer het te gebruiken script (meestal UV)';
$LANGLOOKUP['conf-position33']          = 'Selecteer te gebruiken script of geen';
$LANGLOOKUP['conf-position34']          = 'Kies optioneel 4e blok of beslis later';
$LANGLOOKUP['conf-extrarows_hd']        = 'Normaal 3 rijen met blokken. Wit u 1 of 2 extra rijen toevoegen??';
$LANGLOOKUP['conf-rows_extra']          = 'Selecteer';
$LANGLOOKUP['conf-extra_row1_hd']       = 'Optionele vierde rij met 3 of 4 blokken';
$LANGLOOKUP['conf-extra_row2_hd']       = 'Optionele vijfde rij met 3 of 4 blokken';
$LANGLOOKUP['conf-position43']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position42']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position41']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position44']          = 'Indien ingesteld op 4 blokken breed, selecteer blok of beslis later';
$LANGLOOKUP['conf-position53']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position52']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position51']          = 'Kies nu een blok of beslis later';
$LANGLOOKUP['conf-position54']          = 'Indien ingesteld op 4 blokken breed, selecteer een blok of beslis later';
$LANGLOOKUP['conf-suppliers_hd']        = 'De meeste databronnen hebben een API of extra informatie nodig';
$LANGLOOKUP['conf-suppliers_AERIS']     = 'Aeris Weather: voorspelling alleen beschikbaar als u oplaadt naar pwsweather.com';
$LANGLOOKUP['conf-aeris_access_id']     = 'Aeris API-toegangs-ID';
$LANGLOOKUP['conf-aeris_secret_key']    = 'Aeris API geheime sleutel';
$LANGLOOKUP['conf-suppliers_WU']        = 'WeatherUnderground: weergegevens - voorspelling - historische data';
$LANGLOOKUP['conf-wu_apikey']           = 'Uw 2019 WU API-sleutel zoals gegenereerd op uw WU-dashboard';
$LANGLOOKUP['conf-wuID']                = 'WeatherUnderground station-ID voor historische kaarten';
$LANGLOOKUP['conf-wu_start']            = 'Eerste dag van het uploaden van <b> juiste </b>gegevens naar WU<br />Formaat is JJJJ-MM-DD voorbeeld 2018-11-24';
$LANGLOOKUP['conf-suppliers_DS']        = 'Darksky en alternatieven na 31 maart 2023';
$LANGLOOKUP['conf-dark_alt_vrs']        = 'Selecteer welke alternatieve DS data leverancier u gebruikt';
$LANGLOOKUP['conf-dark_apikey']         = 'API-sleutel voor voorspelling en huidige omstandigheden.';
$LANGLOOKUP['conf-language']            = 'Prognose taal';
$LANGLOOKUP['conf-darkskyunit']         = 'API-EENHEDEN gebruikt';
$LANGLOOKUP['conf-suppliers_METAR']     = 'METAR-gegevens van uw lokale luchthaven, gebruikt voor de huidige "hemel"omstandigheden';
$LANGLOOKUP['conf-metarapikey']         = 'Voor de CheckWX METAR API KEY moet u zich <a href="https://www.checkwxapi.com/" target="blank">hier</a> aanmelden';
$LANGLOOKUP['conf-suppliers_AW']        = 'AmbientWeather: download uw AmbientWeather.net gegevens';
$LANGLOOKUP['conf-aw_key']              = 'Ambient-API-sleutel om uw stationsgegevens te lezen';
$LANGLOOKUP['conf-aw_did']              = 'Apparaat-ID voor uw specifieke apparaat op AmbientWeather.net';
$LANGLOOKUP['conf-suppliers_DWL']       = 'WeatherLink Cloud versie 1 API - b.v. IP-logger';
$LANGLOOKUP['conf-dwl_api']             = 'API Token v1: zoals gegenereerd op uw dashboard';
$LANGLOOKUP['conf-dwl_did']             = 'Apparaat-ID zoals op sticker van IP-logger';
$LANGLOOKUP['conf-dwl_pass']            = 'Wachtwoord dat u gebruikt om toegang te krijgen tot weatherlink.com';
$LANGLOOKUP['conf-suppliers_DWL2']      = 'WeatherLink Cloud versie 2 API - WLL en Airlink';
$LANGLOOKUP['conf-dwl_api2']            = 'API Key v2: zoals gegenereerd op uw weatherlink.com dashboard';
$LANGLOOKUP['conf-dwl_secret']          = 'API-geheim: zoals gegenereerd op uw weatherlink.com dashboard';
$LANGLOOKUP['conf-dwl_station']         = 'Station-ID: u kunt het vinden met dit testprogramma <a href="https://pwsdashboard.com/wll/" target="_blank">hier</a>';
$LANGLOOKUP['conf-suppliers_YRNO']      = 'Bij gebruik van yr.no prognose';
$LANGLOOKUP['conf-yrno_area']           = 'Momenteel niet gebruikt';
$LANGLOOKUP['conf-other_hd']            = 'Andere instellingen';
$LANGLOOKUP['conf-show_indoor']         = 'Binnentemperaturen weergeven?';
$LANGLOOKUP['conf-body_image']          = 'Optioneel: Afbeelding om de achtergrond achter het dashboard te vullen.';
$LANGLOOKUP['conf-check_cron']          = 'Toon waarschuwingsteken voor verouderde geschiedenis e.a.';
$LANGLOOKUP['conf-KISS']                = 'Toon minimalistische wijzerplaten voor temp/baro e.a.';
$LANGLOOKUP['conf-personalmessage']     = 'Optionele tekst in de footer?';
$LANGLOOKUP['conf-use_round']           = 'Verander sommige items, zoals de temperatuur, van vierkant naar rond';
$LANGLOOKUP['conf-txt_border']          = 'Verwijder de dunne of gekleurde randen rond de kleine tekstgedeelten';
$LANGLOOKUP['conf-close_popup']         = 'Standaard wordt een sluitknop weergegeven in de linkerbovenhoek van de pop-ups';
$LANGLOOKUP['Do not use this']          = 'Gebruik dit niet';
$LANGLOOKUP['Do not allow this']        = 'Sta dit niet toe';
$LANGLOOKUP['Yes, we do.']              = 'Ja dat doen we.';
$LANGLOOKUP['Location']                 = 'Locatie';
$LANGLOOKUP['Data']                     = 'Gegevens';
$LANGLOOKUP['Units']                    = 'Eenheden';
$LANGLOOKUP['Devices']                  = 'Apparaten';
$LANGLOOKUP['Tiles']                    = 'Blokken';
$LANGLOOKUP['Other']                    = 'Overige';
$LANGLOOKUP['Save your settings']       = 'Bewaar uw instellingen';
$LANGLOOKUP['Welcome by EasyweatherSetup']  = 'Hier kunt u al uw instellingen aanpassen';
$LANGLOOKUP['The "settings" to adapt the dashboard to your situation are grouped into 8 pages.']  = 'Er zijn 8 groepen instellingen die bereikbaar zijn via een tab-blad';
$LANGLOOKUP['Click on a grey button above to go the another set of questions.']  = 'Klik op een grijze knop om naar een andere groep instellingen te gaan';
$LANGLOOKUP['When you are finished, or if want to stop fo a while, save your answers by clicking at the green button at the top right.']  = 'Als u klaar bent, of even wilt stoppen, druk dan op de groen knop rechtsboven';
$LANGLOOKUP['If you finished all your settings, you should regularly check for updated scripts']  = 'Als al uw instellingen goed werken, contoleer dan regelmatig of er "updates" beschikbaar zijn.';
$LANGLOOKUP['here']                     = 'doe dat hier';
$LANGLOOKUP['All settings are OK, do not show that menu entry']  = 'Alle instellingen zijn OK, geen menu keuze meer nodig.';
$LANGLOOKUP['Year  Month  Day']         = 'Jaar Maand Dag';
$LANGLOOKUP['Day Month Year']           = 'Dag Maand Jaar';
$LANGLOOKUP['Month Day Year']           = 'Maand Dag Jaar';
$LANGLOOKUP['Yes, we have extra sensors and upload the data.']  = 'Ja, we hebben extra sensoren en laden de gegevens ervan op';
$LANGLOOKUP['We do not measure snow heights.']  = 'Geen sneeuw-hoogte metingen in gebruik';
$LANGLOOKUP['We use the dashboard script to enter the snow heights']  = 'We gebruiken het dashboard script om de sneeuwhoogtes in te geven';
$LANGLOOKUP['We enter and upload the values with our weather-program.']  = 'De sneeuwhoogtes komen via ons weer-programma';
$LANGLOOKUP['Officially they use Daylight Saving, but we personally do not want that.']  = 'Officieel gebruiken ze zomertijd maar ik doe er niet aan mee';
$LANGLOOKUP['I do not need this, use always official time zone standards']  = 'Ik volg de officiële zomer/wintertijd';
$LANGLOOKUP['Default Current weather / Sky conditions using METAR (needs API key)']  = 'Standaard wordt de METAR (vliegveld) hiervoor gebruikt (API-key nodig)';
$LANGLOOKUP['Current weather / Sky conditions by Aeris (needs API key)']  = 'Wij gebruiken onze AERIS - API key voor huidige weer-condities';
$LANGLOOKUP['Current weather / Sky conditions from your clientraw file']  = 'Onze clientraw van WeatherDisplay levert de huidige weer-condities';
$LANGLOOKUP['Current weather / Sky conditions  from Darksky (needs API key)']  = 'Wij gebruiken onze Darksky/VP/PW - API key voor huidige weer-condities';
$LANGLOOKUP['Current weather / Sky conditions from Environment Canada (Canada only)']  = 'Ons station ligt in Canada en gebruikt EC hiervoor';
$LANGLOOKUP['Default forecast using free Yr.no  data']  = 'Standaard weersverwachting met YR.no data';
$LANGLOOKUP['Forecast using Aeris data (needs API key)']  = 'Weersverwachting met AERIS data met onze API-key';
$LANGLOOKUP['Forecast using DarkSky data (needs API key)']  = 'Weersverwachting met DarkSky data met onze API-key';
$LANGLOOKUP['Forecast using free Environment Canada  data (Canada only)']  = 'Weersverwachting met EC data (alleen voor Canada)';
$LANGLOOKUP['Forecast using WeatherUnderground data (needs API key)']  = 'Weersverwachting met WeatherUnderground data met onze API-key';
$LANGLOOKUP['Forecast using WXSIM data from extra PC-program data']  = 'Weersverwachting via ons PC programma WXSIM';
$LANGLOOKUP['Europe uses meteoalarm.org']  = 'Gebruik de Europese meteoalarm.org data';
$LANGLOOKUP['Use our WeatherUnderground data for charts']  = 'Gebruik  WeatherUnderground';
$LANGLOOKUP['Save the daily data for the charts']  = 'Bewaar onze weergegevens iedere dag';
$LANGLOOKUP['Yes, we want to use that AQI.']  = 'Ja we willen die Luchtkwaliteit meting gebruiken';
$LANGLOOKUP['Do not have these']        = 'Gebruiken we niet';
$LANGLOOKUP['Our station has both UV and solar']  = 'Ons station heeft Solar en UV';
$LANGLOOKUP['Our station has solar. Use Darksky UV forcast as the UV sensor']  = 'Gebruik Darksky voor de UV metingen';
$LANGLOOKUP['We use a weatherflow device for UV and solar']  = 'Gebruik onze WeatherFlow apparaat voor solar en UV metingen';
$LANGLOOKUP['Always the advices are displayed here']  = 'Geen wijziging mogelijk, hier staan altijd adviezen e.d.';
$LANGLOOKUP['Yes, we need 1 extra block on the right of each row.']  = 'We willen 1 extra blok rechts bij  iedere rij';
$LANGLOOKUP['Yes, we need a fourth row of blocks.']  = 'Ja we willen een extra vierde rij';
$LANGLOOKUP['Yes, we need a fourth and fifth row of blocks.']  = 'Ja we willen een vierde em vijfde rij';
$LANGLOOKUP['Select later']             = 'Kiezen we later wel';
$LANGLOOKUP['si:Standard ISO']          = 'si: Standaard ISO met wind in m/s';
$LANGLOOKUP['ca: same as si, windSpeed km/h']  = 'ca: Gelijk aan "si" met wind in km/h';
$LANGLOOKUP['uk: same as si,windSpeed mph']  = 'uk: Gelijk aan "si" met wind in mph';
$LANGLOOKUP['us: Imperial units (NON METRIC)']  = 'us: Engelse eenheden (NIET METRISCH)';
$LANGLOOKUP['Allow visitor to switch colour themes.']  = 'Bezoeker mag de kleuren aanpassen';
$LANGLOOKUP['Not allowed']              = 'Geen aanpassing door bezoeker';
$LANGLOOKUP['light']                    = 'Lichte kleurstelling';
$LANGLOOKUP['dark']                     = 'Donkere kleurstelling';
$LANGLOOKUP['your own color set']       = 'Uw eigen aanpasbare kleurinstellingen';
$LANGLOOKUP['Yes make items round.']    = 'Gebruik ronde items';
$LANGLOOKUP['We like the square items']  = 'Gebruik hoekige items';
$LANGLOOKUP['Leave the small borders around text blocks, we like that.']  = 'Dunne lijntjes om de text-blokjes';
$LANGLOOKUP['We like "Less is more", remove those borders']  = 'Laat de dunne lijntjes weg';
$LANGLOOKUP['Yes, show the close button/text in every pop-up']  = 'Toon in iedere pop-up de "Sluit" knop';}
        }  // eo nl
        elseif ($setup_lang == 'de' && !isset ($LANGLOOKUP['conf-password_hd'])) {
        if (1 == 1) {
$LANGLOOKUP['conf-password_hd']         = 'Schützen Sie Ihre Einstellungen mit einem Passwort';
$LANGLOOKUP['conf-password']            = 'Verwenden Sie eine Zeichenfolge, an die Sie sich erinnern, mindestens 10 Zeichen, verwenden Sie keine "- oder \'-Zeichen';
$LANGLOOKUP['conf-show_settings']       = 'Einen Menüpunkt für dieses Einstellungsskript verwenden?';
$LANGLOOKUP['conf-language_hd']         = 'Wählen Sie die Standardsprache, die Sie anzeigen und verwenden möchten.';
$LANGLOOKUP['conf-defaultlanguage']     = 'Standardmäßig zu verwendende Sprache';
$LANGLOOKUP['conf-lang_select']         = 'Können Besucher die Sprache ändern?';
$LANGLOOKUP['conf-lang_select_yes']     = 'Ein Besucher kann die verwendete Sprache ändern';
$LANGLOOKUP['conf-future_hd']           = 'Zum späteren Nachschlagen, wenn neue Artikel benötigt werden';
$LANGLOOKUP['conf-solve_problem1']      = 'Lass es so wie es ist';
$LANGLOOKUP['conf-solve_problem2']      = 'Wird verwendet, wenn ein „noch unbekanntes Problem“ auftritt';
$LANGLOOKUP['conf-country_flag']        = 'Ihre Landesflagge';
$LANGLOOKUP['conf-livedata_hd']         = 'Wetterprogramm / Datendatei, die wir verwenden werden';
$LANGLOOKUP['conf-livedataFormat']      = 'Wie bekommen wir unsere Wetterdaten auf Ihre Website?<br />Ändern Sie nur, wenn Sie sicher sind, dass Ihre Datendatei über den richtigen Upload oder über eine API verfügbar ist';
$LANGLOOKUP['conf-livedata']            = 'Pfad zu der von Ihrem Wetterprogramm <b>geladenen</b> Datei.<br />Der richtige Pfad ist für Ihre Datenanzeige unerlässlich.<br />Beispiel "../clientraw.txt", wenn Ihre Datei im root.<br />Nicht mit einer API verwendet.';
$LANGLOOKUP['conf-skydata_hd']          = 'Cloud-Sky-Bedingungen';
$LANGLOOKUP['conf-sky_default']         = 'Wie erhalten wir die aktuellen „Sky“-Konditionen?';
$LANGLOOKUP['conf-fctdata_hd']          = 'Wettervorhersage';
$LANGLOOKUP['conf-fct_default']         = 'Welche Wettervorhersage möchten Sie verwenden?';
$LANGLOOKUP['conf-extra_sensors_hd']    = 'Separater Upload der zusätzlichen Sensoren Ihrer Wetterstation';
$LANGLOOKUP['conf-have_extra']          = 'Bei Verwendung zusätzlicher Sensoren kann eine zusätzliche Datendatei erforderlich sein';
$LANGLOOKUP['conf-extra_data']          = 'Pfad und Dateiname der hochgeladenen Datei (z. B. demodata/extra_sensors.txt )';
$LANGLOOKUP['conf-snow_hd']             = 'Schneehöhen messen und/oder hochladen';
$LANGLOOKUP['conf-snow_show']           = 'Messen Sie Schneefall und Schneehöhe manuell?';
$LANGLOOKUP['conf-liveYMD']             = 'Format der Daten in der hochgeladenen Echtzeitdatei';
$LANGLOOKUP['conf-history_hd']          = 'Die für die Diagramme zu verwendenden Daten';
$LANGLOOKUP['conf-charts_from']         = 'Das Grafikskript kann Ihre WU-Stationsdaten verwenden, um Grafiken zu erstellen<br />Andernfalls werden Ihre Wetterdaten für eine kleine <b>Teilmenge</b> der Grafiken verwendet';
$LANGLOOKUP['conf-cron_hd']             = '<br /><i style="color: red">WICHTIG</i><br /><span style="font-weight: normal;"><br />Das Dashboard benötigt sehr viele Daten, die oft Nicht alle sind in Ihrem Wetterprogramm verfügbar.<br /><br />Nachdem Sie alle Ihre Einstellungen eingegeben und <b>gespeichert</b> haben, <br />nehmen Sie sich ausreichend Zeit, um die 1-Seite zu lesen Dokument über "Cron-Jobs" auf der Website pwsdashboard.com.<br /><br />Die Verwendung eines Cron-Jobs führt zu viel besseren Reaktionszeiten,<br />aktualisiert veraltete Informationen und berechnet fehlende High-Low-Daten.<br /> />Der Cron erleichtert auch das optionale Hochladen in andere Netzwerke mit detaillierteren historischen Daten.</span><br /><br />';
$LANGLOOKUP['conf-hardware_hd']         = 'Welche Wetterstation verwendest du?';
$LANGLOOKUP['conf-hardware']            = 'Beschreiben Sie Ihre Wetterstation';
$LANGLOOKUP['conf-manufacturer']        = 'Welche Marke';
$LANGLOOKUP['conf-davis']               = 'Hast du eine Davis Wetterstation?';
$LANGLOOKUP['conf-descriptions_hd']     = 'Stations-/Besitzerdetails, Beschreibungen kurz halten';
$LANGLOOKUP['conf-stationName']         = 'Der für Ihre Station zu verwendende Name';
$LANGLOOKUP['conf-stationlocation']     = 'Eine relativ kurze Bezeichnung für das Gebiet/die Region, in der sich Ihre Wetterstation befindet.';
$LANGLOOKUP['conf-contact_hd']          = 'Das Kontakt-Popup';
$LANGLOOKUP['conf-contact_show']        = 'Wollen wir dieses Popup verwenden?';
$LANGLOOKUP['conf-email']               = 'Die E-Mail-Adresse für das Kontakt-Popup';
$LANGLOOKUP['conf-twitterUser']         = 'Haben Sie einen Twitter-Account?';
$LANGLOOKUP['conf-twitter']             = 'Ihr @twitter-Konto';
$LANGLOOKUP['conf-facebookuser']        = 'Möchten Sie einen Link zu einem Facebook-Konto anzeigen?';
$LANGLOOKUP['conf-facebook']            = 'Ihr Facebook-Kontoname';
$LANGLOOKUP['conf-location_hd']         = 'Ihre Stationsstandortdaten';
$LANGLOOKUP['conf-noDST']               = 'Ändere nur, wenn du deine Uhren wirklich nie umstellst';
$LANGLOOKUP['conf-TZ']                  = 'Stellen Sie die Zeitzone gemäß den PHP-Standards ein. <a target="_blank" href="http://php.net/manual/en/timezones.php">Schauen Sie hier</a>';
$LANGLOOKUP['conf-lat']                 = 'Der Breitengrad (und der Längengrad im nächsten Feld) werden auch in Ihrem Wetterprogramm angegeben.<br />Beispiel: 50,8500 steht für Leuven in Belgien. <a href="https://www.google.com/maps/" target="blank">Schauen Sie hier.</a> <br />Nördlich des Äquators gibt es kein Schild. Südlich des Äquators ist ein - Zeichen.';
$LANGLOOKUP['conf-lon']                 = 'Längengrade <b>links</b> von Greenwich erfordern ein <b>-</b> Zeichen.<br />Dies ist das <b style="color: red;">Gegenteil</b>, wie es in WeatherDisplay verwendet wird !';
$LANGLOOKUP['conf-icao1']               = 'Geben Sie den Code Ihres nächstgelegenen Flughafens (<b>XXXX</b>) ein, den Sie <a target="_blank" href="https://www.travelmath.com/nearest-airport/"><b>hier finden </b></a><br />Beispiel: Für Amsterdam-NL ist dies der Flughafen Schiphol, der METAR-Code ist <b>EHAM</b><br />Beispiel: Für Amsterdam-NY ist es der Flughafen Albany, der METAR-Code ist <b>KALB</b>';
$LANGLOOKUP['conf-metar']               = 'Pop Window Show in der Nähe von METAR';
$LANGLOOKUP['conf-metar_yes']           = 'Wir werden dies verwenden, um die aktuellen Bedingungen anzuzeigen';
$LANGLOOKUP['conf-airport1']            = 'Kurzer beschreibender Name des Flughafens';
$LANGLOOKUP['conf-airport1dist']        = 'Entfernung zwischen Ihrer Station und dem Flughafen';
$LANGLOOKUP['conf-weatheralarm_hd']     = 'Welcher Wetterwarndienst ist verfügbar?';
$LANGLOOKUP['conf-weatheralarm']        = 'Möchten Sie einen Wetterwarndienst nutzen?';
$LANGLOOKUP['conf-alarm_area']          = '<b>Europa:</b>Kurzcode für Ihre Region, z. BE004 <a href="http://pwsdashboard.be/pwsWD/list_warn_codes.php" target="blank">hier suchen</a><br /><b>UK MetOffice:</b><a href= "https://www.metoffice.gov.uk/weather/guides/rss" target="blank">sehen Sie hier</a>, klicken Sie auf die Region, die letzten 2 Buchstaben der URL <br /><b>Kanada: < /b> <a href="http://dd.weather.gc.ca/citypage_weather/docs/site_list_en.csv" target="blank ">Laden Sie diese Liste herunter.</a> Verwenden Sie einen Editor, um Ihre Gebietssuche zu bearbeiten .<br />Beispiel: s0000047 ist der Code für Calgary';
$LANGLOOKUP['conf-province']            = 'Nur Kanada: der aus zwei Buchstaben bestehende Code für Ihre Provinz';
$LANGLOOKUP['conf-region']              = 'Region, die für die Einheitenumrechnung von WU-Daten verwendet wird';
$LANGLOOKUP['conf-units_used_hd']       = 'Art der zu verwendenden Einheiten (Beispiel: C oder F).';
$LANGLOOKUP['conf-unit']                = 'Wählen Sie die allgemeine Einheiteneinstellung';
$LANGLOOKUP['conf-dec_tmp']             = 'Wählen Sie die Anzahl der Dezimalstellen, die Sie für diese Wetterwerte verwenden möchten';
$LANGLOOKUP['conf-dec_wnd']             = '';
$LANGLOOKUP['conf-dec_rain']            = '';
$LANGLOOKUP['conf-dec_baro']            = '';
$LANGLOOKUP['conf-rainrate']            = 'Regenintensität pro Stunde oder Minute';
$LANGLOOKUP['conf-pressureunit']        = 'Luftdruck-/Barometereinheit';
$LANGLOOKUP['conf-distanceunit']        = 'Distanz';
$LANGLOOKUP['conf-cloudbase']           = 'Höhe der Wolkenbasis';
$LANGLOOKUP['conf-aqhi_type']           = 'AQI-Standard zur Verwendung als Luftqualitätsindex';
$LANGLOOKUP['conf-date_time_hd']        = 'Wählen Sie die zu verwendenden Datums- und Zeitformate aus';
$LANGLOOKUP['conf-dateFormat']          = 'Datumsformat';
$LANGLOOKUP['conf-clockformat']         = 'Verwenden Sie die 24- oder 12-Stunden-Uhr';
$LANGLOOKUP['conf-timeFormat']          = 'Zeitformat';
$LANGLOOKUP['conf-timeFormatShort']     = 'Kurzzeitformat für Sonnenaufgang/Sonnenaufgang und Monduntergang';
$LANGLOOKUP['conf-menu_hd']             = 'Menüpunkte';
$LANGLOOKUP['conf-themes']              = 'Themenauswahl im Menü anzeigen';
$LANGLOOKUP['conf-extralinks']          = 'Zusätzliche Links im Menü anzeigen (sie werden standardmäßig nicht angezeigt)';
$LANGLOOKUP['conf-theme1']              = 'Standarddesignfarbe';
$LANGLOOKUP['conf-extra_devices_hd']    = 'Wir benötigen die Informationen für Ihre optionalen Geräte';
$LANGLOOKUP['conf-purpleair_aq_hd']     = 'Purpleair Luftqualitätssensor';
$LANGLOOKUP['conf-purpleairhardware']   = 'Besitzen Sie einen Purpleair-Sensor?';
$LANGLOOKUP['conf-purpleairID']         = 'Wenn wir eine haben, wie lautet die Sensor-ID?';
$LANGLOOKUP['conf-purpleairAPI']        = 'API-Schlüssel';
$LANGLOOKUP['conf-luftdaten_aq_hd']     = 'Luftdaten Luftqualitätssensor, kann auf zwei verschiedene Arten verwendet werden';
$LANGLOOKUP['conf-luftdatenhardware']   = 'Hast du einen Luftdatensensor?';
$LANGLOOKUP['conf-luftdatenID']         = 'Die Sensor-ID um die Daten von der luftdaten <b>Website</b> zu bekommen?';
$LANGLOOKUP['conf-luftdatenSensor']     = 'Die Sensornummer, wenn Ihre Daten <b>auf diese Website hochgeladen</b> werden';
$LANGLOOKUP['conf-davis_aq_hd']         = 'Cloud-Speicher für Davis AQ-Sensoren';
$LANGLOOKUP['conf-davis_aq_sensor']     = 'Besitzen Sie einen Davis AQ-Sensor?';
$LANGLOOKUP['conf-dwl_AQ']              = 'Wenn wir eine haben, wie lautet die Stations-ID? <br />Sie müssen später auch die WeatherLink Cloud Version 2 API einrichten';
$LANGLOOKUP['conf-official_aq_hd']      = 'Offizielle AQ-Station in der Nähe';
$LANGLOOKUP['conf-gov_aqi']             = 'Möchten Sie eine offizielle AQ-Station in Ihrer Nähe zeigen?';
$LANGLOOKUP['conf-waqitoken']           = 'Dies erfordert ein API-Token, kostenlos von <a href="https://aqicn.org/data-platform/token/#/" target="blank">dieser Seite!</a>';
$LANGLOOKUP['conf-boltek_lightnng_hd']  = 'Blitzdaten mit Nextstorm';
$LANGLOOKUP['conf-boltek']              = 'Haben Sie ein Nexstorm-Gerät (Astrogene Systems)';
$LANGLOOKUP['conf-boltekfile']          = 'Legen Sie den Pfad zu Ihrer NSRealtime.txt fest';
$LANGLOOKUP['conf-weatherflow_hd']      = 'WeatherFlow- oder Tempest-Gerät: Allgemeine Wetterdaten – Blitz – UV-Solar';
$LANGLOOKUP['conf-weatherflowoption']   = 'Haben Sie eine Weatherflow-Station (AIR und SKY)';
$LANGLOOKUP['conf-weatherflowID']       = 'Wetterfluss STATION ID';
$LANGLOOKUP['conf-eco-extra_hd']        = 'Verwenden Sie Ecowitt-Zusatzsensoren mit einem anderen Wetterprogramm';
$LANGLOOKUP['conf-ecowittoption']       = 'Verwenden Sie den Ecowitt „Benutzerdefinierten Upload“?';
$LANGLOOKUP['conf-ecowittfile']         = 'Speicherort der hochgeladenen Datei';
$LANGLOOKUP['conf-ecowittAQ']           = 'Möchten Sie einen oder mehrere AQ-Sensoren verwenden?';
$LANGLOOKUP['conf-ecowittlightning']    = 'Möchten Sie einen Blitzsensor verwenden?';
$LANGLOOKUP['conf-webcam_hd']           = 'Wetterkameras';
$LANGLOOKUP['conf-mywebcam']            = 'Haben Sie eine Webcam, die Sie zeigen möchten?';
$LANGLOOKUP['conf-mywebcamimg']         = 'Geben Sie den Link zu Ihrem Webcam-Bild an';
$LANGLOOKUP['conf-uv_solar_hd']         = 'Quellen für UV-Solardaten';
$LANGLOOKUP['conf-uvsolarsensors']      = 'Ist ein UV- und Sonnensensor vorhanden?';
$LANGLOOKUP['conf-blocks_hd']           = 'Wählen Sie nun alle Blöcke aus, die Sie anzeigen möchten';
$LANGLOOKUP['conf-top_small_hd']        = 'Bis zu 5 kleine Würfel für die obere Reihe';
$LANGLOOKUP['conf-position1']           = 'Position in der obersten Reihe – Zweiter kleiner Block';
$LANGLOOKUP['conf-position2']           = 'Position in der obersten Reihe – dritter kleiner Block';
$LANGLOOKUP['conf-position3']           = 'Position in der obersten Reihe – Vierter kleiner Block';
$LANGLOOKUP['conf-position4']           = 'Obere Reihenposition - letzter Block, wenn es passt';
$LANGLOOKUP['conf-positionlast']        = 'Oben links fixiert';
$LANGLOOKUP['conf-extra3wused_hd']      = 'Normalerweise 3 Blöcke/Reihe. Möchten Sie 1 zusätzlichen Block hinzufügen, um 4 Blöcke pro Reihe zu erhalten?';
$LANGLOOKUP['conf-cols_extra']          = 'Auswählen';
$LANGLOOKUP['conf-top_row_hd']          = 'Die erste Blockreihe ist immer vorhanden, wählen Sie 3 oder 4 Blöcke aus';
$LANGLOOKUP['conf-position11']          = 'Standard-Temperaturblock';
$LANGLOOKUP['conf-position12']          = 'Standard-Prognoseblock';
$LANGLOOKUP['conf-position13']          = 'Standardblock "Himmel" mit aktuellen Bedingungen / 1-Stunden-Vorhersage';
$LANGLOOKUP['conf-position14']          = 'Wählen Sie den optionalen 4. Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-middle_row_hd']       = 'Die zweite Blockreihe ist immer vorhanden, wählen Sie 3 oder 4 Blöcke aus';
$LANGLOOKUP['conf-position21']          = 'Standardwind - Böenblock';
$LANGLOOKUP['conf-position22']          = 'Standard-Barometerblock';
$LANGLOOKUP['conf-position23']          = 'Standard-Sonneninformationsblock';
$LANGLOOKUP['conf-position24']          = 'Wählen Sie den optionalen 4. Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-bottom_row_hd']       = 'Die dritte Blockreihe ist immer vorhanden, wählen Sie 3 oder 4 Blöcke aus';
$LANGLOOKUP['conf-position31']          = 'Standard-Regenblock';
$LANGLOOKUP['conf-position32']          = 'Wählen Sie das zu verwendende Skript (normalerweise UV)';
$LANGLOOKUP['conf-position33']          = 'Wählen Sie das zu verwendende Skript oder keins aus';
$LANGLOOKUP['conf-position34']          = 'Wählen Sie den optionalen 4. Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-extrarows_hd']        = 'Normalerweise 3 Blockreihen. Möchten Sie 1 oder 2 zusätzliche Reihen hinzufügen?';
$LANGLOOKUP['conf-rows_extra']          = 'Auswählen';
$LANGLOOKUP['conf-extra_row1_hd']       = 'Wahlweise vierte Reihe mit 3 oder 4 Blöcken';
$LANGLOOKUP['conf-extra_row2_hd']       = 'Wahlweise fünfte Reihe mit 3 oder 4 Blöcken';
$LANGLOOKUP['conf-position43']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position42']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position41']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position44']          = 'Wenn die Breite auf 4 Blöcke eingestellt ist, wählen Sie Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position53']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position52']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position51']          = 'Wählen Sie jetzt einen Block oder entscheiden Sie sich später';
$LANGLOOKUP['conf-position54']          = 'Wenn die Breite auf 4 Blöcke eingestellt ist, wählen Sie einen Block aus oder entscheiden Sie sich später';
$LANGLOOKUP['conf-suppliers_hd']        = 'Die meisten Datenquellen erfordern eine API oder zusätzliche Informationen';
$LANGLOOKUP['conf-suppliers_AERIS']     = 'Aeris Weather: Vorhersage nur verfügbar, wenn Sie auf pwsweather.com hochladen';
$LANGLOOKUP['conf-aeris_access_id']     = 'Aeris-API-Zugriffs-ID';
$LANGLOOKUP['conf-aeris_secret_key']    = 'Aeris-API-Geheimschlüssel';
$LANGLOOKUP['conf-suppliers_WU']        = 'WeatherUnderground: Wetterdaten - Vorhersage - historische Daten';
$LANGLOOKUP['conf-wu_apikey']           = 'Ihr 2019 WU-API-Schlüssel, wie er auf Ihrem WU-Dashboard generiert wurde';
$LANGLOOKUP['conf-wuID']                = 'WetterU-Bahn-Stations-ID für historische Karten';
$LANGLOOKUP['conf-wu_start']            = 'Erster Tag des Hochladens <b>korrekter</b>Daten in das WU<br />Format ist JJJJ-MM-TT, Beispiel: 2018-11-24';
$LANGLOOKUP['conf-suppliers_DS']        = 'Darksky - Darksky akzeptiert keine neuen Benutzer';
$LANGLOOKUP['conf-dark_alt_vrs']        = 'Wählen Sie aus, welchen alternativen DS-Datenanbieter Sie verwenden';
$LANGLOOKUP['conf-dark_apikey']         = 'API-Schlüssel für Prognose und aktuelle Bedingungen.';
$LANGLOOKUP['conf-language']            = 'Sprachprognose';
$LANGLOOKUP['conf-darkskyunit']         = 'API-EINHEITEN verwendet';
$LANGLOOKUP['conf-suppliers_METAR']     = 'METAR-Daten von Ihrem lokalen Flughafen, verwendet für aktuelle „Himmel“-Bedingungen';
$LANGLOOKUP['conf-metarapikey']         = 'Für den CheckWX METAR API KEY müssen Sie sich <a href="https://www.checkwxapi.com/" target="blank">hier</a> registrieren';
$LANGLOOKUP['conf-suppliers_AW']        = 'AmbientWeather: Laden Sie Ihre AmbientWeather.net-Daten herunter';
$LANGLOOKUP['conf-aw_key']              = 'Umgebungs-API-Schlüssel zum Auslesen Ihrer Stationsdaten';
$LANGLOOKUP['conf-aw_did']              = 'Geräte-ID für Ihr spezifisches Gerät bei AmbientWeather.net';
$LANGLOOKUP['conf-suppliers_DWL']       = 'WeatherLink Cloud Version 1 API - z.B. IP-Logger';
$LANGLOOKUP['conf-dwl_api']             = 'API-Token v1: wie auf Ihrem Dashboard generiert';
$LANGLOOKUP['conf-dwl_did']             = 'Geräte-ID wie auf dem Aufkleber des IP-Loggers';
$LANGLOOKUP['conf-dwl_pass']            = 'Passwort, das Sie für den Zugriff auf weatherlink.com verwenden';
$LANGLOOKUP['conf-suppliers_DWL2']      = 'WeatherLink Cloud Version 2 API - WLL und Airlink';
$LANGLOOKUP['conf-dwl_api2']            = 'API-Schlüssel v2: wie auf Ihrem Weatherlink.com-Dashboard generiert';
$LANGLOOKUP['conf-dwl_secret']          = 'API-Geheimnis: wie auf Ihrem Weatherlink.com-Dashboard generiert';
$LANGLOOKUP['conf-dwl_station']         = 'Stations-ID: Sie finden sie mit diesem Testtool <a href="https://pwsdashboard.com/wll/" target="_blank">hier</a>';
$LANGLOOKUP['conf-suppliers_YRNO']      = 'Bei Verwendung der yr.no-Prognose';
$LANGLOOKUP['conf-yrno_area']           = 'Derzeit nicht verwendet';
$LANGLOOKUP['conf-other_hd']            = 'Andere Einstellungen';
$LANGLOOKUP['conf-show_indoor']         = 'Innentemperaturen anzeigen?';
$LANGLOOKUP['conf-body_image']          = 'Optional: Bild zum Füllen des Hintergrunds hinter dem Dashboard.';
$LANGLOOKUP['conf-check_cron']          = 'Warnzeichen für veraltete Historie und andere anzeigen';
$LANGLOOKUP['conf-KISS']                = 'Zeigen Sie minimalistische Zifferblätter für Temp/Baro und andere.';
$LANGLOOKUP['conf-personalmessage']     = 'Optionaler Text in der Fußzeile?';
$LANGLOOKUP['conf-use_round']           = 'Ändern Sie einige Elemente, z. B. die Temperatur, von quadratisch in rund';
$LANGLOOKUP['conf-txt_border']          = 'Entfernen Sie die dünnen oder farbigen Ränder um die kleinen Textbereiche';
$LANGLOOKUP['conf-close_popup']         = 'Standardmäßig wird in der oberen linken Ecke der Popups eine Schließen-Schaltfläche angezeigt';
$LANGLOOKUP['Do not use this']          = 'Verwenden Sie dies nicht';
$LANGLOOKUP['Do not allow this']        = 'erlaube das nicht';
$LANGLOOKUP['Yes, we do.']              = 'Ja, machen wir.';
$LANGLOOKUP['Location']                 = 'Standort';
$LANGLOOKUP['Data']                     = 'Fakten';
$LANGLOOKUP['Units']                    = 'Einheiten';
$LANGLOOKUP['Devices']                  = 'Geräte';
$LANGLOOKUP['Tiles']                    = 'Blöcke';
$LANGLOOKUP['Other']                    = 'Andere';
$LANGLOOKUP['Save your settings']       = 'Speichern Sie Ihre Einstellungen';
$LANGLOOKUP['Welcome by EasyweatherSetup']  = 'Hier können Sie alle Ihre Einstellungen anpassen';
$LANGLOOKUP['The "settings" to adapt the dashboard to your situation are grouped into 8 pages.']  = 'Es gibt 8 Gruppen von Einstellungen, auf die über eine Registerkarte zugegriffen werden kann';
$LANGLOOKUP['Click on a grey button above to go the another set of questions.']  = 'Klicken Sie auf eine graue Schaltfläche, um zu einer anderen Gruppe von Einstellungen zu wechseln';
$LANGLOOKUP['When you are finished, or if want to stop fo a while, save your answers by clicking at the green button at the top right.']  = 'Wenn Sie fertig sind oder eine Weile anhalten möchten, drücken Sie die grüne Taste oben rechts';
$LANGLOOKUP['If you finished all your settings, you should regularly check for updated scripts']  = 'Wenn alle Ihre Einstellungen ordnungsgemäß funktionieren, prüfen Sie regelmäßig, ob „Updates“ verfügbar sind.';
$LANGLOOKUP['here']                     = 'mach das hier';
$LANGLOOKUP['All settings are OK, do not show that menu entry']  = 'Alle Einstellungen sind OK, keine Menüauswahl mehr nötig.';
$LANGLOOKUP['Year  Month  Day']         = 'Jahr Monat Tag';
$LANGLOOKUP['Day Month Year']           = 'Tag Monat Jahr';
$LANGLOOKUP['Month Day Year']           = 'Monat Tag Jahr';
$LANGLOOKUP['Yes, we have extra sensors and upload the data.']  = 'Ja, wir haben zusätzliche Sensoren und laden ihre Daten hoch';
$LANGLOOKUP['We do not measure snow heights.']  = 'Es werden keine Schneehöhenmessungen verwendet';
$LANGLOOKUP['We use the dashboard script to enter the snow heights']  = 'Wir verwenden das Dashboard-Skript, um die Schneehöhen einzugeben';
$LANGLOOKUP['We enter and upload the values with our weather-program.']  = 'Die Schneehöhen kommen durch unser Wetterprogramm';
$LANGLOOKUP['Officially they use Daylight Saving, but we personally do not want that.']  = 'Offiziell verwenden sie die Sommerzeit, aber ich mache nicht mit';
$LANGLOOKUP['I do not need this, use always official time zone standards']  = 'Ich orientiere mich an der offiziellen Sommer-/Winterzeit';
$LANGLOOKUP['Default Current weather / Sky conditions using METAR (needs API key)']  = 'Standardmäßig wird dafür der METAR (Flughafen) verwendet (API-Schlüssel erforderlich)';
$LANGLOOKUP['Current weather / Sky conditions by Aeris (needs API key)']  = 'Wir verwenden unseren AERIS - API-Schlüssel für aktuelle Wetterbedingungen';
$LANGLOOKUP['Current weather / Sky conditions from your clientraw file']  = 'Unser WeatherDisplay clientraw liefert aktuelle Wetterbedingungen';
$LANGLOOKUP['Current weather / Sky conditions  from Darksky (needs API key)']  = 'Wir verwenden unseren Darksky/VP/PW - API-Schlüssel für aktuelle Wetterbedingungen';
$LANGLOOKUP['Current weather / Sky conditions from Environment Canada (Canada only)']  = 'Unsere Station ist in Kanada und nutzt dafür EC';
$LANGLOOKUP['Default forecast using free Yr.no  data']  = 'Standard-Wettervorhersage mit YR.no-Daten';
$LANGLOOKUP['Forecast using Aeris data (needs API key)']  = 'Wettervorhersage mit AERIS-Daten mit unserem API-Schlüssel';
$LANGLOOKUP['Forecast using DarkSky data (needs API key)']  = 'Wettervorhersage mit DarkSky-Daten mit unserem API-Schlüssel';
$LANGLOOKUP['Forecast using free Environment Canada  data (Canada only)']  = 'Wettervorhersage mit EC-Daten (nur für Kanada)';
$LANGLOOKUP['Forecast using WeatherUnderground data (needs API key)']  = 'Wettervorhersage mit WeatherUnderground-Daten mit unserem API-Schlüssel';
$LANGLOOKUP['Forecast using WXSIM data from extra PC-program data']  = 'Wettervorhersage über unser PC-Programm WXSIM';
$LANGLOOKUP['Europe uses meteoalarm.org']  = 'Verwenden Sie die europäischen Daten von meteoalarm.org';
$LANGLOOKUP['Use our WeatherUnderground data for charts']  = 'Verwenden Sie WeatherUnderground';
$LANGLOOKUP['Save the daily data for the charts']  = 'Speichern Sie täglich unsere Wetterdaten';
$LANGLOOKUP['Yes, we want to use that AQI.']  = 'Ja, wir möchten diese Luftqualitätsmessung verwenden';
$LANGLOOKUP['Do not have these']        = 'wir verwenden nicht';
$LANGLOOKUP['Our station has both UV and solar']  = 'Unsere Station verfügt über Solar und UV';
$LANGLOOKUP['Our station has solar. Use Darksky UV forcast as the UV sensor']  = 'Verwenden Sie Darksky für die UV-Messungen';
$LANGLOOKUP['We use a weatherflow device for UV and solar']  = 'Nutzen Sie unser WeatherFlow-Gerät für Solar- und UV-Messungen';
$LANGLOOKUP['Always the advices are displayed here']  = 'Keine Änderungen möglich, Beratung etc. ist hier immer inklusive.';
$LANGLOOKUP['Yes, we need 1 extra block on the right of each row.']  = 'Wir wollen mit jeder Reihe 1 zusätzlichen Block auf der rechten Seite';
$LANGLOOKUP['Yes, we need a fourth row of blocks.']  = 'Ja, wir wollen eine zusätzliche vierte Reihe';
$LANGLOOKUP['Yes, we need a fourth and fifth row of blocks.']  = 'Ja, wir wollen eine vierte und fünfte Reihe';
$LANGLOOKUP['Select later']             = 'Wir wählen später';
$LANGLOOKUP['si:Standard ISO']          = 'si: Standard-ISO mit Wind in m/s';
$LANGLOOKUP['ca: same as si, windSpeed km/h']  = 'ca: Entspricht "si" mit Wind in km/h';
$LANGLOOKUP['uk: same as si,windSpeed mph']  = 'uk: Äquivalent zu "si" mit Wind in mph';
$LANGLOOKUP['us: Imperial units (NON METRIC)']  = 'us: imperiale Einheiten (NICHT METRISCH)';
$LANGLOOKUP['Allow visitor to switch colour themes.']  = 'Besucher können die Farben anpassen';
$LANGLOOKUP['Not allowed']              = 'Keine Anpassung durch den Besucher';
$LANGLOOKUP['light']                    = 'Helles Farbschema';
$LANGLOOKUP['dark']                     = 'dunkles Farbschema';
$LANGLOOKUP['your own color set']       = 'Ihre eigenen anpassbaren Farbeinstellungen';
$LANGLOOKUP['Yes make items round.']    = 'Verwenden Sie runde Gegenstände';
$LANGLOOKUP['We like the square items']  = 'Verwenden Sie eckige Gegenstände';
$LANGLOOKUP['Leave the small borders around text blocks, we like that.']  = 'Dünne Linien um die Textblöcke';
$LANGLOOKUP['We like "Less is more", remove those borders']  = 'Lassen Sie die dünnen Linien weg';
$LANGLOOKUP['Yes, show the close button/text in every pop-up']  = 'Zeigen Sie die Schaltfläche "Schließen" in jedem Popup an';
}        
        }  // eo de
        elseif ($setup_lang == 'fr' && !isset ($LANGLOOKUP['conf-password_hd'])) {
        if (1 == 1) {
$LANGLOOKUP['conf-password_hd']         = 'Protégez vos paramètres avec un mot de passe';
$LANGLOOKUP['conf-password']            = 'Utilisez une chaîne dont vous vous souvenez, au moins 10 caractères, n\'utilisez pas les caractères " ou \'';
$LANGLOOKUP['conf-show_settings']       = 'Utiliser un élément de menu pour ce script de paramètres ?';
$LANGLOOKUP['conf-language_hd']         = 'Choisissez la langue par défaut que vous souhaitez afficher et utiliser.';
$LANGLOOKUP['conf-defaultlanguage']     = 'Langue à utiliser par défaut';
$LANGLOOKUP['conf-lang_select']         = 'Les visiteurs peuvent-ils changer de langue ?';
$LANGLOOKUP['conf-lang_select_yes']     = 'Un visiteur peut changer la langue utilisée';
$LANGLOOKUP['conf-future_hd']           = 'Pour référence future lorsque de nouveaux articles sont nécessaires';
$LANGLOOKUP['conf-solve_problem1']      = 'Le laisser tel qu\'il est';
$LANGLOOKUP['conf-solve_problem2']      = 'Utilisé lorsqu\'un "problème encore inconnu" apparaît';
$LANGLOOKUP['conf-country_flag']        = 'Le drapeau de votre pays';
$LANGLOOKUP['conf-livedata_hd']         = 'Programme météo / fichier de données que nous utiliserons';
$LANGLOOKUP['conf-livedataFormat']      = 'Comment obtenons-nous nos données météo sur votre site Web ?<br />Ne changez que si vous êtes sûr que votre fichier de données est disponible via le téléchargement correct ou via une API';
$LANGLOOKUP['conf-livedata']            = 'Chemin d\'accès au fichier <b>chargé</b> par votre programme météo.<br />Le bon chemin est essentiel pour l\'affichage de vos données.<br />Exemple "../clientraw.txt" lorsque votre fichier est dans le root.<br />Non utilisé avec une API.';
$LANGLOOKUP['conf-skydata_hd']          = 'Conditions nuage-ciel';
$LANGLOOKUP['conf-sky_default']         = 'Comment obtenir les conditions "Sky" actuelles ?';
$LANGLOOKUP['conf-fctdata_hd']          = 'Prévisions météorologiques';
$LANGLOOKUP['conf-fct_default']         = 'Quelle prévision météo souhaitez-vous utiliser ?';
$LANGLOOKUP['conf-extra_sensors_hd']    = 'Téléchargement séparé des capteurs supplémentaires de votre station météo';
$LANGLOOKUP['conf-have_extra']          = 'Lors de l\'utilisation de capteurs supplémentaires, un fichier de données supplémentaire peut être nécessaire';
$LANGLOOKUP['conf-extra_data']          = 'Chemin et nom du fichier téléchargé (par exemple demodata/extra_sensors.txt )';
$LANGLOOKUP['conf-snow_hd']             = 'Mesurer et/ou télécharger les profondeurs de neige';
$LANGLOOKUP['conf-snow_show']           = 'Mesurez-vous manuellement les chutes de neige et la hauteur de neige ?';
$LANGLOOKUP['conf-liveYMD']             = 'Format des dates dans le fichier en temps réel téléchargé';
$LANGLOOKUP['conf-history_hd']          = 'Les données à utiliser pour les graphiques';
$LANGLOOKUP['conf-charts_from']         = 'Le script graphique peut utiliser les données de votre station WU pour générer des graphiques<br />Sinon, vos données météorologiques seront utilisées pour un petit <b>sous-ensemble</b> des graphiques';
$LANGLOOKUP['conf-cron_hd']             = '<br /><i style="color: red">IMPORTANT</i><br /><span style="font-weight: normal;"><br />Le tableau de bord a besoin de beaucoup de données, qui souvent ne sont pas tous disponibles dans votre programme météo.<br /><br />Après avoir entré tous vos paramètres et <b>enregistré</b>, <br />prenez tout le temps nécessaire pour lire la page 1 document sur "Cron -jobs" sur le site Web pwsdashboard.com.<br /><br />L\'utilisation d\'un travail cron donne de bien meilleurs temps de réponse,<br />actualise les informations obsolètes et calcule les données haut-bas manquantes.<br />Le cron facilite également le téléchargement facultatif vers d\'autres réseaux avec des données historiques plus détaillées.</span><br /><br />';
$LANGLOOKUP['conf-hardware_hd']         = 'Quelle station météo utilisez-vous ?';
$LANGLOOKUP['conf-hardware']            = 'Décrivez votre station météo';
$LANGLOOKUP['conf-manufacturer']        = 'Quelle marque';
$LANGLOOKUP['conf-davis']               = 'Avez-vous une station météo Davis?';
$LANGLOOKUP['conf-descriptions_hd']     = 'Coordonnées de la station/du propriétaire, faites des descriptions courtes';
$LANGLOOKUP['conf-stationName']         = 'Le nom à utiliser pour votre station';
$LANGLOOKUP['conf-stationlocation']     = 'Un nom relativement court pour la zone/région dans laquelle se trouve votre station météo.';
$LANGLOOKUP['conf-contact_hd']          = 'La fenêtre de contact';
$LANGLOOKUP['conf-contact_show']        = 'Voulons-nous utiliser ce popup ?';
$LANGLOOKUP['conf-email']               = 'L\'adresse e-mail de la fenêtre de contact';
$LANGLOOKUP['conf-twitterUser']         = 'Avez-vous un compte Twitter ?';
$LANGLOOKUP['conf-twitter']             = 'Votre compte @twitter';
$LANGLOOKUP['conf-facebookuser']        = 'Souhaitez-vous afficher un lien vers un compte Facebook ?';
$LANGLOOKUP['conf-facebook']            = 'Nom de votre compte Facebook';
$LANGLOOKUP['conf-location_hd']         = 'Les données de localisation de votre station';
$LANGLOOKUP['conf-noDST']               = 'Ne changez que si vous ne réinitialisez vraiment jamais vos horloges';
$LANGLOOKUP['conf-TZ']                  = 'Définissez le fuseau horaire selon les normes PHP. <a target="_blank" href="http://php.net/manual/en/timezones.php">Regardez ici</a>';
$LANGLOOKUP['conf-lat']                 = 'La latitude (et la longitude du champ suivant) sont également spécifiées dans votre programme météo.<br />Exemple : 50.8500 correspond à Louvain en Belgique. <a href="https://www.google.com/maps/" target="blank">Regardez ici.</a> <br />Le nord de l\'équateur n\'a aucun signe. Au sud de l\'équateur se trouve un signe -.';
$LANGLOOKUP['conf-lon']                 = 'Les longitudes <b>gauche</b> de Greenwich nécessitent un caractère <b>-</b>.<br />Il s\'agit du <b style="color: red;">opposé</b> tel qu\'utilisé dans WeatherDisplay !';
$LANGLOOKUP['conf-icao1']               = 'Entrez votre code d\'aéroport le plus proche (<b>XXXX</b>) que vous <a target="_blank" href="https://www.travelmath.com/nearest-airport/"><b>ici peut trouver </b></a><br />Exemple : pour Amsterdam-NL, il s\'agit de l\'aéroport de Schiphol, le code METAR est <b>EHAM</b><br />Exemple : pour Amsterdam-NY, il s\'agit de l\'aéroport d\'Albany, le code METAR est <b>KALB</b>';
$LANGLOOKUP['conf-metar']               = 'Spectacle de fenêtre pop près de METAR';
$LANGLOOKUP['conf-metar_yes']           = 'Nous l\'utiliserons pour afficher les termes actuels';
$LANGLOOKUP['conf-airport1']            = 'Court nom descriptif de l\'aéroport';
$LANGLOOKUP['conf-airport1dist']        = 'Distance entre votre gare et l\'aéroport';
$LANGLOOKUP['conf-weatheralarm_hd']     = 'Quel service d\'alerte météo est disponible ?';
$LANGLOOKUP['conf-weatheralarm']        = 'Vous souhaitez utiliser un service d\'alerte météo ?';
$LANGLOOKUP['conf-alarm_area']          = '<b>Europe :</b>code court pour votre région, par ex. BE004 <a href="http://pwsdashboard.be/pwsWD/list_warn_codes.php" target="blank">rechercher ici</a><br /><b>Royaume-Uni MetOffice :</b><a href= "https://www.metoffice.gov.uk/weather/guides/rss" target="blank">regardez ici</a>, cliquez sur la région, les 2 dernières lettres de l\'URL <br /><b>Canada : < /b> <a href="http://dd.weather.gc.ca/citypage_weather/docs/site_list_fr.csv" target="blank ">Téléchargez cette liste.</a> Utilisez un éditeur pour modifier votre région .<br />Exemple : s0000047 est le code de Calgary';
$LANGLOOKUP['conf-province']            = 'Canada uniquement : le code à deux lettres de votre province';
$LANGLOOKUP['conf-region']              = 'Région utilisée pour la conversion d\'unités des données WU';
$LANGLOOKUP['conf-units_used_hd']       = 'Type d\'unités (exemple : C ou F) à utiliser';
$LANGLOOKUP['conf-unit']                = 'Choisissez le réglage des unités générales';
$LANGLOOKUP['conf-dec_tmp']             = 'Sélectionnez le nombre de décimales que vous souhaitez utiliser pour ces valeurs météorologiques';
$LANGLOOKUP['conf-dec_wnd']             = '';
$LANGLOOKUP['conf-dec_rain']            = '';
$LANGLOOKUP['conf-dec_baro']            = '';
$LANGLOOKUP['conf-rainrate']            = 'Intensité de pluie par heure ou minute';
$LANGLOOKUP['conf-pressureunit']        = 'Unité de pression atmosphérique / baromètre';
$LANGLOOKUP['conf-distanceunit']        = 'Distance';
$LANGLOOKUP['conf-cloudbase']           = 'Hauteur de la base des nuages';
$LANGLOOKUP['conf-aqhi_type']           = 'Norme AQI à utiliser comme indice de la qualité de l\'air';
$LANGLOOKUP['conf-date_time_hd']        = 'Sélectionnez les formats de date et d\'heure à utiliser';
$LANGLOOKUP['conf-dateFormat']          = 'Format de date';
$LANGLOOKUP['conf-clockformat']         = 'Utiliser une horloge de 24 ou 12 heures';
$LANGLOOKUP['conf-timeFormat']          = 'Format de l\'heure';
$LANGLOOKUP['conf-timeFormatShort']     = 'Format d\'heure courte utilisé pour le lever/lever du soleil et le coucher de la lune';
$LANGLOOKUP['conf-menu_hd']             = 'Options de menu';
$LANGLOOKUP['conf-themes']              = 'Afficher la sélection de thèmes dans le menu';
$LANGLOOKUP['conf-extralinks']          = 'Afficher les liens supplémentaires dans le menu (ils ne sont pas affichés par défaut)';
$LANGLOOKUP['conf-theme1']              = 'Couleur de thème par défaut';
$LANGLOOKUP['conf-extra_devices_hd']    = 'Nous avons besoin des informations pour vos appareils optionnels';
$LANGLOOKUP['conf-purpleair_aq_hd']     = 'Capteur de qualité de l\'air Purpleair';
$LANGLOOKUP['conf-purpleairhardware']   = 'Possédez-vous un capteur Purpleair ?';
$LANGLOOKUP['conf-purpleairID']         = 'Si nous en avons un, quel est l\'ID du capteur ?';
$LANGLOOKUP['conf-purpleairAPI']        = 'API Token';
$LANGLOOKUP['conf-luftdaten_aq_hd']     = 'Capteur de qualité de l\'air Luftdaten, peut être utilisé de deux manières différentes';
$LANGLOOKUP['conf-luftdatenhardware']   = 'Avez-vous un capteur Luftdaten ?';
$LANGLOOKUP['conf-luftdatenID']         = 'L\'ID du capteur pour obtenir les données du <b>site Web</b> luftdaten ?';
$LANGLOOKUP['conf-luftdatenSensor']     = 'Le numéro du capteur lorsque vos données sont <b>téléchargées sur ce site Web</b>';
$LANGLOOKUP['conf-davis_aq_hd']         = 'Stockage cloud du capteur Davis AQ';
$LANGLOOKUP['conf-davis_aq_sensor']     = 'Possédez-vous un capteur Davis AQ ?';
$LANGLOOKUP['conf-dwl_AQ']              = 'Si nous en avons un, quel est l\'identifiant de la station ? <br />Vous devrez également configurer l\'API WeatherLink Cloud version 2 ultérieurement';
$LANGLOOKUP['conf-official_aq_hd']      = 'Station officielle AQ à proximité';
$LANGLOOKUP['conf-gov_aqi']             = 'Souhaitez-vous afficher une station AQ officielle à proximité ?';
$LANGLOOKUP['conf-waqitoken']           = 'Cela nécessite un jeton API, gratuit sur <a href="https://aqicn.org/data-platform/token/#/" target="blank">ce site !</a>';
$LANGLOOKUP['conf-boltek_lightnng_hd']  = 'Données Lightning avec Nexstorm';
$LANGLOOKUP['conf-boltek']              = 'Avez-vous un appareil Nexstorm (Astrogenic Systems)';
$LANGLOOKUP['conf-boltekfile']          = 'Définissez le chemin vers votre NSRealtime.txt';
$LANGLOOKUP['conf-weatherflow_hd']      = 'Dispositif WeatherFlow ou Tempest : Données météorologiques générales - Foudre - UV Solaire';
$LANGLOOKUP['conf-weatherflowoption']   = 'Avez-vous une station Weatherflow (AIR et SKY)';
$LANGLOOKUP['conf-weatherflowID']       = 'ID DE STATION Weather-Flow';
$LANGLOOKUP['conf-eco-extra_hd']        = 'Utilisez les capteurs supplémentaires Ecowitt avec un programme météo différent';
$LANGLOOKUP['conf-ecowittoption']       = 'Utilisez-vous le "téléchargement personnalisé" d\'Ecowitt ?';
$LANGLOOKUP['conf-ecowittfile']         = 'Emplacement du fichier téléchargé';
$LANGLOOKUP['conf-ecowittAQ']           = 'Voulez-vous utiliser un ou plusieurs capteurs AQ';
$LANGLOOKUP['conf-ecowittlightning']    = 'Voulez-vous utiliser un capteur de foudre';
$LANGLOOKUP['conf-webcam_hd']           = 'Caméras météo';
$LANGLOOKUP['conf-mywebcam']            = 'Avez-vous une webcam que vous voulez montrer?';
$LANGLOOKUP['conf-mywebcamimg']         = 'Fournissez le lien vers l\'image de votre webcam';
$LANGLOOKUP['conf-uv_solar_hd']         = 'Sources de données solaires UV';
$LANGLOOKUP['conf-uvsolarsensors']      = 'Existe-t-il un capteur UV et solaire ?';
$LANGLOOKUP['conf-blocks_hd']           = 'Sélectionnez maintenant tous les blocs que vous souhaitez afficher';
$LANGLOOKUP['conf-top_small_hd']        = 'Jusqu\'à 5 petits cubes pour la rangée du haut';
$LANGLOOKUP['conf-position1']           = 'Position du haut de la rangée - Deuxième petit bloc';
$LANGLOOKUP['conf-position2']           = 'Position de la rangée supérieure - Troisième petit bloc';
$LANGLOOKUP['conf-position3']           = 'Position du haut de la rangée - Quatrième petit bloc';
$LANGLOOKUP['conf-position4']           = 'Position de la rangée supérieure - dernier bloc, s\'il convient';
$LANGLOOKUP['conf-positionlast']        = 'Fixé en haut à gauche';
$LANGLOOKUP['conf-extra3wused_hd']      = 'Normalement 3 blocs/rangée. Vous voulez ajouter 1 bloc supplémentaire pour obtenir 4 blocs par rangée ?';
$LANGLOOKUP['conf-cols_extra']          = 'Sélectionner';
$LANGLOOKUP['conf-top_row_hd']          = 'La première rangée de blocs est toujours présente, sélectionnez 3 ou 4 blocs';
$LANGLOOKUP['conf-position11']          = 'Bloc de température standard';
$LANGLOOKUP['conf-position12']          = 'Bloc de prévision standard';
$LANGLOOKUP['conf-position13']          = 'Bloc "Sky" par défaut avec les conditions actuelles / 1 heure de prévision';
$LANGLOOKUP['conf-position14']          = 'Choisissez le 4ème bloc facultatif ou décidez plus tard';
$LANGLOOKUP['conf-middle_row_hd']       = 'La deuxième rangée de blocs est toujours présente, sélectionnez 3 ou 4 blocs';
$LANGLOOKUP['conf-position21']          = 'Vent standard - bloc de rafale';
$LANGLOOKUP['conf-position22']          = 'Bloc baromètre standard';
$LANGLOOKUP['conf-position23']          = 'Bloc d\'information standard sur le soleil';
$LANGLOOKUP['conf-position24']          = 'Choisissez le 4ème bloc facultatif ou décidez plus tard';
$LANGLOOKUP['conf-bottom_row_hd']       = 'La troisième rangée de blocs est toujours présente, sélectionnez 3 ou 4 blocs';
$LANGLOOKUP['conf-position31']          = 'Pare-pluie standard';
$LANGLOOKUP['conf-position32']          = 'Sélectionnez le script à utiliser (généralement UV)';
$LANGLOOKUP['conf-position33']          = 'Sélectionnez le script à utiliser ou aucun';
$LANGLOOKUP['conf-position34']          = 'Choisissez le 4ème bloc facultatif ou décidez plus tard';
$LANGLOOKUP['conf-extrarows_hd']        = 'Normalement 3 rangées de blocs. Voulez-vous ajouter 1 ou 2 lignes supplémentaires ? ?';
$LANGLOOKUP['conf-rows_extra']          = 'Sélectionner';
$LANGLOOKUP['conf-extra_row1_hd']       = 'Option quatrième rangée avec 3 ou 4 blocs';
$LANGLOOKUP['conf-extra_row2_hd']       = 'Cinquième rangée en option avec 3 ou 4 blocs';
$LANGLOOKUP['conf-position43']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position42']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position41']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position44']          = 'Si défini sur 4 blocs de large, sélectionnez le bloc ou décidez plus tard';
$LANGLOOKUP['conf-position53']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position52']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position51']          = 'Choisissez un bloc maintenant ou décidez plus tard';
$LANGLOOKUP['conf-position54']          = 'Si défini sur 4 blocs de large, sélectionnez un bloc ou décidez plus tard';
$LANGLOOKUP['conf-suppliers_hd']        = 'La plupart des sources de données nécessitent une API ou des informations supplémentaires';
$LANGLOOKUP['conf-suppliers_AERIS']     = 'Météo Aeris : prévisions uniquement disponibles lorsque vous téléchargez sur pwsweather.com';
$LANGLOOKUP['conf-aeris_access_id']     = 'ID d\'accès à l\'API Aeris';
$LANGLOOKUP['conf-aeris_secret_key']    = 'Clé secrète de l\'API Aeris';
$LANGLOOKUP['conf-suppliers_WU']        = 'WeatherUnderground : données météo - prévisions - données historiques';
$LANGLOOKUP['conf-wu_apikey']           = 'Votre clé API WU 2019 telle que générée sur votre tableau de bord WU';
$LANGLOOKUP['conf-wuID']                = 'ID de la station WeatherUnderground pour les cartes historiques';
$LANGLOOKUP['conf-wu_start']            = 'Premier jour de téléchargement des données <b>correctes</b> vers WU<br />Le format est AAAA-MM-JJ exemple 2018-11-24';
$LANGLOOKUP['conf-suppliers_DS']        = 'Darksky - aucun nouvel utilisateur n\'est accepté par Darksky';
$LANGLOOKUP['conf-dark_alt_vrs']        = 'Sélectionnez le fournisseur de données DS alternatif que vous utilisez';
$LANGLOOKUP['conf-dark_apikey']         = 'Clé API pour les prévisions et les conditions actuelles.';
$LANGLOOKUP['conf-language']            = 'Prévisions linguistiques';
$LANGLOOKUP['conf-darkskyunit']         = 'UNITÉS API utilisées';
$LANGLOOKUP['conf-suppliers_METAR']     = 'Données METAR de votre aéroport local, utilisées pour les conditions "ciel" actuelles';
$LANGLOOKUP['conf-metarapikey']         = 'Pour la clé API CheckWX METAR, vous devez vous inscrire <a href="https://www.checkwxapi.com/" target="blank">ici</a>';
$LANGLOOKUP['conf-suppliers_AW']        = 'AmbientWeather : téléchargez vos données AmbientWeather.net';
$LANGLOOKUP['conf-aw_key']              = 'Clé API ambiante pour lire les données de votre station';
$LANGLOOKUP['conf-aw_did']              = 'ID d\'appareil pour votre appareil spécifique sur AmbientWeather.net';
$LANGLOOKUP['conf-suppliers_DWL']       = 'API WeatherLink Cloud version 1 - par ex. Enregistreur IP';
$LANGLOOKUP['conf-dwl_api']             = 'API Token v1 : tel que généré sur votre tableau de bord';
$LANGLOOKUP['conf-dwl_did']             = 'ID de l\'appareil comme sur l\'autocollant de l\'enregistreur IP';
$LANGLOOKUP['conf-dwl_pass']            = 'Mot de passe que vous utilisez pour accéder à weatherlink.com';
$LANGLOOKUP['conf-suppliers_DWL2']      = 'API WeatherLink Cloud version 2 - WLL et Airlink';
$LANGLOOKUP['conf-dwl_api2']            = 'Clé API v2 : telle que générée sur votre tableau de bord weatherlink.com';
$LANGLOOKUP['conf-dwl_secret']          = 'Secret API : tel que généré sur votre tableau de bord weatherlink.com';
$LANGLOOKUP['conf-dwl_station']         = 'ID de la station : vous pouvez le trouver avec cet outil de test <a href="https://pwsdashboard.com/wll/" target="_blank">ici</a>';
$LANGLOOKUP['conf-suppliers_YRNO']      = 'Lors de l\'utilisation de la prévision yr.no';
$LANGLOOKUP['conf-yrno_area']           = 'Actuellement non utilisé';
$LANGLOOKUP['conf-other_hd']            = 'Autres réglages';
$LANGLOOKUP['conf-show_indoor']         = 'Afficher les températures intérieures ?';
$LANGLOOKUP['conf-body_image']          = 'Facultatif : Image pour remplir l\'arrière-plan derrière le tableau de bord.';
$LANGLOOKUP['conf-check_cron']          = 'Afficher un panneau d\'avertissement pour l\'historique obsolète et autres';
$LANGLOOKUP['conf-KISS']                = 'Afficher des cadrans minimalistes pour temp/baro et autres.';
$LANGLOOKUP['conf-personalmessage']     = 'Texte facultatif dans le pied de page ?';
$LANGLOOKUP['conf-use_round']           = 'Modifiez certains éléments, tels que la température, de carré à rond';
$LANGLOOKUP['conf-txt_border']          = 'Supprimer les bordures fines ou colorées autour des petites zones de texte';
$LANGLOOKUP['conf-close_popup']         = 'Par défaut, un bouton de fermeture est affiché dans le coin supérieur gauche des popups';
$LANGLOOKUP['Do not use this']          = 'Ne l\'utilisez pas';
$LANGLOOKUP['Do not allow this']        = 'ne permettez pas cela';
$LANGLOOKUP['Yes, we do.']              = 'Oui.';
$LANGLOOKUP['Location']                 = 'lieu';
$LANGLOOKUP['Data']                     = 'Les faits';
$LANGLOOKUP['Units']                    = 'Unités';
$LANGLOOKUP['Devices']                  = 'Dispositifs';
$LANGLOOKUP['Tiles']                    = 'blocs';
$LANGLOOKUP['Other']                    = 'Autre';
$LANGLOOKUP['Save your settings']       = 'Enregistrez vos paramètres';
$LANGLOOKUP['Welcome by EasyweatherSetup']  = 'Ici, vous pouvez régler tous vos paramètres';
$LANGLOOKUP['The "settings" to adapt the dashboard to your situation are grouped into 8 pages.']  = 'Il y a 8 groupes de paramètres accessibles via un onglet';
$LANGLOOKUP['Click on a grey button above to go the another set of questions.']  = 'Cliquez sur un bouton gris pour accéder à un autre groupe de paramètres';
$LANGLOOKUP['When you are finished, or if want to stop fo a while, save your answers by clicking at the green button at the top right.']  = 'Lorsque vous avez terminé ou que vous souhaitez vous arrêter un moment, appuyez sur le bouton vert en haut à droite';
$LANGLOOKUP['If you finished all your settings, you should regularly check for updated scripts']  = 'Si tous vos paramètres fonctionnent correctement, vérifiez régulièrement les "mises à jour" disponibles.';
$LANGLOOKUP['here']                     = 'fais ça ici';
$LANGLOOKUP['All settings are OK, do not show that menu entry']  = 'Tous les réglages sont OK, plus besoin de sélection de menu.';
$LANGLOOKUP['Year  Month  Day']         = 'Année mois jour';
$LANGLOOKUP['Day Month Year']           = 'Jour mois année';
$LANGLOOKUP['Month Day Year']           = 'Année mois jour';
$LANGLOOKUP['Yes, we have extra sensors and upload the data.']  = 'Oui, nous avons des capteurs supplémentaires et téléchargeons leurs données';
$LANGLOOKUP['We do not measure snow heights.']  = 'Aucune mesure de hauteur de neige en cours d\'utilisation';
$LANGLOOKUP['We use the dashboard script to enter the snow heights']  = 'Nous utilisons le script du tableau de bord pour entrer les hauteurs de neige';
$LANGLOOKUP['We enter and upload the values with our weather-program.']  = 'Les profondeurs de neige proviennent de notre programme météo';
$LANGLOOKUP['Officially they use Daylight Saving, but we personally do not want that.']  = 'Officiellement, ils utilisent l\'heure d\'été mais je ne participe pas';
$LANGLOOKUP['I do not need this, use always official time zone standards']  = 'Je respecte l\'heure d\'été/d\'hiver officielle';
$LANGLOOKUP['Default Current weather / Sky conditions using METAR (needs API key)']  = 'Par défaut, le METAR (aéroport) est utilisé pour cela (clé API requise)';
$LANGLOOKUP['Current weather / Sky conditions by Aeris (needs API key)']  = 'Nous utilisons notre AERIS - clé API pour les conditions météorologiques actuelles';
$LANGLOOKUP['Current weather / Sky conditions from your clientraw file']  = 'Notre client WeatherDisplay fournit les conditions météorologiques actuelles';
$LANGLOOKUP['Current weather / Sky conditions  from Darksky (needs API key)']  = 'Nous utilisons notre clé API Darksky/VP/PW pour les conditions météorologiques actuelles';
$LANGLOOKUP['Current weather / Sky conditions from Environment Canada (Canada only)']  = 'Notre station est au Canada et utilise EC pour cela';
$LANGLOOKUP['Default forecast using free Yr.no  data']  = 'Prévisions météo standard avec les données YR.no';
$LANGLOOKUP['Forecast using Aeris data (needs API key)']  = 'Prévisions météo avec les données AERIS avec notre clé API';
$LANGLOOKUP['Forecast using DarkSky data (needs API key)']  = 'Prévisions météo avec les données DarkSky avec notre clé API';
$LANGLOOKUP['Forecast using free Environment Canada  data (Canada only)']  = 'Prévisions météorologiques avec données EC (pour le Canada seulement)';
$LANGLOOKUP['Forecast using WeatherUnderground data (needs API key)']  = 'Prévisions météo avec les données WeatherUnderground avec notre clé API';
$LANGLOOKUP['Forecast using WXSIM data from extra PC-program data']  = 'Prévisions météo via notre programme PC WXSIM';
$LANGLOOKUP['Europe uses meteoalarm.org']  = 'Utiliser les données européennes de meteoalarm.org';
$LANGLOOKUP['Use our WeatherUnderground data for charts']  = 'Utiliser WeatherUnderground';
$LANGLOOKUP['Save the daily data for the charts']  = 'Sauvegardez nos données météo chaque jour';
$LANGLOOKUP['Yes, we want to use that AQI.']  = 'Oui, nous voulons utiliser cette mesure de la qualité de l\'air';
$LANGLOOKUP['Do not have these']        = 'nous n\'utilisons pas';
$LANGLOOKUP['Our station has both UV and solar']  = 'Notre station a Solaire et UV';
$LANGLOOKUP['Our station has solar. Use Darksky UV forcast as the UV sensor']  = 'Utilisez Darksky pour les mesures UV';
$LANGLOOKUP['We use a weatherflow device for UV and solar']  = 'Utilisez notre appareil WeatherFlow pour les mesures solaires et UV';
$LANGLOOKUP['Always the advices are displayed here']  = 'Aucune modification possible, des conseils, etc. sont toujours inclus ici.';
$LANGLOOKUP['Yes, we need 1 extra block on the right of each row.']  = 'Nous voulons 1 bloc supplémentaire à droite avec chaque ligne';
$LANGLOOKUP['Yes, we need a fourth row of blocks.']  = 'Oui, nous voulons une quatrième rangée supplémentaire';
$LANGLOOKUP['Yes, we need a fourth and fifth row of blocks.']  = 'Oui, nous voulons une quatrième et une cinquième rangée';
$LANGLOOKUP['Select later']             = 'Nous choisirons plus tard';
$LANGLOOKUP['si:Standard ISO']          = 'si : Norme ISO avec vent en m/s';
$LANGLOOKUP['ca: same as si, windSpeed km/h']  = 'ca : égal à "si" avec vent en km/h';
$LANGLOOKUP['uk: same as si,windSpeed mph']  = 'uk : Equivalent à "si" avec vent en mph';
$LANGLOOKUP['us: Imperial units (NON METRIC)']  = 'us : unités impériales (PAS MÉTRIQUES)';
$LANGLOOKUP['Allow visitor to switch colour themes.']  = 'Le visiteur peut ajuster les couleurs';
$LANGLOOKUP['Not allowed']              = 'Aucun ajustement par le visiteur';
$LANGLOOKUP['light']                    = 'Jeu de couleurs claires';
$LANGLOOKUP['dark']                     = 'jeu de couleurs sombres';
$LANGLOOKUP['your own color set']       = 'Vos propres paramètres de couleur personnalisables';
$LANGLOOKUP['Yes make items round.']    = 'Utilisez des objets ronds';
$LANGLOOKUP['We like the square items']  = 'Utiliser des éléments angulaires';
$LANGLOOKUP['Leave the small borders around text blocks, we like that.']  = 'Lignes fines autour des blocs de texte';
$LANGLOOKUP['We like "Less is more", remove those borders']  = 'Oubliez les lignes fines';
$LANGLOOKUP['Yes, show the close button/text in every pop-up']  = 'Afficher le bouton "Fermer" dans chaque pop-up';
}
}  // eo fr
        else {  
        if (1 == 1) {
$LANGLOOKUP= array();
$LANGLOOKUP['conf-password_hd']         = 'Protect  your settings with a password';
$LANGLOOKUP['conf-password']            = 'Use a string you remember, at least 10 characters, do not use " or \' characters';
$LANGLOOKUP['conf-show_settings']       = 'Show a menu entry for this setings script?';
  
$LANGLOOKUP['conf-language_hd']	        = 'Choose the default language to display and use.';
$LANGLOOKUP['conf-defaultlanguage']	= 'Template language to be used as default';
$LANGLOOKUP['conf-lang_select']	        = 'Are visitors allowed to change the template language?';
$LANGLOOKUP['conf-lang_select_yes']	= 'A visitor may change the language used';
$LANGLOOKUP['conf-future_hd']	        = 'For futere use when new items are needed';
$LANGLOOKUP['conf-solve_problem1']	= 'Leave as is';
$LANGLOOKUP['conf-solve_problem2']	= 'Will be used for "yet unknown problem"  support requests';
$LANGLOOKUP['conf-country_flag']	= 'Your country flag';
$LANGLOOKUP['conf-livedata_hd']	        = 'Weather-program / live data file we will use';
$LANGLOOKUP['conf-livedataFormat']	= 'How do we get our weather data into your website?'
                .'<br />Only change if you are sure your data-file is available from correct upload or from an API';                
$LANGLOOKUP['conf-livedata']	        = 'Path to your <b>realtime</b> data file. Not used with an API.'
                .'<br />Correct path is essential for live realtime data display.'
                .'<br />Example "../clientraw.txt" when your file is in the root.';
$LANGLOOKUP['conf-skydata_hd']	        = 'Sky conditions';
$LANGLOOKUP['conf-sky_default']	        = 'How dow we get current Sky conditions?';

$LANGLOOKUP['conf-fctdata_hd']	        = 'Weather forecast';
$LANGLOOKUP['conf-fct_default']	        = 'Which weather forecast do you want to use?';

$LANGLOOKUP['conf-extra_sensors_hd']	= 'Separate upload of the extra sensors from your weatherstation';
$LANGLOOKUP['conf-have_extra']	        = 'When using extra sensors an extra data file can be needed';
$LANGLOOKUP['conf-extra_data']	        = 'Path and filename of the uploaded file (f.i. demodata/extra_sensors.txt  )';  

$LANGLOOKUP['conf-snow_hd']	        = 'Measure and or upload of snow-heights';
$LANGLOOKUP['conf-snow_show']	        = 'If and how do you measure snow fall and height';

$LANGLOOKUP['conf-liveYMD']	        = 'Format of the dates  in the uploaded realtime file';
$LANGLOOKUP['conf-history_hd']          = 'The data to use for the graphs';
$LANGLOOKUP['conf-charts_from']         = 'The graph-script should use my WU-station-data to generate the graphs
<br />I do not want to use WU, save my weather-data for a <b>subset</b> of the graphs';            

$LANGLOOKUP['conf-cron_hd']             = '<br /><i style="color: red">IMPORTANT</i><br /><span style="font-weight: normal;">
<br />The Dashboard needs a lot of data, which is mostly not available from your weather-program.
<br />After you entered and <b>saved</b> all your settings please take ample time to read the 1 page document about "Cron-jobs" at the pwsdashboard.com website.
<br /><br />Using a cron-job will give far better response times, prohibit outdated information and will make sure that missing high-low data is calculated.
<br />The cron will also facilitates optional uploading to other networks and having  more detailed historical data.</span><br /><br />';   
$LANGLOOKUP['conf-hardware_hd']	        = 'Which brand and type of weatherstation do you own?';
$LANGLOOKUP['conf-hardware']	        = 'Describe your weather-station';
$LANGLOOKUP['conf-manufacturer']	= 'Which brand';
$LANGLOOKUP['conf-davis']	        = 'Do you own a Davis weather-station?';          
$LANGLOOKUP['conf-descriptions_hd']	= 'Station/owner details, keep the descriptions short ';
$LANGLOOKUP['conf-stationName']	        = 'The name to be used for your station';
$LANGLOOKUP['conf-stationlocation']	= 'A relative short name for the area/region your weather-station is in.';

$LANGLOOKUP['conf-contact_hd']          = 'The contact pop-up';
$LANGLOOKUP['conf-contact_show']        = 'Do we want to use it';
$LANGLOOKUP['conf-email']	        = 'The email address for the contact pop-up';
$LANGLOOKUP['conf-twitterUser']	        = 'Do you have a twitter account?';
$LANGLOOKUP['conf-twitter']	        = 'Your @twitter account';

$LANGLOOKUP['conf-facebookuser']	= 'Do you want to show a link to your facebook account ?';
$LANGLOOKUP['conf-facebook']	        = 'Your facebook account-name';

$LANGLOOKUP['conf-location_hd']	        = 'Your station location details';
$LANGLOOKUP['conf-noDST']               = 'Change only if you really never reset your clocks';               
$LANGLOOKUP['conf-TZ']	                = 'Set your timezone according to the PHP standards. '
                                                .'<a target="_blank" href="http://php.net/manual/en/timezones.php">Check here</a>';
$LANGLOOKUP['conf-lat']	                = 'Latitude (and next field longitude) are also specified in your weather program.'
                                                .'<br />Example: 50.8500 is for Leuven in Belgium. <a href="https://www.google.com/maps/" target="blank">Check here.</a> '
                                                .'<br />North of the equator has  no sign.  South of the equator has a - sign.';
$LANGLOOKUP['conf-lon']	                = 'For longitudes <b>left</b> of Greenwich a <b>-</b> sign is needed.'
                                                .'<br />This is the <b style="color: red;">opposite</b> as used in WeatherDisplay!'; 
$LANGLOOKUP['conf-icao1']	        = 'Enter your nearest airport code (<b>XXXX</b>) which you can  find here <a target="_blank" href="https://www.travelmath.com/nearest-airport/"><b>here</b></a>'
                                                .'<br />Example: For Amsterdam-NL it is Schiphol airport , its METAR code is <b>EHAM</b>'
                                                .'<br />Example: For Amsterdam-NY it is Albany airport , its METAR code is <b>KALB</b>';
$LANGLOOKUP['conf-metar']	        = 'Display Nearby Metar pop window';
$LANGLOOKUP['conf-metar_yes']	        = 'We will use this to for the current conditions display';
$LANGLOOKUP['conf-airport1']	        = 'Short descriptive name of the airport';
$LANGLOOKUP['conf-airport1dist']	= 'Distance between your station and airport';

$LANGLOOKUP['conf-weatheralarm_hd']	= 'Which weather-alarm service is available?';
$LANGLOOKUP['conf-weatheralarm']	= 'Do you want to use a weather alarm service';
$LANGLOOKUP['conf-alarm_area']	        = '<b>Europe:</b>short code for your area, example BE004 <a href="http://pwsdashboard.be/pwsWD/list_warn_codes.php" target="blank">Find yours here</a>'
                                         .'<br /><b>UK MetOffice:</b><a href="https://www.metoffice.gov.uk/weather/guides/rss" target="blank">check here</a>, click on region, last 2 letters of URL '
                                         .'<br /><b>Canada:</b> <a href="http://dd.weather.gc.ca/citypage_weather/docs/site_list_en.csv" target="blank">Download this list.</a> '
                                         .'Use an editor find your area.<br />Example: s0000047 is the code for Calgary';     
$LANGLOOKUP['conf-province']	        = 'Canada only: The two letter code for your province';                             
$LANGLOOKUP['conf-region']	        = 'Region used for unit conversion of WU data';
$LANGLOOKUP['conf-units_used_hd']	= 'Type of units (example: C or F) to be used';
$LANGLOOKUP['conf-unit']	        = 'Choose the general units setting';          
$LANGLOOKUP['conf-dec_tmp']	        = 'Select number of decimals you want to use for these weather-values';
$LANGLOOKUP['conf-dec_wnd']	        = ' ';
$LANGLOOKUP['conf-dec_rain']	        = ' ';
$LANGLOOKUP['conf-dec_baro']	        = ' ';
$LANGLOOKUP['conf-rainrate']	        = 'Intensity of rain per hour or minute';
$LANGLOOKUP['conf-pressureunit']	= 'Unit for air pressuere / barometer';
$LANGLOOKUP['conf-distanceunit']	= 'Distance';
$LANGLOOKUP['conf-cloudbase']	        = 'Cloudbase height';                       
$LANGLOOKUP['conf-aqhi_type']	        = 'AQI-standard to use as Airquality Index ';                       

$LANGLOOKUP['conf-date_time_hd']	= 'Select date and time formats to be used';
$LANGLOOKUP['conf-dateFormat']	        = 'Date format';
$LANGLOOKUP['conf-clockformat']	        = 'Use 24 or 12 hour clock';
$LANGLOOKUP['conf-timeFormat']	        = 'Time format';
$LANGLOOKUP['conf-timeFormatShort']	= 'Short time format used for Sun- & Moon- rise/set';
$LANGLOOKUP['conf-menu_hd']	        = 'Menu options';
$LANGLOOKUP['conf-themes']	        = 'Display theme-selection in Menu ';
$LANGLOOKUP['conf-extralinks']	        = 'Display Extra links in Menu (default they are not shown)';
$LANGLOOKUP['conf-theme1']	        = 'Default Theme Colour ';

$LANGLOOKUP['conf-extra_devices_hd']	= 'We need the information for your optional devices';
$LANGLOOKUP['conf-purpleair_aq_hd']	= 'Purpleair Air Quality sensor';
$LANGLOOKUP['conf-purpleairhardware']	= 'Do you own a Purpleair sensor?';
$LANGLOOKUP['conf-purpleairID']	        = 'If we have one, what is the sensor-ID?';
$LANGLOOKUP['conf-purpleairAPI']        = 'Purple Air sensor API key';
$LANGLOOKUP['conf-luftdaten_aq_hd']	= 'Luftdaten Air Quality sensor, can be used in two different ways';
$LANGLOOKUP['conf-luftdatenhardware']	= 'Do you own a Luftdaten sensor?';
$LANGLOOKUP['conf-luftdatenID']	        = 'The sensor-ID to get the data from the luftdaten <b>website</b>?';
$LANGLOOKUP['conf-luftdatenSensor']	= 'The sensor number when your data is <b>uploaded to this website</b>';
$LANGLOOKUP['conf-davis_aq_hd']	        = 'Davis AQ sensor cloud storage';
$LANGLOOKUP['conf-davis_aq_sensor']	= 'Do you own a Davis AQ sensor?';
$LANGLOOKUP['conf-dwl_AQ']	        = 'If we have one, what is the station-ID? <br />You have to set the WeatherLink Cloud version 2 API later on also';
$LANGLOOKUP['conf-official_aq_hd']	= 'Nearby official AQ station';
$LANGLOOKUP['conf-gov_aqi']	        = 'Do you want to show an official AQ station nearby?';
$LANGLOOKUP['conf-waqitoken']	        = 'You need an api token for that, get one at <a href="https://aqicn.org/data-platform/token/#/" target="blank">this site!</a>';
$LANGLOOKUP['conf-boltek_lightnng_hd']	= 'Lightning data using Nexstorm';
$LANGLOOKUP['conf-boltek']	        = 'Do you own a Nexstorm device (Astrogenic Systems)';
$LANGLOOKUP['conf-boltekfile']	        = 'Set the path to your NSRealtime.txt';
$LANGLOOKUP['conf-weatherflow_hd']	= 'WeatherFlow or Tempest device: general weather-data - lightning - UV-Solar';
$LANGLOOKUP['conf-weatherflowoption']	= 'Do you own a Weatherflow station (AIR and SKY)';
$LANGLOOKUP['conf-weatherflowID']	= 'Weather-Flow STATION ID';

$LANGLOOKUP['conf-eco-extra_hd']	= 'Use Ecowitt extra-sensors with another weather-program';
$LANGLOOKUP['conf-ecowittoption']	= 'Do you "custom-upload" your Ecowitt sensors';
$LANGLOOKUP['conf-ecowittfile']	        = 'Location of the uploaded file';
$LANGLOOKUP['conf-ecowittAQ']	        = 'Do you want to use one or more AQ sensors';
$LANGLOOKUP['conf-ecowittlightning']	= 'Do you want to use the lightning sensor';


$LANGLOOKUP['conf-webcam_hd']	        = 'Your weather-cams';
$LANGLOOKUP['conf-mywebcam']	        = 'Do you have webcam you want to show';
$LANGLOOKUP['conf-mywebcamimg']	        = 'Specify the link to your webcam image';
$LANGLOOKUP['conf-uv_solar_hd']	        = 'Sources for UV-solar data';
$LANGLOOKUP['conf-uvsolarsensors']      = 'Is an UV and Solar sensor available?';

$LANGLOOKUP['conf-blocks_hd']	        = 'Select now all blocks you want to show';                             
$LANGLOOKUP['conf-top_small_hd']	= 'Up to 5 small blocks for the top row';
$LANGLOOKUP['conf-position1']	        = 'Position top row - second small block';
$LANGLOOKUP['conf-position2']	        = 'Position top row - third small block';
$LANGLOOKUP['conf-position3']	        = 'Position top row - fourth small block';
$LANGLOOKUP['conf-position4']	        = 'Position top row - last small block, if it fits';
$LANGLOOKUP['conf-positionlast']	= 'Fixed at top left';

$LANGLOOKUP['conf-extra3wused_hd']      = 'Normally 3 blocks / row. Do you want to add 1 extra to get 4 blocks at each row ?';
$LANGLOOKUP['conf-cols_extra']          = 'Select';

$LANGLOOKUP['conf-top_row_hd']          = 'First block row is always present, select 3 or 4 blocks';
$LANGLOOKUP['conf-position11']	        = 'Default temperature block';
$LANGLOOKUP['conf-position12']	        = 'Default forecast block';
$LANGLOOKUP['conf-position13']	        = 'Default "Sky" block with current condions / 1 hour forecast';
$LANGLOOKUP['conf-position14']	        = 'Choose optional 4-th block or decide later';

$LANGLOOKUP['conf-middle_row_hd']       = 'Second block row is always present, select 3 or 4 blocks';
$LANGLOOKUP['conf-position21']	        = 'Default Wind - Gust block';
$LANGLOOKUP['conf-position22']	        = 'Default Barometer block';
$LANGLOOKUP['conf-position23']	        = 'Default Sun information block';
$LANGLOOKUP['conf-position24']	        = 'Choose optional 4-th block or decide later';

$LANGLOOKUP['conf-bottom_row_hd']       = 'Third block row is always present, select 3 or 4 blocks';
$LANGLOOKUP['conf-position31']	        = 'Default Rain block';
$LANGLOOKUP['conf-position32']	        = 'Select script to be used (mostly UV)';
$LANGLOOKUP['conf-position33']	        = 'Select script to be used or none';
$LANGLOOKUP['conf-position34']	        = 'Choose optional 4-th block or decide later';

$LANGLOOKUP['conf-extrarows_hd']        = 'Normally 3 rows with blocks. Do you want to add 1 or 2 extra rows??';
$LANGLOOKUP['conf-rows_extra']          = 'Select';

$LANGLOOKUP['conf-extra_row1_hd']       = 'Optional fourth row with 3 or 4 blocks';
$LANGLOOKUP['conf-extra_row2_hd']       = 'Optional fifth row with 3 or 4 blocks';
$LANGLOOKUP['conf-position41']          = 
$LANGLOOKUP['conf-position42']	        = 
$LANGLOOKUP['conf-position43']	        = 'Choose block or decide later';
$LANGLOOKUP['conf-position44']	        = 'If set to 4 blocks wide, select block or decide later';
$LANGLOOKUP['conf-position51']          = 
$LANGLOOKUP['conf-position52']	        = 
$LANGLOOKUP['conf-position53']	        = 'Choose block or decide later';
$LANGLOOKUP['conf-position54']	        = 'If set to 4 blocks wide, select block or decide later';


$LANGLOOKUP['conf-suppliers_hd']	= 'Most data sources need an API or extra informatiom';

$LANGLOOKUP['conf-suppliers_AERIS']	= 'Aeris Weather: forecast only if you upload to pwsweather.com';
$LANGLOOKUP['conf-aeris_access_id']	= 'Aeris API Access ID';
$LANGLOOKUP['conf-aeris_secret_key']	= 'Aeris API Secret Key';

$LANGLOOKUP['conf-suppliers_WU']	= 'WeatherUnderground: weather-data - forecast - historical data';
$LANGLOOKUP['conf-wu_apikey']           = 'Your 2019 WU API-key as generated on your WU-dashboard';
$LANGLOOKUP['conf-wuID']	        = 'WeatherUnderground station ID for historical charts';
$LANGLOOKUP['conf-wu_start']	        = 'First day of uploading <b> correct </b>data to WU<br />Format is YYYY-MM-DD  example 2018-11-24';

$LANGLOOKUP['conf-suppliers_DS']	= 'Darksky and alternatives after March 31, 2023 ';
$LANGLOOKUP['conf-dark_alt_vrs']        = 'Select which alternative DS data provider you are using';
$LANGLOOKUP['conf-dark_apikey']	        = 'API Key for forecast and current conditions.';
$LANGLOOKUP['conf-language']	        = 'Forecast Language';
$LANGLOOKUP['conf-darkskyunit']	        = 'API UNITS used';

$LANGLOOKUP['conf-suppliers_METAR']	= 'METAR-data from your local airport,  used for current sky conditions';                
$LANGLOOKUP['conf-metarapikey']	        = 'CheckWX Metar API KEY you need to sign up <a href="https://www.checkwxapi.com/" target="blank">here</a>';

$LANGLOOKUP['conf-suppliers_AW']	= 'AmbientWeather: download your AmbientWeather.net-data';
$LANGLOOKUP['conf-aw_key']              = 'Ambient-API-key to read your station data ';
$LANGLOOKUP['conf-aw_did']              = 'Device ID for your specific device on AmbientWeather.net';

$LANGLOOKUP['conf-suppliers_DWL']	= 'WeatherLink Cloud version 1 API - f.i. IP-logger';
$LANGLOOKUP['conf-dwl_api']             = 'API Token v1: as generated on your dashboard';
$LANGLOOKUP['conf-dwl_did']             = 'Device ID as on sticker of IP-logger';
$LANGLOOKUP['conf-dwl_pass']            = 'Password you use to access weatherlink.com';

$LANGLOOKUP['conf-suppliers_DWL2']	= 'WeatherLink Cloud version 2 API - WLL and Airlink';
$LANGLOOKUP['conf-dwl_api2']            = 'API Key v2: as generated on your weatherlink.com dashboard';
$LANGLOOKUP['conf-dwl_secret']          = 'API Secret: as generated on your weatherlink.com dashboard';
$LANGLOOKUP['conf-dwl_station']         = 'Station ID: You can findt it with this test-program <a href="https://pwsdashboard.com/wll/" target="_blank">here</a>';

$LANGLOOKUP['conf-suppliers_YRNO']	= 'When using yr.no forecast';
$LANGLOOKUP['conf-yrno_area']	        = 'Currently not used';

$LANGLOOKUP['conf-other_hd']	        = 'Other settings';
$LANGLOOKUP['conf-show_indoor']         = 'Show indoor temperatures?';
$LANGLOOKUP['conf-body_image']          = 'Optional: Image to fill the background behind the dashboard.';
$LANGLOOKUP['conf-check_cron']          = 'Show warning mark for outdated history a.s.o.';

$LANGLOOKUP['conf-KISS']                = 'Show minimalistic dials for temp/baro a.s.o.';
$LANGLOOKUP['conf-personalmessage']     = 'Optional text to be placed in the footer?';
$LANGLOOKUP['conf-use_round']           = 'Change some items, such as the temperature, from square to round';
$LANGLOOKUP['conf-txt_border']          = 'Remove the thin or coloured borders around the small text parts';
$LANGLOOKUP['conf-close_popup']         = 'Default a close button displayed in te top left corner of the pop-ups';

$LANGLOOKUP['Current weather / Sky conditions  from Darksky (needs API key)']  = 'We use our Darksky/VP/PW - API key for current weather conditions';
$LANGLOOKUP['Do not use this']	        = 'Do not use this';
$LANGLOOKUP['Do not allow this']	= 'Do not allow this';
$LANGLOOKUP['Yes, we do.']	        = 'Yes, we do.';
$LANGLOOKUP['Location']               = 'Location';                
$LANGLOOKUP['Data']                   = 'Data';                
$LANGLOOKUP['Units']                  = 'Units';                
$LANGLOOKUP['Devices']                = 'Devices';                
$LANGLOOKUP['Tiles']                  = 'Tiles';  
$LANGLOOKUP['Other']                  = 'Other';
$LANGLOOKUP['Save your settings']     = 'Save your settings'; 
}  
        }  // eo en default
} // eo load texts

function tr_setting($key, $arr) # generate one line
     {  global  $wp, $region; 
	$wp = '--'; $region = '--';
	if ($arr['wp'] 	   <> '--' && $LANGLOOKUP['wp']     <> $wp) 	// skip lines for other weatherprogram	
		{return;} 
	if ($arr['region'] <> '--' && $arr['region'] <> $region)// skip lines for other region	
		{return;}
	if ($arr['type'] == 'none') 				// skip no use lines
		{return;}					
	if ($arr['type'] == '##') {
		echo '</table>
<table class="tabcontent" id="'.$arr['old'].'">
';	
		return;
	}
	if ($arr['type'] == '#') {
		echo '
    <tr class="headerline1"><td class="headerline1" colspan="2" >'.langtransstr($arr['old']).'</td></tr>';	
		return;
	}
	$border         = $error = '';
	$setting	= $arr['setting'];
	$text		= 'conf-'.trim($setting);
	$link		= 'link-'.trim($setting);
	$text_trans     = langtransstr($text);
	if ($text == $text_trans) {$text_trans = 'explain text for '.$setting.' will be added shortly';}

	$field_type	= $field_typeXX	= $arr['type'];
	$value		= $arr['new'];
	$value_old	= $arr['old'];
	$field_type	= str_replace ('region','',$field_typeXX);
	if ($field_type <> $field_typeXX) {
		$field_type	= lcfirst($field_type);
		$arrOld	= explode ('!',$value_old.'!');
		foreach ($arrOld as $keyOld => $val_regOld) {
			list ($value_old, $regionOld) 	= explode ('#',$val_regOld.'#');	
			if (($regionOld == $region) || ($regionOld == '')  || ($regionOld == 'all') || ($regionOld == '--')  ) {break;}
		} // eo foreach 
	} 
	if  	( ($value_old == 'true') && ($value === true) )	{$value = $value_old;} 
	elseif  ( ($value_old == 'false')&& ($value === false)) {$value = $value_old;}
	$class = '';
	if 	($value == '')		{$value	= $value_old; $class = 'default';} 
	$values		= $arr['values'];
	echo '
    <tr class="">
      <td class="label"><span class="outkey">'.$setting.'</span>'.$text_trans.'</td>
      <td class="value" '.$border.'>';
# ----------------------------      
	if ($field_type == 'select') {
		echo '
        <div class="input '.$class.'" ><!-- '.$field_type.' with value = '.$value. ' -->
          <select class="edit  '.$class.'" id="config__'.$key.'" name="settings['.$setting.']">';
		$arr_values 	= explode ('!',$values);
		foreach ($arr_values as $none => $string) {
			list ($short,$long,$optional) = explode ('#',$string.'#');
			$optional	= trim($optional);
			if ($optional <> '') {
				$ok = array($region, 'all', '--');
				if (!in_array ($optional, $ok ) ) {continue;}
			}
			if ($value == $short) {$selected = 'selected="selected"';} else {$selected = '';}
			$long	= langtransstr($long);
			echo '
            <option value="'.$short.'" '.$selected.'>'.$long.'</option>';
		}
            	echo '
          </select>  
        </div>';
	}
# ----------------------------
	elseif (  $field_type  == 'htmltext' || $field_type == 'numberDecimal'  ||
		  $field_type  == 'allcap'  || $field_type == 'noDecimal') {
		echo '
        <div class="input '.$class.'"><!-- '.$field_type.' with value = '.$value. ' -->
          <input id="config__'.$key.'" name="settings['.$setting.']" type="text" class="edit" value="'.$value.'">
        </div>';
        }
# ----------------------------
	else {	echo ' 
 	<div class="input '.$class.'"><!-- '.$field_type.' with value = '.$value. ' -->
          <input id="config__'.$key.'" name="settings['.$setting.']" type="text" class="edit" value="INVALID FIELDTYPE '.$field_type.' - '.$value.'">
        </div>';
        } // eo if list
	if ($error <> '') {echo $error;}    
	echo '
      </td>
    </tr>';
}

