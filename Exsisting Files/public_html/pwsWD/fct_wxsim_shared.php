<?php $scrpt_vrsn_dt  = 'fct_wxsim_shared.php|01|2020-11-02|';  # release 2012_lts
#
# -----------     location of the plaintext file
#    =====  for this script ======
$plaintextFile  = '../plaintext.txt';           // one folder below the pwsWD/ folder => often the root.
#   ======  then line 133 of 
#   ======  wxsimPP/plaintext-parser.php  
#   ======  is correct
#   ======  $plaintextFile = '../../plaintext.txt';  // two folders below the wxsimPP folder 
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
#
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# ------------------------- settings this script
$cacheFile      = __DIR__.'/jsondata/wxsimpp_'.substr($used_lang,0,2).'.arr'; // language specific
$loadCache      = true;  
# ------------ when testing always discard cache
if (isset ($_REQUEST['test']) )         
     {  $loadCache  = false; }
# ---------------- try to find the plaintext.txt
elseif (file_exists($plaintextFile) === false)  
     {  echo '<b style="color: red;"><small>Problem ('.__LINE__.'): wxsim file not found in '.__FILE__.'</small></b><br />';     
#
# but maybe the parser is modifed correctly, just try the parser
        $loadCache  = false; } 
# ----------------------check if the cache exist
elseif (file_exists($cacheFile) == false)   
     {  $loadCache  = false; }
# -----------  check if new WXSIM upload arrived
elseif (filemtime ($cacheFile) < filemtime ($plaintextFile)) 
     {  $loadCache  = false;}
#
if ($loadCache === true)
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') loading from '.$cacheFile.PHP_EOL;
        $arr_pp = unserialize(file_get_contents($cacheFile));
        return;}
#
# we want to run the original plaintext paser script from Ken True
# but we have to save same-name variables first
$time_format_s  = $timeFormat;  
$lang_s         = $lang; // complete language translation array needs to be saved !
$notAvail       = '<b style="color: red;"><small>Problem ('.__LINE__.'): wxsim file not found in wxsimPP/plaintext-parser.php</small></b><br />';
#
# ---------init variables used by parser
$doPrint        = false;  // we will print ourselves
$lang           = '';
unset ($_REQUEST['lang']);  
$SITE['defaultlang']    = substr($used_lang,0,2); 
#
if (file_exists($plaintextFile) === true) 
     {  $filetimeWxsim  = filemtime ($plaintextFile);
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') loading from '.$plaintextFile.PHP_EOL;}
else {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') file not found at '.$plaintextFile.PHP_EOL;
        $filetimeWxsim  = 0;}
#
# move to wxsim parser folder and run parser
chdir('wxsimPP');                       // set directory pointer to parser folder
include 'plaintext-parser.php';
chdir ('../');                          // restore pointer to this scripts folder
if (isset ($Status) )
     {  $from = array ('<!--','-->');
        $stck_lst .= str_replace ($from,'',$Status);}  #echo  $stck_lst; exit;
#
# ----------  restore modified variables        
$timeFormat     = $time_format_s;
$lang           = $lang_s;  
date_default_timezone_set($TZ); // reset timezone back to correct value if parser not correctly set
# ------- check if parser did do its job
if (!isset ($WXSIMday)) 
     {  return false;}
