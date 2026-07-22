<?php 
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
# first image can be used as background in block or in pop-up
# use as webcamimg  '_my_settings/imgcurl.php'
$image_url_sml  = 'http://xxxxxxx:yyyyy@xxxx.com:9999/auto.jpg';
#
# If we need a large image specify a second image only to be used in popup
# use as webcamimg  '_my_settings/imgcurl.php?large'
$image_url_xxl  = 'http://xxxxxxx:yyyyy@xxxx.com:9999/auto.jpg';
#
# allowed age of image in seconds
$max_age        = 520; 
#
$messages       = '';
#
$show_url       = true;  // report errors and include the URL
$show_url       = false;
#
#-----------------------------------------------
#
if (isset ($_REQUEST['large']) ) 
     {  $image_file     = 'camp_xxl.jpg';
        $image_url      = $image_url_xxl;}
#
else {  $image_file     = 'camp_sml.jpg';
        $image_url      = $image_url_sml;}
#
$diff = 99999;
if (file_exists ($image_file) )
        $diff   = time () - filemtime ($image_file);
if ($diff  > $max_age )                 // file to old or not exists
     {  $return = download_image1($image_url, $image_file); 
        if ($return === false)          // Load failed
             {  echo '<pre>'.$messages.'</pre>';  return;}
        }
#
header('Content-type: image/jpeg; charset=UTF-8');
readfile($image_file);
return;
#-----------------------------------------------
#
#              NOTHING TO CHANGE BELOW THIS LINE
#-----------------------------------------------
function download_image1($image_url, $image_file)
     {  global $messages, $show_url;
        $start_time     =  microtime(true);
        $fp             = fopen ($image_file.'tmp', 'w+');              // open file handle
        $ch             = curl_init($image_url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);       // enable if you want
        curl_setopt ($ch, CURLOPT_FILE, $fp);                   // output to file
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 1000);               // some large value to allow curl to run for a long time
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0 PaleMoon/12.0'); #### 2020-09-30
        curl_exec   ($ch);
        $info	        = curl_getinfo($ch);
        $error          = curl_error($ch);
        curl_close ($ch);                                       // closing curl handle
        fclose ($fp);                                           // closing file handle
        $now            = microtime(true);
        $passed         = $now - $start_time;
        if ($show_url  == false) { $image_url = '_url_not_shown_'; }
        if ($passed < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($passed,4);}
        if ($error <> '')
             {  $messages .= basename(__FILE__).' ('.__LINE__.') '.$image_file.': time spent: '.$string1.' -  invalid CURL '.$error.' - url '.$image_url.PHP_EOL;         
                return false;}
#
        $CHECK_HTTP_CODES = array ('304','404', '429','502', '500');
        if (in_array ($info['http_code'],$CHECK_HTTP_CODES) ) 
             {  $messages .= basename(__FILE__).' ('.__LINE__.') '.$image_file.': time spent: '.$string1.' - PROBLEM => http_code: '.$info['http_code'].', no valid data - url '.$image_url.PHP_EOL;    
                return false;} 
        rename ($image_file.'tmp', $image_file);
        return true;
}
#-----------------------------------------------
