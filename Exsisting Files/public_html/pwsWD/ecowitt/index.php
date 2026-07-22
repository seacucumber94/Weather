<?php  $scrpt_vrsn_dt  = 'index.php|01|2023-09-09|';  # dateUTC + equal + extra fields and swapp release 2012_lts # ecowitt receive data script
# -----------------     SETTINGS for this script
$filename1      = './ecco_lcl.arr';     # filename for correct data, check the path
$always_exists  = 'tempf';              # this field should always be in the data ! 
$filter_value   = 0;                    # if above field contents equals the filter value the upload is rejected
#$filter_value   = false;               # remove comment mark if no contents checking is done on the field
#$always_exists  = array('tempf','baromabsin'); # remove comment mark to test more items
#$th_replace     = 1;                   # remove # and set to swap outside th  <> th?
#$swap_piezo     = false;               # leave piezo rain-values as is or no _piezo rain values
$test_mode      = false;                # set to true if you encounter strange problems 
#
header('Content-type: text/plain; charset=UTF-8');
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
#-----------------------------------------------
#      CREDIT - DO NOT REMOVE WITHOUT PERMISSION
#-----------------------------------------------
# ---------------------------------- Houskeeping
$message        = 'Ecowitt data received:'.PHP_EOL;;
$data_stored    = './ecco_test.arr';    # if upload not correct we store it here
$pass_k_stored  = './ecco_key.arr';     # the first passkey received is stored in this file; 
#
if (file_exists ($pass_k_stored)  )
     {  $passkey1 = file_get_contents ($pass_k_stored); }
else {  $passkey1 = false;}
$data_false     = $data_stored; 
$errors         = 0;  
#-----------------------------------------------                      
#            if passkey is found in post we need 
#              to check with expected passkey(s)
if (array_key_exists ('PASSKEY',$_POST) )      
     {  if     (base64_encode ($_POST['PASSKEY']) == $passkey1) # upload is allowed as passkey is correct. 
             {  $data_stored = $filename1;                      # set the file to store the upload
                unset ($_REQUEST['PASSKEY']);}                  # remove passkey from upload
#
        elseif (isset ($passkey2) || $_POST['PASSKEY'] == $passkey2)  # not yet implemented 
             {  $data_stored = $filename2; 
                unset ($_REQUEST['PASSKEY']);}
#
        elseif ($passkey1 == false)                             # we did not upload to thise webserver in the past
             {  $string = base64_encode ($_POST['PASSKEY']);    # encode passkey
                $result = file_put_contents ($pass_k_stored,$string);   # save in local file
                if ($result <> false)                           # check if save was correct
                     {  $message        .= ' OK-'.__LINE__.': Passkey stored '; }
                else {  $message        .= ' PROBLEM-'.__LINE__.': Folder not writable, could not store passkey  '; 
                        $test_mode      = true;}
                $data_stored = $filename1;
                unset ($_REQUEST['PASSKEY']);
                }
#
        else {  $message        .= ' OK-'.__LINE__.':  The string between =>  and <= is an invalid passkey =>'.trim($_POST['PASSKEY']).'<= .';
                $errors = 1;                    # incorrect passkey -> will be stored local, switch test-mode on
                $test_mode      = true;}
        } // eo check passkey(s)
#
#-----------------------------------------------                      
#            no passkey found =>  illegal upload 
#           or you are testing using the browser
else {  $message        .= ' Problem-'.__LINE__.': NO PASSKEY found. '; 
        $test_mode      = true;
        $errors         = 1;
        $always_exists  = ''; }
#
#-----------------------------------------------
#   check if data fields are there and are valid
#
if (!isset ($always_exists) )
     {  $always_exists = array();}
elseif (!is_array ($always_exists) )
     {  $always_exists = array ($always_exists); }
foreach ($always_exists as $check)
     {  if ( trim($check) <> '' 
          && !array_key_exists ($check,$_POST) )
             {  $message        .= PHP_EOL.' Problem-'.__LINE__.': Missing item in upload: '.$check;
                $errors++;
                continue;}
        elseif ($filter_value === false)  # 2023-07-08
             { continue;}
        $value  = trim($_POST[$check]);
        if (  $value == NULL
           || (string) $value == '' 
           || (float)  $value == $filter_value)      // empty data, check the log file regurly  
             {  $message        .= PHP_EOL.' Problem-'.__LINE__.': Item in upload: '.$check.' with value between next brackets =>'.$value.'<= is invalid';
                $errors++;} 
        } 
if ( $errors <> 0)
     {  $data_stored    = $data_false; # if upload not correct we store it here
        if (count ($_REQUEST) > 0) 
             {  $string = print_r($_REQUEST,true);
                $from   = array('Array','('.PHP_EOL,')'.PHP_EOL);
                $string = PHP_EOL.' Problem-'.__LINE__.': Invalid data: '.str_replace ($from, '',$string);}
#
        else {  $string = PHP_EOL.' Problem-'.__LINE__.': No data uploaded.';}
        $message        .= $string.PHP_EOL;
        unset ($_REQUEST['PASSKEY']);
        $test_mode      = true;
        }
