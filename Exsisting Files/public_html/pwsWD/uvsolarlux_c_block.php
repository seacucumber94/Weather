<?php  $scrpt_vrsn_dt  = 'uvsolarlux_c_block.php|01|2020-11-03|';  # release 2012_lts
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
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
$online_txt_wf  =
#
# ------------------------- translation of texts
$offl_l = lang('Offline');
$solar_l= lang('Solar Radiation');
$uvi_l  = lang('Ultraviolet');
$brght_l= lang('Brightness');
$lux_l  = lang('Lux');
$index_l= lang('Index');
$off_l  = lang('Offline');
#
# ----------- values to be used: both  wf  darksky false
switch ($uvsolarsensors)  { 
    case 'darksky' :
        $scrpt          = 'fct_darksky_shared.php';
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
        include_once $scrpt;
        if (isset ($darkskyhourlyuv))
             { $weather['uv']  = (float) $darkskyhourlyuv;
                if ( ($fcts_refresh + $darkskycurTime) < time() )
                     {  $onl_txt_uvsol  = '<b class="PWS_offline"> '.$online.lang('Offline').' </b>'; }
                else {  $onl_txt_uvsol  = '<b class="PWS_online"> ' .$online.set_my_time_lng($darkskycurTime,true).' </b>' ;} 
             }
        break;
    case 'both': 
        $onl_txt_uvsol  = $online_txt_ld;
        break;   
    case 'wf':
        if ($weatherflowoption == true )
             {  if (time() - $weatherflow['time'] > 600)
                        { $onl_txt_uvsol = '<b class="PWS_offline"> '.$online.$off_l .' </b>'; }
                else    { $onl_txt_uvsol = '<b class="PWS_online"> ' .$online.set_my_time_lng($weatherflow['time'],true).' </b>'; } 
                $weather['solar']  = $weatherflow['solar']  ;
                $weather['uv']     = $weatherflow['uv']     ;
                $weather['lux']    = $weatherflow['lux']    ; 
                break;} // eo weatherflow option
     default:
        echo '<br />'.lang('No valid sensors found'); 
        return;
} // eo switch
#
$b_clrs['maroon']       = 'rgb(208, 80, 65)';
$b_clrs['purple']       = '#916392';
$b_clrs['red']          = '#f37867';
$b_clrs['orange']       = '#ff8841';
$b_clrs['green']        = '#9aba2f';
$b_clrs['yellow']       = '#ecb454'; 
$b_clrs['blue']         = '#01a4b4';
#
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
#
$lvl_sol= array(1000,900,800,700,600,500,400,300,200,100,50,0);
$lvl_lux= array(110000,90000,80000,70000,60000,50000,40000,30000,20000,10000,5000,0);

$fll    = array('maroon', 'maroon','purple', 'purple', 'red', 'red', 
                'orange', 'yellow', 'yellow', 'green', 'green', 'green');
                       
$svg_start      = '<svg opacity="0.8" width="60px" height="100px" viewBox="0 0 44 84">
  <path fill="currentcolor" opacity="0.8" d="M 1.958 8.008 C 3.288 8.018 2.67 8 4 8.01 C 4.01 31.34 3.99 54.67 4 77.99 C 16 78.01 28 78 40 78 C 40.01 54.67 39.99 31.34 40 8.01 C 41.34 8 40.708 8.031 42.038 8.021 C 42.038 8.021 42 56.68 42 80 C 28.67 80.01 15.34 80 2.01 80 C 1.99 56.7 1.958 8.008 1.958 8.008 Z"/>'.PHP_EOL;
