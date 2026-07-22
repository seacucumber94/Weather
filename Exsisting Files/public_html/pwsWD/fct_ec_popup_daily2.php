<?php  $scrpt_vrsn_dt  = 'fct_ec_popup_daily.php|01|2023-09-09|';  # PHP 8.2 update to one popup | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
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
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
# ---------------------------- load all settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ---------------------------  general functions   
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#-----------------------------------------------
$EC_area	= $alarm_area;
# ---------------------------------- for testing
$province       = 'AB'; #
$EC_area	= 's0000047';   
# ---------------------------------- for testing
# 
# ----------------------   load general EC code
$scrpt          = 'fct_ec_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
$return = include_once $scrpt; 
if ($return == false) { echo 'script ends'; return false;}  
# --------------------------      other settings
$color          = $clrwrm = "#FF7C39";
$clrcld         = "#01A4B4";
if ($EC_region <> '') { $EC_region = ' - '.$EC_region;}
$round_crnr     = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     { $round_crnr     = 50;}   
#
# normally we use the easyweather settings
if (!isset ($show_close_x)|| $show_close_x === true)   
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.lang('Close').'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>E.C. Forecast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%;" >
'.$close.'        <span class="tab" style="margin: 0 auto;">
            <label class="tablinks"          onclick="openTab(event, \'Texts\')" id="defaultOpen">'     .lang('Texts').'</label>';
