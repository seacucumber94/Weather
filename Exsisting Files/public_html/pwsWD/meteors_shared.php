<?php  $scrpt_vrsn_dt  = 'meteors_shared.php|01|2020-11-02|';  # release 2012_lts
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
#------------------  EO display source of script
#
# ------------------------- translation of texts
$ltxt_from      = lang('Visible');
$ltxt_peak      = lang('Peak');
$meteor_default = lang('No Active Sky Events');
$mtr_mtr        = lang('Meteor Showers');

$ltxt_solar     = lang('Solar').' '.lang('Eclipse');
$ltxt_lunar     = lang('Lunar').' '.lang('Eclipse');
$ltxt_annular   = lang('Annular');
$ltxt_total     = lang('Total');
$ltxt_partial   = lang('Partial');

$events         = array();
#-----------------------------------------------
#                                      functions
#-----------------------------------------------
#                       add_event
#
function add_event ($y, $m, $d, $nr, $txt, $nextYear = false, $py='', $pm='', $pd='', $pnr='') {
        global $events; 
        $key            = mktime(0, 0, 0, $m, $d, $y);  // event starts at, in seconds
        $until          = $key + ($nr * 24 * 3600);       // number of daysin seconds added to startitme 
# peak available
        if ($pm <> '') {
                $peakfrom       = mktime(0, 0, 0, $pm, $pd, $py); // event starts at, in seconds
                $peakto         = $peakfrom + ($pnr * 24 * 3600);
                $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => true, 'peakfrom' => $peakfrom,    'peakto'  => $peakto);} 
        else {  $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => false);}

        if ($nextYear <> true) { return;}
        $key            = mktime(0, 0, 0, $m, $d, $y+1);  // event starts at, in seconds
        $until          = $key + ($nr * 24 * 3600);       // number of daysin seconds added to startitme 
        if ($pm <> '') {
                $peakfrom       = mktime(0, 0, 0, $pm, $pd, $py+1); // event starts at, in seconds
                $peakto         = $peakfrom + ($pnr * 24 * 3600);
                $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => true, 'peakfrom' => $peakfrom,    'peakto'  => $peakto);} 
        else {  $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => false);}
        $key            = mktime(0, 0, 0, $m, $d, $y-1);  // event starts at, in seconds
        $until          = $key + ($nr * 24 * 3600);       // number of daysin seconds added to startitme 
        $events[$key]   = array ('until' => $until,    'title'  => $txt);
        if ($pm <> '') {
                $peakfrom       = mktime(0, 0, 0, $pm, $pd, $py-1); // event starts at, in seconds
                $peakto         = $peakfrom + ($pnr * 24 * 3600);
                $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => true, 'peakfrom' => $peakfrom,    'peakto'  => $peakto);} 
        else {  $events[$key]   = array ('until' => $until,    'title'  => $txt, 'peak' => false);}
