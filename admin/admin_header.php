<?php
include("../../../include/cp_header.php");
$modbase = dirname(dirname(__FILE__));
$inc = $modbase.'/language/'.$xoopsConfig['language'].'/main.php';
if (file_exists($inc)) include_once $inc;
else {				// fallback
    $inc = $modbase.'/language/english/main.php';
    if (file_exists($inc)) include_once $inc;
}
?>
