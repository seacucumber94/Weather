<?php $scrpt_vrsn_dt  = 'metar_load_funct.php|01|2023-02-15|';  # missing Z + php 8.1.0 time + dewpoint + clean + extra file checks | release 2012_lts
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
#
#  based on w e a t h e r 34 wxcheck API aviation metar script May 2018 
#
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -------- lang translations clouds
$lng_clr        = lang('Clear');
$lng_prtl_cld   = lang('Partly Cloudy');
$lng_mstl_sct   = lang('Mostly Scattered Clouds');
$lng_mstl_cld   = lang('Mostly Cloudy');
$lng_overcast   = lang('Overcast');
#
$metar_fl       = $fl_folder.$mtr_fl;   # in setting.php 'jsondata/metar34.txt'
#
if (!file_exists ($metar_fl) )
     {  echo '<p style="color: red;"><b>no valid METAR file found </b>'.$mtr_fl.'</p>';
        return false;}   
$json_string    = file_get_contents($fl_folder.$mtr_fl); 
if (substr ($json_string, 0,1) == '"') 
     {  $json_string = substr($json_string, 1);
        $json_string = substr($json_string, 0 , -1);
        $from   = array ('\"');
        $to     = array ('"');
        $json_string    = str_replace ($from, $to, $json_string);}
$parsed_json    = json_decode($json_string, true);
if ($parsed_json === NULL) 
     {  $from   = array ('\"');
        $to     = array ('"');
        $json_string    = str_replace ($from, $to, $json_string);
        $parsed_json    = json_decode($json_string, true);}
        
#echo '<pre>'.print_r($json_string,true).PHP_EOL.'json='.print_r($parsed_json,true); exit;
if ( !is_array($parsed_json)
  || !array_key_exists ('data',$parsed_json)
  || !array_key_exists (0,$parsed_json['data']) )
     {  echo '<p style="color: red;"><b>no valid data found in  METAR file </b>'.$mtr_fl.'</p>';
        return false;}   
