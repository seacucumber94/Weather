<?php  $scrpt_vrsn_dt  = 'uvsolarlux_popup.php|01|2020-11-04|';  # release 2012_lts
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = true;         // use generalsetting 
#$show_close_x   = false;        // set to false to switch of regardless of settings
$color          = "#FF7C39"; // head line
$fill           = 'grey';
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
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# ------------------------- translation of texts
$ltxt_clsppp    = lang('Close');
$ltxt_url       = lang('Solar - UV-Index - Lux');
$ltxt_hd1       = lang('CURRENT UVINDEX');
$ltxt_hd2       = lang('Solar Radiation');
$ltxt_hd3       = lang('Current UVINDEX Advisory');
$ltxt_hd4       = lang('Lux Brightness');
#
$strng10= lang('Solar Radiation').': ';
$strng11= lang('Excellent and Sustainable');
$strng12= lang('Good and Sustainable');
$strng13= lang('Moderate ');
$strng14= lang('Poor ');
$strng20= lang('Solar Energy Replenishment').': ';
$strng21= lang('Good to Excellent');
$strng22= lang('Moderate to Good');
$strng23= lang('Low to Moderate');
$strng24= lang('Low ');
$strng25= lang('Poor ');
$strng26= lang('None ');
#
$strng38= lang('Very high risk of harm from unprotected sun exposure. Take extra precautions.');
$strng37= lang('High risk of harm from unprotected sun exposure.');
$strng35= lang('Moderate risk of harm from unprotected sun exposure.');
$strng32= lang('Low danger from the sun&apos;s UV rays for the average person');

$lux_txt = lang('Measures the approximate human eye response to light under a variety of lighting conditions.').' ' 
          .lang('The total amount of all the light measured is known as the "luminous flux".');
$lux_hd  = lang('Lux measurement');

#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['yellow']       = '#ecb454'; #rgba(186, 146, 58, 1)';
$b_clrs['green']        = '#9aba2f';
# ----------------------------------- for testing only 
#$weather["solar"] = 500 ;
#$weather["uv"] = 5;
#$weather['lux'] = 150000; #'--';  
# ------------------------------------- for testing only 
#
# ----------- values to be used: both  wf  darksky false
switch ($uvsolarsensors)  { 
    case 'darksky' :
        $scrpt          = 'fct_darksky_shared.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
        include_once $scrpt;
        if (isset ($darkskyhourlyuv))
             { $weather['uv']  = (float) $darkskyhourlyuv;}
        break;
    case 'both': 
        break;   
    case 'wf':
        if ($weatherflowoption == true )
             {  $weather['solar']  = $weatherflow['solar']  ;
                $weather['uv']     = $weatherflow['uv']     ;
                $weather['lux']    = $weatherflow['lux']    ; 
                break;}
     default:
        echo '<br />'.lang('No valid sensors found');
        return;
} // eo switch
#
$uv_lvl = (int) $weather['uv'];
$uv_img = $uv_lvl;
if ($uv_img > 11) {$uv_img = 11;} 

