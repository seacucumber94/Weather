<?php
#-----------------------------------------------
#                         CREDIT - DO NOT REMOVE
# Author        : Wim van der Kuil
# Support       : https://pwsdashboard.com
$pageVersion	= '4.04 2021-05-10';    // pwsWD
$pageName	= 'wsReports1part.php';
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
# ---------------------------------- Houskeeping
$pageLoaded 	= basename(__FILE__); 
$string         = $pageVersion;
if ( $pageName <>  $pageLoaded)  {  $string .= ' => check script name: '.$pageName; } 
ws_message ('<!-- loaded script: '.basename(__FILE__).' ('.__LINE__.'): '.$string.' -->');
#
if (!isset ($wsDebug) ) {$wsDebug = false;}
#
$wuData		= $wsreportsDir.'wuData/';
$wuLang		= $wsreportsDir.'wuLang/';

$uomhum		= '%';

if (isset ($SITE['textLowerCase']) && $SITE['textLowerCase']) {$lower = true;} else {$lower = false;}
if (!isset ($insideTemplate) )	{$insideTemplate = false;}
#
$script = $wsreportsDir.'wsReportsFunctions.php';
ws_message (  '<!-- module '.basename(__FILE__).' ('.__LINE__.'): loading '.$script.' -->');
include $script; 
#
$numFormat	= '%01.1f';				// most values from WU are whole numbers
$trans		= 'wsreport_';
$seasonal	= false;   // normal year 1-1 / 31-12  season year 1-12/30-11 or 1-6/31-05
#
$fieldLookup= array();
$fieldLookup['Date']			= array ('kind' => 'date', 'level' => '');
$fieldLookup['TemperatureHigh']		= array ('kind' => 'temp', 'level' => 'high');
$fieldLookup['TemperatureAvg']		= array ('kind' => 'temp', 'level' => 'avg');
$fieldLookup['TemperatureLow']		= array ('kind' => 'temp', 'level' => 'low');
$fieldLookup['DewpointHigh']		= array ('kind' => 'dewp', 'level' => 'high');

$fieldLookup['DewpointAvg']		= array ('kind' => 'dewp', 'level' => 'avg');
$fieldLookup['DewpointLow']		= array ('kind' => 'dewp', 'level' => 'low');
$fieldLookup['HumidityHigh']		= array ('kind' => 'hum',  'level' => 'high');
$fieldLookup['HumidityAvg']		= array ('kind' => 'hum',  'level' => 'avg');
$fieldLookup['HumidityLow']		= array ('kind' => 'hum',  'level' => 'low');

$fieldLookup['PressureMax']		= array ('kind' => 'baro', 'level' => 'high');
$fieldLookup['PressureMin']		= array ('kind' => 'baro', 'level' => 'low');
$fieldLookup['WindSpeedMax']		= array ('kind' => 'wind', 'level' => 'high');
$fieldLookup['WindSpeedAvg']		= array ('kind' => 'wind', 'level' => 'avg');
$fieldLookup['GustSpeedMax']		= array ('kind' => 'gust', 'level' => 'high');

