<?php $scrpt_vrsn_dt  = 'fct_wxsim_convert2.php|01|2023-09-09|';   # PHP8.2 graph | 2012_lts
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
# ------------------- save list of loaded scrips
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#----------------------------- leuven compatable
function ws_debug_times($start = '') {
        global $ws_start_time, $ws_passed_time;
        if (!isset ($ws_passed_time)) {
                $ws_start_time = $ws_passed_time = microtime(true);
                return  $start.' Debug timers initialized';}
        $now            = microtime(true);
        $since_last     = $now - $ws_passed_time;
        $ws_passed_time = $now;    
        if ($since_last < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($since_last,4);}
        $until_last     = $now - $ws_start_time;
        if ($until_last < 0.0001) {$string2 = '< 0.0001';} else {$string2 = round($until_last,4);}  
        return $start.' Time spent until here: '.$string2.' seconds. Since last timestamp: '.$string1.' seconds.';
} // eof ws_debug_times
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') '.ws_debug_times('at load of '.$scrpt_vrsn_dt).PHP_EOL;
function ws_debug_info($start = '') {
        global $ws_start_time, $ws_passed_time;
        #$return = ws_debug_times($start).PHP_EOL;
        $size   = memory_get_peak_usage(true);
        $unit   = array('b','kb','mb','gb','tb','pb');
        $used   = round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        $seconds= microtime(true) - $ws_start_time;
        return  'Max memory used: '.$used.'('.$size.' bytes). Duration: '.round($seconds,5).' seconds'.PHP_EOL;
        } 
$wxsim_soil_wanted = false;
$yourLatitude   = $lat;
$yourLongitude  = $lon;
$yourIconDefault= false; 
$toTemp         = $tempunit;
$toRain         = $rainunit;
$toSnow         = $rainunit;
$toWind         = $windunit;
$toDist         = $distanceunit;
if     ($toWind == 'mph') { $toKnots = 0.868976;} 
elseif ($toWind == 'km/h'){ $toKnots = 0.5399568;} 
elseif ($toWind == 'm/s') { $toKnots = 1.943844;}
else                      { $toKnots = 1;}
$iconsFctOrg    = $iconsFctOrgExt = $iconsDef = $iconsDefExt = $ws_img_dir = '';
$iconsWind      = 'img/windicons/';
$iconsWindExt   = '.svg';
$wsRainDecimals = $dec_rain;
$windlabel      = array ('North','NNE', 'NE', 'ENE', 'East', 'ESE', 'SE', 'SSE', 'South',
		         'SSW','SW', 'WSW', 'West', 'WNW', 'NW', 'NNW');
#
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
$winddeg        = array (
"NORTH" =>   "0", "N" =>   "0", "NNE"=>  "23", "NE"=>  "45", "ENE"=> "68", 
"EAST"  =>  "90", "E" =>  "90", "ESE"=> "113", "SE"=> "135", "SSE"=> "158", 
"SOUTH" => "180", "S" => "180", "SSW"=> "203", "SW"=> "225", "WSW"=> "248", 
"WEST"  => "270", "W" => "270", "WNW"=> "292", "NW"=> "315", "NNW" => "338" );
$temp_colors    = array(
'#F6AAB1', '#F6A7B6', '#F6A5BB', '#F6A2C1', '#F6A0C7', '#F79ECD', '#F79BD4', '#F799DB', '#F796E2', '#F794EA', 
'#F792F3', '#F38FF7', '#EA8DF7', '#E08AF8', '#D688F8', '#CC86F8', '#C183F8', '#B681F8', '#AA7EF8', '#9E7CF8', 
'#9179F8', '#8477F9', '#7775F9', '#727BF9', '#7085F9', '#6D8FF9', '#6B99F9', '#68A4F9', '#66AFF9', '#64BBFA', 
'#61C7FA', '#5FD3FA', '#5CE0FA', '#5AEEFA', '#57FAF9', '#55FAEB', '#52FADC', '#50FBCD', '#4DFBBE', '#4BFBAE', 
'#48FB9E', '#46FB8D', '#43FB7C', '#41FB6A', '#3EFB58', '#3CFC46', '#40FC39', '#4FFC37', '#5DFC35', '#6DFC32', 
'#7DFC30', '#8DFC2D', '#9DFC2A', '#AEFD28', '#C0FD25', '#D2FD23', '#E4FD20', '#F7FD1E', '#FDF01B', '#FDDC19', 
'#FDC816', '#FDC816', '#FEB414', '#FEB414', '#FE9F11', '#FE9F11', '#FE890F', '#FE890F', '#FE730C', '#FE730C', 
'#FE5D0A', '#FE5D0A', '#FE4607', '#FE4607', '#FE2F05', '#FE2F05', '#FE1802', '#FE1802', '#FF0000', '#FF0000',);
$maxTemp        = count($temp_colors) - 1;
$distanceArr    = array  (
        "km"	=> array('km' => 1			, 'mi' => 0.621371192237	, 'nmi' => 0.540	, 'ft' => 3280.83989501 , 'm' => 1000 ),
        "mi"	=> array('km' => 1.609344000000865	, 'mi' => 1			, 'nmi' => 0.869	, 'ft' => 5280		, 'm' => 1609.344000000865 ),
        "nmi"	=> array('km' => 1.852			, 'mi' => 1.151			, 'nmi' => 1		, 'ft' => 6076.115	, 'm' => 1852 ),
        "ft"	=> array('km' => 0.0003048		, 'mi' => 0.000189393939394	, 'nmi' => 0.000165	, 'ft' => 1		, 'm' => 0.30480000000029017 ),
        "m"	=> array('km' => 0.001			, 'mi' => 0.000621371192237	, 'nmi' => 0.000540	, 'ft' => 3.28083989501 , 'm' => 1 ) 
        );
$ws_heat_wrd    = array(
        "0"	=> array('t' => 99,  'txt' => 'black', 'bg' => '',        'word' =>'Unknown'),
        "1"	=> array('t' => 54,  'txt' => 'white', 'bg' => '#BA1928', 'word' =>'Extreme Heat Danger'),
        "2"	=> array('t' => 50,  'txt' => 'white', 'bg' => '#E02538', 'word' =>'Heat Danger'),
        "3"	=> array('t' => 45,  'txt' => 'black', 'bg' => '#E178A1', 'word' =>'Extreme Heat Caution'),
        "4"	=> array('t' => 39,  'txt' => 'black', 'bg' => '#E178A1', 'word' =>'Extremely Hot'),
        "5"	=> array('t' => 34,  'txt' => 'white', 'bg' => '#CC6633', 'word' =>'Uncomfortably Hot'),
        "6"	=> array('t' => 29,  'txt' => 'white', 'bg' => '#CC6633', 'word' =>'Hot'),
        "7"	=> array('t' => 21,  'txt' => 'black', 'bg' => '#CC9933', 'word' =>'Warm'),
        "8"	=> array('t' => 16,  'txt' => 'black', 'bg' => '#C6EF8C', 'word' =>'Comfortable'),
        "9"	=> array('t' => 8,   'txt' => 'black', 'bg' => '#89B2EA', 'word' =>'Cool'),
        "10"	=> array('t' => -1,  'txt' => 'white', 'bg' => '#6699FF', 'word' =>'Cold'),
        "11"	=> array('t' => -10, 'txt' => 'white', 'bg' => '#3366FF', 'word' =>'Uncomfortably Cold'),
        "12"	=> array('t' => -17, 'txt' => 'white', 'bg' => '#806AF9', 'word' =>'Very Cold'),
        "13"	=> array('t' => -99, 'txt' => 'black', 'bg' => '#91ACFF', 'word' =>'Extreme Cold')					
        );
$lvl_bft        = array ( 1 ,  4,  7, 11, 17, 22, 28, 34, 41, 48, 56, 64, 999999999999 );   // https://simple.wikipedia.org/wiki/Beaufort_scale
$bft_names      = array( /* Beaufort 0 to 12 in English */
	'Calm', 
	'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
	'Strong breeze', 'Near gale', 'Gale force', 'Stronggale', 'Storm',
	'Violent storm', 'Hurricane');
$bft_color      = array(
        '', '', '', '', '', 
        '#FFFF53', '#FFFF53', '#F46E07', '#F00008', '#F36A6A', '#6D6F04',
        '#640071', '#650003' );
$bft_txt_color  = array(
        'black',  'black', 'black', 'black', 'black',
        'black',  'black', 'black', 'black', 'black', 'yellow',
        'yellow', 'yellow' );
#       
function ws_message ($txt, $do=false, $string=false) {
        global $stck_lst;
        if ($do <> false  ) {echo $txt.PHP_EOL;}
 #       elseif (  isset ($_REQUEST['test']) ) {echo $txt.PHP_EOL;}
        $from   =  array ('<!--','-->','module');
        $echo   = trim(str_replace($from, '',$txt));
        if (strlen($echo) > 120) { $echo = substr($echo,0,110).' . . . truncated';}
        $stck_lst .= $echo.PHP_EOL; 
        }
function ws_num_format($a, $b) {return $a;}

function ws_cvt_temp($a,$b) {
        global $tempunit, $dec_tmp;
        return convert_temp ($a, $b, $tempunit,$dec_tmp);
       }  
function ws_temp_colored($value, $a='', $b='') {
        global $tempunit, $maxTemp, $temp_colors;
        if ($value === 'n/a' || $value === false) 
            {   return '<!-- no value '.$value.' -->'.PHP_EOL; return;}
        $tmp    = (float) $value; 
        if ($tempunit <> 'C')
             {  $tmp    = round (    5*( ($tmp -32)/9) );}
        $n      = 32 + (int) $tmp;
        if ($n > $maxTemp)      
             {  $color  = $temp_colors[$maxTemp];}
        else {  $color  = $temp_colors[$n];}
        return $color;
        }

function ws_cvt_wind ($a, $b ) {
        global $windunit, $dec_wnd;
        return convert_speed ($a,$b,$windunit,$dec_wnd); }
function ws_clc_compass($value) {
       global $windlabel;
        $compass= windlabel($value );
        if (strlen ($compass) > 3) 
             {  $compass = substr ($compass,0,1);}
        return $compass;
        }
function ws_clc_degrees($value) {
        global $winddeg;
        $uc      = strtoupper ($value);
        if (!isset ($winddeg[$uc]) ) 
             {  return  "1";}
        else {  return  $winddeg[$uc];}
        }

function ws_cvt_baro ($value, $usedUnit, $reqUnit='n/a') {
        global $pressureunit, $dec_baro;
        return convert_baro ($value,$usedUnit,$pressureunit,$dec_baro) ;
        }
function ws_cvt_rain ($value, $usedUnit, $reqUnit='n/a') {
        global $rainunit, $dec_rain;
        return convert_baro ($value,$usedUnit,$rainunit,$dec_rain) ;
        }
function ws_cvt_dist ($value, $usedUnit, $reqUnit='n/a') {
        global $stck_lst, $distanceArr, $distanceunit; # echo __LINE__.'distance >'.$usedUnit,'< >'.$distanceunit.'<'; exit;
        if (!isset ($distanceArr[$usedUnit][$distanceunit]) ) {
                $stck_lst .= basename(__FILE__).' ('.__LINE__.') invalid UOM used in ws_cvt_dist'.PHP_EOL;;
                return 1;} 
        else {  return $distanceArr[$usedUnit][$distanceunit];}
        }

