<?php  $scrpt_vrsn_dt  = 'wrnWarningEU-RSS.php|01|2023-04-19|';  # version 2  RSS feed # beta - release 2012_lts
#
# Display a list of warnings from  metealarm.eu 
# used in Advisory box top left  
# https://feeds.meteoalarm.org/ 
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).'('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#  ------------------------------------ settings 
$cache_max_age  = 900;
$detail_page    = true;
$detail_page_url= './index.php?frame=weatheralarms';
#
#  --------------------------------- test values 
#$alarm_area     = 'NL018'; #LT001,LT002,LT003';
#$cache_max_age  = 9000;
#$now            = $now - 3*24*3600;
#  --------------------------------- test values 
$alarm_areas    = explode (',',$alarm_area); #echo __LINE__.print_r($alarm_areas,true); exit;
$check_color    = 'https://feeds.meteoalarm.org/images/rss/wflag-l';
$check_length   = strlen($check_color);
# --------------include warnings on every page ?
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
                        'ME'  => 'montenegro',  'MK'  => 'north-macedonia',                     'MT'  => 'malta',       'NL'  => 'netherlands', 
                        'NO' => 'norway',       'PL'  => 'poland',      'PT'  => 'portugal',    'RO'  => 'romania',     'RS'  => 'serbia',      
                        'SE'  => 'sweden',      'SI'  => 'slovenia',    'SK' => 'slovakia',     'UK' => 'united-kingdom');
#
# / country ??
#
$country        = substr($alarm_areas[0],0,2);
$warn_cache     = './jsondata/warning2EU_'.$country;   #echo $warn_cache; exit;                          
$warn_url	= 'https://feeds.meteoalarm.org/feeds/meteoalarm-legacy-rss-'.$countries[$country]; # echo $warn_url; exit;
$fl_to_load     = $country.'_warnings'; 
$items          = array();
#
if (is_file($warn_cache) )
     {  $cache_age      = $now  - filemtime($warn_cache);}
else {  $cache_age      = $now;}
#
# ----------------- cache valid ?
#
$doc    = new DOMDocument('1.0', 'utf-8');
#
if (array_key_exists('force',$_REQUEST) && trim($_REQUEST['force']) == 'alarm') { $cache_age = $now;}
#
if ($cache_age > $cache_max_age) 
     {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') no / old cache. load   =>'.$warn_url.PHP_EOL; 
        $doc->load($warn_url);
        $stck_lst      .= basename(__FILE__).'('.__LINE__.') cache =>'.$warn_cache.' saved'.PHP_EOL; 
        $doc->save($warn_cache);}
else {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') cache loaded   =>'.$warn_cache.' , age ='.$cache_age.' seconds'.PHP_EOL;
        $doc->load($warn_cache);}

foreach ($doc->getElementsByTagName('item') as $node) 
     {  $title  = $node->getElementsByTagName('title')->item(0)->nodeValue;
        $text   = $node->getElementsByTagName('description')->item(0)->nodeValue;
        $link   = $node->getElementsByTagName('link')->item(0)->nodeValue;
        $found  = false;  
        foreach ($alarm_areas as $area)
             {  if (strpos ($link, $area) == false) {continue;}
                $found= true;
                }
        if ($found == false) {continue;}
        $found  = $color  = $warn = $multi = '0';
        while ($found < strlen($text))
             {  $found  = strpos($text, $check_color, $found);  #echo $found; exit;
                if ($found == false)
                     {  break;}
                $multi++;
                $found  = $found + $check_length;
                if ($color  < substr ($text,$found,1) )
                     {  $color  = substr ($text,$found,1);
                        $found  = $found + 3;
                        $warn   = substr ($text,$found,2);
                        $warn   = str_replace('.','',$warn); }
                #echo $found; exit;
                }
        $check='<b>From:';
        $first  = strpos($text, $check);
        $first  = $first+6;
        $items[]= array ('title' => $title, 'color' => $color, 'multi' => $multi, 'warn' => $warn, 'text' => $text, 'link' => $link, 'area' => $alarm_area);
        }
        
# --------------- Any warnings ?
$count  = count ($items);
if ($count < 1)
     {  return false;}   #echo '<pre>'.__LINE__.' '.$count.print_r($items,true); exit;
if ($count > 1) 
     {  $text =  lang('Multiple warnings') ;}
elseif ((int) $items[0]['multi'] > 1)
     {  $text =  lang('Multiple warnings') ;}
