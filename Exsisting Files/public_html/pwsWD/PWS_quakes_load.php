<?php $scrpt_vrsn_dt  = 'PWS_quakes_load.php|01|2023-10-31|';   # empty data + new link + download + extra test + validity test | extra spaces | release 2012_lts
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
        $filenameReal = __FILE__;    #               display source of script if requested so
        $download_size = filesize($filenameReal);
        header('Pragma: public');
        header('Cache-Control: private');
        header('Cache-Control: no-cache, must-revalidate');
        header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
        header("Content-Length: $download_size");
        header('Connection: close');
        readfile($filenameReal);
        return;}

$url            = 'https://www.seismicportal.eu/fdsnws/event/1/query?limit=30&format=json'; #
$allowed_age    = 2*60;
$cache          = './jsondata/earthquakeJS.json';

if (file_exists($cache) && !array_key_exists ('force',$_REQUEST) 
  && time() - filemtime($cache) <  $allowed_age ) 
     {  if(! array_key_exists('json',$_REQUEST) ) { die ("all OK");}
        $data   = file_get_contents($cache);
        echo $data.'                 ';
        return; }
#
if ( 1 == 2) {  $raw_data = file_get_contents('./jsondata/eqtestJS.json'); }
else {  #
        $ch     = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);    // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,5);           // data timeout 
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0'); 
        $raw_data       = curl_exec ($ch);  
        $info	        = curl_getinfo($ch);
        $error          = curl_error($ch);
        curl_close ($ch);
        if ( array_key_exists ('force',$_REQUEST) )
             {  echo '<pre>'.PHP_EOL.'$error'.__LINE__.'= '.$error
                .PHP_EOL.'$info = '.print_r($info,true)
                .PHP_EOL.'$raw_data = '.PHP_EOL.$raw_data
                .PHP_EOL.'</pre>'; }      
} // eo CURL
#
$levels =  array (4,5,6,7,8,99999999);
$texts  =  array ('Minor','Light','Moderate','Strong','Major','Great');
$dataOK = ''; 
#
$array  = json_decode($raw_data,TRUE); #echo '<pre>'.__LINE__.' '.print_r($array,true); exit;
if ( $array === false || !is_array($array) || !array_key_exists ('features',$array) || !is_array($array['features'] ) )  # 2023-10-06
     {  if (!array_key_exists('json',$_REQUEST) ) 
             {  die (__LINE__.' use old version ');}
        echo  file_get_contents($cache).'                 ';
        return;} 
#  
# data checked, should be OK. although it can be ampty also
$new_arr= array();
foreach ($array['features'] as $quake)                       
     {  $arr    = array();   #echo '<pre>'.__LINE__.' '.print_r($quake,true); exit;
        if (!array_key_exists('properties',$quake) 
         || !is_array($quake['properties']) )  { continue; }
        if (!array_key_exists('geometry',$quake) 
         || !is_array($quake['geometry'])
         || !array_key_exists('coordinates',$quake['geometry'] ) 
         || !is_array($quake['geometry']['coordinates']) ) { continue; } #  echo '<pre>'.__LINE__.' '.print_r($quake['geometry']['coordinates'],true); exit;        
        $prop   = $quake['properties'];
        $chck   = 'mag';
        if (!array_key_exists ($chck,$prop)) {  $rslt   = 'n/a';} else {$rslt   = $prop[$chck];}
        $magn   = $rslt;
        foreach ($levels as $key => $level)
                { if ( (float) ($magn ) < $level) {$text = $texts[$key]; break;}
                  }
        $arr['magnitude']       = $magn; 
#                                                                           
        $chck   = 'time';
        if (!array_key_exists ($chck,$prop)) {  $rslt   = 'n/a';} else {$rslt   = $prop[$chck];}
        $arr['date_time']       = $rslt;
        $unix                   = strtotime($rslt);
#             
        $chck   = 'flynn_region';
        if (!array_key_exists ($chck,$prop)) {  $rslt   = 'n/a';} else {$rslt   = $prop[$chck];}
        $locat  = $rslt; 
        $arr['location']        = $locat;
        $arr['title']           = $text. ' earthquake - '.$locat.' - '.gmdate ('F j, Y',$unix); 
#
        $chck   = 'depth';
        if (!array_key_exists ($chck,$prop)) {  $rslt   = 'n/a';} else {$rslt   = $prop[$chck];}
        $depth  = $rslt;
        $arr['depth']           = $depth;
#
        $crds   = $quake['geometry']['coordinates'];
        $arr['latitude']        = $crds[1];
        $arr['longitude']       = $crds[0];
        
        $arr['link']            = 'https://seismicportal.eu/eventdetails.html?unid='.$quake['id'];
        
        $chck   = 'source_id';   $chck   = 'not_used';
        if (array_key_exists ($chck,$prop))
             {  $string = $prop[$chck];         # https://static1.emsc.eu/Images/EVID_V2/152/1527/1527701/1527701.regional.jpg
                $arr['link']            = 'https://static1.emsc.eu/Images/EVID_V2/'
                        .substr ($string,0,3).'/'               #  152/
                        .substr ($string,0,4).'/'               #  1527/
                        .$string.'/'.$string.'.regional.jpg'; } #  1527701/1527701.regional.jpg';
        #
        $new_arr[]      = $arr;  #echo '<pre>'.__LINE__.' '.print_r($arr,true).print_r($quake,true); exit;
        }
if (count ($new_arr) < 10)
     {  if(! array_key_exists('json',$_REQUEST) ) { die (__LINE__." use old version not enough data");}
        $data   = file_get_contents($cache);
        echo $data.'                 ';
        return;} 
#
$data   = json_encode ($new_arr);
file_put_contents ($cache, $data);
if(! array_key_exists('json',$_REQUEST) ) { die ("succes, saved");}
echo $data.'                 ';
return;
