<?php  $scrpt_vrsn_dt  = 'PWS_cron_stationcron.php|01|2023-07-14|';  # nocache lightning + last rain rain BETA time error DB graphs + 9:00 roll + lightning save + last rain | release 2012_lts
#-----------------------------------------------
#                       settings for this script
#-----------------------------------------------
#            should the cron do a password check
#
$use_password           = false; // true => password needed  false => no pw check
#
if (isset ($hr_rll_vr)) { $hr_rll_vr = (int) $hr_rll_vr;} else {$hr_rll_vr = 0;}
#-----------------------------------------------
#                                  test settings
#$hr_rll_vr = 9;
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
   header('Content-type: text/plain; charset=UTF-8');
   header('Cache-Control: no-cache, must-revalidate');
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
header('Cache-Control: no-cache, no-store, must-revalidate');   # 2023-06-25
header('Pragma: no-cache');                                     # 2023-06-25
header('Expires: 0');                                           # 2023-06-25
if (!isset ($_REQUEST['test']))                                 # 2023-06-25
     {  ini_set('display_errors', 0);   error_reporting(0);}
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/plain; charset=UTF-8');
#-----------------------------------------------
$stck_lst       = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#-----------------------------------------------
$remove = array ('lang','units');       // 2022-04-30   bad bot or cron setup error
foreach ($remove as $key)
     {  if (array_key_exists($key, $_REQUEST) ) 
             {  unset ($_REQUEST[$key]);}
        } #print_r($_REQUEST); exit;
#-----------------------------------------------
#                      which tasks should be run
$load_current_data      = true; // data needed for update history and own graphs data
$update_history         = true; // update  history.txt with current high-low values
$upload_to_others       = true; // upload to Awekas / pwsweather a.o. if the weather=program / net does not support that 
$log_current_errors     = false;// set to true for testing if you want a log invalid data to a file ! can grow to  a big file 
#                                   other settings
# following three fields assume correctly that roll-over is at 00:00
# the compare field $hhmm is also calculated if rollover occurs at 00:00
$no_data_after          = 2355; // Hour-minutes after wich no day data should be processed in history
$no_data_before         = 0005; // Same before
$no_roll_after          = 0100; // No roll over after 1 hour
#
$minimum_age            = 6*3600;  // before a roll-over the files hould be at least this old   ######## 2021-05-11 23*3600  2021-05-25 6*3600
#
$weather_charts_folder  = __DIR__.'/chartsmydata/';
$weather_history_folder = __DIR__.'/_my_settings/'; 
#-----------------------------------------------
#                      load easyweather settings 
$scrpt          = 'PWS_settings.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#-----------------------------------------------
#
$NOW_TIME               = time();
$month_first_day        = (int) date ('j' ,$NOW_TIME+3600);  // daynr 1-31
$year_first_day         = (int) date ('nj',$NOW_TIME+3600); // month 1-12 + daynr 1-31
$blok_period_hour       = $NOW_TIME - ($hr_rll_vr * 3600);
$hhmm                   = (int) date ('Gi',$blok_period_hour); # echo __LINE__.' '.date ('c',$blok_period_hour).' '.$hhmm; exit; ###################   #### 2021-01-27
#-----------------------------------------------
#            default values for missing settings
#                    optional set in easyweather
if (!isset ($cron_use_password) ) 
     {  $cron_use_password = false;} 
if (!isset ($use_history) ) 
     {  $use_history    = true;}
#-----------------------------------------------
#                        optional password check
if ($use_password <> false ||  $cron_use_password <> false )
     {  $getpw          = '';
        if (array_key_exists('pw', $_REQUEST)) 
             {  $getpw = trim($_REQUEST['pw']);
                unset ($_REQUEST['pw']);}
        if ($getpw <> $password) 
            { die (__LINE__.': security error');}
} // eo password check
#-----------------------------------------------
# check if we use our 'own' data files for graphs 
if ($charts_from <> "DB") 
     {  $update_graph_files     = false;}
else {  $update_graph_files     = true; }   
#
#-----------------------------------------------
#   load files = basic task for the station-cron
$cron           = true; // files loaded with shorter refresh time, force default units
$scrpt          = 'PWS_load_files.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;
$stck_lst      = '';
echo 'success files loaded ';  
#-----------------------------------------------
#                 check if we use a history file
#       from easyweathersettings / settings file
if ($use_history == false)     
     {  $update_history = false;}
