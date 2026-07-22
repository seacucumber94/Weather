<?php $scrpt_vrsn_dt  = 'fct_aeris_popup.php|01|2023-09-09|';   # PHP8.2 graph | release 2012_lts
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
# --------------------------------------
$ltxt_clsppp    = lang('Close');
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
# -----------------  load general Aeris daily fct code
$scrpt          = 'fct_aeris_shared2.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return         = include_once $scrpt; 
if ($return == false) { return false;}  
#
/*
if (!function_exists('windlabel') ) {
#----------------------------------------------- 
#      windlabel convert degrees to compass name   
#----------------------------------------------- 
$windlabel_dfld = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
                       'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
$windlabel_shrt = array ('N','NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S',
		        'SSW','SW', 'WSW', 'W', 'WNW', 'NW', 'NNW');
function windlabel($value, $short = false)
     {  global $windlabel_dfld, $windlabel_shrt;
        $degr   = (int) $value;
        $key    = (int) fmod((($degr + 11) / 22.5),16);   # 2022-03-29
        if ($short <> false)
             {  return $windlabel_dfld[$key];}
        else {  return $windlabel_shrt[$key];}
        }
}
#
if (!function_exists ('temp_color') ) {
        $temp_colors = array(
                '#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
                '#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
                '#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
                '#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
                '#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
                '#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
                '#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
                '#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',);
        $maxTemp        = count($temp_colors) - 1;

        function temp_color ( $value)
             {  global $tempunit, $maxTemp, $temp_colors;
                if ($value === 'n/a' || $value === false) 
                    {   return '<!-- no value '.$value.' -->'.PHP_EOL; return;}
                $tmp    = (float) $value; 
                if ($tempunit <> 'C')
                     {  $tmp    = round (    5*( ($tmp -32)/9) );}
                $n      = 32 + (int) $tmp;
                if ($n > $maxTemp)      
                     {  $color  = $temp_colors[$maxTemp];}
                else {  $color  = $temp_colors[$n];}
                return $color;}
} // eo exist temp_color
#
*/
# --------------------------------------
$cols           = count ($parts); // loaded / generated in fct_aeris_shared.php
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
     {  $close  = '      <span style="float: left;">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>Aeris Forecast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden; width:100%; height: 100%; ">
<div class="PWS_module_title font_head" style="width: 100%; padding: 2px;" >
'.$close.'
    <span class="tab" style="margin: 0 auto; font-size: 12px; ">
        <label class="tablinks active"   onclick="openTab(event, \'containerTemp\')" id="defaultOpen">'       .lang('Graph').'</label>&nbsp;
        <label class="tablinks"          onclick="openTab(event, \'Forecast\')">'     .lang('Forecast').'</label>&nbsp;
        <label class="tablinks"          onclick="openTab(event, \'Hourly\')">'       .lang('Hourly forecast').'</label>
    </span>
    <small style="float: right; padding-top: 0px;">
        <a href="https://www.aerisweather.com/" target="_blank" style="color: grey">'
        .lang("Powered by").':&nbsp;www.aerisweather.com&nbsp;&nbsp;</a>
        </small> 
</div>'.PHP_EOL;
echo '<div class= "div_height" style="width: 100%;text-align: left; overflow: auto;">'.PHP_EOL;
# ------------------------ GRAPH 
if (1 == 1) {
$stck_lst.= basename(__FILE__) .' ('.__LINE__.'): DarkSky CHART / GRAPH STARTED'.PHP_EOL;
$arrTimeGraph 	= 
$arrIconGraph	= 
$arrTempGraph	= 
$arrRainGraph	= 
$arrGustGraph	= 
$arrWindGraph	= 
$arrWdirGraph	= 
$arrBaroGraph	= 
$arrHumGraph	= 
$arrUvGraph	= 
$arrSolarGraph	= '';

$graphTempMin   = 200;
$graphTempMax   = -200;
$graphRainMax   = 0;
$graphWindMax   = 0;
$graphGustMax   = 0;
$graphBaroMax   = 0;
$graphBaroMin   = 99999;
$graphHumMax    = 0;
$graphUvMax     = -1;
$graphSolarMax  = -1;
$graphSolarMin  = 99999;

$graphsData	= '';		// we store all javascript data here
$graphLines     = 0;            // number of processed graphlines
$utcDiff 	= date('Z');
$graphsStart    = 9999999999999999999;
$graphsStop     = 0;
$graphsDays     = array();
$graphsNights   = array();
$today          = 0;
#
/*    [isDay] => 1
    [Utime] => 1633582800
    [part] => Today
    [temp] => 16
    [dewp] => 10
    [feel] => 16
    [humi] => 70
    [uvuv] => 1
    [solr] => 455
    [r_ch] => 0
    [rain] => 0
    [baro] => 1027
    [clds] => 39
    [wdir] => NNE
    [wdeg] => 28
    [wicn] => <img src="img/windicons/NNE.svg" class=""  alt="NNE">
    [wspd] => 3
    [w_ft] => 3-8
    [gust] => 14
    [bftn] => 1
    [bftt] => Light air
    [desc] => Partly Cloudy
    [icnx] => pcloudy.png
    [icon] => mc_day*/
#
if (!array_key_exists('test',$_REQUEST) )
     {  $t_utc  = time();}
else {  $t_utc  = 0;}
#
  echo '<!-- line'.__LINE__.' '.print_r($parts[0],true) .' -->'.PHP_EOL;
foreach ($parts as $key => $arr) {  
# skip old lines
        $time           = (int) $arr['Utime']; # echo __LINE__.date('c',$arr['time']); 
        if ($time < $t_utc)
             {  $stck_lst      .= basename(__FILE__) .' ('.__LINE__.'): Skipped old data line: '
                        .$time.' '.date('c',$time)
                        .' current = '.$t_utc.' '.date('c',$t_utc);
                continue;}
# time        
	$thisHour       = date ('H',$time);
	$thisday        = date ('Ymd',$time);
	if ($thisday <> $today)
	     {  $today = $thisday;
                $sun_arr        = date_sun_info((int) $time, $lat, $lon);
                $sunrise        = $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $sunset         = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
#                $sunrise        = date_sunrise($time, SUNFUNCS_RET_TIMESTAMP, $lat, $lon);   // standard time integer
                $graphsDays[]	= ($sunrise + $utcDiff) * 1000;
#                $sunset         = date_sunset ($time, SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $graphsNights[]	= ($sunset + $utcDiff) * 1000;	}     
        $arrTimeGraph	= $time + $utcDiff + 5*3600; // aeris has 07:00 for day and 19:00 for night
        if ($arrTimeGraph < $graphsStart) { $graphsStart= $arrTimeGraph;}
        if ($arrTimeGraph > $graphsStop)  { $graphsStop = $arrTimeGraph;}
    #    if ($sunset       > $graphsStop)  { $graphsStop = $sunset;}
# icon 
        $arrIconGraph	= $icn_prefix.$arr['icon'].$icn_post;
# temp 
        $arrTempGraph   = $arr['temp'];
	if ($arrTempGraph  > $graphTempMax) {$graphTempMax = $arrTempGraph;}
	if ($arrTempGraph  < $graphTempMin) {$graphTempMin = $arrTempGraph;}
# rain	
        $arrRainGraph   = 0;
        if (array_key_exists('r_ch',$arr))
             {  $arrRainGraph  = $arr['rain'];
	if ($arrRainGraph > $graphRainMax)   { $graphRainMax   = $arrRainGraph;}
# wind
	$arrWindGraph   = $arrGustGraph = 0;
	if (array_key_exists ('wmax',$arr) )
             {  $arrWindGraph   = $arr['wmax'];} 
        if (array_key_exists ('gust',$arr) )
             {  $arrGustGraph   = $arr['gust'];}
	if ($arrWindGraph > $graphWindMax) {$graphWindMax = $arrWindGraph;}
	if ($arrGustGraph > $graphGustMax) {$graphGustMax = $arrGustGraph;}
# wind direction
        $arrWdirGraph	= $arr['wdir'];
	if (strlen ($arrWdirGraph) > 3) 
	     {  $arrWdirGraph = substr ($arrWdirGraph,0,1);} 
# baro	
	$arrBaroGraph   = $arr['baro'];
	if ($arrBaroGraph > $graphBaroMax) {$graphBaroMax = $arrBaroGraph;} 
	if ($arrBaroGraph < $graphBaroMin) {$graphBaroMin = $arrBaroGraph;} 
# hum
	$arrHumGraph	= $arr['humi'];
	if ($arrHumGraph > $graphHumMax)  {$graphHumMax = $arrHumGraph;} 
# Uv
        $arrUvGraph     = (float) $arr['uvuv'];
        if ($arrUvGraph > $graphUvMax)  {$graphUvMax = $arrUvGraph;} 
# Ozone 
        if (array_key_exists ('solr',$arr) )
             {  $arrSolarGraph  = (float) $arr['solr'];}
        else {  $arrSolarGraph  = 0;}
        if ($arrSolarGraph > $graphSolarMax)  {$graphSolarMax = $arrSolarGraph;} 
        if ($arrSolarGraph < $graphSolarMin)  {$graphSolarMin = $arrSolarGraph;} 

# ADD the graphsdata table row for use in javascript.
	$graphsData	.= 	'tsv['.$graphLines.'] ="'. # all data fields have the same time field
		$arrTimeGraph.'|'.      # 0
                $arrTempGraph.'|'.      # 1
		$arrBaroGraph.'|'.      # 2
		$arrWindGraph.'|'.      # 3
		$arrWdirGraph.'|'.      # 4
		$arrRainGraph.'|'.      # 5
		$arrIconGraph.'|'.      # 6
		$arrHumGraph.'|'.       # 7
		$arrGustGraph.'|'.      # 8
		$arrUvGraph.'|'.        # 9
		$arrSolarGraph.'|'.     # 10
		date('c',$time).'";'.PHP_EOL;			
	$graphLines++; 
        } 
} // eo for data record 
#
# now we are going to generate the javascript graphs
# calculate Y axis steps for graphs
$graphNrLines	= 6;

# TEMP  Y-axis values
$graphTempMin	= floor ($graphTempMin);        // round down
$graphTempMax	= ceil 	($graphTempMax);        // round up
$tempMin        = $graphTempMin;                // save value 
$graphTempStep	= 2* ceil(($graphTempMax - $graphTempMin) / $graphNrLines);
#
# $stringY is composed for debugging purposes
        $stringY = 'temp max: '.$graphTempMax. ' temp min: '.$graphTempMin. ' temp step: '.$graphTempStep;
#
$graphTempMax	= $graphTempStep * ceil($graphTempMax/$graphTempStep);
$tempMax	= $graphTempMax;
$tempMin	= $tempMin - $graphTempStep;
$graphTempMax	= $graphTempMax + $graphTempStep;  #### 2021-10-02  move temp to top
$graphTempMin   = $graphTempMax - (1+ $graphNrLines) * $graphTempStep;
#
        $stringY .= '  temp max: '.$graphTempMax.' temp min: '.$graphTempMin;
#
# ICON  Y-axis values
$graphIconYvalue = $graphTempMax - ($graphTempStep/4);
#
        $stringY .= ' icon: '.$graphIconYvalue.PHP_EOL;
#
# RAIN  Y-axis values
        $stringY .= 'rain max start: '.$graphRainMax;
$rainMax	=  $graphRainMax;
if (trim(strtolower($rainunit)) == 'mm') 
     {  if ($graphRainMax < 3.5) { $graphRainMax = 3.5;}
	$graphRainStep	= round (($graphRainMax / $graphNrLines),0);} 
else {  #if ($graphRainMax < 1.3) { $graphRainMax = 14;} 
        $graphRainStep	= (ceil (10* $graphRainMax / $graphNrLines))/ 10;	
	$graphRainMax	= $graphRainStep * $graphNrLines;}
$graphRainMax	= 2 * $graphRainStep * $graphNrLines;   // 2 * because we want the graph
$graphRainStep	= 2 * $graphRainStep ;                  // only in bottom half of graphs-area
$rainMax	= $rainMax + $graphRainStep;
#
        $stringY .= ' rain max: '.$graphRainMax.'   rain step: '.$graphRainStep.PHP_EOL;
#
# BARO  Y-axis values
$baroMax	= $graphBaroMax;
$baroMin	= $graphBaroMin;

if (trim(strtolower($pressureunit)) == 'hpa') 
     {  $graphBaroDiff  = $graphBaroMax - $graphBaroMin;
	if (ceil($graphBaroDiff / 15) <= $graphNrLines) 
	      { $graphBaroStep = 15; } 
	else  { $graphBaroStep = 20;}
	$graphBaroMax   = $graphBaroStep * (ceil($graphBaroMax / $graphBaroStep));
	if ($graphBaroMax < 1035) 
	      { $graphBaroMax = 1035;}
	$graphBaroMin   = $graphBaroMax - $graphNrLines * $graphBaroStep;} 
else {  $graphBaroMax   = 32; 
        $graphBaroMin   = 28.5; 
        $graphBaroStep  = .5;}
$baroMax		= $baroMax + $graphBaroStep;
$baroMin		= $baroMin - $graphBaroStep;
#
        $stringY .='baro max: '.$graphBaroMax.' baro min: '.$graphBaroMin.PHP_EOL;
#
# WIND  Y-axis values
if ($graphGustMax > $graphWindMax) { $graphWindMax = $graphGustMax;}
if ($graphWindMax < $graphNrLines) 
     {  $graphWindMax   = $graphNrLines; }
$graphWindStep  = ceil ($graphWindMax / $graphNrLines);
$graphWindMax   = $graphNrLines * $graphWindStep;
$windMax	= $graphWindMax;
$graphWindMax   = 2 * $graphWindMax;    // 2 * because we want the graph
$graphWindStep  = 2 * $graphWindStep;   // only in bottom half of graphs-area
#
        $stringY .='wind max: '.$graphWindMax.' wind step: '.$graphWindStep.PHP_EOL;
#
        $stck_lst.= basename(__FILE__) .' ('.__LINE__.'): '.PHP_EOL.$stringY.PHP_EOL;
#
# list of translations of daynames
$graphDaysString  = "var days        = {";
$graphDaysString .= "'Sun':'".lang('Sunday')."','Mon':'".lang('Monday')."',";
$graphDaysString .= "'Tue':'".lang('Tuesday')."','Wed':'".lang('Wednesday')."',";
$graphDaysString .= "'Thu':'".lang('Thursday')."','Fri':'".lang('Friday')."',";
$graphDaysString .= "'Sat':'".lang('Saturday')."'};";
#
# begin / end of grpahs
$graphsStart 	= 1000 * ($graphsStart - 7200);
$graphsStop	= 1000 * ($graphsStop  + 7200);
#
#  shaded background every other day
$ddays		= ''; #echo '<pre> $graphsDays= '.print_r($graphsDays,true); exit;
for($i=0 ; $i < count($graphsDays); $i++) {
        $ddays.= '{ from: '.$graphsDays[$i].', to: '.$graphsNights[$i].', color: "rgba(255, 255, 255, 0.9)" },';
} // eo for each day
$ddays          = substr($ddays, 0, -1); // remove last ,  after last {day}
#
# javascript does not like html characters, also remove  not needed characters for graphs
$from           = array ('&deg;',' ','/');
$uomRain        = str_replace ($from,'',$rainunit);
$uomTemp        = str_replace ($from,'',$tempunit);
$uomBaro        = str_replace ($from,'',$pressureunit);
$uomWind        = str_replace ($from,'',$windunit);
$negValue       = "return '<span style=\"fill: blue;\">' + this.value + '</span>'";
$posValue       = "return '<span style=\"fill: red;\">' + this.value + '</span>'";
if ($clockformat <> '24') 
     {  $threshold      = 32; 
        $hour_min       = '%l:%M %P';
        $hour_ft        = '%l%P';}
else {  $threshold      = 0; 
        $hour_min       = '%H:%M';
        $hour_ft        = '%H';}
#
$degree   = '°';
#if (!isset ($fc5_charset) || $fc5_charset <> 'UTF-8') {$degree  = utf8_decode ('°');}
#
$fc5_mgraph_html     = '<div id="containerTemp" class="tabcontent fc_day div_height" 
        style="display: flex; width: 100%; overflow: auto; background-color: #f1f1f1;">
here the graph will be drawn</div>'.PHP_EOL;
$fc5_mgraph_html .='<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script>
'.$graphDaysString.';
var globalX = [
{       type: "datetime",
        min: '.$graphsStart.', // '.date('c',$graphsStart/1000).'
        max: '.$graphsStop.', // '.date('c',$graphsStop/1000).'
        plotBands: ['.$ddays.'],
        title: {text: null},
        dateTimeLabelFormats: {day: "%H",hour: "%H"},        
        tickInterval: 6 * 3600 * 1000,        
        gridLineWidth: 0.4,      
        lineWidth: 0,
        labels: {y: 10,x: 0, rotation: 0, style:{fontWeight: "normal",fontSize:"9px"}, 
                formatter: function() { 
                        if (this.value <= '.($graphsStop).') 
                                {return Highcharts.dateFormat("'.$hour_ft.'", this.value);}
                        else {return "";}
                } // eo formatter
        } // eo labels
},
{       type: "datetime",linkedTo: 0,
        min: '.$graphsStart.',
        max: '.$graphsStop.',
        title: {text: null},
        dateTimeLabelFormats: {day: "%a"},        
        tickInterval: 24 * 3600 * 1000,        
        gridLineWidth: 2,      
        lineWidth: 0,
        labels: {y: 2,  x: 10, rotation: 0, align: "left", style:{ fontWeight: "normal",fontSize:"9px"}, 
                formatter: function() { 
                        if (this.value <= '.($graphsStop - 12*3600*1000).') 
                        { return days[Highcharts.dateFormat("%a", this.value)]; }
                        else {return "";}
                }
        }
}];
var tsv = [];
'.$graphsData.'var temps   = [];
var tempdata = []
var wsps    = [];
var gsts    = [];
var baros   = [];
var precs   = [];
var icos    = [];
var hums    = [];
for (j = 0; j < tsv.length; j++) {
        var line =[];
        line     = tsv[j].split("|");
        if(line[0].length > 0 && parseInt(line[0]) != "undefined"){
                date    = 1000 * parseInt(line[0]);
                if (date <= '.$graphsStop.') {
                        d       = new Date (date);
                        temps.push([date, parseFloat(line[1])]);
                        baros.push([date, parseFloat(line[2])]);
                        if (line[4] != " ") {
                                mkr     = "img/windicons/" +line[4]+".svg";
                                str     = {x:date,  y:parseFloat(line[3]), marker:{symbol:"url("+mkr+")", width: 14, height: 14}};
                                wsps.push(str);
                        }
                        else {  str     = {x:date,  y:parseFloat(line[3])}
                                wsps.push(str);
                        }
                        if (line[5] != " ") {
                                precs.push([date, parseFloat(line[5])]);
                        }	
                        mkr     = line[6];
                        str     = {x:date,y:'.$graphIconYvalue.', marker:{symbol:"url("+mkr+")", width: 20, height: 20}};
                        icos.push(str);
                        hums.push([date, parseFloat(line[7])]);
                        gsts.push([date, parseFloat(line[8])]);
                } // line is in range 
        } // Line contains correct data           
}; // eo for each tsv';
$fc5_mgraph_html .='
var yTitles 	= {color: "#000000", fontWeight: "normal", fontSize:"10px"};
var yLabels 	= {color: "#4572A7", fontWeight: "bold",   fontSize:"8px"};
var yLabelsWind = {color: "#1485DC", fontWeight: "bold",   fontSize:"8px"};
var yLabelsBaro = {color: "#9ACD32", fontWeight: "bold",   fontSize:"8px"};
var legendStyle = {color: "#000000", fontWeight: "normal", fontSize:"10px"};
var my_options = new
	Highcharts.setOptions({
		chart: {
		        spacingTop:4, 
		        spacingBottom:4,
			renderTo: "containerTemp",
			defaultSeriesType: "spline",
			backgroundColor: "rgba(255, 255, 255, 0.1)",
			plotBackgroundColor: {linearGradient: [0, 0, 0, 250],stops: [[0, "#ddd"],[1, "rgba(255, 255, 255, 0.2)"]]},
			plotBorderColor: "#88BCCE",
			plotBorderWidth: 0.5,
			marginRight: 60,
			marginTop: 30,
			marginLeft: 60,
			zoomType: "x",
			style: {fontFamily: "Verdana,Helvetica,sans-serif",fontSize:"11px"}
		},
		title: {text: ""},
		xAxis: globalX,
		lang: {thousandsSep: ""},
		credits: {enabled: false},
		plotOptions: {
			series: {marker: { radius: 0,states: {hover: {enabled: true}}}},
			spline: {lineWidth: 1.5, shadow: false, cursor: "pointer",states:{hover:{enabled: false}}},
			column: {pointWidth:15},
			areaspline: {lineWidth: 1.5, shadow: false,states:{hover:{enabled: false}}}
		},
		legend: { x: 0, y: 0, align: "left", verticalAlign: "top", rtl: true, margin: 0, itemStyle:legendStyle},
		exporting: {enabled:true},
		tooltip: {
                        positioner: function () {return { x: 0};},
                        backgroundColor: "silver",
                        borderColor: "#fff",
                        borderRadius: 3,
                        borderWidth: 0,  
                        shared: true,
                        useHTML: true,
                        crosshairs: { width: 0.5,color: "#666"},
                        style: {lineHeight: "1.3em",fontSize: "11px",color: "#000"},
                        formatter: function() {
                                var head= days[Highcharts.dateFormat("%a", this.x)]+" "+ Highcharts.dateFormat("'.$hour_min.'", this.x)  +"<br />----------------";
                                var s   = "";
                                this.points.forEach (function( point , i) {
                                        if (point.series.name != " ") {
                                                var unit = {
                                                   "'.lang('Precipitation')     .'": " '.$uomRain.'",
                                                   "'.lang('Wind')              .'": " '.$uomWind.'",
                                                   "'.lang('Windgust')          .'": " '.$uomWind.'",
                                                   "'.lang('Temperature')       .'": "'.$degree.$uomTemp.'",
                                                   " ": "",
                                                   "'.lang('Pressure')          .'": " '.$uomBaro.'",
                                                   "'.lang('Humidity')          .'": " %"
                                                }[point.series.name];
                                                s  = "<br>"+point.series.name+": <b>"+point.y+unit+"</b>" + s;
                                        }                            
                                });  // eo each
                                return head+s;
                        } // eo formatter
                } // eo tooltip
	});  // eo set general options
const my_chart  = new Highcharts.Chart({
        chart: { renderTo: "containerTemp" },		
      	yAxis: [
      	{ lineWidth: 2, tickAmount: 8,
          gridLineWidth: 0.4, min: '.$graphTempMin.',max:'.$graphTempMax.',tickInterval:'.$graphTempStep.', offset: 25,
          title: {text: "'.$degree.$uomTemp.'", rotation: 0, align:"high", offset: 0, y: -8, margin: 0, style:yTitles},
          labels: {x: -4, y: 1, formatter: function() {
                if (this.value < '.$tempMin.' || this.value > '.$tempMax.' )
                        { return ""; } 
                 else   {if (this.value < '.$threshold.') {'.$negValue.';} else {'.$posValue.';}}
          },style:yLabels}       
      	},
      	{ tickAmount: 8,
          gridLineWidth: 0, min: 0,max:'.$graphRainMax.',tickInterval:'.$graphRainStep.', offset: 30,
          title: {text: "'.$uomRain.'", rotation: 0, align:"low", offset: 0,x: -20, y: 15, style:yTitles},
          labels: {align: "left", x: -4, y: 1,  formatter: function() {if (this.value < 0 || this.value > '.$rainMax.' ){ return ""; } else {return this.value;}},style:yLabels}
      	},
      	{ tickAmount: 8,
          gridLineWidth: 0, min: 0, max: '.$graphWindMax.', tickInterval: '.$graphWindStep.', opposite: true,
          title: {text: "'.$uomWind.'", rotation:0, align:"low", offset: 25,x: 0, y: 15, style:yTitles},      
          labels: {align: "right",x: 25, y: 1, formatter: function() {if (this.value < 0 || this.value > '.$windMax.' ){ return ""; } else {return this.value;}},style:yLabelsWind}      
      	},
      	{ lineWidth: 2, tickAmount: 8,
          gridLineWidth: 0, min: '.$graphBaroMin.',max: '.$graphBaroMax.',tickInterval: '.$graphBaroStep.',opposite: true, offset: 30,
          title: {text:"'.$uomBaro.'", rotation: 0, align:"high", offset: 0, y: -8, style:yTitles},        
          labels: {align: "left",x: 4, y: 1, formatter: function() {if (this.value < '.$baroMin.' || this.value >= '.$baroMax.' ){ return ""; } else {return this.value;}},style:yLabelsBaro}
        },
         { lineWidth: 0, tickAmount: 8,
          gridLineWidth: 0, min: -40, max: 240, tickInterval: 20,opposite: false, offset: 30,
          title: {text:"%", rotation: 0, align:"low", offset: 0,x: 10, y: 15, style:yTitles},       
          labels: {align: "left",x: 10, y: 1, formatter: function() {if (this.value > 100 || this.value < 80){ return ""; } else {return this.value;}},style:yLabels}
        }
        ],
      	series: [
      		{name: "'.lang('Precipitation'). '", data: precs, color:"#AAAAAA",type:"column",yAxis:1},
      		{name: "'.lang('Humidity').      '", data: hums,  color:"#356297", yAxis:4, dashStyle:"Dot"},
      		{name: "'.lang('Wind').          '", data: wsps,  color:"#1485DC", yAxis:2},
                {name: "'.lang('Windgust').      '", data: gsts,  color:"#FFB90F", yAxis:2, marker:{radius:2,symbol:"circle"}},
      		{name: "'.lang('Pressure').      '", data: baros, color:"#9ACD32", yAxis:3},
      		{name: "'.lang('Temperature').   '", data: temps, color:"#EE4643", threshold: '.$threshold.', negativeColor: "#4572EE"},
      		{name: " '.                              '", data: icos , color:"transparent",type:"",events:{legendItemClick:false}},
      		
      	],
      	navigation: {buttonOptions: {verticalAlign: \'top\', y: -0, x: -50} }
        });  // eo chart    
</script>'.PHP_EOL;
echo  $fc5_mgraph_html;
$stck_lst.= basename(__FILE__) .' ('.__LINE__.'): METNO CHART / GRAPH ENDED'.PHP_EOL;
} // eo grpah
# ---------  CHART / GRAPH ENDED
# -------------- AERIS MOS TABLE
if ( 1 == 1) {
$tblmos_col         = $cols;
$row_day_part       = '<tr class="fc_night font_head">';
$row_day_cond_img   = '<tr>';
$row_day_cond_txt   = '<tr class="fc_day">';
$row_day_temp       = '<tr class="fc_day" style="text-shadow: black 1px 1px;font-weight: bolder;">';
$row_day_uv_txt     = '<tr class="fc_night  font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('UV').'</td>';
$row_day_uv_desc    = '<tr class="fc_day">';
$row_day_rain_txt   = '<tr class="fc_night  font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Precipitation').'</td>';
$row_day_rain_value = '<tr class="fc_day">';
$row_day_wind_txt   = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Wind from').'</td>';
$row_day_wind_img   = '<tr class="fc_day">';
$row_day_wind_value = '<tr class="fc_day">';
$row_day_wspeed_txt = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Windspeed').'</td>';
$row_day_wind_speed = '<tr class="fc_day">';  // 18-24
$row_day_bft_value  = '<tr class="fc_day">';  // 3 + descr
$row_day_baro_txt   = '<tr class="fc_night font_head"><td class="" colspan="'.$tblmos_col.'">'.lang('Pressure').'<small> '.lang($pressureunit).'</small></td>';
$row_day_baro_value = '<tr class="fc_day">';

$row_day_rain_total = 0;
$row_day_uv_valid   = 0;
$baro_yesterday = 0;

$nr_of_dayparts = 1;
$fc_first_time  = true;
$fc_col_width   = round(100/$tblmos_col,3,PHP_ROUND_HALF_DOWN);
/*    [isDay] => 1
    [Utime] => 1633582800
    [part] => Today
    [temp] => 16
    [dewp] => 10
    [feel] => 16
    [humi] => 70
    [uvuv] => 1
    [solr] => 455
    [r_ch] => 0
    [rain] => 0
    [baro] => 1027
    [clds] => 39
    [wdir] => NNE
    [wdeg] => 28
    [wicn] => <img src="img/windicons/NNE.svg" class=""  alt="NNE">
    [wspd] => 3
    [w_ft] => 3-8
    [gust] => 14
    [bftn] => 1
    [bftt] => Light air
    [desc] => Partly Cloudy
    [icnx] => pcloudy.png
    [icon] => mc_day*/
$done = false;
foreach ($parts as $arr)
     {  if ($done == false) { echo '<!-- '.__LINE__.' '.print_r($arr,true).' -->'.PHP_EOL; $done= true;}
        $row_day_part          .= '<td style="width: '.$fc_col_width.'%;">'.$arr['part'].'</td>'.PHP_EOL;
        if ($arr['isDay'] == true) {$class = 'fc_day';} else {$class = 'fc_night';} 
        $row_day_cond_img      .= '<td class="'.$class.'">'
                .'<img src="'.$icn_prefix.$arr['icon'].$icn_post.'"'
                        .' style="width: 30px; height: 30px; " class="fc_icon" alt="'.$arr['desc'].'">'
                .'</td>'.PHP_EOL;
        $row_day_cond_txt      .= '<td>'.$arr['desc'].'</td>'.PHP_EOL;
        $temp   = round($arr['temp']);
        $color  = temp_color($temp);
        $row_day_temp          .= '<td style="font-size: 20px; color: '.$color.'">'.$temp.'&deg;'.strtoupper($tempunit).'</td>';
        if (array_key_exists('uvuv',$arr) ) 
             {  $value  = trim($arr['uvuv'].' ');}
        else {  $value  = '';}
        if ($value == '' || (float) $value == 0)   
             {  $row_day_uv_desc .= '<td>'.$nouv.'</td>'.PHP_EOL;  }
        else {  $value  = (int) $value;
                if ($value > 11) {$value = 11;}  
                $row_day_uv_desc .= 
                        '<td><div class="my_uv" style="background-color:'
                       .$fll_uv[$value].'; ">'
                       .(int) $value.'</div>'
                       .'</td>'.PHP_EOL;
                $row_day_uv_valid       = true;
                }
        $value  = trim($arr['rain']);
        if ($value == '' || (float) $value == 0)   
             {  $row_day_rain_value    .= '<td>'.$norain.'</td>'.PHP_EOL;  }
        else {  $extra  = trim($arr['r_ch']);
                if ($extra <> '' && (float) $extra > 0)   
                     {  $extra = ' '.(float) $extra.'%';}
                else {  $extra = '';}  
                $row_day_rain_value .= 
                        '<td>'.round((float) $value,2).lang($rainunit).$extra
                       .'</td>'.PHP_EOL;
                $row_day_rain_total     = $row_day_rain_total + (float) $value;
                }
#$row_day_wind_img
        $row_day_wind_img      .= '<td>'.$arr['wicn'].'</td>';
#$row_day_wind_value
        $row_day_wind_value    .= '<td>'.$arr['wdir'].'</td>';
#$row_day_wind_speed 
        $row_day_wind_speed    .= '<td>'.$arr['w_ft'].'<small> '.lang($windunit).'</small></td>';  #echo '<pre>'.print_r($arr,true); exit;
#$row_day_bft_value
        $row_day_bft_value     .= '<td style="vertical-align: top">'.$arr['bftn'].'<small> '.lang('Bft').'</small><br>'
                                .$arr['bftt'].'</td>';
        $row_day_baro_value    .= '<td>'.$arr['baro'].'</td>';
        } // eo each parts
$row_day_part       .= '</tr>'.PHP_EOL;
$row_day_cond_img   .= '</tr>'.PHP_EOL;
$row_day_cond_txt   .= '</tr>'.PHP_EOL;
$row_day_temp       .= '</tr>'.PHP_EOL;
$row_day_uv_txt     .= '</tr>'.PHP_EOL;
$row_day_uv_desc    .= '</tr>'.PHP_EOL; 
$row_day_rain_txt   .= '</tr>'.PHP_EOL;
$row_day_rain_value .= '</tr>'.PHP_EOL;
$row_day_wind_txt   .= '</tr>'.PHP_EOL;
$row_day_wind_img   .= '</tr>'.PHP_EOL;
$row_day_wind_value .= '</tr>'.PHP_EOL;
$row_day_wspeed_txt .= '</tr>'.PHP_EOL;
$row_day_wind_speed .= '</tr>'.PHP_EOL;
$row_day_bft_value  .= '</tr>'.PHP_EOL;
$row_day_baro_txt   .= '</tr>'.PHP_EOL;
$row_day_baro_value .= '</tr>'.PHP_EOL;
#
$fc6_mos_html = '<table class= "tabcontent div_height  forecast" id="Forecast" 
        style=" width: 100%;  margin: 0px auto; text-align: center; border-spacing: 2px; background-color: transparent; color: black;">'
#        .$row_station
        .$row_day_part
        .$row_day_cond_img
        .$row_day_cond_txt
        .$row_day_temp
        .$row_day_uv_txt
        .$row_day_uv_desc;
if ($row_day_rain_total <> 0 ) { 
     $fc6_mos_html .=
        $row_day_rain_txt
        .$row_day_rain_value;} 
elseif ($fc6_rain_skip == false) {
     $fc6_mos_html .= 
        '<tr class="fc_night  font_head"><td colspan = "'.$tblmos_col.'">'
                .lang('No measurable rain expected during this period.').'</td></tr>'.PHP_EOL;}
$fc6_mos_html .=
        $row_day_wind_txt
        .$row_day_wind_img
        .$row_day_wind_value
        .$row_day_wspeed_txt
        .$row_day_wind_speed
        .$row_day_bft_value
        .$row_day_baro_txt
        .$row_day_baro_value
        .'</table>'.PHP_EOL;
echo $fc6_mos_html;
} // eo mos table
# -------- AERIS MOS TABLE ENDED
# ----------------- 1 HOUR PARTS 
if ( 1 == 1) {
unset ($parts);
# -----------------  load general Aeris daily fct code
$scrpt          = 'fct_aeris_shared_hrs.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return         = include $scrpt; 
if ($return == false) { return false;}  
/* echo '<pre>'.print_r($parts[0],true); exit;
    [part] => Thursday
    [isDay] => 1
    [Utime] => 1633604400
    [temp] => 14
    [dewp] => 11
    [feel] => 14
    [humi] => 81
    [uvuv] => 1
    [r_ch] => 0
    [rain] => 0
    [baro] => 1028
    [clds] => 84
    [wdir] => SE
    [wspd] => 3
    [gust] => 3
    [desc] => Mostly Cloudy
    [icnx] => mcloudy.png
    [icon] => mc_day */
#
$rows           = count ($parts);
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
$cols           = count($cl_cntnt);
$arr            = $parts[0]; #echo  '<pre>'.print_r($arr,true).'</pre>';  exit;
foreach ($cl_headers as $item => $text)
     {  if (!array_key_exists($item,$arr) ) 
             {  unset ($cl_headers[$item]);}
        }
$cols           = count($cl_headers);
#
echo '<table class= "tabcontent div_height font_head"  id="Hourly"
        style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">
<tr style="border-bottom: 1px grey solid; ">';
#
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
echo '</table>'.PHP_EOL;    
}
# ---------- END OF 1 HOUR PARTS
echo '</div>
<script>
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
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>'.PHP_EOL;
#if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
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
          border: 0px solid #ccc; 
          border-top: none;
        }  
';
        $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