elseif ($items[0]['warn'] <> '0')
     {  $warns  = array('none_0',            
                        'wind',            'snow-ice',     'thunderstorm', 'fog',        'high-temperature',
                        'low-temperature', 'coastalevent', 'forest-fire]', 'avalanches', 'rain',
                        'none-11',         'flooding',     'rain-flood');    
        $nr     = $items[0]['warn'];
        $text   = lang($warns[$warn].' warning');}
else {  $text   =  lang('Notifications');}

$color  = '0';
foreach ($items as $item)
     {  if ($item['color'] > $color) { $color = $item['color'];}
        continue;} 
#
$warncolors     = array();
$warncolors[0]  = 'transparent';               
$warncolors[1]  = 'transparent';  
$warncolors[2]  = '#FBEA55'; 
$warncolors[3]  = '#F19E39'; 
$warncolors[4]  = '#BB2739';   #echo '<pre>'.print_r($items,true); exit;
#

$icon   =  '<svg style="vertical-align: bottom;" id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>';      
$wrnStrings    = '<div style="background-color: '.$warncolors[$color].'; text-align: center; position: absolute;top: 18px;  width: 100%; height: 60px;  font-size: 12px; ">
<div style="color: black;  margin-top: 4px;"><b>MeteoAlarm</b><br />'.$text.'<br />';
$wrnHref 	= '<a href="'.$detail_page_url.'">';
$wrnStrings    .= $wrnHref.$icon.'
</a>
</div>
</div>'; 


# ------------------------------
$from   = array ('Â°','<img border="1" ','<th colspan="3" align="left">','<tr><td width="28">', PHP_EOL, '><', 'From: ', 'Until: ','Today', 'Tomorrow', 'border="0" cellspacing="0" cellpadding="3"','. ');
$to     = array ('°' ,'<img style="border: 1px solid black;" ',' <th colspan="2">','<tr><td colspan="2"><hr></td></tr><tr><td>', '<br /><br />'.PHP_EOL, '>'.PHP_EOL.'<', lang('From').':',lang('Until').':',lang('Today'), lang('Tomorrow') ,' style="margin: 0 auto;" ', '. <br />');
$line   = '';
if (count($items) > 0) {$ownpagehtml = '';}
$from2  = array();
$to2    = array();
for ($n = 0; $n < count($items) ; $n++) #foreach ($items as $item)
     {  $item   = $items[$n];     # print_r($item); exit;
        $text   = $item['text'];
        $tomrrw = 1;
        while ($tomrrw <> false) 
             {  $tomrrw = strpos ($text, 'Tomorrow',$tomrrw);
                if ($tomrrw == true)
                     {  $empty  = strpos ($text, '</table>',$tomrrw) - $tomrrw;
                        if ($empty < 19) 
                             {  $text = str_replace ('<br />Tomorrow','',$text);
                                $tomrrw = false;}
                        else {  $tomrrw = $tomrrw + 20;}
                        }
                else {  $tomrrw = false;}# echo $tomrrw; 
                }
        $dt_fr  = 1;
        while($dt_fr <> false)
             {  $dt_fr  = strpos ($text, 'From: ', $dt_fr);
                if ($dt_fr <> false)
                     {  $dt_fr  = $dt_fr + 13;
                        $dt_nt  = strpos ($text, 'Until: ',$dt_fr )+ 14;
                        $date1  = substr($text,$dt_fr,25); 
                        $date2  = substr($text,$dt_nt,25); 
                        $dateA  = date ($dateFormat.' '.$timeFormatShort.' T' ,strtotime($date1));
                        $dateB  = date ($dateFormat.' '.$timeFormatShort.' T' ,strtotime($date2)); # echo $nt_nw.' '.$nd_nt; exit;
                        $from2[]= $date1;
                        $from2[]= $date2;
                        $to2[]  = $dateA;
                        $to2[]  = $dateB;
                        $dt_fr  = $dt_nt;  
                        }  
                }
        $text   = str_replace ($from, $to, $text); 
        $text   = str_replace ($from2,$to2,$text);# echo $item['title'].print_r($from2,true).print_r($to2,true);
        $items[$n]['text'] = $text;
        $ownpagehtml .=  '<div style="background-color: silver; ">
<h4 style=" margin: 0 auto; padding: 4px;">'.$item['title'].'</h4>'.PHP_EOL.$text.'</div>'.$line;
        $line   = '<hr style="height: 4px; background-color: silver; border-color: white;" />';
        }
$ownpagehtml .= '<div style="width: 100%; height: 6px;background-color: silver; margin: 0;"><hr style="margin: 0; background-color: silver; " /></div>'.$legal;
     #   echo $ownpagehtml;
return true; 

        
        
        
        