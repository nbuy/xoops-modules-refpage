<?php
// module local use functions
// $Id: functions.php,v 1.2 2003/12/03 09:20:24 nobu Exp $

$uri_base = preg_replace('/^http:\/\/[^\/]*/', '', XOOPS_URL)."/";
$reg_mod = "/^".preg_quote($uri_base."modules/", "/").'([^\/]+)\//';

function uri_to_name($uri) {
    global $uri_base, $reg_mod;
    if ($uri==$uri_base) return _TB_TOPPAGE;
    if (preg_match($reg_mod, $uri, $d)) {
	// module pages
	$mod = XoopsModule::getByDirname($d[1]);
	$name = isset($mod)?$mod->name():$d[1];
	$rest = rawurldecode(preg_replace($reg_mod, '', $uri));
	$rest = str_replace("index.php?","",$rest);
	return $name.($rest==""?"":" - ").$rest;
    }
    return $uri;
}

function strim($s, $l=50) {
    if (strlen($s)<$l) return $s;
    $h = intval(($l-3)/2);
    $t = strlen($s)-$h+3;
    return substr($s,0,$h)."...".substr($s,$t);
}

$tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
?>