function ws_feels_like ($temp,$chill,$heat,$uom) {
        global $ws_heat_wrd;
# establish the feelslike temperature and return a word describing how it feels
# first clean and convert all temperatures to Centigrade if needed
	$TC = 1* preg_replace('|,|','.',$temp);
	$WC = 1* preg_replace('|,|','.',$chill);
	$HC = 1* preg_replace('|,|','.',$heat);
# convert F to C if needed
	if (preg_match('|F|i',$uom))  
	     {  $TC = ws_cvt_temp($TC, 'f','c');
		$WC = ws_cvt_temp($WC, 'f','c');
		$HC = ws_cvt_temp($HC, 'f','c');}
# Feelslike
	if ($TC <= 16.0 )     {	$feelslike = $WC; }  //use WindChill
	elseif ($TC >=27.0)   { $feelslike = $HC; }  //use HeatIndex     
        else                  {	$feelslike = $TC; }  // use temperature
#
	$hcWord = $ws_heat_wrd[0];	// default = unknown
	$end	= count ($ws_heat_wrd);
	#
	for ($n = 0; $n < $end; $n++) 
	     {  if ($feelslike >= $ws_heat_wrd[$n]['t']) {break;} }
	if (preg_match('|F|i',$uom))   // convert C back to F if need be
	     {  $feelslike = ws_cvt_temp($feelslike, 'c','f');}
	$feelslike	= round($feelslike,0);	
	$colorTxt	= $ws_heat_wrd[$n]['txt'];
	$colorBg	= $ws_heat_wrd[$n]['bg'];
	$word		= $ws_heat_wrd[$n]['word'];
	$words		= ws_translate($word);
	$hcHTML 	= '<span style="border: solid 1px; color: '.$colorTxt.'; background-color: '.$colorBg.';">&nbsp;'.$words.'&nbsp;</span>';
        $ws_script_func = '<!-- module '.basename(__FILE__).' => '.__FUNCTION__; 
#	ws_message($ws_script_func.' ('.__LINE__.'): input T,WC,HI,U = '."$temp,$chill,$heat,$uom' cnvt T,WC,HI='$TC,$WC,$HC' feelslike=$feelslike hcWord=$hcHTML -->");		
	return array($feelslike,$hcHTML);	
    } // end of Feelslike  
function ws_translate ($string, $split_on = 'n/a') {
        $return =  lang (  $string );
        return $return;}
function ws_beaufort_number($speed,$toWind) {
        global $toKnots, $lvl_bft;
        $spd_knts = (int) $speed * $toKnots;
        foreach ($lvl_bft as $key => $lvl)
             {  if ($spd_knts > $lvl) {continue;}  # $key=12; # for test 
                break;}
        return $key;
        $arr['bftt']    = lang($bft_txt[$key]); 
        }
function ws_beaufort_colors ($beaufortnumber) {
        global $bft_names, $bft_color, $bft_txt_color;
        $number = round(1 * $beaufortnumber,0);
        if ($number < 0 ||  $number >=  count ($bft_names) ) {$number = 0;}
	$background_color       = $bft_color[$number];
	$text_color             = $bft_txt_color[$number];
	$name                   = $bft_names[$number];
	if ($background_color == '') {$level = false; $background_color = 'transparent';} else {$level = true;}
	return array  ($name, $level, $background_color, $text_color);	
        }   
function ws_save_cache($ws_cached_file, $ws_wxsim_arr,$proceed= true) {
        global $ws_cached_file, $ws_wxsim_arr;
        if (file_put_contents($ws_cached_file, serialize($ws_wxsim_arr)))
             {  ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): '.$ws_cached_file.' saved OK  -->' );
                return true;}
        if ($proceed == true)
             {  ws_message('<!-- module '.basename(__FILE__).' ('.__LINE__.'): ERROR Could not save data to cache '.$ws_cached_file.' -->',true );
                return true;}
        exit ('<small style="color: red;">'.basename(__FILE__).' ('.__LINE__.'): Could not save data to cache ('.$ws_cached_file.'). <br>
Please make sure your cache directory exists and is writable.</small>'.PHP_EOL);        }  
#-----------------------------------------------
# calculate DST <=> standard time difference
#-----------------------------------------------
$one            = strtotime('20171225T220000'); 
$two            = strtotime('20170625T220000');
$diff1          = date('O',$one); // Difference to Greenwich time (GMT) in hours Example: +0200
$diff2          = date('O',$two);
$DST_offset     = abs($diff1 - $diff2)*36;
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): If needed the DST to normaltime difference is '.$DST_offset.' seconds  -->');     
#-----------------------------------------------
# constants
#-----------------------------------------------
# array with all known fields and their specifications
$ws_wxsim_flds  = array ();
$ws_wxsim_cnds  = array ();
load_D_arrays ();
#-----------------------------------------------
$ws_wxsim_arr   = false;        // intial setting for retrieved data
$loadNew        = false;
#
if (!file_exists($ws_fileToUse) && !file_exists($ws_cached_file) ) // no cache and no data file
     {  echo '<small style="color: red;">Error '.__LINE__.': input file not found, cache file not found either '.$ws_fileToUse.'<h3>'; 
        return;}   
#
# Check contents of cache
if (file_exists($ws_cached_file))
      {	$timeOfCache    = filemtime($ws_cached_file);}
else  { $loadNew        = true;}
#
if ( $loadNew == false && file_exists($ws_fileToUse) )
     {  $ws_time_of_file     = filemtime($ws_fileToUse); 
        if (isset ($_REQUEST['force']) && $_REQUEST['force'] == 'wxsim')
             {  $loadNew = true;
                ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): '
                        .'(Re)load data as ?force=wxsim is used. -->', true);
                }
        } 
