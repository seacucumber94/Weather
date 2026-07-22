<?php $scrpt_vrsn_dt  = 'lightning_boltek_small.php|01|2021-12-08|';  # PHP 8.1 + release 2012_lts
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
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'Boltek script is already displayed'; return;}
$$string = $string;
#
# -------------------------------- load settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -----------------------  general functions aso  
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#
#-----------------------------------------------
#      first check if script has data to display
if (!isset ($boltek) || $boltek == false)
    {   echo 'No Boltek / Nextstorm device available from settings';  return false; }
#
if (!isset ($boltekfile) || trim($boltekfile) == '' 
   || !file_exists($boltekfile) || filesize ($boltekfile) < 10)
     {  echo 'No Boltek / Nextstorm file found '.$boltekfile; return false; }
#
$file_live      = file_get_contents(trim($boltekfile));
$fields         = explode( ',',$file_live);
if (count ($fields) < 21 )   # 22 fields NSDRealtime.txt 53 in NSRealtime.txt
     {  echo 'Invalid (to short)  Boltek / Nextstorm file found'; return false; }
#
# ------------------------- translation of texts
$ltxt_nearby    = lang('Nearby');
$ltxt_total_s   = lang('Total strikes') ;
$ltxt_today     = lang('Today');     
$ltxt_latest_s  = lang('Latest strike at');
$ltxt_direction = lang('Distance');
#
# process the two different files based on their length 
#                          
if (count ($fields) < 53 )      // small file
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Processing file with '.count ($fields).' fields'.PHP_EOL; 
        $filetime                       = (int) $fields[0];
        $strikes['strikestoday']        = (int) $fields[8];
        $strikes['closestrikes']        = (int) $fields[15]; // ????
        $strikes['strikesbearing']      = (int) $fields[4];
        $strikes['laststrike_unix']     = (int) $fields[3];
        $strikes['laststrike']          = date ('H:i:s',$strikes['laststrike_unix']);
        $unit_file      = trim(strtolower($fields[20]));
        $distance       = (float) $fields[5];} 
else {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Processing file with '.count ($fields).' fields'.PHP_EOL; 
        $filetime       = mktime ( (int) $fields[3],  (int) $fields[4],  (int) $fields[5], # H M S Mo D Y
                           (int) $fields[1],  (int) $fields[2],  (int) $fields[0]);
        $strikes['strikestoday']         = (int) $fields[15];  //overall total strikes since midnight 
        $strikes['closestrikes']         = (int) $fields[17];  //close since midnight
        $strikes['strikesbearing']       = (int) $fields[6];	
#
        list ($u,$m,$s) = explode (':',$fields[41].':::');
        $strikes['laststrike_unix']     = 
                mktime ( (int) $u,  (int) $m,  $s,  (int) $fields[1],  (int) $fields[2],  (int) $fields[0]);
        $strikes['laststrike']          = date ('H:i:s',$strikes['laststrike_unix']);
        $unit_file                      = $fields[52];
        if ($unit_file == '1') { $unit_file = 'mi';} else { $unit_file = 'km';}
        $distance       = (float) $fields[7]; } // eo file with 53 items
#-----------------------------------------------
#  remaining code for both files
#-----------------------------------------------
if (time() - $filetime > 3600) 
     {  $message        = 'file to old : '.date('c',$filetime);
        echo '<!-- '.$message.' -->'.PHP_EOL;
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL; }
#
$message        = 'laststrike : '.date('c',$strikes['laststrike_unix']);
echo '<!-- '.$message.' -->'.PHP_EOL;
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL;
#
if ($unit_file <> 'km')
     {  $toMI   = 1;         $toKM   = 1.609344; $unit   = 'mi'; }
else {  $toMI   = 0.621371;  $toKM   = 1;        $unit   = 'km';}   
#
$strikes['laststrikedistance']   = round ($distance , 0);
$strikes['laststrikedistanceKM'] = round ($distance * $toKM, 0);
$strikes['laststrikedistanceMI'] = round ($distance * $toMI, 0);
#
#  check if script was started by the notification script  
#  if so we return the values and stop 
if (isset ($boltek_values) ) 
     {  $weather['lightningtimeago']    = time() - $strikes['laststrike_unix'];
        $weather['lightningkm']         = $strikes['laststrikedistanceKM'];
        $weather['lightningmi']         = $strikes['laststrikedistanceMI'];
        return true;}
#-----------------------------------------------
#        some debug info when there are problems
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '
        .'laststrikedistance= '.$strikes['laststrikedistance'] 
        .' laststrikedistanceKM= '  .$strikes['laststrikedistanceKM']
        .' laststrikedistanceMI= '  .$strikes['laststrikedistanceMI'].PHP_EOL;
#-----------------------------------------------	
#                                      direction
$bearing        = $strikes['strikesbearing'];	
$compass        = windlabel($bearing);  # 2021-12-08
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '
        .' $bearing= '  .$bearing
        .' $compass= '  .$compass
        .' close = '.$strikes['closestrikes'].PHP_EOL;
#
#                           closeness of strikes
if ($strikes['closestrikes'] >= 1)
     {  $Btxt1  = $ltxt_nearby;
        $BclsSt = 'maroon';
        $Bstrks = $strikes['closestrikes']; 
        $Btxt2  = $ltxt_total_s;
        $Bstrks2= $strikes['strikestoday'];}
else {  $Btxt1  = $ltxt_today ;
        $BclsSt = 'orange';
        $Bstrks = $strikes['strikestoday']; 
        $Btxt2  = $Bstrks2 = '';}
#                                       laststrike
$Bltst          = $ltxt_latest_s; 
$dir_txt        = $ltxt_direction;
$comp_l         = lang($compass);
#
if ($distanceunit == $unit) 
     {  $dist   = $strikes['laststrikedistance'];}
elseif ($unit  == 'km' ) 
     {  $dist   = $strikes['laststrikedistanceMI'];
        $unit   = 'mi';} 
else {  $dist   = $strikes['laststrikedistanceKM'];
        $unit   = 'km';}                # echo '<pre>'.print_r($strikes,true); exit;
#
$box_style      = 'width: 70px; height: 42px; float: left; margin: 4px; padding: 4px; margin-top: 10px; border-right-width: 1px;';
#
echo '<div class= "PWS_div_left" style=" '.$box_style.'">
     <b class="'.$BclsSt.'" style="font-size: 18px;">'.$Bstrks.'</b>
     <br />'.$Btxt1.'
</div> 
<div style="font-size:10px; padding-top: 8px;">
<span class="orange">'.$lightningsvg.' </span> '
.$Bltst.' '.set_my_time($strikes['laststrike'])
.'<br />'
.$dir_txt.' '.$dist.' '.$unit.' '.round($strikes['strikesbearing']).'&deg; '.$comp_l;
if ($Bstrks <> '') 
     {  echo '<br />'.$Btxt2.' '.$Bstrks2;}
echo '
</div>'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}