if (isset ($display_graph) && $display_graph == true)
     { echo '
            <label class="tablinks active"   onclick="openTab(event, \'containerTemp\')" >'             .lang('Graph').'</label>'; }
echo '
            <label class="tablinks"          onclick="openTab(event, \'Forecast\')" >'                  .lang('Forecast').'</label>
            <label class="tablinks"          onclick="openTab(event, \'Hourly\')">'                     .lang('Hourly forecast').'</label>
        </span>
        <small style="float: right">'
                .lang("Powered by").':&nbsp;&nbsp;' 
                .'<a href="https://weather.gc.ca/canada_e.html" target="_blank" style="color: grey">'
                .'<b>Environment Canada</b></a>&nbsp;&nbsp;
        </small> 
    </div>'.PHP_EOL;
# start of different tabs contents, all in 1 div
echo '<div id="div_height" class= "div_height" style="width: 100%; text-align: left; overflow: auto;">'.PHP_EOL;
# ------daily PARTS  / MOS TABLE
if (1==1) {
$cl_cntnt       = array ('part','icnc','cond','humi','temp','feel', 'rain','wspd','idir');
$cols           = count($cl_cntnt);
$w_clc_unit     = $wxsim_wnd    = 'km/h';
$wunit          = '<small>'.lang($w_clc_unit).'</small>';
$t_clc_unit     = $wxsim_tmp    = 'C';
$runit          = $wxsim_rn     = 'mm';
$cl_headers     = array ('part' => '', 'icnc' => '', 'cond'=> lang('Conditions'),
                        'temp' => lang('Temp'), 'feel' => 'Feels', 'rain' => lang('Precipitation'),
                        'wspd' => lang('Windspeed'),'idir' => lang('Direction'),'humi' => lang('Humidity') );
$norain         = '-';
#
#echo '<div class= "tabcontent div_height" id="Forecast"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
echo '<table class= "tabcontent div_height font_head" id="Forecast"
         style=" width: 100%; height: 100%;  margin: 0px auto; text-align: center; border-collapse: collapse; ">
<tr style="border-bottom: 1px grey solid; ">';  // print start enclosing div table
#
for ($i = 0; $i < $cols; $i++)
     {  $code   = $cl_cntnt[$i];
        echo PHP_EOL.'<th>'.$cl_headers[$code].'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
/*
    [period] => Thursday
    [daypart] => Today
    [text] => Periods of snow and local blowing snow. Amount 2 cm. Wind northwest 30 km/h gusting to 50. High minus 25. Wind chill near minus 43. Risk of frostbite.
    [condtext] => Periods of snow
    [condicon] => 16
    [temperature] => -25
    [temptype] => high
    [windChill] => -43
    [windspeed] => 20
    [windgust] => 40
    [windunit] => km/h
    [winddeg] => 32
    [winddir] => NW
    [preciptype] => snow
    [precipacc] => 2
    [precipunit] => cm
    [humidity] => 60*/
foreach ($fcts_arr as $arr) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';    #   echo '<pre>'.print_r($arr,true); exit;
        if ($arr['temptype'] <> 'low') 
             {  $color = $clrwrm; } else {$color = $clrcld; }  
        for ($i = 0; $i < $cols; $i++)  // for every data value = column we want to print
            {   $content        = $cl_cntnt[$i];
                switch ($content)
                  { case 'part': 
                        if ($ec_e_f <> 'f') 
                             {  $txts   = explode (' ',$arr['daypart']);
                                $text   = '';
                                foreach ($txts as $str) {$text .= lang($str).' ';}}
                        else {  $text   = $arr['daypart'];} 
                        echo PHP_EOL.'<td>';
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icnc':
                        $key    = (int)$arr['condicon'];
                        $icon   = EC_icon ($key);
                        echo PHP_EOL.'<td>';
                        echo '<img src="'.$icon.'" width="60" height="20" alt="'.$arr['condtext'].'" style="vertical-align: top;"> ';
                        break;
                     case 'cond': 
                        echo PHP_EOL.'<td>';
                        $cond   =  $arr['condtext'];  // ##### translate  ??
                        echo $cond;
                        break;
                    case 'temp': 
                        echo PHP_EOL.'<td>';
                        echo '<span style="font-size: 18px; color: '.$color.';">'.$arr['temperature'].'<small>&deg;</small></span>'; 
                        break;
                    case 'feel':  ## add heat hudx ??
                        echo PHP_EOL.'<td>';
                        $value = '';
                        if (trim($arr['windChill']) <> '' && $arr['windChill'] <> 0) 
                             {  $value  = '<span style="font-size: 18px; color: '.$clrcld.';">'.$arr['windChill'].'<small>&deg;</small></span>'; }
                        echo $value;
                        break;
                    case 'rain': 
                        echo PHP_EOL.'<td>';
                        $precip = $arr['preciptype'];
                        $amount = (int) $arr['precipacc']; 
                        if (!isset ($arr['precipunit']) )
                             {  $unit   = '';}
                        else {  $unit   = lang($arr['precipunit']);}
                        if ($precip == '' && $amount == 0 ) {echo $norain; break;}               
                        if ($precip == 'snow' || $precip == 'neige') 
                             {  echo $snowflakesvg;} else { echo $rainsvg;}
                        if ($amount <> 0)
                             {  echo ' '.$amount.'<small>'.$unit.'</small>';  }
                        break;
                    case 'wspd': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  echo $arr['windspeed'];
                                if (trim($arr['windgust']) <> '' && $arr['windgust'] <> 0) 
                                     {  echo '-'.$arr['windgust'];}
                                echo  $wunit; }
                        else {  echo $norain;}
                        break;
                    case 'idir': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  $dir    = $dirt = $arr['winddir'];
                                if ($ec_e_f == 'f' && $dir <> '') {$dir = $ec_french_winddir[$dir];}
                                if ($dir <> 'VR')
                                     {  echo '<img src="img/windicons/'.$dir.'.svg" width="20" height="20" alt="'.$dir.'" style="vertical-align: bottom;"> ';
                                        echo $dirt;}
                                else {  echo lang('VR');}}
                        else {  echo ' ';}
                        break;
                    case 'humi':
                        echo PHP_EOL.'<td>';
                        echo $arr['humidity'].'%';
                     break; 
                } // eo switch
                echo '</td>';
                } // eo coloms
        echo '</tr>'.PHP_EOL;
        } // eo for each
