<?php
error_reporting(0);
include 'include/config.php';
include 'include/ZlibDecompress.php';
$game = $_GET['g'];
$build = $_GET['v'];
$file = $_GET['f'];
if (!isset($game) || !isset($build) || !isset($file)) header('Location: index.php');
//echo '<title>Hash'.$games[$game][1].'</title>';
hashes($game,$urls[$game].$build.'/'.($file ? $file : $files[$game][0]));
?>