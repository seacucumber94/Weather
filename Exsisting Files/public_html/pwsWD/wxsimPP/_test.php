<?php  ini_set('display_errors', 'On');   error_reporting(E_ALL);  # error_reporting(E_ALL & ~E_NOTICE &  ~E_DEPRECATED);
$script = 'plaintext-parser.php';
if (isset ($_GET['test'])  )    { $script = trim($_GET['test']);}
#echo '<style>';include 'css/main.light.css';echo '</style>';
include ($script);
echo '<pre>'.PHP_EOL;
if (isset ($missing))
     {  foreach ($missing as $txt) {echo $txt;}}