echo '</table>'.PHP_EOL;
} 
# -----------end of  daily PARTS  
# ----------------- 1 HOUR PARTS 
if (1 ==1) {
#echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
$cl_cntnt       = array ('time','icnc','cond','temp','feel', 'rain','wspd','idir');
$cols           = count($cl_cntnt);
$w_clc_unit     = $wxsim_wnd    = 'km/h';
$wunit          = '<small>'.lang($w_clc_unit).'</small>';
$t_clc_unit     = $wxsim_tmp    = 'C';
$runit          = $wxsim_rn     = 'mm';
$cl_headers     = array ('time' => '', 'icnc' => '', 'cond'=> lang('Conditions'),
                        'temp' => lang('Temp'), 'feel' => 'Feels', 'rain' => lang('Precipitation'),
                        'wspd' => lang('Windspeed'),'idir' => lang('Direction'),'humi' => lang('Humidity') );
$norain         = '-';
echo '<table id="Hourly" class= "tabcontent div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
<tr style="border-bottom: 1px grey solid; ">';  // print start enclosing div table
#
for ($i = 0; $i < $cols; $i++)
     {  $code   = $cl_cntnt[$i];
        echo PHP_EOL.'<th>'.$cl_headers[$code].'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
/*
            [time] => 201902031700
            [condtext] => Chance of flurries
            [condicon] => 16
            [temperature] => -25
            [chill] => -30
            [humidex] => 0
            [windspeed] => 5
            [windunit] => km/h
            [windgust] => 0
            [winddir] => VR
            [lop] => 30
            [loptext] => Low
*/
$clrwrm = "#FF7C39";
$clrcld = "#01A4B4";
foreach ($dtls_arr as $arr) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';      #   echo '<pre>'.print_r($arr,true); exit;
        $color = $clrwrm;
        for ($i = 0; $i < $cols; $i++)  // for every data value = column we want to print
            {   $content        = $cl_cntnt[$i];
                switch ($content)
                  { case 'time': 
                        $t      = $arr['time'];
                        $time = gmmktime(substr($t,8,2),substr($t,10,2),0,substr($t,4,2),substr($t,6,2),substr($t,0,4));
                        $text = set_my_time($time,true); # substr($time,8,2).':'.substr($time,10,2);
                        echo PHP_EOL.'<td>'.$text;
                        break;
                    case 'icnc':
                        $key    = (int)$arr['condicon'];
                        $icon   = EC_icon ($key);
                        echo PHP_EOL.'<td>';
                        echo '<img src="'.$icon.'" width="60" height="20" alt="'.$arr['condtext'].'" style="vertical-align: top;"> ';
                        break;
                     case 'cond': 
                        echo PHP_EOL.'<td>';
                        $cond   =  $arr['condtext'];  // ##### translate  ??
                        echo $cond;
                        break;
                    case 'temp': 
                        echo PHP_EOL.'<td>';
                        $temp = convert_temp ($arr['temperature'],'C',$tempunit,0);
                        echo '<span style="font-size: 18px;">'.$temp.'<small>&deg;'.$tempunit.'</small></span>'; 
                        break;
                    case 'feel':  ## add heat hudx ??
                        echo PHP_EOL.'<td>';
                        $value = '';
                        if (trim($arr['chill']) <> '' && $arr['chill'] <> 0) 
                             {  $temp = convert_temp ($arr['chill'],'C',$tempunit,0);
                                $value  = '<span style="font-size: 18px; color: '.$clrcld.';">'.$temp.'<small>&deg;'.$tempunit.'</small></span>'; }
                        elseif (trim($arr['humidex']) <> '' && $arr['humidex'] <> 0) 
                             {  $temp = convert_temp ($arr['humidex'],'C',$tempunit,0);
                                $value  = '<span style="font-size: 18px; color: '.$clrcld.';">'.$temp.'<small>&deg;'.$tempunit.'</small><</span>'; }
                        echo $value;
                        break;
                    case 'rain': 
                        echo PHP_EOL.'<td>';
                        if ((int) $arr['lop'] <> 0) 
                             {  echo $arr['lop'].'%';}
                        else {  echo $norain;}
                        break;
                    case 'wspd': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  $wind   = convert_speed ($arr['windspeed'],'kmh',$windunit,0) ;
                                echo $wind;
                                if (trim($arr['windgust']) <> '' && $arr['windgust'] <> 0) 
                                     {  $wind   = convert_speed ($arr['windgust'],'kmh',$windunit,0) ;
                                        echo '-'.$wind;}
                                echo  ' '.$windunit; }
                        else {  echo $norain;}
                        break;
                    case 'idir': 
                        echo PHP_EOL.'<td>';
                        if (trim($arr['windspeed']) <> '' && $arr['windspeed'] <> 0) 
                             {  $dir    = $dirt = $arr['winddir'];
                                if ($ec_e_f == 'f' && $dir <> '') {$dir = $ec_french_winddir[$dir];}
                                if ($dir <> 'VR')
                                     {  echo '<img src="img/windicons/'.$dir.'.svg" width="20" height="20" alt="'.$dir.'" style="vertical-align: bottom;"> ';
                                        echo $dirt;}
                                else {  echo lang('VR');}}
                        else {  echo ' ';}
                        break;
                    case 'humi':
                        echo PHP_EOL.'<td>';
                        echo $arr['humidity'].'%';
                     break; 
                } // eo switch
                echo '</td>';
                } // eo coloms
        echo '</tr>'.PHP_EOL;
        } // eo for each