$sol_lvl= (float) $weather['solar'];
$lux_lvl= (float) $weather['lux'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
'.my_style().'
</head>
<body class="dark">
    <div class="PWS_module_title" style="width: 100%; height: 20px; padding-top: 4px; font-size: 14px;" >
'.$close
.'    <span style="color: '.$color.'; font-size: 14px;">'.$ltxt_url.'</span>
    </div>
    <div class="PWS_weather_container"><!-- toprow -->
        <div class="PWS_weather_item" style="position: relative;"><!-- weatheritem 1 -->
            <div class="PWS_module_title"><div class="title">'.$ltxt_hd1.'</div></div>
<svg id="pyramid_module" width="130pt" height="130pt" viewBox="0 0 980 792">'.PHP_EOL;
#
$fll    = array();
$fll[0]  = $b_clrs['green'];
$fll[1]  = $b_clrs['green'];
$fll[2]  = $b_clrs['green'];
$fll[3]  = $b_clrs['yellow'];
$fll[4]  = $b_clrs['yellow'];
$fll[5]  = $b_clrs['yellow'];
$fll[6]  = $b_clrs['orange'];
$fll[7]  = $b_clrs['orange'];
$fll[8]  = $b_clrs['red'];
$fll[9]  = $b_clrs['red'];
$fll[10]  = $b_clrs['red'];
$fll[11]  = $b_clrs['maroon'];
$d2     = array();
$d2[1]  =' d=" M 37.24 719.17 C 147.49 718.78 257.75 719.11 368.00 719.00 C 559.56 719.13 751.13 718.75 942.69 719.19 C 954.83 742.74 967.22 766.17 980.00 789.39 L 980.00 792.00 L 0.00 792.00 L 0.00 789.37 C 12.76 766.16 25.41 742.86 37.24 719.17 Z" />';
$d2[2]  =' d=" M 81.31 637.18 C 353.79 636.86 626.31 636.91 898.78 637.16 C 906.01 653.63 915.67 668.54 923.22 684.82 C 927.18 690.19 930.08 696.53 932.10 702.93 C 637.39 703.05 342.67 703.04 47.96 702.93 C 49.21 696.12 53.57 689.92 57.16 684.13 C 64.21 667.89 74.16 653.32 81.31 637.18 Z" />';
$d2[3]  =' d=" M 125.36 555.16 C 368.45 554.89 611.55 554.89 854.64 555.16 C 857.46 560.61 860.95 565.93 863.17 571.85 C 867.36 577.00 869.52 583.57 872.56 589.48 C 877.07 594.10 877.62 601.36 881.96 606.05 C 884.38 610.97 887.32 615.73 889.13 620.93 C 623.06 621.05 356.98 621.05 90.91 620.93 C 94.59 610.88 100.90 602.01 105.31 592.28 C 110.35 586.23 111.63 577.81 116.82 571.85 C 119.04 565.93 122.54 560.61 125.36 555.16 Z" />';
$d2[4]  =' d=" M 165.17 481.10 C 381.75 480.90 598.33 481.00 814.91 481.05 C 822.14 494.55 829.45 508.01 836.33 521.71 C 839.46 527.32 843.00 532.83 845.13 538.93 C 608.38 539.05 371.62 539.05 134.86 538.92 C 138.62 529.13 144.48 520.39 149.05 510.99 C 154.83 501.25 159.22 490.74 165.17 481.10 Z" />';
$d2[5]  =' d=" M 201.31 412.18 C 393.79 411.86 586.31 411.91 778.78 412.15 C 786.97 430.19 797.63 447.15 806.12 464.93 C 595.39 465.05 384.65 465.04 173.92 464.94 C 182.32 447.09 192.76 430.04 201.31 412.18 Z" />';
$d2[6]  =' d=" M 238.32 343.18 C 406.12 342.88 573.96 342.88 741.75 343.18 C 747.04 352.67 751.46 362.64 756.99 372.00 C 760.56 380.21 766.14 387.46 769.13 395.93 C 583.06 396.05 396.98 396.05 210.91 395.93 C 219.12 377.94 229.43 360.88 238.32 343.18 Z" />';
$d2[7]  =' d=" M 273.33 277.19 C 417.80 276.86 562.31 276.91 706.77 277.16 C 715.16 293.95 724.34 310.34 733.02 326.99 C 571.02 327.01 409.02 327.02 247.03 326.98 C 255.55 310.16 264.54 293.84 273.33 277.19 Z" />';
$d2[8]  =' d=" M 306.28 215.17 C 428.76 214.89 551.28 214.88 673.75 215.18 C 682.09 230.39 689.68 245.75 698.02 260.98 C 559.35 261.01 420.67 261.02 282.00 260.97 C 290.25 245.78 298.01 230.35 306.28 215.17 Z" />';
$d2[9]  =' d=" M 336.08 158.04 C 438.69 157.99 541.30 157.93 643.91 158.07 C 648.49 167.73 654.44 176.70 658.49 186.62 C 661.14 190.43 663.71 194.47 665.13 198.93 C 548.38 199.05 431.62 199.05 314.87 198.92 C 321.04 184.87 329.43 171.89 336.08 158.04 Z" />';
$d2[10] =' d=" M 365.36 103.21 C 448.45 102.86 531.56 102.87 614.65 103.20 C 618.01 109.63 621.86 115.83 624.49 122.62 C 628.78 128.60 631.63 135.53 635.15 141.92 C 538.38 142.05 441.62 142.06 344.85 141.92 C 348.40 135.23 351.34 128.01 355.89 121.92 C 358.38 115.35 361.97 109.32 365.36 103.21 Z" />';
$d2[11] =' d=" M 394.17 49.10 C 458.08 48.90 522.01 48.99 585.92 49.06 C 589.55 57.30 594.93 64.39 598.17 72.85 C 601.66 76.94 603.46 82.25 605.95 87.01 C 528.65 86.97 451.34 87.04 374.03 86.97 C 380.77 74.37 386.86 61.39 394.17 49.10 Z" />';

for ($i = 1; $i < 12; $i++)
     {  if ($uv_img >=  $i )  {$color = $fll[$i];} else {$color = $fill;};
        echo '<path fill="'.$color.'" '.$d2[$i].PHP_EOL;}
$uv_color       = $fll[$uv_img];
echo '</svg>
<!-- uv-index circle -->
<div style ="position: absolute; top: 80px; left: 131px; background-color: '.$uv_color.';
    height: 45px; width: 45px;  border-radius: 50%;
    color: #fff;
    line-height: 10px;
    font-size: 24px;
    font-family: Helvetica,sans-seriff;
    border: 1px solid #FFFFFF;
    font-weight: 400;">
<br /><br />'.$uv_lvl.'
</div>  
        </div>
<!-- eo weatheritem UV index -->
<!-- weatheritem 2 Solar radiation -->
        <div class="PWS_weather_item">
            <div class="PWS_module_title"><div class="title">'.$ltxt_hd2.'</div></div>
            <div style="width: 20%; float: right; padding-right: 6px;">
                <br />';
if     ($sol_lvl >=800)  { echo '<img src="./pws_icons/uvstrong.svg" width="60" alt="uvstrong"/>';}	
elseif ($sol_lvl  >=1)   { echo '<img src="./pws_icons/clear_day.svg" width="60" alt="clear"/>';}	
else                     { echo '<img src="./pws_icons/nosunuv.svg" width="60" alt="nosunuv"/>';}
echo '
                <br />
<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>
';
$clr_lux        = $fill;
$lvl    = array(1000,900,800,700,600,500,400,300,200,100,50,0);
$fll    = array();
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['purple']; 
$fll[] = $b_clrs['purple'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['orange'];
$fll[] = $b_clrs['yellow']; 
$fll[] = $b_clrs['yellow'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
$d      = array();
$d[] = 'd=" M 7.00  8.01 C 17.00  8.00 27.00  8.00 37.00  8.00 C 37.00  8.75 37.00 10.25 37.00 11.00 C 27.00 11.00 17.00 11.00 7.00 11.00 C 7.00 10.25 7.00  8.75 7.00  8.01 Z" />';
$d[] = 'd=" M 7.00 12.00 C 17.00 12.00 27.00 12.00 37.00 12.00 C 37.00 13.67 37.00 15.33 37.00 17.00 C 27.00 17.00 17.00 17.00 7.00 17.00 C 7.00 15.33 7.00 13.67 7.00 12.00 Z" />';
$d[] = 'd=" M 7.00 18.00 C 17.00 18.00 27.00 18.00 37.00 18.00 C 37.00 19.67 37.00 21.33 37.00 23.00 C 27.00 23.00 17.00 23.00 7.00 23.00 C 7.00 21.33 7.00 19.67 7.00 18.00 Z" />';
$d[] = 'd=" M 7.00 24.00 C 17.00 24.00 27.00 24.00 37.00 24.00 C 37.00 25.67 37.00 27.33 37.00 29.00 C 27.00 29.00 17.00 29.00 7.00 29.00 C 7.00 27.33 7.00 25.67 7.00 24.00 Z" />';
$d[] = 'd=" M 7.00 30.00 C 17.00 30.00 27.00 30.00 37.00 30.00 C 37.00 31.67 37.00 33.33 37.00 35.00 C 27.00 35.00 17.00 35.00 7.00 35.00 C 7.00 33.33 7.00 31.67 7.00 30.00 Z" />';
$d[] = 'd=" M 7.00 36.00 C 17.00 36.00 27.00 36.00 37.00 36.00 C 37.00 37.67 37.00 39.33 37.00 41.00 C 27.00 41.00 17.00 41.00 7.00 41.00 C 7.00 39.33 7.00 37.67 7.00 36.00 Z" />';
$d[] = 'd=" M 7.00 42.00 C 17.00 41.99 27.00 42.00 37.00 42.00 C 37.00 43.67 37.00 45.33 37.00 47.00 C 27.00 47.00 17.00 47.00 7.00 47.00 C 7.00 45.33 7.00 43.67 7.00 42.00 Z" />';
$d[] = 'd=" M 7.00 48.00 C 17.00 48.00 27.00 48.00 37.00 48.00 C 37.00 49.67 37.00 51.33 37.00 53.00 C 27.00 53.00 17.00 53.00 7.00 53.00 C 7.00 51.33 7.00 49.67 7.00 48.00 Z" />';
$d[] = 'd=" M 7.00 54.00 C 17.00 54.00 27.00 54.00 37.00 54.00 C 37.00 55.67 37.00 57.33 37.00 59.00 C 27.00 59.00 17.00 59.00 7.00 59.00 C 7.00 57.33 7.00 55.67 7.00 54.00 Z" />';
$d[] = 'd=" M 7.00 60.00 C 17.00 60.00 27.00 60.00 37.00 60.00 C 37.00 61.67 37.00 63.33 37.00 65.00 C 27.00 65.00 17.00 65.00 7.00 65.00 C 7.00 63.33 7.00 61.67 7.00 60.00 Z" />';
$d[] = 'd=" M 7.00 66.00 C 17.00 66.00 27.00 66.00 37.00 66.00 C 37.00 67.67 37.00 69.33 37.00 71.00 C 27.00 71.00 17.00 71.00 7.00 71.00 C 7.00 69.33 7.00 67.67 7.00 66.00 Z" />';
$d[] = 'd=" M 7.00 72.00 C 17.00 72.00 27.00 72.00 37.00 72.00 C 37.00 73.67 37.00 75.33 37.00 77.00 C 27.00 77.00 17.00 77.00 7.00 77.00 C 7.00 75.33 7.00 73.67 7.00 72.00 Z" />';
for ($i = 0; $i < 12; $i++)
     {  if ($sol_lvl <=  $lvl[$i]) 
             {  $color = $fill; } 
        else {  $color =  $fll[$i]; }
        echo '<path fill="'.$color.'" '.$d[$i].PHP_EOL;
        if ($clr_lux == $fill) {$clr_lux = $color;}
        }
echo'</svg>
            </div>
            <div style="width: 75%; float: left; padding-left: 6px; text-align: left;">
            <br />
<span style="font-size: 36px;">
<span style="color: '.$clr_lux.';">'.$sol_lvl.'</span>
<span style="font-size: 12px;" > w/m<sup>2</sup></span>
</span>
<br />';


if ($sol_lvl >1000)     { $txt1   = $strng11;    $txt2 = $strng21;  $color  = 'green';} 
elseif ($sol_lvl >600)  { $txt1   = $strng12;    $txt2 = $strng22;  $color  = 'green';}  
elseif ($sol_lvl >400)  { $txt1   = $strng13;    $txt2 = $strng23;  $color  = 'orange';}  
elseif ($sol_lvl >200)  { $txt1   = $strng14;    $txt2 = $strng24;  $color  = 'yellow';}  
elseif ($sol_lvl >100)  { $txt1   = $strng14;    $txt2 = $strng25;  $color  = 'yellow';}  
else                    { $txt1   = $strng14;    $txt2 = $strng26;  $color  = 'red';}  


echo '<p style="font-size: 12px;" >'.$strng10
        .'<br /><span style="color: '.$clr_lux.'">'.$txt1.'</span><br /><br />'
        .$strng20.'<br /><span style="color: '.$color.'">'.$txt2.'</span></p>';
echo '            
            </div>        
        </div>
<!-- eo weatheritem Solar radiation -->
    </div><!-- eo toprow -->
    <div class="PWS_weather_container"><!-- second row -->
<!-- weatheritem 3 UV info -->    
        <div class="PWS_weather_item " style="position: relative;">
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd3.'</div></div>
<table style="font-size: 12px; text-align: center; vertical-align: top; padding-left: 4px; margin-top: 4px;border-collapse: collapse;">
<tr style="border-bottom: 1px solid grey;"><td style="min-width: 24px;">'.lang('UVI').'</td><td>'.lang('Description').'</td></tr>
<tr style="border-bottom: 1px solid grey;"><td class="red    strongnumbers">  8</td><td style="text-align: left;" >'.$strng38.'</td></tr>
<tr style="border-bottom: 1px solid grey;"><td class="orange strongnumbers">6-7</td><td style="text-align: left;" >'.$strng37.'</td></tr>
<tr style="border-bottom: 1px solid grey;"><td class="yellow strongnumbers">3-5</td><td style="text-align: left;" >'.$strng35.'</td></tr>
<tr style="border-bottom: 1px solid grey;"><td class="green  strongnumbers">0-2</td><td style="text-align: left;" >'.$strng32.'</td></tr>
</table>
        </div>
<!-- eo weatheritem UV info -->
        <div class="PWS_weather_item " style="position: relative;">
<!-- weatheritem  4 info -->
        <div class="PWS_module_title"><div class="title">'.$ltxt_hd4.'</div></div>
            <div style="width: 20%; float: right; padding-right: 6px;">
                <br />';
if ($lux_lvl >=1)   { echo '<img src="./pws_icons/clear_day.svg" width="60" alt="clear"/>';}	
else                { echo '<img src="./pws_icons/nosunuv.svg" width="60" alt="nosunuv"/>';}	
echo '
                <br />
<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>
';
$clr_lux = $fill;
$lvl    = array(110000,90000,80000,70000,60000,50000,40000,30000,20000,10000,5000,0);
$fll    = array();
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['maroon'] ;
$fll[] = $b_clrs['purple']; 
$fll[] = $b_clrs['purple'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['red'];
$fll[] = $b_clrs['orange'];
$fll[] = $b_clrs['yellow']; 
$fll[] = $b_clrs['yellow'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
$fll[] = $b_clrs['green'];
for ($i = 0; $i < 12; $i++)
     {  if ($lux_lvl <=  $lvl[$i]) 
             {  $color = $fill;} 
        else {  $color = $fll[$i];}
        if ($clr_lux == $fill) {$clr_lux = $color;}
        echo '<path fill= "'.$color.'" '.$d[$i].PHP_EOL;
        }
#
echo'</svg>
            </div>
            <div style="width: 75%; float: left; padding-left: 6px; text-align: left;">
            <br />
<span style="font-size: 36px;">
<span style="color: '.$clr_lux.';">'.$lux_lvl.'</span>
<span style="font-size: 12px;" > '.$ltxt_hd4.'</span>
</span>
<p style="font-size: 13px;"><span style="color: '.$clr_lux.';">'.$lux_hd.'</span>
<br />
'.$lux_txt.'
</p>
            </div>      
        </div>
<!-- eo weatheritem lux info --> 
    </div>'.PHP_EOL;
if (isset ($_REQUEST['test'] ) ) {echo '<!-- '.$stck_lst.' -->'; } 
echo ' </body>
</html>'.PHP_EOL;
#
function my_style()
     {  global $popup_css ,$b_clrs ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
# add pop-up specific css
        $return .= '
        .orange      { color: '.$b_clrs['orange'].';}
        .green       { color: '.$b_clrs['green'].';}
        .blue        { color: #01a4b4;}
        .yellow      { color: '.$b_clrs['yellow'].';}
        .red         { color: '.$b_clrs['red'].';}
        .purple      { color: '.$b_clrs['purple'].';}
        .maroon      { color: '.$b_clrs['maroon'].';}';
        $return         .= '    </style>'.PHP_EOL;
        return $return;
    }
