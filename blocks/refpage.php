<?php
// $Id: refpage.php,v 1.3 2003/12/02 03:52:09 nobu Exp $
function list_to_regexp($l) {
    $l = trim($l);
    if ($l == '') return '';
    return '/^https?:\/\/('.preg_replace(array('/\n*$/', '/\r?\n(\r?\n)+/','/\r?\n/', '/\./','/\*/', '/\//'),array('', "\n", '|', '\.', '.*', '\/'), $l).')/';
}

function b_trackback_log_show($options) {
    global $xoopsDB;

    $block = array();

    // ** trackback recoding **

    $ref = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"";
    $uri = $_SERVER["REQUEST_URI"];
    $uri = preg_replace('/\/index.php$/', '/', $uri);
    $uriq= addslashes($uri);
    $now = time();

    $tbl = $xoopsDB->prefix("trackback");
    $tbr = $xoopsDB->prefix("trackback_ref");

    $sql = "SELECT track_id,disable FROM $tbl WHERE track_uri='$uriq'";
    $result = $xoopsDB->query($sql);
    // referere self site
    if (preg_match("/^".preg_quote(XOOPS_URL,"/")."\//", $ref)) $ref="";
    if ($xoopsDB->getRowsNum($result)) {
	list($tid, $disable) = $xoopsDB->fetchRow($result);
    } else {
	// new page register
	if ($ref!="") {
	    $xoopsDB->queryF("INSERT INTO $tbl(track_uri, since) VALUES('$uriq', $now)");
	    $result = $xoopsDB->query($sql);
	    list($tid, $disable) = $xoopsDB->fetchRow($result);
	} else {
	    $disable = 0;
	    $tid = 0;
	}
    }

    if ($disable) return $block; // disable in this page

    if (function_exists("getCache")) {
	eval(getCache("trackback/config.php"));
    } else {
	include_once XOOPS_ROOT_PATH."/modules/trackback/cache/config.php";
    }

    $substr = function_exists("mb_strcut")?"mb_strcut":"substr";

    if ($tid && $ref!="") {
	$refq= addslashes($ref);
	$result = $xoopsDB->query("SELECT ref_id,nref FROM $tbr WHERE ref_url='$refq' AND track_from=$tid");
	$ip = $_SERVER["REMOTE_ADDR"];
	$log = $xoopsDB->prefix("trackback_log");
	if ($xoopsDB->getRowsNum($result)) {
	    // already registered
	    list($rid, $refno) = $xoopsDB->fetchRow($result);
	    // check valid reference. (is it not reload?)
	    // remove expire entry
	    $xoopsDB->queryF("DELETE FROM $log WHERE atime<".($now-3600));
	    $result = $xoopsDB->queryF("SELECT log_id FROM $log WHERE tfrom=$tid AND rfrom=$rid AND ip='$ip'");
	    if ($xoopsDB->getRowsNum($result)) {
		list($lid) = $xoopsDB->fetchRow($result);
		$xoopsDB->queryF("UPDATE $log SET atime=$now WHERE log_id=$lid");
	    } else {
		$xoopsDB->queryF("UPDATE $tbr SET nref=nref+1 WHERE ref_id=$rid");
		$refno++;
		$xoopsDB->queryF("INSERT INTO $log(atime, tfrom, rfrom, ip) VALUES($now, $tid, $rid, '$ip')");
	    }
	} else {
	    // new register
	    $xoopsDB->queryF("INSERT INTO $tbr(since,track_from,ref_url)".
			     " VALUES($now, $tid, '$refq')");
	    // check origin page, there is link exist?
	    $exreg = list_to_regexp($trackConfig['exclude']);
	    $inreg = list_to_regexp($trackConfig['include']);
	    $checked = 0;
	    $linked = 0;
	    $title = '';
	    $ctext = '';
	    if ($exreg!='' && preg_match($exreg, $ref)) {
		// No check and No link
	    } elseif ($inreg!='' && preg_match($inreg, $ref)) {
		$linked = 1;	// link accepted
	    } elseif ($trackConfig['auto_check']) {
		$checked = 1;	// accepted
		$fp = @fopen($ref, "r");
		$page = '';
		if ($fp) {
		    while ($ln = fgets($fp)) {
			$page .= $ln;
		    }
		    if ($substr == "mb_strcut") {
			$page = mb_convert_encoding($page, _CHARSET, "JIS,UTF-8,Shift_JIS,EUC-JP");
		    }
		    if (preg_match("/<title>(.*)<\/title>/i", $page, $d)) {
			$title = $d[1];
			$page = preg_replace("/<title>(.*)<\/title>/i", "", $page);
		    }
		    $anc = "/<a\\s+href=[\"']?".preg_quote(XOOPS_URL.$uri,"/")."[^>]*>/i";
		    $relax = "/<a\\s+href=[\"']?".preg_quote(XOOPS_URL,"/")."[^>]*>/i";
		    if (preg_match($anc, $page)) {
			$F = preg_split($anc, $page, 2);
			$linked = 1;
		    } elseif (preg_match($relax, $page)) {
			$F = preg_split($relax, $page, 2);
			$linked = 1;
		    }
		    if ($linked) {
			$l = 50; // string length
			$pre = ltrim(preg_replace('/\s+/', ' ', strip_tags($F[0])));
			list($a, $p)=preg_split('/<\/a>/i', $F[1], 2);
			$post = rtrim(preg_replace('/\s+/', ' ', strip_tags($p)));
			$ctext=$substr($pre, max(strlen($pre)-$l,0),$l, _CHARSET).
			    "<u>".strip_tags($a)."</u>".
			    $substr($post, 0, min(strlen($post),$l), _CHARSET);
		    }
		}
	    }
	    $title=addslashes($title);
	    $ctext=addslashes($ctext);
	    $xoopsDB->queryF("UPDATE $tbr SET nref=nref+1, checked=$checked, linked=$linked, title='$title', context='$ctext' WHERE track_from=$tid AND ref_url='$refq'");
	    $result = $xoopsDB->query("SELECT ref_id FROM $tbr WHERE track_from=$tid AND ref_url='$refq'");
	    list($rid) = $xoopsDB->fetchRow($result);
	    $xoopsDB->queryF("INSERT INTO $log(atime, tfrom, rfrom, ip) VALUES($now, $tid, $rid, '$ip')");
	    $refno = 1;
	}
    }

    // trackback show block build

    if ($trackConfig['block_hide']) return $block;
    $block['title'] = _MB_TRACKBACK_TITLE;
    $body = "";
    if ($tid) {
	$result = $xoopsDB->query("SELECT nref, ref_url, title FROM $tbr WHERE track_from=$tid AND linked=1 ORDER BY nref DESC");
	$nn = $xoopsDB->getRowsNum($result);
	if ($nn) {
	    $n=$options[0];
	    $l=$options[1];
	    while ($n-- &&
		   list($nref, $url, $title)=$xoopsDB->fetchRow($result)) {
		if ($title=="") {
		    $title = preg_replace('/\/.*$/', '', preg_replace('/^https?:\/\//', '', $url));
		}
		if (strlen($title)>$l) $title=$substr($title,0,$l,_CHARSET)."..";
		$body .= "<a href='$url'>$title</a> ($nref)<br/>\n";
	    }
	    $body .= "<div style='text-align: right'><a href='".XOOPS_URL."/modules/trackback/index.php?id=$tid'>"._MB_TRACKBACK_MORE."</a></div>\n";
	    if ($nn>$options[0]) $body .= "<div style='text-align: center;'>:</div>\n";
	}
    }
    if ($body=="") $body = "<div>"._MB_TRACKBACK_NONE."</div>";
    $block['content'] = $body;
    return $block;
}

function b_trackback_log_edit($options) {
    return _MB_TRACKBACK_MAX."&nbsp;<input name='options[0]' value='".$options[0]."' /><br />\n".
	_MB_TRACKBACK_MAX."&nbsp;<input name='options[1]' value='".$options[1]."' />\n";
}
?>