# assemble array to be processed by dashboard scripts
$arr_pp         = array();              // here we store the wxsim data
$cnt            = count($WXSIMday);     // nr of day-parts found
$arrLookupWX_DS = array (               //  icon codes to svg icon translation array
'tsra'	=>	'ovc_thun_dark',	'ntsra'	=>	'ovc_thun_dark',
'nsvrtsra'=>	'tornado',
'scttsra'=>	'ovc_thun_rain_dark',   'nscttsra'=>	'ovc_thun_rain_dark',
'raip'	=>	'ovc_sleet',	        'nraip'=> 	'ovc_sleet_dark',
'rasn'	=>	'ovc_sleet',            'nrasn'	=>	'ovc_sleet_dark',
'fzra'	=>  	'ovc_sleet',	        'nfzra' => 	'ovc_sleet_dark',
'ra'	=>	'mc_rain',	        'nra'	=>	'ovc_rain_dark',	
'rasn'	=>	'mc_flurries',          'nrasn'	=>	'ovc_sleet_dark',
'ip'	=>	'ovc_flurries',	        'nip'	=>  	'ovc_sleet_dark',	
'sn'	=>	'ovc_flurries',         'nsn'	=>	'ovc_flurries_dark',
'sctfg'	=>	'mc_fog',	        'nbknfg'=>	'mc_fog_dark',
'fg'	=>	'mc_fog',	        'nfg'	=>	'mc_fog_dark',	
'sct'	=>	'pc_day',	        'nsct'	=>	'mc_night',	
'bkn'	=>	'pc_day', 	        'nbkn'	=>	'mc_night',	
'cloudy'=>	'ovc', 	                'ncloudy'=>	'ovc_dark',
'ovc'	=>	'ovc',	                'novc'	=>	'ovc_dark',
'few'	=>	'few_day',	        'nfew'	=>	'few_night',
'skc'	=>	'clear_day', 	        'nskc'	=>	'clear_night', );
$arr_pp[0]['city']      = $WXSIMcity;   // store the general information in first daypart
$arr_pp[0]['station']   = $WXSIMstation;
$arr_pp[0]['updated']   = $WXSIMupdated;
#
# for testing  $filetimeWxsim = false;
if ( (int) $filetimeWxsim < 1)  
     {  echo '<!-- $filetimeWxsim == < 1 | $WXSIMupdated ='.$WXSIMupdated.' | ';
        $filetimeWxsim = strtotime ($WXSIMupdated);
        echo ' $filetimeWxsim reset to '.$filetimeWxsim.' => '.date('c',$filetimeWxsim).' -->'.PHP_EOL;} 
