<?php
// $Id: refpage.php,v 1.2 2003/11/20 03:59:11 nobu Exp $
function b_trackback_log_show($options) {
    global $xoopsDB;

    // trackback recoding

    function except_url($url) {
	if (preg_match('/\/\/www\.google\./i', $url)) return true;
	if (preg_match('/\/\/search\.(yahoo|msn)\./i', $url)) return true;
	if (preg_match('/\/\/search\.yahoo\./i', $url)) return true;
	return false;
    }

    $ref = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"";
    $uri = $_SERVER["REQUEST_URI"];
    $uri = preg_replace('/\/index.php$/', '/', $uri);
    $uriq= addslashes($uri);
    $now = time();

    $tbl = $xoopsDB->prefix("trackback");
    $tbr = $xoopsDB->prefix("trackback_ref");
    $sql = "SELECT track_id FROM $tbl WHERE track_uri='$uriq'";
    $result = $xoopsDB->query($sql);
    // referere self site
    if (preg_match("/^".preg_quote(XOOPS_URL,"/")."\//", $ref)) $ref="";
    if ($xoopsDB->getRowsNum($result)) {
	list($tid) = $xoopsDB->fetchRow($result);
    } else {
	// new page register
	if ($ref!="") {
	    $xoopsDB->queryF("INSERT INTO $tbl(track_uri, since) VALUES('$uriq', $now)");
	    $result = $xoopsDB->query($sql);
	    list($tid) = $xoopsDB->fetchRow($result);
	} else {
	    $tid = 0;
	}
    }

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
	    if (except_url($ref)) {
		$checked = 0;
	    } elseif ($options[2]) {
		$checked = 0;
		$fp = @fopen($ref, "r");
		$page = '';
		if ($fp) {
		    while ($page = fgets($fp)) {
			if (preg_match("/href=[\"']?".preg_quote(XOOPS_URL,"/")."/i", $page)) {
			    $checked = 1;
			    break;
			}
		    }
		}
	    } else {
		$checked = 1;
	    }
	    $xoopsDB->queryF("UPDATE $tbr SET nref=nref+1, checked=$checked WHERE track_from=$tid AND ref_url='$refq'");
	    $result = $xoopsDB->query("SELECT ref_id FROM $tbr WHERE track_from=$tid AND ref_url='$refq'");
	    list($rid) = $xoopsDB->fetchRow($result);
	    $xoopsDB->queryF("INSERT INTO $log(atime, tfrom, rfrom, ip) VALUES($now, $tid, $rid, '$ip')");
	    $refno = 1;
	}
    }

    // trackback show block build

    $block = array();
    if (!$options[0]) return $block;
    $block['title'] = _MB_TRACKBACK_TITLE;
    $body = "";
    if ($tid) {
	$result = $xoopsDB->query("SELECT nref, ref_url FROM $tbr WHERE track_from=$tid AND checked=1 ORDER BY nref DESC");
	if ($xoopsDB->getRowsNum($result)) {
	    $n=0;
	    while ($n < $options[1] &&
		   list($nref, $url)=$xoopsDB->fetchRow($result)) {
		$n++;
		$name = preg_replace('/\/.*$/', '', preg_replace('/^https?:\/\//', '', $url));
		$body .= "<a href='$url'>$name</a> ($nref)<br/>\n";
	    }
	    $body .= "<div style='text-align: right'><a href='".XOOPS_URL."/modules/trackback/index.php?id=$tid'>"._MB_TRACKBACK_MORE."</a></div>\n";
	}
    }
    if ($body=="") $body = "<div>"._MB_TRACKBACK_NONE."</div>";
    $block['content'] = $body;
    return $block;
}

function b_trackback_log_edit($options) {
    function ynradio($v, $name) {
	if ($v) {
	    $sel0=" checked";
	    $sel1="";
	} else {
	    $sel0="";
	    $sel1=" checked";
	}
	$c="<input type='radio' name='$name' value=";
	return "$c'1'$sel0 />"._YES." &nbsp; \n$c'0'$sel1 />"._NO."\n";
    }
    return _MB_TRACKBACK_VIEW."&nbsp;".ynradio($options[0],"options[0]")."<br/>".
	_MB_TRACKBACK_MAX."&nbsp;<input name='options[1]' value='".$options[1].
	"' />\n<br/>"._MB_TRACKBACK_CHECK."&nbsp;".ynradio($options[2],"options[2]");
}
?>