#
if ($loadNew == false && $ws_time_of_file < $timeOfCache ) 
     {  ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Datafile '.$ws_fileToUse.' will be loaded from cache '.$ws_cached_file.'  -->');     
        $ws_wxsim_arr   = unserialize(file_get_contents($ws_cached_file));
        if (is_array ($ws_wxsim_arr) )
             {  ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Datafile '.$ws_fileToUse.' is loaded from cache '.$ws_cached_file.'  -->');     } 
	ws_message (basename(__FILE__).' ('.__LINE__.'): '.ws_debug_times());
	return; }
#
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Datafile '.$ws_fileToUse.' will be used -->');    
#
$wxsimFile      = file($ws_fileToUse);  
#
if ($wxsimFile === false) 
     {  echo '<small style="color: red;">Error '.__LINE__.': No forecast data can be retrieved, check  settings / html for errors</small>'; 
        return; }
#
$countLines     = count($wxsimFile);
$fileIsCorrect  = true;
$error          = '';           
#
# check the file if it contains valid data
if ($countLines < 10 || strlen ($wxsimFile[0]) < 4)   
     {  $error          = '<small style="color: red;">Error '.__LINE__.': To short =>'.substr($wxsimFile[0],0,20)
                                .'<= data-lines or to few =>'.$countLines.'<= lines in file<br>probably not a '.$wxsim_file.'</small>';}
elseif  (substr($wxsimFile[0],0,4) == 'WXSI') 
     {  $filetype       = 'lastret'; }
elseif  (substr($wxsimFile[0],0,4) == 'Year')
     {  $filetype       = 'latest'; }
else {  $error          = '<small style="color: red;">Error '.__LINE__.': invalid file fed to the program - this program will fail</small>';} 
#
if ($error <> '')
     {  echo $error.substr($wxsimFile[0],0,50); 
        return;} 
#
if ($filetype == 'lastret') {
#----------------------- pre-process lastret.txt
        $separator	= ' ';						// the characters which separate the fields
        $wxsimFile	= preg_replace( '/\,/', '.',  $wxsimFile );	// replace , with . for decimal spaces
        $lineRaw	= preg_replace( '/\s+/', ' ', $wxsimFile[6]) ; 	// remove white space from line 6 with the field names
        $lineNames	= explode ($separator, $lineRaw);
        $lineNames	= array_reverse($lineNames);
        # check gust:
        $key    = $ws_wxsim_flds['gust']['nameTxt'];  
        if (!in_array($key, $lineNames)) 
             {  if      (in_array('G1HR', $lineNames)) {$ws_wxsim_flds['gust']['nameTxt'] = 'G1HR';}
                elseif  (in_array('G1MN', $lineNames)) {$ws_wxsim_flds['gust']['nameTxt'] = 'G1MN';}
                elseif  (in_array('G10M', $lineNames)) {$ws_wxsim_flds['gust']['nameTxt'] = 'G10M';}
                elseif  (in_array('G6HR', $lineNames)) {$ws_wxsim_flds['gust']['nameTxt'] = 'G6HR';}}
        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Fieldnames: '.PHP_EOL.'<pre>'.print_r($lineNames,true).' -->', true);   
        $countNames 	= count($lineNames);		// number of fieldnames in lastret.txt  + 2 blank entries
        $countFields	= count($ws_wxsim_flds);	// number of fieldnames known to the program
        #check uoms
        $firstUomLine   = $countLines - 7;		// uoms are mostly found at the end of the lastret.txt file
        if (trim($wxsimFile[$firstUomLine]) ==  'Units:') {$firstUomLine++;}    // units is the heading found 1 line before the uoms
        #  check of next line is units otherwise use defaults. some versions it is a line later 
        #                        
        $uomString 	= trim($wxsimFile[$firstUomLine]).'.'.trim($wxsimFile[$firstUomLine + 1]).'.'.trim($wxsimFile[$firstUomLine + 2]);
        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): uoms: '.$uomString.' -->'.PHP_EOL);
        $uomArr         = explode ('.', $uomString);
        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): uoms: '.'<pre>'.print_r($uomArr,true).'</pre> -->');
        $fromTemp = $fromRain = $fromSnow = $fromWind = $fromDist = '';
        foreach ($uomArr as $string) 
             {  list ($unit,$uom)     = explode (':',$string.':'); 
                $unit   = trim(strtolower ($unit));
                $uom    = ' '.strtolower ($uom); 
                switch ($unit) 
                    {   case 'temperature':
                                if (strpos($uom,'c'))  { $fromTemp = 'c'; } else { $fromTemp = 'f';}
                                break;
                        case 'precipitation':
                                if (strpos($uom,'mm')) { $fromRain = 'mm'; } else { $fromRain = 'in';}
                                break;
                        case 'snow':
                                if (strpos($uom,'cm')) { $fromSnow = 'cm'; } else { $fromSnow = 'in';}
                                break;
                        case 'wind':
                                if     (strpos($uom,'km')) { $fromWind = 'kmh';} 
                                elseif (strpos($uom,'mi')) { $fromWind = 'mh';} 
                                elseif (strpos($uom,'kn')) { $fromWind = 'kts';} 
                                else                       { $fromWind = 'ms';}
                                break;
                        case 'visibility':
                                if     (strpos($uom,'km')) { $fromDist = 'km';}  else { $fromDist = 'mi';}
                                break;
                        default; break;} 
        } // eo foreach
        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): uoms: '.'| $fromTemp = '.$fromTemp.'| $fromRain = '.$fromRain.'| $fromSnow = '.$fromSnow.'| $fromWind = '.$fromWind.'| $fromDist = '.$fromDist.' -->');
        # add missing uoms, expect them to be equal the normally used uoms.
        $missingUOM = '';
        if ($fromTemp   == '') {$fromTemp   = $toTemp;  $missingUOM .= 'temperature';}
        if ($fromRain   == '') {$fromRain   = $toRain;  $missingUOM .= 'precipitation';}
        if ($fromSnow   == '') {$fromSnow   = $toSnow;  $missingUOM .= 'snow';}
        if ($fromWind   == '') {$fromWind   = $toWind;  $missingUOM .= 'wind';}
        if ($fromDist   == '') {$fromDist   = $toDist;  $missingUOM .= 'visibility';}
        if ($missingUOM <> '') ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Following UOMs where not found: '.$missingUOM.' -->',true);
        #
        $fromBaro   = $toBaro;                          #####
} 
#-----------------------------------------------
else {
#------------------------ pre-process latest.csv
        $pos            = strpos($wxsimFile[0],';');   			// check which separator is used
        if ($pos === false) {$separator = ' ,';} else {$separator = ';';} #echo '<pre> separator = '.$separator; exit;
        $lineNames 	= explode($separator, $wxsimFile[0]);
        for ($i = 0; $i < count ($lineNames); $i++) 			// first get rid of ( sometekst ) in field names
             {  $pos    = strpos($lineNames[$i],'(');
                if (!$pos) {continue;}
                $lineNames[$i] = substr($lineNames[$i],0,$pos-1);}
        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Fieldnames: '.PHP_EOL.'<pre>'.print_r($lineNames,true).' -->');   
        $countNames = count($lineNames);				// number of fieldnames in in latest file
        $lineUoms	= explode($separator, $wxsimFile[1]);
        for ($i = 0; $i < count ($lineUoms); $i++) 			// first get rid of ( sometekst ) in field uom
             {	$pos 	= strpos($lineUoms[$i],'(');
                if (!$pos) {continue;}
                $lineUoms[$i] = substr($lineUoms[$i],0,$pos-1);}
        $uomString 	= $wxsimFile[1];
        #ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): uoms: '.$uomString.' -->'.PHP_EOL);
        $fromTemp = $fromRain = $fromSnow = $fromWind = $fromDist = $fromBaro = '';
        $pos            = strpos($uomString,'deg ');                    // found a temp uom
        if (!$pos) 
             {  echo '<h3>module '.basename(__FILE__).' ('.__LINE__.'): error retreiving standard data, file seems corrupt, uoms found: '.$uomString.'<h3>';
                exit;}
        $tempFound      = strtolower(substr($uomString,$pos+4,1) );
        if ($tempFound == 'c')                { $fromTemp = 'c'; } 
        else                                  { $fromTemp = 'f'; }
        if (strpos($uomString,'inches'))      { $fromRain = $fromSnow = 'in';} 
        else                                  { $fromRain = 'mm'; $fromSnow = 'cm';}
        if      (strpos($uomString,'mi/hr'))  { $fromWind = 'mh';}
        elseif  (strpos($uomString,'km/hr'))  { $fromWind = 'kmh';} 
        elseif  (strpos($uomString,'knots'))  { $fromWind = 'kts';}
        else                                  { $fromWind = 'ms';}
        if      (strpos($uomString,'miles'))  { $fromDist = 'mi';}
        else                                  { $fromDist = 'km';}
        if      (strpos($uomString,'mb'))     { $fromBaro = 'hpa';}
        else                                  { $fromBaro = 'in';}

        ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): uoms: '.'| $fromTemp = '.$fromTemp.'| $fromRain = '.$fromRain.'| $fromSnow = '.$fromSnow.'| $fromWind = '.$fromWind.'| $fromBaro = '.$fromBaro.'| $fromDist = '.$fromDist.' -->');
        # add missing uoms, expect them to be equal the normally used uoms.
        $missingUOM = '';
        if ($fromTemp   == '') {$fromTemp   = $toTemp;  $missingUOM .= 'temperature';}
        if ($fromRain   == '') {$fromRain   = $toRain;  $missingUOM .= 'precipitation';}
        if ($fromSnow   == '') {$fromSnow   = $toSnow;  $missingUOM .= 'snow';}
        if ($fromWind   == '') {$fromWind   = $toWind;  $missingUOM .= 'wind';}
        if ($fromDist   == '') {$fromDist   = $toDist;  $missingUOM .= 'visibility';}
        if ($fromDist   == '') {$fromDist   = $toDist;  $missingUOM .= 'visibility';}
        if ($fromBaro   == '') {$fromBaro   = $toBaro;  $missingUOM .= 'pressure';}
        if ($missingUOM <> '') ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Following UOMs where not found: '.$missingUOM.' -->',true);
        #
        # clean up the data in the CSV
        if ($separator <> ';') 
             {  $wxsimFile	= preg_replace('/\ ,/', ';', $wxsimFile ); 	// replace space+comma with ; which is now the separator for the data
                $separator	= ';';} 
        $wxsimFile		= preg_replace( '/\,/', '.',  $wxsimFile );     // change decimal , to point
} 
#-----------------------------------------------
#      create a lookup table of the  fields this 
#     program knows of and could be in the input 
#-----------------------------------------------
$arrLookup = array();
foreach ($ws_wxsim_flds as $key => $arr) {
        if ($filetype == 'lastret') 
             {  $name = $arr['nameTxt'];} 
        else {  $name = $arr['nameCsv'];}
	$arrLookup[$name] = $key;} 
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Lookup table'.print_r($arrLookup, true).' -->'); 
# loop through all fieldnames in input file to check if we need those fields
for($i = 0; $i < $countNames; $i++) {
        $fieldName      = trim($lineNames[$i]);
	if ($fieldName == '') {continue;}	// blank part of line with names
	$key ='n/a';
	if (isset ($arrLookup[$fieldName]) ){	// the field from the input file is known / wanted in our table
		$key = $arrLookup[$fieldName];
		$ws_wxsim_flds[$key]['loc'] = $i;  }   // save location of field in input file
	ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): '.$i.' - '.$fieldName.' - '.$key.' -->');} 
// eo for checking fields names and saving locations
#
if ($ws_wxsim_flds['tempGrass']['loc'] == 0)   // even if set to display soil page, no input fields found does override setting 
     {  ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): SOIL: no tempGrass was found. $wxsim_soil_wanted changed from true to false -->',true); 
        $wxsim_soil_wanted	= false;}  
if (!isset ($wxsim_soil_wanted) || $wxsim_soil_wanted == false)  	// skip all soil fields if no soil fields wanted tempGrass is not there, lastret.txt field: GRS  latest.csv Grass Temperature
     {  $ws_wxsim_flds['tempGrass']['loc']      = $ws_wxsim_flds['tempSurf']['loc']	= $ws_wxsim_flds['tempSoil1']['loc']	= 
        $ws_wxsim_flds['tempSoil2']['loc']	= $ws_wxsim_flds['tempSoil3']['loc']    = $ws_wxsim_flds['tempSoil4']['loc']    = $ws_wxsim_flds['tempSoil5']['loc']  = 0;
        $ws_wxsim_flds['moist1']['loc']         = $ws_wxsim_flds['moist2']['loc']       = $ws_wxsim_flds['moist3']['loc']       = $ws_wxsim_flds['moist4']['loc']     = 0;}
#
# save all field names in use for this input file into output array   $ws_wxsim_arr
if (!is_array($ws_wxsim_arr) ) {$ws_wxsim_arr = array();}
$ws_wxsim_arr['details'][0]['intDate']	='time';	// the date-time of each line in the forecast file
#-----------------------------------------------
# save all used fields for reference later
#-----------------------------------------------
$fieldsUsed     = array(); #echo '<pre>'.print_r($ws_wxsim_flds,true).'</pre>'; exit;
foreach ($ws_wxsim_flds as $key => $arr) {
        if ($arr['loc'] == 0) {continue;}	// skip fields which are not in the lastret / latest file
	$ws_wxsim_arr['details'][0][$key]  = $arr['loc'];	// save our field name into array
# and store all fields from the field array which are not used in this forecast 
        $fieldsUsed[$key]       = $arr; }  # echo '<pre>'.print_r($fieldsUsed,true); exit;
