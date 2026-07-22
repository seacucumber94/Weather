<?php $scrpt_vrsn_dt  = 'fct_darksky_popup_daily.php|01|2023-09-09|';  # PHP 8.2 Graphs | release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
$apparent_diff   = 3;           // minimal temperature difference before apparent temps are shown
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
#--------------------------------- more settings
$ltxt_clsppp    = lang('Close');
$round_crnr     = 5;
if (isset ($_REQUEST['round']) || (isset ($use_round) && $use_round == true ) )  
     { $round_crnr     = 50;}   
if ($showFeelsLike <> true) {$apparent_diff = 99;}
#
# -----------------   load general metno fct code
$scrpt          = 'fct_darksky_shared2.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include $scrpt;  
#
if (!isset ($darkskydayCond) || count ($darkskydayCond) < 4 )   
     {  echo '<b style="color: red;"><small>Darksky file not ready</small></b>'; 
        return; }  // if no correct data output small messasge
#
if ($clockformat == '24') 
     {  $date_time_frmt = 'l  j  F';
        $month_day_frmt = 'j  F';}  // 1 October
else {  $date_time_frmt = 'l F j';
        $month_day_frmt = 'F j';}   // October 1
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $close  = '        <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($user_lang,0,2).'">
<head>
    <meta charset="UTF-8">
    <title>DarkSky Forecast</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">'
.my_style().'</head>
<body class="dark" style="overflow: hidden;">
    <div class="PWS_module_title font_head" style="width: 100%; padding: 2px;" >
'.$close.'        <span class="tab" style="margin: 0 auto;">
            <label class="tablinks active"   onclick="openTab(event, \'containerTemp\')" id="defaultOpen">' .lang('Graph').'</label>
            <label class="tablinks"          onclick="openTab(event, \'Forecast\')">'                       .lang('Forecast').'</label>
            <label class="tablinks"          onclick="openTab(event, \'Hourly\')">'                         .lang('Hourly forecast').'</label>
        </span>
        <small style="float: right"><a href="https://darksky.net/" target="_blank" style="color: grey">'
                .lang("Powered by").':&nbsp;&nbsp;' 
                .'<img src="./img/darksky_light.svg" alt="darksky logo" style="height: 12px; vertical-align: middle;">'
                .'</a>&nbsp;&nbsp;
        </small> 
    </div>'.PHP_EOL;