#
$arr    = $_REQUEST;
#-----------------------------------------------
#                             reset main sensors
#  replace tempf / humidity with extra th sensor
#
if ($errors ==  0 && isset ($th_replace) &&  (int) $th_replace <> 0)
     {  $int    = (int) $th_replace;
        $tmp    = 'temp'.$int.'f';
        if (array_key_exists ('tempf',$arr) )  // main outside sensor
             {  $old    = $arr['tempf'];}
        else {  $old    = '---';}
        if (array_key_exists ($tmp,$arr) )      // check if temp?f exitsts
             {  $arr['tempf']   = $arr[$tmp];   // main temp replaced
                if ($old <> '---')
                     {  $arr[$tmp]      = $old;}
                }
        $hum    = 'humidity'.$int;
        if (array_key_exists ('humidity',$arr) ) // main outside hum sensor 
             {  $old    = $arr['humidity'];}
        else {  $old    = '---';}        
        if (array_key_exists ('humidity'.$int,$arr) )
             {  $arr['humidity']= $arr[$hum];
                if ($old <> '---')
                     {  $arr[$hum]      = $old; }  
                }    
        } // eo tempf / humidity  replace with extra th sensor
#
#----------------------------   test rain values
if (1 == 2) {
#        $fields = array('rainratein' ,'eventrainin','hourlyrainin','dailyrainin','weeklyrainin','monthlyrainin','yearlyrainin','totalrainin');
        $fields = array('rrain_piezo','erain_piezo','hrain_piezo' ,'drain_piezo','wrain_piezo' ,'mrain_piezo'  ,'yrain_piezo' ,'train_piezo'); 
        foreach ($fields as $rain_item)
             {  if (array_key_exists($rain_item,$arr)) {unset ($arr[$rain_item]);}
             }  // remove old rain values
} // eo test
# test
#$arr['rrain_piezo']  = $arr ['rainratein'] + 0.01;
#$arr['erain_piezo']  = $arr ['eventrainin'] + 0.002;
#$arr['drain_piezo']  = $arr ['dailyrainin'] + 1;
#-----------------------------------------------
#                               swap rain values 
# test
$piezo  = array('rrain_piezo','erain_piezo','hrain_piezo' ,'drain_piezo','wrain_piezo' ,'mrain_piezo'  ,'yrain_piezo' ,'train_piezo'); 
$common = array('rainratein' ,'eventrainin','hourlyrainin','dailyrainin','weeklyrainin','monthlyrainin','yearlyrainin','totalrainin');
if (!isset ($swap_piezo)) 
     {  $swap_piezo     = true;}
if ($swap_piezo <> false)       # 2022-03-08 problem removed
     {  foreach ($piezo as $n => $name)
             {  if (!array_key_exists($name,$arr) )     // no piezo name found
                     {  continue;}
                $key            = $common[$n];          // corresponding old name
                if (array_key_exists($key ,$arr) )      // old rain name / value found
                     {  $old    = $arr[$key];}
                else {  $old    = '---';}      
                $arr[$key]      = $arr[$name];  // old name with piezo value
                if ($old <> '---')
                     {  $arr[$key.'_pz']= $old; }
                unset ($arr[$name]);  
                } // eof each swap item
        } // eo swap test
#
# ----------------------------------------------
# url decode date  $arr['dateutc'] = '2023-08-30+10%3A41%3A11'; 
#$arr['dateutc'] = str_replace (':','%3A', $arr['dateutc']);
$arr['dateutc'] = urldecode ($arr['dateutc']);  # 2023-09-01 thanks @olicat
#-----------------------------------------------
#                        store all received data 
ksort($arr);
$return = file_put_contents ($data_stored,serialize($arr)); 
if ($return == false ) 
    {   $message        .=  ' Setup error-'.__LINE__.': Failed writing ecowitt data to '.$data_stored.' check line 4 for typing errors';
        $test_mode      = true;}
#
#-----------------------------------------------
#                       add all problem messages 
#       and unknown and invalid data to text-log  
if ($test_mode == true )          
     {  $sf = fopen('./ecco_stats.txt','a');// append to log file
        if($sf) 
             {  $time   = gmdate ('r');
                fwrite($sf,$time.' = '.$message."\n");
                fclose($sf);}
        echo $message;
        }
        
# When started in the browser for testing with ecowitt/test.php
# The following output will be produced
#
#       Ecowitt data received:
#         Problem-77: NO PASSKEY found. 
#
# All problems are stored in ecowitt/ecco_stats.txt
# You should check and clear that file frequently
#
