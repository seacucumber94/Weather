<?php $scrpt_vrsn_dt  = 'PWS_listfile.php|01|2023-02-15|';  # nvers + arr error + php8 + spelling mistake | release 2012_lts
#
#  UTILITY to dump or pretty print file contents
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
ini_set('display_errors', 'On'); error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
header('Content-type: text/html; charset=UTF-8');
#-----------------------------------------------------------------------
#  used to display the contents of a file from within the debug console.
#-----------------------------------------------------------------------
$file   = './license.txt';
$type   = 'txt';
$explain['txt']    = 'text';
$explain['space']  = 'space separated fields';
$explain['comma']  = 'comma separated fields';
$explain['json']   = 'json encoded';
$explain['arr']    = 'php array';
$explain['xml']    = 'xml-text';
#
$types  = array ('space','comma','txt','json','arr', 'xml');
#
if (isset ($_REQUEST['file']))  
     {  $file  = trim($_REQUEST['file']);
        $check  = str_replace ('_my_settings','',$file);
        if ( strpos (' '.$file,'_keys')    > 0  
          || strpos (' '.$check,'settings') > 0 
          || strpos (' '.$file,'../')      > 0      
#          || strpos (' '.$file,'.php')     > 0 
          || substr ($file,0,1) == '/'
          || !is_file($file)  )     
             {  echo 'file:  not found or could not be printed';
                return;}
        } // eo check filename
#
if (isset ($_REQUEST['type']))
     {  $in     = trim($_REQUEST['type']);
        if (in_array ($in,$types) ) {$type = $in;} }
$string = file_get_contents ($file);
#
echo 'Contents of <b>'.$file.'</b>, processed as filetype "<b>'.$explain[$type].'</b>"<br /><br />';
$age    = time() - filemtime($file);
if ($age < 0) {$age = 0;}
#$age    = gmdate ('G',$age).' hrs '.gmdate ('i',$age).' min '.gmdate ('s',$age).' seconds';
$days   = floor ($age/(24*3600)); $age = $age - $days *24*3600; 
if ( $days == 0)   { $days = '';}  else $days .= ' days ';
$hours  = floor ($age/3600);      $age = $age - $hours*3600;    
if ($hours == 0)  { $hours = '';} else $hours .= ' hours ';
$mins   = floor ($age/60);        $age = $age - $mins*60;                                  
if ($mins == 0)   { $mins = '';}  else $mins .= ' minutes ';
if ($age == 0)    { $seconds = '';}  else $seconds = $age.' seconds ';  #### 2021-04-29
$age    = $days.$hours.$mins.$seconds;
$from   = array ('+00:00','T');
$date   = str_replace($from,' ',gmdate('c',filemtime($file)) );

echo 'Filetime: <small>'.$date. 'UTC</small> => Age '.$age.'<br /><br />'.PHP_EOL;
echo '<b>Expand this file as: 
<a href="PWS_listfile.php?file='.$file.'&type=space">'.$explain['space'].'</a>&nbsp;|&nbsp;
<a href="PWS_listfile.php?file='.$file.'&type=comma">'.$explain['comma'].'</a>&nbsp;|&nbsp;
<a href="PWS_listfile.php?file='.$file.'&type=arr">'.$explain['arr'].'</a>&nbsp;|&nbsp;
<a href="PWS_listfile.php?file='.$file.'&type=json">'.$explain['json'].'</a>&nbsp;|&nbsp;
<a href="PWS_listfile.php?file='.$file.'&type=xml">'.$explain['xml'].'</a>&nbsp;|&nbsp;
<a href="PWS_listfile.php?file='.$file.'&type=txt">'.$explain['txt'].'</a></b><br /><br />';

echo '<b>Unprocessed first 80 characters of the file :</b><pre>'.htmlentities (substr($string,0,80)).'</pre>';

switch ($type)
     {  case 'space':
                $arr    = explode (' ',$string);
                break;
        case 'comma':
                $arr    = explode (',',$string);
                break;

        case 'json':
                $arr    = json_decode($string, true); 
                if ($arr == false || $arr == '') {$arr = 'Not a valid .json file.';}
                break;
        case 'arr':
                $arr    = unserialize($string); # echo print_r($arr,true); exit;
                if ($arr == false || $arr == '' || !is_array ($arr)) 
                     {  $arr = 'Not a valid array';}
                break; 
        case 'xml': 
                $arr = str_replace ('<','&lt;',$string) ; 
                break;
        default: $arr   = htmlentities ($string);
     }

echo '<br /><b>Contents processed:</b><pre>'.print_r ($arr, true).'</pre>';