$fieldLookup['PrecipitationSum']	= array ('kind' => 'rain', 'level' => 'sum');
$fieldLookup['Windrum']			= array ('kind' => 'wind', 'level' => 'sum');  // 24* $fieldLookup['WindSpeedAvgKMH']
#
$kindArr	= array();
$kindArr['temp']	= array('kind' => 'temp',	'desc'	=>	'Temperature',		'uom' => $uomTemp);
$kindArr['baro']	= array('kind' => 'baro',	'desc'	=>	'Barometric pressure',	'uom' => $uomBaro);
$kindArr['rain']	= array('kind' => 'rain',	'desc'	=>	'Rain',			'uom' => $uomRain);
$kindArr['wind']	= array('kind' => 'wind',	'desc'	=>	'Wind',			'uom' => $uomWind);
$kindArr['gust']	= array('kind' => 'gust',	'desc'	=>	'Gust',			'uom' => $uomWind);
$kindArr['wrun']	= array('kind' => 'windrun',	'desc'	=>	'Wind run',		'uom' => $uomWrun);
$kindArr['dewp']	= array('kind' => 'dewp',	'desc'	=>	'Dewpoint',		'uom' => $uomTemp);
$kindArr['hum'] 	= array('kind' => 'hum',	'desc'	=>	'Humidity',		'uom' => '%');
#
$typeArr	= array();
$typeArr['daily']	= array('type' => 'daily', 	'desc'  =>      'Daily detail');
$typeArr['summonthly']	= array('type' => 'summonthly', 'desc'  =>      'Monthly summary');
$typeArr['seasonal']	= array('type' => 'seasonal', 	'desc'  =>      'Season detail');
$typeArr['sumseasonal']	= array('type' => 'sumseasonal','desc'  =>      'Seasonal summary');
$typeArr['freezedays']	= array('type' => 'freezedays', 'desc'  =>      'Freeze days');
# print_r ($typeArr); /*
#
$yearArr	= array ();
$startYear	= (int) substr($wustart,6,4);
$endYear	= date( 'Y', time());
$end		= $endYear - $startYear + 1;
if ($end > 10) {$end = 10;}
for ($c	= 0; $c < $end; $c++) { $yearArr[] = $endYear - $c;}
# print_r ($yearArr); /*
#
if (!isset ($_POST["report_kind"]) ) 	{ $kind 	= $kindArr['temp']['kind']; }	else {$kind 	= $_POST["report_kind"];}
if (!isset ($_POST["report_type"]) ) 	{ $type 	= $typeArr['daily']['type']; }	else {$type 	= $_POST["report_type"];}
if (!isset ($_POST["report_year"]) ) 	{ $reqYear      = $yearArr[0]; } 		else {$reqYear 	= $_POST["report_year"];}