unset ($ws_wxsim_flds);
$ws_wxsim_arr['details'][0]['condCloud']='calculated';
$ws_wxsim_arr['details'][0]['condRain']	='calculated';
$ws_wxsim_arr['details'][0]['snowFound']= 0;    #echo '<pre>'.print_r($ws_wxsim_arr['details'],true).'</pre>'; exit;
#-----------------------------------------------------------------------
# Calculate time offset to whole hour
# so that all forecast lines are at the whole hour / once per hour
# 1:55p  2:25p  2:55p  => 2:00p 2:30p  3:00p
#-----------------------------------------------------------------------
$minuteOffset   = 0;
if ($filetype == 'lastret') {
/* two example lines which are correctly alligned to the full hour
12:00a P.CLOUDY LIGHT DEW  14,8  12,3  14,3  14,5  12,4 . . . .            2017-07-09_22:00_UTC
12:30a P.CLOUDY LIGHT DEW  14,4  12,2  13,9  14,1  12,7 . . . .            2017-07-09_22:30_UTC

01234  <- string position  */
$rawMinRight 	= substr($wxsimFile[8],4,1);   // line 8 is first data line position 01234 length 1
if ($rawMinRight <> '0') {   // not a 10 minute multiple
        $rawMin = substr($wxsimFile[8],3,2);
        $i  = 60 - $rawMin;
        if ($i > 30) {$i = $i - 30;}
        $minuteOffset = $i;} 
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Time offset = calculated as : '.$minuteOffset.' -->');
} // eo lastret time difference
else {
/* example lines with latest.csv
#  2013 , 1 , 1 , 10 ,   = clean hour input
#  2013 , 1 , 1 , 10.5 ,   = clean half hour input
#  2012 , 12 , 31 , 20.917 , = 5 min before hour line
# change , to . ;round up minutes ; difference with minutes = $minuteOffset  */
for ($i = 2; $i < 6; $i++) {
        $string			= preg_replace( '/\s+/', ' ', $wxsimFile[$i]) ; // remove white space from first data line (third line in input)
        $arr			= explode($separator, $string);
        $arr2                   = explode ('.',$arr[3].'.0');            
        $raw[$i-2]		= (float)('0.'.$arr2[1]);}
$rawMin		= max($raw);            // "closest to the hour
$rawMinMin	= min($raw);	
$rndMin		= ceil($rawMin);
$minuteOffset	= $rndMin - $rawMin;
if ($minuteOffset == 0.5 && $rawMin <> $rawMinMin) {$minuteOffset = 0;}
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): Time offset = calculated as: $rawMin ='.$rawMin. ' $minuteOffset = '.$minuteOffset.' -->');
} // eo latest time difference
$utcDiff        = date ('Z'); 
#-----------------------------------------------------------------------
# end of  Calculate time offset to whole hour
#-----------------------------------------------------------------------
#-----------------------------------------------------------------------
# MAIN LOOP of every forecast line
#-----------------------------------------------------------------------
# loop through data lines in input file 
# lastret.txt - first line:  8  last line contains "Nighttime lows and daytime highs"
# latest.csv  - first line:  3  last line = last line
$eoFileLastret 	= 'lows and daytime highs';
$linesOK	= 1;	// pointer to current line into output data array. element 0 contains the names of the fields
$rainTotal	= 0;	// input file contains total rain in each line, used to calculate rain in period for this line
$evoTotal	= 0;    // evotransp is total field also
$ws_parts_start = true; // first time switch
$partsOK        = 0;    // pointer to correct 6 hour daypart
$unusedPrecip   = array ('Light ','Chance ', 'of ', 'Probably ', 'Moderate ', 'Heavy ', 'and or ');
$unusedClouds   = array ('Mostly ', 'Partly ');
$dayParts       = array (  0 => 'Evening', 1 => 'Night', 2 => 'Morning', 3 => 'Afternoon', 4 => 'Evening' );
#
for ($i = 2; $i < $countLines ; $i++) {
        $lineRaw = $wxsimFile[$i];
        if ($lineRaw == PHP_EOL ||  strpos ($lineRaw, '------') == true)
             {  continue; }     // skip all unneeded lines  
        if (strpos ($lineRaw, $eoFileLastret) == true )
             {  break; }
#echo $lineRaw.PHP_EOL;
        $lineRawParts   = explode ($separator, preg_replace( '/\s+/', ' ', $lineRaw) );
#-----------------------------------------------------------------------
# Skip unneeded lines and calculate correct time for each  forecast line
#-----------------------------------------------------------------------
        if ($filetype == 'lastret') {
                $sortedData = array_reverse($lineRawParts);     // data array
                if (    !isset ($sortedData[2]) ||              // skip unneded lines
                        !is_numeric($sortedData[2]) ) 
                     {  continue; }
# lastret has a UTC time field which can be used after cleaning                  
                $txtTime        = str_replace ('_UTC',':00UTC', $sortedData[1]);
                $txtTime        = str_replace ('_','T', $txtTime);
                $intTime        = ($minuteOffset*60) + strtotime($txtTime); 
#  $datum = date ($timeFormat,$intTime);  print_r( $sortedData); echo $txtTime.' => '.$datum; exit;               
                }                   
# latest csv has only seperate time fields including an "23 hour" error      
        else {  $sortedData     = $lineRawParts;    // data array
                $extraHour      = $extraDay  = 0;
# 2017 ; 7 ; 9 ; 22 ;   <=  this is the only date/time format available,
# 2017 ; 7 ; 9 ; 22.5 ; <=  this line used in comments below
# 2017 ; 7 ; 10 ; 23 ;  <=  this is always in error: at 23:00 hours it already jumps to the next day
# 2017 ; 7 ; 11 ; 23,5 ;
# 2017 ; 7 ; 11 ; 0 ;
# 2017 ; 7 ; 11 ; 0,5 ;
# 2017 ; 7 ; 11 ; 1 ;
#  0    1   2     3     <= array position after explode for ;
                $rawHour	= (float)$lineRawParts[3] + $minuteOffset; // 22.5 + 0
                $rawMinutes     = round(60 * ($rawHour - floor($rawHour))); // 60 * (22.5 - 22)  => 30
                $rawHour        = floor($rawHour); // 22
                if ($rawMinutes > 59) //  can be caused by adding $minuteOffset
                     {  $rawMinutes     = $rawMinutes - 60;
                        $extraHour      = 1;}
                if ($rawHour > 24 ) // can be caused by adding extra hour from minutes
                     {  $rawHour        = $rawHour - 24;
                        $extraDay       = 1;}
                $rawMinutes     = substr('00'.$rawMinutes,-2,2); // 0030 => 30
                $rawHour        = substr('00'.$rawHour,-2,2);    //  001 => 01
               	$dateString     =       // 20170709T223000
                        trim($lineRawParts[0]).       // 2017
                        substr('00'.trim($lineRawParts[1]),-2,2).  // 007 => 07
                        substr('00'.trim($lineRawParts[2]),-2,2).  // 009 => 09
                        'T'.
                        $rawHour.       // 22
                        $rawMinutes.    // 30
                        '00';           // 00	 for the aseconds
                $intTime        = strtotime($dateString) + $extraHour*3600 + $extraDay*24*3600;
                if ( date ('i',$intTime) <> '00') {continue;} 
#echo $lineRaw.'<br><br>'.PHP_EOL; echo '$minuteOffset='.$minuteOffset.' - '.$intTime.' - '.date('c',$intTime); exit;
# now we try to remove the "23 hour" error which can occur at other hours also
                if (!isset ($previousTime) )  { $previousTime = $intTime; }  
                $difference     = $intTime - $previousTime;
                if ($difference > 23*3600) {$intTime = $intTime - 24*3600;} 
                $previousTime = $intTime;
                $DST_on = date('I',$intTime);
                if ($DST_on) {$intTime += $DST_offset;}
ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): For '.$dateString.' daylight saving is 1-on/0-off: '.$DST_on.' -->');
        } // eo latest.csv correcting time processing
#-----------------------------------------------------------------------
# Store all data fields 1 record / hour
#-----------------------------------------------------------------------
        if ( date ('i',$intTime) <> '00') {continue;} 
        $correctLine    = array();
        $correctLine['intDate'] = $intTime;             #echo '<pre>'.$lineRaw.PHP_EOL;
        foreach ($fieldsUsed as $key => $arr)
             {  $location       = $arr['loc'];
                if (!isset($sortedData[$location])){continue;} // should NOT occur, but it does sometimes
                $value  = trim($sortedData[$location]);
# rain is a cumulative value. get the value for this line  only              
                if ($key == 'rain') 
                     {	$string         = 'rain $value='. $value.' old total = '.$rainTotal;
                        $value          = round(($value - $rainTotal),4);
                        $rainTotal      = round((float) $sortedData[$location],4);
                        $string         .= ' new  total = '.$rainTotal.' new value = '.$value .'<br>'.PHP_EOL;
                        #echo $string;
                        }                      
# evo-transp is a cumulative value. get the value for this line  only   ###########    
                if ($key == 'evotrans_T') 
                     {	$string         = 'EV $value='. $value.' old total = '.$evoTotal;
                        $value          = round(($value - $evoTotal),5);
                        $evoTotal       = round((float) $sortedData[$location],5);
                        $string         .= ' new EV total = '.$evoTotal.' new value = '.$value .'<br>'.PHP_EOL;
                        #echo $string;
                        }
# if a snow value is found the snowindicator is set so the snow graph can be displayed 
                if ($key == 'snow' && $value <> 0) 				
               	     {  $snowFound = true;}
                $correctLine[$key]   = $value;  
        } // eo each field in one record
#echo '<pre> correctline: = '.print_r($correctLine,true).'</pre>';
 #-----------------------------------------------------------------------
# try to make an understandable condition filed
#-----------------------------------------------------------------------
        if ($filetype == 'lastret') 
             {  $textClouds     = substr ($lineRaw, 7,9);
		$textPrecip     = substr ($lineRaw, 16,10).' ';} 
	else {  $cnd1	        = $sortedData[$fieldsUsed['cnd1']['loc']];
		$cnd2	        = $sortedData[$fieldsUsed['cnd2']['loc']];		
		$textClouds     = str_replace ($cnd2,'',$cnd1);
		$textPrecip     = $cnd2; }
# rain expected?
	if (isset ($correctLine['rain']) ) 	
	     {  $rain = $correctLine['rain'];} 	
	else {  $rain  = 0;}
# cloudcover
	if (isset ($correctLine['skyCover']) )	
	     {  $cloud = $correctLine['skyCover']; } 
        elseif (isset ($correctLine['skyCover2']) )	
             {  $cloud  = $correctLine['skyCover2'];
		$correctLine['skyCover']     = $cloud;} 
	else {  $cloud  = 50;  } // no cloudcover fields present in input
	if (isset ($correctLine['skyCover2']) )	
	     {  $cloud  = $correctLine['skyCover2'];}
        $cover          = $cloud;
        $arrClouds      = $ws_wxsim_cnds[trim($textClouds)]; // array ('code' => 100, 'cond' => 'sky', 'text' =>'clear mostly' );
	switch (TRUE)
	    {   case ($cover < 5): 	
			$clouds = 0;
			if ($rain > 0) {$clouds = 100;}			
			break;
		case ($cover < 25): $clouds = 100; break;
		case ($cover < 50): $clouds = 200; break;
		case ($cover < 80): $clouds = 300; break;
		default: $clouds = 400; break;	}
	if ($arrClouds['code'] > $clouds) {   $clouds = $arrClouds['code'];  }
	$textClouds     = $arrClouds['out'];
 	$precip         = 0;
 	$use_cnd2        = -1;
	if (trim($textPrecip) <> '') {
	#
                $arrPrecip      = $ws_wxsim_cnds[trim($textPrecip)]; 
                $textPrecip     = $arrPrecip['out'];
#
                if 	(strpos($textPrecip, 'snow')) 		{$precip = 20;}
                elseif	(strpos($textPrecip, 'flurries')) 	{$precip = 20;}
                elseif	(strpos($textPrecip, 'sleet'))		{$precip = 30;}
                elseif	(strpos($textPrecip, 'rain')) 		{$precip = 10;}
                elseif	(strpos($textPrecip, 'drizzle')) 	{$precip = 10;}
                elseif	(strpos($textPrecip, 'showers')) 	{$precip = 10;}	
                elseif	(strpos($textPrecip, 'fog'))  		{$precip = 50;}
#	
                if (!strpos($textPrecip, 'frost') && !strpos($textPrecip, 'dew')) {
                        if 	(strpos($textPrecip, 'light'))		{$precip = $precip + 0;}
                        elseif 	(strpos($textPrecip, 'moderate'))	{$precip = $precip + 1;}
                        elseif 	(strpos($textPrecip, 'heavy'))		{$precip = $precip + 2;}
                }			
                if ($arrPrecip['code'] > $precip)    {     $precip = $arrPrecip['code'];}
                $use_cnd2       = $arrPrecip['code'];
# echo '<!-- '.$textClouds.' '.$clouds.' '.$textPrecip.' '.$precip.' -->'.PHP_EOL;
	} // precip not empty
	$correctLine['cnd1']         = trim($textClouds);
	if ($use_cnd2  < 0) {$correctLine['cnd2'] = '';} else {$correctLine['cnd2'] = trim($textPrecip);}
	$icon           = $clouds + $precip;
	if ($icon > 0 && $icon < 100) 
	     {  $icon  += 100;}
	$iconString    = substr('000'.$icon,-3,3);
        $iconYrno       = $ws_wxsim_lookup[$iconString];
        $iconDefault    = $iconString;
        
	$time           = $correctLine['intDate'];
        $sun_arr= date_sun_info((int) $time, $lat, $lon);
	$srise 	= $sun_arr['sunrise']; #  
	$sset 	= $sun_arr['sunset'];
	if ($time < $srise || $time > $sset) 
	     {  $night  = 'n';} 
	else {  $night  = 'd';}
	$iconYrno       .= $night ;
	if ($icon < 500 && $night  == 'n') {$iconDefault .= $night;}
        if ($yourIconDefault) 
             {  $string = $iconsDef.$iconDefault.$iconsDefExt; }
        else {  $string = $iconsFctOrg.$iconYrno.$iconsFctOrgExt;}
        $correctLine['symbol_lnk']   = $string;
        $correctLine['symbol_url']   =
