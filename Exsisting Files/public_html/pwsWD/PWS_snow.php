<?php  $scrpt_vrsn_dt  = 'PWS_snow.php|01|2023-10-31|';  # 2023 winter | release 2012_lts
#
# -----------------     SETTINGS for this script
$ws_snow_max_cnt= 1;            # number of lines to show in low height block
$snow_file   = './jsondata/snow.txt';
$snow_arr    = './jsondata/snow.arr';
#
# ------------------------------     texts used

#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
# VERSION       : 4.00
# Author:       : Wim van der Kuil
# Documentation 
#   and support : https://leuven-template.eu/
#-----------------------------------------------
#  display source of script if requested so
#-----------------------------------------------

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
     {  $snw_tst= true;
        ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);
        $snw_tst= false;}  
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------  load settings and common scripts
$scrpt          = 'PWS_settings.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
$scrpt          = 'PWS_shared.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#                      
#                          setup the snow season 
#-----------------------------------------------
$now            = time();
$this_year      = date ('Y',$now);
$this_month     = date ('n',$now);
$this_today     = date ('Ymd',$now);
#
$last_year      = $this_year;   # New season start
#
if ($lat > 0)                   # winter NH spans two calendar years
     {  $snow_nh        = true; 
        if ($this_month < 6)    # start season now in previous year
             {  $last_year      = $this_year - 1;}   
        $arr_snow_months= array (0,7,8,9,10,11,12,1,2,3,4,5,6); }
else {  $snow_nh        = false; 
        $arr_snow_months= array (0,1,2,3,4,5,6,7,8,9,10,11,12);}
#                      
#                            load avialable data 
#-----------------------------------------------
#
if (is_file ($snow_arr) ) 
     {  $array_time     = filemtime ($snow_arr);}
else {  $array_time     = 0; }  #echo __LINE__.$snow_arr.' '.$snow_file;  exit;
#
if (is_file ($snow_file) ) 
     {  $file_time      = filemtime ($snow_file);}
else {  $file_time      = 0; }
#
if ($file_time == 0 && $array_time == 0)
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') No snow files  found, script ends'.PHP_EOL; 
        $arr    = array();
        $arr_snw= array();
        $arr['c_hght'] = 0;
        $last   = 'no snow yet';
        return false;}
#
if ($file_time < $array_time )
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Current '.$snow_arr.' up-to-date'.PHP_EOL;
        $arr_snw        = unserialize (file_get_contents ($snow_arr));
        return $arr_snw['99999999'];}
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') loading txt data'.PHP_EOL;       
if (!is_file($snow_file) ) 
     {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file not found, start with empty one'.PHP_EOL; 
        $snow_txt       = array();
        $snow_txt[]     = '#YYYY|MM|DD|UNIT|HEIGHT|PLUS |';}
else {  $snow_txt       = file($snow_file);}
if (!is_array ($snow_txt) || count($snow_txt) < 1)
     {  $message        = 'Snow file incorrect - Scipt ends';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL; 
        echo '<!-- '.$stck_lst.' -->'.PHP_EOL;
        die ('<b style="color: red;">'.$message.'</b>');}
#                       calculate current values 
#-----------------------------------------------
$old_height     = 0; 
$ttl_melt       = 0;
$ttl_fall       = 0;
$arr_snw        = array();
$lst_ymd        = 0;
$lst_snw        = 0;
$lst_fall       = 0;
foreach ($snow_txt as $key => $string)
     {  $string = trim($string).'#';    # remove spaces new lines
        $first  = substr($string,0,1);
        if ($first == '#')  {continue;} # remove blank lines or comment lines
        if ($first <> '|')  
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' non standard contents are skipped'.PHP_EOL;
                continue;}
        list ($none, $l_year,$l_month,$l_day,$l_unit, $l_hght,$l_fall)        = explode ('|',$string.'|||||||');