if ( ($type	== 'seasonal') || ($type	== 'sumseasonal') ) {$seasonal = true;}
if (  $type	== 'freezedays')  { $seasonal = true;    $kind 	= $kindArr['temp']['kind'];}
#
# ----  calculate period for valid data exapmle year 2013:  20130100 - 20131232
#						      Northern Hemisphere 2013/14:  20131200 - 20141132
#
$validFrom = $firstYear = $validUntil = $lastYear = $reqYear;  // most of the time one year
#
if ($seasonal) {
	$thisMonth	= (int) date('n',time());
	if ($latitude < 0)	{
		$monthsArr 		= array ('', '07','08','09','10','11','00','01','02','03','04','05','06');
		$monthNamesShort	= array ('', "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec","Jan", "Feb", "Mar", "Apr", "May" );
		$monthNames		= array ('', "June", "July", "August", "September", "October", "November", "December","January", "February", "March", "April", "May");
		$thisMonth	        = date('n',time());
		if ($thisMonth > 5 ){ 
			$validUntil = $lastYear =  $reqYear + 1;   // seasonal always two years
		}  
		else {
			$validFrom = $firstYear = $reqYear - 1;
		}
		$mmddFrom	= '0600'; $mmddUntil   = '0532'; 
		$validFrom     .= '0600'; $validUntil .= '0532'; 
	}
	else { 
		$monthsArr 		= array ('','01','02','03','04','05','06','07','08','09','10','11','00');
		$monthNamesShort	= array ('', "Dec","Jan","Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov" );
		$monthNames		= array ('', "December","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November");	
		if ($thisMonth > 11) {
			$validUntil = $lastYear = $reqYear + 1;   // seasonal always two years
		}  
		else {
			$validFrom = $firstYear =  $reqYear - 1;
		}
		$mmddFrom	= '1200'; $mmddUntil   = '1132'; 
		$validFrom .= '1200'; $validUntil .= '1132';
	}
} else	{ 
	$monthsArr 			= array ('','00','01','02','03','04','05','06','07','08','09','10','11');
	$monthNamesShort		= array ('',"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" );
	$monthNames			= array ('',"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$mmddFrom	= '0000'; $mmddUntil   = '1232'; 
	$validFrom     .= '0000'; $validUntil .= '1232';
}
if ($firstYear < $startYear) {$firstYear = $startYear;}
if ($type == 'summonthly' || $type == 'sumseasonal' || $type == 'freezedays'){ 
	$validFrom = $firstYear = $startYear;	$validUntil = $lastYear = $endYear;  // summary alwaus all years
	$validFrom .= '0000'; $validUntil .= '1232';
}
#
$wuURLs		= array();
for ($i = $firstYear; $i <= $lastYear; $i++) 
     {  $wuURLs[]	= $WUdatastr.'?ID='.$wuid.'&month=01&day=01&year='.$i.'&format=1&graphspan=year';}
#
$headText	= 	wsReporttransstr($trans.$kindArr[$kind]['desc']).'&nbsp;('.$kindArr[$kind]['uom'].')&nbsp;'.
				wsReporttransstr($trans.$typeArr[$type]['desc']).' '.
				wsReporttransstr($trans.'report').' - '.
#				wsReporttransstr($trans.'over').
				wsReporttransstr($trans.'period').': ';
if ($type == 'daily') 	{ $headText	.= $firstYear;}
else 			{ $headText	.= $firstYear.'-'.$lastYear;}
$firstpartHtml	= '<h3 class="blockHead" >'.$headText.'</h3>
<div style="width: 100%; margin: 0 auto;">
<div style="text-align: center;  margin: 0 auto;"><br />
<form method="post" name="report_select" action="#" style="padding: 0px; margin: 0 auto;">
<table class="" style="text-align: center; margin: 0 auto;">
<tr>
<th>1: '.wsReporttransstr($trans.'period').'</th>
<th>2: '.wsReporttransstr($trans.'report type').'</th>
<th>3: '.wsReporttransstr($trans.'weather value').'</th>
<th>4:</th>
</tr>
<tr><td>
<select id="report_year" name="report_year"  style="">'.PHP_EOL;
$end	= count ($yearArr);
for ($c	= 0; $c < $end; $c++) {
	$value	= $yearArr[$c];
	$check	= '';
	if (isset ($_POST["report_year"]) ) {$check	= $_POST["report_year"];}
	if ($check == $value) {$selected = 'selected="selected"';} else {$selected = '';}
	$firstpartHtml	 .= '<option value="'.$value.'" '.$selected.'>'.$yearArr[$c].'</option>'.PHP_EOL;
}
$firstpartHtml		.= '</select>
</td><td>
<select id="report_type" name="report_type"  style="">'.PHP_EOL;

foreach ($typeArr as $c => $arr) {
	$value	= $arr['type'];
	$text	= wsReporttransstr($trans.$arr['desc']);
	$check	= '';
	if (isset ($_POST["report_type"]) ) {$check	= $_POST["report_type"];}
	if ($check == $value) {$selected = 'selected="selected"';} else {$selected = '';}
	$firstpartHtml	 .= '<option value="'.$value.'" '.$selected.'>'.wsReporttransstr($trans.$arr['desc']).'</option>'.PHP_EOL;
}
#echo '$value = '.$value.' $arr[\'desc\'] = '.$arr['desc'].'$text ='.$text; exit;
$firstpartHtml		.= '</select>
</td><td>
<select id="report_kind" name="report_kind"  style="">'.PHP_EOL;

foreach ($kindArr as $c => $arr) {
	$value	= $c;
	$text	= wsReporttransstr($trans.$arr['desc']);
	$check	= '';
	if (isset ($_POST["report_kind"]) ) {$check	= $_POST["report_kind"];}
	if ($check == $value) {$selected = 'selected="selected"';} else {$selected = '';}
	$firstpartHtml	 .= '<option value="'.$value.'" '.$selected.'>'.$text.'</option>'.PHP_EOL;
}	
$firstpartHtml		.= '</select>
</td>
<td>';
#$button = '<button id="report_go" name="report_go" type="submit" style="width: 80px; height: 18px; "><span style="">'.wsReporttransstr($trans.'go').'</span></button>';
#$button = '<button id="report_go" name="report_go" type="submit" style="vertical-align: top;"><span style="vertical-align: top;">'.wsReporttransstr($trans.'go').'</span></button>';
$button = '<input type="submit" id="report_go" name="report_go" value="'.wsReporttransstr($trans.'go').'" style="height: 19px; vertical-align: middle;">';
$extralang='';
if (isset ($lang) )         {$extralang .= PHP_EOL.'<input type="hidden" style="padding: 0px; border: 0px; margin: 0px" name="lang" value ="'. $lang .'"/>';}
if ($wsDebug == true)       {$extralang .= PHP_EOL.'<input type="hidden" style="padding: 0px; border: 0px; margin: 0px" name="test" value =""/>';}
$firstpartHtml		.= $button.'
</td>
</tr></table>'
.$extralang
.'</form>
<br />
</div>'.PHP_EOL;
echo $firstpartHtml;
$from			= array ('&deg;',' ','/');
#
if ($kind == 'temp' || $kind == 'dewp') {
	$uomMetric	= 'c';
	$uomEnglish	= 'f';
	$round		= 1;
	$request[]	= 1; // field in col 1
	$request[]	= 3; // field in col 3
	$tempLabels = array ('High','High Avg','Mean','Low Avg','Low' );
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomTemp) ) );
	if ($uomOut == 'c') {
		$tempLow	= -15;
		$tempInc	= 5;
	} 
	else {
		$tempLow	= 0;
		$tempInc	= 10;
	}
	$increments	= 15;		// max 15,  adapt css if more is needed
	$tempLevels	= array ($tempLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$tempLevels[$i+1] = $tempLevels[$i] + $tempInc;
	}
}

