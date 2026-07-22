<?php $scrpt_vrsn_dt  = 'PWS_winter.php|01|2023-10-31|';  # 2023 winter | release 2012_lts 
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ---    load snow data and all settings 
$scrpt          = 'PWS_snow.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include $scrpt; 
# ----------------------------- SCRIPT  SETTINGS
$width  = '840px';
# ------------------------------- enclosing html
$html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Snow update</title>
<style>
body {font-family: arial,sans-serif; }
table {border: 0px solid black;}
table, tr,th, td {  padding: 2px; padding-left: 4px; font-size: 12px;}
th {cursor: n-resize;}
.snow  {width: 400px; margin: 0 auto;}
.snow input {
    width: 80px;
    padding: 3px;
    margin: 3px;
    text-align: right;
</style>
<body style="width: '.$width.'; margin: 0 auto;">';
#
if (isset ($_REQUEST['test']) ) { $test = '_test.php?test=';} else {$test = '';}
unset ($_POST['passwd']);  # echo '<pre>'.print_r($_POST,true).'</pre>';
#
# ------------------ password used, is it valid?             
if (isset($_POST['submit_pwd']))
     {  if (isset($_REQUEST['passwd']) ) {$pass = $_REQUEST['passwd']; } else {$pass = '';} 
        if ($pass != $password )  
             {  echo $html;
                showForm('<b style="color: red;">We need a valid password</b>');   
                exit;} 
        }
elseif (!isset($_POST['submit']) && !isset ($_POST['clear']))
     {  showForm("PWS_winter.php"); 
        exit; } 
#
# --------------------------- set default values
$text1  = $text2 = '';
$errors = false;
$y_clr  = $m_clr= $d_clr= $h_clr= $f_clr= 'initial';
$unix   = time();
$year   = date ('Y',$unix);
$month  = date ('m',$unix);
$day    = date ('d',$unix);
$height = '';
$fall   = '';
$max_h  = 9999;

#
function generate_txt ()
     {  global $snow_file, $arr_snw, $errors;
        $string = '#YYYY|MM|DD|UNIT|HEIGHT|PLUS |'.PHP_EOL;
        foreach ($arr_snw as $key => $arr)
             {  if ($key == '99999999') {break;}
                $y      = substr($arr['c_date'],0,4); 
                $m      = substr($arr['c_date'],4,2);
                $d      = substr($arr['c_date'],6,2);
                $string .= '|'.$y.'|'.$m.'|'.$d.'|dft|'.$arr['c_hght'].'|'.$arr['c_fall'].PHP_EOL;}  # echo '<pre>'.$string; 
        $temp_fl= './jsondata/snow.tmp';
        $return = file_put_contents ($temp_fl,$string);  
        if ($return == false)
             {  $message= '<b style="color: red;">Updates could not be saved</b>';  
                $errors.= $message; }
        else {  if (is_file($snow_file) ) 
                     {  rename  ($snow_file , $snow_file.'old');}
                rename  ($temp_fl   , $snow_file);}
        } // eof generate_txt
#
if ( isset($_POST['submit']) && !password_verify($password,$_POST['hash']) )
    {   die ('<h1>Error 404 - Not found</h1><p class="head" style="width: 800px;">Sorry, 
The current data could not be processed.
<br />The server logged this problem and we will try to find the cause of this problem.
<br />
<br />If this problem persists write a post at the support forum and mention this error_code '.__LINE__.' 
<br />and the date/time. '.date ('c'));}
#
if ( isset($_POST['submit'])  )
     {  $year   = (int)  trim($_POST['snow_year']);  # => 2020
        $month  = (int)  trim($_POST['snow_month']);  # => 12
        $day    = (int)  trim($_POST['snow_day']);  #  => 28
        $height = (float) trim($_POST['snow_height']);  #  => 5
        $fall   = (float) trim($_POST['snow_fall']);  #  => 7
        if ($year <> $this_year && $year <> $last_year)
             {  $y_clr  = 'red';
                $errors.= 'Year not in this snow-season. ';}
        if ($month < 1 || $month > 12)
             {  $m_clr  = 'red';
                $errors.= 'Month invalid. ';}
        elseif (! in_array ($month,$arr_snow_months))
             {  $m_clr  = 'red';
                $errors.= 'Month not in snow season. ';}
        $correct= checkdate ($month, $day,  $year);
        if ($correct == false)
             {  $d_clr  = 'red';
                $errors.= 'Day invalid. ';}
        if ($errors == false)
             {  $date   = $year.substr('0'.$month,-2).substr('0'.$day,-2); # echo '$date='.$date.' $this_today='.$this_today; exit;
                if ($date > $this_today)
                     {  $y_clr  = $m_clr= $d_clr  = 'red';
                        $errors = 'Date in the future. ';}
                }
        if ($height < 0 || $height > $max_h)
             {  $h_clr  = 'red';
                $errors.= 'Measured height invalid? ';}
        if ($fall < 0 || $fall > $max_h)
             {  $f_clr  = 'red';
                $errors.= 'Fresh snowfall invalid? ';}
        }
if ( isset($_POST['submit']) &&  $errors == false)
     {  $key    = $year.substr('0'.$month,-2).substr('0'.$day,-2);
        if (array_key_exists($key,$arr_snw) )
             {  $arr    = $arr_snw[$key];
                if ($height == $arr['c_hght']  &&  $fall == $arr['c_hght'] )
                     {  $errors = 'Weather measurement already valid '; }
                }
        
        } 
if ( isset($_POST['submit']) &&  $errors == false)
     {  $arr_snw[$key]= array('c_date' => $key, 'c_hght' => $height, 'c_fall' => $fall);  
        ksort ($arr_snw);  # echo '<pre>'.print_r($arr_snw,true); 
        generate_txt ();
        $scrpt          = 'PWS_snow.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include $scrpt; 
        $text1  = $text2 = '';
        $errors = false;
        $y_clr  = $m_clr= $d_clr= $h_clr= $f_clr= 'intial';
        $unix   = time();
        $year   = date ('Y',$unix);
        $month  = date ('m',$unix);
        $day    = date ('d',$unix);
        $height = '';
        $fall   = '';
        } 
         
if (isset ($_POST['clear'])  )
     {  #echo __LINE__.'<pre>'.print_r($_POST,true).'</pre>'; 
        $year   = (int)  trim($_POST['snow_year']);  # => 2020
        $month  = (int)  trim($_POST['snow_month']);  # => 12
        $day    = (int)  trim($_POST['snow_day']);  #  => 28
        $key    = $year.substr('0'.$month,-2).substr('0'.$day,-2); 
        if (array_key_exists($key,$arr_snw) ) { unset ($arr_snw[$key]);}
        generate_txt ();
        $scrpt          = 'PWS_snow.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include $scrpt; 
        $text1  = $text2 = '';
        $errors = false;
        $y_clr  = $m_clr= $d_clr= $h_clr= $f_clr= 'intial';
        $unix   = time();
        $year   = date ('Y',$unix);
        $month  = date ('m',$unix);
        $day    = date ('d',$unix);
        $height = '';
        $fall   = '';
        }
if (isset($arr_snw) && is_array($arr_snw) && array_key_exists('99999999',$arr_snw) )
     {  $arr    = $arr_snw['99999999'];  #echo '<pre>'.print_r($arr,true).'</pre>';
        $last   = substr($arr['c_date'],0,4).'-'.substr($arr['c_date'],4,2).'-'.substr($arr['c_date'],6,2);}
else {  $arr    = array();
        $arr_snw= array();
        $arr['c_hght'] = 0;
        $last   = 'no snow yet';}
#
if ($errors <> '') {$message = '<p style="text-align: center;">'.$errors.'</p>';} else {$message = '';}
echo $html.$message.'
<div>
<table style="margin: 0 auto;">
<tr style="height: 300px;">
<td style="border: 1px solid black; vertical-align: top;">
<p style="text-align: center;"><br />Current snow-height: '.$arr['c_hght'].'<br />Last snow-fall on '.$last.'</p>
    <div class="snow">
    <form method="post" id="snow_form" action="'.$test.basename(__FILE__).'">
        <input type="text" id="snow_year" name="snow_year" maxlength="4" style="border-color: '.$y_clr.';"
                value="'.$year.'">
        <label>Year</label><br>

        <input type="text" id="snow_month" name="snow_month" maxlength="2" style="border-color: '.$m_clr.';"
                value="'.$month.'">
        <label>Month</label><br>

        <input type="text" id="snow_day" name="snow_day" maxlength="2" style="border-color: '.$d_clr.';"
                value="'.$day.'">
        <label>Day</label><br>

        <input type="text" id="snow_height" name="snow_height" maxlength="6" style="border-color: '.$h_clr.';"
                value="'.$height.'">
        <label>Current snow height</label><br>

        <input type="text" id="snow_fall" name="snow_fall" maxlength="6" style="border-color: '.$f_clr.';"
                value="'.$fall.'">
        <label>Snowfall since last measurement (<b>Optional</b>)</label><br>
        <br>
        
        <input type="submit" id="snow_ok" name="submit" value="Save your changes" style="text-align: center; float: left;  width: 40%;">
        <input type="submit" id="snow_xx" name="clear"  value="Remove data"       style="text-align: center; float: right; width: 40%;">
        <input type="hidden" name="hash" value="'.password_hash($password, PASSWORD_DEFAULT).'">';
if (isset ($_REQUEST['test']) ) 
     {  echo '
        <input type="hidden" style="padding: 0px; border: 0px; margin: 0px;" name="test" value="test">';}
echo '
    </form>
</div>
</td>
<td style="max-height: 300px; border: 1px solid black; display: block; overflow-y: scroll;">
<table style="height: 300px; text-align: center; vertical-align: top; border: 0px solid black;">
<tr><th>YYYY-MM-DD</th><th>Height</th><th>Fresh</th><th>Melt</th></tr>'.PHP_EOL;
foreach ($arr_snw as $key => $arr)
     { #echo '<pre>'.print_r($arr,true).'</pre>';
        if ($key == '99999999') {continue;}
        $date   = $arr['c_date'];
        $string = '<tr><td>'.substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2).'</td>
<td>'.$arr['c_hght'].'</td>
<td>'.$arr['c_fall'].'</td>
<td>'.$arr['c_mlt'].'</td>
</tr>'.PHP_EOL;
        echo $string;
}

echo '</table>
</td>
</tr>
</table>
</div>
</body>
</html>';

# 
function showForm ($msg="LOGIN")# to display password form
     {  global $password, $test;
        #if (isset ($_POST)) {echo '<!-- '.print_r($_POST,true) .$password.' -->';}
        echo  '<div style="border: none; text-align: center;"><br />'. $msg.'<br /><br />
<a href="./index.php">Back to the main page</a><br /><br />
<div style = " width:600px; margin:0 auto; color:rgba(24, 25, 27, 1.000); border:solid 1px grey; padding:10px; border-radius:4px;" >
<form action = "'.$test.basename(__FILE__).'" method="post" name="pwd" > 
Your Easyweather setup password<br /><br />
<center>
    <input name = "passwd" type= "password"  class = "input" />  
    <input type = "submit" name= "submit_pwd" value="Login" class="btn" /> ';
if (isset ($_REQUEST['test']) ) 
     {  echo '
    <input type="hidden" id="test" name="test" value="test">';}
echo '
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