#echo '<pre>$l_year='.$l_year.'$l_month='.$l_month.'$l_day='.$l_day.'$l_hght='.$l_hght.'$l_fall='.$l_fall.PHP_EOL.'<br /></pre>';
        $check = (int) $l_year;
        if ($check <> $this_year &&  $check <> $last_year)
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' old data $l_year='.$l_year.'$l_month='.$l_month.'$l_day='.$l_day.PHP_EOL;
                continue;}
        $c_year = $check;        
        $check  = (int) $l_month;
        if (! in_array ($check,$arr_snow_months) || $check == 0 )
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' month incorrect $l_month='.$l_month.PHP_EOL;
                continue;}
        $c_month= substr ('0'.$check,-2);
        $check  = (int) $l_day;
        $correct= checkdate ($c_month, $check,  $c_year);
        if ($correct == false)
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' day incorrect $l_day='.$l_day.PHP_EOL;
                continue;}
        $c_day  = substr ('0'.$check,-2);
        $l_ymd  = $c_year.$c_month.$c_day;
        if ($l_ymd < $lst_ymd)
             {  $message        = 'Snow file line '.(1 + $key).' date '.$l_ymd.' older then previous lines '.$lst_ymd;
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL;
                #echo $message.'. <b style="color: red;">Line skipped</b><br />';
                continue;}
        if ($l_ymd > $this_today)
             {  $message        = 'Snow file line '.(1 + $key).' date '.$l_ymd.' in the future '.$lst_ymd;
                $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL;
                #echo $message.'. <b style="color: red;">Line skipped</b><br />';
                continue;}        
        $lst_ymd= $l_ymd;
        $check  = (float) $l_hght;
        if ($check < 0 || $check > 999) 
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' height incorrect $l_hght='.$l_hght.PHP_EOL;  
                continue;}
        $l_hght = $check;
        $check  = (float) $l_fall;
        if ($check < 0 || $check > 999) 
             {  $stck_lst      .= basename(__FILE__).' ('.__LINE__.') Snow file line '.$key.' fresh fallen snow incorrect $l_fall='.$l_fall.PHP_EOL;  
                continue;}
        $l_fall = $check;               // can be zero if not entered
        
        $l_diff = $l_hght - $old_height; // last height - previous height

        if ($l_diff > $l_fall)  // fall not entered but height is heigher as last time
             {  $l_fall = $l_diff;}


        $l_melt       = $l_fall - $l_diff;
        if ($l_melt < 0) { $l_melt = 0;}

        $arr_snw[$l_ymd]= array('c_date' => $l_ymd, 'diff' => $l_diff, 'p_hght' => $old_height, 'c_hght' => $l_hght, 'c_fall' => $l_fall, 'c_mlt' => $l_melt);    
        if ($l_fall > 0) {$lst_snw  = $l_ymd; $lst_fall = $l_fall;}
        $old_height     = $l_hght;
        $ttl_melt       = $ttl_melt + $l_melt;
        $ttl_fall       = $ttl_fall + $l_fall;
        continue;
}
$arr_snw['99999999']    = array('c_date' => $lst_snw, 'c_hght' => $old_height, 'c_fall' => $ttl_fall, 'c_mlt' => $ttl_melt); 
$temp_fl        = './jsondata/snow.tmp';
$return = file_put_contents ($temp_fl, serialize ($arr_snw));  # echo '<pre>'.$stck_lst.print_r($arr_snw,true); exit;
if ($return == false)
     {  $message        = 'Problem saving snow array to '.$temp_fl;
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.$message.PHP_EOL;
        echo $message.'. <b style="color: red;">Reuse txt file</b><br />';}
else {  if (is_file ($snow_arr) )
             {  rename ($snow_arr, $snow_arr.'old');}
        }
rename ($temp_fl , $snow_arr);
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') current snow file '.$snow_arr.' saved.'.PHP_EOL;     
return $arr_snw['99999999'];
