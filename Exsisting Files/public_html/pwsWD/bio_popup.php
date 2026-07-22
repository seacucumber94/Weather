<?php $scrpt_vrsn_dt  = 'bio_popup.php|01|2021-05-15|';  # http mail | close optional + email check | release 2012_lts
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
else {  ini_set('display_errors','On'); error_reporting(E_ALL);}  
header('Content-type: text/html; charset=UTF-8');
# -------------------save list of loaded scrips;
$stck_lst        = basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
# -------------------------------- load settings 
$scrpt          = 'PWS_settings.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
# -----------------------  general functions aso  
$scrpt          = 'PWS_shared.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt;   
#-----------------------------------------------
#                                script settings
#-----------------------------------------------
#$show_close_x   = false;       // the close X in the top left: default we use easyweather settings , set to false or true to override
#$show_close_x   = true;        // remove the # for the line if you want to override to set false or true
#
$show_links     = true;         // false no URLs will be shown, only the icon and name
$width_colomn_2 = '30%';
#
# Always at least your e-mail link is used so that visitors can mail you.
$mail__short    = 'For your remarks or questions';

# Other contact / legal info is optional
#
# Your facebook settings        ##### 2020-10-11  from ewasyweather settings
$facebookshow   = $facebookuser;        // set to true and fill in the next two lines
$facebook_short = 'You can find us here also';
$facebooklink   = 'https://www.facebook.com/'.$facebook.'/';
#                               #### 2020-10-11
# Your twitter settings
$twittershow    = $twitterUser;         // set to false if you do not want it twitterUser
$twitter_short  = 'You can find us here also';
$twitterlink    = 'https://twitter.com/'.$twitter;  // the twitter name comes from your settings
#
# Your website settings
$websiteshow    = true;
$website_short  = 'Our main website';
$websiteurl     = $this_server; // use this website for your PWS_Dashboard from the settings
#$websiteurl    = 'https:// x.x.x.x.';  // use another probably your  main website
#
# Your legal settings
$legalshow      = true;
$legaldocument  = false;      // if set to false the standard text-line from the settings is used
$legalurl       = $this_server.'/legal.pdf'; // the document you want to link to
$legaltxt       = $personalmessage;
#
$support_short  = 'More information about the used website template and tools'; 
#
# Your copyright settings
$copyrightshow  = true;
$copyrighturl   = './license.txt';
$copyright_short= 'About copyrighted parts used in this template';
#
# ----------------------- standard texts
$lng_info       = 'Contact information';
$lng_mail       = 'Mail';
$lng_twitter    = 'Twitter';
$lng_facebook   = 'Facebook';
$lng_legal      = 'Legal information';
$lng_website    = 'Website';
$lng_support    = 'Download and support';
$lng_copyright  = 'Copyrighted material used';
$ltxt_clsppp    = 'Close';
#
$mail_svg='<svg id="e-mail" viewBox="0 0 100 100" width="32" height="32">
  <path fill="orange" d="M 51.05 3 C 25.156 3 4.196 23.146 4.196 48.044 C 4.196 72.939 25.151 93.089 51.05 93.089 C 60.147 93.089 69.063 90.522 76.631 85.784 C 78.899 84.363 79.394 81.372 77.682 79.358 L 75.759 77.1 C 74.308 75.399 71.757 74.982 69.831 76.169 C 64.259 79.604 57.721 81.464 51.05 81.464 C 31.882 81.464 16.287 66.472 16.287 48.044 C 16.287 29.616 31.882 14.624 51.05 14.624 C 69.969 14.624 85.812 25.09 85.812 43.686 C 85.812 50.73 81.828 58.169 74.822 58.885 C 71.545 58.804 71.627 56.552 72.277 53.433 L 76.704 31.436 C 77.245 28.747 75.101 26.249 72.252 26.249 L 63.754 26.249 C 62.455 26.249 61.363 27.186 61.216 28.428 L 61.214 28.444 C 58.437 25.192 53.572 24.489 49.884 24.489 C 35.794 24.489 23.844 35.792 23.844 51.999 C 23.844 63.859 30.794 71.228 41.981 71.228 C 47.079 71.228 52.82 68.387 56.149 64.266 C 57.948 70.46 63.822 70.46 69.509 70.46 C 90.084 70.46 97.903 57.453 97.903 43.686 C 97.903 18.921 77.126 3 51.05 3 Z M 46.954 58.294 C 42.75 58.294 40.139 55.456 40.139 50.889 C 40.139 42.716 45.955 37.679 51.216 37.679 C 55.428 37.679 57.942 40.447 57.942 45.084 C 57.942 53.268 51.542 58.294 46.954 58.294 Z" style=""/>