'<img class="ws_icon" src="'.$string.'" alt="'.$textClouds.' '.$textPrecip.'"/>';
	$correctLine['iconNr']       = $iconString;  
	$correctLine['night']        = $night; 
	$correctLine['condCloud']    = $clouds;
	$correctLine['condRain']	= $precip;
# CHECK SOILTEMPS
	if (isset ($correctLine['tempSoil3']) )	
	     {	if (!isset ($correctLine['tempSoil2']) )	
		     {  $correctLine['tempSoil2']    = $correctLine['tempSoil3'];
			$correctLine['moist2']       = $correctLine['moist3'];}  }
	if (isset ($correctLine['thunder']) ) 
	     {  $correctLine['thunder']              = $correctLine['thunder'] * 20;
		if ($correctLine['thunder'] > 80)    { $correctLine['thunder'] = 80;}   }	
# GENERATE UV PICTURE
        if (isset ($correctLine['UV']) ) 
             {  $uv = substr('0'.round ($correctLine['UV']),-2); 
                if ($uv == 0)
                     {  $correctLine['uvImg']   = 
                        $correctLine['uvColor'] =
                        $correctLine['uvText']  =''; }
                else {  $uv = 1*$uv;
                        if ($uv > 11) {$uv = 11;}
                        $new_uv = substr('0'.$uv,-2); 
                        $correctLine['uvImg']=
'<img class="ws_uv_sml" style="margin-bottom: 4px; " src="'.$ws_img_dir.'uv'.$new_uv.'.gif" alt="uv" />';
                        $correctLine['uvColor'] = $ws_wxsim_UV_colors[$uv];
                        $correctLine['uvText']  = $ws_wxsim_UV_texts[$uv];} }
#-----------------------------------------------------------------------
# 	adapt uom input to default uom of website
#-----------------------------------------------------------------------
        $arrayUnits     = array ('temp','rain','wind','baro','snow','dist','dir','%');
        foreach ($fieldsUsed as $name => $arr)
            {   $unit = $arr['unit'];
                if (!in_array($unit, $arrayUnits) ) {continue;}
                $value  = $correctLine[$name];
                switch ($unit) {
                    case 'temp':    # echo 'value ='.$value;
                        $value = 
                        $correctLine[$name.'_clc'] = ws_cvt_temp($value,$fromTemp);
                        $value = 
                        $correctLine[$name]        = ws_num_format($value,'temp');
                        if ($name == 'temp')
                             {  $correctLine[$name.'_clr'] = ws_temp_colored($value);}
                        break;
                    case 'rain':     
                        $value = 
                        $correctLine[$name.'_clc'] = ws_cvt_rain($value,$fromRain); 
                        $correctLine[$name]        = ws_num_format($value,'rain',$wsRainDecimals+1);
                        break;
                    case 'wind':     
                        $value = 
                        $correctLine[$name.'_clc'] = ws_cvt_wind($value,$fromWind); 
                        $correctLine[$name]        = ws_num_format($value,'wind');
                        break;
                    case 'baro': 
                        $value =
                        $correctLine[$name.'_clc'] = ws_cvt_baro($value,$fromBaro);    
                        $correctLine[$name]        = ws_num_format($value,'baro');
                        break;
                    case 'snow':
                        $old    = $value;
                        $value =
                        $correctLine[$name.'_clc'] = ws_cvt_rain($value,$fromSnow); 
        #ws_message ('<!-- module '.basename(__FILE__).' ('.__LINE__.'): For snow '.$old.' result = '.$correctLine[$name.'_clc'].' -->');
                        $correctLine[$name]        = ws_num_format($value,'snow');
                        break;
                    case 'dist':
                        $value =
                        $correctLine[$name.'_clc'] = ws_cvt_dist($value,$fromDist);    
                        $correctLine[$name]        = ws_num_format($value,'dist');
                        break;
                    case '%':
                        $correctLine[$name]        = round($value);
                        break;
                    case 'dir':
                        if (!isset ($ws_wind_deg) ) 
                            {   if (is_numeric($value)) {$ws_wind_deg = true;} else {$ws_wind_deg = false;} }
                        if (!$ws_wind_deg) {
                                $correctLine['windDir']    = trim($value);      // winddir in letters 
                                $correctLine[$name]        = ws_clc_degrees($value);} 
                        else {  $correctLine[$name]        = round($value);     // windDeg
                                $correctLine['windDir']    = 
                                $value                     = ws_clc_compass($value);}
                        $correctLine['windDirImg'] = '<img src='.$iconsWind.$value.$iconsWindExt.' alt="'.$value.'"/>';;
                    default; break;
                } // eo switch
        } // eo for each unit      
# GENERATE FEELSLIKE 
        list ($feels,$html) = ws_feels_like ($correctLine['temp_clc'],$correctLine['chill_clc'],$correctLine['heat_clc'],$toTemp) ;
        $correctLine['feelsLike']    = $feels;
        $correctLine['feelsHTML']    = $html; 
        $value                     =
        $correctLine['windBft']    = ws_beaufort_number ($correctLine['windSpeed_clc'],$toWind);
        list ($windBftTxt, $windBftLvl, $windBftBgc, $windBftTxc) 
                                   = ws_beaufort_colors($value);
        $correctLine['windBftTxt'] = $windBftTxt;
        $correctLine['windBftLvl'] = $windBftLvl;
        $correctLine['windBftBgc'] = $windBftBgc;
        $correctLine['windBftTxc'] = $windBftTxc;                        
# STORE correct forecast line in detail array
        ksort ($correctLine);
	$ws_wxsim_arr['details'][$linesOK] = $correctLine;
	$linesOK++;
