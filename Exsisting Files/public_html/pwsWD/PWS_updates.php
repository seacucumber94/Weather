<?php $scrpt_vrsn_dt  = 'PWS_updates.php|01|2023-07-14|';  # extended age + description manual load | release 2012_lts 
#-----------------------------------------------
# CREDIT - DO NOT REMOVE WITHOUT PERMISSION
# Author:       : Wim van der Kuil
# Documentation 
#   and support : https://pwsdashboard.com/
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
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);} 
header('Content-type: text/html; charset=UTF-8');
# ----------------------------- SCRIPT  SETTINGS
$url_server_site= 'https://pwsdashboard.com/srv/';
#
$url_download   = $url_server_site.'return_updates.php?rel=01&from=';
#
$url_latest     = $url_server_site.'01/_latest_scrpts.arr';  // uptodate list of current scripts
$url_ltst_back  = 'http://testing4ever.nl/01/_latest_scrpts.arr';  #### 2022-12-09
#
$my_settings    = './_my_settings/settings.php';// local settings file to check password
#
# cached files
$cache_local    = './jsondata/installed.arr';   // list of local installed scripts
$cache_latest   = './jsondata/latest_rel.arr';  // copy of server list of current scripts (max 1 hour old)
#
$test_updates   = '';  #'_test.php?test=';      // used when debugging
#
$width          = '900px';
$message        = '';
$showYN         = ' We do not show unchanged scripts ';
#
#-------------------- load current settings file
if (is_file($my_settings) )
     {  include $my_settings;}  