echo '</table>'.PHP_EOL;
}
# ---------- END OF 1 HOUR PARTS
# ------------------- TEXTS ONLY 
if (1 ==1) {  #echo '<pre>'.print_r($fcts_arr); exit;
echo '<table class= "tabcontent div_height font_head" id="Texts" 
        style=" width: 100%; height: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
';
foreach ($fcts_arr as $arr) // print 1 row / daypart with all data in coloms
     {  if ($arr['temptype'] <> 'low') 
             {  $color = $clrwrm; } else {$color = $clrcld; }
        $string = str_replace (' ','&nbsp;',  $arr['daypart']);
        echo '<tr style="border-bottom: 1px grey solid; ">
<td style="text-align: right; padding-right: 6px; color: '.$color.'">&nbsp;'
.$string.'&nbsp;&nbsp;</td>
<td style="text-align: left; ">'
.$arr['text'].'</td></tr>'.PHP_EOL;
        } // eo for each row
echo '</table>'.PHP_EOL;
}
# ------------ END OF TEXTS ONLY 

echo '</div>'.PHP_EOL;
echo '<script>
function openTab(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  if (cityName == "containerTemp") { document.getElementById(cityName).style.display = "flex";}
  else  { document.getElementById(cityName).style.display = "table";}
  evt.currentTarget.className += " active";
//  const g_width   = document.getElementsByTagName("body")[0].clientWidth;
//  const g_height  = document.getElementsByTagName("body")[0].clientHeight; 
//  alert (">"+g_width+"<>"+g_height+"<");
//  my_chart.setSize(g_width,g_height); 
//  my_chart.reflow();
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;

#
# style is printed in the header 
function my_style()
     {  global $popup_css ,$round_crnr;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css
        $return .= '
        .my_uv  { background-color: lightgrey;  margin: 0 auto; border-radius: '.$round_crnr.'%;
                    height: 20px; width: 20px;  color: #fff;
                    line-height: 20px;font-size: 16px;
                    font-family: Helvetica,sans-seriff;
                    border: 1px solid #FFFFFF;}
        .rTxt   { padding: 3px; text-align: right; float: right;  } 
        .fc_icon{ width: 20px; height: 20px; vertical-align: bottom;}
        .fc_day  { background-color: silver;}
        .fc_night{ background-color: grey; color: white; }
        .forecast table {border-collapse: separate; box-sizing: border-box;} 
        .forecast td {padding: 2px; } 
        .forecast img  {width: 30px; height: 30px;}
        .font_head {height: 14px;}
        .tab {
          overflow: hidden;
          text-align: center;
          border: 0px solid #ccc;
          height: 18px;
          padding-top: 3px;
        /* background-color: grey; */
        }
        /* Style the buttons inside the tab */
        .tab label {
         /* float: left; */
          border: none;
          outline: 1px solid black;
          cursor: pointer;
          padding: 0px 4px;
          transition: 0.3s;
      /*    font-size: 16px; */
          color: orange;
          background-color: grey;
        }
        /* Change background color of labels on hover */
        .tab label:hover {
          background-color: #000;
        }
        /* Create an active/current tablink class */
        .tab label.active {
          background-color: white;
          color: black;
        }
        /* Style the tab content */
        .tabcontent {
          display: none;
        /*  padding: 6px 12px; */
          border: 0px solid #ccc; 
          border-top: none;
        }  
';
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
