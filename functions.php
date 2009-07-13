<?php
// module local use functions
// $Id: functions.php,v 1.10 2009/07/13 07:03:10 nobu Exp $

$uri_base = preg_replace('/^http:\/\/[^\/]*/', '', XOOPS_URL)."/";
$reg_mod = "/^".preg_quote($uri_base."modules/", "/").'([^\/]+)\//';

function uri_to_name($uri) {
    global $uri_base, $reg_mod;
    if ($uri==$uri_base) return _TB_TOPPAGE;
    if (preg_match($reg_mod, $uri, $d)) {
	// module pages
	$mod = XoopsModule::getByDirname($d[1]);
	$name = is_object($mod)?$mod->name():$d[1];
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
    return htmlspecialchars(mysubstr($s,0,$h)._TB_LEADER.mysubstr($s,$t));
}

function myurldecode($url) {
    $url = rawurldecode($url);
    if (XOOPS_USE_MULTIBYTES && function_exists("mb_convert_encoding")) {
	$url = @mb_convert_encoding($url, _CHARSET, "ISO-2022-JP,JIS,EUC-JP,UTF-8,SJIS");
    }
    return $url;
}

// substrings with support multibytes (--with-mbstring)
function mysubstr($s, $f, $l=99999) {
    if (XOOPS_USE_MULTIBYTES && function_exists("mb_strcut")) {
	return mb_strcut($s, $f, $l, _CHARSET);
    } else {
	return substr($s, $f, $l);
    }
}

function make_page_index($title, $max, $cur, $format, $asis=" <b>[%d]</b>") {
    global $xoopsModuleConfig;
    $npg = intval(($max-1)/$xoopsModuleConfig['list_max'])+1;
    if ($npg<2) $npg=1;
    $result = "<div class='pgindex'>$title: ";
    $side = 2;
    if ($cur > $side+2) {
	$result .= sprintf($format, 1, 1)." "._TB_LEADER." ";
	$start = $cur-$side;
    } else {
	$start = 1;
    }
    for ($i=$start; $i<=$npg;$i++) {
	if ($i==$cur) {
	    $result .= sprintf($asis, $i);
	} else {
	    $result .= sprintf($format, $i, $i);
	    if ($i>=$cur+$side && $i<=$npg-$side) {
		$result .= " "._TB_LEADER." ".sprintf($format, $npg, $npg);
		break;
	    }
	}
    }
    $result .= "</div>";
    return $result;
}

// Win/Mac/Unix's newline normalize
function crlf2nl($s) {
    return preg_replace("/\x0D\x0A|\x0D|\x0A/","\n", $s);
}

function make_track_item($data, $add="", $attr="target='_blank'") {
    global $xoopsModuleConfig;
    $cdate = formatTimestamp($data['since'], "m");
    $mdate = ($data['mtime']>10)?formatTimestamp($data['mtime'], "m"):_TB_WAIT_UPDATE;
    $url = $data['ref_url'];
    $nref = $data['nref'];
    $nurl = "";
    if (isset($data['refs'])) {
	$nurl = _TB_REF_NURL.": ".$data['n'];
	$refn = $data["refn"];
	foreach ($data["refs"] as $ref) {
	    $nurl .= " <a href='$ref'>[".array_shift($refn)."]</a>";
	}
	$nurl = "<div class='trinfo'>$nurl</div>";
    }
    $title = $data['title'];
    $len = max($xoopsModuleConfig['title_len'],255);
    $alt = "";
    if ($title == '') $title = strim(myurldecode($url), $len);
    elseif (strlen($title)>$len) {
	$alt = " title='$title'";
	$title=mysubstr($title, 0, $len-2)."..";
    }
    if ($data['context'] != '') {
	$ctext = _TB_LEADER.preg_replace(array('/&lt;u&gt;/', '/&lt;\\/u&gt;/'), array("<u class='anc'>", "</u>"), htmlspecialchars($data['context']))._TB_LEADER;
    } else {
	$ctext = "";
    }
    return "<a href='$url'$alt $attr class='trtitle'>$title</a>$add".
	"<div class='trtext'>$ctext</div>".
	"<div class='trinfo'>".
	_TB_REF_COUNT.":$nref ["._TB_REF_CDATE." $cdate] [".
		_TB_REF_MDATE." $mdate]<br/>"._TB_REF_URL.
	" <a href='$url'>".myurldecode($url)."</a></div>".
	$nurl;
}

$tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
?>
