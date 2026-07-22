<?php  $scrpt_vrsn_dt  = 'wrnWarningEU-CAP.php|01|2023-07-14|';  # lang popup + mltpl corr +img + description + lang +  link adapted + filter multi green + test value + version 2  ATOM feed # release 2012_lts
#
#  Generate list of warnings from  metealarm.org 
#                  used in Advisory box top left                         
#-----------------------------------------------
#
#         DO NOT COPY THIS SCRIPT or reditribute
#
# Script snippets used from other developers and
#     are licensed for use in PWS_Dashboard only 
#-----------------------------------------------
# Datasource: 
# https://feeds.meteoalarm.org/api/v1/warnings/slug  
# Documentation: 
# https://edrop.zamg.ac.at/owncloud/index.php/s/j3axFZiMiKwmcBw#pdfviewer
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#  ------------------------------------ settings 
$skip_lvl1      = true;                         // level1 warnings (green) should be skipped
$warn_cache     = './jsondata/warningCAP.arr';                         
$cache_max_age  = 900;                    
$detail_page    = true;
$detail_page_url= './index.php?frame=weatheralarms';
#
$now            = time();                       // valid warnings this period
$future         = $now + 36*3600;               // warnings should at least start before
$image_url      = './wrnImagesLarge/aw##.jpg';
$test            = 0; 
#
#  --------------------------------- test values 
#
#$alarm_area     = 'AT317,AT320,AT321,ES106,ES107,IT009,IT005';#    'DE052'; #,DE029,DE116,DE183,DE260'; #AT317,AT320,AT321,DE288'; #'BE004,BE34,BE35'; #'NL018,DE173,DE030'; #ES106,ES107, 'IT009,IT005'; #'ES852,ES853'; #'HR803'; #
#$test_cap       = './jsondata/warningCAP-BE.json';
#$warn_cache     = './jsondata/warningCAP-BE.arr';
#$now            = $now - 3*24*3600;
#$cache_max_age  = 900000;
#$test            = 1*24*3600;           // use old warnings for test
#$keep_old       = true;
#
# -------------------------------------- styling
#   
$warncolors     = array();
$warncolors[0]  = '#fff';               
$warncolors[1]  = '#fff';  
$warncolors[2]  = '#FBEA55'; 
$warncolors[3]  = '#F19E39'; 
$warncolors[4]  = '#DD353D'; #'#BB2739'; 
#
$warnlevels     = array('--', 'None', 'Moderate', 'Severe', 'Extreme');
$warntypes      = array('none_0',            
                        'wind',            'snow-ice',     'thunderstorm', 'fog',        'high-temperature',
                        'low-temperature', 'coastalevent', 'forest-fire', 'avalanches', 'rain',
                        'none-11',         'flooding',     'rain-flood');
#
if ($weatheralarm <> 'europe') 
     {  echo 'error '.__LINE__.PHP_EOL;
        return false;}
#
$ownpagehtml ='<h3>'.lang('No active warnings').' ('.$alarm_area.')</h3>';
#
$countries      = array('AT'  => 'austria',     'BA'  => 'bosnia-herzegovina',                  'BE'  => 'belgium',     'BG' => 'bulgaria',    
                        'CH'  => 'switzerland', 'CY'  => 'cyprus',      'CZ'  => 'czechia',     'DE'  => 'germany',     'DK'  => 'denmark',
                        'EE'  => 'estonia',     'ES'  => 'spain',       'FI'  => 'finland',     'FR'  => 'france',      'GR'  => 'greece',
                        'HR'  => 'croatia',     'HU'  => 'hungary',     'IE'  => 'ireland',     'IL'  => 'israel',      'IS' => 'iceland',  
                        'IT'  => 'italy',       'LT'  => 'lithuania',   'LU' => 'luxembourg',   'LV'  => 'latvia',      'MD'  => 'moldova',
                        'ME'  => 'montenegro',  'MK'  => 'republic-of-north-macedonia',         'MT'  => 'malta',       'NL'  => 'netherlands', 
                        'NO'  => 'norway',      'PL'  => 'poland',      'PT'  => 'portugal',    'RO'  => 'romania',     'RS'  => 'serbia',      
                        'SE'  => 'sweden',      'SI'  => 'slovenia',    'SK'  => 'slovakia',    'UK' => 'united-kingdom');
