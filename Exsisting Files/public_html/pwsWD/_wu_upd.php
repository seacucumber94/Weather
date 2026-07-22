<?php $scrpt_vrsn_dt  = '_wu_upd.php|01|2021-05-15|';  # empty lines missing | release 2012_lts
#
# QAD script to update the MITM data storage
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
ini_set('display_errors', 0); error_reporting(0);
#
$stck_lst       = '';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# ------------  load settings and common scripts
$scrpt          = 'PWS_settings.php';  
if (!file_exists  ($scrpt))
     {  $scrpt  = 'w34_settings.php';
        if (!file_exists  ($scrpt)) 
             {  die ('No dashboard settings file found, script ends');}
        $scrpt2 = 'w34_settings.php';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
echo '<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"/>
<title>Wu update</title>
<!-- <link rel="stylesheet" type="text/css" href="css/xxxxx_css.css" /> -->
<style>
table, tr,th, td {text-align: center; border-collapse: collapse; border: 1px solid black; padding: 2px;}
th {cursor: n-resize;}
input {width: 90%; margin: 0 auto; text-align: center;}
</style>
<script src="js/sorttable.js"></script></head>
<body style="width: 95%; margin: 0 auto;">'.PHP_EOL; 
# ----------------------------------------------
#echo '<pre>'.__LINE__.' '.print_r($_REQUEST,true).'</pre>';
$wudata = './wudata';
$wuFiles = scandir ($wudata , SCANDIR_SORT_ASCENDING);
#
if (isset($_POST['submit_pwd']))
     {  if (isset($_POST['passwd']) ) {$pass = $_POST['passwd']; } else {$pass = '';} 
        if ($pass != $password) 
             {  showForm('<b style="color: red;">EASY SETUP needs a valid password</b>');   
                exit;}
        }
elseif (!isset($_POST['submit']) )
     {  showForm("PWS update the WU archive"); 
        exit; } 
#
if (isset ($_POST['selected']) ) { $selected = $_POST['selected']; } else {$selected = '';}
if (isset ($_POST['YMD']) )      { $YMD      = $_POST['YMD']; }      else {$YMD      = '';}
if (isset ($_POST['passwd']) )   { $pass     = $_POST['passwd']; }   else {$pass     = '';}
#
$string='';
if ( trim($_POST['submit'])  == 'Save your changes' )
     {  $file   = $wudata.'/'.$selected; # echo 'HALT '.__LINE__; exit;
        $arr    = unserialize (file_get_contents ($file) );
        $prev   = explode (',',$arr[$YMD]);
        $cnt    = count($prev);
        $return = $_POST['return']; # echo '<pre>'.print_r($prev,true).print_r($return,true).PHP_EOL.'</pre>';
        if (count ($return) > $cnt)
             {  $cnt    = count($return); }
        $string = $record = $return[0];
        $all_OK = true;
        for ($n = 1; $n < $cnt; $n++) 
             {  $new    = trim($return[$n]);
                $old    = trim($prev[$n]);
                $record.= ','.$new;
                if ($new == $old )
                     {  $string .= ','.$new;
                        continue;}
                elseif (is_numeric($new))
                     {  $string .= ',<b style="color: green;">'.$new.'</b>';
                        continue;}
                $string .= ',<b style="color: red;">'.$new.'</b>';
                $all_OK = false;
                } // eo foreach  value checked
        echo '<pre><b>Old: </b>'.$arr[$YMD].PHP_EOL;
        echo '<b>New: </b>'.$string.PHP_EOL.'</pre>';
        if ($all_OK == false)
             {  echo '<b style="color: red;">Data not updated, please try again</b><br />';
                $arr[$YMD]      = $record;}
        else {  echo 'Data OK, will be updated<br /><br />';
                $arr[$YMD]      = $record;
                file_put_contents ($file, serialize ($arr) );
                $YMD = '';}
        } // eo post check
#
echo '
<form id="xyz" method="post" action="_wu_upd.php" style="display: block">
<input type="submit" style="display: none; padding: 0px; border: 0px; margin: 0px;" name="submit"   value="submit">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="passwd" value="'.$pass.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="selected" value="'.$selected.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="YMD" value="'.$YMD.'">
</form>
<br />'.PHP_EOL;
#
if ($selected == '') 
     {  $cnt = 0;
        $file= '';
        $string = '<div style = "text-align: center; width:600px; margin:20px; color:rgba(24, 25, 27, 1.000); border:solid 1px grey; padding:10px; border-radius:4px;" >
Select WUdata year file <br /><br />';
        foreach ($wuFiles as $key => $value)
             {  if (substr ($value,0,1) == '.') {continue;}
                if (is_dir ($value) ){continue;}
                if (substr($value,-4) <> '.arr') {continue;}
                $cnt++;
                $file   = $value;
                $string .= '
<b style="cursor: pointer; color: blue; text-decoration: underline;" 
        onclick="wuPostFunctionFile(\''.$value.'\')">'.$value.'</b>
<br />'.PHP_EOL; }
        if ($cnt == 0) { $string .= '<b style="color: red;">No arrays with WU-data found</b>
<br /><br /><a href="./index.php">Back to the main page</a><br /><br />';
        }
#
        $string .= '</div>
<script>
function wuPostFunctionFile(a) {
        var form = document.getElementById("xyz");
        form.selected.value = a;
        form.submit.click();}
</script>       
</body>
</html>';
        if ($cnt > 1) 
             {  echo $string;
                return; }
        $selected = $file;
        echo 'Only one WU data file found, ready to update.<br /><br />'.PHP_EOL;
}
#       
$fields= array( 'Date',
        'Temp<br />High','Temp<br />Avg','Temp<br />Low',
        'Dewp<br />High','Dewp<br />Avg '  ,'Dewp<br />Low',
        'Hum<br />High' ,'Hum<br />Avg'   ,'Hum<br />Low',
        'Baro<br />Max'  ,'Baro<br />Min',
        'Wind<br />Max' ,'Wind<br />Avg',
        'Gust<br />Max','Precip<br />Sum');
#
if ($YMD == '')
     {  $file   = $wudata.'/'.$selected;
        $arr    = unserialize (file_get_contents ($file) );
        if (!is_array($arr) || count($arr) == 0) 
             {  die ('File seems to be empty, check file with your FTP program.
<br /><a href="./index.php">Back to the main page</a><br /><br />');}  #echo '<pre>'.__LINE__.print_r($arr,true); exit;
#       
        echo 'Select date to change 
or <b style="cursor: pointer; color: blue; text-decoration: underline;"  onclick="wuPostStepBack()"> go back</b> to select another file.</b> 
or <b style="cursor: pointer; color: blue; text-decoration: underline;"><a href="./index.php">go back</a></b> to the main page<br /><br />
All columns can be sorted high-to-low and low-to-high by clicking on the column header<br />'
        .'<table style="width: 100%;" class="sortable"><tr style=""><th> Select </th>';
        foreach ($fields as $field)
             {  echo '<th> '.$field.' </th>'; }
        echo '</tr>'.PHP_EOL;
# 
        $parts  = explode ('-',$selected);
        $year   = substr ($parts[2],0,4);
        list ($wuY,$wuM,$wuD)   = explode ('-',$wu_start.'----');
        if ($wuY == $year) 
             {  $first  = $wuY.$wuM.$wuD;}
        else {  $first  = $year.'0101' ; }
        ksort ($arr);   #echo '<pre>'.__LINE__.print_r($arr,true); exit;
                        #echo __LINE__.' $selected='.$selected.' $first='.$first.' ';         
        $unix   = strtotime ($first.'T120000');
        $next   = date ('Ymd',$unix);
        foreach ($arr as $key => $data)
             {  $line_time      = strtotime ($key.'T120000');
                $thisDate       = date ('Ymd',$line_time); #echo __LINE__.' $key='.$key.' $thisDate='.$thisDate.' $next='.$next.PHP_EOL; 
                while ($thisDate > $next)  
# we have to insert empty lines
                     {  echo '<tr><td><b style="cursor: pointer; color: blue; text-decoration: underline;"  onclick="wuPostFunctionFile(\''.$next.'\')">'.$next.'</b></td>';
                        echo  '<td>'.date ('Y-m-d',$unix).'</td>';
                        for ($n = 1; $n < 16; $n++)
                             {  echo '<td>  </td>'; }  
                        echo '</tr>'.PHP_EOL;
                        $unix   = 24* 3600 + strtotime ($next.'T120000');
                        $next   = date ('Ymd',$unix);   #echo __LINE__.' $key='.$key.' $thisDate='.$thisDate.' $next='.$next.PHP_EOL; 
                        } // eo inserting empty lines
# print current line
                echo '<tr><td><b style="cursor: pointer; color: blue; text-decoration: underline;" onclick="wuPostFunctionFile(\''.$key.'\')">'.$key.'</b></td>';
                $items  = explode (',',$data);
                foreach ($items as $item)
                     {  echo '<td> '.$item.' </td>'; }
                echo '</tr>'.PHP_EOL;
# set pointers for next line to print
                $unix   = $line_time + 24 * 3600;
                $next   = date ('Ymd',$unix);
                } // eo for each row
        echo '</table><br /><br /><br /><br />
<script>
function wuPostStepBack() {
        var form = document.getElementById("xyz");
        form.selected.value = "";
        form.submit.click();}
        
function wuPostFunctionFile(a) {
        var form = document.getElementById("xyz");
        form.YMD.value = a;
        form.submit.click();}
</script>       
</body>
</html>';
return;  
}

if (1 == 1) { 
        if (is_array($arr) && array_key_exists($YMD,$arr) ){ } 
        else {  $file   = $wudata.'/'.$selected;
                $arr    = unserialize (file_get_contents ($file) );}
        $items  = explode (',',$arr[$YMD].',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,');
        if ($items[0] == '') {$items[0]= substr($YMD,0,4).'-'.substr($YMD,4,2).'-'.substr($YMD,6,2);}
        echo 'Update your values for '.$items[0].' or<b style="cursor: pointer; color: blue; text-decoration: underline;" 
                onclick="wuPostStepBack()"> go back </b>to select another date to modify..
<br />
<form id="xyz" method="post" action="_wu_upd.php" style="display: block">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="passwd" value="'.$pass.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="selected" value="'.$selected.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="YMD" value="'.$YMD.'">
<input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="return[0]" value="'.$items[0].'">

<table style="">';
     #   echo '<tr><td colspan="4" style="text-align: left;">Date of this record = <b> '.$items[0],'</b></td></tr>'.PHP_EOL;
        echo '<tr><th style="width: 120px;">weather-data</th><th> high </th><th> Average </th><th> Low </th></tr>'.PHP_EOL;
        echo '<tr><td>Temperature</td>'
        .'<td style="width: 120px;"><input type="text" name="return[1]"  value="'.$items[1].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[2]"   value="'.$items[2].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[3]"   value="'.$items[3].'"></td></tr>'
        .'</tr>'.PHP_EOL;
        echo '<tr><td>Dewpoint</td>'
        .'<td style="width: 120px;"><input type="text" name="return[4]"   value="'.$items[4].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[5]"   value="'.$items[5].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[6]"   value="'.$items[6].'"></td>'
        .'</tr>'.PHP_EOL;
        echo '<tr><td>Humidity</td>'
        .'<td style="width: 120px;"><input type="text" name="return[7]"   value="'.$items[7].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[8]"    value="'.$items[8].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[9]"    value="'.$items[9].'"></td>'
        .'</tr>'.PHP_EOL;
         echo '<tr><td>Barometer</td>'
        .'<td style="width: 120px;"><input type="text" name="return[10]"  value="'.$items[10].'"></td>'
        .'<td style="width: 120px;"> n/a </td>'
        .'<td style="width: 120px;"><input type="text" name="return[11]"   value="'.$items[11].'"></td>'
        .'</tr>'.PHP_EOL;
         echo '<tr><td>Wind speed</td>'
        .'<td style="width: 120px;"><input type="text" name="return[12]"  value="'.$items[12].'"></td>'
        .'<td style="width: 120px;"><input type="text" name="return[13]"   value="'.$items[13].'"></td>'
        .'<td style="width: 120px;"> n/a </td></tr>'.PHP_EOL;
         echo '<tr><td>Gust speed</td>'
        .'<td style="width: 120px;"><input type="text" name="return[14]"  value="'.$items[14].'"></td>'
        .'<td style="width: 120px;"> n/a </td>'
        .'<td style="width: 120px;"> n/a </td>'
        .'</tr>'.PHP_EOL;
         echo '<tr><td>Precipitation Sum</td>'
        .'<td style="width: 120px;"><input type="text" name="return[15]"  value="'.$items[15].'"></td>'
        .'<td style="width: 120px;"> n/a </td>'
        .'<td style="width: 120px;"> n/a </td>'
        .'</tr>'.PHP_EOL;
        echo '<tr><td colspan="4" style="background-color: green;"><input type="submit" style="width: 200px; margin 0 auto;" name="submit" class="button" value="Save your changes"></td></tr>'.PHP_EOL;
        echo '</table></form>'.PHP_EOL;
        echo '<script>
function wuPostStepBack() {
        var form = document.getElementById("xyz");
        form.YMD.value = "";
        form.submit.click();}
</script>'.PHP_EOL;

} // eo update form
#
function showForm ($msg="LOGIN")# to display password form
     {  global $password;
        #if (isset ($_POST)) {echo '<!-- '.print_r($_POST,true) .$password.' -->';}
        echo  '<div style = " width:600px; margin:20px; color:rgba(24, 25, 27, 1.000); border:solid 1px grey; padding:10px; border-radius:4px;" >
<div style="text-align: center;"><br />'. $msg.'<br /><br />
<a href="./index.php">Back to the main page</a><br /><br />
<form action = "_wu_upd.php" method="post" name="pwd" > 
Enter your Easyweather password to continue<br /><br />
<center>
    <input name = "passwd" type= "password"  style="width: 120px;" />  
    <input type = "submit" name= "submit_pwd" value="Login" style="width: 60px;" /> 
</center>         
</form>
</div>
</div>
</body>
</html>';
}