if ($kind == 'dewp') {
	$request[0]	= 4; // field in col 4
	$request[1]	= 6; // field in col 6
	$dewpLabels	= $tempLabels;
	$dewpLevels	= $tempLevels;
}

if ($kind	== 'baro') {
	$uomMetric	= 'hpa';
	$uomEnglish	= 'inhg';
	$request[]	= 10; // field in col 10
	$request[]	= 11; // field in col 11
	$baroLabels = array ('High','Mean','Low' );
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomBaro) ) );
	if ($uomOut == 'hpa' || $uomOut == 'mb') {
		$baroLow	= 980;
		$baroInc	= 5;
		$round		= 0;
		$numFormat	= '%01.0f';
	} 
	else {
		$baroLow	= 29.4;
		$baroInc	= 0.1;
		$round		= 2;
		$numFormat	= '%01.2f';
	}
	$increments	= 12;		// max 15,  adapt css if more is needed
	$baroLevels	= array ($baroLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$baroLevels[$i+1] = $baroLevels[$i] + $baroInc;
	}
}

if ($kind	== 'rain') {
	$uomMetric	= 'cm';
	$uomEnglish	= 'in';
	$request[]	= 15; // field in col 15
	$rainLabels = array ('Raindays','Month total','YTD total' );
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomRain) ) );
	if ($uomOut == 'mm') {
		$rainLow	= 2;
		$rainInc	= 2;
		$round		= 1;
		$numFormat	= '%01.1f';
	} 
	else {
		$rainLow	= 0.1;
		$rainInc	= 0.1;
		$round		= 2;
		$numFormat	= '%01.2f';
	}
	$increments	= 12;		// max 15,  adapt css if more is needed
	$rainLevels	= array ($rainLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$rainLevels[$i+1] = $rainLevels[$i] + $rainInc;
	}
}

if ($kind	== 'wind') {
	$uomMetric	= 'kmh';
	$uomEnglish	= 'mph';
	$request[]	= 12; // field in col 12
	$request[]	= 13; // field in col 13
	$windLabels = array ('High','High Avg ','Avg High','Avg' );
	$windLow	= 1;
	$windInc	= 10;
	$round		= 1;
	$numFormat	= '%01.0f';
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomWind) ) );
	if ($uomOut == 'ms') {
	$windLow	= 1;
	$windInc	= 20;		
	} 
	$increments	= 12;		// max 15,  adapt css if more is needed
	$windLevels	= array ($windLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$windLevels[$i+1] = $windLevels[$i] + $windInc;
	}
}

if ($kind	== 'gust') {
	$uomMetric	= 'kmh';
	$uomEnglish	= 'mph';
	$request[]	= 14; // field in col 14  = gust
	$request[]	= 12; // field in col 12  = normal wind
	$gustLabels = array ('High','Avg High'); #,'High Avg ','Avg High','Avg' );
	$gustLow	= 1;
	$gustInc	= 10;
	$round		= 1;
	$numFormat	= '%01.0f';
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomWind) ) );
	if ($uomOut == 'ms') {
	$gustLow	= 1;
	$gustInc	= 20;		
	} 
	$increments	= 12;		// max 15,  adapt css if more is needed
	$gustLevels	= array ($gustLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$gustLevels[$i+1] = $gustLevels[$i] + $gustInc;
	}
	$fieldLookup['WindSpeedMax']		= array ('kind' => 'wind', 'level' => 'avg');
}