#
$lang_warn      = array('en'    => 'english',   'es'    => 'español',   'fr'    => 'français', 
                        'no'    => 'norsk',     'sk'    => 'slovenčina','ne-NL' => 'nederlands'  ,'nl-BE' => 'nederlands'  ,
                        'de-DE' => 'deutsch',   'en-GB' => 'english',   'es-ES' => 'español',
                        'fi-FI' => 'suomi',     'fr-FR' => 'français',  'gr-GR' => 'Ελληνικά',
                        'hr-HR' => 'hrvatski',  'it-IT' => 'italiano',  'lv'    => 'latviešu',
                        'po-PL' => 'polski',    'pt-PT' => 'português', 'sv-SE' => 'svenska'
                         );  #'' => '', 
#
$warns          = array();
$alarm_areas    = explode (',',$alarm_area);
$cntrs          = array();
$text   = ' areas=';
foreach ($alarm_areas as $area)
     {  $cntr           = substr($area,0,2);
        $text   .= $area.', ';
        $cntrs[$cntr]   =$cntr;} 
$text   = substr($text,0,-2).' countries=';
foreach ($cntrs as $cntr) 
     {  $text   .= $cntr.', ';}
$stck_lst .= basename(__FILE__).' ('.__LINE__.') '.substr($text,0,-2).PHP_EOL;  
#
if (is_file($warn_cache) )
     {  $cache_age      = $now  - filemtime($warn_cache);}
else {  $cache_age      = $now;}
#
if (isset ($test_cap) )
     {  $cache_age      = 999999999999;}
