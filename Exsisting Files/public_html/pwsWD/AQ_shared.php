<?php $scrpt_vrsn_dt  = 'AQ_shared.php|01|2023-09-09|';  # swapped text descriptions + release 2012_lts 2020-11-02
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
# ------------------------------     texts used
$tr_updt        = lang('Updated');
$tr_station     = lang('Station');
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
# 
# -------------------------- levels used
if (!isset ($aqhi_type) ) {$aqhi_type = 'epa';}    
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') aqhi_type =>'.$aqhi_type.PHP_EOL;  
#
$t_color        =  '#000000';  // if we want text-color adapted on background color
$to_outside = '<svg x="0px" y="0px" width="10" height="10" fill="currentcolor" stroke="currentcolor" stroke-width="8%" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000">
<g><path d="M500,10C229.4,10,10,229.4,10,500c0,270.6,219.4,490,490,490c270.6,0,490-219.4,490-490C990,229.4,770.6,10,500,10z M500,967.9C241.6,967.9,32.1,758.4,32.1,500C32.1,241.6,241.6,32.1,500,32.1c258.4,0,467.9,209.5,467.9,467.9C967.9,758.4,758.4,967.9,500,967.9z M634.6,501.4l-247,248.3L371,733l230.3-231.6L371,269.8l16.6-16.7L634.6,501.4L634.6,501.4z"></path></g>
</svg>'.PHP_EOL;
#
$pm10_eea    = array (0,   20,  40,  50,   100,  150,  1200);
$pm25_eea    = array (0,   10,  20,  25,    50,   75,   800);
$ozone_eea   = array (0,   80, 120, 180,   240,  480,   600);  #        µg/m3)
$AQ_eea      = array (0,    1,   2,   3,     4,    5,     6);

