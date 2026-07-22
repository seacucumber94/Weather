<?php   $scrpt_vrsn_dt  = 'PWS_extra_data.php|01|2023-07-14|';  # dec-point = comma problem + \n windows + MB-dt + am pm processing wdtd + trim line | release 2012_lts
#
$wd_sep = '/';
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
#-----------------------------------------------
if (!isset ($stck_lst) )  {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#-----------------------------------------------
#                test if convert script is used
$check  = substr ($extra_data,-4); #echo __LINE__.$check; exit;
if (strtolower ($check) == '.php')
     {  $string = include $extra_data; }
else {  $string = file_get_contents($extra_data);}
$weather['loaded_from'].= PHP_EOL.$extra_data;
$arr    = explode ("\n",$string);  # echo '<pre>'.print_r($arr,true); exit;
unset ($string);
$fromrain       = $torain = $weather['rain_units'];
$fromtemp       = $totemp = $weather['temp_units'];
$clean_from     = array ('_am','_pm',' am',' pm','  ','  ');
$clean_to       = array ('am','pm','am','pm',' ',' ');

#
foreach ($arr as $string)
     {  $string = trim($string);
        $skip   = substr($string,0,1);
        if ($skip == '#' || $skip <> '|') {continue;}
        list ($none, $key, $type, $value) = explode ('|',$string.'||||');
        $key    = trim ($key);
        $type   = trim ($type);
        $value  = trim ($value);
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') processing: '.$string.PHP_EOL;
        if (strlen ($key) < 3 ) {continue;} // skip unknown data
        switch ($type) {  
            case 'uom':     
                $$key   = $value; 
                break;
            case 'rain':
                $weather[$key]  = convert_precip ($value,$fromrain,$torain); 	
                break;
            case 'temp':
                $weather[$key]  = convert_temp   ($value,$fromtemp,$totemp);
                break;
            case 'light':
                $weather[$key]  = (float) $value;
                if ($key == 'lightningkm')   
                     {  $weather['lightningmi']         = round ( (float) $value / 0.621371 );}
                if ($key== 'lightningtime') 
                     {  $weather['lightningtimeago']    = time() - (int) $value;}
                break;
            case 'cudt':
                if (array_key_exists ('wd_sep',$weather)) {$wd_sep = $weather['wd_sep'];}
                list ($wddate, $wdtime) = explode (' ', $value.' ');  #17/12/2020 18:37:33
                list ($hrs,$mns,$scs)   = explode (':', $wdtime.'::');
                list ($y , $m, $d)      = explode (' ', $liveYMD.'  ');
                list ($a , $b, $c)      = explode ($wd_sep, $wddate.$wd_sep.$wd_sep);
                $yr     = $$y;
                $mnth   = $$m;
                $dy     = $$d;
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') calculated Y-M-D-H-M-S =  '.$yr.'-'.$mnth.'-'.$dy.'-'.$hrs.'-'.$mns.'-'.$scs.PHP_EOL;
                $weather[$key]  = mktime ($hrs,$mns,$scs,$mnth,$dy,$yr);
                if ($key== 'lightningtime') 
                     {  $weather['lightningtimeago']    = time() - $weather[$key];}
                break;
            case 'wdtd':
                $value  = strtolower ($value);
                $value  = str_replace ($clean_from, $clean_to, $value); // clean am pm
                $pm     = $am   = false;
                $value2 = str_replace ('pm', '', $value);
                if ($value2 <> $value) 
                     {  $pm     = true; 
                        $value  = $value2;} 
                else {  $value2 = str_replace ('am', '', $value);
                        if ($value2 <> $value) 
                             {  $am = true; 
                                $value  = $value2;} 
                        }  #echo __LINE__.' $value='.$value; exit;
                list ($wdtime, $wddate) = explode (' ', $value.' ');
                list ($hrs,$mns,$scs)   = explode (':', $wdtime.'::');
                if ($am == true && (int) $hrs == 12)
                     {  $hrs    = 0;}
                elseif ($pm == true && (int) $hrs <> 12)
                     {  $hrs    = 12 + (int) $hrs; }
                list ($y , $m, $d)      = explode (' ', $liveYMD.'  ');
                list ($a , $b, $c)      = explode ($wd_sep, $wddate.$wd_sep.$wd_sep);
                $yr     = $$y;
                $mnth   = $$m;
                $dy     = $$d;
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') calculated Y-M-D-H-M-S =  '.$yr.'-'.$mnth.'-'.$dy.'-'.$hrs.'-'.$mns.'-'.$scs.PHP_EOL;
                $weather[$key]  = mktime ($hrs,$mns,$scs,$mnth,$dy,$yr);
                if ($key== 'lightningtime') 
                     {  $weather['lightningtimeago']    = time() - $weather[$key];}
                break;
            case 'mbdt':
                $length = strlen ($value); # 20200926174036  "20080701T093807"
                if ($length <> 14) { $weather[$key]  = $value; break;}
                $dttext = substr($value,0,8).'T'.substr($value,8,6);
                $weather[$key]  = strtotime ($dttext);
                 if ($key== 'lightningtime') 
                     {  $weather['lightningtimeago']    = time() - $weather[$key];}
                break;
            case 'nmrc':                        # 2023-07-08  invalid numeric data
                $text   = str_replace (',','.',$value);
                $weather[$key]  = (float) $text;
                break;                          # 2023-07-08  invalid numeric data
#           case 'hum':
#           case 'text':
            default:
                $weather[$key]  = $value;  
                 
            }     
}
#echo '<pre>'.print_r($weather,true); exit;
#