$svg_end        = '</svg>'.PHP_EOL;
#
if (!function_exists ('fill_bucket') ) {
function fill_bucket($value, $lvl, $colors )
     {  global $d, $b_clrs, $clr; 
        $string = $clr = '';
        for ($i = 0; $i < 12; $i++)
             {  if ($value <  $lvl[$i]) 
                     {  $clr1   =  'currentcolor';}
                else {  $key    = $colors[$i];
                        $clr1   = $b_clrs[$key];
                        if ($clr == '') {$clr = $clr1;}
                      } 
                $string .= ' <path fill="'.$clr1.'" opacity="1.0" '.$d[$i].PHP_EOL;
                } 
        return $string;
        } // eo fill_bucket                
}
#
# ----------------------------------- for testing only 
#$weather["solar"] = 0 ;
#$weather["uv"] = 0 ;
#$weather['lux'] = 50000;  
# ----------------------------------- for testing only 
#
#  values to be used
$sol_lvl        = $weather['solar'];
$lux_lvl        = $weather['lux'];
$uv_lvl         = (int) $weather['uv'];
$uv_img         = $uv_lvl;
if ($uv_img > 11) {$uv_img      = 11;} 

#
# ---------------  generate html
#
# ------------    date time of last data
echo '<div class="PWS_ol_time">'.$onl_txt_uvsol.'</div>'.PHP_EOL;
#
# ------------- the block itself
echo '<div class="PWS_module_content"><br />'.PHP_EOL;
#
# ----------------   left column
echo '<!-- left values -->
<div class="PWS_left">'.PHP_EOL;
echo '<div class="PWS_div_right" style=" border-left-width: 1px;">'.PHP_EOL;
echo $solar_l.'<br /><b>'.$sol_lvl.' W/m² </b></div>'.PHP_EOL; 
echo '<div class="PWS_bar">'.PHP_EOL;
echo $svg_start;
echo fill_bucket($sol_lvl,$lvl_sol, $fll);
echo $svg_end;
echo '</div>'.PHP_EOL;


echo '</div><!-- END of left values -->'.PHP_EOL;       
#
# ----------------  middle area
echo '<!-- middle part  -->
<div class="PWS_middle">'.PHP_EOL;
echo '<div class="PWS_div_right" style=" width: 70px; border-left-width: 1px;">'.PHP_EOL;
echo $uvi_l.'<br /><b>'.$weather['uv'].' '.$index_l.'</b></div>'.PHP_EOL;
echo '<div class="PWS_bar">'.PHP_EOL;
echo ' <svg id="pyramid_module" width="100px" height="100px" viewBox="0 0 980 792">'.PHP_EOL;
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
$fll_uv[10]  = $b_clrs['red'];
$fll_uv[11]  = $b_clrs['maroon'];
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
     {  if ($uv_img >=  $i )  {$color = $fll_uv[$i];} else {$color = 'currentcolor';;};
        echo '<path fill="'.$color.'" '.$d2[$i].PHP_EOL;}
echo '</svg>'.PHP_EOL;
if ($uv_lvl > 0 ) {
        echo '
<!-- uv-index circle -->
<div class="PWS_round" style ="position: absolute; top: 116px; left: 140px; background-color: '.$fll_uv[$uv_img].';
    height: 30px; width: 30px; 
    color: #fff;
    line-height: 10px;
    font-size: 24px;
    font-family: Helvetica,sans-seriff;
    border: 1px solid #FFFFFF;
    font-weight: 400;">
<br />'.$uv_lvl.'
</div>  


'.PHP_EOL;}
echo '</div>
</div>
<!-- END of middle part  -->'.PHP_EOL;
#
# ---------------- right column
echo '<!-- right values -->
<div class="PWS_right">'.PHP_EOL;
echo '<div class="PWS_div_right" style=" border-left-width: 1px;">'.PHP_EOL;
echo $brght_l.'<br /><b>'.$lux_lvl.' '.$lux_l.'</b></div>'.PHP_EOL; 
echo '<div class="PWS_bar">'.PHP_EOL;
echo $svg_start;
echo fill_bucket($lux_lvl,$lvl_lux, $fll);
echo $svg_end;
echo '</div>'.PHP_EOL;
echo '</div><!-- END of right values -->'.PHP_EOL; 
#
# ----------------   end of PWS_module_content
echo '</div>'.PHP_EOL;
# ----------------   end of html
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