# epa
$pm10_epa    = array (0,   54,  154,  254,   354,   424,   604);
$pm25_epa    = array (0.0, 12,   35.4, 55.4, 150.4, 250.4, 500.4);
$ozone_epa   = array (0.0, 54,   70,   85,   105,   200,   604);  # ppb  1 ppb = `2,00` µg/m3
$AQ_epa      = array (0,   50,  100,  150,   200,   300,   500);
#
$clsstxts[0]    = '';
$clsstxts[1]    = 'Air quality is considered satisfactory, and air pollution poses little or no risk';
$clsstxts[2]    = 'Air quality is acceptable; however, for some pollutants there may be a moderate health concern for a very small number of people who are unusually sensitive to air pollution.';
$clsstxts[3]    = 'Members of sensitive groups may experience health effects. The general public is not likely to be affected.';
$clsstxts[4]    = 'Everyone may begin to experience health effects; members of sensitive groups may experience more serious health effects.';
$clsstxts[5]    = 'Health alert: everyone may experience more serious health effects';                                  # 2023-09-08
$clsstxts[6]    = 'Health warnings of emergency conditions. The entire population is more likely to be affected';       # 2023-09-08
$clsstxts[7]    = 'unkown / error in data';
#
#$aqhi_type = 'eea';
#
if (!function_exists ('aq_array_load') ) {
     function aq_array_load () {
        global  $clssfctn, $aqhi_type, $explain_link, 
                $pm10_levels, $pm25_levels, $o3_levels, $AQ_levels, 
                $pm10_epa,    $pm25_epa,    $ozone_epa, $AQ_epa,
                $pm10_eea,    $pm25_eea,    $ozone_eea, $AQ_eea,
                $nr_blocks,$aq_text,$aq_class, $aq_color, $aq_color_txt, $aq_icon, $clsstxts;
        if ($aqhi_type == 'eea') {
                $explain_link   = 'https://www.eea.europa.eu/themes/air/air-quality-index'; #'https://www.euronews.com/weather/copernicus-air-quality-index';
                $pm10_levels    = $pm10_eea;  #echo print_r($pm10_levels,true).PHP_EOL;
                $pm25_levels    = $pm25_eea;
                $o3_levels      = $ozone_eea;
                $AQ_levels      = $AQ_eea;
                $nr_blocks      = count($AQ_levels) -1; 
                $aq_text        = array ('GoodAQ',         'GoodAQ',          'FairAQ',         'ModerateAQ',     'PoorAQ',        'VeryPoorAQ',      'ExtremelyPoorAQ'); 
                $aq_class       = array ('green',          'green',           'fair',          'yellow',         'red',           'purple',          'maroon'); 
                $aq_color       = array ('rgb(80,240,230)','rgb(80,240,230)', 'rgb(80,204,170)','rgb(240,230,65)','rgb(255,80,80)','rgb(150,0,50)',   'rgb(125, 33, 129)');
                $aq_color_txt   = array ('black',          'black',           'white',          'black',          'white',          'white',          'white');
                $aq_icon        = array ('aq_green.svg',   'aq_green.svg',    'aq_yellow.svg',  'aq_yellow.svg',  'aq_orange.svg',  'aq_red.svg',     'aq_purple.svg');
                $clssfctn       = $clsstxts;
        }
        else {  # ($aqhi_type == 'epa') {
                $explain_link   = 'https://www.airnow.gov/aqi/aqi-basics/';
                $pm10_levels    = $pm10_epa;
                $pm25_levels    = $pm25_epa;
                $o3_levels      = $ozone_epa;
                $AQ_levels      = $AQ_epa;
                $nr_blocks      = count($AQ_levels) -1;
                $aq_class       = array ('green',        'green',       'yellow',        'orange',        'red',          'purple',         'maroon'); 
                $aq_color       = array ('rgb(0,228,0)', 'rgb(0,228,0)','rgb(255,255,0)','rgb(255,126,0)','rgb(255, 0,0)','rgb(143,63,151)','rgb(126,0,35)');
                $aq_color_txt   = array ('black',        'black',        'black',        'black',        'black',         'white',          'white');
                $aq_icon        = array ('aq_green.svg', 'aq_green.svg','aq_yellow.svg', 'aq_orange.svg', 'aq_red.svg',   'aq_purple.svg',  'aq_maroon.svg');
                $aq_text        = array ('GoodAQ',       'GoodAQ',      'ModerateAQ',    'UnhealthyFSAQ', 'UnhealthyAQ',  'VeryUnhealthyAQ','HazordousAQ'); 
                $clssfctn       = $clsstxts;    
        }
     } // eof aq_array_load
} // eo check function exits
#
if (!function_exists ('pm25_to_aqi') ) {
        function pm25_to_aqi($pm){ 
                global $aqhi_type, $nr_blocks, $pm25_levels, $AQ_levels, $stck_lst;
                aq_array_load (); # echo '<pre>'.print_r($pm25_levels,true); # exit;
                if  ($pm > $pm25_levels[$nr_blocks])
                     {  $stck_lst      .= basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.')  pm='.$pm. '> max of pm25_levels['.$nr_blocks.'] of '.$pm25_levels[$nr_blocks].PHP_EOL;
                        return $AQ_levels[$nr_blocks];}
                for ($n = 0; $n < $nr_blocks; $n++) 
                     {  if  ($pm > $pm25_levels[$n+1]) {  continue;}
                        break;} // eo for loop 
#echo basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.') $n='.$n.' pm='.$pm. ' $pm25_levels[$n+1]='.$pm25_levels[$n+1].PHP_EOL;         
 #               if ($aqhi_type == 'eea') {return $n+1;}
                # example pm2.5 = 30; range pm 12.0 -> 35.4 range AQ = 50 -> 100 
                $pmRange        = $pm25_levels[$n+1]    - $pm25_levels[$n];     // 35.4 - 12  = 23.4
                $aqRange        = $AQ_levels  [$n+1]    - $AQ_levels  [$n];     // 100  - 50  = 50
                $pmStep         = $pm                   - $pm25_levels[$n];     // 30   - 12  = 18
                $pmPart         = $pmStep / $pmRange;                           // 18   / 23.4= 0/7692
                $aqStep         = $aqRange * $pmPart;                           // 50 *0.7692 = 38.462
                $return         = $aqStep + $AQ_levels  [$n];                   // 38.462 + 50= 88.462
            #    $scaleFactor    = $toRange / $fromRange;
            #    $tmpValue       = $value - $fromLow;            # Re-zero the value within the from range
             #   $tmpValue       = $tmpValue * $scaleFactor;     # Rescale the value to the to range
              #  $return         = $tmpValue + $toLow;           # Re-zero back to the to range
$stck_lst      .= basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.') '
                .' $aqhi_type='.$aqhi_type.' $n='.$n
                .'pm='.$pm. ' $pmPart='.$pmPart
                .' $pm25_levels[$n]='.$pm25_levels[$n]
                .' $pm25_levels[$n+1]='.$pm25_levels[$n]
                .' $return='.$return.PHP_EOL;
                return $return;
        }    // eof   pm25_to_aqi  
} // eo exist pm25_to_aqi
#
if (!function_exists ('pm10_to_aqi') ) {
        function pm10_to_aqi($pm){
                global $aqhi_type, $nr_blocks, $pm10_levels, $AQ_levels, $stck_lst;
                aq_array_load (); # echo  '$nr_blocks='.$nr_blocks.print_r($pm10_levels,true).PHP_EOL; exit;
                if  ($pm > $pm10_levels[$nr_blocks])
                     {  $stck_lst      .= basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.') $aqhi_type='.$aqhi_type.' pm='.$pm. '> max of pm10_levels['.$nr_blocks.'] of '.$pm10_levels[$nr_blocks].PHP_EOL;
                        return $AQ_levels[$nr_blocks];}
                for ($n = 0; $n < $nr_blocks; $n++) 
                     {  if  ($pm > $pm10_levels[$n+1]) {  continue;}  
                        break;} 
#                if ($aqhi_type == 'eea') {return $n+1;}
                $pmRange        = $pm10_levels[$n+1]    - $pm10_levels[$n];     // 35.4 - 12  = 23.4
                $aqRange        = $AQ_levels  [$n+1]    - $AQ_levels  [$n];     // 100  - 50  = 50
                $pmStep         = $pm                   - $pm10_levels[$n];     // 30   - 12  = 18
                $pmPart         = $pmStep / $pmRange;                           // 18   / 23.4= 0/7692
                $aqStep         = $aqRange * $pmPart;                           // 50 *0.7692 = 38.462
                $return         = $aqStep + $AQ_levels  [$n];                   // 38.462 + 50= 88.462
                return $return;
        } // eof   pm10_to_aqi  
} // eo exist pm10_to_aqi
#
if (!function_exists ('o3_to_aqi') ) {
        function o3_to_aqi($pm){
                global $aqhi_type, $nr_blocks, $o3_levels, $AQ_levels, $stck_lst;
                aq_array_load (); # echo  '$nr_blocks='.$nr_blocks.print_r($o3_levels,true).PHP_EOL; exit;
                if  ($pm > $o3_levels[$nr_blocks])
                     {  $stck_lst      .= basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.') $aqhi_type='.$aqhi_type.' pm='.$pm. '> max of pm10_levels['.$nr_blocks.'] of '.$o3_levels[$nr_blocks].PHP_EOL;
                        return $AQ_levels[$nr_blocks];}
                for ($n = 0; $n < $nr_blocks; $n++) 
                     {  if  ($pm > $o3_levels[$n+1]) {  continue;}  
                        break;} 
#                if ($aqhi_type == 'eea') {return $n+1;}
                $pmRange        = $o3_levels[$n+1]      - $o3_levels[$n];       // 35.4 - 12  = 23.4
                $aqRange        = $AQ_levels  [$n+1]    - $AQ_levels  [$n];     // 100  - 50  = 50
                $pmStep         = $pm                   - $o3_levels  [$n];     // 30   - 12  = 18
                $pmPart         = $pmStep / $pmRange;                           // 18   / 23.4= 0/7692
                $aqStep         = $aqRange * $pmPart;                           // 50 *0.7692 = 38.462
                $return         = $aqStep + $AQ_levels  [$n];                   // 38.462 + 50= 88.462
                return $return;
        } // eof   pm10_to_aqi  
} // eo exist pm10_to_aqi
#
if (!function_exists ('aq_set_clrs') ) {
        function aq_set_clrs ($aqi) {
                global $AQ_levels, $aq_icon, $aq_class, $b_color, $aq_color, $aq_text, $nr_blocks, $stck_lst;
                aq_array_load ();
                foreach ($AQ_levels as $n => $value) {
                        if ($aqi > $AQ_levels[$nr_blocks]) 
                             { $n       =  $nr_blocks;}
                        elseif ($aqi > $value) { continue;}
                        $arr    = array();
                        $arr['icon']    = $aq_icon[$n];
                        $arr['class']   = 'dottedcircle'.$aq_class[$n];
                        $arr['color']   = $b_color = $aq_color[$n];
                        $arr['text']    = $aq_text[$n];
                        $arr['clmn']    = $n;
                        break;}
#echo  basename(__FILE__).' '.__FUNCTION__.' ('.__LINE__.') $aqi=' .$aqi.' $AQ_levels='.print_r($AQ_levels,true).' $arr='.print_r($arr,true); exit;             
                return $arr;
        }
} // eo exist aq_set_clrs
   