else {  ?> <META HTTP-EQUIV="refresh" CONTENT="0; url=PWS_easyweathersetup.php"> <?php }      
#
$token = base64_encode($_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_ADDR'].'|'.$_SERVER['REMOTE_ADDR'].'|'.$_SERVER['PHP_SELF']);
#-------------------- check age of version-lists
#
if ( file_exists ($cache_local) )
     {  $age    = time () - filemtime($cache_local);}
else {  $age    = 3600000000;}  // force load
#
if ($age < 120)  # reload all script-versions  #### 2021-04-28 
     {  $string = file_get_contents ($cache_local);
        if  ($string <> false)
             {  $all_files = unserialize ($string );}
        else {  $message .= __LINE__.' scrpt_vrsns could not be loaded into array $all_files'.PHP_EOL;}
        }
#
if (!isset ($all_files) || !is_array($all_files) )
     {  my_scandir('./') ;      // generate new up-to-date list of installed scripts.
        file_put_contents ($cache_local, serialize($all_files) ); // cache list
        }     
#
# ------------------------------- enclosing html
$html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Check for updates</title>
<link rel="stylesheet" type="text/css" href="css/configure_css.css">
<style>
body {font-family: arial,sans-serif; }
table, tr,th, td {text-align: center; border-collapse: collapse; border: 1px solid black; padding: 2px; padding-left: 4px; font-size: 12px;}
th {cursor: n-resize;}
table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { 
    content: " \25B4\25BE" }
</style>
<script src="js/sorttable.js"></script></head>
<body style="width: '.$width.'; margin: 0 auto;">';
#
# ------------------ password used, is it valid?             
if (isset($_POST['submit_pwd']))
     {  if (isset($_POST['passwd']) ) {$pass = $_POST['passwd']; } else {$pass = '';} 
        if ($pass != $password )  
             {  echo $html.'<!-- '.$message.' -->';
                showForm('<b style="color: red;">We need a valid password</b>');   
                exit;}
        } 
#
# -------------------- choice made, is it valid?
elseif (!isset($_POST['select1']) && !isset($_POST['select2'])) 
     {  echo $html.'<!-- '.$message.' -->';
        showForm("PWS_Dashboard check for updates (2012_lts)"); 
        exit; } 
#
# ------------------------ user wants a zip-file
# ------------------ therefor user is redirected
if (isset($_POST['select2'])) 
     {  echo 'Zip requested at pwsdashboard website, please wait'.PHP_EOL; 
        $string= '<META HTTP-EQUIV="refresh" CONTENT="0; url='.$url_download.$_POST['startdate'].'&token='.$token.'">'; 
        echo $string;
        return;}     
# ---------------  show compare list of versions
#
# load the latest list of valid scripts from pwsdashboard;
$latest_versions = false;
load_latest_list($url_latest); // either from cache or from server
#
$a = 1; $b = 2;
#
if (!is_array($latest_versions) || $a == $b) 
      { load_latest_list($url_ltst_back); // retry
        if (!is_array($latest_versions) || $a == $b) 
             {  echo 'Problem accessing main pwsdashboard site to load list of latest dashboard scripts.
<br>Please follow these steps to manually download, unzip and store the list of files.
<br>1.  Click the link to download a zip with the list it should arrive in your browsers download folder
<br>2.  Unzip the downloaded "_latest_scrpts.arr.zip" 
<br>3.  Rename the file  "_latest_scrpts.arr" to "latest_rel.arr"
<br>4.  User your FTP program (or file-manager) to upload the "latest_rel.arr" to your pwsWD/jsondata/ folder
<br>Important: replace the old version of the file if requested so.
<br>5.  Restart your PWS_updates.php script and it should now work';        
        die ('success');   }     
        }
#
# ----------------  check user selected show all
if (isset($_POST['select1'])) {$showYN = '';}
#
$rows = $button = '';
$lowest = '999999999999999999999999';  
# ---------------------  check every script-file
foreach ($all_files as $key =>  $file)
     {  $script = $file['name'];
        $folder = str_replace($script,'',$key);
        list ($name,$version,$date,$text) = explode ('|',$file['version']);
        $old    = $version.'|'.$date; 
        if (array_key_exists($key  , $latest_versions))
             {  list ($Pname,$Pversion,$Pdate,$Ptext) = explode ('|',$latest_versions[$key]['version']);}
        else {  $Pname = $Pversion = $Pdate = $Ptext = '- -';}
        $new    = $Pversion.'|'.$Pdate;
        $hide = '';
        if ($new == $old )  // no update, do we list it eitherway?
             {  if ($showYN <> '') {continue;}
                $todo = ''; }           // unchanged script
        elseif ($new > $old && $old >= '01|2020-11-02')
             {  $button = true;
                if ($new < $lowest) {$lowest = $new;} 
                $todo   = 'update';}    // updated script
        elseif ($new > $old && $old < '01|2020-11-02')
             {  $todo   = '<b style="color: red;" >outdated <br /> do not use</b>';}
        else {  $todo   = 'check'; }    // extra user script 
        $rows .= '
<tr><td style="text-align: right;">'.$folder.'</td><td style="text-align: left;">'.$script.'</td><td>'.$old.'</td><td>'.$todo.'</td><td>'.$new.'</td><td>'.$Ptext.'</td></tr>';
     }
#
echo $html.'<!-- '.PHP_EOL.$message.' -->
<div style=" margin:0 auto; ">
<br />
<form action = "'.$test_updates.basename(__FILE__).'" method="post" name="select_next" >
<table style="width: 80%;  border: 0px solid transparent; margin: auto;">
<tr style="border: 0px solid transparent;">
<td style="border: 0px solid transparent;">
<input type="submit" name="select1" value="Show all installed scripts" style="cursor: pointer;"></td>
<td style="border: 0px solid transparent;">'.PHP_EOL;
if ($button === true) 
     {  echo '<input type="submit" name="select2"     value="Download updates" style="cursor: pointer;">
<input type="input" name = "startdate" value="'.$lowest.'"      style=" display: none; "/>     
     </td>'.PHP_EOL; }
echo '</tr>
</table>
</form>
<div style="max-height: 600px;  margin:0 auto; overflow-y: scroll; ">
<table style="width: 100%;  border: none; " class="sortable">
<tr><th>folder</th><th>script</th><th>Installed</th><th>ToDo</th><th>Current</th><th>Remarks</th></tr>';

echo $rows.'
</table>
</div>
</div>
</body>
</html>';

#
function my_scandir($dir)
     {  global  $all_files;
        static $skip_files     = array ('.','..','.DS_Store', '_my_settings', 'chartsmydata' , 'chartswudata',
                        'demodata', 'img', 'jsondata','languages', 'nws-alerts','pws_icons',
                        'wrnImages','wrnImagesLarge', 'wudata','wxsimPP',);
        $dir_clean = str_replace ('./','',$dir);
        if ($dir_clean<> '') {$dir_clean .= '/';}   
        $files  = scandir ($dir, SCANDIR_SORT_ASCENDING);
        foreach ($files as $key => $file)
             {  if (in_array ($file, $skip_files) ) 
                     {  unset ($files[$key]);
                        continue;}
                $filename       = $dir_clean.$file;
                if (is_dir ( $filename) )
                     {  my_scandir($filename); 
                        continue;}
 #               echo '<br />'.$file.PHP_EOL;
                $version = my_scan_file ($filename);
                if ($version == 'n/a') 
                     {  #echo '<br />'.$filename.' not processed'.PHP_EOL; 
                        continue;}               
                $all_files[$filename] = array ('name' => $file, 'time' => filemtime($filename), 'size' => filesize($filename),'version' => $version );           
                }
        return; 
        }
function my_scan_file ($filename)
     {  static $skip_types     = array ('.svg', '.png', '.jpg', '.gif', '.ico', '.pem', '.arr');
        $type = substr ($filename, -4);  # echo __LINE__.' '.$type; exit; 
        if (in_array ($type, $skip_types) ) {return 'n/a'; }
        $name   = $filename;# echo __LINE__.' filename='.$filename.' ';
        $string = file_get_contents($filename);  
        $string = substr($string,1,200);
        if (trim($string) == '' || $string == false) {return 'n/a'; }
        list ($rest,$version,$none) = explode ('$',$string.'$$');
        if (strpos ($version,"'")  == false ) {$string = str_replace ('"',"'",$version);} # echo __LINE__.' '.substr($version,1,70);
        list ($none,$version,$info) = explode ("'",$version."'''''"); 
        list ($name,$number,$date) = explode ("|",$version.'||||');  
        list ($info,$none) = explode ("\n",$info."\n");      
        list ($none,$info) = explode ('#',$info.'#');
#  echo __LINE__.' name='.$name.' number='.$number.' date='.$date.' info='.$info    .PHP_EOL; 
        return trim($name).'|'.trim($number).'|'.trim($date).'|'.substr(trim($info),0,50).'|';
        }
#
function load_latest_list($url_latest) 
     {  global $message , $latest_versions, $cache_latest  ;
        $age = 999999999;
# check cache 
        if (file_exists($cache_latest) )                // if cache file exists
             {  $age     =  time() - filemtime ($cache_latest);}   
        if ($age < 8 * 3600)      # 2023-07-14  extended age                          //  age max x hour
             {  $latest_versions = unserialize (file_get_contents ($cache_latest) );
                if (is_array($latest_versions) )   
                     {  $message = __LINE__.' Data with age '.$age.' seconds, loaded from cache: '.$cache_latest.PHP_EOL;}
                return;}
# if no (valid) cache exists we need to load it from the server  
        $message = __LINE__. ' No cache, age ='.$age.' load from server '.PHP_EOL;   
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url_latest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,10);        // data timeout 
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);   // 2022-12-08
        curl_setopt ($ch, CURLOPT_USERAGENT, 
                'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0');  # 2023-02-15
#                Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36
        $data   = curl_exec ($ch);      # echo __LINE__.print_r($latest_versions);
        $info	= curl_getinfo($ch);    # echo print_r($info); exit;
        if ($info['http_code'] == '200') 
             {  file_put_contents ($cache_latest,$data);
                $latest_versions = unserialize ($data);}  
        $error  = curl_error($ch);
        curl_close ($ch);
        if ($error <> '')
             {  $message = __LINE__.'returncode:'.$info['http_code'].' | Errors: '.$error.PHP_EOL;}
        return; 
}
# ----------------------------------------------
function langtransstr($string)  # to translate texts
     {  global $LANGLOOKUP;
        if (isset ($LANGLOOKUP[$string]))
             {  return $LANGLOOKUP[$string]; }
        else {  return $string;}} // eof langtransstr
# 
function showForm ($msg="LOGIN")# to display password form
     {  global $password, $test_updates;
        #if (isset ($_POST)) {echo '<!-- '.print_r($_POST,true) .$password.' -->';}
        echo  '<div style="border: none; text-align: center;"><br />'. $msg.'<br /><br />
<a href="./index.php">Back to the main page</a><br /><br />
<div style = " width:600px; margin:0 auto; color:rgba(24, 25, 27, 1.000); border:solid 1px grey; padding:10px; border-radius:4px;" >
<form action = "'.$test_updates.basename(__FILE__).'" method="post" name="pwd" > 
Your Easyweather setup password<br /><br />
<center>
    <input name = "passwd" type= "password"  class = "input" />  
    <input type = "submit" name= "submit_pwd" value="Login" class="btn" /> 
</center>         
</form>
<br />
<b>Info:</b> Your current PHP version is  : ' . phpversion(). ' <br>
PHP 7+ is advised for this template but it will run also with PHP 5.6.3 or higher
</div>
</div>
</body>
</html>';
}