#echo '<pre> details '.__LINE__.' '.print_r($ws_wxsim_arr['details'][$linesOK-1],true); 
/*####
#---------------------------------------------------------------
# 6 hour table
#---------------------------------------------------------------
        $toDay          = date ('Ymd',$correctLine['intDate']);
        $thisLineHour	= date ('H',  $correctLine['intDate']);
        $thisHourStart	= date ('H',  $correctLine['intDate']-3600);
#
        if ($thisLineHour == 7 || $thisLineHour == 19 )  // copy 6 hour to 12 hour
             {  }
        if (1 == $thisLineHour % 6 || $ws_parts_start == true)   # new 6 hour record
             {  $ws_parts_start         = false;
                $part                   = array();
                $part['part_start']     = $thisHourStart;
                $part['part_end']       = $thisLineHour;
                $part['dayName']        = date('l',($correctLine['intDate'] - 2*3600));
                $part['dayPart']        = '';
                $part['nr_hours']       = 1;
                $part['int_start']      = $correctLine['intDate'];
                $part['int_end']        = $correctLine['intDate'];
                $part['rain']           = $correctLine['rain_clc'];             // sum
                $part['snow']           = $correctLine['snow_clc'];             // sum
                $part['tempMin']        = $correctLine['tempMin_clc'];
                $part['tempMax']        = $correctLine['tempMax_clc'];
                $part['minWind']        = $correctLine['windSpeed_clc'];
                $part['maxWind']        = $correctLine['windSpeed_clc'];
                $part['gust']           = $correctLine['gust_clc'];             // max
                $part['hum']            = $correctLine['hum'];           // average
                $part['baro']           = $correctLine['baro_clc'];      // average
                $part['UV']             = $correctLine['UV'];            // max  
                $part['uvImg']          = $correctLine['uvImg']; 
                $part['uvColor']        = $correctLine['uvColor'];
                $part['uvText']         = $correctLine['uvText'];
                $part['solar']          = $correctLine['solar'];
                $part['skyCover']       = $correctLine['skyCover'];             // average?
                $part['condCloud']      = $correctLine['condCloud'];            // max?
                $part['condRain']       = $correctLine['condRain'];             // max?
                if ($wxsim_soil_wanted) {         
                        $part['tempGrass']      = $correctLine['tempGrass_clc'];
                        $part['tempSurf']       = $correctLine['tempSurf_clc'];
                        $part['tempSoil1']      = $correctLine['tempSoil1_clc'];
                        $part['tempSoil2']      = $correctLine['tempSoil2_clc'];
                        $part['tempSoil3']      = $correctLine['tempSoil3_clc'];
                        $part['tempSoil4']      = $correctLine['tempSoil4_clc']; 
                        $part['moist1']         = $correctLine['moist1'];
                        $part['moist2']         = $correctLine['moist2'];;
                        $part['moist3']         = $correctLine['moist3'];
                        $part['moist4']         = $correctLine['moist4'];
                        $part['evotrans_T']	= $correctLine['evotrans_T_clc'];
                }
                $part['icons']          = array();
                $nr                     = $correctLine['iconNr'];
                $part['icons'][$nr]     = $correctLine['symbol_url'];
                $part['wdirs']          = array();
                $key                    = $correctLine['windDir']; 
                $part['wdirs'][$key]    = $correctLine['windDirImg'];       
                $part['cnd1s']          = array();
                $key = strtolower(trim(str_replace($unusedClouds,'',$correctLine['cnd1'])));
                $part['cnd1s'][$key]    = $correctLine['cnd1']; 
                $part['cnd2s']          = array();
                $key = strtolower(trim(str_replace($unusedPrecip,'',$correctLine['cnd2'])));
                if ($key <> '') 
                     {  $part['cnd2s'][$key]    = $correctLine['cnd2'];}
#
#echo '<pre>'.__LINE__.' key=>'.$key.'<  part'.print_r($part,true).PHP_EOL;
#echo '<pre>'.__LINE__.' correctline '.print_r($correctLine,true);           
        }
        else {    
        #---------------------------------------------------------------
        # add / modify values for 6 hour period
        #        $ws_parts_arr[$ws_n_parts]
                $part['part_end']       = $thisLineHour;
                $part['int_end']        = $correctLine['intDate'];
                $part['nr_hours']++;
                $part['rain']           += $correctLine['rain_clc'];
                $part['snow']           += $correctLine['snow_clc'];
                $part['hum']            += $correctLine['hum'];
                $part['baro']           += $correctLine['baro_clc'];
                $part['skyCover']       += $correctLine['skyCover'];
                if ($wxsim_soil_wanted) {  
                        $part['tempGrass']      += $correctLine['tempGrass_clc'];
                        $part['tempSurf']       += $correctLine['tempSurf_clc'];
                        $part['tempSoil1']      += $correctLine['tempSoil1_clc'];
                        $part['tempSoil2']      += $correctLine['tempSoil2_clc'];
                        $part['tempSoil3']      += $correctLine['tempSoil3_clc'];
                        $part['tempSoil4']      += $correctLine['tempSoil4_clc'];
                        $part['moist1']         += $correctLine['moist1'];
                        $part['moist2']         += $correctLine['moist2'];
                        $part['moist3']         += $correctLine['moist3'];
                        $part['moist4']         += $correctLine['moist4'];
                        $part['evotrans_T']	+= $correctLine['evotrans_T_clc'];
                }
                if ($correctLine['condCloud'] > $part['condCloud']) 
                     {  $part['condCloud']      = $correctLine['condCloud'];}
                if ($correctLine['condRain'] >  $part['condRain']) 
                     {  $part['condRain']       = $correctLine['condRain'];}
                if ($correctLine['tempMin_clc'] <  $part['tempMin'])
                     {  $part['tempMin']        = $correctLine['temp_clc'];}
                if ($correctLine['tempMax_clc'] >  $part['tempMax'])
                     {  $part['tempMax']        = $correctLine['tempMax_clc'];}
                if ($correctLine['windSpeed_clc'] < $part['minWind'])
                     {  $part['minWind']        = $correctLine['windSpeed_clc'];}
                if ($correctLine['windSpeed_clc'] > $part['maxWind'])
                     {  $part['maxWind']        = $correctLine['windSpeed_clc'];}
                if ($correctLine['gust_clc'] >  $part['gust'])
                     {  $part['gust']           = $correctLine['gust_clc'];}
                if ($correctLine['UV'] >        $part['UV'])
                     {  $part['UV']             = $correctLine['UV'];
                        $part['uvImg']          = $correctLine['uvImg'];
                        $part['uvColor']        = $correctLine['uvColor'];
                        $part['uvText']         = $correctLine['uvText'];}
                if ($correctLine['solar'] >     $part['solar'])
                     {  $part['solar']          = $correctLine['solar'];}
                $nr                     = $correctLine['iconNr'];
                $part['icons'][$nr]     = $correctLine['symbol_url'];
                $key                    = $correctLine['windDir'];
                $part['wdirs'][$key]    = $correctLine['windDirImg'];
                $key = strtolower(trim(str_replace($unusedClouds,'',$correctLine['cnd1'])));
                $part['cnd1s'][$key]    = $correctLine['cnd1']; 
                $key = strtolower(trim(str_replace($unusedPrecip,'',$correctLine['cnd2'])));
                if ($key <> '') 
                     {  $part['cnd2s'][$key]    = $correctLine['cnd2'];}
#echo '<pre>'.__LINE__.' key='.$key.'  part'.print_r($part,true).PHP_EOL;
#echo '<pre>'.__LINE__.' correctline '.print_r($correctLine,true).PHP_EOL;            
        } // eo processing 6 hour table    
        if (0 == $thisLineHour % 6 && $ws_parts_start == false) # save 6 hours in array
             {  $nr                     = floor ($thisLineHour / 6);
                $part['dayPart']        = $dayParts[$nr];
                $nr                     = $part['nr_hours'];    # calculate averages 
                $part['hum']            = round ($part['hum'] / $nr);
                $part['baro']           = round ($part['baro'] / $nr);
                $part['skyCover']       = round ($part['skyCover'] / $nr);
                if ($wxsim_soil_wanted) {  
                        $part['tempGrass']      = round ($part['tempGrass'] / $nr);
                        $part['tempSurf']       = round ($part['tempSurf'] / $nr);
                        $part['tempSoil1']      = round ($part['tempSoil1'] / $nr);
                        $part['tempSoil2']      = round ($part['tempSoil2'] / $nr);
                        $part['tempSoil3']      = round ($part['tempSoil3'] / $nr);
                        $part['tempSoil4']      = round ($part['tempSoil4'] / $nr);
                        $part['moist1']         = round ($part['moist1'] / $nr);
                        $part['moist2']         = round ($part['moist2'] / $nr);
                        $part['moist3']         = round ($part['moist3'] / $nr);
                        $part['moist4']         = round ($part['moist4'] / $nr);
                        $part['evotrans_T']	= round (($part['evotrans_T']/ $nr),4);
                }                
                $ws_wxsim_arr['parts'][$partsOK] = $part;
                $partsOK++;}  
*/ ####
#echo __LINE__.' exit'; exit;
} // eo for each data line
/* ####
if (0 <> $thisLineHour % 6) {    // last daypart has to be saved also
                $nr                     = floor ( ($thisLineHour+5) / 6);
                $part['dayPart']        = $dayParts[$nr];
                $nr                     = $part['nr_hours'];    # calculate averages 
                $part['hum']            = round ($part['hum'] / $nr);
                $part['baro']           = round ($part['baro'] / $nr);
                $part['skyCover']       = round ($part['skyCover'] / $nr);
                if ($wxsim_soil_wanted) {  
                        $part['tempGrass']      = round ($part['tempGrass'] / $nr);
                        $part['tempSurf']       = round ($part['tempSurf'] / $nr);
                        $part['tempSoil1']      = round ($part['tempSoil1'] / $nr);
                        $part['tempSoil2']      = round ($part['tempSoil2'] / $nr);
                        $part['tempSoil3']      = round ($part['tempSoil3'] / $nr);
                        $part['moist1']         = round ($part['moist1'] / $nr);
                        $part['moist2']         = round ($part['moist2'] / $nr);
                        $part['moist3']         = round ($part['moist3'] / $nr); 
                }               
                $ws_wxsim_arr['parts'][$partsOK] = $part;}
*/ ####
if (isset ($snowFound) ) {
        $ws_wxsim_arr['details'][0]['snowFound'] = 1;}  
#
#echo '<pre>'.__LINE__.' ws_wxsim_arr='.print_r($ws_wxsim_arr,true); exit;
ws_message ( ws_debug_times('module '.basename(__FILE__).' ('.__LINE__.'):') );

#echo '<!-- '.print_r($ws_wxsim_arr['details'][0],true).print_r($ws_wxsim_arr['details'][2],true).' -->'.PHP_EOL; 
#echo '<!-- '.print_r($ws_wxsim_arr['parts'][0],true).print_r($ws_wxsim_arr['parts'][1],true).' -->'.PHP_EOL; 
#exit;
ws_save_cache($ws_cached_file, $ws_wxsim_arr);
ws_message ( '<!-- module '.basename(__FILE__).' ('.__LINE__.'): eof-'.$filetype.' => '.$i.' lines read, saved lines to cache: details '.count($ws_wxsim_arr['details']).' -->'); ####.' parts '.count($ws_wxsim_arr['parts']).' -->');
return;

function load_D_arrays () {
        global $ws_wxsim_flds, $ws_wxsim_cnds, $ws_wxsim_lookup, $ws_wxsim_UV_colors, $ws_wxsim_UV_texts; 
$ws_wxsim_flds['temp'] 	        = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'AIR',	'nameCsv' => 'Temperature'	);
$ws_wxsim_flds['hum'] 		= array ('loc' => 0, 'unit' => '%',	'nameTxt' => '%RH',	'nameCsv' => 'Rel.Hum.'		);
$ws_wxsim_flds['tempMax'] 	= array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TMAX',	'nameCsv' => 'Hi Temp'		);
$ws_wxsim_flds['tempMin'] 	= array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TMIN',	'nameCsv' => 'Low Temp'		);
$ws_wxsim_flds['dew'] 		= array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'DEW',	'nameCsv' => 'Dew Pt.'		);
$ws_wxsim_flds['chill'] 	= array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'WCF',	'nameCsv' => 'Wind Chl'		);
$ws_wxsim_flds['heat'] 	        = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'HT.I',	'nameCsv' => 'Heat Ind'		);
$ws_wxsim_flds['windSpeed']     = array ('loc' => 0, 'unit' => 'wind',	'nameTxt' => 'W.SP',	'nameCsv' => 'Wind Spd.'	);
$ws_wxsim_flds['gust']		= array ('loc' => 0, 'unit' => 'wind',	'nameTxt' => 'G10M',	'nameCsv' => '10 min Gust'	);
$ws_wxsim_flds['windDeg'] 	= array ('loc' => 0, 'unit' => 'dir',	'nameTxt' => 'W.DIR',	'nameCsv' => 'Wind Dir.'	);
$ws_wxsim_flds['baro'] 	        = array ('loc' => 0, 'unit' => 'baro',	'nameTxt' => 'SLP',	'nameCsv' => 'S.L.P.'		);
$ws_wxsim_flds['rain'] 	        = array ('loc' => 0, 'unit' => 'rain',	'nameTxt' => 'PTOT',	'nameCsv' => 'Tot.Prcp'		);
$ws_wxsim_flds['snow'] 	        = array ('loc' => 0, 'unit' => 'snow',	'nameTxt' => 'SN.C',	'nameCsv' => 'Snow Dpth'	);
$ws_wxsim_flds['visib'] 	= array ('loc' => 0, 'unit' => 'dist',	'nameTxt' => 'VIS',	'nameCsv' => 'VIS'		);
$ws_wxsim_flds['thunder'] 	= array ('loc' => 0, 'unit' => '%',	'nameTxt' => 'SWXO',	'nameCsv' => 'Severe index'	);
$ws_wxsim_flds['UV'] 		= array ('loc' => 0, 'unit' => 'index',	'nameTxt' => 'UVI',	'nameCsv' => 'UV Index'		);
$ws_wxsim_flds['solar'] 	= array ('loc' => 0, 'unit' => 'W/m^2',	'nameTxt' => 'S.IR',	'nameCsv' => 'Solar Rad'	);
$ws_wxsim_flds['tempGrass']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'GRS',	'nameCsv' => 'Grass Temperature');
$ws_wxsim_flds['tempSurf']	= array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'SURF',	'nameCsv' => 'Soil Surface Temperature'	);
$ws_wxsim_flds['tempSoil1']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TSO1',	'nameCsv' => 'Soil Temperature Depth 1'	);
$ws_wxsim_flds['tempSoil2']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TSO2',	'nameCsv' => 'Soil Temperature Depth 2'	);
$ws_wxsim_flds['tempSoil3']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TSO3',	'nameCsv' => 'Soil Temperature Depth 3'	);
$ws_wxsim_flds['tempSoil4']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TSO4',	'nameCsv' => 'Soil Temperature Depth 4'	);
$ws_wxsim_flds['tempSoil5']     = array ('loc' => 0, 'unit' => 'temp',	'nameTxt' => 'TSO5',	'nameCsv' => 'Soil Temperature Depth 5'	);
$ws_wxsim_flds['moist1']	= array ('loc' => 0, 'unit' => 'cb',  	'nameTxt' => 'SMT1',	'nameCsv' => 'Soil Tension Depth 1'	);
$ws_wxsim_flds['moist2']	= array ('loc' => 0, 'unit' => 'cb',	'nameTxt' => 'SMT2',	'nameCsv' => 'Soil Tension Depth 2'	);
$ws_wxsim_flds['moist3']	= array ('loc' => 0, 'unit' => 'cb',	'nameTxt' => 'SMT3',	'nameCsv' => 'Soil Tension Depth 3'	);
$ws_wxsim_flds['moist4']	= array ('loc' => 0, 'unit' => 'cb',	'nameTxt' => 'SMT4',	'nameCsv' => 'Soil Tension Depth 4'	);
$ws_wxsim_flds['moist5']	= array ('loc' => 0, 'unit' => 'cb',	'nameTxt' => 'SMT5',	'nameCsv' => 'Soil Tension Depth 5'	);
$ws_wxsim_flds['skyCover']	= array ('loc' => 0, 'unit' => '%',	'nameTxt' => 'L.CD',	'nameCsv' => 'Sky Cov'		);
$ws_wxsim_flds['skyCover2']     = array ('loc' => 0, 'unit' => '%',	'nameTxt' => 'SKY',	'nameCsv' => 'Lower cloud cover');
$ws_wxsim_flds['cnd1'] 	        = array ('loc' => 0, 'unit' => 'txt',	'nameTxt' => 'xxx',	'nameCsv' => 'WX Type 1'	);
$ws_wxsim_flds['cnd2'] 	        = array ('loc' => 0, 'unit' => 'txt',	'nameTxt' => 'xxx',	'nameCsv' => 'WX Type 2'	);
$ws_wxsim_flds['evotrans_T']	= array ('loc' => 0, 'unit' => 'rain',	'nameTxt' => 'ETAT',	'nameCsv' => 'Total Actual Evapotranspiration', 'explain' => 'Evapotranspiration Actual Total'); 