</svg>';
$twitter_svg='<svg id="twitter" viewBox="0 0 100 100" width="32" height="32">
  <path fill="blue" d="M 89.817 26.914 C 89.88 27.794 89.88 28.674 89.88 29.554 C 89.88 56.394 69.451 87.32 32.114 87.32 C 20.611 87.32 9.926 83.989 0.937 78.206 C 2.571 78.394 4.143 78.457 5.84 78.457 C 15.331 78.457 24.068 75.252 31.046 69.783 C 22.12 69.594 14.64 63.749 12.063 55.703 C 13.32 55.891 14.577 56.017 15.897 56.017 C 17.72 56.017 19.543 55.766 21.24 55.326 C 11.937 53.44 4.96 45.269 4.96 35.4 L 4.96 35.149 C 7.662 36.657 10.806 37.6 14.137 37.726 C 8.668 34.08 5.085 27.857 5.085 20.817 C 5.085 17.046 6.091 13.589 7.851 10.571 C 17.845 22.891 32.868 30.937 49.714 31.817 C 49.4 30.309 49.211 28.737 49.211 27.166 C 49.211 15.977 58.263 6.863 69.514 6.863 C 75.36 6.863 80.64 9.314 84.348 13.274 C 88.937 12.394 93.337 10.697 97.234 8.372 C 95.725 13.086 92.52 17.046 88.308 19.56 C 92.394 19.12 96.354 17.989 100 16.417 C 97.234 20.44 93.777 24.023 89.817 26.914 Z" style=""/>
</svg>'.PHP_EOL;
$website_svg='<svg id="desktop"  viewBox="0 0 100 100" width="32" height="32">
  <path fill="darkgrey" d="M 88 0 L 8 0 C 3.583 0 0 4.031 0 9 L 0 69 C 0 73.969 3.583 78 8 78 L 40 78 L 37.333 87 L 25.333 87 C 23.117 87 21.333 89.006 21.333 91.5 C 21.333 93.994 23.117 96 25.333 96 L 70.667 96 C 72.883 96 74.667 93.994 74.667 91.5 C 74.667 89.006 72.883 87 70.667 87 L 58.667 87 L 56 78 L 88 78 C 92.417 78 96 73.969 96 69 L 96 9 C 96 4.031 92.417 0 88 0 Z M 85.333 66 L 10.667 66 L 10.667 12 L 85.333 12 L 85.333 66 Z" style=""/>
</svg>'.PHP_EOL;
$support_svg='<svg id="download" viewBox="0 0 100 100" width="32" height="32">
  <path fill="green" d="M 42.731 3 L 57.446 3 C 59.892 3 61.86 4.968 61.86 7.414 L 61.86 38.316 L 77.991 38.316 C 81.266 38.316 82.903 42.271 80.585 44.588 L 52.608 72.584 C 51.229 73.964 48.966 73.964 47.587 72.584 L 19.573 44.588 C 17.255 42.271 18.892 38.316 22.166 38.316 L 38.316 38.316 L 38.316 7.414 C 38.316 4.968 40.284 3 42.731 3 Z M 97.177 72.161 L 97.177 92.762 C 97.177 95.208 95.208 97.177 92.762 97.177 L 7.414 97.177 C 4.968 97.177 3 95.208 3 92.762 L 3 72.161 C 3 69.715 4.968 67.746 7.414 67.746 L 34.398 67.746 L 43.411 76.759 C 47.109 80.456 53.068 80.456 56.765 76.759 L 65.778 67.746 L 92.762 67.746 C 95.208 67.746 97.177 69.715 97.177 72.161 Z M 74.368 88.347 C 74.368 86.324 72.712 84.669 70.689 84.669 C 68.666 84.669 67.01 86.324 67.01 88.347 C 67.01 90.371 68.666 92.026 70.689 92.026 C 72.712 92.026 74.368 90.371 74.368 88.347 Z M 86.14 88.347 C 86.14 86.324 84.484 84.669 82.461 84.669 C 80.438 84.669 78.783 86.324 78.783 88.347 C 78.783 90.371 80.438 92.026 82.461 92.026 C 84.484 92.026 86.14 90.371 86.14 88.347 Z"/>