$arr                    = $parsed_json['data'][0];
$str_date               = $arr['observed'];
if (substr($str_date,-1) <> 'Z') {$str_date .= 'Z';}    # 2023-03-15
$metar34time            = $str_date;                    # 2023-03-15
$forecastime            = strtotime($metar34time); 
if ($forecastime + 3600 < time())
	{ $online_txt   = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
else    { $online_txt   = '<b class="PWS_online"> ' .$online.set_my_time_lng($forecastime,true).' </b>';}   # 2022-03-28 php 8.1.0 
$metar34raw             = $arr['raw_text']; 
$metar34stationid       = $arr['icao'];	
if (isset ($arr['name']) ) {$metar34stationname     = $arr['name'];	}
elseif (isset ($arr['station']['name']) ) {$metar34stationname = $arr['station']['name'];}
else {$metar34stationname     = '';}
$metar34pressurehg      = $arr['barometer']['hg'];	
$metar34pressuremb      = $arr['barometer']['mb'];
#
# "conditions":[{"code":"RA","prefix":"-","text":"Light Rain"}
#              ,{"code":"BR","text":"Mist"}]
# use first prefix + condition-code for icon
$string         = 
$extra          =
$cnd_prfx       = 
$cnd_cd         = 
$cnd_shwr       =
$sky_icon       = 
$sky_desc       = 
$string2        = 
$metar34clouds  = 
$metar34cloudstext = '';
#
# save first condition if no text fields are found 
if (isset ($arr['conditions'][0]['code']) ) 
     {  $arr2   = $arr['conditions'][0];
        if (isset ($arr2['prefix']) ) 
             {  $cnd_prfx = $arr2['prefix'];
                if     ($cnd_prfx == '-') {$cnd_prfx = 'Light ';}
                elseif ($cnd_prfx == '+') {$cnd_prfx = 'Heavy ';}
                }
        $cnd_cd =  $arr2['code'];
        $cnd_cd =  str_replace ('SH','',$cnd_cd);
        if ($cnd_cd <> $arr2['code']) {$cnd_shwr = ' Showers';}
} // eo check first condition
#
# check all conditions if there are text-descriptions
if (array_key_exists('conditions', $arr))
      { foreach ($arr['conditions'] as $arr2)
             {  $string3= '';
                if (is_array($arr2) &&  array_key_exists('text',$arr2) )
                     {  $arr3   = explode (' ',$arr2['text']);
                        foreach ($arr3 as $txt)
                             {  $txt    = trim($txt);
                                if ($txt <> '') { $string3 .= ucfirst (lang (strtolower($txt))).' ';}
                        } // eo each word
                }  // eo each text
                if ($string3 <> '') { $string.= $extra.trim($string3); $extra = ', '; }       
        } // eo each condition
} // eo  conditions found 
#
$metar34conditionstext  = $string;
# 
# "clouds":[{"code":"FEW","text":"Few","base_feet_agl":4900,"base_meters_agl":1493.52}]
# use first cloud description, optional for icon
if (isset ($arr['clouds']) ) 
     {  $metar34clouds          = $arr['clouds'][0]['code'];
        $metar34cloudstext      = $arr['clouds'][0]['text'];}
#
$metar34temperaturec    = $arr['temperature']['celsius'];
$metar34temperaturef    = $arr['temperature']['fahrenheit'];
if (array_key_exists('dewpoint',$arr) )         #### 2021-10-06
     {  $metar34dewpointc       = $arr['dewpoint']['celsius'];
        $metar34dewpointf       = $arr['dewpoint']['fahrenheit'];}
else {  $metar34dewpointc       = $metar34temperaturec;
        $metar34dewpointf       = $metar34temperaturef;}  #### 2021-10-06
if (isset ($arr['humidity_percent'])) 
     {  $metar34humidity= $arr['humidity_percent'];}
elseif (isset ($arr['humidity']['percent']))
     {  $metar34humidity= $arr['humidity']['percent'];}
else {  $metar34humidity=  0;}
#
$metar34visibility      = $arr['visibility']['meters'];
#
if (!isset ($arr['wind']['speed_mph']))
     {  $metar34windir          = 0;
        $metar34windspeedmph    = 0;
        $metar34windspeedkmh    = 0;
        $metar34windspeedkts    = 0;
        $metar34windspeedmps    = 0;}
else {  $metar34windir          = $arr['wind']['degrees'];
        $metar34windspeedmph    = $arr['wind']['speed_mph'];
        $metar34windspeedkmh    = (float) number_format($metar34windspeedmph*1.60934,0);//kmh
        $metar34windspeedkts    = $arr['wind']['speed_kts'];
        $metar34windspeedmps    = (float) number_format($metar34windspeedmph*0.44704,0);} //mps
if (!isset ($arr['rain_in']) ) 
     {  $metar34raininches      = 0; 
        $metar34rainmm          = 0;}
else {  $metar34raininches      = (float) $arr['rain_in'];
        $metar34rainmm          = (float) number_format($metar34raininches*25.4,2) ;}
$metar34visibility      = (float) str_replace(',', '', $metar34visibility);
$metar34vismiles        = (float) number_format($metar34visibility*0.000621371,1) ;
$metar34viskm           = (float) number_format($metar34visibility*0.00099999969062399994,1) ;
#
# start the icon output and descriptions
#
if ($cnd_cd <> '')
     {  $mtr_icn       = array();
# rain
        $mtr_icn  ['DZ'  ] = 'mc_rain           |mc_rain_dark           |Drizzle        |';
        $mtr_icn  ['DZRA'] = 'ovc_rain          |ovc_rain_dark          |Drizzle Rain |';
        $mtr_icn  ['FZDZ'] = 'ovc_sleet         |ovc_sleet_dark         |Freezing Drizzle |';
        $mtr_icn  ['RA'  ] = 'mc_rain           |mc_rain_dark           |Rain |';
        $mtr_icn  ['FZRA'] = 'ovc_sleet         |ovc_sleet_dark         |Freezing Rain |';
        $mtr_icn  ['RASN'] = 'ovc_flurries      |ovc_flurries_dark      |Rain Snow |';
#
        $mtr_icn  ['SQ'  ] = 'ovc_windy         |ovc_windy              |Squall |';
# snow / hail a.s.o.
        $mtr_icn  ['SN'  ] = 'ovc_flurries      |ovc_flurries_dark      |Snow |';
        $mtr_icn  ['SG'  ] = 'ovc_flurries      |ovc_flurries_dark      |Snow |';
        $mtr_icn['SNINCR'] = 'ovc_flurries      |ovc_flurries_dark      |Snow |';
        $mtr_icn  ['IP'  ] = 'ovc_sleet         |ovc_sleet_dark         |Ice_Pellets |';
# haze
        $mtr_icn  ['HZ'  ] = 'haze_day          |mc_fog_dark            |Haze |';
# fog / mist
        $mtr_icn  ['FG'  ] =
        $mtr_icn  ['FZFG'] =
        $mtr_icn  ['NFG' ] =
        $mtr_icn  ['MIFG'] =
        $mtr_icn  ['VCFG'] =
        $mtr_icn  ['PRFG'] =
        $mtr_icn  ['BCFG'] = 'mc_fog            |mc_fog_dark            |Fog |';
        $mtr_icn  ['BR'  ] =
        $mtr_icn  ['NBR' ] = 'mc_fog            |mc_fog_dark            |Mist |';
# Hail and some
        $mtr_icn  ['PE'  ] =
        $mtr_icn  ['PL'  ] = 
        $mtr_icn  ['IC'  ] = 'ovc_sleet         |ovc_sleet_dark         |Ice_Crystals |';
        $mtr_icn  ['IS'  ] = 'ovc_sleet         |ovc_sleet_dark         |Ice_Crystals|';
        $mtr_icn  ['GR'  ] = 'ovc_sleet         |ovc_sleet_dark         |Hail|';
        $mtr_icn  ['GS'  ] = 'ovc_sleet         |ovc_sleet_dark         |Hail|';
# Thunderstorms

        $mtr_icn  ['LN'  ] = 'ovc_thun_dark     |ovc_thun_dark          |Lightning |';
        $mtr_icn  ['TS'  ] =
        $mtr_icn  ['VCTS'] = 'ovc_thun_dark     |ovc_thun_dark          |Thunderstorm |';
        $mtr_icn  ['TSRA'] =
        $mtr_icn  ['NTSRA']= 'ovc_thun_rain_dark|ovc_thun_rain_dark     |Thunderstorm Rain|';
        
# Dust and Sand
        $mtr_icn  ['BLDU'] = 'dust           |dust                |Blowing Dust|';
        $mtr_icn  ['BLSA'] = 'dust           |dust                |Blowing Sand|';
        $mtr_icn  ['DU']   = 'dust           |dust                |Widespread Dust|';
        $mtr_icn  ['SA']   = 'dust           |dust                |Sand|';
        $mtr_icn  ['DS']   = 'dust           |dust                |Dust Storm|';
        $mtr_icn  ['PO']   = 'dust           |dust                |Dust or Sand Whirls|';
        $mtr_icn  ['SS']   = 'dust           |dust                |Sand Storm|';
        $mtr_icn  ['FC']   = 'dust           |dust                |Tornado or Water_Spout|';
# Volcanic Ash
        $mtr_icn  ['FU']   = 'dust           |dust                |Smoke|';
        $mtr_icn  ['FC']   = 'dust           |dust                |Tornado|';
        $mtr_icn  ['VA']   = 'volcanoe       |volcanoe            |Vulcanic_Ash|';
#        
        $mtr_icn  ['UP']   = 'unknown        |unknown             |Unknown|';
#
        if (array_key_exists($cnd_cd,$mtr_icn) )
             {  list ($d_icon, $n_icon,$text)   = explode ('|',$mtr_icn  [$cnd_cd]);
                if ($itsday == true) 
                     {  $sky_icon = trim($d_icon);} 
                else {  $sky_icon = trim($n_icon);} 
                $sky_desc = trim($text); 
        }
} // eo find condition
#
if ($metar34conditionstext == '' && $sky_desc <> '') {
        $metar34conditionstext = 
                ucfirst (lang(strtolower($cnd_prfx))).
                ucfirst (lang(strtolower($sky_desc))).
                ucfirst (lang(strtolower($cnd_shwr)));}
#
if ($sky_icon == '') {
        $mtr_icn        = array();
        $mtr_icn  ['CLR']   = 
        $mtr_icn  ['CAVOK'] =
        $mtr_icn  ['NSC']   =
        $mtr_icn  ['NCD']   =
        $mtr_icn  ['SKC']   = 'clear_day        |clear_night            |'.$lng_clr.'|';
        $mtr_icn  ['FEW']   = 'few_day          |few_night              |'.$lng_prtl_cld.'|';
        $mtr_icn  ['SCT']   = 'few_day          |few_night              |'.$lng_mstl_sct.'|';
        $mtr_icn  ['BKN']   = 'mc_day           |mc_night               |'.$lng_mstl_cld.'|';
        $mtr_icn  ['OVX']   =        
        $mtr_icn  ['OVC']   = 'ovc              |ovc_dark               |'.$lng_overcast.'|';

        if (array_key_exists($metar34clouds,$mtr_icn) )
             {  list ($d_icon, $n_icon,$text)   = explode ('|',$mtr_icn  [$metar34clouds]);
                if ($itsday == true) 
                     {  $sky_icon = trim($d_icon);} 
                else {  $sky_icon = trim($n_icon);} 
                $sky_desc = trim($text); }
        else  { $sky_icon='ovc_dark';
                $sky_desc='Unknown';}
} 
$sky_icon='pws_icons/'.$sky_icon.'.svg';
#
$stck_lst      .= basename(__FILE__).' ('.__LINE__.')' 
        .' $sky_icon='.$sky_icon
        .' | $sky_desc='.$sky_desc
        .' | metar34conditions='.$cnd_cd
        .' | metar34conditionstext='.$metar34conditionstext
        .' | $metar34clouds='.$metar34clouds.PHP_EOL;      

if ($metar34conditionstext <> '') {$sky_desc = $metar34conditionstext;}
#if (isset ($_REQUEST['test']) ) echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL;