#
#
if (array_key_exists('force',$_REQUEST) && trim($_REQUEST['force']) == 'alarm') { $cache_age = $now;}
#
if ($cache_age > $cache_max_age)
     {  $warns  = array();
        $items  = 0;
        $used   = -1;
        $others = 0;
        $invalid= 0;
        $to_old = 0;
        $to_young = 0;    # 
        $multiple = 0;
        $updated= 0;  
        $now    = time(); 
        $cnts = array ('no-array'=> 0, 'missing'=> 0, 'future'=> 0, 'to-old'=> 0, 'not-forus' => 0, 'forus' => 0, 'total' => 0);  
        foreach ($cntrs as $country)     #----- / country
             {  $warn_url	= 'https://feeds.meteoalarm.org/api/v1/warnings/feeds-'.$countries[$country];   # 2023-03-08
                $fl_to_load     = $country.'_warnings';  #echo __LINE__.' $warn_cache='.$warn_cache.'  $warn_url='.$warn_url; exit;
                $load           = warn_curl();  
                if ($load === false) 
                     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') invalid data load for'.$fl_to_load.PHP_EOL;
                        continue;}      // next country    
                $array          = json_decode($result,TRUE);    #echo $stck_lst.__LINE__.print_r ($array,true); exit;
                unset ($result);
                $valid_data     = true;
                if (!array_key_exists ('warnings',$array) || count ($array['warnings']) < 1)
                     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') no wanrinigs in json file for'.$fl_to_load.PHP_EOL;  
                        continue;}      // next country
                foreach ($array['warnings'] as $warning)
                     {  $id_alrt = $countries[$country].'/'.$warning['uuid']; # echo __LINE__.print_r($warning,true); exit;
                        $cnts['total']++;
                        foreach ($warning as $alert)
                             {  if (!is_array($alert) || !array_key_exists ('info',$alert) )  { continue;} // uuid skipped
                                if (!is_array ($alert['info']) ) 
                                     {  $alert['info'][]= $alert['info'];}
                                if (!array_key_exists ('expires',  $alert['info'][0]) ) { $cnts['missing']++; continue;}
                                if (!array_key_exists ('effective',$alert['info'][0]) ) { $cnts['missing']++; continue;}
                                if (!array_key_exists ('onset',    $alert['info'][0]) ) { $cnts['missing']++; continue;}
                                $from_o         = strtotime ($alert['info'][0]['onset']);
                                if ($future < $from_o)                                  { $cnts['future']++; continue;}    // UK to early warnings
                                $from_e = strtotime ($alert['info'][0]['effective']);
                                $from_l = strtotime ($alert['info'][0]['expires']);
                                if ($now > $from_l + $test)                             { $cnts['to-old']++; continue;}    // old expires already passed
                                if ($from_e > ($now + 1*24*3600) )                      { $cnts['future']++; continue;}    // valid only after 48 hours
                                foreach ($alert['info'] as $key => $info)
                                     {  if (!array_key_exists('area',$info) )           { $cnts['missing']++; continue;}
                                        $areas  = $info['area'];
                                        if (!is_array($areas)) {$areas[0]=$areas;}
                                        $forus  = array(); 
                                        foreach ($areas as $area)
                                             {  if (! array_key_exists('geocode',$area)) {continue;}
                                                $geocodes = $area['geocode'];  
                                                if (!is_array($geocodes) ) {$geocodes[0]=$geocode; }
                                                foreach ($geocodes as $geocode)
                                                     {  if (!array_key_exists ('value',$geocode)) {continue;}
                                                        $value  = $geocode['value'];
                                                        if (!is_string($value) )        {continue;}
                                                        if (in_array($value,$alarm_areas) )
                                                             {  $forus[]= $value.'|'.$area['areaDesc'].'|';}
                                                        } // eo each geocode
                                                } // eo each area
                                        if (count($forus) == 0)                         { $cnts['not-forus']++; continue;}  // not for one of our areas
                                        unset ($info['area']);
                                        $lng    = $info['language'];
                                        $info['forus']  = $forus;
                                        $warns[$id_alrt][$lng]  = $info; #echo __LINE__.print_r($warning,true);  exit;
                                        $cnts['forus']++; 
                                        continue;
                                        }  // eo alert info
                                } // eo each alert
                        } // eo each warning
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') warnings: '.$cnts['total'].' used: '.$cnts['forus'].' others: '.$cnts['not-forus'].
                                ' future: '.$cnts['future'].' to-old: '.$cnts['to-old'].' Errors - no-array: '.$cnts['no-array'].' missing: '.$cnts['missing'].PHP_EOL;
                unset ($array);
                }  // eo each country
        $return = file_put_contents ($warn_cache,serialize ($warns));     
        } // eo all countries 
else {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') warnings loaded from: '.$warn_cache.PHP_EOL; 
        $warns          = unserialize (file_get_contents($warn_cache));}