</svg>'.PHP_EOL;
$facebook_svg='<svg id="facebook" viewBox="0 0 100 100" width="32" height="32">
  <path fill="blue" d="M 100 49.299 C 100 22.066 77.934 0 50.701 0 C 23.469 0 1.403 22.066 1.403 49.299 C 1.403 73.903 19.431 94.298 42.998 98 L 42.998 63.549 L 30.474 63.549 L 30.474 49.299 L 42.998 49.299 L 42.998 38.436 C 42.998 26.082 50.354 19.257 61.619 19.257 C 67.014 19.257 72.655 20.221 72.655 20.221 L 72.655 32.346 L 66.439 32.346 C 60.314 32.346 58.405 36.147 58.405 40.046 L 58.405 49.299 L 72.077 49.299 L 69.891 63.549 L 58.405 63.549 L 58.405 98 C 81.972 94.298 100 73.903 100 49.299 Z" style=""/>
</svg>'.PHP_EOL;
$legal_svg='<svg id="legal" viewBox="0 0 100 100" width="32" height="32">
  <path fill="red" d="M 40.478 60.502 L 40.475 60.502 C 40.475 58.07 40.676 59.19 27.691 33.221 C 25.039 27.916 17.442 27.906 14.785 33.221 C 1.691 59.412 2.003 58.147 2.003 60.502 L 2 60.502 C 2 67.142 10.614 72.526 21.239 72.526 C 31.863 72.526 40.478 67.142 40.478 60.502 Z M 21.239 36.453 L 32.061 58.098 L 10.417 58.098 L 21.239 36.453 Z M 98.191 60.502 C 98.191 58.07 98.393 59.19 85.408 33.221 C 82.755 27.916 75.159 27.906 72.501 33.221 C 59.407 59.412 59.72 58.147 59.72 60.502 L 59.717 60.502 C 59.717 67.142 68.331 72.526 78.955 72.526 C 89.581 72.526 98.194 67.142 98.194 60.502 L 98.191 60.502 Z M 68.134 58.098 L 78.955 36.453 L 89.778 58.098 L 68.134 58.098 Z M 81.36 77.336 L 54.907 77.336 L 54.907 33.034 C 58.44 31.488 61.094 28.302 61.88 24.429 L 81.36 24.429 C 82.689 24.429 83.765 23.353 83.765 22.025 L 83.765 17.214 C 83.765 15.886 82.689 14.809 81.36 14.809 L 59.662 14.809 C 57.468 11.906 54.019 10 50.098 10 C 46.176 10 42.727 11.906 40.532 14.809 L 18.834 14.809 C 17.505 14.809 16.429 15.886 16.429 17.214 L 16.429 22.025 C 16.429 23.353 17.505 24.429 18.834 24.429 L 38.314 24.429 C 39.101 28.301 41.753 31.488 45.287 33.034 L 45.287 77.336 L 18.834 77.336 C 17.505 77.336 16.429 78.412 16.429 79.741 L 16.429 84.551 C 16.429 85.88 17.505 86.955 18.834 86.955 L 81.36 86.955 C 82.689 86.955 83.765 85.88 83.765 84.551 L 83.765 79.741 C 83.765 78.412 82.689 77.336 81.36 77.336 Z" style=""/>