if ($kind	== 'windrun' || $kind == 'wrun') {
	$uomMetric	= 'km';
	$uomEnglish	= 'mi';
	$request[]	= 16; // field in col 16
	$wrunLabels = array ('High','High Avg');
	$wrunLow	= 20;
	$wrunInc	= 40;
	$round		= 1;
	$numFormat	= '%01.0f';
	$uomOut		= trim(strtolower(str_replace ($from,'', $uomWrun) ) );
	$increments	= 12;		// max 15,  adapt css if more is needed
	$wrunLevels	= array ($wrunLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$wrunLevels[$i+1] = $wrunLevels[$i] + $wrunInc;
	}
}

if ($kind == 'hum') {
	$uomMetric	= '%';
	$uomEnglish	= '%';
	$round		= 0;
	$request[]	= 7; // field in col 7
	$request[]	= 9; // field in col 9
	$humLabels = array ('High','High Avg','Mean','Low Avg','Low' );
	$humLow	= 10;
	$humInc	= 10;
	$uomOut		= trim($uomhum);
	$increments	= 9;		// max 15,  adapt css if more is needed
	$humLevels	= array ($humLow);
	for ( $i = 0; $i < $increments ; $i ++ ) {
		$humLevels[$i+1] = $humLevels[$i] + $humInc;
	}
}
#
$levelArr	= $kind.'Levels';
#var_dump($$levelArr); exit;
$row		= 0;
$noValue	= '---';
$empty		= '&nbsp;';
$yearArray	= array();
$fields		= array();

$valuesMonth= count($request);
$months		= 12;
$cols		= $valuesMonth * $months;