#
#                                       echo '<pre>'.__LINE__.PHP_EOL.$stck_lst.print_r($warns,true); exit;
$max_color      = 0;
$table          = '';
$link           = '';
$rows           = 3;
$cln_vnt        = '';
$nr = $wrnng    = 0;
$languages      = array();
$count          = 0;
if (count ($warns) == 0)  {return false;}
foreach ($warns as $warncode => $warn)
     {  $languages      = array();     
# get descriptive texts for each language
        foreach ($warn as $lng => $arr) 
             {  $description = $headline = $instruction = '';
                if (array_key_exists('description',$arr)) {$description = $arr['description'];}
                if (array_key_exists('headline',$arr) )   {$headline    = $arr['headline'];}
                if (array_key_exists('instruction',$arr)) {$instruction = $arr['instruction'];}
                if (array_key_exists('event',$arr))       {$event       = $arr['event'];}
                $languages[$lng] = array ( 
                        'description' => $description, 'headline' => $headline, 
                        'instruction' => $instruction, 'event'    => $event);
                 } // eo language - texts 
        reset ($warn);                  # echo '<pre>'.__LINE__.PHP_EOL.$stck_lst.print_r($languages,true); # exit;
# get more from first item       
        foreach ($warn as $lng => $arr) {break;}
        $level  = $type = ''; 
        foreach ($arr['parameter'] as $check)
             {  if     ($check['valueName'] == 'awareness_level') 
                     {  $level  = $check['value'];}     # awareness_level: 1; green; Minor
                elseif ($check['valueName'] == 'awareness_type') 
                     {  $type   = $check['value'];}     # awareness_type: 12; flooding
                }   # echo '<pre> '.__LINE__.' $lng='.$lng.' $level ='.$level .' $type='.$type.PHP_EOL; exit;
# severity level
        list ($nr_l,$color,$text) = explode (';',$level.';;;');
        $level = $nr_l  = (int) trim($nr_l);
        if ($level < 2 && isset ($skip_lvl1) && $skip_lvl1 == true) {continue;}
        $count++;
        $cln_vnt        = '';   
        if ($level < 2 || $level > 4) {$level = 2;}
        $severity       = $warnlevels[$level];
        if ($level > $max_color) 
             {  $max_color = $level;} 
# type of warning
        list ($nr_e,$event)       = explode (';',$type.';;');  
        $nr_e   = (int) trim($nr_e);
        $event  = trim($event);  
        $img_nr = $nr_e.$nr_l;    
        $image_def = str_replace ('##',$img_nr,$image_url); #echo '<!-- '.$type.' '.$event.' '.$img_nr.' '. $image_def.' -->'.PHP_EOL;
        if (!file_exists($image_def) )
             {  $image_def = str_replace ('##','000',$image_url);} 
        $arr['onset']     = strtotime ($arr['onset']);
        $arr['effective'] = strtotime ($arr['effective']);
        $arr['expires']   = strtotime ($arr['expires']);
        $ymd_frm= date ($dateFormat.' ',$arr['onset']);   #echo '<pre>'.__LINE__.PHP_EOL.$stck_lst.' 
        $ymd_to = date ($dateFormat.' ',$arr['expires']);
        if ($ymd_frm == $ymd_to) {$ymd_to = '';}
        $total_time     = '<b>'.lang('Valid').':&nbsp;</b>'
                          .$ymd_frm. date ($timeFormatShort,$arr['onset'])
                          .'&nbsp;&nbsp;-&nbsp;&nbsp;'
                          .$ymd_to. date ($timeFormatShort,$arr['expires']);
        if ($cln_vnt == '')
             {  $cln_vnt = $warntypes[$nr_e]. ' warning' ; }   # 2023-04-23  echo '<-- '      # ucfirst (trim (str_replace ($colors,'',$arr['event'])));}
        foreach ($arr['forus'] as $region)
             {  list ($geocode,$areaDesc) = explode ('|',$region.'||');
                $table  .= '<tr style="background-color: '.$warncolors[$level].'">'.PHP_EOL; 
                $table  .= '<td colspan="2">'
                        .'<span style="margin-left: 5px; float: left;"><b>'
                        .lang($cln_vnt).'&nbsp;&nbsp;&nbsp;'
                        .$areaDesc.'</b> <small>('.$geocode.')</small></span>'
                        .'<span style="float: right; margin-right:5px;">'
                        .$total_time      
                        .'</span></td>'.PHP_EOL.'</tr>'.PHP_EOL;
                $total_time = '&nbsp;';}
        $table  .= '<tr style="background-color: '.$warncolors[$level].'">'.PHP_EOL;  
        $table  .= '<td style="vertical-align: top;">';
        $table  .= '<img src="'.$image_def.'" style="margin: 4px; max-width: 128px; " alt="'.$image_def.'" title="'.$image_def.'"></td>'.PHP_EOL;
        $table  .= '<td style="text-align: left;">';
        $table  .= '
<span class="tab" style="">'.PHP_EOL;
        $other  = $start = '';
        $display= 'block';
        $active = 'active';
        $margin = 'margin-left: 20px;';
        $wrnng++;  
        $from = $to = '';  
        if ( array_key_exists ('web',$arr) )
                     {  $from   = trim($arr['web']);
                        $to     = '<a href="'.$from.'" target="_blank">'.$from.'</a>';} #echo __LINE__.print_r($arr,true).PHP_EOL.$from.PHP_EOL.$to; exit;
        foreach ($languages as $language =>  $text)
             {  $nr++;
                $lngtxt = $language; # echo __LINE__.print_r($text,true); exit;
                $lngshrt= substr ($language,0,2);
                if      (array_key_exists ($language,$lang_warn) ) {$lngtxt = $lang_warn[$language];}
                elseif  (array_key_exists ($lngshrt, $lang_warn) ) {$lngtxt = $lang_warn[$lngshrt];}
                $start  .= '<label class="t'.$wrnng.'tablinks tablinks '.$active.'"  style="'.$margin.'" onclick="openTab(event,\'t'.$wrnng.'\', \'t'.$wrnng.'-'.$nr.'\')" id="'.$wrnng.$language.'">&nbsp;'.$lngtxt.'&nbsp;</label> '.PHP_EOL;
                $margin = '';
                $other  .= '<span id="t'.$wrnng.'-'.$nr.'" class="t'.$wrnng.'tabcontent tabcontent" style="clear: left; display: '.$display.';">'.PHP_EOL;
                $display= 'none';
                $active = '';
                $hdln1  = trim($text['headline']);
                $hdln2  = trim($text['description']);
                $hdln2  = str_replace($from,$to,$hdln2); 
                if ($hdln2 == $hdln1) {$hdln2 = '';} 
                if ($hdln1 <> '' && $hdln2 <> '') {$hdln1 .= '<br />';}
                if ($hdln1.$hdln2 <> '')
                     {  $other  .= '<b style="text-align: center; width: 100%; display: block;">'.$hdln1.'</b>'.PHP_EOL.'<br />'.PHP_EOL;}
                
                $txtbr  = str_replace(PHP_EOL,'<br />', $hdln2);
                $other  .= $txtbr.'<br />'.PHP_EOL;
                $instruction = $text['instruction'];
                $instruction  = str_replace($from,$to,$instruction);
                if ($instruction <> '' )
                     {  $other  .= '<br />'.$instruction.'<br />'.PHP_EOL;  }
                $other  .= '</span>'.PHP_EOL;
                }  // eo e texts
#
        if (count ($languages) < 2)
             {  $start = '';}
        $table  .= $start.$other;  
        $table  .= '</span></td>'.PHP_EOL;
        $table  .= '</tr>'.PHP_EOL;  
        $table  .= '<tr style="background-color: transparent; height: 10px;"><td colspan="2" style="font-size: 8px;" title="'.$warncode.'">'
                        .'<a href="PWS_frame_text.php?type=url&showtext=https://hub.meteoalarm.org/warnings/feeds-'.$warncode.'" target="_blank"><hr style="border-color: white;" /></a>'
                        .'</td></tr>'.PHP_EOL; 
                   
        } // eo for each warning;  
