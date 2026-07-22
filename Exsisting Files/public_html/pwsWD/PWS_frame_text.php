<?php $scrpt_vrsn_dt  = 'PWS_frame_text.php|01|2022-12-14|';  # nvers + xml problem | release 2012_lts
#
# used to display other files  in menu or as popup
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$type   = 'file';               # default
$link   = './license.txt';      # default
#
#
if (isset ($_REQUEST['showtext']) && $_REQUEST['showtext'] <> '' )
     {  $link   = trim($_REQUEST['showtext']); 
        if (strpos (' '.$link,'keys') > 0    
         || strpos (' '.$link,'settings') > 0
         || strpos (' '.$link,'../') > 0
         || strpos (' '.$link,'.php') > 0
         || substr ($link,0,1) == '/')
             {  echo 'file not found, check your typing';
                return;}
        }
if (isset ($_REQUEST['type']) && $_REQUEST['type'] <> '' )
     {  $param  = trim($_REQUEST['type']); 
        if ($param <> 'url') {$param = 'file';}
        $type   = $param;}

if ($type == 'url')    #     'https://tgftp.nws.noaa.gov/data/raw/cd/cdus41.klwx.cli.bwi.txt';
      { $ch             = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,20);        // data timeout 30 seconds
        $result = curl_exec ($ch);
        $info	= curl_getinfo($ch);
        $error  = curl_error($ch);
        curl_close ($ch);    }
else {  $result         = file_get_contents ( $link);}
#
$result = str_replace ('<','&lt;',$result);
echo '<div style="width: 100%; margin: 0 auto; background-color: white;">
<pre style=" font-family: monospace;">'.$result.'</pre></div>'.PHP_EOL;
/*
# add to _my_settings/frames.php as 
$show   = true; 
#$show   = false; 
#
if ($show == true) {
$frame                  = 'display_text';
$frm_ttls[$frame]       = lang('a_nice_name');  // name in menu

# For an external text file somewhere on the internet
$frm_src[$frame]        = './PWS_frame_text.php?showtext=_url_to_the_text_&type=url&type=url|file';  
#f.i.: $frm_src[$frame] = './PWS_frame_text.php?showtext=https://tgftp.nws.noaa.gov/data/raw/cd/cdus41.klwx.cli.bwi.txt&type=url';

# Or for a a text file in the Dashboard website
$frm_src[$frame]        = './PWS_frame_text.php?showtext=_url_to_the_text_&type=url';  
#f.i.: $frm_src[$frame] = './PWS_frame_text.php?showtext=license.txt&type=file';

$frm_hgth[$frame]       = 800;         //height
$frm_type[$frame]       = 'div'; 
}
*/