#$ws_wxsim_flds['temp15M'] 	= array ('loc' => 0, 'unit' => 0,	'nameTxt' => '15M',	'nameCsv' => '15 m'			);  // removed (50 ft) Temperature
#$ws_wxsim_flds['t850'] 	= array ('loc' => 0, 'unit' => 0,	'nameTxt' => '850T',	'nameCsv' => 'T_850 mb'		);
#$ws_wxsim_flds['wBulb'] 	= array ('loc' => 0, 'unit' => 0,	'nameTxt' => 'WET',	'nameCsv' => 'Wet Bulb'		);
#$ws_wxsim_flds['gust1Hr']	= array ('loc' => 0, 'unit' => 2,	'nameTxt' => 'G1HR',	'nameCsv' => '1 hr Gust'	);
#$ws_wxsim_flds['tscd'] 	= array ('loc' => 0, 'unit' => 'index',	'nameTxt' => 'TSCD',	'nameCsv' => 'Convection index'		);
#$ws_wxsim_flds['level'] 	= array ('loc' => 0, 'unit' => 0,	'nameTxt' => 'LVL1',	'nameCsv' => 'T_Lvl 1'		);
#$ws_wxsim_flds['vst'] 		= array ('loc' => 0, 'unit' => '?',	'nameTxt' => 'VST',	'nameCsv' => 'Vis Trans'	);
#$ws_wxsim_flds['thk'] 		= array ('loc' => 0, 'unit' => '?',	'nameTxt' => 'THK',	'nameCsv' => '10-5 Thk'		);
#$ws_wxsim_flds['tsmo']         = array ('loc' => 0, 'unit' => '?',	'nameTxt' => 'TSMO',	'nameCsv' => 'Convection index'	);
#$ws_wxsim_flds['irt'] 		= array ('loc' => 0, 'unit' => '?',	'nameTxt' => 'I.RT',	'nameCsv' => 'xxxxxxxx'		);
#$ws_wxsim_flds['spress'] 	= array ('loc' => 0, 'unit' => 'baro',	'nameTxt' => 'xxx',	'nameCsv' => 'Stn.Pres.'	);
#Total Actual Evapotranspiration
#		 cloud coverage
$ws_wxsim_cnds['CLEAR']		=
$ws_wxsim_cnds['SUNNY']		= array ('code' => 0, 'cond' => 'sky',  'text' =>'clear', 	        'out' =>'Clear');			
$ws_wxsim_cnds['HAZY SUN']      = array ('code' => 0, 'cond' => 'sky',  'text' =>'sunny with haze', 	'out' =>'Sunny with haze');
# minimum	code 100	= FEW
$ws_wxsim_cnds['M.SUNNY']	= 
$ws_wxsim_cnds['CLR-FAIR']	= 
$ws_wxsim_cnds['FAIR']		= 
$ws_wxsim_cnds['M.CLEAR']	= array ('code' => 100, 'cond' => 'sky','text' =>'clear mostly', 	'out' =>'Mostly clear');
# 		code 200	= SCT
$ws_wxsim_cnds['P.CLOUDY']	= 
$ws_wxsim_cnds['FAIR-P.C']	= 
$ws_wxsim_cnds['FAIR-P.C.']	= array ('code' => 200, 'cond' => 'sky','text' =>'cloudy partly',      'out' =>'Partly cloudy');
# 		code 300	= BKN
$ws_wxsim_cnds['M.CLOUDY']	=
$ws_wxsim_cnds['P.-M.CLD']	= 
$ws_wxsim_cnds['P.-M.CLDY']	= array ('code' => 300, 'cond' => 'sky','text' =>'cloudy mostly', 	'out' =>'Mostly cloudy');
# maximum 	code 400	= OVC VV
$ws_wxsim_cnds['CLOUDY'] 	=
$ws_wxsim_cnds['M.C.-CLD']	= 
$ws_wxsim_cnds['M.C.-CLDY']	= array ('code' => 400, 'cond' => 'sky','text' =>'cloudy', 		'out' =>'Cloudy');
$ws_wxsim_cnds['DNS.OVCS']	= 
$ws_wxsim_cnds['DNS.OVCST']	= array ('code' => 400, 'cond' => 'sky','text' =>'overcast', 		'out' =>'Overcast');
// the following descriptions  are standardized
// 'Fair';
// 'Fair to partly cloudy';
// 'Mostly clear to cloudy';     ####
// 'Mostly clear';		####
// 'Clear to fair';
// 'Partly to mostly cloudy';
// 'Sunny';
##       weather condition
# rain 			code +10	= DZ RA
# light rain	code + 0
$ws_wxsim_cnds['CHNC. DRZ']     = 
$ws_wxsim_cnds['CHNC. DRZL']    = array ('code' => 10, 'cond' => 'drizzle','text' =>'drizzle chance of ',  'out' =>'Chance drizzle');
$ws_wxsim_cnds['CHNC. SHW']     = 
$ws_wxsim_cnds['CHNC. SHWR']    = array ('code' => 10, 'cond' => 'rain',   'text' =>'showers chance of ',  'out' =>'Chance Rain Showers');
$ws_wxsim_cnds['DRIZZLE']       = array ('code' => 10, 'cond' => 'rain',   'text' =>'drizzle', 	        'out' =>'Drizzle');
$ws_wxsim_cnds['LIGHT RAI']     = 
$ws_wxsim_cnds['LIGHT RAIN']    = array ('code' => 10, 'cond' => 'rain',   'text' =>'rain light ',      'out' =>'Light rain');
$ws_wxsim_cnds['PROB. DRZ']     = 
$ws_wxsim_cnds['PROB. DRZL']    = array ('code' => 10, 'cond' => 'drizzle','text' =>'drizzle probably', 'out' =>'Probably drizzle');
$ws_wxsim_cnds['PROB. SHW']     = 
$ws_wxsim_cnds['PROB. SHWR']    = array ('code' => 10, 'cond' => 'rain',   'text' =>'showers probably', 'out' =>'Probably showers');
# moderate rain	code +  1
$ws_wxsim_cnds['MOD. RAIN']	= array ('code' => 11, 'cond' => 'rain',   'text' =>'rain moderate', 	'out' =>'Moderate rain');
# heavy rain	code +  2
$ws_wxsim_cnds['HEAVY RAI']     = 
$ws_wxsim_cnds['HEAVY RAIN']    = array ('code' => 12, 'cond' => 'rain',   'text' =>'rain heavy', 	'out' =>'Heavy rain');
# snow	code + 20	= SN  SG  # light snow	code +  0
$ws_wxsim_cnds['CH. RN/SN']	= 
$ws_wxsim_cnds['CH. RN/SNW']    = array ('code' => 20, 'cond' => 'rain',   'text' =>'rain and or snow chance of',  'out' =>'Chance of snow and or rain');
$ws_wxsim_cnds['CH. SNW/R']     = 
$ws_wxsim_cnds['CH. SNW/RN']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow and or rain chance of ', 'out' =>'Chance of snow and or rain'); ####
$ws_wxsim_cnds['CHNC. SNO']     = 
$ws_wxsim_cnds['CHNC. SNOW']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow chance of ',  'out' =>'Chance of snow');
$ws_wxsim_cnds['LIGHT SNO']     = 
$ws_wxsim_cnds['LIGHT SNOW']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow light ',      'out' =>'Light snow');
$ws_wxsim_cnds['LT. RN/SN']     = 
$ws_wxsim_cnds['LT. RN/SNW']    = array ('code' => 20, 'cond' => 'rain',   'text' =>'rain and or snow light ', 	'out' =>'Light rain and or snow');
$ws_wxsim_cnds['LT. SNW/R']     = 
$ws_wxsim_cnds['LT. SNW/RN']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow and or rain light ', 	'out' =>'Light rain and or snow' );
$ws_wxsim_cnds['PR. RN/SN']     = 
$ws_wxsim_cnds['PR. RN/SNW']    = array ('code' => 20, 'cond' => 'rain',   'text' =>'rain and or snow probably','out' =>'Probably rain and or snow');
$ws_wxsim_cnds['PR. SNW/R']     = 
$ws_wxsim_cnds['PR. SNW/RN']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow and or rain probably','out' =>'Probably rain and or snow');
$ws_wxsim_cnds['PROB.SNOW']     = 
$ws_wxsim_cnds['PROB. SNO']     = 
$ws_wxsim_cnds['PROB. SNOW']    = array ('code' => 20, 'cond' => 'snow',   'text' =>'snow probably', 		'out' =>'Probably snow');
# moderate snow	code +  1
$ws_wxsim_cnds['MOD. SNOW']	= array ('code' => 21, 'cond' => 'snow',   'text' =>'snow moderate',		'out' =>'Moderate snow');
$ws_wxsim_cnds['SN. FLURR']     = 
$ws_wxsim_cnds['SN. FLURRY']    = array ('code' => 21, 'cond' => 'snow',   'text' =>'snow flurries', 		'out' =>'Snow flurries');
$ws_wxsim_cnds['RAIN/SNOW']     = array ('code' => 21, 'cond' => 'rain',   'text' =>'rain and or snow', 	'out' =>'Snow and or rain');
$ws_wxsim_cnds['SNOW/RAIN']     = array ('code' => 21, 'cond' => 'snow',   'text' =>'snow and or rain', 	'out' =>'Snow and or rain');
# heavy snow	code +  2
$ws_wxsim_cnds['HEAVY SNO']     = 
$ws_wxsim_cnds['HEAVY SNOW']    = array ('code' => 22, 'cond' => 'snow',   'text' =>'snow heavy', 		'out' =>'Heavy snow');
$ws_wxsim_cnds['HVY.SNW/R']     = 
$ws_wxsim_cnds['HVY.SNW/RN']    = array ('code' => 22, 'cond' => 'snow',   'text' =>'snow and or rain  heavy', 	'out' =>'Heavy snow and or rain');
$ws_wxsim_cnds['HVY.RN/SN']     = 
$ws_wxsim_cnds['HVY.RN/SNW']    = array ('code' => 22, 'cond' => 'rain',   'text' =>'rain and or snow  heavy', 'out' =>'Heavy snow and or rain');
# winter conditions	code + 30 = IC  PE  GR  GS  # light winter 		code +  0
$ws_wxsim_cnds['CH. FRZ.R']     = 
$ws_wxsim_cnds['CH. FRZ.RN']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'freezing rain chance of', 'out' =>'Chance of freezing rain');
$ws_wxsim_cnds['CH. RN/SL']	= 
$ws_wxsim_cnds['CH. RN/SLT']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'rain and or sleet chance of', 'out' =>'Chance of rain and or sleet');
$ws_wxsim_cnds['CH. SLT/F']     = 
$ws_wxsim_cnds['CH. SLT/FR']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or freezing rain chance of','out' =>'Chance of sleet and or freezing rain');
$ws_wxsim_cnds['CH.SLT/MI']     = 
$ws_wxsim_cnds['CH.SLT/MIX']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or mix chance of',  'out' =>'Chance of sleet and or mix');
$ws_wxsim_cnds['CH.SLT/SN']     = 
$ws_wxsim_cnds['CH.SLT/SNW']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or snow chance of', 'out' =>'Chance of sleet and or snow');
$ws_wxsim_cnds['CHNC.SLEE']     = 
$ws_wxsim_cnds['CHNC.SLEET']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet chance of',		'out' =>'Chance of sleet');
$ws_wxsim_cnds['FRZ. DRZL']     = array ('code' => 30, 'cond' => 'icy',    'text' =>'freezing drizzle',	        'out' =>'Freezing drizzle');
$ws_wxsim_cnds['FRZ. RAIN']     = array ('code' => 30, 'cond' => 'icy',    'text' =>'freezing rain',	        'out' =>'Freezing rain');
$ws_wxsim_cnds['ICE STORM']     = array ('code' => 30, 'cond' => 'icy',    'text' =>'ice storm',		'out' =>'Ice storm');
$ws_wxsim_cnds['LT. SLEET']     = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet light',	        'out' =>'Light sleet');
$ws_wxsim_cnds['LT.SLT/MI']     = 
$ws_wxsim_cnds['LT.SLT/MIX']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or mixture light','out' =>'Light sleet and or snow');
$ws_wxsim_cnds['LT.SLT/SN']     = 
$ws_wxsim_cnds['LT.SLT/SNW']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or snow light',  'out' =>'Light sleet and or snow');
$ws_wxsim_cnds['PR. FRZ.R']     = 
$ws_wxsim_cnds['PR. FRZ.RN']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'freezing rain probably',   'out' =>'Probably freezing rain');
$ws_wxsim_cnds['PR. RN/SL']     = 
$ws_wxsim_cnds['PR. RN/SLT']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'rain and or sleet probably','out' =>'Probably rain and or snow');
$ws_wxsim_cnds['PR.SLT/MI']     = 
$ws_wxsim_cnds['PR.SLT/MIX']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or mixture probably','out' =>'Probably sleet and or snow');
$ws_wxsim_cnds['PR.SLT/SN']     = 
$ws_wxsim_cnds['PR.SLT/SNW']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or snow probably',  'out' =>'Probably sleet and or snow');
$ws_wxsim_cnds['PR. SLT/F']     = 
$ws_wxsim_cnds['PR. SLT/FR']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet and or freezing rain probably', 'out' =>'Probably sleet and or freezing rain');
$ws_wxsim_cnds['PROB.SLEE']     = 
$ws_wxsim_cnds['PROB.SLEET']    = array ('code' => 30, 'cond' => 'icy',    'text' =>'sleet probably',           'out' =>'Probably sleet');
$ws_wxsim_cnds['SN. FLURRY']    = array ('code' => 30, 'cond' => 'snow',   'text' =>'snow flurries', 	        'out' =>'Snow flurries');
# moderate 	winter	code +  1
$ws_wxsim_cnds['MOD. SLEE']     = 
$ws_wxsim_cnds['MOD. SLEET']    = array ('code' => 31, 'cond' => 'icy', 'text' =>'sleet moderate', 	'out' =>'Moderate sleet');
$ws_wxsim_cnds['MOD. SNOW']     = array ('code' => 31, 'cond' => 'snow','text' =>'snow moderate', 	'out' =>'Moderate snow ');
$ws_wxsim_cnds['RAIN/SNOW']	= array ('code' => 31, 'cond' => 'rain','text' =>'rain and or snow',    'out' =>'Snow and or rain');
$ws_wxsim_cnds['SNOW/RAIN']	= array ('code' => 31, 'cond' => 'icy', 'text' =>'snow and or rain',    'out' =>'Snow and or rain');
$ws_wxsim_cnds['RAIN/SLEE']     = 
$ws_wxsim_cnds['RAIN/SLEET']    = array ('code' => 31, 'cond' => 'icy', 'text' =>'rain and or sleet', 	'out' =>'Rain and or sleet');
$ws_wxsim_cnds['SLEET/MIX']     = array ('code' => 31, 'cond' => 'icy', 'text' =>'sleet and or mixture','out' =>'Sleet and or snow');
$ws_wxsim_cnds['SLEET/SNO']     = 
$ws_wxsim_cnds['SLEET/SNOW']    = array ('code' => 31, 'cond' => 'icy', 'text' =>'sleet and or snow',   'out' =>'Sleet and or snow');
$ws_wxsim_cnds['SLT/FRZ.R']     = 
$ws_wxsim_cnds['SLT/FRZ.RN']    = array ('code' => 31, 'cond' => 'icy', 'text' =>'sleet and or freezing rain', 'out' =>'Sleet and or freezing rain');
# extreme winter	code +  2
$ws_wxsim_cnds['BLIZZARD']      = array ('code' => 32, 'cond' => 'icy', 'text' =>'snow and blizzards',  'out' =>'Snow and blizzards');
$ws_wxsim_cnds['HVY. SLEE']     = 
$ws_wxsim_cnds['HVY. SLEET']    = array ('code' => 32, 'cond' => 'icy', 'text' =>'sleet heavy', 	'out' =>'Heavy sleet ');
$ws_wxsim_cnds['HVY.SLT/M']     = 
$ws_wxsim_cnds['HVY.SLT/MX']    = array ('code' => 32, 'cond' => 'icy', 'text' =>'sleet and or mix heavy', 'out' =>'Heavy sleet and or mix');
$ws_wxsim_cnds['HVY.SLT/S']     = 
$ws_wxsim_cnds['HVY.SLT/SN']    = array ('code' => 32, 'cond' => 'icy', 'text' =>'sleet and or mix heavy', 'out' =>'Heavy sleet and or mix');
# fog		code + 50		= BR  FG
# light		code +  0
$ws_wxsim_cnds['LIGHT FOG']     = array ('code' => 50, 'cond' => 'fog', 'text' =>'fog light', 	        'out' =>'Light fog');
# moderate	code +  1
$ws_wxsim_cnds['MOD. FOG']	= array ('code' => 51, 'cond' => 'fog', 'text' =>'fog moderate ', 	'out' =>'Moderate fog');
# extreme	code +  2
$ws_wxsim_cnds['DENSE FOG']     = array ('code' => 52, 'cond' => 'fog', 'text' =>'fog dense', 	        'out' =>'Dense fog');
# 		dew discarded in icon.  used for grass / soil	## less to high
$ws_wxsim_cnds['LIGHT DEW']     = array ('code' => -1, 'cond' => 'dew', 'text' =>'dew light ', 	        'out' =>'Light dew');
$ws_wxsim_cnds['MOD. DEW']	= array ('code' => -1, 'cond' => 'dew', 'text' =>'dew moderate', 	'out' =>'Moderate dew');
$ws_wxsim_cnds['HEAVY DEW']     = array ('code' => -1, 'cond' => 'dew', 'text' =>'dew heavy ', 	        'out' =>'Heavy dew');
# discarded or code extreme cold ? extra text   		## less to high
$ws_wxsim_cnds['SCTD.FROS']     = 
$ws_wxsim_cnds['SCTD.FROST']    = array ('code' => 0,   'cond' => 'frost','text' =>'frost scattered ', 	'out' =>'Scattered frost');
$ws_wxsim_cnds['LT. FROST']     = 
$ws_wxsim_cnds['LT. FROST']     = array ('code' => 0,   'cond' => 'frost','text' =>'frost light ', 	'out' =>'Light frost');
$ws_wxsim_cnds['MOD. FROS']	= 
$ws_wxsim_cnds['MOD. FROST']    = array ('code' => 0,   'cond' => 'frost','text' =>'frost moderate ', 	'out' =>'Moderate frost');
$ws_wxsim_cnds['HVY. FROS']     = 
$ws_wxsim_cnds['HVY. FROST']    = array ('code' => 0,   'cond' => 'frost','text' =>'frost heavy ', 	'out' =>'Heavy frost');
    
