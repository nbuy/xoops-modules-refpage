<?php
// trackback module for XOOPS (user side code)
// $Id: index.php,v 1.11 2009/07/13 07:03:10 nobu Exp $
include("header.php");
include_once "functions.php";

$base = XOOPS_URL."/modules/".$xoopsModule->dirname();
$basedir = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();

$track_id =isset($_GET['id'])?intval($_GET['id']):"";
$page = isset($_GET['page'])?intval($_GET['page']):1;
$detail = isset($_GET['detail']);

$tbl = $xoopsDB->prefix("trackback");
$tbr = $xoopsDB->prefix("trackback_ref");
$tblstyle="border='0' cellspacing='1' cellpadding='3' class='outer' width='100%'";
include(XOOPS_ROOT_PATH."/header.php");

if (!isset($xoopsModuleConfig['threshold'])) $xoopsModuleConfig['threshold'] = 1;

if ($track_id == "all") {
    $order = "nref DESC";
    $opt = "";
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
	case 'time':
	    $order = "mtime DESC";
	    $opt .= "&order=time";
	    break;
	}
    }
    $cond = "linked=1 AND track_id=track_from AND disable=0 AND nref>=".$xoopsModuleConfig['threshold'];
    $title = "";
    $result = $xoopsDB->query("SELECT COUNT(title) FROM $tbr,$tbl WHERE $cond");
    list($nrec) = $xoopsDB->fetchRow($result);
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    echo "<p>"._TB_ALLPAGE."</p>\n";
    $result = $xoopsDB->query("SELECT * FROM $tbr,$tbl WHERE $cond ORDER BY $order", $xoopsModuleConfig['list_max'], $start);
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
	    echo "<tr class='$bg'><td class='trackitem'>$start. ".make_track_item($data, $linkto)."</td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
} elseif ( empty($track_id) ) {
    // list of tracking pages
    $cond = "track_id=track_from AND disable=0 AND linked=1 AND nref>=".$xoopsModuleConfig['threshold']." GROUP BY track_from ";
    $result = $xoopsDB->query("SELECT track_from FROM $tbl,$tbr WHERE $cond");
    $nrec = $xoopsDB->getRowsNum($result);
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    $result = $xoopsDB->query("SELECT track_id, track_uri, COUNT(ref_id) FROM $tbl,$tbr WHERE $cond ORDER BY track_uri", $xoopsModuleConfig['list_max'], $start);
    if ($nrec) {
	$pctrl = make_page_index(_TB_PAGE, $nrec, $page, " <a href='index.php?page=%d'>(%d)</a>");
	echo $pctrl;
	echo "<table $tblstyle>\n";
	echo "<tr class='bg1'><th>"._TB_TRACKPAGE."</th><th>"._TB_REF_SOURCE."</th></tr>\n";
	$nc = 1;
	while (list($tid, $uri, $refs)=$xoopsDB->fetchRow($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $start++;
	    echo "<tr class='$bg'><td class='trackref'>$start. <a href='index.php?id=$tid'>".uri_to_name($uri)."</a></td><td style='text-align:center'>$refs</a></td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
} else {
    // a tracking page
    $order = "nref DESC";
    $opt = "";
    if (isset($_GET['order'])) {
	switch ($_GET['order']) {
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

    $cond = "track_from=$track_id AND linked=1 AND nref>=".$xoopsModuleConfig['threshold'];
    if ($detail) {
	$sql = "SELECT COUNT(ref_id) FROM $tbr WHERE $cond";
	$result = $xoopsDB->query($sql);
	list($nrec) = $xoopsDB->fetchRow($result);
    } else {
	$sql = "SELECT title, count(1) FROM $tbr WHERE $cond GROUP BY title";
	$result = $xoopsDB->query($sql);
	$nrec = $xoopsDB->GetRowsNum($result);
    }
    $start = ($page>1)?($page-1)*$xoopsModuleConfig['list_max']:0;
    echo "<p>"._TB_TRACKPAGE.": <a href='index.php'>"._TB_INDEX."</a> &gt;&gt; <a href='$uri'>".uri_to_name($uri)."</a></p>\n";
    if ($detail) {			// summary by "title"
	$sql = "SELECT * FROM $tbr WHERE $cond ORDER BY $order";
    } else {
	$sql = "SELECT SUM(nref) nref,COUNT(ref_id) n, title, ".
	    " MAX(mtime) mtime, MIN(since) since,".
	    " MIN(ref_url) ref_url, MAX(context) context".
	    " FROM $tbr WHERE $cond".
	    " GROUP BY title ORDER BY $order";
    }
    $result = $xoopsDB->query($sql, $xoopsModuleConfig['list_max'], $start);
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
	    if (!$detail && $data['n']>1) {	// url list
		$rsub = $xoopsDB->query("SELECT nref, ref_url, mtime FROM $tbr WHERE $cond AND title='".addslashes($data['title'])."' ORDER BY $order", 20);
		$refs = array();
		$refn = array();
		while (list($nref, $url)=$xoopsDB->fetchRow($rsub)) {
		    $refs[] = $url;
		    $refn[] = $nref;
		}
		$data["refs"] = $refs;
		$data["refn"] = $refn;
		$data["ref_url"] = $refs[0];
	    }
	    echo "<tr class='$bg'><td class='trackitem'>$start. ".make_track_item($data)."</td></tr>\n";
	}
	echo "</table>\n";
	echo $pctrl;
    }
}
include (XOOPS_ROOT_PATH."/footer.php");
?>