if ($count == 0) {return false;}   # 2023-03-12
$table  = str_replace('Â°','°',$table);
$table  = '<table style="width: 100%; border-collapse: collapse; margin-top: 8px; " >
'.$table.'
</table>'.$legal.'<hr style="border-color: white;" />';
$icon   =  '<svg style="vertical-align: bottom;" id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>';      
if ($count > 1) 
     {  $text =  lang('Multiple warnings') ;}
else {  $text =  lang($cln_vnt);}       # 2023-03-10
$wrnStrings    = '<div style="text-align: center; position: absolute;top: 18px;  width: 100%; height: 60px;  font-size: 12px; background-color: '.$warncolors[$max_color].';">
<div style="color: black;   margin-top: 4px;"><b>MeteoAlarm</b><br />'.$text.'<br />';
$wrnHref 	= '<a href="./wrnPopupWarnings.php?lang='.$user_lang.'" data-featherlight="iframe">'; # 2023-05-07
$wrnStrings    .= $wrnHref.$icon.'
</a>
</div>
</div>'; 
#
$ownpagehtml = '<script>
if(typeof document.createStyleSheet === "undefined") {
    document.createStyleSheet = (function() {
        function createStyleSheet(href) {
            if(typeof href !== "undefined") {
                var element = document.createElement("link");
                element.type = "text/css";
                element.rel = "stylesheet";
                element.href = href;}
            else {
                var element = document.createElement("style");
                element.type = "text/css";}
            document.getElementsByTagName("head")[0].appendChild(element);
            var sheet = document.styleSheets[document.styleSheets.length - 1];
            if(typeof sheet.addRule === "undefined")
               { sheet.addRule = addRule;}
            if(typeof sheet.removeRule === "undefined")
               { sheet.removeRule = sheet.deleteRule;}
            return sheet;
        }
        function addRule(selectorText, cssText, index) {
            if(typeof index === "undefined") { index = this.cssRules.length;}
            this.insertRule(selectorText + " {" + cssText + "}", index);
        }
        return createStyleSheet;
    })();
}
var sheet = document.createStyleSheet();
sheet.addRule(".tab","overflow: hidden; display: block; border: 0px solid #ccc; background-color: white; text-align: left; margin:  4px; margin-left: 0px;");
sheet.addRule(".tab span","text-align: left; margin:  4px;");
sheet.addRule(".tab label","float: left; border-radius: 4px; background-color: #ccc; border: 1px solid #ddd; cursor: pointer; margin: 3px; margin-bottom: 0px; padding: 3px; ");
sheet.addRule(".tab label:hover","background-color: white;");
sheet.addRule(".tab label.active","border-bottom-right-radius: 0px; border-bottom-left-radius: 0px; background-color: transparent; border: 1px solid black; border-bottom: 1px solid white;");
sheet.addRule(".tabcontent","display: none; border-top: none;");
sheet.addRule(".tab a"," text-decoration: underline; color:blue;");
function openTab(evt, block,  spanName) {
  var i, tabcontent, tablinks;
  var clssnm    = block+"tabcontent";
  tabcontent = document.getElementsByClassName(clssnm);
  
  for (i = 0; i < tabcontent.length; i++) 
   {tabcontent[i].style.display = "none";}
   
  
  tablinks = document.getElementsByClassName(block+"tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(spanName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
'.$table; 
return true; 

function warn_curl()
     {  global $warn_url, $stck_lst, $result, $test_cap, $fl_to_load;
        $result = '';
        $timeout        = 20;  # 2023-02-28 
        if ( isset ($test_cap) && strlen($test_cap) > 2 )
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') test_file '.$test_cap.' is used'.PHP_EOL; 
                $result = file_get_contents ($test_cap);
                return true;}
        $start_time     =  microtime(true);
        $ch             = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$warn_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);    // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);     // data timeout 
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0');
        $result         = curl_exec ($ch);
        $info	        = curl_getinfo($ch);
        $error          = curl_error($ch);
        curl_close ($ch);
        $end            = microtime(true);
        $passed         = $end - $start_time;
        if ($passed < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($passed,4);}
        $CHECK_HTTP_CODES = array ('404', '429','502', '500');
        if (in_array ($info['http_code'],$CHECK_HTTP_CODES) ) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' - PROBLEM => http_code: '.$info['http_code'].', no valid data '.$warn_url.PHP_EOL;
                $stck_lst .= basename(__FILE__).' ('.__LINE__.') url used'.$warn_url.PHP_EOL;
                return false;} 
        if ($error <> '')
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  invalid CURL '.$error.' '.$warn_url.PHP_EOL; 
                return false;}
        else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  CURL OK for '.$warn_url.PHP_EOL; }
        return true;        
} // eof warn_curl