$ws_wxsim_lookup = array(		// calculated icon Default to WXSIM (yrno) icon
'000'  => '01',
'100'  => '02',	
'110'  => '05',	'111'  => '05',	'112'  => '05', '120'  => '08', '121'  => '08', '122'  => '13',
'130'  => '12', '131'  => '12', '132'  => '12', '140'  => '11',	'141'  => '11',	'142'  => '11',
'150'  => '15',	'151'  => '15',	'152'  => '15',
'200'  => '02', 
'210'  => '09', '211'  => '09', '212'  => '09', '220'  => '13',	'221'  => '13',	'222'  => '13',
'230'  => '12',	'231'  => '12',	'232'  => '12', '240'  => '11',	'241'  => '11',	'242'  => '11',
'250'  => '15',	'251'  => '15',	'252'  => '15',
'300'  => '03',
'310'  => '09', '311'  => '09', '312'  => '09', '320'  => '13',	'321'  => '13',	'322'  => '13',
'330'  => '12',	'331'  => '12',	'332'  => '12', '340'  => '11',	'341'  => '11',	'342'  => '11',
'350'  => '15',	'351'  => '15',	'352'  => '15',
'400'  => '04',
'410'  => '09', '411'  => '09', '412'  => '09', '420'  => '13',	'421'  => '13',	'422'  => '13',
'430'  => '12',	'431'  => '12',	'432'  => '12','440'  => '11',	'441'  => '11',	'442'  => '11',
'450'  => '15',	'451'  => '15',	'452'  => '15',

'600' => 'windy', '700' => 'cold',  '701' => 'hot', '800' => 'road' , '900' => 'extreme','901' => 'dunno'  );

$ws_wxsim_UV_colors        = array ( 
        0  => '#A4CE6A', 1 =>   '#A4CE6A', 2 =>  '#A4CE6A',  
        3  => '#FBEE09', 4 =>   '#FBEE09', 5 =>  '#FBEE09',
        6  => '#FD9125', 7 =>   '#FD9125', 
        8 =>  '#F63F37', 9  =>  '#F63F37', 10 => '#F63F37', 
        11 => '#807780');
$ws_wxsim_UV_texts        = array (
        0  => 'unknown',    1 => 'Low',         2 => 'Low',  
        3  => 'Medium',     4 => 'Medium',      5 => 'Medium',
        6  => 'High',       7 => 'High',        
        8  => 'Very high',  9  =>'Very high',  10 => 'Very high', 
        11 => 'Extreme'  );

} // eo load arrays