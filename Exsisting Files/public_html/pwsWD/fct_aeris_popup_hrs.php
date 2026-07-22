<?php $scrpt_vrsn_dt  = 'fct_aeris_popup_hrs.php|01|2020-11-04|';  # release 2012_lts
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
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#
$ltxt_clsppp    = lang('Close');
#
$round_crnr             = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;}   
#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
$fll_uv  = array();
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
# -----------------   load general Aeris fct code
$scrpt          = 'fct_aeris_shared_hrs.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return         = include_once $scrpt; 
#
#
if ($return == false) { return false;}  
#
# ------------------------- translation of texts
$ltxt_url       = lang('Forecast');
#
$rows           = count ($parts);
$windlabel      = array ("North","NNE", "NE", "ENE", "East", "ESE", "SE", "SSE", "South",
         "SSW","SW", "WSW", "West", "WNW", "NW", "NNW");
$cl_headers     = array ('part' => '', 
                         'icon' => '', 
                         'desc' => lang('Conditions'),
                         'temp' => lang('Temperature'), 
                         'rain' => lang('Precipitation'), 
                         'w_ft' => lang('Windspeed'),
                         'wdir' => lang('Direction'), 
                         'uvuv' => lang('UV index'), 
                         'baro' => lang('Pressure')  );
$cl_cntnt       = array ('part','icon','temp', 'dewp', 'feel', 'humi', 'uvuv', 'r_ch', 'rain',
                         'baro','clds','wdir', 'wspd', 'w_ft', 'desc');

/*  [isDay] => 1
    [part] => Vandaag
    [temp] => 10
    [dewp] => 0
    [feel] => 7
    [humi] => 67
    [uvuv] => 3
    [r_ch] => 0
    [rain] => 0
    [baro] => 1016
    [clds] => 30
    [wdir] => W
    [wspd] => 15
    [w_ft] => 12-18
    [desc] => Mostly Sunny
    [icnx] => pcloudy.png
    [icon] => mc_day  */
# -------   check if all data is present
$cols           = count($cl_cntnt);
$arr            = $parts[0]; #echo  '<pre>'.print_r($arr,true).'</pre>';  exit;
foreach ($cl_headers as $item => $text)
     {  if (!array_key_exists($item,$arr) ) 
             {  unset ($cl_headers[$item]);}
        }
$cols           = count($cl_headers);
$norain         = '-';
$nouv           = '-';
$icn_prefix     = './pws_icons/';
$icn_post       = '.svg';
$color          = 
$clrwrm         = "#FF7C39";
$clrcld         = "#01A4B4";
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
'.$close.'
    </div>'.PHP_EOL;
echo '<div class= "div_height" style="width: 100%;text-align: left; overflow: auto;">
<table class= "div_height font_head"  style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">
<tr style="border-bottom: 1px grey solid; ">';

foreach ($cl_headers as $key => $header)
     {  echo PHP_EOL.'<th>'.$header.'</th>';}// print   the table headers
echo PHP_EOL.'</tr>'.PHP_EOL;
#
for ($n = 0; $n < $rows; $n++) // print 1 row / daypart with all data in coloms
     {  echo '<tr style="border-bottom: 1px grey solid; ">';
        $arr    = $parts[$n]; #  echo  '<pre>'.print_r($arr,true); exit;        
        if ($arr['isDay']) {$color = $clrwrm; } else {$color = $clrcld;}
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   if (isset ($arr[$key]) ) 
                     {  $content  = $arr[$key]; } 
                else {  $content = 'n/a';}
                switch ($key){
                    case 'part': 
                        $from   = date ($timeFormatShort,$arr['Utime']);
                        $to     = date ($timeFormatShort,$arr['Utime']+ 3600);
                        echo PHP_EOL.'<td><span style="color: '.$color.';">'.$from.' - '.$to.'</span>'; 
                        break;
                    case 'icon':
                        $icon   = $icn_prefix.$content.$icn_post;
                        echo PHP_EOL.'<td><img src="'.$icon.'" width="60" height="32" alt="'.$content.'" style="vertical-align: top;">';
                        break;
                    case 'desc': 
                        echo PHP_EOL.'<td>'.$content;
                        break;
                    case 'temp': 
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$color.';">'.$content.'&deg;</span>';
                        break;
                    case 'rain': 
                        echo PHP_EOL.'<td>';
                        $change = (int)   $arr['r_ch']; 
                        $amount = (float) $arr['rain']; 
                        if (trim($change) == '' || $change == 0 || $amount == 0) 
                             {  echo $norain; 
                                break; }
                        echo  $amount.'<small> '.lang($rainunit). ' '.$change.'%</small>';
                        break;              
                    case 'w_ft':  
                        echo PHP_EOL.'<td>'.$content.'<small> '.lang($windunit).'</small>';
                        break;
                    case 'wdir': 
                        echo PHP_EOL.'<td>';
                        echo '<img src="img/windicons/'.$content.'.svg" width="20" height="20" alt="'.$content.'"  style="vertical-align: bottom;"> ';
                        echo lang($content);
                        break;
                    case 'uvuv': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' || $value == '0')   
                             {  echo $nouv;  break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;
                    case 'baro':
                        echo PHP_EOL.'<td>'.$content.'<small> '.lang($pressureunit).'</small>';
                        break;         
                   # default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
                } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '<tr><td  colspan="'.$cols.'">
<span style="float: right; font-size: 10px;"><a href="https://www.aerisweather.com/" target="_blank" style="color: grey">
'.lang("Powered by").':&nbsp;&nbsp;www.aerisweather.com </a></span>
</tr></table>
</div>'.PHP_EOL;
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
                    border: 1px solid #FFFFFF;} '.PHP_EOL;   
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