if (!function_exists ('aq_epa_eea') ) {
        function aq_epa_eea ($pol, $aqi) {
                global $stck_lst, $pm10_epa, $pm25_epa, $ozone_epa, $AQ_epa;
                $newpm  = false;                  #  echo '<pre>'.' $pol='.$pol.' $aqi='.$aqi.PHP_EOL; 
                $count  = count ($AQ_epa) - 1;    #  echo __LINE__.' $count='.$count.PHP_EOL; 
                for ($n = 1; $n < $count; $n++)
                     {  if ($aqi > $AQ_epa[$count]) {$aqi = $AQ_epa[$count]; $n = $count;}
                        if ($aqi > $AQ_epa[$n]) {continue;}        #  echo __LINE__.' $n='.$n.PHP_EOL;
                        $start  = $AQ_epa[$n-1];                   #  echo __LINE__.' $start='.$start.PHP_EOL;
                        $range  = $AQ_epa[$n]  - $start;           #  echo __LINE__.' $range='.$range.PHP_EOL;
                        $rest   = $aqi  - $start;                  #  echo __LINE__.' $rest='.$rest.PHP_EOL;
                        $calc   = $rest / $range;                  #  echo __LINE__.' $calc='.$calc.PHP_EOL;

                        $rang10 = $pm10_epa[$n]  - $pm10_epa[$n-1];  #  echo __LINE__.' $rang10='.$rang10.PHP_EOL;
                        $part10 = $calc * $rang10;               #  echo __LINE__.' $part10='.$part10.PHP_EOL;
                        $pm10   = $pm10_epa[$n-1] + $part10;     #  echo __LINE__.' $pm10='.$pm10.PHP_EOL;

                        $rang25 = $pm25_epa[$n]  - $pm25_epa[$n-1];  #  echo __LINE__.' $rang25='.$rang25.PHP_EOL;
                        $part25 = $calc * $rang25;               #  echo __LINE__.' $part25='.$part25.PHP_EOL;
                        $pm25   = $pm25_epa[$n-1] + $part25;     #  echo __LINE__.' $pm25='.$pm25.PHP_EOL;

                        $rangO3 = $ozone_epa[$n] - $ozone_epa[$n-1];   #  echo __LINE__.' $rangO3='.$rangO3.PHP_EOL;
                        $partO3 = $calc * $rangO3;                #  echo __LINE__.' $partO3='.$partO3.PHP_EOL;
                        $O3_ppb = $ozone_epa[$n-1]+ $partO3;      #  echo __LINE__.' $O3_ppb='.$O3_ppb.PHP_EOL;
                        $O3_um3 = $O3_ppb * 1.97;                 #  echo __LINE__.' $O3_um3='.$O3_um3.PHP_EOL;     
                        break;
                } // eo forloop
                if      ($pol == 'pm10') 
                     {  $return = pm10_to_aqi ($pm10);           #  echo __LINE__.' $return='.$return.PHP_EOL;
                        }
                elseif  ($pol == 'pm25') {$return = pm25_to_aqi ($pm25);}
                elseif  ($pol == 'o3')   {$return = o3_to_aqi ($pm25); } # echo $stck_lst; exit;}
                else {  echo 'unknown pollutant '.$pol;
                        $return = false;}
                return $return;
        } // eof aq_epa_eea
}// eo exist  aq_epa_eea