#-----------------------------------------------
#      check if in allowed timeframe for updates
$roll_over_new_day      = false;
#
if (    $hhmm  >= $no_data_after 
     || $hhmm  <= $no_data_before)  
     {  echo ' + no history / graph values updated around midnight'; 
        $update_history         = false;
        $update_graph_files     = false;   
        $roll_over_new_day      = true;}  #  echo __LINE__.$hhmm; exit; # $roll_over_new_day      = true;  # for testing;
#-----------------------------------------------
#
if (    $update_graph_files == false
     && $update_history     == false
     && $roll_over_new_day  == false)
     {  $load_current_data = false;}  // no need to load / process current data
#-----------------------------------------------
#                 do we need latest current data
if ($load_current_data == true)
     {  $scrpt          = 'PWS_livedata.php'; 
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
        include_once $scrpt;     #   echo '<pre>'.print_r($weather,true); exit;
#-----------------------------------------------
#                    first do some sanity checks
        $exists = array ('temp','humidity','barometer'); # ini_set('display_errors', 1); error_reporting(1);
        $dataOK = true;
        foreach ($exists as $check)  // check some always there items
             {  if (array_key_exists($check, $weather)) { continue;}
                $dataOK = false; }
#
        $values = array();   // check some often invalid values      
        $values[] = (float) $sql_temp  + (float) $sql_barometer ;
        $values[] = (float) $sql_temp  + (float) $sql_dewpoint ;
        foreach ($values as $value)
             {  if ( $value  <> 0 ) {continue;}  #### 2021-01-22
                $dataOK = false;}
        if ( (float) $sql_barometer === 0) #### 2021-01-22
             {  $dataOK = false;}   #### 2021-01-22
#
        if ($dataOK == false)
             {  echo ' +  invalid current data ';
                if ($log_current_errors) 
                    {   file_put_contents ('./errors.txt',date ('c',$NOW_TIME).PHP_EOL.$weather["loaded_from"].PHP_EOL,FILE_APPEND);}
                $update_graph_files = false;
                $update_history     = false;
                $load_current_data  = false;}
} // eo did we load the current data and it is correct data
#
#-----------------------------------------------
# do we use our own data files for graphs AND do we need to update the files
# we need to  generate the file for today  x times / hour
# we do not update the files around midnight
#
if (    $update_graph_files == true ) { 
# ------------------------ file names / location
        $weatherToday           = $weather_charts_folder.'today.txt';                
# all current values in original units to a string
        $string       = $sql_updated.','.$weather['temp'].','.$weather['barometer'].','.$weather['rain_today'].','.$weather['uv'].','.$weather['wind_gust_speed'].','
                        .$weather['wind_speed'].','.$weather['solar'].','.$weather['dewpoint'].','.$weather['rain_rate'].','.$weather['wind_direction'].','.$sql_date.','.$sql_lightning.','.PHP_EOL;                       
#
# no file (for instance after testing), also include  header line with field descriptions
        if (!file_exists ($weatherToday) )       # first time
             {  $daily_flds     = 'time, outsideTemp, barometer, raintoday, UV, windgust, windSpeed, radiation, dewpoint, rainrate, direction, date, lightning,'.PHP_EOL;
                $string         = $daily_flds. $string;
                echo ' + day file created';}
#  add 1 data line
        $rslt   = file_put_contents ($weatherToday,  $string, FILE_APPEND); 
        if ($rslt == false) 
             {  $stck_lst  .= basename(__FILE__).' ('.__LINE__.') Data could not be saved to '.$weatherToday.PHP_EOL;
                echo ' ! problems with saving data !';        }
        else {  $stck_lst  .= basename(__FILE__).' ('.__LINE__.') Data saved to '.$weatherToday.PHP_EOL;
                echo ' + data appended to day file ';}
#
} // eo save to own data to file  (= option  DB)
#
#-----------------------------------------------
#       do we need to update a history table 
if ($update_history == true) {
        $change = false;   
        $changed_items = '';       
        $loop   = array ('all','year','month','today');
        update_hist_LH ('temp', 'temp'); 
        update_hist_LH ('temp', 'temp_high'); 
        update_hist_LH ('temp', 'temp_low');  
        update_hist_LH ('dewp', 'dewpoint'); 
        update_hist_LH ('dewp', 'dewpoint_low');   #### maybe skip this, then also in history
        #update_hist_LH ('rain', 'rain_today');  
        update_hist_LH ('humd', 'humidity');
        update_hist_LH ('baro', 'barometer');
        update_hist_LH ('baro', 'barometer_max');   
        update_hist_LH ('baro', 'barometer_min'); 
        update_hist_LH ('wind', 'wind_speed');  
        update_hist_LH ('wind', 'wind_speed_max');
        update_hist_LH ('gust', 'wind_gust_speed');    
        update_hist_LH ('gust', 'wind_gust_speed_max'); 
        #
        if ($uvsolarsensors == 'both' || $uvsolarsensors == 'wf' || $uvsolarsensors == 'darksky') 
             {  update_hist_LH ('solr', 'solar');  
                update_hist_LH ('solr', 'solar_max');  
                if ($uvsolarsensors <> 'darksky') 
                     {  update_hist_LH ('uvuv', 'uv');  
                        update_hist_LH ('uvuv', 'uv_max');}
        } 
        #
        #
        if ((float) $hist['rain']['HghV']['today'] <  (float) $weather['rain_today']) #### 2021-01-15
             {  $changed_items .= __LINE__.' rain|HghV|today|'.$weather['rain_today'].'|'.$hist['rain']['HghV']['today'].'|'.PHP_EOL;
                $hist['rain']['HghV']['today'] = (float) $weather['rain_today'];  
                $hist['last_rained']    = $recordDate;
                $change = true;} 
        if (array_key_exists('lightningtime',$weather) ) 
             {  if (!array_key_exists('lightningtime',$hist) ) 
                     {  $hist['lightning']      = 
                        $hist['lightningkm']    = 
                        $hist['lightningmi']    = 
                        $hist['lightningtime']  = 0;}
                $check  = (int) $weather['lightningtime'];                      #### 2021-06-02  error after firmweare update ??
                if ( $check > $hist['lightningtime'] && $check <= time() )      #### 2021-06-02  error after firmweare update ??
                     {  $hist['lightning']      = $weather['lightning'];
                        $hist['lightningkm']    = $weather['lightningkm'];
                        $hist['lightningmi']    = $weather['lightningmi']; 
                        $hist['lightningtime']  = $weather['lightningtime']; 
                        $change = true;}
                } // save updated lightning
        #
        if ($change == true)  {  // check if there were any updates to the history 
                $echo =  ' + history recalculated ';
                $result = file_put_contents($weather_history_folder.'history.rnm', serialize($hist));
                if ($result > 0) 
                     {  rename ($weather_history_folder.'history.rnm', $weather_history_folder.'history.txt');
                        $echo .= ' + history saved '; }
                else {  $echo .=  'error writing temp file chartsmydata/history.rnm'; }
        }
        else {  $echo =  ' + history was already valid ';        #### 2021-01-23     echo $echo.print_r($hist,true); exit;
                touch ($weather_history_folder.'history.txt'); } #### 2021-05-30    
        if (isset ($_REQUEST['test'])) {
                echo $stck_lst;  
                $stck_lst = '';
                $echo .=  PHP_EOL.$changed_items.'$livedata='.$livedata.PHP_EOL.'$hist_file='.$hist_file.PHP_EOL;}
        echo $echo;
        if ($upload_to_others && file_exists (__DIR__.'/PWS_upload_to_others.php') ) 
             {  echo ' + need upload to others ';  
                include __DIR__.'/PWS_upload_to_others.php';}  #### 2021-03-22
        else {  echo ' + no upload to others '; }
} // eo update history file
#
#-----------------------------------------------
# after 23:50  < - > 0100 we have to do / check for  ONE roll-over
#
if ($roll_over_new_day == false 
     && $hhmm > $no_roll_after) { 
        echo ' + no roll-over needed '.date('c');       # 2023-06-25
        if (isset ($_REQUEST['test']) && $stck_lst <> '') {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';} 
        return;}
#
$history_updated= false;  // to later save the file when it was updated
$roll_day       = false;  // only when we use history and it was not updated short time ago
#-----------------------------------------------
#               check if a rollover is needed
if ($use_history == true) 
     {  echo PHP_EOL.'we should check for a roll-over for history';  # echo '<pre>'.print_r($hist, true);
        $last_roll_over = 0;
        if (array_key_exists('last_roll_over', $hist))
             {  $last_roll_over = $hist['last_roll_over'];} # echo __LINE__.' $last_roll_over= '.$last_roll_over; exit;
        $now    = $NOW_TIME;
        $diff   = $now - $last_roll_over;
        if ($diff > $minimum_age ) 
             {  $roll_day = true;
                echo ' => roll-over needed, last_roll_over was '.$diff.' seconds ago';}
        else {  echo ' => not needed, last_roll_over was '.$diff.' seconds ago at '.$hist['last_roll_over2'];} 
        } 
#-----------------------------------------------
#                           a rollover is needed
if ($roll_day == true ) 
     {  echo PHP_EOL.'doing a one day roll-over for history ';  # echo print_r($hist['temp'],true); 
        $wthr_types     = array ('temp','dewp','humd','baro','wind','gust','uvuv','solr');
        $types_values   = array ('HghV','HghV_D','HghV_D_T','LowV','LowV_D','LowV_D_T');
        $values_period  = array ('today','yday','month','year','all');
        foreach ($wthr_types as $type) {
                foreach ($types_values as $value) {
                        $hist[$type][$value]['yday']    = $hist[$type][$value]['today'];       
                        $hist[$type][$value]['today']   = 'n/a'; 
                } // eo foreach value
        } // eo foreach type
        $value                          = (float) $hist['rain']['HghV']['today']; 
        $hist['rain']['HghV']['yday']   = $value; 
        $hist['rain']['HghV']['month']  = $value + (float) $hist['rain']['HghV']['month'];
        $hist['rain']['HghV']['year']   = $value + (float) $hist['rain']['HghV']['year'];
        $hist['rain']['HghV']['all']    = $value + (float) $hist['rain']['HghV']['all'];
        $hist['rain']['HghV']['today']   = 0;
#
        $hist['last_roll_over'] = $now;
        $hist['last_roll_over2']= date('c',$now);
        $history_updated        = true;     # echo print_r($hist['temp'],true);    exit;
#-----------------------------------------------
#  check if we  need to reset month, year values
        $clear          = array();
        $text           = '';
        if ( $month_first_day == 1) {
                echo PHP_EOL.'doing a month-first clear for history ';  
                $text           = 'month ';
                $clear[0]       = 'month';} 
        if ( $year_first_day == 11) {  
                echo PHP_EOL.'doing a year-first clear for history ';  
                $text           = 'year ';
                $clear[1]       = 'year';} // January, 1
        if (count ($clear) > 0)
             {  echo PHP_EOL.'doing a one first roll-over for history ';
                $hist['last_first_day'] = $now;
                $hist['last_first_day2']= date('c',$now);
                
                foreach ($wthr_types as $type) {
                        foreach ($types_values as $value) {
                                foreach ($clear as $period) {
                                        $hist[$type][$value][$period]   = 'n/a'; 
                                } // eo foreach period
                        } // eo foreach value
                } // eo foreach type
                foreach ($clear as $period) {
                        $hist['rain']['HghV'][$period]  = 'n/a'; 
                }
                echo ' succes :  hist '.$text.'file cleared ';
        }  // eo clear fistdays
#-----------------------------------------------
#          save updated history  after roll-over   
        $result = file_put_contents($weather_history_folder.'history.rnm', serialize($hist));
        if ($result > 0) 
             {  rename ($weather_history_folder.'history.txt', $weather_history_folder.'history_old.txt');
                rename ($weather_history_folder.'history.rnm', $weather_history_folder.'history.txt');
                echo  ' + history saved after roll-over '; }
        else {  echo  'error writing temp file chartsmydata/history.rnm'; }
} // eo a rollover is needed
#-----------------------------------------------
#           do we need to update the graph files 
if ($charts_from <> "DB") 
     {  echo PHP_EOL.' No internal DB in use OK';
        if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        return;}
#if ($roll_day <> true)   ##################
#     {  echo PHP_EOL.' No DB update roll-over, only at roll-over-time'.PHP_EOL;}
echo PHP_EOL.'we should check for a roll-over of our graph files';
#
$now                    = $NOW_TIME + 3600;
$weatherfileyear        = $weather_charts_folder.date('Y',$now).'.txt';	
$weatherfilemonth       = $weather_charts_folder.date('Y_m',$now).'.txt';	
$weatherToday           = $weather_charts_folder.'today.txt';

if (!file_exists ($weatherToday))  
     {  if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        die ('PROBLEM: Daily  data file '.$weatherToday.'  does not exist yet. Script ends.');}

if (!file_exists ($weatherfileyear))  
     {  $file_time      = 0; } 
else {  $file_time      = filemtime($weatherfileyear);}
#
$diff                   = $NOW_TIME - $file_time; 
if ($diff < $minimum_age) # probably first incomplete day !
     {  echo ' => not needed, age = '.$diff.' seconds, filetime of /chartsmydata/'.date ('Y').'.txt = '.date('c',$file_time);
        if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        return;}
     {  echo PHP_EOL.' => roll-over needed, last_roll_over was '.$diff.' seconds ago'; }
#
$arr    = file($weatherToday);   // load daily file
$count  = COUNT($arr);
if ($count < 2)  
     {  if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        die ('PROBLEM: Daily  data file '.$weatherToday.'  is empty. Script ends.');}
#
# ----- init min-max fields
$MAX_outsideTemp=$MAX_dewpoint=$MAX_raintoday=$MAX_windgust=$MAX_windSpeed=$MAX_radiation=$MAX_uv = $MAX_barometer = -9000;
$MIN_outsideTemp=$MIN_dewpoint                                                                    = $MIN_barometer = +9000;
$SUM_lightning  = 0;
#
# process each data line and calculate min-max values
#
$daystart= false;
#
for ($n = 1; $n < $count; $n++)        // skip first line with header-texts.
      {  list ($time, $outsideTemp, $barometer, $raintoday, $UV, $windgust, $windSpeed, $radiation, $dewpoint, $rainrate, $direction, $date, $lightning)
                = explode (',',$arr[$n].',,,,,,,,,,, ');
        if (trim($time) == '') {continue;}
        if ($daystart == false) {
                list ($hr,$min) = explode (':',trim($time));
                if ( (int) $hr < 23 ) {$daystart= true; } else {continue;}
        }
        if ($outsideTemp> $MAX_outsideTemp)     {$MAX_outsideTemp = $outsideTemp; }
        if ($outsideTemp< $MIN_outsideTemp)     {$MIN_outsideTemp = $outsideTemp; }

        if ($dewpoint   > $MAX_dewpoint)        {$MAX_dewpoint    = $dewpoint; }
        if ($dewpoint   < $MIN_dewpoint)        {$MIN_dewpoint    = $dewpoint; }

        if ($raintoday  > $MAX_raintoday)       {$MAX_raintoday   = $raintoday;  }

        if ($windgust   > $MAX_windgust)        {$MAX_windgust    = $windgust;  }

        if ($windSpeed  > $MAX_windSpeed)       {$MAX_windSpeed   = $windSpeed;  }

        if ($radiation  > $MAX_radiation)       {$MAX_radiation   = $radiation;  }

        if ($UV         > $MAX_uv)              {$MAX_uv          = $UV;         }

        if ($barometer  > $MAX_barometer)       {$MAX_barometer   = $barometer;  }
        if ($barometer  < $MIN_barometer)       {$MIN_barometer   = $barometer;  }

        $SUM_lightning  = $SUM_lightning + (int) $lightning;
        }

# ------   construct headers for files
$daily_flds     = 'time, outsideTemp, barometer, raintoday, UV, windgust, windSpeed, radiation, dewpoint, rainrate, direction, date, lightning,'.PHP_EOL;
$m_y_flds       = 'date,MAX_outsideTemp,MIN_outsideTemp,MAX_dewpoint,MIN_dewpoint,MAX_raintoday,MAX_windgustmph,MAX_windSpeed,MAX_radiation,MAX_barometer,MIN_barometer,SUM_lightning,MAX_UV,'.PHP_EOL;
#    
$year_string    = $month_string = '';
if (!file_exists ($weatherfileyear))  { $year_string    = $m_y_flds;}
if (!file_exists ($weatherfilemonth)) { $month_string   = $m_y_flds;}
#
# ------   construct data line of todays min-max values  
#
$hr_db_roll     = $hr_rll_vr -1;                #### 2021-05-25  00 -> -1
if ($hr_db_roll < 0) {$hr_db_roll = $hr_db_roll + 24;}   #### 2021-05-25  -1  -> 23
#
$time_db_roll   = substr ('00'.$hr_db_roll,-2) ;   ####  2021-05-06 2021-05-25  
$data_string    = date ('Y-m-d',($NOW_TIME - 3600* ($hr_rll_vr + 1) ) )  ####  2021-05-11  set date to previous day on 09:00 roll over
                .' '.$time_db_roll.':59:05,'.$MAX_outsideTemp.','.$MIN_outsideTemp.','.$MAX_dewpoint.','.$MIN_dewpoint.','.$MAX_raintoday.','.$MAX_windgust.','.$MAX_windSpeed.','.$MAX_radiation.','.$MAX_barometer.','.$MIN_barometer.','.$SUM_lightning.','.$MAX_uv.','.PHP_EOL;#
#
# ----------------------  add max-min data to month & year files
# -------------------------  if files not exist they are created
$rslt   = file_put_contents ($weatherfilemonth,  $month_string.$data_string, FILE_APPEND); 
if ($rslt == false) 
     {  if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        die ('ERROR: Couldn not write data to '.$weatherfilemonth);}
#
$rslt   = file_put_contents ($weatherfileyear,  $year_string.$data_string, FILE_APPEND); 
if ($rslt == false) 
     {  if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        die ('ERROR: Couldn not write data to '.$weatherfileyear);}
#
# ------------------------------------  reset daily file
$rslt   = file_put_contents ($weatherToday,  $daily_flds); 
if ($rslt == false) 
     {  if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
        die ('PROBLEM: Couldn not clear and initialize daily file '.$weatherToday);}
#
if (isset ($_REQUEST['test'])) {echo '<pre>'.$stck_lst.'</pre>'; $stck_lst='';}
echo PHP_EOL.' succes :  totals of '.($count - 1).' daily records added to month/year files, daily file reset';
return;
#
#-----------------------------------------------
#             update_hist_LH  function to update 
#             history table with low high values
#-----------------------------------------------
function update_hist_LH ($type, $key) {  //
        global $change, $loop, $hist, $recordDate,  $sql_updated, $changed_items,$weather;
        if (!array_key_exists($key,$weather) ) 
             {  $changed_items .= __LINE__.' value not exists (yet): '.$key.PHP_EOL;
                return;}
        $value  = $weather[$key];
        if ( (string) $value === 'n/a' ) 
             {  $changed_items .= __LINE__.' skipped: '.$key.' '.$value.PHP_EOL;
                return;}
        foreach ($loop as $period) {
                if (!isset ($hist[$type]['HghV'][$period] ) )
                     {  $check  = -2000;}
                elseif ( (string)   $hist[$type]['HghV'][$period] === 'n/a' )
                     {  $check  = -2001; } 
                else {  $check  =  (float) $hist[$type]['HghV'][$period];}
#  if ($type == 'rain') {echo '$check='.$check.' $value='.$value; exit;}
                if ( ($check < $value)  && (string) $value <> '' && (string) $value <> 'n/a') 
                     {  $change = true; $changed_items .= __LINE__.' '.$type.'|HghV|'.$period.'|'.$value.'|'.$check.'|'.PHP_EOL;
                        $hist[$type]['HghV']    [$period] = $value; 
                        $hist[$type]['HghV_D']  [$period] = $recordDate; 
                        $hist[$type]['HghV_D_T'][$period] = $sql_updated; }
# if ($type == 'rain') {echo '$check='.$check.' $value='.$value.' $changed_items='.$changed_items; exit;}
#$hist[$type]['LowV'][$period] = 'n/a';
                if (!isset ($hist[$type]['LowV'][$period] ))
                     {  $check  =  2000;}
                elseif ( (string)   $hist[$type]['LowV'][$period] === 'n/a' )
                     {  # echo $type.'-'.'LowV'.$period.'-'.$hist[$type]['LowV'][$period].PHP_EOL;
                        $check  =  2001; } 
                else {  $check  =  (float) $hist[$type]['LowV'][$period];}
                if ($value <  $check && (string) $value <> '' && (string) $value <> 'n/a') 
                     {  $change = true; $changed_items .= __LINE__.' '.$type.'|LowV|'.$period.'|'.$value.'|'.$check.'|'.PHP_EOL;
                        $hist[$type]['LowV']    [$period] = $value; 
                        $hist[$type]['LowV_D']  [$period] = $recordDate; 
                        $hist[$type]['LowV_D_T'][$period] = $sql_updated; }
        } // eo foreach
} // eof update_hist_LH