# e.o. testing $filetimeWxsim = false;
#
# ----------------- loop to process each daypart
for ($i=0; $i < $cnt; $i++)
     {  if (isset ($WXSIMday[$i]) )     { $arr_pp[$i]['part'] = $WXSIMday[$i];}      else {$arr_pp[$i]['part']= '';}
        if (isset ($WXSIMtext[$i]) )    { $arr_pp[$i]['text'] = $WXSIMtext[$i];}     else {$arr_pp[$i]['text']= '';}
        if (isset ($WXSIMuv[$i]) )      { $arr_pp[$i]['uvuv'] = $WXSIMuv[$i];}       else {$arr_pp[$i]['uvuv']= '';}
        if (isset ($WXSIMtemp[$i]) )    
             { $arr_pp[$i]['tmps']      = $WXSIMtemp[$i];
# clean temp from formatting
                $string                 = $arr_pp[$i]['tmps'];  // <span style="color: red">High: 4&deg;</span> <span style="color: blue">Minimum 2&deg;</span>
                $string2= str_replace('<span style="color: red">', '',$string);
                if ($string2 <> $string) 
                     {  $arr_pp[$i]['tphl']  = 'red';}
                else {  $string2= str_replace('<span style="color: blue">', '', $string);
                        $arr_pp[$i]['tphl']  = 'blue';}     
                list ($string3)         = explode ('&deg;',$string2);  //  High: 4&deg;</span>  Minimum 2&deg;</span>
                list ($no,$temp)        = explode (' ',$string3);       // High: 4  Minimum 2
                 $arr_pp[$i]['temp']     = trim($temp); }
# eo clean temp                         
        else {  $arr_pp[$i]['tmps'] = $arr_pp[$i]['temp'] = $arr_pp[$i]['tphl'] = '';}         
        if (isset ($WXSIMpop[$i]) )     { $arr_pp[$i]['popp'] = $WXSIMpop[$i];}      else {$arr_pp[$i]['popp']= '';}
        if (isset ($WXSIMprecip[$i]) )  
# eo clean rain
             {  $string = $WXSIMprecip[$i];
                $arr_pp[$i]['rstr'] = $string;
          #      $string = str_replace ('.','',$string);
                list ($value,$unit) = explode(' ',$string.' ');
                $arr_pp[$i]['rain'] = $value;
                list ($unit)        = explode ('<',$unit.'<');
                $unit = str_replace ('.','',$unit);
                $arr_pp[$i]['runt'] = $unit;}    
        else {$arr_pp[$i]['rain']= '';}
# eo clean rain
        if (isset ($WXSIMicon[$i]) )    { $arr_pp[$i]['icon'] = $WXSIMicon[$i];}     else {$arr_pp[$i]['icon']= '';}
# clean icon     
        $from   = $arr_pp[$i]['popp'];
        $string = str_replace ($from,'',$arr_pp[$i]['icon']);
        list ($icon,$none) =    explode ('.',$string);
        $arr_pp[$i]['icnc']= $icon;
        if (isset($arrLookupWX_DS[$icon]) )
             {  $arr_pp[$i]['icDS']     ='pws_icons/'. $arrLookupWX_DS[$icon].'.svg'; }
        else {  $arr_pp[$i]['icDS']     = 'pws_icons/unknown.svg';}
        
# eo clean icon
        if (isset ($WXSIMcond[$i]) )    { $arr_pp[$i]['cond'] = $WXSIMcond[$i];}     else {$arr_pp[$i]['cond']= '';}

        if (isset ($WXSIMwinddir[$i]) ) { $arr_pp[$i]['wdir'] = $WXSIMwinddir[$i];}  else {$arr_pp[$i]['wdir']= '';}
        if (isset ($WXSIMwinddiricon[$i]) ) { $arr_pp[$i]['idir'] = $WXSIMwinddiricon[$i];}  else {$arr_pp[$i]['idir']= '';}

        if (isset ($WXSIMwind[$i]) )      
# clean wind  17-28&rarr;10
              { $wind   = $WXSIMwind[$i];
                list ($wind, $no)       = explode ('&rarr;',    $wind.'&rarr;');
                list ($wind, $no)       = explode ('-',         $wind.'-');
                $arr_pp[$i]['wspd']     = $wind;  }     
# eo clean wind       
        else {$arr_pp[$i]['wspd']= '';}
        if (isset ($WXSIMgust[$i]) )    { $arr_pp[$i]['gust'] = $WXSIMgust[$i];}     else {$arr_pp[$i]['gust']= '';}
        if (isset ($WXSIMwindunits[$i])){ $arr_pp[$i]['wunt'] = $WXSIMwindunits[$i];}else {$arr_pp[$i]['wunt']= '';}
        if (isset ($WXSIMBeaufort[$i]) ){ $arr_pp[$i]['wbft'] = $WXSIMBeaufort[$i];} else {$arr_pp[$i]['wbft']= '';}
        if (isset ($WXSIMfrost[$i]) )   { $arr_pp[$i]['frst'] = $WXSIMfrost[$i];}    else {$arr_pp[$i]['frst']= '';}
        if (isset ($WXSIMhumidex[$i]) ) { $value  = trim($WXSIMhumidex[$i]); } else {$value  = '';}
        if ($value <> '') // <span style="color: red;">Hmdx: 20&deg;</span>
             {  list ($none,$string) = explode ('">',$value); #echo $value.' -> '.PHP_EOL.$string; exit;
                list ($none,$string) = explode (' ',$string); # echo $string; exit;
                list ($value,$none)  = explode ('&deg;',$string); # echo $value; exit;
                $value  = trim($value);}
        $arr_pp[$i]['hmdx']     = $value;
        if (isset ($WXSIMheatidx[$i]) ) { $value  = trim($WXSIMheatidx[$i]); } else {$value  = '';}
        if ($value <> '') // <span style="color: red;">Heat: 27&deg;</span>
             {  list ($none,$string) = explode ('">',$value); #echo $value.' -> '.PHP_EOL.$string; exit;
                list ($none,$string) = explode (' ',$string); # echo $string; exit;
                list ($value,$none)  = explode ('&deg;',$string); # echo $value; exit;
                $value  = trim($value);}
        $arr_pp[$i]['heat']= $value;
        if (isset ($WXSIMwindchill[$i]) ) { $value  = trim($WXSIMwindchill[$i]); } else {$value  = '';}
        if ($value <> '') // <span style="color: blue;">WCh: 27&deg;</span>
             {  list ($none,$string) = explode ('">',$value); #echo $value.' -> '.PHP_EOL.$string; exit;
                list ($none,$string) = explode (' ',$string); # echo $string; exit;
                list ($value,$none)  = explode ('&deg;',$string); # echo $value; exit;
                $value  = trim($value);}
        $arr_pp[$i]['chll']= $value;           
#       if (isset ($WXSIMtempdirect[$i]) ) { $arr_pp[$i]['tmpx'] = $WXSIMtempdirect[$i];}  else {$arr_pp[$i]['tmpx']= '';}
#       if (isset ($WXSIMtitles[$i]))   { $arr_pp[$i]['ttle'] = $WXSIMtitles[$i];}else {$arr_pp[$i]['ttle']= '';}
#
    } // eo loop each day part
#
$error = file_put_contents($cacheFile, serialize($arr_pp));      // save data to reuse
if ($error == false)
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') ERROR data could not be saved to '.$cacheFile.PHP_EOL;}
else {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') data saved to '.$cacheFile.PHP_EOL;}
#        
#echo '<pre>'.print_r($arr_pp,true);exit; #for testing