#if ($wsDebug) {echo '<pre>'.PHP_EOL;}
#
#  gather all data starting at $validFrom  end at $validUntil
#  the files for thos years are at array $fileNames[]
#
$endYear 		= count($wuURLs);
$checkYear		= $firstYear -1;
$now			= time();
$thisYear		= date ('Y',	$now );
$today			= date ('Ymd',	$now );
$nowHour		= date ('G',	$now );
$firstYearOnly	= true;
#
ws_message (ws_debug_times( 'module '.basename(__FILE__).' ('.__LINE__.'): '),true);
#
for ($cntYear = 0; $cntYear <  $endYear; $cntYear++) {
	$checkYear++; 
	$wuURL		= $wuURLs[$cntYear];  #echo print_r($wuURLs,true); ext;
	#
	ws_message ('<!-- module '.basename(__FILE__). '('.__LINE__.'): this year = '.$thisYear.', trying to read '.$wuURL.' -->',true); # echo print_r($wuURLs,true); ext;
	#
        $wsData	        = wsReportCurl ($wuURL);
        if ($wsData == false) 
             {  echo '</div><h4 style="text-align: center;">ERROR: no data for '.$wuURL.'<h4>'; 
                return;}
        $wsData         = trim($wsData);
        $wsData         = str_replace (PHP_EOL.'<br>','',$wsData);	// clean data
        $wsData         = str_replace ('<br>','',$wsData);			// 
	$firstLine	= true;
	$currentYear    = false;
        $arr            = explode ("\n", $wsData);
        foreach ( $arr as $line)
             {  $data   = explode (',', $line) ;
		$num    = count($data);
		if ($num < 2) {continue;}
		if ($firstLine) 		// first line has fieldnames
		     {  $firstLine = false;
			$nrFields = $num;                               #if ($wsDebug) { echo "<!-- $num header fields in line $row:".PHP_EOL;}
			if ($firstYearOnly) 	// save fieldnames in array
			     {  $firstYearOnly = false;
				for ($c=0; $c < $num; $c++) 
				     {  $fieldname	= $data[$c];    #if ($wsDebug) {echo $fieldname.PHP_EOL;}
					$fields[]	= trim($fieldname);}  #if ($wsDebug) {print_r($fields); echo ' -->'.PHP_EOL;}
			        } 
#  check which unit-type  is used for this file  either metric or english
			$fieldname	= trim($data[1]);  // should be either TemperatureHighF  or TemperatureHighC
			$string		= strtolower(substr($fieldname,-1) );
			if ($string == 'c') 		{$uomInput	= $uomMetric;} 
			elseif ($string == 'f') 	{$uomInput	= $uomEnglish;} 
			else {  echo '<h3>Error '.__LINE__.': File for '.$checkYear.' has unknown uom\'s'; 
			        exit;	}
			continue;
		        } // eo if row 1
		if ($num <> $nrFields) 
		     {  echo 'Error '.__LINE__.' in data-record '.$row.': Number of fields = '.$num.', expected '.$nrFields.PHP_EOL; 
		        continue;}
#	clean date field
		$string	=  $data[0];
		if (strpos ($string,'-') == false) 
		     {  echo 'Error '.__LINE__.' invalid data - stop'.print_r ($data,true); 
		        exit; }
		list ($year,$month,$Day) = explode ('-',$string);
		$month = substr('0'.$month,-2);
		$Day   = substr('0'.$Day,-2);
# process only records in range $validFrom  - $validUntil
		$dataDate	= $year.$month.$Day;
		if ($currentYear === false) {  $currentYear = $year;}
		if ($year > $currentYear)   {  break;}
		if ( ($dataDate > $validFrom) 
		 &&  ($dataDate < $validUntil) ) {
			$yearArray[0][$row]	= $year.$month.$Day;
			for ($k = 0; $k < count($request); $k++) {
				$fieldNr        = $request[$k];
				if ($kind == 'windrun' || $kind == 'wrun') 
				    {	$value	= convertUom (24 * (float) $data[13]);}
				else {  $value	= convertUom ($data[$fieldNr]);}
				$yearArray[$k+1][$row]= $value;
			}  // eo for requests
		$row++;
		}  // eo if in range
# end process in range
	}  // end of data
}	//  end of processing all files
#
ws_message (ws_debug_times( 'module '.basename(__FILE__).' ('.__LINE__.'): '),true);
#
$fields[]	= 'Windrum';		// add label for calculated field
#
#if ($wsDebug) {echo '<!-- '; print_r($yearArray); echo ' -->'.PHP_EOL;}
#	echo '<pre>'; print_r($yearArray);  exit;
#
if (    $type == 'summonthly' || 
        $type == 'sumseasonal') {  $script = $wsreportsDir.'wsReportsSum.php'; }
elseif ($type == 'freezedays')  {  $script = $wsreportsDir.'wsReportsFreeze.php'; }
else                            {  $script = $wsreportsDir.'wsReportsDaily.php';}
#
ws_message (  '<!-- module '.basename(__FILE__).' ('.__LINE__.'): loading '.$script.' -->');
include $script; 
echo '</div>'.PHP_EOL;
ws_message (ws_debug_times( 'module '.basename(__FILE__).' ('.__LINE__.'): '),true);
# credits
$link_leuven		= 'https://pwsdashboard.com/';
$link_wundergroundsite	= 'https://www.wunderground.com/dashboard/pws/'.$wuid;
$stringCredit		= '<br /><p class="blockHead" style="text-align: center"><small>';
$stringCredit		.= wsReporttransstr($trans.'Script developed by Wim van der Kuil of').' <a href="'.$link_leuven.'"  target="_blank">PWS_Dashboard</a>.&nbsp;&nbsp;';
$stringCredit		.= wsReporttransstr($trans.'The script uses the data from this station which is uploaded to').' <a href="'.$link_wundergroundsite.'" target="_blank">Weather Underground</a>.&nbsp;&nbsp;';
$stringCredit		.= '</small></p>'.PHP_EOL;
echo $stringCredit;

# missing language translations
if (isset ($ownTranslate) ) 
     {  echo '<!--  '.basename(__FILE__). '('.__LINE__.'): missing langlookup entries for lang='.$lang.PHP_EOL;
	foreach ($missingTrans as $key => $val) 
	     {  echo "langlookup|$key|$val|".PHP_EOL;}
	echo count($missingTrans).' entries.'.PHP_EOL.'End of missing langlookup entries -->'.PHP_EOL;}