<?php $scrpt_vrsn_dt  = 'fct_wxsim_popup_daily.php|01|2020-11-04|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$norain                 = '-';
$nouv                   = '-';
$color                  = "#FF7C39";    // important color
$clrwrm                 = "#FF7C39";    // warm / daytime color
$clrcld                 = "#01A4B4";    // cold
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
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                            For Saratoga script 
#-----------------------------------------------
$SITE['defaultlang']    = substr($used_lang,0,2);   // setting for Saratoga parser
#-----------------------------------------------
$round_crnr             = 5;    // for uv-nr in square or round background
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;} 
#-----------------------------------------------
# load general WXSIM code which loads the fct also
#-----------------------------------------------
$scrpt          = './fct_wxsim_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#---------------------  check if fct is complete
if (!isset ($arr_pp) || count ($arr_pp) < 6 ) 
     {  echo '<b style="color: red;"><small>wxsim file not ready</small></b>'; return;}
#
#-----------------------   language translations
$ltxt_fct_for   = lang('Forecast for');
$ltxt_fct_by    = lang('by');
$ltxt_fct_updtd = lang('Updated');
$ltxt_clsppp    = lang('Close');
#-----------------------------------------------
#                     arrays to drive the script
#-----------------------------------------------#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$fll_uv  = array();     // uv-levels with the correct color
$fll_uv[0]  = $b_clrs['green'];
$fll_uv[1]  = $b_clrs['green'];
$fll_uv[2]  = $b_clrs['green'];
$fll_uv[3]  = $b_clrs['yellow'];
$fll_uv[4]  = $b_clrs['yellow'];
$fll_uv[5]  = $b_clrs['yellow'];
$fll_uv[6]  = $b_clrs['orange'];
$fll_uv[7]  = $b_clrs['orange'];
$fll_uv[8]  = $b_clrs['red'];
$fll_uv[9]  = $b_clrs['red'];
$fll_uv[10] = $b_clrs['red'];
$fll_uv[11] = $b_clrs['maroon'];
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
$ltxt_url       = $ltxt_fct_for.' '.$arr_pp[0]['city'].' '.$ltxt_fct_by.' '.$arr_pp[0]['station'];
$ltxt_updated   = '<small class="invisible" style=" padding-top: 2px; color: '.$color.'; float: right;">'.$ltxt_fct_updtd.': '.$arr_pp[0]['updated'].'&nbsp;&nbsp;</small>';
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.$ltxt_url.'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; " >
'.$close
.'      <span style="color: '.$color.'">'.$ltxt_url.'</span>
      '.$ltxt_updated.'
    </div>'.PHP_EOL;
$rows           = count ($arr_pp);
$cl_cntnt       = array ('part','icnc','cond','temp','rain','wspd','idir','uvuv');
$cols           = count($cl_cntnt);
$w_clc_unit     = $wxsim_wnd    = 'km/h';
$wunit          = '<small>'.lang($w_clc_unit).'</small>';
$t_clc_unit     = $wxsim_tmp    = 'C';
$runit          = $wxsim_rn     = 'mm';
$cl_headers     = array ('part' => '', 'icnc' => '', 'cond'=> lang('Conditions'),
                        'temp' => lang('Temp'), 'feel' => lang('Feels'), 'rain' => lang('Precipitation'),
                        'wspd' => lang('Windspeed'),'idir' => lang('Direction'),'uvuv' => lang('UV index') );
#
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse; ">
<tr style="border-bottom: 1px grey solid; ">';  // print start enclosing div table
#
for ($i = 0; $i < $cols; $i++)
     {  $code   = $cl_cntnt[$i];
        echo PHP_EOL.'<th>'.$cl_headers[$code].'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
#
for ($n = 0; $n < $rows; $n++) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';
        $arr    = $arr_pp[$n];
        if ($arr['tphl'] <> 'blue')     // day or night 
             {$color = $clrwrm; } else {$color = $clrcld ;}  
        for ($i = 0; $i < $cols; $i++)  // for every data value = column we want to print
            {   $content        = $cl_cntnt[$i];
                switch ($content){
                    case 'part': 
                        echo PHP_EOL.'<td style=" text-align: right;">';    // if extra style is needed
                        $text   = $arr['part']; #str_replace(' ','<br />',$arr['part']);
                        echo '<span style="color: '.$color.';">'.$text.'</span>';
                        break;
                    case 'icnc':
                        echo PHP_EOL.'<td>';
                        echo '<img src="'.$arr['icDS'].'" width="60" height="20" alt="'.$arr['icDS'].'" style="vertical-align: top;"> ';
                        break;
                     case 'cond': 
                        echo PHP_EOL.'<td>';
                        $cond   =  $arr['cond'];
                        list ($cond,$cold)   = explode('<br/>',$cond.'<br/>');
                        $cond   = str_replace('<br>',' ',$cond);
                        $cond   = str_replace('blue','#01A4B4',$cond);
                        $cond   = str_replace('red', '#FF7C39',$cond);
                        echo $cond;
                        break;
                    case 'temp': 
                        echo PHP_EOL.'<td>';
                        echo '<span style="font-size: 18px; color: '.$color.';">'.$arr['temp'].'<small>&deg;</small></span>'; 
                        break;
                    case 'feel':
                        echo PHP_EOL.'<td>';
                        $value = '';
                        if (trim($arr['chll']) <> '') 
                             {  $value  = '<span style="font-size: 20px; color: '.$clrcld.';">'.$arr['chll'].'<small>&deg;</small></span>'; }
                        elseif (trim($arr['heat']) <> '') 
                             {  $value  = '<span style="font-size: 20px; color: '.$clrwrm.';">'.$arr['heat'].'<small>&deg;</small></span>'; }
                        elseif (trim($arr['hmdx']) <> '') 
                             {  $value  = '<span style="font-size: 20px; color: '.$clrwrm.';">'.$arr['hmdx'].'<small>&deg;</small></span>'; }
                        echo $value;
                        break;
                    case 'rain': 
                        echo PHP_EOL.'<td>';
                        $rain   = $arr['rain'];
                        if (trim($rain) == '') {echo $norain; break;}
                        $unit   = lang($arr['runt']);
                        echo $rain.'<small>'.$unit.'</small>';
                        $pop    = $arr['popp'];
                        if (trim($pop) <> '') echo ' <small>'.$pop.'%</small>';
                        break;              
                    case 'wspd': 
                        echo PHP_EOL.'<td>';
                        echo $arr['wspd'];
                        if (trim($arr['gust']) <> '') {echo '-'.$arr['gust'];}
                        echo  ' '.lang($arr['wunt']); 
                        break;
                    case 'idir': 
                        echo PHP_EOL.'<td>';
                        $dir    = $arr['idir'];
                        echo '<img src="img/windicons/'.$dir.'.svg" width="18" height="18" alt="'.$dir.'"  style="vertical-align: bottom;"> ';
                        echo lang($dir);
                        break;
                    case 'uvuv': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($arr['uvuv']);
                        if ($value == '') 
                             {  echo '-';
                                break;}
                        $value  = (int) $value;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $value.'</div>';
                        break;
       
                    default: echo $n.'-'.$i.'-'.$content;
                }              
                echo '</td>';
                } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '</table>
</div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
# style is printed in the header 
function my_style()
     {  global $popup_css , $round_crnr;
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
                    border: 1px solid #FFFFFF;} '.PHP_EOL;   
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
