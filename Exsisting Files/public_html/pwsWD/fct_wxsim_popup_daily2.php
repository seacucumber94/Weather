<?php $scrpt_vrsn_dt  = 'fct_wxsim_popup_daily.php|01|2023-09-09|';   # PHP8.2 graph | 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$wxsim_latest   = '../latest.csv';      #location
$wxsim_lastret  = '../lastret.txt';
#
$ws_fileToUse   = $wxsim_latest;        # use this file
$ws_cache_time  = 3600;                 # check cache every xx seconds
$skip_first_line= true;                 # skip first line of forecast file => sometimes problems with wind-data
#
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
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) 
     {  $filenameReal = __FILE__;  
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
# ------------------- save list of loaded scrips
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
# ---------------------------- load all settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ---------------------------  general functions   
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#-----------------------------------------------
$round_crnr             = 5;    // for uv-nr in square or round background
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     {  $round_crnr     = 50;} 
#
$toUOMS = '_'.$windunit.'_'.$tempunit.'_'.$rainunit.'_'.$pressureunit.'_'.$distanceunit;
$toUOMS = str_replace ('/','',$toUOMS);
$toUOMS = strtolower ($toUOMS);
$ws_cached_file = './jsondata/wxsimcache'.$toUOMS.'.arr';
load_yr_trans();
if ($clockformat == '24') 
     {  $date_time_frmt = 'l  j  F';
        $month_day_frmt = 'j  F';}
else {  $date_time_frmt = 'D M j';
        $month_day_frmt = 'F  j';}
#-----------------------------------------------
# load general WXSIM code load/save the forecast 
#-----------------------------------------------
$scrpt          = 'fct_wxsim_convert2.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
#
# check which forecast items arew available (mostly for lastret)
# set defaults to missing
$is_thu = $is_dew = $is_bar = $is_hum = $is_uvi = $is_sol = $is_sky = $is_vis = $is_rai = false;
#
# if data is available is registred in first data record
$arr    = $ws_wxsim_arr['details'][0];          # echo print_r($arr,true); exit;
if (isset ($arr['thunder'])     && $arr['thunder'] <> 0 )   {$is_thu = true;}
if (isset ($arr['dew'])         && $arr['dew'] <> 0 )       {$is_dew = true;}
if (isset ($arr['baro'])        && $arr['baro'] <> 0 )      {$is_bar = true;} 
if (isset ($arr['hum'])         && $arr['hum'] <> 0 )       {$is_hum = true;}
if (isset ($arr['UV'])          && $arr['UV'] <> 0 )        {$is_uvi = true;}
if (isset ($arr['solar'])       && $arr['solar'] <> 0 )     {$is_sol = true;}
if (isset ($arr['skyCover'])    && $arr['skyCover'] <> 0 )  {$is_sky = true;}
if (isset ($arr['rain'])        && $arr['rain'] <> 0)       {$is_rai = true;} 
#-----------------------------------------------
#                    arrays for coloring UV item
#-----------------------------------------------
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
#       normally we use the easyweather settings
if (!isset ($show_close_x)|| $show_close_x === true)   
     {  $close  = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.lang('Close').'</small></span>'.PHP_EOL;}
else {  $close = '';}
#-----------------------------------------------
#                         first part of the html
#-----------------------------------------------
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>'.lang('WXSIM forecast').'</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'
</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; padding: 2px;" >
'.$close
.'        <span class="tab" style="margin: 0 auto;">
            <label class="tablinks active"   onclick="openTab(event, \'containerTemp\')" id="defaultOpen">' .lang('Graph').'</label>
            <label class="tablinks"          onclick="openTab(event, \'Hourly\')">'                         .lang('Hourly forecast').'</label>
        </span>
        <small style="float: right"><a href="https://www.wxsim.com/" target="_blank" style="color: grey">'
                .lang("Powered by").':&nbsp;&nbsp; WXSIM '
                .'</a>&nbsp;&nbsp;
        </small> 
    </div>'.PHP_EOL;
#-----------------------------------------------
# start of different tabs contents, all in 1 div
#-----------------------------------------------
echo '<div class= "div_height"  style="width: 100%; padding: 0px; text-align: left; overflow: auto; ">'.PHP_EOL;
# ------------------------ GRAPH 
if (1==1) {    // dummy if to make long code more readable
$stck_lst.= basename(__FILE__) .' ('.__LINE__.'): CHART / GRAPH STARTED'.PHP_EOL;
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
$arrOzoneGraph	= '';

$graphTempMin   = 200;
$graphTempMax   = -200;
$graphRainMax   = 0;
$graphWindMax   = 0;
$graphGustMax   = 0;
$graphBaroMax   = 0;
$graphBaroMin   = 99999;
$graphHumMax    = 0;
$graphUvMax     = -1;
$graphOzoneMax  = -1;
$graphOzoneMin  = 99999;

$graphsData	= '';		// we store all javascript data here
$graphLines     = 0;            // number of processed graphlines
$utcDiff 	= date('Z');
$graphsStart    = 9999999999999999999;
$graphsStop     = 0;
$graphsDays     = array();
$graphsNights   = array();
#
#-------------------------USED FOR DEBUG
# to test with old files add add &test to the url
if (!array_key_exists('test',$_REQUEST) )
     {  $t_utc  = time();}
else {  $t_utc  = 1;}
#----------------------------- EO DEBUG
#
# on which hours we want to show an icon
# to make sure they do not overlap
$icon_hours     = array ('04','08','12','16','20','00');
#
foreach ($ws_wxsim_arr['details'] as $key => $arr) {  # print_r($arr); exit;
#  skip first line, that one is contains no forecvast
        if ($key == 0)  
             {  continue;}  # echo print_r($arr,true); exit;
#  optional skip first data-line,
         if ($skip_first_line && $key == 1)  
             {  continue;}  # echo print_r($arr,true); exit;     
# skip outdated lines
        $time           = (int) $arr['intDate'];  # echo __LINE__.' $time='.$time.' $t_utc='.$t_utc.' '.date('c',$arr['intDate']); exit;
        if ($time < $t_utc)
             {  $stck_lst      .= basename(__FILE__) .' ('.__LINE__.'): Skipped old data line: '
                        .$time.' '.date('c',$time)
                        .' current = '.$t_utc.' '.date('c',$t_utc);
                continue;}
# time        
	$thisHour       = date ('H',$time);
        $sun_arr        = date_sun_info((int)$time, $lat, $lon);
        $sunrise        = $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
        $sunset         = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
	$graphsDays[]	= ($sunrise + $utcDiff) * 1000;
	$graphsNights[]	= ($sunset + $utcDiff) * 1000;	     
        $arrTimeGraph	= $time + $utcDiff; 
        if ($arrTimeGraph < $graphsStart) { $graphsStart= $arrTimeGraph;}
        if ($arrTimeGraph > $graphsStop)  { $graphsStop = $arrTimeGraph;}
        # if ($sunset       > $graphsStop)  { $graphsStop = $sunset;}  # removed, to much unfilled-graphspace.
# icon 
        if (in_array($thisHour,$icon_hours))
             {  $arrIconGraph	= WXSIMicon_trns($arr['symbol_lnk']);}
        else {  $arrIconGraph	= '';}
# temp 
        $arrTempGraph   = $arr['temp_clc'];
	if ($arrTempGraph  > $graphTempMax) {$graphTempMax = $arrTempGraph;}
	if ($arrTempGraph  < $graphTempMin) {$graphTempMin = $arrTempGraph;}
# rain	
        $arrRainGraph   = '';
        if ($is_rai) {
                $arrRainGraph   = $arr['rain'];
	        if ($arrRainGraph > $graphRainMax)   {  $graphRainMax   = $arrRainGraph;}
	        }
# wind
	$arrWindGraph   = $arrGustGraph = 0;
	if (array_key_exists('windSpeed_clc',$arr) )  { $arrWindGraph   = $arr['windSpeed_clc'];} 
        if (array_key_exists('gust_clc',$arr) )       { $arrGustGraph   = $arr['gust_clc']; }
	if ($arrWindGraph > $graphWindMax) {$graphWindMax = $arrWindGraph;}
	if ($arrGustGraph > $graphGustMax) {$graphGustMax = $arrGustGraph;}
# wind direction
        if (in_array($thisHour,$icon_hours)
            && array_key_exists('windDeg',$arr) ) 
             {  $arrWdirGraph	= windlabel($arr['windDeg']);
	        if (strlen ($arrWdirGraph) > 3) { $arrWdirGraph = substr ($arrWdirGraph,0,1);} }
	else {  $arrWdirGraph	= ' ';}
# baro	
	$arrBaroGraph   = '';
	if ($is_bar) {
                $arrBaroGraph   = $arr['baro_clc'];
                if ($arrBaroGraph > $graphBaroMax) {$graphBaroMax = $arrBaroGraph;} 
                if ($arrBaroGraph < $graphBaroMin) {$graphBaroMin = $arrBaroGraph;} 
        }
# hum
	$arrHumGraph	= '';
	if ($is_hum) {
	        $arrHumGraph	= $arr['hum'];
	        if ($arrHumGraph > $graphHumMax)  {$graphHumMax = $arrHumGraph;} 
	}
# Uv
        $arrUvGraph     = '';
        if ($is_uvi) {
                $arrUvGraph     = (float) $arr['UV'];
                if ($arrUvGraph > $graphUvMax)  {$graphUvMax = $arrUvGraph;}
        } 
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
		$arrOzoneGraph.'|'.     # 10
		date('c',$time).'";'.PHP_EOL;			
	$graphLines++;  
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
$graphRainMax	= 3 * $graphRainStep * $graphNrLines;   // 3 * because we want the graph
$graphRainStep	= 3 * $graphRainStep ;                  // only in bottom half of graphs-area
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
        $stringY .='wind max: '.$graphWindMax.' wind step: '.$graphWindStep;
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
#
echo '<div id="containerTemp" class="tabcontent fc_day div_height" 
        style="display: flex; width: 100%; overflow: auto; background-color: #f1f1f1;">here the graph will be drawn</div>'.PHP_EOL;
echo '<script src="https://code.highcharts.com/highcharts.js"></script>
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
                        if (line[4] != " " && line[3] != "0") {
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
}; // eo for each tsv
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
                                var head= days[Highcharts.dateFormat("%a", this.x)]+" "+ Highcharts.dateFormat("'.$hour_min.'", this.x)  +"<br>----------------";
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
          gridLineWidth: 0, min: 0,max:'.$graphRainMax.',tickInterval:'.$graphRainStep.',offset: 30,
          title: {text: "'.$uomRain.'", rotation: 0, align:"low", offset: 0,x:  10, y: 15, style:yTitles},
          labels: {align: "left", x: 10, y: 1,  formatter: function() {if (this.value < 0 || this.value > '.$rainMax.' ){ return ""; } else {return this.value;}},style:yLabels}
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
          gridLineWidth: 0, min: -20, max: 120, tickInterval: 10,opposite: false, offset: 30,
          title: {text:"%", rotation: 0, align:"high", offset: 0, x: 20, y: -8, style:yTitles},       
          labels: {align: "left",x: 10, y: 1, formatter: function() {if (this.value > 100 || this.value < 80){ return ""; } else {return this.value;}},style:yLabels}
        }
        ],
      	series: [';
if ($is_rai) {  echo  '
      		{name: "'.lang('Precipitation'). '", data: precs, color:"#AAAAAA",type:"column",yAxis:1},';}
if ($is_hum) {  echo  '
      		{name: "'.lang('Humidity').      '", data: hums,  color:"#356297", yAxis:4, dashStyle:"Dot"},';}
                echo  '      
      		{name: "'.lang('Wind').          '", data: wsps,  color:"#1485DC", yAxis:2},
      		{name: "'.lang('Windgust').      '", data: gsts,  color:"#FFB90F", yAxis:2, marker:{radius:2,symbol:"circle"}},';
if ($is_bar) {  echo  ' 
      		{name: "'.lang('Pressure').      '", data: baros, color:"#9ACD32", yAxis:3},';}
                echo  '  
      		{name: "'.lang('Temperature').   '", data: temps, color:"#EE4643", threshold: '.$threshold.', negativeColor: "#4572EE"},
      		{name: " '.                      '", data: icos , color:"transparent",type:"",events:{legendItemClick:false}},	
      	],
      	navigation: {buttonOptions: {verticalAlign: \'top\', y: -0, x: -50} }
        });  // eo chart    
</script>'.PHP_EOL;
$stck_lst.= basename(__FILE__) .' ('.__LINE__.'): CHART / GRAPH ENDED'.PHP_EOL;
} // eo grpah
# ---------  CHART / GRAPH ENDED
# ----------------- 1 HOUR PARTS 
if ( 1 == 1) { // dummy if to make long code more readable
echo '<table class= "tabcontent div_height font_head" id="Hourly" 
        style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">'.PHP_EOL;
$cl_headers     = array ();
                $cl_headers['Period']           = lang('Period'); 
                $cl_headers['Wind']             = lang('Wind');
                $cl_headers['Conditions']       = lang('Conditions'); 
                $cl_headers['Temperature']      = lang('Temperature');
if ($is_dew) {  $cl_headers['Dewpoint']         = lang('Dewpoint');     }
if ($is_hum) {  $cl_headers['Humidity']         = lang('Humidity');     }
if ($is_bar) {  $cl_headers['Pressure']         = lang('Pressure');     }
if ($is_rai) {  $cl_headers['Precipitation']    = lang('Precipitation');}
if ($is_thu) {  $cl_headers['Lightning']        = lang('Lightning');    }
if ($is_uvi) {  $cl_headers['UV_index']         = lang('UV index');     }
if ($is_sky) {  $cl_headers['Clouds']           = lang('Clouds');       }
$cols           = count($cl_headers);
$max_lines      = 999;
$lines          = 0;
$head_str       = '<tr style="border-bottom: 1px grey solid; background-color: DIMGRAY; color: white; ">';   
foreach ($cl_headers as  $header)
     {  $head_str .='<td>'.$header.'</td>';} // print   the table headers
$head_str .= '</tr>'.PHP_EOL;
#echo $head_str;
$ymd_old= 0;
foreach ($ws_wxsim_arr['details'] as $key => $arr) {  # print_r($ws_wxsim_arr['details'][1]); exit;
# skip old lines
        if ($key == 0) { continue;} #  skip first line, that one is for debug only  # echo print_r($arr,true); exit;
        $time    = (int) $arr['intDate'];  # echo __LINE__.' $time='.$time.' $t_utc='.$t_utc.' '.date('c',$arr['intDate']); exit;
        if ($time < $t_utc) {
                $stck_lst      .= basename(__FILE__) .' ('.__LINE__.'): Skipped old data line: '
                        .$time.' '.date('c',$time)
                        .' current = '.$t_utc.' '.date('c',$t_utc);
                continue;}
        $ymd    = date('Ymd',$arr['intDate']);
        if ($ymd <> $ymd_old)
             {  $sun_arr= date_sun_info($time, $lat, $lon);
                $sunrise= $sun_arr['sunrise'];
                $sunset = $sun_arr['sunset'];  
                $day_nm = trans_long_date(date($date_time_frmt,$time));
                echo '<tr style="border-bottom: 1px grey solid;  background-color: DIMGRAY; color: white; height: 22px;" ><td colspan="'
                        .$cols.'"><span style="padding: 4px; font-size: 14px;">&nbsp;<b>'
                        .$day_nm;
                if (!$sunrise == false &&  !$sunset == false)  
                     {  $rise   = date($timeFormatShort,$sunrise);
                        $rise_hr= date('H',$sunrise);
                        $set_hr= date('H',$sunset); # if ($set_hr == '00') {$set_hr = 24;}
                        $set    = date($timeFormatShort,$sunset);
                        $length = $sunset -  $sunrise;
                        $length = gmdate('H:i',$length); 
                        $length = str_replace (':',' '.lang ('hrs '),$length);
                        $length .= ' '.lang ('mins ');              
                        echo '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="img/sunrise.svg" style="width: 32px; height: 16px;" alt="sunrise">&nbsp;&nbsp;'
                                .$rise.'&nbsp;&nbsp;<img src="img/sunset.svg"  style="vertical-align: bottom; width: 32px; height: 16px;" alt="sunset">&nbsp;&nbsp;'
                                .$set.'&nbsp;&nbsp;&nbsp;'
                                .lang ('Daylight') .': '
                                .$length;
                        }
                echo '&nbsp;</span></td></tr>'
                        .PHP_EOL.$head_str;	        
                $ymd_old  = $ymd;}
        echo '<tr style="border-bottom: 1px grey solid; ">';
        $from24 = date('H',$time);
        $to24   = date('H',$time+3600 );
        if ($to24 == 0) {$to24 = 24;}   
        if ($from24 > $rise_hr && $to24 <= $set_hr) {$colorx = $clrwrm; } else {$colorx = $clrcld;} 
        foreach ( $cl_headers as $col => $trans) {
            switch ($col) {      
# time
                case 'Period':
                        $fromtxt= str_replace (':00','',set_my_time($time,true));
                        $totxt  = str_replace (':00','',set_my_time($time+3600,true));
                        echo PHP_EOL.'<td><span style="font-size: 14px; color: '.$colorx.';">'.$fromtxt.' -  '.$totxt.'</span></td>'.PHP_EOL;
                break; 
# Conditions        
                case 'Conditions':
                        $icon   = WXSIMicon_trns($arr['symbol_lnk']);
                        $text   = $arr['cnd1'];
                        echo '<td><img src="'.$icon
                                .'" width="60" height="32" alt="'.$text.'" style="vertical-align: middle;">'; 
                        # and text
                        echo '<br>'.lang($text).'</td>'.PHP_EOL;
                break; 
# temperature
                case 'Temperature':
                        $temp   = $arr['temp_clc'];
                        $tclr   = $arr['temp_clr'];
                        echo '<td style="font-size: 20px; color: '.$tclr.';">'.$temp.'&deg;</td>'.PHP_EOL;
                break;
# wind
                case 'Wind':
                        $speed  = $arr['windSpeed_clc'];
                        $dir    = $arr['windDeg'];
                        $bft    = $arr['windBft'];
                        $txt    = lang($arr['windBftTxt']);
                        $compass= windlabel((int)$speed); 
                        $image= '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$compass.'"  style="vertical-align: bottom;"> '; 
                        echo '<td>'.$image.lang($compass).'<br>'
                                .$speed.' '.$windunit.' '.$bft.' '.lang('Bft').'<br>'
                                .$txt
                                .'</td>'.PHP_EOL;
                break;
# Precipitation 
                case 'Precipitation':
                        $rain   = $arr['rain_clc'];
                        if ($rain == 0)
                             {  echo '<td>'.$norain.'</td>'.PHP_EOL; }
                        else {  echo '<td>'.$rain.' '.$rainunit.'</td>'.PHP_EOL; }
                break;
# lightning        
                case 'Lightning':        
                        $thunder= $arr['thunder'];
                        if ($thunder <= 0)
                             {  echo '<td>'.$norain.'</td>'.PHP_EOL; }
                        else {  echo '<td>'.$thunder.'<small> %</small>'.'</td>'.PHP_EOL; }
                break;
# dewpoint        
                case 'Dewpoint':       
                        $dew    = $arr['dew_clc'];
                        if ($dew == 0)
                             {  echo '<td>'.$norain.'</td>'.PHP_EOL; }
                        else {  echo '<td>'.$dew.'&deg;'.'</td>'.PHP_EOL; }
                break;
# humidity
                case 'Humidity':         
                        $value  = $arr['hum'];
                        echo '<td>'.$value.'<small> %</small></td>'.PHP_EOL;
                break;
# pressure
                case 'Pressure':          
                        $baro   = $arr['baro_clc'];
                        echo '<td>'.$baro.'<small> '.$pressureunit.'</small></td>'.PHP_EOL;
                break;        
# UV
                case 'UV_index':          
                        $uv     = $arr['UV'];
                        if ($uv == '' ||$uv == 'n/a' || $uv == NULL || (float) $uv == 0 ) #|| round ($uv) == 0 )   
                             {  echo '<td>'.$norain.'</td>'.PHP_EOL;}
                        else {  if ($uv > 11)  {$uv = 11;}
                                if ($uv > 3.9) {$uv = round ($uv);} 
                                $colorUV= round($uv);
                                $string = '<div class="my_uv" style="background-color:'.$fll_uv[$colorUV].'; "><small>'.$uv.'</small></div>';
                                echo '<td>'.$string.'</td>'.PHP_EOL;}
                break;    
# Sky-cover
                case 'Clouds':   
                        $value  = $arr['skyCover']; 
                        echo '<td>'.$value.'<small> %</small></td>'.PHP_EOL;  #die (__LINE__.'xxxxxxx');
                break;
                }
            }  // each column
        echo '</tr>'.PHP_EOL; #die (__LINE__.'xxxxxxx');   
# check max lines 
        $lines ++;
        if ($lines > $max_lines) {break;}      
} // each data line hours
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
$stck_lst .= ws_debug_times('End of script').PHP_EOL;
$stck_lst .= ws_debug_info();
echo '<!-- '.$stck_lst.' -->
</body>
</html>'.PHP_EOL;
$stck_lst='';
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
                    height: 24px; width: 24px;  color: #fff;
                    line-height: 24px;font-size: 16px;
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
function WXSIMicon_trns($icon) {
        global $yrn_icn;
        if (array_key_exists($icon,$yrn_icn) ) {return './pws_icons/'.$yrn_icn[$icon]['svg'].'.svg';}
        $from = array ('d','n') ;
        $test   = str_replace ($from,'',$icon);
        if (array_key_exists($test,$yrn_icn) ) {return './pws_icons/'.$yrn_icn[$test]['svg'].'.svg';}
        $test2  = $test.'n';
        if (array_key_exists($test2,$yrn_icn) ) {return './pws_icons/'.$yrn_icn[$test2]['svg'].'.svg';}
        $test2  = $test.'d';
        if (array_key_exists($test2,$yrn_icn) ) {return './pws_icons/'.$yrn_icn[$test2]['svg'].'.svg';}
        
        return 'unknown';
}
function load_yr_trans() {
        global $yrn_icn;
        $yrn_icn = array();
        $yrn_icn["01d"] = array("svg" => "clear_day"          ,"txt" => "Clear sky");
        $yrn_icn["01n"] = array("svg" => "clear_night"        ,"txt" => "Clear sky");
        $yrn_icn["02d"] = array("svg" => "few_day"            ,"txt" => "Fair");
        $yrn_icn["02n"] = array("svg" => "few_night"          ,"txt" => "Fair");
        $yrn_icn["03d"] = array("svg" => "pc_day"             ,"txt" => "Partly cloudy");
        $yrn_icn["03n"] = array("svg" => "pc_night"           ,"txt" => "Partly cloudy");
        $yrn_icn["04"]  = array("svg" => "ovc_dark"           ,"txt" => "Cloudy");
        $yrn_icn["05d"] = array("svg" => "mc_rain"            ,"txt" => "Rain showers");
        $yrn_icn["05n"] = array("svg" => "mc_rain"            ,"txt" => "Rain showers");
        $yrn_icn["06d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Rain showers and thunder");
        $yrn_icn["06n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Rain showers and thunder");
        $yrn_icn["07d"] = array("svg" => "ovc_sleet"          ,"txt" => "Sleet showers");
        $yrn_icn["07n"] = array("svg" => "ovc_sleet"          ,"txt" => "Sleet showers");
        $yrn_icn["08d"] = array("svg" => "ovc_sleet"          ,"txt" => "Snow showers");
        $yrn_icn["08n"] = array("svg" => "ovc_sleet"          ,"txt" => "Snow showers");
        $yrn_icn["09"]  = array("svg" => "mc_rain"            ,"txt" => "Rain");
        $yrn_icn["10"]  = array("svg" => "mc_rain"            ,"txt" => "Heavy rain");
        $yrn_icn["11"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy rain and thunder");
        $yrn_icn["12"]  = array("svg" => "ovc_sleet"          ,"txt" => "Sleet");
        $yrn_icn["13"]  = array("svg" => "ovc_flurries"       ,"txt" => "Snow");
        $yrn_icn["14"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Snow and thunder");
        $yrn_icn["15"]  = array("svg" => "mc_fog"             ,"txt" => "Fog");
        $yrn_icn["20d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Sleet showers and thunder");
        $yrn_icn["20n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Sleet showers and thunder");
        $yrn_icn["21d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Snow showers and thunder");
        $yrn_icn["21n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Snow showers and thunder");
        $yrn_icn["22"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Rain and thunder");
        $yrn_icn["23"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Sleet and thunder");
        $yrn_icn["24d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light rain showers and thunder");
        $yrn_icn["24n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light rain showers and thunder");
        $yrn_icn["25d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy rain showers and thunder");
        $yrn_icn["25n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy rain showers and thunder");
        $yrn_icn["26d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Lights sleet showers and thunder");
        $yrn_icn["26n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Lights sleet showers and thunder");
        $yrn_icn["27d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy sleet showers and thunder");
        $yrn_icn["27n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy sleet showers and thunder");
        $yrn_icn["28d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Lights snow showers and thunder");
        $yrn_icn["28n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Lights snow showers and thunder");
        $yrn_icn["29d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy snow showers and thunder");
        $yrn_icn["29n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy snow showers and thunder");
        $yrn_icn["30d"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light rain and thunder");
        $yrn_icn["30n"] = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light rain and thunder");
        $yrn_icn["31"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light sleet and thunder");
        $yrn_icn["32"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy sleet and thunder");
        $yrn_icn["33"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Light snow and thunder");
        $yrn_icn["34"]  = array("svg" => "ovc_thun_rain_dark" ,"txt" => "Heavy snow and thunder");
        $yrn_icn["40d"] = array("svg" => "mc_flurries"        ,"txt" => "Light rain showers");
        $yrn_icn["40n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Light rain showers");
        $yrn_icn["41d"] = array("svg" => "mc_flurries"        ,"txt" => "Heavy rain showers");
        $yrn_icn["41n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Heavy rain showers");
        $yrn_icn["42d"] = array("svg" => "mc_flurries"        ,"txt" => "Light sleet showers");
        $yrn_icn["42n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Light sleet showers");
        $yrn_icn["43d"] = array("svg" => "mc_flurries"        ,"txt" => "Heavy sleet showers");
        $yrn_icn["43n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Heavy sleet showers");
        $yrn_icn["44d"] = array("svg" => "mc_flurries"        ,"txt" => "Light snow showers");
        $yrn_icn["44n"] = array("svg" => "mc_flurries"        ,"txt" => "Light snow showers");
        $yrn_icn["45d"] = array("svg" => "mc_flurries"        ,"txt" => "Heavy snow showers");
        $yrn_icn["45n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Heavy snow showers");
        $yrn_icn["46d"] = array("svg" => "mc_flurries"        ,"txt" => "Light rain");
        $yrn_icn["46n"] = array("svg" => "mc_flurries_night"  ,"txt" => "Light rain");
        $yrn_icn["47"]  = array("svg" => "mc_flurries"        ,"txt" => "Light sleet");
        $yrn_icn["48"]  = array("svg" => "mc_flurries"        ,"txt" => "Heavy sleet");
        $yrn_icn["49"]  = array("svg" => "mc_flurries"        ,"txt" => "Light snow");
        $yrn_icn["50"]  = array("svg" => "mc_flurries_night"  ,"txt" => "Heavy snow");
        }
function trans_long_date ($date)
     {  $from   = array ( 
                'Apr ','Aug ','Dec ','Feb ','Jan ','Jul ','Jun ','Mar ','May ','Nov ','Oct ','Sep ',
                'April','August','December','February','January','July','June','March','May','November','October','September',
                'Mon ','Tue ','Wed ','Thu ','Fri ','Sat ','Sun ',
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        foreach ($from  as $txt) {$to_dates[] = lang($txt).' ';} # echo '-'.$txt.'-'.lang($txt).PHP_EOL;
        return str_replace ($from, $to_dates, $date.' ');  #### 2018-07-18
        }       