</svg>'.PHP_EOL;
$copyright='<svg id="copyright"  viewBox="0 0 100 100"  width="32" height="32">
  <path fill="darkgrey" d="M 50 4 C 24.595 4 4 24.595 4 50 C 4 75.405 24.595 96 50 96 C 75.405 96 96 75.405 96 50 C 96 24.595 75.405 4 50 4 Z M 71.726 68.317 C 71.432 68.664 64.348 76.799 51.351 76.799 C 35.642 76.799 24.552 65.065 24.552 49.799 C 24.552 34.718 36.052 23.201 51.217 23.201 C 63.636 23.201 70.13 30.122 70.4 30.417 C 71.084 31.163 71.179 32.278 70.63 33.129 L 66.479 39.557 C 65.728 40.719 64.109 40.921 63.096 39.983 C 63.053 39.943 58.175 35.553 51.619 35.553 C 43.065 35.553 37.908 41.781 37.908 49.665 C 37.908 57.011 42.641 64.447 51.686 64.447 C 58.863 64.447 63.795 59.19 63.843 59.137 C 64.795 58.105 66.45 58.204 67.276 59.333 L 71.829 65.56 C 72.438 66.392 72.395 67.532 71.726 68.317 Z" style=""/>
</svg>'.PHP_EOL;
#
if ($show_links == true) 
     {  $extra_ul  = ' text-decoration: none; ';} 
else {  $extra_ul  = '';}
#
# normally we use the easyweather settings
if (isset ($show_close_x) )
     {  if ($show_close_x === false || $show_close_x === true)  
             { $close_popup = $show_close_x;}
        }
if ($close_popup === true) 
     {  $ltxt_clsppp    = lang($ltxt_clsppp); #### 2021-02-11
        $close          = '      <span style="float: left; ">&nbsp;X&nbsp;&nbsp;<small>'.$ltxt_clsppp.'</small></span>'.PHP_EOL;}
else {  $close          = '';}
#
echo '<!DOCTYPE html>
<html lang="'.substr($used_lang,0,2).'" style="">
<head>
  <meta charset="UTF-8">
  <title>contact '.$stationName.' </title>  
'. my_style().'
</head>
<body class="dark" style="background-color: transparent;">
<!-- header area -->
<div class="PWS_module_title" style="width: 100%; font-size: 14px; padding-top: 4px;">
'.$close.'
   <span style="color: #FF7C39; ">'.lang($lng_info).'</span>