# start of different tabs contents, all in 1 div
echo '<div id="div_height" class= "div_height" style="width: 100%; text-align: left; overflow: auto;">'.PHP_EOL;
# ------------------------ GRAPH 
if (1==1) {
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
/*    [time] => 1633104000
    [summary] => Possible Drizzle
    [icon] => rain
    [precipIntensity] => 0.1175
    [precipProbability] => 0.26
    [precipType] => rain
    [temperature] => 15.77
    [apparentTemperature] => 15.77
    [dewPoint] => 10.78
    [humidity] => 0.72
    [pressure] => 1012.2
    [windSpeed] => 6.58
    [windGust] => 12.21
    [windBearing] => 210
    [cloudCover] => 1
    [uvIndex] => 0
    [visibility] => 16.093
    [ozone] => 300.7  */
#
if (!array_key_exists('test',$_REQUEST) )
     {  $t_utc  = time();}
else {  $t_utc  = 0;}
$icon_hours     = array ('04','08','12','16','20','00');
#
foreach ($darkskyhourlyCond as $key => $arr) {  # print_r($darkskyhourlyCond[0]); exit;
# skip old lines
        $time           = (int) $arr['time']; # echo __LINE__.date('c',$arr['time']); 
        if ($time < $t_utc)
             {  $stck_lst      .= basename(__FILE__) .' ('.__LINE__.'): Skipped old data line: '
                        .$time.' '.date('c',$time)
                        .' current = '.$t_utc.' '.date('c',$t_utc);
                continue;}
# time        
	$thisHour       = date ('H',$time);
	$sun_arr= date_sun_info((int) $time, $lat, $lon);
        $sunrise= $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
        $sunset = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
	$graphsDays[]	= ($sunrise + $utcDiff) * 1000;
	$graphsNights[]	= ($sunset + $utcDiff) * 1000;	     
        $arrTimeGraph	= $time + $utcDiff; 
        if ($arrTimeGraph < $graphsStart) { $graphsStart= $arrTimeGraph;}
        if ($arrTimeGraph > $graphsStop)  { $graphsStop = $arrTimeGraph;}
    #    if ($sunset       > $graphsStop)  { $graphsStop = $sunset;}
# icon 
        if (in_array($thisHour,$icon_hours))
             {  $arrIconGraph	= DSicon_trns($arr['icon']);}
        else {  $arrIconGraph	= '';}
# temp 
        $arrTempGraph   = convert_temp ( (float) $arr['temperature'],$darksky_used_temp,$tempunit,1);
	if ($arrTempGraph  > $graphTempMax) {$graphTempMax = $arrTempGraph;}
	if ($arrTempGraph  < $graphTempMin) {$graphTempMin = $arrTempGraph;}
# rain	
        $arrRainGraph   = 0;
        if (array_key_exists('precipIntensity',$arr))
             {  $arrRainGraph  = convert_precip( $arr['precipIntensity'],$darksky_used_rain,$rainunit,2);
	if ($arrRainGraph > $graphRainMax)   { $graphRainMax   = $arrRainGraph;}
# wind
	$arrWindGraph   = $arrGustGraph = 0;
	if (array_key_exists ('windSpeed',$arr) )
             {  $arrWindGraph   = round (convert_speed ((float) $arr['windSpeed'],$darksky_used_wind,$windunit,0) );} 
        if (array_key_exists ('windGust',$arr) )
             {  $arrGustGraph   = round (convert_speed ((float) $arr['windGust'],$darksky_used_wind,$windunit,0) );}
	if ($arrWindGraph > $graphWindMax) {$graphWindMax = $arrWindGraph;}
	if ($arrGustGraph > $graphGustMax) {$graphGustMax = $arrGustGraph;}
# wind direction
        if (in_array($thisHour,$icon_hours)) 
             {  $arrWdirGraph	= windlabel($arr['windBearing']);
	        if (strlen ($arrWdirGraph) > 3) { $arrWdirGraph = substr ($arrWdirGraph,0,1);} }
	else {  $arrWdirGraph	= ' ';}
# baro	
	$arrBaroGraph   = round (convert_baro ( (float) $arr['pressure'], $darksky_used_baro,$pressureunit,$dec_pres) );
	if ($arrBaroGraph > $graphBaroMax) {$graphBaroMax = $arrBaroGraph;} 
	if ($arrBaroGraph < $graphBaroMin) {$graphBaroMin = $arrBaroGraph;} 
# hum
	$arrHumGraph	= round($arr['humidity'] *100);
	if ($arrHumGraph > $graphHumMax)  {$graphHumMax = $arrHumGraph;} 
# Uv
        $arrUvGraph     = (float) $arr['uvIndex'];
        if ($arrUvGraph > $graphUvMax)  {$graphUvMax = $arrUvGraph;} 
# Ozone $arrOzoneGraph
        $arrOzoneGraph  = (float) $arr['ozone'];
        if ($arrOzoneGraph > $graphOzoneMax)  {$graphOzoneMax = $arrOzoneGraph;} 
        if ($arrOzoneGraph < $graphOzoneMin)  {$graphOzoneMin = $arrOzoneGraph;} 

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
        style="display: flex; width: 100%; overflow: auto; background-color: #f1f1f1;">here the graph will be drawn</div>'.PHP_EOL;
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
# ------daily PARTS  / MOS TABLE
if (1==1) {
$days               = 
$tblmos_col         = count ($darkskydayCond);

$row_day_temp_txt   = '<tr class="fc_night font_head">'.PHP_EOL; #<td class="" colspan="'.$tblmos_col.'">'.lang('Feels').'</td></tr>'.PHP_EOL;
$row_day_uv_txt     = '<tr class="fc_night font_head">'.PHP_EOL; #<td class="" colspan="'.$tblmos_col.'">'.lang('UV').'</td></tr>'.PHP_EOL;
$row_day_rain_txt   = '<tr class="fc_night font_head">'.PHP_EOL; #<td class="" colspan="'.$tblmos_col.'">'.lang('Precipitation').'<small> '.lang($rainunit).'</small></td></tr>'.PHP_EOL;
$row_day_wspeed_txt = '<tr class="fc_night font_head">'.PHP_EOL; #<td class="" colspan="'.$tblmos_col.'">'.lang('Wind - Gust').'<small> '.lang($windunit).'</small></td></tr>'.PHP_EOL;
$row_day_wgust_txt  = '<tr class="fc_night font_head">'.PHP_EOL;
$row_day_baro_txt   = '<tr class="fc_night font_head">'.PHP_EOL; #<td class="" colspan="'.$tblmos_col.'">'.lang('Pressure').'<small> '.lang($pressureunit).'</small></td></tr>'.PHP_EOL;
#
$row_day_rain_total = 0;
$row_apparent       = false;
$row_bft_text_lvl   = false;        // very high beaufort value occured
$row_baro_tekst_lvl = false;        // high drop / rise in pressure 
#
$row_day_part       = '<tr class="fc_night font_head">'.PHP_EOL;
$row_day_cond_img   = '<tr class="fc_day">'.PHP_EOL;
$row_day_cond_txt   = '<tr class="fc_day">'.PHP_EOL;
$row_day_temp       = '<tr class="fc_day">'.PHP_EOL; # style="text-shadow: black 1px 1px;font-weight: bolder;">'.PHP_EOL;
$row_day_feels      = '<tr class="fc_day">'.PHP_EOL;
$row_day_uv         = '<tr class="fc_day">'.PHP_EOL;
$row_day_rain       = '<tr class="fc_day">'.PHP_EOL;
$row_day_wind1      = '<tr class="fc_day">'.PHP_EOL; // wnd - gust <. bft
$row_day_wind2      = '<tr class="fc_day">'.PHP_EOL; // wnd - gust <. bft
$row_day_wind3      = '<tr class="fc_day">'.PHP_EOL; // wnd - gust <. bft
$row_day_wind_gust  = '<tr class="fc_day">'.PHP_EOL;
$row_day_baro       = '<tr class="fc_day">'.PHP_EOL;
#
$nr_of_dayparts = 1;
$fc_first_time  = true;
$fc_col_width   = round(50/$tblmos_col,3,PHP_ROUND_HALF_DOWN);
$baro_yesterday = 0;
#
$cntnt  = array ('time','summary','icon'                // items to process for each day
                ,'precipIntensity' , 'precipProbability'
                ,'temperatureHigh', 'temperatureMax'
                ,'apparentTemperatureHigh','apparentTemperatureMax'
                ,'dewPoint'
                ,'humidity', 'pressure'
                ,'windSpeed', 'windGust', 'windBearing' 
                ,'cloudCover', 'uvIndex', 'visibility', 'ozone'
                ,'xxxxxxxx'
                );
# 
$arr            = $darkskydayCond[0]; #echo  '<pre>'.print_r($arr,true).'</pre>';  exit;
echo '<table class="tabcontent div_height  forecast" id="Forecast" 
        style="width: 100%; margin: 0px auto; text-align: center; border-spacing: 2px; background-color: transparent; color: black; display: table;">'.PHP_EOL;
#
$rainOK = false; // no rain period
$uvOK   = false; // no uv
$tempBef= '<span style="text-shadow: black 1px 1px;font-weight: bolder;">';
$tempAft= '</span>';
$empty  = '<td> </td>'.PHP_EOL;
$empty2 = '<td colspan="2"> </td>'.PHP_EOL;
for ($n = 0; $n < $days; $n++) 
     {  $arr            = $darkskydayCond[$n];  # echo  '<pre>'.print_r($arr,true); exit;
        $row_day_temp_txt .=
                '<td colspan="2">'.lang('Temperature').'</td>'.PHP_EOL;
        $row_day_uv_txt    .= 
                '<td colspan="2">'.lang('UV').'</td>'.PHP_EOL;
        $row_day_rain_txt .=
                '<td colspan="2">'.lang('Precipitation').'</td>'.PHP_EOL;
        $row_day_wspeed_txt .=
                '<td colspan="2">'.lang('Wind').'</td>'.PHP_EOL;
        $row_day_wgust_txt  .=
                '<td colspan="2">'.lang('Gust').'</td>'.PHP_EOL;
        $row_day_baro_txt .=
                '<td colspan="2">'.lang('Pressure').'</td>'.PHP_EOL;
        
        $iconfnd        = false;
        $summary        = false;
        $temp24H        = false;        // correct values for this day
        $uv_val         = false;
        $precip         = false;
        $windGst        = false;
        $baro_vl        = false;
        foreach ($cntnt as $x => $key)  // for every data value = column we want to print
            {   $content = 'n/a';
                if (isset ($arr[$key]) ) 
                     { $content  = $arr[$key]; }
                switch ($key){
                    case 'time': 
                        $text   = ucfirst (date('l',(int)$content))
                                . '<br>'
                                . date ($month_day_frmt,(int)$content);
                        $row_day_part   .= 
                                '<td colspan="2" style="width: '.(2*$fc_col_width).'%;">'.trans_long_date($text).'</td>'.PHP_EOL;
                        break;
                    case 'icon':
                        $icon   = DSicon_trns ($content);
                        $row_day_cond_img .= 
                                '<td colspan="2"><img src="./'.$icon.'" class="fc_icon" alt="'.$content.'" ></td>'.PHP_EOL;
                        $iconfnd= true;
                        break;
                     case 'summary': 
                        $row_day_cond_txt .= 
                                '<td colspan="2" >'.$content.'</td>'.PHP_EOL;
                        $summary= true;
                        break;
                    case 'temperatureMax': 
                    case 'temperatureHigh': 
                        if ($temp24H <> false) { break; }  // already done for this day
                        $tempH  = -999;
                        $timeH  = $timeL = 0;
                        $tempL  = 999;
                        $timeH  = $timeL = '';
                        if (array_key_exists('temperatureHigh', $arr))
                             {  $tempH  = (float) $arr['temperatureHigh'];
                                if (array_key_exists('temperatureHighTime', $arr))
                                     {  $timeH  = (int)   $arr['temperatureHighTime'];}
                                }
                        elseif (array_key_exists('temperatureMax', $arr))
                             {  $tempH  = (float) $arr['temperatureMax'];
                                $timeH  = (int)   $arr['temperatureMaxTime'];
                                if (array_key_exists('temperatureMaxTime', $arr))
                                     {  $timeH  = (int)   $arr['temperatureMaxTime'];}
                                }
                        if (array_key_exists('temperatureLow', $arr))
                             {  $tempL  = (float) $arr['temperatureLow'];
                                if (array_key_exists('temperatureLowTime', $arr))
                                     {  $timeH  = (int)   $arr['temperatureLowTime'];}
                                }
                        elseif (array_key_exists('temperatureMin', $arr))
                             {  $tempL  = (float) $arr['temperatureMin'];
                                if (array_key_exists('temperatureMinTime', $arr))
                                     {  $timeH  = (int)   $arr['temperatureMinTime'];}
                                }
                        $timeH_txt      = $timeL_txt    = '';
                        if ($timeH <> '') 
                             {  $timeH_txt      =  '<span><br>@ '.date($timeFormatShort,$timeH).'</span>'  ;}
                        if ($timeL <> '') 
                             {  $timeL_txt      =  '<span><br>@ '.date($timeFormatShort,$timeL).'</span>'  ;}
                                           
                        $tempH  = convert_temp ($tempH,$darksky_used_temp,$tempunit,0);
                        $tempL  = convert_temp ($tempL,$darksky_used_temp,$tempunit,0);
                        $first  = $tempL;  $firstA = '&darr;'; $next = $tempH; $nextA = '&uarr;';
                        $row_day_temp  .= 
                               '<td style="width: '.$fc_col_width.'%;">'
                               .$firstA.'<span style="font-size: 24px; color: '.temp_color($first).'">'.$tempBef.$first.$tempAft.'&deg;</span>'
                               .$timeL_txt #.'<span><br>@ '.date($timeFormatShort,$timeL).'</span>'
                               .'</td>'.PHP_EOL
                               .'<td style="width: '.$fc_col_width.'%;">'
                               .$nextA .'<span style="font-size: 24px; color: '.temp_color($next) .'">'.$tempBef.$next .$tempAft.'&deg;</span>'
                               .$timeH_txt #.'<span><br>@ '.date($timeFormatShort,$timeH).'</span>'
                               .'</td>'.PHP_EOL;
                        $temp24H= true;
                        break;
                    case 'uvIndex': 
                        $value  = trim($content);
                        if ($value == '' || (float) $value == 0)   
                             {  $row_day_uv    .= '<td colspan="2">'.$nouv.'</td>'.PHP_EOL;  
                                $uv_val         = true;
                                break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        $row_day_uv .= 
                                '<td><div class="my_uv" style="background-color:'
                               .$fll_uv[$value].'; ">'
                               .(int) $content.'</div>'
                               .'</td>'
                               .'<td>'.$uv_texts[$value].'</td>'.PHP_EOL;
                        $uv_val = true;
                        $uvOK   = true;
                        break;                        
                    case 'precipProbability':
                    case 'precipIntensity':
                    case 'precipType':
                        if ($precip <> false) { break; }  // already done for this day
                        if (trim($content) == '' || (float) $content == 0 )  {  break; }
                        if (!array_key_exists('precipIntensity',$arr)
                             || trim($arr['precipIntensity'])   == '' 
                             || (float) $arr['precipIntensity'] == 0 ){  break; } 
#
                        $rain   = convert_precip(24 * $arr['precipIntensity'],$darksky_used_rain,$rainunit,2);
                        $unit   = ' '.lang($rainunit);
                        if (array_key_exists ('precipProbability',$arr) )
                             {  $perc   = (int)  100 * (float) $arr['precipProbability'];}
                        $extra1 = $extra2 = '';
                        if ($perc <> 0 ) 
                             { $extra1  = ' <small>'.$perc.'%</small>';}
                        if (array_key_exists ('precipType',$arr) && strtolower($arr['precipType']) <> 'rain')
                             {  $extra2 = '<small><br>'.lang($arr['precipType']).'</small>'; }
                        $row_day_rain .= 
                                '<td style="">'.$rain.$unit.$extra2.'</td>'.PHP_EOL
                               .'<td style="">'.$extra1.'</td>'.PHP_EOL;
                        $precip = true; // this day
                        $rainOK = true; // all days                        
                        break;  
                    case 'windSpeed':
                    case 'windGust':
                    case 'windBearing':
                        if ($windGst <> false) {break;}  
                        if (array_key_exists ('windSpeed',$arr) )
                             {  $wspd   = round (convert_speed ((float) $arr['windSpeed'],$darksky_used_wind,$windunit,0) ); 
                                $wspdKts= (float) $arr['windSpeed']*$toKnots;}
                        else {  $wspd   = $wspdKts = 0;}
                        if (array_key_exists ('windGust',$arr) )
                             {  $wgst   = round (convert_speed ((float) $arr['windGust'],$darksky_used_wind,$windunit,0) );}
                        else {  $wgst   =  -1;}
                        $spd_knts = round ($wspdKts, 0);
                        foreach ($lvl_bft as $key => $lvl)
                             {  if ($spd_knts > $lvl) {continue;}  # $key=12; # for test 
                                break;}
                        $bft_nr         = $key;
                        $bft_txt_l      = lang($bft_txt[$key]);
                        if ($wgst <> -1)
                             {  $wgst   = $wgst;}
                        else {  $wgst   = '';}
                        $bearing= (int) $arr['windBearing']; 
                        $compass= windlabel($bearing);
                        $img    = '<img src="img/windicons/'.$compass.'.svg" class="fc_wnd"  alt="'.$compass.'">';
                        $compass= lang($compass);                       
                        
                        $row_day_wind1 .=      # text beaufort
                                '<td colspan="2">'.$bft_txt_l.'</td>'.PHP_EOL;
                        $row_day_wind2 .=      # arrow and speed
                                '<td>'.$img.'</td>'.PHP_EOL
                                .'<td>'.$wspd.' '.lang($windunit).'</td>'.PHP_EOL;
                        $row_day_wind3 .=      # dir-txt and nr bft
                                '<td>'.$compass.'</td>'.PHP_EOL
                                .'<td>'.$bft_nr.' '.lang('Bft').'</td>'.PHP_EOL;
                        if (array_key_exists('windGustTime',$arr) )
                             {  $tm_txt = '@ '.date($timeFormatShort,$arr['windGustTime']);}
                        else {  $tm_txt = '';}
                        $row_day_wind_gust .=
                                '<td>'.$wgst.' '.lang($windunit).'</td>'.PHP_EOL
                                .'<td>'.$tm_txt.'</td>'.PHP_EOL;
                        $windGst=true;
                        break;
                    case 'pressure':
                        $value  = trim($content);
                        if ($value == '' )  
                             {  $row_day_baro  .= '<td>'.'-'.'</td>'.PHP_EOL;
                                $baro_vl        =true;
                                break;}
                        $value =  convert_baro ($content,$darksky_used_baro,$pressureunit,$dec_pres);
                        $i = $n+1;
                        if ($i >= $days ) 
                             {  $diff = 0;}
                        else {  $diff = $darkskydayCond[$i]['pressure'] - (float)$content; }
                        if ($diff > 0 )   
                             {  $arrow  = ' &uarr;';}
                        elseif ($diff < 0 ) 
                             {  $arrow  = ' &darr;';}
                        else {  $arrow  = '';}
                        $row_day_baro  .= '<td  colspan="2" >'.$value.$arrow.'</td>'.PHP_EOL;
                        $baro_vl=true;
                        break;
                    default:
                        break;
                    #default: echo $n.'-'.$key.'-'.$content; exit;
                } // eo switch             
        } // eo each cntnt
#        if ($appa24H == false ) { $row_day_feels        .= $empty;} 
        if ($iconfnd == false ) { $row_day_cond_img     .= $empty2;}
        if ($summary == false ) { $row_day_cond_txt     .= $empty2;}
        if ($temp24H == false ) { $row_day_temp         .= $empty2;}
        if ($uv_val  == false ) { $row_day_uv           .= $empty2;}
        if ($precip  == false ) { $row_day_rain         .= $empty2;}    
        if ($windGst == false ) { $row_day_wind_1       .= $empty2;
                                  $row_day_wind_2       .= $empty2;
                                  $row_day_wind_3       .= $empty2;
                                  $row_day_wind_gust    .= $empty2;}
        if ($baro_vl == false ) { $row_day_baro         .= $empty2;}
} // eo days
$rowEND = '</tr>'.PHP_EOL;
echo $row_day_part.$rowEND .$row_day_cond_img.$rowEND .$row_day_cond_txt.$rowEND 
        .$row_day_temp_txt.$rowEND.$row_day_temp.$rowEND;
if ($uvOK == true)
     {  echo $row_day_uv_txt.$rowEND.$row_day_uv.$rowEND; }
if ($rainOK == true)
     {  echo $row_day_rain_txt.$rowEND.$row_day_rain.$rowEND;}
echo $row_day_wspeed_txt.$rowEND.
        $row_day_wind1.$rowEND .$row_day_wind2.$rowEND.$row_day_wind3.$rowEND;
echo $row_day_wgust_txt.$rowEND.
        $row_day_wind_gust.$rowEND;
echo $row_day_baro_txt.$rowEND.$row_day_baro.$rowEND;
#
echo '<tr><td  colspan="'.(2*$days).'"><span style="float: right; font-size: 10px;"><a href="https://darksky.net/" target="_blank" style="color: grey">
'.lang("Powered by").':&nbsp;&nbsp;
<img src="./img/darksky_light.svg" alt="darksky logo" style="height: 12px; vertical-align: bottom;"></a>&nbsp;&nbsp;</span>
</td></tr>
</table>'.PHP_EOL;
}  // daily 
# -----------end of  daily PARTS  
# ----------------- 1 HOUR PARTS 
if ( 1 == 1) {
$cl_cntnt       = array ('time','summary','icon','precipIntensity','precipProbability', 
                         'temperature','apparentTemperature','dewPoint', 'humidity', 'pressure',
                         'windSpeed', 'windGust', 'windBearing', 
                         'cloudCover', 'uvIndex', 'visibility', 'ozone');

$cl_headers     = array (
                        'time' => '', 'icon' => '', 
                        'summary'=> lang('Conditions'),              # 3
                        'temperature' => lang('Temp'), 
                        'apparentTemperature' => lang('Feels'),  # 2  + ? dewpoint
                        'precipIntensity' => lang('Precipitation'),                              # 1
                        'windSpeed' => lang('Windspeed'),'windBearing' => lang('Direction'), # 2
                        'uvIndex' => lang('UV index'),                                           # 1
                        'pressure' => lang('Pressure')                                           # 1
                        );

# check if all data is present
$rows           = count ($darkskyhourlyCond);
$cols           = count ($cl_cntnt);
$arr            = $darkskyhourlyCond[0]; #echo  '<pre>'.print_r($arr,true);
for ($n = 0; $n < $cols; $n++)
     {  $key    = $cl_cntnt[$n];
        if (!array_key_exists($key,$arr) )  
             {  $cl_cntnt[$n]   = 'n/a';
                if (array_key_exists($key, $cl_headers )) {unset ($cl_headers[$key]);}
                }  
        } 
$cols           = count($cl_headers);
echo '<table class= "tabcontent div_height font_head" id="Hourly" 
        style=" width: 100%; margin: 0px auto; text-align: center; border-collapse: collapse;">';  

#
$head_str = '<tr style="border-bottom: 1px grey solid; color: white; background-color: DIMGRAY; ">';
foreach ($cl_headers as $key => $header)
     {  $head_str .='<td>'.$header.'</td>';}// print   the table headers
$head_str .= PHP_EOL.'</tr>'.PHP_EOL;

#
$ymd_old        = 0;
#
for ($n = 0; $n < $rows; $n++) // print 1 row / daypart with all data in coloms
     {  
#
# first check if new day arraived:
        $time   = $arr['time'];
        $ymd    = date('Ymd',$time);
        if ($ymd <> $ymd_old)
             {  $sun_arr= date_sun_info((int) $time, $lat, $lon);
                $sunrise= $sun_arr['sunrise']; #date_sunrise($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
                $sunset = $sun_arr['sunset'];  #date_sunset($arr['unix'], SUNFUNCS_RET_TIMESTAMP, $lat, $lon);
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
#
        echo '<tr style="border-bottom: 1px grey solid; ">';
        $arr    = $darkskyhourlyCond[$n];  # echo  '<pre>'.print_r($arr,true); exit;
        
        foreach ($cl_headers as $key => $header)  // for every data value = column we want to print
            {   if (isset ($arr[$key]) ) { $content  = $arr[$key]; } else {$content = 'n/a';}
                switch ($key){
                    case 'time': 
                        echo PHP_EOL.'<td>';    // if extra style is needed
                        $text   = set_my_time((int)$content,true);
                        echo '<span style="color: '.$color.';">'.$text.'</span>'; 
                        break;
                    case 'icon':
                        $icon   = DSicon_trns ($content);
                        echo PHP_EOL.'<td><img src="./'.$icon.'" width="60" height="32" alt="'.$content.'" style="vertical-align: top;">';
                        break;
                     case 'summary': 
                        echo PHP_EOL.'<td>'.$content;
                        break;
                    case 'temperature': 
                        $temp   = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                        echo PHP_EOL.'<td><span style="font-size: 20px; color: '.$color.';">'.$temp.'<small>&deg;</small></span>'; 
                        break;
                    case 'apparentTemperature':
                        if ($content == 'n/a') { $value = $arr['temperature'];}                         
                        $diff   = (float)$content - (float) $arr['temperature'];
                        if (abs ( $diff ) < 1 )  { $value = ' ';}
                        else {  $temp   = convert_temp ($content,$darksky_used_temp,$tempunit,0);
                                $value = '<span style="font-size: 20px; color: '.$color.';">'.$temp.'<small>&deg;</small></span>';} 
                        echo PHP_EOL.'<td>'.$value;
                        break;
                    case 'precipIntensity': 
                        echo PHP_EOL.'<td>'; # $content= 5;
                        $content        = (float) $arr['precipIntensity']; 
                        if ($content == 0 ) {echo $norain; break;}
                        $content= convert_precip($content,$darksky_used_rain,$rainunit,2);
                        #$content= number_format($content,2) ;        
                        $unit   = lang($rainunit);
                        echo $content.'<small> '.$unit.'</small>';
                        if (isset ($arr['precipProbability']) )
                             {  $content = 100 * (float) $arr['precipProbability'];
                                $content = (int) $content;
                                if ($content == 0 ) { break;}}
                        echo ' <small>'.$content.'%</small>';
                        break;              
                    case 'windSpeed':  
                        $wspd   = convert_speed ((float) $content,$darksky_used_wind,$windunit,0) ;
                        echo PHP_EOL.'<td>'.$wspd;
                        if (isset ($arr['windGust']) )
                             {  $content = $arr['windGust'];             
                                if (trim($content) <> '') {echo '-'.convert_speed ((float) $content,$darksky_used_wind,$windunit,0);}}
                        echo  ' '.$windunit; 
                        break;
                    case 'windBearing': 
                        $bearing        = (int) $content; 
                        $compass = windlabel($bearing );
                        echo PHP_EOL.'<td>';
                        echo '<img src="img/windicons/'.$compass.'.svg" width="20" height="20" alt="'.$compass.'"  style="vertical-align: bottom;"> ';
                        $compass = lang($compass);  # 2019-04-18                        
                        echo $compass;
                        break;
                    case 'uvIndex': 
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' || $value == '0')   {  echo $nouv;  break;}
                        $value  = (int) $content;
                        if ($value > 11) {$value = 11;}  
                        echo '<div class="my_uv" style="background-color:'.$fll_uv[$value].'; ">'.(int) $content.'</div>';
                        break;
                    case 'pressure':
                        echo PHP_EOL.'<td>';
                        $value  = trim($content);
                        if ($value == '' )  {  echo '-';  break;}
                        $value =  convert_baro ($content,$darksky_used_baro,$pressureunit,$dec_pres).' '.$pressureunit; 
                        echo number_format((float) $value,$dec_pres,',','');
                        break;
                        
                    default: echo $n.'-'.$i.'-'.$content; exit;
                }              
                echo '</td>';
                } // eo coloms
        echo PHP_EOL.'</tr>'.PHP_EOL;
} // eo rows
echo '<tr><td  colspan="'.$cols.'">
<span style="float: right; font-size: 10px;"><a href="https://darksky.net/" target="_blank" style="color: grey">
'.lang("Powered by").':&nbsp;&nbsp;
<img src="./img/darksky_light.svg" alt="darksky logo" style="height: 12px; vertical-align: bottom;"></a>&nbsp;&nbsp;</span>
</tr></table>'.PHP_EOL;
} // hours
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
//  const g_width   = document.getElementsByTagName("body")[0].clientWidth;
//  const g_height  = document.getElementsByTagName("body")[0].clientHeight; 
//  alert (">"+g_width+"<>"+g_height+"<");
//  my_chart.setSize(g_width,g_height); 
//  my_chart.reflow();
}
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>
<!-- '.$stck_lst.' -->
</body>
</html>'.PHP_EOL;
$stck_lst='';
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
';              $return         .= '    </style>'.PHP_EOL;
        return $return;
 }
