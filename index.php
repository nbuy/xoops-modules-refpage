<?php
// $Id: index.php,v 1.5 2003/12/09 07:15:38 nobu Exp $
include("header.php");
include_once "functions.php";

$base = XOOPS_URL."/modules/".$xoopsModule->dirname();
$basedir = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();

$track_id =isset($HTTP_GET_VARS['id'])?$HTTP_GET_VARS['id']:"";
$page = isset($HTTP_GET_VARS['page'])?$HTTP_GET_VARS['page']:1;

$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");
$tblstyle="border='0' cellspacing='1' cellpadding='3' class='bg2' width='100%'";
include(XOOPS_ROOT_PATH."/header.php");
OpenTable();

if ($track_id == "all") {
    $order = "nref DESC";
    $opt = "";
    if (isset($HTTP_GET_VARS['order'])) {
	switch ($HTTP_GET_VARS['order']) {
	case 'time':
	    $order = "mtime DESC";
	    $opt .= "&order=time";
	    break;
	}
    }
    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr,$tbl WHERE linked=1 AND track_id=track_from AND disable=0");
    list($nrec) = $xoopsDB->fetchRow($result);
    $start = ($page>1)?($page-1)*$trackConfig['list_max']:0;
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    echo "<p>"._TB_ALLPAGE."</p>\n";
    $result = $xoopsDB->query("SELECT * FROM $tbr,$tbl WHERE linked=1 AND track_id=track_from AND disable=0 ORDER BY $order", $trackConfig['list_max'], $start);
    if ($nrec) {
	$popt = ($page>1)?"&page=$page":"";
	$ordstr = sprintf($order=="nref DESC"?"<b>%s</b>":"<a href='index.php?id=all$popt'>%s</a>",_TB_ORDER_NREF).
	    " | ".sprintf($order=="mtime DESC"?"<b>%s</b>":"<a href='index.php?id=all&order=time$popt'>%s</a>",_TB_ORDER_TIME);
	$pctrl = make_page_index("$ordstr - "._TB_PAGE, $nrec, $page, " <a href='index.php?id=all$opt&page=%d'>(%d)</a>");
	echo $pctrl;
	echo "<table $tblstyle>\n";
	$nc = 1;
	while ($data=$xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $start++;
	    $uri = $data['track_uri'];
	    $linkto = " "._TB_LINKTO." <a href='$uri'>".uri_to_name($uri)."</a>";
	    echo "<tr class='$bg'><td>#$start: ".make_track_item($data, $linkto)."</td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
} elseif ( empty($track_id) ) {
    // list of tracking pages
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    $result = $xoopsDB->query("SELECT count(track_id) FROM $tbl,$tbr WHERE track_id=track_from AND disable=0 AND linked=1 GROUP BY track_from");
    list($nrec) = $xoopsDB->fetchRow($result);
    $start = ($page>1)?($page-1)*$trackConfig['list_max']:0;
    $result = $xoopsDB->query("SELECT track_id, track_uri, count(ref_id) FROM $tbl,$tbr WHERE track_id=track_from AND disable=0 AND linked=1 GROUP BY track_from ORDER BY track_uri", $trackConfig['list_max'], $start);
    if ($nrec) {
	$pctrl = make_page_index(_TB_PAGE, $nrec, $page, " <a href='index.php?page=%d'>(%d)</a>");
	echo $pctrl;
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th>"._TB_TRACKPAGE."</th><th>"._TB_REF_SOURCE."</th></tr>\n";
	$nc = 1;
	while (list($tid, $uri, $refs)=$xoopsDB->fetchRow($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $start++;
	    echo "<tr class='$bg'><td><a href='index.php?id=$tid'>#$start: ".uri_to_name($uri)."</a></td><td style='text-align:center'>$refs</a></td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
} else {
    // a tracking page
    $order = "nref DESC";
    $opt = "";
    if (isset($HTTP_GET_VARS['order'])) {
	switch ($HTTP_GET_VARS['order']) {
	case 'time':
	    $order = "mtime DESC";
	    $opt .= "&order=time";
	    break;
	}
    }
    $result = $xoopsDB->query("SELECT track_uri,disable FROM $tbl WHERE track_id=$track_id");
    list($uri, $disable) = $xoopsDB->fetchRow($result);
    if (!isset($disable) || $disable) {
	redirect_header("index.php",1,_TB_NOPAGE);
	exit();
    }

    $result = $xoopsDB->query("SELECT count(ref_id) FROM $tbr WHERE track_from=$track_id AND linked=1");
    list($nrec) = $xoopsDB->fetchRow($result);
    $start = ($page>1)?($page-1)*$trackConfig['list_max']:0;
    echo "<h4>"._MI_TRACKBACK_NAME."</h4>";
    echo "<p>"._TB_TRACKPAGE.": <a href='index.php'>"._TB_INDEX."</a> &gt;&gt; <a href='$uri'>".uri_to_name($uri)."</a></p>\n";
    $result = $xoopsDB->query("SELECT * FROM $tbr WHERE track_from=$track_id AND linked=1 ORDER BY $order", $trackConfig['list_max'], $start);
    if ($nrec) {
	$popt = ($page>1)?"&page=$page":"";
	$ordstr = sprintf($order=="nref DESC"?"<b>%s</b>":"<a href='index.php?id=$track_id$popt'>%s</a>",_TB_ORDER_NREF).
	    " | ".sprintf($order=="mtime DESC"?"<b>%s</b>":"<a href='index.php?id=$track_id&order=time$popt'>%s</a>",_TB_ORDER_TIME);
	$pctrl = make_page_index("$ordstr - "._TB_PAGE, $nrec, $page, " <a href='index.php?id=$track_id$opt&page=%d'>(%d)</a>");
	echo $pctrl;
	echo "<table $tblstyle>\n";
	$nc = 1;
	while ($data=$xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $start++;
	    echo "<tr class='$bg'><td>#$start: ".make_track_item($data)."</td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
}
CloseTable();
include (XOOPS_ROOT_PATH."/footer.php");
?>