</div>
<!-- end header area -->
<div style="text-align: left;">
<table style="width: 100%; margin: 0; border-collapse: collapse; border: none; background-color: white; color: black;">';
#
function one_row ( $link, $svg,  $lng, $type, $additional)
     {  global $show_links, $extra_ul, $width_colomn_2;
        if ($show_links == true) 
             {  $extra  = PHP_EOL.'        <span  style="font-size: 14px; margin: 0;"><br /><u>'.$link.'</u></span>';}
        else {  $extra  = '';}
        echo '<!-- '.$type.'  -->
<tr>
<td style=" border-bottom: 1px solid grey; padding: 10px;  width: 50px; text-align: center;"><span>'.$svg.'</span></td>
<td style=" border-bottom: 1px solid grey; text-align: left; min-width: '.$width_colomn_2.';">';
        if ( $link <> '' && $link <> false) 
             {  echo '
    <a href="'.$link.'" target="_blank" style="'.$extra_ul.' color: inherit;">
        <span style="font-weight: bold; font-size: 18px; ">'.lang($lng).' </span>'.$extra.'
    </a>';}
        else {  echo '
        <span style="font-weight: bold; font-size: 18px; ">'.lang($lng).' </span>';}
        echo '
</td>
<td style=" border-bottom: 1px solid grey; text-align: left; padding-left: 5px; font-size: 12px;" class="invisible">'.lang($additional).'
</td>
</tr><!-- eo '.$type.'  -->'.PHP_EOL;  

} // eof one_row
#
$mailto = 'mailto:';
if (strtolower(substr($email,0,4)) == 'http') {$mailto = '';}
if ($email <> 'someone@dot.com') 
     {  one_row ( $mailto.$email, $mail_svg,  $lng_mail, 'E-MAIL', $mail__short); }
else {  echo '<!-- email  NOT  printed  -->'.PHP_EOL;}
#
if (isset ($twittershow) && $twittershow == true)     // this one is optional
     {  one_row ( $twitterlink, $twitter_svg,  $lng_twitter, 'TWITTER',$twitter_short); }
else {  echo '<!-- no twitter area printed, not a twitter user  -->'.PHP_EOL;}
#
if (isset ($facebookshow) && $facebookshow == true)      // this one is optional
     {  one_row ( $facebooklink, $facebook_svg,  $lng_facebook, 'FACEBOOK', $facebook_short ); }
else {  echo '<!-- no facebook area printed, not a twitter user  -->'.PHP_EOL;}
#
if (isset ($websiteshow) && $websiteshow == true)     // this one is optional
     {  one_row ( $websiteurl, $website_svg,  $lng_website, 'WEBSITE', $website_short); }
else {  echo '<!-- no website area printed  -->'.PHP_EOL;}
#
if (isset ($legalshow) && $legalshow == true)     // this one is optional
     {  if ($legaldocument  <> true) {  $legalurl = ''; }  else { $legaltxt = '';}  
        one_row ( $legalurl, $legal_svg,  $lng_legal, 'LEGAL', $legaltxt); }
else {  echo '<!-- no LEGAL area printed  -->'.PHP_EOL;}
#
if (isset ($copyrightshow) && $copyrightshow == true)     // this one is optional
     {  one_row ( $copyrighturl, $copyright,  $lng_copyright, 'COPYRIGHT', $copyright_short); }
else {  echo '<!-- no website area printed  -->'.PHP_EOL;}
#
if (1 == 1)     // always
     {  one_row ( 'https://pwsdashboard.com', $support_svg,  $lng_support, 'TEMPLATE', $support_short); }
else {  echo '<!-- no support links  printed  -->'.PHP_EOL;}
#
if (isset ($zz) && $zz == true)     // this one is optional
     {  one_row ( $copyrighturl, $website_svg,  $lng_website, 'WEBSITE', $copyrighttext); }
else {  echo '<!-- no website area printed  -->'.PHP_EOL;}

echo '<!-- template support  -->
<!-- template support  -->'.PHP_EOL;
echo '</table>
</div><!-- eo enclosing div -->'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->';}
echo '</body>
</html>'.PHP_EOL;
/*

#
echo '<!-- item support area -->
        <div style="width: 100%;   height: 40px; padding: 1rem;  background-color: #FFFFFF; border-bottom: 1px solid grey;">
        <a href="'.$url_support.'" target="_blank"  style="color: inherit;">
            <div style="width: 20%; float: left;">'.$support_svg.'</div>
            <div style="width: 78%; float: right;">
                <h3 style="font-size: 20px; margin: 0; margin-top: 5px;">'.$lng_support .'</h3>
                <p  style="font-size: 14px; margin: 0;">';
if ($show_links == true) {echo '<u>'.$url_support.'</u>';} else {echo '&nbsp;';}
echo '</p>
            </div> 
        </a>
        </div>
        <!-- end of item support area -->'.PHP_EOL;
echo '</div><!-- eo enclosing div -->'.PHP_EOL;
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->';}
echo '</body>
</html>'.PHP_EOL;
*/
#
# style is printed in the header 
function my_style()
     {  global $popup_css ;
        $return         = PHP_EOL.'    <style>'.PHP_EOL;
# load the genral css for pop-ups
        if (isset ($popup_css) && $popup_css <> false)  
             {  $return .= file_get_contents ($popup_css);}
             
# add pop-up specific css
$return .=    '.featherlight .featherlight-content { background: transparent;}
@media screen and (max-width: 639px) {
                    .invisible {display: none;} }'.PHP_EOL;
        $return         .= '    </style>'.PHP_EOL;
        return $return;

 }