#        echo '<pre>'.print_r($events,true); exit;
} // eo func add_event
#
#-----------------------------------------------
#                     arrays to drive the script
#-----------------------------------------------
/*
# ---------------------------- sun eclipses
add_event ( 2019, 12, 26, 1, $ltxt_annular .' '.$ltxt_solar);
add_event ( 2020, 06, 21, 1, $ltxt_annular .' '.$ltxt_solar);
add_event ( 2020, 12, 14, 1, $ltxt_total   .' '.$ltxt_solar);
add_event ( 2021, 06, 10, 1, $ltxt_annular .' '.$ltxt_solar);
add_event ( 2021, 12, 04, 1, $ltxt_total   .' '.$ltxt_solar);
#
# ---------------------------- moon eclipses
add_event ( 2020, 01, 10, 1, $ltxt_partial .' '.$ltxt_lunar);
add_event ( 2020, 06, 05, 1, $ltxt_partial .' '.$ltxt_lunar);
add_event ( 2020, 07, 05, 1, $ltxt_partial .' '.$ltxt_lunar);
add_event ( 2020, 11, 30, 1, $ltxt_partial .' '.$ltxt_lunar);
add_event ( 2021, 05, 26, 1, $ltxt_total   .' '.$ltxt_lunar);  
*/
# ---------------------------- Meteor Showers
$year   = date ('Y'); 
add_event ( $year, 12, 28, 15, 'Quadrantids',           true, $year+1, 1,  3, 2); 
add_event ( $year,  4, 16, 15, 'Lyrids',                true, $year, 4, 21, 2);
add_event ( $year,  4, 19, 40, 'eta Aquariids',         true, $year, 5,  5, 2); # add_event ( $year,  5,  5,  2, 'eta Aquariids peak',    true);
add_event ( $year,  7, 12, 41, 'Delta Aquariids',       true, $year, 7, 29, 2); # add_event ( $year,  7, 29,  2, 'Delta Aquariids peak',  true);
add_event ( $year,  7,  3, 43, 'alpha Capricornids',    true, $year, 7, 29, 2); # add_event ( $year,  7, 29,  2, 'alpha Capricornids peak',true);
add_event ( $year,  7, 17, 40, 'Perseids',              true, $year, 8, 12, 2); # add_event ( $year,  8, 12,  2, 'Perseids peak',         true);
add_event ( $year,  9, 10, 71, 'Southern Taurids',      true, $year,10,  9, 2); # add_event ( $year, 10,  9,  2, 'Southern Taurids peak', true);
add_event ( $year, 10,  2, 37, 'Orionids',              true, $year,10, 21, 2); # add_event ( $year, 10, 21,  2, 'Orionids peak',         true);
add_event ( $year, 10, 20, 51, 'Northern Taurids',      true, $year,11, 11, 2); # add_event ( $year, 11, 11,  2, 'Northern Taurids peak', true);
add_event ( $year, 11,  6, 25, 'Leonids',               true, $year,11, 16, 2); # add_event ( $year, 11, 16,  2, 'Leonids peak',          true);
add_event ( $year, 12,  4, 14, 'Geminids',              true, $year,12, 13, 2); # add_event ( $year, 12, 13,  2, 'Geminids peak',         true);
add_event ( $year, 12, 17, 10, 'Ursids',                true, $year,12, 21, 2); # add_event ( $year, 12, 21,  2, 'Ursids peak',           true);
ksort ($events);  
#
$mtr_links      = array();
$mtr_links['Quadrantids']       = 'https://en.wikipedia.org/wiki/Quadrantids';
$mtr_links['Lyrids']            = 'https://en.wikipedia.org/wiki/Lyrids';
$mtr_links['eta Aquariids']     = 'https://en.wikipedia.org/wiki/Eta_Aquariids';
$mtr_links['Delta Aquariids']   = 'https://en.wikipedia.org/wiki/Southern_Delta_Aquariids';
$mtr_links['alpha Capricornids']= 'https://en.wikipedia.org/wiki/Alpha_Capricornids';
$mtr_links['Perseids']          = 'https://en.wikipedia.org/wiki/Perseids';
$mtr_links['Southern Taurids']  = 'https://en.wikipedia.org/wiki/Southern_Taurids';
$mtr_links['Orionids']          = 'https://en.wikipedia.org/wiki/Orionids';
$mtr_links['Northern Taurids']  = 'https://en.wikipedia.org/wiki/Northern_Taurids';
$mtr_links['Leonids']           = 'https://en.wikipedia.org/wiki/Leonids';
$mtr_links['Geminids']          = 'https://en.wikipedia.org/wiki/Geminids';
$mtr_links['Ursids']            = 'https://en.wikipedia.org/wiki/Ursids';
#
#echo '<pre>'.print_r($events,true); #exit;
$now    = time();  
# ----- for testing only 
#$now = strtotime ('20190811T000000');
# ----- for testing only 
$next   = $now + 40*24*3600; 
$crrnt_string   = '';
$nxt_string     = '';
$guide_string   = '';
$ths_year_strt  = mktime(0,   0,  0,  1,  1, $year);
$ths_year_end   = mktime(23, 59, 59, 12, 31, $year);
$break          = '';
$cnt_now        = $cnt_nxt = 0;
$meteor_link_blck      = '';
foreach ($events as $from => $event) {
        $title  = $event['title'];
        if (!array_key_exists ( $title , $mtr_links )  ) {continue;}
        $until  = $event['until'];
        $from_text      = lang(date ('M ',$from))  .' '  .date ('j',$from);
        $until_text     = lang(date ('M ',$until)) .' '  .date ('j',$until);
        if     ($now > $from    && $now  < $until && $cnt_now < 3) {
                if ($meteor_link_blck  == '') {  // one link only save for block swcripts
                        $meteor_link_blck  = '<a target="_blank" href="'.$mtr_links[$title].'"><u class="orange">'.$event['title'].'</u></a>';}
                $crrnt_string   .= $mtr_mtr.': <a target="_blank" href="'.$mtr_links[$title].'"><u class="orange">'.$event['title'].'</u></a>'
                                .'<small><br />'.$ltxt_from.' <span class="orange">'.$from_text.' - '.$until_text.'</span>';
                if ($event['peak'] == true && $event['peakto'] >= $now ) { # echo '<pre>'.print_r($event,true); exit;
                        $crrnt_string  .=  '&nbsp;&nbsp;&nbsp;<b class="green">'.$ltxt_peak.' '.lang(date ('M ',$event['peakfrom']))  .' '  .date ('j',$event['peakfrom']);
                        if ($event['peakfrom'] <> $event['peakto']) { 
                                $crrnt_string  .=  ' > ';
                                $gmonth = date ('M ',$event['peakfrom']);
                                $gday   = date ('j',$event['peakfrom']);
                                $gmonthT= date ('M ',$event['peakto']);
                                $gdayT  = date ('j',$event['peakto']);
                                if ($gmonth <> $gmonthT) {$crrnt_string  .=  lang($gmonthT).' ';}
                                $crrnt_string  .=  $gdayT.' ';    
                        }  // eo multiple days peak
                        $crrnt_string  .= '</b>';
                }  // eo peak                 
                $crrnt_string   .='</small><br />'.PHP_EOL;
                $cnt_now++;
        }
        elseif ($until > $now && $from > $now && $cnt_nxt < 3) {
                $nxt_string    .=$mtr_mtr.': <a target="_blank" href="'.$mtr_links[$title].'"><u class="orange">'.$event['title'].'</u></a>'
                                .'<small><br />'.$ltxt_from.' <span class="orange">'.$from_text.' - '.$until_text.'</span>';
                if ($event['peak'] == true) {
                        $nxt_string    .=  '&nbsp;&nbsp;&nbsp;'.$ltxt_peak.' <span class="orange">'.lang(date ('M ',$event['peakfrom']))  .' '  .date ('j',$event['peakfrom']);
                        if ($event['peakfrom'] <> $event['peakto']) { 
                                $nxt_string  .=  ' > ';
                                $gmonth = date ('M ',$event['peakfrom']);
                                $gday   = date ('j',$event['peakfrom']);
                                $gmonthT= date ('M ',$event['peakto']);
                                $gdayT  = date ('j',$event['peakto']);
                                if ($gmonth <> $gmonthT) {$nxt_string  .=  lang($gmonthT).' ';}
                                $nxt_string  .=  $gdayT.' ';    
                        }  // eo multiple days peak
                }    // eo peak               
                $nxt_string    .= '</span></small><br />'.PHP_EOL;
                $cnt_nxt++;
        }
        if ($until >= $ths_year_strt &&  $until <= $ths_year_end) {
                $guide_string  .=  '<tr><td>'.$from_text.' > '.$until_text.'</td><td><a target="_blank" href="'.$mtr_links[$title].'"><u class="orange">'.$title.'</u></a></td><td>';
                if ($event['peak'] == true) {
                        $guide_string  .=  ' &uarr; '.lang(date ('M ',$event['peakfrom']))  .' '  .date ('j',$event['peakfrom']);
                        if ($event['peakfrom'] <> $event['peakto']) { 
                                $guide_string  .=  ' > ';
                                $gmonth = date ('M ',$event['peakfrom']);
                                $gday   = date ('j',$event['peakfrom']);
                                $gmonthT= date ('M ',$event['peakto']);
                                $gdayT  = date ('j',$event['peakto']);
                                if ($gmonth <> $gmonthT) {$guide_string  .=  lang($gmonthT).' ';}
                                $guide_string  .=  $gdayT.' ';    
                        }  // eo multiple days peak
                } // eo peak
                $guide_string  .= '</td></tr>'.PHP_EOL;        
        }   
}// eo for each
if ($crrnt_string == '') {$crrnt_string =  $meteor_default;}
return;
