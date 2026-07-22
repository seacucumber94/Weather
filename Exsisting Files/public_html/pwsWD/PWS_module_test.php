<?php $scrpt_vrsn_dt  = 'PWS_module_test.php|01|2023-07-14|';  # nocache max size selectors +livedata format | release 2012_lts
# ----------------------------------------------
# Select and test all modules of PWS_Dashboard  
# ----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
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
ini_set('display_errors', 'On');   error_reporting(E_ALL);  
# --------store all loaded modules in stack list
$stck_lst       = basename(__FILE__).'('.__LINE__.')  loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# ------------   load all supporting scripts
$scrpt          = 'PWS_livedata.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
# --------  dummy if lang functions did not load
if (!function_exists('lang') ) { function lang ($txt) { return ($txt);}}
#
# ----------  load list of all available blokcks
$scrpt          = 'PWS_blocks.php';  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
ksort($blck_ttls);  # echo print_r($blck_ttls); exit;   // sort script php name 
$all    = $blck_ttls;   // save in "all" array
foreach ($blck_ttls as $script => $name)  // for every script, check if they have popup scripts
     {  if (isset ($blck_ppp[$script]) ) 
            {   $popups = $blck_ppp[$script];   
                foreach ($popups as $popup)
                     {  if ($popup['show'] <> true) {continue;}
                        if ($popup['chartinfo'] <> 'popup') {continue;}
                        $script_name    = $popup['popup'];
                        $script_label   = $popup['text'];
                        $all[$script_name] = $script_label; // save all pop ups in "all" array
                } // eo each popup
            }  // eo check if popups are used 
        } // eo each script
ksort ($all);   // sort on script name
$arr_small      = array();      // array for top row small blocks
$arr_block      = array();      // array for all normal blocks
$arr_popup      = array();      // array for popups
foreach ($all as $script => $text)
     {  if (array_key_exists($script,$blck_type) )
             {  $type   = $blck_type[$script];
                if ($type == 'x' || $type == 's') 
                     {  $arr_small[$script]     = $text; 
                        continue;}
                elseif ($type == 'b' || $type == 'c' || $type == 'f')
                     {  $arr_block[$script]     = $text; 
                        continue;}
                }
        if (strpos ($script, 'small') <> false) 
             {  $arr_small[$script]     = $text; }
        elseif (strpos ($script, 'block') <> false) 
             {  $arr_block[$script]     = $text; }
        else {  $arr_popup[$script]     = $text; }
     } // eo each scripts
# 
# ------------------------------
$folderlist     = array();
$folderlist[]   = '_my_settings';
$folderlist[]   = 'jsondata';
if ($charts_from == "WU") 
     {  $folderlist[]   = 'chartswudata';
        $folderlist[]   = 'wudata';} 
else {  $folderlist[]   = 'chartsmydata';}       // history and optional y md d files
$folderlist[]   = 'demodata';
$filelist       = array();
$dir    = $folderlist[0];
foreach ($folderlist as $dir)
     {  if ($handle = opendir($dir)) 
             {  while ($handle && (false !== ($entry = readdir($handle)))) 
                     {  if (substr ($entry,0,1) != "."  && substr ($entry,-3) != "old") 
                             {  $data   = $dir.'/'.$entry;
                                if ($data == '_my_settings/settings.php' 
                                 || $data == '_my_settings/twitter_keys.php') {continue;}
                                $filelist[]=$data;}
                     } // eo while
                closedir($handle);
             } // eo correct dir
     } // eo each dir
