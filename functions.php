<?php
// module local use functions
// $Id: functions.php,v 1.3 2003/12/04 17:24:16 nobu Exp $

$uri_base = preg_replace('/^http:\/\/[^\/]*/', '', XOOPS_URL)."/";
$reg_mod = "/^".preg_quote($uri_base."modules/", "/").'([^\/]+)\//';

function uri_to_name($uri) {
    global $uri_base, $reg_mod;
    if ($uri==$uri_base) return _TB_TOPPAGE;
    if (preg_match($reg_mod, $uri, $d)) {
	// module pages
	$mod = XoopsModule::getByDirname($d[1]);
	$name = isset($mod)?$mod->name():$d[1];
	$rest = myurldecode(preg_replace($reg_mod, '', $uri));
	$rest = str_replace("index.php?","",$rest);
	return $name.($rest==""?"":" - ").$rest;
    }
    return $uri;
}

function strim($s, $l=50) {
    if (strlen($s)<$l) return $s;
    $h = intval(($l-3)/2);
    $t = strlen($s)-$h+3;
    return htmlspecialchars(mysubstr($s,0,$h)."...".mysubstr($s,$t));
}

function myurldecode($url) {
    $url = rawurldecode($url);
    if (XOOPS_USE_MULTIBYTES && function_exists("mb_convert_encoding")) {
	$url = mb_convert_encoding($url, _CHARSET, "JIS,UTF-8,Shift_JIS,EUC-JP");
    }
    return $url;
}

// substrings with support multibytes (--with-mbstring)
function mysubstr($s, $f, $l=9999) {
    if (XOOPS_USE_MULTIBYTES && function_exists("mb_strcut")) {
	return mb_strcut($s, $f, $l, _CHARSET);
    } else {
	return substr($s, $f, $l);
    }
}

function make_page_index($title, $max, $cur, $format, $asis=" <b>[%d]</b>") {
    global $trackConfig;
    $npg = intval(($max-1)/$trackConfig['list_max'])+1;
    if ($npg<2) $npg=1;
    $result = "<div class='pgindex'>$title: ";
    for ($i=1; $i<=$npg;$i++) {
	if ($i==$cur) {
	    $result .= sprintf($asis, $i);
	} else {
	    $result .= sprintf($format, $i, $i);
	}
    }
    $result .= "</div>";
    return $result;
}

// Win/Mac/Unix's newline normalize
function crlf2nl($s) {
    return preg_replace("/\x0D\x0A|\x0D|\x0A/","\n", $s);
}

$tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
?>