sort ($filelist); #echo '<pre>'.print_r ($filelist,true); exit;
# --------------------- generate selection boxes
header('Content-type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');   # 2023-06-25
header('Pragma: no-cache');                                     # 2023-06-25
header('Expires: 0');                                           # 2023-06-25
echo '<!DOCTYPE html>
<html lang="en">
<head>
<title>Module test PWS_Dashboard</title>
<style>td {background-color: #B6B6B6; padding: 2px;} </style>
</head>
<body style="height: 100%; width: 800px; margin: 0 auto;">
';
echo '<h3 style="text-align: center; margin: 0 auto;">PWS_Dashboard - Debug-Console: Test your modules and check all data <small><small>('.$PWS_version.' version)</small></small></h3>'.PHP_EOL;
echo '<table style="text-align: center; margin: 0 auto;">'.PHP_EOL;
#
# ------------------------    all blocks
echo '<tr ><td colspan="3">Inspect /run all blocks</td></tr>
<tr>
<td><select id="small" name="small" style="">'.PHP_EOL;
foreach ( $arr_small as $script => $text) { echo '<option value="'.$script.'">'.$script.'</option>'.PHP_EOL;}
echo '</select></td>'.PHP_EOL;
echo '<td><select id="block" name="block" style="">'.PHP_EOL;
foreach ( $arr_block as $script => $text) { echo '<option value="'.$script.'">'.$script.'</option>'.PHP_EOL;}
echo '</select></td>'.PHP_EOL;
echo '<td><select id="popup" name="popup" style="">'.PHP_EOL;
foreach ( $arr_popup as $script => $text) 
     {  $script = str_replace ('?','&',$script);
        echo '<option value="'.$script.'">'.substr($script,0,50).'</option>'.PHP_EOL;}  #    2023-04-23
echo '</select></td>'.PHP_EOL;
echo '</tr>
<tr>
<td><input type="submit" onClick="frame_load(\'small\');" value="Test-it" style="cursor: pointer;"></td>
<td><input type="submit" onClick="frame_load(\'block\');" value="Test-it" style="cursor: pointer;"></td>
<td><input type="submit" onClick="frame_load(\'popup\');" value="Test-it" style="cursor: pointer;"></td>
</tr>
<tr><td  colspan="3" style="background-color: transparent;"></td></tr>'.PHP_EOL;
#
# ------------------ Check file contents
echo'<tr><td  colspan="3">Check file contents</td></tr>
<tr><td><select id="files" name="files" style="">'.PHP_EOL;
$from   = realpath(dirname(__FILE__)).'/'; $to = '';   #### 2021-07-30
$script = $livedata = str_replace ($from,$to,$livedata);
$script = str_replace ('?','&',$livedata);
echo '<option value="'.$script.'">'.$script.'</option>'.PHP_EOL;
foreach ( $filelist as $script) 
     {  if ($script == $livedata || $script == './'.$livedata ) {continue;}
        echo '<option value="'.$script.'">'.substr($script,0,50).'</option>'.PHP_EOL;}   #    2023-04-23
echo '</select></td>
<td rowspan="2"><input type="submit" onClick="frame_load2(wdapi);" value="Show station values" style="cursor: pointer;"></td>
<td rowspan="2"><input type="submit" onClick="frame_load2(live);" value="Live data values" style="cursor: pointer;"></td>
</tr>
<tr><td><input type="submit" onClick="frame_load3(\'files\');" value="Listcontents" style="cursor: pointer;"></td></tr>
<tr><td  colspan="3" style="background-color: transparent;"></td></tr>'.PHP_EOL;
#
# ------------------ Load external files
echo '<tr><td colspan="3">Load external files. Important this can take more than 10 seconds </td></tr>
<tr><td colspan="3"><input type="submit" onClick="frame_load2(load);" value="Load files" style="cursor: pointer;"></td></tr>
'.PHP_EOL;
echo '</table>
<script>
var unique= "&t='.time().'";
var wdapi = "PWS_print_file.php?showall"+unique; 
var live  = "PWS_listdata.php?"+unique;
var load  = "PWS_load_files.php?test"+unique;
function frame_load(a) { 
btn     = document.getElementById(a);  
scpt    = "_test.php?test="+btn.value;
which   = document.getElementById("doit");
which.src=scpt+unique; 
}
function frame_load2(a) { 
which   = document.getElementById("doit");
which.src=a;
}
function frame_load3(a) { 
btn     = document.getElementById(a);
which   = document.getElementById("doit");
which.src="PWS_listfile.php?file="+btn.value+unique;
}
</script>
<iframe  style="width: 100%; height: 600px;" id="doit" ></iframe>
</body>
</html>';
